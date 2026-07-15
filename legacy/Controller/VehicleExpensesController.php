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

class VehicleExpensesController extends AppController {

    //VehicleExpenses Created by ***ARUL P DAS on 6/3/2020
    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'VehicleExpenses';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'UserCredentials','TransportationPayment','EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmployeeLoan', 'VehicleExpenses', 'SalarySlip', 'Site');
    public $components = array('MasterdataManagement');

    public function form() {
        //Vehicle expense form by ***ARUL P DAS on 6/3/2020
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Site->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->VehicleExpenses->useDbConfig = $this->Session->read('ds');

        $this->set("arr_vehicles", $arr_vehicles = $this->Site->query("select vehicle_master_pkey,make,model_dec,reg_number from vehicle_master where status=1"));

        $fields = array("site_pkey", "site_id", "site_name");
        $conditions = array("status" => 1);
        $this->set("arr_site", $arr_site = $this->Site->find("all", array('fields' => $fields, 'conditions' => $conditions)));

        $data["transportation_expense_pkey"] = "";
        $data["vehicle_master_fkey"] = "";
        $data["site_fkey"] = "";
        $data["driver_name"] = "";
        $data["purpose"] = "";
        $data["starting_date"] = "";
        $data["starting_km"] = "";
        $data["ending_date"] = "";
        $data["ending_km"] = "";
        $data["total_km"] = "";
        $data["total_cost"] = "";
        $data["remarks"] = "";
        $data["rate_per_km"] = "";
        
//        $data["creation_date"] = "";
//        $data["created_by"] = "";
//        $data["modification_date"] = "";
//        $data["modified_by"] = "";
        if (isset($_REQUEST['transportation_expense_pkey']) && $_REQUEST['transportation_expense_pkey'] != 0) {
            $data_db = $this->VehicleExpenses->find("first", array("conditions" => array("transportation_expense_pkey" => $_REQUEST['transportation_expense_pkey'])));
            $data = $data_db['VehicleExpenses'];
            
            $vid=$data['vehicle_master_fkey'];
            $result=  $this->VehicleExpenses->query("select rate_per_km from vehicle_master where vehicle_master_pkey=$vid");
            $data["rate_per_km"]=$result[0]['vehicle_master']['rate_per_km'];
        }
//        debug($data);
        $this->layout = null;
        $this->set("data", $data);
    }
    
    public function get_rate_per_km(){
        //This function is to get the rate_per_km of selected vehicle in form. By ***ARUL P DAS on 5/6/2020
        $this->autoRender = FALSE;
        $this->VehicleExpenses->useDbConfig = $this->Session->read('ds');
        $resp = array();
        if (isset($_REQUEST["veh_id"]) && $_REQUEST["veh_id"] != 0) {
            $vid=$_REQUEST["veh_id"];
            $result=  $this->VehicleExpenses->query("select rate_per_km from vehicle_master where vehicle_master_pkey=$vid");
            $resp["rate_per_km"]=$result[0]['vehicle_master']['rate_per_km'];
        }
        $resp["success"] = true;
        echo json_encode($resp);
    }

