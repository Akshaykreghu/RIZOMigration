<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
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
class AttendanceCheckInOutController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout = "default";
    public $name = 'AttendanceCheckInOut';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('Earlyin','Earlyout','Latein','Lateout','Attendance','CentralControl','UserCredentials','EmployeeDetails','EmployeeProfessionalDetails','DeviceAttendance','Departments','Grades','Verticals','Units','ReportCriterias','AttendanceRegister','AttendanceRegisterReport','DbConfig','MobileUserauditor','CompanyContactInfo','EditPunchesHist', 'ReportAudit');//santhu
    public $components = array('MasterdataManagement');
    
    /*public $arr_employee_reportcriterias = array(
        'Departments' => 'belonging to a Department',
        'Grades' => 'belonging to a Grade',
        'Verticals' => 'belonging to a Vertical',
        'Units' => 'belonging to a Branch',
        'EmployeeDetails' => 'randomly, without criteria'
    );
    
    public $arr_employee_reportcriteria_fields = array(
        'Departments' => 'emp_dept',
        'Grades' => 'emp_grade',
        'Verticals' => 'emp_vertical',
        'Units' => 'emp_branch',
        'EmployeeDetails' => 'emp_pkey'
    );*/

    /*
     * HR Reports landing view
     */
    public function hrreports() {
        $arr_reporttypes = array(
            //'employee' => 'Employee Information',
            'EarlyInAttendance' => 'Early IN ',
            'EarlyOutAttendance' => 'Early OUT ',
            'LateInAttendance' => 'Late IN ',
            'LateOutAttendance' => 'Late OUT ',
            'AttendanceStatus' => 'Attendance Status ',
            'EarlyInDuration' =>  'Early IN Duration ' ,
            'EarlyOutDuration' =>' Early OUT Duration',
            'LateInDuration' =>'   Late IN Duration ',
            'LateOutDuration' =>'  Late OUT Duration',
             'BreakReport' =>'  Break Report',
             'DailyAttendance' => 'Daily Attendance', //Edited by Megha on 06-10-2025

                // 'Misspunch' => 'Miss Punch Report'
                /* 'attendance' => 'Attendance Summary', */
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '') {
        $this->autoRender = FALSE;
        // debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                // case 'employee':
                //                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                //                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                //                 break;
                case 'EarlyInAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'EarlyOutAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LateInAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'LateOutAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                    break;
                case 'AttendanceStatus':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'EarlyInDuration':
                                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                 break;

                 case 'EarlyOutDuration':
                                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                 break;

                 case 'LateInDuration':
                                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                 break;

                case 'LateOutDuration':
                                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                 break;
                case 'BreakReport':
                                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                 break;
                                  //Edited by Megha on 06-10-2025
                case 'DailyAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                    //End
//                  case 'Misspunch':
//                                  $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
//                                  $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
// //                                  $this->AttendanceRegister->useCbConfig = $this -> Session -> read('ds');
// //                                  $this->set('arr_month', Set::extract('/AttendanceRegister/.',$this->AttendanceRegister->find("month_year",array("conditions"=>array("month_year"<>'P/P',"month_year" != null,'reporttype'=>$type)))));
// // // debug($arr_month);
//                                  break;                
                //  case 'MobilelocationRep':
                //                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                //                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                //                 break;
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

    public function month() {
        $this->autoRender = FALSE;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_month = $this->AttendanceRegister->find("month_year", array("conditions" => array("month_year" => 'P/P', "month_year" != null)));

        $this->set("arr_month", $arr_month);
        debug($arr_month);

        $this->render('showreport');
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

                 $model = ($model == 'Units') ? 'Branches' : $model;
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
            if ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            }
            // Edited by Akshay on 29-1-2025
            elseif ($model == 'Units') {
                // Edited by Akshay on 29-1-2025
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions = array("status" => 1, "branch_code" => $is_ho);
                    } else {
                        $conditions = array("status" => 1);
                    }
                }                //edited by athira on 01-02-2025
                else {
                    $conditions = array("status" => 1);
                }
                //end
                // End
            }
            // End
            else {
                $conditions = array("status" => 1);
            }

            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
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


                case 'EmployeeDetails':
                    //Employee Company ID added by ***ARUL P DAS on 12/12/2019
                    $fields = 'emp_pkey,status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name," ",ifnull(last_name,"")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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

                    $arr_order = array("name" => "ASC");
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
                        'order' => $arr_order
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
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
            //            debug($arr_criteriaItems);
        }
    }

    public function reportAudit($type, $mode) {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        switch ($type) {
            case 'EarlyInAttendance':
                $dataForHistory['report_type'] = "Employee Early In Report";
                break;
            case 'EarlyOutAttendance':
                $dataForHistory['report_type'] = "Employee Early Out Report";
                break;
            case 'LateInAttendance':
                $dataForHistory['report_type'] = "Employee Late In Report";
                break;
            case 'LateOutAttendance':
                $dataForHistory['report_type'] = "Employee Late Out Report";
                break;
            case 'AttendanceStatus':
                $dataForHistory['report_type'] = "Attendance Status Report";
                break;
            case 'EarlyInDuration':
                $dataForHistory['report_type'] = "Employee Early In Duration Report";
                break;
            case 'EarlyOutDuration':
                $dataForHistory['report_type'] = "Employee Early Out Duration Report";
                break;
            case 'LateInDuration':
                $dataForHistory['report_type'] = "Employee Late In Duration Report";
                break;
            case 'LateOutDuration':
                $dataForHistory['report_type'] = "Employee Late Out Duration Report";
                break;
            case 'BreakReport':
                $dataForHistory['report_type'] = "Break Report";
                break;
                 case 'DailyAttendance':
                $dataForHistory['report_type'] = "Daily Attendance";
                break;
        }
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
                default : break;
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

    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;
        $company_code=$this->Session->read('company_code');
//debug($mode);
        switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'EarlyInAttendance':
                $this->generateearlyinreport($mode);
                break;
            case 'EarlyOutAttendance':
                $this->generateearlyoutreport($mode);
                break;
            case 'LateInAttendance':
                $this->generatelateinreport($mode);
                break;
            case 'LateOutAttendance':
                $this->generatelateoutereport($mode);
                break;
            case 'AttendanceStatus':
                $this->generatestatusreport($mode);
                break;
            case 'EarlyInDuration':
                $this->generateearlyindurationreport($mode);
                break;
             case 'EarlyOutDuration':
                $this->generateearlyoutdurationreport($mode);
                break;
             case 'LateInDuration':
                $this->generatelateindurationreport($mode);
                break;
             case 'LateOutDuration':
                $this->generatelateoutdurationreport($mode);
                break;
            case 'BreakReport':
               $this->generatebreakreport($mode);
               break;
                //Edited by Megha on 06-10-2025
            case 'DailyAttendance':
                  if($company_code=='EXTR' || $company_code =='RRLC' || $company_code=='BATT' || $company_code=='ASTL' || $company_code=='ASHL'){
                     $this->generatDailyAttendanceReportEXTR($type, $mode);
             
               }
               else{
            //  $this->generatDailyAttendanceReportEXTR($type, $mode);
            $this->generatDailyAttendanceReport($type, $mode);
               }
                break;
                //End
//            case 'Misspunch':
//                $this->generatemisspunchreport($type, $mode);
//                break;
//            case 'MobilelocationRep':
//                $this->generatemobilelocationreport($type, $mode);
//                break;
            default:
                return false;
                break;
        }

        $this->reportAudit($type, $mode);
    }

    private function _modelExists($modelName) {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }

     private function generatebreakreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date("Y-m-d", strtotime($arr_form_data['reportto'].' +1 day'));
        $f = date('d/m/Y', strtotime($arr_form_data['reportfrom']));
        $t = date("d/m/Y", strtotime($arr_form_data['reportto']));
        $this->set('from', $from);
        $this->set('to', $to);
        $this->set('f', $f);
        $this->set('t', $t);
       

      //Dates array to store the selected dates.
$Dates = array();
$fromDate = new DateTime($from);
$toDate = new DateTime($to);

// Add each date to the array
while ($fromDate <= $toDate) {
    $Dates[] = $fromDate->format('Y-m-d');
    $fromDate->modify('+1 day');
}

$arr_breakdata = array();
$int_criterias_count = $arr_form_data['hidden-criterias-count'];
for ($i = 1; $i <= $int_criterias_count; $i++) {
    $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
    $arr_breakdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
}

if ($str_criteria_item == '') {
    echo "<h1>No Criteria Selected</h1>";
    die();
}

if (!isset($arr_form_data[$str_criteria_item])) {
    echo "<h1>No Criteria Selected</h1>";
    die();
}

if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
    $conditions = "ei.emp_status in('1','2')";
} else {
    $conditions = "ei.emp_status ='1'";
}

