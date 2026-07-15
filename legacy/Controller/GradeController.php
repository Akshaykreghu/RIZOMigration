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
class GradeController extends AppController {


public $datatable = array();
/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Grade';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('UserCredentials','CompanyContactInfo','Grades','Holiday');
	public $components = array('DatatablesManagement');
	
	/*
	 * Dashboard landing view
	 */
	public function index()
	{
		$this->layout = FALSE;
		
		$this->Grades->useDbConfig = $this->Session->read('ds');
		
	
		
	}
	
	public function Hoildaycalender($group_id = 0)
        {
            $this->set("group_id",$group_id);
        }
        public function eventsCalender($group_id = 0)
        {
            $this->autoRender = false;
            $this -> Holiday -> useDbConfig = $this -> Session -> read('ds');
            $conditions['status'] = 1;
            $conditions['HOLIDAY_GROUP_ID'] = $group_id;
            $arr_holidays = $this -> Holiday -> find("all",array("fields"=>array('HOLIDAYNAME','Background','border',"HOLIDAYDATE","HOLIDAYID","HOLIDAYTYPE"),"conditions"=>$conditions));
            $holidays = array();
            $data = array();
            foreach ($arr_holidays as $val)
            {
                $holidays['HOLIDAYID'] = $val['Holiday']['HOLIDAYID'];
                $holidays['title'] = $val['Holiday']['HOLIDAYNAME'];
                $holidays['backgroundColor'] = $val['Holiday']['Background'];
                $holidays['start'] = $val['Holiday']['HOLIDAYDATE'];
                $holidays['description'] = 'This is a cool';
                $holidays['borderColor'] = $val['Holiday']['border'];
                $data[] = $holidays;
            }
            echo json_encode($data);
        }
        public function eventsCalenderdate($group_id = 0)
        {
            $this->autoRender = false;
            $this -> Holiday -> useDbConfig = $this -> Session -> read('ds');
            $conditions['status'] = 1;
            $conditions['HOLIDAY_GROUP_ID'] = $group_id;
            $arr_holidays = $this -> Holiday -> find("all",array("fields"=>array('HOLIDAYNAME','Background','border',"HOLIDAYDATE","HOLIDAYID","HOLIDAYTYPE"),"conditions"=>$conditions));
            $holidays = array();
            $data = array();
            foreach ($arr_holidays as $val)
            {
                $holidays['HOLIDAYID'] = $val['Holiday']['HOLIDAYID'];
                $holidays['title'] = $val['Holiday']['HOLIDAYNAME'];
                $holidays['backgroundColor'] = $val['Holiday']['Background'];
                $holidays['start'] = $val['Holiday']['HOLIDAYDATE'];
                $holidays['description'] = 'This is a cool';
                $holidays['borderColor'] = $val['Holiday']['border'];
                $data[] = $holidays;
            }
            echo json_encode($data);
        }
	public function insert()
        {
            $this->autoRender = false;
            $arr_form_data = $this->request->data;
            $data = array();
            $data['HOLIDAY_GROUP_ID'] = $arr_form_data['group'];
            $data['HOLIDAYNAME'] = $arr_form_data['Text'];
            $data['HOLIDAYDATE'] = $arr_form_data['date'];
            $data['Background'] = $arr_form_data['Background'];
            $data['border'] = $arr_form_data['Border'];
            $this -> Holiday -> useDbConfig = $this -> Session -> read('ds');
            $this->Holiday->save($data);
        }
        public function delete()
        {
            $this->autoRender = false;
            $arr_form_data = $this->request->data;
            $data_holiday_id = $arr_form_data['holiday'];
            $this -> Holiday -> useDbConfig = $this -> Session -> read('ds');
            $this->Holiday->query("update holidays set status = '0' where HOLIDAYID = '$data_holiday_id' ");
        }
        
        
        public function holidayreport($mode = "") {
        
    }
    public function Getholidays()
    {
        $this->Holiday->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $sessionid = $this->Session->read('emp_fkey');
        $arr_holiday = $this->Holiday->query("SELECT HOLIDAYNAME, HOLIDAYDATE, Background, border,HOLIDAYID  FROM holidays
                       where HOLIDAY_GROUP_ID in (select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$sessionid)");
        //debug($_SERVER['HTTP_HOST']);
        $this->set('arr_holiday', $arr_holiday);
        $holidays = array();
            $data = array();
            foreach ($arr_holiday as $val)
            {
                $holidays['HOLIDAYID'] = $val['holidays']['HOLIDAYID'];
                $holidays['title'] = $val['holidays']['HOLIDAYNAME'];
                $holidays['backgroundColor'] = $val['holidays']['Background'];
                $holidays['start'] = $val['holidays']['HOLIDAYDATE'];
                $holidays['borderColor'] = $val['holidays']['border'];
                $data[] = $holidays;
            }
            echo json_encode($data);
    }
    
    public function Getholidayss()
    {
        $this->Holiday->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $start = $_REQUEST['start'];
        $year_month = date("Y-m-1",strtotime($start));
        $sessionid = $this->Session->read('emp_fkey');
        $arr_holiday = $this->Holiday->query("SELECT * from emp_detail_timeattandance where emp_pkey = '14' and yearmonth = '$year_month'  ");
        //debug($_SERVER['HTTP_HOST']);
        $this->set('arr_holiday', $arr_holiday);
        $holidays = array();
            $data = array();
            foreach ($arr_holiday as $val)
            {
                $holidays['HOLIDAYID'] = $val['emp_detail_timeattandance']['emp_pkey'];
                $holidays['title'] = $val['emp_detail_timeattandance']['present'];
                $holidays['backgroundColor'] = 'green';
                $holidays['start'] = $val['emp_detail_timeattandance']['att_date'];
                $holidays['borderColor'] = 'geen';
                $data[] = $holidays;
            }
            echo json_encode($data);
    }

    public function newGrade(){
		
		$data['id'] = 0;
		$data['dept_code'] = "";
		$data['dept_name'] = "";
		$data['status'] = "";
		
				
		$this->Grades->useDbConfig = $this->Session->read('ds');
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
	$data_db = $this->Grades->find("first",array("conditions"=>array("id"=>$_REQUEST['id'])));
		//	debug($data);
			$data = $data_db['Grades'];
		}

		$this->set(compact("data"));
		$this->layout = null;
		
		
	}
		public function saveGrade(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->Grades->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		
		
		$data = array();
		
		$data['id']        = $arr_form_data['id'];
		$data['dept_code'] = $arr_form_data['dept_code'];
		$data['dept_name'] = $arr_form_data['dept_name'];
		//$data['status']    = $arr_form_data['status'];
	
		$result	=	$this->Grades->save($data);
		//debug($result);
		$resp = array();
		$resp["success"] = true;
		
		echo json_encode($resp); 
		/*
		if(!empty($result)){
					return 'success';
				}*/
		
	}
	public function listGrades(){
       
		$this->datatable["conditions"] = array("status"=> 1);
		
		$this->Grades->useDbConfig = $this->Session->read('ds');
		$resp_banks = array();
		$this->Grades->useDbConfig = $this->Session->read('ds');
		$arr_banks =  $this -> Grades ->find("all");
		foreach ($arr_banks as $key => $value) {
			$resp_banks["grades"][$key] = $value["Grades"];
		}
		echo json_encode($resp_banks);
    	$this->autoRender=FALSE;
		
        
	}
	
	public function deleteGrade(){
			$this->autoRender=FALSE;
			$this->Grades->useDbConfig = $this->Session->read('ds');
			$result =   array('success' => 0 );
			if(isset($_REQUEST["ids"]))
			{
				$ar_ids = explode(",", $_REQUEST["ids"]);
			//	debug($ar_ids);
				$this->Grades->updateAll(
				    array('Grades.status' => 0),
				    array('Grades.id' => $ar_ids)
				);
				$result['success'] = 1;
			}
			
		echo json_encode($result);
	}
}
