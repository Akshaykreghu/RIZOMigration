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
class SalaryHeadsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'SalaryHeads';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('SalaryHeadItems', 'SalaryHeads');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index() {

        $this->layout = null;
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');

        $salaryHeadsDB = $this->SalaryHeads->find("all", array("conditions" => array("status" => 1)));
        $salaryHeads = array();
        foreach ($salaryHeadsDB as $key => $value) {

            $salaryHeads[$value['SalaryHeads']['head_pkey']]['title'] = $value['SalaryHeads']['head_desc'];
            /*
              $salaryHeadItemsDB = $this->SalaryHeadItems->find("all",array("condition"=>array("status"=>1,"head_fkey"=>$value['SalaryHeads']['head_pkey'])));
              $salaryHeadItems = array();
              foreach ($salaryHeadItemsDB as $shikey => $shivalue) {
              $salaryHeadItems[$shivalue['SalaryHeadItems']['salary_head_item_pkey']] = $shivalue['SalaryHeadItems'];
              }

              $salaryHeads[$value['SalaryHeads']['head_pkey']]['items'] =  $salaryHeadItems; */
        }
//debug(array_keys($salaryHeads));
        $salaryHeadItemsDB = $this->SalaryHeadItems->find("all", array("conditions" => array("status" => 1, "head_fkey" => array_keys($salaryHeads))));
        foreach ($salaryHeadItemsDB as $shikey => $shivalue) {
            if (in_array($shivalue['SalaryHeadItems']['head_fkey'], array_keys($salaryHeads))) {
                $salaryHeads[$shivalue['SalaryHeadItems']['head_fkey']]['items'][] = $shivalue['SalaryHeadItems'];
            }
            //		$salaryHeadItems[$shivalue['SalaryHeadItems']['salary_head_item_pkey']] = $shivalue['SalaryHeadItems'];
        }

