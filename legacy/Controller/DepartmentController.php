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
class DepartmentController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Department';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Departments');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		$this->layout = FALSE;
		
		$this->Departments->useDbConfig = $this->Session->read('ds');
		
	
		
	}
	
	
public function form() {
		$data['id'] = 0;
		$data['dept_code'] = "";
		$data['dept_name'] = "";
		$data['status'] = "";
		
				
		$this->Departments->useDbConfig = $this->Session->read('ds');
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
			$data_db = $this->Departments->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
		//	debug($data);
			$data = $data_db['Departments'];
		}

		$this->layout = null;
		$this->set("data",$data);
	}
        public function savedepartment(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->Departments->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		
		$data = array();
		
                
                if($this->checkdepartmentcodeexists($arr_form_data['id'], $arr_form_data['dept_code']) > 0){
                   return json_encode(array("success"=>false,"msg"=>"Department Already ")); 
                }
                if($this->checkdepartmentexists($arr_form_data['id'], $arr_form_data['dept_name']) > 0){
                   return json_encode(array("success"=>false,"msg"=>"Department name Already ")); 
                }
                
		$data['id']        = $arr_form_data['id'];
		$data['dept_code'] = $arr_form_data['dept_code'];
		$data['dept_name'] = $arr_form_data['dept_name'];
		//$data['status']    = $arr_form_data['status'];
	
		$result	=	$this->Departments->save($data);
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		
		$resp["msg"] = "Department Saved Successfully";
		echo json_encode($resp); 
		/*
		if(!empty($result)){
					return 'success';
				}*/
		
	}
	public function listdepartments() {
            $this->datatable["conditions"] = array("status" => 1);
            $limit = $_REQUEST['rows'];
            $page = $_REQUEST['page'];

            $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'dept_code';
            $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

            $ofst = ($page - 1) * $limit;

            $this->Departments->useDbConfig = $this->Session->read('ds');
            //    echo json_encode($this->DataTable->getData('Departments',$columns));
            $resp_banks = array();
            $resp_banks["rows"] = array();
            $this->Departments->useDbConfig = $this->Session->read('ds');
            $count = $this->Departments->find("count", array("conditions" => array('status' => 1)));
            $arr_banks = $this->Departments->find("all", array(
                    "conditions" => array(
                        "status" => 1
                    ), 
                    'order'=>array($sort=>$order),
                    'limit' => intval($limit), 
                    'offset' => intval($ofst)
                )
            );
            foreach ($arr_banks as $key => $value) {
                $resp_banks["rows"][$key] = $value["Departments"];
            }
            $resp_banks["total"] = $count;
            echo json_encode($resp_banks);
            $this->autoRender = FALSE;
        }

        public function deleteDepartment(){
			$this->autoRender=FALSE;
			$this->Departments->useDbConfig = $this->Session->read('ds');
			$result =   array('success' => 0 );
			if(isset($_REQUEST["ids"]))
			{
				$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
				$this->Departments->updateAll(
				    array('Departments.status' => 0),
				    array('Departments.id' => $ar_ids)
				);
			$result['success'] = true;
			$result['msg'] = "Record(s)  deleted successfully.";
			}
			
		echo json_encode($result);
	}
         public function checkdepartmentexists($id=0,$namecount = ''){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $dept_name = isset($arr_requestdata['dept_name'])?$arr_requestdata['dept_name']:$namecount;
        
        $int_deptcount = 0;
        if($dept_name != ''){
            $this->Departments->useDbConfig = $this->Session->read('ds');
            $int_deptcount = $this->Departments->find("count",array(
                    'conditions' => array('Departments.dept_name' => $dept_name,'Departments.status' => 1,"Departments.id!='$id' ")
                )
            );
        }
        return $int_deptcount;
    }
    
        public function checkdepartmentcodeexists($id=0,$codecount = ''){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $dept_code = isset($arr_requestdata['dept_code'])?$arr_requestdata['dept_code']:$codecount;
      
            
        
        $int_deptcount = 0;
        if($dept_code != ''){
            $this->Departments->useDbConfig = $this->Session->read('ds');
            $int_deptcount = $this->Departments->find("count",array(
                    'conditions' => array('Departments.dept_code' => $dept_code,'Departments.status' => 1,"Departments.id!='$id' ")
                )
            );
        }
        return $int_deptcount;
    }
} 


