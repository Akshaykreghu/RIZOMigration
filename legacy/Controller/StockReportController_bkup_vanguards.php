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
class StockReportController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'StockReport';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeavePolicyGroup', 'CentralControl','LoanEmi', 'Store', 'MaterialRequest', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'Departments', 'EmployeeGrossDetails', 'Verticals', 'Units', 'ReportCriterias', 'DayTimeProcedures', 'EmpCtcTransaction', 'LeaveRequests', 'Designation','item_allocate', 'allocate_details',);
    public $components = array('MasterdataManagement');

    /* public $arr_employee_reportcriterias = array(
      'Departments' => 'belonging to a Department',
      'Grades' => 'belonging to a Grade',
      'Verticals' => 'belonging to a Vertical',
      'Units' => 'belonging to a Branch',salary
      'EmployeeDetails' => 'randomly, without criteria'
      );

      public $arr_employee_reportcriteria_fields = array(
      'Departments' => 'emp_dept',
      'Grades' => 'emp_grade',
      'Verticals' => 'emp_vertical',
      'Units' => 'emp_branch',
      'EmployeeDetails' => 'emp_pkey'
      ); */

    /*
     * HR Reports landing view
     */

    public function hrreports() {
        $arr_reporttypes = array(
//            'employee' => 'Employee Information',
            // 'LeaveDetaillsReport' =>  'Leave Details Reports',
            'Stock' => 'Stock Report',
            'StockDetail' => 'Stock Monthly Report',
            'Material' =>  'Material Request Report',
            'PO' =>  'Purchase Order Report',
            'GRN' =>  'Goods Received Notes Report',
            'Allocation' =>  'Uniform Allocation Report',
            'StockTransfer' =>  'Stock transfer Report',
             'AllocationEMI' =>  'Uniform Allocation EMI Report',

                /* 'leave' => 'Leaves Report',
                  'attendance' => 'Attendance Summary', */
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
                case 'employee':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LeaveSummary':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'salarystructure':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //ARUN Gross
                case 'Grosssalary' :
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'GrosssalarySummary':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'SummaryPayroll':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'BankTranfer':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Material':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'PO':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'StockTransfer':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Allocation':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'AllocationEMI':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'GRN':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'StockDetail':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //santhu
                case 'Stock':
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

    /*
     * Add criterias
     */

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

    public function listcriteriaitems($str_criteria = '') {
        $this->autoRender = false;
        $model = $str_criteria;

        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');

            $arr_order = array();
            if ($model == 'SalaryHeadItems') {
                $conditions = array("head_fkey" => 6, "value" => 'Y', "status" => 1);
            } elseif ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Leavestatus') {
                $conditions = array();
            } elseif ($model == 'EmployeeGrossDetails') {
                $conditions = array();
            } elseif ($model == 'Units') {
                $arr_order = array("Units.branch_name" => "ASC");
                $conditions = array();
            } else {
                $conditions = array("status" => 1);
            }
            
            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => $arr_order)));
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'Departments':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['dept_code'];
                        $arr_criteriaItems[$key]['text'] = $value['dept_name'];
                        $key++;
                    }
                    break;
                case 'Grades':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['grade_code'];
                        $arr_criteriaItems[$key]['text'] = $value['grade_name'];
                        $key++;
                    }
                    break;
                case 'Verticals':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['vert_code'];
                        $arr_criteriaItems[$key]['text'] = $value['vert_name'];
                        $key++;
                    }
                    break;
                case 'Store':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['store_master_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['store_location'];
                        $key++;
                    }
                    break;
                case 'LeaveRequests':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVEENTRYID'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVESTATUS'];
                        $key++;
                    }
                    break;
                case 'Leavestatus':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVESTATUS'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVESTATUS'];
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

                case 'LeavePolicyGroup':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVEPOLICY_GROUP_ID'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVEPOLICY_GROUP_NAME'];
                        $key++;
                    }
                    break;
                //ARUN Gross
                case 'EmployeeGrossDetails':
                    // echo 'ih';
                    $fields = 'id,Designation.desig_name';
