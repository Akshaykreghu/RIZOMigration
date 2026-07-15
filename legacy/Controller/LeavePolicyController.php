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
class LeavePolicyController extends AppController {

	public $datatable = array();
	/**
	 * Controller name
	 *
	 * @var string
	 */
	public $name = 'LeavePolicy';

	/**
	 * This controller does not use a model
	 *
	 * @var array
	 */
	public $uses = array('UserCredentials', 'EmployeeProfessionalDetails', 'EmployeeDetails','LeavePolicy', 'LeaveEntries', 'LeavePolicyGroup', 'SalaryHeadItems');
	public $components = array('DatatablesManagement');

	/*
	 * Dashboard landing view
	 */
	public function index() {
		$this -> LeavePolicy -> useDbConfig = $this -> Session -> read('ds');
		 $plan=$this->LeavePolicy->query('SELECT plan FROM comp_contact_info');
        $plan=isset($plan['0']['comp_contact_info']['plan'])?$plan['0']['comp_contact_info']['plan']:'';
		 $this->set('plan',$plan);
		//debug(	$this->CompanyContactInfo->find("all"));

	}

	public function groupform() {

		$data['LEAVEPOLICY_GROUP_ID'] = 0;
		$data['LEAVEPOLICY_GROUP_NAME'] = "";

		$this -> LeavePolicyGroup -> useDbConfig = $this -> Session -> read('ds');
		$policies_conditions = array();
		$policies_conditions['LEAVEPOLICY_GROUP_ID'] = isset($_REQUEST["id"]) ? $_REQUEST["id"] : 0;
		$policy = $this -> LeavePolicyGroup -> find("first", array("conditions" => $policies_conditions));
		if (isset($policy['LeavePolicyGroup']))
			$data = $policy['LeavePolicyGroup'];

		//if()

		/*

		 $this->Holiday->useDbConfig = $this->Session->read('ds');

		 $holidaygroup = $this->HolidayGroup->find("list",array(   'fields' => array('HolidayGroup.HOLIDAY_GROUP_ID','HolidayGroup.HOLIDAY_GROUP_NAME')));
		 //debug($holidaygroup);
		 if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
		 $data_db = $this->Holiday->find("first",array("conditions"=>array("HOLIDAYID"=>$_REQUEST['id'])));
		 //	debug($data);
		 $data = $data_db['Holiday'];
		 }
		 */

		//$this->set("holidaygroup",$holidaygroup);
		$this -> set("data", $data);
		//$resp = array('success' => TRUE,"data" =>$data);
		//echo json_encode($resp);
		$this -> layout = null;
		//$this->autoRender = false;

	}
 public function jsons($branch = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and ( first_name like '%$q%' or emp_proff.emp_company_id like '%$q%' ) ";
        } else {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_pkey,first_name,concat(first_name,' ',last_name,' ',emp_proff.emp_company_id) as full_name from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_details.status=1 $q_condition ORDER BY first_name ASC ");
        $array = array();
        $branch = array();
        $branch[] = array("id" => "all", "text" => "ALL");
//        foreach ($branch_array as $key => $value) {
//            $branch[] = array(
//                'id' => $value['emp_details']['emp_pkey'],
//                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
//            );
//        }
//        $array['items'] = $branch;
//        echo json_encode($array);
        $arr_filterresult = array();
        foreach ($branch_array as $val) {
            $arr_filterresult[] = isset($val['emp_details']) ? array_merge($val['emp_details'], $val[0]) : array();
        }
        echo json_encode($arr_filterresult);
    }
	public function form() {
		//edited by athira on 04-02-2024
		$this->Menu->useDbConfig = $this->Session->read('ds');
		$plan=$this->Menu->query('SELECT plan FROM comp_contact_info');
		$plan=isset($plan['0']['comp_contact_info']['plan'])?$plan['0']['comp_contact_info']['plan']:'';
		$this->set('plan',$plan);
		//end
                $company_code = strtoupper($this->Session->read('company_code'));
                $this -> set("company_code", $company_code);  
		$data['LEAVEPOLICYID'] = 0;
                $this -> LeavePolicyGroup -> useDbConfig = $this -> Session -> read('ds');
                
                $excluded_leavepolicies = $this -> LeavePolicyGroup ->query("SELECT LEAVEPOLICYID FROM `leavepolicy` where LEAVEPOLICY_GROUP_ID = '".$_REQUEST["LEAVEPOLICY_GROUP_ID"]."' "); 
												  
                $data['item'] = "";
		$data['code'] = "";
		$data['alloted_leave_forthe_year'] = "";
		$data['alloted_leave_forthe_month'] = "";
		$data['CARRY_FORWARD_LIMIT'] = "";
		//edited by athira on 21-09-2025
		$restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
			 $data['leave_policy_type'] = "";
         $data['leave_cycle_start_date'] = "";
		$data['leave_cycle_end_date'] = "";
		}
		//end
		$data['IS_SANDWICH'] = "";
                $data['is_leave_encash'] = "";
		$data["LEAVEPOLICY_GROUP_ID"] = (isset($_REQUEST["LEAVEPOLICY_GROUP_ID"]) ? $_REQUEST["LEAVEPOLICY_GROUP_ID"] : 0);
		$data['ALLOW_NEGETIVE'] = "";
		$data['REMARKS'] = "";

