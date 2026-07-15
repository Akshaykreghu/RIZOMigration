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
class miscellaniousReportsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'MiscellaniousReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeaveRequests', 'Leavestatus', 'SalaryHeadItems', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'CompanyContactInfo'); //santhu
    public $components = array('MasterdataManagement');

    /* public $arr_employee_reportcriterias = array(
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
      ); */

    /*
     * HR Reports landing view
     */

    public function hrreports() {
        $arr_reporttypes = array(
//            'employee' => 'Employee Information',
            // 'LeaveDetaillsReport' =>  'Leave Details Reports',
            'LeaveSummary' => 'Leave Detailed Reports',
            'LeaveBalance' => 'Leave Balance Report',
//            'LeaveBalanceSummary' => 'Leave Taken Summary Report',
            'Compoff' => 'Leave Comp Off Report'
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '') {
        $this->autoRender = FALSE;
        // debug($type);
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
                case 'LeaveDetaillsReport':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LeaveBalance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'LeaveBalanceSummary':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //santhu
                case 'Compoff':
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
$arr_requestdata = $this->request->data;
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            if ($model == 'SalaryHeadItems') {
                $conditions = array("head_fkey" => 6, "value" => 'Y', "status" => 1);
            } elseif ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Leavestatus') {
                $conditions = array();
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
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",emp_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
                    if(isset($arr_requestdata['name']) && $arr_requestdata['name'] =='1')
                    {
                         $conditions = array("status in(1,2)");
                    }
                    else{
                        
                         $conditions = array("status" => 1);
                    }  

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

    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;

        switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'LeaveSummary':
                $this->generatesummaryreport($mode);
                break;
            case 'LeaveBalance':
                $this->generateleavebalancereport($mode);
                break;
            case 'Compoff':
                $this->generatecompoffreport($mode);
                break;
            case 'LeaveBalanceSummary':
                $this->generateleavebalancesummaryreport($type, $mode);
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

    private function generateemployeereport($mode = '') {
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
            // debug($arr_reportfieldheadings);
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
        $conditions = array('EmployeeDetails.status' => 1);

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
        $arr_emp_details = $this->EmployeeDetails->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions
        ));

        /* $arr_employee_personal = Set::extract('/EmployeeDetails/.',$arr_emp_details);
          $arr_employee_professional = Set::extract('/EmployeeProfessionalDetails/.',$arr_emp_details);
          $arr_employee_departments = Set::extract('/Departments/.',$arr_emp_details);
          $arr_employee_grades = Set::extract('/Grades/.',$arr_emp_details);
          $arr_employee_verticals = Set::extract('/Verticals/.',$arr_emp_details);
          $arr_employee_units = Set::extract('/Units/.',$arr_emp_details);
          $arr_employee_report_details = array_merge($arr_employee_personal,$arr_employee_professional,$arr_employee_departments,$arr_employee_grades,$arr_employee_verticals,$arr_employee_units); */

        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
                $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'), $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'), $arr_empinformation_fields->getFieldHeadings('Departments'), $arr_empinformation_fields->getFieldHeadings('Grades'), $arr_empinformation_fields->getFieldHeadings('Verticals'), $arr_empinformation_fields->getFieldHeadings('Units')
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

        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportemployeeinformation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeInformation.pdf', 'D');
                //$this->render('reportemployeeinformation');
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_EmployeeInformation.xlsx" : "EmployeeInformation_" . strtotime() . ".xlsx";

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

                $sheet = array($arr_emp_field_headings);

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
            default :
                $this->set('mode', '');
                $this->render('reportemployeeinformation');
                break;
        }
    }
