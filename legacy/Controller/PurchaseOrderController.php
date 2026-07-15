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
class PurchaseOrderController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'PurchaseOrder';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('PurchaseOrder', 'MaterialRequest', 'PoMaterial', 'PurchaseList', 'MaterialRequestDetails', 'Store', 'PurchaseOrderDetails', 'Item', 'QuantityDetails', 'AdditionalDetails', 'ItemDetails', 'WarrantyDetails', 'Contacts', 'Site', 'UoMaster','Location','Contacts', 'EmployeeDetails');

    public function index() {
        $this->layout = NULL;
        //$this->render('index');
	$this->Item->useDbConfig = $this->Session->read('ds');
        $this->Contacts->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
//all item      
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
        
        $this->set("a_suppliers", $a_supplier = $this->Contacts->find("all", array("conditions" => array('relationship' => 'Vendor' , 'status' => 1))));
        //debug($all_item);
    }

    public function home() {
        
    }

///data grid list            
    
    public function purchaseorders() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $limit = $_POST['rows'];
        $page = $_POST['page'];
        $ofst = ($page - 1) * $limit;
        $supplier_id = isset($arr_request_data['supplier_code'])?$arr_request_data['supplier_code']:NULL;
         if($supplier_id=='ALL'||$supplier_id=='All'){
            $supplier_id =NULL;
        }
        $conditions = '';
        if ($supplier_id) {
            $conditions = " supplier_code = $supplier_id ";
            $and=" and";
        }else{
            $and="";
        }
        
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            //$conditions .= $and." po_pkey in (select po_fkey from purchase_list where mr_fkey in (select mr_pkey from material_request where store_code in (select store_pkey from access_store where emp_fkey in (select emp_pkey from emp_details where branch_code='".$cur_emp_branch."') and status=1)))";
           // $conditions .= $and." po_pkey in (select po_fkey from purchase_list where mr_fkey in (select mr_pkey from material_request where store_code in (select store_pkey from access_store where emp_fkey =".$cur_emp_key." and status=1)))";
            $conditions .= $and." po_pkey in (select po_fkey from purchase_list where mr_fkey in (select mr_pkey from material_request where store_code in (select store_pkey from access_store where emp_fkey =".$cur_emp_key." and status=1 and store_pkey in (select store_master_pkey from store_master where status=1))))";
        } else {
            $conditions .= "";
        }
	//employee branch wise sorting ends here
        
        
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->datatable["conditions"] = array('status' => 1);
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'po_number';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
        //edited by megha
        $count = $this->PurchaseOrder->find("count", array("conditions" => array('status' => 1,'grn_status'=>'1',$conditions),'limit' => intval($limit)));
           // $count = $this->PurchaseOrder->find("count", array("conditions" => array('status' => 1,$conditions),'limit' => intval($limit)));
            $arr_meterial = $this->PurchaseOrder->find("all",array("conditions"=>array("status"=>'1','grn_status'=>'1',$conditions),'limit' => intval($limit),'order' => array($sort => $order),'offset' => intval($ofst)));
        
        
        $out = array();
        $resp_att["rows"]=array();
        foreach ($arr_meterial as $key => $value) {
            $out['po_pkey'] = isset($value['PurchaseOrder']['po_pkey']) ? $value['PurchaseOrder']['po_pkey'] : '';
            $out['po_number'] = isset($value['PurchaseOrder']['po_number']) ? $value['PurchaseOrder']['po_number'] : '';
            $out['expected_date'] = isset($value['PurchaseOrder']['expected_date']) ? $value['PurchaseOrder']['expected_date'] : '';
            $out['supplier_name'] = isset($value['PurchaseOrder']['supplier_name']) ? $value['PurchaseOrder']['supplier_name'] : '';
            $out['location'] = isset($value['PurchaseOrder']['location']) ? $value['PurchaseOrder']['location'] : '';
            $out['remarks'] = isset($value['PurchaseOrder']['remarks']) ? $value['PurchaseOrder']['remarks'] : '';
            $out['po_date'] = isset($value['PurchaseOrder']['po_date']) ? $value['PurchaseOrder']['po_date'] : '';
            $out['po_type'] = isset($value['PurchaseOrder']['po_type']) ? $value['PurchaseOrder']['po_type'] : '';
            $out['supplier_code'] = isset($value['PurchaseOrder']['supplier_code']) ? $value['PurchaseOrder']['supplier_code'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    

    

    public function materialtable() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $conditions ='';
        $resp_att = array();
        $resp_att["rows"] = array();
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->datatable["conditions"] = array('status' => 1);
        
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            //$branch_condition = " and storemaster.store_master_pkey in (select store_pkey from access_store where emp_fkey in (select emp_pkey from emp_details where branch_code='" . $cur_emp_branch . "') and status=1)";
            $branch_condition = " and storemaster.store_master_pkey in (select store_pkey from access_store where emp_fkey =".$cur_emp_key." and status=1) AND storemaster.status=1";
            
        } else {
            $branch_condition = "";
        }
        //employee branch wise sorting ends here   
         //added by megha required_qty >= ordering_qty on 1_08_2019
        if (isset($_REQUEST['item_desc'])) {
            $item = $_REQUEST['item_desc'];
            $conditions = "and materialrequest.mr_pkey in (select mr_fkey from  mr_details where mr_details.item_code = '$item' and status = '1' ) ";
            
            $count = $this->MaterialRequest->find("count", array("conditions" => array('status' => 1)));
            $arr_meterial = $this->MaterialRequest->query("SELECT materialrequest.mr_pkey,materialrequest.mr_code,materialrequest.mr_date,materialrequest.location,storemaster.store_code,materialrequest.remarks,
materialrequest.customer_name,materialrequest.customer_name,(select sum(required_qty) from mr_details where mr_fkey = materialrequest.mr_pkey and status = '1' ) as required,(select sum(ordering_qty) from mr_details where mr_fkey = materialrequest.mr_pkey and status = '1' ) as ordered
FROM material_request as materialrequest  
join store_master as storemaster on (storemaster.store_master_pkey = materialrequest.store_code)
 WHERE materialrequest.status = '1' and materialrequest.mr_pkey in (select distinct(mr_fkey) from mr_details where  required_qty >= ordering_qty and status = 1) and materialrequest.status = '1' $branch_condition $conditions order by materialrequest.mr_pkey desc limit $limit offset $ofst ");
        } else {
            //edited by megha
             // $count = $this->MaterialRequest->find("count", array("conditions" => array('status' => 1)));
             $count = $this->MaterialRequest->query("SELECT count(*),materialrequest.mr_pkey,materialrequest.mr_code,materialrequest.mr_date,materialrequest.location,storemaster.store_code,materialrequest.remarks,
materialrequest.customer_name,materialrequest.customer_name,(select sum(required_qty) from mr_details where mr_fkey = materialrequest.mr_pkey and status = '1' ) as required,(select sum(ordering_qty) from mr_details where mr_fkey = materialrequest.mr_pkey and status = '1' ) as ordered
 "
                    . "FROM material_request as materialrequest "
                    . "left join store_master as storemaster on (storemaster.store_master_pkey = materialrequest.store_code) "
                    . " WHERE materialrequest.status = '1'  and materialrequest.mr_pkey in (select distinct(mr_fkey) from mr_details where  required_qty >= ordering_qty and status = 1) $branch_condition $conditions ");
            $arr_meterial = $this->MaterialRequest->query("SELECT materialrequest.mr_pkey,materialrequest.mr_code,materialrequest.mr_date,materialrequest.location,storemaster.store_code,materialrequest.remarks,
materialrequest.customer_name,materialrequest.customer_name,(select sum(required_qty) from mr_details where mr_fkey = materialrequest.mr_pkey and status = '1' ) as required,(select sum(ordering_qty) from mr_details where mr_fkey = materialrequest.mr_pkey and status = '1' ) as ordered
 "
                    . "FROM material_request as materialrequest "
                    . "left join store_master as storemaster on (storemaster.store_master_pkey = materialrequest.store_code) "
                    . " WHERE materialrequest.status = '1'  and materialrequest.mr_pkey in (select distinct(mr_fkey) from mr_details where  required_qty >= ordering_qty and status = 1) $branch_condition $conditions order by materialrequest.mr_pkey desc limit $limit offset $ofst ");
        }
        
        $out = array();
        foreach ($arr_meterial as $key => $value) {
            $out['mr_pkey'] = isset($value['materialrequest']['mr_pkey']) ? $value['materialrequest']['mr_pkey'] : '';
            $out['mr_code'] = isset($value['materialrequest']['mr_code']) ? $value['materialrequest']['mr_code'] : '';
            $out['mr_date'] = isset($value['materialrequest']['mr_date']) ? $value['materialrequest']['mr_date'] : '';
            $out['location'] = isset($value['materialrequest']['location']) ? $value['materialrequest']['location'] : '';
            $out['store_code'] = isset($value['storemaster']['store_code']) ? $value['storemaster']['store_code'] : '';
            $out['remarks'] = isset($value['materialrequest']['remarks']) ? $value['materialrequest']['remarks'] : '';
            $out['required'] = isset($value['0']['required']) ? $value['0']['required'] : 0;
            $out['ordered'] = isset($value['0']['ordered']) ? $value['0']['ordered'] : 0;
            $out['pending'] = (isset($value['0']['required']) ? $value['0']['required'] : 0) - (isset($value['0']['ordered']) ? $value['0']['ordered'] : 0);
            $resp_att["rows"][$key] = $out;
        }
         //$resp_att["total"] = count($resp_att["rows"]);
        $resp_att["total"] = $count['0']['0']['count(*)'];
        echo json_encode($resp_att);
    }
//
    public function searchm($item_desc = '') {
        $this->autoRender = FALSE;
        $searchkey = $_REQUEST['item_desc'];
        $filter_condition = 'item_desc LIKE "%' . $searchkey . '%"';
        $this->Item->useDbConfig = $this->Session->read('ds');
        $arr_item = $this->Item->query("SELECT * FROM `item_master` WHERE `status`=1 and $filter_condition");
        $arr_filterresult = array();
        foreach ($arr_item as $val) {
//              $val['MaterialRequest']['name']=$val[0]['name'];
            $arr_filterresult[] = isset($val['item_master']) ? $val['item_master'] : array();
        }

        echo json_encode($arr_filterresult);
    }


    public function details($po_pkey = 0){
        
        $this->PurchaseList->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $arr_find_meterial = $this->PurchaseList->query("select mr.mr_code,mr.mr_pkey,pr.expected_date,pr.location,pr.po_pkey,im.item_code,po.required_qty,po.po_rate,po.ordering_qty,im.item_desc,store_master.store_code from po_item_details as po
join purchase_order pr on (pr.po_pkey = po.po_fkey)
 join material_request mr on (mr.mr_pkey = po.mr_fkey)
join item_master im on (im.item_master_pkey = po.item_code)
join store_master on (store_master.store_master_pkey = mr.store_code)
 where po_fkey = $po_pkey and po.status = 1");
        
        $company_info = $this->PurchaseList->query("select * from comp_contact_info ");
        //debug($company_info);
        $joins = array(
            array(
                'table' => 'locations',
                'alias' => 'Location',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Location.location_code = PurchaseOrder.location_code'
                )
            ),
            array(
                'table' => 'contacts',
                'alias' => 'Contacts',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Contacts.contact_id = PurchaseOrder.supplier_code'
                )
            )
            );
        $fileds = array("Contacts.*,Location.*,PurchaseOrder.*");
        $arr_po_details = $this->PurchaseOrder->find("all",array("fields"=>$fileds,"joins"=>$joins,"conditions"=>array("po_pkey"=>$po_pkey)));
        $arr_merged_items = array();
        foreach ($arr_find_meterial as $key=> $val){
            $arr_merged_items[][$val['im']['item_code']] = $val;
        }
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        //debug($arr_po_details);
        
        $this->set("arr_find_meterial", $arr_merged_items);
        $this->set("po_pkey", $po_pkey);
        $this->set("company_info", $company_info);
        $this->set("arr_po_details", $arr_po_details);
        
    }
    
    public function testdownloads($po_pkey = 0){
         $this->autoRender = false;
          $this->PurchaseList->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $arr_find_meterial = $this->PurchaseList->query("select mr.mr_code,mr.mr_pkey,pr.expected_date,pr.location,pr.po_pkey,im.item_code,po.required_qty,po.po_rate,po.ordering_qty,im.item_desc,store_master.store_code from po_item_details as po
join purchase_order pr on (pr.po_pkey = po.po_fkey)
 join material_request mr on (mr.mr_pkey = po.mr_fkey)
join item_master im on (im.item_master_pkey = po.item_code)
join store_master on (store_master.store_master_pkey = mr.store_code)
 where po_fkey = $po_pkey and po.status = 1");
        $this->PurchaseList->query("UPDATE purchase_order set grn_status = '2' where  po_pkey = $po_pkey  "); 
        $company_info = $this->PurchaseList->query("select * from comp_contact_info ");
        //debug($company_info);
        $joins = array(
            array(
                'table' => 'locations',
                'alias' => 'Location',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Location.location_code = PurchaseOrder.location_code'
                )
            ),
            array(
                'table' => 'contacts',
                'alias' => 'Contacts',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Contacts.contact_id = PurchaseOrder.supplier_code'
                )
            )
            );
        $fileds = array("Contacts.*,Location.*,PurchaseOrder.*");
        $arr_po_details = $this->PurchaseOrder->find("all",array("fields"=>$fileds,"joins"=>$joins,"conditions"=>array("po_pkey"=>$po_pkey)));
        $arr_merged_items = array();
        foreach ($arr_find_meterial as $key=> $val){
            $arr_merged_items[][$val['im']['item_code']] = $val;
        }
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        //debug($arr_po_details);
        
        $this->set("arr_find_meterial", $arr_merged_items);
        $this->set("po_pkey", $po_pkey);
        $this->set("company_info", $company_info);
        $this->set("arr_po_details", $arr_po_details);
        $this->render('download');
    }


    public function downloads($po_pkey = 0){
        $this->autoRender = false;
        $this->PurchaseList->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
       $arr_find_meterial = $this->PurchaseList->query("select mr.mr_code,mr.mr_pkey,pr.expected_date,pr.location,pr.po_pkey,im.item_code,po.required_qty,po.po_rate,po.ordering_qty,im.item_desc,store_master.store_code from po_item_details as po
join purchase_order pr on (pr.po_pkey = po.po_fkey)
 join material_request mr on (mr.mr_pkey = po.mr_fkey)
join item_master im on (im.item_master_pkey = po.item_code)
join store_master on (store_master.store_master_pkey = mr.store_code)
 where po_fkey = $po_pkey and po.status = 1");
        
        $company_info = $this->PurchaseList->query("select * from comp_contact_info ");
        //debug($company_info);
        $joins = array(
            array(
                'table' => 'locations',
                'alias' => 'Location',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Location.location_code = PurchaseOrder.location_code'
                )
            ),
            array(
                'table' => 'contacts',
                'alias' => 'Contacts',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'Contacts.contact_id = PurchaseOrder.supplier_code'
                )
            )
            );
        $fileds = array("Contacts.*,Location.*,PurchaseOrder.*");
        $arr_po_details = $this->PurchaseOrder->find("all",array("fields"=>$fileds,"joins"=>$joins,"conditions"=>array("po_pkey"=>$po_pkey)));
        $arr_merged_items = array();
        foreach ($arr_find_meterial as $key=> $val){
            $arr_merged_items[][$val['im']['item_code']] = $val;
        }
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        //debug($arr_po_details);
        
        $this->set("arr_find_meterial", $arr_merged_items);
        $this->set("po_pkey", $po_pkey);
        $this->set("company_info", $company_info);
        $this->set("arr_po_details", $arr_po_details);
        
            //$content ="<h2>hi</h2>";
            $view = new View($this, false);
            $view_output = $view->render('download');
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
                try
                    {
                        $html2pdf = new HTML2PDF('P', 'Legal', 'en');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                        //$html2pdf->writeHTML($content);
                        $html2pdf->writeHTML($view_output);
                        $html2pdf->Output('Loandetails.pdf', 'D');
                        $this->render('download');
                    }

                    catch(HTML2PDF_exception $e) {
                        echo $e;
                        exit;
                    }
    }
    
