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

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class EmployeeController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'Employee';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Menu', 'CentralControl', 'EmployeeSalaryStructure', 'EmployeeConfig', 'Family', 'passport', 'Promotion', 'NoticePeriod', 'qualifcations', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmpAlterationDetails', 'ReportCriterias', 'SalaryIncrement', 'SalaryIncrementDetails', 'ComponentIncrement', 'EditPunches','WorkExperience', 'EmpDocument', 'EmpFam', 'Education', 'EmployeeJoin'); // Edited by Akshay on 17-4-2025
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index()
    {
        $this->Menu->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-02-2024
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        //end
        $this->autoRender = FALSE;
        $user_group = $this->Session->read("user_group");
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 30-1-2025
        $this->set("user", strtoupper($this->Session->read('company_code')));
        $emp_pkeys = 0;
        $this->set('emp_pkeys', $emp_pkeys);
        if ($user_group == '1' || ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'GAAR'))) { // Edited by Akshay on 30-1-2025
            //Admin view
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
            $missed_prof = $this->checkProff();
            $this->set('active_emp_count', $active_emp_count);

            //Fetch Units for the company
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();

            // Edited by Akshay on 7-2-2025
            $current_emp_pkey = $this->Session->read('emp_fkey');
            $user_group = $this->Session->read('user_group');
            $company_code = $this->Session->read('company_code');
            $branch_condition = "";
            if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'GAAR')) {

                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $arr_is_ho = $this->EmployeeDetails->query(
                    "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                    ['emp_pkey' => $current_emp_pkey]
                );
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                // if ($is_ho != 1) {
                //     $arr_branches = array_filter($arr_branches, function ($branch) use ($is_ho) {
                //         return $branch['branch_code'] === $is_ho;
                //     });
                // }
            }
            // End

            $this->set('arr_branches', $arr_branches);

            // $arr_Emp=$this->MasterdataManagement->getEmployeeListForCombo();
            // $this->set('arr_Emp',$arr_Emp);

            $arr_Des = $this->MasterdataManagement->getDesignationsListForCombo();
            // debug($arr_Des);
            $this->set('missed_prof', $missed_prof);
            $this->set('arr_Des', $arr_Des);
            $this->render('index');
        } else if ($user_group == '2') {
            //Employee View
            $emp_fkey = $this->Session->read("emp_fkey");
            $this->setup($emp_fkey);
            $this->render('setup');
        }
    }

    //edited by bindu
    public function setups($emp_pkey = 0)
    {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        //edited by athira on 04-02-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = $plan['0']['comp_contact_info']['plan'];
        $this->set('plan', $plan);
        $this->set('company_code', $company_code);
        //end
        //$company_code = $this->Session->read('company_code');


        $company_code = strtoupper($this->Session->read('company_code'));
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_countries = $this->EmployeeDetails->query("SELECT * FROM countries_nationality order by id asc");
        $this->set('arr_countries', $arr_countries);
        $this->set('user_group', $user_group);
        // debug($emp_pkey);
        //  exit;
        if ($emp_pkey) {
            //edit mode
            $this->set('emp_pkey', $emp_pkey);

            $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            if (is_array($arr_emp_details['EmployeeDetails'])) {
                $this->set('arr_emp_details', $arr_emp_details['EmployeeDetails']);
            }
            // debug($arr_emp_details); 

            //  $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->findByEmpFkey($emp_pkey);

            // debug($arr_emp_professional_profile);
            // $this->set('arr_professionalinfo', $arr_emp_professional_profile['EmployeeProfessionalDetails']);
            $this->set('user_group', $user_group);

            //   debug($user_group);
            if (isset($user_group) && $user_group == 2) {
                //Employee
                $head = 'My Profile';
            } else {
                //Admin
                $head = $arr_emp_details['EmployeeDetails']['first_name'] . " " . $arr_emp_details['EmployeeDetails']['middile_name'] . " " . $arr_emp_details['EmployeeDetails']['last_name'] . "'s Profile";
            }

            $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_pkey and end_date_effective is null ");
            $this->set('arr_gross', $arr_gross);

            $this->set('head', $head);
            $this->loadEmpDetails($emp_pkey);
            $user_avatar = $this->EmployeeDetails->query("
    SELECT 
        COALESCE(uc.avatar, ed.profile_pic) AS avatar
    FROM emp_details ed
    LEFT JOIN user_credentials uc 
        ON uc.emp_fkey = ed.emp_pkey
    WHERE ed.emp_pkey = $emp_pkey
");

$this->set('arr_user_avatar', $user_avatar);


            // $user_avatar = $this->EmployeeDetails->query("select profile_pic from emp_details where emp_pkey = $emp_pkey ");
            // $this->set('arr_user_avatar', $user_avatar);

            //Ends
            //Load employee professional details
            $this->loadEmpProfDetails($emp_pkey);
            //Ends
            //Load employee tax heads
            $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
            if ($user_group == 2) {
                $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$emp_pkey'");
                $this->set('payroUser', $payroUser);
            }
            //Ends

        } else {
            //add mode
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach ($arr_personalinfokeys as $key) {
                $arr_emp_personal_profile[$key] = '';
            }
            $arr_emp_personal_profile['nationality_id'] = "75"; //This is to set default nationality as INDIAN. by Arul P Das on 20-6-21

            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach ($arr_professionalinfokeys as $key) {
                $arr_emp_professional_profile[$key] = '';
            }
            if ($this->Session->read('company_code') == 'DEMO' || $this->Session->read('company_code') == 'KWMT') {
                $arr_emp_professional_profile['emp_category'] = '';
            }

            $this->set('head', 'New Employee');
            $this->set('emp_pkey', 0);
            // debug($arr_emp_personal_profile);
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
            $this->set('arr_taxationinfo', array());
        }
        // debug($arr_emp_personal_profile);
        //Fetch countries by ARUL P DAS on 9/5/2021
        $arr_countries = $this->EmployeeDetails->query("SELECT * FROM countries_nationality order by id asc");
        $this->set('arr_countries', $arr_countries);

        //Fetch taxation fields for creating form dynamically
        $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Edited by Akshay on 10-10-2023
        //Fetch category
        if ($company_code == 'DEMO' || $company_code == 'KWMT') {
            $arr_category = $this->EmployeeDetails->query("SELECT category_pkey,category_code,category_name FROM category WHERE status = 1 AND category_pkey in (SELECT category_fkey FROM grade WHERE status = 1 AND category_fkey != 0) ORDER BY category_name ASC;");
            $this->set('arr_category', $arr_category);
        }

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        // $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user = strtoupper($this->Session->read('company_code'));
        //$arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        //edited by sinsiya to display full branches for admin split
        if ($company_code == 'VGFS' || $company_code == 'DEMO' || $company_code == 'VSFS') {

            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        } else {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        }
        //edited by sinsiya to display full branches
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        if (count($arr_branches) == 0) {
            //The below code is to check whether the user is employee or admin. if it is employeem then list his criteria branches only
            $user_group = $this->Session->read('user_group');

            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );

                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }

        $this->set('arr_branches', $arr_branches);
        $this->set('user', $user);
        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        $emp_family = $this->EmployeeDetails->query("select * from emp_family where emp_fkey = '$emp_pkey' and is_nominee = 'Y' and status = 1");
        $this->set('emp_family', $emp_family);

        //Fetch Units for the company
        // $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' 
        //                                             and day_time_seq not in (SELECT multishift FROM emp_proff WHERE emp_fkey = '$emp_pkey'
        //                                             and multishift is not null)
        //                                             "); //Edited by Akshay on 12-9-2024
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);


        //Fetch Units for the company
        //        $arr_hierarchy = $this->EmployeeDetails->query("select emp_pkey,concat(first_name,' ',last_name) as fullname from emp_details where status = 1");
        //        $this->set('arr_hierarchy', $arr_hierarchy);
        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);


        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        // debug($qualifications);
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $hierarchy_set = $this->NoticePeriod->query("select * from emp_details where emp_pkey in (select attr1 from emp_proff where emp_fkey = '$emp_pkey') ");
        $this->set('hierarchy_set', $hierarchy_set);
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_pkey` = '$emp_pkey'");
        $emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        $yearmonth = $monthdd;
        // $queryResult = $this->EmployeeDetails->query("SELECT  time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')");
        // if (!($queryResult)) {
        //     return false;
        // }
        //edited by athira on 29-04-2025
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $branch = $this->EmployeeDetails->query("SELECT branch_code FROM emp_details WHERE emp_pkey='$emp_pkey' ");
        $branch_code = 'NULL';
        $month = date('Y-m-01');
        // $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
            return FALSE;
            die();
        }
        // if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
        //     if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
        //         return false;
        //         die();
        //     }
        // } else {
        //     if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
        //         return false;
        //         die();
        //     }
        // }
    }

     public function downloadempdataformatjoin()
    {
        $this->autoRender = false;

        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? $str_company_code . ".xlsx" : "Employee_Data_Format_" . date('Ymd_His') . ".xlsx";

        $columns = array(
            'Name *',
            'Birth Date * (yyyy-mm-dd)',
            'Gender *',
            'Email Address',
            'Phone Number',
            'Address',
            'Pin Code',
            'Nationality *',
            'State',
            'District',
            'Marital Status',
            'Guardian Name',
            'Relation',
            'Blood Group',
            'Aadhaar No *',
            'PAN No',
            'Bank Name',
            'Branch',
            'IFSC Code',
            'Account Number',
            'ESI No',
            'ESI Dispensary',
            'PF No',
            'UAN No',
            'Previous Member ID',
            'WPS ID',
            'LWF Registration No',
            'EPS Eligibility (Yes/No)',
            'Physical Handicap (Yes/No)',
            'International Worker (Yes/No)',
            'Country of Origin',
            'Locomotive (Yes/No)',
            'Hearing (Yes/No)',
            'Visual (Yes/No)'
        );

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Employee Data');

        // Bold header row
        $sheet->getStyle('A1:' . PHPExcel_Cell::stringFromColumnIndex(count($columns) - 1) . '1')
            ->getFont()->setBold(true);

        // Write headers
        foreach (range(0, count($columns) - 1) as $colIndex) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            $sheet->setCellValue($colLetter . '1', $columns[$colIndex]);
        }

        // === Mandatory field validation ===
        $mandatoryFields = [
            'Name *',
            'Birth Date * (yyyy-mm-dd)',
            'Gender *',
            'Nationality *',
            'Aadhaar No *'
        ];

        foreach ($mandatoryFields as $field) {
            $colIndex = array_search($field, $columns);
            if ($colIndex !== false) {
                $colLetter = PHPExcel_Cell::stringFromColumnIndex($colIndex);

                // Create a "required" validation rule
                $validation = $sheet->getCell($colLetter . '2')->getDataValidation();
                $validation->setType(PHPExcel_Cell_DataValidation::TYPE_CUSTOM);
                $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
                $validation->setFormula1('LEN(TRIM(' . $colLetter . '2))>0');
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle('Required Field Missing');
                $validation->setError('This field is mandatory. Please enter a value.');
                $validation->setPromptTitle('Mandatory Field');
                $validation->setPrompt('Please fill this field before saving.');
                for ($row = 2; $row <= 1000; $row++) {
                    $v = clone $validation;
                    $v->setFormula1('LEN(TRIM(' . $colLetter . $row . '))>0');
                    $sheet->getCell($colLetter . $row)->setDataValidation($v);
                }
            }
        }

        // === Gender dropdown ===
        $genderCol = array_search('Gender *', $columns);
        if ($genderCol !== false) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($genderCol);
            $validation = $sheet->getCell($colLetter . '2')->getDataValidation();
            $validation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
            $validation->setFormula1('"Male,Female,Other"');
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Invalid Gender');
            $validation->setError('Please select Male, Female, or Other.');
            $validation->setPromptTitle('Gender');
            $validation->setPrompt('Choose from Male, Female, or Other.');
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell($colLetter . $row)->setDataValidation(clone $validation);
            }
        }

        // === Aadhaar validation (12 digits) ===
        $aadhaarCol = array_search('Aadhaar No *', $columns);
        if ($aadhaarCol !== false) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($aadhaarCol);
            $validation = $sheet->getCell($colLetter . '2')->getDataValidation();
            $validation->setType(PHPExcel_Cell_DataValidation::TYPE_TEXTLENGTH);
            $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
            $validation->setOperator(PHPExcel_Cell_DataValidation::OPERATOR_EQUAL);
            $validation->setFormula1(12);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Invalid Aadhaar');
            $validation->setError('Aadhaar number must be exactly 12 digits.');
            $validation->setPromptTitle('Aadhaar Number');
            $validation->setPrompt('Enter exactly 12 digits.');
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell($colLetter . $row)->setDataValidation(clone $validation);
            }
        }

        // === PAN validation (10 characters) ===
        $panCol = array_search('PAN No', $columns);
        if ($panCol !== false) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($panCol);
            $validation = $sheet->getCell($colLetter . '2')->getDataValidation();
            $validation->setType(PHPExcel_Cell_DataValidation::TYPE_TEXTLENGTH);
            $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
            $validation->setOperator(PHPExcel_Cell_DataValidation::OPERATOR_EQUAL);
            $validation->setFormula1(10);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Invalid PAN');
            $validation->setError('PAN number must be exactly 10 characters.');
            $validation->setPromptTitle('PAN Number');
            $validation->setPrompt('Enter exactly 10 characters.');
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell($colLetter . $row)->setDataValidation(clone $validation);
            }
        }
$validBloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

$bloodGroupCol = array_search('Blood Group', $columns);
if ($bloodGroupCol !== false) {
    $colLetter = PHPExcel_Cell::stringFromColumnIndex($bloodGroupCol);
    $validation = $sheet->getCell($colLetter . '2')->getDataValidation();
    $validation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
    $validation->setFormula1('"' . implode(',', $validBloodGroups) . '"');
    $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
    $validation->setAllowBlank(true);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setErrorTitle('Invalid Blood Group');
    $validation->setError('Please select a valid blood group from the list.');
    $validation->setPromptTitle('Blood Group');
    $validation->setPrompt('Choose a valid blood group from this A+, A-, B+, B-, AB+, AB-, O+, O-');
    for ($row = 2; $row <= 1000; $row++) {
        $sheet->getCell($colLetter . $row)->setDataValidation(clone $validation);
    }
}
        // === Output Excel file ===
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }



    // Edited by Bindu on 22-10-2025 end
      public function saveToDetails()
    {
        $this->autoRender = false;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $this->EmployeeDetails->primaryKey = 'emp_pkey';

        if ($this->request->is('post')) {

            $data = $this->request->data;

            if (empty($data['emp_pkey'])) {
                echo json_encode(array('status' => 'error', 'message' => 'Employee key is required'));
                return;
            }

            $empPkey = (int)$data['emp_pkey'];
            
            // Authorization: only allow editing own record unless session user_group is 1 or 2
            $sessionEmpFkey = (int) $this->Session->read('emp_fkey');
            $userGroup = $this->Session->read('user_group');
            if ($empPkey !== $sessionEmpFkey && $userGroup != 1 && $userGroup != 2) {
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Update not allowed.']);
                return;
            }

            $existing = $this->EmployeeDetails->findByEmpPkey($empPkey);
            if (!$existing) {
                echo json_encode(array('status' => 'error', 'message' => 'Employee not found'));
                return;
            }

            $empId = $existing['EmployeeDetails']['emp_id'];
            $branch_code = $existing['EmployeeDetails']['branch_code'];
            $existingStatus = !empty($existing['EmployeeDetails']['status']) ? $existing['EmployeeDetails']['status'] : 1;


            $checkboxFields = array('eps', 'international_worker', 'physical_handicap', 'locomotive', 'hearing', 'visual');
            foreach ($checkboxFields as $field) {
                $data[$field] = (!empty($data[$field])) ? 'Y' : 'N';
            }

            $salaryQuery = "
    SELECT salary_head_item_desc 
    FROM emp_salary_structure 
    WHERE emp_fkey = '$empPkey' 
    AND end_date_effective IS NULL
";

            $salaryRes = $this->EmployeeDetails->query($salaryQuery);

            $salaryHeads = array();
            foreach ($salaryRes as $row) {
                $salaryHeads[] = strtoupper(trim($row['emp_salary_structure']['salary_head_item_desc']));
            }

            $errors = array();

            $esiChecked = false;
            $pfChecked = false;
            $lwfChecked = false;
            $epsChecked = false;
            $tdsChecked = false;
            $epfChecked = false;

            foreach ($salaryHeads as $head) {

                // ✅ ESI
                if (!$esiChecked && strpos($head, 'ESI') !== false && empty($data['esi'])) {
                    $errors[] = "ESI";
                    $esiChecked = true;
                }

                // ✅ PF (strict match) - standalone PF only, EPF is handled separately below
                if (
                    !$pfChecked &&
                    preg_match('/\bPF\b/', $head) &&
                    strpos($head, 'EPF') === false &&
                    empty($data['company_pf'])
                ) {
                    $errors[] = "PF";
                    $pfChecked = true;
                }

                // ✅ LWF
                if (
                    !$lwfChecked &&
                    strpos($head, 'LWF') !== false &&
                    empty($data['lwf_code'])
                ) {
                    $errors[] = "LWF";
                    $lwfChecked = true;
                }

                // ✅ EPS
                if (
                    !$epsChecked &&
                    strpos($head, 'EPS') !== false &&
                    (empty($data['eps']) || $data['eps'] == 'N')
                ) {
                    $errors[] = "EPS";
                    $epsChecked = true;
                }

                // ✅ TDS → PAN required
                if (
                    !$tdsChecked &&
                    strpos($head, 'TDS') !== false &&
                    empty($data['pan_no'])
                ) {
                    $errors[] = "PAN (required for TDS)";
                    $tdsChecked = true;
                }

                // ✅ EPF → ONLY EPF (not employee contribution)
                if (
                    !$epfChecked &&
                    strpos($head, 'EPF') !== false
                ) {
                    $pfEmpty  = empty($data['company_pf']);
                    $uanEmpty = empty($data['pf']); // UAN field

                    if ($pfEmpty && $uanEmpty) {
                        $errors[] = "PF and UAN required for EPF";
                    } elseif ($pfEmpty) {
                        $errors[] = "PF (required for EPF)";
                    } elseif ($uanEmpty) {
                        $errors[] = "UAN (required for EPF)";
                    }

                    $epfChecked = true;
                }
            }

            // ✅ Final error response
            if (!empty($errors)) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => implode(', ', $errors) . ' field cannot be empty for the allocated salary structure'
                ));
                return;
            }
            $company_code = $this->Session->read('company_code');

           $updateData = array(
    'emp_pkey' => $empPkey,
    'company_code' => $company_code,
    'branch_code' => $branch_code,
    'emp_id' => $empId,

    'first_name' => isset($data['first_name']) ? $data['first_name'] : '',
    'middile_name' => isset($data['middile_name']) ? $data['middile_name'] : '',
    'last_name' => isset($data['last_name']) ? $data['last_name'] : '',
    'address' => isset($data['address']) ? $data['address'] : '',
    'city' => isset($data['district']) ? $data['district'] : '',
    'classification' => isset($data['classification']) ? $data['classification'] : '',
    'state' => isset($data['state']) ? $data['state'] : '',
    'guradian' => isset($data['guradian']) ? $data['guradian'] : '',
    'relation_guardian' => isset($data['relation_guardian']) ? $data['relation_guardian'] : '',
    'nationality_id' => isset($data['nationality_id']) ? $data['nationality_id'] : '',
    'pincode' => isset($data['pincode']) ? $data['pincode'] : '',
    'mobile_no' => isset($data['mobile_no']) ? $data['mobile_no'] : '',
    'email' => isset($data['email']) ? $data['email'] : '',
    'maritual_status' => isset($data['maritual_status']) ? $data['maritual_status'] : '',
    'education' => isset($data['education']) ? $data['education'] : '',
    'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : '',
    'bank_name' => isset($data['bank']) ? $data['bank'] : '',
    'branch_name' => isset($data['bank_branch']) ? $data['bank_branch'] : '',
    'branch_address' => isset($data['branch_address']) ? $data['branch_address'] : '',
    'name_as_per_bank' => isset($data['name_as_per_bank']) ? $data['name_as_per_bank'] : '',
    'ifsc_code' => isset($data['ifsc_code']) ? $data['ifsc_code'] : '',
    'account_no' => isset($data['account_no']) ? $data['account_no'] : '',
   'pf' => isset($data['pf']) ? $data['pf'] : '',
    'company_pf' => isset($data['company_pf']) ? $data['company_pf'] : '',
    'previous_member_id' => isset($data['previous_member_id']) ? $data['previous_member_id'] : '',
    'esi_dispensary' => isset($data['esi_dispensary']) ? $data['esi_dispensary'] : '',
    'blood' => isset($data['blood']) ? $data['blood'] : '',
    'esi' => isset($data['esi']) ? $data['esi'] : '',
    'eps' => isset($data['eps']) ? $data['eps'] : '',
    'international_worker' => isset($data['international_worker']) ? $data['international_worker'] : '',
    'country' => isset($data['country']) ? $data['country'] : '',
    'physical_handicap' => isset($data['physical_handicap']) ? $data['physical_handicap'] : '',
    'locomotive' => isset($data['locomotive']) ? $data['locomotive'] : '',
    'hearing' => isset($data['hearing']) ? $data['hearing'] : '',
    'visual' => isset($data['visual']) ? $data['visual'] : '',
    'id_card' => isset($data['id_card']) ? $data['id_card'] : '',
    'pan_no' => isset($data['pan_no']) ? $data['pan_no'] : '',
    'wps_code' => isset($data['wps_code']) ? $data['wps_code'] : '',
    'lwf_code' => isset($data['lwf_code']) ? $data['lwf_code'] : '',

    'status' => $existingStatus
);


            if (!empty($_FILES['profile_image']['tmp_name'])) {

                $file = $_FILES['profile_image'];
                $fileName = time() . '_' . basename($file['name']);
                $uploadPath = 'img/avatar/';
                $fullPath = $uploadPath . $fileName;

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                    $updateData['profile_pic'] = $fullPath;
                }
            } else {

                if (!empty($data['profile_image_delete']) && $data['profile_image_delete'] == '1') {

                    if (!empty($existing['EmployeeDetails']['profile_pic'])) {
                        $existingFile = $existing['EmployeeDetails']['profile_pic'];
                        if (file_exists($existingFile)) {
                            unlink($existingFile);
                        }
                    }

                    $updateData['profile_pic'] = null;
                } else {
                    $updateData['profile_pic'] = !empty($existing['EmployeeDetails']['profile_pic'])
                        ? $existing['EmployeeDetails']['profile_pic']
                        : null;
                }
            }


            $this->UserCredentials->updateAll(
                array('UserCredentials.avatar' => "'" . addslashes($updateData['profile_pic']) . "'"),
                array('UserCredentials.emp_fkey' => $empPkey)
            );

            $this->EmployeeDetails->id = $empPkey;

            if ($this->EmployeeDetails->save($updateData)) {
                echo json_encode(array(
                    'status' => 'success',
                    'message' => 'Employee updated successfully',
                    'emp_pkey' => $empPkey
                ));
            } else {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Failed to update employee'
                ));
            }
        }
    }


    // public function updateEditable()
    // {
    //     $this->autoRender = false; // No view for AJAX
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    //     if ($this->request->is('post')) {

    //         $emp_pkey = $this->request->data('emp_pkey');
    //         $editable = $this->request->data('editable');

    //         // debug($emp_pkey);

    //         $this->loadModel('EmployeeDetails');

    //         // $arr_emp_details = $this->EmployeeDetails->find('first', [
    //         //     'conditions' => ['EmployeeDetails.emp_pkey' => $emp_pkey]
    //         // ]);

    //         $this->EmployeeDetails->id = $emp_pkey;
    //         if ($this->EmployeeDetails->saveField('editable', $editable)) {
    //             echo json_encode(['status' => 'success', 'editable' => $editable]);
    //         } else {
    //             echo json_encode(['status' => 'error']);
    //         }
    //     }
    // }
    public function updateEditable()
    {
        $this->autoRender = false; // No view for AJAX
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        if ($this->request->is('post')) {

            $emp_pkey = (int) $this->request->data('emp_pkey');
            $editable = $this->request->data('editable');

            if ($emp_pkey <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid employee key.']);
                return;
            }

            // Authorization: only allow editing own record unless session user_group is 1 or 2
            $sessionEmpFkey = (int) $this->Session->read('emp_fkey');
            $userGroup = $this->Session->read('user_group');
            if ($emp_pkey !== $sessionEmpFkey && $userGroup != 1 && $userGroup != 2) {
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Update not allowed.']);
                return;
            }

            $this->loadModel('EmployeeDetails');

            $exists = $this->EmployeeDetails->find('count', ['conditions' => ['emp_pkey' => $emp_pkey]]);
            if (!$exists) {
                echo json_encode(['status' => 'error', 'message' => 'Employee record not found.']);
                return;
            }

            $this->EmployeeDetails->id = $emp_pkey;
            if ($this->EmployeeDetails->saveField('editable', $editable)) {
                echo json_encode(['status' => 'success', 'editable' => $editable]);
            } else {
                echo json_encode(['status' => 'error']);
            }
        }
    }

    function importEmployeetoEmpjoin()
    {
        $this->autoRender = false;

        $arr_request_data = $this->request->data;

        $response_histor_add = $this->getDataFromAPI($arr_request_data['profileID']);
        // $this->layout = null;

        $company_code = $this->Session->read('company_code');
        $this->EmployeeJoin->useDbConfig = $this->Session->read('ds');
        $this->EmpDocument->useDbConfig = $this->Session->read('ds');
        $this->EmpFam->useDbConfig = $this->Session->read('ds');
        $this->Education->useDbConfig = $this->Session->read('ds');
        // $this->WorkExperience->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
      $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $arr_reponse = "";
        $responseData = json_decode($response_histor_add);

        if ($responseData->status == 200) {
            $arr_employee_data = get_object_vars($responseData->employeeInfo->employeeDetails);
            $qualification = $responseData->employeeInfo->qualification;
            $passportVisa = $responseData->employeeInfo->passportVisa;
            $family = $responseData->employeeInfo->family;


            $arr_employee_data = get_object_vars($responseData->employeeInfo->employeeDetails);

$email = isset($arr_employee_data['email']) ? $arr_employee_data['email'] : '';

if (!empty($email)) {

    // Check in EmployeeJoin
    $existingJoin = $this->EmployeeJoin->find('first', [
        'conditions' => ['email' => $email]
    ]);

    // Check in EmployeeDetails table (adjust table name if required)
    $existingDetails = $this->EmployeeDetails->find('first', [
        'conditions' => ['email' => $email]
    ]);

    if (!empty($existingJoin) || !empty($existingDetails)) {
        return json_encode([
            'success' => false,
            'error'   => "Employee with email <b>$email</b> already exists.",
            'message' => "Duplicate Email"
        ]);
    }
}
if (!empty($arr_employee_data['dateOfBirth'])) {

    $dob = date("Y-m-d", strtotime($arr_employee_data['dateOfBirth']));
    $age = date_diff(date_create($dob), date_create('today'))->y;

    if ($age < 18) {
        return json_encode([
            'success' => false,
            'error'   => "Employee must be at least <b>18 years</b> old.",
            'message' => "Invalid Age"
        ]);
    }
}
$id_card = isset($arr_employee_data['idCard']) 
    ? preg_replace('/\D/', '', $arr_employee_data['idCard']) 
    : '';
            $result = $this->EmployeeJoin->save(array(
                // "company_code" => $company_code,
                // "branch_code" => $arr_request_data['branchCode'],
                "emp_fkey" => "0",
                "first_name" => isset($arr_employee_data['firstName']) ? $arr_employee_data['firstName'] : '',
                "last_name" => isset($arr_employee_data['lastName']) ? $arr_employee_data['lastName'] : '',
                // "middile_name" => isset($arr_employee_data['middleName']) ? $arr_employee_data['middleName'] : '',
                "classification" => $arr_employee_data['gender'] == 'M' ? 'male' : 'female',
                "address" => isset($arr_employee_data['address']) ? $arr_employee_data['address'] : '',
                "district" => isset($arr_employee_data['city']) ? $arr_employee_data['city'] : '',
                "state" => isset($arr_employee_data['state']) ? $arr_employee_data['state'] : '',
                "nationality_id" => isset($arr_employee_data['nationality']) ? $arr_employee_data['nationality'] : '',
                "pincode" => isset($arr_employee_data['pincode']) ? $arr_employee_data['pincode'] : '',
                "mobile_no" => isset($arr_employee_data['mobileNo']) ? $arr_employee_data['mobileNo'] : '',
                "email" => isset($arr_employee_data['email']) ? $arr_employee_data['email'] : '',
                "maritual_status" => isset($arr_employee_data['maritalStatus']) ? $arr_employee_data['maritalStatus'] : '',
                // "education" => isset($arr_employee_data['education']) ? $arr_employee_data['education'] : '',
                "date_of_birth" => isset($arr_employee_data['dateOfBirth']) ? date("Y-m-d", strtotime($arr_employee_data['dateOfBirth'])) : '',
                "bank" => isset($arr_employee_data['bankName']) ? $arr_employee_data['bankName'] : '',
                "bank_branch" => isset($arr_employee_data['bankBranchName']) ? $arr_employee_data['bankBranchName'] : '',
                // "branch_address" => isset($arr_employee_data['branchAddress']) ? $arr_employee_data['branchAddress'] : '',
                // "name_as_per_bank" => isset($arr_employee_data['nameAsPerBank']) ? $arr_employee_data['nameAsPerBank'] : '',
                "ifsc_code" => isset($arr_employee_data['ifscCode']) ? $arr_employee_data['ifscCode'] : '',
                "account_no" => isset($arr_employee_data['accountNo']) ? $arr_employee_data['accountNo'] : '',
                "pf" => isset($arr_employee_data['pf']) ? $arr_employee_data['pf'] : '',
                "company_pf" => isset($arr_employee_data['companyPf']) ? $arr_employee_data['companyPf'] : '',
                "esi_dispensary" => isset($arr_employee_data['esiDispensary']) ? $arr_employee_data['esiDispensary'] : '',
                "esi" => isset($arr_employee_data['esi']) ? $arr_employee_data['esi'] : '',
                // "id_card" => isset($arr_employee_data['idCard']) ? $arr_employee_data['idCard'] : '',
                "id_card" => $id_card,
                "guradian" => isset($arr_employee_data['guardian']) ? $arr_employee_data['guardian'] : '',
                "relation_guardian" => isset($arr_employee_data['relationGuardian']) ? $arr_employee_data['relationGuardian'] : '',
                // "pan_no" => isset($arr_employee_data['panNo']) ? $arr_employee_data['panNo'] : '',
             "pan_no" => isset($arr_employee_data['panNo']) 
    ? strtoupper($arr_employee_data['panNo']) 
    : '',
                // "name_as_on_pan" => isset($arr_employee_data['nameAsOnPan']) ? $arr_employee_data['nameAsOnPan'] : '',
                // "name_as_on_aadhaar" => isset($arr_employee_data['nameAsOnAadhaar']) ? $arr_employee_data['nameAsOnAadhaar'] : '',
                // "parent" => isset($arr_employee_data['parent']) ? $arr_employee_data['parent'] : '',
                "hearing" => (isset($arr_employee_data['hearing']) && ($arr_employee_data['hearing']) === 'Yes') ? 'Y' : 'N',
                "visual"  => (isset($arr_employee_data['visual'])  && ($arr_employee_data['visual']) === 'Yes')  ? 'Y' : 'N',
                "physical_handicap" => (isset($arr_employee_data['physicalHandicap']) && ($arr_employee_data['physicalHandicap']) === 'Yes') ? 'Y' : 'N',
                "previous_member_id" => isset($arr_employee_data['previousMemberId']) ? $arr_employee_data['previousMemberId'] : '',
                "blood" => isset($arr_employee_data['blood']) ? $arr_employee_data['blood'] : "",
                "locomotive" => (isset($arr_employee_data['locomotive']) &&  ($arr_employee_data['locomotive']) === 'Yes') ? 'Y' : 'N',
                "status" => 1,
                "international_worker" => (isset($arr_employee_data['internationalWorker'])  && ($arr_employee_data['internationalWorker']) === 'Yes') ? 'Y' : 'N',
            ));


            if (!empty($result)) {
                $message = 'Personal Details Saved Successfully';
                $emp_join_pkey = $this->EmployeeJoin->getLastInsertID();
            }

            foreach ($qualification as $key => $value) {
                // Convert fromDate and toDate into YYYY-YYYY format
                $fromYear = date('Y', strtotime($value->fromDate));
                $toYear   = date('Y', strtotime($value->toDate));
                $duration = $fromYear . '-' . $toYear;

                $result = $this->Education->saveAll(array(
                    "emp_join_fkey" => $emp_join_pkey,
                    "course"        => $value->course,
                    "university"    => $value->university,
                    "duration"      => $duration,  // Added duration
                    "status"        => 1,
                    "mark"          => $value->mark
                ));

                // debug($result);
            }
            $allNationalities = $this->EmpDocument->query("
                SELECT id, nationality 
                FROM countries_nationality
            ");

            $nationalityMap = [];
            foreach ($allNationalities as $c) {
                $nationalityMap[$c['countries_nationality']['id']] = $c['countries_nationality']['nationality'];
            }

            // Passport & Visa documents loop
            foreach ($passportVisa as $value) {
                $nationalityName = isset($nationalityMap[$value->nationality]) ? $nationalityMap[$value->nationality] : '';

                $result = $this->EmpDocument->saveAll(array(
                    "emp_join_fkey" => $emp_join_pkey,
                    "document_type" => $value->documentType,
                    "document_number" => $value->name,
                    "classification" => $value->classification,
                    "name" => $value->documentNumber,
                    "valid_till" => date("Y-m-d", strtotime($value->validTo)),
                    "relation" => $value->relation,
                    "valid_from" => date("Y-m-d", strtotime($value->validFrom)),
                    "nationality" => $nationalityName,
                    "remarks" => $value->remarks,
                    "reccuring" => (isset($value->reccuring) && strtoupper($value->reccuring) === 'Y') ? 'Yes' : 'No',

                ));
            }

            // Family members loop
            foreach ($family as $value) {
                $nationalityName = isset($nationalityMap[$value->nationality]) ? $nationalityMap[$value->nationality] : '';

                $result = $this->EmpFam->saveAll(array(
                    "emp_join_fkey" => $emp_join_pkey,
                    "name" => $value->name,
                    "DOB" => date("Y-m-d", strtotime($value->dateOfBirth)),
                    "gender" => $value->gender,
                    "blood_group" => $value->bloodGroup,
                    "relation" => $value->relation,
                    "nationality" => $nationalityName,
                    "emergency_contact" => (isset($value->emergencyContact) && strtoupper($value->emergencyContact) === 'Yes') ? 'Y' : 'N',
                    "alternate_number" => $value->alternateNumber,
                    "contact_number" => $value->contactNumber,
                    "is_nominee" => $value->isNominee,
                    "status" => 1,
                    "remarks" => $value->remarks,
                ));
            }
        } else {
            $arr_reponse = json_decode($response_histor_add);
        }

        $message = 'All Data Saved Successfully';
        return json_encode(array('success' => true, 'error' => "", 'emp_join_pkey' => $emp_join_pkey, 'message' => $message));
    }

    //end
     public function savedata($pkey)
    {
	 $this->autoRender = false;
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
		 $userCredentials_data = $this->UserCredentials->find("first", array('fields' => 'user_id,email,attr2', "conditions" => array("emp_fkey" => $pkey)));
            $data_db = $this->EmployeeDetails->find("first", array('fields' => 'branch_code, company_code', "conditions" => array("emp_pkey" => $pkey)));

            $employeeInfo = $this->UserCredentials->query("select branch,designation,department,joining_date from employee_info WHERE emp_pkey = " . $pkey);
            $companyInfo = $this->UserCredentials->query("select * from comp_contact_info WHERE id = 1 ");
			 $payloadData = array(
                "branchCode" => $data_db['EmployeeDetails']['branch_code'],
                "companyCode" => $data_db['EmployeeDetails']['company_code'],
                "email" => $userCredentials_data['UserCredentials']['email'],
                "employeeId" => $userCredentials_data['UserCredentials']['user_id'],
                "joiningDate" => isset($employeeInfo['0']['employee_info']['joining_date']) ? date("d-m-Y", strtotime($employeeInfo['0']['employee_info']['joining_date'])) : '',
                "policyName" => "",
                "companyName" => isset($companyInfo['0']['comp_contact_info']['business_name']) ? $companyInfo['0']['comp_contact_info']['business_name'] : '',
                "department" => isset($employeeInfo['0']['employee_info']['department']) ? $employeeInfo['0']['employee_info']['department'] : '',
                "designation" => isset($employeeInfo['0']['employee_info']['designation']) ? $employeeInfo['0']['employee_info']['designation'] : '',
                "profileId" => $userCredentials_data['UserCredentials']['attr2'],
                "status" => 1
            );
			  $employeedetailsResponses = $this->empDataThirdparty_update($payloadData);
              $response_histor_add = $this->companyHistory_add($payloadData);
			 //   echo json_encode(array('msg' => 'User Credentials saved successfully',  'employeeAddAPi' => json_decode($employeedetailsResponses)));
    return false;
            }

 public function empDataThirdparty_update($data) {
        $this->autoRender = false;
        $curl = curl_init();

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $userId = $data['employeeId'];

        $fields = 'EmployeeDetails.*, UserCredentials.avatar, emp_pkey,EmployeeProfessionalDetails.emp_company_id,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeProfessionalDetails.designation,DATE_FORMAT(EmployeeProfessionalDetails.joining_date, "%d/%m/%Y") AS joining_date,mobile_no';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'user_credentials',
                'alias' => 'UserCredentials',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
            )
        );

        $conditions[] = "UserCredentials.user_id = '$userId' ";

        $empdetailData = $this->EmployeeDetails->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            "conditions" => $conditions,
        ));

        // debug($empdetailData);

        $payloadData = array(
            "firstName" => $empdetailData['0']['EmployeeDetails']['first_name'],
            "profileId" => $data['profileId'],
            "middleName" => " ",
            "lastName" => ($empdetailData['0']['EmployeeDetails']['last_name'] != "") ? $empdetailData['0']['EmployeeDetails']['last_name']: '-',
            "gender" => $empdetailData['0']['EmployeeDetails']['classification'],
            "address" => $empdetailData['0']['EmployeeDetails']['address'],
            "city" => $empdetailData['0']['EmployeeDetails']['city'],
            "state" => $empdetailData['0']['EmployeeDetails']['state'],
            "nationality" => 1,
            "country" => 2,
            "pincode" => $empdetailData['0']['EmployeeDetails']['pincode'],
            "mobileNo" => $empdetailData['0']['EmployeeDetails']['mobile_no'],
            "email" => ($empdetailData['0']['EmployeeDetails']['email'] != "") ? $empdetailData['0']['EmployeeDetails']['email']: '',
            "maritalStatus" => $empdetailData['0']['EmployeeDetails']['maritual_status'],
            "education" => $empdetailData['0']['EmployeeDetails']['education'],
            "dateOfBirth" => date("d-m-Y", strtotime($empdetailData['0']['EmployeeDetails']['date_of_birth'])),
            "bankName" => $empdetailData['0']['EmployeeDetails']['bank_name'],
            "bankBranchName" => $empdetailData['0']['EmployeeDetails']['branch_name'],
            "branchAddress" => $empdetailData['0']['EmployeeDetails']['branch_address'],
            "ifscCode" => $empdetailData['0']['EmployeeDetails']['ifsc_code'],
            "nameAsPerBank" => $empdetailData['0']['EmployeeDetails']['name_as_per_bank'],
            "accountNo" => $empdetailData['0']['EmployeeDetails']['account_no'],
            "pf" => $empdetailData['0']['EmployeeDetails']['pf'],
            "companyPf" => $empdetailData['0']['EmployeeDetails']['company_pf'],
            "esiDispensary" => $empdetailData['0']['EmployeeDetails']['esi_dispensary'],
            "esi" => $empdetailData['0']['EmployeeDetails']['esi'],
            "idCard" => $empdetailData['0']['EmployeeDetails']['id_card'],
            "guardian" => $empdetailData['0']['EmployeeDetails']['guradian'],
            "relationGuardian" => $empdetailData['0']['EmployeeDetails']['relation_guardian'],
            "panNo" => $empdetailData['0']['EmployeeDetails']['pan_no'],
            "nameAsOnPan" => $empdetailData['0']['EmployeeDetails']['name_as_on_pan'],
            "nameAsOnAadhaar" => $empdetailData['0']['EmployeeDetails']['name_as_on_aadhaar'],
            "previousMemberId" => $empdetailData['0']['EmployeeDetails']['previous_member_id'],
            "blood" => $empdetailData['0']['EmployeeDetails']['blood'],
            "hearing" => $empdetailData['0']['EmployeeDetails']['hearing'],
            "visual" => $empdetailData['0']['EmployeeDetails']['visual'],
            "physicalHandicap" => $empdetailData['0']['EmployeeDetails']['physical_handicap'],
            "locomotive" => $empdetailData['0']['EmployeeDetails']['locomotive'],
            "internationalWorker" => $empdetailData['0']['EmployeeDetails']['international_worker'],
        );

        // debug(json_encode($payloadData));

        // $payloadData = array(
        //     "firstName" => "Mithun",
        //     "profileId" => "SA103",
        //     "middleName" => "Raj",
        //     "lastName" => "S",
        //     "gender" => "M",
        //     "address" => "Janatha Road",
        //     "city" => "Kochi",
        //     "state" => "Kerala",
        //     "nationality" => 1,
        //     "country" => 2,
        //     "pincode" => "682030",
        //     "mobileNo" => "9847225998",
        //     "email" => "mithunraj2006@gmail.com",
        //     "maritalStatus" => "Un",
        //     "education" => "BTech",
        //     "dateOfBirth" => "01-09-1982",
        //     "bankName" => "SBI",
        //     "bankBranchName" => "Kaloor",
        //     "branchAddress" => "Kaloor Branch",
        //     "ifscCode" => "SBIN0054",
        //     "nameAsPerBank" => "Mit Bank Name",
        //     "accountNo" => "32458799412",
        //     "pf" => "EPF013",
        //     "companyPf" => "CPF4545",
        //     "esiDispensary" => "esiDis442",
        //     "esi" => "ESIID024",
        //     "idCard" => "EMP3459",
        //     "guardian" => "GUA2",
        //     "relationGuardian" => "Friend",
        //     "panNo" => "ARSi49555",
        //     "nameAsOnPan" => "Mithun Su Raj",
        //     "nameAsOnAadhaar" => "Mithun S Raj",
        //     "previousMemberId" => "PMID222",
        //     "blood" => "A+",
        //     "hearing" => "Normal",
        //     "visual" => "20-20",
        //     "physicalHandicap" => "Functional",
        //     "locomotive" => "Loco",
        //     "internationalWorker" => "Yes"
        // );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online/thirdpartyapi/empDetails/update',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payloadData),
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
     public function companyHistory_add($payloadData) {
        $this->autoRender = false;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online/thirdpartyapi/companyHistory/add',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payloadData),
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
    
    public function setupprofile($emp_pkey = 0)
    {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        $this->set("user", $this->Session->read('company_code'));
        if ($emp_pkey) {
            //edit mode
            $this->set('emp_pkey', $emp_pkey);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            if (is_array($arr_emp_details['EmployeeDetails'])) {
                $this->set('arr_emp_details', $arr_emp_details['EmployeeDetails']);
            }


            $this->set('user_group', $user_group);

            //   debug($user_group);
            if (isset($user_group) && $user_group == 2) {
                //Employee
                $head = 'My Profile';
            } else {
                //Admin
                $head = $arr_emp_details['EmployeeDetails']['first_name'] . " " . $arr_emp_details['EmployeeDetails']['middile_name'] . " " . $arr_emp_details['EmployeeDetails']['last_name'] . "'s Profile";
            }

            $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_pkey and end_date_effective is null ");
            $this->set('arr_gross', $arr_gross);

            $this->set('head', $head);
            $this->loadEmpDetails($emp_pkey);

            $user_avatar = $this->EmployeeDetails->query("select avatar from user_credentials where emp_fkey = $emp_pkey ");
            $this->set('arr_user_avatar', $user_avatar);

            //Ends
            //Load employee professional details
            $this->loadEmpProfDetails($emp_pkey);
            //Ends
            //Load employee tax heads
            $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
            //Ends
        } else {
            //add mode
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach ($arr_personalinfokeys as $key) {
                $arr_emp_personal_profile[$key] = '';
            }
            $arr_emp_personal_profile['nationality_id'] = "75"; //This is to set default nationality as INDIAN. by Arul P Das on 20-6-21

            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach ($arr_professionalinfokeys as $key) {
                $arr_emp_professional_profile[$key] = '';
            }

            $this->set('head', 'New Employee');
            $this->set('emp_pkey', 0);
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
            //debug($arr_emp_professional_profile);
            $this->set('arr_taxationinfo', array());
        }

        //Fetch countries by ARUL P DAS on 9/5/2021
        $arr_countries = $this->EmployeeDetails->query("SELECT * FROM countries_nationality order by id asc");
        $this->set('arr_countries', $arr_countries);

        //Fetch taxation fields for creating form dynamically
        $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        // $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $cur_emp_key = $this->Session->read("emp_fkey");
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        if (count($arr_branches) == 0) {
            //The below code is to check whether the user is employee or admin. if it is employeem then list his criteria branches only
            $user_group = $this->Session->read('user_group');
            $user = $this->Session->read('company_code');
            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );
                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }
        $this->set('arr_branches', $arr_branches);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        $emp_family = $this->EmployeeDetails->query("select * from emp_family where emp_fkey = '$emp_pkey' and is_nominee = 'Y' and status = 1");
        $this->set('emp_family', $emp_family);

        //Fetch Units for the company
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);


        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $hierarchy_set = $this->NoticePeriod->query("select * from emp_details where emp_pkey in (select attr1 from emp_proff where emp_fkey = '$emp_pkey') ");
        $this->set('hierarchy_set', $hierarchy_set);
    }

    public function listimported()
    {
        $this->autoRender = FALSE;
        $user_group = $this->Session->read("user_group");
        $emp_pkeys = 0;
        $this->set('emp_pkeys', $emp_pkeys);
        //Admin view
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));

        $missed_prof = $this->checkProff();
        $this->set('active_emp_count', $active_emp_count);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        // Edited by Akshay on 6-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $arr_branches = array_values(array_filter($arr_branches, function ($branch) use ($is_ho) {
                    return $branch['branch_code'] === $is_ho;
                }));
            }
        }
        // End

        $this->set('arr_branches', $arr_branches);

        // debug($arr_Des);
        $this->set('missed_prof', $missed_prof);
        $this->render('listimported');
    }

    public function employeesunder()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read("user_group");
        $this->set('user_group', $user_group);
        $cur_emp_key = $this->Session->read("emp_fkey");
        $emp_pkey = $this->Session->read("emp_fkey");
        $company_code = strtoupper($this->Session->read('company_code'));
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
        } else {
            $emp_pkeys = 0;
        }
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
        $conditions = array(
            array(
                'status' => 1,
                'EmployeeProffessional.attr1' => $emp_pkeys
            )
        );
        $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
        $this->set('active_emp_count', $active_emp_count);

        $this->set('emp_pkeys', $emp_pkeys);
        //Fetch Units for the company
        //commented by sinsiya
        //$arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        if ($company_code == 'VGFS' || $company_code == 'VSFS' || $company_code == 'DEMO') {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
        } else {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        }
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
            // Fetch Employee's Branch Code
            $company_code = $this->Session->read('company_code');
            if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) AS branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                $this->set('is_ho', $is_ho);
                if ($is_ho != 1) {
                    $emp_branch = $this->EmployeeDetails->query("SELECT branch_code, branch_name FROM branches WHERE branch_code = (SELECT branch_code FROM emp_details WHERE emp_pkey = $emp_pkey) LIMIT 1");
                    $arr_branches = array(
                        array(
                            'branch_code' => $emp_branch[0]['branches']['branch_code'],
                            'branch_name' => $emp_branch[0]['branches']['branch_name']
                        )
                    );
                } else {
                    $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
                }
            }
        }


        //added by arul
        if (count($arr_branches) == 0) {
            $user_group = $this->Session->read('user_group');
            $user = $this->Session->read('company_code');
            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );
                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }
        $this->set('arr_branches', $arr_branches);


        // $arr_Emp=$this->MasterdataManagement->getEmployeeListForCombo();
        // $this->set('arr_Emp',$arr_Emp);

        $arr_Des = $this->MasterdataManagement->getDesignationsListForCombo();
        // debug($arr_Des);
        $this->set('arr_Des', $arr_Des);
        $this->render('index');
    }

    public function testfunction()
    {
        $this->autoRender = false;

        var_dump($ffd);
    }

    public function checkProff()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $missed_prof = $this->EmployeeDetails->query("select first_name from emp_details where status !=0 and emp_pkey not in (select emp_fkey from emp_proff)");
        return $missed_prof;
    }

    public function getstages($site_pkey = 0)
    {
        $this->autoRender = false;
        //$site_pkey=[];
        //  debug($site_pkey);
        $sitepkey_array = $site_pkey;
        if ($sitepkey_array == '') {
            $conditions = '';
        } else {
            $conditions = 'branch_code="' . $sitepkey_array . '"';
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code', 'conditions' => array('status' => 1, $conditions))));
        //debug($arr_stages);
        $str_stage_options_html = '';
        $this->set('arr_Emp', $arr_Emp);
        // debug($arr_Emp);
        foreach ($arr_Emp as $value) {
            $emp_pkey = isset($value['emp_pkey']) ? $value['emp_pkey'] : '';
            $emp_name = isset($value['emp_name']) ? $value['emp_name'] : '';
            $str_stage_options_html .= '<option value="' . $emp_pkey . '">' . $emp_name . '</option>';
            //  debug($str_stage_options_html);
        }
        echo $str_stage_options_html;
    }

    public function getautohierarchycompletions()
    {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        $filter_condition = array();
        $emp = $arr_request_data['emp'];
        $searchkey = $arr_request_data['username'];
        $filter_condition[] = "(first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR EmpName like '%" . $searchkey . "%' OR EmployeeDetails.emp_id like '%" . $searchkey . "%') and EmployeeDetails.emp_pkey not in ('$emp') ";

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'employee_info',
                'alias' => 'EmpInfo',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpInfo.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,concat(first_name," ",ifnull(last_name," ")," - ",employee_id) as emp_name,branch_code', 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        $arr_filterresult = array(
            //            array(
            ////                'emp_pkey' => '',
            ////                'emp_name' => 'ALL',
            //            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = array_merge(isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array(), isset($val['0']) ? $val['0'] : array());
        }
        echo json_encode($arr_filterresult);
    }

    public function getautohierarchycompletionsvgfs()
    {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array();
        $emp = $arr_request_data['emp'];
        $searchkey = $arr_request_data['username'];
        $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%' and emp_pkey not in ('$emp') ";

        //    debug($filter_condition);
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => "emp_pkey,CONCAT(ifnull(first_name,''),' ', ifnull(last_name,''),' - ',`EmployeeProfessionalDetails`.`emp_company_id`) as emp_name,branch_code", 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        $arr_filterresult = array(
            array(
                'emp_pkey' => '',
                'emp_name' => 'No Employees Found',
            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = array_merge(isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array(), isset($val['0']) ? $val['0'] : array());
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function getautocompletions()
    {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array('status' => "1");
        if ($arr_request_data['branch'] != '') {

            $searchkey = $arr_request_data['username'];
            $branch = $arr_request_data['branch'];
            $filter_condition[] = 'branch_code = "' . $branch . '" and first_name LIKE "%' . $searchkey . '%"';
        } else {
            $searchkey = $arr_request_data['username'];
            //            $filter_condition[] = 'first_name LIKE "%' . $searchkey . '%"';
            $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%'";
        }
        //    debug($filter_condition);
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,concat(EmployeeDetails.first_name," ",last_name," ",EmployeeProfessionalDetails.emp_company_id) as emp_name,branch_code', 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
        $arr_filterresult = array(
            array(
                'emp_pkey' => '',
                'emp_name' => 'ALL',
            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val['0']) : array();
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */



    public function listemployeesProfileImporteds()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $arr_requestdata = $this->request->data;
        $user_group = $this->Session->read("user_group");
        // debug($arr_request_data['branch']);
        $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        //debug($branch);
        $emp = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $des = isset($arr_request_data['designation']) ? $arr_request_data['designation'] : '';
        //debug($branch);
        // debug($emp);
        $conditions = array("EmployeeDetails.status" => 4);
        //$conditions = array('EmployeeDetails.status != 0');
        if ($branch != '') {
            $conditions[] = 'EmployeeDetails.branch_code="' . $branch . '"';
        }
        // debug($branch);

        if ($emp != '') {

            $conditions[] = 'EmployeeDetails.emp_pkey="' . $emp . '"';
        }

        //   debug($emp);
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

        $fields = 'EmployeeDetails.status,Branches.branch_name,designation.desig_name, EmployeeDetails.classification, UserCredentials.avatar, designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",EmployeeDetails.first_name,EmployeeDetails.last_name) as name,EmployeeProfessionalDetails.designation,DATE_FORMAT(EmployeeProfessionalDetails.joining_date, "%d/%m/%Y") AS joining_date,mobile_no';
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
            $emp_pkey = $this->Session->read('emp_fkey');
            $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        if (isset($arr_request_data['emp'])) {
            // $conditions[] = "(first_name like '%".$arr_request_data['emp']."%' OR EmployeeProfessionalDetails.emp_company_id like '%".$arr_request_data['emp']."%'  OR last_name like '%".$arr_request_data['emp']."%' OR emp_id like '%".$arr_request_data['emp']."%')";
            $conditions[] = "(EmployeeDetails.first_name like '%" . $arr_request_data['emp'] . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $arr_request_data['emp'] . "%'  OR EmployeeDetails.last_name like '%" . $arr_request_data['emp'] . "%')";
        }

        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
        if ($count == 0) { //This is to check whether employee have hierarchie employees or not. if no employee in hierarchie displays all employees. by ***ARUL P DAS on 5/3/2020
            if ($user_group == '2') {
                foreach ($conditions as $check_key => $check_val) {
                    if (strpos($check_val, 'EmployeeProfessionalDetails.attr1') !== false) {
                        unset($conditions[$check_key]);
                    }
                }
            }
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));

            if ($cur_emp_branch_find) {
                $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                $conditions[] = "EmployeeDetails.branch_code = '$cur_emp_branch' ";
            }

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
        // debug($arr_emp);
        $company_code=$this->Session->read('company_code');
        // if($company_code=='HDFN' || $company_code=='HDEQ' || $company_code=='HDSC' || $company_code=='GLET' || $company_code=='GAAR' || $company_code=='NRMY' || $company_code=='TRCK' || $company_code=='MRZC' || $company_code=='DYGL' || $company_code=='SHIN' || $company_code=='AMST' || $company_code=='NWTR' || $company_code=='THNG' || $company_code=='CSMT'){
    if (!in_array($company_code, [
    'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN',
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','VNDG'
])) {
         $baseResumeUrl = Router::url('/', true) . 'Employee/downloadResume2/';
        }
        else{
          $baseResumeUrl = Router::url('/', true) . 'Employee/downloadResume/';
        }
        

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
        //debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
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
        // debug($arr_request_data['branch']);
        $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';

        $emp = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $des = isset($arr_request_data['designation']) ? $arr_request_data['designation'] : '';
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
            // debug($payroUser);exit;
        }
        //debug($branch);
        // debug($emp);
        if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
            $conditions = array("EmployeeDetails.status in(1,2)");
        } else {

            $conditions = array("EmployeeDetails.status" => 1);
        }
        //$conditions = array('EmployeeDetails.status != 0');
        if ($branch != '') {
            $conditions[] = 'EmployeeDetails.branch_code="' . $branch . '"';
        }
        // debug($branch);
        //        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
        //            $emp_fkey = $arr_request_data['employee'];
        //        } else {
        //            $emp_fkey = 'emp_fkey';
        //        }
        //debug($arr_request_data['employee']);
        if ($emp != '') {

            $conditions[] = 'EmployeeDetails.emp_pkey="' . $emp . '"';
        }

        //   debug($emp);
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

        //edited by sinsiya for admin split on 17-04-2024
        // debug($user_group);exit;
        if ($user_group == '2') {
            //if (!isset($payroUser[0]['emp_proff']['payro_priv'])) {
            if ($company_code == 'VGFS' || $company_code == 'VSFS') {
                //DEBUG($company_code);
                $emp_pkey = $this->Session->read('emp_fkey');
                $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
                //Edited by Askhay on 22-3-2024 
                //debug($company_code);
                if ($company_code != 'VGFS' && $company_code != 'VSFS' && $company_code != 'DEMO') { //edited by sinsiya 17-04-2024 
                    //debug($company_code);
                    $arr_branch = $this->EmployeeDetails->query("SELECT DISTINCT ed.branch_code FROM emp_details ed WHERE ed.emp_pkey = '$emp_pkey'");
                    $branch_code = isset($arr_branch[0]['ed']['branch_code']) ? $arr_branch[0]['ed']['branch_code'] : '';
                    $conditions[] = "EmployeeDetails.branch_code = '$branch_code'";
                }

                //edited by athira on 24-01-2025
                //   else {
                //   $conditions = array("EmployeeDetails.branch_code" => 1);
                // }
                //end
                // End
            } elseif ($company_code == 'GLET' || $company_code == 'DEMO') { // Edited by Bindu on 3-1-2026
                // Edited by Akshay on 21-1-2025
                $emp_pkey = $this->Session->read('emp_fkey');
                $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                if ($is_ho != 1) {
                    $conditions[] = "EmployeeDetails.branch_code = '$is_ho'";
                }
            }
            //}
        }


        if (isset($arr_request_data['emp'])) {


            $conditions[] = "(EmployeeDetails.first_name like '%" . $arr_request_data['emp'] . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $arr_request_data['emp'] . "%'  OR EmployeeDetails.last_name like '%" . $arr_request_data['emp'] . "%')";
            //DEBUG($conditions) ;
        }

        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
        // debug($count);
        //exit;
        if ($count == 0) { //This is to check whether employee have hierarchie employees or not. if no employee in hierarchie displays all employees. by ***ARUL P DAS on 5/3/2020
            //hided by sinsiya on 22-04-2024
            //  if ($user_group == '2') {
            // foreach ($conditions as $check_key => $check_val) {
            //  if (strpos($check_val, 'EmployeeProfessionalDetails.attr1') !== false) {
            //    unset($conditions[$check_key]);
            // }
            // }
            // }
            //end hiding by sinsiya
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = isset($cur_emp_branch_find[0]['EmployeeDetails']['branch_code']) ? $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'] : '';
            // if($user_group == '2'){
            // $cur_emp_key = $this->Session->read("emp_fkey");
            // $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");

            // if($payroUser[0]['emp_proff']['payro_priv'] != 1){
            //edited by sinsiya to display the full employees
            // $conditions[] = "EmployeeDetails.branch_code = '$cur_emp_branch' ";
            // }

            //}

            // $user_group = $this->Session->read("user_group");


            // debug('hi');
            $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
        }
        //debug('hi');
        // exit;
        // if($user_group == '2'){
        // debug('hi');
        //  $cur_emp_key = $this->Session->read("emp_fkey");
        //  $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
        //  $branch= $payroUser[0]['emp_proff']['emp_branch'];
        //  if($payroUser[0]['emp_proff']['payro_priv'] == 1){
        //   $conditions[] = "EmployeeDetails.branch_code='$branch' ";
        //   }
        // debug($conditions);
        //  }
        //DEBUG($conditions);
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

        $company_code=$this->Session->read('company_code');
        // if($company_code=='HDFN' || $company_code=='HDEQ' || $company_code=='HDSC' || $company_code=='GLET' || $company_code=='GAAR' || $company_code=='NRMY' || $company_code=='TRCK' || $company_code=='MRZC'  || $company_code=='DYGL' || $company_code=='SHIN' || $company_code=='AMST' || $company_code=='NWTR' || $company_code=='THNG' || $company_code=='CSMT'){
    if (!in_array($company_code, [
    'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN',
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','VNDG'
])) {
        $baseResumeUrl = Router::url('/', true) . 'Employee/downloadResume2/';
        }
        else{
           $baseResumeUrl = Router::url('/', true) . 'Employee/downloadResume/';
        }

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
        //debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

    //branch filtter     
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

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and emp_details.branch_code='" . $cur_emp_branch . "'";
        }
        //employee branch wise sorting ends here

        if ($q != null) {
            $q_condition = "and (first_name like '%$q%' or emp_proff.emp_company_id like '%$q%') ";
        } else {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 $branch_condition $q_condition ORDER BY first_name ASC ");
        // debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function jsonss($emp = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;

        if ($q != null) {
            $q_condition = "and first_name like '%$q%'";
        } else {
            $q_condition = "";
        }

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and emp_details.branch_code='$cur_emp_branch' ";
        } else {
            $branch_condition = "";
        }
        //employee branch wise sorting ends here

        $branch_array = $this->EmployeeDetails->query("select * from emp_details  where status = 1 $q_condition $branch_condition and emp_pkey != $emp ");

        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "SELECT");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function removeProfileImage($pkey = 0)
    {
        $success = 0;
        $this->autoRender = false;
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $query = $this->qualifcations->query("UPDATE `user_credentials` SET `avatar` = null WHERE `emp_fkey` = '$pkey'");
        if ($query !== false) {
            $success = 1;
        } else {
            $success = 0;
        }
        echo json_encode($success);
    }

    public function jsonsb($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "name like '%$q%'";
        } else {
            $q_condition = "";
        }
        $q_condition = "status = '1' ";
        $branch_array = $this->EmployeeDetails->query("select * from emp_banks Where $q_condition  ");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = "";
        //        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch .= '<option >' . $value['emp_banks']['name'];
        }


        //        $array['items'] = $branch;
        echo json_encode($branch);
    }

    public function setup($emp_pkey = 0)
    {
        $this->Menu->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-02-2024
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        //end 
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        //$company_code = $this->Session->read('company_code');
        $company_code = strtoupper($this->Session->read('company_code'));
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->set('user_group', $user_group);

        // Edited by Akshay on 13-5-2025
        $company_code = strtoupper($this->Session->read('company_code'));
        $this->set('company_code', $company_code);
        // End

        if ($emp_pkey) {
            //edit mode
            $this->set('emp_pkey', $emp_pkey);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            if (is_array($arr_emp_details['EmployeeDetails'])) {
                $this->set('arr_emp_details', $arr_emp_details['EmployeeDetails']);
            }

            $this->set('user_group', $user_group);

            //   debug($user_group);
            if (isset($user_group) && $user_group == 2) {
                //Employee
                $head = 'My Profile';
            } else {
                //Admin
                $head = $arr_emp_details['EmployeeDetails']['first_name'] . " " . $arr_emp_details['EmployeeDetails']['middile_name'] . " " . $arr_emp_details['EmployeeDetails']['last_name'] . "'s Profile";
            }

            $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_pkey and end_date_effective is null ");
            $this->set('arr_gross', $arr_gross);

            $this->set('head', $head);
            $this->loadEmpDetails($emp_pkey);

            $user_avatar = $this->EmployeeDetails->query("select avatar from user_credentials where emp_fkey = $emp_pkey ");
            $this->set('arr_user_avatar', $user_avatar);

            //Ends
            //Load employee professional details
            $this->loadEmpProfDetails($emp_pkey);
            //Ends
            //Load employee tax heads
            $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
            if ($user_group == 2) {


                $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$emp_pkey'");
                $this->set('payroUser', $payroUser);
            }
            //Ends

        } else {
           //add mode
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach ($arr_personalinfokeys as $key) {
                $arr_emp_personal_profile[$key] = '';
            }
            $arr_emp_personal_profile['nationality_id'] = "75"; //This is to set default nationality as INDIAN. by Arul P Das on 20-6-21

            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach ($arr_professionalinfokeys as $key) {
                $arr_emp_professional_profile[$key] = '';
            }
            if ($this->Session->read('company_code') == 'DEMO' || $this->Session->read('company_code') == 'KWMT') {
                $arr_emp_professional_profile['emp_category'] = '';
            }

            $this->set('head', 'New Employee');
            $this->set('emp_pkey', 0);
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
            // debug($arr_emp_professional_profile); exit;
            $this->set('arr_taxationinfo', array());
        }

        //Fetch countries by ARUL P DAS on 9/5/2021
        $arr_countries = $this->EmployeeDetails->query("SELECT * FROM countries_nationality order by id asc");
        $this->set('arr_countries', $arr_countries);

        //Fetch taxation fields for creating form dynamically
        $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Edited by Akshay on 10-10-2023
        //Fetch category
        if ($company_code == 'DEMO' || $company_code == 'KWMT') {
            $arr_category = $this->EmployeeDetails->query("SELECT category_pkey,category_code,category_name FROM category WHERE status = 1 AND category_pkey in (SELECT category_fkey FROM grade WHERE status = 1 AND category_fkey != 0) ORDER BY category_name ASC;");
            $this->set('arr_category', $arr_category);
        }

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        // $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user = strtoupper($this->Session->read('company_code'));
        //$arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        //edited by sinsiya to display full branches for admin split
        if ($company_code == 'VGFS' || $company_code == 'DEMO' || $company_code == 'VSFS') {

            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($cur_emp_key);
        } else {
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        }
        //edited by sinsiya to display full branches
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        if (count($arr_branches) == 0) {
            //The below code is to check whether the user is employee or admin. if it is employeem then list his criteria branches only
            $user_group = $this->Session->read('user_group');

            if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branches',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Branches.branch_code = EmployeeDetails.branch_code'
                        )
                    )
                );
                $emp_branch = $this->EmployeeDetails->find("all", array("fields" => "Branches.id,Branches.branch_code,Branches.branch_name", "joins" => $joins, "conditions" => array("emp_pkey" => $cur_emp_key, "EmployeeDetails.status" => 1)));
                $arr_branches = array(
                    array(
                        'id' => $emp_branch[0]['Branches']['id'],
                        'branch_code' => $emp_branch[0]['Branches']['branch_code'],
                        'branch_name' => $emp_branch[0]['Branches']['branch_name']
                    )
                );
            }
        }

        // Edited by Akshay on 6-2-2025
        if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
            $this->Units->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->Units->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $cur_emp_key]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $branch_condition = array();
            if ($is_ho != 1) {
                // $branch_condition = array("branch_code" => $is_ho, "status" => 1);
            }
            try {
                $emp_branch = $this->Units->find("all", array("fields" => "Units.id,Units.branch_code,Units.branch_name",  "conditions" => $branch_condition));
            } catch (Exception $e) {
                debug($e);
            }

            $arr_branches = [];
            foreach ($emp_branch as $branch) {
                $arr_branches[] = [
                    'id' => $branch['Units']['id'],
                    'branch_code' => $branch['Units']['branch_code'],
                    'branch_name' => $branch['Units']['branch_name']
                ];
            }
        }
        // End  
        $this->set('arr_branches', $arr_branches);
        $this->set('user', $user);
        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        $emp_family = $this->EmployeeDetails->query("select * from emp_family where emp_fkey = '$emp_pkey' and is_nominee = 'Y' and status = 1");
        $this->set('emp_family', $emp_family);

        //Fetch Units for the company

        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        //Fetch Units for the company
        //        $arr_hierarchy = $this->EmployeeDetails->query("select emp_pkey,concat(first_name,' ',last_name) as fullname from emp_details where status = 1");
        //        $this->set('arr_hierarchy', $arr_hierarchy);
        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);

        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $hierarchy_set = $this->NoticePeriod->query("select * from emp_details where emp_pkey in (select attr1 from emp_proff where emp_fkey = '$emp_pkey') ");
        $this->set('hierarchy_set', $hierarchy_set);
        if ($user_group == 2) {

            $payroUser = $this->EmployeeProfessionalDetails->query("select payro_priv from emp_proff where emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        //edited by athira on 29-04-2025
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $branch = $this->EmployeeDetails->query("SELECT branch_code FROM emp_details WHERE emp_pkey='$emp_pkey' ");
        $branch_code = 'NULL';
        $month = date('Y-m-01');
        // $deleterecords = $this->EditPunches->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        if (!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
            return FALSE;
            die();
        }
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            if (!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
                return false;
                die();
            }
        } else {
            if ($company_code!=='NRMY'){
               if (!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
                return false;
                die();
            }
            }
            
        }
        //end
    }

    //Edited by Akshay on 10-10-2023
    public function getGrade($category_pkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');

        $arr_grade = array();
        if ($company_code == 'DEMO' || $company_code == 'KWMT') {
            if ($category_pkey != 0) {
                $arr_grade = $this->EmployeeDetails->query("SELECT grade_pkey, grade_code, grade_name, pay_scale FROM grade WHERE category_fkey = $category_pkey AND status = 1 ORDER BY grade_name ASC;");
            }
            echo json_encode($arr_grade);
        }
    }

    /*
     * Show tax Head Details form
     */

    public function showtaxheaddetail($emp_pkey = 0, $tax_heads_fkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
        $this->set('tax_heads_fkey', $tax_heads_fkey);
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->TaxHead->useDbConfig = $this->Session->read('ds');
        $tax_head = Set::extract('/TaxHead/.', $this->TaxHead->find("first", array('conditions' => array('tax_heads_pkey' => $tax_heads_fkey))));
        $tax_head_name = isset($tax_head[0]['tax_name']) ? $tax_head[0]['tax_name'] : 'Details';
        $this->set('tax_head_name', $tax_head_name);

        $arr_taxheaddetails = $this->requestAction("/Taxation/getTaxHeadDetails/$tax_heads_fkey");
        $arr_emptaxtransactions = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$emp_pkey/$tax_heads_fkey");
        $arr_emptaxdocuments = $this->requestAction("/Taxation/loadEmpTaxHeadDocuments/$emp_pkey/$tax_heads_fkey");

        $companycode = $this->Session->read('company_code');

        $this->set('companycode', $companycode);
        $this->set('arr_emptaxdocuments', $arr_emptaxdocuments);
        $this->set('arr_taxheaddetails', $arr_taxheaddetails);
        // debug($arr_emptaxdocuments);
        $this->set('arr_emptaxtransactions', $arr_emptaxtransactions);
        $locked_data = $this->EmployeeTaxTransactions->query("select locked from emp_tax_transactions where emp_fkey =$emp_pkey and tax_heads_fkey= $tax_heads_fkey and tax_heads_details_fkey = 0");
        $locked = isset($locked_data['0']['emp_tax_transactions']['locked']) ? $locked_data['0']['emp_tax_transactions']['locked'] : 'N';
        $this->set('locked', $locked);
    }

    //Ends
    public function loadEmpDetails($emp_pkey = 0)
    {
        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_personal_profile = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            $arr_emp_personal_profile = $arr_emp_personal_profile['EmployeeDetails'];
            $arr_emp_personal_profile['first_name'] .= ($arr_emp_personal_profile['middile_name']) ? " " . $arr_emp_personal_profile['middile_name'] : '';
            $arr_emp_personal_profile['first_name'] .= ($arr_emp_personal_profile['last_name']) ? " " . $arr_emp_personal_profile['last_name'] : '';
            $arr_emp_personal_profile['name_as_per_bank'] = ($arr_emp_personal_profile['name_as_per_bank']) ? $arr_emp_personal_profile['name_as_per_bank'] : $arr_emp_personal_profile['first_name'];
            $arr_emp_personal_profile['name_as_on_aadhaar'] = ($arr_emp_personal_profile['name_as_on_aadhaar']) ? $arr_emp_personal_profile['name_as_on_aadhaar'] : $arr_emp_personal_profile['first_name'];
            $arr_emp_personal_profile['name_as_on_pan'] = ($arr_emp_personal_profile['name_as_on_pan']) ? $arr_emp_personal_profile['name_as_on_pan'] : $arr_emp_personal_profile['first_name'];
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
        } else {
            $this->set('arr_professionalinfo', array());
        }
    }

    public function loadEmpProfDetails($emp_pkey = '')
    {
        $company_code = $this->Session->read('company_code');
        $user_group = $this->Session->read("user_group");
        if ($user_group == 2) {
            $emp_pkey = $emp_pkey; //$sessionObj['emp_fkey'];  
        }

        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
            //Edited by Akshay 12/7/2023
            $arr_end_date = $this->EmployeeProfessionalDetails->query("SELECT contract_end_date FROM contracted_days WHERE emp_fkey = $emp_pkey AND end_date_effective IS NULL");
            $contract_end_date = isset($arr_end_date[0]['contracted_days']['contract_end_date']) ? $arr_end_date[0]['contracted_days']['contract_end_date'] : '';

            if (empty($arr_emp_professional_profile)) {
                $empId = '';
                $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                $arr_emp_professional_profile_last = $this->EmployeeProfessionalDetails->find('first', array('order' => array('emp_proff_pkey' => 'DESC')));
                if (isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])) {
                    $str_emp_id = $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('control_pkey' => $company_key)));
                    $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                    $arr_emp_id = explode($str_company_code, $str_emp_id);
                    if (isset($arr_emp_id[1]) && $arr_emp_id[1] != '') {
                        $emp_id = $str_company_code . ($arr_emp_id[1] + 1);
                    } else {
                        $emp_id = '';
                    }
                } else {
                    //He is the first employee                  
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('control_pkey' => $company_key)));
                    $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                    $emp_id = $str_company_code . "1000";
                }
                $arr_emp_professional_profile['emp_id'] = $emp_id;
                $arr_emp_professional_profile['emp_fkey'] = $emp_pkey;
            } else {
                $arr_emp_professional_profile = $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            }
            if ($contract_end_date != '') {
                $arr_emp_professional_profile['end_date'] = $contract_end_date;
            }

            //Edited by Akshay on 11-10-2023
            if ($company_code == 'DEMO' || $company_code == 'KWMT') {
                $grade = isset($arr_emp_professional_profile['emp_grade']) ? $arr_emp_professional_profile['emp_grade'] : '';

                if ($grade != '') {
                    $arr_category = $this->EmployeeProfessionalDetails->query("SELECT category_fkey FROM grade WHERE grade_pkey = $grade AND status = 1 AND category_fkey != 0");
                }
                $category_pkey = isset($arr_category[0]['grade']['category_fkey']) ? $arr_category[0]['grade']['category_fkey'] : '';

                if ($category_pkey != '') {
                    $arr_emp_professional_profile['emp_category'] = $category_pkey;
                } else {
                    $arr_emp_professional_profile['emp_category'] = '';
                }
            }




            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
        } else {
            $this->set('arr_professionalinfo', array());
        }
    }

    public function Finyear()
    {
        $this->autoRender = false;
    }

    public function promotion($emp_fkey = 0)
    {
        $this->set("emp_pkey", $emp_fkey);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        //Fetch Units for the company
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        //Fetch Units for the company
        $arr_emp_structures = $this->EmployeeDetails->query("select * from employee_structure_vview where emp_pkey = $emp_fkey ");
        $this->set('arr_emp_structures', $arr_emp_structures);

        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_fkey and end_date_effective is null ");
        $this->set('arr_gross', $arr_gross);

        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_fkey)));
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $arr_empdetails = $this->EmployeeDetails->find("all", array("conditions" => array("status" => 1, "emp_pkey" => $emp_fkey)));
        $arr_emp_personal_profile = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
        foreach ($arr_personalinfokeys as $key) {
            $arr_emp_personal_profile[$key] = '';
        }

        $arr_professionalinfokeys = array();
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
        foreach ($arr_professionalinfokeys as $key) {
            $arr_emp_professional_profile[$key] = '';
        }
        $this->set('arr_personalinfo', $arr_emp_personal_profile);
        $this->set('arr_empdetails', $arr_empdetails);
        //        debug($arr_emp_personal_profile);
        //        debug($arr_empdetails);
    }

    public function getautocompletions_superior()
    {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array('status' => "1");
        if ($arr_request_data['branch'] != '') {

            $searchkey = $arr_request_data['username'];
            $branch = $arr_request_data['branch'];
            $filter_condition[] = "EmployeeDetails.emp_pkey not in ('$branch' )";
            $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%'";
        } else {
            $searchkey = $arr_request_data['username'];
            //            $filter_condition[] = 'first_name LIKE "%' . $searchkey . '%"';
            $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%'";
        }

        //    debug($filter_condition);
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,concat(first_name," ",ifnull(last_name," ")," ",EmployeeDetails.emp_id) as emp_name,branch_code', 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
        $arr_filterresult = array(
            //            array(
            //                'emp_pkey' => '',
            //                'emp_name' => 'ALL',
            //            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] =  array_merge(isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array(), isset($val['0']) ? $val['0'] : array());
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function savepromotions()
    {

        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->Promotion->useDbConfig = $this->Session->read('ds');

        $arr_save = array();
        $result = "";

        $arr_form_data['created_date'] = date('Y-m-d');
        $arr_form_data['promotion_status'] = 'APPLIED';
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');

        $data['id'] = 0;
        $data['emp_fkey'] = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
        $data['modified_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modification_date '] = date("Y-m-d H:i:s");


        $data['type'] = $this->Session->read('login_user_id');


        $save = $this->Promotion->save($arr_form_data);

        $message = "Employee Configuration Details Saved Successfully";
        return json_encode(array('success' => TRUE, "result" => $result, 'message' => $message));
    }

    public function approvepromotion($emp_fkey = 0)
    {


        $this->set("emp_pkey", $emp_fkey);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select * from promotions where status = 1 and approved_status = 'N' and emp_fkey = '' ");
        $this->set('arr_holidays', $arr_holidays);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        //Fetch Units for the company
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        //Fetch Units for the company
        $arr_emp_structures = $this->EmployeeDetails->query("select * from employee_structure_vview where emp_pkey = $emp_fkey ");
        $this->set('arr_emp_structures', $arr_emp_structures);

        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_fkey and end_date_effective is null ");
        $this->set('arr_gross', $arr_gross);

        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_fkey)));
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $arr_empdetails = $this->EmployeeDetails->find("all", array("conditions" => array("status" => 1, "emp_pkey" => $emp_fkey)));
        $arr_emp_personal_profile = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
        foreach ($arr_personalinfokeys as $key) {
            $arr_emp_personal_profile[$key] = '';
        }

        $arr_professionalinfokeys = array();
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
        foreach ($arr_professionalinfokeys as $key) {
            $arr_emp_professional_profile[$key] = '';
        }
        $this->set('arr_personalinfo', $arr_emp_personal_profile);
        $this->set('arr_empdetails', $arr_empdetails);
        //        debug($arr_emp_personal_profile);
        //        debug($arr_empdetails);
    }

    public function empprofdetails($emp_pkey = 0)
    {
        $this->layout = null;
        $emp_pkey = 1;

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch employee professional details if in edit mode
        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
            $arr_emp_professional_profile = $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            $this->set('arr_emp_professional_profile', $arr_emp_professional_profile);
            $this->set('emp_id', $arr_emp_professional_profile['emp_id']);
            $emp_proff_pkey = $arr_emp_professional_profile['emp_proff_pkey'];
        } else {
            $emp_proff_pkey = 0;
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile_last = $this->EmployeeProfessionalDetails->find('last');
            if (isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])) {
                $str_emp_id = $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                $company_key = $this->session->read('company_key');
                $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('company_pkey' => $company_key)));
                $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                $arr_emp_id = explode($str_company_code, $str_emp_id);
                if (isset($arr_emp_id[1]) && $arr_emp_id[1] != '') {
                    $emp_id = $str_company_code . ($arr_emp_id[1] + 1);
                } else {
                    $emp_id = '';
                }
            } else {
                $emp_id = '';
            }
            $this->set('emp_id', $arr_emp_professional_profile_last['emp_id']);
        }
        $this->set('emp_proff_pkey', $emp_proff_pkey);
    }

    public function emptaxationdetails($emp_pkey = 0) {}

    public function uploadProfileImage($files)
    {
        // $target_dir = ASSETSPATH . "uploads/Registrations/";
        //$target_file = $target_dir . basename($files["avatarfile"]["name"]);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($files['avatarfile']['tmp_name']),
            array(
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf'
                //edited by megha on 6/6/19 removed xsl and zip
                // 'xlxs' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                // 'docx' => 'application/zip',
                //end
            ),
            true
        )) {
            $resp['error'] = 'Invalid file format.';
        }

        $filename = sprintf('img/avatar/%s.%s', sha1_file($files['avatarfile']['tmp_name']), $ext);

        $uploadOk = 1;
        $pic = "";
        $msg = "";

        // check if image is a
        $check = getimagesize($files["avatarfile"]["tmp_name"]);
        if ($check !== false) {
            $msg = "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            $msg = "Uploaded image is not an image.";
            $uploadOk = 0;
        }

        // check if the image size is too large
        if ($files["avatarfile"]["size"] > 500000) {
            $msg = "Sorry, your image is too large.";
            $uploadOk = 0;
        }
        // an actual image


        //$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        $file = $files["avatarfile"]['tmp_name'];
        list($width, $height) = getimagesize($file);

        // if($width != "450" || $height != "450") {
        //     $msg = "Error : Profile image size must be 450 x 450 pixels.";
        //     $uploadOk = 0;
        // }

        if ($uploadOk == 0) {
            // $msg = "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($files["avatarfile"]["tmp_name"], $filename)) {
                $pic = basename($files["avatarfile"]["name"]);
                $uploadOk = 1;
                $msg = "your file is uploaded.";
            } else {
                $msg = "Sorry, your image is not uploaded.";
                // return false;
                $uploadOk = 0;
            }
        }
        return array("success" => $uploadOk, "msg" => $msg, "image" => $filename);
    }

    public function saveemployeesetupnew()
    {
        $this->autoRender = FALSE;
        $company_code = $this->Session->read('company_code');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);exit;
        //Edited by Akshay on 13-10-2023
        $company_code = $this->Session->read('company_code');
        $emp_type = isset($arr_form_data['emp_type']) ? $arr_form_data['emp_type'] : '';
        //edited by sinsiya on 14-06-2024 removed the company code demo
        if ($company_code == 'KWMT') {
            if ($emp_type == 'Permanent') {
                $arr_form_data['emp_grade'] = isset($arr_form_data['emp_grade2']) ? $arr_form_data['emp_grade2'] : '';
            } else {
                $arr_form_data['emp_grade'] = isset($arr_form_data['emp_grade2']) ? $arr_form_data['emp_grade2'] : '';
            }
        }
        $model = $arr_form_data['model'];
        $pkey = 0;
        $message = '';

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        //$pkey = isset($arr_form_data['emp_pkey']) ? $arr_form_data['emp_pkey'] : 0;
        $pkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;

        $increment = $arr_form_data['attr2'];
        //Edited by Akshay
        $contract_status = $this->EmployeeProfessionalDetails->query("SELECT emp_type FROM emp_proff WHERE emp_fkey = '$pkey'");
        $emp_type_char = isset($contract_status[0]['emp_proff']['emp_type']) ? $contract_status[0]['emp_proff']['emp_type'] : '';

        if ($pkey == 0) {
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        } else {
            $arr_form_data['modified_by'] = $this->Session->read('login_user_id');
            $arr_form_data['modified_date'] = date("Y-m-d H:i:s");
        }
        $date = strtotime("+$increment day", strtotime($arr_form_data['joining_date']));
        $arr_form_data['attr3'] = date("Y-m-d", $date);

        $arr_form_data['company_code'] = $this->Session->read('company_code');

        //international worker and physical handicap value, if unselect the checkbox.//Checkbox operations by ARUL P DAS on 9/5/2021
        $arr_form_data['international_worker'] = isset($arr_form_data['international_worker']) ? $arr_form_data['international_worker'] : 'N';
        $arr_form_data['physical_handicap'] = isset($arr_form_data['physical_handicap']) ? $arr_form_data['physical_handicap'] : 'N';

        $arr_form_data['locomotive'] = isset($arr_form_data['locomotive']) ? $arr_form_data['locomotive'] : 'N';
        $arr_form_data['hearing'] = isset($arr_form_data['hearing']) ? $arr_form_data['hearing'] : 'N';
        $arr_form_data['visual'] = isset($arr_form_data['visual']) ? $arr_form_data['visual'] : 'N';
        $emp_grade = isset($arr_form_data['emp_grade']) ? $arr_form_data['emp_grade'] : '';
        $arr_form_data['branch_name'] = isset($arr_form_data['branch_name']) ? $arr_form_data['branch_name'] : '';
        if ($emp_grade) {
            //edited by sinsiya 04-06-2024 to realocate the employee from grade to allocate the new grade.
            $conditions['emp_fkey'] = $pkey;
            $conditions1['emp_fkey'] = $pkey;
            $conditions1['type'] = 'GRADE';
            $curr_user_id = $this->Session->read('login_user_id');
            $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $conditions1);
            $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.emp_grade' => "0"), $conditions);
            $message = 'Professional Details Saved Successfully';
        }
        //Edited by Akshay on 11-10-2023  removed the company code demo
        if ($company_code = 'DEMO' || $company_code == 'KWMT') {
            $emp_category = isset($arr_form_data['emp_category']) ? $arr_form_data['emp_category'] : '';
        }

        $arr_form_data['last_name'] = ''; // This field is combined with first_name.
        $arr_form_data['middile_name'] = ''; // This field is combined with first_name.
        $arr_form_data['name_as_per_bank'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021
        $arr_form_data['name_as_on_aadhaar'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021
        $arr_form_data['name_as_on_pan'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021
        $arr_user_cred = array();
        $arr_user_cred['avatar'] = isset($arr_form_data['avatarimg']) ? $arr_form_data['avatarimg'] : 'img/placeholdermen.jpeg';
        $arr_form_data['branch_code'] = $branch_code = $arr_form_data['emp_branch']; //Edited by Akshay on 29-8-2024
        $arr_form_data['status'] = 1;
        $arr_form_data['eps'] = isset($arr_form_data['eps']) ? $arr_form_data['eps'] : 'N';
        //edited by megha on 14-06-2025
        $arr_form_data['lwf_code'] = isset($arr_form_data['lwf_reg_num'])?$arr_form_data['lwf_reg_num']:'';
         $company_code = $this->Session->read('company_code');
        //edited by athira on 07-07-2025
        if ($company_code=='DEMO' || $company_code =='SRTS' || $company_code == 'GLET'){
        $arr_form_data['emp_us_company_id']=$arr_form_data['id_us'];
        $arr_form_data['emp_us_name']=$arr_form_data['name_us'];
        }
        //end
        //Save to employee details
        try {
            $arr_emp_details = $this->EmployeeDetails->query("SELECT CONCAT(COALESCE(`first_name`),COALESCE(`middile_name`),COALESCE(`last_name`)) as EmpName, `company_code`, `branch_code` from `emp_details` where emp_details.emp_pkey = '$pkey'"); //Edited by Akshay on 14-10-2024
            $new_name = isset($arr_emp_details[0][0]['EmpName']) ? $arr_emp_details[0][0]['EmpName'] : '';
            $company_code = isset($arr_emp_details[0]['emp_details']['company_code']) ? $arr_emp_details[0]['emp_details']['company_code'] : '';

            //Edited by Akshay on 29-11-2023
            //Edited by Ashin on 17-02-2024
            $arr_id_duplicate = array();
            $id_card = isset($arr_form_data['id_card']) ?  $arr_form_data['id_card'] : '';
            $lwf_code = isset($arr_form_data['lwf_code']) ?  trim($arr_form_data['lwf_code']) : '';

            if ($id_card != '' && $pkey == 0) {
                $arr_id_duplicate = $this->EmployeeDetails->query("SELECT ed.emp_pkey FROM emp_details ed WHERE ed.id_card = '$id_card' AND ed.status = 1");
            } elseif ($id_card != '' && $pkey != 0) {
                $arr_id_duplicate = $this->EmployeeDetails->query("SELECT ed.emp_pkey FROM emp_details ed WHERE ed.id_card = '$id_card' AND ed.emp_pkey != $pkey AND ed.status = 1");
            }

            // Check for duplicate lwf code
            $arr_lwf_duplicate = array();
            if ($lwf_code != '') {
                $arr_lwf_duplicate = $this->EmployeeDetails->query("SELECT ed.emp_pkey FROM emp_details ed WHERE ed.lwf_code = '$lwf_code' AND ed.status = 1");
            }

            if (empty($arr_id_duplicate)) {
                if (empty($arr_lwf_duplicate)) {
                    // Save the form data
                    $result = $this->EmployeeDetails->save($arr_form_data);
                } else {
                    //added by megha on 24_06_2025
                     $result = $this->EmployeeDetails->save($arr_form_data);
                    $result = array(
                        'status' => 'error',
                        'message' => 'Duplicate id card found.'
                    );
                }
            } else {
                $message = 'Duplicate id card or lwf code found.';
                return json_encode(array('success' => FALSE, 'error' => 'Duplicate id card or lwf code found', 'pkey' => $pkey, 'message' => $message));
            }
            //$arr_form_data['attr1'] = $cur_emp_key = $this->Session->read("emp_fkey");
            $cur_emp_key = $this->Session->read("emp_fkey");
            if (!empty($result)) {
                $message = 'Personal Details Saved Successfully';
                //Edited by Akshay on 6-4-2024
                if ($pkey != 0) {
                    date_default_timezone_set('Asia/Kolkata');
                    $current_date = date('Y-m-d');
                    $month  = date('m', strtotime($current_date));
                    $year  = date('Y', strtotime($current_date));
                    $month1 = $year . '-' . $month . '-01';
                    $month_year = date('Y-m', strtotime($current_date));
                    $att_startdate = $this->EmployeeProfessionalDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
                    $att_enddate = $this->EmployeeProfessionalDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
                    $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
                    $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];

                    if (($current_date >= $att_startdate1)) {
                        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
                        try {
                            $this->EmployeeProfessionalDetails->query('SELECT @record_count := 0;');
                            $arr_attendance_register = $this->EmployeeProfessionalDetails->query("CALL delete_an_count_records_fn('$company_code' , $pkey, $yearmonth, @record_count)");
                            $arr_count = $this->EmployeeProfessionalDetails->query('SELECT @record_count AS record_count; ');
                        } catch (Exception $e) {
                            debug($e);
                        }
                        $count = $arr_count[0][0]['record_count'];
                    } else if (($current_date <= $att_enddate1)) {
                        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
                        $this->EmployeeProfessionalDetails->query('SELECT @record_count := 0;');
                        $arr_attendance_register = $this->EmployeeProfessionalDetails->query("CALL delete_an_count_records_fn('$company_code' , $pkey, $yearmonth, @record_count)");
                        $arr_count = $this->EmployeeProfessionalDetails->query('SELECT @record_count AS record_count; ');
                        $count = $arr_count[0][0]['record_count'];
                        $yearmonth1 = date('Y-m-d', strtotime($current_date . ' + 1 months'));
                        $this->EmployeeProfessionalDetails->query('SELECT @record_count := 0;');
                        $arr_attendance_register1 = $this->EmployeeProfessionalDetails->query("CALL delete_an_count_records_fn('$company_code' , $pkey, $yearmonth1, @record_count)");
                        $arr_count1 = $this->EmployeeProfessionalDetails->query('SELECT @record_count AS record_count; ');
                        $count = $arr_count[0][0]['record_count'] + $arr_count1[0][0]['record_count'];
                    } else {
                        $count = 0;
                    }


                    if ($count > 0) { //On confirmation
                        $change_branch = true;
                    } else {
                        $change_branch = false;
                    }

                    //Edited by Akshay on 3-5-2024
                    $arr_approved_attendance = $this->EmployeeProfessionalDetails->query("SELECT count(*) as count, ar.branch_code FROM attendance_register ar
                                                                                                                            LEFT JOIN payroll_master pm ON pm.emp_fkey = ar.emp_fkey AND pm.month_year = ar.month_year
                                                                                                                            WHERE ar.emp_fkey = '$pkey'
                                                                                                                                AND ar.isdelete = 'N'
                                                                                                                                AND ar.branch_code != '$branch_code'
                                                                                                                                AND (pm.action IS NULL OR pm.action NOT IN ('Processed', 'Approved'));");

                    if ($arr_approved_attendance[0][0]['count'] > 0) {
                        $approved_count = true;
                        //Edited by Akshay on 14-10-2024
                        $prev_branch = isset($arr_approved_attendance[0]['ar']['branch_code']) ? $arr_approved_attendance[0]['ar']['branch_code'] : '';
                        $arr_branch_name = $this->EmployeeProfessionalDetails->query("SELECT branch_name FROM branches WHERE branch_code = '$prev_branch' 
                                                                                        AND status = 1");
                        $prev_branch = isset($arr_branch_name[0]['branches']['branch_name']) ? $arr_branch_name[0]['branches']['branch_name'] : '';
                        //End
                    } else {
                        $approved_count = false;
                    }
                }
                //Ended
                if ($pkey == 0) {
                    //Adding
                    $pkey = $this->EmployeeDetails->getLastInsertID();

                    $this->addToNotice($arr_form_data['notice_days'], $pkey);

                    $company_key = $this->Session->read('company_key');
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                    $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                    if ($punch_type && $punch_type == 'device') {
                        //Device available, so generate emp id concatenate with device id and emp id from device
                        $arr_user_cred = array();
                        if ($pkey > 0) {

                            $arr_form_data['emp_fkey'] = $pkey;

                            try {
                                //Save to employee proffessionals table
                                $user_group = $this->Session->read("user_group");
                                $created_by = $this->Session->read('login_user_id');
                                //if (isset($user_group) && $user_group == 2) {
                                $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('GRADE','$pkey','$emp_grade','$created_by') ");
                                //}

                                $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                                //Insert user credentials  
                                //Get company_code 
                                $str_company_code = $this->Session->read('company_code');

                                $emp_username = ''; //No device details here on manually entering emp data

                                if (isset($_FILES['avatarfile']['tmp_name']) && $_FILES['avatarfile']['tmp_name'] != '') {
                                    $imageUpload = $this->uploadProfileImage($_FILES);
                                    if ($imageUpload['success'] != 0) {
                                        $arr_user_cred['avatar'] = $imageUpload['image'];
                                    } else {
                                        //$this->restsave($pkey);
                                        //  $arr_user_cred['avatar'] == ''; edited by sinisya on 18-12-2024
                                        $message = 'Image Upload Failed: ' . $imageUpload['msg'];
                                        //  return json_encode(array('success' => FALSE, 'error' => 'Image Upload Failed', 'pkey' => $pkey, 'message' => $message));
                                    }
                                }

                                if ($arr_user_cred['avatar'] == '') {
                                    $arr_user_cred['avatar'] = $arr_form_data['avatarimg'];
                                }
                                $arr_user_cred['user_pkey'] = 0;
                                $arr_user_cred['emp_fkey'] = $pkey;
                                $arr_user_cred['company_code'] = $str_company_code;
                                $arr_user_cred['user_id'] = $emp_username;
                                $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                                $arr_user_cred['access_allowed'] = 'n';
                                $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                                $arr_user_cred['last_name'] = ''; // Removed Field. by Arul P Das on 20-6-21
                                $arr_user_cred['middle_name'] = ''; // Removed Field. by Arul P Das on 20-6-21
                                $arr_user_cred['name_as_per_bank'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021
                                $arr_user_cred['name_as_on_aadhaar'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021
                                $arr_user_cred['name_as_on_pan'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021


                                $arr_user_cred['email'] = $arr_form_data['email'];
                                $arr_user_cred['phone'] = $arr_form_data['mobile_no'];
                                try {
                                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                    $result = $this->UserCredentials->save($arr_user_cred);
                                    //  debug($result); exit;
                                    if (isset($arr_form_data['nominee'])) {
                                        $this->Family->useDbConfig = $this->Session->read('ds');
                                        $arr_form_datas = array();
                                        $arr_form_datas['emp_family_pkey'] = isset($arr_form_data['emp_family_pkey']) ? $arr_form_data['emp_family_pkey'] : '';
                                        $arr_form_datas['emp_fkey'] = $pkey;
                                        $arr_form_datas['name'] = $arr_form_data['nominee'];
                                        $arr_form_datas['relation'] = $arr_form_data['relation'];
                                        $arr_form_datas['DOB'] = $arr_form_data['date_of_birth_nominee'];
                                        $arr_form_datas['blood_group'] = $arr_form_data['blood_nominee'];
                                        $arr_form_datas['is_nominee'] = "Y";
                                        $arr_form_datas['created_by'] = $this->Session->read('user_name');
                                        $this->Family->save($arr_form_datas);
                                    }
                                } catch (Exception $ex) {
                                    $this->restsave($pkey);
                                    $message = 'UserCredentials Details Saving Failed';
                                    if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                                        $message = 'User Already Exists With the same COMPANYID';
                                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                                }
                            } catch (Exception $ex) {
                                $this->restsave($pkey);
                                $message = 'Proffesional Details Saving Failed';
                                if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                                    $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                                return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                            }
                        }
                    } else {
                        //Manually generate emp id for companies without device 
                        $arr_user_cred = array();
                        $arr_user_cred['avatar'] = isset($arr_form_data['avatarimg']) ? $arr_form_data['avatarimg'] : 'img/placeholdermen.jpeg';
                        $arr_form_data['emp_fkey'] = $pkey;
                        //Save to employee proffessionals table
                        try {
                            $user_group = $this->Session->read("user_group");
                            if (isset($user_group) && $user_group == 2) {
                                $arr_form_data['attr1'] = $cur_emp_key = $this->Session->read("emp_fkey");
                                $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('HIERARCHY','$pkey','$cur_emp_key','$cur_emp_key') ");
                            }

                            $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                            //Edited by Akshay on 12/7/2023
                            if ($arr_form_data['emp_type'] == 'Contract') {
                                $start_date = $arr_form_data['joining_date'];
                                $end_date = $arr_form_data['end_date'];
                                date_default_timezone_set('Asia/Kolkata'); // Set the timezone to Indian Standard Time (IST)
                                $current_time = date("Y-m-d H:i:s");
                                $cur_user = $this->Session->read('user_name');
                                $start_date_eff = date("Y-m-d");
                                $insert_contract_date = $this->EmployeeProfessionalDetails->query("INSERT INTO contracted_days (emp_fkey,contract_start_date,contract_end_date, created_by, created_time, start_date_effective) VALUES('$pkey','$start_date','$end_date','$cur_user','$current_time','$start_date_eff')");
                            }
                            if ($pkey > 0) {
                                //Insert user credentials  

                                if (isset($_FILES['avatarfile']['tmp_name']) && $_FILES['avatarfile']['tmp_name'] != '') {
                                    $imageUpload = $this->uploadProfileImage($_FILES);
                                    if ($imageUpload['success'] != 0) {
                                        $arr_user_cred['avatar'] = $imageUpload['image'];
                                    } else {
                                        //$this->restsave($pkey); edited by sinisya on 18-12-2024
                                        $message = 'Image Upload Failed: ' . $imageUpload['msg'];
                                        // return json_encode(array('success' => FALSE, 'error' => 'Image Upload Failed', 'pkey' => $pkey, 'message' => $message));
                                    }
                                }

                                $str_company_code = $this->Session->read('company_code');
                                $arr_user_cred['user_pkey'] = 0;
                                $arr_user_cred['emp_fkey'] = $pkey;
                                $arr_user_cred['company_code'] = $str_company_code;
                                $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                                $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                                $arr_user_cred['access_allowed'] = 'n';
                                $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                                $arr_user_cred['last_name'] =  ''; // Removed field. by Arul P Das on 20_6_21
                                $arr_user_cred['middle_name'] =  ''; // Removed field. by Arul P Das on 20_6_21
                                $arr_user_cred['name_as_per_bank'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021
                                $arr_user_cred['name_as_on_aadhaar'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021
                                $arr_user_cred['name_as_on_pan'] = $arr_form_data['first_name']; // Commonly one name takes for every name fields. updated on 25/06/2021								
                                $arr_user_cred['email'] = $arr_form_data['email'];
                                $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                                try {
                                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                    $result = $this->UserCredentials->save($arr_user_cred);
                                    //Call procedure 'Linkemp_deviceanddatabase'
                                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                    $outputParameter = array();
                                    $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                                    $outputParameter[] = isset($arr_form_data['emp_branch']) ? "'" . $arr_form_data['emp_branch'] . "'" : "''";
                                    $outputParameter[] = "''";
                                    try {
                                        $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
                                        $company = strtoupper($this->Session->read('company_code'));
                                        if ($company == 'VSFS') {
                                            $empdeviceid = $this->UserCredentials->query("select SUBSTRING(max(trim(emp_company_id)),3, 10) as deviceid from emp_proff 
                                            where emp_company_id like '%VS%' AND emp_company_id NOT REGEXP 'VSFS' and emp_fkey!=$pkey");
                                            $empdeviceid1 = $empdeviceid['0']['0']['deviceid'];
                                            $empdeviceid1++;
                                            $this->EmployeeProfessionalDetails->query("update emp_proff set emp_company_id = concat('VS',$empdeviceid1) where emp_fkey=$pkey");
                                        }
                                        if (isset($arr_form_data['nominee'])) {
                                            $this->Family->useDbConfig = $this->Session->read('ds');
                                            $arr_form_datas = array();
                                            $arr_form_datas['emp_family_pkey'] = isset($arr_form_data['emp_family_pkey']) ? $arr_form_data['emp_family_pkey'] : '';
                                            $arr_form_datas['emp_fkey'] = $pkey;
                                            $arr_form_datas['name'] = isset($arr_form_data['nominee']) ? $arr_form_data['nominee'] : '';
                                            $arr_form_datas['relation'] = isset($arr_form_data['relation']) ? $arr_form_data['relation'] : '';
                                            $arr_form_datas['DOB'] = isset($arr_form_data['date_of_birth_nominee']) ? $arr_form_data['date_of_birth_nominee'] : '';
                                            //   $arr_form_datas['blood_group'] = $arr_form_data['blood_nominee'];
                                            $arr_form_datas['is_nominee'] = "Y";
                                            $arr_form_datas['created_by'] = $this->Session->read('user_name');
                                            $this->Family->save($arr_form_datas);
                                        }
                                    } catch (Exception $ex) {
                                        $this->restsave($pkey);
                                        $message = 'Link Employee Details Saving Failed';
                                        return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                                    }
                                } catch (Exception $ex) {
                                    $this->restsave($pkey);
                                    $message = 'UserCredentials Details Saving Failed';
                                    if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                                        $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                                }
                            }
                        } catch (Exception $ex) {
                            $this->restsave($pkey);
                            $message = 'Proffessional Details Saving Failed';
                            if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                                $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                            return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                        }
                    }
                } else {

                    $avatar = '';
                    if (isset($_FILES['avatarfile']) && $_FILES['avatarfile']['tmp_name'] != '') {
                        $imageUpload = $this->uploadProfileImage($_FILES);
                        if ($imageUpload['success'] != 0) {
                            $avatar = $imageUpload['image'];
                        } else {
                            //$this->restsave($pkey);
                            $message = 'Image Upload Failed: ' . $imageUpload['msg'];
                            // return json_encode(array('success' => FALSE, 'error' => 'Image Upload Failed', 'pkey' => $pkey, 'message' => $message));
                        }
                    }
                    $created_by = $this->Session->read('login_user_id');
                    $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('GRADE','$pkey','$emp_grade','$created_by') ");

                    if (isset($arr_form_data['nominee'])) {
                        $this->Family->useDbConfig = $this->Session->read('ds');
                        $arr_form_datas = array();
                        $arr_form_datas['emp_family_pkey'] = isset($arr_form_data['emp_family_pkey']) ? $arr_form_data['emp_family_pkey'] : '';
                        $arr_form_datas['emp_fkey'] = $pkey;
                        $arr_form_datas['name'] = $arr_form_data['nominee'];
                        $arr_form_datas['relation'] = $arr_form_data['relation'];
                        $arr_form_datas['DOB'] = $arr_form_data['date_of_birth_nominee'];
                        $arr_form_datas['blood_group'] = $arr_form_data['blood_nominee'];
                        $arr_form_datas['is_nominee'] = "Y";
                        $arr_form_datas['created_by'] = $this->Session->read('user_name');
                        $this->Family->save($arr_form_datas);
                    }
                    try {
                        //debug($arr_form_data);
                        $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                        //Edited by Akshay on 12/7/2023
                        if ($arr_form_data['emp_type'] != 'Contract' && $emp_type_char == 'Contract') {
                            $start_date = $arr_form_data['joining_date'];
                            $end_date = $arr_form_data['end_date'];
                            date_default_timezone_set('Asia/Kolkata'); // Set the timezone to Indian Standard Time (IST)
                            $current_time = date("Y-m-d H:i:s");
                            $end_date_eff = $arr_form_data['modified_date'];
                            $modified_by = $arr_form_data['modified_by'];
                            $update_contract_date = $this->EmployeeProfessionalDetails->query("UPDATE contracted_days SET end_date_effective = '$end_date_eff', modified_by = '$modified_by', modified_time = '$current_time', contract_start_date = NULL, contract_end_date = NULL WHERE emp_fkey = $pkey");
                        } else
                        if ($arr_form_data['emp_type'] == 'Contract' && $emp_type_char != 'Contract') {
                            $start_date = $arr_form_data['joining_date'];
                            $end_date = $arr_form_data['end_date'];
                            date_default_timezone_set('Asia/Kolkata'); // Set the timezone to Indian Standard Time (IST)
                            $current_time = date("Y-m-d H:i:s");
                            $start_date_eff = $arr_form_data['modified_date'];
                            $modified_by = $arr_form_data['modified_by'];
                            $cur_user = $this->Session->read('user_name');
                            $insert_contract_date = $this->EmployeeProfessionalDetails->query("INSERT INTO contracted_days (emp_fkey,contract_start_date,contract_end_date, created_by,created_time) VALUES('$pkey','$start_date','$end_date','$cur_user','$current_time')");
                        } else
                        if ($arr_form_data['emp_type'] == 'Contract') {
                            $db_contract_date = $this->EmployeeProfessionalDetails->query("SELECT contract_start_date, contract_end_date FROM contracted_days WHERE end_date_effective IS NULL AND emp_fkey = '$pkey'");
                            $db_start_date = isset($db_contract_date[0]['contracted_days']['contract_start_date']) ? $db_contract_date[0]['contracted_days']['contract_start_date'] : '';
                            $db_end_date = isset($db_contract_date[0]['contracted_days']['contract_end_date']) ? $db_contract_date[0]['contracted_days']['contract_end_date'] : '';
                            $start_date = $arr_form_data['joining_date'];
                            $end_date = $arr_form_data['end_date'];
                            $cur_user = $this->Session->read('user_name');
                            if (count($db_contract_date) == 0) {
                                $insert_contract_date = $this->EmployeeProfessionalDetails->query("INSERT INTO contracted_days (emp_fkey,contract_start_date,contract_end_date, created_by,created_time) VALUES('$pkey','$start_date','$end_date','$cur_user','$current_time')");
                            } else {
                                if (trim($db_start_date) != trim($start_date) || trim($db_end_date) != trim($end_date)) {
                                    date_default_timezone_set('Asia/Kolkata'); // Set the timezone to Indian Standard Time (IST)
                                    $current_time = date("Y-m-d H:i:s");
                                    $start_date_eff = $arr_form_data['modified_date'];
                                    $modified_by = $arr_form_data['modified_by'];
                                    $update_contract_date = $this->EmployeeProfessionalDetails->query("UPDATE contracted_days SET start_date_effective = '$start_date_eff', modified_by = '$modified_by', modified_time = '$current_time', contract_start_date = '$start_date', contract_end_date = '$end_date'  WHERE emp_fkey = $pkey AND end_date_effective IS NULL");
                                }
                            }
                        }
                    } catch (Exception $ex) {
                        //edited by megha
                        $message = 'Personal Details Saved failed';
                        return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                    }
                    $this->addToNotice($arr_form_data['notice_days'], $pkey);
                    //Update user credentials
                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                    if ($avatar === '') {
                        $avatar =  $arr_form_data['avatarimg'];
                    }

                    try {
                        $this->UserCredentials->updateAll(
                            array(
                                'UserCredentials.first_name' => "'" . $arr_form_data['first_name'] . "'",
                                'UserCredentials.last_name' => "'" . $arr_form_data['last_name'] . "'",
                                'UserCredentials.middle_name' => "'" . $arr_form_data['middile_name'] . "'",
                                'UserCredentials.email' => "'" . $arr_form_data['email'] . "'",
                                'UserCredentials.avatar' => "'" . $avatar . "'",
                                'UserCredentials.phone' => "'" . $arr_form_data['mobile_no'] . "'",
                            ),
                            array('UserCredentials.emp_fkey' => $pkey)
                        );
                    } catch (Exception $ex) {
                        $message = 'Updating UserCredentials Failed';
                        return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                    }
                }
            } elseif (empty($arr_id_duplicate)) {
                $message = 'Personal Details Saved failed';
            } else {
                $message = 'Duplicate ID/Aadhaar No. Personal Details Saved failed';
                return json_encode(array('success' => FALSE, 'pkey' => $pkey, 'message' => $message));
            }
        } catch (Exception $ex) {
            $message = 'Personal Details Saving Failed';
            return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
        }
        $emp_companyid = $this->EmployeeProfessionalDetails->query("select emp_company_id,concat(first_name,' ',last_name) as Nme from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_fkey = $pkey ");
        $this->set('emp_companyid', $emp_companyid);
        $company_id = isset($emp_companyid['0']['emp_proff']['emp_company_id']) ? $emp_companyid['0']['emp_proff']['emp_company_id'] : '';
        $name = isset($emp_companyid['0']['0']['Nme']) ? $emp_companyid['0']['0']['Nme'] : '';
        //debug($emp_companyid);
        // return json_encode(array('success' => TRUE, 'error' => '', 'pkey' => $pkey, 'message' => "Employee Added Successfully ", "emp_companyd" => $company_id, "name" => $name));
        //edited by sinsiya on 18-12-2024
        if (isset($imageUpload['success']) && $imageUpload['success'] == 0) {
            //$arr_user_cred['avatar'] = $imageUpload['image'];
            // } else {
            //$this->restsave($pkey);
            $message = 'Image Upload Failed: ' . $imageUpload['msg'];
            return json_encode(array('success' => FALSE, 'error' => 'Image Upload Failed', 'pkey' => $pkey, 'message' => $message));
        }
          //added by megha on 09-09-2025 export changes into myprofile
        $employeedetailsResponses = $this->savedata($pkey);
        //Edited by Akshay on 4-5-2024
        if (isset($change_branch) && isset($approved_count) && isset($month_year)) {
            $prev_branch = isset($prev_branch) ? $prev_branch : ''; //Edited by Akshay on 2-12-2024
            return json_encode(array('success' => TRUE, 'error' => '', 'pkey' => $pkey, 'message' => "Employee Added Successfully ", "emp_companyd" => $company_id, "name" => $name, "change_branch" => $change_branch, "approved_count" => $approved_count, "month_year" => $month_year, "branch_code" => $branch_code, 'emp_pkey' => $pkey, "prev_branch" => $prev_branch)); // Edited by Akshay on 14-10-2024
        } else {
            return json_encode(array('success' => TRUE, 'error' => '', 'pkey' => $pkey, 'message' => "Employee Added Successfully ", "emp_companyd" => $company_id, "name" => $name));
        }
    }

    public function restsave($emp_pkey = 0)
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        try {
            $this->EmployeeProfessionalDetails->query("delete from emp_details where emp_pkey = $emp_pkey ");
            $this->EmployeeProfessionalDetails->query("delete from emp_proff where emp_fkey = $emp_pkey ");
            $this->EmployeeProfessionalDetails->query("delete from user_credentials where emp_fkey = $emp_pkey ");
            $this->EmployeeProfessionalDetails->query("delete from mypayrol_control_db.emp_device_comp_branch where emp_fkey = $emp_pkey and Company_code = '$company_code'");
            return True;
        } catch (Exception $ex) {
            return False;
        }
    }

    public function saveemployeesetup()
    {
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $model = $arr_form_data['model'];
        switch ($model) {
            case 'EmployeeDetails':
                $pkey = $arr_form_data['emp_pkey'];
                $arr_form_data['company_code'] = $this->Session->read('company_code');
                $message = 'Personal Details Saved Successfully';
                //$empname = $arr_form_data['first_name'] .' '.  $arr_form_data['last_name'];
                //$this->mailsend($empname);

                break;
            case 'EmployeeProfessionalDetails':
                $pkey = $arr_form_data['emp_fkey'];
                $message = 'Professional Details Saved Successfully';
                $increment = $arr_form_data['attr2'];
                $date = strtotime("+$increment day", strtotime($arr_form_data['joining_date']));
                $arr_form_data['attr3'] = date("Y-m-d", $date);

                break;
            default:
                $pkey = 0;
                $message = '';
                break;
        }
        $this->{$model}->useDbConfig = $this->Session->read('ds');
        $result = $this->{$model}->save($arr_form_data);
        if (!empty($result)) {
            if ($pkey == 0) {
                $pkey = $this->{$model}->getLastInsertID();
                if ($model == 'EmployeeDetails') {
                    //$this->sendpasswordemail($pkey);
                    //$sessionObj = $this->Session->read("Auth.User");
                    //$company_key    =   $sessionObj['company_key'];
                    $company_key = $this->Session->read('company_key');
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                    $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                    if ($punch_type == 'device') {
                        //Device available, so generate emp id concatenate with device id and emp id from device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            //Insert user credentials  
                            //Get company_code
                            $str_company_code = $this->Session->read('company_code');

                            $emp_username = ''; //No device details here on manually entering emp data

                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = $emp_username;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                            $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                            $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                            $arr_user_cred['email'] = $arr_form_data['email'];
                            $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $result = $this->UserCredentials->save($arr_user_cred);
                        }
                    } else {
                        //Manually generate emp id for companies without device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            //Insert user credentials  
                            $str_company_code = $this->Session->read('company_code');
                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                            $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                            $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                            $arr_user_cred['email'] = $arr_form_data['email'];
                            $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $result = $this->UserCredentials->save($arr_user_cred);
                        }
                    }
                }
            } else {
                if ($model == 'EmployeeDetails') {
                    //Update user credentials
                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                    $this->UserCredentials->updateAll(
                        array(
                            'UserCredentials.first_name' => "'" . $arr_form_data['first_name'] . "'",
                            'UserCredentials.last_name' => "'" . $arr_form_data['last_name'] . "'",
                            'UserCredentials.middle_name' => "'" . $arr_form_data['middile_name'] . "'",
                            'UserCredentials.email' => "'" . $arr_form_data['email'] . "'",
                            'UserCredentials.phone' => "'" . $arr_form_data['mobile_no'] . "'",
                        ),
                        array('UserCredentials.emp_fkey' => $pkey)
                    );
                }
            }

            if (isset($arr_form_data['emp_proff_pkey']) && ($arr_form_data['emp_proff_pkey'] == 0 || $arr_form_data['emp_proff_pkey'] == '')) {
                //Call procedure 'Linkemp_deviceanddatabase'
                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                $outputParameter = array();
                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                $outputParameter[] = isset($arr_form_data['emp_branch']) ? "'" . $arr_form_data['emp_branch'] . "'" : "''";
                $outputParameter[] = "''";
                $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
            }


            //$this->autoRender = FALSE;





            return json_encode(array('success' => TRUE, 'pkey' => $pkey, 'message' => $message));
        }
    }

    public function addToNotice($shift_id = 0, $emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition3['type'] = 'NOTICEPER';
        $condition3['emp_fkey'] = $emp_pkey;
        //$condition3['policy_id'] = $arr_form_data['leave'];
        $data['id'] = 0;
        $data['emp_fkey'] = $emp_pkey;
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        //$data['creation_date'] = date("Y-m-d");

        try {
            $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $this->Session->read('login_user_id') . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
            $data['type'] = 'NOTICEPER';
            $data['policy_id'] = $shift_id;
            $this->EmployeeConfig->saveAll($data);
            return true;
        } catch (Exception $ex) {
            return json_encode(array('success' => FALSE, "result" => "", 'message' => "Failed to Save leave policy "));
        }
    }

    public function jsons_getemps($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = "and branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        if ($q != null) {
            $q_condition = "and first_name like '%$q%'";
        } else {
            $q_condition = "";
        }
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $emp_condition = "and emp_proff.attr1 = '$emp_pkeys' ";
        } else {
            $emp_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1 $branch_condition $q_condition $emp_condition");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function saveconfigs()
    {

        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        $edit_salary = FALSE;
        $upload_salary = FALSE;
        $result = "";
        $gratuity = isset($arr_form_data['gratuity']) ? $arr_form_data['gratuity'] : '';
        if ($gratuity == '') {
            $gratuity = 0;
        } else {
            $gratuity = $gratuity;
        }
        $emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
        //edited by sinisya 13-04-2024
        $fetch_annualgross = $this->EmployeeConfig->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = '$emp_fkey' and end_date_effective is null ");
        $annualgross = isset($fetch_annualgross['0']['emp_ctc_transaction']['emp_anual_ctc']) ? $fetch_annualgross['0']['emp_ctc_transaction']['emp_anual_ctc'] : 0;

        try {
            $this->EmployeeConfig->query("update emp_proff set emp_mgt_priv = $gratuity where emp_fkey=$emp_fkey ");
            $fetch_proff = $this->EmployeeConfig->query("select * from emp_proff where emp_fkey = '$emp_fkey' ");
        } catch (Exception $ex) {
            return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to get proffessional Details "));
        }

        try {
            $arr_gross = $this->EmployeeConfig->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = '$emp_fkey' and end_date_effective is null ");
        } catch (Exception $ex) {

            return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to get annual ctc "));
        }


        $shift_id = isset($fetch_proff['0']['emp_proff']['day_time_seq']) ? $fetch_proff['0']['emp_proff']['day_time_seq'] : 0;
        $leave_policy = isset($fetch_proff['0']['emp_proff']['LEAVEPOLICY_GROUP_ID']) ? $fetch_proff['0']['emp_proff']['LEAVEPOLICY_GROUP_ID'] : 0;
        $holiday_id = isset($fetch_proff['0']['emp_proff']['HOLIDAY_GROUP_ID']) ? $fetch_proff['0']['emp_proff']['HOLIDAY_GROUP_ID'] : 0;
        $salary_id = isset($fetch_proff['0']['emp_proff']['structure_id']) ? $fetch_proff['0']['emp_proff']['structure_id'] : 0;
        $hierarchy = isset($fetch_proff['0']['emp_proff']['attr1']) ? $fetch_proff['0']['emp_proff']['attr1'] : 0;

        $data['id'] = 0;
        $data['emp_fkey'] = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modified_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modification_date '] = date("Y-m-d H:i:s");


        $data['type'] = $this->Session->read('login_user_id');


        if (isset($arr_form_data['shift']) && $arr_form_data['shift'] != $shift_id) {


            $condition['type'] = 'SHIFT';
            $condition['emp_fkey'] = $arr_form_data['emp_fkey'];

            $data['type'] = 'SHIFT';

            $data['policy_id'] = $arr_form_data['shift'];
            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);

                $this->EmployeeConfig->save($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save shift policy "));
            }
        }


        if (isset($arr_form_data['holidays']) && $arr_form_data['holidays'] != $holiday_id) {

            $condition1['type'] = 'HOLIDAY';
            $condition1['emp_fkey'] = $arr_form_data['emp_fkey'];

            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition1);

                $data['type'] = 'HOLIDAY';
                $data['policy_id'] = $arr_form_data['holidays'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save holiday policy "));
            }
        }
        if (isset($arr_form_data['salary']) && $arr_form_data['salary'] != $salary_id) {

            $condition2['type'] = 'SALARY';
            $condition2['emp_fkey'] = $arr_form_data['emp_fkey'];

            $edit_salary = TRUE;

            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $salary_id = $arr_form_data['salary'];
            $emp = $arr_form_data['emp_fkey'];
            //added by megha salary allocation 1
            $resp = 0;
            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');
            $user_ids = $this->Session->read('login_user_id');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            try {
                $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                //employee condition added by megha on 5/03/2020
                $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                    "all",
                    array(
                        'fields' => 'emp_salary_structure_pkey,head_operator,remarks',
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

                    if (!empty($formula_from_remarks)) {
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

                $resp = 1;
            } catch (Exception $ex) {
                $resp = 0;
            }
            //end salary allocation
            try {
                $user_ids = $this->Session->read('login_user_id');

                //debug($result);
                $data['type'] = 'SALARY';
                $data['policy_id'] = $arr_form_data['salary'];
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition2);

                $this->EmployeeConfig->saveAll($data);
                //added by megha sallary allocation 2
                $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save salary policy "));
            }
        }


        if (isset($arr_form_data['leave']) && $arr_form_data['leave'] != $leave_policy) {

            $condition3['type'] = 'LEAVE';
            $condition3['emp_fkey'] = $arr_form_data['emp_fkey'];
            //$condition3['policy_id'] = $arr_form_data['leave'];

            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
                $data['type'] = 'LEAVE';
                $data['policy_id'] = $arr_form_data['leave'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save leave policy "));
            }
        }


        if (isset($arr_form_data['hierarch']) && $arr_form_data['hierarch'] != $hierarchy) {

            $condition4s['type'] = 'HIERARCHY';
            $condition4s['emp_fkey'] = $arr_form_data['emp_fkey'];
            //$condition4s['policy_id'] = $arr_form_data['hierarch'];

            $data['type'] = 'HIERARCHY';

            try {
                $this->EmployeeConfig->updateAll(
                    array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0),
                    $condition4s
                );

                $data['policy_id'] = $arr_form_data['hierarch'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save hierarchy policy "));
            }
        }


        if (isset($arr_form_data['annual_gross']) && $arr_form_data['annual_gross'] != '') {

            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $arr_form_data = $this->request->data;
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');

            $upload_salary = TRUE;

            $arr_form_data['emp_anual_ctc'] = $arr_form_data['annual_gross'];
            try {
                //edited by sinsiya 13-04-2024
                if (isset($annualgross) && $annualgross != $arr_form_data['annual_gross']) {
                    $arr_form_data['start_date_effective'] = date("Y-m-1");
                    $result = $this->EmployeeCTC->save($arr_form_data);
                }
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Upload Salary "));
            }
        }


        if ($edit_salary == TRUE && $upload_salary == FALSE) {
            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');
            $salary_id = $arr_form_data['salary'];
            $user_ids = $this->Session->read('login_user_id');
            $emp = $arr_form_data['emp_fkey'];
            try {
                $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                $result = isset($proc['0']['0']['function']) ? $proc['0']['0']['function'] : '';
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Create Salary "));
            }
        }

        $message = "Employee Configuration Details Saved Successfully";
        return json_encode(array('success' => TRUE, "result" => $result, 'message' => $message));
    }

    public function downloadempdataformat()
    {
        $this->autoRender = FALSE;

        //$auth_user  =   $this->Session->read("Auth.User");
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? $str_company_code . ".xlsx" : "employeedataformat_" . strtotime() . ".xlsx";

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        // create a file pointer connected to the output stream
        //$output = fopen('php://output', 'w');

        App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
        $empcsvdata = new EmployeeCSVData();
        $emp_details_schema = $empcsvdata->getFieldHeadings('NewEmployeeDetails');
        $emp_prof_schema = $empcsvdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_schema = array_merge($emp_details_schema, $emp_prof_schema);
        //fputcsv($output, $emp_schema);

        App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empcsvdata = new EmployeeCSVData();
        $emp_details_schema = $empcsvdata->getFieldHeadings('NewEmployeeDetails');
        $emp_prof_schema = $empcsvdata->getFieldHeadings('NewEmployeeProfessionalDetails');
        $emp_schema = array_merge($emp_details_schema, $emp_prof_schema);
        $objPHPExcel = new PHPExcel();
        $objWorkSheet = $objPHPExcel->createSheet(2);

        $objWorkSheet->getStyle('H4')->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        //$objWorkSheet->getStyle('C3')->getAlignment()->setWrapText(true);
        $cll = 3;
        $clld = 3;
        $clldd = 3;
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $leaves = $this->FinancialYear->query("select * from designation where status=1");
        $leavesdept = $this->FinancialYear->query("select * from department where status=1");
        $leavesgrade = $this->FinancialYear->query("select * from grade where status=1");
        //Setting Up Bold cell
        $objWorkSheet->getStyle('A1')->getFont()->setBold(true);

        $objWorkSheet->getStyle('A2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('A20')->getFont()->setBold(true);
        $objWorkSheet->getStyle('B2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('C2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('D2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('E2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('F2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('G2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('H2')->getFont()->setBold(true);

        //$objWorkSheet->getStyle('H3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('I3')->getFont()->setBold(true);

        $objWorkSheet->getStyle('J2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('K2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('I2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('I3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('J3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('K3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('L3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('M2')->getFont()->setBold(true); //Grade
        $objWorkSheet->getStyle('M3')->getFont()->setBold(true); //Grade Code
        $objWorkSheet->getStyle('N3')->getFont()->setBold(true); //Grade Name
        //Setting the width for all cells
        $objWorkSheet->getColumnDimension('B')->setWidth(20);
        $objWorkSheet->getColumnDimension('D')->setWidth(10);
        $objWorkSheet->getColumnDimension('E')->setWidth(24);
        $objWorkSheet->getColumnDimension('F')->setWidth(24);
        $objWorkSheet->getColumnDimension('G')->setWidth(20);
        $objWorkSheet->getColumnDimension('H')->setWidth(20);
        $objWorkSheet->getColumnDimension('I')->setWidth(20);
        $objWorkSheet->getColumnDimension('J')->setWidth(20);
        $objWorkSheet->getColumnDimension('K')->setWidth(20);
        $objWorkSheet->getColumnDimension('L')->setWidth(20);
        $objWorkSheet->getColumnDimension('M')->setWidth(20);
        $objWorkSheet->getColumnDimension('N')->setWidth(20);

        //Rendering Designation , Department Cell
        foreach ($leaves as $lev) {
            $cll = $cll + 1;
            $objWorkSheet->setCellValueExplicit('I' . $cll, $lev['designation']['desig_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objWorkSheet->setCellValue('J' . $cll, $lev['designation']['desig_name']);
        }
        foreach ($leavesdept as $lev) {
            $clld = $clld + 1;
            $objWorkSheet->setCellValueExplicit('K' . $clld, $lev['department']['dept_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objWorkSheet->setCellValue('L' . $clld, $lev['department']['dept_name']);
        }
        foreach ($leavesgrade as $lev) {
            $clldd = $clldd + 1;
            $objWorkSheet->setCellValueExplicit('M' . $clldd, $lev['grade']['grade_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objWorkSheet->setCellValue('N' . $clldd, $lev['grade']['grade_name']);
        }
        $rowindexhelp = 2;
        $objWorkSheet->setCellValue('A1', 'Help Form');
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $objWorkSheet->getStyle('I2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorkSheet->getStyle('K2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorkSheet->getStyle('M2')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorkSheet->mergeCells("I2:J2");
        $objWorkSheet->mergeCells("K2:L2");
        $objWorkSheet->mergeCells("M2:N2");
        $objWorkSheet->mergeCells("A10:F10");
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objWorkSheet->setCellValue('A2', 'First Name , Last Name');
        $objWorkSheet->setCellValue('A3', 'First Name and Last Name');
        $objWorkSheet->setCellValue('B2', 'Gender');
        $objWorkSheet->setCellValue('B3', 'Male');
        $objWorkSheet->setCellValue('B4', 'Female');
        $objWorkSheet->setCellValue('C2', 'Email');
        $objWorkSheet->setCellValue('C3', 'Email id should be in the correct format');
        $objWorkSheet->setCellValue('D2', 'Marital Status');
        $objWorkSheet->setCellValue('D3', 'Single');
        $objWorkSheet->setCellValue('D4', 'Married');
        $objWorkSheet->setCellValue('E2', 'Date of Birth , Joining Date');
        $objWorkSheet->setCellValue('E3', 'Should be in the format of (DD-MM-YYYY)');
        $objWorkSheet->setCellValue('F2', 'Company Employee ID');
        $objWorkSheet->setCellValue('F3', 'Leave If not Have ');
        $objWorkSheet->setCellValue('G2', 'Employee Type');
        $objWorkSheet->setCellValue('G3', 'Permanent');
        $objWorkSheet->setCellValue('G4', 'Probation');
        $objWorkSheet->setCellValue('G5', 'Contract');
        $objWorkSheet->setCellValue('G6', 'Part Time');
        $objWorkSheet->setCellValue('G7', 'Temporary');
        $objWorkSheet->setCellValue('G8', 'Other');
        //        $objWorkSheet->setCellValue('B2', 'Last Name');$objWorkSheet->setCellValue('A1', 'Terms');
        $objWorkSheet->setCellValue('H2', 'ID/AADHAR Card Number');
        $objWorkSheet->setCellValue('H3', 'ID/AADHAR Card Number');

        $objWorkSheet->setCellValue('I2', 'Designation');
        $objWorkSheet->setCellValue('I3', 'Designation Code');
        $objWorkSheet->setCellValue('J3', 'Designation Name');
        $objWorkSheet->setCellValue('K2', 'Department');
        $objWorkSheet->setCellValue('K3', 'Department Code');
        $objWorkSheet->setCellValue('L3', 'Department Name');
        $objWorkSheet->setCellValue('M2', 'Grade');
        $objWorkSheet->setCellValue('M3', 'Grade Code');
        $objWorkSheet->setCellValue('N3', 'Grade Name');
        $objWorkSheet->setCellValue('A20', 'Mandatory Fields :');
        $objWorkSheet->setCellValue('A21', 'First Name, Last Name, Gender, Date of Birth, Joining Date, ID/AADHAR Card Number, Employee Type, Designation, Department');
        //$objWorkSheet->setCellValueExplicit('K1', '0022',PHPExcel_Cell_DataType::TYPE_STRING); To set a cell value as String with leading zero 
        //        $objWorkSheet->setCellValue('B2', 'First Half = 1');
        //        $objWorkSheet->setCellValue('B3', 'Second Half = 2');
        $objWorkSheet->setTitle('Help');
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();

        $Desig_type = array();

        $this->Designation->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->Designation->find("all", array("conditions" => array("status" => 1)));

        foreach ($arr_leavetypes as $arr_leavetypes) {
            $Desig_type[] = $arr_leavetypes['Designation']['desig_code'];
        }
        $leavetype = implode(", ", $Desig_type);

        //Ends

        $objActiveSheet = $objPHPExcel->getActiveSheet();
        $objActiveSheet->getStyle('A1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('B1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('C1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('D1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('E1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('F1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('G1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('H1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('I1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('J1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('K1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('L1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('M1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('N1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('O1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('P1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Q1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('R1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('S1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('T1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('U1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('V1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('W1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('X1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Y1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Z1')->getFont()->setBold(true);
        //added by megha on 1_06_19 column heading bold
        $objActiveSheet->getStyle('AA1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('AB1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('AC1')->getFont()->setBold(true);
        //added by megha on 1_06_19 column heading bold
        $objActiveSheet->getStyle('AD1')->getFont()->setBold(true); //Grade Added by ***ARUL P DAS on 31/1/2020

        $objActiveSheet->getColumnDimension('A')->setWidth(20);
        $objActiveSheet->getColumnDimension('B')->setWidth(10);
        $objActiveSheet->getColumnDimension('C')->setWidth(20);
        $objActiveSheet->getColumnDimension('D')->setWidth(10);
        $objActiveSheet->getColumnDimension('E')->setWidth(10);
        $objActiveSheet->getColumnDimension('F')->setWidth(10);
        $objActiveSheet->getColumnDimension('G')->setWidth(10);
        $objActiveSheet->getColumnDimension('H')->setWidth(10);
        $objActiveSheet->getColumnDimension('I')->setWidth(10);
        $objActiveSheet->getColumnDimension('J')->setWidth(10);
        $objActiveSheet->getColumnDimension('K')->setWidth(10);
        $objActiveSheet->getColumnDimension('L')->setWidth(10);
        $objActiveSheet->getColumnDimension('M')->setWidth(16);
        $objActiveSheet->getColumnDimension('N')->setWidth(10);
        $objActiveSheet->getColumnDimension('O')->setWidth(20);
        $objActiveSheet->getColumnDimension('P')->setWidth(20);
        $objActiveSheet->getColumnDimension('Q')->setWidth(20);
        $objActiveSheet->getColumnDimension('R')->setWidth(20);
        $objActiveSheet->getColumnDimension('S')->setWidth(20);
        $objActiveSheet->getColumnDimension('T')->setWidth(20);
        $objActiveSheet->getColumnDimension('U')->setWidth(20);
        $objActiveSheet->getColumnDimension('V')->setWidth(20);
        $objActiveSheet->getColumnDimension('X')->setWidth(20);
        $objActiveSheet->getColumnDimension('Y')->setWidth(20);
        $objActiveSheet->getColumnDimension('Z')->setWidth(30);
        //added by megha on 1_06_19 column heading bold & set width
        $objActiveSheet->getColumnDimension('AA')->setWidth(20);
        $objActiveSheet->getColumnDimension('AB')->setWidth(20);
        $objActiveSheet->getColumnDimension('AC')->setWidth(20);
        //added by megha on 1_06_19 column heading bold & set width
        $objActiveSheet->getColumnDimension('AD')->setWidth(20); //Grade added by ***ARUL P DAS on 31/1/2020

        $sheet = array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                //$objPHPExcel->getActiveSheet()->setWidth(10);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }

        $objPHPExcel->getActiveSheet()->setTitle('Employee Data');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function mailsend($empname = '')
    {
        //$database = $this->Session->read('ds');
        App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailerAutoload.php'));
        $mail = new PHPMailer;
        $mail->SMTPDebug = 2;                               // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
        $mail->Password = 'welcome123';                           // SMTP password
        $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
        $mail->addAddress('sanjundev@gmail.com', 'Sanjun Dev');     // Add a recipient
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = '' . $database . ' Added a new Employee';
        $mail->Body = 'This is the HTML message body <b> Name:' . $empname . ' </b>';
        //$mail->Subject  = '<hr><h1><strong>HI ! '.$empname.' </strong></h1>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if (!$mail->send()) {
            //echo 'Message could not be sent.';
            //echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            //echo 'Message has been sent';
        }
    }

    public function uploadandsaveempdetails($emp_branch = 0)
    {
        $this->autoRender = FALSE;
        //$authuser   =   $this->Session->read("Auth.User");
        $authuser['company_code'] = $this->Session->read('company_code');
        $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_' . strtotime("now") . '.xlsx' : 'empdata_' . strtotime("now") . '.xlsx';
        $targetpath = getcwd() . "/files/" . $filename;
        $errors = array();
        $duplicate_id = array(); //Edited by Akshay on 29-11-2023
        $message = 'Employee data imported successfully ';
        $intImportedCount = 0;
        if (move_uploaded_file($_FILES['empdata']['tmp_name'][0], $targetpath)) {

            App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

            $objReader = new PHPExcel_Reader_Excel2007();
            $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

            $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
            $lastColumn++;
            $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $arrayempdata = array();
            $mandatory_fields_warning = FALSE;

            if ($highestRowIndex > 1) {

                $arrDuplicateEmpList = array();


                //atleast one employee records found
                $index = 0;
                for ($row = 1; $row <= $highestRowIndex; $row++) {
                    if ($row == 1) {
                        //Get mandatory headings array here
                        $array_mandatory_columns = array();
                        $array_mandatory_column_names = array('First Name', 'Last Name', 'Gender(Male/Female)', 'Date of Birth (DD-MM-YYYY)', 'ID/AADHAR Card Number', 'Joining Date (DD-MM-YYYY)', 'Employee Type', 'Designation CODE', 'Department CODE');
                        for ($col = 'A'; $col != $lastColumn; $col++) {
                            $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                            if (in_array($value, $array_mandatory_column_names)) {
                                array_push($array_mandatory_columns, $col);
                            } else {
                                // debug($value);
                            }
                        }
                    } else {
                        for ($col = 'A'; $col != $lastColumn; $col++) {

                            if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                if ($objPHPExcel->getActiveSheet()->getCell("A" . $row)->getValue() != '') {
                                    $mandatory_fields_warning = true;
                                    $fieldpath = $col . ":" . $row;
                                    //debug($col.":".$row);
                                    break 2;
                                }
                            }
                            $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                            $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                        }
                        $index++;
                    }
                    // $intImportedCount++;  
                }

                if ($mandatory_fields_warning) {
                    //Exit if mandatory fields not entered
                    unlink($targetpath);
                    echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields are entered. Data missing in ' . $fieldpath . ' cell'));
                    exit;
                } else {
                    //Continue with save if mandatory field warning is not there
                    //Save employees and return success
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                    $this->Designation->useDbConfig = $this->Session->read('ds');
                    $this->Departments->useDbConfig = $this->Session->read('ds');
                    $this->Grades->useDbConfig = $this->Session->read('ds');
                    $arr_designations = Set::extract('/Designation/.', $this->Designation->find("all", array("fields" => array("desig_code"), "Conditions" => array("status" => 1))));
                    $desigantions = array();
                    foreach ($arr_designations as $vals) {
                        $desigantions[] = trim($vals['desig_code']); //Edited by Akshay on 30-11-2023
                    }
                    $arr_departments = Set::extract('/Departments/.', $this->Departments->find("all", array("fields" => array("dept_code"), "Conditions" => array("status" => 1))));
                    $departments = array();
                    foreach ($arr_departments as $vals) {
                        $departments[] = trim($vals['dept_code']); //Edited by Akshay on 30-11-2023
                    }
                    $arr_grades = Set::extract('/Grades/.', $this->Grades->find("all", array("fields" => array("grade_code"), "Conditions" => array("status" => 1))));
                    $grades = array();
                    foreach ($arr_grades as $vals) {
                        $grades[] = trim($vals['grade_code']); //Edited by Akshay on 30-11-2023
                    }
                    App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
                    $empcsvdata = new EmployeeCSVData();
                    $arr_empdetails_fields = $empcsvdata->getFieldNames('NewEmployeeDetails');
                    $arr_empprof_fields = $empcsvdata->getFieldNames('NewEmployeeProfessionalDetails');
                    foreach ($arrayempdata as $key => $row) {
                        $arr_empdetails_data = array();
                        $arr_empdetails_data['emp_pkey'] = 0;
                        $arr_empdetails_data['status'] = 1;
                        $arr_empdetails_data['emp_id'] = '';
                        $str_company_code = isset($authuser['company_code']) ? $authuser['company_code'] : '';
                        $arr_empdetails_data['company_code'] = $str_company_code;
                        $arr_empdetails_data['branch_code'] = $emp_branch;
                        $arr_empdetails_data['nationality_id'] = "75"; //This is to set default nationality as INDIAN. by sinsiya on 07-06-2024

                        foreach ($arr_empdetails_fields as $field => $fieldlabel) {
                            //debug($field);
                            if ($field == 'date_of_birth') {
                                $fieldValue = $row[$fieldlabel];
                                //$fieldValue = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($fieldValue));
                                $fieldValue = date("Y-m-d", strtotime($fieldValue));
                                //$fieldValue = substr($fieldValue, 4, 4) . '-' . substr($fieldValue, 2, 2) . '-' . substr($fieldValue, 0, 2);
                                //$fieldValue = substr($fieldValue, 6, 4) . '-' . substr($fieldValue, 3, 2) . '-' . substr($fieldValue, 0, 2);
                            } else if (in_array($field, array('classification', 'maritual_status'))) {
                                $fieldValue = strtolower($row[$fieldlabel]);
                            } else {
                                $fieldValue = $row[$fieldlabel];
                            }
                            $arr_empdetails_data[$field] = $fieldValue;
                        }
                        //Check for, if employee exists or not
                        if (isset($arr_empdetails_data['first_name']) && isset($arr_empdetails_data['last_name']) && isset($arr_empdetails_data['date_of_birth'])) {
                            $arrDuplicateList = $this->EmployeeDetails->find(
                                'all',
                                array(
                                    'fields' => 'EmployeeDetails.emp_pkey',
                                    'conditions' => array(
                                        'first_name' => $arr_empdetails_data['first_name'],
                                        'last_name' => $arr_empdetails_data['last_name'],
                                        'date_of_birth' => $arr_empdetails_data['date_of_birth'],
                                        'status' => 1
                                    )
                                )
                            );
                            if (count($arrDuplicateList) > 0) {
                                $arrDuplicateEmpList[] = $arrDuplicateList[0]['EmployeeDetails']['emp_pkey'];
                                continue; //Take next employee record, since this employee already exixts
                            }
                        }
                        $arr_empprof_data = array();
                        $arr_empprof_data['emp_proff_pkey'] = 0;

                        //$arr_empprof_data['emp_id'] = ''; //$str_company_code.$user_id; 
                        $arr_empprof_data['emp_branch'] = $emp_branch;

                        foreach ($arr_empprof_fields as $field => $fieldlabel) {
                            if ($field == 'joining_date') {
                                $fieldValue = $row[$fieldlabel];
                                //$fieldValue = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($fieldValue));
                                $fieldValue = date("Y-m-d", strtotime($fieldValue));
                                //$fieldValue = substr($fieldValue, 4, 4) . '-' . substr($fieldValue, 2, 2) . '-' . substr($fieldValue, 0, 2);
                                //$fieldValue = substr($fieldValue, 6, 4) . '-' . substr($fieldValue, 3, 2) . '-' . substr($fieldValue, 0, 2);
                            } elseif ($field == 'emp_grade') {  //Edited by Akshay on 8-2-2024
                                $cur_grade = $row[$fieldlabel];
                                // debug($cur_grade);
                                $arr_grades =  $this->EmployeeDetails->query("SELECT grade_pkey FROM grade WHERE grade_code = '$cur_grade' AND status = 1");
                                //edited by sinsiya 13-03-2024
                                if (empty($arr_grades)) {
                                    // Grade not found in table, add error message and continue to next row
                                    $errors[] = "";
                                    continue;
                                } else {
                                    $fieldValue = isset($arr_grades[0]['grade']['grade_pkey']) ? $arr_grades[0]['grade']['grade_pkey'] : 0;
                                    $cur_grade = $fieldValue;
                                }
                            } else {
                                $fieldValue = $row[$fieldlabel];
                            }
                            $arr_empprof_data[$field] = $fieldValue;
                        }
                        //Edited by Akshay on 29-11-2023
                        $id_card = isset($arr_empdetails_data['id_card']) ? $arr_empdetails_data['id_card'] : '';
                        $arrDuplicateIdCardList = $this->EmployeeDetails->find(
                            'all',
                            array(
                                'fields' => 'EmployeeDetails.emp_pkey',
                                'conditions' => array(
                                    'id_card' => $id_card,
                                    'status' => 1
                                )
                            )
                        );

                        if (!empty($arrDuplicateIdCardList)) {


                            $duplicate_id[] = $arr_empdetails_data['first_name'];

                            continue;
                        } /*Ended*/

                        if (in_array($arr_empprof_data['designation'], $desigantions)) {
                        } else {
                            $errors[] = $arr_empdetails_data['first_name'];

                            continue;
                        }
                        if (in_array($arr_empprof_data['emp_dept'], $departments)) {
                        } else {
                            $errors[] = $arr_empdetails_data['first_name'];

                            continue;
                        }
                        //                        if ($arr_empprof_data['emp_grade']) {
                        //                            if (in_array($arr_empprof_data['emp_grade'], $grades)) {
                        //                            } else {
                        //                                $errors[] = $arr_empdetails_data['first_name'];
                        //                                continue;
                        //                            }
                        //                        }


                        //  try {
                        $result1 = $this->EmployeeDetails->save($arr_empdetails_data);
                        /*  } catch (Exception $ex) {
                            $message = "Employee Details Saving Failed " . $ex->getMessage();
                            $errors[] = $arr_empdetails_data['first_name'];
                           continue;
                        } catch (mysqli_sql_exception $ex) {
                           $message = "Employee Credentials Error " . $ex->getMessage();
                            $errors[] = $arr_empdetails_data['first_name'];
                            continue;
                        } */


                        $intImportedCount++;

                        if (!empty($result1)) {
                            $pkey = $this->EmployeeDetails->getLastInsertID();

                            $arr_user_cred = array();
                            $arr_user_cred['user_pkey'] = 0;
                            if ($pkey > 0) {

                                $arr_user_cred['user_pkey'] = 0;
                                $arr_user_cred['emp_fkey'] = $pkey;
                                $arr_user_cred['company_code'] = $str_company_code;
                                $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                                $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                                $arr_user_cred['access_allowed'] = 'n';
                                $arr_user_cred['first_name'] = isset($arr_empdetails_data['first_name']) ? $arr_empdetails_data['first_name'] : '';
                                $arr_user_cred['last_name'] = isset($arr_empdetails_data['last_name']) ? $arr_empdetails_data['last_name'] : '';
                                $arr_user_cred['middle_name'] = isset($arr_empdetails_data['middile_name']) ? $arr_empdetails_data['middile_name'] : '';
                                $arr_user_cred['email'] = isset($arr_empdetails_data['email']) ? $arr_empdetails_data['email'] : '';
                                $arr_user_cred['phone'] = isset($arr_empdetails_data['mobile_no']) ? $arr_empdetails_data['mobile_no'] : '';

                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                //  try {
                                $result = $this->UserCredentials->save($arr_user_cred);
                                /*   } catch (Exception $ex) {
                                    $message = "Employee Credentials Saving Error " . $ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                } catch (mysqli_sql_exception $ex) {
                                    $message = "Employee Credentials Error " . $ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                } */


                                $arr_empprof_data['emp_fkey'] = $pkey;
                                $arr_empprof_data['notice_days'] = '30';
                                // try {
                                $result2 = $this->EmployeeProfessionalDetails->save($arr_empprof_data);
                                //Edited by Akshay on 8-2-2024
                                if (isset($cur_grade)) {
                                    try {
                                        $user_id = $this->Session->read('login_user_id');
                                        $result3 =  $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('GRADE','$pkey','$cur_grade','$user_id') ");
                                    } catch (Exception $e) {
                                        debug($e);
                                    }
                                }
                                /*  } catch (Exception $ex) {
                                    $message = "Employee Proffessioanls Saving Error " . $ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                } catch (mysqli_sql_exception $ex) {
                                    $message = "Employee Proffessioanls Error " . $ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                } */
                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                $outputParameter = array();
                                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                                $outputParameter[] = "'" . $emp_branch . "'";
                                $outputParameter[] = "''";
                                try {
                                    $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
                                    $company = strtoupper($this->Session->read('company_code'));
                                    if ($company == 'VSFS') {
                                        $empdeviceid = $this->UserCredentials->query("select SUBSTRING(max(trim(emp_company_id)),3, 10) as deviceid from emp_proff 
                                            where emp_company_id like '%VS%' AND emp_company_id NOT REGEXP 'VSFS' and emp_fkey!=$pkey");
                                        $empdeviceid1 = $empdeviceid['0']['0']['deviceid'];
                                        $empdeviceid1++;
                                        $this->EmployeeProfessionalDetails->query("update emp_proff set emp_company_id = concat('VS',$empdeviceid1) where emp_fkey=$pkey");
                                    }
                                } catch (Exception $ex) {
                                    $message = "Employee Saving Error " . $ex->getMessage();
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    $this->restsave($pkey);
                                    continue;
                                } catch (mysqli_sql_exception $ex) {
                                    $message = "Employee Saving Error " . $ex->getMessage();
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    $this->restsave($pkey);
                                    continue;
                                }
                            }
                        }
                    }
                }
                unlink($targetpath);
                echo json_encode(array('success' => 1, 'errors' => $errors, 'msg' => $message, 'imported_count' => $intImportedCount, 'duplicate_emp_list' => $arrDuplicateEmpList, 'duplicate_id' => $duplicate_id));
                exit;
            } else {
                unlink($targetpath);
                echo json_encode(array('success' => 0, 'errors' => $errors, 'msg' => 'Sorry, employee data import failed, no data found!', 'imported_count' => $intImportedCount, 'duplicate_emp_list' => 0));
                exit;
            }
        } else {
            echo json_encode(array('success' => 0, 'errors' => $errors, 'msg' => 'Sorry, employee data import failed!', 'imported_count' => $intImportedCount, 'duplicate_emp_list' => 0));
            exit;
        }
    }

    public function getcurrentemployeekey()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $sessionObj = $this->Session->read("Auth.User");
        if (isset($sessionObj['user_group']) && $sessionObj['user_group'] == 2) {
            return json_encode(array('success' => true, 'empPkey' => $sessionObj['emp_fkey']));
        }
        return json_encode(array('success' => false));
    }

    public function showsalaryupload()
    {
        $str_company_code = strtoupper($this->Session->read('company_code'));
        $this->set('companycode', $str_company_code);
    }

    /*
     * Employee CTC Upload form
     * By santhosh on 24 Oct 2015
     */

    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $arrear = '', $employee = '') //Edited by Akshay on 15-10-2024
    {
        $this->autoRender = FALSE;
        //Edited by Akshay on 19-10-2024
        $ctcuploadtype = ($ctcuploadtype === 'undefined') ? 0 : $ctcuploadtype;
        $branch = ($branch === 'undefined') ? '' : $branch;
        $arrear = ($arrear === 'undefined') ? '' : $arrear;
        //$payout = ($payout === 'undefined') ? '' : $payout;
        //  $startdate = ($startdate === 'undefined') ? '' : $startdate;
        $employee = ($employee === 'undefined') ? '' : $employee;
        //$approved_by = ($approved_by === 'undefined') ? 0 : $approved_by;
        $payout = "";
        $approved_by = '';
        $startdate = "";
        //  debug($ctcuploadtype);
        // debug($branch);
        // debug($arrear);
        // debug($payout);
        // debug($startdate);
        // debug($employee);
        // debug($approved_by);exit;
        //End

        $user_group = $this->Session->read('user_group');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_gross.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
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
        if ($ctcuploadtype == 1) {
            $worksheet = $objPHPExcel->getActiveSheet();
            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Company ID");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(4, 1, "Start Date Effective(yyyy-mm)");
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(27);
        } else {
            $worksheet = $objPHPExcel->getActiveSheet();
            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Company ID");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(4, 1, "New Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(5, 1, "Start Date Effective(yyyy-mm)");
            $worksheet->setCellValueByColumnAndRow(6, 1, "Next Increment Date(yyyy-mm-dd)"); //edited by anukrishnan_03-02-2025
            $worksheet->setCellValueByColumnAndRow(7, 1, "Arrear Need");
            $worksheet->setCellValueByColumnAndRow(8, 1, "Payout Month");
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30); //edited by anukrishnan_03-02-2025
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(27);
        }
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);

        $cond = '';
        //Edited by Akshay on 15-10-2024
        //  if ($approved_by != 0) {
        //      $cond .= " AND au.approved_by = '$approved_by'";
        //  }
        //End
        //        if (isset($employee) && !empty($employee) && $employee != 'null') {
        //            $cond.= " AND  EmployeeDetails.emp_pkey= if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        //        }
        //edited by megha on 24_07_2019 branch wise excel download
        if ($employee == '0') {
            $cond .= " AND  EmployeeDetails.emp_pkey = EmployeeDetails.emp_pkey ";
        } else if (isset($employee)) {
            $cond .= " AND  EmployeeDetails.emp_pkey = if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        }
        //edited by megha on 24_07_2019 branch wise excel download
        if ($branch == '0') {
            $cond .= " AND  EmployeeDetails.branch_code= EmployeeDetails.branch_code ";
        } else if (isset($branch)) {
            $cond .= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $cond .= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }



        if ($ctcuploadtype == 2) {
            $arr_empdetails = $this->EmployeeDetails->query(
                " SELECT UserCredentials.user_id, EmployeeInfo.EmpName,EmployeeInfo.employee_id,ect.emp_anual_ctc,au.start_date_effective , au.next_increment_date "
                    . "FROM emp_details AS EmployeeDetails "
                    . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                    . " LEFT JOIN emp_ctc_transaction AS ect ON (EmployeeDetails.emp_pkey = ect.emp_fkey) "
                    . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                    . " Left join emp_ctc_upload au on (EmployeeDetails.emp_pkey = au.emp_fkey and au.emp_ctc_upload_pkey in (select ctc_upload_fkey 
                from emp_ctc_transaction
                 where end_date_effective is null or end_date_effective >= current_date)) "
                    . " WHERE EmployeeDetails.status = ' 1 ' "
                    . " AND ect.end_date_effective is null  $cond  group by UserCredentials.user_id "
            );
            //  if(!$arr_empdetails){
            //     $arr_empdetails = $this->EmployeeDetails->query(
            //         " SELECT UserCredentials.user_id,EmployeeInfo.employee_id, EmployeeInfo.EmpName "
            //             . "FROM emp_details AS EmployeeDetails "
            //             . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
            //             . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
            //             . " WHERE EmployeeDetails.status = ' 1 '  $cond  group by UserCredentials.user_id "
            //     ); 
            //  }
        } else {
            $arr_empdetails = $this->EmployeeDetails->query(
                " SELECT UserCredentials.user_id,EmployeeInfo.employee_id, EmployeeInfo.EmpName "
                    . "FROM emp_details AS EmployeeDetails "
                    . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                    . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                    . " WHERE EmployeeDetails.status = ' 1 '  $cond  group by UserCredentials.user_id "
            );
        }
        $rowindex = 2;
        $columnindex = 0;
        if ($ctcuploadtype == 1) {
            foreach ($arr_empdetails as $value) {
                // debug($value);
                $userid = $value['UserCredentials']['user_id'];
                $empname = $value['EmployeeInfo']['EmpName'];
                $employee_company_id = $value['EmployeeInfo']['employee_id'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $userid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $empname);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $employee_company_id);
                $rowindex++;
            }
        } else {
            foreach ($arr_empdetails as $value) {
                $userid = $value['UserCredentials']['user_id'];
                $empname = $value['EmployeeInfo']['EmpName'];
                $ctc = isset($value['ect']['emp_anual_ctc']) ? $value['ect']['emp_anual_ctc'] : 0;
                $employee_company_id = $value['EmployeeInfo']['employee_id'];
                $incrementdate = isset($value['au']['next_increment_date']) ? $value['au']['next_increment_date'] : '';
                // $sdate = $value['au']['start_date_effective'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $userid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $empname);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $employee_company_id);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowindex, $ctc);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowindex, $ctc);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowindex, $startdate);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowindex, $incrementdate); // Edited by Akshay on 3-4-2025
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowindex, $arrear);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowindex, $payout);

                $rowindex++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('Annual Salary Upload '); //Edited by Akshay on 10-10-2024

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsaveempctc($ctcuploadtype = 0)
    {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);
        //debug($ctcuploadtype);
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

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
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
                            $array_mandatory_column_names = array('Employee ID', 'Employee Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                            }
                            $index++;
                        }
                    }
                    if ($mandatory_fields_warning) {
                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        //                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
                        //                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
                        //                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
                        //                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');
                        //edited by megha on 10/08/2019 annual gross salary amount empty excel msg 1
                        $count = 0;

                        foreach ($arrayempdata as $key => $row) {

                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($ctcuploadtype == 1) {
                                if ($row['Annual Gross Salary'] == '0') {
                                    continue;
                                }
                                $emp_anual_ctc = isset($row['Annual Gross Salary']) ? $row['Annual Gross Salary'] : '';
                                // $start = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';
                            } else {
                                if ($row['New Annual Gross Salary'] == '0') {
                                    continue;
                                }
                                $emp_anual_ctc = isset($row['New Annual Gross Salary']) ? $row['New Annual Gross Salary'] : '';
                                // $start = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';

                                $arrear = isset($row['Arrear Need']) ? $row['Arrear Need'] : '';
                                $payout = isset($row['Payout Month']) ? $row['Payout Month'] : '';
                                // Edited by Akshay on 5-4-2025
                                if (preg_match('/^\d{4}-\d{2}$/', $payout)) {
                                    $payout .= '-01';
                                }
                                // End
                            }

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
                            // debug($arr_empctc_data);
                            $arr_empctc_data = array();
                            $arr_empctc_data['emp_ctc_upload_pkey'] = 0;
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['emp_fkey'] = $emp_fkey;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['created_date'] = date('Y-m-d');
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
                            // var_dump($start); 
                            // $arr_empctc_data['start_date_effective'] = date('Y-m-d H:i:s'); //edited by anukrishnan 15-02-2025
                            // var_dump($arr_empctc_data['start_date_effective']); exit;
                            // Edited by Akshay on 5-4-2025
                            $startDate = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';
                            if (preg_match('/^\d{4}-\d{2}$/', $startDate)) {
                                $startDate .= '-01';
                            } else {
                                $startDate =  date('Y-m-d');
                            }
                            $arr_empctc_data['start_date_effective'] = $startDate;
                            // End

                            $isValidDate = DateTime::createFromFormat('Y-m-d', $row['Next Increment Date(yyyy-mm-dd)']) !== false; // Edited by Akshay on 4-4-2025

                            // Edited by Akshay on 5-4-2025
                            if (!$isValidDate) {
                                $date = $row['Next Increment Date(yyyy-mm-dd)'];
                                if (is_numeric($date)) {
                                    $base = new DateTime('1899-12-30');
                                    $formattedDate = $base->modify("+{$date} days")->format('Y-m-d');
                                    $row['Next Increment Date(yyyy-mm-dd)'] = $formattedDate;
                                }
                                $isValidDate = DateTime::createFromFormat('Y-m-d', $row['Next Increment Date(yyyy-mm-dd)']) !== false;
                            }
                            // End

                            //edited by anukrishnan_03-02-2025 open
                            if (isset($row['Next Increment Date(yyyy-mm-dd)']) && $isValidDate) { // Edited by Akshay on 4-4-2025
                                // $next_increment_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($row['Next Increment Date(yyyy-mm-dd)']));
                                $next_increment_date = $row['Next Increment Date(yyyy-mm-dd)'];  // Edited by Akshay on 4-4-2025
                            } else {
                                $next_increment_date = ''; // Handle invalid or empty values
                            }

                            $arr_empctc_data['next_increment_date'] = $next_increment_date;
                            $query = $this->EmployeeCTC->query("
                                SELECT ei.branch, ei.designation, ei.department
                                FROM employee_info ei
                                JOIN emp_ctc_upload ectc ON ei.emp_pkey = ectc.emp_fkey
                                WHERE ectc.emp_fkey = $emp_fkey
                            ");
                            $branch = $query[0]['ei']['branch'];
                            $designation = $query[0]['ei']['designation'];
                            $department = $query[0]['ei']['department'];

                            $arr_empctc_data['branch'] = $branch;
                            $arr_empctc_data['designation'] = $designation;
                            $arr_empctc_data['department'] = $department;
                            //edited by anukrishnan_03-02-2025 close

                            // var_dump($next_increment_date); exit;


                            //                            foreach ($arr_empctc_fields as $field => $fieldlabel) {
                            //                                $fieldValue = $row[$fieldlabel];
                            //                                $arr_empctc_data[$field] = $fieldValue;
                            //                               // debug($arr_empctc_data);
                            //                            }


                            try {
                                // debug($arr_empctc_data);exit;
                                // var_dump("huuu");
                                $result1 = $this->EmployeeCTC->save($arr_empctc_data);
                                // var_dump($result1); exit;
                                //Update salary structure for employee by uploaded CTC
                                //On 20 Sep 2016
                                //arun 15-10-2016 based on ashoakn
                                //                                $this->updateSalStructureDistributionFn($emp_fkey);
                            } catch (Exception $e) {
                                //debug($e);
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
                            echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary Revised Successfully'));
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

    //popup for save and update
    public function form()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        //        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));     
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and emp_details.branch_code='$cur_emp_branch' ";
        } else {
            $branch_condition = "";
        }
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;
            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $branch_condition = " and emp_details.branch_code!='$branch' ";
        }

        // Edited by Akshay on 10-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');

        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " and emp_details.branch_code ='$is_ho' ";
            } else {
                $branch_condition = "";
            }
        }
        // End

        //employee branch wise sorting ends here
        //Employee Company ID added by ***ARUL P DAS on 12/12/2019

        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1' . $branch_condition . ' order by first_name ASC');
        $this->set("arr_employees", $arr_employees);
        $data['emp_ctc_upload_pkey'] = 0;
        $data['emp_fkey'] = '';
        $data['emp_anual_ctc'] = '';
        $data['emp_monthly_ctc'] = '';
        $data['emp_loan_balance'] = '';
        $data['emp_advance'] = '';
        $data['emp_tds_deducted'] = '';
        if (isset($_REQUEST['emp_ctc_upload_pkey']) && $_REQUEST['emp_ctc_upload_pkey'] != 0) {
            $data_db = $this->EmployeeCTC->find("first", array("conditions" => array("emp_ctc_upload_pkey" => $_REQUEST['emp_ctc_upload_pkey'])));
            $data = $data_db['EmployeeCTC'];
        }
        $this->set("data", $data);
        //$this->layout = null;
    }

    //save 
    public function employeesave()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        // edited by anukrishnan_04-02-2025 open
        $emp_key = $arr_form_data['emp_fkey'];
        $query = $this->EmployeeCTC->query("
                    SELECT ei.branch, ei.designation, ei.department
                    FROM employee_info ei
                    JOIN emp_ctc_upload ectc ON ei.emp_pkey = ectc.emp_fkey
                    WHERE ectc.emp_fkey = $emp_key
                ");

        $branch = $query[0]['ei']['branch'];
        $designation = $query[0]['ei']['designation'];
        $department = $query[0]['ei']['department'];
        // edited by anukrishnan_04-02-2025 close

        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        //arun 15-10-2016

        // Edited by Akshay on 28-12-2024
        // $date = $arr_form_data['start_date_effective'];

        // $month = explode("-", $date);
        // $year = $month[0];
        // $mon = $month[1];
        // $dat = 1;
        // $set_month = $year . '-' . $mon . '-' . $dat;
        // $arr_form_data['start_date_effective'] = $set_month;
        // End
        $arr_form_data['start_date_effective'] = date('Y-m-d H:i:s');
        // edited by anukrishnan_15-02-2025 close
        $arr_form_data['next_increment_date'] = isset($arr_form_data['next_increment_date']) ? date('Y-m-d', strtotime($arr_form_data['next_increment_date'])) : ''; // edited by anukrishnan_01-02-2025
        $arr_form_data['branch'] = $branch;   // edited by anukrishnan_04-02-2025
        $arr_form_data['designation'] = $designation;    // edited by anukrishnan_04-02-2025
        $arr_form_data['department'] = $department;     // edited by anukrishnan_04-02-2025

        // Edited by Akshay on 7-4-2025
        if (isset($arr_form_data['pay_out_month']) && trim($arr_form_data['pay_out_month']) != '') {
            $arr_form_data['pay_out_month'] = date('Y-m-1', strtotime($arr_form_data['pay_out_month']));
        } else {
            $arr_form_data['pay_out_month'] = '';
        }
        // End

        try {
            $result = $this->EmployeeCTC->save($arr_form_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Employee Gross Salary Added successfully";
            echo json_encode($resp);
        } catch (Exception $ex) {

            $resp = array();
            $resp["success"] = false;
            $resp["msg"] = "Employee Gross Salary Added Failed";
            echo json_encode($resp);
        }

        //Update salary structure for employee by newly added CTC
        //On 20 Sep 2016
        //$emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : '';
        //arun 15-10-2016
        //$this->updateSalStructureDistributionFn($emp_fkey);
    }


    //datagridelist            
    public function employeelist()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        // debug($arr_request_data);
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $arrear = isset($arr_request_data['arrear']) ? $arr_request_data['arrear'] : '';
        // debug($branch_code);
        $no_arrear = isset($arr_request_data['no_arrear']) ? $arr_request_data['no_arrear'] : '';
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        // $limit = round($limit / 2); //Edited by Akshay on 24-10-2024
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $branch_condition = '';
        $emp_condition = '';
        $user_group = $this->Session->read('user_group');
        $cur_emp_key = $this->Session->read("emp_fkey");
        //debug($arr_request_data);
        //        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
        //            $emp = $arr_request_data['employee'];
        //            $emp_condition = "and au.emp_fkey=$emp";
        //        } else {
        //            $emp_condition = ' ';
        //        }
        //        if ($branch_code != '') {
        //            $branch_condition = "and ed.branch_code='$branch_code'";
        //        }
        //debug($emp_fkey);
        // debug($branch_code);
        //edited by megha on 29_07_2019 branch wise pagination  
        if ($emp_fkey == '' || $emp_fkey == '0') {
            $emp_condition = "";
        } else if (isset($emp_fkey)) {
            $emp = $emp_fkey;
            $emp_condition = "and au.emp_fkey=$emp";
        }


        $user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeCTC->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeCTC']['branch_code'];
            $branch_condition = " and ed.branch_code='" . $cur_emp_branch . "'";
        } elseif ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $payroUser = $this->EmployeeCTC->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
            $arr_is_ho = $this->EmployeeCTC->query("SELECT get_branch_code_abs_fn($cur_emp_key) AS branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " and ed.branch_code='$is_ho' ";
            }
        } else
        if ($user_group == 2) {
            $payroUser = $this->EmployeeCTC->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");

            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $branch_condition = "and ed.branch_code!='$branch' and ed.branch_code='$branch_code'";
        } else {
            if ($branch_code == '0' || $branch_code == '') {
                $branch_condition = "";
            } else if (isset($branch_code)) {
                $branch_condition = "and ed.branch_code='$branch_code'";
            }
        }

        $param = '';
        if (isset($arr_request_data['emp'])) {
            // $param = "and ed.first_name like '%" . $arr_request_data['emp'] . "%'";
            // // debug($param);
            $param = "and (ed.first_name like '%" . $arr_request_data['emp'] . "%' OR ei.employee_id like '%" . $arr_request_data['emp'] . "%')";
            // debug($param);
        }
        //        if ($emp_fkey != '') {
        //            $emp_condition = "and au.emp_fkey=$emp_fkey";
        //        }
        //debug($emp_condition);



        $counts = $this->EmployeeCTC->query(
            "select
            COUNT(*)
             from emp_ctc_upload au
                            INNER join emp_details ed on (ed.emp_pkey = au.emp_fkey)
                            INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
                            where au.status=1 
                  $emp_condition $param "
                . " $branch_condition"
                . " and au.emp_ctc_upload_pkey in (select ctc_upload_fkey "
                . " from emp_ctc_transaction"
                . " where end_date_effective is null or end_date_effective >= current_date)"
        );
        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->EmployeeCTC->query("select
            ed.emp_pkey,au.*,ei.employee_id,ei.EmpName
             from emp_details ed
                            INNER join emp_ctc_upload au on (ed.emp_pkey = au.emp_fkey)
                            INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
                            where au.status=1 
                $emp_condition $param"
            . " $branch_condition"
            . " and au.emp_ctc_upload_pkey in (select ctc_upload_fkey "
            . " from emp_ctc_transaction"
            . " where end_date_effective is null or end_date_effective >= current_date) "
            . " ORDER BY emp_ctc_upload_pkey desc "
            . " limit $limit  offset $ofst  ");
        //Edited by Akshay on 24-10-2024
        // $arr_att2 = $this->EmployeeCTC->query("select
        //                         ed.emp_pkey,au.*,ei.employee_id,ei.EmpName
        //                         from emp_details ed
        //                         INNER join promotions au on (ed.emp_pkey = au.emp_fkey)
        //                         INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
        //                         where au.status=1 
        //                         $emp_condition $param 
        //                         $branch_condition
        //                         and au.remarks = 'Salary revision'   
        //                         ORDER BY promotion_pkey desc  limit $limit  offset $ofst ");
        //                         $arr_att = array_merge(array_values($arr_att), array_values($arr_att2));
        //                         $count = count($arr_att);
        //End
        $out = array();
        //debug($arr_att);
        foreach ($arr_att as $key => $value) {
            // debug($value);
            $out['empid'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : '';
            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
            $out['emp_ctc_upload_pkey'] = isset($value['au']['emp_ctc_upload_pkey']) ? $value['au']['emp_ctc_upload_pkey'] : '';
            $out['emp_fkey'] = $emp_pkey = isset($value['au']['emp_fkey']) ? $value['au']['emp_fkey'] : '';
            $out['emp_loan_balance'] = isset($value['au']['emp_loan_balance']) ? $value['au']['emp_loan_balance'] : '';
            $out['emp_advance'] = isset($value['au']['emp_advance']) ? $value['au']['emp_advance'] : '';
            $out['emp_anual_ctc'] = isset($value['au']['emp_anual_ctc']) ? $value['au']['emp_anual_ctc'] : (isset($value['au']['annual_gross']) ? $value['au']['annual_gross'] : '');
            $out['emp_tds_deducted'] = isset($value['au']['emp_tds_deducted']) ? $value['au']['emp_tds_deducted'] : '';
            $out['start_date_effective'] = isset($value['au']['start_date_effective']) ? date('d-m-Y', strtotime($value['au']['start_date_effective'])) : '';
            // Edited by Akshay on 19-6-2025
            $out['next_increment_date'] = (!empty($value['au']['next_increment_date']) && $value['au']['next_increment_date'] !== '0000-00-00')  //edited by anukrishnan_01-02-2025
                ? date('d-m-Y', strtotime($value['au']['next_increment_date'])): '';
            $out['pay_out_month'] = (!empty($value['au']['pay_out_month']) && $value['au']['pay_out_month'] !== '0000-00-00')
                ? date('F-Y', strtotime($value['au']['pay_out_month'])): '';
            // $out['pay_out_month'] = isset($value['au']['pay_out_month']) ? $value['au']['pay_out_month'] : '';
            // End
            // $out['next_increment_date'] = (!empty($value['au']['next_increment_date']) && $value['au']['next_increment_date'] !== '0000-00-00' && $value['au']['next_increment_date'] !== '1970-01-01')  //edited by anukrishnan_01-02-2025
            //     ? date('d-m-Y', strtotime($value['au']['next_increment_date'])) : '';
            // $out['pay_out_month'] = isset($value['au']['pay_out_month']) ? date('Y-m', strtotime($value['au']['pay_out_month'])) : ''; // Edited by Akshay on 20-12-2024
            $out['arrear_salary'] = isset($value['au']['arrear_salary']) ? $value['au']['arrear_salary'] : '';
            $out['status'] = isset($value['au']['status']) ? (($value['au']['status'] == '1') ? 'Approved' : 'Pending') : ''; //Edited by Akshay on 10-10-2024
            //Edited by Akshay on 10-10-2024
            $approved_by = isset($value['au']['approved_by']) ? $value['au']['approved_by'] : '';
            $arr_emp = $this->EmployeeCTC->query("SELECT EmpName, employee_id FROM employee_info ei WHERE emp_pkey = '$approved_by'");
            $out['approved_by'] = isset($arr_emp[0]['ei']['EmpName']) ? $arr_emp[0]['ei']['EmpName'] . ' - ' . $arr_emp[0]['ei']['employee_id'] : '';
            //End
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    //main page dropdown       
    public function ctcupload()
    {
        //$this->autoRender = FALSE; // Edited by Akshay on 15-3-2025
        $company_code = strtoupper($this->Session->read('company_code')); // Edited by Akshay on 15-3-2025
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);
        $emp_pkey = $this->Session->read('emp_fkey');
        $conditions = array("status" => 1); // Edited by Akshay on 15-3-2025
        //edited by sinsiya
        // $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $is_ho = 1; // Edited by Akshay on 11-2-2025
        if ($user_group == 2) {
            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;
            $this->set('payroUser', $payroUser);
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                if ($is_ho != 1) {
                    $conditions = array("branch_code" => $is_ho, "status" => 1); // Edited by Akshay on 15-3-2025
                }
            }
        }
        $this->set("is_ho", $is_ho); // Edited by Akshay on 11-2-2025
        //   debug($conditions);
        //   exit;

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        //$user_group = $this->Session->read('user_group');
        $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions = array("branch_code" => $cur_emp_branch, "status" => 1); // Edited by Akshay on 15-3-2025
        } else {
            $conditions = array("status" => 1); // Edited by Akshay on 15-3-2025
        }
        //edited by sinsiya
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];

            $conditions['branch_code !='] = $branch; // Edited by Akshay on 15-3-2025
            // debug($conditions);
        }
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => $conditions)));


        //debug($arr_branches);
        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => $conditions)));

        
        // debug($this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => $conditions)));
    }

    //delete
    public function deleteEmployees()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_ctc_upload_pkey"]) || isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["emp_ctc_upload_pkey"]);
            //debug($ar_ids);
            $this->EmployeeCTC->updateAll(
                array('EmployeeCTC.status' => 0),
                array('EmployeeCTC.emp_ctc_upload_pkey' => $ar_ids)
            );
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

    public function deleteEmp()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($ar_ids);
        $ar_ids = $_REQUEST["ids"];
        //$msg = 'Removed by admin on '.date('d-m-Y');

        $this->EmployeeDetails->updateAll(
            array('EmployeeDetails.status' => 2),
            array('EmployeeDetails.emp_pkey' => $ar_ids)
        );
        $result['success'] = 1;
        $result['msg'] = "Record(s)  deleted successfully.";

        echo json_encode($result);
    }

    public function activeEmp()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        $ar_ids = $_REQUEST["ids"];
        $unwantedUsers = $this->EmployeeDetails->find('count', array('conditions' => array('emp_pkey' => $ar_ids, 'status' => 1)));

        $this->EmployeeDetails->updateAll(
            array('EmployeeDetails.status' => 1),
            array('EmployeeDetails.emp_pkey' => $ar_ids, 'EmployeeDetails.status' => 2)
        );

        if ($unwantedUsers > 0) {
            $result['success'] = 1;
            $result['msg'] = "Selected employee consists of already active records. Employee activated successfully.";
        } else {
            $result['success'] = 1;
            $result['msg'] = "Employee activated successfully.";
        }

        echo json_encode($result);
    }

    public function addqualification($emp_pkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
        $company_code=$this->Session->read('company_code');
        $this->set('company_code',$company_code);
    }

    public function addfamily($emp_pkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
        $company_code=$this->Session->read('company_code');
        $this->set('company_code',$company_code);
    }

    public function passport($emp_pkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
        $company_code=$this->Session->read('company_code');
        $this->set('company_code',$company_code);
    }
// edited by bindhu 16-01-2026
 public function savehistory()
    {
        $this->history->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        if ($arr_form_data['emp_fkey'] != '') {
            $this->history->save($arr_form_data);
        } else {
        }
        $this->autoRender = FALSE;
    }
    public function savefamily()
    {
        $this->Family->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $this->Family->save($arr_form_data);
        $this->autoRender = FALSE;
    }

    public function savepassport()
    {
        $this->passport->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $this->passport->save($arr_form_data);
        $this->autoRender = FALSE;
    }

    public function savequalifications()
    {
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        if ($arr_form_data['emp_fkey'] != '') {
            $this->qualifcations->save($arr_form_data);
        } else {
        }
        $this->autoRender = FALSE;
    }

    public function history($emp_pkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
        $company_code=$this->Session->read('company_code');
        $this->set('company_code',$company_code);
        
    }
    // edited by bindu 16-01-2026
// public function savequalifications()
//     {
//         $this->qualifcations->useDbConfig = $this->Session->read('ds');
//         $arr_form_data = $this->request->data;

//         if (!empty($arr_form_data['emp_fkey'])) {
//             $user_group   = $this->Session->read('user_group');
//             $company_code = $this->Session->read('company_code');

//             if (($company_code == 'ABSG')) {
//                 echo json_encode([
//                     'status'  => 'error',
//                     'message' => 'You are not allowed to save qualifications for this company.'
//                 ]);
//             } else {
//                 if ($this->qualifcations->save($arr_form_data)) {
//                     echo json_encode([
//                         'status'  => 'success',
//                         'message' => 'Education data saved successfully!'
//                     ]);
//                 } else {
//                     echo json_encode([
//                         'status'  => 'error',
//                         'message' => 'Failed to save education data.'
//                     ]);
//                 }
//             }
//         } else {
//             echo json_encode([
//                 'status'  => 'error',
//                 'message' => 'Employee key is missing.'
//             ]);
//         }

//         $this->autoRender = false;
//     }

    // public function savepassport()
    // {
    //     $this->autoRender = false;

    //     $this->passport->useDbConfig = $this->Session->read('ds');

    //     $data = $this->request->data;

    //     if (!empty($data)) {

    //         $filePath = null;
    //         if (!empty($_FILES['avatarfile']['name'])) {
    //             $uploadDir = 'img/avatar/';
    //             if (!file_exists($uploadDir)) {
    //                 mkdir($uploadDir, 0775, true);
    //             }

    //             // generate unique filename based on sha1_file
    //             $ext = strtolower(pathinfo($_FILES['avatarfile']['name'], PATHINFO_EXTENSION));
    //             $newFileName = sha1_file($_FILES['avatarfile']['tmp_name']) . '.' . $ext;
    //             $fullPath = $uploadDir . $newFileName;

    //             if (move_uploaded_file($_FILES['avatarfile']['tmp_name'], $fullPath)) {
    //                 // save relative path (for DB)
    //                 $filePath = 'img/avatar/' . $newFileName;
    //             }
    //         }
    //         $this->passport->create(); // Always create new record

    //         $saveData = [
    //             'emp_fkey'   => $data['emp_fkey'],
    //             'name'     => $data['name'],
    //             'document_type' => $data['document_type'],
    //             'document_number'   => $data['document_number'],
    //             'relation' => $data['relation'],
    //             'valid_from' => $data['valid_from'],
    //             'valid_till' => $data['valid_till'],
    //             'classification' => $data['classification'],
    //             'nationality' => $data['nationality'],
    //             'files' => $filePath,
    //             'remind' => $data['remind'],
    //             'reccuring' => $data['reccuring'],
    //             'status'       => 1
    //         ];

    //         if ($this->passport->save($saveData)) {
    //             echo json_encode([
    //                 'status' => 'success',
    //                 'message' => 'Document details saved successfully.',
    //                 'file_path' => $filePath
    //             ]);
    //         } else {
    //             echo json_encode([
    //                 'status' => 'error',
    //                 'message' => 'Failed to save document details.'
    //             ]);
    //         }
    //     } else {
    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => 'No data received.'
    //         ]);
    //     }
    // }
//     public function savefamily()
//     {
//         $this->Family->useDbConfig = $this->Session->read('ds');
//         $arr_form_data = $this->request->data;
//         // $arr_form_data['created_by'] = $this->Session->read('user_name');

//         $createdBy = $this->Session->read('user_name');
//         if (empty($createdBy)) {
//             $createdBy = $this->Session->read('login_user_id');
//         }

//         $arr_form_data['created_by'] = $createdBy;

//         if ($this->Family->save($arr_form_data)) {
//             echo json_encode([
//                 'status'  => 'success',
//                 'message' => 'Family data saved successfully!'
//             ]);
//         } else {
//             echo json_encode([
//                 'status'  => 'error',
//                 'message' => 'Failed to save family data.'
//             ]);
//         }

//         $this->autoRender = false;
//     }

//     public function savehistory()
//     {
//         $this->history->useDbConfig = $this->Session->read('ds');
//         $arr_form_data = $this->request->data;

//         if (!empty($arr_form_data['emp_fkey'])) {
//             if ($this->history->save($arr_form_data)) {
//                 echo json_encode([
//                     'status'  => 'success',
//                     'message' => 'Experience data saved successfully!'
//                 ]);
//             } else {
//                 echo json_encode([
//                     'status'  => 'error',
//                     'message' => 'Failed to save experience data.'
//                 ]);
//             }
//         }

//         $this->autoRender = false;
//     }
// edited by bindu 16-01-2026 end
    //edited by bindu 
    public function getEducation($emp_pkey = null)
    {
        $this->autoRender = false;
        $this->qualifcations->useDbConfig = $this->Session->read('ds');

        if (!$emp_pkey) {
            echo json_encode([]);
            return;
        }
        $educationData = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));

        $result = array_map(function ($row) {
            return $row['qualifcations'];
        }, $educationData);

        echo json_encode($result);
    }

    public function getExperience($emp_pkey = null)
    {
        $this->autoRender = false;

        if (!$emp_pkey) {
            echo json_encode([]);
            return;
        }

        $this->history->useDbConfig = $this->Session->read('ds');

        $experienceData = $this->history->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));

        $result = array_map(function ($row) {
            return $row['history'];
        }, $experienceData);

        echo json_encode($result);
    }

    public function getFamily($emp_pkey = null)
    {
        $this->autoRender = false;

        if (!$emp_pkey) {
            echo json_encode([]);
            return;
        }

        $this->Family->useDbConfig = $this->Session->read('ds');

        $familyData = $this->Family->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));

        $result = array_map(function ($row) {
            return $row['Family'];
        }, $familyData);

        echo json_encode($result);
    }

    public function getDocument($emp_pkey = null)
    {
        $this->autoRender = false;

        if (!$emp_pkey) {
            echo json_encode([]);
            return;
        }

        $this->passport->useDbConfig = $this->Session->read('ds');

        $docData = $this->passport->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));

        $result = array_map(function ($row) {
            return $row['passport'];
        }, $docData);

        echo json_encode($result);
    }

    public function savequalificationsjoin()
    {
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        if (!empty($arr_form_data['emp_fkey'])) {
            $user_group   = $this->Session->read('user_group');
            $company_code = $this->Session->read('company_code');

            if (($company_code == 'ABSG')) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'You are not allowed to save qualifications for this company.'
                ]);
            } else {
                if ($this->qualifcations->save($arr_form_data)) {
                    echo json_encode([
                        'status'  => 'success',
                        'message' => 'Education data saved successfully!'
                    ]);
                } else {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'Failed to save education data.'
                    ]);
                }
            }
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Employee key is missing.'
            ]);
        }

        $this->autoRender = false;
    }

    public function savepassportjoin()
    {
        $this->autoRender = false;

        $this->passport->useDbConfig = $this->Session->read('ds');

        $data = $this->request->data;

        if (!empty($data)) {

            $filePath = null;
            if (!empty($_FILES['avatarfile']['name'])) {
                $uploadDir = 'img/avatar/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                // generate unique filename based on sha1_file
                $ext = strtolower(pathinfo($_FILES['avatarfile']['name'], PATHINFO_EXTENSION));
                $newFileName = sha1_file($_FILES['avatarfile']['tmp_name']) . '.' . $ext;
                $fullPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['avatarfile']['tmp_name'], $fullPath)) {
                    // save relative path (for DB)
                    $filePath = 'img/avatar/' . $newFileName;
                }
            }
            $this->passport->create(); // Always create new record

            $saveData = [
                'emp_fkey'   => $data['emp_fkey'],
                'name'     => $data['name'],
                'document_type' => $data['document_type'],
                'document_number'   => $data['document_number'],
                'relation' => $data['relation'],
                'valid_from' => $data['valid_from'],
                'valid_till' => $data['valid_till'],
                'classification' => $data['classification'],
                'nationality' => $data['nationality'],
                'files' => $filePath,
                'remind' => $data['remind'],
                'reccuring' => $data['reccuring'],
                'status'       => 1
            ];

            if ($this->passport->save($saveData)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Document details saved successfully.',
                    'file_path' => $filePath
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save document details.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data received.'
            ]);
        }
    }
    public function savefamilyjoin()
    {
        $this->Family->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // $arr_form_data['created_by'] = $this->Session->read('user_name');

        $createdBy = $this->Session->read('user_name');
        if (empty($createdBy)) {
            $createdBy = $this->Session->read('login_user_id');
        }

        $arr_form_data['created_by'] = $createdBy;

        if ($this->Family->save($arr_form_data)) {
            echo json_encode([
                'status'  => 'success',
                'message' => 'Family data saved successfully!'
            ]);
        } else {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to save family data.'
            ]);
        }

        $this->autoRender = false;
    }

    public function savehistoryjoin()
    {
        $this->history->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        if (!empty($arr_form_data['emp_fkey'])) {
            if ($this->history->save($arr_form_data)) {
                echo json_encode([
                    'status'  => 'success',
                    'message' => 'Experience data saved successfully!'
                ]);
            } else {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Failed to save experience data.'
                ]);
            }
        }

        $this->autoRender = false;
    }
    //end

   

    public function lstfamilies($emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);

        $this->Family->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $resp_emp["data"] = array();
        $this->Family->useDbConfig = $this->Session->read('ds');
        $count = $this->Family->find("count");
        $arr_emp = $this->Family->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $value["Family"]['DOB'] = date("d-m-Y", strtotime($value["Family"]['DOB'])); //Edited by Akshay on 7-3-2024
            $resp_emp["data"][$key] = $value["Family"];
        }

        //  debug($resp_emp["rows"][$key]);
        echo json_encode($resp_emp);
    }

    public function listhistory($emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');



        $resp_emp = array();
        $resp_emp["data"] = array();
        $this->history->useDbConfig = $this->Session->read('ds');
        $count = $this->history->find("count");
        $arr_emp = $this->history->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $value["history"]["from_date"] = date("d-m-Y", strtotime($value["history"]["from_date"])); //Edited by Akshay on 7-3-2024
            $value["history"]["to_date"] = date("d-m-Y", strtotime($value["history"]["to_date"])); //Edited by Akshay on 7-3-2024
            $resp_emp["data"][$key] = $value["history"];
        }
        //  debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

    public function getusers($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$emp_fkey = $this->Session->read('emp_fkey');
        $arr_request_data = $this->request->query;
        $searchkey = $arr_request_data['username'];
        $filter_condition = array();
        //        if (isset($arr_request_data['username'])) {
        //           
        //            $filter_condition = 'first_name LIKE "%' . $searchkey . '%" and emp_pkey != ' . $emp_fkey;
        //        } else {
        //            $filter_condition = '';
        //        }
        $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  "
            . "OR EmpName like '%" . $searchkey . "%' OR EmployeeDetails.emp_id like '%" . $searchkey . "%' ";

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'employee_info',
                'alias' => 'EmpInfo',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpInfo.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        $arr_users = $this->EmployeeDetails->find(
            'all',
            array(
                'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
                'joins' => $joins,
                'conditions' => array(
                    'status' => 1,
                    $filter_condition
                )
            )
        );

        //  debug($arr_users);
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val[0]) : array();
        }
        //  debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function listqualifications($emp_pkey = '')
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $resp_emp["data"] = array();
        $count = $this->qualifcations->find("count", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $arr_emp = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $resp_emp["data"][$key] = $value["qualifcations"];
        }

        //  debug($resp_emp["rows"][$key]);
        echo json_encode($resp_emp);
    }

    public function listpassports($emp_pkey = '')
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);
        $this->passport->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $resp_emp["data"] = array();
        $count = $this->passport->find("count", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $arr_emp = $this->passport->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        //        debug($arr_emp);
        foreach ($arr_emp as $key => $value) {
            //edited by megha on 5_6_19 no file display
            if (!empty($value['passport']['files'])) {
                // debug($value['passport']['files']);
                //$value["passport"]["imgs"] = isset($value['passport']['files'])?'<a href="https://login.mypayrollmaster.com/'.$value['passport']['files'].'" download>Download</a>':'No File';
                $value["passport"]["imgs"] = '<a href="https://v1.mypayrollmaster.online/' . $value['passport']['files'] . '" download target="_blank">Download</a>';
            } else {
                $value["passport"]["imgs"] = 'No File';
            }
            //end
            $value["passport"]["valid_from"] = date("d-m-Y", strtotime($value["passport"]["valid_from"])); //Edited by Akshay on 7-3-2024
            $value["passport"]["valid_till"] = date("d-m-Y", strtotime($value["passport"]["valid_till"])); //Edited by Akshay on 7-3-2024
            $resp_emp["data"][$key] = $value["passport"];
        }

        //  debug($resp_emp["rows"][$key]);
        echo json_encode($resp_emp);
    }

    public function savefile()
    {
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $resp = array();
        $resp['error'] = '';
        $this->autoRender = FALSE;
        try {

            if (
                !isset($_FILES['avatarfile']['error']) ||
                is_array($_FILES['avatarfile']['error'])
            ) {
                $resp['error'] = 'Invalid parameters.';
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['avatarfile']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $resp['error'] = 'No file sent.';
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $resp['error'] = 'Exceeded filesize limit.';
                default:
                    $resp['error'] = 'Unknown errors.';
            }

            // You should also check filesize here. 
            if ($_FILES['avatarfile']['size'] > 1000000) {
                $resp['error'] = 'Exceeded filesize limit.';
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $finfo->file($_FILES['avatarfile']['tmp_name']),
                array(
                    'jpeg' => 'image/jpeg',
                    'jpg' => 'image/jpg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'pdf' => 'application/pdf'
                    //edited by megha on 6/6/19 removed xsl and zip
                    // 'xlxs' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    // 'docx' => 'application/zip',
                    //end
                ),
                true
            )) {
                $resp['error'] = 'Invalid file format.';
            }

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.


            $filename = sprintf(
                'img/avatar/%s.%s',
                sha1_file($_FILES['avatarfile']['tmp_name']),
                $ext
            );
            if (!move_uploaded_file(
                $_FILES['avatarfile']['tmp_name'],
                $filename
            )) {
                $resp['error'] = 'Failed to move uploaded file.';
            }
            //   echo 'File is uploaded successfully.';
            $this->passport->useDbConfig = $this->Session->read('ds');
            /*
              $this->CentralUserCredentials->updateAll(
              array('avatar' => $filename),
              array('user_id' => $this->Session->read('user_pkey'))
              ); */

            $data['user_pkey'] = $this->Session->read('user_pkey');
            $data['avatar'] = $filename;
            $resp['data'] = $data;
            //debug($data);
        } catch (RuntimeException $e) {

            //  echo $e->getMessage();
        }
        echo json_encode($resp);
    }

    public function deletequal($pkey = 0)
    {
        $success = 0;
        $this->autoRender = false;
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        if ($this->qualifcations->query("delete from qualifcations where qualification_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function deletepassports($pkey = 0)
    {
        $success = 0;
        $this->autoRender = false;
        $this->passport->useDbConfig = $this->Session->read('ds');
        if ($this->passport->query("delete from emp_passport_visa where emp_passport_visa_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function deletehist($pkey = 0)
    {
        $success = 0;

        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        if ($this->history->query("delete from history where history_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function deletefdetails($pkey = 0)
    {
        $success = 0;
        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        $details = $this->history->query("SELECT `emp_fkey`, `emergency_contact` FROM `emp_family` WHERE `emp_family_pkey` = '$pkey'");
        $emp_pkey = $details[0]['emp_family']['emp_fkey'];
        $emergency_contact = $details[0]['emp_family']['emergency_contact'];
        if ($emergency_contact == "Y") {
            $this->history->query("UPDATE `settings_runner` SET `exit_status` = 'Next_time' WHERE `emp_fkey` = '$emp_pkey'");
        }
        if ($this->history->query("delete from emp_family where emp_family_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function mark_nominee($pkey = 0, $emp = 0)
    {
        $success = 0;

        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        if ($this->history->query("UPDATE emp_family set is_nominee = 'N' where emp_fkey in('$emp') ")) {
            $success = 1;
        }
        if ($this->history->query("UPDATE emp_family set is_nominee = 'Y' where emp_family_pkey = '$pkey' ")) {
            $success = 1;
        }
    }

    public function mark_emergency($pkey = 0, $emp = 0)
    {
        $resp = array();
        $resp["success"] = false;

        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        $is_contact_exist = $this->history->query("select contact_number,alternate_number from emp_family where emp_family_pkey=$pkey");
        $num1 = $is_contact_exist[0]['emp_family']['contact_number'];
        $num2 = $is_contact_exist[0]['emp_family']['alternate_number'];
        if ($num1 != null || $num2 != null) {
            if ($this->history->query("UPDATE emp_family set emergency_contact = 'N' where emp_fkey in('$emp') ")) {
                $resp["success"] = true;
            }
            if ($this->history->query("UPDATE emp_family set emergency_contact = 'Y' where emp_family_pkey = '$pkey' ")) {
                $resp["success"] = true;
            }

            $setting_key = $this->history->query("select settings_pkey from genaral_setings where description='Emargency Number Collection'");
            $settings_fkey = $setting_key[0]['genaral_setings']['settings_pkey'];
            $exist_check = $this->history->query("select * from settings_runner where emp_fkey=$emp");
            if (isset($exist_check[0]['settings_runner'])) {
                $updated = $exist_check[0]['settings_runner']['updated_times'] + 1;
                $this->history->query("update settings_runner set exit_status='Finished',updated_times=$updated,modification_date=now() where emp_fkey=$emp and settings_fkey=$settings_fkey");
                $resp["success"] = true;
            } else {
                $this->history->query("insert into settings_runner(settings_fkey,emp_fkey,exit_status,updated_times) values($settings_fkey,$emp,'Finished',0)");
                $resp["success"] = true;
            }
        } else {
            $resp["msg"] = "Please select any family member with contact number.";
        }
        echo json_encode($resp);
    }

    public function importProfile($id = 0)
    {
        $this->layout = null;
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
        $company_code=$this->Session->read('company_code');
        // if($company_code=='HDFN' || $company_code=='HDEQ' || $company_code=='HDSC' || $company_code=='GLET' || $company_code=='GAAR' || $company_code=='NRMY' || $company_code=='TRCK' || $company_code=='MRZC'  || $company_code=='DYGL' || $company_code=='SHIN' || $company_code=='AMST' || $company_code=='NWTR' || $company_code=='THNG' || $company_code=='CSMT'){
    if (!in_array($company_code, [
    'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN',
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','VNDG'
])) {
           $this->render('import_profile_join');
        }
        else{
            $this->render('import_profile');
        }
    }

    function sendRequestOTP($empProfileid = '', $email = '')
    {

        $response_histor_add = $this->getDataFromAPI($empProfileid);
        // $this->layout = null;
        //debug($response_histor_add);
        $arr_reponse = "";
        $this->set('profileID', $empProfileid);

        if (json_decode($response_histor_add)->status != 200) {
            $arr_reponse = array();
            $this->set('generatedOTP', array("generatedOTP" => 0, "EmpProfileID" => $empProfileid));
            $this->render('sendrequestotp');
        } else {
            $arr_reponse = json_decode($response_histor_add);

            App::import('Vendor', 'FirebaseNotification', array('file' => 'FirebaseNotification.php'));

            $FCM = new FirebaseNotification();
            //edited by megha for getting missing email input from form. 23-05-2025
            $email = json_decode($response_histor_add)->employeeInfo->employeeDetails->email;
            $userData = $this->getUID($empProfileid, $email);
            if (json_decode($userData)->status == 200) {

                if (json_decode($userData)->uIPushNotificationKey) {
                    $generatedOTP = rand();

                    $FCM->heading = "Data Sharing Request";
                    $FCM->body = "Hello, Use this OTP to access the permission to share your data. The OTP is " . $generatedOTP;

                    $FCM->sendFCM(json_decode($userData)->uIPushNotificationKey);

                    $this->set('generatedOTP', array("generatedOTP" => $generatedOTP, "EmpProfileID" => $empProfileid));
                } else {
                    $this->set('generatedOTP', array("generatedOTP" => 0, "EmpProfileID" => $empProfileid));
                }
            }



            $this->render('sendrequestotp');
        }
    }


    function testFCM()
    {

        App::import('Vendor', 'FirebaseNotification', array('file' => 'FirebaseNotification.php'));

        $FCM = new FirebaseNotification();

        $FCM->heading = "Notification Heading";
        $FCM->body = "Hello";

        $FCM->sendFCM('cZkIvOMnR_eGWKMB-rnzFc:APA91bEU8mqfxxo_Hwmreao_AwZUI8SC5aFB42lsWrdrGuIVLG6TlXluPsw0roptJG-L1eyO8i98lEDJP4va7S4ofu9UzmOde8hXbl-BU4tGgFOaUU5k3uhEi7yJi_y3gtg46CbgBcLR');

        $this->autoRender = false;
    }

    function sendFCM($id = '', $body = '', $title = '')
    {

        $this->autoRender = false;

        $registrationIds = array('cZkIvOMnR_eGWKMB-rnzFc:APA91bEU8mqfxxo_Hwmreao_AwZUI8SC5aFB42lsWrdrGuIVLG6TlXluPsw0roptJG-L1eyO8i98lEDJP4va7S4ofu9UzmOde8hXbl-BU4tGgFOaUU5k3uhEi7yJi_y3gtg46CbgBcLR');

        // prep the bundle
        $msg = array(
            'message' => 'This is an test messgae',
            'title'     => 'This is a title. title',
            'subtitle'  => 'This is a subtitle. subtitle',
            'tickerText'    => 'Ticker text here...Ticker text here...Ticker text here',
            'vibrate'   => 1,
            'sound'     => 1,
            'largeIcon' => 'large_icon',
            'smallIcon' => 'small_icon'
        );

        $fields = array(
            // use this to method if want to send to topics
            // 'to' => 'topics/all'
            "notification" => [
                "body" => "Hello",
                "title" => 'Hello test notifications',
                "icon" => "ic_launcher"
            ],
            'registration_ids'  => $registrationIds,
            'data' => $msg
        );

        $headers = array(
            'Authorization: key=AAAA4J_UTUg:APA91bGPDB3OZAPWQOGrn7cUJ7ypsoKwI3tkdTjmIdLpCijnwC3KxNECA1LJ1rghgUdcVqLzcPRiv7vSy1KExN46Ja5aKvI4ueDYj6TLqEtC4d2oGW-0Opv-DdD8ar2uRa9T8vtH1r8V',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send ');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

        var_dump($result);
    }

    public function requestPermissionAccess($profileID = '', $branchCode = '')
    {

        $response_histor_add = $this->getDataFromAPI($profileID);
        // $this->layout = null;

        // debug(json_decode($response_histor_add));
        $arr_reponse = "";
        $this->set('profileID', $profileID);

        if (json_decode($response_histor_add)->status != 200) {
            $arr_reponse = array();
        } else {
            $arr_reponse = json_decode($response_histor_add);
        }

        $this->set('arr_reponse', $arr_reponse);
        $company_code=$this->Session->read('company_code');
        //  if($company_code=='HDFN' || $company_code=='HDEQ' || $company_code=='HDSC' || $company_code=='GLET' || $company_code=='GAAR' || $company_code=='NRMY' || $company_code=='TRCK' || $company_code=='MRZC'  || $company_code=='DYGL' || $company_code=='SHIN' || $company_code=='AMST' || $company_code=='NWTR' || $company_code=='THNG' || $company_code=='CSMT'){
        if (!in_array($company_code, [
    'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN',
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','VNDG'
])) {
        $this->render('getprofileinfojoin');
         }
         else{
            $this->render('getprofileinfo');
         }
    }

    public function getprofileinfo($profileID = '', $branchCode = '')
    {

        $response_histor_add = $this->getDataFromAPI($profileID);
        // $this->layout = null;

        // debug(json_decode($response_histor_add));
        $arr_reponse = "";
        $this->set('profileID', $profileID);
        $this->set('branchCode', $branchCode);

        if (json_decode($response_histor_add)->status != 200) {
            $arr_reponse = array();
        } else {
            $arr_reponse = json_decode($response_histor_add);
        }

        $this->set('arr_reponse', $arr_reponse);
        $company_code=$this->Session->read('company_code');
    //    if($company_code=='HDFN' || $company_code=='HDEQ' || $company_code=='HDSC' || $company_code=='GLET' || $company_code=='GAAR' || $company_code=='NRMY' || $company_code=='TRCK' || $company_code=='MRZC'  || $company_code=='DYGL' || $company_code=='SHIN' || $company_code=='AMST' || $company_code=='NWTR' || $company_code=='THNG' || $company_code=='CSMT'){
  if (!in_array($company_code, [
    'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN',
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','VNDG'
])) {
        $this->render('getprofileinfojoin');
         }
         else{
            $this->render('getprofileinfo');
         }
    }

    function importEmployeetoDatabase()
    {
        $this->autoRender = false;

        $arr_request_data = $this->request->data;
        $response_histor_add = $this->getDataFromAPI($arr_request_data['profileID']);
        // $this->layout = null;

        $company_code = $this->Session->read('company_code');

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $this->passport->useDbConfig = $this->Session->read('ds');
        $this->Family->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->history->useDbConfig = $this->Session->read('ds');
        $arr_reponse = "";
        $responseData = json_decode($response_histor_add);

        if ($responseData->status == 200) {
            $arr_employee_data = get_object_vars($responseData->employeeInfo->employeeDetails);
            $qualification = $responseData->employeeInfo->qualification;
            $passportVisa = $responseData->employeeInfo->passportVisa;
            $family = $responseData->employeeInfo->family;
            $history = $responseData->employeeInfo->workExp;
            // var_dump($arr_employee_data);
            $result = $this->EmployeeDetails->save(array(
                "company_code" => $company_code,
                // "branch_code" => $arr_request_data['branchCode'],
                "emp_id" => "",
                "first_name" => isset($arr_employee_data['firstName']) ? $arr_employee_data['firstName'] : '',
                "last_name" => isset($arr_employee_data['lastName']) ? $arr_employee_data['lastName'] : '',
                "middile_name" => isset($arr_employee_data['middleName']) ? $arr_employee_data['middleName'] : '',
                "classification" => $arr_employee_data['gender'] == 'M' ? 'male' : 'female',
                "address" => isset($arr_employee_data['address']) ? $arr_employee_data['address'] : '',
                "city" => isset($arr_employee_data['city']) ? $arr_employee_data['city'] : '',
                "state" => isset($arr_employee_data['state']) ? $arr_employee_data['state'] : '',
                "nationality_id" => isset($arr_employee_data['nationality']) ? $arr_employee_data['nationality'] : '',
                "pincode" => isset($arr_employee_data['pincode']) ? $arr_employee_data['pincode'] : '',
                "mobile_no" => isset($arr_employee_data['mobileNo']) ? $arr_employee_data['mobileNo'] : '',
                "email" => isset($arr_employee_data['email']) ? $arr_employee_data['email'] : '',
                "maritual_status" => isset($arr_employee_data['maritalStatus']) ? $arr_employee_data['maritalStatus'] : '',
                "education" => isset($arr_employee_data['education']) ? $arr_employee_data['education'] : '',
                "date_of_birth" => isset($arr_employee_data['dateOfBirth']) ? date("Y-m-d", strtotime($arr_employee_data['dateOfBirth'])) : '',
                "bank_name" => isset($arr_employee_data['bankName']) ? $arr_employee_data['bankName'] : '',
                "branch_name" => isset($arr_employee_data['bankBranchName']) ? $arr_employee_data['bankBranchName'] : '',
                "branch_address" => isset($arr_employee_data['branchAddress']) ? $arr_employee_data['branchAddress'] : '',
                "name_as_per_bank" => isset($arr_employee_data['nameAsPerBank']) ? $arr_employee_data['nameAsPerBank'] : '',
                "ifsc_code" => isset($arr_employee_data['ifscCode']) ? $arr_employee_data['ifscCode'] : '',
                "account_no" => isset($arr_employee_data['accountNo']) ? $arr_employee_data['accountNo'] : '',
                "pf" => isset($arr_employee_data['pf']) ? $arr_employee_data['pf'] : '',
                "company_pf" => isset($arr_employee_data['companyPf']) ? $arr_employee_data['companyPf'] : '',
                "esi_dispensary" => isset($arr_employee_data['esiDispensary']) ? $arr_employee_data['esiDispensary'] : '',
                "esi" => isset($arr_employee_data['esi']) ? $arr_employee_data['esi'] : '',
                "id_card" => isset($arr_employee_data['idCard']) ? $arr_employee_data['idCard'] : '',
                "guradian" => isset($arr_employee_data['guardian']) ? $arr_employee_data['guardian'] : '',
                "relation_guardian" => isset($arr_employee_data['relationGuardian']) ? $arr_employee_data['relationGuardian'] : '',
                "pan_no" => isset($arr_employee_data['panNo']) ? $arr_employee_data['panNo'] : '',
                "name_as_on_pan" => isset($arr_employee_data['nameAsOnPan']) ? $arr_employee_data['nameAsOnPan'] : '',
                "name_as_on_aadhaar" => isset($arr_employee_data['nameAsOnAadhaar']) ? $arr_employee_data['nameAsOnAadhaar'] : '',
                "parent" => isset($arr_employee_data['parent']) ? $arr_employee_data['parent'] : '',
                "hearing" => isset($arr_employee_data['hearing']) ? $arr_employee_data['hearing'] : '',
                "visual" => isset($arr_employee_data['visual']) ? $arr_employee_data['visual'] : "",
                "physical_handicap" => isset($arr_employee_data['physicalHandicap']) ? $arr_employee_data['physicalHandicap'] : '',
                "previous_member_id" => isset($arr_employee_data['previousMemberId']) ? $arr_employee_data['previousMemberId'] : '',
                "blood" => isset($arr_employee_data['blood']) ? $arr_employee_data['blood'] : "",
                "locomotive" => isset($arr_employee_data['locomotive']) ? $arr_employee_data['locomotive'] : '',
                "status" => 4,
                "international_worker" => isset($arr_employee_data['internationalWorker']) ? $arr_employee_data['internationalWorker'] : ''
            ));


            if (!empty($result)) {
                $message = 'Personal Details Saved Successfully';
                $pkey = $this->EmployeeDetails->getLastInsertID();

                // $this->addToNotice($arr_form_data['notice_days'], $pkey);

                $company_key = $this->Session->read('company_key');
                $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                if ($punch_type && $punch_type == 'device') {
                    //Device available, so generate emp id concatenate with device id and emp id from device
                    $arr_user_cred = array();
                    if ($pkey > 0) {

                        $arr_form_data['emp_fkey'] = $pkey;

                       // try {
                            //Save to employee proffessionals table
                            $user_group = $this->Session->read("user_group");
                            $curr_user_id = $this->Session->read('login_user_id');
                            //if (isset($user_group) && $user_group == 2) {
                            $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('GRADE','$pkey','','$curr_user_id') ");
                            //}

                            $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                            //Insert user credentials  
                            //Get company_code 
                            $str_company_code = $this->Session->read('company_code');

                            $emp_username = ''; //No device details here on manually entering emp data 

                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = $emp_username;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_employee_data['firstName'];
                            $arr_user_cred['last_name'] = $arr_employee_data['lastName']; // Removed Field. by Arul P Das on 20-6-21
                            $arr_user_cred['middle_name'] = $arr_employee_data['middleName']; // Removed Field. by Arul P Das on 20-6-21
                            $arr_user_cred['name_as_per_bank'] = $arr_employee_data['nameAsPerBank']; // Commonly one name takes for every name fields. updated on 25/06/2021
                            $arr_user_cred['name_as_on_aadhaar'] = $arr_employee_data['nameAsOnAadhaar']; // Commonly one name takes for every name fields. updated on 25/06/2021
                            $arr_user_cred['name_as_on_pan'] = $arr_employee_data['nameAsOnPan']; // Commonly one name takes for every name fields. updated on 25/06/2021


                            $arr_user_cred['email'] = $arr_employee_data['email'];
                            $arr_user_cred['phone'] = $arr_employee_data['mobileNo'];
                           // try { 
                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                $result = $this->UserCredentials->save($arr_user_cred);
                            // } catch (Exception $ex) {
                            //     $this->restsave($pkey);
                            //     $message = 'UserCredentials Details Saving Failed';
                            //     if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                            //         $message = 'User Already Exists With the same COMPANYID';
                            //     return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                            // }
                        // } catch (Exception $ex) {
                        //     $this->restsave($pkey);
                        //     $message = 'Proffessioanl Details Saving Failed';
                        //     if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                        //         $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                        //     return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                        // }
                    }
                } else {
                    //Manually generate emp id for companies without device 
                    $arr_user_cred = array();
                    $arr_form_data['emp_fkey'] = $pkey;
                    //Save to employee proffessionals table
                   // try {
                        $user_group = $this->Session->read("user_group");
                        if (isset($user_group) && $user_group == 2) {
                            $arr_form_data['attr1'] = $cur_emp_key = $this->Session->read("emp_fkey");
                            $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('HIERARCHY','$pkey','$cur_emp_key','$cur_emp_key') ");
                        }

                        $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                        if ($pkey > 0) {
                            //Insert user credentials  

                            $str_company_code = $this->Session->read('company_code');
                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// */ //rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_employee_data['firstName'];
                            $arr_user_cred['last_name'] =  ''; // Removed field. by Arul P Das on 20_6_21
                            $arr_user_cred['middle_name'] =  ''; // Removed field. by Arul P Das on 20_6_21
                            $arr_user_cred['name_as_per_bank'] = $arr_employee_data['nameAsPerBank']; // Commonly one name takes for every name fields. updated on 25/06/2021
                            $arr_user_cred['name_as_on_aadhaar'] = $arr_employee_data['nameAsOnAadhaar']; // Commonly one name takes for every name fields. updated on 25/06/2021
                            $arr_user_cred['name_as_on_pan'] = $arr_employee_data['firstName']; // Commonly one name takes for every name fields. updated on 25/06/2021								
                            $arr_user_cred['email'] = $arr_employee_data['email'];
                            $arr_user_cred['phone'] = $arr_employee_data['mobileNo'];

                          //  try {
                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                $result = $this->UserCredentials->save($arr_user_cred);
                                //Call procedure 'Linkemp_deviceanddatabase'
                                $outputParameter = array();
                                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                                $outputParameter[] = isset($arr_form_data['emp_branch']) ? "'" . $arr_form_data['emp_branch'] . "'" : "''";
                                $outputParameter[] = "''";
                                try {
                                    $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
                                    $company = strtoupper($this->Session->read('company_code'));
                                    if ($company == 'VSFS') {
                                        $empdeviceid = $this->UserCredentials->query("select SUBSTRING(max(trim(emp_company_id)),3, 10) as deviceid from emp_proff 
                                            where emp_company_id like '%VS%' AND emp_company_id NOT REGEXP 'VSFS' and emp_fkey!=$pkey");
                                        $empdeviceid1 = $empdeviceid['0']['0']['deviceid'];
                                        $empdeviceid1++;
                                        $this->EmployeeProfessionalDetails->query("update emp_proff set emp_company_id = concat('VS',$empdeviceid1) where emp_fkey=$pkey");
                                    }
                                } catch (Exception $ex) {
                                    $this->restsave($pkey);
                                    $message = 'Link Employee Details Saving Failed';
                                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                                }
                            // } catch (Exception $ex) {
                            //     $this->restsave($pkey);
                            //     $message = 'UserCredentials Details Saving Failed';
                            //     if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                            //         $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                            //     return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                            // }
                        }
                    // } catch (Exception $ex) {
                    //     $this->restsave($pkey);
                    //     $message = 'Proffessioanl Details Saving Failed';
                    //     if (substr($ex->getMessage(), 0, 15) == "SQLSTATE[23000]")
                    //         $message = 'User Already Exists With the same COMPANYID try again, or contact the administrator';
                    //     return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                    // }
                }
                  //added by megha on 27-06-2025
                foreach ($history as $key => $value) {
                    $result = $this->history->saveAll(array(
                        "emp_fkey" => $pkey,
                        "company" => $value->companyName,
                        "from_date" => date("Y-m-d", strtotime($value->startDate)),
                        "to_date" => date("Y-m-d", strtotime($value->endDate)),
                        "designation" => $value->designation,
                        "department" => $value->department,
                        "salary" => $value->salary,
                        "status" => 1,
                    )); 
                }
                //end
                foreach ($qualification as $key => $value) {
                    $result = $this->qualifcations->saveAll(array(
                        "emp_fkey" => $pkey,
                        "course" => $value->course,
                        "university" => $value->university,
                        "duration" => 1,
                        "mark" => $value->mark
                    ));
                    // debug($result);
                }

                foreach ($passportVisa as $key => $value) {
                    $result = $this->passport->saveAll(array(
                        "emp_fkey" => $pkey,
                        "document_type" => $value->documentType,
                        "document_number" => $value->documentNumber,
                        "classification" => $value->classification,
                        "name" => $value->name,
                        "valid_till" => date("Y-m-d", strtotime($value->validTo)),
                        "relation" => $value->relation,
                        "valid_from" => date("Y-m-d", strtotime($value->validFrom)),
                        "nationality" => $value->nationality,
                        "remarks" => $value->remarks,
                        "reccuring" => $value->reccuring,
                    ));
                }

                foreach ($family as $key => $value) {
                    $result = $this->Family->saveAll(array(
                        "emp_fkey" => $pkey,
                        "name" => $value->name,
                        "DOB" => date("Y-m-d", strtotime($value->dateOfBirth)),
                        "gender" => $value->gender,
                        "blood_group" => $value->bloodGroup,
                        "relation" => $value->relation,
                        "nationality" => $value->nationality,
                        "emergency_contact" => $value->emergencyContact,
                        "alternate_number" => $value->alternateNumber,
                        "contact_number" => $value->contactNumber,
                        "is_nominee" => $value->isNominee,
                    ));
                }
            }
        } else {
            $arr_reponse = json_decode($response_histor_add);
        }

        $message = 'UserCredentials Details Saved';
        return json_encode(array('success' => true, 'error' => "", 'pkey' => $pkey, 'message' => $message));
    }


    public function getDataFromAPI($profileID)
    {
        $this->autoRender = false;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online/thirdpartyapi/empDetails/fullInfo?profileId=' . $profileID, //FT114
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function getUID($profileID, $email)
    {
        $this->autoRender = false;
        $curl = curl_init();

        $payloadata = array(
            "profileId" => $profileID,
            "email" => $email //"theinteractivecode@gmail.com"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://myportalapi.mypayrollmaster.online//thirdpartyapi/userDetails/pushNotificationKeyVerify', //FT114
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payloadata),
            CURLOPT_HTTPHEADER => array(
                'username: profileadmin',
                'password: admin&*()',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    //To show emoloyee bulk import response and showing not inserted entries list
    public function showimportresponse($importedCount = '', $strDuplicateEmpKeys = '', $errors = '')
    {
        //debug($errors);
        $this->set('importedCount', $importedCount);

        if ($importedCount > 0) {
            $this->set('title', 'Employees imported successfully');
        } else {
            $this->set('title', 'Sorry, not imported any employees');
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arrDuplicateEmpList = array();
        if ($strDuplicateEmpKeys != '') {
            $arrDuplicateEmpKeys = explode(',', urldecode($strDuplicateEmpKeys));
            $arrDuplicateEmpList = $this->EmployeeDetails->find('all', array('fields' => 'first_name,last_name,date_of_birth', 'conditions' => array('emp_pkey' => $arrDuplicateEmpKeys)));
        }
        $arrerrors = array();
        if ($errors != '' && $errors != '0') {
            $arrerrors = explode(',', urldecode($errors));
        }
        $this->set('arrerrors', $arrerrors);
        $this->set('arrDuplicateEmpList', $arrDuplicateEmpList);
    }

    public function sendpasswordemail($userid)
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->addAddress('info@mypayrollmaster.in');     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "New Employee Added To Payroll";
            $mail->Body = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
	<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
    <tbody>
        <tr>
        	<td>
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:100%!important">
                <tbody>
                	<tr>
                    	<td>
                			<table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                            <tbody>
                            	<tr>
                                    <td colspan="3" height="80" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="padding:0;margin:0;font-size:0;line-height:0">
                                        <table width="690" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td width="30"></td>
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.online/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="codexworld" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(65, 132, 243);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Employee Added to<font style="color:#fff;">' . $companyname . '</font> </h1>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.online is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.online/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
                                 	</tbody>
                                    </table>
                             	</td>
                   			</tr>
                            
                            <tr bgcolor="#ffffff">
                                <td width="30" bgcolor="#eeeeee"></td>
                                <td>
                                    <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Your Login Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/anyone.png" alt="tool" width="120" height="120"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Emp Pkey</h3>
                                                <div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                <div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">'
                . $userid .
                '</div>
                                                <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                          	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/IaaS-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Organisation</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $companyname . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                  	</tbody>
                                    </table>
                                    <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td>
                                            	<h2 style="color:#404040;font-size:22px;font-weight:bold;line-height:26px;padding:0;margin:0">&nbsp;</h2>
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.online/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
                                      </tr></tbody></table></td>
                                <td width="30" bgcolor="#eeeeee"></td>
                            </tr>
                          	</tbody>
                            </table>
                  			<table align="center" width="750px" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:750px!important">
                            <tbody>
                            	<tr>
                                	<td>
                                        <table width="630" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                                        <tbody>
                                        	<tr><td colspan="2" height="30"></td></tr>
                                            <tr>
                                            	<td width="360" valign="top">
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
                                              	</td>
                                            </tr>
                                            <tr><td colspan="2" height="5"></td></tr>
                                           
                                      	</tbody>
                                        </table>
                                   	</td>
                  				</tr>
                          	</tbody>
                            </table>
                  		</td>
                	</tr>
              	</tbody>
                </table>
            </td>
		</tr>
 	</tbody>
    </table>
</div>';

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    /*
     * Update salary structure for employee, for the current salary structure
     * On 20 Sep 2016
     */
    //arun 15-10-2016 based on ashoakn
    //    public function updateSalStructureDistributionFn($emp_fkey = 0) {
    //        if (!empty($emp_fkey)) {
    //            //Call sal_structure_distribution_fn starts
    //            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
    //            $arr_emp_structure_id = Set::extract('/EmployeeProfessionalDetails/.', $this->EmployeeProfessionalDetails->find('first', array(
    //                                'fields' => 'structure_id',
    //                                'conditions' => array('emp_fkey' => $emp_fkey)
    //                                    )
    //                            )
    //            );
    //            //debug($arr_emp_structure_id);
    //            $emp_structure_id = isset($arr_emp_structure_id[0]['structure_id']) ? $arr_emp_structure_id[0]['structure_id'] : '';
    //            if (!empty($emp_structure_id)) {
    //                $company_code = $this->Session->read('company_code');
    //                $login_user_id = $this->Session->read('login_user_id');
    //                $query_sal_structure_distribution_fn = "SELECT sal_structure_distribution_fn('$company_code', $emp_fkey, $emp_structure_id,'$login_user_id') AS resp";
    //                //echo $query_sal_structure_distribution_fn;
    //                $resp_sal_structure_distribution_fn = $this->EmployeeCTC->query($query_sal_structure_distribution_fn);
    //                $isSuccess = isset($resp_sal_structure_distribution_fn[0][0]['resp']) ? $resp_sal_structure_distribution_fn[0][0]['resp'] : 0;
    //                if ($isSuccess) {
    //                    //Need to call procedure to update salary structure vales for the employee, 
    //                    //by the current salary structure : PROGRESSING
    //                }
    //            }
    //            //Call sal_structure_distribution_fn ends
    //        }
    //    }
    //Ends



    public function downloadResume($emp_fkey = 0)
    {
        $this->autoRender = FALSE;

        $emp_pkey  = $emp_fkey;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_info = $this->EmployeeDetails->query("SELECT * FROM `comp_contact_info` WHERE `id` = 1 ");

        require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

        // App::import('Vendor', 'TCPDF-main/examples', array('file' => 'tcpdf_include.php'));
        // $mail = new PHPMailer;

        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        define('COMPANY_NAME', $company_info['0']['comp_contact_info']['business_name']);
        define('COMPANY_MAIL', $company_info['0']['comp_contact_info']['email']);
        define('COMPANY_URLS', $company_info['0']['comp_contact_info']['email'] . "\n" . $company_info['0']['comp_contact_info']['website']);
        $current_url = "https://" . $_SERVER['HTTP_HOST'];
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($company_info['0']['comp_contact_info']['business_name']);
        $pdf->SetTitle($company_info['0']['comp_contact_info']['business_name']);
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        // $headerLogo = 'https://login.mypayrollmaster.online/newlogin/img/logo.png';
        $headerLogo = $current_url . '/' . $company_info['0']['comp_contact_info']['logo'];

        // set default header data
        $pdf->SetHeaderData($headerLogo, '20', COMPANY_NAME, COMPANY_URLS, array(0, 64, 255), array(0, 64, 128));
        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)

        if (@file_exists(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"))) {
            require_once(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"));
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', 14, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect
        $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));

        $prof_details = $this->EmployeeDetails->query("SELECT * FROM `employee_structure_vview` WHERE `emp_pkey` = $emp_pkey ");

        $arr_educations = $this->EmployeeDetails->query("SELECT * FROM `qualifcations` WHERE `emp_fkey` = $emp_pkey ");

        $arr_emp_proffs = $this->EmployeeDetails->query("SELECT * FROM `emp_proff` WHERE `emp_fkey` = $emp_pkey ");

        $arr_workExperiences = $this->EmployeeDetails->query("SELECT * FROM `history` WHERE `emp_fkey` = $emp_pkey ");

        $user_image = $this->EmployeeDetails->query("SELECT avatar FROM `user_credentials` WHERE `emp_fkey` = $emp_pkey ");

        $image = '';
        $avtar = isset($user_image['0']['user_credentials']['avatar']) ? $user_image['0']['user_credentials']['avatar'] : '';
        if ($avtar == 'null' || $avtar == null) {
            if ($arr_emp_details['EmployeeDetails']['classification'] == 'male') {
                // $image = 'https://v1.mypayrollmaster.online/mpm/img/placeholdermen.jpeg';
                $image = $current_url . '/img/placeholdermen.jpeg';// Edited by Akshay on 27-8-2025
            } else {
                $image = 'https://cdn4.vectorstock.com/i/thumb-large/52/83/default-placeholder-profile-icon-vector-14065283.jpg';
            }
        } else {
            // $image = 'https://v1.mypayrollmaster.online/' . $avtar;
            $image = $current_url . '/' . $avtar;
            $image = str_replace(' ', '%20', $image);
            //   $image = 'https://login.mypayrollmaster.online/'. $user_image['0']['user_credentials']['avatar'];
        }

        // -----------------------------------------------------------------------------

        // Set some content to print
        $html = '
        <h1 style="text-align: center; ">' . $arr_emp_details['EmployeeDetails']['first_name'] . ' ' . $arr_emp_details['EmployeeDetails']['last_name'] . '</h1>
        <table style="width: 100%; " cellspacing="0" cellpadding="0" border="0">
 			<tr>
 				<td colspan="2">
 					<div style="font-size: 12px; " >';


        if ($arr_emp_details['EmployeeDetails']['address'] != null || $arr_emp_details['EmployeeDetails']['address'] != '') {
            $html .= '<b >Address: </b> ' . $arr_emp_details['EmployeeDetails']['address'] . ', ' . $arr_emp_details['EmployeeDetails']['city'] . ', ' . $arr_emp_details['EmployeeDetails']['state'] . ', ' . $arr_emp_details['EmployeeDetails']['pincode'] . '';
        }


        if ($arr_emp_details['EmployeeDetails']['classification'] != null || $arr_emp_details['EmployeeDetails']['classification'] != '') {
            $html .= '<p style="margin: 4px; " ><b >Gender: </b> ' . ucfirst($arr_emp_details['EmployeeDetails']['classification']) . '</p>';
        }

        if ($arr_emp_details['EmployeeDetails']['date_of_birth'] != null || $arr_emp_details['EmployeeDetails']['date_of_birth'] != '') {
            $html .= '<p style="margin: 4px; " ><b >Birth Date: </b> ' . $arr_emp_details['EmployeeDetails']['date_of_birth'] . '</p>';
        }

        //                     if($arr_emp_details['EmployeeDetails']['mobile_no'] != null || $arr_emp_details['EmployeeDetails']['mobile_no'] != '') {
        //                     $html .= '<p style="margin: 4px; " ><b >Phone: </b> ' . $arr_emp_details['EmployeeDetails']['mobile_no'] . '</p>';
        //                     }
        if ($arr_emp_details['EmployeeDetails']['email'] != null || $arr_emp_details['EmployeeDetails']['email'] != '') {
            $html .= '<p style="margin: 4px; " ><b >Email: </b> ' . $arr_emp_details['EmployeeDetails']['email'] . '</p>';
        }
        $html .= '</div>
 				</td>
 				<td style="text-align: right; ">
                    <div style="font-size: 12px; " >
			        	<img style="width: 120px; height: 140px; object-fit: contain; " src="' . $image . '">
			        </div>
 				</td>
 			</tr>
        </table>
        <hr>
        ';

        $htmlHistories = '
        <h4>Education</h4>
	 	<table style="width: 100%; font-size: 12px; line-height: 28px; text-align: center; " cellspacing="0" cellpadding="0" border="1">
	 		<tr>
	 			<th style="font-weight: bold; " >Course</th>
	 			<th style="font-weight: bold; " >University/college</th>
	 			<th style="font-weight: bold; " >Duration</th>
	 			<th style="font-weight: bold; " >Percentage/Marks</th>
	 		</tr>';

        foreach ($arr_educations as $key => $value) {
            $htmlHistories .= '<tr>
	 			<td>' . $value['qualifcations']['course'] . '</td>
	 			<td>' . $value['qualifcations']['university'] . '</td>
	 			<td>' . $value['qualifcations']['duration'] . '</td>
	 			<td>' . $value['qualifcations']['mark'] . '</td>
	 		</tr>';
        }

        $htmlHistories .= '</table>';

        $htmlExperiences = '
        <br>
        <h4>Experience</h4>

        <table style="width: 100%; font-size: 12px; line-height: 28px; text-align: center; " cellspacing="0" cellpadding="0" border="1">
        	<tr>
        		<th style="font-weight: bold; " >Company</th>
        		<th style="font-weight: bold; " >Department</th>
        		<th style="font-weight: bold; " >Designation</th>
        		<th style="font-weight: bold; " >From Date</th>
        		<th style="font-weight: bold; " >To Date</th>
        		<th style="font-weight: bold; " >CTC</th>
        	</tr>';

        foreach ($arr_workExperiences as $key => $value) {
            $htmlExperiences .= '
                <tr>
                    <td>' . $value['history']['company'] . '</td>
                    <td>' . $value['history']['department'] . '</td>
                    <td>' . $value['history']['designation'] . '</td>
                    <td>' . $value['history']['from_date'] . '</td>
                    <td>' . $value['history']['to_date'] . '</td>
                    <td>' . $value['history']['salary'] . '</td>
                </tr>';
        }

        $htmlExperiences .= '</table>';


        $tbl =
            '
        <table style="width: 100%; font-size: 12px; line-height: 28px; " cellspacing="0" cellpadding="0" border="1">
            <tr>
                
                <td style="padding: 4px; "><b> Joining Date: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['joining_date'] . '</td>
                     <td style="padding: 4px; "><b> Employee ID: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['employee_id'] . '</td>
                     
            </tr>
            <tr>
                <td style="padding: 4px; "><b> Department: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['department'] . '</td>
                     <td style="padding: 4px; "><b> Designation: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['designation'] . '</td>
            </tr>
            <tr>
             
                  <td style="padding: 4px; "><b> Branch: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['branch'] . '</td>
                   <td style="padding: 4px; "><b> Employee Type: </b></td><td> ' . $arr_emp_proffs['0']['emp_proff']['emp_type'] . '</td>
<!--               <td style="padding: 4px; "><b> Site Customer: </b></td><td> Balachandran</td>-->
            </tr>
            <tr>
                <td style="padding: 4px; "><b> Phone Number: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['mobile_no'] . '</td>
                <td style="padding: 4px; "><b> Grade: </b></td><td>  </td>
            </tr>
        </table>

        <div></div>
        
        <table style="width: 100%; font-size: 12px; border-color: #ccc; line-height: 28px; " bordercolor="#ccc" cellspacing="0" cellpadding="0" border="1">
            <tr>
                <td><b> Adhaar ID: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['id_card'] . ' </td>
                <td><b> Blood Group: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['blood'] . '</td>
            </tr>
            <tr>
                <td><b> Guardian Name: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['guradian'] . '</td>
                <td><b> Relation: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['relation_guardian'] . '</td>
            </tr>
            <tr>
                <td><b> Martial Status: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['maritual_status'] . '</td>
                <td><b> Education: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['education'] . '</td>
            </tr>
            <tr>
                <td><b> ESI: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['esi'] . ' </td>
                <td><b> UAN: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['pf'] . '</td>
            </tr>
            <tr>
                <td><b> PF: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['company_pf'] . ' </td>
                <td><b> PAN: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['pan_no'] . ' </td>
            </tr>
        </table>

        <div></div>
        
        <table style="width: 100%; font-size: 12px; line-height: 28px; " cellspacing="0" cellpadding="0" border="1">
            <tr>
                <td><b> Bank Name: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['bank_name'] . ' </td>
                <td><b>  Branch: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['branch_name'] . ' </td>
            </tr>
            <tr>
                <td><b> IFSC: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['ifsc_code'] . ' </td>
                <td><b> Account Number: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['account_no'] . ' </td>
            </tr>
        </table>
        <br><br><br><br><br><br><br><br><br><br><br><br>
        ';

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        $pdf->writeHTMLCell(0, 0, '', '', $tbl, 0, 1, 0, true, '', true);

        // $pdf->writeHTML($tbl, true, false, false, false, '');

        $pdf->writeHTMLCell(0, 0, '', '', $htmlHistories, 0, 1, 0, true, '', true);

        $pdf->writeHTMLCell(0, 0, '', '', $htmlExperiences, 0, 1, 0, true, '', true);

        // ---------------------------------------------------------

        $first_name = isset($arr_emp_details['EmployeeDetails']['first_name']) ? $arr_emp_details['EmployeeDetails']['first_name'] . ' ' . $arr_emp_details['EmployeeDetails']['last_name'] : 'example_001';

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        // $pdf->Output($first_name, 'I');
        $pdf->Output($first_name . '.pdf', 'D');

        //============================================================+
        // END OF FILE
        //============================================================+
    }




    // Edited by Bindu on 22-10-2025

    public function downloadResume2($emp_fkey = 0)
    {
        $this->autoRender = FALSE;

        $emp_pkey  = $emp_fkey;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_info = $this->EmployeeDetails->query("SELECT * FROM `comp_contact_info` WHERE `id` = 1 ");
        $company_code = $this->Session->read('company_code');

        require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

        App::import('Vendor', 'TCPDF-main/examples', array('file' => 'tcpdf_include.php'));
        // $mail = new PHPMailer;

        // create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        define('COMPANY_NAME', $company_info['0']['comp_contact_info']['business_name']);
        define('COMPANY_MAIL', $company_info['0']['comp_contact_info']['email']);
        define('COMPANY_URLS', $company_info['0']['comp_contact_info']['email'] . "\n" . $company_info['0']['comp_contact_info']['website']);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($company_info['0']['comp_contact_info']['business_name']);
        $pdf->SetTitle($company_info['0']['comp_contact_info']['business_name']);
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        // $headerLogo = 'https://login.mypayrollmaster.online/newlogin/img/logo.png';
        $headerLogo = 'https://mpmqa.mypayrollmaster.online/' . $company_info['0']['comp_contact_info']['logo'];

        // set default header data
        $pdf->SetHeaderData($headerLogo, '25', COMPANY_NAME, COMPANY_URLS, array(0, 64, 255), array(0, 64, 128));

        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)

        if (@file_exists(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"))) {
            require_once(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"));
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', 14, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();


        // set text shadow effect
        $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));

        $prof_details = $this->EmployeeDetails->query("SELECT * FROM `employee_structure_vview` WHERE `emp_pkey` = $emp_pkey ");


        $arr_educations = $this->EmployeeDetails->query("SELECT * FROM `qualifcations` WHERE `emp_fkey` = $emp_pkey ");

        $arr_emp_proffs = $this->EmployeeDetails->query("SELECT * FROM `emp_proff` WHERE `emp_fkey` = $emp_pkey ");

        $arr_workExperiences = $this->EmployeeDetails->query("SELECT * FROM `history` WHERE `emp_fkey` = $emp_pkey ");

        $termination_details = $this->EmployeeDetails->query("SELECT * FROM `termination` WHERE `emp_fkey` = $emp_pkey");

        $arr_workHistory = $this->EmployeeDetails->query("SELECT * FROM `emp_config_history` WHERE `emp_fkey` = $emp_pkey");

        $user_image = $this->EmployeeDetails->query("SELECT avatar FROM `user_credentials` WHERE `emp_fkey` = $emp_pkey ");

        $arr_usercredentials = $this->EmployeeDetails->query("SELECT * FROM `user_credentials` WHERE `emp_fkey` = $emp_pkey");

        $arr_details = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_pkey` = $emp_pkey");


        $grade = null;
        $arr_grade = [];

        $grade_id = isset($arr_emp_proffs[0]['emp_proff']['emp_grade'])
            ? $arr_emp_proffs[0]['emp_proff']['emp_grade']
            : null;

        if (!empty($grade_id)) {
            $arr_grade = $this->EmployeeDetails->query(
                "SELECT * FROM `grade` WHERE `grade_pkey` = " . (int)$grade_id
            );

            if (!empty($arr_grade[0]['grade']['grade_name'])) {
                $grade = $arr_grade[0]['grade']['grade_name'];
            }
        }

        $image = '';
        // $avtar = isset($user_image['0']['user_credentials']['avatar']) ? $user_image['0']['user_credentials']['avatar'] : '';
        // // debug($avtar);exit;
        // if ($avtar == 'null' || $avtar == null) {
        //     if ($arr_emp_details['EmployeeDetails']['classification'] == 'male') {
        //         $image = 'https://qaoci.mypayrollmaster.online/img/placeholdermen.jpeg';
        //     } else {
        //         $image = 'https://cdn4.vectorstock.com/i/thumb-large/52/83/default-placeholder-profile-icon-vector-14065283.jpg';
        //     }
        // } else {
        //     $image = 'https://qaoci.mypayrollmaster.online/' . $avtar;
        //     //   $image = 'https://login.mypayrollmaster.online/'. $user_image['0']['user_credentials']['avatar'];
        // }

        if (!empty($arr_usercredentials[0]['user_credentials']['avatar'])) {
            $image = $arr_usercredentials[0]['user_credentials']['avatar'];
            if (!file_exists($image)) {
                if (strtolower($arr_details[0]['emp_details']['classification']) == "male") {
                    $image = "img/placeholdermen.jpeg";
                } else {
                    $image = "img/placeholderwomen.jpeg";
                }
            }
        } else {
            // No avatar in UserCredentials → use placeholder directly
            if (strtolower($arr_details[0]['emp_details']['classification']) == "male") {
                $image = "img/placeholdermen.jpeg";
            } else {
                $image = "img/placeholderwomen.jpeg";
            }
        }
        // debug($image);

        $html = '
    

    <table style="width: 98%;" cellspacing="0" cellpadding="0" border="0">
        
        <tr>
        
          <td style="font-size:14px;"><font size="18"><strong>' . $arr_emp_details['EmployeeDetails']['first_name'] . ' ' . $arr_emp_details['EmployeeDetails']['last_name'] . '</strong></font><br>';
        if ($arr_emp_details['EmployeeDetails']['address'] != null || $arr_emp_details['EmployeeDetails']['address'] != '') {
            $html .= $arr_emp_details['EmployeeDetails']['address'] . '<br>' .
                $arr_emp_details['EmployeeDetails']['city'] . ', ' . $arr_emp_details['EmployeeDetails']['state'] . '<br>' .
                'PIN-' . $arr_emp_details['EmployeeDetails']['pincode'] . '<br>';
        }

        if ($arr_emp_details['EmployeeDetails']['mobile_no'] != null || $arr_emp_details['EmployeeDetails']['mobile_no'] != '') {
            $html .= '<span style="margin: 0;">' . $arr_emp_details['EmployeeDetails']['mobile_no'] . '</span><br>';
        }

        if ($arr_emp_details['EmployeeDetails']['email'] != null || $arr_emp_details['EmployeeDetails']['email'] != '') {
            $html .= '<span style="margin: 0;">' . $arr_emp_details['EmployeeDetails']['email'] . '</span><br>';
        }

        $html .= '
    </td>
    <td style="text-align: right; font-size: 12px; padding: 0 !important; margin: 0 !important; vertical-align: top; position: relative; right: -500px;">
        <img style="width: 103px !important; height: 120px !important; object-fit: contain !important; display: block; margin: -250px 0 0 0 !important; position: relative; top: -20px !important;" src="' . $image . '">
    </td>
