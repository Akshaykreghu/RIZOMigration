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
class EmployeeHierarchyController extends AppController {

	public $name = 'EmployeeHierarchy';
	public $datatable;

	public $uses = array('CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'EmployeeStructure');
	public $components = array('MasterdataManagement');

	public function listemployees() {

		$fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
		$joins = array( array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')));
		$conditions = array('status' => 1);

		$resp_emp = array();
		$this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');
		$arr_emp = $this -> EmployeeDetails -> find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
	
		debug($arr_emp);

		$resp_emp["rows"] = array();
		
		foreach ($arr_emp as $key => $value) {

			//debug($value);
			$data['id'] = $value["EmployeeDetails"]['emp_pkey'];
			$data['data'] = array($value[0]['name']);
			$resp_emp["rows"][] = $data;
		}
		echo json_encode($resp_emp);
		$this -> autoRender = FALSE;
	}

	public function listchildEmployee() {

		$parentEmpKey = (isset($_REQUEST['parentEmpKey']) ? $_REQUEST['parentEmpKey'] : -1);
		$this -> EmployeeStructure -> useDbConfig = $this -> Session -> read('ds');
		//echo $shift;
		$childEmps = $this -> EmployeeStructure -> find("list", array("fields" => "emp_id", "conditions" => array("parent_id" => $parentEmpKey)));

		$fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
		$joins = array( array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')));
		$conditions = array('status' => 1, 'EmployeeDetails.emp_pkey' => $childEmps);

		$resp_emp = array();
		$this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');
		$arr_emp = $this -> EmployeeDetails -> find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));

		/*
		 foreach ($arr_emp as $key => $value) {
		 $resp_emp["emp"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0]);
		 }*/

		$resp_emp["rows"] = array();
		foreach ($arr_emp as $key => $value) {

			//debug($value);
			$data['id'] = $value["EmployeeDetails"]['emp_pkey'];
			$data['data'] = array($value[0]['name']);
			$resp_emp["rows"][] = $data;
		}
		echo json_encode($resp_emp);
		$this -> autoRender = FALSE;
	}

	public function listemployeesforhierarchy() {

		$parentEmpKey = (isset($_REQUEST['parentEmpKey']) ? $_REQUEST['parentEmpKey'] : -1);
		$this -> EmployeeStructure -> useDbConfig = $this -> Session -> read('ds');
		$childEmps = $this -> EmployeeStructure -> find("list", array("fields" => "emp_id", "conditions" => array("parent_id" => $parentEmpKey)));

		$db = $this -> EmployeeStructure -> getDataSource();
		$fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
		$joins = array( array('table' => 'emp_proff', 'alias' => 'EmployeeProfessionalDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')));
		$conditions["status"] = 1;
		//array('status' => 1, 'EmployeeDetails.emp_pkey NOT IN' => $emp_in_shift);
		$childEmps[] = $parentEmpKey;
		if (!empty($childEmps)) {
			$conditions["NOT"] = array('EmployeeDetails.emp_pkey' => $childEmps);
		}
		//	$conditions["NOT"] =  array('EmployeeDetails.emp_pkey' =>$parentEmpKey );
		$resp_emp = array();
		$this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');
		$arr_emp = $this -> EmployeeDetails -> find("all", array('fields' => $fields, 'joins' => $joins, 'conditions' => $conditions));
		/*

		 foreach ($arr_emp as $key => $value) {
		 $resp_emp["emp"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0]);
		 }*/

		$resp_emp["rows"] = array();
		foreach ($arr_emp as $key => $value) {

			//debug($value);
			$data['id'] = $value["EmployeeDetails"]['emp_pkey'];
			$data['data'] = array($value[0]['name']);
			$resp_emp["rows"][] = $data;
		}
		echo json_encode($resp_emp);
		$this -> autoRender = FALSE;
	}

	public function add() {
		$this -> autoRender = false;
		$ids = $_REQUEST['id'];

		$parent = (isset($_REQUEST['parent']) ? $_REQUEST['parent'] : -1);
		$this -> EmployeeStructure -> useDbConfig = $this -> Session -> read('ds');
		if ($ids && $parent != -1) {
			$arr_ids = explode(",", $ids);
			foreach ($arr_ids as $key => $value) {
				$data['emp_id'] = $value;
				$data['parent_id'] = $parent;

				$this -> EmployeeStructure -> save($data);
			}
		}
	}

	public function remove() {
		$this -> autoRender = false;
		$ids = $_REQUEST['id'];

		$parent = (isset($_REQUEST['parent']) ? $_REQUEST['parent'] : -1);
		$this -> EmployeeStructure -> useDbConfig = $this -> Session -> read('ds');
		if ($ids && $parent != -1) {
			$arr_ids = explode(",", $ids);
			foreach ($arr_ids as $key => $value) {
				$condition['emp_id'] = $value;
				$condition['parent_id'] = $parent;
				$this -> EmployeeStructure -> deleteAll($condition);
			}
		}
	}
	function saveData(){
		$this -> autoRender = false;
		//debug($_REQUEST['data']);
		$saveData = json_decode($_REQUEST['data']);
		
			foreach ($saveData as $key => $value) {
				if(isset($value->children)){
					$this->saveHeirarchy($value->children,$value->id);
				}
		}
			
		$data['success'] = true;
		$data['msg'] = 'Employee Hierarchy Successfully Saved!';
		echo json_encode($data);
		//
	}
	function saveHeirarchy($children,$parent)
	{
		foreach ($children as $key => $value) {
			
				$this -> EmployeeDetails -> updateAll(array("parent"=>$parent),array("emp_pkey"=>$value->id));
							if(isset($value->children)){
								$this->saveHeirarchy($value->children,$value->id);
							}
			
		}
	}
	function display_tree($nodes, $indent = 0) {
		if ($indent >= 20)
			return;
		// Stop at 20 sub levels

		foreach ($nodes as $node) {
			print str_repeat('&nbsp;', $indent * 4);
			print $node['name'];
			print '<br/>';
			if (isset($node['children']))
				display_tree($node['children'], $indent + 1);
		}
	}

}
