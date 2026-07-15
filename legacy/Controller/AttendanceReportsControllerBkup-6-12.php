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
ini_set('max_execution_time', 30000);

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class attendanceReportsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'AttendanceReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Attendance', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'DbConfig', 'MobileUserauditor', 'CompanyContactInfo'); //santhu
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
            //'employee' => 'Employee Information',
            'Attendance' => 'Attendance Register Reports',
            'VerifiedAttendance' => 'Verified Attendance Register Reports',
            // 'Simple Attendance' =>  'Simple Attendance Reports',
//            'TimeAttendance' => 'Time Attendance Reports',
            'DetailedAttendance' => 'Detailed Attendance Reports',
            //'AttendanceRep' => 'Attendance Reports',
            //'MobilelocationRep' => 'Mobile User Location Reports',
            'Overtime' => 'Approved Overtime Reports',
                'Dashboard' => 'Employee Check-in/out logs'
//arun
//            'GrossSalary' => 'Gross Salary Report',
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
        // debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'employee':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Attendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                
                case 'VerifiedAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'AttendanceRep':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'DetailedAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //santhu
                case 'TimeAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //Sanju
                case 'Dashboard':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'MobilelocationRep':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Overtime':
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

            $arr_order = array();
            if ($model == 'Departments') {
                $arr_order = array("Departments.dept_name" => "ASC");
                $conditions = array("status" => 1);
            } else if ($model == 'Grades') {
                $arr_order = array("Grades.grade_name" => "ASC");
                $conditions = array("status" => 1);
            } else if ($model == 'Verticals') {
                $arr_order = array("Verticals.vertical_name" => "ASC");
                $conditions = array("status" => 1);
            } else if ($model == 'Units') {
                $arr_order = array("Units.branch_name" => "ASC");
                $conditions = array("status" => 1);
            } else if ($model == 'DayTimeProcedures') {
                $arr_order = array("DayTimeProcedures.day_time_desc" => "ASC");
                $conditions = array("active" => 1);
            } else if ($model == 'LeavePolicyGroup') {
                $arr_order = array("LeavePolicyGroup.LEAVEPOLICY_GROUP_NAME" => "ASC");
                $conditions = array("status" => 1);
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
                        'conditions' => $conditions,
                        'order' => $arr_order
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
//debug($mode);
        switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
            case 'Attendance':
                $this->generatesummaryreport($mode);
                break;
            case 'VerifiedAttendance':
                $this->generateVerifiedAttendancereport($mode);
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
            case 'Dashboard':
                $this->generateCheckinlogsReport($type, $mode);
                break;
            case 'Overtime':
                $this->Overtimereport($type, $mode);
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
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
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

    private function generateattendancereport($mode) {
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
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);
        //  debug($arr_dates);
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
                $arr_attendance_register_entries = $this->AttendanceRegisterReport->query(" (SELECT AttendanceRegisterReport.company_code,"
                        . "AttendanceRegisterReport.branch_code,info.*,registerid,emp_fkey,month_year,emp_company_id,emp_name,FIELD1,FIELD2,FIELD3,FIELD4,FIELD5,FIELD6,FIELD7,FIELD8,FIELD9,FIELD10,FIELD11,FIELD12,FIELD13,FIELD14,FIELD15,FIELD16,FIELD17,FIELD18,FIELD19,FIELD20,FIELD21,FIELD22,FIELD23,FIELD24,FIELD25,FIELD26,FIELD27,FIELD28,FIELD29,FIELD30,FIELD31,FIELD32,isdelete,record_status,userid,id,branch_name,address,city,state,pincode,status,deleted "
                        . "FROM attendance_register_rep AS AttendanceRegisterReport "
                        . "left join branches as branchs on (branchs.branch_code = AttendanceRegisterReport.branch_code) "
                        . "left join employee_info as info on (info.emp_pkey = emp_fkey) "
                        . "where userid = '$rand'  and month_year='$month' ) "
                        . "union all (SELECT AttendanceRegister.company_code,AttendanceRegister.branch_code,info.*,registerid,emp_fkey,month_year,emp_company_id,emp_name,FIELD1,FIELD2,FIELD3,FIELD4,FIELD5,FIELD6,FIELD7,FIELD8,FIELD9,FIELD10,FIELD11,FIELD12,FIELD13,FIELD14,FIELD15,FIELD16,FIELD17,FIELD18,FIELD19,FIELD20,FIELD21,FIELD22,FIELD23,FIELD24,FIELD25,FIELD26,FIELD27,FIELD28,FIELD29,FIELD30,FIELD31,FIELD32,isdelete,record_status,userid,id,branch_name,address,city,state,pincode,status,deleted "
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
            case 'pdf' :
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
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Attendance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

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
                $worksheet->mergeCells('A1:Ai1');
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
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Attendance Reports of ' . $branch . ' For the month ' . $month);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                    $rowcount = 3;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee Name');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Designation');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Date Of joining');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Department');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
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

                    $arr_data = $value['summary'];                        // debug($arr_data);die();
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                    if (count($arr_data) >= 0) {
                        $k = 1;
                        foreach ($arr_data as $key => $val) {
                            $columnindex = 0;
                            $name = $val[0]['EmpName'];
                            //$name=$name.$key;
                            $present = $val[0]['days_present'];
                            $leave = $val[0]['days_leave'];
                            $holidays = $val[0]['days_holidays'];
                            $id = $val[0]['employee_id'];
                            $des = $val[0]['designation'];
                            $join = $val[0]['joining_date'];
                            $department = $val[0]['department'];
                            $branch = $val[0]['branch'];
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $des);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $department);
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $branch);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $present);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $leave);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $holidays);

                            $columnindex = 4;
                            foreach ($arr_dates as $key => $date) {
                                $newIndex = 'FIELD' . ($key + 1);
                                $dta = $val[0][$newIndex];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }

                            $rowcount++;
                            $k++;
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
                $this->render('reportsattendance');
                break;
        }
    }
