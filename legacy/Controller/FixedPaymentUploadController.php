<?php

set_time_limit(500);

class FixedPaymentUploadController extends AppController
{
    //****This FixedPaymentUploadController Created By ARUL P DAS on 6-11-2019****
    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'FixedPaymentUpload';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'SalaryHeadItems', 'EmployeeDetails', 'Units', 'FinancialYear', 'SalaryHeads', 'EmployeeVariableUpload', 'EmployeeProfessionalDetails', 'EmployeeFixedPaymentUpload');
    public $components = array('DatatablesManagement');

    //****This FixedPaymentUploadController Created By ARUL P DAS on 6-11-2019****
    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1))));
        // $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => array('status' => 1))));



        //***emp_pkey replaced with emp_company_id in the employee list. By ARUL P DAS on 05-11-2019***
        $emp_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff join emp_ctc_transaction on emp_details.emp_pkey = emp_ctc_transaction.emp_fkey where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 and emp_details.emp_pkey not in (select emp_fkey from termination where status=1) group by emp_details.first_name');
        //This query eliminates the employees who dont have a salary structure. By **ARUL P DAS on 3/12/2019

        $this->set("emp_list", $emp_list);
        // debug($emp_list);

        //   $this->set("arr_headitems",$arr_headitems =
        // $this->SalaryHeadItems->find("all",array(
        //  'conditions'=>array('status'=>1))));
        //debug($arr_headitems);
        $arr_headitems = $this->SalaryHeadItems->query("SELECT  SalaryHeadItems.salary_head_item_pkey ,  SalaryHeadItems.head_fkey ,  SalaryHeadItems.item ,  SalaryHeadItems.item_type ,  SalaryHeadItems.item_value ,
             SalaryHeadItems.occurance ,  SalaryHeadItems.start_from ,  SalaryHeadItems.comments ,  SalaryHeadItems.value ,  SalaryHeadItems.is_show_salslip , 
            SalaryHeadItems.item_part ,  SalaryHeadItems.status ,  SalaryHeadItems.salary_head_item_order1  
            FROM  salary_head_items  AS  SalaryHeadItems  
            WHERE  status  = 1 and item_type = 'Manually'
            and  SalaryHeadItems.head_fkey  in (select head_pkey 
            from salary_heads where lcase(head_occurance)='variable'
             AND SalaryHeadItems.value = 'Y'
             and status=1) order by SalaryHeadItems.item");
        $this->set("arr_headitems", $arr_headitems);
    }

    public function branchemployee($branch)
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $arr_request_data = $this->request->data;
        // $branch=$arr_request_data['branch'];
        if ($branch == "ALL") {
            //The query also eliminates the employees who have no salary structure. By ***ARUL P DAS on 3/12/2019
            $result = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff join emp_ctc_transaction on emp_details.emp_pkey = emp_ctc_transaction.emp_fkey where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 and emp_details.emp_pkey not in (select emp_fkey from termination where status=1) group by emp_pkey');
        } else {
            //The query also eliminates the employees who have no salary structure. By ***ARUL P DAS on 3/12/2019
            $result = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff join emp_ctc_transaction on emp_details.emp_pkey = emp_ctc_transaction.emp_fkey where emp_details.emp_pkey=emp_proff.emp_fkey and branch_code="' . $branch . '" and emp_details.status=1 and emp_details.emp_pkey not in (select emp_fkey from termination where status=1) group by emp_pkey');
        }
        echo json_encode(array("value" => $result, "msg" => count($result)));
    }

    public function downloadfixedpaymentuploadform($salaryhead = '', $branch = '', $occurance = '', $emp_fkey = '', $start_month = '')
    {

        $this->autoRender = FALSE;

        $arr_request_data = $this->request->data;
        // $start_month=$arr_request_data['start_month'];
        // $end_month=$arr_request_data['end_month'];
        // $salaryhead=$arr_request_data['salhead'];
        // $occurance=$arr_request_data['occurance'];

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_fixedpayment.xlsx" : "fixedpayment_" . strtotime() . ".xlsx";

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        //App::import('Vendor', 'EmployeeCTCData', array('file'=>'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        //            $empctcdata =   new EmployeeCTCData($ctcuploadtype);
        //            $emp_credentials_schema =   $empctcdata->getFieldHeadings('UserCredentials');
        //            $emp_details_schema =   $empctcdata->getFieldHeadings('EmployeeDetails');
        //            $emp_ctc_schema =   $empctcdata->getFieldHeadings('EmployeeCTC');
        //            $emp_schema =   array_merge($emp_credentials_schema, $emp_details_schema, $emp_ctc_schema);
        //            debug($emp_schema);die();
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5); //SL NO
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14); //User ID
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(13); //Employee ID
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20); //Employee Name
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(13); //Date of Joining
        // $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);//Branch
        // $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);//Department
        // $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);//Designation
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13); //Gross salary
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(13); //Monthly CTC
        // $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);//Allowance
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12); //Occurance
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(24); //Start Month
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(14); //Amount
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(14); //Remarks

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true); //SL NO
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true); //User ID
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true); //Employee ID
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true); //Employee Name
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); //Date of Joining
        // $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);//Branch
        // $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);//Department
        // $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);//Designation
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); //Gross salary
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true); //Monthly CTC
        // $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);//Allowance
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true); //Occurance
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true); //Start Mon
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true); //Amount
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true); //Remarks


        ///////////////The Second sheet begins here////////////////////////////By **ARUL P DAS on 3/12/2019

        //        $objWorkSheet = $objPHPExcel->createSheet(2);
        //        $objWorkSheet->getStyle('A1')->getFont()->setBold(true);
        //        $objWorkSheet->setCellValue('A1', 'Occurance');//Heading
        //        $objWorkSheet->setCellValue('A2', 'Yearly');
        //        $objWorkSheet->setCellValue('A3', 'Half-Yearly');
        //        $objWorkSheet->setCellValue('A4', 'Monthly');
        //        $objWorkSheet->setCellValue('A5', 'Bi-Monthly');
        //        $objWorkSheet->setCellValue('A6', 'Quarterly');

        //        $objWorkSheet->getStyle('D1')->getFont()->setBold(true);
        //        $objWorkSheet->setCellValue('D1', 'Month Format');
        //        $objWorkSheet->setCellValue('D2', '(YYYY-MM-DD)');
        //        $objWorkSheet->setTitle('Help');

        ////////////////The second sheet ends here//////////////////////////////

        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Fixed Payment");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setCellValueByColumnAndRow(0, 1, "Sl No");
        $worksheet->setCellValueByColumnAndRow(1, 1, "User ID");
        $worksheet->setCellValueByColumnAndRow(2, 1, "Employee ID");
        $worksheet->setCellValueByColumnAndRow(3, 1, "Employee Name");
        $worksheet->setCellValueByColumnAndRow(4, 1, "Date of Join");
        // $worksheet->setCellValueByColumnAndRow(5, 1, "Branch");//New field in 10/12/2019 by **ARUL P DAS
        // $worksheet->setCellValueByColumnAndRow(6, 1, "Department");//New field in 10/12/2019 by **ARUL P DAS
        // $worksheet->setCellValueByColumnAndRow(7, 1, "Designation");//New field in 10/12/2019 by **ARUL P DAS
        $worksheet->setCellValueByColumnAndRow(5, 1, "Gross Salary");
        $worksheet->setCellValueByColumnAndRow(6, 1, "Monthly CTC");
        //Added Newly. By **ARUL P DAS on 2-12-2019
        // $worksheet->setCellValueByColumnAndRow(10, 1, "Allowance");
        $worksheet->setCellValueByColumnAndRow(7, 1, "Occurrence");
        $worksheet->setCellValueByColumnAndRow(8, 1, "Start Month (YYYY-MM-DD)");
        // $worksheet->setCellValueByColumnAndRow(8, 1, "End Month");
        //Added Newly ends here
        $worksheet->setCellValueByColumnAndRow(9, 1, "Amount");
        $worksheet->setCellValueByColumnAndRow(10, 1, "Remarks");

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $get_head_itemA_fkey = $this->EmployeeDetails->query(" SELECT item from salary_head_items where salary_head_item_pkey  = $salaryhead ");
        $condtion = "";
        // if (isset($branch) && !empty($branch)) {
        //     $condtion .="  EmployeeDetails.branch_code='$branch'";
        // }

        if (isset($branch) && $branch != "ALL") { //This is to check whether all branches or a specified branch. by **ARUL P DAS
            $condtion .= "  EmployeeDetails.branch_code='$branch'";
        }

        $emp_condition = "";
        if (isset($emp_fkey) && $emp_fkey != "ALL") { //This is to check whether all branches or a specified branch. by **ARUL P DAS
            $emp_condition .= "  EmployeeDetails.emp_pkey='$emp_fkey'";
        }

        //The below condition is used to eliminate the resighned employees from the list. By ***ARUL P DAS on 3/12/2019
        $resighned_condition = " EmployeeDetails.emp_pkey not in (select emp_fkey from termination where status=1)";

        //The below condition is to check employee who already processed salary in the selected month.
        $start_month = $start_month.'-1';
        $sal_date = date('Y-m', strtotime($start_month)); //date formar 2020-01-01 to 2020-01
        $sal_condition = " PM.emp_fkey NOT IN (select emp_fkey as count from payroll_master where action in ('Processed','Approved') and month_year='$sal_date')";

        //The below code is to check the employee who exist in selected month
        $doj_condition = "EPM.joining_date < '$start_month'";

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //The below query edited and added employee_info table and its attributes branch,designation and department.
        //By ***ARUL P DAS on 10/12/2019
        $arr_empdetails = $this->EmployeeDetails->find('all', array(
            'fields' => 'DISTINCT UserCredentials.user_id,'
                . 'EPM.emp_company_id,EPM.joining_date,EmployeeDetails.first_name,'
                . 'EmployeeDetails.middile_name,'
                . 'EmployeeDetails.last_name,'
                . 'EIN.branch,EIN.designation,EIN.department,'
                . 'ECT.emp_anual_ctc',
            'joins' => array(
                array(
                    'table' => 'user_credentials',
                    'alias' => 'UserCredentials',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
                ), array(
                    'table' => 'emp_ctc_transaction ',
                    'alias' => 'ECT ',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = ECT.emp_fkey', '`end_date_effective` IS NULL')
                ), array(
                    'table' => 'emp_proff ',
                    'alias' => 'EPM ',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EPM.emp_fkey')
                ), array(
                    'table' => 'employee_info ',
                    'alias' => 'EIN ',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EIN.emp_pkey')
                ), array(
                    'table' => 'payroll_master ',
                    'alias' => 'PM ',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = PM.emp_fkey')
                )
            ),
            'conditions' => array(
                'status' => 1, $condtion, $emp_condition, $resighned_condition, $sal_condition, $doj_condition
            )
        ));
        // debug($arr_empdetails);exit;


        $rowindex = 2;
        $columnindex = 0;
        $sl = 0;
        foreach ($arr_empdetails as $value) {
            $sl++; //This is the serial number. By **ARUL P DAS on 10/12/2019
            $userid = $value['UserCredentials']['user_id'];

            $arr_empdetails_ct = $this->EmployeeDetails->query(" SELECT sum(structure_det_value) as ctc FROM `emp_salary_structure` where head_operator = 'ADDITION'  and lcase(head_type) not in ('manually','variable') and emp_fkey in (select emp_fkey from user_credentials where user_id = '$userid' ) and end_date_effective is null ");


            $empname = $value['EmployeeDetails']['first_name'] . ' ' . $value['EmployeeDetails']['middile_name'] . ' ' . $value['EmployeeDetails']['last_name'];
            $emp_id = $value['EPM']['emp_company_id'];
            $doj = $value['EPM']['joining_date'];
            $branch = $value['EIN']['branch']; //Added by **ARUL P DAS on 10/12/2019
            $designation = $value['EIN']['designation']; //Added by **ARUL P DAS on 10/12/2019
            $department = $value['EIN']['department']; //Added by **ARUL P DAS on 10/12/2019
            $grossctcamount = ($value['ECT']['emp_anual_ctc']) ? $value['ECT']['emp_anual_ctc'] / 12 : 0;
            $ctcRate = ($arr_empdetails_ct['0']['0']['ctc']) ? $arr_empdetails_ct['0']['0']['ctc'] : 0;

            if ($start_month == "" || $start_month == NULL) {
                $start_month = date('Y-m-d');
            }
            //The below code is used to fetch the allowance name of the selected salary head item. 
            //By ***ARUL P DAS on 2/12/2019*** 
            $result = $this->EmployeeDetails->query("select item from salary_head_items where salary_head_item_pkey=$salaryhead and status=1");
            $allowance = $result[0]['salary_head_items']['item'];
            //The below code is used to fetch the allowance name of the selected salary head item. 
            //By ***ARUL P DAS on 2/12/2019***
            if ($occurance == "1" || $occurance == 1) {
                $occurance = "Yearly";
            }
            if ($occurance == "2" || $occurance == 2) {
                $occurance = "Half-Yearly";
            }
            if ($occurance == "3" || $occurance == 3) {
                $occurance = "Monthly";
            }
            if ($occurance == "4" || $occurance == 4) {
                $occurance = "Bi-Monthly";
            }
            if ($occurance == "5" || $occurance == 5) {
                $occurance = "Quarterly";
            }


            //////////////The below code is to add the drop down menu into main field/////////////////////
            /////////////By ***ARUL P DAS on 3/12/2019

            //            $blockNames = array("Yearly","Half-Yearly","Monthly","Bi-Monthly","Quarterly");//This is listing in the dropdown menu of occurance. By ***ARUL P DAS on 3/12/2019
            //            $blocksList = implode(", ", $blockNames);
            //
            //            $objValidation = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(10, $rowindex)->getDataValidation();
            //            $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            //            $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            //            $objValidation->setAllowBlank(true);
            //            $objValidation->setShowDropDown(true);
            //            $objValidation->setErrorTitle('Input error');
            //            $objValidation->setError('Value is not in list');
            //            $objValidation->setFormula1('"' . $blocksList . '"');

            ///////////////This is the ending of adding dropdown into main field///////////////////////////

            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $sl);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $userid);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $emp_id);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowindex, $empname);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowindex, $doj);
            ////Added branch, designation,department by **ARUL P DAS
            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowindex, $branch);
            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowindex, $department);
            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowindex, $designation);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowindex, round($grossctcamount, 2));
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowindex, round($ctcRate, 2));

            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowindex, $occurance);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowindex, $start_month);
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowindex)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT); //This is to set the cell format from general to text. By **ARUL P DAS on 06/12/2019
            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowindex, $end_month);
            // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowindex)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//This is to set the cell format from general to text. By **ARUL P DAS on 06/12/2019
            $rowindex++;
        }

        $h = isset($get_head_itemA_fkey['0']['salary_head_items']['item']) ? $get_head_itemA_fkey['0']['salary_head_items']['item'] : '';
        $objPHPExcel->getActiveSheet()->setTitle("Fixed  " . $h);

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsavefixedpayment($salary_head_item = 0, $month = 0, $occurance = 0)
    {
        $this->autoRender = FALSE;
        if ($salary_head_item != 0) {
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_empfixedpayment_' . strtotime("now") . '.xlsx' : 'empfixedpayment_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['fixedpayment']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                $duplication = 0;
                $payroll_issues = 0;
                $payroll_array = array();
                $i = 0;
                $j = 0; //Edited by Akshay on 2-1-2024
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('User ID', 'Employee Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;
                            }
                            $index++;
                        }
                    }
                    if ($mandatory_fields_warning) {
                        //Exit if mandatory fields not entered
                        unlink($targetpath);
                        echo json_encode(array('success' => 0, 'msg' => 'Please check all mandatory fields entered'));
                        exit;
                    } else {
                        //Iam here now
                        // debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
                        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
                        // $this->EmployeeVariableUpload->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeFixedPaymentUpload->useDbConfig = $this->Session->read('ds');

                        //                            App::import('Vendor', 'EmployeeCTCData', array('file'=>'.php'));
                        //                            $empcsvdata =   new EmployeeCTCData($ctcuploadtype);
                        //                            $arr_empcredentials_fields =   $empcsvdata->getFieldNames('UserCredentials');
                        //                            $arr_empdetails_fields =   $empcsvdata->getFieldNames('EmployeeDetails');
                        //                            $arr_empctc_fields    =   $empcsvdata->getFieldNames('EmployeeLeaveUpload');
                        $current_date = date('Y-m-d');
                        $doj_error = 0; //This is to store whether date of join error occure
                        foreach ($arrayempdata as $key => $row) {
                            $user_id = isset($row['User ID']) ? $row['User ID'] : '';
                            $amount = isset($row['Amount']) ? $row['Amount'] : '';
                            $remarks = isset($row['Remarks']) ? $row['Remarks'] : '';
                            //The below values are now fetch from excel form. Older is fetching from the view form 
                            //select options. By ***ARUL P DAS on 3/12/2019
                            //                            $occurance = isset($row['Occurance']) ? $row['Occurance'] : '';
                            //                            $month_year = isset($row['Start Month (YYYY-MM-DD)']) ? $row['Start Month (YYYY-MM-DD)'] : date('Y-m-d');
                            //$month_year = $month; edited by sinsiya 13-03-2024
                            $month_year = date('Y-m-d', strtotime($month . '-01'));
                           // debug($month); 
                            $doj = isset($row['Date of Join']) ? $row['Date of Join'] : date('Y-m-d');
                            // $end_month_year = isset($row['End Month']) ? $row['End Month'] : '';

                            if ($occurance == "Yearly") {
                                $occurance = '1';
                            } elseif ($occurance == "Half-Yearly") {
                                $occurance = '2';
                            } elseif ($occurance == "Monthly") {
                                $occurance = '3';
                            } elseif ($occurance == "Bi-Monthly") {
                                $occurance = '4';
                            } elseif ($occurance == "Quarterly") {
                                $occurance = '5';
                            }
                            // if($doj>$month_year){//This is to check whether start date is less than date of join. By **ARUL P DAS
                            //     $doj_error=1;
                            //     continue;
                            // }

                            if ($user_id == '') {
                                continue;
                            }

                            if (isset($amount) && !empty($amount)) {
                                // }
                                //fetch emp_fkey using user_id
                                $arr_usercredentials = $this->UserCredentials->find('first', array(
                                    'fields' => 'emp_fkey',
                                    'conditions' => array(
                                        'user_id' => $user_id
                                    )
                                ));

                                $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';


                                $salary_headitems = $this->SalaryHeadItems->find('first', array(
                                    'conditions' => array(
                                        'salary_head_item_pkey' => $salary_head_item
                                    )
                                ));


                                ////////////The below codes are temporerly hided to check the values getting from excel form.
                                // $month_year="";
                                // if($month==0){$month_year=$current_date;}else{$month_year=$month;}

                                // $end_month_year="";
                                // if($end_month==0){$end_month_year='';}else{$end_month_year=$end_month;}

                                // $occurance= ($occurance==0)?'3':$occurance;
                                ////////////////////Temporary codes are Ended here/////////////////////////


                                $head_fkey = isset($salary_headitems['SalaryHeadItems']['head_fkey']) ? $salary_headitems['SalaryHeadItems']['head_fkey'] : '';

                                $salary_head = $this->SalaryHeads->find('first', array(
                                    'conditions' => array(
                                        'head_pkey' => $head_fkey
                                    )
                                ));

                                $operator = isset($salary_head['SalaryHeads']['head_operator']) ? $salary_head['SalaryHeads']['head_operator'] : '';
                                $item = isset($salary_headitems['SalaryHeadItems']['item']) ? $salary_headitems['SalaryHeadItems']['item'] : '';
                                $item_type = isset($salary_headitems['SalaryHeadItems']['item_type']) ? $salary_headitems['SalaryHeadItems']['item_type'] : '';
                                $item_part = isset($salary_headitems['SalaryHeadItems']['item_part']) ? $salary_headitems['SalaryHeadItems']['item_part'] : '';

                                $arr_empfixed_data = array();
                                $arr_empfixed_data['emp_fixed_component_upload_pkey'] = '';
                                $arr_empfixed_data['emp_fkey'] = $emp_fkey;
                                $arr_empfixed_data['salary_head_item_fkey'] = $salary_head_item;
                                $arr_empfixed_data['start_date_effective'] = $month_year;
                                $arr_empfixed_data['end_date_effective'] = '';
                                $arr_empfixed_data['salary_head_item_desc'] = $item;
                                $arr_empfixed_data['uploaded_amount'] = $amount;
                                $arr_empfixed_data['head_operator'] = $operator;
                                $arr_empfixed_data['head_type'] = $item_type;
                                $arr_empfixed_data['item_part'] = $item_part;
                                $arr_empfixed_data['occurance'] = $occurance;
                                $arr_empfixed_data['action'] = 'uploaded by excel';
                                $arr_empfixed_data['remarks'] = $remarks;
                                $arr_empfixed_data['status'] = 1;
                                $arr_empfixed_data['created_by'] = $this->Session->read('login_user_id');
                                // $arr_empfixed_data['creation_date'] = date('Y-m-d');

                                $date = date('Y-m', strtotime($month_year));
                                //This query is to check whether salary is already processed in specified month. By ***ARUL P DAS on 21/12/2019
                                $arr_salary_month_check = $this->EmployeeFixedPaymentUpload->query("select emp_salary_slip.salary_amount FROM emp_salary_slip WHERE month_year= '$date' AND emp_fkey= '$emp_fkey' and end_date_effective IS NULL ");
                                //                                debug($date);
                                //                                if (count($arr_salary_month_check)>0) {
                                //                                    $payroll_issues=1;
                                //                                    continue;
                                //                                }

                                if (count($arr_salary_month_check) > 0) {
                                    $payroll_issues = 1;
                                    $name_of_emp = $this->EmployeeFixedPaymentUpload->query("select EmpName from employee_info where emp_pkey='$emp_fkey'");
                                    // debug($name_of_emp);
                                    $payroll_array[$i]['name'] = $name_of_emp[0]['employee_info']['EmpName'];
                                    $payroll_array[$i]['emp_fkey'] = $emp_fkey;
                                    //                                    $payroll_array[$i]['date']=$date;
                                    $i++;
                                    continue;
                                }

                                //This query is to check whether duplicate entry occures in specified month. By ***ARUL P DAS on 21/12/2019
                                $already_exist_check = $this->EmployeeFixedPaymentUpload->query("select * from emp_fixed_component_upload where emp_fkey='$emp_fkey' and salary_head_item_fkey='$salary_head_item' and uploaded_amount='$amount' and occurance='$occurance' and date_format(start_date_effective,'%Y-%m')='$date' and status=1");
                                if (count($already_exist_check) > 0) {
                                    $duplication = 1;
                                    //Edited by Akshay on 2-1-2024
                                    $name_of_emp = $this->EmployeeFixedPaymentUpload->query("select EmpName from employee_info where emp_pkey='$emp_fkey'");
                                    // debug($name_of_emp);
                                    $duplicate_array[$j]['name'] = $name_of_emp[0]['employee_info']['EmpName'];
                                    $duplicate_array[$j]['emp_fkey'] = $emp_fkey;
                                    $j++;
                                    continue;
                                }
                                $result1 = $this->EmployeeFixedPaymentUpload->save($arr_empfixed_data);
                                try {
                                    // debug($arr_empvar_data);
                                    // $result1 = $this->EmployeeFixedPaymentUpload->save($arr_empvar_data);
                                } catch (Exception $e) {
                                    //debug($e);
                                }
                            }
                        }
                    }
                    unlink($targetpath);
                    if ($doj_error != 0) {
                        echo json_encode(array('success' => 0, 'msg' => 'Start date should be greater than Joining date'));
                        exit;
                    }
                    if ($payroll_issues == 1) {
                        $this_month = date('Y-m');
                        $msg = "paryroll_issue";
                        echo json_encode(array('success' => 0, 'msg' => $msg, 'items' => $payroll_array));
                        exit;
                    }
                    if ($duplication == 1) {
                        $msg = "Duplicate entry found";
                        //                        echo json_encode(array('success' => 0, 'msg' => 'EMI start month should be greater than or equal to current month','error'));
                        echo json_encode(array('success' => 0, 'msg' => $msg));
                        exit;
                    }
                    if ($salary_head_item != 0) {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Fixed Payment imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Fixed Payment reviced successfully'));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    if ($salary_head_item != 0) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Fixed Payment import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Fixed Payment revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($salary_head_item != 0) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Fixed Payment import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Fixed Payment revision failed!'));
                    exit;
                }
            }
        } else {
            if ($salary_head_item != 0) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Fixed Payment import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Fixed Payment revision  failed!'));
                exit;
            }
            exit;
        }
    }

    public function employeelistfixedpayment()
    {
        ///////This is to display all employee fixed payment uploads. By ***ARUL P DAS****
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        // debug($arr_request_data);
        // $this->EmployeeVariableUpload->useDbConfig = $this->Session->read('ds');
        $this->EmployeeFixedPaymentUpload->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $resp_leave = array();
        $resp_leave["rows"] = array();
        $ofst = ($page - 1) * $limit;

        $mm = isset($arr_request_data['start_month']) ? $arr_request_data['start_month'] : '';
        if ($mm != '') {
            $month = isset($arr_request_data['start_month']) ? $arr_request_data['start_month'] : '';
            // $test_start=date("Y-m",strtotime('01-'.$month))."-01";
            $start_month_condition = " and fcu.start_date_effective='$month'";
        } else {
            $start_month_condition = '';
        }
        $mm = isset($arr_request_data['end_month']) ? $arr_request_data['end_month'] : '';
        // debug($mm);
        if ($mm != '') {
            $month = isset($arr_request_data['end_month']) ? $arr_request_data['end_month'] : '';
            // $test_end=date("Y-m-t",strtotime('01-'.$month));
            $end_month_condition = " and fcu.end_date_effective='$month'";
        } else {
            $end_month_condition  = '';
        }
        // debug($start_month_condition. $end_month_condition);
        // debug($month);
        //  debug($arr_request_data['employee']);
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $empfkey = '';
        if ($emp_fkey != '') {
            $empfkey = " and fcu.emp_fkey='$emp_fkey' ";
        }
        //  debug($arr_request_data['branch']);
        $bb = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        if ($bb != '') {
            $branch = $arr_request_data['branch'];
            $branch_code = " and ed.branch_code='$branch' ";
        } else {
            $branch_code = "";
        }
        // debug($arr_request_data['salhead']);
        $salhead = isset($arr_request_data['salhead']) ? $arr_request_data['salhead'] : '';
        if ($salhead != '') {
            $salheadfkey = " and fcu.salary_head_item_fkey='$salhead' ";
        } else {
            $salheadfkey = "";
        }

        $occurance = isset($arr_request_data['occurance']) ? $arr_request_data['occurance'] : '';
        if ($occurance != '') {
            $occurance_condition = " and fcu.occurance='$occurance' ";
        } else {
            $occurance_condition = "";
        }
        // $da = isset($arr_request_data['name']) ? $arr_request_data['name'] : '';
        // debug($da);
        if (isset($arr_request_data['name']) && $arr_request_data['name'] == '1') {
            $status_condition = "fcu.status in(0,1)";
        } else {
            $status_condition = "fcu.status=1";
        }
        // debug($occurance);
        // debug($emp_fkey);
        //   $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');

        $this->datatable["conditions"] = array("status" => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $count = $this->EmployeeFixedPaymentUpload->find("count", array("conditions" => array('status' => 1)));
        $arr_data = $this->EmployeeFixedPaymentUpload->query("select distinct concat(ed.first_name,' ',ed.last_name) empname,emp_company_id,
(select count(*) from emp_salary_slip where emp_fkey = ed.emp_pkey and end_date_effective is null and date_format(STR_TO_DATE(emp_salary_slip.month_year,'%Y-%m'),'%Y-%m') =  date_format(STR_TO_DATE(fcu.start_date_effective,'%m-%Y'),'%Y-%m') ) as proccessed, fcu.start_date_effective,fcu.emp_fkey,emp_fixed_component_upload_pkey,salary_head_item_desc,uploaded_amount,
head_operator, head_type,item_part,remarks,end_date_effective,occurance,fcu.status, fcu.created_by
                from emp_details ed
                INNER join  emp_fixed_component_upload fcu on (ed.emp_pkey = fcu.emp_fkey) INNER join emp_proff on (fcu.emp_fkey=emp_proff.emp_fkey) where $status_condition $empfkey $start_month_condition $end_month_condition $branch_code $salheadfkey $occurance_condition order by emp_fixed_component_upload_pkey desc limit $ofst,$limit ");
        $arr_count = $this->EmployeeFixedPaymentUpload->query("select distinct concat(ed.first_name,' ',ed.last_name) 
                empname,emp_fkey,emp_fixed_component_upload_pkey,salary_head_item_desc,uploaded_amount,head_operator, head_type,item_part,remarks,occurance,fcu.status
                from emp_details ed
                INNER join  emp_fixed_component_upload fcu on (ed.emp_pkey = fcu.emp_fkey)                 
                where $status_condition $empfkey $start_month_condition $end_month_condition $branch_code $salheadfkey");
        $this->set("arr_data", $arr_data);
        $out = array();
        // debug($arr_data);die();
        $counts = count($arr_count);
        if ($counts > 0) {
            foreach ($arr_data as $key => $value) {
                $out['empname'] = isset($value[0]['empname']) ? $value[0]['empname'] : '';
                $out['emp_fixed_component_upload_pkey'] = isset($value['fcu']['emp_fixed_component_upload_pkey']) ? $value['fcu']['emp_fixed_component_upload_pkey'] : '';
                $out['emp_fkey'] = isset($value['fcu']['emp_fkey']) ? $value['fcu']['emp_fkey'] : '';
                $out['emp_company_id'] = isset($value['emp_proff']['emp_company_id']) ? $value['emp_proff']['emp_company_id'] : '';
                $out['salary_head_item_desc'] = isset($value['fcu']['salary_head_item_desc']) ? $value['fcu']['salary_head_item_desc'] : '';
                $out['uploaded_amount'] = isset($value['fcu']['uploaded_amount']) ? $value['fcu']['uploaded_amount'] : '';
                $out['head_operator'] = isset($value['fcu']['head_operator']) ? $value['fcu']['head_operator'] : '';
                $out['head_type'] = isset($value['fcu']['head_type']) ? $value['fcu']['head_type'] : '';
                $start_date_effective = $value['fcu']['start_date_effective'];
                $out['start_month_year'] = isset($start_date_effective) && $start_date_effective !== '0000-00-00' ? date('m-Y', strtotime($start_date_effective)) : '';
                $out['end_month_year'] = isset($value['fcu']['end_date_effective']) ? $value['fcu']['end_date_effective'] : '';
                $out['item_part'] = isset($value['fcu']['item_part']) ? $value['fcu']['item_part'] : '';
                $out['remarks'] = isset($value['fcu']['remarks']) ? $value['fcu']['remarks'] : '';
                $out['status'] = isset($value['fcu']['status']) ? $value['fcu']['status'] : '';

                //Edited by Akshay on 1-1-2024
                $out['created_by'] = isset($value['fcu']['created_by']) ? $value['fcu']['created_by'] : '';
                $occurance = '';
                if ($value['fcu']['occurance'] == '1') {
                    $occurance = 'Yearly';
                } elseif ($value['fcu']['occurance'] == '2') {
                    $occurance = 'Half-Yearly';
                } elseif ($value['fcu']['occurance'] == '3') {
                    $occurance = 'Monthly';
                } elseif ($value['fcu']['occurance'] == '4') {
                    $occurance = 'Bi-Monthly';
                } elseif ($value['fcu']['occurance'] == '5') {
                    $occurance = 'Quarterly';
                }
                $out['occurance'] = isset($occurance) ? $occurance : '';
                $resp_leave["rows"][$key] = $out;
            }
        }
        $resp_leave["total"] = $counts;
        // debug($resp_leave);
        echo json_encode($resp_leave);
    }

    public function form($id = 0)
    {
        //$this->UserCredentials->useDbConfig = $this->Session->read('ds');
        // $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmployeeFixedPaymentUpload->useDbConfig = $this->Session->read('ds');
        //        $arr_employees = $this->EmployeeDetails->query("select EmployeeDetails.emp_pkey,EmployeeDetails.first_name,EmployeeDetails.last_name from emp_details EmployeeDetails where status = '1' ");

        //        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        //        $this->set("arr_employees", $arr_employees);
        //        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
        if (isset($id)) {

            $joins = array(
                array(
                    'table' => 'emp_details',
                    'alias' => 'Employee',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeFixedPaymentUpload.emp_fkey = Employee.emp_pkey')
                )
            );
            $this->set("arr_leaveupoload", $arr_leaveupoload = $this->EmployeeFixedPaymentUpload->find("all", array('fields' => array("EmployeeFixedPaymentUpload.*,Employee.first_name,Employee.last_name"), 'joins' => $joins,  'conditions' => array('EmployeeFixedPaymentUpload.status' => 1, 'emp_fixed_component_upload_pkey' => $id))));
            // debug($arr_leaveupoload);
        }
        //  $this->set("arr_headitems",$arr_headitems = $this->SalaryHeadItems->find("all",array('conditions'=>array('status'=>1))));
        $arr_headitems = $this->SalaryHeadItems->query("SELECT  SalaryHeadItems.salary_head_item_pkey ,  SalaryHeadItems.head_fkey ,  SalaryHeadItems.item ,  SalaryHeadItems.item_type ,  SalaryHeadItems.item_value ,
             SalaryHeadItems.occurance ,  SalaryHeadItems.start_from ,  SalaryHeadItems.comments ,  SalaryHeadItems.value ,  SalaryHeadItems.is_show_salslip , 
            SalaryHeadItems.item_part ,  SalaryHeadItems.status ,  SalaryHeadItems.salary_head_item_order1  
            FROM  salary_head_items  AS  SalaryHeadItems  
            WHERE  status  = 1 and item_type = 'Manually'
            and  SalaryHeadItems.head_fkey  in (select head_pkey 
            from salary_heads where lcase(head_occurance)='variable'
             AND SalaryHeadItems.value = 'Y'
             and status=1)order by SalaryHeadItems.item ");
        $this->set("arr_headitems", $arr_headitems);
        //                    debug($arr_headitems);  
    }
    //The checkdoj function is used to check whether selecting start month is less than Date of joining. By ***ARUL P DAS on 9/12/2019
    public function checkdoj($start_month = '', $emp_fkey = '')
    {
        $this->autoRender = FALSE;
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $month = date('Y-m', strtotime($start_month));
        $result = $this->EmployeeProfessionalDetails->query("select joining_date from emp_proff where emp_fkey='$emp_fkey'");
        $success = 0;
        $msg = "";
        $doj = $result[0]['emp_proff']['joining_date'];
        if ($start_month < $doj) {
            $success = 1;
            //            $msg="Employee not exist in this date<br>&nbsp;";
            $msg = "Employee not exist in this date";
        }
        $result2 = $this->EmployeeProfessionalDetails->query("select count(*) as count from payroll_master where emp_fkey=$emp_fkey and action in ('Processed','Approved') and month_year='$month'");
        if ($result2[0][0]['count'] > 0) {
            $success = 1;
            //            $msg="Salary Processed on this month<br>&nbsp;";
            $msg = "Payroll Already Processed, Please Remove it before upload the fixed payment component";
        }
        // debug($result);
        echo json_encode(array("success" => $success, "msg" => $msg));
    }
    //The below function is used to Remove component of an employee. By ***ARUL P DAS
    public function deleteEmployees()
    {
        $this->autoRender = FALSE;
        $this->EmployeeFixedPaymentUpload->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_ctc_upload_pkey"]) || isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["emp_ctc_upload_pkey"]);
            $remove_date = date('Y-m-d');
            $id_temp = $ar_ids[0];
            $id = intval($id_temp);
            $this->EmployeeFixedPaymentUpload->query('update emp_fixed_component_upload set status=0,end_date_effective="' . $remove_date . '" where emp_fixed_component_upload.emp_fixed_component_upload_pkey=' . $id); //Query edited by **ARUL P DAS on 10/12/2019
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

    public function FixedPaymentSave()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //$this->UserCredentials->useDbConfig = $this->Session->read('ds');  
        //$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');    
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        $this->EmployeeVariableUpload->useDbConfig = $this->Session->read('ds');
        $this->EmployeeFixedPaymentUpload->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');


        $emp_fkey = isset($arr_request_data['emp_fkey1']) ? $arr_request_data['emp_fkey1'] : '';
        $amount = isset($arr_request_data['amount']) ? $arr_request_data['amount'] : '';
        $remarks = isset($arr_request_data['remark']) ? $arr_request_data['remark'] : '';
        $occurance = isset($arr_request_data['occurance']) ? $arr_request_data['occurance'] : '';
        $salary_head_item = isset($arr_request_data['salary_head_item']) ? $arr_request_data['salary_head_item'] : '';
        $start_month_year = isset($arr_request_data['start_month']) ? $arr_request_data['start_month'] : '';
        $end_month_year = isset($arr_request_data['end_month']) ? $arr_request_data['end_month'] : '';
        $mnth = date("Y-m", strtotime('01-' . $start_month_year));

        //The below code is to check whether the employee exist in selected month or already processed the salary on selected month.By **ARUL P DAS on 27/12/2019
        $month = date('Y-m', strtotime($start_month_year));
        $result = $this->EmployeeProfessionalDetails->query("select joining_date from emp_proff where emp_fkey='$emp_fkey'");
        $success = 0;
        $msg = "";
        $doj = $result[0]['emp_proff']['joining_date'];
        if ($start_month_year < $doj) {
            $success = 1;
            // $msg="Employee not exist in this date<br>&nbsp;";
            $msg = "Employee not exist in this date";
        }
        $result2 = $this->EmployeeProfessionalDetails->query("select count(*) as count from payroll_master where emp_fkey=$emp_fkey and action in ('Processed','Approved') and month_year='$month'");
        if ($result2[0][0]['count'] > 0) {
            $success = 1;
            // $msg="Salary Processed on this month<br>&nbsp;";
            $msg = "Salary Processed on this month";
        }
        // debug($result);
        if ($success == 1) {
            echo json_encode(array("success" => 0, "msg" => $msg));
            return;
        }
        //////////Salary check ends here.....

        // $test_start=date("Y-m",strtotime('01-'.$start_month_year))."-01";
        // $test_end=date("Y-m-t",strtotime('01-'.$end_month_year));
        // echo $test_start."<br>".$test_end;

        $check_salary = $this->EmployeeFixedPaymentUpload->query("SELECT * FROM payroll_master WHERE emp_fkey = '$emp_fkey' and month_year = '$mnth' and action in ('Approved','Processed') ");

        if ($check_salary) {
            echo json_encode(array("success" => 0, "msg" => "Payroll Already Processed, Please Remove it before upload Variable "));
            return;
        }

        if (isset($amount) && !empty($amount)) {
            // }

            $salary_headitems = $this->SalaryHeadItems->find('first', array(
                'conditions' => array(
                    'salary_head_item_pkey' => $salary_head_item
                )
            ));
            //    $month_year=($month==0)?Date('m-Y'):$month;


            $head_fkey = isset($salary_headitems['SalaryHeadItems']['head_fkey']) ? $salary_headitems['SalaryHeadItems']['head_fkey'] : '';

            $salary_head = $this->SalaryHeads->find('first', array(
                'conditions' => array(
                    'head_pkey' => $head_fkey
                )
            ));

            $operator = isset($salary_head['SalaryHeads']['head_operator']) ? $salary_head['SalaryHeads']['head_operator'] : '';


            $item = isset($salary_headitems['SalaryHeadItems']['item']) ? $salary_headitems['SalaryHeadItems']['item'] : '';
            $item_type = isset($salary_headitems['SalaryHeadItems']['item_type']) ? $salary_headitems['SalaryHeadItems']['item_type'] : '';
            $item_part = isset($salary_headitems['SalaryHeadItems']['item_part']) ? $salary_headitems['SalaryHeadItems']['item_part'] : '';



            $arr_empvar_data = array();

            if (isset($arr_request_data['fixed_component_upload_pkey']) && $arr_request_data['fixed_component_upload_pkey'] != '') {
                $arr_empvar_data['emp_fixed_component_upload_pkey'] = $arr_request_data['fixed_component_upload_pkey'];
            }

            $arr_empvar_data['emp_fixed_component_upload_pkey'] = isset($arr_empvar_data['emp_fixed_component_upload_pkey']) ? $arr_empvar_data['emp_fixed_component_upload_pkey'] : '';
            $arr_empvar_data['emp_fkey'] = $emp_fkey;
            $arr_empvar_data['salary_head_item_fkey'] = $salary_head_item;
            $arr_empvar_data['salary_head_item_desc'] = $item;
            $arr_empvar_data['uploaded_amount'] = $amount;
            $arr_empvar_data['head_operator'] = $operator;
            $arr_empvar_data['head_type'] = $item_type;
            $arr_empvar_data['item_part'] = $item_part;
            $arr_empvar_data['occurance'] = $occurance;
            $arr_empvar_data['start_date_effective'] = $start_month_year . '-01';
            $arr_empvar_data['end_date_effective'] = $end_month_year;
            // $arr_empvar_data['creation_date'] = date('Y-m-d');
            $arr_empvar_data['created_by'] = $this->Session->read('login_user_id');
            $arr_empvar_data['remarks'] = $remarks;
            $arr_empvar_data['action'] = 'uploaded by form';
            $arr_empvar_data['status'] = 1;

            try {
                //debug($arr_empvar_data);
                $result1 = $this->EmployeeFixedPaymentUpload->save($arr_empvar_data);
                echo json_encode(array("success" => 1, "msg" => "Fixed Payment Saved "));
            } catch (Exception $e) {
                echo json_encode(array("success" => 0, "msg" => "Fixed Payment Saving Failed "));
                //debug($e);
            }
        }
    }
}
