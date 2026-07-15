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
class HierarchyReportController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'HierarchyReport';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('LeaveRequests', 'Leavestatus', 'SalaryHeadItems', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'CompanyContactInfo', 'LeaveType','ReportAudit','EmployeeDetails'); //santhu
    public $components = array('MasterdataManagement');

   
    /*
     * HR Reports landing view
     */

    public function hrreports() {
        $arr_reporttypes = array(
              'EmployeeHierarchy' => 'Employee Hierarchy ',
              'LeaveHierarchy' => 'Leave Hierarchy '
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
                case 'EmployeeHierarchy':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //santhu
                case 'LeaveHierarchy':
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

    public function listcriteriaitems($str_criteria = '', $type = '')
    {
        $this->autoRender = false;
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        $user = $this->Session->read('company_code');
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            //commented by amal on 01/08/2019 leave type list assigned direct & indirect types 1
            //            if ($model == 'LeaveType') {
            //                $conditions = array("head_fkey" => 6, "value" => 'Y', "status" => 1);
            //            } else
            if ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Leavestatus') {
                $conditions = array();
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1, "branch_code" => $cur_emp_branch);
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


                case 'EmployeeDetails':
                    //edited by arul - changed empid ad company id
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




                    $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions[] = array("status in(1,2)");
                    } else {

                        $conditions[] = array("status" => 1);
                    }
                    if ($type == 'LeaveHierarchy') {
                        $conditions[] = array("emp_pkey in (select policy_id from emp_config where policy_id > 0 and type='LAPPR' and status = 1)");
                    } else {
                        $conditions[] = array("emp_pkey in (select attr1 from emp_proff  where attr1 >0)");
                    }
                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    // Edited by Athira on 31-1-2025
                    $user_group = $this->Session->read('user_group');
                    if($user_group == 2){
                        if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG' || $user == 'DEMO')) {
                            $cur_emp_key = $this->Session->read("emp_fkey");
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                            $emp_pkey = $this->Session->read("emp_fkey");
                            // $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                            if ($is_ho != 1) {
                                $conditions["branch_code"] = $is_ho;
                            }
                        }else{
                            $cur_emp_key = $this->Session->read("emp_fkey");
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                            $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);                                       
                        }
                    }
                    // End
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
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;
            }
            //  debug($arr_emp);
            echo json_encode($arr_criteriaItems);
        }
    }
    
    public function downloadHistory($type, $mode) {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
                 case 'EmployeeHierarchy':
                $dataForHistory['report_type'] = "Employee Hierarchy Report";
                break;
                 case 'LeaveHierarchy':
                $dataForHistory['report_type'] = "Leave Hierarchy Report";
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
                case 'Units': $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'LeaveType': $criteria_name_array[] = 'belonging to a Leave Type';
                    break;
                case 'Leavestatus': $criteria_name_array[] = 'belonging to a Leave status';
                    break;
                case 'EmployeeDetails': $criteria_name_array[] = 'belonging to an Employee';
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

        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }

    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;

        switch ($type) {
                 case 'EmployeeHierarchy':
                $this->generateemployeehierarchyreport($type, $mode);
                break;
                 case 'LeaveHierarchy':
                $this->generateleavehierarchyreport($type, $mode);
                break;
            default:
                return false;
                break;
        }
        
        $this->downloadHistory($type,$mode);
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

   

 //employee hierarchy report
   private function generateemployeehierarchyreport($type = '', $mode = '')
    {

        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        //Build conditions based on criterias recieved
        //$fd=$arr_form_data['reportfrom'].' '.'00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);


        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        $condition = ' and employee_info.emp_status = 1  ';
        $condition1 = ' and ed.status = 1 ';
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and employee_info.emp_status in('1','2') ";
            $condition1 = " and ed.status in('1','2') ";
        }

        // $emp_branch_condition = "";




        $arr_hierarchy_for_template = array();
        $arr_hierarchy_emplist = array();
        if ($arr_hierarchy_emplist != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_leavepolicydetails_for_template2 = array();

                try {
                    // $arr_hierarchy_emplist = $this->LeaveRequests->query("select CONCAT(first_name, ' ', ifnull(last_name,'')) AS emp_name,ed.status,emp_company_id,branch,department, employee_info.designation,
                    //     (select EmpName from employee_info where emp_pkey='$leavepolicygroupid' $condition) superior ,
                    //      (select hirc_leval from emp_config where emp_pkey='$leavepolicygroupid' $condition) order ,

                    //         (select emp_status from employee_info where emp_pkey='$leavepolicygroupid' $condition) superior_status ,

                    //     (select employee_id from employee_info where emp_pkey='$leavepolicygroupid' $condition) superior_comp_id from emp_proff 
                    //     left join emp_details as ed on (emp_proff.emp_fkey = ed.emp_pkey)
                    //     left join emp_config as ec on (emp_config.emp_fkey = ed.emp_pkey)

                    //     left join employee_info on (emp_proff.emp_fkey = employee_info.emp_pkey)  where attr1='$leavepolicygroupid' $condition1 "); 




                    //                       $arr_hierarchy_emplist = $this->LeaveRequests->query("select distinct employee_id,EmpName,branch,department,employee_info.emp_id,ed.status, employee_info.designation,hirc_leval,
                    //             (select EmpName from employee_info where emp_pkey='14' and employee_info.emp_status = 1) superior ,
                    // (select employee_id from employee_info where emp_pkey='14' and employee_info.emp_status = 1) superior_comp_id from emp_proff
                    //                          left join emp_config on (emp_proff.emp_fkey = emp_config.emp_fkey)
                    //                          left join employee_info on (emp_proff.emp_fkey = employee_info.emp_pkey)
                    //                          left join emp_details as ed on (emp_proff.emp_fkey = ed.emp_pkey)
                    //                          where emp_config.policy_id='14' and emp_config.type='HIERARCHY'
                    //              and emp_config.status = 1 and employee_info.emp_status = 1 order by hirc_leval"); 
                    $arr_hierarchy_emplist = $this->LeaveRequests->query("select distinct employee_id,EmpName,branch,department,employee_info.emp_id,ed.status, employee_info.designation,employee_info.joining_date,hirc_leval,te.last_approved_working_date,
                           (select EmpName from employee_info where emp_pkey='$leavepolicygroupid' $condition) superior ,
                           (select emp_status from employee_info where emp_pkey='$leavepolicygroupid' $condition) superior_status ,
                           (select employee_id from employee_info where emp_pkey='$leavepolicygroupid' $condition) superior_comp_id from emp_proff
                           left join emp_config on (emp_proff.emp_fkey = emp_config.emp_fkey)
                           left join employee_info on (emp_proff.emp_fkey = employee_info.emp_pkey)
                           left join emp_details as ed on (emp_proff.emp_fkey = ed.emp_pkey)
                           left join termination as te on (emp_proff.emp_fkey = te.emp_fkey)
                           where emp_config.policy_id='$leavepolicygroupid' and emp_config.type='HIERARCHY'
                           and emp_config.status = 1 and employee_info.emp_status = 1 order by hirc_leval");
                } catch (Exception $ex) {
                }

                if (!empty($arr_hierarchy_emplist)) {
                    $arr_hierarchy_for_template[] = array(
                        'summary' => $arr_hierarchy_emplist

                    );
                }
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        // debug($arr_hierarchy_for_template);
        $this->set('arr_hierarchy_for_template', $arr_hierarchy_for_template);
        $cr = $arr_form_data['select-criteria1'];
        // $this->set('cr', $cr);
        // $this->set('from', $from);

        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        //$this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf':
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('employeehierarchy');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeHierarchy.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_EmployeeHierarchy.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Hierarchy");
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
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:J2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $BStyle = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );

                $columncount = 0;
                $rowcount = 3;
                $objPHPExcel->setActiveSheetIndex(0);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Superior');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Company ID');
                // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Joining Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Branch');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Department');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Designation');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Termination Date');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Order');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);


                $i = 1;
                $rowcount = $rowcount + 1;
                foreach ($arr_hierarchy_for_template as $value) {
                    if (count($value['summary']) !== 0) {
                        $empstatus = isset($value['summary']['0']['0']['superior_status']) && $value['summary']['0']['0']['superior_status'] == "2" ? '  (Resigned)' : '';
                        $superior = isset($value['summary']['0']['0']['superior']) ? $value['summary']['0']['0']['superior'] . " - " . $value['summary']['0']['0']['superior_comp_id'] . $empstatus : '';
                        //                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'SUPERIOR - : ' . $superior);
                        //                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        //                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(14);
                        //                            $rowcount+=1;

                        if (count($arr_hierarchy_for_template) != 0) {
                            $arr_data = $value['summary'];
                            if (count($arr_data) > 0) {



                                foreach ($arr_data as $val) {

                                    //edited by athira on 25-04-2025
                                    
                                    $Employeeid = $val['employee_info']['employee_id'];
                                  
                                    $empstatus1 = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '  (Resigned)' : '';
                                    $Employeename = $val['employee_info']['EmpName'] . $empstatus1;
                                    $Branch = $val['employee_info']['branch'];
                                    $Department = $val['employee_info']['department'];
                                  //  $c_id = $val['employee_info']['employee_id'];
                                    $Designation = $val['employee_info']['designation'];
                                    $join = $val['employee_info']['joining_date'];
                                    $j_date = date('d-m-Y', strtotime($join));
                                    $terminn = $val['te']['last_approved_working_date'];
                                    if (!empty($terminn)) {
                                        $termin = date('d-m-Y', strtotime($terminn));
                                    } else {
                                        $termin = '';
                                    }
                                    $order = $val['emp_config']['hirc_leval'];
                                    // $termin=$val['termination']['last_approved_working_date'];



                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $superior);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $Employeeid);
                                 //   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $c_id);

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $Employeename);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $j_date);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $Branch);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $Department);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $Designation);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $termin);
                                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $termin);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $order);
                                    $rowcount++;
                                    $i++;
                                }

                                $BStyle = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                );
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('B4:J4000')
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()
                                    ->getStyle('A4:A400')
                                    ->getAlignment()
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $row = $rowcount - 1;
                                $objPHPExcel->getActiveSheet()->getStyle('A1:J' . $row)->applyFromArray($BStyle);

                                foreach (range('A', 'J') as $columnID) {
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                                }
                            }

                            //                 $BStyle = array(
                            //   'borders' => array(
                            //     'allborders' => array(
                            //       'style' => PHPExcel_Style_Border::BORDER_THIN
                            //     )
                            //   )
                            // );

                        } else {
                            $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                        }
                    }
                }
                $objPHPExcel->getActiveSheet()->setTitle('Employee Hierarchy');
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
                $this->render('employeehierarchy');
                break;
        }
    }






