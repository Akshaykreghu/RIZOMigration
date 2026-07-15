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
class EmployeeResignationController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'EmployeeResignation';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'ResignationRequests', 'EmployeeConfig', 'qualifcations', 'EmpSalarySlip', 'Termination', 'LeaveEncashmentMaster', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index()
    {
        //Edited by Akshay on 11-6-2024
        $company_code = $this->Session->read('company_code');
        $this->set('company_code', $company_code);
        //End
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

    public function getperiod($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $notice_period = $employee = $this->EmployeeDetails->query("select notice_days,joining_date from emp_proff where emp_fkey = '$emp_fkey' ");
        $days = isset($notice_period['0']['emp_proff']['notice_days']) ? $notice_period['0']['emp_proff']['notice_days'] : 0;
        $joining_date = isset($notice_period['0']['emp_proff']['joining_date']) ? $notice_period['0']['emp_proff']['joining_date'] : '';
        if ($notice_period) {
            echo json_encode(array('success' => 1, 'days' => $days, 'dates' => $joining_date));
        } else {
            echo json_encode(array('success' => 0, 'days' => 0, 'dates' => ''));
        }
    }

    public function form($emp_pkey = 0)
    {
        $this->Termination->useDbConfig = $this->Session->read('ds');
        //edited by megha 
        $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeInfo.*"), "conditions" => array("Termination.status" => 1, "emp_fkey" => $emp_pkey), "joins" => array(
            array(
                "table" => "employee_info",
                "alias" => "EmployeeInfo",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
            ),
            array(
                "table" => "emp_details",
                "alias" => "EmployeeDetails",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
            )
        )));

        $this->set("details", $details);
        //debug($details);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProffessional.emp_fkey')
            )
        );

        // Edited by Akshay on 6-2-2025
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $cur_emp_key]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = "branch_code = '$is_ho'";
            }
        }
        // End

        //edited by megha on 28_5_19 status condition added in termination table
        //debug($emp_pkey);
        if ($emp_pkey != 0) {
            $employee = $this->EmployeeDetails->find("all", array('order' =>  array('first_name ASC'), "conditions" => array("status" => "1", "emp_pkey IN ($emp_pkey) ", !empty($branch_condition) ? $branch_condition : null), "joins" => $joins, "fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.first_name", "EmployeeDetails.last_name", "EmployeeProffessional.emp_company_id", "EmployeeProffessional.notice_days")));
        } else {
            $employee = $this->EmployeeDetails->find("all", array('order' =>  array('first_name ASC'), "conditions" => array("status" => "1", "emp_pkey NOT IN (SELECT emp_fkey FROM termination where status = 1) ", !empty($branch_condition) ? $branch_condition : null), "joins" => $joins, "fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.first_name", "EmployeeDetails.last_name", "EmployeeProffessional.emp_company_id", "EmployeeProffessional.notice_days")));
        }
        $this->set("employee", $employee);
    }

    public function save_heads()
    {
        $this->autoRender = false;
        $user = $this->Session->read('login_user_id'); //Edited by Akshay on 10-10-2024
        $str_company_code = $this->Session->read('company_code'); // Edited by Akshay on 5-3-2024
        $data = $this->request->data;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Termination->useDbConfig = $this->Session->read('ds'); //Edited by Akshay on 14-3-2024
        if (!isset($data['head_vals'])) {
            $data['head_vals'] = array();
        }

        for ($i = 0; $i < count($data['head_vals']); $i++) {
            $val = $data['head_vals'][$i];
            $emp_settle_slip_pkey = $data['head_fkey'][$i];

            //   $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$val' WHERE emp_settle_slip_pkey = '$emp_settle_slip_pkey' and status = 'Y' ");
            if ($str_company_code == 'KWMT') { //Edited by Akshay on 26-3-2024
                $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$val',approved = 'Y' WHERE emp_settle_slip_pkey = '$emp_settle_slip_pkey' and status = 'Y'");
            } else {
                //edited by megha on 19/11/2019 approved added
                $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$val',approved = 'Y' WHERE emp_settle_slip_pkey = '$emp_settle_slip_pkey' and status = 'Y' ");
            }
        }

        //Edited by Akshay on 20-12-2023
        if (!isset($data['head_vals2'])) {
            $data['head_vals2'] = array();
        }
        $i = 0;
        for ($i = 0; $i < count($data['head_vals2']); $i++) {
            $val = isset($data['head_vals2'][$i]) ? $data['head_vals2'][$i] : 0;
            $val = 0 - abs($val); //Edited by Akshay on 23-8-2024

            $emp_settle_slip_pkey = $data['head_fkey2'][$i];

            //   $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$val' WHERE emp_settle_slip_pkey = '$emp_settle_slip_pkey' and status = 'Y' ");
            if ($str_company_code == 'KWMT') { //Edited by Akshay on 26-3-2024
                $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$val',approved = 'Y' WHERE emp_settle_slip_pkey = '$emp_settle_slip_pkey' and status = 'Y'");
            } else {
                //edited by megha on 19/11/2019 approved added = 
                $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$val',approved = 'Y' WHERE emp_settle_slip_pkey = '$emp_settle_slip_pkey' and status = 'Y' ");
            }
        }

        //Edited by Akshay on 20-12-2023
        $emp_pkey = isset($data['emp_pkey']) ? $data['emp_pkey'] : '';
        $id_card_amt =  isset($data['id_card']) ? $data['id_card'] : '';
        $resign_month = isset($data['hidden_resign_month']) ? $data['hidden_resign_month'] : '';

        //Edited by Akshay on 10-10-2024
        $notice_pay =  isset($data['notice_pay']) ? $data['notice_pay'] : '';
        //End

        //Edited by akshay on 11-7-2024
        $other_ded =  isset($data['other_ded']) ? $data['other_ded'] : '';
        //End

        if ($str_company_code == 'KWMT') {
            // edited by sinsiya for water metro  start
            $amt_paid_by_empdeduction = isset($data['amt_Empded']) ? abs($data['amt_Empded']) : '';
            $amt_paid_by_empaddition = isset($data['amt_Empadd']) ? $data['amt_Empadd'] : '';

            //Edited by Akshay on 14-3-2024
            $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeInfo.*"), "conditions" => array("emp_fkey" => $emp_pkey), "joins" => array(
                array(
                    "table" => "employee_info",
                    "alias" => "EmployeeInfo",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
                ),
                array(
                    "table" => "emp_details",
                    "alias" => "EmployeeDetails",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
                )
            )));

            $submitted_dat = $from = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
            $newdate = $todate = isset($details['0']['Termination']['last_approved_working_date']) ? $details['0']['Termination']['last_approved_working_date'] : '';

            $date1 = date_create($newdate);
            $date2 = date_create($submitted_dat);
            $date1->modify('+1 day'); //Edited by Akshay on 13-3-2024
            $diff = date_diff($date1, $date2);
            //Edited by Akshay on 4-9-2024
            $from_reduced = date('Y-m-d', strtotime($from . ' -1 day'));
            $todate_increased = date('Y-m-d', strtotime($todate . ' +1 day'));
            $diff_with_weekoff_days = $this->EmployeeDetails->query("select weekoff_days_count_fn('$emp_pkey','$todate_increased','$from_reduced') no_of_weekof");
            // debug($diff_with_weekoff_days );
            $hol = isset($exec_days_holidays['0']['0']['days_count']) ? $exec_days_holidays['0']['0']['days_count'] : 0;
            $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days['0']['0']['no_of_weekof']) + $hol : 0;

            $arr_att_days_after_resignation = "select sum(a) a from(
                select count(*) as a from emp_detail_timeattandance where others in ('LOP','LOP/LOP') and 	emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
                union all
                select count(*)/2 as a from emp_detail_timeattandance where (instr(others,'/LOP') > 0 or instr(others,'LOP/') > 0 ) and others!= 'LOP'   and	 emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
                )ass ";
            $exec_days = $this->EmployeeDetails->query($arr_att_days_after_resignation);

            $days_after_resignation_att = isset($exec_days['0']['0']['a']) ? round($exec_days['0']['0']['a'], 1) : 0;

            $offday = $days_after_resignation_att + $offs;
            if ($offday > 0) {
                $offday = $offday + 1;
            }
            if ($offs > 0) {
                $offs = $offs + 1;
            }

            $working_days_settled = isset($diff) ? $diff->format("%a") - $offs : 0;

            $payroll_days = isset($diff) ? $diff->format("%a") - $offday : 0;

            $this->EmployeeDetails->query("UPDATE termination SET amt_paid_by_empaddition = '$amt_paid_by_empaddition',amt_paid_by_empdeduction= '$amt_paid_by_empdeduction'  WHERE emp_fkey = '$emp_pkey' AND status = '1'"); //Edited by Akshay on 14-3-2024
            //$this->EmployeeDetails->query("INSERT INTO termination (amt_paid_by_empdedu ction,amt_paid_by_empaddition, emp_fkey) VALUES ('$amt_paid_by_empdeduction','$amt_paid_by_empaddition', '$emp_pkey')");
            //edited by sinsiya for water metro end
            if ($id_card_amt != '') {
                $id_card_amt = 0 - abs($id_card_amt);
                $arr_id_card = $this->EmployeeDetails->query("SELECT emp_settle_slip_pkey FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' AND type ='ID' AND status = 'Y'");
                //debug($arr_id_card); exit;
                if (count($arr_id_card) > 0) {
                    $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$id_card_amt', status = 'Y', month_year = '$resign_month', created_by = 'admin'  WHERE emp_fkey = '$emp_pkey' AND type ='ID' AND status = 'Y' ");
                } else {
                    $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (salary_amount, emp_fkey, type, status, approved, salary_head_item_desc, month_year, created_by) VALUES ('$id_card_amt', '$emp_pkey', 'ID', 'Y', 'Y','ID Card', '$resign_month', 'admin')");
                }
            }

            //Edited by Akshay on 11-7-2024
            if ($other_ded != '') {
                $other_ded = 0 - abs($other_ded);
                $arr_other_ded = $this->EmployeeDetails->query("SELECT emp_settle_slip_pkey FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' AND type ='OTHER_DED' AND status = 'Y'");
                //debug($arr_id_card); exit;
                if (count($arr_other_ded) > 0) {
                    $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$other_ded', status = 'Y', month_year = '$resign_month', created_by = 'admin'  WHERE emp_fkey = '$emp_pkey' AND type ='OTHER_DED' AND status = 'Y' ");
                } else {
                    $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (salary_amount, emp_fkey, type, status, approved, salary_head_item_desc, month_year, created_by) VALUES ('$other_ded', '$emp_pkey', 'OTHER_DED', 'Y', 'Y','Other', '$resign_month', '$user')");
                }
            }
            //End

            //Edited by Akshay on 10-10-2024
            if ($notice_pay != '') {
                $notice_pay = 0 - abs($notice_pay);
                $arr_other_ded = $this->EmployeeDetails->query("SELECT emp_settle_slip_pkey FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' AND type ='NOTICE_PAY' AND status = 'Y'");
                //debug($arr_id_card); exit;
                if (count($arr_other_ded) > 0) {
                    $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$notice_pay', status = 'Y', month_year = '$resign_month', created_by = 'admin'  WHERE emp_fkey = '$emp_pkey' AND type ='NOTICE_PAY' AND status = 'Y' ");
                } else {
                    $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (salary_amount, emp_fkey, type, status, approved, salary_head_item_desc, month_year, created_by) VALUES ('$notice_pay', '$emp_pkey', 'NOTICE_PAY', 'Y', 'Y','Notice Pay', '$resign_month', '$user')");
                }
            }
            //End
        }
        return 1;
    }

    /*
     * Employees landing view
     */



    public function approves($emp_pkey = 0, $leaves = 0)
    {
        $user = $this->Session->read('login_user_id'); //Edited by Akshay on 25-3-2024
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $str_company_code = $this->Session->read('company_code'); //Edited by Akshay on 25-3-2024
        $this->set('str_company_code', $str_company_code);


        // Edited by Akshay on 25-2-2025
        if ($str_company_code == 'KWMT') {
            $this->EmployeeDetails->query("UPDATE `emp_settle_slip`
            SET `status` = 'N'
            WHERE `emp_fkey` = '$emp_pkey' 
            AND `status` = 'Y';");
        }
        // End
        $employee = $this->EmployeeDetails->query("SELECT * FROM `payroll_master` WHERE `emp_fkey` = '$emp_pkey' AND `action` != 'Approved'  and month_year > '2018-04'  ORDER BY month_year ");
        $this->set("employee", $employee);
        try {
            $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeInfo.*"), "conditions" => array("emp_fkey" => $emp_pkey, "Termination.status" => 1), "joins" => array( //Edited by Akshay on 10-10-2024
                array(
                    "table" => "employee_info",
                    "alias" => "EmployeeInfo",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
                ),
                array(
                    "table" => "emp_details",
                    "alias" => "EmployeeDetails",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
                )
            )));
        } catch (Exception $e) {
            debug($e);
        }
        //debug($details);exit;
        $branch = isset($details['0']['EmployeeDetails']['branch_code']) ? $details['0']['EmployeeDetails']['branch_code'] : '';
        $approved_date = isset($details['0']['Termination']['last_approved_working_date']) ? date("Y-m", strtotime($details['0']['Termination']['last_approved_working_date'])) : '';
        $this->set('approved_date', $approved_date);
        $emp_pkey = isset($details['0']['Termination']['emp_fkey']) ? $details['0']['Termination']['emp_fkey'] : '';
        $encashment_master = $this->leaveencash($emp_pkey, $leaves, 0);

        //Edited by Akshay on 25-3-2024
        $submitted_date = isset($details['0']['Termination']['submitted_date']) ? date("Y-m", strtotime($details['0']['Termination']['submitted_date'])) : '';
        if ($submitted_date != '') {
            $submitted_date = $submitted_date . '-01';
            $arr_start_end = $this->EmployeeDetails->query("SELECT `att_start_end_fn`('$submitted_date', '1') as start_date, `att_start_end_fn`('$submitted_date', '2') as end_date");
            $start_date = isset($arr_start_end[0][0]['start_date']) ? $arr_start_end[0][0]['start_date'] : '';
            $this->set('start_date', $start_date);
            $end_date = isset($arr_start_end[0][0]['end_date']) ? date('Y-m', strtotime($arr_start_end[0][0]['end_date'])) : '';
            $this->set('end_date', $end_date);

           // $arr_leaves_heads = $this->EmployeeDetails->query("CALL `final_settle_pay_prc`('$branch', '$end_date', '$emp_pkey', '0', '0', 'admin', @`perror_message`) "); // Edited by Akshay on 25-2-2025

            //Edited by Akshay on 6-9-2024
            $submitted_month = date("Y-m", strtotime($end_date));
            //End
        }
        //Edited by Akshay on 6-9-2024
        $arr_aprove_mnth = $this->EmployeeDetails->query("SELECT `att_start_end_fn`(CONCAT('$approved_date', '-01'), '2') as end_date");
        $aprove_mnth = isset($arr_aprove_mnth[0][0]['end_date']) ? date('Y-m', strtotime($arr_aprove_mnth[0][0]['end_date'])) : '';
        $start = new DateTime($submitted_month . '-01');
        $end = new DateTime($aprove_mnth . '-01');
        $end->modify('last day of this month');
        $interval = new DateInterval('P1M');
        $period = new DatePeriod($start, $interval, $end);
        $months = [];
        foreach ($period as $date) {
            $months[] = $date->format('Y-m'); // Get the year-month format
        }
        $months[] = $end->format('Y-m');
        $arr_unique_months = array_unique($months);
        //End
        // Leave Excess Find 
        try {
            //Edited by Akshay on 6-9-2024
            if ($str_company_code == 'KWMT') {
                $user_id = $this->Session->read("login_user_id");
                 $arr_leaves_heads = $this->EmployeeDetails->query("CALL `final_settle_pay_prc`('$branch', '$approved_date', '$emp_pkey', '0', '0', 'admin', @`perror_message`) ");
                  
                foreach ($arr_unique_months as $unique_month) {
                    $arr_payroll_master_pkey = $this->EmployeeDetails->query("SELECT distinct payroll_master_pkey, branch_code  from payroll_master where emp_fkey = '$emp_pkey' and month_year = '$unique_month' and action in ('Processed','Approved');");
                    $payroll_master_pkey = isset($arr_payroll_master_pkey[0]['payroll_master']['payroll_master_pkey']) ? $arr_payroll_master_pkey[0]['payroll_master']['payroll_master_pkey'] : 0;
                    $branch = isset($arr_payroll_master_pkey[0]['payroll_master']['branch_code']) ? $arr_payroll_master_pkey[0]['payroll_master']['branch_code'] : 0;
                
                    if ($payroll_master_pkey == 0) {
                   
                        $this->EmployeeDetails->query("CALL `salary_process_prc`('$unique_month', '$branch', '$emp_pkey', '$payroll_master_pkey', '$user_id', @perr_msg) ");
                        $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds');
                        $arr_formulae_from_remarks = $this->EmpSalarySlip->find(
                            "all",
                            array(
                                'fields' => 'emp_salary_slip_pkey,head_operator,remarks,salary_head_item_desc',
                                'conditions' => array(
                                    'payroll_master_fkey' => $payroll_master_pkey,
                                    'remarks IS NOT NULL',
                                    'end_date_effective is null'
                                )
                            )
                        );


                        foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                            $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey']) ? $row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey'] : '';
                            $head_operator = isset($row_formulae_from_remarks['EmpSalarySlip']['head_operator']) ? $row_formulae_from_remarks['EmpSalarySlip']['head_operator'] : '';
                            $formula_from_remarks = isset($row_formulae_from_remarks['EmpSalarySlip']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmpSalarySlip']['remarks']) : '';
                            // debug($formula_from_remarks); exit;
                            $salary_head_item_desc = isset($row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc'] : '';
                            //debug($row_formulae_from_remarks);
                            if (!empty($formula_from_remarks)) {
                                //Edited by Akshay on 19-4-2024
                                $pattern = '/^[\d\+\-\*\/\(\)\s]+$/';
                                if (preg_match($pattern, $formula_from_remarks)) {
                                    eval('$salary_amount = ' . $formula_from_remarks . ';');
                                } else {
                                    $salary_amount = 0;
                                }

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
                                        'EmpSalarySlip.structure_det_value' => $salary_amount,
                                        'EmpSalarySlip.salary_rate' => $salary_amount,
                                        'EmpSalarySlip.salary_amount' => $salary_amount
                                    );
                                } else {
                                    $salary_amount = round($salary_amount);
                                    if ($head_operator == 'Deduction') {
                                        $salary_amount *= -1;
                                    }
                                    $arr_emp_salary_slip_data = array(
                                        'EmpSalarySlip.salary_rate' => $salary_amount,
                                        'EmpSalarySlip.salary_amount' => $salary_amount
                                    );
                                }


                                $this->EmpSalarySlip->updateAll(
                                    $arr_emp_salary_slip_data,
                                    array('EmpSalarySlip.emp_salary_slip_pkey' => $emp_salary_slip_pkey)
                                );
                            }
                        }
                        $this->EmployeeDetails->query("CALL `tax_salary_process_prc`('$unique_month', '$branch', '$emp_pkey', '$payroll_master_pkey', '$user_id', @perr_msg) ");

                        // Edited by Akshay on 25-2-2025
                    }
                        if (($unique_month != $submitted_month) && count($arr_unique_months) > 1) {
                            $arr_payroll_master_pkey = $this->EmployeeDetails->query("SELECT payroll_master_pkey FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$unique_month'");
                            $payroll_master_pkey = isset($arr_payroll_master_pkey[0]['payroll_master']['payroll_master_pkey']) ? $arr_payroll_master_pkey[0]['payroll_master']['payroll_master_pkey'] : 0;

                            $arr_salary_slip = $this->EmployeeDetails->query("SELECT ectc.salary_head_item_fkey, ectc.salary_head_item_desc, ectc.structure_det_value, ectc.salary_rate, ectc.salary_amount FROM emp_salary_slip ectc WHERE ectc.payroll_master_fkey = '$payroll_master_pkey' AND ectc.emp_fkey ='$emp_pkey' AND ectc.month_year ='$unique_month' AND ectc.item_part = 'Direct' and end_date_effective is null");

                            foreach ($arr_salary_slip as $item) {
                                $salary_head_item_fkey = isset($item['ectc']['salary_head_item_fkey']) ? $item['ectc']['salary_head_item_fkey'] : 0;
                                $salary_head_item_desc = isset($item['ectc']['salary_head_item_desc']) ? $item['ectc']['salary_head_item_desc'] : '';
                                if ($salary_head_item_desc !== '') {
                                    $salary_head_item_desc = trim($salary_head_item_desc) . ' for the month-' . $unique_month;
                                }
                                $structure_det_value = isset($item['ectc']['structure_det_value']) ? $item['ectc']['structure_det_value'] : null;
                                $salary_rate = isset($item['ectc']['salary_rate']) ? $item['ectc']['salary_rate'] : null;
                                $salary_amount = isset($item['ectc']['salary_amount']) ? $item['ectc']['salary_amount'] : null;
                               //  if ($payroll_master_pkey == 0) {
                               // if (true) {
                               if ($unique_month != $approved_date) {
                                    $arr_leaves_heads2 = $this->EmployeeDetails->query("INSERT INTO `emp_settle_slip` (`payroll_master_fkey`, `emp_fkey`, `type`, `month_year`, `salary_head_item_fkey`, `salary_head_item_desc`, `structure_det_value`, `salary_rate`, `salary_amount`, `presant_total`, `leave_total`, `lop_total`, `head_operator`, `head_type`, `item_part`,  `created_by`, `modified_by`, `remarks`, `approved`, `status`, `action`) 
                            VALUES ($payroll_master_pkey, $emp_pkey, 'SALARY', '$unique_month', '$salary_head_item_fkey', '$salary_head_item_desc', 0, '$salary_amount', '$salary_amount', NULL, NULL, NULL, NULL, NULL, NULL, '$user', NULL, NULL, 'N', 'Y', NULL);");
                               }
                            //}
                        }
                        // End
                         }
                 //  $arr_leaves_heads = $this->EmployeeDetails->query("CALL `final_settle_pay_prc`('$branch', '$approved_date', '$emp_pkey', '0', '0', 'admin', @`perror_message`) "); // Edited by Akshay on 25-2-2025

                }
                $arr_payroll_master_pkey = $this->EmployeeDetails->query("SELECT distinct branch_code  from payroll_master where emp_fkey = '$emp_pkey' and month_year = '$approved_date' ;");
                $branch = isset($arr_payroll_master_pkey[0]['payroll_master']['branch_code']) ? $arr_payroll_master_pkey[0]['payroll_master']['branch_code'] : 0;
            }
            //End
            
               // if ((count($arr_unique_months) <= 1) || ($str_company_code != 'KWMT' && $str_company_code != 'DEMO' && $str_company_code != 'GLET')) {
                    $arr_leaves_heads = $this->EmployeeDetails->query("CALL `final_settle_pay_prc`('$branch', '$approved_date', '$emp_pkey', '0', '0', 'admin', @`perror_message`) ");
              //  }


            //Edited by Akshay on 25-3-2024

            if ($str_company_code == 'KWMT' )
                if ($end_date != $approved_date) {
                    $arr_payroll_master_pkey = $this->EmployeeDetails->query("SELECT payroll_master_pkey FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$end_date'");
                    $payroll_master_pkey = isset($arr_payroll_master_pkey[0]['payroll_master']['payroll_master_pkey']) ? $arr_payroll_master_pkey[0]['payroll_master']['payroll_master_pkey'] : 0;

                    $arr_salary_slip = $this->EmployeeDetails->query("SELECT ectc.salary_head_item_fkey, ectc.salary_head_item_desc, ectc.structure_det_value, ectc.salary_rate, ectc.salary_amount FROM emp_salary_slip ectc WHERE ectc.payroll_master_fkey = '$payroll_master_pkey' AND ectc.emp_fkey ='$emp_pkey' AND ectc.month_year ='$end_date' AND ectc.item_part = 'Direct' and end_date_effective is null");

                    foreach ($arr_salary_slip as $item) {
                        $salary_head_item_fkey = isset($item['ectc']['salary_head_item_fkey']) ? $item['ectc']['salary_head_item_fkey'] : 0;
                        $salary_head_item_desc = isset($item['ectc']['salary_head_item_desc']) ? $item['ectc']['salary_head_item_desc'] : '';
                        if ($salary_head_item_desc !== '') {
                            $salary_head_item_desc = trim($salary_head_item_desc) . ' for the month-' . $end_date;
                        }
                        $structure_det_value = isset($item['ectc']['structure_det_value']) ? $item['ectc']['structure_det_value'] : null;
                        $salary_rate = isset($item['ectc']['salary_rate']) ? $item['ectc']['salary_rate'] : null;
                        $salary_amount = isset($item['ectc']['salary_amount']) ? $item['ectc']['salary_amount'] : null;
                        // if ($payroll_master_pkey == 0) {
                      //  if (true) {
                            $arr_leaves_heads2 = $this->EmployeeDetails->query("INSERT INTO `emp_settle_slip` (`payroll_master_fkey`, `emp_fkey`, `type`, `month_year`, `salary_head_item_fkey`, `salary_head_item_desc`, `structure_det_value`, `salary_rate`, `salary_amount`, `presant_total`, `leave_total`, `lop_total`, `head_operator`, `head_type`, `item_part`,  `created_by`, `modified_by`, `remarks`, `approved`, `status`, `action`) 
                    VALUES ($payroll_master_pkey, $emp_pkey, 'SALARY', '$end_date', '$salary_head_item_fkey', '$salary_head_item_desc', 0, '$salary_amount', '$salary_amount', NULL, NULL, NULL, NULL, NULL, NULL, '$user', NULL, NULL, 'N', 'Y', NULL);");
                       // }
                    }
                }
        } catch (Exception $e) {
            debug($e);
            exit;
        }
        // $this->set('arr_leaves', $arr_leaves_heads);
        $arr_formulae_from_remarks = $this->EmployeeDetails->query("select emp_settle_slip_pkey,remarks,payroll_master_fkey from emp_settle_slip where emp_fkey ='$emp_pkey' and status = 'Y' and action = 'P'");

        foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
            $emp_settle_slip_pkey = isset($row_formulae_from_remarks['emp_settle_slip']['emp_settle_slip_pkey']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['emp_settle_slip']['emp_settle_slip_pkey']) : '';
            $formula_from_remarks = isset($row_formulae_from_remarks['emp_settle_slip']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['emp_settle_slip']['remarks']) : '';
            $payroll_master_fkey = isset($row_formulae_from_remarks['emp_settle_slip']['payroll_master_fkey']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['emp_settle_slip']['payroll_master_fkey']) : '';
            if (!empty($formula_from_remarks)) {
                eval('$salary_amount = ' . $formula_from_remarks . ';');

                $result = $this->EmployeeDetails->query("update emp_settle_slip set salary_rate = '$salary_amount' , salary_amount = '$salary_amount' where emp_settle_slip_pkey ='$emp_settle_slip_pkey'");
            }
        }
        $payroll_master_fkey = isset($payroll_master_fkey) ? $payroll_master_fkey : '';
        if ($payroll_master_fkey > 0) {
            $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds');
            $arr_formulae_from_remarks1 = $this->EmpSalarySlip->find(
                "all",
                array(
                    'fields' => 'emp_salary_slip_pkey,head_operator,remarks,salary_head_item_desc',
                    'conditions' => array(
                        'payroll_master_fkey' => $payroll_master_fkey,
                        'remarks IS NOT NULL',
                        'end_date_effective is null'
                    )
                )
            );
            //				debug($arr_formulae_from_remarks);

            foreach ($arr_formulae_from_remarks1 as $row_formulae_from_remarks) {
                $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey']) ? $row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey'] : '';
                $head_operator = isset($row_formulae_from_remarks['EmpSalarySlip']['head_operator']) ? $row_formulae_from_remarks['EmpSalarySlip']['head_operator'] : '';
                $formula_from_remarks = isset($row_formulae_from_remarks['EmpSalarySlip']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmpSalarySlip']['remarks']) : '';
                $salary_head_item_desc = isset($row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc'] : '';
                if (!empty($formula_from_remarks)) {
                    eval('$salary_amount = ' . $formula_from_remarks . ';');

                    if (trim(strtolower($salary_head_item_desc)) == 'esi') {
                        $salary_amount = ceil($salary_amount);
                        if ($head_operator == 'Deduction') {
                            $salary_amount *= -1;
                        }
                        $arr_emp_salary_slip_data = array(
                            'EmpSalarySlip.salary_rate' => $salary_amount,
                            'EmpSalarySlip.salary_amount' => $salary_amount
                        );
                    } else {
                        $salary_amount = round($salary_amount);
                        if ($head_operator == 'Deduction') {
                            $salary_amount *= -1;
                        }
                        $arr_emp_salary_slip_data = array(
                            'EmpSalarySlip.salary_rate' => $salary_amount,
                            'EmpSalarySlip.salary_amount' => $salary_amount
                        );
                    }


                    $this->EmpSalarySlip->updateAll(
                        $arr_emp_salary_slip_data,
                        array('EmpSalarySlip.emp_salary_slip_pkey' => $emp_salary_slip_pkey)
                    );
                }
            }
        }

        //Edited by Akshay on 20-12-2023
        $arr_leave_balance =  $this->EmployeeDetails->query("SELECT leave_total, month_year FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' AND type = 'BALANCE' AND status = 1");
        $leave_balance = isset($arr_leave_balance[0]['emp_settle_slip']['leave_total']) ? $arr_leave_balance[0]['emp_settle_slip']['leave_total'] : '';
        $month_year = isset($arr_leave_balance[0]['emp_settle_slip']['month_year']) ? $arr_leave_balance[0]['emp_settle_slip']['month_year'] : '';

        if ($leave_balance != '') {
            //            $arr_annual_ctc = $this->EmployeeDetails->query("SELECT emp_anual_ctc FROM emp_ctc_transaction ect WHERE ect.end_date_effective IS NULL AND ect.emp_fkey = '$emp_pkey'");
            //            $annual_ctc = isset($arr_annual_ctc[0]['ect']['emp_anual_ctc']) ? $arr_annual_ctc[0]['ect']['emp_anual_ctc'] : 0;
            //            $arr_lop_days = $this->EmployeeDetails->query("SELECT loss_of_pay FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$month_year' AND (action = 'Processed' OR action = 'Approved' OR action = 'Audited') ");
            //            $lop_days = isset($arr_lop_days[0]['payroll_master']['loss_of_pay']) ? $arr_lop_days[0]['payroll_master']['loss_of_pay'] : 0;
            //            list($year, $month) = explode('-', $month_year);
            //            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            //            $salary = ((round($annual_ctc) / 12) / ($daysInMonth - $lop_days)) * $leave_balance; //365
            //edited by megha for correcting leave encashmnet amount on 10-04-2024
            $structure = $this->EmployeeDetails->query("select remarks from emp_salary_structure 
              where emp_fkey=$emp_pkey and end_date_effective is null and lcase(salary_head_item_desc) ='leave encashment'");
            $structure_val = isset($structure[0]['emp_salary_structure']['remarks']) ? $structure[0]['emp_salary_structure']['remarks'] : 0;

            eval('$salary = ' . $structure_val . ';');

            if ($str_company_code = 'KWMT') {
                //Edited by Akshay on 23-9-2024
                $approved_date = isset($details['0']['Termination']['last_approved_working_date']) ? $details['0']['Termination']['last_approved_working_date'] : '';
                // $att_startdate = $att_startdate[0][0]['monthly_att_fromdate'];
                try {
                    $arr_start_end = $this->EmployeeDetails->query("SELECT `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 1) as start_date, `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 2) as end_date");
                } catch (Exception $e) {
                    debug($e);
                }

                $start_date = isset($arr_start_end[0][0]['start_date']) ? $arr_start_end[0][0]['start_date'] : '';
                $end_date = isset($arr_start_end[0][0]['end_date']) ? $arr_start_end[0][0]['end_date'] : '';
                $approved_timestamp = strtotime($approved_date);
                $start_timestamp = strtotime($start_date);
                $end_timestamp = strtotime($end_date);
                // Adjust the month based on the conditions
                if ($approved_timestamp < $start_timestamp) {
                    // Subtract one month
                    $aprvd_month = date('Y-m', strtotime('-1 month', $approved_timestamp));
                } elseif ($approved_timestamp > $end_timestamp) {
                    // Add one month
                    $aprvd_month = date('Y-m', strtotime('+1 month', $approved_timestamp));
                } else {
                    // Keep the approved date's year-month
                    $aprvd_month = date('Y-m', $approved_timestamp);
                }
                //End

                list($year, $month) = explode('-', $aprvd_month);
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $arr_gross_salary = $this->EmployeeDetails->query("SELECT SUM(structure_det_value) AS total_value FROM `emp_salary_structure` 
                                                                        WHERE `emp_fkey` = '$emp_pkey' AND `end_date_effective` IS NULL 
                                                                        AND `head_operator` = 'Addition' 
                                                                        AND `item_part` = 'DIRECT'
                                                                        AND salary_head_item_fkey IN(1,144,154)");
                //SELECT salary_head_item_pkey FROM salary_head_items WHERE head_fkey IN (1, 4)
                $gross_salary = isset($arr_gross_salary[0][0]['total_value']) ? $arr_gross_salary[0][0]['total_value'] : 0;

                $salary = ($gross_salary / 30) * $leave_balance; //Edited by Akshay on 24-9-2024

                $salary = round($salary);
                $salary = strval($salary);
            } else {
                $salary = $salary * $leave_balance;
                $salary = round($salary);
            }

            $this->EmployeeDetails->query("UPDATE emp_settle_slip SET salary_amount = '$salary' WHERE emp_fkey = '$emp_pkey' AND type = 'ENCASHMENT' AND status = 'Y' AND approved = 'N'");
        }

        $arr_leave = array();

        $this->set('emp_pkey', $emp_pkey);
        $this->set('arr_emp_settle', $arr_emp_settle = $this->get_emp_settle_slip($emp_pkey));
        // debug($arr_emp_settle['payd_additional']);
        // debug($arr_emp_settle['payd_deductions']);
        if ($str_company_code == 'KWMT') {
            $groupedArrayAddition = array();
            foreach ($arr_emp_settle['payd_additional'] as $item) {
                $monthYear = $item['emp_settle_slip']['month_year'];
                if (!array_key_exists($monthYear, $groupedArrayAddition)) {
                    $groupedArrayAddition[$monthYear]['payd_additional'] = array();
                }
                $groupedArrayAddition[$monthYear]['payd_additional'][] = $item;
            }
            // $this->set('groupedArrayAddition', $groupedArrayAddition)
            // $arr_emp_settle['payd_additional'] = $groupedArrayAddition;
            $groupedArrayDeduction = array();
            foreach ($arr_emp_settle['payd_deductions'] as $item) {
                $monthYear = $item['emp_settle_slip']['month_year'];
                if (!array_key_exists($monthYear, $groupedArrayDeduction)) {
                    $groupedArrayDeduction[$monthYear]['payd_deductions'] = array();
                }
                $groupedArrayDeduction[$monthYear]['payd_deductions'][] = $item;
            }

            $joinedArray = array();

            if (count($groupedArrayAddition) > count($groupedArrayDeduction)) {
                $firstArray = $groupedArrayAddition;
                $secondArray = $groupedArrayDeduction;
            } else {
                $secondArray = $groupedArrayAddition;
                $firstArray = $groupedArrayDeduction;
            }

            foreach ($firstArray as $monthYear => $data) {
                // If the month_year key exists in the second array, merge the data
                if (array_key_exists($monthYear, $secondArray)) {
                    $joinedArray[$monthYear] = array_merge($data, $secondArray[$monthYear]);
                } else {
                    // If the month_year key does not exist in the second array, add data from the first array
                    $joinedArray[$monthYear] = $data;
                }
            }

            foreach ($secondArray as $monthYear => $data) {
                if (!array_key_exists($monthYear, $joinedArray)) {
                    $joinedArray[$monthYear] = $data;
                }
            }
            ksort($joinedArray);
            $this->set('joinedArray', $joinedArray);
        }

        //Leave Encashment Not Found
        $leave_encashment_reason = '';


        //Edited by Akshay on 21-12-2023
        if ($str_company_code == 'KWMT' ) {
            $arr_id = $this->EmployeeDetails->query("SELECT salary_amount FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'ID' ");
            $id_card = isset($arr_id[0]['emp_settle_slip']['salary_amount']) ? $arr_id[0]['emp_settle_slip']['salary_amount'] : 0;
            $this->set('id_card', $id_card);

            //Edited by Akshay on 10-10-2024
            $notice = isset($details['0']['Termination']['notice_period']) ? $details['0']['Termination']['notice_period'] : 0;
            $notice_period = isset($details['0']['Termination']['notice_period']) ? floatval($details['0']['Termination']['notice_period']) : 0;
            $working_days_settled = isset($details['0']['Termination']['working_days_settled']) ? $details['0']['Termination']['working_days_settled'] : 0;
            $leave_balance = isset($details['0']['Termination']['leave_balance']) ? $details['0']['Termination']['leave_balance'] : 0;
            $last_working_date = isset($details['0']['Termination']['last_working_date']) ? $details['0']['Termination']['last_working_date'] : '';
            $from = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
            $exec_holiday_group_id = $this->Termination->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
            $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
            $from_reduced = date('Y-m-d', strtotime($from . ' -1 day'));
            $last_working_date_increase = date('Y-m-d', strtotime($last_working_date . ' +1 day'));
            $exec_days_holidays_actual = $this->Termination->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
            and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$last_working_date'; ");
            $diff_with_weekoff_days_actual = $this->Termination->query("select weekoff_days_count_fn('$emp_pkey','$last_working_date_increase','$from_reduced') no_of_weekof");
            $offs_actual = isset($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) + $exec_days_holidays_actual['0']['0']['days_count'] : 0;
            $notice_period_served = $working_days_settled;
            if ($notice_period_served > $notice) {
                $notice_period_served = $notice;
            }
            if ($notice > 0) {
                $after_adjustment = $notice_period - $offs_actual - $working_days_settled - $leave_balance;
                // DEBUG($notice_period);DEBUG($offs_actual);DEBUG($working_days_settled );DEBUG($leave_balance); DEBUG($after_adjustment);
            } else {
                $after_adjustment = 0;
            }
            if ($after_adjustment < 0) {
                $after_adjustment = 0;
            }

            // $per_day_salary = (($gross_salary / $daysInMonth));
            $per_day_salary = (($gross_salary / 30)); //Edited by Akshay on 14-10-2024

            $notice_pay = round(0 - ($after_adjustment * $per_day_salary));
            $this->set('notice_pay', $notice_pay);
            //End
        }

        $this->set("str_company_code", $str_company_code); // Edited by Akshay on 5-3-2024
         if ($str_company_code == 'KWMT') {
             $this->render('approves_metro');
         }else{
        $this->render('approves');
         }
    }

    public function get_emp_settle_slip($emp_pkey = 0)
    {
        $str_company_code = $this->Session->read('company_code'); //Edited by Akshay on 25-3-2024
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $datas = array();

        if ($str_company_code == 'KWMT') {
            // additional amounts
            $datas['Extra_additions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type != 'SALARY' and salary_amount >=0 and type != 'ID'"); //Edited by Akshay on 13-3-2024
            // deduction amounts
            $datas['Extra_deductions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type != 'SALARY' and (salary_amount <0 or type = 'ID') ");
        } else {
            // additional amounts
            $datas['Extra_additions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type != 'SALARY' and salary_amount >=0 ");
            // deduction amounts
            $datas['Extra_deductions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type != 'SALARY' and salary_amount <0 ");
        }

        //Edited by Akshay on 25-3-2024
        if ($str_company_code == 'KWMT') {
            // additional amounts salary
            $datas['payd_additional'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' and salary_amount >=0 ");
            // deductssalary
            $datas['payd_deductions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' and salary_amount <0 ");
        } else {
            // additional amounts salary
            $datas['payd_additional'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' and salary_amount >=0 ");
            // deductssalary
            $datas['payd_deductions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' and salary_amount <0 ");
        }

        return $datas;
    }

    public function get_common_codes() {}

    public function get_leave_encashment()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $leave_encash_amts_fetch = $this->EmployeeDetails->query("SELECT * FROM `emp_encash_slip` WHERE `emp_fkey` = '$emp_pkey' ");

        $this->set("leave_encash_amts_fetch", $leave_encash_amts_fetch);
    }

    public function get_leave_encashment_amt()
    {
        $encash_sums = 0;
        $days = 0;
        foreach ($leave_encash_amts_fetch as $encashs) {
            $encash_sums += $encashs['emp_encash_slip']['salary_amount'];
        }
    }

    public function get_complete($emp_pkey = 0)
    {
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $str_company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 25-3-2024
        $this->set('str_company_code', $str_company_code);
        //debug($arr_comp_contact_info);
        $this->set("arr_comp_contact_info", $arr_comp_contact_info);
        //    debug($arr_comp_contact_info);
        $from = date('Y-m');
        $arr_emps = $this->request->data;
        $dayss = $arr_emps['dayss'];
        $leaves = $arr_emps['leaves'];

        //Edited by Akshay on 14-3-2024
        $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeDetails.classification,EmployeeDetails.maritual_status,EmployeeInfo.*,SalaryHeadItems.occurance, EmpSettleSlip.salary_head_item_desc, EmpSettleSlip.status, EmpSettleSlip.type ,EmpSettleSlip.leave_total, EmpSettleSlip.salary_amount, EmpSettleSlip.emp_settle_slip_pkey, EmpSettleSlip.status"), "conditions" => array("Termination.emp_fkey" => $emp_pkey, "Termination.status" => '1'), "joins" => array(
            array(
                "table" => "employee_info",
                "alias" => "EmployeeInfo",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
            ),
            array(
                "table" => "emp_details",
                "alias" => "EmployeeDetails",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
            ),
            array(
                "table" => "leave_encashment_master",
                "alias" => "LeaveEncashmentMaster",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("LeaveEncashmentMaster.emp_fkey = Termination.emp_fkey", "LeaveEncashmentMaster.salary_head_item_fkey" > 0, "LeaveEncashmentMaster.remarks= 'terminate'")
            ),
            array(
                "table" => "salary_head_items",
                "alias" => "SalaryHeadItems",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey ")
            ),
            array(
                "table" => "emp_settle_slip",
                "alias" => "EmpSettleSlip",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("EmpSettleSlip.emp_fkey = Termination.emp_fkey AND (EmpSettleSlip.status = 1 OR EmpSettleSlip.status = 'Y') AND ( EmpSettleSlip.type = 'BALANCE' )")
            )
        )));
        // debug($details);
        $branch = isset($details['0']['EmployeeDetails']['branch_code']) ? $details['0']['EmployeeDetails']['branch_code'] : '';
        $emp_id = 'ADMIN';
        //        $encashment_master = $this->leaveencash($emp_pkey, $leaves, 0);
        //added by megha on 26_07_19 last approved month
        $last_date_effective = $this->Termination->query("select last_approved_working_date from termination where `emp_fkey` = '$emp_pkey'");

        $last_date = isset($last_date_effective['0']['termination']['last_approved_working_date']) ? date('Y-m', strtotime($last_date_effective['0']['termination']['last_approved_working_date'])) : '';
        //debug($last_date);

        $yearmonth = date('Y-m');
        // $sdds = $this->Termination->query("CALL `final_settle_pay_prc`('$branch', '$last_date', '$emp_pkey', '$dayss', '$leaves', '$emp_id', @`perror_message`)");

        // Start of new line 

        $submitted_dat = $from = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
        $newdate = $todate = isset($details['0']['Termination']['last_approved_working_date']) ? $details['0']['Termination']['last_approved_working_date'] : '';
        //Edited by Akshay on 31-5-2024
        $last_working_date = isset($details['0']['Termination']['last_working_date']) ? $details['0']['Termination']['last_working_date'] : '';
        //End
        $date1 = date_create($newdate);
        $date2 = date_create($submitted_dat);
        $date1->modify('+1 day'); //Edited by Akshay on 13-3-2024
        $diff = date_diff($date1, $date2);

        // end of new line 
        $arr_leaves_heads = $this->EmployeeDetails->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 ) and occurance != 'LOP' LIMIT 50");
        $arr_leave = array();
        //            DEBUG($arr_leaves_heads);
        $this->set('arr_leaves', $arr_leaves_heads);
        $month = date("m");
        $year = date("Y");
        $excess_leave = 0;
        foreach ($arr_leaves_heads as $key => $val) {
            $head = $val['salary_head_items']['occurance'];
            $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
            $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$month','$year') as LeaveBalance");
            $resp = isset($lbalance['0']['0']['LeaveBalance']) ? round($lbalance['0']['0']['LeaveBalance'], 1) : 0;
            if ($resp < 0) {
                $excess_leave += $resp;
            }
        }
        $this->set("diff", $diff->format("%a"));
        $this->set("excess_leave", $excess_leave);

        //Leave Encashments 

        $excess_leav_amt = isset($gross_sal['0']['ectc']['gros_sal']) ? $gross_sal['0']['0']['gros_sal'] / 26 * abs($excess_leave) : 0;
        $this->set("excess_leav_amt", $excess_leav_amt);

        if ($str_company_code == 'KWMT' ) {
            $arr_emp_settle = $this->get_emp_settle_slip_watermt($emp_pkey);
        } else {
            $arr_emp_settle = $this->get_emp_settle_slip($emp_pkey);
        }

        $this->set('arr_emp_settle', $arr_emp_settle);

        //debug($arr_salary_for_template);
        $this->set("details", $details);

        $arr_att_days_after_resignation = "select sum(a) a from(
            select count(*) as a from emp_detail_timeattandance where others in ('LOP','LOP/LOP') and 	emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            union all
            select count(*)/2 as a from emp_detail_timeattandance where (instr(others,'/LOP') > 0 or instr(others,'LOP/') > 0 ) and others!= 'LOP'   and	 emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            )ass ";
        $exec_days = $this->Termination->query($arr_att_days_after_resignation);
        $exec_holiday_group_id = $this->Termination->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
        $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        $exec_days_holidays = $this->Termination->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
  and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$todate'; ");
        $days_after_resignation_att = isset($exec_days['0']['0']['a']) ? (round($exec_days['0']['0']['a'], 1)) : 0;
        //Edited by Akshay on 4-9-2024
        $from_reduced = date('Y-m-d', strtotime($from . ' -1 day'));
        $newdate_incrase = date('Y-m-d', strtotime($newdate . ' +1 day'));
        $diff_with_weekoff_days = $this->Termination->query("select weekoff_days_count_fn('$emp_pkey','$newdate_incrase','$from_reduced ') no_of_weekof");
        //End
        $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days['0']['0']['no_of_weekof']) + $exec_days_holidays['0']['0']['days_count'] : 0;

        $this->set("offs", $offs);
        $this->set("days_after_resignation_att", $days_after_resignation_att);

        //Edited by Akshay on 13-3-2024
        $exec_days_holidays_actual = $this->Termination->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
                            and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$last_working_date'; ");
        //Edited by Akshay on 4-9-2024
        $from_reduced = date('Y-m-d', strtotime($from . ' -1 day'));
        $last_working_date_increase = date('Y-m-d', strtotime($last_working_date . ' +1 day'));
        $diff_with_weekoff_days_actual = $this->Termination->query("select weekoff_days_count_fn('$emp_pkey','$last_working_date_increase','$from_reduced') no_of_weekof");
        $offs_actual = isset($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) + $exec_days_holidays_actual['0']['0']['days_count'] : 0;
        $this->set("offs_actual", $offs_actual);

        $res_sub_date = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
        $date = new DateTime($res_sub_date);
        $res_month = $date->format('Y-m');

        $arr_annual_ctc = $this->EmployeeDetails->query("SELECT emp_anual_ctc FROM emp_ctc_transaction ect WHERE ect.end_date_effective IS NULL AND ect.emp_fkey = '$emp_pkey'");
        $annual_ctc = isset($arr_annual_ctc[0]['ect']['emp_anual_ctc']) ? $arr_annual_ctc[0]['ect']['emp_anual_ctc'] : 0;

        list($year, $month) = explode('-', $res_month);
        // $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        //Edited by Akshay on 3-6-2024 
        $notice_period = isset($details['0']['Termination']['notice_period']) ? floatval($details['0']['Termination']['notice_period']) : 0;
        $this->set('notice_period', $notice_period);
        if ($str_company_code == 'KWMT') {
            //Edited by Akshay on 24-5-2024
            //$per_day_salary = ((round($annual_ctc) / 12) / $notice_period); //365
            //Edited by Akshay on 23-9-2024
            $approved_date = isset($details['0']['Termination']['last_approved_working_date']) ? $details['0']['Termination']['last_approved_working_date'] : '';
            // $att_startdate = $att_startdate[0][0]['monthly_att_fromdate'];
            try {
                $arr_start_end = $this->EmployeeDetails->query("SELECT `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 1) as start_date, `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 2) as end_date");
            } catch (Exception $e) {
                debug($e);
            }

            $start_date = isset($arr_start_end[0][0]['start_date']) ? $arr_start_end[0][0]['start_date'] : '';
            $end_date = isset($arr_start_end[0][0]['end_date']) ? $arr_start_end[0][0]['end_date'] : '';
            $approved_timestamp = strtotime($approved_date);
            $start_timestamp = strtotime($start_date);
            $end_timestamp = strtotime($end_date);
            // Adjust the month based on the conditions
            if ($approved_timestamp < $start_timestamp) {
                // Subtract one month
                $aprvd_month = date('Y-m', strtotime('-1 month', $approved_timestamp));
            } elseif ($approved_timestamp > $end_timestamp) {
                // Add one month
                $aprvd_month = date('Y-m', strtotime('+1 month', $approved_timestamp));
            } else {
                // Keep the approved date's year-month
                $aprvd_month = date('Y-m', $approved_timestamp);
            }
            //End
            list($year, $month) = explode('-', $aprvd_month);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $arr_gross_salary = $this->EmployeeDetails->query("SELECT SUM(structure_det_value) AS total_value FROM `emp_salary_structure` 
                                                                    WHERE `emp_fkey` = '$emp_pkey' AND `end_date_effective` IS NULL 
                                                                    AND `head_operator` = 'Addition' 
                                                                    AND `item_part` = 'DIRECT'
                                                                    AND salary_head_item_fkey IN(SELECT 
                                                                    salary_head_item_pkey FROM salary_head_items WHERE head_fkey IN ('1', '4'));");
            $gross_salary = isset($arr_gross_salary[0][0]['total_value']) ? $arr_gross_salary[0][0]['total_value'] : 0;
            $per_day_salary = (($gross_salary / $daysInMonth));
            //End
            //Edited by Akshay on 4-9-2024
            $from_reduced = date('Y-m-d', strtotime($from . ' -1 day'));
            $last_working_date_increase = date('Y-m-d', strtotime($last_working_date . ' +1 day'));
            //Edited by Akshay on 28-8-2024
            $diff_with_weekoff_days_actual = $this->EmployeeDetails->query("select weekoff_days_count_fn('$emp_pkey','$last_working_date_increase','$from_reduced') no_of_weekof");

            $exec_days_holidays_actual = $this->EmployeeDetails->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
                and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$last_working_date' and status = '1'; ");
            $offs_actual = isset($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) + $exec_days_holidays_actual['0']['0']['days_count'] : 0;
            //End
            //Edited by Akshay on 12-7-2024
            $leave_balance =  isset($details['0']['Termination']['leave_balance']) ? floatval($details['0']['Termination']['leave_balance']) : 0;
            $this->set('leave_balance', $leave_balance);
            //End
            $this->set('per_day_salary', $per_day_salary);
            $this->render('metro_salaryslip');
        } else {
            //added by megha on 23-08-2025
            if($notice_period == 0){ 
                $notice_period=30;
            }
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $per_day_salary = ((round($annual_ctc) / 12) / $notice_period); //365
            $this->set('per_day_salary', $per_day_salary);
            $this->render('salaryslip');
        }
        //End
    }

    public function removeemps()
    {

        $this->autoRender = false;
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
        $emp_pkey = $arr_request_data['emp_pkey'];
        //        $g = $arr_request_data['emp'];
        $removes = $this->EmployeeDetails->updateAll(
            array(
                'status' => 2 // Terminated , Status should be 1 untill we proccess this - to avail that Employee in payroll // 
            ),
            array(
                'EmployeeDetails.emp_pkey' => $emp_pkey
            )
        );
        //edited by megha on 17/12/2019 Heirarchy employees auto reversal
        $removeheirarchy = $this->EmployeeProfessionalDetails->updateAll(
            array(
                'attr1' => Null
            ),
            array(
                'EmployeeProfessionalDetails.attr1' => $emp_pkey
            )
        );
        $removepolicy = $this->EmployeeConfig->updateAll(
            array(
                'status' => 0
            ),
            array(
                'EmployeeConfig.policy_id' => $emp_pkey,
                'type' => 'HIERARCHY'
            )
        );
        //end Heirarchy employees auto reversal
        $arr_emp_salaries = $this->EmployeeDetails->query(" SELECT * FROM `payroll_master` WHERE `emp_fkey` = '$emp_pkey' and  `approved` = 'N' and month_year > '2018-04'  ");
        $this->set("arr_emp_salaries", $arr_emp_salaries);

        $unverified_attendances_proccess = $this->EmployeeDetails->query("SELECT count(*) as COUNTS FROM emp_settle_slip join emp_details on (emp_settle_slip.emp_fkey = emp_details.emp_pkey) WHERE `emp_fkey` = '$emp_pkey' and emp_details.status = '2'  ");

        //        debug($working_days
        //        );
        //added by megha on 20/11/2019 updating settlement status
        $this->EmployeeDetails->query("UPDATE emp_settle_slip SET approved = 'Y' WHERE emp_fkey = '$emp_pkey' and status = 'Y' ");
        $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeInfo.*"), "conditions" => array("emp_fkey" => $emp_pkey), "joins" => array(
            array(
                "table" => "employee_info",
                "alias" => "EmployeeInfo",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
            ),
            array(
                "table" => "emp_details",
                "alias" => "EmployeeDetails",
                "foreignKey" => false,
                "type" => "LEFT",
                "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
            )
        )));

        $submitted_dat = $from = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
        $newdate = $todate = isset($details['0']['Termination']['last_approved_working_date']) ? $details['0']['Termination']['last_approved_working_date'] : '';

        $date1 = date_create($newdate);
        $date2 = date_create($submitted_dat);
        $date1->modify('+1 day'); //Edited by Akshay on 13-3-2024
        $diff = date_diff($date1, $date2);

        $arr_att_days_after_resignation = "select sum(a) a from(
            select count(*) as a from emp_detail_timeattandance where others in ('LOP','LOP/LOP') and 	emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            union all
            select count(*)/2 as a from emp_detail_timeattandance where (instr(others,'/LOP') > 0 or instr(others,'LOP/') > 0 ) and others!= 'LOP'   and	 emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            )ass ";
        $exec_days = $this->Termination->query($arr_att_days_after_resignation);
        $exec_holiday_group_id = $this->Termination->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
        $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        $exec_days_holidays = $this->Termination->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
  and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$todate'; ");
        $days_after_resignation_att = isset($exec_days['0']['0']['a']) ? round($exec_days['0']['0']['a'], 1) : 0;
        //Edited by Akshay on 4-9-2024
        $from_reduced = date('Y-m-d', strtotime($from . ' -1 day'));
        $todate_increased = date('Y-m-d', strtotime($todate . ' +1 day'));
        //End
        $diff_with_weekoff_days = $this->Termination->query("select weekoff_days_count_fn('$emp_pkey','$todate_increased','$from_reduced') no_of_weekof");
        $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days['0']['0']['no_of_weekof']) + $exec_days_holidays['0']['0']['days_count'] : 0;
        $this->set("offs", $offs);
        $this->set("days_after_resignation_att", $days_after_resignation_att);
        if ($offs > 0) {
            $offs = $offs + 1;
        }

        $arr_save_data = array();
        $arr_save_data['terminate_pkey'] = $details['0']['Termination']['terminate_pkey'];


        //Edited by Akshay on 22-8-2024
        $str_company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 25-3-2024
        if ($str_company_code == 'KWMT') {
            $arr_save_data['working_days_settled'] = isset($arr_request_data['dayss']) ? $arr_request_data['dayss']  : 0;
            $arr_save_data['payroll_days'] = isset($arr_request_data['total_working']) ? $arr_request_data['total_working']  : 0;
        } else {
            $arr_save_data['working_days_settled'] = isset($diff) ? $diff->format("%a") - $offs  : 0;
            $arr_save_data['payroll_days'] = isset($diff) ? $diff->format("%a") - ($days_after_resignation_att + $offs)  : 0;
        }

        //End

        // debug($arr_save_data); exit;
        $arr_update_termination = $this->Termination->save($arr_save_data);


        if ($removes) {

            $arr_emp_salaries = $this->EmployeeDetails->query(" UPDATE `payroll_master` SET `approved` = 'Y' WHERE `emp_fkey` = '$emp_pkey' and  `approved` = 'N' and month_year > '2018-04'  ");
            $arr_emp_salaries = $this->EmployeeDetails->query(" UPDATE `leave_encashment_master` SET `salary_paid` = 'Y' WHERE `emp_fkey` = '$emp_pkey' ");
            return 'Employee Termination Successfull';
        } else {

            return 'Employee Termination failed please try again ';
        }
    }

    public function Viewslip($emp_pkey = 0)
    {
        $str_company_code = $this->Session->read('company_code'); // Edited by Akshay on 5-3-2024
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        //debug($arr_comp_contact_info);
        try {
            $this->set("arr_comp_contact_info", $arr_comp_contact_info);
            //Edited by Akshay on 16-8-2024
            $structure = $this->EmployeeDetails->query("select salary_head_item_desc,structure_det_value from emp_salary_structure where emp_fkey = '$emp_pkey' "
                . "and end_date_effective is null and head_operator='Addition' and item_part ='Direct' and remarks is null");
            //End
            $this->set("structure", $structure);

            $from = date('Y-m');
            $arr_emps = $this->request->data;
            if ($str_company_code == 'KWMT') {
                $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.first_name, EmployeeDetails.last_name, EmployeeDetails.branch_code,EmployeeDetails.classification,EmployeeDetails.maritual_status,EmployeeInfo.*,SalaryHeadItems.occurance, EmpSettleSlip.salary_head_item_desc, EmpSettleSlip.status, EmpSettleSlip.type ,EmpSettleSlip.leave_total,EmpSettleSlip.salary_amount"), "conditions" => array("Termination.emp_fkey" => $emp_pkey, "Termination.status" => '1'), "joins" => array( //Edited by Akshay on14-3-2024
                    array(
                        "table" => "employee_info",
                        "alias" => "EmployeeInfo",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "emp_details",
                        "alias" => "EmployeeDetails",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "leave_encashment_master",
                        "alias" => "LeaveEncashmentMaster",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("LeaveEncashmentMaster.emp_fkey = Termination.emp_fkey", "LeaveEncashmentMaster.salary_head_item_fkey" > 0, "LeaveEncashmentMaster.remarks= 'terminate'")
                    ),
                    array(
                        "table" => "salary_head_items",
                        "alias" => "SalaryHeadItems",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey ")
                    ),
                    array(
                        "table" => "emp_settle_slip",
                        "alias" => "EmpSettleSlip",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmpSettleSlip.emp_fkey = Termination.emp_fkey AND (EmpSettleSlip.status = 1 OR EmpSettleSlip.status = 'Y') AND EmpSettleSlip.type IN ('ID', 'ENCASHMENT', 'BALANCE')")
                    )
                )));

                //Edited by Akshay on 27-8-2024
                //Edited by Akshay on 21-8-2024
                $arr_pro_data = $this->Termination->query("select distinct(prorate_code)  "
                    . "from emp_salary_structure as ectc "
                    . " where ectc.emp_fkey = '$emp_pkey' "
                    . "and end_date_effective is null ");
                $prorate_code = isset($arr_pro_data[0]['ectc']['prorate_code']) ? $arr_pro_data[0]['ectc']['prorate_code'] : '1';
                if ($prorate_code == '1') {
                    $condition = "AND weekoff IS NULL AND holiday IS NULL";
                } else {
                    $condition = "";
                }
                $submitted_date = $details['0']['Termination']['submitted_date'];
                $last_working_date = isset($details['0']['Termination']['last_working_date']) ? $details['0']['Termination']['last_working_date'] : '';
                $resignaion_period_working_days = $this->Termination->query("SELECT COUNT(DISTINCT `att_date`) AS count
                                                                    FROM `emp_detail_timeattandance`
                                                                    WHERE `emp_pkey` = '$emp_pkey' 
                                                                    AND `att_date` >= '$submitted_date' 
                                                                    AND `att_date` <= '$last_working_date'
                                                                    $condition ");

                $resignaion_period_working_days = isset($resignaion_period_working_days[0][0]['count']) ? $resignaion_period_working_days[0][0]['count'] : 0;
                $this->set("resignaion_period_working_days", $resignaion_period_working_days);
                //End

            } else {
                $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeDetails.classification,EmployeeDetails.maritual_status,EmployeeInfo.*,SalaryHeadItems.occurance"), "conditions" => array("Termination.emp_fkey" => $emp_pkey), "joins" => array(
                    array(
                        "table" => "employee_info",
                        "alias" => "EmployeeInfo",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "emp_details",
                        "alias" => "EmployeeDetails",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "leave_encashment_master",
                        "alias" => "LeaveEncashmentMaster",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("LeaveEncashmentMaster.emp_fkey = Termination.emp_fkey", "LeaveEncashmentMaster.salary_head_item_fkey" > 0)
                    ),
                    array(
                        "table" => "salary_head_items",
                        "alias" => "SalaryHeadItems",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey ")
                    )
                )));
            }
            // debug($details);
            $this->set("details", $details);

            //Edited by Akshay on 23-12-2023
            $res_sub_date = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
            $date = new DateTime($res_sub_date);
            $res_month = $date->format('Y-m');

            $arr_annual_ctc = $this->EmployeeDetails->query("SELECT emp_anual_ctc FROM emp_ctc_transaction ect WHERE ect.end_date_effective IS NULL AND ect.emp_fkey = '$emp_pkey'");
            $annual_ctc = isset($arr_annual_ctc[0]['ect']['emp_anual_ctc']) ? $arr_annual_ctc[0]['ect']['emp_anual_ctc'] : 0;

            list($year, $month) = explode('-', $res_month);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            // $per_day_salary = ((round($annual_ctc) / 12) / $daysInMonth); //365
            //Edited by Akshay on 24-5-2024
            if ($str_company_code == 'KWMT') {
                //Edited by Akshay on 23-9-2024
                $approved_date = isset($details['0']['Termination']['last_approved_working_date']) ? $details['0']['Termination']['last_approved_working_date'] : '';
                // $att_startdate = $att_startdate[0][0]['monthly_att_fromdate'];
                try {
                    $arr_start_end = $this->EmployeeDetails->query("SELECT `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 1) as start_date, `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 2) as end_date");
                } catch (Exception $e) {
                    debug($e);
                }

                $start_date = isset($arr_start_end[0][0]['start_date']) ? $arr_start_end[0][0]['start_date'] : '';
                $end_date = isset($arr_start_end[0][0]['end_date']) ? $arr_start_end[0][0]['end_date'] : '';
                $approved_timestamp = strtotime($approved_date);
                $start_timestamp = strtotime($start_date);
                $end_timestamp = strtotime($end_date);
                // Adjust the month based on the conditions
                if ($approved_timestamp < $start_timestamp) {
                    // Subtract one month
                    $aprvd_month = date('Y-m', strtotime('-1 month', $approved_timestamp));
                } elseif ($approved_timestamp > $end_timestamp) {
                    // Add one month
                    $aprvd_month = date('Y-m', strtotime('+1 month', $approved_timestamp));
                } else {
                    // Keep the approved date's year-month
                    $aprvd_month = date('Y-m', $approved_timestamp);
                }
                //End
                list($year, $month) = explode('-', $aprvd_month);
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                $arr_gross_salary = $this->EmployeeDetails->query("SELECT SUM(structure_det_value) AS total_value FROM `emp_salary_structure` 
                                                                        WHERE `emp_fkey` = '$emp_pkey' AND `end_date_effective` IS NULL 
                                                                        AND `head_operator` = 'Addition' 
                                                                        AND `item_part` = 'DIRECT'
                                                                        AND salary_head_item_fkey IN(SELECT 
                                                                        salary_head_item_pkey FROM salary_head_items WHERE head_fkey IN ('1', '4'));");
                $gross_salary = isset($arr_gross_salary[0][0]['total_value']) ? $arr_gross_salary[0][0]['total_value'] : 0;
                // debug($gross_salary);
                $notice_period = isset($details['0']['Termination']['notice_period']) ? floatval($details['0']['Termination']['notice_period']) : 0;
                // $per_day_salary = (($gross_salary / $daysInMonth)); //365
                $per_day_salary = (($gross_salary / 30)); //Edited by Akshay on 14-10-2024

            } else {
                $per_day_salary = ((round($annual_ctc) / 12) / $daysInMonth); //365
            }

            // $per_day_salary = ((round($annual_ctc) / 12) / $notice_period); //365
            // debug($per_day_salary );
            //End

            $this->set('per_day_salary', $per_day_salary);

            $arr_net_salary = $this->EmployeeDetails->query("SELECT net_salary, loss_of_pay FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$res_month' AND (action = 'Processed' OR action = 'Approved' OR action = 'Audited') ");
            $res_net_salary = isset($arr_net_salary[0]['payroll_master']['net_salary']) ? $arr_net_salary[0]['payroll_master']['net_salary'] : 0;
            $res_lop = isset($arr_net_salary[0]['payroll_master']['loss_of_pay']) ? $arr_net_salary[0]['payroll_master']['loss_of_pay'] : 0;

            $this->set('res_net_salary', $res_net_salary);

            $emp_id = 'ADMIN';

            if ($str_company_code == 'KWMT') {
                //edited by sinsiya for water metro start
                $amt_paid_emp = $this->EmployeeDetails->query("SELECT amt_paid_by_empaddition,amt_paid_by_empdeduction FROM termination WHERE emp_fkey = '$emp_pkey' AND status = 1"); //Edited by akshay on 11-7-2024
                $this->set('amt_paid_emp', $amt_paid_emp);
                //edited by sinsiya for water metro end
            }

            $this->set('arr_emp_settle', $arr_emp_settle = $this->get_emp_full_and_final_settle_slip($emp_pkey));
            // debug($arr_emp_settle);
            $str_company_code = $this->Session->read('company_code'); //Edited by Akshay on 23-11-2023
            if ($str_company_code == 'KWMT') {
                //Edited by Akshay on 20-6-2024
                $submitted_date = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
                $last_working_date = isset($details['0']['Termination']['last_working_date']) ? $details['0']['Termination']['last_working_date'] : '';
                //Edited by Akshay on 4-9-2024
                $submitted_date_reduced = date('Y-m-d', strtotime($submitted_date . ' -1 day'));
                $last_working_date_increased = date('Y-m-d', strtotime($last_working_date . ' +1 day'));
                //End
                $diff_with_weekoff_days_actual = $this->EmployeeDetails->query("select weekoff_days_count_fn('$emp_pkey','$last_working_date_increased','$submitted_date_reduced') no_of_weekof");
                $exec_holiday_group_id = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
                $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
                $exec_days_holidays_actual = $this->EmployeeDetails->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
                and HOLIDAYDATE  between DATE_FORMAT('$submitted_date' , '%Y-%m-%d') and '$last_working_date' and status = '1'; ");
                $offs_actual = isset($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) + $exec_days_holidays_actual['0']['0']['days_count'] : 0;
                $this->set('offs_actual', $offs_actual);
                //End
                $this->render('metro_slip');
            } else {
                $this->render('slip');
            }
        } catch (Exception $e) {
            debug($e);
        }
    }

    public function downloads($emp_pkey = 0, $dayss = 0, $leaves = 0, $gratuity = 0)
    {
        $this->autoRender = false;
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $str_company_code = $this->Session->read('company_code'); //Edited by Akshay on 23-11-2023
        //debug($arr_comp_contact_info);
        $this->set("arr_comp_contact_info", $arr_comp_contact_info);
        //        debug($arr_comp_contact_info);
        $from = date('Y-m');
        $arr_emps = $this->request->data;

        //Edited by Akshay on 27-11-2023
        try {
            //Edited by Akshay on 16-8-2024
            $structure = $this->EmployeeDetails->query("select salary_head_item_desc,structure_det_value from emp_salary_structure where emp_fkey = '$emp_pkey' "
                . "and end_date_effective is null and head_operator='Addition' and item_part ='Direct' and remarks is null");
            //End
            $this->set("structure", $structure);
            if ($str_company_code == 'KWMT') {
                $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeDetails.classification,EmployeeDetails.maritual_status,EmployeeInfo.*,SalaryHeadItems.occurance, EmpSettleSlip.salary_head_item_desc, EmpSettleSlip.status, EmpSettleSlip.type ,EmpSettleSlip.leave_total, EmpSettleSlip.salary_amount"), "conditions" => array("Termination.emp_fkey" => $emp_pkey, "Termination.status" => 1), "joins" => array(
                    array(
                        "table" => "employee_info",
                        "alias" => "EmployeeInfo",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "emp_details",
                        "alias" => "EmployeeDetails",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "leave_encashment_master",
                        "alias" => "LeaveEncashmentMaster",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("LeaveEncashmentMaster.emp_fkey = Termination.emp_fkey", "LeaveEncashmentMaster.salary_head_item_fkey" > 0, "LeaveEncashmentMaster.remarks= 'terminate'")
                    ),
                    array(
                        "table" => "salary_head_items",
                        "alias" => "SalaryHeadItems",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey ")
                    ),
                    array(
                        "table" => "emp_settle_slip",
                        "alias" => "EmpSettleSlip",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmpSettleSlip.emp_fkey = Termination.emp_fkey AND (EmpSettleSlip.status = 1 OR EmpSettleSlip.status = 'Y') AND (EmpSettleSlip.type = 'ID' OR EmpSettleSlip.type = 'ENCASHMENT' OR EmpSettleSlip.type = 'BALANCE' )")
                    )
                )));

                //Edited by Akshay on 21-8-2024
                $arr_pro_data = $this->Termination->query("select distinct(prorate_code)  "
                    . "from emp_salary_structure as ectc "
                    . " where ectc.emp_fkey = '$emp_pkey' "
                    . "and end_date_effective is null ");
                $prorate_code = isset($arr_pro_data[0]['ectc']['prorate_code']) ? $arr_pro_data[0]['ectc']['prorate_code'] : '1';
                if ($prorate_code == '1') {
                    $condition = "AND weekoff IS NULL AND holiday IS NULL";
                } else {
                    $condition = "";
                }
                $submitted_date = $details['0']['Termination']['submitted_date'];
                $last_working_date = isset($details['0']['Termination']['last_working_date']) ? $details['0']['Termination']['last_working_date'] : '';
                $resignaion_period_working_days = $this->Termination->query("SELECT COUNT(DISTINCT `att_date`) AS count
                                                                    FROM `emp_detail_timeattandance`
                                                                    WHERE `emp_pkey` = '$emp_pkey' 
                                                                    AND `att_date` >= '$submitted_date' 
                                                                    AND `att_date` <= '$last_working_date'
                                                                    $condition ");

                $resignaion_period_working_days = isset($resignaion_period_working_days[0][0]['count']) ? $resignaion_period_working_days[0][0]['count'] : 0;
                $this->set("resignaion_period_working_days", $resignaion_period_working_days);
                //End
            } else {
                $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeDetails.classification,EmployeeDetails.maritual_status,EmployeeInfo.*,SalaryHeadItems.occurance"), "conditions" => array("Termination.emp_fkey" => $emp_pkey), "joins" => array(
                    array(
                        "table" => "employee_info",
                        "alias" => "EmployeeInfo",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "emp_details",
                        "alias" => "EmployeeDetails",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "leave_encashment_master",
                        "alias" => "LeaveEncashmentMaster",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("LeaveEncashmentMaster.emp_fkey = Termination.emp_fkey", "LeaveEncashmentMaster.salary_head_item_fkey" > 0)
                    ),
                    array(
                        "table" => "salary_head_items",
                        "alias" => "SalaryHeadItems",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey ")
                    )
                )));
            }

            //   debug($details); exit;
            $this->set("details", $details);

            //Edited by Akshay on 23-12-2023
            $res_sub_date = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
            $date = new DateTime($res_sub_date);
            $res_month = $date->format('Y-m');

            $arr_annual_ctc = $this->EmployeeDetails->query("SELECT emp_anual_ctc FROM emp_ctc_transaction ect WHERE ect.end_date_effective IS NULL AND ect.emp_fkey = '$emp_pkey'");
            $annual_ctc = isset($arr_annual_ctc[0]['ect']['emp_anual_ctc']) ? $arr_annual_ctc[0]['ect']['emp_anual_ctc'] : 0;

            list($year, $month) = explode('-', $res_month);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            // $per_day_salary = ((round($annual_ctc) / 12) / $daysInMonth); //365
            //Edited by Akshay on 24-5-2024
            if ($str_company_code == 'KWMT') {
                //Edited by Akshay on 23-9-2024
                $approved_date = isset($details['0']['Termination']['last_approved_working_date']) ? $details['0']['Termination']['last_approved_working_date'] : '';
                // $att_startdate = $att_startdate[0][0]['monthly_att_fromdate'];
                try {
                    $arr_start_end = $this->EmployeeDetails->query("SELECT `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 1) as start_date, `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 2) as end_date");
                } catch (Exception $e) {
                    debug($e);
                }

                $start_date = isset($arr_start_end[0][0]['start_date']) ? $arr_start_end[0][0]['start_date'] : '';
                $end_date = isset($arr_start_end[0][0]['end_date']) ? $arr_start_end[0][0]['end_date'] : '';
                $approved_timestamp = strtotime($approved_date);
                $start_timestamp = strtotime($start_date);
                $end_timestamp = strtotime($end_date);
                // Adjust the month based on the conditions
                if ($approved_timestamp < $start_timestamp) {
                    // Subtract one month
                    $aprvd_month = date('Y-m', strtotime('-1 month', $approved_timestamp));
                } elseif ($approved_timestamp > $end_timestamp) {
                    // Add one month
                    $aprvd_month = date('Y-m', strtotime('+1 month', $approved_timestamp));
                } else {
                    // Keep the approved date's year-month
                    $aprvd_month = date('Y-m', $approved_timestamp);
                }
                //End
                list($year, $month) = explode('-', $aprvd_month);
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $arr_gross_salary = $this->EmployeeDetails->query("SELECT SUM(structure_det_value) AS total_value FROM `emp_salary_structure` 
                                                                        WHERE `emp_fkey` = '$emp_pkey' AND `end_date_effective` IS NULL 
                                                                        AND `head_operator` = 'Addition' 
                                                                        AND `item_part` = 'DIRECT'
                                                                        AND salary_head_item_fkey IN(SELECT 
                                                                        salary_head_item_pkey FROM salary_head_items WHERE head_fkey IN ('1', '4'));");
                $gross_salary = isset($arr_gross_salary[0][0]['total_value']) ? $arr_gross_salary[0][0]['total_value'] : 0;
                // debug($gross_salary);
                $notice_period = isset($details['0']['Termination']['notice_period']) ? floatval($details['0']['Termination']['notice_period']) : 0;
                // $per_day_salary = (($gross_salary / $daysInMonth)); //365
                $per_day_salary = (($gross_salary / 30)); //Edited by Akshay on 14-10-2024
            } else {
                $per_day_salary = ((round($annual_ctc) / 12) / $daysInMonth); //365
            }
            // $per_day_salary = ((round($annual_ctc) / 12) / $notice_period); //365
            //End

            $this->set('per_day_salary', $per_day_salary);

            $arr_net_salary = $this->EmployeeDetails->query("SELECT net_salary, loss_of_pay FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$res_month' AND (action = 'Processed' OR action = 'Approved' OR action = 'Audited') ");
            $res_net_salary = isset($arr_net_salary[0]['payroll_master']['net_salary']) ? $arr_net_salary[0]['payroll_master']['net_salary'] : 0;
            $res_lop = isset($arr_net_salary[0]['payroll_master']['loss_of_pay']) ? $arr_net_salary[0]['payroll_master']['loss_of_pay'] : 0;

            $this->set('res_net_salary', $res_net_salary);

            $emp_id = 'ADMIN';

            $this->set('arr_emp_settle', $arr_emp_settle = $this->get_emp_full_and_final_settle_slip($emp_pkey));

            if ($str_company_code == 'KWMT') { //Edited by Askhay on 15-3-2024
                //edited by sinsiya for water metro start
                $amt_paid_emp = $this->EmployeeDetails->query("SELECT amt_paid_by_empaddition,amt_paid_by_empdeduction FROM termination WHERE emp_fkey = '$emp_pkey' and status = '1'"); //Edited by Akshay on 18-9-2024
                $this->set('amt_paid_emp', $amt_paid_emp);
                //edited by sinsiya for water metro end
                //Edited by Akshay on 20-6-2024
                $submitted_date = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
                $last_working_date = isset($details['0']['Termination']['last_working_date']) ? $details['0']['Termination']['last_working_date'] : '';
                //Edited by Akshay on 4-9-2024
                $submitted_date_reduced = date('Y-m-d', strtotime($submitted_date . ' -1 day'));
                $last_working_date_increased = date('Y-m-d', strtotime($last_working_date . ' +1 day'));
                //End
                $diff_with_weekoff_days_actual = $this->EmployeeDetails->query("select weekoff_days_count_fn('$emp_pkey','$last_working_date_increased','$submitted_date_reduced') no_of_weekof");
                $exec_holiday_group_id = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
                $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
                $exec_days_holidays_actual = $this->EmployeeDetails->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
                                                and HOLIDAYDATE  between DATE_FORMAT('$submitted_date' , '%Y-%m-%d') and '$last_working_date' and status = '1'; ");
                $offs_actual = isset($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) + $exec_days_holidays_actual['0']['0']['days_count'] : 0;

                $this->set('offs_actual', $offs_actual);
                //End

            }
            //$content ="<h2>hi</h2>";
            //            $view = new View($this, false);
            //            $view_output = $view->render('download');
            //debug($view_output);exit; 
            $this->set('mode', 'pdf');
            $view = new View($this, false);
            if ($str_company_code == 'KWMT') { //Edited by Akshay on 23-11-2023
                $view_output = $view->render('metro_download');
            } else {
                $view_output = $view->render('download');
            }

            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
            $html2pdf = new HTML2PDF('P', 'A4', 'en');
            //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
            //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($view_output);
            $html2pdf->Output('FullAndFinalSettleSlip.pdf', 'D');
            // if ($str_company_code == 'KWMT' || $str_company_code == 'DEMO') { //Edited by Akshay on 23-11-2023
            //     // $this->render('metro_download');
            //     $this->render('metro_download');
            // } else {
            //     $this->render('download');
            // }
        } catch (Exception $e) {
            debug($e);
        }
    }

    public function leaveencash($emp_pkey = 0, $applied = 0, $salary_head_item = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
        //$arr_data = $this->request->data;
        $emp_pkey = $emp_pkey;
        $arr_emps = $this->EmployeeDetails->find("all", array("conditions" => array("status" => 1, "emp_pkey" => $emp_pkey)));
        $branch_code = isset($arr_emps['0']['branch_code']) ? $arr_emps['0']['branch_code'] : 0;
        $cur_emp_key = $emp_pkey;

        $salary_head_items = $this->EmployeeDetails->query("select salary_head_item_fkey as leavepolicy from leavepolicy where is_leave_encash = 'Y' and leave_encash_limit is NOT NULL and status = '1' and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey in ('$cur_emp_key'))");
        $applied = $applied;
        $arr_data = array();
        $LeaveEncashmentMaster_id = 0;
        if ($salary_head_items) {
            foreach ($salary_head_items as $val) {
                $salary = $salary_head_items['0']['leavepolicy']['leavepolicy'];
                $currentyear = $this->EmployeeDetails->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$cur_emp_key')");
                $finyears = isset($currentyear['0']) ? $currentyear['0']['fin_year']['fin_year'] : null;
                $emp_ncashes = $this->EmployeeDetails->query("select leave_balance_inthe_year_fn('$cur_emp_key',salary_head_items.salary_head_item_pkey,'$finyears') as yearlybalance,salary_head_items.item,leavepolicy.*,leavepolicy.LEAVEPOLICYID,leavepolicy.salary_head_item_fkey,leavepolicy.alloted_leave_forthe_year,emp_details.emp_pkey,emp_details.branch_code,concat(emp_details.first_name,' ',ifnull(emp_details.last_name,'')) as emp_name,leavepolicy.alloted_leave_forthe_month,leavepolicy.leave_encash_limit from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on (emp_details.emp_pkey = '$cur_emp_key') where LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$cur_emp_key') and leavepolicy.salary_head_item_fkey = '$salary' and leave_encash_limit is NOT NULL");


                $arr_data['emp_fkey'] = $emp_pkey = $emp_ncashes['0']['emp_details']['emp_pkey'];
                $arr_data['emp_name'] = $emp_ncashes['0']['0']['emp_name'];
                $arr_data['branch_code'] = $branch_code = $emp_ncashes['0']['emp_details']['branch_code'];
                $arr_data['salary_head_item_fkey'] = '0';
                $arr_data['encash_days'] = $emp_ncashes['0']['leavepolicy']['leave_encash_limit'];
                $arr_data['available_days'] = min($emp_ncashes['0']['leavepolicy']['leave_encash_limit'], $emp_ncashes['0']['0']['yearlybalance']);
                $arr_data['requested_days'] = $applied;
                $arr_data['approved_days'] = $applied;
                $arr_data['created_by'] = $this->Session->read('user_name');
                $arr_data['creation_date'] = date('Y-m-d');
                $arr_data['modified_by'] = $this->Session->read('user_name');
                $arr_data['remarks'] = 'terminate';
                $arr_data['approved_by'] = '0';
                $arr_data['is_approved'] = 'Y';
                $arr_data['approved_date'] = date("Y-m-d");
                $result = $this->LeaveEncashmentMaster->query("select * from leave_encashment_master where emp_fkey = '$emp_pkey' and salary_paid = 'N' and salary_head_item_fkey = '$salary'");
                //if(count($result)>0){
                //  $arr_data['leave_encashment_master_pkey'] = isset($result['0']['leave_encashment_master']['leave_encashment_master_pkey'])?$result['0']['leave_encashment_master']['leave_encashment_master_pkey']:'';
                //}

                $this->LeaveEncashmentMaster->query("DELETE FROM leave_encashment_master WHERE remarks = 'terminate' and  emp_fkey = '$emp_pkey' and salary_paid = 'N'");
                if ($results = $this->LeaveEncashmentMaster->save($arr_data)) {
                    $LeaveEncashmentMaster_id = $this->LeaveEncashmentMaster->getLastInsertId();
                    //if(count($result)>0){
                    //   $LeaveEncashmentMaster_id = $arr_data['leave_encashment_master_pkey'];
                    // }
                    $user = $this->Session->read('login_user_id');
                    $emp_ncashes_approve = $this->EmployeeDetails->query("call leave_encash_prc('$branch_code','$emp_pkey','$LeaveEncashmentMaster_id','0','@msg')");
                }
            }
        }
        //$result = $this->LeaveEncashmentMaster->query("select * from leave_encashment_master where emp_fkey = '$emp_pkey' and salary_paid = 'N' ");
        //if(count($result)>0){
        //$arr_data['leave_encashment_master_pkey'] = isset($result['0']['leave_encashment_master']['leave_encashment_master_pkey'])?$result['0']['leave_encashment_master']['leave_encashment_master_pkey']:'';
        //}
        // if ($results = $this->LeaveEncashmentMaster->save($arr_data)) {
        //  $LeaveEncashmentMaster_id = $this->LeaveEncashmentMaster->getLastInsertId();
        //  if(count($result)>0){
        //     $LeaveEncashmentMaster_id = $arr_data['leave_encashment_master_pkey'];
        // }
        // $user = $this->Session->read('login_user_id');
        //  $emp_ncashes_approve = $this->EmployeeDetails->query("call leave_encash_prc('$branch_code','$emp_pkey','$LeaveEncashmentMaster_id','0','@msg')");
        //    return $LeaveEncashmentMaster_id;
        // }
        return $LeaveEncashmentMaster_id;
    }

    public function details_res($emp_pkey = 0)
    {
        $this->autoRender = false;
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $details = $this->Termination->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        //debug($details);
        if ($details) {
            echo json_encode(array(
                'success' => 1,
                'applied_date' => $details['0']['Termination']['last_applied_date'],
                'submitted' => $details['0']['Termination']['submitted_date'],
                'Reason' => $details['0']['Termination']['Reason'],
                'approved' => $details['0']['Termination']['last_approved_working_date'],
                'last_reason' => $details['0']['Termination']['last_working_date'],
                'last_apprv' => $details['0']['Termination']['last_approved_working_date'],
                'LEAVEENTRYID' => $details['0']['Termination']['terminate_pkey'],
                'remarks' => $details['0']['Termination']['remarks'],
                'act_last_working_day' => $details['0']['Termination']['act_last_working_day'],
            ));
        } else {
            echo json_encode(array('success' => 0, 'days' => 0));
        }
    }

    public function getautocompletions()
    {
        $this->autoRender = false;

        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        if ($arr_request_data['branch'] != '') {

            $searchkey = $arr_request_data['username'];
            $branch = $arr_request_data['branch'];
            $filter_condition = 'branch_code = "' . $branch . '" and first_name LIKE "%' . $searchkey . '%"';
        } else {
            $searchkey = $arr_request_data['username'];
            $filter_condition = 'first_name LIKE "%' . $searchkey . '%"';
        }
        //    debug($filter_condition);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code', 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
        $arr_filterresult = array(
            array(
                'emp_pkey' => '',
                'emp_name' => 'ALL',
            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array();
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */

    public function listemployees()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'terminate_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';

        $ofst = ($page - 1) * $limit;
        //edited by amal on 2/7/19 search box start
        $conditions = '';
        if (isset($_REQUEST['emp'])) {
            $qs = $_REQUEST['emp'];
            $con = "CONCAT(EmployeeDetails.first_name,' ', EmployeeDetails.last_name)";
            $conditions[] = $con . '   LIKE "%' . $qs . '%"';
        }
        //edited by amal on 2/7/19 search box end
        $fields = 'Termination.*,Branches.branch_name as branch,CONCAT_WS(" ",first_name,last_name) as name,EmployeeDetails.status as statuses,'
            . 'DATE_FORMAT(Termination.last_applied_date, "%d-%m-%Y") as last_applied_date,DATE_FORMAT(Termination.last_approved_working_date, "%d-%m-%Y") as last_approved_working_date,'
            . 'DATE_FORMAT(Termination.submitted_date, "%d-%m-%Y") as submitted_date';
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = Termination.emp_fkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Branches.branch_code = EmployeeDetails.branch_code')
            ),
        );
        $conditions[] = array('Termination.status' => 1);
        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->Termination->find("count", array('joins' => $joins, "conditions" => $conditions));
        $arr_emp = $this->Termination->find(
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
        //         debug($arr_emp);
        foreach ($arr_emp as $key => $value) {
            //edited by megha on 02-12-2024
            $resp_emp["rows"][$key] = array_merge($value["EmployeeDetails"], $value["Termination"], $value[0], $value["Branches"]);
        }
        //  debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;

        echo json_encode($resp_emp);
    }

    public function DeleteEmployeeResignation()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $head_fkey = $_REQUEST['terminate_pkey'];

        //$arr_form_data = $this -> request -> data;
        //print_r($arr_form_data);die();
        //edited by megha delete resignation request from employee login on 14/12/2019
        $resignation_fkey = $_REQUEST['emp_pkey'];
        $this->ResignationRequests->useDbConfig = $this->Session->read('ds');
        $arr = $this->ResignationRequests->find("all", array("conditions" => array("emp_fkey" => $resignation_fkey, "ResignationRequests.status" => 1)));
        if ($arr > 0) {
            $arr1 = $this->ResignationRequests->updateAll(
                array('status' => 0, 'Resignation_status' => '"Cancelled"'),
                array("emp_fkey" => $resignation_fkey)
            );
        }
        //end delete resignation request from employee login
        $arr_form_data['terminate_pkey'] = $head_fkey;
        $arr_form_data['status'] = 0;
        $this->Termination->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Termination Removed";
        echo json_encode($resp);
    }

    public function workingattendnacedays()
    {
        $this->autoRender = false;
        $arr_emps = $this->request->data;
        $arr_employee = $arr_emps['emp'];

        $todate = $arr_emps['todate'];

        $from = $arr_emps['from'];
        $submitted_dat = date("Y-m-d", strtotime($from));
        $newdate = date("Y-m-d", strtotime($todate));

        $this->Departments->useDbConfig = $this->Session->read('ds');
        $arr_atts = $this->Departments->query("select sum(presant_total)+sum(leave_total) presant_total from attendance_register where month_year >=(
   select max(month_year) from payroll_master where action  in('Approved','Processed') and  emp_fkey = '$arr_employee')  and emp_fkey = '$arr_employee' ");

        $arr_total_work_dats = $arr_atts['0']['0']['presant_total'];

        $leavepolicy_encashed = $this->Departments->query("select distinct salary_head_item_fkey from  leavepolicy where is_leave_encash = 'Y' and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey = '$arr_employee')");

        $leavebalance = 0;
        $warningd = "";
        $currentyear = $this->Departments->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$arr_employee')");
        $finyears = isset($currentyear['0']) ? $currentyear['0']['fin_year']['fin_year'] : null;


        if (!$finyears) {
            $warningd = "you need to provide a Leave Year for this Employee ";
        }
        $leave_name = array(); //Edited by Akshay on 7-3-2024
        foreach ($leavepolicy_encashed as $val) {

            $salary_head_item_fkey = $val['leavepolicy']['salary_head_item_fkey'];
            $arr_leave_name =  $this->Departments->query("SELECT item FROM salary_head_items WHERE salary_head_item_pkey = '$salary_head_item_fkey'");
            $temp_leave_name = isset($arr_leave_name[0]['salary_head_items']['item']) ? trim($arr_leave_name[0]['salary_head_items']['item']) : '';
            if ($temp_leave_name != '') { //Edited by Akshay on 7-3-2024
                $leave_name[] = $temp_leave_name;
            }
            $arr_leav = $this->Departments->query("select leave_balance_inthe_year_fn('$arr_employee','$salary_head_item_fkey','$finyears')  leaves");
            $leavebalance += isset($arr_leav['0']['0']['leaves']) ? round($arr_leav['0']['0']['leaves'], 1) : '0';
            //  debug($arr_leav); 

        }
        $leaveName = '';
        if (!empty($leave_name)) { //Edited by Akshay on 7-3-2024
            $leave_name = array_unique($leave_name);
            $acronyms = array_map(function ($value) {
                $words = explode(' ', $value);
                return strtoupper(substr($words[0], 0, 1) . end($words)[0]);
            }, $leave_name);
            $leaveName = implode(', ',  $acronyms);
        }

        $attendance_days = isset($arr_atts['0']['0']['presant_total']) ? $arr_atts['0']['0']['presant_total'] : '0';

        $arr_att_days_after_resignation = "select sum(a) a from(
            select count(*) as a from emp_detail_timeattandance where others in ('LOP','LOP/LOP') and 	emp_pkey = '$arr_employee' and att_date between '$submitted_dat' and '$newdate'
            union all
            select count(*)/2 as a from emp_detail_timeattandance where (instr(others,'/LOP') > 0 or instr(others,'LOP/') > 0 ) and others!= 'LOP'   and	 emp_pkey = '$arr_employee' and att_date between '$submitted_dat' and '$newdate'
            )ass ";
        $exec_days = $this->Departments->query($arr_att_days_after_resignation);
        $exec_holiday_group_id = $this->Departments->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$arr_employee ");
        $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        $exec_days_holidays = $this->Departments->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
  and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$todate'; ");
        $days_after_resignation_att = isset($exec_days['0']['0']['a']) ? round($exec_days['0']['0']['a'], 1) : 0;
        //Edited by Akshay on 4-9-2024
        $from_reduced = date('Y-m-d', strtotime($from . ' -1 day'));
        $todate_increased = date('Y-m-d', strtotime($todate . ' +1 day'));
        //End
        $diff_with_weekoff_days = $this->Departments->query("select weekoff_days_count_fn('$arr_employee','$todate_increased','$from_reduced') no_of_weekof");

        $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days['0']['0']['no_of_weekof']) + $exec_days_holidays['0']['0']['days_count'] : 0;
        echo json_encode(array('success' => 1, 'Warningd' => $warningd, 'atte' => $attendance_days, 'leave' => $leavebalance, 'weekoff_couts' => $offs, "attendance_days_after_resignation" => $days_after_resignation_att, "leave_name" => $leaveName));
    }

    public function setup($terminate_pkey = 0)
    {
        $str_company_code = strtoupper($this->Session->read('company_code')); // Edited by Akshay on 5-3-2024
        $this->set("str_company_code", $str_company_code); // Edited by Akshay on 5-3-2024
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->set("emp_pkey", $terminate_pkey);
        $termination_details = $arr_emp = $this->Termination->find("all", array("conditions" => array("terminate_pkey" => $terminate_pkey)));
        $this->set("termination_details", $termination_details);
        $emp = $termination_details['0']['Termination']['emp_fkey'];
        $last_approved_wd = $termination_details['0']['Termination']['last_approved_working_date'];
        $submitted_date = $termination_details['0']['Termination']['submitted_date'];
        if ($last_approved_wd > date("Y-m-d")) {
            echo '<div class="callout callout-danger "> You cannot process the full and final before - ' . $last_approved_wd . ' </div>';
            die();
        }
        $loans = $this->Termination->query("select * from emp_loan where emp_fkey = '$emp' and is_completed = 'N' and status = '1' ");

        $resignation_sub = date("Y-m", strtotime($submitted_date));

        $last_approved = date("Y-m", strtotime($last_approved_wd));

        $unverified_attendances_proccess = $this->Termination->query("SELECT count(*) as COUNTS FROM emp_settle_slip join emp_details on (emp_settle_slip.emp_fkey = emp_details.emp_pkey) WHERE `emp_fkey` = '$emp' and emp_details.status = '2' ");

        if ($unverified_attendances_proccess['0']['0']['COUNTS'] > 0) {
            echo '<div class="callout callout-danger "> Termination Already Proccesed  </div>';
            die();
        }
        $policies = $this->Termination->query("SELECT day_time_seq,structure_id FROM emp_proff WHERE `emp_fkey` = '$emp' ");
        //$holiday = $policies['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        //$leave = $policies['0']['emp_proff']['LEAVEPOLICY_GROUP_ID']; 
        $shift = $policies['0']['emp_proff']['day_time_seq'];
        $sal_str = $policies['0']['emp_proff']['structure_id'];
        if (empty($shift) || empty($sal_str)) {
            echo '<div class="callout callout-danger "> Please allocate Shift policy and Salary structure to the employee.</div>';
            die();
        }


        // $unverified_attendances = $this->Termination->query("SELECT count(*) as COUNTS,month_year FROM attendance_register WHERE `emp_fkey` = '$emp' and isdelete = 'Y' and month_year > '2018-04' and month_year < '$last_approved' ");
        //edited by megha on 23_05_19
        $unverified_attendance2 = $this->Termination->query("SELECT distinct max(yearmonth) FROM emp_detail_timeattandance WHERE `emp_pkey` = '$emp' and att_date >= '$submitted_date' and att_date <= '$last_approved_wd' ");
        $unverified_attendance1 = $this->Termination->query("SELECT distinct min(yearmonth) FROM emp_detail_timeattandance WHERE `emp_pkey` = '$emp' and att_date >= '$submitted_date' and att_date <= '$last_approved_wd' ");

        $strtmonth = $unverified_attendance1['0']['0']['min(yearmonth)'];
        $endmonth = $unverified_attendance2['0']['0']['max(yearmonth)'];
        $start_month = date("Y-m", strtotime($strtmonth));
        $end_month = date("Y-m", strtotime($endmonth));
        //added by megha on 28_04_2020 sh infra corrections
        $punverified_attendances = $this->Termination->query("SELECT month_year FROM attendance_register WHERE `emp_fkey` = '$emp' and month_year >= '$start_month' and month_year <= '$end_month' ");
        //debug("SELECT month_year FROM attendance_register WHERE `emp_fkey` = '$emp' and month_year >= '$resignation_sub' and month_year <= '$last_approved' ");
        $punverified_attendances_list = $this->Termination->query("SELECT  month_year FROM attendance_register WHERE month_year >= '$start_month' and month_year <= '$end_month' group by  month_year");
        $monthofattendacne2 = [];
        foreach ($punverified_attendances_list as $key => $value) {
            # code...
            $monthofattendacne2[] = $value['attendance_register']['month_year'];
        }
        //debug($punverified_attendances_list);
        // if(empty($punverified_attendances)) {
        if (count($punverified_attendances_list) > count($punverified_attendances)) {
            echo '<div class="callout callout-danger "> Please process the attendance register of this employee ( ' . implode(", ", $monthofattendacne2) . ' )</div>';
            die();
        }

        //$unverified_attendances = $this->Termination->query("SELECT month_year FROM attendance_register WHERE `emp_fkey` = '$emp' and isdelete = 'Y' and month_year >= '$resignation_sub' and month_year <= '$last_approved' ");
        $unverified_attendances = $this->Termination->query("SELECT month_year FROM attendance_register WHERE `emp_fkey` = '$emp' and isdelete = 'Y' and month_year >= '$start_month' and month_year <= '$end_month' ");
       

        $monthofattendacne1 = [];
        foreach ($unverified_attendances as $key => $value) {
            $monthofattendacne1[] = $value['attendance_register']['month_year'];
        }

        // if (count($punverified_attendances) < count($punverified_attendances)) {
        //     echo '<div class="callout callout-danger "> Please Approve the attendance of this employee ( ' . implode(", ", $monthofattendacne1) . ' )</div>';
        //     die();
        // }
        $monthofattendacne = [];
        foreach ($unverified_attendances as $key => $value) {
            # code...
            $monthofattendacne[] = $value['attendance_register']['month_year'];
        }
        // debug($unverified_attendances);
      
        $count_site = 0;
     //edited by megha on 15_07_2025
        if($str_company_code == 'ABSG'){
            $unverified_attendances_site_ver = $this->Termination->query("SELECT month_year FROM site_attendance_register WHERE `emp_fkey` = '$emp' and isdelete = 'N' and month_year >= '$start_month' and month_year <= '$end_month' ");
       
             $unverified_attendances_site = $this->Termination->query("SELECT month_year FROM site_attendance_register WHERE `emp_fkey` = '$emp' and isdelete = 'Y' and month_year >= '$start_month' and month_year <= '$end_month' ");
        $monthofattendacnesite = [];
        // foreach ($unverified_attendances_site as $key => $value) {
        //     $monthofattendacnesite[] = $value['attendance_register']['month_year'];
        // }
        // $monthofattendacnesite = [];
        // foreach ($unverified_attendances as $key => $value) {
        //     # code...
        //     $monthofattendacnesite[] = $value['attendance_register']['month_year'];
        // }
            $count_site = isset($unverified_attendances_site_ver)?$unverified_attendances_site_ver:0;
        
            if (count($unverified_attendances) >  $count_site ) {
            echo '<div class="callout callout-danger "> Please Approve attendance of this employee. ( ' . implode(", ", $monthofattendacne) . '. )</div>';
            die();
        }
        }else{
         if (count($unverified_attendances) > 0) {
            echo '<div class="callout callout-danger "> Please Approve attendance of this employee ( ' . implode(", ", $monthofattendacne) . ' )</div>';
            die();
        }
        }
        //end
        //debug($loans);
        $assets = $this->Termination->query("select asset_allocate.*,asset_management.* from asset_allocate left join asset_management on (asset_management.asset_pkey = asset_allocate.asset) where asset_allocate.emp_fkey = '$emp' and asset_allocate.damaged_amout is not null ");
        $arr_ln = array();
        foreach ($loans as $val) {
            $loan_pkey = $val['emp_loan']['emp_loan_pkey'];
            $loan = $this->Termination->query("select * from emp_loan_info where emp_loan_info_pkey in (select min(emp_loan_info_pkey) from emp_loan_info where loan_pkey = $loan_pkey and amount_paid = 0 and status =1) and status =1 and paid_status != 'P' ");
            $val['summary'] = $loan;
            $arr_ln[] = $val;
        }
        //$advances = $this->Termination->query("select * from emp_advance where emp_fkey = '$emp' and is_credited = 'N' and status = 1 ");
        //        debug($arr_ln);
        $this->set("arr_ln", $arr_ln);
        $this->set("assets", $assets);
        //$this->set("advances",$advances);
        //Edited by Akshay on 22-12-2023
        $this->Termination->query("UPDATE emp_settle_slip
                SET status = 0
                WHERE status = 1 AND emp_fkey = '$emp' AND type = 'ENCASHMENT';");
        $this->Termination->query("UPDATE emp_settle_slip
                SET status = 0
                WHERE status = 1 AND emp_fkey = '$emp' AND type = 'BALANCE';");
        $this->Termination->query("UPDATE emp_settle_slip
                SET status = 'N'
                WHERE status = 'Y' AND emp_fkey = '$emp' AND type = 'ID';");

        //Edited by Akshay on 21-8-2024
        $arr_pro_data = $this->Termination->query("select distinct(prorate_code)  "
            . "from emp_salary_structure as ectc "
            . " where ectc.emp_fkey = '$emp' "
            . "and end_date_effective is null ");
        $prorate_code = isset($arr_pro_data[0]['ectc']['prorate_code']) ? $arr_pro_data[0]['ectc']['prorate_code'] : '1';
        if ($prorate_code == '1') {
            $condition = "AND weekoff IS NULL AND holiday IS NULL";
        } else {
            $condition = "";
        }

        $exec_holiday_group_id = $this->Termination->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp ");
        $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        // $last_working_date = isset($termination_details['0']['Termination']['last_working_date']) ? $termination_details['0']['Termination']['last_working_date'] : '';
        //Edited by Akshay on 17-10-2024
        $last_working_date = isset($termination_details['0']['Termination']['last_approved_working_date']) ? $termination_details['0']['Termination']['last_approved_working_date'] : '';
        //End
        $notice_period = isset($termination_details['0']['Termination']['notice_period']) ? $termination_details['0']['Termination']['notice_period'] : 0;
        //Edited by Akshay on 4-9-2024
        $submitted_date_reduced = date('Y-m-d', strtotime($submitted_date . ' -1 day'));
        $last_working_date_increased = date('Y-m-d', strtotime($last_working_date . ' +1 day'));
        //End
        $diff_with_weekoff_days_actual = $this->Termination->query("select weekoff_days_count_fn('$emp','$last_working_date_increased','$submitted_date_reduced') no_of_weekof");

        $exec_days_holidays_actual = $this->Termination->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
                and HOLIDAYDATE  between DATE_FORMAT('$submitted_date' , '%Y-%m-%d') and '$last_working_date' and status = '1'; ");
        $offs_actual = isset($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) + $exec_days_holidays_actual['0']['0']['days_count'] : 0;

        // $resignaion_period_working_days = $this->Termination->query("SELECT COUNT(DISTINCT `att_date`) AS count
        //                                                                 FROM `emp_detail_timeattandance`
        //                                                                 WHERE `emp_pkey` = '$emp' 
        //                                                                 AND `att_date` >= '$submitted_date' 
        //                                                                 AND `att_date` <= '$last_working_date'
        //                                                                 $condition");
        // $resignaion_period_working_days = isset($resignaion_period_working_days[0][0]['count']) ? $resignaion_period_working_days[0][0]['count'] : 0;

        $resignaion_period_working_days = ($notice_period > 0) ? ($notice_period - $offs_actual) : 0;
        $this->set("resignaion_period_working_days", $resignaion_period_working_days);

        // $resignaion_period_present_days = $this->Termination->query("SELECT 
        //                                 SUM(
        //                                     CASE 
        //                                         WHEN present LIKE '%P/P%' THEN 1
        //                                         WHEN present LIKE '%P/A%' OR present = 'A/P' THEN 0.5
        //                                         WHEN others LIKE '%WFH%' THEN 1
        //                                         WHEN others LIKE '%WFO%' THEN 1
        //                                         ELSE 0
        //                                     END
        //                                 ) AS count
        //                             FROM 
        //                                 emp_detail_timeattandance 
        //                             WHERE 
        //                                 emp_pkey = '$emp' 
        //                                 AND att_date >= '$submitted_date' 
        //                                 AND att_date <= '$last_working_date';
        //                             ");

        // $resignaion_period_present_days = isset($resignaion_period_present_days[0][0]['count']) ? floatval($resignaion_period_present_days[0][0]['count']) : 0;
        // $this->set("resignaion_period_present_days", $resignaion_period_present_days);
        //End

        //Edited by Akshay on 3-9-2024
        $last_approved_working_date = isset($termination_details['0']['Termination']['last_approved_working_date']) ? $termination_details['0']['Termination']['last_approved_working_date'] : '';
        $att_startdate = $this->Termination->query("select att_start_end_fn(DATE_FORMAT('$submitted_date', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_startdate = $att_startdate[0][0]['monthly_att_fromdate'];
        // debug($last_approved_working_date);
        // $last_working_date = $last_approved_working_date;
        $att_enddate = $this->Termination->query("select att_start_end_fn(DATE_FORMAT('$last_working_date', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_enddate = $att_enddate[0][0]['monthly_att_todate'];
        $att_startmonth_end_date = $this->Termination->query("select att_start_end_fn(DATE_FORMAT('$submitted_date', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startmonth_end_date = $att_startmonth_end_date[0][0]['monthly_att_todate'];

        $att_endmonth_start_date = $this->Termination->query("select att_start_end_fn(DATE_FORMAT('$last_working_date', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_endmonth_start_date = $att_endmonth_start_date[0][0]['monthly_att_fromdate'];
        // debug($last_working_date);
        $submitted_date_obj = new DateTime($submitted_date);
        $att_startdate_obj = new DateTime($att_startmonth_end_date);
        $last_working_date_obj = new DateTime($last_working_date);
        // debug($last_working_date);
        $att_enddate_obj = new DateTime($att_enddate);
        // debug($att_enddate);

        // Determine the start month
        //Edited by Akshay on 19-9-2024
        if ($att_startdate_obj >= $submitted_date_obj) {
            // If the start month end date is greater than or equal to the submitted date
            $start_month = $att_startdate_obj->format('Y-m');
        } else {
            // Add 1 month to the start date
            $att_startdate_obj->modify('+1 month');
            $start_month = $att_startdate_obj->format('Y-m');
        }
        //End

        // Determine the end month

        if ($last_working_date_obj > $att_enddate_obj) {
            $end_month = $att_enddate_obj->modify('+1 month')->format('Y-m');
        } else {
            $end_month = $att_enddate_obj->format('Y-m');
        }
        $start_date = new DateTime($start_month . '-01');
        $end_date = new DateTime($end_month . '-01');
        $start_date->modify('+1 month');
        // debug($start_date);debug($end_date);
        $month_year_array = array();
        while ($start_date < $end_date) {
            // Add the current month-year to the array
            $month_year_array[] = $start_date->format('Y-m');
            // Move to the next month
            $start_date->modify('+1 month');
        }
 
        function getFieldNumber($att_startdate, $submitted_date, $start = true)
        {
            // Convert month_year to a DateTime object representing the first day of that month
            $start_date =  new DateTime($att_startdate);

            // Convert the given date to a DateTime object
            $given_date = new DateTime($submitted_date);
            // Calculate the difference in days
            if ($start) {
                $day_diff = $given_date->diff($start_date)->days;
            } else {
                $day_diff = $start_date->diff($given_date)->days;
            }

            // Field number is the difference plus 1 (since FIELD1 corresponds to the first day)
            $field_number = $day_diff + 1;
            $field_number = ($field_number <= 32) ? $field_number : 32;
            // Return the field number as "FIELDx"
            return $field_number;
        }
        // debug($start_month); debug($end_month);
        $att_startdate = $this->Termination->query("select att_start_end_fn(DATE_FORMAT('$start_month-01', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_startdate = $att_startdate[0][0]['monthly_att_fromdate'];
        $start_field = (getFieldNumber($att_startdate, $submitted_date, true));
        $att_endmonth_start_date = $this->Termination->query("select att_start_end_fn(DATE_FORMAT('$end_month-01', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_endmonth_start_date = $att_endmonth_start_date[0][0]['monthly_att_fromdate'];
        $end_field = getFieldNumber($att_endmonth_start_date, $last_working_date, false);

        // Array of patterns for full and half points
        $full_points_patterns = ['WFH/WFH', 'P/P', 'WFO/WFO', 'WFH', 'P', 'WFO', 'WFH/WFO', 'WFH/P', 'P/WFH'];
        $half_points_patterns = ['WFH/A', 'WFO/A', 'P/A', 'A/P', 'A/WFH', 'A/WFO', 'WFH/WO', 'WFH/SL', 'WFH/PL', 'WFH/COFF', 'WFH/CL', 'SL/WFH', 'SL/P', 'P/WO', 'P/SL', 'P/NA', 'P/LOP', 'P/EL', 'P/COFF', 'P/CL', 'P/A', 'ML/WFH', 'LOP/WFH', 'LOP/P', 'COFF/WFH', 'CL/WFH', 'CL/P', 'AL/WFH',];
        $month_year_in_clause = "'" . implode("', '", $month_year_array) . "'";
        // debug($start_month); debug($end_month);
        // Initialize the query
        $sql = "SELECT emp_fkey, SUM(";
        // Loop through the field numbers
        if ($start_month != $end_month) {
            for ($i = $start_field; $i <= 32; $i++) {
                $field = "FIELD$i";
                // Construct CASE statement for full points
                $sql .= "CASE WHEN $field IN ('" . implode("', '", $full_points_patterns) . "') THEN 1 ";
                // Construct CASE statement for half points
                $sql .= "WHEN $field IN ('" . implode("', '", $half_points_patterns) . "') THEN 0.5 ";
                // Default to 0 points
                $sql .= "ELSE 0 END";
                // Add a plus sign for all but the last field
                if ($i < 32) {
                    $sql .= " + ";
                }
            }
        } else {
            for ($i = $start_field; $i <= $end_field; $i++) {
                $field = "FIELD$i";
                // Construct CASE statement for full points
                $sql .= "CASE WHEN $field IN ('" . implode("', '", $full_points_patterns) . "') THEN 1 ";
                // Construct CASE statement for half points
                $sql .= "WHEN $field IN ('" . implode("', '", $half_points_patterns) . "') THEN 0.5 ";
                // Default to 0 points
                $sql .= "ELSE 0 END";
                // Add a plus sign for all but the last field
                if ($i < $end_field) {
                    $sql .= " + ";
                }
            }
        }

       

        //End month
        $sql2 = "SELECT emp_fkey, SUM(";
        // Loop through the field numbers
        for ($i = 1; $i <= $end_field; $i++) {
            $field = "FIELD$i";
            // Construct CASE statement for full points
            $sql2 .= "CASE WHEN $field IN ('" . implode("', '", $full_points_patterns) . "') THEN 1 ";
            // Construct CASE statement for half points
            $sql2 .= "WHEN $field IN ('" . implode("', '", $half_points_patterns) . "') THEN 0.5 ";
            // Default to 0 points
            $sql2 .= "ELSE 0 END";
            // Add a plus sign for all but the last field
            if ($i < $end_field) {
                $sql2 .= " + ";
            }
        }

         //edited by megha on 15_07_2025
        if($count_site > 0 ){
             $sql .= ") AS total_points FROM attendance_register WHERE record_status = '1' 
                                AND month_year = '$start_month' AND emp_fkey = '$emp' 
                                AND isdelete = 'N' GROUP BY emp_fkey";
             $sql2 .= ") AS total_points FROM attendance_register WHERE record_status = '1' 
                                AND month_year = '$end_month' AND emp_fkey = '$emp' 
                                AND isdelete = 'N' GROUP BY emp_fkey";
        }else{
             // Close the SUM() and add the rest of the query
        $sql .= ") AS total_points FROM attendance_register WHERE record_status = '1' 
                                AND month_year = '$start_month' 
                                AND emp_fkey = '$emp' 
                                AND isdelete = 'N' 
                    GROUP BY emp_fkey";
       // Close the SUM() and add the rest of the query
        $sql2 .= ") AS total_points FROM attendance_register WHERE record_status = '1' 
                                AND month_year = '$end_month' 
                                AND emp_fkey = '$emp' 
                                AND isdelete = 'N' 
                    GROUP BY emp_fkey";
        }

        try {

            $arr_count_start_month = $this->Termination->query($sql);

            $count_start_month = isset($arr_count_start_month[0][0]['total_points']) ? $arr_count_start_month[0][0]['total_points'] : 0;

            if ($start_month != $end_month)
                $arr_count_end_month = $this->Termination->query($sql2);
            $count_end_month =  isset($arr_count_end_month[0][0]['total_points']) ? $arr_count_end_month[0][0]['total_points'] : 0;

            if ($start_month != $end_month)
                $arr_count_intermediate = $arr_count_start_month = $this->Termination->query("SELECT SUM(presant_total) AS presant_days FROM attendance_register 
                                                                                            WHERE record_status = '1' 
                                                                                            AND month_year IN ($month_year_in_clause) 
                                                                                            AND emp_fkey = '$emp' 
                                                                                            AND isdelete = 'N'  
                                                                                            GROUP BY emp_fkey;");
            $count_intermediate = isset($arr_count_intermediate[0][0]['presant_days']) ? $arr_count_intermediate[0][0]['presant_days'] : 0;
            // debug($count_start_month); debug($count_end_month); debug($count_intermediate);
            $count_start_month = floatval($count_start_month);
            $count_end_month = floatval($count_end_month);
            $count_intermediate = floatval($count_intermediate);
            // debug($count_start_month);
            // debug($count_end_month);
            // debug($count_intermediate);
            $resignaion_period_present_days = $count_start_month + $count_end_month + $count_intermediate;
            $this->set("resignaion_period_present_days", $resignaion_period_present_days);
        } catch (Exception $e) {
           // debug($e);
        }

        //End
    }

    /*
     * Show tax Head Details form
     */

    public function Terminate()
    {

        $this->Termination->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data); exit;
        $save_data = array();
        try {
            $save_data['terminate_pkey'] = isset($arr_form_data['terminate_pkey']) ? $arr_form_data['terminate_pkey'] : '';
            $save_data['emp_fkey'] = $emp_fkey = $arr_form_data['emp_fkey'];
            $save_data['applied_date'] = $arr_form_data['applied_date']; //Edited by Akshay on 18-3-2024
            $save_data['Reason'] = $arr_form_data['Reason'];
            $save_data['is_authorized'] = "Y";
            $save_data['authorized_by'] = 0;
            $save_data['is_approved'] = "Y";
            $save_data['approved_by'] = "0";
            $save_data['submitted_date'] = date('Y-m-d', strtotime($arr_form_data['date_submitted'])); //Edited by Akshay on 18-3-2024
            $save_data['last_applied_date'] = date('Y-m-d', strtotime($arr_form_data['applied_date'])); //Edited by Akshay on 18-3-2024
            $save_data['last_working_date'] =  $arr_form_data['lat_workingday'];
            $save_data['notice_period'] = $arr_form_data['notice_period'];
            $save_data['last_approved_working_date'] = date('Y-m-d', strtotime($arr_form_data['apprved'])); //Edited by Akshay on 18-3-2024
            $save_data['last_working_date'] = date('Y-m-d', strtotime($arr_form_data['lat_workingday']));
            $save_data['remarks'] = $arr_form_data['Remarks'];
            $save_data['act_last_working_day'] = date('Y-m-d', strtotime($arr_form_data['apprved']));
            $this->autoRender = FALSE;
            //$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            //            $this->EmployeeDetails->updateAll(array("status"=>2),array("emp_pkey"=>$emp_fkey));
            $arr_form_data1 = array();
            $arr_form_data1['emp_pkey'] = $arr_form_data['emp_fkey'];
            $arr_form_data1['status'] = 2;
            $e = "";

            //reference
            //--trigger_error("Error message to display", E_USER_ERROR);
            //E_USER_ERROR,E_USER_DEPRECATED,E_USER_NOTICE,E_USER_WARNING
            //set_error_handler($error_handler, E_USER_ERROR);



            if (!$this->Termination->save($save_data)) {
                throw new Exception("Data cannot be saved, Please try again");
            }
        } catch (ErrorException $e) {
            $e->getMessage();
        } catch (Exception $e) {
            $e->getMessage();
        } catch (mysqli_sql_exception $m) {
            $m->getMessage();
        }
        echo json_encode(array("success" => 1, "msg" => "Successfully Terminated Employee", "error" => $e));
    }

    //Edited by Akshay on 27-11-2023
    public function get_emp_full_and_final_settle_slip($emp_pkey = 0)
    {
        $str_company_code = $this->Session->read('company_code'); //Edited by Askshay on 26-3-2024
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $datas = array();
        try {
            // additional amounts
            $datas['Extra_additions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type != 'SALARY' and salary_amount >=0 ");
            // deduction amounts
            $datas['Extra_deductions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type != 'SALARY' and salary_amount <0 ");

            // additional amounts salary
            $datas['encashment'] = $this->EmployeeDetails->query("SELECT emp_settle_slip.*, shi.item, shi.occurance
                                                                    FROM emp_settle_slip
                                                                    LEFT JOIN leave_encashment_master AS lm ON lm.emp_fkey = emp_settle_slip.emp_fkey
                                                                    LEFT JOIN salary_head_items AS shi ON lm.salary_head_item_fkey = shi.salary_head_item_pkey
                                                                    WHERE emp_settle_slip.emp_fkey = '$emp_pkey' and emp_settle_slip.status = 'Y' and emp_settle_slip.type = 'SALARY' and emp_settle_slip.salary_amount >=0 
                                                                    AND lm.salary_head_item_fkey > 0
                                                                    AND emp_settle_slip.salary_head_item_fkey = 111
                                                                    ");
            if ($str_company_code == 'KWMT') { //Edited by Akshay on 26-3-2024
                $datas['payd_additional'] = $this->EmployeeDetails->query("SELECT sum(salary_amount) salary_amount,emp_settle_slip.* FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' and salary_amount >=0 and emp_settle_slip.salary_head_item_fkey != 111 and (emp_settle_slip.paid = 'N' OR emp_settle_slip.paid IS NULL) group by emp_settle_slip.salary_head_item_fkey");

                $datas['payd_deductions'] = $this->EmployeeDetails->query("SELECT sum(salary_amount) salary_amount,emp_settle_slip.* FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' and salary_amount <0 and (emp_settle_slip.paid = 'N' OR emp_settle_slip.paid IS NULL) group by emp_settle_slip.salary_head_item_fkey");
            } else {
                $datas['payd_additional'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' and salary_amount >=0 and emp_settle_slip.salary_head_item_fkey != 111");

                $datas['payd_deductions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' and salary_amount <0 ");
            }

            return $datas;
        } catch (Exception $e) {
            // debug($e);
        }
    }
    //Edited by Megha ES on 25-3-2024
    public function get_emp_settle_slip_watermt($emp_pkey = 0)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $datas = array();
        // additional amounts
        $datas['Extra_additions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type != 'SALARY' and salary_amount >=0 and type != 'ID' ");
        // deduction amounts
        $datas['Extra_deductions'] = $this->EmployeeDetails->query("SELECT * FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type != 'SALARY' and (salary_amount <0 or type = 'ID')  ");

        // additional amounts salary
        $datas['payd_additional'] = $this->EmployeeDetails->query("SELECT sum(salary_amount) salary_amount,emp_settle_slip.* FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and (paid = 'N' OR paid IS NULL) and type = 'SALARY' and salary_amount >=0 group by salary_head_item_fkey ");
        // deductssalary
        $datas['payd_deductions'] = $this->EmployeeDetails->query("SELECT sum(salary_amount) salary_amount,emp_settle_slip.* FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' and status = 'Y' and (paid = 'N' OR paid IS NULL) and type = 'SALARY' and salary_amount <0 group by salary_head_item_fkey ");

        return $datas;
    }
    public function paysalary()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
        $emp_pkey = $arr_request_data['emp_pkey'];
        $paidstatus = $arr_request_data['status'];
        $month_year = $arr_request_data['month_year'];
        $e = '';
        try {
            if ($paidstatus == 'Y') {
                $this->EmployeeDetails->query("UPDATE emp_settle_slip SET paid = 'Y' WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' AND month_year ='$month_year'");
            } else {
                $this->EmployeeDetails->query("UPDATE emp_settle_slip SET paid = 'N' WHERE emp_fkey = '$emp_pkey' and status = 'Y' and type = 'SALARY' AND month_year ='$month_year'");
            }
        } catch (Exception $e) {
        }

        //Edited by Akshay on 26-3-2024
        echo json_encode(array("success" => 1, "msg" => "Successfully updated status in emp_settle_slip", "error" => $e));
    }
    //Edited by Akshay on 10-6-2024
    public function load_birthdays()
    {
        $this->autoRender = false;
        $date = date('Y-m-d');
        $table_joins[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins[] = array(
            'table' => 'wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Birthday', 'Wish.date' => $date)
        );
        $table_joins1[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => 'wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Work Anniversary', 'Wish.date' => $date)
        );
        //            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        //            $this->set('arr_employees', $arr_employees);
        $conditions = array(
            array(" DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')
                    or DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d') 
                    or DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d') or 
                    DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d') or 
                    DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')", 'status' => 1)
        );
        $conditions1 = array(

            array(" DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')
                    or DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d') 
                    or DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d') or 
                    DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d') or 
                    DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d') ", 'status' => 1)

        );
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $this->Latein->useDbConfig = $this->Session->read('ds');
        //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
        $arr_employees_pics = $this->EmployeeDetails->find(
            "all",
            array(
                "fields" => array("EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date", "EmployeeDetails.email"),
                "joins" => $table_joins,
                "order" => "EmployeeDetails.date_of_birth DESC",
                "conditions" => $conditions
            )
        );

        $arr_employees_pics1 = $this->EmployeeDetails->find(
            "all",
            array(
                "fields" => array("Wish.emp_fkey,EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"),
                "joins" => $table_joins1,
                "order" => "Empproff.joining_date DESC",
                "conditions" => $conditions1
            )
        );

        $today = date('Y-m-d');

        echo json_encode(array('success' => 1, 'msg' => 'Sending wishes', 'emp' => $arr_employees_pics, 'emp1' => $arr_employees_pics1));
    }
    //End
    public function sendEmail()
    {
        $this->autoRender = false;
        $emp_pkey = $this->request->data['emp_pkey'];

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp_details = $this->EmployeeDetails->query("SELECT ed.email, ei.EmpName FROM employee_info ei 
                                                            LEFT JOIN emp_details ed ON ed.emp_pkey = ei.emp_pkey
                                                            WHERE ei.emp_pkey = $emp_pkey");

        $email = $arr_emp_details[0]['ed']['email'];
        $name  = $arr_emp_details[0]['ei']['EmpName'];

        // Call the reusable function to generate the PDF
        $pdf_path = $this->generateFinalSlipPDF($emp_pkey);

        // PHPMailer setup
        App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
        $mail = new PHPMailer;
        $mail->SMTPDebug = false;
        $mail->isSMTP();
        $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
        $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('mypayrollmaster@office24.online');
       
       // $mail->addBCC('crm@greatleap.tech');
        $mail->addBCC('projects@greatleap.tech');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Full And Final Slip";
        $mail->Body = "
            <html>
            <body>
                <p>Dear $name,</p>
                <p>Your Full and Final Slip is attached in this mail.</p>
            </body>
            </html>
        ";
        //edited by athira on 10-04-2025
        $mail->addAttachment($pdf_path, "Full&FinalSlip.pdf");
        //end

        if (!$mail->send()) {
            echo json_encode([
                'status' => 'danger',
                'message' => "Couldn't sent mail!" . $mail->ErrorInfo
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Email sent successfully'
            ]);
            exit;
        }

        if (file_exists($pdf_path)) {
            unlink($pdf_path);
        }
    }

    public function generateFinalSlipPDF($emp_pkey)
    {
        $this->autoRender = false;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->Termination->useDbConfig = $this->Session->read('ds');

        $str_company_code = $this->Session->read('company_code');
        $company_info = $this->CompanyContactInfo->query('SELECT business_name, logo FROM comp_contact_info');
        $business_name = $company_info[0]['comp_contact_info']['business_name'];
        $logo = $company_info[0]['comp_contact_info']['logo'];
        try {
            $structure = $this->EmployeeDetails->query("select salary_head_item_desc,structure_det_value from emp_salary_structure where emp_fkey = '$emp_pkey' "
                . "and end_date_effective is null and head_operator='Addition' and item_part ='Direct' and remarks is null");
            $this->set("structure", $structure);

            $from = date('Y-m');
            $arr_emps = $this->request->data;
            if ($str_company_code == 'KWMT') {
                $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.first_name, EmployeeDetails.last_name, EmployeeDetails.branch_code,EmployeeDetails.classification,EmployeeDetails.maritual_status,EmployeeInfo.*,SalaryHeadItems.occurance, EmpSettleSlip.salary_head_item_desc, EmpSettleSlip.status, EmpSettleSlip.type ,EmpSettleSlip.leave_total,EmpSettleSlip.salary_amount"), "conditions" => array("Termination.emp_fkey" => $emp_pkey, "Termination.status" => '1'), "joins" => array( //Edited by Akshay on14-3-2024
                    array(
                        "table" => "employee_info",
                        "alias" => "EmployeeInfo",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "emp_details",
                        "alias" => "EmployeeDetails",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "leave_encashment_master",
                        "alias" => "LeaveEncashmentMaster",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("LeaveEncashmentMaster.emp_fkey = Termination.emp_fkey", "LeaveEncashmentMaster.salary_head_item_fkey" > 0, "LeaveEncashmentMaster.remarks= 'terminate'")
                    ),
                    array(
                        "table" => "salary_head_items",
                        "alias" => "SalaryHeadItems",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey ")
                    ),
                    array(
                        "table" => "emp_settle_slip",
                        "alias" => "EmpSettleSlip",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmpSettleSlip.emp_fkey = Termination.emp_fkey AND (EmpSettleSlip.status = 1 OR EmpSettleSlip.status = 'Y') AND EmpSettleSlip.type IN ('ID', 'ENCASHMENT', 'BALANCE')")
                    )
                )));

                $arr_pro_data = $this->Termination->query("select distinct(prorate_code)  "
                    . "from emp_salary_structure as ectc "
                    . " where ectc.emp_fkey = '$emp_pkey' "
                    . "and end_date_effective is null ");
                $prorate_code = isset($arr_pro_data[0]['ectc']['prorate_code']) ? $arr_pro_data[0]['ectc']['prorate_code'] : '1';
                if ($prorate_code == '1') {
                    $condition = "AND weekoff IS NULL AND holiday IS NULL";
                } else {
                    $condition = "";
                }
                $submitted_date = $details['0']['Termination']['submitted_date'];
                $last_working_date = isset($details['0']['Termination']['last_working_date']) ? $details['0']['Termination']['last_working_date'] : '';
                $resignaion_period_working_days = $this->Termination->query("SELECT COUNT(DISTINCT `att_date`) AS count
                                                                    FROM `emp_detail_timeattandance`
                                                                    WHERE `emp_pkey` = '$emp_pkey' 
                                                                    AND `att_date` >= '$submitted_date' 
                                                                    AND `att_date` <= '$last_working_date'
                                                                    $condition ");

                $resignaion_period_working_days = isset($resignaion_period_working_days[0][0]['count']) ? $resignaion_period_working_days[0][0]['count'] : 0;
                $this->set("resignaion_period_working_days", $resignaion_period_working_days);
            } else {
                $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeDetails.classification,EmployeeDetails.maritual_status,EmployeeInfo.*,SalaryHeadItems.occurance"), "conditions" => array("Termination.emp_fkey" => $emp_pkey), "joins" => array(
                    array(
                        "table" => "employee_info",
                        "alias" => "EmployeeInfo",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "emp_details",
                        "alias" => "EmployeeDetails",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey")
                    ),
                    array(
                        "table" => "leave_encashment_master",
                        "alias" => "LeaveEncashmentMaster",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("LeaveEncashmentMaster.emp_fkey = Termination.emp_fkey", "LeaveEncashmentMaster.salary_head_item_fkey" > 0)
                    ),
                    array(
                        "table" => "salary_head_items",
                        "alias" => "SalaryHeadItems",
                        "foreignKey" => false,
                        "type" => "LEFT",
                        "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey ")
                    )
                )));
            }
            $this->set("details", $details);
            $res_sub_date = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
            $date = new DateTime($res_sub_date);
            $res_month = $date->format('Y-m');

            $arr_annual_ctc = $this->EmployeeDetails->query("SELECT emp_anual_ctc FROM emp_ctc_transaction ect WHERE ect.end_date_effective IS NULL AND ect.emp_fkey = '$emp_pkey'");
            $annual_ctc = isset($arr_annual_ctc[0]['ect']['emp_anual_ctc']) ? $arr_annual_ctc[0]['ect']['emp_anual_ctc'] : 0;

            list($year, $month) = explode('-', $res_month);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            if ($str_company_code == 'KWMT') {
                $approved_date = isset($details['0']['Termination']['last_approved_working_date']) ? $details['0']['Termination']['last_approved_working_date'] : '';
                try {
                    $arr_start_end = $this->EmployeeDetails->query("SELECT `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 1) as start_date, `att_start_end_fn`(DATE_FORMAT('$approved_date', '%Y-%m-01'), 2) as end_date");
                } catch (Exception $e) {
                    debug($e);
                }

                $start_date = isset($arr_start_end[0][0]['start_date']) ? $arr_start_end[0][0]['start_date'] : '';
                $end_date = isset($arr_start_end[0][0]['end_date']) ? $arr_start_end[0][0]['end_date'] : '';
                $approved_timestamp = strtotime($approved_date);
                $start_timestamp = strtotime($start_date);
                $end_timestamp = strtotime($end_date);

                if ($approved_timestamp < $start_timestamp) {
                    $aprvd_month = date('Y-m', strtotime('-1 month', $approved_timestamp));
                } elseif ($approved_timestamp > $end_timestamp) {
                    $aprvd_month = date('Y-m', strtotime('+1 month', $approved_timestamp));
                } else {
                    $aprvd_month = date('Y-m', $approved_timestamp);
                }

                list($year, $month) = explode('-', $aprvd_month);
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                $arr_gross_salary = $this->EmployeeDetails->query("SELECT SUM(structure_det_value) AS total_value FROM `emp_salary_structure` 
                                                                        WHERE `emp_fkey` = '$emp_pkey' AND `end_date_effective` IS NULL 
                                                                        AND `head_operator` = 'Addition' 
                                                                        AND `item_part` = 'DIRECT'
                                                                        AND salary_head_item_fkey IN(SELECT 
                                                                        salary_head_item_pkey FROM salary_head_items WHERE head_fkey IN ('1', '4'));");
                $gross_salary = isset($arr_gross_salary[0][0]['total_value']) ? $arr_gross_salary[0][0]['total_value'] : 0;
                $notice_period = isset($details['0']['Termination']['notice_period']) ? floatval($details['0']['Termination']['notice_period']) : 0;
                $per_day_salary = (($gross_salary / 30));
            } else {
                $per_day_salary = ((round($annual_ctc) / 12) / $daysInMonth);
            }

            $this->set('per_day_salary', $per_day_salary);

            $arr_net_salary = $this->EmployeeDetails->query("SELECT net_salary, loss_of_pay FROM payroll_master WHERE emp_fkey = '$emp_pkey' AND month_year = '$res_month' AND (action = 'Processed' OR action = 'Approved' OR action = 'Audited') ");
            $res_net_salary = isset($arr_net_salary[0]['payroll_master']['net_salary']) ? $arr_net_salary[0]['payroll_master']['net_salary'] : 0;
            $res_lop = isset($arr_net_salary[0]['payroll_master']['loss_of_pay']) ? $arr_net_salary[0]['payroll_master']['loss_of_pay'] : 0;

            $this->set('res_net_salary', $res_net_salary);

            $emp_id = 'ADMIN';

            if ($str_company_code == 'KWMT') {
                $amt_paid_emp = $this->EmployeeDetails->query("SELECT amt_paid_by_empaddition,amt_paid_by_empdeduction FROM termination WHERE emp_fkey = '$emp_pkey' AND status = 1"); //Edited by akshay on 11-7-2024
                $this->set('amt_paid_emp', $amt_paid_emp);
            }

            $this->set('arr_emp_settle', $arr_emp_settle = $this->get_emp_full_and_final_settle_slip($emp_pkey));
            $str_company_code = $this->Session->read('company_code');
            if ($str_company_code == 'KWMT') {
                $submitted_date = isset($details['0']['Termination']['submitted_date']) ? $details['0']['Termination']['submitted_date'] : '';
                $last_working_date = isset($details['0']['Termination']['last_working_date']) ? $details['0']['Termination']['last_working_date'] : '';
                $submitted_date_reduced = date('Y-m-d', strtotime($submitted_date . ' -1 day'));
                $last_working_date_increased = date('Y-m-d', strtotime($last_working_date . ' +1 day'));
                $diff_with_weekoff_days_actual = $this->EmployeeDetails->query("select weekoff_days_count_fn('$emp_pkey','$last_working_date_increased','$submitted_date_reduced') no_of_weekof");
                $exec_holiday_group_id = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
                $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
                $exec_days_holidays_actual = $this->EmployeeDetails->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
                and HOLIDAYDATE  between DATE_FORMAT('$submitted_date' , '%Y-%m-%d') and '$last_working_date' and status = '1'; ");
                $offs_actual = isset($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days_actual['0']['0']['no_of_weekof']) + $exec_days_holidays_actual['0']['0']['days_count'] : 0;
                $this->set('offs_actual', $offs_actual);
            }
        } catch (Exception $e) {
            debug($e);
        }

        $startDate = new DateTime($details['0']['EmployeeInfo']['joining_date']);
        $endDate = new DateTime($details['0']['Termination']['act_last_working_day']);

        $interval = $startDate->diff($endDate);

        $years = $interval->y;
        $months = $interval->m;
        $days = $interval->d;
        $this->set(compact('logo', 'business_name', 'months', 'years', 'days'));

        $view_output = $this->render('full_and_final_slip', false);

        // Generate PDF
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        $html2pdf->writeHTML($view_output);
        //edited by athira on 10-04-2025
        $pdf_path = TMP . "Full&FinalSlip.pdf";
        //end
        $html2pdf->Output($pdf_path, 'F');

        return $pdf_path;
    }
    // End
}
