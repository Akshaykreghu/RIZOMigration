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
class DirectPurchaseOrderController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'DirectPurchaseOrder';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('DirectPurchaseOrderDetails','DirectPurchaseOrder','Item','Store','QuantityDetails','AdditionalDetails','ItemDetails','WarrantyDetails','Contacts','Site','UoMaster');

   
public function home(){

}

    public function index() {
        $this->layout = NULL;
		$this->Site->useDbConfig = $this->Session->read('ds');
		$this->Item->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
//all item      
         $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status'=>1))));
         //debug($all_item);
//site
        $this->set("all_site",$all_site = $this->Site->find("all", array("conditions" => array('status' => 1))));
        //debug($all_site);
        
        
        
    }
//data grid list
   
   
    public function addnewrow($rowIndex = 1) {
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditioin" => array('status' => 1))));
        
        $this->set('rowIndex',$rowIndex);
    }

   

//form save
    public function save() {
        $this->autoRender = FALSE;
		$this->DirectPurchaseOrder->useDbConfig = $this->Session->read('ds');
		$this->DirectPurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;

        $arr_form_data['created_by'] = $this->Session->read('user_id');

        $this->DirectPurchaseOrder->save($arr_form_data);
        $getlastid = isset($arr_form_data['direct_po_pkey']) && !empty($arr_form_data['direct_po_pkey']) ? $arr_form_data['direct_po_pkey'] : $this->DirectPurchaseOrder->getInsertid();
//find item in ItemMaster
        $allitem = $this->getitem($arr_form_data['item_code']);
        //debug($allitem);
        $itemkey = $arr_form_data['direct_po_pkey'] = $getlastid;
        //debug($itemkey);
        $arr_form_data['direct_po_fkey'] = $getlastid;
// nee  
        foreach ($allitem as $value) {
//            $arr_form_data['package'] = $value['itemdetails']['package'];
          $arr_form_data['uom'] = $value['itemdetails']['uom'];
       }

        $this->DirectPurchaseOrderDetails->saveAll($arr_form_data);
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $itemkey));
    }
//Item Master search
public function getitem($id='')
            {
			$this->Item->useDbConfig = $this->Session->read('ds');
                       $this->autoRender = false;
                       
                   $arr_item = $this->Item->query("SELECT itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*"
                           . " from item_master as itemmaster "
                           . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                           . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                           . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                           . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                           . "where itemmaster.item_master_pkey = '$id' ");

                   return $arr_item;
               }
//Load table 
       public function loadtable($id, $rowindex = 1)
                {
        $this->autoRender = false;
        //debug($id);
        $this->DirectPurchaseOrder->useDbConfig = $this->Session->read('ds');

        $arr_direct_master = $this->DirectPurchaseOrder->query("SELECT directpurchaseorder.*,directpodetails.*,itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*,uommaster.*"
                . "from direct_purchase_order as directpurchaseorder "
                . "left join direct_po_details as directpodetails on (directpurchaseorder.direct_po_pkey = directpodetails.direct_po_fkey) "
                . "left join item_master as itemmaster on (directpodetails.item_code = itemmaster.item_master_pkey)"
                . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                . "left join uomaster as uommaster on (uommaster.uom_pkey = itemdetails.uom) "
                . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                . "where directpurchaseorder.direct_po_pkey = '$id' and directpodetails.status = '1' ");

        //debug($arr_direct_master);
        
        
        
        $data = '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red;">';
        $data .=' <thead>';
        $data .=' <tr class="warning">';
        $data .=' <th> Sl no </th>';
        $data .=' <th> Item Name </th>';
        $data .=' <th>Ordering Qty* </th>';
        $data .=' <th>UOM</th> ';
        $data .=' <th>PO value</th>';
        $data .=' <th>Action</th>';
        $i = 1;
            
        foreach ($arr_direct_master as $value) {
            $class = ($i % 2) ? 'info' : 'danger';
           
             $pid = $value["directpodetails"]["direct_po_details_pkey"];
             
            $data .= '<tr class="' . $class . '">';
            $data .= '<td> ' . $i . '</td>';

            $data .= '<td> ' . $value["itemmaster"]["item_desc"] . '</td>';
            $data .= '<td> ' . $value["directpodetails"]["ordered_qty"] . '</td>';
            $data .= '<td> ' . $value["uommaster"]["unit"] . '</td>';
            $data .= '<td> ' . $value["directpodetails"]["po_rate"] . '</td>';
            
            

                $data .= '<td> <a href="#" class="  glyphicon glyphicon-pencil"onclick="editdaata(' . $rowindex . ',' . $pid . ');">&nbsp</a>'
                        . ' <a href="#" class=" glyphicon glyphicon-remove btn-danger" onclick="removedaata(' . $rowindex . ',' . $pid . ');"></a></td>';

            $i++;
        }
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '</table>';




        echo $data;
    }  
    
