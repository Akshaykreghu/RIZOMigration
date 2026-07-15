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
class ProjectExpenseReportController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'ProjectExpenseReport';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array 
     */

    public $uses = array( 'CentralControl', 'Store','Site','ExpenseType','MaterialRequest', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'Beneficiary', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'EmpCtcTransaction', 'LeaveRequests', 'Designation','PoReturn','CompanyContactInfo','item_allocate', 'allocate_details');

    public $components = array('MasterdataManagement');

    public function hrreports() {
        $arr_reporttypes = array(
            'ProjectExpense' =>  'Project Expenses Report',
            'BeneficiaryExpense' =>  'Beneficiary Expense Report',
            'ExpenseTypeReport' =>  'Expense Type Report'
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '') {
        $this->autoRender = FALSE;
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                 //megha
                case 'ProjectExpense':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'BeneficiaryExpense':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'ExpenseTypeReport':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                default :
                    echo "No criterias found";
                    break;
            }
            
            $this->render('showreport');
        } else {
            echo "No criterias found";
        }
    }

    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '') {
        $this->autoRender = FALSE;
        if ($type != '' && $str_currentcriterias != '') {
            //$arr_currentcriterias = explode(',', $str_currentcriterias);
            //$arr_remainingcriterias = array_diff(array_flip($this->arr_employee_reportcriterias), $arr_currentcriterias);
            //$this->set('arr_remainingcriterias',array_flip($arr_remainingcriterias));
            $str_currentcriterias = "'" . str_replace(",", "','", $str_currentcriterias) . "'";
            $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type)))));
            $this->set('newindex', $newindex);
            $this->render('showcriteria');
        } else {
            return '';
        }
    }

    /*
     * Load criteria items
     */

    public function loadcriteriaitems($index, $str_criteria = '') {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
                $model = ($model == 'Beneficiary') ? 'Beneficiary' : $model;
                $this->set('criteria', $model);
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function listcriteriaitems($str_criteria = '') {
        $this->autoRender = false;
        $model = $str_criteria;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            $arr_order = array();
            $join = array();
            $fields = array();
            $conditions = array();
            if($model == 'Site'){
            $conditions = array("status" => 1,"expected_starting_date !=" => '0000-00-00');
            $arr_order = array("site_id" => 'asc');
            }else if($model == 'Beneficiary'){
            $conditions = array("status" => 1);
            $arr_order = array("company_name" => 'asc');    
            }else{
            $conditions = array("status" => 1);
            $arr_order = array("expense_type_name" => 'asc');    
            }
            
            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "order" => $arr_order, "joins" => $join)));
            //debug($arr_criteriaItemsDB);
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'Site':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['site_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['site_id'].'-'.$value['site_name'];
                        $key++;
                    }
                    break;
                case 'Beneficiary':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['contact_id'];
                        $arr_criteriaItems[$key]['text'] = $value['company_name'];
                        $key++;
                    }
                    break;
                case 'ExpenseType':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['expense_type_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['expense_type_name'];
                        $key++;
                    }
                    break;
            }
            echo json_encode($arr_criteriaItems);
        }
    }


    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;

        switch ($type) {
            
            case 'ProjectExpense':
                $this->GenerateProjectExpensereport($mode);
                break;	
            case 'BeneficiaryExpense':
                $this->GenerateBeneficiaryExpensereport($mode);
                break;
            case 'ExpenseTypeReport':
                $this->GenerateExpenseTypeReport($mode);
                break;
            default:
                return false;
                break;
        }
    }

    public function listemployeefields() {
        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
                $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'), $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'), $arr_empinformation_fields->getFieldHeadings('Departments'), $arr_empinformation_fields->getFieldHeadings('Grades'), $arr_empinformation_fields->getFieldHeadings('Verticals'), $arr_empinformation_fields->getFieldHeadings('Units')
        );
        $arr_emp_field_names = array(
            'EmployeeDetails' => $arr_empinformation_fields->getFieldNames('EmployeeDetails'),
            'EmployeeProfessionalDetails' => $arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails'),
            'Departments' => $arr_empinformation_fields->getFieldNames('Departments'),
            'Grades' => $arr_empinformation_fields->getFieldNames('Grades'),
            'Verticals' => $arr_empinformation_fields->getFieldNames('Verticals'),
            'Units' => $arr_empinformation_fields->getFieldNames('Units')
        );

        $resp_emp = array();
        $resp_emp["rows"] = array();
        foreach ($arr_emp_field_names as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $data['id'] = $key . '.' . $key1;
                $data['data'] = array($value1);
                $resp_emp["rows"][] = $data;
            }
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    private function _modelExists($modelName) {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }

  
//project expense details report
 private function GenerateProjectExpensereport($mode) {
        $this->Site->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $_REQUEST;
        $site = isset($arr_form_data['Site']['0'])?$arr_form_data['Site']['0']: '';
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto'])); 
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $arr_stocksummary_for_template = array();
        $conditions = '';
        if($site){
        $conditions = " and emp_expense_payment.expense_date >= '$from' and emp_expense_payment.expense_date <= '$to' ";
        $expense_condition = " and emp_expense_details.exp_date >= '$from' and emp_expense_details.exp_date <= '$to' ";
        $expense_opening = " and emp_expense_details.exp_date < '$from' ";
        $condition_opening = " and emp_expense_payment.expense_date < '$from' ";
        $condition_closing = " and emp_expense_payment.expense_date <= '$to' ";
        $conditions1 = " and transportation_expense.starting_date >= '$from' and transportation_expense.starting_date <= '$to' ";
        $conditions2 = " and transportation_expense.ending_date >= '$from' and transportation_expense.ending_date <= '$to' ";
        $conditions3 = " and transportation_expense.ending_date < '$from' ";
        $conditions4 = " and transportation_expense_payment.expensedate < '$from' ";
//        $expense_condition = " and emp_expense.expense_date >= '$from' and emp_expense.expense_date <= '$to' ";
//        $expense_opening = " and emp_expense.expense_date < '$from' ";
        }
        if(!isset($site)){
            echo "No Criteria Selected ";
            return false;
        } 
        
        
        //foreach ($arr_store as $val){
            $arr_empleaverequests = $this->Site->query("SELECT * from site where site_pkey = '$site' and status = 1");
//            $arr_empleaverequests = $this->Site->query("SELECT emp_expense.*,emp_expense_details.*,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details,beneficiary.company_name "
//                . "from emp_expense "
//                . "left join site on (site.site_pkey = emp_expense.vendor) "
//                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
//                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
//                . "where emp_expense.vendor = '$site'  "
//                . "ORDER BY emp_expenses_pkey desc ");
            $sitename = isset($arr_empleaverequests['0']['site']['site_name'])?$arr_empleaverequests['0']['site']['site_name']:'';
            $siteid = isset($arr_empleaverequests['0']['site']['site_id'])?$arr_empleaverequests['0']['site']['site_id']:'';
            //$startdate = isset($arr_empleaverequests['0']['site']['expected_starting_date'])?$arr_empleaverequests['0']['site']['expected_starting_date']:'';
            $startdate = isset($arr_empleaverequests['0']['site']['expected_starting_date'])?date('d-m-Y',strtotime($arr_empleaverequests['0']['site']['expected_starting_date'])):'';
            $projectdetails = isset($arr_empleaverequests["0"]["site"]["address"])?$arr_empleaverequests["0"]["site"]["address"]:'';
            $workdetails = isset($arr_empleaverequests["0"]["site"]["work_details"])?$arr_empleaverequests["0"]["site"]["work_details"]:'';
            $workvalue = isset($arr_empleaverequests["0"]["site"]["longitude"])?$arr_empleaverequests["0"]["site"]["longitude"]:0;
            
             // Calulating the difference in timestamps 
            $diff = strtotime($startdate) - strtotime($from); 
      
    // 1 day = 24 hours 
    // 24 * 60 * 60 = 86400 seconds 
            //debug(abs(round($diff / 86400));
            
//            if($from == $to){
//            $expdate = $from. ' ' . strtoupper(date("l", strtotime($from)));
//            }else{
//            $expdate = $from. ' ' . strtoupper(date("l", strtotime($from))). ' - ' . $to. ' ' . strtoupper(date("l", strtotime($to)));
//            }
            $from1 = date('d-m-Y', strtotime($arr_form_data['reportfrom']));
            $to1 = date('d-m-Y', strtotime($arr_form_data['reportto'])); 
            if($from == $to){
            $expdate = $from1. ' ' . strtoupper(date("l", strtotime($from1)));
            }else{
            $expdate = $from1. ' ' . strtoupper(date("l", strtotime($from1))). ' - ' . $to1. ' ' . strtoupper(date("l", strtotime($to1)));
            }
            $arr_empleaverequests_cond = $this->Site->query("SELECT expense_type.expense_type_name,emp_details.first_name,emp_details.last_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,sum(emp_expense_details.total) as totalfund,"
                . "emp_expense.*,emp_expense_details.*,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details,beneficiary.company_name "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_type_fkey ORDER BY expense_type.expense_type_name asc ");
            
            $arr_opening = $this->Site->query("SELECT expense_type.expense_type_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,sum(emp_expense_details.total) as totalfund,"
                . "emp_expense.*,emp_expense_details.*,sum(emp_expense_details.balance) as exptotal,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details,beneficiary.company_name,sum(emp_expense_payment.pay_amount) as paytotal "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $condition_opening and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_type_fkey ORDER BY emp_expenses_pkey desc ");
             
            $arr_site_cond = $this->Site->query("SELECT expense_type.expense_type_name,emp_details.first_name,emp_details.last_name,sum(emp_expense_details.total) as sumtotal "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and expense_heads.expense_head_pkey=2 and expense_type.expense_type_name not like '%local labor%' and emp_expense.vendor = '$site'  $expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_details_pkey ORDER BY expense_type.expense_type_name asc ");
            
            $arr_sub_cond = $this->Site->query("SELECT expense_type.expense_type_name,emp_details.first_name,emp_details.last_name,sum(emp_expense_details.total) as sumtotal "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_details on (emp_details.emp_pkey = emp_expense.emp_fkey) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and expense_heads.expense_head_pkey=3 and expense_type.expense_type_name not like '%local labor%' and emp_expense.vendor = '$site'  $expense_condition and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_type_fkey ORDER BY expense_type.expense_type_name asc ");
            
            $arr_locallabor_cond = $this->Site->query("SELECT expense_type.expense_type_name,sum(emp_expense_details.total) sum, "
                . "sum(emp_expense_payment.pay_amount) as paytotal "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and expense_heads.expense_head_pkey=2 and expense_type.expense_type_name like '%local labor%' and emp_expense.vendor = '$site'  $conditions and emp_expense.expense_status = 'Approved' "
                . "group by emp_expense_details.expense_type_fkey ORDER BY expense_type.expense_type_name asc ");
            
            
            $released = isset($arr_empleaverequests_cond["0"]["0"]["paytotal"])?$arr_empleaverequests_cond["0"]["0"]["paytotal"]:0;
            $released_opening = isset($arr_opening["0"]["0"]["paytotal"])?$arr_opening["0"]["0"]["paytotal"]:0;
            $bill = isset($arr_empleaverequests_cond["0"]["0"]["totalfund"])?$arr_empleaverequests_cond["0"]["0"]["totalfund"]:0;
            $bill_opening = isset($arr_opening["0"]["0"]["totalfund"])?$arr_opening["0"]["0"]["totalfund"]:0;
            $arr_paymentben_explist = $this->Site->query("SELECT beneficiary.company_name,expense_type.expense_type_pkey,emp_expense.beneficiary_fkey as b, "
                . "emp_expense.emp_expenses_pkey,(select sum(emp_expense_details.total) as totalsum "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_condition and emp_expense.expense_status = 'Approved' "
                . "and emp_expense.beneficiary_fkey > 0 and emp_expense.beneficiary_fkey = b Group BY emp_expense.beneficiary_fkey) as a, "
                . "(select sum(emp_expense_payment.pay_amount)  as paidsum "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_payment.status = 1 and emp_expense.vendor = '$site'  $conditions and emp_expense.expense_status = 'Approved' "
                . "and emp_expense.beneficiary_fkey > 0 and emp_expense.beneficiary_fkey = b Group BY emp_expense.beneficiary_fkey) as c, "
                . "(select sum(emp_expense_details.total) sum1 "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_opening and emp_expense.expense_status = 'Approved' "
                . "and emp_expense.beneficiary_fkey > 0 and emp_expense.beneficiary_fkey = b Group BY emp_expense.beneficiary_fkey) as d, "
                . "(select sum(emp_expense_payment.pay_amount) sum2 "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_payment.status = 1 and emp_expense.vendor = '$site'  $condition_opening and emp_expense.expense_status = 'Approved' "
                . "and emp_expense.beneficiary_fkey > 0 and emp_expense.beneficiary_fkey = b Group BY emp_expense.beneficiary_fkey) as e "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_payment.status = 1 and emp_expense.vendor = '$site'  $condition_closing and emp_expense.expense_status = 'Approved' "
                . " and  emp_expense.beneficiary_fkey > 0  Group BY emp_expense.beneficiary_fkey order by beneficiary.company_name asc");
         $arr_payment_explist = $this->Site->query("SELECT expense_type.expense_type_name,expense_type.expense_type_pkey as b,emp_expense.beneficiary_fkey, "
                . "emp_expense.emp_expenses_pkey,(select sum(emp_expense_details.total) as totalsum "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_condition and emp_expense.expense_status = 'Approved' "
                . "and (emp_expense.beneficiary_fkey is  NULL or emp_expense.beneficiary_fkey = '0') and emp_expense_details.expense_type_fkey = b Group BY emp_expense_details.expense_type_fkey) as a, "
                . "(select sum(emp_expense_payment.pay_amount) as paidsum "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_payment.status = 1 and emp_expense.vendor = '$site'  $conditions and emp_expense.expense_status = 'Approved' "
                . "and (emp_expense.beneficiary_fkey is  NULL or emp_expense.beneficiary_fkey = '0') and emp_expense_details.expense_type_fkey = b Group BY emp_expense_details.expense_type_fkey) as c, "
                . "(select sum(emp_expense_details.total) as totalsum "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_opening and emp_expense.expense_status = 'Approved' "
                . "and (emp_expense.beneficiary_fkey is  NULL or emp_expense.beneficiary_fkey = '0') and emp_expense_details.expense_type_fkey = b Group BY emp_expense_details.expense_type_fkey) as d, "
                . "(select sum(emp_expense_payment.pay_amount) as paidsum "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_payment.status = 1 and emp_expense.vendor = '$site'  $condition_opening and emp_expense.expense_status = 'Approved' "
                . "and (emp_expense.beneficiary_fkey is  NULL or emp_expense.beneficiary_fkey = '0') and emp_expense_details.expense_type_fkey = b Group BY emp_expense_details.expense_type_fkey) as e "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_payment.status = 1 and emp_expense.vendor = '$site'  $condition_closing and emp_expense.expense_status = 'Approved' "
                . " and (emp_expense.beneficiary_fkey is  NULL or emp_expense.beneficiary_fkey = '0') Group BY emp_expense_details.expense_type_fkey order by expense_type.expense_type_name asc");
            $arr_payment_particulars = $this->Site->query("SELECT expense_type.expense_type_name,expense_type.expense_type_pkey as b,
                (select sum(emp_expense_details.total) as totalsum from emp_expense_details 
                left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) 
                where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_condition 
                and emp_expense.expense_status = 'Approved'  and emp_expense_details.expense_type_fkey = b 
                Group BY emp_expense_details.expense_type_fkey) as today, 
                (select sum(emp_expense_details.payment) as paidsum from emp_expense_details 
                left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) 
                where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_condition 
                and emp_expense.expense_status = 'Approved' and emp_expense_details.expense_type_fkey = b 
                Group BY emp_expense_details.expense_type_fkey) as paid, 
                (select sum(emp_expense_details.total) as totalsum from emp_expense_details 
                left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey)
                where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_opening  and emp_expense.expense_status = 'Approved' 
                and emp_expense_details.expense_type_fkey = b Group BY emp_expense_details.expense_type_fkey) as openingtotal,
                (select sum(emp_expense_payment.pay_amount) as paidsum from emp_expense_details 
                left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) 
                left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) 
                where emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_payment.status = 1 and emp_expense.vendor = '$site'  $condition_opening and emp_expense.expense_status = 'Approved' 
                and emp_expense_details.expense_type_fkey = b Group BY emp_expense_details.expense_type_fkey) as openingpaid from emp_expense 
                left join site on (site.site_pkey = emp_expense.vendor) 
                left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) 
                left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) 
                left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey)
                where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense_payment.status = 1 and emp_expense.vendor = '$site'  $condition_closing  and emp_expense.expense_status = 'Approved' 
                Group BY emp_expense_details.expense_type_fkey order by expense_type.expense_type_name asc");
            
