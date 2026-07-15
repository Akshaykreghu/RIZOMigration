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
class SupplierMasterController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout="default";
    public $name ='SupplierMaster';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('SupplierMaster');
    public function index() {
        
    }

    public function addnewrow() {
        
    }
public function form($acct_payable_pkey=0)
 {
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $this->set("all_store", $all_store = $this->Store->find("all", array("conditioin" => array('status' => 1))));
        $arr_att = $this->MaterialRequest->query("SELECT material_request.*,mr_details.*"
                . " from material_request as material_request "
                . "left join mr_details as mr_details on (material_request.mr_pkey = mr_details.mr_fkey) "
                . "where material_request.mr_pkey = '$acct_payable_pkey' ");
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

                    $this->set("all_store",$all_store=$this->Store->find("all",array("conditioin"=>array('status'=>1))));
                    //debug($all_store);
                    $arr_att = $this->MaterialRequest->query("SELECT *"
                            . "FROM `material_request`"
                            . " WHERE status='1';");
                  // debug($arr_att);
                    $out = array();
                        foreach ($arr_att as $key => $value) {
                            $out['mr_pkey'] = isset($value['material_request']['mr_pkey']) ? $value['material_request']['mr_pkey'] : '';
                            $out['mr_code'] = isset($value['material_request']['mr_code']) ? $value['material_request']['mr_code'] : '';
                            $out['client_name'] = isset($value['material_request']['client_name']) ? $value['material_request']['client_name'] : '';
                            $out['location'] = isset($value['material_request']['location']) ? $value['material_request']['location'] : '';
                            $out['mr_date'] = isset($value['material_request']['mr_date']) ? $value['material_request']['mr_date'] : '';
                            $out['store_code'] = isset($value['material_request']['store_code']) ? $value['material_request']['store_code'] : '';
                            $out['created_by'] = isset($value['material_request']['created_by']) ? $value['material_request']['created_by'] : '';


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

public function save() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;

        $arr_form_data['created_by'] = $this->Session->read('user_id');
//modification name
        if ($arr_form_data['mr_pkey'] == 'mr_pkey') {
            $arr_form_data['modified_by'] = $this->Session->read('user_id');
        }
        $this->MaterialRequest->save($arr_form_data);
//last  insert id
        if ($arr_form_data['mr_pkey'] == '') {
            $arr_form_data['mr_fkey'] = $this->MaterialRequest->getInsertID();
        }
//mutiple save      
        $arr = array();
        $count = count($arr_form_data['item_no']);
        $arr = $arr_form_data;
        for ($i = 0; $i < $count; $i++) {
            $arr_form_data['item_no'] = $arr['item_no'][$i];
            $arr_form_data['required_qty'] = $arr['required_qty'][$i];
            $arr_form_data['incoming_qty'] = $arr['incoming_qty'][$i];
            $arr_form_data['incoming_date'] = $arr['incoming_date'][$i];
            $arr_form_data['re_order_level'] = $arr['re_order_level'][$i];
            $arr_form_data['item_description'] = $arr['item_description'][$i];
            $arr_form_data['package'] = $arr['package'][$i];
            $arr_form_data['unit'] = $arr['unit'][$i];
            $this->MaterialRequestDetails->save($arr_form_data);
        }
    }

}
