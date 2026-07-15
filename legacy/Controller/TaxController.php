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
ini_set("display_errors", 0);

App::uses('ConnectionManager', 'Model');

require_once '../Vendor/vendor/autoload.php';

use Smalot\PdfParser\Parser;

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class TaxController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Tax';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeTaxsalsumNew', 'EmpTaxSalTransNew', 'EmpTaxSalTrans', 'EmployeeTaxsalsum', 'EmpTaxRegime', 'TaxSave', 'SalaryHeadItems', 'SalaryHeads', 'FinancialYear', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'EmployeeDetails');
    public $components = array('DatatablesManagement', 'MasterdataManagement');

    /*
     * Dashboard landing view
     */

    public function index()
    {

        $this->layout = null;
        //       debug($_COOKIE[$usr]);
    }
    public function form($tax_detail = 0, $tax_head = 0, $emp = 0)
    {
        $this->set('tax_detail', $tax_detail);
        $this->set('tax_head', $tax_head);
        $this->set('emp', $emp);
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $arr_emp_transaction = $this->EmployeeTaxTransactions->find('count', array('conditions' => array('emp_fkey' => $emp, 'tax_heads_fkey' => $tax_head, 'tax_heads_details_fkey' => $tax_detail)));
        $arr_emp_transaction_all = $this->EmployeeTaxTransactions->find('all', array('conditions' => array('emp_fkey' => $emp, 'tax_heads_fkey' => $tax_head, 'tax_heads_details_fkey' => $tax_detail)));
        $this->set('arr_emp_transaction', $arr_emp_transaction);
    }
    //     public function checkdata($tax_detail = 0,$tax_head = 0,$emp = 0){
    //         $this->autoRender = false;
    //           $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
    //           $arr_emp_transaction = $this->EmployeeTaxTransactions->find('count',array('conditions'=>array('emp_fkey'=>$emp,'tax_heads_fkey'=>$tax_head,'tax_heads_details_fkey'=>$tax_detail)));
    //           $arr_emp_transaction_all = $this->EmployeeTaxTransactions->find('all',array('conditions'=>array('emp_fkey'=>$emp,'tax_heads_fkey'=>$tax_head,'tax_heads_details_fkey'=>$tax_detail)));
    //          // $this->set('arr_emp_transaction',$arr_emp_transaction);
    //          $message = 'Document can be upload';
    //          return json_encode(array('count'=>$arr_emp_transaction,'taxHeadKey'=>$taxHeadKey,'$tax_head'=>$tax_detail,'message'=>$message));
    //                
    //           
    //     }
    public function Tabs()
    {
        $user_group = $this->Session->read('user_group');

        // Edited by Akshay on 1-7-2025
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->set('user_group', $user_group);
        if ($user_group == 1) {
           // $arr_branches = $this->EmployeeTaxTransactions->query("SELECT branch_name, branch_code from branches WHERE status = 1 ORDER BY branch_name ASC;");
           // Edited by Akshay on 16-8-2025
            $arr_branches = $this->EmployeeTaxTransactions->query("SELECT branch_name, branch_code from branches  WHERE status = 1 
                AND branch_code IN (SELECT branch_code FROM fin_year WHERE Year_status = 'OPEN'AND is_current_finyear = 'Y' AND vattr1 = 1 AND status = 1)
                ORDER BY branch_name ASC;");
            // End
           $this->set('arr_branches', $arr_branches);

            $arr_emp_details = $this->EmployeeTaxTransactions->query("SELECT emp_pkey, EmpName, employee_id from employee_info ei WHERE emp_status = 1 ORDER BY EmpName ASC;");
            // debug($arr_emp_details );
            $this->set('arr_emp_details', $arr_emp_details);
        }

        // End

        if ($user_group == 2) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $this->setup($emp_fkey);
        }
    }
    public function Calculate($ctc = 0)
    {
        $this->autoRender = false;
        // debug($ctc);
        $itax3 = 0;
        $tax1 = 0;
        $tax2 = 0;
        $tax3 = 0;
        $itax1 = 0;
        $itax2 = 0;
        $tax = 0;
        if ($ctc > 250000) {
            $itax1 = $ctc - 250000;
            $tax1 = $itax1 * 10 / 100;
        }
        if ($ctc > 500000) {
            $itax2 = $ctc - 500000;
            $tax2 = $itax2 * 20 / 100;
        }
        if ($ctc > 1000000) {
            $itax3 = $ctc - 1000000;
            $tax3 = $itax3 * 30 / 100;
        }
        $Tax = isset($tax1) ? $tax1 : 0 + isset($tax2) ? $tax2 : 0 + isset($tax3) ? $tax3 : 0;
        //     debug($tax);

        echo json_encode($tax);
    }
    public function Calculate_new($ctc = 0)
    {
        $this->autoRender = false;
        // debug($ctc);
        $itax3 = 0;
        $tax1 = 0;
        $tax2 = 0;
        $tax3 = 0;
        $itax1 = 0;
        $itax2 = 0;
        $tax = 0;
        if ($ctc > 250000) {
            $itax1 = $ctc - 250000;
            $tax1 = $itax1 * 10 / 100;
        }
        if ($ctc > 500000) {
            $itax2 = $ctc - 500000;
            $tax2 = $itax2 * 20 / 100;
        }
        if ($ctc > 1000000) {
            $itax3 = $ctc - 1000000;
            $tax3 = $itax3 * 30 / 100;
        }
        $Tax = isset($tax1) ? $tax1 : 0 + isset($tax2) ? $tax2 : 0 + isset($tax3) ? $tax3 : 0;
        //     debug($tax);

        echo json_encode($tax);
    }
    public function setup($emp_pkey = 0)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeTaxsalsum->useDbConfig = $this->Session->read('ds');
        $this->TaxSave->useDbConfig = $this->Session->read('ds');
        $this->EmployeeTaxsalsumNew->useDbConfig = $this->Session->read('ds');
        $employee = $this->EmployeeDetails->find("all", array("conditions" => array("emp_pkey" => $emp_pkey)));
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->EmpTaxSalTrans->useDbConfig = $this->Session->read('ds');
        $this->EmpTaxSalTransNew->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("first", array("conditions" => array("Year_status" => "OPEN", "is_current_finyear" => "Y", "vattr1" => 1, "branch_code= (select branch_code from emp_details where emp_Pkey=$emp_pkey ) ")));

         // Edited by Akshay on 16-8-2025
        $user_group = $this->Session->read('user_group');
        if (!$years && $user_group == 2) {
            // Render an alternate view file
            $this->render('no_fin_year');
            return;
        }
        // End

        // edited by athira on 11-04-2025
        $company_code = strtoupper($this->Session->read('company_code'));
        if($company_code == 'KWMT'){
            $emp_fin = $years['FinancialYear']['fin_year'];
            if ($emp_fin <= '2024') {
                $emp_fin = '2024';
            }
          
        }
        $emp_fin = $years['FinancialYear']['fin_year'];
          $income_tax_slab = $this->EmployeeDetails->query("select * from income_tax_slab where fin_year='$emp_fin' AND regime='NEW' AND salary_range_to <= 5000000");
            $this->set('income_tax_slab', $income_tax_slab);
        //end
        $this->set('years', $years);
        $start_month = $years['FinancialYear']['start_month'];
        $end_month = $years['FinancialYear']['end_month'];
        $date_now = date('Y-m');
        $option_type = $this->EmployeeDetails->query("select option_type from emp_tax_regime where emp_fkey = '$emp_pkey' and end_date_effective is NULL");
        $option = isset($option_type['0']['emp_tax_regime']['option_type']) ? $option_type['0']['emp_tax_regime']['option_type'] : 'O';
        //if ($option == 'O'){
        $conditions = array("EmpTaxSalTrans.emp_fkey" => $emp_pkey, "EmpTaxSalTrans.end_date_effective is null");
        $joins = array(
            array(
                'table' => 'tax_salary_components',
                'alias' => 'TSC',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpTaxSalTrans.tax_salary_components_fkey = TSC.tax_salary_components_pkey'),
            ),
            array(
                'table' => 'salary_head_items',
                'alias' => 'SHI',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpTaxSalTrans.salary_head_item_Fkey = SHI.salary_head_item_pkey')
            )
        );
        $taxcomponents = $this->EmpTaxSalTrans->find("all", array('conditions' => $conditions, 'joins' => $joins, 'fields' => array('EmpTaxSalTrans.salary_head_item_Fkey,EmpTaxSalTrans.tax_salary_components_fkey,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.upper_limit,EmpTaxSalTrans.taxable_salary,TSC.tax_salary_components_name,SHI.item')));
        $this->set('taxcomponents', $taxcomponents);
        // debug($taxcomponents);
        $Total = 0;
        foreach ($taxcomponents as $val) {
            $Total = $Total + $val['EmpTaxSalTrans']['taxable_salary'];
        }
        $this->set('Total', $Total);
        $joinsDec = array(
            array(
                'table' => 'tax_heads',
                'alias' => 'TX',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('TX.tax_heads_pkey = EmployeeTaxTransactions.tax_heads_fkey'),
            ),
            array(
                'table' => 'tax_heads_details',
                'alias' => 'TXD',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeTaxTransactions.tax_heads_details_fkey = TXD.tax_heads_details_pkey')
            )
        );
        $conditionsDec = array('EmployeeTaxTransactions.emp_fkey' => $emp_pkey, "TX.tax_type = 'Deductions' ");
        $taxdeclarations = $this->EmployeeTaxTransactions->find("all", array('fields' => 'TX.tax_name,TX.tax_type,TX.attr1,EmployeeTaxTransactions.tax_value', 'joins' => $joinsDec, 'conditions' => $conditionsDec));
        $tottax = 0;
        foreach ($taxdeclarations as $value) {
            if ($value['TX']['attr1'] < $value['EmployeeTaxTransactions']['tax_value']) {
                $tottax = $tottax + $value['TX']['attr1'];
            } else if ($value['TX']['attr1'] > $value['EmployeeTaxTransactions']['tax_value']) {
                $tottax = $tottax + $value['EmployeeTaxTransactions']['tax_value'];
            }
        }
        $this->set('tottax', $tottax);
        $this->set('taxdeclarations', $taxdeclarations);
        $other_sources = $this->EmployeeTaxTransactions->query("select sum(tax_value)as sums from emp_tax_transactions  where tax_heads_fkey in (select tax_heads_pkey from tax_heads where lcase(tax_type)='income') and emp_fkey='$emp_pkey'
        group by emp_fkey");
        $other_sources = isset($other_sources['0']['0']['sums']) ? $other_sources['0']['0']['sums'] : 0;
        $this->set('other_sources', $other_sources);
        $this->set("employee", $employee);
        $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));
        $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
        $this->set('emp_pkey', $emp_pkey);
        $taxcomponents = $this->EmployeeTaxsalsum->find("all", array("conditions" => array("emp_fkey" => $emp_pkey, "end_date_effective is null")));
        $this->set('taxcomponents', $taxcomponents);
        function firstDayOfMonth($uts = null)
        {
            $today = is_null($uts) ? getDate() : getDate($uts);
            $first_day = getdate(mktime(0, 0, 0, $today['mon'], 1, $today['year']));
            return $first_day[0];
        }
        $emp_joined = $this->EmployeeDetails->query("select * from employee_info where emp_pkey = '$emp_pkey'");
        $joined_date = strtotime($emp_joined['0']['employee_info']['joining_date']);
        $joined = $emp_joined['0']['employee_info']['joining_date'];
        if ($start_month >= $joined) {
            $start = strtotime($start_month);
        } else {
            $start = strtotime($joined);
        }
        $end = strtotime($start);
        $endmonth = strtotime($end_month);
        $month = firstDayOfMonth($end);
        $months = array();
        while ($month <= $endmonth) {
            $months[] = date('Y-m', $month);
            $month = strtotime("+1 month", $month);
        }
        $endmonth1 = strtotime($end_month);
        $month1 = firstDayOfMonth($start);
        $months1 = array();
        while ($month1 < $endmonth) {
            $months1[] = date('Y-m', $month1);
            $month1 = strtotime("+1 month", $month1);
        }
        $monthsbefore = array();
        $taxdates = array();
        foreach ($months1 as $value) {
            $tax = $this->EmployeeTaxsalsum->query("SELECT month_year , sum(salary_amount) tdsdeducted
                                                            FROM emp_salary_slip
                                                            WHERE emp_fkey ='$emp_pkey' 
                                                            and end_date_effective is null and month_year = '$value'
                                                            and payroll_master_fkey in (select payroll_master_pkey from payroll_master where action IN('Processed','Approved'))
                                                            and ucase(salary_head_item_desc)='TDS' group by month_year ;");

            $salary = $this->EmployeeTaxsalsum->query("SELECT month_year , sum(abs(round(salary_amount))) tdsdeducted    FROM emp_salary_slip
                                                            WHERE emp_fkey ='$emp_pkey' 
                                                            and end_date_effective is null and item_part <> 'Indirect' and month_year = '$value'
                                                            and head_operator <>'Deduction' and payroll_master_fkey in (select payroll_master_pkey from payroll_master where action IN('Processed','Approved'))
                                                            and ucase(salary_head_item_desc) not in ('SALARY ADVANCE','LOANS','TDS') group by month_year");

            $taxdates[] = array(
                'months' => $value,
                'tax' => $tax,
                'salary' => $salary
            );
        }
        //edited by megha on 02/01/2019 Tax Avialed amount missing
        $finans_year = isset($years['FinancialYear']['fin_year']) ? $years['FinancialYear']['fin_year'] : date('Y');
        $salary = $this->EmployeeDetails->query("select sum(abs(structure_det_value)) as amount from emp_salary_structure where emp_fkey = '$emp_pkey' and"
            . " end_date_effective is null and head_operator <>'Deduction' and item_part <> 'Indirect' and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1)) ");
        $actual_salary = $this->EmployeeDetails->query("select sum(actual_salary_recd) as actual_salary from emp_tax_sal_trans where end_date_effective is null and salary_head_item_fkey in (select salary_head_item_fkey from emp_salary_slip where head_operator<>'Deduction' 
        and ucase(salary_head_item_desc) not in ('SALARY ADVANCE','LOANS','TDS') and emp_fkey='$emp_pkey' and end_date_effective is null ) 
        and fin_year='$finans_year' and emp_fkey='$emp_pkey' ");
        $prjected_salary = $this->EmployeeDetails->query("select sum(projected_salary) as projected_salary from emp_tax_sal_trans where end_date_effective is null and salary_head_item_fkey in (select salary_head_item_fkey from emp_salary_slip where head_operator<>'Deduction' 
        and ucase(salary_head_item_desc) not in ('SALARY ADVANCE','LOANS','TDS') and emp_fkey='$emp_pkey' and end_date_effective is null ) 
        and fin_year='$finans_year' and emp_fkey='$emp_pkey' ");

        $taxable_salary = $this->EmployeeDetails->query("select sum(taxable_salary) as taxable_salary from emp_tax_sal_trans where end_date_effective is null and emp_fkey = '$emp_pkey' and fin_year = '$finans_year' ");

        $this->set('actual_salary', isset($actual_salary['0']['0']['actual_salary']) ? $actual_salary['0']['0']['actual_salary'] : 0);
        $this->set('prjected_salary', isset($prjected_salary['0']['0']['projected_salary']) ? $prjected_salary['0']['0']['projected_salary'] : 0);
        $this->set('taxable_salary', isset($taxable_salary['0']['0']['taxable_salary']) ? $taxable_salary['0']['0']['taxable_salary'] : 0);
        $this->set('taxdates', $taxdates);
        $this->set("salary", $salary);
        $this->set("months1", $months1);
        $this->set("months", $months);
        $this->set("end_month", $end_month);
        $this->set("endmonth", $endmonth);
        //}else{   tax new regime
        $conditions = array("EmpTaxSalTransNew.emp_fkey" => $emp_pkey, "EmpTaxSalTransNew.end_date_effective is null");
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
                'alias' => 'SHI',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpTaxSalTransNew.salary_head_item_Fkey = SHI.salary_head_item_pkey')
            )
        );
        $taxcomponents_new = $this->EmpTaxSalTransNew->find("all", array('conditions' => $conditions, 'joins' => $joins, 'fields' => array('EmpTaxSalTransNew.salary_head_item_Fkey,EmpTaxSalTransNew.tax_salary_components_fkey,EmpTaxSalTransNew.availed_salary,EmpTaxSalTransNew.upper_limit,EmpTaxSalTransNew.taxable_salary,TSC.tax_salary_components_name,SHI.item')));
        $this->set('taxcomponents_new', $taxcomponents_new);
        $Total_new = 0;
        foreach ($taxcomponents_new as $val) {
            $Total_new = $Total_new + $val['EmpTaxSalTransNew']['taxable_salary'];
        }
        $this->set('Total_new', $Total_new);
        $joinsDec = array(
            array(
                'table' => 'tax_heads',
                'alias' => 'TX',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('TX.tax_heads_pkey = EmployeeTaxTransactions.tax_heads_fkey'),
            ),
            array(
                'table' => 'tax_heads_details',
                'alias' => 'TXD',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeTaxTransactions.tax_heads_details_fkey = TXD.tax_heads_details_pkey')
            )
        );
        $conditionsDec = array('EmployeeTaxTransactions.emp_fkey' => $emp_pkey, "TX.tax_type = 'Deductions' ");
        $taxdeclarations_new = $this->EmployeeTaxTransactions->find("all", array('fields' => 'TX.tax_name,TX.tax_type,TX.attr1,EmployeeTaxTransactions.tax_value', 'joins' => $joinsDec, 'conditions' => $conditionsDec));
        $tottax_new = 0;
        foreach ($taxdeclarations_new as $value) {
            if ($value['TX']['attr1'] < $value['EmployeeTaxTransactions']['tax_value']) {
                $tottax_new = $tottax_new + $value['TX']['attr1'];
            } else if ($value['TX']['attr1'] > $value['EmployeeTaxTransactions']['tax_value']) {
                $tottax_new = $tottax_new + $value['EmployeeTaxTransactions']['tax_value'];
            }
        }
        $this->set('tottax_new', $tottax_new);
        $this->set('taxdeclarations_new', $taxdeclarations_new);
        $other_sources_new = $this->EmployeeTaxTransactions->query("select sum(tax_value)as sums from emp_tax_transactions  where tax_heads_fkey in (select tax_heads_pkey from tax_heads where lcase(tax_type)='income') and emp_fkey='$emp_pkey'
        group by emp_fkey");
        $other_sources_new = isset($other_sources_new['0']['0']['sums']) ? $other_sources_new['0']['0']['sums'] : 0;
        $this->set('other_sources_new', $other_sources_new);
        //repeating code for new tax regime
        //$this->set("employee",$employee);
        //$this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));
        //$this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
        //$this->set('emp_pkey', $emp_pkey);
        //$taxcomponents = $this->EmployeeTaxsalsumNew->find("all",array("conditions"=>array("emp_fkey"=>$emp_pkey,"end_date_effective is null")));
        //$this->set('taxcomponents', $taxcomponents);
        //        function firstDayOfMonth($uts=null) 
        //           { 
        //           $today = is_null($uts) ? getDate() : getDate($uts); 
        //           $first_day = getdate(mktime(0,0,0,$today['mon'],1,$today['year'])); 
        //           return $first_day[0]; 
        //           }       
        $emp_joined = $this->EmployeeDetails->query("select * from employee_info where emp_pkey = '$emp_pkey'");
        $joined_date = strtotime($emp_joined['0']['employee_info']['joining_date']);
        $joined = $emp_joined['0']['employee_info']['joining_date'];
        if ($start_month >= $joined) {
            $start = strtotime($start_month);
        } else {
            $start = strtotime($joined);
        }
        $end = strtotime($start);
        $endmonth = strtotime($end_month);
        $month = firstDayOfMonth($end);
        $months = array();
        while ($month <= $endmonth) {
            $months[] = date('Y-m', $month);
            $month = strtotime("+1 month", $month);
        }
        $endmonth1 = strtotime($end_month);
        $month1 = firstDayOfMonth($start);
        $months1 = array();
        while ($month1 < $endmonth) {
            $months1[] = date('Y-m', $month1);
            $month1 = strtotime("+1 month", $month1);
        }
        $monthsbefore = array();
        $taxdates_new = array();
        foreach ($months1 as $value) {
            $tax_new = $this->EmployeeTaxsalsumNew->query("SELECT month_year , sum(salary_amount) tdsdeducted
                                                            FROM emp_salary_slip
                                                            WHERE emp_fkey ='$emp_pkey' 
                                                            and end_date_effective is null and month_year = '$value'
                                                            and payroll_master_fkey in (select payroll_master_pkey from payroll_master where action IN('Processed','Approved'))
                                                            and ucase(salary_head_item_desc)='TDS' group by month_year ;");

            $salary_new = $this->EmployeeTaxsalsumNew->query("SELECT month_year , sum(abs(round(salary_amount))) tdsdeducted    FROM emp_salary_slip
                                                            WHERE emp_fkey ='$emp_pkey' 
                                                            and end_date_effective is null and item_part <> 'Indirect' and month_year = '$value'
                                                            and head_operator <>'Deduction' and payroll_master_fkey in (select payroll_master_pkey from payroll_master where action IN('Processed','Approved'))
                                                            and ucase(salary_head_item_desc) not in ('SALARY ADVANCE','LOANS','TDS') group by month_year");
            $taxdates_new[] = array(
                'months' => $value,
                'tax' => $tax_new,
                'salary' => $salary_new
            );
        }
        //repeating code for new tax regime
        //edited by megha on 02/01/2019 Tax Availed amount missing
        //        $finans_year = isset($years['FinancialYear']['fin_year'])?$years['FinancialYear']['fin_year']:date('Y');
        //        $salary = $this->EmployeeDetails->query("select sum(abs(structure_det_value)) as amount from emp_salary_structure where emp_fkey = '$emp_pkey' and end_date_effective is null and head_operator <>'Deduction' and item_part <> 'Indirect' ");
        $actual_salary_new = $this->EmployeeDetails->query("select sum(actual_salary_recd) as actual_salary from emp_tax_sal_trans_new where end_date_effective is null and salary_head_item_fkey in (select salary_head_item_fkey from emp_salary_slip where head_operator<>'Deduction' 
        and ucase(salary_head_item_desc) not in ('SALARY ADVANCE','LOANS','TDS') and emp_fkey='$emp_pkey' and end_date_effective is null ) 
        and fin_year='$finans_year' and emp_fkey='$emp_pkey' ");
        $prjected_salary_new = $this->EmployeeDetails->query("select sum(projected_salary) as projected_salary from emp_tax_sal_trans_new where end_date_effective is null and salary_head_item_fkey in (select salary_head_item_fkey from emp_salary_slip where head_operator<>'Deduction' 
        and ucase(salary_head_item_desc) not in ('SALARY ADVANCE','LOANS','TDS') and emp_fkey='$emp_pkey' and end_date_effective is null ) 
        and fin_year='$finans_year' and emp_fkey='$emp_pkey' ");

        $taxable_salary_new = $this->EmployeeDetails->query("select sum(taxable_salary) as taxable_salary from emp_tax_sal_trans_new where end_date_effective is null and emp_fkey = '$emp_pkey' and fin_year = '$finans_year' ");

        $this->set('actual_salary_new', isset($actual_salary_new['0']['0']['actual_salary']) ? $actual_salary_new['0']['0']['actual_salary'] : 0);
        $this->set('prjected_salary_new', isset($prjected_salary_new['0']['0']['projected_salary']) ? $prjected_salary_new['0']['0']['projected_salary'] : 0);
        $this->set('taxable_salary_new', isset($taxable_salary_new['0']['0']['taxable_salary']) ? $taxable_salary_new['0']['0']['taxable_salary'] : 0);
        $this->set('taxdates_new', $taxdates_new);
        //$this->set("salary",$salary);
        //$this->set("months1",$months1);
        //$this->set("months",$months);
        //$this->set("end_month",$end_month);
        //$this->set("endmonth",$endmonth);  
        //}
        $gender = $this->EmployeeDetails->find("all", array("conditions" => array("emp_pkey" => $emp_pkey)));
        $classifications = $gender['0']['EmployeeDetails']['classification'];
        $this->set("classifications", $classifications);
        $datasave = array();
        $datasave['incom'] = $Total;
        $datasave['emp_fkey'] = $emp_pkey;
        $datasave['other'] = $other_sources;
        $datasave['deductions'] = $tottax;
        $this->TaxSave->save($datasave);
        $user_group = $this->Session->read("user_group");
        $this->set('user_group', $user_group);
        $lock_array = $this->TaxSave->query("Select locked from `emp_tax_transactions` WHERE `emp_fkey` = " . $emp_pkey);
        $lockedall = 'Y';
        foreach ($lock_array as $val) {
            if ($val['emp_tax_transactions']['locked'] == 'N') {
                $lockedall = 'N';
            }
        }
        $this->set('lockedall', $lockedall);
        $option_type = $this->TaxSave->query("select option_type from emp_tax_regime where emp_fkey = '$emp_pkey' and end_date_effective is NULL");
        $option = isset($option_type['0']['emp_tax_regime']['option_type']) ? $option_type['0']['emp_tax_regime']['option_type'] : 'N';
        $this->set('option', $option);
        $user_id = $this->Session->read('login_user_id');
        $company_code = $this->Session->read('company_code');
        $years = $this->EmployeeDetails->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 1 and is_current_finyear = 'Y' and status = '1'  and branch_code= (select branch_code from emp_details where emp_Pkey= '$emp_pkey' ) ");
        $year = $years['0']['fin_year']['fin_year'];
        $other_sources = $this->EmployeeDetails->query("select tax_salary_distribution_fn ('$company_code',$emp_pkey,'$year','$user_id') as old");
        $new_regime = $this->EmployeeDetails->query("select tax_salary_distribution_new_fn ('$company_code',$emp_pkey,'$year','$user_id') as new");
        $old = $other_sources['0']['0']['old'];
        $new = $new_regime['0']['0']['new'];
        //$this->set('old',$old);
        //$this->set('new',$new);
        //Edited by Akshay on 27-3-2024
        $arr_reimbursement_items = $this->EmployeeDetails->query("select tax_heads_details_pkey,tax_heads_details,tax_heads_details2,
         emp_tax_transactions.tax_value,tax_heads.attr1 from tax_heads_details 
         left join tax_heads on (tax_heads.tax_heads_pkey = tax_heads_details.tax_heads_fkey)
         left join emp_tax_transactions on (emp_tax_transactions.tax_heads_details_fkey =tax_heads_details.tax_heads_details_pkey)
         WHERE (tax_heads_details LIKE '%Telephone Reimbursement%' or tax_heads_details 
         LIKE '%Car maintenance/Petrol expenses%') and tax_name = 'Perquisite Sec 17(2) -New Regime' AND status = 1 
         and fin_year='$year' and emp_fkey='$emp_pkey' ");

        //            select sum(tax_value)as sums from emp_tax_transactions  where tax_heads_fkey in 
        //(select tax_heads_pkey from tax_heads where lcase(tax_type)='Reimbursement' and tax_name = 'Perquisite Sec 17(2) (b)') 
        //and emp_fkey=3941 and tax_heads_details_fkey = 0


        $reimbursemnetded = 0;
        $totallimit = 0;
        foreach ($arr_reimbursement_items as $reimbursement_items) {
            $totallimit = $reimbursement_items['tax_heads']['attr1'];
            $tax_value = $reimbursement_items['emp_tax_transactions']['tax_value'];
            $limit = $reimbursement_items['tax_heads_details']['tax_heads_details2'];
            if ($tax_value > $limit) {
                $reimbursemnetded += $limit;
            } else {
                $reimbursemnetded += $tax_value;
            }
        }
        if ($totallimit > 0) {
            if ($totallimit > $reimbursemnetded) {
                $reimbursemnetded = $reimbursemnetded;
            } else {
                $reimbursemnetded = $totallimit;
            }
        }
        $this->set('reimbursemnetded', $reimbursemnetded);

        $taxable_salary_old = $this->EmployeeDetails->query("select sum(taxable_salary) as taxable_salary from emp_tax_sal_trans where end_date_effective is null and emp_fkey = '$emp_pkey' and fin_year = '$finans_year' ");
        $this->set('taxable_salary_old', isset($taxable_salary_old['0']['0']['taxable_salary']) ? $taxable_salary_old['0']['0']['taxable_salary'] : 0);
        $taxable_salary_new = $this->EmployeeDetails->query("select taxable_salary,taxable_income,other_income from emp_tax_sal_trans_sum_new where end_date_effective is null and emp_fkey = '$emp_pkey' and fin_year = '$finans_year' ");
        $this->set('taxable_salary_n', isset($taxable_salary_new['0']['emp_tax_sal_trans_sum_new']['taxable_salary']) ? $taxable_salary_new['0']['emp_tax_sal_trans_sum_new']['taxable_salary'] : 0);
        $this->set('taxable_income_new', isset($taxable_salary_new['0']['emp_tax_sal_trans_sum_new']['taxable_income']) ? $taxable_salary_new['0']['emp_tax_sal_trans_sum_new']['taxable_income'] : 0);
        $this->set('other_income_new', isset($taxable_salary_new['0']['emp_tax_sal_trans_sum_new']['other_income']) ? $taxable_salary_new['0']['emp_tax_sal_trans_sum_new']['other_income'] : 0);

        $taxcomponents_old = $this->EmployeeTaxsalsum->find("all", array("conditions" => array("emp_fkey" => $emp_pkey, "end_date_effective is null")));
        $this->set('taxcomponents_old', $taxcomponents_old);
        $taxcomponents_new = $this->EmployeeTaxsalsumNew->find("all", array("conditions" => array("emp_fkey" => $emp_pkey, "end_date_effective is null")));
        $this->set('taxcomponents_new', $taxcomponents_new);
        // debug($taxcomponents_new);
        $this->render('setup');
    }


    public function setupshow($emp_pkey = 0)
    {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");



        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->EmpTaxSalTrans->useDbConfig = $this->Session->read('ds');
        $conditions = array("EmpTaxSalTrans.emp_fkey" => $emp_pkey, "EmpTaxSalTrans.end_date_effective is null");
        $joins = array(
            array(
                'table' => 'tax_salary_components',
                'alias' => 'TSC',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpTaxSalTrans.tax_salary_components_fkey = TSC.tax_salary_components_pkey'),
            ),
            array(
                'table' => 'salary_head_items',
                'alias' => 'SHI',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpTaxSalTrans.salary_head_item_Fkey = SHI.salary_head_item_pkey')
            )
        );
        $taxcomponents = $this->EmpTaxSalTrans->find("all", array('conditions' => $conditions, 'joins' => $joins, 'fields' => array('EmpTaxSalTrans.salary_head_item_Fkey,EmpTaxSalTrans.tax_salary_components_fkey,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.upper_limit,EmpTaxSalTrans.taxable_salary,TSC.tax_salary_components_name,SHI.item')));
        $this->set('taxcomponents', $taxcomponents);
        $Total = 0;
        foreach ($taxcomponents as $val) {
            $Total = $Total + $val['EmpTaxSalTrans']['taxable_salary'];
        }
        $this->set('Total', $Total);
        $joinsDec = array(
            array(
                'table' => 'tax_heads',
                'alias' => 'TX',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('TX.tax_heads_pkey = EmployeeTaxTransactions.tax_heads_fkey'),
            ),
            array(
                'table' => 'tax_heads_details',
                'alias' => 'TXD',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeTaxTransactions.tax_heads_details_fkey = TXD.tax_heads_details_pkey')
            )
        );
        $conditionsDec = array('EmployeeTaxTransactions.emp_fkey' => $emp_pkey, "TX.tax_type = 'Deductions' ");
        $taxdeclarations = $this->EmployeeTaxTransactions->find("all", array('fields' => 'TX.tax_name,TX.tax_type,TX.attr1,EmployeeTaxTransactions.tax_value', 'joins' => $joinsDec, 'conditions' => $conditionsDec));
        $tottax = 0;
        foreach ($taxdeclarations as $value) {
            if ($value['TX']['attr1'] < $value['EmployeeTaxTransactions']['tax_value']) {
                $tottax = $tottax + $value['TX']['attr1'];
            } else if ($value['TX']['attr1'] > $value['EmployeeTaxTransactions']['tax_value']) {
                $tottax = $tottax + $value['EmployeeTaxTransactions']['tax_value'];
            }
        }
        $this->set('tottax', $tottax);
        $this->set('taxdeclarations', $taxdeclarations);
        $other_sources = $this->EmployeeTaxTransactions->query("select sum(tax_value)as sums from emp_tax_transactions  where tax_heads_fkey in (select tax_heads_pkey from tax_heads where lcase(tax_type)='income') and emp_fkey='$emp_pkey'
        group by emp_fkey");
        $other_sources = isset($other_sources['0']['0']['sums']) ? $other_sources['0']['0']['sums'] : 0;
        $this->set('other_sources', $other_sources);
    }

    public function setupshow_new($emp_pkey = 0)
    {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->EmpTaxSalTransNew->useDbConfig = $this->Session->read('ds');
        //edited by megha on 05/03/2024
        $conditions = array("EmpTaxSalTransNew.emp_fkey" => $emp_pkey, "EmpTaxSalTransNew.end_date_effective is null");
        //,"EmpTaxSalTransNew.actual_salary_recd > 0");
        //,"tax_salary_components_fkey in (select tax_salary_components_pkey  from tax_salary_components where ucase(tax_salary_components_name)!='PROFESSIONAL TAX')");
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
                'alias' => 'SHI',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmpTaxSalTransNew.salary_head_item_Fkey = SHI.salary_head_item_pkey')
            )
        );
        $taxcomponents = $this->EmpTaxSalTransNew->find("all", array('conditions' => $conditions, 'joins' => $joins, 'fields' => array('EmpTaxSalTransNew.salary_head_item_Fkey,EmpTaxSalTransNew.tax_salary_components_fkey,EmpTaxSalTransNew.availed_salary,EmpTaxSalTransNew.availed_salary,EmpTaxSalTransNew.upper_limit,EmpTaxSalTransNew.taxable_salary,TSC.tax_salary_components_name,SHI.item')));
        $this->set('taxcomponents', $taxcomponents);
        $Total = 0;
        foreach ($taxcomponents as $val) {
            $Total = $Total + $val['EmpTaxSalTransNew']['taxable_salary'];
        }
        $this->set('Total', $Total);
        $joinsDec = array(
            array(
                'table' => 'tax_heads',
                'alias' => 'TX',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('TX.tax_heads_pkey = EmployeeTaxTransactions.tax_heads_fkey'),
            ),
            array(
                'table' => 'tax_heads_details',
                'alias' => 'TXD',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeTaxTransactions.tax_heads_details_fkey = TXD.tax_heads_details_pkey')
            )
        );
        $conditionsDec = array('EmployeeTaxTransactions.emp_fkey' => $emp_pkey, "TX.tax_type = 'Deductions' ");
        $taxdeclarations = $this->EmployeeTaxTransactions->find("all", array('fields' => 'TX.tax_name,TX.tax_type,TX.attr1,EmployeeTaxTransactions.tax_value', 'joins' => $joinsDec, 'conditions' => $conditionsDec));
        $tottax = 0;
        foreach ($taxdeclarations as $value) {
            if ($value['TX']['attr1'] < $value['EmployeeTaxTransactions']['tax_value']) {
                $tottax = $tottax + $value['TX']['attr1'];
            } else if ($value['TX']['attr1'] > $value['EmployeeTaxTransactions']['tax_value']) {
                $tottax = $tottax + $value['EmployeeTaxTransactions']['tax_value'];
            }
        }
        $this->set('tottax', $tottax);
        $this->set('taxdeclarations', $taxdeclarations);
        $other_sources = $this->EmployeeTaxTransactions->query("select sum(tax_value)as sums from emp_tax_transactions  where tax_heads_fkey in (select tax_heads_pkey from tax_heads where lcase(tax_type)='income') and emp_fkey='$emp_pkey'
        group by emp_fkey");
        $other_sources = isset($other_sources['0']['0']['sums']) ? $other_sources['0']['0']['sums'] : 0;
        $this->set('other_sources', $other_sources);
    }

    public function Proccess($emp_pkey = 0)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        $company_code = $this->Session->read('company_code');
        $years = $this->EmployeeDetails->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 1 and is_current_finyear = 'Y' and status = '1'  and branch_code= (select branch_code from emp_details where emp_Pkey= '$emp_pkey' ) ");
        $year = $years['0']['fin_year']['fin_year'];
        $other_sources = $this->EmployeeDetails->query("select tax_salary_distribution_fn ('$company_code',$emp_pkey,'$year','$user_id') as old");
        $new_regime = $this->EmployeeDetails->query("select tax_salary_distribution_new_fn ('$company_code',$emp_pkey,'$year','$user_id') as new");
        $old = $other_sources['0']['0']['old'];
        $new = $new_regime['0']['0']['new'];
        //$this->set('old',$old);
        //$this->set('new',$new);
        $option_type = $this->EmployeeDetails->query("select option_type from emp_tax_regime where emp_fkey = '$emp_pkey' and end_date_effective is NULL");
        $option = isset($option_type['0']['emp_tax_regime']['option_type']) ? $option_type['0']['emp_tax_regime']['option_type'] : 'N';

        $this->set('option', $option);
        $this->setup($emp_pkey);
        $this->render('setup');
    }
    public function Choosetax($emp_pkey = 0, $fin_year = 0, $tax = '')
    {
        $this->EmpTaxRegime->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;

        //$arr_form_data = $this->request->data;
        $user_id = $this->Session->read('login_user_id');
        $emp = $emp_pkey;
        $date = date('Y-m-d');
        $arr_tax = $this->EmpTaxRegime->find('count', array('conditions' => array('emp_fkey' => $emp, 'end_date_effective' => NULL)));
        //debug($arr_tax) ;  
        // debug($arr_form_data);
        $save_data = array();
        try {
            if ($fin_year == 0) {
                echo json_encode(array("success" => 1, "msg" => "Please process the tax regime."));
            } else {
                $e = "";
                $save_data['emp_fkey'] = $emp;
                $save_data['fin_year'] = $fin_year;
                $save_data['created_by'] = $user_id;
                if ($tax == 'new') {
                    $save_data['option_type'] = 'N';
                } else {
                    $save_data['option_type'] = 'O';
                }
                $user_id = "'" . $user_id . "'";
                $date = "'" . $date . "'";
                if ($arr_tax > 0) {
                    $this->EmpTaxRegime->updateAll(array("end_date_effective" => $date, "modified_by" => $user_id), array("emp_fkey" => $emp));
                    $this->EmpTaxRegime->save($save_data);
                    echo json_encode(array("success" => 1, "msg" => "Updated successfully."));
                } else {
                    $this->EmpTaxRegime->save($save_data);
                    echo json_encode(array("success" => 1, "msg" => "Saved successfully."));
                }
            }
        } catch (ErrorException $e) {
            $e->getMessage();
        } catch (Exception $e) {
            $e->getMessage();
        } catch (mysqli_sql_exception $m) {
            $m->getMessage();
        }
        // echo json_encode(array("success" => 1, "msg" => "Successfully Terminated Employee", "error" => $e));
        //echo json_encode(array("success" => 1));
    }
    //Edited by Akshay on 18-8-2023
    public function setupupload_new($emp_pkey = 0)
    {
        // Calculate the current year and the next year
        $currentYear = date("Y");
        $nextYear = $currentYear + 1;
        // Set variables with current year and next year data
        $this->set('currentYear', $currentYear);
        $this->set('nextYear', $nextYear);
        $this->set('emp_pkey', $emp_pkey);
    }

    //Edited by Akshay on 19-8-2023
    public function addnewtaxdocument_modal($fieldName, $emp)
    {
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $this->set('fieldName', $fieldName);
        $this->set('emp', $emp);
    }

    //Edited by Akshay on 22-8-2023
    //Edited by Akshay on 18-8-2023
    public function setupdownload_new($emp_pkey = 0)
    {
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        // Calculate the current year and the next year
        $currentYear = date("Y");

        $nextYear = $currentYear + 1;
        // Set variables with current year and next year data
        $this->set('currentYear', $currentYear);
        $this->set('nextYear', $nextYear);
        $this->set('emp_pkey', $emp_pkey);

        $finYears = $this->FinancialYear->query("SELECT DISTINCT (fin_year) FROM tax_form_documents");
        $this->set('finYears',  $finYears);


        $fileNames = array();
        $financialYears = $this->FinancialYear->query("SELECT fin_year FROM tax_form_documents WHERE pan IN (SELECT pan_no FROM emp_details WHERE emp_pkey = $emp_pkey)
                                                        GROUP BY fin_year ORDER BY fin_year DESC");
        // debug($financialYears);
        foreach ($financialYears as $years) {
            $year = isset($years['tax_form_documents']['fin_year']) ? $years['tax_form_documents']['fin_year'] : '';
            // debug($year);
            $fileNames[] = $this->FinancialYear->query("SELECT tfd.form_name, tfd.pan, tfd.fin_year, tfd.created_by,tfd.created_date
                                                                FROM tax_form_documents tfd
                                                                WHERE tfd.pan IN (SELECT pan_no FROM emp_details WHERE emp_pkey = $emp_pkey)
                                                                AND tfd.created_date IN (SELECT MAX(created_date) FROM tax_form_documents WHERE 
                                                                                            pan IN(SELECT pan_no FROM emp_details WHERE emp_pkey = $emp_pkey
                                                                                            AND fin_year = '$year'))
                                                                                            AND tfd.fin_year = '$year' ");
        }

        // debug($fileNames);
        $this->set('fileNames',  $fileNames);
    }

    public function downloadtaxdocument_modal($finYear = 0, $empPkey = 0)
    {
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $this->set('fieldName', $finYear);
        $this->set('emp', $empPkey);

        if ($user_group == 1) {

            $fileNames = $this->FinancialYear->query("SELECT tfd.form_name, tfd.pan, tfd.fin_year, tfd.created_by, tfd.created_date
                                                                FROM tax_form_documents tfd
                                                                INNER JOIN (
                                                                    SELECT pan, MAX(created_date) AS max_created_date
                                                                    FROM tax_form_documents
                                                                    WHERE fin_year = '$finYear' AND status = 1
                                                                    GROUP BY pan
                                                                ) max_dates ON tfd.pan = max_dates.pan AND tfd.created_date = max_dates.max_created_date
                                                                WHERE tfd.pan IN (SELECT pan_no FROM emp_details WHERE emp_pkey = $empPkey)
                                                                ORDER BY tfd.created_date DESC;
                                                                ");
            // debug($fileNames);
            $this->set('fileNames', $fileNames);
        } elseif ($user_group == 2) {

            $fileNames = $this->FinancialYear->query("SELECT tfd.form_name, tfd.pan, tfd.fin_year, tfd.created_by, tfd.created_date
                                                                FROM tax_form_documents tfd
                                                                INNER JOIN (
                                                                    SELECT pan, MAX(created_date) AS max_created_date
                                                                    FROM tax_form_documents
                                                                    WHERE fin_year = '$finYear' AND status = 1
                                                                    GROUP BY pan
                                                                ) max_dates ON tfd.pan = max_dates.pan AND tfd.created_date = max_dates.max_created_date
                                                                WHERE tfd.pan IN (SELECT pan_no FROM emp_details WHERE emp_pkey = $empPkey)
                                                                ORDER BY tfd.created_date DESC;
                                                                ");
            $this->set('fileNames', $fileNames);
        }
    }

    public function formSixteen()
    {
        $this->autoRender = FALSE;
        $this->layout = null;

        $user = $this->Session->read('login_user_id');
        $user_group = $this->Session->read("user_group");

        $arr_form_data = $this->request->data;
        // debug($arr_form_data); exit;

        // Set the database configuration based on session data
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');

        // Extract data from form data
        $empPkey = isset($arr_form_data['emp_pkey']) ? $arr_form_data['emp_pkey'] : 0;
        $finYear = isset($arr_form_data['fin_year']) ? $arr_form_data['fin_year'] : 0;


        $uploadedFiles = $_FILES['taxfile'];
        $numFiles = count($uploadedFiles['name']);

        try {
            $dirsep = "/";

            $companycode = strtoupper($this->Session->read('company_code'));
            $user_group = $this->Session->read("user_group");

            $cwd_path = getcwd() . $dirsep;
            $file_webroot_path = "files" . $dirsep . "form16" . $dirsep . $companycode . $dirsep;


            if (!file_exists($cwd_path . $file_webroot_path)) {
                mkdir($cwd_path . $file_webroot_path, 0755, true);
            }

            $acceptedFiles = array();
            $rejectedFiles = array();

            for ($i = 0; $i < $numFiles; $i++) {
                $type = $_FILES['taxfile']['type'][$i];
                // if ($type !== 'application/pdf') {
                //     $message = 'Only PDF files are allowed to be uploaded.';
                //     return json_encode(['success' => false, 'taxHeadKeyValue' => 0, 'message' => $message]);
                // }

                $ext = pathinfo($uploadedFiles['name'][$i], PATHINFO_EXTENSION);

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (false === $ext = array_search(
                    $finfo->file($uploadedFiles['tmp_name'][$i]),
                    [
                        'pdf' => 'application/pdf',
                    ],
                    true
                )) {
                    throw new RuntimeException('Invalid file format.');
                }
                if ($finfo->file($uploadedFiles['size'][$i] > 2000000)) {
                    throw new RuntimeException('Exceeded filesize limit.');
                }


                // Generate a unique filename
                $currentDateTime = date('Y-m-d H:i:s');
                $randomNumber = mt_rand(10000, 99999); // 5-digit random number
                $originalFilename = isset($uploadedFiles['name'][$i]) ? trim($uploadedFiles['name'][$i]) : 'temp';
                $filenameParts = explode('.', $originalFilename);
                $currentFilename = $filenameParts[0];
                $currentFilename = str_replace(',', '', $currentFilename);
                // $filename = $currentFilename . $randomNumber . $currentDateTime . '.' . $ext;





                if (isset($uploadedFiles['tmp_name'][$i])) {
                    $pdfFilePath = $uploadedFiles['tmp_name'][$i];

                    $parser = new Parser();
                    $pdf = $parser->parseFile($pdfFilePath);

                    $document_text = '';

                    foreach ($pdf->getPages() as $page) {
                        $document_text .= $page->getText();
                    }

                    $pattern = '/PAN\s*of\s*the\s*Employee\/Specified\s*senior\s*citizen\s*([A-Za-z0-9]{10})/i';

                    $matches = [];
                    $panValue = 0;

                    if (preg_match($pattern, trim($document_text), $matches)) {
                        $panValue = $matches[1];
                    } else {

                        // return json_encode(['success' => false, 'message' => 'PAN not found in the document.']);
                    }
                }
                $filename =  $panValue . $randomNumber . $currentDateTime . '.' . $ext;

                $validPan = array();

                $validPan = $this->EmployeeTaxTransactions->query("SELECT emp_pkey FROM emp_details WHERE pan_no = '$panValue'");

                if (count($validPan) > 0) {
                    $acceptedFiles[] = $panValue;
                } else {
                    $rejectedFiles[] = $panValue;
                    continue;
                }


                if (!move_uploaded_file($uploadedFiles['tmp_name'][$i], $cwd_path . $file_webroot_path . $filename)) {
                    throw new RuntimeException('Failed to move uploaded file.');
                }


                $sql = "INSERT INTO tax_form_documents (form_name, pan, fin_year, created_by, created_date, status)
        VALUES (:form_name, :pan, :fin_year, :created_by, :created_date, :status)";

                $params = [
                    'form_name' => $filename,
                    'pan' => $panValue,
                    'fin_year' => $finYear,
                    'created_by' => $user,
                    'created_date' => $currentDateTime,
                    'status' => 1,
                ];

                $this->EmployeeTaxTransactions->query($sql, $params);
            }

            $message = 'Document(s) successfully uploaded';
            return json_encode(['status' => 1, 'message' => $message, 'succesfull_files' => $acceptedFiles, 'unsuccesfull_files' => $rejectedFiles]);
        } catch (Exception $ex) {
            // debug($ex);
            $message = 'Document(s) not Uploaded. Something went wrong.';
            return json_encode(['success' => false, 'error' => $ex->getMessage(), 'message' => $message]);
        }
    }


    public function formSixteenDownload($finYear = 0, $empPkey = 0, $pan = 0, $cn = 0)
    {
        try {
            $this->autoRender = false;
            $user = $this->Session->read('login_user_id');
            $user_group = $this->Session->read("user_group");
            $companycode = strtoupper($this->Session->read('company_code'));

            // Set the database configuration based on session data
            $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');

            $dirsep = "/";



            $cwd_path = getcwd() . $dirsep;
            $file_webroot_path = "files" . $dirsep . "form16" . $dirsep . $companycode . $dirsep;



            if (trim($cn) == 'single') {

                $fileNames = array();

                $fileNames = $this->EmployeeTaxTransactions->query("SELECT tfd.form_name, tfd.pan, tfd.fin_year, tfd.created_by,tfd.created_date
                                                                    FROM tax_form_documents tfd
                                                                    WHERE tfd.pan IN (SELECT pan_no FROM emp_details WHERE emp_pkey = $empPkey)
                                                                    AND tfd.created_date IN (SELECT MAX(created_date) FROM tax_form_documents WHERE 
                                                                                                pan IN(SELECT pan_no FROM emp_details WHERE emp_pkey = $empPkey
                                                                                                AND fin_year = '$finYear'))
                                                                                AND tfd.fin_year = '$finYear' ");


                if (count($fileNames) > 0) {
                    foreach ($fileNames as $fileRow) {

                        $response = ['success' => true, 'message' => $fileRow];
                        $fileName = isset($fileRow['tfd']['form_name']) ? $fileRow['tfd']['form_name'] : '';
                        if (!empty($fileName) && !preg_match('/\.pdf$/i', $fileName)) {
                            $fileName .= '.pdf';
                        }
                        $filePath = $file_webroot_path . $dirsep . $fileName;

                        // Check if the file exists and is a regular file
                        if (file_exists($filePath) && is_file($filePath)) {
                            // Set appropriate headers for downloading
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename="' . $fileName . '"');
                            header('Content-Length: ' . filesize($filePath));

                            // Read and output the file content
                            readfile($filePath);
                        }
                    }
                } else {
                }
            } elseif (trim($cn) == 'all') {
                $fileNames = $this->EmployeeTaxTransactions->query("SELECT tfd.form_name, tfd.pan, tfd.fin_year, tfd.created_by, tfd.created_date
                                                                        FROM tax_form_documents tfd
                                                                        INNER JOIN (
                                                                            SELECT fin_year, MAX(created_date) AS max_created_date
                                                                            FROM tax_form_documents
                                                                            WHERE status = 1
                                                                            GROUP BY fin_year
                                                                        ) max_dates ON tfd.fin_year = max_dates.fin_year AND tfd.created_date = max_dates.max_created_date
                                                                        WHERE tfd.pan IN (SELECT pan_no FROM emp_details WHERE emp_pkey = $empPkey)
                                                                        AND tfd.form_name = (
                                                                            SELECT MIN(form_name)
                                                                            FROM tax_form_documents
                                                                            WHERE fin_year = tfd.fin_year
                                                                            AND created_date = max_dates.max_created_date
                                                                        )
                                                                        ORDER BY tfd.fin_year ASC;");
                // debug($fileNames);
                // exit;

                if (count($fileNames) > 0) {
                    // Create a unique ZIP filename
                    $zipFilename = 'form16_download_' . time() . '.zip';
                    // Create a new ZIP archive
                    $zip = new ZipArchive();
                    if ($zip->open($zipFilename, ZipArchive::CREATE) === true) {


                        foreach ($fileNames as $fileRow) {
                            $fileName = isset($fileRow['tfd']['form_name']) ? $fileRow['tfd']['form_name'] : '';
                            $filePath = $file_webroot_path . $dirsep . $fileName;

                            if (file_exists($filePath) && is_file($filePath)) {
                                // Add the file to the ZIP archive
                                $zip->addFile($filePath, $fileName);
                            }
                        }

                        $zip->close();

                        // Set appropriate headers for downloading the ZIP file
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
                        header('Content-Length: ' . filesize($zipFilename));

                        // Read and output the ZIP file content
                        readfile($zipFilename);

                        // Delete the temporary ZIP file
                        unlink($zipFilename);
                    }
                } else {
                }
            }

            // return json_encode(['success' => true,  'message' => 'Form 16 downloaded successfully']);
            // $response = ['success' => true, 'message' => 'Form 16 downloaded successfully'];
        } catch (Exception $ex) {
            $message = 'Document(s) not Uploaded. Something went wrong.';
            //return json_encode(['success' => false, 'error' => $ex->getMessage(), 'message' => $message]);
            $response = ['success' => false, 'error' => $ex->getMessage(), 'message' => 'Document(s) not Uploaded. Something went wrong.'];
        }

        echo json_encode($response);
    }
     // Edited by Akshay on 1-7-2025
    public function getEmployeesByBranch()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');

        if ($this->request->is('ajax')) {
            $branch_code = $this->request->data['branch_code'];
            $condition = "";
            if (trim($branch_code) != '') {
                $condition = " AND branch_code = '$branch_code' ";
            }

            $empList = $this->EmployeeTaxTransactions->query("SELECT emp_pkey, EmpName, employee_id from employee_info ei WHERE emp_status = 1 $condition ORDER BY EmpName ASC;");

            $result = array();
            foreach ($empList as $emp) {
                $result[] = array(
                    'emp_pkey' => $emp['ei']['emp_pkey'],
                    'emp_name' => $emp['ei']['EmpName'],
                    'employee_id' => $emp['ei']['employee_id'],
                );
            }

            echo json_encode($result);
            return;
        }
    }


    // End
}