//                                    $joins = array(
//                                        array(
//                                        'table' => 'emp_proff',
//                                        'alias' => 'EmployeeProfessionalDetails',
//                                        'type' => 'LEFT',
//                                        'foreignKey' => false,
//                                        'conditions'=> array('EmployeeGrossDetails.emp_fkey = EmployeeProfessionalDetails.emp_fkey')
//                                        ),
//                                        array(
//                                        'table' => 'emp_details',
//                                        'alias' => 'EmployeeDetails',
//                                        'type' => 'LEFT',
//                                        'foreignKey' => false,
//                                        'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeGrossDetails.emp_fkey')
//                                        )
//                                    );
                    $conditions = array('status' => 1);
                    $this->Designation->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->Designation->find("all", array(
                        'fields' => $fields,
                        // 'joins' => $joins,
                        'conditions' => $conditions,
                            // 'group' => 'EmployeeGrossDetails.emp_fkey'
                    ));
                    //debug($arr_emp);
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value['Designation']['desig_name'];
                        $arr_criteriaItems[$key]['key'] = $value['Designation']['id'];
                        $key++;
                    }
                    // debug($arr_emp);
                    break;
                case 'EmployeeDetails':
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name,"")," - ",emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                        )
                    );

                    $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    $conditions = array("status" => 1);

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
                        $key++;
                    }
                    break;
                case 'DayTimeProcedures':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['day_time_seq'];
                        $arr_criteriaItems[$key]['text'] = $value['day_time_desc'];
                        $key++;
                    }
                case 'attendance':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['day_time_seq'];
                        $arr_criteriaItems[$key]['text'] = $value['day_time_desc'];
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
            
            case 'Stock':
                $this->GenerateSummaryPayrolreport($mode);
                break;
            case 'StockTransfer':
                $this->GenerateStockTransferreport($mode);
                break;
            case 'Allocation':
                $this->GenerateAllocationreport($mode);
                break;
            case 'StockDetail':
                $this->GenerateStockreport($mode);
                break;
            case 'Material':
                $this->GenerateSalarySlipreport($mode);
                break;
            case 'GRN':
                $this->GenerateGRNreport($mode);
                break;
            case 'PO':
                $this->GeneratePoreport($mode);
                break;
            case 'AllocationEMI':
                $this->GenerateUniformAllocationEMIReport($mode);
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
    

    // edited by sruthi 09/09/16    here ends     
    // edited by sruthi 22/09/16
    private function GenerateSalaryGrossSummary($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');


        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
                                                    where item_part='Direct' Group by salary_head_item_desc
                                                    ORDER BY emp_salary_slip_pkey");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();
        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

            $arr_gross = $this->EmpCtcTransaction->query(" select info.*,ectc.* from emp_salary_slip as ectc "
                    . " left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)"
                    . "where  ectc.emp_fkey= '$leavepolicygroupid' "
                    . "AND ectc.item_part = 'Direct' "
                    . "AND ectc.end_date_effective is null "
                    . "AND  $id  order by info.EmpName");


            $arr_ot = $this->EmpCtcTransaction->query("select ot_master.set_duration from emp_ot_master as ot_master where ot_master.emp_fkey='$leavepolicygroupid' "
                    . "And ot_master.month='$otdate' and ot_master.is_verified='Y'");

            $pro_date_desc = $this->getprodataDesc($leavepolicygroupid, $from);
            //debug($pro_date_desc);
            $this->set('arr_gross', $arr_gross);


            if (isset($arr_gross) && !empty($arr_gross)) {
                $gross[$k]['emp_info'] = $arr_gross[0]['info'];
                $gross[$k]['ectc'] = $arr_gross[0]['ectc'];
                $gross[$k]['ot'] = isset($arr_ot[0]['ot_master']['set_duration']) ? $arr_ot[0]['ot_master']['set_duration'] : 0;
                $gross[$k]['prodata'] = $pro_date_desc;


                $coun = count($arr_keys);
                //debug($arr_gross);
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
                foreach ($arr_gross as $value) {
                    if ($value['ectc']['head_operator'] == 'Addition') {
                        $data = trim($value['ectc']['salary_head_item_desc']);
                        $key = array_search($data, $gross[$k]['Addition']['keys']); // $key = 2;
                        $gross[$k]['Addition']['value'][$key] = $value['ectc']['salary_amount'];
                        $gross[$k]['Addition']['actual'][$key] = $value['ectc']['salary_rate'];
                    } else {
                        $data = trim($value['ectc']['salary_head_item_desc']);
                        $key = array_search($data, $gross[$k]['Deduction']['keys']); // $key = 2;
                        $gross[$k]['Deduction']['value'][$key] = $value['ectc']['salary_amount'];
                        $gross[$k]['Deduction']['actual'][$key] = $value['ectc']['salary_rate'];
                    }
                }
            }

            $arr_salary_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    //'summary' => $arr_empleaverequests,
                    // 'employees'=>$arr_leavepolicy_employees
            );
            $k++;
        }

        //}
        $this->set('keys', $arr_keys);
        $this->set('gross', $gross);
        $this->set('array_key', $array_key);
        //debug($gross);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        //Set informations needed for report

        switch ($mode) {
            case 'pdf' :
                //   echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('grosssummaryreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('grosssummaryreport.pdf', 'D');
                $this->render('grosssummaryreport');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_gross.xlsx" : "GrossReport" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Gross Salary Reports for " . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'M'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 2;

                $i = 0;
                // $rowcount = 3;

                $col = 0;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, '  Sl No  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  ID   ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Employee Name  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, '  Designation ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '  Department  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '  Branch ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '  Total Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '  Days Type');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, '  Present Days ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, '  Overtime (In Hrs.) ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                $col = 9;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, '  Gross Salary  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $col = 10;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, ' Total Deduction  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Net Salary ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                $j = 1;

                $rowcount = $rowcount + 1;

                foreach ($gross as $val) {
                    $col = 0;
                    $name = $val['emp_info']['EmpName'];
                    $id = $val['emp_info']['employee_id'];
                    $deg = $val['emp_info']['designation'];
                    $dep = $val['emp_info']['department'];
                    $branch = $val['emp_info']['branch'];
                    $prodays = $val['prodata']['days'];
                    $type = $val['prodata']['type'];
                    $present_total = round($val['prodata']['present']);
                    $ot = isset($val['ot']) ? round(($val['ot'] / 60), 2) : 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $name);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $deg);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $dep);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $branch);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $prodays);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $type);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $present_total);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, $ot);

                    $col = 9;

                    //debug($val);
                    $count_addition = count($val['Addition']['keys']);
                    $grss_amt = 0;
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Addition']['value'][$m]);
                        $grss_amt = $number + $grss_amt;

                        $val1 = $number;
                    }

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $grss_amt);
                    $count_addition = count($val['Deduction']['keys']);
                    $col = $col + 1;
                    $dd_amt = 0;
                    for ($m = 0; $m < $count_addition; $m++) {
                        $number = round($val['Deduction']['value'][$m]);
                        $dd_amt = $number + $dd_amt;

                        $val1 = $number;
                    }


                    $netamt = $grss_amt + $dd_amt;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $dd_amt);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $netamt);

                    $j++;
                    $rowcount++;
                }

//                                     
                $objPHPExcel->getActiveSheet()->setTitle('Gross Salary Reports');
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
                $this->render('grosssummaryreport');
                break;
        }
    }

    //code ends here//


    private function GenerateGRNreport($mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
//        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//      
        $arr_stocksummary_for_template = array();
        if(!isset($arr_form_data['Store'])){
            echo "No Items Selected";
            return false;
        }
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
            
            $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT sm.store_location,gr.gr_number,gr.gr_date,po.po_number,im.item_code,im.item_desc,gi.gr_item_pkey,gi.grn_fkey,gi.ordering_qty,gi.received_qty FROM `gr_item_details` gi join goods_receved_notes gr on (gr.grn_pkey = gi.grn_fkey) 
 join purchase_order po on (po.po_pkey = gi.po_fkey) 
join item_master im on (im.item_master_pkey = gi.item_code)
join store_master sm on (sm.store_master_pkey = gr.store_code)
where gr.store_code = '$val'  and gr_date between '$from' and '$to'  ");
            
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
            
            
            
        }
        
