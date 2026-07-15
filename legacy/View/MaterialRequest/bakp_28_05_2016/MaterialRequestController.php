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
class MaterialRequestController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout="default";
    public $name ='MaterialRequest';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('MaterialRequest','MaterialRequestDetails','Store','Item','PackageMaster','QuantityDetails','AdditionalDetails','ItemDetails','WarrantyDetails','Contacts','Site');
    
    
    public function home()
    {
        
    }

    public function index() {
        
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $this->PackageMaster->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
//all item      
         $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status'=>1))));
         
         
         
         //debug($all_item);
//all store
        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
        //debug($all_store);
//package
       $this->set("all_package", $all_package = $this->PackageMaster->find("all", array("conditions" => array('status' => 1))));
      // debug($all_package) ;
//Contacts
       $this->set("all_contact",$all_contact = $this->MaterialRequest->query("SELECT * FROM `contacts` WHERE `relationship`='customer' AND status='1' "));
       //debug($all_contact);
//site
        $this->set("all_site",$all_site = $this->Site->find("all", array("conditions" => array('status' => 1))));
        //debug($all_site);
    }

public function addnewrow() 
    {
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));  
    }
public function form($mr_pkey=0)
 {  
         if($mr_pkey == 0){
            $title = 'ADD MATERIAL REQUEST';
        }else{
            $title = 'EDIT MATERIAL REQUEST';
        }
        $this->set('title', $title);
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
        
         $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
       //debug($all_item);
        
        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
        $arr_att = $this->MaterialRequest->query("SELECT material_request.*,mr_details.*"
                . " from material_request as material_request "
                . "left join mr_details as mr_details on (material_request.mr_pkey = mr_details.mr_fkey) "
                . "where material_request.mr_pkey = '$mr_pkey' ");
        $this->layout = null;
        $this->set("arr_att", $arr_att);
    }
//data grid list
        public function materiallist()
            {

                     $this->autoRender = FALSE;
                     $arr_request_data = $this->request->data;
                     $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
                     $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
                    
                    $limit = $_REQUEST['rows'];
                    $page = $_REQUEST['page'];
                    $ofst = ($page - 1) * $limit;
                    $this->datatable["conditions"] = array('status' => 1);
                    $resp_att = array();
                    $resp_att["rows"] = array();
                   
                    $count = $this->MaterialRequest->find("count",array("conditions" => array('status' => 1)));

                    $this->set("all_store",$all_store=$this->Store->find("all",array("conditions"=>array('status'=>1))));
                    //debug($all_store);
                    $arr_att = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.* "
                            . "from material_request as materialrequest "
                            . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                           
                            . "where materialrequest.status = '1'  ");
//                            
                 // debug($arr_att);
                    $out = array();
                        foreach ($arr_att as $key => $value) {
                            $out['mr_pkey'] = isset($value['materialrequest']['mr_pkey']) ? $value['materialrequest']['mr_pkey'] : '';
                            $out['mr_code'] = isset($value['materialrequest']['mr_code']) ? $value['materialrequest']['mr_code'] : '';
                            $out['customer_name'] = isset($value['materialrequest']['customer_name']) ? $value['materialrequest']['customer_name'] : '';
                            $out['location'] = isset($value['materialrequest']['location']) ? $value['materialrequest']['location'] : '';
                            $out['mr_date'] = isset($value['materialrequest']['mr_date']) ? $value['materialrequest']['mr_date'] : '';
                            $out['store_code'] = isset($value['storemaster']['store_code']) ? $value['storemaster']['store_code'] : '';
                            $out['created_by'] = isset($value['materialrequest']['created_by']) ? $value['materialrequest']['created_by'] : '';


                            $resp_att["rows"][$key] = $out;
                        }
                    $resp_att["total"] = $count;
                    echo json_encode($resp_att);
            }

//delete
        public function materialdelete()
            {
                $this->autoRender=FALSE;
                $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
                $result =   array('success' => 0 );
                if(isset($_REQUEST["mr_pkey"]))
                {
                    $ar_ids = explode(",", $_REQUEST["mr_pkey"]);
                    //debug($ar_ids);
                    $this->MaterialRequest->updateAll(
                        array('MaterialRequest.status' => 0),
                            array('MaterialRequest.mr_pkey' => $ar_ids));
                    $result['success'] = 1;
                    $result['msg'] = "Record(s)  deleted successfully.";
                }
                echo json_encode($result);
             }