$arr_breakdata_for_template = array();
if (isset($arr_breakdata) && !empty($arr_breakdata)) {
    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
        

foreach ($arr_breakdata as $breakdata) {
    $arr_empinfo = $this->EmployeeDetails->query("SELECT ei.emp_id, ei.employee_id, ei.EmpName,ei.emp_status, te.last_approved_working_date
        FROM employee_info AS ei
        LEFT JOIN termination AS te ON (ei.emp_pkey = te.emp_fkey AND te.status = 1)
        WHERE $conditions AND ei.emp_pkey = $breakdata
        ORDER BY ei.EmpName");

    $arr_deviceatt = $this->EmployeeDetails->query("
        SELECT ei.emp_id, da.LOGDATE, da.C1
        FROM employee_info AS ei
        LEFT JOIN device_attandance AS da ON (ei.emp_id = da.emp_id)
        WHERE $conditions AND ei.emp_pkey = '$breakdata' AND da.LOGDATE BETWEEN '$from' AND '$to' and status='Y'
        ORDER BY ei.EmpName
    ");

    $arr_today = array(); // Initialize an array to store data for the current date

    foreach ($Dates as $date) {
        foreach ($arr_deviceatt as $attendance) {
            $attendanceDate = date('Y-m-d', strtotime($attendance['da']['LOGDATE']));
            if ($attendanceDate == $date) {
                // If the dates match, store 'da' and 'ei' data in $arr_today
                $arr_today[$date][] = [
                    'da' => $attendance['da'],
                    'ei' => isset($arr_empinfo[0]) ? $arr_empinfo[0] : [],
                ];
            }
        }
    }

    $arr_today_count = count($arr_today);

    $durationArray = array();
            
    foreach ($arr_today as $date => $entries) {
        $value1 = 0;
        $InEntry = false;
        $OutEntry = false;

        foreach ($entries as $entry) {
            //debug($entry);exit();
            if ($entry['da']['C1'] == 'out') {
                $value1 = strtotime($entry['da']['LOGDATE']);
                 $hasOutEntry = true;
            } elseif ($entry['da']['C1'] == 'in' && $value1 != 0) {
                $value2 = strtotime($entry['da']['LOGDATE']);
                $durationInSeconds = $value2 - $value1;


                // Convert duration to minutes
                $durationInMinutes = round($durationInSeconds / 60);

                // Fetch emp_id and EmpName from the 'ei' table if available
                $empId = isset($entry['ei']['ei']['emp_id']) ? $entry['ei']['ei']['emp_id'] : '';
                $empName = isset($entry['ei']['ei']['EmpName']) ? $entry['ei']['ei']['EmpName'] : '';
                $status = isset($entry['ei']['ei']['emp_status']) ? $entry['ei']['ei']['emp_status'] : '';
                $eid = isset($entry['ei']['ei']['employee_id']) ? $entry['ei']['ei']['employee_id'] : '';


                // Check if an entry for this date already exists
                $existingEntryIndex = array_search($date, array_column($durationArray, 'date'));

                if ($existingEntryIndex !== false) {
                    // Update existing entry with additional duration
                    $durationArray[$existingEntryIndex]['duration'] += $durationInMinutes;
                } else {
                    // Add a new entry
                    $durationArray[] = array(
                        'date' => $date,
                        'emp_id' => $empId,
                        'EmpName' => $empName,
                        'status' => $status,
                        'eid' => $eid,
                        'duration' => $durationInMinutes
                    );
                }

                $value1 = 0; // Reset value1 for the next iteration
                $hasInEntry = true;
            }
        }
    }
    if (!empty($durationArray)) {
                    $arr_breakdata_for_template[] = array(
                        'summary' => $durationArray,
                            // 'employees'=>$arr_leavepolicy_employees
                    );
                }
}   
            }
            else{
foreach ($arr_breakdata as $breakdata) {
    $arr_brinfo = $this->EmployeeDetails->query("SELECT ei.emp_pkey,ei.emp_id, ei.employee_id, ei.EmpName,ei.emp_status, te.last_approved_working_date
        FROM employee_info AS ei
        LEFT JOIN termination AS te ON (ei.emp_pkey = te.emp_fkey AND te.status = 1)
        WHERE $conditions AND ei.branch_code = '$breakdata'
        ORDER BY ei.EmpName");
    foreach ($arr_brinfo as $brinfo) {
        $emp_pkey = $brinfo['ei']['emp_pkey'];
        $arr_empinfo = $this->EmployeeDetails->query("SELECT ei.emp_id,ei.branch, ei.employee_id, ei.EmpName,ei.emp_status
        FROM employee_info AS ei
        LEFT JOIN termination AS te ON (ei.emp_pkey = te.emp_fkey AND te.status = 1)
        WHERE $conditions AND ei.emp_pkey = $emp_pkey
        ORDER BY ei.EmpName");
        $emp_id = $brinfo['ei']['emp_id'];
        $arr_deviceatt = $this->EmployeeDetails->query("
        SELECT ei.emp_id, da.LOGDATE, da.C1
        FROM employee_info AS ei
        LEFT JOIN device_attandance AS da ON (ei.emp_id = da.emp_id)
        WHERE $conditions AND ei.emp_id = '$emp_id' AND da.LOGDATE BETWEEN '$from' AND '$to' and status='Y'
        ORDER BY ei.EmpName");
        $arr_today = array(); // Initialize an array to store data for the current date

    foreach ($Dates as $date) {
        foreach ($arr_deviceatt as $attendance) {
            $attendanceDate = date('Y-m-d', strtotime($attendance['da']['LOGDATE']));
            if ($attendanceDate == $date) {
                // If the dates match, store 'da' and 'ei' data in $arr_today
                $arr_today[$date][] = [
                    'da' => $attendance['da'],
                    'ei' => isset($arr_empinfo[0]) ? $arr_empinfo[0] : [],
                ];
            }
        }
    }

    $arr_today_count = count($arr_today);

    $durationArray = array();
    foreach ($arr_today as $date => $entries) {
        $value1 = 0;

        foreach ($entries as $entry) {
            //debug($entry);exit();
            if ($entry['da']['C1'] == 'out') {
                $value1 = strtotime($entry['da']['LOGDATE']);
            } elseif ($entry['da']['C1'] == 'in' && $value1 != 0) {
                $value2 = strtotime($entry['da']['LOGDATE']);
                $durationInSeconds = $value2 - $value1;

                // Convert duration to minutes
                $durationInMinutes = round($durationInSeconds / 60);

                // Fetch emp_id and EmpName from the 'ei' table if available
                $empId = isset($entry['ei']['ei']['emp_id']) ? $entry['ei']['ei']['emp_id'] : '';
                $empName = isset($entry['ei']['ei']['EmpName']) ? $entry['ei']['ei']['EmpName'] : '';
                $status = isset($entry['ei']['ei']['emp_status']) ? $entry['ei']['ei']['emp_status'] : '';
                $branch = isset($entry['ei']['ei']['branch']) ? $entry['ei']['ei']['branch'] : '';
                $eid = isset($entry['ei']['ei']['employee_id']) ? $entry['ei']['ei']['employee_id'] : '';
                // Check if an entry for this date already exists
                $existingEntryIndex = array_search($date, array_column($durationArray, 'date'));

                if ($existingEntryIndex !== false) {
                    // Update existing entry with additional duration
                    $durationArray[$existingEntryIndex]['duration'] += $durationInMinutes;
                } else {
                    // Add a new entry
                    $durationArray[] = array(
                        'date' => $date,
                        'emp_id' => $empId,
                        'EmpName' => $empName,
                        'status' => $status,
                        'eid' => $eid,
                        'branch' => $branch,
                        'duration' => $durationInMinutes
                    );
                }

                $value1 = 0; // Reset value1 for the next iteration
            }
        }
    }
    if (!empty($durationArray)) {
                    $arr_breakdata_for_template[] = array(
                        'summary' => $durationArray,
                            // 'employees'=>$arr_leavepolicy_employees
                    );
                }
    }
}
                
            }
          
        //  debug($arr_breakdata_for_template); exit(); 

            $this->set('arr_breakdata_for_template', $arr_breakdata_for_template);

            //Set informations needed for report

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);
           
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            switch ($mode) {
                case 'pdf' :
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('breakreport');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeEarlyInReport.pdf', 'D');
                    // $this->render('earlyinreport');                
                    break;
                case 'excel' :

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? "{$str_company_code}_BreakReport_{$from} to {$to}.xlsx" : "BreakReport_" . strtotime() . ".xlsx";


                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);
                
                    $worksheet = $objPHPExcel->getActiveSheet();

                  $worksheet->setCellValueByColumnAndRow(0, 1, " Break Report for - " . $f . ' - ' . $t);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(13);
                    $worksheet->mergeCells('A1:E1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                     $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(11);
                $worksheet->mergeCells('A2:E2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(true);
                    }


                    if (empty($arr_breakdata_for_template[0]['summary'])){
    //  echo "<h3>No Data Available With The Selected Criteria</h3>";

     //print nodata
     $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(11);
  $worksheet->mergeCells('A3:E3');
  $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
          array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
  );
  for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(true);
                    }

    }
    else{

                    $rowcount = 3;
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Duration of Break (Minutes)');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    
                    $rowcount+=1;
                    $i = 1;
                    foreach ($arr_breakdata_for_template as $value) {
                        if (isset($value['summary']['0']['EmpName'])) {

                            $arr_data = $value['summary'];

                            if (count($arr_data) >= 0) {

                                foreach ($arr_data as $key => $val) {
                          //debug($val);exit();

                                     $columnindex = 0;
                                     $empstatus = isset($val['status']) && $val['status'] == "2" ? '(Resigned)' : '';
                                     $name = $val['EmpName'] .$empstatus;
                                      $id = $val['eid'];
                                      $date = $val['date'];
                                      $dateatt = date("d-m-Y", strtotime($date));
                                      $duraion = $val['duration'];


                
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $name);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $dateatt);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $duraion);
                     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    



                                    $rowcount++;
                                    $i = $i + 1;
                                }
                                $BStyle = array(

                        'borders' => array(

                            'allborders' => array(

                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )

                        )

                    );

                    $row = $rowcount - 1;

                    $objPHPExcel->getActiveSheet()->getStyle('A1:E' . $row)->applyFromArray($BStyle);
                            }
                            
                        }
                    }
                }
 
                      $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    $objPHPExcel->getActiveSheet()->setTitle('Break');
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
                    $this->render('breakreport');
                    break;
            }
        }
    }
    
   private function generateearlyinreport($mode)
    {
        $arr_form_data = $_REQUEST;
        // debug($arr_form_data);

        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
    
//Edited by ASHIN on 19-11-24      
    $selected_month = date('Y-m', strtotime($arr_form_data['reportfrom']));
    $this->set('from', $selected_month);

    // Calculate the start date (26th of the previous month)
    $start_date = date('Y-m-d', strtotime($selected_month . ' -1 month +25 days'));
    // Calculate the end date (25th of the selected month)
    $end_date = date('Y-m-d', strtotime($selected_month . ' +24 days'));
          $this->set('start_date', $start_date);
          $this->set('end_date', $end_date);
 
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $this->set('from', $from);

        
        $company_code = strtoupper($this->Session->read('company_code'));  //edited by ASHIN on 20-11-24
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);

        //company_code
        // $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        // $month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date('Y-m');
        // $attendance_date = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:0;
        // $att_enddate = date('d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',  strtotime($month)))));
        // $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',strtotime('-1 months',strtotime($month)))))))));
        //   $arr_date_in_selectedmonth = range(1, $att_enddate);        
        //   if($att_startdate != 1){
        //       $arr_date_in_prevmonth = range($att_startdate, date('t',strtotime('-1 months',strtotime($month))));
        //   }else{
        //       $arr_date_in_prevmonth = array();
        //   }
        //   $arr_dates = array_merge($arr_date_in_prevmonth,$arr_date_in_selectedmonth);
        //   $this->set('arr_dates', $arr_dates);
        // $fd=$arr_form_data['reportfrom'].' '.'00:00:00';
        //     if((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom']!= '')){
        //       $report_month = $arr_form_data['reportfrom'];
        //       $from =$arr_form_data['reportfrom'];
        //       // $to = $arr_form_data['reportto'];
        //       $fromdt =$arr_form_data['reportfrom'].' '.'00:00:00';
        // $todt = $arr_form_data['reportto'].' '.'23:59:59';
        // }
        $arr_earlyindata = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            //           debug($str_criteria_item);
            $arr_earlyindata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        if ($str_criteria_item == '') {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            // debug($arr_form_data);     
            $conditions = "ed.status in('1','2')";
            // debug($conditions);
        } else {
            $conditions = "ed.status ='1'";
        }

        // $conditions = 'earlyin.LogDate between "'.$from.'" and "'.$to.'"';
        //        $conditions=array();
        $arr_earlyindata_for_template = array();
        if (isset($arr_earlyindata) && !empty($arr_earlyindata)) {

            foreach ($arr_earlyindata as $earlyindata) {

 //edited by athira on 17-07-2025
                if ($company_code == 'HRBL') {
                    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                        $arr_earlyindata = $this->EmployeeDetails->query("select earlyin.emp_pkey,earlyin.EmpName,earlyin.LogDate,COALESCE(NULLIF(earlyin.Location,''),br.branch_name) Location,
                 earlyin.SharpInTime,earlyin.InTime,earlyin.EarlyInTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
                 from emp_early_in earlyin 
                 Left join emp_details as ed on (earlyin.emp_pkey = ed.emp_pkey)
                 Left join branches as br on (ed.branch_code = br.branch_code)
                 Left join employee_info as ei on (ed.emp_pkey = ei.emp_pkey)
                Left join termination as te on (ed.emp_pkey = te.emp_fkey and te.status = 1)
                Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions and DATE(earlyin.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and earlyin.emp_pkey = $earlyindata order by earlyin.EmpName,earlyin.LogDate");
                    } else {
                        $arr_earlyindata = $this->EmployeeDetails->query("select earlyin.emp_pkey,earlyin.EmpName,earlyin.LogDate,COALESCE(NULLIF(earlyin.Location,''),br.branch_name) Location,
                earlyin.SharpInTime,earlyin.InTime,earlyin.EarlyInTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
                from emp_early_in earlyin 
                   Left join emp_details as ed on (earlyin.emp_pkey = ed.emp_pkey)
                   Left join branches as br on (ed.branch_code = br.branch_code)
                   Left join employee_info as ei on (ed.emp_pkey = ei.emp_pkey)
                   Left join termination as te on (ed.emp_pkey = te.emp_fkey and te.status = 1)
                   Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                   where $conditions and DATE(earlyin.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$earlyindata' order by earlyin.EmpName,earlyin.LogDate");
                    }
                } else {
                    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                        $arr_earlyindata = $this->EmployeeDetails->query("select earlyin.emp_pkey,earlyin.EmpName,earlyin.LogDate,COALESCE(NULLIF(earlyin.Location,''),br.branch_name) Location,
     earlyin.SharpInTime,earlyin.InTime,earlyin.EarlyInTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
     from emp_early_in earlyin 
     Left join emp_details as ed on (earlyin.emp_pkey = ed.emp_pkey)
     Left join branches as br on (ed.branch_code = br.branch_code)
     Left join employee_info as ei on (ed.emp_pkey = ei.emp_pkey)
    Left join termination as te on (ed.emp_pkey = te.emp_fkey and te.status = 1)
    Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
     where $conditions and DATE(earlyin.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and earlyin.emp_pkey = $earlyindata order by earlyin.EmpName,earlyin.LogDate");
                    } else {
                        $arr_earlyindata = $this->EmployeeDetails->query("select earlyin.emp_pkey,earlyin.EmpName,earlyin.LogDate,COALESCE(NULLIF(earlyin.Location,''),br.branch_name) Location,
    earlyin.SharpInTime,earlyin.InTime,earlyin.EarlyInTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
    from emp_early_in earlyin 
       Left join emp_details as ed on (earlyin.emp_pkey = ed.emp_pkey)
       Left join branches as br on (ed.branch_code = br.branch_code)
       Left join employee_info as ei on (ed.emp_pkey = ei.emp_pkey)
       Left join termination as te on (ed.emp_pkey = te.emp_fkey and te.status = 1)
       Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
       where $conditions and DATE(earlyin.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$earlyindata' order by earlyin.EmpName,earlyin.LogDate");
                    }
                }
                //end
              
                if (!empty($arr_earlyindata)) {
                    $arr_earlyindata_for_template[] = array(
                        'summary' => $arr_earlyindata,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
            }
            // zt(array_keys($vaal["AttendanceRegister"], "P"));
            //            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
            //            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
            //
            //            $arr_earlyindata_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
            //            }
            //        }
            // debug($resp_register);
            //debug($arr_leavepolicydetails_for_template);  

            $this->set('arr_earlyindata_for_template', $arr_earlyindata_for_template);

            //Set informations needed for report

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);

            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('earlyinreport');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeEarlyInReport.pdf', 'D');
                    // $this->render('earlyinreport');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "EarlyIN.xlsx" : "AttendanceA";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, " Early IN - " . $mname . "  "  . $year);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:N1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:N2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }


                    if (empty($arr_earlyindata_for_template[0]['summary'])) {
                        //  echo "<h3>No Data Available With The Selected Criteria</h3>";

                        //print nodata
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:N3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                    } else {

                        $rowcount = 3;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, ' Company ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Location');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Shift Start Time');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'IN Time');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Early IN Time');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $rowcount += 1;
                        $i = 1;
                        foreach ($arr_earlyindata_for_template as $value) {
                            if (isset($value['summary']['0']['earlyin']['EmpName'])) {

                                $arr_data = $value['summary'];

                                if (count($arr_data) >= 0) {

                                    foreach ($arr_data as $key => $val) {


                                        $columnindex = 0;
                                        $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                        $name = $val['earlyin']['EmpName'] . $empstatus;
                                        $id = $val['ei']['emp_id'];
                                        $c_id = $val['ei']['employee_id'];
                                        $dep = $val['ei']['department'];
                                        $des = $val['ei']['designation'];
                                        $join = $val['ei']['joining_date'];
                                        $termin = $val['te']['last_approved_working_date'];
                                        $branch = $val['br']['branch_name'];
                                        $date = $val['earlyin']['LogDate'];
                                        $dateatt = date("d-m-Y", strtotime($date));
                                        $location = $val['0']['Location'];
                                        $sharpintime = $val['earlyin']['SharpInTime'];
                                        $intime = $val['earlyin']['InTime'];
                                        $earlyintime = $val['earlyin']['EarlyInTime'];



                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $c_id);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $dep);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $des);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $dateatt);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $location);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $sharpintime);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $intime);
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $earlyintime);


                                        $columnindex = $columnindex + 12;


                                        $rowcount++;
                                        $i = $i + 1;
                                    }
                                }
                                $rowcount1 = $rowcount + 1;
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('B4:N4000')
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            }
                        }
                    }

                    $objPHPExcel->getActiveSheet()->setTitle(' Early IN');
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
                    $this->render('earlyinreport');
                    break;
            }
        }
    }
     private function generateearlyoutreport($mode)
    {
        $arr_form_data = $_REQUEST;
        //      debug($arr_form_data);
        $this->Earlyout->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $this->EditPunchesHist->useDbConfig = $this->Session->read('ds');

 //Edited by ASHIN on 19-11-24
    $selected_month = date('Y-m', strtotime($arr_form_data['reportfrom']));
    $this->set('from', $selected_month);
         // Calculate the start date (26th of the previous month)
    $start_date = date('Y-m-d', strtotime($selected_month . ' -1 month +25 days'));
        // Calculate the end date (25th of the selected month)
    $end_date = date('Y-m-d', strtotime($selected_month . ' +24 days'));
       $this->set('start_date', $start_date);
       $this->set('end_date', $end_date);

        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));    
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
       // $month1 =  $month . '-01';     //Hided by ASHIN on 19-11-24
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $company_code = strtoupper($this->Session->read('company_code')); //edited by ASHIN 20-11-24
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        
        $from_date = date('d-m-Y', strtotime($arr_form_data['reportfrom']));
        $this->set('from_date', $from_date);
        $arr_earlyoutdata = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            //          debug($str_criteria_item);
            $arr_earlyoutdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        if ($str_criteria_item == '') {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            // debug($arr_form_data);     
            $conditions = " ed.status in('1','2')";
            // debug($conditions);
        } else {
            $conditions = " ed.status ='1'";
        }


        //        $conditions=array();
        $arr_earlyoutdata_for_template = array();
        if (isset($arr_earlyoutdata) && !empty($arr_earlyoutdata)) {

            foreach ($arr_earlyoutdata as $earlyoutdata) {
              //edited by athira on 17-07-2025
                if ($company_code == 'HRBL') {
                    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                        $arr_earlyoutdata = $this->EmployeeDetails->query("select user.user_id,earlyout.emp_pkey,earlyout.EmpName,earlyout.LogDate,COALESCE(NULLIF(earlyout.Location,''),br.branch_name) Location,
                earlyout.OffDutyTime,earlyout.OutTime,earlyout.EarlyOutTime,earlyout.ShiftEndTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
                from emp_early_out earlyout
                 Left join emp_details as ed on (earlyout.emp_pkey = ed.emp_pkey)
                 Left join branches as br on (ed.branch_code = br.branch_code)
                 Left join employee_info as ei on (earlyout.emp_pkey = ei.emp_pkey)
                 Left join user_credentials as user on (ei.emp_pkey = user.emp_fkey)
                 Left join termination as te on (earlyout.emp_pkey = te.emp_fkey and te.status = 1)
                 Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions  and earlyout.EarlyOutTime > 0 and DATE(earlyout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and earlyout.emp_pkey = $earlyoutdata  order by earlyout.EmpName,earlyout.LogDate");
                    } else {
                        $arr_earlyoutdata = $this->EmployeeDetails->query("select user.user_id, earlyout.emp_pkey,earlyout.EmpName,earlyout.LogDate,COALESCE(NULLIF(earlyout.Location,''),br.branch_name) Location,
            earlyout.OffDutyTime,earlyout.OutTime,earlyout.EarlyOutTime,earlyout.ShiftEndTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
            from emp_early_out earlyout
                 Left join emp_details as ed on (earlyout.emp_pkey = ed.emp_pkey)
                 Left join branches as br on (ed.branch_code = br.branch_code)
                 Left join employee_info as ei on (earlyout.emp_pkey = ei.emp_pkey)
                 Left join user_credentials as user on (ei.emp_pkey = user.emp_fkey)
                 Left join termination as te on (earlyout.emp_pkey = te.emp_fkey and te.status = 1)
                 Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions  and earlyout.EarlyOutTime > 0 and DATE(earlyout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$earlyoutdata' order by earlyout.EmpName,earlyout.LogDate");
                    }
                } else {
                    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                        $arr_earlyoutdata = $this->EmployeeDetails->query("select user.user_id,earlyout.emp_pkey,earlyout.EmpName,earlyout.LogDate,COALESCE(NULLIF(earlyout.Location,''),br.branch_name) Location,
    earlyout.OffDutyTime,earlyout.OutTime,earlyout.EarlyOutTime,earlyout.ShiftEndTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
    from emp_early_out earlyout
     Left join emp_details as ed on (earlyout.emp_pkey = ed.emp_pkey)
     Left join branches as br on (ed.branch_code = br.branch_code)
     Left join employee_info as ei on (earlyout.emp_pkey = ei.emp_pkey)
     Left join user_credentials as user on (ei.emp_pkey = user.emp_fkey)
     Left join termination as te on (earlyout.emp_pkey = te.emp_fkey and te.status = 1)
     Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
     where $conditions  and earlyout.EarlyOutTime > 0 and DATE(earlyout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and earlyout.emp_pkey = $earlyoutdata  order by earlyout.EmpName,earlyout.LogDate");
                    } else {
                        $arr_earlyoutdata = $this->EmployeeDetails->query("select user.user_id, earlyout.emp_pkey,earlyout.EmpName,earlyout.LogDate,COALESCE(NULLIF(earlyout.Location,''),br.branch_name) Location,
     earlyout.OffDutyTime,earlyout.OutTime,earlyout.EarlyOutTime,earlyout.ShiftEndTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
     from emp_early_out earlyout
     Left join emp_details as ed on (earlyout.emp_pkey = ed.emp_pkey)
     Left join branches as br on (ed.branch_code = br.branch_code)
     Left join employee_info as ei on (earlyout.emp_pkey = ei.emp_pkey)
     Left join user_credentials as user on (ei.emp_pkey = user.emp_fkey)
     Left join termination as te on (earlyout.emp_pkey = te.emp_fkey and te.status = 1)
     Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
     where $conditions  and earlyout.EarlyOutTime > 0 and DATE(earlyout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$earlyoutdata' order by earlyout.EmpName,earlyout.LogDate");
                    }
                }
                //end
                if (!empty($arr_earlyoutdata)) {
                    $arr_earlyoutdata_for_template[] = array(
                        'summary' => $arr_earlyoutdata,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
            }
            //debug($arr_earlyindata_for_template);
            //              foreach ($arr_earlyindata_for_template as $key => $value) {
            //                  foreach($value['summary'] as $ky => $vaal){
            //            $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
            //            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
            //            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
            //
            //            $arr_earlyindata_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
            //            }
            //        }
            // debug($resp_register);
            //debug($arr_leavepolicydetails_for_template);  

            $this->set('arr_earlyoutdata_for_template', $arr_earlyoutdata_for_template);

            //Set informations needed for report

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('from', $from);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);
            $this->set('user_name', $user_name);
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            //echo date('d-m-Y H:i');
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            //debug(count($arr_earlyoutdata_for_template[0]['summary'])); exit;
            //    debug($arr_earlyoutdata_for_template);exit;
            function num2alpha($n)
            {
                $r = '';
                for ($i = 1; $n >= 0 && $i < 10; $i++) {
                    $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
                    $n -= pow(26, $i);
                }
                return $r;
            }

            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('earlyoutreport');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('real');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeEarlyOutReport.pdf', 'D');
                    $this->render('earlyoutreport');
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    //$file_name = isset($str_company_code) ? $str_company_code . "EarlyOut.xlsx".$mname."  "  .$year : "AttendanceA";
                    //edited by sinsiya
                    $file_name = isset($str_company_code) ? "{$str_company_code}EarlyOUT-{$year}-{$month}.xlsx"  : "AttendanceA.xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "                                                              Early OUT - " . $from_date);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:O1');
                    // $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );

                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->setCellValueByColumnAndRow(0, 2, "                                                                (Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:O2');
                    // $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );

                    // debug($arr_earlyoutdata_for_template);
                    // exit();
                    // if (count($arr_earlyoutdata_for_template) == 0){


                    //if (empty($arr_earlyoutdata_for_template[0]['summary'])){
                    //  echo "<h3>No Data Available With The Selected Criteria</h3>";

                    //print nodata
                    //    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under this selected criteria. ");
                    //    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    // $worksheet->mergeCells('A3:O3');
                    // $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    // );

                    //}
                    // else{
                    $rowcount = 3;
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Company ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Location');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Shift End Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Early OUT Limit');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'OUT Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Early OUT Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                    $rowcount += 1;
                    $i = 1;
                    foreach ($arr_earlyoutdata_for_template as $value) {
                        if (isset($value['summary']['0']['earlyout']['EmpName'])) {

                            //                              $rowcount++;
                            //                              $names=isset($value['summary']['0']['earlyin']['EmpName']) ? $value['summary']['0']['earlyin']['EmpName'] : '' ;
                            //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,"Early In Report of ".$names);
                            $arr_data = $value['summary'];
                            //                         $worksheet->mergeCells('A'.$rowcount.':F'.$rowcount);
                            //                         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                            //                                 array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            //                         );
                            //                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((0), $rowcount)->getFont()->setBold(true);
                            //                $columnindex = $columncount+6;
                            //                                 
                            //                         $rowcount+=2;
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                            if (count($arr_data) >= 0) {


                                foreach ($arr_data as $key => $val) {


                                    $columnindex = 0;
                                    $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                    $name = $val['earlyout']['EmpName'] . $empstatus;
                                    //$name=$name.$key;
                                    $id = $val['ei']['employee_id'];
                                    $c_id = $val['user']['user_id'];
                                    $dep = $val['ei']['department'];
                                    $des = $val['ei']['designation'];
                                    $join = $val['ei']['joining_date'];
                                    $formattedDate = date('d-m-Y', strtotime($join));
                                    $terminn = $val['te']['last_approved_working_date'];

                                    // Check if $terminn is set and not empty
                                    if (!empty($terminn)) {
                                        $termin = date('d-m-Y', strtotime($terminn));
                                    } else {
                                        // Provide a default value when there is no data
                                        $termin = '';
                                    }

                                    // Now, $termin contains the formatted date or the default value

                                    $branch = $val['br']['branch_name'];
                                    $date = $val['earlyout']['LogDate'];
                                    $dateatt = date("d-m-Y", strtotime($date));
                                    $location = $val['0']['Location'];

                                    $shiftendtime = $val['earlyout']['ShiftEndTime'];
                                    $earlyoutlimit = $val['earlyout']['OffDutyTime'];

                                    $intime = $val['earlyout']['OutTime'];

                                    $earlyout = $val['earlyout']['EarlyOutTime'];
                                    $earlyouttime = date('H:i:s', strtotime($earlyout));

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);



                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $c_id);
                                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);



                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $formattedDate);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $dep);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $des);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $dateatt);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $location);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $shiftendtime);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $earlyoutlimit);

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $intime);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowcount, $earlyouttime);


                                    $columnindex = $columnindex + 13;


                                    $rowcount++;

                                    $i = $i + 1;
                                }
                            }
                            //                          $objPHPExcel->getActiveSheet()
                            // ->getStyle('B4:O400')
                            // ->getAlignment()
                            // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            // $rowcount1 = $rowcount + 1;
                        }
                    }
                    if ($i == 1) {
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under this selected criteria. ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:O3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                    }
                    // }
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    //Border style
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )
                        )
                    );

                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'O' . ($rowcount - 1))->applyFromArray($styleArray);



                    $objPHPExcel->getActiveSheet()->setTitle('Early OUT ');
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
                    $this->render('earlyoutreport');
                    break;
            }
        }
    }

      private function generatelateinreport($mode)
    {
        $arr_form_data = $_REQUEST;
        //       debug($arr_form_data);
        $this->Latein->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
       
//Edited by ASHIN on 19-11-24
        $selected_month = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $this->set('from', $selected_month);
        // Calculate the start date (26th of the previous month)
        $start_date = date('Y-m-d', strtotime($selected_month . ' -1 month +25 days'));
        // Calculate the end date (25th of the selected month)
        $end_date = date('Y-m-d', strtotime($selected_month . ' +24 days'));
              $this->set('start_date', $start_date);
              $this->set('end_date', $end_date);

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $this->set('from', $from);

        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $company_code = strtoupper($this->Session->read('company_code')); //edited by ASHIN on 20-11-24

        $arr_lateindata = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_lateindata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        if ($str_criteria_item == '') {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            // debug($arr_form_data);     
            $conditions = " ed.status in('1','2')";
            // debug($conditions);
        } else {
            $conditions = " ed.status ='1'";
        }

        // $conditions1 = ' and latein.LogDate between "'.$fromdt.'" and "'.$todt.'"';
        //        $conditions=array();
        $arr_lateindata_for_template = array();
        if (isset($arr_lateindata) && !empty($arr_lateindata)) {
            foreach ($arr_lateindata as $lateindata) {
    //edited by athira on 17-07-2025
                if ($company_code == 'HRBL') {
                    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                        $arr_lateindata = $this->EmployeeDetails->query("select latein.emp_pkey,latein.EmpName,latein.LogDate,COALESCE(NULLIF(latein.Location,''),br.branch_name) Location,
            latein.LateInLimit,latein.InTime,latein.ShiftTime,latein.LateTime,br.branch_name,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
            from emp_late_in latein
                 Left join emp_details as ed on (latein.emp_pkey = ed.emp_pkey)
                  Left join employee_info as ei on (latein.emp_pkey = ei.emp_pkey)
                   Left join termination as te on (latein.emp_pkey = te.emp_fkey and te.status = 1)
                 Left join branches as br on (ed.branch_code = br.branch_code)
                 Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions and DATE(latein.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and latein.emp_pkey = '$lateindata' order by latein.EmpName,latein.LogDate");
                    } else {
                        $arr_lateindata = $this->EmployeeDetails->query("select latein.emp_pkey,latein.EmpName,latein.LogDate,COALESCE(NULLIF(latein.Location,''),br.branch_name) Location,
                    latein.LateInLimit,latein.InTime,latein.ShiftTime,latein.LateTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
             from emp_late_in latein
                   Left join emp_details as ed on (latein.emp_pkey = ed.emp_pkey)
                   Left join employee_info as ei on (latein.emp_pkey = ei.emp_pkey)
                   Left join termination as te on (latein.emp_pkey = te.emp_fkey and te.status = 1)
                   Left join branches as br on (ed.branch_code = br.branch_code)
                   Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions and DATE(latein.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$lateindata' order by latein.EmpName,latein.LogDate");
                    }
                } else {

                    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                        $arr_lateindata = $this->EmployeeDetails->query("select latein.emp_pkey,latein.EmpName,latein.LogDate,COALESCE(NULLIF(latein.Location,''),br.branch_name) Location,
            latein.LateInLimit,latein.InTime,latein.ShiftTime,latein.LateTime,br.branch_name,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
            from emp_late_in latein
      Left join emp_details as ed on (latein.emp_pkey = ed.emp_pkey)
      Left join employee_info as ei on (latein.emp_pkey = ei.emp_pkey)
       Left join termination as te on (latein.emp_pkey = te.emp_fkey and te.status = 1)
     Left join branches as br on (ed.branch_code = br.branch_code)
     Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
     where $conditions and DATE(latein.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and latein.emp_pkey = '$lateindata' order by latein.EmpName,latein.LogDate");
                    } else {
                        $arr_lateindata = $this->EmployeeDetails->query("select latein.emp_pkey,latein.EmpName,latein.LogDate,COALESCE(NULLIF(latein.Location,''),br.branch_name) Location,
        latein.LateInLimit,latein.InTime,latein.ShiftTime,latein.LateTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
 from emp_late_in latein
       Left join emp_details as ed on (latein.emp_pkey = ed.emp_pkey)
       Left join employee_info as ei on (latein.emp_pkey = ei.emp_pkey)
       Left join termination as te on (latein.emp_pkey = te.emp_fkey and te.status = 1)
       Left join branches as br on (ed.branch_code = br.branch_code)
       Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
     where $conditions and DATE(latein.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$lateindata' order by latein.EmpName,latein.LogDate");
                    }
                }
                //end
                if (!empty($arr_lateindata)) {
                    $arr_lateindata_for_template[] = array(
                        'summary' => $arr_lateindata,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
            }
            // debug($arr_lateindata_for_template);
            // exit;
            //debug($arr_earlyindata_for_template);
            //              foreach ($arr_earlyindata_for_template as $key => $value) {
            //                  foreach($value['summary'] as $ky => $vaal){
            //            $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
            //            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
            //            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
            //
            //            $arr_earlyindata_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
            //            }
            //        }
            // debug($resp_register);
            //debug($arr_leavepolicydetails_for_template);  

            $this->set('arr_lateindata_for_template', $arr_lateindata_for_template);

            //Set informations needed for report

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
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('lateinreport');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeLateInReport.pdf', 'D');
                    $this->render('lateinreport');
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "LateIn" . $from . ".xlsx" : "LateIn" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Late IN - " . $mname . "  "  . $year);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:K1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }



                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:K2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    //       if (empty($arr_lateindata_for_template[0]['summary'])){
                    //     //  echo "<h3>No Data Available With The Selected Criteria</h3>";

                    //      //print nodata
                    //      $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
                    //      $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    //   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    //   $worksheet->mergeCells('A3:N3');
                    //   $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    //           array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    //   );
                    // }
                    // else{

                    $rowcount = 3;
                    $columncount = 0;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, ' Company ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Location');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Shift Start Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Late IN Limit');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'IN Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Late IN Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                    $rowcount += 1;
                    $i = 1;
                    foreach ($arr_lateindata_for_template as $value) {
                        if (isset($value['summary']['0']['latein']['EmpName'])) {

                            //                              $rowcount++;
                            //                              $names=isset($value['summary']['0']['earlyin']['EmpName']) ? $value['summary']['0']['earlyin']['EmpName'] : '' ;
                            //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,"Early In Report of ".$names);
                            $arr_data = $value['summary'];
                            //                         $worksheet->mergeCells('A'.$rowcount.':F'.$rowcount);
                            //                         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                            //                                 array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            //                         );
                            //                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((0), $rowcount)->getFont()->setBold(true);
                            //                $columnindex = $columncount+6;
                            //                                 
                            //                         $rowcount+=2;
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                            if (count($arr_data) >= 0) {

                                foreach ($arr_data as $key => $val) {


                                    $columnindex = 0;
                                    $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                    $name = $val['latein']['EmpName'] . $empstatus;
                                    //$name=$name.$key;
                                    $user_id = $val['uc']['user_id'];
                                    $branch = $val['br']['branch_name'];
                                    $id = $val['ei']['emp_id'];
                                    $c_id = $val['ei']['employee_id'];
                                    $dep = $val['ei']['department'];
                                    $des = $val['ei']['designation'];
                                    $join = $val['ei']['joining_date'];
                                    $termin = $val['te']['last_approved_working_date'];
                                    $date = $val['latein']['LogDate'];
                                    $dateatt = date("d-m-Y", strtotime($date));
                                    $location = $val['0']['Location'];
                                    $lateinlimit = $val['latein']['LateInLimit'];

                                    $shiftstart = $val['latein']['ShiftTime'];

                                    $intime = $val['latein']['InTime'];
                                    $latetime = $val['latein']['LateTime'];
                                    $latetimeatt = date('H:i:s', strtotime($latetime));


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $c_id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $dep);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $des);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin);


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $dateatt);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $location);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $shiftstart);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $lateinlimit);

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $intime);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowcount, $latetimeatt);


                                    $columnindex = $columnindex + 14;


                                    $rowcount++;
                                    $i = $i + 1;
                                }
                            }
                            $rowcount1 = $rowcount + 1;
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('B4:O400')
                                ->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                    }

                    if ($i == 1) {
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under this selected criteria. ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:O3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                    }
                    // }

                    $objPHPExcel->getActiveSheet()->setTitle(' Late IN');
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
                    $this->render('lateinreport');
                    break;
            }
        }
    }
         
          private function generatelateoutereport($mode)
    {
        $arr_form_data = $_REQUEST;
        //       debug($arr_form_data);
        // $this->LateOut->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code')); //company_code

//Edited by ASHIN on 19-11-24
  $selected_month = date('Y-m', strtotime($arr_form_data['reportfrom']));
  $this->set('from', $selected_month); 
   // Calculate the start date (26th of the previous month)
   $start_date = date('Y-m-d', strtotime($selected_month . ' -1 month +25 days'));
   // Calculate the end date (25th of the selected month)
   $end_date = date('Y-m-d', strtotime($selected_month . ' +24 days'));
         $this->set('start_date', $start_date);
         $this->set('end_date', $end_date);

        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $this->set('from', $from);

        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $from_date = date('d-m-Y', strtotime($arr_form_data['reportfrom']));
        $this->set('from_date', $from_date);

        $arr_lateindata = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_lateoutdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        if ($str_criteria_item == '') {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            // debug($arr_form_data);     
            $conditions = " ed.status in('1','2')";
            // debug($conditions);
        } else {
            $conditions = " ed.status ='1'";
        }

        // $conditions1 = ' and latein.LogDate between "'.$fromdt.'" and "'.$todt.'"';
        //        $conditions=array();
        $arr_lateoutdata_for_template = array();
        if (isset($arr_lateoutdata) && !empty($arr_lateoutdata)) {
            foreach ($arr_lateoutdata as $lateoutdata) {
  //edited by athira on 17-07-2025
                if ($company_code == 'HRBL') {
                    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                        $arr_lateoutdata = $this->EmployeeDetails->query("select user.user_id,lateout.emp_pkey,lateout.EmpName,lateout.LogDate,COALESCE(NULLIF(lateout.Location,''),br.branch_name) Location,
            lateout.OffDutyTime,lateout.OutTime,lateout.LateOutTime,br.branch_name,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
            from emp_late_out lateout
                 Left join emp_details as ed on (lateout.emp_pkey = ed.emp_pkey)
                 Left join branches as br on (ed.branch_code = br.branch_code)
                 Left join employee_info as ei on (lateout.emp_pkey = ei.emp_pkey)
                 Left join user_credentials as user on (ei.emp_pkey = user.emp_fkey)
                   Left join termination as te on (lateout.emp_pkey = te.emp_fkey and te.status = 1)
                 Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions and lateout.LateOutTime > 0 and DATE(lateout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and lateout.emp_pkey = $lateoutdata order by lateout.EmpName,lateout.LogDate");
                    } else {
                        $arr_lateoutdata = $this->EmployeeDetails->query("select user.user_id,lateout.emp_pkey,lateout.EmpName,lateout.LogDate,COALESCE(NULLIF(lateout.Location,''),br.branch_name) Location,
            lateout.OffDutyTime,lateout.OutTime,lateout.LateOutTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
            from emp_late_out lateout
                 Left join emp_details as ed on (lateout.emp_pkey = ed.emp_pkey)
                 Left join branches as br on (ed.branch_code = br.branch_code)
                 Left join employee_info as ei on (lateout.emp_pkey = ei.emp_pkey)
                 Left join user_credentials as user on (ei.emp_pkey = user.emp_fkey)
                 Left join termination as te on (lateout.emp_pkey = te.emp_fkey and te.status = 1)
                 Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions and lateout.LateOutTime > 0 and  DATE(lateout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$lateoutdata' order by lateout.EmpName,lateout.LogDate");
                    }
                } else {
                    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                        $arr_lateoutdata = $this->EmployeeDetails->query("select user.user_id,lateout.emp_pkey,lateout.EmpName,lateout.LogDate,COALESCE(NULLIF(lateout.Location,''),br.branch_name) Location,
        lateout.OffDutyTime,lateout.OutTime,lateout.LateOutTime,br.branch_name,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
        from emp_late_out lateout
             Left join emp_details as ed on (lateout.emp_pkey = ed.emp_pkey)
             Left join branches as br on (ed.branch_code = br.branch_code)
             Left join employee_info as ei on (lateout.emp_pkey = ei.emp_pkey)
             Left join user_credentials as user on (ei.emp_pkey = user.emp_fkey)
               Left join termination as te on (lateout.emp_pkey = te.emp_fkey and te.status = 1)
             Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
             where $conditions and lateout.LateOutTime > 0 and DATE(lateout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and lateout.emp_pkey = $lateoutdata order by lateout.EmpName,lateout.LogDate");
                    } else {
                        $arr_lateoutdata = $this->EmployeeDetails->query("select user.user_id,lateout.emp_pkey,lateout.EmpName,lateout.LogDate,COALESCE(NULLIF(lateout.Location,''),br.branch_name) Location,
        lateout.OffDutyTime,lateout.OutTime,lateout.LateOutTime,br.branch_name,br.branch_code,ed.status ,uc.user_id,ei.emp_id,ei.employee_id,ei.designation,ei.department,ei.joining_date,te.last_approved_working_date
        from emp_late_out lateout
             Left join emp_details as ed on (lateout.emp_pkey = ed.emp_pkey)
             Left join branches as br on (ed.branch_code = br.branch_code)
             Left join employee_info as ei on (lateout.emp_pkey = ei.emp_pkey)
             Left join user_credentials as user on (ei.emp_pkey = user.emp_fkey)
             Left join termination as te on (lateout.emp_pkey = te.emp_fkey and te.status = 1)
             Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
             where $conditions and lateout.LateOutTime > 0 and  DATE(lateout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$lateoutdata' order by lateout.EmpName,lateout.LogDate");
                    }
                }
                //end
                if (!empty($arr_lateoutdata)) {
                    $arr_lateoutdata_for_template[] = array(
                        'summary' => $arr_lateoutdata,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
            }
            //debug($arr_lateoutdata_for_template);
            // exit;
            // debug($arr_earlyindata_for_template);
            //              foreach ($arr_earlyindata_for_template as $key => $value) {
            //                  foreach($value['summary'] as $ky => $vaal){
            //            $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
            //            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
            //            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
            //
            //            $arr_earlyindata_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
            //            }
            //        }
            // debug($resp_register);
            //debug($arr_leavepolicydetails_for_template);  

            $this->set('arr_lateoutdata_for_template', $arr_lateoutdata_for_template);

            //Set informations needed for report

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);

            function num2alpha($n)
            {
                $r = '';
                for ($i = 1; $n >= 0 && $i < 10; $i++) {
                    $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
                    $n -= pow(26, $i);
                }
                return $r;
            }
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('lateoutreport');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeLateOutReport.pdf', 'D');
                    $this->render('lateoutreport');
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $user_id . "LateOUT" . $from_date . ".xlsx" : "LateOUT" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Late OUT- " . $from_date);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:N1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:N2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    // if (empty($arr_lateoutdata_for_template[0]['summary'])){
                    //     //  echo "<h3>No Data Available With The Selected Criteria</h3>";

                    //      //print nodata
                    //      $worksheet->setCellValueByColumnAndRow(0, 3, "No data available with the selected criteria ");
                    //      $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                    //   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    //   $worksheet->mergeCells('A3:N3');
                    //   $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                    //           array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    //   );

                    //     }
                    //     else{

                    $rowcount = 3;
                    $columncount = 0;
                    //              

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Company ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Location');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Shift End Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'OUT Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Late OUT Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                    $rowcount += 1;
                    $i = 1;
                    $date1 = (date("Y-m", strtotime($f)));
                    foreach ($arr_lateoutdata_for_template as $value) {
                        if (isset($value['summary']['0']['lateout']['EmpName'])) {

                            //                              $rowcount++;
                            //                              $names=isset($value['summary']['0']['earlyin']['EmpName']) ? $value['summary']['0']['earlyin']['EmpName'] : '' ;
                            //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,"Early In Report of ".$names);
                            $arr_data = $value['summary'];
                            //                         $worksheet->mergeCells('A'.$rowcount.':F'.$rowcount);
                            //                         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                            //                                 array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            //                         );
                            //                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((0), $rowcount)->getFont()->setBold(true);
                            //                $columnindex = $columncount+6;
                            //                                 
                            //                         $rowcount+=2;
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                            if (count($arr_data) >= 0) {

                                foreach ($arr_data as $key => $val) {
                                    // debug($employee);
                                    // debug($val);
                                    // exit;
                                    $columnindex = 0;
                                    $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                    $name = $val['lateout']['EmpName'] . $empstatus;
                                    //$name=$name.$key;
                                    $id = $val['ei']['employee_id'];
                                    $c_id = $val['user']['user_id'];
                                    $dep = $val['ei']['department'];
                                    $des = $val['ei']['designation'];
                                    $joiningDate = $val['ei']['joining_date'];
                                    $join = date('d-m-Y', strtotime($joiningDate));
                                    $terminn = $val['te']['last_approved_working_date'];
                                    if (!empty($terminn)) {
                                        $termin = date('d-m-Y', strtotime($terminn));
                                    } else {
                                        $termin = '';
                                    }
                                    $user_id = $val['uc']['user_id'];
                                    $branch = $val['br']['branch_name'];
                                    $date = $val['lateout']['LogDate'];
                                    $dateatt = date("d-m-Y", strtotime($date));
                                    $location = $val['0']['Location'];
                                    $lateinlimit = $val['lateout']['OffDutyTime'];
                                    $intime = $val['lateout']['OutTime'];
                                    $latetime = $val['lateout']['LateOutTime'];
                                    $latetimeatt = date('H:i:s', strtotime($latetime));


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $c_id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $dep);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $des);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $dateatt);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $location);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $lateinlimit);

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $intime);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $latetimeatt);


                                    $columnindex = $columnindex + 12;


                                    $rowcount++;
                                    $i = $i + 1;
                                }
                            }
                            $objPHPExcel->getActiveSheet()
                                ->getStyle('A4:N4000')
                                ->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                    }
                    if ($i == 1) {
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under this selected criteria. ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:O3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                    }

                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    //Border style
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )
                        )
                    );

                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'N' . ($rowcount - 1))->applyFromArray($styleArray);

                    $objPHPExcel->getActiveSheet()->setTitle('Late OUT ');
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
                    $this->render('lateoutreport');
                    break;
            }
        }
    } 
         

