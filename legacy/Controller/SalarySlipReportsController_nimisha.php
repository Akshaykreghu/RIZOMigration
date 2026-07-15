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
class SalarySlipReportsController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'SalarySlipReports';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','EmpCtcTransaction','EmployeeDetails');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		
		$this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
		
	
		
	}
        
        
	public function Reports($id = 0)
	{
         $date = $id;
            $emp = $this->Session->read('emp_fkey');
		$this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
		  
        
           
  //debug($arr_salary_for_template);
           $arr_empleaverequests = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                   
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       
                                          where ectc.head_operator = 'ADDITION' 
                                          and ectc.item_part = 'DIRECT' 
                                          and ectc.emp_fkey = '$emp'"
                        . " and month_year ='$date' "
                        . "and ed.status = 1  "
                        . "and end_date_effective is null ");

//                   }
                $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$emp' "
                        . " and month_year ='$date' "
                        . "and ed.status = 1 and "
                        . "end_date_effective is null ");
                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                        . "from payroll_master "
                        . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                        . "left join department as d on(d.dept_code = ep.emp_dept) "
                        . "left join designation as dd on (dd.desig_code = ep.designation)"
                        . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                        . " and payroll_master.month_year ='$date'"
                        . " and payroll_master.emp_fkey = '$emp' ");


               // debug($empdetails);

                
                
                $arr_salary_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_empleaverequests,
                    'withoutcomponent' => $salaryslipwithoutcomponents,
                    'empdet' => $empdetails
                );
                $this->set('arr_salary_for_template',$arr_salary_for_template);
                $this->set('date',$date);
	}
        public function SalarySlipdownload($id = 0){
            
               $date = $id;
            $emp = $this->Session->read('emp_fkey');
		$this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
		  
                                             $arr_empleaverequests = $this->EmpCtcTransaction->query("select ectc.month_year,br.branch_name,ed.first_name,ed.last_name,ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ep.joining_date "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                   
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       
                                          where ectc.head_operator = 'ADDITION' 
                                          and ectc.item_part = 'DIRECT' 
                                          and ectc.emp_fkey = '$emp'"
                        . " and month_year ='$date' "
                        . "and ed.status = 1  "
                        . "and end_date_effective is null ");

//                   }
                $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$emp' "
                        . " and month_year ='$date' "
                        . "and ed.status = 1 and "
                        . "end_date_effective is null "); 
                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                        . "from payroll_master "
                        . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                        . "left join department as d on(d.dept_code = ep.emp_dept) "
                        . "left join designation as dd on (dd.desig_code = ep.designation)"
                        . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                        . " and payroll_master.month_year ='$date'"
                        . " and payroll_master.emp_fkey = '$emp' ");
            
               // debug($salaryslipwithoutcomponents);
               // debug($arr_empleaverequests);
            $arr_salary_for_template[] = array(
                //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                'summary' => $arr_empleaverequests,
                'withoutcomponent'=>$salaryslipwithoutcomponents,
                'empdet' => $empdetails
            );
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            //debug($arr_salary_for_template);
	$this->set('arr_salary_for_template',$arr_salary_for_template);
             $arr_emp=$this->getempdetails($emp);
             $this->set('arr_emp', $arr_emp);
   
           $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);
          // debug($arr_comp_contact_info);
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('salary_slipdownload');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('SalarySlipdownload.pdf', 'D');  
        }
public function getempdetails($id=''){
    $this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');
      
		
        $fields = 'designation.desig_name,emp_pkey,address,city,state,pincode,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
        $joins = array(
            array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
              array(
            'table' => 'designation',
            'alias' => 'designation',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeProfessionalDetails.designation = designation.desig_code')
            )
        );
        $conditions  =   array('EmployeeDetails.status'=>1,'EmployeeDetails.emp_pkey="'.$id.'"');
      $arr_emp = $this -> EmployeeDetails -> find("first",array('fields' => $fields,'joins' => $joins,"conditions"=>$conditions));
          return $arr_emp;
}

}