//attendance register report
    private function generatesummaryreport($mode) {
        $arr_form_data = $_REQUEST;
        
        //debug($arr_form_data);
//       if($arr_form_data['select-criteria1']!='Units')
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

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
        
            if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
              echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->AttendanceRegister->query("select UCASE(ifnull(occurance,'LOP')) AS abbr from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        $arr_leavetype = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr'])."/".strtoupper($leave[0]['abbr']);
            $arr_leavetype[] = strtoupper($leave[0]['abbr']);
        }
        //debug($arr_leaveabbr);
        $query = "CALL insert_update_att_reg('$company_code','NULL','$user_id','$month');";
        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                $fields = 'AttendanceRegister.*,Branch.branch_name,Info.*';

                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.branch_code = Branch.branch_code'
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
                    )
                );
                $conditions = array();
                if ($arr_form_data['hidden-criteria1'] == "Units") {
                    $conditions[] = 'AttendanceRegister.branch_code="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"  ';
                } else {
                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"  ';
                }
                if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1'){
                     
                    $conditions[] = "EmployeeDetails.status in('1','2')";  
                 }
                 else{
                     $conditions[] = "EmployeeDetails.status = '1' ";  
                 }
                $arr_leavepolicy_details = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, "order" => "Info.EmpName"));
                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
            function getcounts($input,$arr_datas){
                //debug($arr_datas);
//                $input = preg_quote('/A', '~'); // don't forget to quote input string!
                $result = preg_grep('~' . $input . '~', $arr_datas);
                return count($result); 
            }
            
            foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                foreach ($value['summary'] as $ky => $vaal) {
                    $int_days_leave = 0;
                    $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P/P")) + count(array_keys($vaal["AttendanceRegister"], "P/A"))/2 + count(array_keys($vaal["AttendanceRegister"], "A/P"))/2 + count(array_keys($vaal["AttendanceRegister"], "WFH"));
                    
                    foreach ($arr_leavetype as $leaves){
//                        $inp = isset($leave['0']['abbr'])?$leave['0']['abbr']:NULL;
//                        debug($leaves);
                        $int_days_leave +=  getcounts(preg_quote("$leaves/", '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote("/$leaves", '~'),$vaal["AttendanceRegister"])/2 ;
                    }
                    $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "WO"));
                    $int_days_LOPs = getcounts(preg_quote("LOP/", '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote("/LOP", '~'),$vaal["AttendanceRegister"])/2 + count(array_keys($vaal["AttendanceRegister"], "LOP"));
                    $int_days_holidays_holi = count(array_keys($vaal["AttendanceRegister"], "HO"));
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = getcounts(preg_quote('P/', '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote('/P', '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote('WFH/', '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote('/WFH', '~'),$vaal["AttendanceRegister"])/2 + count(array_keys($vaal["AttendanceRegister"], "WFH")) - getcounts(preg_quote("LOP/", '~'),$vaal["AttendanceRegister"])/2;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $int_days_LOPs;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $int_days_holidays_holi;
                }
            }
            
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month",$report_month);
            switch ($mode) {
                case 'pdf' :
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('reportsummary');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('reportsummary.pdf', 'D');


                    // $this->render('reportsummary');                
                    break;
                case 'excel' :

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_attendane.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
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
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Week Off');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Holidays');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'LOP');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Present Days');
                    //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					//$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                    //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                    $columnindex = $columncount + 12;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                        $columnindex++;
                    }
                    $rowcount = 3;
                    //$rowcount1=3;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $branch = isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';
                        //echo $branch;die();                     
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');

                        $arr_data = $value['summary'];
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        if (count($arr_data) >= 0) {
                            $k = 1;
                            foreach ($arr_data as $key => $val) {
                                $columnindex = 0;
                                $name = $val['Info']['EmpName'];

                                //$name = $val['AttendanceRegister']['emp_name'];
                                //$name=$name.$key;
                                $present = $val['AttendanceRegister']['days_present'];
                                $leave = $val['AttendanceRegister']['days_leave'];
                                $holidays = $val['AttendanceRegister']['days_holidays'];
                                $holi = $val['AttendanceRegister']['HO'];
                                $lop = $val['AttendanceRegister']['lops'];
                                $id = $val['Info']['employee_id'];
                                $des = $val['Info']['designation'];
                                $join = $val['Info']['joining_date'];
                                $department = $val['Info']['department'];
                                $branch = $val['Info']['branch'];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $des);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $present);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $leave);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $holidays);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $holi);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $lop);

                                $columnindex = $columnindex + 12;
                                foreach ($arr_dates as $key => $date) {
                                    $newIndex = 'FIELD' . ($key + 1);
                                    $dta = $val['AttendanceRegister'][$newIndex];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                    $columnindex++;
                                }
                                $k++;
                                $rowcount++;
                            }
                        }
                        $rowcount1 = $rowcount + 1;
                    }

                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Report ');
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
                    $this->render('reportsummary');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }
    
    private function generateVerifiedAttendancereport($mode) {
        $arr_form_data = $_REQUEST;
        

        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

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
        
             if($str_criteria_item == ''){
               echo "<h1>No Criteria Selected</h1>";
                die();
            }
            
            if(!isset($arr_form_data[$str_criteria_item])){
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->AttendanceRegister->query("select UCASE(ifnull(occurance,'LOP')) AS abbr from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        $arr_leavetype = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr'])."/".strtoupper($leave[0]['abbr']);
            $arr_leavetype[] = strtoupper($leave[0]['abbr']);
        }
        //debug($arr_leaveabbr);
        $query = "CALL insert_update_att_reg('$company_code','NULL','$user_id','$month');";
        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                $fields = 'AttendanceRegister.*,Branch.branch_name,Info.*';

                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'AttendanceRegister.branch_code = Branch.branch_code'
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
                    )
                );
                $conditions = array("isdelete"=>"N");
                if ($arr_form_data['hidden-criteria1'] == "Units") {
                    $conditions[] = 'AttendanceRegister.branch_code="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"  ';
                } else {
                    $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and AttendanceRegister.month_year = "' . $report_month . '"';
                }
                 if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1'){
                     
                    $conditions[] = "EmployeeDetails.status in('1','2')";  
                 }
                 else{
                     $conditions[] = "EmployeeDetails.status ='1' ";
                 }
                $arr_leavepolicy_details = $this->AttendanceRegister->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, "order" => "Info.EmpName"));
                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
            function getcounts($input,$arr_datas){
                $result = preg_grep('~' . $input . '~', $arr_datas);
                return count($result); 
            }
            
            foreach ($arr_leavepolicydetails_for_template as $key => $value) {
                foreach ($value['summary'] as $ky => $vaal) {
                    $int_days_leave = 0;
                    $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P/P")) + count(array_keys($vaal["AttendanceRegister"], "P/A"))/2 + count(array_keys($vaal["AttendanceRegister"], "A/P"))/2 + count(array_keys($vaal["AttendanceRegister"], "WFH"));
                    
                    foreach ($arr_leavetype as $leaves){
                        $int_days_leave +=  getcounts(preg_quote("$leaves/", '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote("/$leaves", '~'),$vaal["AttendanceRegister"])/2 ;
                    }
                    $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "WO"));
                    $int_days_LOPs = getcounts(preg_quote("LOP/", '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote("/LOP", '~'),$vaal["AttendanceRegister"])/2 + count(array_keys($vaal["AttendanceRegister"], "LOP"));
                    $int_days_holidays_holi = count(array_keys($vaal["AttendanceRegister"], "HO"));
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = getcounts(preg_quote('P/', '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote('/P', '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote('WFH/', '~'),$vaal["AttendanceRegister"])/2 + getcounts(preg_quote('/WFH', '~'),$vaal["AttendanceRegister"])/2 + count(array_keys($vaal["AttendanceRegister"], "WFH")) - getcounts(preg_quote("LOP/", '~'),$vaal["AttendanceRegister"])/2;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['lops'] = $int_days_LOPs;
                    $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['HO'] = $int_days_holidays_holi;
                }
            }
            
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month",$report_month);
            switch ($mode) {
                case 'pdf' :
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('reportsummary');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('reportsummary.pdf', 'D');


                    // $this->render('reportsummary');                
                    break;
                case 'excel' :

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_attendane.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
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
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Week Off');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Holidays');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'LOP');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Present Days');
                    //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					//$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                    //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                    $columnindex = $columncount + 12;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                        $columnindex++;
                    }
                    $rowcount = 3;
                    //$rowcount1=3;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $branch = isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';
                        //echo $branch;die();                     
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');

                        $arr_data = $value['summary'];
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        if (count($arr_data) >= 0) {
                            $k = 1;
                            foreach ($arr_data as $key => $val) {
                                $columnindex = 0;
                                $name = $val['Info']['EmpName'];

                                //$name = $val['AttendanceRegister']['emp_name'];
                                //$name=$name.$key;
                                $present = $val['AttendanceRegister']['days_present'];
                                $leave = $val['AttendanceRegister']['days_leave'];
                                $holidays = $val['AttendanceRegister']['days_holidays'];
                                $holi = $val['AttendanceRegister']['HO'];
                                $lop = $val['AttendanceRegister']['lops'];
                                $id = $val['Info']['employee_id'];
                                $des = $val['Info']['designation'];
                                $join = $val['Info']['joining_date'];
                                $department = $val['Info']['department'];
                                $branch = $val['Info']['branch'];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $des);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $present);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $leave);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $holidays);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $holi);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $lop);

                                $columnindex = $columnindex + 12;
                                foreach ($arr_dates as $key => $date) {
                                    $newIndex = 'FIELD' . ($key + 1);
                                    $dta = $val['AttendanceRegister'][$newIndex];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                    $columnindex++;
                                }
                                $k++;
                                $rowcount++;
                            }
                        }
                        $rowcount1 = $rowcount + 1;
                    }

                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Register');
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
                    $this->render('reportsummary');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

    private function generateDetailedreport($mode) {
        $arr_form_data = $_REQUEST;
        //  debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t H:m:s', strtotime($arr_form_data['reportfrom']. ' ' . '23:00:00' ));
        }


 

        $arr_leavepolicygroupids = array();
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

          $condition = 'and  EmployeeDetails.status = 1';
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        {
            $condition =  "and  EmployeeDetails.status in('1','2')";
           
        }
        $arr_leavepolicydetails_for_template = array();
        //  debug($arr_leavepolicygroupids);
        $arr_DetaildAttendance = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