//        debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('grn');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportGR.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Goods Received Notes Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 4;
                

                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['sm']['store_location']) ? "Store - ".$value['0']['sm']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'GRN Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'GRN Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'PO Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Ordering Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Received Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {

                                $slno = $i;
                                $gr_number = $val['gr']['gr_number'];
                                $gr_date = $val['gr']['gr_date'];
                                $po_number = $val['po']['po_number'];
                                $item_code = $val['im']['item_code'];
                                $item_nmes = $val['im']['item_desc'];
                                $ordering_qty = $val['gi']['ordering_qty'];
                                $received_qty = $val['gi']['received_qty'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $gr_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $gr_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $po_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $item_nmes);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $ordering_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $received_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                $objPHPExcel->getActiveSheet()->setTitle('Goods Received Notes Report');
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
                $this->render('grn');
                break;
        }
    }

    private function GenerateStockTransferreport($mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');


        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
                                                    where item_part='Direct' Group by salary_head_item_desc
                                                    ORDER BY emp_salary_slip_pkey");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-d', strtotime($arr_form_data['reportto']));
//        $conditions = array();
        $conditions = "itm_allocation.date_allocated  between '$from' and '$otdate'  ";
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();
//        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        if(empty($arr_leavepolicygroupids)){
            echo "please choose Criteria";
            return false;
        }
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
               $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT itm_allocation.date_allocated,employee_info.*,allocate_details.*,store_master.store_location,item_master.item_desc FROM `allocate_details` join itm_allocation on (itm_allocation.allocation_pkey = allocate_details.allocate_fkey)
join employee_info on (employee_info.emp_pkey = itm_allocation.emp_fkey)
join item_master on (item_master.item_master_pkey = allocate_details.item_purchase_fkey)
join store_master on (store_master.store_master_pkey = allocate_details.store_code)
WHERE itm_allocation.emp_fkey = '$leavepolicygroupid' and $conditions
ORDER BY `allocate_details_pkey` DESC ");
            }else{
                $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT itm_allocation.date_allocated,employee_info.*,allocate_details.*,store_master.store_location,item_master.item_desc FROM `allocate_details` join itm_allocation on (itm_allocation.allocation_pkey = allocate_details.allocate_fkey)
join employee_info on (employee_info.emp_pkey = itm_allocation.emp_fkey)
join item_master on (item_master.item_master_pkey = allocate_details.item_purchase_fkey)
join store_master on (store_master.store_master_pkey = allocate_details.store_code)
WHERE allocate_details.store_code = '$leavepolicygroupid' and $conditions
ORDER BY `allocate_details_pkey` DESC ");
            }    
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
            
            
        }
        
//        debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->set('type', $arr_form_data['select-criteria1']);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('uniformallocation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportAllocation.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Uniform Allocation Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'L'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getColumnDimension('L')->setWidth(20);


                $rowcount = 3;
                

                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                         if  ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                            $branch = isset($value['0']['employee_info']['EmpName']) ? "Allocation Report of ".$value['0']['employee_info']['EmpName'] : '';
                        } else {
                            $branch = isset($value['0']['store_master']['store_location']) ? "Allocation Report of ".$value['0']['store_master']['store_location'] : '';
                        }
                        

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Date Of Join');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Store');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Allocated Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Returned Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Balance Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
//                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $i;
//                                $PO_Status = $val['po']['grn_status'];
//                                        $string_po = '';
//                                        switch ($PO_Status){
//                                            case "0": $string_po = "GRN Received";
//                                                break;
//                                            case "1": $string_po = "PO Ordered";
//                                                break;
//                                            case "2": $string_po = "Finalised";
//                                                break;
//                                            default : $string_po = "PO";
//                                                break;
//                                        }
                                $employee_name = $val['employee_info']['EmpName'];
                                $employee_id =  $val['employee_info']['employee_id'];
                                $date_of_join = $val['employee_info']['joining_date'];
                                $branch = $val['employee_info']['branch'];
                                $department = $val['employee_info']['department'];
                                $designation = $val['employee_info']['designation'];
                                $item_name = $val['item_master']['item_desc'];
                                $Store_name = $val['store_master']['store_location'];
                                $allocated_qty = $val['allocate_details']['qty'];
                                $retund_qty = $val['allocate_details']['returned_qty'];
                                $balance_qty  = $val['allocate_details']['qty'] - $val['allocate_details']['returned_qty'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $employee_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $employee_id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $date_of_join);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $designation);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $item_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $Store_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $allocated_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $retund_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $balance_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                $objPHPExcel->getActiveSheet()->setTitle('Uniform Allocation Report');
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
                $this->render('uniformallocation');
                break;
        }
    }
    
    private function GenerateAllocationreport($mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');


        $arr_keys = $this->EmpCtcTransaction->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
                                                    where item_part='Direct' Group by salary_head_item_desc
                                                    ORDER BY emp_salary_slip_pkey");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-d', strtotime($arr_form_data['reportto']));
//        $conditions = array();
        $conditions = "itm_allocation.date_allocated  between '$from' and '$otdate'  ";
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();
//        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        if(empty($arr_leavepolicygroupids)){
            echo "please choose Criteria";
            return false;
        }
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
               $arr_empleaverequests = $this->EmpCtcTransaction->query(" select itm_allocation.date_allocated AS date_allocated,employee_info.emp_pkey AS emp_pkey,employee_info.EmpName AS EmpName,
employee_info.employee_id AS employee_id,employee_info.branch AS branch,employee_info.designation AS designation,
employee_info.department AS department,employee_info.joining_date AS joining_date,store_master.store_code,store_master.store_location AS
store_location,item_master.item_desc AS item_desc,allocate_details.qty AS qty,allocate_details.returned_qty AS 
returned_qty,allocate_details.damaged_qty AS damaged_qty,additional_details.sales_price AS amount
from (((((allocate_details join itm_allocation on((itm_allocation.allocation_pkey = allocate_details.allocate_fkey))) 
join employee_info on((employee_info.emp_pkey = itm_allocation.emp_fkey))) join item_master 
on((item_master.item_master_pkey = allocate_details.item_purchase_fkey))) join store_master 
on((store_master.store_master_pkey = allocate_details.store_code))) join additional_details 
on((additional_details.item_master_fkey = allocate_details.item_purchase_fkey))) 
where itm_allocation.emp_fkey = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0) and $conditions and allocate_details.allocate_status = 0 
order by allocate_details.allocate_details_pkey desc ");
            }else{
                $arr_empleaverequests = $this->EmpCtcTransaction->query("select itm_allocation.date_allocated AS date_allocated,employee_info.emp_pkey AS emp_pkey,employee_info.EmpName AS EmpName,
employee_info.employee_id AS employee_id,employee_info.branch AS branch,employee_info.designation AS designation,
employee_info.department AS department,employee_info.joining_date AS joining_date,store_master.store_code,store_master.store_location AS
store_location,item_master.item_desc AS item_desc,allocate_details.qty AS qty,allocate_details.returned_qty AS 
returned_qty,allocate_details.damaged_qty AS damaged_qty,additional_details.sales_price AS amount
from (((((allocate_details join itm_allocation on((itm_allocation.allocation_pkey = allocate_details.allocate_fkey))) 
join employee_info on((employee_info.emp_pkey = itm_allocation.emp_fkey))) join item_master 
on((item_master.item_master_pkey = allocate_details.item_purchase_fkey))) join store_master 
on((store_master.store_master_pkey = allocate_details.store_code))) join additional_details 
on((additional_details.item_master_fkey = allocate_details.item_purchase_fkey))) 
where allocate_details.store_code = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0) and $conditions and allocate_details.allocate_status = 0 
order by allocate_details.allocate_details_pkey desc");
            }    
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
            
            
        }
        
