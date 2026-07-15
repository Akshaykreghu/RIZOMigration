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
class EmpeditpunchesController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Empeditpunches';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'EditPunches', 'EmployeeDetails','EmployeeProfessionalDetails ');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index() {
         $this->layout = null;
       //$arr_months = json_decode($this->getmonths());
        //$this->set('arr_months',$arr_months);
//        
        //$arr_employees = json_decode($this->listempemployees());
      //  $this->set('arr_employees',$arr_employees);
         
         
         
         
         
         
    }

    public function listpunches() {
       
           $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        //debug($this->Session->read('ds'));
//debug($_REQUEST);
        $employee = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
        $month = isset($_REQUEST['month']) ? $_REQUEST['month'] : '';
        $includeinactive = isset($_REQUEST['includeinactive']) ? $_REQUEST['includeinactive'] : '';
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();

        $condition = array();

        if ($includeinactive) {
            if ($includeinactive == 'N') {
                $condition['status'] = array('Y');
            } else {
                $condition['status'] = array('Y', 'N');
            }
        } else {
            $condition['status'] = array('Y');
        }
        if ($employee) {
            $condition['emp_id'] = $employee;
        }
        if ($month) {
            //$condition['MONTH(LOGDATE)'] = $month;
            $condition['DATE_FORMAT(LOGDATE,"%Y-%c")'] = $month;
        }
        $count = 0;
		$arr_mispunches = array();
        if ($employee) {
            $count = $this->EditPunches->find("count", array('conditions' => $condition));
            $arr_mispunches = $this->EditPunches->find("all", array('conditions' => $condition, 'order' => array('EditPunches.LOGDATE'), 'limit' => intval($limit), 'offset' => intval($ofst)));
        }
        foreach ($arr_mispunches as $key => $value) {
            $resp_mispunches["rows"][$key] = $value["EditPunches"];
        }
        $resp_mispunches["total"] = $count;
        echo json_encode($resp_mispunches);
    }

    public function form($empid=0) {
        $this->layout = null;
        $this->set("empid", $empid);
    }

    public function remove() {
        $this->autoRender = FALSE;
        $device_attandance_seq = 0;
        $resp = array('success' => false);
        if (isset($_REQUEST['device_attandance_seq']) && $_REQUEST['device_attandance_seq'] != 0) {
            $device_attandance_seq = $_REQUEST['device_attandance_seq'];

            $data['device_attandance_seq'] = $device_attandance_seq;
            $data['status'] = "D";
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            $this->EditPunches->save($data);
            $resp = array('success' => true);
        }
        echo json_encode($resp);
    }

    public function savenew() {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $resp = array('success' => true);
        $data = array();
        $data["C1"] = $_POST["C1"];
        $data["C3"] = $_POST["C3"];
        
        $logdate = $_POST["LOGDATE"].' '.$_POST["LOGTIME"];
        $d = strtotime($logdate);
        $data["LOGDATE"] = date("Y-m-d H:i:s", $d);
        $data["emp_id"] = $_POST["empid"];
        $data["device_attandance_seq"] = 0;
        $data["DEVICEID"] = 0;
        $data["company_code"] = $this->Session->read('company_code');
        $br_details = $this->EmployeeDetails->find("first", array("conditions" => array("emp_id" => $_POST["empid"]), "fields" => array("branch_code")));
        $data["branch_code"] = isset($br_details['EmployeeDetails']['branch_code']) ? $br_details['EmployeeDetails']['branch_code'] : "";
        $this->EditPunches->save($data);
        echo json_encode($resp);
        
    }

    public function savepunch() {

        $resp = array('success' => true);
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EditPunches->save($_POST);
        echo json_encode($resp);
        //debug($_POST);
    }
    
    public function getmonths(){
        $this->autoRender = FALSE;
        $arr_months = array();
        $start_month = strtotime(date('Y-n', strtotime("+1 month", strtotime(date('Y-n')))));
        for ($i = 0; $i < 10; $i++) {
            $month = date('Y-n', strtotime("-$i month", $start_month));
            $arr_months[] = array(
                'id' => $month,
                'text' => $month
            );
        }
        return json_encode($arr_months);
    }
public function listempemployees(){
//    $this->layout = FALSE; 
//      //$this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');  
//$emp_fkey = $this->Session->read('emp_fkey'); 	
//$joins = array(
//            
//            array(
//                'table' => 'emp_proff',
//                'alias' => 'EmployeeProffessionals',
//                'type' => 'LEFT',
//                'foreignKey' => false,
//                'conditions' => array(
//                    'EmployeeProffessionals.emp_fkey = EmployeeDetails.emp_pkey',
//                    'EmployeeDetails.status=1'
//                )
//            )
//        );      
//$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
// $arr_conditions = array('status' => 1,'EmployeeProffessionals.attr1'=>$emp_fkey);
//         $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("joins"=>$joins, "fields" => array("emp_pkey", "emp_name"), "conditions" => $arr_conditions)));
//        // debug($arr_employees);
//        $resp_emp = array( );
//		
//			$data["id"] 	  = "";
//			$data["text"]     = " ";
//			$resp_emp[] = $data;
//		foreach ($arr_employees as $key => $value) {
//
//			$data["id"] 	  =  $value['emp_pkey'];
//			$data["text"]     =  $value['emp_name'];
//			$resp_emp[] = $data;
//		}
////$resp_emp['total'] = count($arr_emp);
//        echo json_encode($resp_emp);
//        $this->autoRender=FALSE;
    $emp_fkey = $this->Session->read('emp_fkey'); 
      $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,EmployeeDetails.emp_id';
        $joins = array(
            array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        $conditions  =   array('status'=>1,'EmployeeProfessionalDetails.attr1'=>$emp_fkey);
        
        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp =  $this -> EmployeeDetails ->find("all",array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'order'=>'EmployeeDetails.first_name' /*code by sruthi 11/08/2010*/
        ));
      /*
        foreach ($arr_emp as $key => $value) {
                  $resp_emp["emp"][$key] = array_merge($value["EmployeeDetails"],$value["EmployeeProfessionalDetails"],$value[0]);
              }*/
      
		$resp_emp = array( );
		
			$data["id"] 	  = "";
			$data["text"]     = " ";
			$resp_emp[] = $data;
		foreach ($arr_emp as $key => $value) {

			$data["id"] 	  =  $value["EmployeeDetails"]['emp_id'];
			$data["text"]     =  $value[0]['name'].' --'.$value["EmployeeProfessionalDetails"]['emp_company_id'];
			$resp_emp[] = $data;
		}
//$resp_emp['total'] = count($arr_emp);
        echo json_encode($resp_emp);
        $this->autoRender=FALSE;
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
}