		$this -> SalaryHeadItems -> useDbConfig = $this -> Session -> read('ds');
		$this -> LeavePolicy -> useDbConfig = $this -> Session -> read('ds');
		
		$policies_conditions = array();
		
	 
                $conditions_form = "";
                $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeavePolicy.notified_by = EmployeeDetails.emp_pkey')
            ),
          array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionslDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionslDetails.emp_fkey = EmployeeDetails.emp_pkey')
            ));
                 $joins1 = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeavePolicy.sanction_by = EmployeeDetails.emp_pkey')
            ),
          array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionslDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionslDetails.emp_fkey = EmployeeDetails.emp_pkey')
            ));
                $components = $this->LeavePolicy->query("SELECT * FROM `salary_head_items` left join salary_heads on (salary_heads.head_pkey = salary_head_items.head_fkey) WHERE head_occurance = 'VARIABLE' and lcase(value) = 'y'");
                    $this->set("components",$components);
		if (isset($_REQUEST["LEAVEPOLICYID"]) && $_REQUEST["LEAVEPOLICYID"] > 0) {
			$policies_conditions['LeavePolicy.LEAVEPOLICYID'] = $_REQUEST["LEAVEPOLICYID"];
			$policy = $this -> LeavePolicy -> find("first", array("fields" => "LeavePolicy.*,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeProfessionslDetails.emp_company_id", 
                            'joins' => $joins,
                            'conditions' => $policies_conditions));
                        $policy_id = $_REQUEST["LEAVEPOLICYID"];
                        $conditions_form = " and LEAVEPOLICYID not in ('$policy_id')";
                        $policy1 = $this -> LeavePolicy -> find("first", array("fields" => "concat(EmployeeDetails.first_name,EmployeeDetails.last_name,' - ',EmployeeProfessionslDetails.emp_company_id) as SanctionName", 
                            'joins' => $joins1,
                            'conditions' => $policies_conditions));
                        $policy_id = $_REQUEST["LEAVEPOLICYID"];
                        $conditions_form = " and LEAVEPOLICYID not in ('$policy_id')";
		}
  