</tr>
</table>
';



        $htmlHistories = '
        <br>
        <h4>Education</h4>
	 	<table style="width: 100%; font-size: 12px; line-height: 28px; text-align: center; " cellspacing="0" cellpadding="0" border="1">
	 		<tr>
	 			<th style="font-weight: bold; " >Course</th>
	 			<th style="font-weight: bold; " >University/college</th>
	 			<th style="font-weight: bold; " >Duration</th>
	 			<th style="font-weight: bold; " >Percentage/Marks</th>
	 		</tr>';

        foreach ($arr_educations as $key => $value) {
            $htmlHistories .= '<tr>
	 			<td>' . $value['qualifcations']['course'] . '</td>
	 			<td>' . $value['qualifcations']['university'] . '</td>
	 			<td>' . $value['qualifcations']['duration'] . '</td>
	 			<td>' . $value['qualifcations']['mark'] . '</td>
	 		</tr>';
        }

        $htmlHistories .= '</table>';

        $htmlExperiences = '
        <br>
        <h4>Experience</h4>

        <table style="width: 100%; font-size: 12px; line-height: 28px; text-align: center; " cellspacing="0" cellpadding="0" border="1">
        	<tr>
        		<th style="font-weight: bold; " >Company</th>
        		<th style="font-weight: bold; " >Department</th>
        		<th style="font-weight: bold; " >Designation</th>
        		<th style="font-weight: bold; " >From Date</th>
        		<th style="font-weight: bold; " >To Date</th>
        		<th style="font-weight: bold; " >CTC</th>
        	</tr>';

        foreach ($arr_workExperiences as $key => $value) {
            $htmlExperiences .= '
                <tr>
                    <td>' . $value['history']['company'] . '</td>
                    <td>' . $value['history']['department'] . '</td>
                    <td>' . $value['history']['designation'] . '</td>
                    <td>' . $value['history']['from_date'] . '</td>
                    <td>' . $value['history']['to_date'] . '</td>
                    <td>' . $value['history']['salary'] . '</td>
                </tr>';
        }

        $htmlExperiences .= '</table>';

        $htmlWorkhistory = '
        
        <h4>Work History</h4>

        <table style="width: 100%; font-size: 12px; line-height: 28px; text-align: center; " cellspacing="0" cellpadding="0" border="1">
           <tr>
             
              <th style="font-weight: bold; " >Date</th>
              <th style="font-weight: bold; " >Data Type</th>
              <th style="font-weight: bold; " >Changed To</th>
              <th style="font-weight: bold; " >Status</th>
              <th style="font-weight: bold; " >Approved By</th>
          </tr>';

        foreach ($arr_workHistory as $key => $value) {
            $datetime = $value['emp_config_history']['creation_date'];
            $date_obj = new DateTime($datetime);
            $formatted_date = $date_obj->format('d-m-Y');
            $time = $date_obj->format('H:i:s');
            $formatted_datetime = $formatted_date . ' &nbsp;&nbsp; ' . $time;
            $htmlWorkhistory .= '
                <tr>
                     <td>' . $formatted_datetime . '</td>
                     <td>' . $value['emp_config_history']['type'] . '</td>
                     <td>' . $value['emp_config_history']['day_time_desc'] . '</td>
                     <td>' . $value['emp_config_history']['status'] . '</td>
                     <td>' . $value['emp_config_history']['created_by'] . '</td>
                </tr>';
        }

        $htmlWorkhistory .= '</table>';

        // Check if the employee has resigned and add additional fields if true
        if (!empty($termination_details)) {
            $termination = $termination_details[0]['termination']; // Assuming you want the first record
            $htmlWorkhistory .= '
   
    <table style="width: 100%; font-size: 11px; line-height: 28px; text-align: center;" cellspacing="0" cellpadding="0" border="1">
        <tr>
            <td><b>Resignation Date:</b></td>
            <td>' . (isset($termination['submitted_date']) ? date('d-m-Y', strtotime($termination['submitted_date'])) : '') . '</td>
        </tr>
         <tr>
            <td><b>Reason:</b></td>
            <td>' . (isset($termination['Reason']) ? $termination['Reason'] : '') . '</td>
        </tr>
        <tr>
            <td><b>Relieving Date:</b></td>
            <td>' . (isset($termination['last_approved_working_date']) ? date('d-m-Y', strtotime($termination['last_approved_working_date'])) : '') . '</td>
        </tr>
        
        <tr>
            <td><b>Initiated By:</b></td>
            <td>' . (isset($termination['created_by']) ? $termination['created_by'] : '') . '</td>
        </tr>

        <tr>
            <td><b>Approved By:</b></td>
        </tr>
    </table>';
        }

        $tbl =
            '
      
        <div></div>
        
        <table style="width: 100%; font-size: 12px; border-color: #ccc; line-height: 28px; " bordercolor="#ccc" cellspacing="0" cellpadding="0" border="1">
           
             <tr>
                <td><b> Guardian Name: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['guradian'] . '</td>
                <td><b> Relation: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['relation_guardian'] . '</td>
            </tr>
            <tr>
                <td><b> Marital Status: </b></td>
