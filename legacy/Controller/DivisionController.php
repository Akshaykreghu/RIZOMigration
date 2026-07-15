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
class DivisionController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Division';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Division','EmployeeProfessionalDetails');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		$this->layout = FALSE;
		
		$this->Division->useDbConfig = $this->Session->read('ds');
		
	
		
	}
	
	
public function form() {
		$data['id'] = 0;
		$data['div_code'] = "";
		$data['div_name'] = "";
		$data['status'] = "";
		
				
		$this->Division->useDbConfig = $this->Session->read('ds');
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
			$data_db = $this->Division->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
		//	debug($data);
			$data = $data_db['Division'];
		}

		$this->layout = null;
		$this->set("data",$data);
	} 
        public function savedivision(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->Division->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		
		$data = array();
		
                
                if($this->checkdivisioncodeexists($arr_form_data['id'], $arr_form_data['div_code']) > 0){
                   return json_encode(array("success"=>false,"msg"=>"Division Already ")); 
                }
                if($this->checkdivisionexists($arr_form_data['id'], $arr_form_data['div_name']) > 0){
                   return json_encode(array("success"=>false,"msg"=>"Division name Already ")); 
                }
                
		$data['id']        = $arr_form_data['id'];
		$data['div_code'] = $arr_form_data['div_code'];
		$data['div_name'] = $arr_form_data['div_name'];
		//$data['status']    = $arr_form_data['status'];
	         
		$result	=	$this->Division->save($data);
		//debug($result);
		$resp = array();
                
		$resp["success"] = true;
		
		$resp["msg"] = "Division Saved Successfully";
		echo json_encode($resp); 
		//if(!empty($result)){
                
				//}
		
	}
	public function listdivision() {
            $this->datatable["conditions"] = array("status" => 1);
            $limit = $_REQUEST['rows'];
            $page = $_REQUEST['page'];

            $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'div_code';
            $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

            $ofst = ($page - 1) * $limit;

            $this->Division->useDbConfig = $this->Session->read('ds');
            //    echo json_encode($this->DataTable->getData('Departments',$columns));
            $resp_banks = array();
            $resp_banks["rows"] = array();
            $this->Division->useDbConfig = $this->Session->read('ds');
            $count = $this->Division->find("count", array("conditions" => array('status' => 1)));
            $arr_banks = $this->Division->find("all", array(
                    "conditions" => array(
                        "status" => 1
                    ), 
                    'order'=>array($sort=>$order),
                    'limit' => intval($limit), 
                    'offset' => intval($ofst)
                )
            );
            foreach ($arr_banks as $key => $value) {
                $resp_banks["rows"][$key] = $value["Division"];
            }
            $resp_banks["total"] = $count;
            echo json_encode($resp_banks);
            $this->autoRender = FALSE;
        } 

        public function deleteDivision(){
            
	        $this->autoRender=FALSE;
	        $this->Division->useDbConfig = $this->Session->read('ds');
                $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                $arr_form_data	=$this->request->data;

		$ar_id = explode(",", $_REQUEST['ids']) ;
                
                $data = array();
		//$asiigned = $this->EmployeeProfessionalDetails->find("count",array("joins"=>$joins,"conditions"=>array('EmployeeProfessionalDetails.day_time_seq' => $ar_id,'EmployeeDetails.status'=>1)));
                $asiigned = $this->EmployeeProfessionalDetails->find("count",array("conditions"=>array('EmployeeProfessionalDetails.emp_vertical' => $ar_id)));
              //debug($asiigned); exit;
            	$resp = array();
                 $result =   array('success' => 0 );
		if($asiigned > 0 )

                {
                   //edited by sinsiya on 13-03-2024
                   $result["danger"] = true;
                   $result["msg"] = "Employees allocated under the selected division";
                    // $result['color'] = 'red';
                    
                }
                else{
			
			if(isset($_REQUEST["ids"]))
			{
				$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
				$this->Division->updateAll(
				    array('Division.status' => 0),
				    array('Division.id' => $ar_ids)
				);
			$result["success"] = true;
			$result['msg'] = "Record(s)  deleted successfully.";
                        //$result['color'] = 'green';
			}
                }
		echo json_encode($result);
	}
         public function checkdivisionexists($id=0,$namecount = ''){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $div_name = isset($arr_requestdata['div_name'])?$arr_requestdata['div_name']:$namecount;
        
        $int_divcount = 0;
        if($div_name != ''){
            $this->Division->useDbConfig = $this->Session->read('ds');
            $int_divcount = $this->Division->find("count",array(
                    'conditions' => array('Division.div_name' => $div_name,'Division.status' => 1,"Division.id!='$id' ")
                )
            );
        }
        return $int_divcount;
    }
    
        public function checkdivisioncodeexists($id=0,$codecount = ''){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $div_code = isset($arr_requestdata['div_code'])?$arr_requestdata['div_code']:$codecount;
      
            
        
        $int_divcodecount = 0;
        if($div_code != ''){
            $this->Division->useDbConfig = $this->Session->read('ds');
            $int_divcodecount = $this->Division->find("count",array(
                    'conditions' => array('Division.div_code' => $div_code,'Division.status' => 1,"Division.id!='$id' ")
                )
            );
        }
        return $int_divcodecount;
    }
} 


