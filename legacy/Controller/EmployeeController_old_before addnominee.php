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
class EmployeeController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'Employee';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl','EmployeeConfig', 'Family','passport','NoticePeriod', 'qualifcations', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC');
    public $components = array('MasterdataManagement');
    
    /*
     * Employees landing view
     */

    public function index() {
        $this->autoRender = FALSE;
        $user_group = $this->Session->read("user_group");
        if ($user_group == '1') {
            //Admin view
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
            $missed_prof = $this->checkProff();
            $this->set('active_emp_count', $active_emp_count);
            
            //Fetch Units for the company
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
            $this->set('arr_branches', $arr_branches);

            // $arr_Emp=$this->MasterdataManagement->getEmployeeListForCombo();
            // $this->set('arr_Emp',$arr_Emp);

            $arr_Des = $this->MasterdataManagement->getDesignationsListForCombo();
            // debug($arr_Des);
            $this->set('missed_prof', $missed_prof);
            $this->set('arr_Des', $arr_Des);
            $this->render('index');
        } else if ($user_group == '2') {
            //Employee View
            $emp_fkey = $this->Session->read("emp_fkey");
            $this->setup($emp_fkey);
            $this->render('setup');
        }
    }
    public function employeesunder(){
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            if($this->Session->read('emp_fkey')){
            $emp_pkeys = $this->Session->read('emp_fkey');
            }else
            {
                $emp_pkeys = 0;
            }
            $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey'
                )
            )
            );
            $conditions = array(
                array(
                    'status'=>1,
                    'EmployeeProffessional.attr1'=>$emp_pkeys
                )
            );
            $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
            $this->set('active_emp_count', $active_emp_count);
            
            //Fetch Units for the company
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo($emp_pkeys);
            $this->set('arr_branches', $arr_branches);

            // $arr_Emp=$this->MasterdataManagement->getEmployeeListForCombo();
            // $this->set('arr_Emp',$arr_Emp);

            $arr_Des = $this->MasterdataManagement->getDesignationsListForCombo();
            // debug($arr_Des);
            $this->set('arr_Des', $arr_Des);
            $this->render('index');
    }

    public function checkProff(){
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $missed_prof = $this->EmployeeDetails->query("select first_name from emp_details where status !=0 and emp_pkey not in (select emp_fkey from emp_proff)");
        return $missed_prof;
    }

    public function getstages($site_pkey = 0) {
        $this->autoRender = false;
        //$site_pkey=[];
        //  debug($site_pkey);
        $sitepkey_array = $site_pkey;
        if ($sitepkey_array == '') {
            $conditions = '';
        } else {
            $conditions = 'branch_code="' . $sitepkey_array . '"';
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code', 'conditions' => array('status' => 1, $conditions))));
        //debug($arr_stages);
        $str_stage_options_html = '';
        $this->set('arr_Emp', $arr_Emp);
        // debug($arr_Emp);
        foreach ($arr_Emp as $value) {
            $emp_pkey = isset($value['emp_pkey']) ? $value['emp_pkey'] : '';
            $emp_name = isset($value['emp_name']) ? $value['emp_name'] : '';
            $str_stage_options_html .= '<option value="' . $emp_pkey . '">' . $emp_name . '</option>';
            //  debug($str_stage_options_html);
        }
        echo $str_stage_options_html;
    }

    public function getautohierarchycompletions() {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");    
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array();
        $emp = $arr_request_data['emp'];
            $searchkey = $arr_request_data['username'];
            $filter_condition[] = "first_name like '%".$searchkey."%' OR EmployeeProfessionalDetails.emp_company_id like '%".$searchkey."%'  OR last_name like '%".$searchkey."%' OR emp_id like '%".$searchkey."%' and emp_pkey not in ('$emp') ";
        
        //    debug($filter_condition);
        $joins  = array(
        array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if($user_group == '2'){
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code','joins'=>$joins, 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
        $arr_filterresult = array(
            array(
                'emp_pkey' => '',
                'emp_name' => 'ALL',
            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array();
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }
    
    public function getautocompletions() {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");    
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array();
        if ($arr_request_data['branch'] != '') {

            $searchkey = $arr_request_data['username'];
            $branch = $arr_request_data['branch'];
            $filter_condition[] = 'branch_code = "' . $branch . '" and first_name LIKE "%' . $searchkey . '%"';
        } else {
            $searchkey = $arr_request_data['username'];
//            $filter_condition[] = 'first_name LIKE "%' . $searchkey . '%"';
            $filter_condition[] = "first_name like '%".$searchkey."%' OR EmployeeProfessionalDetails.emp_company_id like '%".$searchkey."%'  OR last_name like '%".$searchkey."%' OR emp_id like '%".$searchkey."%'";
        }
        //    debug($filter_condition);
        $joins  = array(
        array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if($user_group == '2'){
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code','joins'=>$joins, 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
        $arr_filterresult = array(
            array(
                'emp_pkey' => '',
                'emp_name' => 'ALL',
            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array();
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }
    
    
    

    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */

    public function listemployees() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $user_group = $this->Session->read("user_group");
        //  debug($arr_request_data);
        $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $emp = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $des = isset($arr_request_data['designation']) ? $arr_request_data['designation'] : '';
        //  debug($branch);
        //   debug($emp);
        if ($branch == '') {
            $branch = '';
        } else {
            $branch = 'EmployeeDetails.branch_code="' . $branch . '"';
        }
        // debug($branch);
        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp_fkey = $arr_request_data['employee'];
        } else {
            $emp_fkey = 'emp_fkey';
        }




        if ($emp == '') {
            $emp = '';
        } else {
            $emp = 'EmployeeDetails.emp_pkey="' . $emp . '"';
        }
        //   debug($emp);
        if ($des == '') {
            $des = '';
        } else {
            $des = 'EmployeeProfessionalDetails.designation="' . $des . '"';
        }
        
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'name';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $fields = 'EmployeeDetails.status,Branches.branch_name,designation.desig_name,designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'designation',
                'alias' => 'designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1)
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1)
            )
        );
        $conditions = array('EmployeeDetails.status != 0', $branch, $emp, $des);
        if($user_group == '2'){
            $emp_pkey = $this->Session->read('emp_fkey');
            $conditions[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }
		if(isset($arr_request_data['emp'])){
            $conditions[] = "(first_name like '%".$arr_request_data['emp']."%' OR EmployeeProfessionalDetails.emp_company_id like '%".$arr_request_data['emp']."%'  OR last_name like '%".$arr_request_data['emp']."%' OR emp_id like '%".$arr_request_data['emp']."%')";
        }
        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
        $arr_emp = $this->EmployeeDetails->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            "conditions" => $conditions,
            'order' => array($sort => $order),
            'limit' => intval($limit),
            'offset' => intval($ofst)
                )
        );
        // debug($arr_emp);
        foreach ($arr_emp as $key => $value) {
            $resp_emp["rows"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0], $value["designation"], $value["Branches"]);
        }
        //debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

//branch filtter     
    public function jsons($branch = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = "and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        if ($q != null) {
            $q_condition = "and first_name like '%$q%'";
        } else {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 $branch_condition $q_condition");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - '. $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }

    public function jsonss($emp = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q']: NULL;
        
        if($q != null)
        {
            $q_condition = "and first_name like '%$q%'";
        }
        else
        {
            $q_condition = "";
        }
        
        $branch_array = $this->EmployeeDetails->query("select * from emp_details  where status = 1 $q_condition and emp_pkey != $emp ");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id"=>"0","text"=>"SELECT");
        foreach ($branch_array as $key => $value)
        {
            $branch[] = array(
                'id' =>$value['emp_details']['emp_pkey'],
            'text' => $value['emp_details']['first_name']. ' ' .$value['emp_details']['last_name']
                );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    } 
    
    public function setup($emp_pkey = 0) {
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this->Session->read("user_group");
        if ($emp_pkey) {
            //edit mode
            $this->set('emp_pkey', $emp_pkey);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_details = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            if (is_array($arr_emp_details['EmployeeDetails'])) {
                $this->set('arr_emp_details', $arr_emp_details['EmployeeDetails']);
            }


            $this->set('user_group', $user_group);

            //   debug($user_group);
            if (isset($user_group) && $user_group == 2) {
                //Employee
                $head = 'My Profile';
            } else {
                //Admin
                $head = $arr_emp_details['EmployeeDetails']['first_name'] . " " . $arr_emp_details['EmployeeDetails']['middile_name'] . " " . $arr_emp_details['EmployeeDetails']['last_name'] . "'s Profile";
            }
            
            $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_pkey and end_date_effective is null ");
            $this->set('arr_gross', $arr_gross);
            
            $this->set('head', $head);
            $this->loadEmpDetails($emp_pkey);
            //Ends
            //Load employee professional details
            $this->loadEmpProfDetails($emp_pkey);
            //Ends
            //Load employee tax heads
            $this->set('arr_emptaxtransactions', $this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
            //Ends
        } else {
            //add mode
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach ($arr_personalinfokeys as $key) {
                $arr_emp_personal_profile[$key] = '';
            }

            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach ($arr_professionalinfokeys as $key) {
                $arr_emp_professional_profile[$key] = '';
            }
            $this->set('head', 'New Employee');
            $this->set('emp_pkey', 0);
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
            //debug($arr_emp_professional_profile);
            $this->set('arr_taxationinfo', array());
        }

        //Fetch taxation fields for creating form dynamically
        $this->set('arr_taxheadfields', $this->requestAction("/Taxation/getTaxHeadFields"));

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_designations = $this->MasterdataManagement->getDesignationsListForCombo();
        $this->set('arr_designations', $arr_designations);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);
        
        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);

        //Fetch Units for the company
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        //Fetch Units for the company
//        $arr_hierarchy = $this->EmployeeDetails->query("select emp_pkey,concat(first_name,' ',last_name) as fullname from emp_details where status = 1");
//        $this->set('arr_hierarchy', $arr_hierarchy);

        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);
        
        
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);
        


        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $this->set('qualifications', $qualifications);

        $this->history->useDbConfig = $this->Session->read('ds');
        $history = $this->history->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $this->set('history', $history);
        $this->Family->useDbConfig = $this->Session->read('ds');
        $resp_emp_family = array();
        $this->Family->useDbConfig = $this->Session->read('ds');
        $count = $this->Family->find("count");
        $family = $this->Family->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $this->set('Family',$family);
        
        $this->passport->useDbConfig = $this->Session->read('ds');
        $count = $this->passport->find("count");
        $passport = $this->passport->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $this->set('passport',$passport);
        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days',$notice_days);
    }

    /*
     * Show tax Head Details form
     */

    public function showtaxheaddetail($emp_pkey = 0, $tax_heads_fkey = 0) {
        $this->set('emp_pkey', $emp_pkey);
        $this->set('tax_heads_fkey', $tax_heads_fkey);

        $this->TaxHead->useDbConfig = $this->Session->read('ds');
        $tax_head = Set::extract('/TaxHead/.', $this->TaxHead->find("first", array('conditions' => array('tax_heads_pkey' => $tax_heads_fkey))));
        $tax_head_name = isset($tax_head[0]['tax_name']) ? $tax_head[0]['tax_name'] : 'Details';
        $this->set('tax_head_name', $tax_head_name);

        $arr_taxheaddetails = $this->requestAction("/Taxation/getTaxHeadDetails/$tax_heads_fkey");
        $arr_emptaxtransactions = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$emp_pkey/$tax_heads_fkey");
        $this->set('arr_taxheaddetails', $arr_taxheaddetails);
        $this->set('arr_emptaxtransactions', $arr_emptaxtransactions);
    }

    //Ends
    public function loadEmpDetails($emp_pkey = 0) {
        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_personal_profile = $this->EmployeeDetails->find('first', array('conditions' => array('emp_pkey' => $emp_pkey)));
            $arr_emp_personal_profile = $arr_emp_personal_profile['EmployeeDetails'];
            $this->set('arr_personalinfo', $arr_emp_personal_profile);
        } else {
            $this->set('arr_professionalinfo', array());
        }
    }

    public function loadEmpProfDetails($emp_pkey = '') {
        $user_group = $this->Session->read("user_group");
        if ($user_group == 2) {
            $emp_pkey = $emp_pkey; //$sessionObj['emp_fkey'];  
        }

        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
            if (empty($arr_emp_professional_profile)) {
                $empId = '';
                $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                $arr_emp_professional_profile_last = $this->EmployeeProfessionalDetails->find('first', array('order' => array('emp_proff_pkey' => 'DESC')));
                if (isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])) {
                    $str_emp_id = $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('control_pkey' => $company_key)));
                    $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                    $arr_emp_id = explode($str_company_code, $str_emp_id);
                    if (isset($arr_emp_id[1]) && $arr_emp_id[1] != '') {
                        $emp_id = $str_company_code . ($arr_emp_id[1] + 1);
                    } else {
                        $emp_id = '';
                    }
                } else {
                    //He is the first employee                  
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key = $sessionObj['company_key'];
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('control_pkey' => $company_key)));
                    $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                    $emp_id = $str_company_code . "1000";
                }
                $arr_emp_professional_profile['emp_id'] = $emp_id;
                $arr_emp_professional_profile['emp_fkey'] = $emp_pkey;
            } else {
                $arr_emp_professional_profile = $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            }

            $this->set('arr_professionalinfo', $arr_emp_professional_profile);
        } else {
            $this->set('arr_professionalinfo', array());
        }
    }

    public function Finyear() {
        $this->autoRender = false;
    }

    public function empprofdetails($emp_pkey = 0) {
        $this->layout = null;
        $emp_pkey = 1;

        //Fetch departments for the company
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments', $arr_departments);

        //Fetch Grades for the company
        $arr_grades = $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades', $arr_grades);

        //Fetch Verticals for the company
        $arr_verticals = $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals', $arr_verticals);

        //Fetch Units for the company
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches', $arr_branches);

        //Fetch employee professional details if in edit mode
        if (isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != '') {
            $arr_emp_professional_profile = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile = $this->EmployeeProfessionalDetails->find('first', array('conditions' => array('emp_fkey' => $emp_pkey)));
            $arr_emp_professional_profile = $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            $this->set('arr_emp_professional_profile', $arr_emp_professional_profile);
            $this->set('emp_id', $arr_emp_professional_profile['emp_id']);
            $emp_proff_pkey = $arr_emp_professional_profile['emp_proff_pkey'];
        } else {
            $emp_proff_pkey = 0;
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile_last = $this->EmployeeProfessionalDetails->find('last');
            if (isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])) {
                $str_emp_id = $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                $company_key = $this->session->read('company_key');
                $arr_central_control = $this->CentralControl->find('first', array('fields' => array('company_code'), 'conditions' => array('company_pkey' => $company_key)));
                $str_company_code = isset($arr_central_control['CentralControl']['company_code']) ? $arr_central_control['CentralControl']['company_code'] : '';
                $arr_emp_id = explode($str_company_code, $str_emp_id);
                if (isset($arr_emp_id[1]) && $arr_emp_id[1] != '') {
                    $emp_id = $str_company_code . ($arr_emp_id[1] + 1);
                } else {
                    $emp_id = '';
                }
            } else {
                $emp_id = '';
            }
            $this->set('emp_id', $arr_emp_professional_profile_last['emp_id']);
        }
        $this->set('emp_proff_pkey', $emp_proff_pkey);
    }

    public function emptaxationdetails($emp_pkey = 0) {
        
    }
    
    public function saveemployeesetupnew() {
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $model = $arr_form_data['model'];
        $pkey = 0;
        $message = '';
        
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        
        $pkey = $arr_form_data['emp_pkey'];
        $pkey = isset($arr_form_data['emp_fkey'])?$arr_form_data['emp_fkey']:0;
        $message = 'Professional Details Saved Successfully';
        $increment = $arr_form_data['attr2'];
        if ($pkey == 0) {
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');
            
        }else{
            $arr_form_data['modified_by'] = $this->Session->read('login_user_id');
            $arr_form_data['modified_date'] = date("Y-m-d");
        }
        $date = strtotime("+$increment day", strtotime($arr_form_data['joining_date']));
        $arr_form_data['attr3'] = date("Y-m-d", $date);
        
        $arr_form_data['company_code'] = $this->Session->read('company_code');
        
        //Save to employee details
        try{
            
            $result = $this->EmployeeDetails->save($arr_form_data);
            
            if (!empty($result)) {
                $message = 'Personal Details Saved Successfully';

                if ($pkey == 0) {
                    //Adding
                    $pkey = $this->EmployeeDetails->getLastInsertID();
                    

                    $company_key = $this->Session->read('company_key');
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                    $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                    if ($punch_type && $punch_type == 'device') {
                        //Device available, so generate emp id concatenate with device id and emp id from device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            
                            $arr_form_data['emp_fkey'] = $pkey;
                            
                            try{
                                //Save to employee proffessionals table
                                $user_group = $this->Session->read("user_group");
                                if (isset($user_group) && $user_group == 2) {
                                    $arr_form_data['attr1'] = $cur_emp_key = $this -> Session -> read("emp_fkey");
                                    $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('HIERARCHY','$pkey','$cur_emp_key','$cur_emp_key') ");
                                }
                                $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                                //Insert user credentials  
                                //Get company_code
                                $str_company_code = $this->Session->read('company_code');

                                $emp_username = ''; //No device details here on manually entering emp data

                                $arr_user_cred['user_pkey'] = 0;
                                $arr_user_cred['emp_fkey'] = $pkey;
                                $arr_user_cred['company_code'] = $str_company_code;
                                $arr_user_cred['user_id'] = $emp_username;
                                $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// *///rand();
                                $arr_user_cred['access_allowed'] = 'n';
                                $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                                $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                                $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                                $arr_user_cred['email'] = $arr_form_data['email'];
                                $arr_user_cred['phone'] = $arr_form_data['mobile_no'];
                                try{
                                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                    $result = $this->UserCredentials->save($arr_user_cred);
                                } catch (Exception $ex) {
                                    $this->restsave($pkey);
                                    $message = 'UserCredentials Details Saving Failed';
                                    if(substr($ex->getMessage(),0,15) == "SQLSTATE[23000]")$message = 'User Already Exists With the same COMPANYID';
                                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                                }
                                
                            } catch (Exception $ex) {
                                $this->restsave($pkey);
                                $message = 'Proffessioanl Details Saving Failed';
                                if(substr($ex->getMessage(),0,15) == "SQLSTATE[23000]")$message = 'User Already Exists With the same COMPANYID try again, or contact the administartor';
                                return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                            }
                            
                        }
                    } else {
                        //Manually generate emp id for companies without device
                        $arr_user_cred = array();
                        $arr_form_data['emp_fkey'] = $pkey;
                        //Save to employee proffessionals table
                        try{
                            $user_group = $this->Session->read("user_group");
                            if (isset($user_group) && $user_group == 2) {
                                $arr_form_data['attr1'] = $cur_emp_key = $this -> Session -> read("emp_fkey");
                                $assign_emps = $this->EmployeeProfessionalDetails->query("INSERT INTO emp_config (type,emp_fkey,policy_id,created_by) values('HIERARCHY','$pkey','$cur_emp_key','$cur_emp_key') ");
                            }
                            
                            $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                            if ($pkey > 0) {
                                //Insert user credentials  
                                $str_company_code = $this->Session->read('company_code');
                                $arr_user_cred['user_pkey'] = 0;
                                $arr_user_cred['emp_fkey'] = $pkey;
                                $arr_user_cred['company_code'] = $str_company_code;
                                $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                                $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// *///rand();
                                $arr_user_cred['access_allowed'] = 'n';
                                $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                                $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                                $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                                $arr_user_cred['email'] = $arr_form_data['email'];
                                $arr_user_cred['phone'] = $arr_form_data['mobile_no'];
                                
                                try{
                                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                    $result = $this->UserCredentials->save($arr_user_cred);
                                    //Call procedure 'Linkemp_deviceanddatabase'
                                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                    $outputParameter = array();
                                    $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                                    $outputParameter[] = isset($arr_form_data['emp_branch']) ? "'" . $arr_form_data['emp_branch'] . "'" : "''";
                                    $outputParameter[] = "''";
                                    try{
                                        $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
                                    } catch (Exception $ex) {
                                        $this->restsave($pkey);
                                        $message = 'Link Employee Details Saving Failed';
                                        return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                                    }
                                    
                                } catch (Exception $ex) {
                                    $this->restsave($pkey);
                                    $message = 'UserCredentials Details Saving Failed';
                                    if(substr($ex->getMessage(),0,15) == "SQLSTATE[23000]")$message = 'User Already Exists With the same COMPANYID try again, or contact the administartor';
                                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                                }
                                
                            }
                        } catch (Exception $ex) {
                            $this->restsave($pkey);
                            $message = 'Proffessioanl Details Saving Failed';
                            if(substr($ex->getMessage(),0,15) == "SQLSTATE[23000]")$message = 'User Already Exists With the same COMPANYID try again, or contact the administartor';
                            return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                        }
                        
                    }
                } else {

                    try{
                        $result1 = $this->EmployeeProfessionalDetails->save($arr_form_data);
                    } catch (Exception $ex) {

                    }
                    
                    //Update user credentials
                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                    try{
                        $this->UserCredentials->updateAll(
                                array(
                            'UserCredentials.first_name' => "'" . $arr_form_data['first_name'] . "'",
                            'UserCredentials.last_name' => "'" . $arr_form_data['last_name'] . "'",
                            'UserCredentials.middle_name' => "'" . $arr_form_data['middile_name'] . "'",
                            'UserCredentials.email' => "'" . $arr_form_data['email'] . "'",
                            'UserCredentials.phone' => "'" . $arr_form_data['mobile_no'] . "'",
                                ), array('UserCredentials.emp_fkey' => $pkey)
                        );
                    } catch (Exception $ex) {
                        $message = 'Updating UserCredentials Failed';
                        return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
                    }
                    
                }

                
            } else {
                $message = 'Personal Details Saved failed';
            }
        } catch (Exception $ex) {
            $message = 'Personal Details Saving Failed';
            return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'pkey' => $pkey, 'message' => $message));
        }
        $emp_companyid = $this->EmployeeProfessionalDetails->query("select emp_company_id,concat(first_name,' ',last_name) as Nme from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_fkey = $pkey ");
        $this->set('emp_companyid', $emp_companyid);
        $company_id = isset($emp_companyid['0']['emp_proff']['emp_company_id'])?$emp_companyid['0']['emp_proff']['emp_company_id']:'';
        $name = isset($emp_companyid['0']['0']['Nme'])?$emp_companyid['0']['0']['Nme']:'';
        //debug($emp_companyid);
        return json_encode(array('success' => TRUE, 'error' => '', 'pkey' => $pkey, 'message' => "Employee Added Successfully ", "emp_companyd" =>$company_id, "name"=> $name));
        
        
        
    }
    
    public function restsave($emp_pkey = 0){
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        try{
            $this->EmployeeProfessionalDetails->query("delete from emp_details where emp_pkey = $emp_pkey ");
            $this->EmployeeProfessionalDetails->query("delete from emp_proff where emp_fkey = $emp_pkey ");
            $this->EmployeeProfessionalDetails->query("delete from user_credentials where emp_fkey = $emp_pkey ");
            return True;
        } catch (Exception $ex) {
            return False;
        }
        
    }

    public function saveemployeesetup() {
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $model = $arr_form_data['model'];
        switch ($model) {
            case 'EmployeeDetails':
                $pkey = $arr_form_data['emp_pkey'];
                $arr_form_data['company_code'] = $this->Session->read('company_code');
                $message = 'Personal Details Saved Successfully';
                //$empname = $arr_form_data['first_name'] .' '.  $arr_form_data['last_name'];
                //$this->mailsend($empname);
                
                break;
            case 'EmployeeProfessionalDetails':
                $pkey = $arr_form_data['emp_fkey'];
                $message = 'Professional Details Saved Successfully';
                $increment = $arr_form_data['attr2'];
                $date = strtotime("+$increment day", strtotime($arr_form_data['joining_date']));
                $arr_form_data['attr3'] = date("Y-m-d", $date);
                
                break;
            default:
                $pkey = 0;
                $message = '';
                break;
        }
        $this->{$model}->useDbConfig = $this->Session->read('ds');
        $result = $this->{$model}->save($arr_form_data);
        if (!empty($result)) {
            if ($pkey == 0) {
                $pkey = $this->{$model}->getLastInsertID();
                if ($model == 'EmployeeDetails') {
					//$this->sendpasswordemail($pkey);
                    //$sessionObj = $this->Session->read("Auth.User");
                    //$company_key    =   $sessionObj['company_key'];
                    $company_key = $this->Session->read('company_key');
                    $arr_central_control = $this->CentralControl->find('first', array('fields' => array('punch_type'), 'conditions' => array('control_pkey' => $company_key)));
                    $punch_type = isset($arr_central_control['CentralControl']['punch_type']) ? $arr_central_control['CentralControl']['punch_type'] : '';
                    if ($punch_type == 'device') {
                        //Device available, so generate emp id concatenate with device id and emp id from device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            //Insert user credentials  
                            //Get company_code
                            $str_company_code = $this->Session->read('company_code');

                            $emp_username = ''; //No device details here on manually entering emp data

                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = $emp_username;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// *///rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                            $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                            $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                            $arr_user_cred['email'] = $arr_form_data['email'];
                            $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $result = $this->UserCredentials->save($arr_user_cred);
							
                        }
                    } else {
                        //Manually generate emp id for companies without device
                        $arr_user_cred = array();
                        if ($pkey > 0) {
                            //Insert user credentials  
                            $str_company_code = $this->Session->read('company_code');
                            $arr_user_cred['user_pkey'] = 0;
                            $arr_user_cred['emp_fkey'] = $pkey;
                            $arr_user_cred['company_code'] = $str_company_code;
                            $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                            $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// *///rand();
                            $arr_user_cred['access_allowed'] = 'n';
                            $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                            $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                            $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                            $arr_user_cred['email'] = $arr_form_data['email'];
                            $arr_user_cred['phone'] = $arr_form_data['mobile_no'];

                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $result = $this->UserCredentials->save($arr_user_cred);
                        }
                    }
                }
            } else {
                if ($model == 'EmployeeDetails') {
                    //Update user credentials
                    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                    $this->UserCredentials->updateAll(
                            array(
                        'UserCredentials.first_name' => "'" . $arr_form_data['first_name'] . "'",
                        'UserCredentials.last_name' => "'" . $arr_form_data['last_name'] . "'",
                        'UserCredentials.middle_name' => "'" . $arr_form_data['middile_name'] . "'",
                        'UserCredentials.email' => "'" . $arr_form_data['email'] . "'",
                        'UserCredentials.phone' => "'" . $arr_form_data['mobile_no'] . "'",
                            ), array('UserCredentials.emp_fkey' => $pkey)
                    );
                }
            }

            if (isset($arr_form_data['emp_proff_pkey']) && ( $arr_form_data['emp_proff_pkey'] == 0 || $arr_form_data['emp_proff_pkey'] == '' )) {
                //Call procedure 'Linkemp_deviceanddatabase'
                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                $outputParameter = array();
                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                $outputParameter[] = isset($arr_form_data['emp_branch']) ? "'" . $arr_form_data['emp_branch'] . "'" : "''";
                $outputParameter[] = "''";
                $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
            }


            //$this->autoRender = FALSE;



            

            return json_encode(array('success' => TRUE, 'pkey' => $pkey, 'message' => $message));
            
        }
    }
    
    public function jsons_getemps($branch = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q']: NULL;
        if($branch != null)
        {
            $branch_condition = "and branch_code in ('$branch')";
        }
        else
        {
            $branch_condition = "";
        }
        if($q != null)
        {
            $q_condition = "and first_name like '%$q%'";
        }
        else
        {
            $q_condition = "";
        }
        if($this->Session->read('emp_fkey')){
           $emp_pkeys = $this->Session->read('emp_fkey');
            $emp_condition = "and emp_proff.attr1 = '$emp_pkeys' ";
        }
        else{
            $emp_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1 $branch_condition $q_condition $emp_condition");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id"=>"0","text"=>"ALL");
        foreach ($branch_array as $key => $value)
        {
            $branch[] = array(
                'id' =>$value['emp_details']['emp_pkey'],
            'text' => $value['emp_details']['first_name']. ' ' .$value['emp_details']['last_name']
                );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    } 
    
    public function saveconfigs() {

        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');

        $edit_salary = FALSE;
        $upload_salary = FALSE;
        $result = "";
                
        $emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
        try {
            $fetch_proff = $this->EmployeeConfig->query("select * from emp_proff where emp_fkey = '$emp_fkey' ");
        } catch (Exception $ex) {
            return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to get proffessional Details "));
        }

        try {
            $arr_gross = $this->EmployeeConfig->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = '$emp_fkey' and end_date_effective is null ");
        } catch (Exception $ex) {

            return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to get annual ctc "));
        }


        $shift_id = isset($fetch_proff['0']['emp_proff']['day_time_seq']) ? $fetch_proff['0']['emp_proff']['day_time_seq'] : 0;
        $leave_policy = isset($fetch_proff['0']['emp_proff']['LEAVEPOLICY_GROUP_ID']) ? $fetch_proff['0']['emp_proff']['LEAVEPOLICY_GROUP_ID'] : 0;
        $holiday_id = isset($fetch_proff['0']['emp_proff']['HOLIDAY_GROUP_ID']) ? $fetch_proff['0']['emp_proff']['HOLIDAY_GROUP_ID'] : 0;
        $salary_id = isset($fetch_proff['0']['emp_proff']['structure_id']) ? $fetch_proff['0']['emp_proff']['structure_id'] : 0;
        $hierarchy = isset($fetch_proff['0']['emp_proff']['attr1']) ? $fetch_proff['0']['emp_proff']['attr1'] : 0;

        $data['id'] = 0;
        $data['emp_fkey'] = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
        $data['modified_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modification_date '] = date("Y-m-d");


        $data['type'] = $this->Session->read('login_user_id');
        
        
        if (isset($arr_form_data['shift']) && $arr_form_data['shift'] != $shift_id) {


            $condition['type'] = 'SHIFT';
            $condition['emp_fkey'] = $arr_form_data['emp_fkey'];

            $data['type'] = 'SHIFT';

            $data['policy_id'] = $arr_form_data['shift'];
            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);

                $this->EmployeeConfig->save($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save shift policy "));
            }
        }


        if (isset($arr_form_data['holidays']) && $arr_form_data['holidays'] != $holiday_id) {

            $condition1['type'] = 'HOLIDAY';
            $condition1['emp_fkey'] = $arr_form_data['emp_fkey'];

            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition1);

                $data['type'] = 'HOLIDAY';
                $data['policy_id'] = $arr_form_data['holidays'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save holiday policy "));
            }
        }
        if (isset($arr_form_data['salary']) && $arr_form_data['salary'] != $salary_id) {

            $condition2['type'] = 'SALARY';
            $condition2['emp_fkey'] = $arr_form_data['emp_fkey'];

            $edit_salary = TRUE;

            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $salary_id = $arr_form_data['salary'];
            $emp = $arr_form_data['emp_fkey'];
            try {
                $user_ids = $this->Session->read('login_user_id');

                //debug($result);
                $data['type'] = 'SALARY';
                $data['policy_id'] = $arr_form_data['salary'];
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition2);

                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save salary policy "));
            }
        }


        if (isset($arr_form_data['leave']) && $arr_form_data['leave'] != $leave_policy) {

            $condition3['type'] = 'LEAVE';
            $condition3['emp_fkey'] = $arr_form_data['emp_fkey'];
            //$condition3['policy_id'] = $arr_form_data['leave'];

            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
                $data['type'] = 'LEAVE';
                $data['policy_id'] = $arr_form_data['leave'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save leave policy "));
            }
        }


        if (isset($arr_form_data['hierarch']) && $arr_form_data['hierarch'] != $hierarchy) {

            $condition4s['type'] = 'HIERARCHY';
            $condition4s['emp_fkey'] = $arr_form_data['emp_fkey'];
            //$condition4s['policy_id'] = $arr_form_data['hierarch'];

            $data['type'] = 'HIERARCHY';

            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $curr_user_id . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition4s
                );

                $data['policy_id'] = $arr_form_data['hierarch'];
                $this->EmployeeConfig->saveAll($data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Save hierarchy policy "));
            }
        }


        if (isset($arr_form_data['annual_gross']) && $arr_form_data['annual_gross'] != '') {

            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $arr_form_data = $this->request->data;
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');

            $upload_salary = TRUE;

            $arr_form_data['emp_anual_ctc'] = $arr_form_data['annual_gross'];
            try {
                $arr_form_data['start_date_effective'] = date("Y-m-1");
                $result = $this->EmployeeCTC->save($arr_form_data);
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Upload Salary "));
            }
        }


        if ($edit_salary == TRUE && $upload_salary == FALSE) {
            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');
            $salary_id = $arr_form_data['salary'];
            $user_ids = $this->Session->read('login_user_id');
            $emp = $arr_form_data['emp_fkey'];
            try {
                $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
                $result = isset($proc['0']['0']['function']) ? $proc['0']['0']['function'] : '';
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Create Salary "));
            }
        }

        $message = "Employee Configuration Details Saved Successfully";
        return json_encode(array('success' => TRUE, "result" => $result, 'message' => $message));
    }

    public function downloadempdataformat() {
        $this->autoRender = FALSE;

        //$auth_user  =   $this->Session->read("Auth.User");
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? $str_company_code . ".xlsx" : "employeedataformat_" . strtotime() . ".xlsx";

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        // create a file pointer connected to the output stream
        //$output = fopen('php://output', 'w');

        App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
        $empcsvdata = new EmployeeCSVData();
        $emp_details_schema = $empcsvdata->getFieldHeadings('NewEmployeeDetails');
        $emp_prof_schema = $empcsvdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_schema = array_merge($emp_details_schema, $emp_prof_schema);
        //fputcsv($output, $emp_schema);

        App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empcsvdata = new EmployeeCSVData();
        $emp_details_schema = $empcsvdata->getFieldHeadings('NewEmployeeDetails');
        $emp_prof_schema = $empcsvdata->getFieldHeadings('NewEmployeeProfessionalDetails');
        $emp_schema = array_merge($emp_details_schema, $emp_prof_schema);
        $objPHPExcel = new PHPExcel();
        $objWorkSheet = $objPHPExcel->createSheet(2);
        
        $objWorkSheet->getStyle('H4')->getNumberFormat()
    ->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
        //$objWorkSheet->getStyle('C3')->getAlignment()->setWrapText(true);
        $cll = 3;
        $clld = 3;
         $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $leaves = $this->FinancialYear->query("select * from designation where status=1");
        $leavesdept = $this->FinancialYear->query("select * from department where status=1");
        
        //Setting Up Bold cell
        $objWorkSheet->getStyle('A1')->getFont()->setBold(true);
        $objWorkSheet->getColumnDimension('B')->setWidth(20);
        $objWorkSheet->getStyle('A2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('B2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('C2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('D2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('E2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('F2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('G2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('H2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('J2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('K2')->getFont()->setBold(true);
        $objWorkSheet->getStyle('H3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('I3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('J3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('K3')->getFont()->setBold(true);
        $objWorkSheet->getStyle('H2')->getFont()->setBold(true);
        
        //Setting the width for all cells
        $objWorkSheet->getColumnDimension('D')->setWidth(10);
        $objWorkSheet->getColumnDimension('E')->setWidth(24);
        $objWorkSheet->getColumnDimension('F')->setWidth(24);
        $objWorkSheet->getColumnDimension('G')->setWidth(20);
        $objWorkSheet->getColumnDimension('H')->setWidth(20);
        $objWorkSheet->getColumnDimension('I')->setWidth(20);
        $objWorkSheet->getColumnDimension('J')->setWidth(20);
        $objWorkSheet->getColumnDimension('k')->setWidth(20);
        $objWorkSheet->getColumnDimension('B')->setWidth(20);
        $objWorkSheet->getColumnDimension('B')->setWidth(20);
        
        
        
        //Rendering Designation , Department Cell
        foreach ($leaves as $lev) {
            $cll = $cll + 1;
            $objWorkSheet->setCellValueExplicit('H' . $cll, $lev['designation']['desig_code'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objWorkSheet->setCellValue('I' . $cll, $lev['designation']['desig_name']);
        }
        foreach ($leavesdept as $lev) {
            $clld = $clld + 1;
            $objWorkSheet->setCellValueExplicit('J' . $clld, $lev['department']['dept_code'],PHPExcel_Cell_DataType::TYPE_STRING);
            $objWorkSheet->setCellValue('K' . $clld, $lev['department']['dept_name']);
        }
        $rowindexhelp = 2;
        $objWorkSheet->setCellValue('A1', 'Form');
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $objWorkSheet->getStyle('H2')->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorkSheet->getStyle('J2')->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorkSheet->mergeCells("H2:I2");
        $objWorkSheet->mergeCells("J2:K2");
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objWorkSheet->setCellValue('A2', 'First Name , Last Name');$objWorkSheet->setCellValue('A3', 'First Name and Last Name Is Mandotory');
        $objWorkSheet->setCellValue('B2', 'Gender');$objWorkSheet->setCellValue('B3', 'Male');$objWorkSheet->setCellValue('B4', 'Female');
        $objWorkSheet->setCellValue('C2', 'Email');$objWorkSheet->setCellValue('C3', 'Email id should be in the correct format and it Is Mandotory');
        $objWorkSheet->setCellValue('D2', 'Martial Status');$objWorkSheet->setCellValue('D3', 'Single');$objWorkSheet->setCellValue('D4', 'Married');
        $objWorkSheet->setCellValue('E2', 'Date of Birth , Joining Date');$objWorkSheet->setCellValue('E3', 'should be in the correct format of (DD-MM-YYYY) and it Is Mandotory');
        $objWorkSheet->setCellValue('F2', 'Company Employee ID');$objWorkSheet->setCellValue('F3', 'Leave If not Have ');
        $objWorkSheet->setCellValue('G2', 'Employee Type');$objWorkSheet->setCellValue('G3', 'Permanent');$objWorkSheet->setCellValue('G4', 'Probation');$objWorkSheet->setCellValue('G5', 'Contract');$objWorkSheet->setCellValue('G6', 'Part Time');$objWorkSheet->setCellValue('G7', 'Temporary');$objWorkSheet->setCellValue('G8', 'Other');
//        $objWorkSheet->setCellValue('B2', 'Last Name');$objWorkSheet->setCellValue('A1', 'Terms');

        $objWorkSheet->setCellValue('H2', ' Designation');
        $objWorkSheet->setCellValue('H3', ' Designation Code');
        $objWorkSheet->setCellValue('I3', ' Designation Name');
        $objWorkSheet->setCellValue('J2', 'Department');
        //$objWorkSheet->setCellValueExplicit('K1', '0022',PHPExcel_Cell_DataType::TYPE_STRING); To set a cell value as String with leading zero 
//        $objWorkSheet->setCellValue('B2', 'First Half = 1');
//        $objWorkSheet->setCellValue('B3', 'Second Half = 2');
        $objWorkSheet->setTitle('Help');
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();
        
        $Desig_type = array();
            
                $this->Designation->useDbConfig = $this->Session->read('ds');
                $arr_leavetypes = $this->Designation->find("all", array("conditions" => array("status"=>1)));

                foreach ($arr_leavetypes as $arr_leavetypes) {
                    $Desig_type[] = $arr_leavetypes['Designation']['desig_code'];
                }
                $leavetype = implode(", ", $Desig_type);
            
            //Ends
            
        $objActiveSheet = $objPHPExcel->getActiveSheet();
        $objActiveSheet->getStyle('A1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('B1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('C1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('D1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('E1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('F1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('G1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('H1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('I1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('J1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('K1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('L1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('M1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('N1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('O1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('P1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Q1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('R1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('S1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('T1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('U1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('V1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('W1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('X1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Y1')->getFont()->setBold(true);
        $objActiveSheet->getStyle('Z1')->getFont()->setBold(true);
        
        $objActiveSheet->getColumnDimension('A')->setWidth(20);
        $objActiveSheet->getColumnDimension('B')->setWidth(10);
        $objActiveSheet->getColumnDimension('C')->setWidth(20);
        $objActiveSheet->getColumnDimension('D')->setWidth(10);
        $objActiveSheet->getColumnDimension('E')->setWidth(10);
        $objActiveSheet->getColumnDimension('F')->setWidth(10);
        $objActiveSheet->getColumnDimension('G')->setWidth(10);
        $objActiveSheet->getColumnDimension('H')->setWidth(10);
        $objActiveSheet->getColumnDimension('I')->setWidth(10);
        $objActiveSheet->getColumnDimension('J')->setWidth(10);
        $objActiveSheet->getColumnDimension('K')->setWidth(10);
        $objActiveSheet->getColumnDimension('L')->setWidth(10);
        $objActiveSheet->getColumnDimension('M')->setWidth(16);
        $objActiveSheet->getColumnDimension('N')->setWidth(10);
        $objActiveSheet->getColumnDimension('O')->setWidth(20);
        $objActiveSheet->getColumnDimension('P')->setWidth(20);
        $objActiveSheet->getColumnDimension('Q')->setWidth(20);
        $objActiveSheet->getColumnDimension('R')->setWidth(20);
        $objActiveSheet->getColumnDimension('S')->setWidth(20);
        $objActiveSheet->getColumnDimension('T')->setWidth(20);
        $objActiveSheet->getColumnDimension('U')->setWidth(20);
        $objActiveSheet->getColumnDimension('V')->setWidth(20);
        $objActiveSheet->getColumnDimension('X')->setWidth(20);
        $objActiveSheet->getColumnDimension('Y')->setWidth(20);
        $objActiveSheet->getColumnDimension('Z')->setWidth(20);
		
        $sheet = array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                //$objPHPExcel->getActiveSheet()->setWidth(10);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                
            }

        }

        $objPHPExcel->getActiveSheet()->setTitle('Employee Data');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function mailsend($empname = '') {
        //$database = $this->Session->read('ds');
        App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailerAutoload.php'));
        $mail = new PHPMailer;
        $mail->SMTPDebug = 2;                               // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
        $mail->Password = 'welcome123';                           // SMTP password
        $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
        $mail->addAddress('sanjundev@gmail.com', 'Sanjun Dev');     // Add a recipient
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = '' . $database . ' Added a new Employee';
        $mail->Body = 'This is the HTML message body <b> Name:' . $empname . ' </b>';
        //$mail->Subject  = '<hr><h1><strong>HI ! '.$empname.' </strong></h1>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if (!$mail->send()) {
            //echo 'Message could not be sent.';
            //echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            //echo 'Message has been sent';
        }
    }

    public function uploadandsaveempdetails($emp_branch = 0) {
        $this->autoRender = FALSE;
        //$authuser   =   $this->Session->read("Auth.User");
        $authuser['company_code'] = $this->Session->read('company_code');
        $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_' . strtotime("now") . '.xlsx' : 'empdata_' . strtotime("now") . '.xlsx';
        $targetpath = getcwd() . "/files/" . $filename;
        $errors = array();
        $message = 'Employee data imported successfully ';
        
        if (move_uploaded_file($_FILES['empdata']['tmp_name'][0], $targetpath)) {

            App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

            $objReader = new PHPExcel_Reader_Excel2007();
            $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

            $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
            $lastColumn++;
            $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
            $arrayempdata = array();
            $mandatory_fields_warning = FALSE;
            if ($highestRowIndex > 1) {

                $arrDuplicateEmpList = array();
                $intImportedCount = 0;

                //atleast one employee records found
                $index = 0;
                for ($row = 1; $row <= $highestRowIndex; $row++) {
                    if ($row == 1) {
                        //Get mandatory headings array here
                        $array_mandatory_columns = array();
                        $array_mandatory_column_names = array('First Name', 'Date of Birth (DD-MM-YYYY)', 'Joining Date (DD-MM-YYYY)', 'Employee Type', 'Designation', 'Department');
                        for ($col = 'A'; $col != $lastColumn; $col++) {
                            $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                            if (in_array($value, $array_mandatory_column_names)) {
                                array_push($array_mandatory_columns, $col);
                            }
                        }
                    } else {
                        for ($col = 'A'; $col != $lastColumn; $col++) {
                            if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                if($objPHPExcel->getActiveSheet()->getCell("A" . $row)->getValue() != ''){
                                $mandatory_fields_warning = true;
                                //debug($col.":".$row);
                                break 2;
                                }
                            }
                            $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                            $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                        }
                        $index++;
                    }
                }
                if ($mandatory_fields_warning) {
                    //Exit if mandatory fields not entered
                    unlink($targetpath);
                    echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                    exit;
                } else {
                    //Continue with save if mandatory field warning is not there
                    //Save employees and return success
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                    $this->Designation->useDbConfig = $this->Session->read('ds');
                    $this->Departments->useDbConfig = $this->Session->read('ds');
                    
                    $arr_designations = Set::extract('/Designation/.',$this->Designation->find("all",array("fields"=>array("desig_code"),"Conditions"=>array("status"=>1))));
                    $desigantions = array();
                    foreach ($arr_designations as $vals){
                        $desigantions[] = $vals['desig_code'];
                    }
                    $arr_departments = Set::extract('/Departments/.',$this->Departments->find("all",array("fields"=>array("dept_code"),"Conditions"=>array("status"=>1))));
                    $departments = array();
                    foreach ($arr_departments as $vals){
                        $departments[] = $vals['dept_code'];
                    }
                    App::import('Vendor', 'EmployeeCSVData', array('file' => 'EmployeeCSVData.php'));
                    $empcsvdata = new EmployeeCSVData();
                    $arr_empdetails_fields = $empcsvdata->getFieldNames('NewEmployeeDetails');
                    $arr_empprof_fields = $empcsvdata->getFieldNames('NewEmployeeProfessionalDetails');
                    foreach ($arrayempdata as $key => $row) {
                        $arr_empdetails_data = array();
                        $arr_empdetails_data['emp_pkey'] = 0;
                        $arr_empdetails_data['status'] = 1;
                        $arr_empdetails_data['emp_id'] = '';
                        $str_company_code = isset($authuser['company_code']) ? $authuser['company_code'] : '';
                        $arr_empdetails_data['company_code'] = $str_company_code;
                        $arr_empdetails_data['branch_code'] = $emp_branch;
                        foreach ($arr_empdetails_fields as $field => $fieldlabel) {
                            //debug($field);
                            if ($field == 'date_of_birth') {
                                $fieldValue = $row[$fieldlabel];
                                $fieldValue = date("Y-m-d",  strtotime($fieldValue));
                                //$fieldValue = substr($fieldValue, 4, 4) . '-' . substr($fieldValue, 2, 2) . '-' . substr($fieldValue, 0, 2);
                                //$fieldValue = substr($fieldValue, 6, 4) . '-' . substr($fieldValue, 3, 2) . '-' . substr($fieldValue, 0, 2);
                            } else if (in_array($field, array('classification', 'maritual_status'))) {
                                $fieldValue = strtolower($row[$fieldlabel]);
                            } else {
                                $fieldValue = $row[$fieldlabel];
                            }
                            $arr_empdetails_data[$field] = $fieldValue;
                        }

                        //Check for, if employee exists or not
                        if (isset($arr_empdetails_data['first_name']) && isset($arr_empdetails_data['last_name']) && isset($arr_empdetails_data['date_of_birth'])) {
                            $arrDuplicateList = $this->EmployeeDetails->find('all', array(
                                'fields' => 'EmployeeDetails.emp_pkey',
                                'conditions' => array(
                                    'first_name' => $arr_empdetails_data['first_name'],
                                    'last_name' => $arr_empdetails_data['last_name'],
                                    'date_of_birth' => $arr_empdetails_data['date_of_birth'],
                                    'status' => 1
                                )
                                    )
                            );
                            if (count($arrDuplicateList) > 0) {
                                $arrDuplicateEmpList[] = $arrDuplicateList[0]['EmployeeDetails']['emp_pkey'];
                                continue; //Take next employee record, since this employee already exixts
                            }
                        } 
                        $arr_empprof_data = array();
                        $arr_empprof_data['emp_proff_pkey'] = 0;
                        
                        //$arr_empprof_data['emp_id'] = ''; //$str_company_code.$user_id; 
                        $arr_empprof_data['emp_branch'] = $emp_branch;

                        foreach ($arr_empprof_fields as $field => $fieldlabel) {
                            if ($field == 'joining_date') {
                                $fieldValue = $row[$fieldlabel];
                                $fieldValue = date("Y-m-d", strtotime($fieldValue));
                                //$fieldValue = substr($fieldValue, 4, 4) . '-' . substr($fieldValue, 2, 2) . '-' . substr($fieldValue, 0, 2);
                                //$fieldValue = substr($fieldValue, 6, 4) . '-' . substr($fieldValue, 3, 2) . '-' . substr($fieldValue, 0, 2);
                            } else {
                                $fieldValue = $row[$fieldlabel];
                            }
                            $arr_empprof_data[$field] = $fieldValue;
                        }
                        //debug($arr_empprof_data);
                        //debug($desigantions);
                        if (in_array($arr_empprof_data['designation'], $desigantions)){
                            
                        }else{
                            $errors[] = $arr_empdetails_data['first_name'];
                            continue;
                        }
                        if (in_array($arr_empprof_data['emp_dept'], $departments)){
                            
                        }else{
                            $errors[] = $arr_empdetails_data['first_name'];
                            continue;
                        }
                        //debug($arr_empdetails_data);
                        try{
                            $result1 = $this->EmployeeDetails->save($arr_empdetails_data);
                        } catch (Exception $ex) {
                            $message = "Employee Details Saving Failed ".$ex->getMessage();
                            $errors[] = $arr_empdetails_data['first_name'];
                            continue;
                        }
                        catch (mysqli_sql_exception $ex){
                            $message = "Employee Credentials Error ".$ex->getMessage();
                            $errors[] = $arr_empdetails_data['first_name'];
                            continue;
                        }
                        
                        $intImportedCount++;

                        if (!empty($result1)) {
                            $pkey = $this->EmployeeDetails->getLastInsertID();

                            $arr_user_cred = array();
                            $arr_user_cred['user_pkey'] = 0;
                            if ($pkey > 0) {
                                
                                $arr_user_cred['user_pkey'] = 0;
                                $arr_user_cred['emp_fkey'] = $pkey;
                                $arr_user_cred['company_code'] = $str_company_code;
                                $arr_user_cred['user_id'] = ''; //$str_company_code.$user_id;
                                $arr_user_cred['password'] = ''; /* Security::hash(rand(), null, true);// *///rand();
                                $arr_user_cred['access_allowed'] = 'n';
                                $arr_user_cred['first_name'] = isset($arr_empdetails_data['first_name']) ? $arr_empdetails_data['first_name'] : '';
                                $arr_user_cred['last_name'] = isset($arr_empdetails_data['last_name']) ? $arr_empdetails_data['last_name'] : '';
                                $arr_user_cred['middle_name'] = isset($arr_empdetails_data['middile_name']) ? $arr_empdetails_data['middile_name'] : '';
                                $arr_user_cred['email'] = isset($arr_empdetails_data['email']) ? $arr_empdetails_data['email'] : '';
                                $arr_user_cred['phone'] = isset($arr_empdetails_data['mobile_no']) ? $arr_empdetails_data['mobile_no'] : '';

                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                try{
                                    $result = $this->UserCredentials->save($arr_user_cred);
                                } catch (Exception $ex) {
                                    $message = "Employee Credentials Saving Error ".$ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                            continue;
                                }
                                 catch (mysqli_sql_exception $ex){
                                     $message = "Employee Credentials Error ".$ex->getMessage();
                                     $this->restsave($pkey);
                                     $errors[] = $arr_empdetails_data['first_name'];
                                     continue;
                                 }
                                
                                
                                $arr_empprof_data['emp_fkey'] = $pkey;
                                try{
                                    $result2 = $this->EmployeeProfessionalDetails->save($arr_empprof_data);
                                } catch (Exception $ex) {
                                    $message = "Employee Proffessioanls Saving Error ".$ex->getMessage();
                                    $this->restsave($pkey);
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    continue;
                                }
                                 catch (mysqli_sql_exception $ex){
                                     $message = "Employee Proffessioanls Error ".$ex->getMessage();
                                     $this->restsave($pkey);
                                     $errors[] = $arr_empdetails_data['first_name'];
                                     continue;
                                 }
                                
                                //Call procedure 'Linkemp_deviceanddatabase'
                                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                $outputParameter = array();
                                $outputParameter[] = "'" . $this->Session->read('company_code') . "'"; //company_code
                                $outputParameter[] = "'" . $emp_branch . "'";
                                $outputParameter[] = "''";
                                try{
                                    $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
                                } catch (Exception $ex) {
                                    $message = "Employee Saving Error ".$ex->getMessage();
                                    $errors[] = $arr_empdetails_data['first_name'];
                                    $this->restsave($pkey);
                                    continue;
                                }
                                 catch (mysqli_sql_exception $ex){
                                     $message = "Employee Saving Error ".$ex->getMessage();
                                     $errors[] = $arr_empdetails_data['first_name'];
                                     $this->restsave($pkey);
                                     continue;
                                 }
                                
                            }
                        }
                    }

                    
                }
                unlink($targetpath);
                echo json_encode(array('success' => 1,'errors'=>$errors, 'msg' => $message, 'imported_count' => $intImportedCount, 'duplicate_emp_list' => $arrDuplicateEmpList));
                exit;
            } else {
                unlink($targetpath);
                echo json_encode(array('success' => 0,'errors'=>$errors, 'msg' => 'Sorry, employee data import failed, no data found!','imported_count' => $intImportedCount, 'duplicate_emp_list' => 0));
                exit;
            }
        } else {
            echo json_encode(array('success' => 0,'errors'=>$errors, 'msg' => 'Sorry, employee data import failed!','imported_count' => $intImportedCount, 'duplicate_emp_list' => 0));
            exit;
        }
    }

    public function getcurrentemployeekey() {
        $this->layout = null;
        $this->autoRender = FALSE;
        $sessionObj = $this->Session->read("Auth.User");
        if (isset($sessionObj['user_group']) && $sessionObj['user_group'] == 2) {
            return json_encode(array('success' => true, 'empPkey' => $sessionObj['emp_fkey']));
        }
        return json_encode(array('success' => false));
    }

    /*
     * Employee CTC Upload form
     * By santhosh on 24 Oct 2015
     */

    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;

        //debug($ctcuploadtype);
        //debug($employee);

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_gross.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        if ($ctcuploadtype == 1) {
            $worksheet = $objPHPExcel->getActiveSheet();
            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Start Date Effective(yyyy-mm)");
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
        } else {
            $worksheet = $objPHPExcel->getActiveSheet();
            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(3, 1, "New Annual Gross Salary");
            $worksheet->setCellValueByColumnAndRow(4, 1, "Start Date Effective(yyyy-mm)");
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(27);
        }
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);

        $cond = '';
        if (isset($employee) && !empty($employee) && $employee != 'null') {
            $cond.= " AND  EmployeeDetails.emp_pkey= if('$employee' in(null,''),EmployeeDetails.emp_pkey, '$employee' )";
        }
        if (isset($branch) && !empty($branch) && $branch != 'null') {
            $cond.= " AND  EmployeeDetails.branch_code= if('$branch' in(null,''),EmployeeDetails.Branch_code,'$branch') ";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($ctcuploadtype == 2) {
            $arr_empdetails = $this->EmployeeDetails->query(" SELECT UserCredentials.user_id, EmployeeInfo.EmpName,ect.emp_anual_ctc "
                    . "FROM emp_details AS EmployeeDetails "
                    . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                    . " LEFT JOIN emp_ctc_transaction AS ect ON (EmployeeDetails.emp_pkey = ect.emp_fkey) "
                    . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                    . " WHERE EmployeeDetails.status = ' 1 ' "
                    . " AND ect.end_date_effective is null "
                    . " $cond ");
        } else {
            $arr_empdetails = $this->EmployeeDetails->query(" SELECT UserCredentials.user_id, EmployeeInfo.EmpName "
                    . "FROM emp_details AS EmployeeDetails "
                    . " INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) "
                    . " INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) "
                    . " WHERE EmployeeDetails.status = ' 1 '   "
                    . " $cond ");
        }
        $rowindex = 2;
        $columnindex = 0;
        if ($ctcuploadtype == 1) {
            foreach ($arr_empdetails as $value) {
                $userid = $value['UserCredentials']['user_id'];
                $empname = $value['EmployeeInfo']['EmpName'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $userid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $empname);
                $rowindex++;
            }
        } else {
            foreach ($arr_empdetails as $value) {
                $userid = $value['UserCredentials']['user_id'];
                $empname = $value['EmployeeInfo']['EmpName'];
                $ctc = $value['ect']['emp_anual_ctc'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $userid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $empname);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $ctc);
                $rowindex++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('Employee Gross Salary ');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsaveempctc($ctcuploadtype = 0) {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);

        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID', 'Employee Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                            }
                            $index++;
                        }
                    }
                    if ($mandatory_fields_warning) {
                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');

                        foreach ($arrayempdata as $key => $row) {

                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($ctcuploadtype == 1) {
                                $emp_anual_ctc = isset($row['Annual Gross Salary']) ? $row['Annual Gross Salary'] : '';
                                $start = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';
                            } else {
                                $emp_anual_ctc = isset($row['New Annual Gross Salary']) ? $row['New Annual Gross Salary'] : '';
                                $start = isset($row['Start Date Effective(yyyy-mm)']) ? $row['Start Date Effective(yyyy-mm)'] : '';
                            }

                            $date = '';
                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($user_id == '') {
                                continue;
                            }
                            // debug($date)
                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            ));
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';
                            // debug($arr_empctc_data);
                            $arr_empctc_data = array();
                            $arr_empctc_data['emp_ctc_upload_pkey'] = 0;
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['emp_fkey'] = $emp_fkey;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['created_date'] = date('Y-m-d');
                            $arr_empctc_data['emp_anual_ctc'] = $emp_anual_ctc;
                            
                            $arr_empctc_data['start_date_effective'] = date("Y-m-1",strtotime($start));

                             //debug($arr_empctc_data);
//                            foreach ($arr_empctc_fields as $field => $fieldlabel) {
//                                $fieldValue = $row[$fieldlabel];
//                                $arr_empctc_data[$field] = $fieldValue;
//                               // debug($arr_empctc_data);
//                            }


                            try {
                                // debug($arr_empctc_data);
                                // die();
                                $result1 = $this->EmployeeCTC->save($arr_empctc_data);

                                //Update salary structure for employee by uploaded CTC
                                //On 20 Sep 2016
                                //arun 15-10-2016 based on ashoakn
//                                $this->updateSalStructureDistributionFn($emp_fkey);
                            } catch (Exception $e) {
                                //debug($e);
                            }
                        }
                    }
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Gross Salary reviced successfully'));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed! '));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Gross Salary revision failed! '));
                exit;
            }
            exit;
        }
    }

//popup for save and update
    public function form() {


        //  debug($_REQUEST['emp_ctc_upload_pkey']);die();
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
        $data['emp_ctc_upload_pkey'] = 0;
        $data['emp_fkey'] = '';
        $data['emp_anual_ctc'] = '';
        $data['emp_monthly_ctc'] = '';
        $data['emp_loan_balance'] = '';
        $data['emp_advance'] = '';
        $data['emp_tds_deducted'] = '';
        if (isset($_REQUEST['emp_ctc_upload_pkey']) && $_REQUEST['emp_ctc_upload_pkey'] != 0) {
            $data_db = $this->EmployeeCTC->find("first", array("conditions" => array("emp_ctc_upload_pkey" => $_REQUEST['emp_ctc_upload_pkey'])));
            $data = $data_db['EmployeeCTC'];
        }
        //  debug($data);

        $this->set("data", $data);
        //$this->layout = null;
    }

//save 
    public function employeesave() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        //arun 15-10-2016
        $date = $arr_form_data['start_date_effective'];
        $month = explode("-", $date);
        $year = $month[0];
        $mon = $month[1];
        $dat = 1;
        $set_month = $year . '-' . $mon . '-' . $dat;
        $arr_form_data['start_date_effective'] = $set_month;
        $result = $this->EmployeeCTC->save($arr_form_data);

        //Update salary structure for employee by newly added CTC
        //On 20 Sep 2016
        //$emp_fkey = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : '';
        //arun 15-10-2016
        //$this->updateSalStructureDistributionFn($emp_fkey);

        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Employee Gross Salary Added successfully";
        echo json_encode($resp);
    }

//datagridelist            
    public function employeelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $branch_condition = '';
        $emp_condition = '';

        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey=$emp";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $branch_condition = "and ed.branch_code='$branch_code'";
        }
        $param = '';
        if(isset($arr_request_data['emp'])){
            $param = "and ed.first_name like '%".$arr_request_data['emp']."%'";
        }
//        if ($emp_fkey != '') {
//            $emp_condition = "and au.emp_fkey=$emp_fkey";
//        }
        //debug($emp_condition);



        $counts = $this->EmployeeCTC->query("select
            COUNT(*)
             from emp_details ed
                            INNER join emp_ctc_upload au on (ed.emp_pkey = au.emp_fkey)
                            INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
                            where au.status=1 
                  $emp_condition $param"
                . "$branch_condition"
                . " and au.emp_ctc_upload_pkey in (select ctc_upload_fkey "
                . "from emp_ctc_transaction"
                . " where end_date_effective is null)"
                . "  "
                . "");
        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->EmployeeCTC->query("select distinct
            emp_fkey,au.*,ei.employee_id,ei.EmpName
             from emp_details ed
                            INNER join emp_ctc_upload au on (ed.emp_pkey = au.emp_fkey)
                            INNER join employee_info  ei on (au.emp_fkey = ei.emp_pkey)
                            where au.status=1 
                $emp_condition $param"
                . "$branch_condition"
                . " and au.emp_ctc_upload_pkey in (select ctc_upload_fkey "
                . "from emp_ctc_transaction"
                . " where end_date_effective is null or end_date_effective >= current_date) "
                . " ORDER BY emp_ctc_upload_pkey desc "
                . " limit $limit  offset $ofst  ");
        $out = array();
        //debug($arr_att);
        foreach ($arr_att as $key => $value) {
            $out['empid'] = isset($value['ei']['employee_id']) ? $value['ei']['employee_id'] : '';
            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
            $out['emp_ctc_upload_pkey'] = isset($value['au']['emp_ctc_upload_pkey']) ? $value['au']['emp_ctc_upload_pkey'] : '';
            $out['emp_fkey'] = isset($value['au']['emp_fkey']) ? $value['au']['emp_fkey'] : '';
            $out['emp_loan_balance'] = isset($value['au']['emp_loan_balance']) ? $value['au']['emp_loan_balance'] : '';
            $out['emp_advance'] = isset($value['au']['emp_advance']) ? $value['au']['emp_advance'] : '';
            $out['emp_anual_ctc'] = isset($value['au']['emp_anual_ctc']) ? $value['au']['emp_anual_ctc'] : '';
            $out['emp_tds_deducted'] = isset($value['au']['emp_tds_deducted']) ? $value['au']['emp_tds_deducted'] : '';
            $out['start_date_effective'] = isset($value['au']['start_date_effective']) ? $value['au']['start_date_effective'] : '';
//            $out['end_date_effective'] = isset($value['au']['end_date_effective']) ? $value['au']['end_date_effective'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    //main page dropdown       
    public function ctcupload() {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1))));
        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
    }

//delete
    public function deleteEmployees() {
        $this->autoRender = FALSE;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_ctc_upload_pkey"]) || isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["emp_ctc_upload_pkey"]);
            //debug($ar_ids);
            $this->EmployeeCTC->updateAll(
                    array('EmployeeCTC.status' => 0), array('EmployeeCTC.emp_ctc_upload_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

    public function deleteEmp() {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($ar_ids);
        $ar_ids = $_REQUEST["ids"];
        $this->EmployeeDetails->updateAll(
                array('EmployeeDetails.status' => 0), array('EmployeeDetails.emp_pkey' => $ar_ids));
        $result['success'] = 1;
        $result['msg'] = "Record(s)  deleted successfully.";

        echo json_encode($result);
    }

    public function addqualification($emp_pkey = 0) {
        $this->set('emp_pkey', $emp_pkey);
    }
    public function addfamily($emp_pkey = 0) {
        $this->set('emp_pkey', $emp_pkey);
    }
    public function passport($emp_pkey = 0){
        $this->set('emp_pkey', $emp_pkey);
    }
    public function savefamily(){
        $this->Family->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $this->Family->save($arr_form_data);
        $this->autoRender = FALSE;
    }
    
    public function savepassport(){
        $this->passport->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $this->passport->save($arr_form_data);
        $this->autoRender = FALSE;
    }
    
    public function savequalifications() {
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        if($arr_form_data['emp_fkey'] != ''){
            $this->qualifcations->save($arr_form_data);
        }else{
            
        }
        $this->autoRender = FALSE;
    }

    public function history($emp_pkey = 0) {
        $this->set('emp_pkey', $emp_pkey);
    }

    public function savehistory() {
        $this->history->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        if($arr_form_data['emp_fkey'] != ''){
            $this->history->save($arr_form_data);
        }else{
            
        }
        $this->autoRender = FALSE;
    }
    public function lstfamilies($emp_pkey = 0){
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);

        $this->Family->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $this->Family->useDbConfig = $this->Session->read('ds');
        $count = $this->Family->find("count");
        $arr_emp = $this->Family->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $resp_emp[$key] = $value["Family"];
        }
        //  debug($resp_emp["rows"][$key]);
        //$resp_emp = $count;
        echo json_encode($resp_emp);
    }

    public function listhistory($emp_pkey = 0) {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');



        $resp_emp = array();
        $resp_emp["data"] = array();
        $this->history->useDbConfig = $this->Session->read('ds');
        $count = $this->history->find("count");
        $arr_emp = $this->history->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $resp_emp["data"][$key] = $value["history"];
        }
        //  debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }
    
    public function getusers($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$emp_fkey = $this->Session->read('emp_fkey');
        $arr_request_data = $this->request->query;
//debug($arr_request_data);
        if (isset($arr_request_data['username'])) {
            $searchkey = $arr_request_data['username'];
            $filter_condition = 'first_name LIKE "%' . $searchkey . '%" and emp_pkey != ' . $emp_fkey;
        } else {
            $filter_condition = '';
        }
        $arr_users = $this->EmployeeDetails->find('all', array(
            'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
            'conditions' => array(
                'status' => 1,
                $filter_condition
            )
                )
        );
        //  debug($arr_users);
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val[0]) : array();
        }
        //  debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function listqualifications($emp_pkey = '') {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $resp_emp = array();
        $resp_emp["data"] = array();
        $count = $this->qualifcations->find("count", array("conditions" => array("emp_fkey" => $emp_pkey)));
        $arr_emp = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        foreach ($arr_emp as $key => $value) {
            $resp_emp["data"][$key] = $value["qualifcations"];
        }

        //  debug($resp_emp["rows"][$key]);
        echo json_encode($resp_emp);
    }

    public function deletequal($pkey = 0) {
        $success = 0;
        $this->autoRender = false;
        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        if ($this->qualifcations->query("delete from qualifcations where qualification_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    public function deletehist($pkey = 0) {
        $success = 0;

        $this->autoRender = false;
        $this->history->useDbConfig = $this->Session->read('ds');
        if ($this->history->query("delete from history where history_pkey in ($pkey) ")) {
            $success = 1;
        }
    }

    //To show emoloyee bulk import response and showing not inserted entries list
    public function showimportresponse($importedCount = '', $strDuplicateEmpKeys = '',$errors = '') {
        //debug($errors);
        $this->set('importedCount', $importedCount);

        if ($importedCount > 0) {
            $this->set('title', 'Employees imported successfully');
        } else {
            $this->set('title', 'Sorry, not imported any employees');
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arrDuplicateEmpList = array();
        if ($strDuplicateEmpKeys != '') {
            $arrDuplicateEmpKeys = explode(',', urldecode($strDuplicateEmpKeys));
            $arrDuplicateEmpList = $this->EmployeeDetails->find('all', array('fields' => 'first_name,last_name,date_of_birth', 'conditions' => array('emp_pkey' => $arrDuplicateEmpKeys)));
        }
		$arrerrors = array();
        if ($errors != '' && $errors != '0') {
            $arrerrors = explode(',', urldecode($errors));
        }
        $this->set('arrerrors', $arrerrors);
        $this->set('arrDuplicateEmpList', $arrDuplicateEmpList);
        
    }
    public function sendpasswordemail($userid) {
        $this->autoRender = FALSE;
		 $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
      $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
         //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
			$companyname = isset($comp['0']['comp_contact_info']['business_name'])? $comp['0']['comp_contact_info']['business_name'] :'Your Company';
			//$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
			//$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
		 try {
            App::import('Vendor', 'PHPMailer', array('file'=>'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;//25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->addAddress('info@mypayrollmaster.in');     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
			$mail->AddAttachment('<?php echo $this->webroot; ?>');
			$mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject  = "New Employee Added To Payroll";
            $mail->Body  = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
	<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
    <tbody>
        <tr>
        	<td>
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:100%!important">
                <tbody>
                	<tr>
                    	<td>
                			<table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                            <tbody>
                            	<tr>
                                    <td colspan="3" height="80" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="padding:0;margin:0;font-size:0;line-height:0">
                                        <table width="690" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td width="30"></td>
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.com/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="codexworld" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(65, 132, 243);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Employee Added to<font style="color:#fff;">'.$companyname.'</font> </h1>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.com is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.com/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
                                 	</tbody>
                                    </table>
                             	</td>
                   			</tr>
                            
                            <tr bgcolor="#ffffff">
                                <td width="30" bgcolor="#eeeeee"></td>
                                <td>
                                    <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Your Login Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/anyone.png" alt="tool" width="120" height="120"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Emp Pkey</h3>
                                                <div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                <div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">'
                                                .$userid.
                                                '</div>
                                                <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                          	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/IaaS-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Organisation</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">'.$companyname.'</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                  	</tbody>
                                    </table>
                                    <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td>
                                            	<h2 style="color:#404040;font-size:22px;font-weight:bold;line-height:26px;padding:0;margin:0">&nbsp;</h2>
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.com/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
                                      </tr></tbody></table></td>
                                <td width="30" bgcolor="#eeeeee"></td>
                            </tr>
                          	</tbody>
                            </table>
                  			<table align="center" width="750px" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:750px!important">
                            <tbody>
                            	<tr>
                                	<td>
                                        <table width="630" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                                        <tbody>
                                        	<tr><td colspan="2" height="30"></td></tr>
                                            <tr>
                                            	<td width="360" valign="top">
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
                                              	</td>
                                            </tr>
                                            <tr><td colspan="2" height="5"></td></tr>
                                           
                                      	</tbody>
                                        </table>
                                   	</td>
                  				</tr>
                          	</tbody>
                            </table>
                  		</td>
                	</tr>
              	</tbody>
                </table>
            </td>
		</tr>
 	</tbody>
    </table>
</div>';

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }
    /*
     * Update salary structure for employee, for the current salary structure
     * On 20 Sep 2016
     */
//arun 15-10-2016 based on ashoakn
//    public function updateSalStructureDistributionFn($emp_fkey = 0) {
//        if (!empty($emp_fkey)) {
//            //Call sal_structure_distribution_fn starts
//            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
//            $arr_emp_structure_id = Set::extract('/EmployeeProfessionalDetails/.', $this->EmployeeProfessionalDetails->find('first', array(
//                                'fields' => 'structure_id',
//                                'conditions' => array('emp_fkey' => $emp_fkey)
//                                    )
//                            )
//            );
//            //debug($arr_emp_structure_id);
//            $emp_structure_id = isset($arr_emp_structure_id[0]['structure_id']) ? $arr_emp_structure_id[0]['structure_id'] : '';
//            if (!empty($emp_structure_id)) {
//                $company_code = $this->Session->read('company_code');
//                $login_user_id = $this->Session->read('login_user_id');
//                $query_sal_structure_distribution_fn = "SELECT sal_structure_distribution_fn('$company_code', $emp_fkey, $emp_structure_id,'$login_user_id') AS resp";
//                //echo $query_sal_structure_distribution_fn;
//                $resp_sal_structure_distribution_fn = $this->EmployeeCTC->query($query_sal_structure_distribution_fn);
//                $isSuccess = isset($resp_sal_structure_distribution_fn[0][0]['resp']) ? $resp_sal_structure_distribution_fn[0][0]['resp'] : 0;
//                if ($isSuccess) {
//                    //Need to call procedure to update salary structure vales for the employee, 
//                    //by the current salary structure : PROGRESSING
//                }
//            }
//            //Call sal_structure_distribution_fn ends
//        }
//    }
    //Ends
}
