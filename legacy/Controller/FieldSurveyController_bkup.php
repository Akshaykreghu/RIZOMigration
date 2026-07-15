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
class FieldSurveyController extends AppController {

    /**
     * Controller name
     * @var string
     */
    //public $layout="default";
    public $name = 'FieldSurvey';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('StockTranferItem','StockTranfer','Item','SiteWork','Store', 'Site', 'Equipments', 'StockTranfer','StockTranferItem','EmployeeDetails', 'StockTranfer','StockTranferItem','PackageMaster','Item','MaterialRequest','MaterialRequestDetails');
    
    // Master Form
    public function home(){
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->SiteWork->useDbConfig = $this->Session->read('ds');
        $this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
        
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $arr_types = $this->StockTranfer->query("SELECT * FROM survey_type WHERE status = '1' ");
        $this->set('arr_types', $arr_types);
        
        //all item      
         $this->set("all_item", $all_item = $this->SiteWork->find("all", array("conditions" => array('status'=>1))));
         //debug($all_item);
        //all store
        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
        //debug($all_store);
    }
    public function index() {
        $this->layout = NULL;
        $arr_request_data = $this->request->data;
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $this->Store->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
        
        //all item      
         $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status'=>1))));
         //debug($all_item);
        //all store
        $this->set("all_store", $all_store = $this->Store->find("all", array("conditions" => array('status' => 1))));
        //debug($all_store);
    }
    
