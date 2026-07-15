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

class AdvanceController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'Advance';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl','UserCredentials','Advance','EmployeeDetails','Designation','EmployeeProfessionalDetails', 'Departments', 'Units');
    public $components = array('MasterdataManagement');

    public function form() {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $this->Advance->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query("select EmployeeDetails.emp_pkey,CONCAT(first_name,' ', ifnull(last_name,'')) as name,"
                . "emp_proff.emp_company_id from emp_details as EmployeeDetails inner join emp_proff on (emp_proff.emp_fkey = EmployeeDetails.emp_pkey) "
                . "where EmployeeDetails.status= '1' order by first_name ASC");
        $this->set("arr_employees", $arr_employees);
        
       // $this->set("arr_advance", $arr_advance = $this->EmployeeDetails->query("select advance_pkey,emp_fkey,advance_date,account_fkey,amount,remarks from advance_expense where status=1"));

        $this->set("arr_bank", $arr_bank  = $this->EmployeeDetails->query("select emp_banks_pkey,name from emp_banks Where status =1 limit 10"));
         if (isset($_REQUEST['advance_pkey']) && $_REQUEST['advance_pkey'] != 0) {
            $data_db = $this->Advance->find("first", array("conditions" => array("advance_pkey" => $_REQUEST['advance_pkey'])));
            $data = $data_db['Advance'];
          $this->set("data", $data);  
        }
        $this->layout = null;
    }
    
//save 
    public function advancesave() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->Advance->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $pkey = $arr_form_data["advance_pkey"];
        if ($arr_form_data["advance_pkey"] != "") {
            $arr_form_datas["modification_date"] = "'".date('Y-m-d h:i:s')."'"; 
            $arr_form_datas["modified_by"] = "'".$this->Session->read('login_user_id')."'"; 
            $arr_form_datas["account_fkey"] = $arr_form_data["account_fkey"]; 
            $arr_form_datas["emp_fkey"] = $arr_form_data["emp_fkey"]; 
            $arr_form_datas["remarks"] = "'".$arr_form_data["remarks"]."'";
            $arr_form_datas["advance_date"] = "'".$arr_form_data["advance_date"]."'"; 
            $arr_form_datas["amount"] = $arr_form_data["amount"];    
                   $this->Advance->updateAll($arr_form_datas, array('advance_pkey' => $pkey));
                   
                   $resp = array();
                   $resp["success"] = true;
                   $resp["msg"] = "Updated Successfully!";
                   echo json_encode($resp);
        } else {
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');
            $arr_form_data["creation_date"] = date('Y-m-d h:i:s');
                    $result = $this->Advance->save($arr_form_data);
                    $resp = array();
                    $resp["success"] = true;
                    $resp["msg"] = "Saved successfully";
                    echo json_encode($resp);
        }
    }

