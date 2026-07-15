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
class MaterialController extends AppController {

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
    public $uses = array('Item','QuantityDetails','AdditionalDetails','ItemDetails','WarrantyDetails','MaterialController');
public function index()
            {

            }
   
//popup for save and update
public function form($acct_payable_pkey=0) 
        {
         
           // debug($acct_payable_pkey);
            $arr_request_data = $this->request->data;
            //debug($arr_request_data);
            $this->Item->useDbConfig = $this->Session->read('ds');
            $this->ItemDetails->useDbConfig = $this->Session->read('ds');
            $this->QuantityDetails->useDbConfig = $this->Session->read('ds');
            $this->WarrantyDetails->useDbConfig = $this->Session->read('ds');
            $this->AdditionalDetails->useDbConfig = $this->Session->read('ds');

        // $this->set("arr_employees",$arr_employees = $this->Item->find("all",array('conditions'=>array('status'=>1))));
                        $arr_att = $this->Item->query("SELECT itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.* from item_master as itemmaster "
                            . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                            . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                            . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                            . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                            . "where itemmaster.item_master_pkey = '$acct_payable_pkey' ");
    //    debug($arr_att);die();
        
                 
                    $this->layout = null;
                    $this->set("arr_att",$arr_att);
        }


//data grid list            
        public function itemlist()
            {
          
                     $this->autoRender = FALSE;              
                     $arr_request_data = $this->request->data;
                     $this->Item->useDbConfig = $this->Session->read('ds');
                     $this->ItemDetails->useDbConfig = $this->Session->read('ds');
                     $this->QuantityDetails->useDbConfig = $this->Session->read('ds');
                     $this->WarrantyDetails->useDbConfig = $this->Session->read('ds');
                     $this->AdditionalDetails->useDbConfig = $this->Session->read('ds');
                     
                    $limit = $_REQUEST['rows'];
                    $page = $_REQUEST['page'];
                    $ofst = ($page - 1) * $limit;      
                    $this->datatable["conditions"] = array('status' => 1);
                    $resp_att = array();
                    $resp_att["rows"] = array();
                    $count = $this->Item->find("count",array("conditions" => array('status' => 1)));
                    
                    $arr_att = $this->Item->query("SELECT itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.* from item_master as itemmaster "
                            . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                            . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                            . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                            . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                            . "where itemmaster.status = '1' ");
                   // debug($arr_att);
                    $out = array();       
                        foreach ($arr_att as $key => $value) {
                            $out['item_master_pkey'] = isset($value['itemmaster']['item_master_pkey']) ? $value['itemmaster']['item_master_pkey'] : '';
                            $out['item_code'] = isset($value['itemmaster']['item_code']) ? $value['itemmaster']['item_code'] : '';
                            $out['item_category'] = isset($value['itemmaster']['item_category']) ? $value['itemmaster']['item_category'] : '';
                            $out['item_specification'] = isset($value['itemmaster']['item_specification']) ? $value['itemmaster']['item_specification'] : '';
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
                        $this->autoRender = FALSE;
                $this->layout = null;
                $result = array('success' => 0);
                $arr_form_data = $this->request->data;
                debug($arr_form_data);
                $arr_form_data['created_by'] = $this->Session->read('user_id');
                //modifiey by            
                if ($arr_form_data['item_master_pkey'] == 'item_master_pkey') {
                    $arr_form_data['modified_by'] = $this->Session->read('user_id');
                }
                $this->Item->save($arr_form_data);
                //last  insert id
                if ($arr_form_data['item_master_pkey'] == '') {
                    $arr_form_data['item_master_fkey'] = $this->Item->getInsertID();
                }
        //debug($arr_form_data);
                $this->ItemDetails->save($arr_form_data);
                $this->QuantityDetails->save($arr_form_data);
                $this->WarrantyDetails->save($arr_form_data);
                $this->AdditionalDetails->save($arr_form_data);
                $this->redirect(array('controller' => 'Item', 'action' => 'index'));
            }

}
