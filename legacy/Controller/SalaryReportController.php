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
class SalaryReportController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'SalaryReport';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Units','EmployeeDetails','Designation','EditPunches','SalarySlip','Payrollmaster','EmpCtcTransaction','EmployeeSalaryStructure');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
	
		
	$arr_reporttypes = array(
//            'employee' => 'Employee Information',
           // 'LeaveDetaillsReport' =>  'Leave Details Reports',
       'SummaryPayroll' => 'Payroll Summary Report',
            'salary' => 'CTC Summary Report',
         'salarystructure' =>  'CTC Detail Reports',
//            'Salaryslip' =>  'Salary slip Reports',
//ARUN
            //'Grosssalary' =>  'Gross Salary Reports',
            
//            'DetailedAttendance' =>  'Detailed Attendance Reports',
//             'AttendanceRep' =>  'Attendance Reports',
            
            /*'leave' => 'Leaves Report',
            'attendance' => 'Attendance Summary',*/
        );
        $this->set('arr_reporttypes',$arr_reporttypes);
		
	}
	
	public function Branches($hierarchy = '') {
        $this->autoRender = FALSE;
        
        $this->Units->useDbConfig = $this->Session->read('ds');
        $branches = $this->Units->find("all",array("conditions"=>array("status"=>1)));

        $arr_resp = array(
            'data' => array()
        );
        $i = 0;
        foreach ($branches as $key => $att) {
            $arr_resp['data'][$key]/* ['EmpName'] */ = $att['Units'];
            
            $i++;
        }
        return json_encode($arr_resp);
    }
    
    public function Designation($hierarchy = '') {
        $this->autoRender = FALSE;
        
        $this->Designation->useDbConfig = $this->Session->read('ds');
        $branches = $this->Designation->find("all",array("conditions"=>array("status"=>1)));

        $arr_resp = array(
            'data' => array()
        );
        $i = 0;
        foreach ($branches as $key => $att) {
            $arr_resp['data'][$key]/* ['EmpName'] */ = $att['Designation'];
            
            $i++;
        }
        return json_encode($arr_resp);
    }
    
    
    public function Departments($hierarchy = '') {
        $this->autoRender = FALSE;
        
        $this->Units->useDbConfig = $this->Session->read('ds');
        $branches = $this->Units->find("all",array("conditions"=>array("status"=>1)));

        $arr_resp = array(
            'data' => array()
        );
        $i = 0;
        foreach ($branches as $key => $att) {
            $arr_resp['data'][$key]/* ['EmpName'] */ = $att['Units'];
            
            $i++;
        }
        return json_encode($arr_resp);
    }
    
    public function Employees($hierarchy = '') {
        $this->autoRender = FALSE;
        
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $employees = $this->EmployeeDetails->find("all",array("conditions"=>array("status"=>1),"fields"=>array("concat(first_name,' ',last_name) as Name,emp_pkey")));

        $arr_resp = array(
            'data' => array()
        );
        $i = 0;
        foreach ($employees as $key => $att) {
            $arr_resp['data'][$key]/* ['EmpName'] */ = array_merge($att['0'],$att['EmployeeDetails']);
            
            $i++;
        }
        return json_encode($arr_resp);
    }
    
    public function Salaryslip()
    {
        $arr_request_data = $this->request->data;
        //debug($arr_request_data);
        $this->SalarySlip->useDbConfig = $this->Session->read('ds');
        $fields = 'Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name,SalarySlip.*';
        $joins = array(
            
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'SalarySlip.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'emp_proff',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.emp_pkey = emp_proff.emp_fkey'
                )
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'emp_proff.designation = Designation.desig_code'
                )
            )
        );
         $conditions[] = "end_date_effective is null and EmployeeDetails.status=1 and month_year = '2015-12' ";
         if(isset($arr_request_data['branch']))
         {
             
             $conditions[] = array('Branch.branch_code' => $arr_request_data['branch']);
         }
         if(isset($arr_request_data['Designation']))
         {
             
             $conditions[] = array('Designation.desig_code' => $arr_request_data['Designation']);
         }
         if(isset($arr_request_data['Employees']))
         {
             
             $conditions[] = array('EmployeeDetails.emp_pkey' => $arr_request_data['Employees']);
         }
         
         
         
         
