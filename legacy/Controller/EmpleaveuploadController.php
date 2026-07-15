<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EmpleaveuploadController extends AppController
{

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Empleaveupload';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'EmployeeLeaveUpload','LeavePolicy', 'EmployeeLeaveTransaction', 'LeaveRequests', 'SalaryHeadItems', 'EmployeeDetails', 'EmpleaveuploadController', 'EmployeeProfessionalDetails', 'EmployeeLeaveBalanceUpload', 'Units', 'FinancialYear', 'EmployeeCTC', 'LeaveEntries');
    public $components = array('DatatablesManagement');

    public function index()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$arr_leavetypes = $this->SalaryHeadItems->query("select salaLeaveRequestsry_head_item_pkey,item from salary_head_items where head_fkey=6 and upper(value)='Y'");
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave'
        and value='Y' and status=1")));
        $this->set("arr_leavetypes", $arr_leavetypes);
        $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1)));
        $this->set("arr_branches", $arr_branches);
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
    }

    public function form()
    {
        $this->layout = null;
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
        // $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));

        // emp_details and emp_proff tables are joined to get employee company id. by Arul
        $sqlQuery = "SELECT EmployeeDetails.*,emp_proff.emp_company_id FROM emp_details as EmployeeDetails ";
        $sqlQuery .= "LEFT JOIN emp_proff ON (emp_proff.emp_fkey =  EmployeeDetails.emp_pkey) ";
        $sqlQuery .= " WHERE EmployeeDetails.status = 1 ";
        // Edited by Akshay on 10-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');

        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $sqlQuery .= " AND EmployeeDetails.branch_code = '$is_ho' ";
            }
        }
        // End


        $arr_employees = $this->EmployeeDetails->query($sqlQuery);

        $this->set("arr_employees", $arr_employees);
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey" => 6, "value" => "Y", "status" => 1, "NOT" => array("item_part" => "Indirect"))));
        $this->set("arr_leavetypes", $arr_leavetypes);
        $arr_leave_details = $this->LeaveRequests->find("all");
        if (isset($arr_leave_details[0]['LeaveRequests']['FROMHALF'])) {
            if ($arr_leave_details[0]['LeaveRequests']['FROMHALF'] == 1) {
                $arr_leave_details[0]['LeaveRequests']['FROMHALF'] = 'First Half';
            } else if ($arr_leave_details[0]['LeaveRequests']['FROMHALF'] == 2) {
                $arr_leave_details[0]['LeaveRequests']['FROMHALF'] = 'Second Half';
            }
        }
        if (isset($arr_leave_details[0]['LeaveRequests']['TOHALF'])) {
            if ($arr_leave_details[0]['LeaveRequests']['TOHALF'] == 1) {
                $arr_leave_details[0]['LeaveRequests']['TOHALF'] = 'First Half';
            } else if ($arr_leave_details[0]['LeaveRequests']['TOHALF'] == 2) {
                $arr_leave_details[0]['LeaveRequests']['TOHALF'] = 'Second Half';
            }
        }
        $this->set("arr_leave_details", $arr_leave_details);

        // debug($arr_leave_details);
        $data['emp_leave_upload_pkey'] = 0;
        $data['emp_fkey'] = $this->Session->read('emp_fkey');
        $data['leave_type'] = "";
        $data['leave_start_date'] = "";
        $data['leave_start_session'] = "";
        $data['leave_end_date'] = "";
        $data['leave_end_session'] = "";
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['emp_leave_upload_pkey']) && $_REQUEST['emp_leave_upload_pkey'] != 0) {
            $data_db = $this->EmployeeLeaveUpload->find("first", array("conditions" => array("emp_leave_upload_pkey" => $_REQUEST['emp_leave_upload_pkey'])));
            $data = $data_db['EmployeeLeaveUpload'];
        }
        $this->set("data", $data);
        // debug($data);
        $arr_leave_details =  $this->LeaveRequests->find("first", array(
            'fields' => 'salary_head_item_fkey,EMP_fkey',
            'conditions' => array('EMP_fkey' => $data['emp_fkey'])
        ));
        $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;
        $emp_fkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : 0;
        //        $year=$this->EmployeeLeaveUpload->query("SELECT fin_year  FROM  fin_year where  current_date between start_month and end_month");       
        //        $y=$year[0]['fin_year']['fin_year'];       
        //        $emp=$this->Session->read('emp_fkey');
        //        $leavebalance=$this->EmployeeLeaveUpload->query("SELECT `leave_balance_inthe_year_fn`($emp_fkey,$salary_head_item_fkey, $y)");        
        //        $this->set('leavebalance',$leavebalance);
        //        debug($leavebalance);
        $leavebalance = $this->getleavebalance($emp_fkey, $salary_head_item_fkey);
        $this->set('leavebalance', $leavebalance);
    }


    public function leavebalancesave()
    {
        date_default_timezone_set('Asia/Kolkata');
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeLeaveBalanceUpload->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');

        $arr_form_data = $this->request->data;

        //debug($arr_form_data);exit;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $empfkey = $arr_form_data['emp_fkey1'];
        $arr_employees = $this->EmployeeDetails->find("all", 
        array(
            'fields' => "ep.emp_company_id,EmployeeDetails.*",
            'conditions' => array('EmployeeDetails.emp_pkey' => $empfkey, 'EmployeeDetails.status' => 1),
            'joins' => array(
                array(
                    'table' => "emp_proff",
                    'alias' => "ep",
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = ep.emp_fkey')
                )
            ),
            )
        );
        $fname  = ($arr_employees[0]['EmployeeDetails']['first_name']) ? $arr_employees[0]['EmployeeDetails']['first_name'] : '';
        $mname  = ($arr_employees[0]['EmployeeDetails']['middile_name']) ? $arr_employees[0]['EmployeeDetails']['middile_name'] : '';
        $lname  = ($arr_employees[0]['EmployeeDetails']['last_name']) ? $arr_employees[0]['EmployeeDetails']['last_name'] : '';
        $name = $fname . ' ' . $mname . ' ' . $lname;
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("salary_head_item_pkey" => $arr_form_data['leave_type'], "head_fkey" => 6, "value" => "Y", "status" => 1)));

        $item = ($arr_leavetypes[0]['SalaryHeadItems']['item']) ? $arr_leavetypes[0]['SalaryHeadItems']['item'] : '';

        //$data['empid'] = $arr_employees[0]['EmployeeDetails']['emp_id'];
        $data['empid'] = $arr_employees[0]['ep']['emp_company_id'];

        $data['leave_type'] = $arr_form_data['leave_type'];
        // $data['current_leave_balance'] = $arr_form_data['current_leave_balance'];
        $data['leave_balance'] = $arr_form_data['leave_balance'];
        $data['created_by'] = $this->Session->read('login_user_id');
        $data['created_date'] = date('Y-m-d H:i:s');
        $data['status'] = 1;
        $data['emp_name'] = $name;
        $data['item'] = $item;
        $this->EmployeeLeaveBalanceUpload->save($data);
        $emp_upload_id = $this->EmployeeLeaveBalanceUpload->getInsertID();

        /*save data to EmployeeLeaveUpload*/

       // $response = "";
       if (isset($emp_upload_id)) {
            
           $company_code = $this->Session->read('company_code');
           $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (in_array($company_code, $restricted_companies, true)) {

     $response = $this->EmployeeLeaveBalanceUpload->query("select Leave_balance_upload_fn()");
}
       }
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = "Leave Uploaded successfully";
        echo json_encode($resp);
    }
    public function load()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $data['emp_leave_upload_pkey'] = 0;
        $data['emp_fkey'] = "";
        $data['leave_type'] = "";
        $data['leave_start_date'] = "";
        $data['leave_start_session'] = "";
        $data['leave_end_date'] = "";
        $data['leave_end_session'] = "";
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        if (isset($_REQUEST['emp_leave_upload_pkey']) && $_REQUEST['emp_leave_upload_pkey'] != 0) {
            $data_db = $this->EmployeeLeaveUpload->find("first", array("conditions" => array("emp_leave_upload_pkey" => $_REQUEST['emp_leave_upload_pkey'])));
            $data = $data_db['EmployeeLeaveUpload'];
        }
        $respdata = array('success' => true, "data" => $data);
        echo json_encode($respdata);
    }
    public function listleave()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $months = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';
        $conditions = array();
        if ($months) {
            $first = date('Y-m-d', strtotime($months));
            $last = date('Y-m-t', strtotime($months));
            $conditions[] = array("DATE_FORMAT(EmployeeLeaveUpload.created_date,'%Y-%m-%d') BETWEEN 
                            DATE_FORMAT('$first', '%Y-%m-%d') AND LAST_DAY(DATE_FORMAT('$last', '%Y-%m-%d')) ");
        }
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        if ($branch) {
            $conditions[] = array("ed.branch_code='$branch'");
        }
        if ($emp_fkey) {
            $conditions[] = array("EmployeeLeaveUpload.emp_fkey" => $emp_fkey);
        }

        $conditions[]  =   array('EmployeeLeaveUpload.status' => 1);
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        $this->datatable["conditions"] = array("status" => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $joins = array(
            array(
                'table' => "emp_details",
                'alias' => "ed",
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ed.emp_pkey = EmployeeLeaveUpload.emp_fkey')
            )
        );
        $count = $this->EmployeeLeaveUpload->find("count", array("conditions" => $conditions, "joins" => $joins));
        $arr_leave = $this->EmployeeLeaveUpload->find(
            "all",
            array(
                'fields' => "distinct concat(ifnull(ed.first_name,''),' ',ifnull(ed.middile_name,''),' ',ifnull(ed.last_name,'')) 
                empname,emp_leave_upload_pkey,emp_fkey,leave_type,leave_start_date,leave_start_session,leave_end_date,leave_end_session",
                'joins' => $joins,
                'conditions' => $conditions,
                'ORDER' => array('emp_leave_upload_pkey desc'),
                'limit' => intval($limit)
            )
        );
        $this->set("arr_leave", $arr_leave);
        $out = array();
        if ($count > 0) {
            foreach ($arr_leave as $key => $value) {
                $out['empname'] = isset($value[0]['empname']) ? $value[0]['empname'] : '';
                $out['emp_leave_upload_pkey'] = isset($value['EmployeeLeaveUpload']['emp_leave_upload_pkey']) ? $value['EmployeeLeaveUpload']['emp_leave_upload_pkey'] : '';
                $out['emp_fkey'] = isset($value['EmployeeLeaveUpload']['emp_fkey']) ? $value['EmployeeLeaveUpload']['emp_fkey'] : '';
                $out['leave_start_date'] = isset($value['EmployeeLeaveUpload']['leave_start_date']) ? $value['EmployeeLeaveUpload']['leave_start_date'] : '';
                if ($value['EmployeeLeaveUpload']['leave_start_session'] == 1) {
                    $out['leave_start_session'] = 'First Half';
                } else if ($value['EmployeeLeaveUpload']['leave_start_session'] == 2) {
                    $out['leave_start_session'] = 'Second Half';
                }
                $out['leave_end_date'] = isset($value['EmployeeLeaveUpload']['leave_end_date']) ? $value['EmployeeLeaveUpload']['leave_end_date'] : '';
                if ($value['EmployeeLeaveUpload']['leave_end_session'] == 1) {
                    $out['leave_end_session'] = 'First Half';
                } else if ($value['EmployeeLeaveUpload']['leave_end_session'] == 2) {
                    $out['leave_end_session'] = 'Second Half';
                }
                $resp_leave["rows"][$key] = $out;
            }
        }
        $resp_leave["total"] = $count;
        echo json_encode($resp_leave);
    }

    public function deleteleave()
    {
        $this->autoRender = FALSE;
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["emp_leave_upload_pkeys"])) {
            $ar_ids = explode(",", $_REQUEST["emp_leave_upload_pkeys"]);

            $this->EmployeeLeaveUpload->updateAll(array('EmployeeLeaveUpload.status' => 0), array('EmployeeLeaveUpload.emp_leave_upload_pkey' => $ar_ids));

            $result['success'] = true;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }

    /* public function attendancesave(){
      $this->autoRender = false;
      $arr_form_data = $this->request->data;
      $sessionid= $this -> Session -> read("emp_fkey");
      $arr_form_data['emp_attendance_upload_pkey']="1";
      $arr_form_data['emp_fkey']=$sessionid;
      $this->EmployeeAttendanceUpload->save($arr_form_data);
      //debug($this->EmployeeAttendanceUpload->getLastQuery());
      echo json_encode(array('msg'=>'Employee Attendance Upload  saved successfully'));

      } */

    public function getleave()
    {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
        //  $year = date('Y');


        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $_REQUEST["employee"];
        $leavebalance = $_REQUEST["type"];

        $years = $this->FinancialYear->query("select fin_year from fin_year where Year_status = 'OPEN' and is_current_finyear = 'Y' "
                . "  and branch_code in (select branch_code from emp_details where emp_pkey=$cur_emp_key) and status = '1' and vattr1=0");
        $year = isset($years['0']['fin_year']['fin_year']) ? $years['0']['fin_year']['fin_year'] : 0 ;

         // Get employee joining date and leave policy group
    $empDetails = $this->LeaveRequests->query(
        "SELECT joining_date, LEAVEPOLICY_GROUP_ID 
         FROM emp_proff 
         WHERE emp_fkey = '$cur_emp_key'"
    );
    $joining_date = $empDetails[0]['emp_proff']['joining_date'];
    $policy_group_id = $empDetails[0]['emp_proff']['LEAVEPOLICY_GROUP_ID'];

    // Get minimum service from leave policy (in months)
    $arr_leave_policy = $this->LeavePolicy->find("first", [
        'fields' => ['minimum_service'],
        'conditions' => [
            'salary_head_item_fkey' => $leavebalance,
            'LEAVEPOLICY_GROUP_ID' => $policy_group_id
        ]
    ]);
    $minimum_service = isset($arr_leave_policy['LeavePolicy']['minimum_service']) 
        ? (int)$arr_leave_policy['LeavePolicy']['minimum_service'] 
        : 0;
         //edited by athira on 22-09-2025
        $company_code=$this->Session->read('company_code');
        $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
            $leave_days="NULL";
            $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$leavebalance',$leave_days) as LeaveBalance");
        }
        else{
         $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$leavebalance','$year') as LeaveBalance");
        }
        //end
        $this->set('lbalance', $lbalance);

        $resp = $lbalance['0']['0']['LeaveBalance'];
        // Apply minimum service logic
    if ($joining_date && $minimum_service > 0) {
        $eligible_date = date('Y-m-d', strtotime("+$minimum_service months", strtotime($joining_date)));
        $current_date = date('Y-m-d');
        if ($current_date < $eligible_date) {
            $resp = 0; // Employee hasn't completed minimum service
        }
    }

        return isset($resp) ? $resp : '0';
        //debug($arr_request_data);
    }


    public function downloadempctcformat($ctcuploadtype = 0)
    {
        $this->autoRender = FALSE;

        $str_company_code   =   $this->Session->read('company_code');
        $file_name  = isset($str_company_code) ? strtolower($str_company_code) . "_employee_LeaveUpload.xlsx" : "employeectcformat_" . strtotime() . ".xlsx";

        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave'
        and value='Y' and status=1) ")));
        foreach ($arr_leavetypes as $arr_leavetypes) {
            $leave_type[] = $arr_leavetypes['SalaryHeadItems']['occurance'];
        }
        $leavetype =  implode(", ", $leave_type);
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);

        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empctcdata =   new EmployeeCTCData($ctcuploadtype);
        $emp_credentials_schema =   $empctcdata->getFieldHeadings('UserCredentials');
        $emp_details_schema =   $empctcdata->getFieldHeadings('EmployeeDetails');
        $emp_ctc_schema =   $empctcdata->getFieldHeadings('EmployeeLeaveUpload');
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

        $objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);

        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();

        $sheet  =   array($emp_schema);
        foreach ($sheet as $row => $columns) {
            foreach ($columns as $column => $data) {
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $data);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            }
        }

        //Fill form with existing users 
        $emp_credentials_fields =   $empctcdata->getFieldNames('UserCredentials');
        $emp_details_fields =   $empctcdata->getFieldNames('EmployeeDetails');
        $emp_ctc_fields =   $empctcdata->getFieldNames('EmployeeCTC');
        $emp_fields =   array_merge(array_keys($emp_credentials_fields), array_keys($emp_details_fields), array_keys($emp_ctc_fields));
        $emp = $this->Session->read('emp_fkey');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_empdetails = $this->EmployeeDetails->find('all', array(
            'fields' => 'UserCredentials.user_id,EmployeeDetails.first_name,EmployeeDetails.middile_name,EmployeeDetails.last_name',
            //'fields'=>"'".implode(',',$emp_fields)."'",
            'joins' => array(
                array(
                    'table' => 'user_credentials',
                    'alias' => 'UserCredentials',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
                ),
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeeProffesional',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeeProffesional.emp_fkey')
                )
            ),
            'conditions' => array(
                'UserCredentials.status' => 1, 'EmployeeeProffesional.attr1' => $emp
            )
        ));
        $blockNames = array(1, 2);
        $blocksList = implode(", ", $blockNames);
        $rowindex = 2;
        $columnindex = 0;
        foreach ($arr_empdetails as $rows) {
            $columnindex = 0;
            foreach ($rows as $columns) {
                foreach ($columns as $column) {




                    $objValidation = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7, $rowindex)->getDataValidation();
                    $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                    $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                    $objValidation->setAllowBlank(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list');
                    $objValidation->setFormula1('"' . $blocksList . '"');


                    $objValidation = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4, $rowindex)->getDataValidation();
                    $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                    $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                    $objValidation->setAllowBlank(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list');
                    $objValidation->setFormula1('"' . $leavetype . '"');
                    $objValidation = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(8, $rowindex)->getDataValidation();
                    $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                    $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                    $objValidation->setAllowBlank(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list');
                    $objValidation->setFormula1('"' . $blocksList . '"');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $columnindex++;
                }
            }
            $rowindex++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Employee CTC Data');

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }


    public function uploadandsaveempctc($ctcuploadtype = 0)
    {
        $this->autoRender   =   FALSE;
        if ($ctcuploadtype != 0) {
            $authuser['company_code']   =   $this->Session->read('company_code');
            $filename   =   isset($authuser['company_code']) ? $authuser['company_code'] . '_empattendance_' .  strtotime("now") . '.xlsx' : 'empctc_' .  strtotime("now") . '.xlsx';
            $targetpath = getcwd() . "/files/" . $filename;
            if (move_uploaded_file($_FILES['empctc']['tmp_name'][0], $targetpath)) {

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));

                $objReader = new PHPExcel_Reader_Excel2007();
                $objPHPExcel = $objReader->load($targetpath); //ARCHIVE excel2007 dir

                $lastColumn  =   $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $lastColumn++;
                $highestRowIndex     =   $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                $arrayempdata  =   array();
                $mandatory_fields_warning   =   FALSE;
                if ($highestRowIndex    >   1) {
                    //atleast one employee records found
                    $index  =   0;
                    for ($row = 1; $row <= $highestRowIndex; $row++) {
                        if ($row ==  1) {
                            //Get mandatory headings array here
                            $array_mandatory_columns    =   array();
                            $array_mandatory_column_names    =   array('User ID', 'First Name', 'Last Name');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning   =   true;
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
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
                        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        $empcsvdata =   new EmployeeCTCData($ctcuploadtype);
                        $arr_empcredentials_fields =   $empcsvdata->getFieldNames('UserCredentials');
                        $arr_empdetails_fields =   $empcsvdata->getFieldNames('EmployeeDetails');
                        $arr_empctc_fields    =   $empcsvdata->getFieldNames('EmployeeLeaveUpload');
                        foreach ($arrayempdata as $key => $row) {


                            $user_id = isset($row['User ID']) ? $row['User ID'] : '';
                            $leavetype = isset($row['Leave Type']) ? $row['Leave Type'] : '';
                            $Startdates = isset($row['Start dates']) ? $row['Start dates'] : '';
                            $enddates = isset($row['Leave End']) ? $row['Leave End'] : '';
                            $Startsession = isset($row['Start Session']) ? $row['Start Session'] : '';
                            $Endsession = isset($row['End Session']) ? $row['End Session'] : '';
                            if ($user_id == '') {
                                continue;
                            }
                            if (isset($leavetype) && !empty($leavetype) && isset($Startdates) && !empty($Startdates) && isset($enddates) && !empty($enddates) && isset($Startsession) && !empty($Startsession) && isset($Endsession) && !empty($Endsession)) {        //fetch emp_fkey using user_id
                                $arr_usercredentials = $this->UserCredentials->find('first', array(
                                    'fields' => 'emp_fkey',
                                    'conditions' => array(
                                        'user_id' => $user_id
                                    )
                                ));
                                $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';

                                $arr_empctc_data    =   array();
                                $arr_empctc_data['emp_leave_upload_pkey'] = '';
                                $arr_empctc_data['emp_leaveentry_fkey']    =   0;
                                $arr_empctc_data['emp_fkey']    =   $emp_fkey;
                                $arr_empctc_data['status']    =   1;
                                $arr_empctc_data['created_by']    =   $this->Session->read('login_user_id');
                                $arr_empctc_data['created_date']    =   date('Y-m-d');
                                foreach ($arr_empctc_fields as $field => $fieldlabel) {
                                    $fieldValue =   $row[$fieldlabel];
                                    $arr_empctc_data[$field]    =   $fieldValue;
                                }
                                $arr_empctc_data['leave_type']    = $this->getLeaveType($leavetype);
                                try {
                                    echo "employee lebe";
                                    $result1 =   $this->EmployeeLeaveUpload->save($arr_empctc_data);
                                } catch (Exception $e) {
                                }

                                echo  $emp_upload_id = $this->EmployeeLeaveUpload->getInsertID();
                                $leavefromtimestamp = strtotime($arr_empctc_data['leave_start_date']);
                                $leavetotimestamp = strtotime($arr_empctc_data['leave_end_date']);
                                if (isset($emp_upload_id)) {
                                    /*save data to LeaveRequests*/

                                    $arr_data['LEAVEENTRYID'] = '';
                                    $arr_data['emp_leave_upload_fkey'] = $emp_upload_id;
                                    $arr_data['salary_head_item_fkey'] = $arr_empctc_data['leave_type'];
                                    $arr_data['LEAVESTATUS'] = 'Applied';
                                    $arr_data['applied_date'] = date('Y-m-d H:i:s');
                                    $arr_data['FROMDATE'] = date('Y-m-d H:i:s', $leavefromtimestamp);
                                    $arr_data['TODATE'] = date('Y-m-d H:i:s', $leavetotimestamp);
                                    $arr_data['FROMHALF'] = $arr_empctc_data['leave_start_session'];
                                    $arr_data['TOHALF'] = $arr_empctc_data['leave_end_session'];
                                    $arr_data['EMP_fkey'] = $arr_empctc_data['emp_fkey'];
                                    $res = $this->LeaveRequests->save($arr_data);
                                    echo       $leave_entry_id = $this->LeaveRequests->getInsertID();
                                    if (isset($leave_entry_id)) {
                                        $arr_leave_details =  $this->LeaveRequests->find("first", array(
                                            'fields' => 'LEAVEENTRYID,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
                                            'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
                                        ));
                                        $outputParameter = isset($arr_leave_details['LeaveRequests']) ? $arr_leave_details['LeaveRequests'] : array();

                                        //Set status as applied on resubmitting the leave : On 21 Feb 2016
                                        $outputParameter['LEAVESTATUS'] = ($outputParameter['LEAVESTATUS'] == 'Cancelled') ? $outputParameter['LEAVESTATUS'] : 'Applied';
                                        $outputParameter['leave_days'] = ($outputParameter['leave_days'] == 'NULL') ? '0' : '0';

                                        $resp["leavestatus"] = isset($outputParameter['LEAVESTATUS']) ? $outputParameter['LEAVESTATUS'] : '';

                                        $outputParameter['LEAVESTATUS'] = "'" . $outputParameter['LEAVESTATUS'] . "'";
                                        $outputParameter['FROMDATE'] = "'" . $outputParameter['FROMDATE'] . "'";
                                        $outputParameter['TODATE'] = "'" . $outputParameter['TODATE'] . "'";

                                        $out = $this->LeaveRequests->leaveTransactionPrc($outputParameter);
                                        if (!$out/* !== 'Successfull'*/) {
                                            $arr_leave_message = $this->LeaveRequests->find("first", array(
                                                'fields' => 'message',
                                                'conditions' => array('LEAVEENTRYID' => $leave_entry_id)
                                            ));

                                            if (isset($arr_leave_message["LeaveRequests"]['message']) && $arr_leave_message["LeaveRequests"]['message'] != '') {
                                                $resp["warningmessage"] = $arr_leave_message["LeaveRequests"]['message'];
                                            } else {
                                                $resp["message"] = $message;
                                            }
                                        }

                                        /* update EmployeeLeaveUpload*/
                                        $data_arr['emp_leave_upload_pkey'] = $emp_upload_id;
                                        $data_arr['emp_leaveentry_fkey'] = $leave_entry_id;



                                        $result = $this->EmployeeLeaveUpload->save($data_arr);



                                        /*  EmployeeLeaveTransaction update */
                                        $emp_leave_transaction = $this->EmployeeLeaveTransaction->find('all', array(
                                            'conditions' => array(
                                                'LEAVEENTRYID' => $leave_entry_id
                                            )
                                        ));
                                        foreach ($emp_leave_transaction as $value) {
                                            $status = $value['EmployeeLeaveTransaction']['Leavestatus'];
                                            if ($status == 'Applied') {
                                                $data_tran['emp_leave_transactions_pkey'] = $value['EmployeeLeaveTransaction']['emp_leave_transactions_pkey'];
                                                $data_tran['Leavestatus'] = 'Approved';
                                                $this->EmployeeLeaveTransaction->save($data_tran);
                                            }
                                        }
                                        $arr_usercredentials = $this->UserCredentials->find('first', array(
                                            'fields' => 'emp_fkey',
                                            'conditions' => array(
                                                'user_id' => $this->Session->read('login_user_id')
                                            )
                                        ));
                                        $emp_fkey = isset($arr_usercredentials['UserCredentials']['emp_fkey']) ? $arr_usercredentials['UserCredentials']['emp_fkey'] : '';
                                        /*update leave entry*/
                                        $arr_data_final['APPROVEDBY'] = $emp_fkey;
                                        $arr_data_final['ISAPPROVED'] = 1;
                                        $arr_data_final['LEAVESTATUS'] = 'Approved';
                                        $arr_data_final['ISAutherizedby'] = $emp_fkey;
                                        $arr_data_final['ISAutherized'] = 1;
                                        $arr_data_final['Autherized_date'] = date('Y-m-d');
                                        $arr_data_final['REMARKS'] = 'Is uploaded and approved by admin execel';
                                        $arr_data_final['APPROVED_date'] = date('Y-m-d');

                                        $res = $this->LeaveRequests->save($arr_data_final);
                                        echo "final";
                                       // debug($arr_data_final);
                                    }
                                }
                            }
                        }
                    }
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee ctc imported successfully'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 1, 'msg' => 'Employee ctc reviced successfully'));
                        exit;
                    }
                } else {
                    unlink($targetpath);
                    if ($ctcuploadtype == 1) {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee ctc import failed, no data found!'));
                        exit;
                    } else {
                        echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee ctc revision failed, no data found!'));
                        exit;
                    }
                }
            } else {
                if ($ctcuploadtype == 1) {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee ctc import failed!'));
                    exit;
                } else {
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee ctc revision failed!'));
                    exit;
                }
            }
        } else {
            if ($ctcuploadtype == 1) {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee ctc import failed!'));
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, employee ctc revision failed!'));
                exit;
            }
            exit;
        }
    }

    // public function getleavebalance($cur_emp_key, $leavebalance)
    // {
    //     $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
    //     $this->FinancialYear->useDbConfig = $this->Session->read('ds');
    //     $years = $this->FinancialYear->query("select fin_year from fin_year where Year_status = 'OPEN' and is_current_finyear = 'Y' and status = '1'");
    //     $year = $years['0']['fin_year']['fin_year'];
    //     //edited by athira on 22-09-2025
    //     $company_code=$this->Session->read('company_code');
    //      $restricted_companies = [
    //                 'KWMT','ABSG','MBCT','MRBS','STCL',
    //                 'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
    //             ];
    //            if (!in_array($company_code, $restricted_companies, true)) {
    //           $leave_days="NULL";
    //         $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$leavebalance',$leave_days) as LeaveBalance");
    //     }
    //     else{
    //       $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$leavebalance','$year') as LeaveBalance");
    //     }
    //     $this->set('lbalance', $lbalance);
    //     $resp = $lbalance['0']['0']['LeaveBalance'];
    //     return isset($resp) ? $resp : 'null';
    // }

    public function getleavebalance($cur_emp_key, $leavebalance)
    {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');

        $years = $this->FinancialYear->query("
            SELECT fin_year 
            FROM fin_year 
            WHERE Year_status = 'OPEN' 
            AND is_current_finyear = 'Y' 
            AND status = '1'
        ");

        $year = $years['0']['fin_year']['fin_year'];

        // edited by athira on 22-09-2025
        $company_code = $this->Session->read('company_code');

        // Current month in format 2026-05
        $month1 = date('Y-m');

        $att_startdate = $this->LeaveRequests->query("
            SELECT att_start_end_fn(
                DATE_FORMAT('$month1', '%Y-%m-01'),
                1
            ) AS monthly_att_fromdate
        ");

        $att_enddate = $this->LeaveRequests->query("
            SELECT att_start_end_fn(
                DATE_FORMAT('$month1', '%Y-%m-01'),
                2
            ) AS monthly_att_todate
        ");

        $monthly_att_todate = $att_enddate[0][0]['monthly_att_todate'];

        $restricted_companies = [
            'KWMT','ABSG','MBCT','MRBS','STCL',
            'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
        ];

        if (!in_array($company_code, $restricted_companies, true)) {

            $lbalance = $this->LeaveRequests->query("
                SELECT leave_balance_inthe_year_fn(
                    '$cur_emp_key',
                    '$leavebalance',
                    '$monthly_att_todate'
                ) AS LeaveBalance
            ");

        } else {

            $lbalance = $this->LeaveRequests->query("
                SELECT leave_balance_inthe_year_fn(
                    '$cur_emp_key',
                    '$leavebalance',
                    '$year'
                ) AS LeaveBalance
            ");
        }

        $this->set('lbalance', $lbalance);

        $resp = $lbalance[0][0]['LeaveBalance'];

        return isset($resp) ? $resp : 'null';
    }

    public function getLeaveType($type)
    {
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey" => 6, "value" => "Y", "status" => 1, "occurance" => $type)));
        $leave_type = $arr_leavetypes[0]['SalaryHeadItems']['salary_head_item_pkey'];
        return $leave_type;
    }


    // leave balance upload areas sanjun
    public function leavebalance()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);
        $emp_pkey = $this->Session->read('emp_fkey');
        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave'
        and value='Y' and status=1) ")));
        $this->set("arr_leavetypes", $arr_leavetypes);
        $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1)));

        // $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        // emp_details and emp_proff tables are joined to get employee company id. by Arul
        $sqlQuery = "SELECT EmployeeDetails.*,emp_proff.emp_company_id FROM emp_details as EmployeeDetails ";
        $sqlQuery .= "LEFT JOIN emp_proff ON (emp_proff.emp_fkey =  EmployeeDetails.emp_pkey) ";
        $sqlQuery .= " WHERE EmployeeDetails.status = 1 ";
        $arr_employees = $this->EmployeeDetails->query($sqlQuery);

        // Edited by Akshay on 10-2-2025
        $is_ho = 1;
        $company_code = $this->Session->read('company_code');
        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            $this->set('is_ho', $is_ho);
            if ($is_ho != 1) {
                $arr_branches = $this->Units->find("all", array("conditions" => array('branch_code' => $is_ho)));
            }
        }
        $this->set("is_ho", $is_ho);
        // End

        $this->set("arr_branches", $arr_branches);
        $this->set("arr_employees", $arr_employees);
    }

    public function listleavebalance()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        $this->EmployeeLeaveBalanceUpload->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $months = isset($arr_request_data['month']) ? $arr_request_data['month'] : '';

        $conditions = array();

        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';

        if ($branch) {
            $conditions[] = array("ed.branch_code='$branch'");
        }
        if ($emp_fkey) {
            $conditions[] = array("ed.emp_pkey" => $emp_fkey);
        }

        $conditions[]  =   array('EmployeeLeaveBalanceUpload.status' => [0,1]);
        //edited by sinsiya 17-04-2024
        $str_company_code = strtoupper($this->Session->read('company_code'));
        // if($str_company_code != 'TRCM'){
        // $conditions[] = array('EmployeeLeaveBalanceUpload.created_by NOT LIKE' => '%support%');
        // }
        $this->EmployeeLeaveUpload->useDbConfig = $this->Session->read('ds');
        $this->datatable["conditions"] = array("status" => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $joins = array(
            array(
                'table' => "emp_proff",
                'alias' => "ep",
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ep.emp_company_id  = EmployeeLeaveBalanceUpload.empid')
            ),
            array(
                'table' => "emp_details",
                'alias' => "ed",
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('ed.emp_pkey = ep.emp_fkey')
            )
        );

        $count = $this->EmployeeLeaveBalanceUpload->find("count", array("conditions" => $conditions, "joins" => $joins));
        $arr_leave = $this->EmployeeLeaveBalanceUpload->find("all", array(
            'fields' => "distinct concat(ifnull(ed.first_name,''),' ',ifnull(ed.middile_name,''),' ',ifnull(ed.last_name,'')) empname,ed.emp_id,ep.emp_company_id,EmployeeLeaveBalanceUpload.created_date,EmployeeLeaveBalanceUpload.created_by,leave_balance_upload_pky,leave_balance,item",
            'joins' => $joins,
            'conditions' => $conditions,
            'order' => array('EmployeeLeaveBalanceUpload.leave_balance_upload_pky DESC'),
            'limit' => intval($limit),
            'offset' => intval($ofst)
        ));
        $this->set("arr_leave", $arr_leave);

        $resp_leave["rows"] = [];
        $out = array();
        if ($count > 0) {
            foreach ($arr_leave as $key => $value) {
                $out['empname'] = isset($value[0]['empname']) ? $value[0]['empname'] : '';
                $out['emp_id'] = isset($value['ed']['emp_id']) ? $value['ed']['emp_id'] : '';
                $out['emp_company_id'] = isset($value['ep']['emp_company_id']) ? $value['ep']['emp_company_id'] : '';
                $out['emp_leave_upload_pkey'] = isset($value['EmployeeLeaveBalanceUpload']['leave_balance_upload_pky']) ? $value['EmployeeLeaveBalanceUpload']['leave_balance_upload_pky'] : '';
                $out['emp_fkey'] = isset($value['ed']['emp_pkey']) ? $value['ed']['emp_pkey'] : '';
                $out['uploaded_time'] = isset($value['EmployeeLeaveBalanceUpload']['created_date']) ? $value['EmployeeLeaveBalanceUpload']['created_date'] : '';
                $out['item'] = isset($value['EmployeeLeaveBalanceUpload']['item']) ? $value['EmployeeLeaveBalanceUpload']['item'] : '';
                $out['created_by'] = isset($value['EmployeeLeaveBalanceUpload']['created_by']) ? $value['EmployeeLeaveBalanceUpload']['created_by'] : '';
                $out['leave_balance'] = isset($value['EmployeeLeaveBalanceUpload']['leave_balance']) ? $value['EmployeeLeaveBalanceUpload']['leave_balance'] : '';
                $resp_leave["rows"][$key] = $out;
            }
        }
        $resp_leave["total"] = $count;
        echo json_encode($resp_leave);
    }


    //edited by athira on 22-09-2025
    public function downloadLeaveBalanceUploadctc($ctcuploadtype = '', $branch = 0, $emp_fkey = 0)
{
    $this->autoRender = false;
    $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
    $this->EmployeeDetails->useDbconfig = $this->Session->read('ds');

    $str_company_code = $this->Session->read('company_code');
    $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_leave_balance_upload.xlsx" : "employeectcformat_" . time() . ".xlsx";

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $file_name);

    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator("Administrator")
        ->setLastModifiedBy("Administrator")
        ->setTitle("Leave Balance Upload")
        ->setSubject("Leave Balance Upload")
        ->setDescription("Employee Data Format By Forsight");

    $worksheet = $objPHPExcel->getActiveSheet();
    $worksheet->setTitle('Leave Balance Upload');

    // Get all leave types
    $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
    $arr_leavetypes = $this->SalaryHeadItems->find("all", [
        "conditions" => [
            "head_fkey in (select head_pkey from salary_heads where lcase(item_type)='leave' and value='Y' and status=1)",
            "item_part NOT IN('Indirect')"
        ]
    ]);

    // Set header columns
    // Set header columns