//            $str_conditions = ' WHERE Attendance.branch_code="'.$leavepolicygroupid.'" and intime between "'.$fd.'" and "'.$Td.'";';
//            $arr_leavepolicy_details = $this->AttendanceRegister->query(''
//                    . 'SELECT '
//                    . '*'
//                    . 'FROM '
//                    . '`client_db1`.`Attandance` AS `Attendance` ' 
//                     .$str_conditions)
                //  debug($leavepolicygroupid);
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,"
                            . "`DeviceAttendance`.C2,`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`info`.*,"
                            . "`department`.`dept_name`,`empproff`.emp_dept FROM `device_attandance` AS `DeviceAttendance`"
                            . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`)"
                            . " LEFT JOIN `emp_proff` AS `empproff` ON (`empproff`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`) "
                            . "LEFT JOIN `department` AS `department` ON (`department`.`dept_code` = `empproff`.`emp_dept`)"
                            . "LEFT JOIN `employee_info` AS `info` ON (`info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                            . " WHERE DeviceAttendance.status = 'Y' $condition and `LOGDATE` between '$from' and '$to' and EmployeeDetails.emp_pkey='$leavepolicygroupid' "
                            . "ORDER BY  `EmployeeDetails`.`emp_id`,LOGDATE");
                } else {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,`DeviceAttendance`.C2,"
                            . "`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`branches`.branch_name,`info`.*"
                            . " FROM `device_attandance` AS `DeviceAttendance`"
                            . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`)"
                            . " LEFT JOIN `branches` AS `branches` ON (`DeviceAttendance`.`branch_code` = `branches`.`branch_code`) "
                            . "LEFT JOIN `employee_info` AS `info` ON (`info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                            . "WHERE DeviceAttendance.status = 'Y' $condition and `LOGDATE` between '$from' and '$to' and DeviceAttendance.branch_code='$leavepolicygroupid'"
                            . " ORDER BY `EmployeeDetails`.`emp_id`,LOGDATE");
                }


                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
           //debug($arr_leavepolicydetails_for_template);
            $arr_dates = array();
           foreach($arr_leavepolicydetails_for_template as $val)
           {
               //debug($val);
               
               foreach($val['summary'] as $dates) {
                   $date = date('Y-m-d',strtotime($dates['DeviceAttendance']['LOGDATE']));
                   $emp = $dates['info']['emp_pkey'];
                   $branch = $dates['info']['branch'];
                   //$arr_dates[$branch][$emp]['Name'] = $dates['Info'];
                   $arr_dates[$branch][$emp][$date][] = $dates;
                   
                }
                //$arr_DetaildAttendance[] = $arr_dates;
           }
           //debug($arr_dates);
            $this->set('arr_leavepolicydetails_for_template', $arr_dates);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($this_month_att);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('detailedattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('AttendanceDetailed.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Detailedattendance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employees Detailed Attendance  Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 3;
                $i = 0;
                $border_style1= array('borders' => array('top' => array('style' => 
PHPExcel_Style_Border::BORDER_THICK,'color' => array('argb' => '766f6e'),)));
                ////debug($arr_dates);
                foreach ($arr_dates as $branch=> $employees) {
                    
                    $i += 1;
                    //$arr_daata = $value['summary'];
                    
                    
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Attendance  Details of ' . $branch);
                    
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(14);
                    $worksheet->mergeCells("A".($rowcount).":C".($rowcount));
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $rowcount = $rowcount + 2;
                    foreach ($employees as $employee =>$date)
                        {
                        $info =  current($date);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'Employee NAME :'.$info['0']['info']['EmpName']);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), 'Employee ID : '.$info['0']['info']['employee_id']);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Branch Name : '.$info['0']['info']['branch']);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Department :'.$info['0']['info']['department']);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation :'.$info['0']['info']['designation']);
                    for ($i = 0; $i <= 8; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }
                    $worksheet->getStyle("A".($rowcount).":F".($rowcount+1))->applyFromArray($border_style1);
                                
                    $columnindex = 1;
                    // 
                    //$arr_data = $value['summary'];
                    if (count($date) > 0) {
                        $rowcount = $rowcount + 3;
                        $k=1;
                        $border_style= array('borders' => array('bottom' => array('style' => 
PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '766f6e'),)));
                        
                        foreach ($date as $key => $val) {
                                //debug($val);
                                $col3 = 2;
                                $col4 = 3;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), date("M d",strtotime($key)));$worksheet->mergeCells("A".($rowcount).":A".($rowcount+1));$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((0), ($rowcount))->getFont()->setBold(true);
                                foreach ($val as $value) {
                                    //debug($value);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex), ($rowcount), $value['DeviceAttendance']['C1']);
                                    $columnindex++;
                                }
                                $worksheet->getStyle("A".($rowcount).":F".($rowcount+1))->applyFromArray($border_style);
                                $columnindex = 1;
                                $rowcount++;
                                foreach($val as $value)
                                {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex), ($rowcount), $value['DeviceAttendance']['LOGDATE']);
                                    $columnindex++;
                                }
                                $columnindex = 1;
                                $rowcount++;
                                $k++;
                            }
                        } else {
                        $msg = 'No employees found under this shift';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . ($rowcount), $msg);
                    }
                    $rowcount++;
                }
                }
        
                $objPHPExcel->getActiveSheet()->setTitle('Detailed Attendance Report');
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
                $this->render('detailedattendance');
                break;
        }
    }

    //santhu
    private function generatetimeattendancereport($type = '', $mode = '') {
        $arr_form_data = $_REQUEST;
 
        $arr_registerentry_heads = array(
            'P' => 'Present',
            'L' => 'Leave',
            'WO' => 'Week Off',
            'HO' => 'Holiday',
            'A' => 'Absent',
            'LOP' => 'Loss Of Pay',
            'OTHERS' => 'Others'
        );
        $this->set('arr_registerentry_heads', $arr_registerentry_heads);

        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        //$fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            //$from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            //$to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }

        $this->set("report_month", $report_month);
        $yearmonth = date('Y-m-1', strtotime($report_month));

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        //debug($arr_form_data);
        $str_criteria_item1 = $arr_form_data['hidden-criteria1'];
        if (isset($arr_form_data[$str_criteria_item1]) > 0) {
            for ($i = 1; $i <= $int_criterias_count; $i++) {
                $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
                $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("fields" => "reportcriteria, reportcriteria_field", "conditions" => array("status" => 1, 'reporttype' => 'TimeAttendance', 'reportcriteria' => $str_criteria_item))));
                //debug($arr_reportcriterias);
                if (isset($arr_reportcriterias[0]['reportcriteria_field'])) {
                    $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                }
                //$arr_leavepolicygroupids = $arr_form_data[$str_criteria_item];
                $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
                $arr_leavepolicygroupids = $crit;

                //debug($arr_leavepolicygroupids);
            }
            foreach ($arr_leavepolicygroupids as $branches) {
                $attend = $this->EmployeeDetails->query("select time_duration_check('$yearmonth','NULL','$branches')");
            }
            $str_conditions = implode(' AND ', $conditions);
            $query_timeattendance = "select EmployeeDetails.first_name,EmployeeDetails.last_name,emp.emp_company_id,Units.*,emp_detail_timeattandance.*,wd.minuts_calc_perday,emp.emp_fkey,emp.emp_company_id "
                    . "from emp_detail_timeattandance "
                    . "left join emp_details as EmployeeDetails on(EmployeeDetails.emp_pkey = emp_detail_timeattandance.emp_pkey)"
                    . " left join emp_proff as emp on (emp.emp_fkey = EmployeeDetails.emp_pkey) "
                    . "left join working_day_time_procedures as wd on (wd.day_time_seq = emp.day_time_seq) "
                    . "left join branches as Units on(Units.branch_code = EmployeeDetails.branch_code) "
                    . "where EmployeeDetails.status=1 "
                    . "and  $str_conditions "
                    . "and yearmonth = '$yearmonth' order by EmployeeDetails.first_name,att_date";
            $arr_timeattendances = $this->DeviceAttendance->query($query_timeattendance);
            //debug($arr_timeattendances);
            $arr_branch_details = array();
        }
        $arr_timeattendances_for_template = array();
        if (!empty($arr_timeattendances)) {
            $arr_dates = array();
            foreach ($arr_timeattendances as $timeattendance) {

                $branch_code = isset($timeattendance['Units']['branch_code']) ? $timeattendance['Units']['branch_code'] : 0;
                if (!isset($arr_branch_details[$branch_code])) {
                    $arr_branch_details[$branch_code] = isset($timeattendance['Units']) ? $timeattendance['Units'] : array();
                }

                if (!isset($arr_timeattendances_for_template[$branch_code])) {
                    $arr_timeattendances_for_template[$branch_code] = array();
                }

                $emp_pkey = isset($timeattendance['emp_detail_timeattandance']['emp_pkey']) ? $timeattendance['emp_detail_timeattandance']['emp_pkey'] : 0;
                if ($emp_pkey) {
                    if (!isset($arr_timeattendances_for_template[$branch_code][$emp_pkey]['employeeinfo'])) {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['employeeinfo'] = isset($timeattendance['EmployeeDetails']) ? $timeattendance['EmployeeDetails'] : array();
                    }
                    if (!isset($arr_timeattendances_for_template[$branch_code][$emp_pkey]['emp'])) {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['emp'] = isset($timeattendance['emp']) ? $timeattendance['emp'] : array();
                    }
                    $att_date = isset($timeattendance['emp_detail_timeattandance']['att_date']) ? $timeattendance['emp_detail_timeattandance']['att_date'] : '';
                    $att_day = date('Y-m-d', strtotime($att_date));
                    if (!in_array($att_day, array_keys($arr_dates))) {
                        $arr_dates[$att_day] = $att_day;
                    }

                    $present = isset($timeattendance['emp_detail_timeattandance']['present']) ? $timeattendance['emp_detail_timeattandance']['present'] . ' ' : '';
                    $leaves = isset($timeattendance['emp_detail_timeattandance']['leaves']) ? $timeattendance['emp_detail_timeattandance']['leaves'] . ' ' : '';
                    $weekoff = isset($timeattendance['emp_detail_timeattandance']['weekoff']) ? $timeattendance['emp_detail_timeattandance']['weekoff'] . ' ' : '';
                    $holiday = isset($timeattendance['emp_detail_timeattandance']['holiday']) ? $timeattendance['emp_detail_timeattandance']['holiday'] . ' ' : '';
                    $others = isset($timeattendance['emp_detail_timeattandance']['others']) ? $timeattendance['emp_detail_timeattandance']['others'] : '';

                    if (!isset($arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentries'])) {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentries'] = array();
                    }
                    $arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentries'][$att_date] = $present . $leaves . $weekoff . $holiday . $others;

                    //Present Count
                    if (!isset($arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentrysummary'][0])) {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentrysummary'][0] = 0 + (substr_count(strtoupper($present), 'P') / 2);
                        ;
                    } else {
                        $arr_timeattendances_for_template[$branch_code][$emp_pkey]['registerentrysummary'][0] += (substr_count(strtoupper($present), 'P') / 2);
                    }


                    $att_in_time = isset($timeattendance['emp_detail_timeattandance']['att_in_time']) ? $timeattendance['emp_detail_timeattandance']['att_in_time'] : '';
                    $att_out_time = isset($timeattendance['emp_detail_timeattandance']['att_out_time']) ? $timeattendance['emp_detail_timeattandance']['att_out_time'] : '';
                    $duration = isset($timeattendance['emp_detail_timeattandance']['duration']) ? $timeattendance['emp_detail_timeattandance']['duration'] : '';
                    $arr_timeattendances_for_template[$branch_code][$emp_pkey]['checkin'][$att_day] = $att_in_time;
                    $arr_timeattendances_for_template[$branch_code][$emp_pkey]['checkout'][$att_day] = $att_out_time;
                    $arr_timeattendances_for_template[$branch_code][$emp_pkey]['duration'][$att_day] = $duration;
                }
            }
//            /debug($arr_timeattendances_for_template);die();
            $this->set('arr_timeattendancereporttemplate', $arr_timeattendances_for_template);
            //debug($arr_timeattendances_for_template);
            $this->set('arr_dates', $arr_dates);

            if (isset($arr_branch_details) && !empty($arr_branch_details)) {
                $this->set('arr_branchinfo', $arr_branch_details);
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reporttimeattendance');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('helvica', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('Timeattendance.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :

                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Attendance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

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
                //autowidth
                for ($col = 'A'; $col !== 'AG'; $col++) {
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

                $rowcount = 2;
                $counts = 0;
                foreach ($arr_timeattendances_for_template as $key => $value) {
//                    debug($value);die();

                    $branch = (isset($arr_branchinfo[$branch_code]['branch_code']) ? $arr_branchinfo[$branch_code]['branch_code'] . ' - ' : '') . (isset($arr_branchinfo[$branch_code]['branch_name']) ? $arr_branchinfo[$branch_code]['branch_name'] : '');
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':F' . $rowcount);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Attedance Reports of ' . $branch . ' For the month ' . date("Y-m",  strtotime($yearmonth)));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                    $rowcount += 1;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    for ($i = 0; $i <= 8; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }

                    $columnindex = 2;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnindex, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($columnindex)->setWidth(12);


                        $columnindex++;
                    }
                    $rowcount += 1;

                    $arr_data = $value;
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                    if (count($arr_data) >= 0) {

                        foreach ($arr_data as $key => $val) {
//                            debug($val);
//                            die();
                            $columnindex = 0;
                            $counts += 1;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $counts);
                            $columnindex = 1;
                            $id = $val['emp']['emp_company_id'];
                            $name = $val['employeeinfo']['first_name'] . ' ' . $val['employeeinfo']['last_name'];


                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $id . ' ' . $name);

                            $columnindex = 2;
                            foreach ($val['registerentries'] as $key => $date) {

                                $dta = $date;

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }

                            $rowcount++;
                            $columnindex = 1;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, "check in");
                            $columnindex = 2;
                            foreach ($val['checkin'] as $key => $date) {

                                if ($date) {
                                    $dta = date("m-d : h:i", strtotime($date));
                                } else {
                                    $dta = $date;
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }

                            $rowcount++;
                            $columnindex = 1;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, "check out");
                            $columnindex = 2;
                            foreach ($val['checkout'] as $key => $date) {

                                if ($date) {
                                    $dta = date("m-d : h:i", strtotime($date));
                                } else {
                                    $dta = $date;
                                }
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                $columnindex++;
                            }
                            $rowcount++;
                            $columnindex = 1;

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, "Duration");
                            $columnindex = 2;
                            foreach ($val['duration'] as $key => $date) {

                                $dta = $date;
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
                $this->render('reporttimeattendance');
                break;
        }
    }

    private function generatemobilelocationreport($type = '', $mode = '') {
        $arr_form_data = $_REQUEST;
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            // debug($arr_leavepolicygroupids);
        }
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            $condion1 = "";
            if ((isset($arr_form_data['select-criteria1']) && $arr_form_data['select-criteria1'] == 'EmployeeDetails')) {
                $arr_leavepolicygroupids = implode(", ", $arr_leavepolicygroupids);
                $condion1 .= 'and  emp_fkey in(' . $arr_leavepolicygroupids . ')';
            }

            $date = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');
            $month = date('m', strtotime($date));
            $year = date('Y', strtotime($date));


            $arr_mob_location = $this->MobileUserauditor->query("select a.emp_fkey,ei.designation,department,branch,a.userid,a.empname	,a.datetimecheck ,a.location,a.in_out 
                from (SELECT emp_fkey, mlu.userid, concat (first_name,' ',middle_name,' ' ,last_name) empname ,mlu.time_check as datetimecheck ,mlu.location,mlu.in_out
                FROM mob_user_login_auditor mlu , user_credentials uc left join employee_info as info on(info.emp_pkey=uc.emp_fkey)
                 where mlu.userid=uc.user_id 
                       union all 
                SELECT emp_fkey,mbuserloc.user_id,concat (first_name,' ',middle_name,' ' ,last_name) empname,mbuserloc.created_time as datetimecheck ,mbuserloc.location,
                'Updated Location' from mob_user_locations mbuserloc, user_credentials uc
                 left join employee_info as info on(info.emp_pkey=uc.emp_fkey)
                 where mbuserloc.user_id=uc.user_id) a, employee_info ei 
                where ei.emp_pkey=a.emp_fkey
                and  month(datetimecheck)='$month' and year(datetimecheck)='$year' $condion1
                order by empname, datetimecheck,in_out
                ,in_out
                ");

            //}
//debug($arr_mob_location);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $this->set('date', $date);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            if (isset($arr_mob_location) && !empty($arr_mob_location)) {
                $this->set('arr_mob_location', $arr_mob_location);

                switch ($mode) {
                    case 'pdf' :
                        $this->set('mode', 'pdf');
                        $view = new View($this, false);
                        $view_output = $view->render('reportmobilelocation');
                        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                        $html2pdf = new HTML2PDF('L', 'A3', 'en');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->writeHTML($view_output);
                        $html2pdf->Output('Mobilelocation.pdf', 'D');
                        break;
                    case 'excel' :
                        $str_company_code = $this->Session->read('company_code');
                        $file_name = isset($str_company_code) ? $str_company_code . "_mobilelocation.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
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
                        $worksheet->setCellValueByColumnAndRow(0, 2, "For the Month : " . $date);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(16);
                        $worksheet->mergeCells('A2:F2');
                        $rowcount = 3;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Date');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Designation');
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Date Of join');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Departments');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Branch');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Action');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Location');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        for ($i = 0; $i <= 8; $i++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                        }
                        $columnindex = 0;
                        $rowcount = 4;
                        $empnme = "";
                        foreach ($arr_mob_location as $value) {

                            //$name=$name.$key;
                            $name = $value['a']['empname'];
                            $date = $value['a']['datetimecheck'];
                            $action = $value['a']['in_out'];
                            $location = $value['a']['location'];
                            $id = $value['a']['userid']; // correcte by sruthi 28/07/2016
                            $clas = $value['ei']['designation'];
                            //$join = $value['ei']['joining_date'];
                            $dept = $value['ei']['department'];
                            $unit = $value['ei']['branch'];

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                            if ($empnme == $name) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, " ");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 2), ($rowcount), $id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 3), ($rowcount), "");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 4), ($rowcount), "");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 5), ($rowcount), "");
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 6), ($rowcount), "");
                                // $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($rowcount1, $columnindex);
                                // $objPHPExcel->setActiveSheetIndex(0)->mergeCells(($columnindex+1).$rowcount.':'.($columnindex+1).($rowcount+1));
                            } else {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 2), ($rowcount), $id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 3), ($rowcount), $clas);
                                //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 4), ($rowcount), $join);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 5), ($rowcount), $dept);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columnindex + 6), ($rowcount), $unit);
                            }

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $action);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $location);
                            $rowcount++;
                            //  $empnme = $value[0]['empname']; corrected by sruthi
                            $empnme = $value['a']['empname'];
                        }


                        $objPHPExcel->getActiveSheet()->setTitle('Mobile Location');
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
                        $this->render('reportmobilelocation');
                        break;
                }
            } else {
                echo "<div style='color:red'><h3>No record Found</h3></div>";
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
        }
    }

