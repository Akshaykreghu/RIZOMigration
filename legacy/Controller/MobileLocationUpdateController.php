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

class MobileLocationUpdateController extends AppController {

    public $helpers = array('GoogleMap');
    public $uses = array('UserCredentials', 'EmployeeDetails','MobileUserTracking', 'Units', 'MobileUserauditor', 'CompanyContactInfo', 'DbConfig','ReportAudit');
    public $components = array('DatatablesManagement');

    public function index()
    {

        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1))));
        //        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
        //Employee Company ID added by ***ARUL P DAS on 20/12/2019
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $conditions = "";
        $user_group = $this->Session->read('user_group');

        // Edited by Akshay on 12-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');

        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND emp_details.branch_code = '$is_ho' ";
                $conditions = " and emp_details.branch_code ='" . $is_ho . "'";
            }
        }else
        // End

        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions = " and emp_details.branch_code ='" . $cur_emp_branch . "'";
        }
        //employee branch wise sorting ends here

        $arr_employees = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey ' . $conditions . ' and emp_details.status=1 order by first_name ASC');
        $this->set("arr_employees", $arr_employees);
    }

    public function loadmap() {
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $emp_fkey = isset($arr_form_data['emp_key']) ? $arr_form_data['emp_key'] : 0;
        $from_dates = isset($arr_form_data['from_dates']) ? $arr_form_data['from_dates']. ' 00:00' : '';
        $to_dates = isset($arr_form_data['to_dates']) ? $arr_form_data['to_dates']. ' 23:59' : '';
        if ($emp_fkey == 0) {
            echo "NO Emp";
            return false;
        }
        $get_user_id = $this->MobileUserTracking->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';

        if ($user_id == '') {
            echo "NO user_id";
            return FALSE;
        }
        $arr_location = $this->MobileUserTracking->query("SELECT * FROM `mob_user_tracking` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $location = array();
        foreach ($arr_location as $key => $val) {

            $location[] = array($val['mob_user_tracking']['location'], $val['mob_user_tracking']['latitude'], $val['mob_user_tracking']['longitude']);
        }
        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");

        $this->set("arr_location", $arr_location_updates);

        $this->set("datas", json_encode($location));
		$this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $dataForHistory = array();
        $dataForHistory['report_type'] = "Mobile Tracking Report";
        $dataForHistory['mode'] = "View Report";
        $dataForHistory['criteria_name'] = "belonging to an Employee";
        $dataForHistory['items'] = $emp_fkey;
        $dataForHistory['items_count'] = 1;
        $dataForHistory['report_from'] = $from_dates;
        $dataForHistory['report_to'] = $to_dates;
        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
        
        $this->ReportAudit->save($dataForHistory);
    }

    public function employeelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $_REQUEST;
        $condition = "ed.status='1'";
        if (isset($arr_request_data['branch']) && !empty($arr_request_data['branch'])) {
            $branch = $arr_request_data['branch'];

            $condition .=" AND ep.emp_branch='$branch'";
        }
        if (isset($arr_request_data['depart']) && !empty($arr_request_data['depart'])) {
            $depart = $arr_request_data['depart'];

            $condition .=" AND ep.emp_dept='$depart'";
        }
        if (isset($arr_request_data['designation']) && !empty($arr_request_data['designation'])) {
            $designation = $arr_request_data['designation'];

            $condition .=" AND ep.designation='$designation'";
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_employees = $this->EmployeeDetails->query("Select ed.emp_pkey,ep.designation,ei.*
from emp_details as ed
left join emp_proff as ep on (ed.emp_pkey=ep.emp_fkey)
 left join employee_info as ei on (ep.emp_fkey=ei.emp_pkey)
           where $condition ");
        $data = array();
        $data['rows'] = $arr_employees;
        //debug($arr_employees);
        echo json_encode($data);
    }
    
    public function downloadexcel($emp = 0,$start_date = '',$end_date = ''){
        $arr_request_data = $_REQUEST;
        // debug($arr_request_data);
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');

        $from_dates = $start_date; 
        $to_dates = $end_date; 
        $emp_fkey = $emp;
		$this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $dataForHistory = array();
        $dataForHistory['report_type'] = "Mobile Tracking Report";
        $dataForHistory['mode'] = "Excel Download";
        $dataForHistory['criteria_name'] = "belonging to an Employee";
        $dataForHistory['items'] = $emp_fkey;
        $dataForHistory['items_count'] = 1;
        $dataForHistory['report_from'] = $from_dates;
        $dataForHistory['report_to'] = $to_dates;
        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
        
        $this->ReportAudit->save($dataForHistory);	 
//        
        $get_user_id = $this->MobileUserTracking->query("SELECT user_id,first_name,last_name from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';
        $empname = isset($get_user_id['0']['user_credentials']['first_name']) ? $get_user_id['0']['user_credentials']['first_name'].' '.$get_user_id['0']['user_credentials']['last_name'] : '';
        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
//        
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('from_dates', $from_dates);
        $this->set('to_dates', $to_dates);
        $this->set("arr_location", $arr_location_updates);
        
        $this->autoRender = FALSE;

        $str_company_code = $this->Session->read('company_code');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
                $file_name = isset($str_company_code) ? $str_company_code . "Location_Updates.xlsx" : "Location" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Location Updates Report");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('A2:F2');
                $rowcount = 2;
                $i = 0;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Mobile Tracking Reports of '.$empname);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                $rowcount = 3;

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Employee ID');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee Name');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Department');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Branch');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Date & Time');

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Location');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Customer');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Purpose');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'IN/OUT');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                for ($i = 0; $i <= 9; $i++) {
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                }


                $rowcount = 4;
                  // debug($arr_data);die();
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);

                $rowcount1 = $rowcount + 1;
                 if (count($arr_location_updates) >= 0) {
                     $k = 1;
                foreach ($arr_location_updates as $val){
                    $columnindex = 0;
                    $rowcount++;
                            $employee_id = $val['employee_info']['employee_id'];
                            $name = $val['employee_info']['EmpName'];
                            $department = $val['employee_info']['department'];
                            $branch = $val['employee_info']['branch'];
                            $dates = $val['mob_user_tracking']['created_time'];
                            $location = $val['mob_user_tracking']['location'];
//                            $customer = $val['mob_user_tracking']['customer_name'];
//                            $purpose = $val['mob_user_tracking']['purpose'];
//                            $in_out = ($val['mob_user_tracking']['stepinout'] == 'step_in')?"IN":"OUT";
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $employee_id);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $department);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $branch);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $dates);
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $location);
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $customer);
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $purpose);
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $in_out);
                            $k++;
                }
                }
                $objPHPExcel->getActiveSheet()->setTitle('Location Updates Report');
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenHeader($arr_comp_contact_info['CompanyContactInfo']['business_name']);
                /* header footer */

                /* print Set up */
                $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                /* print Set up */
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
        
    }

    public function downloadpdf($mode) {
        //$this->autoRender = FALSE;
        $arr_request_data = $_REQUEST;
        // debug($arr_request_data);
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');

        $from_dates = $arr_request_data['from_dates']; 
        $to_dates = $arr_request_data['to_dates']; 
        $emp_fkey = $arr_request_data['emp_fkey'];
        
        $get_user_id = $this->MobileUserTracking->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';
        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` left join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('from_dates', $from_dates);
        $this->set('to_dates', $to_dates);
        $this->set("arr_location", $arr_location_updates);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        if (isset($arr_mob_location) && !empty($arr_mob_location)) {
            //    ob_clean(); 
            $this->set('arr_mob_location', $arr_mob_location);
            $this->set('mode', 'pdf');
            $view = new View($this, false);
            $view_output = $view->render('downloadpdf');

            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

            $html2pdf = new HTML2PDF('P', 'A3', 'en');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($view_output);
            $html2pdf->Output('Mobilelocation.pdf', 'D');
            // $this->render('downloadpdf');
            // ob_end_clean();
        }
    }

}
