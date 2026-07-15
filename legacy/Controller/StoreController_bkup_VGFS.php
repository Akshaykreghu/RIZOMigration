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
class StoreController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'Store';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Item', 'QuantityDetails', 'AdditionalDetails', 'StockAdjustment', 'StockAdjustmentDetails', 'EmployeeDetails', 'ItemDetails', 'WarrantyDetails', 'Store');

    public function index() {
        
    }

    
    public function adj(){
        
    }

//popup for save and update
    public function form($store_pkey = 0) {
        // debug($store_pkey);
        $arr_request_data = $this->request->data;
        //debug($arr_request_data);
        if ($store_pkey) {
            $action = "Edit Store";
        } else {
            $action = "Create Store";
        }
        $this->Store->useDbConfig = $this->Session->read('ds');
        $arr_att = $this->Store->query("SELECT *FROM `store_master` WHERE store_master_pkey='$store_pkey'");
        $this->layout = null;
        $this->set("action", $action);
        $this->set("arr_att", $arr_att);
    }
    
    public function save_stock_transfer(){
        $this->autoRender = false;
        $this->layout = null;
        $arr_request_data = $this->request->data;
        $this->StockAdjustmentDetails->useDbConfig = $this->Session->read('ds');
        $this->StockAdjustment->useDbConfig = $this->Session->read('ds');
        $from_code = $arr_request_data['from_store'];
//        $to_store = $arr_request_data['to_store'];
        $qty_love = $arr_request_data['required_qty']*-1;
        $arr_request_data['created_by'] = $this->Session->read('login_user_id');
        $item_pkey = $arr_request_data['item_code'];
        $item_key = $this->finditembycode($item_pkey);
        $date_created = $arr_request_data['adjustment_date'];
//        debug($arr_request_data);
        
        $this->StockAdjustment->save($arr_request_data);
        $itemkey = $this->StockAdjustment->getInsertid();
//        debug($itemkey);
        $arr_request_data['created_by'] = $created_by = $this->Session->read('login_user_id');
        $created_date = date("Y-m-d");
//        $getlastid = isset($arr_request_data['stock_tranfer_pkey']) && !empty($arr_request_data['stock_tranfer_pkey']) ? $arr_request_data['stock_tranfer_pkey'] : $this->StockTranfer->getInsertid();

        $arr_request_data['stock_adjustments_fkey'] = $itemkey;
        $arr_request_data['adj_qty'] = $arr_request_data['required_qty'];
        $arr_request_data['item_fkey'] = $item_key;

        $this->StockAdjustmentDetails->saveAll($arr_request_data);
        $this->StockAdjustment->query(" INSERT INTO `stock_details` (`store_fkey`, `supplier_fkey`, `invoice_no`, `mr_no`, `po_no`, `item_batch`, `item_fkey`, `received_qty`, `item_qty`, `free_stock`, `offer_stock`, `purchase_rate`, `amount`, `item_mrp`, `varified_by`, `item_state`, `created_by`, `creation_date`, `modified_by`, `modified_date`, `status`) VALUES
($from_code,	0,	NULL,	0,	0,	NULL,	$item_key,	0,$qty_love,NULL,	NULL,	NULL,	0,	NULL,	'$created_by',	'ADJUSTMENT',	'$created_by',	'$created_date',	NULL,	NULL,	1); ");
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $itemkey));
    }
    
    public function stockadjlist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;

        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->StockAdjustment->useDbConfig = $this->Session->read('ds');
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

