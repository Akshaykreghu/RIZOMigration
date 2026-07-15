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
class DesignationController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Designation';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Designation');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		$this->layout = FALSE;
		
		$this->Designation->useDbConfig = $this->Session->read('ds');
		
	
		
	}
	
	
public function form($id=0) {
    
    $this -> layout = null;
		$this -> Designation -> useDbConfig = $this -> Session -> read('ds');
		$data['id'] = 0;
		$data['branch_name'] = "";
		$data['address'] = "";
		$data['state'] = "";
		$data['pincode'] = "";
		$data['city'] = "";

		$this -> Designation -> useDbConfig = $this -> Session -> read('ds');
		if (isset($_REQUEST['id']) && $_REQUEST['id'] != 0) {
			$data_db = $this -> Designation -> find("first", array("conditions" => array("id" => $_REQUEST['id'])));
			//	debug($data);
			$data = $data_db['Designation'];
		}
                
                $desig_codes = $this->Designation->find("all",array("fields"=>array("desig_code"),"conditions"=>array("status"=>"1")));
                
                
                $desig_arr = Set::extract('/Designation/.',$desig_codes);
                
                $this->set("desig_arr",Set::extract('/desig_code/.',$desig_arr));
		
		$this->set("data",$data);
	}
		public function save(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		$companycode=$this->Session->read('company_code');
		$this->Designation->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		//debug($arr_form_data);
		
		$data = array();
		 if($this->checkdesignationcodeexists($arr_form_data['id'],$arr_form_data['desig_code']) > 0){
                    return json_encode(array("success"=>false,"msg"=>"designation Already ")); 
                }
                
                if($this->checkdesignationexists($arr_form_data['id'],$arr_form_data['desig_name']) > 0){
                    return json_encode(array("success"=>false,"msg"=>"designation Already ")); 
                }
                
		$data['id']        = $arr_form_data['id'];
		$data['desig_code'] = $arr_form_data['desig_code'];
		$data['desig_name'] = $arr_form_data['desig_name'];
                //$data['org_id'] = $companycode;
		//$data['status']    = $arr_form_data['status'];
	
		$result	=	$this->Designation->save($data);
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		
		$resp["msg"] = "Designation data   Saved Successfully";
		echo json_encode($resp); 
		/*
		if(!empty($result)){
					return 'success';
				}*/
		
	}
        
        public function listDesignation() {
            $this->autoRender = FALSE;
            $this->datatable["conditions"] = array("status" => 1);
            
            $limit = $_REQUEST['rows'];
            $page = $_REQUEST['page'];

            $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'desig_code';
            $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

            $ofst = ($page - 1) * $limit;

            $this->Designation->useDbConfig = $this->Session->read('ds');
            //    echo json_encode($this->DataTable->getData('Departments',$columns));
            $resp_banks = array();
            $resp_banks["rows"] = array();
            $this->Designation->useDbConfig = $this->Session->read('ds');
            
            $count = $this->Designation->find("count",array("conditions"=> array("status"=> 1)));
            $arr_banks = $this->Designation->find("all", array(
                    "conditions" => array(
                        "status" => 1
                    ),
                    'order'=>array($sort=>$order),
                    'limit'=>intval($limit),
                    'offset'=>intval($ofst)
                )
            );

            foreach ($arr_banks as $key => $value) {
                $resp_banks["rows"][$key] = $value["Designation"];
            }
            $resp_banks["total"] = $count;
            echo json_encode($resp_banks);
        }

        public function deleteDepartment(){
			$this->autoRender=FALSE;
			$this->Designation->useDbConfig = $this->Session->read('ds');
			$result =   array('success' => 0 );
			if(isset($_REQUEST["ids"]))
			{
				$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
				$this->Designation->updateAll(
				    array('Designation.status' => 0),
				    array('Designation.id' => $ar_ids)
				);
			$result['success'] = true;
			$result['msg'] = "Record(s)  deleted successfully.";
			}
			
		echo json_encode($result);
	}
        public function checkdesignationexists($id=0,$namecount = ''){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $desig_name = isset($arr_requestdata['desig_name'])?$arr_requestdata['desig_name']:$namecount;
        
        $int_desigcount = 0;
        if($desig_name != ''){
            $this->Designation->useDbConfig = $this->Session->read('ds');
            $int_desigcount = $this->Designation->find("count",array(
                    'conditions' => array('Designation.desig_name' => $desig_name,'Designation.status' => 1,"Designation.id!='$id' ")
                )
            );
        }
        return $int_desigcount;
        
}
 public function checkdesignationcodeexists($id=0,$codecount = ''){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $desig_code = isset($arr_requestdata['desig_code'])?$arr_requestdata['desig_code']:$codecount;
        
        $int_desigcount = 0;
        if($desig_code != ''){
            $this->Designation->useDbConfig = $this->Session->read('ds');
            $int_desigcount = $this->Designation->find("count",array(
                    'conditions' => array('Designation.desig_code' => $desig_code,'Designation.status' => 1, "Designation.id!='$id' ")
                )
            );
        }
        return $int_desigcount;
    }
}
