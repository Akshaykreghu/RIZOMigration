<?php



class PerformanceController extends AppController
{

    public $uses = array('AppModel', 'CompanyContactInfo', 'ReportAudit', 'ReportCriterias', 'LeaveRequests', 'EmpLeaveApproval', 'EmployeeConfig', 'SalaryHeadItems', 'EmployeeDetails', 'EmployeeLeaveTransaction', 'LeavePolicy', 'EmployeeInfo', 'Category', 'Status');
    public $components = array('Session');


    public function hrreports()
    {
        $arr_reporttypes = array(
            'EmployeeMarks' => 'Employee Marks - Executives',
            'EmployeeMarksStaff' => 'Employee Marks - Staff',
            'SelfAppraisal' => 'Workflow - Executives',
            'StaffAssessment' => 'Workflow - Staff',
            //edited by athira on 09-06-2025
            'AnnualPerformance' => 'Annual Performance Assessment'
            //end
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    public function changereporttype($type = '')
    {
        $this->autoRender = FALSE;
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'EmployeeMarks':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'EmployeeMarksStaff':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                // Edited by Akshay on 22-5-2025
                case 'SelfAppraisal':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'StaffAssessment':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                // End
                //edited by athira on 09-06-2025
                case 'AnnualPerformance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                //end
                default:
                    echo "No criterias found";
                    break;
            }
            // Edited by Akshay on 2-6-2025
            $fin_years = $this->ReportCriterias->query("SELECT DISTINCT fin_year AS fin_year, CONCAT(YEAR(start_month), '-', YEAR(end_month)) AS year_range FROM fin_year WHERE vattr1 = 1 AND status = 1 ORDER BY fin_year DESC;");
            $this->set('fin_years', $fin_years);
            // End
            $this->render('showreport');
        } else {
            echo "No criterias found";
        }
    }

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

    public function listcriteriaitems($str_criteria = '', $report = '')
    {
        $this->autoRender = false;
        $model = $str_criteria;
        $arr_form_data = $_REQUEST;

        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            if ($model == 'EmployeeDetails' || $model == 'Category' || $model == 'Status') {
                $conditions = array("status" => 1);
            }

            if ($model == 'Status') {
                if ($report == 'SelfAppraisal') {
                    $conditions['affecting'] = array('Self', 'All');
                } elseif ($report == 'StaffAssessment') {
                    $conditions['affecting'] = array('Staff', 'All');
                }
            }

            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'EmployeeDetails':
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no, EmployeeDetails.status';
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
                    if (isset($arr_form_data['name']) && $arr_form_data['name'] == 1) {
                        $conditions[] = array('status IN' => array(1, 2));
                    } else {
                        $conditions[] = array('status' => 1);
                    }

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2) {
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
                        'conditions' => $conditions
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;

                case 'Category':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['category_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['category_name'];
                        $key++;
                    }
                    break;
                case 'Status':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['status_name'];
                        $arr_criteriaItems[$key]['text'] = $value['status_desc'];
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
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'EmployeeMarks':
                $dataForHistory['report_type'] = "Employee Marks - Executives";
                break;
            case 'EmployeeMarksStaff':
                $dataForHistory['report_type'] = "Employee Marks - Staff";
                break;
            // Edited by Akshay on 22-5-2025
            case 'SelfAppraisal':
                $dataForHistory['report_type'] = "Workflow - Self Appraisal";
                break;
            case 'StaffAssessment':
                $dataForHistory['report_type'] = "Workflow - Officer Assessment";
                break;
            // End
            //edited by athira on 09-06-2025
            case 'AnnualPerformance':
                $dataForHistory['report_type'] = "Annual Performance Assessment";
                break;
                //end
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
            }
            $items_array[] = isset($arr_form_data[$criteria]) ? implode(",", $arr_form_data[$criteria]) : '';
            $items_count_array[] = isset($arr_form_data[$criteria]) ? count($arr_form_data[$criteria]) : 0;

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
        switch ($type) {
            case 'EmployeeMarks':
                $this->generateEmployeeMarksReport($mode);
                break;
            case 'EmployeeMarksStaff':
                $this->generateEmployeeMarksStaffReport($mode);
                break;
            case 'SelfAppraisal':
                $this->generateSelfAppraisalReport($mode);
                break;
            case 'StaffAssessment':
                $this->generateStaffAssessmentReport($mode);
                break;
            //edited by athira on 09-06-2025
            case 'AnnualPerformance':
                $this->generateAnnualPerformanceAssessment($mode);
                break;
            //end
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


    public function generateEmployeeMarksReport($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $company_info = $this->CompanyContactInfo->query("SELECT business_name,logo,address from comp_contact_info");
        $company_code = $this->Session->read('company_code');

        $this->set('company_info', $company_info);

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();

        $criteria = $arr_form_data['hidden-criteria1']; //Units or EmployeeDetails.
        $this->set('criteria', $criteria);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            if ($criteria == "EmployeeDetails") {           //This is the case of Employee Wise. (EmpolyeeDetails)

                $ids = implode(",", array_map('intval', $arr_leavepolicygroupids));
                $year = $arr_form_data['reportsfrom'];

                $employee_data = $this->EmployeeDetails->query("
                SELECT 
                    emp.employee_id AS employee_id,
                    CASE 
                        WHEN emp_details.status = 2 THEN CONCAT(emp.EmpName, ' (Resigned)')
                        ELSE emp.EmpName
                    END AS employee_name,
                    emp.branch as branch,
                    emp.designation AS employee_designation,
                    emp.department AS department,
                    emp.joining_date,
                    t.last_approved_working_date as termination_date,
                    uc.user_id,

                    officer.employee_id AS officer_id,
                    CONCAT(officer.EmpName, '  ', officer.employee_id) AS officer_name,
                    officer.designation AS officer_designation,

                    a.total_marks,
                    a.grade,

                    CASE 
                        WHEN s.reporting_officer = a.officer_fkey THEN 'Reporting Officer'
                        WHEN s.reviewing_officer = a.officer_fkey THEN 'Reviewing Officer'
                        ELSE 'Other'
                    END AS officer_role

                FROM 
                    assessment_summary_executive a
                JOIN 
                    employee_info emp ON a.emp_fkey = emp.emp_pkey
                LEFT JOIN 
                    termination t ON (emp.emp_pkey = t.emp_fkey AND t.status = 1)
                LEFT JOIN 
                    user_credentials uc ON emp.emp_pkey = uc.emp_fkey
                LEFT JOIN emp_details ON emp.emp_pkey = emp_details.emp_pkey

                JOIN 
                    employee_info officer ON a.officer_fkey = officer.emp_pkey
                JOIN 
                    self_review_details s ON a.emp_fkey = s.emp_fkey
                WHERE 
                    s.status in ('Reviewing Person submitted the Appraisal','Reporting Person submitted the Appraisal')  
                    AND a.emp_fkey IN ($ids)
                    AND s.fin_year = $year
                ORDER BY 
                    emp.emp_pkey, officer_role
            ");



                $grouped_data = [];

                foreach ($employee_data as $row) {
                    $emp_id = $row['emp']['employee_id'];

                    if (!isset($grouped_data[$emp_id])) {

                        $joining_date = isset($row['emp']['joining_date']) && !empty($row['emp']['joining_date'])
                            ? date('d-m-Y', strtotime($row['emp']['joining_date']))
                            : '';

                        $termination_date = isset($row['t']['termination_date']) && !empty($row['t']['termination_date'])
                            ? date('d-m-Y', strtotime($row['t']['termination_date']))
                            : '';

                        $grouped_data[$emp_id] = [
                            'employee_id' => $row['emp']['employee_id'],
                            'employee_name' => trim($row['0']['employee_name']),
                            'branch' => trim($row['emp']['branch']),
                            'employee_designation' => $row['emp']['employee_designation'],
                            'department' => $row['emp']['department'],
                            'joining_date' => $joining_date,
                            'termination_date' => $termination_date,
                            'user_id' => $row['uc']['user_id'],
                            'reporting_officer' => '',
                            'reviewing_officer' => '',
                            'reporting_marks' => '',
                            'reviewing_marks' => '',
                            'grade' => ''
                        ];
                    }

                    $officer_role = $row[0]['officer_role'];

                    if ($officer_role === 'Reporting Officer') {
                        $grouped_data[$emp_id]['reporting_officer'] = trim($row['0']['officer_name']);
                        $grouped_data[$emp_id]['reporting_marks'] = $row['a']['total_marks'];
                    } elseif ($officer_role === 'Reviewing Officer') {
                        $grouped_data[$emp_id]['reviewing_officer'] = trim($row['0']['officer_name']);
                        $grouped_data[$emp_id]['reviewing_marks'] = $row['a']['total_marks'];
                        $grouped_data[$emp_id]['grade'] = $row['a']['grade'];
                    }
                }

                $this->set('grouped_data', $grouped_data);



                switch ($mode) { //There is no need of checking weather mode is pdf or excel. Because it replaced with another method in the reportsummary.ctp (Using of dom:'Bferip')
                    case 'pdf':
                        $this->set('mode', 'pdf');
                        $view = new View($this, false);
                        $view_output = $view->render('pdfemployeemarks');

                        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                        $html2pdf = new HTML2PDF('L', 'A4', 'fr');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->writeHTML($view_output);
                        $html2pdf->Output($company_code . '_' . 'Employee_Marks-Executive' . '.pdf', 'D');

                        break;
                    case 'excel':
                        $file_name = $company_code . '_' . "Employee_Marks-Executive.xlsx";
                        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                        App::import('Vendor', 'PHPExcel_IOFactory', array('file' => 'PHPExcel' . DS . 'IOFactory.php'));
                        App::import('Vendor', 'PHPExcel_Worksheet_Drawing', array('file' => 'PHPExcel' . DS . 'Worksheet' . DS . 'Drawing.php'));

                        $objPHPExcel = new PHPExcel();
                        $objPHPExcel->setActiveSheetIndex(0);
                        $worksheet = $objPHPExcel->getActiveSheet();

                        $worksheet->setShowGridlines(false);
                        // Set specific column widths for better visibility
                        $worksheet->getColumnDimension('A')->setWidth(5);   // SL NO
                        $worksheet->getColumnDimension('B')->setWidth(15);  // Employee ID
                        $worksheet->getColumnDimension('C')->setWidth(15);  // User ID
                        $worksheet->getColumnDimension('D')->setWidth(15);  // Employee Name
                        $worksheet->getColumnDimension('E')->setWidth(12);  // Date of Joining
                        $worksheet->getColumnDimension('F')->setWidth(12);  // Branch
                        $worksheet->getColumnDimension('G')->setWidth(12);  // Department
                        $worksheet->getColumnDimension('H')->setWidth(12);  // Designation
                        $worksheet->getColumnDimension('I')->setWidth(12);  // Termination Date
                        $worksheet->getColumnDimension('J')->setWidth(15);  // Reporting Officer
                        $worksheet->getColumnDimension('K')->setWidth(15);  // Reporting Marks
                        $worksheet->getColumnDimension('L')->setWidth(15);  // Reviewing Officer
                        $worksheet->getColumnDimension('M')->setWidth(15);  // Reviewing Marks
                        $worksheet->getColumnDimension('N')->setWidth(12);  // Grade



                        // Get company info
                        $business_name = $company_info[0]['comp_contact_info']['business_name'];
                        $company_address = $company_info[0]['comp_contact_info']['address'];

                        // --- Business Name ---
                        $worksheet->mergeCells('D1:J1');
                        $worksheet->setCellValue('D1', $business_name);
                        $worksheet->getStyle('D1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('0000FF'); // Blue color
                        $worksheet->getStyle('D1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $worksheet->mergeCells('D2:J2');
                        $worksheet->setCellValue('D2', "(A Govt. of Kerala Public Sector Undertaking)");
                        $worksheet->getStyle('D2')->getFont()->setBold(true)->setSize(16);
                        $worksheet->getStyle('D2:J2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        // --- Address ---
                        $worksheet->mergeCells('D3:J3');
                        $worksheet->setCellValue('D3', $company_address);
                        $worksheet->getStyle('D3')->getFont()->setSize(12);
                        $worksheet->getStyle('D3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $worksheet->mergeCells('D4:J4');
                        $worksheet->setCellValue('D4', "Employee Marks Report");
                        $worksheet->getStyle('D4')->getFont()->setBold(true)->setSize(16);
                        $worksheet->getStyle('D4:J4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                        $rowcount = 6; // Start data after logo/header



                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Date of Joining');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Reporting Officer');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Total Mark by Reporting Officer (out of 100)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Reviewing Officer');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Total Mark by Reviewing Officer (out of 100)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Grade');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);


                        $rowcount = 7;
                        $sl_no = 1;

                        foreach ($grouped_data as $data) {
                            $col = 0;

                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $sl_no++);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_id']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['user_id']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_name']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['joining_date']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['branch']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['department']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_designation']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['termination_date']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reporting_officer']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reporting_marks']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reviewing_officer']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reviewing_marks']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['grade']);

                            $rowcount++;
                        }




                        $lastRow = $rowcount - 1;


                        $worksheet->getStyle("A7:A$lastRow")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $cellRange = 'A6:N' . $lastRow;

                        // Enable text wrapping for all columns and rows (A8 to N[lastRow])
                        $worksheet->getStyle("A6:N$lastRow")->getAlignment()->setWrapText(true);


                        // Center align header text vertically and horizontally
                        $worksheet->getStyle("A6:N$lastRow")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        // $worksheet->getStyle('A8:N8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        // Optional: Increase row height for headers to fit wrapped text
                        $worksheet->getRowDimension(6)->setRowHeight(60);

                        $styleArray = [
                            'borders' => [
                                'allborders' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ];
                        // Center align marks columns K and M
                        $worksheet->getStyle("K7:K$lastRow")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                        $worksheet->getStyle("M7:M$lastRow")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


                        $worksheet->getStyle($cellRange)->applyFromArray($styleArray);


                        // $rowcount++; // Move to next row for note
                        // $worksheet->mergeCells("A$rowcount:N$rowcount");
                        // $worksheet->setCellValue("A$rowcount", "*Here Grade shows based on mark given by Reviewing Officer");

                        // Style the note without border
                        // $worksheet->getStyle("A$rowcount")->getFont()->setBold(false)->setSize(10)->getColor()->setRGB('FF0000');
                        // $worksheet->getStyle("A$rowcount")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                        $objPHPExcel->getActiveSheet()->setTitle('Employee Marks');


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
                        $this->render('reportsummary');
                        break;
                }
            }
        }
    }