//            $arr_ben_explist = $this->Site->query("SELECT expense_type.expense_type_name,expense_type.expense_type_pkey, "
//                . "emp_expense.emp_expenses_pkey from emp_expense "
//                . "left join site on (site.site_pkey = emp_expense.vendor) "
//                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
//                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
//                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
//                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $condition_closing and emp_expense.expense_status = 'Approved' "
//                . "and emp_expense_details.payment_status='Pending' and emp_expense.beneficiary_fkey is NOT NULL Group BY expense_type.expense_type_pkey ");
//            $arr_payment_ben = $this->Site->query("SELECT expense_type.expense_type_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
//                . "sum(emp_expense_details.total) as totalsum,sum(emp_expense_details.cgst) as sumcgst,sum(emp_expense_details.sgst) as sumsgst,emp_expense.*,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details,beneficiary.company_name, "
//                . "sum(emp_expense_payment.pay_amount) as paidsum,emp_expense_details.* from emp_expense "
//                . "left join site on (site.site_pkey = emp_expense.vendor) "
//                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
//                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
//                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
//                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
//                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
//                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $conditions and emp_expense.expense_status = 'Approved' "
//                . "and emp_expense_details.payment_status='Pending' and emp_expense.beneficiary_fkey is NOT NULL Group BY emp_expense.beneficiary_fkey,emp_expense_details.payment_status ");
//            $arr_payment_pending = $this->Site->query("SELECT expense_type.expense_type_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
//                . "sum(emp_expense_details.total) as totalsum,sum(emp_expense_details.cgst) as sumcgst,sum(emp_expense_details.sgst) as sumsgst,emp_expense.*,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details, "
//                . "sum(emp_expense_payment.pay_amount) as paidsum,emp_expense_details.* from emp_expense "
//                . "left join site on (site.site_pkey = emp_expense.vendor) "
//                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
//                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
//                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
//                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
//                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
//                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $conditions and emp_expense.expense_status = 'Approved' "
//                . "and emp_expense_details.payment_status='Pending' and emp_expense.beneficiary_fkey is NULL group by emp_expense_details.expense_type_fkey");
//             $arr_pay = $this->Site->query("SELECT expense_type.expense_type_name,emp_expense_details.*,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
//                . "sum(emp_expense_details.total) as totalsum,sum(emp_expense_details.cgst) as sumcgst,sum(emp_expense_details.sgst) as sumsgst,emp_expense.*,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details,beneficiary.company_name, "
//                . "sum(emp_expense_payment.pay_amount) as paidsum from emp_expense "
//                . "left join site on (site.site_pkey = emp_expense.vendor) "
//                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
//                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
//                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
//                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
//                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
//                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $condition_opening and emp_expense.expense_status = 'Approved' "
//                . "and emp_expense_details.payment_status='Pending' and emp_expense.beneficiary_fkey is NOT NULL Group BY emp_expense.beneficiary_fkey,emp_expense_details.payment_status");
//             $arr_pay_pending = $this->Site->query("SELECT expense_type.expense_type_name,emp_expense_details.*,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
//                . "sum(emp_expense_details.total) as totalsum,sum(emp_expense_details.cgst) as sumcgst,sum(emp_expense_details.sgst) as sumsgst,emp_expense.*,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details, "
//                . "sum(emp_expense_payment.pay_amount) as paidsum from emp_expense "
//                . "left join site on (site.site_pkey = emp_expense.vendor) "
//                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
//                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
//                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
//                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
//                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
//                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $condition_opening and emp_expense.expense_status = 'Approved' "
//                . "and emp_expense_details.payment_status='Pending' and emp_expense.beneficiary_fkey is NULL group by emp_expense_details.expense_type_fkey");
            
            $arr_gst_explist = $this->Site->query("SELECT beneficiary.company_name,expense_type.expense_type_pkey,emp_expense.beneficiary_fkey as b, "
                . "emp_expense.emp_expenses_pkey,(select sum(emp_expense_details.total) as totalsum "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_condition and emp_expense.expense_status = 'Approved' "
                . "and emp_expense.beneficiary_fkey is NOT NULL and emp_expense.beneficiary_fkey = b and emp_expense_details.cgst is not null Group BY emp_expense.beneficiary_fkey) as a, "
                . "(select  sum(emp_expense_payment.pay_amount) as paidsum "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $conditions and emp_expense.expense_status = 'Approved' "
                . "and emp_expense.beneficiary_fkey is NOT NULL and emp_expense.beneficiary_fkey = b and emp_expense_details.cgst is not null Group BY emp_expense.beneficiary_fkey) as c, "
                . "(select sum(emp_expense_details.total) sum1 "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_opening and emp_expense.expense_status = 'Approved' "
                . "and emp_expense.beneficiary_fkey is NOT NULL and emp_expense.beneficiary_fkey = b and emp_expense_details.cgst is not null Group BY emp_expense.beneficiary_fkey) as d, "
                . "(select sum(emp_expense_payment.pay_amount) sum2 "
                . "from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $condition_opening and emp_expense.expense_status = 'Approved' "
                . "and emp_expense.beneficiary_fkey is NOT NULL and emp_expense.beneficiary_fkey = b and emp_expense_details.cgst is not null Group BY emp_expense.beneficiary_fkey) as e "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $condition_closing and emp_expense.expense_status = 'Approved' "
                . " and emp_expense.beneficiary_fkey is NOT NULL and emp_expense_details.cgst is not null Group BY emp_expense.beneficiary_fkey order by beneficiary.company_name asc"); 
            $arr_empleaverequests_ben = $this->Site->query("SELECT expense_type.expense_type_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
                . "sum(emp_expense_details.total) as totalsum,sum(emp_expense_details.cgst) as sumcgst,sum(emp_expense_details.sgst) as sumsgst,emp_expense.*,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details,beneficiary.company_name, "
                . "sum(emp_expense_payment.pay_amount) as paidsum from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $conditions and emp_expense.expense_status = 'Approved' "
                . "Group BY emp_expense.beneficiary_fkey order by beneficiary.company_name asc");
           
             $arr_ben = $this->Site->query("SELECT expense_type.expense_type_name,expense_heads.expense_head_name,expense_heads.expense_head_pkey,"
                . "sum(emp_expense_details.total) as totalsum,sum(emp_expense_details.cgst) as sumcgst,sum(emp_expense_details.sgst) as sumsgst,emp_expense.*,site.site_name,site.site_id,site.site_pkey,site.actual_starting_date,site.address,site.work_details,beneficiary.company_name "
                . "from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join beneficiary on (emp_expense.beneficiary_fkey = beneficiary.contact_id) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) "
                . "left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) "
                . "left join expense_heads on (expense_heads.expense_head_pkey= expense_type.expense_head_fkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $condition_opening and emp_expense.expense_status = 'Approved' "
                . "Group BY emp_expense.beneficiary_fkey ");
            $arr_vehicles = $this->Site->query("SELECT transportation_expense.*,vehicle_master.model_dec "
                . "from transportation_expense "
                . "left join site on (site.site_pkey = transportation_expense.site_fkey) "
                . "left join vehicle_master on (vehicle_master.vehicle_master_pkey = transportation_expense.vehicle_master_fkey) "
                . "where  transportation_expense.status = 1 and transportation_expense.site_fkey = '$site'  $conditions2");
            $arr_vehicles_NILLP = $this->Site->query("SELECT sum(transportation_expense.total_cost) as total "
                . "from transportation_expense "
                . "left join site on (site.site_pkey = transportation_expense.site_fkey) "
                . "where  transportation_expense.status = 1 and transportation_expense.site_fkey = '$site'  $conditions2");
            $arr_paid_NILLP_today = $this->Site->query("SELECT sum(transportation_expense_payment.payamount) as total "
                . "from transportation_expense "
                . "left join site on (site.site_pkey = transportation_expense.site_fkey) "
                . "left join transportation_expense_payment on (transportation_expense_payment.transportation_expense_fkey = transportation_expense.transportation_expense_pkey) "
                . "where  transportation_expense.status = 1 and transportation_expense.site_fkey = '$site' $conditions1 $conditions2");
            
            $NIILP_today = isset($arr_vehicles_NILLP['0']['0']['total'])?$arr_vehicles_NILLP['0']['0']['total']:0;
            $NIILP_today_paid = isset($arr_paid_NILLP_today['0']['0']['total'])?$arr_paid_NILLP_today['0']['0']['total']:0;
            $arr_paid_NILLP_opening = $this->Site->query("SELECT sum(transportation_expense_payment.payamount) as total "
                . "from transportation_expense "
                . "left join site on (site.site_pkey = transportation_expense.site_fkey) "
                . "left join transportation_expense_payment on (transportation_expense_payment.transportation_expense_fkey = transportation_expense.transportation_expense_pkey) "
                . "where  transportation_expense.status = 1 and transportation_expense.site_fkey = '$site' $conditions4");
            $arr_vehicles_NILLP_opening = $this->Site->query("SELECT sum(transportation_expense.total_cost) as total "
                . "from transportation_expense "
                . "left join site on (site.site_pkey = transportation_expense.site_fkey) "
                . "where  transportation_expense.status = 1 and transportation_expense.site_fkey = '$site' $conditions3");
            $NIILP_opening = isset($arr_vehicles_NILLP_opening['0']['0']['total'])?$arr_vehicles_NILLP_opening['0']['0']['total']:0;
            $NIILP_opening_paid = isset($arr_paid_NILLP_opening['0']['0']['total'])?$arr_paid_NILLP_opening['0']['0']['total']:0;
            $NIILP_opening_total = $NIILP_opening - $NIILP_opening_paid;
            $NIILP_total = $NIILP_today + $NIILP_opening_total;