private function generatestatusreport($mode) {
        $arr_form_data = $_REQUEST;
//       debug($arr_form_data);
       $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
       $this->DbConfig->useDbConfig = $this->Session->read('ds');
       $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
         $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);

       $status_type = $arr_form_data['status_type'];

       $company_code = $this->Session->read('company_code'); //company_code

       $arr_statausdata = array();
       $int_criterias_count = $arr_form_data['hidden-criterias-count'];

       for ($i = 1; $i <= $int_criterias_count; $i++) {
           $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
//           debug($str_criteria_item);
           $arr_statausdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
       }

       if ($str_criteria_item == '') {
           echo "<h1>No Criteria Selected</h1>";
           die();
       }

       if (!isset($arr_form_data[$str_criteria_item])) {
           echo "<h1>No Criteria Selected</h1>";
           die();
       }
       if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
           // debug($arr_form_data);    
           $conditions = " ed.status in('1','2')";
           // debug($conditions);
       } else {
           $conditions = " ed.status ='1'";
       }

       switch($status_type) {
           case 'P/P' : $conditions .= " AND  AD.present  LIKE '%P/P%' "; break;
           case 'P/A' : $conditions .= " AND  AD.present LIKE '%P/A%' "; break;
           case 'A/P' : $conditions .= " AND  AD.present LIKE '%A/P%' "; break;
           case 'A/A' : $conditions .= " AND  AD.present LIKE '%A/A%' "; break;
           case 'LOP/LOP' : $conditions .= "  AND (AD.leaves LIKE '%LOP%' OR AD.others LIKE '%LOP%') "; break;
           case 'BLANK' :
               $conditions .= " AND AD.present IS NULL AND AD.weekoff IS NULL AND AD.leaves IS NULL AND AD.holiday IS NULL AND AD.others IS NULL ";
               break;
           case 'WFH/WFH' : $conditions .= " AND AD.others LIKE '%WFH%' " ; break;
           case 'LEAVE' : $conditions .= " AND (AD.leaves LIKE '%SL%' OR AD.leaves LIKE '%CL%' OR AD.leaves LIKE '%ML%' OR AD.leaves LIKE '%EL%' OR AD.leaves LIKE '%AL%') "; break;
       }

       $arr_statusdata_for_template = array();
       if (isset($arr_statausdata) && !empty($arr_statausdata)) {

//            foreach ($arr_statausdata as $statusdata) {

//                  if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

//                $arr_result = $this->EmployeeDetails->query("SELECT ed.emp_pkey,ed.first_name,ed.middile_name,ed.last_name,ed.status,
// AD.att_date,AD.att_in_time,AD.att_out_time,AD.duration,AD.present,AD.weekoff,AD.leaves,AD.holiday,AD.others,ei.emp_id,ei.branch,
// ei.department,ei.designation,ei.joining_date,ei.employee_id,hg.HOLIDAY_GROUP_NAME,lg.LEAVEPOLICY_GROUP_NAME,wp.day_time_desc,
// te.last_approved_working_date,ss.structure_name
// FROM emp_detail_timeattandance AD
// LEFT JOIN emp_details ed ON (AD.emp_pkey = ed.emp_pkey)
// LEFT JOIN emp_proff ep ON (AD.emp_pkey = ep.emp_fkey)
// LEFT JOIN employee_info ei ON (ed.emp_pkey = ei.emp_pkey)    
// LEFT JOIN termination te ON (ed.emp_pkey = te.emp_fkey  and te.status = 1)
// LEFT JOIN holiday_group hg ON (ep.HOLIDAY_GROUP_ID = hg.HOLIDAY_GROUP_ID and hg.status =1)    
// LEFT JOIN salary_structure ss ON (ep.structure_id = ss.structure_id and ss.structure_active = 1)
// LEFT JOIN leavepolicy_group lg ON (ep.LEAVEPOLICY_GROUP_ID = lg.LEAVEPOLICY_GROUP_ID and lg.status =1)    
// LEFT JOIN working_day_time_procedures  wp ON (ep.day_time_seq = wp.day_time_seq and wp.active =1)
//                WHERE $conditions and AD.yearmonth LIKE '%$from%' and ed.emp_pkey = $statusdata"); 
//            }
//                else{

//                   $arr_result = $this->EmployeeDetails->query("SELECT ed.emp_pkey,ed.first_name,ed.middile_name,ed.last_name,ed.status,
// AD.att_date,AD.att_in_time,AD.att_out_time,AD.duration,AD.present,AD.weekoff,AD.leaves,AD.holiday,AD.others,ei.emp_id,ei.branch,
// ei.department,ei.designation,ei.joining_date,ei.employee_id,hg.HOLIDAY_GROUP_NAME,lg.LEAVEPOLICY_GROUP_NAME,wp.day_time_desc,
// te.last_approved_working_date,ss.structure_name
// FROM emp_detail_timeattandance AD
// LEFT JOIN emp_details ed ON (AD.emp_pkey = ed.emp_pkey)
// LEFT JOIN emp_proff ep ON (AD.emp_pkey = ep.emp_fkey)
// LEFT JOIN employee_info ei ON (ed.emp_pkey = ei.emp_pkey)    
// LEFT JOIN termination te ON (ed.emp_pkey = te.emp_fkey  and te.status = 1)
// LEFT JOIN holiday_group hg ON (ep.HOLIDAY_GROUP_ID = hg.HOLIDAY_GROUP_ID and hg.status =1)    
// LEFT JOIN salary_structure ss ON (ep.structure_id = ss.structure_id and ss.structure_active = 1)
// LEFT JOIN leavepolicy_group lg ON (ep.LEAVEPOLICY_GROUP_ID = lg.LEAVEPOLICY_GROUP_ID and lg.status =1)    
// LEFT JOIN working_day_time_procedures  wp ON (ep.day_time_seq = wp.day_time_seq and wp.active =1)
//                WHERE $conditions and AD.yearmonth LIKE '%$from%' and ed.branch_code = '$statusdata'"); 

//                }

               
//                if($arr_result && count($arr_result)) {
//                    $arr_statusdata_for_template[] = array(
//                        'summary' => $arr_result,
//                    );
//                }
               
//            }
//edited by athira on 17-07-2025
foreach ($arr_statausdata as $statusdata) {
    if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
        // Single emp_pkey case
        $emp_pkeys = [$statusdata];
    } else {
        // Fetch all emp_pkeys for the given branch_code
        $emp_pkeys_data = $this->EmployeeDetails->query("SELECT emp_pkey FROM emp_details WHERE branch_code = '$statusdata'");
        $emp_pkeys = array_map(function ($row) {
            return $row['emp_details']['emp_pkey'];
        }, $emp_pkeys_data);
    }

    // Now run the attendance query for each emp_pkey
    foreach ($emp_pkeys as $emp_pkey) {
        $arr_result = $this->EmployeeDetails->query("
            SELECT ed.emp_pkey, ed.first_name, ed.middile_name, ed.last_name, ed.status,
                   AD.att_date, AD.att_in_time, AD.att_out_time, AD.duration, AD.present, AD.weekoff,
                   AD.leaves, AD.holiday, AD.others, ei.emp_id, ei.branch, ei.department,
                   ei.designation, ei.joining_date, ei.employee_id, hg.HOLIDAY_GROUP_NAME,
                   lg.LEAVEPOLICY_GROUP_NAME, wp.day_time_desc, te.last_approved_working_date,
                   ss.structure_name
            FROM emp_detail_timeattandance AD
            LEFT JOIN emp_details ed ON (AD.emp_pkey = ed.emp_pkey)
            LEFT JOIN emp_proff ep ON (AD.emp_pkey = ep.emp_fkey)
            LEFT JOIN employee_info ei ON (ed.emp_pkey = ei.emp_pkey)
            LEFT JOIN termination te ON (ed.emp_pkey = te.emp_fkey AND te.status = 1)
            LEFT JOIN holiday_group hg ON (ep.HOLIDAY_GROUP_ID = hg.HOLIDAY_GROUP_ID AND hg.status = 1)
            LEFT JOIN salary_structure ss ON (ep.structure_id = ss.structure_id AND ss.structure_active = 1)
            LEFT JOIN leavepolicy_group lg ON (ep.LEAVEPOLICY_GROUP_ID = lg.LEAVEPOLICY_GROUP_ID AND lg.status = 1)
            LEFT JOIN working_day_time_procedures wp ON (ep.day_time_seq = wp.day_time_seq AND wp.active = 1)
            WHERE $conditions
              AND AD.yearmonth BETWEEN att_start_end_fn(CONCAT('$from','-01'), 1) 
                                   AND att_start_end_fn(CONCAT('$from','-01'), 2)
              AND ed.emp_pkey = $emp_pkey
        ");

        if (!empty($arr_result)) {
            $arr_statusdata_for_template[] = ['summary' => $arr_result];
        }
    }
}
// end
           // debug($arr_statusdata_for_template);
           // exit;

           $this->set('arr_statusdata_for_template', $arr_statusdata_for_template);

           //Set informations needed for report

           $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name = $this->Session->read('user_name');
           $this->set('user_name', $user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info', $arr_comp_contact_info);
             $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time=strtotime($f);
        $month=date("m",$time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month.'-01'; 
        $year=date("Y",$time);
        $this->set('mname', $mname);
        $this->set('year', $year);

           switch ($mode) {
               case 'pdf' :
                   //echo "entered in";die();
                   $this->set('mode', 'pdf');
                   $view = new View($this, false);
                   $view_output = $view->render('statusreport');
                   //   debug($view_output);
                   App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                   $html2pdf = new HTML2PDF('P', 'A2', 'en');
                   //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                   //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                   $html2pdf->pdf->SetDisplayMode('fullpage');
                   $html2pdf->writeHTML($view_output);
                   $html2pdf->Output('AttendanceStatusReport.pdf', 'D');
                   $this->render('statusreport');
                   break;
               case 'excel' :

                   $str_company_code = $this->Session->read('company_code');
                   $file_name = isset($str_company_code) ? $str_company_code . "AttendanceStatusReport" . $from . ".xlsx" : "AttendanceStatusReport" . strtotime() . ".xlsx";

                   App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                   $objPHPExcel = new PHPExcel();

                   $objPHPExcel->getProperties()->setCreator("Administrator");
                   $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                   $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                   $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                   $objPHPExcel->getProperties()->setDescription("Attendance Status Report By Forsight");

                   $objPHPExcel->setActiveSheetIndex(0);

                   $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Attendance Status - " .$mname." "  .$year);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:K1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                   for ($col = 'A'; $col !== 'Z'; $col++) {
                       $objPHPExcel->getActiveSheet()
                               ->getColumnDimension($col)
                               ->setAutoSize(true);
                   }

                   $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:K2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                  if (count($arr_statusdata_for_template) == 0){
    //  echo "<h3>No Data Available With The Selected Criteria</h3>";

     //print nodata
     $worksheet->setCellValueByColumnAndRow(0, 3, "No data available ");
     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
  $worksheet->mergeCells('A3:I3');
  $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
          array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
  );

    }
  else{

 $worksheet->setCellValueByColumnAndRow(0, 3, "Employee Details");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(12);
                $worksheet->mergeCells('A3:I3');
                $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
$worksheet->setCellValueByColumnAndRow(9, 3, "Allocated Policies");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, 3)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, 3)->getFont()->setSize(12);
                $worksheet->mergeCells('J3:M3');
                $worksheet->getStyle('J3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(13, 3, "Attendance Details");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, 3)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, 3)->getFont()->setSize(12);
                $worksheet->mergeCells('N3:W3');
                $worksheet->getStyle('N3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                               

                    $columncount = 0;

                    $rowcount = 4;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Shift Policy');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Leave Policy');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Holiday');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Salary Structure');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Attendance Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'In Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Out Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'Duration');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'Present');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 18), $rowcount, 'Weekoff');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 18), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 19), $rowcount, 'Leaves');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 19), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 20), $rowcount, 'Holiday');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 20), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 21), $rowcount, 'Other');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 21), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 22), $rowcount, 'Status');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 22), $rowcount)->getFont()->setBold(true);
                    $rowcount+=1;
                    $i = 1;
                    foreach ($arr_statusdata_for_template as $value) {


                            $arr_data  = $value['summary'];
                            if (count($arr_data) >= 0) {

                                foreach ($arr_data as $key => $val) {
                                    // debug($val);exit();

                                    $columnindex = 0;
                                    $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : '';
                                    
                                    $name = $val['ed']['first_name'] .' '. $val['ed']['middile_name'] .' '. $val['ed']['last_name'] . $empstatus;

                                    $att_date = $val['AD']['att_date'];
                                    $att_in_time = $val['AD']['att_in_time'];
                                    $att_out_time = $val['AD']['att_out_time'];
                                    $emp_id=$val['ei']['emp_id'];
                                    $company_id=$val['ei']['employee_id'];
                                    $branch=$val['ei']['branch'];
                                    $dep=$val['ei']['department'];
                                    $join=$val['ei']['joining_date'];
                                    $designation=$val['ei']['designation'];
                                    $termin = $val['te']['last_approved_working_date'];

                                    $shift=$val['wp']['day_time_desc'];
                                    $lev=$val['lg']['LEAVEPOLICY_GROUP_NAME'];
                                    $holi=$val['hg']['HOLIDAY_GROUP_NAME'];
                                    $salstructure = $val['ss']['structure_name'];


                                    $attendance_date = date("d-m-Y", strtotime($att_date));
                                    $attendance_in_time = ($att_in_time) ? date("d-m-Y H:i:s", strtotime($att_in_time)) : '';
                                    $attendance_out_time = ($att_out_time) ? date("d-m-Y H:i:s", strtotime($att_out_time)) : '';

                                    $all_status = ($val['AD']['present']) ? $val['AD']['present'] : '';
                                    $all_status .= ($val['AD']['weekoff']) ? " ".$val['AD']['weekoff'] : '';
                                    $all_status .= ($val['AD']['leaves']) ? " ".$val['AD']['leaves'] : '';
                                    $all_status .= ($val['AD']['holiday']) ? " ".$val['AD']['holiday'] : '';
                                    $all_status .= ($val['AD']['others']) ? " ".$val['AD']['others'] : '';

         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i);
          $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $emp_id);
           $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $company_id);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join); 
         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
          $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $dep);
           $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $designation);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $termin);                           

