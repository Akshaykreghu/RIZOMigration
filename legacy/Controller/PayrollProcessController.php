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
class PayrollProcessController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'PayrollProcess';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Units', 'AttendanceRegister', 'MonthlyCTC', 'GrossSalary', 'TotalDeductions', 'MonthlyAmount', 'Payrollmaster', 'EmpSalarySlip', 'Payrolltransactions');
    public $components = array('MasterdataManagement');

    public function showprocesspayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        /* $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
          $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
          $this->set('arr_employees', $arr_employees); */
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
        $user_id = $this->Session->read("login_user_id");
        $arr_branches = $this->AttendanceRegister->query("call payroll_master_insert('$branch','$month','$user_id',@error)");

        $result = array('success' => 1);
        echo json_encode($result);
    }

    public function showprocesspayrolltab($processed = 0)
    {
        $this->set('tab', $processed);
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
                array('Payrollmaster.action' => NULL)
            )
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }

        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }

        $arr_conditions[] = 'emp_fkey not in (select emp_fkey from attendance_register where month_year="' . $_REQUEST['month'] . '" and isdelete = "Y" )';

        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=2',
                    'Payrolltransactions.final_status is null',
                    'Payrolltransactions.payroll_status is null'
                )
            )
        );
        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*,Payrolltransactions.reverse_remarks',
                'conditions' => $arr_conditions,
                'joins' => $joins,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        //        $log = $this->Payrollmaster->getDataSource(); 
        //        debug($log);
        foreach ($arr_payroll as $key => $value) {
            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollmaster']['loss_of_pay'];
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deductions'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['reverse_remarks'] = isset($value['Payrolltransactions']['reverse_remarks']) ? $value['Payrolltransactions']['reverse_remarks'] : '';
        }

        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
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
            $arr_conditions[] = 'branch_code="' . $branches . '"';
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
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*',
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
            //Edited by Akshay on 24-7-2023
            $payroll_pkey = isset($value['Payrollmaster']['payroll_master_pkey']) ? $value['Payrollmaster']['payroll_master_pkey'] : '';
            $arr_remarks = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'provisional' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['remarks'] = isset($arr_remarks[0]['payroll_transactions']['remarks']) ? $arr_remarks[0]['payroll_transactions']['remarks'] : '';

            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = round($arr_prev_month_net_salary[$emp_fkey]);
                $resp_payroll["rows"][$key]['diff'] =  round($value['Payrollmaster']['net_salary']) - round($arr_prev_month_net_salary[$emp_fkey]);
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
                $resp_payroll["rows"][$key]['diff'] = '';
            }
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function processpayroll()
    { // process provisional process1
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $success = 0;
        $arr_requestdata = $this->request->data;
        // debug($arr_requestdata); 
        try {
            if (isset($arr_requestdata["emp_pkey"])) {
                $arr_emp_pkeys = isset($arr_requestdata["emp_pkey"]) ? explode(",", $arr_requestdata["emp_pkey"]) : array();
                function removeEmptyElements($value) {
                    // Remove elements that are empty or contain only whitespace
                    return !empty(trim($value));
                }
                $arr_emp_pkeys = array_filter($arr_emp_pkeys, 'removeEmptyElements');
                // debug($arr_emp_pkeys);exit;
                $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
                $arr_payroll_pkeys = array_filter( $arr_payroll_pkeys, 'removeEmptyElements');
                $count = count($arr_payroll_pkeys);
                $arr_data['emp'] = $arr_emp_pkeys;
                // debug($arr_data);
                $arr_data1['payroll'] = $arr_payroll_pkeys;
                if ($arr_requestdata['tax'] == '1') {
                    foreach ($arr_data1['payroll'] as $value) {
                        $this->Payrollmaster->query("UPDATE payroll_master SET tax_include = 'Y' WHERE payroll_master.payroll_master_pkey = '$value'");
                    }
                }
                if ($arr_requestdata['tax'] == '0') {
                    foreach ($arr_data1['payroll'] as $value) {
                        $this->Payrollmaster->query("UPDATE payroll_master SET tax_include = 'N' WHERE payroll_master.payroll_master_pkey = '$value'");
                    }
                }
                for ($i = 0; $i < $count; $i++) {
                    $outputParameter = array();
                    $outputParameter[] = (isset($arr_requestdata['month']) && $arr_requestdata['month'] != '') ? $arr_requestdata['month'] : '';
                    $outputParameter[] = (isset($arr_requestdata['branch']) && $arr_requestdata['branch'] != '') ? $arr_requestdata['branch'] : '';
                    $outputParameter[] = $arr_data['emp'][$i];
                    $outputParameter[] = $arr_data1['payroll'][$i];
                    $outputParameter[] = $this->Session->read("login_user_id"); //user id
                    $out = $this->AttendanceRegister->salaryProcessPrc($outputParameter);
                    //Edited by Akshay on 23-7-2023
                    $month_year = (isset($arr_requestdata['month']) && $arr_requestdata['month'] != '') ? $arr_requestdata['month'] : '';
                    $emp_fkey = isset($arr_data['emp'][$i]) ? $arr_data['emp'][$i] : '';
                    $payroll_master_fkey = isset($arr_data1['payroll'][$i]) ? $arr_data1['payroll'][$i] : '';

                    $approved_by = $this->Session->read("login_user_id");
                    $approved_time = date('Y-m-d H:i:s'); // Current date and time  
                    $remarks =  (isset($arr_requestdata['remarks']) && $arr_requestdata['remarks'] != '') ? $arr_requestdata['remarks'] : '';


                    try {
                        $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET status = 0 WHERE payroll_master_fkey = '$payroll_master_fkey' and (final_status is Null or payroll_status ='provisional')");
                    } catch (Exception $ex) {
                        echo $ex->getMessage();
                    }
                    $result1 = $this->Payrollmaster->query("INSERT INTO payroll_transactions (month_year, emp_pkey, payroll_master_fkey, approved_by, approved_time, remarks, status, payroll_status,final_status)
                VALUES ('$month_year', '$emp_fkey', '$payroll_master_fkey', '$approved_by', '$approved_time', '$remarks', '1', 'provisional','provisional')");
                    try {
                        $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET final_status = 'provisional' WHERE payroll_master_fkey = '$payroll_master_fkey' ");
                    } catch (Exception $ex) {
                        echo $ex->getMessage();
                    }
                    //Update salary_amount after procedure executed on emp_salary_slip
                    //Fetch formula from remarks
                    $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds');
                    $arr_formulae_from_remarks = $this->EmpSalarySlip->find(
                        "all",
                        array(
                            'fields' => 'emp_salary_slip_pkey,head_operator,remarks,salary_head_item_desc',
                            'conditions' => array(
                                'payroll_master_fkey' => $arr_data1['payroll'][$i],
                                'remarks IS NOT NULL',
                                'end_date_effective is null'
                            )
                        )
                    );
                    //				debug($arr_formulae_from_remarks);

                    foreach ($arr_formulae_from_remarks as $row_formulae_from_remarks) {
                        $emp_salary_slip_pkey = isset($row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey']) ? $row_formulae_from_remarks['EmpSalarySlip']['emp_salary_slip_pkey'] : '';
                        $head_operator = isset($row_formulae_from_remarks['EmpSalarySlip']['head_operator']) ? $row_formulae_from_remarks['EmpSalarySlip']['head_operator'] : '';
                        $formula_from_remarks = isset($row_formulae_from_remarks['EmpSalarySlip']['remarks']) ? preg_replace("/\s+/", "", $row_formulae_from_remarks['EmpSalarySlip']['remarks']) : '';
                        $salary_head_item_desc = isset($row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc']) ? $row_formulae_from_remarks['EmpSalarySlip']['salary_head_item_desc'] : '';
                        if (!empty($formula_from_remarks)) {
                            eval('$salary_amount = ' . $formula_from_remarks . ';');

                            if (trim(strtolower($salary_head_item_desc)) == 'esi' || trim(strtolower($salary_head_item_desc)) == 'esi - employee contribution' || trim(strtolower($salary_head_item_desc)) == 'esi - employer contribution') {
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
                    $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
                    $out = $this->Payrollmaster->taxSalaryProcessPrc($outputParameter);
                    //Ends
                    $success = 1;
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            // debug($ex);
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
        try {
            $success = 1;
            $arr_requestdata = $this->request->data;
            if (isset($arr_requestdata["payroll_pkey"])) {

                $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();

                $final_status = isset($arr_requestdata['final_status']) ? $arr_requestdata['final_status'] : null;


                $date = date('Y-m-d');
                $date1 = date('Y-m-d H:i:s');
                $remarks = isset($arr_requestdata['reverse_remarks']) ? $arr_requestdata['reverse_remarks'] : '';

                $status_value = 2;
                $arr_payroll_pkeys_list = $arr_requestdata['payroll_pkey'];
                $uid = $this->Session->read("login_user_id");
                $str = substr($arr_payroll_pkeys_list, -1);
                if ($str == ',') {
                    $arr_payroll_pkeys_list = substr($arr_payroll_pkeys_list, 0, -1);
                }


                if ($final_status == 'audited') {
                    $payroll_status = 'provisional';
                } else if ($final_status == 'finalization') {
                    $payroll_status = 'audited';
                } else if ($final_status == 'provisional') {
                    $payroll_status = null;
                    $arr_payroll = $this->EmpSalarySlip->query("UPDATE emp_salary_slip  SET end_date_effective = '$date',modified_by = '$uid' WHERE payroll_master_fkey in ($arr_payroll_pkeys_list) and end_date_effective is null
                                                                AND head_type != 'Arrear' -- Edited by Akshay on 19-8-2025
                                                                ");
                } else {
                    $payroll_status = null;
                }
                if ($payroll_status) {
                    $arr_payroll_status_transactions = $this->EmpSalarySlip->query("UPDATE payroll_transactions SET  final_status = '$payroll_status' WHERE payroll_master_fkey in ($arr_payroll_pkeys_list) ");
                    $arr_payroll_transactions = $this->EmpSalarySlip->query("UPDATE payroll_transactions SET status = '$status_value', modified_by = '$uid', modified_time = '$date1', reverse_remarks ='$remarks', payroll_status = '$payroll_status' WHERE payroll_master_fkey in ($arr_payroll_pkeys_list) AND status = '1' AND payroll_status = '$final_status'");
                } else {
                    $arr_payroll_status_transactions = $this->EmpSalarySlip->query("UPDATE payroll_transactions SET  final_status = null WHERE payroll_master_fkey in ($arr_payroll_pkeys_list) ");
                    $arr_payroll_transactions = $this->EmpSalarySlip->query("UPDATE payroll_transactions SET status = '$status_value', modified_by = '$uid', modified_time = '$date1', reverse_remarks ='$remarks', payroll_status = null WHERE payroll_master_fkey in ($arr_payroll_pkeys_list) AND status = '1' AND payroll_status = '$final_status'");
                }

                if ($payroll_status == null) {
                    $this->Payrollmaster->updateAll(
                        array(
                            'action' => Null
                        ),
                        array(
                            'payroll_master_pkey' => $arr_payroll_pkeys
                        )
                    );
                }
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

        $fields = 'EmpSalarySlip.head_type,EmpSalarySlip.structure_det_value,EmpSalarySlip.salary_head_item_desc,EmpSalarySlip.salary_rate,EmpSalarySlip.salary_amount,Payrollmaster.emp_name,SalaryHeads.head_pkey,SalaryHeads.head_desc';
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
            'EmpSalarySlip.payroll_master_fkey' => $payroll_master_pkey, "end_date_effective is null", "EmpSalarySlip.item_part not in('Indirect')"
        );
        $arr_result = $this->EmpSalarySlip->find(
            "all",
            array(
                'fields' => $fields,
                'joins' => $arr_joins,
                'conditions' => $arr_conditions,
                 // Edited by Akshay on 19-8-2025
                'order' => array(
                    'SalaryHeadItems.head_fkey' => 'ASC',
                    'EmpSalarySlip.salary_head_item_fkey' => 'ASC'
                )
                // End
            )
        );
        $arr_conditions1 = array(
            'EmpSalarySlip.payroll_master_fkey' => $payroll_master_pkey, "end_date_effective is null", "EmpSalarySlip.item_part in('Indirect')"
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
        $this->set('tab', $approved);
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions . ' order by first_name ASC');
        $this->set('arr_employees', $arr_employees);
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
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*',
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
        }

        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function listapprovedpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

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
            $arr_conditions[] = 'branch_code="' . $branches . '"';
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
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*',
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
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function approvepayroll()
    { // salary approval process1
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

    //Edited by Akshay on 20-7-2023
    public function listprovisionalpayroll()
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
            ),
            'AND' => array(
                // array("Payrollmaster.action" => 'Processed'),
                array("Payrolltransactions.payroll_status" => 'provisional'),
            )
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }
        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'
                )
            )
        );
        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*,Payrolltransactions.remarks,Payrolltransactions.final_status',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'joins' => $joins,
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
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deduction'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollmaster']['action'];
            $resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['remarks']) ? $value['Payrolltransactions']['remarks'] : '';
            $resp_payroll["rows"][$key]['final_status'] = isset($value['Payrolltransactions']['final_status']) ? $value['Payrolltransactions']['final_status'] : '';

            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            //Edited by Akshay on 24-7-2023
            //            $payroll_pkey = isset($value['Payrollmaster']['payroll_master_pkey'])? $value['Payrollmaster']['payroll_master_pkey']:'';  
            //            $arr_remarks = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'provisional' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            //            $resp_payroll["rows"][$key]['remarks'] = isset($arr_remarks[0]['payroll_transactions']['remarks'])? $arr_remarks[0]['payroll_transactions']['remarks']:'';

            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = round($arr_prev_month_net_salary[$emp_fkey]);
                $resp_payroll["rows"][$key]['diff'] =  round($value['Payrollmaster']['net_salary']) - round($arr_prev_month_net_salary[$emp_fkey]);
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
                $resp_payroll["rows"][$key]['diff'] = '';
            }
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }
    public function showprovisionalpayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        /* $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
          $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
          $this->set('arr_employees', $arr_employees); */
    }

    public function showprovisionalpayrolltab($processed = 0)
    {
        $this->set('tab', $processed);
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions . ' order by first_name ASC');
        $this->set('arr_employees', $arr_employees);
        //end Employee company id added
    }
    public function addremarks($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }

    //Edited by Akshay on 24-7-2023

    public function addreversalremarks($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }

    //Edited by Akshay on 21-9-2023
    public function addrejectremarks($payroll_master_pkey = '', $status = '')
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
        $this->set('status', $status);
    }

    public function addreversalremarks2($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }


    public function addreversalremarks3($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }

    public function addremarks2($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }
    public function addremarks3($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }
    public function addremarks4($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }
    public function addremarks5($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }
    public function showpreauditpayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        /* $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
          $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
          $this->set('arr_employees', $arr_employees); */
    }

    public function showpreauditpayrolltab($approved = 0)
    {
        $this->set('tab', $approved);
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions . ' order by first_name ASC');
        $this->set('arr_employees', $arr_employees);
        //end Employee company id added
    }


    public function addremarkspreaudit($payroll_master_pkey = 0)
    {
        $this->set('payroll_pkey', $payroll_master_pkey);
    }

    public function preauditpayroll()
    { //To process preaudit data1
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');
        function removeEmptyElements($value) {
            // Remove elements that are empty or contain only whitespace
            return !empty(trim($value));
        }

        $arr_requestdata = $this->request->data;
        $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
        
        $arr_payroll_pkeys = array_filter($arr_payroll_pkeys, 'removeEmptyElements');

        $remarks = $arr_requestdata["remarks"];
        $arr_data = array(
            'Payrollmaster.action' => "'Processed'"
        );

        $arr_data1 = array(
            'Payrolltransactions.remarks' => "$remarks",
            'Payrolltransactions.status' => 1
        );


        $arr_pkey = explode(",", $arr_requestdata["payroll_pkey"]);
        // debug($arr_pkey); exit;

        $arr_pkey = array_filter($arr_pkey, 'removeEmptyElements');

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
            // debug($arr_payroll);
            $branch_code = isset($arr_payroll['0']['Payrollmaster']['branch_code']) ? $arr_payroll['0']['Payrollmaster']['branch_code'] : '';
            $month = isset($arr_payroll['0']['Payrollmaster']['month_year']) ? $arr_payroll['0']['Payrollmaster']['month_year'] : '';
            $emp_fkey = isset($arr_payroll['0']['Payrollmaster']['emp_fkey']) ? $arr_payroll['0']['Payrollmaster']['emp_fkey'] : '';
            $uid = $this->Session->read("login_user_id");
            //$arr_payroll = $this->Payrollmaster->query("call payroll_master_approve('$branch_code','$month','$emp_fkey','$uid', @`perror_message`); ");

        }
        //        $this->Payrollmaster->updateAll(
        //            $arr_data,
        //            array('Payrollmaster.payroll_master_pkey' => $arr_payroll_pkeys,)
        //        );
        $date1 = date('Y-m-d H:i:s');
        foreach ($arr_payroll_pkeys as $payroll_fkey) {

            $approved_by = $this->Session->read("login_user_id");
            $approved_time = date('Y-m-d H:i:s'); // Current date and time  


            try {
                $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET status = 0 WHERE payroll_master_fkey = $payroll_fkey and  final_status ='provisional' and status = 2");
            } catch (Exception $ex) {
            }
            $result1 = $this->Payrollmaster->query("INSERT INTO payroll_transactions (month_year, emp_pkey, payroll_master_fkey, approved_by, approved_time, remarks, status, payroll_status,final_status)
                VALUES ('$month', '$emp_fkey', '$payroll_fkey', '$approved_by', '$approved_time', '$remarks', '1', 'audited','audited')");
            try {
                $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET final_status = 'audited' WHERE payroll_master_fkey = $payroll_fkey ");
            } catch (Exception $ex) {
            }
        }



        $result = array('success' => 1);
        echo json_encode($result);
    }

    public function listpreauditpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'AND' => array(
                array('Payrollmaster.action' => 'Processed'),
                array('Payrolltransactions.payroll_status' => 'provisional'),
                array('Payrolltransactions.final_status' => 'provisional'),
            ),
        );

        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
            $arr_prev_month_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';

            $prevmonth = date('Y-m', strtotime('-1 month', strtotime($_REQUEST['month'])));
            $arr_prev_month_conditions[] = 'Payrollmaster.month_year="' . $prevmonth . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
            $arr_prev_month_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }
        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'
                )
            )
        );
        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'joins' => $joins,
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        //        $log = $this->Payrollmaster->getDataSource()->getLog(false, false);
        //debug($log);
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
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deduction'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollmaster']['action'];
            //$resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['reverse_remarks'])?$value['Payrolltransactions']['reverse_remarks']:'';
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = $arr_prev_month_net_salary[$emp_fkey];
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
            }
            $payroll_pkey = $value['Payrollmaster']['payroll_master_pkey'];
            $arr_remarks = $this->Payrollmaster->query("SELECT reverse_remarks FROM payroll_transactions WHERE payroll_status = 'provisional' AND status = '2' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['remarks'] = isset($arr_remarks[0]['payroll_transactions']['reverse_remarks']) ? $arr_remarks[0]['payroll_transactions']['reverse_remarks'] : '';
        }

        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function listpreauditedpayroll()
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
            ),
            'AND' => array(
                // array('Payrollmaster.action' => 'Processed'),
                array('Payrolltransactions.payroll_status' => 'audited')
            ),
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollmaster.branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'Payrollmaster.emp_fkey=' . $_REQUEST['employee'];
        }

        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'
                )
            )
        );

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins,));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*,Payrolltransactions.remarks,Payrolltransactions.final_status',
                'conditions' => $arr_conditions,
                'joins' => $joins,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        // Edited by Akshay on 26-9-2023
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
            $resp_payroll["rows"][$key]['days_present'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['days_lop'] = $value['Payrollmaster']['loss_of_pay'];
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deductions'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollmaster']['action'];
            $resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['remarks']) ? $value['Payrolltransactions']['remarks'] : '';
            $resp_payroll["rows"][$key]['final_status'] = isset($value['Payrolltransactions']['final_status']) ? $value['Payrolltransactions']['final_status'] : '';
            
            //Edited by Akshay 
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = round($arr_prev_month_net_salary[$emp_fkey]);
                $resp_payroll["rows"][$key]['diff'] =  round($value['Payrollmaster']['net_salary']) - round($arr_prev_month_net_salary[$emp_fkey]);
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
                $resp_payroll["rows"][$key]['diff'] = '';
            }
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function showfinalizationpayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        /* $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
          $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
          $this->set('arr_employees', $arr_employees); */
    }

    public function showfinalizationpayrolltab($processed = 0)
    {
        $this->set('tab', $processed);
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions . ' order by first_name ASC');
        $this->set('arr_employees', $arr_employees);
        //end Employee company id added
    }

    public function listfinalizationpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'AND' => array(
                array('Payrollmaster.action' => 'Processed'),
                array('Payrolltransactions.final_status' => 'audited'),
                array('Payrolltransactions.payroll_status' => 'audited'),
            )
        );
        $arr_prev_month_conditions = array();
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
            $arr_prev_month_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';

            $prevmonth = date('Y-m', strtotime('-1 month', strtotime($_REQUEST['month'])));
            $arr_prev_month_conditions[] = 'Payrollmaster.month_year="' . $prevmonth . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
            $arr_prev_month_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }
        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'

                )
            )
        );
        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*,Payrolltransactions.reject_remarks',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'joins' => $joins,
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        //        $log = $this->Payrollmaster->getDataSource(); 
        //       debug($log);
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
        // debug($arr_payroll);
        foreach ($arr_payroll as $key => $value) {
            // debug($value);
            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollmaster']['loss_of_pay'];
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deduction'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['reject_remarks']) ? $value['Payrolltransactions']['reject_remarks'] : '';
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = $arr_prev_month_net_salary[$emp_fkey];
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
            }
            // $payroll_pkey = $value['Payrollmaster']['payroll_master_pkey'];
            // $arr_remarks = $this->Payrollmaster->query("SELECT reverse_remarks FROM payroll_transactions WHERE payroll_status = 'audited' and final_status = 'audited' AND status = '2' AND payroll_master_fkey = $payroll_pkey");
            // $resp_payroll["rows"][$key]['remarks'] = isset($arr_remarks[0]['payroll_transactions']['reverse_remarks']) ? $arr_remarks[0]['payroll_transactions']['reverse_remarks'] : '';
        }

        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function listfinalizedpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'AND' => array(
                array('Payrollmaster.action' => 'Approved'),
                array('Payrolltransactions.payroll_status' => 'finalization')
            ),
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollmaster.branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'Payrollmaster.emp_fkey=' . $_REQUEST['employee'];
        }

        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'
                )
            )
        );

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, "joins" => $joins, "group" => "Payrollmaster.payroll_master_pkey")); // Edited by Akshay on 1-3-2025
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*,Payrolltransactions.remarks',
                'conditions' => $arr_conditions,
                'joins' => $joins,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst),
                'group' => 'Payrollmaster.payroll_master_pkey' // Edited by Akshay on 1-3-2025
            )
        );

        //Edited by Akshay on 26-9-2023
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
            $resp_payroll["rows"][$key]['days_present'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['days_lop'] = $value['Payrollmaster']['loss_of_pay'];
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deductions'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollmaster']['action'];
            $resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['remarks']) ? $value['Payrolltransactions']['remarks'] : '';
            
            //Edited by Akshay on 26-9-2023
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = round($arr_prev_month_net_salary[$emp_fkey]);
                $resp_payroll["rows"][$key]['diff'] =  round($value['Payrollmaster']['net_salary']) - round($arr_prev_month_net_salary[$emp_fkey]);
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
                $resp_payroll["rows"][$key]['diff'] = '';
            }
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function finalizationpayroll()
    { // To process finalize process
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');

        $arr_requestdata = $this->request->data;
        $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
        function removeEmptyElements($value) {
            // Remove elements that are empty or contain only whitespace
            return !empty(trim($value));
        }
        $arr_payroll_pkeys = array_filter($arr_payroll_pkeys, 'removeEmptyElements');
        $remarks = $arr_requestdata["remarks"];
        $arr_data = array(
            'Payrollmaster.action' => "'Approved'"
        );

        $arr_data1 = array(
            'Payrolltransactions.remarks' => "$remarks",
            'Payrolltransactions.status' => 1
        );


        $arr_pkey = explode(",", $arr_requestdata["payroll_pkey"]);
        $arr_pkey = array_filter($arr_pkey, 'removeEmptyElements');
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
            $uid = $this->Session->read("login_user_id");
            $arr_payroll = $this->Payrollmaster->query("call payroll_master_approve('$branch_code','$month','$emp_fkey','$uid', @`perror_message`); ");
        }
        $this->Payrollmaster->updateAll(
            $arr_data,
            array('Payrollmaster.payroll_master_pkey' => $arr_payroll_pkeys,)
        );
        $date1 = date('Y-m-d H:i:s');
        foreach ($arr_payroll_pkeys as $payroll_fkey) {

            $approved_by = $this->Session->read("login_user_id");
            $approved_time = date('Y-m-d H:i:s'); // Current date and time  

            try {
                $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET final_status = 'finalization' WHERE payroll_master_fkey = $payroll_fkey ");
            } catch (Exception $ex) {
            }
            try {
                $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET status = 0 WHERE payroll_master_fkey = $payroll_fkey and status = 2");
            } catch (Exception $ex) {
            }
            $result1 = $this->Payrollmaster->query("INSERT INTO payroll_transactions (month_year, emp_pkey, payroll_master_fkey, approved_by, approved_time, remarks, status, payroll_status,final_status)
                VALUES ('$month', '$emp_fkey', '$payroll_fkey', '$approved_by', '$approved_time', '$remarks', '1', 'finalization','finalization')");
        }



        $result = array('success' => 1);
        echo json_encode($result);
    }

    //Payment

    public function paymentprocessedpayrolllist()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            "Payrollmaster.action" => 'Processed'
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
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

        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1',
                    'Payrolltransactions.payroll_status="payed"'
                )
            )
        );

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'joins' => $joins,
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
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function paymentpayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        /* $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
          $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
          $this->set('arr_employees', $arr_employees); */
    }
    public function paymentpayrolltab($processed = 0, $month1 = '', $branch1 = '')
    {
        $this->set('tab', $processed);
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;

        //Edited by Akshay on 22-9-2023
        if ($branch1 == '') {
            $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        } else {
            $branch = $branch1;
        }


        //Edited by Akshay on 21-9-2023
        if ($month1 == '') {
            $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
        } else {
            $month = $month1;
        }
        //edited by arul on 12/12/2019 Employee company id added
        //        $arr_conditions = array('status' => 1);
        //        if ($branch != '') {
        //            $arr_conditions['branch_code'] = $branch;
        //        }
        //        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => $arr_conditions)));
        //        $this->set('arr_employees', $arr_employees);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $arr_total = $this->EmployeeDetails->query("select sum(net_salary),count(*) from payroll_master where month_year='$month' and action='Approved' ");
       // $arr_total = $this->EmployeeDetails->query("select sum(net_salary),count(*) from payroll_master where month_year='$month' and branch_code = '$branch' and action='Approved' and payroll_master_pkey in (select distinct payroll_master_fkey from payroll_transactions where payroll_status = 'salaryapproval' and month_year = '$month' and final_status in ('salaryapproval','paymentapproval') and status = '1' ) ");
        $arr_total = $this->EmployeeDetails->query("select sum(round(net_salary)),count(*) from payroll_master where month_year='$month' and branch_code = '$branch' and action='Approved' and payroll_master_pkey in (select payroll_master_fkey from payroll_transactions where final_status in ('salaryapproval') and status = '1');");
        $sum = isset($arr_total[0][0]['sum(round(net_salary))']) ? $arr_total[0][0]['sum(round(net_salary))'] : 0;
        $total = isset($arr_total[0][0]['count(*)']) ? $arr_total[0][0]['count(*)'] : 0;
        $this->set('sum', $sum);
        $this->set('total', $total);
        
        $arr_total1 = $this->EmployeeDetails->query("select sum(round(net_salary)),count(*) from payroll_master where month_year='$month' and branch_code = '$branch' and action='Approved' and payroll_master_pkey in (select payroll_master_fkey from payroll_transactions where final_status in ('paymentapproval') and status = '1');");
        $sum1 = isset($arr_total1[0][0]['sum(round(net_salary))']) ? $arr_total1[0][0]['sum(round(net_salary))'] : 0;
        $total1 = isset($arr_total1[0][0]['count(*)']) ? $arr_total1[0][0]['count(*)'] : 0;
        $this->set('sum1', $sum1);
        $this->set('total1', $total1);

        $arr_conditions = "";
        if ($branch != '') {
            $arr_conditions = 'and branch_code="' . $branch . '"';
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions . ' order by first_name ASC');
        $this->set('arr_employees', $arr_employees);
        //end Employee company id added
    }

    public function listpaymentapprovalpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'AND' => array(
                array('Payrollmaster.action' => 'Approved'),
                array('Payrolltransactions.final_status' => 'salaryapproval'),
                array('Payrolltransactions.payroll_status' => 'salaryapproval'),
            )
        );
        $arr_prev_month_conditions = array();
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
            $arr_prev_month_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';

            $prevmonth = date('Y-m', strtotime('-1 month', strtotime($_REQUEST['month'])));
            $arr_prev_month_conditions[] = 'Payrollmaster.month_year="' . $prevmonth . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
            $arr_prev_month_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }
        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'

                )
            )
        );
        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'joins' => $joins,
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        //        $log = $this->Payrollmaster->getDataSource(); 
        //       debug($log);
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

        // debug($arr_payroll);

        foreach ($arr_payroll as $key => $value) {
            $payroll_pkey = $value['Payrollmaster']['payroll_master_pkey'];
            $arr_remarks = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'provisional' and final_status = 'salaryapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['provisional_remarks'] = isset($arr_remarks[0]['payroll_transactions']['remarks']) ? $arr_remarks[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_aud = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'audited' and final_status = 'salaryapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['finance_remarks'] = isset($arr_remarks_aud[0]['payroll_transactions']['remarks']) ? $arr_remarks_aud[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_fin = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'finalization' and final_status = 'salaryapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['final_remarks'] = isset($arr_remarks_fin[0]['payroll_transactions']['remarks']) ? $arr_remarks_fin[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_approval = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'salaryapproval' and final_status = 'salaryapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['salary_remarks'] = isset($arr_remarks_approval[0]['payroll_transactions']['remarks']) ? $arr_remarks_approval[0]['payroll_transactions']['remarks'] : '';

            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollmaster']['loss_of_pay'];
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deduction'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['reverse_remarks']) ? $value['Payrolltransactions']['reverse_remarks'] : '';
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = $arr_prev_month_net_salary[$emp_fkey];
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
            }
            //                      $payroll_pkey = $value['Payrollmaster']['payroll_master_pkey'];
            //           $arr_remarks = $this->Payrollmaster->query("SELECT reverse_remarks FROM payroll_transactions WHERE payroll_status = 'audited' and final_status = 'audited' AND status = '2' AND payroll_master_fkey = $payroll_pkey");
            //           $resp_payroll["rows"][$key]['remarks'] = isset($arr_remarks[0]['payroll_transactions']['reverse_remarks'])? $arr_remarks[0]['payroll_transactions']['reverse_remarks']:'';

        }
        // debug($resp_payroll);

        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }
    public function PaymentapprovePayroll()
    { // To process payment approval of payroll
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');

        $arr_requestdata = $this->request->data;
        // debug($arr_requestdata); exit;
        $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
        $remarks = $arr_requestdata["remarks"];
        $arr_data = array(
            'Payrollmaster.action' => "'Approved'"
        );

        $arr_data1 = array(
            'Payrolltransactions.remarks' => "$remarks",
            'Payrolltransactions.status' => 1
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
            $uid = $this->Session->read("login_user_id");
            // $arr_payroll = $this->Payrollmaster->query("call payroll_master_approve('$branch_code','$month','$emp_fkey','$uid', @`perror_message`); ");

        }
        //        $this->Payrollmaster->updateAll(
        //            $arr_data,
        //            array('Payrollmaster.payroll_master_pkey' => $arr_payroll_pkeys,)
        //        );
        $date1 = date('Y-m-d H:i:s');
        foreach ($arr_payroll_pkeys as $payroll_fkey) {

            $approved_by = $this->Session->read("login_user_id");
            $approved_time = date('Y-m-d H:i:s'); // Current date and time  

            try {
                $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET final_status = 'paymentapproval' WHERE payroll_master_fkey = $payroll_fkey ");
            } catch (Exception $ex) {
            }
            try {
                $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET status = 0 WHERE payroll_master_fkey = $payroll_fkey and status = 2");
            } catch (Exception $ex) {
            }
            $result1 = $this->Payrollmaster->query("INSERT INTO payroll_transactions (month_year, emp_pkey, payroll_master_fkey, approved_by, approved_time, remarks, status, payroll_status,final_status)
                VALUES ('$month', '$emp_fkey', '$payroll_fkey', '$approved_by', '$approved_time', '$remarks', '1', 'paymentapproval','paymentapproval')");
        }



        $result = array('success' => 1);
        echo json_encode($result);
    }
    public function listpaymentapprovedpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'AND' => array(
                array('Payrollmaster.action' => 'Approved'),
                array('Payrolltransactions.payroll_status' => 'paymentapproval'),
                array('Payrolltransactions.final_status' => 'paymentapproval'),
            ),
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollmaster.branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'Payrollmaster.emp_fkey=' . $_REQUEST['employee'];
        }

        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'
                )
            )
        );

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*,Payrolltransactions.remarks',
                'conditions' => $arr_conditions,
                'joins' => $joins,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        foreach ($arr_payroll as $key => $value) {
            $payroll_pkey = $value['Payrollmaster']['payroll_master_pkey'];
            $arr_remarks = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE month_year = '$month' and payroll_status = 'provisional' and final_status = 'paymentapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['provisional_remarks'] = isset($arr_remarks[0]['payroll_transactions']['remarks']) ? $arr_remarks[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_aud = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE month_year = '$month' and payroll_status = 'audited' and final_status = 'paymentapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['finance_remarks'] = isset($arr_remarks_aud[0]['payroll_transactions']['remarks']) ? $arr_remarks_aud[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_fin = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE month_year = '$month' and payroll_status = 'finalization' and final_status = 'paymentapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['final_remarks'] = isset($arr_remarks_fin[0]['payroll_transactions']['remarks']) ? $arr_remarks_fin[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_sal = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE month_year = '$month' and payroll_status = 'salaryapproval' and final_status = 'paymentapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['salary_remarks'] = isset($arr_remarks_sal[0]['payroll_transactions']['remarks']) ? $arr_remarks_sal[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_pay = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE month_year = '$month' and payroll_status = 'paymentapproval' and final_status = 'paymentapproval' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['payment_remarks'] = isset($arr_remarks_pay[0]['payroll_transactions']['remarks']) ? $arr_remarks_pay[0]['payroll_transactions']['remarks'] : '';

            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_present'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['days_lop'] = $value['Payrollmaster']['loss_of_pay'];
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deductions'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollmaster']['action'];
            $resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['remarks']) ? $value['Payrolltransactions']['remarks'] : '';
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }

    public function processpayment()
    { // 
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');

        $arr_requestdata = $this->request->data;
        //debug($arr_requestdata); exit;
        $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
        //debug($arr_payroll_pkeys); exit;
        $remarks = $arr_requestdata["remarks"];
        $arr_data = array(
            'Payrollmaster.action' => "'Processed'"
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
            array('Payrollmaster.payroll_master_pkey' => $arr_payroll_pkeys,)
        );
        //Edited by Akshay on 24-7-2023
        $date1 = date('Y-m-d H:i:s');
        foreach ($arr_payroll_pkeys as $payroll_fkey) {
            // Prepare the conditions array for the current payroll_fkey
            $conditions = array(
                'Payrolltransactions.payroll_master_fkey' => $payroll_fkey,
                'Payrolltransactions.payroll_status' => 'audited',
                'Payrolltransactions.status' => 1
            );

            $conditions1 = array(
                'Payrolltransactions.payroll_master_fkey' => $payroll_fkey,
                'Payrolltransactions.status' => 1
            );

            // Prepare the data array with the updated values
            $arr_data1 = array(
                'Payrolltransactions.remarks' => $this->Payrolltransactions->getDataSource()->value($remarks, 'string'), // Properly escape the string value
                'Payrolltransactions.payroll_status' => "'finalized'",
                'Payrolltransactions.modified_by' => $this->Payrolltransactions->getDataSource()->value($uid, 'string'), // Properly escape the string value
                'Payrolltransactions.modified_time' => $this->Payrolltransactions->getDataSource()->value($date1, 'datetime') // Properly escape the datetime value
            );

            $arr_data2 = array(
                'Payrolltransactions.final_status' => "'finalized'"
            );

            // Call the updateAll method with the data and conditions arrays for each payroll_fkey
            $this->Payrolltransactions->updateAll($arr_data1, $conditions);
            $this->Payrolltransactions->updateAll($arr_data2, $conditions1);
        }



        $result = array('success' => 1);
        echo json_encode($result);
    }
    //Approval payroll
    public function approvalpayroll()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        /* $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
          $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
          $this->set('arr_employees', $arr_employees); */
    }

    public function approvalpayrolltab($processed = 0, $month1 = '', $branch1 = '')
    {
        $this->set('tab', $processed);
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
      
        if ($branch1 == '') {
            $branch = isset($arr_requestdata['branch']) ? $arr_requestdata['branch'] : '';
        } else {
            $branch = $branch1;
        }

        if ($month1 == '') {
            $month = isset($arr_requestdata['month']) ? $arr_requestdata['month'] : '';
        } else {
            $month = $month1;
        }

        //edited by arul on 12/12/2019 Employee company id added
        //        $arr_conditions = array('status' => 1);
        //        if ($branch != '') {
        //            $arr_conditions['branch_code'] = $branch;
        //        }
        //        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => $arr_conditions)));
        //        $this->set('arr_employees', $arr_employees);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_total = $this->EmployeeDetails->query("select sum(round(net_salary)),count(*) from payroll_master where month_year='$month' and branch_code = '$branch' and action='Approved' and payroll_master_pkey in (select distinct payroll_master_fkey from payroll_transactions where  month_year = '$month' and final_status in ('finalization') and status = '1' ) ");
       // $arr_total = $this->EmployeeDetails->query("select sum(round(net_salary)),count(*) from payroll_master where month_year='$month' and branch_code = '$branch' and action='Approved';");
       
        $sum = isset($arr_total[0][0]['sum(round(net_salary))']) ? $arr_total[0][0]['sum(round(net_salary))'] : 0;
        $total = isset($arr_total[0][0]['count(*)']) ? $arr_total[0][0]['count(*)'] : 0;
        $this->set('sum', $sum);
        $this->set('total', $total);
        $arr_total1 = $this->EmployeeDetails->query("select sum(round(net_salary)),count(*) from payroll_master where month_year='$month' and branch_code = '$branch' and action='Approved' and payroll_master_pkey in (select distinct payroll_master_fkey from payroll_transactions where  month_year = '$month' and final_status in ('salaryapproval','paymentapproval') and status = '1' ) ");
        $sum1 = isset($arr_total1[0][0]['sum(round(net_salary))']) ? $arr_total1[0][0]['sum(round(net_salary))'] : 0;
        $total1 = isset($arr_total1[0][0]['count(*)']) ? $arr_total1[0][0]['count(*)'] : 0;
        $this->set('sum1', $sum1);
        $this->set('total1', $total1);
        $arr_conditions = "";
        if ($branch != '') {
            $arr_conditions = 'and branch_code="' . $branch . '"';
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on emp_pkey=emp_fkey where emp_details.status=1 ' . $arr_conditions . ' order by first_name ASC');
        $this->set('arr_employees', $arr_employees);
        //end Employee company id added
    }

    public function processapprovalpayroll()
    { // To process salary approval process1
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');

        $arr_requestdata = $this->request->data;
        //debug($arr_requestdata); exit;
        $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
        //debug($arr_payroll_pkeys); exit;
        $remarks = $arr_requestdata["remarks"];
        $arr_data = array(
            'Payrollmaster.action' => "'Processed'"
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
            array('Payrollmaster.payroll_master_pkey' => $arr_payroll_pkeys,)
        );
        //Edited by Akshay on 24-7-2023
        $date1 = date('Y-m-d H:i:s');
        foreach ($arr_payroll_pkeys as $payroll_fkey) {
            // Prepare the conditions array for the current payroll_fkey
            $conditions = array(
                'Payrolltransactions.payroll_master_fkey' => $payroll_fkey,
                'Payrolltransactions.payroll_status' => 'finalized',
                'Payrolltransactions.status' => 1
            );
            $conditions1 = array(
                'Payrolltransactions.payroll_master_fkey' => $payroll_fkey,
                'Payrolltransactions.status' => 1
            );

            // Prepare the data array with the updated values
            $arr_data1 = array(
                'Payrolltransactions.remarks' => $this->Payrolltransactions->getDataSource()->value($remarks, 'string'), // Properly escape the string value
                'Payrolltransactions.payroll_status' => "'Approved'",
                'Payrolltransactions.modified_by' => $this->Payrolltransactions->getDataSource()->value($uid, 'string'), // Properly escape the string value
                'Payrolltransactions.modified_time' => $this->Payrolltransactions->getDataSource()->value($date1, 'datetime') // Properly escape the datetime value
            );
            $arr_data2 = array(
                'Payrolltransactions.final_status' => "'Approved'",
            );

            // Call the updateAll method with the data and conditions arrays for each payroll_fkey
            $this->Payrolltransactions->updateAll($arr_data1, $conditions);
            $this->Payrolltransactions->updateAll($arr_data2, $conditions1);
        }



        $result = array('success' => 1);
        echo json_encode($result);
    }
    public function listsalaryapprovalpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'AND' => array(
                array('Payrollmaster.action' => 'Approved'),
                array('Payrolltransactions.final_status' => 'finalization'),
                array('Payrolltransactions.payroll_status' => 'finalization'),
            )
        );
        $arr_prev_month_conditions = array();
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'branch_code="' . $branches . '"';
            $arr_prev_month_conditions[] = 'branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';

            $prevmonth = date('Y-m', strtotime('-1 month', strtotime($_REQUEST['month'])));
            $arr_prev_month_conditions[] = 'Payrollmaster.month_year="' . $prevmonth . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
            $arr_prev_month_conditions[] = 'emp_fkey=' . $_REQUEST['employee'];
        }
        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'

                )
            )
        );
        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins, "group" => "Payrollmaster.payroll_master_pkey"));// Edited by Akshay on 1-3-2025
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*',
                'conditions' => $arr_conditions,
                'order' => array($sort => $order),
                'joins' => $joins,
                'group' => '`Payrollmaster`.`payroll_master_pkey`',// Edited by Akshay on 1-3-2025
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        //        $log = $this->Payrollmaster->getDataSource(); 
        //       debug($log);
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
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_total = $this->EmployeeDetails->query("select sum(net_salary),count(*) from payroll_master where month_year='$month' and action='Approved' ");
        $sum = isset($arr_total[0][0]['sum(net_salary)']) ? $arr_total[0][0]['sum(net_salary)'] : 0;
        $total = isset($arr_total[0][0]['count(*)']) ? $arr_total[0][0]['count(*)'] : 0;

        $this->set('sum', $sum);
        $this->set('total', $total);
        foreach ($arr_payroll as $key => $value) {
            $payroll_pkey = $value['Payrollmaster']['payroll_master_pkey'];
            $arr_remarks = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'provisional' and final_status = 'finalization' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['provisional_remarks'] = isset($arr_remarks[0]['payroll_transactions']['remarks']) ? $arr_remarks[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_aud = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'audited' and final_status = 'finalization' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['finance_remarks'] = isset($arr_remarks_aud[0]['payroll_transactions']['remarks']) ? $arr_remarks_aud[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_fin = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE payroll_status = 'finalization' and final_status = 'finalization' AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['final_remarks'] = isset($arr_remarks_fin[0]['payroll_transactions']['remarks']) ? $arr_remarks_fin[0]['payroll_transactions']['remarks'] : '';
            
            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_presant'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['loss_of_pay'] = $value['Payrollmaster']['loss_of_pay'];
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deduction'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['reverse_remarks']) ? $value['Payrolltransactions']['reverse_remarks'] : '';
            $emp_fkey = isset($value['Payrollmaster']['emp_fkey']) ? $value['Payrollmaster']['emp_fkey'] : '';
            if (in_array($emp_fkey, array_keys($arr_prev_month_net_salary))) {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = $arr_prev_month_net_salary[$emp_fkey];
            } else {
                $resp_payroll["rows"][$key]['last_month_net_salary'] = '';
            }
            //                      $payroll_pkey = $value['Payrollmaster']['payroll_master_pkey'];
            //           $arr_remarks = $this->Payrollmaster->query("SELECT reverse_remarks FROM payroll_transactions WHERE payroll_status = 'audited' and final_status = 'audited' AND status = '2' AND payroll_master_fkey = $payroll_pkey");
            //           $resp_payroll["rows"][$key]['remarks'] = isset($arr_remarks[0]['payroll_transactions']['reverse_remarks'])? $arr_remarks[0]['payroll_transactions']['reverse_remarks']:'';

        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }
    public function SalaryapprovePayroll()
    { // To process salary approval of payroll
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->Payrolltransactions->useDbConfig = $this->Session->read('ds');

        function removeEmptyElements($value) {
            // Remove elements that are empty or contain only whitespace
            return !empty(trim($value));
        }

        $arr_requestdata = $this->request->data;
        // debug($arr_requestdata); exit;
        $arr_payroll_pkeys = isset($arr_requestdata["payroll_pkey"]) ? explode(",", $arr_requestdata["payroll_pkey"]) : array();
        $arr_payroll_pkeys = array_filter($arr_payroll_pkeys, 'removeEmptyElements');
        $remarks = $arr_requestdata["remarks"];
        $arr_data = array(
            'Payrollmaster.action' => "'Approved'"
        );

        $arr_data1 = array(
            'Payrolltransactions.remarks' => "$remarks",
            'Payrolltransactions.status' => 1
        );


        $arr_pkey = explode(",", $arr_requestdata["payroll_pkey"]);
        $arr_pkey = array_filter($arr_pkey, 'removeEmptyElements');
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
            $uid = $this->Session->read("login_user_id");
            //$arr_payroll = $this->Payrollmaster->query("call payroll_master_approve('$branch_code','$month','$emp_fkey','$uid', @`perror_message`); ");

        }
        //        $this->Payrollmaster->updateAll(
        //            $arr_data,
        //            array('Payrollmaster.payroll_master_pkey' => $arr_payroll_pkeys,)
        //        );
        $date1 = date('Y-m-d H:i:s');
        foreach ($arr_payroll_pkeys as $payroll_fkey) {

            $approved_by = $this->Session->read("login_user_id");
            $approved_time = date('Y-m-d H:i:s'); // Current date and time  

            try {
                $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET final_status = 'salaryapproval' WHERE payroll_master_fkey = $payroll_fkey ");
            } catch (Exception $ex) {
            }
            try {
                $arr_payroll = $this->Payrollmaster->query("UPDATE payroll_transactions  SET status = 0 WHERE payroll_master_fkey = $payroll_fkey and status = 2");
            } catch (Exception $ex) {
            }
            $result1 = $this->Payrollmaster->query("INSERT INTO payroll_transactions (month_year, emp_pkey, payroll_master_fkey, approved_by, approved_time, remarks, status, payroll_status,final_status)
                VALUES ('$month', '$emp_fkey', '$payroll_fkey', '$approved_by', '$approved_time', '$remarks', '1', 'salaryapproval','salaryapproval')");
        }



        $result = array('success' => 1);
        echo json_encode($result);
    }
    public function listsalaryapprovedpayroll()
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $arr_conditions = array(
            'AND' => array(
                array('Payrollmaster.action' => 'Approved'),
                array('Payrolltransactions.payroll_status' => 'salaryapproval')
            ),
        );
        if (isset($_REQUEST['branch']) && $_REQUEST['branch'] != '') {
            $branches = $_REQUEST['branch'];
            $arr_conditions[] = 'Payrollmaster.branch_code="' . $branches . '"';
        }
        if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
            $month = $_REQUEST['month'];
            $arr_conditions[] = 'Payrollmaster.month_year="' . $_REQUEST['month'] . '"';
        } else {
            $month = date('Y-m', strtotime(date('M-Y'))) . '"';
        }
        if (isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') {
            $arr_conditions[] = 'Payrollmaster.emp_fkey=' . $_REQUEST['employee'];
        }

        $joins = array(
            array(
                'table' => 'payroll_transactions',
                'alias' => 'Payrolltransactions',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrolltransactions.payroll_master_fkey = Payrollmaster.payroll_master_pkey',
                    'Payrolltransactions.status=1'
                )
            )
        );

        $resp_payroll = array();
        $resp_payroll["rows"] = array();
        $count = $this->Payrollmaster->find("count", array("conditions" => $arr_conditions, 'joins' => $joins));
        $arr_payroll = $this->Payrollmaster->find(
            "all",
            array(
                'fields' => 'Payrollmaster.*,Payrolltransactions.remarks',
                'conditions' => $arr_conditions,
                'joins' => $joins,
                'order' => array($sort => $order),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            )
        );
        foreach ($arr_payroll as $key => $value) {
            $payroll_pkey = $value['Payrollmaster']['payroll_master_pkey'];
            $arr_remarks = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE month_year = '$month' and payroll_status = 'provisional' and final_status in ('salaryapproval','paymentapproval') AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['provisional_remarks'] = isset($arr_remarks[0]['payroll_transactions']['remarks']) ? $arr_remarks[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_aud = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE month_year = '$month' and payroll_status = 'audited' and final_status  in ('salaryapproval','paymentapproval') AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['finance_remarks'] = isset($arr_remarks_aud[0]['payroll_transactions']['remarks']) ? $arr_remarks_aud[0]['payroll_transactions']['remarks'] : '';
            $arr_remarks_fin = $this->Payrollmaster->query("SELECT remarks FROM payroll_transactions WHERE month_year = '$month' and payroll_status = 'finalization' and final_status  in ('salaryapproval','paymentapproval') AND status = '1' AND payroll_master_fkey = $payroll_pkey");
            $resp_payroll["rows"][$key]['final_remarks'] = isset($arr_remarks_fin[0]['payroll_transactions']['remarks']) ? $arr_remarks_fin[0]['payroll_transactions']['remarks'] : '';

            $resp_payroll["rows"][$key]['payroll_master_pkey'] = $value['Payrollmaster']['payroll_master_pkey'];
            $resp_payroll["rows"][$key]['emp_fkey'] = $value['Payrollmaster']['emp_fkey'];
            $resp_payroll["rows"][$key]['emp_name'] = $value['Payrollmaster']['emp_name'];
            $resp_payroll["rows"][$key]['days_present'] = $value['Payrollmaster']['days_presant'];
            $resp_payroll["rows"][$key]['days_leave'] = $value['Payrollmaster']['days_leave'];
            $resp_payroll["rows"][$key]['days_lop'] = $value['Payrollmaster']['loss_of_pay'];
            $resp_payroll["rows"][$key]['monthly_ctc'] = round($value['Payrollmaster']['monthly_ctc']);
            $resp_payroll["rows"][$key]['monthly_amount'] = round($value['Payrollmaster']['monthly_amount']);
            $resp_payroll["rows"][$key]['calander_days'] = $value['Payrollmaster']['calander_days'];
            $resp_payroll["rows"][$key]['working_days'] = $value['Payrollmaster']['working_days'];
            $resp_payroll["rows"][$key]['gross_salary'] = round($value['Payrollmaster']['gross_salary']);
            $resp_payroll["rows"][$key]['net_salary'] = round($value['Payrollmaster']['net_salary']);
            $resp_payroll["rows"][$key]['total_deductions'] = $value['Payrollmaster']['total_deduction'];
            $resp_payroll["rows"][$key]['action'] = $value['Payrollmaster']['action'];
            $resp_payroll["rows"][$key]['remarks'] = isset($value['Payrolltransactions']['remarks']) ? $value['Payrolltransactions']['remarks'] : '';
        }
        $resp_payroll["total"] = $count;
        echo json_encode($resp_payroll);
    }


    //Edited by Akshay on 22-9-2023
    public function rejectPayrollEntry($payroll_master_pkeys = '', $status = '')
    {
        $this->autoRender = FALSE;
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $this->EmpSalarySlip->useDbConfig = $this->Session->read('ds');
        // debug($payroll_master_pkey);

        try {
            $success = 1;
            $arr_requestdata = $this->request->data;
            $remarks = isset($arr_requestdata['remarks'])? $arr_requestdata['remarks']:'';
            $date = date('Y-m-d');
            $date1 = date('Y-m-d H:i:s');
            $uid = $this->Session->read("login_user_id");

            if ($status == 'finalization' || $status == 'salaryapproval') {
                $payroll_status = 'audited';
            }
            if ($status == 'finalization') {
                $arr_status = "('finalization','salaryapproval')";
            }

            if (!empty($payroll_master_pkeys)) {
                $payroll_master_pkeys = explode(",", $payroll_master_pkeys);
            }
            // debug($payroll_master_pkeys); exit;
           foreach( $payroll_master_pkeys as $payroll_master_pkey) {            
            if( $payroll_master_pkey != '' && isset($payroll_master_pkey) ){                
                $arr_payroll_status_transactions = $this->EmpSalarySlip->query("UPDATE payroll_transactions SET  final_status = 'audited' WHERE payroll_master_fkey = $payroll_master_pkey ");
                $arr_payroll_transactions = $this->EmpSalarySlip->query("UPDATE payroll_transactions SET status = '0', modified_by = '$uid', modified_time = '$date1', reverse_remarks ='$remarks' WHERE payroll_master_fkey = $payroll_master_pkey AND status = '1' AND payroll_status in ('finalization','salaryapproval')");
                $arr_payroll_transactions2 = $this->EmpSalarySlip->query("UPDATE payroll_transactions SET modified_by = '$uid', modified_time = '$date1', reject_remarks ='$remarks' WHERE payroll_master_fkey = $payroll_master_pkey AND status = '1'");
                $arr_payroll_master = $this->EmpSalarySlip->query("UPDATE payroll_master SET action = 'Processed' WHERE payroll_master_pkey = $payroll_master_pkey ");
            }
            }   
            $result = array('success' => $success);
            echo json_encode($result);
        } catch (Exception $ex) {
            $message = 'Something went wrong. Please Try again!!';
            return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'message' => $message));
        }
    }
}
