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
class EditedReportsController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EditedReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EditPunchesHist', 'Attendance', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'DbConfig', 'MobileUserauditor', 'CompanyContactInfo', 'ReportAudit'); //santhu
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
    public function hrreports()
    {
        $arr_reporttypes = array(
            //'employee' => 'Employee Information',
            'EditPunches' =>  'Edited Attendance ',
            'EditPunch' =>  'Detailed Edit Attendance '
            /*'leave' => 'Leaves Report',
            'attendance' => 'Attendance Summary',*/
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */
    public function changereporttype($type = '')
    {
        $this->autoRender = FALSE;
        // debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                    // case 'employee':
                    //                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                    //                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                    //                 break;
                case 'EditPunches':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'EditPunch':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $query = $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    // $lastQuery = $query->sql();
                    // debug($lastQuery); exit;
                    break;

                    // case 'AttendanceRep':
                    //                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                    //                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                    //                 break;
                    // case 'DetailedAttendance':
                    //                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                    //                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                    //                 break;
                    // //santhu
                    // case 'TimeAttendance':
                    //                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                    //                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                    //                 break;
                    //  case 'MobilelocationRep':
                    //                 $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                    //                 $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                    //                 break;
                default:
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
    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '')
    {
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
    public function loadcriteriaitems($index, $str_criteria = '')
    {
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
            // Edited by Akshay on 28-1-2025
            elseif ($model == 'Units') {
                $conditions = array();
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions = array("Units.status" => 1, "branch_code" => $is_ho);
                    }else{
                        $conditions = array("Units.status" => 1);
                    }
                }
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
                    $fields = 'emp_pkey,status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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


                    $arr_order = array(" CONCAT(`EmployeeDetails`.`first_name`, ' ', IFNULL(`EmployeeDetails`.`last_name`, '')) ASC"); //Edited by Akshay on 8-3-2024
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions[] = array("status in(1,2)");
                    } else {

                        $conditions[] = array("status" => 1);
                    }
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds'); // Edited by Akshay on 29-1-2025
                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.By ***ARUL P DAS on 25/2/2020
                    $user_group = $this->Session->read('user_group');
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                    }
                    // Edited by Akshay on 27-1-2025
                    elseif ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions[] = array("EmployeeDetails.branch_code" => $is_ho);
                        }
                    }
                    // End
                    //employee branch wise sorting ends here

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
        }
    }
    public function reportAudit($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'EditPunches':
                $dataForHistory['report_type'] = "Edited Attendance Report";
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
                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'EmployeeDetails':
                    $criteria_name_array[] = 'belonging to an Employee';
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

        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        //debug($mode);
        switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'EditPunches':
                $this->generatesummaryreport($mode);
                break;
            case 'EditPunch':
                $this->generateeditedreport($mode);
                break;
            case 'AttendanceRep':
                $this->generateattendancereport($mode);
                break;
            case 'DetailedAttendance':
                $this->generateDetailedreport($mode);
                break;
            case 'TimeAttendance':
                $this->generatetimeattendancereport($type, $mode);
                break;
            case 'MobilelocationRep':
                $this->generatemobilelocationreport($type, $mode);
                break;
            default:
                return false;
                break;
        }
        $this->reportAudit($type, $mode);
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

    private function generateemployeereport($mode = '')
    {
        $arr_form_data = $_REQUEST;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $arr_reportfields = array();
        $fields = '';
        if (isset($arr_form_data['hidden-reportfields']) && $arr_form_data['hidden-reportfields'] != '') {
            $fields = $arr_form_data['hidden-reportfields'];
            $search = array('EmployeeDetails.', 'EmployeeProfessionalDetails.', 'Departments.', 'Grades.', 'Verticals.', 'Units.');
            $replace = array('', '', '', '', '', '');
            $str_reportfieldheadings = str_replace($search, $replace, $fields);
            $arr_reportfieldheadings = explode(',', $str_reportfieldheadings);
        }
        //$fields = 'EmployeeDetails.*,EmployeeProfessionalDetails.*,Departments.*,Grades.*,Verticals.*,Units.*';

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
            array(
                'table' => 'grade',
                'alias' => 'Grades',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_grade = Grades.grade_code')
            ),
            array(
                'table' => 'verticals',
                'alias' => 'Verticals',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_vertical = Verticals.vert_code')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
        );
        $conditions  =   array('EmployeeDetails.status' => 1);

        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
            $conditions[] = 'EmployeeProfessionalDetails.joining_date BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
        }
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$str_employee_reportcriteria_field = $this->arr_employee_reportcriteria_fields[$str_criteria_item];
            //$this->arr_employee_reportcriteria_fields[$str_criteria_item];

            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("fields" => "reportcriteria_field", "conditions" => array("status" => 1, 'reportcriteria' => $str_criteria_item))));
            if (isset($arr_reportcriterias[0]['reportcriteria_field'])) {
                if ($str_criteria_item != 'EmployeeProfessionalDetails') {
                    $conditions[] = $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                }
            }
        }
        $arr_emp_details =  $this->EmployeeDetails->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions
        ));

        /*$arr_employee_personal = Set::extract('/EmployeeDetails/.',$arr_emp_details);
        $arr_employee_professional = Set::extract('/EmployeeProfessionalDetails/.',$arr_emp_details);
        $arr_employee_departments = Set::extract('/Departments/.',$arr_emp_details);
        $arr_employee_grades = Set::extract('/Grades/.',$arr_emp_details);
        $arr_employee_verticals = Set::extract('/Verticals/.',$arr_emp_details);
        $arr_employee_units = Set::extract('/Units/.',$arr_emp_details);
        $arr_employee_report_details = array_merge($arr_employee_personal,$arr_employee_professional,$arr_employee_departments,$arr_employee_grades,$arr_employee_verticals,$arr_employee_units);*/

        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields =   new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
            $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
            $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
            $arr_empinformation_fields->getFieldHeadings('Departments'),
            $arr_empinformation_fields->getFieldHeadings('Grades'),
            $arr_empinformation_fields->getFieldHeadings('Verticals'),
            $arr_empinformation_fields->getFieldHeadings('Units')
        );
        $arr_emp_field_names = array(
            'EmployeeDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeDetails')),
            'EmployeeProfessionalDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails')),
            'Departments' => array_keys($arr_empinformation_fields->getFieldNames('Departments')),
            'Grades' => array_keys($arr_empinformation_fields->getFieldNames('Grades')),
            'Verticals' => array_keys($arr_empinformation_fields->getFieldNames('Verticals')),
            'Units' => array_keys($arr_empinformation_fields->getFieldNames('Units'))
        );

        $this->set('arr_emp_field_headings', $arr_emp_field_headings);
        $this->set('arr_report_field_headings', $arr_reportfieldheadings);
        $this->set('arr_emp_field_names', $arr_emp_field_names);
        $this->set('arr_employee_report_details', $arr_emp_details);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportemployeeinformation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeInformation.pdf', 'D');
                //$this->render('reportemployeeinformation');
                break;
            case 'excel':
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code) ? $str_company_code . "_EmployeeInformation.xlsx" : "EmployeeInformation_" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Information Report");
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $sheet  =   array($arr_emp_field_headings);

                $columnindex = 0;
                foreach ($sheet as $row => $columns) {
                    foreach ($columns as $column => $data) {
                        if (in_array($column, $arr_reportfieldheadings)) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . "2", $data);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $columnindex++;
                        }
                    }
                }

                $rowcount = 3;
                foreach ($arr_emp_details as $value) {
                    $columnindex = 0;
                    foreach ($arr_emp_field_names as $key => $val) {
                        foreach ($val as $val1) {
                            if (isset($value[$key][$val1])) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $value[$key][$val1]);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $columnindex++;
                            }
                        }
                    }
                    $rowcount++;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Employee Information');

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
                $this->render('reportemployeeinformation');
                break;
        }
    }



    private function generateattendancereport($mode)
    {
        $arr_form_data = $_REQUEST;
        // debug($mode);
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');

        //By santhosh on 27 Dec 2015
        //$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',  strtotime($month));

        //On 20 Feb 2016
        //$att_enddate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t',  strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);
        $d = $arr_form_data['reportfrom'] . '-01';
        //  debug($d);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;
        }

        $arr_leavepolicydetails_for_template = array();
        //  debug($arr_leavepolicygroupids);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                //            $str_conditions = ' WHERE Attendance.branch_code="'.$leavepolicygroupid.'" and intime between "'.$fd.'" and "'.$Td.'";';
                //            $arr_leavepolicy_details = $this->AttendanceRegister->query(''
                //                    . 'SELECT '
                //                    . '*'
                //                    . 'FROM '
                //                    . '`client_db1`.`Attandance` AS `Attendance` ' 
                //                     .$str_conditions);
                //                     
                //                     
                //            
                // debug($leavepolicygroupid);
                //   $from="2015-10-06";

                $month = $arr_form_data['reportfrom'];
                $rand = $leavepolicygroupid . strtotime('now');
                // debug($rand);
                $this->AttendanceRegisterReport->useDbConfig = $this->Session->read('ds');
                $outputParameter = array();
                $outputParameter[] = $this->Session->read('company_code');
                $outputParameter[] = $leavepolicygroupid;
                $outputParameter[] = $rand;
                $outputParameter[] = $d;
                $out = $this->AttendanceRegisterReport->insertUpdateAttendanceRegisterForReportProc($outputParameter);
                //debug($out);
                $arr_attendance_register_entries = $this->AttendanceRegisterReport->query(" SELECT AttendanceRegisterReport.company_code,"
                    . "AttendanceRegisterReport.branch_code,info.*,registerid,emp_fkey,month_year,emp_company_id,emp_name,FIELD1,FIELD2,FIELD3,FIELD4,FIELD5,FIELD6,FIELD7,FIELD8,FIELD9,FIELD10,FIELD11,FIELD12,FIELD13,FIELD14,FIELD15,FIELD16,FIELD17,FIELD18,FIELD19,FIELD20,FIELD21,FIELD22,FIELD23,FIELD24,FIELD25,FIELD26,FIELD27,FIELD28,FIELD29,FIELD30,FIELD31,FIELD32,isdelete,record_status,userid,id,branch_name,address,city,state,pincode,status,deleted "
                    . "FROM attendance_register_rep AS AttendanceRegisterReport "
                    . "left join branches as branchs on (branchs.branch_code = AttendanceRegisterReport.branch_code) "
                    . "left join employee_info as info on (info.emp_pkey = emp_fkey) "
                    . "where userid = '$rand'  and month_year='$month' "

                    . "union all SELECT AttendanceRegister.company_code,AttendanceRegister.branch_code,info.*,registerid,emp_fkey,month_year,emp_company_id,emp_name,FIELD1,FIELD2,FIELD3,FIELD4,FIELD5,FIELD6,FIELD7,FIELD8,FIELD9,FIELD10,FIELD11,FIELD12,FIELD13,FIELD14,FIELD15,FIELD16,FIELD17,FIELD18,FIELD19,FIELD20,FIELD21,FIELD22,FIELD23,FIELD24,FIELD25,FIELD26,FIELD27,FIELD28,FIELD29,FIELD30,FIELD31,FIELD32,isdelete,record_status,userid,id,branch_name,address,city,state,pincode,status,deleted "
                    . "FROM attendance_register AS AttendanceRegister"
                    . " left join branches as branchs on (branchs.branch_code = AttendanceRegister.branch_code)"
                    . "left join employee_info as info on (info.emp_pkey = emp_fkey) "
                    . " where isdelete='N' and userid = '$rand'  and month_year='$month' ");
                // debug($arr_attendance_register_entries);


                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_attendance_register_entries,
                    // 'employees'=>$arr_leavepolicy_employees
                );
            }

            foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                //  debug($value);
                foreach ($value['summary'] as $ky => $vaal) {
                    //     debug($vaal['AttendanceRegister']);

                    //   $resp_register["rows"][$key] = $value["AttendanceRegister"];
                    // debug($vaal["AttendanceRegister"]);
                    $int_days_present = count(array_keys($vaal["0"], "P"));
                    $int_days_leave = count(array_keys($vaal["0"], "L"));
                    $int_days_holidays = count(array_keys($vaal["0"], "HO"));

                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["0"]['days_present'] = $int_days_present;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["0"]['days_leave'] = $int_days_leave;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["0"]['days_holidays'] = $int_days_holidays;
                }
            }
            // debug($resp_register);
            // debug($arr_leavepolicydetails_for_template);  
            // debug($arr_leavepolicydetails_for_template);die();
            $this->set('month', $month);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            //Set informations needed for report

        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }

        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportsattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Attendance.pdf', 'D');
                break;
            case 'excel':
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code) ? $str_company_code . "_Attendance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Attendance Employees Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('A2:F2');
                $rowcount = 2;
                $i = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i += 1;
                    $branch = $value['summary']['0']['0']['branch_name'];
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Attedance Reports of ' . $branch . ' For the month ' . $month);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                    $rowcount = 3;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Designation');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Date Of join');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Departments');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Branch');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    for ($i = 0; $i <= 8; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }

                    $columnindex = 4;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnindex, ($rowcount))->getFont()->setBold(true);
                        $columnindex++;
                    }
                    $rowcount = 4;

                    $arr_data  = $value['summary'];                        // debug($arr_data);die();
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                    if (count($arr_data) >= 0) {
                        foreach ($arr_data as $key => $val) {
                            $columnindex = 0;
                            $name = $val[0]['emp_name'];
                            //$name=$name.$key;
                            $present = $val[0]['days_present'];
                            $leave = $val[0]['days_leave'];
                            $holidays = $val[0]['days_holidays'];
                            $id = $val[0]['employee_id'];
                            $des = $val[0]['designation'];
                            $join = $val[0]['joining_date'];
                            $department = $val[0]['department'];
                            $branch = $val[0]['branch'];
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $des);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $join);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $department);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $present);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $leave);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $holidays);

                            $columnindex = 4;
                            foreach ($arr_dates as $key => $date) {
                                $newIndex = 'FIELD' . ($key + 1);
                                $dta = $val[0][$newIndex];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }

                            $rowcount++;
                        }
                    }
                    $rowcount1 = $rowcount + 1;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Attendance');
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
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            default:
                $this->set('mode', '');
                $this->render('reportsattendance');
                break;
        }
    }


    private function generatesummaryreport($mode)
    {
        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $this->EditPunchesHist->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $date_time = date('d-M-Y');
        $this->set('date_time', $date_time);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $month = $arr_form_data['reportfrom'];
            $month1 =  $month . '-01';
            // debug($month); exit();   
            $att_startdate = $this->EditPunchesHist->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
            $att_enddate = $this->EditPunchesHist->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
            $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
            $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
            $from = $att_startdate1;
            $to = date('Y-m-d H:m:s', strtotime($att_enddate1 . ' ' . '23:00:00'));
        }
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
            $needBranchWiseReport = false;
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            $this->set('needBranchWiseReport', $needBranchWiseReport);
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }



            // if ($str_criteria_item == '') {
            //     echo "<h1>No Criteria Selected </h1>";
            //     die();
            // }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h2>No Criteria Selected </h2>";
                die();
            }
        }

        $arr_leavepolicydetails_for_template = array();


        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {

            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_leavepolicy_details = array();
                $conditions = array();
                $fields = 'EditPunchesHist.creation_date,EditPunchesHist.LOGDATE,EmployeeDetails.status,employee_info.EmpName,EditPunchesHist.emp_id,
                user_credentials.user_id,employee_info.branch,employee_info.department,employee_info.designation,employee_info.joining_date,device_attandance.C3,                  
                EditPunchesHist.C1,EditPunchesHist.C3,termination.last_approved_working_date ,EditPunchesHist.created_by ,EditPunchesHist.creation_date,           
                  employee_regularaization.updated_date ,EditPunchesHist.status,Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name';
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunchesHist.branch_code = Branch.branch_code',
                            'Branch.status=1'
                        )
                    ),
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunchesHist.emp_id = EmployeeDetails.emp_id'
                        )
                    ),

                    array(
                        'table' => 'employee_info',
                        'alias' => 'employee_info',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'employee_info.emp_pkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'user_credentials',
                        'alias' => 'user_credentials',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'user_credentials.emp_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'employee_regularaization',
                        'alias' => 'employee_regularaization',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'employee_regularaization.empid = EmployeeDetails.emp_id'
                        )
                    ),

                    array(
                        'table' => 'device_attandance',
                        'alias' => 'device_attandance',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunchesHist.emp_id = device_attandance.emp_id',
                            'device_attandance.C3' => 'Uploaded attandance'
                            // Add other conditions as needed
                        )
                    ),

                    array(
                        'table' => 'termination',
                        'alias' => 'termination',
                        'type' => 'LEFT',
                        'foreignKey' => false,

                        'conditions' => array('EmployeeDetails.emp_pkey  = termination.emp_fkey', 'termination.status = 1')

                    )
                );


                if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                    // debug($arr_form_data);     
                    $conditions[] = "EmployeeDetails.status in('1','2')";
                    // debug($conditions);
                } else {
                    $conditions[] = "EmployeeDetails.status ='1' ";
                }

                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and EditPunchesHist.DEVICELOGID is NULL and EditPunchesHist.LOGDATE between "' . $from . '" and "' . $to . '"';
                } else {
                    $conditions[] = 'EmployeeDetails.branch_code="' . $leavepolicygroupid . '" and EditPunchesHist.DEVICELOGID is NULL and EditPunchesHist.LOGDATE between "' . $from . '" and "' . $to . '"';
                }

                //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $conditions[] = " EmployeeDetails.branch_code ='" . $cur_emp_branch . "'";
                }
                //employee branch wise sorting ends here

                $group = array("EditPunchesHist.device_attandance_hist_pkey");
                // $arr_leavepolicy_details = $this->EditPunchesHist->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'group' => $group));
                $arr_leavepolicy_details = $this->EditPunchesHist->find("all", array(
                    'fields' => $fields,
                    'joins' => $joins,
                    'conditions' => $conditions,
                    'group' => $group,
                    'order' => array('employee_info.EmpName' => 'ASC', 'EditPunchesHist.LOGDATE' => 'ASC')
                ));

                //  debug($conditions);
                if (!empty($arr_leavepolicy_details['0'])) {
                    $arr_leavepolicydetails_for_template[] = array(
                        'summary' => $arr_leavepolicy_details
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
                //debug($leavepolicygroupid);

            }
            // debug($arr_leavepolicydetails_for_template);



            //              foreach ($arr_leavepolicydetails_for_template as $key => $value) {
            //                  foreach($value['summary'] as $ky => $vaal){
            //            $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
            //            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
            //            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
            //
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
            //            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
            //            }
            //        }
            // debug($resp_register);
            // debug($arr_leavepolicydetails_for_template);  

            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);

            //Set informations needed for report


            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            //echo date('d-m-Y H:i');
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);
            $time = strtotime($f);
            $month = date("m", $time);
            $mname = date('F', mktime(0, 0, 0, $month, 10));
            $month1 =  $month . '-01';
            $year = date("Y", $time);

            $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $this->set('from', $from);
            $this->set('mname', $mname);
            $this->set('year', $year);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->set('month', $month);
            $this->set('arr_leavepolicygroupids', $arr_leavepolicygroupids);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('reportsummary');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('reportsummary.pdf', 'D');
                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_Editedattendance - " . $from . ".xlsx" : "BankTransfer" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Edited Attendance- " . $mname . " "  . $year);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:P1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );


                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:P2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    if (count($arr_leavepolicydetails_for_template) == 0) {
                        //  echo "<h3>No Data Available With The Selected Criteria</h3>";

                        //print nodata
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:J3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    } else {

                        $rowcount = 3;
                        $columncount = 0;


                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Attendance Time');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Direction');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Created Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Attendance Edited By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Attendance Edited Date and Time');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);

                        // $columnindex = $columncount + 9;

                        $i = 1;
                        $rowcount += 1;
                        foreach ($arr_leavepolicydetails_for_template as $value) {
                            //debug($value);exit();
                            $arr_data = $value['summary'];

                            if (count($arr_data) >= 0) {

                                foreach ($arr_data as  $val) {
                                    //debug($val);exit();
                                    $c_date = $val['EditPunchesHist']['creation_date'];
                                    //$c_date = date('Y-m-d', strtotime($date));
                                    $l_date = $val['EditPunchesHist']['LOGDATE'];
                                    $date = date('Y-m-d', strtotime($l_date));
                                    $columnindex = 0;
                                    $empstatus = isset($val['EmployeeDetails']['status']) && $val['EmployeeDetails']['status'] == "2" ? '(Resigned)' : '';
                                    $name = $val['employee_info']['EmpName'] . $empstatus;
                                    //$name=$name.$key;
                                    // $i = $i + 1;
                                    $id = $val['EditPunchesHist']['emp_id'];
                                    $userid = isset($val['user_credentials']['user_id']) ? $val['user_credentials']['user_id'] : '';
                                    //debug($userid);exit();
                                    $branch = $val['employee_info']['branch'];
                                    $department = $val['employee_info']['department'];
                                    $designation = $val['employee_info']['designation'];
                                    $join = $val['employee_info']['joining_date'];
                                    $direction = $val['EditPunchesHist']['C1'];
                                    // $remark = $val['EditPunchesHist']['C3'];
                                    $remark = isset($val['EditPunchesHist']['C3']) ? $val['EditPunchesHist']['C3'] : '';
                                    $location = $val['device_attandance']['C3'];
                                    $tdate = $val['termination']['last_approved_working_date'];
                                    $by = $val['EditPunchesHist']['created_by'];
                                    $creationDate = $val['EditPunchesHist']['creation_date'];
                                    $updatedTime = $val['employee_regularaization']['updated_date'];

                                    if ($creationDate == $updatedTime) {
                                        $status = 'Regularized';
                                    } else {
                                        $status = $val['EditPunchesHist']['status'];
                                    }

                                    switch ($status) {
                                        case "Y":
                                            $status = 'Active';
                                            break;
                                        case "N":
                                            $status = 'Inactive';
                                            break;
                                    }
                                    //debug($status);exit();
                                    //$this->set('status', $status);
                                    //   $status = $val['EditPunchesHist']['status'];


                                    // switch ($status) {
                                    //     case "N":
                                    //         $status = 'Inactive';
                                    //         break;
                                    //     case "Y":
                                    //         $status = 'Active';
                                    //         break;
                                    //     default:
                                    //         $statusv = 'Invalid data';
                                    // }
                                    // $cdate = $val['EditPunchesHist']['creation_date'];

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i++);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $userid);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $designation);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $tdate);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $remark);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $l_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $direction);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12) . $rowcount, $date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13) . $rowcount, $by);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14) . $rowcount, $c_date);

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 15) . $rowcount, $status);

                                    $columnindex = $columnindex + 13;


                                    $rowcount++;
                                }
                            }
                        }
                        // $rowcount1 = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('B4:O4000')
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );
                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:P' . $row)->applyFromArray($BStyle);
                    }

                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);

                    // }

                    $objPHPExcel->getActiveSheet()->setTitle('Edited Attendance');
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
                    $this->render('reportsummary');
                    break;
            }
            // } else {
            //   echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }


    private function generateeditedreport($mode)
    {
        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $this->EditPunchesHist->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $date_time = date('d-M-Y');
        $this->set('date_time', $date_time);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $month = $arr_form_data['reportfrom'];
            $month1 =  $month . '-01';
            // debug($month); exit();   
            $att_startdate = $this->EditPunchesHist->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
            $att_enddate = $this->EditPunchesHist->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
            $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
            $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
            $from = $att_startdate1;
            $to = date('Y-m-d H:m:s', strtotime($att_enddate1 . ' ' . '23:00:00'));
        }
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
            $needBranchWiseReport = false;
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            $this->set('needBranchWiseReport', $needBranchWiseReport);
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }



            // if ($str_criteria_item == '') {
            //     echo "<h1>No Criteria Selected </h1>";
            //     die();
            // }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h2>No Criteria Selected </h2>";
                die();
            }
        }

        $arr_leavepolicydetails_for_template = array();


        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {

            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_leavepolicy_details = array();
                $conditions = array();
                $fields = 'EditPunchesHist.creation_date,EditPunchesHist.LOGDATE,EmployeeDetails.status,employee_info.EmpName,employee_info.employee_id, EditPunchesHist.emp_id,
                user_credentials.user_id,employee_info.branch,employee_info.department,employee_info.designation,employee_info.joining_date,device_attandance.C3,                  
                EditPunchesHist.C1, EditPunchesHist.C2, EditPunchesHist.C3,termination.last_approved_working_date ,EditPunchesHist.created_by ,EditPunchesHist.creation_date,           
                  employee_regularaization.updated_date ,EditPunchesHist.status,Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name';
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunchesHist.branch_code = Branch.branch_code',
                            'Branch.status=1'
                        )
                    ),
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunchesHist.emp_id = EmployeeDetails.emp_id'
                        )
                    ),

                    array(
                        'table' => 'employee_info',
                        'alias' => 'employee_info',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'employee_info.emp_pkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'user_credentials',
                        'alias' => 'user_credentials',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'user_credentials.emp_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'employee_regularaization',
                        'alias' => 'employee_regularaization',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'employee_regularaization.empid = EmployeeDetails.emp_id'
                        )
                    ),

                    array(
                        'table' => 'device_attandance',
                        'alias' => 'device_attandance',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunchesHist.emp_id = device_attandance.emp_id',
                            'device_attandance.C3' => 'Uploaded attandance'
                            // Add other conditions as needed
                        )
                    ),

                    array(
                        'table' => 'termination',
                        'alias' => 'termination',
                        'type' => 'LEFT',
                        'foreignKey' => false,

                        'conditions' => array('EmployeeDetails.emp_pkey  = termination.emp_fkey', 'termination.status = 1')

                    )
                );


                if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                    // debug($arr_form_data);     
                    $conditions[] = "EmployeeDetails.status in('1','2')";
                    // debug($conditions);
                } else {
                    $conditions[] = "EmployeeDetails.status ='1' ";
                }

                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and EditPunchesHist.DEVICELOGID is NULL and EditPunchesHist.LOGDATE between "' . $from . '" and "' . $to . '"';
                } else {
                    $conditions[] = 'EmployeeDetails.branch_code="' . $leavepolicygroupid . '" and EditPunchesHist.DEVICELOGID is NULL and EditPunchesHist.LOGDATE between "' . $from . '" and "' . $to . '"';
                }

                //Edited by Akshay on 10-2-2024
               // $conditions[] =  "EditPunchesHist.action = 'insert' ";

                //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $conditions[] = " EmployeeDetails.branch_code ='" . $cur_emp_branch . "'";
                }
                //employee branch wise sorting ends here

                $group = array("EditPunchesHist.device_attandance_hist_pkey");

                // $arr_leavepolicy_details = $this->EditPunchesHist->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'group' => $group));
                $arr_leavepolicy_details = $this->EditPunchesHist->find("all", array(
                    'fields' => $fields,
                    'joins' => $joins,
                    'conditions' => $conditions,
                    'group' => $group,
                    'order' => array('employee_info.EmpName' => 'ASC', 'EditPunchesHist.LOGDATE' => 'ASC')
                ));

                //  debug($conditions);
                if (!empty($arr_leavepolicy_details['0'])) {
                    $arr_leavepolicydetails_for_template[] = array(
                        'summary' => $arr_leavepolicy_details
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
                //debug($leavepolicygroupid);

            }


            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);

            //Set informations needed for report


            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $user_id = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            //echo date('d-m-Y H:i');
            $date_time = date('d-m-Y H:i');
            $this->set('user_id', $user_id);
            $this->set('date_time', $date_time);
            $time = strtotime($f);
            $month = date("m", $time);
            $mname = date('F', mktime(0, 0, 0, $month, 10));
            $month1 =  $month . '-01';
            $year = date("Y", $time);

            $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $this->set('from', $from);
            $this->set('mname', $mname);
            $this->set('year', $year);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->set('month', $month);
            $this->set('arr_leavepolicygroupids', $arr_leavepolicygroupids);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('editedsummary');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('editedsummary.pdf', 'D');
                    // $this->render('editedsummary');                
                    break;
                case 'excel':

                    function num2alpha($n) {
                        $r = '';
                        for ($i = 1; $n >= 0 && $i < 10; $i++) {
                        $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
                        $n -= pow(26, $i);
                        }
                        return $r;
                    }

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_DetailedEditedattendance - " . $from . ".xlsx" : "BankTransfer" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Detailed Edit Attendance - " . $mname . " "  . $year);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:J1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );


                    $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()
                    ->getStyle('A2')
                    ->getFont()
                    ->getColor()
                    ->setRGB ('FF0000'); 
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                    $worksheet->mergeCells('A2:J2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }

                    if (count($arr_leavepolicydetails_for_template) == 0) {
                        //  echo "<h3>No Data Available With The Selected Criteria</h3>";

                        //print nodata
                        $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria ");
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                        $worksheet->mergeCells('A3:J3');
                        $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                        );
                        $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    } else {

                        $rowcount = 3;
                        $columncount = 0;


                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Company ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);

                        //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date ');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(true); 

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6 - 1), $rowcount, 'Department ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6 - 1, $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7 - 1), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7 - 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8 - 1), $rowcount, 'Attendance Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8 - 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9 - 1), $rowcount, 'Primary Attendance');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9 - 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10 - 1), $rowcount, 'Primary Direction');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10 - 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11 - 1), $rowcount, 'Edited By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11 - 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12 - 1), $rowcount, 'Edited Date & Time');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12 - 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13 - 1), $rowcount, 'Edited Form');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13 - 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14 - 1), $rowcount, 'Edit Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14 - 1), $rowcount)->getFont()->setBold(true);

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15 - 1), $rowcount, 'Remarks');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15 - 1), $rowcount)->getFont()->setBold(true);
                        // $columnindex = $columncount + 9;
                        // for( $column = 0; $column < ($columncount + 15); $column++){
                        //     $objPHPExcel->getActiveSheet()
                        //     ->getStyle(num2alpha($column). ($rowcount ))
                        //     ->getFill()
                        //     ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        //     ->getStartColor()
                        //     ->setRGB('C8C8C8');
                        // }


                        $i = 1;
                        $rowcount += 1;
                        foreach ($arr_leavepolicydetails_for_template as $value) {

                            $arr_data = $value['summary'];

                            if (count($arr_data) >= 0) {

                                foreach ($arr_data as  $val) {

                                    $c_date = isset($val['EditPunchesHist']['creation_date'])? $val['EditPunchesHist']['creation_date']:'';
                                    $l_date = isset($val['EditPunchesHist']['LOGDATE'])? $val['EditPunchesHist']['LOGDATE']:'';
                                    $date = date('Y-m-d', strtotime($l_date));
                                    $columnindex = 0;
                                    $empstatus = isset($val['EmployeeDetails']['status']) && $val['EmployeeDetails']['status'] == "2" ? '(Resigned)' : '';
                                    $name = isset($val['employee_info']['EmpName'])? $val['employee_info']['EmpName'] . $empstatus:'';

                                    $id = isset($val['EditPunchesHist']['emp_id'])? $val['EditPunchesHist']['emp_id']:'';
                                    $userid = isset($val['employee_info']['employee_id']) ? $val['employee_info']['employee_id'] : '';
                                    $branch = isset($val['employee_info']['branch'])? $val['employee_info']['branch']:'';
                                    $department = isset($val['employee_info']['department'])? $val['employee_info']['department']:'';
                                    $designation = isset($val['employee_info']['designation'])? $val['employee_info']['designation']:'';
                                    $join = isset($val['employee_info']['joining_date'])? $val['employee_info']['joining_date']:'';
                                    $direction = isset($val['EditPunchesHist']['C1'])? $val['EditPunchesHist']['C1']:'';
                                    $regularisation = isset($val['EditPunchesHist']['C2'])? $val['EditPunchesHist']['C2']:'';
                                    if($regularisation == 'REG'){
                                        $edit_form = 'Approve/Reject Regularisation';
                                    }else{
                                        $edit_form = 'Edit Attendance';
                                    }
                                    $remark = isset($val['EditPunchesHist']['C3']) ? $val['EditPunchesHist']['C3'] : '';

                                    $tdate = isset($val['termination']['last_approved_working_date'])? $val['termination']['last_approved_working_date']:'';
                                    $by = isset($val['EditPunchesHist']['created_by'])? $val['EditPunchesHist']['created_by']:'';
                                    $creationDate = isset($val['EditPunchesHist']['creation_date'])? $val['EditPunchesHist']['creation_date']:'';
                                    $updatedTime = isset($val['employee_regularaization']['updated_date'])? $val['employee_regularaization']['updated_date']:'';

                                    if ($creationDate === $updatedTime) {
                                        $status = 'Regularized';
                                    } else {
                                        $status = $val['EditPunchesHist']['status'];
                                    }

                                    switch ($status) {
                                        case "N":
                                            $status = 'Inactive';
                                            break;
                                        case "Y":
                                            $status = 'Active';
                                            break;
                                    }


                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $i++);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $userid);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex  + 5 - 1) . $rowcount, $branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex  + 6 - 1) . $rowcount, $department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex  + 7 - 1) . $rowcount, $designation);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex  + 8 - 1) . $rowcount, $date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex  + 9 - 1) . $rowcount, $l_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex  + 10 - 1) . $rowcount, $direction);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11 - 1) . $rowcount, $by);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 12 - 1) . $rowcount, $c_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 13 - 1) . $rowcount, $edit_form);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 14 - 1) . $rowcount, $status);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 15 - 1) . $rowcount, $remark);

                                    for($colum = 0; $colum < 15; $colum++){
                                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($colum);
                                        $objPHPExcel->getActiveSheet()->getStyle($columnLetter . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    }

                                    $columnindex = $columnindex + 13;


                                    $rowcount++;
                                }
                            }
                        }
                        // $rowcount1 = $rowcount + 1;
                        // $objPHPExcel->getActiveSheet()
                        //     ->getStyle('B4:O4000')
                        //     ->getAlignment()
                        //     ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );
                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A3:O' . $row)->applyFromArray($BStyle);
                       $objPHPExcel->getActiveSheet()->freezePane('E4');
                    }

                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);

                    // }

                    $objPHPExcel->getActiveSheet()->setTitle('Detailed Edit Attendance');
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
                    $this->render('editedsummary');
                    break;
            }
            // } else {
            //   echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }
}
