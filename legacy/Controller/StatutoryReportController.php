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
class StatutoryReportController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'StatutoryReport';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array(
        'EditPunches',
        'Attendance',
        'CentralControl',
        'UserCredentials',
        'EmployeeDetails',
        'Designation',
        'EmployeeProfessionalDetails',
        'DeviceAttendance',
        'Departments',
        'Grades',
        'Verticals',
        'Units',
        'ReportCriterias',
        'AttendanceRegister',
        'AttendanceRegisterReport',
        'DbConfig',
        'MobileUserauditor',
        'EmployeeLoan',
        'Taxsalarycomponents',
        'EmpCtcTransaction',
        'FinancialYear',
        'EmployeeGrossDetails',
        'LeaveRequests',
        'ReportAudit',
        'EmployeeTaxTransactions',
        'TaxHead',
        'EmpTaxSalTransNew',
        'EmployeeTaxsalsum' //Edited by Akshay on 15-6-2024
    );
    public $components = array('MasterdataManagement');

    public function hrreports()
    {
        $company_code = strtoupper($this->Session->read('company_code'));
        if (($company_code == 'DEMO') || ($company_code == 'GEDE')) {
            $arr_reporttypes = array(
                //'Labour' => 'ESI EPF Report ',
                //edited by megha on 11_06_19 added tds and pt
                'Labour' => 'Statutory Report ',
                //added by megha on 30/09/2019  PT Report ----1
                'ProfTax' => 'Professional Tax Summary Report ',
                //edited by athira on 11-04-2025
                'ProfSalary' => 'Professional Tax Salary Report ',
                //end
                'EPF' => 'PF SUMMARY Report ',
                'EPF_NEW' => 'PF SUMMARY',
                'ESI' => 'ESI  ',
                'ESI_NEW' => 'ESI SUMMARY',
                'TAX' => 'Tax Detailed ',
                '12BB' => 'Form - 12BB',
                'EPF_SYNTHIET' => 'PF STATEMENT',
                'ESI_SYNTHIET' => 'ESI STATEMENT',
                'PF_UPLOAD_SYNTHIET' => 'PF Upload Summary'
            );
        } else if ($company_code == 'KWMT') {
            $arr_reporttypes = array(
                'Labour' => 'Statutory Report ',
                'ProfTax' => 'Professional Tax Summary Report ',
                //edited by athira on 11-04-2025
                'ProfSalary' => 'Professional Tax Salary Report ',
                //end
                'EPF_NEW' => 'PF SUMMARY ',
                'ESI_NEW' => 'ESI SUMMARY ',
                'TAX' => 'Tax Details',
                '12BB' => 'Form - 12BB'
            );
        } else if ($company_code == 'HRBL') {
            $arr_reporttypes = array(
                'Labour' => 'Statutory Report ',
                'ProfTax' => 'Professional Tax Summary Report ',
                'EPF' => 'PF SUMMARY ',
                'ESI' => 'ESI SUMMARY ',
                'EPF_SYNTHIET' => 'PF STATEMENT',
                'ESI_SYNTHIET' => 'ESI STATEMENT',
                'TAX' => 'Tax Detailed ',
                'PF_UPLOAD_SYNTHIET' => 'PF Upload Summary'
            );
        }
        // Edited by Akshay on 21-1-2026
        elseif ($company_code == 'DRRC' || $company_code == '	DJIC' || $company_code == 'AGNG' || $company_code == 'AYRK') {
            $arr_reporttypes = array(
                'Labour' => 'Statutory Report ',
                'EPF' => 'PF SUMMARY ',
                'ESI' => 'ESI SUMMARY '
            );
        }
        // End
        else {
            $arr_reporttypes = array(
                'Labour' => 'Statutory Report ',
                'ProfTax' => 'Professional Tax Summary Report ',
                'ProfSalary' => 'Professional Tax Salary Report ', // Edited by Akshay on 21-1-2026
                'EPF' => 'PF SUMMARY ',
                'ESI' => 'ESI SUMMARY ',
                'TAX' => 'Tax Detailed '
            );
        }
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '')
    {
        $this->autoRender = FALSE;
        // debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'Labour':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'EPF':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'EPF_NEW':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    //Edited by Akshay on 20-5-2024
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type),  "order" => array("reportcriteria_desc ASC")))));
                    break;
                case 'ESI':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'ESI_NEW':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //added by megha on 30/09/2019  PT Report ----2
                case 'ProfTax':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    //$finyear = $this->ReportCriterias->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = '1'  group by fin_year");
                    $finyear = $this->ReportCriterias->query("select fin_year from fin_year where  vattr1 = '1' and fin_year >= '2019' group by fin_year");
                    $this->set('fin_year', $finyear);
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //edited by athira on 11-04-2025
                case 'ProfSalary':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    //$finyear = $this->ReportCriterias->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = '1'  group by fin_year");
                    $finyear = $this->ReportCriterias->query("select fin_year from fin_year where  vattr1 = '1' and fin_year >= '2019' group by fin_year");
                    $this->set('fin_year', $finyear);
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //end
                case 'TAX':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case '12BB':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'EPF_SYNTHIET':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'ESI_SYNTHIET':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'PF_UPLOAD_SYNTHIET':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
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

    /*
     * Add criterias
     */

    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '')
    {
        $this->autoRender = FALSE;
        if ($type != '' && $str_currentcriterias != '') {
            //$arr_currentcriterias = explode(',', $str_currentcriterias);
            //$arr_remainingcriterias = array_diff(array_flip($this->arr_employee_reportcriterias), $arr_currentcriterias);
            //$this->set('arr_remainingcriterias',array_flip($arr_remainingcriterias));
            $str_currentcriterias = "'" . str_replace(",", "','", $str_currentcriterias) . "'";
            $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type)))));

            $this->set('newindex', $newindex);
            $this->render('showcriteria');
        } else {
            return '';
        }
    }

    /*
     * Load criteria items
     */

    public function loadcriteriaitems($index, $str_criteria = '')
    {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model) || $model == 'Gender') {
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Gender') ? 'Gender' : $model;
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        if (isset($model) && $model != '') {
            if (trim($model) != 'Gender') {
                $this->{$model}->useDbConfig = $this->Session->read('ds');
            }


            //$this->{$model}->useDbConfig = $this->Session->read('ds');

            if ($model == 'DayTimeProcedures') {

                $conditions = array("active" => 1);
            } elseif ($model == 'Units') {

                $arr_order = array("Units.branch_name" => "ASC");
                $conditions = array("Units.status" => 1);

                //Edited by Ashin on 23-02-2024
                if ($user_group == 2) {
                    // Edited by Akshay on 28-1-2025
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions["Units.branch_code"] = $is_ho;
                        }
                    } else {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
                        $branch = $payroUser[0]['emp_proff']['emp_branch'];
                        if ($payroUser[0]['emp_proff']['payro_priv'] == 1) {
                            $conditions[] = "Units.branch_code!='$branch' ";
                        }
                    }
                    // End
                }
            } else {

                $conditions = array("status" => 1);
            }



            if ($model == 'Gender') {
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $arr_order = array("TRIM(emp_details.classification)" => "ASC");
                $conditions = array();

                $arr_criteriaItemsDB[0] = 'Female';
                $arr_criteriaItemsDB[1] = 'Male';
                $arr_criteriaItemsDB[2] = 'Transgender';
            } else {


                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions,)));
            }
            $arr_criteriaItems = array();

            $key = 0;

            switch ($model) {



                case 'Units':

                    foreach ($arr_criteriaItemsDB as $key => $value) {

                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];

                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];

                        $key++;
                    }

                    break;
                case 'Gender':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = strtolower($value);
                        $arr_criteriaItems[$key]['text'] = $value;
                        $key++;
                    }

                    break;

                case 'Designation':

                    foreach ($arr_criteriaItemsDB as $key => $value) {

                        $arr_criteriaItems[$key]['key'] = $value['desig_code'];

                        $arr_criteriaItems[$key]['text'] = $value['desig_name'];

                        $key++;
                    }

                    break;



                case 'Departments':

                    foreach ($arr_criteriaItemsDB as $key => $value) {

                        $arr_criteriaItems[$key]['key'] = $value['dept_code'];

                        $arr_criteriaItems[$key]['text'] = $value['dept_name'];

                        $key++;
                    }
                    break;


                case 'EmployeeDetails':

                    //edited by arul - changed empid ad company id

                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,status';

                    $joins = array(

                        array(

                            'table' => 'emp_proff',

                            'alias' => 'EmployeeProfessionalDetails',

                            'type' => 'LEFT',

                            'foreignKey' => false,

                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')

                        )

                    );

                    $conditions = array();





                    //$arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    $arr_order = array("CONCAT(EmployeeDetails.first_name, IFNULL(EmployeeDetails.last_name, ' '))" => "ASC");

                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {

                        $conditions = array("status in(1,2)");
                    } else {



                        $conditions = array("status" => 1);
                    }
                    //Edited by Ashin on 23-02-2024
                    if ($user_group == 2) {
                        // Edited by Akshay on 28-1-2025
                        $user = $this->Session->read('company_code');
                        if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                            $emp_pkey = $this->Session->read('emp_fkey');
                            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                            if ($is_ho != 1) {
                                $conditions["EmployeeDetails.branch_code"] = $is_ho;
                            }
                        } else {
                            $cur_emp_key = $this->Session->read("emp_fkey");
                            $payroUser = $this->EmployeeDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
                            $branch = $payroUser[0]['emp_proff']['emp_branch'];
                            if ($payroUser[0]['emp_proff']['payro_priv'] == 1) {
                                $conditions[] = "EmployeeDetails.branch_code!='$branch' ";
                            }
                        }
                        // End
                    }
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');


                    $arr_emp = $this->EmployeeDetails->find("all", array(

                        'fields' => $fields,

                        'joins' => $joins,

                        'conditions' => $conditions,
                        'order' => $arr_order

                    ));

                    foreach ($arr_emp as $key => $value) {

                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];

                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];

                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];

                        $key++;
                    }

                    break;
                case  'Gender':
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $conditions = array();

                    $arr_criteriaItemsDB[0] = 'Male';
                    $arr_criteriaItemsDB[1] = 'Female';
                    $arr_criteriaItemsDB[2] = 'Other';
                    break;
            }

            echo json_encode($arr_criteriaItems);
        }
    }

    public function reportAudit($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        switch ($type) {
            case 'Labour':
                $dataForHistory['report_type'] = "Statutory Report";
                $component = $arr_form_data['report_component'];
                switch ($component) {
                    case 'tax':
                        $dataForHistory['report_component'] = 'TDS Report';
                        break;
                    case 'esi':
                        $dataForHistory['report_component'] = 'EPF ESI Report';
                        break;
                    case 'protax':
                        $dataForHistory['report_component'] = 'Professional Tax Report';
                        break;
                }
                break;
            case 'TAX':
                $dataForHistory['report_component'] = 'Tax Detailed Report';
                break;
            case 'ProfTax':
                $dataForHistory['report_type'] = "Professional Tax Summary Report";
                $dataForHistory['report_from'] = isset($arr_form_data['year']) ? $arr_form_data['year'] : '';
                $dataForHistory['report_component'] = isset($arr_form_data['report_from']) ? $arr_form_data['report_from'] : '';
                break;
            //edited by athira on 11-04-2025
            case 'ProfSalary':
                $dataForHistory['report_type'] = "Professional Tax Salary Report";
                $dataForHistory['report_from'] = isset($arr_form_data['year']) ? $arr_form_data['year'] : '';
                $dataForHistory['report_component'] = isset($arr_form_data['report_from']) ? $arr_form_data['report_from'] : '';
                break;
            //end
            case 'EPF':
                $dataForHistory['report_type'] = "EPF Report";
                break;
            case 'ESI':
                $dataForHistory['report_type'] = "ESI Report";
                break;
            case 'ESI_NEW':
                $dataForHistory['report_type'] = "ESI New Report";
                break;
            case '12BB':
                $dataForHistory['report_component'] = 'Form -12BB';
                break;
            case 'EPF_SYNTHIET':
                $dataForHistory['report_type'] = "EPF_SYNTHIET Report";
                break;
            case 'ESI_SYNTHIET':
                $dataForHistory['report_type'] = "ESI SYNTHIET Report";
                break;
            case 'PF_UPLOAD_SYNTHIET':
                $dataForHistory['report_type'] = "ESI SYNTHIET Report";
                break;
        }
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
    //function for dropdown
    public function generatereport($type = '', $mode = '')
    {
        $str_company_code = strtoupper($this->Session->read('company_code'));
        $this->autoRender = false;
        //debug($mode);
        switch ($type) {
            case 'Labour':
                $this->generateemployeelabour($mode);
                break;
            //added by megha on 30/09/2019  PT Report ----3
            case 'ProfTax':
                $this->generateprofessionaltaxreport($mode);
                break;
            //edited by athira on 11-04-2025
            case 'ProfSalary':
                if ($str_company_code == 'KWMT' || $str_company_code == 'GLET' || $str_company_code == 'DEMO') { // Edited by Akshay on 16-6-2025
                    $this->generateprofessionaltaxsalaryreportKWMT($mode);
                } else {
                    $this->generateprofessionaltaxsalaryreport();
                }

                break;
            //end
            case 'ESI':
                $this->generateESIlabourreport($mode);
                break;
            case 'ESI_NEW':
                $this->generateESINEWlabourreport($mode);
                break;
            case 'EPF':
                $this->generateEPFlabourreport($mode);
                break;
            case 'EPF_NEW':
                $this->generateEPFNEWlabourreport($mode);
                break;
            case 'TAX':
                $this->generateTAXreport($mode);
                break;
            case 'MobilelocationRep':
                $this->generatemobilelocationreport($type, $mode);
                break;
            case '12BB':
                $this->generate12BBFormreport($mode);
                break;
            case 'EPF_SYNTHIET':
                $this->generateEPFSYNTHIETlabourreport($mode);
                break;
            case 'ESI_SYNTHIET':
                $this->generateESISYNTHIETlabourreport($mode);
                break;
            case 'PF_UPLOAD_SYNTHIET':                                                   //edited by ASHIN on 10-08-24
                $this->generateEPFUPLOADSYNTHIETlabourreport($mode);
                break;
            default:
                return false;
                break;
        }
        $this->reportAudit($type, $mode);
    }

    public function listemployeefields()
    {
        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
            $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
            $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
            $arr_empinformation_fields->getFieldHeadings('Departments'),
            $arr_empinformation_fields->getFieldHeadings('Grades'),
            $arr_empinformation_fields->getFieldHeadings('Verticals'),
            $arr_empinformation_fields->getFieldHeadings('Units')
        );
        $arr_emp_field_names = array(
            'EmployeeDetails' => $arr_empinformation_fields->getFieldNames('EmployeeDetails'),
            'EmployeeProfessionalDetails' => $arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails'),
            'Departments' => $arr_empinformation_fields->getFieldNames('Departments'),
            'Grades' => $arr_empinformation_fields->getFieldNames('Grades'),
            'Verticals' => $arr_empinformation_fields->getFieldNames('Verticals'),
            'Units' => $arr_empinformation_fields->getFieldNames('Units')
        );

        $resp_emp = array();
        $resp_emp["rows"] = array();
        foreach ($arr_emp_field_names as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $data['id'] = $key . '.' . $key1;
                $data['data'] = array($value1);
                $resp_emp["rows"][] = $data;
            }
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    private function _modelExists($modelName)
    {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }
    //edited by megha tax report on 13_06_19  
    private function generateemployeelabour($mode = '')
    {
        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $report_component = $arr_form_data['report_component'];
        $this->set('report_component', $report_component);
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc
                                                    where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc
                                                    ORDER BY emp_salary_slip_pkey ");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }
        //debug($array_key);
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $monthname = $this->EmpCtcTransaction->query("select MONTHNAME('$otdate') as month");
        $year = $this->EmpCtcTransaction->query("select YEAR('$otdate') as year");
        $this->set('monthname', $monthname);
        $this->set('year', $year);
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }

        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $conditions = array();
        $conditions[] = 'and ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {
            $resign_condition = " and emp_details.status = '1' ";
        }
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $arr_salary_for_template = array();
        //debug($id);
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

            //            $arr_emp_info = $this->EmpCtcTransaction->query(" select * from employee_info where  emp_pkey =  '$leavepolicygroupid' ");
            //            $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
            //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,
            //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
            //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
            //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
            //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
            //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
            //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
            //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
            //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
            //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
            //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
            //abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
            //AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 
            //where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' and end_date_effective is null)
            // and emp_details.branch_Code = '$leavepolicygroupid' " );
            //edited by megha on 11_6_19 add tds and pt       
            $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,
abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0)) as Esi ,
abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY ,
abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'tds' and status=1) and end_date_effective is null) ,0)) as TDS ,emp_details.pan_no,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components 
where lcase(tax_salary_components_name)= 'professional tax' and status=1) and end_date_effective is null) ,0)) as Professional_Tax,
ifnull((select ptv.amount from professional_tax_view as ptv where ptv.emp_fkey = employee_info.emp_pkey  
and ptv.month_year = '$from' and ptv.source = 'settle') ,0) as Settle_PT 
 from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from'  and end_date_effective is null)
and emp_details.branch_Code = '$leavepolicygroupid' $resign_condition ");
            if (!empty($arr_gross)) {
                $arr_salary_for_template[] = $arr_gross;
                $k++;
            }
        }
        //  debug($arr_salary_for_template);

        //}
        $this->set('keys', $arr_keys);
        $this->set('array_key', $array_key);
        //debug($gross);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        //debug($arr_salary_for_template); 
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        //Set informations needed for report

        switch ($mode) {
            case 'pdf':
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('Empstatutony');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                //edited by megha on 11_6_19 add tds and pt
                $html2pdf->Output('StatutoryReport.pdf', 'D');
                $this->render('Empstatutony');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "StatutoryReport.xlsx" : "StatutoryReport" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("StatutoryReport Report By Forsight");
                if ($report_component == 'esi') {
                    $objPHPExcel->setActiveSheetIndex(0);
                    $worksheet = $objPHPExcel->getActiveSheet();
                    $worksheet->setCellValueByColumnAndRow(0, 1, "ESI EPF Reports - " . $monthname['0']['0']['month'] . " " . $year['0']['0']['year']);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    for ($col = 'A'; $col !== 'M'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:L1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $rowcount = 1;

                    $i = 0;

                    foreach ($arr_salary_for_template as $value) {

                        //                debug($value);
                        if (count($value) > 0) {
                            $col = 0;
                            $rowcount++;
                            $branch = isset($value['0']['employee_info']['branch']) ? $value['0']['employee_info']['branch'] : '';
                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Statutory Report of " . $branch);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            $worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $rowcount++;
                            $col = 0;

                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            for ($col = 'A'; $col !== 'H'; $col++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getColumnDimension($col)
                                    ->setAutoSize(true);
                            }
                            $worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );


                            $worksheet->setCellValueByColumnAndRow(8, $rowcount, "Employee Contribution");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                            for ($col = 'I'; $col !== 'K'; $col++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getColumnDimension($col)
                                    ->setAutoSize(true);
                            }
                            $worksheet->mergeCells('I' . $rowcount . ':K' . $rowcount);
                            $worksheet->getStyle('I' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );


                            $worksheet->setCellValueByColumnAndRow(11, $rowcount, "Employer Contribution");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                            for ($col = 'L'; $col !== 'N'; $col++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getColumnDimension($col)
                                    ->setAutoSize(true);
                            }
                            $worksheet->mergeCells('L' . $rowcount . ':N' . $rowcount);
                            $worksheet->getStyle('L' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $rowcount++;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee Name   ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee ID  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Joining Date ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Branch  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Designation ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Gross Salary ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  EPF ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  ESI ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, '  WWF ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, '  EPF  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  ESI  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  WWF  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);
                            $col = 7;
                            $rowcount = $rowcount + 1;
                            $arr_data = $value;
                            $i = 0;
                            $emp_epf = 0;
                            $j = 0;
                            $emp_esi = 0;
                            $emp_wwf = 0;
                            $empr_epf = 0;
                            $empr_esi = 0;
                            $empr_wwf = 0;

                            foreach ($arr_data as $val) {
                                if ($val['0']['EPF'] != '0' || $val['0']['Esi'] != '0' || $val['0']['WWF'] != '0' || $val['0']['EMPLOYER_EPF'] != '0' || $val['0']['EMPLOYER_ESI'] != '0' || $val['0']['EMPLOYER_WWFS'] != '0') {
                                    $col = 0;
                                    $name = $val['employee_info']['EmpName'];
                                    $id = $val['employee_info']['employee_id'];
                                    $join_date = $val['employee_info']['joining_date'];
                                    $dep = $val['employee_info']['department'];
                                    $deg = $val['employee_info']['designation'];
                                    $branch = $val['employee_info']['branch'];
                                    $gross = round($val['0']['SALARY'], 2);
                                    $epf_salary = round($val['0']['EPF'], 2);
                                    $esi_salary = round($val['0']['Esi'], 2);
                                    $www_salary = round($val['0']['WWF'], 2);
                                    $epf = round($val['0']['EMPLOYER_EPF'], 2);
                                    $esi = round($val['0']['EMPLOYER_ESI'], 2);
                                    $www = round($val['0']['EMPLOYER_WWFS'], 2);
                                    $emp_epf += $epf_salary;
                                    $emp_esi += $esi_salary;
                                    $emp_wwf += $www_salary;
                                    $empr_epf += $epf;
                                    $empr_esi += $esi;
                                    $empr_wwf += $www;
                                    $j = $j + 1;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $join_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $dep);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $deg);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $gross);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $epf_salary);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $esi_salary);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $www_salary);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $epf);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, $esi);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $www);
                                    $col = 7;
                                    //                    $j++;
                                    $rowcount++;
                                }
                            }
                            $col = 0;
                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            for ($col = 'A'; $col !== 'H'; $col++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getColumnDimension($col)
                                    ->setAutoSize(true);
                            }
                            $worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $emp_epf);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $emp_esi);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $emp_wwf);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $empr_epf);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, $empr_esi);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $empr_wwf);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setTitle('ESI EPF Reports');
                } else if ($report_component == 'tax') {
                    $objPHPExcel->setActiveSheetIndex(0);
                    $worksheet = $objPHPExcel->getActiveSheet();
                    $worksheet->setCellValueByColumnAndRow(0, 1, "TDS Reports - " . $monthname['0']['0']['month'] . " " . $year['0']['0']['year']);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    for ($col = 'A'; $col !== 'I'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:I1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $rowcount = 1;

                    $i = 0;

                    foreach ($arr_salary_for_template as $value) {

                        //                debug($value);
                        if (count($value) > 0) {
                            $col = 0;
                            $rowcount++;
                            $branch = isset($value['0']['employee_info']['branch']) ? $value['0']['employee_info']['branch'] : '';
                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Statutory Report of " . $branch);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $rowcount++;
                            $col = 0;

                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            for ($col = 'A'; $col !== 'I'; $col++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getColumnDimension($col)
                                    ->setAutoSize(true);
                            }
                            $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );


                            $rowcount++;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee Name   ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee ID  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Joining Date ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Branch  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Designation ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  PAN Number ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Gross Salary ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  TDS ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                            $col = 8;
                            $rowcount = $rowcount + 1;
                            $arr_data = $value;
                            $i = 0;
                            $emp_epf = 0;
                            $j = 0;
                            $emp_esi = 0;
                            $emp_wwf = 0;
                            $empr_epf = 0;
                            $empr_esi = 0;
                            $empr_wwf = 0;
                            $tds = 0;
                            $pt = 0;

                            foreach ($arr_data as $val) {
                                if ($val['0']['TDS'] != '0') {
                                    $col = 0;
                                    $tdsval = $val['0']['TDS'];

                                    $protax = round($val['0']['Professional_Tax']);
                                    $name = $val['employee_info']['EmpName'];
                                    $id = $val['employee_info']['employee_id'];
                                    $join_date = $val['employee_info']['joining_date'];
                                    $dep = $val['employee_info']['department'];
                                    $deg = $val['employee_info']['designation'];
                                    $pan = $val['emp_details']['pan_no'];
                                    $branch = $val['employee_info']['branch'];
                                    $gross = round($val['0']['SALARY'], 2);
                                    $epf_salary = round($val['0']['EPF'], 2);
                                    $esi_salary = round($val['0']['Esi'], 2);
                                    $www_salary = round($val['0']['WWF'], 2);
                                    $epf = round($val['0']['EMPLOYER_EPF'], 2);
                                    $esi = round($val['0']['EMPLOYER_ESI'], 2);
                                    $www = round($val['0']['EMPLOYER_WWFS'], 2);
                                    $tds += $tdsval;
                                    $pt += $protax;
                                    $emp_epf += $epf_salary;
                                    $emp_esi += $esi_salary;
                                    $emp_wwf += $www_salary;
                                    $empr_epf += $epf;
                                    $empr_esi += $esi;
                                    $empr_wwf += $www;
                                    $j = $j + 1;
                                    $tdsval = round($tdsval);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $join_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $dep);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $deg);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $pan);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $gross);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $tdsval);
                                    $col = 8;
                                    //                    $j++;
                                    $rowcount++;
                                }
                            }
                            $col = 0;
                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            for ($col = 'A'; $col !== 'J'; $col++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getColumnDimension($col)
                                    ->setAutoSize(true);
                            }
                            $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $tds = round($tds);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $tds);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setTitle('TDS Reports');
                } else {
                    $objPHPExcel->setActiveSheetIndex(0);
                    $worksheet = $objPHPExcel->getActiveSheet();
                    $worksheet->setCellValueByColumnAndRow(0, 1, "Professional Tax Reports - " . $monthname['0']['0']['month'] . " " . $year['0']['0']['year']);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    for ($col = 'A'; $col !== 'I'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:I1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );


                    $rowcount = 1;

                    $i = 0;

                    foreach ($arr_salary_for_template as $value) {

                        //                debug($value);
                        if (count($value) > 0) {
                            $col = 0;
                            $rowcount++;
                            $branch = isset($value['0']['employee_info']['branch']) ? $value['0']['employee_info']['branch'] : '';
                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Statutory Report of " . $branch);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $rowcount++;
                            $col = 0;

                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            for ($col = 'A'; $col !== 'I'; $col++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getColumnDimension($col)
                                    ->setAutoSize(true);
                            }
                            $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );


                            $rowcount++;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee Name   ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee ID  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Joining Date ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Branch  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Designation ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Gross Salary ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Professional Tax ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);

                            $col = 7;
                            $rowcount = $rowcount + 1;
                            $arr_data = $value;
                            $i = 0;
                            $emp_epf = 0;
                            $j = 0;
                            $emp_esi = 0;
                            $emp_wwf = 0;
                            $empr_epf = 0;
                            $empr_esi = 0;
                            $empr_wwf = 0;
                            $tds = 0;
                            $pt = 0;

                            foreach ($arr_data as $val) {
                                if ($val['0']['Professional_Tax'] != '0') {
                                    $col = 0;
                                    $tdsval = round($val['0']['TDS']);
                                    //added by megha on 28/09/2019 pt settlement amount
                                    $settle_pt = ($val['0']['Settle_PT']) * -1;
                                    if ($settle_pt == 0) {
                                        $pro_tax = round($val['0']['Professional_Tax']);
                                        $pt += round($val['0']['Professional_Tax'], 2);
                                    } else {
                                        $pro_tax = round($settle_pt);
                                        $pt += round($settle_pt, 2);
                                    }
                                    //$protax = round($val['0']['Professional_Tax']);
                                    $name = $val['employee_info']['EmpName'];
                                    $id = $val['employee_info']['employee_id'];
                                    $join_date = $val['employee_info']['joining_date'];
                                    $dep = $val['employee_info']['department'];
                                    $deg = $val['employee_info']['designation'];
                                    $branch = $val['employee_info']['branch'];
                                    $gross = round($val['0']['SALARY'], 2);
                                    $epf_salary = round($val['0']['EPF'], 2);
                                    $esi_salary = round($val['0']['Esi'], 2);
                                    $www_salary = round($val['0']['WWF'], 2);
                                    $epf = round($val['0']['EMPLOYER_EPF'], 2);
                                    $esi = round($val['0']['EMPLOYER_ESI'], 2);
                                    $www = round($val['0']['EMPLOYER_WWFS'], 2);
                                    $tds += $tdsval;
                                    //$pt += $protax;
                                    $emp_epf += $epf_salary;
                                    $emp_esi += $esi_salary;
                                    $emp_wwf += $www_salary;
                                    $empr_epf += $epf;
                                    $empr_esi += $esi;
                                    $empr_wwf += $www;
                                    $j = $j + 1;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $join_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $dep);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $deg);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $gross);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $pro_tax);
                                    $col = 7;
                                    //                    $j++;
                                    $rowcount++;
                                }
                            }
                            $col = 0;
                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            for ($col = 'A'; $col !== 'H'; $col++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getColumnDimension($col)
                                    ->setAutoSize(true);
                            }
                            $worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $pt = round($pt);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $pt);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);

                            $rowcount++;
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setTitle('Professional Tax Reports');
                }
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */
                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);
                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            case 'print':
                //   echo "entered in";
                $this->set('mode', 'print');
                $this->render('Empstatutony');
                break;
            default:
                $this->set('mode', '');
                $this->render('Empstatutony');
                break;
        }
    }
    //    private function generateemployeelabour($mode = '') {
    //        $arr_form_data = $_REQUEST;
    //        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
    //
    //        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc
    //                                                    where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc
    //                                                    ORDER BY emp_salary_slip_pkey ");
    //        $array_key = array();
    //
    //        foreach ($arr_keys as $val) {
    //
    //            if ($val['ectc']['head_operator'] == 'Addition') {
    //                $array_key['Addition'][] = $val[0]['sal_head'];
    //            } else {
    //                $array_key['Deduction'][] = $val[0]['sal_head'];
    //            }
    //        }
    //        //debug($array_key);
    //        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
    //        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
    //        
    //        if(isset($arr_form_data['hidden-criteria' . 1]) == 0){
    //            echo "Choose Criteria ";
    //            return false;
    //        }
    //        
    //        if(isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0){
    //            echo "Choose Criteria ";
    //            return false;
    //        }
    //        $conditions = array();
    //        $conditions[] = 'and ectc.month_year="' . $from . '"';
    //        $arr_leavepolicygroupids = array();
    //        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
    //        for ($i = 1; $i <= $int_criterias_count; $i++) {
    //            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
    //            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
    //        }
    //        $arr_leavepolicydetails_for_template = array();
    //        $id = implode(' AND ', $conditions);
    //        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
    //            $k = 0;
    //        //$gross = array();
    //        //debug($id);
    //        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
    //
    ////            $arr_emp_info = $this->EmpCtcTransaction->query(" select * from employee_info where  emp_pkey =  '$leavepolicygroupid' ");
    //            $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
    //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,
    //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
    //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
    //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
    //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
    //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
    //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
    //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
    //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
    //abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
    //AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
    //abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
    //AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 
    //where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' and end_date_effective is null)
    // and emp_details.branch_Code = '$leavepolicygroupid' " );
    //            
    //           
    //            $arr_salary_for_template[] = $arr_gross;
    //            $k++;
    //        }
    ////        debug($arr_salary_for_template);
    //
    //        //}
    //        $this->set('keys', $arr_keys);
    //        $this->set('array_key', $array_key);
    //        //debug($gross);
    //        $this->set('arr_salary_for_template', $arr_salary_for_template);
    //        //debug($arr_salary_for_template); 
    //        $cr = $arr_form_data['select-criteria1'];
    //        $this->set('cr', $cr);
    //        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
    //        $user_name = $this->Session->read('user_name');
    //        $this->set('user_name', $user_name);
    //        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
    //        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
    //        $this->set('month', $from);
    //        //Set informations needed for report
    //
    //        switch ($mode) {
    //            case 'pdf' :
    //                //   echo "entered in";
    //                $this->set('mode', 'pdf');
    //                $view = new View($this, false);
    //                $view_output = $view->render('Empstatutony');
    //                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
    //
    //                $html2pdf = new HTML2PDF('L', 'A4', 'en');
    //                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
    //                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
    //                $html2pdf->pdf->SetDisplayMode('fullpage');
    //                $html2pdf->writeHTML($view_output);
    //                $html2pdf->Output('ESIEPFReport.pdf', 'D');
    //                $this->render('Empstatutory');
    //                break;
    //            case 'excel' :
    //                $str_company_code = $this->Session->read('company_code');
    //                $file_name = isset($str_company_code) ? $str_company_code . "ESIEPF.xlsx" : "ESIEPF" . strtotime() . ".xlsx";
    //                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
    //                $objPHPExcel = new PHPExcel();
    //                $objPHPExcel->getProperties()->setCreator("Administrator");
    //                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
    //                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
    //                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
    //                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
    //                $objPHPExcel->setActiveSheetIndex(0);
    //                $worksheet = $objPHPExcel->getActiveSheet();
    //                $worksheet->setCellValueByColumnAndRow(0, 1, "ESI EPF Reports - Month:" . $from);
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
    //                for ($col = 'A'; $col !== 'M'; $col++) {
    //                    $objPHPExcel->getActiveSheet()
    //                            ->getColumnDimension($col)
    //                            ->setAutoSize(true);
    //                }
    //                $worksheet->mergeCells('A1:L1');
    //                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
    //                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
    //                );
    //                $rowcount = 2;
    //
    //                $i = 0;
    //
    //                foreach ($arr_salary_for_template as $value) {
    ////                debug($value);
    //                if (count($value) > 0) {
    //                $rowcount++;     
    //                $col = 0;
    //                
    //                $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
    //                for ($col = 'A'; $col !== 'H'; $col++) {
    //                    $objPHPExcel->getActiveSheet()
    //                            ->getColumnDimension($col)
    //                            ->setAutoSize(true);
    //                }
    //                $worksheet->mergeCells('A'.$rowcount.':H'.$rowcount);
    //                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
    //                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
    //                );
    //                
    //                
    //                $worksheet->setCellValueByColumnAndRow(8, $rowcount, "Employee Contribution");
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
    //                for ($col = 'I'; $col !== 'K'; $col++) {
    //                    $objPHPExcel->getActiveSheet()
    //                            ->getColumnDimension($col)
    //                            ->setAutoSize(true);
    //                }
    //                $worksheet->mergeCells('I'.$rowcount.':K'.$rowcount);
    //                $worksheet->getStyle('I'.$rowcount)->getAlignment()->applyFromArray(
    //                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
    //                );
    //                
    //                
    //                $worksheet->setCellValueByColumnAndRow(11, $rowcount, "Employer Contribution");
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
    //                for ($col = 'L'; $col !== 'N'; $col++) {
    //                    $objPHPExcel->getActiveSheet()
    //                            ->getColumnDimension($col)
    //                            ->setAutoSize(true);
    //                }
    //                $worksheet->mergeCells('L'.$rowcount.':N'.$rowcount);
    //                $worksheet->getStyle('L'.$rowcount)->getAlignment()->applyFromArray(
    //                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
    //                );
    //                $rowcount++;
    //                
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee Name   ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee ID  ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Joining Date ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Branch  ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Designation ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Gross Salary ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  EPF Salary ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  ESI Salary ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, '  WWF Salary ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, '  EPF  ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  ESI  ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);
    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  WWF  ');
    //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);
    //
    //
    //
    //                $col = 7;
    //
    //
    //                $rowcount = $rowcount + 1;
    //                $arr_data = $value;
    //                $i = 0;
    //                $emp_epf = 0;
    //                $j = 0;
    //                $emp_esi = 0;
    //                $emp_wwf = 0;
    //                $empr_epf = 0;
    //                $empr_esi = 0;
    //                $empr_wwf = 0;
    //                
    //                foreach ($arr_data as $val) {
    //                    
    //                    $col = 0;
    //                    $name = $val['employee_info']['EmpName'];
    //                    $id = $val['employee_info']['employee_id'];
    //                    $join_date = $val['employee_info']['joining_date'];
    //                    $dep = $val['employee_info']['department'];
    //                    $deg = $val['employee_info']['designation'];
    //                    $branch = $val['employee_info']['branch'];
    //                    $gross = round($val['0']['SALARY'],2);
    //                    $epf_salary = round($val['0']['EPF'],2);
    //                    $esi_salary = round($val['0']['Esi'],2);
    //                    $www_salary = round($val['0']['WWF'],2);
    //                    $epf = round($val['0']['EMPLOYER_EPF'],2);
    //                    $esi = round($val['0']['EMPLOYER_ESI'],2);
    //                    $www = round($val['0']['EMPLOYER_WWFS'],2);
    //                    $emp_epf += $epf_salary;
    //                    $emp_esi += $esi_salary;
    //                    $emp_wwf += $www_salary;
    //                    $empr_epf += $epf;
    //                    $empr_esi += $esi;
    //                    $empr_wwf += $www;
    //                    
    //                    $j = $j+1;
    //
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $name);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $id);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $join_date);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $branch);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $dep);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $deg);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $gross);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $epf_salary);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $esi_salary);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $www_salary);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $epf);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, $esi);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $www);
    //
    //                    $col = 7;
    //
    //
    //
    //
    ////                    $j++;
    //                    $rowcount++;
    //                    
    //                }
    //                $col = 0;
    //                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");
    //                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
    //                        for ($col = 'A'; $col !== 'H'; $col++) {
    //                            $objPHPExcel->getActiveSheet()
    //                                    ->getColumnDimension($col)
    //                                    ->setAutoSize(true);
    //                        }
    //                        $worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount);
    //                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
    //                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
    //                        );
    //
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col+8) . $rowcount, $emp_epf);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col+9) . $rowcount, $emp_esi);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col+10) . $rowcount, $emp_wwf);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col+11) . $rowcount, $empr_epf);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col+12) . $rowcount, $empr_esi);
    //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col+13) . $rowcount, $empr_wwf);
    //                    
    //                    $rowcount++;
    //                }
    //                }
    ////                                     
    //                $objPHPExcel->getActiveSheet()->setTitle('ESI EPF Reports');
    //                /* header footer */
    //                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
    //                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
    //                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
    //                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
    //                /* header footer */
    //                /* print Set up */
    //                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    //                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
    //                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
    //                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
    //                /* print Set up */
    //                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    //                $objWriter->save(dirname(__FILE__) . "/" . $file_name);
    //                // output headers so that the file is downloaded rather than displayed
    //                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    //                header('Content-Disposition: attachment; filename=' . $file_name);
    //
    //                readfile(dirname(__FILE__) . "/" . $file_name);
    //                unlink(dirname(__FILE__) . "/" . $file_name);
    //                break;
    //            case 'print' :
    //                //   echo "entered in";
    //                $this->set('mode', 'print');
    //                $this->render('Empstatutony');
    //                break;    
    //            default :
    //                $this->set('mode', '');
    //                $this->render('Empstatutony');
    //                break;
    //        }
    //    }
    //commented by megha on 12_6_19

    private function generateESIlabourreport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc
                                                        where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc
                                                        ORDER BY emp_salary_slip_pkey ");
        $array_key = array();



        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $prevMonth = date('Y-m', strtotime($from . '-01 -1 month')); // Edited by Akshay on 2-1-2026
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        //added by megha on 24_07_19
        $this->set('from', $from);
        $this->set('prevMonth', $prevMonth); // Edited by Akshay on 2-1-2026
        $this->set('otdate', $otdate);
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }

        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }

        // Edited by Akshay on 30-6-2025
        // 1. Get ESI salary_head_item_fkey
        $arr_esi_fkey = $this->EmpCtcTransaction->query("SELECT salary_head_item_fkey FROM tax_salary_components WHERE LCASE(tax_salary_components_name) = 'employee esi' AND status = 1");
        $esi_fkey = $arr_esi_fkey[0]['tax_salary_components']['salary_head_item_fkey'];
        // End

        $conditions = array();
        $conditions[] = 'and ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $needBranchWiseReport = false;
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            $this->set('needBranchWiseReport', $needBranchWiseReport);
            //   belongs to branch section added by megha ends...
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        $arr_leavepolicydetails_for_template = array();
        $arr_salary_for_template = array();
        $arr_gross = array();
        $arr_termination = array(); // Edited by Akshay on 1-1-2026
        $id = implode(' AND ', $conditions);
        // if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
        $k = 0;
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {
            $resign_condition = " and emp_details.status = '1' ";
        }

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {

            // Edited by Akshay on 1-1-2025
            $arr_total_days = $this->EmpCtcTransaction->query("SELECT DAY(LAST_DAY(CONCAT('$from', '-01'))) AS total_days;");
            $total_days_in_month = isset($arr_total_days[0][0]['total_days']) ? $arr_total_days[0][0]['total_days'] : 0;
            $this->set('total_days_in_month', $total_days_in_month);
            // End

            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'Units') {


                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi, 
                        termination.last_approved_working_date,

                            -- Edited by Akshay on 1-1-2025
                            (SELECT lop_total FROM attendance_register WHERE emp_fkey = employee_info.emp_pkey AND month_year = '$from') as lop_total,
                            (SELECT
                                                        (
                                                            IFNULL(CASE WHEN FIELD1  = 'NA' THEN 1 WHEN FIELD1  LIKE 'NA/%' OR FIELD1  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD2  = 'NA' THEN 1 WHEN FIELD2  LIKE 'NA/%' OR FIELD2  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD3  = 'NA' THEN 1 WHEN FIELD3  LIKE 'NA/%' OR FIELD3  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD4  = 'NA' THEN 1 WHEN FIELD4  LIKE 'NA/%' OR FIELD4  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD5  = 'NA' THEN 1 WHEN FIELD5  LIKE 'NA/%' OR FIELD5  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD6  = 'NA' THEN 1 WHEN FIELD6  LIKE 'NA/%' OR FIELD6  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD7  = 'NA' THEN 1 WHEN FIELD7  LIKE 'NA/%' OR FIELD7  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD8  = 'NA' THEN 1 WHEN FIELD8  LIKE 'NA/%' OR FIELD8  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD9  = 'NA' THEN 1 WHEN FIELD9  LIKE 'NA/%' OR FIELD9  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD10 = 'NA' THEN 1 WHEN FIELD10 LIKE 'NA/%' OR FIELD10 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD11 = 'NA' THEN 1 WHEN FIELD11 LIKE 'NA/%' OR FIELD11 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD12 = 'NA' THEN 1 WHEN FIELD12 LIKE 'NA/%' OR FIELD12 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD13 = 'NA' THEN 1 WHEN FIELD13 LIKE 'NA/%' OR FIELD13 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD14 = 'NA' THEN 1 WHEN FIELD14 LIKE 'NA/%' OR FIELD14 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD15 = 'NA' THEN 1 WHEN FIELD15 LIKE 'NA/%' OR FIELD15 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD16 = 'NA' THEN 1 WHEN FIELD16 LIKE 'NA/%' OR FIELD16 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD17 = 'NA' THEN 1 WHEN FIELD17 LIKE 'NA/%' OR FIELD17 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD18 = 'NA' THEN 1 WHEN FIELD18 LIKE 'NA/%' OR FIELD18 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD19 = 'NA' THEN 1 WHEN FIELD19 LIKE 'NA/%' OR FIELD19 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD20 = 'NA' THEN 1 WHEN FIELD20 LIKE 'NA/%' OR FIELD20 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD21 = 'NA' THEN 1 WHEN FIELD21 LIKE 'NA/%' OR FIELD21 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD22 = 'NA' THEN 1 WHEN FIELD22 LIKE 'NA/%' OR FIELD22 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD23 = 'NA' THEN 1 WHEN FIELD23 LIKE 'NA/%' OR FIELD23 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD24 = 'NA' THEN 1 WHEN FIELD24 LIKE 'NA/%' OR FIELD24 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD25 = 'NA' THEN 1 WHEN FIELD25 LIKE 'NA/%' OR FIELD25 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD26 = 'NA' THEN 1 WHEN FIELD26 LIKE 'NA/%' OR FIELD26 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD27 = 'NA' THEN 1 WHEN FIELD27 LIKE 'NA/%' OR FIELD27 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD28 = 'NA' THEN 1 WHEN FIELD28 LIKE 'NA/%' OR FIELD28 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD29 = 'NA' THEN 1 WHEN FIELD29 LIKE 'NA/%' OR FIELD29 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD30 = 'NA' THEN 1 WHEN FIELD30 LIKE 'NA/%' OR FIELD30 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD31 = 'NA' THEN 1 WHEN FIELD31 LIKE 'NA/%' OR FIELD31 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD32 = 'NA' THEN 1 WHEN FIELD32 LIKE 'NA/%' OR FIELD32 LIKE '%/NA' THEN 0.5 ELSE 0 END,0)
                                                        )
                                                        FROM attendance_register
                                                        WHERE emp_fkey = employee_info.emp_pkey
                                                        AND month_year = '$from'
                                                        AND isdelete = 'N') AS na_count,
                            -- End

                            abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
                            AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
                            abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
                            AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
                                (select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
                            AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null)  as ESI_REMARKS,
                            abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
                            AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info
                            left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
                            left join termination ON (termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1)
                            where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 
                            and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components
                            where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)
                            and emp_details.branch_code = '$leavepolicygroupid' $resign_condition");
                } else {


                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,
                                termination.last_approved_working_date,
                                
                                -- Edited by Akshay on 1-1-2025
                                (SELECT lop_total FROM attendance_register WHERE emp_fkey = employee_info.emp_pkey AND month_year = '$from') as lop_total,
                                (SELECT
                                                        (
                                                            IFNULL(CASE WHEN FIELD1  = 'NA' THEN 1 WHEN FIELD1  LIKE 'NA/%' OR FIELD1  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD2  = 'NA' THEN 1 WHEN FIELD2  LIKE 'NA/%' OR FIELD2  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD3  = 'NA' THEN 1 WHEN FIELD3  LIKE 'NA/%' OR FIELD3  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD4  = 'NA' THEN 1 WHEN FIELD4  LIKE 'NA/%' OR FIELD4  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD5  = 'NA' THEN 1 WHEN FIELD5  LIKE 'NA/%' OR FIELD5  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD6  = 'NA' THEN 1 WHEN FIELD6  LIKE 'NA/%' OR FIELD6  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD7  = 'NA' THEN 1 WHEN FIELD7  LIKE 'NA/%' OR FIELD7  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD8  = 'NA' THEN 1 WHEN FIELD8  LIKE 'NA/%' OR FIELD8  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD9  = 'NA' THEN 1 WHEN FIELD9  LIKE 'NA/%' OR FIELD9  LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD10 = 'NA' THEN 1 WHEN FIELD10 LIKE 'NA/%' OR FIELD10 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD11 = 'NA' THEN 1 WHEN FIELD11 LIKE 'NA/%' OR FIELD11 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD12 = 'NA' THEN 1 WHEN FIELD12 LIKE 'NA/%' OR FIELD12 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD13 = 'NA' THEN 1 WHEN FIELD13 LIKE 'NA/%' OR FIELD13 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD14 = 'NA' THEN 1 WHEN FIELD14 LIKE 'NA/%' OR FIELD14 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD15 = 'NA' THEN 1 WHEN FIELD15 LIKE 'NA/%' OR FIELD15 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD16 = 'NA' THEN 1 WHEN FIELD16 LIKE 'NA/%' OR FIELD16 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD17 = 'NA' THEN 1 WHEN FIELD17 LIKE 'NA/%' OR FIELD17 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD18 = 'NA' THEN 1 WHEN FIELD18 LIKE 'NA/%' OR FIELD18 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD19 = 'NA' THEN 1 WHEN FIELD19 LIKE 'NA/%' OR FIELD19 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD20 = 'NA' THEN 1 WHEN FIELD20 LIKE 'NA/%' OR FIELD20 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD21 = 'NA' THEN 1 WHEN FIELD21 LIKE 'NA/%' OR FIELD21 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD22 = 'NA' THEN 1 WHEN FIELD22 LIKE 'NA/%' OR FIELD22 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD23 = 'NA' THEN 1 WHEN FIELD23 LIKE 'NA/%' OR FIELD23 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD24 = 'NA' THEN 1 WHEN FIELD24 LIKE 'NA/%' OR FIELD24 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD25 = 'NA' THEN 1 WHEN FIELD25 LIKE 'NA/%' OR FIELD25 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD26 = 'NA' THEN 1 WHEN FIELD26 LIKE 'NA/%' OR FIELD26 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD27 = 'NA' THEN 1 WHEN FIELD27 LIKE 'NA/%' OR FIELD27 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD28 = 'NA' THEN 1 WHEN FIELD28 LIKE 'NA/%' OR FIELD28 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD29 = 'NA' THEN 1 WHEN FIELD29 LIKE 'NA/%' OR FIELD29 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD30 = 'NA' THEN 1 WHEN FIELD30 LIKE 'NA/%' OR FIELD30 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD31 = 'NA' THEN 1 WHEN FIELD31 LIKE 'NA/%' OR FIELD31 LIKE '%/NA' THEN 0.5 ELSE 0 END,0) +
                                                            IFNULL(CASE WHEN FIELD32 = 'NA' THEN 1 WHEN FIELD32 LIKE 'NA/%' OR FIELD32 LIKE '%/NA' THEN 0.5 ELSE 0 END,0)
                                                        )
                                                        FROM attendance_register
                                                        WHERE emp_fkey = employee_info.emp_pkey
                                                        AND month_year = '$from'
                                                        AND isdelete = 'N') AS na_count,
                                -- End

                                abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
                                AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
                                abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'
                                AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
                                (select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'
                                AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) as ESI_REMARKS,
                                abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
                                AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info
                                left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
                                left join termination ON (termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1)
                                where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 
                                and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components
                                where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)
                                and emp_details.emp_pkey = '$leavepolicygroupid' $resign_condition");
                }

                // Edited by Akshay on 30-6-2025
                if (!empty($arr_gross)) {
                    foreach ($arr_gross as $emp_row) {
                        $emp_id = $emp_row['employee_info']['emp_pkey'];
                        $formula_string = '';
                        $esi_salary = 0;

                        // 1. Get ESI formula for this employee
                        $columns = $this->EmpCtcTransaction->getDataSource()->query("
                                SHOW COLUMNS FROM emp_salary_slip LIKE 'combined_base_value'
                            ");

                        $hasRemarks2 = !empty($columns); // true if combined_base_value exists

                        if ($hasRemarks2) {
                            $formula_result = $this->EmpCtcTransaction->query("
                                    SELECT combined_base_value 
                                    FROM emp_salary_slip 
                                    WHERE salary_head_item_fkey = $esi_fkey 
                                    AND emp_fkey = $emp_id
                                    AND month_year = '$from'
                                    AND end_date_effective IS NULL
                                ");
                            $esi_salary = isset($formula_result[0]['emp_salary_slip']['combined_base_value']) ? $formula_result[0]['emp_salary_slip']['combined_base_value'] : 0;
                        } else {
                            $formula_result = $this->EmpCtcTransaction->query("
                                    SELECT remarks 
                                    FROM emp_salary_slip 
                                    WHERE salary_head_item_fkey = $esi_fkey 
                                    AND emp_fkey = $emp_id
                                    AND month_year = '$from'
                                    AND end_date_effective IS NULL
                                ");

                            $formula_string = '';

                            if (!empty($formula_result)) {
                                $row = $formula_result[0]['emp_salary_slip'];

                                if (!empty($row['remarks_2'])) {
                                    $formula_string = $row['remarks_2'];
                                } elseif (!empty($row['remarks'])) {
                                    $formula_string = $row['remarks'];
                                }
                            }

                            // 2. Parse and calculate ESI salary
                            $esi_salary = 0;
                            if (!empty($formula_string)) {

                                $parts = explode('*', $formula_string);
                                $sum_part = trim($parts[0]); // (15000 + 2000 + 3000 )

                                // Step 2: Remove all spaces
                                $sum_part = str_replace(' ', '', $sum_part); // (15000+2000+3000)

                                // Step 3: Evaluate the expression
                                if (preg_match('/[a-zA-Z]/', $sum_part)) {
                                    $esi_salary = 0;
                                    $esi_salary = $emp_row['0']['SALARY'];
                                } else {
                                    eval('$esi_salary = ' . $sum_part . ';');
                                }
                            }
                        }

                        // Edited by Akshay on 3-1-2026
                        $hightlight = false;
                        $last_approved_working_date = isset($emp_row['termination']['last_approved_working_date']) ? $emp_row['termination']['last_approved_working_date'] : '';
                        if ($last_approved_working_date != '') {
                            $resignation_month = date('Y-m', strtotime($last_approved_working_date));
                            if ($resignation_month == $prevMonth) {
                                $hightlight = true;
                            }
                        }
                        // End

                        // 4. Push to result array
                        $arr_data = array(
                            'summary' => array($emp_row),
                            'esi_salary' => $esi_salary,
                            'hightlight' => $hightlight // Edited by Akshay on 3-1-2026
                        );
                        // debug($arr_data);
                        if ($arr_form_data['select-criteria1'] == 'Units') {
                            $arr_salary_for_template[$leavepolicygroupid][] = $arr_data;
                        } else {
                            $arr_salary_for_template[$leavepolicygroupid] = $arr_data;
                        }

                        $k++;
                    }
                }

                // End

            }
            // debug($arr_salary_for_template);
            // Edited by Akshay on 1-1-2026
            $prevMonth = date('Y-m', strtotime('-1 month', strtotime($from . '-01')));
            $commaSeparatedLeavepolicygroupids =  implode("','", $arr_leavepolicygroupids);

            if ($arr_form_data['select-criteria1'] == 'Units') {
                $conditon = " AND  ed.branch_code IN ('$commaSeparatedLeavepolicygroupids') ";
            } else {
                $conditon = " AND  ed.emp_pkey IN ('$commaSeparatedLeavepolicygroupids') ";
            }

            if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                $resign_condition = " and ed.status in ('1','2') ";
            } else {
                $resign_condition = " and ed.status = '1' ";
            }

            $arr_leavepolicygroupids = $this->EmpCtcTransaction->query("select ed.*, tm.last_approved_working_date, tm.Reason,
                                                                    ei.emp_id, ei.employee_id, ei.branch_code, ei.branch, ei.designation, ei.department, ei.joining_date
                                                                    from emp_details ed
                                                                    LEFT JOIN termination tm ON (ed.emp_pkey = tm.emp_fkey AND tm.status = 1)
                                                                    LEFT JOIN employee_info ei ON ed.emp_pkey = ei.emp_pkey
                                                                    where ed.status in (1, 2) AND ed.esi IS NOT NULL AND ed.esi !='' 
                                                                    $conditon
                                                                    $resign_condition
                                                                    AND DATE_FORMAT(tm.last_approved_working_date, '%Y-%m') = '$prevMonth';");

            foreach ($arr_leavepolicygroupids as $key => $val) {
                $leavepolicygroupid = isset($val['ed']['emp_pkey']) ? $val['ed']['emp_pkey'] : 0;
                $esi_number = isset($val['ed']['esi']) ? $val['ed']['esi'] : 0;
                $arr_esi_sal = $this->EmpCtcTransaction->query("SELECT salary_amount FROM emp_salary_slip ectc
                                                                    LEFT JOIN tax_salary_components tsc ON tsc.salary_head_item_Fkey = ectc.salary_head_item_fkey
                                                                    WHERE ectc.emp_fkey = '$leavepolicygroupid'
                                                                    AND ectc.end_date_effective IS NULL
                                                                    AND ectc.month_year = '$prevMonth'
                                                                    AND (tsc.tax_salary_components_name) = 'Employer ESI';");
                $esi_sal = isset($arr_esi_sal[0]['ectc']['salary_amount']) ? $arr_esi_sal[0]['ectc']['salary_amount'] : 0;

                if (isset($esi_number) && $esi_sal) {
                    $ipName = implode(' ', array_filter(array_map('trim', [
                        isset($val['ed']['first_name'])   ? $val['ed']['first_name']   : '',
                        isset($val['ed']['middile_name']) ? $val['ed']['middile_name'] : '',
                        isset($val['ed']['last_name'])    ? $val['ed']['last_name']    : ''
                    ])));

                    $empId = isset($val['ei']['employee_id	'])   ? $val['ei']['employee_id	']   : '';

                    $last_approved_working_date = isset($val['tm']['last_approved_working_date']) ? $val['tm']['last_approved_working_date'] : '';

                    $reason_code = 0;
                    $reason = isset($val['tm']['Reason']) ? strtoupper($val['tm']['Reason']) : '';
                    if ($reason) {
                        if ($reason == 'LEFT SERVICE' || $reason == 'RESIGNED' || $reason == 'RESIGNATION') { //Edited by Akshay on 18-4-2024
                            $reason_code = 2;
                        } elseif ($reason == 'RETIREMENT') {
                            $reason_code = 3;
                        } elseif ($reason == 'DEATH' || $reason == 'DEATH IN SERVICE') {
                            $reason_code = 5;
                        } elseif ($reason == 'RETRENCHMENT') {
                            $reason_code = 10;
                        } else {
                            $reason_code = 0;

                            //$working_days = ceil($wd - $lops);
                            $working_days = ceil($present);
                            if ($working_days == 0) {
                                $leave_count = $this->EmpCtcTransaction->query("SELECT count(*) as cnt FROM `leaveentries` WHERE LEAVESTATUS = 'Approved' AND `FROMDATE` LIKE '%$from%' AND `TODATE` LIKE '%$from%' AND EMP_fkey = $leavepolicygroupid");
                                if (isset($leave_count[0][0]["cnt"])) {
                                    $count = $leave_count[0][0]["cnt"];
                                    if ($count > 0) {
                                        $reason_code = 1;
                                    }
                                }
                            }
                        }
                    } else {
                        $leave_count = $this->EmpCtcTransaction->query("SELECT count(*) as cnt FROM `leaveentries` WHERE LEAVESTATUS = 'Approved' AND `FROMDATE` LIKE '%$from%' AND `TODATE` LIKE '%$from%' AND EMP_fkey = $leavepolicygroupid");
                        if (isset($leave_count[0][0]["cnt"])) {
                            $count = $leave_count[0][0]["cnt"];
                            if ($count > 0) {
                                $reason_code = 1;
                            }
                        }
                    }

                    $empId = isset($val['ei']['employee_id']) ? $val['ei']['employee_id'] : '';
                    $emp_id = isset($val['ei']['emp_id']) ? $val['ei']['emp_id'] : '';
                    $joinDate = isset($val['ei']['joining_date']) ? $val['ei']['joining_date'] : '';
                    $branchCode = isset($val['ei']['branch_code']) ? $val['ei']['branch_code'] : '';
                    $branch = isset($val['ei']['branch']) ? $val['ei']['branch'] : '';
                    $dept = isset($val['ei']['department']) ? $val['ei']['department'] : '';
                    $desg = isset($val['ei']['designation']) ? $val['ei']['designation'] : '';
                    $arr_resigned_data = array(
                        'IPNO' => $esi_number,
                        'IPNAME' => $ipName,
                        'EMPID' => $empId,
                        'REASONCODE' => $reason_code,
                        'last_approved_working_date' => $last_approved_working_date != '' ? date('d-m-Y', strtotime($last_approved_working_date)) : '',
                        'joining_date' => $joinDate,
                        'branch_code' => $branchCode,
                        'branch' => $branch,
                        'dept' => $dept,
                        'desg' => $desg
                    );

                    $emp_row['employee_info']['emp_pkey'] = $leavepolicygroupid;
                    $emp_row['employee_info']['branch_code'] = $branchCode;
                    $emp_row['employee_info']['emp_id'] = $emp_id;
                    $emp_row['employee_info']['EmpName'] = $ipName;
                    $emp_row['employee_info']['employee_id'] = $empId;
                    $emp_row['employee_info']['branch'] = $branch;
                    $emp_row['employee_info']['designation'] = $desg;
                    $emp_row['employee_info']['department'] = $dept;
                    $emp_row['employee_info']['joining_date'] = $joinDate;
                    $emp_row['employee_info']['grade']  = '';
                    $emp_row['employee_info']['emp_status'] = '';

                    $emp_row['emp_details']['esi'] = $esi_number;

                    $emp_row['termination']['last_approved_working_date'] = $last_approved_working_date;

                    $emp_row[0]['lop_total'] = 0;
                    $emp_row[0]['na_count'] = 0;
                    $emp_row[0]['Esi'] = 0;
                    $emp_row[0]['EMPLOYER_ESI'] = 0;
                    $emp_row[0]['ESI_REMARKS'] =  0;
                    $emp_row[0]['SALARY'] = 0;


                    $arr_data = array(
                        'summary' => array($emp_row),
                        'esi_salary' => 0
                    );
                    // debug($arr_data);
                    if ($arr_form_data['select-criteria1'] == 'Units') {
                        $arr_salary_for_template[$leavepolicygroupid][] = $arr_data;
                        $arr_termination[$branchCode][] = $arr_resigned_data;
                    } else {
                        if (!isset($arr_salary_for_template[$leavepolicygroupid])) {
                            $arr_salary_for_template[$leavepolicygroupid] = $arr_data;
                        }

                        $arr_termination[$leavepolicygroupid] = $arr_resigned_data;
                    }
                }
            }
            // End
        }

        $this->set('arr_termination', $arr_termination); // Edited by Akshay on 1-1-2026
        $this->set('keys', $arr_keys);
        $this->set('array_key', $array_key);
        //debug($gross);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        // debug($arr_salary_for_template); 
        // die();
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);

        // Edited by Akshay on 4-7-2025
        // Create a DateTime object for the first day of the month
        $firstDayObject = DateTime::createFromFormat('Y-m', $from);
        // Get number of days in the month
        $totalDaysInMonth = $firstDayObject->format('t');
        $this->set('totalDaysInMonth', $totalDaysInMonth);
        // End

        $cname = $arr_comp_contact_info['CompanyContactInfo']['business_name'];

        $arr_compliance = $this->EmpCtcTransaction->query("select emp_state_ins_no,pf_no,service_tax from compliance");
        // debug($arr_compliance);exit();
        $eip = isset($arr_compliance['0']['compliance']['emp_state_ins_no']) ? $arr_compliance['0']['compliance']['emp_state_ins_no'] : '';

        $this->set('eip', $eip);
        //Set informations needed for report

        switch ($mode) {
            case 'pdf':
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('empesi');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('ESIReport.pdf', 'D');
                $this->render('empesi');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "ESIReport.xlsx" : "ESIReport" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, $cname . " - ESI Report -" . $from);
                $objPHPExcel->getActiveSheet()->freezePane('D5'); // Edited by Akshay on 13-6-2025
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'M'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:O1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 2;
                if (!empty($arr_salary_for_template)) {
                    // Edited by Akshay on 13-6-2025
                    $lightGreyFill = array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'D9D9D9')
                        )
                    );
                    // End

                    $worksheet->setCellValueByColumnAndRow(0, 2, "Employer ESI No.:" . $eip);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':O' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $rowcount = 3;

                    $i = 0;
                    $col = 0;
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");
                    $worksheet->getStyle('A' . $rowcount)->applyFromArray($lightGreyFill); // Edited by Akshay on 13-6-2025
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );


                    $worksheet->setCellValueByColumnAndRow(9, $rowcount, "ESI Details");
                    $worksheet->getStyle('J' . $rowcount)->applyFromArray($lightGreyFill); // Edited by Akshay on 13-6-2025
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('J' . $rowcount . ':M' . $rowcount);
                    $worksheet->getStyle('J' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );


                    $worksheet->setCellValueByColumnAndRow(13, $rowcount, "ESI - Employee");
                    $worksheet->getStyle('N' . $rowcount)->applyFromArray($lightGreyFill); // Edited by Akshay on 13-6-2025
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                    $worksheet->setCellValueByColumnAndRow(14, $rowcount, "ESI - Employer");
                    $worksheet->getStyle('O' . $rowcount)->applyFromArray($lightGreyFill); // Edited by Akshay on 13-6-2025
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                    $worksheet->setCellValueByColumnAndRow(15, $rowcount, "Total");
                    $worksheet->getStyle('P' . $rowcount)->applyFromArray($lightGreyFill); // Edited by Akshay on 13-6-2025
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                    $rowcount++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee ID  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee Name   ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  ESI IP No  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Joining Date ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Branch  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Department ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Designation ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);

                    // Edited by Akshay on 13-6-2025
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Termination Date ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                    // End

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  Days Worked ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, '  Gross Salary ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, '  ESI Salary ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  Excluded Salary ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);
                    $chg_date = '2019-07-01';
                    if ($otdate < $chg_date) {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  1.75% ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, '  4.75%  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 14, $rowcount)->getFont()->setBold(true);
                    } else {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  0.75% ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, '  3.25%  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 14, $rowcount)->getFont()->setBold(true);
                    }

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, '  4%  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 15, $rowcount)->getFont()->setBold(true);

                    // Edited by Akshay on 13-6-2025
                    for ($i = 0; $i <= 15; $i++) {
                        $cell = PHPExcel_Cell::stringFromColumnIndex($i) . $rowcount;

                        $worksheet->getStyle($cell)->applyFromArray($lightGreyFill);

                        // ✅ Center align
                        $worksheet->getStyle($cell)
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $cell = PHPExcel_Cell::stringFromColumnIndex($i) . ($rowcount - 1);
                        $worksheet->getStyle($cell)
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    }
                    // End

                    $rowcount++;
                    $gross_tot = 0;
                    $pf_salary = 0;
                    $emp_ep = 0;
                    $epr_ep = 0;
                    $esi_t = 0;
                    $ip = 0;
                    $days = 0;
                    $day = 0;
                    $total = 0;
                    $total1 = 0;
                    $split1 = 0;
                    $split2 = 0;
                    $split3 = 0;
                    $esi_sal = 0;
                    $gross = 0;
                    //edited by megha on 10/08/2019 replace $j to outside loop.. serial no. duplication(1)
                    $j = 1;
                    // Edited by Akshay on 30-6-2025
                    if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) {
                        foreach ($arr_salary_for_template as $values) {
                            $branchCode = $values[0]['summary']['0']['employee_info']['branch_code']; // Edited by Akshay on 2-1-2025
                            foreach ($values as $value) {
                                // End
                                $arr_daata = $value['summary'];
                                $employees = $value;
                                if (empty($arr_daata))      continue;

                                if (count($value) > 0) {

                                    $arr_data = $value;

                                    $arr_e = $employees['summary'];
                                    $esi_salary = $employees['esi_salary']; // Edited by Akshay on 30-6-2025
                                    $hightlight = isset($employees['hightlight']) ?  $employees['hightlight'] : false; // Edited by Akshay on 3-1-2026
                                    $col = 0;
                                    foreach ($arr_e as $employee => $val) {
                                        if ($val['0']['Esi'] > 0) {
                                            $name = ucwords(strtolower($val['employee_info']['EmpName'])); // Edited by Akshay on 23-3-2026
                                            $id = $val['employee_info']['employee_id'];
                                            $ip = $val['emp_details']['esi'];
                                            $join_date = $val['employee_info']['joining_date'];
                                            $dep = ucwords(strtolower($val['employee_info']['department'])); // Edited by Akshay on 23-3-2026
                                            $deg = ucwords(strtolower($val['employee_info']['designation'])); // Edited by Akshay on 23-3-2026
                                            $branch = ucwords(strtolower($val['employee_info']['branch'])); // Edited by Akshay on 23-3-2026
                                            $termination_date = isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : ''; // Edited by Akshay on 13-6-2025

                                            $sal = ($val['0']['SALARY'] != '0') ? round($val['0']['SALARY']) : 0;
                                            $gross += round($val['0']['SALARY']);

                                            // Edited by Akshay on 1-1-2026
                                            $na_count = isset($val[0]['na_count']) ? $val[0]['na_count'] : 0;
                                            $lop_total = isset($val[0]['lop_total']) ? $val[0]['lop_total'] : 0;
                                            $days = ceil(max(0, ($total_days_in_month - ($lop_total))));
                                            // End

                                            $day += $days;
                                            $esi_remarks = isset($val['0']['ESI_REMARKS']) ? $val['0']['ESI_REMARKS'] : 0;
                                            $string = preg_replace('/\*.*$/', '', $esi_remarks);

                                            if ($esi_salary > $val['0']['SALARY']) {
                                                $esi_salary = $val['0']['SALARY'];
                                            }
                                            $salary = round($val['0']['SALARY'] - $esi_salary);
                                            $pf_salary += round($salary);
                                            $esi_sal += round($esi_salary);

                                            // Edited by Akshay on 13-6-2025
                                            $esi1 = ceil($esi_salary * .0075);
                                            $esi = $esi_salary * .0325;
                                            $total = $esi1 + $esi;

                                            $split1 += $esi1; // Edited by Akshay on 13-6-2025
                                            $split2 += ($esi_salary * .0325);
                                            $split3 += $total;
                                            // End

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $name);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $ip);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $join_date);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $branch);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $dep);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $deg);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '');


                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $days);
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, round($sal));
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, round($esi_salary)); //esi sal
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, round($salary)); //exsal
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, round($esi1));
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, ($esi)); // Edited by Akshay on 13-6-2025
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, ($total));

                                            // Edited by Akshay on 3-1-2026
                                            // Now apply yellow background if $hightlight is set and true
                                            if (isset($hightlight) && $hightlight === true) {
                                                $objPHPExcel->getActiveSheet()->getStyle(
                                                    PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount . ':' . PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount
                                                )->getFill()->applyFromArray([
                                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                                    'startcolor' => [
                                                        'rgb' => 'FFFF00', // Yellow
                                                    ],
                                                ]);
                                            }
                                            // End

                                            $rowcount++;
                                            $j++;
                                        }
                                    }

                                    // Edtited by Akshay on 23-3-2026
                                    // Sl No column (A column example)
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('A5:A' . ($rowcount - 1))
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                                    // Rest columns
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('B5:I' . ($rowcount - 1))
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle('J5:P' . ($rowcount - 1))
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                    // End
                                }
                            } // Edited by Akshay on 30-6-2025


                            foreach (range('A', 'O') as $columnID) {
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                            }

                            foreach (range('A1', 'O') as $columnID) {

                                $BStyle = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                );
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('C5:C400')
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('D5:D500')
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $row = $rowcount - 1;
                                $objPHPExcel->getActiveSheet()->getStyle('A1:P' . $row)->applyFromArray($BStyle);
                            }
                        }

                        // Edited by Akshay on 2-1-2025
                        if (!empty($arr_termination))
                            foreach ($arr_termination as $branchVal)
                                foreach ($branchVal as $val) {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val['EMPID']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $val['IPNAME']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $val['IPNO']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $val['joining_date']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $val['branch']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $val['dept']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $val['desg']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $val['last_approved_working_date']);


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 0);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 0);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, 0); //esi sal
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, 0); //exsal
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, 0);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, 0); // Edited by Akshay on 13-6-2025
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, 0);
                                    $rowcount++;
                                    $j++;
                                }
                        // End

                        // Edtited by Akshay on 23-3-2026
                        // Sl No column (A column example)
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A5:A' . ($rowcount - 1))
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        // Rest columns
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('B5:I' . ($rowcount - 1))
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                        $objPHPExcel->getActiveSheet()
                            ->getStyle('J5:P' . ($rowcount - 1))
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        // End

                    } else {
                        foreach ($arr_salary_for_template as $value) {
                            // End
                            $arr_daata = $value['summary'];
                            $employees = $value;
                            if (empty($arr_daata))      continue;

                            if (count($value) > 0) {


                                $arr_data = $value;

                                $arr_e = $employees['summary'];
                                $esi_salary = $employees['esi_salary']; // Edited by Akshay on 30-6-2025
                                $hightlight = isset($employees['hightlight']) ? $employees['hightlight'] : false; // Edited by Akshay on 3-1-2026
                                $col = 0;
                                foreach ($arr_e as $employee => $val) {
                                    if ($val['0']['Esi'] > 0) {
                                        $name = ucwords(strtolower($val['employee_info']['EmpName'])); // Edited by Akshay on 23-3-2026
                                        $id = $val['employee_info']['employee_id'];
                                        $ip = $val['emp_details']['esi'];
                                        $join_date = $val['employee_info']['joining_date'];
                                        $dep = ucwords(strtolower($val['employee_info']['department'])); // Edited by Akshay on 23-3-2026
                                        $deg = ucwords(strtolower($val['employee_info']['designation'])); // Edited by Akshay on 23-3-2026
                                        $branch = ucwords(strtolower($val['employee_info']['branch'])); // Edited by Akshay on 23-3-2026
                                        $termination_date = isset($val['termination']['last_approved_working_date']) ? $val['termination']['last_approved_working_date'] : ''; // Edited by Akshay on 13-6-2025

                                        $sal = ($val['0']['SALARY'] != '0') ? round($val['0']['SALARY']) : 0;
                                        $gross += round($val['0']['SALARY']);

                                        // Edited by Akshay on 1-1-2026
                                        $na_count = isset($val[0]['na_count']) ? $val[0]['na_count'] : 0;
                                        $lop_total = isset($val[0]['lop_total']) ? $val[0]['lop_total'] : 0;
                                        $days = ceil(max(0, ($total_days_in_month - ($lop_total))));
                                        // End
                                        $day += $days;
                                        $esi_remarks = isset($val['0']['ESI_REMARKS']) ? $val['0']['ESI_REMARKS'] : 0;
                                        $string = preg_replace('/\*.*$/', '', $esi_remarks);

                                        if ($esi_salary > $val['0']['SALARY']) {
                                            $esi_salary = $val['0']['SALARY'];
                                        }
                                        $salary = round($val['0']['SALARY'] - $esi_salary);
                                        $pf_salary += round($salary);
                                        $esi_sal += round($esi_salary);

                                        // Edited by Akshay on 13-6-2025
                                        $esi1 = ceil($esi_salary * .0075);
                                        $esi = $esi_salary * .0325;
                                        $total = $esi1 + $esi;

                                        $split1 += $esi1; // Edited by Akshay on 13-6-2025
                                        $split2 += ($esi_salary * .0325);
                                        $split3 += $total;
                                        // End

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $name);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $ip);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $join_date);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $dep);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $deg);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '');


                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $days);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, round($sal));
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, round($esi_salary)); //esi sal
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, round($salary)); //exsal
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, round($esi1));
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, ($esi)); // Edited by Akshay on 13-6-2025
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, ($total));

                                        // Edited by Akshay on 3-1-2026
                                        // Now apply yellow background if $hightlight is set and true
                                        if (isset($hightlight) && $hightlight === true) {
                                            $objPHPExcel->getActiveSheet()->getStyle(
                                                PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount . ':' . PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount
                                            )->getFill()->applyFromArray([
                                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                                'startcolor' => [
                                                    'rgb' => 'FFFF00', // Yellow
                                                ],
                                            ]);
                                        }
                                        // End

                                        $rowcount++;
                                        $j++;
                                    }
                                }

                                // Edtited by Akshay on 23-3-2026
                                // Sl No column (A column example)
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('A5:A' . ($rowcount - 1))
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                                // Rest columns
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('B5:I' . ($rowcount - 1))
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('J5:P' . ($rowcount - 1))
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                // End
                            }



                            foreach (range('A', 'O') as $columnID) {
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                            }

                            foreach (range('A1', 'O') as $columnID) {

                                $BStyle = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                );
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('C5:C400')
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('D5:D500')
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $row = $rowcount - 1;
                                $objPHPExcel->getActiveSheet()->getStyle('A1:P' . $row)->applyFromArray($BStyle);
                            }
                        }

                        // Edited by Akshay on 2-1-2025
                        if (!empty($arr_termination))
                            foreach ($arr_termination as $val) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val['EMPID']);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $val['IPNAME']);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $val['IPNO']);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $val['joining_date']);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $val['branch']);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $val['dept']);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $val['desg']);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $val['last_approved_working_date']);


                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 0);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 0);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, 0); //esi sal
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, 0); //exsal
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, 0);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, 0); // Edited by Akshay on 13-6-2025
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, 0);
                                $rowcount++;
                                $j++;
                            }
                        // Edtited by Akshay on 23-3-2026
                        // Sl No column (A column example)
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A5:A' . ($rowcount - 1))
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        // Rest columns
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('B5:I' . ($rowcount - 1))
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                        $objPHPExcel->getActiveSheet()
                            ->getStyle('J5:P' . ($rowcount - 1))
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        // End
                        // End
                    }
                    // exit;
                    // End
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Grand Total");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->getStyleByColumnAndRow(0, $rowcount)->applyFromArray($lightGreyFill); // Edited by Akshay on 13-6-2025

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $day);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, round($gross));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)
                        ->getNumberFormat()->setFormatCode('#,##0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, round($esi_sal));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)
                        ->getNumberFormat()->setFormatCode('#,##0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, round($pf_salary));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)
                        ->getNumberFormat()->setFormatCode('#,##0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, round($split1));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)
                        ->getNumberFormat()->setFormatCode('#,##0');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, ceil($split2)); // Edited by Akshay on 8-8-2025
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)
                        ->getNumberFormat()->setFormatCode('#,##0');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, ceil($split3)); // Edited by Akshay on 8-8-2025
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)
                        ->getNumberFormat()->setFormatCode('#,##0');

                    // Edited by Akshay on 13-6-2025
                    $startCol = PHPExcel_Cell::stringFromColumnIndex(8);
                    $endCol   = PHPExcel_Cell::stringFromColumnIndex(15);

                    $objPHPExcel->getActiveSheet()
                        ->getStyle($startCol . $rowcount . ':' . $endCol . $rowcount)
                        ->applyFromArray($lightGreyFill);

                    $objPHPExcel->getActiveSheet()
                        ->getStyle($startCol . $rowcount . ':' . $endCol . $rowcount)
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    // End  

                    $rowcount++;
                } else {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records found under this Criteria');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':O' . $rowcount . '');
                    $worksheet->getStyle('A' . $rowcount . '')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                }
                $BStyle = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $objPHPExcel->getActiveSheet()->setShowGridlines(false);

                $row = $rowcount - 1;
                $objPHPExcel->getActiveSheet()->getStyle('A1:P' . $row)->applyFromArray($BStyle);
                $objPHPExcel->getActiveSheet()->setTitle('ESI Report');
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */
                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);
                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            case 'print':
                //   echo "entered in";
                $this->set('mode', 'print');
                $this->render('empesi');
                break;
            default:
                $this->set('mode', '');
                $this->render('empesi');
                break;
        }
    }



    private function generateESINEWlabourreport($mode)
    {

        $arr_form_data = $_REQUEST;

        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');



        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc

                                                    where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc

                                                    ORDER BY emp_salary_slip_pkey ");

        $array_key = array();


        foreach ($arr_keys as $val) {



            if ($val['ectc']['head_operator'] == 'Addition') {

                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {

                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        //debug($array_key);

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));

        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('M', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);

        $this->set('mname1', $mname);
        $this->set('y1', $year);
        $this->set('month2', $month);
        $this->set('month1', $month1);

        //added by megha on 24_07_19

        // $this->set('from', $from);

        // $this->set('otdate', $otdate);

        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {

            echo "Choose Criteria ";

            return false;
        }



        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {

            echo "Choose Criteria ";

            return false;
        }



        $conditions = array();

        $conditions[] = 'and ectc.month_year="' . $from . '"';

        $arr_leavepolicygroupids = array();

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {

            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';

            $needBranchWiseReport = false;
            $needdepartmentwiseReport = false;
            $needdesignationwise = false;
            $needgenderwise = false;


            if ($str_criteria_item == 'Units') {

                $needBranchWiseReport = true;
            }

            $this->set('needBranchWiseReport', $needBranchWiseReport);
            if ($str_criteria_item == 'Gender') {

                $needgenderwise = true;
            }

            $this->set('needgenderwise', $needgenderwise);


            if ($str_criteria_item == 'Departments') {
                $needdepartmentwiseReport = true;
            }


            $this->set('needdepartmentwiseReport', $needdepartmentwiseReport);

            if ($str_criteria_item == 'Designation') {
                $needdesignationwise = true;
            }


            $this->set('needdesignationwise', $needdesignationwise);


            $this->set('str_criteria_item', $str_criteria_item);

            if ($str_criteria_item == '') {

                echo "<h1>No Criteria Selected</h1>";

                die();
            }



            if (!isset($arr_form_data[$str_criteria_item])) {

                echo "<h1>No Criteria Selected</h1>";

                die();
            }
        }



        $arr_leavepolicydetails_for_template = array();

        $arr_salary_for_template = array();

        $arr_gross = array();

        $id = implode(' AND ', $conditions);

        // if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))

        $k = 0;

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {

            $resign_condition = " and emp_details.status = '1' ";
        }



        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {

            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {



                if ($arr_form_data['select-criteria1'] == 'Units') {




                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,


(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,



abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info

left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and emp_details.branch_code = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } elseif ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info

left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and emp_details.emp_pkey = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } elseif ($arr_form_data['select-criteria1'] == 'Departments') {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info

left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)

 left join department on (department.dept_code = emp_proff.emp_dept)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and department.dept_code = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } elseif ($arr_form_data['select-criteria1'] == 'Designation') {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info

left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)

 left join designation on (designation.desig_code = emp_proff.designation)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and designation.desig_code = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } else {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info

left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)

 left join department on (department.dept_code = emp_proff.emp_dept)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and emp_details.classification = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                }

                //($arr_gross);exit();

                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = array(
                        'summary' => $arr_gross,
                    );
                    $k++;
                }
            }
        }



        $this->set('keys', $arr_keys);

        $this->set('array_key', $array_key);

        //debug($gross);

        $this->set('arr_salary_for_template', $arr_salary_for_template);

        //debug($arr_salary_for_template); die();

        $cr = $arr_form_data['select-criteria1'];

        $this->set('cr', $cr);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $user_name = $this->Session->read('user_name');

        $this->set('user_name', $user_name);

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $this->set('month', $from);

        $cname = $arr_comp_contact_info['CompanyContactInfo']['business_name'];



        $arr_compliance = $this->EmpCtcTransaction->query("select emp_state_ins_no,pf_no,service_tax from compliance");

        // debug($arr_compliance);exit();

        $eip = $arr_compliance['0']['compliance']['emp_state_ins_no'];



        $this->set('eip', $eip);

        //Set informations needed for report



        switch ($mode) {

            case 'pdf':

                //   echo "entered in";

                $this->set('mode', 'pdf');

                $view = new View($this, false);

                $view_output = $view->render('empesinew');

                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));



                $html2pdf = new HTML2PDF('L', 'A4', 'en');

                $html2pdf->pdf->SetDisplayMode('fullpage');

                $view_output = '<style>table { width: 100%; }</style>' . $view_output;

                $html2pdf->writeHTML($view_output);

                //Edited by Akshay on 14/7/2023
                // Get the total number of pages in the PDF
                $totalPages = $html2pdf->pdf->getPage();

                // Add the footer to each page
                for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
                    $html2pdf->pdf->setPage($pageNumber);

                    // Set the font and font size for the footer
                    $html2pdf->pdf->SetFont('helvetica', '', 10);

                    // Set the position for the line
                    $footerX = 15;
                    $footerY = $html2pdf->pdf->getPageHeight() - 31;
                    $footerWidth = $html2pdf->pdf->getPageWidth() - 30;

                    $html2pdf->pdf->SetXY($footerX, $footerY + 10);
                    $html2pdf->pdf->Cell($footerWidth, 0, '', 'B', 0, 'C');


                    // Set the position for the page number
                    $html2pdf->pdf->SetXY($footerX, $footerY + 12);
                    $html2pdf->pdf->Cell($footerWidth, 10, $pageNumber, 0, 0, 'R');
                }

                //--------------------------------
                $str_company_code = $this->Session->read('company_code');

                //$html2pdf->Output('_ESI SUMMARY_REPORT.pdf', 'D');
                $html2pdf->Output($str_company_code . '_ESI SUMMARY_REPORT.pdf', 'D');






                //$this->render('empepf');

                break;

            case 'excel':

                $str_company_code = $this->Session->read('company_code');

                //$file_name = isset($str_company_code) ? $str_company_code . "ESIReport.xlsx" : "ESIReport" . strtotime() . ".xlsx";
                //$str_company_code = 'KWML';

                $file_name = isset($str_company_code) ? $str_company_code . "_ESI SUMMARY_REPORT.xlsx" : "PFSummary" . strtotime() . ".xlsx";


                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");

                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");

                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                if (empty($arr_salary_for_template)) {


                    $worksheet = $objPHPExcel->getActiveSheet();
                    $worksheet->mergeCells('A1:S1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, 1, $cname . " ESI Summary report for the month of " . $mname .  " / "  . $year);


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(15);


                    $rowcount = 2;
                    //print nodata
                    $worksheet->setCellValueByColumnAndRow(0, 2, "No data available under the selected criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:G2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $rowcount++;

                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                } else {

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, $cname . " ESI Summary report for the month of " . $mname .  " / "  . $year);


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(13);

                    $worksheet->mergeCells('A1:G1');

                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                    );
                    $rowcount = 2;
                    $worksheet->mergeCells('A2:G2');

                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)

                    );
                    $worksheet->setCellValueByColumnAndRow(0, 2, "Employer ESI No.:" . $eip);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);

                    $rowcount = 3;

                    $i = 0;

                    $col = 0;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Sl. No.');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'ESI No.');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'Name of Member');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Days Worked');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'ESI Earnings');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'ESI Contribution');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'Employer Contribution');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                    $rowcount = 4;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '(1)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '(2)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '(3)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '(4)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '(5)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '(6)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '(7)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);


                    $rowcount++;

                    $gross_tot = 0;

                    $pf_salary = 0;

                    $emp_ep = 0;

                    $epr_ep = 0;

                    $esi_t = 0;

                    $ip = 0;

                    $days = 0;

                    $day = 0;

                    $total = 0;

                    $total1 = 0;

                    $split1 = 0;

                    $split2 = 0;

                    $split3 = 0;

                    $esi_sal = 0;

                    $gross = 0;

                    //edited by megha on 10/08/2019 replace $j to outside loop.. serial no. duplication(1)

                    $j = 1;

                    foreach ($arr_salary_for_template as $value) {

                        $arr_daata = $value['summary'];

                        $employees = $value;

                        if (empty($arr_daata))      continue;



                        if (count($value) > 0) {

                            //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  WWF  ');

                            //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);



                            $arr_data = $value;



                            $arr_e = $employees['summary'];

                            $col = 0;

                            foreach ($arr_e as $employee => $val) {

                                if ($val['0']['Esi'] > 0) {

                                    $name = $val['employee_info']['EmpName'];
                                    $eno = $val['emp_details']['esi'];

                                    $esi1 = round($val['0']['Esi']);
                                    $esi = round($val['0']['EMPLOYER_ESI']);
                                    //$ec=floatval($val[0]['EMPLOYER_ESI']);
                                    //debug($ec);exit();
                                    $employer_esi = isset($val['0']['EMPLOYER_ESI']) ? $val['0']['EMPLOYER_ESI'] : '';
                                    eval('$esi = ' . $employer_esi . ';');
                                    $esi = round($esi, 1);
                                    $total = round($esi1 + $esi);
                                    $sal = ($val['0']['SALARY'] != '0') ? round($val['0']['SALARY']) : 0;
                                    $gross += round($val['0']['SALARY']);
                                    $days = $val['0']['present'] + $val['0']['leaves'];
                                    $split1 += round($val['0']['Esi']);

                                    $split3 += $esi + round($val['0']['Esi']);
                                    $day += $days;
                                    $excluded = ($val['0']['Esi'] + $esi) / .04;
                                    if ($excluded > $val['0']['SALARY']) {
                                        $excluded = $val['0']['SALARY'];
                                    }
                                    $salary = round($val['0']['SALARY'] - $excluded);
                                    $pf_salary += round($salary);
                                    // $esi_sal += round($excluded);
                                    $excluded1 = isset($val['0']['ESI_EARNING']) ? $val['0']['ESI_EARNING'] : '';
                                    $expressionWithoutPortion = str_replace(['* .0075', '* .75 / 100'], '', $excluded1);
                                    eval('$esiearning = ' . $expressionWithoutPortion . ';');
                                    $esiearning = round($esiearning);
                                    $esi_sal += $esiearning;
                                    $esi = round($esiearning * 0.0325, 2);
                                    $split2 += $esi;



                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $eno);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 1, $rowcount)->setValueExplicit($eno, PHPExcel_Cell_DataType::TYPE_STRING);


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                    $worksheet->getStyleByColumnAndRow(($col + 3), $rowcount)->getNumberFormat()->setFormatCode('0.00');
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $days);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                                    $worksheet->getStyleByColumnAndRow(($col + 4), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $esiearning);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                    //$worksheet->getStyleByColumnAndRow(($col+5), $rowcount)->getNumberFormat()->setFormatCode('0.00');
                                    $worksheet->getStyleByColumnAndRow(($col + 5), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $esi1);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                                    //$worksheet->getStyleByColumnAndRow(($col+6), $rowcount)->getNumberFormat()->setFormatCode('0.0');

                                    //$formatted_esi = sprintf("%.2f", $esi);
                                    $worksheet->getStyleByColumnAndRow(($col + 6), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $esi);

                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                                    $rowcount++;

                                    $j++;
                                }
                            }
                        }



                        foreach (range('A', 'O') as $columnID) {

                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                        }
                    }

                    $BStyle = array(

                        'borders' => array(

                            'allborders' => array(

                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )

                        )

                    );

                    $row = $rowcount - 1;

                    $objPHPExcel->getActiveSheet()->getStyle('A1:G' . $row)->applyFromArray($BStyle);
                    $worksheet->mergeCells('A' . $rowcount . ':B' . $rowcount);
                    $worksheet->setCellValueByColumnAndRow(2, $rowcount, "T O T A L");

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);


                    $worksheet->mergeCells('C' . $rowcount . ':D' . $rowcount);

                    $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                    );


                    $objPHPExcel->getActiveSheet()
                        ->getStyle(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount)
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    //$worksheet->getStyleByColumnAndRow(4, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    $worksheet->getStyleByColumnAndRow(4, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $esi_sal);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                    $objPHPExcel->getActiveSheet()
                        ->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    //$worksheet->getStyleByColumnAndRow(5, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    $worksheet->getStyleByColumnAndRow(5, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $split1);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                    //$worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    $worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');
                    //edited by sinsiya on 11-06-2025
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $split2);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6, $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                        ),
                    );

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':G' . $rowcount)->applyFromArray($styleArray);
                    $rowcount = $rowcount + 2;
                    //$worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                    $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Employee Contribution");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    //$worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    $worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $worksheet->setCellValueByColumnAndRow(3, $rowcount, $split1);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    );

                    $rowcount = $rowcount + 1;
                    // $worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                    $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Employer Contribution");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    //$worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    $worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $worksheet->setCellValueByColumnAndRow(3, $rowcount, ceil($split2));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    );
                    $styleArray = array(
                        'borders' => array(
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                        ),
                    );

                    $objPHPExcel->getActiveSheet()->getStyle('C' . $rowcount . ':D' . $rowcount)->applyFromArray($styleArray);
                    $rowcount = $rowcount + 1;
                    //$worksheet->mergeCells('A'.$rowcount.':B'.$rowcount);
                    $worksheet->setCellValueByColumnAndRow(2, $rowcount, "T O T A L");

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    $t = $split1 + ceil($split2);
                    $worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $worksheet->setCellValueByColumnAndRow(3, $rowcount, $t);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    );

                    $objPHPExcel->getActiveSheet()->getStyle('C' . $rowcount . ':D' . $rowcount)->applyFromArray($styleArray);





                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A3:G4')
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }


                $objPHPExcel->getActiveSheet()->setTitle('ESI Summary');

                /* header footer */

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                /* header footer */

                /* print Set up */

                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed

                header('Content-Type: application/vnd.ms-excel; charset=utf-8');

                header('Content-Disposition: attachment; filename=' . $file_name);



                readfile(dirname(__FILE__) . "/" . $file_name);

                unlink(dirname(__FILE__) . "/" . $file_name);

                break;

            case 'print':

                //   echo "entered in";

                $this->set('mode', 'print');

                $this->render('empesinew');

                break;

            default:

                $this->set('mode', '');

                $this->render('empesinew');

                break;
        }
    }

    private function generateEPFlabourreport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc
                                                                        where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc
                                                                        ORDER BY emp_salary_slip_pkey ");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));

        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }

        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $conditions = array();
        $conditions[] = 'and ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {
            $resign_condition = " and emp_details.status = '1' ";
        }

        // Edited by Akshay on 29-7-2025
        $arr_epf_fkey = $this->EmpCtcTransaction->query("SELECT salary_head_item_fkey FROM tax_salary_components WHERE LCASE(tax_salary_components_name) = 'employee epf' AND status = 1");
        $epf_fkey = $arr_epf_fkey[0]['tax_salary_components']['salary_head_item_fkey'];

        $arr_epf_fkey = $this->EmpCtcTransaction->query("SELECT salary_head_item_fkey FROM tax_salary_components WHERE LCASE(tax_salary_components_name) = 'employer epf' AND status = 1");
        $employer_epf_fkey = $arr_epf_fkey[0]['tax_salary_components']['salary_head_item_fkey'];

        $columns = $this->EmpCtcTransaction->getDataSource()->query("
                                                                        SHOW COLUMNS FROM emp_salary_slip LIKE 'combined_base_value'
                                                                    ");

        $hasRemarks2 = !empty($columns); // true if combined_base_value exists
        // End

        // Edited by Akshay on 11-12-2025
        $checkColumn = $this->EmpCtcTransaction->query("
                                                                    SELECT COUNT(*) cnt
                                                                    FROM information_schema.COLUMNS
                                                                    WHERE TABLE_SCHEMA = DATABASE()
                                                                    AND TABLE_NAME = 'attendance_register'
                                                                    AND COLUMN_NAME = 'lop_only'
                                                                ");
        $lopField = 'ar.lop_total'; // default

        if ($checkColumn[0][0]['cnt'] > 0) {
            $lopField = "CASE 
                                        WHEN ar.month_year > '2026-01' 
                                        THEN ar.lop_only 
                                        ELSE ar.lop_total 
                                    END";
        }

        $ncp_days_query = " (
                                            SELECT $lopField
                                            FROM attendance_register ar
                                            WHERE ar.emp_fkey = employee_info.emp_pkey
                                            AND ar.month_year = '$from'
                                        ) AS NCP_days ";

        // End

        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

            try {

                // Edited by Akshay on 29-7-2025

                $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.pf,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
                                        AND ectc.end_date_effective is null $id and ectc.head_type != 'Arrear' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,
                                        abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
                                        AND ectc.end_date_effective is null $id and ectc.head_type != 'Arrear' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
                                        abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
                                        AND ectc.end_date_effective is null $id and ectc.head_type != 'Arrear' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
                                        abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
                                        AND ectc.end_date_effective is null $id and ectc.head_type != 'Arrear' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
                                        abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
                                        AND ectc.end_date_effective is null $id and ectc.head_type != 'Arrear' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
                                        abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
                                        AND ectc.end_date_effective is null $id and ectc.head_type != 'Arrear' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
                                        $ncp_days_query,
                                        abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
                                        AND ectc.end_date_effective is null and ectc.head_type != 'Arrear' and head_operator = 'Addition' $id ),0)) SALARY, payroll_master.eps, payroll_master.calander_days
                                        


                                        from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 
                                        LEFT JOIN payroll_master ON payroll_master.emp_fkey = employee_info.emp_pkey AND payroll_master.month_year = '$from' AND payroll_master.action in ('Approved', 'Processed')
                                        where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' and end_date_effective is null)
                                        and emp_details.branch_Code = '$leavepolicygroupid' $resign_condition ");
                // End
            } catch (Exception $e) {
                debug($e);
            }


            // Edited by Akshay on 29-7-2025
            foreach ($arr_gross as &$item) {
                if (isset($item[0]) && is_array($item[0])) {
                    $epf_salary = 0;
                    $emp_id = isset($item['employee_info']['emp_pkey']) ? $item['employee_info']['emp_pkey'] : '';
                    if ($emp_id != '') {
                        // 1. Get ESI formula for this employee

                        if ($hasRemarks2) {
                            $formula_result = $this->EmpCtcTransaction->query("
                                                                            SELECT combined_base_value, head_type, remarks_2, structure_det_value -- Edited by Akshay on 5-11-2025
                                                                            FROM emp_salary_slip 
                                                                            WHERE salary_head_item_fkey = $epf_fkey 
                                                                            AND emp_fkey = $emp_id
                                                                            AND month_year = '$from'
                                                                            AND end_date_effective IS NULL
                                                                        ");
                            $epf_salary = isset($formula_result[0]['emp_salary_slip']['combined_base_value']) ? $formula_result[0]['emp_salary_slip']['combined_base_value'] : 0;

                            // Edited by Akshay on 5-11-2025
                            $head_type = isset($formula_result[0]['emp_salary_slip']['head_type']) ? trim($formula_result[0]['emp_salary_slip']['head_type']) : '';
                            if ($head_type == 'limit_wl') {
                                $upper_lt = 15000;
                                $epf_salary = min($upper_lt, $epf_salary);
                            }
                            // End

                            // Employer epf
                            $formula_result = $this->EmpCtcTransaction->query("
                                                                            SELECT combined_base_value, head_type, remarks_2, structure_det_value -- Edited by Akshay on 5-11-2025
                                                                            FROM emp_salary_slip 
                                                                            WHERE salary_head_item_fkey = $employer_epf_fkey 
                                                                            AND emp_fkey = $emp_id
                                                                            AND month_year = '$from'
                                                                            AND end_date_effective IS NULL
                                                                        ");
                            $employer_epf_salary = isset($formula_result[0]['emp_salary_slip']['combined_base_value']) ? $formula_result[0]['emp_salary_slip']['combined_base_value'] : 0;
                            // Edited by Akshay on 5-11-2025
                            $head_type = isset($formula_result[0]['emp_salary_slip']['head_type']) ? trim($formula_result[0]['emp_salary_slip']['head_type']) : '';
                            if ($head_type == 'limit_wl') {
                                $upper_lt = 15000;
                                $employer_epf_salary = min($upper_lt, $employer_epf_salary);
                            }
                            // End
                        } else {
                            $formula_result = $this->EmpCtcTransaction->query("
                                                                            SELECT remarks 
                                                                            FROM emp_salary_slip 
                                                                            WHERE salary_head_item_fkey = $epf_fkey 
                                                                            AND emp_fkey = $emp_id
                                                                            AND month_year = '$from'
                                                                            AND end_date_effective IS NULL
                                                                        ");
                            $formula_string = '';

                            // 2. Parse and calculate EPF salary
                            if (!empty($formula_result)) {
                                $row = $formula_result[0]['emp_salary_slip'];

                                if (!empty($row['remarks_2'])) {
                                    $formula_string = $row['remarks_2'];
                                } elseif (!empty($row['remarks'])) {
                                    $formula_string = $row['remarks'];
                                }
                            }


                            // 2. Parse and calculate ESI salary
                            $epf_salary = 0;
                            if (!empty($formula_string)) {
                                $parts = explode('*', $formula_string);
                                $sum_part = trim($parts[0]); // (15000 + 2000 + 3000 )

                                // Step 2: Remove all spaces
                                $sum_part = str_replace(' ', '', $sum_part); // (15000+2000+3000)

                                // Step 3: Evaluate the expression
                                if (preg_match('/[a-zA-Z]/', $sum_part)) {
                                    if ($from >= '2020-05' && $from <= '2020-07') {
                                        $epf_salary = round($item['0']['EPF'] * 100 / 10, 2);
                                    } else {
                                        $epf_salary = round($item['0']['EPF'] * 100 / 12, 2);
                                    }
                                } else {
                                    eval('$epf_salary = ' . $sum_part . ';');
                                    if (!is_numeric($epf_salary)) {
                                        $epf_salary = 0;
                                    }
                                }
                            }

                            // Employer epf
                            $formula_result = $this->EmpCtcTransaction->query("
                                                                            SELECT remarks 
                                                                            FROM emp_salary_slip 
                                                                            WHERE salary_head_item_fkey = $employer_epf_fkey 
                                                                            AND emp_fkey = $emp_id
                                                                            AND month_year = '$from'
                                                                            AND end_date_effective IS NULL
                                                                        ");
                            $formula_string = '';

                            // 2. Parse and calculate EPF salary
                            if (!empty($formula_result)) {
                                $row = $formula_result[0]['emp_salary_slip'];

                                if (!empty($row['remarks_2'])) {
                                    $formula_string = $row['remarks_2'];
                                } elseif (!empty($row['remarks'])) {
                                    $formula_string = $row['remarks'];
                                }
                            }


                            // 2. Parse and calculate ESI salary
                            $employer_epf_salary = 0;
                            if (!empty($formula_string)) {
                                $parts = explode('*', $formula_string);
                                $sum_part = trim($parts[0]); // (15000 + 2000 + 3000 )

                                // Step 2: Remove all spaces
                                $sum_part = str_replace(' ', '', $sum_part); // (15000+2000+3000)

                                // Step 3: Evaluate the expression
                                if (preg_match('/[a-zA-Z]/', $sum_part)) {
                                    if ($from >= '2020-05' && $from <= '2020-07') {
                                        $employer_epf_salary = round($item['0']['EMPLOYER_EPF'] * 100 / 10, 2);
                                    } else {
                                        $employer_epf_salary = round($item['0']['EMPLOYER_EPF'] * 100 / 12, 2);
                                    }
                                } else {
                                    eval('$employer_epf_salary = ' . $sum_part . ';');
                                    if (!is_numeric($employer_epf_salary)) {
                                        $employer_epf_salary = 0;
                                    }
                                }
                            }

                            // Edited by Akshay on 6-11-2025
                            $upper_lt = 15000;
                            $employer_epf_salary = min($upper_lt, $employer_epf_salary);
                            $epf_salary = min($upper_lt, $epf_salary);
                            // End
                        }
                    }
                    $item[0]['epf_salary'] = $epf_salary;
                    $item[0]['employer_epf_salary'] = $employer_epf_salary;
                }
            }
            unset($item); // break the reference
            // End

            $arr_salary_for_template[] = $arr_gross;
            $k++;
        }


        // Edited by Akshay on 24-3-2026
        $arr_compliance = $this->EmpCtcTransaction->query("select emp_state_ins_no,pf_no,service_tax from compliance");
        $eip = $arr_compliance['0']['compliance']['pf_no'];
        $this->set('eip', $eip);
        // End

        $this->set('keys', $arr_keys);
        $this->set('array_key', $array_key);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $cname = $arr_comp_contact_info['CompanyContactInfo']['business_name']; // Edited by Akshay on 26-3-2026
        $this->set('month', $from);
        //Set informations needed for report

        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('empepf');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('PFSummary.pdf', 'D');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "PFSummary.xlsx" : "PFSummary" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, $cname . " - EPF Report - Month - " . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'K'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                // Edited by Akshay on 28-3-2026
                $worksheet->mergeCells('M1:T1');
                $worksheet->mergeCells('M2:T2');
                // End

                // Edited by Akshay on 24-3-2026
                $worksheet->setCellValueByColumnAndRow(0, 2, "Employer EPF No.: " . $eip);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:L2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                // End

                $objPHPExcel->getActiveSheet()->setShowGridlines(false);

                $rowcount = 3;


                $i = 0;


                $total_gross = 0;
                $total_epf_salary = 0;
                $total_employee_epf_salary = 0;
                $total_employer_epf_salary = 0;
                $total_employer_epf = 0;
                $total_eps = 0;
                $total_employer_epf = 0;
                $total_employer_eps = 0;
                $total_employer_epf_contribution = 0;
                $total_employer_edli = 0;
                $total_employer_admin_charges = 0;
                $total_excluded = 0;


                foreach ($arr_salary_for_template as $value) {
                    if (count($value) > 0) {
                        $col = 0;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee Name   ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee ID  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Joining Date ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Branch  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Designation ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, ' UAN Number ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, ' Gross Salary ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, ' NCP Days ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, ' Employee EPF Salary ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, '  Employee EPF ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  Employer EPF Salary ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  Total Employer Contribution ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, '  Employer EPS/EDLI Salary ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 14, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, '  Employer EPF Contribution 3.67% ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 15, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount, '  Employer EPS Contribution 8.33%');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 16, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 17) . $rowcount, '  Employer EDLI Contribution 0.5%');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 17, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 18) . $rowcount, ' Employer Admin Charges 0.5% ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 18, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 19) . $rowcount, '  Excluded Salary  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 19, $rowcount)->getFont()->setBold(true);



                        $headerStyle = [
                            'font' => ['bold' => true],
                            'fill' => [
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => ['rgb' => 'D9D9D9']
                            ]
                        ];

                        for ($i = 0; $i <= 19; $i++) {
                            $objPHPExcel->getActiveSheet()
                                ->getStyleByColumnAndRow($col + $i, $rowcount)
                                ->applyFromArray($headerStyle);
                        }

                        $columns1 = range(0, 19); // Edited by Akshay on 24-3-2026

                        foreach ($columns1 as $c) {
                            $colLetter = PHPExcel_Cell::stringFromColumnIndex($col + $c);

                            // Set bold font
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + $c, $rowcount)->getFont()->setBold(true);

                            // Set fixed column width
                            $objPHPExcel->getActiveSheet()->getColumnDimension($colLetter)->setWidth(15);

                            // Enable text wrap
                            $objPHPExcel->getActiveSheet()->getStyle($colLetter . $rowcount)
                                ->getAlignment()->setWrapText(true);

                            // Set horizontal center alignment
                            $objPHPExcel->getActiveSheet()->getStyle($colLetter . $rowcount)
                                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        }



                        //edited by sinsiya on 12-06-2024 end changed the index number
                        $col = 9;

                        $rowcount = $rowcount + 1;

                        $arr_data = $value;



                        $j = 0;
                        foreach ($arr_data as $val) {
                            if ($val['0']['EPF'] == 0)
                                continue;

                            $objPHPExcel->getActiveSheet()
                                ->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)
                                ->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            for ($iCol = 1; $iCol <= 7; $iCol++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle(PHPExcel_Cell::stringFromColumnIndex($iCol) . $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            }

                            for ($iCol = 8; $iCol <= 19; $iCol++) {
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle(PHPExcel_Cell::stringFromColumnIndex($iCol) . $rowcount)
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            }

                            $col = 0;
                            $name = ucwords(strtolower($val['employee_info']['EmpName']));
                            $id = $val['employee_info']['employee_id'];
                            $join_date = $val['employee_info']['joining_date'];
                            $dep = ucwords(strtolower($val['employee_info']['department']));
                            $deg = ucwords(strtolower($val['employee_info']['designation']));
                            //edited by sinsiya on 12-06-2024 to add lop days
                            // Edited by Akshay on 4-10-2025
                            $calendar_days = isset($val['payroll_master']['calander_days']) ? ($val['payroll_master']['calander_days']) : 0;
                            $working_days = isset($val[0]['total_days']) ? ($val[0]['total_days']) : 0;

                            $ncp = isset($val[0]['NCP_days']) ? $val[0]['NCP_days'] : 0;
                            $ncp = max(0, $ncp);
                            // End
                            $branch = ucwords(strtolower($val['employee_info']['branch']));
                            $uan = $val['emp_details']['pf'];
                            $gross = round($val['0']['SALARY'], 2);
                            if ($from >= '2020-05' && $from <= '2020-07') {
                                $epf_salary = round($val['0']['EPF'] * 100 / 10);
                                $sal = round($val['0']['EPF'] * 100 / 10, 2);
                                $www_salary = round($sal * 10 / 100);
                                $employer_epf_salary = round($val['0']['EMPLOYER_EPF'] * 100 / 10);
                            } else {
                                $epf_salary = round($val['0']['epf_salary']);
                                $sal = round($val['0']['epf_salary'], 2);
                                $www_salary = round($sal * 12 / 100);
                                $employer_epf_salary = round($val['0']['employer_epf_salary']);
                            }
                            $employee_epf = $val['0']['EPF'];
                            $employer_epf = $val['0']['EMPLOYER_EPF'];
                            $epf = round($sal * 8.33 / 100);

                            $esia = round($sal * .5 / 100, 2);
                            $emp_epf_contr = round($employer_epf_salary * .12) - round($employer_epf_salary * .0833); // Edited by Akshay on 5-11-2025
                            $j = $j + 1;




                            $eps_status = isset($val['payroll_master']['eps']) ? $val['payroll_master']['eps'] : 'Y'; // Edited by Akshay on 23-7-2025

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $name);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $id);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $join_date);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $branch);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $dep);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $deg);

                            // Edited by Akshay on 18-7-2025
                            $objPHPExcel->getActiveSheet()->setCellValueExplicit(
                                PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount,
                                $uan,
                                PHPExcel_Cell_DataType::TYPE_STRING
                            );
                            // End


                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $gross);
                            $total_gross +=  $gross;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, floor($ncp));

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $epf_salary);
                            $total_epf_salary += $epf_salary;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $employee_epf); // EPF
                            $total_employee_epf_salary += $employee_epf;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, $employer_epf_salary);
                            $total_employer_epf_salary += $employer_epf_salary;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $employer_epf); // A
                            $total_employer_epf += $employer_epf;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, (min($employer_epf_salary, 15000))); // B
                            $total_eps += (min($employer_epf_salary, 15000));

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, $emp_epf_contr); // Edited by Akshay on 5-11-2025
                            $total_employer_epf_contribution += $emp_epf_contr; // Edited by Akshay on 5-11-2025

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount, round(min($employer_epf_salary, 15000) * .0833)); // Edited by Akshay on 5-11-2025
                            $total_employer_eps += round(min($employer_epf_salary, 15000) * .0833); // Edited by Akshay on 5-11-2025

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 17) . $rowcount, (min($employer_epf_salary, 15000) * .005));
                            $total_employer_edli +=  (min($employer_epf_salary, 15000) * .005);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 18) . $rowcount, ($employer_epf_salary * .005));
                            $total_employer_admin_charges +=  ($employer_epf_salary * .005);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 19) . $rowcount, ($gross - $employer_epf_salary));
                            $total_excluded +=  ($gross - $employer_epf_salary);

                            $rowcount++;
                        }



                        $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");
                        $worksheet->getStyleByColumnAndRow(0, $rowcount)
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);

                        $worksheet->getStyle('L' . $rowcount)->getAlignment()->applyFromArray(

                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                        );


                        //edited by sinsiya on 12-06-2024 changed the index value 
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, round($total_gross));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, round($total_epf_salary));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);

                        // Edited by Akshay on 28-7-2025
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, round($total_employee_epf_salary));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, round($total_employer_epf_salary));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, round($total_employer_epf));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                        // End


                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, round($total_eps));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, round($total_employer_epf_contribution));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, round($total_employer_eps));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, round($total_employer_edli));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, round($total_employer_admin_charges));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, round($total_excluded));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(19, $rowcount)->getFont()->setBold(true);
                        //edited by sinsiya on 12-06-2024 changing index value ended

                        // Edited by Akshay on 18-7-2025
                        $columnsToStyle = [8, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19];

                        foreach ($columnsToStyle as $colIndex) {
                            $objPHPExcel->getActiveSheet()
                                ->getStyleByColumnAndRow($colIndex, $rowcount)
                                ->applyFromArray([
                                    'font' => ['bold' => true],
                                    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
                                ]);
                        }
                        // End

                        $rowcount++;
                    }
                }

                // Edited by Akshay on 24-3-2026
                $lastRow = $objPHPExcel->getActiveSheet()->getHighestRow(); // e.g., 20

                if ($lastRow > 3) {
                    $objPHPExcel->getActiveSheet()->freezePane('D3');
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'), // Black
                            ),
                        ),
                    );

                    $lastColumnLetter = $objPHPExcel->getActiveSheet()->getHighestColumn();
                    $lastColumnIndex = PHPExcel_Cell::columnIndexFromString($lastColumnLetter); // 20

                    $range = 'A1:' . $lastColumnLetter . $lastRow; // Edited by Akshay on 26-3-2026

                    $objPHPExcel->getActiveSheet()->getStyle($range)->applyFromArray($styleArray);

                    $objPHPExcel->getActiveSheet()->getStyle($range)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); // Edited by Akshay on 26-3-2026

                    $objPHPExcel->getActiveSheet()
                        ->getStyle('I' . $lastRow . ':' . 'T' . $lastRow)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');

                    $headerStyle = [
                        'font' => ['bold' => true],
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'D9D9D9']
                        ]
                    ];

                    // Apply $headerStyle to each cell in the last row
                    for ($i = 0; $i < $lastColumnIndex; $i++) {
                        $objPHPExcel->getActiveSheet()
                            ->getStyleByColumnAndRow($i, $lastRow)
                            ->applyFromArray($headerStyle);
                    }
                } else {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . 3, 'No data available under the selected criteria');
                }

                // End                                  

                $objPHPExcel->getActiveSheet()->setTitle('PF Summary Report');
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */
                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);
                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            case 'print':
                //   echo "entered in";
                $this->set('mode', 'print');
                $this->render('empepf');
                break;
            default:
                $this->set('mode', '');
                $this->render('empepf');
                break;
        }
    }

    private function generatesummaryreport($mode)
    { {
            $arr_form_data = $_REQUEST;
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            $this->DbConfig->useDbConfig = $this->Session->read('ds');
            $company_code = $this->Session->read('company_code'); //company_code
            $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
            $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');
            $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
            $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
            $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
            $arr_date_in_selectedmonth = range(1, $att_enddate);
            if ($att_startdate != 1) {
                $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
            } else {
                $arr_date_in_prevmonth = array();
            }

            $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
            $this->set('arr_dates', $arr_dates);
            $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
            if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
                $report_month = $arr_form_data['reportfrom'];
                $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
                $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
            }
            $arr_leavepolicygroupids = array();
            $int_criterias_count = $arr_form_data['hidden-criterias-count'];
            for ($i = 1; $i <= $int_criterias_count; $i++) {
                $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

                $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            }

            $arr_leavepolicydetails_for_template = array();
            if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
                foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                    $fields = 'EditPunches.*,Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name';
                    $joins = array(
                        array(
                            'table' => 'branches',
                            'alias' => 'Branch',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array(
                                'EditPunches.branch_code = Branch.branch_code',
                                'Branch.status=1'
                            )
                        ),
                        array(
                            'table' => 'emp_details',
                            'alias' => 'EmployeeDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array(
                                'EditPunches.emp_id = EmployeeDetails.emp_id',
                                'EmployeeDetails.status=1'
                            )
                        )
                    );
                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and EditPunches.DEVICEID = 0 and EditPunches.LOGDATE between "' . $from . '" and "' . $to . '"';
                    $arr_leavepolicy_details = $this->EditPunches->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));

                    $arr_leavepolicydetails_for_template[] = array(
                        'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
                //      
                //              foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                //                  foreach($value['summary'] as $ky => $vaal){
                //            $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
                //            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
                //            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
                //
                //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
                //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
                //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
                //            }
                //        }
                // debug($resp_register);
                //   debug($arr_leavepolicydetails_for_template);  

                $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);

                //Set informations needed for report

                $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                $user_name = $this->Session->read('user_name');
                $this->set('user_name', $user_name);
                $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
                $this->set('arr_comp_contact_info', $arr_comp_contact_info);
                switch ($mode) {
                    case 'pdf':
                        //echo "entered in";die();
                        $this->set('mode', 'pdf');
                        $view = new View($this, false);
                        $view_output = $view->render('reportsummary');
                        //   debug($view_output);
                        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                        $html2pdf = new HTML2PDF('L', 'A2', 'en');
                        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->writeHTML($view_output);
                        $html2pdf->Output('reportsummary.pdf', 'D');
                        // $this->render('reportsummary');                
                        break;
                    case 'excel':

                        $str_company_code = $this->Session->read('company_code');
                        $file_name = isset($str_company_code) ? $str_company_code . "_attendane.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                        $objPHPExcel = new PHPExcel();

                        $objPHPExcel->getProperties()->setCreator("Administrator");
                        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                        $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                        $objPHPExcel->setActiveSheetIndex(0);

                        $worksheet = $objPHPExcel->getActiveSheet();

                        $worksheet->setCellValueByColumnAndRow(0, 1, "Summary Attendance Employees Report");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                        $worksheet->mergeCells('A1:F1');
                        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        for ($col = 'A'; $col !== 'Z'; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(true);
                        }

                        $rowcount = 2;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Id');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Present Days');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Holiday Days');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $columnindex = $columncount + 9;
                        foreach ($arr_dates as $key => $date) {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                            $columnindex++;
                        }
                        $rowcount = 3;
                        //$rowcount1=3;
                        foreach ($arr_leavepolicydetails_for_template as $value) {
                            $branch = isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';
                            //echo $branch;die();                     
                            //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');

                            $arr_data = $value['summary'];
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                            if (count($arr_data) >= 0) {
                                foreach ($arr_data as $key => $val) {
                                    $columnindex = 0;
                                    $name = $val['AttendanceRegister']['emp_name'];
                                    //$name=$name.$key;
                                    $present = $val['AttendanceRegister']['days_present'];
                                    $leave = $val['AttendanceRegister']['days_leave'];
                                    $holidays = $val['AttendanceRegister']['days_holidays'];
                                    $id = $val['Info']['employee_id'];
                                    $des = $val['Info']['designation'];
                                    $join = $val['Info']['joining_date'];
                                    $department = $val['Info']['department'];
                                    $branch = $val['Info']['branch'];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $des);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $present);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $leave);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $holidays);

                                    $columnindex = $columnindex + 9;
                                    foreach ($arr_dates as $key => $date) {
                                        $newIndex = 'FIELD' . ($key + 1);
                                        $dta = $val['AttendanceRegister'][$newIndex];
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                        $columnindex++;
                                    }

                                    $rowcount++;
                                }
                            }
                            $rowcount1 = $rowcount + 1;
                        }

                        $objPHPExcel->getActiveSheet()->setTitle('Attendance Policy');
                        /* header footer */
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                        /* header footer */

                        /* print Set up */
                        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                        /* print Set up */
                        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                        $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                        // output headers so that the file is downloaded rather than displayed
                        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                        header('Content-Disposition: attachment; filename=' . $file_name);

                        readfile(dirname(__FILE__) . "/" . $file_name);
                        unlink(dirname(__FILE__) . "/" . $file_name);
                        break;
                    default:
                        $this->set('mode', '');
                        $this->render('reportsummary');
                        break;
                }
            } else {
                echo "<div style='color:red'><h3>No record Found</h3></div>";
                // $this->layout=null;
            }
        }
    }
    private function generateprofessionaltaxreport($mode)
    {
        $arr_form_data = $_REQUEST;
        // debug($arr_form_data);
        $criteria = $arr_form_data['hidden-criteria1'];
        $year = $arr_form_data['year'];
        $from = $arr_form_data['report_from'];
        //$to = $arr_form_data['reportsto'];
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        //$fin_year_arr = $this->FinancialYear->find("first", array("fields" => array('start_month','end_month'), 
        // "conditions" => array('is_current_finyear' => 'Y', 'vattr1' => '1', 'Year_status' => 'OPEN', 'fin_year' => $year)));
        $fin_year_arr = $this->FinancialYear->find("first", array(
            "fields" => array('start_month', 'end_month'),
            "conditions" => array('vattr1' => '1', 'fin_year' => $year)
        ));
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }

        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $conditions = array();
        if ($arr_form_data['report_from'] == 1) {
            $startmonth = date("Y-m", strtotime($fin_year_arr['FinancialYear']['start_month']));
            $start_month = $startmonth;
            $months[] = $startmonth;
            for ($k = 1; $k < 6; $k++) {
                $months[] = date('Y-m', strtotime("+$k months", strtotime($startmonth)));
            }
            $conditions[] = " between '$start_month' and '$startmonth' ";
        } else {
            $endmonth = date("Y-m", strtotime($fin_year_arr['FinancialYear']['end_month']));

            for ($k = 5; $k > 0; $k--) {
                if ($k == 5) {
                    $start = date('Y-m', strtotime("-$k months", strtotime($endmonth)));
                }
                $months[] = date('Y-m', strtotime("-$k months", strtotime($endmonth)));
            }
            $months[] = $endmonth;
            $conditions[] = " between '$start' and '$endmonth' ";
        }
        $this->set('months', $months);
        $this->set('criteria', $criteria);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {
            $resign_condition = " and emp_details.status = '1' ";
        }
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $user = $this->Session->read('company_code'); // Edited by Akshay on 21-3-2025
        // $user = 'DEMO';
        $this->set('user', $user); // Edited by Akshay on 22-3-2025
        $arr_salary_for_template = array();
        // debug($arr_leavepolicygroupids);
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if ($criteria == 'EmployeeDetails') {
                $arr_gross = array();
                foreach ($months as $mon) {
                    $monFormatted = date('m-Y', strtotime($mon));
                    try {
                        if ($user == 'GLET' || $user == 'KWMT') {
                            $sql = "
                                SELECT 
                                    employee_info.*,
                                    emp_details.status,
                                    
                                    -- Calculate Professional Tax from emp_salary_slip
                                    ABS(
                                        IFNULL(
                                            (
                                                SELECT 
                                                    ectc.salary_amount
                                                FROM 
                                                    emp_salary_slip AS ectc
                                                WHERE 
                                                    ectc.emp_fkey = employee_info.emp_pkey
                                                    AND LCASE(ectc.item_part) = 'direct'
                                                    AND ectc.end_date_effective IS NULL
                                                    AND ectc.month_year = '$mon'
                                                    AND ectc.salary_head_item_fkey IN (
                                                        SELECT 
                                                            salary_head_item_fkey
                                                        FROM 
                                                            tax_salary_components
                                                        WHERE 
                                                            LCASE(tax_salary_components_name) = 'professional tax'
                                                            AND status = 1
                                                    )
                                            ),
                                            0
                                        )
                                    ) AS Professional_Tax,
                            
                                    -- Get Professional Tax from settle source
                                    IFNULL(
                                        (
                                            SELECT 
                                                ptv.amount
                                            FROM 
                                                professional_tax_view AS ptv
                                            WHERE 
                                                ptv.emp_fkey = employee_info.emp_pkey
                                                AND DATE_FORMAT(ptv.month_year, '%Y-%m') = '$mon'
                                                AND ptv.source = 'settle'
                                        ),
                                        0
                                    ) AS Settle_PT
                            
                                FROM 
                                    employee_info
                                LEFT JOIN 
                                    emp_details ON (emp_details.emp_pkey = employee_info.emp_pkey)
                            
                                WHERE 
                                    emp_details.emp_pkey = '$leavepolicygroupid'
                                    $resign_condition
                                    AND emp_details.emp_pkey IN (
                                    SELECT emp_fkey FROM payroll_master 
                                    WHERE
                                    month_year = '$mon'
                                    AND
                                    action IN ('Approved', 'Processed')
                                    OR action IS NULL
                                    )
                                
                                UNION
                                -- Query 2: Fetch from emp_variables_upload
            
                                SELECT 
                                    employee_info.*,
                                    emp_details.status,
                                    
                                    -- Calculate Professional Tax from emp_salary_slip
                                    ABS(
                                        IFNULL(
                                            (
                                                SELECT 
                                                    ectc.uploaded_amount
                                                FROM 
                                                    emp_variables_upload AS ectc
                                                WHERE 
                                                    ectc.emp_fkey = employee_info.emp_pkey
                                                    AND LCASE(ectc.item_part) = 'direct'
                                                    AND ectc.status = 1
                                                    AND ectc.month_year = '$monFormatted'
                                                    AND ectc.salary_head_item_fkey IN (
                                                        SELECT 
                                                            salary_head_item_fkey
                                                        FROM 
                                                            tax_salary_components
                                                        WHERE 
                                                            LCASE(tax_salary_components_name) = 'professional tax'
                                                            AND status = 1
                                                    )
                                            ),
                                            0
                                        )
                                    ) AS Professional_Tax,
                            
                                    -- Get Professional Tax from settle source
                                    IFNULL(
                                        (
                                            SELECT 
                                                ptv.amount
                                            FROM 
                                                professional_tax_view AS ptv
                                            WHERE 
                                                ptv.emp_fkey = employee_info.emp_pkey
                                                AND DATE_FORMAT(ptv.month_year, '%Y-%m') = '$mon'
                                                AND ptv.source = 'settle'
                                        ),
                                        0
                                    ) AS Settle_PT
                            
                                FROM 
                                    employee_info
                                LEFT JOIN 
                                    emp_details ON (emp_details.emp_pkey = employee_info.emp_pkey)
                            
                                WHERE 
                                    emp_details.emp_pkey = '$leavepolicygroupid'
                                    $resign_condition
                                    AND emp_details.emp_pkey NOT IN (
                                    SELECT emp_fkey FROM payroll_master 
                                    WHERE
                                    month_year = '$mon'
                                    AND
                                        (
                                            action IN ('Approved', 'Processed')
                                            OR action IS NULL
                                        )
                                    )
            
                            ";
                            try {
                                $arr = $this->EmpCtcTransaction->query($sql);
                            } catch (Exception $e) {
                                debug($e);
                                exit;
                            }
                        } else {
                            $arr = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.status,
                            abs(ifnull((select SUM(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
                            AND ectc.end_date_effective is null and ectc.month_year='$mon' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components 
                            where lcase(tax_salary_components_name)= 'professional tax' and status=1) and end_date_effective is null) ,0)) as Professional_Tax,
                            ifnull((select ptv.amount from professional_tax_view as ptv where ptv.emp_fkey = employee_info.emp_pkey  
                            and ptv.month_year='$mon' and ptv.source = 'settle') ,0) as Settle_PT 
                            from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
                            where emp_details.emp_pkey = '$leavepolicygroupid' $resign_condition ");
                        }
                        // debug($arr);
                    } catch (Exception $e) {
                        debug($e);
                    }
                    $arr['0']['month'] = $mon;

                    // Edited by Akshay on 26-3-2025
                    if ($user == 'GLET' || $user == 'KWMT') {
                        $professionalTaxSum = 0;
                        $settlePTSum = 0;
                        foreach ($arr as $key => $value) {
                            if (isset($value[0]['Professional_Tax'])) {
                                $professionalTaxSum += isset($value[0]['Professional_Tax']) ?  (int)$value[0]['Professional_Tax'] : 0;
                            }
                            if (isset($value[0]['Settle_PT'])) {
                                $settlePTSum += isset($value[0]['Settle_PT']) ? (int)$value[0]['Settle_PT'] : 0;
                            }
                        }

                        if (isset($arr[0][0]['Professional_Tax'])) {
                            $arr[0][0]['Professional_Tax'] = (string)$professionalTaxSum;
                        }

                        if (isset($arr[0][0]['Settle_PT'])) {
                            $arr[0][0]['Settle_PT'] = (string)$settlePTSum;
                        }
                    }
                    // End

                    if (!empty($arr)) {
                        $arr_gross[] = $arr;
                    }
                }
                // debug($arr_gross);
                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = $arr_gross;
                    $k++;
                }
                $arr_salary_for_templates = $arr_salary_for_template;
            } else {
                $arr_gross = array();
                foreach ($months as $mon) {
                    $arr = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.status,
            abs(ifnull((select SUM(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
            AND ectc.end_date_effective is null and ectc.month_year='$mon' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components 
            where lcase(tax_salary_components_name)= 'professional tax' and status=1) and end_date_effective is null) ,0)) as Professional_Tax,
            ifnull((select ptv.amount from professional_tax_view as ptv where ptv.emp_fkey = employee_info.emp_pkey  
            and ptv.month_year='$mon' and ptv.source = 'settle') ,0) as Settle_PT 
            from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
            where emp_details.branch_code = '$leavepolicygroupid' $resign_condition ");
                    $arr['month'] = $mon;
                    if (!empty($arr)) {
                        $arr_gross[] = $arr;
                    }
                }
                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = $arr_gross;
                    $k++;
                }
                $arr_salary_for_templates = $arr_salary_for_template;
            }
        }

        // debug($arr_salary_for_templates);exit;
        $this->set('arr_salary_for_template', $arr_salary_for_templates);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        //Set informations needed for report

        switch ($mode) {
            case 'pdf':
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('professionaltax');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('ProfessionalTaxReport.pdf', 'D');
                $this->render('professionaltax');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "ProfessionalTaxReport.xlsx" : "ProfessionalTaxReport" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Professional Tax Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Professional Tax Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'O'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 1;
                try {
                    if (count($arr_salary_for_template) !== 0) {
                        $rowcount++;
                        $col = 0;

                        $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('A' . $rowcount . ':G' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(7, $rowcount, "Professional Tax Summary Details");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('H' . $rowcount . ':N' . $rowcount);
                        $worksheet->getStyle('H' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee Name   ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee ID  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Joining Date ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Branch  ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Department ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Designation ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                        $i = 7;
                        foreach ($months as $month) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $i) . $rowcount, $month);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + $i, $rowcount)->getFont()->setBold(true);
                            $i++;
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $i) . $rowcount, '  Total Amount Paid ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + $i, $rowcount)->getFont()->setBold(true);

                        $rowcount = $rowcount + 1;
                        $i = 0;
                        $sum = 0;
                        foreach ($arr_salary_for_template as $values) {
                            $pt = 0;
                            // Edited by Akshay on 22-3-2025
                            if ($user == 'GLET' || $user == 'KWMT') {
                                $emp = $values['0']['0']['0']['emp_pkey'];
                            } else {
                                $emp = $values['0']['0']['employee_info']['emp_pkey'];
                            }

                            // End

                            foreach ($values as $value) {

                                // Edited by Akshay on 22-3-2025
                                if ($user == 'GLET' || $user == 'KWMT') {
                                    $condition = $value['0']['0']['emp_pkey'] == $emp;
                                } else {
                                    $condition = $value['0']['employee_info']['emp_pkey'] == $emp;
                                }

                                if ($condition) {
                                    // End
                                    $settle_pt = isset($value['0']['0']['Settle_PT']) ? $value['0']['0']['Settle_PT'] * -1 : 0;
                                    if ($settle_pt == 0) {
                                        $pro_tax = isset($value['0']['0']['Professional_Tax']) ? round($value['0']['0']['Professional_Tax']) : 0;
                                        $pt += isset($value['0']['0']['Professional_Tax']) ? round($value['0']['0']['Professional_Tax'], 2) : 0;
                                    } else {
                                        $pro_tax = round($settle_pt);
                                        $pt += round($settle_pt, 2);
                                    }
                                }
                            }
                            $sum += $pt;
                            if ($pt >= 0) {
                                $arr_data = $values;
                                if (count($arr_data) > 0) {
                                    $pt = 0;
                                    $i = $i + 1;
                                    // Edited by Akshay on 22-3-2025
                                    if ($user == 'GLET' || $user == 'KWMT') {
                                        $emp = $values['0']['0']['0']['emp_pkey'];
                                        $emp_name = $values['0']['0']['0']['EmpName'];
                                        // Edited by Akshay on 19-3-2025
                                        $status = (isset($values[0][0]['0']['status']) && $values[0][0]['0']['status'] == 1) ? '' : ' (Resigned)';
                                        $emp_name = TRIM($emp_name) . $status;
                                        // End
                                        $emp_id = $values['0']['0']['0']['employee_id'];
                                        $joining_date = $values['0']['0']['0']['joining_date'];
                                        $branch = $values['0']['0']['0']['branch'];
                                        $department = $values['0']['0']['0']['department'];
                                        $designation = $values['0']['0']['0']['designation'];
                                    } else {
                                        $emp = $values['0']['0']['employee_info']['emp_pkey'];
                                        $emp_name = $values['0']['0']['employee_info']['EmpName'];
                                        // Edited by Akshay on 19-3-2025
                                        $status = (isset($values[0][0]['emp_details']['status']) && $values[0][0]['emp_details']['status'] == 1) ? '' : ' (Resigned)';
                                        $emp_name = TRIM($emp_name) . $status;
                                        // End
                                        $emp_id = $values['0']['0']['employee_info']['employee_id'];
                                        $joining_date = $values['0']['0']['employee_info']['joining_date'];
                                        $branch = $values['0']['0']['employee_info']['branch'];
                                        $department = $values['0']['0']['employee_info']['department'];
                                        $designation = $values['0']['0']['employee_info']['designation'];
                                    }

                                    // End

                                    $col = 0;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $emp_name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $emp_id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $joining_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $designation);
                                    $j = 7;
                                    foreach ($arr_salary_for_template as $values) {
                                        foreach ($values as $value) {
                                            // Edited by Akshay on 22-3-2025
                                            if ($user == 'GLET' || $user == 'KWMT') {
                                                $condition = isset($value['0']['0']['emp_pkey']) ?  $value['0']['0']['emp_pkey'] == $emp : false;
                                            } else {
                                                $condition = isset($value['0']['employee_info']['emp_pkey']) ? $value['0']['employee_info']['emp_pkey'] == $emp : false;
                                            }

                                            if ($condition) {
                                                // End
                                                $settle_pt = isset($value['0']['0']['Settle_PT']) ? $value['0']['0']['Settle_PT'] * -1 : 0;
                                                if ($settle_pt == 0) {
                                                    $pro_tax = isset($value['0']['0']['Professional_Tax']) ? round($value['0']['0']['Professional_Tax']) : 0;
                                                    $pt += isset($value['0']['0']['Professional_Tax']) ? round($value['0']['0']['Professional_Tax'], 2) : 0;
                                                } else {
                                                    $pro_tax = round($settle_pt);
                                                    $pt += round($settle_pt, 2);
                                                }
                                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $j) . $rowcount, $pro_tax);
                                                $j = $j + 1;
                                            }
                                        }
                                    }
                                    // exit;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $j) . $rowcount, $pt);

                                    $col = 7;
                                    $rowcount++;
                                }
                            }
                        }
                        if ($sum == 0) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records Found');
                            $worksheet->mergeCells('A' . $rowcount . ':N' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Total');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            $worksheet->mergeCells('A' . $rowcount . ':M' . $rowcount);
                            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $sum);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                        }
                    }
                } catch (Exception $e) {
                    debug($e);
                    exit;
                }

                //                                     
                $objPHPExcel->getActiveSheet()->setTitle('ProfessionalTax Report');
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */
                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);
                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            case 'print':
                //   echo "entered in";
                $this->set('mode', 'print');
                $this->render('professionaltax');
                break;
            default:
                $this->set('mode', '');
                $this->render('professionaltax');
                break;
        }
    }

    //Edited by Akshay 13/7/2023
    private function generateEPFNEWlabourreport($mode)
    {
        //debug($mode);exit;

        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);exit;

        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc
                where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc ORDER BY emp_salary_slip_pkey ");

        $array_key = array();
        foreach ($arr_keys as $val) {
            if ($val['ectc']['head_operator'] == 'Addition') {

                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {

                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        //$otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));

        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $conditions = array();

        $conditions[] = 'and ectc.month_year="' . $from . '"';

        $arr_leavepolicygroupids = array();

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {

            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_salary_for_template = array();
        $arr_leavepolicydetails_for_template = array();
        $arr_emp = array();

        $id = implode(' AND ', $conditions);
        // debug($id);exit;

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))

            $k = 0;

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $resign_condition = " and emp_details.status in ('1','2') ";
            $resign_c = " and status in ('1','2') ";
        } else {

            $resign_condition = " and emp_details.status = '1' ";
            $resign_c = " and status = '1' ";
        }
        $arr_excluded_emp = array();
        $arr_empcount = array();
        $arr_included = array();

        try {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'Units') {


                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.pf,emp_details.eps,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,
(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) as EPF_earning ,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee epf' and status=1) and salary_amount != 0) and employee_info.emp_pkey in 
(select emp_fkey from payroll_master where month_year='$from' and net_salary > 0 and action in('Approved','Processed'))
and emp_details.branch_code = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } elseif ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.pf,emp_details.eps,emp_details.classification,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,
(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) as EPF_earning ,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 
where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee epf' and status=1) and salary_amount != 0) and employee_info.emp_pkey in 
(select emp_fkey from payroll_master where month_year='$from' and net_salary > 0 and action in('Approved','Processed'))
and emp_details.emp_pkey = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } elseif ($arr_form_data['select-criteria1'] == 'Departments') {

                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.pf,emp_details.eps,emp_details.classification,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,

    (select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) as EPF_earning ,

abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY from employee_info 
left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)
left join department on (department.dept_code = emp_proff.emp_dept)
where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee epf' and status = 1) and salary_amount != 0) and employee_info.emp_pkey in 
(select emp_fkey from payroll_master where month_year='$from' and net_salary > 0 and action in('Approved','Processed'))
and department.dept_code = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } elseif ($arr_form_data['select-criteria1'] == 'Designation') {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.pf,emp_details.eps,emp_details.classification,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,

    (select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) as EPF_earning ,

abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY from employee_info 
left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)
left join designation on (designation.desig_code = emp_proff.designation) 
where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee epf' and status=1) and salary_amount != 0) and employee_info.emp_pkey in 
(select emp_fkey from payroll_master where month_year='$from' and net_salary > 0 and action in('Approved','Processed'))
and designation.desig_code = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } else {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.pf,emp_details.eps,emp_details.classification,abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,

    (select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) as EPF_earning ,

abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
abs(ifnull((select MAX(ectc.salary_amount) from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY from employee_info 
left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)
left join designation on (designation.desig_code = emp_proff.designation) 
where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee epf' and status=1) and salary_amount != 0) and employee_info.emp_pkey in 
(select emp_fkey from payroll_master where month_year='$from' and net_salary > 0 and action in('Approved','Processed'))
and emp_details.classification = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                }


                //Edited by aswathy add itempart and head operator on 23/11/23.
                $gross_excluded[] = $this->EmpCtcTransaction->query("SELECT SUM(salary_amount) AS total_gross FROM emp_salary_slip WHERE month_year = '$from' AND item_part='Direct'  AND head_operator = 'Addition' AND end_date_effective IS NULL
AND emp_salary_slip.emp_fkey not in(SELECT DISTINCT emp_fkey
     FROM emp_salary_slip
     WHERE month_year = '$from'
       AND end_date_effective IS NULL
       AND emp_salary_slip.salary_head_item_fkey IN ( 
         SELECT salary_head_item_fkey
         FROM tax_salary_components
         WHERE lcase(tax_salary_components_name) = 'employee epf'
         AND status = 1
       )) AND emp_salary_slip.emp_fkey IN (SELECT emp_pkey FROM emp_details WHERE branch_code = '$leavepolicygroupid' $resign_c);
");
                $resign_cond = str_replace(" and ", " ", $resign_c);

                // $arr_empcount[] = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_fkey AS emp_pkey FROM emp_salary_slip WHERE month_year = '$from' AND end_date_effective IS NULL AND emp_fkey IN (SELECT emp_pkey FROM emp_details WHERE $resign_cond)");
                // $arr_included[] = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_fkey AS emp_pkey FROM emp_salary_slip WHERE month_year='$from' and 
                //                                                     end_date_effective IS NULL and emp_salary_slip.salary_head_item_fkey in (select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) AND emp_fkey IN (SELECT emp_pkey FROM emp_details WHERE $resign_cond)");

                // $pkeys = array();                
                // foreach ($arr_included as $sub_array) {
                //     foreach ($sub_array as $item) {
                //         $pkeys[] = $item['emp_salary_slip']['emp_pkey'];
                //     }
                // }

                // $comma_separated_included = "'" . implode("', '", $pkeys) . "'";

                // //Edited by Akshay on 21-5-2024
                // $arr_excluded_emp_pkey[] = $this->EmpCtcTransaction->query("SELECT DISTINCT ectc.emp_fkey AS emp_pkey FROM emp_salary_slip ectc
                //                                                     WHERE ectc.month_year = '$from' AND ectc.end_date_effective IS NULL 
                //                                                     AND ectc.emp_fkey IN (SELECT emp_pkey FROM emp_details WHERE $resign_cond)
                //                                                     AND ectc.emp_fkey NOT IN ($comma_separated_included);
                //                                                     ");
                //End
                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = $arr_gross;
                }

                $k++;
            }

            //code
            $gross_exc_sum = 0;
            foreach ($gross_excluded as $branch) {
                $gross_exc_sum += isset($branch[0][0]['total_gross']) ? $branch[0][0]['total_gross'] : 0;
            }

            // end debug($gross_exc_sum);exit();


            // debug($arr_included); exit;
            $arr_emplr_pf_no = $this->EmpCtcTransaction->query("SELECT pf_no FROM compliance");
            $emplr_pf_no = $arr_emplr_pf_no[0]['compliance']['pf_no'];


            $arr_total_emp = array();
            $excluded_empkeys = array();
            $arr_included_emp = array();
            $arr_excluded = array();

            $arr_excluded = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_fkey AS emp_pkey
    FROM emp_salary_slip
    WHERE month_year = '$from'
      AND end_date_effective IS NULL
      AND emp_salary_slip.salary_head_item_fkey IN (
        SELECT salary_head_item_fkey
        FROM tax_salary_components
        WHERE lcase(tax_salary_components_name) != 'employee epf'
        AND status = 1
      )");
        } catch (Exception $e) {
            debug($e);
        }

        //Edited by Akshay on 24-5-2024
        $arr_empcount[] = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_fkey AS emp_pkey FROM emp_salary_slip WHERE month_year = '$from' AND end_date_effective IS NULL AND emp_fkey IN (SELECT emp_pkey FROM emp_details WHERE $resign_cond)");
        //End
        foreach ($arr_empcount as $branch) {
            foreach ($branch as $emp) {
                if ($emp['emp_salary_slip']['emp_pkey']) {
                    $arr_total_emp[] = $emp['emp_salary_slip']['emp_pkey'];
                }
            }
        }
        foreach ($arr_included as $branch) {
            foreach ($branch as $emp) {
                if ($emp['emp_salary_slip']['emp_pkey']) {
                    $arr_included_emp[] = $emp['emp_salary_slip']['emp_pkey'];
                }
            }
        }

        foreach ($arr_excluded as $slip) {
            $arr_excluded_emp = $slip['emp_salary_slip']['emp_pkey'];
        }

        $empcount = count($arr_total_emp);
        $included_empcount = count($arr_included_emp);
        // foreach($arr_exc_emp as $emp){
        //     $excluded_empkeys[] =  $emp['emp_details']['emp_pkey'];
        // }
        //$excluded_empkeys = implode(',', $excluded_empkeys); 
        //$exc_gross = $this->EmpCtcTransaction->query("SELECT SUM(salary_amount) AS total_gross FROM emp_salary_slip WHERE month_year = '$from' AND end_date_effective IS NULL AND emp_salary_slip.salary_head_item_fkey in (select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)!= 'employee epf' and status=1)");
        // $total_employees = $arr_empcount[0][0]['total_no'];
        // $rejected_count = $arr_excluded[0][0]['rejected_no'];


        //$arr_excluded = $this->EmpCtcTransaction->query();
        // debug($empcount);
        // debug($gross_excluded); exit;
        // $this->set('total_employees', $total_employees);
        // $this->set('rejected_count', $rejected_count);
        $this->set('excluded_empkeys', $excluded_empkeys);
        $this->set('exc_gross', $gross_exc_sum);
        $this->set('emplr_pf_no', $emplr_pf_no);
        $this->set('empcount', $empcount);
        $this->set('included_empcount', $included_empcount);


        $this->set('keys', $arr_keys);

        $this->set('array_key', $array_key);

        //debug($gross_exc_sum); exit();

        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('M', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $date = date('d-m-Y', strtotime($arr_form_data['reportfrom']));
        $this->set('date', $date);

        $this->set('mname1', $mname);
        $this->set('y1', $year);
        $this->set('month2', $month);
        $this->set('month1', $month1);

        //Edited by Akshay in 21-5-2024
        $arr_included[] = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_fkey AS emp_pkey FROM emp_salary_slip WHERE month_year='$from' and 
        end_date_effective IS NULL and emp_salary_slip.salary_head_item_fkey in (select salary_head_item_Fkey from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) AND emp_fkey IN (SELECT emp_pkey FROM emp_details WHERE $resign_cond)
        AND salary_amount != 0");

        $pkeys = array();
        foreach ($arr_included as $sub_array) {
            foreach ($sub_array as $item) {
                $pkeys[] = $item['emp_salary_slip']['emp_pkey'];
            }
        }

        $comma_separated_included = "'" . implode("', '", $pkeys) . "'";

        //Edited by Akshay on 21-5-2024
        $arr_excluded_emp_pkey[] = $this->EmpCtcTransaction->query("SELECT DISTINCT ectc.emp_fkey AS emp_pkey FROM emp_salary_slip ectc
                WHERE ectc.month_year = '$from' AND ectc.end_date_effective IS NULL 
                AND ectc.emp_fkey IN (SELECT emp_pkey FROM emp_details WHERE $resign_cond)
                AND ectc.emp_fkey NOT IN ($comma_separated_included);
                ");
        $arr_exc_pkey = array();

        foreach ($arr_excluded_emp_pkey as $arr_exc_emps) {
            foreach ($arr_exc_emps as $exc_emp)
                $arr_exc_pkey[] = isset($exc_emp['ectc']['emp_pkey']) ? $exc_emp['ectc']['emp_pkey'] : 0;
        }

        $excluded_key = count($arr_exc_pkey);
        $this->set('excluded_key', $excluded_key);
        $arr_exc_pkey = "'" . implode("', '", $arr_exc_pkey) . "'";
        try {
            $arr_exc_gross_sum = $this->EmpCtcTransaction->query("SELECT ectc.payroll_master_fkey, SUM(ROUND(ectc.salary_amount)) as exc_sum FROM emp_salary_slip ectc 
            LEFT JOIN salary_head_items shi ON shi.salary_head_item_pkey = ectc.salary_head_item_fkey
            WHERE ectc.emp_fkey IN ($arr_exc_pkey) AND ectc.end_date_effective IS NULL 
            AND ectc.head_operator = 'Addition' AND ectc.item_part = 'Direct'
            AND ectc.month_year = '$from'
            AND shi.head_fkey = 1");
        } catch (Exception $e) {
            debug($e);
        }
        $excluded_sum = isset($arr_exc_gross_sum[0][0]['exc_sum']) ? floatval($arr_exc_gross_sum[0][0]['exc_sum']) : 0;
        $this->set('excluded_sum', $excluded_sum);
        //End
        //debug($arr_salary_for_template); exit;
        // $arr_employeepf = array();
        // foreach ($arr_salary_for_template as $value) {
        //     if (count($value) > 0) {
        //         $arr_employeepf[] = $value;
        //     }
        // }
        // foreach ($arr_employeepf as $branch) {
        //     foreach ($branch as $employee) {
        //         debug($employee);
        //     }
        // }
        // exit;

        $cr = $arr_form_data['select-criteria1'];

        $this->set('cr', $cr);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $user_name = $this->Session->read('user_name');

        $this->set('user_name', $user_name);

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $this->set('month', $from);
        $cname = $arr_comp_contact_info['CompanyContactInfo']['business_name'];
        $this->set('str_criteria_item', $str_criteria_item);


        //Set informations needed for report


        switch ($mode) {

            case 'pdf':

                //   echo "entered in";

                $this->set('mode', 'pdf');

                $view = new View($this, false);

                $view_output = $view->render('empepfnew');

                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));



                $html2pdf = new HTML2PDF('P', 'A4', 'en');

                // $html2pdf->addFont('Times New Roman Bold', '', getcwd().'/fonts/times new roman bold.ttf');
                // $html2pdf->addFont('Arial', '', getcwd().'/fonts/Arialn.ttf');
                //debug(getcwd());exit;

                $html2pdf->pdf->SetDisplayMode('fullpage');


                // Add CSS styles to increase the table width
                $view_output = '<style>table { width: 100%; }</style>' . $view_output;

                $html2pdf->writeHTML($view_output);

                //Edited by Akshay on 14/7/2023
                // Get the total number of pages in the PDF
                $totalPages = $html2pdf->pdf->getPage();

                // Define the margin for the footer (adjust this value as needed)
                $footerMargin = 30; // You can change this value as per your requirements

                // Add the footer to each page
                for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
                    $html2pdf->pdf->setPage($pageNumber);

                    // Set the font and font size for the footer
                    $html2pdf->pdf->SetFont('helvetica', '', 10);

                    // Set the position for the line
                    $footerX = 15;
                    $footerY = $html2pdf->pdf->getPageHeight() - $footerMargin;
                    $footerWidth = $html2pdf->pdf->getPageWidth() - 30;

                    // Calculate the remaining space for content on the page
                    $contentHeight = $html2pdf->pdf->getPageHeight() - $html2pdf->pdf->GetY() - $footerMargin;

                    // Check if there is enough space for the footer
                    if ($contentHeight < 20) { // Adjust this value as needed
                        $html2pdf->pdf->AddPage();
                        // $pageNumber++; // Increase the page number
                        $html2pdf->pdf->setPage($pageNumber);

                        // Reset the content height
                        $contentHeight = $html2pdf->pdf->getPageHeight() - $footerMargin;
                    }

                    $html2pdf->pdf->SetXY($footerX, $footerY + 10);
                    $html2pdf->pdf->Cell($footerWidth, 0, '', 'B', 0, 'C');

                    // Set the position for the page number
                    $html2pdf->pdf->SetXY($footerX, $footerY + 12);
                    $html2pdf->pdf->Cell($footerWidth, 10, $pageNumber, 0, 0, 'R');

                    // Increment the page number only once at the end of each loop iteration
                    $pageNumber++;
                }
                //--------------------------------
                $str_company_code = $this->Session->read('company_code');
                date_default_timezone_set('Asia/Kolkata');
                $currentMonthYear = date('m-Y');
                $file_name = isset($str_company_code) ? $str_company_code . "PFSummary_" . $currentMonthYear . ".pdf" : "PFSummary" . $currentMonthYear . ".pdf";
                $html2pdf->Output($file_name, 'D');

                //$this->render('empepf');

                break;

            case 'excel':

                $str_company_code = $this->Session->read('company_code');
                date_default_timezone_set('Asia/Kolkata');
                $currentMonthYear = date('m-Y');
                $file_name = isset($str_company_code) ? $str_company_code . "PFSummary_" . $currentMonthYear . ".xlsx" : "PFSummary" . $currentMonthYear . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");

                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");

                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                if (empty($arr_salary_for_template)) {


                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 2,  " PF Summary for the month of " . date('F-Y', strtotime($date)));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);

                    for ($col = 'A'; $col !== 'M'; $col++) {

                        $objPHPExcel->getActiveSheet()

                            ->getColumnDimension($col)

                            ->setAutoSize(false);
                    }

                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(23);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);


                    $worksheet->mergeCells('A2:G2');

                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                    );



                    $rowcount = 2;
                    $worksheet->mergeCells('A1:D1');

                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)

                    );
                    $worksheet->setCellValueByColumnAndRow(0, 1, $cname);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);


                    //Edited by Akshay on 28-5-2024
                    $worksheet->mergeCells('E1:G1');

                    $worksheet->getStyle('E1')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)

                    );
                    $worksheet->setCellValueByColumnAndRow(4, 1, "Employer PF No.:" . $emplr_pf_no);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->getFont()->setBold(true);
                    //End



                    //print nodata
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(11);
                    $worksheet->mergeCells('A3:G3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                } else {
                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 2,  " PF Summary for the month of " . date('F-Y', strtotime($date)));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);

                    for ($col = 'A'; $col !== 'M'; $col++) {

                        $objPHPExcel->getActiveSheet()

                            ->getColumnDimension($col)

                            ->setAutoSize(false);
                    }

                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(23);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);


                    $worksheet->mergeCells('A2:G2');

                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                    );



                    $rowcount = 2;
                    $worksheet->mergeCells('A1:D1');

                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)

                    );
                    $worksheet->setCellValueByColumnAndRow(0, 1, $cname);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);


                    //Edited by Akshay on 28-5-2024
                    $worksheet->mergeCells('E1:G1');

                    $worksheet->getStyle('E1')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)

                    );
                    $worksheet->setCellValueByColumnAndRow(4, 1, "Employer PF No.:" . $emplr_pf_no);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->getFont()->setBold(true);
                    $worksheet->getStyle('G1')->applyFromArray([
                        'borders' => [
                            'right' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                    //End

                    $rowcount = 3;
                    $worksheet->mergeCells('D3:E3');

                    $worksheet->getStyle('D3')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                    );
                    $worksheet->setCellValueByColumnAndRow(3, 3, "Employee Contribution");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 3)->getFont()->setBold(true);

                    $worksheet->mergeCells('F3:G3');

                    $worksheet->getStyle('F3')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                    );
                    $worksheet->setCellValueByColumnAndRow(5, 3, "Employer Contribution");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 3)->getFont()->setBold(true);

                    $worksheet->mergeCells('A3:A4');

                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)

                    );
                    $worksheet->setCellValueByColumnAndRow(0, 3, "Sl. No.");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);


                    $worksheet->mergeCells('B3:B4');

                    $worksheet->getStyle('B3')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)

                    );
                    $worksheet->setCellValueByColumnAndRow(1, 3, "UAN");


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 3)->getFont()->setBold(true);

                    $worksheet->mergeCells('C3:C4');

                    $worksheet->getStyle('C3')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)

                    );
                    //$worksheet->getColumnDimension('C')->setWidth(60);
                    $worksheet->setCellValueByColumnAndRow(2, 3, "Name of Member");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, 3)->getFont()->setBold(true);





                    $rowcount = 4;


                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'PF Earnings ');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Contribution EPF');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'EPF Difference');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'Pension 8.33% ');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                    $rowcount = 5;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '(1)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '(2)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '(3)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '(4)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '(5)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '(6)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '(7)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                    $i = 0;



                    $granttotal = 0;
                    $total = 0;
                    $arr_employeepf = array();
                    foreach ($arr_salary_for_template as $value) {
                        if (count($value) > 0) {
                            $arr_employeepf[] = $value;
                        }
                    }

                    if (count($arr_employeepf) > 0) {
                        $i = 0;
                        $column4 = 0;
                        $column5 = 0;
                        $column6 = 0;
                        $column7 = 0;
                        $edli = 0;
                        $saltot = 0;
                        $totctr = 0;
                        $totdiff = 0;
                        $totpen = 0;
                        $t1 = 0;
                        $t2 = 0;
                        $t3 = 0;
                        $t4 = 0;
                        $ac1 = 0;
                        $ac2 = 0;
                        $ac21 = 0;
                        $ac10 = 0;
                        $pensiontotal = 0;
                        $rowcount = 6;

                        foreach ($arr_employeepf as $branch) {
                            foreach ($branch as $val) {
                                // debug($employee);exit();

                                $i++;

                                $name = $val['employee_info']['EmpName'];
                                $uan = $val['emp_details']['pf'];



                                $sal1 = isset($val['0']['EPF_earning']) ? $val['0']['EPF_earning'] : '';
                                if ($sal1 != '') {
                                    $normalized = preg_replace('/\s*([\*\/])\s*/', '$1', $sal1);
                                    $expressionWithoutPortion = str_replace(['*.12', '*12/100'], '', $normalized);
                                    eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                    $sal1 = floor($epf_earnings);
                                }
                                //Edited by Akshay on 20-5-2024
                                if ($sal1 > 15000) {
                                    $sal1 = 15000;
                                }
                                //End

                                $sal = ($val['0']['EPF'] * 100 / 12);
                                $column4 = $column4 + $sal1;

                                if ($sal1 > 15000) {
                                    $edli = $edli + 15000;
                                    if ($val['emp_details']['eps'] != 'N') {
                                        $pension = round((15000) * 0.0833);
                                        $column7 = $column7 + $pension;
                                        $pensiontotal = $pensiontotal + 15000;
                                    } else {
                                        $pension = 0;
                                        $pensiontotal = $pensiontotal + 0;
                                    }
                                } else {
                                    $edli = $edli + $sal1;

                                    if ($val['emp_details']['eps'] != 'N') {
                                        $pension = round($sal1 * 0.0833);
                                        $column7 = $column7 + $pension;
                                        $pensiontotal = $pensiontotal + $sal1;
                                    } else {
                                        $pension = 0;
                                        $pensiontotal = $pensiontotal + 0;
                                    }
                                }

                                $emp_contr = $val['0']['EPF'];
                                $column5 = $column5 + $emp_contr;

                                $difference = $emp_contr - $pension;
                                $column6 = $difference + $column6;



                                //cpoied


                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $i);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $uan);
                                $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 1, $rowcount)->setValueExplicit($uan, PHPExcel_Cell_DataType::TYPE_STRING);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                // $objPHPExcel ->getActiveSheet() ->getCellByColumnAndRow($col+ 1, $rowcount) ->setValueExplicit($uan, PHPExcel_Cell_DataType::TYPE_STRING);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $worksheet->getStyleByColumnAndRow(($col + 3), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $sal1); //debug($sal1);exit();
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                                $worksheet->getStyleByColumnAndRow(($col + 4), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, ($emp_contr == 0) ? '' : round($emp_contr));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                $worksheet->getStyleByColumnAndRow(($col + 5), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $difference);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                                $worksheet->getStyleByColumnAndRow(($col + 6), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $pension);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                                $rowcount++;
                            }
                        }

                        $worksheet->setCellValueByColumnAndRow(0, $rowcount, "T O T A L");

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                        $worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);

                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(

                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)

                        );

                        // setlocale(LC_MONETARY, 'en_IN');
                        // $saltot1 = money_format('%!i', ($saltot));

                        $worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $column4);
                        // $worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);

                        //$totctr1 = money_format('%!i', ($totctr));
                        $worksheet->getStyleByColumnAndRow(4, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $column5);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);

                        //$totdiff1 = money_format('%!i', ($totdiff));
                        $worksheet->getStyleByColumnAndRow(5, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $column6);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);

                        //$totpen1 = money_format('%!i', ($totpen));
                        $worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $column7);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);


                        $rowcount++;
                        //  $objPHPExcel->getActiveSheet()
                        // ->getStyle('D6:G500')
                        // ->getAlignment()
                        // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A3:G4')
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        // $objPHPExcel->getActiveSheet()
                        // ->getStyle('A6:C500')
                        // ->getAlignment()
                        // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $BStyle = array(

                            'borders' => array(

                                'allborders' => array(

                                    'style' => PHPExcel_Style_Border::BORDER_THIN

                                )

                            )

                        );


                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A2:G' . $row)->applyFromArray($BStyle);
                        //     foreach(range('A','G') as $columnID) {
                        //     $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                        // }


                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->setShowGridlines(false);

                        //$worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Account No:01");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);

                        $worksheet->mergeCells('D' . $rowcount . ':E' . $rowcount);
                        $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(3, $rowcount, "(Column Nos.5+6)");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyle('G' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $worksheet->getStyle('F' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(5, $rowcount, " = ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);

                        $worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');
                        $worksheet->setCellValueByColumnAndRow(6, $rowcount, ($column5 + $column6));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $t1 = 0;
                        $t1 = $ac1;

                        $rowcount = $rowcount + 1;
                        //$worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Account No:02");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);

                        // Set horizontal alignment to left



                        $worksheet->mergeCells('D' . $rowcount . ':E' . $rowcount);
                        $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(3, $rowcount, "(0.50000% of Column No.4)");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyle('F' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(5, $rowcount, " = ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyle('G' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        // $ac2_formatted = round($ac2 . '.00');
                        // debug($ac2_formatted);exit();
                        //  $t2=0;
                        //  $t2 = $ac2 ;
                        //  $a3=0;
                        //  // debug($t2);exit();
                        //   $a2=round((float)str_replace([','], '', $t2));
                        //   //debug($a2);exit();
                        //   $a3=number_format($t2,2);
                        //   //debug($a3);exit();

                        // $worksheet->setCellValueByColumnAndRow(6, $rowcount,$a3);
                        // $t2 = 0;
                        // $t2 = $ac2;


                        // $ac2 = round((float)str_replace([','], '', $t2));
                        // // $a2_formatted = number_format($ac2, 2);
                        // $temp_ac2 = money_format('%!i', ($ac2));
                        // $worksheet->setCellValueByColumnAndRow(6, $rowcount,$a4 .'00');
                        $worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                        $worksheet->setCellValueByColumnAndRow(6, $rowcount, round(($column4 * .005)));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        // $t2=0;
                        //  $t2 = $ac2 ;



                        $rowcount = $rowcount + 1;
                        //$worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Account No:10");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);


                        // Set horizontal alignment to left


                        $worksheet->mergeCells('D' . $rowcount . ':E' . $rowcount);
                        $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(3, $rowcount, "(Column No. 7)");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyle('F' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->setCellValueByColumnAndRow(5, $rowcount, " = ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyle('G' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );

                        //$temp_ac10 = money_format('%!i', ($ac10));
                        $worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                        $worksheet->setCellValueByColumnAndRow(6, $rowcount, $column7);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        $t3 = 0;
                        $t3 = $ac10;


                        $rowcount = $rowcount + 1;

                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(0, $rowcount, "E  D L  I  Wages   :");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->setCellValueByColumnAndRow(1, $rowcount, number_format($edli, 2));


                        $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Account No: 21");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);

                        // Set horizontal alignment to left

                        $worksheet->mergeCells('D' . $rowcount . ':E' . $rowcount);
                        $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(3, $rowcount, "E D L I WAGES * 0.50000%");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);


                        $worksheet->getStyle('F' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->setCellValueByColumnAndRow(5, $rowcount, " = ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyle('G' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );


                        // $t4=0;
                        //  $t4 = $ac21;
                        //  $a4=0;
                        //  // debug($t2);exit();
                        //   $ac21=round((float)str_replace([','], '', $t4));
                        //   //debug($a21);exit();
                        //   $a4=number_format($t4,2);
                        //   //debug($a4);exit();

                        // $t4 = 0;
                        // $t4 = $ac21;
                        // $a4 = 0;

                        // $ac21 = round((float)str_replace([','], '', $t4));
                        // $temp_ac21 = money_format('%!i', ($ac21));
                        // $a4_formatted = number_format($ac21, 2);

                        // $worksheet->setCellValueByColumnAndRow(6, $rowcount,$a4 .'00');
                        $worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                        $worksheet->setCellValueByColumnAndRow(6, $rowcount, round(($edli * 0.5 / 100)));

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        // $t4=0;
                        // $t4=$ac21;

                        $rowcount = $rowcount + 1;
                        $styleArray = array(
                            'borders' => array(
                                'bottom' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                ),
                            ),
                        );

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':G' . $rowcount)->applyFromArray($styleArray);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );

                        $worksheet->getStyle('B' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Pension Wages :");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyleByColumnAndRow(1, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                        $worksheet->setCellValueByColumnAndRow(1, $rowcount, ($pensiontotal));

                        $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Account No: 22");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);

                        $worksheet->mergeCells('D' . $rowcount . ':E' . $rowcount);
                        $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(3, $rowcount, "E D L I WAGES * 0.00000%");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyle('F' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->setCellValueByColumnAndRow(5, $rowcount, " = ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(6, $rowcount, "0.00");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);

                        //total

                        $rowcount = $rowcount + 1;
                        $styleArray = array(
                            'borders' => array(
                                'bottom' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                ),
                            ),
                        );

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':G' . $rowcount)->applyFromArray($styleArray);


                        //  $worksheet->mergeCells('A' . $rowcount . ':E' . $rowcount);
                        // $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,));

                        $worksheet->setCellValueByColumnAndRow(2, $rowcount, "T O T A L");
                        $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);

                        $styleArray = array(
                            'borders' => array(
                                'bottom' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                ),
                            ),
                        );

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':G' . $rowcount)->applyFromArray($styleArray);



                        // $a1 = round((float)str_replace([','], '', $t1));
                        // $a2 = round((float)str_replace([','], '', $t2));
                        // $a3 = round((float)str_replace([','], '', $t3));
                        // $a4 = round((float)str_replace([','], '', $t4));
                        // $integerValue = (float)str_replace([','], '', $t1);

                        // $total = ($a1 + $a2 + $a3 + $a4);
                        // //debug($total);exit();
                        // $temp_total = money_format('%!i', ($total));

                        // $sum_total = ceil($column5 + $column6 + ($column4 * 0.005) + $column7 + ($edli * 0.5 / 100));
                        // if ($sum_total != 0) {
                        //     // You can add further PHP logic here if needed.
                        // } else {
                        //     $sum_total = '';
                        // }

                        // setlocale(LC_MONETARY, 'en_IN');
                        // $saltot1 = money_format('%!i', $sum_total);
                        $sum_total = $column5 + $column6 + ($column4 * 0.005) + $column7 + round(($edli * 0.5 / 100));

                        if ($sum_total != 0) {
                            // You can add further PHP logic here if needed.
                        } else {
                            $sum_total = '';
                        }

                        setlocale(LC_MONETARY, 'en_IN');
                        $saltot1 = number_format($sum_total, 2, '.', ''); // Adjust the number of decimal places as needed

                        //debug($sum_total);exit();

                        //Edited by Akshay on 20-5-2024
                        $worksheet->getStyleByColumnAndRow(($col + 6), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');
                        //End
                        $worksheet->setCellValueByColumnAndRow(6, $rowcount, round($saltot1));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $worksheet->getStyle('G' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );




                        $rowcount = $rowcount + 1;

                        $worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total No. of Employees in the Month:" . $empcount);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('D' . $rowcount . ':G' . $rowcount);

                        $rowcount = $rowcount + 1;

                        $worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(0, $rowcount, "No. of Excluded Employees:" . ($excluded_key));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('D' . $rowcount . ':G' . $rowcount);

                        $rowcount = $rowcount + 1;

                        $worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Gross Wages of Excluded Employees:" . number_format(max(0, $excluded_sum), 2));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('D' . $rowcount . ':G' . $rowcount);
                        $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    }
                }

                // $rowcount++;

                // $col = 0;

                // $worksheet->setCellValueByColumnAndRow(0, $rowcount, "PF SUMMARY NEW");

                // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                // $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);

                // $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(

                //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                // );


                $objPHPExcel->getActiveSheet()
                    ->getStyle('A5:G5')
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                $objPHPExcel->getActiveSheet()->setTitle('PF summary');

                /* header footer */

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                /* header footer */

                /* print Set up */

                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed

                header('Content-Type: application/vnd.ms-excel; charset=utf-8');

                header('Content-Disposition: attachment; filename=' . $file_name);



                readfile(dirname(__FILE__) . "/" . $file_name);

                unlink(dirname(__FILE__) . "/" . $file_name);

                break;

            case 'print':

                //   echo "entered in";

                $this->set('mode', 'print');

                $this->render('empepfnew');

                break;

            default:

                $this->set('mode', '');

                $this->render('empepfnew');

                break;
        }
    }

    private function generateTAXreport($mode = '')
    {
        $arr_form_data = $_REQUEST;
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->EmpTaxSalTransNew->useDbConfig = $this->Session->read('ds');
        //Edited by Akshay on 8-7-2024
        $this->EmployeeTaxsalsum->useDbConfig = $this->Session->read('ds');
        //End
        //        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc
        //        where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc ORDER BY emp_salary_slip_pkey ");
        //        $array_key = array();
        //        foreach ($arr_keys as $val) {
        //            if ($val['ectc']['head_operator'] == 'Addition') {
        //                $array_key['Addition'][] = $val[0]['sal_head'];
        //            } else {
        //                $array_key['Deduction'][] = $val[0]['sal_head'];
        //            }
        //        }
        $from = date('Y-m', strtotime($arr_form_data['reportsfrom']));
        $to = date('Y-m', strtotime($arr_form_data['reportsto']));
        $this->set('from', $from);
        $this->set('to', $to);
        $arr_finyear = $this->EmpCtcTransaction->query("SELECT DISTINCT start_month, end_month FROM fin_year WHERE is_current_finyear = 'Y' AND vattr1 = 1");
        $start_month = isset($arr_finyear[0]['fin_year']['start_month']) ? $arr_finyear[0]['fin_year']['start_month'] : '';
        $end_month = isset($arr_finyear[0]['fin_year']['end_month']) ?  $arr_finyear[0]['fin_year']['end_month'] : '';
        // Convert strings to timestamps
        $to_timestamp = strtotime($to);
        $start_month_timestamp = strtotime($start_month);
        $end_month_timestamp = strtotime($end_month);
        // Check if $to is between $start_month and $end_month
        if ($to_timestamp >= $start_month_timestamp && $to_timestamp <= $end_month_timestamp && $start_month != '' && $end_month != '') {
            $isFinYear = true;
            $finyear = 2024;
        } else {
            $isFinYear = false;
            // $finyear=2023;
            // $finstart ="2023-04";
            // $finend ="2024-03";
            $finyear = 2024;
            $finstart = $from;
            $finend = $to;
        }


        $this->set('isFinYear', $isFinYear);
        //Edited by Akshay on 15-6-2024
        function monthsBetweenInclusive($start_month, $end_month)
        {
            $start = new DateTime($start_month);
            $end = new DateTime($end_month);
            // Include the end month in the calculation
            $end->modify('+1 month');
            // Calculate the difference in months
            $interval = $start->diff($end);
            $months = $interval->y * 12 + $interval->m;
            return $months;
        }
        $month_count = monthsBetweenInclusive($start_month, $end_month);
        $this->set('month_count', $month_count);
        //End

        date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
        $f = date('Y-m', strtotime($arr_form_data['reportsfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $arr_tax_heads = array();
        $this->TaxHead->useDbConfig = $this->Session->read('ds');
        //End
        $arr_taxheadfields = $this->requestAction("/Taxation/getTaxHeadFields");
        foreach ($arr_taxheadfields as $field => $value) {
            if (!empty($value['tax_heads'])) {
                $arr_taxheads = $value['tax_heads'];
                // $index = 0;
                foreach ($arr_taxheads as $key => $value) {
                    //  $lim = $value['attr1'];
                    $fieldname = $value['tax_name'];
                    $tax_heads_fkey = $value['tax_heads_pkey'];
                    $tax_head = Set::extract('/TaxHead/.', $this->TaxHead->find("first", array('conditions' => array('tax_heads_pkey' => $tax_heads_fkey))));
                    // $tax_head_name = isset($tax_head[0]['tax_name']) ? $tax_head[0]['tax_name'] : 'Details';
                    $arr_taxheaddetails = $this->requestAction("/Taxation/getTaxHeadDetails/$tax_heads_fkey");
                    foreach ($arr_taxheaddetails as $key1 => $value) {
                        // debug($value);
                        $fieldName = $value['tax_heads_details'];
                        $fieldDesc = ($value['tax_heads_details1'] != '') ? '(' . $value['tax_heads_details1'] . ')' : '';
                        // $amt = $value['tax_new_regime' ];
                        if ($fieldName . $fieldDesc != '')
                            // $arr_tax_heads[$field][$fieldname][$key1] = $fieldName . $fieldDesc;
                            $arr_tax_heads[$field][$fieldname][$key1] = array(
                                'fieldName' => $fieldName . $fieldDesc,
                                'tax_heads_fkey' => $tax_heads_fkey
                            );
                        // $arr_tax_heads[$field][$fieldname][$key1]['value'] = $amt;
                    }
                }
            }
        }
        $this->set('arr_tax_heads', $arr_tax_heads);
        //End


        $conditions = array();
        $conditions[] = 'and ectc.month_year >= "' . $from . '" and ectc.month_year <= "' . $to . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $needBranchWiseReport = false;
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            $this->set('needBranchWiseReport', $needBranchWiseReport);
        }

        $arr_salary_for_template = array();
        $id = implode(' AND ', $conditions);

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $resign_condition = " and emp_status in ('1','2') ";
        } else {
            $resign_condition = " and emp_status = '1' ";
        }

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            //Edited by Akshay on 13-6-2024
            if ($isFinYear) {
                $conditions = array(
                    array("EmpTaxSalTransNew.end_date_effective is null"),
                    array(" ectc.month_year >= '" . $from . "' and ectc.month_year <= '" . $to . "'"),
                    array("ectc.end_date_effective is null")
                );
                $fincondition = " ectc.month_year >= '" . $from . "' and ectc.month_year <= '" . $to . "'";
            } else {
                $conditions = array(
                    array("EmpTaxSalTransNew.end_date_effective is null"),
                    array(" ectc.month_year >= '" . $finstart . "' and ectc.month_year <= '" . $finend . "'"),
                    array("ectc.end_date_effective is null")
                );
                $fincondition = " ectc.month_year >= '" . $finstart . "' and ectc.month_year <= '" . $finend . "'";
            }

            $joins = array(
                array(
                    'table' => 'tax_salary_components',
                    'alias' => 'TSC',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmpTaxSalTransNew.tax_salary_components_fkey = TSC.tax_salary_components_pkey'),
                ),
                array(
                    'table' => 'salary_head_items',
                    'alias' => 'emp_salary_slip',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmpTaxSalTransNew.salary_head_item_Fkey = emp_salary_slip.salary_head_item_pkey')
                ),
                array(
                    'table' => 'emp_salary_slip',
                    'alias' => 'ectc',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmpTaxSalTransNew.salary_head_item_Fkey = ectc.salary_head_item_fkey')
                )
            );
            $arr_sal_items = $this->EmpTaxSalTransNew->find("all", array(
                'conditions' => $conditions,
                'joins' => $joins,
                'fields' => array('DISTINCT emp_salary_slip.salary_head_item_pkey as salary_head_item_fkey , emp_salary_slip.item as salary_head_item_desc')
            ));


            $arr_settle_items = array();
            if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                $arr_settle_items = $this->EmpCtcTransaction->query("SELECT DISTINCT emp_settle_slip.salary_head_item_fkey, emp_settle_slip.salary_head_item_desc,emp_settle_slip.type
                                                                    FROM emp_settle_slip 
                                                                     LEFT JOIN termination ON (termination.emp_fkey = emp_settle_slip.emp_fkey and termination.status = 1)
                                                                    WHERE DATE_FORMAT(termination.last_approved_working_date, '%Y-%m') >= '$from' AND DATE_FORMAT(termination.last_approved_working_date, '%Y-%m') <='$to'
                                                                    AND emp_settle_slip.status = 'Y' and  emp_settle_slip.type != 'SALARY'
                                                                    AND emp_settle_slip.approved = 'Y'
                                                                    GROUP BY emp_settle_slip.type
                                                                    ORDER BY emp_settle_slip.salary_head_item_fkey
                                                                    ");
                //AND salary_amount > 0 
            }
            //End

            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($arr_form_data['select-criteria1'] == 'Units') {
                    try {
                        $arr_gross = $this->EmpCtcTransaction->query("SELECT tax_computation_report.*,ei.EmpName,ei.emp_pkey,emp_details.pan_no,
                                                ei.emp_status,ei.branch_Code,ei.branch,ei.designation,ei.department,ei.emp_id,ei.employee_id,
                                                te.status,te.last_approved_working_date,SUM(tax_computation_report.Monthly_Tax) AS total_tax_deducted,uc.user_id,
                                                (select option_type FROM `emp_tax_regime` WHERE end_date_effective is NULL and emp_fkey = ei.emp_pkey) as regime		  
                                                FROM employee_info ei
                                                LEFT JOIN tax_computation_report  ON (tax_computation_report.emp_fkey = ei.emp_pkey and 
                                                tax_computation_report.modified_date IS NULL and Month_year >= '$from' and Month_year <='$to')
                                                LEFT JOIN termination te ON tax_computation_report.emp_fkey = te.emp_fkey AND te.status = 1
                                                LEFT JOIN user_credentials uc ON uc.emp_fkey = ei.emp_pkey
                                                LEFT JOIN emp_details ON emp_details.emp_pkey = ei.emp_pkey
                                                 WHERE ei.branch_Code = '$leavepolicygroupid' $resign_condition GROUP BY emp_pkey
                                                 ORDER BY ei.branch_Code, ei.EmpName ");
                    } catch (Exception $e) {
                        // debug($e);
                        exit;
                    }
                    foreach ($arr_gross as $value) {
                        $emp = $value['ei']['emp_pkey'];
                        $taxdetails = $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp");
                        $arr_tax_sub_data = array();
                        foreach ($arr_tax_heads['Income'] as $tax_details_componenet) {
                            $tax_key = isset($tax_details_componenet[0]['tax_heads_fkey']) ? $tax_details_componenet[0]['tax_heads_fkey'] : 0;
                            $arr_emp_transaction = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$emp/$tax_key");
                            $return_arr_emp_transaction = array();
                            ksort($arr_emp_transaction);
                            $return_arr_emp_transaction[] = array_values($arr_emp_transaction);
                            $arr_tax_sub_data[$emp][$tax_key]['Income'] = $return_arr_emp_transaction;
                        }
                        foreach ($arr_tax_heads['Deductions'] as $tax_details_componenet) {
                            $tax_key = isset($tax_details_componenet[0]['tax_heads_fkey']) ? $tax_details_componenet[0]['tax_heads_fkey'] : 0;
                            $arr_emp_transaction = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$emp/$tax_key");
                            $return_arr_emp_transaction = array();
                            ksort($arr_emp_transaction);
                            $return_arr_emp_transaction[] = array_values($arr_emp_transaction);
                            $arr_tax_sub_data[$emp][$tax_key]['Deductions'] = $return_arr_emp_transaction;
                        }

                        $arr_tax_sub_head = array();
                        foreach ($arr_tax_heads['Income'] as $key_head => $heads) {
                            foreach ($heads as $key => $head) {
                                $arr_tax_sub_head['Income'][] = isset($arr_tax_sub_data[$emp][$head['tax_heads_fkey']]['Income'][0][$key]) ? $arr_tax_sub_data[$emp][$head['tax_heads_fkey']]['Income'][0][$key] : 0;
                            }
                        }
                        foreach ($arr_tax_heads['Deductions'] as $key_head => $heads) {
                            foreach ($heads as $key => $head) {
                                $arr_tax_sub_head['Deductions'][] = isset($arr_tax_sub_data[$emp][$head['tax_heads_fkey']]['Deductions'][0][$key]) ? $arr_tax_sub_data[$emp][$head['tax_heads_fkey']]['Deductions'][0][$key] : 0;
                            }
                        }
                        $arr_ectc_details = array();
                        $arr_settle_slip = array();
                        $yearlytotal = array();
                        $arr_salary = array();
                        $total = 0;
                        $professional = 0;
                        foreach ($arr_sal_items as $sal_item) {
                            $item_key = isset($sal_item['emp_salary_slip']['salary_head_item_fkey']) ? $sal_item['emp_salary_slip']['salary_head_item_fkey'] : '';

                            $arr_ectc_details[] = $this->EmpCtcTransaction->query("SELECT salary_head_item_fkey, salary_head_item_desc, SUM(ROUND(structure_det_value)) AS std_availed, SUM(ROUND(salary_amount)) AS actual_availed, COUNT(*) AS row_count  
                                                                                    FROM emp_salary_slip 
                                                                                    WHERE emp_fkey = '$emp' AND month_year >= '$from' AND month_year <= '$to'
                                                                                    AND end_date_effective IS NULL AND item_part = 'Direct'
                                                                                    AND salary_head_item_fkey = '$item_key'
                                                                                    GROUP BY salary_head_item_fkey
                                                                                  ");
                            //AND head_operator = 'Addition'
                            //if(!$isFinYear){
                            $arr_ectc_detailsactual = $this->EmpCtcTransaction->query("SELECT  SUM(ROUND(salary_amount)) AS actual_availed
                                                                                    FROM emp_salary_slip ectc
                                                                                    WHERE emp_fkey = '$emp' and $fincondition
                                                                                    AND end_date_effective IS NULL AND item_part = 'Direct' AND salary_head_item_fkey = '$item_key' AND salary_head_item_fkey !=145");

                            $total += isset($arr_ectc_detailsactual[0][0]['actual_availed']) ? $arr_ectc_detailsactual[0][0]['actual_availed'] : 0;
                            //  }                 
                            $arr_salary[] = $this->EmpCtcTransaction->query("select abs(structure_det_value) as amount, salary_head_item_desc from emp_salary_structure where emp_fkey = '$emp' and end_date_effective is null and head_operator <>'Deduction' and item_part <> 'Indirect' and salary_head_item_fkey = '$item_key'");
                        }
                        $yearlytotal = $total;
                        $profess = $this->EmpCtcTransaction->query("SELECT SUM(ROUND(salary_amount)) AS actual_availed 
                                                                                    FROM emp_salary_slip 
                                                                                    WHERE emp_fkey = '$emp' AND month_year >= '$from' AND month_year <= '$to'
                                                                                    AND end_date_effective IS NULL AND item_part = 'Direct'
                                                                                    AND salary_head_item_desc = 'Professional Tax'
                                                                                  ");
                        $professional = isset($profess[0][0]['actual_availed']) ? $profess[0][0]['actual_availed'] : 0;
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $i = 1000;
                            foreach ($arr_settle_items as $settle_item) {
                                $settle_key = isset($settle_item['emp_settle_slip']['salary_head_item_fkey']) ? $settle_item['emp_settle_slip']['salary_head_item_fkey'] : $i;
                                $type = isset($settle_item['emp_settle_slip']['type']) ? $settle_item['emp_settle_slip']['type'] : '';
                                $arr_settle_slip[] =  $this->EmpCtcTransaction->query("SELECT emp_settle_slip.salary_head_item_fkey, emp_settle_slip.salary_head_item_desc, SUM(emp_settle_slip.structure_det_value) AS std_availed, SUM(emp_settle_slip.salary_amount) AS actual_availed 
                                                                                            FROM emp_settle_slip
                                                                                              LEFT JOIN termination ON (termination.emp_fkey = emp_settle_slip.emp_fkey and termination.status = 1)
                                                                                            WHERE emp_settle_slip.emp_fkey = '$emp' AND DATE_FORMAT(termination.last_approved_working_date, '%Y-%m') >= '$from' AND DATE_FORMAT(termination.last_approved_working_date, '%Y-%m') <= '$to'
                                                                                            AND emp_settle_slip.status = 'Y' AND emp_settle_slip.type = '$type'
                                                                                            AND emp_settle_slip.approved = 'Y'
                                                                                            AND emp_settle_slip.salary_amount > 0
                                                                                            GROUP BY emp_settle_slip.type
                                                                                        ");
                                $i++;
                            }
                            $tot = 0;
                            $prof = 0;
                            //debug($arr_settle_slip);
                            foreach ($arr_settle_slip as $item) {
                                $itemname = isset($item[0]['emp_settle_slip']['salary_head_item_desc']) ? $item[0]['emp_settle_slip']['salary_head_item_desc'] : '';
                                if ($itemname == 'Professional Tax Balance') {
                                    $prof = isset($item[0][0]['actual_availed']) ? $item[0][0]['actual_availed'] : 0;
                                } else {
                                    $tot += isset($item[0][0]['actual_availed']) ? $item[0][0]['actual_availed'] : 0;
                                }
                            }
                            $professional = $professional + $prof;
                            $yearlytotal = $yearlytotal + $tot;
                        }

                        $arr_sec_eighty = $this->EmpCtcTransaction->query("SELECT sum(tax_value) sum,tax_heads_details2 FROM `emp_tax_transactions` left join tax_heads_details on 
                        (tax_heads_details.tax_heads_details_pkey = emp_tax_transactions.tax_heads_details_fkey) WHERE `emp_fkey` = '$emp' 
                        and tax_heads_details_fkey != 0 and fin_year=$finyear group by tax_heads_details.tax_heads_fkey");
                        $sec_80_sum = 0;
                        foreach ($arr_sec_eighty as $var) {
                            $taxvalue = $var['0']['sum'];
                            $limit = $var['tax_heads_details']['tax_heads_details2'];
                            if ($taxvalue > $limit) {
                                $sec_80_sum += $limit;
                            } else {
                                $sec_80_sum += $taxvalue;
                            }
                        }
                        $value['sec_80_sum'] = $sec_80_sum;
                        //End

                        //Edited by Akshay on 8-7-2024
                        //$taxcomponents = $this->EmployeeTaxsalsum->find("all",array("conditions"=>array("emp_fkey"=>$emp,"end_date_effective is null")));
                        //$value['other_ded'] = isset($taxcomponents['0']['EmployeeTaxsalsum']['tax_heads_limitsum'])? round($taxcomponents['0']['EmployeeTaxsalsum']['tax_heads_limitsum']):0;
                        //End

                        $value['tax'] = $taxdetails;
                        if ($yearlytotal > 0) {
                            $arr_salary_for_template[$leavepolicygroupid][] = array(
                                'summary' => $value,
                                'yearlytotal' => $yearlytotal,
                                'prof' => $professional,
                                'ectc' => $arr_ectc_details,
                                'settlement' => $arr_settle_slip,
                                'std' => $arr_salary,
                                'tax_sub' => $arr_tax_sub_head
                            );
                        }
                    }

                    // debug($arr_salary_for_template);exit;
                } else {

                    $arr_gross = $this->EmpCtcTransaction->query(" SELECT
                                                                    tax_computation_report.*,
                                                                    ei.EmpName,ei.emp_pkey,emp_details.pan_no,
                                                                    ei.emp_status,ei.branch_Code,ei.branch,ei.designation,ei.department,ei.emp_id,ei.employee_id,
                                                                    te.status,te.last_approved_working_date,
                                                                    SUM(tax_computation_report.Monthly_Tax) AS total_tax_deducted,uc.user_id,
                                                                    (select option_type FROM `emp_tax_regime` WHERE end_date_effective is NULL and emp_fkey = ei.emp_pkey) as regime		  
                                                                FROM
                                                                    employee_info ei 
                                                                LEFT JOIN
                                                                    tax_computation_report ON (tax_computation_report.emp_fkey = ei.emp_pkey and tax_computation_report.modified_date IS NULL and Month_year >= '$from' and Month_year <= '$to')
                                                                LEFT JOIN
                                                                    termination te ON tax_computation_report.emp_fkey = te.emp_fkey AND te.status = 1
								LEFT JOIN user_credentials uc ON uc.emp_fkey = ei.emp_pkey
                                                                LEFT JOIN emp_details  ON emp_details.emp_pkey = ei.emp_pkey
                                                                WHERE
                                                                     ei.emp_pkey = '$leavepolicygroupid' 
                                                                    $resign_condition
                                                                    GROUP BY emp_pkey
                                                                 ORDER BY
                                                                    -- tax_computation_report.Month_year DESC
                                                                    ei.EmpName ASC
                                                                    
                                                                    ");
                    $emp = isset($value['ei']['emp_pkey']) ? $value['ei']['emp_pkey'] : '';
                    $arr_tax_sub_data = array();
                    $arr_tax_sub_head = array();
                    $taxdetails = array();
                    // if($emp){
                    $taxdetails = $this->requestAction("/Taxation/loadEmpTaxationDetails/$leavepolicygroupid");

                    foreach ($arr_tax_heads['Income'] as $tax_details_componenet) {
                        // debug($tax_details_componenet);
                        $tax_key = isset($tax_details_componenet[0]['tax_heads_fkey']) ? $tax_details_componenet[0]['tax_heads_fkey'] : 0;

                        $arr_emp_transaction = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$leavepolicygroupid/$tax_key");
                        $return_arr_emp_transaction = array();
                        ksort($arr_emp_transaction);
                        $return_arr_emp_transaction[] = array_values($arr_emp_transaction);

                        $arr_tax_sub_data[$leavepolicygroupid][$tax_key]['Income'] = $return_arr_emp_transaction;
                    }

                    foreach ($arr_tax_heads['Deductions'] as $tax_details_componenet) {
                        // debug($tax_details_componenet);
                        $tax_key = isset($tax_details_componenet[0]['tax_heads_fkey']) ? $tax_details_componenet[0]['tax_heads_fkey'] : 0;
                        $arr_emp_transaction = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$leavepolicygroupid/$tax_key");
                        $return_arr_emp_transaction = array();
                        ksort($arr_emp_transaction);
                        $return_arr_emp_transaction[] = array_values($arr_emp_transaction);

                        $arr_tax_sub_data[$leavepolicygroupid][$tax_key]['Deductions'] = $return_arr_emp_transaction;
                    }


                    foreach ($arr_tax_heads['Income'] as $key_head => $heads) {
                        foreach ($heads as $key => $head) {
                            $arr_tax_sub_head['Income'][] = isset($arr_tax_sub_data[$leavepolicygroupid][$head['tax_heads_fkey']]['Income'][0][$key]) ? $arr_tax_sub_data[$leavepolicygroupid][$head['tax_heads_fkey']]['Income'][0][$key] : 0;
                        }
                    }

                    foreach ($arr_tax_heads['Deductions'] as $key_head => $heads) {
                        foreach ($heads as $key => $head) {
                            $arr_tax_sub_head['Deductions'][] = isset($arr_tax_sub_data[$leavepolicygroupid][$head['tax_heads_fkey']]['Deductions'][0][$key]) ? $arr_tax_sub_data[$leavepolicygroupid][$head['tax_heads_fkey']]['Deductions'][0][$key] : 0;
                        }
                    }
                    //  }

                    if (!empty($arr_gross)) {
                        //Edited by Akshay on 13-6-2024
                        $arr_ectc_details = array();
                        $arr_settle_slip = array();
                        $arr_salary = array();
                        $total = 0;
                        $yearlytotal = 0;
                        foreach ($arr_sal_items as $sal_item) {
                            $item_key = isset($sal_item['emp_salary_slip']['salary_head_item_fkey']) ? $sal_item['emp_salary_slip']['salary_head_item_fkey'] : '';

                            $arr_ectc_details[] = $this->EmpCtcTransaction->query("SELECT salary_head_item_fkey, salary_head_item_desc, SUM(ROUND(structure_det_value)) AS std_availed, SUM(ROUND(salary_amount)) AS actual_availed, COUNT(*) AS row_count 
                                                                                                            FROM emp_salary_slip 
                                                                                                            WHERE emp_fkey = '$leavepolicygroupid' AND month_year >= '$from' AND month_year <= '$to'
                                                                                                            AND end_date_effective IS NULL AND item_part = 'Direct'
                                                                                                            
                                                                                                            AND salary_head_item_fkey = '$item_key'
                                                                                                            GROUP BY salary_head_item_fkey
                                                                                                          ");
                            //  debug($arr_ectc_details);
                            //AND head_operator = 'Addition'
                            // if(!$isFinYear){
                            $arr_ectc_detailsactual = $this->EmpCtcTransaction->query("SELECT SUM(ROUND(salary_amount)) AS actual_availed
                                                                                    FROM emp_salary_slip 
                                                                                    WHERE emp_fkey = '$leavepolicygroupid' AND month_year >= '$from' AND month_year <= '$to'
                                                                                    AND end_date_effective IS NULL AND item_part = 'Direct' AND salary_head_item_fkey = '$item_key' AND salary_head_item_fkey !=145");

                            $total += isset($arr_ectc_detailsactual[0][0]['actual_availed']) ? $arr_ectc_detailsactual[0][0]['actual_availed'] : 0;
                            //   }    
                            $arr_salary[] = $this->EmpCtcTransaction->query("select abs(structure_det_value) as amount, salary_head_item_desc from emp_salary_structure where emp_fkey = '$leavepolicygroupid' and end_date_effective is null and head_operator <>'Deduction' and item_part <> 'Indirect' and salary_head_item_fkey = '$item_key'");
                        }
                        $yearlytotal = $total;
                        $professional = $this->EmpCtcTransaction->query("SELECT SUM(ROUND(salary_amount)) AS actual_availed 
                                                                                    FROM emp_salary_slip ectc
                                                                                    WHERE emp_fkey = '$leavepolicygroupid' AND $fincondition
                                                                                    AND end_date_effective IS NULL AND item_part = 'Direct'
                                                                                    AND salary_head_item_desc = 'Professional Tax'
                                                                                  ");
                        $professional = isset($professional[0][0]['actual_availed']) ? $professional[0][0]['actual_availed'] : 0;
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $i = 1000;
                            foreach ($arr_settle_items as $settle_item) {
                                $settle_key = isset($settle_item['emp_settle_slip']['salary_head_item_fkey']) ? $settle_item['emp_settle_slip']['salary_head_item_fkey'] : $i;
                                $type = isset($settle_item['emp_settle_slip']['type']) ? $settle_item['emp_settle_slip']['type'] : '';
                                $arr_settle_slip[] =  $this->EmpCtcTransaction->query("SELECT emp_settle_slip.salary_head_item_fkey, emp_settle_slip.salary_head_item_desc, SUM(emp_settle_slip.structure_det_value) AS std_availed, SUM(emp_settle_slip.salary_amount) AS actual_availed 
                                                                                                                    FROM emp_settle_slip
                                                                                                                      LEFT JOIN termination ON (termination.emp_fkey = emp_settle_slip.emp_fkey and termination.status = 1)
                                                                                                                    WHERE emp_settle_slip.emp_fkey = '$leavepolicygroupid' 
                                                                                                                    AND emp_settle_slip.status = 'Y' AND emp_settle_slip.type = '$type'
                                                                                                                     and  emp_settle_slip.type != 'SALARY'
                                                                                                                    AND emp_settle_slip.approved = 'Y' 
                                                                                                                    GROUP BY type
                                                                                                                ");
                                //AND emp_settle_slip.salary_amount > 0
                                // debug($arr_settle_slip);//AND DATE_FORMAT(termination.last_approved_working_date, '%Y-%m') >= '$from' AND DATE_FORMAT(termination.last_approved_working_date, '%Y-%m') <= '$to'
                                $i++;
                            }
                            $tot = 0;
                            $prof = 0;
                            foreach ($arr_settle_slip as $item) {
                                $itemname = isset($item[0]['emp_settle_slip']['salary_head_item_desc']) ? $item[0]['emp_settle_slip']['salary_head_item_desc'] : '';
                                if ($itemname == 'Professional Tax Balance') {
                                    $prof = isset($item[0][0]['actual_availed']) ? $item[0][0]['actual_availed'] : 0;
                                } else {
                                    $tot += isset($item[0][0]['actual_availed']) ? $item[0][0]['actual_availed'] : 0;
                                }
                            }
                            $professional = $professional + $prof;
                            $yearlytotal = $yearlytotal + $tot;
                        }

                        $arr_sec_eighty = $this->EmpCtcTransaction->query("SELECT sum(tax_value) as sum,tax_heads_details2 FROM `emp_tax_transactions` left join tax_heads_details on 
                        (tax_heads_details.tax_heads_details_pkey = emp_tax_transactions.tax_heads_details_fkey) WHERE `emp_fkey` = '$leavepolicygroupid' 
                        and tax_heads_details_fkey != 0 and fin_year=$finyear group by tax_heads_details.tax_heads_fkey");
                        $sec_80_sum = 0;
                        foreach ($arr_sec_eighty as $var) {
                            $taxvalue = $var['0']['sum'];
                            $limit = $var['tax_heads_details']['tax_heads_details2'];
                            if ($taxvalue > $limit) {
                                $sec_80_sum += $limit;
                            } else {
                                $sec_80_sum += $taxvalue;
                            }
                        }
                        // $sec_80_sum = isset($arr_sec_eighty[0]['emp_tax_transactions']['sec_80_sum']) ? $arr_sec_eighty[0]['emp_tax_transactions']['sec_80_sum'] : 0;
                        $arr_gross['0']['sec_80_sum'] = $sec_80_sum;
                        //                         $taxyearly = $this->EmployeeTaxsalsum->query("select sum(availed_salary),sum(taxable_salary) 
                        //                       from emp_tax_sal_trans_new where emp_fkey = '$leavepolicygroupid' and end_date_effective is null and fin_year=$finyear");

                        //End
                        //Edited by Akshay on 8-7-2024
                        //                        $taxcomponents = $this->EmployeeTaxsalsum->find("all",array("conditions"=>array("emp_fkey"=>$leavepolicygroupid,"end_date_effective is null")));
                        //                        $value['other_ded'] = isset($taxcomponents['0']['EmployeeTaxsalsum']['tax_heads_limitsum'])? round($taxcomponents['0']['EmployeeTaxsalsum']['tax_heads_limitsum']):0;

                        //End
                        $arr_gross['0']['tax'] = $taxdetails;
                        if ($yearlytotal > 0) {
                            $arr_salary_for_template[] = array(
                                'summary' => $arr_gross,
                                'yearlytotal' => $yearlytotal,
                                'prof' => $professional,
                                'ectc' => $arr_ectc_details,
                                'settlement' => $arr_settle_slip,
                                'std' => $arr_salary,
                                'tax_sub' => $arr_tax_sub_head
                            );
                        }
                    }
                    //  debug($arr_salary_for_template);
                }
            }
        }

        // debug($arr_salary_for_template);exit;

        //$this->set('keys', $arr_keys);
        //$this->set('array_key', $array_key);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $this->set('arr_taxheadfields', $arr_taxheadfields);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $user_id = $this->Session->read('login_user_id');
        $this->set('user_id', $user_id);
        $date_time = date('d-m-Y H:i');
        $this->set('date_time', $date_time);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $user_name = $this->Session->read('user_name');

        $this->set('user_name', $user_name);

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $this->set('month', $from);
        //Edited by Akshay on 12-6-2024
        $fromMonthYear =  date('M-Y', strtotime($arr_form_data['reportsfrom']));
        $toMonthYear = date('M-Y', strtotime($arr_form_data['reportsto']));
        $this->set('fromMonthYear', $fromMonthYear);
        $this->set('toMonthYear', $toMonthYear);
        //End
        $from1 = date('d-m-Y', strtotime($arr_form_data['reportsfrom']));
        $to1 = date('d-m-Y', strtotime($arr_form_data['reportsto']));
        $this->set('from1', $from1);
        $this->set('to1', $to1);

        //Edited by Akshay on 13-6-2024
        $this->set('arr_sal_items', $arr_sal_items);
        $this->set('arr_settle_items', $arr_settle_items);
        //End

        //Edited by Akshay on 22-6-2024
        function spaceForHeading($string)
        {
            return $spaces = str_repeat(' ', strlen($string));
        }
        //End

        switch ($mode) {


            case 'excel':

                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_TaxDetails_" . $from1 . '  -  ' . $to1 . ".xlsx" : "TaxDetailed_" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(4, 1, "TAX Details - " . $fromMonthYear . "  -  "  . $toMonthYear);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 1)->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->freezePane('E6');
                //Find last column
                // $number5 = count($arr_taxheadfields) + 62;

                $number5 = count(isset($arr_tax_heads['Deductions']) ? $arr_tax_heads['Deductions'] : 0) + count(isset($arr_tax_heads['Income']) ? $arr_tax_heads['Income'] : 0) + 138; //Edited by Akshay on 21-6-2024
                $columnName = '';
                while ($number5 > 0) {
                    $remainder = ($number5 - 1) % 26;
                    $columnName = chr(65 + $remainder) . $columnName;
                    $number5 = intval(($number5 - $remainder) / 26);
                }
                // debug($columnName);exit;
                for ($col = 'A'; $col !== $columnName; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(false);
                }

                $worksheet->mergeCells('E1:F1');
                $worksheet->getStyle('E1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(4, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 2)->getFont()->setSize(11);
                $objPHPExcel->getActiveSheet()
                    ->getStyle('E2')
                    ->getFont()
                    ->getColor()
                    ->setRGB('FF0000');
                $worksheet->mergeCells('E2:F2');
                $worksheet->getStyle('E2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('M2:AH2');
                $worksheet->mergeCells('M1:AH1');

                if (!empty($arr_salary_for_template)) {
                    $rowcount = 3;
                    function num3alpha($n)
                    {
                        $r = '';
                        for ($i = 1; $n >= 0 && $i < 10; $i++) {
                            $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
                            $n -= pow(26, $i);
                        }
                        return $r;
                    }
                    //Function to add column name
                    function addNumberToColumnName($existingColumnName, $numberToAdd)
                    {
                        $columnNumber = 0;
                        $length = strlen($existingColumnName);

                        for ($i = 0; $i < $length; $i++) {
                            $columnNumber *= 26;
                            $columnNumber += ord($existingColumnName[$i]) - 64;
                        }

                        $resultColumnNumber = $columnNumber + $numberToAdd;

                        $resultColumnName = '';
                        while ($resultColumnNumber > 0) {
                            $remainder = ($resultColumnNumber - 1) % 26;
                            $resultColumnName = chr(65 + $remainder) . $resultColumnName;
                            $resultColumnNumber = intval(($resultColumnNumber - $remainder) / 26);
                        }

                        return $resultColumnName;
                    }

                    $inc_count = isset($arr_taxheadfields['Income']['tax_heads']) ? count($arr_taxheadfields['Income']['tax_heads']) : 0;
                    $ded_count = isset($arr_taxheadfields['Deductions']['tax_heads']) ? count($arr_taxheadfields['Deductions']['tax_heads']) : 0;
                    $tax_ded_start_col = trim(addNumberToColumnName('R', $inc_count + 1));
                    $income_tax = trim(addNumberToColumnName($tax_ded_start_col, $ded_count + 1));
                    $tax_slab_new = trim(addNumberToColumnName($income_tax, 1));
                    $tax_rate_new2 = trim(addNumberToColumnName($income_tax, 6));
                    $tax_rate_new = trim(addNumberToColumnName($tax_rate_new2, 1));
                    $tax_rate_new1 = trim(addNumberToColumnName($tax_rate_new2, 7));
                    $tax_slab_old = trim(addNumberToColumnName($tax_rate_new, 7));
                    $tax_rate_old = trim(addNumberToColumnName($tax_slab_old, 5));
                    $tax_details = trim(addNumberToColumnName($tax_rate_old, 5));
                    //Edited  by Akshay on 15-6-2024
                    $salary_details = trim(addNumberToColumnName('J', count($arr_sal_items) - 1));
                    //End
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $rowcount, 'Employee Details')
                        ->setCellValue('J' . $rowcount, 'Salary Details');
                    $boldFont = $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':' . $tax_details . $rowcount)->getFont();
                    $boldFont->setBold(true);

                    $tax_ded_start_col2 = trim(addNumberToColumnName($salary_details, 5));
                    $next2 = trim(addNumberToColumnName($tax_ded_start_col, $ded_count));
                    $tax_slab_new2 = trim(addNumberToColumnName($income_tax, 1));

                    $tax_slab_old2 = trim(addNumberToColumnName($tax_rate_new, 6));
                    $tax_rate_old2 = trim(addNumberToColumnName($tax_slab_old, 4));
                    $tax_details2 = trim(addNumberToColumnName($tax_rate_old, 4));
                    $last_column = trim(addNumberToColumnName($tax_details, 6));
                    $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $worksheet->mergeCells('K' . $rowcount . ':' . $salary_details . $rowcount); //Salary Details
                    $worksheet->getStyle('K' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    // $worksheet->mergeCells(trim(addNumberToColumnName($salary_details, 5)) . $rowcount . ':' . $tax_ded_start_col2 . $rowcount); // Tax Head - Income
                    $worksheet->getStyle('R' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    );

                    $rowcount = 4;
                    for ($column_merge = 0; $column_merge < 9; $column_merge++) {
                        $objPHPExcel->getActiveSheet()->mergeCells(PHPExcel_Cell::stringFromColumnIndex($column_merge) . $rowcount . ':' . PHPExcel_Cell::stringFromColumnIndex($column_merge) . ($rowcount + 1));
                    }

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'User ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Termination Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
                    //Edited by Akshay on 12-7-2024
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'PAN No');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
                    //End
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Opted By');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
                    //Edited by Akshay on 14-6-2024
                    $colmn = 9;
                    //Edited by Akshay on 21-6-2024
                    for ($column_merge = $colmn; $column_merge <= $colmn + count($arr_sal_items) + count($arr_settle_items); $column_merge++) {
                        $objPHPExcel->getActiveSheet()->mergeCells(PHPExcel_Cell::stringFromColumnIndex($column_merge) . ($rowcount) . ':' . PHPExcel_Cell::stringFromColumnIndex($column_merge) . ($rowcount + 1));
                    }
                    //End
                    foreach ($arr_sal_items as $col_no => $sal_item) {
                        $item = isset($sal_item['emp_salary_slip']['salary_head_item_desc']) ? $sal_item['emp_salary_slip']['salary_head_item_desc'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10 + $col_no) . $rowcount, $item);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10 + $col_no, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getColumnDimension(num3alpha(10 + $col_no))->setAutoSize(false);
                    }
                    $column = $colmn + count($arr_sal_items) + 1;
                    // debug($column); 
                    foreach ($arr_settle_items as $col_no => $set_item) {
                        $settle_item = isset($set_item['emp_settle_slip']['type']) ? $set_item['emp_settle_slip']['type'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column + $col_no) . $rowcount, $settle_item);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + $col_no, $rowcount)->getFont()->setBold(true);
                    }

                    $column = $column + count($arr_settle_items);
                    // debug($column);
                    //End
                    //Edited by Akshay on 21-6-2024
                    // Merging cells for each header column
                    for ($column_merge = $column; $column_merge < $column + 4; $column_merge++) {
                        $objPHPExcel->getActiveSheet()->mergeCells(PHPExcel_Cell::stringFromColumnIndex($column_merge) . ($rowcount - 1) . ':' . PHPExcel_Cell::stringFromColumnIndex($column_merge) . ($rowcount + 1));
                    }

                    // Setting cell values and styles for the headers
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount - 1), 'Availed Salary Yearly');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, ($rowcount - 1))->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount - 1), 'Professional Tax');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, ($rowcount - 1))->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount - 1), 'Taxable Income from Salary');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, ($rowcount - 1))->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount - 1), 'Standard Deduction');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, ($rowcount - 1))->getFont()->setBold(true);
                    $column++;

                    // Merging cells for 'Total Taxable Income' and setting value and styles
                    $objPHPExcel->getActiveSheet()->mergeCells(PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount - 1) . ':' . PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount + 1));
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount - 1), 'Total Taxable Income');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, ($rowcount - 1))->getFont()->setBold(true);
                    $column++;


                    $tax_head_income_start = $column;
                    foreach ($arr_tax_heads['Income'] as $key_head => $heads) {
                        $col_main_tax_head_income_start = $column;
                        if (count($heads) > 0)
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col_main_tax_head_income_start) . ($rowcount),  $key_head);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col_main_tax_head_income_start, ($rowcount))->getFont()->setBold(true);
                        foreach ($heads as $key => $head) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount + 1),  $head['fieldName']);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, ($rowcount + 1))->getFont()->setBold(true);
                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setAutoSize(true);
                            $column++;
                        }
                        $col_main_tax_head_income_end = $column - 1;

                        // Main Tax head
                        if ($col_main_tax_head_income_end > $col_main_tax_head_income_start) {
                            $worksheet->mergeCells(num3alpha($col_main_tax_head_income_start) . ($rowcount) . ':' . num3alpha($col_main_tax_head_income_end) . ($rowcount));
                        }
                    }
                    $tax_head_income_end = $column - 1;
                    //Tax-head income
                    if ($tax_head_income_start < $tax_head_income_end) {
                        $worksheet->mergeCells(num3alpha($tax_head_income_start) . ($rowcount - 1) . ':' . num3alpha($tax_head_income_end) . ($rowcount - 1)); //Tax details
                        $worksheet->getStyle(num3alpha($tax_head_income_start) . ($rowcount - 1))->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($tax_head_income_start) . ($rowcount - 1), 'Tax Head - Income');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($tax_head_income_start, ($rowcount - 1))->getFont()->setBold(true);
                    }
                    //End
                    $tax_head_deduction_start = $column;
                    foreach ($arr_tax_heads['Deductions'] as $key_head => $heads) {
                        $col_main_tax_head_deduction_start = $column;
                        if (count($heads) > 0) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col_main_tax_head_deduction_start) . ($rowcount),  $key_head);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col_main_tax_head_deduction_start, ($rowcount))->getFont()->setBold(true);
                        }
                        foreach ($heads as $key => $head) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . ($rowcount + 1), $head['fieldName']);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, ($rowcount + 1))->getFont()->setBold(true);
                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setAutoSize(true);
                            $column++;
                        }
                        $col_main_tax_head_deduction_end = $column - 1;
                        if ($col_main_tax_head_deduction_end > $col_main_tax_head_deduction_start) {
                            $worksheet->mergeCells(num3alpha($col_main_tax_head_deduction_start) . ($rowcount) . ':' . num3alpha($col_main_tax_head_deduction_end) . ($rowcount));
                        }
                    }
                    $tax_head_deduction_end = $column - 1;
                    //Tax-head income
                    if ($tax_head_deduction_start < $tax_head_deduction_end) {
                        $worksheet->mergeCells(num3alpha($tax_head_deduction_start) . ($rowcount - 1) . ':' . num3alpha($tax_head_deduction_end) . ($rowcount - 1)); //Tax details
                        $worksheet->getStyle(num3alpha($tax_head_deduction_start) . ($rowcount - 1))->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($tax_head_deduction_start) . ($rowcount - 1), 'Tax Head - Deductions');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($tax_head_deduction_start, ($rowcount - 1))->getFont()->setBold(true);
                    }

                    for ($column_merge = $column; $column_merge < $column + 31; $column_merge++) {
                        $objPHPExcel->getActiveSheet()->mergeCells(PHPExcel_Cell::stringFromColumnIndex($column_merge) . $rowcount . ':' . PHPExcel_Cell::stringFromColumnIndex($column_merge) . ($rowcount + 1));
                    }
                    //End
                    $income_tax_slab_start = $column;
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Up to Rs.3,00,000');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 1, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.3,00,000 - Rs.6,00,000');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 2, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.6,00,000 - Rs.9,00,000');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 3, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.9,00,000 - Rs.12,00,000');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.12,00,000 - Rs.15,00,000');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.15,00,000');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 6, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column++) . $rowcount, 'Total Income');
                    $income_tax_slab_end = $column - 1;
                    //Edited by Akshay on 19-6-2024
                    $worksheet->mergeCells(num3alpha($income_tax_slab_start) . ($rowcount - 1) . ':' . num3alpha($income_tax_slab_end) . ($rowcount - 1)); //Tax details
                    $worksheet->getStyle(num3alpha($income_tax_slab_start) . ($rowcount - 1))->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($income_tax_slab_start) . ($rowcount - 1), 'Income Tax Slab -New Regime');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($income_tax_slab_start, ($rowcount - 1))->getFont()->setBold(true);
                    //End

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 7, $rowcount)->getFont()->setBold(true);

                    $tax_rate_start = $column;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Up to Rs.3,00,000 (Nil)');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 8, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column++) . $rowcount, 'Above Rs.3 lakh - Rs.6 lakh 5%');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 9, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.6 lakh - Rs.9 lakh  10%');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column + 10, $rowcount)->getFont()->setBold(true);

                    // $column = $column + 10;
                    // $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.9 lakh - Rs.12 lakh 15%');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.12 lakh - Rs.15 lakh 20%');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.15 lakh 30%');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Total Tax');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    //Edited by Akshay on 19-6-2024
                    $tax_rate_end = $column;
                    $worksheet->mergeCells(num3alpha($tax_rate_start) . ($rowcount - 1) . ':' . num3alpha($tax_rate_end) . ($rowcount - 1)); //Tax details
                    $worksheet->getStyle(num3alpha($tax_rate_start) . ($rowcount - 1))->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($tax_rate_start) . ($rowcount - 1), 'Income Tax Slab -New Regime');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($tax_rate_start, ($rowcount - 1))->getFont()->setBold(true);
                    //End

                    $column++;
                    $slab_old_regime_start = $column;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Up to Rs.2,50,000');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.2,50,000 - Rs.5,00,000');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.5,00,000 - Rs.10,00,000');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.10,00,000');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Total Income');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);

                    //Edited by Akshay on 19-6-2024
                    $slab_old_regime_end = $column;
                    $worksheet->mergeCells(num3alpha($slab_old_regime_start) . ($rowcount - 1) . ':' . num3alpha($slab_old_regime_end) . ($rowcount - 1)); //Tax details
                    $worksheet->getStyle(num3alpha($slab_old_regime_start) . ($rowcount - 1))->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($slab_old_regime_start) . ($rowcount - 1), 'Income Tax Slab- Old Regime ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($slab_old_regime_start, ($rowcount - 1))->getFont()->setBold(true);
                    //End
                    $column++;
                    $tax_old_regime_start = $column; //Edited by Akshay on 19-6-2024
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Up to Rs.2,50,000 (Nil)');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.2.5 lakh - Rs.5 lakh - 5%');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.5 lakh - Rs.10 lakh - 20%');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Above Rs.10 lakh - 30%');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Total Tax');
                    //Edited by Akshay on 19-6-2024
                    $tax_old_regime_end = $column;
                    $worksheet->mergeCells(num3alpha($tax_old_regime_start) . ($rowcount - 1) . ':' . num3alpha($tax_old_regime_end) . ($rowcount - 1)); //Tax details
                    $worksheet->getStyle(num3alpha($tax_old_regime_start) . ($rowcount - 1))->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($tax_old_regime_start) . ($rowcount - 1), 'Tax Rate - Old Regime ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($tax_old_regime_start, ($rowcount - 1))->getFont()->setBold(true);
                    //End

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $column++;

                    $tax_details_start = $column; //Edited by Akshay on 19-6-2024
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Cess');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Surcharge');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Rebate');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Total');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Tax Deducted');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Balance Tax');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                    $column++;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . $rowcount, 'Monthly Tax');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $rowcount)->getFont()->setBold(true);
                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($column);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);

                    // $objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column))->setWidth(15); //Edited by Akshay on 7-9-2023
                    //Edited by Akshay on 19-6-2024
                    $tax_details_end = $column;
                    $worksheet->mergeCells(num3alpha($tax_details_start) . ($rowcount - 1) . ':' . num3alpha($tax_details_end) . ($rowcount - 1)); //Tax details
                    $worksheet->getStyle(num3alpha($tax_details_start) . ($rowcount - 1))->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($tax_details_start) . ($rowcount - 1), 'Tax Details ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($tax_details_start, ($rowcount - 1))->getFont()->setBold(true);
                    //End
                    $column++;
                    $rowcount = $rowcount + 1;
                    $arr_total = array();
                    $j = 1;
                    //debug($arr_salary_for_template);exit();
                    if (isset($needBranchWiseReport) && $needBranchWiseReport != 1) {
                        $rowcount = $rowcount + 1;
                        foreach ($arr_salary_for_template as $value) {
                            //  debug($val);exit();
                            if (count($value) > 0) {
                                $col = 0;
                                $val = $value['summary']['0'];
                                $employees = $value;
                                $pan = isset($val['emp_details']['pan_no']) ? $val['emp_details']['pan_no'] : '';
                                $regime = trim($val['0']['regime']);
                                // debug($val['tax_computation_report']); exit;
                                if ($regime == 'N') {
                                    $reg = 'New Regime';
                                    $hraexemption = 0;
                                    $investment = 0;
                                    $deductions_pt = 0;
                                }
                                if ($regime == 'O') {
                                    $reg = 'Old Regime';

                                    $deductions_pt = isset($val['tax_computation_report']['Availed_Salary_Yearly']) ? $val['tax_computation_report']['Availed_Salary_Yearly'] - $val['tax_computation_report']['Taxable_Income_from_Salary'] : 0;
                                    $hraexemption = isset($val['tax_computation_report']['Hra_exemption']) ? $val['tax_computation_report']['Hra_exemption'] : 0;
                                    $investment = isset($val['tax_computation_report']['Investments_Other_Deductions']) ? $val['tax_computation_report']['Investments_Other_Deductions'] : 0;
                                }
                                $reg = isset($reg) ? $reg : 'New Regime';
                                $id = $val['ei']['emp_id'];
                                $c_id = $val['ei']['employee_id'];
                                $name = $val['0']['EmpName'];
                                $user_id = $val['uc']['user_id'];
                                $empstatus = (isset($val['ei']['emp_status'])) && $val['ei']['emp_status'] == "2" ? '(Resigned)' : '';
                                $branch = $val['ei']['branch'];
                                $department = $val['ei']['department'];
                                $designation = $val['ei']['designation'];
                                $termination = $val['te']['last_approved_working_date'];
                                $date = !empty($termination) ? (new DateTime($termination))->format('d-m-Y') : '';
                                $mon = $from1 . $to1;
                                $deduction_invest = isset($val['tax_computation_report']['Investments_Other_Deductions']) ? $val['tax_computation_report']['Investments_Other_Deductions'] : 0;
                                $deduction_std = isset($val['tax_computation_report']['Standard_deduction']) ? $val['tax_computation_report']['Standard_deduction'] : 0;
                                $deductiontotal = $deduction_invest + $deduction_std;
                                $m_sal = isset($val['tax_computation_report']['Monthly_Salary']) ? $val['tax_computation_report']['Monthly_Salary'] : 0;
                                //$avail_sal = isset($val['0']['total_availed_salary']) ? $val['0']['total_availed_salary'] : 0;
                                //debug($avail_sal);die();

                                $Taxable_Income_from_Salary = isset($val['tax_computation_report']['Taxable_Income_from_Salary']) ? $val['tax_computation_report']['Taxable_Income_from_Salary'] : 0;

                                $Taxable_Income_from_Other_Sources = isset($val['tax_computation_report']['Taxable_Income_from_Other_Sources']) ? $val['tax_computation_report']['Taxable_Income_from_Other_Sources'] : 0;

                                $Investments_Other_Deductions = isset($val['tax_computation_report']['Investments_Other_Deductions']) ? $val['tax_computation_report']['Investments_Other_Deductions'] : 0;

                                $Standard_deduction = isset($val['tax_computation_report']['Standard_deduction']) ? $val['tax_computation_report']['Standard_deduction'] : 0;

                                $Total_Taxable_Income = isset($val['tax_computation_report']['Total_Taxable_Income']) ? $val['tax_computation_report']['Total_Taxable_Income'] : 0;


                                $Upto = isset($val['tax_computation_report']['Up_to_first_income']) ? $val['tax_computation_report']['Up_to_first_income'] : 0;
                                $above1 = isset($val['tax_computation_report']['Above_first_income']) ? $val['tax_computation_report']['Above_first_income'] : 0;
                                $above2 = isset($val['tax_computation_report']['Above_Secnd_income']) ? $val['tax_computation_report']['Above_Secnd_income'] : 0;
                                $above3 = isset($val['tax_computation_report']['Above_third_income']) ? $val['tax_computation_report']['Above_third_income'] : 0;
                                $above4 = isset($val['tax_computation_report']['Above_fourth_income']) ? $val['tax_computation_report']['Above_fourth_income'] : 0;
                                $above5 = isset($val['tax_computation_report']['Above_fifth_income']) ? $val['tax_computation_report']['Above_fifth_income'] : 0;
                                $above6 = isset($val['tax_computation_report']['Above_sixth_income']) ? $val['tax_computation_report']['Above_sixth_income'] : 0;
                                $TotalIncome = isset($val['tax_computation_report']['Total_Income']) ? $val['tax_computation_report']['Total_Income'] : 0;

                                $above1_slab = isset($val['tax_computation_report']['Above_first_slab']) ? $val['tax_computation_report']['Above_first_slab'] : 0;
                                $above2_slab = isset($val['tax_computation_report']['Above_second_slab']) ? $val['tax_computation_report']['Above_second_slab'] : 0;
                                $above3_slab = isset($val['tax_computation_report']['Above_third_slab']) ? $val['tax_computation_report']['Above_third_slab'] : 0;
                                $above4_slab = isset($val['tax_computation_report']['Above_fourth_slab']) ? $val['tax_computation_report']['Above_fourth_slab'] : 0;
                                $above5_slab = isset($val['tax_computation_report']['Above_fifth_slab']) ? $val['tax_computation_report']['Above_fifth_slab'] : 0;
                                $above6_slab = isset($val['tax_computation_report']['Above_sixth_slab']) ? $val['tax_computation_report']['Above_sixth_slab'] : 0;
                                $deductions = isset($val['tax_computation_report']['Deductions']) ? $val['tax_computation_report']['Deductions'] : 0;
                                $total_tax = isset($val['tax_computation_report']['Total_Tax']) ? $val['tax_computation_report']['Total_Tax'] : 0;
                                $availed_salary_yearly = isset($val['tax_computation_report']['Availed_Salary_Yearly']) ? $val['tax_computation_report']['Availed_Salary_Yearly'] : 0;
                                $cess = isset($val['tax_computation_report']['Cess']) ? $val['tax_computation_report']['Cess'] : 0;
                                $Surcharge = isset($val['tax_computation_report']['Surcharge']) ? $val['tax_computation_report']['Surcharge'] : 0;
                                $rebate = isset($val['tax_computation_report']['Rebate']) ? $val['tax_computation_report']['Rebate'] : 0;
                                $total = isset($val['tax_computation_report']['Total']) ? $val['tax_computation_report']['Total'] : 0;
                                $Tax_Deducted = isset($val['0']['total_tax_deducted']) ? $val['0']['total_tax_deducted'] : 0;
                                $bal_tax = $total - $Tax_Deducted;
                                //$bal_tax = isset($val['tax_computation_report']['Balance_Tax']) ? $val['tax_computation_report']['Balance_Tax'] : 0;
                                $monthly_tax = isset($val['tax_computation_report']['Monthly_Tax']) ? $val['tax_computation_report']['Monthly_Tax'] : 0;

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '   ' . $j . '   ');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '      ' . $c_id . '     ');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '     ' . $user_id . '     ');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '       ' . $name . ' ' . $empstatus . '       ');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '        ' . $branch . '        ');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '         ' . $department . '         ');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '      ' . $designation . '      ');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '        ' . $date . '        ');
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '    ' . $pan . '    '); //Edited by Akshay on 12-7-2024
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '    ' . $reg . '    ');

                                // debug($col + 8); exit;
                                //Edited by Askshay on 15-6-2024
                                $col = $col + 10;
                                // debug($col); exit;
                                $arr_sal_slip = isset($value['ectc']) ? $value['ectc'] : array();
                                $arr_salary = isset($value['std']) ? $value['std'] : array();
                                $arr_settle_slip = isset($value['settlement']) ? $value['settlement'] : array();
                                $availed_sal = 0;
                                foreach ($arr_sal_slip as $id => $salary_item) {
                                    // if ($isFinYear) {
                                    //     $ectc_val = isset($salary_item[0][0]['actual_availed']) ?
                                    //         $salary_item[0][0]['actual_availed'] : (isset($arr_salary[$id][0][0]['amount']) ?
                                    //             $arr_salary[$id][0][0]['amount'] :
                                    //             0);
                                    // } else {
                                    $ectc_val = isset($salary_item[0][0]['actual_availed']) ?
                                        $salary_item[0][0]['actual_availed'] : 0;
                                    //  }

                                    $avl_count = isset($salary_item[0][0]['row_count']) ? $salary_item[0][0]['row_count'] : 0;
                                    $std_value = isset($arr_salary[$id][0][0]['amount']) ? $arr_salary[$id][0][0]['amount'] : 0;
                                    //if ($isFinYear) {
                                    //    $ectc_val += ($month_count - $avl_count) * $std_value;
                                    //  }


                                    $head_item = isset($salary_item[0]['emp_salary_slip']['salary_head_item_desc']) ? $salary_item[0]['emp_salary_slip']['salary_head_item_desc'] : '                             ';
                                    $spaces = str_repeat(' ', strlen($head_item));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $id) . $rowcount, $ectc_val);
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col + $id);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(strlen($head_item) + 14);
                                    $arr_total[$col + $id] = isset($arr_total[$col + $id]) ? $arr_total[$col + $id] + $ectc_val : $ectc_val;
                                    //Edited by Akshay on 5-7-2024
                                    $item_desc = isset($salary_item[0]['emp_salary_slip']['salary_head_item_desc']) ? trim($salary_item[0]['emp_salary_slip']['salary_head_item_desc']) : '';
                                    if ($item_desc != 'Uniform Allowance') {
                                        $availed_sal += $ectc_val;
                                    }
                                    //End
                                }

                                $col = $col + count($arr_sal_slip);
                                foreach ($arr_settle_slip as $id =>  $settle_item) {
                                    //if($isFinYear){
                                    // $ectc_val = isset($settle_item[0][0]['actual_availed']) ?
                                    //     $settle_item[0][0]['actual_availed'] : (isset($settle_item[0][0]['std_availed']) ?
                                    //         $settle_item[0][0]['std_availed'] :
                                    //         0);
                                    //                                    }else{
                                    $ectc_val = isset($settle_item[0][0]['actual_availed']) ?
                                        $settle_item[0][0]['actual_availed'] : 0;
                                    //                                    }

                                    $head_item = isset($salary_item[0]['emp_settle_slip']['salary_head_item_desc']) ? $salary_item[0]['emp_settle_slip']['salary_head_item_desc'] : '                             ';
                                    $spaces = str_repeat(' ', strlen($head_item));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $id) . $rowcount, $ectc_val);
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col + $id);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(strlen($head_item) + 14);
                                    $arr_total[$col + $id] = isset($arr_total[$col + $id]) ? $arr_total[$col + $id] + $ectc_val : $ectc_val;

                                    $availed_sal += $ectc_val;
                                }
                                $col = $col + count($arr_settle_slip);
                                // debug($col);
                                // debug($availed_sal);
                                // exit;
                                // $availed_sal = $availed_salary_yearly;
                                //Edited by Akshay on 6-7-2024
                                // if (!$isFinYear) {
                                $availed_sal = $value['yearlytotal'];
                                //  }
                                //End
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $availed_sal);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $availed_sal : $availed_sal;
                                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                $col++;

                                $prof_tax = ($value['prof']) ? abs($value['prof']) : 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $prof_tax);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $prof_tax : $prof_tax;
                                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                $col++;
                                if ($regime == 'OLD') {
                                    $taxable_income = ($availed_sal - $prof_tax);
                                } else {
                                    $taxable_income = $availed_sal;
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $taxable_income);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $taxable_income : $taxable_income;
                                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                $col++;

                                if ($taxable_income > 0) {
                                    $std_ded = 50000;
                                } else {
                                    $std_ded = 0;
                                }

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $std_ded);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $std_ded : $std_ded;
                                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                $col++;
                                if ($regime == 'OLD') {
                                    $sec_80_sum = isset($val['sec_80_sum']) ? $val['sec_80_sum'] : 0;
                                    $taxable_income = $taxable_income - $std_ded - $sec_80_sum;
                                } else {
                                    $taxable_income = $taxable_income - $std_ded;
                                }

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, ($taxable_income));
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + ($taxable_income) : ($taxable_income);
                                $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                $data_centre_align_end = $col;
                                $col++;

                                foreach ($employees['tax_sub']['Income'] as $tax_value) {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $tax_value);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $tax_value : $tax_value;
                                    $col++;
                                }
                                foreach ($employees['tax_sub']['Deductions'] as $tax_value) {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $tax_value);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $tax_value : $tax_value;
                                    $col++;
                                }
                                //End
                                $data_centre_align_start = $col;

                                $spaces = '                              ';
                                if ($regime == 'NEW') {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above1);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above1 : $above1;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above2);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above2 : $above2;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above3);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above3 : $above3;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above4);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above4 : $above4;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above5);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above5 : $above5;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above6);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above6 : $above6;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $TotalIncome);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $TotalIncome : $TotalIncome;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                } else {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                }

                                if ($regime == 'NEW') {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above1_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above1_slab : $above1_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount,  $above2_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above2_slab : $above2_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above3_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above3_slab : $above3_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above4_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above4_slab : $above4_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above5_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above5_slab : $above5_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above6_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above6_slab : $above6_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $total_tax);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $total_tax : $total_tax;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                } else {

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount,  0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                }

                                //Old regime
                                if ($regime == 'OLD') {
                                    if ($Total_Taxable_Income > 250000) {
                                        $firsttax = 250000;
                                        // $TotalIncome = $TotalIncome + 250000;
                                    } else {
                                        $firsttax = $Total_Taxable_Income;
                                    }

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $firsttax);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $firsttax : $firsttax;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above1);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above1 : $above1;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above2);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above2 : $above2;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above3);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above3 : $above3;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $Total_Taxable_Income);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $Total_Taxable_Income : $Total_Taxable_Income;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                } else {

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                }

                                if ($regime == 'OLD') {

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount,  $above1_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above1_slab : $above1_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above2_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above2_slab : $above2_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above3_slab);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above3_slab : $above3_slab;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $total_tax);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $total_tax : $total_tax;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                } else {

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount,  0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                    $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                    $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                    $col++;
                                }
                                // $col--;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $cess);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $cess : $cess;
                                $col++;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $Surcharge);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $Surcharge : $Surcharge;
                                $col++;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $rebate);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $rebate : $rebate;
                                $col++;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $total);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $total : $total;
                                $col++;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $Tax_Deducted);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $Tax_Deducted : $Tax_Deducted;
                                $col++;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $bal_tax);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $bal_tax : $bal_tax;
                                $col++;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $monthly_tax);
                                $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $monthly_tax : $monthly_tax;
                            }
                            $j++;
                            $rowcount++;
                        }
                    } else if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) {
                        $j = 1;
                        $rowcount++;

                        foreach ($arr_salary_for_template as $values) {

                            if (count($values) !== 0 && !empty($values[0]['summary'])) {
                                foreach ($values as  $employees => $value) {
                                    $val = $value['summary'];
                                    //   debug($val);exit;
                                    if (count($val) > 0) {
                                        $col = 0;

                                        $employees = $value;
                                        $regime = isset($val['0']['regime']) ? trim($val['0']['regime']) : 'NEW';
                                        $pan = isset($val['emp_details']['pan_no']) ? $val['emp_details']['pan_no'] : ''; //Edited by Akshay on 12-7-2024
                                        if ($regime == 'N') {
                                            $reg = 'New Regime';
                                            $hraexemption = 0;
                                            $investment = 0;
                                            $deductions_pt = 0;
                                        }
                                        if ($regime == 'O') {
                                            $reg = 'Old Regime';

                                            $deductions_pt = isset($val['tax_computation_report']['Availed_Salary_Yearly']) ? $val['tax_computation_report']['Availed_Salary_Yearly'] - $val['tax_computation_report']['Taxable_Income_from_Salary'] : 0;
                                            $hraexemption = isset($val['tax_computation_report']['Hra_exemption']) ? $val['tax_computation_report']['Hra_exemption'] : 0;
                                            $investment = isset($val['tax_computation_report']['Investments_Other_Deductions']) ? $val['tax_computation_report']['Investments_Other_Deductions'] : 0;
                                        }
                                        $reg = isset($reg) ? $reg : 'New Regime'; //Edited by Akshay on 27-6-2024
                                        $id = $val['ei']['emp_id'];
                                        $c_id = $val['ei']['employee_id'];
                                        $user_id = $val['uc']['user_id'];
                                        $name = $val['ei']['EmpName'];
                                        $empstatus = (isset($val['ei']['emp_status'])) && $val['ei']['emp_status'] == "2" ? '(Resigned)' : '';
                                        $branch = $val['ei']['branch'];
                                        $department = $val['ei']['department'];
                                        $designation = $val['ei']['designation'];
                                        $termination = $val['te']['last_approved_working_date'];
                                        $date = !empty($termination) ? (new DateTime($termination))->format('d-m-Y') : '';
                                        $mon = $from1 . $to1;
                                        $deduction_invest = isset($val['tax_computation_report']['Investments_Other_Deductions']) ? $val['tax_computation_report']['Investments_Other_Deductions'] : 0;
                                        $deduction_std = isset($val['tax_computation_report']['Standard_deduction']) ? $val['tax_computation_report']['Standard_deduction'] : 0;
                                        $deductiontotal = $deduction_invest + $deduction_std;
                                        $m_sal = isset($val['tax_computation_report']['Monthly_Salary']) ? $val['tax_computation_report']['Monthly_Salary'] : 0;
                                        //$avail_sal = isset($val['0']['total_availed_salary']) ? $val['0']['total_availed_salary'] : 0;
                                        //debug($avail_sal);die();

                                        $Taxable_Income_from_Salary = isset($val['tax_computation_report']['Taxable_Income_from_Salary']) ? $val['tax_computation_report']['Taxable_Income_from_Salary'] : 0;

                                        $Taxable_Income_from_Other_Sources = isset($val['tax_computation_report']['Taxable_Income_from_Other_Sources']) ? $val['tax_computation_report']['Taxable_Income_from_Other_Sources'] : 0;

                                        $Investments_Other_Deductions = isset($val['tax_computation_report']['Investments_Other_Deductions']) ? $val['tax_computation_report']['Investments_Other_Deductions'] : 0;

                                        $Standard_deduction = isset($val['tax_computation_report']['Standard_deduction']) ? $val['tax_computation_report']['Standard_deduction'] : 0;

                                        $Total_Taxable_Income = isset($val['tax_computation_report']['Total_Taxable_Income']) ? $val['tax_computation_report']['Total_Taxable_Income'] : 0;


                                        $Upto = isset($val['tax_computation_report']['Up_to_first_income']) ? $val['tax_computation_report']['Up_to_first_income'] : 0;
                                        $above1 = isset($val['tax_computation_report']['Above_first_income']) ? $val['tax_computation_report']['Above_first_income'] : 0;
                                        $above2 = isset($val['tax_computation_report']['Above_Secnd_income']) ? $val['tax_computation_report']['Above_Secnd_income'] : 0;
                                        $above3 = isset($val['tax_computation_report']['Above_third_income']) ? $val['tax_computation_report']['Above_third_income'] : 0;
                                        $above4 = isset($val['tax_computation_report']['Above_fourth_income']) ? $val['tax_computation_report']['Above_fourth_income'] : 0;
                                        $above5 = isset($val['tax_computation_report']['Above_fifth_income']) ? $val['tax_computation_report']['Above_fifth_income'] : 0;
                                        $above6 = isset($val['tax_computation_report']['Above_sixth_income']) ? $val['tax_computation_report']['Above_sixth_income'] : 0;
                                        $TotalIncome = isset($val['tax_computation_report']['Total_Income']) ? $val['tax_computation_report']['Total_Income'] : 0;

                                        $above1_slab = isset($val['tax_computation_report']['Above_first_slab']) ? $val['tax_computation_report']['Above_first_slab'] : 0;
                                        $above2_slab = isset($val['tax_computation_report']['Above_second_slab']) ? $val['tax_computation_report']['Above_second_slab'] : 0;
                                        $above3_slab = isset($val['tax_computation_report']['Above_third_slab']) ? $val['tax_computation_report']['Above_third_slab'] : 0;
                                        $above4_slab = isset($val['tax_computation_report']['Above_fourth_slab']) ? $val['tax_computation_report']['Above_fourth_slab'] : 0;
                                        $above5_slab = isset($val['tax_computation_report']['Above_fifth_slab']) ? $val['tax_computation_report']['Above_fifth_slab'] : 0;
                                        $above6_slab = isset($val['tax_computation_report']['Above_sixth_slab']) ? $val['tax_computation_report']['Above_sixth_slab'] : 0;
                                        $deductions = isset($val['tax_computation_report']['Deductions']) ? $val['tax_computation_report']['Deductions'] : 0;
                                        $total_tax = isset($val['tax_computation_report']['Total_Tax']) ? $val['tax_computation_report']['Total_Tax'] : 0;
                                        $availed_salary_yearly = isset($val['tax_computation_report']['Availed_Salary_Yearly']) ? $val['tax_computation_report']['Availed_Salary_Yearly'] : 0;
                                        $cess = isset($val['tax_computation_report']['Cess']) ? $val['tax_computation_report']['Cess'] : 0;
                                        $Surcharge = isset($val['tax_computation_report']['Surcharge']) ? $val['tax_computation_report']['Surcharge'] : 0;
                                        $rebate = isset($val['tax_computation_report']['Rebate']) ? $val['tax_computation_report']['Rebate'] : 0;
                                        $total = isset($val['tax_computation_report']['Total']) ? $val['tax_computation_report']['Total'] : 0;
                                        $Tax_Deducted = isset($val['0']['total_tax_deducted']) ? $val['0']['total_tax_deducted'] : 0;
                                        $bal_tax = $total - $Tax_Deducted;
                                        $monthly_tax = isset($val['tax_computation_report']['Monthly_Tax']) ? $val['tax_computation_report']['Monthly_Tax'] : 0;

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '   ' . $j . '   ');
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '      ' . $c_id . '     ');
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '     ' . $user_id . '     ');
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '       ' . $name . ' ' . $empstatus . '       ');
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '        ' . $branch . '        ');
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '         ' . $department . '         ');
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '      ' . $designation . '      ');
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '        ' . $date . '        ');
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '    ' . $pan . '    '); //Edited by Akshay on 12-7-2024
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '    ' . $reg . '    ');
                                        // debug($col + 8); exit;
                                        //Edited by Askshay on 15-6-2024
                                        $col = $col + 10;
                                        // debug($col); exit;
                                        $arr_sal_slip = isset($value['ectc']) ? $value['ectc'] : array();
                                        $arr_salary = isset($value['std']) ? $value['std'] : array();
                                        $arr_settle_slip = isset($value['settlement']) ? $value['settlement'] : array();
                                        $availed_sal = 0;
                                        foreach ($arr_sal_slip as $id => $salary_item) {
                                            // if ($isFinYear) {
                                            //     $ectc_val = isset($salary_item[0][0]['actual_availed']) ?
                                            //         $salary_item[0][0]['actual_availed'] : (isset($arr_salary[$id][0][0]['amount']) ?
                                            //             $arr_salary[$id][0][0]['amount'] :
                                            //             0);
                                            // } else {
                                            $ectc_val = isset($salary_item[0][0]['actual_availed']) ?
                                                $salary_item[0][0]['actual_availed'] : 0;
                                            //  }

                                            $avl_count = isset($salary_item[0][0]['row_count']) ? $salary_item[0][0]['row_count'] : 0;
                                            $std_value = isset($arr_salary[$id][0][0]['amount']) ? $arr_salary[$id][0][0]['amount'] : 0;

                                            //  if ($isFinYear) {
                                            // $ectc_val += ($month_count - $avl_count) * $std_value;
                                            //  }

                                            $head_item = isset($salary_item[0]['emp_salary_slip']['salary_head_item_desc']) ? $salary_item[0]['emp_salary_slip']['salary_head_item_desc'] : '                             ';
                                            $spaces = str_repeat(' ', strlen($head_item));
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $id) . $rowcount, $ectc_val);
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col + $id);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(strlen($head_item) + 14);
                                            $arr_total[$col + $id] = isset($arr_total[$col + $id]) ? $arr_total[$col + $id] + $ectc_val : $ectc_val;
                                            //Edited by Akshay on 5-7-2024
                                            $item_desc = isset($salary_item[0]['emp_salary_slip']['salary_head_item_desc']) ? trim($salary_item[0]['emp_salary_slip']['salary_head_item_desc']) : '';
                                            if ($item_desc != 'Uniform Allowance') {
                                                $availed_sal += $ectc_val;
                                            }
                                            //End
                                        }
                                        $col = $col + count($arr_sal_slip);
                                        foreach ($arr_settle_slip as $id =>  $settle_item) {
                                            // if ($isFinYear) {
                                            //     $ectc_val = isset($settle_item[0][0]['actual_availed']) ?
                                            //         $settle_item[0][0]['actual_availed'] : (isset($settle_item[0][0]['std_availed']) ?
                                            //             $settle_item[0][0]['std_availed'] :
                                            //             0);
                                            // } else {
                                            $ectc_val = isset($settle_item[0][0]['actual_availed']) ?
                                                $settle_item[0][0]['actual_availed'] : 0;
                                            // }

                                            $head_item = isset($salary_item[0]['emp_settle_slip']['salary_head_item_desc']) ? $salary_item[0]['emp_settle_slip']['salary_head_item_desc'] : '                             ';
                                            $spaces = str_repeat(' ', strlen($head_item));
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $id) . $rowcount, $ectc_val);
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col + $id);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(strlen($head_item) + 14);
                                            $arr_total[$col + $id] = isset($arr_total[$col + $id]) ? $arr_total[$col + $id] + $ectc_val : $ectc_val;

                                            $availed_sal += $ectc_val;
                                        }
                                        $col = $col + count($arr_settle_slip);

                                        // $availed_sal = $availed_salary_yearly;
                                        //Edited by Akshay on 6-7-2024
                                        //if (!$isFinYear) {
                                        $availed_sal = $value['yearlytotal'];
                                        // }
                                        //End
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $availed_sal);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $availed_sal : $availed_sal;
                                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                        $col++;
                                        $prof_tax = ($value['prof']) ? abs($value['prof']) : 0;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $prof_tax);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $prof_tax : $prof_tax;
                                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                        $col++;
                                        if ($regime == 'OLD') {
                                            $taxable_income = ($availed_sal - $prof_tax);
                                        } else {
                                            $taxable_income = $availed_sal;
                                        }

                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $taxable_income);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $taxable_income : $taxable_income;
                                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                        $col++;

                                        if ($taxable_income > 0) {
                                            $std_ded = 50000;
                                        } else {
                                            $std_ded = 0;
                                        }
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $std_ded);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $std_ded : $std_ded;
                                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                        $col++;
                                        if ($regime == 'OLD') {
                                            $sec_80_sum = isset($val['sec_80_sum']) ? $val['sec_80_sum'] : 0;
                                            $taxable_income = $taxable_income - $std_ded - $sec_80_sum;
                                        } else {
                                            $taxable_income = $taxable_income - $std_ded;
                                        }
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, ($taxable_income));
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + ($taxable_income) : ($taxable_income);
                                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                        $data_centre_align_end = $col; //Edited by Akshay on 27-6-2024
                                        $col++;

                                        foreach ($employees['tax_sub']['Income'] as $tax_value) {
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $tax_value);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $tax_value : $tax_value;
                                            $col++;
                                        }
                                        foreach ($employees['tax_sub']['Deductions'] as $tax_value) {
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $tax_value);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $tax_value : $tax_value;
                                            $col++;
                                        }
                                        //End
                                        $data_centre_align_start = $col; //Edited by Akshay on 27-6-2024

                                        $spaces = '                              ';
                                        if ($regime == 'NEW') {
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above1);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above1 : $above1;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above2);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above2 : $above2;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above3);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above3 : $above3;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above4);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above4 : $above4;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above5);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above5 : $above5;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above6);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above6 : $above6;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $TotalIncome);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $TotalIncome : $TotalIncome;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                        } else {
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                        }

                                        if ($regime == 'NEW') {
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above1_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above1_slab : $above1_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount,  $above2_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above2_slab : $above2_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above3_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above3_slab : $above3_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above4_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above4_slab : $above4_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above5_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above5_slab : $above5_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above6_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above6_slab : $above6_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $total_tax);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $total_tax : $total_tax;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                        } else {

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount,  0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                        }

                                        //Old regime
                                        if ($regime == 'OLD') {
                                            if ($Total_Taxable_Income > 250000) {
                                                $firsttax = 250000;
                                                // $TotalIncome = $TotalIncome + 250000;
                                            } else {
                                                $firsttax = $Total_Taxable_Income;
                                            }

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $firsttax);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $firsttax : $firsttax;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above1);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above1 : $above1;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above2);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above2 : $above2;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above3);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above3 : $above3;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $Total_Taxable_Income);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $Total_Taxable_Income : $Total_Taxable_Income;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                        } else {

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                        }

                                        if ($regime == 'OLD') {

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount,  $above1_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above1_slab : $above1_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above2_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above2_slab : $above2_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $above3_slab);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $above3_slab : $above3_slab;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $total_tax);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $total_tax : $total_tax;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                        } else {

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount,  0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;

                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 0);
                                            $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + 0 : 0;
                                            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(30);
                                            $col++;
                                        }
                                        // $col--;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $cess);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $cess : $cess;
                                        $col++;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $Surcharge);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $Surcharge : $Surcharge;
                                        $col++;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $rebate);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $rebate : $rebate;
                                        $col++;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $total);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $total : $total;
                                        $col++;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $Tax_Deducted);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $Tax_Deducted : $Tax_Deducted;
                                        $col++;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $bal_tax);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $bal_tax : $bal_tax;
                                        $col++;
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $monthly_tax);
                                        $arr_total[$col] = isset($arr_total[$col]) ? $arr_total[$col] + $monthly_tax : $monthly_tax;
                                    }
                                    // exit;
                                    $j++;
                                    $rowcount++;
                                }
                            }
                        }
                    }

                    //Total
                    $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);
                    $worksheet->mergeCells('E' . $rowcount . ':J' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'TOTAL');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                    foreach ($arr_total as $key => $sum) {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($key) . $rowcount, $sum);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($key, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($key, $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    }

                    //Get column name
                    // $number3 = $inc_count + $ded_count + 51 - 1; //Edited by Akshay on 7-9-2023
                    $number3 = count($arr_total) + 10;
                    $columnName = '';
                    // debug(num3alpha($number3)); exit;
                    while ($number3 > 0) {
                        $remainder = ($number3 - 1) % 26;
                        $columnName = chr(65 + $remainder) . $columnName;
                        $number3 = intval(($number3 - $remainder) / 26);
                    }

                    $BStyle = array(

                        'borders' => array(

                            'allborders' => array(

                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )

                        )

                    );

                    $row = $rowcount - 1;

                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    // debug($columnName); exit;
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . $columnName . $rowcount)->applyFromArray($BStyle);

                    $alignment = $worksheet->getStyle('A5:' . $columnName . '5')->getAlignment();
                    $alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT); //Edited by Akshay on 27-6-2024

                    $alignment = $worksheet->getStyle('A3:' . $columnName . '4')->getAlignment();
                    $alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //Edited by Akshay on 27-6-2024
                    $alignment->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); //Edited by Akshay on 27-6-2024

                    $alignment = $worksheet->getStyle('A6:' . $columnName . $row)->getAlignment();
                    $alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                    if (isset($data_centre_align_end) && isset($data_centre_align_start)) {
                        $data_centre_align_end = num3alpha($data_centre_align_end);
                        $data_centre_align_start = num3alpha($data_centre_align_start);
                        // $objPHPExcel->getActiveSheet()->getStyle('A3:' . $columnName . $rowcount)->applyFromArray($BStyle);
                        $alignment = $worksheet->getStyle('A6:' . $data_centre_align_end . ($row + 1))->getAlignment();
                        $alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //Edited by Akshay on 27-6-2024
                        $alignment = $worksheet->getStyle($data_centre_align_start . '6' . ':' .  $columnName . ($row + 1))->getAlignment();
                        $alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //Edited by Akshay on 27-6-2024
                    }

                    //Background color for heading
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A3:' . $columnName . '3')
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('C8C8C8');
                    //Background color for Total
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A' . $rowcount . ':' . $columnName . $rowcount)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('C8C8C8');

                    //Edited by Akshay on 19-6-2024
                    $BSideStyle = array(
                        'borders' => array(
                            'left' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                            'right' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );

                    //End
                } else {
                    $worksheet->freezePane(false);
                    $rowcount = 4;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);

                    $worksheet->mergeCells('A' . $rowcount . ':L' . $rowcount);

                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)

                    );
                }

                $BStyle = array(

                    'borders' => array(

                        'allborders' => array(

                            'style' => PHPExcel_Style_Border::BORDER_THIN

                        )

                    )

                );

                $row = $rowcount - 1;
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                //$objPHPExcel->getActiveSheet()->getStyle('A1:BC'.$row)->applyFromArray($BStyle);

                $objPHPExcel->getActiveSheet()->setTitle('TAX Details');

                /* header footer */

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                /* header footer */

                /* print Set up */

                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed

                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);
                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);

                break;
            case 'print':
                $this->set('mode', 'print');
                $this->render('tax');
                break;
            default:
                $this->set('mode', '');
                $this->render('tax');
                break;
        }
    }


    private function generate12BBFormreport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $this->set('from', $from);
        $this->set('otdate', $otdate);
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $needBranchWiseReport = false;
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            $this->set('needBranchWiseReport', $needBranchWiseReport);
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $arr_salary_for_template = array();
        $arr_gross = array();
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $resign_condition = " and emp_status in ('1','2') ";
        } else {
            $resign_condition = " and emp_status = '1' ";
        }
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            if ($arr_form_data['select-criteria1'] == 'Units') {
                foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                    $fin_year = $this->EmpCtcTransaction->query("SELECT * from fin_year where branch_code='$leavepolicygroupid' and status = 1");
                    foreach ($fin_year as $year) {
                        $startDate = $year['fin_year']['start_month'];
                        $endDate = $year['fin_year']['end_month'];
                        // Check if the date to check is after the start date and before the end date
                        if ($otdate > $startDate && $otdate < $endDate) {
                            $finyear1 = $year['fin_year']['fin_year'];
                            $finyear = $finyear1 . '-' . ($finyear1 + 1);
                            $start = $startDate;
                            $end = date("Y-m-t", strtotime("$otdate"));
                        }
                    }
                    $arr_gross = $this->EmpCtcTransaction->query("SELECT emp_details.*,employee_info.EmpName,employee_info.designation,employee_info.branch  from employee_info left join emp_details on(emp_details.emp_pkey = employee_info.emp_pkey)"
                        . "left join  emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = employee_info.emp_pkey) "
                        . "WHERE employee_info.branch_code = '$leavepolicygroupid' $resign_condition group by employee_info.emp_pkey order by EmpName");
                    $arr_salary_for_template_br = array();
                    foreach ($arr_gross as $arr) {
                        $emp_pkey = isset($arr['emp_details']['emp_pkey']) ? $arr['emp_details']['emp_pkey'] : '';
                        $EmpName = isset($arr['employee_info']['EmpName']) ? $arr['employee_info']['EmpName'] : '';
                        $designation = isset($arr['employee_info']['designation']) ? $arr['employee_info']['designation'] : '';
                        $arr_taxheadfields = $this->requestAction("/Taxation/getTaxHeadFields");
                        $taxdetails = $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey");
                        $tax = array();
                        if (!empty($taxdetails)) {
                            foreach ($arr_taxheadfields['Deductions']['tax_heads'] as $heads) {
                                foreach ($taxdetails as $key => $val) {
                                    if ($heads['tax_heads_pkey'] == $key) {
                                        $arr_taxheaddetails = $this->requestAction("/Taxation/getTaxHeadDetails/$key");
                                        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
                                        $arr_emp_transaction = Set::extract('/EmployeeTaxTransactions/.', $this->EmployeeTaxTransactions->find('all', array('conditions' => array('emp_fkey' => $emp_pkey, 'tax_heads_fkey' => $key, 'tax_heads_details_fkey != 0', 'fin_year' => $finyear1, 'creation_date <=' => $end))));
                                        $transaction = array();
                                        foreach ($arr_emp_transaction as $emptransaction) {
                                            if ($emptransaction['tax_value'] > 0) {
                                                foreach ($arr_taxheaddetails as $val) {
                                                    if ($val['tax_heads_details_pkey'] == $emptransaction['tax_heads_details_fkey'] && $val['tax_heads_fkey'] == $emptransaction['tax_heads_fkey']) {
                                                        $subheadname = $val['tax_heads_details'];
                                                        $subheadname1 = $val['tax_heads_details1'];
                                                        $document = 'No';
                                                        if ($emptransaction['file_name'] != null && $emptransaction['file_type'] != null) {
                                                            $document = 'Yes';
                                                        }
                                                        $transaction[] = array(
                                                            'tax_heads_details_fkey' => $emptransaction['tax_heads_details_fkey'],
                                                            'tax_value' => $emptransaction['tax_value'],
                                                            'sub_details' => $subheadname,
                                                            'sub_details1' => $subheadname1,
                                                            'evidence' => $document
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                        if (!empty($transaction)) {
                                            $tax[] = array(
                                                'name' => $heads['tax_name'],
                                                'details' => $heads['tax_details'],
                                                'subheadvalues' => $transaction
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        if (!empty($tax)) {
                            $arr_salary_for_template_br[] = array(
                                'summary' => $arr,
                                'Name' => $EmpName,
                                'Designation' => $designation,
                                'finyear' => $finyear,
                                'details' => $tax
                            );
                        }
                    }
                    if (!empty($arr_salary_for_template_br)) {
                        $arr_salary_for_template[$leavepolicygroupid][] = array(
                            'tax' => $arr_salary_for_template_br
                        );
                    }
                }
            } else {
                $arr_salary_for_template = array();
                foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                    $arr_gross = $this->EmpCtcTransaction->query("SELECT emp_details.*,employee_info.EmpName,employee_info.designation from employee_info left join emp_details on(emp_details.emp_pkey = employee_info.emp_pkey)"
                        . "left join  emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = employee_info.emp_pkey) "
                        . "WHERE employee_info.emp_pkey = '$leavepolicygroupid' $resign_condition group by employee_info.emp_pkey");
                    $branch_code = isset($arr_gross['0']['emp_details']['branch_code']) ? $arr_gross['0']['emp_details']['branch_code'] : '';
                    $fin_year = $this->EmpCtcTransaction->query("SELECT * from fin_year where branch_code='$branch_code' and status = 1");
                    foreach ($fin_year as $year) {
                        $startDate = $year['fin_year']['start_month'];
                        $endDate = $year['fin_year']['end_month'];
                        // Check if the date to check is after the start date and before the end date
                        if ($otdate > $startDate && $otdate < $endDate) {
                            $finyear1 = $year['fin_year']['fin_year'];
                            $finyear = $finyear1 . '-' . ($finyear1 + 1);
                            $start = $startDate;
                            $end = date("Y-m-t", strtotime("$otdate"));
                        }
                    }
                    $arr_taxheadfields = $this->requestAction("/Taxation/getTaxHeadFields");
                    $taxdetails = $this->requestAction("/Taxation/loadEmpTaxationDetails/$leavepolicygroupid");
                    $tax = array();
                    if (!empty($taxdetails)) {
                        foreach ($arr_taxheadfields['Deductions']['tax_heads'] as $heads) {
                            foreach ($taxdetails as $key => $val) {
                                if ($heads['tax_heads_pkey'] == $key) {
                                    $arr_taxheaddetails = $this->requestAction("/Taxation/getTaxHeadDetails/$key");
                                    $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
                                    $arr_emp_transaction = Set::extract('/EmployeeTaxTransactions/.', $this->EmployeeTaxTransactions->find('all', array('conditions' => array('emp_fkey' => $leavepolicygroupid, 'tax_heads_fkey' => $key, 'tax_heads_details_fkey != 0', 'fin_year' => $finyear1, 'creation_date <=' => $end))));
                                    //                     $log = $this->EmployeeTaxTransactions->getDataSource()->getLog(false, false);
                                    //debug($log);exit;
                                    $transaction = array();
                                    foreach ($arr_emp_transaction as $emptransaction) {
                                        if ($emptransaction['tax_value'] > 0) {
                                            foreach ($arr_taxheaddetails as $val) {
                                                if ($val['tax_heads_details_pkey'] == $emptransaction['tax_heads_details_fkey'] && $val['tax_heads_fkey'] == $emptransaction['tax_heads_fkey']) {
                                                    $subheadname = isset($val['tax_heads_details']) ? $val['tax_heads_details'] : '';
                                                    $subheadname1 = isset($val['tax_heads_details1']) ? $val['tax_heads_details1'] : '';
                                                    $document = 'No';
                                                    if ($emptransaction['file_name'] != null && $emptransaction['file_type'] != null) {
                                                        $document = 'Yes';
                                                    }
                                                    $transaction[] = array(
                                                        'tax_heads_details_fkey' => $emptransaction['tax_heads_details_fkey'],
                                                        'tax_value' => $emptransaction['tax_value'],
                                                        'sub_details' => $subheadname,
                                                        'sub_details1' => $subheadname1,
                                                        'evidence' => $document
                                                    );
                                                }
                                            }
                                        }
                                    }
                                    if (!empty($transaction)) {
                                        $tax[] = array(
                                            'name' => $heads['tax_name'],
                                            'details' => $heads['tax_details'],
                                            'subheadvalues' => $transaction
                                        );
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($tax)) {
                        $arr_salary_for_template[] = array(
                            'summary' => $arr_gross['0']['emp_details'],
                            'Name' => $arr_gross['0']['0']['EmpName'],
                            'Designation' => $arr_gross['0']['employee_info']['designation'],
                            'finyear' => $finyear,
                            'details' => $tax
                        );
                    }
                }
            }
        }

        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('12bbform');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);

                $html2pdf->Output('Form_12BB.pdf', 'D');
                $this->render('12bbform');
                break;
            case 'print':
                $this->set('mode', 'print');
                $this->render('form');
                break;
            default:
                $this->set('mode', '');
                $this->render('12bbform');
                break;
        }
    }
    private function generateEPFSYNTHIETlabourreport($mode)
    {

        $arr_form_data = $_REQUEST;

        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc
                    where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc ORDER BY emp_salary_slip_pkey ");

        $array_key = array();
        foreach ($arr_keys as $val) {
            if ($val['ectc']['head_operator'] == 'Addition') {

                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {

                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        //$otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $f = date('Y/m', strtotime($arr_form_data['reportfrom']));
        $f1 = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('M', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);

        $this->set('mname1', $mname);
        $this->set('y1', $year);
        $this->set('from', $from);
        $date_timenew = date('d-m-Y H:i:s');
        $date_time = date('d/m/Y');
        $downtime = date('H:i:s');
        $user_id = $this->Session->read('login_user_id');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $this->set('downtime', $downtime);
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $conditions = array();

        $conditions[] = 'and ectc.month_year="' . $from . '"';

        $arr_leavepolicygroupids = array();

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {

            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        $arr_leavepolicydetails_for_template = array();

        $id = implode(' AND ', $conditions);

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))

            $k = 0;

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {

            $resign_condition = " and emp_details.status = '1' ";
        }

        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {



            //            $arr_emp_info = $this->EmpCtcTransaction->query(" select * from employee_info where  emp_pkey =  '$leavepolicygroupid' ");
            //edited by sinsiya on 12-06-2024--> join the query with the table attendance_register to get lop days changed ectc.salary_amount into  SUM(ectc.salary_amount) AS sum_amount in sub queries
            $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.status,emp_details.company_pf,emp_details.eps,abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 

AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,

abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 

AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  

AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  

AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,

abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 

AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  

AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
            AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey 
            from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) as EPF_earning ,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 

AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY, attendance_register.weekoff_total,attendance_register.holiday_total,attendance_register.leave_total,attendance_register.presant_total,payroll_master.calander_days,payroll_master.loss_of_pay from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 
LEFT JOIN attendance_register ON attendance_register.emp_fkey = employee_info.emp_pkey AND attendance_register.month_year = '$from' AND attendance_register.isdelete = 'N'
LEFT JOIN payroll_master ON payroll_master.emp_fkey = employee_info.emp_pkey AND payroll_master.month_year = '$from'
where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' and end_date_effective is null)

 and emp_details.branch_Code = '$leavepolicygroupid' $resign_condition  ORDER BY employee_info.EmpName ASC");


            //debug($arr_gross); 


            $arr_salary_for_template[] = $arr_gross;

            $k++;
        }

        //}

        $this->set('keys', $arr_keys);

        $this->set('array_key', $array_key);



        $this->set('arr_salary_for_template', $arr_salary_for_template);

        //  debug($arr_salary_for_template);

        $cr = $arr_form_data['select-criteria1'];

        $this->set('cr', $cr);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $user_name = $this->Session->read('user_name');

        $this->set('user_name', $user_name);

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $this->set('month', $from);

        //Set informations needed for report



        switch ($mode) {

            case 'pdf':

                //   echo "entered in";
                $str_company_code = $this->Session->read('company_code');
                $this->set('mode', 'pdf');

                $view = new View($this, false);

                $view_output = $view->render('empepfsynthiet');

                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));



                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //  $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');

                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');

                $html2pdf->pdf->SetDisplayMode('fullpage');

                $html2pdf->writeHTML($view_output);
                $html2pdf->Output($str_company_code  . " PF_STATEMENT "  . $from .  '.pdf', 'D');
                //  $html2pdf->Output('PFSummary.pdf', 'D');

                $this->render('empepfsynthiet');

                break;
            case 'excel':

                $str_company_code = $this->Session->read('company_code');

                $file_name = isset($str_company_code) ? $str_company_code . "_PF_STATEMENT_ " . $from . ".xlsx" : "PFSTATEMENT" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");

                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");

                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
                // Set the left-aligned text
                //$worksheet->setCellValue('A1', $arr_comp_contact_info['CompanyContactInfo']['business_name']);
                //  $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
                //  $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                //    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                //   );

                // Set the right-aligned text
                //  $worksheet->setCellValue('K1', "Report Date: " . $date_time);
                //   $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
                //   $worksheet->getStyle('K1')->getAlignment()->applyFromArray(
                //       array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                //   );

                // Optional: Adjust column widths to ensure visibility
                $worksheet->getColumnDimension('A')->setAutoSize(true);
                $worksheet->getColumnDimension('K')->setAutoSize(true);
                $worksheet->setCellValueByColumnAndRow(0, 1, " PF STATEMENT FOR THE MONTH OF  " . $f1);

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $worksheet->mergeCells('A1:K1');

                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                );
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_timenew . ")");

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:K2');

                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(

                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                );

                for ($col = 'A'; $col !== 'M'; $col++) {

                    $objPHPExcel->getActiveSheet()

                        ->getColumnDimension($col)

                        ->setAutoSize(true);
                }

                //  $worksheet->mergeCells('A1:K1');

                // $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                //     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                // );

                //$worksheet->mergeCells('A2:K2');
                $rowcount = 2;
                $i = 0;

                //Border style
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );


                foreach ($arr_salary_for_template as $value) {
                    if (count($value) == 0) {
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $worksheet->mergeCells('A3:K3');

                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(

                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)

                        );
                    }
                    if (count($value) > 0) {
                        $objPHPExcel->getActiveSheet()->freezePane('D3');
                        $rowcount++;

                        $col = 0;
                        //  $worksheet->setCellValueByColumnAndRow(0, 2, $value[0]['employee_info']['branch']);

                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $value[0]['employee_info']['branch']);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('A' . $rowcount . ':L' . $rowcount);
                        $rowcount++;

                        // $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                        //$worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount);

                        // $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(

                        // array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                        //   );
                        // $worksheet->setCellValueByColumnAndRow(8, $rowcount, "EPF Details");

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);

                        //  $worksheet->mergeCells('I' . $rowcount . ':K' . $rowcount);

                        //  $worksheet->getStyle('I' . $rowcount)->getAlignment()->applyFromArray(

                        //   array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                        // );
                        //  $worksheet->setCellValueByColumnAndRow(11, $rowcount, "EPF - Employee");

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);


                        //$worksheet->setCellValueByColumnAndRow(12, $rowcount, "EPF - Employer");

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);

                        //$worksheet->mergeCells('M' . $rowcount . ':O' . $rowcount);

                        //$worksheet->getStyle('M' . $rowcount)->getAlignment()->applyFromArray(

                        //   array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                        // );

                        // $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Sl No');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                        // $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col, $rowcount)->setWidth('20');

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'PF NO');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'Employee ID');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Employee Name');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Days Present');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'NCP Days');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'PF Salary');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, 'Employee PF');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                        //edited by sinsiya on 12-06-2024
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, 'Employee VPF');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 'Employer PF');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 'Employer Pension');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, 'Total PF');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, '  Excluded Salary ');

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);

                        //edited by megha on 30/05/2020

                        //  if ($from >= '2020-05' && $from <= '2020-07') {

                        //   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  10% ');
                        //   } else {

                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, '  12% ');
                        // }

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);

                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  8.33% ');

                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);

                        //  if ($from >= '2020-05' && $from <= '2020-07') {

                        //   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, '  1.67%  ');
                        //   } else {

                        //   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, '  3.67%  ');
                        //  }

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 14, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, '  0.5%  ');

                        //   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 15, $rowcount)->getFont()->setBold(true);

                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount, '  0.5%  ');

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 16, $rowcount)->getFont()->setBold(true);
                        //edited by sinsiya on 12-06-2024 end changed the index number
                        $col = 7;

                        $rowcount = $rowcount + 1;

                        $arr_data = $value;

                        $pf_salary = 0;
                        $split1 = 0;
                        $split2 = 0;
                        $split3 = 0;
                        $split5 = 0;

                        $grandtot = 0;

                        // $emp_ep = 0;

                        //  $epr_ep = 0;
                        //   $esi_t = 0;
                        //   $esia_t = 0;
                        $j = 1;
                        $dataFound = false;
                        foreach ($arr_data as $val) {
                            if ($val['0']['EPF'] != 0) {
                                $dataFound = true;
                                $col = 0;
                                //edited by sinsiya on 10-07-2024
                                $empstatus = isset($val['emp_details']['status']) && $val['emp_details']['status'] == "2" ? '(Resigned)' : '';
                                $name = $val['employee_info']['EmpName'] . $empstatus;
                                $pf_no = htmlspecialchars($val['emp_details']['company_pf']);
                                $empid = $val['employee_info']['employee_id'];
                                $dayscount = $val['attendance_register']['leave_total'] + $val['attendance_register']['weekoff_total'] + $val['attendance_register']['holiday_total'] + $val['attendance_register']['presant_total'];
                                $ncp = $val['payroll_master']['calander_days'] - $dayscount;
                                $dayspresent = $val['payroll_master']['calander_days'] - $ncp;
                                //$sal = ($month >= '2020-05' && $month <= '2020-07') ? round($val[0]['EPF'] * 100 / 10, 0) : round($val[0]['EPF'] * 100 / 12, 0);
                                if ($month >= '2020-05' && $month <= '2020-07') {
                                    $sal = round($val['0']['EPF'] * 100 / 10, 0);
                                } else {
                                    $sal = round($val['0']['EPF'] * 100 / 12, 0);
                                }
                                $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 0) : round($sal * 12 / 100, 0);
                                $epf = $pf;
                                $sal1 = isset($val['0']['EPF_earning']) ? $val['0']['EPF_earning'] : '';
                                if ($sal1 != '') {
                                    $expressionWithoutPortion = str_replace(['* .12', '* 12/100'], '', $sal1);
                                    eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                    $sal1 = floor($epf_earnings);
                                }
                                if ($epf == 1800) {
                                    $sal1 = 15000;
                                }

                                // echo $sal; 
                                $pf_salary += $sal1;
                                //$split1 += round($sal * 8.33 / 100, 2);
                                if ($month >= '2020-05' && $month <= '2020-07') {
                                    // $split2 += round($sal * 1.67 / 100, 0);
                                    $epf = round($sal * 10 / 100, 0);
                                    // if ($epf > 1800) {
                                    $split5 += 0;
                                    // } else {
                                    $split3 += round($sal * 10 / 100, 0);
                                    //}
                                } else {
                                    // $split2 += round($sal * 3.67 / 100, 0);
                                    $epf = round($sal * 12 / 100, 0);
                                    //  if ($epf > 1800) {
                                    $split5 += 0;
                                    //} else {
                                    $split3 += round($sal * 12 / 100, 0);
                                    // }
                                }
                                //$dep = $val['employee_info']['department'];
                                //$deg = $val['employee_info']['designation'];
                                //edited by sinsiya on 12-06-2024 to add lop days
                                //$ncp = $val['attendance_register']['lop_total'];
                                //$branch = $val['employee_info']['branch'];
                                //$uan = $val['emp_details']['pf'];
                                //$gross = round($val['0']['SALARY'], 2);
                                // $epf_salary = round($val['0']['SALARY'], 2) - round($val['0']['EPF'], 2);
                                //                            if ($from >= '2020-05' && $from <= '2020-07') {
                                //                                $esi_salary = round($val['0']['EPF'] * 100 / 10, 2);
                                //                                $sal = round($val['0']['EPF'] * 100 / 10, 2);
                                //                                $www_salary = round($sal * 10 / 100, 2);
                                //                            } else {
                                //                                $esi_salary = round($val['0']['EPF'] * 100 / 12, 2);
                                //                                $sal = round($val['0']['EPF'] * 100 / 12, 2);
                                //                                $www_salary = round($sal * 12 / 100, 2);
                                //                            }
                                $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 0) : round($sal * 12 / 100, 0);
                                // if ($pf <= 1800) {
                                $epf = $pf;
                                // } else {
                                //    $epf = '0';
                                //  }
                                //$pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 2) : round($sal * 12 / 100, 2); 
                                // if ($pf > 1800) {
                                // $vpf = $pf;
                                // } else {
                                $vpf = '0';
                                // }
                                $eps_status = isset($val['emp_details']['eps']) ? $val['emp_details']['eps'] : 'Y';
                                if ($sal > 15000) {
                                    if ($eps_status != 'N') {
                                        $employerpension = round(15000 * 8.33 / 100, 0);
                                    } else {
                                        $employerpension = 0;
                                    }
                                    $employerpf = $val['0']['EPF'] - $employerpension;
                                } else {
                                    if ($eps_status != 'N') {
                                        $employerpension = round($sal * 8.33 / 100, 0);
                                    } else {
                                        $employerpension = 0;
                                    }
                                    $employerpf = $val['0']['EPF'] - $employerpension;
                                }
                                if ($eps_status != 'N') {
                                    $employerpension = $employerpension;
                                } else {
                                    $employerpension = 0;
                                    // $totals[4] += 0;
                                }
                                // $employerpf = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                $split2 += $employerpf;
                                //  $employerpension = round($sal * 8.33 / 100, 0);
                                $split1 += $employerpension;
                                if ($month >= '2020-05' && $month <= '2020-07') {
                                    $pfnew = round($sal * 10 / 100, 0);
                                    // $epfnew = round($sal * 1.67 / 100, 0);
                                    //  $epfnew = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                    //  $employer_pension = round($sal * 8.33 / 100, 0);
                                    $tot = round($pfnew + $employerpf);
                                    //echo round($tot);
                                    //debug($tot);exit;
                                } else {
                                    $pfnew = round($sal * 12 / 100, 0);
                                    // $epfnew = round($sal * 3.67 / 100, 0);
                                    // $epfnew = $val['0']['EPF']- round($sal * 8.33 / 100, 0);
                                    //$employer_pension = round($sal * 8.33 / 100, 0);
                                    $tot = round($pfnew + $employerpf);
                                    //  echo round($tot);


                                }
                                $grandtot += $tot;

                                //debug($eps_status);exit;
                                //                            if ($from >= '2020-05' && $from <= '2020-07') {
                                //                                $esi = round($sal * 1.67 / 100, 2);
                                //                            } else {
                                //                                $esi = round($sal * 3.67 / 100, 2);
                                //                            }
                                //                            $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 2) : round($sal * 12 / 100, 2); 
                                //                            if($pf < 1800) { $epf=$pf; }else{$epf= '0';}
                                //                            $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 2) : round($sal * 12 / 100, 2); 
                                //                            if($vpf > 1800){ $vpf=$pf; } else{$vpf='0';}
                                //                            $esia = round($sal * .5 / 100, 2);
                                //                            $www = round($val['0']['EMPLOYER_WWFS'], 2);
                                //                            $j = $j + 1;
                                //                            $gross_tot += $gross;
                                //
                                //                            $pf_sal_t += $sal;
                                //
                                //                            $emp_ep += $www_salary;
                                //
                                //                            $epr_ep += $epf;
                                //
                                //                            $esi_t += $esi;
                                //
                                //                            $esia_t += $esia;
                                //


                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $pf_no);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $empid);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $dayspresent);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $ncp);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $sal1);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $epf);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $vpf);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $employerpf);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $employerpension);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $tot);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, $esi);

                                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, $esia);

                                //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount, $esia);

                                //                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $epf);

                                //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, $esi);

                                //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $www);



                                //$col = 7;








                                $j++;

                                $rowcount++;
                            }
                        }

                        if (!$dataFound) {
                            // $rowcount++; // Move to the next row

                            // Set "No Data Found" message
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowcount, "No Data Found");
                            $worksheet->mergeCells('A' . $rowcount . ':K' . $rowcount);
                            // Optionally, apply center alignment
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            // Optionally, apply additional styling or formatting as needed

                            // Increment $rowcount further if necessary for additional content after "No Data Found"
                            // $rowcount++;
                        }
                        if ($pf_salary != 0) {
                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");

                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                            $worksheet->mergeCells('A' . $rowcount . ':F' . $rowcount);

                            $worksheet->getStyle('L' . $rowcount)->getAlignment()->applyFromArray(

                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                            );

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $pf_salary);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $split3);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $split5);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $split2);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $split1);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $grandtot);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                        // $rowcount++;
                        //edited by sinsiya on 12-06-2024 changed the index value 
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $gross_tot);

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $pf_sal_t);

                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, '');

                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $emp_ep);

                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $epr_ep);

                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $esi_t);

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $esia_t);

                        //   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $esia_t);

                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                        //edited by sinsiya on 12-06-2024 changing index value ended
                        $col = 11;
                        // $rowcount++;
                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $columnLetter . $rowcount)->applyFromArray($styleArray);
                    }
                }

                //                                     
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('PF STATEMENT');

                /* header footer */

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                /* header footer */

                /* print Set up */

                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed

                header('Content-Type: application/vnd.ms-excel; charset=utf-8');

                header('Content-Disposition: attachment; filename=' . $file_name);



                readfile(dirname(__FILE__) . "/" . $file_name);

                unlink(dirname(__FILE__) . "/" . $file_name);

                break;

            case 'print':

                //   echo "entered in";

                $this->set('mode', 'print');

                $this->render('empepfsynthiet');

                break;

            default:

                $this->set('mode', '');

                $this->render('empepfsynthiet');

                break;
        }
    }
    //edited by sinsiya on 23-06-2024
    private function generateESISYNTHIETlabourreport($mode)
    {

        $arr_form_data = $_REQUEST;

        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');



        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc

                                                    where item_part='Direct'  and head_operator = 'Deduction' Group by salary_head_item_desc

                                                    ORDER BY emp_salary_slip_pkey ");

        $array_key = array();


        foreach ($arr_keys as $val) {



            if ($val['ectc']['head_operator'] == 'Addition') {

                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {

                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        //debug($array_key);

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $fromdate = date('Y/m', strtotime($arr_form_data['reportfrom']));
        //  $otdate = date('Y-m-1', strtotime($arr_form_data['reportsto']));
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('M', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        // Set the default timezone
        date_default_timezone_set('Asia/Kolkata'); // Change to your desired timezone
        $currentDateTime = date('d/m/Y h:i A');
        $this->set('currentDateTime', $currentDateTime);
        $this->set('from', $fromdate);
        // $this->set('mname1', $mname);
        // $this->set('y1', $year);
        // $this->set('month2', $month);
        // $this->set('month1', $month1);

        //added by megha on 24_07_19

        // $this->set('from', $from);

        // $this->set('otdate', $otdate);

        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {

            echo "Choose Criteria ";

            return false;
        }



        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {

            echo "Choose Criteria ";

            return false;
        }



        $conditions = array();

        $conditions[] = 'and ectc.month_year="' . $from . '"';

        $arr_leavepolicygroupids = array();

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        // debug($int_criterias_count);
        for ($i = 1; $i <= $int_criterias_count; $i++) {

            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';

            $needBranchWiseReport = false;
            $needdepartmentwiseReport = false;
            $needdesignationwise = false;
            $needgenderwise = false;


            if ($str_criteria_item == 'Units') {

                $needBranchWiseReport = true;
            }

            $this->set('needBranchWiseReport', $needBranchWiseReport);
            if ($str_criteria_item == 'Gender') {

                $needgenderwise = true;
            }

            $this->set('needgenderwise', $needgenderwise);


            if ($str_criteria_item == 'Departments') {
                $needdepartmentwiseReport = true;
            }


            $this->set('needdepartmentwiseReport', $needdepartmentwiseReport);

            if ($str_criteria_item == 'Designation') {
                $needdesignationwise = true;
            }


            $this->set('needdesignationwise', $needdesignationwise);


            $this->set('str_criteria_item', $str_criteria_item);
            //debug($str_criteria_item);
            if ($str_criteria_item == '') {

                echo "<h1>No Criteria Selected</h1>";

                die();
            }



            if (!isset($arr_form_data[$str_criteria_item])) {

                echo "<h1>No Criteria Selected</h1>";

                die();
            }
        }



        $arr_leavepolicydetails_for_template = array();

        $arr_salary_for_template = array();

        $arr_gross = array();

        $id = implode(' AND ', $conditions);

        // if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))

        $k = 0;

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {

            $resign_condition = " and emp_details.status = '1' ";
        }



        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {

            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {



                if ($arr_form_data['select-criteria1'] == 'Units') {




                    $arr_gross = $this->EmpCtcTransaction->query("SELECT 
    employee_info.*, 
    emp_details.esi, 
    emp_details.classification,
    emp_details.status,
    (SELECT presant_total FROM attendance_register WHERE month_year='$from' AND isdelete='N' AND attendance_register.emp_fkey = employee_info.emp_pkey) AS present,
    (SELECT leave_total FROM attendance_register WHERE month_year='$from' AND isdelete='N' AND attendance_register.emp_fkey = employee_info.emp_pkey) AS leaves,
    (SELECT weekoff_total FROM attendance_register WHERE month_year='$from' AND isdelete='N' AND attendance_register.emp_fkey = employee_info.emp_pkey) AS weekoff,
    (SELECT holiday_total FROM attendance_register WHERE month_year='$from' AND isdelete='N' AND attendance_register.emp_fkey = employee_info.emp_pkey) AS holiday,
    ABS(IFNULL((SELECT ectc.salary_amount FROM emp_salary_slip AS ectc WHERE ectc.emp_fkey = employee_info.emp_pkey AND LCASE(ectc.item_part) = 'direct' AND ectc.end_date_effective IS NULL AND ectc.month_year = '$from' AND ectc.salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE LCASE(tax_salary_components_name) = 'employee esi' AND status = 1) AND ectc.end_date_effective IS NULL), 0)) AS Esi,
    (SELECT ectc.remarks FROM emp_salary_slip AS ectc WHERE ectc.emp_fkey = employee_info.emp_pkey AND LCASE(ectc.item_part) = 'direct' AND ectc.end_date_effective IS NULL AND ectc.month_year = '$from' AND ectc.salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE LCASE(tax_salary_components_name) = 'employee esi' AND status = 1) AND ectc.end_date_effective IS NULL) AS ESI_EARNING,
    ABS(IFNULL((SELECT ectc.salary_amount FROM emp_salary_slip AS ectc WHERE ectc.emp_fkey = employee_info.emp_pkey AND LCASE(ectc.item_part) = 'indirect' AND ectc.end_date_effective IS NULL AND ectc.month_year = '$from' AND ectc.salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE LCASE(tax_salary_components_name) = 'employer esi' AND status = 1) AND ectc.end_date_effective IS NULL), 0)) AS EMPLOYER_ESI,
    ABS(IFNULL((SELECT SUM(ectc.salary_amount) FROM emp_salary_slip AS ectc WHERE ectc.emp_fkey = employee_info.emp_pkey AND LCASE(ectc.item_part) = 'direct' AND ectc.end_date_effective IS NULL AND head_operator = 'Addition' AND ectc.month_year = '$from'), 0)) AS SALARY,
    payroll_master.loss_of_pay,
    payroll_master.working_days,
    payroll_master.monthly_ctc,
    payroll_master.gross_salary,
    payroll_master.calander_days
FROM 
    employee_info
LEFT JOIN 
    emp_details ON emp_details.emp_pkey = employee_info.emp_pkey
LEFT JOIN 
    payroll_master ON payroll_master.emp_fkey = employee_info.emp_pkey AND payroll_master.month_year = '$from'
WHERE 
    employee_info.emp_pkey IN (SELECT emp_fkey FROM emp_salary_slip WHERE month_year = '$from' AND end_date_effective IS NULL AND emp_salary_slip.salary_head_item_fkey IN (SELECT salary_head_item_Fkey FROM tax_salary_components WHERE LCASE(tax_salary_components_name) = 'employee esi' AND status = 1) AND salary_amount != 0)
    AND emp_details.branch_code = '$leavepolicygroupid' 
    $resign_condition 
ORDER BY 
    employee_info.EmpName ASC;
");
                    //foreach($arr_gross as $val){
                    //  $empfkey=$val['employee_info']['emp_pkey'];
                    //  $LOP = $this->EmpCtcTransaction->query("select loss_of_pay,working_days,monthly_ctc,gross_salary from payroll_master where emp_fkey = '$empfkey' AND month_year = '$from' ");
                    //  $lops = isset($LOP['0']['payroll_master']['loss_of_pay']) ? $LOP['0']['payroll_master']['loss_of_pay'] : 0;
                    //  $wd = isset($LOP['0']['payroll_master']['working_days']) ? $LOP['0']['payroll_master']['working_days'] : 0;
                    //$termination_details = $this->EmpCtcTransaction->query("select * from termination where emp_fkey = '$leavepolicygroupid' and status = 1 and (last_approved_working_date like '%$from%' OR last_approved_working_date like '$prev_month')");
                    // $resignation_date = isset($termination_details['0']['termination']['last_approved_working_date']) ? $termination_details['0']['termination']['last_approved_working_date'] : '';
                    // $numberOfWorkingDays = '';
                    // debug($numberOfWorkingDays);
                    //  }
                } elseif ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,emp_details.status,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY, payroll_master.loss_of_pay,
payroll_master.working_days,
payroll_master.monthly_ctc,
payroll_master.gross_salary,
payroll_master.calander_days from employee_info
left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
LEFT JOIN payroll_master ON payroll_master.emp_fkey = employee_info.emp_pkey AND payroll_master.month_year = '$from'

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and emp_details.emp_pkey = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                    // DEBUG($arr_gross);
                    // $LOP = $this->EmpCtcTransaction->query("select calander_days,loss_of_pay,working_days,monthly_ctc,gross_salary from payroll_master where emp_fkey = '$leavepolicygroupid' AND month_year = '$from' ");
                    // $lops = isset($LOP['0']['payroll_master']['loss_of_pay']) ? $LOP['0']['payroll_master']['loss_of_pay'] : 0;
                    //  $wd = isset($LOP['0']['payroll_master']['calander_days']) ? $LOP['0']['payroll_master']['calander_days'] : 0;
                    //$termination_details = $this->EmpCtcTransaction->query("select * from termination where emp_fkey = '$leavepolicygroupid' and status = 1 and (last_approved_working_date like '%$from%' OR last_approved_working_date like '$prev_month')");
                    // $dayscount = $arr_gross['0']['0']['leaves'] + $arr_gross['0']['0']['weekoff'] + $arr_gross['0']['0']['holiday']+ $arr_gross['0']['0']['present'];
                    // $ncp = $LOP['0']['payroll_master']['calander_days'] - $dayscount;

                    // $resignation_date = isset($termination_details['0']['termination']['last_approved_working_date']) ? $termination_details['0']['termination']['last_approved_working_date'] : '';
                    // $numberOfWorkingDays = ceil($wd - $ncp);
                } elseif ($arr_form_data['select-criteria1'] == 'Departments') {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info

left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)

 left join department on (department.dept_code = emp_proff.emp_dept)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and department.dept_code = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } elseif ($arr_form_data['select-criteria1'] == 'Designation') {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info

left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)

 left join designation on (designation.desig_code = emp_proff.designation)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and designation.desig_code = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                } else {
                    $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.esi,emp_details.classification,(select presant_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) present,

(select leave_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) leaves,

(select weekoff_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) weekoff,

(select holiday_total from attendance_register where month_year='$from' and isdelete='N' and attendance_register.emp_fkey= employee_info.emp_pkey) holiday,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,

(select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in
(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) 
and end_date_effective is null) as ESI_EARNING,

abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'

AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,

abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'

AND ectc.end_date_effective is null and head_operator = 'Addition' and ectc.month_year='$from' ),0)) SALARY from employee_info

left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey)
left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)

 left join department on (department.dept_code = emp_proff.emp_dept)

where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 

and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components

where lcase(tax_salary_components_name)= 'employee esi' and status=1) and salary_amount != 0)

and emp_details.classification = '$leavepolicygroupid' $resign_condition order by employee_info.EmpName asc");
                }

                //($arr_gross);exit();

                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = array(
                        'summary' => $arr_gross,
                        // 'WORKDAYS' => $numberOfWorkingDays
                    );
                    $k++;
                }
            }
        }
        //debug($arr_salary_for_template);


        $this->set('keys', $arr_keys);

        $this->set('array_key', $array_key);

        //debug($gross);

        $this->set('arr_salary_for_template', $arr_salary_for_template);

        //debug($arr_salary_for_template); die();

        $cr = $arr_form_data['select-criteria1'];

        $this->set('cr', $cr);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $user_name = $this->Session->read('user_name');

        $this->set('user_name', $user_name);

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $this->set('month', $from);

        $cname = $arr_comp_contact_info['CompanyContactInfo']['business_name'];



        $arr_compliance = $this->EmpCtcTransaction->query("select emp_state_ins_no,pf_no,service_tax from compliance");

        // debug($arr_compliance);exit();

        $eip = $arr_compliance['0']['compliance']['emp_state_ins_no'];



        $this->set('eip', $eip);

        //Set informations needed for report



        switch ($mode) {

            case 'pdf':


                //   echo "entered in";

                $this->set('mode', 'pdf');

                $view = new View($this, false);

                $view_output = $view->render('empesisynthiet');

                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));



                $html2pdf = new HTML2PDF('L', 'A4', 'en');

                $html2pdf->pdf->SetDisplayMode('fullpage');

                $view_output = '<style>table { width: 100%; }</style>' . $view_output;

                $html2pdf->writeHTML($view_output);

                //Edited by Akshay on 14/7/2023
                // Get the total number of pages in the PDF
                $totalPages = $html2pdf->pdf->getPage();

                // Add the footer to each page
                //  for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
                //   $html2pdf->pdf->setPage($pageNumber);

                // Set the font and font size for the footer
                //    $html2pdf->pdf->SetFont('helvetica', '', 10);

                // Set the position for the line
                //    $footerX = 15;
                //    $footerY = $html2pdf->pdf->getPageHeight() - 31;
                //    $footerWidth = $html2pdf->pdf->getPageWidth() - 30;

                //    $html2pdf->pdf->SetXY($footerX, $footerY + 10);
                //    $html2pdf->pdf->Cell($footerWidth, 0, '', 'B', 0, 'C');


                // Set the position for the page number
                //    $html2pdf->pdf->SetXY($footerX, $footerY + 12);
                //    $html2pdf->pdf->Cell($footerWidth, 10, $pageNumber, 0, 0, 'R');
                //   }

                //--------------------------------
                $str_company_code = $this->Session->read('company_code');

                //$html2pdf->Output('_ESI SUMMARY_REPORT.pdf', 'D');
                $html2pdf->Output($str_company_code . '_ESI SUMMARY.pdf', 'D');






                //$this->render('empepf');

                break;

            case 'excel':

                $str_company_code = $this->Session->read('company_code');

                //$file_name = isset($str_company_code) ? $str_company_code . "ESIReport.xlsx" : "ESIReport" . strtotime() . ".xlsx";
                //$str_company_code = 'KWML';

                //$file_name = isset($str_company_code) ? $str_company_code . "_ESI SUMMARY.xlsx" : "PFSummary" . strtotime() . ".xlsx";
                $file_name = isset($str_company_code) ? $str_company_code . "_ESI SUMMARY_" . $f . ".xlsx" : "PFSummary" . strtotime() . ".xlsx";


                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");

                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");

                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                if (empty($arr_salary_for_template)) {


                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, 'ESI STATEMENT');


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->mergeCells('A1:S1');

                    $worksheet->setCellValueByColumnAndRow(0, 2, 'FOR THE PERIOD FROM ' . $fromdate . ' TO ' . $fromdate);


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->mergeCells('A2:S2');
                    $rowcount = 2;
                    //print nodata
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(11);
                    $worksheet->mergeCells('A3:G3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $rowcount++;

                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                } else {
                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, 'ESI STATEMENT');


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(15);
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->mergeCells('A1:G1');


                    $worksheet->setCellValueByColumnAndRow(0, 2, 'FOR THE PERIOD FROM ' . $fromdate . ' TO ' . $fromdate);


                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);

                    $worksheet->mergeCells('A2:G2');

                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(

                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                    );
                    $rowcount = 3;
                    // $worksheet->setCellValueByColumnAndRow(0, 2, "Employer ESI No.:" . $eip);
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    // $worksheet->mergeCells('A2:G2');

                    // $worksheet->getStyle('A2')->getAlignment()->applyFromArray(

                    //  array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)

                    //  );

                    // $rowcount = 3;

                    $i = 0;

                    $col = 0;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Sl No');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'ESI NO');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'Employee ID');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Employee Name');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Days');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'Wages Paid');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'ESI Amount Employee');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, 'ESI Amount Employer');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);

                    //  $rowcount = 4;
                    //   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '(1)');

                    //    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                    //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '(2)');

                    //    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);

                    //   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '(3)');

                    //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                    //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '(4)');

                    //   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    //   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '(5)');

                    //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '(6)');

                    //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    //   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '(7)');

                    //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);


                    $rowcount++;

                    $gross_tot = 0;

                    $pf_salary = 0;

                    $emp_ep = 0;

                    $epr_ep = 0;

                    $esi_t = 0;

                    $ip = 0;

                    $days = 0;

                    $day = 0;

                    $total = 0;

                    $total1 = 0;

                    $split1 = 0;

                    $split2 = 0;

                    $split3 = 0;

                    $esi_sal = 0;

                    $gross = 0;

                    //edited by megha on 10/08/2019 replace $j to outside loop.. serial no. duplication(1)

                    $j = 1;

                    foreach ($arr_salary_for_template as $value) {

                        $arr_daata = $value['summary'];

                        $employees = $value;

                        if (empty($arr_daata))      continue;



                        if (count($value) > 0) {

                            //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, '  WWF  ');

                            //                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);



                            $arr_data = $value;



                            $arr_e = $employees['summary'];

                            $col = 0;

                            foreach ($arr_e as $employee => $val) {

                                if ($val['0']['Esi'] > 0) {
                                    $id = $val['employee_info']['employee_id'];
                                    $empstatus = (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : '';
                                    $name = $val['employee_info']['EmpName'] . $empstatus;
                                    $eno = $val['emp_details']['esi'];

                                    $esi1 = round($val['0']['Esi']);
                                    $esi = round($val['0']['EMPLOYER_ESI']);
                                    //$ec=floatval($val[0]['EMPLOYER_ESI']);
                                    //debug($ec);exit();
                                    $employer_esi = isset($val['0']['EMPLOYER_ESI']) ? $val['0']['EMPLOYER_ESI'] : '';
                                    eval('$esi = ' . $employer_esi . ';');
                                    $esi = round($esi, 1);
                                    $total = round($esi1 + $esi);
                                    $sal = ($val['0']['SALARY'] != '0') ? round($val['0']['SALARY']) : 0;
                                    $gross += round($val['0']['SALARY']);
                                    // $days = $val['0']['present'] + $val['0']['leaves'];
                                    // if($str_criteria_item == 'Units'){
                                    $dayscount = $val['0']['leaves'] + $val['0']['weekoff'] + $val['0']['holiday'] + $val['0']['present'];
                                    $ncp = $val['payroll_master']['calander_days'] - $dayscount;


                                    $days = ceil($val['payroll_master']['calander_days'] - $ncp);

                                    // }else{
                                    // $days = $value['WORKDAYS'];
                                    // }

                                    $split1 += round($val['0']['Esi']);

                                    $split3 += $esi + round($val['0']['Esi']);
                                    $day += round($days);
                                    $excluded = ($val['0']['Esi'] + $esi) / .04;
                                    if ($excluded > $val['0']['SALARY']) {
                                        $excluded = $val['0']['SALARY'];
                                    }
                                    $salary = round($val['0']['SALARY'] - $excluded);
                                    $pf_salary += round($salary);
                                    // $esi_sal += round($excluded);
                                    $excluded1 = isset($val['0']['ESI_EARNING']) ? $val['0']['ESI_EARNING'] : '';
                                    $expressionWithoutPortion = str_replace(['* .0075', '* .75 / 100'], '', $excluded1);
                                    eval('$esiearning = ' . $expressionWithoutPortion . ';');
                                    $esiearning = round($esiearning);
                                    $esi_sal += $esiearning;
                                    //$esi = round($esiearning * 0.0325, 2);
                                    $esival = $esiearning * 0.0325;
                                    $esi = ceil($esival);
                                    $split2 += round($esi);



                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $eno);
                                    //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    //  $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 1, $rowcount)->setValueExplicit($eno, PHPExcel_Cell_DataType::TYPE_STRING);
                                    // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    //  $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col + 2, $rowcount)->setValueExplicit($id, PHPExcel_Cell_DataType::TYPE_STRING);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $eno);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                    //  $worksheet->getStyleByColumnAndRow(($col + 3), $rowcount)->getNumberFormat()->setFormatCode('0.00');
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $days);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                                    // $worksheet->getStyleByColumnAndRow(($col + 4), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $esiearning);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                    //$worksheet->getStyleByColumnAndRow(($col+5), $rowcount)->getNumberFormat()->setFormatCode('0.00');
                                    //   $worksheet->getStyleByColumnAndRow(($col + 5), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $esi1);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                                    //$worksheet->getStyleByColumnAndRow(($col+6), $rowcount)->getNumberFormat()->setFormatCode('0.0');

                                    //$formatted_esi = sprintf("%.2f", $esi);
                                    // $worksheet->getStyleByColumnAndRow(($col + 6), $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $esi);

                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                                    $rowcount++;

                                    $j++;
                                }
                            }
                        }



                        foreach (range('A', 'O') as $columnID) {

                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                        }
                    }

                    $BStyle = array(

                        'borders' => array(

                            'allborders' => array(

                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )

                        )

                    );

                    $row = $rowcount - 1;

                    $objPHPExcel->getActiveSheet()->getStyle('A3:H' . $row)->applyFromArray($BStyle);

                    // Merge cells from column A to D for the total row
                    $worksheet->mergeCells('A' . $rowcount . ':D' . $rowcount);

                    // Set the value of the merged cell to "TOTAL"
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "T O T A L");

                    // Apply bold font style to the cell containing "TOTAL"
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    // Center align the content of the merged cell
                    //$worksheet->getStyle('A' . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                    $objPHPExcel->getActiveSheet()
                        ->getStyle(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount)
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    //$worksheet->getStyleByColumnAndRow(4, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    // $worksheet->getStyleByColumnAndRow(4, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $day);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    //$worksheet->getStyleByColumnAndRow(4, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    // $worksheet->getStyleByColumnAndRow(5, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $esi_sal);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                    $objPHPExcel->getActiveSheet()
                        ->getStyle(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount)
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    //$worksheet->getStyleByColumnAndRow(5, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    // $worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $split1);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                    //$worksheet->getStyleByColumnAndRow(6, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    // $worksheet->getStyleByColumnAndRow(7, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $split2);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6, $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                        ),
                    );

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowcount . ':G' . $rowcount)->applyFromArray($styleArray);
                    //  $rowcount = $rowcount + 2;
                    //$worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                    //   $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Employee Contribution");
                    //   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    //$worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    //   $worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    // $worksheet->setCellValueByColumnAndRow(3, $rowcount, $split1);
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    //  $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                    //   array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    // );

                    //  $rowcount = $rowcount + 1;
                    // $worksheet->mergeCells('A' . $rowcount . ':C' . $rowcount);
                    // $worksheet->setCellValueByColumnAndRow(2, $rowcount, "Employer Contribution");
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    //$worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('0.00');
                    // $worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    // $worksheet->setCellValueByColumnAndRow(3, $rowcount, ceil($split2));
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    // $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                    //   array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    // );
                    //$styleArray = array(
                    //   'borders' => array(
                    //         'bottom' => array(
                    //         'style' => PHPExcel_Style_Border::BORDER_THIN
                    //     ),
                    //   ),
                    // );

                    //  $objPHPExcel->getActiveSheet()->getStyle('C' . $rowcount . ':D' . $rowcount)->applyFromArray($styleArray);
                    //   $rowcount = $rowcount + 1;
                    //$worksheet->mergeCells('A'.$rowcount.':B'.$rowcount);
                    //   $worksheet->setCellValueByColumnAndRow(2, $rowcount, "T O T A L");

                    //   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    //   $t = $split1 + ceil($split2);
                    //   $worksheet->getStyleByColumnAndRow(3, $rowcount)->getNumberFormat()->setFormatCode('#,##0.00');

                    //  $worksheet->setCellValueByColumnAndRow(3, $rowcount, $t);
                    //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                    //  $worksheet->getStyle('D' . $rowcount)->getAlignment()->applyFromArray(
                    //      array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    //   );

                    $objPHPExcel->getActiveSheet()->getStyle('C' . $rowcount . ':H' . $rowcount)->applyFromArray($styleArray);





                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A3:D4')
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                }


                $objPHPExcel->getActiveSheet()->setTitle('ESI SUMMARY');

                /* header footer */

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                /* header footer */

                /* print Set up */

                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed

                header('Content-Type: application/vnd.ms-excel; charset=utf-8');

                header('Content-Disposition: attachment; filename=' . $file_name);



                readfile(dirname(__FILE__) . "/" . $file_name);

                unlink(dirname(__FILE__) . "/" . $file_name);

                break;

            case 'print':

                //   echo "entered in";

                $this->set('mode', 'print');

                $this->render('empesisynthiet');

                break;

            default:

                $this->set('mode', '');

                $this->render('empesisynthiet');

                break;
        }
    }
    //Edited by ASHIN on 10-08-24
    private function generateEPFUPLOADSYNTHIETlabourreport($mode)
    {

        $arr_form_data = $_REQUEST;

        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,salary_head_item_fkey,head_operator FROM emp_salary_slip as ectc
                    where item_part='Direct'  and head_operator = 'Addition' Group by salary_head_item_desc ORDER BY emp_salary_slip_pkey ");

        $array_key = array();
        foreach ($arr_keys as $val) {
            if ($val['ectc']['head_operator'] == 'Addition') {

                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {

                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        //$otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $f = date('Y/m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('M', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);

        $this->set('mname1', $mname);
        $this->set('y1', $year);
        $this->set('from', $from);
        //$date_time = date('d-m-Y H:i');
        $date_time = date('d/m/Y');
        //$this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $conditions = array();

        $conditions[] = 'and ectc.month_year="' . $from . '"';

        $arr_leavepolicygroupids = array();

        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {

            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        $arr_leavepolicydetails_for_template = array();

        $id = implode(' AND ', $conditions);

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))

            $k = 0;

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {

            $resign_condition = " and emp_details.status = '1' ";
        }

        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {


            //edited by ASHIN on 01-08-24
            //edited by ASHIN on 02-08-24
            //$arr_emp_info = $this->EmpCtcTransaction->query(" select * from employee_info where  emp_pkey =  '$leavepolicygroupid' ");
            //edited by sinsiya on 12-06-2024--> join the query with the table attendance_register to get lop days changed ectc.salary_amount into  SUM(ectc.salary_amount) AS sum_amount in sub queries
            $arr_gross = $this->EmpCtcTransaction->query("select employee_info.*,emp_details.status,emp_details.pf, emp_details.eps,abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'              

            AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) ,0)) as EPF ,
            
            abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
            
            AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee esi' and status=1) and end_date_effective is null) ,0))  as Esi ,
            
            abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'  
            
            AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employee www' and status=1) and end_date_effective is null) ,0)) as WWF,
            
            abs(ifnull((select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
            
            AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer epf' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_EPF,
            
            abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect' 
            
            AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer esi' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_ESI,
            
            abs(ifnull((select SUM(ectc.salary_amount) AS sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'indirect'  
            
            AND ectc.end_date_effective is null $id and ectc.salary_head_item_fkey in(select salary_head_item_Fkey  from tax_salary_components where lcase(tax_salary_components_name)= 'employer www' and status=1) and end_date_effective is null) ,0)) as EMPLOYER_WWFS,
            
            (select ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct'
            AND ectc.end_date_effective is null and ectc.month_year='$from' and ectc.salary_head_item_fkey in(select salary_head_item_Fkey 
            from tax_salary_components where lcase(tax_salary_components_name)= 'employee epf' and status=1) and end_date_effective is null) as EPF_earning ,
             
            abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
            
            AND ectc.end_date_effective is null and head_operator = 'Addition' $id ),0)) SALARY, attendance_register.weekoff_total,attendance_register.holiday_total,attendance_register.leave_total,attendance_register.presant_total,payroll_master.calander_days,payroll_master.loss_of_pay from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 
            LEFT JOIN attendance_register ON attendance_register.emp_fkey = employee_info.emp_pkey AND attendance_register.month_year = '$from' AND attendance_register.isdelete = 'N'
            LEFT JOIN payroll_master ON payroll_master.emp_fkey = employee_info.emp_pkey AND payroll_master.month_year = '$from'
            where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' and end_date_effective is null)
            
             and emp_details.branch_Code = '$leavepolicygroupid' $resign_condition  ORDER BY employee_info.EmpName ASC");


            //debug($arr_gross); 


            $arr_salary_for_template[] = $arr_gross;

            $k++;
        }

        //}

        $this->set('keys', $arr_keys);

        $this->set('array_key', $array_key);



        $this->set('arr_salary_for_template', $arr_salary_for_template);

        //  debug($arr_salary_for_template);

        $cr = $arr_form_data['select-criteria1'];

        $this->set('cr', $cr);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $user_name = $this->Session->read('user_name');

        $this->set('user_name', $user_name);

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');

        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $this->set('month', $from);

        //Set informations needed for report



        switch ($mode) {

            case 'pdf':

                //   echo "entered in";

                $this->set('mode', 'pdf');

                $view = new View($this, false);

                $view_output = $view->render('empepfuploadsynthiet');

                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));



                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //  $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');

                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');

                $html2pdf->pdf->SetDisplayMode('fullpage');

                $html2pdf->writeHTML($view_output);

                $html2pdf->Output('PFUploadSummary.pdf', 'D');     //edited by ASHIN on 12-08-24

                $this->render('empepfuploadsynthiet');

                break;
            case 'excel':

                $str_company_code = $this->Session->read('company_code');

                $file_name = isset($str_company_code) ? $str_company_code . "_PFUploadSummary " . $from . ".xlsx" : "PFUploadSummary" . strtotime() . ".xlsx";             //edited by ASHIN on 14-08-24

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");

                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");

                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");

                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
                // Set the left-aligned text
                //$worksheet->setCellValue('A1', $arr_comp_contact_info['CompanyContactInfo']['business_name']);
                //  $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
                //  $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                //    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                //   );

                // Set the right-aligned text
                //  $worksheet->setCellValue('K1', "Report Date: " . $date_time);
                //   $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
                //   $worksheet->getStyle('K1')->getAlignment()->applyFromArray(
                //       array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                //   );

                // Optional: Adjust column widths to ensure visibility
                $worksheet->getColumnDimension('A')->setAutoSize(true);
                $worksheet->getColumnDimension('K')->setAutoSize(true);
                $worksheet->setCellValueByColumnAndRow(0, 1, " PF Upload Summary " . $f);         //edited by ASHIN on 14-08-24

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $worksheet->mergeCells('A1:K1');

                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                );
                $worksheet->setCellValueByColumnAndRow(0, 2, "Report Date:" . $date_time);

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:K2');

                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(

                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                );

                for ($col = 'A'; $col !== 'K'; $col++) {

                    $objPHPExcel->getActiveSheet()

                        ->getColumnDimension($col)

                        ->setAutoSize(true);
                }

                //  $worksheet->mergeCells('A1:K1');

                // $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                //     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                // );

                //$worksheet->mergeCells('A2:K2');
                $rowcount = 2;
                $i = 0;

                //Border style
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );


                foreach ($arr_salary_for_template as $value) {
                    if (count($value) == 0) {
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $worksheet->mergeCells('A3:K3');

                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(

                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)

                        );
                    }
                    if (count($value) > 0) {
                        //$objPHPExcel->getActiveSheet()->freezePane('D3');
                        $rowcount++;

                        $col = 0;
                        //  $worksheet->setCellValueByColumnAndRow(0, 2, $value[0]['employee_info']['branch']);

                        //  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $value[0]['employee_info']['branch']);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('A' . $rowcount . ':K' . $rowcount);
                        $rowcount++;

                        //edited by ASHIN on 31-07-24         

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'UAN');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col, $rowcount)->setWidth('20');

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'MEMBER NAME');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'GROSS WAGES');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'EPF WAGES');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'EPS WAGES');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'EDLI WAGES');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'EPF CONTRI REMITTED');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, 'EPS CONTRI REMITTED');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                        //edited by sinsiya on 12-06-2024
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, 'EPF EPS DIFF REMITTED');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 'NCP DAYS');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 'REFUND OF ADVANCES');

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);

                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, 'Total PF');

                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);


                        $col = 7;

                        $rowcount = $rowcount + 1;

                        $arr_data = $value;

                        $pf_salary = 0;
                        $split1 = 0;
                        $split2 = 0;
                        $split3 = 0;
                        $split5 = 0;

                        $grandtot = 0;

                        // $emp_ep = 0;

                        //  $epr_ep = 0;
                        //   $esi_t = 0;
                        //   $esia_t = 0;
                        $j = 1;
                        $dataFound = false;
                        $totals = array_fill(0, 11, 0);
                        foreach ($arr_data as $val) {
                            if ($val['0']['EPF'] != 0) {
                                $dataFound = true;
                                $col = 0;
                                //edited by sinsiya on 10-07-2024
                                $empstatus = isset($val['emp_details']['status']) && $val['emp_details']['status'] == "2" ? '(Resigned)' : '';
                                $name = $val['employee_info']['EmpName'] . $empstatus;
                                $pf_no = htmlspecialchars($val['emp_details']['pf']);        //EDITED BY ASHIN ON 02-08-24
                                // $empid = $val['employee_info']['employee_id'];
                                $dayscount = $val['attendance_register']['leave_total'] + $val['attendance_register']['weekoff_total'] + $val['attendance_register']['holiday_total'] + $val['attendance_register']['presant_total'];
                                $ncp = $val['payroll_master']['calander_days'] - $dayscount;
                                $dayspresent = $val['payroll_master']['calander_days'] - $ncp;
                                $sal = ($month >= '2020-05' && $month <= '2020-07') ? round($val[0]['EPF'] * 100 / 10, 0) : round($val[0]['EPF'] * 100 / 12, 0);
                                //Edited by Akshay on 3-8-2024
                                $eps_status = isset($val['emp_details']['eps']) ? $val['emp_details']['eps'] : 'Y';
                                //End
                                // echo $sal; 
                                $pf_salary += $sal;
                                //Edited by ASHIN on 03-08-24            
                                //$split1 += round($sal * 8.33 / 100, 2);
                                if ($month >= '2020-05' && $month <= '2020-07') {
                                    $split2 += round($sal * 1.67 / 100, 0);
                                    $epf = round($sal * 10 / 100, 0);
                                    if (false) {
                                        $split5 += round($sal * 10 / 100, 0);
                                    } else {
                                        $split3 += round($sal * 10 / 100, 0);
                                    }
                                } else {
                                    $split2 += round($sal * 3.67 / 100, 0);
                                    $epf = round($sal * 12 / 100, 0);
                                    if (false) {
                                        $split5 += round($sal * 12 / 100, 0);
                                    } else {
                                        $split3 += round($sal * 12 / 100, 0);
                                    }
                                }


                                // debug($val);
                                // exit;

                                $grss_amt = round($val[0]['SALARY']);      //edited by ASHIN on 03-08-24
                                // $grss_amt = round($grss_amt,2);
                                // debug($grss_amt);
                                // $grss_amt = $val['SALARY'];
                                // $grss_amt = round($grss_amt, 2);
                                //$dep = $val['employee_info']['department'];
                                //$deg = $val['employee_info']['designation'];
                                //edited by sinsiya on 12-06-2024 to add lop days
                                //$ncp = $val['attendance_register']['lop_total'];
                                //$branch = $val['employee_info']['branch'];
                                //$uan = $val['emp_details']['pf'];
                                //$gross = round($val['0']['SALARY'], 2);
                                // $epf_salary = round($val['0']['SALARY'], 2) - round($val['0']['EPF'], 2);
                                //                            if ($from >= '2020-05' && $from <= '2020-07') {
                                //                                $esi_salary = round($val['0']['EPF'] * 100 / 10, 2);
                                //                                $sal = round($val['0']['EPF'] * 100 / 10, 2);
                                //                                $www_salary = round($sal * 10 / 100, 2);
                                //                            } else {
                                //                                $esi_salary = round($val['0']['EPF'] * 100 / 12, 2);
                                //                                $sal = round($val['0']['EPF'] * 100 / 12, 2);
                                //                                $www_salary = round($sal * 12 / 100, 2);
                                //                            }
                                $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 0) : round($sal * 12 / 100, 0);
                                // if ($pf <= 1800) {
                                $epf = $pf;
                                //  } else {
                                // $epf = '0';
                                //  }
                                //$pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 2) : round($sal * 12 / 100, 2); 
                                // if ($pf > 1800) {
                                //     $vpf = $pf;
                                // } else {
                                //     $vpf = '0';
                                // }
                                $employerpf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 1.67 / 100, 0) : round($sal * 3.67 / 100, 0);
                                //  $employerpension = round($sal * 8.33 / 100, 0);

                                $eps_contr_rtd = 0;
                                if ($sal > 15000) {
                                    // if($eps_status != 'N'){ 
                                    $employerpension = round(15000 * 8.33 / 100, 0);

                                    //  }else{$employerpension=0;}
                                    //  $epfnew = $val['0']['EPF']- $emppension;

                                } else {
                                    // if($eps_status != 'N'){ 
                                    $employerpension = round($sal * 8.33 / 100, 0);

                                    //}
                                    //else{$employerpension=0;} 
                                    //  $epfnew = $val['0']['EPF']- $emppension;
                                } //$split2+= $epfnew;  echo $epfnew ;
                                // $total6 += $eps_contr_rtd;

                                $split1 += $employerpension;
                                if ($month >= '2020-05' && $month <= '2020-07') {
                                    $pfnew = round($sal * 10 / 100, 0);
                                    $epfnew = round($sal * 1.67 / 100, 0);
                                    $employer_pension = round($sal * 8.33 / 100, 0);
                                    $tot = round($pfnew + $epfnew + $employer_pension);
                                    //echo round($tot);
                                    //debug($tot);exit;
                                } else {
                                    $pfnew = round($sal * 12 / 100, 0);
                                    $epfnew = round($sal * 3.67 / 100, 0);
                                    $employer_pension = round($sal * 8.33 / 100, 0);
                                    $tot = round($pfnew + $epfnew + $employer_pension);
                                    //  echo round($tot);


                                }
                                $grandtot += $tot;
                                $diff = abs($epf - $employer_pension);
                                //edited by ASHIN on 21-08-24               
                                $displayed_eps_contr_rtd = 0; // Initialize the variable
                                //edited by ASHIN on 06-08-24         
                                $sal1 = isset($val['0']['EPF_earning']) ? $val['0']['EPF_earning'] : '';
                                if ($sal1 != '') {
                                    $expressionWithoutPortion = str_replace(['* .12', '* 12/100'], '', $sal1);
                                    eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                    $sal1 = floor($epf_earnings);
                                }
                                if ($epf == 1800) {
                                    $sal1 = 15000;
                                }

                                $sal2 = isset($val['0']['EPF_earning']) ? $val['0']['EPF_earning'] : '';
                                if ($sal2 != '') {
                                    $expressionWithoutPortion = str_replace(['* .12', '* 12/100'], '', $sal1);
                                    eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                    $sal2 = floor($epf_earnings);
                                }
                                if ($sal2 > 15000) {
                                    $sal2 = 15000;
                                }
                                //                            if ($from >= '2020-05' && $from <= '2020-07') {
                                //                                $esi = round($sal * 1.67 / 100, 2);
                                //                            } else {
                                //                                $esi = round($sal * 3.67 / 100, 2);
                                //                            }
                                //                            $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 2) : round($sal * 12 / 100, 2); 
                                //                            if($pf < 1800) { $epf=$pf; }else{$epf= '0';}
                                //                            $pf = ($month >= '2020-05' && $month <= '2020-07') ? round($sal * 10 / 100, 2) : round($sal * 12 / 100, 2); 
                                //                            if($vpf > 1800){ $vpf=$pf; } else{$vpf='0';}
                                //                            $esia = round($sal * .5 / 100, 2);
                                //                            $www = round($val['0']['EMPLOYER_WWFS'], 2);
                                //                            $j = $j + 1;
                                //                            $gross_tot += $gross;
                                //
                                //                            $pf_sal_t += $sal;
                                //
                                //                            $emp_ep += $www_salary;
                                //
                                //                            $epr_ep += $epf;
                                //
                                //                            $esi_t += $esi;
                                //
                                //                            $esia_t += $esia;
                                //

                                //edited by ASHIN on 02-08-24
                                //edited by ASHIN on 06-08-24
                                $objPHPExcel->getActiveSheet()->SetCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $pf_no, PHPExcel_Cell_DataType::TYPE_STRING);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $grss_amt);
                                $totals[2] += round($grss_amt);     //edited by ASHIN on 03-08-24
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                //Edited by Akshay on 3-8-2024
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $sal1);   //edited by ASHIN on 13-08-24
                                $totals[3] += $sal1;        //edited by ASHIN on 13-08-24
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                if ($eps_status != 'N') {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $sal2);
                                    $totals[4] += $sal2;
                                } else {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 0);
                                    $totals[4] += 0;
                                }
                                //End
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $sal2);
                                $totals[5] += $sal2;     //edited by ASHIN on 03-08-24 
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $epf);
                                $totals[6] += $epf;
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                                $eps_contr_rtd = 0;
                                if ($sal1 > 15000) {
                                    if ($eps_status != 'N') {
                                        $eps_contr_rtd = round(15000 * 8.33 / 100, 0);
                                    } else {
                                        $eps_contr_rtd = 0;
                                    }
                                    //  $epfnew = $val['0']['EPF']- $emppension;

                                } else {
                                    if ($eps_status != 'N') {
                                        $eps_contr_rtd = round($sal1 * 8.33 / 100, 0);
                                    } else {
                                        $eps_contr_rtd = 0;
                                    }
                                    //  $epfnew = $val['0']['EPF']- $emppension;
                                } //$split2+= $epfnew;  echo $epfnew ;
                                $totals[7] += $eps_contr_rtd;

                                //edited by ASHIN on 21-08-24 
                                //if($eps_status != 'N'){
                                // $displayed_eps_contr_rtd = $employerpension;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $eps_contr_rtd);
                                //  $totals[7] += $displayed_eps_contr_rtd ;
                                // }else{
                                //  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, 0);
                                //  $totals[7] += 0;
                                // }
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $diff);
                                // $totals[8] += $diff;
                                // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $diff = abs($eps_contr_rtd - round($epf));
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $diff);
                                $totals[8] += $diff;
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $ncp);
                                $totals[9] += $ncp;
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, '');
                                //$totals[10] += 0;
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $tot);
                                // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $j++;

                                $rowcount++;
                            }
                        }

                        if (!$dataFound) {
                            // $rowcount++; // Move to the next row

                            // Set "No Data Found" message
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowcount, "No Data available under the selected criteria");     //edited by ASHIN on 03-08-24
                            $worksheet->mergeCells('A' . $rowcount . ':K' . $rowcount);
                            // Optionally, apply center alignment
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            // Optionally, apply additional styling or formatting as needed

                            // Increment $rowcount further if necessary for additional content after "No Data Found"
                            // $rowcount++;
                        }
                        if ($pf_salary != 0) {
                            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");

                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                            $worksheet->mergeCells('A' . $rowcount . ':B' . $rowcount);

                            $worksheet->getStyle('K' . $rowcount)->getAlignment()->applyFromArray(

                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                            );
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount,  $totals[2]);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $totals[3]);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $totals[4]);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $totals[5]);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $totals[6]);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $totals[7]);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $totals[8]);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $totals[9]);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, '');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $grandtot);
                            // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                            // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }


                        //edited by sinsiya on 12-06-2024 changing index value ended
                        $col = 10;
                        // $rowcount++;
                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
                        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $columnLetter . $rowcount)->applyFromArray($styleArray);
                    }
                }
                // exit;
                //                                     
                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('PF Upload Summary');       //edited by ASHIN on 14-08-24

                /* header footer */

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);

                /* header footer */

                /* print Set up */

                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);

                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed

                header('Content-Type: application/vnd.ms-excel; charset=utf-8');

                header('Content-Disposition: attachment; filename=' . $file_name);



                readfile(dirname(__FILE__) . "/" . $file_name);

                unlink(dirname(__FILE__) . "/" . $file_name);

                break;

            case 'print':

                //   echo "entered in";

                $this->set('mode', 'print');

                $this->render('empepfuploadsynthiet');

                break;

            default:

                $this->set('mode', '');

                $this->render('empepfuploadsynthiet');

                break;
        }
    }

    //edited by athira on 11-04-2025
    private function generateprofessionaltaxsalaryreport()
    {
        $arr_form_data = $_REQUEST;
        // var_dump($arr_form_data); exit;
        // debug($arr_form_data);
        $criteria = $arr_form_data['hidden-criteria1'];
        $year = $arr_form_data['year'];
        $from = $arr_form_data['report_from'];
        //$to = $arr_form_data['reportsto'];
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        //$fin_year_arr = $this->FinancialYear->find("first", array("fields" => array('start_month','end_month'), 
        // "conditions" => array('is_current_finyear' => 'Y', 'vattr1' => '1', 'Year_status' => 'OPEN', 'fin_year' => $year)));
        $fin_year_arr = $this->FinancialYear->find("first", array(
            "fields" => array('start_month', 'end_month'),
            "conditions" => array('vattr1' => '1', 'fin_year' => $year)
        ));
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }

        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $start = date("Y-m", strtotime($fin_year_arr['FinancialYear']['start_month']));
        $end = date("Y-m", strtotime($fin_year_arr['FinancialYear']['end_month']));
        $conditions = array();
        if ($arr_form_data['report_from'] == 1) {
            $startmonth = date("Y-m", strtotime($fin_year_arr['FinancialYear']['start_month']));
            $start_month = $startmonth;
            $months[] = $startmonth;
            for ($k = 1; $k < 6; $k++) {
                $months[] = date('Y-m', strtotime("+$k months", strtotime($startmonth)));
            }
            $conditions[] = " between '$start_month' and '$startmonth' ";
        } else {
            $endmonth = date("Y-m", strtotime($fin_year_arr['FinancialYear']['end_month']));

            for ($k = 5; $k > 0; $k--) {
                if ($k == 5) {
                    $start = date('Y-m', strtotime("-$k months", strtotime($endmonth)));
                }
                $months[] = date('Y-m', strtotime("-$k months", strtotime($endmonth)));
            }
            $months[] = $endmonth;
            $conditions[] = " between '$start' and '$endmonth' ";
        }

        $this->set('months', $months);
        $this->set('criteria', $criteria);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $resign_condition = " and emp_details.status in ('1','2') ";
        } else {
            $resign_condition = " and emp_details.status = '1' ";
        }
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $arr_salary_for_template = array();
        $month_count = count($months);


        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if ($criteria == 'EmployeeDetails') {
                $arr_gross = array();
                $i = 0;


                foreach ($months as $mon) {
                    $joining_date = $this->EmpCtcTransaction->query("SELECT joining_date from emp_proff where emp_fkey='$leavepolicygroupid'");
                    $joining_date = $joining_date['0']['emp_proff']['joining_date'];




                    $i++;
                    $standard_amount = $this->EmpCtcTransaction->query("select ifnull(round(sum(structure_det_value)),0) as standard_salary
                    from emp_salary_structure where emp_fkey='$leavepolicygroupid'  and end_date_effective is null and head_operator<>'Deduction' 
                    and end_date_effective is null and item_part <> 'Indirect' 
                    and salary_head_item_Fkey not in (select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey in(3,4,5) )
                    and salary_head_item_Fkey in (select salary_head_item_pkey from salary_head_items where head_fkey=1)");

                    $standard_salary = isset($standard_amount[0][0]['standard_salary']) ? $standard_amount[0][0]['standard_salary'] : 0;

                    $arr = $this->EmpCtcTransaction->query("
                                                    SELECT 
                                                    employee_info.*,
                                                    termination.last_approved_working_date,
                                                    user_credentials.user_id,
                                                    SUM(ABS(emp_salary_slip.salary_amount)) AS monthly_salary
                                                    FROM 
                                                    employee_info
                                                    LEFT JOIN termination  ON employee_info.emp_pkey = termination.emp_fkey
                                                    LEFT JOIN user_credentials  ON employee_info.emp_pkey = user_credentials.emp_fkey
                                                    LEFT JOIN emp_salary_slip
                                            
                                                    ON employee_info.emp_pkey = emp_salary_slip.emp_fkey
                                                    AND emp_salary_slip.month_year = '$mon'
                                                    AND emp_salary_slip.end_date_effective IS NULL
                                                    AND emp_salary_slip.head_operator <> 'Deduction'
                                                    AND emp_salary_slip.item_part <> 'Indirect'
                                                    AND EXISTS (
                                                    SELECT 1 
                                                    FROM emp_salary_structure 
                                                    WHERE emp_salary_structure.emp_fkey = employee_info.emp_pkey
                                                    AND emp_salary_structure.salary_head_item_fkey = emp_salary_slip.salary_head_item_fkey
                                                    AND emp_salary_structure.end_date_effective IS NULL 
                                                    AND emp_salary_structure.head_operator <> 'Deduction' 
                                                    AND emp_salary_structure.item_part <> 'Indirect'
                                                    AND emp_salary_structure.salary_head_item_fkey NOT IN (
                                                        SELECT salary_head_item_fkey 
                                                        FROM tax_salary_components 
                                                        WHERE tax_salary_components_pkey IN (3, 4, 5)
                                                    )
                                                )
                                                    WHERE 
                                                        employee_info.emp_pkey = '$leavepolicygroupid'
                                                    GROUP BY 
                                                        employee_info.emp_pkey

                                                    ORDER BY employee_info.EmpName
            
            ");

                    $arr['0']['month'] = $mon;


                    $payroll_processed = $this->EmpCtcTransaction->query("SELECT action from payroll_master where emp_fkey='$leavepolicygroupid' and month_year='$mon'");


                    if (!empty($arr)) {


                        $smonth = $months[0];
                        $emonth = $months[5];
                        $joining_year_month = date('Y-m', strtotime($joining_date));

                        if (strtotime($mon) < strtotime($joining_year_month)) {
                            // Employee hasn't joined yet, salary for months prior to joining is 0
                            $arr[0][0]['standard_salary'] = 0;
                        } elseif (strtotime($mon) == strtotime($joining_year_month)) {
                            // Employee joined in this month, assign monthly salary for the joining month
                            if (!empty($payroll_processed)) {
                                $arr[0][0]['standard_salary'] = $arr[0][0]['monthly_salary']; // Use monthly salary
                            } else {
                                $arr[0][0]['standard_salary'] = $standard_salary; // Fallback if payroll isn't processed
                            }
                        } else {
                            // For all months after the joining month, assign standard salary
                            if (!empty($payroll_processed) && strtotime($mon) == strtotime($emonth)) {
                                $arr[0][0]['standard_salary'] = $arr[0][0]['monthly_salary']; // If payroll processed
                            } else {
                                $arr[0][0]['standard_salary'] = $standard_salary; // Standard salary for remaining months
                            }
                        }
                    }
                    $arr_gross[] = $arr;
                }


                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = $arr_gross;
                    $k++;
                }
                $arr_salary_for_templates = $arr_salary_for_template;
            } else {
                $arr_gross = array();


                $employees = $this->EmpCtcTransaction->query("SELECT emp_pkey,EmpName from employee_info where branch_code ='$leavepolicygroupid' ORDER BY employee_info.EmpName ");
                foreach ($employees as $employee) {
                    $emp_pkey = $employee['employee_info']['emp_pkey'];
                    $arr_gross = array(); // reset for each employee
                    $i = 0;
                    foreach ($months as $mon) {
                        $joining_date = $this->EmpCtcTransaction->query("SELECT joining_date from emp_proff where emp_fkey='$emp_pkey'");
                        $joining_date = $joining_date['0']['emp_proff']['joining_date'];
                        $i++;


                        $standard_amount = $this->EmpCtcTransaction->query("select ifnull(round(sum(structure_det_value)),0) as standard_salary
                                                                from emp_salary_structure where emp_fkey='$emp_pkey'  and end_date_effective is null and head_operator<>'Deduction' 
                                                                and end_date_effective is null and item_part <> 'Indirect' 
                                                                and salary_head_item_Fkey not in (select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey in(3,4,5) )
                                                                and salary_head_item_Fkey in (select salary_head_item_pkey from salary_head_items where head_fkey=1)");
                        $standard_salary = isset($standard_amount[0][0]['standard_salary']) ? $standard_amount[0][0]['standard_salary'] : 0;

                        $arr = $this->EmpCtcTransaction->query("
                                                            SELECT 
                                                                employee_info.*,
                                                                termination.last_approved_working_date,
                                                                user_credentials.user_id,
                                                                SUM(ABS(emp_salary_slip.salary_amount)) AS monthly_salary
                                                            FROM 
                                                                employee_info
                                                            LEFT JOIN termination  ON employee_info.emp_pkey = termination.emp_fkey
                                                            LEFT JOIN user_credentials  ON employee_info.emp_pkey = user_credentials.emp_fkey
                                                            LEFT JOIN 
                                                                emp_salary_slip
                                                                ON employee_info.emp_pkey = emp_salary_slip.emp_fkey
                                                                AND emp_salary_slip.month_year = '$mon'
                                                                AND emp_salary_slip.end_date_effective IS NULL
                                                                AND emp_salary_slip.head_operator <> 'Deduction'
                                                                AND emp_salary_slip.item_part <> 'Indirect'
                                                                AND EXISTS (
                                                                    SELECT 1 
                                                                    FROM emp_salary_structure 
                                                                    WHERE emp_salary_structure.emp_fkey = employee_info.emp_pkey
                                                                    AND emp_salary_structure.salary_head_item_fkey = emp_salary_slip.salary_head_item_fkey
                                                                    AND emp_salary_structure.end_date_effective IS NULL 
                                                                    AND emp_salary_structure.head_operator <> 'Deduction' 
                                                                    AND emp_salary_structure.item_part <> 'Indirect'
                                                                    AND emp_salary_structure.salary_head_item_fkey NOT IN (
                                                                        SELECT salary_head_item_fkey 
                                                                        FROM tax_salary_components 
                                                                        WHERE tax_salary_components_pkey IN (3, 4, 5)
                                                                    )
                                                                )
                                                            WHERE 
                                                                employee_info.emp_pkey = '$emp_pkey'
                                                            GROUP BY 
                                                                employee_info.emp_pkey
                       
                    ");



                        foreach ($arr as $data) {
                            $data['month'] = $mon;

                            $payroll_processed = $this->EmpCtcTransaction->query("SELECT action from payroll_master where emp_fkey='$emp_pkey' and month_year='$mon'");

                            $smonth = $months[0];
                            $emonth = $months[5];
                            $joining_year_month = date('Y-m', strtotime($joining_date));
                            if (strtotime($mon) < strtotime($joining_year_month)) {
                                // Employee hasn't joined yet, salary for months prior to joining is 0
                                $data[0]['standard_salary'] = 0;
                            } elseif (strtotime($mon) == strtotime($joining_year_month)) {
                                // Employee joined in this month, assign monthly salary for the joining month
                                if (!empty($payroll_processed)) {
                                    $data[0]['standard_salary'] = $data[0]['monthly_salary']; // Use monthly salary
                                } else {
                                    $data[0]['standard_salary'] = $standard_salary; // Fallback if payroll isn't processed
                                }
                            } else {
                                // For all months after the joining month, assign standard salary
                                if (!empty($payroll_processed) && strtotime($mon) == strtotime($emonth)) {
                                    $data[0]['standard_salary'] = $data[0]['monthly_salary']; // If payroll processed
                                } else {
                                    $data[0]['standard_salary'] = $standard_salary; // Standard salary for remaining months
                                }
                            }



                            $arr_gross[]['0'] = $data;
                        }
                    }

                    if (!empty($arr_gross)) {
                        $arr_salary_for_template[$k] = $arr_gross;
                        $k++;
                    }
                }

                $arr_salary_for_templates = $arr_salary_for_template;
            }
        }
        // debug($arr_salary_for_template);
        $this->set('arr_salary_for_template', $arr_salary_for_templates);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        $str_company_code = $this->Session->read('company_code');
        //edited by athira on 19-04-2025    
        $file_name = isset($str_company_code) ? $str_company_code . "ProfessionalTaxSalary.xlsx" : "ProfessionalTaxSalary" . strtotime() . ".xlsx";
        //end
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Professional Tax Report By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        //edited by athira on 19-04-2025
        $worksheet->setShowGridlines(false);
        //end
        $worksheet->setCellValueByColumnAndRow(0, 1, "Professional Tax Salary Report");
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
        for ($col = 'A'; $col !== 'O'; $col++) {
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
        }
        $worksheet->mergeCells('A1:Q1');
        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $rowcount = 1;
        if (count($arr_salary_for_template) !== 0) {
            $rowcount++;
            $col = 0;

            $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
            $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
            $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            );

            $worksheet->setCellValueByColumnAndRow(9, $rowcount, "Professional Tax Salary Details");
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
            $worksheet->mergeCells('J' . $rowcount . ':Q' . $rowcount);
            $worksheet->getStyle('J' . $rowcount)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            );

            $rowcount++;
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee ID   ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  User ID   ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Employee Name  ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Joining Date ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Branch  ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Department ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Designation ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Termination Date   ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
            $i = 9;
            foreach ($months as $month) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $i) . $rowcount, $month);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + $i, $rowcount)->getFont()->setBold(true);
                $i++;
            }
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $i) . $rowcount, '  Total PT Salary ');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + $i, $rowcount)->getFont()->setBold(true);

            $rowcount = $rowcount + 1;
            $i = 0;
            $sum = 0;

            foreach ($arr_salary_for_template as $values) {
                $total_amount = 0;
                foreach ($values as $value) {
                    $amount = isset($value['0']['0']['monthly_salary']) ? $value['0']['0']['monthly_salary'] : $value['0']['0']['standard_salary'];
                    $total_amount += $amount;
                }
                $sum += $total_amount;
                if ($total_amount > 0) {
                    $i = $i + 1;
                    $emp = $values['0']['0']['employee_info']['emp_pkey'];
                    $user_id = $values['0']['0']['user_credentials']['user_id'];
                    $emp_id = $values['0']['0']['employee_info']['employee_id'];
                    $emp_name = $values['0']['0']['0']['EmpName'];
                    $joining_date = isset($values['0']['0']['employee_info']['joining_date']) ? date('d-m-Y', strtotime($values['0']['0']['employee_info']['joining_date'])) : " ";
                    $branch = $values['0']['0']['employee_info']['branch'];
                    $department = $values['0']['0']['employee_info']['department'];
                    $designation = $values['0']['0']['employee_info']['designation'];
                    $termination_date = isset($values['0']['0']['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($values['0']['0']['termination']['last_approved_working_date'])) : " ";
                    $col = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $i);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $emp_id);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $user_id);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $emp_name);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $joining_date);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $branch);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $department);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $designation);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $termination_date);
                    $j = 9;

                    foreach ($values as $value) {

                        $amount = isset($value['0']['0']['monthly_salary']) ? $value['0']['0']['monthly_salary'] : $value['0']['0']['standard_salary'];


                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $j) . $rowcount, $amount);
                        $j = $j + 1;
                    }
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $j) . $rowcount, $total_amount);
                    $worksheet->mergeCells('P' . $rowcount . ':Q' . $rowcount);

                    $col = 9;
                    $rowcount++;
                }
            }
            //   if ($sum == 0){
            //              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records Found');
            //             $worksheet->mergeCells('A' . $rowcount . ':Q' . $rowcount);
            //              $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
            //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            // );    
            //   }
            // else{
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'TOTAL');
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
            $worksheet->mergeCells('C' . $rowcount . ':I' . $rowcount);
            $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
            );
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $sum);
            $worksheet->mergeCells('P' . $rowcount . ':Q' . $rowcount);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
            // }

            $highestRow = $worksheet->getHighestRow(); // Last row with content
            $highestColumn = $worksheet->getHighestColumn(); // Last column with content
            $range = 'A1:' . $highestColumn . $highestRow;

            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'), // Black
                    ),
                ),
            );

            $worksheet->getStyle($range)->applyFromArray($styleArray);


            $objPHPExcel->getActiveSheet()->setTitle('ProfessionalTaxSalary');
            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save(dirname(__FILE__) . "/" . $file_name);
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $file_name);

            readfile(dirname(__FILE__) . "/" . $file_name);
            unlink(dirname(__FILE__) . "/" . $file_name);
        }
    }
    //end
    //edited by athira on 11-04-2025
    private function generateprofessionaltaxsalaryreportKWMT($mode)
    {
        $arr_form_data = $_REQUEST;
        // var_dump($arr_form_data); exit;
        // debug($arr_form_data);
        $criteria = $arr_form_data['hidden-criteria1'];
        $year = $arr_form_data['year'];
        $from = $arr_form_data['report_from'];
        //$to = $arr_form_data['reportsto'];
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        //$fin_year_arr = $this->FinancialYear->find("first", array("fields" => array('start_month','end_month'), 
        // "conditions" => array('is_current_finyear' => 'Y', 'vattr1' => '1', 'Year_status' => 'OPEN', 'fin_year' => $year)));
        $fin_year_arr = $this->FinancialYear->find("first", array(
            "fields" => array('start_month', 'end_month'),
            "conditions" => array('vattr1' => '1', 'fin_year' => $year, 'status' => '1') // Edited by Akshay on 18-6-2025
        ));
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }

        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $start = date("Y-m", strtotime($fin_year_arr['FinancialYear']['start_month']));
        $end = date("Y-m", strtotime($fin_year_arr['FinancialYear']['end_month']));
        $conditions = array();
        if ($arr_form_data['report_from'] == 1) {
            $startmonth = date("Y-m", strtotime($fin_year_arr['FinancialYear']['start_month']));
            $start_month = $startmonth;
            $months[] = $startmonth;
            for ($k = 1; $k < 6; $k++) {
                $months[] = date('Y-m', strtotime("+$k months", strtotime($startmonth)));
            }
            $conditions[] = " between '$start_month' and '$startmonth' ";
        } else {
            $endmonth = date("Y-m", strtotime($fin_year_arr['FinancialYear']['end_month']));

            for ($k = 5; $k > 0; $k--) {
                if ($k == 5) {
                    $start = date('Y-m', strtotime("-$k months", strtotime($endmonth)));
                }
                $months[] = date('Y-m', strtotime("-$k months", strtotime($endmonth)));
            }
            $months[] = $endmonth;
            $conditions[] = " between '$start' and '$endmonth' ";
        }

        $this->set('months', $months);
        $this->set('criteria', $criteria);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $resign_condition = " and employee_info.emp_status  in ('1','2') ";
        } else {
            $resign_condition = " and employee_info.emp_status  = '1' ";
        }
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $arr_salary_for_template = array();
        $month_count = count($months);


        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if ($criteria == 'EmployeeDetails') {
                $arr_gross = array();
                $i = 0;


                foreach ($months as $mon) {
                    $joining_date = $this->EmpCtcTransaction->query("SELECT joining_date from emp_proff where emp_fkey='$leavepolicygroupid'");
                    $joining_date = $joining_date['0']['emp_proff']['joining_date'];




                    $i++;

                    $projected_amount = $this->EmpCtcTransaction->query("select ifnull(round(sum(structure_det_value)),0) as projected_salary
            from emp_salary_structure where emp_fkey='$leavepolicygroupid'  and end_date_effective is null and head_operator<>'Deduction' 
            and end_date_effective is null and item_part <> 'Indirect' 
                and salary_head_item_Fkey not in (select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey in(3,4,26) )
                and salary_head_item_Fkey in (select salary_head_item_pkey from salary_head_items where head_fkey=1)");


                    $projected_salary = isset($projected_amount[0][0]['projected_salary']) ? $projected_amount[0][0]['projected_salary'] : 0;


                    $arr = $this->EmpCtcTransaction->query("
                SELECT 
                    employee_info.*,
                    termination.last_approved_working_date,
                    user_credentials.user_id,
                    SUM(emp_salary_slip.salary_amount) AS monthly_salary
                FROM 
                    employee_info
                LEFT JOIN termination ON employee_info.emp_pkey = termination.emp_fkey
                LEFT JOIN user_credentials ON employee_info.emp_pkey = user_credentials.emp_fkey
                LEFT JOIN emp_salary_slip ON employee_info.emp_pkey = emp_salary_slip.emp_fkey
                    AND emp_salary_slip.end_date_effective IS NULL
                    AND emp_salary_slip.month_year ='$mon'
                    AND emp_salary_slip.item_part <> 'Indirect'
                    AND (
                        emp_salary_slip.salary_head_item_Fkey IN (
                            SELECT salary_head_item_pkey 
                            FROM salary_head_items 
                            WHERE head_fkey IN (1, 9)
                        )
                        OR emp_salary_slip.salary_head_item_Fkey IN (152)
                    )
                    AND emp_salary_slip.salary_head_item_Fkey NOT IN (
                        SELECT salary_head_item_fkey 
                        FROM tax_salary_components 
                        WHERE tax_salary_components_pkey IN (3, 4, 26)
                    )
                WHERE 
                    employee_info.emp_pkey = '$leavepolicygroupid' $resign_condition
                GROUP BY 
                    employee_info.emp_pkey
                ORDER BY 
                    employee_info.EmpName
            ");




                    $arr['0']['month'] = $mon;


                    $payroll_processed = $this->EmpCtcTransaction->query("SELECT action from payroll_master where emp_fkey='$leavepolicygroupid' and month_year='$mon'");


                    if (!empty($arr)) {


                        $smonth = $months[0];
                        $emonth = $months[5];
                        $joining_year_month = date('Y-m', strtotime($joining_date));


                        if (strtotime($mon) < strtotime($joining_year_month)) {
                            // Employee hasn't joined yet
                            $arr[0][0]['monthly_salary'] = 0;
                            $arr[0][0]['projected_salary'] = 0;
                        } else {
                            if ($payroll_processed && $payroll_processed[0]['payroll_master']['action'] == 'Processed') {
                                // If payroll is processed, show monthly_salary (even if it's 0 or null)
                                $arr[0][0]['projected_salary'] = $arr[0][0]['monthly_salary'];
                            } else {
                                // If not processed, fallback to projected_salary
                                $arr[0][0]['projected_salary'] = $projected_salary;
                            }
                        }
                    }
                    $arr_gross[] = $arr;
                }


                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = $arr_gross;
                    $k++;
                }
                $arr_salary_for_templates = $arr_salary_for_template;
            } else {
                $arr_gross = array();


                $employees = $this->EmpCtcTransaction->query("SELECT emp_pkey,EmpName from employee_info where branch_code ='$leavepolicygroupid' ORDER BY employee_info.EmpName ");
                foreach ($employees as $employee) {
                    $emp_pkey = $employee['employee_info']['emp_pkey'];
                    $arr_gross = array(); // reset for each employee
                    $i = 0;
                    foreach ($months as $mon) {
                        $joining_date = $this->EmpCtcTransaction->query("SELECT joining_date from emp_proff where emp_fkey='$emp_pkey'");
                        $joining_date = $joining_date['0']['emp_proff']['joining_date'];
                        $i++;


                        $projected_amount = $this->EmpCtcTransaction->query("select ifnull(round(sum(structure_det_value)),0) as projected_salary
            from emp_salary_structure where emp_fkey='$emp_pkey'  and end_date_effective is null and head_operator<>'Deduction' 
            and end_date_effective is null and item_part <> 'Indirect' 
                and salary_head_item_Fkey not in (select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey in(3,4,26) )
                and salary_head_item_Fkey in (select salary_head_item_pkey from salary_head_items where head_fkey=1)");
                        $projected_salary = isset($projected_amount[0][0]['projected_salary']) ? $projected_amount[0][0]['projected_salary'] : 0;

                        $arr = $this->EmpCtcTransaction->query("
            SELECT 
                employee_info.*,
                termination.last_approved_working_date,
                user_credentials.user_id,
                SUM(emp_salary_slip.salary_amount) AS monthly_salary
            FROM 
                employee_info
            LEFT JOIN termination ON employee_info.emp_pkey = termination.emp_fkey
            LEFT JOIN user_credentials ON employee_info.emp_pkey = user_credentials.emp_fkey
            LEFT JOIN emp_salary_slip ON employee_info.emp_pkey = emp_salary_slip.emp_fkey
                AND emp_salary_slip.end_date_effective IS NULL
                AND emp_salary_slip.month_year ='$mon'
                AND emp_salary_slip.item_part <> 'Indirect'
                AND (
                    emp_salary_slip.salary_head_item_Fkey IN (
                        SELECT salary_head_item_pkey 
                        FROM salary_head_items 
                        WHERE head_fkey IN (1, 9)
                    )
                    OR emp_salary_slip.salary_head_item_Fkey IN (152)
                )
                AND emp_salary_slip.salary_head_item_Fkey NOT IN (
                    SELECT salary_head_item_fkey 
                    FROM tax_salary_components 
                    WHERE tax_salary_components_pkey IN (3, 4, 26)
                )
            WHERE 
                employee_info.emp_pkey = '$emp_pkey' $resign_condition
            GROUP BY 
                employee_info.emp_pkey
            ORDER BY 
                employee_info.EmpName
        ");




                        foreach ($arr as $data) {
                            $data['month'] = $mon;

                            $payroll_processed = $this->EmpCtcTransaction->query("SELECT action from payroll_master where emp_fkey='$emp_pkey' and month_year='$mon'");

                            $smonth = $months[0];
                            $emonth = $months[5];
                            $joining_year_month = date('Y-m', strtotime($joining_date));


                            if (strtotime($mon) < strtotime($joining_year_month)) {
                                // Employee hasn't joined yet
                                $data[0]['monthly_salary'] = 0;
                                $data[0]['projected_salary'] = 0;
                            } else {
                                if ($payroll_processed && $payroll_processed[0]['payroll_master']['action'] == 'Processed') {
                                    // If payroll is processed, show monthly_salary (even if it's 0 or null)
                                    $data[0]['projected_salary'] = $data[0]['monthly_salary'];
                                } else {
                                    // If not processed, fallback to projected_salary
                                    $data[0]['projected_salary'] = $projected_salary;
                                }
                            }
                            $arr_gross[]['0'] = $data;
                        }
                    }

                    if (!empty($arr_gross)) {
                        $arr_salary_for_template[$k] = $arr_gross;
                        $k++;
                    }
                }

                $arr_salary_for_templates = $arr_salary_for_template;
            }
        }

        // debug($arr_salary_for_template);
        $this->set('arr_salary_for_template', $arr_salary_for_templates);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        $str_company_code = $this->Session->read('company_code');
        switch ($mode) {
            case 'excel':
                //edited by athira on 19-04-2025    
                $file_name = isset($str_company_code) ? $str_company_code . "ProfessionalTaxSalary.xlsx" : "ProfessionalTaxSalary" . strtotime() . ".xlsx";
                //end
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Professional Tax Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                //edited by athira on 19-04-2025
                $worksheet->setShowGridlines(false);
                //end
                $worksheet->setCellValueByColumnAndRow(0, 1, "Professional Tax Salary Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'O'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:Q1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 1;
                if (count($arr_salary_for_template) !== 0) {
                    $rowcount++;
                    $col = 0;

                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Employee Details");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $worksheet->setCellValueByColumnAndRow(9, $rowcount, "Professional Tax Salary Details");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('J' . $rowcount . ':Q' . $rowcount);
                    $worksheet->getStyle('J' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $rowcount++;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Employee ID   ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  User ID   ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Employee Name  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Joining Date ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Branch  ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Department ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Designation ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Termination Date   ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                    $i = 9;
                    foreach ($months as $month) {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $i) . $rowcount, $month);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + $i, $rowcount)->getFont()->setBold(true);
                        $i++;
                    }
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $i) . $rowcount, '  Total PT Salary ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + $i, $rowcount)->getFont()->setBold(true);

                    $rowcount = $rowcount + 1;
                    $i = 0;
                    $sum = 0;


                    foreach ($arr_salary_for_template as $values) {
                        $total_amount = 0;
                        foreach ($values as $value) {
                            // $amount = $value['0']['0']['projected_salary'];
                            $amount = !empty($value['0']['0']['monthly_salary']) ? $value['0']['0']['monthly_salary'] : $value['0']['0']['projected_salary'];

                            $total_amount += $amount;
                        }
                        $sum += $total_amount;
                        if ($total_amount > 0) {
                            $i = $i + 1;
                            $emp = $values['0']['0']['employee_info']['emp_pkey'];
                            $user_id = $values['0']['0']['user_credentials']['user_id'];
                            $emp_id = $values['0']['0']['employee_info']['employee_id'];
                            $emp_name = $values['0']['0']['0']['EmpName'];
                            $joining_date = isset($values['0']['0']['employee_info']['joining_date']) ? date('d-m-Y', strtotime($values['0']['0']['employee_info']['joining_date'])) : " ";
                            $branch = $values['0']['0']['employee_info']['branch'];
                            $department = $values['0']['0']['employee_info']['department'];
                            $designation = $values['0']['0']['employee_info']['designation'];
                            $termination_date = isset($values['0']['0']['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($values['0']['0']['termination']['last_approved_working_date'])) : " ";
                            $col = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $i);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $emp_id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $user_id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $emp_name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $joining_date);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $branch);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $department);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $designation);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $termination_date);
                            $j = 9;

                            foreach ($values as $value) {

                                // $amount = $value['0']['0']['projected_salary'];
                                $amount = !empty($value['0']['0']['monthly_salary']) ? $value['0']['0']['monthly_salary'] : $value['0']['0']['projected_salary'];



                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $j) . $rowcount, $amount);
                                $j = $j + 1;
                            }
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + $j) . $rowcount, $total_amount);
                            $worksheet->mergeCells('P' . $rowcount . ':Q' . $rowcount);

                            $col = 9;
                            $rowcount++;
                        }
                    }
                    //   if ($sum == 0){
                    //              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records Found');
                    //             $worksheet->mergeCells('A' . $rowcount . ':Q' . $rowcount);
                    //              $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );    
                    //   }
                    // else{
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'TOTAL');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('C' . $rowcount . ':I' . $rowcount);
                    $worksheet->getStyle('C' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $sum);
                    $worksheet->mergeCells('P' . $rowcount . ':Q' . $rowcount);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                    // }

                    $highestRow = $worksheet->getHighestRow(); // Last row with content
                    $highestColumn = $worksheet->getHighestColumn(); // Last column with content
                    $range = 'A1:' . $highestColumn . $highestRow;

                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'), // Black
                            ),
                        ),
                    );

                    $worksheet->getStyle($range)->applyFromArray($styleArray);


                    $objPHPExcel->getActiveSheet()->setTitle('ProfessionalTaxSalary');
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                    $objWriter->save(dirname(__FILE__) . "/" . $file_name);
                    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                    header('Content-Disposition: attachment; filename=' . $file_name);

                    readfile(dirname(__FILE__) . "/" . $file_name);
                    unlink(dirname(__FILE__) . "/" . $file_name);
                }
                break;
            default:
                $this->set('mode', '');
                $this->render('professionaltaxsalary');
                break;
        }
    }
    //end
}