$worksheet->setCellValueByColumnAndRow(0, 1, "Sl No.");
$worksheet->setCellValueByColumnAndRow(1, 1, "Employee ID");
$worksheet->setCellValueByColumnAndRow(2, 1, "User ID");
$worksheet->setCellValueByColumnAndRow(3, 1, "Employee Name");
$worksheet->getStyle('A1:D1')->getFont()->setBold(true);

foreach ($arr_leavetypes as $key => $value) {
    $colIndex = $key * 2 + 4; // each leave has 2 columns
    $worksheet->setCellValueByColumnAndRow($colIndex, 1, trim($value['SalaryHeadItems']['item']));
    $worksheet->setCellValueByColumnAndRow($colIndex + 1, 1, "Upload " . trim($value['SalaryHeadItems']['item']));
    $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($colIndex) . '1')->getFont()->setBold(true);
    $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($colIndex + 1) . '1')->getFont()->setBold(true);
}

// === Add borders and auto-size only up to headers ===
$lastColIndex = count($arr_leavetypes) * 2 + 3; // 4 standard columns + 2 per leave

// Apply borders to header row
for ($col = 0; $col <= $lastColIndex; $col++) {
    $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . '1')
        ->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
}

// Auto-size only up to last column
for ($col = 0; $col <= $lastColIndex; $col++) {
    $worksheet->getColumnDimensionByColumn($col)->setAutoSize(true);
}

