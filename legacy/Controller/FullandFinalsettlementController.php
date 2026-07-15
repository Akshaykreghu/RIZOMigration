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
class FullandFinalsettlementController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'FullandFinalsettlement';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('AppModel','LeaveRequests', 'Termination', 'EmployeeDetails','EmployeeLeaveTransaction','LeavePolicy','Units','LeaveEncashmentMaster','allocate');
    public $components = array('Session');

    /*
     * Leave List Landing Page
     */
    public function index() {
        $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
        $this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        $employee = $this -> EmployeeDetails ->find("all",array("conditions"=>array("status"=>"1"),"fields"=>array("emp_pkey","first_name","last_name")));
        $resigned = $this -> EmployeeDetails ->find("all",array("conditions"=>array("status"=>"2"),"fields"=>array("emp_pkey","first_name","last_name")));
        //My Leaves count
        $this->set("employee",$employee);
        $this->set("resigned",$resigned);
        
    }
    
    public function leavebalance(){
        $emp_pkey = $this->request->data;
        $emp_pkey = isset($emp_pkey['emp_fkey'])?$emp_pkey['emp_fkey']:'0';
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $fields = 'leave_days,ISAutherizedby,APPROVEDBY,LEAVEENTRYID, CONCAT(first_name, " ", last_name) AS emp_name, SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';
        $joins = array(
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey')
            )
        );
        $conditions  =   array (
                        'OR' => array(
                            array('EMP_fkey' => $emp_pkey,'LEAVESTATUS IN("Applied")'),
                            array('EMP_fkey' => $emp_pkey,'LEAVESTATUS IN ("Authorized")'),
                        )
                    );
        
        $resp_empleaverequests = array();
        $resp_empleaverequests["rows"]= array();
        $count = $this -> LeaveRequests -> find("count",array("conditions"=>$conditions));
        
        $arr_empleaverequests =  $this -> LeaveRequests ->find("all",array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions
        ));
        foreach ($arr_empleaverequests as $key => $value) {
            $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveRequests"],$value["SalaryHeadItems"],$value[0]);
            if($resp_empleaverequests["rows"][$key]['APPROVEDBY'] == $emp_pkey){
				$resp_empleaverequests["rows"][$key]['Action'] = 'Approve';
			}
                        else
                        {
                            $resp_empleaverequests["rows"][$key]['Action'] = 'Authorise';
                        }
            
        }
        $resp_empleaverequests["total"] = $count;
        echo json_encode($resp_empleaverequests);
    }
    
    /*
     * Leave List Landing Page
     */
    public function Terminate(){
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $this->Termination-> useDbConfig = $this -> Session -> read('ds');
        $save_data = array();
        $save_data['terminate_pkey'] = isset($arr_form_data['termination_pkey'])?$arr_form_data['termination_pkey']:0;
        $save_data['emp_fkey'] = $emp_fkey = $arr_form_data['emp_fkey'];
        $save_data['applied_date'] = $arr_form_data['applied_date'];
        $save_data['Reason'] = $arr_form_data['Reason'];
        $save_data['is_authorized'] = "Y";
        $save_data['authorized_by'] = 0;
        $save_data['is_approved'] = "Y";
        $save_data['approved_by'] = "0";
        $save_data['submitted_date'] = $arr_form_data['date_submitted'];
        $save_data['last_applied_date'] = $arr_form_data['applied_date'];
        $save_data['last_working_date'] = $arr_form_data['lat_workingday'];
        $save_data['notice_period'] = $arr_form_data['notice_period'];
        $save_data['last_approved_working_date'] = $arr_form_data['apprved'];
        $save_data['last_working_date'] = $arr_form_data['lat_workingday'];
        $save_data['remarks'] = $arr_form_data['Remarks'];
        $save_data['act_last_working_day'] = $arr_form_data['apprved'];
        $this->Termination->save($save_data);
        $this->Termination->query("update emp_details set status = '2' where emp_pkey = '$emp_fkey' ");
        echo json_encode(array('success' => 1, 'pkeys' => $this->Termination->getLastInsertID()));
    }
    public function details_res($emp_pkey = 0) {
        $this->autoRender = false;
        $this->Termination-> useDbConfig = $this -> Session -> read('ds');
        $details = $this->Termination->find("all",array("conditions"=>array("emp_fkey"=>$emp_pkey,"status"=>1)));
        if($details){
        echo json_encode(array('success' => 1,
            'applied_date' => $details['0']['Termination']['last_applied_date'],
            'submitted' => $details['0']['Termination']['submitted_date'],
            'Reason'=> $details['0']['Termination']['Reason'],
            'approved'=> $details['0']['Termination']['last_approved_working_date'],
            'last_reason'=> $details['0']['Termination']['last_working_date'],
            'last_apprv' => $details['0']['Termination']['last_approved_working_date'],
            'LEAVEENTRYID' => $details['0']['Termination']['terminate_pkey'],
            'remarks' => $details['0']['Termination']['remarks'],
            'act_last_working_day' => $details['0']['Termination']['act_last_working_day'],
                ));
        }
        else
        {
        echo json_encode(array('success' => 0, 'days' => 0));            
        }
    }
    
    public function Assets($emp_fkey = 0){
        $this->EmployeeDetails-> useDbConfig = $this -> Session -> read('ds');
//        $loans = $this -> EmployeeDetails ->query("select notice_days from emp_proff where emp_fk");
        $this->allocate->useDbConfig = $this->Session->read('ds');
        $assets = $this->allocate->find("all",array("fields"=>array("allocate.allocated_date,allocate.status,allocate.retreived_date,allocate.allocate_pkey,allocate.asset,Assets.name,Assets.specifications,Assets.Type,Assets.serial_no,Assets.model,Assets.brand,Assets.allocated_status"),"joins"=>array(array(
                'table' => 'asset_management',
                'alias' => 'Assets',
                'type' => 'INNER',
                'foreignKey' => false,
                'conditions' => array('Assets.asset_pkey = allocate.asset')
            )),"conditions"=>array("allocate.emp_fkey"=>$emp_fkey,"allocate.status"=>"Allocated")));
        $this->set('assets',$assets);
    }

    public function approve_selectd(){
        $this->autoRender = false;
        $arr_emps = $this->request->data;
        $arr_employee = $arr_emps['ss'];
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        foreach($arr_employee as $emps){
            //debug($emps);
            $arr_leaves = $this -> LeaveRequests -> find("first",array("conditions"=>array("LeaveRequests.LEAVEENTRYID"=>$emps)));
            //debug($arr_leaves);
            $emp_fkey = $arr_leaves['LeaveRequests']['EMP_fkey'];
            $PFROMDATE = $arr_leaves['LeaveRequests']['FROMDATE'];
            $PFROMHALF = $arr_leaves['LeaveRequests']['FROMHALF'];
            $PTODATE = $arr_leaves['LeaveRequests']['TODATE'];
            $PTOHALF = $arr_leaves['LeaveRequests']['TOHALF'];
            $Pleave_days = $arr_leaves['LeaveRequests']['leave_days'];
            $Pleave_status = $arr_leaves['LeaveRequests']['LEAVESTATUS'];
            $this->LeaveRequests->query("call leave_transaction_prc('$emps','$emp_fkey','$PFROMDATE','$PFROMHALF','$PTODATE','$PTOHALF','$Pleave_days','$Pleave_status')");
        }
        if($this->LeaveRequests->updateAll(
                            array(
                        "LeaveRequests.LEAVESTATUS" => "'Approved'",
                        "LeaveRequests.ISAutherized" => 1,
                        "LeaveRequests.ISAutherizedby" => "0",
                        "LeaveRequests.Autherized_date" => date('Y-m-d'),
                        "LeaveRequests.ISAPPROVED" => 1,
                        "LeaveRequests.APPROVEDBY" => "0",
                        "LeaveRequests.APPROVED_date" => date('Y-m-d'),
                        "LeaveRequests.REMARKS" => "'Approved for settlement'",        
                            ), array("LeaveRequests.LEAVEENTRYID" => $arr_employee)
                    )){
            return true;
        }
        
    }
    
    public function savedetails(){
        $this->autoRender = false;
        $arr_emps = $this->request->data;
        $termi_pkey = $arr_emps['pkeys'];
        $this->Termination->useDbConfig = $this->Session->read('ds');
        if($this->Termination->updateAll(
                            array(
                        "Termination.working_days_settled" => $arr_emps['total'],
                        "Termination.leave_balance" => $arr_emps['leave'],
                        "Termination.days_attendance" => $arr_emps['atte'],
                        "Termination.payroll_days" => $arr_emps['total'],        
                            ), array("Termination.terminate_pkey" => $termi_pkey)
                    )){
            return true;
        }
    }

    public function workingattendnacedays(){
        $this->autoRender = false;
        $arr_emps = $this->request->data;
        $arr_employee = $arr_emps['emp'];
        
        $todate = $arr_emps['todate'];
        $from = $arr_emps['from'];
        
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $arr_atts = $this -> LeaveRequests -> query("select sum(presant_total)+sum(leave_total) presant_total from attendance_register where month_year >=(
   select max(month_year)from payroll_master where action  in('Approved','Processed') and  emp_fkey = '$arr_employee')  and emp_fkey = '$arr_employee' ");
        $arr_total_work_dats = $arr_atts['0']['0']['presant_total'];
        $arr_leav = $this -> LeaveRequests ->query("select leave_balance_inthe_year_fn('14','86','2016')  leaves");
        $attendance_days = isset($arr_atts['0']['0']['presant_total'])?$arr_atts['0']['0']['presant_total']:'0';
        $leave_balance = isset($arr_leav['0']['0']['leaves'])?$arr_leav['0']['0']['leaves']:'0';
        $diff_with_weekoff_days = $this -> LeaveRequests -> query("select weekoff_days_count_fn('$arr_employee','$todate','$from') no_of_weekof");
        $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof'])?abs($diff_with_weekoff_days['0']['0']['no_of_weekof']):0;
        echo json_encode(array('success'=>1,'atte'=>$attendance_days,'leave'=>$leave_balance ,'weekoff_couts' =>$offs));
    }
   
    public function reject_selected(){
        $this->autoRender = false;
        $arr_emps = $this->request->data;
        $arr_employee = $arr_emps['ss'];
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        foreach ($arr_employee as $emps){
           $arr_leaves = $this -> LeaveRequests -> find("first",array("conditions"=>array("LeaveRequests.LEAVEENTRYID"=>$emps)));
            //debug($arr_leaves);
            $emp_fkey = $arr_leaves['LeaveRequests']['EMP_fkey'];
            $PFROMDATE = $arr_leaves['LeaveRequests']['FROMDATE'];
            $PFROMHALF = $arr_leaves['LeaveRequests']['FROMHALF'];
            $PTODATE = $arr_leaves['LeaveRequests']['TODATE'];
            $PTOHALF = $arr_leaves['LeaveRequests']['TOHALF'];
            $Pleave_days = $arr_leaves['LeaveRequests']['leave_days'];
            $Pleave_status = $arr_leaves['LeaveRequests']['LEAVESTATUS'];
            $this->LeaveRequests->query("call leave_transaction_prc('$emps','$emp_fkey','$PFROMDATE','$PFROMHALF','$PTODATE','$PTOHALF','$Pleave_days','$Pleave_status')");
        }
        if($this->LeaveRequests->updateAll(
                            array(
                        "LeaveRequests.LEAVESTATUS" => "'Rejected'",
                        "LeaveRequests.ISAutherized" => 0,
                        "LeaveRequests.ISAutherizedby" => "0",
                        "LeaveRequests.Autherized_date" => date('Y-m-d'),
                        "LeaveRequests.ISAPPROVED" => 0,
                        "LeaveRequests.APPROVEDBY" => "0",
                        "LeaveRequests.APPROVED_date" => date('Y-m-d'),
                        "LeaveRequests.REMARKS" => "'Rejected for settlement'",        
                            ), array("LeaveRequests.LEAVEENTRYID" => $arr_employee)
                    )){
            return true;
        }
        
    }
    
    public function get_complete($emp_pkey = 0,$dayss = 0,$leaves  = 0){
        $this->Termination-> useDbConfig = $this -> Session -> read('ds');
        $this->LeaveRequests-> useDbConfig = $this -> Session -> read('ds');
        $from = date('Y-m');
        $details = $this->Termination->find("all",array("fields"=>array("Termination.*,EmployeeInfo.*"),"conditions"=>array("emp_fkey"=>$emp_pkey),"joins"=>array(array("table"=>"employee_info","alias"=>"EmployeeInfo","foreignKey"=>false,"type"=>"LEFT","conditions"=>array("EmployeeInfo.emp_pkey = Termination.emp_fkey")))));
        $arr_empleaverequests = $this->LeaveRequests->query("select ectc.month_year,br.branch_name,ed.first_name,ed.last_name,ectc.* "
                . "from emp_salary_slip as ectc "
                . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                   
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       
                                          where ectc.head_operator = 'ADDITION' 
                                          and ectc.item_part = 'DIRECT' 
                                          and ectc.emp_fkey = '$emp_pkey'"
                . " and month_year ='$from' "
                . "and ed.status = 1  "
                . "and end_date_effective is null ");

//                   }
        $salaryslipwithoutcomponents = $this->LeaveRequests->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
                . "from emp_salary_slip as ectc "
                . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$emp_pkey' "
                . " and month_year ='$from' "
                . "and ed.status = 1 and "
                . "end_date_effective is null ");
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->LeaveRequests->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                . "from payroll_master "
                . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                . "left join department as d on(d.dept_code = ep.emp_dept) "
                . "left join designation as dd on (dd.desig_code = ep.designation)"
                . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                . " and payroll_master.month_year ='$from'"
                . " and payroll_master.emp_fkey = '$emp_pkey' ");


        // debug($empdetails);

        $branch = $details['0']['EmployeeInfo']['branch'];
        $emp_id = 'ADMIN';
        $yearmonth = date('Y-m');
        $sdds = $this->LeaveRequests->query("CALL `final_settle_pay_prc`('$branch', '$yearmonth', '$emp_pkey', '$dayss', '$leaves', '$emp_id', @`perror_message`)");
        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails
        );
        $this->set("details",$details);
        $this->set("arr_salary_for_template",$arr_salary_for_template);
    }
    
    
    public function approveencash(){
        $this->autoRender = false;
        $this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $arr_data = $this->request->data;
        $emp_pkey = $arr_data['emp_pkey'];
        $arr_emps  = $this->EmployeeDetails ->find("all",array("conditions"=>array("status"=>1,"emp_pkey"=>$emp_pkey)));
        $branch_code = isset($arr_emps['0']['branch_code'])?$arr_emps['0']['branch_code']:0;
        $cur_emp_key = $emp_pkey;
        
        $salary_head_items = $this->EmployeeDetails->query("select salary_head_item_fkey as leavepolicy from leavepolicy where is_leave_encash = 'Y' and leave_encash_limit is NOT NULL and status = '1' and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey in ('14'))");
        $applied = $arr_data['leaveencash'];
        $salary = $salary_head_items['0']['leavepolicy']['leavepolicy'];
        $currentyear = $this->EmployeeDetails->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$cur_emp_key')");
        $finyears = isset($currentyear['0'])?$currentyear['0']['fin_year']['fin_year']:null;
        $emp_ncashes = $this -> EmployeeDetails ->query("select leave_balance_inthe_year_fn('$cur_emp_key',salary_head_items.salary_head_item_pkey,'$finyears') as yearlybalance,salary_head_items.item,leavepolicy.*,leavepolicy.LEAVEPOLICYID,leavepolicy.salary_head_item_fkey,leavepolicy.alloted_leave_forthe_year,emp_details.emp_pkey,emp_details.branch_code,concat(emp_details.first_name,' ',emp_details.last_name) as emp_name,leavepolicy.alloted_leave_forthe_month,leavepolicy.leave_encash_limit from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on (emp_details.emp_pkey = '$cur_emp_key') where LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$cur_emp_key') and leavepolicy.salary_head_item_fkey = '$salary' and leave_encash_limit is NOT NULL");
        $arr_data = array(); 
        $arr_data['emp_fkey'] = $emp_pkey =  $emp_ncashes['0']['emp_details']['emp_pkey'];
        $arr_data['emp_name'] = $emp_ncashes['0']['0']['emp_name'];
        $arr_data['branch_code'] = $branch_code = $emp_ncashes['0']['emp_details']['branch_code'];
        $arr_data['salary_head_item_fkey'] = $emp_ncashes['0']['leavepolicy']['salary_head_item_fkey'];
        $arr_data['encash_days'] = $emp_ncashes['0']['leavepolicy']['leave_encash_limit'];
        $arr_data['available_days'] = min($emp_ncashes['0']['leavepolicy']['leave_encash_limit'],$emp_ncashes['0']['0']['yearlybalance']);
        $arr_data['requested_days'] = $applied;
        $arr_data['approved_days'] = $applied;
        $arr_data['created_by'] = $this -> Session -> read('user_name');
        $arr_data['creation_date'] = date('Y-m-d');
        $arr_data['modified_by'] = $this -> Session -> read('user_name');
        $arr_data['approved_by'] = '0';
        $arr_data['is_approved'] = 'Y';
        $arr_data['approved_date'] = date("Y-m-d");
        if($result = $this->LeaveEncashmentMaster->save($arr_data)){
            $LeaveEncashmentMaster_id=$this->LeaveEncashmentMaster->getLastInsertId();;
            $emp_ncashes_approve = $this -> EmployeeDetails ->query("call leave_encash_prc('$branch_code','$emp_pkey','$LeaveEncashmentMaster_id','0','@msg')");
            return true;
        }
         
   }
   
   public function getperiod($emp_fkey = 0){
        $this->autoRender = false;
        $this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');
        $notice_period = $employee = $this -> EmployeeDetails ->query("select notice_days from emp_proff where emp_fkey = '$emp_fkey' ");
        $days = isset($notice_period['0']['emp_proff']['notice_days'])?$notice_period['0']['emp_proff']['notice_days']:0;
        if($notice_period){
        echo json_encode(array('success' => 1, 'days' => $days));
        }
        else
        {
        echo json_encode(array('success' => 0, 'days' => 0));            
        }
   }