//form save
public function save() 
   {
            $this->autoRender = FALSE;
            $this->layout = null;
            $result = array('success' => 0);
            $arr_form_data = $this->request->data;
            $arr_form_data['created_by'] = $this->Session->read('user_id');
            $this->MaterialRequest->save($arr_form_data);
            $getlastid = isset($arr_form_data['mr_pkey']) && !empty($arr_form_data['mr_pkey']) ? $arr_form_data['mr_pkey'] : $this->MaterialRequest->getInsertid();
            $itemid = $this->getitemid($arr_form_data['item_code']);
            $arr_form_data['item_code']=$itemid;
            $allitem = $this->getitem($arr_form_data['item_code']);
            $itemkey = $arr_form_data['mr_pkey'] = $getlastid;
            $arr_form_data['unit']=$arr_form_data['required_qty'];     
            $arr_form_data['mr_fkey'] = $getlastid;
                                 foreach ($allitem as $value)
                                     {
                                        $arr_form_data['package'] = $value['itemdetails']['package'];
                                        $arr_form_data['re_order_level'] = $value['qtydetails']['re_order_level'];
                                      }                           
                               
        $this->MaterialRequestDetails->saveAll($arr_form_data);
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $itemkey));
    }
//get itemid
     public function getitemid($name='')
            {
                       $this->autoRender = false;
                       $this->Item->useDbConfig = $this->Session->read('ds');
                       $arr_item_id= $this->Item->query("SELECT *  from item_master  where item_desc = '$name' ");
                       $id=$arr_item_id['0']['item_master']['item_master_pkey'];
                       return $id;
                  
               }
    
    