//edited by athira on 20-01-2026
    $conditions = "";
if (!empty($branch)) {
    $conditions .= " AND ed.branch_code = '" . $branch . "'";
}

if ($emp_fkey != 0) {
    $conditions .= " AND ed.emp_pkey = " . $emp_fkey;
}

$arr_emp = $this->EmployeeCTC->query("
    SELECT CONCAT(ed.first_name,' ',IFNULL(ed.last_name,'')) AS emp_name,
           ed.emp_pkey,
           ed.emp_id,
           pf.emp_company_id
    FROM emp_details ed
    LEFT JOIN emp_proff pf ON ed.emp_pkey = pf.emp_fkey
    JOIN branches b 
        ON ed.branch_code = b.branch_code 
       AND b.status = 1
    WHERE ed.status = 1
    $conditions
    ORDER BY ed.emp_pkey, ed.first_name ASC
");
//end

    $rowIndex = 2;
    $slNo = 1;

    foreach ($arr_emp as $emp) {
        $emp_id = $emp['ed']['emp_id'];
        $emp_pkey = $emp['ed']['emp_pkey'];
        $emp_name = $emp[0]['emp_name'];
        $emp_company_id = $emp['pf']['emp_company_id'];

        $colIndex = 0;
        $worksheet->setCellValueByColumnAndRow($colIndex++, $rowIndex, $slNo);
        $worksheet->setCellValueByColumnAndRow($colIndex++, $rowIndex, $emp_id);
        $worksheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($colIndex++) . $rowIndex, $emp_company_id, PHPExcel_Cell_DataType::TYPE_STRING);
        $worksheet->setCellValueByColumnAndRow($colIndex++, $rowIndex, $emp_name);

        // Left-align values for first 4 columns
        for ($i = 0; $i < 4; $i++) {
            $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($i) . $rowIndex)
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        // Get current financial year
        $years = $this->EmployeeCTC->query("
            SELECT fin_year 
            FROM fin_year 
            WHERE Year_status = 'OPEN' 
              AND is_current_finyear = 'Y'
              AND branch_code IN (SELECT branch_code FROM emp_details WHERE emp_pkey = $emp_pkey)
              AND status = '1' AND vattr1 = 0
        ");
        $year = $years[0]['fin_year']['fin_year'];

        // Get allocated leaves for employee
        $allocated_leaves = $this->SalaryHeadItems->query("
            SELECT salary_head_item_fkey
            FROM leavepolicy
            WHERE status = '1'
              AND LEAVEPOLICY_GROUP_ID IN (
                  SELECT LEAVEPOLICY_GROUP_ID
                  FROM emp_proff
                  WHERE emp_fkey = '$emp_pkey'
              )
        ");

        $allocated_ids = [];
        if (!empty($allocated_leaves)) {
            foreach ($allocated_leaves as $row) {
                $allocated_ids[] = $row['leavepolicy']['salary_head_item_fkey'];
            }
        }

        // Fill leave balances and apply highlight + left alignment + borders
        foreach ($arr_leavetypes as $key => $leave) {
            $sal_head = $leave['SalaryHeadItems']['salary_head_item_pkey'];
            $colIndex = $key * 2 + 4;

            $company_code = $this->Session->read('company_code');
          $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
                $leave_days = "NULL";
                $leave_bal = $this->EmployeeCTC->query("SELECT `leave_balance_inthe_year_fn`('$emp_pkey', '$sal_head', $leave_days) as leave_balance");
            } else {
                $leave_bal = $this->EmployeeCTC->query("SELECT `leave_balance_inthe_year_fn`('$emp_pkey', '$sal_head', '$year') as leave_balance");
            }

            $balance = $leave_bal[0][0]['leave_balance'];

            $worksheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $balance);
            $worksheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex, '');

            // Left align both columns
            for ($i = $colIndex; $i <= $colIndex + 1; $i++) {
                $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($i) . $rowIndex)
                    ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            }

            // Highlight if leave not allocated
            if (!in_array($sal_head, $allocated_ids)) {
                for ($i = $colIndex; $i <= $colIndex + 1; $i++) {
                    $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($i) . $rowIndex)
                        ->getFill()->applyFromArray([
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'C0C0C0']
                        ]);
                }
            }

            // Apply borders to both columns
            for ($i = $colIndex; $i <= $colIndex + 1; $i++) {
                $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($i) . $rowIndex)
                    ->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            }
        }

        // Apply borders to first 4 columns
        for ($i = 0; $i < 4; $i++) {
            $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($i) . $rowIndex)
                ->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }

        $rowIndex++;
        $slNo++;
    }

    // Apply borders to header row
    $lastCol = count($arr_leavetypes) * 2 + 3;
    for ($col = 0; $col <= $lastCol; $col++) {
        $worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . '1')
            ->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
    }


    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $objWriter->save('php://output');
    exit;
}
//end

    public function uploadandsaveempctcleavebalance($ctcuploadtype = 0)
    {

        $this->autoRender = FALSE;
        $company_code=$this->Session->read('company_code');
        // debug($ctcuploadtype);


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
                            $array_mandatory_column_names = array('Employee ID');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue();
                                if (in_array($value, $array_mandatory_column_names)) {
                                    array_push($array_mandatory_columns, $col);
                                }
                            }
                        } else {
                            $array_mandatory_columns = array();
                            $array_mandatory_column_names = array('Employee ID');
                            for ($col = 'A'; $col != $lastColumn; $col++) {
                                if (in_array($col, $array_mandatory_columns) && $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue() == '') {
                                    $mandatory_fields_warning = true;
                                    break 2;
                                }
                                $value = $objPHPExcel->getActiveSheet()->getCell($col . $row)->getValue();
                                $arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()] = $value;


                                // debug($arrayempdata[$index][$objPHPExcel->getActiveSheet()->getCell($col . "1")->getValue()]);
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

                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $this->EmployeeLeaveBalanceUpload->useDbConfig = $this->Session->read('ds');

                        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
                        // debug($arrayempdata);

                        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
                        $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave'
                        and value='Y' and status=1) ")));

                        $arr_leaveTypesItems = [];
                        foreach ($arr_leavetypes as $key => $value) {
                            # code...
                            if ($key != 0) {
                                $key = $key + $key;
                            }
                            $arr_leaveTypesItems[] = trim($value['SalaryHeadItems']['item']);
                        }

                        foreach ($arrayempdata as $key => $row) {
                            date_default_timezone_set('Asia/Kolkata');

                            $arr_empctc_data = array();

                            $emp_id = isset($row['Employee ID']) ? $row['Employee ID'] : '';
                            $emp_company_id = isset($row['User ID']) ? $row['User ID'] : '';
                            $emp_name = isset($row['Employee Name']) ? $row['Employee Name'] : '';
                            // $com = isset($row['components']) ? $row['components'] : '';
                            // $Amount = isset($row['Amount']) ? $row['Amount'] : '0';
                            // $head_fkey = isset($row['Salary Head fkey']) ? $row['Salary Head fkey'] : '';  

                            // if($emp_id == ''){
                            //     continue;
                            // }

                            $arr_usercredentials = $this->EmployeeDetails->find('first', array(
                                'fields' => 'emp_pkey',
                                'conditions' => array(
                                    'emp_id' => $emp_id
                                )
                            ));
                            $emp = isset($arr_usercredentials['EmployeeDetails']['emp_pkey']) ? $arr_usercredentials['EmployeeDetails']['emp_pkey'] : '';
                            // debug($emp);
                            $head_fkey = "";
                            $head_name = "";
                            foreach ($row as $key => $value) {
                                # code...

                                if (in_array($key, $arr_leaveTypesItems)) {
                                    // var_dump("inside here", $key);
                                    // var_dump("Upload Balance " . $key);
                                    $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
                                    $arr_leavetypes = $this->SalaryHeadItems->find("all", array("conditions" => array("TRIM(item)" => $key)));
                                    $head_name = $key;

                                    $head_fkey = isset($arr_leavetypes[0]['SalaryHeadItems']['salary_head_item_pkey']) ? $arr_leavetypes[0]['SalaryHeadItems']['salary_head_item_pkey'] : 1000;
                                } else if (!in_array($key, array("Sl No.", "User ID", "Employee ID", "Employee Name"))) {
                                    // var_dump($value);

                                    if (gettype($value) != 'NULL') { // Zero value also insert by Arul on 18-12-22

                                        $arr_empctc_data = array();
                                        $arr_empctc_data['empid'] = $emp_company_id; // $emp_id
                                        $arr_empctc_data['leave_type'] = $head_fkey;
                                        $user_ids = $arr_empctc_data['created_by'] = $this->Session->read('login_user_id');
                                        $arr_empctc_data['item'] = $head_name; //$key; //isset($head_fkey)?$head_fkey:1000;
                                        $arr_empctc_data['leave_balance'] = ($value) ? $value : '0';
                                        $arr_empctc_data['emp_name'] = $emp_name;
                                        $result1 = $this->EmployeeLeaveBalanceUpload->saveAll($arr_empctc_data);
                                        //  }
                                    }
                                }
                            }


                            $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (in_array($company_code, $restricted_companies, true)) {
      $response = $this->EmployeeLeaveBalanceUpload->query("select Leave_balance_upload_fn()");
    }

                           

                            $company = $this->Session->read('company_code');
                            $user_ids = $this->Session->read('login_user_id');
                        }
                    }

                    unlink($targetpath);

                    echo json_encode(array('success' => 1, 'msg' => 'Employee Leave Balance Updated successfully'));
                    exit;
                } else {
                    unlink($targetpath);
                    echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Leave Balance Update failed, no data found!'));
                    exit;
                }
            } else {
                echo json_encode(array('success' => 0, 'msg' => 'Sorry, Employee Leave Balance Update failed! '));
                exit;
            }
        } else {

            echo json_encode(array('success' => 0, 'msg' => 'Please select a month '));
            exit;

            exit;
        }
    }
    //edited by athira on 22-09-2025
    public function getLeaves() {
    $this->autoRender = false;
     $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
    if ($this->request->is('post')) {
        $emp_pkey = $this->request->data['emp_pkey'];

        $arr_leavetypes = $this->SalaryHeadItems->query("
            SELECT *
            FROM salary_head_items AS SalaryHeadItems
            WHERE head_fkey IN (
                SELECT head_pkey
                FROM salary_heads
                WHERE LCASE(head_occurance) = 'leave'
                  AND status = 1
            )
              AND salary_head_item_pkey IN (
                SELECT salary_head_item_fkey
                FROM leavepolicy
                WHERE status = '1'
                  AND LEAVEPOLICY_GROUP_ID IN (
                        SELECT LEAVEPOLICY_GROUP_ID
                        FROM emp_proff
                        WHERE emp_fkey = '$emp_pkey'
                  )
            )
        ");

        echo json_encode($arr_leavetypes);
    }
}

}
