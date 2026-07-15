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
class ResighnedReportsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout = "default";
    public $name = 'ResighnedReports';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('ResignationRequests','Attendance','CentralControl','UserCredentials','EmployeeDetails','EmployeeProfessionalDetails','Termination','Departments','Grades','Verticals','Units','ReportCriterias','AttendanceRegister','AttendanceRegisterReport','DbConfig','MobileUserauditor','CompanyContactInfo','ReportAudit');//santhu
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
             //'Resighned' =>  'Resigned '
            'Resighned' =>  'Resignation Report'//edited by ***ARUL P DAS
            /*'leave' => 'Leaves Report',
            'attendance' => 'Attendance Summary',*/
        );
        $this->set('arr_reporttypes',$arr_reporttypes);
    }
    
    /*
     * Change Sub Report type
     */
    public function changereporttype($type=''){
        $this -> autoRender = FALSE;
         // debug($type);die();
        if($type != ''){
            $this->set('type',$type);
            switch ($type){
                case 'employee':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'Resighned':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                            
                case 'AttendanceRep':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'DetailedAttendance':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                //santhu
                case 'TimeAttendance':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                 case 'MobilelocationRep':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                default :
                        echo "No criterias found";
                        break;
            }
            $this->render('showreport');
        }else{
            echo "No criterias found";
        }
    }
    
    /*
     * Add criterias
     */
    public function addreportcriteria($type='', $newindex='',$str_currentcriterias=''){
        $this -> autoRender = FALSE;
        if($type != '' && $str_currentcriterias != ''){            
            //$arr_currentcriterias = explode(',', $str_currentcriterias);
            //$arr_remainingcriterias = array_diff(array_flip($this->arr_employee_reportcriterias), $arr_currentcriterias);
            //$this->set('arr_remainingcriterias',array_flip($arr_remainingcriterias));
            $str_currentcriterias = "'".str_replace(",","','",$str_currentcriterias)."'";
            $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1, 'reportcriteria NOT IN('.$str_currentcriterias.')','reporttype'=>$type)))));
                                
            $this->set('newindex',$newindex);
            $this->render('showcriteria');
        }else{
            return '';
        }
    }
    
    /*
     * Load criteria items
     */
    public function loadcriteriaitems($index,$str_criteria=''){
        $this -> autoRender = FALSE;
        if($str_criteria != ''){
            $model = $str_criteria;
            if($this->_modelExists($model)){
                $this->set('index',$index);
                $model = ($model == 'EmployeeDetails')?'Employees':$model;
                $this->set('criteria',$model);
                $this->render('loadcriteriaitems');
            }else{
                return '';
            }
        }else{
            return '';
        }
    }
    
    public function listcriteriaitems($str_criteria = '')
    {
        $this->autoRender = false;
        $model = $str_criteria;

        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            if ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    // Edited by Akshay on 28-1-2025
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
                    } else {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $arr_order = array("Units.branch_name" => "ASC");
                        $conditions = array("Units.status" => 1, "branch_code" => $cur_emp_branch);
                    }
                    // End
                } else {
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1);
                }
            } else {
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
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey'),
                            'order' => array('EmployeeDetails.first_name' => 'ASC')
                        )
                    );
                    $conditions = array();
                    $conditions[] = array('status' => 2);

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2) {
                        // Edited by Akshay on 28-1-2025
                        $user = $this->Session->read('company_code');
                        if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $emp_pkey = $this->Session->read('emp_fkey');
                            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                            if ($is_ho != 1) {
                                $conditions["EmployeeDetails.branch_code"] = $is_ho;
                            }
                        }else{
                            $cur_emp_key = $this->Session->read("emp_fkey");
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                            $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                        }
                        // End
                    }
                    //employee branch wise sorting ends here

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions
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
    
    public function reportAudit($type,$mode){
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Resighned':
                $dataForHistory['report_type'] = "Employee Resignation Report";
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
            }
            $items_array[] = isset($arr_form_data[$criteria])?implode(",", $arr_form_data[$criteria]):'';
            $items_count_array[] = isset($arr_form_data[$criteria])?count($arr_form_data[$criteria]):0;

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
    
    public function generatereport($type='',$mode=''){
        $this->autoRender = false;
//debug($mode);
       switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'Resighned':
                $this->generatesummaryreport($mode);
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
        
        $this->reportAudit($type,$mode);
    }
    
    public function listemployeefields(){
        App::import('Vendor', 'EmployeeInformationFields', array('file'=>'ReportFields'.DS.'EmployeeInformationFields.php'));
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
                $data['id'] = $key.'.'.$key1;
                $data['data'] = array($value1);
                $resp_emp["rows"][] = $data;
            }
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    
    private function _modelExists($modelName){
      $models = App::objects('model');
      return in_array($modelName,$models);
   }
   
   private function generateemployeereport($mode = ''){
        $arr_form_data = $_REQUEST;
        
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $arr_reportfields = array();
        $fields = '';
        if(isset($arr_form_data['hidden-reportfields']) && $arr_form_data['hidden-reportfields']!= ''){
            $fields = $arr_form_data['hidden-reportfields'];
            $search = array('EmployeeDetails.','EmployeeProfessionalDetails.','Departments.','Grades.','Verticals.','Units.');
            $replace = array('','','','','','');
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
                'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
            array(
                'table' => 'grade',
                'alias' => 'Grades',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeProfessionalDetails.emp_grade = Grades.grade_code')
            ),
            array(
                'table' => 'verticals',
                'alias' => 'Verticals',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeProfessionalDetails.emp_vertical = Verticals.vert_code')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
        );
        $conditions  =   array('EmployeeDetails.status'=>1);

        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count']; 
        if((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom']!= '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto']!= '')){
            $conditions[] = 'EmployeeProfessionalDetails.joining_date BETWEEN "'.$arr_form_data['reportfrom'].'" AND "'.$arr_form_data['reportto'].'"';
        }
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];

            //$str_employee_reportcriteria_field = $this->arr_employee_reportcriteria_fields[$str_criteria_item];
            //$this->arr_employee_reportcriteria_fields[$str_criteria_item];

            $arr_reportcriterias = Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("fields"=>"reportcriteria_field", "conditions"=>array("status"=>1,'reportcriteria'=>$str_criteria_item))));
            if(isset($arr_reportcriterias[0]['reportcriteria_field'])){
                if($str_criteria_item != 'EmployeeProfessionalDetails'){
                    $conditions[] = $arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
                }
            }
        }
        $arr_emp_details =  $this -> EmployeeDetails ->find("all",array(
            'fields'=>$fields,
            'joins'=>$joins,
            'conditions'=>$conditions
        ));

        /*$arr_employee_personal = Set::extract('/EmployeeDetails/.',$arr_emp_details);
        $arr_employee_professional = Set::extract('/EmployeeProfessionalDetails/.',$arr_emp_details);
        $arr_employee_departments = Set::extract('/Departments/.',$arr_emp_details);
        $arr_employee_grades = Set::extract('/Grades/.',$arr_emp_details);
        $arr_employee_verticals = Set::extract('/Verticals/.',$arr_emp_details);
        $arr_employee_units = Set::extract('/Units/.',$arr_emp_details);
        $arr_employee_report_details = array_merge($arr_employee_personal,$arr_employee_professional,$arr_employee_departments,$arr_employee_grades,$arr_employee_verticals,$arr_employee_units);*/

        App::import('Vendor', 'EmployeeInformationFields', array('file'=>'ReportFields'.DS.'EmployeeInformationFields.php'));
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
                'EmployeeDetails'=>array_keys($arr_empinformation_fields->getFieldNames('EmployeeDetails')),
                'EmployeeProfessionalDetails'=>array_keys($arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails')),
                'Departments'=>array_keys($arr_empinformation_fields->getFieldNames('Departments')),
                'Grades'=>array_keys($arr_empinformation_fields->getFieldNames('Grades')),
                'Verticals'=>array_keys($arr_empinformation_fields->getFieldNames('Verticals')),
                'Units'=>array_keys($arr_empinformation_fields->getFieldNames('Units'))
        );

        $this->set('arr_emp_field_headings', $arr_emp_field_headings);
        $this->set('arr_report_field_headings', $arr_reportfieldheadings);
        $this->set('arr_emp_field_names', $arr_emp_field_names);
        $this->set('arr_employee_report_details', $arr_emp_details);
