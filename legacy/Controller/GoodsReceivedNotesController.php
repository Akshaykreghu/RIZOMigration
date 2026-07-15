<?php

/**
 * Static content controller.
 *
 * This file will render views from views   /pages/
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
class GoodsReceivedNotesController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'GoodsReceivedNotes';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('GrItemDetails','StockDetails','GoodsReceivedNotes','Contacts', 'Store', 'PurchaseOrderDetails', 'Item','item_purchase',  'GoodsReceivedNotesitem', 'PurchaseOrder', 'MaterialRequest', 'PoMaterial', 'PurchaseList', 'MaterialRequestDetails', 'Store', 'PurchaseOrderDetails', 'Item'
        , 'StockStoreTranferItem', 'StockStoreTranfer', 'EmployeeDetails');

    public function index() {
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->Contacts->useDbConfig = $this->Session->read('ds');
        $this->item_purchase->useDbConfig = $this->Session->read('ds');

        if ($this->Session->read('user_group') == 2) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $conditions = "and store_master_pkey in(select store_pkey from access_store where emp_fkey = '$emp_fkey' and status=1)";
        } else {
            $conditions = "";
        }
        $store_arr = $this->item_purchase->query("select * from store_master where status = '1'  $conditions");

        $this->set("all_store", $all_store = $this->item_purchase->query("select * from store_master where status = '1'  $conditions"));
//        $this->set("all_store", $all_store = $this->Store->find("all", array('conditions' => array('status' => 1))));
        $this->set("a_suppliers", $a_supplier = $this->Contacts->find("all", array("conditions" => array('status' => 1))));
    }

    public function home() {
        
    }

    public function purchaselist() {
        $this->autoRender = FALSE;
        $arr_request = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'po_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
        $ofst = ($page - 1) * $limit;
        $supplier_id = isset($arr_request['supplier_code'])?$arr_request['supplier_code']:NULL;
        $conditions = '';
        if($supplier_id != 'All' && $supplier_id != ''){
            $conditions .= " And purchaseorder.supplier_code = $supplier_id ";
        }else{
            $conditions .="";
        }
        if (isset($_REQUEST['store_code'])) {
            $id = $arr_request['store_code'];
            $conditions .= " AND materialrequest.store_code= '$id' ";
            $resp_att = array();
            $resp_att["rows"] = array();
            //$count = $this->MaterialRequest->find("count", array("conditions" => array('status' => 1)));
            $count = $this->MaterialRequest->query("SELECT count(*) from purchase_order as purchaseorder where purchaseorder.po_pkey in (select distinct(po_fkey) from po_item_details where mr_fkey in (select mr_pkey from material_request where store_code = '$id'))");
//            $arr_purchase = $this->MaterialRequest->query("SELECT purchaselist.*,materialrequest.*,purchaseorder.* "
//                    . "from purchase_list as purchaselist "
//                    . "left join material_request as materialrequest on (purchaselist.mr_fkey = materialrequest.mr_pkey) "
//                    . "left join purchase_order as purchaseorder on (purchaseorder.po_pkey = purchaselist.po_fkey) "
//                    . " where materialrequest.status = '1' "
//                    . "$conditions ORDER BY $sort $order limit $limit offset $ofst ");
            $arr_purchase = $this->MaterialRequest->query("SELECT * from purchase_order as purchaseorder where purchaseorder.po_pkey in (select distinct(po_fkey) from po_item_details where mr_fkey in (select mr_pkey from material_request where store_code = '$id')) ORDER BY $sort $order limit $limit offset $ofst");
            
        } else {
            $resp_att = array();
            $resp_att["rows"] = array();
            $po_date = isset($arr_request['po_date'])?$arr_request['po_date']:NULL;
            if($po_date){
            $conditions .= " AND purchaseorder.po_date<= '$po_date' ";
            }
             //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
            $user_group = $this->Session->read('user_group');
            if ($user_group == 2) {
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                //$branch_conditions = " and purchaseorder.po_pkey in (select distinct(po_fkey) from po_item_details where mr_fkey in (select mr_pkey from material_request where store_code in (select store_pkey from access_store where emp_fkey in (select emp_pkey from emp_details where branch_code='".$cur_emp_branch."') and status=1)))";
                $branch_conditions = " and purchaseorder.po_pkey in (select distinct(po_fkey) from po_item_details where mr_fkey in (select mr_pkey from material_request where store_code in (select store_pkey from access_store where emp_fkey=".$cur_emp_key." and status=1 and store_pkey in (select store_master_pkey from store_master where status=1))))";
                
            } else {
                $branch_conditions = "";
            }
            //employee branch wise sorting ends here
            //$count = $this->MaterialRequest->find("count", array("conditions" => array('status' => 1)));
            $count = $this->MaterialRequest->query("SELECT count(*) from purchase_order as purchaseorder where purchaseorder.status = '1' $branch_conditions $conditions");
//            $arr_purchase = $this->PurchaseOrder->query("SELECT purchaselist.*,materialrequest.*,purchaseorder.* "
//                    . "from purchase_list as purchaselist "
//                    . "left join material_request as materialrequest on (purchaselist.mr_fkey = materialrequest.mr_pkey) "
//                    . "left join purchase_order as purchaseorder on (purchaseorder.po_pkey = purchaselist.po_fkey) "
//                    . " where materialrequest.status = '1' "
//                    . "$conditions ORDER BY $sort $order limit $limit offset $ofst ");
            
            $arr_purchase = $arr_purchase = $this->PurchaseOrder->query("SELECT * from purchase_order as purchaseorder where purchaseorder.status = '1' $branch_conditions $conditions ORDER BY $sort $order limit $limit offset $ofst ");
        }
        
        $out = array();
        foreach ($arr_purchase as $key => $value) {
            $out['po_pkey'] = isset($value['purchaseorder']['po_pkey']) ? $value['purchaseorder']['po_pkey'] : '';
            $out['po_number'] = isset($value['purchaseorder']['po_number']) ? $value['purchaseorder']['po_number'] : ''; 
            $out['po_date'] = isset($value['purchaseorder']['po_date']) ? $value['purchaseorder']['po_date'] : '';
            $out['expected_date'] = isset($value['purchaseorder']['expected_date']) ? $value['purchaseorder']['expected_date'] : '';
            $out['location'] = isset($value['purchaseorder']['location']) ? $value['purchaseorder']['location'] : '';
            $out['supplier_name'] = isset($value['purchaseorder']['supplier_name']) ? $value['purchaseorder']['supplier_name'] : '';
            $out['remarks'] = isset($value['purchaseorder']['remarks']) ? $value['purchaseorder']['remarks'] : '';
            $resp_att["rows"][$key] = $out;
        }
        //$resp_att["total"] = count($resp_att["rows"]);
        $resp_att["total"] = $count['0']['0']['count(*)'];
        echo json_encode($resp_att);
    }
    
    public function pgrnlist() {
        $this->autoRender = FALSE;
        $arr_request = $this->request->data;
        $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'grn_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
        $ofst = ($page - 1) * $limit;
        $supplier_id = isset($arr_request['supplier_code']) ? $arr_request['supplier_code'] : NULL;
        $conditions = '';

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $conditions=" GoodsReceivedNotes.store_code in (select store_pkey from access_store where emp_fkey=".$cur_emp_key." and status=1) ";
        }

        $resp_att = array();
        $resp_att["rows"] = array();

        $joins = array(
            array(
                'table' => 'store_master',
                'alias' => 'Store',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('Store.store_master_pkey = GoodsReceivedNotes.store_code')
            ),
        );
        $count = $this->GoodsReceivedNotes->find("count", array("conditions" => array('GoodsReceivedNotes.status' => 1, 'Store.status' => 1,$conditions), "joins" => $joins));
        $arr_purchase = $this->GoodsReceivedNotes->find("all", array("fields" => array("GoodsReceivedNotes.*,Store.store_location"), "conditions" => array('GoodsReceivedNotes.status' => 1, 'Store.status' => 1,$conditions), "joins" => $joins, 'order' => array($sort => $order),
            'limit' => intval($limit),
            'offset' => intval($ofst)));


        $out = array();
        foreach ($arr_purchase as $key => $value) {
            $out['grn_pkey'] = isset($value['GoodsReceivedNotes']['grn_pkey']) ? $value['GoodsReceivedNotes']['grn_pkey'] : '';
            $out['gr_number'] = isset($value['GoodsReceivedNotes']['gr_number']) ? $value['GoodsReceivedNotes']['gr_number'] : '';
            $out['gr_date'] = isset($value['GoodsReceivedNotes']['gr_date']) ? $value['GoodsReceivedNotes']['gr_date'] : '';
            $out['store_location'] = isset($value['Store']['store_location']) ? $value['Store']['store_location'] : '';
            $out['remarks'] = isset($value['GoodsReceivedNotes']['remark']) ? $value['GoodsReceivedNotes']['remark'] : '';
            $resp_att["rows"][$key] = $out;
        }
//        $resp_att["total"] = count($resp_att["rows"]);
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    public function showgrndetails($grn_fkey = 0){
        $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
        $grn_details = $this->GoodsReceivedNotes->query("select goods_receved_notes.gr_number,goods_receved_notes.gr_date,ordering_qty,received_qty,goods_receved_notes.remark,goods_receved_notes.creation_date,item_desc from goods_receved_notes LEFT JOIN gr_item_details on (goods_receved_notes.grn_pkey = gr_item_details.grn_fkey) left join item_master on (item_master.item_master_pkey = gr_item_details.item_code) where grn_pkey = '".$grn_fkey."'  ");
//        debug($grn_details);
        $this->set("arr_att", $grn_details);
    }

//form
//      public function form($po_key = 0, $pk = 0, $store = 0) {
//         $arr_request_data = $this->request->data;
//         $this->Item->useDbConfig = $this->Session->read('ds');
//         $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
//         $this->PurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
//         // $this->set("all_item", $all_item = $this->Item->find("all", array("conditioin" => array('status' => 1))));
        
//         $arr_purchase = $this->PurchaseOrder->query("select ifnull((select sum(received_qty) from gr_item_details where po_fkey = po_item_details.po_fkey and item_code = po_item_details.item_code and grn_fkey in (select grn_pkey from goods_receved_notes where store_code = '$store' )),0) tos,
//         ifnull((select sum(received_qty) from gr_item_details where item_code = po_item_details.item_code and grn_fkey in (select grn_pkey from goods_receved_notes where store_code = '$store' and status = 1)),0) current_stock,po_item_details.item_code,po_item_details.required_qty,purchase_order.remarks,po_number,po_date,purchase_order.po_type,supplier_name,supplier_code,material_request.mr_code,material_request.mr_pkey,purchase_order.expected_date,
// purchase_order.location,purchase_order.po_pkey,store_master.store_code,item_master.item_desc,item_master.item_master_pkey,po_item_details.ordering_qty from po_item_details 
// join purchase_order on (po_item_details.po_fkey = purchase_order.po_pkey)
// join material_request on (material_request.mr_pkey = po_item_details.mr_fkey)
// join store_master on (store_master.store_master_pkey = material_request.store_code)
// join item_master on (item_master.item_master_pkey = po_item_details.item_code)
//  where store_master.store_master_pkey = '$store' and po_pkey = '$po_key' and po_item_details.status = 1");
//         $today = date('Y-m-d'); 
//         $arr_purchase_new = array();
//         foreach ($arr_purchase as $key){
//             $item = $key['item_master']['item_master_pkey'];
//              $pendig_qty = isset($key['0']['tos']) ? $key['po_item_details']['ordering_qty'] - $key['0']['tos'] : 0;
//        //edited ny megha on 5-06-2025   
// if($pendig_qty > 0){
//             $arr_purchase_item = $this->PurchaseOrder->query("select sum(item_qty) qtyuptodate  from
// (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
// item_except_grn_allocation_view where creation_date <= '$today' and store_master_pkey= '$store'  
// group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc
// union all
// select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
// grn_stock_details_date_view where gr_date <= '$today' and store_master_pkey= '$store'  group by store_master_pkey,item_master_pkey,item_code,item_desc
// union all
// select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty
// from Item_allocation_details_view where date_allocated <= '$today' and store_master_pkey= '$store'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
// join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
// grn_stock_det_rate_date_view where gr_date <= '$today' and store_master_pkey= '$store'  group by store_master_pkey,item_master_pkey,item_code,item_desc) b
// on (a.store_master_pkey= b.store_master_pkey
// and a.item_master_pkey=b.item_master_pkey) and a.item_master_pkey = $item
// group by a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,
// b.po_rate");
//             $qty = isset($arr_purchase_item['0']['0']['qtyuptodate'])?$arr_purchase_item['0']['0']['qtyuptodate']:'';
//             $key['0']['current_stock'] = isset($qty)?$qty:'';
//             $arr_purchase_new[] = $key;
// }
// if(empty($arr_purchase_new)){
// $arr_purchase_item = $this->PurchaseOrder->query("select sum(item_qty) qtyuptodate  from
// (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
// item_except_grn_allocation_view where creation_date <= '$today' and store_master_pkey= '$store'  
// group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc
// union all
// select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
// grn_stock_details_date_view where gr_date <= '$today' and store_master_pkey= '$store'  group by store_master_pkey,item_master_pkey,item_code,item_desc
// union all
// select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty
// from Item_allocation_details_view where date_allocated <= '$today' and store_master_pkey= '$store'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
// join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
// grn_stock_det_rate_date_view where gr_date <= '$today' and store_master_pkey= '$store'  group by store_master_pkey,item_master_pkey,item_code,item_desc) b
// on (a.store_master_pkey= b.store_master_pkey
// and a.item_master_pkey=b.item_master_pkey) and a.item_master_pkey = $item
// group by a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,
// b.po_rate");
//               $qty = isset($arr_purchase_item['0']['0']['qtyuptodate'])?$arr_purchase_item['0']['0']['qtyuptodate']:'';
//             $key['0']['current_stock'] = isset($qty)?$qty:'';
//             $arr_purchase_new[] = $key;
// }
//         }
//        // debug($arr_purchase_new);
//         $this->layout = null;
//         isset( $arr_purchase_new) ?  $arr_purchase_new: '';
//         $this->set("arr_att", $arr_purchase_new);
//         $this->set("pk", $pk);
//     }

 public function form($po_key = 0, $pk = 0, $store = 0) {
        $arr_request_data = $this->request->data;
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
       
$today = date('Y-m-d');

// Step 1: Fetch all PO items (same as before)
$arr_purchase = $this->PurchaseOrder->query("
    SELECT 
        IFNULL((
            SELECT SUM(received_qty) 
            FROM gr_item_details 
            WHERE po_fkey = po_item_details.po_fkey 
              AND item_code = po_item_details.item_code 
              AND grn_fkey IN (
                  SELECT grn_pkey 
                  FROM goods_receved_notes 
                  WHERE store_code = '$store'
              )
        ), 0) AS tos,
        item_master.item_master_pkey,
        item_master.item_desc,
        po_item_details.ordering_qty,
        po_item_details.required_qty,
        purchase_order.remarks,po_number,po_date,supplier_name,purchase_order.expected_date,purchase_order.po_pkey
    FROM po_item_details 
    JOIN purchase_order ON (po_item_details.po_fkey = purchase_order.po_pkey)
    JOIN material_request ON (material_request.mr_pkey = po_item_details.mr_fkey)
    JOIN store_master ON (store_master.store_master_pkey = material_request.store_code)
    JOIN item_master ON (item_master.item_master_pkey = po_item_details.item_code)
    WHERE store_master.store_master_pkey = '$store' 
      AND po_pkey = '$po_key' 
      AND po_item_details.status = 1
");

// Step 2: Extract all item_master_pkey values
$item_ids = array_map(function ($row) {
    return $row['item_master']['item_master_pkey'];
}, $arr_purchase);

if (empty($item_ids)) {
    $this->set("arr_att", []);
    return;
}

$item_ids_str = implode(',', $item_ids);

// Step 3: Run ONE query to fetch all current stock for these items
$stock_query = "
    SELECT a.item_master_pkey, SUM(a.item_qty) AS qtyuptodate
    FROM (
        SELECT item_master_pkey, SUM(item_qty) AS item_qty
        FROM item_except_grn_allocation_view
        WHERE creation_date <= '$today' 
          AND store_master_pkey = '$store' 
          AND item_master_pkey IN ($item_ids_str)
        GROUP BY item_master_pkey

        UNION ALL

        SELECT item_master_pkey, SUM(item_qty) AS item_qty
        FROM grn_stock_details_date_view
        WHERE gr_date <= '$today' 
          AND store_master_pkey = '$store' 
          AND item_master_pkey IN ($item_ids_str)
        GROUP BY item_master_pkey

        UNION ALL

        SELECT item_master_pkey, (SUM(qty) - SUM(returned_qty)) * -1 AS item_qty
        FROM Item_allocation_details_view
        WHERE date_allocated <= '$today' 
          AND store_master_pkey = '$store' 
          AND item_master_pkey IN ($item_ids_str)
        GROUP BY item_master_pkey
    ) a
    GROUP BY a.item_master_pkey
";

$stock_results = $this->PurchaseOrder->query($stock_query);

// Step 4: Create a stock map for easy access
$stock_map = [];

foreach ($stock_results as $row) {
    $data= array();
      $data[] = isset($row['0']) ? $row[0] : null;
      $data[] = isset($row['a']) ? $row['a'] : null;
   
  if ($data && isset($data['1']['item_master_pkey'])) {
        $stock_map[$data['1']['item_master_pkey']] = $data['0']['qtyuptodate'];
    }
}
// Step 5: Add stock data into your result array
foreach ($arr_purchase as &$item) {
    $item_id = $item['item_master']['item_master_pkey'];
    $item['0']['current_stock'] = isset($stock_map[$item_id]) ? $stock_map[$item_id] : 0;
}
  // Step 6: Set data to the view
  $this->set("arr_att", $arr_purchase);
  $this->set("pk", $pk);
    }

//edit form  
    public function edit($id = 0) {
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->Item->useDbConfig = $this->Session->read('ds');
//all item        
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
//debug($all_item);
        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
        $arr_att = $this->GoodsReceivedNotes->query("SELECT  goodsrecevednotes.*,gritemdetails.*,purchaseorder.*  "
                . "from goods_receved_notes as goodsrecevednotes "
                . "left join gr_item_details as gritemdetails on (gritemdetails.grn_fkey = goodsrecevednotes.grn_pkey) "
                . "left join purchase_order as purchaseorder on (purchaseorder.po_pkey = gritemdetails.po_fkey) "
                . "where goodsrecevednotes.grn_pkey = '$id' and goodsrecevednotes.status = '1'   ");

        $this->layout = null;
        $this->set("arr_att", $arr_att);
        $this->set("pk", $id);
    }

//delete
    public function grndelete() {
        $this->autoRender = FALSE;
        $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["grn_pkey"])) {
            $ar_ids = explode(",", $_REQUEST["grn_pkey"]);
            //debug($ar_ids);
            $this->GoodsReceivedNotes->updateAll(
                    array('GoodsReceivedNotes.status' => 0), array('GoodsReceivedNotes   .grn_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

// save form
    public function save($po_pkey = 0) {
        $this->autoRender = FALSE;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
        $this->GoodsReceivedNotesitem->useDbConfig = $this->Session->read('ds');
        //debug($arr_form_data);
        if ($arr_form_data['grn_fkey'] == '') {
            //debug($arr_form_data);
            //$arr_form_data['gr_date'] = date("Y-m-d");
            $this->GoodsReceivedNotes->save($arr_form_data);
//            $this->StockStoreTranfer->save($arr_form_data);

            $getlastid = isset($arr_form_data['grn_pkey']) && !empty($arr_form_data['grn_pkey']) ? $arr_form_data['grn_pkey'] : $this->GoodsReceivedNotes->getInsertid();
            $getlastStoreid = isset($arr_form_data['stock_store_tranfer_pkey']) && !empty($arr_form_data['stock_store_tranfer_pkey']) ? $arr_form_data['stock_store_tranfer_pkey'] : $this->StockStoreTranfer->getInsertid();
            
            $itemkey = $arr_form_data['grn_pkey'] = $getlastid;
        } else {
            $arr = array();
            $count = count($arr_form_data['item_code']);
            $arr = $arr_form_data;
            $arr_form_data['gr_item_pkey'] = isset($arr_form_data['gr_item_pkey']) ? $arr_form_data['gr_item_pkey'] : 0;
            $arr_form_data['grn_fkey'] = $arr_form_data['grn_fkey'];

            if ($arr_form_data['po_fkey']) {
                $arr_postatus_data['po_pkey'] = $arr_form_data['po_fkey'];
                $arr_postatus_data ['grn_status'] = 0;
                $arr_postatus_data['po_date'] = date("Y-m-d",strtotime($arr_form_data['po_date']));
                $this->PurchaseOrder->save($arr_postatus_data);
            }

            for ($i = 0; $i < $count; $i++) {
                $arr_form_data['item_code'] = isset($arr['item_code'][$i]) ? $arr['item_code'][$i] : 0;
                $arr_form_data['ordering_qty'] = isset($arr['ordering_qty'][$i]) ? $arr['ordering_qty'][$i] : 0;
                $arr_form_data['received_qty'] = isset($arr['required_qty'][$i]) ? $arr['required_qty'][$i] : 0;
                $arr_form_data['current_stock'] = isset($arr['current_stock'][$i]) ? $arr['current_stock'][$i] : 0;
                $arr_form_data['re_order_level'] = isset($arr['re_order_level'][$i]) ? $arr['re_order_level'][$i] : 0;
                $arr_form_data['package'] = isset($arr['package'][$i]) ? $arr['package'][$i] : 0;
                $arr_form_data['gr_item_pkey'] = isset($arr['gr_item_pkey'][$i]) ? $arr['gr_item_pkey'][$i] : 0;
                $arr_form_data['mr_fkeys'] = isset($arr['mr_fkeys'][$i]) ? $arr['mr_fkeys'][$i] : 0;
//                $this->GrItemDetails->query("update mr_details set ordering_qty  = ordering_qty+$ordering_qty,po_status = '2' where mr_details_pkey = $mr_details_pkey");
                $this->GrItemDetails->save($arr_form_data);
                $grdetailsid = $this->GrItemDetails->getInsertid();
                $this->UpdateStock($grdetailsid,$arr_form_data['received_qty'],$arr_form_data['mr_fkeys']);
                
            }


            $itemkey = $arr_form_data['grn_fkey'];
            //$storeitemkey = $arr_form_data['stock_store_tranfer_fkey'];
        }
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $itemkey));
    }
    
    public function UpdateStock($gritemsid = 0,$Qty = 0,$mr_number = 0) {
        $this->StockDetails->useDbConfig = $this->Session->read('ds');
        $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
        $joins = array(
            array(
                'table' => 'goods_receved_notes',
                'alias' => 'GRNotes',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('GrItemDetails.grn_fkey = GRNotes.grn_pkey')
            ),
            array(
                'table' => 'purchase_order',
                'alias' => 'Purchaseorder',
                'foreignKey' => false,
                'conditions' => array('GrItemDetails.po_fkey = Purchaseorder.po_pkey')
            ),
            array(
                'table' => 'item_master',
                'alias' => 'ItemMaster',
                'foreignKey' => false,
                'conditions' => array('GrItemDetails.item_code = ItemMaster.item_master_pkey')
            )
        );
        $fields = array("GRNotes.store_code,Purchaseorder.supplier_code,Purchaseorder.po_number,ItemMaster.item_master_pkey,GrItemDetails.po_rate");
        $created_by = $this->Session->read('user_name');
        $arr_gr = $this->GrItemDetails->find("all",array("joins"=>$joins,"fields"=>$fields,"conditions"=>array("gr_item_pkey"=>$gritemsid)));
        $arr_save_data = array();
        $arr_save_data['store_fkey'] = isset($arr_gr['0']['GRNotes']['store_code'])?$arr_gr['0']['GRNotes']['store_code']:"0";
        $arr_save_data['supplier_fkey'] = $arr_gr['0']['Purchaseorder']['supplier_code'];
        $arr_save_data['item_qty'] = $Qty;
        $arr_save_data['purchase_rate'] = $arr_gr['0']['GrItemDetails']['po_rate'];
        $arr_save_data['item_fkey'] = $arr_gr['0']['ItemMaster']['item_master_pkey'];
        $arr_save_data['created_by'] = $created_by;
        $arr_save_data['varified_by'] = $created_by;
        $arr_save_data['mr_no'] = $mr_number;
        $arr_save_data['po_no'] = $arr_gr['0']['Purchaseorder']['po_number'];
        $this->StockDetails->saveAll($arr_save_data);
        return true;
    }

//Load table 
    public function loadtable($id, $rowindex = 1) {
        $this->autoRender = false;
        $this->GrItemDetails->useDbConfig = $this->Session->read('ds');
        $arr_find_goods = $this->GrItemDetails->query("SELECT  goodsrecevednotes.*,gritemdetails.*,purchaseorder.*,itemmaster.*  "
                . "from goods_receved_notes as goodsrecevednotes "
                . "left join gr_item_details as gritemdetails on (gritemdetails.grn_fkey = goodsrecevednotes.grn_pkey) "
                . "left join purchase_order as purchaseorder on (purchaseorder.po_pkey = gritemdetails.po_fkey) "
                . "left join item_master as itemmaster on (itemmaster.item_master_pkey = gritemdetails.item_code) "
                . "where goodsrecevednotes.grn_pkey = '$id' and goodsrecevednotes.status = '1' ");
        $data = '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red;">';
        $data .=' <thead>';
        $data .=' <tr class="warning">';
        $data .=' <th>Sl no</th>';
        $data .=' <th>PO Number</th>';
        $data .=' <th>Po Date</th>';
        $data .=' <th>Expected Date</th>';
        $data .=' <th>Location</th>';
        $data .=' <th>Supplier Name</th>';
        $data .=' <th>Supplier Code</th>';
        $data .=' <th>Item Code</th>';
        $data .=' <th>Ordering qty</th>';
        $data .=' <th>Received qty</th>';


        //$data .=' <th>Action</th>';
        $i = 1;

        foreach ($arr_find_goods as $value) {
            $class = ($i % 2) ? 'info' : 'danger';
            $pid = $value["goodsrecevednotes"]["grn_pkey"];

            $data .= '<tr>';
            $data .= '<td> ' . $i . '</td>';
            $data .= '<td> ' . $value["purchaseorder"]["po_number"] . '</td>';
            $data .= '<td> ' . $value["purchaseorder"]["po_date"] . '</td>';
            $data .= '<td> ' . $value["purchaseorder"]["expected_date"] . '</td>';
            $data .= '<td> ' . $value["purchaseorder"]["location"] . '</td>';
            $data .= '<td> ' . $value["purchaseorder"]["supplier_name"] . '</td>';
            $data .= '<td> ' . $value["purchaseorder"]["supplier_code"] . '</td>';
            $data .= '<td> ' . $value["itemmaster"]["item_desc"] . '</td>';
            $data .= '<td> ' . $value["gritemdetails"]["ordering_qty"] . '</td>';
            $data .= '<td> ' . $value["gritemdetails"]["received_qty"] . '</td>';


            $data .=' <input name="grn_pkey" id="grn_pkey" type="hidden"  value="' . $value["goodsrecevednotes"]["grn_pkey"] . '" >';
            //$data .= '<td> <a href="#" class="  glyphicon glyphicon-pencil"onclick="editdaata(' . $rowindex . ',' . $pid . ');">&nbsp</a>';
            $i++;
        }
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '</table>';
        echo $data;
    }

//find item name       
    public function getautocompletionsgr_number() {
        $this->autoRender = false;
        if (isset($_REQUEST['gr_number']) && !empty($_REQUEST['gr_number'])) {
            $searchkey = $_REQUEST['gr_number'];
            $filter_condition = 'gr_number LIKE "%' . $searchkey . '%"';
            $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
            $arr_item = $this->GoodsReceivedNotes->query("SELECT * FROM `goods_receved_notes` WHERE `status`=1 and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_item as $val) {
                $arr_filterresult[] = isset($val['goods_receved_notes']) ? $val['goods_receved_notes'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }

// form store    
    public function getautocompletionsstore_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['store_code']) && !empty($_REQUEST['store_code'])) {
            $searchkey = $_REQUEST['store_code'];
            $filter_condition = 'store_code LIKE "%' . $searchkey . '%"';
            //debug($filter_condition);


            if ($this->Session->read('user_group') == 2) {
                $emp_fkey = $this->Session->read('emp_fkey');
                $conditions = "and store_master_pkey in(select store_pkey from access_store where emp_fkey = '$emp_fkey' and status=1)";
            } else {
                $conditions = "";
            }
            
            $this->Store->useDbConfig = $this->Session->read('ds');
            $arr_store = $this->Store->query("SELECT * FROM `store_master` WHERE `status`=1 and $filter_condition $conditions");
            $arr_filterresult = array();
            foreach ($arr_store as $val) {
                $arr_filterresult[] = isset($val['store_master']) ? $val['store_master'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }

    public function searchm($po_number = '') {
        $this->autoRender = false;
        if (isset($_REQUEST['po_number']) && !empty($_REQUEST['po_number'])) {
            $searchkey = $_REQUEST['po_number'];
            $filter_condition = 'po_number LIKE "%' . $searchkey . '%"';

            //debug($filter_condition);
            $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
            $arr_item = $this->PurchaseOrder->query("SELECT * FROM `purchase_order` WHERE `status`=1 and $filter_condition");
            // debug($arr_item);
            $arr_filterresult = array();
            foreach ($arr_item as $val) {
//              $val['DirectPurchaseOrder']['name']=$val[0]['name'];
                $arr_filterresult[] = isset($val['purchase_order']) ? $val['purchase_order'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }

    public function save1() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
//create by 
        //debug($arr_form_data);
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $arr_form_po['grn_pkey'] = $arr_form_data['grn_pkey'];
        $arr_form_po['grstatus'] = 0;
        $this->PurchaseOrder->save($arr_form_po);

        $this->GoodsReceivedNotes->save($arr_form_data);

//mutiple save    

        $arr = array();
        $count = count($arr_form_data['item_code']);
        $arr = $arr_form_data;
        for ($i = 0; $i < $count; $i++) {
            if (isset($arr_form_data['gr_item_pkey']) && !empty($arr_form_data['gr_item_pkey'])) {
                $arr_form_data['gr_item_pkey'] = $arr['gr_item_pkey'][$i];
            }

            $arr_form_data['item_code'] = $arr['item_code'][$i];
            $arr_form_data['item_description'] = $arr['item_description'][$i];
            $arr_form_data['uom'] = $arr['uom'][$i];
            $arr_form_data['ordering_qty'] = $arr['ordering_qty'][$i];
            $arr_form_data['order_qty'] = $arr['order_qty'][$i];
            $arr_form_data['po_rate'] = $arr['po_rate'][$i];

//last  insert id
            if ($arr_form_data['grn_pkey'] == '') {

                $arr_form_data['grn_fkey'] = $this->GoodsReceivedNotes->getInsertID();
            } else {
                $arr_form_data['grn_fkey'] = $arr['grn_pkey'];
            }


            //debug($arr_form_data);
            //die();
            $this->GoodsReceivedNotesitem->saveAll($arr_form_data);
            //die();
        }
        // debug($result);
    }

    public function goodlist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;

        $this->GoodsReceivedNotesitem->useDbConfig = $this->Session->read('ds');
        $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();


        if (isset($_REQUEST['po_number'])) {
            $item = $_REQUEST['po_number'];
            $conditions = "and mrdetails.item_code = $item AND (mrdetails.required_qty-mrdetails.ordering_qty) > 0";
            $resp_att = array();
            $resp_att["rows"] = array();
            $count = $this->GoodsReceivedNotes->find("count", array("conditions" => array('status' => 1)));
            $arr_meterial = $this->GoodsReceivedNotes->query("SELECT materialrequest.*,storemaster.*,mrdetails.* "
                    . "FROM material_request as materialrequest "
                    . "left join mr_details as mrdetails on (mrdetails.mr_fkey = materialrequest.mr_pkey) "
                    . "left join store_master as storemaster on (storemaster.store_master_pkey = materialrequest.store_code) "
                    . " WHERE materialrequest.status = '1'  $conditions  ");
        } else {


            $count = $this->GoodsReceivedNotes->find("count", array("conditions" => array('status' => 1)));
            $arr_goods = $this->GoodsReceivedNotes->query("SELECT * from goods_receved_notes where status = '1'");
        }








        //debug($arr_goods);
        $out = array();
        foreach ($arr_goods as $key => $arr_goods) {
            $out['grn_pkey'] = isset($arr_goods['goods_receved_notes']['grn_pkey']) ? $arr_goods['goods_receved_notes']['grn_pkey'] : '';
            $out['supplier_name'] = isset($arr_goods['goods_receved_notes']['supplier_name']) ? $arr_goods['goods_receved_notes']['supplier_name'] : '';
            $out['gr_create'] = isset($arr_goods['goods_receved_notes']['gr_create']) ? $arr_goods['goods_receved_notes']['gr_create'] : '';
            $out['gr_date'] = isset($arr_goods['goods_receved_notes']['gr_date']) ? $arr_goods['goods_receved_notes']['gr_date'] : '';
            $out['created_by'] = isset($arr_goods['goods_receved_notes']['created_by']) ? $arr_goods['goods_receved_notes']['created_by'] : '';
            $out['remark'] = isset($arr_goods['goods_receved_notes']['remark']) ? $arr_goods['goods_receved_notes']['remark'] : '';
            $out['created_date'] = isset($arr_goods['goods_receved_notes']['created_date']) ? $arr_goods['goods_receved_notes']['created_date'] : '';


            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

}
