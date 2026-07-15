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
ini_set('max_execution_time', 300);

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class EventReportsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EventReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails','Departments','Units', 'ReportCriterias','DbConfig','CompanyContactInfo','Branches', 'EmployeeProfessionalDetails', 'Designation','ReportAudit','Types','user_credentials');
    public $components = array('MasterdataManagement');

    public function hrreports() {
        $arr_reporttypes = array(
//drop down            
            //'employee' => 'Employee Information',
            'Events' => 'Events'
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '') {
        $this->autoRender = FALSE;
         $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        // debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
               case 'Events':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type), 'order' => 'reportcriteria_desc'))));
                    break;
//                case 'Events':
//                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
//                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type,)))));
//                    break;
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
            //$this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type)))));
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type, 'order' => 'reportcriteria')))));
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
            if ($this->_modelExists($model)|| $model == 'Types') {
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Units') ? 'Branches' : $model;
                $model = ($model == 'Types') ? 'Types' : $model;
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
    $arr_requestdata = $this->request->data;
    $arr_criteriaItems = array();

    if (isset($model) && $model != '') {
        // Set the datasource configuration for the model
        $this->{$model}->useDbConfig = $this->Session->read('ds');

        // Define conditions based on the model
        if ($model == 'DayTimeProcedures') {
            $conditions = array("active" => 1);
        } else {
            $conditions = array("status" => 1);
        }

        // Special handling for the Types model
        if ($model == 'Types') {
            // Assuming Types is a static array or predefined data
           // $arr_criteriaItemsDB = array('Birthday', 'Anniversary', 'Probation');
            $arr_criteriaItemsDB = array('Anniversary', 'Birthday', 'Probation');
        } else {
            // Fetch data from the database for other models
            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
        }

        // Process the fetched data
        $key = 0;
        switch ($model) {
            case 'Units':
                foreach ($arr_criteriaItemsDB as $value) {
                    $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                    $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                    $key++;
                }
                break;

            case 'EmployeeDetails':
                // Join and fetch employee details
                $fields = 'emp_pkey, status, EmployeeProfessionalDetails.emp_company_id, 
                           CONCAT(first_name, " ", IFNULL(last_name, "."), " - ", EmployeeProfessionalDetails.emp_company_id) as name, 
                           EmployeeProfessionalDetails.designation, EmployeeProfessionalDetails.joining_date, mobile_no';
                $joins = array(
                    array(
                        'table' => 'emp_proff',
                        'alias' => 'EmployeeProfessionalDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                    )
                );
                $conditions = array("status" => 1);
                if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                    $conditions = array("status IN (1, 2)");
                }
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $arr_emp = $this->EmployeeDetails->find("all", array(
                    'fields' => $fields,
                    'joins' => $joins,
                    'conditions' => $conditions,
                    "order" => array("EmployeeDetails.first_name" => "ASC")
                ));
                foreach ($arr_emp as $value) {
                    $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                    $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                    $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                    $key++;
                }
                break;

            case 'Types':
                // Process the static array for Types
                foreach ($arr_criteriaItemsDB as $value) {
                    $arr_criteriaItems[$key]['key'] = strtolower($value);
                    $arr_criteriaItems[$key]['text'] = $value;
                    $key++;
                }
                break;

            default:
                foreach ($arr_criteriaItemsDB as $value) {
                    $arr_criteriaItems[$key]['key'] = $value[$model]['id'];
                    $arr_criteriaItems[$key]['text'] = $value[$model]['name'];
                    $key++;
                }
                break;
        }
    }

    // Output the results
    echo json_encode($arr_criteriaItems);
}

     public function reportAudit($type, $mode) {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Events':
                $dataForHistory['report_type'] = "Events";
                break;
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
                case 'EmployeeDetails': $criteria_name_array[] = 'belonging to an Employee';
                    break;
                case 'Units': $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'Types': $criteria_name_array[] = 'belonging to a Type';
                    break;
            }
            $items_array[] = isset($arr_form_data[$criteria]) ? implode(",", $arr_form_data[$criteria]) : '';
            $items_count_array[] = isset($arr_form_data[$criteria]) ? count($arr_form_data[$criteria]) : 0;

            $i++;
        }

        $dataForHistory['criteria'] = implode(",", $criteria_array);
        $dataForHistory['criteria_name'] = implode(",", $criteria_name_array);
        $dataForHistory['items'] = implode(",", $items_array);
        $dataForHistory['items_count'] = implode(",", $items_count_array);
