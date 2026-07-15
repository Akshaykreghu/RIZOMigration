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
class PromoController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'Promo';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'EmployeeConfig', 'Family', 'EmployeeConfig', 'NoticePeriod', 'qualifcations', 'Promotion', 'history', 'EmployeeTaxTransactions', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function getautocompletions_superior() {
        $this->autoRender = false;
        $user_group = $this->Session->read("user_group");
        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        $filter_condition = array('status' => "1");
        if ($arr_request_data['branch'] != '') {

            $searchkey = $arr_request_data['username'];
            $branch = $arr_request_data['branch'];
            $filter_condition[] = "EmployeeDetails.emp_pkey not in ('$branch' )";
        } else {
            $searchkey = $arr_request_data['username'];
//            $filter_condition[] = 'first_name LIKE "%' . $searchkey . '%"';
            $filter_condition[] = "first_name like '%" . $searchkey . "%' OR EmployeeProfessionalDetails.emp_company_id like '%" . $searchkey . "%'  OR last_name like '%" . $searchkey . "%' OR emp_id like '%" . $searchkey . "%'";
        }
        //    debug($filter_condition);
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        if ($user_group == '2') {
            $emp_pkey = $this->Session->read('emp_fkey');
            $filter_condition[] = "EmployeeProfessionalDetails.attr1 = '$emp_pkey' ";
        }

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code', 'joins' => $joins, 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
//        $arr_filterresult = array(
//            array(
//                'emp_pkey' => '',
//                'emp_name' => 'ALL',
//            )
//        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array();
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function promotion($emp_fkey = 0) {


        $this->set("emp_pkey", $emp_fkey);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
         //edited by sinsiya 17-04-2024
         

$company_code = $this->Session->read('company_code');

if (strtolower($company_code) == 'glet') {
    $arr_history = $this->EmployeeDetails->query("
        SELECT * FROM emp_config_history 
        WHERE emp_fkey = '$emp_fkey'
    ");
    
} else {
   $arr_history = $this->EmployeeDetails->query("
        SELECT * FROM emp_config_history 
        WHERE emp_fkey = '$emp_fkey'
        AND (
            created_by NOT LIKE '%support%' 
            OR created_by = 'support550'
        )
    ");
}

$this->set('arr_history', $arr_history);
        

        //Fetch Units for the company
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAY_GROUP_ID,HOLIDAY_GROUP_NAME from holiday_group where status = 1");
        $this->set('arr_holidays', $arr_holidays);
        
        
        //Fetch Units for the company
        $arr_hierarchy = $this->EmployeeDetails->query("select CONCAT(first_name,' ',last_name) as name from emp_details  where emp_pkey in (select attr1 from emp_proff where emp_fkey = $emp_fkey) and status = 1");
        $this->set('arr_hierarchy', $arr_hierarchy);
//        debug($arr_hierarchy);
        
        //Fetch Units for the company
        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        //Fetch Units for the company
        $arr_leaves = $this->EmployeeDetails->query("select LEAVEPOLICY_GROUP_ID,LEAVEPOLICY_GROUP_NAME from leavepolicy_group where status = 1");
        $this->set('arr_leaves', $arr_leaves);

        //Fetch Units for the company
        $arr_emp_structures = $this->EmployeeDetails->query("select * from employee_structure_vview where emp_pkey = $emp_fkey ");
        $this->set('arr_emp_structures', $arr_emp_structures);

        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_fkey and end_date_effective is null ");
        $this->set('arr_gross', $arr_gross);


        $arr_employee = $this->EmployeeDetails->query("
    SELECT 
        CONCAT(first_name, 
               IF(last_name IS NULL OR last_name = '', '', CONCAT(' ', last_name))
        ) AS name
    FROM emp_details 
    WHERE emp_pkey = $emp_fkey
"); 
$this->set('arr_employee', $arr_employee);


        //Fetch the user id of employees
        $arr_userid = $this->EmployeeDetails->query(" SELECT user_id FROM user_credentials WHERE emp_fkey = $emp_fkey"); 
        $this->set('arr_userid', $arr_userid);    
        //debug($arr_userid);

        $arr_info = $this->EmployeeDetails->query("SELECT joining_date,designation,grade FROM employee_info WHERE emp_pkey = $emp_fkey");
        $this->set('arr_info', $arr_info);

         //edited by athira on 11-03-2025
        $arr_proff=$this->EmployeeDetails->query("SELECT * FROM emp_proff WHERE emp_fkey = $emp_fkey");
        $this->set('arr_proff',$arr_proff);

//        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
//        $years = $this->FinancialYear->find("all");
//        $this->set('years', $years);
//
//
//
//        $this->qualifcations->useDbConfig = $this->Session->read('ds');
//        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_fkey)));
//        $this->set('qualifications', $qualifications);
//
//        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
//        //debug($family);
//        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
//        $this->set('notice_days', $notice_days);
//
//        $arr_empdetails = $this->EmployeeDetails->find("all", array("conditions" => array("status" => 1, "emp_pkey" => $emp_fkey)));
//        $arr_emp_personal_profile = array();
//        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//        $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
//        foreach ($arr_personalinfokeys as $key) {
//            $arr_emp_personal_profile[$key] = '';
//        }
//
//        $arr_professionalinfokeys = array();
//        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
//        $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
//        foreach ($arr_professionalinfokeys as $key) {
//            $arr_emp_professional_profile[$key] = '';
//        }
//        $this->set('arr_personalinfo', $arr_emp_personal_profile);
//        $this->set('arr_empdetails', $arr_empdetails);
//        debug($arr_emp_personal_profile);
//        debug($arr_empdetails);
$company_code=$this->Session->read('company_code');
    //    if($company_code=='HDFN' || $company_code=='HDEQ' || $company_code=='HDSC' || $company_code=='GLET' || $company_code=='GAAR' || $company_code=='NRMY' || $company_code=='TRCK' || $company_code=='MRZC'  || $company_code=='DYGL' || $company_code=='SHIN' || $company_code=='AMST' || $company_code=='NWTR' || $company_code=='THNG' || $company_code=='CSMT'){
    if (!in_array($company_code, [
    'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN',
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','VNDG'
])) {
        $this->render('promotionjoin');
         }
         else{
            $this->render('promotion');
         }
    }

    public function listemployees() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $user_group = $this->Session->read("user_group");

        $emp_fkey = $this->Session->read("emp_fkey");

        $this->Promotion->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];


        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'promotion_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';

        $ofst = ($page - 1) * $limit;

        $fields = 'CONCAT(EmployeeDetails.first_name," ",EmployeeDetails.last_name) AS name,EmployeeProfessionalDetails.emp_company_id, Promotion.promotion_pkey,Promotion.emp_fkey,Promotion.created_date,Promotion.approved_status,Promotion.remarks,Promotion.promotion_status';
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Promotion.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        $conditions = array('Promotion.status' => 1, "approved_by" => $emp_fkey, "promotion_status" => "APPLIED");

        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->Promotion->find("count", array('joins' => $joins, "conditions" => $conditions));
        $arr_emp = $this->Promotion->find("all", array(
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
            $resp_emp["rows"][$key] = array_merge($value["Promotion"], $value['EmployeeProfessionalDetails'], $value['0']);
        }
        //debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

    public function promotion_home() {
        
    }

    public function savepromotions() {

        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->Promotion->useDbConfig = $this->Session->read('ds');

        $arr_save = array();

        $arr_form_data['created_date'] = date('Y-m-d');
        $arr_form_data['promotion_status'] = 'APPLIED';
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');

        $data['id'] = 0;
        $data['emp_fkey'] = isset($arr_form_data['emp_fkey']) ? $arr_form_data['emp_fkey'] : 0;
        $data['modified_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modification_date '] = date("Y-m-d");


        $data['type'] = $this->Session->read('login_user_id');


        $save = $this->Promotion->save($arr_form_data);

        $message = "Employee Configuration Details Saved Successfully";
        return json_encode(array('success' => TRUE, "result" => $result, 'message' => $message));
    }

    public function approvepromotion($promo_pkey = 0) {



        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_departments = $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->Promotion->useDbConfig = $this->Session->read('ds');
        $this->set('arr_departments', $arr_departments);
        $this->set('promo_pkey', $promo_pkey);

        $fields = 'CONCAT(EmployeeDetails.first_name," ",EmployeeDetails.last_name) AS name,EmployeeProfessionalDetails.emp_company_id,'
                . ' Promotion.*,Designation.desig_name,Department.dept_name,ShiftPolicy.day_time_desc,SalaryStructure.structure_name,'
                . 'LeavePolicy.LEAVEPOLICY_GROUP_NAME,Branches.branch_name';

        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Promotion.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Promotion.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Department',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Promotion.emp_dept = Department.dept_code')
            ),
            array(
                'table' => 'working_day_time_procedures',
                'alias' => 'ShiftPolicy',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Promotion.shift = ShiftPolicy.day_time_seq')
            ),
            array(
                'table' => 'leavepolicy_group',
                'alias' => 'LeavePolicy',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Promotion.leave = LeavePolicy.LEAVEPOLICY_GROUP_ID')
            ),
            array(
                'table' => 'salary_structure',
                'alias' => 'SalaryStructure',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Promotion.salary = SalaryStructure.structure_id')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Promotion.emp_branch = Branches.branch_code')
            )
        );

