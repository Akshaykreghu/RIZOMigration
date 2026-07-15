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
class GoodsReceivedNotesController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout="default";
    public $name ='GoodsReceivedNotes';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('GoodsReceivedNotes','PurchaseOrder','Store','PurchaseOrderDetails','Item','GoodsReceivedNotesitem');
    public function index() 
        {
            $this->set("all_store", $all_store = $this->Store->find("all", array('conditions' => array('status' => 1))));
           
        }
   //data grid list
    public function grnlist()
        {
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
            $count = $this->GoodsReceivedNotes->find("count", array("conditions" => array('status' => 1)));
            $arr_goods = $this->GoodsReceivedNotes->query("SELECT * from goods_receved_notes where status = '1'");
                     $out = array();
//                /debug($arr_goods);
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
 

public function addnewrow() 
    {
           $this->Item->useDbConfig = $this->Session->read('ds');
           $this->set("all_item", $all_item = $this->Item->find("all", array("conditioin" => array('status' => 1))));
    }
public function form($po_key=0,$grn_pkey='')
    {
        $arr_request_data = $this->request->data;
        
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
        $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
       
        
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditioin" => array('status' => 1))));
        //debug($all_item)
        
            if($grn_pkey ==1)
        {
                 $arr_grn = $this->GoodsReceivedNotes->query("SELECT grnorder.*,grnitem.* "  
                . "from goods_receved_notes as grnorder "
                . "left join gr_item_details as grnitem on (grnitem.grn_fkey = grnorder.grn_pkey) "
               
                
                . "where grnorder.grn_pkey= '$po_key' ");
              
        }  else {
             $arr_grn = $this->PurchaseOrder->query("SELECT grnorder.*,grnitem.* "  
                . "from purchase_order as grnorder "
                . "left join po_item_details as grnitem on (grnitem.po_fkey = grnorder.po_pkey) "
               
                
                . "where grnorder.po_pkey= '$po_key' ");
        
        }
        //debug($arr_grn);
        $this->layout = null;
        $this->set("arr_grn", $arr_grn);
    }

//delete
        public function grndelete()
            {
                $this->autoRender=FALSE;
                $this->GoodsReceivedNotes->useDbConfig = $this->Session->read('ds');
                $result =   array('success' => 0 );
                if(isset($_REQUEST["grn_pkey"]))
                {
                    $ar_ids = explode(",", $_REQUEST["grn_pkey"]);
                    //debug($ar_ids);
                    $this->GoodsReceivedNotes->updateAll(
                        array('GoodsReceivedNotes.status' => 0),
                            array('GoodsReceivedNotes   .grn_pkey' => $ar_ids));
                    $result['success'] = 1;
                    $result['msg'] = "Record(s)  deleted successfully.";
                }
                echo json_encode($result);
             }

public function save() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
//create by 
        //debug($arr_form_data);
        $arr_form_data['created_by'] = $this->Session->read('user_id');
        $arr_form_po['po_pkey']=  $arr_form_data['po_pkey'];
        $arr_form_po['grstatus']=0;
        $this->PurchaseOrder->save($arr_form_po);
        
        $this->GoodsReceivedNotes->save($arr_form_data);
       
//mutiple save     
        $arr = array();
        $count = count($arr_form_data['item_code']);
        $arr = $arr_form_data;
        for ($i=0; $i<$count; $i++) {
          if(isset($arr_form_data['gr_item_pkey']) && !empty($arr_form_data['gr_item_pkey'] )){
            $arr_form_data['gr_item_pkey'] = $arr['gr_item_pkey'][$i];}
            
            $arr_form_data['item_code'] = $arr['item_code'][$i];
            $arr_form_data['item_description'] = $arr['item_description'][$i];
            $arr_form_data['uom'] = $arr['uom'][$i];
            $arr_form_data['ordering_qty'] = $arr['ordering_qty'][$i];
            $arr_form_data['order_qty'] = $arr['order_qty'][$i];
            $arr_form_data['po_rate'] = $arr['po_rate'][$i];
            $arr_form_data['po_value'] = $arr['po_value'][$i];
//last  insert id
           if ($arr_form_data['grn_pkey'] == '') { 
              
             $arr_form_data['grn_fkey'] = $this->GoodsReceivedNotes->getInsertID();
     }   
 else {
          $arr_form_data['grn_fkey'] = $arr['grn_pkey'];
     }
         

        //debug($arr_form_data);
        //die();
            $this->GoodsReceivedNotesitem->saveAll($arr_form_data);
                       //die();
        }
       // debug($result);
    }
   
        
         public function purchaselist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        
        $this->PurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $count = $this->PurchaseOrder->find("count", array("conditions" => array('status' => 1)));
        $arr_purchase = $this->PurchaseOrder->query("SELECT * from purchase_order where status = '1' and grstatus='1' ");
        $out = array();
            //debug($arr_purchase);
        foreach ($arr_purchase as $key => $allpurchase) {
            $out['po_pkey'] = isset($allpurchase['purchase_order']['po_pkey']) ? $allpurchase['purchase_order']['po_pkey'] : '';
            $out['po_number'] = isset($allpurchase['purchase_order']['po_number']) ? $allpurchase['purchase_order']['po_number'] : '';
            $out['client_name'] = isset($allpurchase['purchase_order']['client_name']) ? $allpurchase['purchase_order']['client_name'] : '';
            $out['po_date'] = isset($allpurchase['purchase_order']['po_date']) ? $allpurchase['purchase_order']['po_date'] : '';
            $out['expected_date'] = isset($allpurchase['purchase_order']['expected_date']) ? $allpurchase['purchase_order']['expected_date'] : '';
            $out['location'] = isset($allpurchase['purchase_order']['location']) ? $allpurchase['purchase_order']['location'] : '';
            $out['remark'] = isset($allpurchase['purchase_order']['remark']) ? $allpurchase['purchase_order']['remark'] : '';
            
         
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }


}