//                              $arr_empleaverequests = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* from emp_salary_slip as ectc "
//                                      . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) left join branches as br on (br.branch_code = ep.emp_branch)
//                                          where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and ectc.emp_fkey = '$leavepolicygroupid' and month_year ='$from' and ed.status = 1  and end_date_effective is null ");  
//
////                   }
////                    
//                   
//             $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* from emp_salary_slip as ectc "
//                                      . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) left join branches as br on (br.branch_code = ep.emp_branch)
//                                          where ectc.head_operator = 'Deduction' and ectc.emp_fkey = '$leavepolicygroupid'  and month_year ='$from' and ed.status = 1 and end_date_effective is null ");  
//            
//           $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
//   
//            
//            $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,d.dept_name,dd.desig_name,payroll_master.* from payroll_master left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) left join department as d on(d.dept_code = ep.emp_dept) left join designation as dd on (dd.desig_code = ep.designation) where payroll_master.payroll_master_pkey = '$payroll_pkey' and payroll_master.month_year ='$from' and payroll_master.emp_fkey = '$leavepolicygroupid' ");
//            
//         
         
         
         
        $arr_leavepolicy_details = $this->SalarySlip->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions,$conditions[] = "SalarySlip.head_operator = 'ADDITION' and SalarySlip.item_part = 'DIRECT' "));
        $this->set("arr_leavepolicy_details",$arr_leavepolicy_details);
        
        $salaryslipwithoutcomponents = $this->SalarySlip->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions,$conditions[] = "SalarySlip.head_operator = 'Deduction' and SalarySlip.item_part = 'DIRECT' "));
        $this->set("arr_leavepolicy_details",$arr_leavepolicy_details);
        
        debug($arr_leavepolicy_details);
        debug($salaryslipwithoutcomponents);
        foreach($arr_empleaverequests as $val)
      {
          $emp = $val['ectc']['head_operator'];
          $emppk = $val['ectc']['head_type'];
          $itempart = $val['ectc']['item_part'];
          $employee_attendance[$emppk][$emp][$itempart][] = $val;
          
      }
    }
    
    public function SummaryPayroll()
    {
        $arr_request_data = $this->request->data;
        //debug($arr_request_data);
        $this->Payrollmaster->useDbConfig = $this->Session->read('ds');
        $fields = 'Branch.branch_name,Designation.desig_name,emp_proff.emp_company_id,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeDetails.emp_id,Payrollmaster.*';
        $joins = array(
            
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Payrollmaster.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'emp_proff',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.emp_pkey = emp_proff.emp_fkey'
                )
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'emp_proff.designation = Designation.desig_code'
                )
            )
        );
         $conditions[] = "EmployeeDetails.status=1 and month_year = '2015-12' ";
         if(isset($arr_request_data['branch']))
         {
             
             $conditions[] = array('Branch.branch_code' => $arr_request_data['branch']);
         }
         if(isset($arr_request_data['Designation']))
         {
             
             $conditions[] = array('Designation.desig_code' => $arr_request_data['Designation']);
         }
         if(isset($arr_request_data['Employees']))
         {
             
             $conditions[] = array('EmployeeDetails.emp_pkey' => $arr_request_data['Employees']);
         }
         
         
        $arr_payroll_details = $this->Payrollmaster->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
        $this->set("arr_payroll_details",$arr_payroll_details);
        
    }
    
    public function salary()
    {
        $arr_request_data = $this->request->data;
        //debug($arr_request_data);
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $fields = 'Branch.branch_name,Designation.desig_name,emp_proff.emp_company_id,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeDetails.emp_id,EmpCtcTransaction.*';
        $joins = array(
            
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmpCtcTransaction.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'emp_proff',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.emp_pkey = emp_proff.emp_fkey'
                )
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'emp_proff.designation = Designation.desig_code'
                )
            )
        );
         $conditions[] = "EmployeeDetails.status=1 and end_date_effective is null ";
         if(isset($arr_request_data['branch']))
         {
             
             $conditions[] = array('Branch.branch_code' => $arr_request_data['branch']);
         }
         if(isset($arr_request_data['Designation']))
         {
             
             $conditions[] = array('Designation.desig_code' => $arr_request_data['Designation']);
         }
         if(isset($arr_request_data['Employees']))
         {
             
             $conditions[] = array('EmployeeDetails.emp_pkey' => $arr_request_data['Employees']);
         }
         
         
        $arr_payroll_details = $this->EmpCtcTransaction->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
        $this->set("arr_payroll_details",$arr_payroll_details);
        //debug($arr_payroll_details);
        
    }
    
   public function salarystructure()
    {
        $arr_request_data = $this->request->data;
        //debug($arr_request_data);
        $this->EmployeeSalaryStructure->useDbConfig = $this->Session->read('ds');
        $fields = 'Branch.branch_name,Designation.desig_name,emp_proff.emp_company_id,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeDetails.emp_id,EmployeeSalaryStructure.*';
        $joins = array(
            
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeSalaryStructure.emp_fkey = EmployeeDetails.emp_pkey',
                    'EmployeeDetails.status=1'
                )
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'emp_proff',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeDetails.emp_pkey = emp_proff.emp_fkey'
                )
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'emp_proff.designation = Designation.desig_code'
                )
            )
        );
         $conditions[] = "EmployeeDetails.status=1 and end_date_effective is null and EmployeeSalaryStructure.head_operator = 'ADDITION'";
         if(isset($arr_request_data['branch']))
         {
             
             $conditions[] = array('Branch.branch_code' => $arr_request_data['branch']);
         }
         if(isset($arr_request_data['Designation']))
         {
             
             $conditions[] = array('Designation.desig_code' => $arr_request_data['Designation']);
         }
         if(isset($arr_request_data['Employees']))
         {
             
             $conditions[] = array('EmployeeDetails.emp_pkey' => $arr_request_data['Employees']);
         }
         
         
        $arr_payroll_details = $this->EmployeeSalaryStructure->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
        $this->set("arr_payroll_details",$arr_payroll_details);
        
        $salary_details = array();
         foreach($arr_payroll_details as $val)
      {
          $emp = $val['EmployeeSalaryStructure']['emp_fkey'];
          $salary_detailss[$emp][] = $val;
          
      }
      
      $this->set("salary_detailss",$salary_detailss);
        
    } 
    
}
