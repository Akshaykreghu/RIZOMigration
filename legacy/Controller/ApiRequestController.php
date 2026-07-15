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
class ApiRequestController extends AppController {

	public $datatable = array();
	/**
	 * Controller name
	 *
	 * @var string
	 */
	public $name = 'ApiRequest';

	/**
	 * This controller does not use a model
	 *
	 * @var array
	 */
	public $uses = array('UserCredentials','EmployeeDetails','EmployeeProfessionalDetails', 'CompanyContactInfo', 'Menu', 'EmployeeMenu', 'Departments', 'Units');
	public $components = array('DatatablesManagement');

	public function getMenus() {
		$this -> layout = null;
		$resp_menu  =  array();
		$sessionObj = $this -> Session -> read("Auth.User");
		$this -> set('user_group', $sessionObj['user_group']);

		$context = isset($_REQUEST["context"]) ? $_REQUEST["context"] : "main";
		$root = isset($_REQUEST["root"]) ? $_REQUEST["root"] : 0;

		$conditions = array();
		if ($context == "sub") {
			$conditions['parent_id'] = $root;
		}
		if ($sessionObj['user_group'] == 2) {
			/*
			 * Employee Menu
			 * Added by santhosh on 14 March 2015
			 */
			$this -> EmployeeMenu -> useDbConfig = $this -> Session -> read('ds');

			$menudb = $this -> EmployeeMenu -> find("all", array("conditions" => $conditions));
			// $menu = array("success" => true);
			if ($context != "sub") {
				$arr_menu = array();
				$arr_menu["id"] = "dashboard";
				// $m["menu_url"];
				$arr_menu["url"] = "dashboard";
				$arr_menu["text"] = "Dashboard";

				$arr_menu["leaf"] = true;
				$menu[-1] = $arr_menu;
			}
			foreach ($menudb as $key => $value) {
				$m = $value['EmployeeMenu'];
				$arr_menu = array();
				$arr_menu["id"] = $m["menu_id"];
				// $m["menu_url"];
				$arr_menu["url"] = $m["menu_url"];
				$arr_menu["text"] = $m["menu_title"];

				$arr_menu["leaf"] = true;
				if (isset($menu[$m['parent_id']])) {
					$menu[$m['parent_id']]["leaf"] = false;
					$menu[$m['parent_id']]['children'][] = $arr_menu;
				} else {
					$menu[$m['menu_id']] = $arr_menu;
				}
			}
		} else {
			$this -> Menu -> useDbConfig = $this -> Session -> read('ds');
			//$this->Menu->recover('tree');
			$menudb = $this -> Menu -> find("all", array("conditions" => $conditions));

			$menu = array();

			foreach ($menudb as $key => $value) {
				$m = $value['Menu'];
				$arr_menu = array();
				$arr_menu["id"] = ($context == "main") ? $m["menu_id"] : (($m["menu_url"]) ? $m["menu_url"] : "none_" . $m["menu_id"]);
				$arr_menu["url"] = $m["menu_url"];
				$arr_menu["text"] = $m["menu_title"];
				$arr_menu["iconCls"] = "fa fa-user";
				$arr_menu["leaf"] = true;
				// "xf007@FontAwesome";

				if (isset($menu[$m['parent_id']])) {
					//$menu[$m['parent_id']]["leaf"] = false;
					$menu[$m['parent_id']]['children'][] = $arr_menu;

				} else {
					$arr_menu["leaf"] = true;
					$menu[$m['menu_id']] = $arr_menu;
				}
			}

		}

		//   $resp_menu["success"] = true;
		$i = 0;
		if ($context == "main") {
			foreach ($menu as $key => $value) {
				$resp_menu[$i++] = $value;
			}
		} else {
			foreach ($menu as $key => $value) {
				$resp_menu[$i++] = $value;
			}

		}
		//	debug($resp_menu);
		if ($context == "main") {
			echo json_encode($resp_menu);
			//$conditions['parent_id'] = $root;
		} else if ($root) {
			echo json_encode($resp_menu);
		}

		$this -> autoRender = FALSE;

	}
public function listemployeesforhierarchy(){
            
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
        $resp_emp = array( );
		
			$data["id"] 	  = "";
			$data["text"]     = " ";
			$resp_emp[] = $data;
		foreach ($arr_emp as $key => $value) {

			$data["id"] 	  =  $value["EmployeeDetails"]['emp_id'];
			$data["text"]     =  $value[0]['name'].' --'.$value["EmployeeProfessionalDetails"]['emp_company_id'];
			$resp_emp[] = $data;
		}
        return json_encode($resp_emp);
        $this->autoRender=FALSE;
    }
public function listemployees(){
            
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
         //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
       $user = $this->Session->read('company_code');
        if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions = array("EmployeeDetails.branch_code" => $cur_emp_branch,"status" => 1);
        }else{
            $conditions = array('status' => 1);
        }
        //employee branch wise sorting ends here
        $resp_emp = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_emp =  $this -> EmployeeDetails ->find("all",array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'order'=> array('first_name ASC')
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
        //echo json_encode($resp_emp);
        //$this->autoRender=FALSE;
        return json_encode($resp_emp);
    }
	public function listdepartmentsforcombo() {

		$this -> Departments -> useDbConfig = $this -> Session -> read('ds');
		//    echo json_encode($this->DataTable->getData('Departments',$columns));
		$resp_banks = array();
		$this -> Departments -> useDbConfig = $this -> Session -> read('ds');
		$arr_banks = $this -> Departments -> find("all", array("conditions" => array("status" => 1)));
		foreach ($arr_banks as $key => $value) {
			$resp_banks["depts"][$key]['key'] = $value["Departments"]['id'];
			$resp_banks["depts"][$key]['label'] = $value["Departments"]['dept_name'];
		}
		echo json_encode($resp_banks);
		$this -> autoRender = FALSE;

	}