//leave deatiled report
    private function generatesummaryreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-d', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-d', strtotime($arr_form_data['reportto']));

      
        $needBranchWiseReport = false;
        $conditions = array();
        
          $condition = ' where EmployeeDetails.status = 1 and ';
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition =  "where EmployeeDetails.status in('1','2') and ";
           
        }
        
        $conditions[] = 'FROMDATE >="' . $from . '" and TODATE<="' . $to . '"';
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }
            
            if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
            echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            
            try{
                $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                                "fields" => "reportcriteria,reportcriteria_field",
                                "conditions" => array(
                                    "reporttype" => "LeaveSummary",
                                    "status" => 1,
                                    'reportcriteria' => $str_criteria_item
                                )
            )));
            } catch (Exception $ex) {

            }

            
            
            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
            }
        }
        $str_conditions = implode(' AND ', $conditions);

        try{
            $arr_empleaverequests = $this->LeaveRequests->query('SELECT (SELECT CONCAT(first_name," ",last_name) from emp_details where emp_pkey =  LeaveRequests.ISAutherizedby) as Authorized_name,(SELECT CONCAT(first_name," ",last_name) from emp_details where emp_pkey =  LeaveRequests.APPROVEDBY) as Approved_name,`termination`.`last_approved_working_date`,`LeaveRequests`.`LEAVEENTRYID`,`LeaveRequests`.`FROMHALF`,`LeaveRequests`.`TOHALF`,`LeaveRequests`.`leave_days`,`LeaveRequests`.`REMARKS`,Units.branch_name,EmployeeDetails.branch_code,EmployeeDetails.status,CONCAT(first_name, " ", last_name) AS emp_name,Info.* ,`LeaveType`.`item` AS `leave_type`,`LeaveRequests`.`applied_date`, `LeaveRequests`.`FROMDATE`, `LeaveRequests`.`TODATE`, `LeaveRequests`.`LEAVESTATUS` FROM `leaveentries` AS `LeaveRequests`'
                . ' LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`LeaveRequests`.`EMP_fkey` = `EmployeeDetails`.`emp_pkey`)'
                . ' LEFT JOIN `salary_head_items` AS `LeaveType` ON (`LeaveRequests`.`salary_head_item_fkey` = `LeaveType`.`salary_head_item_pkey`) '
                . ' LEFT JOIN `branches` AS `Units` ON (`EmployeeDetails`.`branch_code` = `Units`.`branch_code`) '
                . ' LEFT JOIN `leavestatus` AS `Leavestatus` ON (`LeaveRequests`.`LEAVESTATUS` = `Leavestatus`.`LEAVESTATUS`) '
                . 'LEFT JOIN `employee_info` AS `Info` ON (`EmployeeDetails`.`emp_pkey` = `Info`.`emp_pkey`)'
                . 'LEFT JOIN `termination` AS `termination` ON (`termination`.`emp_fkey` = `Info`.`emp_pkey`)'
                . $condition . $str_conditions . 'ORDER BY EmployeeDetails.emp_pkey desc ');
//        debug($arr_empleaverequests);
        } catch (Exception $ex) {

        }
        
        $arr_leavesummary_for_template = array();
        if ($needBranchWiseReport) { 
            //Parse array for branchwise report
            foreach ($arr_empleaverequests as $leaverequest) {
                //  debug($leaverequest);
                $branch_code = isset($leaverequest['EmployeeDetails']['branch_code']) ? $leaverequest['EmployeeDetails']['branch_code'] : '';
                $branch_name = isset($leaverequest['Units']['branch_name']) ? $leaverequest['Units']['branch_name'] : '';
                if ($branch_code != '') {
                    if (!isset($arr_leavesummary_for_template[$branch_code])) {
                        $arr_leavesummary_for_template[$branch_code] = array(
                            'branch_name' => $branch_name,
                            'leaverequests' => array()
                        );
                    }
                    $request = array();
                    $request['emp_name'] = isset($leaverequest['Info']['EmpName']) ? $leaverequest['Info']['EmpName'] : '';
                    $request['employee_id'] = isset($leaverequest['Info']['employee_id']) ? $leaverequest['Info']['employee_id'] : '';
                    $request['branch'] = isset($leaverequest['Info']['branch']) ? $leaverequest['Info']['branch'] : '';
                    $request['designation'] = isset($leaverequest['Info']['designation']) ? $leaverequest['Info']['designation'] : '';
                    $request['department'] = isset($leaverequest['Info']['department']) ? $leaverequest['Info']['department'] : '';
                    $request['joining_date'] = isset($leaverequest['Info']['joining_date']) ? $leaverequest['Info']['joining_date'] : '';
                    $request['leave_type'] = isset($leaverequest['SalaryHeadItems']['leave_type']) ? $leaverequest['SalaryHeadItems']['leave_type'] : '';
                    $request['Authorized_name'] = isset($leaverequest['0']['Authorized_name']) ? $leaverequest['0']['Authorized_name'] : '';
                    $request['Approved_name'] = isset($leaverequest['0']['Approved_name']) ? $leaverequest['0']['Approved_name'] : '';
                    $request['leave_status'] = isset($leaverequest['LeaveRequests']['LEAVESTATUS']) ? $leaverequest['LeaveRequests']['LEAVESTATUS'] : '';
                    $request['leave_from'] = isset($leaverequest['LeaveRequests']['FROMDATE']) ? $leaverequest['LeaveRequests']['FROMDATE'] : '';
                    $request['leave_to'] = isset($leaverequest['LeaveRequests']['TODATE']) ? $leaverequest['LeaveRequests']['TODATE'] : '';
                    $request['leave_applied_on'] = isset($leaverequest['LeaveRequests']['applied_date']) ? $leaverequest['LeaveRequests']['applied_date'] : '';
                    $request['fromhalf'] = isset($leaverequest['LeaveRequests']['FROMHALF']) ? $leaverequest['LeaveRequests']['FROMHALF'] : '';
                    $request['tohalf'] = isset($leaverequest['LeaveRequests']['TOHALF']) ? $leaverequest['LeaveRequests']['TOHALF'] : '';
                    $request['leavedays'] = isset($leaverequest['LeaveRequests']['leave_days']) ? $leaverequest['LeaveRequests']['leave_days'] : '';

                    $arr_leavesummary_for_template[$branch_code]['leaverequests'][] = $request;
                }
            }
        } else {
            //Parse array for simple report
            $arr_leavesummary_for_template['leaverequests'] = array();
            foreach ($arr_empleaverequests as $leaverequest) {
                $request = array(); 
                $request['emp_name'] = isset($leaverequest['Info']['EmpName']) ? $leaverequest['Info']['EmpName'] : '';
                $request['employee_id'] = isset($leaverequest['Info']['employee_id']) ? $leaverequest['Info']['employee_id'] : '';
                $request['branch'] = isset($leaverequest['Info']['branch']) ? $leaverequest['Info']['branch'] : '';
                $request['designation'] = isset($leaverequest['Info']['designation']) ? $leaverequest['Info']['designation'] : '';
                $request['department'] = isset($leaverequest['Info']['department']) ? $leaverequest['Info']['department'] : '';
                $request['joining_date'] = isset($leaverequest['Info']['joining_date']) ? $leaverequest['Info']['joining_date'] : '';
                $request['leave_type'] = isset($leaverequest['SalaryHeadItems']['leave_type']) ? $leaverequest['SalaryHeadItems']['leave_type'] : '';
                $request['Authorized_name'] = isset($leaverequest['0']['Authorized_name']) ? $leaverequest['0']['Authorized_name'] : 'Admin';
                $request['Approved_name'] = isset($leaverequest['0']['Approved_name']) ? $leaverequest['0']['Approved_name'] : 'Admin';
                $request['leave_status'] = isset($leaverequest['LeaveRequests']['LEAVESTATUS']) ? $leaverequest['LeaveRequests']['LEAVESTATUS'] : '';
                $request['leave_from'] = isset($leaverequest['LeaveRequests']['FROMDATE']) ? $leaverequest['LeaveRequests']['FROMDATE'] : '';
                $request['leave_to'] = isset($leaverequest['LeaveRequests']['TODATE']) ? $leaverequest['LeaveRequests']['TODATE'] : '';
                $request['leave_applied_on'] = isset($leaverequest['LeaveRequests']['applied_date']) ? $leaverequest['LeaveRequests']['applied_date'] : '';
                $request['fromhalf'] = isset($leaverequest['LeaveRequests']['FROMHALF']) ? $leaverequest['LeaveRequests']['FROMHALF'] : '';
                $request['tohalf'] = isset($leaverequest['LeaveRequests']['TOHALF']) ? $leaverequest['LeaveRequests']['TOHALF'] : '';
                $request['leavedays'] = isset($leaverequest['LeaveRequests']['leave_days']) ? $leaverequest['LeaveRequests']['leave_days'] : '';

                $arr_leavesummary_for_template['leaverequests'][] = $request;
            }
        }

        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_leavesummary_for_template', $arr_leavesummary_for_template);

        $dates = $from .' - '.$to;
        
