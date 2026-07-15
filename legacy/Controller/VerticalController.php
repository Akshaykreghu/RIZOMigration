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
class VerticalController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Vertical';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Verticals');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		$this->layout = FALSE;
		
		$this->Verticals->useDbConfig = $this->Session->read('ds');
		
	
		
	}
	
	
	public function newVertical(){
		
		$data['id'] = 0;
		$data['dept_code'] = "";
		$data['dept_name'] = "";
		$data['status'] = "";
		
				
		$this->Verticals->useDbConfig = $this->Session->read('ds');
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
	$data_db = $this->Verticals->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
		//	debug($data);
			$data = $data_db['Verticals'];
		}

		$this->set(compact("data"));
		$this->layout = null;
		
		
	}
		public function saveVertical(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->Verticals->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		
		$data = array();
		
		$data['id']        = $arr_form_data['id'];
		$data['dept_code'] = $arr_form_data['dept_code'];
		$data['dept_name'] = $arr_form_data['dept_name'];
		//$data['status']    = $arr_form_data['status'];
	
		$result	=	$this->Verticals->save($data);
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		
		echo json_encode($resp); 
		/*
		if(!empty($result)){
					return 'success';
				}*/
		
	}
	public function listVerticals(){
       
		$this->datatable["conditions"] = array("status"=> 1);
		
		$this->Verticals->useDbConfig = $this->Session->read('ds');
		$resp_banks = array();
		$this->Verticals->useDbConfig = $this->Session->read('ds');
		$arr_banks =  $this -> Verticals ->find("all");
		foreach ($arr_banks as $key => $value) {
			$resp_banks["verticals"][$key] = $value["Verticals"];
		}
		echo json_encode($resp_banks);
    	$this->autoRender=FALSE;
		
        
	}
	
	public function deleteVertical(){
			$this->autoRender=FALSE;
			$this->Verticals->useDbConfig = $this->Session->read('ds');
			$result =   array('success' => 0 );
			if(isset($_REQUEST["ids"]))
			{
				$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
				$this->Verticals->updateAll(
				    array('Verticals.status' => 0),
				    array('Verticals.id' => $ar_ids)
				);
				$result['success'] = 1;
			}
			
		echo json_encode($result);
	}
}