//form save
    public function save() {
        $this->autoRender = FALSE;
        $result = array('success' => 0);
		$this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
		$this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
		$this->PurchaseList->useDbConfig = $this->Session->read('ds');
        $arr_form_data['created_by'] = $this->Session->read('user_name');
        $created_by = $this->Session->read('user_name');
        //$arr_form_data['po_fkey'] = $arr_form_data['po_pkey'];
        //$arr_form_data['po_date'] = date("Y-m-d");
        //edited by megha not showing store code
        $exp_date = isset($arr_form_data['expected_date'])?$arr_form_data['expected_date']:'';
        $arr_form_data['expected_date'] = date("Y-m-d",strtotime($exp_date));
        if ($arr_form_data['po_fkey'] == '') {
            $this->PurchaseOrder->save($arr_form_data);
            $getlastid = isset($arr_form_data['po_pkey']) && !empty($arr_form_data['po_pkey']) ? $arr_form_data['po_pkey'] : $this->PurchaseOrder->getInsertid();
            $itemkey = $arr_form_data['po_pkey'] = $getlastid;
        } else {
            $arr_purchaselist_data = array();
            $arr_purchaselist_data['purchase_list_pkey'] = isset($arr_form_data['purchase_list_pkey']) ? $arr_form_data['purchase_list_pkey'] : 0;
            $arr_purchaselist_data['mr_fkey'] = $arr_form_data['mr_pkey'];
            $arr_purchaselist_data['po_fkey'] = $arr_form_data['po_fkey'];
            $itemkey = $arr_form_data['po_fkey'];
            $arr = array();
            $count = count($arr_form_data['required_qty']);
            $arr = $arr_form_data;
           // debug($arr_purchaselist_data);
            $this->PurchaseList->save($arr_purchaselist_data);
            $leaveentryId = $this->PurchaseList->getLastInsertID();
            $mr_fkey = isset($arr_form_data['mr_pkey'])?$arr_form_data['mr_pkey']:'';
            for ($i = 0; $i < $count; $i++) {
                
                $ordering_qty = isset($arr['ordering_qty'][$i]) ? $arr['ordering_qty'][$i] : 0;
                $requiredd_qty = isset($arr['required_qty'][$i]) ? $arr['required_qty'][$i] : 0;
                $rate = isset($arr['rate'][$i]) ? $arr['rate'][$i] : 0;
                $item_code = isset($arr['item_code'][$i]) ? $arr['item_code'][$i] : 0;
                $mr_details_pkey = isset($arr['mr_details_pkey'][$i]) ? $arr['mr_details_pkey'][$i] : 0;
                $this->MaterialRequestDetails->query("update mr_details set ordering_qty  = ordering_qty+$ordering_qty where mr_details_pkey = $mr_details_pkey");
                $this->MaterialRequestDetails->query("update material_request set po_status = '2' where mr_pkey = $mr_fkey");
                $check_exists = $this->MaterialRequestDetails->query("select * from po_item_details where po_fkey ='$itemkey' and item_code = '$item_code' and mr_fkey = '$mr_fkey' and status = '1' ");
                if(!empty($check_exists)){
                    $this->MaterialRequestDetails->query("update po_item_details set ordering_qty = ordering_qty+'$ordering_qty',po_rate = '$rate' where po_fkey ='$itemkey' and item_code = '$item_code' and mr_fkey = '$mr_fkey' and status = '1' ");
                }else{
                    $this->MaterialRequestDetails->query("insert into po_item_details(po_fkey,item_code,ordering_qty,required_qty,created_by,po_rate,mr_fkey,po_list_pkey) values('$itemkey','$item_code','$ordering_qty','$requiredd_qty','$created_by','$rate','$mr_fkey','$leaveentryId')");
                }
            }
            
            //debug($arr_form_data);
            
        }


        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $itemkey));
    }