        //debug($salaryHeads);		
        $this->set("salaryHeads", $salaryHeads);
        //debug($salaryHeads);
    }

    function getSalaryHead() {


        $this->layout = null;
        $this->autoRender = false;
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');

        $salaryHeadsDB = $this->SalaryHeads->find("all", array("conditions" => array("status" => 1)));
        $salaryHeads = array();
        foreach ($salaryHeadsDB as $key => $value) {

            $salaryHeads[$key]['text'] = $value['SalaryHeads']['head_desc'];
            $salaryHeads[$key]['key'] = $value['SalaryHeads']['head_pkey'];
        }

        //debug($salaryHeads);
        echo(json_encode($salaryHeads));
    }

    function items() {
        $this->layout = null;

        //debug($_POST);
    }

    function getSalaryHeadItems() {
        $this->autoRender = false;
 $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            $salarycomponents = $this->SalaryHeadItems->query("select salary_head_item_Fkey from tax_salary_components where status = 1 ");
           $salaryHeadItemsDB = $this->SalaryHeadItems->find("all",array("conditions" => array("SalaryHeadItems.status" => 1, "SalaryHeadItems.head_fkey" => $id)));
            $salaryHeads = array();
            $salarytsc = array();
            foreach($salarycomponents as $val)
            {
                $salarytsc[] = $val['tax_salary_components']['salary_head_item_Fkey'];
            }
            $key = 0;
            $tsc = array();
            
            //debug($salarytsc);
            
            foreach ($salaryHeadItemsDB as $shikey => $shivalue) {
                
                if ($shivalue['SalaryHeadItems']['head_fkey'] == $id) {
                    $salaryHeads[$key]['text'] = $shivalue['SalaryHeadItems']['item'];
                    $salaryHeads[$key]['type'] = $shivalue['SalaryHeadItems']['item_type'];
                    $salaryHeads[$key]['occurance'] = $shivalue['SalaryHeadItems']['occurance'];
                    $salaryHeads[$key]['date'] = $shivalue['SalaryHeadItems']['start_from'];
                    $salaryHeads[$key]['comments'] = $shivalue['SalaryHeadItems']['comments'];
                    $salaryHeads[$key]['isinslip'] = $shivalue['SalaryHeadItems']['is_show_salslip'];
                    $salaryHeads[$key]['part'] = $shivalue['SalaryHeadItems']['item_part'];
                    //$salaryHeads[$key]['fkey'] = $shivalue['TSC']['salary_head_item_Fkey'];
                    //debug($shivalue['SalaryHeadItems']['salary_head_item_pkey']);
                    if (in_array($shivalue['SalaryHeadItems']['salary_head_item_pkey'], $salarytsc)) {
                        $salaryHeads[$key]['fkey'] = 1;
                    }
                    else
                    {
                        $salaryHeads[$key]['fkey'] = 0;
                    }
                    //debug($salaryHeads[$key]['fkey']);
                    if ($shivalue['SalaryHeadItems']['value'] == "Y") {
                        $salaryHeads[$key]['checked'] = true;
                        $salaryHeads[$key]['selected'] = true;
                    }
                    $salaryHeads[$key]['key'] = $shivalue['SalaryHeadItems']['salary_head_item_pkey'];

                    $key++;
                }
            }

            echo json_encode($salaryHeads);
        }
    }

    function removeSalaryHead() {
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array('success' => false, "msg" => "Error while saving");

        //$salaryHeadItem = $_REQUEST['salary_head_item'];
        //	debug($salaryHeadItem);
        if (isset($_REQUEST['salary_head_item'])) {

            $salaryHeadItem = explode(',', $_REQUEST['salary_head_item']);
            //debug($salaryHeadItem);
            $head_fkey = $_REQUEST['salary_head'];
            if (is_array($salaryHeadItem)) {
                //debug($salaryHeadItem);
                //$this->SalaryHeadItems->updateAll(array('value' => "'N'"),array("head_fkey"=>$head_fkey));

                foreach ($salaryHeadItem as $k => $v) {
                    $data["value"] = "N";
                    $data["salary_head_item_pkey"] = $v;
                    $this->SalaryHeadItems->save($data);
                }

                // $this->SalaryHeadItems->updateAll( array('value' => "'N'"),array("status"=>1));
                $resp["success"] = true;
                $resp["msg"] = "Salary Heads Removed Successfully";
            }
        }

        echo json_encode($resp);
        ///$this->redirect("index");
    }

    function saveSalaryHead() {
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array('success' => false, "msg" => "Error while saving");

        //$salaryHeadItem = $_REQUEST['salary_head_item'];
        //	debug($salaryHeadItem);
        if (isset($_REQUEST['salary_head_item'])) {

            $salaryHeadItem = explode(',', $_REQUEST['salary_head_item']);
            //debug($salaryHeadItem);
            $head_fkey = $_REQUEST['salary_head'];
            if (is_array($salaryHeadItem)) {
                //debug($salaryHeadItem);
                //$this->SalaryHeadItems->updateAll(array('value' => "'N'"),array("head_fkey"=>$head_fkey));

                foreach ($salaryHeadItem as $k => $v) {
                    $data["value"] = "Y";
                    $data["salary_head_item_pkey"] = $v;
                    $this->SalaryHeadItems->save($data);
                }

                // $this->SalaryHeadItems->updateAll( array('value' => "'N'"),array("status"=>1));
                $resp["success"] = true;
                $resp["msg"] = "Salary Heads Saved Successfully";
            }
        }

        echo json_encode($resp);
        ///$this->redirect("index");
    }

    public function form() { //echo "hlo";die();
        $this->layout = null;
   $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        // $id = $this->params['url']['id']; 
        //  echo $id;die();
        if (isset($this->params['url']['id'])) {
            $id = $this->params['url']['id'];
            //echo $arg;exit;
            //print_r($_GET);die();
            $this->set('id', $id);
            $salaryDB = $this->SalaryHeads->find("all", array("conditions" => array("status" => 1, "head_pkey" => $id)));
            $desc['name'] = $salaryDB[0]['SalaryHeads']['head_desc'];
            $desc ['operator']= $salaryDB[0]['SalaryHeads']['head_operator'];
            $desc ['head_occurance']= $salaryDB[0]['SalaryHeads']['head_occurance'];
            $this->set('desc', $desc);
        }
        //echo $id.$desc;die();
        //$desc="ts";
        //$data["SALARYHEAD_NAME"]="";
        //$this -> layout = null;
    }

    public function addsalaryheads() { //echo "hlo";die();
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        if (isset($_REQUEST['SALARYHEAD_ID'])) {
            $arr_form_data['head_pkey'] = $arr_form_data['SALARYHEAD_ID'];
            $arr_form_data['head_desc'] = $arr_form_data['SALARYHEAD_NAME'];
            $arr_form_data['head_operator'] = $arr_form_data['ITEM_OPERATOR'];
            $arr_form_data['head_occurance'] = $arr_form_data['ITEM_OCCURANCE'];
            $result = $this->SalaryHeads->save($arr_form_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Salary Head Added";
        } else {
            if (isset($_REQUEST['SALARYHEAD_NAME'])) {
                $arr_form_data['head_desc'] = $arr_form_data['SALARYHEAD_NAME'];
                $arr_form_data['head_operator'] = $arr_form_data['ITEM_OPERATOR'];
                $arr_form_data['head_occurance'] = $arr_form_data['ITEM_OCCURANCE'];
                $arr_form_data['salary_head_order1'] = 1;
                $arr_form_data['status'] = 1;
                $result = $this->SalaryHeads->save($arr_form_data);
                $resp = array();
                $resp["success"] = true;
                $resp["msg"] = "Salary Head Added";
            }
        }
    }

    public function form_items($id, $kid) {
        $this->set('id', $id);
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
          $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
              $salaryDB1 = $this->SalaryHeads->find("all", array("conditions" => array("status" => 1, "head_pkey" => $id)));
            //  debug($salaryDB1);
           // $desc['name'] = $salaryDB1[0]['SalaryHeads']['head_desc'];
            $desc = $salaryDB1[0]['SalaryHeads']['head_operator'];
             $head_occ= $salaryDB1[0]['SalaryHeads']['head_occurance'];
          $this->set('ocurrance', $head_occ);
            $this->set('Operator', $desc);
        if ($kid >= 1) {
            $this->set('kid', $kid);
            //$this->set('id', $id);
            $salaryDB = $this->SalaryHeadItems->find("all", array("conditions" => array("status" => 1, "salary_head_item_pkey" => $kid)));
            //debug($salaryDB);die();
            $SalaryHeadItems['item'] = $salaryDB[0]['SalaryHeadItems']['item'];
            $SalaryHeadItems['item_type'] = $salaryDB[0]['SalaryHeadItems']['item_type'];
            $SalaryHeadItems['item_value'] = $salaryDB[0]['SalaryHeadItems']['item_value'];
            $SalaryHeadItems['occurance'] = $salaryDB[0]['SalaryHeadItems']['occurance'];
            $SalaryHeadItems['start_from'] = $salaryDB[0]['SalaryHeadItems']['start_from'];
            $SalaryHeadItems['comments'] = $salaryDB[0]['SalaryHeadItems']['comments'];
            $SalaryHeadItems['is_show_salslip'] = $salaryDB[0]['SalaryHeadItems']['is_show_salslip'];
            $SalaryHeadItems['item_part'] = $salaryDB[0]['SalaryHeadItems']['item_part'];
            $this->set('SalaryHeadItems', $SalaryHeadItems);
        }
        $this->layout = null;
    }

    public function addsalaryheaditems() {  //echo "test";die();
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        if (isset($arr_form_data['SALARYHEADITEM_ID'])) {
            $arr_data['salary_head_item_pkey'] = $arr_form_data['SALARYHEADITEM_ID'];
            $arr_data['head_fkey'] = $arr_form_data['ITEM_ID'];
            $arr_data['item'] = $arr_form_data['ITEM_NAME'];
            $arr_data['item_value'] = $arr_form_data['ITEM_VALUE'];
            $arr_data['occurance'] = $arr_form_data['ITEM_OCCURANCE'];
            $arr_data['start_from'] = $arr_form_data['ITEM_START'];
            $arr_data['item_type'] = $arr_form_data['ITEM_TYPE'];
            $arr_data['comments'] = $arr_form_data['ITEM_COMMENT'];
            $arr_data['is_show_salslip'] = isset($arr_form_data['ITEM_IN_SAL'])?$arr_form_data['ITEM_IN_SAL']:'N';
            $arr_data['item_part']=$arr_form_data['ITEM_PART'];
            $result = $this->SalaryHeadItems->save($arr_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Data updated sucessfully";
            echo json_encode($resp);
        } else if (isset($arr_form_data['ITEM_ID'])) {
            $arr_data['head_fkey'] = $arr_form_data['ITEM_ID'];
            $arr_data['item'] = $arr_form_data['ITEM_NAME'];
            $arr_data['item_value'] = $arr_form_data['ITEM_VALUE'];
            $arr_data['occurance'] = $arr_form_data['ITEM_OCCURANCE'];
            $arr_data['start_from'] = $arr_form_data['ITEM_START'];
            $arr_data['item_type'] = $arr_form_data['ITEM_TYPE'];
            $arr_data['comments'] = $arr_form_data['ITEM_COMMENT'];
            $arr_data['status'] = 1;
            $arr_data['is_show_salslip'] =  isset($arr_form_data['ITEM_IN_SAL'])?$arr_form_data['ITEM_IN_SAL']:'N';
           $arr_data['item_part']=$arr_form_data['ITEM_PART'];
            $arr_data['salary_head_item_order1'] = 5;
            $result = $this->SalaryHeadItems->save($arr_data);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Data added sucessfully";
            echo json_encode($resp);
            //$this -> render('index');
        }
        //echo "<pre>";
        //  print_r($arr_data);die();
    }

    public function DeleteHead() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        $head_fkey = $_REQUEST['salary_head'];
        //$arr_form_data = $this -> request -> data;
        //print_r($arr_form_data);die();
        $arr_form_data['head_pkey'] = $head_fkey;
        $arr_form_data['status'] = 0;
        $this->SalaryHeads->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Salary Head Removed";
        echo json_encode($resp);
    }

}