$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $val['wp']['day_time_desc']);
          $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $lev);
           $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $holi);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $salstructure);                           

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $attendance_date);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowcount, $attendance_in_time);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 15) . $rowcount, $attendance_out_time);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 16) . $rowcount, $val['AD']['duration']);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 17) . $rowcount, $val['AD']['present']);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 18) . $rowcount, $val['AD']['weekoff']);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 19) . $rowcount, $val['AD']['leaves']);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 20) . $rowcount, $val['AD']['holiday']);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 21) . $rowcount, $val['AD']['others']);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 22) . $rowcount, $all_status);


                                    $columnindex = $columnindex + 11;


                                    $rowcount++;
                                    $i = $i + 1;
                                }
                            }}
                            // $rowcount1 = $rowcount + 1;
                    }
                        
                           $BStyle = array(
                             'borders' => array(
                             'allborders' => array(
                             'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                               )
                            );
     
                        // $row = $rowcount - 1;
                        // $objPHPExcel->getActiveSheet()->getStyle('A1:W'.$row)->applyFromArray($BStyle);
                        

                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Status');
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
                    $this->render('statusreport');
                    break;
            }
        }
    }

          private function generatemisspunchreport($mode){
        $arr_form_data = $_REQUEST;
//       debug($arr_form_data);
        $this->Latein->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date('Y-m');
    $attendance_date = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:0;
        $att_enddate = date('d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',  strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',strtotime('-1 months',strtotime($month)))))))));
    $arr_date_in_selectedmonth = range(1, $att_enddate);        
        if($att_startdate != 1){
            $arr_date_in_prevmonth = range($att_startdate, date('t',strtotime('-1 months',strtotime($month))));
        }else{
            $arr_date_in_prevmonth = array();
        }
        
        $arr_dates = array_merge($arr_date_in_prevmonth,$arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);
      $fd=$arr_form_data['reportfrom'].' '.'00:00:00';
          if((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom']!= '')){
            $report_month = $arr_form_data['reportfrom'];
            $from =($arr_form_data['reportfrom']);
            $to = ($arr_form_data['reportto']);
            $fromdt =$arr_form_data['reportfrom'].' '.'00:00:00';
            $todt = $arr_form_data['reportto'].' '.'23:59:59';
        }
        $arr_lateoutdata = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
//           debug($str_criteria_item);
                $arr_empmisspunches = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        
                if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
            echo "<h1>No Criteria Selected</h1>";
                die();
            }
    if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1'){
                // debug($arr_form_data);     
            $conditions = " and ed.status in('1','2')";  
            // debug($conditions);
         }
         else{
              $conditions = " and ed.status ='1'";  
         }

         // $conditions1 = ' and edt.att_date between "'.$fromdt.'" and "'.$todt.'"';
//        $conditions=array();
        $arr_empmisspunches_for_template = array();
        if(isset($arr_empmisspunches) && !empty($arr_empmisspunches)){
            
        foreach ($arr_empmisspunches as $misspunches) {
//debug($arr_form_data);
       
//         debug($from);
            $arr_empmisspunches = $this->EmployeeDetails->query("select ed.emp_id,concat(ed.first_name,' ',ifnull(ed.last_name,'')) as fullname,ed.branch_name,ed.status,br.branch_name, yearmonth, count(*) misscount
     from emp_detail_timeattandance edt ,emp_details ed 
     Left join branches as br on (ed.branch_code = br.branch_code)
     where ed.emp_pkey=edt.emp_pkey
    and present<>'P/P' and present is not null and date_format(current_date,'%y-%m') =date_format(yearmonth,'%y-%m') $conditions and edt.att_date between '$fromdt' and '$todt' and edt.emp_pkey = $misspunches
    group by edt.emp_pkey,ed.first_name,yearmonth order by 3 desc  ");
          // debug($arr_empmisspunches);
     $arr_empmisspunches_for_template[] = array(
                'summary'=>$arr_empmisspunches,
               // 'employees'=>$arr_leavepolicy_employees
            );
     
        }
//debug($arr_earlyindata_for_template);
//              foreach ($arr_earlyindata_for_template as $key => $value) {
//                  foreach($value['summary'] as $ky => $vaal){
//            $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
//            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
//            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
//
//            $arr_earlyindata_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
//            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
//            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
//            }
//        }
       // debug($resp_register);
   //debug($arr_leavepolicydetails_for_template);  
    
        $this->set('arr_empmisspunches_for_template', $arr_empmisspunches_for_template);
        
        //Set informations needed for report
    
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);  
        switch ($mode){
         
            case 'pdf' : 
                //echo "entered in";die();
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('misspunchesreport');
           //   debug($view_output);
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                // $html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                // $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeLateOutReport.pdf', 'D');
//                $this->render('lateinreport');                
                break;
            case 'excel' :
                
              $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."LateInTime".$month.".xlsx":"LateOutTime".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Late Out Report - ".$from." to ".$to."");
                   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                
                $rowcount = 2;
                $columncount=0;
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Employee Name');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Id');
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Branch');
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Date');
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Direction');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
//                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Comments');
//                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
//                $columnindex = $columncount+9;
                                
                $rowcount = 2;  
                                              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Sl No. ');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, ' User ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Branch Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Location');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Shift Out Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Out Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Late Out Time');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $rowcount+=1;
                          $i = 1;  
                      foreach($arr_lateindata_for_template as $value){
                          if(isset($value['summary']['0']['lateout']['EmpName']))
                {
                              
//                              $rowcount++;
//                              $names=isset($value['summary']['0']['earlyin']['EmpName']) ? $value['summary']['0']['earlyin']['EmpName'] : '' ;
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,"Early In Report of ".$names);
                         $arr_data  = $value['summary'];
//                         $worksheet->mergeCells('A'.$rowcount.':F'.$rowcount);
//                         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
//                                 array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                         );
//                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((0), $rowcount)->getFont()->setBold(true);
                       

               
//                $columnindex = $columncount+6;
                        
                        
//                                 
//                         $rowcount+=2;
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        if(count($arr_data)>=0){
                            
                            foreach($arr_data as $key=>$val){
                               
                            
                                      $columnindex = 0;
                            $empstatus = isset($val['ed']['status']) && $val['ed']['status']=="2" ? '(Resigned)':'' ;         
                            $name=$val['lateout']['EmpName'].$empstatus;
                            //$name=$name.$key;
                            $user_id=$val['uc']['user_id'];
                            $branch=$val['br']['branch_name'];
                            $date=$val['lateout']['LogDate'];
                            $dateatt = date("d-m-Y", strtotime($date));
                            $location = $val['lateout']['Location'] ;
                              
                            $lateinlimit=$val['lateout']['LateInLimit']; 
                            
                            $intime=$val['lateout']['InTime'];
                            $latetime=$val['lateout']['LateTime']; 
                            $latetimeatt = date('H:i:s', strtotime($latetime));
                    
                            
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$i );
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$user_id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$branch);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$dateatt);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$location);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).$rowcount,$lateinlimit);
                           
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$intime);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$latetimeatt);
                       
                           
                            $columnindex=$columnindex+8;
                                            
                            
                          $rowcount++;
                            $i = $i+1; 
                            }
                               
                        }
                        $rowcount1=$rowcount+1;
                      }}
             
                $objPHPExcel->getActiveSheet()->setTitle('Employee Late In Report');
    /* header footer */
                                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                               /* header footer */
                
                /*print Set up*/
                 $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /*print Set up*/
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__)."/".$file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename='.$file_name);                        

                readfile(dirname(__FILE__)."/".$file_name);
                unlink(dirname(__FILE__)."/".$file_name);
                break;
            default : 
                $this->set('mode','');
                $this->render('misspunchesreport');
                break;
        
        }  

   }
         }
           private function generateearlyindurationreport($mode){
        $arr_form_data = $_REQUEST;
      // debug($arr_form_data);

        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $company_code = $this->Session->read('company_code'); //company_code
       
        $arr_earlyindata = array();  
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
//           debug($str_criteria_item);
                $arr_earlyindata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        
                if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
            echo "<h1>No Criteria Selected</h1>";
                die();
            }
    if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1'){
                // debug($arr_form_data);     
            $conditions = "ed.emp_status in('1','2')";  
            // debug($conditions);
         }
         else{
              $conditions = "ed.emp_status ='1'";  
         }

        
         // $conditions = 'earlyin.LogDate between "'.$from.'" and "'.$to.'"';
//        $conditions=array();
        $arr_earlyindata_for_template = array();
        if(isset($arr_earlyindata) && !empty($arr_earlyindata)){
            
        foreach ($arr_earlyindata as $earlyindata) {
        if ($arr_form_data['select-criteria1'] == 'Units') {

//         debug($from);
       $arr_earlyindata= $this->EmployeeDetails->query("select earlyin.emp_pkey,earlyin.EmpName,earlyin.LogDate,COALESCE(NULLIF(earlyin.Location,''),br.branch_name) Location,
            earlyin.SharpInTime,earlyin.InTime, SEC_TO_TIME( SUM(time_to_sec(`EarlyInTime`)))
As timesum,br.branch_name,ed.emp_status,uc.user_id,ed.designation,department,employee_id,emp_id,cd.emp_company_id,branch,last_approved_working_date 
            from emp_early_in earlyin 
                 Left join employee_info as ed on (earlyin.emp_pkey = ed.emp_pkey)
                  Left join emp_proff as cd on (earlyin.emp_pkey = cd.emp_fkey)
                  Left join termination as edd on (earlyin.emp_pkey = edd.emp_fkey)
                 Left join branches as br on (ed.branch_code = br.branch_code)
                 Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions and DATE(earlyin.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and ed.branch_code = '$earlyindata' GROUP BY emp_id");
       // debug($arr_earlyindata);exit();

   }
       else{

       $arr_earlyindata= $this->EmployeeDetails->query("select earlyin.emp_pkey,earlyin.EmpName,earlyin.LogDate,COALESCE(NULLIF(earlyin.Location,''),br.branch_name) Location,
            earlyin.SharpInTime,earlyin.InTime, SEC_TO_TIME( SUM(time_to_sec(`EarlyInTime`)))
As timesum,br.branch_name,ed.emp_status,uc.user_id,ed.designation,department,employee_id,emp_id,emp_company_id,branch,last_approved_working_date 
            from emp_early_in earlyin 
                 Left join employee_info as ed on (earlyin.emp_pkey = ed.emp_pkey)
                  Left join emp_proff as cd on (earlyin.emp_pkey = cd.emp_fkey)
                  Left join termination as edd on (earlyin.emp_pkey = edd.emp_fkey)
                 Left join branches as br on (ed.branch_code = br.branch_code)
                 Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                 where $conditions and DATE(earlyin.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and  earlyin.emp_pkey = $earlyindata GROUP BY emp_id");
       }
  // DATEADD(ms, SUM(DATEDIFF(ms, '00:00:00.000', mytime)), '00:00:00.000') as time
//        sum(datediff(minute, 0, TotalHours)) / 60.0 as hours_worked
// from Table2


    
  // debug($arr_earlyindata1);
       if(!empty($arr_earlyindata)){
        $arr_earlyindata_for_template[] = array(
                'summary'=>$arr_earlyindata,
               // 'employees'=>$arr_leavepolicy_employees
            );
     
        }
        }
     
        $this->set('arr_earlyindata_for_template', $arr_earlyindata_for_template);
          $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        date_default_timezone_set('Asia/Kolkata');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time=strtotime($f);
        $month=date("m",$time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month.'-01'; 
        $year=date("Y",$time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y H:i');
        
        //Set informations needed for report
    
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);  
           switch ($mode){
            case 'pdf' : 
                //echo "entered in";die();
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('earlyinduration');
           //   debug($view_output);
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeEarlyIndurationReport.pdf', 'D');
               // $this->render('earlyinreport');                
                break;
            case 'excel' :
                
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_EmployeeEarlyInReport.xlsx":"AttendanceA";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Check IN Duration of " .$mname." "  .$year);
                   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:I1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:I2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                
                
                //print nodata
                if (count($arr_earlyindata_for_template) == 0){
                    //  echo "<h3>No Data Available With The Selected Criteria</h3>";

                     //print nodata
                     $worksheet->setCellValueByColumnAndRow(0, 3, "No data available ");
                     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                  $worksheet->mergeCells('A3:I3');
                  $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                          array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                  );
    
                    }
                    else{
                
                    $columncount=0;
                    $rowcount = 3;   
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Sl No. ');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Termination Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Early In Duration');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $rowcount+=1;
                          $i = 1;  
                          if (count($arr_earlyindata_for_template) == 0){
                     echo "<h3>No Data Available With The Selected Criteria</h3>";
                    }
                    // else{
                      foreach($arr_earlyindata_for_template as $value){
                        if(isset($value['summary']['0']['earlyin']['EmpName']))
                    
                {
                              
                         $arr_data  = $value['summary'];
//                        
                         if(count($arr_data)>=0){
                            
                            foreach($arr_data as $key=>$val){
                          
                            $columnindex = 0;
                            $empstatus = isset($val['ed']['emp_status']) && $val['ed']['emp_status']=="2" ? '(Resigned)':'' ;         
                            $name=$val['earlyin']['EmpName'].$empstatus;
                            //$name=$name.$key;
                            $emp_id=$val['ed']['emp_id'];
                            $company_id=$val['ed']['employee_id'];
                            $branch=$val['ed']['branch'];
                            $dep=$val['ed']['department'];
                            $designation=$val['ed']['designation'];
                            $date=$val['earlyin']['LogDate'];
                            $dateatt = date("d-m-Y", strtotime($date));
                            $location = $val['0']['Location'];
                            $termin = $val['edd']['last_approved_working_date'];
                            $sharpintime=$val['earlyin']['SharpInTime']; 
                            $intime=$val['earlyin']['InTime'];
                            $earlyintime=$val['0']['timesum'];

   
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$i );
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$emp_id);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$company_id);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$name);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$branch);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$dep);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).$rowcount,$designation);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$termin); 
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$earlyintime);
             
                            $columnindex=$columnindex+8;
                                            
                          $rowcount++;
                            $i = $i+1; 
                            $BStyle = array(
                             'borders' => array(
                             'allborders' => array(
                             'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                               )
                            );
                 $row=$rowcount-1;
                            $objPHPExcel->getActiveSheet()->getStyle('A1:I'.$row)->applyFromArray($BStyle);
    $objPHPExcel->getActiveSheet()
    ->getStyle('B3:B40000')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $objPHPExcel->getActiveSheet()
    ->getStyle('C3:A40000')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            }
                               
                        }
                    }
                        $rowcount1=$rowcount+1;
                      }
                //     else {
                //     $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                // }
            }
             
                $objPHPExcel->getActiveSheet()->setTitle('Employee Check IN Duration');
    /* header footer */
                                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                               /* header footer */
                
                /*print Set up*/
                 $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /*print Set up*/
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__)."/".$file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename='.$file_name);                        

                readfile(dirname(__FILE__)."/".$file_name);
                unlink(dirname(__FILE__)."/".$file_name);
                break;
            default : 
                $this->set('mode','');
                $this->render('earlyinduration');
                break;
        }  
   }
   
     }
       private function generateearlyoutdurationreport($mode){
        $arr_form_data = $_REQUEST;
      // debug($arr_form_data);

        $this->Earlyout->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $company_code = $this->Session->read('company_code'); //company_code
       
        $arr_earlyoutdata = array();  
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
//           debug($str_criteria_item);
                $arr_earlyoutdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        
                if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
            echo "<h1>No Criteria Selected</h1>";
                die();
            }
    if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1'){
                // debug($arr_form_data);     
            $conditions = "ed.emp_status in('1','2')";  
            // debug($conditions);
         }
         else{
              $conditions = "ed.emp_status ='1'";  
         }

        
         // $conditions = 'earlyin.LogDate between "'.$from.'" and "'.$to.'"';
//        $conditions=array();
        $arr_earlyoutdata_for_template = array();
        if(isset($arr_earlyoutdata) && !empty($arr_earlyoutdata)){
            
        foreach ($arr_earlyoutdata as $earlyoutdata) {
        if ($arr_form_data['select-criteria1'] == 'Units') {

//         debug($from);
$arr_earlyoutdata= $this->EmployeeDetails->query("select earlyout.emp_pkey,earlyout.EmpName,earlyout.LogDate,COALESCE(NULLIF(earlyout.Location,''),br.branch_name) Location,
earlyout.OffDutyTime,earlyout.OutTime,SEC_TO_TIME( SUM(time_to_sec(`EarlyOutTime`)))
As outsum,br.branch_name,ed.emp_status ,uc.user_id,ed.department,ed.designation,emp_id,employee_id,last_approved_working_date
from emp_early_out earlyout
    Left join employee_info as ed on (earlyout.emp_pkey = ed.emp_pkey)
     Left join termination as edd on (earlyout.emp_pkey = edd.emp_fkey)
    Left join branches as br on (ed.branch_code = br.branch_code)
    Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
    
   
    where $conditions  and earlyout.EarlyOutTime > 0 and DATE(earlyout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$earlyoutdata' GROUP BY emp_id ");
    
   }



       else{
        $arr_earlyoutdata= $this->EmployeeDetails->query("select earlyout.emp_pkey,earlyout.EmpName,earlyout.LogDate,COALESCE(NULLIF(earlyout.Location,''),br.branch_name) Location,
            earlyout.OffDutyTime,earlyout.OutTime,SEC_TO_TIME( SUM(time_to_sec(`EarlyOutTime`)))
As outsum,br.branch_name,ed.emp_status ,uc.user_id,ed.department,ed.designation,emp_id,employee_id,last_approved_working_date
           from emp_early_out earlyout
                Left join employee_info as ed on (earlyout.emp_pkey = ed.emp_pkey)
                 Left join termination as edd on (earlyout.emp_pkey = edd.emp_fkey)
                Left join branches as br on (ed.branch_code = br.branch_code)
                Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
                
               
                where $conditions  and earlyout.EarlyOutTime > 0 and DATE(earlyout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and earlyout.emp_pkey = $earlyoutdata GROUP BY emp_id ");
       }
    
       if(!empty($arr_earlyoutdata)){
        $arr_earlyoutdata_for_template[] = array(
                'summary'=>$arr_earlyoutdata,
               // 'employees'=>$arr_leavepolicy_employees
            );
     
        }
        }
        }
        $this->set('arr_earlyoutdata_for_template', $arr_earlyoutdata_for_template);
          $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        date_default_timezone_set('Asia/Kolkata');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time=strtotime($f);
        $month=date("m",$time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month.'-01'; 
        $year=date("Y",$time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y H:i');
        //Set informations needed for report
    
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);  
           switch ($mode){
            case 'pdf' : 
                //echo "entered in";die();
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('earlyoutduration');
           //   debug($view_output);
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeEarlyOutdurationReport.pdf', 'D');
               // $this->render('earlyinreport');                
                break;
            case 'excel' :
                
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_EmployeeEarlyOutReport.xlsx":"AttendanceA";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Check OUT Duration of " .$mname." "  .$year);
                   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:I1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
               $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:I2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
                if (count($arr_earlyoutdata_for_template) == 0){

                     //print nodata
                     $worksheet->setCellValueByColumnAndRow(0, 3, "No data available ");
                     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                  $worksheet->mergeCells('A3:I3');
                  $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                          array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                  );
    
                    }
                    else{

                    $columncount=0;
                    $rowcount = 3;  
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Sl No. ');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Termination Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Early Out Duration');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $rowcount+=1;
                          $i = 1;  
                     
                      foreach($arr_earlyoutdata_for_template as $value){
                        if(isset($value['summary']['0']['earlyout']['EmpName']))
                    
                {
                              
                         $arr_data  = $value['summary'];
//                        
                         if(count($arr_data)>=0){
                            
                            foreach($arr_data as $key=>$val){
                          
                            $columnindex = 0;
                            $empstatus = isset($val['ed']['emp_status']) && $val['ed']['emp_status']=="2" ? '(Resigned)':'' ;         
                            $name=$val['earlyout']['EmpName'].$empstatus;
                            //$name=$name.$key;
                            $emp_id=$val['ed']['emp_id'];
                            $company_id=$val['ed']['employee_id'];
                            $branch=$val['br']['branch_name'];
                            $dep=$val['ed']['department'];
                            $designation=$val['ed']['designation'];
                            $date=$val['earlyout']['LogDate'];
                            // $dateatt = date("d-m-Y", strtotime($date));
                            // $location = $val['0']['Location'];
                            $termin = $val['edd']['last_approved_working_date'];
                            // $sharpintime=$val['earlyout']['SharpInTime']; 
                            // $intime=$val['earlyout']['InTime'];
                            $earlyouttime=$val['0']['outsum'];


                            // $datetime = strtotime($earlyintime);
                            // $datetime = date(' H:i:s', 'earlyintime');
                           // $timestamp = $datetime->getTimestamp();
   
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$i );
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$emp_id);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$company_id);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$name);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$branch);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$dep);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).$rowcount,$designation);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$termin); 
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$earlyouttime);
             
                            $columnindex=$columnindex+8;
                                            
                          $rowcount++;
                            $i = $i+1; 
                            $BStyle = array(
                             'borders' => array(
                             'allborders' => array(
                             'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                               )
                            );
                 $row=$rowcount-1;
                            $objPHPExcel->getActiveSheet()->getStyle('A1:I'.$row)->applyFromArray($BStyle);
    $objPHPExcel->getActiveSheet()
    ->getStyle('B3:B40000')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $objPHPExcel->getActiveSheet()
    ->getStyle('C3:A40000')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            }
                               
                        }
                    }
                        $rowcount1=$rowcount+1;
                      }
                //     else {
                //     $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                // }
            }
             
                $objPHPExcel->getActiveSheet()->setTitle('Employee Check OUT Duration');
    /* header footer */
                                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                               /* header footer */
                
                /*print Set up*/
                 $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /*print Set up*/
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__)."/".$file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename='.$file_name);                        

                readfile(dirname(__FILE__)."/".$file_name);
                unlink(dirname(__FILE__)."/".$file_name);
                break;
            default : 
                $this->set('mode','');
                $this->render('earlyoutduration');
                break;
        }  
   
     }

//lateoutduration

private function generatelateoutdurationreport($mode){
    $arr_form_data = $_REQUEST;
  // debug($arr_form_data);

    $this->Lateout->useDbConfig = $this->Session->read('ds');
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    $this->DbConfig->useDbConfig = $this->Session->read('ds');
    $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
    $company_code = $this->Session->read('company_code'); //company_code
   
    $arr_lateoutdata = array();  
    $int_criterias_count = $arr_form_data['hidden-criterias-count'];
    
    for($i=1;$i<=$int_criterias_count;$i++){
        $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
//           debug($str_criteria_item);
            $arr_lateoutdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
    }
    
            if($str_criteria_item == ''){
           echo "<h1>No Criteria Selected</h1>";
            die();
        }
        
        if(!isset($arr_form_data[$str_criteria_item])){
        echo "<h1>No Criteria Selected</h1>";
            die();
        }
if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1'){
            // debug($arr_form_data);     
        $conditions = "ed.emp_status in('1','2')";  
        // debug($conditions);
     }
     else{
          $conditions = "ed.emp_status ='1'";  
     }

    
     // $conditions = 'earlyin.LogDate between "'.$from.'" and "'.$to.'"';
//        $conditions=array();
    $arr_lateoutdata_for_template = array();
    if(isset($arr_lateoutdata) && !empty($arr_lateoutdata)){
        
    foreach ($arr_lateoutdata as $lateoutdata) {
    if ($arr_form_data['select-criteria1'] == 'Units') {

//         debug($from);
$arr_lateoutdata= $this->EmployeeDetails->query("select lateout.emp_pkey,lateout.EmpName,lateout.LogDate,COALESCE(NULLIF(lateout.Location,''),br.branch_name) Location,
lateout.OffDutyTime,lateout.OutTime,SEC_TO_TIME( SUM(time_to_sec(`LateOutTime`)))
As lateoutsum,br.branch_name,ed.emp_status ,uc.user_id,emp_id,employee_id,ed.department,ed.designation,last_approved_working_date
from emp_late_out lateout
Left join employee_info as ed on (lateout.emp_pkey = ed.emp_pkey)
 Left join termination as edd on (lateout.emp_pkey = edd.emp_fkey)
Left join branches as br on (ed.branch_code = br.branch_code)
Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)


where $conditions  and lateout.LateOutTime > 0 and DATE(lateout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$lateoutdata' GROUP BY emp_id ");

}



   else{
    $arr_lateoutdata= $this->EmployeeDetails->query("select lateout.emp_pkey,lateout.EmpName,lateout.LogDate,COALESCE(NULLIF(lateout.Location,''),br.branch_name) Location,
lateout.OffDutyTime,lateout.OutTime,SEC_TO_TIME( SUM(time_to_sec(`LateOutTime`)))
As lateoutsum,br.branch_name,ed.emp_status ,uc.user_id,ed.department,ed.designation,emp_id,employee_id,last_approved_working_date
from emp_late_out lateout
Left join employee_info as ed on (lateout.emp_pkey = ed.emp_pkey)
 Left join termination as edd on (lateout.emp_pkey = edd.emp_fkey)
Left join branches as br on (ed.branch_code = br.branch_code)
Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
        
           
            where $conditions  and lateout.LateOutTime > 0 and DATE(lateout.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and lateout.emp_pkey = $lateoutdata GROUP BY emp_id ");
   }


   if(!empty($arr_lateoutdata)){
    $arr_lateoutdata_for_template[] = array(
            'summary'=>$arr_lateoutdata,
           // 'employees'=>$arr_leavepolicy_employees
        );
 
    }
    }
 
    $this->set('arr_lateoutdata_for_template', $arr_lateoutdata_for_template);
      $cr = $arr_form_data['select-criteria1'];
    $this->set('cr', $cr);
        date_default_timezone_set('Asia/Kolkata');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time=strtotime($f);
        $month=date("m",$time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month.'-01'; 
        $year=date("Y",$time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y H:i');
    //Set informations needed for report

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
       $user_name= $this->Session->read('user_name');
         $this->set('user_name',$user_name);
       $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
       $this->set('arr_comp_contact_info',$arr_comp_contact_info);  
       switch ($mode){
        case 'pdf' : 
            //echo "entered in";die();
            $this->set('mode','pdf');
            $view = new View($this, false);
            $view_output = $view->render('lateoutduration');
       //   debug($view_output);
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

            $html2pdf = new HTML2PDF('P', 'A2', 'en');
            //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
            //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($view_output);
            $html2pdf->Output('EmployeeLateOutdurationReport.pdf', 'D');
           // $this->render('earlyinreport');                
            break;
        case 'excel' :
            
            $str_company_code   =   $this->Session->read('company_code');
            $file_name  = isset($str_company_code)?$str_company_code."_EmployeeLateOutReport.xlsx":"AttendanceA";

            App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
            $objPHPExcel = new PHPExcel();

            $objPHPExcel->getProperties()->setCreator("Administrator");
            $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
            $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
            $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
            $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

            $objPHPExcel->setActiveSheetIndex(0);

            $worksheet = $objPHPExcel->getActiveSheet();

            $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Late OUT Duration of " .$mname." "  .$year);
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
            $worksheet->mergeCells('A1:I1');
            $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            );
            for ($col = 'A'; $col !== 'Z'; $col++) {
                $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
            }
           $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:I2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

            if (count($arr_lateoutdata_for_template) == 0){
                 //print nodata
                 $worksheet->setCellValueByColumnAndRow(0, 3, "No data available ");
                 $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
              $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
              $worksheet->mergeCells('A3:I3');
              $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                      array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
              );

                }
                else{

                $columncount=0;
                $rowcount = 3;   
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Sl No. ');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Termination Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Late Out Duration');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $rowcount+=1;
                      $i = 1;  
                         
                  foreach($arr_lateoutdata_for_template as $value){
                    if(isset($value['summary']['0']['lateout']['EmpName']))
                
            {
                          
                     $arr_data  = $value['summary'];
//                        
                     if(count($arr_data)>=0){
                        
                        foreach($arr_data as $key=>$val){
                      
                        $columnindex = 0;
                        $empstatus = isset($val['ed']['emp_status']) && $val['ed']['emp_status']=="2" ? '(Resigned)':'' ;         
                        $name=$val['lateout']['EmpName'].$empstatus;
                        //$name=$name.$key;
                        $emp_id=$val['ed']['emp_id'];
                        $company_id=$val['ed']['employee_id'];
                        $branch=$val['br']['branch_name'];
                        $dep=$val['ed']['department'];
                        $designation=$val['ed']['designation'];
                        $date=$val['lateout']['LogDate'];
                        // $dateatt = date("d-m-Y", strtotime($date));
                        // $location = $val['0']['Location'];
                        $termin = $val['edd']['last_approved_working_date'];
                        // $sharpintime=$val['earlyout']['SharpInTime']; 
                        // $intime=$val['earlyout']['InTime'];
                        $lateouttime=$val['0']['lateoutsum'];


                        // $datetime = strtotime($earlyintime);
                        // $datetime = date(' H:i:s', 'earlyintime');
                       // $timestamp = $datetime->getTimestamp();

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$i );
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$emp_id);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$company_id);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$name);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$branch);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$dep);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).$rowcount,$designation);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$termin); 
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$lateouttime);
         
                        $columnindex=$columnindex+8;
                                        
                      $rowcount++;
                        $i = $i+1; 
                        $BStyle = array(
                         'borders' => array(
                         'allborders' => array(
                         'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                           )
                        );
             $row=$rowcount-1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:I'.$row)->applyFromArray($BStyle);
$objPHPExcel->getActiveSheet()
->getStyle('B3:B40000')
->getAlignment()
->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getActiveSheet()
->getStyle('C3:A40000')
->getAlignment()
->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                           
                    }
                }
                    $rowcount1=$rowcount+1;
                  }
            //     else {
            //     $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
            //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
            // }
        }
         
            $objPHPExcel->getActiveSheet()->setTitle('Employee Late OUT Duration');