//        debug($arr_leavesummary_for_template);
        //Set informations needed for report 
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $dates);
        
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
                $html2pdf->Output('LeaveDetailedReports.pdf', 'D');
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
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Detailed Reports  ".$dates);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $columncount = 0;
                $rowcount = 2;
                if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {
                    foreach ($arr_leavesummary_for_template as $branch_code => $leavesummary) {



                        $branchname = $leavesummary['branch_name'] .' Branch';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $branchname);
                        $objPHPExcel->getActiveSheet()->mergeCells('A'. $rowcount . ':J' . $rowcount);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 2;

                        
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No . ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Id');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Applied Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'From Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 8, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'To Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 9, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Authorized By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 10, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Approved By');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 11, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Leave Type');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 12, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Leave Days');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 13, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Leave Status ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 14, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        
                        $rowcount = $rowcount + 1;
                        $arr_data = $leavesummary['leaverequests'];
                        if (count($arr_data) >= 0) {
                            $i = 1;
                            foreach ($arr_data as $val) {

                                $name = $val['emp_name'];
                                $applieddate = $val['leave_applied_on'];
                                $frm = ($val['fromhalf'] == '1')?$val['leave_from'] .' '. 'First Half':$val['leave_from'] .' '. 'Second Half';
                                $to = ($val['tohalf'] == '1')?$val['leave_to'] .' '. 'First Half':$val['leave_to'] .' '. 'Second Half';
                                $status = $val['leave_status'];
                                $type = $val['leave_type'];
                                $id = $val['employee_id'];
                                $clas = $val['designation'];
                                $authorized = $val['Authorized_name'];
                                $approved = $val['Approved_name'];
                                $join = $val['joining_date'];
                                $dept = $val['department'];
                                $unit = $val['branch'];
                                $leave_days = $val['leavedays'];

                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $i++ );
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $name);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $join);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $unit);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $dept);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $clas);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, $applieddate);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, $frm);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $to);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $authorized);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $approved);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $type);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $leave_days);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, $status);
                                $rowcount = $rowcount + 1;
                            }
                        } else {
                            $msg = 'No Report found under this';
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $msg);
                        }
                        $rowcount++;
                    }
                } else {

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No . ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Id');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Applied Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'From Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 8, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'To Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 9, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Authorized By');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 10, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Approved By');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 11, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Leave Type');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 12, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 13, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Leave Status ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 14, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    
                    $rowcount = $rowcount + 1;


                    $arr_data = $arr_leavesummary_for_template['leaverequests'];
                    if (count($arr_data) >= 0) {
                        $i = 1;
                        foreach ($arr_data as $val) {

                            
                            $name = $val['emp_name'];
                            $applieddate = $val['leave_applied_on'];
                            $frm = ($val['fromhalf'] == '1')?$val['leave_from'] .' '. 'First Half':$val['leave_from'] .' '. 'Second Half';
                            $to = ($val['tohalf'] == '1')?$val['leave_to'] .' '. 'First Half':$val['leave_to'] .' '. 'Second Half';
                            $status = $val['leave_status'];
                            $type = $val['leave_type'];
                            $id = $val['employee_id'];
                            $clas = $val['designation'];
                            $authorized = $val['Authorized_name'];
                            $approved = $val['Approved_name'];
                            $join = $val['joining_date'];
                            $dept = $val['department'];
                            $unit = $val['branch'];
                            $leave_days = $val['leavedays'];

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount), ($rowcount), $i++ );
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, $applieddate);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, $frm);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $to);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $authorized);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $approved);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $type);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $leave_days);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, $status);
                            $rowcount = $rowcount + 1;
                        }
                    } else {

                        $msg = 'No Report found under this';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $msg);
                    }
                }
                $objPHPExcel->getActiveSheet()->setTitle('Leave Detailed Report');
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
//leave balance report
    private function generateleavebalancereport($mode) {
      
        $arr_form_data = $_REQUEST;
        //   debug($mode);
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        //  debug($arr_form_data);
        //Build conditions based on criterias recieved
        //$fd=$arr_form_data['reportfrom'].' '.'00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        $report_month = $arr_form_data['reportfrom'];
        if($arr_form_data['reportfrom'])
        $from = date('Y', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        //  debug($to);
        // debug($from);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        
            //    debug($arr_leavepolicygroupids);
        
             if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
        }
        
        // debug($arr_leavepolicygroupids);
//  for($i=1;$i<=$int_criterias_count;$i++){
      $condition = 'and ed.status = 1';
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        { 
          
            $condition =  "and ed.status in('1','2')";
           
        }
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'SalaryHeadItems') {
                    
                    try{
                        $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . ' AS emp_name, info.*,sh.item AS leave_type,lp.alloted_leave_forthe_year,ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey,'
                            . "lp.salary_head_item_fkey, '$from') leavebalance,leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '$from') leavetaken from "
                            . 'emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                                join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= "1" )
                                join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
                                left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey and ecf.emp_fkey=ed.emp_pkey and  ecf.fin_year='.$from.')
                                join employee_info as info on (info.emp_pkey=ep.emp_fkey)
                                where ed.emp_pkey=ed.emp_pkey
                                and ed.branch_code=ed.branch_code
                                and lp.salary_head_item_fkey=' . $leavepolicygroupid . '  '. $condition .' ORDER BY ed.emp_pkey ');
                    } catch (Exception $ex) {

                    }
                    
                } else if ($arr_form_data['select-criteria1'] == 'Departments') {
                    try{
                        $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . " AS emp_name, info.*, sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') leavebalance,leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '$from') leavetaken from  emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)"
                            . 'join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                                join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
                                left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey and ecf.emp_fkey=ed.emp_pkey and  ecf.fin_year='.$from.')
                                join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                                where ed.emp_pkey=ed.emp_pkey
                                and ed.branch_code=ed.branch_code
                                '. $condition .'
                                and lp.salary_head_item_fkey=' . $leavepolicygroupid . ' $condition ORDER BY ed.emp_pkey ');
                    } catch (Exception $ex) {

                    }
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    try{
                        
                        $arr_empleaverequests = $this->LeaveRequests->query("SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, ' ', last_name)
                                 AS emp_name, info.*, sh.item AS leave_type,lp.alloted_leave_forthe_year,ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') 
                                leavebalance,leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '$from') leavetaken 
                                from  emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
                                 join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= '1' )
                                join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
                                join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                                left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey and ecf.emp_fkey=ed.emp_pkey and  ecf.fin_year='$from')
                                where ed.emp_pkey=$leavepolicygroupid
                                and ed.branch_code=ed.branch_code
                                $condition
                                and lp.salary_head_item_fkey=lp.salary_head_item_fkey $condition ORDER BY ed.emp_pkey ");
                    } catch (Exception $ex) {

                    }
                } 
                else {
                    try{
                        $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,Units.branch_name,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . " AS emp_name,  info.*,sh.item AS leave_type,lp.alloted_leave_forthe_year,ifnull(ecf.carry_forwarded,0) as carryforwarded,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') leavebalance,leave_taken_fn(ed.emp_pkey,lp.salary_head_item_fkey, '$from') leavetaken from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)"
                            . 'join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID and lp.status= 1 )
                                LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
                                left join emp_leave_balance_year as ecf on (ecf.salary_head_item_fkey=lp.salary_head_item_fkey and ecf.emp_fkey=ed.emp_pkey and  ecf.fin_year='.$from.')
                                join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
                                join employee_info as info on (info.emp_pkey=ed.emp_pkey)
                                where ed.emp_pkey=ed.emp_pkey
                                '. $condition .'
                                and ed.branch_code="' . $leavepolicygroupid . '"
                                and lp.salary_head_item_fkey=lp.salary_head_item_fkey ORDER BY ed.emp_pkey ');
                    } catch (Exception $ex) {

                    }
                }



                $arr_leavepolicydetails_for_template[] = array(
                    'summary' => isset($arr_empleaverequests)?$arr_empleaverequests:array(),
                );
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        
        // debug($resp_register);
        // debug($arr_leavepolicydetails_for_template);  
