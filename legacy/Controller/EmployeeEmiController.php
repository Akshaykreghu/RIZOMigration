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
class EmployeeEmiController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EmployeeEmi';
    public $datatable;
    public $san = 'AMAL JAMES';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeLoanInfo', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'LoanEmi', 'EmployeeCTC', 'EmployeeLoan', 'SalarySlip');
    public $components = array('MasterdataManagement');

    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        // Edited by Akshay on 11-2-2025
        $is_ho = 1;
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $conditions = array('status' => 1);
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $conditions = array('status' => 1, 'branch_code' => $is_ho);
            }
        }
        $this->set("is_ho", $is_ho);
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => $conditions)));
        // End
    }

  public function emi_upload(){
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $loan_months =isset($arr_form_data['month']) ? $arr_form_data['month'] : '';
        // debug($loan_months);
        // debug($arr_form_data);
        // $month_year = $month;
        // debug($month_year);
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        // $arr_form_data = $this->request->data;
         // debug($arr_form_data);
         // debug($month);
        // debug($arr_form_data);
        // $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array(
        //         'status' => 1
        // )));

        //***emp_pkey replaced with emp_company_id in the employee list. By ARUL P DAS on 05-11-2019***
        $arr_employees=$this->EmployeeDetails->query("select loan.emp_fkey,loan.is_completed,ed.emp_pkey,ed.first_name,ed.last_name,emp_pro.emp_company_id,loan_info.loan_month,ifnull((select sum(amt) from emi_upload where emp_pkey=loan.emp_fkey and loan_pkey= loan.emp_loan_pkey),0) paid_amount,loan_amount-ifnull((select sum(amt) from emi_upload where emp_pkey=loan.emp_fkey and loan_pkey= loan.emp_loan_pkey),0) balance_due from emp_loan loan
left join emp_details as ed on (loan.emp_fkey = ed.emp_pkey) 
left join emp_proff as emp_pro on (ed.emp_pkey = emp_pro.emp_fkey)
Left join emp_loan_info as loan_info on(ed.emp_pkey = loan_info.emp_fkey)
where loan.emp_fkey = ed.emp_pkey and ed.status = 1 and loan.is_completed = 'N' and loan_info.loan_month = '$loan_months' group by loan.emp_fkey");

        // debug($arr_employees);
        $this->set("arr_employees", $arr_employees);
        // debug($arr_employees);
        // $pkey=$arr_employees['0']['ed']['emp_pkey'];
        // debug($pkey);
        // debug($pkey);
        // echo implode($pkey);
        //  $arr_loan=$this->EmployeeLoan->query("select loan.loan_amount,emp.first_name from emp_loan loan left join emp_details as emp on (loan.emp_fkey = emp.emp_pkey) where loan.status =1 and loan.is_completed = 'N' and loan.emp_fkey = '$pkey'");
        // $this->set("arr_loan", $arr_loan);
        
        $data['emp_loan_pkey'] = 0;
        $data['loan_amount'] = "";
        $data['emp_fkey'] = "";
        $data['tenure'] = 0;
        $data['intrest_rate'] = 0;
        $data['emi_amount'] = 0;
        $data['emi_start_month'] = "";
        $data['emi_end_month'] = "";
        $data['is_completed'] = "";
        $data['remarks'] = "";
        $data['paid_amount'] = "";
        $data['balance_due'] = "";
    
       
        // $this->set("options",$appnds);
        // debug()
       
        // debug($data);
        if (isset($_REQUEST['emp_loan_pkey']) && $_REQUEST['emp_loan_pkey'] != 0) {
            $data_db = $this->EmployeeLoan->find("first", array("conditions" => array(
                    "emp_loan_pkey" => $_REQUEST['emp_loan_pkey']),
            ));
            $data = $data_db['EmployeeLoan'];
            $result = array();
            $current = date("Y/m/d");
            $datechec = (strtotime($data['created_date']));
            $date = date('Y/m/d', $datechec);
            $newdate = strtotime('+3 day', strtotime($date));
            $sumdate = date('Y/m/d', $newdate);
            if ($sumdate < $current) {
                $result['success'] = 1;
                $result['msg'] = "Record(s)   successfully.";
                //echo json_encode($result);
                $this->set("data3", '');
                $this->set("data", $data);
                $this->set("data2", $result);
                //$this->set("data2", $data2);
            } else {
                $current = date("d", strtotime(date("Y/m/d")));
                $datechec = date("d", strtotime($data['created_date']));
                $lol = $current - $datechec;
                // debug($lol);
                $result['success'] = $lol;
                //debug($lol);
                //$fk = date('Y/m/d', $lol);
                //debug($fk);

                $this->set("data2", '');
                $this->set("data3", $lol);
                $this->set("data", $data, "hi", $lol);
            }
        } else {
            $this->set("data3", '');
            $this->set("data2", '');
            $this->set("data", $data);
        }
        
        // debug($appnds);
 // echo json_encode(array("success"=>1, "data"=> $appnds));
        //debug($data);
    }
     
    public function getEmi(){
        $this->autoRender = false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $month = $arr_form_data['month_year'];
        $emp_pkey = $arr_form_data['empid'];
        // debug($emp_pkey);
        // $joins = array(
        //   array(
        //       "table" => "emp_loan",
        //       "type" => "INNER",
        //       "alias" => "EmpLoan",
        //       'foreignKey' => false,
        //         'conditions' => array('EmployeeLoanInfo.loan_pkey = EmpLoan.emp_loan_pkey')
        //   )  
        // );
        
        $arr_loan_data = $this->EmployeeLoanInfo->query("select * from (select  loan_info.loan_pkey,loan_info.loan_emi,EmpLoan.loan_amount,loan_amount-ifnull((select sum(amt) from emi_upload where emp_pkey=EmpLoan.emp_fkey and loan_pkey= EmpLoan.emp_loan_pkey),0)
 balance_due from emp_loan as EmpLoan left join emi_upload as emi on (EmpLoan.emp_loan_pkey = emi.loan_pkey)
left join emp_loan_info as loan_info on (EmpLoan.emp_loan_pkey  = loan_info.loan_pkey)
  WHERE EmpLoan.emp_fkey = '$emp_pkey' AND 
loan_info.loan_month = '$month' AND EmpLoan.is_completed = 'N' AND loan_info.status = 1 group by EmpLoan.emp_loan_pkey)a where a.balance_due >0 ");

        // debug($arr_loan_data);
        $emi_balance = isset($arr_loan_data['0']['a']['balance_due'])?$arr_loan_data['0']['a']['balance_due'] : 0;
// debug($emi_balance);
        $emi_sum = isset($arr_loan_data['0']['a']['loan_emi'])?$arr_loan_data['0']['a']['loan_emi']: 0;
        $emi_loan = isset($arr_loan_data['0']['a']['loan_amount'])?$arr_loan_data['0']['a']['loan_amount'] : 0;
        // debug($emi_loan);
        $appnds = '';

        // $appnds.='<option>select</option>';
        foreach ($arr_loan_data as $val){
            // debug($val);
            // $appnds .= '<option value="' . 

            $appnds .= '<option value="'.$val['a']['loan_pkey'].'" >'.$val['a']['loan_amount'].'</option>';
        }
       // debug($arr_loan_data);
        echo json_encode(array("success"=>1,"sum"=>$emi_sum,"sums"=>$emi_balance,"loans"=>$emi_loan, "data"=> $appnds));
        
    }
    public function getEmployee($month = '')
    {
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        // $month = $arr_form_data['month_year'];
        // debug($month);
        // debug($arr_form_data);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        // debug($arr_form_data);
        // $loan_months =isset($arr_form_data['months']) ? $arr_form_data['months'] : '';
        // debug($loan_months)
        // debug($month_year);
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');


        if ($q != null) {
            if (is_numeric($q)) {
                $q_condition = "and a.emp_company_id like '%$q%' ";
            } else {
                $q_condition = "and a.first_name like '%$q%' ";
            }
        } else {
            $q_condition = "";
        }

        // Edited by Akshay on 12-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $q_condition .= " AND a.branch_code = '$is_ho' ";
            }
        }


        $arr_emp = $this->EmployeeDetails->query("select * from(
                        select ed.emp_pkey, loan.emp_fkey,loan.emp_loan_pkey,loan.is_completed,ed.first_name,ed.last_name,ed.branch_code,emp_pro.emp_company_id,loan_info.loan_month,ifnull((select sum(amt)
                        from emi_upload where emp_pkey=loan.emp_fkey and loan_pkey= loan.emp_loan_pkey),0) paid_amount,loan_amount-ifnull((select sum(amt) 
                        from emi_upload where emp_pkey=loan.emp_fkey and loan_pkey= loan.emp_loan_pkey),0) balance_due from emp_loan loan
                        left join emp_details as ed on (loan.emp_fkey = ed.emp_pkey) 
                        left join emp_proff as emp_pro on (ed.emp_pkey = emp_pro.emp_fkey)
                        Left join emp_loan_info as loan_info on(ed.emp_pkey = loan_info.emp_fkey)

                        WHERE ed.status = '1' AND loan.is_completed = 'N'   and loan.emp_loan_pkey 
                        -- not  in(select loan_pkey from emp_loan_info where loan_month = '$month'   
                        -- and ((paid_status = 'P') or (loan_emi =0))) 
                        and loan.emp_loan_pkey  in(select loan_pkey from emp_loan_info where loan_month = '$month' ) and loan_info.loan_month = '$month'
                        and '$month' between loan.emi_start_month and loan.emi_end_month 



                        )a 
                        where a.balance_due >0 $q_condition group by a.emp_pkey order by a.first_name
 ");
         // End

        // debug($arr_emp);


        // debug($arr_emp);
        // $appnds = '';
        //  $appnds.='<option>select</option>';
        // foreach ($arr_emp as $val){
        //   // debug($val);
        //    // ' <option value="'.$val['asset_management']['asset_pkey'].'" >'.$val['asset_management']['name'].'</option>'

        //     $appnds .= '<option value="'. $val['ed']['emp_pkey'].'">'.$val['ed']['first_name'].' '.$val['ed']['last_name'].' - '.$val['emp_pro']['emp_company_id'].'</option>';
        //     } 

        // $emi_balance = isset($arr_loan_data['0']['0']['balance_due'])?$arr_loan_data['0']['0']['balance_due'] : 0;
        $array = array();
        $employee = array();
        $employee[] = array("id" => "1", "text" => "ALL");
        foreach ($arr_emp as $key => $value) {
            // debug($value);
            $employee[] = array(
                'id' => $value['a']['emp_fkey'],
                'text' => $value['a']['first_name'] . ' ' . $value['a']['last_name'] . '-' . $value['a']['emp_company_id']
            );
        }
        $array['items'] = $employee;
        echo json_encode($array);
        // echo json_encode(array("success"=>1,"sum"=>$appnds));

    }
     public function getBalance(){
        $this->autoRender = false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $month = $arr_form_data['month_year'];
        $emp_pkey = $arr_form_data['empid'];
        $key = $arr_form_data['loan_pkey'];
        // debug($emp_pkey);
        $joins = array(
          array(
              "table" => "emp_loan",
              "type" => "INNER",
              "alias" => "EmpLoan",
              'foreignKey' => false,
                'conditions' => array('EmployeeLoanInfo.loan_pkey = EmpLoan.emp_loan_pkey')
          )  
        );
        
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("EmployeeLoanInfo.emp_fkey" => $emp_pkey, "EmployeeLoanInfo.loan_month" => $month ,"EmpLoan.is_completed" => "N" ,"EmpLoan.emp_loan_pkey" => $key ,"EmployeeLoanInfo.status" => "1"), "group" =>array(" EmpLoan.emp_loan_pkey"), "fields" => array("loan_amount-ifnull((select sum(amt) from emi_upload where emp_pkey=EmpLoan.emp_fkey and loan_pkey= EmpLoan.emp_loan_pkey),0) balance_due "), "joins" => $joins ));
        // debug($arr_loan_data);
            $emi_balance = isset($arr_loan_data['0']['0']['balance_due'])?$arr_loan_data['0']['0']['balance_due'] : 0;
            

  