//save 
    public function employeeloansave() {
        //Vehicle expense save function by ***ARUL P DAS on 6/3/2020
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->VehicleExpenses->useDbConfig = $this->Session->read('ds');
        $this->TransportationPayment->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $pkey = $arr_form_data["transportation_expense_pkey"];
        if ($arr_form_data["transportation_expense_pkey"] != "") {
            $arr_form_datas["modification_date"] = "'".date('Y-m-d h:i:s')."'"; 
            $arr_form_datas["driver_name"] = "'".$this->Session->read('login_user_id')."'"; 
            $arr_form_datas["vehicle_master_fkey"] = $arr_form_data["vehicle_master_fkey"]; 
            $arr_form_datas["site_fkey"] = $arr_form_data["site_fkey"]; 
            $arr_form_datas["purpose"] = "'".$arr_form_data["purpose"]."'";
            $arr_form_datas["starting_date"] = "'".$arr_form_data["starting_date"]."'"; 
            $arr_form_datas["starting_km"] = $arr_form_data["starting_km"]; 
            $arr_form_datas["ending_date"] = "'".$arr_form_data["ending_date"]."'"; 
            $arr_form_datas["ending_km"] = $arr_form_data["ending_km"]; 
            $arr_form_datas["total_km"] = $arr_form_data["total_km"]; 
            $arr_form_datas["total_cost"] = $arr_form_data["total_cost"]; 
            $arr_form_datas['payments'] = isset($arr_form_data['payamount'])?$arr_form_data['payamount'] + $arr_form_data['payments']:$arr_form_data["payments"]; 
            $arr_form_datas['balance'] = $arr_form_datas['total_cost'] - $arr_form_datas['payments'];       
                   $this->VehicleExpenses->updateAll($arr_form_datas, array('transportation_expense_pkey' => $pkey));
                   $arr['transportation_expense_fkey'] = $pkey;
                   if($arr_form_data['payamount']>0){
                   $arr['payamount'] = $arr_form_data['payamount'];
                   $arr['expensedate'] = $arr_form_data['expensedate'];
                   $arr['bal'] = $arr_form_data['balance'];
                   $arr['created_by'] = $this->Session->read('user_name');
                   $result = $this->TransportationPayment->save($arr);
                   }
                   $resp = array();
                   $resp["success"] = true;
                   $resp["msg"] = "Updated Successfully!";
                   echo json_encode($resp);
        } else {
            $arr_form_data['created_by'] = $this->Session->read('login_user_id');
            $arr_form_data["creation_date"] = date('Y-m-d h:i:s');
                    $result = $this->VehicleExpenses->save($arr_form_data);
                    $insert_id = $this->VehicleExpenses->getInsertid();
                    if($arr_form_data['payments']>0){
                    $arr['transportation_expense_fkey'] = $insert_id;
                    $arr['payamount'] = $arr_form_data['payments'];
                    $arr['expensedate'] = $arr_form_data['invoice_date'];
                    $arr['bal'] = $arr_form_data['balance'];
                    $arr['created_by'] = $this->Session->read('user_name');
                    $result = $this->TransportationPayment->save($arr);
                    }
                    $resp = array();
                    $resp["success"] = true;
                    $resp["msg"] = "Saved successfully";
                    echo json_encode($resp);
        }
//        $msg = "Saved Successfully!!!";
//        $result = $this->VehicleExpenses->save($arr_form_data);
//        $resp = array();
//        $resp["success"] = true;
//        $resp["msg"] = $msg;
//        echo json_encode($resp);
    }