//Over Time
    public function Overtimereport($type = '', $mode = '') {
        $arr_form_data = $_REQUEST;
        //  debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }




        $arr_leavepolicygroupids = array();
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
 $condition = "WHERE EmployeeDetails.status = 1";
//        debug($arr_form_data);
        if(isset($arr_form_data['resigned']) && $arr_form_data['resigned'] =='1')
        { 
          
            $condition =  "WHERE EmployeeDetails.status in('1','2')";
           
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
//                     .$str_conditions)
                //  debug($leavepolicygroupid);

                $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT `OTMASTER`.*,"
                        . "`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`Info`.*"
                        . " FROM `emp_ot_master` AS `OTMASTER`"
                        . " LEFT JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_pkey` = `OTMASTER`.`emp_fkey`)"
                        . "LEFT JOIN `employee_info` AS `Info` ON (`Info`.`emp_pkey` = `EmployeeDetails`.`emp_pkey`)"
                        . "$condition and OTMASTER.month = '$from' and EmployeeDetails.branch_code = '$leavepolicygroupid' and is_verified = 'Y' ");



                $arr_leavepolicydetails_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
            //debug($arr_leavepolicydetails_for_template);


            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        // debug($this_month_att);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('overtime');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('OvertimeAttendance.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_OverTimeattendance.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Over Time Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Over Time  Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 3;
                $i = 0;
                //$arr_leavepolicydetails_for_template
                foreach ($arr_leavepolicydetails_for_template as $value) {

                    //    debug($value);
                    $i += 1;
                    $arr_daata = $value['summary'];
                    //debug($arr_daata);
                    $pp = isset($value['summary'][0]['Info']['branch']) ? $value['summary'][0]['Info']['branch'] : '';
                    if ($pp != '') {
                        $branch = isset($value['summary'][0]['Info']['branch']) ? $value['summary'][0]['Info']['branch'] : '';
                    } else {
                        $branch = isset($value['summary'][0]['Info']['dept_name']) ? $value['summary'][0]['department']['dept_name'] : '';
                    }
                    // debug($branch);
                    //die();
                    $arr_data = $value['summary'];
                    if (count($arr_data) > 0) {
//header                    
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Over Time  Details of ' . $branch);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//merge cell
                        $objPHPExcel->getActiveSheet()->mergeCells('A3:D3');
//set width
                        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(22);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);

                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(10);
                        //field start                   
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), 'SL No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), 'Employee name');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Branch');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Department');
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Joining Date');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . ($rowcount), 'Total Duration(In Hrs)');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . ($rowcount), 'Verified Duration(In Hrs)');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . ($rowcount), 'Approved');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . ($rowcount), 'Remarks');
//set bond text size
                        for ($i = 0; $i <= 10; $i++) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                        }
                        $columnindex = 0;

                        $rowcount = $rowcount + 1;
                        $k = 1;
                        foreach ($arr_data as $val) {
                            $name = $val['Info']['EmpName'];
                            $id = $val['Info']['employee_id'];
                            $branch = $val['Info']['branch'];
                            $designation = $val['Info']['designation'];
                            $department = $val['Info']['department'];
                            $join = $val['Info']['joining_date'];
                            $totel = round(($val['OTMASTER']['total_duration'] / 60), 2);
                            $verified = isset($val['OTMASTER']['set_duration']) ? round(($val['OTMASTER']['set_duration'] / 60), 2) : round(($val['OTMASTER']['total_duration'] / 60), 2);
                            $approved = isset($val['OTMASTER']['is_verified']) == "Y" ? "Yes" : "NO";
                            $remarks = $val['OTMASTER']['remarks'];
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $k);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $name);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $branch);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $designation);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $department);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $totel);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $verified);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), $approved);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), $remarks);
                            $rowcount++;
                            $k++;
                        }
                    }
