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
class UniformController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'Uniform';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'item_master','EmployeeLoanInfo', 'EmployeeLoan' , 'Event_receiver', 'Units', 'item_purchase', 'item_allocate', 'allocate_details', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index() {
        
    }
    public function master(){
        
    }
    public function employeelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';

        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey=$emp";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $branch_condition = "and ed.branch_code='$branch_code'";
        }
        $counts = $this->item_master->query("select count(*) count from item where status = '1'  ");

        $count = $counts[0][0]['count'];
        $arr_att = $this->item_master->find("all",array("conditions"=>array("status"=>1)));

        // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['item_pkey'] = isset($value['item_master']['item_pkey']) ? $value['item_master']['item_pkey'] : '';
            $out['item_name'] = isset($value['item_master']['item_name']) ? $value['item_master']['item_name'] : '';
            $out['item_code'] = isset($value['item_master']['item_code']) ? $value['item_master']['item_code'] : '';
            $out['item_desc'] = isset($value['item_master']['item_desc']) ? $value['item_master']['item_desc'] : '';
            $out['status'] = isset($value['item_master']['status']) ? $value['item_master']['status'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    public function lists_purchase() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';

        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey=$emp";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $branch_condition = "branch_code = '$branch_code' ";
        }
        $counts = $this->item_purchase->query("select count(*) count from item_purchase where status = '1'  ");

        $count = $counts[0][0]['count'];
        $arr_att = $this->item_purchase->find("all",array("fields"=>array("item_purchase.*,item.*"),"joins"=>array(array("table"=>"item","alias"=>"item","type"=>"inner","conditions"=>array("item.item_pkey = item_purchase.item_fkey"))),"conditions"=>array("item_purchase.status"=>1,$branch_condition)));

        // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['item_pkey'] = isset($value['item_purchase']['item_pkey']) ? $value['item_purchase']['item_pkey'] : '';
            $out['name'] = isset($value['item']['item_name']) ? $value['item']['item_name'] : '';
            $out['vendor'] = isset($value['item_purchase']['vendor']) ? $value['item_purchase']['vendor'] : '';
            $out['qty'] = isset($value['item_purchase']['qty']) ? $value['item_purchase']['qty'] : '';
            $out['date_purchased'] = isset($value['item_purchase']['date_purchased']) ? $value['item_purchase']['date_purchased'] : '';
            $out['PO_number'] = isset($value['item_purchase']['PO_number']) ? $value['item_purchase']['PO_number'] : '';
            $out['branch_code'] = isset($value['item_purchase']['branch_code']) ? $value['item_purchase']['branch_code'] : '';
            $out['status'] = isset($value['item_purchase']['status']) ? $value['item_purchase']['status'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    public function lists() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';
        $conditions = array("item.emp_pkey = item_allocate.emp_fkey");
        
        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $conditions[] = "item.emp_pkey = $emp ";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $conditions[] = "item.branch_code = '$branch_code' ";
        }
        $counts = $this->item_allocate->query("select count(*) count from itm_allocation where status = '1' and allocation_pkey in (select allocate_fkey from allocate_details where (qty - returned_qty - damaged_qty) > 0)  ");

        $count = $counts[0][0]['count'];
        $arr_att = $this->item_allocate->find("all",array("fields"=>array("item_allocate.*,item.*,proff.emp_company_id"),"joins"=>array(array("table"=>"emp_details","alias"=>"item","type"=>"inner","conditions"=>$conditions),array("table"=>"emp_proff","alias"=>"proff","type"=>"inner","conditions"=>array("item.emp_pkey = proff.emp_fkey"))),
            "conditions"=>array("item_allocate.status"=>1,"allocation_pkey in (select allocate_fkey from allocate_details where (qty - returned_qty - damaged_qty) > 0)"),
            'offset'=>$ofst,
            'limit' => intval($limit)));
        // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            $loan_fkey = isset($value['item_allocate']['loan_fkey']) ? $value['item_allocate']['loan_fkey'] : '';
            $loan = $this->item_allocate->query("select * from emp_loan_info where emp_loan_info_pkey in (select min(emp_loan_info_pkey) from emp_loan_info where loan_pkey = $loan_fkey and amount_paid = 0 and status =1) and status =1");
            $allocated_pkey = isset($value['item_allocate']['allocation_pkey']) ? $value['item_allocate']['allocation_pkey'] : '';
              
            $out['item_pkey'] = isset($value['item_allocate']['allocation_pkey']) ? $value['item_allocate']['allocation_pkey'] : '';
            $out['name'] = isset($value['item']['first_name']) ? $value['item']['first_name'].' '.$value['item']['last_name'].' '.$value['proff']['emp_company_id'] : '' ;
            $out['value'] = isset($value['item_allocate']['value']) ? $value['item_allocate']['value'] : '';
            $out['date_allocated'] = isset($value['item_allocate']['date_allocated']) ? $value['item_allocate']['date_allocated'] : '';
            $out['balance_recover_amt'] = isset($loan['0']['emp_loan_info']['opening_balance']) ? $loan['0']['emp_loan_info']['opening_balance'] : '';
            $out['loan_fkey'] = isset($value['item_allocate']['loan_fkey']) ? $value['item_allocate']['loan_fkey'] : '';
            $out['status'] = isset($value['item_purchase']['status']) ? $value['item_purchase']['status'] : '';
            $resp_att["rows"][$key] = $out;
            
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    public function returns($allocate_pkey = 0){
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $item_details = $this->item_master->query(" SELECT ad.*,im.item_desc,im.item_master_pkey FROM `allocate_details` ad 
            join item_master im on (im.item_master_pkey = ad.item_purchase_fkey)
            where ad.qty > ad.returned_qty and ad.status = '1'  and allocate_fkey = $allocate_pkey ");
//        debug($item_details);
        $this->set("item_details",$item_details); 
        $allocate_master = $this->item_master->query(" select * from itm_allocation where allocation_pkey = $allocate_pkey ");
        //debug($item_details);
        $this->set("allocate_master",$allocate_master); 
        $loan_pkeys = $allocate_master['0']['itm_allocation']['loan_fkey'];
        $loan_master = $this->item_master->query(" select * from emp_loan where emp_loan_pkey = $loan_pkeys ");
        $loan_status = $loan_master['0']['emp_loan']['is_completed'];
        $arr_loans_pendig = $this->item_master->query(" SELECT SUM(amt) as amts FROM emi_upload WHERE loan_pkey = '$loan_pkeys' and status = '1'  ");
        $loan_amt = $allocate_master['0']['itm_allocation']['value'];
        $emi_paidd = $arr_loans_pendig['0']['0']['amts'];
        $bal_amt = $loan_amt - $emi_paidd;
        if($loan_status =='Y'){
            $bal_amt = 0;
        }
        $this->set("bal_amt",$bal_amt); 
        $this->render('return');
        
    }
    
    public function return_item(){
        $this->autoRender = false;
        $this->layout = null;
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $qty = $arr_form_data['qty'];
        $allocate_pkey = $arr_form_data['pkey'];
        $emp_fkey = $arr_form_data['emp_fkey'];
        $item_fkey = $arr_form_data['item_fkey'];
        $damaged_qty = isset($arr_form_data['damaged_qty'])?$arr_form_data['damaged_qty']:0;
        
        try{

            $item_details = $this->item_master->query(" update allocate_details set returned_qty = returned_qty+$qty,damaged_qty = damaged_qty+$damaged_qty where allocate_details_pkey = '$allocate_pkey' ");
            $item_details = $this->item_master->query(" insert into returned_stocks(allocation_fkey,item_fkey,qty,emp_pkey,returned_stocks) values('$allocate_pkey','$item_fkey','$qty','$emp_fkey','$damaged_qty') ");

            return 'Returned Successfully';
            
        } catch (Exception $ex) {
            return $ex->getMessage();
        } catch (mysqli_sql_exception $ex){
            return $ex->getMessage();
        }
    }

    public function form($item_pkey = 0) {
        $this->item_master->useDbConfig = $this->Session->read('ds');
        //debug($item_pkey);
        $item_details = "";
        if (isset($item_pkey) && $item_pkey != 0) {
            $item_details = $this->item_master->query(" select * from item where item_pkey = '$item_pkey' ");
        }
        //debug($item_details);
        $this->set("item_details",$item_details);
    }
    
    public function Master_save(){
        $this->autoRender = false;
        $this->layout = null;
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $result = $this->item_master->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Uniform Added successfully";
        echo json_encode($resp);
    }
    
    public function deleteEmp() {
        $this->autoRender = FALSE;
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($ar_ids);
        $ar_ids = $_REQUEST["ids"];
        $this->item_master->updateAll(
                array('item_master.status' => 0), array('item_master.item_pkey' => $ar_ids));
        $result['success'] = 1;
        $result['msg'] = "Record(s)  deleted successfully.";

        echo json_encode($result);
    }
    
    public function load_qty($store_fkey = 0){
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $arr_att = $this->item_purchase->query("select item_master.item_master_pkey,item_master.item_code,item_master.item_desc,
        (select ifnull(sum(item_qty),0) from stock_details where item_fkey = item_master.item_master_pkey and status = 1 and store_fkey = '$store_fkey') as qty,
        (select ifnull(sum(qty-returned_qty),0) from allocate_details where item_purchase_fkey = item_master.item_master_pkey and store_code = '$store_fkey' and status = 1) as allocated 
        from item_master where status = 1 and item_master_pkey in (select item_fkey from stock_details where item_qty > 0 and store_fkey = '$store_fkey' ) ");
        //debug($arr_att);
        $this->set("arr_att",$arr_att);                                      
    }

    public function form_purchase($item_pkey = 0){
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        //debug($item_pkey);
        $item_details = "";
        $arr_att = $this->item_master->find("all",array("conditions"=>array("status"=>1)));
        $arr_branchs = $this->Units->find("all",array("conditions"=>array("status"=>1)));
        if (isset($item_pkey) && $item_pkey != 0) {
            $item_details = $this->item_master->query(" select * from item_purchase where item_pkey = '$item_pkey' ");
        }
        //debug($arr_branchs);
        $this->set("arr_att",$arr_att);
        $this->set("arr_branchs",$arr_branchs);
        $this->set("item_details",$item_details);
    }
    
    public function purchase_file(){
        $this->Units->useDbConfig = $this->Session->read('ds');
        $arr_branchs = $this->Units->find("all",array("conditions"=>array("status"=>1)));
        $this->set("arr_branchs",$arr_branchs);
    }
    
    public function save_Purchase(){
        $this->autoRender = false;
        $this->layout = null;
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $result = $this->item_purchase->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Purchase Added successfully";
        echo json_encode($resp);
    }
    
     public function deleteEmppurchase() {
        $this->autoRender = FALSE;
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($ar_ids);
        $ar_ids = $_REQUEST["ids"];
        $this->item_purchase->updateAll(
                array('item_purchase.status' => 0), array('item_purchase.item_pkey' => $ar_ids));
        $result['success'] = 1;
        $result['msg'] = "Record(s)  deleted successfully.";

        echo json_encode($result);
    }
    
    public function allocate(){
        $this->Units->useDbConfig = $this->Session->read('ds');
        $arr_branchs = $this->Units->find("all",array("conditions"=>array("status"=>1)));
        $this->set("arr_branchs",$arr_branchs);
    }
    
    public function allocate_form(){
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $emp_arr = $this->item_purchase->query("select emp_details.*,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = '1' ");
        $this->set("emp_arr",$emp_arr);
        if($this->Session->read('user_group') == 2){
            $emp_fkey = $this->Session->read('emp_fkey');
            $conditions = "and store_master_pkey in(select store_pkey from access_store where emp_fkey = '$emp_fkey')";
            
        }else{
            $conditions = "";
        }
        $store_arr = $this->item_purchase->query("select * from store_master where status = '1'  $conditions");
        $this->set("store_arr",$store_arr);
    }
    
    public function getval($item_fkey = 0){
        $this->autoRender = FALSE;
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $get_details = $this->item_purchase->query("SELECT `sales_price` FROM `additional_details` WHERE `item_master_fkey` = '$item_fkey' AND `status` = '1' LIMIT 50");
        echo isset($get_details['0']['additional_details']['sales_price'])?$get_details['0']['additional_details']['sales_price']:0;
    }
    
    public function getendmonth(){
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $next = strtotime($arr_form_data['start_month']);
        $tenure = $arr_form_data['tenure'];
        $starts = date("Y-m",$next);
        $months = date("Y-m",strtotime("+$tenure month",$next));
        echo $months;
    }

    public function allocate_emp(){
        
        
        $this->autoRender = false;
        $this->layout = null;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $this->allocate_details->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        
        
        
       
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        $count = $arr_form_data['tenure'] = $arr_form_data['tenures'];
        $loan_amt = $arr_form_data['loan_amount'] = $arr_form_data['value'];
        $emp_pkey = $arr_form_data['emp_fkey'];
        $interest = $arr_form_data['intrest_rate'] = '0';
        
        $emi = $arr_form_data['emiss'];
        $next = strtotime($arr_form_data['emi_start_date']);
        $arr_form_data['emi_amount'] = $emi;
        $arr_form_data['emi_start_month'] = date("Y-m",$next);
        $months = $arr_form_data['emi_end_month'] = date("Y-m",strtotime("+$count month",$next));
        $closing_balance = $loan_amt;
        $result = $this->EmployeeLoan->save($arr_form_data);
        $loan_pkey = $this->EmployeeLoan->getLastInsertId();
        $arr_loan_data = array();
        for($i=0;$i<$count;$i++){
            $opening_balance = $closing_balance;
            $interests = $opening_balance*$interest/100;
            $interestpaid = $interests*1/12;
            $principal = $emi - $interestpaid;
            $closing_balance = $opening_balance - $principal;
             $arr_loan_data['opening_balance'] = $opening_balance;
             $arr_loan_data['closing_balance'] = $closing_balance;
             $arr_loan_data['principle'] = $principal;
             $arr_loan_data['interest'] = $interestpaid;
             $arr_loan_data['amount_to_paid'] = $emi;
             $arr_loan_data['loan_emi'] = $emi;
             $starts = date("Y-m",strtotime("+1 month",$next));
             $next = strtotime($starts);
             //debug($starts);
            //debug("opening Balance :".$opening_balance.", EMI :".$emi.", Inetrest: ".$interestpaid.", Principal: ".$principal.", Closing Balance: ".$closing_balance);
            $result = $this->EmployeeLoanInfo->query("insert into emp_loan_info(opening_balance,emp_fkey,loan_pkey,loan_month,closing_balance,principle,interest,amount_to_paid,loan_emi)"
                    . "values('$opening_balance','$emp_pkey','$loan_pkey','$starts', '$closing_balance','$principal','$interestpaid','$emi','$emi')");
        }
        $arr_form_data['loan_fkey'] = $loan_pkey;
        $result = $this->item_allocate->save($arr_form_data);
        $allocate_ids = $this->item_allocate->getLastInsertId();
        $allocate_arr = array();
        $allocate_arr = $arr_form_data['item_name'];
        $allocate_qty = $arr_form_data['item_qty'];
        //debug($allocate_arr);
        $counts = count($allocate_arr);
        $arr_form_data1 = array();
        $arr_form_data1['allocate_fkey'] = $allocate_ids;
        $arr_form_data1['store_code'] = $arr_form_data['store_fkey'];
        
        for($i=0;$i<$counts;$i++){
            $item_pkey = substr($allocate_arr[$i], 0, strpos($allocate_arr[$i], '-'));;
            $item_qty = $allocate_qty[$i];
            $arr_form_data1['item_purchase_fkey'] = $item_pkey;
            $arr_form_data1['qty'] = $item_qty;
            $result1 = $this->allocate_details->saveAll($arr_form_data1);
        }
        
        
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Item Allocated successfully";
        echo json_encode($resp);
        
    }


    public function allocate_save(){
         $this->autoRender = false;
        $this->layout = null;
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $this->allocate_details->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $result = $this->item_allocate->save($arr_form_data);
        $allocate_ids = $this->item_allocate->getLastInsertId();
        $allocate_arr = array();
        $allocate_arr = $arr_form_data['item_name'];
        $allocate_qty = $arr_form_data['item_qty'];
        //debug($allocate_arr);
        $counts = count($allocate_arr);
        $arr_form_data1 = array();
        $arr_form_data1['allocate_fkey'] = $allocate_ids;
        
        for($i=0;$i<$counts;$i++){
            $item_pkey = $allocate_arr[$i];
            $item_qty = $allocate_qty[$i];
            $arr_form_data1['item_purchase_fkey'] = $item_pkey;
            $arr_form_data1['qty'] = $item_qty;
            $result1 = $this->allocate_details->saveAll($arr_form_data1);
        }
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Purchase Added successfully";
        echo json_encode($resp);
    }

}
