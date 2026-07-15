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
class HolidayCalendarController extends AppController {

public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'HolidayCalendar';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials', 'EmployeeProfessionalDetails', 'Holiday','HolidayGroup','Units');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{//$this->layout = FALSE;
		
		$this->Holiday->useDbConfig = $this->Session->read('ds');

        $plan=$this->Holiday->query('SELECT plan FROM comp_contact_info');
        $plan=isset($plan['0']['comp_contact_info']['plan'])?$plan['0']['comp_contact_info']['plan']:'';
        $this->set('plan',$plan);
//debug(	$this->CompanyContactInfo->find("all"));
                $array_date = $this->Holiday->query("select distinct year(HOLIDAYDATE) from holidays where status = '1' ");
                $this->set("array_date",$array_date);
              //  debug($array_date);
		
		
	}
	
	
	public function form(){
		
		$data['id'] = 0;
		$data['HOLIDAYID'] = 0;
		$data['HOLIDAYNAME'] = "";
		$data['HOLIDAYDATE'] = "";
		$data['DESCRIPTION'] = "";
			$data["HOLIDAY_GROUP_ID"] = (isset($_REQUEST["HOLIDAY_GROUP_ID"]) ? $_REQUEST["HOLIDAY_GROUP_ID"] : 0);
		$data['HOLIDAYTYPE'] =  "";
		
		$data['status'] = "";
		
		$this->Holiday->useDbConfig = $this->Session->read('ds');
		
		if(isset($_REQUEST['HOLIDAYID']) && $_REQUEST['HOLIDAYID'] != 0){
			//$data_db = $this->Holiday->find("first",array("fields"=>array('HOLIDAY_GROUP_ID','HOLIDAYNAME','DESCRIPTION','HOLIDAYTYPE'," date_format(HOLIDAYDATE ,'%d/%m/%Y') AS HOLIDAYDATE ","HOLIDAYID","HOLIDAYTYPE"),"conditions"=>array("HOLIDAYID"=>$_REQUEST['HOLIDAYID'])));
			$data_db = $this->Holiday->find("first",array("fields"=>array('HOLIDAY_GROUP_ID','HOLIDAYNAME','DESCRIPTION','HOLIDAYTYPE',"HOLIDAYDATE","HOLIDAYID","HOLIDAYTYPE"),"conditions"=>array("HOLIDAYID"=>$_REQUEST['HOLIDAYID'])));
			$data = $data_db['Holiday'];			
			//$data['HOLIDAYDATE'] = $data_db["0"]['HOLIDAYDATE'];
		}


		$type = array();

		$type[0]['id'] = "OPTIONAL";
		$type[0]['label'] = "Optional";

		$type[1]['id'] = "MANDATORY";
		$type[1]['label'] = "Mandatory";

		$this -> set("types", $type);
		$this -> set("data", $data);
		$this->layout = null;
		
		
	}
	public function filterjson($id =''){			 
		$this->autoRender = false;
		$this->Holiday->useDbConfig = $this->Session->read('ds');
		$arr_form_data = $this->request->data;
                //added by amal orderby condition
                $array_date = $this->Holiday->query("select distinct year(HOLIDAYDATE) from holidays where status = '1' order by HOLIDAYID desc");
                $array = array();
                $branch = array();
                $branch[] = array("id" => "0", "text" => "ALL");
                foreach ($array_date as $key => $value) {
                $branch[] = array(
                'id' => $value['0']['year(HOLIDAYDATE)'],
                'text' => $value['0']['year(HOLIDAYDATE)']
                );
                }  
        $array['items'] = $branch;
        echo json_encode($array);

	}
	public function groupform(){
		$data['HOLIDAY_GROUP_ID']   =  0;
		$data['HOLIDAY_GROUP_NAME'] =  "";
		$this->HolidayGroup->useDbConfig = $this->Session->read('ds');
		//$holidaygroup = $this->HolidayGroup->find("list",array(   'fields' => array('HolidayGroup.HOLIDAY_GROUP_ID','HolidayGroup.HOLIDAY_GROUP_NAME')));
		//debug($holidaygroup);
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){	
			$data_db = $this->HolidayGroup->find("first",array("conditions"=>array("HOLIDAY_GROUP_ID"=>$_REQUEST['id'])));
					$data = $data_db['HolidayGroup'];
		}
		$this -> set("data", $data);
		$this->layout = null;	
	}
	public function listholidaygroup()
	{
			$holidaygroup = $this->HolidayGroup->find("list",array(   'fields' => array('HolidayGroup.HOLIDAY_GROUP_ID','HolidayGroup.HOLIDAY_GROUP_NAME'),'conditions'=>array("status = 1")));
			
			//debug($holidaygroup);
			$arr_holiday  = array( );
			$i = 0;
			foreach ($holidaygroup as $key => $value) {
				
				$arr_holiday[$i]["key"] = $key;
				$arr_holiday[$i]["value"] = $value;
				$i++;
				//$value[] = 
			}
			$respdata = array('success' => true, "group" => $arr_holiday);
			echo	json_encode($respdata);
			$this->autoRender = false;
			$this->layout = null;
	
	}
	
	public function listholidaygroupforconfig()
	{
                        $this->HolidayGroup->useDbConfig = $this->Session->read('ds');
		$holidaygroup = $this->HolidayGroup->find("list",array(   'fields' => array('HolidayGroup.HOLIDAY_GROUP_ID','HolidayGroup.HOLIDAY_GROUP_NAME'),
           "conditions" =>array("status"=>1)));
			//debug($holidaygroup);
			$arr_holiday  = array( );
			
			
				$arr_holiday["rows"] = array( );
		foreach ($holidaygroup as $key => $value) {
			
			
				$data['id']   =$key;
			$data['data'] =  array($value);
			$arr_holiday["rows"][] = $data;
		}
		
		echo json_encode($arr_holiday);
	
			$this->autoRender = false;
			$this->layout = null;
	
	}
	public function saveholiday(){
		
		$this->autoRender = FALSE;
		$this->layout = null;		
		$this->Holiday->useDbConfig = $this->Session->read('ds');		
		$arr_form_data	=	$this->request->data;		
		$data = array();		
	//	$dt = DateTime::createFromFormat('m/d/Y', $arr_form_data['HOLIDAYDATE']);
		$d=strtotime( $arr_form_data['HOLIDAYDATE']);

		//$data['id'] = 0;
		$data['HOLIDAYID']   =  $arr_form_data['HOLIDAYID'];
		$data['HOLIDAYNAME'] =  $arr_form_data['HOLIDAYNAME'];
		$data['HOLIDAYDATE'] =  date("Y-m-d",$d);
              
		$data['DESCRIPTION'] =  $arr_form_data['DESCRIPTION'];
		$data['HOLIDAY_GROUP_ID'] =  $arr_form_data['HOLIDAY_GROUP_ID'];
		$data['HOLIDAYTYPE'] =  $arr_form_data['HOLIDAYTYPE'];
		$holidaygrpid = $data['HOLIDAY_GROUP_ID'];
                $holidaydate = $data['HOLIDAYDATE'];
                $conditions = "";
                if(isset($arr_form_data['HOLIDAYID'])){
                    $holiday_id = $arr_form_data['HOLIDAYID'];
                    $conditions = " and HOLIDAYID != '$holiday_id' ";
                }
		$arr_holidaydate = $this->Holiday->query(" select HOLIDAYDATE from holidays where holidays.HOLIDAY_GROUP_ID = '$holidaygrpid' and holidays.HOLIDAYDATE = '$holidaydate' $conditions and status = '1' " );
//              echo  count($arr_holidaydate);
                if($data['HOLIDAYTYPE'] == '[--Select--]'){
			$resp = array();
			$resp["success"] = false;
			$resp["msg"] = "Select holiday type.";
			echo json_encode($resp);
		}
                elseif(count($arr_holidaydate) > 0)
                {
                $resp = array();
		$resp["success"] = false;
		$resp["msg"] = "Holiday date exists";
		
		echo json_encode($resp); 
                }
               else{
	
		$result	=	$this->Holiday->save($data);
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Holiday Saved Successfully";
		
		echo json_encode($resp); 
               }
		/*
		if(!empty($result)){
					return 'success';
				}*/
		
	}
	
	
	public function delete(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->Holiday->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		$ar_id = explode(",", $_REQUEST['ids']) ;
		$data = array();
		
	//	$dt = DateTime::createFromFormat('m/d/Y', $arr_form_data['HOLIDAYDATE']);
		
		//$data['id'] = 0;
		
		foreach ($ar_id as $key => $value) {
			$data['HOLIDAYID']   = $value;
			$data['status']    = 0;
		
			$this->Holiday->save($data);
		}
					
	
		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Holiday  deleted successfully";
		echo json_encode($resp); 
		
	}
	public function deletegroup(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->HolidayGroup->useDbConfig = $this->Session->read('ds');
                $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		$ar_id = explode(",", $_REQUEST['ids']) ;
		$data = array();
		
	//	$dt = DateTime::createFromFormat('m/d/Y', $arr_form_data['HOLIDAYDATE']);
		
		//$data['id'] = 0;
		//$asiigned = $this->EmployeeProfessionalDetails->find("count",array("conditions"=>array('EmployeeProfessionalDetails.HOLIDAY_GROUP_ID' => $ar_id)));
                //edited by megha on 27/12/2019 Holiday .Remove active employees only
                 $joins = array(
                        array(
                            'table' => 'emp_details',
                            'alias' => 'EmployeeDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                        )
                    );
		$asiigned = $this->EmployeeProfessionalDetails->find("count",array('joins' => $joins,'conditions'=>array('EmployeeProfessionalDetails.HOLIDAY_GROUP_ID' => $ar_id , 'EmployeeDetails.status' => 1)));
                if($asiigned == 0)
                {
		foreach ($ar_id as $key => $value) {
			$data['HOLIDAY_GROUP_ID']   = $value;
			$data['status']    = 0;
		
			$this->HolidayGroup->save($data);
		}
					
                $this->EmployeeProfessionalDetails->updateAll(array('EmployeeProfessionalDetails.HOLIDAY_GROUP_ID' => NULL), array('EmployeeProfessionalDetails.HOLIDAY_GROUP_ID' => $ar_id));
		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Holiday group deleted successfully";
                }
                else
                {
                $resp = array();
		$resp["success"] = FALSE;
		$resp["msg"] = "Holiday group cannot be deleted, remove employees under this group"; 
                }
		echo json_encode($resp); 
		
	}
	public function saveholidaygroup(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->HolidayGroup->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		
		$data = array();
		
	//	$dt = DateTime::createFromFormat('m/d/Y', $arr_form_data['HOLIDAYDATE']);
		
		//$data['id'] = 0;
		$data['HOLIDAY_GROUP_ID']   =  $arr_form_data['HOLIDAY_GROUP_ID'];
		$data['HOLIDAY_GROUP_NAME'] =  $arr_form_data['HOLIDAY_GROUP_NAME'];
		$data['status'] =  1;
		
		
		
		//$data['status']    = $arr_form_data['status'];
	
		$result	=	$this->HolidayGroup->save($data);
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		$resp["msg"] = "Holiday Group Saved Successfully";
		
		echo json_encode($resp); 
		/*
		if(!empty($result)){
					return 'success';
				}*/
		
	}
	
	
	public function listholidays(){
        $resp_holidays = array();
		$resp_holidays['rows'] = array();
		$this -> autoRender = FALSE;
		$this -> Holiday -> useDbConfig = $this -> Session -> read('ds');
		//debug($this->Session->read('ds'));	
		$count = 0;
		if (isset($_REQUEST["group"])) {
			$group = $_REQUEST["group"];
		$conditions['HOLIDAY_GROUP_ID'] = $group;
                
                if (isset($_REQUEST["d"])) {
                    $d=$_REQUEST["d"];
                    if($d != '')
                    $conditions['year(HOLIDAYDATE)']= $d;
                }
                
		$conditions['status'] = 1;
		
		$limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];
		
                $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'HOLIDAYDATE';
                $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
                
		$ofst = ($page-1)*$limit;
		
		
		
			$count = $this -> Holiday -> find("count",array("conditions"=>$conditions));
		
		//$arr_holidays = $this -> Holiday -> find("all",array("fields"=>array('HOLIDAYNAME','DESCRIPTION','HOLIDAYTYPE'," date_format(HOLIDAYDATE ,'%d/%m/%Y') AS HOLIDAYDATE ","HOLIDAYID","HOLIDAYTYPE"),"conditions"=>$conditions,'order'=>array($sort=>$order),'limit'=>intval($limit),'offset'=>intval($ofst)));
		$arr_holidays = $this -> Holiday -> find("all",array("fields"=>array('HOLIDAYNAME','DESCRIPTION','HOLIDAYTYPE',"HOLIDAYDATE","HOLIDAYID","HOLIDAYTYPE"),"conditions"=>$conditions,'order'=>array($sort=>$order),'limit'=>intval($limit),'offset'=>intval($ofst)));
		
		foreach ($arr_holidays as $key => $value) {
				$resp_holidays["rows"][$key] = $value["Holiday"];
				$resp_holidays["rows"][$key]['HOLIDAYDATE'] = date("d-m-Y",strtotime($value["Holiday"]['HOLIDAYDATE']));
			
		}
		}
		$resp_holidays["total"] = $count;
		
		echo json_encode($resp_holidays);
       
	}
	public function listholidaygroupforgrid(){
        

		$this -> autoRender = FALSE;
		$this -> HolidayGroup -> useDbConfig = $this -> Session -> read('ds');
		//debug($this->Session->read('ds'));
		$conditions['status'] = 1;
	
		$count = $this -> HolidayGroup -> find("count",array("conditions"=>$conditions));
		$limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];
		
                $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'HOLIDAY_GROUP_ID';
                $order = isset($_POST['order']) ? strval($_POST['order']) : 'desc';
                        
		$ofst = ($page-1)*$limit;
		
		
		
		$arr_holidaygrps = $this -> HolidayGroup -> find("all",array("conditions"=>$conditions,'order'=>array($sort=>$order),'limit'=>intval($limit),'offset'=>$ofst));
		
		$resp_holidaygroup["rows"] = array();
		foreach ($arr_holidaygrps as $key => $value) {
			$resp_holidaygroup["rows"][$key] = $value["HolidayGroup"];
			//$resp_holidaygroup["holidays"][$key]["BRANCH_NAME"] = "";//$value["Units"]['branch_name'];
		}

		$resp_holidaygroup["total"] = $count;
		
		
		echo json_encode($resp_holidaygroup);
       
	}
   public function checkholidaydateexists($HOLIDAYID = 0,$codecount = '',$HOLIDAY_GROUP_ID = 0){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
//        debug($arr_requestdata);
        $HOLIDAYDATE = isset($arr_requestdata['HOLIDAYDATE'])?$arr_requestdata['HOLIDAYDATE']:$codecount;
        
        if($HOLIDAY_GROUP_ID !='0'){
        $itemcheck = "Holiday.HOLIDAY_GROUP_ID != '$HOLIDAY_GROUP_ID'";
        }else
        {
             $itemcheck = '';
        }
        $int_typecount = 0;
        if($HOLIDAYDATE != ''){
            $this->Holiday->useDbConfig = $this->Session->read('ds');
            $int_typecount = $this->Holiday->find("count",array(
                    'conditions' => array('Holiday.HOLIDAYDATE' => $HOLIDAYDATE,'Holiday.status' => '1', 'Holiday.HOLIDAY_GROUP_ID '=> $HOLIDAYID,$itemcheck)
                )
            );
        }
        return $int_typecount;
    }
}