/* $log = $this->LeavePolicy->getDataSource()->getLog(false, false);
debug($log);
die;*/
                if (isset($policy["LeavePolicy"])) {
                 $data =   array_merge($policy["LeavePolicy"],$policy["EmployeeDetails"],$policy["EmployeeProfessionslDetails"],$policy1[0]);

		}                  

                $leavepolicygroupid = $_REQUEST["LEAVEPOLICY_GROUP_ID"];
		$this -> SalaryHeadItems -> useDbConfig = $this -> Session -> read('ds');

		$policygroup = $this -> SalaryHeadItems -> find("all", array('conditions' =>  array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave'
                and value='Y' and status=1) and salary_head_item_pkey not in (select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID = '$leavepolicygroupid' $conditions_form and status = 1)")));
		$arr_lpgrp = array();
		foreach ($policygroup as $key => $value) {
			$arr_lpgrp[$key]["id"] = $value["SalaryHeadItems"]["salary_head_item_pkey"];
			$arr_lpgrp[$key]["label"] = $value["SalaryHeadItems"]["item"];
		}

	//edited by athira on 20-09-2025
	$restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
	$currentMonth = date("Y-m-01");

    $att_start_date = $this->LeavePolicy->query(
        "SELECT att_start_end_fn('$currentMonth', 1) AS start_date"
    );

	$att_end_date = $this->LeavePolicy->query(
        "SELECT att_start_end_fn('$currentMonth', 2) AS start_date"
    );

	$att_start_date=$att_start_date[0][0]['start_date'];
	$att_end_date=$att_end_date[0][0]['start_date'];
	$this->set('att_start_date',$att_start_date);
	$this->set('att_end_date',$att_end_date);

	$leave_types = $this->SalaryHeadItems->query("SELECT salary_head_item_pkey, occurance FROM salary_head_items");

	$occurrence_map = [];
	foreach ($leave_types as $lt) {
		$occurrence_map[$lt['salary_head_items']['salary_head_item_pkey']] = $lt['salary_head_items']['occurance'];
	}

	$this->set('occurrence_map', $occurrence_map);
}
	//end

		$applicableTo = array();

		$applicableTo[0]['id'] = "A";
		$applicableTo[0]['label'] = "All";

		$applicableTo[1]['id'] = "M";
		$applicableTo[1]['label'] = "Male";

		$applicableTo[2]['id'] = "F";
		$applicableTo[2]['label'] = "Female";
		$this -> set("applicableTo", $applicableTo);
		$this -> set("policygroup", $arr_lpgrp);
		$this -> set("data", $data);

		//edited by athira on 21-09-2025
		$restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
		if($data['leave_policy_type']=='Y'){
			$year_start= $data['leave_cycle_start_date'];
			$year_end=$data['leave_cycle_end_date'];
			$this->set('year_start',$year_start);
			$this->set('year_end',$year_end);
		}

		if($data['leave_policy_type']=='P'){
			$present_start= $data['leave_cycle_start_date'];
			$present_end=$data['leave_cycle_end_date'];
			$this->set('present_start',$present_start);
			$this->set('present_end',$present_end);
		}
		}
		$this -> layout = null;

		$restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
			$this->render('policyform'); // create app/View/LeavePolicy/form_glet.ctp
		 } else {
		 	$this->render('form'); // existing form.ctp
		 }
	//end

	}

	public function savepolicy() {

		$this -> autoRender = FALSE;
		$this -> layout = null;

		$this -> LeavePolicy -> useDbConfig = $this -> Session -> read('ds');
		
		$arr_form_data = $this -> request -> data;

		//edited by athira on 20-09-2025
		$company_code=strtoupper($this->Session->read('company_code'));
      $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
		$salary_head_item_pkey=isset($arr_form_data['salary_head_item_fkey']) ? $arr_form_data['salary_head_item_fkey'] : '';
		
		$occurance=$this->LeavePolicy->query("SELECT occurance FROM salary_head_items WHERE salary_head_item_pkey='$salary_head_item_pkey'");
		$occurance=isset($occurance[0]['salary_head_items']['occurance']) ? $occurance[0]['salary_head_items']['occurance'] : '';

			if ($occurance=='COFF') {
    		$arr_form_data['ALLOW_NEGETIVE'] = 'Y';
			}
		 }

		//end
		
		$leavepolicyid = isset($arr_form_data['LEAVEPOLICYID']) ? $arr_form_data['LEAVEPOLICYID'] : '' ;
		if (!isset($arr_form_data['document_mandatory']))
			$arr_form_data['document_mandatory'] = "N";
		if (!isset($arr_form_data['is_leave_encash']))
			$arr_form_data['is_leave_encash'] = "N";
		if (!isset($arr_form_data['ALLOW_NEGETIVE']))
			$arr_form_data['ALLOW_NEGETIVE'] = "N";
		if (!isset($arr_form_data['IS_SANDWICH']))
			$arr_form_data['IS_SANDWICH'] = "N";
                if (!isset($arr_form_data['exceptions']))
			$arr_form_data['exceptions'] = "N";

		// edited by athira on 01-08-2025
		$restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
		$arr_form_data['allow_all_leaves'] = isset($arr_form_data['allow_all_leaves']) && $arr_form_data['allow_all_leaves'] === 'on' ? 'Y' : 'N';
		
		if(isset($arr_form_data['notified_by'])){
			$arr_existing1 = array_filter(array_unique(array_merge($arr_existing, $arr_form_data['notified_by']))) ;
		}else{
			$arr_existing1 = array();
		}
		
		if(isset($arr_form_data['existing'])){
			$arr_existing4 = array_filter(array_unique(array_merge($arr_existing1, $arr_form_data['existing']))) ;
			$arr_form_data['notified_by'] = isset($arr_existing4)?implode(",",$arr_existing4):'';
		}else{
			  $arr_form_data['notified_by'] = isset($arr_form_data['notified_by'])?implode(",",$arr_form_data['notified_by']):'';
		}
		}
		//end
		$arr_form_data['status'] = 1;
		$data = array();
		$result = $this -> LeavePolicy -> save($arr_form_data);
		
