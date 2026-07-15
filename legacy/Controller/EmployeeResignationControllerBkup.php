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
class EmployeeResignationController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'EmployeeResignation';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'qualifcations', 'Termination', 'LeaveEncashmentMaster', 'EmpTaxSalTrans', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index() {
        
    }

    public function getstages($site_pkey = 0) {
        $this->autoRender = false;
        //$site_pkey=[];
        //  debug($site_pkey);
        $sitepkey_array = $site_pkey;
        if ($sitepkey_array == '') {
            $conditions = '';
        } else {
            $conditions = 'branch_code="' . $sitepkey_array . '"';
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code', 'conditions' => array('status' => 1, $conditions))));
        //debug($arr_stages);
        $str_stage_options_html = '';
        $this->set('arr_Emp', $arr_Emp);
        // debug($arr_Emp);
        foreach ($arr_Emp as $value) {
            $emp_pkey = isset($value['emp_pkey']) ? $value['emp_pkey'] : '';
            $emp_name = isset($value['emp_name']) ? $value['emp_name'] : '';
            $str_stage_options_html .= '<option value="' . $emp_pkey . '">' . $emp_name . '</option>';
            //  debug($str_stage_options_html);
        }
        echo $str_stage_options_html;
    }

    public function getperiod() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $notice_period = $employee = $this->EmployeeDetails->query("select notice_days from emp_proff where emp_fkey = '$emp_fkey' ");
        $days = isset($notice_period['0']['emp_proff']['notice_days']) ? $notice_period['0']['emp_proff']['notice_days'] : 0;
        if ($notice_period) {
            echo json_encode(array('success' => 1, 'days' => $days));
        } else {
            echo json_encode(array('success' => 0, 'days' => 0));
        }
    }

    public function form() {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProffessional.emp_fkey')
            )
        );
        $employee = $this->EmployeeDetails->find("all", array('order' =>  array('emp_pkey DESC'),"conditions" => array("status" => "1","emp_pkey NOT IN (SELECT emp_fkey FROM termination) "), "joins"=>$joins, "fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.first_name", "EmployeeDetails.last_name", "EmployeeProffessional.emp_company_id")));
        $this->set("employee", $employee);
    }
    
    public function approves($emp_pkey = 0,$leaves = 0) {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $employee = $this->EmployeeDetails->query("SELECT * FROM `payroll_master` WHERE `emp_fkey` = '$emp_pkey' AND `approved` = 'N'  and month_year > '2018-04'  ORDER BY month_year ");
        $leave_encash_amts = $this->EmployeeDetails->query("select salary_rate from emp_encash_slip where emp_fkey = '$emp_pkey' and end_date_effective is     null ");
        $this->set("employee", $employee);
        $loans = $this->requestAction('/EmployeeLoan/getLoanBalance/' . $emp_pkey);
        $this->set("loans", $loans);
        
        $encashment_master = $this->leaveencash($emp_pkey, $leaves, 0);
        // Leave Excess Find 
        $arr_leaves_heads = $this->EmployeeDetails->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 ) and occurance != 'LOP' "); 
            $arr_leave = array();
//            DEBUG($arr_leaves_heads);
        $this->set('arr_leaves', $arr_leaves_heads);
        $month = date("m");
        $year = date("Y");
        $excess_leave = 0;
        foreach($arr_leaves_heads as $key => $val){
            $head = $val['salary_head_items']['occurance'];
            $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
            $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$month','$year') as LeaveBalance");
            $resp = isset($lbalance['0']['0']['LeaveBalance'])?round($lbalance['0']['0']['LeaveBalance'],1):0;
            if($resp < 0){
                $excess_leave += $resp;
            }
        }
        $this->set("excess_leave", $excess_leave);
        
        
        
        //Leave Encashments 
        
        $leave_encash_amts_fetch = $this->EmployeeDetails->query("SELECT * FROM `emp_encash_slip` WHERE `emp_fkey` = '$emp_pkey' and end_date_effective is     null"); 
        
        $this->set("leave_encash_amts_fetch", $leave_encash_amts_fetch);
        
        //Leave Encashment Not Found
        $leave_encashment_reason = '';
        $leave_salary_structur_format = $this->EmployeeDetails->query("select emp_fkey,emp_structure_id,prorate_code,prorate_desc,defined_structure_for,	 
salary_head_item_fkey,salary_head_item_desc,structure_det_value,head_operator,head_type	,item_part from emp_salary_structure 
  where emp_fkey=$emp_pkey and end_date_effective is null and lcase(salary_head_item_desc) ='leave encashment' "); 
        
        if(empty($leave_salary_structur_format)){
            $leave_encashment_reason .= 'Please Check Leave Encashment Assignment in Allocated Salary Structure ';
        }
        
        $this->set("leave_encashment_reason", $leave_encashment_reason);
        
        $encash_sums = 0;
        foreach ($leave_encash_amts_fetch as $encashs){
            $encash_sums += $encashs['emp_encash_slip']['salary_amount'];
        }
        $this->set("encash_sums", $encash_sums);
        $joining_date_fetch = $this->EmployeeDetails->query("SELECT joining_date FROM `emp_proff` WHERE `emp_fkey` = '$emp_pkey' "); 
        $grativity = 0;
        $gr_eq = "";
        $excess_leav_amt = 0;
        if(round((time()-strtotime($joining_date_fetch['0']['emp_proff']['joining_date']))/(3600*24*365.25)) > 1){
            $gross_sal = $this->EmployeeDetails->query("select emp_anual_ctc/12 as gros_sal from emp_ctc_transaction where emp_fkey = '$emp_pkey' and end_date_effective is null "); 
            $years = round((time()-strtotime($joining_date_fetch['0']['emp_proff']['joining_date']))/(3600*24*365.25));
            $grativity = round(($gross_sal['0']['0']['gros_sal'] /26 * 15) * $years,1);
            $excess_leav_amt = $gross_sal['0']['0']['gros_sal'] /26 * abs($excess_leave);
            $gr_eq = "Gross Salary " . $gross_sal['0']['0']['gros_sal'] . "/26 * 15 * " . $years;
        }
        
        $this->set("grativity", $grativity);
            
        $this->set("gr_eq", $gr_eq);
        $this->set("excess_leav_amt", $excess_leav_amt);
        $this->render('approves');
    }

    public function get_complete($emp_pkey = 0) {
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        //debug($arr_comp_contact_info);
        $this->set("arr_comp_contact_info", $arr_comp_contact_info);
//        debug($arr_comp_contact_info);
        $from = date('Y-m');
        $arr_emps = $this->request->data;
        $dayss = $arr_emps['dayss'];
        $g = $arr_emps['emp'];
        $leaves = $arr_emps['leaves'];
        $this->set("gratuity", $g);
        $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeInfo.*"), "conditions" => array("emp_fkey" => $emp_pkey), "joins" => array(
                array("table" => "employee_info",
                    "alias" => "EmployeeInfo",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")),
                array("table" => "emp_details",
                    "alias" => "EmployeeDetails",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey"))
        )));
        $branch = $details['0']['EmployeeDetails']['branch_code'];
        $emp_id = 'ADMIN';