        $conditions = array('Promotion.status' => 1, 'Promotion.promotion_pkey' => $promo_pkey);

        //Fetch Units for the company
        $arr_promo = $this->Promotion->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            "conditions" => $conditions));
        $this->set('arr_promo', $arr_promo);

        $emp_fkey = isset($arr_promo['0']['Promotion']['emp_fkey']) ? $arr_promo['0']['Promotion']['emp_fkey'] : '';

        $this->set("emp_pkey", $emp_fkey);

        //debug($arr_promo);
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
        $arr_emp_structures = $this->EmployeeDetails->query("select * from employee_structure_vview where emp_pkey = $emp_fkey ");
        $this->set('arr_emp_structures', $arr_emp_structures);

        //Fetch Units for the company
        $arr_salary = $this->EmployeeDetails->query("select structure_id,structure_name,structure_eg_amt from salary_structure where structure_active = 1");
        $this->set('arr_salary', $arr_salary);

        $arr_gross = $this->EmployeeDetails->query("select emp_anual_ctc from emp_ctc_transaction where emp_fkey = $emp_fkey and end_date_effective is null ");
        $this->set('arr_gross', $arr_gross);

        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $years = $this->FinancialYear->find("all");
        $this->set('years', $years);



        $this->qualifcations->useDbConfig = $this->Session->read('ds');
        $qualifications = $this->qualifcations->find("all", array("conditions" => array("emp_fkey" => $emp_fkey)));
        $this->set('qualifications', $qualifications);

        $this->NoticePeriod->useDbConfig = $this->Session->read('ds');
        //debug($family);
        $notice_days = $this->NoticePeriod->find("all", array("conditions" => array("status" => 1)));
        $this->set('notice_days', $notice_days);

        $arr_empdetails = $this->EmployeeDetails->find("all", array("conditions" => array("status" => 1, "emp_pkey" => $emp_fkey)));
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
        $this->set('arr_personalinfo', $arr_emp_personal_profile);
        $this->set('arr_empdetails', $arr_empdetails);