	public function listbranchesforcombo() {

		$this -> Units -> useDbConfig = $this -> Session -> read('ds');
		//    echo json_encode($this->DataTable->getData('Departments',$columns));
		$resp_branches = array();

		$arr_branches = $this -> Units -> find("all", array("conditions" => array("status" => 1)));
		foreach ($arr_branches as $key => $value) {
			$resp_branches["branches"][$key]['key'] = $value["Units"]['id'];
			$resp_branches["branches"][$key]['label'] = $value["Units"]['branch_name'];
		}
		echo json_encode($resp_branches);
		$this -> autoRender = FALSE;

	}

	public function listdepartments1forcombo() {

		$this -> Departments -> useDbConfig = $this -> Session -> read('ds');
		//    echo json_encode($this->DataTable->getData('Departments',$columns));
		$resp_banks = array();
		$this -> Departments -> useDbConfig = $this -> Session -> read('ds');
		$arr_banks = $this -> Departments -> find("all", array("conditions" => array("status" => 1)));
		foreach ($arr_banks as $key => $value) {
			$resp_banks["depts"][$key]['key'] = $value["Departments"]['id'];
			$resp_banks["depts"][$key]['label'] = $value["Departments"]['dept_name'];
		}
		echo json_encode($resp_banks);
		$this -> autoRender = FALSE;

	}

	public function listdepartments1sforcombo() {

		$this -> Departments -> useDbConfig = $this -> Session -> read('ds');
		//    echo json_encode($this->DataTable->getData('Departments',$columns));
		$resp_banks = array();
		$this -> Departments -> useDbConfig = $this -> Session -> read('ds');
		$arr_banks = $this -> Departments -> find("all", array("conditions" => array("status" => 1)));
		foreach ($arr_banks as $key => $value) {
			$resp_banks["depts"][$key]['key'] = $value["Departments"]['id'];
			$resp_banks["depts"][$key]['label'] = $value["Departments"]['dept_name'];
		}
		echo json_encode($resp_banks);
		$this -> autoRender = FALSE;

	}

}