//        $encashment_master = $this->leaveencash($emp_pkey, $leaves, 0);
        $encashment_master = "`emp_fkey` = '$emp_pkey' ";
        $leaveenmcashs = $this->Termination->query("select * from emp_encash_slip where $encashment_master and end_date_effective is     null");

        
        $arr_emp_salaries = $this->Termination->query(" SELECT * FROM `payroll_master` WHERE `emp_fkey` = '$emp_pkey' and  `approved` = 'N' and month_year > '2018-04'  ORDER BY month_year ");
        $this->set("arr_emp_salaries", $arr_emp_salaries);
            
        $this->set("leaveenmcashs", $leaveenmcashs);
        $yearmonth = date('Y-m');
        $sdds = $this->Termination->query("CALL `final_settle_pay_prc`('$branch', '$yearmonth', '$emp_pkey', '$dayss', '$leaves', '$emp_id', @`perror_message`)");
        //debug("CALL `final_settle_pay_prc`('$branch', '$yearmonth', '$emp_pkey', '$dayss', '$leaves', '$emp_id', @`perror_message`)");
        $arr_empleaverequests = $this->Termination->query("select ectc.month_year,br.branch_name,ed.first_name,ed.last_name,ectc.* "
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
        $salaryslipwithoutcomponents = $this->Termination->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
                . "from emp_salary_slip as ectc "
                . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$emp_pkey' "
                . " and month_year ='$from' "
                . "and ed.status = 1 and "
                . "end_date_effective is null ");
        //debug($from);
        //($emp_pkey);
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->Termination->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                . "from payroll_master "
                . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                . "left join department as d on(d.dept_code = ep.emp_dept) "
                . "left join designation as dd on (dd.desig_code = ep.designation)"
                . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                . " and payroll_master.month_year ='$from'"
                . " and payroll_master.emp_fkey = '$emp_pkey' ");


        // debug($empdetails);

        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails
        );
        
        
        
        // Start of new line 
        
        
        
        $submitted_dat = $from = $details['0']['Termination']['submitted_date'];
        $newdate = $todate = $details['0']['Termination']['last_approved_working_date'];
        
        $date1 = date_create($newdate);
        $date2 = date_create($submitted_dat);
        $diff = date_diff($date1, $date2);

        $arr_att_days_after_resignation = "select sum(a) a from(
            select count(*) as a from emp_detail_timeattandance where others in ('LOP','LOP/LOP') and 	emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            union all
            select count(*)/2 as a from emp_detail_timeattandance where (instr(others,'/LOP') > 0 or instr(others,'LOP/') > 0 ) and others!= 'LOP'   and	 emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            )ass ";
        $exec_days = $this->Termination->query($arr_att_days_after_resignation);
        $exec_holiday_group_id = $this->Termination->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
        $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        $exec_days_holidays = $this->Termination->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
  and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$todate'; ");
        $days_after_resignation_att = isset($exec_days['0']['0']['a']) ? round($exec_days['0']['0']['a'],1) : 0;
        $diff_with_weekoff_days = $this->Termination->query("select weekoff_days_count_fn('$emp_pkey','$todate','$from') no_of_weekof");
        $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days['0']['0']['no_of_weekof'])+$exec_days_holidays['0']['0']['days_count'] : 0;
        $this->set("offs", $offs);
        $this->set("days_after_resignation_att", $days_after_resignation_att);
//        debug($days_after_resignation_att);
        
        
        // end of new line 
        $arr_leaves_heads = $this->EmployeeDetails->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 ) and occurance != 'LOP' LIMIT 50"); 
            $arr_leave = array();