//get itemdettails
    public function getitem($id='')
            {
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
               
               
//down table
    public function loadtable($id, $rowindex = 1)
                {
        $this->autoRender = false;
        //debug($id);
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');

        $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*"
                            . "from material_request as materialrequest "
                            . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                            ."left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                            . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                            . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                            . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                            . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                
                            . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                
                           
                           
                           
                
                           . "where materialrequest.mr_pkey = '$id' and mrdetails.status = '1' ");

                   //debug($arr_material_master);
        
        
        
        $data = '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red;">';
        $data .=' <thead>';
        $data .=' <tr class="warning">';
        $data .=' <th> Sl no </th>';
        $data .=' <th> Item Name </th>';
        $data .=' <th> Item Code </th>';
        $data .=' <th>Required Qty* </th>';
        $data .= '  <th>Current Stock</th> ';
//        $data .= '  <th>Incoming Qty</th>';
//        $data .= '<th>Incoming Date</th>';
        $data .= '  <th>Re Order Level</th>';
        $data .= '  <th>Package</th>';
        $data .= '  <th>Action<getitem/th>';
        
        $i = 1;
//        $qty = 0;
//        $amt = 0;
//        $gross = 0;
       // debug($arr_material_master);
        foreach ($arr_material_master as $value) {
           // debug($value);
            $class = ($i % 2) ? 'info' : 'danger';
           
             $pid = $value["mrdetails"]["mr_details_pkey"];
//            $total_qty = $qty + $value["SalesOrderDetails"]["item_qty"];
//            $total_amt = $amt + $value["SalesOrderDetails"]["total_amt"];
//            $gross_amt = $gross + $value["SalesOrderDetails"]["gross_amt"];
            $data .= '<tr class="' . $class . '">';
            $data .= '<td> ' . $i . '</td>';

            $data .= '<td> ' . $value["itemmaster"]["item_desc"] . '</td>';
            $data .= '<td> ' . $value["itemmaster"]["item_code"] . '</td>';
            $data .= '<td> ' . $value["mrdetails"]["required_qty"] . '</td>';
            $data .= '<td> ' . $value["mrdetails"]["current_stock"] . '</td>';
//            $data .= '<td> ' . $value["mrdetails"]["incoming_qty"] . '</td>';
//            $data .= '<td> ' . $value["mrdetails"]["incoming_date"] . '</td>';
            $data .= '<td> ' . $value["qtydetails"]["re_order_level"] . '</td>';
            $data .= '<td> ' . $value["itemdetails"]["package"] . '</td>';
            
            
//            if (isset($value["materialrequest"]["item_rate"]) && !empty($value["SalesOrderDetails"]["item_rate"])) {
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
           $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
           $this->Item->useDbConfig = $this->Session->read('ds');
//all item      
        //debug($id);
        
         $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
         
         $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.* "
                            . "from material_request as materialrequest "
                            . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                            ."left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                            . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                            . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                            . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                            . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                
                            . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                
                           
                           
                           
                
                           . "where mrdetails.mr_details_pkey = '$id'  ");
        
   
                    foreach ($arr_material_master as $key => $value) {
                            $out['mr_fkey'] = isset($value['mrdetails']['mr_fkey']) ? $value['mrdetails']['mr_fkey'] : '';
                           
                    }

           $this->set('id',$id);
          $this->set('arr_material_master',$arr_material_master);
    }
    
     public function editordersave() {
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $masterpk = $arr_form_data['mr_fkey'];
        $allitem = $this->getitem($arr_form_data['item_code']);
        $arr_form_data['unit'] = $arr_form_data['required_qty'];
        foreach ($allitem as $value) {
            $arr_form_data['package'] = $value['itemdetails']['package'];
            $arr_form_data['re_order_level'] = $value['qtydetails']['re_order_level'];
        }
        $this->MaterialRequestDetails->save($arr_form_data);
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $masterpk));
    }

    public function deleteordermaster($id=0){
          $this->autoRender = false;
        if($id != 0){
            $this->MaterialRequest->updateAll(array('status'=>0),array('mr_pkey'=>$id));
 
          
            echo json_encode(array('msg' => 'Material Request deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Material Request deletion failed!'));
        }
    }
    
   public function deleteorder($id=0){
       
          $this->autoRender = false;
        if($id != 0){
            $this->MaterialRequestDetails->updateAll(array('status'=>0),array('mr_details_pkey'=>$id));
 
          
            echo json_encode(array('msg' => 'Material Request Details deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Material Request Details  deletion failed!'));
        }
    } 
     public function getautocompletionsmr_code(){
       $this->autoRender = false;
        if(isset($_REQUEST['mr_code']) && !empty($_REQUEST['mr_code'])){
               $searchkey = $_REQUEST['mr_code'];
            $filter_condition = 'mr_code LIKE "%' . $searchkey . '%"';
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        
         $arr_Emp = $this->MaterialRequest->query("SELECT * FROM `material_request` WHERE `status`=1 and $filter_condition");
         
         
        

        //debug($arr_Emp);
        $arr_filterresult = array();
        foreach ($arr_Emp as $val) {
//              $val['MaterialRequest']['name']=$val[0]['name'];
            $arr_filterresult[] = isset($val['material_request']) ? $val['material_request'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    
        
    }
//site name 
     public function getautocompletionssite_name(){
       $this->autoRender = false;
           if(isset($_REQUEST['site_name']) && !empty($_REQUEST['site_name'])){
               $searchkey = $_REQUEST['site_name'];
            $filter_condition = 'site_name LIKE "%' . $searchkey . '%"';
        $this->Site->useDbConfig = $this->Session->read('ds');
         $arr_site = $this->Site->query("SELECT * FROM `site` WHERE `status`=1 and $filter_condition");
        $arr_filterresult = array();
        foreach ($arr_site as $val) {
            $arr_filterresult[] = isset($val['site']) ? $val['site'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    }
   
//Gsite name 
     public function getautocompletionsgstore_code(){
       $this->autoRender = false;
           if(isset($_REQUEST['gstore_code']) && !empty($_REQUEST['gstore_code'])){
               $searchkey = $_REQUEST['gstore_code'];
            $filter_condition = 'site_name LIKE "%' . $searchkey . '%"';
        $this->Site->useDbConfig = $this->Session->read('ds');
         $arr_site = $this->Site->query("SELECT * FROM `site` WHERE `status`=1 and $filter_condition");
        $arr_filterresult = array();
        foreach ($arr_site as $val) {
            $arr_filterresult[] = isset($val['site']) ? $val['site'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    }
//site Client name 
     public function getautocompletionscustomer_name(){
       $this->autoRender = false;
           if(isset($_REQUEST['customer_name']) && !empty($_REQUEST['customer_name'])){
               $searchkey = $_REQUEST['customer_name'];
            $filter_condition = 'customer_name LIKE "%' . $searchkey . '%"';
        $this->Site->useDbConfig = $this->Session->read('ds');
         $arr_customer_name = $this->Site->query("SELECT * FROM `site` WHERE `status`=1 and $filter_condition");
        $arr_filterresult = array();
        foreach ($arr_customer_name as $val) {
            $arr_filterresult[] = isset($val['site']) ? $val['site'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    }
//site customer_po_number 
   public function getautocompletionscustomer_po_number(){
       $this->autoRender = false;
           if(isset($_REQUEST['customer_po_number']) && !empty($_REQUEST['customer_po_number'])){
               $searchkey = $_REQUEST['customer_po_number'];
            $filter_condition = 'customer_po_number LIKE "%' . $searchkey . '%"';
        $this->Site->useDbConfig = $this->Session->read('ds');
        $arr_customer_po_number = $this->Site->query("SELECT * FROM `site` WHERE `status`=1 and $filter_condition");
        $arr_filterresult = array();
        foreach ($arr_customer_po_number as $val) {
            $arr_filterresult[] = isset($val['site']) ? $val['site'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    }
//itemnname
public function getautocompletionsitem_code(){
       $this->autoRender = false;
           if(isset($_REQUEST['item_code']) && !empty($_REQUEST['item_code'])){
               $searchkey = $_REQUEST['item_code'];
            $filter_condition = 'item_desc LIKE "%' . $searchkey . '%"';
        $this->Item->useDbConfig = $this->Session->read('ds');
        $arr_item_name = $this->Item->query("SELECT * FROM `item_master` WHERE `status`=1 and $filter_condition");
        $arr_filterresult = array();
        foreach ($arr_item_name as $val) {
            $arr_filterresult[] = isset($val['item_master']) ? $val['item_master'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    }
//itemcode
public function getautocompletionsitemcode(){
       $this->autoRender = false;
           if(isset($_REQUEST['itemcode']) && !empty($_REQUEST['itemcode'])){
               $searchkey = $_REQUEST['itemcode'];
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
//report print
 public function generatereport(){
    
         $arr_request = $this->request->data;
        if ($arr_request['store_code'] == '') {
            $storecode = "";
        } else {

            $store = $arr_request['store_code'];
            $storecode = "AND `materialrequest`.`location` = '$store' ";
        }

        if ($arr_request['item_code'] == '') {
            $itemcode = "";
        } else {
            $item = $arr_request['item_code'];
            $itemcode = "AND mrdetails.item_code = '$item' ";
        }
        
        
        $originalDate = isset($arr_request['fromdate']) ? $arr_request['fromdate'] : '';
        $fromdate = date("Y-m-d", strtotime($originalDate));
        $arr_request['fromdate'] = $fromdate;
        
        $originalDate1 = isset($arr_request['todate']) ? $arr_request['todate'] : '';
        if ($originalDate1 !== '') {
            echo 'hi';
            $todate = date("Y-m-d", strtotime($originalDate1));
            
        } else {
            $todate =date("2222-12-31", strtotime($originalDate1));
        }
        $arr_request['todate'] = $todate;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.* "
                . "from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                . "where materialrequest.status= '1' AND materialrequest.mr_date BETWEEN '$fromdate' AND '$todate' "
                . "$storecode $itemcode ");

             debug($arr_material_master);
             
        $this->set('arr_material_master', $arr_material_master);
       //debug($arr_material_master);
        
    }
//storeprint
private function generateitem($itemcode = ''){
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $arr_reportfields = array();
        //$itemcode=$itemcode;
        $arr_materialmaster = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.* "
                            . "from material_request as materialrequest "
                            . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                            ."left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                            . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                            . "where materialrequest.status= '1' and mrdetails.item_code='$itemcode' ");
 return $arr_materialmaster;
   }
  //itemprint
private function generatestorecode($sitename = ''){
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $arr_reportfields = array();
        $location=$sitename;
        $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.* "
                            . "from material_request as materialrequest "
                            . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                            ."left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                            . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                            . "where materialrequest.status= '1' and materialrequest.location='$location' ");
                $this->set('arr_material_master', $arr_material_master);
//        $out = array();
//        foreach ($arr_material_master as $key => $materialmaster) {
//            $out['mr_code'] = isset($materialmaster['materialrequest']['mr_code']) ? $materialmaster['materialrequest']['mr_code'] : '';
//            $out['mr_date']=isset($materialmaster['materialrequest']['mr_date']) ? $materialmaster['materialrequest']['mr_date'] : '' ;
//            $out['customer_name']=isset($materialmaster['materialrequest']['customer_name']) ? $materialmaster['materialrequest']['customer_name'] : '' ;
//            $out['location']=isset($materialmaster['materialrequest']['location']) ? $materialmaster['materialrequest']['location'] : '' ;
//            $out['store_code']=isset($materialmaster['storemaster']['store_code']) ? $materialmaster['storemaster']['store_code'] : '' ;
//            $out['item_code']=isset($materialmaster['itemmaster']['item_desc']) ? $materialmaster['itemmaster']['item_desc'] : '' ;
//            $out['required_qty']=isset($materialmaster['mrdetails']['required_qty']) ? $materialmaster['mrdetails']['required_qty'] : '' ;
//            $out['package']=isset($materialmaster['mrdetails']['package']) ? $materialmaster['mrdetails']['package'] : '' ;
//           $resp_att["rows"][$key] = $out;
//        }
//        return $resp_att;
                return $arr_material_master;
   }
  
    
    
//item name
    public function getautocompletionsitem_desc() {
        $this->autoRender = false;
        if (isset($_REQUEST['item_desc']) && !empty($_REQUEST['item_desc'])) {
            $searchkey = $_REQUEST['item_desc'];
            $filter_condition = 'item_desc LIKE "%' . $searchkey . '%"';
            $this->Item->useDbConfig = $this->Session->read('ds');
            $arr_item = $this->Item->query("SELECT * FROM `item_master` WHERE `status`=1 and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_item as $val) {
                $arr_filterresult[] = isset($val['item_master']) ? $val['item_master'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }

}


