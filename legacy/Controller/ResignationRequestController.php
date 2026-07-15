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
ini_set("display_errors", 0);

App::uses('ConnectionManager', 'Model');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class ResignationRequestController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'ResignationRequest';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('AppModel','ResignationRequests', 'LeaveRequests', 'EmployeeDetails','ResignationAccept','LeavePolicy','Termination');
    public $components = array('Session');

    /*
     * Leave List Landing Page
     */
    public function index() {
        $this -> ResignationRequests -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        
     $cur_emp_key = $this -> Session -> read("emp_fkey");
        
        $fields = 'ResignationRequests.*,ResignationAccept.isApproved,ResignationAccept.isauthorized,ResignationAccept.last_allowed_date,ResignationAccept.comments_to_emp';
        $joins = array(
            array(
                'table' => 'resignation_accept',
                'alias' => 'ResignationAccept',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('ResignationAccept.Resignation_pkey = ResignationRequests.Resignation_pkey')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = ResignationRequests.authorised_to')
            )
        );
        $conditions  =   array ("ResignationRequests.emp_fkey"=>$cur_emp_key,"ResignationRequests.status"=>1);
        
        $resp_empleaverequests = array();
        $resp_empleaverequests["rows"]= array();
        $count = $this -> ResignationRequests -> find("count",array("conditions"=>$conditions));
        
        $arr_empleaverequests =  $this -> ResignationRequests ->find("all",array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions
        ));
        $this->set("arr_empleaverequests",$arr_empleaverequests);
        //added by megha on 15/02/2020
        $termination = $this->ResignationRequests->query("select * from termination where emp_fkey = $cur_emp_key and status = 1");
        $this->set("termination",$termination);
    }
    
    /*
     * Leave List Landing Page
     */
    public function Emprequests() {
       
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        $this->set("cur_emp_key",$cur_emp_key);
    }
    
    
      public function getusers() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
      $emp_fkey = $this->Session->read('emp_fkey');     
        $arr_request_data = $this->request->query;
