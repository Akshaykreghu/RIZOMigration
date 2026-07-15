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
class LeaveEncashmentRequestController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'LeaveEncashmentRequest';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('AppModel','LeaveRequests', 'SalaryHeadItems', 'EmployeeDetails','EmployeeLeaveTransaction','LeavePolicy','Units','LeaveEncashmentMaster','HolidayGroup');
    public $components = array('Session','MasterdataManagement');

    /*
     * Leave List Landing Page
     */
    public function index() {
        $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        
        //My Leaves count
        $leave_count = $this -> LeaveRequests -> find('count', array('conditions' => array('EMP_fkey' => $cur_emp_key)));
        $this -> set('leave_count', $leave_count);     
    }
    
    /*
     * Leave List Landing Page
     */
    public function employeeleaves() {
        $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        
        //Employee Leaves count
        //On 10 Aug 2015
        $emp_leave_count = $this -> LeaveRequests -> find('count', 
            array(
                'conditions' =>  array (
                    'OR' => array(
                        array('ISAutherizedby' => $cur_emp_key,'LEAVESTATUS IN("Applied","Authorized")'),
                        array('APPROVEDBY' => $cur_emp_key,'LEAVESTATUS IN ("Authorized","Approved","Rejected")'),
                    )
                )
            )
        );
        $this -> set('emp_leave_count', $emp_leave_count);
    }
    
    /*
     * Employee Leave Lists
     * By santhosh on 21 March 2015
     */
    /*public function listempleaves() {
        $sessionObj = $this -> Session -> read("Auth.User");
        $cur_emp_key = $sessionObj['emp_fkey'];

        $columns = array( array('db' => 'LEAVEENTRYID', 'dt' => 0), array('db' => 'emp_name', 'dt' => 1), array('db' => 'leave_type', 'dt' => 2), array('db' => 'applied_date', 'dt' => 3), array('db' => 'FROMDATE', 'dt' => 4), array('db' => 'TODATE', 'dt' => 5), array('db' => 'LEAVESTATUS', 'dt' => 6));
        $this -> datatable["fields"] = 'LEAVEENTRYID, CONCAT(first_name, " ", last_name) AS emp_name, SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';
        $this -> datatable["joins"] = array( array('table' => 'salary_head_items', 'alias' => 'SalaryHeadItems', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')), array('table' => 'emp_details', 'alias' => 'EmployeeDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey')));
        $this -> datatable["conditions"] = array (
                        'OR' => array(
                            array('ISAutherizedby' => $cur_emp_key),
                            array('APPROVEDBY' => $cur_emp_key),
                        )
                    );

        $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
        echo json_encode($this -> DataTable -> getData('LeaveRequests', $columns));
        $this -> autoRender = FALSE;
    }*/
    /*
     * List employee leave requests
     * Added on 23 April 2015
     */  
    
     public function addleave()
    {
        $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
        // $this->set('arr_branches', $arr_branches);
        $user_group = $this->Session->read('user_group');
        $emp_pkey = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds'); // Edited by Akshay on 1-2-2025
        $company_code = $this->Session->read('company_code');
        $emp_list = $this->LeaveEncashmentMaster->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details 
        join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 order by first_name');
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $this->set('is_ho',$is_ho);
            if ($is_ho != 1) {

                $branch_code_to_find = $is_ho; // Specific branch code
                $arr_branches = array_filter(
                    $arr_branches,
                    function ($branch) use ($branch_code_to_find) {
                        return $branch['branch_code'] === $branch_code_to_find;
                    }
                );
                $arr_branches = array_values($arr_branches);
                $emp_list = $this->EmployeeDetails->query("SELECT emp_pkey, first_name, last_name, emp_company_id 
                FROM emp_details 
                JOIN emp_proff ON emp_details.emp_pkey = emp_proff.emp_fkey 
                WHERE emp_details.status = 1 AND emp_details.branch_code = '$is_ho'
                ORDER BY first_name ASC");
            }
        }
        $this->set('arr_branches', $arr_branches);
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
        $arr_heads = $this->LeaveEncashmentMaster->query("select salary_head_items.item,leavepolicy.salary_head_item_fkey
            from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) 
            where leavepolicy.status = 1  and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID from emp_proff ) and  is_leave_encash ='Y' and leave_encash_limit is NOT NULL group by salary_head_item_fkey");
        $this->set('arr_heads', $arr_heads);

        $this->set("emp_list", $emp_list);
        $this->set("company_code", $company_code); // Edited by Akshay on 28-10-2025
    }
    public function listappliedleaveencashs(){
        $this->autoRender = false;
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $arr_empleaverequests = $this -> LeaveEncashmentMaster ->find("all",array("fields"=>array("LeaveEncashmentMaster.*,SalaryHeadItems.item"),"joins"=>array(array("type"=>"left","table"=>"salary_head_items","conditions"=>array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey"),"alias"=>"SalaryHeadItems")),"conditions"=>array('is_approved'=>'Y','LeaveEncashmentMaster.status'=>'1')));
        //debug($arr_empleaverequests);
        $resp_empleaverequests = array();
        $resp_empleaverequests["rows"]= array();
        foreach ($arr_empleaverequests as $key => $value) {
            $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveEncashmentMaster"],$value["SalaryHeadItems"]);
            
            
        }
        $resp_empleaverequests["total"] = count($arr_empleaverequests);
        echo json_encode($resp_empleaverequests);
    }

    public function editvalue(){
        $this->autoRender = false;
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $arr_form_data = $this->request->data;
        $leaveencashfkey = $arr_form_data['leaveencashfkey'];
        $values = $arr_form_data['values'];
        $arr_empleaverequests = $this -> LeaveEncashmentMaster ->query("update leave_encashment_master set requested_days = '$values' , approved_days = '$values' where  is_approved !='Y' and status = 1 and leave_encashment_master_pkey= $leaveencashfkey ");
        $arr_empleave[] = array();
        $arr_empleave['msg']=true;
        echo json_encode($arr_empleave);
    }
    
    public function listallempsforenc($id = 0){
       $this->autoRender = false;
       $arr_request = $this->request->data;
       
       $this -> Units -> useDbConfig = $this -> Session -> read('ds');
       $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
       $limit = isset($arr_request['rows'])?$arr_request['rows']:'';
       $page = $arr_request['page'];
       $ofst = ($page - 1) * $limit;
       $smonth = isset($arr_request['month'])?$arr_request['month']:date('Y-m-d');
       $month = isset($arr_request['month'])?date('Y-m',strtotime($arr_request['month'])):date('Y-m');
       $branch = isset($arr_request['branch'])?$arr_request['branch']:'';
       $emp = isset($arr_request['id'])?$arr_request['id']:'';
//       if (isset($month) && $month != '') {
//            $conditions3 = ' and "'.$month.'" = "'.date('Y-m',strtotime('leave_encashment_master.creation_date')).'"';
//       }else{
//	    $conditions3 = '';
//       }
       if (isset($branch) && $branch != '') {
            $conditions2 = ' and leave_encashment_master.branch_code ="'.$branch.'"';
       }else{
	    $conditions2 = '';
       }
       if (isset($emp) && $emp != '') {
            $conditions1 = ' and leave_encashment_master.emp_fkey =' . $emp;
       }else{
	    $conditions1 = '';
       }
       if (isset($_REQUEST['item']) && $_REQUEST['item'] != '') {
            $conditions = ' and leave_encashment_master.salary_head_item_fkey =' . $arr_request['item'];
       }else{
	    $conditions = '';
       }
       $resp_empleaverequests = array();
       $employess = $this->Units->query("select leave_encashment_master.leave_encashment_master_pkey,leave_encashment_master.salary_paid,leave_encashment_master.branch_code,leave_encashment_master.emp_fkey,"
               . "leave_encashment_master.emp_name,leave_encashment_master.salary_head_item_fkey,leave_encashment_master.encash_days as leave_encash_limit,leave_encashment_master.available_days as eligible, "
               . "emp_proff.emp_company_id as emp_company_id,salary_head_items.item,payroll_master.action from leave_encashment_master "
               . "left join salary_head_items on (salary_head_items.salary_head_item_pkey = leave_encashment_master.salary_head_item_fkey) "
               . "left join emp_proff on (leave_encashment_master.emp_fkey = emp_proff.emp_fkey) "
               . "left join emp_details on (leave_encashment_master.emp_fkey = emp_details.emp_pkey) "
               . "left join payroll_master on (emp_details.emp_pkey = payroll_master.emp_fkey and payroll_master.month_year = '$month') "
               . "where leave_encashment_master.is_approved !='Y' and leave_encashment_master.status=1 and emp_details.status=1 and emp_proff.joining_date <= '$smonth' "
             //. "and emp_proff.emp_fkey not in(select emp_fkey from payroll_master where  month_year ='$month' and action is not null)"
               . " $conditions2 $conditions1 $conditions "
               . "order by emp_name limit $ofst,$limit");
       $emp_ncashes_count = $this -> LeaveEncashmentMaster ->query("select count(*) as count from leave_encashment_master "
               . "left join salary_head_items on (salary_head_items.salary_head_item_pkey = leave_encashment_master.salary_head_item_fkey) "
               . "left join emp_proff on (leave_encashment_master.emp_fkey = emp_proff.emp_fkey) "
               . "left join emp_details on (leave_encashment_master.emp_fkey = emp_details.emp_pkey) "
               . "left join payroll_master on (emp_details.emp_pkey = payroll_master.emp_fkey and payroll_master.month_year = '$month') "
               . "where leave_encashment_master.is_approved !='Y' and leave_encashment_master.status=1 and emp_details.status=1 and emp_proff.joining_date <= '$smonth' "
               . " $conditions2 $conditions1 $conditions ");
       
       foreach ($employess as $key => $value) {
           $arra_dsd = array_merge($value['leave_encashment_master'],$value["salary_head_items"],$value['emp_proff'],$value['payroll_master']);
           $resp_empleaverequests["rows"][] = $arra_dsd;
       } 
       
       $resp_empleaverequests["total"] = $emp_ncashes_count['0']['0']['count'];
      
       echo json_encode($resp_empleaverequests);
    }
    
    public function listallempsforencash(){
       $this->autoRender = false;
       $arr_request = $this->request->data;
       $this -> Units -> useDbConfig = $this -> Session -> read('ds');
       $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
       $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
       $limit = isset($arr_request['rows'])?$arr_request['rows']:'';
       $page = $arr_request['page'];
       $ofst = ($page - 1) * $limit;
       //$smonth = isset($arr_request['month'])?$arr_request['month']:date('Y-m-d');
       $smonth = isset($arr_request['month']) ? date('Y-m-t', strtotime($arr_request['month'] . '-01')) : date('Y-m-t');
       $month = isset($arr_request['month'])?date('Y-m',strtotime($arr_request['month'])):date('Y-m');
       $branch = isset($arr_request['branch'])?$arr_request['branch']:'';
       $emp = isset($arr_request['emp'])?$arr_request['emp']:'';
//       if (isset($month) && $month != '') {
//            $conditions3 = ' and "'.$month.'" = "'.date('Y-m',strtotime('leave_encashment_master.creation_date')).'"';
//       }else{
//	    $conditions3 = '';
//       }
       if (isset($branch) && $branch != '') {
            $conditions2 = ' and leave_encashment_master.branch_code ="'.$branch.'"';
       }else{
	    $conditions2 = '';
       }
       if (isset($emp) && $emp != '') {
            $conditions1 = ' and leave_encashment_master.emp_fkey =' . $emp;
       }else{
	    $conditions1 = '';
       }
       if (isset($_REQUEST['item']) && $_REQUEST['item'] != '') {
            $conditions = ' and leave_encashment_master.salary_head_item_fkey =' . $arr_request['item'];
       }else{
	    $conditions = '';
       }
       $resp_empleaverequests = array();
       $employess = $this->Units->query("select leave_encashment_master.leave_encashment_master_pkey,leave_encashment_master.salary_paid,leave_encashment_master.branch_code,leave_encashment_master.emp_fkey,"
               . "leave_encashment_master.emp_name,leave_encashment_master.salary_head_item_fkey,leave_encashment_master.encash_days as leave_encash_limit,leave_encashment_master.available_days as eligible, "
               . "emp_proff.emp_company_id as emp_company_id,salary_head_items.item,payroll_master.action from leave_encashment_master "
               . "left join salary_head_items on (salary_head_items.salary_head_item_pkey = leave_encashment_master.salary_head_item_fkey) "
               . "left join emp_proff on (leave_encashment_master.emp_fkey = emp_proff.emp_fkey) "
               . "left join emp_details on (leave_encashment_master.emp_fkey = emp_details.emp_pkey) "
               . "left join payroll_master on (emp_details.emp_pkey = payroll_master.emp_fkey and payroll_master.month_year = '$month') "
               . "where leave_encashment_master.is_approved !='Y' and leave_encashment_master.status=1 and emp_details.status=1 and emp_proff.joining_date <= '$smonth' "
             //. "and emp_proff.emp_fkey not in(select emp_fkey from payroll_master where  month_year ='$month' and action is not null)"
               . " $conditions2 $conditions1 $conditions "
               . "order by emp_name limit $ofst,$limit");
       $emp_ncashes_count = $this -> LeaveEncashmentMaster ->query("select count(*) as count from leave_encashment_master "
               . "left join salary_head_items on (salary_head_items.salary_head_item_pkey = leave_encashment_master.salary_head_item_fkey) "
               . "left join emp_proff on (leave_encashment_master.emp_fkey = emp_proff.emp_fkey) "
               . "left join emp_details on (leave_encashment_master.emp_fkey = emp_details.emp_pkey) "
               . "left join payroll_master on (emp_details.emp_pkey = payroll_master.emp_fkey and payroll_master.month_year = '$month') "
               . "where leave_encashment_master.is_approved !='Y' and leave_encashment_master.status=1 and emp_details.status=1 and emp_proff.joining_date <= '$smonth' "
               . " $conditions2 $conditions1 $conditions ");
               //edited by athira on 29-09-2025
       $company_code = $this->Session->read('company_code');
      $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
       foreach ($employess as $key => $value) {
    $emp_fkey = $value['leave_encashment_master']['emp_fkey'];
    $item     = $value['leave_encashment_master']['salary_head_item_fkey'];

    $arra_dsd = array_merge($value['leave_encashment_master'], $value['salary_head_items'], $value['emp_proff'], $value['payroll_master']);

    // Get employee-specific leave policy
    $policy = $this->LeavePolicy->find('first', [
        'conditions' => [
            'salary_head_item_fkey' => $item,
            'status' => 1,
            "LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = $emp_fkey)"
        ]
    ]);

    $leave_limit = $policy['LeavePolicy']['leave_encash_limit'];
    $cycle_start = $policy['LeavePolicy']['leave_cycle_start_date'];
    $cycle_end   = $policy['LeavePolicy']['leave_cycle_end_date'];

    // Already encashed in current cycle
    $already_encashed = $this->LeaveEncashmentMaster->find('first', [
        'fields' => ['SUM(approved_days) as total'],
        'conditions' => [
            'emp_fkey' => $emp_fkey,
            'salary_head_item_fkey' => $item,
            'is_approved' => 'Y',
            'approved_date >=' => $cycle_start,
            'approved_date <=' => $cycle_end
        ]
    ]);

    $already = $already_encashed[0]['total'];

    $arra_dsd['already_encashed'] = $already;
    $arra_dsd['leave_limit'] = $leave_limit; // send to frontend
    $arra_dsd['can_encash'] = ($already < $leave_limit) ? true : false;

    $resp_empleaverequests["rows"][] = $arra_dsd;
    }
    }else{
       foreach ($employess as $key => $value) {
           $arra_dsd = array_merge($value['leave_encashment_master'],$value["salary_head_items"],$value['emp_proff'],$value['payroll_master']);
           $resp_empleaverequests["rows"][] = $arra_dsd;
       } 
    }
       //end
       $resp_empleaverequests["total"] = $emp_ncashes_count['0']['0']['count'];
       echo json_encode($resp_empleaverequests);
    }
//    public function listallempsforencash(){
//       $this->autoRender = false;
//       $arr_request = $this->request->data;
//       $id = isset($arr_request['id'])?$arr_request['id']:'';
//       if($id){
//           $employess = explode('&', $arr_request['id']);
//       }
//       if($arr_request['rows']){
//           $limit = $arr_request['rows']; 
//       }
//       $page = $arr_request['page'];
//       $ofst = ($page - 1) * $limit;
//       $this -> Units -> useDbConfig = $this -> Session -> read('ds');
//       $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
//       //$month = date('Y-m-d');
//       $month = $arr_request['month'];
//       $branch = isset($arr_request['branch'])?$arr_request['branch']:'';
//       $emp = isset($arr_request['emp'])?$arr_request['emp']:'';
//       if (isset($branch) && $branch != '') {
//            $conditions2 = ' emp_details.branch_code ="'.$branch.'"';
//       }else{
//	    $conditions2 = ' emp_details.branch_code is not null';
//       }
//       if (isset($emp) && $emp != '') {
//            $conditions1 = ' and emp_details.emp_pkey =' . $emp;
//       }else{
//	    $conditions1 = '';
//       }
//       
//       
//       $employess = $this->Units->query("select emp_pkey from emp_details where $conditions2 $conditions1 and emp_details.status = 1 ");
//       
//       if (isset($_REQUEST['item']) && $_REQUEST['item'] != '') {
//            $conditions = ' and salary_head_item_pkey =' . $arr_request['item'];
//       }else{
//	    $conditions = '';
//       }
//       
//       
//       $resp_empleaverequests = array();
//       $arra_dsd = array();
//       $count = 0;
//       $resp_empleaverequests["rows"]= array();
//       foreach($employess as $key => $emp){
//           //$curr_emp = explode('=', $emp)['0'];
//           $curr_emp = $emp['emp_details']['emp_pkey'];
//           $currentyear = $this->Units->query("select * from fin_year where Year_status in('OPEN') and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$curr_emp')");
//           $finyears = isset($currentyear['0'])?$currentyear['0']['fin_year']['fin_year']:null;
//           //$emp_ncashes = $this -> LeaveEncashmentMaster ->query("select leave_balance_inthe_year_fn('$curr_emp',salary_head_items.salary_head_item_pkey,
//           //'$finyears') as yearlybalance,salary_head_items.item,leavepolicy.*,leavepolicy.LEAVEPOLICYID,leavepolicy.salary_head_item_fkey,
//           //leavepolicy.alloted_leave_forthe_year,emp_details.emp_pkey,concat(emp_details.first_name,' ',emp_details.last_name) as emp_name,
//           //leavepolicy.alloted_leave_forthe_month,leavepolicy.leave_encash_limit from leavepolicy left join salary_head_items on 
//           //(salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on (emp_details.emp_pkey = '$curr_emp') 
//           //where leavepolicy.status = 1 and emp_details.emp_pkey NOT IN (select emp_fkey from leave_encashment_master WHERE 
//           //leave_encashment_master.emp_fkey = emp_details.emp_pkey and leave_encashment_master.is_approved='Y' and 
//           //leave_encashment_master.salary_head_item_fkey = leavepolicy.salary_head_item_fkey and leave_encashment_master.fin_year = '$finyears' and 
//           //leave_encashment_master.status=1) and LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$curr_emp') and 
//           //is_leave_encash ='Y' and leave_encash_limit is NOT NULL");
//	   $emp_ncashes = $this -> LeaveEncashmentMaster ->query("select leave_balance_inthe_month_fn('$curr_emp',salary_head_items.salary_head_item_pkey,"
//                   . "'$month','$finyears') as monthlybalance,leave_balance_inthe_year_fn('$curr_emp',salary_head_items.salary_head_item_pkey,'$finyears') "
//                   . "as yearlybalance,salary_head_items.item,leavepolicy.*,leavepolicy.LEAVEPOLICYID,leavepolicy.salary_head_item_fkey,emp_proff.emp_company_id as emp_company_id,"
//                   . "leavepolicy.alloted_leave_forthe_year,emp_details.emp_pkey,concat(emp_details.first_name,' ',emp_details.last_name) as emp_name,"
//                   . "leavepolicy.alloted_leave_forthe_month,leavepolicy.leave_encash_limit from leavepolicy left join salary_head_items on "
//                   . "(salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on "
//                   . "(emp_details.emp_pkey = '$curr_emp') left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where leavepolicy.status = 1  "
//                   . "and leavepolicy.LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from "
//                   . "emp_proff where emp_fkey = '$curr_emp') and is_leave_encash ='Y' $conditions and leave_encash_limit is NOT NULL ");
//           $emp_ncashes_count = $this -> LeaveEncashmentMaster ->query("select count(*) as count from leavepolicy left join salary_head_items on "
//                   . "(salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on "
//                   . "(emp_details.emp_pkey = '$curr_emp') left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where leavepolicy.status = 1  "
//                   . "and leavepolicy.LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from "
//                   . "emp_proff where emp_fkey = '$curr_emp') and is_leave_encash ='Y' $conditions and leave_encash_limit is NOT NULL ");
//           $count = $count + count($emp_ncashes);
//		   //debug($this->LeaveEncashmentMaster->getLastQuery());
//           foreach ($emp_ncashes as $key => $value) {
//			  
//            $arra_dsd = array_merge($value['0'],$value["salary_head_items"],$value["leavepolicy"],$value['emp_details'],$value['emp_proff']);
//            $emp = $arra_dsd['emp_pkey']; 
//            $head = $arra_dsd['salary_head_item_fkey'];		
//			$terminate = $this->LeaveEncashmentMaster->query("select count(*) as count from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where remarks = 'terminate' and salary_paid = 'N' and leave_encashment_master.status = 1 and emp_fkey = '$emp' and emp_details.status = 2");
//			$encash = $this->LeaveEncashmentMaster->query("select count(*) as count,sum(approved_days) as sum from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where (remarks != 'terminate' or remarks is null) and leave_encashment_master.status = 1 and leave_encashment_master.is_approved ='Y' and emp_fkey = '$emp' and emp_details.status = 1 and leave_encashment_master.approved_date between '$month' and LAST_DAY('$month') and salary_head_item_fkey = '$head'");
//			$encash_yearly = $this->LeaveEncashmentMaster->query("select count(*) as count,sum(approved_days) as sum from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where (remarks != 'terminate' or remarks is null) and leave_encashment_master.status = 1 and leave_encashment_master.is_approved ='Y' and emp_fkey = '$emp' and emp_details.status = 1 and DATE_FORMAT(leave_encashment_master.approved_date, '%Y') = '$finyears' and salary_head_item_fkey = '$head'");
//			$date= date('Y-m', strtotime($month));
//                        $encash_payroll = $this->LeaveEncashmentMaster->query("select action from payroll_master where month_year = '$date' and emp_fkey = '$emp'");
//                        $payroll = isset($encash_payroll['0']['payroll_master']['action'])?$encash_payroll['0']['payroll_master']['action']:'';
//                        $arra_dsd['payroll'] = $payroll;
//                        $limit = isset($arra_dsd['leave_encash_limit'])?$arra_dsd['leave_encash_limit']:0;
//			$sum = isset($encash_yearly['0']['0']['sum'])?$encash_yearly['0']['0']['sum']:0;
//			$leave_bal = $limit - $sum;
//			if($arra_dsd['ALLOW_NEGETIVE'] == 'Y'){
//				$bal = $arra_dsd['yearlybalance'] ;
//			}else{
//				$bal = $arra_dsd['monthlybalance'];
//			}
//			if($terminate['0']['0']['count'] == 0){
//            $arra_dsd['eligible'] = min($bal,$leave_bal);
//			}else{
//			$arra_dsd['eligible'] = 0;	
//			}
//            $arra_dsd['editing'] = true;
//            $arra_dsd['applied'] = min($bal,$leave_bal);
//            $arra_dsd['fin_year'] = $finyears;
//                $resp_empleaverequests["rows"][] = $arra_dsd;
//            }
//       }
//        
    
        
//        //foreach ($arr_empleaverequests as $key => $value) {
//          //  $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveEncashmentMaster"],$value["SalaryHeadItems"]);
//            
//            
//       // }
//        $resp_empleaverequests["total"] = $count;
//        echo json_encode($resp_empleaverequests);
//    }
    
    public function listallempsforverified(){
       $this->autoRender = false;
       $arr_request = $this->request->data;
       $id = isset($arr_request['id'])?$arr_request['id']:'';
       $month = isset($arr_request['month'])?$arr_request['month']:date('Y-m-d');
       if($id){
           $employess = explode('&', $arr_request['id']);
       }
       
       $this -> Units -> useDbConfig = $this -> Session -> read('ds');
       $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
       $id = isset($arr_request['id'])?$arr_request['id']:'';
       $branch = isset($arr_request['branch'])?$arr_request['branch']:'';
       $emp = isset($arr_request['emp'])?$arr_request['emp']:'';
       $head = isset($arr_request['item'])?$arr_request['item']:'';
       if (isset($branch) && $branch != '') {
            $conditions2 = ' and LeaveEncashmentMaster.branch_code ="'.$branch.'"';
            $branch2 = ' and emp_details.branch_code ="'.$branch.'"';
       }else{
	    $conditions2 = '';
            $branch2 = '';
       }
       if (isset($emp) && $emp != '') {
            $conditions1 = ' and LeaveEncashmentMaster.emp_fkey =' . $emp;
            $emp1 = ' and emp_details.emp_pkey =' . $emp;
       }else{
	    $conditions1 = '';
            $emp1 = '';
       }
       if (isset($head) && $head != '') {
            $conditions = ' and LeaveEncashmentMaster.salary_head_item_fkey =' . $head;
            $head1 = $head;
       }else{
	    $conditions = '';
            $head1 = 'LeaveEncashmentMaster.salary_head_item_fkey';
       }
       $employess = $this->Units->query("select emp_pkey from emp_details where   emp_details.status = 1 $emp1 $branch2");
       
       
       //$limit = 10;
       if($arr_request['rows']){
           $limit = $arr_request['rows'];
       }
       $page = $arr_request['page'];
       $ofst = ($page - 1) * $limit;
       
       //debug($employess);
       $resp_empleaverequests = array();
       $arra_dsd = array();
        $resp_empleaverequests["rows"]= array();
       foreach($employess as $key => $emp){
           //$curr_emp[] = explode('=', $emp)['0'];
           $curr_emp[] = $emp['emp_details']['emp_pkey'];
           }
           //debug(implode(",",$curr_emp));
          // DEBUG($month);
          // $month = '2025-02';
$month = date('Y-m-d', strtotime($month . '-01'));
           $arr_leaves = $this->LeaveEncashmentMaster->query("SELECT LeaveEncashmentMaster.*,SalaryHeadItems.item,Emp_Proff.emp_company_id,"
                   . "(select action from payroll_master where month_year = date_format('$month','%Y-%m') and emp_fkey = `LeaveEncashmentMaster`.`emp_fkey`) as action  FROM
               `leave_encashment_master` AS `LeaveEncashmentMaster` left JOIN `salary_head_items` AS `SalaryHeadItems`
                ON (`SalaryHeadItems`.`salary_head_item_pkey` = `LeaveEncashmentMaster`.`salary_head_item_fkey`) 
                LEFT JOIN `emp_proff` AS `Emp_Proff` ON (`LeaveEncashmentMaster`.`emp_fkey` = `Emp_Proff`.`emp_fkey`) WHERE `is_approved` = 'Y' and 
                `LeaveEncashmentMaster`.`status` = 1 $conditions2 $conditions1 $conditions ORDER BY `LeaveEncashmentMaster`.`modified_date` DESC LIMIT $ofst,$limit");
           $leavecount = $this->LeaveEncashmentMaster->query("select count(*) as count from `leave_encashment_master` AS `LeaveEncashmentMaster` left JOIN `salary_head_items` AS `SalaryHeadItems`
                ON (`SalaryHeadItems`.`salary_head_item_pkey` = `LeaveEncashmentMaster`.`salary_head_item_fkey`) 
                LEFT JOIN `emp_proff` AS `Emp_Proff` ON (`LeaveEncashmentMaster`.`emp_fkey` = `Emp_Proff`.`emp_fkey`) WHERE `is_approved` = 'Y' AND 
                `LeaveEncashmentMaster`.`status` = 1 $conditions2 $conditions1 $conditions");
//           debug("select count(*) as count from `leave_encashment_master` AS `LeaveEncashmentMaster` left JOIN `salary_head_items` AS `SalaryHeadItems`
//                ON (`SalaryHeadItems`.`salary_head_item_pkey` = `LeaveEncashmentMaster`.`salary_head_item_fkey`) 
//                LEFT JOIN `emp_proff` AS `Emp_Proff` ON (`LeaveEncashmentMaster`.`emp_fkey` = `Emp_Proff`.`emp_fkey`) WHERE `is_approved` = 'Y' AND approved_date = '$month' AND 
//                `LeaveEncashmentMaster`.`status` = 1 $conditions2 $conditions1 $conditions");
//           $arr_empleaverequests = $this -> LeaveEncashmentMaster ->find("all",array("fields"=>array("LeaveEncashmentMaster.*,SalaryHeadItems.item,Emp_Proff.emp_company_id"),
//               "joins"=>array(array("type"=>"left","table"=>"salary_head_items",
//                   "conditions"=>array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey"),
//                   "alias"=>"SalaryHeadItems"),
//                   array(
//                'table' => 'emp_proff',
//                'alias' => 'Emp_Proff',
//                'type' => 'LEFT',
//                'foreignKey' => false,
//                'conditions'=> array('LeaveEncashmentMaster.emp_fkey = Emp_Proff.emp_fkey')
//            )),
//               "conditions"=>array('is_approved'=>'Y','LeaveEncashmentMaster.status'=>'1','LeaveEncashmentMaster.emp_fkey'=>$emp1,
//                   'LeaveEncashmentMaster.branch_code'=>$branch1,'LeaveEncashmentMaster.salary_head_item_fkey'=>$head1,
//                   ),
//               "limit"=>intval($limit),'offset'=>intval($ofst),"order"=>array('LeaveEncashmentMaster.leave_encashment_master_pkey DESC')));
           //$arr_empleaverequests_count = $this -> LeaveEncashmentMaster ->find("count",array("conditions"=>array('is_approved'=>'Y','LeaveEncashmentMaster.status'=>'1','LeaveEncashmentMaster.emp_fkey'=>$emp1,'LeaveEncashmentMaster.branch_code'=>$branch1,'LeaveEncashmentMaster.salary_head_item_fkey'=>$head1)));
           foreach ($arr_leaves as $key => $value) {
            $arra_dsd = array_merge($value["SalaryHeadItems"],$value['LeaveEncashmentMaster'],$value["Emp_Proff"],$value["0"]);
            $resp_empleaverequests["rows"][] = $arra_dsd;
            }
       //debug($this->LeaveEncashmentMaster->getLastQuery());
        $resp_empleaverequests["total"] = $leavecount['0']['0']['count']; 
        echo json_encode($resp_empleaverequests);
    }

     public function cancelentries($registerid = 0) {
        $this->autoRender = FALSE;
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
  
        if (isset($arr_requestdata["ids"])) {
            //$ar_ids = explode(",", $_REQUEST["ids"]);$arr_requestdata
            $arr_registerids = isset($arr_requestdata["ids"]) ? explode(",", $arr_requestdata["ids"]) : array();
        
            $ar_ids = array();
            foreach ($arr_registerids as $LeaveEncashmentMaster_id) {
           
        $this->LeaveEncashmentMaster->query("delete from leave_encashment_master where leave_encashment_master_pkey='$LeaveEncashmentMaster_id'");
        }
        }
      $data["success"] = true;
	echo json_encode($data);
     }
     public function verifyregisterentries() {
        $this->autoRender = FALSE;
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        
        $month = $arr_requestdata["month"];
        $month = date("Y-m-d", strtotime($month . "-01")); // Edited by Akshay on 27-2-2025
        if (isset($arr_requestdata["ids"])) {
            //$ar_ids = explode(",", $_REQUEST["ids"]);$arr_requestdata
            $arr_registerids = isset($arr_requestdata["ids"]) ? explode(",", $arr_requestdata["ids"]) : array();
            $ar_ids = array();
            foreach ($arr_registerids as $LeaveEncashmentMaster_id) {
            $arr_emp = $this->LeaveEncashmentMaster->query("SELECT emp_fkey from leave_encashment_master where leave_encashment_master_pkey='$LeaveEncashmentMaster_id'");
            $emp_pkey = $arr_emp[0]['leave_encashment_master']['emp_fkey'];
            $arr_leaves = $this->LeaveEncashmentMaster->query("SELECT branch_code from emp_details where emp_pkey='$emp_pkey'");
            $branch_code = $arr_leaves[0]['emp_details']['branch_code'];
        $arr_data = array();
        $user = $this -> Session -> read('user_name');
        $arr_data['modified_by'] = '"'.$user.'"';
        $arr_data['approved_by'] = '0';
        $arr_data['is_approved'] = '"'.'Y'.'"';
        $arr_data['branch_code'] = '"'.$branch_code.'"';
        $date = new DateTime("now", new DateTimeZone('Asia/Kolkata') );
        $mdate = $date->format('Y-m-d H:i:s');
        $arr_data['modified_date'] = '"'.$mdate.'"'; 
        $arr_data['approved_date'] = '"'.$month.'"';
        $this->LeaveEncashmentMaster->updateAll($arr_data,array('leave_encashment_master_pkey' => $LeaveEncashmentMaster_id));
        $emp_ncashes_approve = $this -> LeaveEncashmentMaster ->query("call leave_encash_prc('$branch_code','$emp_pkey','$LeaveEncashmentMaster_id','0','@msg')");
            }
        }
      $data["success"] = true;
	echo json_encode($data);
     }
     public function encashemp(){
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $emp_pkey = $arr_form_data['emp'];
        $month = $arr_form_data['month'];
        $LeaveEncashmentMaster_id = $arr_form_data['leaveencashfkey'];
        $arr_leaves = $this->LeaveEncashmentMaster->query("SELECT branch_code from emp_details where emp_pkey='$emp_pkey'");
        $branch_code = $arr_leaves[0]['emp_details']['branch_code'];
        $arr_data = array();
        $user = $this -> Session -> read('user_name');
        $arr_data['modified_by'] = '"'.$user.'"';
        $arr_data['approved_by'] = '0';
        $arr_data['is_approved'] = '"'.'Y'.'"';
        $arr_data['branch_code'] = '"'.$branch_code.'"';
        $arr_data['modified_date'] = '"'.date("Y-m-d h:i:sa").'"'; 
        // $arr_data['approved_date'] = '"'.$month.'"';
        $arr_data['approved_date']='"'.date("Y-m-d").'"';
        $this->LeaveEncashmentMaster->updateAll($arr_data,array('leave_encashment_master_pkey' => $LeaveEncashmentMaster_id));
        $emp_ncashes_approve = $this -> LeaveEncashmentMaster ->query("call leave_encash_prc('$branch_code','$emp_pkey','$LeaveEncashmentMaster_id','0','@msg')");
        $data["msg"] = true;
	echo json_encode($data);
    }
//    public function encashemp(){
//        $this->Units->useDbConfig = $this->Session->read('ds');
//        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
//        $this->autoRender = false;
//        $arr_form_data = $this->request->data;
//        $cur_emp_key = $arr_form_data['emp'];
//        $salary = $arr_form_data['salaryheaditemsfkey'];
//        $applied = $arr_form_data['applied_days'];
//        $currentyear = $this->Units->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$cur_emp_key')");
//        $finyears = isset($currentyear['0'])?$currentyear['0']['fin_year']['fin_year']:null;
//        $emp_ncashes = $this -> LeaveEncashmentMaster ->query("select leave_balance_inthe_year_fn('$cur_emp_key',salary_head_items.salary_head_item_pkey,'$finyears') as yearlybalance,salary_head_items.item,leavepolicy.*,leavepolicy.LEAVEPOLICYID,leavepolicy.salary_head_item_fkey,leavepolicy.alloted_leave_forthe_year,emp_details.emp_pkey,emp_details.branch_code,concat(emp_details.first_name,' ',emp_details.last_name) as emp_name,leavepolicy.alloted_leave_forthe_month,leavepolicy.leave_encash_limit from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) left join emp_details on (emp_details.emp_pkey = '$cur_emp_key') where LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$cur_emp_key') and leavepolicy.salary_head_item_fkey = '$salary' and leave_encash_limit is NOT NULL");
//        $arr_data = array();
//        $arr_data['emp_fkey'] = $emp_pkey =  $emp_ncashes['0']['emp_details']['emp_pkey'];
//        $arr_data['emp_name'] = $emp_ncashes['0']['0']['emp_name'];
//        $arr_data['branch_code'] = $branch_code = $emp_ncashes['0']['emp_details']['branch_code'];
//        $arr_data['salary_head_item_fkey'] = $emp_ncashes['0']['leavepolicy']['salary_head_item_fkey'];
//        $arr_data['encash_days'] = $emp_ncashes['0']['leavepolicy']['leave_encash_limit'];
//        $arr_data['available_days'] = min($emp_ncashes['0']['leavepolicy']['leave_encash_limit'],$emp_ncashes['0']['0']['yearlybalance']);
//        $arr_data['requested_days'] = $applied;
//        $arr_data['approved_days'] = $applied;
//        $arr_data['created_by'] = $this -> Session -> read('user_name');
//        $arr_data['creation_date'] = date('Y-m-d');
//        $arr_data['modified_by'] = $this -> Session -> read('user_name');
//        $arr_data['approved_by'] = '0';
//        $arr_data['is_approved'] = 'Y';
//        $arr_data['approved_date'] = date("Y-m-d");
//        $arr_data['fin_year'] = $finyears;
//	$pay_month = date('Y-m');
//	$arr_payroll = $this->LeaveEncashmentMaster->query("SELECT action FROM `payroll_master` WHERE `month_year` = '$pay_month' AND `emp_fkey`='$cur_emp_key'"); 
//	$action = isset($arr_payroll['0']['payroll_master']['action'])?$arr_payroll['0']['payroll_master']['action']:'';
//	if($action != 'Processed' && $action != 'Approved'){
//          if($result = $this->LeaveEncashmentMaster->save($arr_data)){
//            $LeaveEncashmentMaster_id=$this->LeaveEncashmentMaster->getLastInsertId();
//            $emp_ncashes_approve = $this -> LeaveEncashmentMaster ->query("call leave_encash_prc('$branch_code','$emp_pkey','$LeaveEncashmentMaster_id','0','@msg')");
//          }
//	$data["msg"] = true;
//	echo json_encode($data);
//	}else{
//        $data["msg"] = "Payroll Processed for this month. Cannot approve leave encashment."; 
//        echo json_encode($data);
//        }
//    }
    
     public function encashlisting(){
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this->LeaveEncashmentMaster->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $emp = isset($arr_form_data['emp'])?$arr_form_data['emp']:'';
        $month = isset($arr_form_data['month'])?$arr_form_data['month']:'';
        $yearmonth=$month."-01";

        $branch = isset($arr_form_data['branch'])?$arr_form_data['branch']:'';
        $item = isset($arr_form_data['item'])?$arr_form_data['item']:'';
        $user = $this -> Session -> read('user_name');
        $list = $this->Units->query("CALL `leave_encash_insert_prc`('$branch', '$emp','$item','$yearmonth', '$user', @`perr_msg`)");
	
        if($list){
            $data["msg"] = true;
        }else{
           $data["msg"] = false; 
        }
	echo json_encode($data);
    }
    
    public function getusers() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
      $emp_fkey = $this->Session->read('emp_fkey');     
        $arr_request_data = $this->request->query;
//debug($arr_request_data);
        if (isset($arr_request_data['username'])) {
            $searchkey = $arr_request_data['username'];
            $filter_condition = 'first_name LIKE "%' . $searchkey . '%" and emp_pkey != '.$emp_fkey;
        } else {
            $filter_condition = '';
        }
        $arr_users = $this->EmployeeDetails->find('all', array(
            'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
            'conditions' => array(
			'status'=>1,
                $filter_condition
            )
                )
              
        ); 
    //  debug($arr_users);
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'],$val[0]) : array();
        }
     //  debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }
    
    
    public function listempleavesverified(){      
        $this->autoRender = false;
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        $arr_empleaverequests = $this -> LeaveEncashmentMaster ->find("all",array("fields"=>array("LeaveEncashmentMaster.*,SalaryHeadItems.item"),"joins"=>array(array("type"=>"left","table"=>"salary_head_items","conditions"=>array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey"),"alias"=>"SalaryHeadItems")),"conditions"=>array("approved_by"=>$cur_emp_key,'is_approved'=>'Y','LeaveEncashmentMaster.status'=>'1')));
        //debug($arr_empleaverequests);
        $resp_empleaverequests = array();
        $resp_empleaverequests["rows"]= array();
        foreach ($arr_empleaverequests as $key => $value) {
            $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveEncashmentMaster"],$value["SalaryHeadItems"]);
            
            
        }
        $resp_empleaverequests["total"] = count($arr_empleaverequests);
        echo json_encode($resp_empleaverequests);
    }
    
    /*
     * Manage Employee Leave
     * By santhosh on 21 March 2015
     */
    public function manageleave($leaveentryId = 0,$emp = 0) {
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        $arr_empleaverequests = $this -> LeaveEncashmentMaster ->find("all",array("fields"=>array("LeaveEncashmentMaster.*,SalaryHeadItems.item"),"joins"=>array(array("type"=>"left","table"=>"salary_head_items","conditions"=>array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey"),"alias"=>"SalaryHeadItems")),"conditions"=>array("leave_encashment_master_pkey"=>$leaveentryId)));
		$monthyear = date('Y-m');
        $arr_payroll = $this->LeaveEncashmentMaster->query("SELECT action FROM `payroll_master` WHERE `month_year` = '$monthyear' AND `emp_fkey`='$emp'"); 
		$action = $arr_payroll['0']['payroll_master']['action'];
		$this->set('action',$action);
        $this -> set('arr_empleaverequests', $arr_empleaverequests);
		$empkey = $arr_empleaverequests['0']['LeaveEncashmentMaster']['emp_fkey'];
		$finyears = $arr_empleaverequests['0']['LeaveEncashmentMaster']['fin_year'];
		$salary_head_item_fkey = $arr_empleaverequests['0']['LeaveEncashmentMaster']['salary_head_item_fkey'];
		$arr_leave_type = $this->LeaveEncashmentMaster->query("select leavepolicy.leave_encash_limit,
        (select sum(approved_days) as sum from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where (remarks != 'terminate' or remarks is null) and leave_encashment_master.status = 1 and leave_encashment_master.is_approved ='Y' and emp_fkey = '$empkey' and emp_details.status = 1 and DATE_FORMAT(leave_encashment_master.approved_date, '%Y') = '$finyears' and salary_head_item_fkey = salary_head_items.salary_head_item_pkey) as encashyearly	
		from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) where LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$empkey') and leave_encash_limit is NOT NULL  and salary_head_item_fkey = '$salary_head_item_fkey'");
		$eligible = $arr_leave_type['0']['leavepolicy']['leave_encash_limit'] - $arr_leave_type['0']['0']['encashyearly'];
		$this -> set('eligible', $eligible);
    }
    
    /*
     * My Leave Lists
     * By santhosh on 19 March 2015
     */
    /*public function listleaves() {
        $sessionObj = $this -> Session -> read("Auth.User");
        $cur_emp_key = $sessionObj['emp_fkey'];

        $columns = array( array('db' => 'LEAVEENTRYID', 'dt' => 0), array('db' => 'leave_type', 'dt' => 1), array('db' => 'applied_date', 'dt' => 2), array('db' => 'FROMDATE', 'dt' => 3), array('db' => 'TODATE', 'dt' => 4), array('db' => 'LEAVESTATUS', 'dt' => 5));
        $this -> datatable["fields"] = 'LEAVEENTRYID,SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';
        $this -> datatable["joins"] = array( array('table' => 'salary_head_items', 'alias' => 'SalaryHeadItems', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')));
        $this -> datatable["conditions"] = array('EMP_fkey' => $cur_emp_key);

        $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
        echo json_encode($this -> DataTable -> getData('LeaveRequests', $columns));
        $this -> autoRender = FALSE;
    }*/
    
    /*
     * My Leave Requests
     * By santhosh on 23 April 2015
     */
    public function getfinyear($date = null){
        $this -> autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where '$date' between start_month and end_month");
        $finyear = isset($get_finyear['0']['fin_year']['fin_year'])?$get_finyear['0']['fin_year']['fin_year']:date('Y');
        return $finyear;
    }
    public function listleaves() {        
        $this -> autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        
        $ofst = ($page-1)*$limit;
        
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        
        $fields = 'ISAutherizedby,APPROVEDBY,LEAVEENTRYID,SalaryHeadItems.item as leave_type,applied_date,FROMDATE,FROMHALF,TODATE,TOHALF,LEAVESTATUS,leave_days';
        $joins = array(
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
            )
        );
        $conditions  =   array('EMP_fkey' => $cur_emp_key);
        
        $resp_myleaverequests = array();
        $resp_myleaverequests["rows"]= array();
        $count = $this -> LeaveRequests -> find("count",array("conditions"=>$conditions));
        
        $arr_myleaverequests =  $this -> LeaveRequests ->find("all",array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'limit'=>intval($limit),
            'offset'=>intval($ofst)
        ));
        foreach ($arr_myleaverequests as $key => $value) {
            $resp_myleaverequests["rows"][$key] = array_merge($value["LeaveRequests"],$value["SalaryHeadItems"]);
                        
			if($resp_myleaverequests["rows"][$key]['FROMHALF'] == '1'){
				$resp_myleaverequests["rows"][$key]['FROMHALF'] = 'First Half';
			}else if($resp_myleaverequests["rows"][$key]['FROMHALF'] == '2'){
				$resp_myleaverequests["rows"][$key]['FROMHALF'] = 'Second Half';
			}else{
				$resp_myleaverequests["rows"][$key]['FROMHALF'] = '--';
			}
			
			if($resp_myleaverequests["rows"][$key]['TOHALF'] == '1'){
				$resp_myleaverequests["rows"][$key]['TOHALF'] = 'First Half';
			}else if($resp_myleaverequests["rows"][$key]['TOHALF'] == '2'){
				$resp_myleaverequests["rows"][$key]['TOHALF'] = 'Second Half';
			}else{
				$resp_myleaverequests["rows"][$key]['TOHALF'] = '--';
			}
        }
        $resp_myleaverequests["total"] = $count;
        echo json_encode($resp_myleaverequests);        
    }

    public function save($leaveentryId = 0) {
        $arr_form_data = $this->request->data;
        $this->autoRender = false;
        //debug($arr_form_data);
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $arr_groups = $arr_form_data['encashment'];
       // debug($arr_groups);
        $data = array();
        $cur_emp_key = $data['emp_fkey'] = $arr_form_data['emp_pkey'];
        $name = $data['emp_name'] = $arr_form_data['name'];
        $branch = $data['branch_code'] = $arr_form_data['branch'];
        $data['created_by'] = $cur_emp = $this -> Session -> read("emp_fkey");
        //$data['creation_date'] = date('Y-m-d');
        $approve = $data['approved_by'] = $arr_form_data['ISAutherizedby'];
        $remarks = $data['remarks'] = $arr_form_data['Reason'];
        foreach($arr_groups as $key => $leaves){
            $encash = $data['encash_days'] = $leaves['limit'];
            $avail = $data['available_days'] = $leaves['available'];
            $request = $data['requested_days'] = $leaves['requested'];
            $data['salary_head_item_fkey'] = $key;
			$currentyear = $this->LeaveEncashmentMaster->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$cur_emp_key')");
            $fin = $data['fin_year'] = isset($currentyear['0'])?$currentyear['0']['fin_year']['fin_year']:null;
            if($data['requested_days'] > 0){
            $this -> LeaveEncashmentMaster ->query("insert into leave_encashment_master(emp_fkey,emp_name,branch_code,salary_head_item_fkey,encash_days,available_days,requested_days,created_by,approved_by,fin_year,remarks) values('$cur_emp_key','$name','$branch','$key','$encash','$avail','$request','$cur_emp','$approve','$fin','$remarks')");
			}
        }
    }

    /*public function getLeaveType() {
        $this -> autoRender = FALSE;
        $this -> SalaryHeadItems -> useDbConfig = $this -> Session -> read('ds');
        $leave_type = Set::extract('/SalaryHeadItems/.', $this -> SalaryHeadItems -> find("all", array("conditions" => array("head_fkey" => 6, "value" => "Y", "status" => 1))));
        return json_encode($leave_type);
    }*/
    public function form() {
        $arr_leave_type = array();
        $this -> SalaryHeadItems -> useDbConfig = $this -> Session -> read('ds');
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        $currentyear = $this->Units->query("select * from fin_year where Year_status in('OPEN')  and is_current_finyear = 'Y' and vattr1 = 0 and status = 1 and branch_code in (select branch_code from emp_details where emp_pkey = '$cur_emp_key')");
        $finyears = isset($currentyear['0'])?$currentyear['0']['fin_year']['fin_year']:null;
        $emp_det = $this->Units->query("select branch_code,first_name,last_name from emp_details where emp_pkey = '$cur_emp_key' ");
        //debug($emp_det);
        $name = isset($emp_det['0'])?$emp_det['0']['emp_details']['first_name'].' '.$emp_det['0']['emp_details']['last_name']:null;
        $branch = $emp_det['0']['emp_details']['branch_code'];
        $this->set("branch_code",$branch);
        $this->set("emp_name",$name);
	$month = date('Y-m-01');
        $monthyear = date('Y-m');
        $arr_leave_type = $this->SalaryHeadItems->query("select leave_balance_inthe_year_fn('$cur_emp_key',salary_head_items.salary_head_item_pkey,'$finyears') as yearlybalance,"
                . "leave_balance_inthe_month_fn('$cur_emp_key',salary_head_items.salary_head_item_pkey,'$monthyear','$finyears') as monthlybalance,salary_head_items.item,leavepolicy.*,"
                . "(select sum(requested_days) as sum from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) "
                . "where (remarks != 'terminate' or remarks is null) and leave_encashment_master.status = 1 and leave_encashment_master.is_approved ='N' and emp_fkey = '$cur_emp_key' "
                . "and emp_details.status = 1 and leave_encashment_master.created_by = '$cur_emp_key' and fin_year = '$finyears' and salary_head_item_fkey = salary_head_items.salary_head_item_pkey) as yearlylimit,
		(select sum(requested_days) as sum from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) 
                where (remarks != 'terminate' or remarks is null) and leave_encashment_master.status = 1 and leave_encashment_master.is_approved ='N' and emp_fkey = '$cur_emp_key' "
                . "and emp_details.status = 1 and leave_encashment_master.creation_date between '$month' and LAST_DAY('$month') and salary_head_item_fkey = salary_head_items.salary_head_item_pkey) as monthlylimit,
        (select sum(approved_days) as sum from leave_encashment_master left join emp_details on(leave_encashment_master.emp_fkey = emp_details.emp_pkey) where (remarks != 'terminate' or remarks is null) 
        and leave_encashment_master.status = 1 and leave_encashment_master.is_approved ='Y' and emp_fkey = '$cur_emp_key' and emp_details.status = 1 and "
                . "DATE_FORMAT(leave_encashment_master.approved_date, '%Y') = '$finyears' and salary_head_item_fkey = salary_head_items.salary_head_item_pkey) as encashyearly	
		from leavepolicy left join salary_head_items on (salary_head_items.salary_head_item_pkey = leavepolicy.salary_head_item_fkey) 
                where LEAVEPOLICY_GROUP_ID in (select LEAVEPOLICY_GROUP_ID  from emp_proff where emp_fkey = '$cur_emp_key') and leave_encash_limit is NOT NULL");
		
        $this->set("leaves",$arr_leave_type);
        $this->set('emp_pkey',$cur_emp_key);
		$pay_month = date('Y-m');
		$arr_payroll = $this->SalaryHeadItems->query("SELECT action FROM `payroll_master` WHERE `month_year` = '$monthyear' AND `emp_fkey`='$cur_emp_key'"); 
		$action = isset($arr_payroll['0']['payroll_master']['action'])?$arr_payroll['0']['payroll_master']['action']:'';
		$this->set('action',$action);
    }
    
  
        
   /*  public function loadleave($leaveentryId = 0) {
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        //added by megha leave policy join condition on 22_08_2019
        $leave_details = $this -> LeaveEncashmentMaster ->find("all",array("fields"=>array("LeaveEncashmentMaster.*,SalaryHeadItems.item"),
            "joins"=>array(
                array("type"=>"left",
                    "table"=>"salary_head_items",
                    "conditions"=>array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey"),
                    "alias"=>"SalaryHeadItems"),
                array("type"=>"left",
                    "table"=>"leavepolicy",
                    "conditions"=>array("SalaryHeadItems.salary_head_item_pkey = LeavePolicy.salary_head_item_fkey"),
                    "alias"=>"LeavePolicy"),
                ),
            "conditions"=>array("emp_fkey"=>$cur_emp_key,'LeaveEncashmentMaster.status'=>1,'leave_encash_limit is NOT NULL')));

        $this->set("arr_leaves",$leave_details);
	} */
    public function loadleave($leaveentryId = 0) {
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        $this->LeaveEncashmentMaster->query("delete from leave_encashment_master where  is_approved !='Y' and status=1 and emp_fkey= $cur_emp_key and created_by != $cur_emp_key");
        //added by megha leave policy join condition on 22_08_2019
        $leave_details = $this -> LeaveEncashmentMaster ->find("all",array("fields"=>array("LeaveEncashmentMaster.*,SalaryHeadItems.item"),
            "joins"=>array(
                array("type"=>"left",
                    "table"=>"salary_head_items",
                    "conditions"=>array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey"),
                    "alias"=>"SalaryHeadItems"),
                ),
            "conditions"=>array("emp_fkey"=>$cur_emp_key,'LeaveEncashmentMaster.status'=>1,'LeaveEncashmentMaster.salary_head_item_fkey != 0')));

        $this->set("arr_leaves",$leave_details);
    }
	

    public function approvetab(){
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        
    }
    public function listleavesrequests(){
        $this->autoRender = false;
        $this -> Units -> useDbConfig = $this -> Session -> read('ds');
        $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        $arr_empleaverequests = $this -> LeaveEncashmentMaster ->find("all",array("fields"=>array("LeaveEncashmentMaster.*,SalaryHeadItems.item"),"joins"=>array(array("type"=>"left","table"=>"salary_head_items","conditions"=>array("SalaryHeadItems.salary_head_item_pkey = LeaveEncashmentMaster.salary_head_item_fkey"),"alias"=>"SalaryHeadItems")),"conditions"=>array("approved_by"=>$cur_emp_key,'is_approved'=>'N','LeaveEncashmentMaster.status'=>'1')));
        //debug($arr_empleaverequests);
        $resp_empleaverequests = array();
        $resp_empleaverequests["rows"]= array();
        foreach ($arr_empleaverequests as $key => $value) {
            $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveEncashmentMaster"],$value["SalaryHeadItems"]);
        }
        $resp_empleaverequests["total"] = count($arr_empleaverequests);
        echo json_encode($resp_empleaverequests);
    }
    public function grandLeave() {
        $this -> autoRender = FALSE;
        
        $arr_form_data = $this -> request -> data;
        $leaveentryId = isset($arr_form_data['leaveentryid'])?$arr_form_data['leaveentryid']:0;
        
        $resp   =   array();
        $data = array();
        $cur_emp_key = $this -> Session -> read("emp_fkey");
        
        
            $this -> LeaveEncashmentMaster -> useDbConfig = $this -> Session -> read('ds');
            $data['leave_encashment_master_pkey'] = $leaveentryId;
            $data['approved_days'] = $arr_form_data['approved_days'];
            $data['modified_date'] = date('Y-m-d');
            $data['is_approved'] = 'Y';
            $data['approved_date'] = date('Y-m-d');
            $this -> LeaveEncashmentMaster -> save($data);
            
            $resp["success"] = true;
            $resp["message"] = 'Leave Approved successfully';
            return json_encode($resp);      
        
    }

     
    public function cancel_leave(){
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $leave_encashment_master_pkey = $arr_request_data['leave_encashment_master_pkey'];
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $update_table_leave_encashment_master = $this->EmployeeMenu->query("update leave_encashment_master set status = '0' where leave_encashment_master_pkey = '$leave_encashment_master_pkey' ");
        return TRUE;
    }
	 public function listcriteriaitems($str_criteria = '',$branch = '') {
        $this->autoRender = false;
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            $conditions = array("status" => 1);
            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {


                case 'EmployeeDetails':
                    $fields = 'emp_pkey,status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name';
					$condition = array();
					$condition[]  = array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey');
					
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => $condition
                        )
                    );
                    $conditions = array();


                    $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    $conditions[] = array("status" => 1);
                    
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2) {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                    }else{
			$conditions[] = array("EmployeeDetails.branch_code" => $branch);
			}
                   
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions
                    ));
					
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;
               
            }
            echo json_encode($arr_criteriaItems);
        }
    }
    public function employeelists($branch) {
        $this->autoRender=FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $arr_request_data = $this->request->data;
        // $branch=$arr_request_data['branch'];
        if ($branch=="ALL") {
            //The query also eliminates the employees who have no salary structure. By ***ARUL P DAS on 3/12/2019
            //$result=$this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff join emp_ctc_transaction on emp_details.emp_pkey = emp_ctc_transaction.emp_fkey where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 and emp_details.emp_pkey not in (select emp_fkey from termination where status=1) group by emp_pkey');
            $result=$this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 order by first_name');
       }else{
            //The query also eliminates the employees who have no salary structure. By ***ARUL P DAS on 3/12/2019
            $result=$this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details '
                    . ' join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.emp_pkey=emp_proff.emp_fkey '
                    . ' and emp_details.branch_code="'.$branch.'" and emp_details.status=1 order by first_name');
        }
//        $array = array();
//        $employees[] = array("id" => "0", "text" => "ALL");
//        //***emp_pkey replaced with emp_company_id in the employee list. By ARUL P DAS on 05-11-2019***
//        foreach ($result as $key => $value) { 
//            $employees[] = array(
//                'id' => $value['emp_details']['emp_pkey'],
//                'text' => $value['emp_details']['first_name'] ." ". $value['emp_details']['last_name']." - ".$value['emp_proff']['emp_company_id']
//            );
//        }
//        $array['items'] = $employees;
//        echo json_encode($array);  
        echo json_encode(array("value"=>$result,"msg"=>count($result)));
    }
    //Ends
}