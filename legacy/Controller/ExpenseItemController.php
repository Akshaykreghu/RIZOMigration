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
class ExpenseItemController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout="default";
    public $name ='ExpenseItem';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('Item' ,'ItemPricing','QuantityDetails','ItemAdditionalDetails', 'AdditionalDetails','ItemDetails','WarrantyDetails','CategoryMaster','ItemSpecification','PackageMaster','UoMaster','ExpenseItem','ExpenseType');
public function index()
            {

            }
   

            
    public function itemfilter(){
                
        $this->autoRender = false;
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->ExpenseItem->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and expense_item_name like '%$q%'";
        } else {
            $q_condition = "";
        }
        $arr_site = $this->Item->query("select * from expense_item where status= '1' $q_condition ");
        // debug($arr_site);
        $array = array();
        $items[] = array("id" => "0", "text" => "ALL");
        foreach ($arr_site as $key => $value) {
            // debug($value);
            $items[] = array(
                'id' => $value['expense_item']['expense_item_pkey'],
                'text' => $value['expense_item']['category'] 
            );
        }
        $array['items'] = $items;
//        debug($site);
        echo json_encode($array);
            }

//              public function expensefilter(){
                
//         $this->autoRender = false;
//         $this->Item->useDbConfig = $this->Session->read('ds');
//         $this->ExpenseItem->useDbConfig = $this->Session->read('ds');
//         $this->ExpenseType->useDbConfig = $this->Session->read('ds');
//         $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
//         if ($q != null) {
//             $q_condition = "and expense_type_name like '%$q%'";
//         } else {
//             $q_condition = "";
//         }
//         $arr_type= $this->Item->query("select * from expense_type where status= '1' $q_condition ");
//         // debug($arr_type);
//         $array = array();
//         $items[] = array("id" => "0", "text" => "ALL");
//         foreach ($arr_type as $key => $value) {
//             // debug($value);
//             $items[] = array(
//                 'id' => $value['expense_type']['expense_type_pkey'],
//                 'text' => $value['expense_type']['expense_type_name'] 
//             );
//         }
//         $array['items'] = $items;
// //        debug($site);
//         echo json_encode($array);
//             }

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
            $this->ExpenseItem->useDbConfig = $this->Session->read('ds');
//drop down         
            $this->set("all_category",$all_category = $this->CategoryMaster->find("all", array("conditions" => array('status' => 1))));
            $this->set("all_specification",$all_specification = $this->ItemSpecification->find("all", array("conditions" => array('status' => 1))));
            $this->set("all_package",$all_package=$this->PackageMaster->find("all",array("conditioin"=>array('status'=>1))));
            $this->set("all_measurement",$all_measurement=$this->UoMaster->find("all",array("conditioin"=>array('status'=>1))));
            
            //debug(all_specification);
            //debug($all_category);
            //die();
        // $this->set("arr_employees",$arr_employees = $this->Item->find("all",array('conditions'=>array('status'=>1))));
            
                        $arr_att = $this->Item->query("select expense_item.expense_item_name,expense_item.category,expense_item.description,expense_item.expense_item_pkey,expense_type.expense_type_name from expense_item left join expense_type on (expense_item.expense_item_name = expense_type.expense_type_pkey) where expense_item_pkey = '$acct_payable_pkey' ");

      // debug($arr_att);
                            $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and expense_type_name like '%$q%'";
        } else {
            $q_condition = "";
        }
         $arr_type= $this->Item->query("select * from expense_type where status= '1' $q_condition ");
                 
                    $this->layout = null;
                    $this->set("arr_att",$arr_att);
                    $this->set("arr_type",$arr_type);
        }
//edited by sreekanth on 22_5_19 check item name duplication
   public function chkcategory()
    {
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE;
        $this->layout = null;    
        $arr_form_data = $this->request->data;
        $item_code = isset($arr_form_data['item_code'])?$arr_form_data['item_code'] :'';
        if($item_code){
         $catgory_check = $this->Item->query("SELECT * FROM `item_master` WHERE item_code = '$item_code'");   
         $msg = "1";
        }
        $item_desc = isset($arr_form_data['item_desc'])?$arr_form_data['item_desc'] :'';
        if($item_desc){
        $catgory_check = $this->Item->query("SELECT * FROM `item_master` WHERE item_desc = '$item_desc'"); 
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
                     $this->ExpenseItem->useDbConfig = $this->Session->read('ds');
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
                     // debug($itemkey);
                     if($itemkey == 0 ){
                             $cond = "";
                     }
                     else {
                          $cond = " and expense_item.expense_item_pkey = $itemkey ";
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
                    
                    $counts = $this->ExpenseItem->query("select COUNT(*)from expense_item left join expense_type on (expense_item.expense_item_name = expense_type.expense_type_pkey) where expense_item.status = 1 $cond ");
                    // debug($count);
                    $count  = $counts['0']['0']['COUNT(*)'];
                    
                    
                    $arr_att = $this->Item->query("select expense_item.expense_item_name,expense_item.category,expense_item.description,expense_item.expense_item_pkey,expense_type.expense_type_name from expense_item left join expense_type on (expense_item.expense_item_name = expense_type.expense_type_pkey) where expense_item.status = 1 $cond order by expense_item.expense_item_pkey desc LIMIT $limit  OFFSET $ofst");
                     // debug($arr_att);
                    $out = array();       
                        foreach ($arr_att as $key => $value) {
                            // debug($value);
                            $out['expense_item_pkey'] = isset($value['expense_item']['expense_item_pkey']) ? $value['expense_item']['expense_item_pkey'] : '';
                             //added by megha on 24_05_19 item code
                            $out['expense_item_name'] = isset($value['expense_type']['expense_type_name']) ? $value['expense_type']['expense_type_name'] : '';
                            $out['category'] = isset($value['expense_item']['category']) ? $value['expense_item']['category'] : '';
                            $out['description'] = isset($value['expense_item']['description']) ? $value['expense_item']['description'] : '';
                            
                            
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
                $this->ExpenseItem->useDbConfig = $this->Session->read('ds');
                $data = $_REQUEST;
                // debug($data);
                $result =   array('success' => 0 );
                if(isset($_REQUEST["item_master_pkey"]))
                {   
                    $ar_ids = explode(",", $_REQUEST["item_master_pkey"]);
                    //debug($ar_ids);
                    $this->ExpenseItem->updateAll(
                        array('ExpenseItem.status' => 0),
                            array('ExpenseItem.expense_item_pkey' => $ar_ids));
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
                $this->ItemPricing->useDbConfig = $this->Session->read('ds');
                $this->ExpenseItem->useDbConfig = $this->Session->read('ds');
                        $this->autoRender = FALSE;
                $this->layout = null;
                
                $result = array('success' => 0);
                $arr_form_data = $this->request->data;
                debug($arr_form_data);
                $arr_form_data['created_by'] = "Admin";
                $time = $this->ExpenseItem->query("select now() as time");
                $this->set('time', $time);
                //modifiey by            
                if ($arr_form_data['expense_item_pkey'] != '') {
                    $arr_form_data['modified_by'] = $this->Session->read('user_name');
                    $arr_form_data['modified_date'] = $time['0']['0']['time'];
                }
                
                $this->ExpenseItem->save($arr_form_data);
             
                          }

}
