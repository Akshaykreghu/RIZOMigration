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
    public $name = 'MaterialRequest';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('MaterialRequest', 'MaterialRequestDetails', 'Store', 'Item', 'PackageMaster', 'QuantityDetails', 'AdditionalDetails', 'ItemDetails', 'WarrantyDetails', 'Contacts', 'Site','EmployeeDetails');

    public function home() {
        
    }

    public function index() {

	
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->PackageMaster->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
		$this->Site->useDbConfig = $this->Session->read('ds');
//all item      
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));



        //debug($all_item);
//all store
        if($this->Session->read('user_group') == 2){
            $emp_fkey = $this->Session->read('emp_fkey');
            $conditions = "	store_master_pkey in(select store_pkey from access_store where emp_fkey = '$emp_fkey')";
            $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1,$conditions))));
        }else{
            $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
        }
        //debug($all_store);
//package
        $this->set("all_package", $all_package = $this->PackageMaster->find("all", array("conditions" => array('status' => 1))));
        // debug($all_package) ;
//Contacts
        $this->set("all_contact", $all_contact = $this->MaterialRequest->query("SELECT * FROM `contacts` WHERE `relationship`='customer' AND status='1' "));
        //debug($all_contact);
//site
        
        if($this->Session->read('user_group') == 2){
            $emp_fkey = $this->Session->read('emp_fkey');
            $conditions = "	site_pkey in(select store_pkey from access_store where emp_fkey = '$emp_fkey')";
            $this->set("all_site", $all_site = $this->Site->find("all", array("conditions" => array('status' => 1,$conditions))));
        }else{
            $this->set("all_site", $all_site = $this->Site->find("all", array("conditions" => array('status' => 1))));
        }
        
        //debug($all_site);
    }
    
    public function showDetails($mr_pkey = 0){
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $data = $this->MaterialRequest->find("all",array("conditions"=>array("mr_pkey"=>$mr_pkey)));
        
        $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.required_qty,itemmaster.item_desc,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*"
                . "from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                . "where materialrequest.mr_pkey = '$mr_pkey' and mrdetails.status = '1' ");
        
//        debug($arr_material_master);
        $this->set("arr_att", $data);
        $this->set("arr_material_master", $arr_material_master);
    }

    public function loadnew() {
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->PackageMaster->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->Site->useDbConfig = $this->Session->read('ds');
//all item      
       // $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));


//all store
        if ($this->Session->read('user_group') == 2) {
//            $emp_fkey = $this->Session->read('emp_fkey');
//            $conditions = "	store_master_pkey in(select store_pkey from access_store where emp_fkey = '$emp_fkey')";
//            $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1, $conditions))));

            
            $cur_emp_key = $this->Session->read("emp_fkey");
            $join = array(array(
                    'table' => 'access_store',
                    'type' => 'INNER',
                    'conditions' => array(
                        'access_store.store_pkey = Store.store_master_pkey'
                    )
            ));
            $fields=array('DISTINCT `Store`.`store_master_pkey`, `Store`.`store_code`, `Store`.`store_location`, `Store`.`address`, `Store`.`city`, `Store`.`state`, `Store`.`pincode`, `Store`.`store_manager`, `Store`.`roc`, `Store`.`tan`, `Store`.`tin`, `Store`.`rtgs`, `Store`.`created_by`, `Store`.`creation_date`, `Store`.`modified_by`, `Store`.`modified_date`, `Store`.`status`');
            $conditions = array("Store.status" => 1, "access_store.emp_fkey" => $cur_emp_key,"access_store.status"=>1);
            $this->set("all_store", $all_store = $this->Store->find("all", array("fields"=>$fields,"conditions" => $conditions,"joins"=>$join)));
        } else {
            $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
        }
        //debug($all_store);
//package
       // $this->set("all_package", $all_package = $this->PackageMaster->find("all", array("conditions" => array('status' => 1))));
        // debug($all_package) ;
//Contacts
        //$this->set("all_contact", $all_contact = $this->MaterialRequest->query("SELECT * FROM `contacts` WHERE `relationship`='customer' AND status='1' "));
        //debug($all_contact);
//site
        //$this->set("all_site", $all_site = $this->Site->find("all", array("conditions" => array('status' => 1))));
        $this->render('loadnewrequest');
    }
    
    public function addnewrow() {
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
    }

    public function form($mr_pkey = 0) {
        if ($mr_pkey == 0) {
            $title = 'ADD MATERIAL REQUEST';
        } else {
            $title = 'EDIT MATERIAL REQUEST';
        }
        $this->set('title', $title);
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbConfig = $this->Session->read('ds');
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
    public function materiallist() {

        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
		$this->Store->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();

        $count = $this->MaterialRequest->find("count", array("conditions" => array('status' => 1)));

        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
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
    public function materialdelete() {
        $this->autoRender = FALSE;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["mr_pkey"])) {
            $ar_ids = explode(",", $_REQUEST["mr_pkey"]);
            //debug($ar_ids);
            $this->MaterialRequest->updateAll(
                    array('MaterialRequest.status' => 0), array('MaterialRequest.mr_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

//form save
    public function save() {
        $this->autoRender = FALSE;
        $this->layout = null;
		$this->MaterialRequest->useDbConfig = $this->Session->read('ds');
		$this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $arr_form_data['mr_date'] = date("Y-m-d",strtotime($arr_form_data['mr_date']));
        $this->MaterialRequest->save($arr_form_data);
        $getlastid = isset($arr_form_data['mr_pkey']) && !empty($arr_form_data['mr_pkey']) ? $arr_form_data['mr_pkey'] : $this->MaterialRequest->getInsertid();
        $itemid = $arr_form_data['item_code'];
        $arr_form_data['item_code'] = $itemid;
        $allitem = $this->getitem($arr_form_data['item_code']);
        //debug($allitem);
        
        $itemkey = $arr_form_data['mr_pkey'] = $getlastid;
        $arr_form_data['unit'] = $arr_form_data['required_qty'];
        $arr_form_data['mr_fkey'] = $getlastid;
        $arr_form_data['package'] = ' ';
        $arr_form_data['re_order_level'] = 0;
        foreach ($allitem as $value) {
            $arr_form_data['package'] = isset($value['itemdetails']['package'])?$value['itemdetails']['package']:0;
            $arr_form_data['re_order_level'] = $value['qtydetails']['re_order_level'];
        }
        if($this->CheckIfExists($getlastid,$itemid)){
            //debug("available");
            $this->UpdateIfExists($getlastid,$itemid,$arr_form_data['required_qty']);
            echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $getlastid));
            return;
        }else{
            //debug("Not available");
        }
        
        $this->MaterialRequestDetails->saveAll($arr_form_data);
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $itemkey));
    }
    
    public function CheckIfExists($mr_fkey= 0,$item_code = 0) {
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $item_check = $this->MaterialRequestDetails->find("all",array("conditions"=>array("mr_fkey"=>$mr_fkey,"item_code"=>$item_code,"status"=>1)));
        //debug($item_check);
        if($item_check){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    public function UpdateIfExists($mr_fkey= 0,$item_code = 0,$new = 0){
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $item_check = $this->MaterialRequestDetails->query("update mr_details set required_qty = required_qty+$new,unit = unit+$new where mr_fkey = $mr_fkey and item_code = $item_code ");
        return;
    }

//get itemid
    public function getitemid($name = '') {
        $this->autoRender = false;
		$this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
        $arr_item_id = $this->Item->find(array("conditions"=>array("item_desc"=>$name)));
        $id = $arr_item_id['0']['item_master']['item_master_pkey'];
        return $id;
    }

//get itemdettails
    public function getitem($id = '') {
        $this->autoRender = false;
		$this->Item->useDbConfig = $this->Session->read('ds');
        $arr_item = $this->Item->query("SELECT itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*"
                . " from item_master as itemmaster "
                . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                . "where itemmaster.item_master_pkey = '$id' ");

        return $arr_item;
    }
public function materialtable() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $conditions = '';
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->datatable["conditions"] = array('status' => 1);

        if (isset($_REQUEST['emp'])) {
            $qs = $_REQUEST['emp'];
            $conditions .= "and (materialrequest.mr_code like '%" . $qs . "%' OR  materialrequest.remarks like '%" . $qs . "%' OR materialrequest.location like '%" . $qs . "%' OR materialrequest.customer_name like '%" . $qs . "%' )";
        }
//        //added by megha on 8_07_19 filter store for employee login
//        $user_group = $this->Session->read('user_group');
//        if ($user_group == 2) {
//            $emp_fkey = $this->Session->read('emp_fkey');
//            $store = $this->MaterialRequest->query("select store_pkey from access_store where emp_fkey = $emp_fkey ");
//            $store_pkey = $store['0']['access_store']['store_pkey'];
//            $conditions .= " and materialrequest.store_code =$store_pkey ";
//        }
//        //filter store for employee login end
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
//            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
//            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
//            $conditions .= " and materialrequest.store_code in (select store_pkey from access_store where emp_fkey in (select emp_pkey from emp_details where branch_code='".$cur_emp_branch."') and status=1)";
//            $count_conditions = "MaterialRequest.store_code in (select store_pkey from access_store where emp_fkey in (select emp_pkey from emp_details where branch_code='".$cur_emp_branch."') and status=1)";

            $conditions .= " and materialrequest.store_code in (select store_pkey from access_store where emp_fkey =" . $cur_emp_key . " and status=1)";
            $count_conditions = " MaterialRequest.store_code in (select store_pkey from access_store where emp_fkey =" . $cur_emp_key . " and status=1)";
        } else {
            $conditions .= "";
            $count_conditions = "";
        }
        //employee branch wise sorting ends here
        if (isset($_REQUEST['item_desc'])) {
            $item = $_REQUEST['item_desc'];
            $conditions .= "and materialrequest.mr_pkey in (select mr_fkey from  mr_details where mr_details.item_code = '$item' and status = '1' ) ";
            $resp_att = array();
            $resp_att["rows"] = array();
//            $count = $this->MaterialRequest->find("count", array("conditions" => array('status' => 1,$count_conditions)));
            $count_array = $this->MaterialRequest->query("select count(*) as count FROM material_request as materialrequest  
            join store_master as storemaster on (storemaster.store_master_pkey = materialrequest.store_code)
            WHERE materialrequest.status = '1'  $conditions and storemaster.status=1");
            $count = $count_array[0][0]['count'];

            $arr_meterial = $this->MaterialRequest->query("SELECT materialrequest.mr_pkey,materialrequest.mr_code,materialrequest.po_status,materialrequest.mr_date,materialrequest.location,storemaster.store_location,storemaster.store_master_pkey,materialrequest.remarks,
            materialrequest.customer_name,materialrequest.customer_name,(select sum(required_qty) from mr_details where mr_fkey = materialrequest.mr_pkey) as required,(select sum(ordering_qty) from mr_details where mr_fkey = materialrequest.mr_pkey) as ordered
            FROM material_request as materialrequest  
            join store_master as storemaster on (storemaster.store_master_pkey = materialrequest.store_code)
            WHERE materialrequest.status = '1'  $conditions and storemaster.status=1 order by materialrequest.mr_pkey desc limit $limit OFFSET $ofst ");
        } else {
//            $count = $this->MaterialRequest->find("count", array("conditions" => array('status' => 1,$count_conditions)));
            $count_array = $this->MaterialRequest->query("select count(*) as count FROM material_request as materialrequest  
            join store_master as storemaster on (storemaster.store_master_pkey = materialrequest.store_code)
            WHERE materialrequest.status = '1'  $conditions and storemaster.status=1");
            $count = $count_array[0][0]['count'];

            $arr_meterial = $this->MaterialRequest->query("SELECT materialrequest.mr_pkey,materialrequest.mr_code,materialrequest.po_status,materialrequest.mr_date,materialrequest.location,storemaster.store_location,storemaster.store_master_pkey,materialrequest.remarks,
materialrequest.customer_name,materialrequest.customer_name,(select sum(required_qty) from mr_details where mr_fkey = materialrequest.mr_pkey and status = '1' ) as required,(select sum(ordering_qty) from mr_details where mr_fkey = materialrequest.mr_pkey and status = '1' ) as ordered
 "
                    . "FROM material_request as materialrequest "
                    . "left join store_master as storemaster on (storemaster.store_master_pkey = materialrequest.store_code) "
                    . " WHERE materialrequest.status = '1'  $conditions and storemaster.status=1 order by materialrequest.mr_pkey desc limit $limit OFFSET $ofst ");
        }

        $out = array();
        $array_mrstatus = array(1 => "MR Requested", 2 => "Po Applied", 3 => "GRN Received");
        foreach ($arr_meterial as $key => $value) {
            $out['mr_pkey'] = isset($value['materialrequest']['mr_pkey']) ? $value['materialrequest']['mr_pkey'] : '';
            $out['mr_code'] = isset($value['materialrequest']['mr_code']) ? $value['materialrequest']['mr_code'] : '';
            $out['mr_date'] = isset($value['materialrequest']['mr_date']) ? $value['materialrequest']['mr_date'] : '';
            $out['location'] = isset($value['materialrequest']['location']) ? $value['materialrequest']['location'] : '';
            $out['att'] = isset($value['materialrequest']['att']) ? $value['materialrequest']['att'] : '';
            $out['customer_name'] = isset($value['materialrequest']['customer_name']) ? $value['materialrequest']['customer_name'] : '';
            $out['customer_po_number'] = isset($value['materialrequest']['customer_po_number']) ? $value['materialrequest']['customer_po_number'] : '';
            $out['store_code'] = isset($value['storemaster']['store_location']) ? $value['storemaster']['store_location'] : '';
            $out['remarks'] = isset($value['materialrequest']['remarks']) ? $value['materialrequest']['remarks'] : '';
            $out['required'] = isset($value['0']['required']) ? $value['0']['required'] : 0;
            $out['ordered'] = isset($value['0']['ordered']) ? $value['0']['ordered'] : 0;

            $out['matstatus'] = $array_mrstatus[isset($value['materialrequest']['po_status']) ? $value['materialrequest']['po_status'] : 0];
            $out['store_fkey'] = isset($value['storemaster']['store_master_pkey']) ? $value['storemaster']['store_master_pkey'] : '';
            $out['pending'] = (isset($value['0']['required']) ? $value['0']['required'] : 0) - (isset($value['0']['ordered']) ? $value['0']['ordered'] : 0);
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = (int)$count;
        echo json_encode($resp_att);
    }

    
//down table
    public function loadtable($id, $rowindex = 1) {
        $this->autoRender = false;
        //debug($id);
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');

        $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.*"
                . "from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                . "where materialrequest.mr_pkey = '$id' and mrdetails.status = '1' ");

        //debug($arr_material_master);


        $data = '<h3 style="text-align:center; ">Item Details</h3> <hr style="border-top:1px solid #000; ">';
        $data .= '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red; text-align: -webkit-auto; ">';
        $data .=' <thead>';
        $data .=' <tr class="warning">';
        $data .=' <th> Sl no </th>';
        $data .=' <th> Item Name </th>';
        $data .=' <th> Item Code </th>';
        $data .=' <th>Required Qty* </th>';
//        $data .= '  <th>Current Stock</th> ';
//        $data .= '  <th>Incoming Qty</th>';
//        $data .= '<th>Incoming Date</th>';
        $data .= '  <th>Re Order Level</th>';
        $data .= '  <th>Package</th>';
        $data .= '  <th>Action</th>';

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
            $data .= '<tr>';
            $data .= '<td> ' . $i . '</td>';

            $data .= '<td> ' . $value["itemmaster"]["item_desc"] . '</td>';
            $data .= '<td> ' . $value["itemmaster"]["item_code"] . '</td>';
            $data .= '<td> ' . $value["mrdetails"]["required_qty"] . '</td>';
//            $data .= '<td> ' . $value["mrdetails"]["current_stock"] . '</td>';
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

    public function editorder($id) {
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
//all item      
        //debug($id);

        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
        $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.*,itemdetails.*,qtydetails.*,warrantydetails.*,additionaldetails.* "
                . "from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                . "left join item_details as itemdetails on (itemmaster.item_master_pkey = itemdetails.item_master_fkey) "
                . "left join quantity_details as qtydetails on (itemmaster.item_master_pkey = qtydetails.item_master_fkey) "
                . "left join waranty_details as warrantydetails on (itemmaster.item_master_pkey = warrantydetails.item_master_fkey)"
                . " left join additional_details as additionaldetails on (itemmaster.item_master_pkey = additionaldetails.item_master_fkey) "
                . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                . "where mrdetails.mr_details_pkey = '$id' and mrdetails.status = 1 ");
        foreach ($arr_material_master as $key => $value) {
            $out['mr_fkey'] = isset($value['mrdetails']['mr_fkey']) ? $value['mrdetails']['mr_fkey'] : '';
        }
        $this->set('id', $id);
        $this->set('arr_material_master', $arr_material_master);
    }
    public function editordersave() {
        $this->autoRender = false;
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
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
    public function deleteordermaster($id = 0) {
        $this->autoRender = false;
		$this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        if ($id != 0) {
            $this->MaterialRequest->updateAll(array('status' => 0), array('mr_pkey' => $id));
            
            echo json_encode(array('msg' => 'Material Request deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'Material Request deletion failed!'));
        }
    }
    public function deleteorder($id = 0) {

        $this->autoRender = false;
		$this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        if ($id != 0) {
            //$this->MaterialRequestDetails->updateAll(array('status' => 0), array('mr_details_pkey' => $id));
            $this->MaterialRequestDetails->query("DELETE FROM mr_details WHERE mr_details_pkey = $id");

            echo json_encode(array('msg' => "Item successfully removed"));
        } else {
            echo json_encode(array('msg' => "Item failed to removed"));
        }
    }

    public function getautocompletionsmr_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['mr_code']) && !empty($_REQUEST['mr_code'])) {
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
    public function getautocompletionssite_name() {
        $this->autoRender = false;
        if (isset($_REQUEST['site_name']) && !empty($_REQUEST['site_name'])) {
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
    public function getautocompletionsgstore_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['gstore_code']) && !empty($_REQUEST['gstore_code'])) {
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
    public function getautocompletionscustomer_name() {
        $this->autoRender = false;
        if (isset($_REQUEST['customer_name']) && !empty($_REQUEST['customer_name'])) {
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
    public function getautocompletionscustomer_po_number() {
        $this->autoRender = false;
        if (isset($_REQUEST['customer_po_number']) && !empty($_REQUEST['customer_po_number'])) {
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
//    public function getautocompletionsitem_code() {
//        $this->autoRender = false;
//        if (isset($_REQUEST['item_code']) && !empty($_REQUEST['item_code'])) {
//            $searchkey = $_REQUEST['item_code'];
//            $filter_condition = 'item_desc LIKE "%' . $searchkey . '%"';
//            $this->Item->useDbConfig = $this->Session->read('ds');
//            $arr_item_name = $this->Item->query("SELECT * FROM `item_master` WHERE `status`=1 and $filter_condition");
//            $arr_filterresult = array();
//            foreach ($arr_item_name as $val) {
//                $arr_filterresult[] = isset($val['item_master']) ? $val['item_master'] : array();
//            }
//        }
//        echo json_encode($arr_filterresult);
//    }
    //edited by megha 3_5_19
    public function getautocompletionsitem_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['item_code']) && !empty($_REQUEST['item_code'])) {
            $searchkey = $_REQUEST['item_code'];
            $store_fkey = $_REQUEST['store_code'];
            $filter_condition = 'item_desc LIKE "%' . $searchkey . '%"';
            $this->Item->useDbConfig = $this->Session->read('ds');
            $arr_item_name = $this->Item->query("SELECT * FROM `item_master` WHERE `status`=1 and $filter_condition");
            $arr_item_name_details = array();
            foreach ($arr_item_name as $value) {
                $item = $value['item_master']['item_master_pkey'];
                $arr_item = $this->Item->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
                item_except_grn_allocation_view  
                where store_master_pkey= '$store_fkey' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
                grn_stock_details_date_view 
                where store_master_pkey= '$store_fkey' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where store_master_pkey= '$store_fkey' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
                grn_stock_det_rate_date_view where store_master_pkey= '$store_fkey' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
                group by store_master_pkey,item_master_pkey
                order by 4,5");
                $arr_items = array();
                if(empty($arr_item)){
                    $arr_items['qtyuptodate'] = 0;
                }
                else{
                   $arr_items['qtyuptodate']=$arr_item['0']['0']['qtyuptodate']; 
                }
            
                $arr_item_name_details[] = isset($value['item_master']['item_master_pkey']) ? array_merge($value['item_master'],$arr_items) : array();
            
            }
        }      
        echo json_encode($arr_item_name_details);
    }
      //edited by megha 3_5_19

//itemcode
    public function getautocompletionsitemcode() {
        $this->autoRender = false;
        if (isset($_REQUEST['itemcode']) && !empty($_REQUEST['itemcode'])) {
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
    public function generatereport() {
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
        if ($originalDate != '') {
            $fromdate = date("Y-m-d", strtotime($originalDate));
        } else {
            $fromdate = date("2030-12-31", strtotime($originalDate));
        }
        $arr_request['fromdate'] = $fromdate;
        $originalDate1 = isset($arr_request['todate']) ? $arr_request['todate'] : '';
        if ($originalDate1 !== '') {
            $todate = date("Y-m-d", strtotime($originalDate1));
        } else {
            $todate = date("2222-12-31", strtotime($originalDate1));
        }
        $arr_request['todate'] = $todate;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.* "
                . "from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                . "where materialrequest.status= '1' AND materialrequest.mr_date BETWEEN '$fromdate' AND '$todate' AND mrdetails.status='1'   "
                . "$storecode $itemcode ");
        $this->set('arr_material_master', $arr_material_master);
        $site = array();
        foreach ($arr_material_master as $materialmaster) {
            $mr_pkey = $materialmaster['materialrequest']['mr_pkey'];
            $site[$mr_pkey]['material_master'] = $materialmaster['materialrequest'];
            $site[$mr_pkey]['store_details'] = $materialmaster['storemaster']['store_code'];
            $arr_data['itemname'] = $materialmaster['itemmaster']['item_desc'];
            $arr_data['required_qty'] = $materialmaster['mrdetails']['required_qty'];
            $site[$mr_pkey]['material_details'][] = $arr_data;
        }
        $this->set('site', $site);
    }

//storeprint
    private function generateitem($itemcode = '') {
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $arr_reportfields = array();
        //$itemcode=$itemcode;
        $arr_materialmaster = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.* "
                . "from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
                . "left join store_master as storemaster on (materialrequest.store_code = storemaster.store_master_pkey) "
                . "where materialrequest.status= '1' and mrdetails.item_code='$itemcode' and mrdetails.status = 1 ");
        return $arr_materialmaster;
    }

    //itemprint
    private function generatestorecode($sitename = '') {
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $arr_reportfields = array();
        $location = $sitename;
        $arr_material_master = $this->MaterialRequest->query("SELECT materialrequest.*,storemaster.*,mrdetails.*,itemmaster.* "
                . "from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "left join item_master as itemmaster on (mrdetails.item_code = itemmaster.item_master_pkey)"
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
    public function itemfilter(){
        $this->autoRender = false;
        $this->Store->useDbConfig = $this->Session->read('ds');
        
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and item_desc like '%$q%'";
        } else {
            $q_condition = "";
        }
        $arr_site = $this->Store->query("select * from item_master where status= '1' $q_condition order by item_desc asc");
        $array = array();
        $stores = array();
       // $stores[] = array("id" => "0", "text" => "ALL");
        foreach ($arr_site as $key => $value) {
            $stores[] = array(
                'id' => $value['item_master']['item_master_pkey'],
                'text' => $value['item_master']['item_desc']
            );
        }
        $array['items'] = $stores;
//        debug($site);
        echo json_encode($array);
    }
        public function getitem_code($store_fkey = 0,$item = 0) {
        $this->autoRender = false;
        $this->Item->useDbConfig = $this->Session->read('ds');
            $arr_item_name = $this->Item->query("SELECT * FROM `item_master` WHERE `status`=1 and item_master_pkey = $item");
            $arr_item_name_details = array();
                $arr_item = $this->Item->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
                item_except_grn_allocation_view  
                where store_master_pkey= '$store_fkey' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
                grn_stock_details_date_view 
                where store_master_pkey= '$store_fkey' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where store_master_pkey= '$store_fkey' and item_master_pkey= '$item'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
                grn_stock_det_rate_date_view where store_master_pkey= '$store_fkey' and item_master_pkey= '$item' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
                group by store_master_pkey,item_master_pkey
                order by 4,5");
                $arr_items = array();
                if(empty($arr_item)){
                    $arr_items['qtyuptodate'] = 0;
                }
                else{
                   $arr_items['qtyuptodate']=$arr_item['0']['0']['qtyuptodate']; 
                }
            
                $arr_item_name_details = array_merge($arr_item_name['0']['item_master'],$arr_items);
            
                
        echo json_encode($arr_item_name_details);
    }
}
