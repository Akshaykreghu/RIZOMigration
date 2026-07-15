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
 * For full copyright and license information, please see the LICENSE       .txt
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
class EmployeeadvanceController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'Employeeadvance';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmployeeLoan', 'EmployeeAdvance', 'salarySlip', 'EmpCtcTransaction', 'DbConfig');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index()
    {
        $this->autoRender = FALSE;
        $user_group = $this->Session->read("user_group");
        if ($user_group == '1') {
            //Admin view
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
            $this->set('active_emp_count', $active_emp_count);

            //Fetch Units for the company
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
            $this->set('arr_branches', $arr_branches);
            $this->render('index');
        } else if ($user_group == '2') {
            //Employee View
            $emp_fkey = $this->Session->read("emp_fkey");
            $this->setup($emp_fkey);
            $this->render('setup');
        }
    }

    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */

    public function listemployees()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        $conditions = array('status' => 1);

        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->EmployeeDetails->find("count", array("conditions" => $conditions));
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_emp as $key => $value) {
            $resp_emp["rows"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0]);
        }
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

    /*
     * Show tax Head Details form
     */

    public function showtaxheaddetail($emp_pkey = 0, $tax_heads_fkey = 0)
    {
        $this->set('emp_pkey', $emp_pkey);
        $this->set('tax_heads_fkey', $tax_heads_fkey);

        $this->TaxHead->useDbConfig = $this->Session->read('ds');
        $tax_head = Set::extract('/TaxHead/.', $this->TaxHead->find("first", array('conditions' => array('tax_heads_pkey' => $tax_heads_fkey))));
        $tax_head_name = isset($tax_head[0]['tax_name']) ? $tax_head[0]['tax_name'] : 'Details';
        $this->set('tax_head_name', $tax_head_name);

        $arr_taxheaddetails = $this->requestAction("/Taxation/getTaxHeadDetails/$tax_heads_fkey");
        $arr_emptaxtransactions = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$emp_pkey/$tax_heads_fkey");
        $this->set('arr_taxheaddetails', $arr_taxheaddetails);
        $this->set('arr_emptaxtransactions', $arr_emptaxtransactions);
    }

    //Ends
    public function loadEmpDetails($emp_pkey = 0)
    {
        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_personal_profile = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            $arr_emp_personal_profile = $arr_emp_personal_profile['EmployeeDetails'];
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
        } else {
            $this->set('arr_professionalinfo', array());
        }
    }

    public function loadEmpProfDetails($emp_pkey = '')
    {
        $user_group = $this->Session->read("user_group");
        if ($user_group == 2) {
            $emp_pkey = $emp_pkey; //$sessionObj['emp_fkey'];  
        }

        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
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

            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
        } else {
            $this->set('arr_professionalinfo', array());
        }
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

    /*
     * Employee CTC Upload form
     * By santhosh on 24 Oct 2015
     */

    public function uploadandsaveempctc($ctcuploadtype = 0)
    {
        $this->autoRender = FALSE;
        $rejeted_exceeding_ctc = array();
        $rejeted_exceeding_advance = array();
        $save_count = 0; // Edited by Akshay on 21-1-2025
        if ($ctcuploadtype != 0) {
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_empctc_' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
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
                        $this->EmployeeAdvance->useDbConfig = $this->Session->read('ds');
                        $this->salarySlip->useDbConfig = $this->Session->read('ds');
                        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');


                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeAdvance');
                        foreach ($arrayempdata as $key => $row) {

                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            //edited by sinsiya 23-05-2024
                            $paymentDate = isset($row['Payment Date']) ? date('Y-m-d', strtotime($row['Payment Date'])) : '';

                            // $paymentDate = isset($row['Payment Date']) ?$row['Payment Date'] : '';
                            //debug($row['Payment Date']);
                            // Your existing code for fetching emp_fkey using user_id continues here...

                            // Add payment date to the data array for insertion

                            if ($user_id == '') {
                                continue;
                            }

                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            ));
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';
                            $salary_amount = 0;
                            $arr_empctc_data = array();
                            $arr_empctc_data['emp_advance_pkey'] = 0;
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['emp_fkey'] = $emp_fkey;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['modified_by'] = $this->Session->read('login_user_id');
                            date_default_timezone_set('Asia/Kolkata');
                            $arr_empctc_data['modified_date'] = date('Y-m-d H:i:s');
                            $arr_empctc_data['affected_month'] = isset($row['Affected Month (yyyy-mm)']) ? date('Y-m', strtotime($row['Affected Month (yyyy-mm)'])) . '-01' : '';

                            //edited by sinsiya 23-05-2024

                            $arr_empctc_data['payment_date'] = $paymentDate;

                            //$arr_empctc_data['created_date'] = date('Y-m-d');
                            foreach ($arr_empctc_fields as $field => $fieldlabel) {
                                $fieldValue = $row[$fieldlabel];
                                //debug($fieldValue);
                                $arr_empctc_data[$field] = $fieldValue;
                            }
                            try {
                                $month = $arr_empctc_data['affected_month'];
                                $empfkey = $arr_empctc_data['emp_fkey'];
                                $amount = $arr_empctc_data['advance_amount'];

                                if (isset($amount) && !empty($amount) && isset($month) && !empty($month) && isset($paymentDate) && !empty($paymentDate)) { // Edited by Akshay on 21-1-2025
                                    if (isset($month) && !empty($month)) {
                                        $salary_month_check = $this->salarySlip->query("select * FROM   emp_salary_slip WHERE month_year= '$month' AND emp_fkey= '$empfkey' and end_date_effective is null ");
                                    }
                                    $type = $this->EmpCtcTransaction->query("select emp_type  from emp_proff where emp_fkey= '$empfkey' ");
                                    $emp_type = $type[0]['emp_proff']['emp_type'];
                                    //added by megha for avoid daily wage and hourly wage employees on 08-02-2023
                                    if ($emp_type != 'DAILY WAGES' && $emp_type != 'HOURLY WAGES') {
                                        $arr_salary = $this->EmpCtcTransaction->query("select emp_anual_ctc gross_salary from emp_ctc_transaction where end_date_effective is null and emp_fkey= '$empfkey' ");
                                        // Edited by Akshay on 4-2-2025
                                        $arr_salary_structure = $this->EmployeeAdvance->query("SELECT DISTINCT prorate_code FROM emp_salary_structure
                                                 WHERE emp_fkey = $emp_fkey AND end_date_effective IS NULL
                                                 LIMIT 1;");
                                        // End
                                        if (!empty($arr_salary) && !empty($arr_salary_structure)) {
                                            $emp_slary = $arr_salary[0]['emp_ctc_transaction']['gross_salary'];
                                            $ep_salary = $monthly_ctc = round($emp_slary / 12); //Edited by Akshay on 3-1-2024
                                            $salary = round((80 / 100) * $ep_salary);
                                            $salary_amount = $salary + 1;

                                            //Edited by Akshay on 3-1-2024
                                            $prorate_code = $arr_salary_structure[0]['emp_salary_structure']['prorate_code'];
                                            $month_year = $row['Affected Month (yyyy-mm)'];
                                            if ($authuser['company_code'] == 'GLET' || $authuser['company_code'] == 'ABSG') {

                                                $arr_att_reg = $this->EmpCtcTransaction->query("SELECT presant_total FROM attendance_register
                                                 WHERE emp_fkey = '$emp_fkey' AND month_year = '$month_year' AND isdelete = 'N';
                                                 ");
                                                $present_days = isset($arr_att_reg[0]['attendance_register']['presant_total']) ? $arr_att_reg[0]['attendance_register']['presant_total'] : '';

                                                if ($present_days == '') {
                                                    $this->request->data = [
                                                        'month' => $month_year, // Example month
                                                        'emp' => $emp_fkey,    // Example employee primary key
                                                    ];
                                                    $result = $this->Updateame();
                                                    $result = $this->Listpunches($emp_fkey, $month_year);

                                                    $month1 =  $month_year . '-01';
                                                    $att_type = $this->EmployeeAdvance->query("SELECT COUNT(*) as count 
                                                                                         FROM device_attandance 
                                                                                         WHERE `C2` = 'SIT' 
                                                                                         AND DATE(DOWNLOADDATE) BETWEEN 
                                                                                             att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) 
                                                                                             AND att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1)
                                                                                          AND emp_id IN (SELECT emp_id FROM employee_info WHERE emp_pkey = '$emp_fkey')     
                                                                                         ;
                                                                                         ");

                                                    $type = isset($att_type[0][0]['count']) ? $att_type[0][0]['count'] : 0;
                                                    if ($type > 0) {
                                                        $table = ' emp_site_detail_timeattandance ed';
                                                    } else {
                                                        $table = ' emp_detail_timeattandance ed';
                                                    }



                                                    $emp_detail_attendance = $this->EmployeeAdvance->query("
                                                                                                             SELECT 
                                                                                                                     emp_pkey,
                                                                                                                     yearmonth,
                                                                                                                     SUM(
                                                                                                                         CASE
                                                                                                                             WHEN LOCATE('/', present) > 0 THEN 
                                                                                                                                 -- Split the 'present' value and count 'P' occurrences as 0.5 each
                                                                                                                                 (LENGTH(SUBSTRING_INDEX(present, '/', 1)) - LENGTH(REPLACE(SUBSTRING_INDEX(present, '/', 1), 'P', ''))) * 0.5 +
                                                                                                                                 (LENGTH(SUBSTRING_INDEX(present, '/', -1)) - LENGTH(REPLACE(SUBSTRING_INDEX(present, '/', -1), 'P', ''))) * 0.5
                                                                                                                             ELSE 
                                                                                                                                 LENGTH(present) - LENGTH(REPLACE(present, 'P', ''))
                                                                                                                         END
                                                                                                                     ) AS count
                                                                                                                 FROM 
                                                                                                                     $table
                                                                                                                 WHERE 
                                                                                                                     emp_pkey = '$emp_fkey'
                                                                                                                     AND yearmonth = '$month1' 
                                                                                                                 GROUP BY 
                                                                                                                     emp_pkey, 
                                                                                                                     yearmonth;
 
                                                                             ");

                                                    $working_day_count = isset($emp_detail_attendance[0][0]['count']) ? $emp_detail_attendance[0][0]['count'] : 0;
                                                } else {
                                                    $working_day_count = $present_days;
                                                }
                                                // debug($working_day_count);
                                                $conditon_sql = '';
                                                if ($prorate_code != 1) {
                                                    $conditon_sql = "AND weekoff IS NULL
                                                             AND holiday IS NULL";
                                                }
                                                $arr_days = $this->EmployeeAdvance->query("SELECT COUNT(*) AS count
                                                             FROM emp_detail_timeattandance ed
                                                             WHERE emp_pkey = $emp_fkey
                                                             AND att_date BETWEEN 
                                                                 (SELECT att_start_end_fn(DATE_FORMAT(CONCAT('$month_year', '-1'), '%Y-%m-01'), 1)) 
                                                                 AND 
                                                                 (SELECT att_start_end_fn(DATE_FORMAT(CONCAT('$month_year', '-1'), '%Y-%m-01'), 2))
                                                             $conditon_sql
                                                             ;");
                                                $daysInMonth = $arr_days[0][0]['count'];

                                                // else {
                                                //     $arr_days = $this->EmployeeAdvance->query("SELECT 
                                                //                                             att_start_end_fn(DATE_FORMAT(CONCAT('$month_year', '-1'), '%Y-%m-01'), 1) AS start_date,
                                                //                                             att_start_end_fn(DATE_FORMAT(CONCAT('$month_year', '-1'), '%Y-%m-01'), 2) AS end_date
                                                //                                             ;");
                                                //     $start_date = $arr_days[0][0]['start_date']; // Start date
                                                //     $end_date = $arr_days[0][0]['end_date']; // End date
                                                //     // Convert dates from string to DateTime objects for calculation
                                                //     $start_date_obj = new DateTime($start_date);
                                                //     $end_date_obj = new DateTime($end_date);

                                                //     // Calculate the difference in days (including both dates)
                                                //     $interval = $start_date_obj->diff($end_date_obj);
                                                //     // $daysInMonth = $interval->days + 1;
                                                //     $date = new DateTime("$month_year-01");
                                                //     $daysInMonth = $date->format('t');
                                                // }
                                                // if ($prorate_code != 1) {
                                                //     list($year, $month) = explode('-', $month_year);
                                                //     $first_date = "$year-$month-01";
                                                //     $last_date = date("Y-m-t", strtotime($first_date));
                                                //     $week_off = $this->EmpCtcTransaction->query("SELECT weekoff_days_count_fn($emp_fkey, ' $first_date', '$last_date') as week_off;");
                                                //     $week_off = isset($week_off[0][0]['week_off'])? $week_off[0][0]['week_off']:0;
                                                //     $holiday = $this->EmpCtcTransaction->query("SELECT get_holiday_total_fn($emp_fkey, ' $first_date', '$last_date') as holiday;");
                                                //     $holiday = isset($holiday[0][0]['holiday'])?  $holiday[0][0]['holiday']:0;
                                                //     $daysInMonth = $daysInMonth - $week_off - $holiday;
                                                // }
                                                list($year, $month) = explode('-', $month_year);
                                                $per_day_wage = ($daysInMonth > 0) ? $monthly_ctc / $daysInMonth : 0;
                                                $advance_limit = round($per_day_wage * $working_day_count);

                                                // End
                                            }
                                        } else {
                                            $advance_limit = $salary_amount = 0; //Edited by Akshay on 3-1-2024
                                        }
                                    }
                                    if (empty($salary_month_check)) {
                                        //added by megha for avoid daily wage and hourly wage employees on 08-02-2023
                                        if ($emp_type != 'DAILY WAGES' && $emp_type != 'HOURLY WAGES') {
                                            // Edited by Akshay on 23-1-2025
                                            if ($authuser['company_code'] == 'GLET' || $authuser['company_code'] == 'ABSG') {
                                                $month_year = $row['Affected Month (yyyy-mm)'];
                                                $month1 = $month_year . '-01';
                                                $arr_advance = $this->EmployeeAdvance->query("SELECT SUM(advance_amount) AS advance_amount 
                                                                                             FROM emp_advance
                                                                                             WHERE emp_fkey = $emp_fkey AND (affected_month = '$month1' OR affected_month = '$month_year' ) AND status = 1; 
                                                                                             ");
                                                $existing_advance = isset($arr_advance[0][0]['advance_amount']) ? $arr_advance[0][0]['advance_amount'] : 0;
                                                $amount = (float)$amount + (float)$existing_advance;
                                            }
                                            if ($authuser['company_code'] == 'GLET' || $authuser['company_code'] == 'ABSG') {
                                                // For GLET company, the condition checks both salary and advance limit
                                                $condition_statemnt = ($amount <= $advance_limit);
                                                if (!$condition_statemnt) {
                                                    if (isset($advance_limit) && $amount > $advance_limit) {
                                                        $rejeted_exceeding_advance[] = $row;
                                                    }
                                                }
                                                $condition_statemnt = true;
                                            } else {
                                                // For other companies, the condition only checks salary
                                                $condition_statemnt = ($amount < $salary_amount);
                                            }
                                            if ($condition_statemnt) {
                                                // End
                                                //added by megha for getting amount in salary on 27-08-2021
                                                //edited by sinsiya 23-05-2024
                                                $inputDate = $arr_empctc_data['affected_month'];
                                                list($month, $year) = explode('-', $inputDate);
                                                $date = $year . '-' . $month;
                                                 $arr_empctc_data['affected_month'] = date('Y-m', strtotime($arr_empctc_data['affected_month'])) . '-01';

                                                // Edited by Akshay on 22-5-2025
                                                // $date = DateTime::createFromFormat('m-Y', $arr_empctc_data['affected_month']);
                                                // $arr_empctc_data['affected_month'] = $date->format('Y-m') . '-01';
                                                // End

                                                $result1 = $this->EmployeeAdvance->save($arr_empctc_data);
                                                // Edited by Akshay on 21-1-2025
                                                if (!empty($result1)) {
                                                    $save_count++;
                                                }
                                                // End
                                            } else {
                                                // debug($amount);debug($salary_amount);debug($advance_limit);
                                                if ((isset($salary_amount)) && $amount >= $salary_amount) {
                                                    $rejeted_exceeding_ctc[] = $row;
                                                }
                                                // if ($authuser['company_code'] == 'GLET' || $authuser['company_code'] == 'ABSG') {
                                                //     if (isset($advance_limit) && $amount > $advance_limit) {
                                                //         $rejeted_exceeding_advance[] = $row;
                                                //     }
                                                // }
                                            }
                                        } else {
                                            $inputDate = $arr_empctc_data['affected_month'];
                                            list($month, $year) = explode('-', $inputDate);
                                            $date = $year . '-' . $month;
                                            $arr_empctc_data['affected_month'] = date('Y-m', strtotime($arr_empctc_data['affected_month'])) . '-01';
                                            $result1 = $this->EmployeeAdvance->save($arr_empctc_data);
                                            // Edited by Akshay on 21-1-2025
                                            if (!empty($result1)) {
                                                $save_count++;
                                            }
                                            // End
                                        }
                                    }
                                }
                            } catch (Exception $e) {
                                debug($e);
                            }
                        }
                    }
                    unlink($targetpath);
                    // Edited by Akshay on 21-1-2025
                    if ($save_count == 0) {
                        $message = 'Sorry, employee advance import failed!';
                        $success = 0;
                    } else {
                        $message = 'Employee advance imported successfully';
                        $success = 1;
                    }

                    if ($ctcuploadtype == 1) { // End
                        echo json_encode(array('success' => $success, 'msg' => $message, 'rejected_ctc' => $rejeted_exceeding_ctc, 'rejected_mctc' => $rejeted_exceeding_advance));
                        exit;
                    } else {
                        echo json_encode(array('success' => $success, 'msg' => $message, 'rejected_ctc' => $rejeted_exceeding_ctc, 'rejected_mctc' => $rejeted_exceeding_advance));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee advance import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee advance import failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee advance import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee advance import failed!'));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee advance import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee advance import failed!'));
                exit;
            }
            exit;
        }
    }

    //popup for save and update
    public function form()
    {
        $this->autoRender = FALSE; // Edited by Akshay on 28-2-2025
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeAdvance->useDbConfig = $this->Session->read('ds');

        // Edited by Akshay on 10-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $arr_conditions = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $arr_conditions = 'and emp_details.branch_code="' . $is_ho . '"';
            }
        }

        //edited by arul on 12/12/2019 Employee company id added
        $arr_employees = $this->EmployeeDetails->query("select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 $arr_conditions order by first_name ASC");
        // End
        // $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
        $this->set("arr_employees", $arr_employees);
        //end Employee company id added
        $data['emp_advance_pkey'] = 0;
        $data['emp_fkey'] = '';
        $data['advance_amount'] = "";
        $data['affected_month'] = "";
        $data['is_credited'] = "";
        $data['remarks'] = "";
        if (isset($_REQUEST['emp_advance_pkey']) && $_REQUEST['emp_advance_pkey'] != 0) {
            $data_db = $this->EmployeeAdvance->find("first", array("conditions" => array("emp_advance_pkey" => $_REQUEST['emp_advance_pkey'])));
            $data_db['EmployeeAdvance']['affected_month'] = date('Y-m', strtotime($data_db['EmployeeAdvance']['affected_month']));
            $data = $data_db['EmployeeAdvance'];
        }

        //  debug($data);
        $this->layout = null;
        $this->set("data", $data);

        // Edited by Akshay on 27-1-2025
        $company_code = $this->Session->read('company_code');
        $this->set("company_code", $company_code);
        // End
        if ($company_code == 'GLET' || $company_code == 'ABSG') {
            $this->render('absForm');
        } else {
            $this->render('form');
        }
    }

    //checking salary slip
    public function salarycheck()
    {
        $arr_request = $this->request->data;
        //debug($arr_request);
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->salarySlip->useDbConfig = $this->Session->read('ds');
        //edited by sinsiya 21-05-2024
        // $month = $arr_request['month_year'];
        $inputDate = $arr_request['month_year'];  // e.g., '03-2024'

        // Split the input date string into month and year
        list($month, $year) = explode('-', $inputDate);

        // Concatenate year and month in 'yyyy-mm' format
        $month = $year . '-' . $month;
        $empfkey = $arr_request['empid'];
        $arr_salary_month_check = $this->salarySlip->query("select * FROM   emp_salary_slip WHERE month_year= '$month' AND emp_fkey= '$empfkey' and end_date_effective is null ");
        //debug($arr_salary_month_check);
        //$extingsalary=$arr_salary_month_check[0]['emp_salary_slip']['salary_amount'];
        //debug($extingsalary);
        $this->set('arr_salary_month_check', $arr_salary_month_check);
        $data = array();
        $data['rows'] = $arr_salary_month_check;
        $data['msg'] = "Salary already processed";
        echo json_encode($data);
        //debug($arr_salary_month_check);
    }

    //check salary amount
    public function salary()
    {
        $arr_request = $this->request->data;
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 24-1-2025
        //edited by sinsiya 21-05-2024
        //$month = $arr_request['month_year'];
        $inputDate = $arr_request['month_year'];  // e.g., '03-2024'
        // Split the input date string into month and year
        list($month, $year) = explode('-', $inputDate);
        // Concatenate year and month in 'yyyy-mm' format
        $month = $year . '-' . $month;
        $emp_fkey = $arr_request['empid']; // Edited by Akshay on 6-1-2024
        $arr_salary = $this->EmpCtcTransaction->query("select emp_anual_ctc gross_salary from emp_ctc_transaction where end_date_effective is null and emp_fkey= '$emp_fkey' "); // Edited by Akshay on 6-1-2024
        $emp_slary = isset($arr_salary[0]['emp_ctc_transaction']['gross_salary']) ?  $arr_salary[0]['emp_ctc_transaction']['gross_salary'] : 0;
        $ep_salary = round($emp_slary / 12);
        $salary = round((80 / 100) * $ep_salary);

        // Edited by Akshay on 6-1-2024
        try {
            $arr_salary_structure = $this->EmpCtcTransaction->query("SELECT DISTINCT prorate_code FROM emp_salary_structure
        WHERE emp_fkey = $emp_fkey AND end_date_effective IS NULL
        LIMIT 1;");

            $month_year = $month;

            $prorate_code = isset($arr_salary_structure[0]['emp_salary_structure']['prorate_code']) ? $arr_salary_structure[0]['emp_salary_structure']['prorate_code'] : '';

            if ($company_code == 'GLET' || $company_code == 'ABSG') {
                if ($prorate_code == '') {
                    $salary = 0;
                } else {
                    $arr_att_reg = $this->EmpCtcTransaction->query("SELECT presant_total FROM attendance_register
                WHERE emp_fkey = '$emp_fkey' AND month_year = '$month_year' AND isdelete = 'N';
                ");
                    $present_days = isset($arr_att_reg[0]['attendance_register']['presant_total']) ? $arr_att_reg[0]['attendance_register']['presant_total'] : '';

                    if ($present_days == '') {
                        $this->request->data = [
                            'month' => $month_year, // Example month
                            'emp' => $emp_fkey,    // Example employee primary key
                        ];
                        $result = $this->Updateame();
                        $result = $this->Listpunches($emp_fkey, $month_year);

                        $month1 =  $month_year . '-01';
                        $att_type = $this->EmpCtcTransaction->query("SELECT COUNT(*) as count 
                FROM device_attandance 
                WHERE `C2` = 'SIT' 
                AND DATE(DOWNLOADDATE) BETWEEN 
                    att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) 
                    AND att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1)
                AND emp_id IN (SELECT emp_id FROM employee_info WHERE emp_pkey = '$emp_fkey')    
                ;
                ");

                        $type = isset($att_type[0][0]['count']) ? $att_type[0][0]['count'] : 0;
                        if ($type > 0) {
                            $table = ' emp_site_detail_timeattandance ed';
                        } else {
                            $table = ' emp_detail_timeattandance ed';
                        }

                        $emp_detail_attendance = $this->EmpCtcTransaction->query("
                            SELECT 
                                    emp_pkey,
                                    yearmonth,
                                    SUM(
                                        CASE
                                            WHEN LOCATE('/', present) > 0 THEN 
                                                -- Split the 'present' value and count 'P' occurrences as 0.5 each
                                                (LENGTH(SUBSTRING_INDEX(present, '/', 1)) - LENGTH(REPLACE(SUBSTRING_INDEX(present, '/', 1), 'P', ''))) * 0.5 +
                                                (LENGTH(SUBSTRING_INDEX(present, '/', -1)) - LENGTH(REPLACE(SUBSTRING_INDEX(present, '/', -1), 'P', ''))) * 0.5
                                            ELSE 
                                                LENGTH(present) - LENGTH(REPLACE(present, 'P', ''))
                                        END
                                    ) AS count
                                FROM 
                                    $table
                                WHERE 
                                    emp_pkey = '$emp_fkey'
                                    AND yearmonth = '$month1' 
                                GROUP BY 
                                    emp_pkey, 
                                    yearmonth;
                        ");

                        $working_day_count = isset($emp_detail_attendance[0][0]['count']) ? $emp_detail_attendance[0][0]['count'] : 0;
                    } else {
                        $working_day_count = $present_days;
                    }

                    $conditon_sql = '';
                    if ($prorate_code != 1) {
                        $conditon_sql = "AND weekoff IS NULL
                                                    AND holiday IS NULL";
                    }

                    $arr_days = $this->EmpCtcTransaction->query("SELECT COUNT(*) AS count
                                                                FROM emp_detail_timeattandance ed
                                                                WHERE emp_pkey = $emp_fkey
                                                                AND att_date BETWEEN 
                                                                (SELECT att_start_end_fn(DATE_FORMAT(CONCAT('$month_year', '-1'), '%Y-%m-01'), 1)) 
                                                                AND 
                                                                (SELECT att_start_end_fn(DATE_FORMAT(CONCAT('$month_year', '-1'), '%Y-%m-01'), 2))
                                                                $conditon_sql
                                                                ;");
                    $daysInMonth = $arr_days[0][0]['count'];


                    list($year, $month) = explode('-', $month_year);
                    $per_day_wage = ($daysInMonth > 0) ? ($ep_salary / $daysInMonth) : 0;
                    $advance_limit = $per_day_wage * $working_day_count;
                    $salary = round($advance_limit);

                    $arr_advance = $this->EmpCtcTransaction->query("SELECT SUM(advance_amount) AS advance_amount 
                        FROM emp_advance
                        WHERE emp_fkey = $emp_fkey AND (affected_month = '$month_year' OR affected_month =  CONCAT('$month_year', '-01')) AND status = 1; 
                        ");
                    $existing_advance = isset($arr_advance[0][0]['advance_amount']) ? $arr_advance[0][0]['advance_amount'] : 0;
                    $salary = (float)$salary - (float)$existing_advance;
                    $salary = ($salary > 0) ? $salary : 0;
                }
            }
        } catch (Exception $e) {
            debug($e);
        }
        // End

        $this->set('salary', $salary);
        echo json_encode($salary);
    }

    //save 
    public function employeeloansave()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeAdvance->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data['Payment_date']);
        // if(isset($arr_form_data['Payment_date'])){
        //edited by sinsiya 21-05-2024
        $payment_date = date('Y-m-d', strtotime($arr_form_data['Payment_date']));
        $arr_form_data['payment_date'] = $payment_date;
        //$empfkey=$arr_form_data['emp_fkey'];
        // }
        // $arr_form_data['created_by']   =   $this->Session->read('login_user_id');
        //$date = date('Y-m-01', strtotime($arr_form_data['affected_month']));
        $inputDate = $arr_form_data['affected_month'];
        list($month, $year) = explode('-', $inputDate);
        $date = $year . '-' . $month;
        // debug($date);
        //$date = date('Y-m', strtotime($arr_form_data['affected_month']));
        $arr_form_data['affected_month'] = date('Y-m-01', strtotime($date));
        date_default_timezone_set('Asia/Kolkata');
        $edited =  date('Y-m-d H:i:s');
        $arr_form_data['modified_date']   =   $edited;
        $arr_form_data['modified_by']   =   $this->Session->read('login_user_id');
        //$payroll_processed =  $this->EmployeeAdvance->query("select * from payroll_master where month_year='$date' and emp_fkey= '$empfkey' and action!='Approved' and action!='Processed'");
        // debug("select * from payroll_master where month_year='$date' and emp_fkey= '$empfkey' and action!='Approved' and action!='Processed'");exit;
        $result = $this->EmployeeAdvance->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Salary Advance Added successfully";
        echo json_encode($resp);
    }

    //list            
    public function employeelist()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->EmployeeAdvance->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';
        //edited by sinsiya on 05-03-2025
        $month_condition = '';

        $mm = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
        //debug($mm);

        //debug($mm); exit;
        if ($mm != '') {
            //  $month = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
            $date = DateTime::createFromFormat('m-Y', $mm);
            $formattedDate = $date->format('Y-m-01');
            $YmformattedDate = $date->format('Y-m'); // Edited by Akshay on 15-3-2025

            $month_condition = " and (au.affected_month='$formattedDate' or au.affected_month='$YmformattedDate')"; // Edited by Akshay on 15-3-2025
        } else {
            $formattedDate = date('Y-m-01');
            $YmformattedDate = date('Y-m'); // Edited by Akshay on 15-3-2025
            $month_condition = " and (au.affected_month='$formattedDate' or au.affected_month='$YmformattedDate')"; // Edited by Akshay on 15-3-2025
        }
        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey=$emp";
        } else {
            $emp_condition = ' ';
        }
        //        if ($emp_fkey != '') {
        //            $emp_condition = "and au.emp_fkey=$emp_fkey";
        //        }
        if ($branch_code != '') {
            $branch_condition = "and ei.branch_code='$branch_code'";
        }

        $user_group = $this->Session->read('user_group');
        $emp_pkey = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 30-1-2025
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) AS branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " and ei.branch_code='$is_ho' ";
            }
        }



        // $count = $this->EmployeeAdvance->find("count", array("conditions" => array('status' => 1, 'is_credited' => 'N')));
        $counts = $this->EmployeeAdvance->query(" select COUNT(*)
                    from employee_info ei 
                   INNER join emp_advance au on (ei.emp_pkey = au.emp_fkey)
                   where  au.is_credited = 'N'
                   and au.status=1 
                   $emp_condition $branch_condition $month_condition ");

        $count = $counts[0][0]['COUNT(*)'];

        $arr_att = $this->EmployeeAdvance->query("select au.emp_advance_pkey,au.advance_amount,
ei.employee_id,ei.EmpName,au.affected_month,au.is_credited,au.remarks
                    from employee_info ei 
                    INNER join emp_advance au on (ei.emp_pkey = au.emp_fkey)
                    where  au.is_credited = 'N'
                    and au.status=1 
                    $emp_condition $branch_condition $month_condition "
            . " ORDER BY emp_advance_pkey desc "
            . "limit $limit  offset $ofst ");
        //debug($arr_att);
        //    $arr_count=$this->EmployeeAdvance->query("select au.emp_advance_pkey,au.advance_amount,
        //    ei.employee_id,ei.EmpName,au.affected_month,au.is_credited,au.remarks
        //                        from employee_info ei 
        //                        INNER join emp_advance au on (ei.emp_pkey = au.emp_fkey)
        //                        where  au.is_credited = 'N'
        //                        and au.status=1 
        //                        $emp_condition $branch_condition $month_condition ");
        // $count = count($arr_count);
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
            $out['id'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : '';
            $out['emp_advance_pkey'] = isset($value['au']['emp_advance_pkey']) ? $value['au']['emp_advance_pkey'] : '';
            $out['advance_amount'] = isset($value['au']['advance_amount']) ? $value['au']['advance_amount'] : '';
            //$out['affected_month'] = isset($value['au']['affected_month']) ? $value['au']['affected_month'] : '';
            //edited by sinsiya 21-05-2024
            $out['affected_month'] = isset($value['au']['affected_month']) ? date("m-Y", strtotime($value['au']['affected_month'])) : '';
            $out['is_credited'] = isset($value['au']['is_credited']) ? $value['au']['is_credited'] : '';
            $out['remarks'] = isset($value['au']['remarks']) ? $value['au']['remarks'] : '';
            //$out['created_date'] = isset($value['au']['created_date']) ? $value['au']['created_date'] : '';
            //$out['created_by'] = isset($value['au']['created_by']) ? $value['au']['created_by'] : '';
            //$out['modified_by'] = isset($value['au']['modified_by']) ? $value['au']['modified_by'] : '';
            //$out['modified_date'] = isset($value['au']['modified_date']) ? $value['au']['modified_date'] : '';
            //$out['status'] = isset($value['au']['status']) ? $value['au']['status'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    //main page dropdown       
    public function advance()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1))));
        // $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1),'order' =>  array('first_name ASC'))));
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            if ($user_group == 2) {
                $emp_pkey = $this->Session->read("emp_fkey");
                $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                $this->set('is_ho', $is_ho);
                if ($is_ho != 1) {
                    $this->set(
                        "arr_branches",
                        $arr_branches = $this->Units->find("all", array(
                            "conditions" => array(
                                "status" => 1,
                                "branch_code =" => $is_ho // Add this condition
                            )
                        ))
                    );
                }
            }
        }

        // Edited by Akshay on 21-1-2025
        $company_code = strtoupper($this->Session->read('company_code'));
        $this->set('company_code', $company_code);
        // End
    }

    //delete
    public function deleteEmployee()
    {
        $this->autoRender = FALSE;
        $this->EmployeeAdvance->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_advance_pkey"])) {
            $ar_ids = explode(",", $_REQUEST["emp_advance_pkey"]);
            //debug($ar_ids);
            $this->EmployeeAdvance->updateAll(
                array('EmployeeAdvance.status' => 0),
                array('EmployeeAdvance.emp_advance_pkey' => $ar_ids)
            );
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '')
    {
        $this->autoRender = FALSE;
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_advance.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empctcdata = new EmployeeCTCData($ctcuploadtype);
        $emp_credentials_schema = $empctcdata->getFieldHeadings('UserCredentials');
        $emp_details_schema = $empctcdata->getFieldHeadings('EmployeeDetails');
        $emp_proff_schema = $empctcdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_ctc_schema = $empctcdata->getFieldHeadings('EmployeeAdvance');
        $emp_schema = array_merge($emp_credentials_schema, $emp_proff_schema, $emp_details_schema, $emp_ctc_schema);

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(26);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);


        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();

        $sheet = array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }

        //Fill form with existing users 
        $emp_credentials_fields = $empctcdata->getFieldNames('UserCredentials');
        $emp_details_fields = $empctcdata->getFieldNames('EmployeeDetails');
        $emp_ctc_fields = $empctcdata->getFieldNames('EmployeeCTC');
        $emp_fields = array_merge(array_keys($emp_credentials_fields), array_keys($emp_details_fields), array_keys($emp_ctc_fields));

        $cond = '';
        if (isset($employee) && !empty($employee) && $employee != 'null') {
            $cond .= " AND  EmployeeDetails.emp_pkey= if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        }
        if (isset($branch) && !empty($branch) && $branch != 'null') {
            $cond .= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_empdetails = $this->EmployeeDetails->query(" SELECT UserCredentials.user_id, EmployeeInfo.employee_id, EmployeeInfo.EmpName FROM emp_details AS EmployeeDetails "
            . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
            . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
            . " WHERE EmployeeDetails.status = ' 1 '   "
            . " $cond ");

        //debug($arr_empdetails);
        //        $cond = array(
        //            'EmployeeDetails.status' => 1);
        //        if (isset($branch) && !empty($branch)) {
        //
        //            $cond[] = "EmployeeDetails.branch_code= '$branch' ";
        //        }
        //        if (isset($employee) && !empty($employee)) {
        //
        //            $cond[] = "EmployeeDetails.emp_fkey= '$employee' ";
        //        }
        //
        //        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //        $arr_empdetails = $this->EmployeeDetails->find('all', array(
        //            'fields' => 'UserCredentials.user_id,concat(EmployeeDetails.first_name," ",EmployeeDetails.middile_name," ",EmployeeDetails.last_name) as Name',
        //            //'fields'=>"'".implode(',',$emp_fields)."'",
        //            'joins' => array(
        //                array(
        //                    'table' => 'user_credentials',
        //                    'alias' => 'UserCredentials',
        //                    'type' => 'INNER',
        //                    'foreignKey' => false,
        //                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
        //                )
        //            ),
        //            'conditions' => $cond
        //        ));
        // debug($arr_empdetails);
        //die();
        //edited by sinsiya 21-05-2024
        $currentDate = date('d-m-Y');

        $rowindex = 2;
        $columnindex = 0;
        foreach ($arr_empdetails as $rows) {
            $columnindex = 0;
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowindex, $currentDate); // Assuming payment date column is 'A'
            //  $columnindex = 0; // Assuming payment date column is the first column after payment date
            foreach ($rows as $columns) {
                foreach ($columns as $column) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $columnindex++;
                }
            }
            $rowindex++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Employee Advance Data');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    // Edited by Akshay on 4-1-2024
    public function Updateame()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        // debug($arr_data);

        $month = $arr_data['month'] . '-01';
        $get_emp = array();
        $emp_pkey = $arr_data['emp'];
        $branch_code = isset($get_emp['0']['emp_details']['branch_code']) ? $get_emp['0']['emp_details']['branch_code'] : 'NULL';
        $deleterecords = $this->EmployeeDetails->query("delete from emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m') ) ");
        if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")) {
            return FALSE;
            die();
        }
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")) {
                return false;
                die();
            }
        } else {
            if (!$this->EmployeeDetails->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")) {
                return false;
                die();
            }
        }
    }

    public function listpunches($emp_pkey = 0, $month = '')
    {
        $this->autoRender = FALSE;

        $base_table = "emp_detail_timeattandance";

        if ($emp_pkey == 0) {
            return;
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_pkey` = '$emp_pkey'");
        $branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code']) ? $arr_emp_pkey['0']['emp_details']['branch_code'] : '';

        $limit = 32;
        $page = 1;

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();
        $resp_mispunches["data"] = array();
        $resp_mispunch["out"] = array();

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));

        $yearmonth = $month . '-01';

        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);

        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        if ($emp_pkey != 0) {
            $condition = "$base_table.emp_pkey = '$emp_pkey' and ";
            $emp = $emp_pkey;
        } else {
            $condition = '';
            $emp = NULL;
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        if ($this->Session->read('emp_fkey')) {
            $emp_pkeys = $this->Session->read('emp_fkey');
            $useracess = $this->EmployeeDetails->query("select * from user_access as Useraccess where user_fkey = '$emp_pkeys' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                $emp_condition = ""; // "emp.attr1 = '$emp_pkeys' and ";
            } else {
                $emp_condition = "";
            }
        } else {
            $emp_condition = "";
        }


        if (!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey')")) {
            $resp_mispunches["total"] = "0";
            $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
            $resp_mispunches["type"] = "danger";
            //            return FALSE;
            die();
        }

        //debug($shiftdetailed);
        if ($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y') {
            if (!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$yearmonth', '$emp_pkey', '$branch_code')")) {
                $resp_mispunches["total"] = "0";
                $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                $resp_mispunches["type"] = "danger";
                //                return false;
                die();
            }
        } else {
            if (!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")) {
                $resp_mispunches["total"] = "0";
                $resp_mispunches["message"] = "Employee Does not have any Shift Policy , Please assign one ";
                $resp_mispunches["type"] = "danger";
                //                return false;
                die();
            }
        }
        $attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $emp_condition $condition yearmonth = '$yearmonth' order by att_date ");
        $count = isset($attendances_count[0][0]['count']) ? $attendances_count[0][0]['count'] : 0;
    }
    // End
}
