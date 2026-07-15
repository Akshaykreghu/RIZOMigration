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
ini_set("display_errors", 1);
App::uses('ConnectionManager', 'Model');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class BranchController extends AppController {

	public $datatable = array();
	/**
	 * Controller name
	 *
	 * @var string
	 */
	public $name = 'Branch';

	/**
	 * This controller does not use a model
	 *
	 * @var array
	 */
	//edited by sinsiya 13-06-2025
	public $uses = array('UserCredentials', 'CompanyContactInfo', 'Units','Device');
	public $components = array('DatatablesManagement');

	/*
	 * Dashboard landing view
	 */
	public function index() {

		$this -> UserCredentials -> useDbConfig = $this -> Session -> read('ds');

	}
public function form() {
		$this -> layout = null;
		$this -> UserCredentials -> useDbConfig = $this -> Session -> read('ds');
		$data['id'] = 0;
		$data['branch_name'] = "";
		$data['address'] = "";
		$data['state'] = "";
		$data['pincode'] = "";
		$data['city'] = "";
		$data['latitude'] = 0;
		$data['longitude'] = 0;
		
		$this -> Units -> useDbConfig = $this -> Session -> read('ds');
		if (isset($_REQUEST['id']) && $_REQUEST['id'] != 0) {
                        
			$data_db = $this -> Units -> find("first", array("conditions" => array("id" => $_REQUEST['id'])));
                        $branch_code = $data_db['Units']['branch_code'];
                        $data_db_fin_year = $this -> Units -> query("select * from fin_year where branch_code = '$branch_code' and is_current_finyear = 'Y' and status = '1' and vattr1 = '1' ");
                        $data_db_leave_year = $this -> Units -> query("select * from fin_year where branch_code = '$branch_code' and is_current_finyear = 'Y' and status = '1' and vattr1 = '0' ");
                        $data_db['Units']['fin_year'] = isset($data_db_fin_year['0']['fin_year'])?$data_db_fin_year['0']['fin_year']:'';
                        $data_db['Units']['leave_year'] = isset($data_db_leave_year['0']['fin_year'])?$data_db_leave_year['0']['fin_year']:'';
//				debug($data_db);
			$data = $data_db['Units'];
		}
		
		$this->set("data",$data);
	}
	public function branches() {
		$this -> layout = FALSE;

	}

	public function load() {

		$this -> autoRender = FALSE;
		$this -> layout = null;
		$data['id'] = 0;
		$data['branch_name'] = "";
		$data['address'] = "";
		$data['state'] = "";
		$data['pincode'] = "";
		$data['city'] = "";

		$this -> Units -> useDbConfig = $this -> Session -> read('ds');
		if (isset($_REQUEST['id']) && $_REQUEST['id'] != 0) {
			$data_db = $this -> Units -> find("first", array("conditions" => array("id" => $_REQUEST['id'])));
			//	debug($data);
			$data = $data_db['Units'];
		}
		$respdata = array('success' => true, "data" => $data);
	echo	json_encode($respdata);

	}

	public function savebranch() {

		$this -> autoRender = FALSE;
		$this -> layout = null;

		$this -> Units -> useDbConfig = $this -> Session -> read('ds');

		$arr_form_data = $this -> request -> data;

		$data = array();

		$data['id'] = $arr_form_data['id'];
		$data['branch_name'] = $arr_form_data['branch_name'];
		if($arr_form_data['id'] == 0 || $arr_form_data['id'] == null || $arr_form_data['id'] == ""){
		if(strlen($arr_form_data['branch_name']) >3){
			$data['branch_code'] = substr($arr_form_data['branch_name'], 0,3).strtotime("now");
		}else{
			$data['branch_code'] = $arr_form_data['branch_name'].strtotime("now");
		}
		}
		
		$data['company_code'] = $this -> Session -> read('company_code');
		$data['address'] = $arr_form_data['address'];
		$data['state'] = $arr_form_data['state'];
		$data['latitude'] = $arr_form_data['lat'];
        $data['longitude'] = $arr_form_data['longt'];
		$data['status'] = 1;
		$data['pincode'] = $arr_form_data['pincode']; 
		$data['city'] = $arr_form_data['city'];
		$result = $this -> Units -> save($data); 
//                debug($result);
                $company_code = $data['company_code'];
                $leaveentryId = $this->Units->getLastInsertID();
                if(isset($data['id']) && $data['id'] != '0') $leaveentryId = $data['id'];
//                debug($leaveentryId);
                $get_branchCode = $this->Units->query("select branch_code from branches where	id = '$leaveentryId' ");
                
                $branch_code = $leaveentryId = isset($get_branchCode['0']['branches']['branch_code'])?$get_branchCode['0']['branches']['branch_code']:'';

     //edited by sinsiya 13-06-2025
               $branch_name = $data['branch_name'];
               $branches = $this->Device->query("SELECT  COUNT(*) as count FROM mypayrol_control_db.company_branches WHERE `status` = '1' AND company_code = '" . $company_code . "' AND branch_code = '" . $branch_code . "'");
               if($branches[0][0]['count']== 0){
                              $save_company = $this->Device->query("
                                 INSERT INTO mypayrol_control_db.company_branches (company_code, branch_code, branch_name, status)
                                   VALUES ('$company_code', '$branch_code', '$branch_name', 1)
                                  ");
                  
              }
              //end by sinsiya
                $fin_year = date("Y",strtotime($arr_form_data['finstartdate']));
                $fin_year_start = date("Y-m-d",strtotime($arr_form_data['finstartdate']));
                $fin_year_tend = date("Y-m-d",strtotime($arr_form_data['finenddate']));
                
                $leave_year = date("Y",strtotime($arr_form_data['leavestartdate']));
                $leave_year_start = date("Y-m-d",strtotime($arr_form_data['leavestartdate']));
                $leave_year_tend = date("Y-m-d",strtotime($arr_form_data['leaveenddate']));
                
                $Fin_year_seq_leave = $arr_form_data['Fin_year_seq_leave'];
                $Fin_year_seq = $arr_form_data['Fin_year_seq'];
                if($arr_form_data['id'] == 0 || $arr_form_data['id'] == null || $arr_form_data['id'] == ""){
                    $save_finYear = $this -> Units ->query("INSERT INTO fin_year (company_code,branch_code,fin_year,start_month,end_month,vattr1) VALUES ('$company_code','$branch_code','$fin_year','$fin_year_start','$fin_year_tend','1') ");
                    $save_leaveYear = $this -> Units ->query("INSERT INTO fin_year (company_code,branch_code,fin_year,start_month,end_month,vattr1) VALUES ('$company_code','$branch_code','$leave_year','$leave_year_start','$leave_year_tend','0') ");
                }else{
                    $update_fin_year = $this -> Units ->query("UPDATE fin_year set start_month = '$fin_year_start', end_month = '$fin_year_tend' WHERE branch_code = '$branch_code' and Fin_year_seq = '$Fin_year_seq' ");
                    $update_fin_year = $this -> Units ->query("UPDATE fin_year set start_month = '$leave_year_start', end_month = '$leave_year_tend' WHERE branch_code = '$branch_code' and Fin_year_seq = '$Fin_year_seq_leave' ");
                }
                
                
                
                
                
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Branch saved successfully";

		echo json_encode($resp);
		/*
		 if(!empty($result)){
		 return 'success';
		 }*/

	}

	public function listunits() {

		$this -> autoRender = FALSE;
		$this -> Units -> useDbConfig = $this -> Session -> read('ds');
		//debug($this->Session->read('ds'));
		
		

		$limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];
                //edited by megha on 14/09/2019 latest added branch first
                //$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'branch_name';			  
                //$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
				$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'id';
                $order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
		
		$ofst = ($page - 1) * $limit;
		
		
//		$this -> datatable["conditions"] = array("status" => 1);
		$resp_branches = array();
                $table_joins[] = array(
                    'table' => 'fin_year',
                    'alias' => 'Leave_year',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('Units.branch_code = Leave_year.branch_code','Leave_year.vattr1'=>'0','Leave_year.is_current_finyear'=>'Y','Leave_year.Year_status'=>'OPEN','Leave_year.status'=>1)
                );
                $table_joins[] = array(
                    'table' => 'fin_year',
                    'alias' => 'Fin_year',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('Units.branch_code = Fin_year.branch_code' ,'Fin_year.vattr1'=>'1','Fin_year.is_current_finyear'=>'Y','Fin_year.Year_status'=>'OPEN','Fin_year.status'=>1)
                );
		$resp_branches["rows"]= array();
		$count = $this -> Units -> find("count",array("conditions"=>array('status' => 1)));
		$arr_branches = $this -> Units -> find("all",array(
                    "fields"=>array("Units.*,Leave_year.start_month as LeaveStart,Leave_year.end_month as LeaveEnd,Fin_year.start_month FinStart,Fin_year.end_month FinEnd "),
                    'joins'=>$table_joins,
                    "conditions"=>array(
                        'Units.status' => 1
                    ),
					'group' => 'Units.branch_code',  
                    'order'=>array($sort=>$order),
                    'limit'=>intval($limit),
                    'offset'=>$ofst
                    )
                );
//                $sql = "SELECT `Units`.`id`, `Units`.`company_code`, `Units`.`branch_code`, `Units`.`branch_name`, `Units`.`address`, `Units`.`city`, `Units`.`state`, `Units`.`pincode`,
// `Units`.`latitude`, `Units`.`longitude`, `Units`.`status`, `Units`.`deleted` FROM `mypayrol_hedgefin`.`branches` AS `Units` 
//  WHERE `status` = 1   ORDER BY `branch_name` asc  LIMIT 10";
//                $arr_branches = $this->Units->query($sql);
//                debug($arr_branches);
//		foreach ($arr_branches as $key => $value) {
//			$resp_branches["rows"][$key] = array_merge($value["Units"],$value["Leave_year"],$value['Fin_year']);
//		}
                foreach ($arr_branches as $key => $value) {
			$resp_branches["rows"][$key] = $value["Units"];
                        $resp_branches["rows"][$key]['LeaveStart'] = date("d-m-Y",strtotime($value["Leave_year"]['LeaveStart']));
			$resp_branches["rows"][$key]['LeaveEnd']  = date("d-m-Y",strtotime($value["Leave_year"]['LeaveEnd']));
			$resp_branches["rows"][$key]['FinStart']  = date("d-m-Y",strtotime($value["Fin_year"]['FinStart']));
                        $resp_branches["rows"][$key]['FinEnd']  = date("d-m-Y",strtotime($value["Fin_year"]['FinEnd']));
		}
		$resp_branches["total"] = $count;
		echo json_encode($resp_branches);
	}

	public function deleteBranches() {
		$this -> autoRender = FALSE;
		$this -> Units -> useDbConfig = $this -> Session -> read('ds');
		$result = array('success' => 0);
		if (isset($_REQUEST["ids"])) {
			$ar_ids = explode(",", $_REQUEST["ids"]);

			//debug($ar_ids);

			$this -> Units -> updateAll(array('Units.status' => 0), array('Units.id' => $ar_ids));

			$result['success'] = true;
			$result['msg'] = "Record(s)  deleted successfully.";
		}

		echo json_encode($result);
	}
    public function checkbranchexists($id = 0) {
        $this->autoRender = false;

        $arr_requestdata = $this->request->data;

        $branch_name = isset($arr_requestdata['branch_name']) ? $arr_requestdata['branch_name'] : '';

        $int_branchcount = 0;
        if ($branch_name != '') {
            $this->Units->useDbConfig = $this->Session->read('ds');
            $int_branchcount = $this->Units->find("count", array(
                //'conditions' => array('Units.branch_name' => $branch_name, 'Units.status' => 1, 'Units.id != ' . $id)
				//edited by athira on 19-02-2025
                'conditions' => array('Units.branch_name' => $branch_name, 'Units.status' => 1, "Units.id != '$id'")
				//end  
                    )
            );
        }
        echo $int_branchcount;
    }

}