//        if ($emp_fkey != '') {
//            $emp_condition = "and au.emp_fkey=$emp_fkey";
//        }
        if ($branch_code != '') {
            $branch_condition = "and ed.branch_code='$branch_code'";
        }
        $counts = $this->StockAdjustment->query("SELECT COUNT(*) FROM stock_adjustments_details join stock_adjustments on (stock_adjustments.stock_adjustments_pkey = stock_adjustments_details.stock_adjustments_fkey) join store_master on (store_master.store_master_pkey = stock_adjustments.from_store) where stock_adjustments_details.status = '1' 
                            $emp_condition $branch_condition ");
        $count = $counts[0][0]['COUNT(*)'];
        //debug($emp_condition);
        $arr_att = $this->StockAdjustment->query("SELECT *,store_master.store_location FROM stock_adjustments_details join stock_adjustments on (stock_adjustments.stock_adjustments_pkey = stock_adjustments_details.stock_adjustments_fkey) join store_master on (store_master.store_master_pkey = stock_adjustments.from_store) where stock_adjustments_details.status = '1' 
                            $emp_condition $branch_condition "
                . " ORDER BY created_date desc"
                . " limit $limit  offset $ofst  ");
//        debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {

            $out['store'] = isset($value['store_master']['store_location']) ? $value['store_master']['store_location'] : '';
            $out['item'] = isset($value['stock_adjustments_details']['item_desc']) ? $value['stock_adjustments_details']['item_desc'] : '';
            $out['qty'] = isset($value['stock_adjustments_details']['adj_qty']) ? $value['stock_adjustments_details']['adj_qty'] : '';
            $out['dates'] = isset($value['stock_adjustments']['adjustment_date']) ? $value['stock_adjustments']['adjustment_date'] : '';
            $out['remarks'] = isset($value['stock_adjustments']['remarks']) ? $value['stock_adjustments']['remarks'] : '';
            $resp_att["rows"][$key] = $out;
            //debug($getmonth);
            //debug($date);

        }


        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    public function finditembycode($item_code = ""){
        $this->StockAdjustment->useDbConfig = $this->Session->read('ds');
        $item_master_pkey = $this->StockAdjustment->query("select item_master_pkey from item_master where item_code = '$item_code' ");
        return $item_master_pkey['0']['item_master']['item_master_pkey'];
    }

    public function Storedata($store_pkey = 0) {

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
        $this->set("store_pkey", $store_pkey);
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
        $arr_employees_allocates = $this->EmployeeDetails->query(" select emp_pkey,emp_details.first_name,last_name from access_store join emp_details on (emp_details.emp_pkey = access_store.emp_fkey) where store_pkey = '$store_pkey' and access_store.status = '1' and emp_pkey != access_store.emp_fkey ");
        $this->set("arr_employees_allocates", $arr_employees_allocates);
//            debug($arr_employees_allocates);
    }
    
    public function storefilter(){
        $this->autoRender = false;
        $this->Store->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and store_location like '%$q%'";
        } else {
            $q_condition = "";
        }
        $arr_site = $this->Store->query("select * from store_master where status= '1' $q_condition ");
        $array = array();
        $stores[] = array("id" => "0", "text" => "ALL");
        foreach ($arr_site as $key => $value) {
            $stores[] = array(
                'id' => $value['store_master']['store_master_pkey'],
                'text' => $value['store_master']['store_location'] 
            );
        }
        $array['items'] = $stores;
//        debug($site);
        echo json_encode($array);
    }

//data grid list            
    public function Storelist() {

        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->Store->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        
        $storekey = isset($arr_request_data['item']) ? $arr_request_data['item'] : '0';
                     if($storekey == 0 ){
                             $cond = "";
                     }
                     else {
                          $cond = " and store_master_pkey = $storekey ";
                     }
        
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'store_code';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $count = $this->Store->find("count", array("conditions" => array('status' => 1)));
        $arr_att = $this->Store->query("SELECT *FROM `store_master` WHERE status='1' $cond order by $sort $order limit $limit offset $ofst ; ");
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['store_master_pkey'] = isset($value['store_master']['store_master_pkey']) ? $value['store_master']['store_master_pkey'] : '';
            $out['store_code'] = isset($value['store_master']['store_code']) ? $value['store_master']['store_code'] : '';
            $out['store_location'] = isset($value['store_master']['store_location']) ? $value['store_master']['store_location'] : '';
            $out['address'] = isset($value['store_master']['address']) ? $value['store_master']['address'] : '';
            $out['city'] = isset($value['store_master']['city']) ? $value['store_master']['city'] : '';
            $out['state'] = isset($value['store_master']['state']) ? $value['store_master']['state'] : '';
            $out['pincode'] = isset($value['store_master']['pincode']) ? $value['store_master']['pincode'] : '';
            $out['created_by'] = isset($value['store_master']['created_by']) ? $value['store_master']['created_by'] : '';
            $out['creation_date'] = isset($value['store_master']['creation_date']) ? $value['store_master']['creation_date'] : '';
            $out['modified_by'] = isset($value['store_master']['modified_by']) ? $value['store_master']['modified_by'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    public function save_allocate() {
        $this->autoRender = FALSE;
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        $emps = $arr_form_data['emps'];
        $store_code = $arr_form_data['store_code'];
        $resp_att = array();
        try {

            $this->Store->query(" insert into access_store(store_pkey,emp_fkey) values('$store_code','$emps') ");
            $resp_att['success'] = 1;
            $resp_att['msg'] = "Saved Successfully ";
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }

    public function remove_allocate() {
        $this->autoRender = FALSE;
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        $emps = $arr_form_data['emps'];
        $store_code = $arr_form_data['store_code'];
        $resp_att = array();
        try {

            $this->Store->query(" update access_store set status = '0' where store_pkey = '$store_code'  and emp_fkey  = '$emps' ");
            $resp_att['success'] = 1;
            $resp_att['msg'] = "Removed Successfully ";
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }

//delete
    public function Storedelete() {
        $this->autoRender = FALSE;

        $this->Store->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["store_master_pkey"])) {
            $ar_ids = explode(",", $_REQUEST["store_master_pkey"]);
            $this->Store->updateAll(
                    array('Store.status' => 0), array('Store.store_master_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

    public function save() {
        $this->autoRender = FALSE;
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        if ($arr_form_data['store_master_pkey'] != '') {
            $arr_form_data['modified_by'] = $this->Session->read('user_name');
        }
        $this->Store->save($arr_form_data);
    }

}