<td> ' . ucfirst($arr_emp_details['EmployeeDetails']['maritual_status']) . '</td>
                <td><b> State: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['state'] . '</td>
            </tr>
            <tr>
                <td><b> Gender: </b></td>
<td> ' . ucfirst($arr_emp_details['EmployeeDetails']['classification']) . ' </td>
                <td><b> Date Of Birth:</b></td> <td> ' . (isset($arr_emp_details['EmployeeDetails']['date_of_birth'])
                ? date('d-m-Y', strtotime($arr_emp_details['EmployeeDetails']['date_of_birth'])) : 'N/A') . '</td> 
            </tr>
            
            <tr>
    <td><b>' . (($company_code == 'DEMO') ? ' IC No:' : ' Aadhaar No:') . '</b></td>
    <td>&nbsp;' .  $arr_emp_details['EmployeeDetails']['id_card'] . '</td>
    <td><b> Blood Group:</b></td>
    <td>&nbsp;' .  $arr_emp_details['EmployeeDetails']['blood'] . '</td>
</tr>
           

            <tr>
                <td><b> ESI: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['esi'] . ' </td>
                <td><b> UAN: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['pf'] . '</td>
            </tr>
            
            <tr>
    <td><b>' . (($company_code == 'DEMO') ? ' SPK No:' : ' PF No:') . '</b></td>
    <td>&nbsp;' .  $arr_emp_details['EmployeeDetails']['company_pf'] . '</td>
    <td><b> PAN:</b></td>
    <td>&nbsp;' .  $arr_emp_details['EmployeeDetails']['pan_no'] . '</td>
