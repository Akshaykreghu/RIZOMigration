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

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class EmployeeTaxController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'EmployeeTax';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmpTaxSalTrans','EmployeeTaxsalsum','TaxSave','SalaryHeadItems', 'SalaryHeads','FinancialYear','EmployeeTaxTransactions','EmpTaxSalTrans','EmployeeDetails');
    public $components = array('DatatablesManagement','MasterdataManagement');

    /*
     * Dashboard landing view
     */

    public function index() {

        $this->layout = null;
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $emp_pkey = $this->Session->read('emp_fkey');
        $this->set('emp_fkey',$emp_pkey);
//       debug($_COOKIE[$usr]);
    }
    public function Tabs()
    {
        $emp_pkey = $this->Session->read('emp_fkey');
        $this->set('emp_fkey',$emp_pkey);
    }
    public function Calculate($ctc = 0)
    {
        $this->autoRender = false;
       // debug($ctc);
        $itax3 =0;
        $tax1 = 0;
        $tax2 = 0;
        $tax3 = 0;
        $itax1 = 0;
        $itax2 = 0;
        $tax = 0;
        if($ctc > 250000)
        {
            $itax1 = $ctc - 250000;
            $tax1 = $itax1 * 10 /100;
        }
        if($ctc > 500000)
        {
            $itax2 = $ctc - 500000;
            $tax2 = $itax2 * 20 /100;
            
        }
        if($ctc > 1000000)
        {
            $itax3 = $ctc - 1000000;
            $tax3 = $itax3 * 30/100;
        }
        $Tax = isset($tax1)? $tax1 : 0 + isset($tax2) ? $tax2:0 + isset($tax3) ? $tax3:0;
   //     debug($tax);
      
        echo json_encode($tax);
    }
    public function setup() {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeTaxsalsum->useDbConfig = $this->Session->read('ds');
        $this->TaxSave->useDbConfig = $this->Session->read('ds');
        $emp_pkey = $this->Session->read('emp_fkey');
        $employee = $this->EmployeeDetails->find("all",array("conditions"=>array("emp_pkey"=>$emp_pkey)));
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("first",array("conditions"=>array("Year_status"=>"OPEN","is_current_finyear"=>"Y")));
        $this->set('years',$years);
        $start_month = $years['FinancialYear']['start_month'];
        $end_month = $years['FinancialYear']['end_month'];
        $date_now = date('Y-m');
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->EmpTaxSalTrans->useDbConfig = $this->Session->read('ds');
        $conditions = array("EmpTaxSalTrans.emp_fkey"=>$emp_pkey,"EmpTaxSalTrans.end_date_effective is null");
        $joins = array(
            array(
                'table' => 'tax_salary_components',
                'alias' => 'TSC',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmpTaxSalTrans.tax_salary_components_fkey = TSC.tax_salary_components_pkey'),
            ),
            array(
                'table'=>'salary_head_items',
                'alias'=>'SHI',
                'type'=>'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmpTaxSalTrans.salary_head_item_Fkey = SHI.salary_head_item_pkey')
            )
        );
        $taxcomponents = $this->EmpTaxSalTrans->find("all",array('conditions'=>$conditions,'joins'=>$joins,'fields'=>array('EmpTaxSalTrans.salary_head_item_Fkey,EmpTaxSalTrans.tax_salary_components_fkey,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.upper_limit,EmpTaxSalTrans.taxable_salary,TSC.tax_salary_components_name,SHI.item')));
        $this->set('taxcomponents',$taxcomponents);
        $Total = 0;
        foreach($taxcomponents as $val)
        {
            $Total = $Total + $val['EmpTaxSalTrans']['taxable_salary'];
        }
        $this->set('Total',$Total);
        $joinsDec = array(
            array(
                'table' => 'tax_heads',
                'alias' => 'TX',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=>array('TX.tax_heads_pkey = EmployeeTaxTransactions.tax_heads_fkey'),
            ),
            array(
                'table'=>'tax_heads_details',
                'alias'=>'TXD',
                'type'=>'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmployeeTaxTransactions.tax_heads_details_fkey = TXD.tax_heads_details_pkey')
            )
        );
        $conditionsDec = array('EmployeeTaxTransactions.emp_fkey'=>$emp_pkey,"TX.tax_type = 'Deductions' ");
        $taxdeclarations = $this->EmployeeTaxTransactions->find("all",array('fields'=>'TX.tax_name,TX.tax_type,TX.attr1,EmployeeTaxTransactions.tax_value','joins'=>$joinsDec,'conditions'=>$conditionsDec));
        $tottax =0;
        foreach($taxdeclarations as $value)
        {
          if($value['TX']['attr1'] < $value['EmployeeTaxTransactions']['tax_value'])
          {
              $tottax = $tottax + $value['TX']['attr1'];
          }
          else if($value['TX']['attr1'] > $value['EmployeeTaxTransactions']['tax_value'])
          {
              $tottax = $tottax + $value['EmployeeTaxTransactions']['tax_value'];
          }
        }
        $this->set('tottax',$tottax);
        $this->set('taxdeclarations',$taxdeclarations);
        $other_sources = $this->EmployeeTaxTransactions->query("select sum(tax_value)as sums from emp_tax_transactions  where tax_heads_fkey in (select tax_heads_pkey from tax_heads where lcase(tax_type)='income') and emp_fkey='$emp_pkey'
        group by emp_fkey");
        $other_sources = isset($other_sources['0']['0']['sums']) ? $other_sources['0']['0']['sums'] : 0;
        $this->set('other_sources',$other_sources);
        $this->set("employee",$employee);
        $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));
        $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
        $this->set('emp_pkey', $emp_pkey);
        $taxcomponents = $this->EmployeeTaxsalsum->find("all",array("conditions"=>array("emp_fkey"=>$emp_pkey,"end_date_effective is null")));
        $this->set('taxcomponents', $taxcomponents);
        function firstDayOfMonth($uts=null) 
{ 
    $today = is_null($uts) ? getDate() : getDate($uts); 
    $first_day = getdate(mktime(0,0,0,$today['mon'],1,$today['year'])); 
    return $first_day[0]; 
}       
        $emp_joined = $this->EmployeeDetails->query("select * from employee_info where emp_pkey = '$emp_pkey'");
        $joined_date = strtotime($emp_joined['0']['employee_info']['joining_date']);
        $joined = $emp_joined['0']['employee_info']['joining_date'];
        if($start_month < $joined_date)
        {
            $start = strtotime($start_month);
        }
        else
        {
        $start = strtotime($joined);
        }
        $end = strtotime($date_now);
        $endmonth = strtotime($end_month);
        $month = firstDayOfMonth($end);
        $months = array();
        while($month <= $endmonth) {
          $months[] = date('Y-m', $month);
          $month = strtotime("+1 month", $month);
        }
        //debug($months);
        $endmonth1 = strtotime($end_month);
        $month1 = firstDayOfMonth($start);
        $months1 = array();
        while($month1 < $endmonth) {
          $months1[] = date('Y-m', $month1);
          $month1 = strtotime("+1 month", $month1);
        }
        $monthsbefore = array();
        $taxdates = array();
        foreach($months1 as $value)
        {
            $tax = $this->EmployeeTaxsalsum->query("SELECT month_year , sum(salary_amount) tdsdeducted
                                                            FROM emp_salary_slip
                                                            WHERE emp_fkey ='$emp_pkey' 
                                                            and end_date_effective is null and month_year = '$value'
                                                            and payroll_master_fkey in (select payroll_master_pkey from payroll_master where action IN('Processed','Approved'))
                                                            and ucase(salary_head_item_desc)='TDS DEDUCTED' group by month_year ;");
        
            $salary = $this->EmployeeTaxsalsum->query("SELECT month_year , sum(abs(salary_amount)) tdsdeducted    FROM emp_salary_slip
                                                            WHERE emp_fkey ='$emp_pkey' 
                                                            and end_date_effective is null and month_year = '$value'
                                                            and head_operator <>'Deduction' and payroll_master_fkey in (select payroll_master_pkey from payroll_master where action IN('Processed','Approved'))
                                                            and ucase(salary_head_item_desc) not in ('SALARY ADVANCE','LOANS','TDS DEDUCTED') group by month_year");
            $taxdates[] = array(
                'months'=>$value,
                'tax'=>$tax,
                'salary'=>$salary
            );
        }
        $salary = $this->EmployeeDetails->query("select sum(abs(structure_det_value)) as amount from emp_salary_structure where emp_fkey = '$emp_pkey' and end_date_effective is null");
        $this->set('taxdates', $taxdates);
        $this->set("salary",$salary);
        $this->set("months1",$months1);
        $this->set("months",$months);
        $this->set("end_month",$end_month);
        $this->set("endmonth",$endmonth);
        $this->set("endmonth",$endmonth);
        //gender
        $gender = $this->EmployeeDetails->find("all",array("conditions"=>array("emp_pkey"=>$emp_pkey)));
        $classifications = $gender['0']['EmployeeDetails']['classification'];
        $this->set("classifications",$classifications);
        $datasave = array();
        $datasave['incom'] = $Total;
        $datasave['emp_fkey'] = $emp_pkey;
        $datasave['other'] = $other_sources;
        $datasave['deductions'] = $tottax;
        $this->TaxSave->save($datasave);
        
    }
    public function Employeesetup($emp_pkey = 0) {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeTaxsalsum->useDbConfig = $this->Session->read('ds');
        $this->TaxSave->useDbConfig = $this->Session->read('ds');
        $employee = $this->EmployeeDetails->find("all",array("conditions"=>array("emp_pkey"=>$emp_pkey)));
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("first",array("conditions"=>array("Year_status"=>"OPEN","is_current_finyear"=>"Y")));
        $this->set('years',$years);
        $start_month = $years['FinancialYear']['start_month'];
        $end_month = $years['FinancialYear']['end_month'];
        $date_now = date('Y-m');
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->EmpTaxSalTrans->useDbConfig = $this->Session->read('ds');
        $conditions = array("EmpTaxSalTrans.emp_fkey"=>$emp_pkey,"EmpTaxSalTrans.end_date_effective is null");
        $joins = array(
            array(
                'table' => 'tax_salary_components',
                'alias' => 'TSC',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmpTaxSalTrans.tax_salary_components_fkey = TSC.tax_salary_components_pkey'),
            ),
            array(
                'table'=>'salary_head_items',
                'alias'=>'SHI',
                'type'=>'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmpTaxSalTrans.salary_head_item_Fkey = SHI.salary_head_item_pkey')
            )
        );
        $taxcomponents = $this->EmpTaxSalTrans->find("all",array('conditions'=>$conditions,'joins'=>$joins,'fields'=>array('EmpTaxSalTrans.salary_head_item_Fkey,EmpTaxSalTrans.tax_salary_components_fkey,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.upper_limit,EmpTaxSalTrans.taxable_salary,TSC.tax_salary_components_name,SHI.item')));
        $this->set('taxcomponents',$taxcomponents);
        $Total = 0;
        foreach($taxcomponents as $val)
        {
            $Total = $Total + $val['EmpTaxSalTrans']['taxable_salary'];
        }
        $this->set('Total',$Total);
        $joinsDec = array(
            array(
                'table' => 'tax_heads',
                'alias' => 'TX',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=>array('TX.tax_heads_pkey = EmployeeTaxTransactions.tax_heads_fkey'),
            ),
            array(
                'table'=>'tax_heads_details',
                'alias'=>'TXD',
                'type'=>'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmployeeTaxTransactions.tax_heads_details_fkey = TXD.tax_heads_details_pkey')
            )
        );
        $conditionsDec = array('EmployeeTaxTransactions.emp_fkey'=>$emp_pkey,"TX.tax_type = 'Deductions' ");
        $taxdeclarations = $this->EmployeeTaxTransactions->find("all",array('fields'=>'TX.tax_name,TX.tax_type,TX.attr1,EmployeeTaxTransactions.tax_value','joins'=>$joinsDec,'conditions'=>$conditionsDec));
        $tottax =0;
        foreach($taxdeclarations as $value)
        {
          if($value['TX']['attr1'] < $value['EmployeeTaxTransactions']['tax_value'])
          {
              $tottax = $tottax + $value['TX']['attr1'];
          }
          else if($value['TX']['attr1'] > $value['EmployeeTaxTransactions']['tax_value'])
          {
              $tottax = $tottax + $value['EmployeeTaxTransactions']['tax_value'];
          }
        }
        $this->set('tottax',$tottax);
        $this->set('taxdeclarations',$taxdeclarations);
        $other_sources = $this->EmployeeTaxTransactions->query("select sum(tax_value)as sums from emp_tax_transactions  where tax_heads_fkey in (select tax_heads_pkey from tax_heads where lcase(tax_type)='income') and emp_fkey='$emp_pkey'
        group by emp_fkey");
        $other_sources = isset($other_sources['0']['0']['sums']) ? $other_sources['0']['0']['sums'] : 0;
        $this->set('other_sources',$other_sources);
        $this->set("employee",$employee);
        $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));
        $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
        $this->set('emp_pkey', $emp_pkey);
        $taxcomponents = $this->EmployeeTaxsalsum->find("all",array("conditions"=>array("emp_fkey"=>$emp_pkey,"end_date_effective is null")));
        $this->set('taxcomponents', $taxcomponents);
        function firstDayOfMonth($uts=null) 
{ 
    $today = is_null($uts) ? getDate() : getDate($uts); 
    $first_day = getdate(mktime(0,0,0,$today['mon'],1,$today['year'])); 
    return $first_day[0]; 
}       
        $emp_joined = $this->EmployeeDetails->query("select * from employee_info where emp_pkey = '$emp_pkey'");
        $joined_date = strtotime($emp_joined['0']['employee_info']['joining_date']);
        $joined = $emp_joined['0']['employee_info']['joining_date'];
        if($start_month < $joined_date)
        {
            $start = strtotime($start_month);
        }
        else
        {
        $start = strtotime($joined);
        }
        $end = strtotime($date_now);
        $endmonth = strtotime($end_month);
        $month = firstDayOfMonth($end);
        $months = array();
        while($month <= $endmonth) {
          $months[] = date('Y-m', $month);
          $month = strtotime("+1 month", $month);
        }
        //debug($months);
        $endmonth1 = strtotime($end_month);
        $month1 = firstDayOfMonth($start);
        $months1 = array();
        while($month1 < $endmonth) {
          $months1[] = date('Y-m', $month1);
          $month1 = strtotime("+1 month", $month1);
        }
        $monthsbefore = array();
        $taxdates = array();
        foreach($months1 as $value)
        {
            $tax = $this->EmployeeTaxsalsum->query("SELECT month_year , sum(salary_amount) tdsdeducted
                                                            FROM emp_salary_slip
                                                            WHERE emp_fkey ='$emp_pkey' 
                                                            and end_date_effective is null and month_year = '$value'
                                                            and payroll_master_fkey in (select payroll_master_pkey from payroll_master where action IN('Processed','Approved'))
                                                            and ucase(salary_head_item_desc)='TDS DEDUCTED' group by month_year ;");
        
            $salary = $this->EmployeeTaxsalsum->query("SELECT month_year , sum(abs(salary_amount)) tdsdeducted    FROM emp_salary_slip
                                                            WHERE emp_fkey ='$emp_pkey' 
                                                            and end_date_effective is null and month_year = '$value'
                                                            and head_operator <>'Deduction' and payroll_master_fkey in (select payroll_master_pkey from payroll_master where action IN('Processed','Approved'))
                                                            and ucase(salary_head_item_desc) not in ('SALARY ADVANCE','LOANS','TDS DEDUCTED') group by month_year");
            $taxdates[] = array(
                'months'=>$value,
                'tax'=>$tax,
                'salary'=>$salary
            );
        }
        $salary = $this->EmployeeDetails->query("select sum(abs(structure_det_value)) as amount from emp_salary_structure where emp_fkey = '$emp_pkey' and end_date_effective is null");
        $this->set('taxdates', $taxdates);
        $this->set("salary",$salary);
        $this->set("months1",$months1);
        $this->set("months",$months);
        $this->set("end_month",$end_month);
        $this->set("endmonth",$endmonth);
        $this->set("endmonth",$endmonth);
        //gender
        $gender = $this->EmployeeDetails->find("all",array("conditions"=>array("emp_pkey"=>$emp_pkey)));
        $classifications = $gender['0']['EmployeeDetails']['classification'];
        $this->set("classifications",$classifications);
        $datasave = array();
        $datasave['incom'] = $Total;
        $datasave['emp_fkey'] = $emp_pkey;
        $datasave['other'] = $other_sources;
        $datasave['deductions'] = $tottax;
        $this->TaxSave->save($datasave);
        
    }
    
    public function setupshow($emp_pkey = 0) {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        

        
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years',$years);
        $this->EmployeeTaxTransactions->useDbConfig = $this->Session->read('ds');
        $this->EmpTaxSalTrans->useDbConfig = $this->Session->read('ds');
        $conditions = array("EmpTaxSalTrans.emp_fkey"=>$emp_pkey,"EmpTaxSalTrans.end_date_effective is null");
        $joins = array(
            array(
                'table' => 'tax_salary_components',
                'alias' => 'TSC',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmpTaxSalTrans.tax_salary_components_fkey = TSC.tax_salary_components_pkey'),
            ),
            array(
                'table'=>'salary_head_items',
                'alias'=>'SHI',
                'type'=>'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmpTaxSalTrans.salary_head_item_Fkey = SHI.salary_head_item_pkey')
            )
        );
        $taxcomponents = $this->EmpTaxSalTrans->find("all",array('conditions'=>$conditions,'joins'=>$joins,'fields'=>array('EmpTaxSalTrans.salary_head_item_Fkey,EmpTaxSalTrans.tax_salary_components_fkey,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.availed_salary,EmpTaxSalTrans.upper_limit,EmpTaxSalTrans.taxable_salary,TSC.tax_salary_components_name,SHI.item')));
        $this->set('taxcomponents',$taxcomponents);
        $Total = 0;
        foreach($taxcomponents as $val)
        {
            $Total = $Total + $val['EmpTaxSalTrans']['taxable_salary'];
        }
        $this->set('Total',$Total);
        $joinsDec = array(
            array(
                'table' => 'tax_heads',
                'alias' => 'TX',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=>array('TX.tax_heads_pkey = EmployeeTaxTransactions.tax_heads_fkey'),
            ),
            array(
                'table'=>'tax_heads_details',
                'alias'=>'TXD',
                'type'=>'LEFT',
                'foreignKey' => false,
                'conditions'=>array('EmployeeTaxTransactions.tax_heads_details_fkey = TXD.tax_heads_details_pkey')
            )
        );
        $conditionsDec = array('EmployeeTaxTransactions.emp_fkey'=>$emp_pkey,"TX.tax_type = 'Deductions' ");
        $taxdeclarations = $this->EmployeeTaxTransactions->find("all",array('fields'=>'TX.tax_name,TX.tax_type,TX.attr1,EmployeeTaxTransactions.tax_value','joins'=>$joinsDec,'conditions'=>$conditionsDec));
        $tottax =0;
        foreach($taxdeclarations as $value)
        {
          if($value['TX']['attr1'] < $value['EmployeeTaxTransactions']['tax_value'])
          {
              $tottax = $tottax + $value['TX']['attr1'];
          }
          else if($value['TX']['attr1'] > $value['EmployeeTaxTransactions']['tax_value'])
          {
              $tottax = $tottax + $value['EmployeeTaxTransactions']['tax_value'];
          }
        }
        $this->set('tottax',$tottax);
        $this->set('taxdeclarations',$taxdeclarations);
        $other_sources = $this->EmployeeTaxTransactions->query("select sum(tax_value)as sums from emp_tax_transactions  where tax_heads_fkey in (select tax_heads_pkey from tax_heads where lcase(tax_type)='income') and emp_fkey='$emp_pkey'
        group by emp_fkey");
        $other_sources = isset($other_sources['0']['0']['sums']) ? $other_sources['0']['0']['sums'] : 0;
        $this->set('other_sources',$other_sources);
        
    }
    
    public function Proccess($emp_pkey = 0)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        $company_code = $this->Session->read('company_code');
        $years= $this->EmployeeDetails->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = 1 and is_current_finyear = 'Y' and status = '1'");
             $year = $years['0']['fin_year']['fin_year'];
        $other_sources = $this->EmployeeDetails->query("select tax_salary_distribution_fn ('$company_code',$emp_pkey,'$year','$user_id')");
        $this->setup($emp_pkey);
        $this->render('setup');
    }
}