    public function generateEmployeeMarksStaffReport($mode)
    {
        $arr_form_data = $_REQUEST;
        $year = $arr_form_data['reportsfrom'];
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $company_info = $this->CompanyContactInfo->query("SELECT business_name,logo,address from comp_contact_info");
        $company_code = $this->Session->read('company_code');

        $this->set('company_info', $company_info);

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();

        $criteria = $arr_form_data['hidden-criteria1']; //Units or EmployeeDetails.
        $this->set('criteria', $criteria);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            if ($criteria == "EmployeeDetails") {           //This is the case of Employee Wise. (EmpolyeeDetails)

                $ids = implode(",", array_map('intval', $arr_leavepolicygroupids));

                $employee_data = $this->EmployeeDetails->query("
                SELECT 
                    emp.employee_id AS employee_id,
                    CASE 
                        WHEN emp_details.status = 2 THEN CONCAT(emp.EmpName, ' (Resigned)')
                        ELSE emp.EmpName
                    END AS employee_name,
                    emp.branch as branch,
                    emp.designation AS employee_designation,
                    emp.department AS department,
                    emp.joining_date,
                    t.last_approved_working_date as termination_date,
                    uc.user_id,

                    reporting_officer.employee_id AS reporting_officer_id,
                    CONCAT(reporting_officer.EmpName, ' ', reporting_officer.employee_id) AS reporting_officer_name,
                    reporting_officer.designation AS reporting_officer_designation,

                    reviewing_officer.employee_id AS reviewing_officer_id,
                    CONCAT(reviewing_officer.EmpName, ' ', reviewing_officer.employee_id) AS reviewing_officer_name,
                    reviewing_officer.designation AS reviewing_officer_designation,

                    aasd.reporting_officer_marks,
                    aasd.reporting_officer_grade,
                    aasd.reviewing_officer_marks,
                    aasd.reviewing_officer_grade

                FROM 
                    assessment_attributes_staff_details aasd
                JOIN 
                    employee_info emp ON aasd.emp_fkey = emp.emp_pkey
                LEFT JOIN 
                    termination t ON (emp.emp_pkey = t.emp_fkey AND t.status = 1)
                LEFT JOIN 
                    user_credentials uc ON emp.emp_pkey = uc.emp_fkey
                LEFT JOIN 
                
                    employee_info reporting_officer ON aasd.reporting_officer = reporting_officer.emp_pkey
                    LEFT JOIN emp_details ON emp.emp_pkey = emp_details.emp_pkey

                LEFT JOIN 
                    employee_info reviewing_officer ON aasd.reviewing_officer = reviewing_officer.emp_pkey
                WHERE 
                    aasd.emp_fkey IN ($ids) AND aasd.status=3 AND aasd.fin_year='$year'
            ");

                $grouped_data = [];

                foreach ($employee_data as $row) {
                    $emp_id = $row['emp']['employee_id'];

                    $joining_date = isset($row['emp']['joining_date']) && !empty($row['emp']['joining_date'])
                        ? date('d-m-Y', strtotime($row['emp']['joining_date']))
                        : '';

                    $termination_date = isset($row['t']['termination_date']) && !empty($row['t']['termination_date'])
                        ? date('d-m-Y', strtotime($row['t']['termination_date']))
                        : '';

                    $grouped_data[$emp_id] = [
                        'employee_id' => $row['emp']['employee_id'],
                        'employee_name' => trim($row['0']['employee_name']),
                        'branch' => trim($row['emp']['branch']),
                        'employee_designation' => $row['emp']['employee_designation'],
                        'department' => $row['emp']['department'],
                        'joining_date' => $joining_date,
                        'termination_date' => $termination_date,
                        'user_id' => $row['uc']['user_id'],
                        'reporting_officer' => trim($row['0']['reporting_officer_name']),
                        'reviewing_officer' => trim($row['0']['reviewing_officer_name']),
                        'reporting_marks' => $row['aasd']['reporting_officer_marks'],
                        'reviewing_marks' => $row['aasd']['reviewing_officer_marks'],
                        'grade' => $row['aasd']['reporting_officer_grade'], // Use reviewing grade for final grade
                    ];
                }

                $this->set('grouped_data', $grouped_data);






                switch ($mode) { //There is no need of checking weather mode is pdf or excel. Because it replaced with another method in the reportsummary.ctp (Using of dom:'Bferip')
                    case 'pdf':
                        $this->set('mode', 'pdf');
                        $view = new View($this, false);
                        $view_output = $view->render('pdfemployeemarks');

                        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                        $html2pdf = new HTML2PDF('L', 'A4', 'fr');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->writeHTML($view_output);
                        $html2pdf->Output($company_code . '_' . 'Employee_Marks-Staff' . '.pdf', 'D');

                        break;
                    case 'excel':
                        $file_name = $company_code . '_' . "Employee_Marks-Staff.xlsx";
                        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                        App::import('Vendor', 'PHPExcel_IOFactory', array('file' => 'PHPExcel' . DS . 'IOFactory.php'));
                        App::import('Vendor', 'PHPExcel_Worksheet_Drawing', array('file' => 'PHPExcel' . DS . 'Worksheet' . DS . 'Drawing.php'));

                        $objPHPExcel = new PHPExcel();
                        $objPHPExcel->setActiveSheetIndex(0);
                        $worksheet = $objPHPExcel->getActiveSheet();

                        $worksheet->setShowGridlines(false);
                        // Set specific column widths for better visibility
                        $worksheet->getColumnDimension('A')->setWidth(5);   // SL NO
                        $worksheet->getColumnDimension('B')->setWidth(15);  // Employee ID
                        $worksheet->getColumnDimension('C')->setWidth(15);  // User ID
                        $worksheet->getColumnDimension('D')->setWidth(15);  // Employee Name
                        $worksheet->getColumnDimension('E')->setWidth(12);  // Date of Joining
                        $worksheet->getColumnDimension('F')->setWidth(12);  // Branch
                        $worksheet->getColumnDimension('G')->setWidth(12);  // Department
                        $worksheet->getColumnDimension('H')->setWidth(12);  // Designation
                        $worksheet->getColumnDimension('I')->setWidth(12);  // Termination Date
                        $worksheet->getColumnDimension('J')->setWidth(15);  // Reporting Officer
                        $worksheet->getColumnDimension('K')->setWidth(15);  // Reporting Marks
                        $worksheet->getColumnDimension('L')->setWidth(15);  // Reviewing Officer
                        $worksheet->getColumnDimension('M')->setWidth(15);  // Reviewing Marks
                        $worksheet->getColumnDimension('N')->setWidth(12);  // Grade

                        // Get company info
                        $business_name = $company_info[0]['comp_contact_info']['business_name'];
                        $company_address = $company_info[0]['comp_contact_info']['address'];

                        // --- Business Name ---
                        $worksheet->mergeCells('D1:J1');
                        $worksheet->setCellValue('D1', $business_name);
                        $worksheet->getStyle('D1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('0000FF'); // Blue color
                        $worksheet->getStyle('D1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $worksheet->mergeCells('D2:J2');
                        $worksheet->setCellValue('D2', "(A Govt. of Kerala Public Sector Undertaking)");
                        $worksheet->getStyle('D2')->getFont()->setBold(true)->setSize(16);
                        $worksheet->getStyle('D2:J2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        // --- Address ---
                        $worksheet->mergeCells('D3:J3');
                        $worksheet->setCellValue('D3', $company_address);
                        $worksheet->getStyle('D3')->getFont()->setSize(12);
                        $worksheet->getStyle('D3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $worksheet->mergeCells('D4:J4');
                        $worksheet->setCellValue('D4', "Employee Marks Report");
                        $worksheet->getStyle('D4')->getFont()->setBold(true)->setSize(16);
                        $worksheet->getStyle('D4:J4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                        $rowcount = 6; // Start data after logo/header



                        $columncount = 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Date of Joining');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Reporting Officer');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Total Mark by Reporting Officer(out of 100)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Reviewing Officer');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Total Mark by Reviewing Officer(out of 100)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Grade');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);


                        $rowcount = 7;
                        $sl_no = 1;

                        foreach ($grouped_data as $data) {
                            $col = 0;

                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $sl_no++);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_id']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['user_id']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_name']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['joining_date']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['branch']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['department']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_designation']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['termination_date']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reporting_officer']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reporting_marks']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reviewing_officer']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reviewing_marks']);
                            $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['grade']);

                            $rowcount++;
                        }

                        $lastRow = $rowcount - 1;

                        $worksheet->getStyle("A7:A$lastRow")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        $cellRange = 'A6:N' . $lastRow;

                        // Enable text wrapping for all columns and rows (A8 to N[lastRow])
                        $worksheet->getStyle("A6:N$lastRow")->getAlignment()->setWrapText(true);


                        // Center align header text vertically and horizontally
                        $worksheet->getStyle("A6:N$lastRow")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        // $worksheet->getStyle('A8:N8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        // Optional: Increase row height for headers to fit wrapped text
                        $worksheet->getRowDimension(6)->setRowHeight(60);

                        $styleArray = [
                            'borders' => [
                                'allborders' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ];
                        // Center align marks columns K and M
                        $worksheet->getStyle("K7:K$lastRow")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                        $worksheet->getStyle("M7:M$lastRow")->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


                        $worksheet->getStyle($cellRange)->applyFromArray($styleArray);

                        // $rowcount++; // Move to next row for note
                        // $worksheet->mergeCells("A$rowcount:N$rowcount");
                        // $worksheet->setCellValue("A$rowcount", "*Here Grade shows based on mark given by Reporting Officer");

                        // Style the note without border
                        // $worksheet->getStyle("A$rowcount")->getFont()->setBold(false)->setSize(10)->getColor()->setRGB('FF0000');
                        // $worksheet->getStyle("A$rowcount")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);




                        $objPHPExcel->getActiveSheet()->setTitle('Employee Marks');



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
                        $this->render('reportsummary');
                        break;
                }
            }
        }
    }

    // Edited by Akshay on 22-5-2025
    public function generateSelfAppraisalReport($mode)
    {
        try {
            $arr_form_data = $_REQUEST;
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

            $company_info = $this->CompanyContactInfo->query("SELECT business_name,logo,address from comp_contact_info");

            $this->set('company_info', $company_info);

            $arr_leavepolicygroupids = array();
            $int_criterias_count = $arr_form_data['hidden-criterias-count'];
            for ($i = 1; $i <= $int_criterias_count; $i++) {
                $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
                $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            }
            $arr_leavepolicydetails_for_template = array();

            $criteria = $arr_form_data['hidden-criteria1']; //Units or EmployeeDetails.

            $this->set('criteria', $criteria);
            if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
                if (isset($criteria)) {

                    $ids = implode(",", array_map('intval', $arr_leavepolicygroupids));

                    $condition = " ";
                    if ($criteria == "EmployeeDetails") {
                        $condition = " AND s.emp_fkey IN ($ids) ";
                    } elseif ($criteria == 'Category') {
                        $condition = " AND s.emp_fkey IN ( SELECT emp_fkey FROM emp_proff WHERE emp_grade IN (SELECT grade_pkey FROM grade WHERE category_fkey IN ($ids))  ) ";
                    } elseif ($criteria == 'Status') {
                        // $condition = 
                        if (in_array('New', $arr_leavepolicygroupids)) {
                            $condition = " ";
                        } elseif (in_array('Applied', $arr_leavepolicygroupids)) {
                            $condition = " AND s.status IN ('Applied', 'Reporting person submitted the appraisal', 'Reviewing Person submitted the appraisal', 'Reporting person Rejected the appraisal', 'Reviewing person Rejected the appraisal', 'Reporting Person Drafted the Appraisal', 'Reviewing Person Drafted the Appraisal') ";
                        } elseif (in_array('Approved', $arr_leavepolicygroupids)) {
                            $condition = " AND s.status IN ('Reporting person submitted the appraisal', 'Reviewing Person submitted the appraisal', 'Reporting person Rejected the appraisal', 'Reviewing person Rejected the appraisal', 'Reviewing Person Drafted the Appraisal') ";
                        } elseif (in_array('Authorized', $arr_leavepolicygroupids)) {
                            $condition = " AND s.status IN ('Reviewing Person submitted the appraisal') ";
                        }
                    }

                    $is_resigned = $arr_form_data['resigned'];

                    if ($is_resigned == 0) {
                        $condition .= "AND emp.emp_status = 1 ";
                    }

                    $year = $arr_form_data['reportsfrom'];

                    $employee_data = $this->EmployeeDetails->query("
                                                                        SELECT 
                                                                            emp.employee_id AS employee_id,
                                                                            emp.EmpName AS employee_name,
                                                                            emp.emp_status AS status,
                                                                            emp.branch as branch,
                                                                            emp.designation AS employee_designation,
                                                                            emp.department AS department,
                                                                            emp.joining_date,
                                                                            t.last_approved_working_date as termination_date,
                                                                            uc.user_id,
                                                                            s.status,
                                                                            s.applied_date,
                                                                            s.reported_date,
                                                                            s.reviewed_date,
                                                                            s.drafted_date,
                                                                            s.applied_date,
                                                                            s.reported_date,
                                                                            s.reviewed_date,
                                                                            s.rejected_date


                                                                            FROM 
                                                                                self_review_details s
                                                                            LEFT JOIN 
                                                                                employee_info emp ON s.emp_fkey = emp.emp_pkey
                                                                            LEFT JOIN 
                                                                                termination t ON (emp.emp_pkey = t.emp_fkey AND t.status = 1)
                                                                            LEFT JOIN 
                                                                                user_credentials uc ON emp.emp_pkey = uc.emp_fkey
                                                                        WHERE 
                                                                            s.status != 'Deleted'
                                                                            AND s.fin_year = $year
                                                                            $condition
                                                                            
                                                                        ORDER BY 
                                                                            emp.EmpName
                                                            ");

                    $year = $arr_form_data['reportsfrom_text'];
                    $this->set('year', $year);
                    $grouped_data = [];

                    foreach ($employee_data as $row) {
                        $emp_id = $row['emp']['employee_id'];

                        if (!isset($grouped_data[$emp_id])) {

                            $joining_date = isset($row['emp']['joining_date']) && !empty($row['emp']['joining_date'])
                                ? date('d-m-Y', strtotime($row['emp']['joining_date']))
                                : '';

                            $termination_date = isset($row['t']['termination_date']) && !empty($row['t']['termination_date'])
                                ? date('d-m-Y', strtotime($row['t']['termination_date']))
                                : '';

                            $status = isset($row['s']['status']) ? $row['s']['status'] : '';

                            // if (!in_array($status, ['Draft', 'Drafted', 'New'])) {
                            //     $self_appr_status = 'Completed';
                            //     $self_appr_date = isset($row['s']['applied_date']) && !empty($row['s']['applied_date'])
                            //         ? date('d-m-Y H:i:s', strtotime($row['s']['applied_date']))
                            //         : '';
                            // } else {
                            //     $self_appr_status = 'Not Completed';
                            // }

                            // if (!in_array($status, ['Draft', 'Drafted', 'New', 'Applied', 'Reporting person Rejected the appraisal','Reporting Person Drafted the Appraisal'])) {
                            //     $reporting_status = 'Completed';
                            //     $reporting_date = isset($row['s']['reported_date']) && !empty($row['s']['reported_date'])
                            //         ? date('d-m-Y H:i:s', strtotime($row['s']['reported_date']))
                            //         : '';
                            // } else {
                            //     $reporting_status = 'Not Completed';
                            // }

                            // if (!in_array($status, ['Draft', 'Drafted', 'New', 'Applied', 'Reporting person Rejected the appraisal', 'Reviewing person Rejected the appraisal', 'Reporting Person submitted the Appraisal', 'Reporting Person Drafted the Appraisal', 'Reviewing Person Drafted the Appraisal'])) {
                            //     $reviewing_status = $completion_status = 'Completed';
                            //     $reviewing_date = isset($row['s']['reviewed_date']) && !empty($row['s']['reviewed_date'])
                            //         ? date('d-m-Y H:i:s', strtotime($row['s']['reviewed_date']))
                            //         : '';
                            // } else {
                            //     $reviewing_status = $completion_status =  'Not Completed';
                            // }

                            // Edited by Akshay on 30-5-2025
                            $status_lower = strtolower($status);

                            if (!in_array($status_lower, array_map('strtolower', ['Draft', 'Drafted', 'New']))) {
                                $self_appr_status = 'Completed';
                                $self_appr_date = isset($row['s']['applied_date']) && !empty($row['s']['applied_date'])
                                    ? date('d-m-Y H:i:s', strtotime($row['s']['applied_date']))
                                    : '';
                            } else {
                                $self_appr_status = 'Not Completed';
                            }

                            if (!in_array($status_lower, array_map('strtolower', [
                                'Draft',
                                'Drafted',
                                'New',
                                'Applied',
                                'Reporting person Rejected the appraisal',
                                'Reporting Person Drafted the Appraisal'
                            ]))) {
                                $reporting_status = 'Completed';
                                $reporting_date = isset($row['s']['reported_date']) && !empty($row['s']['reported_date'])
                                    ? date('d-m-Y H:i:s', strtotime($row['s']['reported_date']))
                                    : '';
                            } else {
                                $reporting_status = 'Not Completed';
                            }

                            if (!in_array($status_lower, array_map('strtolower', [
                                'Draft',
                                'Drafted',
                                'New',
                                'Applied',
                                'Reporting person Rejected the appraisal',
                                'Reviewing person Rejected the appraisal',
                                'Reporting Person submitted the Appraisal',
                                'Reporting Person Drafted the Appraisal',
                                'Reviewing Person Drafted the Appraisal'
                            ]))) {
                                $reviewing_status = $completion_status = 'Completed';
                                $reviewing_date = isset($row['s']['reviewed_date']) && !empty($row['s']['reviewed_date'])
                                    ? date('d-m-Y H:i:s', strtotime($row['s']['reviewed_date']))
                                    : '';
                            } else {
                                $reviewing_status = $completion_status = 'Not Completed';
                            }

                            // End


                            $grouped_data[$emp_id] = [
                                'employee_id'          => isset($row['emp']['employee_id']) ? $row['emp']['employee_id'] : '',
                                'employee_name' => isset($row['emp']['employee_name'])
                                    ? trim($row['emp']['employee_name']) .
                                    (isset($row['emp']['status']) && $row['emp']['status'] == 2 ? ' (Resigned)' : '')
                                    : '',

                                'branch'               => isset($row['emp']['branch']) ? trim($row['emp']['branch']) : '',
                                'employee_designation' => isset($row['emp']['employee_designation']) ? $row['emp']['employee_designation'] : '',
                                'department'           => isset($row['emp']['department']) ? $row['emp']['department'] : '',
                                'joining_date'         => isset($joining_date) ? $joining_date : '',
                                'termination_date'     => isset($termination_date) ? $termination_date : '',
                                'user_id'              => isset($row['uc']['user_id']) ? $row['uc']['user_id'] : '',
                                'self_appr_status'     => isset($self_appr_status) ? $self_appr_status : '',
                                'self_appr_date'       => isset($self_appr_date) ? $self_appr_date : '',
                                'reporting_status'     => isset($reporting_status) ? $reporting_status : '',
                                'reporting_date'       => isset($reporting_date) ? $reporting_date : '',
                                'reviewing_status'     => isset($reviewing_status) ? $reviewing_status : '',
                                'reviewing_date'       => isset($reviewing_date) ? $reviewing_date : '',
                                'completion_status'    => isset($completion_status) ? $completion_status : ''
                            ];
                        }
                    }

                    $this->set('grouped_data', $grouped_data);

                    $user_id = $this->Session->read('login_user_id');
                    $date_time = date('d-m-Y H:i');
                    $this->set('user_id', $user_id);
                    $this->set('date_time', $date_time);

                    switch ($mode) { //There is no need of checking weather mode is pdf or excel. Because it replaced with another method in the reportsummary.ctp (Using of dom:'Bferip')
                        case 'pdf':
                            $this->set('mode', 'pdf');
                            $view = new View($this, false);
                            $view_output = $view->render('pdfemployeemarks');

                            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                            $html2pdf = new HTML2PDF('L', 'A4', 'fr');
                            $html2pdf->pdf->SetDisplayMode('fullpage');
                            $html2pdf->writeHTML($view_output);
                            $html2pdf->Output('Employee Mark' . '.pdf', 'D');

                            break;
                        case 'excel':
                            $str_company_code = strtoupper($this->Session->read('company_code'));
                            $file_name = isset($str_company_code) ? $str_company_code . "_Workflow - Self Appraisal_$year.xlsx" : "Workflow - Self Appraisal_$year.xlsx";
                            App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                            App::import('Vendor', 'PHPExcel_IOFactory', array('file' => 'PHPExcel' . DS . 'IOFactory.php'));
                            App::import('Vendor', 'PHPExcel_Worksheet_Drawing', array('file' => 'PHPExcel' . DS . 'Worksheet' . DS . 'Drawing.php'));

                            $objPHPExcel = new PHPExcel();
                            $objPHPExcel->setActiveSheetIndex(0);
                            $worksheet = $objPHPExcel->getActiveSheet();

                            $worksheet->setShowGridlines(false);
                            // Set specific column widths for better visibility
                            $worksheet->getColumnDimension('A')->setWidth(5);   // SL NO
                            $worksheet->getColumnDimension('B')->setWidth(15);  // Employee ID
                            $worksheet->getColumnDimension('C')->setWidth(15);  // User ID
                            $worksheet->getColumnDimension('D')->setWidth(20);  // Employee Name
                            $worksheet->getColumnDimension('E')->setWidth(14);  // Date of Joining
                            $worksheet->getColumnDimension('F')->setWidth(20);  // Branch
                            $worksheet->getColumnDimension('G')->setWidth(20);  // Department
                            $worksheet->getColumnDimension('H')->setWidth(23);  // Designation
                            $worksheet->getColumnDimension('I')->setWidth(12);  // Termination Date
                            $worksheet->getColumnDimension('J')->setWidth(20);  // Self Appraisal Status
                            $worksheet->getColumnDimension('K')->setWidth(15);  // Self Appraisal Submitted Date and Time
                            $worksheet->getColumnDimension('L')->setWidth(20);  // Reporting Officer Assessment Status
                            $worksheet->getColumnDimension('M')->setWidth(15);  // Reporting Officer Assessment Submission Date & Time
                            $worksheet->getColumnDimension('N')->setWidth(20);  // Reviewing Officer Assessment Status
                            $worksheet->getColumnDimension('O')->setWidth(15);  // Reviewing Officer Assessment Submission Date & Time
                            $worksheet->getColumnDimension('P')->setWidth(25);  // Completion Status



                            // Get company info
                            $business_name = $company_info[0]['comp_contact_info']['business_name'];
                            $company_address = $company_info[0]['comp_contact_info']['address'];

                            // --- Business Name ---
                            $worksheet->mergeCells('F1:L1');
                            $worksheet->setCellValue('F1', $business_name);
                            $worksheet->getStyle('F1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('0000FF'); // Blue color
                            $worksheet->getStyle('F1:L1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            $worksheet->mergeCells('F2:L2');
                            $worksheet->setCellValue('F2', "(A Govt. of Kerala Public Sector Undertaking)");
                            $worksheet->getStyle('F2')->getFont()->setBold(true)->setSize(16);
                            $worksheet->getStyle('F2:L2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            // --- Address ---
                            $worksheet->mergeCells('F3:L3');
                            $worksheet->setCellValue('F3', $company_address);
                            $worksheet->getStyle('F3')->getFont()->setSize(12);
                            $worksheet->getStyle('F3:L3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            $worksheet->mergeCells('F4:L4');
                            $worksheet->setCellValue('F4', "Workflow - Self Appraisal " . $year);
                            $worksheet->getStyle('F4')->getFont()->setBold(true)->setSize(16);
                            $worksheet->getStyle('F4:L4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


                            $worksheet->mergeCells('F5:L5');
                            $worksheet->setCellValue('F5', "(Report Run by " . $user_id . " at " . $date_time . ")"); // Date and time
                            $worksheet->getStyle('F5')->getFont()->setBold(true)->setSize(16);
                            $worksheet->getStyle('F5:L5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                            $rowcount = 6; // Start data after logo/header
                            $columncount = 0;

                            if (!empty($grouped_data)) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Date of Joining');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Self Appraisal Status');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Self Appraisal Submitted Date and Time');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Reporting Officer Assessment Status');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Reporting Officer Assessment Submission Date & Time');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Reviewing Officer Assessment Status');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'Reviewing Officer Assessment Submission Date & Time');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Completion Status');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);


                                $rowcount = 7;
                                $sl_no = 1;

                                foreach ($grouped_data as $data) {
                                    $col = 0;

                                    // Format joining_date as d-m-Y or leave blank
                                    $joining_date = isset($data['joining_date']) && !empty($data['joining_date'])
                                        ? date('d-m-Y', strtotime($data['joining_date']))
                                        : '';
                                    // Format termination_date as d-m-Y or leave blank
                                    $termination_date = isset($data['termination_date']) && !empty($data['termination_date'])
                                        ? date('d-m-Y', strtotime($data['termination_date']))
                                        : '';

                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $sl_no++);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_id']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['user_id']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_name']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $joining_date);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['branch']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['department']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_designation']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $termination_date);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['self_appr_status']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['self_appr_date']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reporting_status']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reporting_date']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reviewing_status']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reviewing_date']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['completion_status']);


                                    $rowcount++;
                                }
                            } else {
                                $worksheet->mergeCells('A5:L5');
                                $worksheet->setCellValue('A5', "No data available under the selected criteria.");
                            }



                            $lastRow = $rowcount - 1;
                            $cellRange = 'A6:P' . $lastRow;

                            // Enable text wrapping for all columns and rows (A8 to N[lastRow])
                            $worksheet->getStyle("A6:P$lastRow")->getAlignment()->setWrapText(true);


                            // Center align header text vertically and horizontally
                            $worksheet->getStyle("A6:P$lastRow")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                            // $worksheet->getStyle('A8:N8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            // Optional: Increase row height for headers to fit wrapped text
                            $worksheet->getRowDimension(6)->setRowHeight(100);

                            $styleArray = [
                                'borders' => [
                                    'allborders' => [
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => ['argb' => 'FF000000'],
                                    ],
                                ],
                            ];
                            // Center align marks columns K and M
                            $worksheet->getStyle("A7:P$lastRow")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                            // $worksheet->getStyle("M7:M$lastRow")->getAlignment()
                            //     ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            //     ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


                            $worksheet->getStyle($cellRange)->applyFromArray($styleArray);


                            $objPHPExcel->getActiveSheet()->setTitle('Workflow - Self Appraisal');


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
                            $this->render('self_review');
                            break;
                    }
                }
            } else {
                echo "<div><h2>Please Choose Criteria Item First</h2></div>";
            }
        } catch (Exception $e) {
            debug($e);
        }
    }

    public function generateStaffAssessmentReport($mode)
    {
        try {
            $arr_form_data = $_REQUEST;
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

            $company_info = $this->CompanyContactInfo->query("SELECT business_name,logo,address from comp_contact_info");

            $this->set('company_info', $company_info);

            $arr_leavepolicygroupids = array();
            $int_criterias_count = $arr_form_data['hidden-criterias-count'];
            for ($i = 1; $i <= $int_criterias_count; $i++) {
                $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
                $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            }
            $arr_leavepolicydetails_for_template = array();

            $criteria = $arr_form_data['hidden-criteria1']; //Units or EmployeeDetails.

            $this->set('criteria', $criteria);
            if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
                if (isset($criteria)) {

                    $ids = implode(",", array_map('intval', $arr_leavepolicygroupids));

                    $condition = " ";
                    if ($criteria == "EmployeeDetails") {
                        $condition = " AND a.emp_fkey IN ($ids) ";
                    } elseif ($criteria == 'Category') {
                        $condition = " AND a.emp_fkey IN ( SELECT emp_fkey FROM emp_proff WHERE emp_grade IN (SELECT grade_pkey FROM grade WHERE category_fkey IN ($ids))  ) ";
                    } elseif ($criteria == 'Status') {
                        // $condition = 
                        if (in_array(5, $arr_leavepolicygroupids)) {
                            $condition = " ";
                        } elseif (in_array(2, $arr_leavepolicygroupids)) {
                            $condition = " AND a.status IN (2,3,4) ";
                        } elseif (in_array(3, $arr_leavepolicygroupids)) {
                            $condition = " AND a.status = 3 ";
                        }
                    }

                    $is_resigned = $arr_form_data['resigned'];

                    if ($is_resigned == 0) {
                        $condition .= " AND emp.emp_status = 1 ";
                    } else {
                        $condition .= " AND emp.emp_status IN('1', '2') ";
                    }

                    $year = $arr_form_data['reportsfrom'];

                    $employee_data = $this->EmployeeDetails->query("
                                                                SELECT 
                                                                    emp.employee_id AS employee_id,
                                                                    emp.EmpName AS employee_name,
                                                                    emp.emp_status AS status,
                                                                    emp.branch as branch,
                                                                    emp.designation AS employee_designation,
                                                                    emp.department AS department,
                                                                    emp.joining_date,
                                                                    t.last_approved_working_date as termination_date,
                                                                    uc.user_id,

                                                                    a.status,
                                                                    a.reported_date,
                                                                    a.reviewed_date,
                                                                    a.drafted_date,
                                                                    a.reported_date,
                                                                    a.reviewed_date,
                                                                    a.rejected_date

                                                                    FROM 
                                                                         assessment_attributes_staff_details a
                                                                    LEFT JOIN 
                                                                        employee_info emp ON a.emp_fkey = emp.emp_pkey
                                                                    LEFT JOIN 
                                                                        termination t ON (emp.emp_pkey = t.emp_fkey AND t.status = 1)
                                                                    LEFT JOIN 
                                                                        user_credentials uc ON emp.emp_pkey = uc.emp_fkey
                                                                WHERE 
                                                                    a.status != 0
                                                                    $condition
                                                                    AND a.fin_year = $year
                                                                GROUP BY a.attr_staff_details_pkey
                                                                ORDER BY 
                                                                    emp.EmpName
                                                            ");

                    $year = $arr_form_data['reportsfrom_text'];
                    $this->set('year', $year);
                    foreach ($employee_data as $row) {
                        $emp_id = $row['emp']['employee_id'];

                        if (!isset($grouped_data[$emp_id])) {

                            $joining_date = isset($row['emp']['joining_date']) && !empty($row['emp']['joining_date'])
                                ? date('d-m-Y', strtotime($row['emp']['joining_date']))
                                : '';

                            $termination_date = isset($row['t']['termination_date']) && !empty($row['t']['termination_date'])
                                ? date('d-m-Y', strtotime($row['t']['termination_date']))
                                : '';

                            $status = isset($row['a']['status']) ? $row['a']['status'] : '';

                            if (in_array($status, ['2', '3', '4'])) {
                                $reporting_status = 'Completed';
                                $reporting_date = isset($row['a']['reported_date']) && !empty($row['a']['reported_date'])
                                    ? date('d-m-Y H:i:s', strtotime($row['a']['reported_date']))
                                    : '';
                            } else {
                                $reporting_status = 'Not Completed';
                            }

                            if ($status == 3) {
                                $reviewing_status = $completion_status = 'Completed';
                                $reviewing_date = isset($row['a']['reviewed_date']) && !empty($row['a']['reviewed_date'])
                                    ? date('d-m-Y H:i:s', strtotime($row['a']['reviewed_date']))
                                    : '';
                            } else {
                                $reviewing_status = $completion_status =  'Not Completed';
                            }


                            $grouped_data[$emp_id] = [
                                'employee_id'            => isset($row['emp']['employee_id']) ? $row['emp']['employee_id'] : '',
                                'employee_name' => isset($row['emp']['employee_name'])
                                    ? trim($row['emp']['employee_name']) .
                                    (isset($row['emp']['status']) && $row['emp']['status'] == 2 ? ' (Resigned)' : '')
                                    : '',
                                'branch'                 => isset($row['emp']['branch']) ? trim($row['emp']['branch']) : '',
                                'employee_designation'   => isset($row['emp']['employee_designation']) ? $row['emp']['employee_designation'] : '',
                                'department'             => isset($row['emp']['department']) ? $row['emp']['department'] : '',
                                'joining_date'           => isset($joining_date) ? $joining_date : '',
                                'termination_date'       => isset($termination_date) ? $termination_date : '',
                                'user_id'                => isset($row['uc']['user_id']) ? $row['uc']['user_id'] : '',
                                'reporting_status'       => isset($reporting_status) ? $reporting_status : '',
                                'reporting_date'         => isset($reporting_date) ? $reporting_date : '',
                                'reviewing_status'       => isset($reviewing_status) ? $reviewing_status : '',
                                'reviewing_date'         => isset($reviewing_date) ? $reviewing_date : '',
                                'completion_status'      => isset($completion_status) ? $completion_status : ''
                            ];
                        }
                    }

                    if (isset($grouped_data)) {
                        $this->set('grouped_data', $grouped_data);
                    }

                    $user_id = $this->Session->read('login_user_id');
                    $date_time = date('d-m-Y H:i');
                    $this->set('user_id', $user_id);
                    $this->set('date_time', $date_time);

                    switch ($mode) { //There is no need of checking weather mode is pdf or excel. Because it replaced with another method in the reportsummary.ctp (Using of dom:'Bferip')
                        case 'pdf':
                            $this->set('mode', 'pdf');
                            $view = new View($this, false);
                            $view_output = $view->render('pdfemployeemarks');

                            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                            $html2pdf = new HTML2PDF('L', 'A4', 'fr');
                            $html2pdf->pdf->SetDisplayMode('fullpage');
                            $html2pdf->writeHTML($view_output);
                            $html2pdf->Output('Employee Mark' . '.pdf', 'D');

                            break;
                        case 'excel':
                            $str_company_code = strtoupper($this->Session->read('company_code'));
                            $file_name = isset($str_company_code) ? $str_company_code . "_Workflow - Officer Assessment_$year.xlsx" : "Workflow - Officer Assessment_$year.xlsx";
                            App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                            App::import('Vendor', 'PHPExcel_IOFactory', array('file' => 'PHPExcel' . DS . 'IOFactory.php'));
                            App::import('Vendor', 'PHPExcel_Worksheet_Drawing', array('file' => 'PHPExcel' . DS . 'Worksheet' . DS . 'Drawing.php'));

                            $objPHPExcel = new PHPExcel();
                            $objPHPExcel->setActiveSheetIndex(0);
                            $worksheet = $objPHPExcel->getActiveSheet();

                            $worksheet->setShowGridlines(false);
                            // Set specific column widths for better visibility
                            $worksheet->getColumnDimension('A')->setWidth(5);   // SL NO
                            $worksheet->getColumnDimension('B')->setWidth(15);  // Employee ID
                            $worksheet->getColumnDimension('C')->setWidth(15);  // User ID
                            $worksheet->getColumnDimension('D')->setWidth(15);  // Employee Name
                            $worksheet->getColumnDimension('E')->setWidth(12);  // Date of Joining
                            $worksheet->getColumnDimension('F')->setWidth(12);  // Branch
                            $worksheet->getColumnDimension('G')->setWidth(12);  // Department
                            $worksheet->getColumnDimension('H')->setWidth(12);  // Designation
                            $worksheet->getColumnDimension('I')->setWidth(12);  // Termination Date
                            $worksheet->getColumnDimension('J')->setWidth(15);  // Reporting Officer
                            $worksheet->getColumnDimension('K')->setWidth(15);  // Reporting Marks
                            $worksheet->getColumnDimension('L')->setWidth(15);  // Reviewing Officer
                            $worksheet->getColumnDimension('M')->setWidth(15);  // Reviewing Marks
                            $worksheet->getColumnDimension('N')->setWidth(25);  // Grade



                            // Get company info
                            $business_name = $company_info[0]['comp_contact_info']['business_name'];
                            $company_address = $company_info[0]['comp_contact_info']['address'];

                            // --- Business Name ---
                            $worksheet->mergeCells('D1:L1');
                            $worksheet->setCellValue('D1', $business_name);
                            $worksheet->getStyle('D1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('0000FF'); // Blue color
                            $worksheet->getStyle('D1:L1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            $worksheet->mergeCells('D2:L2');
                            $worksheet->setCellValue('D2', "(A Govt. of Kerala Public Sector Undertaking)");
                            $worksheet->getStyle('D2')->getFont()->setBold(true)->setSize(16);
                            $worksheet->getStyle('D2:L2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            // --- Address ---
                            $worksheet->mergeCells('D3:L3');
                            $worksheet->setCellValue('D3', $company_address);
                            $worksheet->getStyle('D3')->getFont()->setSize(12);
                            $worksheet->getStyle('D3:L3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            $worksheet->mergeCells('D4:L4');
                            $worksheet->setCellValue('D4', "Workflow - Officer Assessment " . $year);
                            $worksheet->getStyle('D4')->getFont()->setBold(true)->setSize(16);
                            $worksheet->getStyle('D4:L4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            $worksheet->mergeCells('D5:L5');
                            $worksheet->setCellValue('D5', "(Report Run by " . $user_id . " at " . $date_time . ")"); // Date and time
                            $worksheet->getStyle('D5')->getFont()->setBold(true)->setSize(16);
                            $worksheet->getStyle('D5:L5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);



                            $rowcount = 6; // Start data after logo/header
                            $columncount = 0;

                            if (!empty($grouped_data)) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Date of Joining');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Department');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Designation');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Reporting Officer Assessment Status');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Reporting Officer Assessment Submission Date & Time');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Reviewing Officer Assessment Status');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'Reviewing Officer Assessment Submission Date & Time');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'Completion Status');
                                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);


                                $rowcount = 7;
                                $sl_no = 1;

                                foreach ($grouped_data as $data) {
                                    $col = 0;
                                    // Format joining_date as d-m-Y or leave blank
                                    $joining_date = isset($data['joining_date']) && !empty($data['joining_date'])
                                        ? date('d-m-Y', strtotime($data['joining_date']))
                                        : '';
                                    // Format termination_date as d-m-Y or leave blank
                                    $termination_date = isset($data['termination_date']) && !empty($data['termination_date'])
                                        ? date('d-m-Y', strtotime($data['termination_date']))
                                        : '';

                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $sl_no++);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_id']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['user_id']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_name']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $joining_date);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['branch']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['department']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['employee_designation']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $termination_date);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reporting_status']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reporting_date']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reviewing_status']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['reviewing_date']);
                                    $worksheet->setCellValueByColumnAndRow($col++, $rowcount, $data['completion_status']);


                                    $rowcount++;
                                }
                            } else {
                                $worksheet->mergeCells('A5:L5');
                                $worksheet->setCellValue('A5', "No data available under the selected criteria.");
                            }


                            $lastRow = $rowcount - 1;
                            $cellRange = 'A6:N' . $lastRow;

                            // Enable text wrapping for all columns and rows (A8 to N[lastRow])
                            $worksheet->getStyle("A6:N$lastRow")->getAlignment()->setWrapText(true);


                            // Center align header text vertically and horizontally
                            $worksheet->getStyle("A6:N$lastRow")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                            // $worksheet->getStyle('A8:N8')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                            // Optional: Increase row height for headers to fit wrapped text
                            $worksheet->getRowDimension(6)->setRowHeight(110);

                            $styleArray = [
                                'borders' => [
                                    'allborders' => [
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => ['argb' => 'FF000000'],
                                    ],
                                ],
                            ];
                            // Center align marks columns K and M
                            $worksheet->getStyle("A7:P$lastRow")->getAlignment()
                                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                            // $worksheet->getStyle("M7:M$lastRow")->getAlignment()
                            //     ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                            //     ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


                            $worksheet->getStyle($cellRange)->applyFromArray($styleArray);


                            $objPHPExcel->getActiveSheet()->setTitle('Workflow - Officer Assessment');


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
                            $this->render('staff_review');
                            break;
                    }
                }
            } else {
                echo "<div><h2>Please Choose Criteria Item First</h2></div>";
            }
        } catch (Exception $e) {
            debug($e);
        }
    }
    // End

    //edited by athira on 09-06-2025
    public function generateAnnualPerformanceAssessment($mode)
    {
        ini_set('max_execution_time', 240); // Edited by Athira on 9-6-2025
        $arr_form_data = $_REQUEST;
        $year = $arr_form_data['reportsfrom'];
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $company_info = $this->CompanyContactInfo->query("SELECT business_name,logo,address from comp_contact_info");
        $company_code = $this->Session->read('company_code');
        $this->set('company_info', $company_info);


        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }
        $arr_leavepolicydetails_for_template = array();

        $criteria = $arr_form_data['hidden-criteria1']; //Units or EmployeeDetails.
        $this->set('criteria', $criteria);
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            if ($criteria == "EmployeeDetails") {           //This is the case of Employee Wise. (EmpolyeeDetails)

                $ids = implode(",", array_map('intval', $arr_leavepolicygroupids));

                $self_review_data = $this->EmployeeDetails->query("
                                SELECT 
                                CONCAT_WS(' ', emp_details.first_name, emp_details.middile_name, emp_details.last_name) AS full_name,
                                s.*,
                                aaed.*,
                                aae.*,
                                ase.total_marks AS reporting_total_marks,
                                ase.grade AS reporting_grade,
                                ase.agreement_comment AS reporting_comment,
                                ase.training_need AS reporting_training_need,
                                ase.comments_recommendation AS reporting_recommendation,
                                ase2.total_marks AS reviewing_total_marks,
                                ase2.grade AS reviewing_grade,
                                ase2.agreement_comment AS reviewing_comment,
                                ase2.training_need AS reviewing_training_need,
                                ase2.comments_recommendation AS reviewing_recommendation,
                                eip.EmpName AS reporting_officer_name,
                                eip.designation AS reporting_officer_designation,
                                eiv.EmpName AS reviewing_officer_name,
                                eiv.designation AS reviewing_officer_designation
                            FROM self_review_details AS s
                            LEFT JOIN emp_details ON emp_details.emp_pkey = s.emp_fkey
                            LEFT JOIN assessment_attributes_executive_details AS aaed ON aaed.emp_fkey = s.emp_fkey
                            LEFT JOIN assessment_attributes_executive AS aae ON aaed.attributes_exec_fkey = aae.attributes_exec_pkey
                            LEFT JOIN assessment_summary_executive AS ase ON ase.emp_fkey = s.emp_fkey AND ase.officer_fkey = s.reporting_officer
                            LEFT JOIN employee_info AS eip ON eip.emp_pkey = s.reporting_officer
                            LEFT JOIN assessment_summary_executive AS ase2 ON ase2.emp_fkey = s.emp_fkey AND ase2.officer_fkey = s.reviewing_officer
                            LEFT JOIN employee_info AS eiv ON eiv.emp_pkey = s.reviewing_officer
                            WHERE s.emp_fkey IN ($ids) 
                            AND s.fin_year = '$year' 
                            AND s.status='Reviewing Person submitted the Appraisal';


                            ");

                $staff_review_data = $this->EmployeeDetails->query("
                SELECT 
                    CONCAT_WS(' ', emp_details.first_name, emp_details.middile_name, emp_details.last_name) AS full_name,
                    s.*, aast.*, asf.*, eip.EmpName AS reporting_officer_name,
                    eip.designation AS reporting_officer_designation,
                    eiv.EmpName AS reviewing_officer_name,
                    eiv.designation AS reviewing_officer_designation
                FROM assessment_attributes_staff_details AS s
                LEFT JOIN emp_details ON emp_details.emp_pkey = s.emp_fkey
                LEFT JOIN assessment_attributes_staff_items AS aast ON aast.attr_staff_details_fkey = s.attr_staff_details_pkey
                LEFT JOIN assessment_attributes_staff AS asf ON asf.attributes_staff_pkey = aast.attributes_staff_fkey
                LEFT JOIN employee_info AS eip ON eip.emp_pkey = s.reporting_officer
                LEFT JOIN employee_info AS eiv ON eiv.emp_pkey = s.reviewing_officer
                WHERE s.emp_fkey IN ($ids) AND s.fin_year = '$year' AND s.status = 3
                ");
            } else {
                $ids = implode(",", array_map('intval', $arr_leavepolicygroupids));
                $emp_fkeys_data = $this->EmployeeDetails->query("
                SELECT emp_fkey 
                FROM emp_proff 
                WHERE emp_grade IN (
                    SELECT grade_pkey 
                    FROM grade 
                    WHERE category_fkey IN ($ids)
                )
            ");

                $emp_ids = [];
                foreach ($emp_fkeys_data as $row) {
                    $emp_ids[] = (int)$row['emp_proff']['emp_fkey'];
                }
                $emp_ids_str = implode(",", $emp_ids);

                if (!empty($emp_ids_str)) {
                    $self_review_data = $this->EmployeeDetails->query("
                SELECT 
                CONCAT_WS(' ', emp_details.first_name, emp_details.middile_name, emp_details.last_name) AS full_name,
                s.*,
                aaed.*,
                aae.*,
                ase.total_marks AS reporting_total_marks,
                ase.grade AS reporting_grade,
                ase.agreement_comment AS reporting_comment,
                ase.training_need AS reporting_training_need,
                ase.comments_recommendation AS reporting_recommendation,
                ase2.total_marks AS reviewing_total_marks,
                ase2.grade AS reviewing_grade,
                ase2.agreement_comment AS reviewing_comment,
                ase2.training_need AS reviewing_training_need,
                ase2.comments_recommendation AS reviewing_recommendation,
                eip.EmpName AS reporting_officer_name,
                eip.designation AS reporting_officer_designation,
                eiv.EmpName AS reviewing_officer_name,
                eiv.designation AS reviewing_officer_designation
            FROM self_review_details AS s
            LEFT JOIN emp_details ON emp_details.emp_pkey = s.emp_fkey
            LEFT JOIN assessment_attributes_executive_details AS aaed ON aaed.emp_fkey = s.emp_fkey
            LEFT JOIN assessment_attributes_executive AS aae ON aaed.attributes_exec_fkey = aae.attributes_exec_pkey
            LEFT JOIN assessment_summary_executive AS ase ON ase.emp_fkey = s.emp_fkey AND ase.officer_fkey = s.reporting_officer
            LEFT JOIN employee_info AS eip ON eip.emp_pkey = s.reporting_officer
            LEFT JOIN assessment_summary_executive AS ase2 ON ase2.emp_fkey = s.emp_fkey AND ase2.officer_fkey = s.reviewing_officer
            LEFT JOIN employee_info AS eiv ON eiv.emp_pkey = s.reviewing_officer
            WHERE s.emp_fkey IN ($emp_ids_str)
            AND s.fin_year = '$year' 
            AND s.status = 'Reviewing Person submitted the Appraisal'
            ");

                    $staff_review_data = $this->EmployeeDetails->query("
                SELECT 
                    CONCAT_WS(' ', emp_details.first_name, emp_details.middile_name, emp_details.last_name) AS full_name,
                    s.*, aast.*, asf.*, eip.EmpName AS reporting_officer_name,
                    eip.designation AS reporting_officer_designation,
                    eiv.EmpName AS reviewing_officer_name,
                    eiv.designation AS reviewing_officer_designation
                FROM assessment_attributes_staff_details AS s
                LEFT JOIN emp_details ON emp_details.emp_pkey = s.emp_fkey
                LEFT JOIN assessment_attributes_staff_items AS aast ON aast.attr_staff_details_fkey = s.attr_staff_details_pkey
                LEFT JOIN assessment_attributes_staff AS asf ON asf.attributes_staff_pkey = aast.attributes_staff_fkey
                LEFT JOIN employee_info AS eip ON eip.emp_pkey = s.reporting_officer
                LEFT JOIN employee_info AS eiv ON eiv.emp_pkey = s.reviewing_officer
                WHERE s.emp_fkey IN ($emp_ids_str) AND s.fin_year = '$year' AND s.status = 3
                ");
                }
            }

            $structured_data = [];

            foreach ($self_review_data as $entry) {
                $empKey = $entry['s']['emp_fkey'];

                if (!isset($structured_data[$empKey])) {
                    $structured_data[$empKey] = [
                        'full_name' => $entry[0]['full_name'],
                        'designation' => $entry['s']['designation'],
                        'fin_year' => $entry['s']['fin_year'],
                        'dob' => $entry['s']['dob'],
                        'doj' => $entry['s']['doj'],
                        'absence_period' => $entry['s']['absence_period'],
                        'employment_type' => $entry['s']['employment_type'],
                        'duty_desc' => $entry['s']['duty_desc'],
                        'work_done_desc' => $entry['s']['work_done_desc'],
                        'department' => $entry['s']['department'],
                        'status' => $entry['s']['status'],
                        'reported_date' => $entry['s']['reported_date'],
                        'reviewed_date' => $entry['s']['reviewed_date'],
                        'reporting_officer_name' => $entry['eip']['reporting_officer_name'],
                        'reporting_officer_designation' => $entry['eip']['reporting_officer_designation'],
                        'reviewing_officer_name' => $entry['eiv']['reviewing_officer_name'],
                        'reviewing_officer_designation' => $entry['eiv']['reviewing_officer_designation'],
                        'attributes' => [],
                        'summary' => [
                            'reporting_officer' => [
                                'total_marks' => $entry['ase']['reporting_total_marks'],
                                'grade' => $entry['ase']['reporting_grade'],
                                'agreement_comment' => $entry['ase']['reporting_comment'],
                                'training_need' => $entry['ase']['reporting_training_need'],
                                'comments_recommendation' => $entry['ase']['reporting_recommendation']
                            ],
                            'reviewing_officer' => [
                                'total_marks' => $entry['ase2']['reviewing_total_marks'],
                                'grade' => $entry['ase2']['reviewing_grade'],
                                'agreement_comment' => $entry['ase2']['reviewing_comment'],
                                'training_need' => $entry['ase2']['reviewing_training_need'],
                                'comments_recommendation' => $entry['ase2']['reviewing_recommendation']
                            ]
                        ]
                    ];
                }

                $structured_data[$empKey]['attributes'][] = [
                    'attribute' => $entry['aae']['attributes'],
                    'marks_given_by_reporting_officer' => $entry['aaed']['reporting_officer_marks'],
                    'marks_given_by_reviewing_officer' => $entry['aaed']['reviewing_officer_marks']
                ];
            }


            $structured_staff_review = [];
            foreach ($staff_review_data as $entry) {
                $empKey = $entry['s']['emp_fkey'];

                if (!isset($structured_staff_review[$empKey])) {
                    $structured_staff_review[$empKey] = [
                        'full_name' => $entry['0']['full_name'],  // Corrected reference
                        'designation' => $entry['s']['designation'],
                        'department' => $entry['s']['department'],
                        'dob' => $entry['s']['dob'],
                        'doj' => $entry['s']['doj'],
                        'fin_year' => $entry['s']['fin_year'],
                        'grade_entry_date' => $entry['s']['grade_entry_date'],
                        'absence_period' => $entry['s']['absence_period'],
                        'employment_type' => $entry['s']['employment_type'],
                        'reported_date' => $entry['s']['reported_date'],
                        'reviewed_date' => $entry['s']['reviewed_date'],
                        'reporting_officer' => [
                            'name' => $entry['eip']['reporting_officer_name'],
                            'designation' => $entry['eip']['reporting_officer_designation'],
                        ],
                        'reviewing_officer' => [
                            'name' => $entry['eiv']['reviewing_officer_name'],
                            'designation' => $entry['eiv']['reviewing_officer_designation'],
                        ],
                        'attributes' => [],
                        'summary' => [
                            'reporting_total_marks' => $entry['s']['reporting_officer_marks'],
                            'reporting_total_grade' => $entry['s']['reporting_officer_grade'],
                            'comments' => [
                                'reporting' => $entry['s']['reporting_officer_comments'],
                                'reviewing' => $entry['s']['reviewing_officer_comments']
                            ],
                            'training_needs' => [
                                'reporting' => $entry['s']['reporting_officer_training_needs'],
                                'reviewing' => $entry['s']['reviewing_officer_training_needs']
                            ]
                        ]
                    ];
                }

                // ✅ Ensure attributes are correctly structured
                $structured_staff_review[$empKey]['attributes'][] = [
                    'attribute' => $entry['asf']['attributes'],
                    'marks_given_by_reporting_officer' => $entry['aast']['marks'],
                ];
            }





            // debug($structured_data);
            $this->set('structured_data', $structured_data);
            $this->set('structured_staff_review', $structured_staff_review);

            // debug($structured_staff_review);



            switch ($mode) {
                case 'pdf':
                    try {
                        $this->set('mode', 'pdf');

                        App::import('Vendor', 'HTML2PDF', ['file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php']);
                        // Render combined PDF view
                        $view_combined = new View($this, false);
                        $view_combined_output = $view_combined->render('annualperformanceassessment');

                        // Generate single PDF
                        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->writeHTML($view_combined_output);
                        $html2pdf->Output('AnnualPerformanceAssessment.pdf', 'D');
                    } catch (Exception $e) {
                        debug($e);
                    }

                    break;

                default:
                    break;
            }
        }
    }
    //end
}