</tr>

        </table>
        

        <div></div>
        
          <table style="width: 100%; font-size: 12px; line-height: 28px; " cellspacing="0" cellpadding="0" border="1">
            <tr>
                
                <td style="padding: 4px; "><b> Joining Date: </b></td><td> ' . (isset($prof_details['0']['employee_structure_vview']['joining_date'])
                ? date('d-m-Y', strtotime($prof_details['0']['employee_structure_vview']['joining_date'])) : 'N/A') . '</td>
                
                     <td style="padding: 4px; "><b> Employee ID: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['employee_id'] . '</td>
            </tr>
            <tr>
                <td style="padding: 4px; "><b> Department: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['department'] . '</td>
                     <td style="padding: 4px; "><b> Designation: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['designation'] . '</td>
            </tr>
            <tr>
             
                  <td style="padding: 4px; "><b> Branch: </b></td><td> ' . $prof_details['0']['employee_structure_vview']['branch'] . '</td>
                   <td style="padding: 4px; "><b> Employee Type: </b></td><td> ' . $arr_emp_proffs['0']['emp_proff']['emp_type'] . '</td>
<!--               <td style="padding: 4px; "><b> Site Customer: </b></td><td> Balachandran</td>-->
            </tr>
            <tr>
             <td style="padding: 4px; "><b> Grade: </b></td><td> ' . $grade . '</td>
           </tr>
        </table>
         <table style="width: 100%; font-size: 12px; line-height: 28px; " cellspacing="0" cellpadding="0" border="1">
            <tr>
                <td><b> Bank Name: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['bank_name'] . ' </td>
                <td><b>  Branch: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['branch_name'] . ' </td>
            </tr>
            <tr>
                <td><b> IFSC: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['ifsc_code'] . ' </td>
                <td><b> Account Number: </b></td><td> ' . $arr_emp_details['EmployeeDetails']['account_no'] . ' </td>
            </tr>
        </table>

        <br><br><br><br><br><br><br><br><br><br><br><br>
        ';

        // Print text using writeHTMLCell()
        $pdf->SetY(31);
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // $pdf->Image($image, 50, 10, 20, 40, '', '', '', false, 30, '', false, false, 0, false, false, false);


        $pdf->writeHTMLCell(0, 0, '', '', $tbl, 0, 1, 0, true, '', true);

        // $pdf->writeHTML($tbl, true, false, false, false, '');
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $htmlHistories, 0, 1, 0, true, '', true);

        $pdf->writeHTMLCell(0, 0, '', '', $htmlExperiences, 0, 1, 0, true, '', true);

        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $htmlWorkhistory, 0, 1, 0, true, '', true);



        $first_name = isset($arr_emp_details['EmployeeDetails']['first_name']) ? $arr_emp_details['EmployeeDetails']['first_name'] . ' ' . $arr_emp_details['EmployeeDetails']['last_name'] : 'example_001';

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        // $pdf->Output($first_name, 'I');

        $pdf->Output($first_name . '.pdf', 'D');

        //============================================================+
        // END OF FILE
        //============================================================+
    }

    // edited by anukrishnan_17-02-2025 open
    public function currentctctake()
    {
        $this->autoRender = false;
        $empId = $this->request->data['emp_fkey'];
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $emp_ctc = $this->EmployeeCTC->query("
                        SELECT emp_ctc_upload.next_increment_date, emp_ctc_upload.created_date,emp_ctc_upload.emp_anual_ctc
                        FROM emp_ctc_upload 
                        WHERE emp_ctc_upload.emp_fkey = '$empId'
                        AND emp_ctc_upload.status = 1
                        ORDER BY created_date DESC
                        LIMIT 1;
                    ");
        $emp_ctc_salary = $this->EmployeeCTC->query("
                        SELECT emp_ctc_upload.emp_anual_ctc, emp_ctc_upload.created_date,emp_ctc_upload.emp_anual_ctc
                        FROM emp_ctc_upload 
                        WHERE emp_ctc_upload.emp_fkey = '$empId'
                        AND emp_ctc_upload.status = 1
                        AND emp_ctc_upload.next_increment_date = (
                            SELECT MAX(next_increment_date) 
                            FROM emp_ctc_upload 
                            WHERE emp_fkey = '$empId'
                            AND status = 1
                            AND next_increment_date < CURDATE()
                        )
                        LIMIT 1
                    ");
        $response = [];

        if (!empty($emp_ctc_salary)) {
            $response['emp_anual_ctc'] = $emp_ctc_salary[0]['emp_ctc_upload']['emp_anual_ctc'];
            $revision_date = new DateTime($emp_ctc_salary[0]['emp_ctc_upload']['created_date']);
            $formattedrevisionDate = $revision_date->format('d-m-Y');
            $response['prevision_revision_date'] = $formattedrevisionDate;
        } else {
            // $response['emp_anual_ctc'] = 'No Data Found';
            // $response['prevision_revision_date'] = 'No Data Found';
            if (!empty($emp_ctc)) {
                $response['emp_anual_ctc'] = $emp_ctc[0]['emp_ctc_upload']['emp_anual_ctc'];
                $response['prevision_revision_date'] = 'No Data Found';
            }
        }

        if (!empty($emp_ctc)) {
            $nextIncrementDate = $emp_ctc[0]['emp_ctc_upload']['next_increment_date'];
            if (!empty($nextIncrementDate) && $nextIncrementDate !== '0000-00-00') {
                $createdDate = new DateTime($nextIncrementDate);
                $formattedDate = $createdDate->format('d-m-Y');
            } else {
                $formattedDate = 'No Data Found';
            }
            $response['previous_increment_date'] = $formattedDate;
        } else {
            $response['previous_increment_date'] = 'No Data Found';
        }

        if (empty($response)) {
            $response['error'] = 'No Data Found';
        }

        echo json_encode($response);
    }
    // End

    //Edited by Akshay on 6-4-2024
    public function changeBranchAr()
    {
        $this->autoRender = FALSE;
        $branch_code = $_POST['branch'];
        $pkey = $_POST['pkey'];
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_ar_update = $this->EmployeeDetails->query("UPDATE attendance_register ar
                 LEFT JOIN payroll_master pm ON pm.emp_fkey = ar.emp_fkey AND pm.month_year = ar.month_year
                 SET ar.branch_code = '$branch_code'
                 WHERE ar.emp_fkey = '$pkey'
                     AND ar.isdelete = 'N'
                     AND (pm.action IS NULL OR pm.action NOT IN ('Processed', 'Approved'));        
                 ");

        //edited by athira on 04-06-2025
        $str_company_code = strtoupper($this->Session->read('company_code'));
        if ($str_company_code === 'GLET' || $str_company_code === 'GAAR' || $str_company_code === 'DEMO' || $str_company_code === 'KWMT' || $str_company_code === 'GEAA') {
            $arr_ar_update2 = $this->EmployeeDetails->query("UPDATE attendance_register_calendar arc
                LEFT JOIN payroll_master pm ON pm.emp_fkey = arc.emp_fkey AND pm.month_year = arc.month_year
                SET arc.branch_code = '$branch_code'
                WHERE arc.emp_fkey = '$pkey'
                    AND arc.isdelete = 'N'
                    AND (pm.action IS NULL OR pm.action NOT IN ('Processed', 'Approved'));        
                ");
        }

        //end           

        $num_rows = $this->EmployeeDetails->query("SELECT ROW_COUNT() AS num_rows_affected")[0][0]['num_rows_affected'];
        if ($num_rows > 0) {
            $changed_rows = true;
            $arr_pm_delete = $this->EmployeeDetails->query("DELETE FROM payroll_master
                                                             WHERE branch_code != '$branch_code'
                                                             AND (action IS NULL OR action = '')
                                                             AND emp_fkey = '$pkey';");
        } else {
            $changed_rows = false;
        }

        return json_encode(array('success' => TRUE, 'error' => '', 'message' => "Employee Added Successfully ", "changed_rows" => $changed_rows));
    }

    // edited by anukrishnan_03-02-2025 open
    public function incrimentdatapdf()
    {
        $this->autoRender = false;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_info = $this->EmployeeDetails->query("SELECT * FROM `comp_contact_info` WHERE `id` = 1 ");

        require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        define('COMPANY_NAME', $company_info['0']['comp_contact_info']['business_name']);
        define('COMPANY_MAIL', $company_info['0']['comp_contact_info']['email']);
        define('COMPANY_URLS', $company_info['0']['comp_contact_info']['email'] . "\n" . $company_info['0']['comp_contact_info']['website']);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($company_info['0']['comp_contact_info']['business_name']);
        $pdf->SetTitle($company_info['0']['comp_contact_info']['business_name']);
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        // $headerLogo = 'https://login.mypayrollmaster.online/newlogin/img/logo.png';
        // $pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
        $current_url = "https://" . $_SERVER['HTTP_HOST'];
        $headerLogo = $current_url . '/' . $company_info['0']['comp_contact_info']['logo'];
        //$headerLogo = 'https://v1.mypayrollmaster.online/' . $company_info['0']['comp_contact_info']['logo'];

        $pdf->SetHeaderData($headerLogo, '25', COMPANY_NAME, COMPANY_URLS, array(0, 64, 255), array(0, 64, 128));

        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"))) {
            require_once(realpath("../Vendor/TCPDF-main/examples/lang/eng.php"));
            $pdf->setLanguageArray($l);
        }

        $pdf->setFontSubsetting(true);

        $pdf->SetFont('dejavusans', '', 14, '', true);

        $pdf->AddPage();

        $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        $fromDate = date("Y-m-01");
        $nextMonth = date("Y-m-d", strtotime("+1 month"));
        $entDate = date("Y-m-t", strtotime($nextMonth));

        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        // Edited by Akshay on 7-4-2025
        $incrementdata = $this->EmployeeCTC->query("SELECT 
                        e.emp_fkey, 
                        ei.EmpName, 
                        ei.employee_id, 
                        e.next_increment_date
                    FROM 
                        emp_ctc_upload e
                    JOIN 
                        employee_info ei ON e.emp_fkey = ei.emp_pkey
                    WHERE 
                        e.next_increment_date BETWEEN '$fromDate' AND '$entDate'
                    AND e.created_date = (
                        SELECT MAX(sub.created_date) 
                        FROM emp_ctc_upload sub 
                        WHERE sub.emp_fkey = e.emp_fkey
                    )
                    ORDER BY ei.EmpName ASC
                ");
        // End

        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $notify = $this->EmployeeMenu->query(
            "select distinct site.site_id,working_day_time_procedures.day_time_desc from site_transactions "
                . "left join site on(site.site_pkey = site_transactions.site_fkey) "
                . "left join working_day_time_procedures on(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) "
                . "where DATEDIFF(site_transactions.end_date_effective,now()) > 0 and DATEDIFF(site_transactions.end_date_effective,now()) < 31 "
                . "and site_pkey is not null and site.status = 1 and site_transactions.status = 1"
        );

        if (!empty($incrementdata)) {
            $html = '<h2 style="text-align:center;">Employee Increment Report</h2>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:center;">
                                <thead>
                                    <tr style="background-color:#f2f2f2;">
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>Next Increment Date</th>
                                    </tr>
                                </thead>
                                <tbody>';

            foreach ($incrementdata as $data) {

                $employee_id = isset($data['ei']['employee_id']) ? $data['ei']['employee_id'] : (isset($data[0]['employee_id']) ? $data[0]['employee_id'] : '');
                $EmpName = isset($data['ei']['EmpName']) ? $data['ei']['EmpName'] : (isset($data[0]['EmpName']) ? $data[0]['EmpName'] : '');
                $html .= '<tr>
                                    <td>' . $employee_id . '</td>
                                    <td>' . $EmpName . '</td>
                                    <td>' . date('d-m-Y', strtotime($data['e']['next_increment_date'])) . '</td>
                                </tr>';
            }

            $html .= '</tbody></table>';
        }

        if (!empty($notify)) {
            $html .= '<h2 style="text-align:center;">Site Notifications</h2>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:center;">
                                <thead>
                                    <tr style="background-color:#f2f2f2;">
                                        <th>Site Id</th>
                                        <th>Site Name</th>
                                    </tr>
                                </thead>
                                <tbody>';

            foreach ($notify as $data) {
                $site_id = $data['site']['site_id'];
                $day_time_desc = $data['working_day_time_procedures']['day_time_desc'];

                $html .= '<tr>
                                    <td>' . htmlspecialchars($site_id) . '</td>
                                    <td>' . htmlspecialchars($day_time_desc) . '</td>
                                </tr>';
            }

            $html .= '</tbody></table>';
        }

        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $pdf->writeHTML($html, true, false, true, false, '');

        $fileName = 'Employee_Increment_Report_' . date('Y-m-d') . '.pdf';
        $pdf->Output($fileName, 'D');
    }
    // edited by anukrishnan_03-02-2025 close

    // Edited by Akshay on 5-4-2025
    public function vdaRevisionForm()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $this->render('vda_revision');
    }
    // End





    // edited by anukrishnan_04-02-2025 open
    public function employeeincrement()
    {
        $arr_increment = array(
            'increment_history' => 'Increment History'
        );
        $this->set('arr_increment', $arr_increment);
    }

    // Edited by Anu krishnan
    private function _modelExists($modelName)
    {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }

    public function loadcriteriaitems($index, $str_criteria = '')
    {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Units') ? 'Branches' : $model;
                $this->set('criteria', $model);
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function listcriteriaitems($str_criteria = '')
    {
        $this->autoRender = false;
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        $arr_criteriaItems = array();

        if (isset($model) && $model != '') {
            // Set the datasource configuration for the model
            $this->{$model}->useDbConfig = $this->Session->read('ds');

            // Define conditions based on the model
            if ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } else {
                $conditions = array("status" => 1);
            }

            // Special handling for the Types model
            if ($model == 'Types') {
                // Assuming Types is a static array or predefined data
                // $arr_criteriaItemsDB = array('Birthday', 'Anniversary', 'Probation');
                $arr_criteriaItemsDB = array('Anniversary', 'Birthday', 'Probation');
            } else {
                // Fetch data from the database for other models
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            }

            // Process the fetched data
            $key = 0;
            switch ($model) {
                case 'Units':
                    foreach ($arr_criteriaItemsDB as $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;

                case 'EmployeeDetails':
                    // Join and fetch employee details
                    $fields = 'emp_pkey, status, EmployeeProfessionalDetails.emp_company_id, 
                               CONCAT(first_name, " ", IFNULL(last_name, "."), " - ", EmployeeProfessionalDetails.emp_company_id) as name, 
                               EmployeeProfessionalDetails.designation, EmployeeProfessionalDetails.joining_date, mobile_no';
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                        )
                    );
                    $conditions = array("status" => 1);
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions = array("status IN (1, 2)");
                    }
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        "order" => array("EmployeeDetails.first_name" => "ASC")
                    ));
                    foreach ($arr_emp as $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;

                case 'Types':
                    // Process the static array for Types
                    foreach ($arr_criteriaItemsDB as $value) {
                        $arr_criteriaItems[$key]['key'] = strtolower($value);
                        $arr_criteriaItems[$key]['text'] = $value;
                        $key++;
                    }
                    break;

                default:
                    foreach ($arr_criteriaItemsDB as $value) {
                        $arr_criteriaItems[$key]['key'] = $value[$model]['id'];
                        $arr_criteriaItems[$key]['text'] = $value[$model]['name'];
                        $key++;
                    }
                    break;
            }
        }

        // Output the results
        echo json_encode($arr_criteriaItems);
    }

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        switch ($type) {
            case 'increment_history':
                $this->generateemployeincrement($mode);
                break;
            default:
                return false;
                break;
        }
    }

   public function generateemployeincrement($mode)
    {
        $arr_form_data = $_REQUEST;
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y H:i');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $reportFrom = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : null;
        $reportFromFormatted = $reportFrom . '-01';
        $reportYear = date('Y', strtotime($reportFrom));
        $reportMonth = date('m', strtotime($reportFrom));
        $mname = date('F', mktime(0, 0, 0, $reportMonth, 10));

        $employeeIds = isset($arr_form_data['EmployeeDetails']) ? $arr_form_data['EmployeeDetails'] : [];
        $employeeIdsList = !empty($employeeIds) ? "'" . implode("','", $employeeIds) . "'" : "''";

        $branchs = isset($arr_form_data['Units']) ? $arr_form_data['Units'] : [];
        $branchCondition = "";

        if (!empty($branchs)) {
            $branchsList = "'" . implode("','", $branchs) . "'";
            $branchCondition = "AND e.branch_code IN ($branchsList)";
        }

        $resigned = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : 0;
        $condition = "";
        if ($resigned != 1) {
            $condition = " AND e.emp_status = 1";
        } else {
            $condition = " AND e.emp_status IN(1, 2)";
        }

        if (!empty($employeeIds)) {
            $incrementquery = $this->EmployeeCTC->query("
                SELECT c.*, e.*, u.user_id,t.last_approved_working_date,ROUND(c.emp_anual_ctc / 12, 0) AS new_gross_salary
                FROM emp_ctc_upload c
                JOIN employee_info e ON e.emp_pkey = c.emp_fkey
                JOIN user_credentials u ON u.emp_fkey = e.emp_pkey
                LEFT JOIN termination t ON (t.emp_fkey = e.emp_pkey and t.status = 1)
                WHERE YEAR(next_increment_date) = '$reportYear'
                AND MONTH(next_increment_date) = '$reportMonth'
                AND c.emp_fkey IN ($employeeIdsList)
                AND c.created_date = (
                    SELECT MAX(created_date)
                    FROM emp_ctc_upload
                    WHERE emp_fkey = c.emp_fkey
                    AND YEAR(next_increment_date) = '$reportYear'
                    AND MONTH(next_increment_date) = '$reportMonth'
                )
                $condition
                ORDER BY e.EmpName ASC
            ");
        } else {
            $incrementquery = $this->EmployeeCTC->query("
                SELECT c.*, e.*, u.user_id,t.last_approved_working_date,ROUND(c.emp_anual_ctc / 12, 0) AS new_gross_salary
                FROM emp_ctc_upload c
                JOIN employee_info e ON e.emp_pkey = c.emp_fkey
                JOIN user_credentials u ON u.emp_fkey = e.emp_pkey
                LEFT JOIN termination t ON (t.emp_fkey = e.emp_pkey  and t.status = 1)
                WHERE YEAR(next_increment_date) = '$reportYear'
                AND MONTH(next_increment_date) = '$reportMonth'
                AND c.created_date = (
                    SELECT MAX(created_date)
                    FROM emp_ctc_upload
                    WHERE emp_fkey = c.emp_fkey
                    AND YEAR(next_increment_date) = '$reportYear'
                    AND MONTH(next_increment_date) = '$reportMonth'
                )
                $branchCondition
                $condition
                ORDER BY e.branch ASC, e.EmpName ASC
            ");
        }

        $processedData = [];
        $slNo = 1;
        try {
            foreach ($incrementquery as $row) {
                $processedData[] = [
                    'SlNo'           => $slNo++,
                    'EmployeeID'     => isset($row['e']['employee_id']) ? $row['e']['employee_id'] : '',
                    // Edited by Akshay on 12-7-2025
                    'EmployeeName' => isset($row['e']['EmpName'])
                        ? trim($row['e']['EmpName']) . ($row['e']['emp_status'] == 2 ? ' (Resigned)' : '')
                        : '',
                    // End

                    // Edited by Akshay on 13-8-2025
                    'JoiningDate' => ($d = DateTime::createFromFormat('Y-m-d', trim($row['e']['joining_date'])))
                        ? $d->format('d-m-Y')
                        : '',
                    'IncrementDate' => ($d = DateTime::createFromFormat('Y-m-d', trim($row['c']['next_increment_date'])))
                        ? $d->format('d-m-Y')
                        : '',
                    // End

                    //edited by athira on 13-06-2025
                    'TerminationDate' => !empty($row['t']['last_approved_working_date']) && ($date = DateTime::createFromFormat('Y-m-d', $row['t']['last_approved_working_date'])) ? $date->format('d-m-Y') : '',
                    'NewGrossSalary' => isset($row['0']['new_gross_salary']) ? $row['0']['new_gross_salary'] : '',
                    //end
                    'create_date' => date('d-m-Y', strtotime($row['c']['created_date'])),
                    'Amount'         => isset($row['c']['emp_anual_ctc']) ? $row['c']['emp_anual_ctc'] : '',
                    'Branch'         => isset($row['e']['branch']) ? $row['e']['branch'] : '',
                    'Department'     => isset($row['e']['department']) ? $row['e']['department'] : '',
                    'Designation'    => isset($row['e']['designation']) ? $row['e']['designation'] : '',
                    'branch_head'    => isset($row['e']['branch']) ? $row['e']['branch'] : '',
                    'user_id'    => isset($row['u']['user_id']) ? $row['u']['user_id'] : '',
                ];
            }
        } catch (Exception $e) {
            debug($e);
        }

        $this->set('selectCriteria1', $arr_form_data['select-criteria1']);
        $this->set('processedData', $processedData);
        $this->set('mname',  $mname);
        $this->set('yname',  $reportYear);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);


        switch ($mode) {
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Employee_Increment_" . $reportMonth . "-" . $reportYear . ".xlsx" : "Events" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Attendance Register By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $table_count = 0;
                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Increment- " . $mname . "  "  . $reportYear);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $worksheet->getStyle('A2')->getFont()->setBold(true);
                $worksheet->getStyle('A2')->getFont()->setSize(13);
                $worksheet->mergeCells('A2:K2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                if ($processedData) {
                    $headerStyleArray = [
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'D9E1F2']
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                            'size' => 12
                        ],
                        'alignment' => [
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ],
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ];

                    $worksheet->getStyle('A3:M3')->applyFromArray($headerStyleArray);

                    $worksheet->setCellValueByColumnAndRow(0, 3, "Sl No");
                    $worksheet->setCellValueByColumnAndRow(1, 3, "Employee ID");
                    $worksheet->setCellValueByColumnAndRow(2, 3, "User ID");
                    $worksheet->setCellValueByColumnAndRow(3, 3, "Employee Name");
                    $worksheet->setCellValueByColumnAndRow(4, 3, "Branch");
                    $worksheet->setCellValueByColumnAndRow(5, 3, "Department");
                    $worksheet->setCellValueByColumnAndRow(6, 3, "Designation");
                    $worksheet->setCellValueByColumnAndRow(7, 3, "Joining Date");
                    //edited by athira on 13-06-2025
                    $worksheet->setCellValueByColumnAndRow(8, 3, "Termination Date");
                    //end
                    $worksheet->setCellValueByColumnAndRow(9, 3, "Annual CTC"); // Edited by Akshay on 12-7-2025
                    $worksheet->setCellValueByColumnAndRow(10, 3, "Increment Date");
                    $worksheet->setCellValueByColumnAndRow(11, 3, "Created Date");
                    //edited by athira on 13-06-2025
                    $worksheet->setCellValueByColumnAndRow(12, 3, "New Gross Salary");
                    //end



                    $rowIndex = 4;
                    foreach ($processedData as $data) {
                        foreach (range(0, 9) as $colIndex) {
                            $worksheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
                        }
                        $worksheet->setCellValueByColumnAndRow(0, $rowIndex, $data['SlNo']);
                        $worksheet->getStyleByColumnAndRow(0, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(1, $rowIndex, $data['EmployeeID']);
                        $worksheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(2, $rowIndex, $data['user_id']);
                        $worksheet->getStyleByColumnAndRow(2, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(3, $rowIndex, $data['EmployeeName']);
                        $worksheet->getStyleByColumnAndRow(3, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(4, $rowIndex, $data['Branch']);
                        $worksheet->getStyleByColumnAndRow(4, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(5, $rowIndex, $data['Department']);
                        $worksheet->getStyleByColumnAndRow(5, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(6, $rowIndex, $data['Designation']);
                        $worksheet->getStyleByColumnAndRow(6, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(7, $rowIndex, $data['JoiningDate']);
                        $worksheet->getStyleByColumnAndRow(7, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(8, $rowIndex, $data['TerminationDate']);
                        $worksheet->getStyleByColumnAndRow(8, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(9, $rowIndex, $data['Amount']);
                        $worksheet->getStyleByColumnAndRow(9, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(10, $rowIndex, $data['IncrementDate']);
                        $worksheet->getStyleByColumnAndRow(10, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(11, $rowIndex, $data['create_date']);
                        $worksheet->getStyleByColumnAndRow(11, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(12, $rowIndex, $data['NewGrossSalary']);
                        $worksheet->getStyleByColumnAndRow(12, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $rowIndex++;
                        $table_count++;
                    }
                }
                $worksheet->getColumnDimension('K')->setWidth(20);
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $worksheet->getColumnDimension('L')->setWidth(20);
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $worksheet->getColumnDimension('M')->setWidth(20);
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                if ($table_count == 0) {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $worksheet->mergeCells('A3:J3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    );
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'M3')->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'M' . ($rowIndex - 1))->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                }

                $objPHPExcel->getActiveSheet()->setTitle('Employee_Increment');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output');
                exit;
            default:
                $this->set('mode', '');
                $this->render('employeeincrementview');
                break;
        }
    }

    // edited by anukrishnan_04-02-2025 close
   // Edited by Akshay on 5-4-2025
    public function salaryIncrementForm()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeCTC->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 order by first_name ASC');
        $this->set("arr_employees", $arr_employees);

        $arr_salary = $this->EmployeeCTC->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $this->render('salary_increment');
    }

  

   

    public function getSalaryStructure($emp_pkey = 0)
    {
        $this->autoRender = FALSE;
        $this->response->type('json');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

        $arr_sal_structure = $this->EmployeeCTC->query("SELECT DISTINCT emp_structure_id
                                                            FROM emp_salary_structure
                                                            WHERE emp_fkey = '$emp_pkey'
                                                            AND end_date_effective IS NULL
                                                            LIMIT 1;
                                                            ");
        $structure_id = isset($arr_sal_structure[0]['emp_salary_structure']['emp_structure_id']) ? $arr_sal_structure[0]['emp_salary_structure']['emp_structure_id'] : '';
        // Gross Salary Components
        $arr_emp_sal_structure = $this->EmployeeCTC->query("SELECT emp_salary_structure.salary_head_item_fkey, emp_salary_structure.salary_head_item_desc, emp_salary_structure.structure_det_value 
                                                                FROM emp_salary_structure
                                                                LEFT JOIN salary_head_items 
                                                                    ON emp_salary_structure.salary_head_item_fkey = salary_head_items.salary_head_item_pkey
                                                                LEFT JOIN salary_heads 
                                                                    ON salary_head_items.head_fkey = salary_heads.head_pkey
                                                                WHERE emp_fkey = $emp_pkey
                                                                AND TRIM(emp_salary_structure.head_operator) = 'Addition' 
                                                                AND TRIM(emp_salary_structure.item_part) = 'Direct' 
                                                                AND emp_salary_structure.end_date_effective IS NULL
                                                                AND salary_heads.head_pkey = 1;
                                                                ");
        $result = [];
        foreach ($arr_emp_sal_structure as $item) {
            $result[] = [
                'key' => ($item['emp_salary_structure']['salary_head_item_fkey']) ? $item['emp_salary_structure']['salary_head_item_fkey'] : '',
                'desc' => ($item['emp_salary_structure']['salary_head_item_desc']) ? $item['emp_salary_structure']['salary_head_item_desc'] : '',
                'value' => $item['emp_salary_structure']['structure_det_value'] ? $item['emp_salary_structure']['structure_det_value'] : ''
            ];
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
                'value' => $item['emp_salary_structure']['structure_det_value'] ? $item['emp_salary_structure']['structure_det_value'] : ''
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

        $arr_ctc_result = $this->EmployeeCTC->query("SELECT emp_anual_ctc 
                                                            FROM emp_ctc_transaction 
                                                            WHERE emp_fkey = $emp_pkey 
                                                            AND end_date_effective IS NOT NULL 
                                                            ORDER BY end_date_effective DESC 
                                                            LIMIT 1;
                                                        ");
        $ctc = isset($arr_ctc_result[0]['emp_ctc_transaction']['emp_anual_ctc']) ? ($arr_ctc_result[0]['emp_ctc_transaction']['emp_anual_ctc'] / 12) : '';

        echo json_encode(['success' => true, 'structure_id' => $structure_id, 'structure' => $result, 'structure_indirect' => $result_indirect, 'emp' => $emp[0], 'ctc' => $ctc]);
        return;
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

        if ($emp_structure_id != '') {
            $arr_result = $this->EmployeeCTC->query("CALL calculate_emp_salary_breakup('$empFkey', $emp_structure_id, '$monthlyGross');");

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
 public function changeIncrementType($type = '')
    {
        $this->autoRender = false;

        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'increment_history':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_increment', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type), 'order' => 'reportcriteria_desc'))));
                    break;
                default:
                    echo "No criterias found";
                    break;
            }
            $this->render('showreport');
        } else {
            echo "No criterias found";
        }
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
        $emp = $arr_form_data['emp_fkey'];
        $fetch_proff = $this->EmployeeConfig->query("select * from emp_proff where emp_fkey = '$emp' ");
        $salary_id = isset($fetch_proff['0']['emp_proff']['structure_id']) ? $fetch_proff['0']['emp_proff']['structure_id'] : 0;

        if (isset($arr_form_data['structure_id']) && $arr_form_data['structure_id'] != $salary_id) {

            $condition2['type'] = 'SALARY';
            $condition2['emp_fkey'] = $arr_form_data['emp_fkey'];

            $edit_salary = TRUE;

            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $salary_id = $arr_form_data['structure_id'];
            //added by megha salary allocation 1
            $resp = 0;
            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $user_ids = $this->Session->read('login_user_id');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            try {
                $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                //debug($proc);exit;
                //employee condition added by megha on 5/03/2020
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
                    // $head_type=isset($row_formulae_from_remarks['EmployeeSalaryStructure']['head_type'])?$row_formulae_from_remarks['EmpSalarySlip']['head_type']:'';
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
            //end salary allocation
            try {
                $user_ids = $this->Session->read('login_user_id');

                //debug($result);
                $data['type'] = 'SALARY';
                $data['policy_id'] = $arr_form_data['structure_id'];
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $user_ids . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition2);

                $this->EmployeeConfig->saveAll($data);
                //added by megha sallary allocation 2
                $prc = $this->EmployeeSalaryStructure->query("call salary_structure_limit_prc('$emp','$user_ids',@`perr_msg`)");
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save salary policy "));
            }

            if ($resp == 1) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Salary structure updated successfully.'
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Failed to update salary structure.'
                ));
            }
            exit;
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Structure is same as the current structure.'
            ));
        }
    }

    public function onEffectiveDateChange()
    {
        $this->autoRender = FALSE;
        // $this->response->type('json');

        try {
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $empFkey = isset($_POST['empPkey']) ? (int)$_POST['empPkey'] : 0;
            $start_date_effective = isset($_POST['start_date_effective']) ? $_POST['start_date_effective'] : 0;
            if (!empty($start_date_effective)) {
                $date = DateTime::createFromFormat('d-m-Y', $start_date_effective);
                $month = $date->format('Y-m');

                $arr_processed = $this->EmployeeCTC->query(
                    "SELECT COUNT(*) as count FROM payroll_master
                                            WHERE emp_fkey = $empFkey
                                            AND month_year = '$month'
                                            AND action in ('Processed', 'Approved');"
                );
                $is_processed = $arr_processed[0][0]['count'];

                if ($is_processed > 0) {
                    echo json_encode(['status' => 'success', 'is_processed' => true,  'message' => 'Payroll already processed for this month. This will be included in arrear']);
                } else {
                    echo json_encode(['status' => 'success', 'is_processed' => false, 'message' => 'Payroll not yet processed for this month.']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }


    public function saveIncrement()
    {
        $this->autoRender = FALSE;

        try {
            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
            $this->SalaryIncrement->useDbConfig = $this->Session->read('ds');
            $this->SalaryIncrementDetails->useDbConfig = $this->Session->read('ds');

            $arr_form_data = $_POST;

            $emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
            $salary_structure = isset($arr_form_data['salary_structure']) ? $arr_form_data['salary_structure'] : '';
            $user_id = $this->Session->read("login_user_id");
            $arr_form_data['created_by'] = $user_id;

            // Convert dd-mm-yyyy to Y-m-d
            $arr_form_data['start_date_effective'] = date('Y-m-d', strtotime($arr_form_data['start_date_effective']));
            $arr_form_data['next_increment_date']  = date('Y-m-d', strtotime($arr_form_data['next_increment_date']));

            // Convert September-2025 to Y-m-1
            $arr_form_data['pay_out_month'] = date('Y-m-1', strtotime('01-' . $arr_form_data['pay_out_month']));

            // debug($arr_form_data);
            $total_ctc = 0;
            foreach ($arr_form_data as $key => $value) {
                if ((strpos($key, 'new_value_') === 0 || strpos($key, 'indirect_new_value_') === 0) && is_numeric($value)) {
                    $total_ctc += round((float)$value);
                }
            }

            // $arr_form_data['emp_monthly_ctc'] = $total_ctc;
            // $arr_form_data['emp_anual_ctc'] = $total_ctc * 12;

            $arr_branch = $this->EmployeeCTC->query("SELECT branch_code FROM emp_details ed
                                                        WHERE emp_pkey = '$emp_fkey';
                                                        ");
            $branch = isset($arr_branch[0]['ed']['branch_code']) ? $arr_branch[0]['ed']['branch_code'] : '';
            $month = isset($arr_form_data['start_date_effective']) ? $arr_form_data['start_date_effective'] : 0;
            if ($month != 0) {
                $month = date('Y-m', strtotime($month));
            } else {
                $month = 0;
            }

            $result = $this->EmployeeCTC->save($arr_form_data);

            // Edited by Akshay on 23-4-2025
            $arr_ctc_upload_pkey = $this->EmployeeCTC->query("SELECT emp_ctc_upload_pkey
                                            FROM emp_ctc_upload
                                            ORDER BY emp_ctc_upload_pkey DESC
                                            LIMIT 1;
                                            ");
            $ctc_upload_pkey = $arr_ctc_upload_pkey['0']['emp_ctc_upload']['emp_ctc_upload_pkey'];
            $arr_form_data['emp_ctc_upload_fkey'] = $ctc_upload_pkey;

            $this->SalaryIncrement->save($arr_form_data);
            // End

            $arr_salary_increment_pkey = $this->SalaryIncrement->query("SELECT salary_increment_pkey
                                                                            FROM salary_increment
                                                                            ORDER BY salary_increment_pkey DESC
                                                                            LIMIT 1;
                                                                            ");
            $salary_increment_pkey = isset($arr_salary_increment_pkey[0]['salary_increment']['salary_increment_pkey']) ? $arr_salary_increment_pkey[0]['salary_increment']['salary_increment_pkey'] : 0;

            $arr_data = [];

            foreach ($arr_form_data as $key => $value) {
                if (preg_match('/^new_value_(\d+)$/', $key, $matches)) {
                    $number = $matches[1];

                    $arr_data[] = [
                        'salary_increment_fkey' => $salary_increment_pkey,
                        'emp_fkey' => $emp_fkey,
                        'salary_head_item_fkey' => $number,
                        'salary_head_item_desc' => isset($arr_form_data["desc_$number"]) ? trim($arr_form_data["desc_$number"]) : '',
                        'current_value' => isset($arr_form_data["current_$number"]) ? trim($arr_form_data["current_$number"]) : '',
                        'new_value' => $value,
                        'increment_amt' => isset($arr_form_data["new_value_amt$number"]) ? $arr_form_data["new_value_amt$number"] : '',
                        'increment_perc' => isset($arr_form_data["new_value_pct$number"]) ? $arr_form_data["new_value_pct$number"] : '',
                    ];
                }

                // Handle indirect salary heads
                if (preg_match('/^indirect_new_value_(\d+)$/', $key, $matches)) {
                    $number = $matches[1];

                    $arr_data[] = [
                        'salary_increment_fkey' => $salary_increment_pkey,
                        'emp_fkey' => $emp_fkey,
                        'salary_head_item_fkey' => $number,
                        'salary_head_item_desc' => isset($arr_form_data["desc_$number"]) ? trim($arr_form_data["desc_$number"]) : '',
                        'current_value' => isset($arr_form_data["indirect_current_$number"]) ? trim($arr_form_data["indirect_current_$number"]) : '',
                        'new_value' => $value,
                        'increment_amt' => isset($arr_form_data["indirect_new_value_amt$number"]) ? $arr_form_data["indirect_new_value_amt$number"] : '',
                        'increment_perc' => isset($arr_form_data["indirect_new_value_pct$number"]) ? $arr_form_data["indirect_new_value_pct$number"] : '',
                    ];
                }
            }


            $arr_structure = $this->EmployeeCTC->query("SELECT * FROM emp_salary_structure
                                                        WHERE emp_fkey = '$emp_fkey'
                                                        AND head_operator = 'Addition'
                                                        AND end_date_effective IS NULL;");

            foreach ($arr_structure as &$structure) {
                $item_fkey = $structure['emp_salary_structure']['salary_head_item_fkey'];
                $emp_salary_slip_pkey = $structure['emp_salary_structure']['emp_salary_structure_pkey'];

                foreach ($arr_data as $data) {
                    if ($data['salary_head_item_fkey'] == $item_fkey) {
                        $structure['emp_salary_structure']['structure_det_value'] = $data['new_value'];

                        $arr_emp_salary_slip_data = array(
                            'EmployeeSalaryStructure.structure_det_value' => $data['new_value'] //edited by sinsiya on 26-09-2024
                        );
                        $this->EmployeeSalaryStructure->updateAll(
                            $arr_emp_salary_slip_data,
                            array('EmployeeSalaryStructure.emp_salary_structure_pkey' => $emp_salary_slip_pkey)
                        );
                        break;
                    }
                }
            }

            if ($salary_increment_pkey != 0) {
                $sql = "INSERT INTO salary_increment_details (
                    salary_increment_fkey,
                    emp_fkey,
                    salary_head_item_fkey,
                    salary_head_item_desc,
                    current_value,
                    new_value,
                    increment_amt,
                    increment_perc,
                    status
                  ) VALUES ";

                $values = [];
                foreach ($arr_data as $data) {
                    // Escape and format values
                    $salary_increment_fkey = (int) $data['salary_increment_fkey'];
                    $emp_fkey = (int) $data['emp_fkey'];
                    $salary_head_item_fkey = (int) $data['salary_head_item_fkey'];
                    $salary_head_item_desc = addslashes($data['salary_head_item_desc']);
                    $current_value = (float) $data['current_value'];
                    $new_value = (float) $data['new_value'];
                    $increment_amt = (float) $data['increment_amt'];
                    $increment_perc = (float) $data['increment_perc'];
                    $status = 1;

                    $values[] = "(
                        $salary_increment_fkey,
                        $emp_fkey,
                        $salary_head_item_fkey,
                        '$salary_head_item_desc',
                        $current_value,
                        $new_value,
                        $increment_amt,
                        $increment_perc,
                        $status
                      )";
                }

                $sql .= implode(",\n", $values);

                // Now run the query using CakePHP or raw DB connection
                $this->SalaryIncrementDetails->query($sql); // If using CakePHP 2.x

                // $arr_form_data['salary_structure']

            }

            $result = $this->EmployeeCTC->query("select arrear_month_fn('$branch','$month','$user_id')"); // Arrear

            $this->SalaryIncrementDetails->query(
                "UPDATE emp_proff SET structure_id = ? WHERE emp_fkey = ?",
                [$salary_structure, $emp_fkey]
            );


            echo json_encode([
                'status' => 'success',
                'message' => 'Salary increment saved successfully.',
                'increment_id' => $salary_increment_pkey
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to save increment.'
            ]);
        }



        // unset($structure); // Break reference
        // exit;
    }
    // End

    // Edited by Akshay on 17-4-2025
    public function itemIncrementForm()
    {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->render('item_increment');
    }

    public function saveItemIncrement()
    {
        try {
            $this->autoRender = FALSE;
            $this->response->type('json');
            $this->ComponentIncrement->useDbConfig = $this->Session->read('ds');
            $arr_form_data = $this->request->data;

            $user_id = $this->Session->read("login_user_id");
            $arr_form_data['created_by'] = $user_id;
            $arr_form_data['created_date'] = date('Y-m-d');

            $this->ComponentIncrement->save($arr_form_data);

            $latest = $this->ComponentIncrement->find('first', [
                'conditions' => ['ComponentIncrement.status' => 1],
                'order' => ['ComponentIncrement.sal_pkey DESC'],
                'fields' => ['ComponentIncrement.sal_pkey'],
                'recursive' => -1
            ]);

            $latestSalPkey = $latest['ComponentIncrement']['sal_pkey'];

            echo json_encode([
                'success' => true, // Used to check status in JS
                'message' => 'Salary increment saved successfully.', // Custom message
                'increment_id' => $latestSalPkey // Return the latest insert ID or anything else needed
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Unable to save increment.'
            ]);
        }
    }
    // End

}

require_once(realpath("../Vendor/TCPDF-main/tcpdf.php"));

class MYPDF extends TCPDF
{
    public function Header()
    {
        // Get the page width
        $pageWidth = $this->getPageWidth();

        // Set header color
        $this->SetTextColor(0, 64, 255);

        // Set logo (assuming $this->header_logo is set by SetHeaderData)
        if ($this->header_logo) {
            //  $logoWidth = 25; // Logo width
            $logoSize = 20; // Logo width and height for square shape
            $logoX = 170; // Increase this value to move the logo further to the right
            //edited by sinsiya on 24-07-2025 
            $logoY = 10;
            $this->Image($this->header_logo, $logoX, $logoY, $logoSize);
        }

        // Set font for the company name
        $this->SetFont('helvetica', 'B', 12);

        // Calculate logo and company name positions
        $this->SetY(16); // Set Y position for header
        $this->SetX(13); // Set the starting X position to move left (reduce this value for more leftward movement)
        $this->Cell(1, 6, '', 0, 0); // Placeholder cell for logo width
        $this->Cell(0, 4, COMPANY_NAME, 0, 1); // Company name next to the logo


        // Set font for URLs
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 4, COMPANY_URLS, 0, 1); // Centered URLs

        // Draw a line under the header
        $this->Line(10, $this->GetY(), $pageWidth - 10, $this->GetY()); // Draw a line from left to right
    }

    //End

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        date_default_timezone_set("Asia/Calcutta");
        // Page number
        $this->Cell(0, 10, 'Downloaded from Mypayrollmaster Admin at ' . date("d-m-Y h:i a") . '            Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