public function editorder($id)
    {
           $this->DirectPurchaseOrder->useDbConfig = $this->Session->read('ds');
           $this->Item->useDbConfig = $this->Session->read('ds');
//all item      
         $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
        $arr_direct_master = $this->DirectPurchaseOrder->query("SELECT directpurchaseorder.*,directpodetails.*,itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*"
                . "from direct_purchase_order as directpurchaseorder "
                . "left join direct_po_details as directpodetails on (directpurchaseorder.direct_po_pkey = directpodetails.direct_po_fkey) "
                . "left join item_master as itemmaster on (directpodetails.item_code = itemmaster.item_master_pkey)"
                . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                
                . "where directpodetails.direct_po_details_pkey = '$id' ");
        
                 
                    foreach ($arr_direct_master as $key => $value) {
                            $out['direct_po_fkey'] = isset($value['directpodetails']['direct_po_fkey']) ? $value['directpodetails']['direct_po_fkey'] : '';
                    }
           $this->set('id',$id);
          $this->set('arr_direct_master',$arr_direct_master);
          
    }
//edit save
      public function editordersave() {
        $this->autoRender = false;
		$this->DirectPurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $masterpk = $arr_form_data['direct_po_fkey'];
        $this->DirectPurchaseOrderDetails->save($arr_form_data);

        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $masterpk));
    }

//search 
     public function getautocompletionsdirect_po_number(){
       
       $this->autoRender = false;

        
           if(isset($_REQUEST['direct_po_number']) && !empty($_REQUEST['direct_po_number'])){
               $searchkey = $_REQUEST['direct_po_number'];
            $filter_condition = 'direct_po_number LIKE "%' . $searchkey . '%"';
 
            //debug($filter_condition);
        $this->DirectPurchaseOrder->useDbConfig = $this->Session->read('ds');
         $arr_Emp = $this->DirectPurchaseOrder->query("SELECT * FROM `direct_purchase_order` WHERE `status`=1 and $filter_condition");
        
        $arr_filterresult = array();
        foreach ($arr_Emp as $val) {
//              $val['DirectPurchaseOrder']['name']=$val[0]['name'];
            $arr_filterresult[] = isset($val['direct_purchase_order']) ? $val['direct_purchase_order'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    }
//delete 
         public function delete($id=0){
          $this->autoRender = false;
		  $this->DirectPurchaseOrder->useDbConfig = $this->Session->read('ds');
        if($id != 0){
            $this->DirectPurchaseOrder->updateAll(array('status'=>0),array('direct_po_pkey'=>$id));
 
          
            echo json_encode(array('msg' => 'Material Request deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Material Request deletion failed!'));
        }
    }
 //delete sup table
    public function deleteorder($id=0){
       $this->DirectPurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
          $this->autoRender = false;
        if($id != 0){
            $this->DirectPurchaseOrderDetails->updateAll(array('status'=>0),array('direct_po_details_pkey'=>$id));
 
          
            echo json_encode(array('msg' => 'Direct Order deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Direct Order  deletion failed!'));
        }
    } 
       
    
    
    
    }

 
    

   