//Load table 

      public function loadtable($id, $rowindex = 1) {
        $this->autoRender = false;
        $this->PurchaseList->useDbConfig = $this->Session->read('ds');


        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = "and store_master_pkey in (select store_pkey from access_store where emp_fkey =".$cur_emp_key." and status=1 and store_pkey in (select store_master_pkey from store_master where status=1))";
        } else {
            $branch_condition = "";
        }
        //employee branch wise sorting ends here

        $arr_find_meterial = $this->PurchaseList->query("select mr.mr_code,mr.mr_pkey,pr.expected_date,pr.location,pr.po_pkey,po.item_code,po.ordering_qty,im.item_desc,store_master.store_code from po_item_details as po
join purchase_order pr on (pr.po_pkey = po.po_fkey)
 join material_request mr on (mr.mr_pkey = po.mr_fkey)
join item_master im on (im.item_master_pkey = po.item_code)
join store_master on (store_master.store_master_pkey = mr.store_code)
 where po_fkey = $id and po.status = 1 " . $branch_condition);
        $data = '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i> Item Added Successfully To Purchase Order!</h4>
                The following Items has been added to the Purchase Order Successfully.
              </div>';
        $data .= '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red;">';
        $data .=' <thead>';
        $data .=' <tr class="warning">';
        $data .=' <th>Sl No</th>';
        $data .=' <th>Material Request Code</th>';
        $data .=' <th>Required Date</th>';
//        $data .=' <th>Location</th>'; 
        $data .=' <th>Store Code</th> ';
        $data .=' <th>Item Name</th>';
        $data .=' <th>Ordered qty</th>';
//        $data .=' <th>Pending</th>';
//        $data .=' <th>Remark</th>'; 
        $data .=' <th>Action</th>';

        $i = 1;

        foreach ($arr_find_meterial as $value) {
            $item_code = $value['po']['item_code'];
           $mr_code = $value['mr']['mr_pkey'];
            $arr_find_mr = $this->PurchaseList->query("select mr_details_pkey from mr_details  where mr_fkey = '$mr_code' and item_code = '$item_code'");
            $class = ($i % 2) ? 'info' : 'danger';
            //debug($value);
            $pid = $arr_find_mr['0']['mr_details']['mr_details_pkey'];
            //$pid = $value["mr"]["mr_pkey"];
            $data .= '<tr>';
            $data .= '<td> ' . $i . '</td>';
            $data .= '<td> ' . $value["mr"]["mr_code"] . '</td>';
            $data .= '<td> ' . $value["pr"]["expected_date"] . '</td>';
//            $data .= '<td> ' . $value["pr"]["location"] . '</td>';
            $data .= '<td> ' . $value["store_master"]["store_code"] . '</td>';
            $data .= '<td> ' . $value["im"]["item_desc"] . '</td>';
            $data .= '<td> ' . $value["po"]["ordering_qty"] . '</td>';
//            $data .= '<td> ' . $sum . '</td>';

            $data .=' <input name="po_pkey" id="po_pkey" type="hidden"  value="' . $value["pr"]["po_pkey"] . '" >';
            $data .= '<td> <a href="#" class="  glyphicon glyphicon-pencil"onclick="editdaata(' . $rowindex . ',' . $pid . ');">&nbsp</a>';
            $i++;
        }
        $data .= '</tr>';




        $data .= '</thead>';
        $data .= '</table>';





        echo $data;
    }

