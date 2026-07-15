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
class SectionController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Section';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Section','EmployeeProfessionalDetails');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		$this->layout = FALSE;
		
		$this->Section->useDbConfig = $this->Session->read('ds');
		
	
		
	}
	
	
public function form() {
		$data['id'] = 0;
		$data['section_code'] = "";
		$data['section_name'] = "";
		$data['status'] = "";
		
				
		$this->Section->useDbConfig = $this->Session->read('ds');
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
			$data_db = $this->Section->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
		//	debug($data);
			$data = $data_db['Section'];
		}

		$this->layout = null;
		$this->set("data",$data);
	} 
        public function savesection(){
            
            //echo save;
            //exit;
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->Section->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		
		$data = array();
		
                
                if($this->checksectioncodeexists($arr_form_data['id'], $arr_form_data['sect_code']) > 0){
                   return json_encode(array("success"=>false,"msg"=>"Section Already ")); 
                }
                if($this->checksectionexists($arr_form_data['id'], $arr_form_data['sect_name']) > 0){
                   return json_encode(array("success"=>false,"msg"=>"Section name Already ")); 
                }
                
		$data['id']        = $arr_form_data['id'];
		$data['section_code'] = $arr_form_data['sect_code'];
		$data['section_name'] = $arr_form_data['sect_name'];
		//$data['status']    = $arr_form_data['status'];
	         
		$result	=	$this->Section->save($data);
		//debug($result);
                //exit;
		$resp = array();
                
		$resp["success"] = true;
		
		$resp["msg"] = "Section Saved Successfully";
		echo json_encode($resp); 
		//if(!empty($result)){
                
				//}
		
	}
	public function listsection() {
            $this->datatable["conditions"] = array("status" => 1);
            $limit = $_REQUEST['rows'];
            $page = $_REQUEST['page'];

            $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'section_code';
            $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

            $ofst = ($page - 1) * $limit;

            $this->Section->useDbConfig = $this->Session->read('ds');
            //    echo json_encode($this->DataTable->getData('Departments',$columns));
            $resp_banks = array();
            $resp_banks["rows"] = array();
            $this->Section->useDbConfig = $this->Session->read('ds');
            $count = $this->Section->find("count", array("conditions" => array('status' => 1)));
            $arr_banks = $this->Section->find("all", array(
                    "conditions" => array(
                        "status" => 1
                    ), 
                    'order'=>array($sort=>$order),
                    'limit' => intval($limit), 
                    'offset' => intval($ofst)
                )
            );
            foreach ($arr_banks as $key => $value) {
                $resp_banks["rows"][$key] = $value["Section"];
            }
            $resp_banks["total"] = $count;
            echo json_encode($resp_banks);
            $this->autoRender = FALSE;
        } 

        public function deleteSection(){
			$this->autoRender=FALSE;
			$this->Section->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                        $arr_form_data	=$this->request->data;
                        $ar_id = explode(",", $_REQUEST['ids']) ;
		        $data = array();
                        $asiigned = $this->EmployeeProfessionalDetails->find("count",array("conditions"=>array('EmployeeProfessionalDetails.emp_sep_priv' => $ar_id)));
              
            	        $resp = array();
                        $result =   array('success' => 0 );
		        if($asiigned > 0 )

                        {
                            //edited by sinisya 0n 13-03-2024
                            $result["danger"] = true;
                            $result["msg"] = "Employees allocated under the selected section";
                          // $result["success"] = false;
    
                          // $result["msg"] = "Employees allocated under the selected section";

                        }
                        else{
			   $result =   array('success' => 0 );
			   if(isset($_REQUEST["ids"]))
			   {
				$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
				$this->Section->updateAll(
				    array('Section.status' => 0),
				    array('Section.id' => $ar_ids)
				);
			        $result['success'] = true;
			        $result['msg'] = "Record(s)  deleted successfully.";
			   }
		        }
		
		echo json_encode($result);
	}
         public function checksectionexists($id=0,$namecount = ''){  
             
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $sec_name = isset($arr_requestdata['section_name'])?$arr_requestdata['section_name']:$namecount;
        
        $int_seccount = 0;
        if($sec_name != ''){
            
            $this->Section->useDbConfig = $this->Session->read('ds');
            $int_seccount = $this->Section->find("count",array(
                    'conditions' => array('Section.section_name' => $sec_name,'Section.status' => 1,"Section.id!='$id' ")
                )
            );
        }
        return $int_seccount;
    }
    
        public function checksectioncodeexists($id=0,$codecount = ''){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $section_code = isset($arr_requestdata['section_code'])?$arr_requestdata['section_code']:$codecount;
      
            
        
        $int_seccodecount = 0;
        if($section_code != ''){
            $this->Section->useDbConfig = $this->Session->read('ds');
            $int_seccodecount = $this->Section->find("count",array(
                    'conditions' => array('Section.section_code' => $section_code,'Section.status' => 1,"Section.id!='$id' ")
                )
            );
        }
        return $int_seccodecount;
    }
} 


