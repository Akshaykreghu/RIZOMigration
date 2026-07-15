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
    public $uses = array('Item', 'QuantityDetails', 'AdditionalDetails', 'StockAdjustment', 'StockAdjustmentDetails', 'EmployeeDetails', 'ItemDetails', 'WarrantyDetails', 'Store','GoodsReceivedNotes','StockDetails','GrItemDetails','PoReturn','PoReturnRequest');
    public $components = array('MasterdataManagement');
    public function index() {
        
    }
 
    public function adj(){
        
    }

     public function returnitem(){
        $this->Store->useDbConfig = $this->Session->read('ds');
        $arr_stores = $this->MasterdataManagement->getStoreListForCombo();
       // debug($arr_stores);
        $this->set('arr_stores', $arr_stores);
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
     public function getStoreNameList($str = 0)
	{
		$this->controller->Units->useDbConfig = $this->Session->read('ds');
                if($emps != 0){
                    $conditions = array("status = 1 and branch_code in (select distinct(emp_branch) from emp_proff where emp_proff.attr1 = 14)");
                }
                else{
                    $conditions = array("status = 1");
                }
		$arr_branches	=	Set::extract('/Units/.',$this->controller->Units->find('all',array('fields'=>'id,branch_code,branch_name','conditions'=>array($conditions))));
		return $arr_branches;
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
         // $item_pkey = $arr_request_data['item_code'];
        //$item_key = $this->finditembycode($item_pkey);
        $item_key = $arr_request_data['item_desc'];
        $date_created = $arr_request_data['adjustment_date'];

        $item_details = $this->StockAdjustment->query("SELECT `stock_bal_qty_fn`('$date_created','$from_code','$item_key')qty");
        $qty = $item_details['0']['0']['qty'];
        if($qty >= $arr_request_data['required_qty']){
        $this->StockAdjustment->save($arr_request_data);
        $itemkey = $this->StockAdjustment->getInsertid();
$arr_request_data['created_by'] = $created_by = $this->Session->read('login_user_id');
        $created_date = date("Y-m-d");
//        $getlastid = isset($arr_request_data['stock_tranfer_pkey']) && !empty($arr_request_data['stock_tranfer_pkey']) ? $arr_request_data['stock_tranfer_pkey'] : $this->StockTranfer->getInsertid();

        $arr_request_data['stock_adjustments_fkey'] = $itemkey;
        $arr_request_data['adj_qty'] = $arr_request_data['required_qty'];
        $arr_request_data['item_fkey'] = $item_key;

        $this->StockAdjustmentDetails->saveAll($arr_request_data);
        $this->StockAdjustment->query(" INSERT INTO `stock_details` (`store_fkey`, `supplier_fkey`, `invoice_no`, `mr_no`, `po_no`, `item_batch`, `item_fkey`, `received_qty`, `item_qty`, `free_stock`, `offer_stock`, `purchase_rate`, `amount`, `item_mrp`, `varified_by`, `item_state`, `created_by`, `creation_date`, `modified_by`, `modified_date`, `status`) VALUES
        ($from_code,	0, $itemkey,	0,	0,	NULL,	$item_key,	0,$qty_love,NULL,	NULL,	NULL,	0,	NULL,	'$created_by',	'ADJUSTMENT',	'$created_by',	'$date_created',	NULL,	NULL,	1); ");
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $itemkey));
        }else{
           echo json_encode(array('msg' => 'Adding new item failed', 'pk' => 0));  
        }
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
        $counts = $this->StockAdjustment->query("SELECT COUNT(*) FROM stock_adjustments_details 
            join stock_adjustments on (stock_adjustments.stock_adjustments_pkey = stock_adjustments_details.stock_adjustments_fkey) 
            join store_master on (store_master.store_master_pkey = stock_adjustments.from_store) 
            join item_master on (item_master.item_master_pkey = stock_adjustments_details.item_desc) 
            where stock_adjustments_details.status = '1' 
                            $emp_condition $branch_condition ");
        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->StockAdjustment->query("SELECT *,store_master.store_location,item_master.item_desc FROM stock_adjustments_details 
            join stock_adjustments on (stock_adjustments.stock_adjustments_pkey = stock_adjustments_details.stock_adjustments_fkey) 
            join store_master on (store_master.store_master_pkey = stock_adjustments.from_store)
            join item_master on (item_master.item_master_pkey = stock_adjustments_details.item_desc) where stock_adjustments_details.status = '1' 
                            $emp_condition $branch_condition "
                . " ORDER BY created_date desc"
                . " limit $limit  offset $ofst  ");
        $out = array();
        foreach ($arr_att as $key => $value) {

            $out['store'] = isset($value['store_master']['store_location']) ? $value['store_master']['store_location'] : '';
            $out['item'] = isset($value['item_master']['item_desc']) ? $value['item_master']['item_desc'] : '';
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
  //added by sreekanth on 22_5_19 checking duplicate name of store code  and store location
      public function chkcategory()
    {
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE;
        $this->layout = null;
                   
        $arr_form_data = $this->request->data;
       // debug($arr_form_data);
        $store_pkey = isset($arr_form_data['store_master_pkey'])?$arr_form_data['store_master_pkey']:0;
        $arr_form_data['created_by']  =  $this->Session->read('user_name');
        $store_code = $this->Session->read('store_code');
         $store_location = strtoupper($this->Session->read('store_location'));
          
        $store_code = isset($arr_form_data['store_code'])?$arr_form_data['store_code'] :'';
        if($store_code){
                //added by megha store code duplication avoided with status
        $catgory_check = $this->Store->query("SELECT * FROM `store_master` WHERE  store_code = '$store_code' and status ='1' and store_master_pkey != $store_pkey;");
         //debug("SELECT * FROM `store_master` WHERE  store_code = '$store_code' and status ='1' and store_master_pkey != $store_pkey;");
        $msg = "1";
        }
        $store_location = isset($arr_form_data['store_location'])?$arr_form_data['store_location'] :'';
        if($store_location){
            //added by megha store location duplication avoided with status
        $catgory_check = $this->Store->query("SELECT * FROM `store_master` WHERE store_location = '$store_location' and status ='1' and store_master_pkey != $store_pkey;");
         $msg = "2";
        }
        $resp = array();       
        if(!empty($catgory_check)){

          $resp["msg"] = $msg;          

        }
        echo json_encode($resp);               
    }
      //added by sreekanth on 22_5_19 checking duplicate name of store code  and store location
    public function finditembycode($item_code = ""){
        $this->StockAdjustment->useDbConfig = $this->Session->read('ds');
        $item_master_pkey = $this->StockAdjustment->query("select item_master_pkey from item_master where item_code = '$item_code' ");
        return $item_master_pkey['0']['item_master']['item_master_pkey'];
    }

   public function Storedata($store_pkey = 0) {

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
        $this->set("store_pkey", $store_pkey);
        //The below code is to display only unallocated employees to the specified store in employee list. By ***ARUL P DAS on 17/1/2020
        $condition="`emp_pkey` not in (select emp_fkey from access_store where store_pkey=$store_pkey and access_store.status=1)";
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1,$condition)));
        $this->set("arr_employees", $arr_employees);
        //query edited by ***ARUL P DAS on 17/1/2020
        $arr_employees_allocates = $this->EmployeeDetails->query("select distinct emp_pkey,emp_details.first_name,last_name from access_store join emp_details on (emp_details.emp_pkey = access_store.emp_fkey) where store_pkey = '$store_pkey' and access_store.status = '1'");
//        $arr_employees_allocates = $this->EmployeeDetails->query(" select emp_pkey,emp_details.first_name,last_name from access_store join emp_details on (emp_details.emp_pkey = access_store.emp_fkey) where store_pkey = '$store_pkey' and access_store.status = '1' and emp_pkey != access_store.emp_fkey ");
        $this->set("arr_employees_allocates", $arr_employees_allocates);
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
        //edited by megha on 20_5_19 
        //$count = $this->Store->find("count", array("conditions" => array('status' => 1)));
        $count = $this->Store->query("SELECT count(*) as count FROM `store_master` WHERE status='1' $cond");
        //edited by megha on 20_5_19 
        $arr_att = $this->Store->query("SELECT * FROM `store_master` WHERE status='1' $cond order by store_master_pkey desc,$sort $order limit $limit offset $ofst ; ");
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
        //edited by megha on 20_5_19 
        //$resp_att["total"] = $count;
        $resp_att["total"] = $count['0']['0']['count'];
        //edited by megha on 20_5_19 
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
    //items from specific store
    public function findstoreitems($store = ''){
       $this->autoRender = false;
       $this->Item->useDbConfig = $this->Session->read('ds');
       $this->StockDetails->useDbConfig = $this->Session->read('ds');
       $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($store != null) {
            $store_condition = "and stockdetails.store_fkey in ('$store')";
        } else {
            $store_condition = "";
        } 
        if ($q != null) {
            $q_condition = "and itemmaster.item_desc like '%$q%'";
        } else {
            $q_condition = "";
        }
       $arr_stokeitems = $this->StockDetails->query("SELECT distinct stockdetails.item_fkey,itemmaster.*"
                . "from stock_details as stockdetails "
                . "left join item_master as itemmaster on (itemmaster.item_master_pkey = stockdetails.item_fkey) "
                . "where itemmaster.status = 1 and stockdetails.status = 1 $store_condition $q_condition");
       $array = array();
       $stores = array();
       //$stores[] = array("id" => "0", "text" => "-Select-");
        foreach ($arr_stokeitems as $key => $value) {
            $stores[] = array(
                'id' => $value['stockdetails']['item_fkey'],
                'text' => $value['itemmaster']['item_desc'] 
            );
        }
       $array['items'] = $stores;
       echo json_encode($array);
    }
    //goods received - specific item list
    public function findgritems($item = ''){
        //debug($item);
       $this->autoRender = false;
       $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
       $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
       $qu = isset($_REQUEST['qu']) ? $_REQUEST['qu'] : NULL;
        if ($item != null) {
            $item_condition = "and gritemdetails.item_code = ('$item')";
        } else {
            $item_condition = "";
        } 
        if ($qu != null) {
            $q_condition = "and goodsrecevednotes.gr_number like '%$qu%'";
        } else {
            $q_condition = "";
        }
       $arr_gritems = $this->GoodsReceivedNotes->query("SELECT gritemdetails.*,goodsrecevednotes.*"
                . "from goods_receved_notes as goodsrecevednotes "
                . "left join gr_item_details as gritemdetails on (gritemdetails.grn_fkey = goodsrecevednotes.grn_pkey) "
                . "where goodsrecevednotes.status = 1 $item_condition $q_condition");
       $grarray = array();
       $gritems = array();
       //debug($arr_gritems);
      // $stores[] = array("id" => "0", "text" => "-Select-");
        foreach ($arr_gritems as $key => $values) {
            $gritems[] = array(
                'id' => $values['gritemdetails']['gr_item_pkey'],
                'text' => $values['goodsrecevednotes']['gr_number']. ' - ' . $values['gritemdetails']['received_qty'],
                //'grno' => $values['goodsrecevednotes']['gr_number']
            );
        }
    }
    
    //goods received list - from specific store
    public function findgrnlistitems($store = '',$date = ''){
        //debug($date);
       $this->autoRender = false;
       $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
       $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
       $qu = isset($_REQUEST['qu']) ? $_REQUEST['qu'] : NULL;
        if ($store != null) {
            $store_condition = "and goodsrecevednotes.store_code = ('$store') and goodsrecevednotes.gr_date <= ('$date')";
        } else {
            $store_condition = "";
        } 
        if ($qu != null) {
            $q_condition = "and goodsrecevednotes.gr_number like '%$qu%'";
        } else {
            $q_condition = "";
        }
       $arr_gritems = $this->GoodsReceivedNotes->query("SELECT "
              // . "gritemdetails.received_qty,"
              // . "goodsrecevednotes.grn_pkey,"
               . " distinct goodsrecevednotes.gr_number "
                . "from goods_receved_notes as goodsrecevednotes "
                . "left join gr_item_details as gritemdetails on (gritemdetails.grn_fkey = goodsrecevednotes.grn_pkey) "
                . "join purchase_order as po on (po.po_pkey = gritemdetails.po_fkey) "
                . "where goodsrecevednotes.status = 1 and gritemdetails.status = 1 $store_condition $q_condition");
       $grarray = array();
       $gritems = array();
       //debug($arr_gritems);
      // $stores[] = array("id" => "0", "text" => "-Select-");
        foreach ($arr_gritems as $key => $values) {
            $gritems[] = array(
                'id' => $values['goodsrecevednotes']['gr_number'],
                'text' => $values['goodsrecevednotes']['gr_number'],
                //'grno' => $values['goodsrecevednotes']['gr_number']
            );
        }
       $grarray['items'] = $gritems;
       echo json_encode($grarray);
    }
    //created by megha for calling function to select least count of available qty
     public function finditem_qty($store = 0,$grn_fkey = 0,$date_allocate = 0){
         $this->autoRender = FALSE;
         $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
         $avail_qty = $this->GrItemDetails->query("SELECT item_code "
               . " from gr_item_details "
               . " where gr_item_pkey = '$grn_fkey' ");
       
        $item_pkey = $avail_qty['0']['gr_item_details']['item_code'];
       
        $item_details = $this->GrItemDetails->query("SELECT `stock_bal_qty_fn`('$date_allocate','$store','$item_pkey')qty");
        $qty = $item_details['0']['0']['qty'];
        //debug($qty);
        echo json_encode($qty);
    }
   //created by megha  
    //created by megha for calling function to select least count of available qty in stockadjustment
     public function finditem_qtyvalue_po($store = 0,$item_pkey = 0,$date_allocate = 0){
        $this->autoRender = FALSE;
        $this->Item->useDbConfig = $this->Session->read('ds');
        $item_details = $this->Item->query("SELECT `stock_bal_qty_fn`('$date_allocate','$store','$item_pkey')qty");
        $qty = $item_details['0']['0']['qty'];
        //debug($item_details);
        echo json_encode($qty);
     }
     public function finditem_qtyvalue($date_allocate = 0,$store = 0,$item_pkey = 0){
        $this->autoRender = FALSE;
        $this->Item->useDbConfig = $this->Session->read('ds');
        $item_details = $this->Item->query("SELECT `stock_bal_qty_fn`('$date_allocate','$store','$item_pkey')qty");
        $qty = $item_details['0']['0']['qty'];
        //debug($item_details);
        echo json_encode($qty);
    }
 //created by megha  
    //items from specific grn selected
    public function findgrnitemslist($grn = '',$store = ''){
       $this->autoRender = false;
       //debug($store);
       $this->Item->useDbConfig = $this->Session->read('ds');
       $this->StockDetails->useDbConfig = $this->Session->read('ds');
       $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($grn != null) {
            $grn_condition = "and goods_receved_notes.gr_number in ('$grn') and goods_receved_notes.store_code = '$store' ";
        } else {
            $grn_condition = "";
        } 
        if ($q != null) {
            $q_condition = "and itemmaster.item_desc like '%$q%'";
        } else {
            $q_condition = "";
        }
       $arr_stokeitems = $this->StockDetails->query("SELECT  gr_item_details.gr_item_pkey,itemmaster.item_desc,gr_item_details.received_qty "
                . "from gr_item_details  "
               . "left join goods_receved_notes as goods_receved_notes on (goods_receved_notes.grn_pkey = gr_item_details.grn_fkey) "
                . "left join item_master as itemmaster on (itemmaster.item_master_pkey = gr_item_details.item_code) "
                . "where itemmaster.status = 1 and gr_item_details.status = 1 $grn_condition $q_condition");
        //debug($arr_stokeitems);
       $array = array();
       $stores = array();
       //$stores[] = array("id" => "0", "text" => "-Select-");
        foreach ($arr_stokeitems as $key => $value) {
            $stores[] = array(
                'id' => $value['gr_item_details']['gr_item_pkey'],
                'text' => $value['itemmaster']['item_desc']. ' - ' . $value['gr_item_details']['received_qty'], 
            );
        }
        //debug($stores);
       $array['items'] = $stores;
       echo json_encode($array);
    }
    //item_name 
  public function getautocompletionsitem_desc() {
        $this->autoRender = false;
        if (isset($_REQUEST['item_desc']) && !empty($_REQUEST['item_desc'])) {
            $searchkey = $_REQUEST['item_desc'];
            
            $store_code = isset($_REQUEST['store'])?$_REQUEST['store']:'';
            
            if($store_code != ''){
                $store_fkey_condoition = " and store_fkey = '$store_code'";
                $store_code_condoition = " and store_code = '$store_code'";
            }else{
                $store_condoition = "";
                $store_code_condoition = "";
            }
            
            $filter_condition = 'item_master.item_desc LIKE "%' . $searchkey . '%"';
            $this->Item->useDbConfig = $this->Session->read('ds');
            $arr_item = $this->Item->query("select item_master.item_master_pkey,item_master.item_code,item_master.item_desc,
        (select ifnull(sum(item_qty) - (select ifnull(sum(qty-returned_qty),0) from allocate_details where item_purchase_fkey = item_master.item_master_pkey $store_code_condoition and status = 1) ,0) from stock_details where item_fkey = item_master.item_master_pkey and status = 1 $store_fkey_condoition) as qty
        from item_master where status = 1 and item_master_pkey in (select item_fkey from stock_details where item_qty > 0 $store_fkey_condoition )  and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_item as $val) {
                $arr_filterresult[] = isset($val['item_master']) ? array_merge($val['item_master'],$val['0']) : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
      //check the item is already exist in table
    public function itemexists($code = '',$gr_pkey = ''){
       // debug($code);
        //debug($item);
       $this->autoRender = false;
       $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
       $this->PoReturn->useDbConfig = $this->Session->read('ds');
       $item_code = $this->GrItemDetails->query("SELECT gr_item_details.item_code "
               . " from gr_item_details "
               . " where gr_item_pkey = '$gr_pkey'  ");
       $result = isset($item_code['0']['gr_item_details']['item_code'])? $item_code['0']['gr_item_details']['item_code'] : ''; 
       //debug($result);
       $return_qtyno = $this->PoReturn->query("SELECT count(rtn_pkey) "
               . " from return_gr_items "
               . " left join gr_item_details as gr_item_details on (gr_item_details.gr_item_pkey = return_gr_items.grn_fkey) "
               . " where return_gr_items.return_number = '$code' and gr_item_details.gr_item_pkey = '$gr_pkey' "
               . " and return_gr_items.item_fkey = '$result' and return_gr_items.status = '0' and return_gr_items.delete_status = '1'");
       $cnt = $return_qtyno['0']['0']['count(rtn_pkey)'];
       //debug($return_qtyno);
//       if($return_qtyno['0']['0']['count(rtn_pkey)'] == 0){
//        $avail_qty = getavailqty($gr_pkey);
         if ($cnt > 0) {
          echo json_encode(array('msg' => "Item is already selected.", 'cnt' => $cnt));
        } else {
          echo json_encode(array('msg' => "Success.", 'cnt' => $cnt));
            //return $balance_qty;
        }
//        }else{
//           echo json_encode(array('msg' => "Item is already selected")); 
//        }
       
       //echo json_encode(array('msg' => "Item is already selected.", 'cnt' => $cnt));
    }
    //available quantity of an item
    public function getavailqty($gr_no = '',$store = ''){
        //debug($gr_no);
        //debug($item);
       $this->autoRender = false;
       $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
       $this->PoReturn->useDbConfig = $this->Session->read('ds');
       
//       $avail_qtyno = $this->GrItemDetails->query("SELECT gr_item_details.received_qty "
//               . " from gr_item_details "
//               . " where gr_item_pkey = '$gr_no'  ");
//       $result = isset($avail_qtyno['0']['gr_item_details']['received_qty'])? $avail_qtyno['0']['gr_item_details']['received_qty'] : ''; 
//       $return_qtyno = $this->PoReturn->query("SELECT sum(return_qty) "
//               . " from return_gr_items "
//               . " left join gr_item_details as gr_item_details on (gr_item_details.gr_item_pkey = return_gr_items.grn_fkey) "
//               . " where gr_item_details.gr_item_pkey = '$gr_no' and return_gr_items.status = '1' and return_gr_items.delete_status = '1'");
//       $return_no = isset($return_qtyno['0']['0']['sum(return_qty)'])? $return_qtyno['0']['0']['sum(return_qty)'] : ''; 
//       $balance_qty = $result - $return_no;
       
       $itempo = $this->getitem_pkey($gr_no);
       //debug($itempo);
       $arr_item_name = $this->GrItemDetails->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
                item_except_grn_allocation_view  where  store_master_pkey= '$store' and item_master_pkey='$itempo' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
                grn_stock_details_date_view where  item_master_pkey='$itempo' and store_master_pkey= '$store' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where  item_master_pkey='$itempo' and store_master_pkey= '$store' group by store_master_pkey,item_master_pkey,item_code,item_desc) a
                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
                grn_stock_det_rate_date_view where  item_master_pkey='$itempo' and store_master_pkey= '$store' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
                group by store_master_pkey,item_master_pkey
                order by 4,5");
       $balance_qty = $arr_item_name['0']['0']['qtyuptodate'];
//       $return_qtyno = $this->PoReturn->query("SELECT sum(return_qty) "
//               . " from return_gr_items "
//               . " left join gr_item_details as gr_item_details on (gr_item_details.gr_item_pkey = return_gr_items.grn_fkey) "
//               . " where gr_item_details.gr_item_pkey = '$gr_no' and return_gr_items.status = '1' and return_gr_items.delete_status = '1'");
//       $return_no = isset($return_qtyno['0']['0']['sum(return_qty)'])? $return_qtyno['0']['0']['sum(return_qty)'] : ''; 
//       $balance_qty = $balance_qty - $return_no;
 
       
         if ($balance_qty > 0) {
          echo json_encode(array('msg' => "Items available", 'pk' => $balance_qty));
        } else {
            echo json_encode(array('msg' => "All Items are returned.", 'pk' => $balance_qty));
            //return $balance_qty;
        }
    }
    
    //get quantity pkey from grn table
    public function getitem_pkey($grn= ''){
        $this->autoRender = false;
        $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
        $avail_qtyno = $this->GrItemDetails->query("SELECT gr_item_details.item_code "
               . " from gr_item_details "
               . " where gr_item_pkey = '$grn'  ");
       $result = isset($avail_qtyno['0']['gr_item_details']['item_code'])? $avail_qtyno['0']['gr_item_details']['item_code'] : '';
       return $result;
    }
    //available quantity
    public function getavailqty_grn($gr_no = ''){
       $this->autoRender = false;
       $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
       $this->PoReturn->useDbConfig = $this->Session->read('ds');
       $avail_qtyno = $this->GrItemDetails->query("SELECT gr_item_details.received_qty "
               . " from gr_item_details "
               . " where gr_item_pkey = '$gr_no' ");
       $result = isset($avail_qtyno['0']['gr_item_details']['received_qty'])? $avail_qtyno['0']['gr_item_details']['received_qty'] : ''; 
       $return_qtyno = $this->PoReturn->query("SELECT sum(return_qty) "
               . " from return_gr_items "
               . " where grn_fkey = '$gr_no' and return_gr_items.status = '1' and return_gr_items.delete_status = '1'");
       
       $return_no = isset($return_qtyno['0']['0']['sum(return_qty)'])? $return_qtyno['0']['0']['sum(return_qty)'] : ''; 
       $balance_qty = $result - $return_no;
         if ($balance_qty > 0) {
          echo json_encode(array('msg' => "Items available", 'pk' => $balance_qty));
        } else {
            echo json_encode(array('msg' => "All Items are returned.", 'pk' => $balance_qty));
            //return $balance_qty;
        }
    }
   
    //save po return form
    public function save_po_return(){
        $this->autoRender = false;
        $this->layout = null;
        $arr_request_data = $this->request->data;
        $result = array('success' => 0);
        //debug($arr_request_data);
        $this->PoReturnRequest->useDbConfig = $this->Session->read('ds');
        $this->PoReturn->useDbConfig = $this->Session->read('ds');
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
        $arr_request_data['created_by'] = $created_by = $this->Session->read('login_user_id');
        $arr_request_data['creation_date'] = date("Y-m-d h:i:s");
        $arr_request_data['rtn_date'] = date("Y-m-d",strtotime($arr_request_data['return_date']));
        $arr_request_data['rtn_code'] = $arr_request_data['return_number'];
        $arr_request_data['store_code'] = $arr_request_data['store_fkey'];
        $arr_request_data['grn_fkey'] = $arr_request_data['item_fkey'];
        $this->PoReturnRequest->save($arr_request_data);
        
        $getlastid = isset($arr_request_data['po_pkey']) && !empty($arr_request_data['po_pkey']) ? $arr_request_data['po_pkey'] : $this->PoReturnRequest->getInsertid();
        $itemkey = $arr_request_data['po_pkey'] = $getlastid;
        $arr_request_data['mr_fkey'] = $getlastid;
        $store_fkey = $arr_request_data['store_fkey'];
        //$item_name = $arr_request_data['item_fkey'];
        $return_qty = $arr_request_data['return_qty']*-1;
        $grn_fkey = $arr_request_data['item_fkey'];
        //debug($grn_fkey);
        $avail_qty = $this->GrItemDetails->query("SELECT purchase_order.po_number,material_request.mr_code,purchase_order.supplier_code,gr_item_details.item_code "
               . " from gr_item_details "
               . " left join purchase_list as purchase_list on (gr_item_details.po_fkey = purchase_list.purchase_list_pkey) "
               . " left join goods_receved_notes as goods_receved_notes on (goods_receved_notes.grn_pkey = gr_item_details.grn_fkey) "
               . " left join material_request as material_request on (purchase_list.mr_fkey = material_request.mr_pkey) "
               . " left join purchase_order as purchase_order on (purchase_list.po_fkey = purchase_order.po_pkey) "
               . " where gr_item_details.gr_item_pkey = '$grn_fkey' ");
       //debug($avail_qty);
        $arr_request_detail['created_by'] = $created_by = $this->Session->read('login_user_id');
        $arr_request_detail['item_fkey'] = $avail_qty['0']['gr_item_details']['item_code'];
        $arr_request_detail['store_fkey'] = $arr_request_data['store_fkey'];
        $arr_request_detail['creation_date'] = $arr_request_data['return_date'];
        $arr_request_detail['po_no'] = $avail_qty['0']['purchase_order']['po_number'];
        $arr_request_detail['supplier_fkey'] = $avail_qty['0']['purchase_order']['supplier_code'];
        $arr_request_detail['mr_no'] = $avail_qty['0']['material_request']['mr_code'];
        $arr_request_detail['item_state'] = 'PORETURN';
        $arr_request_detail['invoice_no'] = $getlastid;
        $arr_request_detail['item_qty'] = $return_qty;
        $arr_request_detail['status'] = 0;
        $this->StockDetails->save($arr_request_detail);
        //debug($getlastid);
        $stk_id = $this->StockDetails->getInsertid();
        $arr_request_data['po_fkey'] = $getlastid;
        $arr_request_data['stk_fkey'] = $stk_id;
        $arr_request_data['item_fkey'] = $avail_qty['0']['gr_item_details']['item_code'];
        $date_created = $arr_request_data['return_date'];
        $remark = $arr_request_data['remark'];
        $return_number = $arr_request_data['return_number'];
        $arr_request_data['status'] = 0;
        $this->PoReturn->saveAll($arr_request_data);
        $po_id = $this->PoReturn->getInsertid();
        echo json_encode(array('msg' => 'Item added sucessfully', 'pk' => $getlastid));
    }
    //down table view
    public function loadtabledata($id, $rowindex = 1) {
        $this->autoRender = false;
        //debug($id);
        $this->PoReturnRequest->useDbConfig = $this->Session->read('ds');

        $arr_po_master = $this->PoReturnRequest->query("SELECT po_return_request.*,itemmaster.*,return_gr_items.*,goods_receved_notes.gr_number,gr_item_details.received_qty  "
                . "from po_return_request as po_return_request "
                . "left join return_gr_items as return_gr_items on (po_return_request.po_pkey = return_gr_items.po_fkey) "
                . "left join item_master as itemmaster on (return_gr_items.item_fkey = itemmaster.item_master_pkey) "
                . "left join gr_item_details on (return_gr_items.grn_fkey = gr_item_details.gr_item_pkey ) "
                . "left join goods_receved_notes on (goods_receved_notes.grn_pkey = gr_item_details.grn_fkey ) "
                . "where return_gr_items.po_fkey = '$id' and po_return_request.status = '1' and return_gr_items.status = '0' and return_gr_items.delete_status = '1' ");

        $data = '<h3 style="text-align:center; ">Item Details</h3> <hr style="border-top:1px solid #000; ">';
        $data .= '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red; text-align: -webkit-auto; ">';
        $data .=' <thead>';
        $data .=' <tr class="warning">';
        $data .=' <th> Sl no </th>';
        $data .=' <th> Item Name </th>';
        $data .=' <th> GR Number </th>';
        $data .=' <th>Available Qty* </th>';
        $data .= '  <th>Return Qty*</th>';
        $data .= '  <th>Remarks*</th>';
        $data .= '  <th>Action</th>';

        $i = 1;
        foreach ($arr_po_master as $value) {
            $class = ($i % 2) ? 'info' : 'danger';

            $pid = $value["return_gr_items"]["rtn_pkey"];
            $data .= '<tr>';
            $data .= '<td> ' . $i . '</td>';

            $data .= '<td> ' . $value["itemmaster"]["item_desc"] . '</td>';
            $data .= '<td> ' . $value["goods_receved_notes"]["gr_number"] . '</td>';
            $data .= '<td> ' . $value["return_gr_items"]["available_qty"] . '</td>';
            $data .= '<td> ' . $value["return_gr_items"]["return_qty"] . '</td>';
            $data .= '<td> ' . $value["return_gr_items"]["remark"] . '</td>';

            $data .= '<td> <a href="#" class="  glyphicon glyphicon-pencil"onclick="editdata(' . $rowindex . ',' . $pid . ');">&nbsp</a>'
                    . ' <a href="#" class=" glyphicon glyphicon-remove btn-danger" onclick="removedata(' . $rowindex . ',' . $pid . ');"></a></td>';

            $i++;
        }
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '</table>';
        echo $data;
    }
    //delete selected po return
     public function deletepoorder($id = 0) {

        $this->autoRender = false;
	$this->PoReturn->useDbConfig = $this->Session->read('ds');
        $this->PoReturnRequest->useDbConfig = $this->Session->read('ds');
        if ($id != 0) {
        //$this->PoReturn->updateAll(array('delete_status' => 0), array('rtn_pkey' => $id));
        $this->PoReturn->query("DELETE FROM return_gr_items WHERE rtn_pkey = $id");
        //$this->PoReturnRequest->query("DELETE FROM po_return_request WHERE stock_item_pkey = $id");
          echo json_encode(array('msg' => "Item successfully removed"));
        } else {
            echo json_encode(array('msg' => "Item failed to removed"));
        }
    }
    // delete all items added to po_return temporary list
    public function deleteordermaster($id = 0) {
        $this->autoRender = false;
	$this->PoReturn->useDbConfig = $this->Session->read('ds');
        $this->PoReturnRequest->useDbConfig = $this->Session->read('ds');
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        if ($id != 0) {
            //$this->PoReturn->updateAll(array('delete_status' => 0), array('po_fkey' => $id));
            $this->PoReturn->query("DELETE FROM return_gr_items WHERE po_fkey = $id");
            $this->PoReturnRequest->query("DELETE FROM po_return_request WHERE po_pkey = $id");
            $this->StockDetails->query("DELETE FROM stock_details WHERE invoice_no = $id");
            echo json_encode(array('msg' => 'PO Return Request deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'PO Return Request deletion failed!'));
        }
    }
    // Return items final submission
    public function submit_return($id = 0) {

        $this->autoRender = false;
	$this->PoReturn->useDbConfig = $this->Session->read('ds');
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        if ($id != 0) {
        $this->PoReturn->updateAll(array('status' => 1), array('po_fkey' => $id ,'delete_status' => 1 ));
        $this->StockDetails->updateAll(array('status' => 1), array('invoice_no' => $id ));
          echo json_encode(array('msg' => "Items returned successfully. "));
        } else {
            echo json_encode(array('msg' => "PO Return failed."));
        }
    }
    // load edit po_order table view
    public function editpo_order($id,$store,$grn) {
        $this->PoReturn->useDbConfig = $this->Session->read('ds');
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
        $all_item = $this->StockDetails->query("SELECT distinct stockdetails.item_fkey,itemmaster.*,gritemdetails.*,goodsrecevednotes.* "
                . "from stock_details as stockdetails "
                . "left join item_master as itemmaster on (itemmaster.item_master_pkey = stockdetails.item_fkey) "
                . "left join gr_item_details as gritemdetails on (itemmaster.item_master_pkey = gritemdetails.item_code) "
                . "left join goods_receved_notes as goodsrecevednotes on (goodsrecevednotes.grn_pkey = gritemdetails.grn_fkey) "
                . "where itemmaster.status = 1 and stockdetails.status = 1 and stockdetails.store_fkey in ('$store')");
        //debug($all_item);
        $this->set("all_item", $all_item);
       
        //$arr_gritems = $this->GoodsReceivedNotes->query("SELECT gritemdetails.*,goodsrecevednotes.* "
              //  . "from goods_receved_notes as goodsrecevednotes "
              //  . "left join gr_item_details as gritemdetails on (gritemdetails.grn_fkey = goodsrecevednotes.grn_pkey) "
              //  . "where goodsrecevednotes.status = 1 and gritemdetails.grn_fkey in ('$grn')");
       // $this->set("arr_gritems", $arr_gritems);
        //debug($all_item);
        $arr_por_master = $this->PoReturn->query("SELECT itemmaster.*,return_gr_items.*,goods_receved_notes.gr_number,gr_item_details.received_qty  "
                . "from return_gr_items as return_gr_items "
                . "left join item_master as itemmaster on (return_gr_items.item_fkey = itemmaster.item_master_pkey) "
                . "join gr_item_details on (return_gr_items.grn_fkey = gr_item_details.gr_item_pkey ) "
                . "join goods_receved_notes on (goods_receved_notes.grn_pkey = gr_item_details.grn_fkey ) "
                . "where return_gr_items.rtn_pkey = '$id' and return_gr_items.status = '0' and return_gr_items.delete_status = '1' ");
        //debug($arr_por_master);
//        foreach ($arr_por_master as $key => $value) {
//            $out['po_fkey'] = isset($value['return_gr_items']['po_fkey']) ? $value['return_gr_items']['po_fkey'] : '';
//        }
        $this->set('id', $id);
        $this->set('arr_por_master', $arr_por_master);
    }
    //save edited data in modal of po return
     public function editorder_save() {
        $this->autoRender = false;
        $this->PoReturn->useDbConfig = $this->Session->read('ds');
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
        $item_qty = $arr_form_data['return_qty1']*-1;
        $stock_pkey = $arr_form_data['stk_fkey'];
        $this->StockDetails->updateAll(array('item_qty' => $item_qty), array('stock_details_pkey' => $stock_pkey));
        $masterpk = $arr_form_data['po_fkey'];
        $arr_form_data['rtn_pkey'] = $arr_form_data['po_details_pkey'];
        $arr_form_data['return_qty'] = $arr_form_data['return_qty1'];
        $arr_form_data['modified_date'] = date("Y-m-d h:i:s");
        $arr_form_data['modified_by'] = $this->Session->read('login_user_id');
        $this->PoReturn->saveAll($arr_form_data);
        $arr_por_master = $this->PoReturn->query("SELECT itemmaster.*,return_gr_items.*,goods_receved_notes.gr_number,gr_item_details.received_qty  "
                . "from return_gr_items as return_gr_items "
                . "left join item_master as itemmaster on (return_gr_items.item_fkey = itemmaster.item_master_pkey) "
                . "join gr_item_details on (return_gr_items.grn_fkey = gr_item_details.gr_item_pkey ) "
                . "join goods_receved_notes on (goods_receved_notes.grn_pkey = gr_item_details.grn_fkey ) "
                . "where return_gr_items.rtn_pkey = '$masterpk' and return_gr_items.status = '0' and return_gr_items.delete_status = '1' ");
         $this->set('arr_por_master', $arr_por_master);
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $masterpk));
    }
    //load return po list
      public function poreturn() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->PoReturn->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $arr_store = $this->PoReturn->query("SELECT return_gr_items.*,item_master.item_desc,store_master.store_location,goods_receved_notes.gr_number FROM return_gr_items "
                ."join item_master on (item_master.item_master_pkey = return_gr_items.item_fkey ) "
                ."join store_master on (store_master.store_master_pkey = return_gr_items.store_fkey )"
                ."join gr_item_details on (gr_item_details.gr_item_pkey	 = return_gr_items.grn_fkey)"
                ."join goods_receved_notes on (goods_receved_notes.grn_pkey = gr_item_details.grn_fkey )"
                ."WHERE return_gr_items.status = '1' and return_gr_items.delete_status = '1' ORDER BY rtn_pkey desc limit $limit  offset $ofst  ");
        $counts = $this->PoReturn->query("SELECT COUNT(*) FROM return_gr_items join item_master on (item_master.item_master_pkey = return_gr_items.item_fkey ) join store_master on (store_master.store_master_pkey = return_gr_items.store_fkey ) WHERE return_gr_items.status = '1'");
        //debug($counts);
        $count = $counts[0][0]['COUNT(*)'];
        $gr = array();
        $arr_return["rows"]=array();
        foreach ($arr_store as $key => $value) {

            $gr['return_number'] = isset($value['return_gr_items']['return_number']) ? $value['return_gr_items']['return_number'] : '';
            $gr['return_date'] = isset($value['return_gr_items']['return_date']) ? $value['return_gr_items']['return_date'] : '';
            $gr['store_fkey'] = isset($value['store_master']['store_location']) ? $value['store_master']['store_location'] : '';
            $gr['gr_number'] = isset($value['goods_receved_notes']['gr_number']) ? $value['goods_receved_notes']['gr_number'] : '';
            $gr['item_fkey'] = isset($value['item_master']['item_desc']) ? $value['item_master']['item_desc'] : '';
            $gr['return_qty'] = isset($value['return_gr_items']['return_qty']) ? $value['return_gr_items']['return_qty'] : '';
            $gr['remark'] = isset($value['return_gr_items']['remark']) ? $value['return_gr_items']['remark'] : '';
            $arr_return["rows"][$key] = $gr;
        }

        $arr_return["total"] = $count;
        echo json_encode($arr_return);
    }


}
