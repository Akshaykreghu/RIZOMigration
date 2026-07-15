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
class BankController extends AppController {

public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Bank';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Banks');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		$this->layout = FALSE;
		
		$this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
debug(		$this->CompanyContactInfo->find("all"));
	
		
	}
	
	
	
public function form() {
		$this -> layout = null;
		$this -> UserCredentials -> useDbConfig = $this -> Session -> read('ds');
		$data['id'] = 0;
		$data['bank_name'] = "";
		$data['bank_branch'] = "";
		
		$data['ifsc_code'] = "";
		$data['acct_no'] = "";
		$data['status'] = "";
		
			
		$this->Banks->useDbConfig = $this->Session->read('ds');
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
		$data_db = $this->Banks->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
		//	debug($data);
			$data = $data_db['Banks'];
		}
		$this->set("data",$data);
	}
	public function branches() {
		$this -> layout = FALSE;

	}
	
		public function savebank(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->Banks->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		
		$data = array();
		
		$data['id'] = $arr_form_data['id'];
		$data['bank_name'] = $arr_form_data['bank_name'];
		$data['bank_branch'] = $arr_form_data['bank_branch'];
		
		$data['ifsc_code'] = $arr_form_data['ifsc_code'];
		$data['acct_no'] = $arr_form_data['acct_no'];
		$data['status'] = (isset($arr_form_data['status'])?$arr_form_data['status']:1);
		
		//$data['status']    = $arr_form_data['status'];
	
		$result	=	$this->Banks->save($data);
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Bank saved successfully";

		echo json_encode($resp);
		/*
		if(!empty($result)){
					return 'success';
				}*/
		
	}
	public function listbanks(){
        
		$this->datatable["conditions"] = array("status"=> 1);
		

		$limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];
		
                $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'bank_name';
                $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

		$ofst = ($page-1)*$limit;
		
		$resp_banks  = array();
		$this->Banks->useDbConfig = $this->Session->read('ds');
		$count = $this -> Banks -> find("count",array("conditions"=>array('status' => 1)));
		$arr_banks =  $this -> Banks ->find("all",array(
                        "conditions"=>array(
                            "status"=> 1
                        ),
                        'order'=>array($sort=>$order),
                        'limit'=>intval($limit),
                        'offset'=>intval($ofst)
                    )
                );
		$resp_banks["rows"]= array();
		foreach ($arr_banks as $key => $value) {
			$resp_banks["rows"][$key] = $value["Banks"];
		}
		
		$resp_banks["total"] = $count;
		
		echo json_encode($resp_banks);
		
		//$arr_banks = $this->Banks->find("all");
		
      //  echo json_encode($this->DataTable->getData('Banks',$columns));
    	$this->autoRender=FALSE;
		
       
		

		
	}

public function deleteBank()
{
			$this->autoRender=FALSE;
			$this->Banks->useDbConfig = $this->Session->read('ds');
			$result =   array('success' => 0 );
			if(isset($_REQUEST["ids"]))
			{
				$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
				$this->Banks->updateAll(
				    array('Banks.status' => 0),
				    array('Banks.id' => $ar_ids)
				);
				$result['success'] = true;
			    $result['msg'] = "Record(s)  deleted successfully.";
			}
			
		echo json_encode($result);
}

}
