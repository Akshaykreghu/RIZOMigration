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
ini_set('max_execution_time', 2000);
ini_set('memory_limit', '1024M');
/** 
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class StatutoryRegistersController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'StatutoryRegisters';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeavePolicyGroup', 'CentralControl', 'AttendanceRegister', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'EmployeeGrossDetails', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'EmpCtcTransaction', 'LeaveRequests', 'Designation', 'DbConfig', 'ReportAudit');
    public $components = array('MasterdataManagement');



    /*
     * HR Reports landing view
     */

    public function hrreports()
    {
        //edited by sinsiya on 26-11-2024
        $company_code = $this->Session->read('company_code');
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2 && ($company_code == 'HRBL')) {
            $arr_reporttypes = array(
                'Musterroll' => 'Muster Roll'
                // 'wage'           =>'Wage Sheet',
            );
        } else {
            $arr_reporttypes = array(
                'Musterroll' => 'Muster Roll',
                'wage'           => 'Wage Sheet',
                'ServiceRecord' => 'Service Record', // Edited by Akshay on 5-2-2026
            );
        }
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '')
    {
        $this->autoRender = FALSE;
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code')); // Edited by Akshay on 26-11-2024
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'wage':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Musterroll':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                // Edited by Akshay on 5-2-2026
                case 'ServiceRecord':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                // End
                default:
                    echo "No criterias found";
                    break;
            }
            $this->set('company_code', $company_code); // Edited by Akshay on 26-11-2024
            $this->render('showreport');
        } else {
            echo "No criterias found";
        }
    }

    /*
     * Add criterias
     */

    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '')
    {
        $this->autoRender = FALSE;
        if ($type != '' && $str_currentcriterias != '') {
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

    public function loadcriteriaitems($index, $str_criteria = '')
    {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $this->set('criteria', $model);
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function listcriteriaitems($str_criteria = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds'); // Edited by Akshay on 29-1-2025
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');

            $arr_order = array();
            if ($model == 'Units') {
                $arr_order = array("Units.branch_name" => "ASC");
                $conditions = array("Units.status" => 1);
                // Edited by Akshay on 29-1-2025
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions["Units.branch_code"] = $is_ho;
                    }
                }
                // End
            } else {
                $conditions = array("status" => 1);
            }
            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => $arr_order)));

            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'EmployeeDetails':
                    $fields = 'emp_pkey,EmployeeDetails.status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                        )
                    );
                    $conditions = array();


                    $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions = array("status in(1,2)");
                    } else {

                        $conditions = array("status" => 1);
                    }

                    // Edited by Akshay on 29-1-2025
                    $user_group = $this->Session->read('user_group');
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions["EmployeeDetails.branch_code"] = $is_ho;
                        }
                    }
                    // End

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        "order" => $arr_order
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;
                case 'Units':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;
            }
            echo json_encode($arr_criteriaItems);
        }
    }
    public function downloadHistory($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'wage':
                $dataForHistory['report_type'] = "Wage Sheet";
                break;
            case 'Musterroll':
                $dataForHistory['report_type'] = "Muster Roll";
                break;
            // Edited by Akshay on 5-2-2026
            case 'ServiceRecord':
                $dataForHistory['report_type'] = "'Service Record'";
                break;
                // End
        }

        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        $dataForHistory['include_resigned'] = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : '';
        $dataForHistory['Include_negative_salary'] = isset($arr_form_data['ngtvsal']) ? $arr_form_data['ngtvsal'] : '';
        $criteria_count = isset($arr_form_data['hidden-criterias-count']) ? $arr_form_data['hidden-criterias-count'] : 1;
        $i = 1;
        $criteria_array = array();
        $criteria_name_array = array();
        $items_array = array();
        $items_count_array = array();
        while ($i <= $criteria_count) {
            $criteria = isset($arr_form_data['hidden-criteria' . $i]) ? $arr_form_data['hidden-criteria' . $i] : '';
            $criteria_array[] = $criteria;

            switch ($criteria) {
                case 'EmployeeDetails':
                    $criteria_name_array[] = 'belonging to an Employee';
                    break;
                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'Departments':
                    $criteria_name_array[] = 'belonging to a Department';
                    break;
                case 'Grades':
                    $criteria_name_array[] = 'belonging to a Grade';
                    break;
                case 'Verticals':
                    $criteria_name_array[] = 'belonging to a Vertical';
                    break;
                case 'SalaryHeadItems':
                    $criteria_name_array[] = 'belonging to a Salary Head Item';
                    break;
                case 'LeaveRequests':
                    $criteria_name_array[] = 'belonging to a Leave Request';
                    break;
                case 'LeavePolicyGroup':
                    $criteria_name_array[] = 'belonging to a Bank';
                    break;
                case 'Leavestatus':
                    $criteria_name_array[] = 'belonging to a Leave status';
                    break;
                case 'LeavesPolicyGroup':
                    $criteria_name_array[] = 'belonging to a Leaves Policy Group';
                    break;
                case 'EmployeeGrossDetails':
                    $criteria_name_array[] = 'belonging to a Designation';
                    break;
                case 'DayTimeProcedures':
                    $criteria_name_array[] = 'belonging to a Day Time Procedure';
                    break;
                case 'attendance':
                    $criteria_name_array[] = 'belonging to an Attendance';
                    break;
                default:
                    break;
            }
            $items_array[] = implode(",", $arr_form_data[$criteria]);
            $items_count_array[] = count($arr_form_data[$criteria]);

            $i++;
        }

        $dataForHistory['criteria'] = implode(",", $criteria_array);
        $dataForHistory['criteria_name'] = implode(",", $criteria_name_array);
        $dataForHistory['items'] = implode(",", $items_array);
        $dataForHistory['items_count'] = implode(",", $items_count_array);


        if ($mode == 'pdf') {
            $dataForHistory['mode'] = 'PDF Download';
        } else if ($mode == 'excel') {
            $dataForHistory['mode'] = 'Excel Download';
        } else {
            $dataForHistory['mode'] = 'View Report';
        }

        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';

        //        debug($dataForHistory);
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;

        switch ($type) {
            case 'wage':
                //wage sheet
                $this->Generatewage($mode);
                break;
            case 'Musterroll':
                //muster roll
                $this->generatemusterrollreport($mode);
                break;
            // Edited by Akshay on 5-2-2026
            case 'ServiceRecord':
                $this->generateServiceRecordReport($mode);
                break;
            // End
            default:
                return false;
        }
        $this->downloadHistory($type, $mode);
    }

    public function listemployeefields()
    {
        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
            $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
            $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
            $arr_empinformation_fields->getFieldHeadings('Departments'),
            $arr_empinformation_fields->getFieldHeadings('Grades'),
            $arr_empinformation_fields->getFieldHeadings('Verticals'),
            $arr_empinformation_fields->getFieldHeadings('Units')
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

    private function _modelExists($modelName)
    {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }




    //wagesheet
    private function Generatewage($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_requestdata = $this->request->data;
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        //edited by megha on 13_06_19 end_date_effective condition added for deleting repeating tds and pt
        //commented on 27_07_2019 Group by salary_head_item_desc
        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
             left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                                                    where ectc.item_part='Direct' 
                                                    and end_date_effective is null  and month_year = '$from'                                            
                                                    Group by salary_head_item_desc
                                                    ORDER BY salhead.salary_head_item_order1 asc");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }


        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            //   belongs to branch section added by megha start 1...
            $needBranchWiseReport = false;
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            $this->set('needBranchWiseReport', $needBranchWiseReport);
            //   belongs to branch section added by megha ends...
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $conditions[] .=  " emp_details.status in('1','2')";
        } else {
            $conditions[] .= " emp_details.status= 1";
        }
        //Negative Salary added.
        if (isset($arr_form_data['ngtvsal']) && $arr_form_data['ngtvsal'] == '1') {
            $conditions1 = " ";
        } else {
            $conditions1 = " and ectc.payroll_master_fkey  in (select payroll_master_pkey from payroll_master where net_salary >=0) ";
        }
        $arr_salary_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            //   belongs to branch section added by megha  2...
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                $arr_gross = $this->EmpCtcTransaction->query(" select salary_heads.head_pkey,info.*,ar.presant_total,ar.weekoff_total,ar.holiday_total,ar.leave_total,"
                    . "user_credentials.user_id,emp_ctc_transaction.emp_anual_ctc,termination.last_approved_working_date,emp_details.status,emp_details.guradian,ectc.* from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                    . "left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) "
                    . "left join attendance_register as ar on(ar.emp_fkey = emp_details.emp_pkey ) "
                    . "left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey) "
                    . "left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) "
                    . "left join branches as branches on (emp_details.branch_code = branches.branch_code and branches.status = 1) "
                    . "left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) "
                    . "left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) "
                    . "left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null ) "
                    . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey) "
                    . "where  ectc.emp_fkey= '$leavepolicygroupid' "
                    . "AND ectc.item_part = 'Direct' $conditions1 "
                    //. "and salary_amount > 0 "
                    //                ." and ectc.payroll_master_fkey  in (select payroll_master_pkey from payroll_master where net_salary >=0)"
                    . "AND ectc.end_date_effective is null and ar.isdelete='N' "
                    . "AND  $id and payroll_master.action in ('Approved','Processed') and ar.month_year= '$from' order by info.EmpName asc ,salhead.salary_head_item_order1 asc ");
                $arr_gross1 = $this->EmpCtcTransaction->query(" select salary_heads.head_pkey,info.*,ar.presant_total,ar.weekoff_total,ar.holiday_total,ar.leave_total,"
                    . "user_credentials.user_id,emp_ctc_transaction.emp_anual_ctc,termination.last_approved_working_date,emp_details.status,ectc.* from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                    . "left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) "
                    . "left join site_attendance_register as ar on(ar.emp_fkey = emp_details.emp_pkey ) "
                    . "left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey) "
                    . "left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) "
                    . "left join branches as branches on (emp_details.branch_code = branches.branch_code and branches.status = 1) "
                    . "left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) "
                    . "left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) "
                    . "left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null ) "
                    . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey) "
                    . "where  ectc.emp_fkey= '$leavepolicygroupid' "
                    . "AND ectc.item_part = 'Direct' $conditions1 "
                    . "AND ectc.end_date_effective is null and ar.isdelete='N' "
                    . "AND  $id and payroll_master.action in ('Approved','Processed') and ar.month_year= '$from' order by info.EmpName asc ,salhead.salary_head_item_order1 asc ");


                $arr_gross = array_merge($arr_gross, $arr_gross1);
                $arr_ot = $this->EmpCtcTransaction->query("select ot_master.set_duration from emp_ot_master as ot_master where ot_master.emp_fkey='$leavepolicygroupid' "
                    . "And ot_master.month='$otdate' and ot_master.is_verified='Y'");
                //edited by megha on 13/11/2019 settlement amonut 1
                $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
            left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
            left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
            left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
            where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$leavepolicygroupid' and date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");
                $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
                //end
                // $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
                $arr_days = $this->EmpCtcTransaction->query("select distinct(prorate_code) from emp_salary_structure "
                    . " where emp_fkey = '$leavepolicygroupid' and `end_date_effective` IS NULL");
                $prodata = isset($arr_days[0]['emp_salary_structure']['prorate_code']) ? $arr_days[0]['emp_salary_structure']['prorate_code'] : 0;


                $this->set('arr_gross', $arr_gross);


                if (isset($arr_gross) && !empty($arr_gross)) {
                    $gross[$k]['emp_info'] = $arr_gross[0]['info'];
                    $gross[$k]['emp_details'] = $arr_gross[0]['emp_details'];
                    $gross[$k]['termination'] = $arr_gross[0]['termination'];
                    $gross[$k]['user_credentials'] = $arr_gross[0]['user_credentials'];
                    $gross[$k]['ectc'] = $arr_gross[0]['ectc'];
                    $gross[$k]['ot'] = isset($arr_ot[0]['ot_master']['set_duration']) ? $arr_ot[0]['ot_master']['set_duration'] : 0;
                    $gross[$k]['prorate_code'] = $prodata;
                    $gross[$k]['emp_ctc_transaction'] =  $arr_gross[0]['emp_ctc_transaction'];
                    $gross[$k]['ar'] = $arr_gross[0]['ar'];
                    //edited by megha on 13/11/2019 settlement amonut 2
                    $gross[$k]['settle'] = $settle;

                    $coun = count($arr_keys);
                    //   debug($arr_gross);
                    for ($j = 0; $j < $coun; $j++) {
                        if ($arr_keys[$j]['ectc']['head_operator'] == 'Addition') {
                            $gross[$k]['Addition']['keys'][] = $arr_keys[$j][0]['sal_head'];
                            $gross[$k]['Addition']['value'][] = 0;
                            $gross[$k]['Addition']['actual'][] = 0;
                        } else {
                            $gross[$k]['Deduction']['keys'][] = $arr_keys[$j][0]['sal_head'];
                            $gross[$k]['Deduction']['value'][] = 0;
                            $gross[$k]['Deduction']['actual'][] = 0;
                        }
                    }
                    //                debug($arr_gross);
                    foreach ($arr_gross as $value) {
                        if ($value['ectc']['head_operator'] == 'Addition') {
                            $data = trim($value['ectc']['salary_head_item_desc']);
                            $key = array_search($data, $gross[$k]['Addition']['keys']); // $key = 2;
                            $gross[$k]['Addition']['value'][$key] += $value['ectc']['salary_amount']; //actual
                            if ($value['salary_heads']['head_pkey'] == '1') {
                                $gross[$k]['Addition']['actual'][$key] += $value['ectc']['structure_det_value']; ///std
                            }
                        } else {
                            $data = trim($value['ectc']['salary_head_item_desc']);
                            $key = array_search($data, $gross[$k]['Deduction']['keys']); // $key = 2;
                            $gross[$k]['Deduction']['value'][$key] += $value['ectc']['salary_amount'];
                            $gross[$k]['Deduction']['actual'][$key] += $value['ectc']['structure_det_value'];
                        }
                    }
                }
                $k++;
                //   belongs to branch section added by megha 3 start...
            }
            if ($arr_form_data['select-criteria1'] == 'Units') {
                $arr_gross = array();
                $arr_gross = $this->EmpCtcTransaction->query("select salary_heads.head_pkey,info.*,ar.presant_total,ar.weekoff_total,ar.holiday_total,ar.leave_total,user_credentials.user_id,emp_ctc_transaction.emp_derived_anualctc,termination.last_approved_working_date,emp_details.status,emp_details.guradian,ectc.* from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey) "
                    . " join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) "
                    . " left join attendance_register as ar on(ar.emp_fkey = ectc.emp_fkey) "
                    . " left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) "
                    . " left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey) "
                    . " left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) "
                    . " left join branches as branches on (emp_details.branch_code = branches.branch_code and branches.status = 1)"
                    . " left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1)"
                    . " left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null )"
                    . "  join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey)"
                    . " where emp_details.branch_code = '$leavepolicygroupid' and emp_details.branch_code = emp_details.branch_code"
                    . " AND ectc.item_part = 'Direct' $conditions1 "
                    //                        . "and salary_amount != 0 "
                    //  ." and ectc.payroll_master_fkey  in (select payroll_master_pkey from payroll_master where net_salary >=0)"
                    . " AND ectc.end_date_effective is null and ar.isdelete='N'"
                    . " AND  $id and payroll_master.action in ('Approved','Processed') and ar.month_year= '$from' order by info.EmpName asc ,salhead.salary_head_item_order1 asc");
                $arr_gross1 = $this->EmpCtcTransaction->query("select salary_heads.head_pkey,info.*,ar.presant_total,ar.weekoff_total,ar.holiday_total,ar.leave_total,user_credentials.user_id,emp_ctc_transaction.emp_derived_anualctc,termination.last_approved_working_date,emp_details.status,ectc.* from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey) "
                    . " join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) "
                    . " left join site_attendance_register as ar on(ar.emp_fkey = ectc.emp_fkey ) "
                    . " left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) "
                    . " left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey) "
                    . " left join salary_heads on (salary_heads.head_pkey = salhead.head_fkey) "
                    . " left join branches as branches on (emp_details.branch_code = branches.branch_code and branches.status = 1) "
                    . " left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) "
                    . " left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null )"
                    . "  join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey)"
                    . " where emp_details.branch_code = '$leavepolicygroupid' and emp_details.branch_code = emp_details.branch_code"
                    . " AND ectc.item_part = 'Direct' $conditions1 "
                    //                        . "and salary_amount != 0 "
                    //  ." and ectc.payroll_master_fkey  in (select payroll_master_pkey from payroll_master where net_salary >=0)"
                    . " AND ectc.end_date_effective is null and ar.isdelete='N'"
                    . " AND  $id and payroll_master.action in ('Approved','Processed') and ar.month_year= '$from' order by info.EmpName asc ,salhead.salary_head_item_order1 asc");
                $arr_gross = array_merge($arr_gross, $arr_gross1);
                $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
            left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
            left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
            left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
            where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.branch_code = '$leavepolicygroupid'  and date_format(termination.last_approved_working_date,'%Y-%m') = '$from'  group by emp_details.emp_pkey");

                //debug($arr_gross);

                if (empty($arr_gross)) continue;

                $this->set('arr_gross', $arr_gross);
                $arr_gross_emp = $this->EmpCtcTransaction->query("select distinct(ectc.emp_fkey),emp_details.first_name from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                    . " left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey)"
                    . " left join attendance_register as ar on(ar.emp_fkey = ectc.emp_fkey ) "
                    . " left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) "
                    . " join branches as branches on (emp_details.branch_code = branches.branch_code and branches.status = 1)"
                    . " left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1)"
                    . " left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null )"
                    . " left join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey)"
                    . " where emp_details.branch_code = '$leavepolicygroupid' and emp_details.branch_code = emp_details.branch_code"
                    . " AND ectc.item_part = 'Direct' $conditions1 "
                    //                        . "and salary_amount != 0 "
                    //   ." and ectc.payroll_master_fkey  in (select payroll_master_pkey from payroll_master where net_salary >=0)"
                    . " AND ectc.end_date_effective is null and ar.isdelete='N'"
                    . " AND  $id and payroll_master.action in ('Approved','Processed') order by info.EmpName ");
                $arr_gross_emp1 = $this->EmpCtcTransaction->query("select distinct(ectc.emp_fkey),emp_details.first_name from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                    . " left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey)"
                    . " left join site_attendance_register as ar on(ar.emp_fkey = ectc.emp_fkey ) "
                    . " left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) "
                    . " join branches as branches on (emp_details.branch_code = branches.branch_code and branches.status = 1)"
                    . " left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1)"
                    . " left join emp_ctc_transaction on (emp_ctc_transaction.emp_fkey = ectc.emp_fkey  and emp_ctc_transaction.end_date_effective is null )"
                    . " left join user_credentials as user_credentials on (user_credentials.emp_fkey = info.emp_pkey)"
                    . " where emp_details.branch_code = '$leavepolicygroupid' and emp_details.branch_code = emp_details.branch_code"
                    . " AND ectc.item_part = 'Direct' $conditions1"
                    . " AND ectc.end_date_effective is null and ar.isdelete='N'"
                    . " AND  $id and payroll_master.action in ('Approved','Processed') order by info.EmpName ");
                $arr_gross_emp = array_merge($arr_gross_emp, $arr_gross_emp1);

                if (isset($arr_gross) && !empty($arr_gross)) {
                    if ($arr_gross) {
                        foreach ($arr_gross_emp as $emps) {
                            $emp = $emps['ectc']['emp_fkey'];
                            $coun = count($arr_keys);
                            for ($j = 0; $j < $coun; $j++) {
                                if ($arr_keys[$j]['ectc']['head_operator'] == 'Addition') {
                                    $gross[$leavepolicygroupid][$emp]['Addition']['keys'][] = $arr_keys[$j][0]['sal_head'];
                                    $gross[$leavepolicygroupid][$emp]['Addition']['value'][] = 0;
                                    $gross[$leavepolicygroupid][$emp]['Addition']['actual'][] = 0;
                                } else {
                                    $gross[$leavepolicygroupid][$emp]['Deduction']['keys'][] = $arr_keys[$j][0]['sal_head'];
                                    $gross[$leavepolicygroupid][$emp]['Deduction']['value'][] = 0;
                                    $gross[$leavepolicygroupid][$emp]['Deduction']['actual'][] = 0;
                                }
                            }
                        }
                        foreach ($arr_gross as $value) {
                            $emp_pkey = $value['info']['emp_pkey'];
                            //edited by megha on 13/11/2019 settlement amonut 4
                            $gross[$leavepolicygroupid][$emp_pkey]['settle'] = 0;
                            foreach ($arr_settle as $values) {
                                if ($values['info']['emp_pkey'] == $emp_pkey) {
                                    $gross[$leavepolicygroupid][$emp_pkey]['settle'] = $values['0']['sum(salary_amount)'];
                                }
                            }
                            //end
                            $arr_ot = $this->EmpCtcTransaction->query("select ot_master.set_duration from emp_ot_master as ot_master "
                                . " left join emp_details as emp_details on (emp_details.emp_pkey = ot_master.emp_fkey)"
                                . " where ot_master.emp_fkey = '$emp_pkey' "
                                . " And ot_master.month='$otdate' and ot_master.is_verified='Y'");
                            $arr_days = $this->EmpCtcTransaction->query("select distinct(prorate_code) from emp_salary_structure "
                                . " where emp_fkey = '$emp_pkey' and `end_date_effective` IS NULL");
                            $prodata = isset($arr_days[0]['emp_salary_structure']['prorate_code']) ? $arr_days[0]['emp_salary_structure']['prorate_code'] : 0;


                            $gross[$leavepolicygroupid][$emp_pkey]['emp_info'] = $value['info'];
                            $gross[$leavepolicygroupid][$emp_pkey]['emp_details'] = $value['emp_details'];
                            $gross[$leavepolicygroupid][$emp_pkey]['termination'] = $value['termination'];
                            $gross[$leavepolicygroupid][$emp_pkey]['user_credentials'] = $value['user_credentials'];
                            $gross[$leavepolicygroupid][$emp_pkey]['ectc'] = $value['ectc'];
                            $gross[$leavepolicygroupid][$emp_pkey]['ot'] = isset($arr_ot[0]['ot_master']['set_duration']) ? $arr_ot[0]['ot_master']['set_duration'] : 0;
                            $gross[$leavepolicygroupid][$emp_pkey]['prorate_code'] = $prodata;
                            $gross[$leavepolicygroupid][$emp_pkey]['emp_ctc_transaction'] = $value['emp_ctc_transaction'];
                            $gross[$leavepolicygroupid][$emp_pkey]['ar'] = $value['ar'];


                            if ($value['ectc']['head_operator'] == 'Addition') {
                                $data = trim($value['ectc']['salary_head_item_desc']);

                                $key = array_search($data, $gross[$leavepolicygroupid][$emp_pkey]['Addition']['keys']); // $key = 2;
                                //debug($key);
                                $gross[$leavepolicygroupid][$emp_pkey]['Addition']['value'][$key] += $value['ectc']['salary_amount'];
                                if ($value['salary_heads']['head_pkey'] == '1') {
                                    $gross[$leavepolicygroupid][$emp_pkey]['Addition']['actual'][$key] += $value['ectc']['structure_det_value'];
                                }
                            } else {
                                $data = trim($value['ectc']['salary_head_item_desc']);
                                $key = array_search($data, $gross[$leavepolicygroupid][$emp_pkey]['Deduction']['keys']); // $key = 2;
                                $gross[$leavepolicygroupid][$emp_pkey]['Deduction']['value'][$key] += $value['ectc']['salary_amount'];
                                $gross[$leavepolicygroupid][$emp_pkey]['Deduction']['actual'][$key] += $value['ectc']['structure_det_value'];
                            }
                        }
                    }
                }
                $k++;
            }
            //   belongs to branch section added by megha end...
        }

        //}
        $this->set('keys', $arr_keys);
        $this->set('gross', $gross);
        $this->set('array_key', $array_key);
        // debug($gross);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        $city = $arr_comp_contact_info['CompanyContactInfo']['city'];
        $cname = $arr_comp_contact_info['CompanyContactInfo']['business_name'];
        // $month_year=$arr_gross['ectc']['month_year'];
        //Set informations needed for report

        switch ($mode) {
            case 'pdf':
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('wage');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Wage Sheet' . $from . '.pdf', 'D');
                $this->render('wage');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_WageSheet_" . $from . ".xlsx" : "WageSheet" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->getColumnDimension('A')->setWidth(16);
                $worksheet->getColumnDimension('B')->setWidth(12);
                $worksheet->getColumnDimension('D')->setWidth(12);
                $worksheet->getColumnDimension('C')->setWidth(19);
                $worksheet->getColumnDimension('E')->setWidth(12);
                $worksheet->getColumnDimension('F')->setWidth(12);
                $worksheet->getColumnDimension('G')->setWidth(20);
                $worksheet->getColumnDimension('H')->setWidth(12);
                $worksheet->getColumnDimension('I')->setWidth(12);
                $worksheet->getColumnDimension('J')->setWidth(20);
                $worksheet->getColumnDimension('K')->setWidth(20);
                $worksheet->getColumnDimension('L')->setWidth(22);
                $worksheet->getColumnDimension('M')->setWidth(12);
                $worksheet->getColumnDimension('N')->setWidth(12);
                $worksheet->getColumnDimension('O')->setWidth(12);
                $worksheet->getColumnDimension('P')->setWidth(12);
                $worksheet->getColumnDimension('Q')->setWidth(12);
                $worksheet->getColumnDimension('R')->setWidth(12);
                $worksheet->getColumnDimension('S')->setWidth(12);
                $worksheet->getColumnDimension('T')->setWidth(12);
                $worksheet->getColumnDimension('U')->setWidth(18);
                $worksheet->getColumnDimension('V')->setWidth(12);
                $worksheet->getColumnDimension('W')->setWidth(12);
                $columncount = 0;
                //   belongs to branch section added by megha 4 start...
                if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {
                    if (count($gross) <= 0) {
                        $worksheet->setCellValueByColumnAndRow(0, 1, "Register of Wages - " . $from);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(14);
                        $worksheet->mergeCells('A1:U1');
                        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                        );
                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                        );
                        $worksheet->setCellValueByColumnAndRow(0, 2, "There is no data available under the selected criteria.");
                        $worksheet->mergeCells('A2:U2');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    } else {

                        $worksheet->setCellValueByColumnAndRow(0, 1, "FORM XI");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(13);
                        $worksheet->mergeCells('A1:U1');
                        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(0, 2, "Register of Wages");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                        $worksheet->mergeCells('A2:U2');
                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(0, 3, "See Rule 29(1)");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:U3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );


                        $worksheet->mergeCells('A4:F4');
                        $worksheet->setCellValueByColumnAndRow(7, 4, "Name of Establishment :");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 4)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 4)->getFont()->setSize(13);
                        $worksheet->mergeCells('H4:K4');
                        $worksheet->getStyle('H4')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );


                        $worksheet->setCellValueByColumnAndRow(11, 4, "$cname");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, 4)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, 4)->getFont()->setSize(13);
                        $worksheet->mergeCells('L4:U4');
                        $worksheet->getStyle('L4')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A5', 'Wage Period ')

                            ->setCellValue('H5', 'Place ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 5)->getFont()->setSize(13);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 5)->getFont()->setSize(13);


                        $worksheet->setCellValueByColumnAndRow(1, 5,  "$from");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 5)->getFont()->setSize(13);
                        $worksheet->mergeCells('B5:F5');
                        $worksheet->getStyle('B5')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(8, 5,  "$city");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 5)->getFont()->setSize(13);
                        $worksheet->mergeCells('I5:U5');
                        $worksheet->getStyle('I5')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(4, 6,  "Minimum Rates of Wages Payable");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 6)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 6)->getFont()->setSize(13);
                        $worksheet->mergeCells('E6:G6');
                        $worksheet->getStyle('E6')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->setCellValueByColumnAndRow(7, 6, "Rates of Wages Actually Paid");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 6)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 6)->getFont()->setSize(13);
                        $worksheet->mergeCells('H6:J6');
                        $worksheet->getStyle('H6')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );


                        $objPHPExcel->getActiveSheet()->getStyle('A7:W7')
                            ->getAlignment()->setWrapText(true);
                        $objPHPExcel->getActiveSheet()->getStyle('A6:W6')
                            ->getAlignment()->setWrapText(true);


                        $rowcount = 7;
                        $worksheet->setCellValueByColumnAndRow(0, 6,  "Sl No");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(1, 6,  "Name of the Employee");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(2, 6,  "Father's/Husband's Name");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(3, 6,  "Designation");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(10, 6,  "Total Attendance Units of Work Done");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(11, 6,  "Overtime Worked/Performance Incentives");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(12, 6,  "Gross Wages Payable");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(13, 6,  "Employees Contribution to PF");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(14, 6,  "Employees Contribution to ESI");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(15, 6,  "HR");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(16, 6,  "Other Deductions");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(17, 6,  "Total Deductions");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(18, 6,  "Wages Paid");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(19, 6,  "Date of Payment");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(19, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(20, 6,  " Signature/Thumb Impression of Employee");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(20, 6)->getFont()->setBold(true);



                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:A7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B6:B7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C6:C7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D6:D7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('K6:K7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L6:L7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('M6:M7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('N6:N7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('O6:O7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('P6:P7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('Q6:Q7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('R6:R7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('S6:S7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('T6:T7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('U6:U7');




                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount, 'Basic');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'DA');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Conv/Washing/Other Allowances');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 7, $rowcount, 'Basic');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 8, $rowcount, 'DA');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 8, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Conv/Washing/Other Allowances');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);

                        $rowcount = $rowcount + 1;
                        $j = 1;

                        $bname = '';
                        $uniqueStr = implode(',', array_unique(explode(',', $bname)));

                        foreach ($gross as $branch => $brnch) {
                            $branches = current($brnch);
                            $branch = $branches['emp_info']['branch'];

                            foreach ($brnch as $val) {
                                if ($val['emp_info']['branch'] == $val['emp_info']['branch']) {



                                    $bname .= $val['emp_info']['branch'] . ',';

                                    if ($val['emp_details']['status'] == "2") {
                                        $stat = " (Resigned) ";
                                    } else {
                                        $stat = "";
                                    }
                                    $name = ucwords(strtolower($val['emp_info']['EmpName'])) . $stat;

                                    $gname = isset($val['emp_details']['guradian']) ? ucwords(strtolower($val['emp_details']['guradian'])) : '';

                                    $deg = isset($val['emp_info']['designation']) ? $val['emp_info']['designation'] : '';

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount, $j);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 1) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 2) . $rowcount, $gname);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 3) . $rowcount, $deg);

                                    $count_addition = count($val['Addition']['keys']);
                                    $grss_amt = 0;
                                    $stdbasic = 0;
                                    $actualbasic = 0;
                                    $stdda = 0;
                                    $actualda = 0;
                                    $stdhra = 0;
                                    $actualhra = 0;
                                    $stdca = 0;
                                    $actualca = 0;
                                    $ot = 0;
                                    $pi = 0;
                                    $totalot = 0;
                                    $sothers = 0;
                                    $sgross = 0;
                                    $aothers = 0;
                                    $agross = 0;
                                    $number1 = 0;


                                    for ($m = 0; $m < $count_addition; $m++) {

                                        $number = round($val['Addition']['actual'][$m], 2); //acutal=std
                                        $sgross = $number + $sgross;

                                        $number1 = round($val['Addition']['value'][$m], 2); //value=actual
                                        $agross = $number1 + $agross;

                                        if ($val['Addition']['keys'][$m] == 'Basic') {

                                            $stdbasic = round($val['Addition']['actual'][$m], 2);
                                            $actualbasic = round($val['Addition']['value'][$m], 2);
                                        }

                                        if ($val['Addition']['keys'][$m] == 'Dearness Allowance (DA)') {
                                            $stdda = round($val['Addition']['actual'][$m], 2);
                                            $actualda = round($val['Addition']['value'][$m], 2);
                                        }

                                        if ($val['Addition']['keys'][$m] == 'Performance Incentives' || $val['Addition']['keys'][$m] == 'Performance Incentive') {

                                            $pi = round($val['Addition']['value'][$m], 2);
                                        }

                                        if ($val['Addition']['keys'][$m] == 'Overtime (OT)' || $val['Addition']['keys'][$m] == 'Overtime Allowance(OT)') {
                                            $ot = round($val['Addition']['value'][$m], 2);
                                        }
                                    }
                                    $totalot = $pi + $ot;
                                    $sothers = $sgross - ($stdbasic + $stdda);
                                    $aothers = round($agross - ($actualbasic + $actualda + $totalot)) + $val['settle'];
                                    $agross = $agross + $val['settle'];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 4) . $rowcount, round($stdbasic));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 5) . $rowcount, round($stdda));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 6) . $rowcount, round($sothers));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 7) . $rowcount, round($actualbasic));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 8) . $rowcount, round($actualda));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 9) . $rowcount, round($aothers));
                                    $at = 0;
                                    if ($val['prorate_code'] == '1') {
                                        $at = $val['ar']['presant_total'] + $val['ar']['leave_total'] + $val['ar']['weekoff_total'] + $val['ar']['holiday_total'];
                                    } else if ($val['prorate_code'] == '2') {
                                        $at = $val['ar']['presant_total'] + $val['ar']['leave_total'];
                                    } else {
                                        $at = $val['ar']['presant_total'] + $val['ar']['leave_total'];
                                    }
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 10) . $rowcount, $at);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 11) . $rowcount, $totalot);

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 12) . $rowcount, round($agross));

                                    $hr = 0;
                                    $dd_amt = 0;
                                    $esi = 0;
                                    $pf = 0;
                                    $d = '';
                                    $s = '';
                                    $otherdeduction = 0;
                                    $wp = 0;
                                    if (isset($array_key['Deduction'])) {
                                        $count_addition = count($val['Deduction']['keys']);

                                        for ($m = 0; $m < $count_addition; $m++) {
                                            $number = round($val['Deduction']['value'][$m], 2);
                                            $dd_amt = $number + $dd_amt;
                                            $val1 = round($number, 2);
                                            if ($val['Deduction']['keys'][$m] == 'PF' || $val['Deduction']['keys'][$m] == 'employee PF' || $val['Deduction']['keys'][$m] == 'EPF - Employee Contribution' || $val['Deduction']['keys'][$m] == 'Employee EPF') {
                                                $pf = abs((round($val['Deduction']['value'][$m], 2)));
                                            }
                                            if ($val['Deduction']['keys'][$m] == 'ESI' || $val['Deduction']['keys'][$m] == 'ESI - Employee Contribution' || $val['Deduction']['keys'][$m] == 'Employee ESI') {
                                                $esi = abs((round($val['Deduction']['value'][$m], 2)));
                                            }
                                        }
                                        $dd_amt = abs(round($dd_amt, 2));
                                        $otherdeduction = ($dd_amt) - ($pf + $esi);
                                        $wp = ($agross) - ($dd_amt);
                                    }
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 13) . $rowcount, $pf);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 14) . $rowcount, $esi);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 15) . $rowcount, $hr);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 16) . $rowcount, $otherdeduction); //otherddn
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 17) . $rowcount, round($dd_amt)); //ttldd
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 18) . $rowcount, round($wp));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 19) . $rowcount, $d);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 20) . $rowcount, $s);
                                }

                                $j++;
                                $rowcount++;
                            }
                        }

                        // Edited by Akshay on 23-3-2026
                        for ($col = 0; $col <= 3; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))
                                ->setAutoSize(true);
                        }

                        for ($col = 4; $col <= 20; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))
                                ->setWidth(20); // or 10–15 depending on need
                        }

                        // Grey for A6:D6
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:D6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        // Grey for E7:J7
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('E7:J7')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        // Grey for K6:U6
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('K6:U6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                        // End
                        // Edited by Akshay on 23-3-2026
                        $objPHPExcel->getActiveSheet()->freezePane('C8');
                        // End

                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':J' . $rowcount);
                        $uniqueStr = implode(',', array_unique(explode(',', $bname)));
                        $uniqueStr = rtrim($uniqueStr, ',');

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowcount, "Please Note - Wage Sheet for the Branch " . $uniqueStr);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    }
                } else {
                    //   belongs to branch section added by megha end...
                    if (count($gross) <= 0) {
                        $worksheet->setCellValueByColumnAndRow(0, 1, "Register of Wages - " . $from);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(14);
                        $worksheet->mergeCells('A1:U1');
                        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                        );
                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                        );
                        $worksheet->setCellValueByColumnAndRow(0, 2, "There is no data available under the selected criteria.");
                        $worksheet->mergeCells('A2:U2');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    } else {
                        $worksheet->setCellValueByColumnAndRow(0, 1, "FORM XI");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(13);
                        $worksheet->mergeCells('A1:U1');
                        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(0, 2, "Register of Wages");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                        $worksheet->mergeCells('A2:U2');
                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(0, 3, "See Rule 29(1)");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:U3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );


                        $worksheet->mergeCells('A4:F4');
                        $worksheet->setCellValueByColumnAndRow(7, 4, "Name of Establishment :");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 4)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 4)->getFont()->setSize(13);
                        $worksheet->mergeCells('H4:K4');
                        $worksheet->getStyle('H4')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );


                        $worksheet->setCellValueByColumnAndRow(11, 4, "$cname");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, 4)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, 4)->getFont()->setSize(13);
                        $worksheet->mergeCells('L4:U4');
                        $worksheet->getStyle('L4')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A5', 'Wage Period ')

                            ->setCellValue('H5', 'Place ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 5)->getFont()->setSize(13);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 5)->getFont()->setSize(13);


                        $worksheet->setCellValueByColumnAndRow(1, 5,  "$from");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 5)->getFont()->setSize(13);
                        $worksheet->mergeCells('B5:F5');
                        $worksheet->getStyle('B5')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(8, 5,  "$city");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 5)->getFont()->setSize(13);
                        $worksheet->mergeCells('I5:U5');
                        $worksheet->getStyle('I5')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $worksheet->setCellValueByColumnAndRow(4, 6,  "Minimum Rates of Wages Payable");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 6)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, 6)->getFont()->setSize(13);
                        $worksheet->mergeCells('E6:G6');
                        $worksheet->getStyle('E6')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $worksheet->setCellValueByColumnAndRow(7, 6, "Rates of Wages Actually Paid");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 6)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 6)->getFont()->setSize(13);
                        $worksheet->mergeCells('H6:J6');
                        $worksheet->getStyle('H6')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );


                        $objPHPExcel->getActiveSheet()->getStyle('A7:U7')
                            ->getAlignment()->setWrapText(true);
                        $objPHPExcel->getActiveSheet()->getStyle('A6:U6')
                            ->getAlignment()->setWrapText(true);


                        $rowcount = 7;
                        $worksheet->setCellValueByColumnAndRow(0, 6,  "Sl No");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(1, 6,  "Name of the Employee");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(2, 6,  "Father's/Husband's Name");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(3, 6,  "Designation");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(10, 6,  "Total Attendance Units of Work Done");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(11, 6,  "Overtime Worked/Performance Incentives");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(12, 6,  "Gross Wages Payable");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(13, 6,  "Employees Contribution to PF");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(14, 6,  "Employees Contribution to ESI");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(15, 6,  "HR");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(16, 6,  "Other Deductions'");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(17, 6,  "Total Deductions");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(18, 6,  "Wages Paid");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(19, 6,  "Date of Payment");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(19, 6)->getFont()->setBold(true);

                        $worksheet->setCellValueByColumnAndRow(20, 6,  " Signature/Thumb Impression of Employee");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(20, 6)->getFont()->setBold(true);



                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:A7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B6:B7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C6:C7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D6:D7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('K6:K7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L6:L7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('M6:M7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('N6:N7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('O6:O7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('P6:P7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('Q6:Q7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('R6:R7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('S6:S7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('T6:T7');
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('U6:U7');

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 4, $rowcount, 'Basic');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'DA');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Conv/Washing/Other Allowances');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 7, $rowcount, 'Basic');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 8, $rowcount, 'DA');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 8, $rowcount)->getFont()->setBold(true);

                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'HRA');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 10, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Conv/Washing/Other Allowances');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);


                        $j = 1;

                        $rowcount = $rowcount + 1;

                        foreach ($gross as $val) {
                            $columncount = 0;

                            $gname = $val['emp_details']['guradian'];
                            if ($val['emp_details']['status'] == "2") {
                                $stat = " (Resigned) ";
                            } else {
                                $stat = "";
                            }
                            $name = ucwords(strtolower($val['emp_info']['EmpName'])) . $stat; // Edited by Akshay on 23-3-2026
                            $deg = $val['emp_info']['designation'];

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount) . $rowcount, $j);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 1) . $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 2) . $rowcount, $gname);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 3) . $rowcount, $deg);

                            $count_addition = count($val['Addition']['keys']);
                            $grss_amt = 0;
                            $stdbasic = 0;
                            $actualbasic = 0;
                            $stdda = 0;
                            $actualda = 0;
                            $stdhra = 0;
                            $actualhra = 0;
                            $stdca = 0;
                            $actualca = 0;
                            $ot = 0;
                            $pi = 0;
                            $totalot = 0;
                            $sothers = 0;
                            $sgross = 0;
                            $aothers = 0;
                            $agross = 0;
                            $number1 = 0;


                            for ($m = 0; $m < $count_addition; $m++) {

                                $number = round($val['Addition']['actual'][$m], 2); //acutal=std
                                $sgross = $number + $sgross;

                                $number1 = round($val['Addition']['value'][$m], 2); //value=actual
                                $agross = $number1 + $agross;

                                if ($val['Addition']['keys'][$m] == 'Basic') {

                                    $stdbasic = round($val['Addition']['actual'][$m], 2);
                                    $actualbasic = round($val['Addition']['value'][$m], 2);
                                }

                                if ($val['Addition']['keys'][$m] == 'Dearness Allowance (DA)') {
                                    $stdda = round($val['Addition']['actual'][$m], 2);
                                    $actualda = round($val['Addition']['value'][$m], 2);
                                }

                                if ($val['Addition']['keys'][$m] == 'Performance Incentives' || $val['Addition']['keys'][$m] == 'Performance Incentive') {

                                    $pi = round($val['Addition']['value'][$m], 2);
                                }

                                if ($val['Addition']['keys'][$m] == 'Overtime (OT)' || $val['Addition']['keys'][$m] == 'Overtime Allowance(OT)') {
                                    $ot = round($val['Addition']['value'][$m], 2);
                                }
                                // $number = round($val['Addition']['actual'][$m],2);
                                //   $grss_amt = $number + $grss_amt;
                            }
                            $totalot = $pi + $ot;
                            $sothers = $sgross - ($stdbasic + $stdda);
                            $aothers = round($agross - ($actualbasic + $actualda + $totalot)) + $val['settle'];
                            $agross = $agross + $val['settle'];

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 4) . $rowcount, round($stdbasic));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 5) . $rowcount, round($stdda));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 6) . $rowcount, round($sothers));


                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 7) . $rowcount, round($actualbasic));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 8) . $rowcount, round($actualda));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 9) . $rowcount, round($aothers));
                            $at = 0;
                            if ($val['prorate_code'] == '1') {
                                $at = $val['ar']['presant_total'] + $val['ar']['leave_total'] + $val['ar']['weekoff_total'] + $val['ar']['holiday_total'];
                            } else if ($val['prorate_code'] == '2') {
                                $at = $val['ar']['presant_total'] + $val['ar']['leave_total'];
                            } else {
                                $at = $val['ar']['presant_total'] + $val['ar']['leave_total'];
                            }
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 10) . $rowcount, $at);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 11) . $rowcount, $totalot);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 12) . $rowcount, round($agross));

                            //end
                            $hr = 0;
                            $dd_amt = 0;
                            $esi = 0;
                            $pf = 0;
                            $d = '';
                            $s = '';
                            if (isset($array_key['Deduction'])) {
                                $count_addition = count($val['Deduction']['keys']);

                                for ($m = 0; $m < $count_addition; $m++) {
                                    $number = round($val['Deduction']['value'][$m], 2);
                                    $dd_amt = $number + $dd_amt;
                                    $val1 = round($number, 2);
                                    if ($val['Deduction']['keys'][$m] == 'PF' || $val['Deduction']['keys'][$m] == 'employee PF' || $val['Deduction']['keys'][$m] == 'EPF - Employee Contribution' || $val['Deduction']['keys'][$m] == 'Employee EPF') {
                                        $pf = abs((round($val['Deduction']['value'][$m], 2)));
                                    }
                                    if ($val['Deduction']['keys'][$m] == 'ESI' || $val['Deduction']['keys'][$m] == 'ESI - Employee Contribution' || $val['Deduction']['keys'][$m] == 'Employee ESI') {
                                        $esi = abs((round($val['Deduction']['value'][$m], 2)));
                                    }
                                }
                            }
                            $dd_amt = abs(round($dd_amt, 2));
                            $otherdeduction = ($dd_amt) - ($pf + $esi);
                            $wp = ($agross) - ($dd_amt);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 13) . $rowcount, $pf);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 14) . $rowcount, $esi);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 15) . $rowcount, $hr);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 16) . $rowcount, $otherdeduction); //otherddn
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 17) . $rowcount, $dd_amt); //ttldd
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 18) . $rowcount, round($wp));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 19) . $rowcount, $d);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columncount + 20) . $rowcount, $s);

                            $j++;
                            $rowcount++;
                        }

                        foreach (range('A1', 'U') as $columnID) {
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(false);
                            $BStyle = array(
                                'borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                )
                            );
                            $row = $rowcount - 1;
                            $objPHPExcel->getActiveSheet()->getStyle('A1:U' . $row)->applyFromArray($BStyle);
                        }

                        // Edited by Akshay on 23-3-2026
                        for ($col = 0; $col <= 3; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))
                                ->setAutoSize(true);
                        }

                        for ($col = 4; $col <= 20; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))
                                ->setWidth(20); // or 10–15 depending on need
                        }

                        // Grey for A6:D6
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A6:D6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        // Grey for E7:J7
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('E7:J7')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');

                        // Grey for K6:U6
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('K6:U6')
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                        // End

                        // Edited by Akshay on 23-3-2026
                        $objPHPExcel->getActiveSheet()->freezePane('C8');
                        // End
                    }
                }


                // Edited by Akshay on 23-3-2026
                $objPHPExcel->getActiveSheet()
                    ->getStyle('A5:U7')
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setShowGridlines(false);

                $BStyle = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                if (isset($rowcount)) {
                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A1:U' . ($rowcount - 1))
                        ->applyFromArray($BStyle);

                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A8:A' . ($rowcount - 1))
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $objPHPExcel->getActiveSheet()
                        ->getStyle('A1:U' . $rowcount)
                        ->getAlignment()
                        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                }
                // End

                //   belongs to branch section added by megha 5...


                $objPHPExcel->getActiveSheet()->setTitle('Wage Sheet');
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
            default:
                $this->set('mode', '');
                $this->render('wage');
                break;
        }
    }

    private function generatemusterrollreport($mode)
    {
        $arr_form_data = $_REQUEST;


        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));


        $month = $arr_form_data['reportfrom'];
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $month1 =  $month . '-01';
        $att_startdate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->AttendanceRegister->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = date("d", strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("d", strtotime($att_enddate['0']['0']['monthly_att_todate']));

        $arr_date_in_selectedmonth = range(1, $att_enddate1);

        if ($att_startdate1 != 1) {
            $arr_date_in_prevmonth = range($att_startdate1, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }
        //end

        $hiddenreporttype = $arr_form_data['hidden-report-type'];
        $this->set('reporttype', $hiddenreporttype);
        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);

        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->AttendanceRegister->query("select UCASE(ifnull(occurance,'LOP')) AS abbr from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        $arr_leavetype = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr']) . "/" . strtoupper($leave[0]['abbr']);
            $arr_leavetype[] = strtoupper($leave[0]['abbr']);
        }
        //debug($arr_leaveabbr);
        $query = "CALL insert_update_att_reg('$company_code','NULL','$user_id','$month');";

        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                $fields = 'AttendanceRegister.*,EmployeeDetails.*,Termination.last_approved_working_date,Branch.branch_name,Info.*';

                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.branch_code = Branch.branch_code',
                            'Branch.status = 1'
                        )
                    ),
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.emp_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'employee_info',
                        'alias' => 'Info',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = Info.emp_pkey')
                    ),
                    array(
                        'table' => 'termination',
                        'alias' => 'Termination',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey  = Termination.emp_fkey', 'Termination.status = 1')
                    )
                );
                $conditions = array("isdelete" => "N", "Branch.status" => "1");
                if ($arr_form_data['hidden-criteria1'] == "Units") {
                    $conditions[] = 'AttendanceRegister.branch_code="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"  ';
                } else {
                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"';
                }
                if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

                    $conditions[] = "EmployeeDetails.status in('1','2')";
                } else {
                    $conditions[] = "EmployeeDetails.status ='1' ";
                }
                $arr_leavepolicy_details = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, "order" => "Info.EmpName"));
                if (!empty($arr_leavepolicy_details)) {
                    $arr_leavepolicydetails_for_template[] = array(
                        'summary' => $arr_leavepolicy_details,
                    );
                }
            }
            function getcounts($input, $arr_datas)
            {
                $result = preg_grep('~' . $input . '~', $arr_datas);
                return count($result);
            }

            foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                foreach ($value['summary'] as $ky => $vaal) {
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $vaal["AttendanceRegister"]['presant_total'];
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $vaal["AttendanceRegister"]['leave_total'];
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $vaal["AttendanceRegister"]['weekoff_total'];
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $vaal["AttendanceRegister"]['lop_total'];
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $vaal["AttendanceRegister"]['holiday_total'];
                }
            }

            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $this->set('company_code', $company_code);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set('month', $from);
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            //echo date('d-m-Y H:i');
            $date_time = date('d-m-Y H:i');
            $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $time = strtotime($f);
            $month = date("m", $time);
            $mname = date('F', mktime(0, 0, 0, $month, 10));
            $month1 =  $month . '-01';
            $year = date("Y", $time);
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);
            $this->set('mname', $mname);
            $this->set('month2', $month);
            $this->set('month1', $month1);
            $this->set('year', $year);
            $cname = $arr_comp_contact_info['CompanyContactInfo']['business_name'];
            $city = $arr_comp_contact_info['CompanyContactInfo']['city'];
            $state = $arr_comp_contact_info['CompanyContactInfo']['state'];
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('reportsummary');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'A2', 'en');

                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('VerifiedAttendanceRegisterReport.pdf', 'D');


                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_MusterRoll.xlsx" : "MusterRoll" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("MusterRoll By Greatleap");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();


                    //$rowcount1=3;
                    //edited by megha 2/12/2019 serial no.corrected
                    if ($arr_form_data['hidden-criteria1'] == "EmployeeDetails") {
                        $k = 1;
                    }
                    $k = 1;
                    if (!empty($arr_leavepolicydetails_for_template)) {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, "Name of Establishment");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 3)->getFont()->setBold(true);
                        $worksheet->mergeCells('D3:E3');
                        $worksheet->getStyle('D3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 4, "Place");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 4)->getFont()->setBold(true);
                        $worksheet->mergeCells('D4:E4');
                        $worksheet->getStyle('D4')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 5, "District");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 5)->getFont()->setBold(true);
                        $worksheet->mergeCells('D5:E5');
                        $worksheet->getStyle('D5')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $daycount = (count($arr_dates));

                        if ($daycount == 28) {
                            $worksheet->mergeCells('J1:AP1');
                            $worksheet->mergeCells('J2:AP2');
                            $worksheet->mergeCells('J3:AP3');
                            $worksheet->mergeCells('J4:AP4');
                            $worksheet->mergeCells('J5:AP5');
                        } elseif ($daycount == 29) {
                            $worksheet->mergeCells('J1:AQ1');
                            $worksheet->mergeCells('J2:AQ2');
                            $worksheet->mergeCells('J3:AQ3');
                            $worksheet->mergeCells('J4:AQ4');
                            $worksheet->mergeCells('J5:AQ5');
                        } elseif ($daycount == 30) {
                            $worksheet->mergeCells('J1:AR1');
                            $worksheet->mergeCells('J2:AR2');
                            $worksheet->mergeCells('J3:AR3');
                            $worksheet->mergeCells('J4:AR4');
                            $worksheet->mergeCells('J5:AR5');
                        } elseif ($daycount == 31) {
                            $worksheet->mergeCells('J1:AS1');
                            $worksheet->mergeCells('J2:AS2');
                            $worksheet->mergeCells('J3:AS3');
                            $worksheet->mergeCells('J4:AS4');
                            $worksheet->mergeCells('J5:AS5');
                        }



                        $worksheet->mergeCells('D2:H2');


                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 3, "$cname");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 3)->getFont()->setSize(12);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 4, "$city");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 4)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 4)->getFont()->setSize(12);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 5, "$state");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 5)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, 5)->getFont()->setSize(12);
                        $worksheet->mergeCells('I4:I5');
                        $worksheet->mergeCells('A1:B5');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 3, "Year");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 3, "$year");


                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 3)->getFont()->setSize(12);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 4, "Month");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, 4)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 4, "$mname");

                        $worksheet->mergeCells('C1:H1'); // Edited by Akshay on 28-3-2026

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 4)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, 4)->getFont()->setSize(12);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, "FORM VI");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 1)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 1)->getFont()->setSize(13);


                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 2, "(See Rule 11)");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 2)->getFont()->setSize(13);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 3, "MUSTER ROLL");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, 3)->getFont()->setSize(13);



                        $worksheet = $objPHPExcel->getActiveSheet();

                        $worksheet->getStyle('A1:I5')
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        for ($col = 'A'; $col !== 'Z'; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(true);
                        }

                        $rowcount = 6;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 0), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Father/Husband Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Present Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Week Off');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Holidays');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Non Paying Days'); // Edited by Akshay on 27-2-2026
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);


                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $columnindex = $columncount + 14;
                        foreach ($arr_dates as $key => $date) {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                            $columnindex++;
                        }

                        // Edited by Akshay on 20-3-2026
                        $lastColumn = PHPExcel_Cell::stringFromColumnIndex($columnindex - 1);
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . $lastColumn . $rowcount)
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A' . $rowcount . ':' . $lastColumn . $rowcount)
                            ->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('D9D9D9');
                        // End

                        $rowcount = 7;
                        foreach ($arr_leavepolicydetails_for_template as $value) {
                            $branch = isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';
                            //echo $branch;die();                     
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);

                            $arr_data = $value['summary'];

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                            if (count($arr_data) >= 0) {
                                //edited by megha 2/12/2019 serial no.corrected
                                if ($arr_form_data['hidden-criteria1'] == "Units") {
                                    //  $k = 1;
                                }
                                foreach ($arr_data as $key => $val) {

                                    // debug($val);

                                    $columnindex = 0;
                                    $eid = $val['Info']['employee_id'];
                                    $name = ucwords(strtolower($val['Info']['EmpName']));

                                    $guardian = ucwords(strtolower($val['EmployeeDetails']['guradian']));
                                    $des =  ucwords(strtolower($val['Info']['designation']));
                                    $join = $val['Info']['joining_date'];
                                    $termination = $val['Termination']['last_approved_working_date'];
                                    $department = ucwords(strtolower($val['Info']['department']));
                                    $branch = ucwords(strtolower($val['Info']['branch']));
                                    $present = $val['AttendanceRegister']['days_present'];
                                    $leave = $val['AttendanceRegister']['days_leave'];
                                    $weekoff = $val['AttendanceRegister']['days_holidays'];
                                    $holidays = $val['AttendanceRegister']['HO'];
                                    $lop = $val['AttendanceRegister']['lops'];

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                                    // Edited by Akshay on 20-3-2026
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyleByColumnAndRow($columnindex, $rowcount)
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    // End
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $eid);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $guardian);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $des);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $termination);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $branch);

                                    // Edited by Akshay on 23-3-2026
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle(
                                            PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount . ':' .
                                                PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount
                                        )
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    // End

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $present);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $leave);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $weekoff);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $holidays);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $lop);

                                    // Edited by Akshay on 23-3-2026
                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle(
                                            PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount . ':' .
                                                PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount
                                        )
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                    // End

                                    $columnindex = $columnindex + 14;
                                    foreach ($arr_dates as $key => $date) {
                                        $newIndex = 'FIELD' . ($key + 1);
                                        $dta = $val['AttendanceRegister'][$newIndex];
                                        $dta = str_replace("WFH", "P", $dta);
                                        $dta = str_replace("PL", "L", $dta);
                                        $dta = str_replace("SL", "L", $dta);
                                        $dta = str_replace("EL", "L", $dta);
                                        $dta = str_replace("CL", "L", $dta);
                                        $dta = str_replace("ML", "L", $dta);
                                        $dta = str_replace("PT", "L", $dta);
                                        $dta = str_replace("COFF", "L", $dta);
                                        $dta = str_replace("AL", "L", $dta);
                                        if ($company_code == 'HRBL') {
                                            //  $dta = str_replace("P/P", "P", $dta);

                                            $split = explode('/', $dta);
                                            if (count($split) == 2 && $split[0] == $split[1]) {
                                                $dta = $split[0];
                                            }
                                        } // Edited by Akshay on 5-12-2024


                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);

                                        $columnindex++;
                                    }

                                    // Edited by Akshay on 23-3-2026
                                    $startColumn = $columnindex - count($arr_dates); // where dates started
                                    $endColumn = $columnindex - 1;

                                    $objPHPExcel->getActiveSheet()
                                        ->getStyle(
                                            PHPExcel_Cell::stringFromColumnIndex($startColumn) . $rowcount . ':' .
                                                PHPExcel_Cell::stringFromColumnIndex($endColumn) . $rowcount
                                        )
                                        ->getAlignment()
                                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    // End

                                    $k++;
                                    $rowcount++;


                                    $daycount = (count($arr_dates));
                                    // debug($daycount);

                                    if ($daycount == 28) {
                                        $BStyle = array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                                )
                                            )
                                        );
                                        $row = $rowcount - 1;
                                        $objPHPExcel->getActiveSheet()->getStyle('A1:AP' . $row)->applyFromArray($BStyle);
                                    } elseif ($daycount == 29) {
                                        $BStyle = array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                                )
                                            )
                                        );
                                        $row = $rowcount - 1;
                                        $objPHPExcel->getActiveSheet()->getStyle('A1:AQ' . $row)->applyFromArray($BStyle);
                                    } elseif ($daycount == 30) {
                                        $BStyle = array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                                )
                                            )
                                        );
                                        $row = $rowcount - 1;
                                        $objPHPExcel->getActiveSheet()->getStyle('A1:AR' . $row)->applyFromArray($BStyle);
                                    } elseif ($daycount == 31) {
                                        $BStyle = array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                                )
                                            )
                                        );
                                        $row = $rowcount - 1;
                                        $objPHPExcel->getActiveSheet()->getStyle('A1:AS' . $row)->applyFromArray($BStyle);
                                    }

                                    foreach (range('A', 'W') as $columnID) {
                                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                                    }
                                }
                            }
                            $rowcount1 = $rowcount + 1;
                        }

                        // Edited by Akshay on 23-3-2026
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A1:' . $lastColumn . $rowcount1)
                            ->getAlignment()
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        // End

                        $objPHPExcel->getActiveSheet()->freezePane('D7'); // Edited by Akshay on 20-3-2026
                    } else {

                        $worksheet->setCellValueByColumnAndRow(0, 1, "Muster Roll Register - " . $report_month);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(14);
                        $worksheet->mergeCells('A1:P1');
                        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                        );
                        $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                        );
                        $worksheet->setCellValueByColumnAndRow(0, 2, "There is no data available under the selected criteria.");
                        $worksheet->mergeCells('A2:P2');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    }

                    $objPHPExcel->getActiveSheet()->setShowGridlines(false); // Edited by Akshay on 20-3-2026

                    $objPHPExcel->getActiveSheet()->setTitle('Muster Roll');
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
                default:
                    $this->set('mode', '');
                    $this->render('Musterroll');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

    // Edited by Akshay on 5-2-2026
    private function generateServiceRecordReport($mode)
    {
        // --- All your initial setup and data fetching logic is CORRECT and REMAINS UNCHANGED ---
        $arr_form_data = $_REQUEST;
        date_default_timezone_set('Asia/Kolkata');
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $date = date('d-m-Y');
        $this->set('date', $date);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $user_id = $this->Session->read('login_user_id');

        $report_month = $arr_form_data['reportfrom'];
        $this->set('report_month', $report_month);

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        $business_name = $arr_comp_contact_info['CompanyContactInfo']['business_name'];


        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

            $resign_condition = " and ed.status in ('1','2') ";
        } else {

            $resign_condition = " and ed.status = '1' ";
        }

        if (isset($arr_form_data['ngtvsal']) && $arr_form_data['ngtvsal'] == '1') {

            $ngtvsal_condition = "";
        } else {

            $ngtvsal_condition = " and pm.net_salary >= 0 ";
        }

        $arr_leavepolicydetails_for_template = [];
        if (!empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                $policy_condition = ($str_criteria_item == 'EmployeeDetails') ?  " pm.emp_fkey = '$leavepolicygroupid' " : " pm.branch_code = '$leavepolicygroupid' ";

                $arr_details = $this->EmployeeDetails->query("
                                                                    SELECT
                                                                        '$business_name' AS establishment,
                                                                        pm.emp_fkey,
                                                                        ei.EmpName,
                                                                        ei.designation,
                                                                        ed.guradian,

                                                                        CASE
                                                                            WHEN ed.date_of_birth IS NULL OR ed.date_of_birth = '0000-00-00' THEN ''
                                                                            ELSE TIMESTAMPDIFF(YEAR, ed.date_of_birth, CURDATE())
                                                                        END AS age,

                                                                        ed.address,

                                                                        CASE
                                                                            WHEN ed.classification = 'male' THEN 'Male'
                                                                            WHEN ed.classification = 'female' THEN 'Female'
                                                                            ELSE 'Transgender'
                                                                        END AS gender,

                                                                        ei.joining_date,
                                                                        pm.desig,
                                                                        t.last_approved_working_date,

                                                                        /* Salary breakup */
                                                                        COALESCE(SUM(
                                                                            CASE 
                                                                                WHEN tsc.tax_salary_components_name = 'Basic'
                                                                                THEN ess.salary_amount
                                                                                ELSE 0
                                                                            END
                                                                        ), 0) AS basic,

                                                                        COALESCE(SUM(
                                                                            CASE 
                                                                                WHEN tsc.tax_salary_components_name = 'Dearness Allowance (DA)'
                                                                                THEN ess.salary_amount
                                                                                ELSE 0
                                                                            END
                                                                        ), 0) AS DA,

                                                                        COALESCE(SUM(
                                                                            CASE
                                                                                WHEN ess.item_part = 'Direct'
                                                                                AND ess.head_operator = 'Addition'
                                                                                AND ( tsc.tax_salary_components_name NOT IN ('Basic', 'Dearness Allowance (DA)') OR tsc.tax_salary_components_name IS NULL )
                                                                                THEN ess.salary_amount
                                                                                ELSE 0
                                                                            END
                                                                        ), 0) AS other_allowances

                                                                    FROM payroll_master pm
                                                                    LEFT JOIN employee_info ei 
                                                                        ON ei.emp_pkey = pm.emp_fkey
                                                                    LEFT JOIN emp_details ed 
                                                                        ON ed.emp_pkey = pm.emp_fkey
                                                                    LEFT JOIN termination t 
                                                                        ON (t.emp_fkey = pm.emp_fkey AND t.status = 1)
                                                                    LEFT JOIN emp_salary_slip ess 
                                                                        ON ess.payroll_master_fkey = pm.payroll_master_pkey
                                                                    AND ess.end_date_effective IS NULL
                                                                    LEFT JOIN tax_salary_components tsc 
                                                                        ON tsc.salary_head_item_fkey = ess.salary_head_item_fkey

                                                                    WHERE 
                                                                    $policy_condition
                                                                    $resign_condition
                                                                    $ngtvsal_condition
                                                                    AND pm.month_year = '$report_month'
                                                                    AND pm.action IN ('Approved', 'Processed')

                                                                    GROUP BY pm.emp_fkey;
                                                                ");


                if (!empty($arr_details)) {

                    if ($str_criteria_item == 'EmployeeDetails') {
                        $arr_leavepolicydetails_for_template[$leavepolicygroupid] = $arr_details;
                    } else {
                        foreach ($arr_details as $emp_details) {
                            $emp_pkey = isset($emp_details['pm']['emp_fkey']) ? $emp_details['pm']['emp_fkey'] : 0;

                            if ($emp_pkey != 0)
                                $arr_leavepolicydetails_for_template[$emp_pkey][] = $emp_details;
                        }
                    }
                }
            }
        }


        $date_time = date('d-m-Y H:i');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $hiddenreporttype = $arr_form_data['hidden-report-type'];

        $this->set('reporttype', $hiddenreporttype);
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $this->set('mname', $mname);
        $this->set('month2', $month);
        $this->set('month1', $month1);
        $this->set('year', $year);
        $criteria = $arr_form_data['select-criteria1'];
        $this->set('criteria', $criteria);
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);

        // debug($arr_leavepolicydetails_for_template);
        // exit;
        $str_company_code = $this->Session->read('company_code');

        switch ($mode) {
            case 'pdf':
                //echo "entered in";die();
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('service_record');

                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');

                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $file_name = $str_company_code . "_Service_Record_" . $mname . "_" . $year . ".pdf";
                $html2pdf->Output($file_name, 'D');
                break;
            case 'excel':
                // --- This block is the ONLY part we are changing ---
                $file_name = $str_company_code . "_Service_Record_" . $mname . "_" . $year . ".xlsx";
                App::import('Vendor', 'PHPExcel', ['file' => 'PHPExcel.php']);
                App::import('Vendor', 'PHPExcel_Writer_Excel2007', ['file' => 'PHPExcel/Writer/Excel2007.php']);

                $objPHPExcel = new PHPExcel();
                $sheet = $objPHPExcel->setActiveSheetIndex(0);
                $sheet->setTitle('Service Record');

                $rowNum = 1;

                /* =======================
                            COMMON STYLES
                            ======================= */
                $styleBold = [
                    'font' => ['bold' => true]
                ];

                $styleCenterBold = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
                ];

                $styleLabel = [
                    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT],
                    'font' => ['bold' => false]
                ];

                $styleValue = [
                    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT]
                ];

                /* Column widths */
                $sheet->getColumnDimension('A')->setWidth(45);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('C')->setWidth(55);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(40);

                /* =======================
                                    NO DATA CASE
                                    ======================= */
                if (empty($arr_leavepolicydetails_for_template)) {

                    $sheet->setCellValue("A{$rowNum}", 'No data available under the selected criteria');
                    $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
                    $sheet->getStyle("A{$rowNum}")
                        ->getFont()->setSize(16);

                    $sheet->getStyle("A{$rowNum}")
                        ->getAlignment()->setHorizontal(
                            PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        );
                } else {

                    foreach ($arr_leavepolicydetails_for_template as $empData) {

                        $row    = $empData[0];
                        $common = $row[0];
                        $pm     = $row['pm'];
                        $ei     = $row['ei'];
                        $ed     = $row['ed'];
                        $t      = $row['t'];

                        /* =======================
                                            HEADER
                                            ======================= */
                        $sheet->setCellValue("A{$rowNum}", 'FORM BB');
                        $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
                        $sheet->getStyle("A{$rowNum}")->applyFromArray($styleCenterBold);
                        $rowNum++;

                        $sheet->setCellValue("A{$rowNum}", '[See Rule 10 (1)a]');
                        $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
                        $sheet->getStyle("A{$rowNum}")
                            ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $rowNum++;

                        $sheet->setCellValue("A{$rowNum}", 'SERVICE RECORD');
                        $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
                        $sheet->getStyle("A{$rowNum}")->applyFromArray($styleCenterBold);
                        $rowNum += 2;

                        /* =======================
                        HELPER FUNCTION
                        ======================= */
                        $addRow = function ($label, $value) use (&$sheet, &$rowNum, $styleLabel, $styleValue) {
                            $sheet->setCellValue("A{$rowNum}", $label);
                            $sheet->setCellValue("B{$rowNum}", ':');
                            $sheet->setCellValue("C{$rowNum}", $value);
                            $sheet->mergeCells("C{$rowNum}:E{$rowNum}");
                            $sheet->getStyle("A{$rowNum}")->applyFromArray($styleLabel);
                            $sheet->getStyle("C{$rowNum}")->applyFromArray($styleValue);
                            $rowNum++;
                        };

                        /* =======================
                        DETAILS
                        ======================= */
                        $addRow('1) Name of Establishment', $common['establishment']);

                        $empName = '';
                        if (isset($ei['EmpName']) && !empty($ei['EmpName'])) {
                            $empName = trim($ei['EmpName']);
                        } elseif (isset($common['EmpName']) && !empty($common['EmpName'])) {
                            $empName = trim($common['EmpName']);
                        }

                        $addRow('2) Name of Employee', $empName);

                        $addRow('3) Name of father/husband', $ed['guradian']);
                        $addRow('4) Age', $common['age']);
                        $addRow('5) Full Residential address', $ed['address']);
                        $addRow('6) Sex', $common['gender']);
                        $joiningDate = '';

                        if (isset($ei['joining_date']) && !empty($ei['joining_date'])) {
                            $joiningDate = $ei['joining_date'];
                        } elseif (isset($common['joining_date']) && !empty($common['joining_date'])) {
                            $joiningDate = $common['joining_date'];
                        }

                        $addRow('7) Date of entry into service', $joiningDate);

                        // Edited by Akshay on 19-2-2026
                        if (isset($pm['desig']) && $pm['desig'] !== '') {
                            $designation = $pm['desig'];
                        } elseif (isset($ei['designation']) && $ei['designation'] !== '') {
                            $designation = $ei['designation'];
                        } elseif (isset($common['designation'])) {
                            $designation = $common['designation'];
                        } else {
                            $designation = '';
                        }

                        $addRow('8) Category / designation', $designation);
                        // End

                        /* =======================
                        PAY SECTION
                        ======================= */
                        $sheet->setCellValue("A{$rowNum}", '9) Pay');
                        $sheet->setCellValue("B{$rowNum}", '');
                        $rowNum++;

                        $sheet->setCellValue("A{$rowNum}", 'Basic: ' . $common['basic']);
                        $sheet->setCellValue("C{$rowNum}", 'DA: ' . $common['DA']);
                        $sheet->setCellValue("D{$rowNum}", 'Other Emoluments: ' . $common['other_allowances']);

                        $sheet->getStyle("A{$rowNum}:E{$rowNum}")
                            ->getFont()->setSize(11);

                        $rowNum += 2;

                        /* =======================
                        FINAL DETAILS
                        ======================= */
                        $addRow(
                            '10) Date of Retire / Discharge / Resignation',
                            $t['last_approved_working_date']
                        );
                        $addRow('11) Signature of Employee', '');
                        $addRow('12) Signature of Employer', '');
                        $addRow('13) Counter signature of the Inspector', '');

                        /* =======================
                            NOTE
                            ======================= */
                        $rowNum++;
                        $sheet->setCellValue(
                            "A{$rowNum}",
                            'Note: whenever there is a change in designation and wages, the changes shall be noted in column 8 and 9 respectively with date of such changes.'
                        );
                        $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
                        $sheet->getStyle("A{$rowNum}")->getFont()->setSize(11);

                        $rowNum += 3;
                    }
                }

                /* =======================
                    OUTPUT
                    ======================= */
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                header('Cache-Control: max-age=0');

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save('php://output');
                exit;

                break;

            default:
                $this->render('service_record');
                break;
        }
    }
    // End
}