//debug($arr_request_data);
        if (isset($arr_request_data['username'])) {
            $searchkey = $arr_request_data['username'];
            $filter_condition = 'first_name LIKE "%' . $searchkey . '%" and emp_pkey != '.$emp_fkey;
        } else {
            $filter_condition = '';
        }
        $arr_users = $this->EmployeeDetails->find('all', array(
            'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
            'conditions' => array(
                $filter_condition
            )
                )
              
        ); 
    //  debug($arr_users);
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'],$val[0]) : array();
        }
     //  debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }
    
    
    public function addeditleave($emp = 0) {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->ResignationRequests->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $company = $this->CompanyContactInfo->find("first");
        $this->set("company",$company);
        $fields = 'ResignationAccept.last_allowed_date,ResignationAccept.comments_to_emp,ResignationAccept.comments_to_hr,ResignationAccept.isApproved,ResignationAccept.manager_reason,ResignationAccept.forwarded,ResignationAccept.isauthorized,ResignationAccept.authorized_date,ResignationAccept.handover_to,ResignationRequests.*,EmployeeDetails.address,EmployeeDetails.state,EmployeeDetails.mobile_no,EmployeeDetails.email,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeProffessional.joining_date,EmployeeProffessional.emp_company_id,EmployeeProffessional.emp_type,Branches.branch_name,Designation.desig_name,Department.dept_name';
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ResignationRequests.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'resignation_accept',
                'alias' => 'ResignationAccept',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ResignationRequests.Resignation_pkey = ResignationAccept.Resignation_pkey')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_branch = Branches.id')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.designation = Designation.id')
            ),
            array(
                'table' => 'department',
                'alias' => 'Department',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_dept = Department.id')
            )
        );
        $arr_requests = $this->ResignationRequests->find("all",array("joins"=>$joins,"fields"=>$fields,"conditions"=>array("ResignationRequests.Resignation_pkey"=>$emp,"ResignationRequests.status"=>1)));
        $this->set("arr_requests",$arr_requests);
        $fields1 = 'EmployeeDetails.first_name,EmployeeDetails.last_name,Branches.branch_name,Designation.desig_name,Department.dept_name';
        $to = isset($arr_requests['0']['ResignationRequests']['authorised_to'])?$arr_requests['0']['ResignationRequests']['authorised_to']:'0';
        $joins1 = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_branch = Branches.id')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.designation = Designation.id')
            ),
            array(
                'table' => 'department',
                'alias' => 'Department',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_dept = Department.id')
            )
        );
        if($to)
        {
        $arr_to = $this->EmployeeDetails->find("all",array("joins"=>$joins1,"fields"=>$fields1,"conditions"=>array("EmployeeDetails.emp_pkey"=>$to)));
        $this->set("arr_to",$arr_to);
        //$fields2 = 'EmployeeDetails.first_name,EmployeeDetails.last_name,Branches.branch_name,Designation.desig_name,Department.dept_name';
        $to1 = $arr_requests['0']['ResignationAccept']['handover_to'];
//        $joins2 = array(
//            array(
//                'table' => 'emp_proff',
//                'alias' => 'EmployeeProffessional',
//                'type' => 'LEFT',
//                'foreignKey' => false,
//                'conditions' => array('EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey')
//            ),
//            array(
//                'table' => 'branches',
//                'alias' => 'Branches',
//                'type' => 'LEFT',
//                'foreignKey' => false,
//                'conditions' => array('EmployeeProffessional.emp_branch = Branches.id')
//            ),
//            array(
//                'table' => 'designation',
//                'alias' => 'Designation',
//                'type' => 'LEFT',
//                'foreignKey' => false,
//                'conditions' => array('EmployeeProffessional.designation = Designation.id')
//            ),
//            array(
//                'table' => 'department',
//                'alias' => 'Department',
//                'type' => 'LEFT',
//                'foreignKey' => false,
//                'conditions' => array('EmployeeProffessional.emp_dept = Department.id')
//            )
//        );
        
        $arr_emp = $this->EmployeeDetails->find("all",array("fields"=>"first_name,last_name","conditions"=>array("EmployeeDetails.emp_pkey"=>$to1)));
        $this->set("arr_emp",$arr_emp);
        $tohr = $arr_requests['0']['ResignationAccept']['forwarded'];
        $arr_emphr = $this->EmployeeDetails->find("all",array("fields"=>"first_name,last_name","conditions"=>array("EmployeeDetails.emp_pkey"=>$tohr)));
        $this->set("arr_emphr",$arr_emphr);
        }
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        $this->set("cur_emp_key",$cur_emp_key);
        
    }
    

    public function listleaves() {        
        $this -> autoRender = FALSE;
        $this->ResignationRequests->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        
        $ofst = ($page-1)*$limit;
        
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        
        $fields = "ResignationRequests.Resignation_pkey,ResignationRequests.authorised_to,ResignationRequests.applied_date,ResignationRequests.Reason,ResignationRequests.Reason_Desc,ResignationRequests.Comments_to_manager,ResignationRequests.Last_workingday,ResignationRequests.contact_no,concat(EmployeeDetails.first_name,' ',EmployeeDetails.Last_name) as Name";
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeDetails.emp_pkey = ResignationRequests.emp_fkey')
            ),
            array(
                'table' => 'resignation_accept',
                'alias' => 'ResignationAccept',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('ResignationAccept.Resignation_pkey = ResignationRequests.Resignation_pkey')
            )
        );
        $conditions  =   array("(ResignationRequests.authorised_to = $cur_emp_key and isauthorized = '0') or (ResignationAccept.forwarded = $cur_emp_key and isApproved = '0') and ResignationRequests.status = 1");
        
        $resp_myleaverequests = array();
        $resp_myleaverequests["rows"]= array();
        
        $arr_myleaverequests =  $this -> ResignationRequests ->find("all",array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'limit'=>intval($limit),
            'offset'=>intval($ofst)
        ));
        //  debug($arr_myleaverequests);
        foreach ($arr_myleaverequests as $key => $value) {
            $resp_myleaverequests["rows"][$key] = array_merge($value["0"],$value["ResignationRequests"]);
			
        }
        
        echo json_encode($resp_myleaverequests);        
    }
    public function Manageleave()
    {
        
    }
    public function Request($leaveentryId = 0) {
        $this->ResignationRequests->useDbConfig = $this->Session->read('ds');
        $leaveentryId = $this -> Session -> read("emp_fkey");
        $fields = 'ResignationRequests.*,EmployeeDetails.address,EmployeeDetails.state,EmployeeDetails.mobile_no,EmployeeDetails.email,EmployeeDetails.first_name,EmployeeDetails.last_name';
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ResignationRequests.authorised_to = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey')
            )
        );
        $arr_requests = $this->ResignationRequests->find("all",array("joins"=>$joins,"fields"=>$fields,"conditions"=>array("ResignationRequests.emp_fkey"=>$leaveentryId,"ResignationRequests.status"=>1)));
        $this->set("arr_requests",$arr_requests);
        //debug($arr_requests);
    }
    public function letter()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->ResignationRequests->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $company = $this->CompanyContactInfo->find("first");
        $this->set("company",$company);
        $leaveentryId = $this -> Session -> read("emp_fkey");
        $fields = 'ResignationRequests.*,EmployeeDetails.address,EmployeeDetails.state,EmployeeDetails.mobile_no,EmployeeDetails.email,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeProffessional.joining_date,EmployeeProffessional.emp_company_id,EmployeeProffessional.emp_type,Branches.branch_name,Designation.desig_name,Department.dept_name';
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ResignationRequests.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_branch = Branches.id')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.designation = Designation.id')
            ),
            array(
                'table' => 'department',
                'alias' => 'Department',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_dept = Department.id')
            )
        );
        $arr_requests = $this->ResignationRequests->find("all",array("joins"=>$joins,"fields"=>$fields,"conditions"=>array("ResignationRequests.emp_fkey"=>$leaveentryId,"ResignationRequests.status"=>1)));
        $this->set("arr_requests",$arr_requests);
        $fields1 = 'EmployeeDetails.first_name,EmployeeDetails.last_name,Branches.branch_name,Designation.desig_name,Department.dept_name';
        $to = $arr_requests['0']['ResignationRequests']['authorised_to'];
        $joins1 = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_branch = Branches.id')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.designation = Designation.id')
            ),
            array(
                'table' => 'department',
                'alias' => 'Department',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_dept = Department.id')
            )
        );
        $arr_to = $this->EmployeeDetails->find("all",array("joins"=>$joins1,"fields"=>$fields1,"conditions"=>array("EmployeeDetails.emp_pkey"=>$to)));
        $this->set("arr_to",$arr_to);
    }