/* header footer */
                            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
            $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                           /* header footer */
            
            /*print Set up*/
             $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
            /*print Set up*/
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save(dirname(__FILE__)."/".$file_name);

            // output headers so that the file is downloaded rather than displayed
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename='.$file_name);                        

            readfile(dirname(__FILE__)."/".$file_name);
            unlink(dirname(__FILE__)."/".$file_name);
            break;
        default : 
            $this->set('mode','');
            $this->render('lateoutduration');
            break;
    }  
}

 }
   private function generatelateindurationreport($mode){
        $arr_form_data = $_REQUEST;
      // debug($arr_form_data);

        $this->Latein->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $company_code = $this->Session->read('company_code'); //company_code
       
        $arr_lateindata = array();  
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
//           debug($str_criteria_item);
                $arr_lateindata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        
                if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
            echo "<h1>No Criteria Selected</h1>";
                die();
            }
    if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1'){
                // debug($arr_form_data);     
            $conditions = "ed.emp_status in('1','2')";  
            // debug($conditions);
         }
         else{
              $conditions = "ed.emp_status ='1'";  
         }

        
         // $conditions = 'earlyin.LogDate between "'.$from.'" and "'.$to.'"';
//        $conditions=array();
        $arr_lateindata_for_template = array();
        if(isset($arr_lateindata) && !empty($arr_lateindata)){
            
        foreach ($arr_lateindata as $lateindata) {
        if ($arr_form_data['select-criteria1'] == 'Units') {

//         debug($from);
$arr_lateindata= $this->EmployeeDetails->query("select latein.emp_pkey,latein.EmpName,latein.LogDate,COALESCE(NULLIF(latein.Location,''),br.branch_name) Location,
latein.InTime,SEC_TO_TIME( SUM(time_to_sec(`LateTime`)))
As lateinsum,br.branch_name,ed.emp_status ,uc.user_id,department,emp_id,employee_id,ed.designation,last_approved_working_date
from emp_late_in latein
    Left join employee_info as ed on (latein.emp_pkey = ed.emp_pkey)
     Left join termination as edd on (latein.emp_pkey = edd.emp_fkey)
    Left join branches as br on (ed.branch_code = br.branch_code)
    Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
    where $conditions  and latein.LateTime > 0 and DATE(latein.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and br.branch_code = '$lateindata' GROUP BY emp_id ");
   
   }



       else{
        $arr_lateindata= $this->EmployeeDetails->query("select latein.emp_pkey,latein.EmpName,latein.LogDate,COALESCE(NULLIF(latein.Location,''),br.branch_name) Location,
        latein.InTime,SEC_TO_TIME( SUM(time_to_sec(`LateTime`)))
        As lateinsum,br.branch_name,ed.emp_status ,uc.user_id,department,emp_id,employee_id,ed.designation,last_approved_working_date
        from emp_late_in latein
            Left join employee_info as ed on (latein.emp_pkey = ed.emp_pkey)
             Left join termination as edd on (latein.emp_pkey = edd.emp_fkey)
            Left join branches as br on (ed.branch_code = br.branch_code)
            Left join user_credentials as uc on (ed.emp_pkey = uc.emp_fkey)
            
                
               
                where $conditions  and latein.LateTime > 0 and DATE(latein.LogDate) between att_start_end_fn(concat('$from','-01'),1) and att_start_end_fn(concat('$from','-01'),2) and latein.emp_pkey = $lateindata GROUP BY emp_id ");
       }
   
       if(!empty($arr_lateindata)){
        $arr_lateindata_for_template[] = array(
                'summary'=>$arr_lateindata,
               // 'employees'=>$arr_leavepolicy_employees
            );
     
        }
        }
     
        $this->set('arr_lateindata_for_template', $arr_lateindata_for_template);
          $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        date_default_timezone_set('Asia/Kolkata');
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time=strtotime($f);
        $month=date("m",$time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month.'-01'; 
        $year=date("Y",$time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-Y H:i');
        //Set informations needed for report
    
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);  
           switch ($mode){
            case 'pdf' : 
                //echo "entered in";die();
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('lateinduration');
           //   debug($view_output);
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeLateIndurationReport.pdf', 'D');
               // $this->render('earlyinreport');                
                break;
            case 'excel' :
                
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_EmployeeLateInReport.xlsx":"AttendanceA";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Late IN Duration of " .$mname." "  .$year);
                   $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:I1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:I2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
//for printin no data
if (count($arr_lateindata_for_template) == 0){
    //  echo "<h3>No Data Available With The Selected Criteria</h3>";

     //print nodata
     $worksheet->setCellValueByColumnAndRow(0, 3, "No data available ");
     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
  $worksheet->mergeCells('A3:I3');
  $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
          array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
  );

    }
    else{

                
                    $columncount=0;
                    $rowcount = 3;  
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Sl No. ');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Termination Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Late In Duration');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $rowcount+=1;
                          $i = 1;  
                          if (count($arr_lateindata_for_template) == 0){
                     echo "<h3>No Data Available With The Selected Criteria</h3>";
                    }
                    // else{
                      foreach($arr_lateindata_for_template as $value){
                        if(isset($value['summary']['0']['latein']['EmpName']))
                    
                {
                              
                         $arr_data  = $value['summary'];
//                          debug($arr_data);exit();
// //                        
                         if(count($arr_data)>=0){
                            
                            foreach($arr_data as $key=>$val){
                          
                            $columnindex = 0;
                            $empstatus = isset($val['ed']['emp_status']) && $val['ed']['emp_status']=="2" ? '(Resigned)':'' ;         
                            $name=$val['latein']['EmpName'].$empstatus;
                            //$name=$name.$key;
                            $emp_id=$val['ed']['emp_id'];
                            $company_id=$val['ed']['employee_id'];
                            $branch=$val['br']['branch_name'];
                            $dep=$val['ed']['department'];
                            $designation=$val['ed']['designation'];
                            $date=$val['latein']['LogDate'];
                            // $dateatt = date("d-m-Y", strtotime($date));
                            // $location = $val['0']['Location'];
                            $termin = $val['edd']['last_approved_working_date'];
                            // $sharpintime=$val['earlyout']['SharpInTime']; 
                            // $intime=$val['earlyout']['InTime'];
                            $lateintime=$val['0']['lateinsum'];


                            // $datetime = strtotime($earlyintime);
                            // $datetime = date(' H:i:s', 'earlyintime');
                           // $timestamp = $datetime->getTimestamp();
   
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$i );
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$emp_id);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$company_id);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$name);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$branch);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$dep);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).$rowcount,$designation);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$termin); 
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$lateintime);
             
                            $columnindex=$columnindex+8;
                                            
                          $rowcount++;
                            $i = $i+1; 
                            $BStyle = array(
                             'borders' => array(
                             'allborders' => array(
                             'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                               )
                            );
                 $row=$rowcount-1;
                            $objPHPExcel->getActiveSheet()->getStyle('A1:I'.$row)->applyFromArray($BStyle);
    $objPHPExcel->getActiveSheet()
    ->getStyle('B3:B40000')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $objPHPExcel->getActiveSheet()
    ->getStyle('C3:A40000')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                            }
                               
                        }
                    }
                        $rowcount1=$rowcount+1;
                      }
                //     else {
                //     $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                // }
            }
             
                $objPHPExcel->getActiveSheet()->setTitle('Employee Late IN Duration');
    /* header footer */
                                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                               /* header footer */
                
                /*print Set up*/
                 $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /*print Set up*/
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__)."/".$file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename='.$file_name);                        

                readfile(dirname(__FILE__)."/".$file_name);
                unlink(dirname(__FILE__)."/".$file_name);
                break;
            default : 
                $this->set('mode','');
                $this->render('lateinduration');
                // break;
        }  
   }
   
     }
      private function generatDailyAttendanceReport($type,$mode)
    {
        $arr_form_data = $_REQUEST;
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
       // $to = date("Y-m-d", strtotime($arr_form_data['reportto'] . ' +1 day'));
        $to = date("Y-m-d", strtotime($arr_form_data['reportto']));
        $f = date('d/m/Y', strtotime($arr_form_data['reportfrom']));
        $t = date("d/m/Y", strtotime($arr_form_data['reportto']));
        $this->set('from', $from);
        $this->set('to', $to);
        $this->set('f', $f);
        $this->set('t', $t);


        //Dates array to store the selected dates.
        $Dates = array();
        $fromDate = new DateTime($from);
        $toDate = new DateTime($to);

        // Add each date to the array
        while ($fromDate <= $toDate) {
            $Dates[] = $fromDate->format('Y-m-d');
            $fromDate->modify('+1 day');
        }

        $arr_breakdata = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_breakdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        if ($str_criteria_item == '') {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $conditions = "ei.emp_status in('1','2')";
        } else {
            $conditions = "ei.emp_status ='1'";
        }

        $arr_breakdata_for_template = array();
        if (isset($arr_breakdata) && !empty($arr_breakdata)) {
             
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

           foreach ($Dates as $date) {
                foreach ($arr_breakdata as $breakdata) {
                  $arr_deviceatt = $this->EmployeeDetails->query("
        SELECT ei.EmpName, da.att_date,da.att_in_time,da.att_out_time,da.duration,working_day_time_procedures.day_time_desc,on_dutty1,off_dutty1,
        (SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND DATE(d.LOGDATE) = da.att_date AND (d.c1= 'IN' OR d.DIRECTION = 'IN')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_in_time)) ASC LIMIT 1 ) AS in_location,
        ( SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND DATE(d.LOGDATE) = da.att_date AND (d.c1= 'OUT' OR d.DIRECTION = 'OUT')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_out_time)) ASC LIMIT 1) AS out_location
        FROM employee_info AS ei
        LEFT JOIN emp_detail_timeattandance AS da ON (ei.emp_pkey = da.emp_pkey) AND da.att_date = '$date'
        LEFT JOIN emp_proff AS emp_proff ON (ei.emp_pkey = emp_proff.emp_fkey)
        LEFT JOIN working_day_time_procedures  AS working_day_time_procedures ON (working_day_time_procedures.day_time_seq = emp_proff.day_time_seq)
        WHERE $conditions AND ei.emp_pkey = '$breakdata' ");
      if(!empty($arr_deviceatt)){
         $shiftStart = $arr_deviceatt['0']['working_day_time_procedures']['on_dutty1'] ?$arr_deviceatt['0']['working_day_time_procedures']['on_dutty1']:'';
        $shiftEnd   = $arr_deviceatt['0']['working_day_time_procedures']['off_dutty1'] ?$arr_deviceatt['0']['working_day_time_procedures']['off_dutty1']:'';
        $punchIn    = $arr_deviceatt['0']['da']['att_in_time'] ?$arr_deviceatt['0']['da']['att_in_time']:'';
        $punchOut   = $arr_deviceatt['0']['da']['att_out_time'] ?$arr_deviceatt['0']['da']['att_out_time']:'';

        // Calculate late punch-in (in minutes)
        $lateInMinutes = '';
        if (!empty($shiftStart) && !empty($punchIn)) {
            $shiftStartTime = strtotime($date . ' ' . $shiftStart);
            $punchInTime    = strtotime($punchIn);
            if ($punchInTime > $shiftStartTime) {
                $lateInMinutes = round(($punchInTime - $shiftStartTime) / 60);
            } else {
                $lateInMinutes = 0;
            }
        }

        // (Optional) Calculate early/late punch-out similarly
        $lateOutMinutes = '';
        if (!empty($shiftEnd) && !empty($punchOut)) {
            $shiftEndTime  = strtotime($date . ' ' . $shiftEnd);
            $punchOutTime  = strtotime($punchOut);
            if ($punchOutTime < $shiftEndTime) {
                // Early leave
                $lateOutMinutes = "-" . round(($shiftEndTime - $punchOutTime) / 60);
            } else {
                // Late overtime
                $lateOutMinutes = round(($punchOutTime - $shiftEndTime) / 60);
            }
        }

        $durationMinutes = $arr_deviceatt['0']['da']['duration']?$arr_deviceatt['0']['da']['duration']: 0; // total minutes worked
        $durationHHMMSS = '';

    if (!empty($durationMinutes) && is_numeric($durationMinutes)) {
    // Convert minutes to seconds first, then format
    $durationSeconds = $durationMinutes * 60;
    $hours = floor($durationSeconds / 3600);
    $minutes = floor(($durationSeconds % 3600) / 60);
    $seconds = $durationSeconds % 60;
    $durationHHMMSS = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    } else {
    $durationHHMMSS = '00:00:00';
    }
        // Prepare an associative array of needed fields
        $empData = [
        'EmpName' => isset($arr_deviceatt['0']['ei']['EmpName'])?$arr_deviceatt['0']['ei']['EmpName']:'',
        'att_date' => isset($arr_deviceatt['0']['da']['att_date'])?$arr_deviceatt['0']['da']['att_date']:'',
        'att_in_time' => isset($arr_deviceatt['0']['da']['att_in_time'])?$arr_deviceatt['0']['da']['att_in_time']:'',
        'att_out_time' => isset($arr_deviceatt['0']['da']['att_out_time'])?$arr_deviceatt['0']['da']['att_out_time']:'',
        'in_location' => isset($arr_deviceatt['0']['0']['in_location'])?$arr_deviceatt['0']['0']['in_location']:'',
        'out_location' => isset($arr_deviceatt['0']['0']['out_location'])?$arr_deviceatt['0']['0']['out_location']:'',
        'duration' => $durationHHMMSS,
        'day_time_desc' => isset($arr_deviceatt['0']['working_day_time_procedures']['day_time_desc'])?$arr_deviceatt['0']['working_day_time_procedures']['day_time_desc']:'',
        'late_in_minutes'=> $lateInMinutes,
        'late_out_minutes'=> $lateOutMinutes
         ];
                $groupedData[$date][] = $empData;
        }
                 }
                }
            } else{
 foreach ($Dates as $date) {
                foreach ($arr_breakdata as $breakdata) {
                         $arr_deviceatt = $this->EmployeeDetails->query("
        SELECT ei.EmpName, da.att_date,da.att_in_time,da.att_out_time,da.duration,working_day_time_procedures.day_time_desc,on_dutty1,off_dutty1 ,
        (SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND DATE(d.LOGDATE) = da.att_date AND (d.c1= 'IN' OR d.DIRECTION = 'IN')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_in_time)) ASC LIMIT 1 ) AS in_location,
        ( SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND DATE(d.LOGDATE) = da.att_date AND (d.c1= 'OUT' OR d.DIRECTION = 'OUT')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_out_time)) ASC LIMIT 1) AS out_location
        FROM employee_info AS ei
        LEFT JOIN emp_detail_timeattandance AS da ON (ei.emp_pkey = da.emp_pkey) AND da.att_date = '$date'
        LEFT JOIN emp_proff AS emp_proff ON (ei.emp_pkey = emp_proff.emp_fkey)
        LEFT JOIN working_day_time_procedures  AS working_day_time_procedures ON (working_day_time_procedures.day_time_seq = emp_proff.day_time_seq)
        WHERE $conditions AND ei.branch_code  = '$breakdata' order by ei.EmpName");
       // debug($arr_deviceatt);
                // Initialize an array for the day's data
$dayEmployees = []; 

if (!empty($arr_deviceatt)) {
    foreach ($arr_deviceatt as $record) {
        $shiftStart = $record['working_day_time_procedures']['on_dutty1'] ?$record['working_day_time_procedures']['on_dutty1']:'';
        $shiftEnd   = $record['working_day_time_procedures']['off_dutty1'] ?$record['working_day_time_procedures']['off_dutty1']:'';
        $punchIn    = $record['da']['att_in_time'] ?$record['da']['att_in_time']:'';
        $punchOut   = $record['da']['att_out_time'] ?$record['da']['att_out_time']:'';

        // Calculate late punch-in (in minutes)
        $lateInMinutes = '';
        if (!empty($shiftStart) && !empty($punchIn)) {
            $shiftStartTime = strtotime($date . ' ' . $shiftStart);
            $punchInTime    = strtotime($punchIn);
            if ($punchInTime > $shiftStartTime) {
                $lateInMinutes = round(($punchInTime - $shiftStartTime) / 60);
            } else {
                $lateInMinutes = 0;
            }
        }

        // (Optional) Calculate early/late punch-out similarly
        $lateOutMinutes = '';
        if (!empty($shiftEnd) && !empty($punchOut)) {
            $shiftEndTime  = strtotime($date . ' ' . $shiftEnd);
            $punchOutTime  = strtotime($punchOut);
            if ($punchOutTime < $shiftEndTime) {
                // Early leave
                $lateOutMinutes = "-" . round(($shiftEndTime - $punchOutTime) / 60);
            } else {
                // Late overtime
                $lateOutMinutes = round(($punchOutTime - $shiftEndTime) / 60);
            }
        }

        $durationMinutes = $record['da']['duration'] ?$record['da']['duration']: 0; // total minutes worked
        $durationHHMMSS = '';

    if (!empty($durationMinutes) && is_numeric($durationMinutes)) {
    // Convert minutes to seconds first, then format
    $durationSeconds = $durationMinutes * 60;
    $hours = floor($durationSeconds / 3600);
    $minutes = floor(($durationSeconds % 3600) / 60);
    $seconds = $durationSeconds % 60;
    $durationHHMMSS = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    } else {
    $durationHHMMSS = '00:00:00';
    }
        $empData = [
            'EmpName'        => isset($record['ei']['EmpName']) ? $record['ei']['EmpName'] : '',
            'att_date'       => isset($record['da']['att_date']) ? $record['da']['att_date'] : '',
            'att_in_time'    => isset($record['da']['att_in_time']) ? $record['da']['att_in_time'] : '',
            'att_out_time'   => isset($record['da']['att_out_time']) ? $record['da']['att_out_time'] : '',
            'in_location' => isset($record['0']['in_location'])?$record['0']['in_location']:'',
            'out_location' => isset($record['0']['out_location'])?$record['0']['out_location']:'',
            'duration'       => $durationHHMMSS,
            'day_time_desc'  => isset($record['working_day_time_procedures']['day_time_desc']) ? $record['working_day_time_procedures']['day_time_desc'] : '',
            'late_in_minutes'=> $lateInMinutes,
            'late_out_minutes'=> $lateOutMinutes
        ];

        // Push each employee's data to the day's array
        $groupedData[$date][] = $empData;
    }
}
               // $groupedData[$date][] = $dayEmployees;
                 }
                }
        }
    $arr_breakdata_for_template = $groupedData;
      

            $this->set('arr_breakdata_for_template', $arr_breakdata_for_template);

            //Set informations needed for report

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);

            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
              
            switch ($mode) {
                
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('breakreport');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeEarlyInReport.pdf', 'D');
                    // $this->render('earlyinreport');                
                    break;
                case 'excel':
                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code ."Attendance.xlsx" : "Attendance.xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, " Attendance Report for - " . $f . ' - ' . $t);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(13);
                    $worksheet->mergeCells('A1:J1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(11);
                    $worksheet->mergeCells('A2:J2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }


                    if (empty($arr_breakdata_for_template)) {
                        //print nodata
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(11);
                        $worksheet->mergeCells('A3:J3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        for ($col = 'A'; $col !== 'Z'; $col++) {
                            $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(true);
                        }
                    } else {

                             
$rowcount = 3;
$columncount = 0;

// Set headers
$headers = [
    'Sl. No',
    'Employee Name',
    'Shift Time',
    'Punch In Time',
    'In Location',
    'Punch Out Time',
    'Out Location',
    'Late Punch-in (minutes)',
    'Late Punch-out (minutes)',
    'Hours Worked (HH:MM:SS)'
];





// Loop through your grouped attendance data
if (!empty($arr_breakdata_for_template)) {
    foreach ($arr_breakdata_for_template as $date => $employees) {

        // Add Date Header Row
        $objPHPExcel->getActiveSheet()->mergeCells("A" . $rowcount . ":J" . $rowcount);
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $rowcount, "Date: " . $date);
        $objPHPExcel->getActiveSheet()->getStyle("A" . $rowcount)->getFont()->setBold(true);
        $worksheet->getStyle("A" . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
        $rowcount++;
// Write table headers
foreach ($headers as $col => $header) {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + $col), $rowcount, $header);
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + $col), $rowcount)->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setAutoSize(true);
   
}
// Move to next row for data
$rowcount++;
$slno = 1;
        if (!empty($employees)) {
            foreach ($employees as $emp) {
                $punchIn  = !empty($emp['att_in_time']) ? date("H:i:s", strtotime($emp['att_in_time'])) : 'No Punch In';
                $punchOut = !empty($emp['att_out_time']) ? date("H:i:s", strtotime($emp['att_out_time'])) : 'No Punch Out';
                $shiftTime = !empty($emp['day_time_desc']) ? $emp['day_time_desc'] : '-';
                $in_location = !empty($emp['in_location']) ? $emp['in_location'] : '-';
                $out_location = !empty($emp['out_location']) ? $emp['out_location'] : '-';
                $workedHours = !empty($emp['duration']) ? $emp['duration'] : '-';
                $lateIn  = !empty($emp['late_in_minutes']) ? $emp['late_in_minutes'] : '0';
                $lateOut = !empty($emp['late_out_minutes']) ? $emp['late_out_minutes'] : '0';

                // Write data row
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowcount, $slno++);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowcount, $emp['EmpName']);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowcount, $shiftTime);
               // indices
$colPunchIn  = 3;
$colInLoc    = 4;
$colPunchOut = 5;

// set punch in
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colPunchIn, $rowcount, $punchIn);

// Option 1: using column+row
if (trim((string)$punchIn) === 'No Punch In') {
    $objPHPExcel->getActiveSheet()
        ->getStyleByColumnAndRow($colPunchIn, $rowcount)
        ->getFill()
        ->applyFromArray([
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => ['rgb' => 'FFC7CE'],
        ]);
}
               // in_location and punch out
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colInLoc, $rowcount, $in_location);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colPunchOut, $rowcount, $punchOut);

