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
ini_set("display_errors", 0);

App::uses('ConnectionManager', 'Model');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class DayTimeProcedureController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'DayTimeProcedure';

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('DayTimeProcedures','EmployeeProfessionalDetails');
	public function index() {
		$this -> DayTimeProcedures -> useDbConfig = $this -> Session -> read('ds');
		//debug(	$this->CompanyContactInfo->find("all"));

	}
	
        public function lists(){
            $this->layout = null;
            $proc_id = isset($_REQUEST['id'])?$_REQUEST['id']:0;
            $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
            $respdata = array();

            if($proc_id)
            {
                    $dayTimeProcedure  = $this->DayTimeProcedures->find("first",array("conditions" =>array("day_time_seq" =>$proc_id,"active" =>"1")));
            //	debug($dayTimeProcedure);
                    $respdata = $dayTimeProcedure['DayTimeProcedures'];
            }
            $components = $this->DayTimeProcedures->query("SELECT * FROM `salary_head_items` left join salary_heads on (salary_heads.head_pkey = salary_head_items.head_fkey) WHERE head_occurance = 'VARIABLE' and lcase(value) = 'y'");
                    $this->set("data",$respdata);
                    $this->set("components",$components);
        }

        public function listpolicies(){
      
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$conditions['active'] =1;
			$limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];
		
                $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'day_time_desc';
                $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
                
		$ofst = ($page-1)*$limit;
		
		$count = $this -> DayTimeProcedures -> find("count",array("conditions"=>$conditions));
		$policies = $this->DayTimeProcedures->find("all",array("conditions"=>$conditions,'order'=>array($sort=>$order),'limit'=>intval($limit),'offset'=>intval($ofst)));
		
		
		$arr_policies  = array( );
			$arr_policies["rows"] = array( );
		foreach ($policies as $key => $value) {
			$arr_policies["rows"][$key] = $value["DayTimeProcedures"];
			
		}
		
		
		$arr_policies["total"] = $count;
		
		
		echo json_encode($arr_policies);
			
		
    	$this->autoRender=FALSE;
		
       
	}
	
	public function listpoliciesforconfig(){
      
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$conditions['active'] =1;
		//$count = $this -> DayTimeProcedures -> find("count",array("conditions"=>$conditions));
		$policies = $this->DayTimeProcedures->find("all",array("conditions"=>$conditions));
		
		
		$arr_policies  = array( );
		$arr_policies["rows"] = array( );
		foreach ($policies as $key => $value) {
			$policy['id']   = $value["DayTimeProcedures"]['day_time_seq'];
			$policy['data'] =  array( $value["DayTimeProcedures"]['day_time_desc']);
			$arr_policies["rows"][] = $policy;
			
		}
		
		

		
		echo json_encode($arr_policies);
			
		
    	$this->autoRender=FALSE;
		
       
	}
    public function saveDayTimeProcedure()
   {
        $this->autoRender = FALSE;
		$this->layout = null;
		
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		
		$arr_form_data	=	$this->request->data;
		$data  = array();
		 if($this->checkshiftpolicyexists($arr_form_data['day_time_desc'],$arr_form_data['day_time_seq']) > 0){
                   return json_encode(array("success"=>false,"msg"=>"Shift Policy Exists ")); 
                } 
                
		$data['day_time_seq']  = ($arr_form_data['day_time_seq'])?$arr_form_data['day_time_seq']:0;
		$data['day_time_desc'] = $arr_form_data['day_time_desc'];
		$data['Monday'] 	   = (isset($arr_form_data['Monday']))?"Y":"N";
		$data['Tuesday'] 	   = (isset($arr_form_data['Tuesday']))?"Y":"N";
		$data['Wednesday']     = (isset($arr_form_data['Wednesday']))?"Y":"N";
		$data['Thursday'] 	   = (isset($arr_form_data['Thursday']))?"Y":"N";
		$data['Friday'] 	   = (isset($arr_form_data['Friday']))?"Y":"N";
		$data['Saturday']      = (isset($arr_form_data['Saturday']))?"Y":"N";
                $data['strict_monitorings'] 	   = (isset($arr_form_data['someSwitchOption001'])) && $arr_form_data['someSwitchOption001']=="Y" ? 'N':'Y';
		$data['Sunday']        = (isset($arr_form_data['Sunday']))?"Y":"N";
                $data['Monday_F'] 	   = (isset($arr_form_data['Monday_F']))?"Y":"N";
		$data['Tuesday_F'] 	   = (isset($arr_form_data['Tuesday_F']))?"Y":"N";
		$data['Wednesday_F']     = (isset($arr_form_data['Wednesday_F']))?"Y":"N";
		$data['Thursday_F'] 	   = (isset($arr_form_data['Thursday_F']))?"Y":"N";
		$data['Friday_F'] 	   = (isset($arr_form_data['Friday_F']))?"Y":"N";
		$data['Saturday_F']      = (isset($arr_form_data['Saturday_F']))?"Y":"N";
		$data['Sunday_F']        = (isset($arr_form_data['Sunday_F']))?"Y":"N";
		$data['isnextday']     =isset($arr_form_data['nextday'])?"1":"0";
		$data['on_dutty1']     = $arr_form_data['on_dutty1'];
		$data['off_dutty1']    = $arr_form_data['off_dutty1'];
		$data['working_time1'] = $arr_form_data['working_time1'];
                $data['working_time4'] = $arr_form_data['working_time4'];
		$data['on_dutty2']     =  isset($arr_form_data['on_dutty2'])?$arr_form_data['on_dutty2']:"";
		$data['off_dutty2']    = isset($arr_form_data['off_dutty2'])?$arr_form_data['off_dutty2']:"";
                $data['on_dutty4']     = isset($arr_form_data['in_duty4'])?$arr_form_data['in_duty4']:"";
                $data['off_dutty4']    = isset($arr_form_data['out_duty4'])?$arr_form_data['out_duty4']:"";
                if(isset($arr_form_data['weekday'])){
                    $data[$arr_form_data['weekday'].'_F'] = isset($arr_form_data['off_occures'])?$arr_form_data['off_occures']:'';
                }
                $data['is_exception'] = isset($arr_form_data['is_exception'])?$arr_form_data['is_exception']:'0';
                $data['working_time2'] = isset($arr_form_data['working_time2'])?$arr_form_data['working_time2']:""; 
		$data['minuts_calc_perday']  = $arr_form_data['minuts_calc_perday'];
                $data['minutes_per_half']  = $arr_form_data['minutes_per_half'];
		$data['minuts_aftr_on_dutty_cal_late'] = $arr_form_data['minuts_aftr_on_dutty_cal_late'];
		$data['minuts_bfr_off_dutty_cal_early'] = $arr_form_data['minuts_bfr_off_dutty_cal_early'];
		$data['min_cal_late_ifnoclockin'] = (isset($arr_form_data['min_cal_late_ifnoclockin']))?$arr_form_data['min_cal_late_ifnoclockin']:0;
		$data['min_cal_leave_early_ifnoclockout'] = (isset($arr_form_data['min_cal_leave_early_ifnoclockout']))?$arr_form_data['min_cal_leave_early_ifnoclockout']:0;
		$data['min_aftr_off_dutty_cal_ot'] = (isset($arr_form_data['min_aftr_off_dutty_cal_ot']))?$arr_form_data['min_aftr_off_dutty_cal_ot']:0;
		$data['min_bfr_on_dutty_cal_ot'] = (isset($arr_form_data['min_bfr_on_dutty_cal_ot']))?$arr_form_data['min_bfr_on_dutty_cal_ot']:0;
		$data['work_time_day_off_cal_ot'] = (isset($arr_form_data['work_time_day_off_cal_ot']))?$arr_form_data['work_time_day_off_cal_ot']:0;
		$data['day_time_proc_active'] = "Y";
                $data['shift_allowance'] = isset($arr_form_data['shift_allowance'])?$arr_form_data['shift_allowance']:"";
                $data['otcomponents'] = isset($arr_form_data['Otc'])?$arr_form_data['Otc']:"";
                $data['is_multiple_days'] = isset($arr_form_data['enablemutishift'])?$arr_form_data['enablemutishift']:"N";
                $data['no_of_shift_days'] = isset($arr_form_data['multishift'])?$arr_form_data['multishift']:"0";
                $this->DayTimeProcedures->save($data);
		$day_time_proc_id = $this->DayTimeProcedures->getLastInsertId();
		$resp = array();
		$resp["success"] = true;
		$resp['day_time_seq'] = ($arr_form_data['day_time_seq'])?$arr_form_data['day_time_seq']:$day_time_proc_id;
		$resp['msg'] = "Daytime procedure saved successfully";
	
		echo json_encode($resp); 
   }	


	public function delete(){
		
		$this->autoRender = FALSE;
		$this->layout = null;
		
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                
		$arr_form_data	=	$this->request->data;
			
	
		$ar_id = explode(",", $_REQUEST['ids']) ;
		$data = array();
		
	//	$dt = DateTime::createFromFormat('m/d/Y', $arr_form_data['HOLIDAYDATE']);
		
		//$data['id'] = 0;
		$joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
		$asiigned = $this->EmployeeProfessionalDetails->find("count",array("joins"=>$joins,"conditions"=>array('EmployeeProfessionalDetails.day_time_seq' => $ar_id,'EmployeeDetails.status'=>1)));
            	$resp = array();
		if($asiigned > 0 )
                {
                    $resp["success"] = false;
                    $resp["msg"] = "Shift policy cannot be deleted, remove employees under this shift";
                }
                else
                {
                    $this->DayTimeProcedures->updateAll(array('DayTimeProcedures.active' => 0), array('DayTimeProcedures.day_time_seq' => $ar_id));
                    $resp["success"] = true;
                    $resp["msg"] = "Shift policy  deleted successfully";
                }
		echo json_encode($resp); 
		
	}
   public function form()
   {
       // $this->autoRender = FALSE;
		$this->layout = null;
		$proc_id = isset($_REQUEST['id'])?$_REQUEST['id']:0;
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$respdata = array();
		
		if($proc_id)
		{
			$dayTimeProcedure  = $this->DayTimeProcedures->find("first",array("conditions" =>array("day_time_seq" =>$proc_id,"active" =>"1")));
		//	debug($dayTimeProcedure);
			$respdata = $dayTimeProcedure['DayTimeProcedures'];
		}
                $components = $this->DayTimeProcedures->query("SELECT * FROM `salary_head_items` left join salary_heads on (salary_heads.head_pkey = salary_head_items.head_fkey) WHERE head_occurance = 'VARIABLE' and lcase(value) = 'y'");
			$this->set("data",$respdata);
                        $this->set("components",$components);
		//	die;
   }
   public function checkshiftpolicyexists($codecount = '',$day_time_seq=0){
        $this->autoRender = false;
        
        $arr_requestdata = $this->request->data;
        
        $day_time_desc = isset($arr_requestdata['day_time_desc'])?$arr_requestdata['day_time_desc']:$codecount;
        
        $int_shiftcount = 0;
        if($day_time_desc != ''){
            $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
            $int_shiftcount = $this->DayTimeProcedures->find("count",array(
                    'conditions' => array('DayTimeProcedures.day_time_desc' => $day_time_desc,'DayTimeProcedures.active' => 1, "DayTimeProcedures.day_time_seq != '$day_time_seq'")
                )
            );
        }
        return $int_shiftcount;
    }

}
