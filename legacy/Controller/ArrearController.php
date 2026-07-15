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
class ArrearController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Arrear';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister', 'MonthlyCTC', 'GrossSalary', 'TotalDeductions', 'MonthlyAmount', 'Payrollmaster', 'EmpSalarySlip', 'EmployeeCTC', 'Payrollarrearmaster', 'SalaryHeadItems', 'EmpArrearSalarySlip', 'PayrollArrearComponents');
    public $components = array('MasterdataManagement');

    public function showprocesspayroll()
    {
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->Payrollarrearmaster->useDbconfig = $this->Session->read('ds');
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);
        // debug($arr_branches);
        $branch_code = $arr_branches['0']['branch_code'];
        // debug($branch_code);
        $arr_month = $this->EmployeeCTC->query("select distinct pay_out_month from emp_ctc_upload where arrear_salary='Y' 
and emp_fkey in (select emp_pkey from emp_details where branch_code='$branch_code' and status=1) and emp_ctc_upload_pkey in (select ctc_upload_fkey from emp_ctc_transaction where  end_date_effective is null
and emp_fkey in (select emp_pkey from emp_details where branch_code='$branch_code'))");
        $this->set('arr_month', $arr_month);

        $arr_dates = $this->EmployeeCTC->query("select distinct month_year from payroll_arrear_master where 
emp_fkey in (select emp_pkey from emp_details where branch_code='$branch_code' and status=1) ");
        $this->set('arr_dates', $arr_dates);
        // debug($arr_dates);
    }
    public function listcomponents()
    {
        $this->autoRender = FALSE;
        // debug($branch);

        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $branch = $arr_form_data['branch'];
        // debug($branch);
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_sal_components = $this->SalaryHeadItems->query(" select distinct salary_head_item_fkey,trim(salary_head_item_desc)salary_head_item_desc from emp_salary_structure  where end_date_effective is null and emp_fkey in (select emp_pkey from emp_details where branch_code = '$branch')");
        // debug($arr_sal_components);
        foreach ($arr_sal_components as $val) {
            // debug($val);
            $arr_outputs = isset($val['0']['salary_head_item_desc']) ? $val['0']['salary_head_item_desc'] : '';
            // debug($arr_outputs);
            $arr_outputss = isset($val['emp_salary_structure']['salary_head_item_fkey']) ? $val['emp_salary_structure']['salary_head_item_fkey'] : '';
            $arr_output['item'] = $arr_outputs;
            $arr_output['item_pkey'] = $arr_outputss;
            $resp_data['rows'][] = $arr_output;
        }

        echo json_encode($resp_data);
        // debug($resp_data);
        // debug($resp_data);

    }


    // public function loadcriteriaitems($showprocesspayroll,$str_criteria=''){
    //     $this -> autoRender = FALSE;
    //     if($str_criteria != ''){
    //         $model = $str_criteria;
    //         debug($model);
    //         if($this->_modelExists($model)){
    //             $this->set('showprocesspayroll',$showprocesspayroll);
    //             $model = ($model == 'EmployeeDetails')?'Employees':$model;
    //             $this->set('criteria',$model);
    //             $this->render('loadcriteriaitems');
    //         }else{
    //             return '';
    //         }
    //     }else{
    //         return '';
    //     }
    // }


    // debug($arr_output);



    //branch filtter     
    public function filter_month($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = " and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        // if ($q != null) {
        //     $q_condition = "and first_name like '%$q%' or emp_proff.emp_company_id like '%$q%' ";
        // } else {
        //     $q_condition = "";
        // }
        $branch_array = $this->EmployeeDetails->query("select distinct pay_out_month from emp_ctc_upload where  emp_fkey in (select emp_pkey from emp_details where status=1 $branch_condition) and emp_ctc_upload_pkey in (select ctc_upload_fkey from emp_ctc_transaction where  end_date_effective is null
                                                            and emp_fkey in (select emp_pkey from emp_details where branch_code='$branch')) and arrear_salary = 'Y' ORDER BY pay_out_month");
        // debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        // $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            // Edited by Akshay on 23-12-2024
            if (isset($value['emp_ctc_upload']['pay_out_month']) && trim($value['emp_ctc_upload']['pay_out_month']) != '' && $value['emp_ctc_upload']['pay_out_month'] != '0000-00-00') {
                $branch[] = array(
                    'id' => $value['emp_ctc_upload']['pay_out_month'],
                    'text' => date('m-Y', strtotime($value['emp_ctc_upload']['pay_out_month']))
                );
            }
            // Edited by Akshay on 23-12-2024
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
    public function showapprovepayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        $this->set('arr_employees', $arr_employees);
    }

    public function FilterList()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
        $month = $month ? date('Y-m-1', strtotime($month . '-01')) : '';
        $user_id = $this->Session->read("login_user_id");
        try {
            $arr_branches = $this->AttendanceRegister->query("select arrear_month_fn('$branch','$month','$user_id')");
        } catch (Exception $e) {
            debug($e);
        }

        try {
            $arr_emp_ctc =  $this->AttendanceRegister->query("SELECT ecu.start_date_effective, ecu.emp_fkey, pam.payroll_arrear_master_pkey FROM payroll_arrear_master pam
                                                                LEFT JOIN emp_ctc_upload ecu ON pam.emp_fkey = ecu.emp_fkey AND pam.payout_month = ecu.pay_out_month
                                                                WHERE pam.payroll_arrear_master_pkey IS NOT NULL
                                                                    AND ecu.created_date = (
                                                                            SELECT MAX(created_date)
                                                                            FROM emp_ctc_upload 
                                                                            WHERE emp_fkey = ecu.emp_fkey
                                                                            AND pay_out_month = ecu.pay_out_month
                                                                            AND start_date_effective = ecu.start_date_effective
                                                                        )
                                                                    AND ecu.arrear_salary = 'Y'
                                                                ");
        } catch (Exception $e) {
            debug($e);
        }
        $result = array('success' => 1);
        echo json_encode($result);
    }

    public function showprocesspayrolltab($processed = '')
    {
        $this->set('tab', $processed);
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
        $arr_conditions = array('status' => 1);


        if ($processed == 0) {
            $arr_conditions = array('action' => 'Arrear');
            if ($branch != '') {
                $arr_conditions['Payrollarrearmaster.branch_code'] = $branch;
            }
        }
        if ($processed == 1) {
            $arr_conditions = array('action' => 'Processed');

            if ($branch != '') {
                $arr_conditions['Payrollarrearmaster.branch_code'] = $branch;
            }
        }
        if ($processed == 2) {
            if ($branch != '') {
                $arr_conditions['Payrollarrearmaster.branch_code'] = $branch;
            }
            $arr_conditions = array('action' => 'Approved');
        }



        $joins = array(
            array(
                'table' => 'payroll_arrear_master',
                'alias' => 'Payrollarrearmaster',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.emp_pkey = Payrollarrearmaster.emp_fkey',

                )
            ),

        );

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'group' => array('emp_pkey'), "fields" => array("emp_pkey", "emp_name", "emp_id"), "joins" => $joins, "conditions" => $arr_conditions)));
        $this->set('arr_employees', $arr_employees);
    }

    public function listpayroll()
    {
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE;
        $this->Payrollarrearmaster->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(

            "Payrollarrearmaster.action" => 'Arrear'

        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollarrearmaster.branch_code="' . $branches . '"'; // Edited by Akshay on 13-12-2024
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = isset($_REQUEST['month']) ? $_REQUEST['month'] : '';
            // $month =date('Y-m', strtotime($month));
            // debug($month);


            $arr_conditions[] = 'payout_month = "' . $month . '-01"';
            // debug($arr_conditions);
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }

        // Edited by Akshay on 13-12-2024
        // $arr_conditions[] = 'ed.status=1';
        if (isset($branches) && $branches != '') {
            $arr_conditions[] = 'ed.branch_code = "' . $branches . '"';
        }

        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'ed',
                'type' => 'LEFT',
                'conditions' => array(
                    'ed.emp_pkey = Payrollarrearmaster.emp_fkey'
                )
            )
        );
        //End

        //$query = "select * from payroll_master where " . implode(' AND ', $arr_salary_conditions);
        //$payroll = $this->AttendanceRegister->query($query);

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        try {
            $count = $this->Payrollarrearmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins)); // Edited by Akshay on 13-12-2024
        } catch (Exception $e) {
            debug($e);
        }

        $arr_payroll = $this->Payrollarrearmaster->find(
            "all",
            array(
                'fields' => 'Payrollarrearmaster.*',
                'conditions' => $arr_conditions,
                'joins' => $joins,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );

        // debug($arr_payroll);
        foreach ($arr_payroll as $key => $value) {
            $resp_payroll["rows"][$key]['payroll_arrear_master_pkey'] = $value['Payrollarrearmaster']['payroll_arrear_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollarrearmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollarrearmaster']['emp_name'];
            $resp_payroll["rows"][$key]['month_year'] = date('m-Y', strtotime($value['Payrollarrearmaster']['month_year'])); // Edited by Akshay on 31-12-2024
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollarrearmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollarrearmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollarrearmaster']['loss_of_pay'];
            //$emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : 0;
            $resp_payroll["rows"][$key]['monthly_ctc'] = $value['Payrollarrearmaster']['monthly_ctc'];
            $resp_payroll["rows"][$key]['monthly_amount'] = $value['Payrollarrearmaster']['monthly_amount'];
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollarrearmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollarrearmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = $value['Payrollarrearmaster']['gross_salary'];
            $resp_payroll["rows"][$key]['net_salary'] = $value['Payrollarrearmaster']['net_salary'];
            $resp_payroll["rows"][$key]['total_deductions'] = $value['Payrollarrearmaster']['total_deduction'];
            $resp_patroll["rows"][$key]['arrear_net_salary'] = $value['Payrollarrearmaster']['arrear_net_salary'];
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
        $this->Payrollarrearmaster->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(

            "Payrollarrearmaster.action" => 'Processed'


        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $months = $_REQUEST['month'];
            $date = DateTime::createFromFormat('Y-m', $months);
            $months = $date->format('Y-m-01'); // Ensures correct format and padding

            $month = date('Y-m', strtotime($months));

            $arr_conditions[] = 'payout_month="' . $months . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }
        // debug($month);
        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollarrearmaster->find("count", array("conditions" => $arr_conditions));
        $arr_payroll = $this->Payrollarrearmaster->find(
            "all",
            array(
                'fields' => 'Payrollarrearmaster.*',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        // debug($arr_payroll);
        $arr_conditions_prev = array(
            "NOT" => array(
                "Payrollarrearmaster.action" => '',
                "Payrollarrearmaster.action" => NULL
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
        $prevsalary = $this->Payrollarrearmaster->find(
            "all",
            array(
                'fields' => 'Payrollarrearmaster.emp_fkey,Payrollarrearmaster.net_salary',
                'conditions' => $arr_conditions_prev,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        $arr_prev_month_net_salary = array();
        foreach ($prevsalary as $key => $value) {
            $emp_fkey = isset($value['Payrollrrearmaster']['emp_fkey']) ? $value['Payrollarrearmaster']['emp_fkey'] : '';
            if ($emp_fkey != '') {
                $arr_prev_month_net_salary[$emp_fkey] = isset($value['Payrollarrearmaster']['net_salary']) ? $value['Payrollarrearmaster']['net_salary'] : '';
            }
        }
        foreach ($arr_payroll as $key => $value) {

            $resp_payroll["rows"][$key]['payroll_arrear_master_pkey'] = $value['Payrollarrearmaster']['payroll_arrear_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollarrearmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollarrearmaster']['emp_name'];
            $resp_payroll["rows"][$key]['month'] = date('m-Y', strtotime($value['Payrollarrearmaster']['month_year'])); // Edited by Akshay on 31-12-2024
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollarrearmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollarrearmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollarrearmaster']['loss_of_pay'];
            //$emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : 0;
            $resp_payroll["rows"][$key]['monthly_ctc'] = $value['Payrollarrearmaster']['monthly_ctc'];
            $resp_payroll["rows"][$key]['monthly_amount'] = $value['Payrollarrearmaster']['monthly_amount'];
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollarrearmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollarrearmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = $value['Payrollarrearmaster']['gross_salary'];
            $resp_payroll["rows"][$key]['net_salary'] = $value['Payrollarrearmaster']['net_salary'];
            $resp_payroll["rows"][$key]['arrear_net_salary'] = $value['Payrollarrearmaster']['arrear_net_salary'];
            $resp_payroll["rows"][$key]['total_deduction'] = $value['Payrollarrearmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollarrearmaster']['action'];
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollarrearmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = $arr_prev_month_net_salary[$emp_fkey];
                $resp_payroll["rows"][$key]['diff'] =  $value['Payrollmaster']['net_salary'] - $arr_prev_month_net_salary[$emp_fkey];
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
                $resp_payroll["rows"][$key]['diff'] = '';
            }
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }


    public function processpayroll()
    {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrollarrearmaster->useDbConfig = $this->Session->read('ds');
        $this->PayrollArrearComponents->useDbConfig = $this->Session->read('ds');
        $success = 0;
        $arr_requestdata = $this->request->data;

        $branch = isset($arr_requestdata["branch"]) ? $arr_requestdata["branch"] : '';
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');


        if (isset($arr_requestdata["emp_pkey"])) {
            $arr_emp_pkeys = isset($arr_requestdata["emp_pkey"]) ? explode(",", $arr_requestdata["emp_pkey"]) : array();

            $arr_month = (isset($arr_requestdata['month']) && $arr_requestdata['month'] != '') ? $arr_requestdata['month'] : '';

            $date = date('Y-m', strtotime($arr_month));

            $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();

            // Edited by Akshay on 20-12-2024
            $arr_payroll_pkeys = array_filter($arr_payroll_pkeys, function ($value) {
                return $value !== '';
            });
            // End


            $arr_components = isset($arr_requestdata["component_key"]) ?  $arr_requestdata["component_key"]  : '';



            $arr_data['emp'] = $arr_emp_pkeys;

            $arr_data1['payroll'] = $arr_payroll_pkeys;

            if ($arr_components == '') {
                $Components = 0;
            } else {
                $Components = isset($arr_requestdata["component_key"]) ? $arr_requestdata["component_key"] : '';
            }
            $arr_data2['components'] = '';

            if ($arr_components == '') {
                $Components = 0;
            } else {
                $Components = isset($arr_requestdata["component_key"]) ? $arr_requestdata["component_key"] : '';
            }

            $arr_payroll_pkeys_len = $count = count($arr_payroll_pkeys);
            for ($x = 0; $x < $arr_payroll_pkeys_len; $x++) {
                $str_emp = isset($arr_emp_pkeys[$x]) ? $arr_emp_pkeys[$x] : 0;
                if ($str_emp > 0) {
                    $str_value = $arr_payroll_pkeys[$x];

                    // Edited by Akshay on 23-4-2025
                    $arr_sal_components = $this->SalaryHeadItems->query(" select distinct salary_head_item_fkey from emp_salary_structure  where end_date_effective is null and emp_fkey = '$str_emp'");
                    // Extract the values into an array
                    $arr_components = array_map(function ($row) {
                        return $row['emp_salary_structure']['salary_head_item_fkey'];
                    }, $arr_sal_components);

                    // Convert to comma-separated string
                    $Components = implode(',', $arr_components);
                    // End

                    $query = $this->Payrollarrearmaster->query("select month_year from payroll_arrear_master where payroll_arrear_master_pkey = '$str_value'");
                    $process_month = $query[0]['payroll_arrear_master']['month_year'];
                    // $db_data = $this->PayrollArrearComponents->query("select COUNT(*) from payroll_arrear_comp_master where payroll_arrear_master_pkey = '$str_value' and end_date_effective is  NULL");
                    // $db_count = $db_data['0']['0']['COUNT(*)'];
                    // if ($db_count == '0') {
                    //     $this->PayrollArrearComponents->query("INSERT INTO payroll_arrear_comp_master (payroll_arrear_master_pkey, emp_fkey, month_year,salary_head_fkey) VALUES ($str_value,$str_emp,'$process_month','$Components') ");
                    // } else {
                    //     $this->PayrollArrearComponents->query("UPDATE payroll_arrear_comp_master SET month_year = '$process_month', salary_head_fkey = '$Components' WHERE payroll_arrear_comp_master.payroll_arrear_master_pkey = '$str_value' and end_date_effective is NULL");
                    // }
                }
            }

            if (isset($arr_requestdata['tax']) && $arr_requestdata['tax'] == '1') { // Edited by Akshay on 19-6-2025

                foreach ($arr_data1['payroll'] as $value) {

                    $this->Payrollmaster->query("UPDATE payroll_arrear_master SET tax_include = 'Y' WHERE payroll_arrear_master.payroll_arrear_master_pkey = '$value'");
                }
            }
            if (isset($arr_requestdata['tax']) && $arr_requestdata['tax'] == '0') { // Edited by Akshay on 19-6-2025
                foreach ($arr_data1['payroll'] as $value) {
                    // debug($value);
                    $this->Payrollmaster->query("UPDATE payroll_arrear_master SET tax_include = 'N' WHERE payroll_arrear_master.payroll_arrear_master_pkey = '$value'");
                }
            }


            for ($i = 0; $i < $count; $i++) {
                $str_value = $arr_data1['payroll'][$i];
                $query = $this->Payrollarrearmaster->query("select month_year from payroll_arrear_master where payroll_arrear_master_pkey = '$str_value'");
                $process_month = $query[0]['payroll_arrear_master']['month_year'];
                $outputParameter = array();
                $outputParameter[] = $process_month;
                // Edited by Akshay on 22-5-2025
                $outputParameter[] = (isset($arr_requestdata['month']) && $arr_requestdata['month'] != '')
                    ? $arr_requestdata['month'] . '-1'
                    : '';
                // End
                $outputParameter[] = (isset($arr_requestdata['branch']) && $arr_requestdata['branch'] != '') ? $arr_requestdata['branch'] : '';
                $outputParameter[] = $arr_data['emp'][$i];
                $outputParameter[] = $arr_data1['payroll'][$i];
                $outputParameter[] = $this->Session->read("login_user_id"); //user id
                //debug($outputParameter);
                $outputParameters = array();
                $outputParameters[] = $process_month;
                // $outputParameter[] = (isset($arr_requestdata['month']) && $arr_requestdata['month'] != '') ? $arr_requestdata['month'] : '';
                $outputParameters[] = (isset($arr_requestdata['branch']) && $arr_requestdata['branch'] != '') ? $arr_requestdata['branch'] : '';
                $outputParameters[] = $arr_data['emp'][$i];
                $outputParameters[] = $arr_data1['payroll'][$i];
                $outputParameters[] = $this->Session->read("login_user_id");
                // debug($outputParameter);
                try {
                    // $out = $this->AttendanceRegister->ArrearsalaryProcessPrc($outputParameter);
                    $params = $outputParameter; // Assuming this is your input array with 6 elements

                    $sql = "
                                        CALL arrear_salary_process_prc(
                                            '{$params[0]}',
                                            '{$params[1]}',
                                            '{$params[2]}',
                                            {$params[3]},
                                            {$params[4]},
                                            '{$params[5]}',
                                            @perr_msg
                                        );
                                    ";

                    // Run as raw SQL
                    $out = $this->AttendanceRegister->query($sql);
                } catch (Exception $e) {
                    debug($e);
                }



                $this->EmpArrearSalarySlip->useDbConfig = $this->Session->read('ds');
                $arr_formulae_from_remarks = $this->EmpArrearSalarySlip->find(
                    "all",
                    array(
                        'fields' => 'emp_new_salary_slip_pkey,head_operator,remarks',
                        'conditions' => array(
                            'payroll_arrear_master_fkey' => $arr_data1['payroll'][$i],
                            'remarks IS NOT NULL',
                            'end_date_effective is null'
                        )
                    )
                );
                // debug($arr_formulae_from_remarks);

                foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                    $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmpArrearSalarySlip']['emp_new_salary_slip_pkey']) ? $row_formulae_from_remarks['EmpArrearSalarySlip']['emp_new_salary_slip_pkey'] : '';
                    $head_operator = isset($row_formulae_from_remarks['EmpArrearSalarySlip']['head_operator']) ? $row_formulae_from_remarks['EmpArrearSalarySlip']['head_operator'] : '';
                    $formula_from_remarks = isset($row_formulae_from_remarks['EmpArrearSalarySlip']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmpArrearSalarySlip']['remarks']) : '';

                    if (!empty($formula_from_remarks)) {

                        eval('$salary_amount = ' . $formula_from_remarks . ';');
                        // debug($salary_amount);
                        if ($head_operator == 'Deduction') {
                            $salary_amount *= -1;
                        }

                        $arr_emp_arrear_salary_slip_data = array(
                            'EmpArrearSalarySlip.salary_rate' => $salary_amount,
                            'EmpArrearSalarySlip.salary_amount' => round($salary_amount)
                        );
                        $this->EmpArrearSalarySlip->updateAll(
                            $arr_emp_arrear_salary_slip_data,
                            array('EmpArrearSalarySlip.emp_new_salary_slip_pkey' => $emp_salary_slip_pkey)
                        );
                        // debug($arr_emp_salary_slip_data);
                    }
                }
                // $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
                $this->Payrollarrearmaster->useDbConfig = $this->Session->read('ds');
                // $out = $this->Payrollmaster->taxSalaryProcessPrc($outputParameter);
                $out = $this->Payrollarrearmaster->taxArrearProcessOldPrc($outputParameters); // Edited by Akshay on 19-6-2025
                $out = $this->Payrollarrearmaster->taxArrearProcessPrc($outputParameters); // Edited by Akshay on 23-5-2025

                //Ends

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
        $this->Payrollarrearmaster->useDbConfig = $this->Session->read('ds');
        $this->EmpArrearSalarySlip->useDbConfig = $this->Session->read('ds');
        $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds');

        $success = 1;
        $arr_requestdata = $this->request->data;
        // debug($arr_requestdata);
        if (isset($arr_requestdata["payroll_pkey"])) {
            $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : ' ';
            // $this->Payrollmaster->deleteAll(array('Payrollarrearmaster.payroll_arrear_master_pkey' => $arr_payroll_pkeys), false);
            $date = date('Y-m-d');
            $this->EmpArrearSalarySlip->updateAll(
                array(
                    'end_date_effective' => $date
                ),
                array(
                    'payroll_arrear_master_fkey' => $arr_payroll_pkeys
                )
            );
        }
        // debug($arr_payroll_pkeys);
        //$imp_key = implode(', ', $arr_payroll_pkeys);
        $arr_payroll_pkeys_list = $arr_requestdata['payroll_pkey'];
        $str = substr($arr_payroll_pkeys_list, -1);
        if ($str == ',') {
            $arr_payroll_pkeys_list = substr($arr_payroll_pkeys_list, 0, -1);
        }
        $this->Payrollarrearmaster->query("update payroll_arrear_master set action = 'Arrear' where payroll_arrear_master_pkey in ($arr_payroll_pkeys_list) ");
        $result = array('success' => $success);
        echo json_encode($result);
    }

    public function showarrearsalaryslip($payroll_arrear_master_pkey = 0)
    {
        // $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds');
        $this->EmpArrearSalarySlip->useDbConfig = $this->Session->read('ds');

        $fields = 'EmpArrearSalarySlip.head_operator,,EmpArrearSalarySlip.structure_det_value,EmpArrearSalarySlip.salary_head_item_desc,EmpArrearSalarySlip.salary_rate,EmpArrearSalarySlip.salary_amount,EmpArrearSalarySlip.arrear_amount,Payrollarrearmaster.emp_name,SalaryHeads.head_pkey,SalaryHeads.head_desc';
        $arr_joins = array(
            array(
                'table' => 'payroll_arrear_master',
                'alias' => 'Payrollarrearmaster',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmpArrearSalarySlip.payroll_arrear_master_fkey = Payrollarrearmaster.payroll_arrear_master_pkey'
                )
            ),
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmpArrearSalarySlip.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey'
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
            'EmpArrearSalarySlip.payroll_arrear_master_fkey' => $payroll_arrear_master_pkey,
            "end_date_effective is null",
            "EmpArrearSalarySlip.item_part not in('Indirect')",
            "(EmpArrearSalarySlip.action IS NULL OR EmpArrearSalarySlip.action = 'P')"
        );
        $arr_result = $this->EmpArrearSalarySlip->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $arr_joins,
                'conditions' => $arr_conditions
            )
        );
        // debug($arr_result);
        $arr_conditions1 = array(
            'EmpArrearSalarySlip.payroll_arrear_master_fkey' => $payroll_arrear_master_pkey,
            "end_date_effective is null",
            "EmpArrearSalarySlip.item_part in('Indirect')",
            "(EmpArrearSalarySlip.action IS NULL OR EmpArrearSalarySlip.action = 'P')"
        );
        $arr_indirect = $this->EmpArrearSalarySlip->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $arr_joins,
                'conditions' => $arr_conditions1
            )
        );
        // debug($arr_indirect);
        $this->set('arr_indirect', $arr_indirect);

        $arr_emparrearsalaryslip = array();
        $head = '';
        //debug($arr_result);
        foreach ($arr_result as $value) {
            $head = isset($value['Payrollarrearmaster']['emp_name']) ? $value['Payrollarrearmaster']['emp_name'] . "'s salary as follows" : '';
            $head_pkey = isset($value['SalaryHeads']['head_pkey']) ? $value['SalaryHeads']['head_pkey'] : '';
            if (!isset($arr_emparrearsalaryslip[$head_pkey])) {
                $head_desc = isset($value['SalaryHeads']['head_desc']) ? $value['SalaryHeads']['head_desc'] : '';
                $head_pkey = isset($value['SalaryHeads']['head_pkey']) ? $value['SalaryHeads']['head_pkey'] : '';
                $arr_emparrearsalaryslip[$head_pkey] = array(
                    'head_pkey' => $head_pkey,
                    'head_desc' => $head_desc,
                    'items' => array()
                );
            }
            // debug($head_desc);
            if (!empty($value['EmpArrearSalarySlip'])) {
                $arr_emparrearsalaryslip[$head_pkey]['items'][] = $value['EmpArrearSalarySlip'];
            }
        }
        // debug($arr_emparrearsalaryslip);
        $this->set('head', $head);
        // $this->set('head_desc',$head_desc);
        // $this->set('head_pkey',$head_pkey);
        // debug($head_desc);
        // debug($head);

        $this->set('arr_emparrearsalaryslip', $arr_emparrearsalaryslip);
        // debug($arr_emparrearsalaryslip);
    }

    public function showapprovepayrolltab($approved = 0)
    {
        $this->set('tab', $approved);
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
        $arr_conditions = array('status' => 1);

        if ($branch != '') {
            $arr_conditions['branch_code'] = $branch;
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => $arr_conditions)));
        $this->set('arr_employees', $arr_employees);
    }

    public function listapprovepayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->payrollarrearmaster->useDbConfig = $this->Session->read('ds');


        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'OR' => array(
                array('Payrollarrearmaster.action' => 'Processed'),
                array('Payrollarrearmaster.action' => 'Verified'),
            )
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
            $arr_prev_month_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'month_year="' . $_REQUEST['month'] . '"';

            $prevmonth = date('Y-m', strtotime('-1 month', strtotime($_REQUEST['month'])));
            $arr_prev_month_conditions[] = 'month_year="' . $prevmonth . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
            $arr_prev_month_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollarrearmaster->find("count", array("conditions" => $arr_conditions));
        $arr_payroll = $this->Payrollarrearmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        // debug($arr_payroll);
        //Fetch last month net salary
        $arr_prev_month_payroll = $this->Payrollarrearmaster->find(
            "all",
            array(
                'fields' => 'Payrollarrearmaster.emp_fkey,Payrollarrearmaster.net_salary',
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
        }

        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function listapprovedpayroll()
    {
        $this->autoRender = FALSE;
        // $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrollarrearmaster->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            "Payrollarrearmaster.action" => 'Approved'
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'] . '-01'; // Edited by Akshay on 30-5-2025
            $arr_conditions[] = 'payout_month="' . $month . '"'; // Edited by Akshay on 30-5-2025
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollarrearmaster->find("count", array("conditions" => $arr_conditions));
        $arr_payroll = $this->Payrollarrearmaster->find(
            "all",
            array(
                'fields' => 'Payrollarrearmaster.*',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        // debug($arr_payroll);
        foreach ($arr_payroll as $key => $value) {
            $resp_payroll["rows"][$key]['payroll_arrear_master_pkey'] = $value['Payrollarrearmaster']['payroll_arrear_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollarrearmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollarrearmaster']['emp_name'];
            $resp_payroll["rows"][$key]['month_year'] = date('m-Y', strtotime($value['Payrollarrearmaster']['month_year'])); // Edited by Akshay on 31-12-2024
            $resp_payroll["rows"][$key]['days_present'] = $value['Payrollarrearmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollarrearmaster']['days_leave'];
            $resp_payroll["rows"][$key]['days_lop'] = $value['Payrollarrearmaster']['loss_of_pay'];
            //$emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : 0;
            $resp_payroll["rows"][$key]['monthly_ctc'] = $value['Payrollarrearmaster']['monthly_ctc'];
            $resp_payroll["rows"][$key]['monthly_amount'] = $value['Payrollarrearmaster']['monthly_amount'];
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollarrearmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollarrearmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = $value['Payrollarrearmaster']['gross_salary'];
            $resp_payroll["rows"][$key]['net_salary'] = $value['Payrollarrearmaster']['net_salary'];
            $resp_payroll["rows"][$key]['arrear_net_salary'] = $value['Payrollarrearmaster']['arrear_net_salary'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollarrearmaster']['action'];
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function approvePayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrollarrearmaster->useDbConfig = $this->Session->read('ds');

        $arr_requestdata = $this->request->data;
        $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();

        $arr_data = array(
            'Payrollarrearmaster.action' => "'Approved'"
        );

        $valid_pkeys = array(); // Edited  by Akshay on 11-6-2025
        $invalid_keys = array(); // Edited  by Akshay on 11-6-2025

        $arr_pkey = array_filter(explode(",", $arr_requestdata["payroll_pkey"])); // Edited by Akshay on 19-6-2025
        foreach ($arr_pkey as $k) {
            $arr_conditions = array();
            $arr_conditions[] = array("Payrollarrearmaster.payroll_arrear_master_pkey" => $k);
            $arr_payroll = $this->Payrollarrearmaster->find(
                "all",
                array(
                    'fields' => 'Payrollarrearmaster.*',
                    'conditions' => $arr_conditions
                )
            );
            $branch_code = $arr_payroll[0]['Payrollarrearmaster']['branch_code'];
            $month = $arr_payroll[0]['Payrollarrearmaster']['month_year'];
            $payout_month = $arr_payroll[0]['Payrollarrearmaster']['payout_month']; // Edited by Akshay on 6-6-2025
            $emp_fkey = $arr_payroll[0]['Payrollarrearmaster']['emp_fkey'];
            $uid = $this->Session->read("login_user_id"); //user id
            //            debug($arr_payroll);

            // Edited by Akshay on 11-6-2025
            $month_year =   date('Y-m', strtotime($payout_month));
            $arr_ar_count = $this->Payrollarrearmaster->query("SELECT count(*) AS count FROM attendance_register ar WHERE ar.emp_fkey = '$emp_fkey' AND ar.month_year = '$month_year' AND ar.isdelete = 'N';");
            // debug("SELECT count(*) AS count FROM attendance_register ar WHERE ar.emp_fkey = '$emp_fkey' AND ar.month_year = '$month_year' AND ar.isdelete = 'N';");
            // debug($arr_payroll_pkeys);
            // debug($k);
            // debug($arr_ar_count);
            // exit;
            $ar_count = isset($arr_ar_count[0][0]['count']) ? $arr_ar_count[0][0]['count'] : 0;
            if ($ar_count > 0) {
                $arr_payroll = $this->Payrollarrearmaster->query("call payroll_arrear_approve('$branch_code','$month', '$payout_month', '$emp_fkey','$uid', @`perror_message`); ");
                // debug("call payroll_arrear_approve('$branch_code','$month', '$payout_month', '$emp_fkey','$uid', @`perror_message`); ");
                $valid_pkeys[] = $k;
            } else {
                $invalid_keys[] = $k;
            }
            // End


        }
        // $this->Payrollarrearmaster->updateAll(
        //     $arr_data,
        //     array('Payrollarrearmaster.payroll_arrear_master_pkey' => $arr_payroll_pkeys)
        // );

        // Edited by Akshay on 11-6-2025
        if (!empty($valid_pkeys)) {
            $this->Payrollarrearmaster->updateAll(
                $arr_data,
                array('Payrollarrearmaster.payroll_arrear_master_pkey' => $valid_pkeys)
            );
        }
        // End

        // $result = array('success' => 1);
        // Edited by Akshay on 11-6-2025
        $result = array(
            'success' => 1,
            'processed' => $valid_pkeys,
            'skipped' => $invalid_keys
        );
        // End

        echo json_encode($result);
    }
}