// debug($arr_leavepolicydetails_for_template);
        
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);

        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportleavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavebalance.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveBalance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
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
                $worksheet->mergeCells('A1:H1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $rowcount = 2;
                
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i = 0;
                    $arr_data = $value['summary'];
                    if (count($arr_data) > 0) {
                    $i += 1;
                    $le = 'Leave Balance Reports of ';
                    if ($cr == 'Departments') {
                        $dep = isset($value['summary']['0']['Departments']['dept_name']) ? $value['summary'][0]['Departments']['dept_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('A'.$rowcount.':H'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                        } else if ($cr == 'Units') {
                        $brn = isset($value['summary'][0]['Units']['branch_name']) ? $value['summary'][0]['Units']['branch_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $brn);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('A'.$rowcount.':H'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                        } else if ($cr == 'EmployeeDetails') {
                        $name = isset($value['summary'][0]['0']['emp_name']) ? $value['summary'][0]['0']['emp_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $name);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('A'.$rowcount.':H'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                        } else {
                        $type = isset($value['summary'][0]['sh']['leave_type']) ? $value['summary'][0]['sh']['leave_type'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $type);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $worksheet->mergeCells('A'.$rowcount.':H'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                        }

                    $rowcount = $rowcount + 1;
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No ');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Id');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 3, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 4, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 5, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 6, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Leave Type');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 7, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Allotted Leave For The year');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 8, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Carry Forwarded');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 9, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Leave Taken');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 10, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Leave Balance');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 11, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $rowcount = $rowcount + 1;
                    
                        foreach ($arr_data as $val) {
                            $leavetaken = $val['0']['leavetaken'];
                            $emp = isset($val['0']['emp_name'])?$val['0']['emp_name']:'';
                            $typ = isset($val['sh']['leave_type'])?$val['sh']['leave_type']:'';
                            $lp = isset($val['lp']['alloted_leave_forthe_year'])?round($val['lp']['alloted_leave_forthe_year'],1):0;
                            $levbalnce = isset($val['0']['leavebalance'])?round($val['0']['leavebalance'],1):0;
                            $id = isset($val['info']['employee_id'])?$val['info']['employee_id']:'';
                            $clas = isset($val['info']['designation'])?$val['info']['designation']:'';
                            $join = isset($val['info']['joining_date'])?$val['info']['joining_date']:'';
                            $dept = isset($val['info']['department'])?$val['info']['department']:'';
                            $unit = isset($val['info']['branch'])?$val['info']['branch']:'';
                            $carry = isset($val['0']['carryforwarded'])?$val['0']['carryforwarded']:0;
                            
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i++);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $emp);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $typ);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $lp);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $carry);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $leavetaken);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $levbalnce);
                            $rowcount ++;
                        }
                        $rowcount ++;
                    }
                }
                $objPHPExcel->getActiveSheet()->setTitle('Leave Balance');
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
                $this->render('reportleavebalance');
                break;
        }
    }

    private function generateleavebalancesummaryreport($mode) {
        $arr_form_data = $_REQUEST;
        //   debug($mode);
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y', strtotime($arr_form_data['reportfrom']));
        $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        //  debug($to);
        // debug($from);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            //    debug($arr_leavepolicygroupids);
        }
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'SalaryHeadItems') {
                    $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . ' AS emp_name, info.*,sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey,'
                            . "lp.salary_head_item_fkey, '$from') leavebalance from"
                            . 'emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)
join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ep.emp_fkey)
where ed.emp_pkey=ed.emp_pkey
and ed.branch_code=ed.branch_code
and lp.salary_head_item_fkey=' . $leavepolicygroupid . '');
                } else if ($arr_form_data['select-criteria1'] == 'Departments') {
                    $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . " AS emp_name, info.*, sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)"
                            . 'join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey=ed.emp_pkey
and ed.branch_code=ed.branch_code
and ed.status = 1
and lp.salary_head_item_fkey=' . $leavepolicygroupid . '');
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . " AS emp_name, info.*, sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)"
                            . 'join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey=' . $leavepolicygroupid . '
and ed.branch_code=ed.branch_code
and ed.status = 1
and lp.salary_head_item_fkey=lp.salary_head_item_fkey');
                } else {
                    $arr_empleaverequests = $this->LeaveRequests->query('SELECT ed.emp_pkey,Units.branch_name,ed.branch_code, CONCAT(first_name, " ", last_name)'
                            . " AS emp_name,  info.*,sh.item AS leave_type,lp.alloted_leave_forthe_year,leave_balance_inthe_year_fn(ed.emp_pkey, lp.salary_head_item_fkey, '$from') leavebalance from emp_details ed join emp_proff ep on (ed.emp_pkey=ep.emp_fkey)"
                            . 'join leavepolicy lp on (lp.LEAVEPOLICY_GROUP_ID= ep.LEAVEPOLICY_GROUP_ID)
LEFT JOIN branches Units on(Units.branch_code = ed.branch_code)
join salary_head_items sh on (sh.salary_head_item_pkey=lp.salary_head_item_fkey)
join employee_info as info on (info.emp_pkey=ed.emp_pkey)
where ed.emp_pkey=ed.emp_pkey
and ed.status = 1
and ed.branch_code="' . $leavepolicygroupid . '"
and lp.salary_head_item_fkey=lp.salary_head_item_fkey');
                }







                //debug($arr_empleaverequests);

                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_empleaverequests,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
//  debug($arr_leavepolicydetails_for_template);
        foreach ($arr_leavepolicydetails_for_template as $key => $value) {
           
        }
        // debug($resp_register);
        // debug($arr_leavepolicydetails_for_template);  
// debug($arr_leavepolicydetails_for_template);
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);

        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportleavebalance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavetakenummary.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveTaken.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Taken Report");
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
                $i = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i += 1;
                    $le = 'Leave Balance Reports of ';
                    if ($cr == 'Departments') {
                        $dep = isset($value['summary']['0']['Departments']['dept_name']) ? $value['summary'][0]['Departments']['dept_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    } else if ($cr == 'Units') {
                        $brn = isset($value['summary'][0]['Units']['branch_name']) ? $value['summary'][0]['Units']['branch_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $brn);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    } else if ($cr == 'EmployeeDetails') {
                        $name = isset($value['summary'][0]['0']['emp_name']) ? $value['summary'][0]['0']['emp_name'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $name);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    } else {
                        $type = isset($value['summary'][0]['sh']['leave_type']) ? $value['summary'][0]['sh']['leave_type'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $type);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    }

                    $rowcount = $rowcount + 1;
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
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
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'leave Type');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Allotted leave For The year');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Taken');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Leave Balance');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    $rowcount = $rowcount + 1;
                    $arr_data = $value['summary'];
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
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $emp);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $clas);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $typ);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $lp);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $leavetaken);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $levbalnce);
                            $rowcount ++;
                        }
                    } else {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No record Found');
                    }
                    $rowcount ++;
                }
                $objPHPExcel->getActiveSheet()->setTitle('Leave Taken');
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
                $this->render('reportleavebalancesummary');
                break;
        }
    }

    private function generatecompoffreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $to = date("Y-m-d", strtotime(date("Y-m-d", strtotime($from)) . " + 1 year"));
        
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
       
             if($str_criteria_item == ''){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            
        }
        try{
            $salary_head_pkey = $this->LeaveRequests->query("select salary_head_item_pkey from salary_head_items where occurance = 'COFF' ");
            $salary_head_pkey = isset($salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey'])?$salary_head_pkey['0']['salary_head_items']['salary_head_item_pkey']:0 ;
            
            
        } catch (Exception $ex) {

        } 
        
        
        
        
        $arr_leavepolicydetails_for_template = array();
        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    
                    
//                    $arr_empleaverequests = $this->LeaveRequests->query("select employee_info.*,emp_name,month_year,count(*) as compoff
//                        from attendance_register,fin_year fy left join employee_info on(employee_info.emp_pkey = '$leavepolicygroupid')
//                         where (FIELD1='COFF' OR FIELD2='COFF' OR FIELD3='COFF' OR FIELD4='COFF' 
//                         OR FIELD5='COFF' OR FIELD6='COFF' OR FIELD7='COFF' OR FIELD8='COFF' OR 
//                         FIELD9='COFF' OR FIELD10='COFF' OR FIELD11='COFF' OR FIELD12='COFF' OR 
//                         FIELD13='COFF' OR FIELD14='COFF' OR FIELD15='COFF' OR FIELD16='COFF' OR 
//                         FIELD17='COFF' OR FIELD18='COFF' OR FIELD19='COFF' OR FIELD20='COFF' OR 
//                         FIELD21='COFF' OR FIELD22='COFF' OR FIELD23='COFF' OR FIELD24='COFF' OR 
//                         FIELD25='COFF' OR FIELD26='COFF' OR FIELD27='COFF' OR FIELD28='COFF' OR 
//                         FIELD29='COFF' OR FIELD30='COFF' OR FIELD31='COFF' OR FIELD32='COFF')
//                        and isdelete='N' and concat(month_year,'-01') between fy.start_month and 
//                        end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and attendance_register.emp_fkey = '$leavepolicygroupid' group by emp_name,month_year");
//
//                    $arr_empleave_eligiility = $this->LeaveRequests->query("select emp_pkey ,yearmonth ,count(*) as eligibility from
//                        emp_detail_timeattandance, fin_year fy where (weekoff is not null or holiday is not null) and present='P/P'
//                        and yearmonth between fy.start_month and end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and emp_pkey = '$leavepolicygroupid'
//                         group by emp_pkey ,yearmonth 
//                        union all
//                        select emp_pkey ,yearmonth ,count(*)*.5 from
//                        emp_detail_timeattandance , fin_year fy where (weekoff is not null or holiday is not null) and present in('P/A','A/P')
//                        and yearmonth between fy.start_month and end_month and fy.Year_status='OPEN' and fy.vattr1 = 0 and emp_pkey = '$leavepolicygroupid'
//                        group by emp_pkey ,yearmonth");
                    
                    /* @var $arr_empleave_eligiility type */
                    $arr_emps_dets = $this->LeaveRequests->query("SELECT employee_info.*
FROM `employee_info`
WHERE `emp_pkey` = '$leavepolicygroupid'
LIMIT 50 ");
                    try{
                        $arr_empleaverequests = $this->LeaveRequests->query("select * from emp_detail_timeattandance where  att_date between '$from' and '$to' and emp_pkey = '$leavepolicygroupid' and (weekoff != '' or holiday != '') and weekoff != '/WO' and duration != '' and emp_pkey in (select emp_fkey from emp_proff where day_time_seq in (select day_time_seq from working_day_time_procedures where work_time_day_off_cal_ot	 = '2' ))");
                        
                    } catch (Exception $ex) {

                    }
                    
                    $year = date("Y",  strtotime($from));
                    try{
                        $arr_empleave_eligiility = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$leavepolicygroupid','$salary_head_pkey','$year') as blnce ");
                        
                    } catch (Exception $ex) {

                    }
                    
                    
                } else {
                    $arr_empleaverequests = $this->LeaveRequests->query("select * from leaveentries where salary_head_item_fkey = $salary_head_pkey and EMP_fkey = '$leavepolicygroupid' " );
                }
                
                
                
                $arr_empleavetaken = $this->LeaveRequests->query("select LEAVEENTRYID,salary_head_item_fkey,applied_date,LEAVESTATUS,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,concat(ed.first_name,' ',ed.last_name) applied_name,Autherized_date,APPROVED_date,contact_person,contact_No,Reason,REMARKS,leave_days,(select concat(first_name,' ',last_name) from emp_details where emp_pkey = leaveentries.ISAutherizedby) as Authorized_name,(SELECT CONCAT(first_name,'',last_name) from emp_details where emp_pkey = leaveentries.APPROVEDBY) approved_name from leaveentries
left join emp_details ed on (ed.emp_pkey = leaveentries.EMP_fkey)
where leaveentries.salary_head_item_fkey = '$salary_head_pkey' and EMP_fkey = '$leavepolicygroupid' and TODATE between '$from' and '$to'  " ); 
                
                
                $arr_leavepolicydetails_for_template[] = array(
                    'emp_dets'=>$arr_emps_dets,
                    'summary' => $arr_empleaverequests,
                    'eligibility' => $arr_empleave_eligiility,
                    'leaves' => $arr_empleavetaken
                );
            }
        } else {
            echo "<div style='color:red' ><h3>No record Found</h3></div>";
            die();
        }
        