//                    else {
//                        $msg = 'No employees found under this shift';
//                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . ($rowcount), $msg);
//                    }
                    $rowcount++;
                }
                $objPHPExcel->getActiveSheet()->setTitle('Overtime Attendance Report');
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
                $this->render('overtime');
                break;

            //   die();
        }
    }
    
    
    
    private function generateCheckinlogsReport($mode) {
        $arr_form_data = $_REQUEST;
        

        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');
        
        //Parsing the months
        $spli_month = explode(" - ", $month);
        $start_month = $month; 
//        isset($spli_month[0])?date('Y-m-d',  strtotime($spli_month[0])):date('Y-m-d');
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto']. " " ."23:59:59" : date('Y-m-1');
//        isset($spli_month[1])?date('Y-m-d',  strtotime($spli_month[1])):date('Y-m-d');
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);
        $fd = $start_month . ' ' . '00:00:00';
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
        }
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
     
        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                
                
                
            }
            
            
            
            $today_cond = array(
                "LOGDATE BETWEEN '$fd' and '$end_month' ",
                "DeviceAttendance.status >=" => "Y"
            );
            
            if($arr_form_data['select-criteria1'] == 'EmployeeDetails'){
                $today_cond[] = array("EmployeeDetails.emp_pkey"=>$arr_leavepolicygroupids);
            }else{
                $today_cond[] = array("EmployeeDetails.branch_code"=>$arr_leavepolicygroupids);
            }

            $today_join[] = array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'INNER',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
            );

