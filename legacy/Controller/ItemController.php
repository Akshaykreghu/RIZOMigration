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
class ItemController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout="default";
    public $name ='Item';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('Item' ,'ItemPricing','QuantityDetails','ItemAdditionalDetails', 'AdditionalDetails','ItemDetails','WarrantyDetails','CategoryMaster','ItemSpecification','PackageMaster','UoMaster');
public function index()
            {

            }
   

            
    public function itemfilter(){
                
        $this->autoRender = false;
        $this->Item->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and item_desc like '%$q%'";
        } else {
            $q_condition = "";
        }
        $arr_site = $this->Item->query("select * from item_master where status= '1' $q_condition ");
        $array = array();
        $items[] = array("id" => "0", "text" => "ALL");
        foreach ($arr_site as $key => $value) {
            $items[] = array(
                'id' => $value['item_master']['item_master_pkey'],
                'text' => $value['item_master']['item_desc'] 
            );
        }
        $array['items'] = $items;
//        debug($site);
        echo json_encode($array);
            }
            //popup for save and update
     public function form($acct_payable_pkey=0) 
        {
         
           // debug($acct_payable_pkey);
            $arr_request_data = $this->request->data;
            //debug($arr_request_data);
            $this->Item->useDbConfig = $this->Session->read('ds');
            $this->ItemDetails->useDbConfig = $this->Session->read('ds');
            $this->PackageMaster->useDbConfig = $this->Session->read('ds');
            $this->ItemSpecification->useDbConfig = $this->Session->read('ds');
            $this->UoMaster->useDbConfig = $this->Session->read('ds');
			$this->CategoryMaster->useDbConfig = $this->Session->read('ds');
//drop down         
            $this->set("all_category",$all_category = $this->CategoryMaster->find("all", array("conditions" => array('status' => 1))));
            $this->set("all_specification",$all_specification = $this->ItemSpecification->find("all", array("conditions" => array('status' => 1))));
            $this->set("all_package",$all_package=$this->PackageMaster->find("all",array("conditioin"=>array('status'=>1))));
            $this->set("all_measurement",$all_measurement=$this->UoMaster->find("all",array("conditioin"=>array('status'=>1))));
            
            //debug(all_specification);
            //debug($all_category);
            //die();
        // $this->set("arr_employees",$arr_employees = $this->Item->find("all",array('conditions'=>array('status'=>1))));
            
                        $arr_att = $this->Item->query("SELECT itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*"
                            . " from item_master as itemmaster "
                            . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                            . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                            . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                            . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                                
                                
                            . "where itemmaster.item_master_pkey = '$acct_payable_pkey' ");
                        
    //   debug($arr_att);die();
        
                 
                    $this->layout = null;
                    $this->set("arr_att",$arr_att);
        }
//edited by sreekanth on 22_5_19 check item name duplication
  public function chkcategory()
    {
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE;
        $this->layout = null;    
        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
           //added by megha item code duplication avoided with status
        $item_pkey = isset($arr_form_data['item_master_pkey'])?$arr_form_data['item_master_pkey']:'';
        $item_code = isset($arr_form_data['item_code'])?$arr_form_data['item_code'] :'';
        if($item_pkey){
         $catgory_check = $this->Item->query("SELECT * FROM `item_master` WHERE item_code = '$item_code'  and status ='1' and item_master_pkey != $item_pkey;");   
         $msg = "1";
        }else{
            $catgory_check = $this->Item->query("SELECT * FROM `item_master` WHERE item_code = '$item_code'  and status ='1'");   
         $msg = "1";
        }
        $item_desc = isset($arr_form_data['item_desc'])?$arr_form_data['item_desc'] :'';
        if($item_pkey){
        $catgory_check = $this->Item->query("SELECT * FROM `item_master` WHERE item_desc = '$item_desc'  and status ='1' and item_master_pkey != $item_pkey;"); 
         $msg = "2";
        }else{
         $catgory_check = $this->Item->query("SELECT * FROM `item_master` WHERE item_desc = '$item_desc'  and status ='1' "); 
         $msg = "2";
        }
        $resp = array();       
        if(!empty($catgory_check)){
          $resp["msg"] = $msg;          
        }     
        echo json_encode($resp);                
    }

