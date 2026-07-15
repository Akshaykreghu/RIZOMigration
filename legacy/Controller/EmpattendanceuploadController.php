<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmpattendanceuploadController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Empattendanceupload';
    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'EmployeeAttendanceUpload', 'EmployeeDetails', 'Units', 'CompanyInfo');
    public $components = array('DatatablesManagement');
    public function index() {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1)));
        $this->set("arr_branches", $arr_branches);
        //debug($arr_branches);
        $emp = $this -> Session -> read("emp_fkey");
           $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
           $joins = array(
            array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
                $arr_employees = $this->EmployeeDetails->find("all",array('joins'=>$joins,'conditions'=>array('status'=>1,'EmployeeProfessionalDetails.attr1'=>$emp)));
        $this->set("arr_employees", $arr_employees);
    }

    public function getemployeenames() {
        $this->autoRender = false;
        $s = $this->request->data;
        $status=$s['status'];
        $emp = $this -> Session -> read("emp_fkey");
           $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
           $joins = array(
            array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
                $arr_employees = $this->EmployeeDetails->find("all",array('joins'=>$joins,'conditions'=>array('status'=>1,'EmployeeProfessionalDetails.attr1'=>$emp)));
        $this->set("arr_employees", $arr_employees);
        //debug($arr_employees);
        $str_empname_options_html = '';
        foreach ($arr_employees as $value) {
            $emp_pkey = isset($value['EmployeeDetails']['emp_pkey']) ? $value['EmployeeDetails']['emp_pkey'] : '';
            $f_name = isset($value['EmployeeDetails']['first_name']) ? $value['EmployeeDetails']['first_name'] : '';
            $l_name = isset($value['EmployeeDetails']['last_name']) ? $value['EmployeeDetails']['last_name'] : '';
            $str_empname_options_html .= '<option value="' . $emp_pkey . '">' . $f_name . $l_name . '</option>';
        }
        echo $str_empname_options_html;
        //debug($str_empname_options_html);
    }

    public function form($emp_fkey=0) {        
        $this->layout = null;
        $this->CompanyInfo->useDbConfig = $this->Session->read('ds');
          $emp = $this -> Session -> read("emp_fkey");
           $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
           $joins = array(
            array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions'=> array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
                $arr_employees = $this->EmployeeDetails->find("all",array('joins'=>$joins,'conditions'=>array('status'=>1,'EmployeeProfessionalDetails.attr1'=>$emp)));
        $this->set("arr_employees", $arr_employees);
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
       $arr_att_types = $this->CompanyInfo->find("all",array('conditions'=>array('active'=>'Y')));
     //  debug($emp_fkey);
        if(isset($arr_att_types[0]['CompanyInfo']['attendance_type'])){
                if($arr_att_types[0]['CompanyInfo']['attendance_type'] == 1){
                    $arr_att_types[0]['CompanyInfo']['attendance_type'] = 'Simple Attendance';
                }else if($arr_att_types[0]['CompanyInfo']['attendance_type'] == 2){
                    $arr_att_types[0]['CompanyInfo']['attendance_type'] = 'Time Attendance';
                }
            }        
        $this->set("arr_att_types", $arr_att_types);
        $data['emp_attendance_upload_pkey'] = 0;
        $data['emp_fkey'] = $this->Session->read('emp_fkey');
       // debug($data['emp_fkey']);
        $data['attendance_type'] = "";
        $data['in_date'] = "";
        $data['in_time'] = "";
        $data['out_date'] = "";
        $data['out_time'] = "";
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
        //debug($_REQUEST['emp_attendance_upload_pkey']);
        if (isset($_REQUEST['emp_attendance_upload_pkey']) && $_REQUEST['emp_attendance_upload_pkey'] != 0) {
            $data_db = $this->EmployeeAttendanceUpload->find("first", array("conditions" => array("emp_attendance_upload_pkey" => $_REQUEST['emp_attendance_upload_pkey'])));
            $data = $data_db['EmployeeAttendanceUpload'];
        }
     //   debug($data);
        $this->set("data", $data);
    }

    public function attendancesave() {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
      //  debug($arr_form_data);
        $originalDate = isset($arr_form_data['in_date']) ? $arr_form_data['in_date'] : '';
        $newDate = date("Y-m-d", strtotime($originalDate));
        $arr_form_data['in_date'] = $newDate;
        $originalDate1 = isset($arr_form_data['out_date']) ? $arr_form_data['out_date'] : '';
        $newDate1 = date("Y-m-d", strtotime($originalDate1));
        $arr_form_data['out_date'] = $newDate1;
        
        $data = array();
        $data['emp_attendance_upload_pkey'] = $arr_form_data['emp_attendance_upload_pkey'];
        $data['attendance_type'] = $arr_form_data['attendance_type'];
        $data['emp_fkey'] =$arr_form_data['emp_fkey'];
        $attintimestamp = strtotime($arr_form_data['in_date']);
        $attouttimestamp = strtotime($arr_form_data['out_date']);
        $data['in_date'] = date('Y-m-d H:i:s', $attintimestamp);
        if($data['attendance_type'] == 1)
        {
             $data['out_date'] = '0000:00:00'; 
        }
 else {
       $data['out_date'] = date('Y-m-d H:i:s', $attouttimestamp);
 }
      
        //$data['in_date'] = $arr_form_data['in_date'];
        $data['in_time'] = $arr_form_data['in_time'];
        $data['status'] = 1;
        $data['created_by'] = $this->Session->read('user_name');
        //$data['out_date'] = $arr_form_data['out_date'];
        $data['out_time'] = $arr_form_data['out_time'];
        $result = $this->EmployeeAttendanceUpload->save($data);
        //debug($result);
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Attendance Uploaded successfully";
        echo json_encode($resp);
    }

    public function load() {

        $this->autoRender = FALSE;
        $this->layout = null;
        $data['emp_attendance_upload_pkey'] = 0;
        $data['emp_fkey'] = "";
        $data['attendance_type'] = "";
        $data['in_date'] = "";
        $data['in_time'] = "";
        $data['out_date'] = "";
        $data['out_time'] = "";
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['emp_attendance_upload_pkey']) && $_REQUEST['emp_attendance_upload_pkey'] != 0) {
            $data_db = $this->EmployeeAttendanceUpload->find("first", array("conditions" => array("emp_attendance_upload_pkey" => $_REQUEST['emp_attendance_upload_pkey'])));
            //	debug($data);
            $data = $data_db['EmployeeAttendanceUpload'];
        }
        $respdata = array('success' => true, "data" => $data);
        echo json_encode($respdata);
    }

    public function listattendance() {
$emp = $this->Session->read('emp_fkey');
        $this->autoRender = FALSE;              
        $arr_request_data = $this->request->data;    
        $month = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
     //   debug($month);
        $first = date('Y-m-d', strtotime($month)); 
        $last = date('Y-m-t', strtotime($month));
        $emp_fkey = isset($arr_request_data['employee'])?$arr_request_data['employee']:'';   
         $branch_code = isset($arr_request_data['branch'])?$arr_request_data['branch']:'';       
 $condition='';
 $condition1='';
 $condition2='';
         
 if($branch_code!= '')
 {
     $condition1=" and ed.branch_code='$branch_code' ";
 }
 if($emp_fkey != '')
 {
     $condition2="and EmployeeAttendanceUpload.emp_fkey='$emp_fkey'   ";
 }
 if($month != '')
 {
     $condition= " and EmployeeAttendanceUpload.created_date BETWEEN '$first' AND '$last'  ";
 }
        
        
        
       
//debug($emp_fkey);        
       
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');      
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;      
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        
        $arr_att = $this->EmployeeAttendanceUpload->find('all',array(
                    'fields'=>"distinct concat(ed.first_name,' ',ed.middile_name,' ',ed.last_name) 
 empname,emp_attendance_upload_pkey,emp_fkey,in_date,in_time,out_date,out_time",
                    //'fields'=>"'".implode(',',$emp_fields)."'",
                    'joins'=>array(
                        array(
                            'table' => 'emp_details',
                            'alias' => 'ed',
                            'type' => 'INNER',
                            'foreignKey' => false,
                            'conditions'=> array('ed.emp_pkey = EmployeeAttendanceUpload.emp_fkey')
                        ),
                        array(
                            'table' => 'branches',
                            'alias' => 'bn',
                            'type' => 'INNER',
                            'foreignKey' => false,
                            'conditions'=> array('ed.branch_code = bn.branch_code')
                        ),
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'empproff',
                            'type' => 'INNER',
                            'foreignKey' => false,
                            'conditions'=> array('ed.emp_pkey = empproff.emp_fkey')
                        )
                    ),
                    'conditions' => array(
                        "EmployeeAttendanceUpload.status=1 and empproff.attr1='$emp' $condition $condition2 $condition1"
                    ),
                    'limit'=>intval($limit)
            ));
            $count = count($arr_att);
        $out = array();       
        foreach ($arr_att as $key => $value) {
            $out['empname'] = isset($value['0']['empname']) ? $value['0']['empname'] : '';
            $out['emp_attendance_upload_pkey'] = isset($value['au']['emp_attendance_upload_pkey']) ? $value['au']['emp_attendance_upload_pkey'] : '';
            $out['emp_fkey'] = isset($value['au']['emp_fkey']) ? $value['au']['emp_fkey'] : '';
            $out['in_date'] = isset($value['au']['in_date']) ? $value['au']['in_date'] : '';
            $out['in_time'] = isset($value['au']['in_time']) ? $value['au']['in_time'] : '';
            $out['out_date'] = isset($value['au']['out_date']) ? $value['au']['out_date'] : '';
            $out['out_time'] = isset($value['au']['out_time']) ? $value['au']['out_time'] : '';
            $resp_att["rows"][$key] = $out;
        }       
        $resp_att["total"] = $count;
        echo json_encode($resp_att); 
       
    }

    public function deleteattendance() {
        $this->autoRender = FALSE;
        $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_attendance_upload_pkeys"])) {
            $ar_ids = explode(",", $_REQUEST["emp_attendance_upload_pkeys"]);

            //debug($ar_ids);

            $this->EmployeeAttendanceUpload->updateAll(array('EmployeeAttendanceUpload.status' => 0), array('EmployeeAttendanceUpload.emp_attendance_upload_pkey' => $ar_ids));

            $result['success'] = true;
            $result['msg'] = "Record(s)  deleted successfully.";
        }

        echo json_encode($result);
    }

    
     public function downloadempctcformat($ctcuploadtype=0){
            $this->autoRender=FALSE;
            
            $str_company_code   =   $this->Session->read('company_code');
            $file_name  = isset($str_company_code)?strtolower($str_company_code)."_employee_attendance.xlsx":"employeectcformat_".strtotime().".xlsx";
            $emp = $this->Session->read('emp_fkey');
            // output headers so that the file is downloaded rather than displayed
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename='.$file_name);
            
            App::import('Vendor', 'EmployeeCTCData', array('file'=>'EmployeeCTCData.php'));
            App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));
            $empctcdata =   new EmployeeCTCData($ctcuploadtype);
            $emp_credentials_schema =   $empctcdata->getFieldHeadings('UserCredentials');
            $emp_details_schema =   $empctcdata->getFieldHeadings('EmployeeDetails');
            $emp_ctc_schema =   $empctcdata->getFieldHeadings('EmployeeAttendanceUpload');
            $emp_schema =   array_merge($emp_credentials_schema, $emp_details_schema, $emp_ctc_schema);
            
            $objPHPExcel = new PHPExcel();
             $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
            $objPHPExcel->getProperties()->setCreator("Administrator");
            $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
            $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
            $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
            $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");            
            
            $objPHPExcel->setActiveSheetIndex(0);
            
            $worksheet = $objPHPExcel->getActiveSheet();
            
            $sheet  =   array($emp_schema);
            foreach($sheet as $row => $columns) {
               foreach($columns as $column => $data) { 
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column)."1", $data);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
               }
            }
           
            //Fill form with existing users 
            $emp_credentials_fields =   $empctcdata->getFieldNames('UserCredentials');
            $emp_details_fields =   $empctcdata->getFieldNames('EmployeeDetails');
            $emp_ctc_fields =   $empctcdata->getFieldNames('EmployeeCTC');
            $emp_fields =   array_merge(array_keys($emp_credentials_fields), array_keys($emp_details_fields), array_keys($emp_ctc_fields));
            
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_empdetails = $this->EmployeeDetails->find('all',array(
                    'fields'=>'UserCredentials.user_id,EmployeeDetails.first_name,EmployeeDetails.middile_name,EmployeeDetails.last_name',
                    //'fields'=>"'".implode(',',$emp_fields)."'",
                    'joins'=>array(
                        array(
                            'table' => 'user_credentials',
                            'alias' => 'UserCredentials',
                            'type' => 'INNER',
                            'foreignKey' => false,
                            'conditions'=> array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
                        ),
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'empproff',
                            'type' => 'INNER',
                            'foreignKey' => false,
                            'conditions'=> array('EmployeeDetails.emp_pkey = empproff.emp_fkey')
                        )
                    ),
                    'conditions' => array(
                        'status'=>1,'empproff.attr1'=>$emp
                    )
            ));
            
            $rowindex = 2;
            $columnindex = 0;
            foreach($arr_empdetails as $rows) {
               $columnindex = 0;
               foreach($rows as $columns) { 
                    foreach($columns as $column) {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowindex, $column);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $columnindex++;
                    }
               }
               $rowindex++;
            }
            
            $objPHPExcel->getActiveSheet()->setTitle('Employee CTC Data');
            
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save(dirname(__FILE__)."/".$file_name);
            readfile(dirname(__FILE__)."/".$file_name);
            unlink(dirname(__FILE__)."/".$file_name);
        }
    
  public function uploadandsaveempctc($ctcuploadtype=0) {
            $this->autoRender   =   FALSE;
            if($ctcuploadtype != 0){
                $authuser['company_code']   =   $this->Session->read('company_code');
                $filename   =   isset($authuser['company_code'])?$authuser['company_code'].'_empattendance_'.  strtotime("now").'.xlsx':'empctc_'.  strtotime("now").'.xlsx';
                $targetpath = getcwd()."/files/".$filename;
                if(move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)){

                    App::import('Vendor', 'PHPExcel', array('file'=>'PHPExcel.php'));

                    $objReader = new PHPExcel_Reader_Excel2007();
                    $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                    $lastColumn  =   $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                    $lastColumn++;
                    $highestRowIndex     =   $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                    $arrayempdata  =   array();
                    $mandatory_fields_warning   =   FALSE;    
                    if($highestRowIndex    >   1){                    
                        //atleast one employee records found
                        $index  =   0;
                        for ($row = 1; $row <= $highestRowIndex; $row++) 
                        {
                            if($row ==  1){
                                //Get mandatory headings array here
                                $array_mandatory_columns    =   array();
                                $array_mandatory_column_names    =   array('User ID','First Name','Last Name');
                                for ($col = 'A'; $col != $lastColumn; $col++) {
                                    $value=$objPHPExcel->getActiveSheet()->getCell($col."1")->getValue();
                                    if(in_array($value, $array_mandatory_column_names)){
                                        array_push($array_mandatory_columns,$col);
                                    }
                                } 
                            }else{
                                for ($col = 'A'; $col != $lastColumn; $col++) {
                                    if(in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col.$row)->getValue() == ''){
                                        $mandatory_fields_warning   =   true;
                                        break 2;
                                    }
                                    $value=$objPHPExcel->getActiveSheet()->getCell($col.$row)->getValue();
                                    $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col."1")->getValue()]=$value;
                                }
                                $index++;
                            }
                        }
                        if($mandatory_fields_warning){
                            //Exit if mandatory fields not entered
                            unlink($targetpath);
                            echo json_encode(array('success'=>0,'msg'=>'Please check all mandatory fields entered'));exit;
                        }else{
                            //Iam here now
                            //debug($arrayempdata);
                            //Continue with save if mandatory field warning is not there
                            //Save employee ctc and return success
                            $this->UserCredentials->useDbConfig = $this->Session->read('ds');  
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');    
                            $this->EmployeeAttendanceUpload->useDbConfig = $this->Session->read('ds');

                            App::import('Vendor', 'EmployeeCTCData', array('file'=>'EmployeeCTCData.php'));
                            $empcsvdata =   new EmployeeCTCData($ctcuploadtype);
                            $arr_empcredentials_fields =   $empcsvdata->getFieldNames('UserCredentials');
                            $arr_empdetails_fields =   $empcsvdata->getFieldNames('EmployeeDetails');
                            $arr_empctc_fields    =   $empcsvdata->getFieldNames('EmployeeAttendanceUpload');
                            foreach ($arrayempdata as $key =>$row) {
                                
                                $user_id = isset($row['User ID'])?$row['User ID']:'';
                                if($user_id == ''){
                                    continue;
                                }
                               
                                //fetch emp_fkey using user_id
                                $arr_usercredentials = $this->UserCredentials->find('first',array(
                                    'fields'=>'emp_fkey',
                                    'conditions'=>array(
                                        'user_id'=>$user_id
                                    )
                                ));
                                $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey'])?$arr_usercredentials['UserCredentials']['emp_fkey']:'';
                            
                                $arr_empctc_data    =   array();
                                $arr_empctc_data['emp_attendance_upload_pkey']='';
                                $arr_empctc_data['attendance_type']    =   1;
                                $arr_empctc_data['emp_fkey']    =   $emp_fkey;
                                $arr_empctc_data['created_by']    =   $this->Session->read('login_user_id');
                                $arr_empctc_data['created_date']    =   date('Y-m-d');
                                foreach ($arr_empctc_fields as $field=>$fieldlabel){
                                    $fieldValue =   $row[$fieldlabel];
                                    $arr_empctc_data[$field]    =   $fieldValue;
                                }
                                try{
                                   
                                    $result1 =   $this->EmployeeAttendanceUpload->save($arr_empctc_data);
                                }  catch (Exception $e){
                                    //debug($e);
                                }
                            }
                        }
                        unlink($targetpath);
                        if($ctcuploadtype == 1){
                            echo json_encode(array('success'=>1,'msg'=>'Employee ctc imported successfully'));exit;
                        }else{
                            echo json_encode(array('success'=>1,'msg'=>'Employee ctc reviced successfully'));exit;
                        }
                    }else{
                        unlink($targetpath);
                        if($ctcuploadtype == 1){
                            echo json_encode(array('success'=>0,'msg'=>'Sorry, employee ctc import failed, no data found!'));exit;
                        }else{
                            echo json_encode(array('success'=>0,'msg'=>'Sorry, employee ctc revision failed, no data found!'));exit;
                        }
                    }                
                }else{
                    if($ctcuploadtype == 1){
                        echo json_encode(array('success'=>0,'msg'=>'Sorry, employee ctc import failed!'));exit;
                    }else{
                        echo json_encode(array('success'=>0,'msg'=>'Sorry, employee ctc revision failed!'));exit;
                    }
                }
            }else{
                if($ctcuploadtype == 1){
                    echo json_encode(array('success'=>0,'msg'=>'Sorry, employee ctc import failed!'));
                }else{
                    echo json_encode(array('success'=>0,'msg'=>'Sorry, employee ctc revision failed!'));exit;
                }
                exit;
            }
        }    
}