$this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);
        switch ($mode){
            case 'pdf' : 
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportemployeeinformation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeInformation.pdf', 'D');
                //$this->render('reportemployeeinformation');
                break;
            case 'excel' :
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_EmployeeInformation.xlsx":"EmployeeInformation_".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
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
                foreach($sheet as $row => $columns) {
                   foreach($columns as $column => $data) { 
                        if(in_array($column,$arr_reportfieldheadings)){
                           $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex)."2", $data);
                           $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                           $columnindex++;
                        }
                    }
                }

                $rowcount = 3;
                foreach($arr_emp_details as $value){
                    $columnindex = 0;
                    foreach($arr_emp_field_names as $key=>$val){
                        foreach($val as $val1){ 
                            if(isset($value[$key][$val1])){
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount, $value[$key][$val1]);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $columnindex++;
                            }
                        }
                    }
                    $rowcount++;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Employee Information');

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
                $this->render('reportemployeeinformation');
                break;
        }
   }
   
  
   
     private function generateattendancereport($mode){
       $arr_form_data = $_REQUEST;
      // debug($mode);
     $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
            $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date('Y-m');
	
        //By santhosh on 27 Dec 2015
	//$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',  strtotime($month));
        
        //On 20 Feb 2016
        //$att_enddate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_startdate = $att_enddate + 1;
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
     $d = $arr_form_data['reportfrom'].'-01';
   //  debug($d);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
           
                //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
               $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';    
            $arr_leavepolicygroupids = $crit;
            
        }
        
        $arr_leavepolicydetails_for_template = array();
      //  debug($arr_leavepolicygroupids);
        if(isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
         {
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid){
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
           
           $month= $arr_form_data['reportfrom'];
            $rand=$leavepolicygroupid.strtotime('now');
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

."union all SELECT AttendanceRegister.company_code,AttendanceRegister.branch_code,info.*,registerid,emp_fkey,month_year,emp_company_id,emp_name,FIELD1,FIELD2,FIELD3,FIELD4,FIELD5,FIELD6,FIELD7,FIELD8,FIELD9,FIELD10,FIELD11,FIELD12,FIELD13,FIELD14,FIELD15,FIELD16,FIELD17,FIELD18,FIELD19,FIELD20,FIELD21,FIELD22,FIELD23,FIELD24,FIELD25,FIELD26,FIELD27,FIELD28,FIELD29,FIELD30,FIELD31,FIELD32,isdelete,record_status,userid,id,branch_name,address,city,state,pincode,status,deleted "
                    . "FROM attendance_register AS AttendanceRegister"
                    . " left join branches as branchs on (branchs.branch_code = AttendanceRegister.branch_code)"
                    . "left join employee_info as info on (info.emp_pkey = emp_fkey) "
                    . " where isdelete='N' and userid = '$rand'  and month_year='$month' ");
        // debug($arr_attendance_register_entries);
            

            $arr_leavepolicydetails_for_template[] = array(
                //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                'summary'=>$arr_attendance_register_entries,
               // 'employees'=>$arr_leavepolicy_employees
            );
        }
        
              foreach ($arr_leavepolicydetails_for_template as $key => $value) {
//  debug($value);
                  foreach($value['summary'] as $ky => $vaal)
                  {
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
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);
        //Set informations needed for report
       
         }
           else {
        echo "<div style='color:red'><h3>No record Found</h3></div>"  ;
        die();
    }
        
        switch ($mode){
            case 'pdf' : 
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportsattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Attendance.pdf', 'D');
                break;
            case 'excel' :
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_Attendance.xlsx":"ShiftPolicy".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
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
                $rowcount=2;
                $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                   $i += 1; 
                   $branch=$value['summary']['0']['0']['branch_name'] ;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Attedance Reports of '.$branch.' For the month '.$month);
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                $rowcount=3;
                
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Designation');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Date Of join');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Departments');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Branch');
                  
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6).$rowcount, 'Present Days');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7).$rowcount, 'Leave Days');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8).$rowcount, 'Holiday Days');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                 for ($i = 0; $i <= 8; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                                           }
                
                $columnindex = 4;
                                 foreach ($arr_dates as $key=> $date){ 
                       
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount, $date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                 $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnindex, ($rowcount))->getFont()->setBold(true);
                                $columnindex++;
                           
                        }
                        $rowcount=4;
                        
                         $arr_data  = $value['summary'];                        // debug($arr_data);die();
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        if(count($arr_data)>=0){
                            foreach($arr_data as $key=>$val){
                                      $columnindex = 0;
                            $name=$val[0]['emp_name'];
                            //$name=$name.$key;
                            $present=$val[0]['days_present'];
                            $leave=$val[0]['days_leave']; 
                            $holidays=$val[0]['days_holidays'];
                            $id=$val[0]['employee_id']; 
                                         $des=$val[0]['designation']; 
                                          $join=$val[0]['joining_date']; 
                                          $department=$val[0]['department']; 
                                          $branch=$val[0]['branch']; 
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$des);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$join);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$department);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$branch);
                           // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).$rowcount,$present);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$leave);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$holidays);
                            
                            $columnindex=4;
                                            foreach($arr_dates as $key=> $date)
                                                
                                            {         $newIndex='FIELD'.($key+1);
                                                $dta= $val[0][$newIndex]; 
                                              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$dta);  
                                              $columnindex++;
                                            }
                            
                          $rowcount++;
                            }
                        }
                        $rowcount1=$rowcount+1;
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
                $objWriter->save(dirname(__FILE__)."/".$file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename='.$file_name);                        

                readfile(dirname(__FILE__)."/".$file_name);
                unlink(dirname(__FILE__)."/".$file_name);
                break;
            default : 
                $this->set('mode','');
                $this->render('reportsattendance'); 
                break;
        }
   }
   
   
   private function generatesummaryreport($mode){
        $arr_form_data = $_REQUEST;
        $this->ResignationRequests->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date('Y-m');
        $end_month = isset($arr_form_data['reportto'])?$arr_form_data['reportto']:date('Y-m');//Added by **ARUL P DAS on 13/12/2019
        $this->set('start_month',$month);
        $this->set('end_month',$end_month);
	$user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $this->set('criteria_name',$arr_form_data['select-criteria1']);
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
        if((isset($arr_form_data['reportfrom']) && $arr_form_data['reportto']!= '')){
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1',  strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t',  strtotime($arr_form_data['reportto']));
        }
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $this->Termination->useDbConfig = $this->Session->read('ds');
        $arr_leavepolicydetails_for_template = array();
        // debug($arr_form_data);
        // debug($arr_leavepolicygroupids);
        //*********************Resignation report Edited By Arul on 19-10-2019*********************
        //Added Employee wise report.
        $criteria=$arr_form_data['hidden-criteria1'];//Units or EmployeeDetails.
        $this->set('criteria',$criteria);
        if(isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)){
            if ($criteria=="Units") {//This is the case of Branch wise
                foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                }
                $conditions = array("Branch.branch_code"=>$arr_leavepolicygroupids);
                $fields = 'Termination.*,Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name,EP.emp_company_id,EI.designation,EI.department';
                //Fields edited by ***ARUL P DAS on 16/12/2019
                $joins = array(
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Termination.emp_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EmployeeDetails.branch_code = Branch.branch_code',
                            'Branch.status=1'
                        )
                    ),
                    array(
                        'table' => 'emp_proff',
                        'alias' => 'EP',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EmployeeDetails.emp_pkey = EP.emp_fkey'
                        )
                    ),
                    array(
                        'table' => 'employee_info',
                        'alias' => 'EI',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EmployeeDetails.emp_pkey = EI.emp_pkey'
                        )
                    )//There two tables joined by ***ARUL P DAS on 16/12/2019
                );
                //$conditions[] = " Termination.last_applied_date between '$from' and '$to' ";//This is bkuped by **ARUL P DAS on 14/1/2020
                $conditions[] = " Termination.last_approved_working_date between '$from' and '$to' ";//Showing last approved working date.
                $conditions[]= " Termination.status=1 ";//This is edited by **ARUL P DAS on 5/12/19
                $conditions[]= " EmployeeDetails.status=2 ";//This is edited by ***ARUL P DAS on 5/12/2019
                $conditions[]= " Branch.status=1 ";
                //$conditions[] = 'Branch.branch_code="' . $leavepolicygroupid . '"';
                $arr_leavepolicy_details = $this->Termination->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));

                 if (count($arr_leavepolicy_details) > 0) {
                    $arr_leavepolicydetails_for_template[] = array(
                        'summary' => $arr_leavepolicy_details,
                            // 'employees'=>$arr_leavepolicy_employees
                    );
                }
                $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
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
                        $view_output = $view->render('reportsummary');
                        // debug($view_output);
                        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                        $html2pdf = new HTML2PDF('L', 'A2', 'en');
                        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->writeHTML($view_output);
                        $html2pdf->Output('reportsummary.pdf', 'D');
                        // $this->render('reportsummary');                
                        break;
                    case 'excel' :
                        
                        $str_company_code   =   $this->Session->read('company_code');
                        $file_name  = isset($str_company_code)?$str_company_code."_attendane.xlsx":"AttendanceA".strtotime().".xlsx";

                        App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                        $objPHPExcel = new PHPExcel();

                        $objPHPExcel->getProperties()->setCreator("Administrator");
                        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                        $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

                        $objPHPExcel->setActiveSheetIndex(0);

                        $worksheet = $objPHPExcel->getActiveSheet();

                        $worksheet->setCellValueByColumnAndRow(0, 1, "Summary Attendance Employees Report");
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
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Id');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6).$rowcount, 'Present Days');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7).$rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8).$rowcount, 'Holiday Days');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $columnindex = $columncount+9;
                        foreach ($arr_dates as $key=> $date){ 
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount, $date);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                            $columnindex++;
                        }
                        $rowcount = 3;  
                        //$rowcount1=3;
                        foreach($arr_leavepolicydetails_for_template as $value){
                            $branch=isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch' ;
                            //echo $branch;die();                     
                            //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');
                                   
                             $arr_data  = $value['summary'];
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                            if(count($arr_data)>=0){
                                foreach($arr_data as $key=>$val){
                                    $columnindex = 0;
                                    $name=$val['AttendanceRegister']['emp_name'];
                                    //$name=$name.$key;
                                    $present=$val['AttendanceRegister']['days_present'];
                                    $leave=$val['AttendanceRegister']['days_leave']; 
                                    $holidays=$val['AttendanceRegister']['days_holidays'];
                                    $id=$val['Info']['employee_id']; 
                                    $des=$val['Info']['designation']; 
                                    $join=$val['Info']['joining_date']; 
                                    $department=$val['Info']['department']; 
                                    $branch=$val['Info']['branch']; 
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$id);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$des);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$join);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).$rowcount,$present);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$leave);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$holidays);
                                
                                    $columnindex=$columnindex+9;
                                    foreach($arr_dates as $key=> $date){
                                        $newIndex='FIELD'.($key+1);
                                        $dta= $val['AttendanceRegister'][$newIndex]; 
                                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$dta);  
                                        $columnindex++;
                                    }
                                    $rowcount++;
                                }
                            }
                            $rowcount1=$rowcount+1;
                        }
                     
                        $objPHPExcel->getActiveSheet()->setTitle('Attendance Policy');
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
                        $this->render('reportsummary');
                        break;
                }
            }else{              //This is the case of Employee Wise. (EmpolyeeDetails)
                
                // foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                // }
                $conditions = array("EmployeeDetails.emp_pkey"=>$arr_leavepolicygroupids);
                $fields = 'Termination.*,Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name,EP.emp_company_id,EI.designation,EI.department';
                //The fields edited by ***ARUL P DAS on 16/12/2019
                $joins = array(
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'Termination.emp_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EmployeeDetails.branch_code = Branch.branch_code',
                            'Branch.status=1'
                        )
                    ),
                    array(
                        'table' => 'emp_proff',
                        'alias' => 'EP',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EmployeeDetails.emp_pkey = EP.emp_fkey'
                        )
                    ),
                    array(
                        'table' => 'employee_info',
                        'alias' => 'EI',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EmployeeDetails.emp_pkey = EI.emp_pkey'
                        )
                    )
                );
                //$conditions[] = " Termination.last_applied_date between '$from' and '$to' ";//This is bkuped by **ARUL P DAS on 14/1/2020
                $conditions[] = " Termination.last_approved_working_date between '$from' and '$to' ";//Showing last approved working date.
                $conditions[]= " Termination.status=1 ";//This is edited by **ARUL P DAS on 5/12/19
                $conditions[]= " EmployeeDetails.status=2 ";//This is edited by ***ARUL P DAS on 5/12/2019
                $conditions[]= " Branch.status=1 ";
                $arr_leavepolicy_details = $this->Termination->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
                $arr_leavepolicydetails_for_template[] = array(
                    'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                );
                
                $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
                // debug($arr_leavepolicydetails_for_template);
                //Set informations needed for report
                $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                $user_name= $this->Session->read('user_name');
                $this->set('user_name',$user_name);
                $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
                $this->set('arr_comp_contact_info',$arr_comp_contact_info);//company contact information
                switch ($mode){//There is no need of checking weather mode is pdf or excel. Because it replaced with another method in the reportsummary.ctp (Using of dom:'Bferip')
                    case 'pdf' : 
                        //echo "entered in";die();
                        // $this->set('mode','pdf');
                        // $view = new View($this, false);
                        // $view_output = $view->render('reportsummary');
                        // debug($view_output);
                        // App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                        // $html2pdf = new HTML2PDF('L', 'A2', 'en');
                        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                        // $html2pdf->pdf->SetDisplayMode('fullpage');
                        // $html2pdf->writeHTML($view_output);
                        // $html2pdf->Output('reportsummary.pdf', 'D');
                        // $this->render('reportsummary');                
                        break;
                    case 'excel' :
                        
                        // $str_company_code   =   $this->Session->read('company_code');
                        // $file_name  = isset($str_company_code)?$str_company_code."_attendane.xlsx":"AttendanceA".strtotime().".xlsx";

                        // App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                        // $objPHPExcel = new PHPExcel();

                        // $objPHPExcel->getProperties()->setCreator("Administrator");
                        // $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                        // $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                        // $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                        // $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

                        // $objPHPExcel->setActiveSheetIndex(0);

                        // $worksheet = $objPHPExcel->getActiveSheet();

                        // $worksheet->setCellValueByColumnAndRow(0, 1, "Summary Attendance Employees Report");
                        //    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                        // $worksheet->mergeCells('A1:F1');
                        // $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        //         array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        // );
                        // for ($col = 'A'; $col !== 'Z'; $col++) {
                        //     $objPHPExcel->getActiveSheet()
                        //             ->getColumnDimension($col)
                        //             ->setAutoSize(true);
                        // }
                        
                        // $rowcount = 2;
                        // $columncount=0;
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Employee Name');
                        // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Id');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Designation');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Department');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6).$rowcount, 'Present Days');
                        // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7).$rowcount, 'Leave Days');
                        // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8).$rowcount, 'Holiday Days');
                        // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        // $columnindex = $columncount+9;
                        // foreach ($arr_dates as $key=> $date){ 
                        //     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount, $date);
                        //     $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        //     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                        //     $columnindex++;
                        // }
                        // $rowcount = 3;  
                        // //$rowcount1=3;
                        // foreach($arr_leavepolicydetails_for_template as $value){
                        //     $branch=isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch' ;
                        //     //echo $branch;die();                     
                        //     //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');
                                   
                        //      $arr_data  = $value['summary'];
                        //     // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        //     if(count($arr_data)>=0){
                        //         foreach($arr_data as $key=>$val){
                        //             $columnindex = 0;
                        //             $name=$val['AttendanceRegister']['emp_name'];
                        //             //$name=$name.$key;
                        //             $present=$val['AttendanceRegister']['days_present'];
                        //             $leave=$val['AttendanceRegister']['days_leave']; 
                        //             $holidays=$val['AttendanceRegister']['days_holidays'];
                        //             $id=$val['Info']['employee_id']; 
                        //             $des=$val['Info']['designation']; 
                        //             $join=$val['Info']['joining_date']; 
                        //             $department=$val['Info']['department']; 
                        //             $branch=$val['Info']['branch']; 
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$id);
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$des);
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$join);
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$department);
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$branch);
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).$rowcount,$present);
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$leave);
                        //             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$holidays);
                                
                        //             $columnindex=$columnindex+9;
                        //             foreach($arr_dates as $key=> $date){
                        //                 $newIndex='FIELD'.($key+1);
                        //                 $dta= $val['AttendanceRegister'][$newIndex]; 
                        //                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$dta);  
                        //                 $columnindex++;
                        //             }
                        //             $rowcount++;
                        //         }
                        //     }
                        //     $rowcount1=$rowcount+1;
                        // }
                     
                        // $objPHPExcel->getActiveSheet()->setTitle('Attendance Policy');
                        // /* header footer */
                        // $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                        // $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                        // $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                        // $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                        // /* header footer */
                        
                        // /*print Set up*/
                        // $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                        // $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                        // $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                        // $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                        // /*print Set up*/
                        // $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                        // $objWriter->save(dirname(__FILE__)."/".$file_name);

                        // // output headers so that the file is downloaded rather than displayed
                        // header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                        // header('Content-Disposition: attachment; filename='.$file_name);                        

                        // readfile(dirname(__FILE__)."/".$file_name);
                        // unlink(dirname(__FILE__)."/".$file_name);
                        break;
                    default : 
                        $this->set('mode','');
                        $this->render('reportsummary');
                        break;
                }
            }
       } else {
            echo "<div><h2>Please Choose Criteria Item First</h2></div>";
            // $this->layout=null;
        }
    }
   
   
    
   private function generateDetailedreport($mode){
        $arr_form_data = $_REQUEST;
    //  debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $fd=$arr_form_data['reportfrom'].' '.'00:00:00';
       // $Td=$arr_form_data['reportto'].' '.'00:00:00';
            if((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom']!= '')){
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1',  strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t',  strtotime($arr_form_data['reportfrom']));
        }
        
        
        
        
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
           
                //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
               // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';    
            $arr_leavepolicygroupids = $crit;
            
        }
        
        $arr_leavepolicydetails_for_template = array();
      //  debug($arr_leavepolicygroupids);
        if(isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid){
//            $str_conditions = ' WHERE Attendance.branch_code="'.$leavepolicygroupid.'" and intime between "'.$fd.'" and "'.$Td.'";';
//            $arr_leavepolicy_details = $this->AttendanceRegister->query(''
//                    . 'SELECT '
//                    . '*'
//                    . 'FROM '
//                    . '`client_db1`.`Attandance` AS `Attendance` ' 
//                     .$str_conditions)
        //  debug($leavepolicygroupid);
                   if($arr_form_data['select-criteria1'] == 'Departments') 
                   {
                      $arr_leavepolicy_details =$this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,"
                              . "`DeviceAttendance`.C2,`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`info`.*,"
                              . "`department`.`dept_name`,`empproff`.emp_dept FROM `device_attandance` AS `DeviceAttendance`"
                              . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`)"
                              . " LEFT JOIN `emp_proff` AS `empproff` ON (`empproff`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`) "
                              . "LEFT JOIN `department` AS `department` ON (`department`.`dept_code` = `empproff`.`emp_dept`)"
                              . "LEFT JOIN `employee_info` AS `Info` ON (`info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                              . " WHERE `LOGDATE` between '$from' and '$to' and empproff.emp_dept='$leavepolicygroupid' "
                              . "ORDER BY  `DeviceAttendance`.`LOGDATE`,`EmployeeDetails`.`emp_id`,c1");
                   }
                     else 
                   {
                      $arr_leavepolicy_details =$this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,`DeviceAttendance`.C2,"
                              . "`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`branches`.branch_name,`info`.*"
                              . " FROM `device_attandance` AS `DeviceAttendance`"
                              . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`)"
                              . " LEFT JOIN `branches` AS `branches` ON (`DeviceAttendance`.`branch_code` = `branches`.`branch_code`) "
                              . "LEFT JOIN `employee_info` AS `Info` ON (`info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                              . "WHERE `LOGDATE` between '$from' and '$to' and DeviceAttendance.branch_code='$leavepolicygroupid'"
                              . " ORDER BY `DeviceAttendance`.`LOGDATE`,`EmployeeDetails`.`emp_id`");
     
                   }

                   
            $arr_leavepolicydetails_for_template[] = array(
                //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                'summary'=>$arr_leavepolicy_details,
               // 'employees'=>$arr_leavepolicy_employees
            );
        }
   //  debug($arr_leavepolicydetails_for_template);
        
        
           $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        }
        else {
              echo "<div style='color:red'><h3>No record Found</h3></div>"  ;
        die();
        }
       // debug($this_month_att);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);
        switch ($mode){
            case 'pdf' : 
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('detailedattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('AttendanceDetailed.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_Detailedattendance.xlsx":"ShiftPolicy".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employees Attendance  Report");
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
                $rowcount = 3;
              $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                   $i += 1;
                   $arr_daata  = $value['summary'];
                     $pp= isset($arr_daata[0]['branches']['branch_name'])?$arr_daata[0]['branches']['branch_name']:''; 
                        if($pp != '')
                        {
                        $branch= isset($arr_daata[0]['branches']['branch_name'])?$arr_daata[0]['branches']['branch_name']:''; 
                        }
                        else
                        {
                        $branch= isset($arr_daata[0]['department']['dept_name'])?$arr_daata[0]['department']['dept_name']:''; 
                        
                        }
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Attedance Details of '.$branch);
                
                     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                     
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
               $rowcount=$rowcount+2;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).($rowcount), 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Designation');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Date Of join');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Departments');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Branch');
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6).($rowcount), 'Date');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7).($rowcount), 'in/out');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8).($rowcount), 'Location');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                 for ($i = 0; $i <= 8; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                                           }
                
                $columnindex = 0;
                          // 
              $arr_data  = $value['summary'];
                           if(count($arr_data)>0){ 
                               $rowcount=$rowcount+1;   
                                    foreach($arr_data as $val){
                                    
                                         
                                             $name=$val['EmployeeDetails']['first_name'].$val['EmployeeDetails']['last_name'];
                                             $date=$val['DeviceAttendance']['LOGDATE']; 
                                             $att1=$val['DeviceAttendance']['C1']; 
                                             $att2= $val['DeviceAttendance']['C3']; 
                                              $id = $val['Info']['employee_id'];
                            $clas = $val['Info']['designation'];
                            $join = $val['Info']['joining_date'];
                            $dept = $val['Info']['department'];
                            $unit = $val['Info']['branch'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $name);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $unit);
                             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+6).($rowcount),$date);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).($rowcount),$att1);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).($rowcount),$att2);
                            $rowcount++;
                                     } 
                                }else{  
                                    $msg='No employees found under this shift';
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).($rowcount),$msg);
                              }
                              $rowcount++;
              }
                $objPHPExcel->getActiveSheet()->setTitle('Detailed Attendance Report');
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
                $this->render('detailedattendance');
                break;
        }
   }
   
   //santhu
   private function generatetimeattendancereport($type = '', $mode = ''){
        $arr_form_data = $_REQUEST;
        
        $arr_timeattendancereporttemplate = array();
        
        $arr_registerentry_heads = array(
            'P' => 'Present', 
            'L' => 'Leave', 
            'WO' => 'Week Off', 
            'HO' => 'Holiday', 
            'A' => 'Absent', 
            'LOP' => 'Loss Of Pay', 
            'OTHERS' => 'Others'
        );
        $this->set('arr_registerentry_heads',$arr_registerentry_heads);
        
        $arr_reportmasterdata = array();
        $control_pkey = $this->Session->read('company_key');
        $this->CentralControl->setDataSource('controldb');
        $arr_company_info = Set::extract('/CentralControl/.',$this->CentralControl->find('first', 
            array(
                'fields' => 'CentralControl.company_name, CentralControl.company_name, CentralControl.Address',
                'conditions' => array(
                    'control_pkey' => $control_pkey, 
                    'end_date_effective >= ' . date("Y-m-d")
                )
            )
        ));
        $this->set('arr_company_info',$arr_company_info);
               
        $report_month = '';
        if((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom']!= '')){
            $report_month = $arr_form_data['reportfrom'];
        }
        $this->set('report_month',date('F-Y',  strtotime($report_month)));
        
        //Fetch company's attendance end date
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y','company_code' => $company_code))));
        
        //On 20 Feb 2016
        //$att_enddate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:0;
        $att_enddate = date('d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',  strtotime($report_month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d',strtotime('-'.$attendance_date.' day',strtotime(date('Y-m-t',strtotime('-1 months',strtotime($report_month)))))))));
	$arr_date_in_selectedmonth = range(1, $att_enddate);        
        if($att_startdate != 1){
            $arr_date_in_prevmonth = range($att_startdate, date('t',strtotime('-1 months',strtotime($report_month))));
        }else{
            $arr_date_in_prevmonth = array();
        }
        
        $arr_dates = array_merge($arr_date_in_prevmonth,$arr_date_in_selectedmonth);
        $this->set('arr_dates',$arr_dates);
        
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        
        $arr_conditions  =   array();
        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        $from = '';
        $to = '';
        if($report_month != ''){
            $from = date('Y-m-'.$att_startdate, strtotime('-1 months',strtotime($report_month)));
            $to = date('Y-m-'.$att_enddate, strtotime($report_month));
            $str_conditions_for_month = ' AND DeviceAttendance.LOGDATE BETWEEN "'.$from.'" AND "'.$to.'"';
        }
        
        $branch = 'all';
        $str_branch = '';
        $arr_branch = array();
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
            $arr_reportcriterias = Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("fields"=>"reportcriteria_field", "conditions"=>array("status"=>1,'reportcriteria'=>$str_criteria_item,'reporttype'=>$type))));
            if(isset($arr_reportcriterias[0]['reportcriteria_field']) &&!empty($arr_form_data[$str_criteria_item])){
            
                if($str_criteria_item == 'Units'){
                    $str_branch = '\''.implode("','",$arr_form_data[$str_criteria_item]).'\'';
                    $arr_branch = $arr_form_data[$str_criteria_item];
                    $arr_conditions[] = "EmployeeDetails.".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
                }else{//to be removed after procedure update
                    $arr_conditions[] = $str_criteria_item.".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
                }
            }
        }
        $str_conditions = implode(' AND ', $arr_conditions);
        $str_conditions = ($str_conditions != '')?$str_conditions:' 1=1 ';
        
        $arr_branch_details = array();
        foreach ($arr_branch as $branch_code){
            
            $this->Units->useDbConfig = $this->Session->read('ds');
            $arr_branchinfo[$branch_code] = $this->Units->find('first',array(
                    'fields' => 'Units.branch_code, Units.branch_name',
                    'conditions' => array(
                        'Units.branch_code' => $branch_code
                    )
                )
            );
            
            $rand = $branch_code.strtotime('now');
            //Call procedure insert_update_att_reg_rep to update register entries in attendance_register_rep
            $this->AttendanceRegisterReport->useDbConfig = $this->Session->read('ds');
            $outputParameter = array();
            $outputParameter[] = $this->Session->read('company_code');
            $outputParameter[] = $branch_code;
            $outputParameter[] = $rand;//$this->Session->read('login_user_id');
            $outputParameter[] = date('Y-m-1', strtotime($report_month));
            $out = $this->AttendanceRegisterReport->insertUpdateAttendanceRegisterForReportProc($outputParameter);

            $arr_attendance_register_entries = $this->AttendanceRegisterReport->query(''
                    . 'SELECT * '
                    . 'FROM attendance_register_rep AS AttendanceRegisterReport '
                    . 'LEFT JOIN emp_details AS EmployeeDetails ON(AttendanceRegisterReport.emp_fkey=EmployeeDetails.emp_pkey) '
                    . 'LEFT JOIN branches AS Units ON(EmployeeDetails.branch_code=Units.branch_code) '
                    . 'WHERE AttendanceRegisterReport.month_year = "'.$report_month.'" '
                    . 'AND AttendanceRegisterReport.userid = "'.$rand.'"'
                    . 'AND '.$str_conditions
                    . '');
            
            $arr_timeattendancereporttemplate[$branch_code] = array();
            foreach ($arr_attendance_register_entries as $value){
                $arr_branch_details[$branch_code] = isset($value['Units'])?$value['Units']:array();
                $arr_regdata = array();
                $emp_pkey = isset($value['EmployeeDetails']['emp_pkey'])?$value['EmployeeDetails']['emp_pkey']:0;
                if($emp_pkey){
                    $i = 1;
                    $arr_regentries = array();
                    foreach ($arr_dates as $val){
                        $arr_regentries[$val] = isset($value['AttendanceRegisterReport']['FIELD'.$i])?$value['AttendanceRegisterReport']['FIELD'.$i]:'';
                        $i++;
                    }

                    $arr_registerentrysummary = array();
                    $arr_registerentrysummary[] = count(array_keys($arr_regentries, "P"));
                    $arr_registerentrysummary[] = count(array_keys($arr_regentries, "L"));
                    $arr_registerentrysummary[] = count(array_keys($arr_regentries, "WO"));
                    $arr_registerentrysummary[] = count(array_keys($arr_regentries, "HO"));
                    $arr_registerentrysummary[] = count(array_keys($arr_regentries, ""));
                    $arr_registerentrysummary[] = count(array_keys($arr_regentries, "LOP"));
                    $arr_registerentrysummary[] = count(array_keys($arr_regentries, "NA"))+count(array_keys($arr_regentries, "WFH"))+count(array_keys($arr_regentries, "COFF"));
                    
                    $arr_regdata = array(
                        'employeeinfo' => isset($value['EmployeeDetails'])?$value['EmployeeDetails']:array(),
                        'registerentries' => $arr_regentries,
                        'registerentrysummary' => $arr_registerentrysummary
                    );
                }
                $arr_timeattendancereporttemplate[$branch_code][$emp_pkey] = $arr_regdata;
            }
        }
       if(isset($arr_branchinfo)&& !empty($arr_branchinfo))
       {
        $this->set('arr_branchinfo',$arr_branchinfo);
       }
            else {
        echo "<div style='color:red'><h3>No record Found</h3></div>"  ;
        die();
    }
        /*$arr_time_attendance =  $this -> DeviceAttendance ->query(""
                . "SELECT "
                . "DeviceAttendance.LOGDATE,"
                . "DeviceAttendance.C1,"
                . "DeviceAttendance.C2,"
                . "DeviceAttendance.C3,"
                . "EmployeeDetails.emp_pkey,"
                . "EmployeeDetails.branch_code,"
                . "EmployeeDetails.first_name,"
                . "EmployeeDetails.last_name,"
                . "Units.branch_name "
                . "FROM "
                . "device_attandance AS DeviceAttendance "
                . "LEFT JOIN emp_details AS EmployeeDetails ON (EmployeeDetails.emp_id = DeviceAttendance.emp_id) "
                . "LEFT JOIN branches AS Units ON (DeviceAttendance.branch_code = Units.branch_code) WHERE ".$str_conditions.$str_conditions_for_month
                ." ORDER BY EmployeeDetails.first_name,DeviceAttendance.LOGDATE,DeviceAttendance.C1"
                ."");*/
         $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $arr_time_attendance = $this->DeviceAttendance->query("SELECT
        FIRST_NAME,     BRANCH_CODE, EMP_PKEY,       INTIME,     INVAL,
        (CASE WHEN lcase(INVAL) = 'in' AND lcase(OUTVAL) = 'in' THEN NULL ELSE OUTTIME END) AS OUTTIME ,
        OUTVAL
FROM
    (
        SELECT 
               MO.C1 AS OUTVAL,       MO.branch_code, MO.EMP_PKEY,       MO.FIRST_NAME,
               MO.LOGDATE AS OUTTIME,
               @S AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)' ,
               @LOGIN := @S AS INTIME,
               @C AS 'LEAD(c1) over(PARTITION BY emp_pkey ORDER BY logdate)',
               @INVAL := @C AS INVAL,
               (CASE WHEN @PARTITION_BY_COLUMN = EMP_PKEY OR @_SEASON IS NULL THEN @S := LOGDATE
                                                                              ELSE @S := NULL
                 END) TMP_VALUE,
               (CASE WHEN @PARTITION_BY_COLUMN = EMP_PKEY OR @_SEASON IS NULL THEN @C := C1
                                                                              ELSE @C := NULL
                 END) TMP_VALUE1,
               (@PARTITION_BY_COLUMN := EMP_PKEY) PARTITION_COLUMN
        FROM 
               (
                  SELECT 
                        EmployeeDetails.branch_code, EmployeeDetails.EMP_PKEY,    EmployeeDetails.FIRST_NAME,      ATT.LOGDATE,    ATT.C1
                  FROM (SELECT @PARTITION_BY_COLUMN = NULL,
                               @S := NULL,
                               @C := NULL,
                               @LOGIN := NULL,
                               @INVAL := NULL
                       ) VARS, DEVICE_ATTANDANCE ATT, EMP_DETAILS EmployeeDetails
                  WHERE 
                        ATT.EMP_ID = EmployeeDetails.EMP_ID and att.status='Y'
                        AND DATE_FORMAT(ATT.LOGDATE,'%Y-%m-%d') BETWEEN '".$from."' AND '".$to."' AND ".$str_conditions." order by att.logdate,ATT.C1
                ) MO
    ) XX
WHERE lcase(INVAL) = 'in' AND lcase(OUTVAL) IN ('in','out' )");
        //debug($arr_time_attendance);die();
        $i = 0;
        foreach($arr_time_attendance as $value){
            $branch_code = isset($value['XX']['BRANCH_CODE'])?$value['XX']['BRANCH_CODE']:'';
            $emp_pkey = isset($value['XX']['EMP_PKEY'])?$value['XX']['EMP_PKEY']:'';
            
            $indate = isset($value['XX']['INTIME'])?$value['XX']['INTIME']:'';
            $outdate = isset($value[0]['OUTTIME'])?$value[0]['OUTTIME']:'';
            
            $intime = ($indate != '')?date('H:i',  strtotime($indate)):'';
            $outtime = ($outdate != '')?date('H:i',  strtotime($outdate)):'';
            
            $inlogday = date('j',  strtotime($indate));
            $outlogday = date('j',  strtotime($outdate));
            
            if(!isset($arr_timeattendancereporttemplate[$branch_code][$emp_pkey]['employeeinfo'])){
                $empname = isset($value['XX']['FIRST_NAME'])?$value['XX']['FIRST_NAME']:'';
                $arr_timeattendancereporttemplate[$branch_code][$emp_pkey]['employeeinfo'] = array('first_name'=>$empname);
            }
            
            $arr_timeattendancereporttemplate[$branch_code][$emp_pkey]['checkin'][$inlogday] = $intime;
            $arr_timeattendancereporttemplate[$branch_code][$emp_pkey]['checkout'][$inlogday] = $outtime;            
            $arr_timeattendancereporttemplate[$branch_code][$emp_pkey]['duration'][$inlogday] = (isset($outdate) && $outdate != NULL && $outdate != '')?round(abs(strtotime($outdate) - strtotime($indate)) / 60,2):'';
            
            $i++;
        }
        //debug($arr_timeattendancereporttemplate);
        $this->set('arr_timeattendancereporttemplate',$arr_timeattendancereporttemplate);
$this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);
        switch ($mode){
            case 'pdf' : 
               
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('reporttimeattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('TimeAttendanceReport.pdf', 'D');
    
                break;
            case 'excel' :
                  $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_timeattendane.xlsx":"AttendanceA".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Time Attendance Report");
              $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:F1');
                $worksheet->mergeCells('A2:F2');
                $worksheet->mergeCells('A3:F3');
                $worksheet->mergeCells('A4:F4');
                 $worksheet->mergeCells('A5:F5');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $columncount = 0; 
                $rowcount = 2;
                if(!empty($arr_company_info)){
                    $companyname=$arr_company_info[0]['company_name'];
                    $companyadrress=$arr_company_info[0]['Address'];
                   
                }
               $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $companyname);
                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setSize(14);
               $objPHPExcel->getActiveSheet()->mergeCells('A2:F2');
                
               $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, 3, $companyadrress);
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, 3)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, 4, "For the month ".$report_month);
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, 4)->getFont()->setBold(true);
               $rowcount=5;
              foreach ($arr_timeattendancereporttemplate as $branch_code=>$data){
                  
                  $cname=(isset($arr_branchinfo[$branch_code]['Units']['branch_code'])?$arr_branchinfo[$branch_code]['Units']['branch_code'].' - ':'').(isset($arr_branchinfo[$branch_code]['Units']['branch_name'])?$arr_branchinfo[$branch_code]['Units']['branch_name']:'');
                  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $cname);
                  $rowcount=$rowcount+1;
                   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), $rowcount, 'SL.No');
                           $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount+1), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount+1), $rowcount)->getFont()->setBold(true);
                     $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount+2), $rowcount, 'Days');
                     $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount+2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->mergeCells('C6:AG6');
                    $objPHPExcel->getActiveSheet()->getStyle('C6')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                      $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(33, $rowcount, 'summary');
                       $rowcount=$rowcount+1;
                       $columnindex=2;
                   $objPHPExcel->getActiveSheet()->mergeCells('AH6:AN6');
                  $objPHPExcel->getActiveSheet()->getStyle('AH6')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                ); 
            
             

                                 foreach ($arr_dates as $key=> $date){ 
                       
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount, $date);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                 $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                                $columnindex++;
                           
                        }
                        foreach ($arr_registerentry_heads as $head=>$val) {
                          $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount, $head);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                                $columnindex++;  
                        }
                         $rowcount=$rowcount+1;
                       $columnindex=0;
                        if(!empty($data)){ $i = 1; 
                        foreach ($data as $emp_pkey => $regdata){
                            $name=(isset($regdata['employeeinfo']['first_name'])?$regdata['employeeinfo']['first_name']:' ').(isset($regdata['employeeinfo']['last_name'])?$regdata['employeeinfo']['last_name']:' ');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,$i++);  
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1).$rowcount,$name);
                       $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((1), $rowcount)->getFont()->setBold(true);

                        $columnindex=2;
                        if(!empty($regdata['registerentries'])){ foreach ($regdata['registerentries'] as $day=>$regentry){
                       
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$regentry); 
     $columnindex++;
                        } }else{
                            foreach ($arr_dates as $date){
                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,''); 
     $columnindex++;
                        }
                        }
                       // }
                        if(!empty($regdata['registerentrysummary'])){
                            foreach ($regdata['registerentrysummary'] as $regentryhead=>$cnt_regentry){
            
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$cnt_regentry); 
        $columnindex++;  } 
         
                            }
                            else{ foreach ($arr_registerentry_heads as $date){ 
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,''); 
    $columnindex++;
         }
                        }
      //time chek in starts
           $colindex = 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount + 1), 'Check In Time');
                        if (!empty($regdata['checkin'])) {

                            foreach ($arr_dates as $index => $day) {
                                if (isset($regdata['checkin'][$day])) {
                                    $checkin = $regdata['checkin'][$day];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 1), $checkin);
                                    $colindex++;
                                } else {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 1), '');
                                    $colindex++;
                                }
                            }
                        } else {
                            foreach ($arr_dates as $date) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 1), '');

                                $colindex++;
                            }
                        }
                            //time chek in ends
                          //time check out starts
                        $colindex = 2;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount + 2), 'Check Out Time');
                        if (!empty($regdata['checkout'])) {
                            foreach ($arr_dates as $index => $day) {
                                if (isset($regdata['checkout'][$day])) {
                                    $checkout = $regdata['checkout'][$day];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 2), $checkout);
                                    $colindex++;
                                } else {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 2), '');
                                    $colindex++;
                                }
                            }
                        } else {
                            foreach ($arr_dates as $date) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 1), '');

                                $colindex++;
                            }
                        }
                             //time check out ends
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount + 3), 'Duration(In Min)');

                        if (!empty($regdata['duration'])) {
                            foreach ($arr_dates as $index => $day) {
                                if (isset($regdata['duration'][$day])) {
                                    $duration = $regdata['duration'][$day];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 3), $duration);

                                    $colindex++;
                                } else {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 3), '');

                                    $colindex++;
                                }
                            }
                        } else {
                            foreach ($arr_dates as $date) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($colindex) . ($rowcount + 1), '');

                                $colindex++;
                            }
                        }
                        $rowcount=$rowcount+4;
                 // $rowcount++;  
           
                            }
                        $rowcount++;
                        }
                        else {
                            $msg='No record found';
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1).$rowcount,$msg);
                        }
                        $rowcount++;
              }
                $objPHPExcel->getActiveSheet()->setTitle('Time atttendance');
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
                $this->render('reporttimeattendance');
                break;
        }
   }
   private function generatemobilelocationreport($type = '', $mode = '')
   {
       $arr_form_data = $_REQUEST;
      $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
         $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

                $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
               // debug($arr_leavepolicygroupids);
        }
        if(isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids))
       {
          $condion1="";
               if((isset($arr_form_data['select-criteria1']) && $arr_form_data['select-criteria1']== 'EmployeeDetails')){
           $arr_leavepolicygroupids = implode (", ", $arr_leavepolicygroupids);
        $condion1 .= 'and  uc.emp_fkey in ('.$arr_leavepolicygroupids.')';
               }
        
       $date = isset($arr_form_data['reportfrom'])?$arr_form_data['reportfrom']:date('Y-m');  
       $month=date('m',strtotime($date));
       $year=date('Y',strtotime($date));
       
       $arr_mob_location = $this->MobileUserauditor->query("SELECT mlu .userid,
           concat (first_name,' ',middle_name,' ' ,last_name) empname ,info.*,
           uc.emp_fkey,mlu .time_check,mlu .location,mlu .in_out
           FROM mob_user_login_auditor mlu , 
           user_credentials uc 
         left join employee_info as info on(info.emp_pkey=uc.emp_fkey)  
where mlu.userid=uc.user_id 
           and Month(mlu .time_check)=$month and Year(mlu .time_check)=$year
           $condion1
           UNION ALL SELECT mbuserloc.user_id,concat (first_name,' ',middle_name,' ' ,last_name) empname ,info.*,
           'name',mbuserloc.created_time,mbuserloc.location,'Updated Location' from mob_user_locations mbuserloc, user_credentials uc 
       left join employee_info as info  on(info.emp_pkey=uc.emp_fkey)      
where 
           mbuserloc.user_id=uc.user_id 
        
           order by time_check,in_out
");  
 //debug($arr_mob_location);die();
  //}
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
              $this->set('date',$date);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);
     if(isset($arr_mob_location) && !empty($arr_mob_location))
       {
        $this->set('arr_mob_location',$arr_mob_location);
    
        switch ($mode){
            case 'pdf' : 
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportmobilelocation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Mobilelocation.pdf', 'D');
                break;
            case 'excel' :
                   $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_mobilelocation.xlsx":"AttendanceA".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "  Mobile Location Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'J'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
 $worksheet->setCellValueByColumnAndRow(0,2, "For the Month : ".$date);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(16);
                $worksheet->mergeCells('A2:F2');
                $rowcount = 3;
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Date');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1).$rowcount, 'Employee Name');
               $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Designation');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Date Of join');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Departments');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Branch');
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7).$rowcount, 'Action');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8).$rowcount, 'Location');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            for ($i = 0; $i <= 8; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                      
                    }
                     $columnindex =0;
                      $rowcount=4;
                      $empnme="";
                      foreach($arr_mob_location as $value){
                             
                                               //$name=$name.$key;
                            $name=$value[0]['empname']; 
                            $date=$value[0]['time_check']; 
                            $action=$value[0]['in_out'];
                            $location=$value[0]['location'];
                                $id = $value[0]['employee_id'];
                            $clas = $value[0]['designation'];
                            $join = $value[0]['joining_date'];
                            $dept = $value[0]['department'];
                            $unit = $value[0]['branch'];
                     
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$date);
                      if($empnme==$name)
                      {
                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount," ");   
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+2), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+3), ($rowcount), "");
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+4), ($rowcount), "");
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+5), ($rowcount), "");
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+6), ($rowcount), "");
                         // $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($rowcount1, $columnindex);
                         // $objPHPExcel->setActiveSheetIndex(0)->mergeCells(($columnindex+1).$rowcount.':'.($columnindex+1).($rowcount+1));
                      }
                      else {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$name);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+2), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+3), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+4), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+5), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex+6), ($rowcount), $unit);
                      }
                           
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+7).$rowcount,$action);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+8).$rowcount,$location);
                            $rowcount++;
                            $empnme=$value[0]['empname'];
                            }
                      
             
                $objPHPExcel->getActiveSheet()->setTitle('Mobile Location');
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
                $this->render('reportmobilelocation');
                break;
        }
   }
   else {
        echo "<div style='color:red'><h3>No record Found</h3></div>"  ; 
   }
       }
   else {
        echo "<div style='color:red'><h3>No record Found</h3></div>"  ; 
   }
   }

        }
