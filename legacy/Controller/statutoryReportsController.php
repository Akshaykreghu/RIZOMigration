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
class statutoryReportsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout = "default";
    public $name = 'Reports';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('LeavePolicyGroup','CentralControl','UserCredentials','EmployeeDetails','EmployeeProfessionalDetails','Departments','Grades','Verticals','Units','ReportCriterias','DayTimeProcedures');
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
            'employee' => 'Employee Information',
            'shiftpolicy' =>  'Shift Policy Reports',
            'leavepolicy' =>  'Leave Policy Reports',
            'holiday' =>  'Holiday Group Reports',
            /*'leave' => 'Leaves Report',
            'attendance' => 'Attendance Summary',*/
            'tax' => 'Tax Declarations'
        );
        $this->set('arr_reporttypes',$arr_reporttypes);
    }
    
    /*
     * Change Sub Report type
     */
    public function changereporttype($type=''){
        $this -> autoRender = FALSE;
        if($type != ''){
            $this->set('type',$type);
            switch ($type){
                case 'employee':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'shiftpolicy':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                            
                case 'leavepolicy':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'holiday':
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
                                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,ifnull(last_name," ")) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
                                    break;
            }
            echo json_encode($arr_criteriaItems);
        } 
    }
    
    public function generatereport($type='',$mode=''){
        $this->autoRender = false;
        switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'shiftpolicy':
                $this->generateshiftpolicyreport($mode);
                break;
             case 'leavepolicy':
                $this->generateleavepolicyreport($mode);
                break;
             case 'holiday':
                $this->generateshiftpolicyreport($mode);
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
   
   private function generateshiftpolicyreport($mode){
        $arr_form_data = $_REQUEST;
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        
        $fields = 'DayTimeProcedures.*,EmployeeConfig.*,EmployeeDetails.*,EmployeeProfessionalDetails.*,Departments.*,Grades.*,Verticals.*,Units.*';

        $joins = array(
            array(
                'table' => 'emp_config',
                'alias' => 'EmployeeConfig',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('DayTimeProcedures.day_time_seq = EmployeeConfig.day_time_seq')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeConfig.emp_fkey = EmployeeDetails.emp_pkey')
            ),
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
            $arr_reportcriterias = Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("fields"=>"reportcriteria,reportcriteria_field", "conditions"=>array("status"=>1,'reportcriteria'=>$str_criteria_item))));
            if(isset($arr_reportcriterias[0]['reportcriteria_field'])){
                if($str_criteria_item != 'EmployeeProfessionalDetails'){
                    $conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
                }
            }
        }
        $arr_shiftpolicy_details =  $this -> DayTimeProcedures ->find("all",array(
            'fields'=>$fields,
            'joins'=>$joins,
            'conditions'=>$conditions
        ));
        
        //$this->set('arr_shiftpolicy_details', $arr_shiftpolicy_details);
        
        //Set informations needed for report
        $policytitle = isset($arr_shiftpolicy_details[0]['DayTimeProcedures']['day_time_desc'])?$arr_shiftpolicy_details[0]['DayTimeProcedures']['day_time_desc']:'';
        
        $arr_workingdays = array();
        $arr_offdays = array();
        
        $arr_days = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
        
        for($i=0;$i<count($arr_days);$i++){
            if(isset($arr_shiftpolicy_details[0]['DayTimeProcedures'][$arr_days[$i]])){
                if($arr_shiftpolicy_details[0]['DayTimeProcedures'][$arr_days[$i]] == 'Y'){
                    $arr_workingdays[] = $arr_days[$i];
                }else if($arr_shiftpolicy_details[0]['DayTimeProcedures'][$arr_days[$i]] == 'N'){
                    $arr_offdays[] = $arr_days[$i];
                }
            }
        }
        
        $arr_duty = array();
        for($i=1;$i<=4;$i++){
            $duty = array();
            $duty['on_dutty'] = isset($arr_shiftpolicy_details[0]['DayTimeProcedures']['on_dutty'.$i])?$arr_shiftpolicy_details[0]['DayTimeProcedures']['on_dutty'.$i]:'';
            $duty['off_dutty'] = isset($arr_shiftpolicy_details[0]['DayTimeProcedures']['off_dutty'.$i])?$arr_shiftpolicy_details[0]['DayTimeProcedures']['off_dutty'.$i]:'';
            $duty['working_time'] = isset($arr_shiftpolicy_details[0]['DayTimeProcedures']['working_time'.$i])?$arr_shiftpolicy_details[0]['DayTimeProcedures']['working_time'.$i]:'';
            $arr_duty[] = $duty;
        }
        
        $this->set('policytitle',$policytitle);
        $this->set('str_workingdays',implode(',',$arr_workingdays));
        $this->set('str_offdays',implode(',',$arr_offdays));
        $this->set('arr_duty',$arr_duty);
        $this->set('arr_shiftpolicy_summary',isset($arr_shiftpolicy_details[0]['DayTimeProcedures'])?$arr_shiftpolicy_details[0]['DayTimeProcedures']:array());
        
        //Fetch employees under this shift        
        $arr_employee_details = array();
        foreach($arr_shiftpolicy_details as $value){
            $arr_employee = array();
            $firstname = isset($value['EmployeeDetails']['first_name'])?$value['EmployeeDetails']['first_name']:'';
            $lastname = isset($value['EmployeeDetails']['last_name'])?$value['EmployeeDetails']['last_name']:'';
            $arr_employee['companyemployeeid'] = isset($value['EmployeeProfessionalDetails']['emp_company_id'])?$value['EmployeeProfessionalDetails']['emp_company_id']:'';
            $arr_employee['fullname'] = $firstname." ".$lastname;
            $arr_employee['designation'] = isset($value['EmployeeProfessionalDetails']['designation'])?$value['EmployeeProfessionalDetails']['designation']:'';
            $arr_employee['branch'] = isset($value['Units']['branch_name'])?$value['Units']['branch_name']:'';
                    
            $arr_employee_details[] = $arr_employee;
        }
        $this->set('arr_employee_details',$arr_employee_details);
        //Ends
        
        switch ($mode){
            case 'pdf' : 
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportshiftpolicy');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('ShiftPolicy.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_ShiftPolicy.xlsx":"ShiftPolicy".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Shift Policy Report");
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                
                $rowcount = 2;
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Policy');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1).$rowcount, $policytitle);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                $objPHPExcel->getActiveSheet()->setTitle('Shift Policy');

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
                $this->render('reportshiftpolicy');
                break;
        }
   }
   
    
   private function generateleavepolicyreport($mode){
        $arr_form_data = $_REQUEST;
        $this->LeavePolicyGroup->useDbConfig = $this->Session->read('ds');
        
        //Build conditions based on criterias recieved
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for($i=1;$i<=$int_criterias_count;$i++){
            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
            if($str_criteria_item == 'LeavePolicyGroup'){
                //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
                $arr_leavepolicygroupids = $arr_form_data[$str_criteria_item];
            }
        }
        
        $arr_leavepolicydetails_for_template = array();
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid){
            $str_conditions = ' WHERE LeavePolicyGroup.LEAVEPOLICY_GROUP_ID='.$leavepolicygroupid;
            $arr_leavepolicy_details = $this->LeavePolicyGroup->query(''
                    . 'SELECT '
                    . '`LeavePolicyGroup`.*, LeavePolicy.*,salary_head_items.item '
                    . 'FROM '
                    . '`client_db1`.`leavepolicy_group` AS `LeavePolicyGroup` '
                    . 'LEFT JOIN `client_db1`.`leavepolicy` AS `LeavePolicy` ON (`LeavePolicyGroup`.`LEAVEPOLICY_GROUP_ID` = `LeavePolicy`.`LEAVEPOLICY_GROUP_ID`)'
                    .'LEFT JOIN `client_db1`.`salary_head_items` AS `salary_head_items` ON (`salary_head_items`.`salary_head_item_pkey` = `LeavePolicy`.`salary_head_item_fkey`)'
                    .$str_conditions);
            
            $str_conditions .= ' AND EmployeeDetails.status=1';
            $arr_leavepolicy_employees = $this->LeavePolicyGroup->query(''
                    . 'SELECT '
                    . '`EmployeeDetails`.first_name, `EmployeeDetails`.last_name, '
                    . '`EmployeeProfessionalDetails`.emp_company_id, `EmployeeProfessionalDetails`.designation, '
                    . '`Units`.branch_name '
                    . 'FROM '
                    . '`client_db1`.`leavepolicy_group` AS `LeavePolicyGroup` '
                    . 'LEFT JOIN `client_db1`.`emp_proff` AS `EmployeeProfessionalDetails` ON (`LeavePolicyGroup`.`LEAVEPOLICY_GROUP_ID` = `EmployeeProfessionalDetails`.`LEAVEPOLICY_GROUP_ID`) '
                    . 'LEFT JOIN `client_db1`.`emp_details` AS `EmployeeDetails` ON (`EmployeeProfessionalDetails`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`) '
                    . 'LEFT JOIN `client_db1`.`branches` AS `Units` ON (`EmployeeProfessionalDetails`.`emp_branch` = `Units`.`branch_code`)'.$str_conditions);
            
            $arr_leavepolicydetails_for_template[] = array(
                'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                'summary'=>$arr_leavepolicy_details,
                'employees'=>$arr_leavepolicy_employees
            );
        }
        
        //debug($arr_leavepolicydetails_for_template);die();
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        
        //Set informations needed for report
    
        
        switch ($mode){
            case 'pdf' : 
//                $this->set('mode','pdf');
//                $view = new View($this, false);
//                $view_output = $view->render('reportshiftpolicy');
//                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
//
//                $html2pdf = new HTML2PDF('P', 'A4', 'en');
//                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
//                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
//                $html2pdf->pdf->SetDisplayMode('fullpage');
//                $html2pdf->writeHTML($view_output);
//                $html2pdf->Output('ShiftPolicy.pdf', 'D');
//                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_ShiftPolicy.xlsx":"ShiftPolicy".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Shift Policy Report");
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                
                $rowcount = 2;
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Policy');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1).$rowcount, $policytitle);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                $objPHPExcel->getActiveSheet()->setTitle('Shift Policy');

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
                $this->render('reportleavepolicy');
                break;
        }
   }
   
    
   private function generateholidaypolicyreport($mode){
        $arr_form_data = $_REQUEST;
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        
        $fields = 'DayTimeProcedures.*,EmployeeConfig.*,EmployeeDetails.*,EmployeeProfessionalDetails.*,Departments.*,Grades.*,Verticals.*,Units.*';

        $joins = array(
            array(
                'table' => 'emp_config',
                'alias' => 'EmployeeConfig',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('DayTimeProcedures.day_time_seq = EmployeeConfig.day_time_seq')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions'=> array('EmployeeConfig.emp_fkey = EmployeeDetails.emp_pkey')
            ),
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
            $arr_reportcriterias = Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("fields"=>"reportcriteria,reportcriteria_field", "conditions"=>array("status"=>1,'reportcriteria'=>$str_criteria_item))));
            if(isset($arr_reportcriterias[0]['reportcriteria_field'])){
                if($str_criteria_item != 'EmployeeProfessionalDetails'){
                    $conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
                }
            }
        }
        $arr_shiftpolicy_details =  $this -> DayTimeProcedures ->find("all",array(
            'fields'=>$fields,
            'joins'=>$joins,
            'conditions'=>$conditions
        ));
        
        //$this->set('arr_shiftpolicy_details', $arr_shiftpolicy_details);
        
        //Set informations needed for report
    
        
        switch ($mode){
            case 'pdf' : 
//                $this->set('mode','pdf');
//                $view = new View($this, false);
//                $view_output = $view->render('reportshiftpolicy');
//                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
//
//                $html2pdf = new HTML2PDF('P', 'A4', 'en');
//                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
//                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
//                $html2pdf->pdf->SetDisplayMode('fullpage');
//                $html2pdf->writeHTML($view_output);
//                $html2pdf->Output('ShiftPolicy.pdf', 'D');
//                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_ShiftPolicy.xlsx":"ShiftPolicy".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Shift Policy Report");
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                
                $rowcount = 2;
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Policy');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1).$rowcount, $policytitle);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                $objPHPExcel->getActiveSheet()->setTitle('Shift Policy');

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
                $this->render('reportleavepolicy');
                break;
        }
   }
}