// debug($emi_balance);
        // $emi_sum = isset($arr_loan_data['0']['EmployeeLoanInfo']['loan_emi'])?$arr_loan_data['0']['EmployeeLoanInfo']['loan_emi']: 0;
        // $emi_loan = isset($arr_loan_data['0']['EmpLoan']['loan_amount'])?$arr_loan_data['0']['EmpLoan']['loan_amount'] : 0;
        // debug($emi_loan);
        // $appnds = '';
        // $appnds.='<option>select</option>';
        // foreach ($arr_loan_data as $val){

        //     $appnds .= '<option value="'.$val['EmployeeLoanInfo']['loan_pkey'].'" >'.$val['EmpLoan']['loan_amount'].'</option>';
        // }
       // debug($arr_loan_data);
        echo json_encode(array("success"=>1,"sum"=>$emi_balance));
        
    }
  public function checkmonth($from_month=0,$loan_pkey = 0){
        // debug($from_month);
        $this->autoRender=false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $loan_months_new=$this->EmployeeLoanInfo->query("SELECT DISTINCT loan_month FROM emp_loan_info where loan_pkey='$loan_pkey' and status=1 and paid_status='A' and loan_emi!=0 and loan_month!='$from_month' order by loan_month");
        $this->set("loan_months_new", $loan_months_new);
        $data='';
        foreach ($loan_months_new as $key => $value) {
            $data.="<option>".$value['emp_loan_info']['loan_month']."</option>";
        }
        $last_month=$this->EmployeeLoanInfo->query("SELECT DISTINCT MAX(loan_month) as last FROM emp_loan_info where loan_pkey='$loan_pkey' and status=1 and paid_status='A' and loan_emi!=0");
        $start_month = (strtotime($last_month[0][0]['last']));
        $month = date('Y-m', strtotime("+1 month", $start_month));
        $data.="<option value='$month'>".$month."</option>";
        echo json_encode(array('success' => 1, 'data' => $data));

    }
     public function employeeemiloansave(){
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->LoanEmi->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $month_year = $arr_form_data['ctc_upload_type_'];
        $balance_due = $arr_form_data['balance_amount'];
        // debug($balance_due);
        $emi_amount = $arr_form_data['loan_emi'];
    $loan_balance = $balance_due - $emi_amount;
    // debug($loan_balance);
    if($loan_balance >= 0){


        // debug($month_year);
        // $encashment_master = $this->emi_upload($month_year);
        // debug($arr_form_data);
        // $encashment_master = $this->leaveencash($emp_pkey, $leaves, 0);
        $arr_form_data['amt'] = $arr_form_data['loan_emi'];
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        $arr_form_data['month_year'] = $month_year;



        
        $result = $this->LoanEmi->save($arr_form_data);
        $resp = array();
        $resp["success"] = 1;
        $resp["msg"] = "Employee loan Save successfully";
        echo json_encode($resp);
    }else{

    	$resp = array();
        $resp["success"] = 0;


        $resp["msg"] = "EMI amount should not be greater than balance amount";
        echo json_encode($resp);
    }
    }
  public function jsons($branch = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //debug($branch);
        //debug($_REQUEST['q']);
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = "and branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }
        if ($q != null) {
            $q_condition = "and first_name like '%$q%'";
        } else {
            $q_condition = "";
        }
        
//        if ($month != '') {
////          $mnthval = explode ("-", $slctmonth);
////          $selmonth = $mnthval[1];
//            $branch_month = "and au.emi_start_month='$month'";                
//        }else
//        {
//            $branch_month = '';
//        }
 
        $branch_array = $this->EmployeeDetails->query("select *,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where status = 1 $branch_condition $q_condition and emp_details.status=1 ORDER BY emp_pkey DESC ");
        //debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
//        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
  public function salarycheck() {
        $arr_request = $this->request->data;
        //debug($arr_request);
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $form_month = $arr_request['month_year'];
        $month = explode("-", $form_month);
        $year = $month[0];
        $mon = $month[1];
        $set_month = $year . '-' . $mon;
        $empfkey = $arr_request['empid'];
        $arr_salary_month_check = $this->EmployeeLoan->query("select emp_salary_slip.salary_amount FROM   emp_salary_slip WHERE month_year= '$set_month' AND emp_fkey= '$empfkey' and end_date_effective IS NULL ");
        // debug($arr_salary_month_check);
        //$extingsalary=$arr_salary_month_check[0]['emp_salary_slip']['salary_amount'];
        // debug($arr_salary_month_check);
        $this->set('arr_salary_month_check', $arr_salary_month_check);
        $data = array();
        $data['msg'] = "Salary already processed";
        $data['rows'] = $arr_salary_month_check;
        echo json_encode($data);
    }
      public function employeelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        // debug($arr_request_data);
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $month = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
       
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        // debug($resp_att["rows"]);
        $branch_condition = '';
        $emp_condition = '';
        $emi_month = '';

           if ($month != '') {
            $emp_month = "and elp.emi_start_month>= '$month' or elp.emi_end_month <= '$month'";
        }else{
            $emp_month =" ";
        }

        if ($branch_code!= '') {
            $branch_condition = "and EmployeeDetails.branch_code='$branch_code'";
        }else{
            $branch_condition =  " ";
        }
        if ($branch_code== 'All') {
            $branch_condition = "";
        }
        if ($emp_fkey!= '') {
            $emp_condition = "and elp.emp_fkey='$emp_fkey'";
        }else{
            $emp_condition = " ";
        }
        if ($emp_fkey== '0' || $emp_fkey== 'null') {
            $emp_condition = "";
        }
        //debug($emp_condition);

//        $counts = $this->EmployeeCTC->query("select COUNT(*) FROM emp_details AS EmployeeDetails 
//INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) 
//LEFT JOIN emp_loan AS elp ON (EmployeeDetails.emp_pkey = elp.emp_fkey) 
//LEFT JOIN emp_proff AS ep ON (EmployeeDetails.emp_pkey = ep.emp_fkey)
//LEFT JOIN emp_loan_info AS loan_info ON (EmployeeDetails.emp_pkey = loan_info.emp_fkey)
//INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) 
//WHERE EmployeeDetails.status = '1' AND elp.is_completed = 'N'   and elp.emp_loan_pkey 
//not  in(select loan_pkey from emp_loan_info where loan_month = '$month' and emp_fkey = '$emp_fkey' 
//and ((paid_status = 'P') or (loan_emi =0))) 
//and elp.emp_loan_pkey  in(select loan_pkey from emp_loan_info where loan_month = '$month' )
//$emp_condition $branch_condition and '$month' between elp.emi_start_month and elp.emi_end_month ");
        $counts = $this->EmployeeCTC->query("select COUNT(*) from (
Select ep.emp_company_id,UserCredentials.user_id, EmployeeInfo.EmpName,EmployeeInfo.branch,EmployeeInfo.department,EmployeeInfo.designation,loan_info.loan_month,elp.* 
,ifnull((select sum(amt) from emi_upload where emp_pkey=EmployeeDetails.emp_pkey and loan_pkey= elp.emp_loan_pkey),0) paid_amount,
loan_amount-ifnull((select sum(amt) from emi_upload where emp_pkey=EmployeeDetails.emp_pkey and loan_pkey= elp.emp_loan_pkey),0) balance_due 
FROM emp_details AS EmployeeDetails 
INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) 
LEFT JOIN emp_loan AS elp ON (EmployeeDetails.emp_pkey = elp.emp_fkey) 
LEFT JOIN emp_proff AS ep ON (EmployeeDetails.emp_pkey = ep.emp_fkey)
LEFT JOIN emp_loan_info AS loan_info ON (EmployeeDetails.emp_pkey = loan_info.emp_fkey)
INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) 
WHERE EmployeeDetails.status = '1' AND elp.is_completed = 'N'   and elp.emp_loan_pkey 
-- not  in(select loan_pkey from emp_loan_info where loan_month = '$month' $emp_condition 
-- and ((paid_status = 'P') or (loan_emi =0))) 
and elp.emp_loan_pkey  in(select loan_pkey from emp_loan_info where loan_month = '$month' ) and loan_info.loan_month = '$month'
$emp_condition $branch_condition and '$month' between elp.emi_start_month and elp.emi_end_month group by elp.emp_loan_pkey)a
where a.balance_due >0 ");
        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->EmployeeCTC->query("select * from (
Select ep.emp_company_id,UserCredentials.user_id, EmployeeInfo.EmpName,EmployeeInfo.branch,EmployeeInfo.department,EmployeeInfo.designation,loan_info.loan_month,elp.* 
,ifnull((select sum(amt) from emi_upload where emp_pkey=EmployeeDetails.emp_pkey and loan_pkey= elp.emp_loan_pkey),0) paid_amount,
loan_amount-ifnull((select sum(amt) from emi_upload where emp_pkey=EmployeeDetails.emp_pkey and loan_pkey= elp.emp_loan_pkey),0) balance_due 
FROM emp_details AS EmployeeDetails 
INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) 
LEFT JOIN emp_loan AS elp ON (EmployeeDetails.emp_pkey = elp.emp_fkey) 
LEFT JOIN emp_proff AS ep ON (EmployeeDetails.emp_pkey = ep.emp_fkey)
LEFT JOIN emp_loan_info AS loan_info ON (EmployeeDetails.emp_pkey = loan_info.emp_fkey)
INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) 
WHERE EmployeeDetails.status = '1' AND elp.is_completed = 'N'   and elp.emp_loan_pkey 
-- not  in(select loan_pkey from emp_loan_info where loan_month = '$month' $emp_condition 
-- and ((paid_status = 'P') or (loan_emi =0))) 
and elp.emp_loan_pkey  in(select loan_pkey from emp_loan_info where loan_month = '$month' ) and loan_info.loan_month = '$month'
$emp_condition $branch_condition and '$month' between elp.emi_start_month and elp.emi_end_month group by elp.emp_loan_pkey order by elp.emp_loan_pkey desc
)a
where a.balance_due >0  limit $limit  offset $ofst
 ");

         // $end_month = $value['a']['emi_end_month'];

               // 


               
               // if($end_month == $month){
               //      $emi_amount = $actual_emi;

               // }
        // debug($arr_att);
        $out = array();
        //debug($arr_att);
        foreach ($arr_att as $key => $value) {
            $end_month = $value['a']['emi_end_month'];
            
         // $balance = $paid + $emi_amount;
           $loan_amt = $value['a']['loan_amount'];
                $emi_amount = $value['a']['emi_amount'];
                // $paid = ' '.$value['a']['paid_amount'];
                $tenure = $value['a']['tenure'];
                $balance_due = ' '.$value['a']['balance_due'];
               // $total_paid = $balance - $loan_amt;
               $emi_balance = $emi_amount * $tenure;
               $require_emi = $emi_balance - $loan_amt;
               $actual_emi = $emi_amount - $require_emi;
               // debug($actual_emi);
               if($month != $end_month){
                   $emi_amount = $value['a']['emi_amount']; 
                   // debug($emi_amount);
                  
               }
               if($month == $end_month){
                $emi_amount = $actual_emi;
                // debug($emi_amount);

               }
               if($emi_amount > $balance_due){
                $emi_amount = $balance_due;
               }


            $out['empid'] = isset($value['a']['user_id']) ? $value['a']['user_id'] : '';
            $out['empname'] = isset($value['a']['EmpName']) ? $value['a']['EmpName'] : '';
            $out['emp_ctc_upload_pkey'] = isset($value['a']['emp_loan_pkey']) ? $value['a']['emp_loan_pkey'] : '';
            $out['emp_fkey'] = isset($value['a']['emp_fkey']) ? $value['a']['emp_fkey'] : '';
            $out['tenure'] = isset($value['a']['tenure']) ? $value['a']['tenure'] : '';
            $out['emi_amount'] = $emi_amount;
            $out['loan_amount'] = isset($value['a']['loan_amount']) ? $value['a']['loan_amount'] : '';
            $out['balance'] = isset($value['a']['balance_due']) ? $value['a']['balance_due'] : '';
            $out['paid'] = isset($value['a']['paid_amount']) ? $value['a']['paid_amount'] : '';
            $out['start_date_effective'] = isset($value['a']['emi_start_month']) ? $value['a']['emi_start_month'] : '';
// debug($out['balance']);
          //   debug($out);
            $resp_att["rows"][$key] = $out;
        }
        //debug($resp_att);
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
        // debug($resp_att);
    }
    
    public function downloademploanformat( $employee = '',$branch ='', $month='', $irandom='',$click_count='') {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
        // debug($employee);
        // debug($i);
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_emi_upload.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";
        // debug($file_name);
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        
        $worksheet = $objPHPExcel->getActiveSheet();
        // $worksheet->setCellValueByColumnAndRow(0, 1, "Employee EMI Upload");
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
        //         $worksheet->mergeCells('A1:F1');
        //          $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
        //             array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        //         );
        $worksheet->setCellValueByColumnAndRow(0, 1, "Sl No.");
        $worksheet->setCellValueByColumnAndRow(1, 1, "Employee ID");
        $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Name");
        $worksheet->setCellValueByColumnAndRow(3, 1, "Employee Company ID");
        $worksheet->setCellValueByColumnAndRow(4, 1, "Branch");
        $worksheet->setCellValueByColumnAndRow(5, 1, "Department");
        $worksheet->setCellValueByColumnAndRow(6, 1, "Designation");
        $worksheet->setCellValueByColumnAndRow(7, 1, "Loan Amount"); 
        $worksheet->setCellValueByColumnAndRow(8, 1, "EMI Amount");
        $worksheet->setCellValueByColumnAndRow(9, 1, "Paid Amount");
        $worksheet->setCellValueByColumnAndRow(10, 1, "Tenure");
        $worksheet->setCellValueByColumnAndRow(11, 1, "Balance Amount");
        $worksheet->setCellValueByColumnAndRow(12, 1, "EMI month");
        $worksheet->setCellValueByColumnAndRow(13, 1, "Loan Pkey");
        $worksheet->setCellValueByColumnAndRow(14, 1, "Random");
         // $worksheet->setCellValueByColumnAndRow(14, 1, "Exhausted Amount");


        // $worksheet->setCellValueByColumnAndRow(9, 1, "Start Date");
        // $worksheet->setCellValueByColumnAndRow(10, 1, "End Date");
        // $worksheet->setCellValueByColumnAndRow(9, 1, "End Date");
        // $worksheet->setCellValueByColumnAndRow(10, 1, "Upload Emi");
        // $worksheet->setCellValueByColumnAndRow(11, 1, "Loan PKEY");
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(23);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('j')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setVisible(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setVisible(false);

        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);

        $branch_condition = '';
        $emp_condition = '';
        $emp_month = '';
        //$msg =  "oops they is no data found sorry!!";
        // debug($msg);

        // if ($month != '') {
        //     $emp_month = "and elp.emi_start_month='$month'";
        // }else{
        //     $emp_month =" ";
        // }

        if ($branch!= '') {
            $branch_condition = "and EmployeeDetails.branch_code='$branch'";
        }else{
            $branch_condition =" ";
        }
        if ($branch== 'All') {
            $branch_condition = "";
        }
        if ($employee!= '') {
            $emp_condition = "and elp.emp_fkey='$employee'";
        }else{
            $emp_condition = " ";
        }
        if ($employee== '0' || $employee== 'null') {
            $emp_condition = "";
        }
        // debug($emp_condition);
        
        $param = '';
        if(isset($arr_request_data['emp'])){
            $param = "and ed.first_name like '%".$arr_request_data['emp']."%'";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
              $arr_emi = $this->EmployeeCTC->query("select * from (
Select ep.emp_company_id,UserCredentials.user_id, EmployeeInfo.EmpName,EmployeeInfo.branch,EmployeeInfo.department,EmployeeInfo.designation,loan_info.loan_month,elp.* 
,ifnull((select sum(amt) from emi_upload where emp_pkey=EmployeeDetails.emp_pkey and loan_pkey= elp.emp_loan_pkey),0) paid_amount,
loan_amount-ifnull((select sum(amt) from emi_upload where emp_pkey=EmployeeDetails.emp_pkey and loan_pkey= elp.emp_loan_pkey),0) balance_due 
FROM emp_details AS EmployeeDetails 
INNER JOIN user_credentials AS UserCredentials ON (EmployeeDetails.emp_pkey = UserCredentials.emp_fkey) 
LEFT JOIN emp_loan AS elp ON (EmployeeDetails.emp_pkey = elp.emp_fkey) 
LEFT JOIN emp_proff AS ep ON (EmployeeDetails.emp_pkey = ep.emp_fkey)
LEFT JOIN emp_loan_info AS loan_info ON (EmployeeDetails.emp_pkey = loan_info.emp_fkey)
INNER JOIN employee_info AS EmployeeInfo ON (EmployeeInfo.emp_pkey = UserCredentials.emp_fkey) 
WHERE EmployeeDetails.status = '1' AND elp.is_completed = 'N'   and elp.emp_loan_pkey 
-- not  in(select loan_pkey from emp_loan_info where loan_month = '$month' $emp_condition 
-- and ((paid_status = 'P') or (loan_emi =0))) 
and elp.emp_loan_pkey  in(select loan_pkey from emp_loan_info where loan_month = '$month' ) and loan_info.loan_month = '$month'
$emp_condition $branch_condition and '$month' between elp.emi_start_month and elp.emi_end_month group by elp.emp_loan_pkey order by EmployeeInfo.EmpName ASC
)a
where a.balance_due >0  
 ");
           // debug($arr_emi);
        
        $rowindex = 2;
        $columnindex = 0;
        $i = 0;
        if($arr_emi != ""){
            foreach ($arr_emi as $value) {
                // debug($arr_emi);
                $i+=1;
                // debug($value);
                $userid = $value['a']['user_id'];
                $empname = $value['a']['EmpName'];
                $company = $value['a']['emp_company_id'];
                $branch = $value['a']['branch'];
                $dept =$value['a']['department'];
                $des =$value['a']['designation'];
                $loan_amt = $value['a']['loan_amount'];
                $emi_amount = $value['a']['emi_amount'];
                $paid = ' '.$value['a']['paid_amount'];
                $tenure = $value['a']['tenure'];
                $balance_due = ' '.$value['a']['balance_due'];
                $loan_pkey =$value['a']['emp_loan_pkey'];
                $end_month = $value['a']['emi_end_month'];

                $balance = $paid + $emi_amount;
               $total_paid = $balance - $loan_amt;
               $emi_balance = $emi_amount * $tenure;
               $require_emi = $emi_balance - $loan_amt;
               $actual_emi = $emi_amount - $require_emi;
 
               if($end_month == $month){
                    $emi_amount = $actual_emi;

               }
                 if($emi_amount > $balance_due){
                $emi_amount = $balance_due;
               }
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $i);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $userid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $empname);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowindex, $company);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowindex, $branch);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowindex, $dept);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowindex, $des);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowindex, $loan_amt);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowindex, $emi_amount);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowindex, $paid);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowindex, $tenure);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowindex, $balance_due);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowindex, $month);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowindex, $loan_pkey);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowindex, $irandom);

               
                // if($balance == $loan_amt){
                // 	$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowindex, $total_paid);
                // }
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowindex, $loan_pkey);
                $rowindex++;
            }
           

        }else{
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex + 2, $msg);

        }
        
        $objPHPExcel->getActiveSheet()->setTitle('Employee EMI Upload ');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    
    public function uploadandsaveempemi($ctcuploadtype = 0, $icheck = 0, $click_count = 0) {
        $this->autoRender = FALSE;
        

        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID', 'Employee Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;


                                // debug($arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()]);
                            }
                            $index++;
                        }
                    }
                  
                    if ($mandatory_fields_warning) {

                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->LoanEmi->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');
                        // $count = count($arrayempdata);

                        // debug($count);

$status = 0;
$names = '';
$error = 0; 
$n_counts = '';
$count = count($arrayempdata);
// debug($count);
$array_n = array();
// debug($filename);

                        foreach ($arrayempdata as $key => $row) {
                            $arr_empctc_data = array();
                            $arr_status= array();
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            $emp_name = isset($row['Employee Name']) ? $row['Employee Name'] : '';
                            $loan_key = isset($row['Loan Pkey']) ? $row['Loan Pkey'] : '';
//                              $start = isset($row['PF']) ? $row['PF'] : '';
                                $Emi_amount = isset($row['EMI Amount']) ? abs($row['EMI Amount']) : '';
                                $month = isset($row['EMI month']) ? $row['EMI month'] : '';
                                $emi_amount = isset($row['EMI Amount']) ? abs($row['EMI Amount']) : '';
                                $loan_amount = isset($row['Loan Amount']) ? $row['Loan Amount'] : '';
                                $paid_amount = isset($row['Paid Amount']) ? abs($row['Paid Amount']): '';
                                $random = isset($row['Random']) ? $row['Random'] : '';
                                //  if($prev == $paid_amount){
                               //  echo json_encode(array('success' => 0, 'msg' => 'Duplicate Excel dont accept here please change it'));
                               //    exit;
                               // } 

                               if($icheck != $random && $click_count != '0'){
                                         echo json_encode(array('success' => 5, 'msg' =>'This excel file aleardy uploaded make sure upload new one'));
                                         exit;
                                }

                                $paid = $paid_amount + $emi_amount;
                                $balance = $loan_amount - $paid;
              
                        $count = $this->EmployeeLoan->query("select emi.loan_pkey,emi.emp_pkey,emi.month_year,
                            ifnull((select sum(amt) from emi_upload where emp_pkey=loan.emp_fkey and loan_pkey= loan.emp_loan_pkey),0) paid_amount,
                            loan_amount-ifnull((select sum(amt) from emi_upload where emp_pkey=loan.emp_fkey and loan_pkey= loan.emp_loan_pkey),0) balance_due from emp_loan loan
                            left join emi_upload as emi on (loan.emp_loan_pkey = emi.loan_pkey) where loan_pkey = $loan_key and amt = '$emi_amount' and month_year = '$month' ");
                           $b_paid = '';
                           $t_paid ='';
                             foreach ($count as $value) {
                               $b_paid = $value['0']['balance_due'];
                               $t_paid = $value['0']['paid_amount'];
                               }
                                
                            if($loan_key == ''){
                                continue;
                            }
                            if($Emi_amount == ''){
                                continue;
                            }
                            
                            $date = '';
                            $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            if ($user_id == '') {
                                continue;
                            }
                            //fetch emp_fkey using user_id
                            $arr_usercredentials = $this->UserCredentials->find('first', array(
                                'fields' => 'emp_fkey',
                                'conditions' => array(
                                    'user_id' => $user_id
                                )
                            )); 
                            $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';
                            // debug($arr_empctc_data);
                            $arr_empctc_data = array();
                            $arr_empctc_data['status'] = 1;
                            $arr_empctc_data['emp_pkey'] = $emp_fkey;
                            $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['loan_pkey'] = $loan_key; //UAN 
                            $arr_empctc_data['amt'] = $Emi_amount;
                            $arr_empctc_data['month_year'] = $month;
                            $arr_empctc_data['emp_pkey'] = $emp_fkey;


                             //    if($paid == $t_paid &&  $b_paid == $balance){
                             //    echo json_encode(array('success' => 5, 'msg' => 'This excel is already uploaded make sure upload a new one'));
                             //      exit;
                             // }
                             // if($b_paid == $balance){
                             //    echo json_encode(array('success' => 6,'msg' => $random));
                             //    exit;
                             // }
                             // debug($balance);
                             // debug($t_paid);

                             // if($paid_amount > $b_paid){
                             //    $names .= isset($row['Employee Name']) ? $row['Employee Name'].'-'.$row['Employee ID'].',' : '';
                             //    $status = 1;
                             //    continue;
                             // }
                             // debug($b_paid);
         //                     debug($emi_amount);
         // $f_bal = $b_paid + $emi_amount;
         // debug($f_bal);
         
              
                 
           if($paid > $loan_amount){
            
            $names .= isset($row['Employee Name']) ? $row['Employee Name'].'-'.$row['Employee ID'].'   ' : '';
            
            
             $status = 1;
            continue;
           }  
          // debug($names);
            $result1 = $this->LoanEmi->saveAll($arr_empctc_data);

                        } 
                      }
                       
                   if ($status == 1) {
                        echo json_encode(array('success' => 3, 'msg' => $names,'data' =>$names));   
                        exit;
                    }
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee EMI Amounts imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee EMI Amounts reviced successfully'));
                        exit;
                    }

                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee EMI Amounts revision failed! '));
                    exit;
                }
            }
        } else {
            
                echo json_encode(array('success' => 0, 'msg' => 'Please select a month '));
                exit;
            
            exit;
        }
              
    }
      public function jsonsb($branch = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
      
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        
        
     
       
        if($branch != null) {
             $branch_condition = " and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }

        if ($q != null) {
            if(is_numeric($q)){
            $q_condition = "and  emp_proff.emp_company_id like '%$q%' ";
        }else{
            $q_condition = "and first_name like '%$q%' ";
        }
        } else {
            $q_condition = "";
        }
         if($branch == 'All') {
             $branch_condition = " ";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 $branch_condition $q_condition ORDER BY first_name ASC ");
       // debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - '. $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
}