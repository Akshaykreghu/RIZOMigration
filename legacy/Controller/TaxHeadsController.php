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
ini_set("display_errors", 0);

App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class TaxHeadsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'TaxHeads';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('TaxType', 'TaxHead', 'TaxHeadDetail');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index() {

        $this->layout = null;
//		$this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
//		$this->SalaryHeads->useDbConfig = $this->Session->read('ds');
//		
//		$salaryHeadsDB = $this->SalaryHeads->find("all",array("conditions"=>array("status"=>1)));
//		$salaryHeads = array();
//		foreach ($salaryHeadsDB as $key => $value) {
//			
//			$salaryHeads[$value['SalaryHeads']['head_pkey']]['title'] = $value['SalaryHeads']['head_desc'];
//		/*
//			$salaryHeadItemsDB = $this->SalaryHeadItems->find("all",array("condition"=>array("status"=>1,"head_fkey"=>$value['SalaryHeads']['head_pkey'])));
//					$salaryHeadItems = array();
//					foreach ($salaryHeadItemsDB as $shikey => $shivalue) {
//					$salaryHeadItems[$shivalue['SalaryHeadItems']['salary_head_item_pkey']] = $shivalue['SalaryHeadItems'];
//					}
//		
//					$salaryHeads[$value['SalaryHeads']['head_pkey']]['items'] =  $salaryHeadItems;*/
//		
//		}
////debug(array_keys($salaryHeads));
//		$salaryHeadItemsDB = $this->SalaryHeadItems->find("all",array("conditions"=>array("status"=>1,"head_fkey"=>array_keys($salaryHeads))));
//	foreach ($salaryHeadItemsDB as $shikey => $shivalue) {
//		if(in_array($shivalue['SalaryHeadItems']['head_fkey'], array_keys($salaryHeads)))
//		{
//			$salaryHeads[$shivalue['SalaryHeadItems']['head_fkey']]['items'][] = $shivalue['SalaryHeadItems'];
//		}
//			//		$salaryHeadItems[$shivalue['SalaryHeadItems']['salary_head_item_pkey']] = $shivalue['SalaryHeadItems'];
//					}
//		
//	//debug($salaryHeads);		
//		$this->set("salaryHeads",$salaryHeads);
        //debug($salaryHeads);
    }

    function getTaxType() {


        $this->layout = null;
        $this->autoRender = false;
        $this->TaxType->useDbConfig = $this->Session->read('ds');
        $taxTypeDB = $this->TaxType->find("all", array("conditions" => array("tax_status" => 1)));

        foreach ($taxTypeDB as $key => $value) {

            $taxType[$key]['text'] = $value['TaxType']['tax_type'];
            $taxType[$key]['key'] = $value['TaxType']['tax_type_pkey'];
        }
        echo(json_encode($taxType));
    }

    function items() {
        $this->layout = null;

        //debug($_POST);
    }

    function getTaxHead() {
        //echo "hlo";die();

        $this->autoRender = false;
$this->TaxHead->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            $taxHeadDB = $this->TaxHead->find("all", array("conditions" => array("tax_active" => 'Y', "tax_type_fkey" => $id)));
            //debug($taxHeadDB);die();
            $taxHeads = array();
            $key = 0;

            foreach ($taxHeadDB as $shikey => $shivalue) {
                if ($shivalue['TaxHead']['tax_type_fkey'] == $id) {
                    $taxHeads[$key]['text'] = $shivalue['TaxHead']['tax_name']. ' Limit:'.$shivalue['TaxHead']['attr1'];

                    $taxHeads[$key]['key'] = $shivalue['TaxHead']['tax_heads_pkey'];

                    $key++;
                }
                //print_r($shivalue);
            }
            //echo $taxHeads;die();
            echo json_encode($taxHeads);
        }
    }

    function getTaxHeaddetails() {  //echo "hlo";die();
        $this->autoRender = false;
$this->TaxHeadDetail->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            // echo $id;die();
            $taxHeadDetailsDB = $this->TaxHeadDetail->find("all", array("conditions" => array("status" => 1, "tax_heads_fkey" => $id)));
            //debug($taxHeadDetailsDB);die();
            $taxHeadsdetails = array();
            $key = 0;

            foreach ($taxHeadDetailsDB as $shikey => $shivalue) {
                if ($shivalue['TaxHeadDetail']['tax_heads_fkey'] == $id) {
                    $taxHeadsdetails[$key]['text'] = $shivalue['TaxHeadDetail']['tax_heads_details'];
                    $taxHeadsdetails[$key]['text1'] = $shivalue['TaxHeadDetail']['tax_heads_details1'];
                    $taxHeadsdetails[$key]['text2'] = $shivalue['TaxHeadDetail']['tax_heads_details2'];
                    $taxHeadsdetails[$key]['key'] = $shivalue['TaxHeadDetail']['tax_heads_details_pkey'];
                    if ($shivalue['TaxHeadDetail']['active'] == 1) {
                        $taxHeadsdetails[$key]['checked'] = true;
                        $taxHeadsdetails[$key]['selected'] = true;
                    }
                    $key++;
                }
                //print_r($shivalue);
            }
            //echo $taxHeadsdetails;die();
            echo json_encode($taxHeadsdetails);
        }
    }

    function removeTaxHead() {
        $this->TaxHeadDetail->useDbConfig = $this->Session->read('ds');
        //$this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array('success' => false, "msg" => "Error while saving");

        //$salaryHeadItem = $_REQUEST['salary_head_item'];
        //	debug($salaryHeadItem);
        if (isset($_REQUEST['tax_head_item'])) {

            $taxHeadItem = explode(',', $_REQUEST['tax_head_item']);
            //debug($taxHeadItem);die();
            $head_fkey = $_REQUEST['tax_head'];
            if (is_array($taxHeadItem)) {
                //debug($salaryHeadItem);
                //$this->SalaryHeadItems->updateAll(array('value' => "'N'"),array("head_fkey"=>$head_fkey));

                foreach ($taxHeadItem as $k => $v) {
                    $data["active"] = 0;
                    $data["tax_heads_details_pkey"] = $v;
                    $this->TaxHeadDetail->save($data);
                }

                // $this->SalaryHeadItems->updateAll( array('value' => "'N'"),array("status"=>1));
                $resp["success"] = true;
                $resp["msg"] = "Tax Heads Removed Successfully";
            }
        }

        echo json_encode($resp);
        ///$this->redirect("index");
    }

    function saveTaxHead() {

        // echo $_REQUEST['tax_head_item'];die();
        $this->TaxHeadDetail->useDbConfig = $this->Session->read('ds');
        $this->TaxHeads->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array('success' => false, "msg" => "Error while saving");

        //$salaryHeadItem = $_REQUEST['salary_head_item'];
        //	debug($salaryHeadItem);
        if (isset($_REQUEST['tax_head_item'])) {

            $taxHeaddetails = explode(',', $_REQUEST['tax_head_item']);
            //debug($salaryHeadItem);
            $head_fkey = $_REQUEST['tax_head'];
            if (is_array($taxHeaddetails)) {
                //debug($taxHeaddetails);die();
                //$this->SalaryHeadItems->updateAll(array('value' => "'N'"),array("head_fkey"=>$head_fkey));

                foreach ($taxHeaddetails as $k => $v) {
                    $data["active"] = 1;
                    $data["tax_heads_details_pkey"] = $v;
                    $this->TaxHeadDetail->save($data);
                }

                // $this->SalaryHeadItems->updateAll( array('value' => "'N'"),array("status"=>1));

                $resp["success"] = true;
                $resp["msg"] = "Tax Heads Saved Successfully";
            }
        }

        echo json_encode($resp);
        ///$this->redirect("index");
    }

    public function form() { //echo "hlo";die();
        $this->layout = null;
  $this->TaxType->useDbConfig = $this->Session->read('ds');
        // $id = $this->params['url']['id']; 
        //  echo $id;die();
        if (isset($this->params['url']['id'])) {
            $id = $this->params['url']['id'];

            $this->set('id', $id);
            $salaryDB = $this->TaxType->find("all", array("conditions" => array("tax_status" => 1, "tax_type_pkey" => $id)));
            //print_r($salaryDB);die();
            $desc['name']= $salaryDB[0]['TaxType']['tax_type'];
            $desc['desc_name'] = $salaryDB[0]['TaxType']['tax_desc'];
            $desc['occurance']= $salaryDB[0]['TaxType']['tax_occurance'];
            $desc['operator'] = $salaryDB[0]['TaxType']['tax_operator'];
          //  $this->set('name', $name);
            $this->set('desc', $desc);
        }
    }

    public function addtaxtypes() { //echo "hlo";die();
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->TaxType->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        if (isset($_REQUEST['TAXHEAD_ID'])) {
            $arr_form_data['tax_type_pkey'] = $arr_form_data['TAXHEAD_ID'];
            $arr_form_data['tax_type'] = $arr_form_data['TAX_NAME'];
            $arr_form_data['tax_desc'] = $arr_form_data['TAX_DESC'];
            $arr_form_data['tax_occurance'] = $arr_form_data['ITEM_OCCURANCE'];
            $arr_form_data['tax_operator'] = $arr_form_data['ITEM_OPERATOR'];
            $result = $this->TaxType->save($arr_form_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Salary Head Added";
        } else {
            if (isset($_REQUEST['TAX_NAME'])) {
                $arr_form_data['tax_type_pkey'] = $arr_form_data['TAXHEAD_ID'];
                $arr_form_data['tax_type'] = $arr_form_data['TAX_NAME'];
                $arr_form_data['tax_desc'] = $arr_form_data['TAX_DESC'];
                 $arr_form_data['tax_occurance'] = $arr_form_data['ITEM_OCCURANCE'];
                $arr_form_data['tax_operator'] = $arr_form_data['ITEM_OPERATOR'];
                $arr_form_data['tax_status'] = 1;
                $result = $this->TaxType->save($arr_form_data);
                $resp = array();
                $resp["success"] = true;
                $resp["msg"] = "Tax added sucessfully";
            }
        }
    }
	 

    public function Deletetaxtype() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->TaxType->useDbConfig = $this->Session->read('ds');
        $head_fkey = $_REQUEST['tax_id'];
        //$arr_form_data = $this -> request -> data;
        //print_r($arr_form_data);die();
        $arr_form_data['tax_type_pkey'] = $head_fkey;
        $arr_form_data['tax_status'] = 0;
        $this->TaxType->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Tax type Removed";
        echo json_encode($resp);
    }

    public function form_items($id, $kid) {
        $this->set('id', $id);
        $this->TaxType->useDbConfig = $this->Session->read('ds');
        $this->TaxHead->useDbConfig = $this->Session->read('ds');
        $taxDB = array();
        $taxDB = Set::extract('/TaxType/.', $this->TaxType->find("all", array("conditions" => array("tax_status" => 1))));

        //  debug($arr_leave_type);
        if ($kid != 0) {
            $this->set('kid', $kid);
            //$this->set('id', $id);
            $TaxHeadDB = $this->TaxHead->find("all", array("conditions" => array("tax_active" => 'Y', "tax_heads_pkey" => $kid)));
            //debug($TaxHeadDB);die();
            $TaxHead['tax_name'] = $TaxHeadDB[0]['TaxHead']['tax_name'];
            $TaxHead['tax_type'] = $TaxHeadDB[0]['TaxHead']['tax_type'];
            $TaxHead['tax_details'] = $TaxHeadDB[0]['TaxHead']['tax_details'];
            $TaxHead['tax_active'] = $TaxHeadDB[0]['TaxHead']['tax_active'];
            $TaxHead['attr1'] = $TaxHeadDB[0]['TaxHead']['attr1'];
            $this->set('TaxHead', $TaxHead);
            $this->set('taxnamelist', $taxDB);
        }
    }

    public function addtaxheads() {  //echo "test";die();
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->TaxHead->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // print_r($arr_form_data);die();

        if (isset($arr_form_data['TAX_ITEM_ID'])) {
            $arr_data['tax_heads_pkey'] = $arr_form_data['TAX_ITEM_ID'];
            $arr_data['tax_name'] = $arr_form_data['TAX_NAME'];
            $arr_data['tax_type_fkey'] = $arr_form_data['TAX_TYPE'];
            $arr_data['tax_type'] = $this->gettaxtypename($arr_form_data['TAX_TYPE']);
            $arr_data['tax_details'] = $arr_form_data['TAX_DESC'];
            $arr_data['tax_active'] = $arr_form_data['TAX_ACTIVE'];
            $arr_data['attr1'] = $arr_form_data['limit'];
            //debug($arr_data);die();
            $result = $this->TaxHead->save($arr_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Data added sucessfully";
            echo json_encode($resp);
        } else if (isset($arr_form_data)) {
            $arr_data['tax_type_fkey'] = $arr_form_data['ITEM_ID'];
            $arr_data['tax_name'] = $arr_form_data['TAX_NAME'];
            $arr_data['tax_type'] = $this->gettaxtypename($arr_data['tax_type_fkey']);
            $arr_data['tax_details'] = $arr_form_data['TAX_DESC'];
            $arr_data['tax_active'] = $arr_form_data['TAX_ACTIVE'];
            $arr_data['attr1'] = $arr_form_data['limit'];
            //debug($arr_data);die();
            $result = $this->TaxHead->save($arr_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Data added sucessfully";
            echo json_encode($resp);
            //$this -> render('index');
        }
    }

    public function Deletetaxhead() {

        $this->autoRender = FALSE;
        $this->layout = null;
        $this->TaxHead->useDbConfig = $this->Session->read('ds');
        $head_fkey = $_REQUEST['tax_id'];
        //$arr_form_data = $this -> request -> data;
        //print_r($arr_form_data);die();
        $arr_form_data['tax_heads_pkey'] = $head_fkey;
        $arr_form_data['tax_active'] = "N";
        $this->TaxHead->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Tax head Removed";
        echo json_encode($resp);
    }

    public function gettaxtypename($id) {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->TaxType->useDbConfig = $this->Session->read('ds');
        $taxDB = $this->TaxType->find("all", array("conditions" => array("tax_status" => 1, "tax_type_pkey" => $id)));
        //print_r($salaryDB);die();
        $name = $taxDB[0]['TaxType']['tax_type'];
        return $name; //die();
        // $desc=$salaryDB[0]['TaxType']['tax_desc'];
        //$this->set('name', $name);
        //$this->set('desc', $desc);
        //return $name;
    }

    public function Gettaxnamelist() {

        $this->autoRender = FALSE;
        $this->layout = null;
        $this->TaxType->useDbConfig = $this->Session->read('ds');
        $taxDB = array();
        $taxDB = Set::extract('/TaxType/.', $this->TaxType->find("all", array("conditions" => array("tax_status" => 1))));
        return $taxDB;
    }

    public function add_details($id, $kid) {
          $this->TaxHeadDetail->useDbConfig = $this->Session->read('ds');
        $this->set('id', $id);
        if (isset($kid) && $kid != 0) {
            $this->set('kid', $kid);
            $TaxHeadDB = $this->TaxHeadDetail->find("all", array("conditions" => array("status" => 1, "tax_heads_details_pkey" => $kid)));
            // debug($TaxHeadDB);die();

            $TaxHeadDetail['detail'] = $TaxHeadDB[0]['TaxHeadDetail']['tax_heads_details'];
            $TaxHeadDetail['detail1'] = $TaxHeadDB[0]['TaxHeadDetail']['tax_heads_details1'];
            $TaxHeadDetail['detail2'] = $TaxHeadDB[0]['TaxHeadDetail']['tax_heads_details2'];
            $TaxHeadDetail['active'] = $TaxHeadDB[0]['TaxHeadDetail']['active'];
            $this->set('TaxHeadDetail', $TaxHeadDetail);
            //$this->set('taxnamelist',$taxDB);
        }
    }

    Public function addtaxheaddetails() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->TaxHeadDetail->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        //print_r($arr_form_data);die();

        if (isset($arr_form_data['TAX_ITEM_ID'])) {
            $arr_data['tax_heads_details_pkey'] = $arr_form_data['TAX_ITEM_ID'];
            $arr_data['tax_heads_details'] = $arr_form_data['TAX_DESC'];
            $arr_data['tax_heads_details1'] = $arr_form_data['TAX_DESC1'];
            $arr_data['tax_heads_details2'] = $arr_form_data['limit'];
            $arr_data['active'] = $arr_form_data['TAX_ACTIVE'];
            //debug($arr_data);die();
            $result = $this->TaxHeadDetail->save($arr_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Data added sucessfully";
            echo json_encode($resp);
        } else if (isset($arr_form_data)) {
            $arr_data['tax_heads_fkey'] = $arr_form_data['ITEM_HID'];
            $arr_data['tax_heads_details'] = $arr_form_data['TAX_DESC'];
            $arr_data['tax_heads_details1'] = $arr_form_data['TAX_DESC1'];
            $arr_data['tax_heads_details2'] = $arr_form_data['limit'];
            $arr_data['active'] = $arr_form_data['TAX_ACTIVE'];
            $arr_data['status'] = 1;
            //debug($arr_data);die();
            $result = $this->TaxHeadDetail->save($arr_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Data added sucessfully";
            echo json_encode($resp);
        }
    }

}
