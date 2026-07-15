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
ini_set('max_execution_time', 200);
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class TaxReportController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout = "default";
    public $name = 'TaxReport';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('LeaveRequests','Leavestatus','SalaryHeadItems','CentralControl','UserCredentials','EmployeeDetails','EmployeeProfessionalDetails','DeviceAttendance','Departments','Grades','Verticals','Units','ReportCriterias','AttendanceRegister','AttendanceRegisterReport','CompanyContactInfo','EmployeeTaxsalsum', 'ReportAudit');//santhu
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
//            'employee' => 'Employee Information',
       'TaxTDS' => 'Tax TDS Report ',
        );
        $this->set('arr_reporttypes',$arr_reporttypes);
    }
    
    /*
     * Change Sub Report type
     */
    public function changereporttype($type=''){
        $this -> autoRender = FALSE;
       // debug($type);
        if($type != ''){
            $this->set('type',$type);
            switch ($type){
                case 'employee':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'TaxTDS':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'LeaveDetaillsReport':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break; 
                case 'LeaveBalance':
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
 //view   
    public function listcriteriaitems($str_criteria=''){   
        $this->autoRender = false;
        $model = $str_criteria; 

        if(isset( $model ) && $model != ''){
            $this -> {$model} -> useDbConfig = $this -> Session -> read('ds');
            if($model == 'SalaryHeadItems'){
               $conditions = array("head_fkey"=>6,"value"=>'Y',"status"=>1); 
            }
         elseif($model == 'DayTimeProcedures'){
               $conditions = array("active"=>1); 
            }
         elseif($model == 'Leavestatus'){
               $conditions = array(); 
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
                case 'SalaryHeadItems':
                                    foreach ($arr_criteriaItemsDB as $key => $value) {
                                        $arr_criteriaItems[$key]['key'] = $value['salary_head_item_pkey'];
                                        $arr_criteriaItems[$key]['text'] = $value['item'];
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
                                    
                                    
                case 'EmployeeDetails':
                    //edited by arul - changed empid ad company id
                                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
     public function reportAudit($type, $mode) {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        switch ($type) {
            case 'TaxTDS':
                $dataForHistory['report_type'] = "Tax TDS Reports";
                break;
            case 'TaxB':
                $dataForHistory['report_type'] = "Tax Part B Report";
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
                case 'EmployeeDetails		': $criteria_name_array[] = 'belonging to an Employee';
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
    public function generatereport($type='',$mode=''){
        $this->autoRender = false;

       switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'TaxTDS':
                $this->generatesummaryreport($mode);
                break;
             case 'LeaveBalance':
                $this->generateleavebalancereport($mode);
                break;
             case 'DetailedAttendance':
                $this->generateDetailedreport($mode);
                break;
             case 'TimeAttendance':
                $this->generatetimeattendancereport($type, $mode);
                break;
            default:
                return false;
                break;
        }
  $this->reportAudit($type, $mode);
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
           // debug($arr_reportfieldheadings);
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

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
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
        }
   }

   
   
   
   
   
  
   
   private function generatesummaryreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EmployeeTaxsalsum->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        $needBranchWiseReport = false;
        $conditions = array();
        $conditions[] = 'start_date_effective >="' . $from . '" and start_date_effective<="' . $to . '"';
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                                "fields" => "reportcriteria,reportcriteria_field",
                                "conditions" => array(
                                    "reporttype" => "LeaveSummary",
                                    "status" => 1,
                                    'reportcriteria' => $str_criteria_item
                                )
            )));
            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
            }
        }
        $str_conditions = implode(' AND ', $conditions);
        $arr_tds_report = $this->EmployeeTaxsalsum->query(' Select EmployeeTaxsalsum.*,EmployeeDetails.*,EmployeePro.*
                        FROM `emp_tax_sal_trans_sum` AS `EmployeeTaxsalsum`
                        LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeTaxsalsum`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)
                        LEFT JOIN `emp_proff` AS `EmployeePro` ON (`EmployeePro`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)
                        WHERE `EmployeeTaxsalsum`.`status` = 1  and ' . $str_conditions);
        $this->set('arr_tds_report', $arr_tds_report);
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
                $view_output = $view->render('reportleavesummary');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('leavereport.pdf', 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_leave.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Tax TDS Report");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Tax TDS Report");
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
                $i=0;
              $rowcount = $rowcount + 1;
                foreach ($arr_tds_report as $value) {
                    
                    $firstname =$value['EmployeeDetails']['first_name'];
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'First Name:');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount,  $firstname);
                    $lastname =$value['EmployeeDetails']['last_name'];
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Last Name:'.$lastname);
                    $comid =$value['EmployeePro']['emp_company_id'];
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Company ID:'.$comid);
                    $emtype =$value['EmployeePro']['emp_type'];
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee Type:'.$emtype);
                    
                  //  $ $rowcount++;
                }
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
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
                $this->render('reportleavesummary');
                break;
        }
    }

    
    
    
    
    
    
    
    
    
    private function generateleavebalancereport($mode){
        $arr_form_data = $_REQUEST;
    //   debug($mode);
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
//  debug($arr_form_data);
        //Build conditions based on criterias recieved
        //$fd=$arr_form_data['reportfrom'].' '.'00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
$report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1',  strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t',  strtotime($arr_form_data['reportfrom']));
          //  debug($to);
          //  debug($from);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

                $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            //    debug($arr_leavepolicygroupids);
        }
        // debug($arr_leavepolicygroupids);
//  for($i=1;$i<=$int_criterias_count;$i++){
//            $str_criteria_item = $arr_form_data['hidden-criteria'.$i];
/////  debug($str_criteria_item);
//            //$str_employee_reportcriteria_field = $this->arr_employee_reportcriteria_fields[$str_criteria_item];
//            //$this->arr_employee_reportcriteria_fields[$str_criteria_item];
//
//            $arr_reportcriterias = Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("fields"=>"reportcriteria_field", "conditions"=>array("status"=>1,'reportcriteria'=>$str_criteria_item))));
//           
//          //  debug($arr_reportcriterias);
//            if(isset($arr_reportcriterias[0]['reportcriteria_field'])){
//                if($str_criteria_item != 'EmployeeProfessionalDetails'){
//                    $conditions[] = $arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
//                }
//            }
//        }
        
//           for ($i = 1; $i <= $int_criterias_count; $i++) {
//            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
//
//            
//            
//            
//            
//                $arr_leavepolicygroupids = $arr_form_data[$str_criteria_item];
//             //   debug($arr_leavepolicygroupids);
//        }
      //  debug(array_merge($conditions));
        $arr_leavepolicydetails_for_template = array();
       if($arr_leavepolicygroupids != '')
       {
        foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
//  debug($arr_form_data['select-criteria1']);
        //  debug($leavepolicygroupid);
            
              if($arr_form_data['select-criteria1'] == 'SalaryHeadItems') 
                   {       
                  $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                          
                            . ' AS emp_name, info.*,sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, 
                                lp.salary_head_item_fkey, 2015) leavebalance from 
                                emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ep.emp_fkey)
where ed.emp_pkey=ed.emp_pkey
and ed.branch_code=ed.branch_code
and lp.salary_head_item_fkey=' . $leavepolicygroupid . '');
                }
                     else  if($arr_form_data['select-criteria1'] == 'Departments')  
                   {
                  $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                          . ' AS emp_name, info.*, sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, 2015) leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey=ed.emp_pkey
and ed.branch_code=ed.branch_code
and ed.status = 1
and lp.salary_head_item_fkey='.$leavepolicygroupid.'');  
                   }
                   
                   
                     else  if($arr_form_data['select-criteria1'] == 'EmployeeDetails')  
                   {
                              $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                          . ' AS emp_name, info.*, sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, 2015) leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey='.$leavepolicygroupid.'
and ed.branch_code=ed.branch_code
and ed.status = 1
and lp.salary_head_item_fkey=lp.salary_head_item_fkey');  

                   }
                    else 
                   { 
                              $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,Units.branch_name,ed.branch_code, CONCAT(first_name, " ", last_name)'
                          . ' AS emp_name,  info.*,sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, 2015) leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey=ed.emp_pkey
and ed.status = 1
and ed.branch_code="'.$leavepolicygroupid.'"
and lp.salary_head_item_fkey=lp.salary_head_item_fkey');  

                   }
            
            
            
            
            
            
  
     //  debug($arr_empleaverequests);

            $arr_leavepolicydetails_for_template[] = array(
                //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                'summary' => $arr_empleaverequests,
                    // 'employees'=>$arr_leavepolicy_employees
            );
        }
    }
    
    else {
        echo "<div style='color:red'><h3>No record Found</h3></div>"  ;
        die();
    }
//  debug($arr_leavepolicydetails_for_template);
        foreach ($arr_leavepolicydetails_for_template as $key => $value) {
            //   debug($value);
//            foreach ($value['summary'] as $ky => $vaal) {
//                //     debug($vaal['AttendanceRegister']);
//                //   $resp_register["rows"][$key] = $value["AttendanceRegister"];
//// debug($vaal["AttendanceRegister"]);
//                $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
//                $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
//                $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
//
//                $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
//                $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
//                $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
//            }
        }
        // debug($resp_register);
        // debug($arr_leavepolicydetails_for_template);  
// debug($arr_leavepolicydetails_for_template);
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $cr=$arr_form_data['select-criteria1'];
        $this->set('cr',$cr);

        //Set informations needed for report
             $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
           $user_name= $this->Session->read('user_name');
             $this->set('user_name',$user_name);
           $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
           $this->set('arr_comp_contact_info',$arr_comp_contact_info);

        switch ($mode){
            case 'pdf' : 
                //echo "entered in";
                $this->set('mode','pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportleavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavebalance.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code   =   $this->Session->read('company_code');
                $file_name  = isset($str_company_code)?$str_company_code."_LeaveBalance.xlsx":"ShiftPolicy".strtotime().".xlsx";

                App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");            

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Balance Report");
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
                $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                   $i += 1; 
                   $le='Leave Balance Reports of ';
                      if($cr == 'Departments')
                        {
                         $dep=isset($value['summary']['0']['Departments']['dept_name'])?$value['summary'][0]['Departments']['dept_name']:''; 
                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, $le.$dep);
                           $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                         }
                        else  if($cr == 'Units')
                        {
                       $brn=isset($value['summary'][0]['Units']['branch_name'])?$value['summary'][0]['Units']['branch_name']:''; 
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, $le.$brn);
                          $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                        }
                        else  if($cr == 'EmployeeDetails')
                        {
                      $name= isset($value['summary'][0]['0']['emp_name'])?$value['summary'][0]['0']['emp_name']:''; 
                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, $le.$name);
                       $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);

                        }
                        else
                        {
                        $type=isset($value['summary'][0]['sh']['leave_type'])?$value['summary'][0]['sh']['leave_type']:''; 
                         $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, $le.$type);
                           $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        }
                        
                $rowcount = $rowcount+1;         
                 $columncount=0;       
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'Employee Name');
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
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6).$rowcount, 'leave Type');
                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7).$rowcount, 'Allotted leave For The year');
                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8).$rowcount, 'Leave Taken');
                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9).$rowcount, 'Leave Balance');
                  $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
             $rowcount = $rowcount+1;   
             $arr_data  = $value['summary']; 
                                if (count($arr_data) > 0) {
                        foreach ($arr_data as $val) {
                            $leavetaken = $val['lp']['alloted_leave_forthe_year'] - $val['0']['leavebalance'];
                            $emp = $val['0']['emp_name'];
                            $typ = $val['sh']['leave_type'];
                            $lp = $val['lp']['alloted_leave_forthe_year'];
                            $levbalnce = $val['0']['leavebalance'];
                             $id = $val['info']['employee_id'];
                            $clas = $val['info']['designation'];
                            $join = $val['info']['joining_date'];
                            $dept = $val['info']['department'];
                            $unit = $val['info']['branch'];
                       $columncount=0;  
                          $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, $emp);
                                 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount +3), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount +4), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount +5), ($rowcount), $dept);
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6).$rowcount, $typ);
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7).$rowcount, $lp);
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8).$rowcount, $leavetaken);
             $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9).$rowcount, $levbalnce);   
                   $rowcount ++;         
                        }
                    } else {
                     $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount, 'No record Found');   
                    }
            $rowcount ++;           }
                $objPHPExcel->getActiveSheet()->setTitle('Leave Balance');
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
                $this->render('reportleavebalance');
                break;
        }
   }
   
    
   
   
}