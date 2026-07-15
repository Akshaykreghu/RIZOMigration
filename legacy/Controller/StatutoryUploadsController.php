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
ini_set('max_execution_time', 300);

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class StatutoryUploadsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'StatutoryUploads';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EditPunches', 'Attendance', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'DbConfig', 'MobileUserauditor', 'EmployeeLoan',
        'Taxsalarycomponents', 'EmpCtcTransaction'); //santhu
    public $components = array('MasterdataManagement');
    public function index() {
        
    }
    
    public function statutory(){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

        //debug($array_key);
        $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = $this->EmpCtcTransaction->query("select * from emp_details where esi != '' ");
        $id = implode(' AND ', $conditions);
            $k = 0;
        //$gross = array();
        //debug($id);
        foreach ($arr_leavepolicygroupids as $val) {
            $leavepolicygroupid = $val['emp_details']['emp_pkey'];
            $arr_emp_info = $this->EmpCtcTransaction->query(" select * from employee_info where emp_pkey =  '$leavepolicygroupid' ");
            $arr_gross = $this->EmpCtcTransaction->query("select ectc.* from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' 
AND ectc.end_date_effective is null AND  $id and head_operator = 'Deduction' ");
            $Employer_Epf = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = '10' ");
            $Employer_Esi = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = '12' ");
            $Employer_wwf = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = 14+1 ");
            $epf = $Employer_Epf['0']['tax_salary_components']['salary_head_item_Fkey'];
            $esi = $Employer_Esi['0']['tax_salary_components']['salary_head_item_Fkey'];
            $wwf = $Employer_wwf['0']['tax_salary_components']['salary_head_item_Fkey'];
            $emp_epf = $this->EmpCtcTransaction->query("select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' 
AND ectc.end_date_effective is null AND  $id and ectc.salary_head_item_fkey = '$epf' ");
            $emp_esi = $this->EmpCtcTransaction->query("select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' 
AND ectc.end_date_effective is null AND  $id and ectc.salary_head_item_fkey = '$esi' ");
            $emp_wwf = $this->EmpCtcTransaction->query("select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' 
AND ectc.end_date_effective is null AND  $id and ectc.salary_head_item_fkey = '$wwf' ");
            $salary = $this->EmpCtcTransaction->query("select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct'
AND ectc.end_date_effective is null and head_operator = 'Addition' AND $id ");
               $epf_salary = isset($emp_epf['0']['ectc']['salary_amount'])?$emp_epf['0']['ectc']['salary_amount']:0;
            $esi_salary = isset($emp_esi['0']['ectc']['salary_amount'])?$emp_esi['0']['ectc']['salary_amount']:0;
            $www_salary = isset($emp_wwf['0']['ectc']['salary_amount'])?$emp_wwf['0']['ectc']['salary_amount']:0;
            $gross = isset($salary['0']['0']['sum_amount'])?$salary['0']['0']['sum_amount']:0;
            $LOP = $this->EmpCtcTransaction->query("select loss_of_pay,working_days from payroll_master where emp_fkey = '$leavepolicygroupid' AND month_year = '$from' ");
            $lops = isset($LOP['0']['payroll_master']['loss_of_pay'])?$LOP['0']['payroll_master']['loss_of_pay']:0;
            $salary_UAN = $this->EmpCtcTransaction->query("select pf from emp_details where emp_pkey = '$leavepolicygroupid' ");
            $salary_esi = $this->EmpCtcTransaction->query("select * from emp_details where emp_pkey = '$leavepolicygroupid' and attr4 = 'Y' and esi != '' ");
            $termination_details = $this->EmpCtcTransaction->query("select * from termination where emp_fkey = '$leavepolicygroupid' ");
            $lworkingdate = $this->EmpCtcTransaction->query("select max(LOGDATE) as dates from device_attandance left join emp_details on (emp_details.emp_id = device_attandance.emp_id) where emp_details.emp_pkey = '$leavepolicygroupid' ");
            $resignation_date = '';
            $reason = '';
            $reason_desc = 1;
            $statu = isset($salary_esi['0']['emp_details']['status'])?$salary_esi['0']['emp_details']['status']:'0';
            if(count($salary_esi) > 0){
                $statuss = isset($salary_esi['0']['emp_details']['status'])?$salary_esi['0']['emp_details']['status']:0;
                if($statuss == 2){
                    $resignation_date = isset($termination_details['0']['termination']['act_last_working_day'])?$termination_details['0']['termination']['act_last_working_day']:'';
                    $reason = isset($termination_details['0']['termination']['Reason'])?$termination_details['0']['termination']['Reason']:'';
                    
                    switch ($reason){
                        case 'Resigned':
                            $reason_desc = 2;
                            break;
                        case 'Retrenchment':
                            $reason_desc = 10;
                            break;
                        case 'Retirement':
                            $reason_desc = 3;
                            break;
                        default :
                            $reason_desc = 1;
                            break;
                    }
                }
            }
            $UAN = isset($salary_UAN['0']['emp_details']['pf'])?$salary_UAN['0']['emp_details']['pf']:0;
            $wd = isset($LOP['0']['payroll_master']['working_days'])?$LOP['0']['payroll_master']['working_days']:0;
            $esi_number = isset($salary_esi['0']['emp_details']['esi'])?$salary_esi['0']['emp_details']['esi']:0;
            $last_working_date = isset($lworkingdate['0']['device_attandance']['dates'])?$lworkingdate['0']['device_attandance']['dates']:0;
            $arr_salary_for_template[] = array(
                'data'=>$arr_emp_info,
                'gros'=>$gross,
                'UAN' => $UAN,
                'wwf'=>$www_salary,
                'epf'=>$epf_salary,
                'esi'=>$esi_salary,
                'lop' =>$lops,
                'desc_reason' =>$reason_desc,
                'date_resignatio' =>$resignation_date,
                'working_date' =>$last_working_date,
                'wday' =>$wd-$lops,
                'status' => $statu,
                'termination' =>$termination_details,
                'esi_number' => $esi_number,
                
                );
            $k++;
        }     
        if(isset($arr_salary_for_template)){  
                $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                $user_name = $this->Session->read('user_name');
                $this->set('user_name', $user_name);
                $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
                $this->set('arr_comp_contact_info', $arr_comp_contact_info);
                $this->set('month', $from);
                $str_company_code = $this->Session->read('company_code');
                $file_name = 'Statutony.xls';
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                //$worksheet->setCellValueByColumnAndRow(0, 1, "Statutony Reports -Month:" . $from);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                for ($col = 'A'; $col !== 'M'; $col++) {
                    $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                }
                $rowcount = 1;

                $i = 0;


                $col = 0;
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'IP Number');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'IP Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, '  No of Days for which wages paid/payable during the month  ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, ' Total Monthly Wages ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, ' Reason Code for Zero workings days(numeric only; provide 0 for all other reasons- Click on the link for reference)');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);
                //$objPHPExcel->getActiveSheet()->getStyle($col + 4)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, '   Last Working Day ');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                


                $col = 5;


                $j = 1;

                $rowcount = $rowcount + 1;
                foreach ($arr_salary_for_template as $val) {
                    
                    
                    $col = 0;
                    $esi = $val['esi_number'];
                    $name = $val['data']['0']['employee_info']['EmpName'];
                    $daysss = $val['wday'];
                    $wages_tota = $val['gros'];
                    $col1 = '';
                    $col2 ='';
                    if($daysss == 0){
                        $col2 = $val['date_resignatio'];
                        $col1 = $val['desc_reason'];
                        if($col1 == 1){
                            $col2 = $val['working_date'];
                        }
                    }
                    

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $esi);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col , $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $name);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $daysss);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $wages_tota);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $col1);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, $col2);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    




                    $j++;
                    $rowcount++;
                    }
                
                $objPHPExcel->getActiveSheet()->setTitle('SHEET1');
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
            else{

            }
    }
    public function pf(){
        
    }

    public function pfdownload($from = ''){
        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');

        //debug($array_key);
        //$from = date('Y-m', strtotime($arr_form_data['reportfrom']));
        //$otdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $conditions = array();
        $conditions[] = 'ectc.month_year="' . $from . '"';
        $arr_leavepolicygroupids = $this->EmpCtcTransaction->query("select * from emp_details where status = '1' ");
        $id = implode(' AND ', $conditions);
            $k = 0;
        //$gross = array();
        //debug($id);
        foreach ($arr_leavepolicygroupids as $val) {
            $leavepolicygroupid = $val['emp_details']['emp_pkey'];
            $arr_emp_info = $this->EmpCtcTransaction->query(" select * from employee_info where emp_pkey =  '$leavepolicygroupid' ");
            $arr_gross = $this->EmpCtcTransaction->query("select ectc.* from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' 
AND ectc.end_date_effective is null AND  $id and head_operator = 'Deduction' ");
            $Employer_Epf = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = '10' ");
            $Employer_Esi = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = '12' ");
            $Employer_wwf = $this->EmpCtcTransaction->query("select salary_head_item_Fkey from tax_salary_components where tax_salary_components_pkey = 14+1 ");
            $epf = $Employer_Epf['0']['tax_salary_components']['salary_head_item_Fkey'];
            $esi = $Employer_Esi['0']['tax_salary_components']['salary_head_item_Fkey'];
            $wwf = $Employer_wwf['0']['tax_salary_components']['salary_head_item_Fkey'];
            $emp_epf = $this->EmpCtcTransaction->query("select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' 
AND ectc.end_date_effective is null AND  $id and ectc.salary_head_item_fkey = '$epf' ");
            $emp_esi = $this->EmpCtcTransaction->query("select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' 
AND ectc.end_date_effective is null AND  $id and ectc.salary_head_item_fkey = '$esi' ");
            $emp_wwf = $this->EmpCtcTransaction->query("select ectc.salary_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct' 
AND ectc.end_date_effective is null AND  $id and ectc.salary_head_item_fkey = '$wwf' ");
            $salary = $this->EmpCtcTransaction->query("select sum(ectc.salary_amount) as sum_amount from emp_salary_slip as ectc where ectc.emp_fkey= '$leavepolicygroupid' AND ectc.item_part = 'Direct'
AND ectc.end_date_effective is null and head_operator = 'Addition' AND $id ");
               $epf_salary = isset($emp_epf['0']['ectc']['salary_amount'])?$emp_epf['0']['ectc']['salary_amount']:0;
            $esi_salary = isset($emp_esi['0']['ectc']['salary_amount'])?$emp_esi['0']['ectc']['salary_amount']:0;
            $www_salary = isset($emp_wwf['0']['ectc']['salary_amount'])?$emp_wwf['0']['ectc']['salary_amount']:0;
            $gross = isset($salary['0']['0']['sum_amount'])?$salary['0']['0']['sum_amount']:0;
            $LOP = $this->EmpCtcTransaction->query("select loss_of_pay,working_days from payroll_master where emp_fkey = '$leavepolicygroupid' AND month_year = '$from' ");
            $lops = isset($LOP['0']['payroll_master']['loss_of_pay'])?$LOP['0']['payroll_master']['loss_of_pay']:0;
            $salary_UAN = $this->EmpCtcTransaction->query("select pf from emp_details where emp_pkey = '$leavepolicygroupid' ");
            $salary_esi = $this->EmpCtcTransaction->query("select * from emp_details where emp_pkey = '$leavepolicygroupid' and attr4 = 'Y' and esi != '' ");
            $termination_details = $this->EmpCtcTransaction->query("select * from termination where emp_fkey = '$leavepolicygroupid' ");
            $lworkingdate = $this->EmpCtcTransaction->query("select max(LOGDATE) as dates from device_attandance left join emp_details on (emp_details.emp_id = device_attandance.emp_id) where emp_details.emp_pkey = '$leavepolicygroupid' ");
            $resignation_date = '';
            $reason = '';
            $reason_desc = 1;
            $statu = isset($salary_esi['0']['emp_details']['status'])?$salary_esi['0']['emp_details']['status']:'0';
            
            $UAN = isset($salary_UAN['0']['emp_details']['pf'])?$salary_UAN['0']['emp_details']['pf']:0;
            $wd = isset($LOP['0']['payroll_master']['working_days'])?$LOP['0']['payroll_master']['working_days']:0;
            $esi_number = isset($salary_esi['0']['emp_details']['esi'])?$salary_esi['0']['emp_details']['esi']:0;
            $last_working_date = isset($lworkingdate['0']['device_attandance']['dates'])?$lworkingdate['0']['device_attandance']['dates']:0;
            $arr_salary_for_template[] = array(
                'data'=>$arr_emp_info,
                'gros'=>$gross,
                'UAN' => $UAN,
                'wwf'=>$www_salary,
                'epf'=>$epf_salary,
                'esi'=>$esi_salary,
                'lop' =>$lops,
                'desc_reason' =>$reason_desc,
                'date_resignatio' =>$resignation_date,
                'working_date' =>$last_working_date,
                'wday' =>$wd-$lops,
                'status' => $statu,
                'termination' =>$termination_details,
                'esi_number' => $esi_number,
                
                );
            $k++;
        } 
        $this->set('arr_salary_for_template',$arr_salary_for_template);
    }
}