//            $emp_fkey = $this->Session->read('emp_fkey');
//            if ($emp_fkey != '') {
//                if ($hierarchy == 'hierarchy') {
//                    $today_join[] = array(
//                        'table' => 'emp_proff',
//                        'alias' => 'EmployeeProfessionalDetails',
//                        'type' => 'LEFT',
//                        'foreignKey' => false,
//                        'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
//                    );
//                    $today_cond[] = "EmployeeDetails.emp_pkey in (select emp_fkey from emp_proff where attr1 = '$emp_fkey' or emp_fkey = '$emp_fkey')  ";
//                } else {
//                    $today_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
//                }
//            }

            $today_join[] = array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('DeviceAttendance.branch_code = Branch.branch_code')
            );

            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $today_att = $this->DeviceAttendance->find("all", array(
                "order" => "DeviceAttendance.LOGDATE ASC",
                "conditions" => $today_cond,
                'joins' => $today_join,
                "fields" => "Branch.branch_name,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
                if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location"
                    )
            );

            $arr_resp = array(
                'data' => array()
            );

            
            $i = 0;
            foreach ($today_att as $key => $att) {
                $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
                $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
                $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d-m-Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
                $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
                $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
                $i++;
            }
            
//            debug($arr_resp);

            $this->set('arr_leavepolicydetails_for_template', $arr_resp);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month",$report_month);
            switch ($mode) {
                case 'pdf' :
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('Dashboard');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('reportsummary.pdf', 'D');


                    // $this->render('reportsummary');                
                    break;
                case 'excel' :

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_attendane.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
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
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'SL No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Week Off');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Holidays');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'LOP');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					//$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Present Days');
                    //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					//$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Leave Days');
                    //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                    $columnindex = $columncount + 12;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                        $columnindex++;
                    }
                    $rowcount = 3;
                    //$rowcount1=3;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $branch = isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';
                        //echo $branch;die();                     
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');

                        $arr_data = $value['summary'];
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        if (count($arr_data) >= 0) {
                            $k = 1;
                            foreach ($arr_data as $key => $val) {
                                $columnindex = 0;
                                $name = $val['Info']['EmpName'];

                                //$name = $val['AttendanceRegister']['emp_name'];
                                //$name=$name.$key;
                                $present = $val['AttendanceRegister']['days_present'];
                                $leave = $val['AttendanceRegister']['days_leave'];
                                $holidays = $val['AttendanceRegister']['days_holidays'];
                                $holi = $val['AttendanceRegister']['HO'];
                                $lop = $val['AttendanceRegister']['lops'];
                                $id = $val['Info']['employee_id'];
                                $des = $val['Info']['designation'];
                                $join = $val['Info']['joining_date'];
                                $department = $val['Info']['department'];
                                $branch = $val['Info']['branch'];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $des);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $join);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $present);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $leave);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $holidays);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $holi);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 11) . $rowcount, $lop);

                                $columnindex = $columnindex + 12;
                                foreach ($arr_dates as $key => $date) {
                                    $newIndex = 'FIELD' . ($key + 1);
                                    $dta = $val['AttendanceRegister'][$newIndex];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                    $columnindex++;
                                }
                                $k++;
                                $rowcount++;
                            }
                        }
                        $rowcount1 = $rowcount + 1;
                    }

                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Policy');
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
                    $this->render('Dashboard');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

}
