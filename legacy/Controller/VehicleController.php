<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * 
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
class VehicleController extends AppController {
    
	public $name = 'Vehicle';
    public $datatable;
    public $uses = array('Vehicle');//models
    
    /*b
     * Lists workstatus Here
     */
    public function index(){
        
    }


    public function vehicle($vehicle_master_pkey = 0){
		 $this->Vehicle->useDbConfig = $this->Session->read('ds');
         if($vehicle_master_pkey == 0){
            $title = 'Add Vehicle';
        }
        else{
            $title = 'Edit Vehicle';
        }
        $this->set('title', $title);
        $this->layout = NULL;
        $result = $this->Vehicle->query("select * from vehicle_master where vehicle_master_pkey = '$vehicle_master_pkey'");
        $this->set('result',$result);   
    }

     public function delete($user_pkey = 0)
    {
		$this->Vehicle->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE ; 
        if($user_pkey ==0){
        }
        if ($user_pkey != 0) {
            $this->Vehicle->updateAll(array('status' => 0), array('vehicle_master_pkey' => $user_pkey));
            echo json_encode(array('msg' => 'Vehicle details deleted successfully!'));
        } else {
            echo json_encode(array('msg' => 'Vehicle details deletion failed!'));
        }     
    }
        
    public function save()
        {
         
                $this->autoRender = FALSE;
                $this->layout = null;
                $this->Vehicle->useDbConfig = $this->Session->read('ds');
                $arr_form_data = $this->request->data;
                   $code = $arr_form_data['reg_number']; 
                   $name = $arr_form_data['model_dec']; 
                   $pkey = $arr_form_data['vehicle_master_pkey'];
                   $cond = " Vehicle.vehicle_master_pkey != $pkey";
                   $int_vehiclecount=0;
                   $int_namecount = 0;
                if(isset($pkey)) {
                   if ($code !='') {
                   $int_vehiclecount = $this->Vehicle->find("count", array(
                   'conditions' => array('Vehicle.reg_number' => $code, 'Vehicle.status' =>  1 ,'Vehicle.vehicle_master_pkey != '=> $pkey)
                    ));
                }
               /*      if ($name !='') {
                $int_namecount = $this->Vehicle->find("count", array(
                'conditions' => array('Vehicle.model_dec' => $name, 'Vehicle.status' => 1 ,'Vehicle.vehicle_master_pkey != '=> $pkey)
                  
                    ));

                   } */
}

          /*     if($int_vehiclecount >0) {
                    $resp["msg"] = "Registration Number Already Exist";
                    $resp = array();

                    $resp["success"] = false; 
                    
                    echo json_encode($resp);
                  } */
               /*  if($int_namecount >0){
                     $resp["msg"] = "Vehicle Already Exist";
                    $resp =array();
                    $resp["success"] = false;
                    
                    echo json_encode($resp);
                  } */
                
                   // if($int_vehiclecount==0 && $int_namecount==0){
                   if($int_vehiclecount==0){
                        $time=$this->Vehicle->query("select now() as time");
                        $this->set('time',$time);
                    $arr_form_data['creation_date'] = $time['0']['0']['time'];
                    $arr_form_data['created_by'] = $this->Session->read('user_name');
                    $arr_form_data['modified_date'] = $time['0']['0']['time'];   
                    $arr_form_data['modified_by'] = $this->Session->read('user_name');
                    $arr_form_data['status']=1;
        
                    $result = $this->Vehicle->save($arr_form_data);
                    $resp = array();
                    $resp["success"] = true;
                    $resp["msg"] = "Vehicle Added successfully";
                    echo json_encode($resp);
                   }else{
					$resp["msg"] = "Registration Number Already Exist";
                    $resp = array();
                    $resp["success"] = false; 
                    echo json_encode($resp);   
                   }
	}
    public function listvehicle()
    {
		
        $this->autoRender = false ;
         $this->Vehicle->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
       
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'vehicle_master_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';

        $ofst = ($page - 1) * $limit;  
         $where = array();
        if(isset($arr_data['model_dec'])){
            $where[] = "((Vehicle.model_dec like '%".$arr_data['model_dec']."%')  or (Vehicle.reg_number like '%".$arr_data['reg_number']."%') or (Vehicle.make like '%".$arr_data['make']."%') or (Vehicle.vehicle_type like '%".$arr_data['vehicle_type']."%') or (Vehicle.make_year like '%".$arr_data['make_year']."%') or (Vehicle.fual_type like '%".$arr_data['fual_type']."%'))";
        }
        $result_count = $this->Vehicle->find("count",array(
             'conditions'=>array('Vehicle.status' => 1)
         ));
        
         $result = $this->Vehicle->find("all",array(
             'conditions'=>array('Vehicle.status' => 1,$where) ,
                    'order'=>array($sort=>$order),
                    'limit'=>intval($limit),
                    'offset'=>intval($ofst)
         ));
         
        $rows = array();
        foreach ($result as $key => $val) {
            $rows[] = $val["Vehicle"];
        }

        $resp_data["rows"] = $rows;
        $resp_data["total"] = $result_count;

        echo json_encode($resp_data);
    }
     public function checkexpensetypenameexists($expense_type_name = 0){
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;
        $expense_type_name   = isset($arr_requestdata['expense_type_name']) ? $arr_requestdata['expense_type_name'] : '';

        $int_expensetypecount = 0;
        if ($expense_type_name  != '') {
            $this->Vehicle->useDbConfig = $this->Session->read('ds');
            $int_expensetypecount = $this->Vehicle->find("count", array(
                'conditions' => array('Vehicle.expense_type_name ' => $expense_type_name, 'Vehicle.status' => 1,)
                    )
            );
        }
        echo $int_expensetypecount;
    }
     public function checkregnoexists($reg_number = 0,$pkey = 0) {
        $this->autoRender = false;
        $arr_requestdata = $this->request->data;
        $reg_number = isset($arr_requestdata['reg_number']) ? $arr_requestdata['reg_number'] : '';
		$pkey = isset($arr_requestdata['pkey']) ? $arr_requestdata['pkey'] : '';
		if($pkey !=''){
			$Where = "Vehicle.vehicle_master_pkey != $pkey";
		}else{
			$Where = "";
		}
        $int_regcount = 0;
        if ($reg_number != '') {
            $this->Vehicle->useDbConfig = $this->Session->read('ds');
           $int_regcount = $this->Vehicle->find("count", array(
                'conditions' => array('Vehicle.reg_number' => $reg_number, 'Vehicle.status' => 1,$Where)
                    )
            );
        }
        echo $int_regcount;
    }
    public function search(){
      $this->autoRender = false;
      $this->Vehicle->useDbConfig = $this->Session->read('ds');
      $arr_request_data = $this->request->query;
      $searchkey = $arr_request_data['model_dec'];
	  $searchkey1 = $arr_request_data['reg_number'];
      $arr_list = array();
      $arr_list[] = $this->Vehicle->find('all', array(
      'fields' => 'vehicle_master_pkey,model_dec,reg_number','Vehicle.',
      'conditions' => array(
      '(Vehicle.model_dec LIKE "%' . $searchkey . '%")' or '(Vehicle.reg_number LIKE "%' . $searchkey1 . '%")','Vehicle.status' => 1
       ),
       ));
      echo json_encode($arr_list);
    }
// 
// SELECT `Example`.* FROM `examples` AS `Example` WHERE match(`Example`.`body`) against('search text');SELECT `Example`.* FROM `examples` AS `Example` WHERE match(`Example`.`body`) against('search text');
}