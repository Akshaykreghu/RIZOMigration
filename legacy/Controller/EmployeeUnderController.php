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
class EmployeeUnderController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout="default";
    public $name ='EmployeeUnder';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('CentralControl','UserCredentials','EmployeeDetails','Designation','EmployeeProfessionalDetails','Departments','Grades','Verticals','Units','TaxHead','EmployeeCTC');
    public $components = array('MasterdataManagement');
    
    /*
     * Employees landing view
     */
    public function index()
    {   
        $this -> autoRender = FALSE;
        $user_group = $this -> Session -> read("user_group");
        $userPkey = $this -> Session -> read("emp_fkey");
           //Admin view
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $active_emp_count   =   $this->EmployeeDetails->find('count',array('conditions'=>array('status'=>1)));
            $this->set('active_emp_count',$active_emp_count);
           $branch = $this->EmployeeDetails->query("select branches.* from emp_proff left join branches on (branches.branch_code = emp_proff.emp_branch) where emp_fkey = '$userPkey' ");
          
            //Fetch Units for the company
            $arr_branches   =  array(
	(int) 0 => array(
		'id' => '1',
		'branch_code' => $branch['0']['branches']['branch_code'],
		'branch_name' => $branch['0']['branches']['branch_name']
	));
            $this->set('arr_branches',$arr_branches);
           // $arr_Emp=$this->MasterdataManagement->getEmployeeListForCombo();
             
           // $this->set('arr_Emp',$arr_Emp);
            
            $arr_Des=$this->MasterdataManagement->getDesignationsListForCombo();
           // debug($arr_Des);
             $this->set('arr_Des',$arr_Des);
            $this->render('index');
       
    }
    
    public function getstages($site_pkey =0) {
        $this->autoRender = false;
        //$site_pkey=[];
      //  debug($site_pkey);
         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $sitepkey_array =$site_pkey;
        if($sitepkey_array == '')
        {
            $conditions='';
        }
        else
        {
        $conditions='branch_code="'.$sitepkey_array.'"';
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
		$arr_Emp=Set::extract('/EmployeeDetails/.',$this->EmployeeDetails->find('all',array('fields'=>'emp_pkey,emp_name,branch_code','conditions'=>array('status'=>1,$conditions))));
		   //debug($arr_stages);
        $str_stage_options_html = '';
        $this->set('arr_Emp',$arr_Emp);
       // debug($arr_Emp);
        foreach ($arr_Emp as $value) {
            $emp_pkey = isset($value['emp_pkey']) ? $value['emp_pkey'] : '';
            $emp_name= isset($value['emp_name']) ? $value['emp_name'] : '';
            $str_stage_options_html .= '<option value="' . $emp_pkey . '">' . $emp_name . '</option>';
          //  debug($str_stage_options_html);
        }
        echo $str_stage_options_html;
    }
    
      public function getautocompletions() {
        $this->autoRender = false;
       
         $arr_request_data = $this->request->query;//$site_pkey=[];
    //  debug($arr_request_data);
      if ($arr_request_data['branch'] != '') {
          
            $searchkey = $arr_request_data['username'];
            $branch =  $arr_request_data['branch'];
            $filter_condition = 'branch_code = "'.$branch.'" and first_name LIKE "%' . $searchkey . '%"';
        } else {
            $searchkey = $arr_request_data['username'];
            $filter_condition = 'first_name LIKE "%' . $searchkey . '%"';
        }
    //    debug($filter_condition);
      $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
		$arr_Emp=$this->EmployeeDetails->find('all',array('fields'=>'emp_pkey,emp_name,branch_code','conditions'=>array('status'=>1,$filter_condition)));
		   
    //debug($arr_Emp);
        $arr_filterresult = array();
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ?$val['EmployeeDetails'] : array();
        }
  //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }
    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */    
    public function listemployees(){
        $this -> autoRender = FALSE;
        $arr_request_data = $this->request->data;
       //  debug($arr_request_data);
         $userPkey = $this -> Session -> read("emp_fkey");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
          $branch = $this->EmployeeDetails->query("select branches.* from emp_proff left join branches on (branches.branch_code = emp_proff.emp_branch) where emp_fkey = '$userPkey' ");
         
        $branch=  $branch['0']['branches']['branch_code'];
        $emp= isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '' ;
        $des=isset($arr_request_data['designation']) ? $arr_request_data['designation'] : '' ;
     //  debug($branch);
     //   debug($emp);
        if ($branch == '') 
        {
            $branch = '';
        } 
        else 
        {
           $branch= 'EmployeeDetails.branch_code="'.$branch.'"';
        }
       // debug($branch);
          if ($emp == '') 
        {
            $emp = '';
        } 
        else 
        {
           $emp= 'EmployeeDetails.emp_pkey="'.$emp.'"';
        }
     //   debug($emp);
            if ($des == '') 
        {
            $des = '';
        } 
        else 
        {
           $des= 'EmployeeProfessionalDetails.designation="'.$des.'"';
        }
        $this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');
        $limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];
		
		$ofst = ($page-1)*$limit;
		
        $fields = 'designation.desig_name,designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
        $conditions  =   array('EmployeeDetails.status'=>1,$branch,$emp,$des);
        
        $this -> datatable["conditions"] = $conditions;
        $resp_emp = array();
		$resp_emp["rows"]= array();
       // $count = $this -> EmployeeDetails -> find("count",array("conditions"=>$conditions));
        $arr_emp = $this -> EmployeeDetails -> find("all",array('fields' => $fields,'joins' => $joins,"conditions"=>$conditions,'limit'=>intval($limit),'offset'=>intval($ofst)));
      // debug($arr_emp);
        foreach ($arr_emp as $key => $value) {
            $resp_emp["rows"][$key] = array_merge($value["EmployeeDetails"],$value["EmployeeProfessionalDetails"],$value[0],$value["designation"]);
        }
      //  debug($resp_emp["rows"][$key]);
       // $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
        
    }

    public function setup($emp_pkey=0){       
        $sessionObj = $this->Session->read("Auth.User");
        $user_group = $this -> Session -> read("user_group");
        if($emp_pkey){
            //edit mode
            $this->set('emp_pkey',$emp_pkey);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_details    =   $this->EmployeeDetails->find('first',array('conditions'=>array('emp_pkey'=>$emp_pkey)));
            if(is_array($arr_emp_details['EmployeeDetails'])){
                $this->set('arr_emp_details',$arr_emp_details['EmployeeDetails']);  
            }
            
            
            $this->set('user_group',$user_group);
            
         //   debug($user_group);
               //Admin
                $head   =   $arr_emp_details['EmployeeDetails']['first_name']." ".$arr_emp_details['EmployeeDetails']['middile_name']." ".$arr_emp_details['EmployeeDetails']['last_name']."'s Profile";
         
            $this->set('head',$head);
            
            //Load employee personal details 
            /*$arr_emp_personal_profile   =   array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_personal_profile   =   $this->EmployeeDetails->find('first',array('conditions'=>array('emp_pkey'=>$emp_pkey)));
            $arr_emp_personal_profile   =   $arr_emp_personal_profile['EmployeeDetails'];
            $this->set('arr_personalinfo',$arr_emp_personal_profile);*/            
            $this->loadEmpDetails($emp_pkey);
            //Ends
            
            //Load employee professional details
            $this->loadEmpProfDetails($emp_pkey);
            //Ends
            
            //Load employee tax heads
            $this->set('arr_emptaxtransactions',$this->requestAction("/Taxation/loadEmpTaxationDetails/$emp_pkey"));
            //Ends
        }else{
            //add mode
            $arr_emp_personal_profile = array();
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_personalinfokeys = array_keys($this->EmployeeDetails->schema());
            foreach($arr_personalinfokeys as $key){
                $arr_emp_personal_profile[$key] = '';
            }
            
            $arr_professionalinfokeys = array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_professionalinfokeys = array_keys($this->EmployeeProfessionalDetails->schema());
            foreach($arr_professionalinfokeys as $key){
                $arr_emp_professional_profile[$key] = '';
            }
            $this->set('head','New Employee');
            $this->set('emp_pkey',0);
            $this->set('arr_personalinfo',$arr_emp_personal_profile);
            $this->set('arr_professionalinfo',$arr_emp_professional_profile);
            $this->set('arr_taxationinfo',array());
        }

        //Fetch taxation fields for creating form dynamically
        $this->set('arr_taxheadfields',$this->requestAction("/Taxation/getTaxHeadFields"));
        
        //Fetch departments for the company
        $arr_departments =   $this->MasterdataManagement->getDepartmentsListForCombo();  
        $this->set('arr_departments',$arr_departments);
        
        //Fetch Grades for the company
        $arr_designations =   $this->MasterdataManagement->getDesignationsListForCombo();  
        $this->set('arr_designations',$arr_designations);
        
        //Fetch Grades for the company
        $arr_grades =   $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades',$arr_grades);
        
        //Fetch Verticals for the company
        $arr_verticals  =   $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals',$arr_verticals);
        
        //Fetch Units for the company
       $userPkey = $this -> Session -> read("emp_fkey");
           //Admin view
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
           $branch = $this->EmployeeDetails->query("select branches.* from emp_proff left join branches on (branches.branch_code = emp_proff.emp_branch) where emp_fkey = '$userPkey' ");
          
            //Fetch Units for the company
            $arr_branches   =  array(
	(int) 0 => array(
		'id' => '1',
		'branch_code' => $branch['0']['branches']['branch_code'],
		'branch_name' => $branch['0']['branches']['branch_name']
	));
        $this->set('arr_branches',$arr_branches);
    }

    /*
     * Show tax Head Details form
     */
    public function showtaxheaddetail($emp_pkey=0, $tax_heads_fkey=0)
    {     
        $this->set('emp_pkey',$emp_pkey);
        $this->set('tax_heads_fkey',$tax_heads_fkey);
        
        $this -> TaxHead -> useDbConfig = $this -> Session -> read('ds');
        $tax_head = Set::extract('/TaxHead/.', $this -> TaxHead -> find("first",array('conditions'=>array('tax_heads_pkey'=>$tax_heads_fkey))));
        $tax_head_name = isset($tax_head[0]['tax_name'])?$tax_head[0]['tax_name']:'Details';
        $this->set('tax_head_name',$tax_head_name);
        
        $arr_taxheaddetails = $this->requestAction("/Taxation/getTaxHeadDetails/$tax_heads_fkey");
        $arr_emptaxtransactions = $this->requestAction("/Taxation/loadEmpTaxHeadDetails/$emp_pkey/$tax_heads_fkey");
        $this->set('arr_taxheaddetails',$arr_taxheaddetails);
        $this->set('arr_emptaxtransactions',$arr_emptaxtransactions);
    }
    //Ends
    public function loadEmpDetails($emp_pkey=0){        
            if(isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != ''){
                    $arr_emp_personal_profile   =   array();
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp_personal_profile   =   $this->EmployeeDetails->find('first',array('conditions'=>array('emp_pkey'=>$emp_pkey)));
                    $arr_emp_personal_profile   =   $arr_emp_personal_profile['EmployeeDetails'];
                    $this->set('arr_personalinfo',$arr_emp_personal_profile);
            }
            else{
                $this->set('arr_professionalinfo',array());
            }
    }
    public function loadEmpProfDetails($emp_pkey='')
    {
        $user_group = $this -> Session -> read("user_group");
        if($user_group == 2){
            $emp_pkey   = $emp_pkey;//$sessionObj['emp_fkey'];  
        }

        if(isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != ''){
            $arr_emp_professional_profile   =   array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile   =   $this->EmployeeProfessionalDetails->find('first',array('conditions'=>array('emp_fkey'=>$emp_pkey)));
            if(empty($arr_emp_professional_profile)){
                $empId  =   '';
                $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                $arr_emp_professional_profile_last  =   $this->EmployeeProfessionalDetails->find('first',array('order' => array('emp_proff_pkey' => 'DESC')));
                if(isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])){         
                    $str_emp_id =   $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key    =   $sessionObj['company_key'];
                    $arr_central_control    =   $this->CentralControl->find('first',array('fields'=>array('company_code'),'conditions'=>array('control_pkey'=>$company_key)));
                    $str_company_code   =   isset($arr_central_control['CentralControl']['company_code'])?$arr_central_control['CentralControl']['company_code']:'';
                    $arr_emp_id =   explode($str_company_code, $str_emp_id);
                    if(isset($arr_emp_id[1]) && $arr_emp_id[1] != ''){
                        $emp_id =   $str_company_code.($arr_emp_id[1]+1);
                    }else{
                        $emp_id =   '';
                    }
                }else{
                    //He is the first employee                  
                    $sessionObj = $this->Session->read("Auth.User");
                    $company_key    =   $sessionObj['company_key'];
                    $arr_central_control    =   $this->CentralControl->find('first',array('fields'=>array('company_code'),'conditions'=>array('control_pkey'=>$company_key)));
                    $str_company_code   =   isset($arr_central_control['CentralControl']['company_code'])?$arr_central_control['CentralControl']['company_code']:'';
                    $emp_id =   $str_company_code."1000";
                }
                $arr_emp_professional_profile['emp_id'] =   $emp_id;
                $arr_emp_professional_profile['emp_fkey'] =   $emp_pkey;
            }else{
                $arr_emp_professional_profile   =   $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            }
            
            $this->set('arr_professionalinfo',$arr_emp_professional_profile);
        }
        else{
            $this->set('arr_professionalinfo',array());
        }
    }
    public function empprofdetails($emp_pkey=0){
        $this->layout   =   null;
        $emp_pkey=1;
        
        //Fetch departments for the company
        $arr_departments    =   $this->MasterdataManagement->getDepartmentsListForCombo();
        $this->set('arr_departments',$arr_departments);
        
        //Fetch Grades for the company
        $arr_grades =   $this->MasterdataManagement->getGradesListForCombo();
        $this->set('arr_grades',$arr_grades);
        
        //Fetch Verticals for the company
        $arr_verticals  =   $this->MasterdataManagement->getVerticalsListForCombo();
        $this->set('arr_verticals',$arr_verticals);
        
        //Fetch Units for the company
        $arr_branches   =   $this->MasterdataManagement->getBranchesListForCombo();
        $this->set('arr_branches',$arr_branches);
        
        //Fetch employee professional details if in edit mode
        if(isset($emp_pkey) && $emp_pkey != 0 && $emp_pkey != ''){
            $arr_emp_professional_profile   =   array();
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile   =   $this->EmployeeProfessionalDetails->find('first',array('conditions'=>array('emp_fkey'=>$emp_pkey)));
            $arr_emp_professional_profile   =   $arr_emp_professional_profile['EmployeeProfessionalDetails'];
            $this->set('arr_emp_professional_profile',$arr_emp_professional_profile);
            $this->set('emp_id',$arr_emp_professional_profile['emp_id']);
            $emp_proff_pkey =   $arr_emp_professional_profile['emp_proff_pkey'];
        }else{
            $emp_proff_pkey =   0;
            $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
            $arr_emp_professional_profile_last  =   $this->EmployeeProfessionalDetails->find('last');
            if(isset($arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'])){         
                $str_emp_id =   $arr_emp_professional_profile_last['EmployeeProfessionalDetails']['emp_id'];
                $company_key    =   $this->session->read('company_key');
                $arr_central_control    =   $this->CentralControl->find('first',array('fields'=>array('company_code'),'conditions'=>array('company_pkey'=>$company_key)));
                $str_company_code   =   isset($arr_central_control['CentralControl']['company_code'])?$arr_central_control['CentralControl']['company_code']:'';
                $arr_emp_id =   explode($str_company_code, $str_emp_id);
                if(isset($arr_emp_id[1]) && $arr_emp_id[1] != ''){
                    $emp_id =   $str_company_code.($arr_emp_id[1]+1);
                }else{
                    $emp_id =   '';
                }
            }else{
                $emp_id =   '';
            }
            $this->set('emp_id',$arr_emp_professional_profile_last['emp_id']);
        }
        $this->set('emp_proff_pkey',$emp_proff_pkey);
    }
    public function emptaxationdetails($emp_pkey=0){
        
    }
    public function saveemployeesetup(){
        
          $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->autoRender   =   FALSE;
        $arr_form_data  =   $this->request->data;
         $userPkey = $this -> Session -> read("emp_fkey");
       $company_code = $this -> Session -> read("company_code");
          $branch = $this->EmployeeDetails->query("select branches.* from emp_proff left join branches on (branches.branch_code = emp_proff.emp_branch) where emp_fkey = '$userPkey' ");
         
        $branch=  $branch['0']['branches']['branch_code'];
        $model  =   $arr_form_data['model'];
        switch ($model) {
            case 'EmployeeDetails':
                                $pkey   =   $arr_form_data['emp_pkey'];
                                $arr_form_data['company_code'] = $company_code;
                                 $arr_form_data['branch_code'] = $branch;
                                $message    =   'Personal Details Saved Successfully';
                break;
            case 'EmployeeProfessionalDetails':
                $pkey   =   $arr_form_data['emp_fkey'];
                 $arr_form_data['branch_code'] = $branch;
                                $message    =   'Professional Details Saved Successfully';
                break;
            default:
                $pkey   =   0;
                                $message    =   ''; 
                break;
        }
        $this->{$model}->useDbConfig = $this->Session->read('ds');
        $result =   $this->{$model}->save($arr_form_data);
        if(!empty($result)){
                    if($pkey == 0){
                            $pkey   =   $this->{$model}->getLastInsertID();
                            if($model   ==  'EmployeeDetails'){
                                //$sessionObj = $this->Session->read("Auth.User");
                                //$company_key    =   $sessionObj['company_key'];
                                $company_key    =   $this->Session->read('company_key');
                                $arr_central_control    =   $this->CentralControl->find('first',array('fields'=>array('punch_type'),'conditions'=>array('control_pkey'=>$company_key)));
                                $punch_type   =   isset($arr_central_control['CentralControl']['punch_type'])?$arr_central_control['CentralControl']['punch_type']:'';
                                if($punch_type == 'device'){
                                    //Device available, so generate emp id concatenate with device id and emp id from device
                                    $arr_user_cred  =   array();
                                    if($pkey > 0){
                                        //Insert user credentials  
                                        //Get company_code
                                        $str_company_code   =   $this->Session->read('company_key');
                                        
                                        $emp_username    =   '';//No device details here on manually entering emp data
                                        
                                        $arr_user_cred['user_pkey'] = 0;
                                        $arr_user_cred['emp_fkey'] = $pkey;
                                         $arr_user_cred['branch_code'] = $branch;
                                        $arr_user_cred['company_code'] = $company_code;
                                        $arr_user_cred['user_id'] = $emp_username;
                                        $arr_user_cred['password'] = '';/*Security::hash(rand(), null, true);//*///rand();
                                        $arr_user_cred['access_allowed'] = 'n';
                                        $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                                        $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                                        $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                                        $arr_user_cred['email'] = $arr_form_data['email'];
                                        $arr_user_cred['phone'] = $arr_form_data['mobile_no'];
    
                                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                        $result =   $this->UserCredentials->save($arr_user_cred);
                                    }
                                }else{
                                    //Manually generate emp id for companies without device
                                    $arr_user_cred  =   array();
                                    if($pkey > 0){
                                        //Insert user credentials  
                                        $str_company_code   =   $this->Session->read('company_key');
                                        /*//Get company_code
                                        $auth_user_id   =   $this->Session->read('user_id');
    
                                        //Generate user_id
                                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                        $arr_last_user =   $this->UserCredentials->find('first',array(
                                                'order' => array('user_pkey' => 'DESC')
                                            )
                                        );
                                        if(isset($arr_last_user['UserCredentials']['user_id']) && $arr_last_user['UserCredentials']['user_id'] != $auth_user_id){
                                            if(isset($arr_last_user['UserCredentials']['user_id']) && $arr_last_user['UserCredentials']['user_id'] != '') {
                                                $arr_user_id    =   explode($str_company_code, $arr_last_user['UserCredentials']['user_id']);
                                                $last_user_id   =   isset($arr_user_id[1])?$arr_user_id[1]:0;    
                                                $user_id        =   $last_user_id+1;
                                            }else{
                                                $user_id    =   '1000';
                                            }
                                        }else{
                                            //no employees added yet
                                            $user_id    =   '1000';
                                        }*/
                                        $arr_user_cred['user_pkey'] = 0;
                                        $arr_user_cred['emp_fkey'] = $pkey;
                                         $arr_user_cred['branch_code'] = $branch;
                                        $arr_user_cred['company_code'] = $str_company_code;
                                        $arr_user_cred['user_id'] = '';//$str_company_code.$user_id;
                                        $arr_user_cred['password'] = '';/*Security::hash(rand(), null, true);//*///rand();
                                        $arr_user_cred['access_allowed'] = 'n';
                                        $arr_user_cred['first_name'] = $arr_form_data['first_name'];
                                        $arr_user_cred['last_name'] = $arr_form_data['last_name'];
                                        $arr_user_cred['middle_name'] = $arr_form_data['middile_name'];
                                        $arr_user_cred['email'] = $arr_form_data['email'];
                                        $arr_user_cred['phone'] = $arr_form_data['mobile_no'];
    
                                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                                        $result =   $this->UserCredentials->save($arr_user_cred);
                                    }
                                }
                            }
                    }else{
                        if($model   ==  'EmployeeDetails'){
                            //Update user credentials
                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                            $this->UserCredentials->updateAll(
                                array(
                                    'UserCredentials.first_name' => "'".$arr_form_data['first_name']."'",
                                    'UserCredentials.last_name' => "'".$arr_form_data['last_name']."'",
                                    'UserCredentials.middle_name' => "'".$arr_form_data['middile_name']."'",
                                    'UserCredentials.email' => "'".$arr_form_data['email']."'",
                                    'UserCredentials.phone' => "'".$arr_form_data['mobile_no']."'",                                
                                ),
                                array('UserCredentials.emp_fkey' => $pkey)
                            );
                        }
                    }
                    
                    if(isset($arr_form_data['emp_proff_pkey']) && ( $arr_form_data['emp_proff_pkey'] == 0 || $arr_form_data['emp_proff_pkey'] =='' )){
                        //Call procedure 'Linkemp_deviceanddatabase'
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $outputParameter = array();
                        $outputParameter[] = "'".$this->Session->read('company_code')."'"; //company_code
                        $outputParameter[] = isset($arr_form_data['emp_branch']) ? "'".$arr_form_data['emp_branch']."'" :"''";
                        $outputParameter[] = "''";
                        $out = $this->UserCredentials->linkempDeviceanddatabase($outputParameter);
                    }
                    
                    return json_encode(array('success'=>TRUE,'pkey'=>$pkey,'message'=>$message));
        }
    }
    
        
}