//            DEBUG($arr_leaves_heads);
        $this->set('arr_leaves', $arr_leaves_heads);
        $month = date("m");
        $year = date("Y");
        $excess_leave = 0;
        foreach($arr_leaves_heads as $key => $val){
            $head = $val['salary_head_items']['occurance'];
            $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
            $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$month','$year') as LeaveBalance");
            $resp = isset($lbalance['0']['0']['LeaveBalance'])?round($lbalance['0']['0']['LeaveBalance'],1):0;
            if($resp < 0){
                $excess_leave += $resp;
            }
        }
        $this->set("diff", $diff->format("%a"));
        $this->set("excess_leave", $excess_leave);
        
        
        
        //Leave Encashments 
        
        $leave_encash_amts_fetch = $this->EmployeeDetails->query("SELECT * FROM `emp_encash_slip` WHERE `emp_fkey` = '$emp_pkey' "); 
        
        $this->set("leave_encash_amts_fetch", $leave_encash_amts_fetch);
        
        $encash_sums = 0;
        $days = 0;
        foreach ($leave_encash_amts_fetch as $encashs){
            $encash_sums += $encashs['emp_encash_slip']['salary_amount'];
        }
        $this->set("encash_sums", $encash_sums);
        $joining_date_fetch = $this->EmployeeDetails->query("SELECT joining_date FROM `emp_proff` WHERE `emp_fkey` = '$emp_pkey' "); 
        $grativity = 0;
        $gross_sal = $this->EmployeeDetails->query("select emp_anual_ctc/12 as gros_sal from emp_ctc_transaction where emp_fkey = '$emp_pkey' and end_date_effective is null "); 
        if(round((time()-strtotime($joining_date_fetch['0']['emp_proff']['joining_date']))/(3600*24*365.25)) > 1){
            
            $years = round((time()-strtotime($joining_date_fetch['0']['emp_proff']['joining_date']))/(3600*24*365.25));
            $grativity = round(($gross_sal['0']['0']['gros_sal'] /26 * 15) * $years,1);
        }
        $this->set("grativity", $grativity);
        $excess_leav_amt = isset($gross_sal['0']['0']['gros_sal'])?$gross_sal['0']['0']['gros_sal'] /26 * abs($excess_leave):0;
        $this->set("excess_leav_amt", $excess_leav_amt);
        
        
        $loans = $this->requestAction('/EmployeeLoan/getLoanBalance/' . $emp_pkey);
        $this->set("loans", $loans);
        //debug($arr_salary_for_template);
        $this->set("details", $details);
        $this->set("arr_salary_for_template", $arr_salary_for_template);
        $this->render('salaryslip');
    }

    public function removeemps() {

        $this->autoRender = false;
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
        $emp_pkey = $arr_request_data['emp_pkey'];
        $g = $arr_request_data['emp'];
        $removes = $this->EmployeeDetails->updateAll(
                    array(
                        'status' => 2 // Terminated , Status should be 1 untill we proccess this - to avail that Employee in payroll // 
                    ),
                    array(
                        'EmployeeDetails.emp_pkey' => $emp_pkey
                    )
                );
        $arr_emp_salaries = $this->EmployeeDetails->query(" SELECT * FROM `payroll_master` WHERE `emp_fkey` = '$emp_pkey' and  `approved` = 'N' and month_year > '2018-04'  ");
        $this->set("arr_emp_salaries", $arr_emp_salaries);
        
        $unverified_attendances_proccess = $this->EmployeeDetails->query("SELECT count(*) as COUNTS FROM emp_settle_slip WHERE `emp_fkey` = '$emp_pkey'  ");
        
        if($unverified_attendances_proccess['0']['0']['COUNTS'] > 0){
            echo '<div class="callout callout-danger "> Termination Already Proccesed  </div>';
            die();
        }
        
        foreach ($arr_emp_salaries as $val){
            $pay_month = $val['payroll_master']['month_year'];
            $gross = $val['payroll_master']['gross_salary'];
            $net_pay = $val['payroll_master']['net_salary'];
            $arr_emp_salaries = $this->EmployeeDetails->query(" INSERT INTO emp_settle_slip(emp_fkey,type,payroll_master_fkey,month_year,salary_head_item_desc,salary_rate,structure_det_value) VALUES ('$emp_pkey','P','0','$pay_month','$pay_month','$gross','$net_pay')   ");
        }
        
        $loans = $this->requestAction('/EmployeeLoan/getLoanBalance/' . $emp_pkey);
        
        $month_year = date("Y-m-d");
        foreach ($loans as $value){
            $month_year = $value['LoanDetails']['emp_loan']['emi_start_month'];
            $loan_type = $value['LoanDetails']['emp_loan']['loan_amount'];
            $net = $value['BalanceOutstanding'];
            $desc = 'Loan Amount - '.$value['LoanDetails']['emp_loan']['loan_amount'].' EMI - '.$value['LoanDetails']['emp_loan']['emi_amount'];
            $arr_insert = $this->EmployeeDetails->query(" INSERT INTO emp_settle_slip(type,month_year,salary_head_item_desc,structure_det_value,salary_rate,emp_fkey) VALUES ('LOAN','$month_year','$desc','$net','$loan_type','$emp_pkey')  ");
        }
        
        $leave_encash_amts_fetch = $this->EmployeeDetails->query("SELECT * FROM `emp_encash_slip` WHERE `emp_fkey` = '$emp_pkey' "); 
        
        $this->set("leave_encash_amts_fetch", $leave_encash_amts_fetch);
        
        $encash_sums = 0;
        $encash_l_days = 0;
        foreach ($leave_encash_amts_fetch as $encashs){
            $encash_sums += $encashs['emp_encash_slip']['salary_amount'];
            $encash_l_days += $encashs['emp_encash_slip']['leave_total'];
        }
        
        $arr_insert_leave_encashment = $this->EmployeeDetails->query(" INSERT INTO emp_settle_slip (emp_fkey,type,month_year,salary_head_item_desc,structure_det_value,salary_rate) VALUES ('$emp_pkey','ENCASHMENT','$month_year','Leave Encashment Amount','$encash_sums','4') ");
        
        $month = date("m");
        $year = date("Y");
        $excess_leave = 0;
        $arr_leaves_heads = $this->EmployeeDetails->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 ) and occurance != 'LOP' LIMIT 50"); 
            
        foreach($arr_leaves_heads as $key => $val){
            $head = $val['salary_head_items']['occurance'];
            $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
            $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$month','$year') as LeaveBalance");
            $resp = isset($lbalance['0']['0']['LeaveBalance'])?round($lbalance['0']['0']['LeaveBalance'],1):0;
            if($resp < 0){
                $excess_leave += $resp;
            }
        }
                
        $joining_date_fetch = $this->EmployeeDetails->query("SELECT joining_date FROM `emp_proff` WHERE `emp_fkey` = '$emp_pkey' "); 
        $grativity = 0;
        $gross_sal = $this->EmployeeDetails->query("select emp_anual_ctc/12 as gros_sal from emp_ctc_transaction where emp_fkey = '$emp_pkey' and end_date_effective is null "); 
        if(round((time()-strtotime($joining_date_fetch['0']['emp_proff']['joining_date']))/(3600*24*365.25)) > 1){
            
            $years = round((time()-strtotime($joining_date_fetch['0']['emp_proff']['joining_date']))/(3600*24*365.25));
            $grativity = round(($gross_sal['0']['0']['gros_sal'] /26 * 15) * $years,1);
        }
        $this->set("grativity", $grativity);
        $excess_leav_amt = isset($gross_sal['0']['0']['gros_sal'])?$gross_sal['0']['0']['gros_sal'] /26 * abs($excess_leave):0;
        $excess_leav_amt_rate = isset($gross_sal['0']['0']['gros_sal'])?$gross_sal['0']['0']['gros_sal'] /26:0;
        $this->set("excess_leav_amt", $excess_leav_amt);
        
        $arr_excess_leave_insert = $this->EmployeeDetails->query(" INSERT INTO emp_settle_slip (emp_fkey,month_year,type,salary_head_item_desc,structure_det_value,salary_head_item_fkey,salary_rate) VALUES('$emp_pkey','$month','EXCESS','EXCESS LEAVE','$excess_leav_amt','0','$excess_leav_amt_rate') ");
        if($g == '1')
        $arr_gratitvity = $this->EmployeeDetails->query(" INSERT INTO emp_settle_slip(emp_fkey,type,month_year,salary_head_item_fkey,salary_head_item_desc,structure_det_value,salary_rate) VALUES ('$emp_pkey','GRAT','$month_year','0','Gratuity','$grativity','$excess_leav_amt_rate') ");
        
        
        $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeInfo.*"), "conditions" => array("emp_fkey" => $emp_pkey), "joins" => array(
                array("table" => "employee_info",
                    "alias" => "EmployeeInfo",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")),
                array("table" => "emp_details",
                    "alias" => "EmployeeDetails",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey"))
        )));
        
        
        $submitted_dat = $from = $details['0']['Termination']['submitted_date'];
        $newdate = $todate = $details['0']['Termination']['last_approved_working_date'];
        
        $date1 = date_create($newdate);
        $date2 = date_create($submitted_dat);
        $diff = date_diff($date1, $date2);
        $this->set("diff", $diff->format("%a"));
        $diff = $diff->format("%a");
        
        $arr_att_days_after_resignation = "select sum(a) a from(
            select count(*) as a from emp_detail_timeattandance where others in ('LOP','LOP/LOP') and 	emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            union all
            select count(*)/2 as a from emp_detail_timeattandance where (instr(others,'/LOP') > 0 or instr(others,'LOP/') > 0 ) and others!= 'LOP'   and	 emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            )ass ";
        $exec_days = $this->Termination->query($arr_att_days_after_resignation);
        $exec_holiday_group_id = $this->Termination->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
        $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        $exec_days_holidays = $this->Termination->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
  and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$todate'; ");
        $days_after_resignation_att = isset($exec_days['0']['0']['a']) ? round($exec_days['0']['0']['a'],1) : 0;
        $diff_with_weekoff_days = $this->Termination->query("select weekoff_days_count_fn('$emp_pkey','$todate','$from') no_of_weekof");
        $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days['0']['0']['no_of_weekof'])+$exec_days_holidays['0']['0']['days_count'] : 0;
        $this->set("offs", $offs);
        $working_days = ($diff != 0)? $diff - $offs : 0;
        $prese = ($diff != 0) ?$diff - ($days_after_resignation_att+$offs) : 0 ;
        $bal = ($diff != 0)? ($diff - $offs) - ($diff - ($days_after_resignation_att+$offs)):0;