//list            
    public function vehicleexpenselist() {
        //Vehicle expense list by ***ARUL P DAS on 6/3/2020
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $site = isset($arr_request_data['site']) ? $arr_request_data['site'] : '';
        $vehicle = isset($arr_request_data['vehicle']) ? $arr_request_data['vehicle'] : '';
        $this->VehicleExpenses->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $conditions = "";
        if ($site != '') {
            $conditions .= " and site_fkey=" . $site;
//            $emp = $arr_request_data['employee'];
//            $emp_condition = "and au.emp_fkey=$emp";
        }
        if ($vehicle != '') {
            $conditions .= " and vehicle_master_fkey=" . $vehicle;
        }
        $counts = $this->VehicleExpenses->query(" SELECT COUNT(*) FROM transportation_expense te left join vehicle_master vm on (vehicle_master_pkey=vehicle_master_fkey)
left join site s on (site_pkey=site_fkey) 
where te.status=1 and s.status=1 and vm.status=1 $conditions ");

        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->VehicleExpenses->query("SELECT vm.reg_number,vm.make,vm.model_dec,s.site_id,s.site_name,te.* FROM transportation_expense te 
left join vehicle_master vm on (vehicle_master_pkey=vehicle_master_fkey)
left join site s on (site_pkey=site_fkey) 
where te.status=1 and s.status=1 and vm.status=1 $conditions ORDER BY creation_date desc"
                . " limit $limit  offset $ofst ");

        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['transportation_expense_pkey'] = isset($value["te"]["transportation_expense_pkey"]) ? $value["te"]["transportation_expense_pkey"] : '';
            $out['vehicle_master_fkey'] = isset($value["te"]["vehicle_master_fkey"]) ? $value["te"]["vehicle_master_fkey"] : '';
            $out['reg_number'] = isset($value["vm"]["reg_number"]) ? $value["vm"]["reg_number"] . ' - ' . $value["vm"]["model_dec"] : '';
            $out['site_fkey'] = isset($value["te"]["site_fkey"]) ? $value["te"]["site_fkey"] : '';
            $out['site_name'] = isset($value["s"]["site_name"]) ? $value["s"]["site_id"] . ' - ' . $value["s"]["site_name"] : '';
            $out['driver_name'] = isset($value["te"]["driver_name"]) ? $value["te"]["driver_name"] : '';
            $out['purpose'] = isset($value["te"]["purpose"]) ? $value["te"]["purpose"] : '';
            $out['starting_date'] = isset($value["te"]["starting_date"]) ? date('d-m-Y',strtotime($value["te"]["starting_date"])) : '';
            $out['ending_date'] = isset($value["te"]["ending_date"]) ? date('d-m-Y',strtotime($value["te"]["ending_date"])) : '';
            $out['total_km'] = isset($value["te"]["total_km"]) ? $value["te"]["total_km"] : '';
            $out['total_cost'] = isset($value["te"]["total_cost"]) ? $value["te"]["total_cost"] : '';
            $out['payments'] = isset($value["te"]["payments"]) ? $value["te"]["payments"] : '';
            $out['balance'] = isset($value["te"]["balance"]) ? $value["te"]["balance"] : '';
//            $out['remarks'] = isset($value["te"]["remarks"]) ? $value["te"]["remarks"] : '';
//            $out['ending_km'] = isset($value["te"]["ending_km"]) ? $value["te"]["ending_km"] : '';
//            $out['starting_km'] = isset($value["te"]["starting_km"]) ? $value["te"]["starting_km"] : '';
//            $out['creation_date'] = isset($value["te"]["creation_date"]) ? $value["te"]["creation_date"] : '';
//            $out['created_by'] = isset($value["te"]["created_by"]) ? $value["te"]["created_by"] : '';
//            $out['modification_date'] = isset($value["te"]["modification_date"]) ? $value["te"]["modification_date"] : '';
//            $out['modified_by'] = isset($value["te"]["modified_by"]) ? $value["te"]["modified_by"] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    public function Expenses() {
        //Vehicle expense index function by ***ARUL P DAS on 6/3/2020
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Site->useDbConfig = $this->Session->read('ds');
        $conditions = array("status" => 1);
        $fields = array("site_pkey", "site_id", "site_name");
        $this->set("arr_site", $arr_employees = $this->Site->find("all", array('fields' => $fields, 'conditions' => $conditions)));
        $this->set("arr_vehicles", $arr_vehicles = $this->Site->query("select vehicle_master_pkey,make,model_dec from vehicle_master where status=1"));
    }

//delete
    public function deleteEmployee() {
        //Vehicle expense delete function by ***ARUL P DAS on 6/3/2020
        $this->autoRender = FALSE;
        $this->VehicleExpenses->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["ids"]);
            //debug($ar_ids);
            $this->VehicleExpenses->updateAll(
                    array('VehicleExpenses.status' => 0), array('VehicleExpenses.transportation_expense_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s) deleted successfully.";
        }
        echo json_encode($result);
    }

}
