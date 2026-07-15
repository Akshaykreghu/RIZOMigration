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
 * @link http://book.cakephp.org/2.0  /en/controllers/pages-controller.html
 */
class StockTranferController extends AppController {

    /**
     * Controller name
     * @var string
     */
    //public $layout="default";
    public $name = 'StockTranfer';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('StockTranferItem','StockTranfer','Item','Store','StockDetails','PackageMaster','MaterialRequest','MaterialRequestDetails');
    public function home(){
        
    }
    public function index() {
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
        
//all item      
         $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status'=>1))));
//all store
        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
    }
    
    public function save_stock_transfer(){
        $this->autoRender = false;
        $this->layout = null;
        $arr_request_data = $this->request->data;
        $this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        //debug($arr_request_data);
        $from_code = $arr_request_data['from_store'];
        $to_store = $arr_request_data['to_store'];
        $qty_love = $arr_request_data['required_qty'];
        // Edited by Akshay on 9-4-2025
        $item_master_pkey = $arr_request_data['item_master_pkey'];
        $arr_item = $this->StockTranfer->query("SELECT `item_desc` FROM `item_master` WHERE `item_master_pkey` = '$item_master_pkey' AND status = 1;");
        $item_desc = isset($arr_item[0]['item_master']['item_desc'])? $arr_item[0]['item_master']['item_desc']:'';
        $arr_request_data['item_desc'] = $item_desc;
        // End
        $arr_request_data['po_number'] = $arr_request_data['po_number'];
        $arr_request_data['tranfer_stock'] = $arr_request_data['po_pkey'];
        $arr_request_data['created_by'] = $this->Session->read('login_user_id');
        $arr_request_data['creation_date'] = date("Y-m-d h:i:s");
        $item_pkey = $arr_request_data['item_code'];
        //updated by megha 23/4/2019
        //$arr_request_data['item_fkey'] = $item_key = $this->finditembycode($item_pkey);
        $arr_request_data['item_fkey'] = $item_key = $arr_request_data['item_master_pkey'];
        $arr_request_data['status'] = 1 ;
        //updated by megha 23/4/2019
        $date_created = $arr_request_data['adjustment_date'];
        //debug($arr_request_data);
        if($arr_request_data['stock_tranfer_fkey']){
        $getlastid = $arr_request_data['stock_tranfer_fkey'];
        
        }else{
        
        $this->StockTranfer->save($arr_request_data);  
        $getlastid = $this->StockTranfer->getInsertid();
        $arr_request_data['stock_tranfer_fkey'] = $getlastid;
        }
        
        //$getlastid = $this->StockTranfer->getInsertid();
        $arr_request_data['created_by'] = $this->Session->read('login_user_id');
       // $getlastid = isset($arr_request_data['stock_tranfer_pkey']) && !empty($arr_request_data['stock_tranfer_pkey']) ? $arr_request_data['stock_tranfer_pkey'] : $this->StockTranfer->getInsertid();

        $arr_request_data['status'] = 0 ;
        $this->StockTranferItem->saveAll($arr_request_data);
        $getlastinsertid = $this->StockTranferItem->getInsertid();
        
//        $arr_request_detail['created_by'] = $this->Session->read('login_user_id');
//        $arr_request_detail['creation_date'] = date("Y-m-d h:i:s");
//        $arr_request_detail['status'] = 0 ;
//        $arr_request_detail['item_state'] = 'TRANSFER';
//        $arr_request_detail['store_fkey'] = $arr_request_data['from_store'];
//        $arr_request_detail['item_qty'] = $arr_request_data['required_qty'] * -1;
//        $arr_request_detail['item_fkey'] = $item_key;
//        $arr_request_detail['invoice_no'] = $getlastinsertid;
//        $this->StockDetails->save($arr_request_detail);
//        $getinsid = $this->StockDetails->getInsertid();
//        if($getinsid){
//        $arr_request_details['stock_details_pkey'] = $getinsid + 1;
//        $arr_request_details['created_by'] = $this->Session->read('login_user_id');
//        $arr_request_details['creation_date'] = date("Y-m-d h:i:s");
//        $arr_request_details['status'] = 0 ;
//        $arr_request_details['item_state'] = 'TRANSFER';
//        $arr_request_details['store_fkey'] = $arr_request_data['to_store'];
//        $arr_request_details['item_qty'] = $arr_request_data['required_qty'];
//        $arr_request_details['item_fkey'] = $item_key;
//        $arr_request_details['invoice_no'] = $getlastinsertid;
//        //debug($arr_request_data);
//        $this->StockDetails->save($arr_request_details);
//        }
       
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $getlastid));
    }
    
      //down table view
    public function loadtabledata($id, $rowindex = 1) {
        $this->autoRender = false;
        //debug($id);
        $this->StockTranferItem->useDbConfig = $this->Session->read('ds');

        $arr_po_master = $this->StockTranferItem->query("SELECT stock_tranfer_item.*,stock_tranfer.*,itemmaster.item_desc  "
                . "from stock_tranfer_item as stock_tranfer_item "
                . "left join stock_tranfer as stock_tranfer on (stock_tranfer.stock_tranfer_pkey = stock_tranfer_item.stock_tranfer_fkey) "
                . "left join item_master as itemmaster on (stock_tranfer_item.item_fkey = itemmaster.item_master_pkey) "
                . "where stock_tranfer_item.stock_tranfer_fkey = '$id' and stock_tranfer_item.status = 0 ");
        $data = '<h3 style="text-align:center; ">Item Details</h3> <hr style="border-top:1px solid #000; ">';
        $data .= '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red; text-align: -webkit-auto; ">';
        $data .=' <thead>';
        $data .=' <tr class="warning">';
        $data .=' <th> Sl No </th>';
        $data .=' <th> Item Name </th>';
        $data .=' <th>Available Qty* </th>';
        $data .= '  <th>Transfer Qty*</th>';
        $data .= '  <th>Action</th>';

        $i = 1;
        foreach ($arr_po_master as $value) {
            $class = ($i % 2) ? 'info' : 'danger';

            $pid = $value["stock_tranfer_item"]["stock_item_pkey"];
            $data .= '<tr>';
            $data .= '<td> ' . $i . '</td>';

            $data .= '<td> ' . $value["itemmaster"]["item_desc"] . '</td>';
            $data .= '<td> ' . $value["stock_tranfer_item"]["available_qty"] . '</td>';
            $data .= '<td> ' . $value["stock_tranfer_item"]["required_qty"] . '</td>';

            $data .= '<td> <a href="#" class="  glyphicon glyphicon-pencil" onclick="editdata(' . $rowindex . ',' . $pid . ');">&nbsp</a>'
                    . ' <a href="#" class=" glyphicon glyphicon-remove btn-danger" onclick="removedata(' . $rowindex . ',' . $pid . ');"></a></td>';

            $i++;
        }
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '</table>';
        echo $data;
    }
    
    public function lists() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['item']) ? $arr_request_data['item'] : '';
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'stock_tranfer_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
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
            $branch_condition = "and from_store = '$branch_code' ";
        }
        $id = 1;
        //$counts = $this->StockTranfer->query("SELECT count(*) as count FROM `stock_tranfer` where status = '$id' ");
        $counts = $this->StockTranfer->query("SELECT count(*) as count  FROM `stock_tranfer` left join stock_tranfer_item on (stock_tranfer_item.stock_tranfer_fkey = stock_tranfer.stock_tranfer_pkey) left join store_master on (store_master.store_code = stock_tranfer.from_store) where stock_tranfer.status = '$id' and stock_tranfer_item.status = '1' $branch_condition  ");
       
        $count = $counts[0][0]['count'];
        
        $arr_att = $this->StockTranfer->query("SELECT stock_tranfer.*,stock_tranfer_item.item_desc,stock_tranfer_item.required_qty  FROM `stock_tranfer` left join stock_tranfer_item on (stock_tranfer_item.stock_tranfer_fkey = stock_tranfer.stock_tranfer_pkey) left join store_master on (store_master.store_code = stock_tranfer.from_store) where stock_tranfer.status = '$id' and stock_tranfer_item.status = '1' $branch_condition ORDER BY $sort $order limit $limit offset $ofst ");
        
        // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            
            $from_store = isset($value['stock_tranfer']['from_store']) ? $value['stock_tranfer']['from_store'] : '';
            $to_store = isset($value['stock_tranfer']['to_store']) ? $value['stock_tranfer']['to_store'] : '';
            $store_start = $this->StockTranfer->query("select store_location from store_master where store_master_pkey = '$from_store' ");
            $store_to = $this->StockTranfer->query("select store_location from store_master where store_master_pkey = '$to_store' ");
            
            $out['item_pkey'] = isset($value['item_purchase']['item_pkey']) ? $value['item_purchase']['item_pkey'] : '';
            $out['adjustment_code'] = isset($value['stock_tranfer']['adjustment_code']) ? $value['stock_tranfer']['adjustment_code'] : '';
            $out['name'] = isset($value['stock_tranfer_item']['item_desc']) ? $value['stock_tranfer_item']['item_desc'] : '';
            $out['qty'] = isset($value['stock_tranfer_item']['required_qty']) ? $value['stock_tranfer_item']['required_qty'] : '';
            $out['from_store'] = isset($store_start['0']['store_master']['store_location'])?$store_start['0']['store_master']['store_location']:'';
            $out['to_store'] = isset($store_to['0']['store_master']['store_location'])?$store_to['0']['store_master']['store_location']:'';
            $out['adjustment_date'] = isset($value['stock_tranfer']['adjustment_date']) ? $value['stock_tranfer']['adjustment_date'] : '';
            $out['status'] = isset($value['stock_tranfer']['status']) ? $value['stock_tranfer']['status'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    public function finditembycode($item_code = ""){
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $item_master_pkey = $this->StockTranfer->query("select item_master_pkey from item_master where item_code = '$item_code' ");
        //debug($item_master_pkey);
        return $item_master_pkey['0']['item_master']['item_master_pkey'];
    }

// form store    
    public function getautocompletionsstore_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['store_code']) && !empty($_REQUEST['store_code'])) {
            $searchkey = $_REQUEST['store_code'];
            $filter_condition = '(store_code LIKE "%' . $searchkey . '%" or store_location LIKE "%' . $searchkey . '%")';
            //debug($filter_condition);
            $this->Store->useDbConfig = $this->Session->read('ds');
            $arr_store = $this->Store->query("SELECT * FROM `store_master` WHERE  status = 1 and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_store as $val) {
                $arr_filterresult[] = isset($val['store_master']) ? $val['store_master'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
//tostore_code
public function getautocompletionstostore_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['tostore_code']) && !empty($_REQUEST['tostore_code'])) {
            $searchkey = $_REQUEST['tostore_code'];
            $filter_condition = '(store_code LIKE "%' . $searchkey . '%" or store_location LIKE "%' . $searchkey . '%")';
            //debug($filter_condition);
            $this->Store->useDbConfig = $this->Session->read('ds');
            $arr_store = $this->Store->query("SELECT * FROM `store_master` WHERE status = 1 and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_store as $val) {
                $arr_filterresult[] = isset($val['store_master']) ? $val['store_master'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
//item_name 
//  public function getautocompletionsitem_desc() {
//        $this->autoRender = false;
//        if (isset($_REQUEST['item_desc']) && !empty($_REQUEST['item_desc'])) {
//            $searchkey = $_REQUEST['item_desc'];
//            
//            $store_code = isset($_REQUEST['store'])?$_REQUEST['store']:'';
//            
//            if($store_code != ''){
//                $store_fkey_condoition = " and store_fkey = '$store_code'";
//                $store_code_condoition = " and store_code = '$store_code'";
//            }else{
//                $store_condoition = "";
//                $store_code_condoition = "";
//            }
//            
//            $filter_condition = 'item_master.item_desc LIKE "%' . $searchkey . '%"';
//            $this->Item->useDbConfig = $this->Session->read('ds');
//            $arr_item = $this->Item->query("select item_master.item_master_pkey,item_master.item_code,item_master.item_desc,
//            (select ifnull(sum(item_qty) - (select ifnull(sum(qty-returned_qty),0) from allocate_details where item_purchase_fkey = item_master.item_master_pkey 
//            $store_code_condoition and status = 1) ,0) from stock_details where item_fkey = item_master.item_master_pkey and status = 1 $store_fkey_condoition) as qty
//            from item_master where status = 1 and item_master_pkey in (select item_fkey from stock_details where item_qty > 0 $store_fkey_condoition )  and $filter_condition");
//           
//            $arr_filterresult = array();
//            foreach ($arr_item as $val) {
//                $arr_filterresult[] = isset($val['item_master']) ? array_merge($val['item_master'],$val['0']) : array();
//            }
//          // debug($arr_filterresult);
//        }
//        echo json_encode($arr_filterresult);
//    }
//     public function getautocompletionsitem_desc() {
//        $this->autoRender = false;
//        if (isset($_REQUEST['item_desc']) && !empty($_REQUEST['item_desc'])) {
//            $searchkey = $_REQUEST['item_desc'];
//            
//            $store_code = isset($_REQUEST['store'])?$_REQUEST['store']:'';
//            $stock_pkey = isset($_REQUEST['stock_pkey'])?$_REQUEST['stock_pkey']:'0';
//            if($store_code != ''){
//                $store_fkey_condoition = " and store_fkey = '$store_code'";
//                $store_code_condoition = " and store_code = '$store_code'";
//            }else{
//                $store_condoition = ""; 
//                $store_code_condoition = "";
//            }
//          
//            $filter_condition = 'item_master.item_desc LIKE "%' . $searchkey . '%" ';
//            
//            $this->Item->useDbConfig = $this->Session->read('ds');
//            if($stock_pkey){
//            $arr_item_list = $this->Item->query("SELECT `item_fkey` FROM `stock_tranfer_item` WHERE `status`=0 and `stock_tranfer_fkey` = $stock_pkey ");
//            $arr_list = "";
//             foreach ($arr_item_list as $val) {
//                 $arr_list .= $val['stock_tranfer_item']['item_fkey'].",";
//             }
//             $list = substr($arr_list, 0, -1);
//             if($list){
//             $list_condition = " and item_master_pkey not in ($list)";
//             }else{
//              $list_condition = " ";  
//             }
//            }else{
//            $list_condition = " ";  
//            }
//            //$arr_item_name = $this->Item->query("SELECT * FROM `item_master` join stock_details on (stock_details.item_fkey = item_master.item_master_pkey) WHERE item_master.`status`=1 and stock_details.invoice_no != $stock_pkey and $filter_condition");
//          //  }else{
//            $arr_item_name = $this->Item->query("SELECT item_desc,item_code,item_master_pkey FROM `item_master` WHERE `status`=1 $list_condition and $filter_condition limit 6");
//            //}
//            $arr_item_name_details = array();
//            foreach ($arr_item_name as $value) {
//                $item = $value['item_master']['item_master_pkey'];
//                $itemcode = $value['item_master']['item_code'];
//                $arr_item = $this->Item->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
//                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
//                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
//                item_except_grn_allocation_view  
//                where store_master_pkey= '$store_code' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc
//                union all
//                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
//                grn_stock_details_date_view 
//                where store_master_pkey= '$store_code' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc
//                union all
//                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
//                from Item_allocation_details_view where store_master_pkey= '$store_code' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
//                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
//                grn_stock_det_rate_date_view where store_master_pkey= '$store_code' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
//                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
//                group by store_master_pkey,item_master_pkey
//                order by 4,5 ");
//                $arr_items = array();
//                if(empty($arr_item)){
//                    $arr_items['qtyuptodate'] = 0;
//                }
//                else{
//                   $arr_items['qtyuptodate']=$arr_item['0']['0']['qtyuptodate']; 
//                }
//            
//                $arr_item_name_details[] = isset($value['item_master']['item_master_pkey']) ? array_merge($value['item_master'],$arr_items) : array();
//            
//            }
//        }
//        echo json_encode($arr_item_name_details);
//    }
   public function getautocompletionsitem_desc() {
        $this->autoRender = false;
        if (isset($_REQUEST['item_desc']) && !empty($_REQUEST['item_desc'])) {
            $searchkey = $_REQUEST['item_desc'];
            
            $store_code = isset($_REQUEST['store'])?$_REQUEST['store']:'';
            $stock_pkey = isset($_REQUEST['stock_pkey'])?$_REQUEST['stock_pkey']:'0';
            if($store_code != ''){
                $store_fkey_condoition = " and store_fkey = '$store_code'";
                $store_code_condoition = " and store_code = '$store_code'";
            }else{
                $store_condoition = ""; 
                $store_code_condoition = "";
            }
          
            $filter_condition = 'item_master.item_desc LIKE "%' . $searchkey . '%" ';
            
            $this->Item->useDbConfig = $this->Session->read('ds');
            if($stock_pkey){
            $arr_item_list = $this->Item->query("SELECT `item_fkey` FROM `stock_tranfer_item` WHERE `status`=0 and `stock_tranfer_fkey` = $stock_pkey ");
            $arr_list = "";
             foreach ($arr_item_list as $val) {
                 $arr_list .= $val['stock_tranfer_item']['item_fkey'].",";
             }
             $list = substr($arr_list, 0, -1);
             if($list){
             $list_condition = " and item_master_pkey not in ($list)";
             }else{
              $list_condition = " ";  
             }
             $list_conditions = "and a.item_master_pkey not in (SELECT `item_fkey` FROM `stock_tranfer_item` WHERE `status`=0 and `stock_tranfer_fkey` = $stock_pkey) ";  
            }else{
            $list_condition = " ";
            $list_conditions = " "; 
            }
            //$arr_item_name = $this->Item->query("SELECT * FROM `item_master` join stock_details on (stock_details.item_fkey = item_master.item_master_pkey) WHERE item_master.`status`=1 and stock_details.invoice_no != $stock_pkey and $filter_condition");
          //  }else{
           // $arr_item_name = $this->Item->query("SELECT item_master_pkey,item_code FROM `item_master` WHERE `status`=1 $list_condition and $filter_condition ");
            //}
            //debug($arr_item_name);
            $arr_item_name_details = array();
//            foreach ($arr_item_name as $value) {
//                $item = $value['item_master']['item_master_pkey'];
//                $itemcode = $value['item_master']['item_code'];
//                //debug($value['item_master']['item_master_pkey']);
//                $arr_item = $this->Item->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
//                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
//                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
//                item_except_grn_allocation_view  
//                where store_master_pkey= '$store_code' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc
//                union all
//                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
//                grn_stock_details_date_view 
//                where store_master_pkey= '$store_code' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc
//                union all
//                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
//                from Item_allocation_details_view where store_master_pkey= '$store_code' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
//                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
//                grn_stock_det_rate_date_view where store_master_pkey= '$store_code' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
//                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
//                group by store_master_pkey,item_master_pkey
//                order by 4,5");
                $arr_items = array();
//                //debug($arr_item);
//                
//                if(empty($arr_item)){
//                    $arr_items['qtyuptodate'] = 0;
//                }
//                else{
//                   $arr_items['qtyuptodate']=$arr_item['0']['0']['qtyuptodate']; 
//                }
//            
//                $arr_item_name_details[] = isset($value['item_master']['item_master_pkey']) ? array_merge($value['item_master'],$arr_items) : array();
//            
//            }
             $arr_item = $this->Item->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
                item_except_grn_allocation_view  
                where store_master_pkey= '$store_code' and item_desc LIKE '%" . $searchkey . "%' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
                grn_stock_details_date_view 
                where store_master_pkey= '$store_code' and item_desc LIKE '%" . $searchkey . "%' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where store_master_pkey= '$store_code' and item_desc LIKE '%" . $searchkey . "%'   group by store_master_pkey,item_master_pkey,item_code,item_desc) a
                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
                grn_stock_det_rate_date_view where store_master_pkey= '$store_code' and item_desc LIKE '%" . $searchkey . "%'  group by store_master_pkey,item_master_pkey,item_code,item_desc) b
                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey) $list_conditions 
                group by store_master_pkey,item_master_pkey
                order by 4,5 limit 6");
//             debug($arr_item);
//          if(empty($arr_item)){
//                    $arr_items['qtyuptodate'] = 0;
//                }
//                else{
//                   $arr_items['qtyuptodate']=$arr_item['0']['0']['qtyuptodate']; 
//                }
                foreach ($arr_item as $value) {
                 
                 $arr_item_name_details[] = isset($value['a']['item_master_pkey']) ? array_merge($value['a'],$value['b'],$value['0']) : array();
                }
        }
           //debug($arr_item_name_details);
        echo json_encode($arr_item_name_details);
    }
 //created by megha for calling function to select least count of available qty
//     public function finditem_qty($date_allocate = 0,$store = 0,$item_pkey = 0,$tostore = 0){
//        $this->autoRender = FALSE;
//        $this->Item->useDbConfig = $this->Session->read('ds');
//        $item_details = $this->Item->query("SELECT `stock_bal_qty_fn`('$date_allocate','$store','$item_pkey')qty");
//        //added by megha on 22/01/2020 item count checking for transfer
//        $item_count = $this->Item->query("SELECT count(item_master_pkey) count FROM `grn_stock_det_rate_date_view` WHERE `store_master_pkey` = '$tostore' and item_master_pkey = '$item_pkey'");
//        
//        $result['count'] = $item_count['0']['0']['count'];
//        $result['qty'] = $item_details['0']['0']['qty'];
//        //debug($item_count);
//        echo json_encode($result);
//    }
    public function finditem_qty($date_allocate = 0,$store = 0,$item_pkey = 0){
        $this->autoRender = FALSE;
        $this->Item->useDbConfig = $this->Session->read('ds');
        $item_details = $this->Item->query("SELECT `stock_bal_qty_fn`('$date_allocate','$store','$item_pkey')qty");
        $arr_ponumber = $this->Item->query("SELECT * FROM `stock_details` WHERE `status`=1 and item_fkey='$item_pkey' and store_fkey='$store' and po_no !='0' order by creation_date desc LIMIT 1");
	$po = $arr_ponumber['0']['stock_details']['po_no'];
        $arr_pokey = $this->Item->query("SELECT po_pkey FROM `purchase_order` WHERE `status`=1 and po_number='$po'");
	$result['po_number'] = $po;
        $result['po'] = $arr_pokey['0']['purchase_order']['po_pkey'];
        $result['qty'] = $item_details['0']['0']['qty'];
        echo json_encode($result);
    }
 //created by megha  
//item_code
  public function getautocompletionsitem_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['item_code']) && !empty($_REQUEST['item_code'])) {
            $searchkey = $_REQUEST['item_code'];
            $filter_condition = 'item_code LIKE "%' . $searchkey . '%"';
            $this->Item->useDbConfig = $this->Session->read('ds');
            $arr_item_code = $this->Item->query("SELECT * FROM `item_master` WHERE `status`=1 and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_item_code as $val) {
                $arr_filterresult[] = isset($val['item_master']) ? $val['item_master'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
//item conunt find   
 public function finditem($itemcode = '',$tostore ='',$adjdate ='') {
	$this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $itemname =$this->itemname($itemcode);
        $storecode = $this->store_name($tostore);
        $existingitem = $this->existingitem($tostore,$itemcode);
        //$itempo = $itemname['0']['item_master']['item_master_pkey'];
        $itempo = $itemcode;
        $storeid =$storecode['0']['store_master']['store_master_pkey'];
        //$arr_item_store = $this->MaterialRequest->query("SELECT materialrequest.*,SUM(mrdetails.unit) as sumunit,SUM(mrdetails.ordering_qty) as sumorder "
          //      . " from material_request as materialrequest "
          //      . " left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
          //      . " where mrdetails.item_code= '$itempo'  and materialrequest.store_code= '$storeid' ");
        
//        $arr_item_store = $this->MaterialRequest->query("SELECT SUM(item_qty) as sumunit"
//                . " from stock_details  "
//                . " where item_fkey= '$itempo'  and store_fkey= '$storeid' and status=1");
       $arr_item_store = $this->MaterialRequest->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
                item_except_grn_allocation_view  where  store_master_pkey= '$storeid' and item_master_pkey='$itempo' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
                grn_stock_details_date_view where  item_master_pkey='$itempo' and store_master_pkey= '$storeid' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where  item_master_pkey='$itempo' and store_master_pkey= '$storeid' group by store_master_pkey,item_master_pkey,item_code,item_desc) a
                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
                grn_stock_det_rate_date_view where  item_master_pkey='$itempo' and store_master_pkey= '$storeid' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
                group by store_master_pkey,item_master_pkey
                order by 4,5");
       $result = $arr_item_store['0']['0']['qtyuptodate'];
        //$new= $arr_item_store['0']['0']['sumunit'];  
        //$order =$arr_item_store['0']['0']['sumorder'];  
        //$result =$new - $existingitem - $order; 
        //$result =$arr_item_store['0']['0']['sumunit'];
        if($result == null){$result = 0 ; }
        return $result;
    }
//find store code
    public function itemname($itemcode= '') {
         $this->autoRender = false;
	 $this->Item->useDbConfig = $this->Session->read('ds');
         $arr_item = $this->Item->query("SELECT item_master_pkey FROM `item_master` WHERE `status`= '1' and item_desc ='$itemcode' ");
         return $arr_item;
    }
 //find store code
    public function storename($store_code= '') {
         $this->autoRender = false;
	 $this->Store->useDbConfig = $this->Session->read('ds');
         $arr_store = $this->Store->query("SELECT * FROM `store_master` WHERE `status`= '1' and store_code ='$store_code' ");
         //debug($store_code);
         return $arr_store;
    }
     //find store name
    public function store_name($store_code= '') {
         $this->autoRender = false;
	 $this->Store->useDbConfig = $this->Session->read('ds');
         $arr_store = $this->Store->query("SELECT store_master_pkey FROM `store_master` WHERE `status`= '1' and store_location ='$store_code' ");
         //debug($arr_store);
         return $arr_store;
    }
//find alredy purchase itemwz
   public function existingitem($storecode = '', $itemcode ='')
   {
       $this->autoRender = false;
	   $this->StockTranfer->useDbConfig = $this->Session->read('ds');
       $arr_exting_store = $this->StockTranfer->query("SELECT stockstoretranfer.*,sum(stockstoretranferitem.required_qty) as unitsum "
                . " from stock_tranfer as stockstoretranfer "
                . "left join stock_tranfer_item as stockstoretranferitem on (stockstoretranfer.stock_tranfer_pkey = stockstoretranferitem.stock_tranfer_fkey) "
                . "where stockstoretranfer.to_store= '$storecode'  and stockstoretranferitem.item_desc= '$itemcode' ");
       $exting =$arr_exting_store['0']['0']['unitsum'];
       return $exting;
   }
//form save
public function save() 
   {
        $this->autoRender = FALSE;
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
		$this->StockTranfer->useDbConfig = $this->Session->read('ds');
		$this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $arr_form_data['created_by'] = $this->Session->read('user_id');
        $this->StockTranfer->save($arr_form_data);
        $getlastid = isset($arr_form_data['stock_tranfer_pkey']) && !empty($arr_form_data['stock_tranfer_pkey']) ? $arr_form_data['stock_tranfer_pkey'] : $this->StockTranfer->getInsertid();
//        $allitem = $this->getitem($arr_form_data['item_code']);
        $itemkey = $arr_form_data['stock_tranfer_pkey'] = $getlastid;
        $arr_form_data['stock_tranfer_fkey'] = $getlastid;
//        foreach ($allitem as $value) {
//            $arr_form_data['package'] = $value['itemdetails']['package'];
//            $arr_form_data['year_of_make'] = $value['warrantydetails']['waranty_begin_date'];
//            $arr_form_data['thickness'] = $value['itemdetails']['thickness'];
//            $arr_form_data['dimension'] = $value['itemdetails']['dimension'];
//            $arr_form_data['density'] = $value['itemdetails']['density'];
//        }

        $this->StockTranferItem->saveAll($arr_form_data);
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $itemkey));
    }