//data grid list            
        public function itemlist($param = "")
            {
          
                     $this->autoRender = FALSE;              
                     $arr_request_data = $this->request->data;
                     $this->Item->useDbConfig = $this->Session->read('ds');
                     $this->ItemDetails->useDbConfig = $this->Session->read('ds');
                     $this->QuantityDetails->useDbConfig = $this->Session->read('ds');
                     $this->WarrantyDetails->useDbConfig = $this->Session->read('ds');
                     $this->ItemAdditionalDetails->useDbConfig = $this->Session->read('ds');
                     $this->CategoryMaster->useDbConfig = $this->Session->read('ds');
                     $this->ItemSpecification->useDbConfig = $this->Session->read('ds');
                     $this->PackageMaster->useDbConfig = $this->Session->read('ds');
                     $this->UoMaster->useDbConfig = $this->Session->read('ds');
                     
                     $itemkey = isset($arr_request_data['item']) ? $arr_request_data['item'] : '0';
                     if($itemkey == 0 ){
                             $cond = "";
                     }
                     else {
                          $cond = " and item_master_pkey = $itemkey ";
                     }
//                     debug($itemkey);
//                    if(isset($arr_request_data['site'])){
//                   
//                    $cond = " and item_master_pkey = $site_pke ";
//                    }else{
//                    $cond = "";
//                    }
                     
                    $limit = $_REQUEST['rows'];
                    $page = $_REQUEST['page'];
                    $ofst = ($page - 1) * $limit;      
                    $this->datatable["conditions"] = array('status' => 1);
                    $resp_att = array();
                    $resp_att["rows"] = array();
                    $count = $this->Item->find("count",array("conditions" => array('status' => 1)));
                    
                    
                    
                    $arr_att = $this->Item->query("SELECT itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*,categorymaster.*,itemspecification.* "
                            . "from item_master as itemmaster "
                            . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                            . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                            . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                            . "left join category_master as categorymaster on (itemmaster.item_category = categorymaster.category_pkey)"
                            . "left join item_specification as itemspecification on (itemmaster.item_specification = itemspecification.specification_pkey)"
                           
                            
                            . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                            . "where itemmaster.status = '1' $cond  order by itemmaster.item_master_pkey desc LIMIT $limit  OFFSET $ofst");
                     //debug($arr_att);
                    $out = array();       
                        foreach ($arr_att as $key => $value) {
                            $out['item_master_pkey'] = isset($value['itemmaster']['item_master_pkey']) ? $value['itemmaster']['item_master_pkey'] : '';
                             //added by megha on 24_05_19 item code
                            $out['item_code'] = isset($value['itemmaster']['item_code']) ? $value['itemmaster']['item_code'] : '';
                            $out['item_desc'] = isset($value['itemmaster']['item_desc']) ? $value['itemmaster']['item_desc'] : '';
                            $out['description'] = isset($value['categorymaster']['description']) ? $value['categorymaster']['description'] : '';
                            $out['item_specification'] = isset($value['itemspecification']['item_specification']) ? $value['itemspecification']['item_specification'] : '';
                            $out['created_by'] = isset($value['itemmaster']['created_by']) ? $value['itemmaster']['created_by'] : '';
                            
                            
                            $resp_att["rows"][$key] = $out;
                        }       
                    $resp_att["total"] = $count;
                    echo json_encode($resp_att);
            }
 
//delete
        public function itemdelete()
            {
                $this->autoRender=FALSE;
                
                $this->Item->useDbConfig = $this->Session->read('ds');
                $result =   array('success' => 0 );
                if(isset($_REQUEST["item_master_pkey"]))
                {   
                    $ar_ids = explode(",", $_REQUEST["item_master_pkey"]);
                    //debug($ar_ids);
                    $this->Item->updateAll(
                        array('Item.status' => 0),
                            array('Item.item_master_pkey' => $ar_ids));
                    $result['success'] = 1;
                    $result['msg'] = "Record(s)  deleted successfully.";
                }
                echo json_encode($result);
             }
             
    public function save()
            {
		$this->Item->useDbConfig = $this->Session->read('ds');
		$this->QuantityDetails->useDbConfig = $this->Session->read('ds');
		$this->ItemDetails->useDbConfig = $this->Session->read('ds');
		$this->WarrantyDetails->useDbConfig = $this->Session->read('ds');
		$this->ItemAdditionalDetails->useDbConfig = $this->Session->read('ds');
                $this->autoRender = FALSE;
                $this->layout = null;
                $result = array('success' => 0);
                $arr_form_data = $this->request->data;
                $arr_form_data['created_by'] = $this->Session->read('login_user_id');
                
                //modifiey by            
                if ($arr_form_data['item_master_fkey'] > 0) {
                    $arr_form_data['modified_by'] = $this->Session->read('login_user_id');
                    date_default_timezone_set('Asia/Kolkata');
                    // showing current date and time of INDIA;
                    $date = date( 'Y-m-d h:i:sa');
                    $arr_form_data['modified_date'] = $date;
                }
                $this->Item->save($arr_form_data);
                //last  insert id
                if ($arr_form_data['item_master_pkey'] == '') {
                    $arr_form_data['item_master_fkey'] = $this->Item->getInsertID();
                }
               // $arr_form_data['purchase_rate'] = $arr_form_data['item_master_pkey'];
                $this->ItemDetails->save($arr_form_data);
                if($arr_form_data['quantity_details_pkey']){
                    
                }else{
                    //$this->QuantityDetails->save($arr_form_data);
                }
//                $this->WarrantyDetails->save($arr_form_data);
                $this->ItemAdditionalDetails->save($arr_form_data);
               // $this->ItemPricing->save($arr_form_data);
                
                          }

}