//        debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->set('type', $arr_form_data['select-criteria1']);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('from', $from);
        $this->set('otdate', $otdate);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('uniformallocation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportAllocation.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Uniform Allocation Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'L'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getColumnDimension('L')->setWidth(20);


                $rowcount = 3;
                

                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                         if  ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                            $branch = isset($value['0']['employee_info']['EmpName']) ? "Allocation Report of ".$value['0']['employee_info']['EmpName'] : '';
                        } else {
                            $branch = isset($value['0']['store_master']['store_location']) ? "Allocation Report of ".$value['0']['store_master']['store_location'] : '';
                        }
                        

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Date Of Join');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Store');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Issued Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Issued Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Returned Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Balance Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Value');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
//                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $i;
//                                $PO_Status = $val['po']['grn_status'];
//                                        $string_po = '';
//                                        switch ($PO_Status){
//                                            case "0": $string_po = "GRN Received";
//                                                break;
//                                            case "1": $string_po = "PO Ordered";
//                                                break;
//                                            case "2": $string_po = "Finalised";
//                                                break;
//                                            default : $string_po = "PO";
//                                                break;
//                                        }
                                $employee_name = $val['employee_info']['EmpName'];
                                $employee_id =  $val['employee_info']['employee_id'];
                                $date_of_join = $val['employee_info']['joining_date'];
                                $branch = $val['employee_info']['branch'];
                                $department = $val['employee_info']['department'];
                                $designation = $val['employee_info']['designation'];
                                $item_name = $val['item_master']['item_desc'];
                                $Store_name = $val['store_master']['store_location'];
                                $allocated_qty = $val['allocate_details']['qty'];
                                $retund_qty = $val['allocate_details']['returned_qty'];
                                $balance_qty  = $val['allocate_details']['qty'] - $val['allocate_details']['returned_qty'];
                                $amount = round($val['additional_details']['amount'] * (($val['allocate_details']['qty'] - $val['allocate_details']['returned_qty'])));
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $employee_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $employee_id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $date_of_join);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $designation);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $item_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $Store_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $allocated_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $allocated_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $retund_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $balance_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $amount);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                $objPHPExcel->getActiveSheet()->setTitle('Uniform Allocation Report');
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
                $this->render('uniformallocation');
                break;
        }
    }
    //----------------------//
    private function GenerateUniformAllocationEMIReport($mode){
        $arr_form_data = $_REQUEST;
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $this->LoanEmi->useDbConfig = $this->Session->read('ds');


        $arr_keys = $this->item_allocate->query("SELECT trim(salary_head_item_desc) as sal_head,head_operator FROM emp_salary_slip as ectc
                                                    where item_part='Direct' Group by salary_head_item_desc
                                                    ORDER BY emp_salary_slip_pkey");
        $array_key = array();

        foreach ($arr_keys as $val) {

            if ($val['ectc']['head_operator'] == 'Addition') {
                $array_key['Addition'][] = $val[0]['sal_head'];
            } else {
                $array_key['Deduction'][] = $val[0]['sal_head'];
            }
        }

        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-d', strtotime($arr_form_data['reportto']));
        $this->set('from', $from);
        $this->set('otdate', $otdate);
//        $conditions = array();
        $conditions = "itm_allocation.date_allocated  between '$from' and '$otdate'  ";
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();
//        $id = implode(' AND ', $conditions);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
            $k = 0;
        $gross = array();
        if(empty($arr_leavepolicygroupids)){
            echo "please choose Criteria";
            return false;
        }
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
               $arr_empleaverequests = $this->item_allocate->query("SELECT itm_allocation.date_allocated,employee_info.*,allocate_details.*,store_master.store_location,item_master.item_desc FROM `allocate_details` join itm_allocation on (itm_allocation.allocation_pkey = allocate_details.allocate_fkey)
                                                                    join employee_info on (employee_info.emp_pkey = itm_allocation.emp_fkey)
                                                                    join item_master on (item_master.item_master_pkey = allocate_details.item_purchase_fkey)
                                                                    join store_master on (store_master.store_master_pkey = allocate_details.store_code)
                                                                    WHERE itm_allocation.emp_fkey = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0) and $conditions
                                                                    ORDER BY `allocate_details_pkey` DESC ");
                                                                    }else{
                $arr_empleaverequests = $this->item_allocate->query("SELECT itm_allocation.*,employee_info.*,allocate_details.*,store_master.store_location
                                                                    FROM `allocate_details` left join itm_allocation on (itm_allocation.allocation_pkey = allocate_details.allocate_fkey) 
                                                                    join employee_info on (employee_info.emp_pkey = itm_allocation.emp_fkey) 
                                                                     join store_master on (store_master.store_master_pkey = allocate_details.store_code) 
                                                                    WHERE allocate_details.store_code = '$leavepolicygroupid'  and ((allocate_details.qty - allocate_details.returned_qty - damaged_qty) > 0)  and $conditions
                                                                    GROUP BY `allocation_pkey`; ");

                                                                    $arr_emireport = array();
                                                                    foreach ($arr_empleaverequests as $value) {
                                                    //             debug($value);
                                                                            $loankey = isset($value['itm_allocation']['loan_fkey']) ? $value['itm_allocation']['loan_fkey'] : '0';
                                                                            $emiamount = $this->LoanEmi->query("SELECT `amt`FROM `emi_upload`WHERE `loan_pkey` = '$loankey' AND `status` = '1'");
                                                                            $emicount[] = $this->LoanEmi->query("SELECT COUNT(`loan_pkey`) FROM `emi_upload` WHERE `loan_pkey` = '$loankey' ");
                                                                            $value['emi'] = $emiamount;
                                                                            if(!empty($value))
                                                                            $arr_emireport[] = $value;
                                                                    }    
            $arr_stocksummary_for_template[] = $arr_emireport;
            
            
        }
        }      
        $maxemi  = max($emicount);
        $maxemicount = isset($maxemi['0']['0']['COUNT(`loan_pkey`)']) ? $maxemi['0']['0']['COUNT(`loan_pkey`)'] : 1;
//         debug($maxemicount);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->set('maxemicount', $maxemicount);
        $this->set('type', $arr_form_data['select-criteria1']);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('uniformemireport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportAllocationemi.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Uniform Allocation EMI Report - ".$from." - ".$otdate."");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'L'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:L1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getColumnDimension('L')->setWidth(20);


                $rowcount = 3;
                

                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                         if  ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                            $branch = isset($value['0']['employee_info']['EmpName']) ? "Uniform Allocation EMI Report of ".$value['0']['employee_info']['EmpName'] : '';
                        } else {
                            $branch = isset($value['0']['store_master']['store_location']) ? "Uniform Allocation EMI  Report of ".$value['0']['store_master']['store_location'] : '';
                        }
                        

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
                        $worksheet->mergeCells('A'.$rowcount.':L'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Date Of Join');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Store Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Issued Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Issued Qty');
//                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Total Value');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        for($i=1; $i <= $maxemicount ; $i++){
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9+$i) . $rowcount, $i.' EMI');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9+$i))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9+$i, $rowcount)->getFont()->setBold(true);
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9+$i) . $rowcount, 'Balance');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9+$i))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9+$i, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
                            $tot = 0;
                            $j = 1;
                            foreach ($arr_data as $val) {
//                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $j;
//                                $PO_Status = $val['po']['grn_status'];
//                                        $string_po = '';
//                                        switch ($PO_Status){
//                                            case "0": $string_po = "GRN Received";
//                                                break;
//                                            case "1": $string_po = "PO Ordered";
//                                                break;
//                                            case "2": $string_po = "Finalised";
//                                                break;
//                                            default : $string_po = "PO";
//                                                break;
//                                        }
                                $employee_name = $val['employee_info']['EmpName'];
                                $employee_id =  $val['employee_info']['employee_id'];
                                $date_of_join = $val['employee_info']['joining_date'];
                                $branch = $val['employee_info']['branch'];
                                $department = $val['employee_info']['department'];
                                $designation = $val['employee_info']['designation'];
                                $Store_name = $val['store_master']['store_location'];
                                $issueddate = $val['itm_allocation']['date_allocated'];
                                $allocated_qty = $val['allocate_details']['qty'];
                                $totalvalue = $val['itm_allocation']['value'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $employee_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $employee_id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $date_of_join);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $designation);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $Store_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $issueddate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $allocated_qty);