//get itemdettails
    public function getitem($id = '') {
        $this->autoRender = false;
		$this->Item->useDbConfig = $this->Session->read('ds');
        $arr_item = $this->Item->query("SELECT itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*"
                . " from item_master as itemmaster "
                . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                . "where itemmaster.item_master_pkey = '$id' ");

        return $arr_item;
        //debug($arr_item);
    }

//load table              
    public function loadtable($id, $rowindex = 1) {
        $this->autoRender = false;
        //debug($id);
        
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
		$this->MaterialRequest->useDbConfig = $this->Session->read('ds');
//        $itemname =$this->itemname($itemcode);
//        $itempo = $itemname[0]['item_master']['item_master_pkey'];
        
        $arr_store_master = $this->StockTranfer->query("SELECT stock.*,stockitem.*"
                . " from stock_tranfer as stock"
                . " left join stock_tranfer_item as stockitem on (stock.stock_tranfer_pkey = stockitem.stock_tranfer_fkey)"
                
                . " where stock.stock_tranfer_pkey = '$id' and stockitem.status='1' ");
       
        //debug($arr_store_master);
        $itemcode=$arr_store_master['0']['stockitem']['item_desc'];
        $tostore=$arr_store_master['0']['stock']['to_store'];
        $itemname =$this->itemname($itemcode);
        $storecode = $this->storename($tostore);
        debug($storecode);
        $itempo =$itemname['0']['item_master']['item_master_pkey'];
        $storeid =$storecode['0']['store_master']['store_master_pkey'];
        
        
        
        //debug($itempo);
        $arr_item_store = $this->MaterialRequest->query("SELECT materialrequest.*,SUM(mrdetails.ordering_qty) as sumorder "
                . " from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "where mrdetails.item_code= '$itempo'  and materialrequest.store_code= '$storeid' ");
        
            //debug($arr_item_store);
        $currnt= $arr_item_store['0']['0']['sumorder'];
       // debug($currnt);
        $data = '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red;">';
        $data .= '  <thead>';
        $data .= '  <tr class="warning">';
        $data .= '  <th> Sl no </th>';
        $data .= '  <th>Item Name</th>';
//        $data .= '  <th>Package</th> ';
//        $data .= '  <th>year of make</th>';
//        $data .= '  <th>Thickness</th>';
//        $data .= '  <th>Dimension</th>';
//        $data .= '  <th>Density</th>';
        $data .= '  <th>Available Qty</th>';
        $data .= '  <th>Required Qty</th>';
        $data .= '  <th>Current  Stock</th>';
        $data .= '  <th>Action</th>';

        $i = 1;
        foreach ($arr_store_master as $value) {
            $class = ($i % 2) ? 'info' : 'danger';
            $pid = $value["stockitem"]["stock_item_pkey"];
            $data .= '<tr class="' . $class . '">';
            $data .= '<td> ' . $i . '</td>';
            $data .= '<td> ' . $value["stockitem"]["item_desc"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["package"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["year_of_make"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["thickness"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["dimension"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"][""] . '</td>';
            
            $data .= '<td> ' . $value["stockitem"]["available_qty"] . '</td>';
            $data .= '<td> ' . $value["stockitem"]["required_qty"] . '</td>';
            $data .= '<td> ' . $currnt . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["tranfer_stock"] . '</td>';


            $data .= '<td> '
                   // . '<a href="#" class="  glyphicon glyphicon-pencil"onclick="editdaata(' . $rowindex . ',' . $pid . ');">&nbsp</a>'
                    . ' <a href="#" class=" glyphicon glyphicon-remove btn-danger" onclick="removedaata(' . $rowindex . ',' . $pid . ');"></a></td>';

            $i++;
        }
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '</table>';
        echo $data;
    }

//edit order
    public function editstoreitem($id) {
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
        //all item      
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
        $arr_store_master = $this->StockTranfer->query("SELECT stock.*,stockitem.*"
                . " from stock_tranfer as stock"
                . " left join stock_tranfer_item as stockitem on (stock.stock_tranfer_pkey = stockitem.stock_tranfer_fkey)"
               
                . " where stockitem.stock_item_pkey = '$id' ");
        //debug($arr_store_master);
        foreach ($arr_store_master as $key => $value) {
            $out['stock_tranfer_fkey'] = isset($value['stockitem']['stock_tranfer_fkey']) ? $value['stockitem']['stock_tranfer_fkey'] : '';
        }
        $this->set('id', $id);
        $this->set('arr_store_master', $arr_store_master);
    }
    //edit return quantity
    public function transfer($id) {
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        //$this->Item->useDbConfig = $this->Session->read('ds');
        //all item      
       // $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
        $arr_store_master = $this->StockTranfer->query("SELECT stock.*,stockitem.*"
                . " from stock_tranfer as stock"
                . " left join stock_tranfer_item as stockitem on (stock.stock_tranfer_pkey = stockitem.stock_tranfer_fkey)"
               
                . " where stockitem.stock_item_pkey = '$id' ");
        //debug($arr_store_master);
        foreach ($arr_store_master as $key => $value) {
            $out['stock_tranfer_fkey'] = isset($value['stockitem']['stock_tranfer_fkey']) ? $value['stockitem']['stock_tranfer_fkey'] : '';
        }
        $this->set('id', $id);
        $this->set('arr_store_master', $arr_store_master);
    }
//save edited data in modal of po return
     public function editorder_save() {
        $this->autoRender = false;
        $this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
        $item_qty = $arr_form_data['return_qty1'];
        $stock_pkey = $arr_form_data['stock_item_pkey'];
        $modified_by = $this->Session->read('login_user_id');
        $modified_date = date("Y-m-d h:i:s");
        $this->StockTranferItem->updateAll(array('required_qty' => $item_qty), array('stock_item_pkey' => $stock_pkey));
        $masterpk = $arr_form_data['stock_tranfer_fkey'];
       
      //  $this->StockDetails->updateAll(array('item_qty' => $item_qty,'modified_by' => $modified_by,'modified_date' => $modified_date), array('invoice_no' => $stock_pkey));
//        $arr_form_data['rtn_pkey'] = $arr_form_data['po_details_pkey'];
//        $arr_form_data['return_qty'] = $arr_form_data['return_qty1'];
//        $arr_form_data['modified_date'] = date("Y-m-d h:i:s");
//        $arr_form_data['modified_by'] = $this->Session->read('login_user_id');
//        $this->PoReturn->saveAll($arr_form_data);
//        $arr_por_master = $this->PoReturn->query("SELECT itemmaster.*,return_gr_items.*,goods_receved_notes.gr_number,gr_item_details.received_qty  "
//                . "from return_gr_items as return_gr_items "
//                . "left join item_master as itemmaster on (return_gr_items.item_fkey = itemmaster.item_master_pkey) "
//                . "join gr_item_details on (return_gr_items.grn_fkey = gr_item_details.gr_item_pkey ) "
//                . "join goods_receved_notes on (goods_receved_notes.grn_pkey = gr_item_details.grn_fkey ) "
//                . "where return_gr_items.rtn_pkey = '$masterpk' and return_gr_items.status = '0' and return_gr_items.delete_status = '1' ");
//         $this->set('arr_por_master', $arr_por_master);
        echo json_encode(array('msg' => 'Item quantity edited sucessfully', 'pk' => $masterpk));
    }
//edit save
    public function editordersave() {
        $this->autoRender = false;
		$this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $masterpk = $arr_form_data['stock_tranfer_fkey'];
       // debug($arr_form_data);
        //debug($masterpk)
        //$allitem = $this->getitem($arr_form_data['item_code']);
//        foreach ($allitem as $value) {
//            $arr_form_data['package'] = $value['itemdetails']['package'];
//            $arr_form_data['year_of_make'] = $value['warrantydetails']['waranty_begin_date'];
//            $arr_form_data['thickness'] = $value['itemdetails']['thickness'];
//            $arr_form_data['dimension'] = $value['itemdetails']['dimension'];
//            $arr_form_data['density'] = $value['itemdetails']['density'];
//        }
        $this->StockTranferItem->save($arr_form_data);
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $masterpk));
    }
 
  //delete storeitem
    public function deletestoreitem($id=0){
        $this->autoRender = false;
	    $this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $item_key = $this->getitemfkey($id);
        $item = $item_key['0']['stock_tranfer_item']['stock_tranfer_fkey'];
        //debug($item);
        if($id != 0){
			//delete commented by megha on 26_03_2020
            //$this->StockTranfer->query("DELETE FROM stock_tranfer WHERE stock_tranfer_pkey = $item");
           // $this->StockTranferItem->query("DELETE FROM stock_tranfer_item WHERE stock_item_pkey = $id");
            //$this->StockTranfer->updateAll(array('status'=>2),array('stock_tranfer_pkey'=>$item));
            $this->StockTranferItem->updateAll(array('status'=>2),array('stock_item_pkey'=>$id));
            echo json_encode(array('msg' => 'Store Tranfer deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Store Tranfer Details  deletion failed!'));
        }
    } 
   //get stock_transfer_fkey using stock_transfer_pkey
    public function getitemfkey($id = 0) {
     $this->autoRender = false;
     $this->StockTranfer->useDbConfig = $this->Session->read('ds');
     $arr_store = $this->StockTranfer->query("SELECT stock_tranfer_fkey FROM `stock_tranfer_item` WHERE `status`= '0' and stock_item_pkey ='$id' ");
      //debug($arr_store);
     return $arr_store;
    }
   // Return items final submission
    public function submit_return($id = 0) {

        $this->autoRender = false;
	$this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        if ($id != 0) {
        $this->StockTranferItem->updateAll(array('status' => 1), array('stock_tranfer_fkey' => $id ,'status' => 0 ));
       // $this->StockDetails->updateAll(array('status' => 1), array('invoice_no' => $id ));
        $arr_stock = $this->StockTranfer->query("SELECT * FROM stock_tranfer_item  "
           . " left join stock_tranfer as stock_tranfer on (stock_tranfer.stock_tranfer_pkey = stock_tranfer_item.stock_tranfer_fkey)"
           . " WHERE stock_tranfer_item.stock_tranfer_fkey= $id ");
        $arr_stock_details = $this->StockTranfer->query("CALL `stock_tranfer_pkey_prc`('$id', @`perr_msg`)");
       //debug($arr_stock);
         //$this->StockDetails->updateAll(array('status' => 1), array('invoice_no' => $id ,'status' => 0 ));
//        foreach ($arr_stock as $val) {
//            debug($id);
//            $arr_form_data['store_fkey'] = isset($val['stock_tranfer']['from_store']) ? $val['stock_tranfer']['from_store'] : '';
//            //debug($from_store);
//            $arr_form_detail['store_fkey'] = isset($val['stock_tranfer']['to_store']) ? $val['stock_tranfer']['to_store'] : '';
//            $arr_form_data['item_fkey'] = isset($val['stock_tranfer_item']['item_fkey']) ? $val['stock_tranfer_item']['item_fkey'] : '';
//            $arr_form_data['item_qty'] = isset($val['stock_tranfer_item']['required_qty']) ? $val['stock_tranfer_item']['required_qty'] : '';
//            $arr_form_data['creation_date'] = isset($val['stock_tranfer_item']['created_date']) ? $val['stock_tranfer_item']['created_date'] : '';
//            $this->StockTranfer->save($arr_form_data);
//            //$this->StockTranfer->query("CALL `stock_trans_prc`('$from_store', '$to_store', '$item_key', '$qty_return', '$date_created', 'admin', @`perrm`) ");
//        }
          echo json_encode(array('msg' => "Stock transferred successfully. "));
        } else {
            echo json_encode(array('msg' => "Stock transfer failed."));
        }
    }
//search main stocktranfer
    public function getautocompletionsadjustment_code(){
       $this->autoRender = false;
           if(isset($_REQUEST['adjustment_code']) && !empty($_REQUEST['adjustment_code'])){
               $searchkey = $_REQUEST['adjustment_code'];
            $filter_condition = 'adjustment_code LIKE "%' . $searchkey . '%"';
   
            //debug($filter_condition);
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->StockTranfer->query("SELECT * FROM `stock_tranfer` WHERE `status`=1 and $filter_condition");
        $arr_filterresult = array();
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['stock_tranfer']) ? $val['stock_tranfer'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    }
//main table delete
   public function deletestoremaster($id=0){
        $this->autoRender = false;
	$this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        if($id != 0){
			//delete commented by megha on 26_03_2020
            //$this->StockTranferItem->query("DELETE FROM stock_tranfer_item WHERE stock_tranfer_fkey = $id");
            //$this->StockTranfer->query("DELETE FROM stock_tranfer WHERE stock_tranfer_pkey = $id");
            $this->StockTranfer->updateAll(array('status'=>2),array('stock_tranfer_pkey'=>$id));
            $this->StockTranferItem->updateAll(array('status'=>2),array('stock_tranfer_fkey'=>$id));
            echo json_encode(array('msg' => 'Stock Transfer Request deleted successfully!'));
        }else{
            echo json_encode(array('msg' => 'Stock Transfer Request deletion failed!'));
        }
    }

    //edited by sinsiya on 20-03-2025  
    public function itemfilter(){
        $this->autoRender = false;
        $this->Store->useDbConfig = $this->Session->read('ds');
        
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and item_desc like '%$q%'";
        } else {
            $q_condition = "";
        }
        $arr_site = $this->Store->query("select * from item_master where status= '1' $q_condition order by item_desc asc");
        $array = array();
        $stores = array();
        //$stores[] = array("id" => "0", "text" => "ALL");
        foreach ($arr_site as $key => $value) {
            $stores[] = array(
                'id' => $value['item_master']['item_master_pkey'],
                'text' => $value['item_master']['item_desc']
            );
        }
        $array['items'] = $stores;
        echo json_encode($array);
    }
public function getitem_code($store_fkey = 0,$item = 0) {
   $this->autoRender = false;
   $this->Item->useDbConfig = $this->Session->read('ds');
  //  $searchkey = $item;
    
//   debug($item); 
  
       $arr_item_name = $this->Item->query("SELECT * FROM `item_master` WHERE `status`=1 and item_master_pkey = $item");
     //  debug($arr_item_name);
       $arr_item_name_details = array();
           $arr_item = array(); // Edited by Akshay on 1-4-2025
           if($store_fkey != 0 && $item != 0){  // Edited by Akshay on 1-4-2025
               $arr_item = $this->Item->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty) as qtyuptodate ,
               b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
               (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
               item_except_grn_allocation_view  
               where store_master_pkey= '$store_fkey' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc
               union all
               select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
               grn_stock_details_date_view 
               where store_master_pkey= '$store_fkey' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc
               union all
               select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
               from Item_allocation_details_view where store_master_pkey= '$store_fkey' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
               join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
               grn_stock_det_rate_date_view where store_master_pkey= '$store_fkey' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
               on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
               group by store_master_pkey,item_master_pkey
               order by 4,5");
           }  // Edited by Akshay on 1-4-2025

           
           $arr_items = array();
           if(empty($arr_item)){
               $arr_items['qtyuptodate'] = 0;
           }
           else{
              $arr_items['qtyuptodate']=$arr_item['0']['0']['qtyuptodate']; 
           }
       if(!empty($arr_item_name)){
           $arr_item_name_details = array_merge($arr_item_name['0']['item_master'],$arr_items);
       }else{
           $arr_item_name_details =$arr_items;
       }
       
      //  debug($arr_item_name_details) ;  
   echo json_encode($arr_item_name_details);
}

   
}