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
class EditedReportsController extends AppController {

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
    public $uses = array('EditPunches','Attendance','CentralControl','UserCredentials','EmployeeDetails','EmployeeProfessionalDetails','DeviceAttendance','Departments','Grades','Verticals','Units','ReportCriterias','AttendanceRegister','AttendanceRegisterReport','DbConfig','MobileUserauditor','CompanyContactInfo');//santhu
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
            'EditPunches' =>  'Edit Punches'
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
                case 'EditPunches':
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
    
    public function listcriteriaitems($str_criteria=''){   
        $this->autoRender = false;
        $model = $str_criteria; 

        if(isset( $model ) && $model != ''){
            $this -> {$model} -> useDbConfig = $this -> Session -> read('ds');
            if($model == 'DayTimeProcedures'){
               $conditions = array("active"=>1); 
            }else{
                $conditions = array("status"=>1);
            }
        
            $arr_criteriaItemsDB = Set::extract('/'.$model.'/.',$this->{$model}->find("all",array("conditions"=>$conditions)));
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model){
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
                                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",emp_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
                                    $joins = array(
                                        array(
                                        'table' => 'emp_proff',
                                        'alias' => 'EmployeeProfessionalDetails',
                                        'type' => 'LEFT',
                                        'foreignKey' => false,
                                        'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                                        )
                                    );
                                    $conditions  =   array('status'=>1);

                                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                                    $arr_emp =  $this -> EmployeeDetails ->find("all",array(
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
    
    public function generatereport($type='',$mode=''){
        $this->autoRender = false;
//debug($mode);
       switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'EditPunches':
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
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
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
            $from = date('Y-m-1',  strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t',  strtotime($arr_form_data['reportfrom']));
        }
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
           
                $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            
        }
        
        $arr_leavepolicydetails_for_template = array();
        if(isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)){
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid){
              $fields = 'EditPunches.*,Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name';
        $joins = array(
            array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EditPunches.branch_code = Branch.branch_code',
                    'Branch.status=1'
                )
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array(
                    'EditPunches.emp_id = EmployeeDetails.emp_id',
                    'EmployeeDetails.status=1'
                )
            )
        );
         $conditions = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and EditPunches.DEVICELOGID is NULL and EditPunches.LOGDATE between "'.$from.'" and "'.$to.'"';
        $arr_leavepolicy_details = $this->EditPunches->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
     
     $arr_leavepolicydetails_for_template[] = array(
                'summary'=>$arr_leavepolicy_details,
               // 'employees'=>$arr_leavepolicy_employees
            );
     
        }
//      
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
   //debug($arr_leavepolicydetails_for_template);  
    
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
           //   debug($view_output);
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
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

                $worksheet->setCellValueByColumnAndRow(0, 1, "Edited Attendance Report");
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
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
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
                                
                $rowcount = 3;  
                      //$rowcount1=3;
                      foreach($arr_leavepolicydetails_for_template as $value){
                          if(isset($value['summary']['0']['EmployeeDetails']['first_name']))
                {
                              
                              $names=isset($value['summary']['0']['EmployeeDetails']['first_name']) ? $value['summary']['0']['EmployeeDetails']['first_name'].' ' .$value['summary']['0']['EmployeeDetails']['last_name'] : '' ;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,"Attendanbce Report of ".$names);
                         $arr_data  = $value['summary'];
                         $worksheet->mergeCells('A'.$rowcount.':F'.$rowcount);
                         $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                 array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                         );
                         $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((0), $rowcount)->getFont()->setBold(true);
                         $rowcount+=2;
                              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Id');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Direction');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Comments');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                $columnindex = $columncount+9;
                              
                                 
                         $rowcount+=2;
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        if(count($arr_data)>=0){
                            foreach($arr_data as $key=>$val){
                                      $columnindex = 0;
                                      
                            $name=$val['EmployeeDetails']['first_name'].' '.$val['EmployeeDetails']['last_name'];
                            //$name=$name.$key;
                            $date=$val['EditPunches']['LOGDATE'];
                            $direction=$val['EditPunches']['C1'];
                            $commnts=$val['EditPunches']['C3'];
                                        $id=$val['EditPunches']['emp_id']; 
                                          $branch=$val['Branch']['branch_name']; 
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+1).$rowcount,$id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+2).$rowcount,$branch);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+3).$rowcount,$date);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+4).$rowcount,$direction);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex+5).$rowcount,$commnts);
                            
                            $columnindex=$columnindex+9;
                                            
                            
                          $rowcount++;
                            }
                        }
                        $rowcount1=$rowcount+1;
                      }}
             
                $objPHPExcel->getActiveSheet()->setTitle('Edited Attendance Report ');
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
          }
   else     {
           echo "<div style='color:red'><h3>No record Found</h3></div>"  ;
         // $this->layout=null;
        }
   }
   
  
  

        }