// edit form   
    public function edit($mr_pkey = 0, $po_pkey = 0) {

        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');

        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));

        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));



        $arr_att = $this->PurchaseOrder->query("SELECT po_details.ordering_qty,po_details.po_rate,purchaseorder.*,purchaselist.*,materialrequest.*,mrdetails.*,sum(mrdetails.ordering_qty)as req ,sum(mrdetails.required_qty)as ord  "
                . " from purchase_order as purchaseorder "
                . "left join purchase_list as purchaselist on (purchaseorder.po_pkey = purchaselist.po_fkey) "
                . "left join material_request as materialrequest on (materialrequest.mr_pkey = purchaselist.mr_fkey) "
                . "left join mr_details as mrdetails on (mrdetails.mr_fkey = materialrequest.mr_pkey) "
                ."join po_item_details as po_details on (po_details.po_fkey = purchaseorder.po_pkey and po_details.item_code = mrdetails.item_code )"
                . "where mrdetails.status='1' and mrdetails.mr_details_pkey = '$mr_pkey' and purchaseorder.po_pkey = '$po_pkey'  ");


        $req = $arr_att['0']['0']['req'];
        $ord = $arr_att['0']['0']['ord'];
        $sum = $ord - $req;
        //debug($sum);
       // debug($arr_att);
        $this->layout = null;
        $this->set("arr_att", $arr_att);
        $this->set("sum", $sum);
        $this->set("pk", $po_pkey);
    }
    
    
