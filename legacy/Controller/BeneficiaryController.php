<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class BeneficiaryController extends AppController {

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Beneficiary','Organization');
    
    /*
     * List Locations Here
     */
    public function index(){
        
    }
    
    /*
     * Add / edit locations form
     */
    public function addeditcontacts($contact_id=0){
        if($contact_id == 0){
            $title = 'Add Beneficiary';
        }else{
            $title = 'Edit Beneficiary';
        }
        $this->set('title', $title);
        $this->Beneficiary-> useDbConfig = $this -> Session -> read('ds');
        $this->layout = NULL;
        $arr_contacts = $this->Beneficiary->find('all', array(
                            'conditions' => array('Beneficiary.contact_id' => $contact_id)
                        ));
        $this->set('contact_id',$contact_id);
        $this->set('arr_contacts',$arr_contacts);
    }
    
    /*
     * Save here
     */
    public function save(){
        $this->autoRender = false;
        $this->Beneficiary-> useDbConfig = $this -> Session -> read('ds');
	$this->Organization-> useDbConfig = $this -> Session -> read('ds');
        $arr_form_data = $this->request->data;
        $org_info=$this->Organization->find("first");
        $arr_form_data['organization_id'] = $org_info['Organization']['organization_id'];
        $this->Beneficiary->save($arr_form_data);
        echo json_encode(array('msg'=>'Beneficiary saved successfully'));
    }
    //EDITED BY MEGHA ON 18/02/2020 FILTER DATA RELATION SHIP AND SEARCH OPTION
    function listcontacts()
    {
        $this->Beneficiary-> useDbConfig = $this -> Session -> read('ds');
        $resp_data = array();
        $this->autoRender = false;
        $arr_data = $this->request->data;
       //debug($arr_data);
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $contact = isset($arr_data['emp']) ? $arr_data['emp'] : '';
        $relationship = isset($arr_data['relationship']) ? $arr_data['relationship'] : '';
        //debug($contact);
        $sort = isset($arr_data['sort'])?$arr_data['sort']:'contact_id';
        $order = isset($arr_data['order'])?$arr_data['order']:'desc';
        
        if($sort != '' && $order !== ''){
            $arr_order = array($sort.' '.$order);
        }else{
            $arr_order = array();
        }
        $conditions = array("Beneficiary.status" => 1);
        if(isset($contact)){
            $conditions[] = "(first_name like '%".$contact."%' OR company_name like '%".$contact."%'  OR email like '%".$contact."%' OR phone like '%".$contact."%' OR city like '%".$contact."%' OR relationship like '%".$contact."%')";
        }
        if(isset($relationship)){
            $conditions[] = "(relationship like '%".$relationship."%')";
        }
        $totalcount = $this->Beneficiary->find("count",array(
                'conditions' => $conditions 
            )
                );
        $arr_contacts = $this->Beneficiary->find("all",array(
                'order'=>$arr_order,
                'conditions' => $conditions ,
                'order'=>array($sort=>$order),
                'limit'=>intval($limit),
                'offset'=>intval($ofst)
            )
                );
       
        $rows = array();
        foreach($arr_contacts as $key=>$val){
            $rows[] = $val["Beneficiary"];
        }
        
          $resp_data["total"] = $totalcount;
          $resp_data["rows"] = $rows;
        
        
      
        
        echo json_encode($resp_data);
    }
    public function deletecontacts($contact_id=0){
        $this->autoRender = false;
		$this->Beneficiary-> useDbConfig = $this -> Session -> read('ds');
        if($contact_id != 0){
            $this->Beneficiary->updateAll(array('status'=>0),array('contact_id'=>$contact_id));
            echo json_encode(array('msg' => 'Beneficiary deletion successfull!'));
        }else{
            echo json_encode(array('msg' => 'Beneficiary deletion failed!'));
        }
    }
    public function downloadempctcformat($ctcuploadtype = 0, $branch = '', $employee = '') {
        $this->autoRender = FALSE;
        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_beneficiary_list.xlsx" : "_beneficiary_list" . strtotime() . ".xlsx";
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
            $worksheet->setCellValueByColumnAndRow(1, 1, "Beneficiary Name");
            $worksheet->setCellValueByColumnAndRow(2, 1, "Code");
            $worksheet->setCellValueByColumnAndRow(3, 1, "Reg No");
            $worksheet->setCellValueByColumnAndRow(4, 1, "Address");
            $worksheet->setCellValueByColumnAndRow(5, 1, "City");
            $worksheet->setCellValueByColumnAndRow(6, 1, "State");
            $worksheet->setCellValueByColumnAndRow(7, 1, "Pin Code");
            $worksheet->setCellValueByColumnAndRow(8, 1, "Email ID");
            $worksheet->setCellValueByColumnAndRow(9, 1, "Relationship");
            $worksheet->setCellValueByColumnAndRow(10, 1, "Phone");
            $worksheet->setCellValueByColumnAndRow(11, 1, "TAN");
            $worksheet->setCellValueByColumnAndRow(12, 1, "PAN No");
            $worksheet->setCellValueByColumnAndRow(13, 1, "GST No");
            $worksheet->setCellValueByColumnAndRow(14, 1, "Bank Name");
            $worksheet->setCellValueByColumnAndRow(15, 1, "Branch");
            $worksheet->setCellValueByColumnAndRow(16, 1, "IFSC Code");
            $worksheet->setCellValueByColumnAndRow(17, 1, "Account No");
            $worksheet->setCellValueByColumnAndRow(18, 1, "Contact Person Name");
            $worksheet->setCellValueByColumnAndRow(19, 1, "Designation");
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
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
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
        $objPHPExcel->getActiveSheet()->getStyle('T1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setTitle('Beneficiary List');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }

    public function uploadandsaveempctc($ctcuploadtype = 0) {
        $this->autoRender = FALSE;
        $ctcuploadtype = 1;
        if ($ctcuploadtype != 0) {
            $authuser['company_code'] = $this->Session->read('company_code');
            $filename = isset($authuser['company_code']) ? $authuser['company_code'] . '_beneficiary_list' . strtotime("now") . '.xlsx' : 'empctc_' . strtotime("now") . '.xlsx';
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
                            $array_mandatory_column_names = array('Beneficiary Name');
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
                        echo json_encode(array('success' => 0, 'msg' => 'Please fill all fields.'));
                        exit;
                    } else {
                        //Continue with save if mandatory field warning is not there
                        //Save employee ctc and return success
                        $this->Beneficiary->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
//                        $empcsvdata = new EmployeeCTCData($ctcuploadtype);
//                        $arr_empcredentials_fields = $empcsvdata->getFieldNames('UserCredentials');
//                        $arr_empdetails_fields = $empcsvdata->getFieldNames('EmployeeDetails');
//                        $arr_empctc_fields = $empcsvdata->getFieldNames('EmployeeCTC');

                        foreach ($arrayempdata as $key => $row) {
                            $arr_empctc_data = array();
                            // $user_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
//                            if ($ctcuploadtype == 1) {
                                $arr_empctc_data['company_name'] = isset($row['Beneficiary Name']) ? $row['Beneficiary Name'] : '';
                                $arr_empctc_data['code'] = isset($row['Code']) ? $row['Code'] : '';
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

//                            $arr_empctc_data = array();
                            $arr_empctc_data['status'] = 1;
//                            $arr_empctc_data['emp_pkey'] = $emp_fkey;
                            $arr_empctc_data['modified_by'] = $this->Session->read('login_user_id');
                            $arr_empctc_data['modified_date'] = date('Y-m-d');

                            
//                                debug($arr_empctc_data);
                                // die();
                                $result1 = $this->Beneficiary->saveAll($arr_empctc_data);

                        }
                    if ($result1 == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Beneficiary List imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Beneficiary List saved successfully'));
                        exit;
                    }
                    }
                    unlink($targetpath);
                   
                } else {
                    unlink($targetpath);
                    
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, Beneficiary List import failed, no data found!'));
                        exit;
                    
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Beneficiary List import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Beneficiary List failed! '));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Beneficiary List import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Beneficiary List failed! '));
                exit;
            }
            exit;
        }
    }
}