//                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $totalvalue);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $paid = 0;
                                for($i=0; $i < $maxemicount ; $i++ ) {
                                    $emi = isset($val['emi'][$i]['emi_upload']['amt']) ? $val['emi'][$i]['emi_upload']['amt'] : '0'; 
                                    $paid+=isset($val['emi'][$i]['emi_upload']['amt']) ? $val['emi'][$i]['emi_upload']['amt'] : '0';
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10+$i) . $rowcount, $emi);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10+$i))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10+$i) . $rowcount, $totalvalue -$paid);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10+$i))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $rowcount++;
                                $j++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                $objPHPExcel->getActiveSheet()->setTitle('Uniform Allocation Report');
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
                $this->render('uniformemireport');
                break;
        }
    }
    private function GeneratePoreport($mode){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
//        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//      
        $arr_stocksummary_for_template = array();
        if(!isset($arr_form_data['Store'])){
            echo "No Items Selected ";
            return false;
        }
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
            
            $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT sm.store_location,po.grn_status,pd.po_item_pkey,pd.po_fkey,im.item_code,im.item_desc,pd.ordering_qty,pd.required_qty,pd.po_rate,po.po_date,po.po_number,po.supplier_name,mr.mr_code FROM `po_item_details` pd left join purchase_order po on (po.po_pkey = pd.po_fkey) 
left join material_request mr on (mr.mr_pkey = pd.mr_fkey)
left join item_master im on (pd.item_code = im.item_master_pkey)
join store_master sm on (sm.store_master_pkey = mr.store_code)
where mr.store_code = '$val' and po_date between '$from' and '$to'  ");
            
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
            
            
            
        }
        //debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('salaryslip');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportpo.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Purchase Order Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 4;
                

                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['sm']['store_location']) ? "Store - ".$value['0']['sm']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'PO Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'PO Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Supplier Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'MR Number');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Ordering Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'PO Rate');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'PO Status');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
//                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $i;
                                $PO_Status = $val['po']['grn_status'];
                                        $string_po = '';
                                        switch ($PO_Status){
                                            case "0": $string_po = "GRN Received";
                                                break;
                                            case "1": $string_po = "PO Ordered";
                                                break;
                                            case "2": $string_po = "Finalised";
                                                break;
                                            default : $string_po = "PO";
                                                break;
                                        }
                                $po_number = $val['po']['po_number'];
                                $po_date = $val['po']['po_date'];
                                $supplier_name = $val['po']['supplier_name'];
                                $mr_number = $val['mr']['mr_code'];
                                $item_code = $val['im']['item_code'];
                                $item_deesc = $val['im']['item_desc'];
                                $ordering_qty = $val['pd']['ordering_qty'];
                                $po_rate = $val['pd']['po_rate'];
                                $grn_status  = $string_po;
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $po_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $po_date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $supplier_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $mr_number);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $item_deesc);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $ordering_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $po_rate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $grn_status);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                $objPHPExcel->getActiveSheet()->setTitle('Purchase Order  Report');
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
                $this->render('salaryslip');
                break;
        }
    }






    private function GenerateSummaryPayrolreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date("Y-m-d");
        $from = date('Y-m', strtotime($report_month));