//form         
    public function form($mr_pkey = 0, $pk = 0) {

        $title = 'ADD MATERIAL REQUEST';
        
        $this->set('title', $title);
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');

        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
        //debug($all_item);

        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));


//added store location by megha
        $arr_att = $this->MaterialRequest->query("SELECT materialrequest.*,mrdetails.*,store_master.store_location "
                . " from material_request as materialrequest "
                . "left join store_master as store_master on (materialrequest.store_code = store_master.store_master_pkey) "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "where materialrequest.mr_pkey = '$mr_pkey' and mrdetails.status='1'  ");

        $arr_atts = $this->MaterialRequest->query("SELECT purchaseorder.*,purchaselist.*,materialrequest.*,mrdetails.*,sum(mrdetails.ordering_qty)as req ,sum(mrdetails.required_qty)as ord,store_master.store_code,store_master.store_location  "
                . " from purchase_order as purchaseorder "
                . "left join purchase_list as purchaselist on (purchaseorder.po_pkey = purchaselist.po_fkey) "
                . "left join material_request as materialrequest on (materialrequest.mr_pkey = purchaselist.mr_fkey) "
                . "left join store_master as store_master on (materialrequest.store_code = store_master.store_master_pkey) "
                . "left join mr_details as mrdetails on (mrdetails.mr_fkey = materialrequest.mr_pkey) "
                . "where mrdetails.status='1' and materialrequest.mr_pkey = '$mr_pkey' and purchaseorder.po_pkey = '$pk'  ");
        

        $req = isset($arr_atts['0']['0']['req'])?$arr_atts['0']['0']['req']:0;
        $ord = isset($arr_atts['0']['0']['ord'])?$arr_atts['0']['0']['ord']:0;
        $sum = $ord - $req;
        //debug($sum);
        //debug($arr_atts);
        $this->layout = null;
        $this->set("sum", $sum);

       //debug($arr_att);
        $this->layout = null;
        $this->set("arr_att", $arr_att);
        $this->set("pk", $pk);
    }
    
    //delete
    public function purchasedelete() {
        $this->autoRender = FALSE;
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["po_pkey"])) {
            $ar_ids = explode(",", $_REQUEST["po_pkey"]);
            //debug($ar_ids);
            $this->PurchaseOrder->updateAll(
                    array('PurchaseOrder.status' => 0), array('PurchaseOrder.po_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }
//find item name       
    public function getautocompletionspo_number() {
        $this->autoRender = false;
        if (isset($_REQUEST['po_number']) && !empty($_REQUEST['po_number'])) {
            $searchkey = $_REQUEST['po_number'];
            $filter_condition = 'po_number LIKE "%' . $searchkey . '%"';
            $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
            $arr_item = $this->PurchaseOrder->query("SELECT * FROM `purchase_order` WHERE `status`=1 and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_item as $val) {
                $arr_filterresult[] = isset($val['purchase_order']) ? $val['purchase_order'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
//get itemdettails
    public function getitem($id = '') {
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

//location    
      public function getautocompletionslocation() {
        $this->autoRender = false;
        if (isset($_REQUEST['location']) && !empty($_REQUEST['location'])) {
            $searchkey = $_REQUEST['location'];
             $filter_condition = 'location_name LIKE "%' . $searchkey . '%"';
            $this->Location->useDbConfig = $this->Session->read('ds');
            $arr_location = $this->Location->query("SELECT * FROM `locations` WHERE `status`=1 and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_location as $val) {
                $arr_filterresult[] = isset($val['locations']) ? $val['locations'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
//suppliername
     public function getautocompletionssupplier_name() {
        $this->autoRender = false;
        if (isset($_REQUEST['supplier_name']) && !empty($_REQUEST['supplier_name'])) {
            $searchkey = $_REQUEST['supplier_name'];
            $filter_condition = ' (first_name LIKE "%' . $searchkey . '%" or company_name LIKE "%' . $searchkey . '%" )';
            $this->Contacts->useDbConfig = $this->Session->read('ds');
            $arr_contact = $this->Contacts->query("SELECT *,CONCAT(company_name,' - ',first_name,' ',last_name) as name FROM `contacts` WHERE `status`=1 AND relationship='Vendor' and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_contact as $val) {
                $arr_filterresult[] = isset($val['contacts']) ? array_merge($val['contacts'],$val['0']) : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
//suppliercode
    public function getautocompletionssupplier_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['supplier_code']) && !empty($_REQUEST['supplier_code'])) {
            $searchkey = $_REQUEST['supplier_code'];
             $filter_condition = 'contact_id LIKE "%' . $searchkey . '%"';
            $this->Contacts->useDbConfig = $this->Session->read('ds');
            $arr_user = $this->Contacts->query("SELECT * FROM `contacts` WHERE `status`=1 AND relationship='Vendor' and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_user as $val) {
                $arr_filterresult[] = isset($val['contacts']) ? $val['contacts'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
// form store    
    public function getautocompletionsstore_code() {
        $this->autoRender = false;
        if (isset($_REQUEST['store_code']) && !empty($_REQUEST['store_code'])) {
            $searchkey = $_REQUEST['store_code'];
            $filter_condition = 'store_code LIKE "%' . $searchkey . '%"';
            //debug($filter_condition);
            $this->Store->useDbConfig = $this->Session->read('ds');
            $arr_store = $this->Store->query("SELECT * FROM `store_master` WHERE `status`=1 and $filter_condition");
            $arr_filterresult = array();
            foreach ($arr_store as $val) {
                $arr_filterresult[] = isset($val['store_master']) ? $val['store_master'] : array();
            }
        }
        echo json_encode($arr_filterresult);
    }
        public function meteriallist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $count = $this->MaterialRequest->find("count", array("conditions" => array('status' => 1)));
// search list add all unit     
        $arr_att = $this->MaterialRequest->query("SELECT material_request.supplier_name,sum(mr_details.unit)as sumunit,im.item_desc 
               from material_request as material_request
               left join mr_details as mr_details on (material_request.mr_pkey = mr_details.mr_fkey)
               left join item_master as im on(mr_details.item_code = im.item_master_pkey)
               where  material_request.status='1' group by item_desc; ");
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['item_desc'] = isset($value['im']['item_desc']) ? $value['im']['item_desc'] : '';
            $out['sumunit'] = isset($value[0]['sumunit']) ? $value[0]['sumunit'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    public function addnewrow($rowIndex = 1) {
        $this->Item->useDbConfig = $this->Session->read('ds');
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditioin" => array('status' => 1))));

        $this->set('rowIndex', $rowIndex);
    }




    public function save1() {
        $this->autoRender = FALSE;
        $this->layout = null;
		$this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        //
        //debug($arr_form_data);
//create by 
        $arr_form_data['created_by'] = $this->Session->read('user_id');
        $this->PurchaseOrder->save($arr_form_data);
		$this->PurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
		$this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
		$this->PoMaterial->useDbConfig = $this->Session->read('ds');
//mutiple save     

        $arr = array();
        $count = count($arr_form_data['item_code']);
        $arr = $arr_form_data;
        for ($i = 0; $i < $count; $i++) {
            if (isset($arr_form_data['po_item_pkey']) && !empty($arr_form_data['po_item_pkey'])) {
                $arr_form_data['po_item_pkey'] = $arr['po_item_pkey'][$i];
            }
            $arr_form_data['item_code'] = $arr['item_code'][$i];
            $arr_form_data['item_description'] = $arr['item_description'][$i];
            $arr_form_data['uom'] = $arr['uom'][$i];
            $arr_form_data['requested_qty'] = $arr['requested_qty'][$i];
            $arr_form_data['ordering_qty'] = $arr['ordering_qty'][$i];
            $arr_form_data['po_rate'] = $arr['po_rate'][$i];
            $arr_form_data['po_value'] = $arr['po_value'][$i];

//last insert id purchaseorder
            if ($arr_form_data['po_pkey'] == '') {
                $arr_form_data['po_fkey'] = $this->PurchaseOrder->getInsertID();
            } else {
                $arr_form_data['po_fkey'] = $arr['po_pkey'];
            }
            // debug($arr_form_data);
            $this->PurchaseOrderDetails->saveAll($arr_form_data);
//summ of unit for materialrequst           
            $updateunit = count($arr_form_data['unit1']);
            for ($i = 0; $i < $updateunit; $i++) {
                $arr_meteral_data['sum'] = $arr['unit1'][$i];
                $arr_meteral_data['res'] = $arr['pending'][$i];
                $arr_finaldata_data['unit'] = $arr_meteral_data['res'] - $arr_meteral_data['sum'];
                $arr_finaldata_data['mr_details_pkey'] = $arr['mr_details_pkey'][$i];
                $arr_material = $this->MaterialRequestDetails->save($arr_finaldata_data);
            }




            if ($arr_form_data['po_item_pkey'] == '') {
                $arr_form_data['po_item_fkey'] = $this->PurchaseOrderDetails->getInsertID();
            } else {
                $arr_form_data['po_item_fkey'] = $arr['po_item_pkey'];
            }

            $arr = array();
            $findsome = count($arr_form_data['mr_details_pkey']);
            $arr = $arr_form_data;
            for ($i = 0; $i < $findsome; $i++) {
                if (isset($arr_form_data['mr_details_pkey']) && !empty($arr_form_data['mr_details_pkey'])) {
                    $arr_form_data['mr_details_pkey'] = $arr['mr_details_pkey'][$i];
                }

                //$arr_pome_data['po_item_fkey'] = $arr['po_item_pkey'][$i];
                $arr_pome_data['po_item_details'] = $arr['mr_details_pkey'][$i];
                $this->PoMaterial->saveAll($arr_pome_data);
            }
            //debug($arr_form_data);
            //debug($arr_pome_data);
            //die();
        }
        // debug($result);
    }

    public function getitem1($item_code = 0, $rowIndex = 1) {
        $this->autoRender = FALSE;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrder->useDbConfig = $this->Session->read('ds');
        $this->PurchaseOrderDetails->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbconfig = $this->Session->read('ds');


        $arr_meterial = $this->MaterialRequest->query("SELECT material_request.*,mr_details.*,store_master.* "
                . " from material_request as material_request "
                . "join mr_details as mr_details on (material_request.mr_pkey = mr_details.mr_fkey )"
                . "left join store_master as store_master on (material_request.store_code = store_master.store_master_pkey)"
                . "where mr_details.item_code = '$item_code' and mr_details.status = 1 ");

        // debug($arr_meterial);
        $out = array();
        foreach ($arr_meterial as $key => $value) {
            $out['mr_details_pkey'] = isset($value['mr_details']['mr_details_pkey']) ? $value['mr_details']['mr_details_pkey'] : '';
            $out['client_name'] = isset($value['material_request']['client_name']) ? $value['material_request']['client_name'] : '';
            $out['store_code'] = isset($value['store_master']['store_code']) ? $value['store_master']['store_code'] : '';
            $out['mr_date'] = isset($value['material_request']['mr_date']) ? $value['material_request']['mr_date'] : '';
            $out['unit'] = isset($value['mr_details']['unit']) ? $value['mr_details']['unit'] : '';
            $out['location'] = isset($value['material_request']['location']) ? $value['material_request']['location'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $count = count($resp_att["rows"]);
        //$resp_att["rows"]['totel']=$count;
        //  echo json_encode($resp_att);
        // debug($resp_att);

        $data = '<table  class="table table-bordered">';
        $data.='<thead>';
        $data.='<tr>';
        $data.='<th>' . 'Client Name' . '</th>';
        $data.='<th>' . 'Store Code' . '</th>';
        $data.='<th>' . 'MR Date' . '</th>';
        $data.='<th>' . 'Pending' . '</th>';
        $data.='<th>' . 'Ordering Qty<lable style="color:red;">*</lable>' . '</th>';
        $data.='<th>' . 'Location' . '</th>';
        $data.='<th>' . 'Action' . '</th>';
        $data.='</tr>';
        $data.='</thead>';

        $data . '<tbody>';


        $resp_att["total"] = $count;
        for ($i = 0; $i < $count; $i++) {

            $data.='<tr><td>' . $resp_att['rows'][$i]['client_name'] . '</td>';
            $data.='<td>' . $resp_att['rows'][$i]['store_code'] . '</td>';
            $data.='<td>' . $resp_att['rows'][$i]['mr_date'] . '</td>';
            //$data.='<td>' . $resp_att['rows'][$i]['unit'] . '</td>';
            $data.='<td><input name="pending[]" type="text" id="dedu" value="' . $resp_att['rows'][$i]['unit'] . '" readonly="readonly" ></td>';
            //$data.='<td><input type="text" name="unit" id="sumall"  class="sumall" keyup="sumall();"></td>';
            $data.='<td><input id="pending" type="text" name="unit' . $rowIndex . '[]" onchange="calculateSum(' . $rowIndex . ');"></td>';
            $data.='<td>' . $resp_att['rows'][$i]['location'] . '</td></tr>';
            $data.='<td><input type="hidden" value="' . $resp_att['rows'][$i]['mr_details_pkey'] . '" name="mr_details_pkey[]"></td>';
        }
        $data . '</tbody>';
        $data.='</table>';






        echo $data;

        $this->layout = null;
    }

    public function itemtotel($item_code = 0) {
        $this->autoRender = FALSE;
        $this->MaterialRequest->useDbConfig = $this->Session->read('ds');
        $this->MaterialRequestDetails->useDbConfig = $this->Session->read('ds');

//         $arr_att = $this->MaterialRequest->query("SELECT material_request.client_name,sum(mr_details.unit)as sumunit,im.item_desc 
//               from material_request as material_request
//               left join mr_details as mr_details on (material_request.mr_pkey = mr_details.mr_fkey)
//               left join item_master as im on(mr_details.item_code = im.item_master_pkey)
//               where  material_request.status='1' and mr_details.item_code = '$item_code' ");
//        // debug($arr_att);
//         foreach ($arr_att as $key => $value) {
////            $out['item_desc'] = isset($value['im']['item_desc']) ? $value['im']['item_desc'] : '';
//            $out['sumunit'] = isset($value[0]['sumunit']) ? $value[0]['sumunit'] : '';
//            $resp_att["rows"][$key] = $out;
//        }
//     //   $resp_att["total"] = $count;
//        echo json_encode($resp_att);
        $arr_att = $this->MaterialRequest->query("SELECT material_request.*,mr_details.* "
                . " from material_request as material_request "
                . "left join mr_details as mr_details on (material_request.mr_pkey = mr_details.mr_fkey)"
                . "where mr_details.item_code = '$item_code' and mr_details.status   = 1 ");

        $add = 0;
        foreach ($arr_att as $value) {
            $amt = $value['mr_details']['unit'];
            $add = $add + $amt;
        }
        echo $add;
    }

}