//            debug($arr_paid_NILLP_opening);
//                debug($arr_vehicles_NILLP_opening);
            $works = $this->Site->query("SELECT work_detail "
                . "from project_activity "
                . "where status = 1 and project_fkey = '$site' and date = '$from' ");
            $work_detail = isset($works['0']['project_activity']['work_detail'])?$works['0']['project_activity']['work_detail']:'';
            $expenseheads = $this->Site->query("select expense_head_pkey,expense_head_name from expense_heads where status = 1");
            $expensetypes = $this->Site->query("select expense_type_pkey,expense_type_name,expense_head_fkey from expense_type where status = 1 order by expense_type_name asc");
            $sum = $this->Site->query("select emp_expense_details.cgst from emp_expense "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) where "
                . "emp_expense_details.status = 1 and emp_expense.vendor = '$site'  and emp_expense_payment.balance = 0 "
                . "and emp_expense_details.cgst > 0 $condition_opening group by emp_expense_details.expense_details_pkey");
            $cstsum = 0;
            $cgsttotal = 0;
            if(!empty($sum)){
            foreach ($sum as $val) {
                $cstsum = $val['emp_expense_details']['cgst'] + $val['emp_expense_details']['cgst'];
                $cgsttotal += $cstsum;
            }}
            $arr_gst = $this->Site->query("SELECT sum(emp_expense_details.cgst) as totalfund,sum(emp_expense_details.sgst) as paytotal, "
                . "sum(emp_expense_details.igst) as igstsum from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$site'  $expense_condition and emp_expense.expense_status = 'Approved' "
                . "and (emp_expense_details.cgst is not null or emp_expense_details.igst is not null) "
                . "ORDER BY emp_expenses_pkey desc ");
            $arr_opening_gst = $this->Site->query("SELECT sum(emp_expense_details.cgst) as totalfund,sum(emp_expense_details.sgst) as paytotal, "
                . "sum(emp_expense_details.igst) as igstsum from emp_expense "
                . "left join site on (site.site_pkey = emp_expense.vendor) "
                . "left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) "
                . "where  emp_expense.status = 1 and emp_expense_details.status = 1  and emp_expense.vendor = '$site'  $expense_opening and emp_expense.expense_status = 'Approved' "
                . "and (emp_expense_details.cgst is not null or emp_expense_details.igst is not null) "
                . "ORDER BY emp_expenses_pkey desc ");
           
            $gstinput = isset($arr_gst["0"]["0"]["totalfund"])?round($arr_gst["0"]["0"]["totalfund"],0)+round($arr_gst["0"]["0"]["paytotal"],0):'-';
            $gstinput_opening = isset($arr_opening_gst["0"]["0"]["totalfund"])?round($arr_opening_gst["0"]["0"]["totalfund"],0)+round($arr_opening_gst["0"]["0"]["paytotal"],0) - round($cgsttotal,0):'-';
            //$gstoutput = isset($arr_opening_gst["0"]["0"]["paytotal"])?$arr_opening_gst["0"]["0"]["paytotal"]:'-';
            //$gstoutput_opening = isset($arr_gst["0"]["0"]["paytotal"])?$arr_gst["0"]["0"]["paytotal"]:'-';
            $billtoday_condition = " and project_income.invoice_date >= '$from' and project_income.invoice_date <= '$to' ";
            $bill_opening = " and project_income.invoice_date < '$from' ";
            $arr_bill = $this->Site->query("SELECT sum(project_income.total) as sumtotal,sum(project_income.cgst) as sumcgst,sum(project_income.sgst) as sumsgst "
                . "from project_income "
                . "where  project_income.status = 1 and project_fkey = '$site' $bill_opening ");
            $arr_bill_rec = $this->Site->query("SELECT sum(project_income_payment.payamount) as paytotal from project_income "
                . "left join project_income_payment on (project_income_payment.project_income_fkey = project_income.project_income_pkey) "
                . "where  project_income.status = 1 and project_income_payment.status = 1 and project_fkey = '$site' $bill_opening ");
            $arr_bill_today = $this->Site->query("SELECT sum(project_income.total) as sumtotal,sum(project_income.cgst) as sumcgst,sum(project_income.sgst) as sumsgst "
                . "from project_income "
                . "where  project_income.status = 1  and project_fkey = '$site' $billtoday_condition ");
            $arr_bill_today_rec = $this->Site->query("SELECT sum(project_income_payment.payamount) as paytotal from project_income "
                . "left join project_income_payment on (project_income_payment.project_income_fkey = project_income.project_income_pkey) "
                . "where  project_income.status = 1 and project_income_payment.status = 1 and project_fkey = '$site' $billtoday_condition ");
          
            $bill_opening_amount = isset($arr_bill['0']['0']['sumtotal'])?$arr_bill['0']['0']['sumtotal']:'-';
            $bill_today_amount = isset($arr_bill_today['0']['0']['sumtotal'])?$arr_bill_today['0']['0']['sumtotal']:'-';
            $bill_total = $bill_opening_amount + $bill_today_amount;
            $output = isset($arr_bill['0']['0']['sumcgst'])?round($arr_bill['0']['0']['sumcgst'],0) + round($arr_bill['0']['0']['sumsgst'],0):'-';
            if($bill_total == 0){$bill_total='-';}
            $received_opening_amount = isset($arr_bill_rec['0']['0']['paytotal'])?$arr_bill_rec['0']['0']['paytotal']:'-';
            $received_today_amount = isset($arr_bill_today_rec['0']['0']['paytotal'])?$arr_bill_today_rec['0']['0']['paytotal']:'-';
            $received_total = $received_opening_amount + $received_today_amount;
            $output_today = isset($arr_bill_today['0']['0']['sumcgst'])?round($arr_bill_today['0']['0']['sumcgst']) + round($arr_bill_today['0']['0']['sumsgst']):'-';
            if($received_total == 0){$received_total='-';}
           // if (!empty($arr_empleaverequests)) {
           // $arr_stocksummary_for_template[] = $arr_empleaverequests;    
           // } 
       // }
     // debug($arr_empleaverequests);
        

       ini_set('memory_limit', '-1');
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        //$this->set('month', $from. ' - '.$to);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('stockalldetails');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('itemdetailsreport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "ProjectExpenseReport.xlsx" : "ProjectExpenseReport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Project Expense Report");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(1, 1, "NALAKATH INFRASTRUCTURE LLP");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 1)->getFont()->setSize(20);
                for ($col = 'B'; $col !== 'L'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('B1:L1');
                $worksheet->getStyle('B1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                //$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(false);
                //$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setWidth('20');
                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
                 $arr_count_day = $this->Site->query("SELECT count(distinct(expense_date)) FROM `emp_expense` where expense_date between '$startdate' and '$from' and vendor = '$site' and status = 1");
                $days = $arr_count_day['0']['0']['count(distinct(expense_date))'];
               // $diff= abs(strtotime($from) - strtotime($startdate));
               // $days = $diff/(60 * 60 * 24) + 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 2, "Project ID:" . $siteid  );
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('B2:E2');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 2, "Project Name:" . $sitename  );
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('F2:L2');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 3, "Project Details:" . $projectdetails  );
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 3)->getFont()->setBold(true);
                $worksheet->mergeCells('B3:L3');
                 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 4, "Project Start Date:" . $startdate  );
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 4)->getFont()->setBold(true);
                $worksheet->mergeCells('B4:E4');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 4, "Work Details:" . $work_detail  );
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 4)->getFont()->setBold(true);
                $worksheet->mergeCells('F4:O4');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 5, "Date of Expenses :" . $expdate  );
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 5)->getFont()->setBold(true);
                $worksheet->mergeCells('M1:O3');
                $worksheet->getStyle('M1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,)
                        
                );
                
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, 1, "DAY : ".$days);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, 1)->getFont()->setSize(20);
                $worksheet->mergeCells('B5:O5');
                $worksheet->mergeCells('B6:F6');
                $worksheet->mergeCells('H6:L7');
                
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 6, "Particulars");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 6)->getFont()->setBold(true);
                $worksheet->getStyle('H6')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('M6:O6');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, 6, "Project Expenses");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, 6)->getFont()->setBold(true);
                $worksheet->getStyle('M6')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, 7, "Opening");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, 7)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, 7, "Today");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, 7)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, 7, "Balance");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, 7)->getFont()->setBold(true);
                $rowcount = 8;
                $total_opening = 0;
                $opening_today = 0;
                $paytotal = 0;
                
                foreach ($expenseheads as $val) {
                $head = $val['expense_heads']['expense_head_name'];
                $key = $val['expense_heads']['expense_head_pkey'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $head);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount); 
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                $rowcount++;
                
                foreach ($expensetypes as $value) {
                if($key == $value['expense_type']['expense_head_fkey']){
                $type = $value['expense_type']['expense_type_name']; 
                $pkey = $value['expense_type']['expense_type_pkey'];
                $opening = 0;
                
                $amout = 0;
                
//                foreach($arr_empleaverequests_cond as $values){
//                    if($pkey == $values['emp_expense_details']['expense_type_fkey']){
//                       // $amout = $values['0']['exptotal'];
//                        //$opening_today = $values['0']['totalfund'];
//                        $opening_today = $values['emp_expense']['expenses_amount'];
//                        //$opening_today = $values['0']['exptotal'] + $opening_today;
//                        $total_today = $opening_today + $total_today;
//                    }
//                }
//                foreach($arr_opening as $value){
//                    if($pkey == $value['emp_expense_details']['expense_type_fkey']){
//                        foreach($arr_empleaverequests_cond as $valu){
//                           if($pkey == $valu['emp_expense_details']['expense_type_fkey']){
//                              $paytotal = $valu['0']['totalfund']; 
//                           }
//                        }
//                        $amout = $value['0']['exptotal'];
//                        //$opening = $value['0']['totalfund'] + $opening - $paytotal;
//                        $opening = $value['0']['totalfund'] ;
//                        //$total = $value['0']['totalfund'] + $total - $paytotal;
//                        //$total = $value['0']['totalfund'] + $total ;
//                         $total = $amout + $total ;
//                    }
//                }
                //debug($arr_payment_particulars);
                $today = 0;
                $total_bal = 0;
                $s =0;
                if(!empty($arr_payment_particulars)){
                foreach ($arr_payment_particulars as $value) {
                     if($pkey == $value['expense_type']['b']){
                        $s++;
                        $total = isset($value['0']['openingtotal'])?$value['0']['openingtotal']-$value['0']['openingpaid']:'-';
                        $today = isset($value['0']['today'])?$value['0']['today']:'-';
                        $total_bal= $total + $today;
                        $total_opening +=$total;
                        $opening_today +=$today;
                        $paytotal +=$total_bal;
                } } }
                if($type  == 'Transportation NILLP'){
                if($NIILP_opening_total > 0 || $NIILP_today > 0){
                 $s = 1;
                 $total = $NIILP_opening_total;
                 $today = $NIILP_today;
                 $total_bal= $NIILP_total;   
                 $total_opening +=$total;
                 $opening_today +=$today;
                 $paytotal +=$total_bal;
                }
                }
                if($s==0){
                $total = 0;
                $today = 0;
                $total_bal = 0;
                }
                if($total ==0){$total ='-';}
                if($today ==0){$today =' ';}
                if($total_bal ==0){$total_bal ='-';}
                if($total > 0 || $today > 0){
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $type);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount); 
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $total);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $today);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $total_bal);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $rowcount++;   
                }
                }
                }
        }
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "Total");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount,$total_opening);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $opening_today);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $paytotal);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                
                $objPHPExcel->getActiveSheet()->getStyle('M9:M'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('N9:N'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('O9:O'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                
                $worksheet->getStyle('H'.$rowcount.':O'.$rowcount)->applyFromArray(
                   array(    'borders' => array(
                  'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
//                $rowcount++;
//                $worksheet->mergeCells('H'.$rowcount.':O'.$rowcount);
                $worksheet->getStyle("H6:O".$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                $rowcount = $rowcount + 2;
                $row_count= $rowcount;
//                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "GST");
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, "Opening");
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, "Today");
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, "Balance");
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
//                $rowcount++;
//                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "Input");
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $gstinput_opening);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $gstinput);
//                $gst_input_bal = $gstinput + $gstinput_opening;
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $gst_input_bal);
//                $rowcount++;
//                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "Output");
//                
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $output);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $output_today);
//                $output_total = $output + $output_today;
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $output_total);
//                $rowcount++;
//                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
//                $worksheet->getStyle('H'.$rowcount.':O'.$rowcount)->applyFromArray(
//                 array(
//                  'borders' => array(
//                  'allborders' => array(
//                  'style' => PHPExcel_Style_Border::BORDER_THIN,
//                  'color' => array('rgb' => '000000')
//                  )
//                 )
//                )
//                );
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "GST Payable");
//                $gst_in_total = $gstinput_opening - $output;
//                $gst_out_total = abs($gstinput - $output_today);
//                $gst_bal_total = $gst_input_bal - $output_total;
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $gst_in_total);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $gst_out_total);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $gst_bal_total);
                $l = $rowcount - 2;
