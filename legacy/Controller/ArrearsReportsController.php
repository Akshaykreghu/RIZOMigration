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
class arrearsReportsController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'ArrearsReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Attendance', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'DbConfig', 'MobileUserauditor', 'CompanyContactInfo', 'ReportAudit'); //santhu
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

    public function hrreports()
    {
        $user_group = $this->Session->read('user_group');
        $user = strtoupper($this->Session->read('company_code'));

        //edited by athira on 06-02-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = $plan['0']['comp_contact_info']['plan'];
        //end

        $arr_reporttypes = array(
            'arrears' => 'Arrears',
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
        //end
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
                //changes anukrishnan 17-01-2025 open
                case 'arrears':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //changes anukrishnan 17-01-2025 close
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
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
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
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,status';
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

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                    }
                    //employee branch wise sorting ends here

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
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status']; //The status is added by ***ARUL P DAS on 3/1/2020
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

    public function downloadHistory($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Arrears':
                $dataForHistory['report_type'] = "Arrears Report";
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
                case 'EmployeeDetails':
                    $criteria_name_array[] = 'belonging to an Employee';
                    break;
                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';
                    break;
                default:
                    break;
            }
            $stcriteria = isset($arr_form_data[$criteria]) ? $arr_form_data[$criteria] : '';
            if ($stcriteria) {
                $items_array[] = implode(",", $arr_form_data[$criteria]);
                $items_count_array[] = count($arr_form_data[$criteria]);
            }
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

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        //debug($mode);
        switch ($type) {
            case 'arrears':
                $this->generatearrearreport($type, $mode);
                break;
            default:
                return false;
                break;
        }

        $this->downloadHistory($type, $mode);
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



    public function generatearrearreport($type, $mode)
    {
        $arr_form_data = $_REQUEST;
        // var_dump($arr_form_data);
        $user_id = $this->Session->read('login_user_id');
        $date_time = date('d-m-y H:i:s');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $employeeIds = isset($arr_form_data['EmployeeDetails']) ? $arr_form_data['EmployeeDetails'] : [];
        $branchs = isset($arr_form_data['Units']) ? $arr_form_data['Units'] : [];


        $reporttfrom = isset($arr_form_data['reportfrom']) && !empty($arr_form_data['reportfrom']) ?
            $arr_form_data['reportfrom'] : null;




        // Edited by Akshay on 22-1-2025
        $resigned = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : 0;
        $condition = "";
        if ($resigned != 1) {
            $condition = " AND ei.emp_status = 1";
        } else {
            $condition = " AND ei.emp_status IN(1, 2)";
        }
        // End

        $baseQuery = "SELECT DISTINCT
                        ei.emp_pkey AS ei__emp_pkey,
                        ei.EmpName AS ei__EmpName,
                        ei.joining_date AS ei__joining_date,
                        ei.branch AS ei__branch,
                        ei.department AS ei__department,
                        ei.designation AS ei__designation,
                        ei.employee_id AS ei__employee_id,
                        ei.emp_status AS ei__emp_status,

                        tm.last_working_date AS tm__last_working_date,
                        uc.user_id AS uc__user_id,

                        old.salary_head_item_fkey AS old__salary_head_item_fkey,
                        old.salary_head_item_desc AS old__salary_head_item_desc,
                        old.salary_amount AS old__salary_amount,
                        new.salary_amount AS new__salary_amount,
                        new.arrear_amount AS new_arrear_amount

                        FROM 
                            emp_new_salary_slip old

                        INNER JOIN emp_new_salary_slip new 
                            ON old.salary_head_item_fkey = new.salary_head_item_fkey
                            AND old.emp_fkey = new.emp_fkey
                            AND old.month_year = new.month_year
                            AND (new.action = 'P' OR new.action IS NULL)

                        INNER JOIN employee_info ei ON ei.emp_pkey = old.emp_fkey
                        LEFT JOIN termination tm ON ei.emp_pkey = tm.emp_fkey
                        LEFT JOIN user_credentials uc ON ei.emp_pkey = uc.emp_fkey
                        LEFT JOIN salary_head_items shi ON old.salary_head_item_fkey = shi.salary_head_item_pkey

                        WHERE 
                            old.month_year = '$reporttfrom'
                            -- AND old.emp_fkey = 1005
                            AND old.action = 'O'
                            AND old.end_date_effective IS NULL
                            AND new.end_date_effective IS NULL
                        $condition
                        ";
        if ($arr_form_data['select-criteria1'] == 'EmployeeDetails' && !empty($employeeIds)) {
            $employeeIdsPlaceholder = implode(",", array_map('intval', $employeeIds));
            $baseQuery .= " AND old.emp_fkey IN ($employeeIdsPlaceholder)";
        } elseif (!empty($branchs)) {
            $branchsPlaceholder = "'" . implode("','", array_map('addslashes', $branchs)) . "'";
            $baseQuery .= " AND ei.branch_code IN ($branchsPlaceholder)";
        }

        // Edited by Akshay on 22-1-2025
        $condition = "";
        if ($resigned != 1) {
            $condition = " AND ei.emp_status = 1";
        } else {
            $condition = " AND ei.emp_status IN(1, 2)";
        }
        // End

        $baseQuery .= " ORDER BY ei.emp_pkey, shi.head_fkey, shi.salary_head_item_pkey, ei.branch, ei.EmpName";
        try {
            $result = $this->DeviceAttendance->query($baseQuery);
        } catch (Exception $e) {
            debug($e);
        }

        $processedData = [];

        foreach ($result as $row) {
            $data = $row[0]; // CakePHP raw SQL format
            $arr_arrear = $row['new']; 
            $empStatus = isset($data['ei__emp_status']) ? $data['ei__emp_status'] : 1;

            $processedData[] = [
                'sl_no' => count($processedData) + 1,
                'employee_id' => isset($data['ei__employee_id']) ? $data['ei__employee_id'] : '',
                'user_id' => isset($data['uc__user_id']) ? $data['uc__user_id'] : '',
                'first_name' => isset($data['ei__EmpName'])
                    ? trim($data['ei__EmpName']) . (isset($empStatus) && $empStatus == 1 ? '' : ' (Resigned)')
                    : '',
                'joining_date' => !empty($data['ei__joining_date'])
                    ? DateTime::createFromFormat('Y-m-d', $data['ei__joining_date'])->format('d-m-Y')
                    : '',
                'branch' => isset($data['ei__branch']) ? $data['ei__branch'] : '',
                'department' => isset($data['ei__department']) ? $data['ei__department'] : '',
                'designation' => isset($data['ei__designation']) ? $data['ei__designation'] : '',
                'last_working_date' => !empty($data['tm__last_working_date'])
                    ? DateTime::createFromFormat('Y-m-d', $data['tm__last_working_date'])->format('d-m-Y')
                    : '',
                'item' => isset($data['old__salary_head_item_desc']) ? $data['old__salary_head_item_desc'] : '',
                'old_salary' => isset($data['old__salary_amount']) ? $data['old__salary_amount'] : '',
                'new_salary' => isset($data['new__salary_amount']) ? $data['new__salary_amount'] : '',
                'salary_difference' => (isset($data['new__salary_amount']) && is_numeric($data['new__salary_amount']) &&
                    isset($data['old__salary_amount']) && is_numeric($data['old__salary_amount']))
                    ? $data['new__salary_amount'] - $data['old__salary_amount']
                    : '',
                'arrear_amount' => isset($arr_arrear['new_arrear_amount']) ? $arr_arrear['new_arrear_amount'] : '',
            ];
        }

        $reporttfrom = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $reportto = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        $heading = "Arrears " . date('F - Y', strtotime($reporttfrom));

        $this->set('selectCriteria1', $arr_form_data['select-criteria1']);
        $this->set('processedData', $processedData);
        $this->set('heading', $heading);
        $this->set('user_id', $user_id);
        $this->set('datetime', $date_time);
        switch ($mode) {
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                // $file_name = isset($str_company_code) ? $str_company_code . "_Nonpunched.xlsx" : "_Nonpunched_" . strtotime() . ".xlsx";
                if (isset($arr_form_data['reportfrom']) && isset($arr_form_data['reportto'])) {
                    $reportFrom = $arr_form_data['reportfrom'];
                    $reportTo = $arr_form_data['reportto'];
                    $file_name = isset($str_company_code) ? "{$str_company_code}_Non - Punched - {$reportFrom} - {$reportTo}.xlsx" : "Nonpunched - {$reportFrom} - {$reportTo}.xlsx";
                } else {
                    $file_name = isset($str_company_code) ? "{$str_company_code}_Non - Punched_" . strtotime() . ".xlsx" : "Nonpunched_" . strtotime() . ".xlsx";
                }
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Attendance Register By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $table_count = 0;
                $worksheet->setCellValueByColumnAndRow(0, 1, $heading);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $worksheet->getStyle('A2')->getFont()->setBold(true);
                $worksheet->getStyle('A2')->getFont()->setSize(13);
                $worksheet->mergeCells('A2:J2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                if ($processedData) {
                    $headerStyleArray = [
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'D9E1F2']
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                            'size' => 12
                        ],
                        'alignment' => [
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
                        ],
                        'borders' => [
                            'allborders' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ]
                        ]
                    ];

                    $worksheet->getStyle('A3:J3')->applyFromArray($headerStyleArray);

                    $worksheet->setCellValueByColumnAndRow(0, 3, "Sl No");
                    $worksheet->setCellValueByColumnAndRow(1, 3, "Employee ID");
                    $worksheet->setCellValueByColumnAndRow(2, 3, "User ID");
                    $worksheet->setCellValueByColumnAndRow(3, 3, "Employee Name");
                    $worksheet->setCellValueByColumnAndRow(4, 3, "Joining Date");
                    $worksheet->setCellValueByColumnAndRow(5, 3, "Branch");
                    $worksheet->setCellValueByColumnAndRow(6, 3, "Department");
                    $worksheet->setCellValueByColumnAndRow(7, 3, "Designation");
                    $worksheet->setCellValueByColumnAndRow(8, 3, "Termination Date");
                    // $worksheet->setCellValueByColumnAndRow(9, 3, "Non-Punched Date");

                    $rowIndex = 4;
                    foreach ($processedData as $data) {
                        foreach (range(0, 9) as $colIndex) {
                            $worksheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
                        }
                        $worksheet->setCellValueByColumnAndRow(0, $rowIndex, $data['sl_no']);
                        $worksheet->getStyleByColumnAndRow(0, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(1, $rowIndex, $data['employee_id']);
                        $worksheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(2, $rowIndex, $data['user_id']);
                        $worksheet->getStyleByColumnAndRow(2, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(3, $rowIndex, $data['first_name']);
                        $worksheet->getStyleByColumnAndRow(3, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(4, $rowIndex, $data['joining_date']);
                        $worksheet->getStyleByColumnAndRow(4, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(5, $rowIndex, $data['branch']);
                        $worksheet->getStyleByColumnAndRow(5, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(6, $rowIndex, $data['department']);
                        $worksheet->getStyleByColumnAndRow(6, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(7, $rowIndex, $data['designation']);
                        $worksheet->getStyleByColumnAndRow(7, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $worksheet->setCellValueByColumnAndRow(8, $rowIndex, $data['last_working_date']);
                        $worksheet->getStyleByColumnAndRow(8, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        // $worksheet->setCellValueByColumnAndRow(9, $rowIndex, $data['att_date']);
                        // $worksheet->getStyleByColumnAndRow(9, $rowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        $rowIndex++;
                        $table_count++;
                    }
                }

                // Edited by Akshay on 22-1-2025
                $worksheet->setShowGridlines(false);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                // End

                if ($table_count == 0) {
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $worksheet->mergeCells('A3:J3');
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    );
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'I3')->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'I' . ($rowIndex - 1))->applyFromArray($styleArray); // Edited by Akshay on 22-1-2025
                }

                // }

                $objPHPExcel->getActiveSheet()->setTitle('Non-Punched');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output');
                exit;
            default:
                $this->set('mode', '');
                $this->render('arrears');
                break;
        }
    }

    // edited by anu krishnan 22-01-2025 end

    // Edited by Akshay on 22-1-2025


    // End
}
