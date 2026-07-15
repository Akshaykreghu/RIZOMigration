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
class UniformController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'Uniform';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'item_master','EmployeeLoanInfo', 'EmployeeLoan' , 'Event_receiver', 'Units', 'item_purchase', 'item_allocate', 'allocate_details', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index() {
        
    }
    public function master(){
        
    }
    public function employeelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';

        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey=$emp";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $branch_condition = "and ed.branch_code='$branch_code'";
        }
        $counts = $this->item_master->query("select count(*) count from item where status = '1'  ");

        $count = $counts[0][0]['count'];
        $arr_att = $this->item_master->find("all",array("conditions"=>array("status"=>1)));

        // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['item_pkey'] = isset($value['item_master']['item_pkey']) ? $value['item_master']['item_pkey'] : '';
            $out['item_name'] = isset($value['item_master']['item_name']) ? $value['item_master']['item_name'] : '';
            $out['item_code'] = isset($value['item_master']['item_code']) ? $value['item_master']['item_code'] : '';
            $out['item_desc'] = isset($value['item_master']['item_desc']) ? $value['item_master']['item_desc'] : '';
            $out['status'] = isset($value['item_master']['status']) ? $value['item_master']['status'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
     public function jsons($branch = '') {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($branch != null) {
            $branch_condition = " and emp_details.branch_code in ('$branch')";
        } else {
            $branch_condition = "";
        }

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " and emp_details.branch_code='$cur_emp_branch' ";
        }
        //employee branch wise sorting ends here

        if ($q != null) {
            $q_condition = "and (first_name like '%$q%' or emp_proff.emp_company_id like '%$q%' ) ";
        } else {
            $q_condition = "";
        }
        $branch_array = $this->EmployeeDetails->query("select emp_details.*,emp_proff.emp_company_id from emp_details join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 $branch_condition $q_condition ORDER BY emp_pkey DESC ");
        // debug($branch_array);
        //$datas = $this->request->data;
        $array = array();
        $branch = array();
        //edited by athira on 02-04-2025
        // $branch[] = array("id" => "0", "text" => "All");
        //end
        foreach ($branch_array as $key => $value) {
            $branch[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $branch;
        echo json_encode($array);
    }
     public function downloadexcels($loan_pkey = 0) {
        $this->autoRender = FALSE;
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1"),"order"=>"loan_month"));//Order by the loan month.
        $count_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(amount_paid) as Totalpaid"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $shifted_times_count = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(emi_transfer) as shifted_times"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","emi_transfer"=>"Y")));
        $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(loan_emi) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","paid_status"=>"S")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $emi_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(loan_emi) as Totalemi"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $balance = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "amount_paid != '0' "), "order" => array("emp_loan_info_pkey desc"), "limit" => 1));

        $user_name = $this->Session->read('user_name');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $str_company_code = $this->Session->read('company_code');

        // $total = $count_paid['0']['0']['Totalpaid'];
        // $count_paid_times = $count_paid_addition['0']['0']['count'];
        // $count_all_times=count($arr_loan_data);
        // $shifted_times=$shifted_times_count['0']['0']['shifted_times'];
        // $emi_pay = $emi_paid['0']['0']['Totalemi'];
        // $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        // $tenure=$arr_loan_master[0]['EmployeeLoan']['tenure'];
        // $balance_amount=$loan_amount-$total;//The balance amount is subtracting total paid amount from loan amount.***ARUL P DAS on 06/11/2019
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        // $file_name = 'Employee Loan Details.xls';
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_employee_emi_details.xls" : "employeeemidetails_" . strtotime() . ".xls";
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setCellValueByColumnAndRow(0, 1, $arr_loan_master['0']['EmployeeInfo']['EmpName'] . "'s EMI Details");
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
        for ($col = 'A'; $col !== 'N'; $col++) {
            $objPHPExcel->getActiveSheet()
                    ->getColumnDimension($col)
                    ->setAutoSize(true);
        }
        $worksheet->mergeCells('A1:N1');
        $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $col = 0;
        $interest= ($arr_loan_master['0']['EmployeeLoan']['intrest_rate']!=0)?$arr_loan_master['0']['EmployeeLoan']['intrest_rate']:'0';
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 2, 'Loan Amount : ' . $arr_loan_master['0']['EmployeeLoan']['loan_amount']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . 2, 'Tenure : ' . $arr_loan_master['0']['EmployeeLoan']['tenure']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . 2, '  Interest Rate : ' .$interest);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . 2, ' Started On : ' . $arr_loan_master['0']['EmployeeLoan']['emi_start_month']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . 2, ' Employee ID : ' .$arr_loan_master['0']['EmployeeInfo']['employee_id']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//added by arul
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . 2, ' Branch : ' .$arr_loan_master['0']['EmployeeInfo']['branch']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . 2, ' Department : ' .$arr_loan_master['0']['EmployeeInfo']['department']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . 2, ' Designation : ' .$arr_loan_master['0']['EmployeeInfo']['designation']);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, 2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
  //end      
        $rowcount = 4;

        $i = 0;

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Sl No');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Month');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'Creation Date');
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  Opening Balance');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, ' EMI');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, ' Interest');
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
        // //$objPHPExcel->getActiveSheet()->getStyle($col + 4)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '   Principal');
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, '   Amount Paid');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '   Closing Balance');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, '   Monthly Status');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Monthly Status added to excel by ARUL P DAS on 14/11/2019

        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, '   Remarks');
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);//Remarks added to excel by ARUL P DAS on 20/11/2019

        $col = 5;
        $j = 0;

        $rowcount = $rowcount + 1;
        $opening_balance=0;$closing_balance=0; 
        $zero = 0;
        foreach ($arr_loan_data as $loan) {
              if($opening_balance==0){$opening_balance=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];}
                                $closing_balance=$opening_balance-$loan['EmployeeLoanInfo']['amount_paid'];
                                // debug($closing_balance);
                                
            if($loan['EmployeeLoanInfo']['loan_emi']=='0' && ($loan['EmployeeLoanInfo']['paid_status']=='A' || $loan['EmployeeLoanInfo']['paid_status']=='P')){
            //This is to avoid to showing removed emi fields by cause of adding additional payment. By *** ARUL P DAS on 16/11/2019
            }else{
                $j+=1;
                $col = 0;


                //The below code is directly fetched from emp_loan_info table. By **** ARUL P DAS on 15/11/2019
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $j);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $loan['EmployeeLoanInfo']['loan_month']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $loan['EmployeeLoanInfo']['opening_balance']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 4
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $loan['EmployeeLoanInfo']['loan_emi']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 5
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $loan['EmployeeLoanInfo']['interest']);
                // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                // //*****Starting of column 6
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $loan['EmployeeLoanInfo']['principle']);
                // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 7
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $loan['EmployeeLoanInfo']['amount_paid']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 8
                if($closing_balance <= 0){
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $zero);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }else{
                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $closing_balance);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
                //*****Starting of column 8
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, $loan['EmployeeLoanInfo']['remarks']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //*****Starting of column 8
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, $loan['EmployeeLoanInfo']['user_remarks']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
          $opening_balance=$closing_balance; 
                $rowcount++;
             
                   if($closing_balance ==0){
                                     $closing_balance = 0;
                                      break;
                                        }   

            }

            
        }

         
        $objPHPExcel->getActiveSheet()->setTitle('Employee EMI Details');
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
     public function downloads($loan_pkey = 0) {
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        $arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1"),"order"=>"loan_month"));//Order by the loan month.
        $count_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(amount_paid) as Totalpaid"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $shifted_times_count = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(emi_transfer) as shifted_times"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","emi_transfer"=>"Y")));
        // $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(amount_paid) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","loan_emi"=>"0","amount_paid >"=>"0")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(loan_emi) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","paid_status"=>"S")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $count_paid_addition_payrolled = $this->EmployeeLoanInfo->query("select count(amount_paid) as paid_count from emp_loan_info where loan_pkey=$loan_pkey and status=1 and paid_status='P'");//paid count by ARUL P DAS on 14/11/2019
        $emi_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(loan_emi) as Totalemi"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $balance = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "amount_paid != '0' "), "order" => array("emp_loan_info_pkey desc"), "limit" => 1));
        $standing_instruction_payed = $this->EmployeeLoanInfo->query("select emp_loan_info_pkey from emp_loan_info where paid_status='P' and loan_pkey=$loan_pkey and remarks like '%Standing%'");//paid count by ARUL P DAS on 14/11/2019
        // debug($standing_instruction_payed);
        $list_of_standing_instruction=array();
        $increment=0;
        foreach ($standing_instruction_payed as $key => $value) {
            $list_of_standing_instruction[$increment]=$value['emp_loan_info']['emp_loan_info_pkey'];
            $increment++;
        }
        $this->set("list_of_standing_instruction",$list_of_standing_instruction);
        $this->set("arr_loan_master", $arr_loan_master);
        $total = $count_paid['0']['0']['Totalpaid'];
        $count_paid_times = $count_paid_addition['0']['0']['count'];
        $count_paid_payroll_times = $count_paid_addition_payrolled['0']['0']['paid_count'];//This is the count of paid amount. That is payrolled amount. By ARUL P DAS on 14/11/2019
        // debug($count_paid_addition_payrolled);
        $count_all_times=count($arr_loan_data);
        $shifted_times=$shifted_times_count['0']['0']['shifted_times'];
        $emi_pay = $emi_paid['0']['0']['Totalemi'];
        $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        $tenure=$arr_loan_master[0]['EmployeeLoan']['tenure'];

        $data=array();
        $data['tenure']=$arr_loan_master[0]['EmployeeLoan']['tenure'];
        $data['count_all_times']=count($arr_loan_data);
        $data['shifted_times']=$shifted_times_count['0']['0']['shifted_times'];
        $data['count_paid_times']=$count_paid_addition['0']['0']['count'];
        $data['count_paid_payroll_times']=$count_paid_addition_payrolled['0']['0']['paid_count'];//This is the count of paid amount. That is payrolled amount. By ARUL P DAS on 14/11/2019

        $this->set("data",$data);//This data array is created for sending the all data as a single data.
        // debug($data);
        // $balance_amount = isset($balance['0']['EmployeeLoanInfo']['closing_balance']) ? $balance['0']['EmployeeLoanInfo']['closing_balance'] : null;
        //The balance amount is subtracting total paid amount from loan amount.***ARUL P DAS on 06/11/2019
        $balance_amount=$loan_amount-$total;
        $this->set("emi_pay", $emi_pay);
        $this->set("total", $total);
        $this->set("tenure", $tenure);
        $this->set("count_paid_times", $count_paid_times);
        $this->set("count_paid_payroll_times", $count_paid_payroll_times);
        $this->set("count_all_times", $count_all_times);
        $this->set("shifted_times",$shifted_times);
        $this->set("balance_amount", $balance_amount);
        $this->set("arr_loan_data", $arr_loan_data);

        //$content ="<h2>hi</h2>";
        $view = new View($this, false);
        $view_output = $view->render('downloads');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
        try {
            $html2pdf = new HTML2PDF('L', 'A4', 'en');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
            //$html2pdf->writeHTML($content);
            $html2pdf->writeHTML($view_output);
            $html2pdf->Output('Loandetails.pdf', 'D');
            $this->render('downloads');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }
    public function view_emi($loan_pkey = 0) {
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        //$arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
        $arr_loan_master = $this->EmployeeLoan->find("all", array("fields" => array("EmployeeLoan.*,EmployeeInfo.*"), "joins" => array(array("table" => "employee_info", "alias" => "EmployeeInfo", "type" => "left", "conditions" => array("EmployeeInfo.emp_pkey = EmployeeLoan.emp_fkey"))), "conditions" => array("emp_loan_pkey" => $loan_pkey)));
       
        //$arr_loan_data = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1"/*, "paid_status" => "p"*/),"order"=>"loan_month"));
        $arr_loan_data = $this->EmployeeLoanInfo->query("select EmployeeLoanInfo.*,EmployeeLoan.remarks from emp_loan_info  EmployeeLoanInfo left join emp_loan as EmployeeLoan on (EmployeeLoanInfo.loan_pkey = EmployeeLoan.emp_loan_pkey) where loan_pkey = '$loan_pkey' and EmployeeLoanInfo.status= 1");
        $count_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(amount_paid) as Totalpaid"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        // debug($count_paid);
        $shifted_times_count = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(emi_transfer) as shifted_times"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","emi_transfer"=>"Y")));
        // $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(amount_paid) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","loan_emi"=>"0","amount_paid >"=>"0")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $count_paid_addition = $this->EmployeeLoanInfo->find("all", array("fields" => array("count(loan_emi) as count"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1","paid_status"=>"S")));//This is to count the payed additional amount. That is not correspond to the tenure.
        $count_paid_addition_payrolled = $this->EmployeeLoanInfo->query("select count(amount_paid) as paid_count from emp_loan_info where loan_pkey=$loan_pkey and status=1 and paid_status='P' and emi_transfer!='Y'");//paid count by ARUL P DAS on 14/11/2019
        $emi_paid = $this->EmployeeLoanInfo->find("all", array("fields" => array("sum(loan_emi) as Totalemi"), "conditions" => array("loan_pkey" => $loan_pkey, "status" => "1")));
        $balance = $this->EmployeeLoanInfo->find("all", array("conditions" => array("loan_pkey" => $loan_pkey, "status" => "1", "amount_paid != '0' "), "order" => array("emp_loan_info_pkey desc"), "limit" => 1));
        $standing_instruction_payed = $this->EmployeeLoanInfo->query("select emp_loan_info_pkey from emp_loan_info where paid_status='P' and loan_pkey=$loan_pkey and remarks like '%Standing%'");//paid count by ARUL P DAS on 14/11/2019
        $arr_update = $this->EmployeeLoan->query("select sum(amount_paid)sum from emp_loan_info where loan_pkey = '$loan_pkey'");
        foreach ($arr_update as $value) {
           $cmp = $value['0']['sum'];
        }
        $list_of_standing_instruction=array();
        $increment=0;
        foreach ($standing_instruction_payed as $key => $value) {
            $list_of_standing_instruction[$increment]=$value['emp_loan_info']['emp_loan_info_pkey'];
            $increment++;
        }
        $this->set("cmp", $cmp);
        $this->set("list_of_standing_instruction",$list_of_standing_instruction);
        $this->set("arr_loan_master", $arr_loan_master);

        $total = $count_paid['0']['0']['Totalpaid'];
        // debug($total);
        $count_paid_times = $count_paid_addition['0']['0']['count'];
        $count_paid_payroll_times = $count_paid_addition_payrolled['0']['0']['paid_count'];//This is the count of paid amount. That is payrolled amount. By ARUL P DAS on 14/11/2019
        // debug($count_paid_addition_payrolled);
        $count_all_times=count($arr_loan_data);
        $shifted_times=$shifted_times_count['0']['0']['shifted_times'];
        $emi_pay = $emi_paid['0']['0']['Totalemi'];
        $loan_amount=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        $tenure=$arr_loan_master[0]['EmployeeLoan']['tenure'];
        // debug($loan_amount);
        $data=array();
        $data['tenure']=$arr_loan_master[0]['EmployeeLoan']['tenure'];
        $data['count_all_times']=count($arr_loan_data);
        $data['shifted_times']=$shifted_times_count['0']['0']['shifted_times'];
        $data['count_paid_times']=$count_paid_addition['0']['0']['count'];
        $data['count_paid_payroll_times']=$count_paid_addition_payrolled['0']['0']['paid_count'];//This is the count of paid amount. That is payrolled amount. By ARUL P DAS on 14/11/2019

        $this->set("data",$data);//This data array is created for sending the all data as a single data.
        // debug($data);
        // $balance_amount = isset($balance['0']['EmployeeLoanInfo']['closing_balance']) ? $balance['0']['EmployeeLoanInfo']['closing_balance'] : null;
        //The balance amount is subtracting total paid amount from loan amount.***ARUL P DAS on 06/11/2019
        $balance_amount=$loan_amount-$total;
        // debug($balance_amount);
        $this->set("emi_pay", $emi_pay);
        $this->set("total", $total);
        $this->set("tenure", $tenure);
        $this->set("count_paid_times", $count_paid_times);
        $this->set("count_paid_payroll_times", $count_paid_payroll_times);
        $this->set("count_all_times", $count_all_times);
        $this->set("shifted_times",$shifted_times);
        $this->set("balance_amount", $balance_amount);
        $this->set("arr_loan_data", $arr_loan_data);
        //debug($arr_loan_master);
        $opening_balance=0;$closing_balance=0;
        $temp_opening_balance=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];
        $remarks="";
        foreach($arr_loan_data as $loan) {
            $loan_info_pkey=$loan['EmployeeLoanInfo']['emp_loan_info_pkey'];
            $paid_status=$loan['EmployeeLoanInfo']['paid_status'];
            $emi_transfer=$loan['EmployeeLoanInfo']['emi_transfer'];
            // if($paid_status=="P" && $emi_transfer!='Y'){
            //     $paid=$this->EmployeeLoanInfo->query("update emp_loan_info set remarks='Paid' where emp_loan_info_pkey=$loan_info_pkey and status=1");
            // }
            if($opening_balance==0){$opening_balance=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];}
            $closing_balance=$opening_balance-$loan['EmployeeLoanInfo']['amount_paid'];
            if($loan['EmployeeLoanInfo']['loan_emi']=='0'){
                $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance='0', closing_balance='0' where emp_loan_info_pkey=$loan_info_pkey and status=1");
            }else{
                if($loan['EmployeeLoanInfo']['loan_emi']>$temp_opening_balance){
                    $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance='$opening_balance', closing_balance='$closing_balance',loan_emi='$temp_opening_balance' where emp_loan_info_pkey=$loan_info_pkey and status=1");
                }else{
                    $result=$this->EmployeeLoanInfo->query("update emp_loan_info set opening_balance='$opening_balance', closing_balance='$closing_balance' where emp_loan_info_pkey=$loan_info_pkey and status=1");
                }
            }
            $temp_opening_balance=$temp_opening_balance-$loan['EmployeeLoanInfo']['loan_emi'];
            $opening_balance=$closing_balance;
        }

    }
    public function completed($loan_pkey = 0) {
    	// debug($loan_pkey);
        $this->autoRender = false;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $arr_loan_master = $this->EmployeeLoan->query("update emp_loan set is_completed = 'Y' where emp_loan_pkey = '$loan_pkey' and status=1 ");
        $arr_loan_master = $this->EmployeeLoan->query("update emp_loan_info set paid_status = 'P' where loan_pkey = '$loan_pkey' and status=1 ");
        

        $arr_update = $this->EmployeeLoan->query("update emp_loan set remarks = 'Closed by admin' where emp_loan_pkey = '$loan_pkey' and status=1 ");
        echo 1;
    }

    public function lists_purchase() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';

        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey=$emp";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $branch_condition = "branch_code = '$branch_code' ";
        }
        $counts = $this->item_purchase->query("select count(*) count from item_purchase where status = '1'  ");

        $count = $counts[0][0]['count'];
        $arr_att = $this->item_purchase->find("all",array("fields"=>array("item_purchase.*,item.*"),"joins"=>array(array("table"=>"item","alias"=>"item","type"=>"inner","conditions"=>array("item.item_pkey = item_purchase.item_fkey"))),"conditions"=>array("item_purchase.status"=>1,$branch_condition)));

        // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['item_pkey'] = isset($value['item_purchase']['item_pkey']) ? $value['item_purchase']['item_pkey'] : '';
            $out['name'] = isset($value['item']['item_name']) ? $value['item']['item_name'] : '';
            $out['vendor'] = isset($value['item_purchase']['vendor']) ? $value['item_purchase']['vendor'] : '';
            $out['qty'] = isset($value['item_purchase']['qty']) ? $value['item_purchase']['qty'] : '';
            $out['date_purchased'] = isset($value['item_purchase']['date_purchased']) ? $value['item_purchase']['date_purchased'] : '';
            $out['PO_number'] = isset($value['item_purchase']['PO_number']) ? $value['item_purchase']['PO_number'] : '';
            $out['branch_code'] = isset($value['item_purchase']['branch_code']) ? $value['item_purchase']['branch_code'] : '';
            $out['status'] = isset($value['item_purchase']['status']) ? $value['item_purchase']['status'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }
    
   
    public function lists() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $name = isset($arr_request_data['name']) ? $arr_request_data['name'] : '';
        
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        
        $ofst = ($page - 1) * $limit;
        
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';
        $check_condition = array();
        $conditions = array("item.emp_pkey = item_allocate.emp_fkey");

        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $conditions[] = "item.emp_pkey = $emp ";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
            $user_group = $this->Session->read('user_group');
            if ($user_group == 2) {
                $company_code = strtolower($this->Session->read('company_code'));
                if($company_code == 'absg'){
                $conditions[] = "item.branch_code = '$branch_code' ";
                }else{
                $cur_emp_key = $this->Session->read("emp_fkey");
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                $conditions[] = "item.branch_code = '$cur_emp_branch' ";
                }
            } else {
                $conditions[] = "item.branch_code = '$branch_code' ";
            }
            //employee branch wise sorting ends here
        }

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $store_condition="";
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
             $company_code = strtolower($this->Session->read('company_code'));
                if($company_code == 'absg'){
                     if ($branch_code != '') {
                $conditions[] = "item.branch_code = '$branch_code' ";
                     }
                }else{
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions[] = "item.branch_code = '$cur_emp_branch' ";
            $store = $this->EmployeeDetails->query("select store_pkey from access_store where emp_fkey=".$cur_emp_key." and store_pkey in (select store_master_pkey from store_master where status=1) and status=1");
        
            if(count($store > 1)){
            // Extract `store_pkey` values into an array
            foreach($store as $val){
             $store_pkeys = array_column($val, 'store_pkey');
           
            // Convert array to comma-separated string
            $store_pkey_str = implode(',', $store_pkeys);
            }
           
            }else{
                $store_pkey_str =   $store[0]['access_store']['store_pkey'];
            }
            $store_condition=" allocation_pkey in (select allocate_fkey from allocate_details where store_code in (".$store_pkey_str."))";
           
            //$store_condition=" allocation_pkey in (select distinct allocate_fkey from allocate_details where store_code in (select store_pkey from access_store where emp_fkey=".$cur_emp_key." and store_pkey in (select store_master_pkey from store_master where status=1) and status=1)) ";
                }
            }

        
        if ($name != 0) {
            $check_condition = " loan.is_completed in ('Y','N') ";
        } else {
            $check_condition = " loan.is_completed = 'N' ";
        }
        
    
        $counts = $this->item_allocate->find("count", array("joins" => array(array("table" => "emp_details", "alias" => "item", "type" => "inner", "conditions" => $conditions), array("table" => "emp_proff", "alias" => "proff", "type" => "inner", "conditions" => array("item.emp_pkey = proff.emp_fkey")), array("table" => "emp_loan", "alias" => "loan", "type" => "left", "conditions" => array("item_allocate.loan_fkey = loan.emp_loan_pkey"))),
            "conditions" => array("item_allocate.status" => 1, $check_condition, "allocation_pkey in (select allocate_fkey from allocate_details where (qty - returned_qty - damaged_qty) > 0)",$store_condition)));
        
        $count = $counts[0][0]['count'];

        $arr_att = $this->item_allocate->find("all", array("fields" => array("loan.remarks,item_allocate.*,item.*,loan.is_completed,loan.loan_amount,ifnull((select sum(amt) from emi_upload where emp_pkey=item_allocate.emp_fkey and loan_pkey= loan.emp_loan_pkey),0) paid_amount,proff.emp_company_id"), "joins" => array(array("table" => "emp_details", "alias" => "item", "type" => "inner", "conditions" => $conditions), array("table" => "emp_proff", "alias" => "proff", "type" => "inner", "conditions" => array("item.emp_pkey = proff.emp_fkey")), array("table" => "emp_loan", "alias" => "loan", "type" => "left", "conditions" => array("item_allocate.loan_fkey = loan.emp_loan_pkey"))),
            "conditions" => array("item_allocate.status" => 1, $check_condition, "allocation_pkey in (select allocate_fkey from allocate_details where (qty - returned_qty - damaged_qty) > 0)",$store_condition),
            
            'order' => array('allocation_pkey' => 'desc'),
            'limit' => intval($limit),
            'offset' => intval($ofst)
        ));
        
        $out = array();
        foreach ($arr_att as $key => $value) {
        	
            $loan_amount = isset($value['loan']['loan_amount']) ? $value['loan']['loan_amount'] : '';
            $paid = isset($value['0']['paid_amount']) ? $value['0']['paid_amount'] : '';
            $remark = isset($value['loan']['remarks']) ? $value['loan']['remarks'] : '';
          
            if($remark == 'Closed by admin'){
            $balance_amount = 0;	
            }else{
            $balance_amount = $loan_amount - $paid;
        	}
            $loan_fkey = isset($value['item_allocate']['loan_fkey']) ? $value['item_allocate']['loan_fkey'] : '';
            $loan = $this->item_allocate->query("select * from emp_loan_info where emp_loan_info_pkey in (select min(emp_loan_info_pkey) from emp_loan_info where loan_pkey = $loan_fkey and amount_paid = 0 and status =1) and status =1");
            $allocated_pkey = isset($value['item_allocate']['allocation_pkey']) ? $value['item_allocate']['allocation_pkey'] : '';

            $out['item_pkey'] = isset($value['item_allocate']['allocation_pkey']) ? $value['item_allocate']['allocation_pkey'] : '';
            $out['name'] = isset($value['item']['first_name']) ? $value['item']['first_name'] . ' ' . $value['item']['last_name'] . ' ' . $value['proff']['emp_company_id'] : '';
            $out['value'] = isset($value['item_allocate']['value']) ? $value['item_allocate']['value'] : '';
            $out['date_allocated'] = isset($value['item_allocate']['date_allocated']) ? $value['item_allocate']['date_allocated'] : '';
            $out['balance_recover_amt'] = $balance_amount;
            $out['loan_fkey'] = isset($value['item_allocate']['loan_fkey']) ? $value['item_allocate']['loan_fkey'] : '';
            $out['status'] = isset($value['item_purchase']['status']) ? $value['item_purchase']['status'] : '';
            $out['is_completed'] = isset($value['loan']['is_completed']) ? $value['loan']['is_completed'] : '';
            $resp_att["rows"][$key] = $out;
            // $balance = $paid + $emi_amount;
        }
        $resp_att["total"] = $counts;
        echo json_encode($resp_att); 
        
    }
    
    public function returns($allocate_pkey = 0){
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $item_details = $this->item_master->query(" SELECT ad.*,im.item_desc,im.item_master_pkey FROM `allocate_details` ad 
            join item_master im on (im.item_master_pkey = ad.item_purchase_fkey)
            where ad.qty > ad.returned_qty and ad.status = '1'  and allocate_fkey = $allocate_pkey ");
//        debug($item_details);
        $this->set("item_details",$item_details); 
        $allocate_master = $this->item_master->query(" select * from itm_allocation where allocation_pkey = $allocate_pkey ");
        //debug($item_details);
        $this->set("allocate_master",$allocate_master); 
        $loan_pkeys = $allocate_master['0']['itm_allocation']['loan_fkey'];
        $loan_master = $this->item_master->query(" select * from emp_loan where emp_loan_pkey = $loan_pkeys ");
        $loan_status = $loan_master['0']['emp_loan']['is_completed'];
        $arr_loans_pendig = $this->item_master->query(" SELECT SUM(amt) as amts FROM emi_upload WHERE loan_pkey = '$loan_pkeys' and status = '1'  ");
        $loan_amt = $allocate_master['0']['itm_allocation']['value'];
        $emi_paidd = $arr_loans_pendig['0']['0']['amts'];
        $bal_amt = $loan_amt - $emi_paidd;
        if($loan_status =='Y'){
            $bal_amt = 0;
        }
        $this->set("bal_amt",$bal_amt); 
        $this->render('return');
        
    }
    
    public function return_item(){
        $this->autoRender = false;
        $this->layout = null;
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $qty = $arr_form_data['qty'];
        $allocate_pkey = $arr_form_data['pkey'];
        $emp_fkey = $arr_form_data['emp_fkey'];
        $item_fkey = $arr_form_data['item_fkey'];
        $date = date("Y-m-d",strtotime($arr_form_data['ret_date']));
        $damaged_qty = isset($arr_form_data['damaged_qty'])?$arr_form_data['damaged_qty']:0;
        
        try{

            $item_details = $this->item_master->query(" update allocate_details set returned_qty = returned_qty+$qty,damaged_qty = damaged_qty+$damaged_qty where allocate_details_pkey = '$allocate_pkey' ");
            $item_details = $this->item_master->query(" insert into returned_stocks(allocation_fkey,item_fkey,qty,emp_pkey,returned_stocks,tr_date) values('$allocate_pkey','$item_fkey','$qty','$emp_fkey','$damaged_qty','$date') ");

            return 'Returned Successfully';
            
        } catch (Exception $ex) {
            return $ex->getMessage();
        } catch (mysqli_sql_exception $ex){
            return $ex->getMessage();
        }
    }

    public function form($item_pkey = 0) {
        $this->item_master->useDbConfig = $this->Session->read('ds');
        //debug($item_pkey);
        $item_details = "";
        if (isset($item_pkey) && $item_pkey != 0) {
            $item_details = $this->item_master->query(" select * from item where item_pkey = '$item_pkey' ");
        }
        //debug($item_details);
        $this->set("item_details",$item_details);
    }
    
    public function Master_save(){
        $this->autoRender = false;
        $this->layout = null;
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $result = $this->item_master->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Uniform Added successfully";
        echo json_encode($resp);
    }
    
    public function deleteEmp() {
        $this->autoRender = FALSE;
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($ar_ids);
        $ar_ids = $_REQUEST["ids"];
        $this->item_master->updateAll(
                array('item_master.status' => 0), array('item_master.item_pkey' => $ar_ids));
        $result['success'] = 1;
        $result['msg'] = "Record(s)  deleted successfully.";

        echo json_encode($result);
    }
    
     public function load_qty($store_fkey = 0,$date = ''){
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
//        $arr_att = $this->item_purchase->query("select item_master.item_master_pkey,item_master.item_code,item_master.item_desc,
//        (select ifnull(sum(item_qty),0) from stock_details where item_fkey = item_master.item_master_pkey and status = 1 and store_fkey = '$store_fkey') as qty,
//        (select ifnull(sum(qty-returned_qty),0) from allocate_details where item_purchase_fkey = item_master.item_master_pkey and store_code = '$store_fkey' and status = 1) as allocated 
//        from item_master where status = 1 and item_master_pkey in (select item_fkey from stock_details where item_qty > 0 and store_fkey = '$store_fkey' ) ");
        
//        $arr_att = $this->item_purchase->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
//                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
//                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
//                item_except_grn_allocation_view  where  creation_date <= '$date' and store_master_pkey= '$store_fkey' and item_master_pkey not in (select item_master_pkey  from item_except_grn_allocation_view where item_state='TRANSFER' and item_qty >0 and creation_date <= '$date' and store_master_pkey= '$store_fkey' )
//                   group by store_master_pkey,item_master_pkey,item_code,item_desc
//                union all
//                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
//                grn_stock_details_date_view where gr_date <= '$date' and store_master_pkey= '$store_fkey' group by store_master_pkey,item_master_pkey,item_code,item_desc
//                union all
//                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
//                from Item_allocation_details_view where date_allocated <= '$date' and store_master_pkey= '$store_fkey' group by store_master_pkey,item_master_pkey,item_code,item_desc) a
//                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
//                grn_stock_det_rate_date_view where gr_date <= '$date' and store_master_pkey= '$store_fkey' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
//                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
//                group by store_master_pkey,item_master_pkey
//                order by 4,5");
        $arr_att = $this->item_purchase->query(" select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty) qtyuptodate ,
b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from
(select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
item_except_grn_allocation_view where creation_date <= '$date' and store_master_pkey= '$store_fkey'  
group by store_master_pkey,store_location,item_master_pkey,item_code,item_desc
union all
select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
grn_stock_details_date_view where gr_date <= '$date' and store_master_pkey= '$store_fkey'  group by store_master_pkey,item_master_pkey,item_code,item_desc
union all
select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty
from Item_allocation_details_view where date_allocated <= '$date' and store_master_pkey= '$store_fkey'  group by store_master_pkey,item_master_pkey,item_code,item_desc) a
left join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
grn_stock_det_rate_date_view where gr_date <= '$date' and store_master_pkey= '$store_fkey'  group by store_master_pkey,item_master_pkey,item_code,item_desc) b
on (a.store_master_pkey= b.store_master_pkey
and a.item_master_pkey=b.item_master_pkey)
group by a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,
b.po_rate
order by 4,5;");
       
        /* $arr_att = $this->item_purchase->query("select a.store_master_pkey,a.store_location,a.item_master_pkey,a.item_code,a.item_desc,sum(item_qty)qtyuptodate ,
                b.po_rate ,sum(item_qty)* b.po_rate as closingvalue from 
                (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from
                item_except_grn_allocation_view  where   store_master_pkey= '$store_fkey' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,sum(item_qty) item_qty from 
                grn_stock_details_date_view where  store_master_pkey= '$store_fkey' group by store_master_pkey,item_master_pkey,item_code,item_desc
                union all
                select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,(sum(qty)-sum(returned_qty ))*-1 item_qty	
                from Item_allocation_details_view where  store_master_pkey= '$store_fkey' group by store_master_pkey,item_master_pkey,item_code,item_desc) a
                join (select store_master_pkey,store_location,item_master_pkey,item_code,item_desc,max(po_rate) po_rate from
                grn_stock_det_rate_date_view where  store_master_pkey= '$store_fkey' group by store_master_pkey,item_master_pkey,item_code,item_desc) b
                on (a.store_master_pkey= b.store_master_pkey and a.item_master_pkey=b.item_master_pkey)
                group by store_master_pkey,item_master_pkey
                order by 4,5"); */
        
        $this->set("arr_att",$arr_att);                                      
    }
//created by megha for calling function to select least count of available qty
     public function finditem_qty($date_allocate = 0,$store = 0,$item_pkey = 0){
        $this->autoRender = FALSE;
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $item_details = $this->item_master->query("SELECT `stock_bal_qty_fn`('$date_allocate','$store','$item_pkey')qty");
        $qty = $item_details['0']['0']['qty'];
        //debug($qty);
        echo json_encode($qty);
    }
 //created by megha   
    public function form_purchase($item_pkey = 0){
        $this->item_master->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        //debug($item_pkey);
        $item_details = "";
        $arr_att = $this->item_master->find("all",array("conditions"=>array("status"=>1)));
        $arr_branchs = $this->Units->find("all",array("conditions"=>array("status"=>1)));
        if (isset($item_pkey) && $item_pkey != 0) {
            $item_details = $this->item_master->query(" select * from item_purchase where item_pkey = '$item_pkey' ");
        }
        //debug($arr_branchs);
        $this->set("arr_att",$arr_att);
        $this->set("arr_branchs",$arr_branchs);
        $this->set("item_details",$item_details);
    }
    
    public function purchase_file(){
        $this->Units->useDbConfig = $this->Session->read('ds');
        $arr_branchs = $this->Units->find("all",array("conditions"=>array("status"=>1)));
        $this->set("arr_branchs",$arr_branchs);
    }
    
    public function save_Purchase(){
        $this->autoRender = false;
        $this->layout = null;
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $result = $this->item_purchase->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Purchase Added successfully";
        echo json_encode($resp);
    }
    
     public function deleteEmppurchase() {
        $this->autoRender = FALSE;
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($ar_ids);
        $ar_ids = $_REQUEST["ids"];
        $this->item_purchase->updateAll(
                array('item_purchase.status' => 0), array('item_purchase.item_pkey' => $ar_ids));
        $result['success'] = 1;
        $result['msg'] = "Record(s)  deleted successfully.";

        echo json_encode($result);
    }
    
   public function allocate() {
        $this->Units->useDbConfig = $this->Session->read('ds');
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $created_by = $this->Session->read('login_user_id');
            $company_code = strtolower($this->Session->read('company_code'));
            $cur_emp_branch = $this->EmployeeDetails->query("select roc from store_master join access_store on (access_store.store_pkey = store_master.store_master_pkey) 
                 where emp_fkey='$cur_emp_key' and  store_master.status=1 and access_store.status=1");
          
          //  $conditions = "status = 1 and branch_code in (select distinct branch_code from emp_details where status =1 and emp_pkey in (select emp_fkey from emp_loan where status = 1 and created_by='$created_by'))";
            $arr_branchs = array();
            foreach ($cur_emp_branch as $value) {
            $branch = isset($value['store_master']['roc'])?$value['store_master']['roc']:'';
            $conditions = array("branch_code" => $branch, "status" => 1);
            $arr_branch = $this->Units->find("all", array("conditions" => $conditions));
            if(!empty($arr_branch))
                $arr_branchs[] = $arr_branch['0'];
        }
            } else {
            $conditions = array("status" => 1);
            $arr_branchs = $this->Units->find("all", array("conditions" => $conditions));
        }
      
       
        $this->set("arr_branchs", $arr_branchs);
    }

    public function allocate_form() {
        $this->item_purchase->useDbConfig = $this->Session->read('ds');   
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition =  " and branch_code='$cur_emp_branch' ";
        }else{
            $branch_condition="";
        }
        //employee branch wise sorting ends here   
        $emp_arr = $this->item_purchase->query("select emp_details.*,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where status = '1' ".$branch_condition);
        $this->set("emp_arr", $emp_arr);
        if ($this->Session->read('user_group') == 2) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $conditions = "and store_master_pkey in(select store_pkey from access_store where emp_fkey = '$emp_fkey' and status=1)";
			
        } else {
            $conditions = "";
        }
        $store_arr = $this->item_purchase->query("select * from store_master where status = '1'  $conditions");
        $this->set("store_arr", $store_arr);
    }
    
    public function getval($item_fkey = 0){
        $this->autoRender = FALSE;
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $get_details = $this->item_purchase->query("SELECT `sales_price` FROM `additional_details` WHERE `item_master_fkey` = '$item_fkey' AND `status` = '1' LIMIT 50");
        echo isset($get_details['0']['additional_details']['sales_price'])?$get_details['0']['additional_details']['sales_price']:0;
    }
    
    public function getendmonth(){
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $next = strtotime($arr_form_data['start_month']);
        $tenure = $arr_form_data['tenure'] - 1;
        $starts = date("Y-m",$next);
        $months = date("Y-m",strtotime("+$tenure month",$next));
        if($next == false){
            $months = " ";
        }
        echo $months;
    }

    public function allocate_emp(){
        
        
        $this->autoRender = false;
        $this->layout = null;
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $this->allocate_details->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        
        
        
       
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        $count = $arr_form_data['tenure'] = $arr_form_data['tenures'];
        $loan_amt = $arr_form_data['loan_amount'] = $arr_form_data['value'];
        $emp_pkey = $arr_form_data['emp_fkey'];
        $interest = $arr_form_data['intrest_rate'] = '0';
        
        $emi = $arr_form_data['emiss'];
        $next = strtotime($arr_form_data['emi_start_date']);
        $arr_form_data['emi_amount'] = $emi;
        $arr_form_data['emi_start_month'] = date("Y-m",$next);
        $count1 = $count - 1;
        $months = $arr_form_data['emi_end_month'] = date("Y-m",strtotime("+$count1 month",$next));
        $closing_balance = $loan_amt;
        $result = $this->EmployeeLoan->save($arr_form_data);
        $loan_pkey = $this->EmployeeLoan->getLastInsertId();
        $arr_loan_data = array();
        for($i=0;$i<$count;$i++){
            $opening_balance = $closing_balance;
            $interests = $opening_balance*$interest/100;
            $interestpaid = $interests*1/12;
            $principal = $emi - $interestpaid;
            $closing_balance = $opening_balance - $principal;
             $arr_loan_data['opening_balance'] = $opening_balance;
             $arr_loan_data['closing_balance'] = $closing_balance;
             $arr_loan_data['principle'] = $principal;
             $arr_loan_data['interest'] = $interestpaid;
             $arr_loan_data['amount_to_paid'] = $emi;
             $arr_loan_data['loan_emi'] = $emi;
             $starts = date("Y-m",strtotime("+".$i." month",$next));
             //$next = strtotime($starts);
             //debug($starts);
            //debug("opening Balance :".$opening_balance.", EMI :".$emi.", Inetrest: ".$interestpaid.", Principal: ".$principal.", Closing Balance: ".$closing_balance);
            $result = $this->EmployeeLoanInfo->query("insert into emp_loan_info(opening_balance,emp_fkey,loan_pkey,loan_month,closing_balance,principle,interest,amount_to_paid,loan_emi)"
                    . "values('$opening_balance','$emp_pkey','$loan_pkey','$starts', '$closing_balance','$principal','$interestpaid','$emi','$emi')");
        }
        $arr_form_data['loan_fkey'] = $loan_pkey;
        //debug($arr_form_data);
        $result = $this->item_allocate->save($arr_form_data);
        $allocate_ids = $this->item_allocate->getLastInsertId();
        $allocate_arr = array();
        $allocate_arr = $arr_form_data['item_name'];
        $allocate_qty = $arr_form_data['item_qty'];
        //debug($allocate_arr);
        $counts = count($allocate_arr);
        $arr_form_data1 = array();
        $arr_form_data1['allocate_fkey'] = $allocate_ids;
        $arr_form_data1['store_code'] = $arr_form_data['store_fkey'];
        
        for($i=0;$i<$counts;$i++){
            $item_pkey = substr($allocate_arr[$i], 0, strpos($allocate_arr[$i], '-'));;
            $item_qty = $allocate_qty[$i];
            $arr_form_data1['item_purchase_fkey'] = $item_pkey;
            $arr_form_data1['qty'] = $item_qty;
            $result1 = $this->allocate_details->saveAll($arr_form_data1);
        }
        
        
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLoanInfo->useDbConfig = $this->Session->read('ds');
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Item Allocated successfully";
        echo json_encode($resp);
        
    }


    public function allocate_save(){
         $this->autoRender = false;
        $this->layout = null;
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $this->allocate_details->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $result = $this->item_allocate->save($arr_form_data);
        $allocate_ids = $this->item_allocate->getLastInsertId();
        $allocate_arr = array();
        $allocate_arr = $arr_form_data['item_name'];
        $allocate_qty = $arr_form_data['item_qty'];
        $counts = count($allocate_arr);
        $arr_form_data1 = array();
        $arr_form_data1['allocate_fkey'] = $allocate_ids;
        
        for($i=0;$i<$counts;$i++){
            $item_pkey = $allocate_arr[$i];
            $item_qty = $allocate_qty[$i];
            $arr_form_data1['item_purchase_fkey'] = $item_pkey;
            $arr_form_data1['qty'] = $item_qty;
            $result1 = $this->allocate_details->saveAll($arr_form_data1);
        }
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Purchase Added successfully";
        echo json_encode($resp);
    } 

}