//   public function leaveadjustment()
//   {
//       $this->autoRender = false;
//       $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//       $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
//       $data = $this->request->data;
//       // debug($data); exit;
//       //Edited by Akshay on 16-12-2023
//       $emp_pkey = isset($data['emp_pkey']) ? $data['emp_pkey'] : '';
//       $leave_adjusted = isset($data['leaveadjusted']) ? $data['leaveadjusted'] : 0;
//       $leave_balance = isset($data['balanceAfter']) ? $data['balanceAfter'] : 0;
//       $leave_name = isset($data['currentLeaveName']) ? $data['currentLeaveName'] : '';
//       $resignation_date = isset($data['resignationMonth']) ? $data['resignationMonth'] : '';
//       $date = new DateTime($resignation_date);
//       $resignation_month = $date->format('Y-m');
//
//       if(trim($leave_adjusted) == ''){
//           $leave_adjusted =0;
//       }
//
//       if(trim($leave_balance) == ''){
//           $leave_balance = 0;
//       }
//       $this->EmployeeDetails->query("UPDATE termination SET leave_balance='$leave_adjusted',approved_balance ='$leave_balance' WHERE status = 1 AND emp_fkey = '$emp_pkey'");
//     
//
//       $arr_annual_ctc = $this->EmployeeDetails->query("SELECT emp_anual_ctc FROM emp_ctc_transaction ect WHERE ect.end_date_effective IS NULL AND ect.emp_fkey = '$emp_pkey'");
//       $annual_ctc = isset($arr_annual_ctc[0]['ect']['emp_anual_ctc']) ? $arr_annual_ctc[0]['ect']['emp_anual_ctc'] : 0;
//
//       $salary = ($annual_ctc / 365) * $leave_adjusted;
//       $salary = round($salary);
//
//       $response = ['success' => false, 'message' => 'Leave adjustment failed'];
//       // debug($data);
//       // exit;
//       if ($emp_pkey != '') {
//           try {
//               $arr_exists = $this->EmployeeDetails->query("SELECT emp_settle_slip_pkey FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' AND month_year = '$resignation_month'");
//               if (count($arr_exists) > 0) {
//                   $this->EmployeeDetails->query("UPDATE emp_settle_slip SET status = 0 WHERE emp_fkey = '$emp_pkey' AND month_year = '$resignation_month'");
//                   $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (type, salary_head_item_desc, month_year, salary_amount,emp_fkey,leave_total, status)
//                                                 VALUES ('ENCASHMENT', '$leave_name', '$resignation_month', '$salary', '$emp_pkey', '$leave_adjusted', 1)
//                                                 ");
//                   $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (type, salary_head_item_desc, month_year, salary_amount,emp_fkey,leave_total, status)
//                                       VALUES ('BALANCE', '$leave_name', '$resignation_month', '$salary', '$emp_pkey', '$leave_balance', 1)
//                                       ");
//               } else {
//                   $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (type, salary_head_item_desc, month_year, salary_amount,emp_fkey,leave_total, status)
//                                             VALUES ('ENCASHMENT', '$leave_name', '$resignation_month', '$salary', '$emp_pkey', '$leave_adjusted', 1)
//                 ");
//                   $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (type, salary_head_item_desc, month_year, salary_amount,emp_fkey,leave_total, status)
//                                     VALUES ('BALANCE', '$leave_name', '$resignation_month', '$salary', '$emp_pkey', '$leave_balance', 1)
//                   ");
//               }
//               $response['success'] = true;
//               $response['message'] = "Leave adjustment successful.";
//           } catch (Exception $e) {
//               $response = ['success' => false, 'message' => 'Leave adjustment failed'];
//              // debug($e);
//           }
//       }
//       echo json_encode($response);
//   }
    
    public function leaveadjustment()
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
        $data = $this->request->data;

        //Edited by Akshay on 16-12-2023
        $str_company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 25-3-2024
        $emp_pkey = isset($data['emp_pkey']) ? $data['emp_pkey'] : '';
        $leave_adjusted = isset($data['leaveadjusted']) ? $data['leaveadjusted'] : 0;
        $leave_balance = isset($data['balanceAfter']) ? $data['balanceAfter'] : 0;
        $leave_name = isset($data['currentLeaveName']) ? $data['currentLeaveName'] : '';
        $resignation_date = isset($data['resignationMonth']) ? $data['resignationMonth'] : '';
        $date = new DateTime($resignation_date);
        $resignation_month = $date->format('Y-m');
        $balance_working_days = isset($data['balance_working_days']) ? $data['balance_working_days'] : 0;
        $payroll_days = isset($data['total_working_days']) ? $data['total_working_days'] : 0;
        $res_present_days = isset($data['res_present_days']) ? $data['res_present_days'] : 0;

        if (trim($leave_adjusted) == '') {
            $leave_adjusted = 0;
        }

        if (trim($leave_balance) == '') {
            $leave_balance = 0;
        }
        if ($str_company_code == 'DEMO' || $str_company_code = 'KWMT') {
            try{
                $this->EmployeeDetails->query("UPDATE termination SET leave_balance='$leave_adjusted',approved_balance ='$leave_balance', working_days_settled = '$res_present_days',payroll_days = '$payroll_days' WHERE status = 1 AND emp_fkey = '$emp_pkey'");
            }catch(Exception $e){
                debug($e);
            }
        } else {
            $this->EmployeeDetails->query("UPDATE termination SET leave_balance='$leave_adjusted',approved_balance ='$leave_balance' WHERE status = 1 AND emp_fkey = '$emp_pkey'");
        }

        $arr_annual_ctc = $this->EmployeeDetails->query("SELECT emp_anual_ctc FROM emp_ctc_transaction ect WHERE ect.end_date_effective IS NULL AND ect.emp_fkey = '$emp_pkey'");
        $annual_ctc = isset($arr_annual_ctc[0]['ect']['emp_anual_ctc']) ? $arr_annual_ctc[0]['ect']['emp_anual_ctc'] : 0;

        $salary = ($annual_ctc / 365) * $leave_adjusted;
        $salary = round($salary);

        $response = ['success' => false, 'message' => 'Leave adjustment failed'];
        // debug($data);
        // exit;
        if ($emp_pkey != '') {
            try {
                $arr_exists = $this->EmployeeDetails->query("SELECT emp_settle_slip_pkey FROM emp_settle_slip WHERE emp_fkey = '$emp_pkey' AND month_year = '$resignation_month'");
                if (count($arr_exists) > 0) {
                    $this->EmployeeDetails->query("UPDATE emp_settle_slip SET status = 0 WHERE emp_fkey = '$emp_pkey' AND month_year = '$resignation_month'");
                    $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (type, salary_head_item_desc, month_year, salary_amount,emp_fkey,leave_total, status)
                                                 VALUES ('ENCASHMENT', '$leave_name', '$resignation_month', '$salary', '$emp_pkey', '$leave_adjusted', 1)
                                                 ");
                    $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (type, salary_head_item_desc, month_year, salary_amount,emp_fkey,leave_total, status)
                                       VALUES ('BALANCE', '$leave_name', '$resignation_month', '$salary', '$emp_pkey', '$leave_balance', 1)
                                       ");
                } else {
                    $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (type, salary_head_item_desc, month_year, salary_amount,emp_fkey,leave_total, status)
                                             VALUES ('ENCASHMENT', '$leave_name', '$resignation_month', '$salary', '$emp_pkey', '$leave_adjusted', 1)
                 ");
                    $this->EmployeeDetails->query("INSERT INTO emp_settle_slip (type, salary_head_item_desc, month_year, salary_amount,emp_fkey,leave_total, status)
                                     VALUES ('BALANCE', '$leave_name', '$resignation_month', '$salary', '$emp_pkey', '$leave_balance', 1)
                   ");
                }
                $response['success'] = true;
                $response['message'] = "Leave adjustment successful.";
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Leave adjustment failed'];
                // debug($e);
            }
        }
        echo json_encode($response);
    }
}