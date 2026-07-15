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

App::uses('ConnectionManager', 'Model','Organization');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class AnalysisController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Analysis';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Assets','EmployeeMenu','allocate');
    public $components = array('MasterdataManagement');

    public function index() {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array('order'=> array('emp_pkey DESC'), "fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        $this->set('arr_employees', $arr_employees);
//        debug($arr_employees);
    }
    
    public function duplication_founds() {
//        $this->autoRender = false;
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $depats = $this->Assets->query("SELECT     dept_code,    COUNT(dept_code) FROM    department GROUP BY dept_code HAVING COUNT(dept_code) > 1; ");
        $dept = $this->Assets->query("SELECT     desig_code,    COUNT(desig_code) FROM    designation GROUP BY desig_code HAVING COUNT(desig_code) > 1; ");
        $bran = $this->Assets->query("SELECT     branch_code,    COUNT(branch_code) FROM    branches GROUP BY branch_code HAVING COUNT(branch_code) > 1; ");
        if(!empty($depats)){
            $table = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Duplicate Departments Found 
              </div><table class='table table-bordered'><tr><td>Department Name</td></tr>";
            foreach ($depats as $vals){
                $table .= "<tr><td>".$vals['department']['dept_code']."</td></tr>";
            }
            echo $table;
        }
        
        if(!empty($dept)){
            $table = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Duplicate Designation Found 
              </div><table class='table table-bordered'><tr><td>Designation Name</td></tr>";
            foreach ($dept as $vals){
                $table .= "<tr><td>".$vals['designation']['desig_code']."</td></tr>";
            }
            echo $table;
        }
        
        if(!empty($bran)){
            $tables = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Duplicate Branches Found 
              </div><table class='table table-bordered'><tr><td>Branches Name</td></tr>";
            foreach ($bran as $vals){
                $tables .= "<tr><td>".$vals['branches']['branch_code']."</td></tr>";
            }
            echo $tables;
        }
    }
    
    public function salary_structure_allocate_issues() {
//        $this->autoRender = false;
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $fin_year = $this->Assets->query("select fin_year from fin_year where lcase(Year_status)='open' and vattr1 = 1 and is_current_finyear='Y'
   and status=1 and branch_code= (select branch_code from emp_details where emp_Pkey=Pemp_fkey );   ");
        $gross_salary = $this->Assets->query("select emp_anual_ctc  from  emp_ctc_transaction  where emp_fkey=Pemp_fkey and end_date_effective is null; ");
        $types = $this->Assets->query("select head_operator,head_occurance  
 from salary_heads  where head_pkey in (select head_fkey from  salary_head_items 
 where  salary_head_item_pkey=vsalary_head_item_fkey);  ");
        
        $item_part = $this->Assets->query("select item ,item_part,item_type  from salary_head_items  where salary_head_item_pkey=vsalary_head_item_fkey;' ");
        
        $rembalance = $this->Assets->query("SELECT salary_head_item_fkey   FROM salary_structure_details
 where structure_id =Pemp_structure_id  and structure_det_operator='rembalance'; ");
        
        $bran = $this->Assets->query("select item ,item_part,item_type  from salary_head_items  where salary_head_item_pkey=vsalary_head_item_fkey;' ");
        
        
        if(empty($fin_year)){
            $table = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Financial Year Not Added 
              </div>";
            echo $table;
        }
        
        if(empty($gross_salary)){
            $table = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Salary Not Uploaded 
              </div>";
            echo $table;
        }
        
        if(!empty($types)){
            $table = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Multiple Heads Found 
              </div> ";
            echo $table;
        }
        
        if(!empty($gross_salary)){
            $table = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Salary Not Uploaded 
              </div> ";
            echo $table;
        }
        
        if(!empty($gross_salary)){
            $table = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Salary Not Uploaded 
              </div> ";
            echo $table;
        }
        
        if(!empty($bran)){
            $tables = "Issue Found! Duplicate Branches Found <br><table class='table table-bordered'><tr><td>Branches Name</td></tr>";
            foreach ($bran as $vals){
                $tables .= "<tr><td>".$vals['branches']['branch_code']."</td></tr>";
            }
            echo $tables;
        }
    }
    
    public function payroll($emp_pkey = 0) {
        $this->autoRender = false;
        $this->Assets->useDbConfig = $this->Session->read('ds');
        $structure = $this->Assets->query("select emp_fkey,emp_structure_id,prorate_code,prorate_desc,defined_structure_for,	 
salary_head_item_fkey,salary_head_item_desc,structure_det_value,head_operator,head_type	,item_part from emp_salary_structure 
  where emp_fkey='$emp_pkey' and end_date_effective is null ; ");
        $dept = $this->Assets->query("SELECT     desig_code,    COUNT(desig_code) FROM    designation GROUP BY desig_code HAVING COUNT(desig_code) > 1; ");
        $bran = $this->Assets->query("SELECT     branch_code,    COUNT(branch_code) FROM    branches GROUP BY branch_code HAVING COUNT(branch_code) > 1; ");
        if(empty($structure)){
            $table = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-ban'></i> Alert!</h4>
                Issue Found! Salary Structure Not Assigned 
              </div>";
            echo $table;
        }
        
        if(!empty($dept)){
            $table = "Issue Found! Duplicate Designations Found <br><table class='table table-bordered'><tr><td>Designation Name</td></tr>";
            foreach ($dept as $vals){
                $table .= "<tr><td>".$vals['designation']['desig_code']."</td></tr>";
            }
            echo $table;
        }
        
        if(!empty($bran)){
            $tables = "Issue Found! Duplicate Branches Found <br><table class='table table-bordered'><tr><td>Branches Name</td></tr>";
            foreach ($bran as $vals){
                $tables .= "<tr><td>".$vals['branches']['branch_code']."</td></tr>";
            }
            echo $tables;
        }
    }

}