//                $objPHPExcel->getActiveSheet()->getStyle('M'.$l.':M'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//                $objPHPExcel->getActiveSheet()->getStyle('N'.$l.':N'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//                $objPHPExcel->getActiveSheet()->getStyle('O'.$l.':O'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
//                
//                $worksheet->getStyle('H'.$rowcount.':O'.$rowcount)->applyFromArray(
//                   array(    'borders' => array(
//                  'bottom' => array(
//                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
//                   ) )));
//                $rowcount++;
                
                //first part
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 7, "Particulars");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 7)->getFont()->setBold(true);
                $worksheet->getStyle('B7')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                
                $worksheet->mergeCells('B7:F7');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 7, "Expenses");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, 7)->getFont()->setBold(true);
                //$worksheet->mergeCells('F7:G7');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 8, "Material Purchases");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 8)->getFont()->setBold(true);
                $worksheet->mergeCells('B8:F8');
                $rowcount = 9;
                $i_total = 0;
                $i = 0;
             //   debug($arr_empleaverequests_cond);
                foreach ($arr_empleaverequests_cond as $value) {
                    if($value['expense_heads']['expense_head_pkey'] == 1){
                        $i++;
                        $exptype = $value['expense_type']['expense_type_name'];
                        $total = $value['0']['totalfund'];
                        $i_total = $i_total + $total;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $exptype);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $total);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount++;
                    }
                }
                if($i == 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Nil');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                }
                
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount = $rowcount + 1;
//                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
//                $rowcount = $rowcount + 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Site Expenses");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount++;
                $j_total = 0;
                $j = 0;
                
                foreach ($arr_site_cond as $value) {
                        $j++;
                        $exptype = $value['expense_type']['expense_type_name'];
                        $total = $value['0']['sumtotal'];
                        $j_total = $j_total + $total;
                        $name = isset($value['emp_details']['first_name'])?' : '.$value['emp_details']['first_name'].' - '.$value['emp_details']['last_name']:'';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $exptype.$name);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $total);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount++;
                }
                if($j == 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Nil');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                }
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount = $rowcount + 1;
//                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
//                $rowcount = $rowcount + 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Transportation Charges");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount++;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Transportation Charges ('.$siteid.')');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                       // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 0);
                       // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount++;
                        $k_total = 0;
                        $k = 0;
                foreach ($arr_vehicles as $value) {
                        $expame = $value['vehicle_master']['model_dec'];
                        $exp = $value['transportation_expense']['total_cost'];
                        $k_total = $k_total + $exp;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $expame);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $exp);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount++;
                        $k++;
                }
                if($k == 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Nil');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                }
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, '(Minivan)');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $worksheet->mergeCells('A'.$rowcount.':E'.$rowcount);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 0);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $rowcount++;
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, '(Bolero)');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $worksheet->mergeCells('A'.$rowcount.':E'.$rowcount);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 0);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $rowcount++;
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, '(Etios)');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $worksheet->mergeCells('A'.$rowcount.':E'.$rowcount);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 0);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount = $rowcount + 1;
//                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
//                $rowcount = $rowcount + 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Local Labour");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount++;
                $n_total = 0;
                $n = 0;
                foreach ($arr_locallabor_cond as $value) {
                        $n++;
                        $exptype_local = $value['expense_type']['expense_type_name'];
                        $local_total = $value['0']['sum'];
                        $n_total = $n_total + $local_total;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $exptype_local);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $local_total);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount++;
                }
                if($j == 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Nil');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                }
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount++;
//                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
//                $rowcount = $rowcount + 1;
                $m = 0;
                $m_total = 0;
                if(!empty($arr_sub_cond)){
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Sub Contracts");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount++;
                foreach ($arr_sub_cond as $value) {
                    $m++;
                        $exptype = $value['expense_type']['expense_type_name'];
                        $total = $value['0']['sumtotal'];
                        $m_total = $m_total + $total;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $exptype);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $total);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount++;
                }
                if($l == 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Nil');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                }
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount = $rowcount + 1;
//                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
//                $rowcount = $rowcount + 1;
                }
                $admin = 0;
                $l_total = 0;
                 foreach ($arr_empleaverequests_cond as $value) {
                    if($value['expense_heads']['expense_head_pkey'] == 4){
                        $admin = 1;
                    }
                 }
                if($admin==1){
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Administration Charges");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount++;
                
                $l = 0;
                foreach ($arr_empleaverequests_cond as $value) {
                    if($value['expense_heads']['expense_head_pkey'] == 4){
                        $l++;
                        $exptype = $value['expense_type']['expense_type_name'];
                        $total = $value['0']['totalfund'];
                        $l_total = $l_total + $total;
                        $name = isset($value['emp_details']['first_name'])?' : '.$value['emp_details']['first_name'].' - '.$value['emp_details']['last_name']:'';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $exptype.$name);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $total);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount++;
                    }
                }
                if($l == 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Nil');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                }
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $rowcount = $rowcount + 1;
//                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
//                $rowcount = $rowcount + 1;
                }
                $subtotal = $i_total + $j_total + $k_total + $n_total +$l_total+$m_total;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Sub Total");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $worksheet->mergeCells('B'.$rowcount.':F'.$rowcount);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $subtotal);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('G9:G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                
                $worksheet->getStyle('G'.$rowcount.':G'.$rowcount)->applyFromArray(
                   array(    'borders' => array(
//                  'left' => array(
//                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
//                   ),
//                  'right' => array(
//                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
//                   ),
                  'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ),
                  'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Payment Pendings");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowcount, "Opening");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, "Today");
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
//                $worksheet->mergeCells('D'.$rowcount.':E'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, "Debit");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, "Credit");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, "Balance");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $rowcount++;
                $l = 0;
                $t_opening = 0;
                $total_bal = 0;
                $paidsum1 = 0;
                $total_paid = 0;
                $name ="";
                $closing_bal = 0;
                $total = 0;
                $total_bal1 = 0;
                $paymentcount = $rowcount;
                if(!empty($arr_paymentben_explist)){
                foreach ($arr_paymentben_explist as $value) {
                $total_opening = 0;
                       // $j++;
                        $pkey = $value['emp_expense']['emp_expenses_pkey'];
                        $name = $value['beneficiary']['company_name'];
                        $total = isset($value['0']['a'])?$value['0']['a']:'-';
                        $paidsum1 = isset($value['0']['c'])?$value['0']['c']:'-';
                        $total_opening = isset($value['0']['d'])?$value['0']['d'] - $value['0']['e']:'-';
                        $t_opening += $total_opening;
                        $total_bal1 += $total;
                        $total_paid += $paidsum1;
//                        $l = $l + $total;
//                        $totalpaid1 = $totalpaid1 + $paidsum1;
//                        $ben = $val['emp_expense']['beneficiary_fkey'];
//                 
//                foreach($arr_pay as $values){
//                    if($name ==""){
//                        $name = $values['beneficiary']['company_name'];
//                        $total = $values['0']['totalsum'];
//                        $paidsum1 = $values['0']['paidsum'];
//                        $l = $l + $total;
//                        $totalpaid1 = $totalpaid1 + $paidsum1; 
//                        $ben = $val['emp_expense']['beneficiary_fkey'];
//                    }
//                    if($ben == $values['emp_expense']['beneficiary_fkey']){
//                        $total_opening = $values['0']['totalsum'] - $values['0']['paidsum'];
//                        $t_opening = abs($t_opening + $total_opening - $paidsum1) ;
//                    }
//                }
                  $total_bal = abs($total_opening - $paidsum1 + $total);
                        $closing_bal += $total_bal;
                        if($total_bal > 0 || $paidsum1 > 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $name);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $total_opening);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $total);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $paidsum1);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $total_bal);
		        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true); 
                        $rowcount++;
                        }
                } }
                $total_opening = 0;
                $total = 0;
                $paidsum1 = 0;
                $total_bal = 0;
                //'Transportation NILLP'; removing megha on 31/10/2020
                
