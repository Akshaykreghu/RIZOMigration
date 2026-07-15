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
class FinancialYearController extends AppController {

public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'FinancialYear';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Units','FinancialYear');
	public $components = array('DatatablesManagement','MasterdataManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		
	
		
	}
	
	
	
public function form() {
		$this -> layout = null;
		$this -> FinancialYear -> useDbConfig = $this -> Session -> read('ds');
                $this->Units->useDbConfig = $this->Session->read('ds');
                $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
                $this->set('branches', $arr_branches);
		$data['Fin_year_seq'] = 0;
		$data['start_month'] = "";
		$data['end_month'] = "";
		
		$data['Year_status'] = "OPEN";
	
			
		$this->FinancialYear->useDbConfig = $this->Session->read('ds');
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
		$data_db = $this->FinancialYear->find("first",array("conditions"=>array("Fin_year_seq"=>$_REQUEST['id'])));
			
			
		//	debug($data);
		$data  = $data_db['FinancialYear'];
		$start = strtotime( $data['start_month']);
		$end   = strtotime( $data['end_month']);
		$data['start_month'] = date("d/m/Y",$start);;
		$data['end_month'] =  date("d/m/Y",$end);;
		}
		//debug($data);
		$this->set("data",$data);
	}
	
	
		public function save(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->FinancialYear->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		
		$data = array();
		//$start = DateTime::createFromFormat('d/M/Y', $arr_form_data['start_month']);
	//	$end   = DateTime::createFromFormat('d/M/Y', $arr_form_data['end_month']);
		$end   = DateTime::createFromFormat('d/m/Y', $arr_form_data['end_month']);
		
		$start = DateTime::createFromFormat('d/m/Y',$arr_form_data['start_month']);
		/*
		$start = strtotime( $arr_form_data['start_month']);
				$end   = strtotime( $arr_form_data['end_month']);*/
		$data['branch_code'] = $arr_form_data['branch'];
                $data['vattr1'] = $arr_form_data['type'];
		$data['Fin_year_seq'] = $arr_form_data['Fin_year_seq'];
		$data['start_month']  = $start->format('Y-m-d');
		$data['end_month']    = $end->format('Y-m-d');
		$data['Year_status']  = $arr_form_data['Year_status'];
		$data['fin_year']     = $start->format('Y');
		$data['is_current_finyear'] = (isset($arr_form_data['is_current_finyear'])?$arr_form_data['is_current_finyear']:"N");
		//added by megha on 24_10_2019 company code not saving
                $data['company_code']  =  $this->Session->read('company_code');
		//debug($data);
		//$data['status']    = $arr_form_data['status'];
	
		$result	=	$this->FinancialYear->save($data);
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Financial saved successfully";
		echo json_encode($resp);

		
	}
	public function listfinyears(){
        
		$this->datatable["conditions"] = array("status"=> 1);
		

		$limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];
		
                $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'fin_year';
                $order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
		
		$ofst = ($page-1)*$limit;
		
		$resp_banks  = array();
		$this->FinancialYear->useDbConfig = $this->Session->read('ds');
		$count = $this -> FinancialYear -> find("count",array("conditions"=> array("FinancialYear.status"=> 1)));
		$arr_banks =  $this -> FinancialYear ->find("all",array(
                        "fields"=>array("FinancialYear.*,Branches.branch_code,Branches.branch_name"),
                        "conditions"=> array(
                            "FinancialYear.status"=> 1
                        ),
                        'joins'=>array(
                            array(
                                'table'=>'branches',
                                'alias'=>'Branches',
                                'type'=>'LEFT',
                                'foreignKey' => false,
                                'conditions'=>array("Branches.branch_code = FinancialYear.branch_code")
                            )
                        ),
                        'order'=>array($sort=>$order),
                        'limit'=>intval($limit),
                        'offset'=>intval($ofst)
                    )
                );
		$resp_banks["rows"]= array();
                foreach ($arr_banks as $key => $value) {
			$resp_banks["rows"][$key] = $value["FinancialYear"];
			$start = strtotime( $value["FinancialYear"]['start_month']);
			$end   = strtotime( $value["FinancialYear"]['end_month']);
                        if($value["FinancialYear"]['vattr1'] == 0){
                            $resp_banks["rows"][$key]['vattr1']  = 'Leave';
                        }
                        else{
                            $resp_banks["rows"][$key]['vattr1']  = 'Financial';
                        }
                        $resp_banks["rows"][$key]['Branches'] = $value['Branches']['branch_name'];
			$resp_banks["rows"][$key]['start_month']  = date("d-m-Y",$start);
			$resp_banks["rows"][$key]['end_month']  = date("d-m-Y",$end);
		}
		
		$resp_banks["total"] = $count;
		
		echo json_encode($resp_banks);
		
		//$arr_banks = $this->Banks->find("all");
		
      //  echo json_encode($this->DataTable->getData('Banks',$columns));
    	$this->autoRender=FALSE;
		
       
		

		
	}

public function deleteFinYear()
{
			$this->autoRender=FALSE;
			$this->FinancialYear->useDbConfig = $this->Session->read('ds');
			$result =   array('success' => 0 );
			if(isset($_REQUEST["ids"]))
			{
				$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
				$this->FinancialYear->updateAll(
				    array('FinancialYear.status' => 0),
				    array('FinancialYear.Fin_year_seq' => $ar_ids)
				);
				$result['success'] = true;
			    $result['msg'] = "Record(s)  deleted successfully.";
			}
			
		echo json_encode($result);
}

}