//        debug($arr_emp_personal_profile);
//        debug($arr_empdetails);
    }

    public function approvesave() {
        $this->autoRender = FALSE;
        $this->Promotion->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        $arr_form_data['approved_status'] = 'Y';
        $arr_form_data['approved_date'] = date("Y-m-d");
        $arr_form_data['promotion_status'] = 'APPROVED';
        
        $promo_pkey = $arr_form_data['promotion_pkey'];
        try {

            $get_promtions = $this->Promotion->find("all",array("conditions"=>array("promotion_pkey"=>$promo_pkey)));

            if($get_promtions){
                $arr_form_data['emp_fkey'] = $get_promtions['0']['Promotion']['emp_fkey'];
                if(isset($get_promtions['0']['Promotion']['emp_type']) && $get_promtions['0']['Promotion']['emp_type'] != null){
                    $this->chnageType($get_promtions['0']['Promotion']['emp_type'],$arr_form_data['emp_fkey']);
                }
                if(isset($get_promtions['0']['Promotion']['designation']) && $get_promtions['0']['Promotion']['designation'] != null){
                    $this->changeDesignation($get_promtions['0']['Promotion']['designation'],$arr_form_data['emp_fkey']);
                }
                if(isset($get_promtions['0']['Promotion']['emp_dept']) && $get_promtions['0']['Promotion']['emp_dept'] != null){
                    $this->changeDepartment($get_promtions['0']['Promotion']['emp_dept'],$arr_form_data['emp_fkey']);
                }
                if(isset($get_promtions['0']['Promotion']['emp_branch']) && $get_promtions['0']['Promotion']['emp_branch'] != null){
                    $this->changeBranch($get_promtions['0']['Promotion']['emp_branch'],$arr_form_data['emp_fkey']);
                }
                if(isset($get_promtions['0']['Promotion']['shift']) && $get_promtions['0']['Promotion']['shift'] != null){
                    $this->addToShift($get_promtions['0']['Promotion']['shift'],$arr_form_data['emp_fkey']);
                }
                if(isset($get_promtions['0']['Promotion']['leave']) && $get_promtions['0']['Promotion']['leave'] != null){
                    $this->addToLeave($get_promtions['0']['Promotion']['leave'],$arr_form_data['emp_fkey']);
                }
                if(isset($get_promtions['0']['Promotion']['annual_gross']) && $get_promtions['0']['Promotion']['annual_gross'] != null){
                    $this->promotionWage($get_promtions['0']['Promotion']['annual_gross'],$arr_form_data['emp_fkey']);
                }
                if(isset($get_promtions['0']['Promotion']['hierarch']) && $get_promtions['0']['Promotion']['hierarch'] != null){
                    $this->addToSuperior($get_promtions['0']['Promotion']['hierarch'],$arr_form_data['emp_fkey']);
                }
                if(isset($get_promtions['0']['Promotion']['salary']) && $get_promtions['0']['Promotion']['salary'] != null){
                    $this->addToSalary($get_promtions['0']['Promotion']['salary'],$arr_form_data['emp_fkey']);
                }
                
                $save_approve = $this->Promotion->save($arr_form_data);
                
            }
            
        } catch (Exception $ex) {
            echo json_encode(array("success" => 0, "msg" => $ex->getMessage(). "Promotion Approval Saving Failed, Please Try again", "color" => "danger"));
            die();
        }
        echo json_encode(array("success" => 1, "msg" => "Approved Promotion Successfully ! ", "color" => "success"));
    }

    public function addToShift($shift_id = 0, $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition['type'] = 'SHIFT';
        $condition['emp_fkey'] = $emp_pkey;

        $data['id'] = 0;
        $data['emp_fkey'] = $emp_pkey;
        $data['modified_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['modification_date '] = date("Y-m-d");
        
        $data['type'] = 'SHIFT';

        $data['policy_id'] = $shift_id;
        try {
            $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $this->Session->read('login_user_id') . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition);

            $this->EmployeeConfig->save($data);
            return true;
        } catch (Exception $ex) {
            return json_encode(array('success' => FALSE, "result" => "", 'message' => "Failed to Save shift policy "));
        }
    }

    public function addToSalary($shift_id = 0, $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition2['type'] = 'SALARY';
            $condition2['emp_fkey'] = $emp_pkey;

            $edit_salary = TRUE;

            $data['id'] = 0;
            $data['emp_fkey'] = $emp_pkey;
            $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
            $data['creation_date'] = date("Y-m-d");
            
            $error = '@`Perror_massage`';
            $company = $this->Session->read('company_code');

            $emp = $emp_pkey;
            try {
                $user_ids = $this->Session->read('login_user_id');

                //debug($result);
                $data['type'] = 'SALARY';
                $data['policy_id'] = $shift_id;
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $this->Session->read('login_user_id') . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition2);

                $this->EmployeeConfig->saveAll($data);
                
                try{
                    
                    $this->UpdateSalary($shift_id,$emp_pkey);
                } catch (Exception $ex) {

                }
                
                return true;
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => "", 'message' => "Failed to Save salary policy "));
            }
    }

    public function addToSuperior($shift_id = 0, $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition4s['type'] = 'HIERARCHY';
            $condition4s['emp_fkey'] = $emp_pkey;
            //$condition4s['policy_id'] = $arr_form_data['hierarch'];
            $data['id'] = 0;
            $data['emp_fkey'] = $emp_pkey;
            $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
            $data['creation_date'] = date("Y-m-d");

            $data['type'] = 'HIERARCHY';

            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $this->Session->read('login_user_id') . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition4s
                );

                $data['policy_id'] = $shift_id;
                $this->EmployeeConfig->saveAll($data);
                return true;
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => "", 'message' => "Failed to Save hierarchy policy "));
            }
    }

    public function addToLeave($shift_id = 0, $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition3['type'] = 'LEAVE';
            $condition3['emp_fkey'] = $emp_pkey;
            //$condition3['policy_id'] = $arr_form_data['leave'];
            $data['id'] = 0;
            $data['emp_fkey'] = $emp_pkey;
            $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
            $data['creation_date'] = date("Y-m-d");
            
            try {
                $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $this->Session->read('login_user_id') . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
                $data['type'] = 'LEAVE';
                $data['policy_id'] = $shift_id;
                $this->EmployeeConfig->saveAll($data);
                return true;
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => "", 'message' => "Failed to Save leave policy "));
            }
    }

    public function changeDesignation($shift_id = 0, $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition3['type'] = 'DESIG';
        $condition3['emp_fkey'] = $emp_pkey;
        
        $data['id'] = 0;
        $data['emp_fkey'] = $emp_pkey;
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        //$data['creation_date'] = date("Y-m-d");
        $created_by = $this->Session->read('login_user_id');
        $this->EmployeeProfessionalDetails->query("UPDATE emp_proff SET designation = '$shift_id',modified_by = '$curr_user_id' WHERE emp_fkey = '$emp_pkey' "); // updateAll(array("emp_type = '".$shift_id."' "),array("emp_fkey"=>$emp_pkey));
        
        $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $created_by . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
        $res = $this->EmployeeConfig->query("select id from designation where desig_code = '$shift_id' and status = 1");
        $code = $res['0']['designation']['id'];
        $data['type'] = 'DESIG';
        $data['policy_id'] = $code;
        $this->EmployeeConfig->saveAll($data);
        return true;
    }

    public function changeDepartment($shift_id = 0, $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition3['type'] = 'DEPARTMENTS';
        $condition3['emp_fkey'] = $emp_pkey;
        
        $data['id'] = 0;
        $data['emp_fkey'] = $emp_pkey;
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        //$data['creation_date'] = date("Y-m-d");
        
        $created_by = $this->Session->read('login_user_id');
        
        $this->EmployeeProfessionalDetails->query("UPDATE emp_proff SET emp_dept = '$shift_id',modified_by = '$curr_user_id' WHERE emp_fkey = '$emp_pkey' "); // updateAll(array("emp_type = '".$shift_id."' "),array("emp_fkey"=>$emp_pkey));
        
        $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $created_by . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
        $res = $this->EmployeeConfig->query("select id from department where dept_code = '$shift_id' and status = 1");
        $code = $res['0']['department']['id'];
        $data['type'] = 'DEPARTMENTS';
        $data['policy_id'] = $code;
        $this->EmployeeConfig->saveAll($data);
        return true;
    }
    
    public function chnageType($shift_id = '', $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition3['type'] = 'TYPE';
        $condition3['emp_fkey'] = $emp_pkey;
        
        $data['id'] = 0;
        $data['emp_fkey'] = $emp_pkey;
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        $data['creation_date'] = date("Y-m-d");
        
        $created_by = $this->Session->read('login_user_id');
        
        
        $this->EmployeeProfessionalDetails->query("UPDATE emp_proff SET emp_type = '$shift_id',modified_by = '$created_by' WHERE emp_fkey = '$emp_pkey' "); // updateAll(array("emp_type = '".$shift_id."' "),array("emp_fkey"=>$emp_pkey));
        
//        $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $this->Session->read('login_user_id') . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
//        
//                $data['type'] = 'TYPE';
//                $data['policy_id'] = $shift_id;
//                $this->EmployeeConfig->saveAll($data);
        
        return true;
    }

    public function changeBranch($shift_id = 0, $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $condition3['type'] = 'BRANCH';
        $condition3['emp_fkey'] = $emp_pkey;
        
        $data['id'] = 0;
        $data['emp_fkey'] = $emp_pkey;
        $data['created_by'] = $curr_user_id = $this->Session->read('login_user_id');
        //$data['creation_date'] = date("Y-m-d");
        $created_by = $this->Session->read('login_user_id');
        
        $this->EmployeeProfessionalDetails->query("UPDATE emp_proff SET emp_branch = '$shift_id',modified_by = '$created_by' WHERE emp_fkey = '$emp_pkey' ");
        
        $this->EmployeeConfig->updateAll(array('EmployeeConfig.modified_by' => "'" . $created_by . "'", 'EmployeeConfig.modification_date' => 'now()', 'EmployeeConfig.status' => 0), $condition3);
        $res = $this->EmployeeConfig->query("select id from branches where branch_code = '$shift_id' and status = 1");
        $code = $res['0']['branches']['id'];
        $data['type'] = 'BRANCH';
        $data['policy_id'] = $code;
        $this->EmployeeConfig->saveAll($data);
        
        return true;
    }

    public function UpdateSalary($salary_id = 0,$emp = 0){
        $error = '@`Perror_massage`';
        $company = $this->Session->read('company_code');
//        $salary_id = $arr_form_data['salary'];
        $user_ids = $this->Session->read('login_user_id');
//        $emp = $arr_form_data['emp_fkey'];
        try {
            $proc = $this->EmployeeConfig->query("select sal_structure_distribution_fn('$company',$emp,$salary_id,'$user_ids') as function");
            $result = isset($proc['0']['0']['function']) ? $proc['0']['0']['function'] : '';
        } catch (Exception $ex) {
            return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Create Salary "));
        }
    }


    public function promotionWage($shift_id = 0, $emp_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            $arr_form_data = $this->request->data;
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');

            $upload_salary = TRUE;

            $arr_form_data['emp_fkey'] = $emp_pkey; 
            
            $arr_form_data['emp_anual_ctc'] = $shift_id;
            try {
                $arr_form_data['start_date_effective'] = date("Y-m-1");
                $result = $this->EmployeeCTC->save($arr_form_data);
                
            } catch (Exception $ex) {
                return json_encode(array('success' => FALSE, "result" => $result, 'message' => "Failed to Upload Salary "));
            }
        
        return true;
    }

    public function rejsave() {
        $this->autoRender = FALSE;
        $this->Promotion->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        $arr_form_data['approved_status'] = 'R';
        $arr_form_data['approved_date'] = date("Y-m-d");
        $arr_form_data['promotion_status'] = 'REJECTED';

        try {

            $save_approve = $this->Promotion->save($arr_form_data);
        } catch (Exception $ex) {
            echo json_encode(array("success" => 0, "msg" => "Promotion Saving Failed, Please Try again", "color" => "danger"));
            die();
        }
        echo json_encode(array("success" => 1, "msg" => "Rejected Promotion Changes  ! ", "color" => "success"));
    }

}