/*
$log = $this->LeavePolicy->getDataSource()->getLog(false, false);
debug($log);
die;*/

		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Leave Policy Updated";

		echo json_encode($resp);
		/*
		 if(!empty($result)){
		 return 'success';
		 }*/

	}

	public function saveleavepolicygroup() {

		$this -> autoRender = FALSE;
		$this -> layout = null;

		$this -> LeavePolicyGroup -> useDbConfig = $this -> Session -> read('ds');

		$arr_form_data = $this -> request -> data;
		$arr_form_data['status'] = 1;
		//	debug($arr_form_data);
		$data = array();

		$result = $this -> LeavePolicyGroup -> save($arr_form_data);
		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Leave Policy Group Updated";

		echo json_encode($resp);

	}

	public function listpolicies() {

		$this -> SalaryHeadItems -> useDbConfig = $this -> Session -> read('ds');
		$this -> LeavePolicy -> useDbConfig = $this -> Session -> read('ds');
		$arr_policies = array();
		//edited by athira on 20-09-2025
		$company_code=strtoupper($this->Session->read('company_code'));
		//end
		$arr_policies["data"] = array();
		$count = 0;
		if (isset($_REQUEST["group"])) {
			$group = $_REQUEST["group"];
			$conditions['LEAVEPOLICY_GROUP_ID'] = $group;
			$conditions['LeavePolicy.status'] = 1;
			$limit = $_REQUEST['rows'];
			$page = $_REQUEST['page'];
		
                        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'item';
                        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
			
                        $ofst = ($page-1)*$limit;
			$count = $this -> LeavePolicy -> find("count", array("joins" => array( array("table" => "salary_head_items", "alias" => "SalaryHeadItems", "type" => "INNER", "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeavePolicy.salary_head_item_fkey"))), "conditions" => $conditions));

			$policies = $this -> LeavePolicy -> find("all", array("fields" => "SalaryHeadItems.*,LeavePolicy.*", "joins" => array( array("table" => "salary_head_items", "alias" => "SalaryHeadItems", "type" => "INNER", "conditions" => array("SalaryHeadItems.salary_head_item_pkey = LeavePolicy.salary_head_item_fkey"))), "conditions" => $conditions,'order'=>array($sort=>$order),'limit'=>$limit,'offset'=>$ofst));
			$arr_policies["rows"] = array();
			foreach ($policies as $key => $value) {
				$arr_policies["rows"][$key]["item"] = $value["SalaryHeadItems"]["item"];

				$arr_policies["rows"][$key]["salary_head_item_pkey"] = $value["SalaryHeadItems"]["salary_head_item_pkey"];
				if (isset($value["LeavePolicy"])) {
					$arr_policies["rows"][$key]["alloted_leave_forthe_year"] = $value["LeavePolicy"]["alloted_leave_forthe_year"];

					$arr_policies["rows"][$key]["LEAVEPOLICYID"] = $value["LeavePolicy"]["LEAVEPOLICYID"];

					$arr_policies["rows"][$key]["alloted_leave_forthe_month"] = $value["LeavePolicy"]["alloted_leave_forthe_month"];
                      
					//edited by athira on 20-09-2025
					$restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
                       if($value["LeavePolicy"]["leave_policy_type"] == 'P'){
                                            $arr_policies["rows"][$key]["alloted_leave_forthe_year"] = '';
                                        }else{
                                            $arr_policies["rows"][$key]["alloted_leave_forthe_year"] = $value["LeavePolicy"]["alloted_leave_forthe_year"];
                                        }
				 }else{
					 if($value["LeavePolicy"]["leave_policy_type"] == 'Y'){
                                            $arr_policies["rows"][$key]["alloted_leave_forthe_year"] = $value["LeavePolicy"]["alloted_leave_forthe_year"];
                                        }else{
                                            $arr_policies["rows"][$key]["alloted_leave_forthe_year"] = $value["LeavePolicy"]["alloted_leave_forthe_month"];
                                        }
				 }
					//end
					//$arr_policies["rows"][$key]["cflimit"] = $value["LeavePolicy"]["CARRY_FORWARD_LIMIT"];
					$arr_policies["rows"][$key]["CARRY_FORWARD_LIMIT"] = $value["LeavePolicy"]["CARRY_FORWARD_LIMIT"];

					$arr_policies["rows"][$key]["applicableto"] = $value["LeavePolicy"]["APPLICABLE_TO"];

					$arr_policies["rows"][$key]["allownegetive"] = $value["LeavePolicy"]["ALLOW_NEGETIVE"];
					$arr_policies["rows"][$key]["remarks"] = $value["LeavePolicy"]["REMARKS"];

				} else {

					$arr_policies["rows"][$key]["alloted_leave_forthe_year"] = "";

					$arr_policies["rows"][$key]["alloted_leave_forthe_month"] = "";

					//$arr_policies["rows"][$key]["cflimit"] = "";
					$arr_policies["rows"][$key]["CARRY_FORWARD_LIMIT"] = "";

					$arr_policies["rows"][$key]["applicableto"] = "";

					$arr_policies["rows"][$key]["allownegetive"] = "";
					$arr_policies["rows"][$key]["remarks"] = "";
				}
			}

		}

	//	$arr_policies["draw"] = $_REQUEST['draw'];
		$arr_policies["total"] = $count;
	//	$arr_policies["recordsFiltered"] = $count;
		echo json_encode($arr_policies);

		$this -> autoRender = FALSE;

	}

	public function listpolicygroupforconfig() {

		$this -> LeavePolicyGroup -> useDbConfig = $this -> Session -> read('ds');
		$count = $this -> LeavePolicyGroup -> find("count", array("conditions" => array('status' => 1)));
		$policygroup = $this -> LeavePolicyGroup -> find("all", array("conditions" => array('status' => 1)));
		$arr_lpgrp = array();

	
			$arr_lpgrp["rows"] = array( );
		foreach ($policygroup as $key => $value) {
			
			
				$data['id']   = $value["LeavePolicyGroup"]['LEAVEPOLICY_GROUP_ID'];
			$data['data'] =  array($value["LeavePolicyGroup"]["LEAVEPOLICY_GROUP_NAME"]);
			$arr_lpgrp["rows"][] = $data;
		}
		
		echo json_encode($arr_lpgrp);
		$this -> autoRender = FALSE;

	}
	public function listpolicygroup() {

		$this -> LeavePolicyGroup -> useDbConfig = $this -> Session -> read('ds');
			//debug($policygroup);
		$limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];
		
                $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'LEAVEPOLICY_GROUP_NAME';
                $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
        
		$ofst = ($page-1)*$limit;
		$count = $this -> LeavePolicyGroup -> find("count", array("conditions" => array('status' => 1)));
		$policygroup = $this -> LeavePolicyGroup -> find("all", array(
                        "conditions" => array(
                            'status' => 1
                        ),
                        'order'=>array($sort=>$order),
                        'limit'=>$limit,
                        'offset'=>$ofst
                    )
                );