//debug($dataForHistory);exit;

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

        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }

//function for dropdown
    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;
//debug($mode);
        switch ($type) {
            case 'Events':
                $this->generateemployeeevents($mode);
                break;
            default:
                return false;
                break;
        }
           $this->reportAudit($type, $mode);                                 
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

    

    private function generateemployeeevents($mode) {
        ini_set('memory_limit', '512M');
        $arr_form_data = $_REQUEST;
        //  debug($arr_form_data);
        //$this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'];
        $dateTime = DateTime::createFromFormat('Y-m', $fd);
        // Extract the month part
        $monthcon = $dateTime->format('m');
        $currentYear=$dateTime->format('Y');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time=strtotime($f);
        $month=date("m",$time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month.'-01'; 
        $year=date("Y",$time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        
         $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
       // debug($fd);
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
//        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
//            $report_month = $arr_form_data['reportfrom'];
//            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
//            $to = date('Y-m-t H:m:s', strtotime($arr_form_data['reportfrom']. ' ' . '23:00:00' ));
//            $month = date('M - Y', strtotime($from));
//            $this->set('month',$month);
//        }
         $criterias = $arr_form_data['select-criteria1'];

 

      //  debug($fd);
        $arr_leavepolicygroupids = array();
        $arr_leavepolicy_detailsanniversary=array();
        $arr_leavepolicy_detailsprobation=array();
        $arr_leavepolicy_detailsbirthday=array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;
       
             if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            }

          $condition = 'and  emp_details.status = 1';
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition =  "and  emp_details.status in('1','2')";
           
        }
        $arr_leavepolicydetails_for_template = array();
      
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    //                   
																			
				
//    $arr_leavepolicy_detailsbirthday = $this->EmployeeDetails->query(
//    "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.designation,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2  FROM emp_details "
//    . "LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_details.emp_pkey) "
//    . "LEFT JOIN employee_info ON (employee_info.emp_pkey = emp_proff.emp_fkey) "
//    ."LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1) " 
//    . "LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)"      
//    . "LEFT JOIN branches ON (branches.branch_code = emp_details.branch_code) "
//    . "WHERE emp_details.emp_pkey = '$leavepolicygroupid' "
//    . "AND MONTH(emp_details.date_of_birth) = '$monthcon' AND YEAR(emp_details.date_of_birth) < '$currentYear' $condition ORDER BY emp_details.first_name, emp_details.last_name"
//);
// $arr_leavepolicy_detailsanniversary = $this->EmployeeDetails->query(
//    "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.designation,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2  FROM emp_details "
//    . "LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_details.emp_pkey) "
//    . "LEFT JOIN employee_info ON (employee_info.emp_pkey = emp_proff.emp_fkey) " 
//    . "LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1) "  
//    . "LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)"  
//    . "LEFT JOIN branches ON (branches.branch_code = emp_details.branch_code) "
//    . "WHERE emp_details.emp_pkey = '$leavepolicygroupid' "
//    . "AND MONTH(emp_proff.joining_date) = '$monthcon' AND YEAR(emp_proff.joining_date) < '$currentYear' $condition ORDER BY emp_details.first_name, emp_details.last_name" 
//);  
// $arr_leavepolicy_detailsprobation = $this->EmployeeDetails->query(
//    "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.designation,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2  FROM emp_details "
//    . "LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_details.emp_pkey) "
//    . "LEFT JOIN employee_info ON (employee_info.emp_pkey = emp_proff.emp_fkey) "
//    . "LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1) "
//    . "LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)"     
//    . "LEFT JOIN branches ON (branches.branch_code = emp_details.branch_code) "
//    . "WHERE emp_details.emp_pkey = '$leavepolicygroupid' "
//    . "AND MONTH(emp_proff.joining_date) < '$monthcon' AND emp_proff.emp_type = 'Probation' $condition ORDER BY emp_details.first_name, emp_details.last_name"
//);  
     $query = "
    SELECT 
        user_credentials.user_id,
        termination.last_approved_working_date,
        employee_info.department,
        employee_info.employee_id,
        employee_info.emp_id,
        emp_details.status,
        emp_details.emp_id,
        emp_proff.emp_company_id,
        emp_details.first_name,
        emp_details.last_name,
        branches.branch_name,
        desg.desig_name,
        emp_proff.joining_date,
        emp_details.date_of_birth,
        emp_proff.emp_type,
        emp_proff.attr2,
        'Birthday' AS event_type
    FROM emp_details
    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey
    LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
    LEFT JOIN termination ON termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1
    LEFT JOIN user_credentials ON user_credentials.emp_fkey = employee_info.emp_pkey
    LEFT JOIN branches ON branches.branch_code = emp_details.branch_code
    LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
    WHERE emp_details.emp_pkey = '$leavepolicygroupid'
        AND MONTH(emp_details.date_of_birth) = '$monthcon'
        AND YEAR(emp_details.date_of_birth) < '$currentYear'
        $condition

    UNION ALL

    SELECT 
        user_credentials.user_id,
        termination.last_approved_working_date,
        employee_info.department,
        employee_info.employee_id,
        employee_info.emp_id,
        emp_details.status,
        emp_details.emp_id,
        emp_proff.emp_company_id,
        emp_details.first_name,
        emp_details.last_name,
        branches.branch_name,
        desg.desig_name,
        emp_proff.joining_date,
        emp_details.date_of_birth,
        emp_proff.emp_type,
        emp_proff.attr2,
        'Anniversary' AS event_type
    FROM emp_details
    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey
    LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
    LEFT JOIN termination ON termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1
    LEFT JOIN user_credentials ON user_credentials.emp_fkey = employee_info.emp_pkey
    LEFT JOIN branches ON branches.branch_code = emp_details.branch_code
    LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
    WHERE emp_details.emp_pkey = '$leavepolicygroupid'
        AND MONTH(emp_proff.joining_date) = '$monthcon'
        AND YEAR(emp_proff.joining_date) < '$currentYear'
        $condition

    UNION ALL

    SELECT 
        user_credentials.user_id,
        termination.last_approved_working_date,
        employee_info.department,
        employee_info.employee_id,
        employee_info.emp_id,
        emp_details.status,
        emp_details.emp_id,
        emp_proff.emp_company_id,
        emp_details.first_name,
        emp_details.last_name,
        branches.branch_name,
        desg.desig_name,
        emp_proff.joining_date,
        emp_details.date_of_birth,
        emp_proff.emp_type,
        emp_proff.attr2,
        'Probation' AS event_type
    FROM emp_details
    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey
    LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
    LEFT JOIN termination ON termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1
    LEFT JOIN user_credentials ON user_credentials.emp_fkey = employee_info.emp_pkey
    LEFT JOIN branches ON branches.branch_code = emp_details.branch_code
    LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
    WHERE emp_details.emp_pkey = '$leavepolicygroupid'
        AND MONTH(DATE_ADD(emp_proff.joining_date, INTERVAL emp_proff.attr2 DAY)) = '$monthcon'
        AND emp_proff.emp_type = 'Probation'
        $condition
";

$arr_leavepolicy_details = $this->EmployeeDetails->query($query);
     

                 //   debug($arr_leavepolicy_detailsanniversary); 
 
                   
                } else   if ($arr_form_data['select-criteria1'] == 'Units'){
//                
//             $arr_leavepolicy_detailsbirthday = $this->EmployeeDetails->query(
//    "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.designation,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2  FROM emp_details "
//    . "LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_details.emp_pkey) "
//    . "LEFT JOIN employee_info ON (employee_info.emp_pkey = emp_proff.emp_fkey) " 
//    . "LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1) " 
//    . "LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)"                
//    . "LEFT JOIN branches ON (branches.branch_code = emp_details.branch_code) "
//    . "WHERE branches.branch_code = '$leavepolicygroupid' "
//    . "AND MONTH(emp_details.date_of_birth) = '$monthcon' AND YEAR(emp_details.date_of_birth) < '$currentYear' $condition  ORDER BY branches.branch_name, emp_details.first_name, emp_details.last_name"
//);
// $arr_leavepolicy_detailsanniversary = $this->EmployeeDetails->query(
//    "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.designation,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2  FROM emp_details "
//    . "LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_details.emp_pkey) "
//    . "LEFT JOIN employee_info ON (employee_info.emp_pkey = emp_proff.emp_fkey) "  
//    . "LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1) " 
//    . "LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)"   
//    . "LEFT JOIN branches ON (branches.branch_code = emp_details.branch_code) "
//    . "WHERE branches.branch_code = '$leavepolicygroupid'  "
//    . "AND MONTH(emp_proff.joining_date) = '$monthcon' AND YEAR(emp_proff.joining_date) < '$currentYear' $condition  ORDER BY branches.branch_name, emp_details.first_name, emp_details.last_name "
//);  
// $arr_leavepolicy_detailsprobation = $this->EmployeeDetails->query(
//    "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.designation,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2  FROM emp_details "
//    . "LEFT JOIN emp_proff ON (emp_proff.emp_fkey = emp_details.emp_pkey) "
//    . "LEFT JOIN employee_info ON (employee_info.emp_pkey = emp_proff.emp_fkey) " 
//    . "LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1) "
//    . "LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)"     
//    . "LEFT JOIN branches ON (branches.branch_code = emp_details.branch_code) "
//    . "WHERE branches.branch_code = '$leavepolicygroupid'  " 
//    . "AND MONTH(emp_proff.joining_date) < '$monthcon' AND emp_proff.emp_type = 'Probation' $condition  ORDER BY branches.branch_name, emp_details.first_name, emp_details.last_name"
//);  
//$query = "
//    SELECT 
//        user_credentials.user_id, 
//        termination.last_approved_working_date, 
//        employee_info.department, 
//        employee_info.employee_id, 
//        employee_info.emp_id, 
//        emp_details.status, 
//        emp_details.emp_id, 
//        emp_proff.emp_company_id, 
//        emp_details.first_name, 
//        emp_details.last_name, 
//        branches.branch_name, 
//        emp_proff.designation, 
//        emp_proff.joining_date, 
//        emp_details.date_of_birth, 
//        emp_proff.emp_type, 
//        emp_proff.attr2,
//        CASE
//            WHEN MONTH(emp_details.date_of_birth) = '$monthcon' THEN 'Birthday'
//            WHEN MONTH(emp_proff.joining_date) = '$monthcon' THEN 'Anniversary'
//            WHEN MONTH(emp_proff.joining_date) < '$monthcon' AND emp_proff.emp_type = 'Probation' THEN 'Probation'
//            ELSE ''
//        END AS event_type
//    FROM emp_details
//    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey
//    LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
//    LEFT JOIN termination ON termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1
//    LEFT JOIN user_credentials ON user_credentials.emp_fkey = employee_info.emp_pkey
//    LEFT JOIN branches ON branches.branch_code = emp_details.branch_code
//    WHERE branches.branch_code = '$leavepolicygroupid'
//    AND (
//        (MONTH(emp_details.date_of_birth) = '$monthcon' AND YEAR(emp_details.date_of_birth) < '$currentYear' $condition)
//        OR (MONTH(emp_proff.joining_date) = '$monthcon' AND YEAR(emp_proff.joining_date) < '$currentYear' $condition)
//        OR (MONTH(emp_proff.joining_date) < '$monthcon' AND emp_proff.emp_type = 'Probation' $condition)
//    )
//    ORDER BY branch_name, first_name, last_name
//";
$query = "
    SELECT 
        user_credentials.user_id, 
        termination.last_approved_working_date, 
        employee_info.department, 
        employee_info.employee_id, 
        employee_info.emp_id, 
        emp_details.status, 
        emp_details.emp_id, 
        emp_proff.emp_company_id, 
        emp_details.first_name, 
        emp_details.last_name, 
        branches.branch_name, 
        desg.desig_name, 
        emp_proff.joining_date, 
        emp_details.date_of_birth, 
        emp_proff.emp_type, 
        emp_proff.attr2,
        'Birthday' AS event_type
    FROM emp_details
    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey
    LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
    LEFT JOIN termination ON termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1
    LEFT JOIN user_credentials ON user_credentials.emp_fkey = employee_info.emp_pkey
    LEFT JOIN branches ON branches.branch_code = emp_details.branch_code
    LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
    WHERE branches.branch_code = '$leavepolicygroupid'
    AND MONTH(emp_details.date_of_birth) = '$monthcon' 
    AND YEAR(emp_details.date_of_birth) < '$currentYear' $condition

    UNION ALL

    SELECT 
        user_credentials.user_id, 
        termination.last_approved_working_date, 
        employee_info.department, 
        employee_info.employee_id, 
        employee_info.emp_id, 
        emp_details.status, 
        emp_details.emp_id, 
        emp_proff.emp_company_id, 
        emp_details.first_name, 
        emp_details.last_name, 
        branches.branch_name, 
        desg.desig_name, 
        emp_proff.joining_date, 
        emp_details.date_of_birth, 
        emp_proff.emp_type, 
        emp_proff.attr2,
        'Anniversary' AS event_type
    FROM emp_details
    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey
    LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
    LEFT JOIN termination ON termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1
    LEFT JOIN user_credentials ON user_credentials.emp_fkey = employee_info.emp_pkey
    LEFT JOIN branches ON branches.branch_code = emp_details.branch_code
    LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
    WHERE branches.branch_code = '$leavepolicygroupid'
    AND MONTH(emp_proff.joining_date) = '$monthcon' 
    AND YEAR(emp_proff.joining_date) < '$currentYear' $condition

    UNION ALL

    SELECT 
        user_credentials.user_id, 
        termination.last_approved_working_date, 
        employee_info.department, 
        employee_info.employee_id, 
        employee_info.emp_id, 
        emp_details.status, 
        emp_details.emp_id, 
        emp_proff.emp_company_id, 
        emp_details.first_name, 
        emp_details.last_name, 
        branches.branch_name, 
        desg.desig_name, 
        emp_proff.joining_date, 
        emp_details.date_of_birth, 
        emp_proff.emp_type, 
        emp_proff.attr2,
        'Probation' AS event_type
    FROM emp_details
    LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey
    LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
    LEFT JOIN termination ON termination.emp_fkey = employee_info.emp_pkey AND termination.status = 1
    LEFT JOIN user_credentials ON user_credentials.emp_fkey = employee_info.emp_pkey
    LEFT JOIN branches ON branches.branch_code = emp_details.branch_code
    LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
    WHERE branches.branch_code = '$leavepolicygroupid'
   AND MONTH(DATE_ADD(emp_proff.joining_date, INTERVAL emp_proff.attr2 DAY)) = '$monthcon'
    AND emp_proff.emp_type = 'Probation' $condition
    ORDER BY branch_name, first_name, last_name
";

// Log the query for debugging
//error_log($query);

// Execute the combined query
$arr_leavepolicy_details = $this->EmployeeDetails->query($query);

// Debug output to ensure data is being returned
//debug($arr_leavepolicy_details);




 
                  
                    
                }else {
         //  DEBUG($leavepolicygroupid);
                    $monthcon = intval($monthcon); 
	            if($leavepolicygroupid=='birthday'){
                                   
                 // Ensure $monthcon is treated as an integer
                $arr_leavepolicy_detailsbirthday = $this->EmployeeDetails->query(
                    "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,desg.desig_name,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2 
                     FROM emp_details 
                     LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey 
                     LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
                     LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1) 
                     LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)
                     LEFT JOIN branches ON branches.branch_code = emp_details.branch_code 
                     LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
                     WHERE MONTH(emp_details.date_of_birth) = '$monthcon' AND YEAR(emp_details.date_of_birth) < '$currentYear' $condition ORDER BY emp_details.first_name, emp_details.last_name"
                   );
                }
               if($leavepolicygroupid=='anniversary'){
                       $arr_leavepolicy_detailsanniversary = $this->EmployeeDetails->query(
                       "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,desg.desig_name,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2  
                        FROM emp_details 
                        LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey 
                        LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey
                        LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1)
                        LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)
                        LEFT JOIN branches ON branches.branch_code = emp_details.branch_code 
                        LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
                        WHERE MONTH(emp_proff.joining_date) = '$monthcon' AND YEAR(emp_proff.joining_date) < '$currentYear' $condition ORDER BY emp_details.first_name, emp_details.last_name"
                      );
 
 //debug($arr_leavepolicy_detailsanniversary);
               }
               if($leavepolicygroupid=='probation'){
                   $arr_leavepolicy_detailsprobation = $this->EmployeeDetails->query(
                     "SELECT user_credentials.user_id,termination.last_approved_working_date,employee_info.department,employee_info.employee_id ,employee_info.emp_id,emp_details.status,emp_details.emp_id,emp_proff.emp_company_id,emp_details.first_name,emp_details.last_name,branches.branch_name,desg.desig_name,emp_proff.designation,emp_proff.joining_date,emp_details.date_of_birth,emp_proff.emp_type,emp_proff.attr2  
                      FROM emp_details 
                      LEFT JOIN emp_proff ON emp_proff.emp_fkey = emp_details.emp_pkey 
                      LEFT JOIN employee_info ON employee_info.emp_pkey = emp_proff.emp_fkey 
                      LEFT JOIN termination as termination on (termination.emp_fkey = employee_info.emp_pkey and termination.status=1)
                      LEFT JOIN user_credentials as user_credentials on (user_credentials.emp_fkey = employee_info.emp_pkey)
                      LEFT JOIN branches ON branches.branch_code = emp_details.branch_code 
                      LEFT JOIN designation as desg on (desg.desig_code = emp_proff.designation)
                      WHERE MONTH(DATE_ADD(emp_proff.joining_date, INTERVAL emp_proff.attr2 DAY)) = '$monthcon' AND emp_proff.emp_type = 'Probation' $condition ORDER BY emp_details.first_name, emp_details.last_name"
                    );
 
               }
                
               if (!empty($arr_leavepolicy_detailsbirthday)) {
                      $arr_leavepolicydetails_for_template['summary'] = $arr_leavepolicy_detailsbirthday;
                }
                if (!empty($arr_leavepolicy_detailsanniversary)) {
                    $arr_leavepolicydetails_for_template['workanniversary'] = $arr_leavepolicy_detailsanniversary;
                }
                if (!empty($arr_leavepolicy_detailsprobation)) {
                 $arr_leavepolicydetails_for_template['probation'] = $arr_leavepolicy_detailsprobation;
                }


                } 

           if ($arr_form_data['select-criteria1'] == 'Units'||$arr_form_data['select-criteria1'] == 'EmployeeDetails'){
              //if(!empty($arr_leavepolicy_detailsbirthday)||!empty($arr_leavepolicy_detailsanniversary)||!empty($arr_leavepolicy_detailsprobation)){
              if(!empty($arr_leavepolicy_details)){
//                $arr_leavepolicydetails_for_template[] = array(
//                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
//                    'summary' => $arr_leavepolicy_detailsbirthday,
//                    'workanniversary'=>$arr_leavepolicy_detailsanniversary,
//                    'probation'=>$arr_leavepolicy_detailsprobation
//                    );
                  
                  $arr_leavepolicydetails_for_template[] = array(
//                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                  'summary' => $arr_leavepolicy_details,
                 
                  );
                  
                } 
           }
            }
          //  debug($arr_leavepolicydetails_for_template);
       //  debug($arr_leavepolicydetails_for_template);