    public function form_ticket($efsr_tickets_pkey = 0){
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $this->SiteWork->useDbConfig = $this->Session->read('ds');
        //all item      
        $this->set("all_item", $all_item = $this->SiteWork->find("all", array("conditions" => array('status' => 1))));
        $arr_types = $this->StockTranfer->query("SELECT efsr_tickets_pkey,survey_type.type_name,efsr_tickets.survey_type_fkey,efsr_tickets.site_fkey,efsr_equipments_master_fkey,ticket_no,survey_type_fkey,type_name,emp_fkey,emp_details.first_name,emp_approved.first_name approved_by,concat(equipments_name,'|',manufacturer) equipments_name,efsr_id,site_name,sap_site_id,site_id
 from efsr_tickets join efsr_site on (efsr_site.efsr_site_pkey = site_fkey)
 join survey_type on (type_pkey=survey_type_fkey)
 join emp_details on (emp_details.emp_pkey=emp_fkey)
  join emp_details as emp_approved on (emp_approved.emp_pkey=approved_by)
join efsr_equipments_master on (efsr_equipments_master_fkey=efsr_equipments_master_pkey)
  where efsr_tickets.status = '1' and efsr_tickets_pkey = '$efsr_tickets_pkey' ");
        $this->set('arr_types', $arr_types);
    }

    
    public function form($id = 0){
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $arr_types = $this->StockTranfer->query("SELECT * FROM equipment_type WHERE status = '1' ");
        $this->set('arr_types', $arr_types);
        
        $arr_site_list = $this->StockTranfer->query("SELECT efsr_site_pkey,site_name FROM efsr_site WHERE status = '1' ");
        $this->set('arr_site_list', $arr_site_list);
        $arr_equipment_data = array();
        $id = isset($_GET['id'])?$_GET['id']:null;
        if(isset($id)){
            $arr_equipment_data = $this->StockTranfer->query("SELECT * FROM efsr_equipments_master WHERE status = '1' and efsr_equipments_master_pkey = '$id' ");
        }
        $this->set('arr_equipment_data', $arr_equipment_data);
    }


    public function lists() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'efsr_equipments_master_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';

        if ($branch_code != '') {
            $branch_condition = "and site_fkey = '$branch_code' ";
        }
        $id = 1;
        $counts = $this->StockTranfer->query("SELECT count(*) as count FROM `efsr_equipments_master` where status = '1' $branch_condition ");

        $count = $counts[0][0]['count'];
        
        $arr_att = $this->StockTranfer->query("SELECT efsr_equipments_master.*,survey_type.type_name from efsr_equipments_master left join survey_type on (survey_type.type_pkey = efsr_equipments_master.equipments_type) where efsr_equipments_master.status = '1' $branch_condition ORDER BY $sort $order limit $limit offset $ofst ");
        
//         debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            
            $out['efsr_equipments_master_pkey'] = isset($value['efsr_equipments_master']['efsr_equipments_master_pkey']) ? $value['efsr_equipments_master']['efsr_equipments_master_pkey'] : '';
            $out['equipments_name'] = isset($value['efsr_equipments_master']['equipments_name']) ? $value['efsr_equipments_master']['equipments_name'] : '';
            $out['equipments_type'] = isset($value['survey_type']['type_name']) ? $value['survey_type']['type_name'] : '';
            $out['manufacturer'] = isset($value['efsr_equipments_master']['manufacturer']) ? $value['efsr_equipments_master']['manufacturer'] : '';
            $out['model_no'] = isset($value['efsr_equipments_master']['model_no']) ? $value['efsr_equipments_master']['model_no'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
    public function lists_site() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $equpiment= isset($arr_request_data['equipments']) ? $arr_request_data['equipments'] : '';
        $approvs_fkey = isset($arr_request_data['Approved_fkey']) ? $arr_request_data['Approved_fkey'] : '';
        $emp_fkey = isset($arr_request_data['emp_fkey']) ? $arr_request_data['emp_fkey'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'efsr_tickets_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';

        if ($branch_code != '') {
            $branch_condition = "and efsr_site.efsr_site_pkey  = '$branch_code' ";
        }
        if ($approvs_fkey != '') {
            $branch_condition .= "and emp_approved.emp_pkey = '$approvs_fkey' ";
        }
        if ($equpiment != '') {
            $branch_condition .= "and efsr_equipments_master_fkey = '$equpiment' ";
        }
        if ($emp_fkey != '') {
            $branch_condition .= "and emp_details.emp_pkey = '$emp_fkey' ";
        }
        $id = 1;
        $counts = $this->StockTranfer->query("SELECT count(*) as count from efsr_tickets join efsr_site on (efsr_site.efsr_site_pkey = site_fkey)
 join survey_type on (type_pkey=survey_type_fkey)
 join emp_details on (emp_details.emp_pkey=emp_fkey)
  join emp_details as emp_approved on (emp_approved.emp_pkey=approved_by)
join efsr_equipments_master on (efsr_equipments_master_fkey=efsr_equipments_master_pkey)
  where efsr_tickets.status = '1' $branch_condition ");

        $count = $counts[0][0]['count'];
        
        $arr_att = $this->StockTranfer->query("SELECT efsr_tickets_pkey,ticket_no,survey_type_fkey,type_name,emp_fkey,emp_details.first_name,emp_approved.first_name approved_by,concat(equipments_name,'|',manufacturer) equipments_name,efsr_id,site_name,sap_site_id,site_id
 from efsr_tickets join efsr_site on (efsr_site.efsr_site_pkey = site_fkey)
 join survey_type on (type_pkey=survey_type_fkey)
 join emp_details on (emp_details.emp_pkey=emp_fkey)
  join emp_details as emp_approved on (emp_approved.emp_pkey=approved_by)
join efsr_equipments_master on (efsr_equipments_master_fkey=efsr_equipments_master_pkey)
  where efsr_tickets.status = '1' $branch_condition ORDER BY $sort $order limit $limit offset $ofst ");
        
//         debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            
            $out['efsr_tickets_pkey'] = isset($value['efsr_tickets']['efsr_tickets_pkey']) ? $value['efsr_tickets']['efsr_tickets_pkey'] : '';
            $out['site_name'] = isset($value['efsr_site']['site_name']) ? $value['efsr_site']['site_name'] : '';
            $out['ticket_no'] = isset($value['efsr_tickets']['ticket_no']) ? $value['efsr_tickets']['ticket_no'] : '';
            $out['survey_type'] = isset($value['survey_type']['type_name']) ? $value['survey_type']['type_name'] : '';
            $out['first_name'] = isset($value['emp_details']['first_name']) ? $value['emp_details']['first_name'] : '';
            $out['approved_by'] = isset($value['emp_approved']['approved_by']) ? $value['emp_approved']['approved_by'] : '';
            $out['equipments_name'] = isset($value['0']['equipments_name']) ? $value['0']['equipments_name'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
   
//form save
    public function save() 
   {
        $this->autoRender = FALSE;
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
		$this->Equipments->useDbConfig = $this->Session->read('ds');
		$this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $arr_form_data['created_by'] = $this->Session->read('user_id');
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        $site_fkey = $arr_form_data['filterby_branch'];
        $filterby_Equipment = $arr_form_data['filterby_Equipment'];
        $survey_type = $arr_form_data['survey_type_fkey'];
        $emp_fkey = $arr_form_data['emp_fkey'];
        $approved_pkey = $arr_form_data['app_fkey'];
        
        // $survey_fkey = $this->StockTranferItem->query("SELECT equipments_type FROM efsr_equipments_master WHERE efsr_equipments_master_pkey  = '$filterby_Equipment' ");
        // $survey_type = isset($survey_fkey['0']['efsr_equipments_master']['equipments_type'])?$survey_fkey['0']['efsr_equipments_master']['equipments_type']:0;

        $arr_filterby_Equipment = array();

        if($filterby_Equipment != 0){

	        $arr_filterby_Equipment[] = $filterby_Equipment;
        }else{
        	$arr_filterby_Equipment =  Set::extract('/efsr_equipments_master/efsr_equipments_master_pkey/.', $this->StockTranferItem->query("SELECT efsr_equipments_master_pkey FROM efsr_equipments_master WHERE site_fkey = '$site_fkey' and equipments_type = '$survey_type' ") );
        }
        // print_r($arr_filterby_Equipment); die();
        foreach ($arr_filterby_Equipment as $key => $value) {
        	$this->StockTranferItem->query("INSERT INTO efsr_tickets(site_fkey,efsr_equipments_master_fkey,survey_type_fkey,emp_fkey,approved_by) VALUES('$site_fkey','$filterby_Equipment','$survey_type','$emp_fkey','$approved_pkey') ");
        }
        
        
        echo json_encode(array('msg' => 'Added new item sucessfully'));
    }
    
    public function saveEquip() 
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
		$this->Equipments->useDbConfig = $this->Session->read('ds');
		$this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $arr_form_data['created_by'] = $this->Session->read('user_id');
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        
        
        $survey_fkey = $this->Equipments->save($arr_form_data);
        
        
        echo json_encode(array('msg' => 'Added new item sucessfully'));
    }

    // Master Form Ends here
    
    
    // ----------------------------------------------------------- // 
    
    
    //Ticket Form Starts Here
    
    public function ticket(){
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $this->SiteWork->useDbConfig = $this->Session->read('ds');
        //all item      
        $this->set("all_item", $all_item = $this->SiteWork->find("all", array("conditions" => array('status' => 1))));
    }
    
    public function jsons($branch = '',$yearmonth = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//            debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch == '0') {
            
             $branch_condition = "where date_format(LOGDATE,'%Y-%m') = '$yearmonth' and C2 = 'SIT' "; // "where c6 in (select site.site_pkey from site where site_pkey in (
//        select c6 from device_attandance where date_format(LOGDATE,'%Y-%m')= '$yearmonth' and C2 = 'SIT')) ";
        } else {
           $branch_condition = "where date_format(LOGDATE,'%Y-%m') = '$yearmonth' and C2 = 'SIT' and c6 = $branch";
        }
        $q_condition = "";
        $emp_condition = "";
//        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1  $q_condition $emp_condition ORDER BY emp_pkey DESC ");
        $branch_array = $this->EmployeeDetails->query("Select emp_pkey,first_name,last_name,emp_id From emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) 
                                                       where  status = 1  $q_condition  ORDER BY emp_pkey");
//        debug("Select emp_pkey,first_name,last_name,emp_id From emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) 
//                                                       where  status = 1 and emp_details.emp_pkey in (select emp_pkey from emp_site_detail_timeattandance $branch_condition)  ORDER BY emp_pkey");
//        debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
      $branch = array();
        $branch[] = array();
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_details']['emp_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
//        $array = array(
//            "items"=>
//            array(
//            array(
//                "id"=>0,
//                'text'=>'sanjun'
//                
//            ),
//            array(
//                "id"=>1,
//                "text"=>'ananthu'
//                
//            ),
//            array(
//                "id"=>2,
//                "text"=>'sruthi'
//                
//            )
//                )
//        );
        //echo json_encode($array) ;
    }
    
    public function jsons_equipments($branch = '',$store = '') {
        $this->autoRender = false;
        $this->Equipments->useDbConfig = $this->Session->read('ds');
//            debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        
        if ($q != null) {
            $q_condition = "and (equipments_name like '%$q%' )  ";
        } else {
            $q_condition = "";
        }
        if(isset($branch) && $branch != ''){
            $branch_cond = " and type_pkey = $branch ";
        }else{
            $branch_cond = " ";
        }
        if(isset($store) && $store != ''){
            $site_cond = " and site_fkey = $store ";
        }else{
            $site_cond = " ";
        }
//        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1  $q_condition $emp_condition ORDER BY emp_pkey DESC ");
        $branch_array = $this->Equipments->query(" SELECT efsr_equipments_master.*,equipment_type.*,CONCAT(equipment_name,' - ', equipments_name,' | ', survey_type.type_code,' - ',survey_type.type_name) as equi_name FROM equipment_type,survey_type,efsr_equipments_master
 WHERE equipment_type_pkey = equipment_type_fkey $site_cond
 $branch_cond ");
//        debug("Select emp_pkey,first_name,last_name,emp_id From emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) 
//                                                       where  status = 1 and emp_details.emp_pkey in (select emp_pkey from emp_site_detail_timeattandance $branch_condition)  ORDER BY emp_pkey");
//        debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
      $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['efsr_equipments_master']['efsr_equipments_master_pkey'],
                'text' => $value['0']['equi_name'] 
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
//        $array = array(
//            "items"=>
//            array(
//            array(
//                "id"=>0,
//                'text'=>'sanjun'
//                
//            ),
//            array(
//                "id"=>1,
//                "text"=>'ananthu'
//                
//            ),
//            array(
//                "id"=>2,
//                "text"=>'sruthi'
//                
//            )
//                )
//        );
        //echo json_encode($array) ;
    }
    
    public function jsons_Surveys($branch = '',$yearmonth = '') {
        $this->autoRender = false;
        $this->Equipments->useDbConfig = $this->Session->read('ds');
//            debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        
        if ($q != null) {
            $q_condition = "and (type_name like '%$q%' )  ";
        } else {
            $q_condition = "";
        }
        if($branch != 'All'){
            $branch_cond = "and equipment_type_fkey = '$branch' ";
        }else{
            $branch_cond = " ";
        }
//        $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = 1  $q_condition $emp_condition ORDER BY emp_pkey DESC ");
        $branch_array = $this->Equipments->query("SELECT * FROM survey_type WHERE status = 1  ORDER BY type_pkey DESC");
//        debug("Select emp_pkey,first_name,last_name,emp_id From emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) 
//                                                       where  status = 1 and emp_details.emp_pkey in (select emp_pkey from emp_site_detail_timeattandance $branch_condition)  ORDER BY emp_pkey");
//        debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
      $branch = array();
        $branch[] = array();
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['survey_type']['type_pkey'],
                'text' => $value['survey_type']['type_name'] 
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
//        $array = array(
//            "items"=>
//            array(
//            array(
//                "id"=>0,
//                'text'=>'sanjun'
//                
//            ),
//            array(
//                "id"=>1,
//                "text"=>'ananthu'
//                
//            ),
//            array(
//                "id"=>2,
//                "text"=>'sruthi'
//                
//            )
//                )
//        );
        //echo json_encode($array) ;
    }

//load table              
    public function loadtable($id, $rowindex = 1) {
        $this->autoRender = false;
        //debug($id);
        
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
		$this->MaterialRequest->useDbConfig = $this->Session->read('ds');
//        $itemname =$this->itemname($itemcode);
//        $itempo = $itemname[0]['item_master']['item_master_pkey'];
        
        $arr_store_master = $this->StockTranfer->query("SELECT stock.*,stockitem.*"
                . " from stock_tranfer as stock"
                . " left join stock_tranfer_item as stockitem on (stock.stock_tranfer_pkey = stockitem.stock_tranfer_fkey)"
                
                . " where stock.stock_tranfer_pkey = '$id' and stockitem.status='1' ");
       
        //debug($arr_store_master);
        $itemcode=$arr_store_master['0']['stockitem']['item_desc'];
        $tostore=$arr_store_master['0']['stock']['to_store'];
        $itemname =$this->itemname($itemcode);
        $storecode = $this->storename($tostore);
        //debug($storecode);
        $itempo =$itemname['0']['item_master']['item_master_pkey'];
        $storeid =$storecode['0']['store_master']['store_master_pkey'];
        
        
        
        //debug($itempo);
        $arr_item_store = $this->MaterialRequest->query("SELECT materialrequest.*,SUM(mrdetails.ordering_qty) as sumorder "
                . " from material_request as materialrequest "
                . "left join mr_details as mrdetails on (materialrequest.mr_pkey = mrdetails.mr_fkey) "
                . "where mrdetails.item_code= '$itempo'  and materialrequest.store_code= '$storeid' ");
        
            //debug($arr_item_store);
        $currnt= $arr_item_store['0']['0']['sumorder'];
       // debug($currnt);
        $data = '  <table id="vendortable" class="table table-striped table-responsive" style="border-color:red;">';
        $data .= '  <thead>';
        $data .= '  <tr class="warning">';
        $data .= '  <th> Sl no </th>';
        $data .= '  <th>Item Name</th>';
//        $data .= '  <th>Package</th> ';
//        $data .= '  <th>year of make</th>';
//        $data .= '  <th>Thickness</th>';
//        $data .= '  <th>Dimension</th>';
//        $data .= '  <th>Density</th>';
        $data .= '  <th>Available Qty</th>';
        $data .= '  <th>Required Qty</th>';
        $data .= '  <th>Current  Stock</th>';
        $data .= '  <th>Action<getitem/th>';

        $i = 1;
        foreach ($arr_store_master as $value) {
            $class = ($i % 2) ? 'info' : 'danger';
            $pid = $value["stockitem"]["stock_item_pkey"];
            $data .= '<tr class="' . $class . '">';
            $data .= '<td> ' . $i . '</td>';
            $data .= '<td> ' . $value["stockitem"]["item_desc"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["package"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["year_of_make"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["thickness"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["dimension"] . '</td>';
//            $data .= '<td> ' . $value["stockitem"][""] . '</td>';
            
            $data .= '<td> ' . $value["stockitem"]["available_qty"] . '</td>';
            $data .= '<td> ' . $value["stockitem"]["required_qty"] . '</td>';
            $data .= '<td> ' . $currnt . '</td>';
//            $data .= '<td> ' . $value["stockitem"]["tranfer_stock"] . '</td>';


            $data .= '<td> <a href="#" class="  glyphicon glyphicon-pencil"onclick="editdaata(' . $rowindex . ',' . $pid . ');">&nbsp</a>'
                    . ' <a href="#" class=" glyphicon glyphicon-remove btn-danger" onclick="removedaata(' . $rowindex . ',' . $pid . ');"></a></td>';

            $i++;
        }
        $data .= '</tr>';
        $data .= '</thead>';
        $data .= '</table>';
        echo $data;
    }

//edit order
    public function editstoreitem($id) {
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $this->Item->useDbConfig = $this->Session->read('ds');
        //all item      
        $this->set("all_item", $all_item = $this->Item->find("all", array("conditions" => array('status' => 1))));
        $arr_store_master = $this->StockTranfer->query("SELECT stock.*,stockitem.*"
                . " from stock_tranfer as stock"
                . " left join stock_tranfer_item as stockitem on (stock.stock_tranfer_pkey = stockitem.stock_tranfer_fkey)"
               
                . " where stockitem.stock_item_pkey = '$id' ");
        //debug($arr_store_master);
        foreach ($arr_store_master as $key => $value) {
            $out['stock_tranfer_fkey'] = isset($value['stockitem']['stock_tranfer_fkey']) ? $value['stockitem']['stock_tranfer_fkey'] : '';
        }
        $this->set('id', $id);
        $this->set('arr_store_master', $arr_store_master);
    }

//edit save
    public function editordersave() {
        $this->autoRender = false;
		$this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $masterpk = $arr_form_data['stock_tranfer_fkey'];
       // debug($arr_form_data);
        //debug($masterpk)
        //$allitem = $this->getitem($arr_form_data['item_code']);
//        foreach ($allitem as $value) {
//            $arr_form_data['package'] = $value['itemdetails']['package'];
//            $arr_form_data['year_of_make'] = $value['warrantydetails']['waranty_begin_date'];
//            $arr_form_data['thickness'] = $value['itemdetails']['thickness'];
//            $arr_form_data['dimension'] = $value['itemdetails']['dimension'];
//            $arr_form_data['density'] = $value['itemdetails']['density'];
//        }
        $this->StockTranferItem->save($arr_form_data);
        echo json_encode(array('msg' => 'Added new item sucessfully', 'pk' => $masterpk));
    }

//delete storeitem
    public function deletestoreitem($id=0){
          $this->autoRender = false;
		 $this->StockTranferItem->useDbConfig = $this->Session->read('ds');
        if($id != 0){
            $this->StockTranferItem->updateAll(array('status'=>0),array('stock_item_pkey'=>$id));
 
          
            echo json_encode(array('msg' => 'Store Tranfer   deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Store Tranfer  Details  deletion failed!'));
        }
    } 
//search main stocktranfer
    public function getautocompletionsadjustment_code(){
       $this->autoRender = false;
           if(isset($_REQUEST['adjustment_code']) && !empty($_REQUEST['adjustment_code'])){
               $searchkey = $_REQUEST['adjustment_code'];
            $filter_condition = 'adjustment_code LIKE "%' . $searchkey . '%"';
   
            //debug($filter_condition);
        $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->StockTranfer->query("SELECT * FROM `stock_tranfer` WHERE `status`=1 and $filter_condition");
        $arr_filterresult = array();
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['stock_tranfer']) ? $val['stock_tranfer'] : array();
        }
           }  
         echo json_encode($arr_filterresult);
    }
    
    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;

        //debug($ctcuploadtype);
        //debug($employee);

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "Equipments.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        
            $worksheet = $objPHPExcel->getActiveSheet();
            $i = 0;
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Site Code");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Equipment Name");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Manufacturer");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Model No");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Equipments Specifications");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Serial Number");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Supplied By");
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);
        
        $objPHPExcel->getActiveSheet()->setTitle('Equipment Master  ');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    
    public function downloadempticketformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;

        //debug($ctcuploadtype);
        //debug($employee);

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_gross.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        
            $worksheet = $objPHPExcel->getActiveSheet();
            $i = 0;
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Site Code");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Survey Code");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Equipment Code");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Techinician User ID");
            $worksheet->setCellValueByColumnAndRow($i++, 1, "Supervisor User ID");
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);
        
        $objPHPExcel->getActiveSheet()->setTitle('Ticket Master  ');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    
    
    public function uploadandsaveempctc($equipment_type = 0,$site_fkey = 0) {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);

        $ctcuploadtype = 1;
        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Equipment Name', 'Equipment Type');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                            }
                            $index++;
                        }
                    }
                    if ($mandatory_fields_warning) {
                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->Equipments->useDbConfig = $this->Session->read('ds');
//                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
//                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->StockTranfer->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');

                        foreach ($arrayempdata as $key => $row) {
                            $arr_empctc_data = array();
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
//                            if ($ctcuploadtype == 1) {
                                $arr_empctc_data['equipments_name'] = isset($row['Equipment Name']) ? $row['Equipment Name'] : '';
                                $site_code = isset($row['Site Code']) ? $row['Site Code'] : '';
                                $arr_Emp = $this->StockTranfer->query("SELECT * FROM `efsr_site` WHERE `site_id`='$site_code' ");
                                
                                $arr_empctc_data['equipments_type'] = $equipment_type;
                                $arr_empctc_data['manufacturer'] = isset($row['Manufacturer']) ? $row['Manufacturer'] : '';
                                $arr_empctc_data['model_no'] = isset($row['Model No']) ? $row['Model No'] : '';
                                $arr_empctc_data['equipments_spec'] = isset($row['Equipments Specifications']) ? $row['Equipments Specifications'] : '';
                                $arr_empctc_data['equipments_sn'] = isset($row['Serial Number']) ? $row['Serial Number'] : '';
                                $arr_empctc_data['supplied_by'] = isset($row['Supplied By']) ? $row['Supplied By'] : '';
                                $arr_empctc_data['site_fkey'] = isset($arr_Emp['0']['efsr_site']['efsr_site_pkey'])?$arr_Emp['0']['efsr_site']['efsr_site_pkey']:0 ;
//                                $Branch = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            $emp_id = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            if($Esi == ''){
//                                continue;
//                            }

                            $date = '';
                            
                            
//                            $emp_fkey = isset($arr_usercredentials['EmployeeProfessionalDetails']['emp_fkey']) ? $arr_usercredentials['EmployeeProfessionalDetails']['emp_fkey'] : '';
//                             debug($arr_empctc_data);
//                            $arr_empctc_data = array();
                            $arr_empctc_data['status'] = 1;
//                            $arr_empctc_data['emp_pkey'] = $emp_fkey;
                            $arr_empctc_data['modified_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['modified_date'] = date('Y-m-d');

                            
//                                debug($arr_empctc_data);
                                // die();
                                $result1 = $this->Equipments->saveAll($arr_empctc_data);

                        }
                    }
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Equipments imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Equipments successfully'));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments import failed, no data found!'));
                        exit;
                    
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments failed! '));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments failed! '));
                exit;
            }
            exit;
        }
    }
    
    
    public function uploadandsaveempequipment($equipment_type = 0,$site_fkey = 0) {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);

        $ctcuploadtype = 1;
        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Site Code', 'Survey Code');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                            }
                            $index++;
                        }
                    }
                    if ($mandatory_fields_warning) {
                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
//                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
//                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->Equipments->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');

                        foreach ($arrayempdata as $key => $row) {
                            $arr_empctc_data = array();
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
//                            if ($ctcuploadtype == 1) {
                                $arr_empctc_data['equipments_name'] = isset($row['Equipment Name']) ? $row['Equipment Name'] : '';
                                $site_code = isset($row['Site Code']) ? $row['Site Code'] : '';
                                $arr_Emp = $this->StockTranfer->query("SELECT * FROM `efsr_site` WHERE `site_id`='$site_code' ");
                                
                                $survey_code = isset($row['Survey Code']) ? $row['Survey Code'] : '';
                                $arr_survey = $this->StockTranfer->query("SELECT * FROM `survey_type` WHERE `type_code`='$survey_code' ");
                                
                                $technician_id = isset($row['Site Code']) ? $row['Site Code'] : '';
                                $arr_Emp = $this->StockTranfer->query("SELECT * FROM `user_credentials` WHERE `site_id`='$site_code' ");
                                
                                $site_code = isset($row['Site Code']) ? $row['Site Code'] : '';
                                $arr_Emp = $this->StockTranfer->query("SELECT * FROM `user_credentials` WHERE `site_id`='$site_code' ");
                                
                                $arr_empctc_data['equipments_type'] = $equipment_type;
                                $arr_empctc_data['remarks'] = isset($row['Manufacturer']) ? $row['Manufacturer'] : '';
                                $arr_empctc_data['efsr_equipments_master_fkey'] = isset($row['Model No']) ? $row['Model No'] : '';
                                $arr_empctc_data['approved_by'] = isset($row['Equipments Specifications']) ? $row['Equipments Specifications'] : '';
                                $arr_empctc_data['survey_type_fkey'] = isset($arr_survey['0']['survey_type']['type_pkey'])?$arr_survey['0']['survey_type']['type_pkey']:0 ;
                                $arr_empctc_data['emp_fkey'] = isset($row['Supplied By']) ? $row['Supplied By'] : '';
                                $arr_empctc_data['site_fkey'] = isset($arr_Emp['0']['efsr_site']['efsr_site_pkey'])?$arr_Emp['0']['efsr_site']['efsr_site_pkey']:0 ;
//                                $Branch = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            $emp_id = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            if($Esi == ''){
//                                continue;
//                            }

                            $date = '';
                            
                            
//                            $emp_fkey = isset($arr_usercredentials['EmployeeProfessionalDetails']['emp_fkey']) ? $arr_usercredentials['EmployeeProfessionalDetails']['emp_fkey'] : '';
//                             debug($arr_empctc_data);
//                            $arr_empctc_data = array();
                            $arr_empctc_data['status'] = 1;
//                            $arr_empctc_data['emp_pkey'] = $emp_fkey;
                            $arr_empctc_data['modified_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['modified_date'] = date('Y-m-d');

                            
//                                debug($arr_empctc_data);
                                // die();
                                $result1 = $this->Equipments->saveAll($arr_empctc_data);

                        }
                    }
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Equipments imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Equipments successfully'));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments import failed, no data found!'));
                        exit;
                    
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments failed! '));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Equipments failed! '));
                exit;
            }
            exit;
        }
    }

//main table delete
   public function deletestoremaster($id=0){
          $this->autoRender = false;
		  $this->StockTranfer->useDbConfig = $this->Session->read('ds');
        if($id != 0){
            $this->StockTranfer->updateAll(array('status'=>0),array('stock_tranfer_pkey'=>$id));
            echo json_encode(array('msg' => 'Store Tranfer  deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Store Tranfer deletion failed!'));
        }
    }
        
    
    
    
  

 
    

   
}