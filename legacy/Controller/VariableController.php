<?php

set_time_limit(500); 

class VariableController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Variable';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'SalaryHeadItems', 'EmployeeDetails', 'Units', 'FinancialYear', 'SalaryHeads', 'EmployeeVariableUpload','EmployeeProfessionalDetails');
    public $components = array('DatatablesManagement');

    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        $plan = $this->EmployeeDetails->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $user_group = $this->Session->read('user_group');
        $this->set("user_group", $user_group);
        $emp_pkey = $this->Session->read('emp_fkey');
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1))));
        //edited by arul - changing emp id as company id
        // $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('order' => array('emp_pkey DESC'), 'conditions' => array('status' => 1))));
        $emp_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details 
	                                                join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 order by first_name ASC');
        // $this->set("emp_list", $emp_list);

        $company_code = $this->Session->read('company_code');
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $this->set(
                    "arr_branches",
                    $arr_branches = $this->Units->find("all", array(
                        "conditions" => array(
                            "status" => 1,
                            "branch_code =" => $is_ho // Add this condition
                        )
                    ))
                );
                $emp_list = $this->EmployeeDetails->query("SELECT emp_pkey, first_name, last_name, emp_company_id 
                FROM emp_details 
                JOIN emp_proff ON emp_details.emp_pkey = emp_proff.emp_fkey 
                WHERE emp_details.status = 1 AND emp_details.branch_code = '$is_ho'
                ORDER BY first_name ASC");
            }
        }
        $this->set('arr_branches', $arr_branches);
        $this->set("emp_list", $emp_list);
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
             and status=1) order by SalaryHeadItems.item ASC");
        $this->set("arr_headitems", $arr_headitems);
    }

    public function variableupload() {
        
    }

      public function downloadvariableuploadform($salaryhead, $branch = '', $emp_pkey = 0) {
       
        
        $this->autoRender = FALSE;
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_variable.xlsx" : "variable_" . strtotime() . ".xlsx";
        $user_group = $this->Session->read("user_group");
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
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(27);
        if ($user_group == 1){ 
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(21);
            
        }else{
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(26); 
        }
       
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        if ($user_group == 1){ 
           $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
           $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
           $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
           $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        }else{
            $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        }
       // $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
       // $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        


        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Variable");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setCellValueByColumnAndRow(0, 1, "User ID");
        $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Company ID");
        $worksheet->setCellValueByColumnAndRow(2, 1, "Employee Name");
        if ($user_group == 1){ 
         $worksheet->setCellValueByColumnAndRow(3, 1, "Gross Salary");
         $worksheet->setCellValueByColumnAndRow(4, 1, "Monthly CTC");
         $worksheet->setCellValueByColumnAndRow(5, 1, "Amount");
         $worksheet->setCellValueByColumnAndRow(6, 1, "Remarks");
        }
        else{
        $worksheet->setCellValueByColumnAndRow(3, 1, "Amount");
        $worksheet->setCellValueByColumnAndRow(4, 1, "Remarks");
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $conditions = array();
        if($branch == '0'){
            $branch = '';
        }
       if ($branch != '' && $emp_pkey != 0) {
            $conditions = array("EmployeeDetails.emp_pkey"=>$emp_pkey,"EmployeeDetails.branch_code"=>$branch,"EmployeeDetails.status"=>1);
        }else if ($emp_pkey != 0 && $branch == '') {
            $conditions = array("EmployeeDetails.emp_pkey"=>$emp_pkey,"EmployeeDetails.status"=>1);
        }else if ($branch != '') {
            $conditions = array("EmployeeDetails.branch_code"=>$branch,"EmployeeDetails.status"=>1);
        }
        else{
            $conditions = array("EmployeeDetails.status"=>1);
        }
        $get_head_itemA_fkey = $this->EmployeeDetails->query(" SELECT item from salary_head_items where salary_head_item_pkey  = $salaryhead ");
       
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_empdetails = $this->EmployeeDetails->find('all', array(
            'fields' => 'UserCredentials.user_id,'
            . 'EPM.emp_company_id,EmployeeDetails.first_name,'
            . 'EmployeeDetails.middile_name,'
            . 'EmployeeDetails.last_name,'
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
                    'conditions' => array('EmployeeDetails.emp_pkey = ECT.emp_fkey','`end_date_effective` IS NULL')
                ), array(
                    'table' => 'emp_proff ',
                    'alias' => 'EPM ',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EPM.emp_fkey')
                )
            ),
            'conditions' => $conditions
        ));
     //   debug($arr_empdetails);die();exit;
        
        
        $rowindex = 2;
        $columnindex = 0;
        foreach ($arr_empdetails as $value) {
            $userid = $value['UserCredentials']['user_id'];
            
            $arr_empdetails_ct = $this->EmployeeDetails->query(" SELECT sum(structure_det_value) as ctc FROM `emp_salary_structure` where head_operator = 'ADDITION'  and lcase(head_type) not in ('manually','variable') and emp_fkey in (select emp_fkey from user_credentials left join emp_details on (emp_details.emp_pkey = user_credentials.emp_fkey) where user_id = '$userid' and status =1) and end_date_effective is null ");
            
            
            $empname = $value['EmployeeDetails']['first_name'] . ' ' . $value['EmployeeDetails']['middile_name'] . ' ' . $value['EmployeeDetails']['last_name'];
            $emp_id = $value['EPM']['emp_company_id'];
            $grossctcamount = ($value['ECT']['emp_anual_ctc']) ? $value['ECT']['emp_anual_ctc']/12 : 0;
            $ctcRate = ($arr_empdetails_ct['0']['0']['ctc']) ? $arr_empdetails_ct['0']['0']['ctc'] : 0;
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $userid);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $emp_id);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $empname);
            if ($user_group == 1){
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowindex, round($grossctcamount,2));
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowindex, round($ctcRate,2));
                
            }
            // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $rowindex++;
        }

        $h = isset($get_head_itemA_fkey['0']['salary_head_items']['item'])?$get_head_itemA_fkey['0']['salary_head_items']['item']:'';
        //$objPHPExcel->getActiveSheet()->setTitle("Variable  " . $h);
        //edited by megha on 27_06_19 other variable allowance not working
        $objPHPExcel->getActiveSheet()->setTitle("Variable  " );
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    public function uploadandsaveempvar($salary_head_item = 0, $month = 0) {
     
        $this->autoRender = FALSE;
        if ($salary_head_item != 0) {
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_empvariable_' . strtotime("now") . '.xlsx' : 'empvar_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empvar']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

                $arrayempdata = array();
                $mandatory_fields_warning = FALSE;
                if ($highestRowIndex > 1) {
                    //atleast one employee records found
                    $index = 0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row == 1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('User ID','Employee Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
							//	debug($value);
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
                        //    debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
                        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeVariableUpload->useDbConfig = $this->Session->read('ds');

//                            App::import('Vendor', 'EmployeeCTCData', array('file'=>'.php'));
//                            $empcsvdata =   new EmployeeCTCData($ctcuploadtype);
//                            $arr_empcredentials_fields =   $empcsvdata->getFieldNames('UserCredentials');
//                            $arr_empdetails_fields =   $empcsvdata->getFieldNames('EmployeeDetails');
//                            $arr_empctc_fields    =   $empcsvdata->getFieldNames('EmployeeLeaveUpload');
                        foreach ($arrayempdata as $key => $row) {

                            $user_id = isset($row['User ID']) ? $row['User ID'] : '';
                            
                            $amount = isset($row['Amount']) ? $row['Amount'] : '';
                            $remarks = isset($row['Remarks']) ? $row['Remarks'] : '';
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
                                $month_year = ($month == 0) ? Date('m-Y') : $month;


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
                                $arr_empvar_data['emp_variables_upload_pkey'] = '';
                                $arr_empvar_data['emp_fkey'] = $emp_fkey;
                                $arr_empvar_data['salary_head_item_fkey'] = $salary_head_item;
                                $arr_empvar_data['month_year'] = $month_year;
                                $arr_empvar_data['salary_head_item_desc'] = $item;
                                $arr_empvar_data['uploaded_amount'] = $amount;
                                $arr_empvar_data['head_operator'] = $operator;
                                $arr_empvar_data['head_type'] = $item_type;
                                $arr_empvar_data['item_part'] = $item_part;
                                $arr_empvar_data['action'] = 'uploaded by excel';
                                $arr_empvar_data['remarks'] = $remarks;
                                $arr_empvar_data['status'] = 1;
                                $arr_empvar_data['created_by'] = $this->Session->read('login_user_id');
                                $arr_empvar_data['created_date'] = date('Y-m-d');

                                try {
//                                    debug($arr_empvar_data);
                                    $result1 = $this->EmployeeVariableUpload->save($arr_empvar_data);
                                } catch (Exception $e) {
                                    //debug($e);
                                }
                            }
                        }
                    }
                    unlink($targetpath);
                    if ($salary_head_item != 0) {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Variable imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee Variable reviced successfully'));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    if ($salary_head_item != 0) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Variable import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Variable revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($salary_head_item != 0) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Variable import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Variable revision failed!'));
                    exit;
                }
            }
        } else {
            if ($salary_head_item != 0) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Variable import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee Variable revision  failed!'));
                exit;
            }
            exit;
        }
    }

    public function employeelistvariable()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        // Edited by Athira on 31-1-2025
        $user_group=$this->Session->read('user_group');
        $emp_pkey=$this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // End
        //debug($arr_request_data);
        $this->EmployeeVariableUpload->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $resp_leave = array();
        $resp_leave["rows"] = array();
        $ofst = ($page - 1) * $limit;
        $mm = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
        if ($mm != '') {
            $month = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';

            $month_condition = " and vu.month_year='$month'";
        } else {
            $month_condition = '';
        }
        //  debug($arr_request_data['employee']);
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $empfkey = '';
        if ($emp_fkey != '') {
            $empfkey = " and vu.emp_fkey='$emp_fkey' ";
        }
        //  debug($arr_request_data['branch']);
        $bb = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        if ($bb != '') {
            $branch = $arr_request_data['branch'];
            $branch_code = " and ed.branch_code='$branch' ";
        } else {
            $branch_code = "";
        }
        // Edited by Akshay on 31-1-2025
        $company_code = $this->Session->read('company_code'); 
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) AS branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if($is_ho!=1){
                $branch_code = " and ed.branch_code='$is_ho' ";
            }
        }
        // End

        $salhead = isset($arr_request_data['salhead']) ? $arr_request_data['salhead'] : '';
        if ($salhead != '') {
            $salheadfkey = " and vu.salary_head_item_fkey='$salhead' ";
        } else {
            $salheadfkey = "";
        }

        // debug($emp_fkey);
        //   $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');

        $this->datatable["conditions"] = array("status" => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $count = $this->EmployeeVariableUpload->find("count", array("conditions" => array('status' => 1)));
        //commented by megha from list query  loading issue on 31/08/2019
        //(select count(*) from emp_salary_slip where emp_fkey = ed.emp_pkey and end_date_effective is null and date_format(STR_TO_DATE(emp_salary_slip.month_year,'%Y-%m'),'%Y-%m') =  date_format(STR_TO_DATE(vu.month_year,'%m-%Y'),'%Y-%m') ) as proccessed, 

        $arr_data = $this->EmployeeVariableUpload->query("select distinct concat(ed.first_name,' ',ed.last_name) empname,
vu.month_year,emp_fkey,emp_variables_upload_pkey,salary_head_item_desc,uploaded_amount,
head_operator, head_type,item_part,remarks,month_year
                from emp_details ed
                INNER join  emp_variables_upload vu on (ed.emp_pkey = vu.emp_fkey)                 
                where vu.status=1 $empfkey $month_condition $branch_code $salheadfkey order by vu.creation_date desc limit $ofst,$limit ");
        $arr_count = $this->EmployeeVariableUpload->query("select distinct concat(ed.first_name,' ',ed.last_name) 
                empname,emp_fkey,emp_variables_upload_pkey,salary_head_item_desc,uploaded_amount,head_operator, head_type,item_part,remarks
                from emp_details ed
                INNER join  emp_variables_upload vu on (ed.emp_pkey = vu.emp_fkey)                 
                where vu.status=1 $empfkey $month_condition $branch_code $salheadfkey
                ORDER BY emp_variables_upload_pkey DESC ");
        $this->set("arr_data", $arr_data);
        $out = array();
        //debug($arr_data);die();
        $counts = count($arr_count);
        // debug($counts);  
        if ($counts > 0) {
            foreach ($arr_data as $key => $value) {
                $out['empname'] = isset($value[0]['empname']) ? $value[0]['empname'] : '';
                $out['emp_variables_upload_pkey'] = isset($value['vu']['emp_variables_upload_pkey']) ? $value['vu']['emp_variables_upload_pkey'] : '';
                $out['emp_fkey'] = isset($value['vu']['emp_fkey']) ? $value['vu']['emp_fkey'] : '';
                $out['salary_head_item_desc'] = isset($value['vu']['salary_head_item_desc']) ? $value['vu']['salary_head_item_desc'] : '';
                $out['uploaded_amount'] = isset($value['vu']['uploaded_amount']) ? $value['vu']['uploaded_amount'] : '';
                $out['head_operator'] = isset($value['vu']['head_operator']) ? $value['vu']['head_operator'] : '';
                $out['head_type'] = isset($value['vu']['head_type']) ? $value['vu']['head_type'] : '';
                $out['month_year'] = isset($value['vu']['month_year']) ? $value['vu']['month_year'] : '';
                $out['item_part'] = isset($value['vu']['item_part']) ? $value['vu']['item_part'] : '';
                $out['remarks'] = isset($value['vu']['remarks']) ? $value['vu']['remarks'] : '';

                $resp_leave["rows"][$key] = $out;
            }
        }
        $resp_leave["total"] = $counts;
        echo json_encode($resp_leave);
    }

    public function form($id = 0) {
        //$this->UserCredentials->useDbConfig = $this->Session->read('ds');
        // $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->EmployeeVariableUpload->useDbConfig = $this->Session->read('ds');
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
                    'conditions' => array('EmployeeVariableUpload.emp_fkey = Employee.emp_pkey')
                )
            );
            $this->set("arr_leaveupoload", $arr_leaveupoload = $this->EmployeeVariableUpload->find("all", array('fields'=>array("EmployeeVariableUpload.*,Employee.first_name,Employee.last_name"), 'joins'=>$joins,  'conditions' => array('EmployeeVariableUpload.status' => 1, 'emp_variables_upload_pkey' => $id))));
//            debug($arr_leaveupoload);
        }
        //  $this->set("arr_headitems",$arr_headitems = $this->SalaryHeadItems->find("all",array('conditions'=>array('status'=>1))));
        $arr_headitems = $this->SalaryHeadItems->query("SELECT  SalaryHeadItems.salary_head_item_pkey ,  SalaryHeadItems.head_fkey ,  
            (SalaryHeadItems.item) ,  SalaryHeadItems.item_type ,  SalaryHeadItems.item_value ,
             SalaryHeadItems.occurance ,  SalaryHeadItems.start_from ,  SalaryHeadItems.comments ,  SalaryHeadItems.value ,  SalaryHeadItems.is_show_salslip , 
            SalaryHeadItems.item_part ,  SalaryHeadItems.status ,  SalaryHeadItems.salary_head_item_order1  
            FROM  salary_head_items  AS  SalaryHeadItems  
            WHERE  status  = 1 and item_type = 'Manually'
            and  SalaryHeadItems.head_fkey  in (select head_pkey 
            from salary_heads where lcase(head_occurance)='variable'
             AND SalaryHeadItems.value = 'Y'
             and status=1)order by SalaryHeadItems.item ASC
 ");
        $this->set("arr_headitems", $arr_headitems);
//                    debug($arr_headitems);  
    }

    public function deleteEmployees() {
        $this->autoRender = FALSE;
        $this->EmployeeVariableUpload->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_ctc_upload_pkey"]) || isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["emp_ctc_upload_pkey"]);
            //debug($ar_ids);
            $this->EmployeeVariableUpload->updateAll(
                    array('EmployeeVariableUpload.status' => 0), array('EmployeeVariableUpload.emp_variables_upload_pkey' => $ar_ids));
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

    public function VariableSave() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //$this->UserCredentials->useDbConfig = $this->Session->read('ds');  
        //$this->EmployeeDetails->useDbConfig = $this->Session->read('ds');    
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeads->useDbConfig = $this->Session->read('ds');
        $this->EmployeeVariableUpload->useDbConfig = $this->Session->read('ds');


        $emp_fkey = isset($arr_request_data['emp_fkey1']) ? $arr_request_data['emp_fkey1'] : '';
        $amount = isset($arr_request_data['amount']) ? $arr_request_data['amount'] : '';
        $remarks = isset($arr_request_data['remark']) ? $arr_request_data['remark'] : '';
        $salary_head_item = isset($arr_request_data['salary_head_item']) ? $arr_request_data['salary_head_item'] : '';
        $month_year = isset($arr_request_data['filterby_month']) ? $arr_request_data['filterby_month'] : '';
        $mnth = date("Y-m",strtotime('01-'.$month_year));
        $check_salary = $this->EmployeeVariableUpload->query("SELECT * FROM payroll_master WHERE emp_fkey = '$emp_fkey' and month_year = '$mnth' and action in ('Approved','Processed') "); 
        
        if($check_salary){
           echo json_encode(array("success"=>0,"msg"=>"Payroll Already Processed, Please Remove it before upload Variable "));
           return;
        }
         //edited by megha upload error
       // if (isset($amount) && !empty($amount)) {
         if (isset($amount) && $amount >=0) {  
           // if (!empty($amount)) {
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

            if (isset($arr_request_data['variable_upload_pkey']) && $arr_request_data['variable_upload_pkey'] != '') {
                $arr_empvar_data['emp_variables_upload_pkey'] = $arr_request_data['variable_upload_pkey'];
            }

            $arr_empvar_data['emp_variables_upload_pkey'] = isset($arr_empvar_data['emp_variables_upload_pkey']) ? $arr_empvar_data['emp_variables_upload_pkey'] : '';
            $arr_empvar_data['emp_fkey'] = $emp_fkey;
            $arr_empvar_data['salary_head_item_fkey'] = $salary_head_item;
            $arr_empvar_data['month_year'] = $month_year;
            $arr_empvar_data['salary_head_item_desc'] = $item;
            $arr_empvar_data['uploaded_amount'] = $amount;
            $arr_empvar_data['head_operator'] = $operator;
            $arr_empvar_data['head_type'] = $item_type;
            $arr_empvar_data['item_part'] = $item_part;
            $arr_empvar_data['action'] = 'uploaded by form';
            $arr_empvar_data['remarks'] = $remarks;
            $arr_empvar_data['status'] = 1;
            $arr_empvar_data['created_by'] = $this->Session->read('login_user_id');
            $arr_empvar_data['created_date'] = date('Y-m-d');

            try {
                //debug($arr_empvar_data);
                $result1 = $this->EmployeeVariableUpload->save($arr_empvar_data);
                echo json_encode(array("success"=>1,"msg"=>"Variable Saved "));
            } catch (Exception $e) {
                echo json_encode(array("success"=>0,"msg"=>"Variable Saving Failed "));
                //debug($e);
            }
        } 
    }

}