//list            
    public function advancelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp = isset($arr_request_data['emp']) ? $arr_request_data['emp'] : '';
        $this->Advance->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $conditions = "";
        if ($emp != '') {
            $conditions .= " and emp_fkey=" . $emp;
        }
        $counts = $this->Advance->query("SELECT COUNT(*) FROM advance_expense ae left join emp_details on (emp_details.emp_pkey=ae.emp_fkey)
                                         left join emp_banks on (emp_banks.emp_banks_pkey=ae.account_fkey) 
                                         where ae.status=1 and emp_details.status=1 and emp_banks.status=1 $conditions ");

        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->Advance->query("SELECT ae.*,CONCAT(emp_details.first_name,' ', ifnull(emp_details.last_name,'')) as empname,emp_banks.name FROM advance_expense ae left join emp_details on (emp_details.emp_pkey=ae.emp_fkey)
                                         left join emp_banks on (emp_banks.emp_banks_pkey=ae.account_fkey) 
                                         where ae.status=1 and emp_details.status=1 and emp_banks.status=1 $conditions ORDER BY creation_date desc"
                                         . " limit $limit  offset $ofst ");
 
        $out = array();  
        foreach ($arr_att as $key => $value) {
            
            $out['advance_pkey'] = isset($value["ae"]["advance_pkey"]) ? $value["ae"]["advance_pkey"] : '';
            $out['account_fkey'] = isset($value["ae"]["account_fkey"]) ? $value["ae"]["account_fkey"] : '';
            $out['emp_name'] = isset($value["0"]["empname"]) ? $value["0"]["empname"]: '';
            $out['emp_fkey'] = isset($value["ae"]["emp_fkey"]) ? $value["ae"]["emp_fkey"] : '';
            $out['advance_date'] = isset($value["ae"]["advance_date"]) ? date('d-m-Y',strtotime($value["ae"]["advance_date"])) : '';
            $out['amount'] = isset($value["ae"]["amount"]) ? $value["ae"]["amount"] : '';
            $out['remarks'] = isset($value["ae"]["remarks"]) ? $value["ae"]["remarks"] : '';
            
              if($out['account_fkey'] == 1){ 
            $out['bank_name'] = 'Bank Accounts';
            }else if($out['account_fkey'] == 2){
            $out['bank_name'] = 'Cash';   
            }else if($out['account_fkey'] == 3){
            $out['bank_name'] = 'Credit Card';   
            }else if($out['account_fkey'] == 4){
            $out['bank_name'] = 'Debit Card';   
            }else if($out['account_fkey'] == 5){
            $out['bank_name'] = 'Mobile Payments';   
            }else if($out['account_fkey'] == 6){ 
            $out['bank_name'] = 'ANOOP PC';
            }else if($out['account_fkey'] == 7){
            $out['bank_name'] = 'RAFEEQ PC';   
            }else if($out['account_fkey'] == 8){
            $out['bank_name'] = 'MUSFEER PC';   
            }else if($out['account_fkey'] == 9){
            $out['bank_name'] = 'MUNEEB PC';   
            }else if($out['account_fkey'] == 10){
            $out['bank_name'] = 'PRABEESH PC';   
            }else if($out['account_fkey'] == 11){ 
            $out['bank_name'] = 'ALLES HAPPAY';
            }else if($out['account_fkey'] == 12){
            $out['bank_name'] = 'JAC HAPPAY';   
            }else if($out['account_fkey'] == 13){
            $out['bank_name'] = 'ZB CARD';   
            }else{
            $out['bank_name'] = 'Other Payments';      
            }
//            $out['ending_km'] = isset($value["te"]["ending_km"]) ? $value["te"]["ending_km"] : '';
//            $out['starting_km'] = isset($value["te"]["starting_km"]) ? $value["te"]["starting_km"] : '';
//            $out['creation_date'] = isset($value["te"]["creation_date"]) ? $value["te"]["creation_date"] : '';
//            $out['created_by'] = isset($value["te"]["created_by"]) ? $value["te"]["created_by"] : '';
//            $out['modification_date'] = isset($value["te"]["modification_date"]) ? $value["te"]["modification_date"] : '';
//            $out['modified_by'] = isset($value["te"]["modified_by"]) ? $value["te"]["modified_by"] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    public function Advance() {
        $this->Advance->useDbConfig = $this->Session->read('ds');
        $arr_advance = $this->Advance->query("select advance_pkey,emp_fkey,advance_date,account_fkey,amount,remarks "
                . "from advance_expense where status=1");
        $this->set("arr_advance",$arr_advance);
        
        $arr_employees = $this->Advance->query("select EmployeeDetails.emp_pkey,CONCAT(first_name,' ', ifnull(last_name,'')) as name,"
                . "emp_proff.emp_company_id from emp_details as EmployeeDetails inner join emp_proff on (emp_proff.emp_fkey = EmployeeDetails.emp_pkey) "
                . "where EmployeeDetails.status= '1' order by first_name ASC");
        $this->set("arr_employees", $arr_employees);
    }

//delete
    public function deleteEmployee() {
        $this->autoRender = FALSE;
        $this->Advance->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["ids"]);
            //debug($ar_ids);
            $this->Advance->updateAll(
                    array('status' => 0), array('advance_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s) deleted successfully.";
        }
        echo json_encode($result);
    }

}