/*
	$log = $this->LeavePolicyGroup->getDataSource()->getLog(false, false);
debug($log);*/

		$arr_lpgrp = array();
		$arr_lpgrp["rows"]= array();
		foreach ($policygroup as $key => $value) {
			$arr_lpgrp["rows"][$key]["LEAVEPOLICY_GROUP_ID"] = $value["LeavePolicyGroup"]["LEAVEPOLICY_GROUP_ID"];
			$arr_lpgrp["rows"][$key]["LEAVEPOLICY_GROUP_NAME"] = $value["LeavePolicyGroup"]["LEAVEPOLICY_GROUP_NAME"];
		//	$arr_lpgrp["data"][$key]['DT_RowId'] = $value["LeavePolicyGroup"]['LEAVEPOLICY_GROUP_ID'];
		}

		//$arr_lpgrp["draw"] = $_REQUEST['draw'];
		$arr_lpgrp["total"] = $count;
		//$arr_lpgrp["recordsFiltered"] = $count;
		echo json_encode($arr_lpgrp);
		$this -> autoRender = FALSE;

	}
	
			
	public function listleavetype() {

		$this -> SalaryHeadItems -> useDbConfig = $this -> Session -> read('ds');

		$policygroup = $this -> SalaryHeadItems -> find("all", array('conditions' => array('SalaryHeadItems.head_fkey' => 6)));
		$arr_lpgrp = array();
		foreach ($policygroup as $key => $value) {
			$arr_lpgrp[$key]["id"] = $value["SalaryHeadItems"]["salary_head_item_pkey"];
			$arr_lpgrp[$key]["label"] = $value["SalaryHeadItems"]["item"];
		}

		echo json_encode(array("leavetypes" => $arr_lpgrp));

		$this -> autoRender = FALSE;

	}

	public function delete() {
		$this -> autoRender = FALSE;
		$this -> LeavePolicy -> useDbConfig = $this -> Session -> read('ds');
		$result = array('success' => 0);
		if (isset($_REQUEST["ids"])) {
			$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
			/*
			foreach ($ar_ids as $key => $value) {
			
						}*/
			
			$this -> LeavePolicy -> updateAll(array('LeavePolicy.status' => 0), array('LeavePolicy.LEAVEPOLICYID' => $ar_ids));
			$result['success'] = 1;
		}
		//holiday_group

		echo json_encode($result);
	}

	public function deletegroup() {
		$this -> autoRender = FALSE;
		$this -> LeavePolicyGroup -> useDbConfig = $this -> Session -> read('ds');
                $this -> EmployeeProfessionalDetails -> useDbConfig = $this -> Session -> read('ds');
		$result = array('success' => 0);
		if (isset($_REQUEST["ids"])) {
			$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
			foreach ($ar_ids as $key => $value) {

			}
                        
                        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
                        
                        $asiigned = $this->EmployeeProfessionalDetails->find("count",array("joins"=>$joins,"conditions"=>array('EmployeeProfessionalDetails.LEAVEPOLICY_GROUP_ID' => $ar_ids,'EmployeeDetails.status'=>1)));
                        if($asiigned > 0)
                        {
			$result['success'] = 0;
                        $result["msg"] = "Leave policy cannot be deleted, remove employees under this Policy";
                        }
                        else
                        {
                 	$this -> LeavePolicyGroup -> updateAll(array('LeavePolicyGroup.status' => 0), array('LeavePolicyGroup.LEAVEPOLICY_GROUP_ID' => $ar_ids));
                        $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.LEAVEPOLICY_GROUP_ID' => NULL), array('EmployeeProfessionalDetails.LEAVEPOLICY_GROUP_ID' => $ar_ids));
			$result['success'] = 1;    
                        $result["msg"] = "Leave policy deleted Successfully";
                        }
		}
		//holiday_group

		echo json_encode($result);
	}
            public function checkleavepolicyexists($LEAVEPOLICY_GROUP_ID = 0) {
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;

        $LEAVEPOLICY_GROUP_NAME = isset($arr_requestdata['LEAVEPOLICY_GROUP_NAME']) ? $arr_requestdata['LEAVEPOLICY_GROUP_NAME'] : '';

        $int_leavcount = 0;
        if ($LEAVEPOLICY_GROUP_NAME != '') {
            $this->LeavePolicyGroup->useDbConfig = $this->Session->read('ds');
            $int_leavcount = $this->LeavePolicyGroup->find("count", array(
                'conditions' => array('LeavePolicyGroup.LEAVEPOLICY_GROUP_NAME' => $LEAVEPOLICY_GROUP_NAME, 'LeavePolicyGroup.status' => 1, 'LeavePolicyGroup.LEAVEPOLICY_GROUP_ID != ' . $LEAVEPOLICY_GROUP_ID)
                    )
            );
        }
        echo $int_leavcount;
    }

}
