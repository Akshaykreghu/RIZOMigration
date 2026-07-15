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
class PayrollController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Payroll';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */

    //edited by athira on 04-07-2025
    public $uses = array('EmployeeDetails', 'CompanyContactInfo', 'Units', 'AttendanceRegister', 'MonthlyCTC', 'GrossSalary', 'TotalDeductions', 'MonthlyAmount', 'Payrollmaster', 'EmpSalarySlip', 'EmployeeProfessionalDetails');
    //end
    public $components = array('MasterdataManagement');

    public function showprocesspayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
        $cur_emp_key = $this->Session->read("emp_fkey");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $plan = $this->EmployeeDetails->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $user_group = $this->Session->read('user_group');
        //edited by sinsiya
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;
            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $arr_conditions = 'and branch_code!="' . $branch . '"';
            //debug($branch);
            // exit;
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), 'conditions' => array('status' => 1, 'branch_code' => $branch))));
            $this->set('arr_employees', $arr_employees);
        }
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        //$arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array( 'order' => array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), 'conditions' => array('status' => 1, 'branch_code' => $branch) )));
        $this->set('arr_employees', $arr_employees);
        /* $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
          $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
          $this->set('arr_employees', $arr_employees); */
        // back button
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $this->CentralUserCredentials->setDataSource('controldb');

        $company_code = $this->Session->read('company_code');

        $data = $this->CentralUserCredentials->find('first', array(
            'conditions' => array(
                'CentralUserCredentials.company_code' => $company_code
            ),
            'fields' => array('CentralUserCredentials.plan_id'),
            'recursive' => -1
        ));

        $planId = !empty($data)
            ? (int)$data['CentralUserCredentials']['plan_id']
            : null;
        $user_group = $this->Session->read('user_group');

        $this->set('planId', $planId);
        $this->set('user_group', $user_group);
    }

    public function showapprovepayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
        $cur_emp_key = $this->Session->read("emp_fkey");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $plan = $this->EmployeeDetails->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $user_group = $this->Session->read('user_group');
        //edited by sinsiya
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        if ($user_group == 2) {
            $payroUser = $this->EmployeeProfessionalDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            //debug($payroUser);
            //exit;

            $this->set('payroUser', $payroUser);
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $arr_conditions = 'and branch_code!="' . $branch . '"';
            //debug($branch);
            // exit;
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), 'conditions' => array('status' => 1, 'branch_code' => $branch))));
            $this->set('arr_employees', $arr_employees);
        }

        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        //$arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array( 'order' => array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), 'conditions' => array('status' => 1, 'branch_code' => $branch) )));
        $this->set('arr_employees', $arr_employees);
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $this->CentralUserCredentials->setDataSource('controldb');

        $company_code = $this->Session->read('company_code');

        $data = $this->CentralUserCredentials->find('first', array(
            'conditions' => array(
                'CentralUserCredentials.company_code' => $company_code
            ),
            'fields' => array('CentralUserCredentials.plan_id'),
            'recursive' => -1
        ));

        $planId = !empty($data)
            ? (int)$data['CentralUserCredentials']['plan_id']
            : null;
        // debug($plan);
        // debug($planId);
        $this->set('planId', $planId);
        $this->set('user_group', $user_group);
        //  debug($planId);
        //  debug($user_group);
        //  debug($plan);
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $this->CentralUserCredentials->setDataSource('controldb');

        $company_code = $this->Session->read('company_code');

        $data = $this->CentralUserCredentials->find('first', array(
            'conditions' => array(
                'CentralUserCredentials.company_code' => $company_code
            ),
            'fields' => array('CentralUserCredentials.plan_id'),
            'recursive' => -1
        ));

        $planId = !empty($data)
            ? (int)$data['CentralUserCredentials']['plan_id']
            : null;
        // debug($plan);
        // debug($planId);
        $this->set('planId', $planId);
        $this->set('user_group', $user_group);
        //  debug($planId);
        //  debug($user_group);
        //  debug($plan);

    }

    public function FilterList()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
        $user_id = $this->Session->read("login_user_id");

        // Edited by Akshay on 5-3-2026
        $this->AttendanceRegister->query("DELETE FROM attendance_register WHERE branch_code = '$branch' AND emp_fkey = 0;");
        // $this->AttendanceRegister->query("DELETE FROM payroll_master WHERE branch_code = '$branch' AND action IS NULL AND month_year = '$month';");
        // End

        $arr_branches = $this->AttendanceRegister->query("call payroll_master_insert('$branch','$month','$user_id',@error)");

        $result = array('success' => 1);
        echo json_encode($result);
    }

    public function showprocesspayrolltab($processed = 0)
    {
        $this->set('tab', $processed);
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-07-2025
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        //end
        $user_group = $this->Session->read('user_group');
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
        $cur_emp_key = $this->Session->read("emp_fkey");
        //edited by athira on 04-07-2025
        $plan = $this->CompanyContactInfo->query("SELECT plan FROM comp_contact_info");
        $plan = $plan[0]['comp_contact_info']['plan'];
        $this->set('plan', $plan);
        // debug($plan);
        //end

        if ($user_group == 2) {
            // Edited by Athira on 31-1-2025
            $company_code = $this->Session->read('company_code');
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'DEMO')) {
                $emp_pkey = $this->Session->read("emp_fkey");
                $arr_is_ho = $this->EmployeeProfessionalDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                if ($is_ho != 1) {
                    $arr_conditions = 'and branch_code="' . $is_ho . '"';
                }
            }
            // End
            $payroUser = $this->EmployeeProfessionalDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
            $this->set('payroUser', $payroUser);
        }
        //edited by arul on 12/12/2019 Employee company id added
        //        $arr_conditions = array('status' => 1);
        //        if ($branch != '') {
        //            $arr_conditions['branch_code'] = $branch;
        //        }
        //        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => $arr_conditions)));
        //        $this->set('arr_employees', $arr_employees);

        $arr_conditions = "";
        if ($branch != '') {
            $arr_conditions = 'and branch_code="' . $branch . '"';
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $arr_conditions = 'and branch_code="' . $branch . '"';
            //debug($branch);
            // exit;
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // Edited by Akshay on 10-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');

        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $arr_conditions = 'and branch_code="' . $is_ho . '"';
            }
        }
        // End

        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions . ' order by first_name ASC');
        $this->set('arr_employees', $arr_employees);
        //end Employee company id added
    }

    public function listpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'OR' => array(
                array('Payrollmaster.action' => ''),
                array('Payrollmaster.action' => NULL),
            )
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollmaster.branch_code="' . $branches . '"'; // Edited by Akshay on 5-8-2025
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"'; // Edited by Akshay on 5-8-2025
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'Payrollmaster.emp_fkey=' . $_REQUEST['employee']; // Edited by Akshay on 5-8-2025
        }
        $company_code = $this->Session->read('company_code');
        if ($company_code == 'ABSG') {
            $arr_conditions[] = 'Payrollmaster.emp_fkey not in (select emp_fkey from site_attendance_register where month_year="' . $_REQUEST['month'] . '" and isdelete = "Y" and  branch_code="' . $branches . '")';
        } else {
            //added by megha attendance reversal condition on 30/04/2020
            $arr_conditions[] = 'Payrollmaster.emp_fkey not in (select emp_fkey from attendance_register where month_year="' . $_REQUEST['month'] . '" and isdelete = "Y" and  branch_code="' . $branches . '")'; // Edited by Akshay on 5-8-2025
            //$query = "select * from payroll_master where " . implode(' AND ', $arr_salary_conditions);
            //$payroll = $this->AttendanceRegister->query($query);
        }
        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions));

        // Edited by Akshay on 5-8-2025
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmpDetails',
                'type' => 'LEFT',
                'conditions' => array('EmpDetails.emp_pkey = Payrollmaster.emp_fkey')
            ),
            array(
                'table' => 'termination',
                'alias' => 'Termination',
                'type' => 'LEFT',
                'conditions' => array(
                    'Termination.emp_fkey = Payrollmaster.emp_fkey',
                    'Termination.status' => 1
                )
            )
        );
        // End

        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*, EmpDetails.status, Termination.submitted_date, Termination.last_approved_working_date, Termination.notice_period', // Edited by Akshay on 5-8-2025
                'joins' => $joins, // Edited by Akshay on 5-8-2025
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        foreach ($arr_payroll as $key => $value) {
            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollmaster']['loss_of_pay'];
            //$emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : 0;
            $resp_payroll["rows"][$key]['monthly_ctc'] = $value['Payrollmaster']['monthly_ctc'];
            $resp_payroll["rows"][$key]['monthly_amount'] = $value['Payrollmaster']['monthly_amount'];
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = $value['Payrollmaster']['gross_salary'];
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deductions'] = $value['Payrollmaster']['total_deduction'];

            // Edited by Akshay on 5-8-2025
            // Default resigned to false
            $resigned = false;

            // Condition 1: Status is 2 (already resigned)
            if (!empty($value['emp_details']['status']) && $value['emp_details']['status'] == 2) {
                $resigned = true;
            }
            // Condition 2: Resignation submitted and date is before or on today
            elseif (!empty($value['Termination']['submitted_date'])) {
                // $submittedDate = date('Y-m-d', strtotime($value['Termination']['submitted_date']));
                // $today = date('Y-m-d');

                // if ($submittedDate <= $today) {
                //     $resigned = true;
                // }
                $resigned = true;
            }

            $resp_payroll["rows"][$key]['resigned'] = $resigned;
            // End
        }

        //        foreach ($payroll as $key => $value) {
        //            //   $resp_register["rows"][$key] = $value["AttendanceRegister"];
        //            $resp_register["rows"][$key]['payroll_master_pkey'] = $value['payroll_master']['payroll_master_pkey'];
        //            $resp_register["rows"][$key]['emp_fkey'] = $value['payroll_master']['emp_fkey'];
        //            $resp_register["rows"][$key]['emp_name'] = $value['payroll_master']['emp_name'];
        //            $resp_register["rows"][$key]['days_present'] = $value['payroll_master']['days_presant'];
        //            $resp_register["rows"][$key]['days_leave'] = $value['payroll_master']['days_leave'];
        //            $resp_register["rows"][$key]['days_lop'] = $value['payroll_master']['loss_of_pay'];
        //
        //            $emp_fkey = isset($value['payroll_master']['emp_fkey']) ? $value['payroll_master']['emp_fkey'] : 0;
        //            $resp_register["rows"][$key]['monthly_ctc'] = $value['payroll_master']['monthly_ctc'];
        //            $resp_register["rows"][$key]['monthly_amount'] = $value['payroll_master']['monthly_amount'];
        //            $resp_register["rows"][$key]['calander_days'] = $value['payroll_master']['calander_days'];
        //            $resp_register["rows"][$key]['working_days'] = $value['payroll_master']['working_days'];
        //            $resp_register["rows"][$key]['gross_salary'] = $value['payroll_master']['gross_salary'];
        //            $resp_register["rows"][$key]['net_salary'] = $value['payroll_master']['net_salary'];
        //            $resp_register["rows"][$key]['total_deductions'] = $value['payroll_master']['total_deduction'];
        //        }

        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function listpayrollold()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $arr_salary_conditions = array();
        $conditions = array('AttendanceRegister.isdelete="N"');
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $conditions[] = 'AttendanceRegister.branch_code="' . $_REQUEST['branch'] . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $conditions[] = 'AttendanceRegister.emp_fkey=' . $_REQUEST['employee'];
            $arr_salary_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $conditions[] = 'AttendanceRegister.month_year="' . $_REQUEST['month'] . '"';
            $arr_salary_conditions[] = 'month_year="' . $_REQUEST['month'] . '"';
        } else {
            $conditions[] = 'AttendanceRegister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            )
        );

        $arr_monthly_ctc = array();
        $arr_gross_salary = array();
        $arr_total_deductions = array();

        $this->MonthlyCTC->useDbConfig = $this->Session->read('ds');
        $resp_monthly_ctc = $this->MonthlyCTC->find("all", array('fields' => 'MonthlyCTC.*', "conditions" => $arr_salary_conditions));
        foreach ($resp_monthly_ctc as $value) {
            if (isset($value['MonthlyCTC']['emp_fkey'])) {
                $arr_monthly_ctc[$value['MonthlyCTC']['emp_fkey']] = isset($value['MonthlyCTC']['ctcRate']) ? $value['MonthlyCTC']['ctcRate'] : 0;
            }
        }

        $this->MonthlyAmount->useDbConfig = $this->Session->read('ds');
        $resp_monthly_amount = $this->MonthlyAmount->find("all", array('fields' => 'MonthlyAmount.*', "conditions" => $arr_salary_conditions));
        foreach ($resp_monthly_amount as $value) {
            if (isset($value['MonthlyAmount']['emp_fkey'])) {
                $arr_monthly_amount[$value['MonthlyAmount']['emp_fkey']] = isset($value['MonthlyAmount']['ctcAmount']) ? $value['MonthlyAmount']['ctcAmount'] : 0;
            }
        }

        $this->GrossSalary->useDbConfig = $this->Session->read('ds');
        $resp_gross_salary = $this->GrossSalary->find("all", array('fields' => 'GrossSalary.*', "conditions" => $arr_salary_conditions));
        foreach ($resp_gross_salary as $value) {
            if (isset($value['GrossSalary']['emp_fkey'])) {
                $arr_gross_salary[$value['GrossSalary']['emp_fkey']] = isset($value['GrossSalary']['grossctcamount']) ? $value['GrossSalary']['grossctcamount'] : 0;
            }
        }

        $this->TotalDeductions->useDbConfig = $this->Session->read('ds');
        $resp_total_deductions = $this->TotalDeductions->find("all", array('fields' => 'TotalDeductions.*', "conditions" => $arr_salary_conditions));
        foreach ($resp_total_deductions as $value) {
            if (isset($value['TotalDeductions']['emp_fkey'])) {
                $arr_total_deductions[$value['TotalDeductions']['emp_fkey']] = isset($value['TotalDeductions']['total_deduction']) ? $value['TotalDeductions']['total_deduction'] : 0;
            }
        }

        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["AttendanceRegister"];

            //Count of present / leave / lop days
            $int_days_present = 0; //count(array_keys($value["AttendanceRegister"], "P"));
            $int_days_leave = 0; //count(array_keys($value["AttendanceRegister"], "L"));
            $int_days_lop = 0; //count(array_keys($value["AttendanceRegister"], "HO"));

            foreach ($value["AttendanceRegister"] as $key1 => $val) {
                if (strpos($key1, "FIELD") === 0) {
                    $int_days_present += (strpos($val, '/') != FALSE) ? substr_count($val, 'P') / 2 : 0;
                    $int_days_leave += substr_count($val, 'FHL') / 2 + substr_count($val, 'SHL') / 2 + substr_count($val, 'FDL');
                    $int_days_lop += (strpos($val, '/') != FALSE) ? substr_count($val, 'LOP') / 2 : substr_count($val, 'LOP');
                }
            }

            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            $resp_register["rows"][$key]['days_leave'] = $int_days_leave;
            $resp_register["rows"][$key]['days_lop'] = $int_days_lop;

            $emp_fkey = isset($value['AttendanceRegister']['emp_fkey']) ? $value['AttendanceRegister']['emp_fkey'] : 0;
            $resp_register["rows"][$key]['monthly_ctc'] = isset($arr_monthly_ctc[$emp_fkey]) ? $arr_monthly_ctc[$emp_fkey] : 0;
            $resp_register["rows"][$key]['monthly_amount'] = isset($arr_monthly_amount[$emp_fkey]) ? $arr_monthly_amount[$emp_fkey] : 0;
            $resp_register["rows"][$key]['gross_salary'] = isset($arr_gross_salary[$emp_fkey]) ? $arr_gross_salary[$emp_fkey] : 0;
            $resp_register["rows"][$key]['net_salary'] = 0;
            $resp_register["rows"][$key]['total_deductions'] = isset($arr_total_deductions[$emp_fkey]) ? $arr_total_deductions[$emp_fkey] : 0;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function listprocessedpayrollold()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $conditions = array('AttendanceRegister.isdelete="N"');
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $conditions[] = 'AttendanceRegister.branch_code="' . $_REQUEST['branch'] . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $conditions[] = 'AttendanceRegister.emp_fkey=' . $_REQUEST['employee'];
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $conditions[] = 'AttendanceRegister.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $conditions[] = 'AttendanceRegister.month_year="' . date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        $fields = 'AttendanceRegister.*';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            )
        );

        $resp_register = array();
        $resp_register["rows"] = array();
        $count = $this->AttendanceRegister->find("count", array("conditions" => $conditions));
        $arr_register = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_register as $key => $value) {
            $resp_register["rows"][$key] = $value["AttendanceRegister"];

            //Count of present / leave / lop days
            $int_days_present = 0; //count(array_keys($value["AttendanceRegister"], "P"));
            $int_days_leave = 0; //count(array_keys($value["AttendanceRegister"], "L"));
            $int_days_lop = 0; //count(array_keys($value["AttendanceRegister"], "HO"));

            foreach ($value["AttendanceRegister"] as $key1 => $val) {
                if (strpos($key1, "FIELD") === 0) {
                    $int_days_present += (strpos($val, '/') != FALSE) ? substr_count($val, 'P') / 2 : 0;
                    $int_days_leave += substr_count($val, 'FHL') / 2 + substr_count($val, 'SHL') / 2 + substr_count($val, 'FDL');
                    $int_days_lop += (strpos($val, '/') != FALSE) ? substr_count($val, 'LOP') / 2 : substr_count($val, 'LOP');
                }
            }

            $resp_register["rows"][$key]['days_present'] = $int_days_present;
            $resp_register["rows"][$key]['days_leave'] = $int_days_leave;
            $resp_register["rows"][$key]['days_lop'] = $int_days_lop;

            $resp_register["rows"][$key]['monthly_ctc'] = 0;
            $resp_register["rows"][$key]['gross_salary'] = 0;
            $resp_register["rows"][$key]['net_salary'] = 0;
        }
        $resp_register["total"] = $count;
        echo json_encode($resp_register);
    }

    public function listprocessedpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            "NOT" => array(
                "Payrollmaster.action" => '',
                "Payrollmaster.action" => NULL
            )
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollmaster.branch_code="' . $branches . '"'; // Edited by Akshay on 5-8-2025
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"'; // Edited by Akshay on 5-8-2025
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'Payrollmaster.emp_fkey=' . $_REQUEST['employee']; // Edited by Akshay on 5-8-2025
        }

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions));

        // Edited by Akshay on 5-8-2025
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmpDetails',
                'type' => 'LEFT',
                'conditions' => array('EmpDetails.emp_pkey = Payrollmaster.emp_fkey')
            ),
            array(
                'table' => 'termination',
                'alias' => 'Termination',
                'type' => 'LEFT',
                'conditions' => array(
                    'Termination.emp_fkey = Payrollmaster.emp_fkey',
                    'Termination.status' => 1
                )
            )
        );
        // End

        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*, EmpDetails.status, Termination.submitted_date, Termination.last_approved_working_date, Termination.notice_period', // Edited by Akshay on 5-8-2025
                'joins' => $joins, // Edited by Akshay on 5-8-2025
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        $arr_conditions_prev = array(
            "NOT" => array(
                "Payrollmaster.action" => '',
                "Payrollmaster.action" => NULL
            )
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions_prev[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $prevmonth = date('Y-m', strtotime('-1 month', strtotime($_REQUEST['month'])));
            $arr_conditions_prev[] = 'month_year="' . $prevmonth . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions_prev[] = 'emp_fkey=' . $_REQUEST['employee'];
        }
        $prevsalary = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.emp_fkey,Payrollmaster.net_salary',
                'conditions' => $arr_conditions_prev,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        $arr_prev_month_net_salary = array();
        foreach ($prevsalary as $key => $value) {
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if ($emp_fkey != '') {
                $arr_prev_month_net_salary[$emp_fkey] = isset($value['Payrollmaster']['net_salary']) ? $value['Payrollmaster']['net_salary'] : '';
            }
        }
        foreach ($arr_payroll as $key => $value) {

            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollmaster']['loss_of_pay'];
            //$emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : 0;
            $resp_payroll["rows"][$key]['monthly_ctc'] = $value['Payrollmaster']['monthly_ctc'];
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deduction'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollmaster']['action'];
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = round($arr_prev_month_net_salary[$emp_fkey]);
                $resp_payroll["rows"][$key]['diff'] =  round($value['Payrollmaster']['net_salary']) - round($arr_prev_month_net_salary[$emp_fkey]);
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
                $resp_payroll["rows"][$key]['diff'] = '';
            }

            // Edited by Akshay on 5-8-2025
            // Default resigned to false
            $resigned = false;

            // Condition 1: Status is 2 (already resigned)
            if (!empty($value['emp_details']['status']) && $value['emp_details']['status'] == 2) {
                $resigned = true;
            }
            // Condition 2: Resignation submitted and date is before or on today
            elseif (!empty($value['Termination']['submitted_date'])) {
                // $submittedDate = date('Y-m-d', strtotime($value['Termination']['submitted_date']));
                // $today = date('Y-m-d');

                // if ($submittedDate <= $today) {
                //     $resigned = true;
                // }
                $resigned = true;
            }

            $resp_payroll["rows"][$key]['resigned'] = $resigned;
            // End
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }
    public function processpayroll()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds'); // Edited by Akshay on 26-2-2026
        $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 7-8-2025
        $success = 0;
        $arr_requestdata = $this->request->data;

        // Edited by Akshay on 11-2-2026
        // $specialCompanies = [
        //     'ABSG',
        //     'VGFS',
        //     'VSFS',
        //     'DRRC',
        //     'DJIC',
        //     'AGNG',
        //     'AYRK',
        //     'SHRD',
        //     'SNRY',
        //     'GTRA',
        //     'SHYD',
        //     'VGNN',
        //     'SRTS',
        //     'MPCP'
        // ];

        $specialCompanies = ['HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN','ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','SHYD','GTRA'];

        // End

        //   debug($arr_requestdata);
        if (isset($arr_requestdata["emp_pkey"])) {
            $arr_emp_pkeys = isset($arr_requestdata["emp_pkey"]) ? explode(",", $arr_requestdata["emp_pkey"]) : array();
            $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
            $count = count($arr_payroll_pkeys);

            $arr_data['emp'] = $arr_emp_pkeys;
            $arr_data1['payroll'] = $arr_payroll_pkeys;
            //---- Include tax - update the tax field = Y in table --- Added By Nimisha 16/03/2019     
            if ($arr_requestdata['tax'] == '1') {
                //                $paydata = array();
                foreach ($arr_data1['payroll'] as $value) {
                    //                    var_dump($value);
                    //                    debug($value);
                    //                    debug("UPDATE payroll_master SET tax_include = 'Y' WHERE payroll_master.payroll_master_pkey = '$value'");
                    //                    $paydata = array("tax_include" => "'Y'");
                    $this->Payrollmaster->query("UPDATE payroll_master SET tax_include = 'Y' WHERE payroll_master.payroll_master_pkey = '$value'");
                    //                  var_dump("UPDATE Payrollmaster SET tax_include = 'Y' WHERE Payrollmaster.payroll_master_pkey' => $value");

                    //                            updateAll(
                    //                         $paydata, array('Payrollmaster.payroll_master_pkey' => $value)
                    //                  );
                }
            }
            if ($arr_requestdata['tax'] == '0') {
                foreach ($arr_data1['payroll'] as $value) {
                    $this->Payrollmaster->query("UPDATE payroll_master SET tax_include = 'N' WHERE payroll_master.payroll_master_pkey = '$value'");
                }
            }
            //--End of update tax----
            //
            //  debug($arr_data);
            //$i=0;

            for ($i = 0; $i < $count; $i++) {
                //echo $i;
                //Call procedure for each employee
                //    debug($arr_data1['payroll'][$i]);
                //	debug($arr_data['emp'][$i]);
                $outputParameter = array();
                $outputParameter[] = (isset($arr_requestdata['month']) && $arr_requestdata['month'] != '') ? $arr_requestdata['month'] : '';
                $outputParameter[] = (isset($arr_requestdata['branch']) && $arr_requestdata['branch'] != '') ? $arr_requestdata['branch'] : '';
                $outputParameter[] = $emp_pkey = $arr_data['emp'][$i]; // Edited by Akshay on 19-2-2026
                $outputParameter[] = $payroll_master_pkey = $arr_data1['payroll'][$i];
                $outputParameter[] = $this->Session->read("login_user_id"); //user id
                // debug($outputParameter); exit;
                try {

                    // Edited by Akshay on 22-1-2026
                    if (!in_array($company_code, $specialCompanies)) {
                        $this->AttendanceRegister->calculateSalaryMainPrc($outputParameter); // Edited by Akshay on 19-12-2025
                    } else {
                        $out = $this->AttendanceRegister->salaryProcessPrc($outputParameter);
                    }
                    // End

                    // Edited by Akshay on 19-2-2026
                    $arr_designation = $this->AttendanceRegister->query("SELECT desig_name FROM emp_proff LEFT JOIN designation ON emp_proff.designation = designation.desig_code WHERE emp_proff.emp_fkey = '$emp_pkey';");
                    $designation = isset($arr_designation[0]['designation']['desig_name']) ? $arr_designation[0]['designation']['desig_name'] : '';

                    $schema = $this->Payrollmaster->schema();
                    if (isset($schema['desig'])) {
                        $this->Payrollmaster->updateAll(
                            array('Payrollmaster.desig' => "'" . addslashes($designation) . "'"),
                            array('Payrollmaster.payroll_master_pkey' => $payroll_master_pkey)
                        );
                    }
                    // End
                } catch (Exception $e) {
                    debug($e);
                }
                //debug($out);exit;
                //Update salary_amount after procedure executed on emp_salary_slip
                //Fetch formula from remarks

                // $arr_formulae_from_remarks = $this->EmpSalarySlip->find(
                //     "all",
                //     array(
                //         'fields' => 'emp_salary_slip_pkey,head_operator,remarks,salary_head_item_desc',
                //         'conditions' => array(
                //             'payroll_master_fkey' => $arr_data1['payroll'][$i],
                //             'remarks IS NOT NULL',
                //             'end_date_effective is null'
                //         )
                //     )
                // );

                // Edited by Akshay on 11-2-2026
                if (in_array($company_code, $specialCompanies)) {
                    $arr_formulae_from_remarks = $this->EmpSalarySlip->find(
                        "all",
                        array(
                            // 'fields' => array('emp_salary_slip_pkey', 'head_operator', 'remarks', 'salary_head_item_desc'),
                            'conditions' => array(
                                'EmpSalarySlip.payroll_master_fkey' => $arr_data1['payroll'][$i],
                                'EmpSalarySlip.remarks IS NOT NULL',
                                'EmpSalarySlip.end_date_effective IS NULL',
                                'NOT' => array(
                                    'EmpSalarySlip.salary_head_item_desc' => 'Overtime Allowance(OT)',
                                    'EmpSalarySlip.head_type' => 'Arrear'
                                )  //edited by sinsiya on 04-07-2025
                                // Edited by Akshay on 26-7-2025 arrear
                            )
                        )
                    );
                    // debug($arr_formulae_from_remarks);

                    foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                        $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey']) ? $row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey'] : '';
                        $head_operator = isset($row_formulae_from_remarks['EmpSalarySlip']['head_operator']) ? $row_formulae_from_remarks['EmpSalarySlip']['head_operator'] : '';
                        $formula_from_remarks = isset($row_formulae_from_remarks['EmpSalarySlip']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmpSalarySlip']['remarks']) : '';
                        $salary_head_item_desc = isset($row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc'] : '';

                        if (!empty($formula_from_remarks)) {
                            //Edited by Akshay on 19-4-2024
                            // $pattern = '/^[\d\+\-\*\/\(\)\s]+$/';
                            // if(preg_match($pattern, $formula_from_remarks)){ //edited by sinsiya on 04-07-2025
                            if (!is_numeric($formula_from_remarks)) {
                                // Edited by Akshay on 28-8-2025
                                if (preg_match('/^[0-9\+\-\*\/\(\)\. ]+$/', $formula_from_remarks)) {
                                    // safe: only arithmetic expression
                                    eval('$salary_amount = ' . $formula_from_remarks . ';');
                                } else {
                                    // contains letters or other symbols -> not arithmetic
                                    $salary_amount = 0;
                                }
                                // End
                            } else {
                                $salary_amount = 0;
                            }
                            // }
                            //if (trim(strtolower($salary_head_item_desc)) == 'epf - employee contribution'){ 


                            if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'ESI - Employer Contribution' || trim(strtolower($salary_head_item_desc)) == 'ESI - Employee Contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {

                                // if ($company_code != 'GEDE') // Edited by Akshay on 7-8-2025
                                if ($head_operator == 'Deduction') {
                                    $salary_amount = ceil($salary_amount); //Edited by Akshay on 30-4-2024
                                } else {
                                    $salary_amount = round($salary_amount); //Edited by Akshay on 30-4-2024
                                }

                                //if (trim(strtolower($salary_head_item_desc)) =='esi' || trim(strtolower($salary_head_item_desc)) =='esi - employee contribution' || trim(strtolower($salary_head_item_desc)) =='esi - employer contribution'){         
                                if ($head_operator == 'Deduction') {
                                    $salary_amount *= -1;
                                }
                                // }
                                // }

                                if (preg_match('/^[0-9\+\-\*\/\(\)\. ]+$/', $formula_from_remarks)) // Edited by  Akshay on 28-8-2025
                                    $arr_emp_salary_slip_data = array(
                                        // 'EmpSalarySlip.structure_det_value' => $salary_amount,
                                        'EmpSalarySlip.salary_rate' => $salary_amount,
                                        'EmpSalarySlip.salary_amount' => $salary_amount
                                    );
                            } else {
                                // if ($company_code != 'GEDE') // Edited by Akshay on 7-8-2025
                                $salary_amount = round($salary_amount);
                                if ($head_operator == 'Deduction') {
                                    $salary_amount *= -1;
                                }
                                //edited by sinsiya on 04-07-2025
                                if (preg_match('/^[0-9\+\-\*\/\(\)\. ]+$/', $formula_from_remarks)) // Edited by  Akshay on 28-8-2025
                                    if ($salary_head_item_desc != "Overtime Allowance(OT)") {
                                        $arr_emp_salary_slip_data = array(
                                            // 'EmpSalarySlip.remarks' => 'NULL', 
                                            // 'EmpSalarySlip.structure_det_value' => 0,
                                            'EmpSalarySlip.salary_rate' => 0,
                                            'EmpSalarySlip.salary_amount' => $salary_amount
                                        );
                                    } else {
                                        $arr_emp_salary_slip_data = array(
                                            'EmpSalarySlip.remarks' => 'NULL',
                                            'EmpSalarySlip.structure_det_value' => 0,
                                            'EmpSalarySlip.salary_rate' => 0,
                                            'EmpSalarySlip.salary_amount' => $salary_amount
                                        );
                                    }
                            }

                            if (isset($arr_emp_salary_slip_data) && !empty($arr_emp_salary_slip_data)) // Edited by Akshay on 23-1-2026
                                $this->EmpSalarySlip->updateAll(
                                    $arr_emp_salary_slip_data,
                                    array('EmpSalarySlip.emp_salary_slip_pkey' => $emp_salary_slip_pkey)
                                );
                        }
                    }
                    $this->Payrollmaster->useDbConfig = $this->Session->read('ds');


                    $out = $this->Payrollmaster->taxSalaryProcessPrc($outputParameter);
                }
                // End
                //Ends

             
                // Edited by Akshay on 2-9-2025
                $arr_formulae_from_remarks = $this->EmpSalarySlip->find(
                    "all",
                    array(
                        'conditions' => array(
                            'EmpSalarySlip.payroll_master_fkey' => $arr_data1['payroll'][$i],
                            'EmpSalarySlip.remarks IS NOT NULL',
                            'EmpSalarySlip.end_date_effective IS NULL',
                            'NOT' => array('EmpSalarySlip.head_type' => 'Arrear')
                        )
                    )
                );

                foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                    $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey']) ? $row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey'] : '';
                    $head_operator = isset($row_formulae_from_remarks['EmpSalarySlip']['head_operator']) ? $row_formulae_from_remarks['EmpSalarySlip']['head_operator'] : '';
                    $formula_from_remarks = isset($row_formulae_from_remarks['EmpSalarySlip']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmpSalarySlip']['remarks']) : '';
                    $salary_head_item_desc = isset($row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc'] : '';
                    $arr_emp_salary_slip_data = array();

                    if (!empty($formula_from_remarks)) {
                        $salary_head_item = isset($row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_fkey']) ? $row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_fkey'] : '';
                        $emp_fkey = isset($row_formulae_from_remarks['EmpSalarySlip']['emp_fkey']) ? $row_formulae_from_remarks['EmpSalarySlip']['emp_fkey'] : '';
                        $from = isset($row_formulae_from_remarks['EmpSalarySlip']['month_year']) ? $row_formulae_from_remarks['EmpSalarySlip']['month_year'] : '';

                        // Get formula from structure
                        $formula_result = $this->EmpSalarySlip->query("
                                                                            SELECT structure_formula, structure_det_calequation 
                                                                            FROM salary_structure_details 
                                                                            WHERE salary_head_item_fkey = $salary_head_item 
                                                                            AND structure_id = (
                                                                                SELECT DISTINCT emp_structure_id 
                                                                                FROM emp_salary_structure 
                                                                                WHERE emp_fkey = '$emp_fkey' 
                                                                                AND end_date_effective IS NULL
                                                                            )
                                                                        ");

                        $formula_string = '';
                        if (!empty($formula_result) && isset($formula_result[0]['salary_structure_details']['structure_det_calequation'])) {
                            $formula_string = $formula_result[0]['salary_structure_details']['structure_det_calequation'];
                            $structure_formula = $formula_result[0]['salary_structure_details']['structure_formula'];
                        }

                        // Edited by Akshay on 1-10-2025
                        $limit_val = isset($formula_result[0]['salary_structure_details']['structure_det_depends']) ? $formula_result[0]['salary_structure_details']['structure_det_depends'] : 0;
                        $limit_type = isset($formula_result[0]['salary_structure_details']['structure_det_operator']) ? $formula_result[0]['salary_structure_details']['structure_det_operator'] : '';
                        // End

                        // 2. Parse and calculate salary
                        $replaced_formula_string = ''; // <- This will store replaced formula like "15000 + 2000"

                        if (!empty($formula_string)) {
                            $replaced_formula_string = $formula_string;

                            // Match all full item names (letters, spaces, and optional aliases in parentheses)
                            preg_match_all('/\d+_[A-Za-z_]+|monthsal/', $formula_string, $matches);
                            $tokens = array_unique(array_map('trim', $matches[0]));

                            foreach ($tokens as $token) {
                                if ($token === '') continue;

                                $amount = 0;

                                if (strtolower($token) === 'monthsal') {
                                    $gross_result = $this->EmpSalarySlip->query("
                                                                                    SELECT SUM(salary_amount) AS gross
                                                                                    FROM emp_salary_slip
                                                                                    WHERE emp_fkey = '$emp_fkey'
                                                                                    AND end_date_effective IS NULL
                                                                                    AND month_year = '$from'
                                                                                    AND head_operator = 'Addition'
                                                                                    AND item_part = 'Direct'
                                                                                ");
                                    if (!empty($gross_result) && isset($gross_result[0][0]['gross'])) {
                                        $amount = (float)$gross_result[0][0]['gross'];
                                    }
                                } else {
                                    $parts = explode("_", $token);
                                    $item_pkey = is_numeric($parts[0]) ? (int)$parts[0] : 0;


                                    if ($item_pkey != 0) {
                                        $item_val_result = $this->EmpSalarySlip->query("
                                                                                            SELECT salary_amount 
                                                                                            FROM emp_salary_slip 
                                                                                            WHERE emp_fkey = '$emp_fkey'
                                                                                            AND salary_head_item_fkey = $item_pkey
                                                                                            AND month_year = '$from'
                                                                                            AND end_date_effective IS NULL
                                                                                            LIMIT 1
                                                                                        ");
                                        if (!empty($item_val_result) && isset($item_val_result[0]['emp_salary_slip']['salary_amount'])) {
                                            $amount = (float)$item_val_result[0]['emp_salary_slip']['salary_amount'];
                                        }
                                    }
                                }

                                // Replace the exact token (even with parentheses)
                                $replaced_formula_string = str_replace($token, $amount, $replaced_formula_string);
                            }

                            // Save the final replaced formula in the array
                            $columns = $this->EmpSalarySlip->getDataSource()->query("
                                                SHOW COLUMNS FROM emp_salary_slip LIKE 'remarks_2'
                                            ");
                            $hasRemarks2 = !empty($columns); // true if remarks_2 exists

                            if ($hasRemarks2) {
                                // Edited by  Akshay on 28-8-2025
                                if (!preg_match('/^[0-9\+\-\*\/\(\)\. ]+$/', $formula_from_remarks)) {
                                    $arr_emp_salary_slip_data = array();
                                }
                                $arr_emp_salary_slip_data['EmpSalarySlip.remarks_2'] = "'" . $replaced_formula_string . "'";
                                $arr_emp_salary_slip_data['EmpSalarySlip.formula'] = isset($structure_formula)
                                    ? "'" . $structure_formula . "'"
                                    : 'NULL';
                                if (!empty($replaced_formula_string)) {

                                    $lastAsteriskPos = strrpos($replaced_formula_string, '*'); // Step 1: Find the position of the last '*'
                                    if ($lastAsteriskPos !== false) {
                                        $sum_part = trim(substr($replaced_formula_string, 0, $lastAsteriskPos)); // Keep everything before the last '*'
                                        $multiplier_part = trim(substr($replaced_formula_string, $lastAsteriskPos + 1)); // after last * // Edited by Akshay on 1-10-2025
                                        $multiplier_part = str_replace(' ', '', $multiplier_part);
                                    } else {
                                        $sum_part = trim($replaced_formula_string); // No '*' found, keep whole formula
                                    }

                                    // Step 2: Remove all spaces
                                    $sum_part = str_replace(' ', '', $sum_part); // (15000+2000+3000)

                                    // Step 3: Validate numeric expression (only digits, +, -, (, ))
                                    if (preg_match('/^[0-9+\-().]+$/', $sum_part)) { // Edited by Akshay on 1-10-2025
                                        // Safely evaluate using math only
                                        try {
                                            // Eval may throw errors if something is wrong
                                            $combined_base_value = @eval("return $sum_part;");
                                            if (!is_numeric($combined_base_value)) {
                                                $combined_base_value = null; // not suitable for arithmetic
                                            }
                                        } catch (\Throwable $e) {
                                            $combined_base_value = null; // in case eval fails
                                        }

                                        // Edited by Akshay on 1-10-2025
                                        if (isset($multiplier_part) && preg_match('#^[0-9+\-*/().\s]+$#', $multiplier_part)) {
                                            if ($limit_type == 'limit_wl' || $limit_type == 'limit_wg') {
                                                $multiplier_part = ($tmp = @eval("return " . str_replace(' ', '', $multiplier_part) . ";")) && is_numeric($tmp) && $tmp != 0 ? $tmp : 1;
                                                $limit_val = $limit_val / $multiplier_part;
                                                $combined_base_value = ($limit_type == 'limit_wg') ? max($combined_base_value, $limit_val) : min($combined_base_value, $limit_val);
                                            }
                                        }
                                        // End

                                        $arr_emp_salary_slip_data['EmpSalarySlip.combined_base_value'] = "'" . $combined_base_value . "'";
                                    }
                                }
                                // End
                            }
                        }


                        
                        // End
                        try {
                            if(!empty($arr_emp_salary_slip_data))
                            $this->EmpSalarySlip->updateAll(
                                $arr_emp_salary_slip_data,
                                array('EmpSalarySlip.emp_salary_slip_pkey' => $emp_salary_slip_pkey)
                            );
                        } catch (Exception $e) {
                            debug($e);
                        }
                    }
                }
                // End

                $success = 1;
            }
        }



        $result = array('success' => $success);
        echo json_encode($result);
    }


    public function holdProcessPayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

        $success = 0;
        $arr_requestdata = $this->request->data;
        if (isset($arr_requestdata["payroll_pkey"])) {
            $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
            foreach ($arr_payroll_pkeys as $payrol_pkey) {
                //Call procedure for each employee
                $arr_form_data = array();
                $arr_form_data['payroll_master_pkey'] = $payrol_pkey;
                $arr_form_data['action'] = 'Hold';
                $this->Payrollmaster->save($arr_form_data);
                $success = 1;
            }
        }
        $result = array('success' => $success);
        echo json_encode($result);
    }

    public function removePayrollEntry()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 3-3-2026
        try {
            $success = 1;
            $arr_requestdata = $this->request->data;
            if (isset($arr_requestdata["payroll_pkey"])) {
                $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
                //commented by megha on 31/08/2019 delete action not needed 
                //$this->Payrollmaster->deleteAll(array('Payrollmaster.payroll_master_pkey' => $arr_payroll_pkeys), false);
                $this->Payrollmaster->updateAll(
                    array(
                        'action' => Null
                    ),
                    array(
                        'payroll_master_pkey' => $arr_payroll_pkeys
                    )
                );
                $date = date('Y-m-d');
                $arr_payroll_pkeys_list = $arr_requestdata['payroll_pkey'];
                $uid = $this->Session->read("login_user_id");
                $str = substr($arr_payroll_pkeys_list, -1);
                if ($str == ',') {
                    $arr_payroll_pkeys_list = substr($arr_payroll_pkeys_list, 0, -1);
                }
                //  date saving error edited by megha on 16/09/2019
                $arr_payroll = $this->EmpSalarySlip->query("UPDATE emp_salary_slip  SET end_date_effective = '$date',modified_by = '$uid' WHERE payroll_master_fkey in ($arr_payroll_pkeys_list) and end_date_effective is null");

                // Edited by Akshay on 3-3-2026
                // $specialCompanies = [
                //     'ABSG',
                //     'VGFS',
                //     'VSFS',
                //     'DRRC',
                //     'DJIC',
                //     'AGNG',
                //     'AYRK',
                //     'SHRD',
                //     'RRLC',
                //     'SNRY',
                //     'GTRA',
                //     'SHYD'
                // ];

                $specialCompanies = ['HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN','ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','SHYD','GTRA']; // Edited by Akshay on 23-3-2026
                

                if (!in_array($company_code, $specialCompanies)) {
                    $arr_list = $this->EmpSalarySlip->query("select emp_fkey,month_year from payroll_master where payroll_master_pkey in ($arr_payroll_pkeys_list)");
                    foreach ($arr_list as $data) {
                        $emp_pkey = $data['payroll_master']['emp_fkey'];
                        $month_year = $data['payroll_master']['month_year'];

                        $this->EmpSalarySlip->query("update emp_variables_upload set status = 0 
                                                    where emp_fkey = $emp_pkey 
                                                    and month_year = date_format(concat('$month_year', '-01'), '%m-%Y') 
                                                    and status = 1 and lcase(head_type) = 'fixed';");
                    }
                }
                // End
            }
            $result = array('success' => $success);
            echo json_encode($result);
        } catch (Exception $ex) {
            $message = 'Something went wrong. Please Try again!!';
            return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'message' => $message));
        }
    }

    public function showsalaryslip($payroll_master_pkey = 0)
    {
        $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds');

        $company_code = strtoupper($this->Session->read('company_code')); // Edited by Akshay on 30-7-2025

        $fields = 'EmpSalarySlip.head_type,EmpSalarySlip.structure_det_value,EmpSalarySlip.salary_head_item_desc,EmpSalarySlip.salary_rate,EmpSalarySlip.salary_amount,Payrollmaster.emp_name,SalaryHeads.head_pkey,SalaryHeads.head_desc, EmpSalarySlip.head_operator'; //Edited by Akshay on 2-12-2024
        $arr_joins = array(
            array(
                'table' => 'payroll_master',
                'alias' => 'Payrollmaster',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmpSalarySlip.payroll_master_fkey = Payrollmaster.payroll_master_pkey'
                )
            ),
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmpSalarySlip.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey'
                )
            ),
            array(
                'table' => 'salary_heads',
                'alias' => 'SalaryHeads',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'SalaryHeadItems.head_fkey = SalaryHeads.head_pkey'
                )
            )
        );
        $arr_conditions = array(
            'EmpSalarySlip.payroll_master_fkey' => $payroll_master_pkey,
            "end_date_effective is null",
            "EmpSalarySlip.item_part not in('Indirect')"
        );

        $order = array(); // default order

        if ($company_code == 'KWMT') {
            $order = array(
                'SalaryHeads.head_pkey ASC',
                'EmpSalarySlip.salary_head_item_fkey ASC'
            );
        }

        // Edited by Akshay on 30-7-2025
        $arr_result = $this->EmpSalarySlip->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $arr_joins,
                'conditions' => $arr_conditions,
                'order' => $order // dynamic order applied here
            )
        );
        // End

        $arr_conditions1 = array(
            'EmpSalarySlip.payroll_master_fkey' => $payroll_master_pkey,
            "end_date_effective is null",
            "EmpSalarySlip.item_part in('Indirect')"
        );
        $arr_indirect = $this->EmpSalarySlip->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $arr_joins,
                'conditions' => $arr_conditions1
            )
        );
        $this->set('arr_indirect', $arr_indirect);

        $arr_empsalaryslip = array();
        $head = '';
        foreach ($arr_result as $value) {
            $head = isset($value['Payrollmaster']['emp_name']) ? $value['Payrollmaster']['emp_name'] . "'s salary as follows" : '';
            $head_pkey = isset($value['SalaryHeads']['head_pkey']) ? $value['SalaryHeads']['head_pkey'] : '';
            if (!isset($arr_empsalaryslip[$head_pkey])) {
                $head_desc = isset($value['SalaryHeads']['head_desc']) ? $value['SalaryHeads']['head_desc'] : '';
                $head_pkey = isset($value['SalaryHeads']['head_pkey']) ? $value['SalaryHeads']['head_pkey'] : '';
                $arr_empsalaryslip[$head_pkey] = array(
                    'head_pkey' => $head_pkey,
                    'head_desc' => $head_desc,
                    'items' => array()
                );
            }
            if (!empty($value['EmpSalarySlip'])) {
                $arr_empsalaryslip[$head_pkey]['items'][] = $value['EmpSalarySlip'];
            }
        }
        //debug($arr_empsalaryslip);
        $this->set('head', $head);
        $this->set('arr_empsalaryslip', $arr_empsalaryslip);
    }


    public function showapprovepayrolltab($approved = 0)
    {
        //edited by athira on 07-02-2025
        // $this->Menu->useDbConfig = $this->Session->read('ds');
        // $plan=$this->Menu->query('SELECT plan FROM comp_contact_info');
        // $plan=$plan['0']['comp_contact_info']['plan'];
        // $this->set('plan',$plan);
        //end
        $this->set('tab', $approved);
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            // Edited by Athira on 31-1-2025
            $company_code = $this->Session->read('company_code');
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'DEMO')) {
                // $this->set('payroUser', $payroUser);
                $emp_pkey = $this->Session->read("emp_fkey");
                $arr_is_ho = $this->EmployeeProfessionalDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                if ($is_ho != 1) {
                    $arr_conditions = 'and branch_code="' . $is_ho . '"';
                }
            } else {
                $payroUser = $this->EmployeeProfessionalDetails->query("select emp_proff.payro_priv,emp_proff.emp_branch,branches.branch_name from emp_proff JOIN branches ON emp_proff.emp_branch = branches.branch_code where emp_proff.emp_fkey ='$cur_emp_key'");
                $this->set('payroUser', $payroUser);
            }
            // End
        }
        //Edited by Akshay on 19-11-2024
        $company_code = strtoupper($this->Session->read('company_code'));
        $this->set('company_code', $company_code);
        //End

        //edited by arul on 12/12/2019 Employee company id added
        //        $arr_conditions = array('status' => 1);
        //        if ($branch != '') {
        //            $arr_conditions['branch_code'] = $branch;
        //        }
        //        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => $arr_conditions)));
        //        $this->set('arr_employees', $arr_employees);
        $arr_conditions = "";
        if ($branch != '') {
            $arr_conditions = 'and branch_code="' . $branch . '"';
        }
        if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1') {
            $branch = $payroUser[0]['emp_proff']['emp_branch'];
            $arr_conditions = 'and branch_code="' . $branch . '"';
            //debug($branch);
            // exit;
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
                $arr_conditions = 'and branch_code="' . $is_ho . '"';
            }
        }
        // End

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions . ' order by first_name ASC');
        $this->set('arr_employees', $arr_employees);
        // debug($arr_employees);
        //end Employee company id added
    }

    public function listapprovepayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'OR' => array(
                array('Payrollmaster.action' => 'Processed'),
                array('Payrollmaster.action' => 'Verified'),
            )
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollmaster.branch_code="' . $branches . '"'; // Edited by Akshay on 5-8-2025
            $arr_prev_month_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"'; // Edited by Akshay on 5-8-2025

            $prevmonth = date('Y-m', strtotime('-1 month', strtotime($_REQUEST['month'])));
            $arr_prev_month_conditions[] = 'month_year="' . $prevmonth . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'Payrollmaster.emp_fkey=' . $_REQUEST['employee']; // Edited by Akshay on 5-8-2025
            $arr_prev_month_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions));


        // Edited by Akshay on 5-8-2025
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmpDetails',
                'type' => 'LEFT',
                'conditions' => array('EmpDetails.emp_pkey = Payrollmaster.emp_fkey')
            ),
            array(
                'table' => 'termination',
                'alias' => 'Termination',
                'type' => 'LEFT',
                'conditions' => array(
                    'Termination.emp_fkey = Payrollmaster.emp_fkey',
                    'Termination.status' => 1
                )
            )
        );
        // End

        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*, EmpDetails.status, Termination.submitted_date, Termination.last_approved_working_date, Termination.notice_period', // Edited by Akshay on 5-8-2025
                'joins' => $joins, // Edited by Akshay on 5-8-2025
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );

        //Fetch last month net salary
        $arr_prev_month_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.emp_fkey,Payrollmaster.net_salary',
                'conditions' => $arr_prev_month_conditions,
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        $arr_prev_month_net_salary = array();
        foreach ($arr_prev_month_payroll as $key => $value) {
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if ($emp_fkey != '') {
                $arr_prev_month_net_salary[$emp_fkey] = isset($value['Payrollmaster']['net_salary']) ? $value['Payrollmaster']['net_salary'] : '';
            }
        }

        foreach ($arr_payroll as $key => $value) {
            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollmaster']['loss_of_pay'];
            //$emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : 0;
            $resp_payroll["rows"][$key]['monthly_ctc'] = $value['Payrollmaster']['monthly_ctc'];
            $resp_payroll["rows"][$key]['monthly_amount'] = $value['Payrollmaster']['monthly_amount'];
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = $value['Payrollmaster']['gross_salary'];
            $resp_payroll["rows"][$key]['net_salary'] = $value['Payrollmaster']['net_salary'];
            $resp_payroll["rows"][$key]['total_deduction'] = $value['Payrollmaster']['total_deduction'];

            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = $arr_prev_month_net_salary[$emp_fkey];
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
            }

            // Edited by Akshay on 5-8-2025
            // Default resigned to false
            $resigned = false;

            // Condition 1: Status is 2 (already resigned)
            if (!empty($value['emp_details']['status']) && $value['emp_details']['status'] == 2) {
                $resigned = true;
            }
            // Condition 2: Resignation submitted and date is before or on today
            elseif (!empty($value['Termination']['submitted_date'])) {
                // $submittedDate = date('Y-m-d', strtotime($value['Termination']['submitted_date']));
                // $today = date('Y-m-d');

                // if ($submittedDate <= $today) {
                //     $resigned = true;
                // }
                $resigned = true;
            }

            $resp_payroll["rows"][$key]['resigned'] = $resigned;
            // End
        }

        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function listapprovedpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code')); // Edited by Akshay on 15-7-2025

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            "Payrollmaster.action" => 'Approved'
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollmaster.branch_code="' . $branches . '"'; // Edited by Akshay on 15-7-2025
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'month_year="' . $_REQUEST['month'] . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions));

        // Edited by Akshay on 15-7-2025
        $fields = array('Payrollmaster.*');
        $joins = array();

        if ($company_code == 'GLET' || $company_code == 'SHYD') {
            $fields[] = 'EmployeeDetails.email';

            $joins[] = array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'conditions' => array(
                    'Payrollmaster.emp_fkey = EmployeeDetails.emp_pkey'
                )
            );
        }
        // End

        // Edited by Akshay on 5-8-2025
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmpDetails',
                'type' => 'LEFT',
                'conditions' => array('EmpDetails.emp_pkey = Payrollmaster.emp_fkey')
            ),
            array(
                'table' => 'termination',
                'alias' => 'Termination',
                'type' => 'LEFT',
                'conditions' => array(
                    'Termination.emp_fkey = Payrollmaster.emp_fkey',
                    'Termination.status' => 1
                )
            )
        );
        // End

        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*, EmpDetails.status, EmpDetails.email, Termination.submitted_date, Termination.last_approved_working_date, Termination.notice_period', // Edited by Akshay on 5-8-2025
                'joins' => $joins, // Edited by Akshay on 5-8-2025
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        foreach ($arr_payroll as $key => $value) {
            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_present'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['days_lop'] = $value['Payrollmaster']['loss_of_pay'];
            //$emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : 0;
            $resp_payroll["rows"][$key]['monthly_ctc'] = $value['Payrollmaster']['monthly_ctc'];
            $resp_payroll["rows"][$key]['monthly_amount'] = $value['Payrollmaster']['monthly_amount'];
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = $value['Payrollmaster']['gross_salary'];
            $resp_payroll["rows"][$key]['net_salary'] = $value['Payrollmaster']['net_salary'];
            $resp_payroll["rows"][$key]['total_deductions'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollmaster']['action'];
            if ($company_code == 'GLET' || $company_code == 'SHYD') {
                $resp_payroll["rows"][$key]['email'] = $value['EmpDetails']['email']; // Edited by Akshay on 15-7-2025
            }

            // Edited by Akshay on 5-8-2025
            // Default resigned to false
            $resigned = false;

            // Condition 1: Status is 2 (already resigned)
            if (!empty($value['emp_details']['status']) && $value['emp_details']['status'] == 2) {
                $resigned = true;
            }
            // Condition 2: Resignation submitted and date is before or on today
            elseif (!empty($value['Termination']['submitted_date'])) {
                // $submittedDate = date('Y-m-d', strtotime($value['Termination']['submitted_date']));
                // $today = date('Y-m-d');

                // if ($submittedDate <= $today) {
                //     $resigned = true;
                // }
                $resigned = true;
            }

            $resp_payroll["rows"][$key]['resigned'] = $resigned;
            // End
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }
    public function approvepayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

        $arr_requestdata = $this->request->data;
        $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();

        $arr_data = array(
            'Payrollmaster.action' => "'Approved'"
        );

        $arr_pkey = explode(",", $arr_requestdata["payroll_pkey"]);
        foreach ($arr_pkey as $k) {
            $arr_conditions = array();
            $arr_conditions[] = array("Payrollmaster.payroll_master_pkey" => $k);
            $arr_payroll = $this->Payrollmaster->find(
                "all",
                array(
                    'fields' => 'Payrollmaster.*',
                    'conditions' => $arr_conditions
                )
            );
            $branch_code = isset($arr_payroll['0']['Payrollmaster']['branch_code']) ? $arr_payroll['0']['Payrollmaster']['branch_code'] : '';
            $month = isset($arr_payroll['0']['Payrollmaster']['month_year']) ? $arr_payroll['0']['Payrollmaster']['month_year'] : '';
            $emp_fkey = isset($arr_payroll['0']['Payrollmaster']['emp_fkey']) ? $arr_payroll['0']['Payrollmaster']['emp_fkey'] : '';
            $uid = $this->Session->read("login_user_id"); //user id
            //            debug($arr_payroll);
            $arr_payroll = $this->Payrollmaster->query("call payroll_master_approve('$branch_code','$month','$emp_fkey','$uid', @`perror_message`); ");
        }
        $this->Payrollmaster->updateAll(
            $arr_data,
            array('Payrollmaster.payroll_master_pkey' => $arr_payroll_pkeys)
        );

        $result = array('success' => 1);
        echo json_encode($result);
    }
    //Edited by Akshay on 18-11-2024
    public function generatePasswords()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        date_default_timezone_set('Asia/Kolkata');

        // Get the current month and year or use the passed month
        // $current_month_year = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
        // $Month = date('F Y', strtotime($current_month_year . '-01'));
        $current_month_year = date('Y-m');
        $Month = isset($_REQUEST['month']) ? $_REQUEST['month'] : $current_month_year;
        $monthName = date('F Y', strtotime($Month));

        // Array to track employees with errors
        $failed_employees = [];

        // Fetch employee details
        $arr_emps = $this->Payrollmaster->query("
                SELECT emp_pkey, email, first_name, middile_name, last_name 
                FROM emp_details ed 
                WHERE status = 1
                AND email != '' AND email IS NOT NULL;
            ");
        $user_name = $this->Session->read('user_name');

        foreach ($arr_emps as $emp) {
            try {
                $emp_pkey = isset($emp['ed']['emp_pkey']) ? $emp['ed']['emp_pkey'] : 0;
                $email = isset($emp['ed']['email']) ? $emp['ed']['email'] : '';
                $first_name = isset($emp['ed']['first_name']) ? $emp['ed']['first_name'] : '';
                $middile_name = isset($emp['ed']['middile_name']) ? $emp['ed']['middile_name'] : '';
                $last_name = isset($emp['ed']['last_name']) ? $emp['ed']['last_name'] : '';
                $name = trim("$first_name $middile_name $last_name");

                // Generate random password
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
                $passwordLength = rand(5, 8);
                $randomPassword = '';
                for ($i = 0; $i < $passwordLength; $i++) {
                    $randomPassword .= $characters[rand(0, strlen($characters) - 1)];
                }

                // Update password status and insert new password
                $this->Payrollmaster->query("
                        UPDATE passwords
                        SET status = 0
                        WHERE emp_fkey = '$emp_pkey' AND status != 0;
                    ");
                $this->Payrollmaster->query("
                        INSERT INTO passwords (
                            emp_fkey, pass_word, month_year, created_by, status
                        ) VALUES (
                            $emp_pkey, '$randomPassword', '$current_month_year', '$user_name', 1
                        );
                    ");

                // Send email
                App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
                $mail = new PHPMailer;

                $mail->isSMTP();
                $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
                $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('mypayrollmaster@office24.online');
                $mail->addAddress($email);
                $mail->addReplyTo('mypayrollmaster@office24.online');
                $mail->isHTML(true);

                $mail->Subject = 'Your Password';
                $mail->MsgHTML("<h4>Hi $name,</h4><div>Your password for $monthName is provided below:</div><h1>$randomPassword</h1><h4>*** This is a system-generated email and should not be replied to ***</h4>");

                if (!$mail->send()) {
                    throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
                }
            } catch (Exception $e) {
                // Add the name of the employee to the failed list
                $failed_employees[] = $name . ' (' . $email . ')';
            }
        }

        // Prepare response
        if (empty($failed_employees)) {
            echo json_encode(['status' => 'success', 'message' => 'Passwords generated and emails sent successfully']);
        } else {
            echo json_encode([
                'status' => 'partial_success',
                'message' => 'Some passwords generated, but errors occurred for the following employees:',
                'failed_employees' => $failed_employees
            ]);
        }
    }
    //End

}