///leave hierarchy

   private function generateleavehierarchyreport($type = '',$mode = '') {

        $arr_form_data = $_REQUEST;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

        //Build conditions based on criterias recieved
        //$fd=$arr_form_data['reportfrom'].' '.'00:00:00';
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

       
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

         $condition = 'and ed.status = 1';
         $conditions = 'and employee_info.emp_status = 1';
         if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
             $condition = "and ed.status in('1','2')";
             $conditions = "and employee_info.emp_status in('1','2')";
         }

        // $emp_branch_condition = "";
        


        
        $arr_hierarchy_for_template = array();
        $arr_hierarchy_emplist = array();
        if ($arr_hierarchy_emplist!= '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_leavepolicydetails_for_template2 = array();
                
                // if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    try {
                        $arr_hierarchy_emplist = $this->LeaveRequests->query("select distinct employee_id,EmpName,branch,department,ed.status, employee_info.designation,
                            (select EmpName from employee_info where emp_pkey='$leavepolicygroupid' $conditions) superior ,
                                   (select emp_status from employee_info where emp_pkey='$leavepolicygroupid' $conditions) superior_status ,
                            (select employee_id from employee_info where emp_pkey='$leavepolicygroupid' $conditions) superior_comp_id from emp_proff 
                            left join emp_config on (emp_proff.emp_fkey = emp_config.emp_fkey)
                            left join employee_info on (emp_proff.emp_fkey = employee_info.emp_pkey) 
                            left join emp_details as ed on (emp_proff.emp_fkey = ed.emp_pkey)
                            where emp_config.policy_id='$leavepolicygroupid' and emp_config.type='LAPPR'"
                                . " and emp_config.status = 1 $condition $conditions"); 

                    }
                         catch (Exception $ex) {
                   
                  }
                 
                 //}
                if (!empty($arr_hierarchy_emplist)) {
                    $arr_hierarchy_for_template[] = array(
                        'summary' => $arr_hierarchy_emplist
                            
                    );
                }
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
// debug($arr_hierarchy_for_template);
        $this->set('arr_hierarchy_for_template', $arr_hierarchy_for_template);
        $cr = $arr_form_data['select-criteria1'];
        // $this->set('cr', $cr);
        // $this->set('from', $from);

        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        //$this->set('dates', $from);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf' :
                //echo "entered in";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('leavehierarchy');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('LeaveHierarchy.pdf', 'D');
                //$this->render('reportleavebalance');                
                break;
            case 'excel' :
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_LeaveeHierarchyReport.xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Leave Hierarchy Report");
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
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:H2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $BStyle = array(
  'borders' => array(
    'allborders' => array(
      'style' => PHPExcel_Style_Border::BORDER_THIN
    )
  )
);

                $columncount = 0;
                    $rowcount = 3;
                    $objPHPExcel->setActiveSheetIndex(0);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Superior');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                        
                        $i = 1;
                  $rowcount = $rowcount + 1;
                      foreach ($arr_hierarchy_for_template as $value) {
                        if (count($value['summary']) !== 0) {
                             $supstatus = isset($value['summary']['0']['0']['superior_status']) && $value['summary']['0']['0']['superior_status'] == "2" ? '(Resigned)' : '';
                           $superior = isset($value['summary']['0']['0']['superior']) ? $value['summary']['0']['0']['superior'] . " - " . $value['summary']['0']['0']['superior_comp_id']. " " .$supstatus. " " : '';
//                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Superior - : ' . $superior);
//                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
//                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(14);
//                            $rowcount+=1;
                        
                if (count($arr_hierarchy_for_template) != 0) {
                        $arr_data = $value['summary'];
                        if (count($arr_data) > 0) {

                             foreach ($arr_data as $val) {
                                
                            $Employeeid=$val['employee_info']['employee_id'];
                            $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '  (Resigned)' : '';
                                 $Employeename=$val['employee_info']['EmpName'].$empstatus;
                                  $Branch=$val['employee_info']['branch'];
                                   $Department=$val['employee_info']['department'];
                                    $Designation=$val['employee_info']['designation'];
                                
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $superior);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $Employeeid);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $Employeename);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $Branch);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $Department);
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $Designation);
                 
                                        
                                        $rowcount ++; 
                                        $i++;
                                    }

                                     $BStyle = array(
                             'borders' => array(
                             'allborders' => array(
                             'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                               )
                            );
     $objPHPExcel->getActiveSheet()
    ->getStyle('B4:B400')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $objPHPExcel->getActiveSheet()
    ->getStyle('A4:A400')
    ->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:H'.$row)->applyFromArray($BStyle);
                        
                        foreach(range('A','H') as $columnID) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                    }


                                      }

//                 $BStyle = array(
//   'borders' => array(
//     'allborders' => array(
//       'style' => PHPExcel_Style_Border::BORDER_THIN
//     )
//   )
// );
                       
                 }
                 else {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No Data Available With The Selected Criteria");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(true);
                }
                }} 
                $objPHPExcel->getActiveSheet()->setTitle('Leave Hierarchy Report');
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
                $this->render('leavehierarchy');
                break;
        }
    }
}