//        debug($working_days
//        );
        $arr_save_data = array();
        $arr_save_data['terminate_pkey'] = $details['0']['Termination']['terminate_pkey'];
        $arr_save_data['working_days_settled'] = $working_days;
        $arr_save_data['payroll_days'] = $prese;
        
        $arr_update_termination = $this->Termination->save($arr_save_data);
        
        
        if ($removes) {
            
            $arr_emp_salaries = $this->EmployeeDetails->query(" UPDATE `payroll_master` SET `approved` = 'Y' WHERE `emp_fkey` = '$emp_pkey' and  `approved` = 'N' and month_year > '2018-04'  ");
            $arr_emp_salaries = $this->EmployeeDetails->query(" UPDATE `leave_encashment_master` SET `salary_paid` = 'Y' WHERE `emp_fkey` = '$emp_pkey' ");
            return 'Employee Termination Successfull';
            
        } else {
            
            return 'Employee Termination failed please try again ';
            
        }
    }
    
    public function downloads($emp_pkey = 0,$dayss = 0,$leaves = 0,$gratuity = 0){
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        //debug($arr_comp_contact_info);
        $this->set("arr_comp_contact_info", $arr_comp_contact_info);
//        debug($arr_comp_contact_info);
        $from = date('Y-m');
        $arr_emps = $this->request->data;
        $dayss = $dayss;
        $leaves = $leaves;
        $details = $this->Termination->find("all", array("fields" => array("Termination.*,EmployeeDetails.branch_code,EmployeeInfo.*"), "conditions" => array("emp_fkey" => $emp_pkey), "joins" => array(
                array("table" => "employee_info",
                    "alias" => "EmployeeInfo",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeInfo.emp_pkey = Termination.emp_fkey")),
                array("table" => "emp_details",
                    "alias" => "EmployeeDetails",
                    "foreignKey" => false,
                    "type" => "LEFT",
                    "conditions" => array("EmployeeDetails.emp_pkey = Termination.emp_fkey"))
        )));
        $branch = $details['0']['EmployeeDetails']['branch_code'];
        $emp_id = 'ADMIN';
//        $encashment_master = $this->leaveencash($emp_pkey, $leaves, 0); 
        $encashment_master = "`emp_fkey` = '$emp_pkey' ";
        $leaveenmcashs = $this->Termination->query("select * from emp_encash_slip where $encashment_master  and end_date_effective is null ");

        
        $arr_emp_salaries = $this->Termination->query(" SELECT * FROM `payroll_master` WHERE `emp_fkey` = '$emp_pkey' and  `approved` = 'N' and month_year > '2018-04'  ORDER BY month_year ");
        $this->set("arr_emp_salaries", $arr_emp_salaries);
            
        $this->set("leaveenmcashs", $leaveenmcashs);
        $this->set("gratuity", $gratuity);
        $yearmonth = date('Y-m');
        $sdds = $this->Termination->query("CALL `final_settle_pay_prc`('$branch', '$yearmonth', '$emp_pkey', '$dayss', '$leaves', '$emp_id', @`perror_message`)");
        //debug("CALL `final_settle_pay_prc`('$branch', '$yearmonth', '$emp_pkey', '$dayss', '$leaves', '$emp_id', @`perror_message`)");
        $arr_empleaverequests = $this->Termination->query("select ectc.month_year,br.branch_name,ed.first_name,ed.last_name,ectc.* "
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
        $salaryslipwithoutcomponents = $this->Termination->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
                . "from emp_salary_slip as ectc "
                . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$emp_pkey' "
                . " and month_year ='$from' "
                . "and ed.status = 1 and "
                . "end_date_effective is null ");
        //debug($from);
        //($emp_pkey);
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->Termination->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                . "from payroll_master "
                . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                . "left join department as d on(d.dept_code = ep.emp_dept) "
                . "left join designation as dd on (dd.desig_code = ep.designation)"
                . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                . " and payroll_master.month_year ='$from'"
                . " and payroll_master.emp_fkey = '$emp_pkey' ");


        // debug($empdetails);

        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails
        );
        
        
        
        // Start of new line 
        
        
        
        $submitted_dat = $from = $details['0']['Termination']['submitted_date'];
        $newdate = $todate = $details['0']['Termination']['last_approved_working_date'];
        
        $date1 = date_create($newdate);
        $date2 = date_create($submitted_dat);
        $diff = date_diff($date1, $date2);
        
        $arr_att_days_after_resignation = "select sum(a) a from(
            select count(*) as a from emp_detail_timeattandance where others in ('LOP','LOP/LOP') and 	emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            union all
            select count(*)/2 as a from emp_detail_timeattandance where (instr(others,'/LOP') > 0 or instr(others,'LOP/') > 0 ) and others!= 'LOP'   and	 emp_pkey = '$emp_pkey' and att_date between '$submitted_dat' and '$newdate'
            )ass ";
        $exec_days = $this->Termination->query($arr_att_days_after_resignation);
        $exec_holiday_group_id = $this->Termination->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$emp_pkey ");
        $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        $exec_days_holidays = $this->Termination->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
  and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$todate'; ");
        $days_after_resignation_att = isset($exec_days['0']['0']['a']) ? round($exec_days['0']['0']['a'],1) : 0;
        $diff_with_weekoff_days = $this->Termination->query("select weekoff_days_count_fn('$emp_pkey','$todate','$from') no_of_weekof");
        $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days['0']['0']['no_of_weekof'])+$exec_days_holidays['0']['0']['days_count'] : 0;
        $this->set("offs", $offs);
        $this->set("days_after_resignation_att", $days_after_resignation_att);
