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
class CompanyProfileReportController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'CompanyProfileReport';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'Units', 'ComplianceInfo', 'PolicyInfo','Departments','Designation','Banks','ReportAudit');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index() {

        $this->UserCredentials->useDbConfig = $this->Session->read('ds');

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
    }
    public function addcriterias(){
        
    }
	 public function reportAudit($company, $branch, $department, $designation, $bank) {
//        $this->autoRender = false;

        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $dataForHistory = array();
        $dataForHistory['report_type'] = "Company Profile Report";
        $dataForHistory['mode'] = "View Report";
        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
        
        $items = array();
        if($company == 1){
            $items[] = "Company Details";
        }
        if($branch == 1){
            $items[] = "Branch Details";
        }
        if($department == 1){
            $items[] = "Department Details";
        }
        if($designation == 1){
            $items[] = "Designation Details";
        }
        if($bank == 1){
            $items[] = "Bank Details";
        }
        if(count($items)>0){
            $dataForHistory['items'] = implode(",", $items);
            $this->ReportAudit->save($dataForHistory);
        }
    }
    public function viewreport($company = '',$branch = '', $department = '',$designation = '', $bank = ''){
        
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this->Departments->useDbConfig = $this->Session->read('ds');
        $this->Designation->useDbConfig = $this->Session->read('ds');
        $this->ComplianceInfo->useDbConfig = $this->Session->read('ds');
        $this->Banks->useDbConfig = $this->Session->read('ds');
//        if($company == 1){
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $arr_comp_complaince_info = $this->ComplianceInfo->find('first');
//            debug($arr_comp_complaince_info);
            $this->set("contactinfo", $arr_comp_contact_info['CompanyContactInfo']);
             $this->set("arr_comp_complaince_info", $arr_comp_complaince_info);
            $this->set("company", $company);
//        }
//        else{
//            $this->set("company", $company);
//        }
        if($branch == 1){
//            $resp_branches = array();
                $table_joins[] = array(
                    'table' => 'fin_year',
                    'alias' => 'Leave_year',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('Units.branch_code = Leave_year.branch_code','Leave_year.vattr1'=>'0','Leave_year.is_current_finyear'=>'Y','Leave_year.Year_status'=>'OPEN','Leave_year.status'=>1)
                );
                $table_joins[] = array(
                    'table' => 'fin_year',
                    'alias' => 'Fin_year',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('Units.branch_code = Fin_year.branch_code' ,'Fin_year.vattr1'=>'1','Fin_year.is_current_finyear'=>'Y','Fin_year.Year_status'=>'OPEN','Fin_year.status'=>1)
                );
//		$resp_branches["rows"]= array();
//		$count = $this -> Units -> find("count",array("conditions"=>array('status' => 1)));
		$arr_branches = $this -> Units -> find("all",array(
                    "fields"=>array("Units.*,Leave_year.start_month as LeaveStart,Leave_year.end_month as LeaveEnd,Fin_year.start_month FinStart,Fin_year.end_month FinEnd "),
                    'joins'=>$table_joins,
                    "conditions"=>array(
                        'Units.status' => 1
                    ),
                   
                    )
                );
                $this->set("arr_branches", $arr_branches);
                $this->set("branch", $branch);
                
        }else{
             $this->set("branch", $branch);
        }
        if($department == 1){
            $arr_dept = $this->Departments->find("all", array(
                    "conditions" => array(
                        "status" => 1
                    )
                 )
            );
            $this->set("arr_dept", $arr_dept);
            $this->set("department", $department);
        }
        else{
             $this->set("department", $department);
        }
        if($designation == 1){
            $arr_desig = $this->Designation->find("all", array(
                    "conditions" => array(
                        "status" => 1
                    ),
                    
                )
            );

            $this->set("arr_desig", $arr_desig);
            $this->set("designation", $designation);
        }else{
            $this->set("designation", $designation);
        }
        if($bank == 1){
            $arr_banks =  $this -> Banks ->find("all",array(
                        "conditions"=>array(
                            "status"=> 1
                        ),
                        
                    )
                );
             $this->set("arr_banks", $arr_banks);
             $this->set("bank", $bank);
        }
        else{
            $this->set("bank", $bank);
        }
        $this->reportAudit($company, $branch, $department, $designation, $bank);
    }
    

}