// Option 2: using string coordinate (alternative)
$cellPunchOut = PHPExcel_Cell::stringFromColumnIndex($colPunchOut) . $rowcount;
if (trim((string)$punchOut) === 'No Punch Out') {
    $objPHPExcel->getActiveSheet()->getStyle($cellPunchOut)->getFill()->applyFromArray([
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => ['rgb' => 'FFC7CE'],
    ]);
}
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $rowcount, $out_location);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $rowcount, $lateIn);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $rowcount, $lateOut);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $rowcount, $workedHours);

                // Optional: Align all text centrally
                for ($c = 0; $c <= 9; $c++) {
                    $objPHPExcel->getActiveSheet()
                        ->getStyleByColumnAndRow($c, $rowcount)
                        ->getAlignment()
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }

                $rowcount++;
            }
        } else {
            // No employee data for this date
            $objPHPExcel->getActiveSheet()->mergeCells("A" . $rowcount . ":J" . $rowcount);
            $objPHPExcel->getActiveSheet()->setCellValue("A" . $rowcount, "No employee data found for this date.");
            $objPHPExcel->getActiveSheet()->getStyle("A" . $rowcount)
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $rowcount++;
        }

        // Add one blank row between date sections
        $rowcount++;
    }
} 


                                    $BStyle = array(

                                        'borders' => array(

                                            'allborders' => array(

                                                'style' => PHPExcel_Style_Border::BORDER_THIN

                                            )

                                        )

                                    );

                                    $row = $rowcount - 1;

                                    $objPHPExcel->getActiveSheet()->getStyle('A1:J' . $row)->applyFromArray($BStyle);
                                }
                        
                       

                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    $objPHPExcel->getActiveSheet()->setTitle('Attendance');
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
                    $this->render('dailyattendance');
                    break;
            }
        }
    }

     private function generatDailyAttendanceReportEXTR($type,$mode)
    {
        $arr_form_data = $_REQUEST;
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code');
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
       // $to = date("Y-m-d", strtotime($arr_form_data['reportto'] . ' +1 day'));
        $to = date("Y-m-d", strtotime($arr_form_data['reportto']));
        $f = date('d/m/Y', strtotime($arr_form_data['reportfrom']));
        $t = date("d/m/Y", strtotime($arr_form_data['reportto']));
        $this->set('from', $from);
        $this->set('to', $to);
        $this->set('f', $f);
        $this->set('t', $t);


        //Dates array to store the selected dates.
        $Dates = array();
        $fromDate = new DateTime($from);
        $toDate = new DateTime($to);

        // Add each date to the array
        while ($fromDate <= $toDate) {
            $Dates[] = $fromDate->format('Y-m-d');
            $fromDate->modify('+1 day');
        }

        $groupedData = array();
        $arr_breakdata = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_breakdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        if ($str_criteria_item == '') {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>No Criteria Selected</h1>";
            die();
        }

        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $conditions = "ei.emp_status in('1','2')";
        } else {
            $conditions = "ei.emp_status ='1'";
        }

        $arr_breakdata_for_template = array();
        if (isset($arr_breakdata) && !empty($arr_breakdata)) {
             
            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

           foreach ($Dates as $date) {
                foreach ($arr_breakdata as $breakdata) {
                   $arr_deviceatt = $this->EmployeeDetails->query("
        SELECT ei.emp_pkey,ei.emp_id,ei.EmpName, da.att_date,da.att_in_time,da.att_out_time,da.duration,da.present,
        COALESCE(punch_shift.day_time_desc, working_day_time_procedures.day_time_desc) AS day_time_desc,
        COALESCE(punch_shift.on_dutty1, working_day_time_procedures.on_dutty1) AS on_dutty1,
        COALESCE(punch_shift.off_dutty1, working_day_time_procedures.off_dutty1) AS off_dutty1,
        emp_proff.HOLIDAY_GROUP_ID, working_day_time_procedures.day_time_seq,
        working_day_time_procedures.Sunday, working_day_time_procedures.Monday, working_day_time_procedures.Tuesday, working_day_time_procedures.Wednesday, working_day_time_procedures.Thursday, working_day_time_procedures.Friday, working_day_time_procedures.Saturday,
        working_day_time_procedures.Sunday_F, working_day_time_procedures.Monday_F, working_day_time_procedures.Tuesday_F, working_day_time_procedures.Wednesday_F, working_day_time_procedures.Thursday_F, working_day_time_procedures.Friday_F, working_day_time_procedures.Saturday_F,
        (SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND (d.c1= 'IN' OR d.DIRECTION = 'IN')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_in_time)) ASC LIMIT 1 ) AS in_location,
        ( SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND (d.c1= 'OUT' OR d.DIRECTION = 'OUT')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_out_time)) ASC LIMIT 1) AS out_location
        FROM employee_info AS ei
        LEFT JOIN emp_detail_timeattandance AS da ON (ei.emp_pkey = da.emp_pkey) AND da.att_date = '$date'
        LEFT JOIN emp_proff AS emp_proff ON (ei.emp_pkey = emp_proff.emp_fkey)
        LEFT JOIN working_day_time_procedures  AS working_day_time_procedures ON (working_day_time_procedures.day_time_seq = emp_proff.day_time_seq)
        LEFT JOIN working_day_time_procedures AS punch_shift ON (
            punch_shift.day_time_seq = (
                SELECT d.SHIFT FROM device_attandance AS d 
                WHERE d.emp_id = ei.emp_id 
                AND (d.c1 = 'IN' OR d.DIRECTION = 'IN')
                AND DATE(d.LOGDATE) = '$date'
                ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_in_time)) ASC 
                LIMIT 1
            )
        )
        WHERE $conditions AND ei.emp_pkey = '$breakdata' ");

      
      if(!empty($arr_deviceatt)){

      $empId = $arr_deviceatt[0]['ei']['emp_id'];
      $emp_pkey = $arr_deviceatt[0]['ei']['emp_pkey'];

      $leave_txn = $this->EmployeeDetails->query("SELECT le.ISAutherized, le.ISAPPROVED, le.LEAVESTATUS, tr.leave_session FROM emp_leave_transactions tr JOIN leaveentries le ON tr.LEAVEENTRYID = le.LEAVEENTRYID WHERE tr.leave_date = '$date' AND le.EMP_fkey = '$emp_pkey' LIMIT 1");
        $detailed_leave_status = '';
        if(!empty($leave_txn)){
            if($leave_txn[0]['le']['ISAPPROVED'] == 1) $detailed_leave_status = 'Leave Approved';
            elseif($leave_txn[0]['le']['ISAutherized'] == 1) $detailed_leave_status = 'Leave Authorized';
            else $detailed_leave_status = 'Leave ' . ($leave_txn[0]['le']['LEAVESTATUS'] ?: 'Applied');
        }

         $shiftStart = isset($arr_deviceatt['0']['0']['on_dutty1']) ? $arr_deviceatt['0']['0']['on_dutty1'] : (isset($arr_deviceatt['0']['working_day_time_procedures']['on_dutty1']) ? $arr_deviceatt['0']['working_day_time_procedures']['on_dutty1'] : '');
        $shiftEnd   = isset($arr_deviceatt['0']['0']['off_dutty1']) ? $arr_deviceatt['0']['0']['off_dutty1'] : (isset($arr_deviceatt['0']['working_day_time_procedures']['off_dutty1']) ? $arr_deviceatt['0']['working_day_time_procedures']['off_dutty1'] : '');
        $punchIn    = $arr_deviceatt['0']['da']['att_in_time'] ?$arr_deviceatt['0']['da']['att_in_time']:'';
        $punchOut   = $arr_deviceatt['0']['da']['att_out_time'] ?$arr_deviceatt['0']['da']['att_out_time']:'';


        

        if (!empty($punchIn) && !empty($punchOut)) {
            // $punches = $this->EmployeeDetails->query("
            //     SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
            //     FROM device_attandance
            //     WHERE emp_id = '{$empId}'
            //       AND LOGDATE >= '{$punchIn}'
            //       AND LOGDATE <= '{$punchOut}' AND  status='Y'
            //     ORDER BY LOGDATE
            // ");
            $punches = $this->EmployeeDetails->query("
            SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
            FROM device_attandance
            WHERE emp_id = '{$empId}'
            AND (
                    (LOGDATE >= '{$punchIn}' AND LOGDATE <= '{$punchOut}')
                    OR DATE(LOGDATE) = '{$date}'
                )
            AND status='Y'
            ORDER BY LOGDATE
        ");
        } else {
            $punches = $this->EmployeeDetails->query("
                SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
                FROM device_attandance
                WHERE emp_id = '{$empId}'
                  AND DATE(LOGDATE) = '{$date}' AND status='Y'
                ORDER BY LOGDATE
            ");
        }

        // Calculate late punch-in (in minutes)
        $lateInMinutes = '';
        if (!empty($shiftStart) && !empty($punchIn)) {
            $shiftStartTime = strtotime($date . ' ' . $shiftStart);
            $punchInTime    = strtotime($punchIn);
            if ($punchInTime > $shiftStartTime) {
                $lateInMinutes = round(($punchInTime - $shiftStartTime) / 60);
            } else {
                $lateInMinutes = 0;
            }
        }

        // (Optional) Calculate early/late punch-out similarly
        $lateOutMinutes = '';
        if (!empty($shiftEnd) && !empty($punchOut)) {
            $shiftEndTime  = strtotime($date . ' ' . $shiftEnd);
            $punchOutTime  = strtotime($punchOut);
            if ($punchOutTime < $shiftEndTime) {
                // Early leave
                $lateOutMinutes = "-" . round(($shiftEndTime - $punchOutTime) / 60);
            } else {
                // Late overtime
                $lateOutMinutes = round(($punchOutTime - $shiftEndTime) / 60);
            }
        }

        $durationMinutes = $arr_deviceatt['0']['da']['duration']?$arr_deviceatt['0']['da']['duration']: 0; // total minutes worked
        $durationHHMMSS = '';

    if (!empty($durationMinutes) && is_numeric($durationMinutes)) {
    // Convert minutes to seconds first, then format
    $durationSeconds = $durationMinutes * 60;
    $hours = floor($durationSeconds / 3600);
    $minutes = floor(($durationSeconds % 3600) / 60);
    $seconds = $durationSeconds % 60;
    $durationHHMMSS = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    } else {
    $durationHHMMSS = '00:00:00';
    }

        // --- Day Type Logic ---
        $day_type = '-';
        $holidayId = isset($arr_deviceatt[0]['emp_proff']['HOLIDAY_GROUP_ID']) ? $arr_deviceatt[0]['emp_proff']['HOLIDAY_GROUP_ID'] : '';
        $dayTimeSeq = isset($arr_deviceatt[0]['working_day_time_procedures']['day_time_seq']) ? $arr_deviceatt[0]['working_day_time_procedures']['day_time_seq'] : '';

        $dayName = date('l', strtotime($date));
        $dayOfMonth = date('j', strtotime($date));
        $weekNum = floor(($dayOfMonth - 1) / 7) + 1;

        $isHoliday = false;
        if (!empty($holidayId)) {
            $hCount = $this->EmployeeDetails->query("SELECT count(*) as count FROM holidays WHERE HOLIDAY_GROUP_ID = '{$holidayId}' AND status = 1 AND HOLIDAYDATE = '{$date}'");
            if (!empty($hCount) && $hCount[0][0]['count'] > 0) {
                $day_type = 'Holiday';
                $isHoliday = true;
            }
        }

        if (!$isHoliday && !empty($dayTimeSeq)) {
            $weekdayFlag = isset($arr_deviceatt[0]['working_day_time_procedures'][$dayName]) ? $arr_deviceatt[0]['working_day_time_procedures'][$dayName] : 'Y';
            $halfDayFlag = isset($arr_deviceatt[0]['working_day_time_procedures'][$dayName . '_F']) ? $arr_deviceatt[0]['working_day_time_procedures'][$dayName . '_F'] : 'N';

            $exCount = $this->EmployeeDetails->query("SELECT count(*) as count FROM shift_exceptions WHERE shift_id = '{$dayTimeSeq}' AND week_off = 'Y' AND status = 1 AND lcase(ex_week_day) = '" . strtolower($dayName) . "' AND cast(ex_week as unsigned) = {$weekNum}");

            if ($weekdayFlag === 'N' || (!empty($exCount) && $exCount[0][0]['count'] > 0)) {
                $day_type = 'WO-' . $dayName . ' (Full Day)';
            } elseif ($halfDayFlag === 'Y') {
                $day_type = 'WO-' . $dayName . ' (Half Day)';
            }
        }

         // Fetch leave details separately
        $leaves_query = $this->EmployeeDetails->query("
            SELECT 
                elt.leave_date, 
                elt.leave_session, 
                elt.Leavestatus,
                shi.occurance
            FROM emp_leave_transactions AS elt
            JOIN leaveentries AS leaves ON leaves.LEAVEENTRYID = elt.LEAVEENTRYID
            JOIN salary_head_items AS shi ON shi.salary_head_item_pkey = leaves.salary_head_item_fkey
            WHERE leaves.EMP_fkey = '$emp_pkey' AND elt.Leavestatus IN ('Applied','Approved','Authorized')
            AND elt.leave_date ='$date'
           
        ");

        $attendanceStatus = isset($arr_deviceatt[0]['da']['present']) ? $arr_deviceatt[0]['da']['present'] : '';

        $finalStatus = $attendanceStatus;

        // Split attendance halves
        $attFH = '';
        $attSH = '';

        if(!empty($attendanceStatus) && strpos($attendanceStatus,'/') !== false){
            list($attFH,$attSH) = explode('/',$attendanceStatus);
        }else{
            $attFH = $attendanceStatus;
            $attSH = $attendanceStatus;
        }

        if(!empty($leaves_query)){

            $leaveSession = $leaves_query[0]['elt']['leave_session'];
            $occurance = $leaves_query[0]['shi']['occurance'];

            if($leaveSession == 1){
                // First half leave
                $attFH = $occurance;
            }
            elseif($leaveSession == 2){
                // Second half leave
                $attSH = $occurance;
            }
            elseif($leaveSession == 3){
                // Full day leave
                $attFH = $occurance;
                $attSH = $occurance;
            }

            $finalStatus = $attFH.'/'.$attSH;
        }
       

        // Prepare an associative array of needed fields
        $empData = [
        'EmpName' => isset($arr_deviceatt['0']['ei']['EmpName'])?$arr_deviceatt['0']['ei']['EmpName']:'',
        'att_date' => isset($arr_deviceatt['0']['da']['att_date'])?$arr_deviceatt['0']['da']['att_date']:'',
        'att_in_time' => isset($arr_deviceatt['0']['da']['att_in_time'])?$arr_deviceatt['0']['da']['att_in_time']:'',
        'att_out_time' => isset($arr_deviceatt['0']['da']['att_out_time'])?$arr_deviceatt['0']['da']['att_out_time']:'',
        'in_location' => !empty($arr_deviceatt['0']['da']['att_in_time']) && isset($arr_deviceatt['0']['0']['in_location'])?$arr_deviceatt['0']['0']['in_location']:'',
        'out_location' => !empty($arr_deviceatt['0']['da']['att_out_time']) && isset($arr_deviceatt['0']['0']['out_location'])?$arr_deviceatt['0']['0']['out_location']:'',
        'duration' => $durationHHMMSS,
        'day_time_desc' => isset($arr_deviceatt['0']['0']['day_time_desc']) ? $arr_deviceatt['0']['0']['day_time_desc'] : (isset($arr_deviceatt['0']['working_day_time_procedures']['day_time_desc']) ? $arr_deviceatt['0']['working_day_time_procedures']['day_time_desc'] : ''),
        'late_in_minutes'=> $lateInMinutes,
        'late_out_minutes'=> $lateOutMinutes,
        'detailed_leave_status' => $detailed_leave_status,
        'punches'=>$punches,
        'day_type' => $day_type,
        // 'status' => isset($arr_deviceatt['0']['da']['present']) ? $arr_deviceatt['0']['da']['present'] : ''
        'status' => $finalStatus
         ];
                $groupedData[$date][] = $empData;
        }
                 }
                if (isset($groupedData[$date]) && !empty($groupedData[$date])) {
                    usort($groupedData[$date], function($a, $b) {
                        return strcmp($a['EmpName'], $b['EmpName']);
                    });
                }
                }
            }
             else{
 foreach ($Dates as $date) {
                foreach ($arr_breakdata as $breakdata) {
                    
                         $arr_deviceatt = $this->EmployeeDetails->query("
        SELECT ei.emp_pkey,ei.emp_id,ei.EmpName, da.att_date,da.att_in_time,da.att_out_time,da.duration,da.present,
        COALESCE(punch_shift.day_time_desc, working_day_time_procedures.day_time_desc) AS day_time_desc,
        COALESCE(punch_shift.on_dutty1, working_day_time_procedures.on_dutty1) AS on_dutty1,
        COALESCE(punch_shift.off_dutty1, working_day_time_procedures.off_dutty1) AS off_dutty1,
        emp_proff.HOLIDAY_GROUP_ID, working_day_time_procedures.day_time_seq,
        working_day_time_procedures.Sunday, working_day_time_procedures.Monday, working_day_time_procedures.Tuesday, working_day_time_procedures.Wednesday, working_day_time_procedures.Thursday, working_day_time_procedures.Friday, working_day_time_procedures.Saturday,
        working_day_time_procedures.Sunday_F, working_day_time_procedures.Monday_F, working_day_time_procedures.Tuesday_F, working_day_time_procedures.Wednesday_F, working_day_time_procedures.Thursday_F, working_day_time_procedures.Friday_F, working_day_time_procedures.Saturday_F,
        (SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND (d.c1= 'IN' OR d.DIRECTION = 'IN')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_in_time)) ASC LIMIT 1 ) AS in_location,
        ( SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND (d.c1= 'OUT' OR d.DIRECTION = 'OUT')
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_out_time)) ASC LIMIT 1) AS out_location
        FROM employee_info AS ei
        LEFT JOIN emp_detail_timeattandance AS da ON (ei.emp_pkey = da.emp_pkey) AND da.att_date = '$date'
        LEFT JOIN emp_proff AS emp_proff ON (ei.emp_pkey = emp_proff.emp_fkey)
        LEFT JOIN working_day_time_procedures  AS working_day_time_procedures ON (working_day_time_procedures.day_time_seq = emp_proff.day_time_seq)
        LEFT JOIN working_day_time_procedures AS punch_shift ON (
            punch_shift.day_time_seq = (
                SELECT d.SHIFT FROM device_attandance AS d 
                WHERE d.emp_id = ei.emp_id 
                AND (d.c1 = 'IN' OR d.DIRECTION = 'IN')
                AND DATE(d.LOGDATE) = '$date'
                ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_in_time)) ASC 
                LIMIT 1
            )
        )
        LEFT JOIN leaveentries le
    ON le.EMP_fkey = ei.emp_pkey

    LEFT JOIN emp_leave_transactions elt
        ON elt.LEAVEENTRYID = le.LEAVEENTRYID
        AND elt.leave_date = '$date'
        WHERE $conditions AND ei.branch_code  = '$breakdata' order by ei.EmpName");

      
        

       
                // Initialize an array for the day's data
$dayEmployees = []; 


// Added by Antigravity to prevent duplicates
$processed_employees = [];