//        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//      
        $arr_stocksummary_for_template = array();
        
        if(!isset($arr_form_data['Store'])){
        echo "No Items Selected ";
        return false; 
        }
        
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
            
//            $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct(item_fkey),stock_details.store_fkey,IFNULL((select sum(item_qty) from stock_details as st where item_fkey = stock_details.item_fkey and store_fkey = '$val' ),0) - IFNULL((select sum(qty) from  allocate_details where store_code = '$val' and status = '1'),0) as item_qty,
//(select item_code from item_master where item_master_pkey = stock_details.item_fkey) as item_code,
//(select item_desc from item_master where item_master_pkey = stock_details.item_fkey) as item_name from stock_details where store_fkey = '$val' " );
            $arr_empleaverequests = $this->EmpCtcTransaction->query("select iss.item_code,a.store_fkey,sr.store_location,a.item_fkey,iss.item_desc,sum(a.stock_qty) stock_qty,ifnull(sum(b.issue_qty),0)issue_qty,
 ifnull(sum(b.damaged_qty),0) damaged_qty, sum(a.stock_qty)-ifnull(sum(b.issue_qty),0) -  ifnull(sum(b.damaged_qty),0) balance_qty  from (
select st.store_fkey,item_fkey,sum(item_qty) stock_qty
 from stock_details as st
where  st.status = '1' and  st.store_fkey = $val
  group by st.store_fkey,item_fkey)a left join
(select item_purchase_fkey,store_code,sum(ais.qty) - ifnull(sum(ais.returned_qty),0) - ifnull(sum(ais.damaged_qty),0) issue_qty,ifnull(sum(ais.damaged_qty),0) damaged_qty
 from allocate_details ais
where allocate_status=0 and status=1
    group by item_purchase_fkey,store_code)b on (a.store_fkey=b.store_code
 and b.item_purchase_fkey=a.item_fkey)
inner join item_master iss on (iss.item_master_pkey = a.item_fkey)
 inner join store_master sr on (sr.store_master_pkey = a.store_fkey)
group by a.store_fkey,sr.store_location,a.item_fkey,iss.item_desc");
            
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
            
            
            
        }
//        debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('empsalary');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportsalary.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Stock Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 4;
                

                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['sr']['store_location']) ? "Store - ".$value['0']['sr']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Store Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $i;
                                $item_code = $val['iss']['item_code'];
                                $it_name = $val['iss']['item_desc'];
                                $store = $val['sr']['store_location'];
                                //  $branch = $val['month_year']. " ".$fromhalf;
                                $qty = $val['0']['balance_qty'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $it_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $store);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                $objPHPExcel->getActiveSheet()->setTitle('Stock Summary Report');
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
                $this->render('empsalary');
                break;
        }
    }
    
    private function GenerateStockreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
//        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
//      
        $arr_stocksummary_for_template = array();
        
        if(!isset($arr_form_data['Store'])){
            echo "No Criteria Selected ";
            return false;
        }
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
            

