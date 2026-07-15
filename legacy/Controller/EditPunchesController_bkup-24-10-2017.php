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
App::uses('CakeEmail', 'Network/Email');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class EditPunchesController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'EditPunches';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'EditPunches', 'EmployeeDetails', 'DbConfig');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index($emp_id='') {
        $this->layout = null;
        
        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months',$arr_months);
        
        $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployees/"));
        $this->set('arr_employees',$arr_employees);
		
		if(!empty($emp_id)){
			$this->set('emp_id',$emp_id);
		}
    }
    public function hierarchy(){
        $this->layout = null;
        
        $arr_months = json_decode($this->getmonths());
        $this->set('arr_months',$arr_months);
        
        $arr_employees = json_decode($this->requestAction("/ApiRequest/listemployeesforhierarchy/"));
        $this->set('arr_employees',$arr_employees);
        $this->render('index');
    }

    /*public function listpunches() {
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        //debug($this->Session->read('ds'));

        $employee = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
        $month = isset($_REQUEST['month']) ? $_REQUEST['month'] : '';
        $includeinactive = isset($_REQUEST['includeinactive']) ? $_REQUEST['includeinactive'] : '';
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();

        $condition = array();

        if ($includeinactive) {
            if ($includeinactive == 'N') {
                $condition['status'] = array('Y');
            } else {
                $condition['status'] = array('Y', 'N');
            }
        } else {
            $condition['status'] = array('Y');
        }
        if ($employee) {
            $condition['emp_id'] = $employee;
        }
        if ($month) {
            //$condition['MONTH(LOGDATE)'] = $month;
            $condition['DATE_FORMAT(LOGDATE,"%Y-%c")'] = $month;
        }
        $count = 0;
        if ($employee) {
            $count = $this->EditPunches->find("count", array('conditions' => $condition));
            $arr_mispunches = $this->EditPunches->find("all", array('conditions' => $condition, 'order' => array('EditPunches.LOGDATE'), 'limit' => intval($limit), 'offset' => intval($ofst)));
        }
        foreach ($arr_mispunches as $key => $value) {
            $resp_mispunches["rows"][$key] = $value["EditPunches"];
        }
        $resp_mispunches["total"] = $count;
        echo json_encode($resp_mispunches);
    }*/
	
	//New List starts
	public function listpunches() {
        $this->autoRender = FALSE;
		
		$base_table = "emp_detail_timeattandance";
		
		$emp = isset($_REQUEST['emp']) ? $_REQUEST['emp'] : 0;
		if($emp == 0){
			return;
		}
		$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
		$arr_emp_pkey = $this->EmployeeDetails->query("SELECT * FROM `emp_details` WHERE `emp_id` = '$emp'");
		$emp_pkey = isset($arr_emp_pkey[0]['emp_details']['emp_pkey']) ? $arr_emp_pkey[0]['emp_details']['emp_pkey'] : 0;
		$branch_code = isset($arr_emp_pkey['0']['emp_details']['branch_code'])?$arr_emp_pkey['0']['emp_details']['branch_code']:'';	
        $monthdd = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('Y-m');
		
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();
		
		$this->DbConfig->useDbConfig = $this->Session->read('ds');
		$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
		$company_code = $this->Session->read('company_code');
		$arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
		$month = $monthdd;
		$yearmonth = $month . '-01';
		//  debug($yearmonth);
		$attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
		$att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
		$att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
		$arr_date_in_selectedmonth = range(1, $att_enddate);
		if ($att_startdate != 1) {
			$arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
		} else {
			$arr_date_in_prevmonth = array();
		}

		if ($emp_pkey != 0) {
			$condition = "$base_table.emp_pkey = '$emp_pkey' and ";
			$emp = $emp_pkey;
		} else {
			$condition = '';
			$emp = NULL;
		}
		
		$arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
		if ($this->Session->read('emp_fkey')) {
			$emp_pkeys = $this->Session->read('emp_fkey');
			$emp_condition = "emp.attr1 = '$emp_pkeys' and ";
		} else {
			$emp_condition = "";
		}
//                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//		$attend = $this->EmployeeDetails->query("select time_duration_check('$yearmonth','$emp','')");
//		
		if(!$shiftdetailed = $this->EmployeeDetails->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey')")){
                    return FALSE;
                    die();
                }

//debug($shiftdetailed);
                if($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y'){
                    if(!$this->EmployeeDetails->query("SELECT time_duration_check_multishift('$yearmonth', '$emp_pkey', '$branch_code')")){
                    return false;
                    die();
                    }
                }else{
                    if(!$this->EmployeeDetails->query("SELECT time_duration_check('$yearmonth', '$emp_pkey', '$branch_code')")){
                    return false;
                    die();
                    }
                }
		$attendances_count = $this->EmployeeDetails->query("select count(*) AS count from $base_table left join emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) left join emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) where $emp_condition $condition yearmonth = '$yearmonth' order by att_date ");
		$count = isset($attendances_count[0][0]['count'])?$attendances_count[0][0]['count']:0;
		
		$attendances = $this->EmployeeDetails->query("select 
			empdetails.emp_id, 
			empdetails.first_name, 
			empdetails.last_name, 
			$base_table.*, 
			wd.minuts_calc_perday, 
			emp.emp_fkey 
			from 
			$base_table 
				left join 
					emp_details as empdetails on(empdetails.emp_pkey = $base_table.emp_pkey) 
						left join 
							emp_proff as emp on (emp.emp_fkey = empdetails.emp_pkey) 
							left join 
								working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) 
									where $emp_condition $condition yearmonth = '$yearmonth' 
									order by att_date 
									LIMIT $ofst, $limit
		");
		
		$arr_attendance_register = $this->EmployeeDetails->query("select count(*) as count from attendance_register where emp_fkey = $emp_pkey AND month_year = '" . date('Y-m', strtotime($month)) . "' AND isdelete = 'Y'");
		$iseditable = isset($arr_attendance_register[0][0]['count'])?$arr_attendance_register[0][0]['count']:0;
		//debug($iseditable);
		//debug($attendances);
		$employee_attendance = array();
		foreach ($attendances as $val) {
			//$emppk = $val[$base_table]['emp_pkey'];
			//$employee_attendance[$emppk][] = $val;
			$arr_output = array();
			$arr_output['emp_id'] = isset($val['empdetails']['emp_id'])?$val['empdetails']['emp_id']:'';
			$arr_output['att_date'] = isset($val[$base_table]['att_date'])?$val[$base_table]['att_date']:'';
			$arr_output['att_in_time'] = isset($val[$base_table]['att_in_time'])?$val[$base_table]['att_in_time']/*date("h:i:s A", strtotime($val[$base_table]['att_in_time']))*/:'';
			$arr_output['att_out_time'] = isset($val[$base_table]['att_out_time'])?$val[$base_table]['att_out_time']/*date("h:i:s A", strtotime($val[$base_table]['att_out_time']))*/:'';
			$arr_output['duration'] = isset($val[$base_table]['duration'])?$val[$base_table]['duration']:'';
			
			//$arr_output['min_bfr_on_dutty_cal_ot'] = isset($val[$base_table]['min_bfr_on_dutty_cal_ot'])?$val[$base_table]['min_bfr_on_dutty_cal_ot']:'';
			//$arr_output['min_aftr_off_dutty_cal_ot'] = isset($val[$base_table]['min_aftr_off_dutty_cal_ot'])?$val[$base_table]['min_aftr_off_dutty_cal_ot']:'';
			//$arr_output['ot_duration'] = isset($val[$base_table]['ot_duration'])?$val[$base_table]['ot_duration']:'';
			
			$status = '';
			$status_color  = 'black';
			if(!empty($val[$base_table]['weekoff'])){
				//$status = $val[$base_table]['weekoff'];
				$status_color = "black";
			}else if(!empty($val[$base_table]['present'])){
				//$status = $val[$base_table]['present'];
				if(in_array(strtoupper($val[$base_table]['present']), array('A/A','P/A','A/P'))){
					$status_color = 'red';
				}else if(strtoupper($val[$base_table]['present']) == 'P/P'){
					$status_color = 'green';
				}
			}/*else if(!empty($val[$base_table]['holiday'])){
				$status = $val[$base_table]['holiday'];
				$status_color = "blue";
			}else if(!empty($val[$base_table]['leaves'])){
				$status = $val[$base_table]['leaves'];
				$status_color = "black";
			}else if(!empty($val[$base_table]['others'])){
				$status = $val[$base_table]['others'];
				$status_color = "black";
			}else{
				$status = $val[$base_table]['others'];
				$status_color = "black";
			}*/ else{
				$status_color = "black";
			}
			$status = $val[$base_table]['present'].' '.
						$val[$base_table]['holiday'].' '.
						$val[$base_table]['leaves'].' '.
						$val[$base_table]['weekoff'].' '.
						$val[$base_table]['others'];
			
			
			$arr_output['status'] = $status;
			$arr_output['status_color'] = $status_color;
			
			$arr_output['editable'] = !empty($iseditable)?true:false;
			
			$resp_mispunches["rows"][] = $arr_output;
		}
		
        $resp_mispunches["total"] = $count;
        echo json_encode($resp_mispunches);
	}
	
    public function editpunch($att_date='', $emp_id = '', $att_in_time = '', $att_out_time = '') {
        $this->layout = null;
		$this->set('att_date', $att_date);
		$this->set('emp_id', $emp_id);
		$this->set('att_in_time', $att_in_time);
		$this->set('att_out_time', $att_out_time);
    }
	
	public function listpunchesbydate() {
        $this->autoRender = FALSE;

        $employee = isset($_REQUEST['empid']) ? $_REQUEST['empid'] : 0;
        $att_date = isset($_REQUEST['att_date']) ? $_REQUEST['att_date'] : '';
        $att_in_time = isset($_REQUEST['att_in_time']) ? $_REQUEST['att_in_time'] : '';
        $att_out_time = isset($_REQUEST['att_out_time']) ? $_REQUEST['att_out_time'] : '';
        $includeinactive = isset($_REQUEST['includeinactive']) ? $_REQUEST['includeinactive'] : '';
		
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $resp_mispunches = array();
        $resp_mispunches["rows"] = array();

        $condition = array();

        if ($includeinactive) {
            if ($includeinactive == 'N') {
                $condition['status'] = array('Y');
            } else {
                $condition['status'] = array('Y', 'N');
            }
        } else {
            $condition['status'] = array('Y');
        }
        if ($employee) {
            $condition['emp_id'] = $employee;
        }
        if ($att_date) {
            //$condition['MONTH(LOGDATE)'] = $month;
            //$condition['DATE_FORMAT(LOGDATE,"%Y-%m-%d")'] = $att_date;
			if(!empty($att_in_time) && !empty($att_out_time)){
				$condition[] = "DATE_FORMAT(LOGDATE,'%Y-%m-%d') BETWEEN '$att_in_time' AND '$att_out_time'";
			}else{
				$condition['DATE_FORMAT(LOGDATE,"%Y-%m-%d")'] = $att_date;
			}
        }
		
        $count = 0;
        if ($employee) {
			$this->EditPunches->useDbConfig = $this->Session->read('ds');
            $count = $this->EditPunches->find("count", array('conditions' => $condition));
            $arr_mispunches = $this->EditPunches->find("all", array(
									'conditions' => $condition, 
									'order' => array(
										'EditPunches.LOGDATE'), 
										'limit' => intval($limit), 
										'offset' => intval($ofst)
									)
								);
			
			foreach ($arr_mispunches as $key => $value) {
				$resp_mispunches["rows"][$key] = $value["EditPunches"];
			}
        }
        $resp_mispunches["total"] = $count;
        echo json_encode($resp_mispunches);
    }
	//Ends
	
    public function Updateame()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        //debug($arr_data);
        $emp_id = $arr_data['emp'];
        $month = $arr_data['month'].'-01';
        $get_emp = $this->EditPunches->query("select emp_pkey from emp_details where emp_id= '$emp_id' and status = '1' ");
        $emp_pkey = $get_emp['0']['emp_details']['emp_pkey'];
        $branch_code = isset($get_emp['0']['emp_details']['branch_code'])?$get_emp['0']['emp_details']['branch_code']:'NULL';
        $deleterecords = $this->EditPunches->query("delete from  emp_detail_timeattandance where emp_pkey='$emp_pkey' and yearmonth='$month' ");
        if(!$shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )")){
                return FALSE;
                die();
            }
            if($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y'){
                if(!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")){
                return false;
                die();
                }
            }else{
                if(!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")){
                return false;
                die();
                }
            }
    }
    public function Updateamendmens()
    {
        $this->layout = null;
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $branch_code = $arr_data['brn'];
        $month = $arr_data['month'].'-01';
        $get_emp = $this->EditPunches->query("select emp_pkey from emp_details where branch_code= '$branch_code' and status = '1' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq is not null)");
        
        
            //die();
        
        //debug($get_emp);
        foreach ($get_emp as $value){
            //debug($value);
            $emp_pkey = isset($value['emp_details']['emp_pkey'])?$value['emp_details']['emp_pkey']:0;
            $deleterecords = $this->EditPunches->query("delete from  emp_detail_timeattandance where emp_pkey in ('$emp_pkey')  and yearmonth='$month' ");
               
            
            $shiftdetailed = $this->EditPunches->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )");
            if($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'] == 'Y'){
                if(!$this->EditPunches->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')")){
                    continue;    
                //return false;
                //die();
                }
            }else{
                if(!$this->EditPunches->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')")){
                    continue;
                //return false;
                //die();
                }
            }
            
        }
        return true;
    }
    public function form($empid=0,$att_date='') {
        $this->layout = null;
        $this->set("empid", $empid);
        $this->set("att_date", $att_date);
    }

    public function remove() {
        $this->autoRender = FALSE;
        $device_attandance_seq = 0;
        $resp = array('success' => false);
        if (isset($_REQUEST['device_attandance_seq']) && $_REQUEST['device_attandance_seq'] != 0) {
            $device_attandance_seq = $_REQUEST['device_attandance_seq'];

            $data['device_attandance_seq'] = $device_attandance_seq;
            $data['status'] = "D";
            $this->EditPunches->useDbConfig = $this->Session->read('ds');
            $this->EditPunches->save($data);
            $resp = array('success' => true);
        }
        echo json_encode($resp);
    }

    public function savenew() {

        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
       // debug($_SESSION);DIE();
        $resp = array('success' => true);
        $data = array();
        $data["C1"] = $_POST["C1"];
        $data["C3"] = $_POST["C3"];
        
        $logdate = $_POST["LOGDATE"].' '.$_POST["LOGTIME"];
        $d = strtotime($logdate);
        $data["LOGDATE"] = date("Y-m-d H:i:s", $d);
        $data["emp_id"] = $_POST["empid"];
        $data["device_attandance_seq"] = 0;
        $data["DEVICEID"] = 0;
        $data["company_code"] = $this->Session->read('company_code');
        $data["created_by"] = $this->Session->read('login_user_id');
        //$data["company_code"] = $this->Session->read('company_code');
        $br_details = $this->EmployeeDetails->find("first", array("conditions" => array("emp_id" => $_POST["empid"]), "fields" => array("branch_code")));
        $data["branch_code"] = isset($br_details['EmployeeDetails']['branch_code']) ? $br_details['EmployeeDetails']['branch_code'] : "";
        $this->EditPunches->save($data);
        echo json_encode($resp);
        
    }

    public function savepunch() {

        $resp = array('success' => true);
        $this->autoRender = FALSE;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $_POST["modified_by"] = $this->Session->read('login_user_id');
        $this->EditPunches->save($_POST);
        echo json_encode($resp);
        //debug($_POST);
    }
    
    public function getmonths(){
        //$this->autoRender = FALSE;
        $arr_months = array();
        $start_month = strtotime(date('Y-n', strtotime("+1 month", strtotime(date('Y-n')))));
        for ($i = 0; $i < 10; $i++) {
            $month = date('Y-n', strtotime("-$i month", $start_month));
            $arr_months[] = array(
                'id' => $month,
                'text' => $month
            );
        }
        return json_encode($arr_months);
    }
public function sendmemo(){
    
    
    
    $Email = new CakeEmail();
$Email->from(array('sruthi.pb@gmail.com' => 'My Site'));
$Email->to('sruthiforsight@gmail.com');
$Email->subject('About');
$Email->send('My message');
//debug($Email);
echo "sucess";
}
}