if (!empty($arr_deviceatt)) {

    foreach ($arr_deviceatt as $record) {

    $empId = $record['ei']['emp_id'];
    $emp_pkey = $record['ei']['emp_pkey'];

    if (in_array($empId, $processed_employees)) {
        continue;
    }
    $processed_employees[] = $empId;

    $leave_txn = $this->EmployeeDetails->query("SELECT le.ISAutherized, le.ISAPPROVED, le.LEAVESTATUS, tr.leave_session FROM emp_leave_transactions tr JOIN leaveentries le ON tr.LEAVEENTRYID = le.LEAVEENTRYID WHERE tr.leave_date = '$date' AND le.EMP_fkey = '$emp_pkey' LIMIT 1");
    $detailed_leave_status = '';
    if(!empty($leave_txn)){
        if($leave_txn[0]['le']['ISAPPROVED'] == 1) $detailed_leave_status = 'Leave Approved';
        elseif($leave_txn[0]['le']['ISAutherized'] == 1) $detailed_leave_status = 'Leave Authorized';
        else $detailed_leave_status = 'Leave ' . ($leave_txn[0]['le']['LEAVESTATUS'] ?: 'Applied');
    }

        $shiftStart = isset($record['0']['on_dutty1']) ? $record['0']['on_dutty1'] : (isset($record['working_day_time_procedures']['on_dutty1']) ? $record['working_day_time_procedures']['on_dutty1'] : '');
        $shiftEnd   = isset($record['0']['off_dutty1']) ? $record['0']['off_dutty1'] : (isset($record['working_day_time_procedures']['off_dutty1']) ? $record['working_day_time_procedures']['off_dutty1'] : '');
        $punchIn    = $record['da']['att_in_time'] ?$record['da']['att_in_time']:'';
        $punchOut   = $record['da']['att_out_time'] ?$record['da']['att_out_time']:'';

       

        if (!empty($punchIn) && !empty($punchOut)) {
            // $punches = $this->EmployeeDetails->query("
            //     SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
            //     FROM device_attandance
            //     WHERE emp_id = '{$empId}'
            //       AND LOGDATE >= '{$punchIn}'
            //       AND LOGDATE <= '{$punchOut}' AND status='Y'
            //     ORDER BY LOGDATE
            // ");

             $punches = $this->EmployeeDetails->query("
            SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
            FROM device_attandance
            WHERE emp_id = '{$empId}'
            AND (
                    (LOGDATE >= '{$punchIn}' AND LOGDATE <= '{$punchOut}')
                    OR DATE(LOGDATE) = '{$date}'
                )
            AND status='Y'
            ORDER BY LOGDATE
        ");
        } else {
            $punches = $this->EmployeeDetails->query("
                SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
                FROM device_attandance
                WHERE emp_id = '{$empId}'
                  AND DATE(LOGDATE) = '{$date}' AND status='Y'
                ORDER BY LOGDATE
            ");
        }

        // Calculate late punch-in (in minutes)
        $lateInMinutes = '';
        if (!empty($shiftStart) && !empty($punchIn)) {
            $shiftStartTime = strtotime($date . ' ' . $shiftStart);
            $punchInTime    = strtotime($punchIn);
            if ($punchInTime > $shiftStartTime) {
                $lateInMinutes = round(($punchInTime - $shiftStartTime) / 60);
            } else {
                $lateInMinutes = 0;
            }
        }

        // (Optional) Calculate early/late punch-out similarly
        $lateOutMinutes = '';
        if (!empty($shiftEnd) && !empty($punchOut)) {
            $shiftEndTime  = strtotime($date . ' ' . $shiftEnd);
            $punchOutTime  = strtotime($punchOut);
            if ($punchOutTime < $shiftEndTime) {
                // Early leave
                $lateOutMinutes = "-" . round(($shiftEndTime - $punchOutTime) / 60);
            } else {
                // Late overtime
                $lateOutMinutes = round(($punchOutTime - $shiftEndTime) / 60);
            }
        }

        $durationMinutes = $record['da']['duration'] ?$record['da']['duration']: 0; // total minutes worked
        $durationHHMMSS = '';

    if (!empty($durationMinutes) && is_numeric($durationMinutes)) {
    // Convert minutes to seconds first, then format
    $durationSeconds = $durationMinutes * 60;
    $hours = floor($durationSeconds / 3600);
    $minutes = floor(($durationSeconds % 3600) / 60);
    $seconds = $durationSeconds % 60;
    $durationHHMMSS = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    } else {
    $durationHHMMSS = '00:00:00';
    }

        // --- Day Type Logic ---
        $day_type = '-';
        $holidayId = isset($record['emp_proff']['HOLIDAY_GROUP_ID']) ? $record['emp_proff']['HOLIDAY_GROUP_ID'] : '';
        $dayTimeSeq = isset($record['working_day_time_procedures']['day_time_seq']) ? $record['working_day_time_procedures']['day_time_seq'] : '';

        $dayName = date('l', strtotime($date));
        $dayOfMonth = date('j', strtotime($date));
        $weekNum = floor(($dayOfMonth - 1) / 7) + 1;

        $isHoliday = false;
        if (!empty($holidayId)) {
            $hCount = $this->EmployeeDetails->query("SELECT count(*) as count FROM holidays WHERE HOLIDAY_GROUP_ID = '{$holidayId}' AND status = 1 AND HOLIDAYDATE = '{$date}'");
            if (!empty($hCount) && $hCount[0][0]['count'] > 0) {
                $day_type = 'Holiday';
                $isHoliday = true;
            }
        }

        if (!$isHoliday && !empty($dayTimeSeq)) {
            $weekdayFlag = isset($record['working_day_time_procedures'][$dayName]) ? $record['working_day_time_procedures'][$dayName] : 'Y';
            $halfDayFlag = isset($record['working_day_time_procedures'][$dayName . '_F']) ? $record['working_day_time_procedures'][$dayName . '_F'] : 'N';

            $exCount = $this->EmployeeDetails->query("SELECT count(*) as count FROM shift_exceptions WHERE shift_id = '{$dayTimeSeq}' AND week_off = 'Y' AND status = 1 AND lcase(ex_week_day) = '" . strtolower($dayName) . "' AND cast(ex_week as unsigned) = {$weekNum}");

            if ($weekdayFlag === 'N' || (!empty($exCount) && $exCount[0][0]['count'] > 0)) {
                $day_type = 'WO-' . $dayName . ' (Full Day)';
            } elseif ($halfDayFlag === 'Y') {
                $day_type = 'WO-' . $dayName . ' (Half Day)';
            }
        }

         // Fetch leave details separately
        $leaves_query = $this->EmployeeDetails->query("
            SELECT 
                elt.leave_date, 
                elt.leave_session, 
                elt.Leavestatus,
                shi.occurance
            FROM emp_leave_transactions AS elt
            JOIN leaveentries AS leaves ON leaves.LEAVEENTRYID = elt.LEAVEENTRYID
            JOIN salary_head_items AS shi ON shi.salary_head_item_pkey = leaves.salary_head_item_fkey
            WHERE leaves.EMP_fkey = '$emp_pkey' AND elt.Leavestatus IN ('Applied','Approved','Authorized')
            AND elt.leave_date ='$date'
           
        ");

        $attendanceStatus = isset($record['da']['present']) ? $record['da']['present'] : '';

        $finalStatus = $attendanceStatus;

        // Split attendance halves
        $attFH = '';
        $attSH = '';

        if(!empty($attendanceStatus) && strpos($attendanceStatus,'/') !== false){
            list($attFH,$attSH) = explode('/',$attendanceStatus);
        }else{
            $attFH = $attendanceStatus;
            $attSH = $attendanceStatus;
        }

        if(!empty($leaves_query)){

            $leaveSession = $leaves_query[0]['elt']['leave_session'];
            $occurance = $leaves_query[0]['shi']['occurance'];

            if($leaveSession == 1){
                // First half leave
                $attFH = $occurance;
            }
            elseif($leaveSession == 2){
                // Second half leave
                $attSH = $occurance;
            }
            elseif($leaveSession == 3){
                // Full day leave
                $attFH = $occurance;
                $attSH = $occurance;
            }

            $finalStatus = $attFH.'/'.$attSH;
        }

        $empData = [
            'EmpName'        => isset($record['ei']['EmpName']) ? $record['ei']['EmpName'] : '',
            'att_date'       => isset($record['da']['att_date']) ? $record['da']['att_date'] : '',
            'att_in_time'    => isset($record['da']['att_in_time']) ? $record['da']['att_in_time'] : '',
            'att_out_time'   => isset($record['da']['att_out_time']) ? $record['da']['att_out_time'] : '',
            'in_location' => !empty($record['da']['att_in_time']) && isset($record['0']['in_location'])?$record['0']['in_location']:'',
            'out_location' => !empty($record['da']['att_out_time']) && isset($record['0']['out_location'])?$record['0']['out_location']:'',
            'duration'       => $durationHHMMSS,
            'day_time_desc'  => isset($record['0']['day_time_desc']) ? $record['0']['day_time_desc'] : (isset($record['working_day_time_procedures']['day_time_desc']) ? $record['working_day_time_procedures']['day_time_desc'] : ''),
            'late_in_minutes'=> $lateInMinutes,
            'late_out_minutes'=> $lateOutMinutes,
            'detailed_leave_status' => $detailed_leave_status,
            'punches' => $punches,
            'day_type' => $day_type,
            'status' => $finalStatus
            
        ];

        // Push each employee's data to the day's array
        $groupedData[$date][] = $empData;
    }
}
               // $groupedData[$date][] = $dayEmployees;
                 }
                if (isset($groupedData[$date]) && !empty($groupedData[$date])) {
                    usort($groupedData[$date], function($a, $b) {
                        return strcmp($a['EmpName'], $b['EmpName']);
                    });
                }
                }
        }
        }
    $arr_breakdata_for_template = $groupedData;
      

            $this->set('arr_breakdata_for_template', $arr_breakdata_for_template);
            // debug($arr_breakdata_for_template);

            //Set informations needed for report

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $cr = $arr_form_data['select-criteria1'];
            $this->set('cr', $cr);
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);

            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
              
            switch ($mode) {
                
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('breakreport');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('EmployeeEarlyInReport.pdf', 'D');
                    // $this->render('earlyinreport');                
                    break;
               case 'excel':

    $str_company_code = $this->Session->read('company_code');
    $file_name = $str_company_code
        ? $str_company_code . "_Attendance.xlsx"
        : "Attendance.xlsx";

    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
    $objPHPExcel = new PHPExcel();

    /* ================= PROPERTIES ================= */
    $objPHPExcel->getProperties()
        ->setCreator("Administrator")
        ->setLastModifiedBy("Administrator")
        ->setTitle("Attendance Report")
        ->setSubject("Attendance Report")
        ->setDescription("Employee Attendance Report");

    
    $objPHPExcel->setActiveSheetIndex(0);
$worksheet = $objPHPExcel->getActiveSheet();


    /* ================= NO PUNCH STYLE ================= */
    $noPunchStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '9C0006']
        ],
        'fill' => [
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => ['rgb' => 'FFC7CE']
        ]
    ];

    /* ================= TITLE ================= */
    $worksheet->setCellValue('A1', "Attendance Report for - $f - $t");
    $worksheet->mergeCells('A1:M1');
    $worksheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
    $worksheet->getStyle('A1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

    $worksheet->setCellValue('A2', "(Report Run by $user_id at $date_time)");
    $worksheet->mergeCells('A2:M2');
    $worksheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
    $worksheet->getStyle('A2')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

    for ($colNum = 0; $colNum < 13; $colNum++) {
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($colNum)->setAutoSize(true);
    }

    if (!empty($arr_breakdata_for_template)) {

        $rowcount = 3;

        $headers = [
            'Sl. No','Employee Name','Date','Day Type','Shift Time','Punch In Time','In Location',
            'Punch Out Time','Out Location','Status','Late Punch-in','Late Punch-out','Hours Worked'
        ];

        foreach ($arr_breakdata_for_template as $date => $employees) {

            /* ===== DATE HEADER ===== */
            $formattedDateValue = date('d/m/Y', strtotime($date));
            // $worksheet->mergeCells("A{$rowcount}:M{$rowcount}");
            // $worksheet->setCellValue("A{$rowcount}", "Date: $formattedDateValue");
            // $worksheet->getStyle("A{$rowcount}")->getFont()->setBold(true);
            // $worksheet->getStyle("A{$rowcount}")->getAlignment()
                // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            // $rowcount++;

            /* ===== HEADERS ===== */
            foreach ($headers as $c => $header) {
                $worksheet->setCellValueByColumnAndRow($c, $rowcount, $header);
                $worksheet->getStyleByColumnAndRow($c, $rowcount)
                    ->getFont()->setBold(true);
            }
            $rowcount++;

            $slno = 1;

            foreach ($employees as $emp) {

                /* ===== BUILD PAIRS ===== */
                $pairs = [];
                $lastIn = null;

                if (!empty($emp['punches'])) {
                    foreach ($emp['punches'] as $p) {

                        $type = strtolower($p[0]['punch_type']);
                        $time = $p['device_attandance']['LOGDATE'];
                        $loc  = $p['device_attandance']['location'];

                        if ($type === 'in') {
                            if ($lastIn) {
                                // Output the previous IN that didn't have an OUT
                                $pairs[] = [
                                    'in_time'=>$lastIn['in_time'],
                                    'out_time'=>'',
                                    'in_location'=>$lastIn['in_location'],
                                    'out_location'=>''
                                ];
                            }
                            $lastIn = ['in_time'=>$time,'in_location'=>$loc];
                        } elseif ($type === 'out') {
                            if ($lastIn) {
                                $pairs[] = [
                                    'in_time'=>$lastIn['in_time'],
                                    'out_time'=>$time,
                                    'in_location'=>$lastIn['in_location'],
                                    'out_location'=>$loc
                                ];
                                $lastIn = null;
                            } else {
                                // OUT without a preceding IN
                                $pairs[] = [
                                    'in_time'=>'',
                                    'out_time'=>$time,
                                    'in_location'=>'',
                                    'out_location'=>$loc
                                ];
                            }
                        }
                    }

                    if ($lastIn) {
                        $pairs[] = [
                            'in_time'=>$lastIn['in_time'],
                            'out_time'=>'',
                            'in_location'=>$lastIn['in_location'],
                            'out_location'=>''
                        ];
                    }
                }

                if (empty($pairs)) {
                    $pairs[] = [
                        'in_time'=>$emp['att_in_time'],
                        'out_time'=>$emp['att_out_time'],
                        'in_location'=>$emp['in_location'],
                        'out_location'=>$emp['out_location']
                    ];
                }

                $startRow = $rowcount;

                foreach ($pairs as $pair) {

                    /* ===== Punch In ===== */
                    if (!empty($pair['in_time'])) {
                        $worksheet->setCellValue("F{$rowcount}", date('H:i:s', strtotime($pair['in_time'])));
                    } else {
                        $val = !empty($emp['detailed_leave_status']) ? $emp['detailed_leave_status'] : 'No Punch In Time';
                        $worksheet->setCellValue("F{$rowcount}", $val);
                        if (!empty($emp['detailed_leave_status'])) {
                            $worksheet->getStyle("F{$rowcount}")->applyFromArray([
                                'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'CCFFCC']]
                            ]);
                        } else {
                            $worksheet->getStyle("F{$rowcount}")->applyFromArray($noPunchStyle);
                        }
                    }

                    $worksheet->setCellValue("G{$rowcount}", $pair['in_location'] ?: '');

                    /* ===== Punch Out ===== */
                    if (!empty($pair['out_time'])) {
                        $worksheet->setCellValue("H{$rowcount}", date('H:i:s', strtotime($pair['out_time'])));
                    } else {
                        $val = !empty($emp['detailed_leave_status']) ? $emp['detailed_leave_status'] : 'No Punch Out Time';
                        $worksheet->setCellValue("H{$rowcount}", $val);
                         if (!empty($emp['detailed_leave_status'])) {
                            $worksheet->getStyle("H{$rowcount}")->applyFromArray([
                                'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'CCFFCC']]
                            ]);
                        } else {
                            $worksheet->getStyle("H{$rowcount}")->applyFromArray($noPunchStyle);
                        }
                    }

                    $worksheet->setCellValue("I{$rowcount}", $pair['out_location'] ?: '');

                    $rowcount++;
                }

                $endRow = $rowcount - 1;

                /* ===== COMMON DATA ===== */
                $worksheet->setCellValue("A{$startRow}", $slno++);
                $worksheet->setCellValue("B{$startRow}", trim($emp['EmpName']));
                $worksheet->setCellValue("C{$startRow}", !empty($emp['att_date']) ? date('d/m/Y', strtotime($emp['att_date'])) : $formattedDateValue);
                $worksheet->setCellValue("D{$startRow}", $emp['day_type']);
                $worksheet->setCellValue("E{$startRow}", $emp['day_time_desc']);
                $worksheet->setCellValue("J{$startRow}", $emp['status']);
                $worksheet->setCellValue("K{$startRow}", $emp['late_in_minutes']);
                $worksheet->setCellValue("L{$startRow}", $emp['late_out_minutes']);
                $worksheet->setCellValue("M{$startRow}", $emp['duration']);

                if ($startRow < $endRow) {
                    foreach (['A','B','C','D','E','J','K','L','M'] as $c) {
                        $worksheet->mergeCells("{$c}{$startRow}:{$c}{$endRow}");
                    }
                }
            }

            $rowcount++;
        }

        $worksheet->getStyle("A4:M".($rowcount-1))
            ->applyFromArray([
                'borders'=>['allborders'=>['style'=>PHPExcel_Style_Border::BORDER_THIN]]
            ]);
    }

    $worksheet->setShowGridlines(false);
    $worksheet->setTitle('Attendance');

    if (ob_get_length()) ob_end_clean();

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $objWriter->save($file_name);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$file_name\"");
    readfile($file_name);
    unlink($file_name);
    break;

                default:
                    $this->set('mode', '');
                    $this->render('dailyattendanceextr');
                    break;
            }
        }


//     private function generatDailyAttendanceReportEXTR($type,$mode)
//     {
//         $arr_form_data = $_REQUEST;
//         $this->Earlyin->useDbConfig = $this->Session->read('ds');
//         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//         $this->DbConfig->useDbConfig = $this->Session->read('ds');
//         $company_code = $this->Session->read('company_code');
//         $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
//        // $to = date("Y-m-d", strtotime($arr_form_data['reportto'] . ' +1 day'));
//         $to = date("Y-m-d", strtotime($arr_form_data['reportto']));
//         $f = date('d/m/Y', strtotime($arr_form_data['reportfrom']));
//         $t = date("d/m/Y", strtotime($arr_form_data['reportto']));
//         $this->set('from', $from);
//         $this->set('to', $to);
//         $this->set('f', $f);
//         $this->set('t', $t);


//         //Dates array to store the selected dates.
//         $Dates = array();
//         $fromDate = new DateTime($from);
//         $toDate = new DateTime($to);

//         // Add each date to the array
//         while ($fromDate <= $toDate) {
//             $Dates[] = $fromDate->format('Y-m-d');
//             $fromDate->modify('+1 day');
//         }

//         $groupedData = array();
//         $arr_breakdata = array();
//         $int_criterias_count = $arr_form_data['hidden-criterias-count'];
//         for ($i = 1; $i <= $int_criterias_count; $i++) {
//             $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
//             $arr_breakdata = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
//         }

//         if ($str_criteria_item == '') {
//             echo "<h1>No Criteria Selected</h1>";
//             die();
//         }

//         if (!isset($arr_form_data[$str_criteria_item])) {
//             echo "<h1>No Criteria Selected</h1>";
//             die();
//         }

//         if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
//             $conditions = "ei.emp_status in('1','2')";
//         } else {
//             $conditions = "ei.emp_status ='1'";
//         }

//         $arr_breakdata_for_template = array();
//         if (isset($arr_breakdata) && !empty($arr_breakdata)) {
             
//             if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

//            foreach ($Dates as $date) {
//                 foreach ($arr_breakdata as $breakdata) {
//                    $arr_deviceatt = $this->EmployeeDetails->query("
//         SELECT ei.emp_pkey,ei.emp_id,ei.EmpName, da.att_date,da.att_in_time,da.att_out_time,da.duration,da.present,working_day_time_procedures.day_time_desc,on_dutty1,off_dutty1,
//         emp_proff.HOLIDAY_GROUP_ID, working_day_time_procedures.day_time_seq,
//         working_day_time_procedures.Sunday, working_day_time_procedures.Monday, working_day_time_procedures.Tuesday, working_day_time_procedures.Wednesday, working_day_time_procedures.Thursday, working_day_time_procedures.Friday, working_day_time_procedures.Saturday,
//         working_day_time_procedures.Sunday_F, working_day_time_procedures.Monday_F, working_day_time_procedures.Tuesday_F, working_day_time_procedures.Wednesday_F, working_day_time_procedures.Thursday_F, working_day_time_procedures.Friday_F, working_day_time_procedures.Saturday_F,
//         (SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND (d.c1= 'IN' OR d.DIRECTION = 'IN')
//         ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_in_time)) ASC LIMIT 1 ) AS in_location,
//         ( SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND (d.c1= 'OUT' OR d.DIRECTION = 'OUT')
//         ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_out_time)) ASC LIMIT 1) AS out_location
//         FROM employee_info AS ei
//         LEFT JOIN emp_detail_timeattandance AS da ON (ei.emp_pkey = da.emp_pkey) AND da.att_date = '$date'
//         LEFT JOIN emp_proff AS emp_proff ON (ei.emp_pkey = emp_proff.emp_fkey)
//         LEFT JOIN working_day_time_procedures  AS working_day_time_procedures ON (working_day_time_procedures.day_time_seq = emp_proff.day_time_seq)
//         WHERE $conditions AND ei.emp_pkey = '$breakdata' ");

      
//       if(!empty($arr_deviceatt)){

//       $empId = $arr_deviceatt[0]['ei']['emp_id'];
//       $emp_pkey = $arr_deviceatt[0]['ei']['emp_pkey'];

//       $leave_txn = $this->EmployeeDetails->query("SELECT le.ISAutherized, le.ISAPPROVED, le.LEAVESTATUS, tr.leave_session FROM emp_leave_transactions tr JOIN leaveentries le ON tr.LEAVEENTRYID = le.LEAVEENTRYID WHERE tr.leave_date = '$date' AND le.EMP_fkey = '$emp_pkey' LIMIT 1");
//         $detailed_leave_status = '';
//         if(!empty($leave_txn)){
//             if($leave_txn[0]['le']['ISAPPROVED'] == 1) $detailed_leave_status = 'Leave Approved';
//             elseif($leave_txn[0]['le']['ISAutherized'] == 1) $detailed_leave_status = 'Leave Authorized';
//             else $detailed_leave_status = 'Leave ' . ($leave_txn[0]['le']['LEAVESTATUS'] ?: 'Applied');
//         }

//          $shiftStart = $arr_deviceatt['0']['working_day_time_procedures']['on_dutty1'] ?$arr_deviceatt['0']['working_day_time_procedures']['on_dutty1']:'';
//         $shiftEnd   = $arr_deviceatt['0']['working_day_time_procedures']['off_dutty1'] ?$arr_deviceatt['0']['working_day_time_procedures']['off_dutty1']:'';
//         $punchIn    = $arr_deviceatt['0']['da']['att_in_time'] ?$arr_deviceatt['0']['da']['att_in_time']:'';
//         $punchOut   = $arr_deviceatt['0']['da']['att_out_time'] ?$arr_deviceatt['0']['da']['att_out_time']:'';


        

//         if (!empty($punchIn) && !empty($punchOut)) {
//             // $punches = $this->EmployeeDetails->query("
//             //     SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
//             //     FROM device_attandance
//             //     WHERE emp_id = '{$empId}'
//             //       AND LOGDATE >= '{$punchIn}'
//             //       AND LOGDATE <= '{$punchOut}' AND  status='Y'
//             //     ORDER BY LOGDATE
//             // ");
//             $punches = $this->EmployeeDetails->query("
//             SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
//             FROM device_attandance
//             WHERE emp_id = '{$empId}'
//             AND (
//                     (LOGDATE >= '{$punchIn}' AND LOGDATE <= '{$punchOut}')
//                     OR DATE(LOGDATE) = '{$date}'
//                 )
//             AND status='Y'
//             ORDER BY LOGDATE
//         ");
//         } else {
//             $punches = $this->EmployeeDetails->query("
//                 SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
//                 FROM device_attandance
//                 WHERE emp_id = '{$empId}'
//                   AND DATE(LOGDATE) = '{$date}' AND status='Y'
//                 ORDER BY LOGDATE
//             ");
//         }

//         // Calculate late punch-in (in minutes)
//         $lateInMinutes = '';
//         if (!empty($shiftStart) && !empty($punchIn)) {
//             $shiftStartTime = strtotime($date . ' ' . $shiftStart);
//             $punchInTime    = strtotime($punchIn);
//             if ($punchInTime > $shiftStartTime) {
//                 $lateInMinutes = round(($punchInTime - $shiftStartTime) / 60);
//             } else {
//                 $lateInMinutes = 0;
//             }
//         }

//         // (Optional) Calculate early/late punch-out similarly
//         $lateOutMinutes = '';
//         if (!empty($shiftEnd) && !empty($punchOut)) {
//             $shiftEndTime  = strtotime($date . ' ' . $shiftEnd);
//             $punchOutTime  = strtotime($punchOut);
//             if ($punchOutTime < $shiftEndTime) {
//                 // Early leave
//                 $lateOutMinutes = "-" . round(($shiftEndTime - $punchOutTime) / 60);
//             } else {
//                 // Late overtime
//                 $lateOutMinutes = round(($punchOutTime - $shiftEndTime) / 60);
//             }
//         }

//         $durationMinutes = $arr_deviceatt['0']['da']['duration']?$arr_deviceatt['0']['da']['duration']: 0; // total minutes worked
//         $durationHHMMSS = '';

//     if (!empty($durationMinutes) && is_numeric($durationMinutes)) {
//     // Convert minutes to seconds first, then format
//     $durationSeconds = $durationMinutes * 60;
//     $hours = floor($durationSeconds / 3600);
//     $minutes = floor(($durationSeconds % 3600) / 60);
//     $seconds = $durationSeconds % 60;
//     $durationHHMMSS = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
//     } else {
//     $durationHHMMSS = '00:00:00';
//     }

//         // --- Day Type Logic ---
//         $day_type = '-';
//         $holidayId = isset($arr_deviceatt[0]['emp_proff']['HOLIDAY_GROUP_ID']) ? $arr_deviceatt[0]['emp_proff']['HOLIDAY_GROUP_ID'] : '';
//         $dayTimeSeq = isset($arr_deviceatt[0]['working_day_time_procedures']['day_time_seq']) ? $arr_deviceatt[0]['working_day_time_procedures']['day_time_seq'] : '';

//         $dayName = date('l', strtotime($date));
//         $dayOfMonth = date('j', strtotime($date));
//         $weekNum = floor(($dayOfMonth - 1) / 7) + 1;

//         $isHoliday = false;
//         if (!empty($holidayId)) {
//             $hCount = $this->EmployeeDetails->query("SELECT count(*) as count FROM holidays WHERE HOLIDAY_GROUP_ID = '{$holidayId}' AND status = 1 AND HOLIDAYDATE = '{$date}'");
//             if (!empty($hCount) && $hCount[0][0]['count'] > 0) {
//                 $day_type = 'Holiday';
//                 $isHoliday = true;
//             }
//         }

//         if (!$isHoliday && !empty($dayTimeSeq)) {
//             $weekdayFlag = isset($arr_deviceatt[0]['working_day_time_procedures'][$dayName]) ? $arr_deviceatt[0]['working_day_time_procedures'][$dayName] : 'Y';
//             $halfDayFlag = isset($arr_deviceatt[0]['working_day_time_procedures'][$dayName . '_F']) ? $arr_deviceatt[0]['working_day_time_procedures'][$dayName . '_F'] : 'N';

//             $exCount = $this->EmployeeDetails->query("SELECT count(*) as count FROM shift_exceptions WHERE shift_id = '{$dayTimeSeq}' AND week_off = 'Y' AND status = 1 AND lcase(ex_week_day) = '" . strtolower($dayName) . "' AND cast(ex_week as unsigned) = {$weekNum}");

//             if ($weekdayFlag === 'N' || (!empty($exCount) && $exCount[0][0]['count'] > 0)) {
//                 $day_type = 'WO-' . $dayName . ' (Full Day)';
//             } elseif ($halfDayFlag === 'Y') {
//                 $day_type = 'WO-' . $dayName . ' (Half Day)';
//             }
//         }

//          // Fetch leave details separately
//         $leaves_query = $this->EmployeeDetails->query("
//             SELECT 
//                 elt.leave_date, 
//                 elt.leave_session, 
//                 elt.Leavestatus,
//                 shi.occurance
//             FROM emp_leave_transactions AS elt
//             JOIN leaveentries AS leaves ON leaves.LEAVEENTRYID = elt.LEAVEENTRYID
//             JOIN salary_head_items AS shi ON shi.salary_head_item_pkey = leaves.salary_head_item_fkey
//             WHERE leaves.EMP_fkey = '$emp_pkey' AND elt.Leavestatus IN ('Applied','Approved','Authorized')
//             AND elt.leave_date ='$date'
           
//         ");

//         $attendanceStatus = isset($arr_deviceatt[0]['da']['present']) ? $arr_deviceatt[0]['da']['present'] : '';

//         $finalStatus = $attendanceStatus;

//         // Split attendance halves
//         $attFH = '';
//         $attSH = '';

//         if(!empty($attendanceStatus) && strpos($attendanceStatus,'/') !== false){
//             list($attFH,$attSH) = explode('/',$attendanceStatus);
//         }else{
//             $attFH = $attendanceStatus;
//             $attSH = $attendanceStatus;
//         }

//         if(!empty($leaves_query)){

//             $leaveSession = $leaves_query[0]['elt']['leave_session'];
//             $occurance = $leaves_query[0]['shi']['occurance'];

//             if($leaveSession == 1){
//                 // First half leave
//                 $attFH = $occurance;
//             }
//             elseif($leaveSession == 2){
//                 // Second half leave
//                 $attSH = $occurance;
//             }
//             elseif($leaveSession == 3){
//                 // Full day leave
//                 $attFH = $occurance;
//                 $attSH = $occurance;
//             }