//            $arr_empleaverequests = $this->EmpCtcTransaction->query("select iss.item_code,a.store_fkey,sr.store_location,a.item_fkey,iss.item_desc,sum(a.stock_qty) stock_qty,ifnull(sum(b.issue_qty),0)issue_qty,sum(a.stock_qty)-ifnull(sum(b.issue_qty),0) balance_qty from (
//select st.store_fkey,item_fkey,sum(item_qty) stock_qty
// from stock_details as st
//where  st.status = '1' and  st.store_fkey = $val
//  group by st.store_fkey,item_fkey)a left join
//(select item_purchase_fkey,store_code,sum(ais.qty) - ifnull(sum(ais.returned_qty),0) issue_qty
// from allocate_details ais
//where allocate_status=0 and status=1
//    group by item_purchase_fkey,store_code)b on (a.store_fkey=b.store_code
// and b.item_purchase_fkey=a.item_fkey)
//inner join item_master iss on (iss.item_master_pkey = a.item_fkey)
// inner join store_master sr on (sr.store_master_pkey = a.store_fkey)
//group by a.store_fkey,sr.store_location,a.item_fkey,iss.item_desc");
            $arr_empleaverequests = $this->EmpCtcTransaction->query("select iss.item_code,a.store_fkey,sr.store_location,a.item_fkey,Prices,iss.item_desc,ifnull(sum(a.stock_qty),0) + ifnull(sum(c.stock_qty),0) - ifnull(sum(d.issue_qty),0) stock_qty,ifnull(sum(b.issue_qty),0)issue_qty, ifnull(sum(c.stock_qty),0) - ifnull(sum(d.issue_qty),0) cqtys ,
 ifnull(sum(b.damaged_qty),0) damaged_qty, (sum(a.stock_qty) + sum(c.stock_qty) - sum(d.issue_qty) )-ifnull(sum(b.issue_qty),0) -  ifnull(sum(b.damaged_qty),0) balance_qty  from (
select st.store_fkey,item_fkey,sum(item_qty) stock_qty , (SELECT `sales_price` FROM `additional_details` WHERE `item_master_fkey` = item_fkey AND `status` = '1') as Prices 
 from stock_details as st
where  st.status = '1' and  st.store_fkey = '$val' and po_no in ( select po_number from purchase_order Where po_pkey in (select po_fkey from gr_item_details where grn_fkey in (select grn_pkey from goods_receved_notes where gr_date between '$from' and '$to' )  ))
  group by st.store_fkey,item_fkey)a left join
(select item_purchase_fkey,store_code,sum(ais.qty) - ifnull(sum(ais.returned_qty),0) - ifnull(sum(ais.damaged_qty),0) issue_qty,ifnull(sum(ais.damaged_qty),0) damaged_qty
 from allocate_details ais
where allocate_status=0 and status=1 and store_code= '$val' and allocate_fkey in (select allocation_pkey from itm_allocation where date_allocated between '$from' and '$to')
    group by item_purchase_fkey,store_code)b on (a.store_fkey=b.store_code
 and b.item_purchase_fkey=a.item_fkey)
left join
	(select aiss.store_fkey,item_fkey,sum(aiss.item_qty) stock_qty
	 from stock_details aiss
	where aiss.status = '1' and  aiss.store_fkey = '$val' and po_no in ( select po_number from purchase_order Where po_pkey in (select po_fkey from gr_item_details where grn_fkey in (select grn_pkey from goods_receved_notes where gr_date < '$from' )  ))
		group by aiss.store_fkey,item_fkey)c on (a.store_fkey=b.store_code
	 and b.item_purchase_fkey=c.item_fkey)
 left join
(select item_purchase_fkey,store_code,sum(ais.qty) - ifnull(sum(ais.returned_qty),0) - ifnull(sum(ais.damaged_qty),0) issue_qty,ifnull(sum(ais.damaged_qty),0) damaged_qty
 from allocate_details ais
where allocate_status=0 and status=1 and store_code='$val' and allocate_fkey in (select allocation_pkey from itm_allocation where date_allocated < '$from')
    group by item_purchase_fkey,store_code)d on (a.store_fkey=d.store_code
 and d.item_purchase_fkey=a.item_fkey)
inner join item_master iss on (iss.item_master_pkey = a.item_fkey)
 inner join store_master sr on (sr.store_master_pkey = a.store_fkey)
group by a.store_fkey,sr.store_location,a.item_fkey,iss.item_desc union 
select iss.item_code,a.store_fkey,sr.store_location,a.item_fkey,Prices,iss.item_desc,ifnull(sum(a.stock_qty),0) + ifnull(sum(c.stock_qty),0) - ifnull(sum(d.issue_qty),0) stock_qty,ifnull(sum(b.issue_qty),0)issued_qty, ifnull(sum(c.stock_qty),0) - ifnull(sum(d.issue_qty),0) cqtys ,
 ifnull(sum(b.damaged_qty),0) damaged_qty, (sum(a.stock_qty) + sum(c.stock_qty) - sum(d.issue_qty) )-ifnull(sum(b.issue_qty),0) -  ifnull(sum(b.damaged_qty),0) balance_qty  from (
select st.store_fkey,item_fkey,sum(item_qty) stock_qty , (SELECT `sales_price` FROM `additional_details` WHERE `item_master_fkey` = item_fkey AND `status` = '1') as Prices 
 from stock_details as st
where  st.status = '1' and  st.store_fkey = '$val' and po_no not in ( select po_number from purchase_order Where po_pkey in (select po_fkey from gr_item_details where grn_fkey in (select grn_pkey from goods_receved_notes where gr_date between '$from' and '$to'  ) ))
  and item_fkey not in (select item_fkey from stock_details as st
where  st.status = '1' and  st.store_fkey = '$val' and po_no in ( select po_number from purchase_order Where po_pkey in (select po_fkey from gr_item_details where grn_fkey in (select grn_pkey from goods_receved_notes where gr_date between '$from' and '$to'  ) ))
  )  group by st.store_fkey,item_fkey)a 

left join
(select item_purchase_fkey,store_code,sum(ais.qty) - ifnull(sum(ais.returned_qty),0) - ifnull(sum(ais.damaged_qty),0) issue_qty,ifnull(sum(ais.damaged_qty),0) damaged_qty
 from allocate_details ais
where allocate_status=0 and status=1 and store_code= '$val' and allocate_fkey in (select allocation_pkey from itm_allocation where date_allocated between '2018-04-01' and '2018-12-01')
    group by item_purchase_fkey,store_code)b on (a.store_fkey=b.store_code
 and b.item_purchase_fkey=a.item_fkey)
join
	(select aiss.store_fkey,item_fkey,sum(aiss.item_qty) stock_qty
	 from stock_details aiss
	where aiss.status = '1' and  aiss.store_fkey = '$val' and po_no in ( select po_number from purchase_order Where po_pkey in (select po_fkey from gr_item_details where grn_fkey in (select grn_pkey from goods_receved_notes where gr_date < '$from' )  ))
		group by aiss.store_fkey,item_fkey)c on (a.store_fkey=b.store_code
	 and b.item_purchase_fkey=c.item_fkey)
 left join
(select item_purchase_fkey,store_code,sum(ais.qty) - ifnull(sum(ais.returned_qty),0) - ifnull(sum(ais.damaged_qty),0) issue_qty,ifnull(sum(ais.damaged_qty),0) damaged_qty
 from allocate_details ais
where allocate_status=0 and status=1 and store_code='$val' and allocate_fkey in (select allocation_pkey from itm_allocation where date_allocated < '$from')
    group by item_purchase_fkey,store_code)d on (a.store_fkey=d.store_code
 and d.item_purchase_fkey=a.item_fkey)
left join item_master iss on (iss.item_master_pkey = a.item_fkey)
 inner join store_master sr on (sr.store_master_pkey = a.store_fkey)
group by a.store_fkey,sr.store_location,a.item_fkey,iss.item_desc  ");
            
            $arr_stocksummary_for_template[] = $arr_empleaverequests;
            
            
            
        }
//        debug($arr_s  tocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('month', $from. ' - '.$to);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('stockdetails');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportstockdetails.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Stock Monthly Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 3;
                

                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $branch = isset($value['0']['0']['store_location']) ? "Store - ".$value['0']['0']['store_location'] : '';

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        
                        $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount);
//                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                        );
                        
                        $rowcount = $rowcount + 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Item Code');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Item Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Opening Stock');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Stock Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Issued Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Damaged Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Balance Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Sales Rate');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Balance Qty');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                        
                        $rowcount = $rowcount + 1;
                        $arr_data  = $value;
                        if(count($arr_data)>=0){ $sum = 0; 
                            $tot = 0;
                            $i = 1;
                            foreach ($arr_data as $val) {
                                $sum += $val['0']['balance_qty']; 
                                

                                $slno = $i;
                                $item_code = $val['0']['item_code'];
                                $it_name = $val['0']['item_desc'];
                                $opening_stock = $val['0']['cqtys'];
                                $stock_qty = $val['0']['stock_qty'] - $val['0']['cqtys'];
                                $Issued_qty = $val['0']['issue_qty'];
                                $damaged_qty = $val['0']['damaged_qty'];
                                $balance_qty = $val['0']['balance_qty'];
                                $rate = $val['0']['Prices'];
                                $sum = round($val['0']['Prices'] * $val['0']['balance_qty']);
                                //  $branch = $val['month_year']. " ".$fromhalf;
                                $qty = $val['0']['balance_qty'];
                                
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $item_code);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $it_name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $opening_stock);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $stock_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $Issued_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $damaged_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $balance_qty);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $rate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $sum);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                
                                $rowcount++;
                                $i++;
                            }
                        } else {

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "No records Found");
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        }
                    }
                }//end foreach
                $objPHPExcel->getActiveSheet()->setTitle('Stock Monthly Report');
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
                $this->render('stockdetails');
                break;
        }
    }

    //summary report



    private function GenerateSalarySlipreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