//        debug($days_after_resignation_att);
        
        
        // end of new line 
        $arr_leaves_heads = $this->EmployeeDetails->query("SELECT salary_head_item_pkey,occurance FROM `salary_head_items` WHERE head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey') and status = 1 ) and occurance != 'LOP' LIMIT 50"); 
            $arr_leave = array();
//            DEBUG($arr_leaves_heads);
        $this->set('arr_leaves', $arr_leaves_heads);
        $this->set("diff", $diff->format("%a"));
        $month = date("m");
        $year = date("Y");
        $excess_leave = 0;
        foreach($arr_leaves_heads as $key => $val){
            $head = $val['salary_head_items']['occurance'];
            $slary_head_item_pkey = $val['salary_head_items']['salary_head_item_pkey'];
            $lbalance = $this->EmployeeDetails->query("select leave_balance_inthe_month_fn('$emp_pkey','$slary_head_item_pkey','$month','$year') as LeaveBalance");
            $resp = isset($lbalance['0']['0']['LeaveBalance'])?round($lbalance['0']['0']['LeaveBalance'],1):0;
            if($resp < 0){
                $excess_leave += $resp;
            }
        }
        $this->set("excess_leave", $excess_leave);
        
        
        
        //Leave Encashments 
        
        $leave_encash_amts_fetch = $this->EmployeeDetails->query("SELECT * FROM `emp_encash_slip` WHERE `emp_fkey` = '$emp_pkey' "); 
        
        $this->set("leave_encash_amts_fetch", $leave_encash_amts_fetch);
        
        $encash_sums = 0;
        foreach ($leave_encash_amts_fetch as $encashs){
            $encash_sums += $encashs['emp_encash_slip']['salary_amount'];
        }
        $this->set("encash_sums", $encash_sums);
        $joining_date_fetch = $this->EmployeeDetails->query("SELECT joining_date FROM `emp_proff` WHERE `emp_fkey` = '$emp_pkey' "); 
        $grativity = 0;
        if(round((time()-strtotime($joining_date_fetch['0']['emp_proff']['joining_date']))/(3600*24*365.25)) > 1){
            $gross_sal = $this->EmployeeDetails->query("select emp_anual_ctc/12 as gros_sal from emp_ctc_transaction where emp_fkey = '$emp_pkey' and end_date_effective is null "); 
            $years = round((time()-strtotime($joining_date_fetch['0']['emp_proff']['joining_date']))/(3600*24*365.25));
            $grativity = round(($gross_sal['0']['0']['gros_sal'] /26 * 15) * $years,1);
        }
        $this->set("grativity", $grativity);
        $excess_leav_amt = isset($gross_sal['0']['0']['gros_sal'])?$gross_sal['0']['0']['gros_sal'] /26 * abs(isset($excess_leave)?$excess_leave:0) : 0;
        $this->set("excess_leav_amt", $excess_leav_amt);
        
        
        $loans = $this->requestAction('/EmployeeLoan/getLoanBalance/' . $emp_pkey);
        $this->set("loans", $loans);
        //debug($arr_salary_for_template);
        $this->set("details", $details);
        $this->set("arr_salary_for_template", $arr_salary_for_template);
            //$content ="<h2>hi</h2>";
            $view = new View($this, false);
            $view_output = $view->render('download');
            
            $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('download');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
    }

    public function leaveencash($emp_pkey = 0, $applied = 0, $salary_head_item = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
        //$arr_data = $this->request->data;
        $emp_pkey = $emp_pkey;
        $arr_emps = $this->EmployeeDetails->find("all", array("conditions" => array("status" => 1, "emp_pkey" => $emp_pkey)));
        $branch_code = isset($arr_emps['0']['branch_code']) ? $arr_emps['0']['branch_code'] : 0;
        $cur_emp_key = $emp_pkey;

        $salary_head_items = $this->EmployeeDetails->query("select salary_head_item_fkey as leavepolicy from leavepolicy where is_leave_encash = 'Y' and leave_encash_limit is NOT NULL and status = '1' and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey in ('$cur_emp_key'))");
        $applied = $applied;
        $arr_data = array();
        if ($salary_head_items) {
            $salary = $salary_head_items['0']['leavepolicy']['leavepolicy'];
            $currentyear = $this->EmployeeDetails->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$cur_emp_key')");
            $finyears = isset($currentyear['0']) ? $currentyear['0']['fin_year']['fin_year'] : null;
            $emp_ncashes = $this->EmployeeDetails->query("select leave_balance_inthe_year_fn('$cur_emp_key',salary_head_items.salary_head_item_pkey,'$finyears') as yearlybalance,salary_head_items.item,leavepolicy.*,leavepolicy.LEAVEPOLICYID,leavepolicy.salary_head_item_fkey,leavepolicy.alloted_leave_forthe_year,emp_details.emp_pkey,emp_details.branch_code,concat(emp_details.first_name,' ',emp_details.last_name) as emp_name,leavepolicy.alloted_leave_forthe_month,leavepolicy.leave_encash_limit from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on (emp_details.emp_pkey = '$cur_emp_key') where LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$cur_emp_key') and leavepolicy.salary_head_item_fkey = '$salary' and leave_encash_limit is NOT NULL");

            
            $arr_data['emp_fkey'] = $emp_pkey = $emp_ncashes['0']['emp_details']['emp_pkey'];
            $arr_data['emp_name'] = $emp_ncashes['0']['0']['emp_name'];
            $arr_data['branch_code'] = $branch_code = $emp_ncashes['0']['emp_details']['branch_code'];
            $arr_data['salary_head_item_fkey'] = $emp_ncashes['0']['leavepolicy']['salary_head_item_fkey'];
            $arr_data['encash_days'] = $emp_ncashes['0']['leavepolicy']['leave_encash_limit'];
            $arr_data['available_days'] = min($emp_ncashes['0']['leavepolicy']['leave_encash_limit'], $emp_ncashes['0']['0']['yearlybalance']);
            $arr_data['requested_days'] = $applied;
            $arr_data['approved_days'] = $applied;
            $arr_data['created_by'] = $this->Session->read('user_name');
            $arr_data['creation_date'] = date('Y-m-d');
            $arr_data['modified_by'] = $this->Session->read('user_name');
            $arr_data['approved_by'] = '0';
            $arr_data['is_approved'] = 'Y';
            $arr_data['approved_date'] = date("Y-m-d");
        }
        $result = $this->LeaveEncashmentMaster->query("select * from leave_encashment_master where emp_fkey = '$emp_pkey' and salary_paid = 'N' ");
        if(count($result)>0){
            $arr_data['leave_encashment_master_pkey'] = isset($result['0']['leave_encashment_master']['leave_encashment_master_pkey'])?$result['0']['leave_encashment_master']['leave_encashment_master_pkey']:'';
        }
        if ($results = $this->LeaveEncashmentMaster->save($arr_data)) {
            $LeaveEncashmentMaster_id = $this->LeaveEncashmentMaster->getLastInsertId();
            if(count($result)>0){
                $LeaveEncashmentMaster_id = $arr_data['leave_encashment_master_pkey'];
            }
            $user = $this->Session->read('login_user_id');
            $emp_ncashes_approve = $this->EmployeeDetails->query("call leave_encash_prc('$branch_code','$emp_pkey','$LeaveEncashmentMaster_id','0','@msg')");
            return $LeaveEncashmentMaster_id;
        }
    }

    public function details_res($emp_pkey = 0) {
        $this->autoRender = false;
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $details = $this->Termination->find("all", array("conditions" => array("emp_fkey" => $emp_pkey)));
        if ($details) {
            echo json_encode(array('success' => 1,
                'applied_date' => $details['0']['Termination']['last_applied_date'],
                'submitted' => $details['0']['Termination']['submitted_date'],
                'Reason' => $details['0']['Termination']['Reason'],
                'approved' => $details['0']['Termination']['last_approved_working_date'],
                'last_reason' => $details['0']['Termination']['last_working_date'],
                'last_apprv' => $details['0']['Termination']['last_approved_working_date'],
                'LEAVEENTRYID' => $details['0']['Termination']['terminate_pkey'],
                'remarks' => $details['0']['Termination']['remarks'],
                'act_last_working_day' => $details['0']['Termination']['act_last_working_day'],
            ));
        } else {
            echo json_encode(array('success' => 0, 'days' => 0));
        }
    }

    public function getautocompletions() {
        $this->autoRender = false;

        $arr_request_data = $this->request->query; //$site_pkey=[];
        //  debug($arr_request_data);
        if ($arr_request_data['branch'] != '') {

            $searchkey = $arr_request_data['username'];
            $branch = $arr_request_data['branch'];
            $filter_condition = 'branch_code = "' . $branch . '" and first_name LIKE "%' . $searchkey . '%"';
        } else {
            $searchkey = $arr_request_data['username'];
            $filter_condition = 'first_name LIKE "%' . $searchkey . '%"';
        }
        //    debug($filter_condition);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_Emp = $this->EmployeeDetails->find('all', array('fields' => 'emp_pkey,emp_name,branch_code', 'conditions' => array('status' => 1, $filter_condition)));

        //debug($arr_Emp);
        $arr_filterresult = array(
            array(
                'emp_pkey' => '',
                'emp_name' => 'ALL',
            )
        );
        foreach ($arr_Emp as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? $val['EmployeeDetails'] : array();
        }
        //debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */

    public function listemployees() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'terminate_pkey';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';

        $ofst = ($page - 1) * $limit;

        $fields = 'Termination.*,EmployeeDetails.*,CONCAT_WS(" ",first_name,last_name," - ",EmployeeProffessional.emp_company_id) as name,EmployeeDetails.status as statuses';
        $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = Termination.emp_fkey')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProffessional',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProffessional.emp_fkey')
            )
        );
        $conditions = array('Termination.status' => 1);

        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->Termination->find("count", array('joins' => $joins, "conditions" => $conditions));
        $arr_emp = $this->Termination->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            "conditions" => $conditions,
            'order' => array($sort => $order),
            'limit' => intval($limit),
            'offset' => intval($ofst)
                )
        );
        // debug($arr_emp);
        foreach ($arr_emp as $key => $value) {
            $resp_emp["rows"][$key] = array_merge($value["EmployeeDetails"], $value["Termination"], $value[0]);
        }
        //  debug($resp_emp["rows"][$key]);
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

    public function workingattendnacedays() {
        $this->autoRender = false;
        $arr_emps = $this->request->data;
        $arr_employee = $arr_emps['emp'];

        $todate = $arr_emps['todate'];

        $from = $arr_emps['from'];
        $submitted_dat = date("Y-m-d", strtotime($from));
        $newdate = date("Y-m-d", strtotime($todate));

        $this->Departments->useDbConfig = $this->Session->read('ds');
        $arr_atts = $this->Departments->query("select sum(presant_total)+sum(leave_total) presant_total from attendance_register where month_year >=(
   select max(month_year) from payroll_master where action  in('Approved','Processed') and  emp_fkey = '$arr_employee')  and emp_fkey = '$arr_employee' ");

        $arr_total_work_dats = $arr_atts['0']['0']['presant_total'];
        
        $leavepolicy_encashed = $this->Departments->query("select salary_head_item_fkey from  leavepolicy where is_leave_encash = 'Y' and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey = '$arr_employee')");
        
        $leavebalance = 0;
        $warningd = "";
        $currentyear = $this->Departments->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$arr_employee')");
        $finyears = isset($currentyear['0'])?$currentyear['0']['fin_year']['fin_year']:null;
        
        
        if(!$finyears){
            $warningd = "you need to provide a Leave Year for this Employee ";
        }
        
        foreach ($leavepolicy_encashed as $val){
            
            $salary_head_item_fkey = $val['leavepolicy']['salary_head_item_fkey'];
            $arr_leav = $this->Departments->query("select leave_balance_inthe_year_fn('$arr_employee','$salary_head_item_fkey','$finyears')  leaves");
            $leavebalance += isset($arr_leav['0']['0']['leaves']) ? $arr_leav['0']['0']['leaves'] : '0';
//            debug($arr_leav); 
            
        }
        
        
        $attendance_days = isset($arr_atts['0']['0']['presant_total']) ? $arr_atts['0']['0']['presant_total'] : '0';

        $arr_att_days_after_resignation = "select sum(a) a from(
            select count(*) as a from emp_detail_timeattandance where others in ('LOP','LOP/LOP') and 	emp_pkey = '$arr_employee' and att_date between '$submitted_dat' and '$newdate'
            union all
            select count(*)/2 as a from emp_detail_timeattandance where (instr(others,'/LOP') > 0 or instr(others,'LOP/') > 0 ) and others!= 'LOP'   and	 emp_pkey = '$arr_employee' and att_date between '$submitted_dat' and '$newdate'
            )ass ";
        $exec_days = $this->Departments->query($arr_att_days_after_resignation);
        $exec_holiday_group_id = $this->Departments->query("select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$arr_employee ");
        $holiday_group_id = $exec_holiday_group_id['0']['emp_proff']['HOLIDAY_GROUP_ID'];
        $exec_days_holidays = $this->Departments->query("select count(*) as days_count from holidays where HOLIDAY_GROUP_ID = '$holiday_group_id' 
  and HOLIDAYDATE  between DATE_FORMAT('$from' , '%Y-%m-%d') and '$todate'; ");
        $days_after_resignation_att = isset($exec_days['0']['0']['a']) ? round($exec_days['0']['0']['a'],1) : 0;
        $diff_with_weekoff_days = $this->Departments->query("select weekoff_days_count_fn('$arr_employee','$from','$todate') no_of_weekof");
        $offs = isset($diff_with_weekoff_days['0']['0']['no_of_weekof']) ? abs($diff_with_weekoff_days['0']['0']['no_of_weekof'])+$exec_days_holidays['0']['0']['days_count'] : 0;
        echo json_encode(array('success' => 1,'Warningd'=>$warningd, 'atte' => $attendance_days, 'leave' => $leavebalance, 'weekoff_couts' => $offs, "attendance_days_after_resignation" => $days_after_resignation_att));
    }

    public function setup($emp_pkey = 0) {
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $this->set("emp_pkey", $emp_pkey);
        $termination_details = $arr_emp = $this->Termination->find("all", array("conditions" => array("terminate_pkey" => $emp_pkey)));
        $this->set("termination_details", $termination_details);
        $emp = $termination_details['0']['Termination']['emp_fkey'];
        $last_approved_wd = $termination_details['0']['Termination']['last_approved_working_date'];
        if($last_approved_wd > date("Y-m-d")){
            echo '<div class="callout callout-danger "> You can process the full and final on or after employees last approved working date - '.$last_approved_wd.' </div>';
            die();
        }
        $loans = $this->Termination->query("select * from emp_loan where emp_fkey = '$emp' and is_completed = 'N' ");
        
        $last_approved = date("Y-m-",strtotime($last_approved_wd));

        $unverified_attendances_proccess = $this->Termination->query("SELECT count(*) as COUNTS FROM emp_settle_slip WHERE `emp_fkey` = '$emp'  ");
        
        if($unverified_attendances_proccess['0']['0']['COUNTS'] > 0){
            echo '<div class="callout callout-danger "> Termination Already Proccesed  </div>';
            die();
        }
        
        $unverified_attendances = $this->Termination->query("SELECT count(*) as COUNTS,month_year FROM attendance_register WHERE `emp_fkey` = '$emp' and isdelete = 'Y' and month_year > '2018-04' and month_year < '$last_approved' ");
        
        if($unverified_attendances['0']['0']['COUNTS'] > 0){
            echo '<div class="callout callout-danger "> Please Approve all attendance of this employee ('.$unverified_attendances[0]['attendance_register']['month_year'].' )</div>';
            die();
        }
        //debug($loans);
        $assets = $this->Termination->query("select asset_allocate.*,asset_management.* from asset_allocate left join asset_management on (asset_management.asset_pkey = asset_allocate.asset) where asset_allocate.emp_fkey = '$emp' and asset_allocate.damaged_amout is not null ");
        $arr_ln = array();
        foreach ($loans as $val) {
            $loan_pkey = $val['emp_loan']['emp_loan_pkey'];
            $loan = $this->Termination->query("select * from emp_loan_info where emp_loan_info_pkey in (select min(emp_loan_info_pkey) from emp_loan_info where loan_pkey = $loan_pkey and amount_paid = 0 and status =1) and status =1");
            $val['summary'] = $loan;
            $arr_ln[] = $val;
        }
        //$advances = $this->Termination->query("select * from emp_advance where emp_fkey = '$emp' and is_credited = 'N' and status = 1 ");
        //debug($arr_ln);
        $this->set("arr_ln", $arr_ln);
        $this->set("assets", $assets);
        //$this->set("advances",$advances);
    }

    /*
     * Show tax Head Details form
     */

    public function Terminate() {

        $this->Termination->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
        $save_data = array();
        try {
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
            $this->autoRender = FALSE;
            //$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            //$this->EmployeeDetails->updateAll(array("status"=>2),array("emp_pkey"=>$emp_fkey));
            $arr_form_data1 = array();
            $arr_form_data1['emp_pkey'] = $arr_form_data['emp_fkey'];
            $arr_form_data1['status'] = 2;
            $e = "";

            //reference
            //--trigger_error("Error message to display", E_USER_ERROR);
            //E_USER_ERROR,E_USER_DEPRECATED,E_USER_NOTICE,E_USER_WARNING
            //set_error_handler($error_handler, E_USER_ERROR);

 

            if (!$this->Termination->save($save_data)) {
                throw new Exception("Data cannot be saved, Please try again");
            }
        } catch (ErrorException $e) {
            $e->getMessage();
        } catch (Exception $e) {
            $e->getMessage();
        } catch (mysqli_sql_exception $m) {
            $m->getMessage();
        }
        echo json_encode(array("success" => 1, "msg" => "Successfully Terminated Employee", "error" => $e));
    }

}