//                  if($NIILP_opening_total > 0 || $NIILP_today > 0 || $NIILP_total >0 ){
//                      $name = 'Transportation NILLP';
//                      $total_opening = $NIILP_opening_total;
//                      $total = $NIILP_today;
//                      $paidsum1 = $NIILP_today_paid;
//                      $total_bal= $NIILP_total - $NIILP_today_paid;   
//                      $t_opening += $total_opening;
//                        $total_bal1 += $total;
//                        $total_paid += $paidsum1;
//                        } 
//                        $total_bal = abs($total_opening - $paidsum1 + $total);
//                        $closing_bal += $total_bal;
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $name);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $total_opening);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $total);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $paidsum1);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $total_bal);
//		        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true); 
//                        $rowcount++;
            //------------------  'Transportation NILLP'; removing megha on 31/10/2020 
                if(!empty($arr_payment_explist)){
                foreach ($arr_payment_explist as $value) {
                $total_opening = 0;
                       // $j++;
                        $pkey = $value['emp_expense']['emp_expenses_pkey'];
                        $name = $value['expense_type']['expense_type_name'];
                        $total = isset($value['0']['a'])?$value['0']['a']:'-';
                        $paidsum1 = isset($value['0']['c'])?$value['0']['c']:'-';
                        $total_opening = isset($value['0']['d'])?$value['0']['d'] - $value['0']['e']:'-';
                        $t_opening += $total_opening;
                        $total_bal1 += $total;
                        $total_paid += $paidsum1;
//                foreach($arr_payment_ben as $val){
//                    if($pkey == $val['emp_expense']['emp_expenses_pkey']){
//                        $name = $val['beneficiary']['company_name'];
//                        $total = $val['0']['totalsum'];
//                        $paidsum1 = $val['0']['paidsum'];
//                        $l = $l + $total;
//                        $totalpaid1 = $totalpaid1 + $paidsum1;
//                        $ben = $val['emp_expense']['beneficiary_fkey'];
//                    }         
//                }
//                foreach($arr_pay as $values){
//                    if($name ==""){
//                        $name = $values['beneficiary']['company_name'];
//                        $total = $values['0']['totalsum'];
//                        $paidsum1 = $values['0']['paidsum'];
//                        $l = $l + $total;
//                        $totalpaid1 = $totalpaid1 + $paidsum1; 
//                        $ben = $val['emp_expense']['beneficiary_fkey'];
//                    }
//                    if($ben == $values['emp_expense']['beneficiary_fkey']){
//                        $total_opening = $values['0']['totalsum'] - $values['0']['paidsum'];
//                        $t_opening = abs($t_opening + $total_opening - $paidsum1) ;
//                    }
//                }
                        $total_bal = abs($total_opening - $paidsum1 + $total);
                        $closing_bal += $total_bal;
                        if($total_bal > 0 || $paidsum1 > 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $name);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $total_opening);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $total);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $paidsum1);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $total_bal);
		        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);   
                        $rowcount++;
                        }
                } }
                $bal_closing = $t_opening + $l - $paidsum1;
                //$rowcount++;
//                $l1 = 0;
//                $t_opening1 = 0;
//                $total_bal1 = 0;
//                $paidsum11 = 0;
//                $totalpaid11 = 0;
//                $name1="";
//                if(!empty($arr_payment_pending)){
//                foreach ($arr_payment_explist as $value) {
//                $total_opening1 = 0;
//                $pkey1 = $value['emp_expense']['emp_expenses_pkey'];
//                $total1 = $value['0']['totalsum'];
//                     foreach($arr_payment_pending as $val){
//                        if($pkey1 == $val['emp_expense']['emp_expenses_pkey']){
//                        $name1 = $value['expense_type']['expense_type_name'];
//                        $total1 = $val['0']['totalsum'];
//                        $paidsum11 = $val['0']['paidsum'];
//                        $l1 = $l1 + $total1;
//                        $totalpaid11 = $totalpaid11 + $paidsum11;
//                        $ben1 = $val['emp_expense']['beneficiary_fkey'];
//                        }
//                     }
//                foreach($arr_pay_pending as $values){
//                    if($name1 ==""){
//                        $name1 = $value['expense_type']['expense_type_name'];
//                        $total1 = $values['0']['totalsum'];
//                        $paidsum11 = $values['0']['paidsum'];
//                        $l1 = $l1 + $total1;
//                        $totalpaid11 = $totalpaid11 + $paidsum11;
//                        $ben1 = $val['emp_expense']['beneficiary_fkey'];
//                    }
//                    if($ben1 == $values['emp_expense']['beneficiary_fkey']){
//                        $total_opening1 = $values['0']['totalsum'] - $values['0']['paidsum'];
//                        $t_opening1 = abs($t_opening1 + $total_opening1 - $paidsum11) ;
//                    }
//                } 
//                        $total_bal1 = abs($total_opening1 - $paidsum11 + $total1);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $name1);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $total_opening1);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $total1);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $paidsum11);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $total_bal1);
//		        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);  
//                        $rowcount++;
//                } }
//                $bal_closing1 = $t_opening1 + $l1 - $paidsum11;
//                $total_openings = $t_opening + $t_opening1;
//                $total_l = $l + $l1;
//                $total_paid = $total + $total1;
//                $closing_bal = $bal_closing + $bal_closing1;
                $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Total");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowcount, $total_openings);
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, $total_l);
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
//                //$worksheet->mergeCells('D'.$rowcount.':E'.$rowcount);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, $total_paid);
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $closing_bal);
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowcount, $t_opening);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, $total_bal1);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                //$worksheet->mergeCells('D'.$rowcount.':E'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, $total_paid);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $closing_bal);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$paymentcount.':D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$paymentcount.':E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$paymentcount.':F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$paymentcount.':G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                
                $worksheet->getStyle('B'.$rowcount.':G'.$rowcount)->applyFromArray(
                   array(    'borders' => array(
                  'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "GST Bill Pendings");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowcount, "Opening");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, "Today");
//		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                //$worksheet->mergeCells('D'.$rowcount.':E'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, "Pending");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, "Received");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, "Balance");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $worksheet->getStyle("B6:G".$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                $rowcount++;
                $m = 0;
                $tl_opening = 0;
                $total_balance = 0;
                $paidsum = 0;
                $totalpaid = 0;
                $gstopening = 0;
                $gsttoday = 0;
                $gstpaid = 0;
                $gsttotal = 0;
                $gstcount = $rowcount;
                 if(!empty($arr_gst_explist)){
                foreach ($arr_gst_explist as $value) {
                $total_opening = 0;
                       // $j++;
                        $pkey = $value['emp_expense']['emp_expenses_pkey'];
                        $name = $value['beneficiary']['company_name'];
                        $total = isset($value['0']['a'])?$value['0']['a']:'-';
                        $paidsum1 = isset($value['0']['c'])?$value['0']['c']:'-';
                        $total_opening = isset($value['0']['d'])?$value['0']['d'] - $value['0']['e']:'-';
                        $gstopening += $total_opening;
                        $gsttoday += $total;
                        $gstpaid += $paidsum1;
                        $total_bal = abs($total_opening - $paidsum1 + $total);
                        $gsttotal += $total_bal;
                        if($total_bal > 0 || $paidsum1 > 0){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $name);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $total_opening);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $total);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $paidsum1);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $total_bal);
		        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);   
                        $rowcount++;
                        }
                } }
