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
class VendorController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout="default";
    public $name = 'Vendor';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'item_master', 'Event_receiver', 'Units', 'item_purchase', 'item_allocate', 'allocate_details', 'FinancialYear', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC','Contacts');
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
    
    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_customer_vendor_list.xlsx" : "_customer_vendor_list" . strtotime() . ".xlsx";
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        
            $worksheet = $objPHPExcel->getActiveSheet();
            $worksheet->setCellValueByColumnAndRow(0, 1, "Sl No");
            $worksheet->setCellValueByColumnAndRow(1, 1, "Company Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Reg No");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Address");
            $worksheet->setCellValueByColumnAndRow(4, 1, "City");
            $worksheet->setCellValueByColumnAndRow(5, 1, "State");
            $worksheet->setCellValueByColumnAndRow(6, 1, "Pin Code");
            $worksheet->setCellValueByColumnAndRow(7, 1, "Email ID");
            $worksheet->setCellValueByColumnAndRow(8, 1, "Relationship");
            $worksheet->setCellValueByColumnAndRow(9, 1, "Phone");
            $worksheet->setCellValueByColumnAndRow(10, 1, "TAN");
            $worksheet->setCellValueByColumnAndRow(11, 1, "PAN No");
            $worksheet->setCellValueByColumnAndRow(12, 1, "GST No");
            $worksheet->setCellValueByColumnAndRow(13, 1, "Bank Name");
            $worksheet->setCellValueByColumnAndRow(14, 1, "Branch");
            $worksheet->setCellValueByColumnAndRow(15, 1, "IFSC Code");
            $worksheet->setCellValueByColumnAndRow(16, 1, "Account No");
            $worksheet->setCellValueByColumnAndRow(17, 1, "Contact Person Name");
            $worksheet->setCellValueByColumnAndRow(18, 1, "Designation");
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('S1')->getFont()->setBold(true);
        
        $objPHPExcel->getActiveSheet()->setTitle('Vendor List');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsaveempctc($ctcuploadtype = 0) {
        $this->autoRender = FALSE;
        // debug($ctcuploadtype);

        $ctcuploadtype = 1;
        if ($ctcuploadtype != 0) {
            //  echo "hi" ;
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_employee_gross' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

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
                            $array_mandatory_column_names = array('Company Name','Address','City','State','Pin Code','Email ID','Relationship','Phone','PAN No','GST No','Bank Name','Branch','IFSC Code
','Account No','Contact Person Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() === '') {
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
                        echo json_encode(array('success' => 0, 'msg' => 'Please fill all fields. '));
                        exit;
                    } else {
                        //Iam here now
                        //debug($arrayempdata);
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->Contacts->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');

                        foreach ($arrayempdata as $key => $row) {
                            $arr_empctc_data = array();
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
//                            if ($ctcuploadtype == 1) {
                                $arr_empctc_data['company_name'] = isset($row['Company Name']) ? $row['Company Name'] : '';
                                $arr_empctc_data['reg'] = isset($row['Reg No']) ? $row['Reg No'] : '';
                                $arr_empctc_data['address'] = isset($row['Address']) ? $row['Address'] : '';
                                $arr_empctc_data['city'] = isset($row['City']) ? $row['City'] : '';
                                $arr_empctc_data['state'] = isset($row['State']) ? $row['State'] : '';
                                $arr_empctc_data['pincode'] = isset($row['Pin Code']) ? $row['Pin Code'] : '';
                                $arr_empctc_data['email'] = isset($row['Email ID']) ? $row['Email ID'] : '';
                                $arr_empctc_data['relationship'] = isset($row['Relationship']) ? $row['Relationship'] : '';
                                $arr_empctc_data['phone'] = isset($row['Phone']) ? $row['Phone'] : '';
                                $arr_empctc_data['tin'] = isset($row['TAN']) ? $row['TAN'] : '';
                                $arr_empctc_data['pan'] = isset($row['PAN No']) ? $row['PAN No'] : '';
                                $arr_empctc_data['gst'] = isset($row['GST No']) ? $row['GST No'] : '';
//                                $Esi = isset($row['ESI']) ? $row['ESI'] : '';
                                $arr_empctc_data['bank_name'] = isset($row['Bank Name']) ? $row['Bank Name'] : '';
                                $arr_empctc_data['bank_branch'] = isset($row['Branch']) ? $row['Branch'] : '';
                                
//                                $start = isset($row['PF']) ? $row['PF'] : '';
                                $arr_empctc_data['ifsc_code'] = isset($row['IFSC Code']) ? $row['IFSC Code'] : '';
                                $arr_empctc_data['account_no'] = isset($row['Account No']) ? $row['Account No'] : '';
                                $arr_empctc_data['first_name'] = isset($row['Contact Person Name']) ? $row['Contact Person Name'] : '';
                                $arr_empctc_data['c_designation'] = isset($row['Designation']) ? $row['Designation'] : '';
//                                $Branch = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            $emp_id = isset($row['Branch']) ? $row['Branch'] : '';
                            
//                            if($Esi == ''){
//                                continue;
//                            }

                            $date = '';
                            
                            
//                            $emp_fkey = isset($arr_usercredentials['EmployeeProfessionalDetails']['emp_fkey']) ? $arr_usercredentials['EmployeeProfessionalDetails']['emp_fkey'] : '';
//                             debug($arr_empctc_data);
//                            $arr_empctc_data = array();
                            $arr_empctc_data['status'] = 1;
//                            $arr_empctc_data['emp_pkey'] = $emp_fkey;
                            $arr_empctc_data['modified_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['modified_date'] = date('Y-m-d');

                            
//                                debug($arr_empctc_data);
                                // die();
                                $result1 = $this->Contacts->saveAll($arr_empctc_data);

                        }
                    if ($result1 == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Contacts imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Contacts successfully'));
                        exit;
                    }
                    }
                    unlink($targetpath);
                   
                } else {
                    unlink($targetpath);
                    
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Contacts import failed, no data found!'));
                        exit;
                    
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Contacts import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Contacts failed! '));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Contacts import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Contacts failed! '));
                exit;
            }
            exit;
        }
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
        $this->item_allocate->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';
        $conditions = array("item.emp_pkey = item_allocate.emp_fkey");
        
        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $conditions[] = "item.emp_pkey = $emp ";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $conditions[] = "item.branch_code = '$branch_code' ";
        }
        $counts = $this->item_allocate->query("select count(*) count from itm_allocation where status = '1'  ");

        $count = $counts[0][0]['count'];
        $arr_att = $this->item_allocate->find("all",array("fields"=>array("item_allocate.*,item.*"),"joins"=>array(array("table"=>"emp_details","alias"=>"item","type"=>"inner","conditions"=>$conditions)),"conditions"=>array("item_allocate.status"=>1)));

        // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['item_pkey'] = isset($value['item_allocate']['allocation_pkey']) ? $value['item_allocate']['allocation_pkey'] : '';
            $out['name'] = isset($value['item']['first_name']) ? $value['item']['first_name'].' '.$value['item']['last_name'] : '';
            $out['value'] = isset($value['item_allocate']['value']) ? $value['item_allocate']['value'] : '';
            $out['date_allocated'] = isset($value['item_allocate']['date_allocated']) ? $value['item_allocate']['date_allocated'] : '';
            $out['balance_recover_amt'] = isset($value['item_allocate']['balance_recover_amt']) ? $value['item_allocate']['balance_recover_amt'] : '';
            $out['status'] = isset($value['item_purchase']['status']) ? $value['item_purchase']['status'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
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
        $this->Contacts->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        //print_r($arr_form_data);die();
        $result = $this->Contacts->save($arr_form_data);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Vendor Added successfully";
        echo json_encode($resp);
    }

    public function vendorlist(){
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
 $this->Contacts->useDbConfig = $this->Session->read('ds');
        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey=$emp";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $branch_condition = "and ed.branch_code='$branch_code'";
        }
        $counts = $this->Contacts->query("select count(*) count from contacts where status = '1'  and relationship='vendor' ");

        $count = $counts[0][0]['count'];
        $arr_att = $this->Contacts->find("all",array("conditions"=>array("status"=>1,"relationship"=>'vendor')));

       // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
           $out['contact_id'] = isset($value['Contacts']['contact_id']) ? $value['Contacts']['contact_id'] : '';
            
            $out['vendorname'] = isset($value['Contacts']['company_name']) ? $value['Contacts']['company_name'] : '';
            $out['contact'] = isset($value['Contacts']['first_name']) ? $value['Contacts']['first_name'] : '';
            $out['address'] = isset($value['Contacts']['address']) ? $value['Contacts']['address'] : '';
          
            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
        //echo json_encode($resp_att);

    }
    
    public function deleteEmp() {
        $this->autoRender = FALSE;
        $this->Contacts->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($ar_ids);
        $ar_ids = $_REQUEST["ids"];
        $this->Contacts->updateAll(
                array('contacts.status' => 0), array('contacts.contact_id' => $ar_ids));
        $result['success'] = 1;
        $result['msg'] = "Record(s)  deleted successfully.";

        echo json_encode($result);
    }
    
    public function load_qty(){
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $arr_att = $this->item_purchase->query("select * from item_view");
        $this->set("arr_att",$arr_att);                                      
    }

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
    
    public function allocate(){
        $this->Units->useDbConfig = $this->Session->read('ds');
        $arr_branchs = $this->Units->find("all",array("conditions"=>array("status"=>1)));
        $this->set("arr_branchs",$arr_branchs);
    }
    
    public function allocate_form(){
        $this->item_purchase->useDbConfig = $this->Session->read('ds');
        $emp_arr = $this->item_purchase->query("select * from emp_details where status = '1' ");
        $this->set("emp_arr",$emp_arr);
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
        //debug($allocate_arr);
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