//        $report_month = $arr_form_data['reportfrom'];
        if(isset($arr_form_data['reportfrom']))
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));
//        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//      
        $arr_stocksummary_for_template = array();
        
        if(!isset($arr_form_data['Store']))
            return false;
        $arr_store = $arr_form_data['Store'];
        
        foreach ($arr_store as $val){
            $arr_mr_details = array();
            $arr_empleaverequests = $this->EmpCtcTransaction->query("SELECT material_request.*,sm.store_location FROM material_request join store_master sm on (sm.store_master_pkey = material_request.store_code) where material_request.status = '1' and material_request.store_code = $val  and mr_date between '$from' and '$to'");
            
            $arr_material_details = array();
            
            foreach ($arr_empleaverequests as $value){
                
                $mr_fkey = isset($value['material_request']['mr_pkey'])?$value['material_request']['mr_pkey']:0;
//                debug($mr_fkey);
                $arr_mr_details = $this->EmpCtcTransaction->query("select mr_details.mr_fkey,item_master.item_code,mr_details.required_qty,mr_details.required_qty,mr_details.mr_fkey,item_master.item_desc from mr_details
                    join item_master on (item_master.item_master_pkey = mr_details.item_code) where mr_fkey = $mr_fkey ");
               $value['Items'] = $arr_mr_details;
//               debug($arr_mr_details);
               $arr_material_details[] = $value;
            }
            
            $arr_stocksummary_for_template[] = $arr_material_details;
            
            
            
        }
//        debug($arr_stocksummary_for_template);
        
        
        //debug($needBranchWiseReport);
        // debug($arr_leavesummary_for_template);
        $this->set('arr_stocksummary_for_template', $arr_stocksummary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                // echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('material');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('report.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "reportmaterial.xlsx" : "reportsalary" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Material Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:G1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $rowcount = 2;
                
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $active = 0;
                foreach ($arr_stocksummary_for_template as $value) {
                    if (count($value) !== 0) {
                        $rowcount++;
                        $i = 0;
                        $branch = isset($value['0']['sm']['store_location']) ? "Store - " . $value['0']['sm']['store_location'] : '';
                        
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $branch);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $rowcount++;
                        
                        $arr_mr = $value;
                        
                            
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, "SI Number");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                            
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, "MR Code");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                            
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, "MR Date");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                            
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, "Item Code");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                                                        
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, "Item Name");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                            
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, "Customer");
//                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                                                     
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, "Qty");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                            
                        foreach ($arr_mr as $val) {
                            
                            $mr_code = isset($val['material_request']['mr_code']) ? $val['material_request']['mr_code'] : '';
                            $mr_date = isset($val['material_request']['mr_date']) ? $val['material_request']['mr_date']:'';
                            $cutomer = isset($val['material_request']['customer_name']) ? $val['material_request']['customer_name'] : '';
                            $po_status = isset($val['material_request']['po_status']) ? $val['material_request']['po_status'] : '';
                            
                            $arr_data  = $val['Items'];
                            if(count($arr_data)>=0){
                                 $sum = 0; 
                                foreach($arr_data as $v){
                                    $rowcount++;
                                    $i++;
                                    $sum+=$v['mr_details']['required_qty'];
                                    $item_code = $v['item_master']['item_code'];
                                    $item_desc = $v['item_master']['item_desc'];
                                    $customer = $v['mr_details']['required_qty'];
                                    $qty = $v['mr_details']['required_qty'];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $mr_code);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $mr_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $item_code);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $item_desc);
//                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $customer);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $qty);
                                }
                            }
                            
                                
                        }
                    }
                }//end foreach
                $objPHPExcel->getActiveSheet()->setTitle('Material Report');
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
                $this->render('material');
                break;
        }
    }

    public function getprodataDesc($id, $date) {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $arr_pro_data = $this->EmpCtcTransaction->query("select distinct(prorate_code)  "
                . "from emp_salary_structure as ectc "
                . " where ectc.emp_fkey = '$id' "
                . "and end_date_effective is null ");
        $prorate_code = isset($arr_pro_data[0]['ectc']['prorate_code']) ? $arr_pro_data[0]['ectc']['prorate_code'] : '1'; //set default as calender days
        $year = date('Y', strtotime($date));
        $month = $from = date('m', strtotime($date));
        $day = array();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $loparray = $this->EmployeeDetails->query("SELECT loss_of_pay FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
        $lop = isset($loparray[0]['payroll_master']['loss_of_pay']) ? $loparray[0]['payroll_master']['loss_of_pay'] : 0;
        if ($prorate_code == '1') {
            $day['type'] = "Calender Days";
            $day['days'] = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        } else if ($prorate_code == '2') {
            $day['type'] = "Working Days";


            $holiday = $this->EmployeeDetails->query("SELECT count(ho.HOLIDAYID) as count FROM emp_proff as ep left join  holidays as ho on(ho.HOLIDAY_GROUP_ID=ep.HOLIDAY_GROUP_ID) where month(ho.HOLIDAYDATE)='$month' and year(ho.HOLIDAYDATE)='$year' and ep.emp_fkey='$id'");
            $weekoff = $this->EmployeeDetails->query("SELECT week_off_days FROM payroll_master WHERE emp_fkey = '$id' AND month_year = '$date'");
            // debug($holiday);die();
            $holiday_count = isset($holiday[0][0]['count']) ? $holiday[0][0]['count'] : 0;
            $weekoffdays = isset($weekoff[0]['payroll_master']['week_off_days']) ? $weekoff[0]['payroll_master']['week_off_days'] : 0;
            $day_count = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $day['days'] = $day_count - $weekoffdays - $holiday_count;
        } else {
            $day['type'] = "Fixed Days";
            $day['days'] = 30;
        }
        $day['present'] = $day['days'] - $lop;
        return $day;
    }

}