//                foreach ($arr_empleaverequests_ben as $value) {
//                $gst_opening = 0;
//                   // $j = 0;
//                    if($value['emp_expense']['gst_bill_status'] == 'Pending'){
//                        $j++;
//                        $name1 = $value['beneficiary']['company_name'];
//                        $total1 = $value['0']['totalsum'];
//                        $paidsum = $value['0']['paidsum'];
//                        $pkey1 = $value['emp_expense']['emp_expenses_pkey'];
//                        $m = $m + $total1;
//                        $totalpaid = $totalpaid + $paidsum;
//                        $ben = $value['emp_expense']['beneficiary_fkey'];
//                foreach($arr_ben as $values){
//                    if($ben == $values['emp_expense']['beneficiary_fkey']){
//                        $gst_opening = $value['0']['totalsum'];
//                        $tl_opening = abs($tl_opening + $gst_opening+ $paidsum);
//                    }
//                }
//                        $gst_balance = abs($gst_opening - $paidsum +$total1);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $name1);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $gst_opening);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $total1);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $paidsum);
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $gst_balance);
//		        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);                        
////$worksheet->mergeCells('D'.$rowcount.':E'.$rowcount);
//                        $rowcount++;
//                    }
//                }
                $gst_totbalance = abs($tl_opening - $m);
                $worksheet->mergeCells('B'.$rowcount.':C'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Total");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowcount, $gstopening);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, $gsttoday);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                //$worksheet->mergeCells('D'.$rowcount.':E'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, $gstpaid);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $gsttotal);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$gstcount.':D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$gstcount.':E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$gstcount.':F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$gstcount.':G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                
                $worksheet->getStyle("B6:G".$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                $worksheet->getStyle('B'.$rowcount.':G'.$rowcount)->applyFromArray(
                   array('borders' => array(
                  'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
                $row = $rowcount++;
                 if($rowcount < $row_count){
                $rowcount = $row_count;
                $rowcount++;
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "GST");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, "Opening");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, "Today");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, "Balance");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                $rowcount++;
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "Input");
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $gstinput_opening);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $gstinput);
                $gst_input_bal = round($gstinput + $gstinput_opening,0);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $gst_input_bal);
                $rowcount++;
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "Output");
                
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $output);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $output_today);
                $output_total = round($output + $output_today,0);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $output_total);
                $rowcount++;
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $worksheet->getStyle('H'.$rowcount.':O'.$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "GST Payable");
                $gst_in_total = round($gstinput_opening - $output,0);
                $gst_out_total = round(abs($gstinput - $output_today),0);
                $gst_bal_total = round($gst_input_bal - $output_total,0);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $gst_in_total);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $gst_out_total);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $gst_bal_total);
