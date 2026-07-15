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
class ConfigReportController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'ConfigReport';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Units','EmployeeDetails','Designation','EditPunches');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
	
		
	
		
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
    
    public function Generate()
    {
        $arr_request_data = $this->request->data;
        //debug($arr_request_data);
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $fields = 'EditPunches.*,Branch.branch_name,Designation.desig_name,EmployeeDetails.first_name,EmployeeDetails.last_name';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EditPunches.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EditPunches.emp_id = EmployeeDetails.emp_id',
                    'EmployeeDetails.status=1'
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
         $conditions[] = 'EditPunches.DEVICELOGID is NULL ';
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
        $arr_leavepolicy_details = $this->EditPunches->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
        $this->set("arr_leavepolicy_details",$arr_leavepolicy_details);
    }
    
}
