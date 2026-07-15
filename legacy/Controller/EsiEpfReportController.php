<?php

//ESI AND EPF REPORT CREATED BY ARUL P DAS ON 2/5/2021

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
class EsiEpfReportController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EsiEpfReport';
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
        'EmployeeSalaryStructure',
        'UserCredentials',
        'EmployeeDetails',
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
        'ReportAudit'
    );
    public $components = array('MasterdataManagement');

    public function hrreports()
    {
        $arr_reporttypes = array(
            'epf_member_reg' => 'EPF - Member Registration',
            'epf_exit' => 'EPF - Exit',
            'epf_contr' => 'EPF - Contribution',
            'esi_contr' => 'ESI- Monthly Contribution',
            'wps_template' => 'WPS',
            'epf_upload' => 'EPF -Upload' // Edited by Akshay on 30-7-2025
        );
        $this->set('arr_reporttypes', $arr_reporttypes);

        // Edited by Akshay on 8-5-2026
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_branches = $this->EmpCtcTransaction->query("SELECT branch_code, branch_name FROM branches WHERE status = 1;");
        $this->set('arr_branches', $arr_branches);
        // End
    }


    public function reportAudit($type, $mode, $month)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        $dataForHistory['report_from'] = isset($month) ? $month : '';
        switch ($type) {
            case 'epf_member_reg':
                $dataForHistory['report_type'] = "EPF - Member Registration";
                break;
            case 'epf_exit':
                $dataForHistory['report_type'] = "EPF - Exit";
                break;
            case 'epf_contr':
                $dataForHistory['report_type'] = "EPF - Contribution";
                break;
            case 'esi_contr':
                $dataForHistory['report_type'] = "ESI- Monthly Contribution";
                break;
            case 'wps_template':
                $dataForHistory['report_type'] = "WPS";
                break;
            // Edited by Akshay on 30-7-2025
            case 'epf_upload':
                $dataForHistory['report_type'] = "EPF - Upload";
                break;
                // End
        }
        $dataForHistory['include_resigned'] = '';
        $dataForHistory['Include_negative_salary'] = '';
        $criteria_count = 1;
        $dataForHistory['criteria'] = '';
        $dataForHistory['criteria_name'] = '';
        $dataForHistory['items'] = '';
        $dataForHistory['items_count'] = '';
        if ($mode == 'pdf') {
            $dataForHistory['mode'] = 'PDF Download';
        } else if ($mode == 'excel') {
            $dataForHistory['mode'] = 'Excel Download';
        } else {
            $dataForHistory['mode'] = 'Download Report';
        }
        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }

    // Edited by Akshay on 8-5-2026
    public function generatereport()
    {
        $this->autoRender = false;

        $type    = isset($this->request->data['type']) ? $this->request->data['type'] : '';
        $month   = isset($this->request->data['month']) ? $this->request->data['month'] : '';
        $mode    = isset($this->request->data['mode']) ? $this->request->data['mode'] : '';
        $subcat  = isset($this->request->data['subcat']) ? $this->request->data['subcat'] : '';
        $branch  = isset($this->request->data['branch']) ? $this->request->data['branch'] : '';

        switch ($type) {
            case 'esi_contr':
                // $mode = 'excel';
                $this->generate_esi_contr($month, $mode, $type, $branch);
                break;
            case 'wps_template':
                $mode = 'excel';
                $this->generate_wps_template($month, $type, $branch);
                break;
            // Edited by Akshay on 30-7-2025
            case 'epf_upload':
                $this->epfUploadReport($month, $type, $subcat, $branch);
                return false;
                // End
            default:
                $this->generate_report($month, $type, $subcat, $branch);
                return false;
                break;
        }

        $this->reportAudit($type, $mode, $month);
    }

    public function generatereportPdf()
    {
        $this->autoRender = false;

        $type    = isset($this->request->query['type']) ? $this->request->query['type'] : '';
        $month   = isset($this->request->query['month']) ? $this->request->query['month'] : '';
        $mode    = isset($this->request->query['mode']) ? $this->request->query['mode'] : '';
        $subcat  = isset($this->request->query['subcat']) ? $this->request->query['subcat'] : '';
        $branch  = isset($this->request->query['branch']) ? $this->request->query['branch'] : '';

        switch ($type) {

            case 'esi_contr':

                $this->generate_esi_contr($month, $mode, $type, $branch);
                break;

            case 'wps_template':

                $mode = 'excel';
                $this->generate_wps_template($month, $type, $branch);
                break;

            case 'epf_upload':

                $this->epfUploadReport($month, $type, $subcat, $branch);
                return false;

            default:

                $this->generate_report($month, $type, $subcat, $branch);
                return false;
        }

        $this->reportAudit($type, $mode, $month);
    }
    // End

    //ALL OTHER CONDITIONS EXECUTE HERE. CREATED BY ARUL P DAS ON 2/5/2021
    public function generate_report($from = '', $type = '', $subcat = '', $branch = '')
    {
        // $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        //added or condition instead of and for pf and esi by megha on 25_01_2023

        // Edited by Akshay on 12-5-2026
        $branch_condtion = '';

        if ($branch != '') {

            $branches = explode(',', $branch);

            $branches = array_map(function ($v) {
                return "'" . trim($v) . "'";
            }, $branches);

            $branch_condtion = " AND emp_details.branch_code IN (" . implode(',', $branches) . ")";
        }
        // End

        $arr_leavepolicygroupids = $this->EmpCtcTransaction->query("select * from emp_details where status in ('1',2) AND (pf IS NOT NULL AND pf !='' AND  pf != '0' and eps is not null OR esi IS NOT NULL) 
                                                                        AND pf IS NOT NULL AND pf !='' AND  pf != '0' 
                                                                        $branch_condtion
                                                                        ");
        //End
        $monthyearCond = implode(' AND ', $conditions);

        foreach ($arr_leavepolicygroupids as $val) {
            $leavepolicygroupid = $val['emp_details']['emp_pkey'];
            $eps = isset($val['emp_details']['eps']) ? $val['emp_details']['eps'] : '';
            //Edited by Akshay on 18-5-2024
            $arr_emp_info = $this->EmpCtcTransaction->query(" select employee_info.*, ed.date_of_birth from employee_info 
            left join emp_details ed on (ed.emp_pkey = employee_info.emp_pkey)
            where employee_info.emp_pkey =  '$leavepolicygroupid' ");
            //End
            // $arr_gross = $this->EmpCtcTransaction->query("select ectc.* from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null AND  $monthyearCond and head_operator = 'Deduction' ");

            $Employer_Epf = $this->EmpCtcTransaction->query("select salary_head_item_fkey from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null AND  $monthyearCond and head_operator = 'Deduction' and salary_head_item_desc LIKE '%pf%'");
            $Employer_Esi = $this->EmpCtcTransaction->query("select salary_head_item_fkey from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null AND  $monthyearCond and head_operator = 'Deduction' and salary_head_item_desc LIKE '%esi%'");
            $Employer_wwf = $this->EmpCtcTransaction->query("select salary_head_item_fkey from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null AND  $monthyearCond and head_operator = 'Deduction' and salary_head_item_desc LIKE '%wwf%'");

            $epf = '';
            $esi = '';
            $wwf = '';
            if (count($Employer_Epf) > 0) {
                $epf = $Employer_Epf[0]['ectc']['salary_head_item_fkey'];
            }
            if (count($Employer_Esi) > 0) {
                $esi = $Employer_Esi[0]['ectc']['salary_head_item_fkey'];
            }
            if (count($Employer_wwf) > 0) {
                $wwf = $Employer_wwf[0]['ectc']['salary_head_item_fkey'];
            }


            // $Employer_Epf = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = '10' ");
            // $Employer_Esi = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = '12' ");
            // $Employer_wwf = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = 14+1 ");
            // $epf = $Employer_Epf['0']['tax_salary_components']['salary_head_item_Fkey'];
            // $esi = $Employer_Esi['0']['tax_salary_components']['salary_head_item_Fkey'];
            // $wwf = $Employer_wwf['0']['tax_salary_components']['salary_head_item_Fkey'];


            $emp_epf = array();
            $emp_esi = array();
            $emp_wwf = array();
            $pf_contribution = 0;
            if ($epf) {
                $emp_epf = $this->EmpCtcTransaction->query("select ectc.salary_amount,ectc.remarks from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null AND  $monthyearCond and ectc.salary_head_item_fkey = '$epf' ");
                //Edited by Akshay on 23-10-2024
                $emp_epf_contr = isset($emp_epf[0]['ectc']['salary_amount']) ? $emp_epf[0]['ectc']['salary_amount'] : 0;
                //End
                $excluded1 = isset($emp_epf['0']['ectc']['remarks']) ? $emp_epf['0']['ectc']['remarks'] : '';
                $pf_contribution = isset($emp_epf['0']['ectc']['salary_amount']) ? abs(round($emp_epf['0']['ectc']['salary_amount'])) : 0;
                $expressionWithoutPortion = str_replace('* .12', '', $excluded1);
                if ($expressionWithoutPortion != '') {
                    $expressionWithoutPortion = str_replace(' ', '', $expressionWithoutPortion);
                    eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                    $emp_epf = round($epf_earnings);
                } else {
                    $emp_epf = 0;
                }
            }
            if ($esi) {
                $emp_esi = $this->EmpCtcTransaction->query("select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null AND  $monthyearCond and ectc.salary_head_item_fkey = '$esi' ");
            }
            if ($wwf) {
                $emp_wwf = $this->EmpCtcTransaction->query("select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null AND  $monthyearCond and ectc.salary_head_item_fkey = '$wwf' ");
            }


            $salary = $this->EmpCtcTransaction->query("select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null and head_operator = 'Addition' AND $monthyearCond ");

            // The pf deduction is by Arul P Das on 4/7/2021
            //            $get_pf_deduction_rate = $this->EmpCtcTransaction->query("select salary_rate from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null and head_operator = 'Deduction' AND $monthyearCond AND salary_head_item_desc like '%PF%'");

            $get_pf_deduction_rate = $this->EmpCtcTransaction->query("select salary_rate from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' AND ectc.end_date_effective is null and head_operator = 'Addition' AND $monthyearCond AND salary_head_item_desc like '%HRA%'");


            $epf_salary = isset($emp_epf['0']['ectc']['salary_amount']) ? $emp_epf['0']['ectc']['salary_amount'] : 0;
            $esi_salary = isset($emp_esi['0']['ectc']['salary_amount']) ? $emp_esi['0']['ectc']['salary_amount'] : 0;
            $www_salary = isset($emp_wwf['0']['ectc']['salary_amount']) ? $emp_wwf['0']['ectc']['salary_amount'] : 0;
            $gross = isset($salary['0']['0']['sum_amount']) ? $salary['0']['0']['sum_amount'] : 0;

            $deduction_rate = isset($get_pf_deduction_rate[0]['ectc']['salary_rate']) ? $get_pf_deduction_rate[0]['ectc']['salary_rate'] : 0;

            $epf_deducted_salary = 0;
            $actual_epf = 0; //Edited by Akshay on 24-10-2024
            if ($deduction_rate) {
                //                eval('$epf_deducted_salary = ' . $gross.$deduction_rate . ';'); // The eval function is used to do arithmetic operations with a string format.
                // $epf_deducted_salary = $gross - $deduction_rate;
                if ($subcat == 'pf') {
                    $epf_deducted_salary = $gross - $deduction_rate;
                    // debug($epf_deducted_salary);
                } elseif ($subcat == 'actual') {
                    $epf_deducted_salary = $gross;
                    //debug($epf_deducted_salary);
                }
            } else {
                $epf_deducted_salary = $gross;
            }
            //edited by megha epf formula on 15/10/2021
            // debug($arr_emp_info[0]['employee_info']['EmpName']);debug($epf_deducted_salary);
            $grosspf = $this->EmpCtcTransaction->query("select remarks from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.end_date_effective is null AND $monthyearCond AND salary_head_item_desc like '%PF%' limit 1");
            $grosspfval = isset($grosspf['0']['ectc']['remarks']) ? $grosspf['0']['ectc']['remarks'] : '0';
            try {
                if ($subcat == 'pf') {
                    //Edited by Akshay on 20-5-2024
                    $grosspfval = str_replace(' ', '', $grosspfval);
                    eval('$epf_deducted_salary = ' . $grosspfval . ';');
                    $epf_deducted_salary = $epf_deducted_salary / .12;
                    //End
                    // $epf_deducted_salary = $grosspfval . '/.12;';
                    // eval('$epf_deducted_salary = ' . $grosspfval . '/.12;');
                } else {
                    $grosspfval = str_replace(' ', '', $grosspfval);
                    eval('$actual_epf = ' . $grosspfval . ';');
                    $actual_epf = $actual_epf / .12;
                }
            } catch (Exception $ex) {
            }
            $LOP = $this->EmpCtcTransaction->query("select gross_salary,loss_of_pay,working_days,net_salary from payroll_master where emp_fkey = '$leavepolicygroupid' AND month_year = '$from' ");
            $lops = isset($LOP['0']['payroll_master']['loss_of_pay']) ? $LOP['0']['payroll_master']['loss_of_pay'] : 0;
            $wd = isset($LOP['0']['payroll_master']['working_days']) ? $LOP['0']['payroll_master']['working_days'] : 0;
            $net_salary = isset($LOP['0']['payroll_master']['net_salary']) ? $LOP['0']['payroll_master']['net_salary'] : 0;

            $salary_UAN = $this->EmpCtcTransaction->query("select pf from emp_details where emp_pkey = '$leavepolicygroupid' ");
            $UAN = isset($salary_UAN['0']['emp_details']['pf']) ? $salary_UAN['0']['emp_details']['pf'] : 0;

            $salary_esi = $this->EmpCtcTransaction->query("select status,esi from emp_details where emp_pkey = '$leavepolicygroupid' and attr4 = 'Y' and esi != '' ");
            $statu = isset($salary_esi['0']['emp_details']['status']) ? $salary_esi['0']['emp_details']['status'] : '0';
            $esi_number = isset($salary_esi['0']['emp_details']['esi']) ? $salary_esi['0']['emp_details']['esi'] : 0;

            $termination_details = $this->EmpCtcTransaction->query("select * from termination where emp_fkey = '$leavepolicygroupid' ");
            $resignation_date = isset($termination_details['0']['termination']['last_approved_working_date']) ? $termination_details['0']['termination']['last_approved_working_date'] : '';
            $reason = isset($termination_details['0']['termination']['remarks']) ? $termination_details['0']['termination']['remarks'] : '';

            $lworkingdate = $this->EmpCtcTransaction->query("select max(LOGDATE) as dates from device_attandance left join emp_details on (emp_details.emp_id = device_attandance.emp_id) where emp_details.emp_pkey = '$leavepolicygroupid' ");
            $last_working_date = isset($lworkingdate['0']['device_attandance']['dates']) ? $lworkingdate['0']['device_attandance']['dates'] : 0;

            $reason_desc = 1;


            if ($type == 'epf_member_reg') {
                $emp_details = $val['emp_details'];

                // $qualification = $this->EmpCtcTransaction->query("select course from qualifcations where emp_fkey = '$leavepolicygroupid' ");
                // $qua = (count($qualification) > 0) ? $qualification[0]['qualifcations']['course'] : '';

                $passport = $this->EmpCtcTransaction->query("SELECT document_number,valid_from,valid_till FROM emp_passport_visa WHERE document_type='Passport' AND relation='self' AND emp_fkey = '$leavepolicygroupid' ");

                if (isset($val['emp_details']['country']) && isset($val['emp_details']['international_worker']) && $val['emp_details']['international_worker'] == 'Y') {
                    $country_origin = $this->EmpCtcTransaction->query("SELECT country_name FROM countries_nationality WHERE id=" . $val['emp_details']['country']);
                    $country = $country_origin[0]['countries_nationality']['country_name'];
                }
                if (isset($val['emp_details']['nationality_id'])) {
                    $arr_nationality = $this->EmpCtcTransaction->query("SELECT nationality FROM countries_nationality WHERE id=" . $val['emp_details']['nationality_id']);
                    $nationality = $arr_nationality[0]['countries_nationality']['nationality'];
                }
            }


            if ($net_salary > 0) {
                $arr_salary_for_template[] = array(
                    'data' => $arr_emp_info,
                    'gros' => $gross,
                    'epf_deduction_salary' => $epf_deducted_salary,
                    'UAN' => $UAN,
                    'wwf' => $www_salary,
                    'epf' => $emp_epf,
                    'esi' => $esi_salary,
                    'eps' => $eps,
                    'lop' => $lops,
                    'epf_contribution' => $pf_contribution,
                    'desc_reason' => $reason_desc,
                    'date_resignatio' => $resignation_date,
                    'working_date' => $last_working_date,
                    'wday' => $wd - $lops,
                    'status' => $statu,
                    'termination' => $termination_details,
                    'esi_number' => $esi_number,
                    'emp_details' => isset($emp_details) ? $emp_details : '',
                    'passport' => isset($passport) ? $passport : '',
                    'nationality' => isset($nationality) ? strtoupper($nationality) : '',
                    'country_origin' => isset($country) ? $country : '',
                    'emp_epf_contr' => isset($emp_epf_contr) ? abs($emp_epf_contr) : 0, //Edited by Akshay on 23-10-2024
                    'subcat' => isset($subcat) ? $subcat : '', //Edited by Akshay on 24-10-2024
                    'actual_epf' => isset($actual_epf) ? $actual_epf : 0 //Edited by Akshay on 24-10-2024
                );
            }
        }

        if (isset($arr_salary_for_template)) {
            $this->set('arr_salary_for_template', $arr_salary_for_template);
            $this->render($type);
        } else {
            echo "No data found!";
        }


        // echo "<pre>";
        // print_r($arr_salary_for_template);



    }
    private function generate_wps_template($from = '', $type = '', $branch = '')
    {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');

        $otdate = date('Y-m-1', strtotime($from));

        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator,salary_head_item_pkey FROM emp_salary_slip as ectc
            left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
            where ectc.item_part='Direct' 
            and end_date_effective is null  and month_year = '$from'                                            
            Group by salary_head_item_desc
            ORDER BY salhead.salary_head_item_order1 asc");
        $array_key = array();
        $variable_keys = $this->EmpCtcTransaction->query("select trim(salary_head_item_desc) as sal_head,ectc.head_operator,salary_head_item_pkey FROM emp_salary_slip as ectc
             left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
             left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) where ectc.item_part='Direct' and salary_heads.head_pkey in (2,7,9,12)
             and end_date_effective is null  and month_year = '$from' Group by salary_head_item_desc ORDER BY salhead.salary_head_item_order1 asc");
        $variable_key = array();
        foreach ($variable_keys as $val) {
            if ($val['ectc']['head_operator'] == 'Addition') {
                $variable_key['VAddition'][] = $val[0]['sal_head'];
            }
        }

        foreach ($arr_keys as $key => $val) {
            $exit_flag = 0;

            foreach ($variable_keys as $val2) {
                if ($val2['salhead']['salary_head_item_pkey'] == $val['salhead']['salary_head_item_pkey']) {
                    unset($arr_keys[$key]);
                    $exit_flag = 1;
                    continue;
                }
            }

            if ($exit_flag == 1) {
                continue;
            }

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        // debug($arr_keys);

        // echo "<pre>"; print_r($array_key); echo "</pre>";

        $conditions_array = array();
        $conditions_array[] = 'ectc.month_year="' . $from . '"';
        // This conditions leave as array to get new conditions in future.

        $conditions = implode(' AND ', $conditions_array);

        // Edited by Akshay on 12-5-2026
        $branch_condtion = '';

        if ($branch != '') {

            $branches = explode(',', $branch);

            $branches = array_map(function ($v) {
                return "'" . trim($v) . "'";
            }, $branches);

            $branch_condtion = " AND emp_details.branch_code IN (" . implode(',', $branches) . ")";
        }
        // End

        $arr_leavepolicygroupids = $this->EmpCtcTransaction->query("select emp_details.*,emp_proff.*  from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where status in (1,2)  $branch_condtion");

        $gross = array();
        $k = 0;
        foreach ($arr_leavepolicygroupids as $val) {

            $leavepolicygroupid = $val['emp_details']['emp_pkey'];

            $arr_gross = $this->EmpCtcTransaction->query(" select salary_heads.head_pkey,info.*,ar.weekoff_total,ar.lop_total,ar.holiday_total,ar.leave_total,ar.presant_total,"
                . "user_credentials.user_id,emp_ctc_transaction.emp_anual_ctc,termination.last_approved_working_date,emp_details.status, 
                emp_details.guradian,emp_details.classification,emp_details.date_of_birth,emp_details.mobile_no,emp_details.email,
                emp_details.bank_name,emp_details.ifsc_code,emp_details.account_no,emp_details.esi,emp_details.pf,emp_details.company_pf,emp_details.id_card,emp_details.wps_code, 
                emp_proff.emp_company_id,emp_proff.joining_date,payroll_master.bank_details,
                ectc.* 
                from emp_salary_slip as ectc "
                . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                . "left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) "
                . "left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) "
                . "left join attendance_register as ar on(ar.emp_fkey = emp_details.emp_pkey ) "
                . "left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey) "
                . "left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) "
                . "left join branches as branches on (emp_details.branch_code = branches.branch_code and branches.status = 1) "
                . "left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) "
                . "left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) "
                . "left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null ) "
                . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey) "
                . "where  ectc.emp_fkey= '$leavepolicygroupid' "
                . "AND ectc.item_part = 'Direct' "
                . "AND ectc.end_date_effective is null and ar.isdelete='N' "
                . "AND  $conditions and payroll_master.action in ('Approved','Processed') and ar.month_year= '$from' 
                order by info.EmpName asc ,salhead.salary_head_item_order1 asc ");
            $arr_gross1 = $this->EmpCtcTransaction->query(" select salary_heads.head_pkey,info.*,ar.weekoff_total,ar.lop_total,ar.holiday_total,ar.leave_total,ar.presant_total,"
                . "user_credentials.user_id,emp_ctc_transaction.emp_anual_ctc,termination.last_approved_working_date,emp_details.status, 
                emp_details.guradian,emp_details.classification,emp_details.date_of_birth,emp_details.mobile_no,emp_details.email,
                emp_details.bank_name,emp_details.ifsc_code,emp_details.account_no,emp_details.esi,emp_details.pf,emp_details.company_pf,emp_details.id_card,emp_details.wps_code,
                emp_proff.emp_company_id,emp_proff.joining_date,payroll_master.bank_details, 
                ectc.* from emp_salary_slip as ectc "
                . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                . "left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) "
                . "left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) "
                . "left join site_attendance_register as ar on(ar.emp_fkey = emp_details.emp_pkey ) "
                . "left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey) "
                . "left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) "
                . "left join branches as branches on (emp_details.branch_code = branches.branch_code and branches.status = 1) "
                . "left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) "
                . "left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) "
                . "left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null ) "
                . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey) "
                . "where  ectc.emp_fkey= '$leavepolicygroupid' "
                . "AND ectc.item_part = 'Direct' "
                . "AND ectc.end_date_effective is null and ar.isdelete='N' "
                . "AND  $conditions and payroll_master.action in ('Approved','Processed') and ar.month_year= '$from' 
                order by info.EmpName asc ,salhead.salary_head_item_order1 asc ");

            $arr_gross = array_merge($arr_gross, $arr_gross1);

            $arr_ot = $this->EmpCtcTransaction->query("select ot_master.set_duration from emp_ot_master as ot_master where ot_master.emp_fkey='$leavepolicygroupid' "
                . "And ot_master.month='$otdate' and ot_master.is_verified='Y'");

            $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
            left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
            left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
            left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
            where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$leavepolicygroupid' 
            and date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");

            $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;


            $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);


            if (isset($arr_gross) && !empty($arr_gross)) {
                $gross[$k]['emp_info'] = $arr_gross[0]['info'];
                $gross[$k]['emp_details'] = $arr_gross[0]['emp_details'];
                $gross[$k]['termination'] = $arr_gross[0]['termination'];
                $gross[$k]['user_credentials'] = $arr_gross[0]['user_credentials'];
                $gross[$k]['ectc'] = $arr_gross[0]['ectc'];
                $gross[$k]['ot'] = isset($arr_ot[0]['ot_master']['set_duration']) ? $arr_ot[0]['ot_master']['set_duration'] : 0;
                $gross[$k]['prodata'] = $pro_date_desc;
                $gross[$k]['emp_ctc_transaction'] =  $arr_gross[0]['emp_ctc_transaction'];
                $gross[$k]['ar'] = $arr_gross[0]['ar'];
                $gross[$k]['payroll_master'] = $arr_gross[0]['payroll_master'];
                $gross[$k]['settle'] = ($settle) ? $settle : '';

                foreach ($arr_keys as $val) {
                    if ($val['ectc']['head_operator'] == 'Addition') {
                        $gross[$k]['Addition']['keys'][] = $val[0]['sal_head'];
                        $gross[$k]['Addition']['value'][] = 0;
                        $gross[$k]['Addition']['actual'][] = 0;
                    } else {
                        $gross[$k]['Deduction']['keys'][] = $val[0]['sal_head'];
                        $gross[$k]['Deduction']['value'][] = 0;
                        $gross[$k]['Deduction']['actual'][] = 0;
                    }
                }

                // debug($gross);

                $coun1 = count($variable_keys);
                for ($j = 0; $j < $coun1; $j++) {
                    if ($variable_keys[$j]['ectc']['head_operator'] == 'Addition') {
                        $gross[$k]['VAddition']['keys'][] = $variable_keys[$j][0]['sal_head'];
                        $gross[$k]['VAddition']['value'][] = 0;
                        $gross[$k]['VAddition']['actual'][] = 0;
                    }
                }
                foreach ($arr_gross as $value) {
                    if ($value['ectc']['head_operator'] == 'Addition') {
                        if ($value['salary_heads']['head_pkey'] == 1) {
                            $data = trim($value['ectc']['salary_head_item_desc']);
                            $key = array_search($data, $gross[$k]['Addition']['keys']); // $key = 2;
                            $gross[$k]['Addition']['value'][$key] += $value['ectc']['salary_amount'];
                            $gross[$k]['Addition']['actual'][$key] += $value['ectc']['structure_det_value'];
                        }
                    } else {
                        $data = trim($value['ectc']['salary_head_item_desc']);
                        $key = array_search($data, $gross[$k]['Deduction']['keys']); // $key = 2;
                        $gross[$k]['Deduction']['value'][$key] += $value['ectc']['salary_amount'];
                        $gross[$k]['Deduction']['actual'][$key] += $value['ectc']['structure_det_value'];
                    }

                    if ($value['ectc']['head_operator'] == 'Addition' && $value['salary_heads']['head_pkey'] == 1) {
                    } else if ($value['ectc']['head_operator'] == 'Deduction' && $value['salary_heads']['head_pkey'] == 5) {
                    } else {
                        $keys = isset($gross[$k]['VAddition']['keys']) ? $gross[$k]['VAddition']['keys'] : '';
                        if ($keys) {
                            if ($value['ectc']['head_operator'] == 'Addition') {
                                $data = trim($value['ectc']['salary_head_item_desc']);
                                $key = array_search($data, $gross[$k]['VAddition']['keys']); // $key = 2;
                                if ($key >= 0) {
                                    // echo $value['ectc']['salary_amount']."<br>";
                                    $gross[$k]['VAddition']['value'][$key] += $value['ectc']['salary_amount'];
                                    $gross[$k]['VAddition']['actual'][$key] += $value['ectc']['structure_det_value'];
                                }
                            }
                        }
                    }
                }
            }
            $k++;
        }
        // die();
        //debug($gross);die();


        if (!$gross) {
            echo "No data found!";
            die();
        }

        $no_data = 0;
        $mode = 'excel';
        switch ($mode) {
            case 'pdf':

                break;
            case 'excel':
                //EXCEL REPORT CREATED BY ARUL P DAS ON 2/5/2021

                //$str_company_code = $this->Session->read('company_code');
                $file_name = "wpstemplate.xls";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 97-2007 XLS Document");
                $objPHPExcel->getProperties()->setSubject("Office 97-2007 XLS Document");
                $objPHPExcel->getProperties()->setDescription("Salary Excel Report ");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $worksheet->getColumnDimension($col)->setAutoSize(true);
                    $worksheet->getColumnDimension('A' . $col)->setAutoSize(true);
                }
                // $worksheet->mergeCells('A1:L1');

                $style = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );



                $rowcount = 0;
                // if (isset($arr_salary_for_template) && count($arr_salary_for_template) !== 0) {

                $rowcount++;
                $col = 0;
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Employee Code');
                $worksheet->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Employee Name');
                $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'Name of father/husband');
                $worksheet->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Sex');
                $worksheet->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Date of Birth');
                $worksheet->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'Designation');
                $worksheet->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'Designation code/ grade as in Government Order');
                $worksheet->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, 'Date of joining');
                $worksheet->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, 'Mobile Number');
                $worksheet->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 'E-mail ID');
                $worksheet->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 'Bank Name');
                $worksheet->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, 'IFSC Code');
                $worksheet->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, 'Bank Account Number');
                $worksheet->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, 'Days of attendance');
                $worksheet->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, 'Loss of pay days');
                $worksheet->getStyleByColumnAndRow($col + 14, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, 'Number of weekly off granted');
                $worksheet->getStyleByColumnAndRow($col + 15, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount, 'Number of Leave granted');
                $worksheet->getStyleByColumnAndRow($col + 16, $rowcount)->getFont()->setBold(true);

                $col = 16;
                // Actual Salary Heads start here.
                $addition = isset($array_key['Addition']) ? $array_key['Addition'] : [];
                foreach ($addition as $value) {
                    $head = $value;
                    if (trim($head) ==  'Basic' || trim($head) ==  'Dearness Allowance (DA)' || trim($head) == 'House Rent Allowance (HRA)' || trim($head) == 'City Compensation Allowance (CCA)') {
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $head);
                        $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $col++;
                    }
                }
                $itemsToFind = array('Basic', 'Dearness Allowance (DA)', 'House Rent Allowance (HRA)', 'City Compensation Allowance (CCA)');
                $notFoundItems = array();
                foreach ($itemsToFind as $item) {
                    if (!in_array($item, $addition)) {
                        $notFoundItems[] = $item;
                    }
                }
                foreach ($notFoundItems as $notFoundItem) {
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $notFoundItem);
                    $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $col++;
                }

                // This is the total of Actual
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Gross Monthly Wages');
                $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);

                $col = $col + 1;
                // Other Salary Heads start here.
                $vaddition = isset($variable_key['VAddition']) ? $variable_key['VAddition'] : [];
                foreach ($vaddition as $value) {
                    $head = $value;
                    //Edited by Akshay on 4-4-2024
                    if (trim($head) == 'Overtime wages' || trim($head) == 'Leave wages' || trim($head) == 'National & Festival Holidays wages' || trim($head) == 'Arrear paid' || trim($head) == 'Bonus' || trim($head) == 'Maternity Benefit' || trim($head) == 'Advance') {
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $head);
                        $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $worksheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col + 1))->setAutoSize(true);
                        $col++;
                    }
                }
                $itemsToFind = array('Overtime wages', 'Leave wages', 'National & Festival Holidays wages', 'Arrear paid', 'Bonus', 'Maternity Benefit', 'Advance');
                $notFoundItems = array();
                foreach ($itemsToFind as $item) {
                    if (!in_array($item, $vaddition)) {
                        $notFoundItems[] = $item;
                    }
                }
                foreach ($notFoundItems as $notFoundItem) {
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $notFoundItem);
                    $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $col++;
                }

                //Edited by Akshay on 3-4-2024
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Other Allowances');
                $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $col++;
                //End

                // This is the total of Actual
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Total Amount');
                $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);

                // Deduction
                if (isset($array_key['Deduction'])) {
                    $col = $col + 1;

                    $deduction = isset($array_key['Deduction']) ? $array_key['Deduction'] : [];
                    foreach ($deduction as $value) {
                        $head = $value;
                        if ($head == 'WWF - Employee Contribution' || $head == 'EPF - Employee Contribution' || $head == 'ESI - Employee Contribution' || $head == 'TDS' || trim($head) == 'Deduction for Loss & Damages' || trim($head) == 'Professional Tax' || trim($head) == 'Deduction of Fine') { //Edited by Akshay on 3-4-2024
                            $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $head);
                            $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                            $col++;
                        }
                    }
                    $itemsToFind = array('WWF - Employee Contribution', 'EPF - Employee Contribution', 'ESI - Employee Contribution', 'TDS', 'Deduction for Loss & Damages', 'Professional Tax', 'Deduction of Fine');
                    $notFoundItems = array();
                    foreach ($itemsToFind as $item) {
                        if (!in_array($item, $deduction)) {
                            $notFoundItems[] = $item;
                        }
                    }

                    foreach ($notFoundItems as $notFoundItem) {
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $notFoundItem);
                        $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                        $col++;
                    }
                    //Ended

                }

                //Edited by Akshay on 3-4-2024
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Other Deduction');
                $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $col++;

                // Total of Deduction
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Total Deduction');
                $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                // Total Addition - Total Deduction
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'Net wages paid');
                $worksheet->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Date of payment'); // Blank
                $worksheet->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Remarks'); // Month and Year
                $worksheet->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);


                //Edited by Akshay on 3-4-2024
                $rowcount = $rowcount + 1;
                for ($i = 0; $i <= $col + 4; $i++) {
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($i) . $rowcount, $i + 1);
                }

                $rowcount = $rowcount + 1;
                $i = 0;
                $sum = 0;
                $lopDays = 0;
                $numberOfLeaves = 0;
                if ($gross) {
                    foreach ($gross as $values) {
                        $daysOfAttendance = ($values['ar']['presant_total'] ? $values['ar']['presant_total'] : '');
                        //$daysOfAttendance = ($values['ar']['presant_total']) ? $values['ar']['presant_total'] + (($values['ar']['leave_total']) ? $values['ar']['leave_total'] : 0) : (($values['ar']['leave_total']) ? $values['ar']['leave_total'] : '');
                        $numberOfWeekOff = ($values['ar']['weekoff_total'] ? $values['ar']['weekoff_total'] : '');
                        //$lopDays = ($daysOfAttendance) ? (($values['prodata']['days'] - $numberOfWeekOff) - $daysOfAttendance) : ($values['prodata']['days'] - $numberOfWeekOff);
                        $numberOfLeaves = ($values['ar']['leave_total'] ? $values['ar']['leave_total'] : '');
                        $lopDays = ($values['ar']['lop_total'] ? $values['ar']['lop_total'] : '');
                        $aadhar = "";
                        $voter_id = "";
                        if (isset($values['emp_details']['id_card']) && $values['emp_details']['id_card']) {
                            $id_card = $values['emp_details']['id_card'];
                            if (is_numeric($id_card)) {
                                $voter_id = $id_card;
                            } else {
                                $aadhar = $id_card;
                            }
                        }

                        $col = 0;
                        $wpsCode = isset($values['emp_details']['wps_code']) ? $values['emp_details']['wps_code'] : '';
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $wpsCode);

                        // $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, ($values['emp_details']['wps_code']) ? $values['emp_details']['wps_code'] : '', PHPExcel_Cell_DataType::TYPE_STRING);

                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $values['emp_info']['EmpName']);
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $values['emp_details']['guradian']);
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, ucfirst($values['emp_details']['classification']));
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, date('d/m/Y', strtotime($values['emp_details']['date_of_birth'])));

                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $values['emp_info']['designation']); // Designation
                        //Edited by Akshay on 4-4-2024
                        $cellCoordinate = PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount;
                        $worksheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, ''); // Designation code blank
                        $bank_name = '';
                        $branch_name = '';
                        $ifsc_code = '';
                        $acc_number = '';
                        $bank = isset($values['payroll_master']['bank_details']) ? $values['payroll_master']['bank_details'] : '';
                        if ($bank != '') {
                            list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                        }
                        if ($bank_name == '') {
                            $bank_name = isset($values['emp_details']['bank_name']) ? stripslashes($values['emp_details']['bank_name']) : '';
                        }
                        if ($ifsc_code == '') {
                            $ifsc_code = isset($values['emp_details']['ifsc_code']) ? stripslashes($values['emp_details']['ifsc_code']) : '';
                        }
                        if ($acc_number == '') {
                            $acc_number = isset($values['emp_details']['account_no']) ? stripslashes($values['emp_details']['account_no']) : '';
                        }

                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, date('d/m/Y', strtotime($values['emp_info']['joining_date'])));
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $values['emp_details']['mobile_no'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $values['emp_details']['email']);
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, $bank_name);
                        $worksheet->SetCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, $ifsc_code, PHPExcel_Cell_DataType::TYPE_STRING);
                        $worksheet->SetCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, $acc_number, PHPExcel_Cell_DataType::TYPE_STRING);

                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, $daysOfAttendance); // Days of attendance (Present days + leave)
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, ($lopDays) ? $lopDays : '0'); // Loss of pay days (Absent days)
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, $numberOfWeekOff); // Number of WeekOffs
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount, ($numberOfLeaves) ? $numberOfLeaves : '0'); // Number of leaves 

                        $col = $col + 16;
                        // Actual
                        $net_wages_paid = 0;
                        $grss_amt = 0;
                        $standard = isset($values['Addition']) ? $values['Addition'] : [];
                        $numberFormat = '#,##0.00'; // Edited by Akshay on 4-4-2024

                        $other_allowances = 0;
                        foreach ($standard as $key => $value) {
                            if ($key == 'value') {
                                foreach ($value as $key1 => $val) {
                                    if (trim($standard['keys'][$key1]) == 'Basic' || trim($standard['keys'][$key1]) == 'Dearness Allowance (DA)' || trim($standard['keys'][$key1]) == 'House Rent Allowance (HRA)' || trim($standard['keys'][$key1]) == 'city Compensation allowances') {
                                        $number = round($val);
                                        $grss_amt += $number;
                                        //Edited by Akshay on 4-4-2024
                                        $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $number, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                                        $col++;
                                    } else {
                                        $other_allowances += round($val);
                                    }
                                }
                            }
                        }
                        $itemsToFind = array('Basic', 'Dearness Allowance (DA)', 'House Rent Allowance (HRA)', 'city Compensation allowances');
                        $notFoundItems = array();
                        foreach ($itemsToFind as $item) {
                            if (!in_array($item, $standard['keys'])) {
                                $notFoundItems[] = $item;
                            }
                        }
                        foreach ($notFoundItems as $notFoundItem) {
                            $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode('0.00');
                            $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 0.00);
                            $col++;
                        }


                        // Gross Monthly Wages
                        $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $grss_amt, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        // $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $grss_amt);
                        // $net_wages_paid += $grss_amt;

                        $col = $col + 1;



                        // Total Amount
                        $count = isset($values['VAddition']) ? count($values['VAddition']) : 0;
                        $grss_amt1 = 0;
                        if ($count > 0) {
                            $count_addition1 = count($values['VAddition']['keys']);
                            for ($m = 0; $m < $count_addition1; $m++) {
                                $number1 = round($values['VAddition']['value'][$m]);
                                $grss_amt1 = $number1 + $grss_amt1;
                                //Edited by Akshay on 4-4-2024
                                if (trim($values['VAddition']['keys'][$m]) == 'Overtime wages' || trim($values['VAddition']['keys'][$m]) == 'Leave wages' || trim($values['VAddition']['keys'][$m]) == 'National & Festival Holidays wages' || trim($values['VAddition']['keys'][$m]) == 'Arrear paid' || trim($values['VAddition']['keys'][$m]) == 'Bonus' || trim($values['VAddition']['keys'][$m]) == 'Maternity Benefit' || trim($values['VAddition']['keys'][$m]) == 'Advance') {
                                    //Edited by Akshay on 4-4-2024
                                    $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                                    $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $number1, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                                    $col++;
                                } else {
                                    $other_allowances += round($number1);
                                }
                            }
                            $grss_amt1 = round($grss_amt1);
                        }
                        $itemsToFind = array('Overtime wages', 'Leave wages', 'National & Festival Holidays wages', 'Arrear paid', 'Bonus', 'Maternity Benefit', 'Advance');
                        $notFoundItems = array();
                        if ($count > 0) // Edited by Akshay on 29-5-2026
                        foreach ($itemsToFind as $item) {
                            if (!in_array($item, $values['VAddition']['keys'])) {
                                $notFoundItems[] = $item;
                            }
                        }
                        foreach ($notFoundItems as $notFoundItem) {
                            $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode('0.00');
                            $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 0.00);
                            $col++;
                        }

                        //Edited by Akshay on 3-4-2024
                        $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $other_allowances, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $col++;

                        $total_amount = ($grss_amt + $grss_amt1 + $other_allowances); //Edited by Akshay on 3-4-2024
                        $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $total_amount, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        // $net_wages_paid += $ttl_amt;

                        $col = $col + 1;
                        // Deduction Actual
                        $tota_deduction = 0;
                        $actual_deduction = isset($values['Deduction']) ? $values['Deduction'] : '';
                        $other_deductions = 0;
                        if ($actual_deduction) {
                            $count_addition = count($actual_deduction['keys']);
                            for ($m = 0; $m < $count_addition; $m++) {
                                //Edited by Akshay on 3-4-2024
                                $number = round($actual_deduction['value'][$m]);
                                $tota_deduction += $number;
                                if ($actual_deduction['keys'][$m] == 'WWF - Employee Contribution' || $actual_deduction['keys'][$m] == 'EPF - Employee Contribution' || $actual_deduction['keys'][$m] == 'ESI - Employee Contribution' || $actual_deduction['keys'][$m] == 'TDS' || trim($actual_deduction['keys'][$m]) == 'Deduction for Loss & Damages' || trim($actual_deduction['keys'][$m]) == 'Professional Tax' || trim($actual_deduction['keys'][$m] == 'Deduction of Fine')) {
                                    //Edited by Akshay on 4-4-2024
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                                    $objPHPExcel->getActiveSheet()->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, abs($number), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                                    $col++;
                                } else {
                                    $other_deductions += $number;
                                }
                            }
                            //Edited by Akshay on 3-4-2024
                            $itemsToFind = array('WWF - Employee Contribution', 'EPF - Employee Contribution', 'ESI - Employee Contribution', 'TDS', 'Deduction for Loss & Damages', 'Professional Tax', 'Deduction of Fine');
                            $notFoundItems = array();
                            foreach ($itemsToFind as $item) {
                                if (!in_array($item, $actual_deduction['keys'])) {
                                    $notFoundItems[] = $item;
                                }
                            }

                            foreach ($notFoundItems as $notFoundItem) {
                                $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode('0.00');
                                $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 0.00);
                                $col++;
                            }
                        }
                        //Edited by Akshay on 4-4-2024
                        $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, abs($other_deductions), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $col++;

                        // Total Deduction
                        $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, abs($tota_deduction), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $net_wages_paid = ($grss_amt + $tota_deduction + $grss_amt1); // Total addition - total deduction

                        //Edited by Akshay on 4-4-2024
                        $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, ($net_wages_paid), PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $currentDate = date("Y-m-d");

                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, date('d/m/Y', strtotime($currentDate))); // Blank 
                        $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, date('M Y', strtotime($from))); // Month and Year company_pf


                        $rowcount++;


                        // $styleArray = array(
                        //     'font'  => array(
                        //         'bold'  => true,
                        //         'color' => array('rgb' => 'FF0000'),
                        //         'size'  => 15,
                        //         'name'  => ''
                        //     ));
                        //   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "* Date format should be dd/mm/yyyy");
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $worksheet->setCellValueByColumnAndRow(1, $rowcount, "* Date format should be dd/mm/yyyy");
                        //$objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($styleArray);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, ($rowcount))->getFont()->setSize(10);
                        // $worksheet->mergeCells('B' . $rowcount . ':E' . $rowcount);
                        // $worksheet->getStyle('B' . $rowcount)->getAlignment()->applyFromArray(
                        //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        //);

                        $BStyle = array(

                            'borders' => array(

                                'allborders' => array(

                                    'style' => PHPExcel_Style_Border::BORDER_THIN

                                )

                            )

                        );

                        $row = $rowcount - 1;
                        $col = $col + 5;
                        $columnName = '';
                        while ($col > 0) {
                            $remainder = ($col - 1) % 26;
                            $columnName = chr(65 + $remainder) . $columnName;
                            $col = intval(($col - $remainder) / 26);
                        }
                        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $columnName . $row)->applyFromArray($BStyle);
                    }
                } else {
                    $no_data = 1;
                }

                $worksheet->setTitle('WPS');
                /* header footer */
                $user_name = $this->Session->read('user_name');
                $worksheet->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $worksheet->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
                $worksheet->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $worksheet->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */
                /* print Set up */
                $worksheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $worksheet->getPageSetup()->setFitToPage(true);
                $worksheet->getPageSetup()->setFitToWidth(1);
                $worksheet->getPageSetup()->setFitToHeight(0);
                /* print Set up */
                // $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                $objWriter->save($file_name);
                // output headers so that the file is downloaded rather than displayed
                // header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                // header('Content-Disposition: attachment; filename=' . $file_name);

                // readfile($file_name);
                // unlink($file_name);
                break;
            case 'print':
                break;
            default:
                break;
        }

        if ($no_data) {
            echo "No data found!";
        } else {
            $this->render($type);
        }
    }


    //ESI CONTRIBUTION CREATED BY ARUL P DAS ON 2/5/2021
    private function generate_esi_contr($from = '', $mode = '', $type = '', $branch = '')
    {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        // Edited by Akshay on 12-5-2026
        $branch_condtion = '';

        if ($branch != '') {

            $branches = explode(',', $branch);

            $branches = array_map(function ($v) {
                return "'" . trim($v) . "'";
            }, $branches);

            $branch_condtion = " AND emp_details.branch_code IN (" . implode(',', $branches) . ")";
        }
        // End
        $arr_leavepolicygroupids = $this->EmpCtcTransaction->query("select * from emp_details where status in (1, 2) AND esi IS NOT NULL AND esi !='' $branch_condtion"); //Edited by Akshay in 18-4-2024
        $monthyearCond = implode(' AND ', $conditions);

        $arr_salary_for_template = array(); // Edited by Akshay on 1-1-2025

        foreach ($arr_leavepolicygroupids as $key => $val) {
            $highlight = false; // Edited by Akshay on 31-12-2025
            $leavepolicygroupid = $val['emp_details']['emp_pkey'];
            $arr_emp_info = $this->EmpCtcTransaction->query(" select * from employee_info where emp_pkey =  '$leavepolicygroupid' ");
            //edited by sinsiya on 16-08-2024
            $LOP = $this->EmpCtcTransaction->query("select calander_days,loss_of_pay,working_days,monthly_ctc,gross_salary,days_presant,days_leave from payroll_master where emp_fkey = '$leavepolicygroupid' AND month_year = '$from' ");
            $lops = isset($LOP['0']['payroll_master']['loss_of_pay']) ? $LOP['0']['payroll_master']['loss_of_pay'] : 0;

            $leave = isset($LOP['0']['payroll_master']['days_leave']) ? $LOP['0']['payroll_master']['days_leave'] : 0;
            $wd = isset($LOP['0']['payroll_master']['working_days']) ? $LOP['0']['payroll_master']['working_days'] : 0;
            $present = isset($LOP['0']['payroll_master']['days_presant']) ? $LOP['0']['payroll_master']['days_presant'] + $leave : 0;
            // edited by sinsiya on 16-08-2024
            $valuetotal = $this->EmpCtcTransaction->query("SELECT weekoff_total, holiday_total, leave_total, presant_total FROM attendance_register WHERE emp_fkey = '$leavepolicygroupid' AND month_year = '$from'");

            // Check if $value contains data
            if (!empty($valuetotal) && isset($valuetotal[0]['attendance_register'])) {
                $dayscount = $valuetotal[0]['attendance_register']['leave_total']
                    // Edited by Akshay on 19-5-2025
                    + $valuetotal[0]['attendance_register']['weekoff_total']
                    + $valuetotal[0]['attendance_register']['holiday_total']
                    + $valuetotal[0]['attendance_register']['presant_total'];
                // End
            } else {

                $dayscount = 0;
            }

            $calenderdays = isset($LOP[0]['payroll_master']['calander_days']) ? $LOP[0]['payroll_master']['calander_days'] : 0;


            $lopna =  $calenderdays - $dayscount;

            // $calenderdays += 1;
            // $year1 = date('Y', strtotime($from));
            // $month1 = date('m', strtotime($from));
            // $calenderdaysnew = cal_days_in_month(CAL_GREGORIAN, $month1, $year1);


            //  debug($calenderdaysnew);
            // debug($lopna);
            $dayspresent = $calenderdays - $lopna;

            //edited by sinsiya on 13-12-2024
            // if($dayspresent == -1){
            //     $dayspresent=0;
            // }
            //$dayspresent +=1;
            // debug($dayspresent);
            //            $monthlyWages = isset($LOP['0']['payroll_master']['monthly_ctc'])?$LOP['0']['payroll_master']['monthly_ctc']:0;
            $monthlyWages = isset($LOP['0']['payroll_master']['gross_salary']) ? $LOP['0']['payroll_master']['gross_salary'] : 0;
            //edited by megha for getting only esi deducted employees of that month
            //$salary_esi = $this->EmpCtcTransaction->query("select status,esi from emp_details where emp_pkey = '$leavepolicygroupid'");
            $salary_esi = $this->EmpCtcTransaction->query("select status,esi,remarks from emp_details left join emp_salary_slip on (emp_salary_slip.emp_fkey =emp_details.emp_pkey)"
                . " where emp_pkey = '$leavepolicygroupid' and `end_date_effective` IS NULL AND head_operator='Addition' and
            `month_year` = '$from' and salary_head_item_desc like '%ESI%'");
            // debug($arr_emp_info);
            // debug($salary_esi);
            $esi_number = isset($salary_esi['0']['emp_details']['esi']) ? $salary_esi['0']['emp_details']['esi'] : 0;
            $arr_formulae_from_remarks = $this->EmployeeSalaryStructure->find(
                "all",
                array(
                    'fields' => 'emp_salary_structure_pkey,head_operator,remarks',
                    'conditions' => array(
                        'emp_fkey' => $leavepolicygroupid,
                        'head_operator' => 'Addition',
                        'remarks IS NOT NULL',
                        'end_date_effective is null',
                        'salary_head_item_desc like "%ESI%"'
                    )
                )
            );
            $value = isset($salary_esi['0']['emp_salary_slip']['remarks']) ? $salary_esi['0']['emp_salary_slip']['remarks'] : '';
            if ($value) {
                $myArray = explode('*', $value);

                //Edited by Akshay on 2-4-2024
                $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
                $year1 = date('Y', strtotime($from));
                $month1 = date('m', strtotime($from));
                $month_days = cal_days_in_month(CAL_GREGORIAN, $month1, $year1);

                $loparray = $this->EmployeeDetails->query("SELECT loss_of_pay FROM payroll_master WHERE emp_fkey = '$leavepolicygroupid' AND month_year = '$from'");
                $lop = isset($loparray[0]['payroll_master']['loss_of_pay']) ? $loparray[0]['payroll_master']['loss_of_pay'] : 0;
                $present = $month_days - $lop;
                // ended

                // eval('$monthlyWages = ' . $myArray[0] . ';');
                //Edited by Akshay on 18-4-2024
                if (!empty($myArray[0]) && preg_match('/^[\d\s\+\-\*\/\(\)]+$/', $myArray[0])) {
                    // Execute eval only if $myArray[0] contains a valid expression
                    eval('$monthlyWages = ' . $myArray[0] . ';');
                }
                // eval('$monthlyWages = ' . $myArray[0] . ';');

                $month_year = explode("-", $from);
                $year = $month_year[0];
                $cur_month = $month_year[1] - 1;
                $prev_month = date('Y-m', strtotime(date($year . "-" . $cur_month . "-01")));

                $termination_details = $this->EmpCtcTransaction->query("select * from termination where emp_fkey = '$leavepolicygroupid' and status = 1 and (last_approved_working_date like '%$from%' OR last_approved_working_date like '%$prev_month%')"); // Edited by Akshya on 1-1-2025

                $resignation_date = isset($termination_details['0']['termination']['last_approved_working_date']) ? $termination_details['0']['termination']['last_approved_working_date'] : '';
                $reason = isset($termination_details['0']['termination']['Reason']) ? strtoupper($termination_details['0']['termination']['Reason']) : '';

                $lworkingdate = $this->EmpCtcTransaction->query("select max(LOGDATE) as dates from device_attandance left join emp_details on (emp_details.emp_id = device_attandance.emp_id) where emp_details.emp_pkey = '$leavepolicygroupid' ");
                $last_working_date = isset($lworkingdate['0']['device_attandance']['dates']) ? date('d-m-Y', $lworkingdate['0']['device_attandance']['dates']) : ''; //Edited by Akshay on 18-4-2024

                //Edited by Akshay on 18-4-2024
                if ($resignation_date != '') {
                    $resignation_month_year = date('Y-m', strtotime($resignation_date)); //Edited by Akshay ion 25-4-2024
                    $prev_month_year = date('Y-m',  strtotime($prev_month)); //Edited by Akshay ion 25-4-2024
                    // debug($resignation_month_year);
                    // debug($prev_month_year);
                    if ($resignation_month_year == $prev_month_year) {
                        $last_working_date = $resignation_date;
                        $highlight = true;
                    }
                }

                $reason_code = 0;
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

                        $last_working_date = $resignation_date; // Edited by Akshay on 28-8-2025
                    }
                } else {
                    //$working_days = ceil($wd - $lops);
                    $working_days = ceil($present);
                    if ($working_days == 0) {
                        $leave_count = $this->EmpCtcTransaction->query("SELECT count(*) as cnt FROM `leaveentries` WHERE LEAVESTATUS = 'Approved' AND `FROMDATE` LIKE '%$from%' AND `TODATE` LIKE '%$from%' AND EMP_fkey = $leavepolicygroupid");
                        if (isset($leave_count[0][0]["cnt"])) {
                            $count = $leave_count[0][0]["cnt"];
                            if ($count > 0) {
                                $reason_code = 1;
                                $last_working_date =  $resignation_date; // Edited by Akshay on 28-8-2025
                            }
                        }
                    }

                    // if ($monthlyWages == 0) {
                    if (true) { //Edited by Akshay on 18-4-2024
                        $month_year = explode("-", $from);
                        $year = $month_year[0];
                        $cur_month = $month_year[1];
                        if ($cur_month == "04" || $cur_month == "10") {
                            $cur_month = $cur_month - 1;
                            $prev_month = date('Y-m', strtotime(date($year . "-" . $cur_month . "-01")));
                            // Edited by Akshay on 1-11-2025
                            $prev_sal = $this->EmpCtcTransaction->query("SELECT structure_det_value
                                                                            FROM `emp_salary_slip`
                                                                            WHERE `emp_fkey` = '$leavepolicygroupid' AND `end_date_effective` IS NULL AND `month_year` = '$prev_month'
                                                                            AND salary_head_item_fkey IN (
                                                                            SELECT salary_head_item_Fkey FROM tax_salary_components WHERE `tax_salary_components_name` = 'Employee ESI'
                                                                            );");
                            $salary = isset($prev_sal['0']['emp_salary_slip']['structure_det_value']) ? abs($prev_sal['0']['emp_salary_slip']['structure_det_value'] / .0075) : 0;
                            // End
                            if (ceil($salary) > 21000) {
                                $reason_code = 4;
                            }
                        }
                    } else {
                        $reason_code = '';
                        $last_working_date =  $resignation_date; // Edited by Akshay on 28-8-2025
                    }
                }
            } else {
                $esi_number = 0;
            }
            // Without Reason	0
            // On Leave	1
            // Out of Coverage	4
            // Left service = 2 months. take previous month
            // Compliance by Immediate Employer	7 = On Leave 1
            // No Work	11 = On Leave 1
            // Doesnt Belong To This Employer	12 = On Leave 1
            // Duplicate IP	13 = On Leave 1

            //Edited by Akshay on 18-4-2024
            if (isset($resignation_month_year) && isset($prev_month_year) && ($resignation_month_year == $prev_month_year)) {
                $numberOfWorkingDays = 0;
            } else {
                //$numberOfWorkingDays = ceil($present);
                $numberOfWorkingDays = ceil($dayspresent);

                // Edited by Akshay on 23-7-2025
                // Step 1: Get payroll_type from db_config
                $payroll_result = $this->EmpCtcTransaction->query("SELECT payroll_type FROM db_config LIMIT 1");
                $payroll_type = isset($payroll_result[0]['db_config']['payroll_type']) ? $payroll_result[0]['db_config']['payroll_type'] : null;

                // Step 2: Determine start and end date based on payroll_type
                if (in_array($payroll_type, ['F1', 'F2'])) {
                    // Use regular calendar month
                    $start_date = $from . '-01';
                    $end_date = date('Y-m-t', strtotime($start_date));
                } else {
                    // Use custom attendance period via att_start_end_fn
                    $att_start = $this->EmpCtcTransaction->query("
                                                                    SELECT att_start_end_fn(CONCAT('$from', '-01'), 1) AS monthly_att_fromdate
                                                                ");
                    $att_end = $this->EmpCtcTransaction->query("
                                                                 SELECT att_start_end_fn(CONCAT('$from', '-01'), 2) AS monthly_att_todate
                                                            ");

                    $start_date = isset($att_start[0][0]['monthly_att_fromdate']) ? $att_start[0][0]['monthly_att_fromdate'] : null;
                    $end_date = isset($att_end[0][0]['monthly_att_todate']) ? $att_end[0][0]['monthly_att_todate'] : null;
                }

                // Step 3: Execute final attendance count query

                // Edited by Akshay on 1-1-2026
                $arr_total_days = $this->EmpCtcTransaction->query("SELECT DAY(LAST_DAY(CONCAT('$from', '-01'))) AS total_days");
                $total_days_in_month = $arr_total_days[0][0]['total_days'];

                $columns = $this->EmpCtcTransaction->query("
                                                                SHOW COLUMNS FROM attendance_register LIKE 'lop_only'
                                                            ");
                // Edited by Akshay on 12-2-2026
                if (!empty($columns) && $from > '2026-01') {
                    // lop_only column exists
                    $lopField = "lop_only as lop_total";
                } else {
                    // lop_only column does NOT exist
                    $lopField = "lop_total as lop_total";
                }
                // End

                $count_result = $this->EmpCtcTransaction->query("SELECT
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
                                                        ) AS na_count, $lopField
                                                        FROM attendance_register
                                                        WHERE emp_fkey = '$leavepolicygroupid'
                                                        AND month_year = '$from'
                                                        AND isdelete = 'N';");

                $na_count = isset($count_result[0][0]['na_count']) ? $count_result[0][0]['na_count'] : 0;
                $lop_total = 0;
                if (isset($count_result[0]['attendance_register']['lop_total'])) {
                    $lop_total = $count_result[0]['attendance_register']['lop_total'];
                }
                // If not found, check under [0]
                elseif (isset($count_result[0][0]['lop_total'])) {
                    $lop_total = $count_result[0][0]['lop_total'];
                }
                $numberOfWorkingDays = max(0, ($total_days_in_month - ($lop_total + $na_count)));
                // End
                // End
            }

            if ($esi_number) {
                $arr_salary_for_template[] = array(
                    // 'data'=>$arr_emp_info,
                    'IPNO' => $esi_number,
                    'IPNAME' => isset($arr_emp_info['0']['employee_info']['EmpName']) ? $arr_emp_info['0']['employee_info']['EmpName'] : '',
                    'NOOFWORKINGDAYS' => ceil($numberOfWorkingDays), //Edited by Akshay on 18-4-2024
                    'TOTALMONTHLYWAGES' => $monthlyWages,
                    'REASONCODE' => 0,
                    'working_date' => '',
                    'highlight' => $highlight
                );
            }
        }

        // Edited by Akshay on 1-1-2026
        $prevMonth = date('Y-m', strtotime('-1 month', strtotime($from . '-01')));
        $arr_leavepolicygroupids = $this->EmpCtcTransaction->query("select ed.*, tm.last_approved_working_date, tm.Reason from emp_details ed
                                                                    LEFT JOIN termination tm ON (ed.emp_pkey = tm.emp_fkey AND tm.status = 1)
                                                                    where ed.status in (1, 2) AND ed.esi IS NOT NULL AND ed.esi !='' 
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

                $last_approved_working_date = isset($val['tm']['last_approved_working_date']) ? $val['tm']['last_approved_working_date'] : '';

                // Edited by Akshay on 2-1-2026
                if ($last_approved_working_date != '') {
                    $last_approved_working_date = date('d-m-Y', strtotime($last_approved_working_date));
                }
                // End

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

                $arr_salary_for_template[] = array(
                    'IPNO' => $esi_number,
                    'IPNAME' => $ipName,
                    'NOOFWORKINGDAYS' => 0, //Edited by Akshay on 18-4-2024
                    'TOTALMONTHLYWAGES' => 0,
                    'REASONCODE' => $reason_code,
                    'working_date' => $last_approved_working_date,
                    'highlight' => false
                );
            }
        }
        // End

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y h:i A');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        //  $this->set('month', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        //  $this->set('month', $from);
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        // $date_time = date('d.m.Y');
        $f = date('Y-m', strtotime($from));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $this->set('arr_salary_for_template', (isset($arr_salary_for_template)) ? $arr_salary_for_template : array());

        $no_data = 0;

        switch ($mode) {
            case 'pdf':
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('esi_contrpdf');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $str_company_code = $this->Session->read('company_code');
                //$html2pdf->Output($str_company_code . '_Overtime Summary Month Wise - ' . $month . " " . $year . ' to ' . $toMonth . ' ' . $toYear . '.pdf', 'D');
                $html2pdf->Output($str_company_code . '_Esicontribution'  . $from .  '.pdf', 'D');
                $this->render('esi_contrpdf');
                break;
            case 'excel':
                //EXCEL REPORT CREATED BY ARUL P DAS ON 2/5/2021

                // $str_company_code = $this->Session->read('company_code');
                $file_name = "esicontribution.xls";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 97-2007 XLS Document");
                $objPHPExcel->getProperties()->setSubject("Office 97-2007 XLS Document");
                $objPHPExcel->getProperties()->setDescription("ESI Contribution Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                for ($col = 'A'; $col !== 'F'; $col++) {
                    $worksheet->getColumnDimension($col)->setAutoSize(false);
                }
                // $worksheet->mergeCells('A1:L1');

                $style = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );

                $worksheet->getStyle("A1:F1")->applyFromArray($style); //ALIGN CENTER
                //FIXED WIDTH
                $worksheet->getColumnDimension('A')->setWidth(12);
                $worksheet->getColumnDimension('B')->setWidth(35);
                $worksheet->getColumnDimension('C')->setWidth(35);
                $worksheet->getColumnDimension('D')->setWidth(25);
                $worksheet->getColumnDimension('E')->setWidth(35);
                $worksheet->getColumnDimension('F')->setWidth(25);

                $worksheet->getStyle('A1:F1')->getAlignment()->setWrapText(true); //TEXT WRAP

                $rowcount = 0;
                if (isset($arr_salary_for_template) && count($arr_salary_for_template) !== 0) {

                    $rowcount++;
                    $col = 0;
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'IP Number');
                    $worksheet->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'IP Name');
                    $worksheet->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'No of Days for which wages paid/payable during the month');
                    $worksheet->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Total Monthly Wages');
                    $worksheet->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, ' Reason Code for Zero workings days(numeric only; provide 0 for all other reasons- Click on the link for reference)');
                    $worksheet->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                    $worksheet->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, ' Last Working Day');
                    $worksheet->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $rowcount = $rowcount + 1;
                    $i = 0;
                    $sum = 0;
                    foreach ($arr_salary_for_template as $values) {
                        $col = 0;
                        //Edited by Akshay on 18-4-2024
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, (string)$values['IPNO'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, (string)$values['IPNAME'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, (string)$values['NOOFWORKINGDAYS'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, (string)$values['TOTALMONTHLYWAGES'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, (string)$values['REASONCODE'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, (string)$values['working_date'], PHPExcel_Cell_DataType::TYPE_STRING);

                        // Edited by Akshay on 31-12-2025
                        // Highlight full row if flag is true
                        if (!empty($values['highlight'])) {
                            $startCol = PHPExcel_Cell::stringFromColumnIndex(0);
                            $endCol   = PHPExcel_Cell::stringFromColumnIndex(5); // last column
                            $range    = $startCol . $rowcount . ':' . $endCol . $rowcount;

                            $worksheet->getStyle($range)->applyFromArray([
                                'fill' => [
                                    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => ['rgb' => 'FFF000'] // light yellow
                                ]
                            ]);
                        }
                        // End

                        $rowcount++;
                    }
                } else {
                    $no_data = 1;
                }

                $worksheet->setTitle('Sheet1');
                /* header footer */
                $user_name = $this->Session->read('user_name');
                $worksheet->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $worksheet->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
                $worksheet->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $worksheet->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */
                /* print Set up */

                $worksheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $worksheet->getPageSetup()->setFitToPage(true);
                $worksheet->getPageSetup()->setFitToWidth(1);
                $worksheet->getPageSetup()->setFitToHeight(0);
                /* print Set up */
                $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                $objWriter->save($file_name);
                //                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                //                $objWriter->save(dirname(__FILE__) . "/" . $file_name);
                // output headers so that the file is downloaded rather than displayed
                // header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                // header('Content-Disposition: attachment; filename=' . $file_name);

                // readfile($file_name);
                // unlink($file_name);
                break;
            case 'print':
                break;
            default:
                break;
        }

        if ($no_data) {
            echo "No data found!";
        } else {
            $this->render($type);
        }
    }
    public function getprodataDesc($id, $date)
    {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_pro_data = $this->EmpCtcTransaction->query("select distinct(prorate_code)  "
            . "from emp_salary_structure as ectc "
            . " where ectc.emp_fkey = '$id' "
            . "and end_date_effective is null ");
        $prorate_code = isset($arr_pro_data[0]['ectc']['prorate_code']) ? $arr_pro_data[0]['ectc']['prorate_code'] : '1'; //set default as calender days
        $year = date('Y', strtotime($date));
        $month = $from = date('m', strtotime($date));
        $day = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $loparray = $this->EmployeeDetails->query("SELECT loss_of_pay FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
        $lop = isset($loparray[0]['payroll_master']['loss_of_pay']) ? $loparray[0]['payroll_master']['loss_of_pay'] : 0;
        if ($prorate_code == '1') {
            $day['type'] = "Calender Days";
            $day['days'] = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            //added by megha on 7_5_19
            $weekoff = $this->EmployeeDetails->query("SELECT week_off_days,working_days,days_presant,days_leave FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
            $leave = isset($weekoff[0]['payroll_master']['days_leave']) ? $weekoff[0]['payroll_master']['days_leave'] : 0;
            //added by megha on 7_5_19
        } else if ($prorate_code == '2') {
            $day['type'] = "Working Days";


            $holiday = $this->EmployeeDetails->query("SELECT count(ho.HOLIDAYID) as count FROM emp_proff as ep left join  holidays as ho on(ho.HOLIDAY_GROUP_ID=ep.HOLIDAY_GROUP_ID) where month(ho.HOLIDAYDATE)='$month' and year(ho.HOLIDAYDATE)='$year' and ep.emp_fkey='$id'");
            $weekoff = $this->EmployeeDetails->query("SELECT week_off_days,working_days,days_presant,days_leave FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
            // debug($holiday);die();
            $holiday_count = isset($holiday[0][0]['count']) ? $holiday[0][0]['count'] : 0;
            $weekoffdays = isset($weekoff[0]['payroll_master']['week_off_days']) ? $weekoff[0]['payroll_master']['week_off_days'] : 0;
            $day_count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $day['days'] = isset($weekoff[0]['payroll_master']['working_days']) ? $weekoff[0]['payroll_master']['working_days'] : 0; //$day_count - $weekoffdays - $holiday_count;
            $leave = isset($weekoff[0]['payroll_master']['days_leave']) ? $weekoff[0]['payroll_master']['days_leave'] : 0;
        } else {
            $day['type'] = "Fixed Days";
            $day['days'] = 30;
            //added by megha on 7_5_19
            $weekoff = $this->EmployeeDetails->query("SELECT week_off_days,working_days,days_presant,days_leave FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
            $leave = isset($weekoff[0]['payroll_master']['days_leave']) ? $weekoff[0]['payroll_master']['days_leave'] : 0;
            //added by megha on 7_5_19
        }
        $day['present'] = isset($weekoff[0]['payroll_master']['days_presant']) ? $weekoff[0]['payroll_master']['days_presant'] + $leave : 0; //$day['days'] - $lop;
        return $day;
    }
    // Edited by Akshay on 30-7-2025
    public function epfUploadReport($from, $type, $subcat, $branch)
    {
        // $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

        $arr_epf_fkey = $this->EmpCtcTransaction->query("SELECT salary_head_item_fkey FROM tax_salary_components WHERE LCASE(tax_salary_components_name) = 'employee epf' AND status = 1");
        $epf_fkey = $arr_epf_fkey[0]['tax_salary_components']['salary_head_item_fkey'];

        $conditions = array();
        $conditions[] = 'and ectc.month_year="' . $from . '"';
        $id = implode(' AND ', $conditions);


        // Edited by Akshay on 11-12-2025
        $checkColumn = $this->EmpCtcTransaction->query("
                                                        SELECT COUNT(*) cnt
                                                        FROM information_schema.COLUMNS
                                                        WHERE TABLE_SCHEMA = DATABASE()
                                                        AND TABLE_NAME = 'attendance_register'
                                                        AND COLUMN_NAME = 'lop_only'
                                                    ");
        // Edited by Akshay on 12-2-2026
        // Default
        $lopField = 'ar.lop_total';

        // If column exists AND month condition satisfied
        if ($checkColumn[0][0]['cnt'] > 0 && $from > '2026-01') {
            $lopField = 'ar.lop_only';
        }
        // End

        $ncp_days_query = " ,(
                                SELECT $lopField
                                FROM attendance_register ar
                                WHERE ar.emp_fkey = employee_info.emp_pkey
                                AND ar.month_year = '$from'
                            ) AS NCP_days ";

        // End


        // Edited by Akshay on 12-5-2026
        $branch_condition = '';

        if ($branch != '') {

            $branches = explode(',', $branch);

            $branches = array_map(function ($v) {
                return "'" . trim($v) . "'";
            }, $branches);

            $branch_condition = " AND emp_details.branch_code IN (" . implode(',', $branches) . ")";
        }
        // End

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
                (SELECT (presant_total + leave_total +  weekoff_total + holiday_total) FROM attendance_register WHERE emp_fkey = employee_info.emp_pkey AND month_year = '$from') as total_days,
                abs(ifnull((select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= employee_info.emp_pkey AND lcase(ectc.item_part) = 'direct' 
                AND ectc.end_date_effective is null and ectc.head_type != 'Arrear' and head_operator = 'Addition' $id ),0)) SALARY, 
                
                payroll_master.loss_of_pay, payroll_master.eps, payroll_master.calander_days $ncp_days_query
            

                from employee_info left join emp_details on (emp_details.emp_pkey = employee_info.emp_pkey) 
                LEFT JOIN payroll_master ON payroll_master.emp_fkey = employee_info.emp_pkey AND payroll_master.month_year = '$from' AND payroll_master.action IN ('Approved', 'Processed')
                where employee_info.emp_pkey in (select emp_fkey from emp_salary_slip where month_year='$from' 
                        and end_date_effective is null and emp_salary_slip.salary_head_item_fkey in(select salary_head_item_Fkey from tax_salary_components
                        where lcase(tax_salary_components_name)= 'employee epf' and status=1) and salary_amount != 0)

                AND emp_details.status in ('1',2) AND (emp_details.pf IS NOT NULL AND emp_details.pf !='' AND  emp_details.pf != '0' and emp_details.eps is not null OR emp_details.esi IS NOT NULL) 
                AND emp_details.pf IS NOT NULL AND emp_details.pf !='' AND  emp_details.pf != '0'
                $branch_condition
                -- ORDER BY employee_info.EmpName;
                ");


        $arr_salary_for_template = array();

        foreach ($arr_gross as &$item) {
            if (isset($item[0]) && is_array($item[0])) {
                $epf_salary = 0;
                $emp_id = isset($item['employee_info']['emp_pkey']) ? $item['employee_info']['emp_pkey'] : '';
                if ($emp_id != '') {
                    // 1. Get ESI formula for this employee
                    $columns = $this->EmpCtcTransaction->getDataSource()->query("
                                                SHOW COLUMNS FROM emp_salary_slip LIKE 'combined_base_value'
                                            ");

                    $hasRemarks2 = !empty($columns); // true if combined_base_value exists
                    $hasRemarks2 = ($from > '2025-08') ? $hasRemarks2 : false; // Edited by Akshay on 30-9-2025
                    if ($hasRemarks2) {
                        $formula_result = $this->EmpCtcTransaction->query("
                                                    SELECT combined_base_value, salary_amount 
                                                    FROM emp_salary_slip 
                                                    WHERE salary_head_item_fkey = $epf_fkey 
                                                    AND emp_fkey = $emp_id
                                                    AND month_year = '$from'
                                                    AND end_date_effective IS NULL
                                                ");
                        $epf_salary = isset($formula_result[0]['emp_salary_slip']['combined_base_value']) ? $formula_result[0]['emp_salary_slip']['combined_base_value'] : 0;
                        $epf_salary = min($epf_salary, 15000); // Edited by Akshay on 1-10-2025
                    } else {
                        $formula_result = $this->EmpCtcTransaction->query("
                                                    SELECT remarks, salary_amount 
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

                            $salary_amount =  isset($row['salary_amount']) ? $row['salary_amount'] : 0;
                            // $epf_salary = round($lhs_value, 2);
                        }


                        // 2. Parse and calculate ESI salary
                        if (!empty($formula_string)) {
                            $parts = explode('*', $formula_string);
                            $sum_part = trim($parts[0]); // (15000 + 2000 + 3000 )

                            // Step 2: Remove all spaces
                            $sum_part = str_replace(' ', '', $sum_part); // (15000+2000+3000)

                            // Step 3: Evaluate the expression
                            if (preg_match('/[a-zA-Z]/', $sum_part)) {
                                $epf_salary = $salary_amount;
                            } else {
                                eval('$epf_salary = ' . $sum_part . ';');
                            }
                            $epf_salary = is_numeric($epf_salary) ? min(15000, $epf_salary) : 0;
                        }
                    }
                }


                // if ($epf_salary > 0) {
                if (true) {
                    $emp_pkey = isset($item['employee_info']['emp_pkey']) ? $item['employee_info']['emp_pkey'] : '';
                    $is_eps = isset($item['payroll_master']['eps']) ? $item['payroll_master']['eps'] : 'Y'; // Edited by Akshay on 4-10-2025
                    $eps = ($is_eps == 'Y') ? $epf_salary : 0;
                    $arr_salary_for_template[$emp_pkey]['uan'] = isset($item['emp_details']['pf']) ? $item['emp_details']['pf'] : '';
                    $arr_salary_for_template[$emp_pkey]['name'] = isset($item['employee_info']['EmpName']) ? trim($item['employee_info']['EmpName']) : '';
                    $arr_salary_for_template[$emp_pkey]['gross'] = isset($item[0]['SALARY']) ? $item[0]['SALARY'] : 0;
                    $arr_salary_for_template[$emp_pkey]['epf'] = $epf_salary;
                    $arr_salary_for_template[$emp_pkey]['eps'] = $eps;
                    $arr_salary_for_template[$emp_pkey]['edli'] = $epf_salary;
                    $arr_salary_for_template[$emp_pkey]['epf_contr'] = round($epf_salary * .12);
                    $arr_salary_for_template[$emp_pkey]['eps_contr'] = round($eps * .0833);
                    $arr_salary_for_template[$emp_pkey]['edli_contr'] = (round($epf_salary * .12) - round($eps * .0833));
                    // Edited by Akshay on 4-10-2025
                    $calendar_days = isset($item['payroll_master']['calander_days']) ? ($item['payroll_master']['calander_days']) : 0;
                    $working_days = isset($item[0]['total_days']) ? ($item[0]['total_days']) : 0;
                    $lop = isset($item[0]['NCP_days']) ? $item[0]['NCP_days'] : 0;
                    $lop = max(0, $lop);
                    $arr_salary_for_template[$emp_pkey]['ncp'] = floor($lop);
                    // End
                    $arr_salary_for_template[$emp_pkey]['zero'] = 0;
                }
            }
        }
        unset($item); // break the reference
        // $arr_salary_for_template[] = $arr_gross;
        // debug($arr_salary_for_template);
        // exit;
        //End


        if (isset($arr_salary_for_template)) {
            $this->set('arr_salary_for_template', $arr_salary_for_template);
            $this->render($type);
        } else {
            echo "No data found!";
        }


        // echo "<pre>";
        // print_r($arr_salary_for_template);



    }
}