//                $worksheet->getStyle('H'.$rowcount.':O'.$rowcount)->applyFromArray(
//                   array(    'borders' => array(
//                  'bottom' => array(
//                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
//                   ) )));
              
                }else{
                $row_count = $rowcount++;
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "GST");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, "Opening");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, "Today");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, "Balance");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                $rowcount++;
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "Input");
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $gstinput_opening);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $gstinput);
                $gst_input_bal = round($gstinput + $gstinput_opening,0);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $gst_input_bal);
                $rowcount++;
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "Output");
                
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $output);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $output_today);
                $output_total = round($output + $output_today,0);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $output_total);
                $rowcount++;
                $worksheet->mergeCells('H'.$rowcount.':L'.$rowcount);
                $worksheet->getStyle('H'.$rowcount.':O'.$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, "GST Payable");
                $gst_in_total = round($gstinput_opening - $output,0);
                $gst_out_total = round(abs($gstinput - $output_today),0);
                $gst_bal_total = round($gst_input_bal - $output_total,0);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(12, $rowcount, $gst_in_total);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, $rowcount, $gst_out_total);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, $rowcount, $gst_bal_total);
                
                }
                
                  $worksheet->getStyle("H".$row_count.":O".$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                  $worksheet->getStyle('H'.$rowcount.':O'.$rowcount)->applyFromArray(
                   array(    'borders' => array(
                  'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
                $rowcount = $row+1;
                $worksheet->mergeCells('B'.$rowcount.':G'.$rowcount);
                $worksheet->mergeCells('H'.$rowcount.':O'.$rowcount);
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':D'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Project Income");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, "Opening");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, "Today");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, "Total");
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $worksheet->getStyle('F'.$rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':D'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Bill Submitted ");
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, $bill_opening_amount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, $bill_today_amount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $bill_total);
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':D'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Payment Received");
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, $received_opening_amount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, $received_today_amount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $received_total);
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':D'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Total");
                $totals_opening = $bill_opening_amount - $received_opening_amount;
                $totals_today = $bill_today_amount - $received_today_amount;
                $totals_diff = $bill_total - $received_total;
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, $totals_opening);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $rowcount, $totals_today);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $totals_diff);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $l = $rowcount - 2;
                $objPHPExcel->getActiveSheet()->getStyle('E'.$l.':E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$l.':F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$l.':G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $worksheet->getStyle('B'.$rowcount.':G'.$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
//                $worksheet->getStyle('B5:O5')->applyFromArray(
//                   array(    'borders' => array(
//                  'bottom' => array(
//                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
//                   ) )));
                
//                $worksheet->getStyle('H'.$rowcount.':O'.$rowcount)->applyFromArray(
//                   array(    'borders' => array(
//                  'bottom' => array(
//                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
//                   ) )));
                
                $rowcount1 = $rowcount -1;
                
                $worksheet->getStyle('B'.$row_count.':G'.$rowcount1)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                $worksheet->getStyle('G6:G'.$rowcount)->applyFromArray(
                   array(    'borders' => array(
                  'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
                $worksheet->getStyle('B1:B'.$rowcount)->applyFromArray(
                   array(    'borders' => array(
                  'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
                $worksheet->getStyle('O1:O'.$rowcount)->applyFromArray(
                   array(    'borders' => array(
                  'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
                $worksheet->getStyle('B'.$rowcount.':G'.$rowcount)->applyFromArray(
                   array(    'borders' => array(
                  'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
                   ) )));
               // $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(3);
//           $objPHPExcel->getActiveSheet()
//    ->getColumnDimension('G')
//    ->setAutoSize(true);
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':O'.$rowcount);
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':D'.$rowcount);
                $worksheet->mergeCells('E'.$rowcount.':G'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, "Prepared By");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $rowcount, "Checked By");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                $worksheet->getStyle('B'.$rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getStyle('E'.$rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('H'.$rowcount.':K'.$rowcount);
                $worksheet->mergeCells('L'.$rowcount.':O'.$rowcount);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $rowcount, "Approved By");
                 $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                $worksheet->getStyle('L'.$rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount++;
                $worksheet->mergeCells('B'.$rowcount.':O'.$rowcount);
//                $objPHPExcel->getActiveSheet()->getStyle('D8:D'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//               	$objPHPExcel->getActiveSheet()->getStyle('E8:E'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $objPHPExcel->getActiveSheet()->getStyle('F8:F'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $objPHPExcel->getActiveSheet()->getStyle('G8:G'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $objPHPExcel->getActiveSheet()->getStyle('M8:M'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $objPHPExcel->getActiveSheet()->getStyle('N8:N'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                $objPHPExcel->getActiveSheet()->getStyle('O8:O'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//                
                $objPHPExcel->getActiveSheet()->setTitle('Project Expense Report');
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */

                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            default :
                $this->set('mode', '');
                $this->render('stockalldetails');
                break;
        }
    }


public function projectcriteria($site = ''){
    $this->Site->useDbConfig = $this->Session->read('ds');
    $arr_dates = $this->Site->query("select po_expirydate,expected_starting_date,actual_completion_date from  site where site_pkey = '$site' ");
    $this->set('arr_dates', $arr_dates);
}
public function bencriteria($ben = '',$crit = ''){
    $this->set('ben', $ben);
    $this->set('criteria', $crit);
    $this->Site->useDbConfig = $this->Session->read('ds');
    if($crit == 'Beneficiary'){
    $arr_dates = $this->Site->query("select site_name,site_pkey,expected_starting_date,actual_completion_date from site left join emp_expense on (emp_expense.vendor = site.site_pkey) where beneficiary_fkey = $ben and site.status = '1' ");
    }else{
    $arr_dates = $this->Site->query("select site_name,site_pkey,expected_starting_date,actual_completion_date from site left join emp_expense on (emp_expense.vendor = site.site_pkey) "
            . " left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) where emp_expense_details.expense_type_fkey = $ben and site.status = '1' and emp_expense.status = '1' and emp_expense_details.status = '1'");
    }
    $this->set('arr_dates', $arr_dates);
}
public function itemcriterialist($ben = '',$crit = '') {
        $this->autoRender = false;
        $arr_criteriaItems = array();
        $this->Site->useDbConfig = $this->Session->read('ds');
         if($crit == 'Beneficiary'){
        $arr_emp = $this->Site->query("select distinct site.site_name,site.site_pkey,site.status from site  left join emp_expense on (emp_expense.vendor = site.site_pkey) where beneficiary_fkey = '$ben' and site.status = '1' order by site_name ASC ");
         }else{
              $arr_emp = $this->Site->query("select distinct site_name,site_pkey,expected_starting_date,actual_completion_date from site left join emp_expense on (emp_expense.vendor = site.site_pkey) "
            . " left join emp_expense_details on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) where emp_expense_details.expense_type_fkey = $ben and site.status = '1' and emp_expense.status = '1' and emp_expense_details.status = '1' order by site_name ASC ");
        }
        foreach ($arr_emp as $key => $value) {
            $arr_criteriaItems[$key]['text'] = $value['site']['site_name'];
            $arr_criteriaItems[$key]['key'] = $value['site']['site_pkey'];
        }
        echo json_encode($arr_criteriaItems);
    }
	private function GenerateBeneficiaryExpensereport($mode) {
        $arr_form_data = $_REQUEST;
        $project = isset($arr_form_data['Project'])?$arr_form_data['Project']:'';
        $ben = $arr_form_data['Beneficiary']['0'];
        $this->Site->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_from = $arr_form_data['reportfrom'];
        $report_to = $arr_form_data['reportto'];
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $arr_projects = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            
            if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected.</h1>";
                die();
            }
            if(empty($project)){
                echo "<h1>No Criteria Selected .</h1>";
                die();
            }
//            foreach ($project as $pro) {
//                $arr_projects = isset($pro) ? $pro : '';
//            }
            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                                "fields" => "reportcriteria,reportcriteria_field",
                                "conditions" => array(
                                    "reporttype" => "BeneficiaryExpense",
                                    "status" => 1,
                                    'reportcriteria' => $str_criteria_item
                                )
            )));
            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
            }
         }
         //$condition = ' where EmployeeDetails.status= 1';
         $arr_totalrequests = array();
         $str_conditions = implode(' AND ', $conditions);
         foreach ($project as $projects) {
         $arr_requests = $this->Site->query("SELECT beneficiary.company_name,site.site_name,expense_type.expense_type_name,emp_expense.*,emp_expense_details.*,emp_expense.expense_id as d,emp_expense_payment.pay_amount,emp_expense_payment.expense_date as b,
                 (select sum(emp_expense_payment.pay_amount) as paidsum 
                 from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) 
                 left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) 
                 where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$projects' and emp_expense_payment.expense_date <= b and emp_expense.expense_status = 'Approved' 
                 and emp_expense.beneficiary_fkey = '$ben' and emp_expense.expense_id = d) as c,expense_type.expense_type_pkey 
                 from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) 
                 left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) 
                 left join beneficiary on (beneficiary.contact_id = emp_expense.beneficiary_fkey) 
                 left join site on (site.site_pkey = emp_expense.vendor)
                 left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) 
                 where emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$projects' and 
                 emp_expense_payment.expense_date >= '$report_from' and emp_expense_payment.expense_date <= '$report_to' and emp_expense.expense_status = 'Approved' and 
                 emp_expense.beneficiary_fkey = '$ben' ");
       
         if (!empty($arr_requests))
             $arr_totalrequests[] = $arr_requests;
         }
       //debug($arr_totalrequests);
        $arr_summary_for_template = array();
            foreach ($arr_totalrequests as $requests) {
                foreach ($requests as $leaverequest) {
                  
                    $request['beneficiary'] = isset($leaverequest['beneficiary']['company_name']) ? $leaverequest['beneficiary']['company_name'] : '';
                    $request['expense_type_name'] = isset($leaverequest['expense_type']['expense_type_name']) ? $leaverequest['expense_type']['expense_type_name'] : '';
                    $request['site'] = isset($leaverequest['site']['site_name']) ? $leaverequest['site']['site_name'] : '';
                    $request['emp_expenses_pkey'] = isset($leaverequest['emp_expense']['emp_expenses_pkey']) ? $leaverequest['emp_expense']['emp_expenses_pkey'] : '';
                    $request['emp_fkey'] = isset($leaverequest['emp_expense']['emp_fkey']) ? $leaverequest['emp_expense']['emp_fkey'] : '';
                    $request['expense_id'] = isset($leaverequest['emp_expense']['expense_id']) ? $leaverequest['emp_expense']['expense_id'] : '';
                    $request['expenses_amount'] = isset($leaverequest['emp_expense']['expenses_amount']) ? $leaverequest['emp_expense']['expenses_amount'] : '';
                    //$request['payment'] = isset($leaverequest['emp_expense']['payment']) ? $leaverequest['emp_expense']['payment'] : '';
                    $request['gst_bill_no'] = isset($leaverequest['emp_expense']['gst_bill_no']) ? $leaverequest['emp_expense']['gst_bill_no'] : '';
                    $request['gst_bill_status'] = isset($leaverequest['emp_expense']['gst_bill_status']) ? $leaverequest['emp_expense']['gst_bill_status'] : '';
                    //$request['balance'] = isset($leaverequest['emp_expense']['balance']) ? $leaverequest['emp_expense']['balance'] : '';
                    //$request['payment_status'] = isset($leaverequest['emp_expense']['payment_status']) ? $leaverequest['emp_expense']['payment_status'] : '';
                    $request['expense_date'] = isset($leaverequest['emp_expense']['expense_date']) ? $leaverequest['emp_expense']['expense_date'] : '';
                    $request['remarks'] = isset($leaverequest['emp_expense']['remarks']) ? $leaverequest['emp_expense']['remarks'] : '';
                    $request['exp_amount'] = isset($leaverequest['emp_expense_details']['exp_amount']) ? $leaverequest['emp_expense_details']['exp_amount'] : '';
                    $request['cgst'] = isset($leaverequest['emp_expense_details']['cgst']) ? $leaverequest['emp_expense_details']['cgst'] : '0';
                    $request['sgst'] = isset($leaverequest['emp_expense_details']['sgst']) ? $leaverequest['emp_expense_details']['sgst'] : '0';
                    $request['igst'] = isset($leaverequest['emp_expense_details']['igst']) ? $leaverequest['emp_expense_details']['igst'] : '0';
                    $request['total'] = isset($leaverequest['emp_expense_details']['total']) ? $leaverequest['emp_expense_details']['total'] : '';
                    $request['payment'] = isset($leaverequest['emp_expense_details']['payment']) ? $leaverequest['emp_expense_details']['payment'] : '0';
                    $request['balance'] = isset($leaverequest['emp_expense_details']['balance']) ? $leaverequest['emp_expense_details']['balance'] : '0';
                    $request['payment_status'] = isset($leaverequest['emp_expense_details']['payment_status']) ? $leaverequest['emp_expense_details']['payment_status'] : '';
                    $request['exppayment'] = isset($leaverequest['emp_expense_payment']['b']) ? $leaverequest['emp_expense_payment']['b'] : '';
                    $request['payamount'] = isset($leaverequest['emp_expense_payment']['pay_amount']) ? $leaverequest['emp_expense_payment']['pay_amount'] : '';
                    $request['c'] = isset($leaverequest['0']['c']) ? $leaverequest['0']['c'] : '';
                   
                    $arr_summary_for_template[] = $request;
                 // debug($request);
                }
            }
        //debug($request['status']);
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
       // $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_summary_for_template', $arr_summary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        //$this->set('arr_empleaverequests', $arr_empleaverequests);
        //$this->set('report_month', $report_month);
        switch ($mode) {
           
            case 'excel' :
                $month = isset($report_from) ? $report_from.' - '.$report_to : '';
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . " Project Expense Report ".$month.".xlsx" : "Project Expense Report" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Project Expense Beneficiary Report");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
             
                $worksheet->setCellValueByColumnAndRow(0, 1, "Project Expense Report - ".$month);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(17);
                
                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                
                $worksheet->mergeCells('A1:Q1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 2;
            if(!empty($arr_summary_for_template)){
                
                        $beneficiary = $arr_summary_for_template['0']['beneficiary'];
                        
                        $worksheet->mergeCells('A' . $rowcount . ':Q' . $rowcount);
                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(15);
                            $worksheet->getStyle('A'. $rowcount)->getAlignment()->applyFromArray(
                              array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Beneficiary Name : '.$beneficiary);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Project Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Expense date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Expense Type Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'GST Bill No.');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'GST Bill Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Expense ID');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Expense Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'CGST');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'SGST');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'IGST');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Total');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Payment Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Paid Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Total Paid');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Balance');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Payment Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);
                        
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Total Deductions');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, 'Settlement Amount');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, 'Net Salary');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, 'Approved');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(19, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $i =1;
                        $t_amount = 0;
                        $t_cgst = 0;
                        $t_sgst = 0;
                        $t_igst = 0;
                        $t_total = 0;
                        $t_payment = 0;
                        $t_balance = 0;
                        $t_payamount = 0;
                        $t_c = 0;
                    foreach ($arr_summary_for_template as $request) {
                        $expense_type_name = $request['expense_type_name'];
                        $site = $request['site'];
                        $emp_expenses_pkey = $request['emp_expenses_pkey'];
                        $emp_fkey = $request['emp_fkey'];
                        $expense_id = $request['expense_id'];
                        $expenses_amount = $request['expenses_amount'];
                        $payment = $request['payment'];
                        $gst_bill_no = $request['gst_bill_no'];
                        $gst_bill_status = $request['gst_bill_status'];
                        
                        $payment_status = $request['payment_status'];
                        $expense_date = $request['expense_date'];
                        $remarks = $request['remarks'];
                        $exp_amount = $request['exp_amount'];
                        $exppayment = $request['exppayment'];
                        $payamount = $request['payamount'];
                        $cgst = $request['cgst'];
                        $sgst = $request['sgst'];
                        $igst = $request['igst'];
                        $total = $request['total'];
                        $c = $request['c'];
                        $balance = $total - $c;
                        $t_amount = $t_amount + $exp_amount;
                        $t_cgst = $t_cgst + $cgst;
                        $t_sgst = $t_sgst + $sgst;
                        $t_igst = $t_igst + $igst;
                        $t_total = $t_total + $total;
                        $t_payment = $t_payment + $payment;
                        $t_balance = $t_balance + $balance;
                        $t_payamount = $t_payamount + $payamount; 
                        $t_c = $t_c + $c;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $site);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $expense_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $expense_type_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $gst_bill_no);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $gst_bill_status);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $exp_amount);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $cgst);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $sgst);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $igst);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $total);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $exppayment);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $payamount);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $c);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $balance);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $payment_status);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(15))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $remarks);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(16))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                 $rowcount++;
                                $i++;
                            }
                              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Grand Total');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':L' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    );