public function letteredit()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->ResignationRequests->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $company = $this->CompanyContactInfo->find("first");
        $this->set("company",$company);
        $leaveentryId = $this -> Session -> read("emp_fkey");
        $fields = 'ResignationRequests.*,EmployeeDetails.address,EmployeeDetails.state,EmployeeDetails.mobile_no,EmployeeDetails.email,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeProffessional.joining_date,EmployeeProffessional.emp_company_id,EmployeeProffessional.emp_type,Branches.branch_name,Designation.desig_name,Department.dept_name';
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ResignationRequests.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_branch = Branches.branch_code')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.designation = Designation.desig_code')
            ),
            array(
                'table' => 'department',
                'alias' => 'Department',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_dept = Department.dept_code')
            )
        );
        $arr_requests = $this->ResignationRequests->find("all",array("joins"=>$joins,"fields"=>$fields,"conditions"=>array("ResignationRequests.emp_fkey"=>$leaveentryId,"ResignationRequests.status"=>1)));
        $this->set("arr_requests",$arr_requests);
        $fields1 = 'EmployeeDetails.first_name,EmployeeDetails.last_name,Branches.branch_name,Designation.desig_name,Department.dept_name';
        $to = isset($arr_requests['0']['ResignationRequests']['authorised_to'])?$arr_requests['0']['ResignationRequests']['authorised_to']:'0';
        $joins1 = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_fkey = EmployeeDetails.emp_pkey')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_branch = Branches.id')
            ),
            array(
                'table' => 'designation',
                'alias' => 'Designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.designation = Designation.id')
            ),
            array(
                'table' => 'department',
                'alias' => 'Department',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProffessional.emp_dept = Department.id')
            )
        );
        $arr_to = $this->EmployeeDetails->find("all",array("joins"=>$joins1,"fields"=>$fields1,"conditions"=>array("EmployeeDetails.emp_pkey"=>$to)));
        $this->set("arr_to",$arr_to);
    }
    
    public function withd()
    {
        $this->autoRender = false;
        $resignation_fkey = $this -> Session -> read("emp_fkey");
        $this->ResignationRequests->useDbConfig = $this -> Session -> read('ds');
        $this->Termination->useDbConfig = $this -> Session -> read('ds');
        $this->ResignationAccept->useDbConfig = $this -> Session -> read('ds');
        $arr = $this->ResignationRequests->find("all",array("conditions"=>array("emp_fkey"=>$resignation_fkey,"ResignationRequests.status"=>1)));
        // edited by megha on 15/02/2020
      $arr_count = $this->Termination->find("count",array("conditions"=>array("emp_fkey"=>$resignation_fkey,"Termination.status"=>1)));
       
        //if($arr > 0)
         if($arr > 0 && $arr_count > 0)
        {
            $arr1 = $this->ResignationRequests->updateAll(
            array('status' => 0,'Resignation_status'=>'"Cancelled"'),
            array("emp_fkey"=>$resignation_fkey)
            );
            //edited by megha 05/12/2019 admin termination remove
            $arr3 = $this->Termination->updateAll(
            array('status' => 0,'remarks'=>'"Employee cancelled"'),
            array("emp_fkey"=>$resignation_fkey)
            );
         echo json_encode(1);
        }
        else{
         echo json_encode(0);
        }
        $pkey = isset($arr['0']['ResignationRequests']['Resignation_pkey'])? $arr['0']['ResignationRequests']['Resignation_pkey'] : ''; 
        $arr2 = $this->ResignationAccept->find("all",array("conditions"=>array("Resignation_pkey"=>$pkey,"ResignationAccept.status"=>1)));
        if($arr > 0)
        {
            $arr3 = $this->ResignationAccept->updateAll(
    array('status' => 0),
    array("Resignation_pkey"=>$pkey)
            );
        }
        
    }
    public function grandrequest() {
        $this->autoRender = FALSE ;
        $arr_form_data = $this->request -> data;
        if(isset($arr_form_data['chek_assets']))
        {
            $arr_form_data['chek_assets'] = 1;
        }
        if(isset($arr_form_data['chek_formalities']))
        {
            $arr_form_data['chek_formalities'] = 1;
        }
        if(isset($arr_form_data['chek_leave']))
        {
            $arr_form_data['chek_leave'] = 1;
        }
        $resignation_fkey = $arr_form_data['Resignation_pkey'];
        $this->ResignationAccept->useDbConfig = $this -> Session -> read('ds');
        $arr = $this->ResignationAccept->find("all",array("conditions"=>array("Resignation_pkey"=>$resignation_fkey,"ResignationAccept.status"=>1)));
        if($arr > 0)
        {
            $arr_form_data['resignation_accept_pkey'] = $arr['0']['ResignationAccept']['resignation_accept_pkey'];
        }
        $this->ResignationAccept->useDbConfig = $this -> Session -> read('ds');
        $this->ResignationAccept->save($arr_form_data);
    }
    public function Empagreed()
    {
        $this->autoRender = FALSE ;
        $resignation_fkey = $this -> Session -> read("emp_fkey");
        $this->ResignationRequests->useDbConfig = $this -> Session -> read('ds');
        $arr = $this->ResignationRequests->find("all",array("conditions"=>array("emp_fkey"=>$resignation_fkey,"ResignationRequests.status"=>1)));
        $arr_form_data = $this->request -> data;
        
        if($arr > 0)
        {
            $arr_form_data['Resignation_pkey'] = $arr['0']['ResignationRequests']['Resignation_pkey'];
            $arr_form_data['agree'] == 1;
        }
        $this->ResignationRequests->save($arr_form_data);
    }

    public function deleteresignation($rs = 0)
    {
        $this->autoRender = false;
         $this->ResignationRequests->useDbConfig = $this -> Session -> read('ds');
        $this->ResignationAccept->useDbConfig = $this -> Session -> read('ds');
        
            $arr1 = $this->ResignationRequests->updateAll(
    array('status' => 0),
    array("Resignation_pkey"=>$rs)
            );
        
            $arr3 = $this->ResignationAccept->updateAll(
    array('status' => 0),
    array("Resignation_pkey"=>$rs)
            );
    }

    public function Saverequests($leaveentryId = 0) {
        $this->autoRender = false;
        $arr_form_data = $this->request -> data;
        $arr_form_data['emp_fkey'] = $this -> Session -> read("emp_fkey");
        $resignation_fkey = $this -> Session -> read("emp_fkey");
        $this->ResignationRequests->useDbConfig = $this -> Session -> read('ds');
        $this->Termination->useDbConfig = $this -> Session -> read('ds');
        $arr_coun = $this->Termination->find("count",array("conditions"=>array("emp_fkey"=>$resignation_fkey,"Termination.status"=>1)));
        $arr = $this->ResignationRequests->find("all",array("conditions"=>array("emp_fkey"=>$resignation_fkey,"ResignationRequests.status"=>1)));
        // edited by megha on 04/10/2019 admin request
        if(empty($arr) && $arr_coun == 0)
        {
        //$arr_form_data['Resignation_pkey'] = $arr['0']['ResignationRequests']['Resignation_pkey'];
        $this->ResignationRequests->save($arr_form_data);
        $emp = $this -> Session -> read("emp_fkey");
        $reason = $arr_form_data['Reason'];
        $applied_date = date('Y-m-d');
        $last_workingday = $arr_form_data['Last_workingday'];
        $reason_Desc = $arr_form_data['Reason_Desc'];
        $last_approved_working_date = $arr_form_data['Last_workingday'];
        $count = $this->ResignationRequests->query("select count(emp_fkey) from termination where emp_fkey = $emp ");
        if($count['0']['0']['count(emp_fkey)']== 0){
        //last working day added by megha on 15/02/2020
        //$termination = $this->ResignationRequests->query("insert into termination (emp_fkey,Reason,submitted_date,last_applied_date,last_approved_working_date,remarks,act_last_working_day) values('$emp','$reason','$applied_date','$last_workingday','$last_workingday','$reason_Desc','$last_workingday');");
        $termination = $this->ResignationRequests->query("insert into termination (emp_fkey,Reason,submitted_date,last_applied_date,last_working_date,last_approved_working_date,remarks,act_last_working_day) values('$emp','$reason','$applied_date','$last_workingday','$last_workingday','$last_workingday','$reason_Desc','$last_workingday');");
       } else{
            $emp_term = $this->Termination->find("first",array("conditions"=>array("emp_fkey"=>$emp,"Termination.status"=>0),'order' => array('terminate_pkey' => 'DESC')));
            $term_pkey = $emp_term['Termination']['terminate_pkey'];
            //edited by megha on 26/12/2019 inserting new request as new row
            $arr3 = $this->Termination->updateAll(
            array('status' => 0),
	    array("emp_fkey"=>$emp,"terminate_pkey"=>$term_pkey)
            );
            $termination = $this->ResignationRequests->query("insert into termination (emp_fkey,Reason,submitted_date,last_applied_date,last_working_date,last_approved_working_date,remarks,act_last_working_day) values('$emp','$reason','$applied_date','$last_workingday','$last_workingday','$last_workingday','$reason_Desc','$last_workingday');");
//            $arr3 = $this->Termination->updateAll(
//            array('status' => 1,
//                  'Reason' => "'$reason'",
//                  'submitted_date' => "'$applied_date'",
//                  'last_applied_date' => "'$last_workingday'",
//                  'last_approved_working_date' => "'$last_workingday'",
//                  'remarks' => "'$reason_Desc'",
//                  'act_last_working_day' => "'$last_workingday'",),
//            array("emp_fkey"=>$emp,"terminate_pkey"=>$term_pkey)
//            );
        }
        //end admin    
       }else{
        $this->ResignationRequests->save($arr_form_data);
        }
    }
//Ends
}