//             $finalStatus = $attFH.'/'.$attSH;
//         }
       

//         // Prepare an associative array of needed fields
//         $empData = [
//         'EmpName' => isset($arr_deviceatt['0']['ei']['EmpName'])?$arr_deviceatt['0']['ei']['EmpName']:'',
//         'att_date' => isset($arr_deviceatt['0']['da']['att_date'])?$arr_deviceatt['0']['da']['att_date']:'',
//         'att_in_time' => isset($arr_deviceatt['0']['da']['att_in_time'])?$arr_deviceatt['0']['da']['att_in_time']:'',
//         'att_out_time' => isset($arr_deviceatt['0']['da']['att_out_time'])?$arr_deviceatt['0']['da']['att_out_time']:'',
//         'in_location' => !empty($arr_deviceatt['0']['da']['att_in_time']) && isset($arr_deviceatt['0']['0']['in_location'])?$arr_deviceatt['0']['0']['in_location']:'',
//         'out_location' => !empty($arr_deviceatt['0']['da']['att_out_time']) && isset($arr_deviceatt['0']['0']['out_location'])?$arr_deviceatt['0']['0']['out_location']:'',
//         'duration' => $durationHHMMSS,
//         'day_time_desc' => isset($arr_deviceatt['0']['working_day_time_procedures']['day_time_desc'])?$arr_deviceatt['0']['working_day_time_procedures']['day_time_desc']:'',
//         'late_in_minutes'=> $lateInMinutes,
//         'late_out_minutes'=> $lateOutMinutes,
//         'detailed_leave_status' => $detailed_leave_status,
//         'punches'=>$punches,
//         'day_type' => $day_type,
//         // 'status' => isset($arr_deviceatt['0']['da']['present']) ? $arr_deviceatt['0']['da']['present'] : ''
//         'status' => $finalStatus
//          ];
//                 $groupedData[$date][] = $empData;
//         }
//                  }
//                 if (isset($groupedData[$date]) && !empty($groupedData[$date])) {
//                     usort($groupedData[$date], function($a, $b) {
//                         return strcmp($a['EmpName'], $b['EmpName']);
//                     });
//                 }
//                 }
//             }
//              else{
//  foreach ($Dates as $date) {
//                 foreach ($arr_breakdata as $breakdata) {
                    
//                          $arr_deviceatt = $this->EmployeeDetails->query("
//         SELECT ei.emp_pkey,ei.emp_id,ei.EmpName, da.att_date,da.att_in_time,da.att_out_time,da.duration,da.present,working_day_time_procedures.day_time_desc,on_dutty1,off_dutty1 ,
//         emp_proff.HOLIDAY_GROUP_ID, working_day_time_procedures.day_time_seq,
//         working_day_time_procedures.Sunday, working_day_time_procedures.Monday, working_day_time_procedures.Tuesday, working_day_time_procedures.Wednesday, working_day_time_procedures.Thursday, working_day_time_procedures.Friday, working_day_time_procedures.Saturday,
//         working_day_time_procedures.Sunday_F, working_day_time_procedures.Monday_F, working_day_time_procedures.Tuesday_F, working_day_time_procedures.Wednesday_F, working_day_time_procedures.Thursday_F, working_day_time_procedures.Friday_F, working_day_time_procedures.Saturday_F,
//         (SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND (d.c1= 'IN' OR d.DIRECTION = 'IN')
//         ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_in_time)) ASC LIMIT 1 ) AS in_location,
//         ( SELECT d.C3 FROM device_attandance AS d WHERE d.emp_id = ei.emp_id AND (d.c1= 'OUT' OR d.DIRECTION = 'OUT')
//         ORDER BY ABS(TIMESTAMPDIFF(SECOND, d.LOGDATE, da.att_out_time)) ASC LIMIT 1) AS out_location
//         FROM employee_info AS ei
//         LEFT JOIN emp_detail_timeattandance AS da ON (ei.emp_pkey = da.emp_pkey) AND da.att_date = '$date'
//         LEFT JOIN emp_proff AS emp_proff ON (ei.emp_pkey = emp_proff.emp_fkey)
//         LEFT JOIN working_day_time_procedures  AS working_day_time_procedures ON (working_day_time_procedures.day_time_seq = emp_proff.day_time_seq)
//         LEFT JOIN leaveentries le
//     ON le.EMP_fkey = ei.emp_pkey

//     LEFT JOIN emp_leave_transactions elt
//         ON elt.LEAVEENTRYID = le.LEAVEENTRYID
//         AND elt.leave_date = '$date'
//         WHERE $conditions AND ei.branch_code  = '$breakdata' order by ei.EmpName");

      
        

       
//                 // Initialize an array for the day's data
// $dayEmployees = []; 


// // Added by Antigravity to prevent duplicates
// $processed_employees = [];

// if (!empty($arr_deviceatt)) {

//     foreach ($arr_deviceatt as $record) {

//     $empId = $record['ei']['emp_id'];
//     $emp_pkey = $record['ei']['emp_pkey'];

//     if (in_array($empId, $processed_employees)) {
//         continue;
//     }
//     $processed_employees[] = $empId;

//     $leave_txn = $this->EmployeeDetails->query("SELECT le.ISAutherized, le.ISAPPROVED, le.LEAVESTATUS, tr.leave_session FROM emp_leave_transactions tr JOIN leaveentries le ON tr.LEAVEENTRYID = le.LEAVEENTRYID WHERE tr.leave_date = '$date' AND le.EMP_fkey = '$emp_pkey' LIMIT 1");
//     $detailed_leave_status = '';
//     if(!empty($leave_txn)){
//         if($leave_txn[0]['le']['ISAPPROVED'] == 1) $detailed_leave_status = 'Leave Approved';
//         elseif($leave_txn[0]['le']['ISAutherized'] == 1) $detailed_leave_status = 'Leave Authorized';
//         else $detailed_leave_status = 'Leave ' . ($leave_txn[0]['le']['LEAVESTATUS'] ?: 'Applied');
//     }

//         $shiftStart = $record['working_day_time_procedures']['on_dutty1'] ?$record['working_day_time_procedures']['on_dutty1']:'';
//         $shiftEnd   = $record['working_day_time_procedures']['off_dutty1'] ?$record['working_day_time_procedures']['off_dutty1']:'';
//         $punchIn    = $record['da']['att_in_time'] ?$record['da']['att_in_time']:'';
//         $punchOut   = $record['da']['att_out_time'] ?$record['da']['att_out_time']:'';

       

//         if (!empty($punchIn) && !empty($punchOut)) {
//             // $punches = $this->EmployeeDetails->query("
//             //     SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
//             //     FROM device_attandance
//             //     WHERE emp_id = '{$empId}'
//             //       AND LOGDATE >= '{$punchIn}'
//             //       AND LOGDATE <= '{$punchOut}' AND status='Y'
//             //     ORDER BY LOGDATE
//             // ");

//              $punches = $this->EmployeeDetails->query("
//             SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
//             FROM device_attandance
//             WHERE emp_id = '{$empId}'
//             AND (
//                     (LOGDATE >= '{$punchIn}' AND LOGDATE <= '{$punchOut}')
//                     OR DATE(LOGDATE) = '{$date}'
//                 )
//             AND status='Y'
//             ORDER BY LOGDATE
//         ");
//         } else {
//             $punches = $this->EmployeeDetails->query("
//                 SELECT LOGDATE, COALESCE(c1, DIRECTION) AS punch_type, C3 AS location
//                 FROM device_attandance
//                 WHERE emp_id = '{$empId}'
//                   AND DATE(LOGDATE) = '{$date}' AND status='Y'
//                 ORDER BY LOGDATE
//             ");
//         }

//         // Calculate late punch-in (in minutes)
//         $lateInMinutes = '';
//         if (!empty($shiftStart) && !empty($punchIn)) {
//             $shiftStartTime = strtotime($date . ' ' . $shiftStart);
//             $punchInTime    = strtotime($punchIn);
//             if ($punchInTime > $shiftStartTime) {
//                 $lateInMinutes = round(($punchInTime - $shiftStartTime) / 60);
//             } else {
//                 $lateInMinutes = 0;
//             }
//         }

//         // (Optional) Calculate early/late punch-out similarly
//         $lateOutMinutes = '';
//         if (!empty($shiftEnd) && !empty($punchOut)) {
//             $shiftEndTime  = strtotime($date . ' ' . $shiftEnd);
//             $punchOutTime  = strtotime($punchOut);
//             if ($punchOutTime < $shiftEndTime) {
//                 // Early leave
//                 $lateOutMinutes = "-" . round(($shiftEndTime - $punchOutTime) / 60);
//             } else {
//                 // Late overtime
//                 $lateOutMinutes = round(($punchOutTime - $shiftEndTime) / 60);
//             }
//         }

//         $durationMinutes = $record['da']['duration'] ?$record['da']['duration']: 0; // total minutes worked
//         $durationHHMMSS = '';

//     if (!empty($durationMinutes) && is_numeric($durationMinutes)) {
//     // Convert minutes to seconds first, then format
//     $durationSeconds = $durationMinutes * 60;
//     $hours = floor($durationSeconds / 3600);
//     $minutes = floor(($durationSeconds % 3600) / 60);
//     $seconds = $durationSeconds % 60;
//     $durationHHMMSS = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
//     } else {
//     $durationHHMMSS = '00:00:00';
//     }

//         // --- Day Type Logic ---
//         $day_type = '-';
//         $holidayId = isset($record['emp_proff']['HOLIDAY_GROUP_ID']) ? $record['emp_proff']['HOLIDAY_GROUP_ID'] : '';
//         $dayTimeSeq = isset($record['working_day_time_procedures']['day_time_seq']) ? $record['working_day_time_procedures']['day_time_seq'] : '';

//         $dayName = date('l', strtotime($date));
//         $dayOfMonth = date('j', strtotime($date));
//         $weekNum = floor(($dayOfMonth - 1) / 7) + 1;

//         $isHoliday = false;
//         if (!empty($holidayId)) {
//             $hCount = $this->EmployeeDetails->query("SELECT count(*) as count FROM holidays WHERE HOLIDAY_GROUP_ID = '{$holidayId}' AND status = 1 AND HOLIDAYDATE = '{$date}'");
//             if (!empty($hCount) && $hCount[0][0]['count'] > 0) {
//                 $day_type = 'Holiday';
//                 $isHoliday = true;
//             }
//         }

//         if (!$isHoliday && !empty($dayTimeSeq)) {
//             $weekdayFlag = isset($record['working_day_time_procedures'][$dayName]) ? $record['working_day_time_procedures'][$dayName] : 'Y';
//             $halfDayFlag = isset($record['working_day_time_procedures'][$dayName . '_F']) ? $record['working_day_time_procedures'][$dayName . '_F'] : 'N';

//             $exCount = $this->EmployeeDetails->query("SELECT count(*) as count FROM shift_exceptions WHERE shift_id = '{$dayTimeSeq}' AND week_off = 'Y' AND status = 1 AND lcase(ex_week_day) = '" . strtolower($dayName) . "' AND cast(ex_week as unsigned) = {$weekNum}");

//             if ($weekdayFlag === 'N' || (!empty($exCount) && $exCount[0][0]['count'] > 0)) {
//                 $day_type = 'WO-' . $dayName . ' (Full Day)';
//             } elseif ($halfDayFlag === 'Y') {
//                 $day_type = 'WO-' . $dayName . ' (Half Day)';
//             }
//         }

//          // Fetch leave details separately
//         $leaves_query = $this->EmployeeDetails->query("
//             SELECT 
//                 elt.leave_date, 
//                 elt.leave_session, 
//                 elt.Leavestatus,
//                 shi.occurance
//             FROM emp_leave_transactions AS elt
//             JOIN leaveentries AS leaves ON leaves.LEAVEENTRYID = elt.LEAVEENTRYID
//             JOIN salary_head_items AS shi ON shi.salary_head_item_pkey = leaves.salary_head_item_fkey
//             WHERE leaves.EMP_fkey = '$emp_pkey' AND elt.Leavestatus IN ('Applied','Approved','Authorized')
//             AND elt.leave_date ='$date'
           
//         ");

//         $attendanceStatus = isset($record['da']['present']) ? $record['da']['present'] : '';

//         $finalStatus = $attendanceStatus;

//         // Split attendance halves
//         $attFH = '';
//         $attSH = '';

//         if(!empty($attendanceStatus) && strpos($attendanceStatus,'/') !== false){
//             list($attFH,$attSH) = explode('/',$attendanceStatus);
//         }else{
//             $attFH = $attendanceStatus;
//             $attSH = $attendanceStatus;
//         }

//         if(!empty($leaves_query)){

//             $leaveSession = $leaves_query[0]['elt']['leave_session'];
//             $occurance = $leaves_query[0]['shi']['occurance'];

//             if($leaveSession == 1){
//                 // First half leave
//                 $attFH = $occurance;
//             }
//             elseif($leaveSession == 2){
//                 // Second half leave
//                 $attSH = $occurance;
//             }
//             elseif($leaveSession == 3){
//                 // Full day leave
//                 $attFH = $occurance;
//                 $attSH = $occurance;
//             }

//             $finalStatus = $attFH.'/'.$attSH;
//         }

//         $empData = [
//             'EmpName'        => isset($record['ei']['EmpName']) ? $record['ei']['EmpName'] : '',
//             'att_date'       => isset($record['da']['att_date']) ? $record['da']['att_date'] : '',
//             'att_in_time'    => isset($record['da']['att_in_time']) ? $record['da']['att_in_time'] : '',
//             'att_out_time'   => isset($record['da']['att_out_time']) ? $record['da']['att_out_time'] : '',
//             'in_location' => !empty($record['da']['att_in_time']) && isset($record['0']['in_location'])?$record['0']['in_location']:'',
//             'out_location' => !empty($record['da']['att_out_time']) && isset($record['0']['out_location'])?$record['0']['out_location']:'',
//             'duration'       => $durationHHMMSS,
//             'day_time_desc'  => isset($record['working_day_time_procedures']['day_time_desc']) ? $record['working_day_time_procedures']['day_time_desc'] : '',
//             'late_in_minutes'=> $lateInMinutes,
//             'late_out_minutes'=> $lateOutMinutes,
//             'detailed_leave_status' => $detailed_leave_status,
//             'punches' => $punches,
//             'day_type' => $day_type,
//             'status' => $finalStatus
            
//         ];

//         // Push each employee's data to the day's array
//         $groupedData[$date][] = $empData;
//     }
// }
//                // $groupedData[$date][] = $dayEmployees;
//                  }
//                 if (isset($groupedData[$date]) && !empty($groupedData[$date])) {
//                     usort($groupedData[$date], function($a, $b) {
//                         return strcmp($a['EmpName'], $b['EmpName']);
//                     });
//                 }
//                 }
//         }
//         }
//     $arr_breakdata_for_template = $groupedData;
      

//             $this->set('arr_breakdata_for_template', $arr_breakdata_for_template);
//             // debug($arr_breakdata_for_template);

//             //Set informations needed for report

//             $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
//             $user_name = $this->Session->read('user_name');
//             $this->set('user_name', $user_name);
//             $cr = $arr_form_data['select-criteria1'];
//             $this->set('cr', $cr);
//             $user_id = $this->Session->read('login_user_id');
//             date_default_timezone_set('Asia/Kolkata');
//             $date_time = date('d-m-Y H:i');
//             $this->set('user_id', $user_id);
//             $this->set('date_time', $date_time);

//             $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
//             $this->set('arr_comp_contact_info', $arr_comp_contact_info);
              
//             switch ($mode) {
                
//                 case 'pdf':
//                     //echo "entered in";die();
//                     $this->set('mode', 'pdf');
//                     $view = new View($this, false);
//                     $view_output = $view->render('breakreport');
//                     //   debug($view_output);
//                     App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

//                     $html2pdf = new HTML2PDF('P', 'A2', 'en');
//                     //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
//                     //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
//                     $html2pdf->pdf->SetDisplayMode('fullpage');
//                     $html2pdf->writeHTML($view_output);
//                     $html2pdf->Output('EmployeeEarlyInReport.pdf', 'D');
//                     // $this->render('earlyinreport');                
//                     break;
//                case 'excel':

//     $str_company_code = $this->Session->read('company_code');
//     $file_name = $str_company_code
//         ? $str_company_code . "_Attendance.xlsx"
//         : "Attendance.xlsx";

//     App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
//     $objPHPExcel = new PHPExcel();

//     /* ================= PROPERTIES ================= */
//     $objPHPExcel->getProperties()
//         ->setCreator("Administrator")
//         ->setLastModifiedBy("Administrator")
//         ->setTitle("Attendance Report")
//         ->setSubject("Attendance Report")
//         ->setDescription("Employee Attendance Report");

    
//     $objPHPExcel->setActiveSheetIndex(0);
// $worksheet = $objPHPExcel->getActiveSheet();


//     /* ================= NO PUNCH STYLE ================= */
//     $noPunchStyle = [
//         'font' => [
//             'bold' => true,
//             'color' => ['rgb' => '9C0006']
//         ],
//         'fill' => [
//             'type' => PHPExcel_Style_Fill::FILL_SOLID,
//             'color' => ['rgb' => 'FFC7CE']
//         ]
//     ];

//     /* ================= TITLE ================= */
//     $worksheet->setCellValue('A1', "Attendance Report for - $f - $t");
//     $worksheet->mergeCells('A1:M1');
//     $worksheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
//     $worksheet->getStyle('A1')->getAlignment()
//         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

//     $worksheet->setCellValue('A2', "(Report Run by $user_id at $date_time)");
//     $worksheet->mergeCells('A2:M2');
//     $worksheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
//     $worksheet->getStyle('A2')->getAlignment()
//         ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

//     for ($colNum = 0; $colNum < 13; $colNum++) {
//         $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($colNum)->setAutoSize(true);
//     }

//     if (!empty($arr_breakdata_for_template)) {

//         $rowcount = 3;

//         $headers = [
//             'Sl. No','Employee Name','Date','Day Type','Shift Time','Punch In Time','In Location',
//             'Punch Out Time','Out Location','Status','Late Punch-in','Late Punch-out','Hours Worked'
//         ];

//         foreach ($arr_breakdata_for_template as $date => $employees) {

//             /* ===== DATE HEADER ===== */
//             $formattedDateValue = date('d/m/Y', strtotime($date));
//             // $worksheet->mergeCells("A{$rowcount}:M{$rowcount}");
//             // $worksheet->setCellValue("A{$rowcount}", "Date: $formattedDateValue");
//             // $worksheet->getStyle("A{$rowcount}")->getFont()->setBold(true);
//             // $worksheet->getStyle("A{$rowcount}")->getAlignment()
//                 // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//             // $rowcount++;

//             /* ===== HEADERS ===== */
//             foreach ($headers as $c => $header) {
//                 $worksheet->setCellValueByColumnAndRow($c, $rowcount, $header);
//                 $worksheet->getStyleByColumnAndRow($c, $rowcount)
//                     ->getFont()->setBold(true);
//             }
//             $rowcount++;

//             $slno = 1;

//             foreach ($employees as $emp) {

//                 /* ===== BUILD PAIRS ===== */
//                 $pairs = [];
//                 $lastIn = null;

//                 if (!empty($emp['punches'])) {
//                     foreach ($emp['punches'] as $p) {

//                         $type = strtolower($p[0]['punch_type']);
//                         $time = $p['device_attandance']['LOGDATE'];
//                         $loc  = $p['device_attandance']['location'];

//                         if ($type === 'in') {
//                             if ($lastIn) {
//                                 // Output the previous IN that didn't have an OUT
//                                 $pairs[] = [
//                                     'in_time'=>$lastIn['in_time'],
//                                     'out_time'=>'',
//                                     'in_location'=>$lastIn['in_location'],
//                                     'out_location'=>''
//                                 ];
//                             }
//                             $lastIn = ['in_time'=>$time,'in_location'=>$loc];
//                         } elseif ($type === 'out') {
//                             if ($lastIn) {
//                                 $pairs[] = [
//                                     'in_time'=>$lastIn['in_time'],
//                                     'out_time'=>$time,
//                                     'in_location'=>$lastIn['in_location'],
//                                     'out_location'=>$loc
//                                 ];
//                                 $lastIn = null;
//                             } else {
//                                 // OUT without a preceding IN
//                                 $pairs[] = [
//                                     'in_time'=>'',
//                                     'out_time'=>$time,
//                                     'in_location'=>'',
//                                     'out_location'=>$loc
//                                 ];
//                             }
//                         }
//                     }

//                     if ($lastIn) {
//                         $pairs[] = [
//                             'in_time'=>$lastIn['in_time'],
//                             'out_time'=>'',
//                             'in_location'=>$lastIn['in_location'],
//                             'out_location'=>''
//                         ];
//                     }
//                 }

//                 if (empty($pairs)) {
//                     $pairs[] = [
//                         'in_time'=>$emp['att_in_time'],
//                         'out_time'=>$emp['att_out_time'],
//                         'in_location'=>$emp['in_location'],
//                         'out_location'=>$emp['out_location']
//                     ];
//                 }

//                 $startRow = $rowcount;

//                 foreach ($pairs as $pair) {

//                     /* ===== Punch In ===== */
//                     if (!empty($pair['in_time'])) {
//                         $worksheet->setCellValue("F{$rowcount}", date('H:i:s', strtotime($pair['in_time'])));
//                     } else {
//                         $val = !empty($emp['detailed_leave_status']) ? $emp['detailed_leave_status'] : 'No Punch In Time';
//                         $worksheet->setCellValue("F{$rowcount}", $val);
//                         if (!empty($emp['detailed_leave_status'])) {
//                             $worksheet->getStyle("F{$rowcount}")->applyFromArray([
//                                 'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'CCFFCC']]
//                             ]);
//                         } else {
//                             $worksheet->getStyle("F{$rowcount}")->applyFromArray($noPunchStyle);
//                         }
//                     }

//                     $worksheet->setCellValue("G{$rowcount}", $pair['in_location'] ?: '');

//                     /* ===== Punch Out ===== */
//                     if (!empty($pair['out_time'])) {
//                         $worksheet->setCellValue("H{$rowcount}", date('H:i:s', strtotime($pair['out_time'])));
//                     } else {
//                         $val = !empty($emp['detailed_leave_status']) ? $emp['detailed_leave_status'] : 'No Punch Out Time';
//                         $worksheet->setCellValue("H{$rowcount}", $val);
//                          if (!empty($emp['detailed_leave_status'])) {
//                             $worksheet->getStyle("H{$rowcount}")->applyFromArray([
//                                 'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'CCFFCC']]
//                             ]);
//                         } else {
//                             $worksheet->getStyle("H{$rowcount}")->applyFromArray($noPunchStyle);
//                         }
//                     }

//                     $worksheet->setCellValue("I{$rowcount}", $pair['out_location'] ?: '');

//                     $rowcount++;
//                 }

//                 $endRow = $rowcount - 1;

//                 /* ===== COMMON DATA ===== */
//                 $worksheet->setCellValue("A{$startRow}", $slno++);
//                 $worksheet->setCellValue("B{$startRow}", trim($emp['EmpName']));
//                 $worksheet->setCellValue("C{$startRow}", !empty($emp['att_date']) ? date('d/m/Y', strtotime($emp['att_date'])) : $formattedDateValue);
//                 $worksheet->setCellValue("D{$startRow}", $emp['day_type']);
//                 $worksheet->setCellValue("E{$startRow}", $emp['day_time_desc']);
//                 $worksheet->setCellValue("J{$startRow}", $emp['status']);
//                 $worksheet->setCellValue("K{$startRow}", $emp['late_in_minutes']);
//                 $worksheet->setCellValue("L{$startRow}", $emp['late_out_minutes']);
//                 $worksheet->setCellValue("M{$startRow}", $emp['duration']);

//                 if ($startRow < $endRow) {
//                     foreach (['A','B','C','D','E','J','K','L','M'] as $c) {
//                         $worksheet->mergeCells("{$c}{$startRow}:{$c}{$endRow}");
//                     }
//                 }
//             }

//             $rowcount++;
//         }

//         $worksheet->getStyle("A4:M".($rowcount-1))
//             ->applyFromArray([
//                 'borders'=>['allborders'=>['style'=>PHPExcel_Style_Border::BORDER_THIN]]
//             ]);
//     }

//     $worksheet->setShowGridlines(false);
//     $worksheet->setTitle('Attendance');

//     if (ob_get_length()) ob_end_clean();

//     $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//     $objWriter->save($file_name);

//     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//     header("Content-Disposition: attachment; filename=\"$file_name\"");
//     readfile($file_name);
//     unlink($file_name);
//     break;

//                 default:
//                     $this->set('mode', '');
//                     $this->render('dailyattendanceextr');
//                     break;
//             }
//         }

   }