//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $t_amount);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $t_cgst);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $t_sgst);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $t_igst);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, round($t_total));
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $t_payment);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $t_payamount);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $t_c);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $t_balance);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                    $worksheet->getStyle('A3:Q'.$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                    $row = $rowcount-1;
                    $objPHPExcel->getActiveSheet()->getStyle('A4:I'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getStyle('J4:P'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                } else {
                            $worksheet->mergeCells('A' . $rowcount . ':N' . $rowcount);
                            $worksheet->getStyle('A'. $rowcount)->getAlignment()->applyFromArray(
                              array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No Records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    
                $objPHPExcel->getActiveSheet()->setTitle('Project Expense Report');
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */

                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            default :
                $this->set('mode', '');
                $this->render('payrolsummaryreports');
                break;
        }
    }
    private function GenerateExpenseTypeReport($mode) {
        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $project = isset($arr_form_data['Project'])?$arr_form_data['Project']:'';
        $ben = $arr_form_data['ExpenseType']['0'];
        $this->Site->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_from = $arr_form_data['reportfrom'];
        $report_to = $arr_form_data['reportto'];
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $arr_projects = array();
       // $needBranchWiseReport = false;
       // $conditions = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            
//            if ($str_criteria_item == 'Units') {
//                $needBranchWiseReport = true;
//            }
            if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected.</h1>";
                die();
            }
            if(empty($project)){
                echo "<h1>No Criteria Selected .</h1>";
                die();
            }
//            foreach ($project as $pro) {
//                $arr_projects = isset($pro) ? $pro : '';
//            }
            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                                "fields" => "reportcriteria,reportcriteria_field",
                                "conditions" => array(
                                    "reporttype" => "ExpenseTypeReport",
                                    "status" => 1,
                                    'reportcriteria' => $str_criteria_item
                                )
            )));
            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
            }
         }
         //$condition = ' where EmployeeDetails.status= 1';
         $arr_totalrequests = array();
         $str_conditions = implode(' AND ', $conditions);
         foreach ($project as $projects) {
         $arr_requests = $this->Site->query("SELECT beneficiary.company_name,site.site_name,expense_type.expense_type_name,emp_expense.*,emp_expense_details.*,emp_expense.expense_id as d,emp_expense_payment.pay_amount,emp_expense_payment.expense_date as b,
                 (select sum(emp_expense_payment.pay_amount) as paidsum 
                 from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) 
                 left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) 
                 where  emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$projects' and emp_expense_payment.expense_date <= b and emp_expense.expense_status = 'Approved' 
                 and emp_expense_details.expense_type_fkey = '$ben' and emp_expense.expense_id = d) as c,expense_type.expense_type_pkey 
                 from emp_expense_details left join emp_expense on (emp_expense_details.emp_expense_fkey = emp_expense.emp_expenses_pkey) 
                 left join emp_expense_payment on (emp_expense_details.expense_details_pkey = emp_expense_payment.expense_details_fkey) 
                 left join beneficiary on (beneficiary.contact_id = emp_expense.beneficiary_fkey) 
                 left join site on (site.site_pkey = emp_expense.vendor)
                 left join expense_type on (emp_expense_details.expense_type_fkey = expense_type.expense_type_pkey) 
                 where emp_expense.status = 1 and emp_expense_details.status = 1 and emp_expense.vendor = '$projects' and 
                 emp_expense_payment.expense_date >= '$report_from' and emp_expense_payment.expense_date <= '$report_to' and emp_expense.expense_status = 'Approved' and 
                 emp_expense_details.expense_type_fkey = '$ben' ");
       
         if (!empty($arr_requests))
             $arr_totalrequests[] = $arr_requests;
         }
       //debug($arr_totalrequests);
        $arr_summary_for_template = array();
            foreach ($arr_totalrequests as $requests) {
                foreach ($requests as $leaverequest) {
                  
                    $request['beneficiary'] = isset($leaverequest['beneficiary']['company_name']) ? $leaverequest['beneficiary']['company_name'] : '';
                    $request['expense_type_name'] = isset($leaverequest['expense_type']['expense_type_name']) ? $leaverequest['expense_type']['expense_type_name'] : '';
                    $request['site'] = isset($leaverequest['site']['site_name']) ? $leaverequest['site']['site_name'] : '';
                    $request['emp_expenses_pkey'] = isset($leaverequest['emp_expense']['emp_expenses_pkey']) ? $leaverequest['emp_expense']['emp_expenses_pkey'] : '';
                    $request['emp_fkey'] = isset($leaverequest['emp_expense']['emp_fkey']) ? $leaverequest['emp_expense']['emp_fkey'] : '';
                    $request['expense_id'] = isset($leaverequest['emp_expense']['expense_id']) ? $leaverequest['emp_expense']['expense_id'] : '';
                    $request['expenses_amount'] = isset($leaverequest['emp_expense']['expenses_amount']) ? $leaverequest['emp_expense']['expenses_amount'] : '';
                    //$request['payment'] = isset($leaverequest['emp_expense']['payment']) ? $leaverequest['emp_expense']['payment'] : '';
                    $request['gst_bill_no'] = isset($leaverequest['emp_expense']['gst_bill_no']) ? $leaverequest['emp_expense']['gst_bill_no'] : '';
                    $request['gst_bill_status'] = isset($leaverequest['emp_expense']['gst_bill_status']) ? $leaverequest['emp_expense']['gst_bill_status'] : '';
                    //$request['balance'] = isset($leaverequest['emp_expense']['balance']) ? $leaverequest['emp_expense']['balance'] : '';
                    //$request['payment_status'] = isset($leaverequest['emp_expense']['payment_status']) ? $leaverequest['emp_expense']['payment_status'] : '';
                    $request['expense_date'] = isset($leaverequest['emp_expense']['expense_date']) ? $leaverequest['emp_expense']['expense_date'] : '';
                    $request['remarks'] = isset($leaverequest['emp_expense']['remarks']) ? $leaverequest['emp_expense']['remarks'] : '';
                    $request['exp_amount'] = isset($leaverequest['emp_expense_details']['exp_amount']) ? $leaverequest['emp_expense_details']['exp_amount'] : '';
                    $request['cgst'] = isset($leaverequest['emp_expense_details']['cgst']) ? $leaverequest['emp_expense_details']['cgst'] : '0';
                    $request['sgst'] = isset($leaverequest['emp_expense_details']['sgst']) ? $leaverequest['emp_expense_details']['sgst'] : '0';
                    $request['igst'] = isset($leaverequest['emp_expense_details']['igst']) ? $leaverequest['emp_expense_details']['igst'] : '0';
                    $request['total'] = isset($leaverequest['emp_expense_details']['total']) ? $leaverequest['emp_expense_details']['total'] : '';
                    $request['payment'] = isset($leaverequest['emp_expense_details']['payment']) ? $leaverequest['emp_expense_details']['payment'] : '0';
                    $request['balance'] = isset($leaverequest['emp_expense_details']['balance']) ? $leaverequest['emp_expense_details']['balance'] : '0';
                    $request['payment_status'] = isset($leaverequest['emp_expense_details']['payment_status']) ? $leaverequest['emp_expense_details']['payment_status'] : '';
                    $request['exppayment'] = isset($leaverequest['emp_expense_payment']['b']) ? $leaverequest['emp_expense_payment']['b'] : '';
                    $request['payamount'] = isset($leaverequest['emp_expense_payment']['pay_amount']) ? $leaverequest['emp_expense_payment']['pay_amount'] : '';
                    $request['c'] = isset($leaverequest['0']['c']) ? $leaverequest['0']['c'] : '';
                   
                    $arr_summary_for_template[] = $request;
                 // debug($request);
                }
            }
        //debug($request['status']);
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
       // $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_summary_for_template', $arr_summary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        //$this->set('arr_empleaverequests', $arr_empleaverequests);
        //$this->set('report_month', $report_month);
        switch ($mode) {
           
            case 'excel' :
                $month = isset($report_from) ? $report_from.' - '.$report_to : '';
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . " Project Expense Report ".$month.".xlsx" : "Project Expense Report" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Project Expense Beneficiary Report");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
             
                $worksheet->setCellValueByColumnAndRow(0, 1, "Project Expense Report - ".$month);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(17);
                
                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                
                $worksheet->mergeCells('A1:Q1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 2;
            if(!empty($arr_summary_for_template)){
                
                        $beneficiary = $arr_summary_for_template['0']['beneficiary'];
                        $type = $arr_summary_for_template['0']['expense_type_name'];
                        $worksheet->mergeCells('A' . $rowcount . ':Q' . $rowcount);
                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(15);
                            $worksheet->getStyle('A'. $rowcount)->getAlignment()->applyFromArray(
                              array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
if($str_criteria_item == 'Beneficiary'){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Beneficiary Name : '.$beneficiary);
}else{
    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Expense Name : '.$type);
}
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Project Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Expense date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        if($str_criteria_item == 'Beneficiary'){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Expense Type Name');
                        }else{
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Beneficiary Name');
                        }
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'GST Bill No.');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'GST Bill Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Expense ID');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Expense Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'CGST');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'SGST');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'IGST');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Total');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Payment Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Paid Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Total Paid');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Balance');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Payment Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);
                        
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Total Deductions');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, 'Settlement Amount');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, 'Net Salary');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, 'Approved');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(19, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $i =1;
                        $t_amount = 0;
                        $t_cgst = 0;
                        $t_sgst = 0;
                        $t_igst = 0;
                        $t_total = 0;
                        $t_payment = 0;
                        $t_balance = 0;
                        $t_payamount = 0;
                        $t_c = 0;
                    foreach ($arr_summary_for_template as $request) {
                        $expense_type_name = $request['expense_type_name'];
                        $bene = $request['beneficiary'];
                        $site = $request['site'];
                        $emp_expenses_pkey = $request['emp_expenses_pkey'];
                        $emp_fkey = $request['emp_fkey'];
                        $expense_id = $request['expense_id'];
                        $expenses_amount = $request['expenses_amount'];
                        $payment = $request['payment'];
                        $gst_bill_no = $request['gst_bill_no'];
                        $gst_bill_status = $request['gst_bill_status'];
                        
                        $payment_status = $request['payment_status'];
                        $expense_date = $request['expense_date'];
                        $remarks = $request['remarks'];
                        $exp_amount = $request['exp_amount'];
                        $exppayment = $request['exppayment'];
                        $payamount = $request['payamount'];
                        $cgst = $request['cgst'];
                        $sgst = $request['sgst'];
                        $igst = $request['igst'];
                        $total = $request['total'];
                        $c = $request['c'];
                        $balance = $total - $c;
                        $t_amount = $t_amount + $exp_amount;
                        $t_cgst = $t_cgst + $cgst;
                        $t_sgst = $t_sgst + $sgst;
                        $t_igst = $t_igst + $igst;
                        $t_total = $t_total + $total;
                        $t_payment = $t_payment + $payment;
                        $t_balance = $t_balance + $balance;
                        $t_payamount = $t_payamount + $payamount; 
                        $t_c = $t_c + $c;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $site);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $expense_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                if($str_criteria_item == 'Beneficiary'){
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $expense_type_name);
                                }else{
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $bene);
                                }
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $gst_bill_no);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $gst_bill_status);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $exp_amount);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $cgst);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $sgst);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $igst);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $total);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $exppayment);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $payamount);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $c);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $balance);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $payment_status);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(15))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $remarks);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(16))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                 $rowcount++;
                                $i++;
                            }
                              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Grand Total');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':L' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    );

//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $t_amount);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $t_cgst);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $t_sgst);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $t_igst);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, round($t_total));
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $t_payment);
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $t_payamount);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $t_c);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $t_balance);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                    $worksheet->getStyle('A3:Q'.$rowcount)->applyFromArray(
                 array(
                  'borders' => array(
                  'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => '000000')
                  )
                 )
                )
                );
                    $row = $rowcount-1;
                    $objPHPExcel->getActiveSheet()->getStyle('A4:I'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getStyle('J4:P'.$rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                } else {
                            $worksheet->mergeCells('A' . $rowcount . ':N' . $rowcount);
                            $worksheet->getStyle('A'. $rowcount)->getAlignment()->applyFromArray(
                              array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No Records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    
                $objPHPExcel->getActiveSheet()->setTitle('Project Expense Report');
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */

                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            default :
                $this->set('mode', '');
                $this->render('payrolsummaryreports');
                break;
        }
    }
																																																																																			 
}
					 
							  
																																						
								   
																																																				
																																																				  
																																																							  
																																																								 
								   

								  
																																																								  
																																				  
																																				
																																				 
								  

																		 
																	   

																					  
																				
																				  

															   
															 
					  
					 
									   
										   
					  
            
				

 