//            $arr_dates = array();
           //debug($arr_dates);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($this_month_att);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
         date_default_timezone_set("Asia/Calcutta");
         $arr_date = date('d-m-Y H:i');
         $this->set('arr_date', $arr_date);
        $this->set('criterias', $arr_form_data['select-criteria1']);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('timeattendancereports');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('eventreports.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Events".$fd.".xlsx" : "Events" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
                
                $worksheet->setCellValueByColumnAndRow(0, 1, "Events- ".$mname."  "  .$year);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                for ($col = 'A'; $col !== 'M'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
            
               
            $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
            $worksheet->mergeCells('A2:J2');
            $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
             $rowcount = 3;
             if (empty($arr_leavepolicydetails_for_template)){
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No data available under the selected criteria');
                   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true); 
                   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setSize(11);
                   $worksheet->mergeCells('A'.$rowcount.':I'.$rowcount.'');
                   $worksheet->getStyle('A'.$rowcount.'')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                }else{
                        $objPHPExcel->getActiveSheet()->freezePane('D4');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'User ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Branch'); 
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Designation'); 
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Department'); 
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Date of Joining');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), 'Termination');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), 'Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), 'Events/Probation End Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, ($rowcount))->getFont()->setBold(true);
                      
                        $rowcount = $rowcount + 1;
                $i=1;
                if($criterias == 'Types'){
                                if(isset($arr_leavepolicydetails_for_template['summary'])){
                                 $arr_daata  = $arr_leavepolicydetails_for_template['summary']; 
                                }
                                 if(isset($arr_leavepolicydetails_for_template['workanniversary'])){
                                 $arr_work=$arr_leavepolicydetails_for_template['workanniversary'];
                                 }
                                 if(isset($arr_leavepolicydetails_for_template['probation'])){
                                 $arr_prob=$arr_leavepolicydetails_for_template['probation'];
                                 }
                                  if(isset($arr_work)){
                  foreach ($arr_work as $employee =>$val)
                        {
                        $empid = $val['employee_info']['employee_id'];
                        $companyid = $val['user_credentials']['user_id'];
                        $department=$val['employee_info']['department'];
                        $termin= isset($val['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($val['termination']['last_approved_working_date'])) : '';
                        $empstatus = (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : '';
                        $emp_name = $val['emp_details']['first_name'].$val['emp_details']['last_name'].$empstatus;
                        $branch = $val['branches']['branch_name'];
                        $desig=$val['desg']['desig_name'];
                        $join = date('d-m-Y', strtotime($val['emp_proff']['joining_date']));
                        if(!empty($val['emp_proff']['joining_date'])){  $type= 'Anniversary';}   //edited by ASHIN on 03-07-24
                       
                      // $event=date('d-m-Y', strtotime($val['emp_proff']['joining_date']));
                        if (!empty($val['emp_proff']['joining_date'])) {
                                 $joining_date = new DateTime($val['emp_proff']['joining_date']);
                                 $currentYear = date('Y');
                                 $joining_date->setDate($currentYear, $joining_date->format('m'), $joining_date->format('d'));
                                 $event= $joining_date->format('d-m-Y');
                               }
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), " " . $empid);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . ($rowcount), $companyid);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . ($rowcount), $emp_name);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $branch);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $desig);
                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $department);
                      $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $join);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $termin);
                  
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), $type);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $event);
                 
                                
                    $columnindex = 1;
                    


                    $rowcount++;
                    
                  $i++;
                }
            }
                   if(isset($arr_daata)){
                     foreach ($arr_daata as $employee =>$val)
                        {
                        $empid = $val['employee_info']['employee_id'];
                        $companyid = $val['user_credentials']['user_id'];
                        $department=$val['employee_info']['department'];
                        $termin= isset($val['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($val['termination']['last_approved_working_date'])) : '';
                        $empstatus = (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : '';
                        $emp_name = $val['emp_details']['first_name'].$val['emp_details']['last_name'].$empstatus;
                        $branch = $val['branches']['branch_name'];
                        $desig=$val['desg']['desig_name'];
                        $join = date('d-m-Y', strtotime($val['emp_proff']['joining_date']));
                       if(!empty($val['emp_details']['date_of_birth'])){ $type= 'Birthday';}
                       if (!empty($val['emp_details']['date_of_birth'])) {
                               $dob = new DateTime($val['emp_details']['date_of_birth']);
                               $currentYear = date('Y');
                               $dob->setDate($currentYear, $dob->format('m'), $dob->format('d'));
                               $event= $dob->format('d-m-Y');}
                      // $event=date('d-m-Y', strtotime($val['emp_details']['date_of_birth']));
                       
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), " " . $empid);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . ($rowcount), $companyid);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . ($rowcount), $emp_name);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $branch);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $desig);
                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $department);
                    
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $join);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $termin);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), $type);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $event);
                 
                                
                    $columnindex = 1; 
                    


                    $rowcount++;
                    
                  $i++;
                }
            }
           
            if(isset($arr_prob)){
                foreach ($arr_prob as $employee =>$val)
                        {
                        $empid = $val['employee_info']['employee_id'];
                        $companyid = $val['user_credentials']['user_id'];
                        $department=$val['employee_info']['department'];
                        $empstatus = (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : '';
                        $emp_name = $val['emp_details']['first_name'].$val['emp_details']['last_name'].$empstatus;
                        $termin= isset($val['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($val['termination']['last_approved_working_date'])) : '';
                        $branch = $val['branches']['branch_name'];
                        $desig=$val['desg']['desig_name'];
                        $join = date('d-m-Y', strtotime($val['emp_proff']['joining_date']));
                       // if(!empty($val['emp_proff']['joining_date'])){  $type= 'Annivesary';}
                       //if(!empty($val['emp_proff']['emp_type'])){ $type= 'Probation-'.$val['emp_proff']['attr2'].'days';}
                        if (!empty($val['emp_proff']['emp_type']) && !empty($val['emp_proff']['attr2'])) {
                           $type = 'Probation-' . $val['emp_proff']['attr2'] . 'Days';
                        } else {
                            $type = 'Probation- 0 Days'; // or some default value if needed
                         }

                      // $event=date('d-m-Y', strtotime($val['emp_proff']['joining_date']));
                        
                            if (!empty($val['emp_proff']['attr2'])) {
                                $probationEndDate = new DateTime($val['emp_proff']['joining_date']);
                                $probationEndDate->add(new DateInterval('P' . $val['emp_proff']['attr2'] . 'D'));
                                $event= $probationEndDate->format('d-m-Y');
                            } else {
                                $event='';
                            }
                        
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), " " . $empid);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . ($rowcount), $companyid);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . ($rowcount), $emp_name);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $branch);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $desig);
                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $department);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $join);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $termin);
                    
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), $type);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $event);
                 
                                
                    $columnindex = 1;
                    


                    $rowcount++;
                    
                  $i++;
                }
            }
                                 
                }     
                if($criterias == 'Units'|| $criterias == 'EmployeeDetails'){
                foreach ($arr_leavepolicydetails_for_template as $value) {
                   //$i += 1;     
												   
                    $arr_daata = $value['summary'];
                    //$arr_work=$value['workanniversary'];
                   // $arr_prob=$value['probation'];
                    
                    if(empty($arr_daata)) continue;
								 

                  //  $branch = $arr_daata['0']['branches']['branch_name'];

                  
                    foreach ($arr_daata as $employee =>$val)
                        {
                        //debug($val);exit;
                        $empid = $val[0]['employee_id'];
                        $companyid = $val[0]['user_id'];
                        $empstatus = (isset($val[0]['status'])) && $val[0]['status'] == "2" ? '  (Resigned)' : '';
                        $emp_name = $val[0]['first_name'].$val[0]['last_name'].$empstatus;
                        $termin= isset($val[0]['last_approved_working_date']) ? date('d-m-Y', strtotime($val[0]['last_approved_working_date'])) : '';
                        $branch = $val[0]['branch_name'];
                        $desig=$val[0]['desig_name'];
                        $department=$val[0]['department'];
                        $join = date('d-m-Y', strtotime($val[0]['joining_date']));
                       //if(!empty($val[0]['date_of_birth'])){ $type= 'Birthday';}
                          $eventType = isset($val[0]['event_type']) ? $val[0]['event_type'] : '';
                       if ($eventType == 'Birthday') {
                              $type= 'Birthday';
                       } elseif ($eventType == 'Anniversary') {
                            $type= 'Anniversary';
                       } elseif ($eventType == 'Probation') {
                       if(!empty($val[0]['attr2'])){
                          $type= 'Probation-' . $val[0]['attr2'] . ' Days';
                        }else{
                           $type= 'Probation- 0 Days';
                         }
            
                       }
                       //$event=date('d-m-Y', strtotime($val['emp_details']['date_of_birth']));
//                       if (!empty($val[0]['date_of_birth'])) {
//                                       $dob = new DateTime($val['emp_details']['date_of_birth']);
//                                       $currentYear = date('Y');
//                                       $dob->setDate($currentYear, $dob->format('m'), $dob->format('d'));
//                                       $event= $dob->format('d-m-Y');}
                       if (!empty($val[0]['date_of_birth'])&& $eventType == 'Birthday') {
                                       $dob = new DateTime($val[0]['date_of_birth']);
                                       $currentYear = date('Y');
                                       $dob->setDate($currentYear, $dob->format('m'), $dob->format('d'));
                                       $event= $dob->format('d-m-Y');}
                                                      elseif (!empty($val[0]['joining_date'])&&$eventType == 'Anniversary') {
                                                            $joining_date = new DateTime($val[0]['joining_date']);
                                                            $currentYear = date('Y');
                                                            $joining_date->setDate($currentYear, $joining_date->format('m'), $joining_date->format('d'));
                                                            $event= $joining_date->format('d-m-Y');
                                                        }elseif (!empty($val[0]['attr2'])) {
                                $probationEndDate = new DateTime($val[0]['joining_date']);
                                $probationEndDate->add(new DateInterval('P' . $val[0]['attr2'] . 'D'));
                                $event= $probationEndDate->format('d-m-Y');
                            } else {
                                $event= '';
                            }
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), " " . $empid);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . ($rowcount), $companyid);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . ($rowcount), $emp_name);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $branch);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $desig);
                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $department);
                     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $join);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $termin);
                   
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), $type);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $event);
                 
                                
                    $columnindex = 1; 
                    


                    $rowcount++;
                    
                  $i++;
                }

                    
					 
                }
        }
         $BStyle = array(
                             'borders' => array(
                             'allborders' => array(
                             'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                               )
                            );
         $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
                       $objPHPExcel->getActiveSheet()
    ->getStyle('G3:G'.$highestRow)
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

    $objPHPExcel->getActiveSheet()
    ->getStyle('H3:H'.$highestRow)
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

     
                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A3:K'.$row)->applyFromArray($BStyle);
                        
//                        foreach(range('A','K') as $columnID) {
//                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
//                    }
                }
       
               
            $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('Events');
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
                $this->render('eventreports'); 
                break;
        }
    }


}
                