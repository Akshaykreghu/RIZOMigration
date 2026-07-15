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
 * 
 * DeviceEmployeeInfo by Arul P Das on 23_7_21
 */
class DeviceEmployeeInfoController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'DeviceEmployeeInfo';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'Device');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */
    public function index()
    {
        //$this->layout = FALSE;
        $company_code = $this->Session->read('company_code');
        $devices = $this->Device->query("SELECT `DeviceId`, `DeviceFName`, `SerialNumber` FROM `mypayrol_control_db`.`devices` WHERE `company_code` = '".$company_code."'");
        $this->set('devices',$devices);

        // back button
         $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $this->CentralUserCredentials->setDataSource('controldb');

        $company_code = $this->Session->read('company_code');

        $data = $this->CentralUserCredentials->find('first', array(
            'conditions' => array(
                'CentralUserCredentials.company_code' => $company_code
            ),
            'fields' => array('CentralUserCredentials.plan_id'),
            'recursive' => -1
        ));

        $planId = !empty($data)
            ? (int)$data['CentralUserCredentials']['plan_id']
            : null;
              $user_group = $this->Session->read('user_group');
      
        $this->set('planId', $planId);
        $this->set('user_group', $user_group);
    }

    public function listEmpDev()
    {
        $this->autoRender = false;
        $company_code = $this->Session->read('company_code');
        $condition = '';
        $emp = (isset($_REQUEST['emp'])) ? $_REQUEST['emp'] : ''; // This is the search parameter
        $device_id = (isset($_REQUEST['device_id'])) ? $_REQUEST['device_id'] : ''; // This is the search parameter
        if ($emp) {
            $condition .= " AND (emp_name LIKE '%" . $emp . "%' OR emp_device_comp_branch.deviceid LIKE '%" . $emp . "%' OR DeviceFName LIKE '%" . $emp . "%' OR SerialNumber LIKE '" . $emp . "%' OR emp_device_id LIKE '%" . $emp . "%' OR emp_username LIKE '" . $emp . "%') ";
        }
        if ($device_id) {
            $condition .= " AND emp_device_comp_branch.deviceid LIKE '%" . $device_id . "%' ";
        }
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'creation_date';
        $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
        // echo $company_code;
        $json_output = array();
        $result = array();
        $i = 0;
        // $emp_devices = $this->Device->query("SELECT edcb.emp_device_comp_branch_seq,edcb.deviceid,d.DeviceFName,emp_device_id,emp_fkey,emp_id,emp_username,emp_name,edcb.branch_code, branch_name , cb.Company_code FROM mypayrol_control_db.emp_device_comp_branch edcb LEFT JOIN mypayrol_control_db.company_branches cb ON cb.branch_code = edcb.branch_code AND cb.status = 1 LEFT JOIN mypayrol_control_db.devices d ON d.DeviceId = edcb.deviceid WHERE cb.Company_code = '" . $company_code . "' AND edcb.status = 1 LIMIT $limit OFFSET $ofst");
        // $emp_devices_count = $this->Device->query("SELECT count(emp_fkey) as total FROM mypayrol_control_db.emp_device_comp_branch edcb LEFT JOIN mypayrol_control_db.company_branches cb ON cb.branch_code = edcb.branch_code AND cb.status = 1 LEFT JOIN mypayrol_control_db.devices d ON d.DeviceId = edcb.deviceid WHERE cb.Company_code = '" . $company_code . "' AND edcb.status = 1 ");

        // Edited by Akshay on 7-2-2025
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $is_ho = 1;
        $branch_condition = '';
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->UserCredentials->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND `emp_device_comp_branch`.`branch_code` = '$is_ho' ";
            }
        }
        // End

         $emp_devices = $this->Device->query("
    SELECT 
        emp_device_comp_branch_seq,
        emp_device_comp_branch.deviceid,
        emp_device_comp_branch.emp_device_id,
        emp_device_comp_branch.emp_fkey,
        emp_device_comp_branch.emp_username,
        emp_device_comp_branch.emp_name,
        devices.DeviceFName,
        devices.SerialNumber
    FROM mypayrol_control_db.emp_device_comp_branch
    LEFT JOIN mypayrol_control_db.devices 
        ON devices.DeviceId = emp_device_comp_branch.deviceid 
        AND devices.company_code = '$company_code'
    WHERE emp_device_comp_branch.Company_code = '$company_code'
        $condition 
        AND emp_device_comp_branch.status = '1'
        $branch_condition
    GROUP BY emp_device_comp_branch.emp_fkey
    ORDER BY $sort $order
    LIMIT $limit OFFSET $ofst
");

$emp_devices_count = $this->Device->query("
    SELECT COUNT(DISTINCT emp_fkey) AS total
    FROM mypayrol_control_db.emp_device_comp_branch
    LEFT JOIN mypayrol_control_db.devices 
        ON devices.DeviceId = emp_device_comp_branch.deviceid 
        AND devices.company_code = '$company_code'
    WHERE emp_device_comp_branch.Company_code = '$company_code'
        $condition
        AND emp_device_comp_branch.status = '1'
        $branch_condition
");

        // echo "<pre>";print_r($emp_devices_count);echo "</pre>";
        foreach ($emp_devices as $data) {
            $result[$i]['deviceid'] = $data['emp_device_comp_branch']['deviceid'];
            $result[$i]['emp_device_id'] = $data['emp_device_comp_branch']['emp_device_id'];
            $result[$i]['emp_fkey'] = $data['emp_device_comp_branch']['emp_fkey'];
            $result[$i]['emp_username'] = $data['emp_device_comp_branch']['emp_username'];
            $result[$i]['emp_name'] = $data['emp_device_comp_branch']['emp_name'];
            $result[$i]['emp_device_comp_branch_seq'] = $data['emp_device_comp_branch']['emp_device_comp_branch_seq'];
            $result[$i]['DeviceFName'] = $data['devices']['DeviceFName'];
            $result[$i]['SerialNumber'] = $data['devices']['SerialNumber'];
            // $result[$i]['branch_code'] = $data['edcb']['branch_code'];
            $i++;
        }
        $json_output['rows'] = $result;
        $json_output['total'] = $emp_devices_count[0][0]['total'];
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        echo json_encode($json_output);
    }
    
    public function downloadempuploadform()
    {

        $this->autoRender = FALSE;

        $str_company_code = $this->Session->read('company_code');
        $file_name = isset($str_company_code) ? strtolower($str_company_code) . "_empdevices.xlsx" : "empdevices_" . strtotime() . ".xlsx";

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
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(27);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(21);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(26);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        //$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);


        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Variable");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setCellValueByColumnAndRow(0, 1, "Employee ID");
        $worksheet->setCellValueByColumnAndRow(1, 1, "Employee Name");
        $worksheet->setCellValueByColumnAndRow(2, 1, "Device Id");
        $worksheet->setCellValueByColumnAndRow(3, 1, "Device Name");
        $worksheet->setCellValueByColumnAndRow(4, 1, "Serial Number");
        $worksheet->setCellValueByColumnAndRow(5, 1, "Employee Device Id");

        // Edited by Akshay on 7-2-2025
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $is_ho = 1;
        $branch_condition = '';
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->UserCredentials->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND `emp_device_comp_branch`.`branch_code` = '$is_ho' ";
            }
        }

        $emp_devices = $this->Device->query("SELECT `emp_device_comp_branch_seq`, `emp_device_comp_branch`.`deviceid`,`DeviceFName`,`SerialNumber`, "
            . "`emp_device_id`, `emp_name`, `emp_id`, `emp_username`,`emp_fkey` FROM mypayrol_control_db.emp_device_comp_branch "
            . "LEFT JOIN mypayrol_control_db.devices ON devices.DeviceId = emp_device_comp_branch.deviceid AND devices.company_code = '$str_company_code' "
            . "WHERE `emp_device_comp_branch`.`Company_code` = '$str_company_code' AND `emp_device_comp_branch`.`status` = '1' $branch_condition"
            . "ORDER BY creation_date DESC ");
        // End


        $rowindex = 2;
        $columnindex = 0;
        foreach ($emp_devices as $data) {
            $deviceid = $data['emp_device_comp_branch']['deviceid'];
            $emp_device_id = $data['emp_device_comp_branch']['emp_device_id'];
            //$emp_fkey = $data['emp_device_comp_branch']['emp_fkey'];
            $emp_username = $data['emp_device_comp_branch']['emp_username'];
            $emp_name = $data['emp_device_comp_branch']['emp_name'];
            //$emp_device_comp_branch_seq = $data['emp_device_comp_branch']['emp_device_comp_branch_seq'];
            $DeviceName = $data['devices']['DeviceFName'];
            $SerialNumber = $data['devices']['SerialNumber'];


            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $emp_username);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowindex, $emp_name);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowindex, $deviceid);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowindex, $DeviceName);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowindex, $SerialNumber);
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowindex, $emp_device_id);
            // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $rowindex++;
        }


        $objPHPExcel->getActiveSheet()->setTitle("Employee Devices");

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
    public function editEmpDev($emp_dev_seq)
    {
        $this->autoRender = true;
        $company_code = $this->Session->read('company_code');

        $devices = $this->Device->query("SELECT `DeviceId`, `DeviceFName`, `SerialNumber` FROM `mypayrol_control_db`.`devices` WHERE `company_code` = '".$company_code."'");
        $this->set('devices',$devices);

        if ($emp_dev_seq) {
            $emp_dev_data = $this->Device->query("SELECT * FROM mypayrol_control_db.emp_device_comp_branch LEFT JOIN mypayrol_control_db.devices ON devices.DeviceId = emp_device_comp_branch.deviceid WHERE `emp_device_comp_branch_seq` = $emp_dev_seq AND `emp_device_comp_branch`.`Company_code` = '".$company_code."' AND `emp_device_comp_branch`.`status` = '1' ");

            $this->set('emp_dev_data', array_merge($emp_dev_data[0]['emp_device_comp_branch'],$emp_dev_data[0]['devices']));
            $this->set('emp_dev_seq', $emp_dev_seq);
        }else{
            exit();
        }
        
        // echo "<pre>";                        
                                                                                                        
                  
        
                        
        // print_r($emp_dev_data[0]['emp_device_comp_branch']);
        // echo "</pre>";
    }

    // public function saveEmpDev()
    // {
    //     $this->autoRender = false;
    //     // echo "<pre>";
    //     // print_r($_REQUEST);
    //     // echo "</pre>";
    //     $emp_device_comp_branch_seq = ($_REQUEST['emp_device_comp_branch_seq']) ? $_REQUEST['emp_device_comp_branch_seq'] : '';
    //     $deviceid = ($_REQUEST['deviceid']) ? $_REQUEST['deviceid'] : '';
    //     $emp_device_id = ($_REQUEST['emp_device_id']) ? $_REQUEST['emp_device_id'] : 0;
    //     $login_user = $this->Session->read('login_user_id');
    //     $company_code = $this->Session->read('company_code');
    //     $date = date("Y-m-d H:i:s");
    //     if ($emp_device_comp_branch_seq && $deviceid) {
            
    //         $this->Device->query("UPDATE mypayrol_control_db.emp_device_comp_branch SET deviceid = " . $deviceid . ", emp_device_id = '".$emp_device_id."',modified_by ='".$login_user."',modified_date = '".$date."' WHERE `emp_device_comp_branch_seq` = $emp_device_comp_branch_seq AND Company_code = '".$company_code."'");
            
    //         echo true;
    //     } else {
    //         echo false;
    //     }
    // }
      public function saveEmpDev()
{
    $this->autoRender = false;

    $emp_device_comp_branch_seq = isset($_REQUEST['emp_device_comp_branch_seq'])
        ? (int)$_REQUEST['emp_device_comp_branch_seq'] : 0;

    $deviceid = isset($_REQUEST['deviceid'])
        ? (int)$_REQUEST['deviceid'] : 0;

    $emp_device_id = isset($_REQUEST['emp_device_id'])
        ? (int)$_REQUEST['emp_device_id'] : 0;

    $login_user = $this->Session->read('login_user_id');
    $company_code = $this->Session->read('company_code');
    $date = date("Y-m-d H:i:s");

    // Allow 0 as a valid value
    if ($emp_device_comp_branch_seq > 0) {

        $sql = "
            UPDATE mypayrol_control_db.emp_device_comp_branch
            SET deviceid = ?,
                emp_device_id = ?,
                modified_by = ?,
                modified_date = ?
            WHERE emp_device_comp_branch_seq = ?
            AND Company_code = ?
        ";

        $result = $this->Device->query($sql, [
            $deviceid,
            $emp_device_id,
            $login_user,
            $date,
            $emp_device_comp_branch_seq,
            $company_code
        ]);

        echo $result ? true : false;
    } else {
        echo false;
    }
}
}