//        debug($arr_leavepolicydetails_for_template);
        
        $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);

        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('compoff');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('reportleavecompoffssummary.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveTaken.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Comp Off Report ");
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
                $rowcount = 3;
                $i = 0;
                
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    if (!empty($value['summary'])) {
                        $i += 1;
                        $le = 'Leave Comp Off Report of ';
                        $dep = isset($value['emp_dets']['0']['employee_info']['EmpName']) ? $value['emp_dets']['0']['employee_info']['EmpName'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $le . $dep);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                        
                        $worksheet->mergeCells('A'.$rowcount.':K'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        
                        $arr_data = $value['summary'];
                        $emp_dets = $value['emp_dets'];
                        
                        $columncount = 0;
                        $rowcount++;
                        $rowcount++;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Details ');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                        $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
                        $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        
                        $rowcount = $rowcount + 2;
                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Id');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $rowcount = $rowcount + 1;
                        $arr_data = $value['summary'];
                        
                            $emp = $emp_dets['0']['employee_info']['EmpName'];
                            $id = $emp_dets['0']['employee_info']['employee_id'];
                            $clas = $emp_dets['0']['employee_info']['designation'];
                            $join = $emp_dets['0']['employee_info']['joining_date'];
                            $dept = $emp_dets['0']['employee_info']['department'];
                            $unit = $emp_dets['0']['employee_info']['branch'];
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $emp);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $unit);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $clas);
                            $rowcount ++;
                            
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Compensatory Accrued Details ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                            $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                        
                            $rowcount = $rowcount + 2;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No.  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Accrued Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Duration');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Day Type');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                            $rowcount = $rowcount + 1;   
                            
                            
                            
                        if (count($arr_data) > 0) {
                            $used = 0;
                            foreach ($arr_data as $val) {
                                $used = $used + 1;
                                $att_date = $val['emp_detail_timeattandance']['att_date'];
                                $duration = $val['emp_detail_timeattandance']['duration'];
                                $dys = $val['emp_detail_timeattandance']['weekoff'] . ' ' . $val['emp_detail_timeattandance']['holiday'];
                                
                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $used);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $att_date);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $duration);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $dys);
                                $rowcount ++;
                            }
                            
                            $balance_levv = isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 0), $rowcount, 'Available Comp Off Leave Balance : '.$balance_levv);
                            
                            $worksheet->mergeCells('A'.$rowcount.':K'.$rowcount);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 0), $rowcount)->getFont()->setBold(true);
                            $rowcount++;
                            
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Record Found');
                        }
                        
                        $arr_data = $value['leaves'];
                
                        
                            $rowcount++;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Comp Off Leave List ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                            $worksheet->mergeCells('A'.$rowcount.':D'.$rowcount);
                            $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                            );
                            
                            
                            $rowcount = $rowcount + 1;
                            $columncount = 0;
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No.  ');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'From Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'To Date');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Status');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                            $rowcount = $rowcount + 2;   
                            
                        if (count($arr_data) > 0) {
                            $used = 0;
                            foreach ($arr_data as $val) {
                                $used = $used + 1;
                                $att_date = $val['leaveentries']['FROMDATE'] . ' ' . $val['leaveentries']['FROMHALF'];
                                $duration = $val['leaveentries']['TODATE'] . ' ' . $val['leaveentries']['TOHALF'];
                                $dys = $val['leaveentries']['LEAVESTATUS'];
                                
                                $columncount = 0;
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $used);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $att_date);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $duration);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $dys);
                                $rowcount ++;
                            }
                            
                            $balance_levv = isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 0), $rowcount, 'Available Comp off Leave Balance ');
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, $balance_levv );
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                            $rowcount ++;
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Record Found');
                        }
                        
                        $rowcount ++;
                        $rowcount ++;
                    }
                }

                
                
                $objPHPExcel->getActiveSheet()->setTitle('Leave Compoff Report ');
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
                $this->render('compoff');
                break;
        }
    }

}
