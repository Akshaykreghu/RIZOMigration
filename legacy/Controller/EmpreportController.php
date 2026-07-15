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
class EmpreportController extends AppController {

    public $uses = array('DayTimeProcedures', 'DbConfig', 'EmpCtcTransaction', 'FinancialYear', 
        'EmployeeProfessionalDetails', 'Salarystructure', 'DeviceAttendance', 'SalaryStructures', 
        'SalaryHeadItems', 'EmployeeDetails', 'Units', 'AttendanceRegister', 'LeavePolicy', 'Holiday',
        'EmployeeLeaveTransaction','MobileUserauditor','CompanyContactInfo','MobileUserTracking');
    public $name = 'Empreport';

    public function index() {
        
    }

    public function salarystructure() {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
      $sessionid = $this->Session->read('emp_fkey'); 
//  $arr_empleaverequests = $this->EmpCtcTransaction->query("select desg.desig_name,dpt.dept_name,br.branch_name,ed.first_name,ed.last_name,ectc.* from emp_salary_structure as ectc "
//          . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) left join branches as br on (br.branch_code = ep.emp_branch)"
//            ."left join department as dpt on (dpt.dept_code = ep.emp_dept)"
//               ."left join designation as desg on (desg.desig_code = ep.designation)
//                 where ectc.head_operator = 'ADDITION' and ectc.head_type = 'FIXED' and ectc.emp_fkey = '$sessionid' and end_date_effective is null ");  

  $arr_empleaverequests = $this->EmpCtcTransaction->query("select desg.desig_name,dpt.dept_name,br.branch_name,ed.first_name,
      ed.middile_name,ed.last_name,ectc.emp_fkey,ectc.salary_head_item_fkey,ectc.salary_head_item_desc,ectc.structure_det_value,ectc.head_operator,ectc.head_type,ectc.item_part,ep.emp_company_id from emp_salary_structure as ectc left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
      left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) left join branches as br on (br.branch_code = ep.emp_branch)
      left join department as dpt on (dpt.dept_code = ep.emp_dept) left join designation as desg on (desg.desig_code = ep.designation)
      where ectc.head_operator = 'ADDITION'  and lcase(head_type) not in ('manually','variable') and ectc.emp_fkey = '$sessionid' and ed.status = 1 
      and salary_head_item_fkey in(select salary_head_item_pkey from salary_head_items where head_fkey in (1,4,10))
      and end_date_effective is null union select desg.desig_name,dpt.dept_name,br.branch_name,ed.first_name,ed.middile_name,ed.last_name,
ectc.emp_fkey,ectc.salary_head_item_fkey,ectc.salary_head_item_desc,ectc.structure_det_value,ectc.head_operator,ectc.head_type,ectc.item_part,ep.emp_company_id from emp_variable_pay_upload as ectc
left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
 left join branches as br on (br.branch_code = ep.emp_branch)
left join department as dpt on (dpt.dept_code = ep.emp_dept)
left join designation as desg on (desg.desig_code = ep.designation) where ectc.emp_fkey = '$sessionid'  and ed.status = 1 ");
																																																							
//   debug($arr_empleaverequests);

            $arr_salary_for_template[] = array(
                //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                'summary' => $arr_empleaverequests,
                    // 'employees'=>$arr_leavepolicy_employees
            );
     
     $this->set('arr_salary_for_template', $arr_salary_for_template);


    }

    public function attendancedetails() {
        
    }
    public function refresh($month = 0) {
        $this->autoRender = false;
        $emp_pkey = $this->Session->read('emp_fkey');
        $month = date('Y-m-d', strtotime($month));
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $current_date =  date('Y-m-d');
        $att_startdate = $this->EmpCtcTransaction->query("select att_start_end_fn(DATE_FORMAT('$month', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->EmpCtcTransaction->query("select att_start_end_fn(DATE_FORMAT('$month', '%Y-%m-01'), 2) as monthly_att_todate");  
        $att_startdate1 = date("Y-m-d",strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("Y-m-d",strtotime($att_enddate['0']['0']['monthly_att_todate']));
        $current_date =  date('Y-m-d');
        $month = date('Y-m', strtotime($att_enddate1)).'-01';
        $arr_empdata = $this->EmpCtcTransaction->query("select branch_code from employee_info where employee_info.emp_pkey = $emp_pkey");
        $branch_code = isset($arr_empdata['0']['employee_info']['branch_code'])?$arr_empdata['0']['employee_info']['branch_code']:'';
        //edited by athira on 04-03-2026
        $att_start_date = $this->EmployeeDetails->query("SELECT att_start_end_fn('$month', 1) AS start_date");
    $att_end_date = $this->EmployeeDetails->query("SELECT att_start_end_fn('$month', 2) AS end_date");
    $attendance_start = $att_start_date[0][0]['start_date'];
    $attendance_end = $att_end_date[0][0]['end_date'];
    //end
        $company_code=$this->Session->read('company_code');
           $restrictedCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
];
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
		if (in_array($company_code, $restrictedCompanies)) {
        $deleterecords = $this->EmpCtcTransaction->query("delete from  emp_detail_timeattandance where emp_pkey = $emp_pkey and yearmonth='$month' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m')) ");
        
        $shiftdetailed = $this->EmpCtcTransaction->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )");
        $shift = isset($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'])?$shiftdetailed['0']['working_day_time_procedures']['is_multiple_days']:'';
        if ($shift == 'Y') {
                $this->EmpCtcTransaction->query("SELECT time_duration_check_multishift('$month', '$emp_pkey', '$branch_code')");
            } else {
                $this->EmpCtcTransaction->query("SELECT time_duration_check('$month', '$emp_pkey', '$branch_code')");
            }
    }

   else {

    // Step 1: Fetch emp_id from emp_details using emp_pkey
    $empData = $this->EmployeeDetails->query("
        SELECT emp_id 
        FROM emp_details 
        WHERE emp_pkey = '$emp_pkey'
        LIMIT 1
    ");

    if (empty($empData)) {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
        return;
    }

    $emp_id = $empData[0]['emp_details']['emp_id'];

    // Step 2: Fetch attendance dates using emp_id
    $dates = $this->EmployeeDetails->query("
        SELECT DISTINCT SHIFTDATE
        FROM device_attandance
        WHERE emp_id = '$emp_id'
        AND SHIFTDATE BETWEEN '$attendance_start' AND '$attendance_end'
        AND status = 'Y'
        ORDER BY SHIFTDATE
    ");

    if (empty($dates)) {
        echo json_encode(['success' => false, 'message' => 'No attendance found for this period']);
        return;
    }

      foreach ($dates as $row) {
                $shift_date = $row['device_attandance']['SHIFTDATE'];

                 $this->EmployeeDetails->query("
                                                    SELECT time_duration_check('$shift_date', '$emp_pkey', '$branch_code')
                                                ");
            }
    

}
//        if($current_date >= $att_startdate1 && $current_date <= $att_enddate1){
//            $month = $month;
//        }else{ 
            $month1 = date('Y-m', strtotime($month.' + 1 months')).'-01';
            $month2 = date('Y-m', strtotime($month1.' + 1 months')).'-01';
       // }
   
       $restrictedCompanies = [
    'ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK',
    'GTRA','VGNN','SHYD','SRTS'
];
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
		if (in_array($company_code, $restrictedCompanies)) {
        $deleterecords = $this->EmpCtcTransaction->query("delete from  emp_detail_timeattandance where emp_pkey = $emp_pkey and yearmonth='$month1' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m')) ");
        $deleterecords = $this->EmpCtcTransaction->query("delete from  emp_detail_timeattandance where emp_pkey = $emp_pkey and yearmonth='$month2' and emp_pkey  not in (select emp_fkey from attendance_register where isdelete='N' and month_year = DATE_FORMAT('$month','%Y-%m')) ");
       
        $shiftdetailed = $this->EmpCtcTransaction->query("select is_multiple_days from working_day_time_procedures where  day_time_seq in (select day_time_seq from emp_proff where emp_fkey = '$emp_pkey' )");
        $shift = isset($shiftdetailed['0']['working_day_time_procedures']['is_multiple_days'])?$shiftdetailed['0']['working_day_time_procedures']['is_multiple_days']:'';
        if ($shift == 'Y') {
                $this->EmpCtcTransaction->query("SELECT time_duration_check_multishift('$month1', '$emp_pkey', '$branch_code')");
                $this->EmpCtcTransaction->query("SELECT time_duration_check_multishift('$month2', '$emp_pkey', '$branch_code')");
            
            } else {
                $this->EmpCtcTransaction->query("SELECT time_duration_check('$month1', '$emp_pkey', '$branch_code')");
                $this->EmpCtcTransaction->query("SELECT time_duration_check('$month2', '$emp_pkey', '$branch_code')");
            }
         }
    
    }
    public function downloads(){
        $this->autoRender = false;
         $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $sessionid = $this->Session->read('emp_fkey');
        $arr_empleaverequests = $this->EmpCtcTransaction->query("select desg.desig_name,dpt.dept_name,br.branch_name,ed.first_name,ed.last_name,ectc.*,ep.emp_company_id 
            from emp_salary_structure as ectc 
            left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
             left join branches as br on (br.branch_code = ep.emp_branch)
            left join department as dpt on (dpt.dept_code = ep.emp_dept)
            left join designation as desg on (desg.desig_code = ep.designation)
                                             where ectc.head_operator = 'ADDITION'  and lcase(head_type) not in ('manually','variable') and ectc.emp_fkey = '$sessionid' and ed.status = 1 and end_date_effective is null");
        
        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
                // 'employees'=>$arr_leavepolicy_employees
        );
        
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info',$arr_comp_contact_info);
        //$content ="<h2>hi</h2>";
            $view = new View($this, false);
            $view_output = $view->render('download');
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
                try
                    {
                        $html2pdf = new HTML2PDF('P', 'Legal', 'en');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                        //$html2pdf->writeHTML($content);
                        $html2pdf->writeHTML($view_output);
                        $html2pdf->Output('SalaryReport.pdf', 'D');
                        $this->render('download');
                    }

                    catch(HTML2PDF_exception $e) {
                        echo $e;
                        exit;
                    }
    }
    
     public function downloadexcels($loan_pkey = 0){
        $this->autoRender = false;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $sessionid = $this->Session->read('emp_fkey');
        $arr_empleaverequests = $this->EmpCtcTransaction->query("select desg.desig_name,dpt.dept_name,br.branch_name,ed.first_name,ed.last_name,ectc.*,ep.emp_company_id 
            from emp_salary_structure as ectc 
            left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
             left join branches as br on (br.branch_code = ep.emp_branch)
            left join department as dpt on (dpt.dept_code = ep.emp_dept)
            left join designation as desg on (desg.desig_code = ep.designation)
                                             where ectc.head_operator = 'ADDITION'  and lcase(head_type) not in ('manually','variable') and ectc.emp_fkey = '$sessionid' and ed.status = 1 and end_date_effective is null");
//        debug($arr_empleaverequests);
        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
                // 'employees'=>$arr_leavepolicy_employees
        );
        
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                $user_name = $this->Session->read('user_name');
                $this->set('user_name', $user_name);
                $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
                $this->set('arr_comp_contact_info', $arr_comp_contact_info);
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
                if(count($arr_empleaverequests) > 0){
                $worksheet->setCellValueByColumnAndRow(0, 1, $arr_empleaverequests['0']['ed']['first_name']." ".$arr_empleaverequests['0']['ed']['first_name']."'s CTC Details");
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
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . 2, 'Branch : '.$arr_empleaverequests['0']['br']['branch_name']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . 2, 'Designation : '.$arr_empleaverequests['0']['desg']['desig_name']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . 2, '  Department : '.$arr_empleaverequests['0']['dpt']['dept_name']);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . 2, ' Started On : '.$arr_loan_master['0']['EmployeeLoan']['emi_start_month']);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, 2)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, 2)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $rowcount = 4;

                $i = 0;


                
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'SALARY');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'AMOUNT');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                


                $col = 5;


                $j = 0;
                
                $rowcount = $rowcount + 1;
                $sum = 0;
                foreach ($arr_empleaverequests as $val) {
                    
                    $j+=1;
                    $col = 0;
                    $sum = $sum + $val['ectc']['structure_det_value'];

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $val['ectc']['salary_head_item_desc']);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col , $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $val['ectc']['structure_det_value']);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    
                    $rowcount++;
                    }
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, "Grand Total");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col , $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $sum);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col+1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
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

    public function attendanceReports() {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => array('status' => 1))));
        $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
        $arr_registerentries = array(
            'P' => array(
                'label' => 'Present',
                'color' => 'green',
                'textColor' => 'white'
            ),
            /*'L' => array(
                'label' => 'On Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),*/
            /*'FDL' => array(
                'label' => 'Full Day Leave', 
             * 
                'color' => 'orange',
                'textColor' => 'white'
            ),
            'FHL' => array(
                'label' => 'First Half Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),
            'SHL' => array(
                'label' => 'Second Half Leave',
                'color' => 'orange',
                'textColor' => 'white'
            ),*/
            'WO' => array(
                'label' => 'Week Off',
                'color' => 'yellow',
                'textColor' => 'black'
            ),
            'HO' => array(
                'label' => 'Holiday',
                'color' => 'blue',
                'textColor' => 'white'
            ),
            'A' => array(
                'label' => 'Absent',
                'color' => 'red',
                'textColor' => 'white'
            ),
            'LOP' => array(
                'label' => 'Loss Of Pay',
                'color' => 'maroon',
                'textColor' => 'white'
            ),
            'OTHERS' => array(
                'label' => 'Others',
                'color' => 'deepskyblue',
                'textColor' => 'white'
            )
        );
        
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_leavetypes = $this->SalaryHeadItems->query("select UCASE(ifnull(occurance,'LOP')) AS abbr,item from salary_head_items where ucase(item_type)='LEAVE' AND occurance != 'LOP'");
        $arr_leaveabbr = array();
        foreach ($arr_leavetypes as $leaveabbr => $leave) {
            if($leave[0]['abbr'] == 'TC'){
                $arr_registerentries['TC'] = array(
                'label' => 'Time Coupen',
                'color' => '#ef00ff',
                'textColor' => 'white'
            );
            }else{
            $arr_registerentries[$leave[0]['abbr']] = array(
                'label' => $leave['salary_head_items']['item'],
                'color' => 'orange',
                'textColor' => 'white'
            );
            }
            $arr_leaveabbr[] = strtoupper($leave[0]['abbr']);
        }
        
        //On 31 July 2016
        //$arr_leaveabbr[] = "LOP";
        $this->set('str_leaveabbr', implode('#', $arr_leaveabbr));
        
        $this->set('arr_registerentries', $arr_registerentries);
    }
    
    public function employeelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $attmonth = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';

        $first = date('Y-m-d', strtotime($attmonth));
        $last = date('Y-m-t', strtotime($attmonth));
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $sessionid = $this->Session->read('emp_fkey');

        $resp_att = array();
        $resp_att["rows"] = array();
        //   $count = $this->EmployeeCTC->find("count",array("conditions" => array('status' => 1)));
          $joins = array(
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`')
            ),
            array(
                'table' => 'emp_proff',
                'alias' => 'empproff',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('`empproff`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`')
            ),
              array(
                'table' => 'department',
                'alias' => 'department',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('`department`.`dept_code` = `empproff`.`emp_dept`')
            )
        );
        $resp_att = array();
        $resp_att["rows"] = array();
        $count = $this->DeviceAttendance->find("count",array(
           "fields"=>"`DeviceAttendance`.LOGDATE",
           "joins"=>$joins,
           "conditions"=>"`EmployeeDetails`.`emp_pkey`='$sessionid' and `DeviceAttendance`.`LOGDATE` between '$first' and '$last'",
           "limit"=>$limit,
           "ORDER BY  `DeviceAttendance`.`LOGDATE`,`EmployeeDetails`.`emp_id`,c1"
       ));
        $arr_attendancereport = $this->DeviceAttendance->find("all",array(
           "fields"=>"`DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,`DeviceAttendance`.C2,`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`department`.`dept_name`,`empproff`.emp_dept",
           "joins"=>$joins,
           "conditions"=>"`EmployeeDetails`.`emp_pkey`='$sessionid' and `DeviceAttendance`.`LOGDATE` between '$first' and '$last'",
           "limit"=>$limit,
           "ORDER BY  `DeviceAttendance`.`LOGDATE`,`EmployeeDetails`.`emp_id`,c1"
       ));
       // debug($arr_attendancereport);
//        $arr_attendancereport = $this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,`DeviceAttendance`.C2,`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`department`.`dept_name`,`empproff`.emp_dept "
//                . "FROM `device_attandance` AS `DeviceAttendance` "
//                . "LEFT JOIN `emp_details` AS `EmployeeDetails`"
//                . " ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`) "
//                . "LEFT JOIN `emp_proff` AS `empproff` ON (`empproff`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
//                . " LEFT JOIN `department` AS `department` ON (`department`.`dept_code` = `empproff`.`emp_dept`) "
//                . "WHERE `EmployeeDetails`.`emp_pkey` ='$sessionid' and  `DeviceAttendance`.`LOGDATE` between '$first' and '$last'"
//                . "ORDER BY  `DeviceAttendance`.`LOGDATE`,`EmployeeDetails`.`emp_id`,c1");


        //debug($arr_attendancereport);
        $out = array();
        foreach ($arr_attendancereport as $key => $value) {
            $out['C1'] = isset($value['DeviceAttendance']['C1']) ? $value['DeviceAttendance']['C1'] : '';
            $resp_att["rows"][$key] = $out;
            $out['C2'] = isset($value['DeviceAttendance']['C2']) ? $value['DeviceAttendance']['C2'] : '';
            $resp_att["rows"][$key] = $out;
            $out['C3'] = isset($value['DeviceAttendance']['C3']) ? $value['DeviceAttendance']['C3'] : '';
            $resp_att["rows"][$key] = $out;
            $out['LOGDATE'] = isset($value['DeviceAttendance']['LOGDATE']) ? $value['DeviceAttendance']['LOGDATE'] : '';
            $resp_att["rows"][$key] = $out;

            $resp_att["rows"][$key] = $out;
        }
            $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }



public function getattendancedays($start = '', $end = '')
    {
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        $this->Holiday->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        //          debug($arr_form_data);
        $start_req = isset($_GET['start']) ? $_GET['start'] : null;
        $end_req = isset($_GET['end']) ? $_GET['end'] : null;
        $sessionid = $this->Session->read('emp_fkey');
        $emp_pkey = $this->Session->read('emp_fkey');
        $curdate = date("Y-m-d");

        $company_code = strtoupper($this->Session->read('company_code'));
        $restrictedCompanies = [
            'ABSG',
            'VGFS',
            'VSFS',
            'DRRC',
            'DJIC',
            'AGNG',
            'AYRK',
            'SHRD',
            'SNRY',
            'GTRA',
            'VGNN',
            'SHYD'
        ];
        $isRestricted = in_array($company_code, $restrictedCompanies);

        $selected_month = isset($_GET['selected_month']) ? $_GET['selected_month'] : null;
        if ($selected_month) {
            $yearmonth = date("Y-m", strtotime($selected_month));
        } else {
            $yearmonth = date("Y-m", strtotime($start_req));
        }
        $yearmonth_date = $yearmonth . "-01";

        $holiday_map = [];
        $sc = [];
        $exception_map = [];
        $leave_map = [];
        $att_reg_final_record = null;
        $attendance_start = $yearmonth_date;
        $attendance_end = date("Y-m-t", strtotime($yearmonth_date));

        if (!$isRestricted) {
            // 1. Fetch Attendance cycle start and end
            $q_cycle = $this->EmployeeDetails->query("SELECT att_start_end_fn('$yearmonth_date', 1) AS start_date, att_start_end_fn('$yearmonth_date', 2) AS end_date");
            if (!empty($q_cycle)) {
                // In CakePHP 2.x, raw function results are often in [0][0] or [0]['']
                if (isset($q_cycle[0][0]['start_date'])) {
                    $attendance_start = $q_cycle[0][0]['start_date'];
                    $attendance_end = $q_cycle[0][0]['end_date'];
                } elseif (isset($q_cycle[0]['']['start_date'])) {
                    $attendance_start = $q_cycle[0]['']['start_date'];
                    $attendance_end = $q_cycle[0]['']['end_date'];
                }
            }

            // Ensure the range covers at least the full calendar month if selected_month is provided
            // to satisfy the requirement of showing Dec 26-31 when December is selected.
            if ($selected_month) {
                $month_end = date("Y-m-t", strtotime($yearmonth_date));
                if ($attendance_end < $month_end) {
                    $attendance_end = $month_end;
                }
            }

            // 2. Fetch all overlapping Attendance Register records
            $reg_months = [];
            $cycle_start_day = (int) date('d', strtotime($attendance_start));

            // Determine which register records cover our date range dynamically.
            $check_points = [$attendance_start, $attendance_end];
            foreach ($check_points as $cp) {
                $cp_time = strtotime($cp);
                if ($cycle_start_day > 1 && (int) date('d', $cp_time) >= $cycle_start_day) {
                    $m = date('Y-m', strtotime('+1 month', strtotime(date('Y-m-01', $cp_time))));
                } else {
                    $m = date('Y-m', $cp_time);
                }
                $reg_months[$m] = $m;
            }

            $m_start = min($reg_months);
            $m_end = max($reg_months);
            $cur_reg = strtotime($m_start . "-01");
            $last_reg = strtotime($m_end . "-01");
            $final_reg_months = [];
            while ($cur_reg <= $last_reg) {
                $final_reg_months[] = date('Y-m', $cur_reg);
                $cur_reg = strtotime('+1 month', $cur_reg);
            }
            $reg_months_str = "'" . implode("','", $final_reg_months) . "'";

            $arr_att_reg_final = $this->EmployeeDetails->query("
                SELECT * 
                FROM attendance_register 
                WHERE emp_fkey = '$emp_pkey' 
                AND month_year IN ($reg_months_str)
                AND isdelete = 'N'
            ");

            $att_reg_map = [];
            foreach ($arr_att_reg_final as $reg) {
                $att_reg_map[$reg['attendance_register']['month_year']] = $reg['attendance_register'];
            }
            $att_reg_final_record = !empty($att_reg_map) ? true : false;

            if ($att_reg_final_record) {
                // Check if we also need March register for boundary dates? 
                // For now, fallback to primary sources for dates outside cycle is safer.
            }

            // --- Primary Sources: Always fetch as fallback for boundary dates (e.g. Feb 26-28) ---

            // Modified Query to Include HOLIDAY_GROUP_ID
            $have_shift = $this->EmployeeDetails->query("SELECT day_time_seq, HOLIDAY_GROUP_ID FROM emp_proff WHERE emp_fkey='$emp_pkey'");
            $shift_id = !empty($have_shift) ? $have_shift[0]['emp_proff']['day_time_seq'] : null;
            $holiday_group_id = !empty($have_shift) ? $have_shift[0]['emp_proff']['HOLIDAY_GROUP_ID'] : null;

            // Fetch Holidays
            if ($holiday_group_id) {
                $holidays_query = $this->EmployeeDetails->query("
                    SELECT HOLIDAYDATE, HOLIDAYNAME 
                    FROM holidays 
                    WHERE HOLIDAY_GROUP_ID = '$holiday_group_id' 
                    AND status = 1 
                    AND HOLIDAYDATE BETWEEN '$attendance_start' AND '$attendance_end'
                ");
                foreach ($holidays_query as $h) {
                    $holiday_map[$h['holidays']['HOLIDAYDATE']] = $h['holidays']['HOLIDAYNAME'];
                }
            }

            // Fetch Week Off Config
            if ($shift_id) {
                $shift_config = $this->EmployeeDetails->query("
                    SELECT Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday,is_exception,
                           Sunday_F, Monday_F, Tuesday_F, Wednesday_F, Thursday_F, Friday_F, Saturday_F
                    FROM working_day_time_procedures 
                    WHERE day_time_seq = '$shift_id'
                ");
                $sc = !empty($shift_config) ? $shift_config[0]['working_day_time_procedures'] : [];
                $is_exception = $sc['is_exception'];

                if ($is_exception == '1') {
                    // Fetch Shift Exceptions
                    $shift_exceptions = $this->EmployeeDetails->query("
                    SELECT ex_week_day, ex_week, week_off 
                    FROM shift_exceptions 
                    WHERE shift_id = '$shift_id' 
                    AND status = 1
                ");
                    foreach ($shift_exceptions as $ex) {
                        $day = $ex['shift_exceptions']['ex_week_day'];
                        $week = $ex['shift_exceptions']['ex_week'];
                        $exception_map[$day][$week] = $ex['shift_exceptions']['week_off'];
                    }
                }
            }

            // Fetch Leaves — use attendance cycle dates, not function params
            // ($start/$end are URL segments = empty; GET params are in $start_req/$end_req)
            $leaves_query = $this->EmployeeDetails->query("
                SELECT
                    elt.leave_date,
                    elt.leave_session,
                    elt.Leavestatus,
                    shi.occurance
                FROM emp_leave_transactions AS elt
                JOIN leaveentries AS leaves ON leaves.LEAVEENTRYID = elt.LEAVEENTRYID
                JOIN salary_head_items AS shi ON shi.salary_head_item_pkey = leaves.salary_head_item_fkey
                WHERE leaves.EMP_fkey = '$emp_pkey' AND elt.Leavestatus IN ('Authorized','Approved')
                AND elt.leave_date BETWEEN '$attendance_start' AND '$attendance_end'
            ");
            foreach ($leaves_query as $lq) {
                $leave_map[$lq['elt']['leave_date']] = $lq;
            }
        }

        // Override start and end with the attendance cycle boundaries to follow the payroll cycle
        $start = $attendance_start;
        $end = $attendance_end;

        $arr_leavedays = $this->EmployeeLeaveTransaction->query("select att_date,att_in_time,att_out_time,duration,present,weekoff,leaves,holiday,yearmonth,others
                                                                 from emp_detail_timeattandance where emp_pkey ='$sessionid' and att_date between '$start' and  '$end' ");

        $attendance_map = [];
        foreach ($arr_leavedays as $val) {
            $attendance_map[$val['emp_detail_timeattandance']['att_date']] = $val;
        }

        $period = [];
        $current = strtotime($start);
        $last = strtotime($end);
        while ($current <= $last) {
            $period[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        $data = array();
        foreach ($period as $att_date) {
            $holidays = array();
            $val = isset($attendance_map[$att_date]) ? $attendance_map[$att_date] : null;

            $intime = isset($val['emp_detail_timeattandance']['att_in_time']) ? $val['emp_detail_timeattandance']['att_in_time'] : '00:00:00';
            $time = date("H:i:s", strtotime($intime));
            $outtime = isset($val['emp_detail_timeattandance']['att_out_time']) ? $val['emp_detail_timeattandance']['att_out_time'] : '00:00:00';
            $time2 = date("H:i:s", strtotime($outtime));
            $duration = isset($val['emp_detail_timeattandance']['duration']) ? $val['emp_detail_timeattandance']['duration'] : '0';
            $present = isset($val['emp_detail_timeattandance']['present']) ? $val['emp_detail_timeattandance']['present'] : '';

            $final_title = '';

            if ($isRestricted) {
                // Restricted: Always use emp_detail_timeattandance
                $db_p = isset($val['emp_detail_timeattandance']['present']) ? $val['emp_detail_timeattandance']['present'] : '';
                $db_w = isset($val['emp_detail_timeattandance']['weekoff']) ? $val['emp_detail_timeattandance']['weekoff'] : '';
                $db_l = isset($val['emp_detail_timeattandance']['leaves']) ? $val['emp_detail_timeattandance']['leaves'] : '';
                $db_h = isset($val['emp_detail_timeattandance']['holiday']) ? $val['emp_detail_timeattandance']['holiday'] : '';
                $db_o = isset($val['emp_detail_timeattandance']['others']) ? $val['emp_detail_timeattandance']['others'] : '';

                // Merge Presence (db_p), Leaves (db_l), and Weekoffs (db_w) session by session
                $merged_p = '';
                $p_parts = explode('/', str_replace(' ', '', $db_p));
                $l_parts = explode('/', str_replace(' ', '', $db_l));
                $w_parts = explode('/', str_replace(' ', '', $db_w));

                $p1 = isset($p_parts[0]) ? $p_parts[0] : '';
                $p2 = isset($p_parts[1]) ? $p_parts[1] : '';
                $l1 = isset($l_parts[0]) ? $l_parts[0] : '';
                $l2 = isset($l_parts[1]) ? $l_parts[1] : '';
                $w1 = isset($w_parts[0]) ? $w_parts[0] : '';
                $w2 = isset($w_parts[1]) ? $w_parts[1] : '';

                // Priority: Leave > WeekOff > Presence
                $s1 = !empty($l1) ? $l1 : (!empty($w1) ? $w1 : $p1);
                $s2 = !empty($l2) ? $l2 : (!empty($w2) ? $w2 : $p2);

                if ($s1 || $s2) {
                    $merged_p = $s1 . '/' . $s2;
                }

                $final_title = trim($merged_p . ' ' . $db_h . ' ' . $db_o);
                $leavedays = $db_l; // Ensure leavedays is set for color logic Rule 5
            } else {
                $use_primary = false;

                if ($att_reg_final_record) {
                    if ($att_date >= $attendance_start && $att_date <= $attendance_end) {
                        $d_time = strtotime($att_date);
                        $d_day = (int) date('d', $d_time);
                        $cycle_start_day = (int) date('d', strtotime($attendance_start));

                        if ($cycle_start_day == 1) {
                            // Standard 1-31 cycle
                            $att_month = date('Y-m', $d_time);
                            $field_idx = $d_day;
                        } else {
                            // Shifted cycle (e.g. 26-25)
                            if ($d_day >= $cycle_start_day) {
                                // Date is in the shifted part (e.g. 26-31): belongs to NEXT payroll month
                                $att_month = date('Y-m', strtotime('+1 month', strtotime(date('Y-m-01', $d_time))));
                                $field_idx = $d_day - ($cycle_start_day - 1);
                            } else {
                                // Date is in the current month part (e.g. 1-25): belongs to CURRENT payroll month
                                $att_month = date('Y-m', $d_time);
                                $prev_month_last_day = date('t', strtotime('-1 month', strtotime(date('Y-m-01', $d_time))));
                                $field_idx = ($prev_month_last_day - ($cycle_start_day - 1)) + $d_day;
                            }
                        }

                        $field_name = 'FIELD' . $field_idx;
                        $reg_record = isset($att_reg_map[$att_month]) ? $att_reg_map[$att_month] : null;
                        $reg_status = isset($reg_record[$field_name]) ? trim($reg_record[$field_name]) : '';

                        if (!empty($reg_status)) {
                            // Overlay approved leave from leave_map onto register status
                            $reg_leavedays = '';
                            if (isset($leave_map[$att_date])) {
                                $l_data = $leave_map[$att_date];
                                $l_session = $l_data['elt']['leave_session'];
                                $l_occ = $l_data['shi']['occurance'];
                                if ($l_session == 3) {
                                    $reg_leavedays = "$l_occ/$l_occ";
                                } elseif ($l_session == 1) {
                                    $reg_leavedays = "$l_occ/";
                                } elseif ($l_session == 2) {
                                    $reg_leavedays = "/$l_occ";
                                }
                            }

                            if (!empty($reg_leavedays)) {
                                $rp = explode('/', str_replace(' ', '', $reg_status));
                                $rl = explode('/', str_replace(' ', '', $reg_leavedays));
                                $rp1 = isset($rp[0]) ? $rp[0] : '';
                                $rp2 = isset($rp[1]) ? $rp[1] : '';
                                $rl1 = isset($rl[0]) ? $rl[0] : '';
                                $rl2 = isset($rl[1]) ? $rl[1] : '';
                                $rs1 = !empty($rl1) ? $rl1 : $rp1;
                                $rs2 = !empty($rl2) ? $rl2 : $rp2;
                                $final_title = trim($rs1 . '/' . $rs2);
                                $leavedays = $reg_leavedays;
                            } else {
                                $final_title = $reg_status;
                            }
                        } else {
                            $use_primary = true;
                        }
                    } else {
                        // Date is outside the finalized register cycle
                        $use_primary = true;
                    }
                } else {
                    // Register is deleted ('Y') or does not exist
                    $use_primary = true;
                }

                if ($use_primary) {
                    // Non-Restricted with Primary Sources
                    $leavedays = '';
                    if (isset($leave_map[$att_date])) {
                        $l_data = $leave_map[$att_date];
                        $session = $l_data['elt']['leave_session'];
                        $occurance = $l_data['shi']['occurance'];
                        if ($session == 3) {
                            $leavedays = "$occurance/$occurance";
                        } elseif ($session == 1) {
                            $leavedays = "$occurance/";
                        } elseif ($session == 2) {
                            $leavedays = "/$occurance";
                        }
                    }

                    $hoilday = isset($holiday_map[$att_date]) ? 'HO' : '';

                    $weekoff = '';
                    if (!empty($sc)) {
                        $day_name = date('l', strtotime($att_date));
                        $day_num = date('d', strtotime($att_date));
                        $week_index = ceil($day_num / 7);

                        $is_wo = false;
                        $is_half_wo = false;

                        $exception_wo = isset($exception_map[$day_name][$week_index]) ? $exception_map[$day_name][$week_index] : null;

                        if ($exception_wo === 'Y') {
                            $is_wo = true;
                        } elseif ($exception_wo === 'N') {
                            $is_wo = false;
                        } else {
                            $is_wo = ($sc[$day_name] == 'N');
                            $is_half_wo = ($sc[$day_name . '_F'] == 'Y');
                        }

                        if ($is_wo) {
                            $weekoff = 'WO';
                        } elseif ($is_half_wo) {
                            $weekoff = '/WO';
                        }
                    }

                    // Merge Presence (present), Leaves (leavedays), and Weekoff (weekoff) session by session
                    $merged_p = '';
                    $p_parts = explode('/', str_replace(' ', '', $present));
                    $l_parts = explode('/', str_replace(' ', '', $leavedays));
                    $w_temp = ($weekoff == 'WO') ? 'WO/WO' : $weekoff; // Normalize full WO
                    $w_parts = explode('/', str_replace(' ', '', $w_temp));

                    $p1 = isset($p_parts[0]) ? $p_parts[0] : '';
                    $p2 = isset($p_parts[1]) ? $p_parts[1] : '';
                    $l1 = isset($l_parts[0]) ? $l_parts[0] : '';
                    $l2 = isset($l_parts[1]) ? $l_parts[1] : '';
                    $w1 = isset($w_parts[0]) ? $w_parts[0] : '';
                    $w2 = isset($w_parts[1]) ? $w_parts[1] : '';

                    // Priority: Leave > WeekOff > Presence
                    $s1 = !empty($l1) ? $l1 : (!empty($w1) ? $w1 : $p1);
                    $s2 = !empty($l2) ? $l2 : (!empty($w2) ? $w2 : $p2);

                    if ($s1 || $s2) {
                        $merged_p = $s1 . '/' . $s2;
                    }

                    $final_title = trim($merged_p . ' ' . $hoilday);
                }
            }

            $holidays['content'] = 'In-Time : ' . $time . '-Out-Time : ' . $time2 . ' Duration :' . $duration . ' Minutes';

            if ($final_title == '' && $att_date < $curdate) {
                $holidays['backgroundColor'] = 'Red';
                $holidays['textColor'] = 'white';
                $holidays['title'] = '';
                $holidays['leavetype'] = $holidays['title'];
                $holidays['start'] = $att_date;
                $data[] = $holidays;
            }

            if ($final_title != '') {
                $holidays['HOLIDAYID'] = $att_date;
                $holidays['title'] = $final_title;

                // Set colors
                $holidays['backgroundColor'] = 'white';
                $holidays['textColor'] = 'black';

                $check_status = strtoupper($final_title);
                if (strpos($check_status, 'LOP') !== false || strpos($check_status, 'A') !== false) {
                    $holidays['backgroundColor'] = 'Red';
                    $holidays['textColor'] = 'white';
                } elseif (strpos($check_status, 'HO') !== false) {
                    $holidays['backgroundColor'] = 'blue';
                    $holidays['textColor'] = 'white';
                } elseif (strpos($check_status, 'WO') !== false) {
                    $holidays['backgroundColor'] = 'yellow';
                    $holidays['textColor'] = 'black';
                } elseif (strpos($check_status, 'P/P') !== false || $check_status === 'P' || $check_status === 'P/' || $check_status === '/P' || $check_status === 'P / P') {
                    $holidays['backgroundColor'] = 'green';
                    $holidays['textColor'] = 'white';
                } elseif ((isset($leavedays) && !empty($leavedays) && $leavedays != '0/0')) {
                    $holidays['backgroundColor'] = 'orange';
                    $holidays['textColor'] = 'white';
                }

                $holidays['leavetype'] = $holidays['title'];
                $holidays['start'] = $att_date;
                $data[] = $holidays;
            }
        }
        echo json_encode($data);

    }



    public function showattendancedetails($HOLIDAYID = "")
    {

        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        $sessionid = $this->Session->read('emp_fkey');
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $arr_leavedata = array();
        if ($arr_form_data == null) {
            $arr_attendancedays = $this->DeviceAttendance->query("select emp_detail_timeattandance.*,emp_details.emp_id from emp_detail_timeattandance left join emp_details on (emp_details.emp_pkey = emp_detail_timeattandance.emp_pkey) where att_date = '$HOLIDAYID' and emp_detail_timeattandance.emp_pkey = '$sessionid'");
            $startdate = isset($arr_attendancedays['0']['emp_detail_timeattandance']['att_in_time']) ? $arr_attendancedays['0']['emp_detail_timeattandance']['att_in_time'] : '';
            $in = date("Y-m-d", strtotime($startdate));
            $out = isset($arr_attendancedays['0']['emp_detail_timeattandance']['att_out_time']) ? $arr_attendancedays['0']['emp_detail_timeattandance']['att_out_time'] : '';
            if ($out == null) {
                $enddate = $in . ' 23:23:00';
            } else {
                $enddate = $out;
            }
            $this->set('arr_attendancedays', $arr_attendancedays);
            if (!empty($HOLIDAYID)) {
                /* Holiday lookup for this date */
                $holiday_info = array();
                $have_shift = $this->DeviceAttendance->query("SELECT HOLIDAY_GROUP_ID FROM emp_proff WHERE emp_fkey='$sessionid'");
                $holiday_group_id = !empty($have_shift) ? $have_shift[0]['emp_proff']['HOLIDAY_GROUP_ID'] : null;
                if ($holiday_group_id) {
                    $holiday_info = $this->DeviceAttendance->query("
                        SELECT HOLIDAYNAME, HOLIDAYDATE
                        FROM holidays
                        WHERE HOLIDAY_GROUP_ID = '$holiday_group_id'
                        AND status = 1
                        AND DATE(HOLIDAYDATE) = '$HOLIDAYID'
                        LIMIT 1
                    ");
                }
                $this->set('arr_holiday', $holiday_info);

                $arr_leavedata = $this->EmployeeLeaveTransaction->query("
                    SELECT emp_leave_transactions.leave_date, emp_leave_transactions.leave_session,
                           emp_leave_transactions.Leavestatus, emp_leave_transactions.Remarks,
                           salary_head_items.occurance, salary_head_items.item,
                           leaveentries.Autherized_date, leaveentries.APPROVED_date,
                           (SELECT CONCAT(first_name,' ',middile_name,' ',last_name)
                            FROM emp_details WHERE emp_pkey = leaveentries.ISAutherizedby) AS authorized_by,
                           (SELECT CONCAT(first_name,' ',middile_name,' ',last_name)
                            FROM emp_details WHERE emp_pkey = leaveentries.APPROVEDBY) AS approved_by
                    FROM emp_leave_transactions
                    JOIN leaveentries ON leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID
                    JOIN salary_head_items ON salary_head_items.salary_head_item_pkey = leaveentries.salary_head_item_fkey
                    WHERE leaveentries.EMP_fkey = '$sessionid'
                    AND DATE(emp_leave_transactions.leave_date) = '$HOLIDAYID'
                    AND emp_leave_transactions.Leavestatus IN ('Applied', 'Authorized', 'Approved')
                ");
            }
        } else {
            $startdate = isset($arr_form_data['start']) ? $arr_form_data['start'] : '';
            $enddate = isset($arr_form_data['end']) ? $arr_form_data['end'] : '';
            $this->set('arr_holiday', array());
        }
        $arr_empid = $this->DeviceAttendance->query("select emp_id from emp_details where emp_pkey = $sessionid");
        $empid = $arr_empid['0']['emp_details']['emp_id'];
        $arr_timings = array();
        $arr_shiftname = '';
        if ($startdate != null && $enddate != null) {
            $arr_timings = $this->DeviceAttendance->query("
                SELECT C1, LOGDATE, C3, SHIFT
                FROM device_attandance
                WHERE status = 'Y'
                AND LOGDATE BETWEEN '$startdate' AND '$enddate'
                AND emp_id = '$empid'
            ");

            /* Separate shift name lookup — avoids cross-DB JOIN uncertainty */
            if (!empty($arr_timings)) {
                $shiftSeq = isset($arr_timings[0]['device_attandance']['SHIFT']) ? $arr_timings[0]['device_attandance']['SHIFT'] : '';
                $shiftSeqInt = intval($shiftSeq);
                if ($shiftSeqInt > 0) {
                    $shiftResult = $this->DeviceAttendance->query("
                        SELECT day_time_desc FROM working_day_time_procedures
                        WHERE day_time_seq = $shiftSeqInt LIMIT 1
                    ");
                    if (!empty($shiftResult)) {
                        $arr_shiftname = isset($shiftResult[0]['working_day_time_procedures']['day_time_desc'])
                            ? trim($shiftResult[0]['working_day_time_procedures']['day_time_desc'])
                            : '';
                    }
                }
            }
        }

        /* Fallback to emp_proff if shift is still empty */
        if (trim($arr_shiftname) == '') {
            $empProffResult = $this->DeviceAttendance->query("
                SELECT day_time_seq FROM emp_proff
                WHERE emp_fkey = $sessionid LIMIT 1
            ");
            if (!empty($empProffResult)) {
                $shiftSeqInt = intval($empProffResult[0]['emp_proff']['day_time_seq']);
                if ($shiftSeqInt > 0) {
                    $shiftResult = $this->DeviceAttendance->query("
                        SELECT day_time_desc FROM working_day_time_procedures
                        WHERE day_time_seq = $shiftSeqInt LIMIT 1
                    ");
                    if (!empty($shiftResult)) {
                        $arr_shiftname = isset($shiftResult[0]['working_day_time_procedures']['day_time_desc'])
                            ? trim($shiftResult[0]['working_day_time_procedures']['day_time_desc'])
                            : '';
                    }
                }
            }
        }
        $arr_empdata = $this->DeviceAttendance->query("select EmpName,employee_id,branch from employee_info where employee_info.emp_pkey = $sessionid");
        $this->set('arr_timings', $arr_timings);
        $this->set('arr_shiftname', $arr_shiftname);
        $this->set('arr_empid', $arr_empid);
        $this->set('arr_empdata', $arr_empdata);
        $this->set('arr_leavedata', $arr_leavedata);
        //$this->render('showattendancedetails');
        //debug($arr_attendancedays);
        // debug($arr_timings);
    }
    public function employeeleavelist() {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //  debug($arr_request_data);
        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $attmonth = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';

        $first = date('Y-m-d', strtotime($attmonth));
        $last = date('Y-m-t', strtotime($attmonth));
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $sessionid = $this->Session->read('emp_fkey');
        //debug($sessionid);
        $resp_att = array();
        $resp_att["rows"] = array();
        //   $count = $this->EmployeeCTC->find("count",array("conditions" => array('status' => 1)));
        $arr_attendancereport = $this->EmployeeLeaveTransaction->query("select leave_date,leave_session, Leavestatus, Remarks  from  emp_leave_transactions 
                           where   Leavestatus in('Applied','Authorized','Approved','Cancelled') 
and LEAVEENTRYID in(select LEAVEENTRYID from leaveentries where EMP_fkey='$sessionid'  and  `leave_date` between '$first' and '$last')"
                . "and Leavestatus in('Applied','Authorized','Approved','Cancelled')");

        // debug($arr_attendancereport);
        $out = array();
        // debug($out);

        foreach ($arr_attendancereport as $key => $value) {
            // debug($value);
            $out['leave_date'] = isset($value['emp_leave_transactions']['leave_date']) ? $value['emp_leave_transactions']['leave_date'] : '';
            $resp_att["rows"][$key] = $out;
            $out['Leavestatus'] = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : '';
            $resp_att["rows"][$key] = $out;
            if ($value['emp_leave_transactions']['leave_session'] == 3) {
                $sess = "Full Day Leave";
            } elseif ($value['emp_leave_transactions']['leave_session'] == 2) {
                $sess = "Second Half ";
            } else {
                $sess = "First Half ";
            }
            $out['leave_session'] = isset($sess) ? $sess : '';
            $resp_att["rows"][$key] = $out;
            $out['Remarks'] = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';
            $resp_att["rows"][$key] = $out;


            $resp_att["rows"][$key] = $out;

            $resp_att["rows"][$key] = $out;
        }
        //    $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    public function attendance() {

        $this->layout = null;
        $sessionid = $this->Session->read('emp_fkey');
        //debug($sessionid);
        $arr_select = $this->request->data;
        $attmonth = isset($arr_select['attmonth']) ? $arr_select['attmonth'] : '';
        $resp_att = array();
        $resp_att["rows"] = array();
        $first = date('Y-m-d', strtotime($attmonth));
        $last = date('Y-m-t', strtotime($attmonth));
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $arr_attendancereport = $this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,`DeviceAttendance`.C2,`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`department`.`dept_name`,`empproff`.emp_dept "
                . "FROM `device_attandance` AS `DeviceAttendance` "
                . "LEFT JOIN `emp_details` AS `EmployeeDetails`"
                . " ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`) "
                . "LEFT JOIN `emp_proff` AS `empproff` ON (`empproff`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                . " LEFT JOIN `department` AS `department` ON (`department`.`dept_code` = `empproff`.`emp_dept`) "
                . "WHERE `EmployeeDetails`.`emp_pkey` ='$sessionid'  "
                . "ORDER BY  `DeviceAttendance`.`LOGDATE`,`EmployeeDetails`.`emp_id`,c1");
//debug($arr_attendancereport);
        $out = array();
        foreach ($arr_attendancereport as $key => $value) {

            // $out['empname'] = isset($value[0]['empname']) ? $value[0]['empname'] : '';
            $out['C1'] = isset($value['DeviceAttendance']['C1']) ? $value['DeviceAttendance']['C1'] : '';

            $resp_att["rows"][$key] = $out;
        }
        //   $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

//
    public function attendanceregister() {
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $empfkey = $this->Session->read('emp_fkey');
        $arr_employees = $this->AttendanceRegister->find("all", array("conditions" => array('emp_fkey' => $empfkey)));
        $this->set('arr_employees', $arr_employees);
        //debug(arr_employees);
    }

   public function leavepolicyreport($mode = '') {
        $sessionid = $this->Session->read('emp_fkey');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->FinancialYear->useDbConfig = $this->Session->read('ds');
        $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_db_config = Set::extract('/FinancialYear/.', $this->FinancialYear->find("first", array("fields" => array("fin_year"), "conditions" => array('is_current_finyear' => 'Y', 'status' => 1, 'Year_status' => 'OPEN', "branch_code in (select branch_code from emp_details where emp_Pkey='$sessionid' )", 'vattr1'=>0))));
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code')); // Edited by Akshay on 23-10-2025
        $this->set('company_code', $company_code); // Edited by Akshay on 23-10-2025
        $year = $arr_db_config[0]['fin_year'];
        $month =  date('Y M');

        // Edited by Akshay on 23-10-2025
      $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
            $currentDate = date('Y-m-d'); 
            $monthFnPart = " leave_balance_inthe_year_fn($sessionid,salary_head_item_fkey,'$currentDate') as leavebal, NULL as monthlybalance ";  
        }else{
            $monthFnPart = " leave_balance_inthe_year_fn($sessionid,salary_head_item_fkey,'$year') 
                                                as leavebal, leave_balance_inthe_month_fn($sessionid, salary_head_item_fkey, '$month', '$year') as monthlybalance ";
        }
        // End 

        //edited by athira 24-01-2026
        $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {

         $arr_leave = $this->LeavePolicy->query("select ( select (count(case when leave_session = 3 then 1 else null end) + (count(case when leave_session != 3 
                                                then 1 else null end))/2) as counts from emp_leave_transactions WHERE Leavestatus in ('Approved', 'Applied', 'Authorized') and LEAVEENTRYID in -- Edited by Akshay on 11-11-2025
                                                (select LEAVEENTRYID FROM leaveentries where EMP_fkey = $sessionid and salary_head_item_fkey = leavepolicy.salary_head_item_fkey) 
                                                and leave_date between leavepolicy.leave_cycle_start_date and leavepolicy.leave_cycle_end_date  ) as Takens_leaves, is_leave_encash,occurance,
                                                REMARKS,item,alloted_leave_forthe_year,alloted_leave_forthe_month,leave_policy_type,CARRY_FORWARD_LIMIT,
                                                APPLICABLE_TO,ALLOW_NEGETIVE,IS_SANDWICH,
                                                $monthFnPart 
                 
                                                from leavepolicy,salary_head_items where salary_head_item_fkey=salary_head_item_pkey 
                                                and LEAVEPOLICY_GROUP_ID
                                                in (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey= $sessionid and leavepolicy.status = 1)");

         }
         else{

         $arr_leave = $this->LeavePolicy->query("select ( select (count(case when leave_session = 3 then 1 else null end) + (count(case when leave_session != 3 
                                                then 1 else null end))/2) as counts from emp_leave_transactions WHERE Leavestatus in ('Approved', 'Applied', 'Authorized') and LEAVEENTRYID in -- Edited by Akshay on 11-11-2025
                                                (select LEAVEENTRYID FROM leaveentries where EMP_fkey = $sessionid and salary_head_item_fkey = leavepolicy.salary_head_item_fkey) 
                                                and leave_date between '$year-01-01' and '$year-12-31'  ) as Takens_leaves, is_leave_encash,occurance,
                                                REMARKS,item,alloted_leave_forthe_year,alloted_leave_forthe_month,leave_policy_type,CARRY_FORWARD_LIMIT,
                                                APPLICABLE_TO,ALLOW_NEGETIVE,IS_SANDWICH,
                                                $monthFnPart 
                 
                                                from leavepolicy,salary_head_items where salary_head_item_fkey=salary_head_item_pkey 
                                                and LEAVEPOLICY_GROUP_ID
                                                in (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey= $sessionid and leavepolicy.status = 1)");

         }

//end
     
        $arr_leave_details = $this->EmployeeDetails->query("select applied_date, LEAVESTATUS, FROMDATE, FROMHALF, TODATE, TOHALF, ISAutherized,
                                                            (select concat(first_name,' ',middile_name,' ',last_name) from emp_details where emp_pkey=ISAutherizedby) 
                                                            Autherizedby, Autherized_date ,ISAPPROVED,
                                                            (select concat(first_name,' ',middile_name,' ',last_name) from emp_details where emp_pkey=APPROVEDBY)
                                                            APPROVEDBY, APPROVED_date, Reason, contact_No, contact_person ,REMARKS ,leave_days, message
                                                            from leaveentries where EMP_fkey=$sessionid 
                                                            and Leavestatus in('Applied','Authorized','Approved','Cancelled')
                                                            ");
  
        $arr_leavedays = $this->EmployeeLeaveTransaction->query("select leaveentries .LEAVEENTRYID,salary_head_items.occurance,salary_head_items.salary_head_item_pkey,leave_date,leave_session, emp_leave_transactions.Leavestatus, emp_leave_transactions.Remarks  from  emp_leave_transactions
                                    join leaveentries on (leaveentries.LEAVEENTRYID =   emp_leave_transactions.LEAVEENTRYID)     join salary_head_items on (salary_head_items.salary_head_item_pkey =       leaveentries.salary_head_item_fkey )               where   emp_leave_transactions.Leavestatus in('Applied','Authorized','Approved','Cancelled')
                                    and  leaveentries.EMP_fkey=$sessionid");
  

        $this->set('arr_leave', $arr_leave);
        $arr_emp = $this->getempdetails($sessionid);
        $this->set('arr_emp', $arr_emp);
        $this->set('arr_leave_details', $arr_leave_details);
        $arr_emp = $this->getempdetails($sessionid);
        $this->set('arr_emp', $arr_emp);
        $this->set('arr_leavedays', $arr_leavedays);
        //        $this->set('arr_leave_policy', $arr_leave_policy);
        switch ($mode) {
            case 'pdf' :
                //echo "entered in";die();
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('leavepolicyreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('leavepolicyreport.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :

                break;
            default :
                $this->set('mode', '');
                $this->render('leavedaysreport');
                break;
        }
    }
     public function Getleavedays()
    { 
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        $this->Holiday->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $sessionid = $this->Session->read('emp_fkey');
        $arr_leavedays = $this->EmployeeLeaveTransaction->query("select leaveentries .LEAVEENTRYID,salary_head_items.occurance,salary_head_items.item,leave_date,leave_session, emp_leave_transactions.Leavestatus, emp_leave_transactions.Remarks  from  emp_leave_transactions
                                                                join leaveentries on (leaveentries.LEAVEENTRYID =   emp_leave_transactions.LEAVEENTRYID)     join salary_head_items on (salary_head_items.salary_head_item_pkey =       leaveentries.salary_head_item_fkey )  where   emp_leave_transactions.Leavestatus in('Applied','Authorized','Approved','Cancelled')
                                                                and  leaveentries.EMP_fkey=$sessionid");
        // debug($arr_ss);
        $arr_leave_details = $this->EmployeeDetails->query("select applied_date, LEAVESTATUS, FROMDATE, FROMHALF, TODATE, TOHALF, ISAutherized,
                                                      (select concat(first_name,middile_name,last_name) from emp_details where emp_pkey=ISAutherizedby) 
                                                      Autherizedby, Autherized_date ,ISAPPROVED,
                                                      (select concat(first_name,middile_name,last_name) from emp_details where emp_pkey=APPROVEDBY)
                                                      APPROVEDBY, APPROVED_date, Reason, contact_No, contact_person ,REMARKS ,leave_days, message
                                                      from leaveentries where EMP_fkey=$sessionid 
                                                      and Leavestatus in('Applied','Authorized','Approved','Cancelled')
                                                     ");
        $this->set('arr_holiday', $arr_leavedays);
//        debug($arr_leavedays['0']['leaveentries']['LEAVEENTRYID']);
       // debug($arr_leavedays);
        $holidays = array();
            $data = array();
            foreach ($arr_leavedays as $val)
            {
               
                $holidays['HOLIDAYID'] = $val['leaveentries']['LEAVEENTRYID'];
               
                $holidays['title'] = $val['salary_head_items']['occurance'];
                if($val['emp_leave_transactions']['leave_session'] == 3){
                  $holidays['backgroundColor'] = '#00659f';  
                  $leavesesion = ' - Full Day';
                   $holidays['leavetype'] = $val['salary_head_items']['item'].$leavesesion;
                }
                else if($val['emp_leave_transactions']['leave_session'] == 2){
                  $holidays['backgroundColor'] = 'crimson'; 
                  $leavesesion = ' - Second Half';
                  $holidays['leavetype'] = $val['salary_head_items']['item'].$leavesesion;
                }
                else{
                  $holidays['backgroundColor'] = 'green';  
                  $leavesesion = ' - First Half';
                  $holidays['leavetype'] = $val['salary_head_items']['item'].$leavesesion;
                }
                
                $holidays['start'] = $val['emp_leave_transactions']['leave_date'];
                $holidays['borderColor'] = '#dsfsde';
                $data[] = $holidays;
            }
            echo json_encode($data);
    }
    public function showleavedetails($HOLIDAYID = ""){
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $sessionid = $this->Session->read('emp_fkey');
        $arr_leave_details = $this->EmployeeDetails->query("select applied_date,item,LEAVEENTRYID,LEAVESTATUS, FROMDATE, FROMHALF, TODATE,salary_head_item_fkey, TOHALF, ISAutherized,ifnull((select concat(first_name,' ',middile_name,' ',last_name) from emp_details where emp_pkey=ISAutherizedby) 
                                                            ,'Admin') Autherizedby, Autherized_date ,ISAPPROVED,
                                                            ifnull((select concat(first_name,' ',middile_name,' ',last_name) from emp_details where emp_pkey=APPROVEDBY)
                                                            ,'Admin') APPROVEDBY, APPROVED_date, Reason, contact_No, contact_person ,REMARKS ,leave_days, message
                                                            from leaveentries join salary_head_items on (salary_head_item_pkey =  salary_head_item_fkey ) where EMP_fkey=$sessionid and LEAVEENTRYID=$HOLIDAYID
                                                              ");
        $this->set('arr_leave_details', $arr_leave_details);
        $salaryheadkey = $arr_leave_details['0']['leaveentries']['salary_head_item_fkey'];
        $arr_leave_policy = $this->EmployeeDetails->query("select REMARKS from leavepolicy where leavepolicy.salary_head_item_fkey = $salaryheadkey");
//        $conditions = ;
//        $arr_leave_type = $this->SalaryHeadItems->query("select occurance from salary_head_items where salary_head_items.salary_head_item_pkey = $conditions");
//        leaveentries
        $this->set('arr_leave_policy', $arr_leave_policy);
//      debug($arr_leave_policy);
    }

    public function holidayreport($mode = "") {
        $this->Holiday->useDbConfig = $this->Session->read('ds');
        $sessionid = $this->Session->read('emp_fkey');
        $arr_holiday = $this->Holiday->query("SELECT HOLIDAYNAME, HOLIDAYDATE, DESCRIPTION, HOLIDAYTYPE  FROM holidays
                       where HOLIDAY_GROUP_ID in (select HOLIDAY_GROUP_ID from emp_proff where emp_fkey=$sessionid)");
        //debug($_SERVER['HTTP_HOST']);
        $this->set('arr_holiday', $arr_holiday);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $arr_emp = $this->getempdetails($sessionid);
        $this->set('arr_emp', $arr_emp);
        switch ($mode) {
            case 'pdf' :
                //echo "entered in";die();
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('holidayreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('holidayreport.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            default :
                $this->set('mode', '');
                $this->render('holidayreport');
                break;
        }
    }

    public function leavedaysreport() {
        $sessionid = $this->Session->read('emp_fkey');
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        // debug($sessionid);
        $arr_leavedays = $this->EmployeeLeaveTransaction->query("select leave_date,leave_session, Leavestatus, Remarks  from  emp_leave_transactions 
                           where   Leavestatus in('Applied','Authorized','Approved','Cancelled')
                           and LEAVEENTRYID in(select LEAVEENTRYID from leaveentries where EMP_fkey=$sessionid
                           and Leavestatus in('Applied','Authorized','Approved','Cancelled'))");
        //    debug($arr_leavedays);
        $this->set('arr_leavedays', $arr_leavedays);
    }

    public function leavedetailsreport($mode = '') {
        $sessionid = $this->Session->read('emp_fkey');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_leave_details = $this->EmployeeDetails->query("select applied_date, LEAVESTATUS, FROMDATE, FROMHALF, TODATE, TOHALF, ISAutherized,`LeaveType`.`item`, 
                                                      (select concat(first_name,' ',middile_name,' ',last_name) from emp_details where emp_pkey=ISAutherizedby) 
                                                      Autherizedby, Autherized_date ,ISAPPROVED,
                                                      (select concat(first_name,' ',middile_name,' ',last_name) from emp_details where emp_pkey=APPROVEDBY) 
                                                      APPROVEDBY, APPROVED_date, Reason, contact_No, contact_person ,REMARKS ,leave_days, message
                                                      from leaveentries LEFT JOIN `salary_head_items` AS `LeaveType` ON (`leaveentries`.`salary_head_item_fkey` = `LeaveType`.`salary_head_item_pkey`) where EMP_fkey=$sessionid 
                                                      and Leavestatus  in('Applied','Authorized','Approved','Cancelled','Rejected','CancellationOfAuthorized','Cancellation Approved','Cancellation Authorized')
                                                     ");


         // debug($arr_leave_details);
        $this->set('arr_leave_details', $arr_leave_details);
        $arr_emp = $this->getempdetails($sessionid);
        $this->set('arr_emp', $arr_emp);
        if ($mode == 'pdf') {
            $this->set('mode', 'pdf');
            $view = new View($this, false);
            $view_output = $view->render('leavedetailsreportpdf');
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

            $html2pdf = new HTML2PDF('P', 'A2', 'en');
            //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
            //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($view_output);
            $html2pdf->Output('leavedetailsreport.pdf', 'D');
        } else {
            $this->set('mode', '');
            $this->render('leavedetailsreport');
        }
    }

    public function shiftpolicyreport($mode = '') {
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $sessionid = $this->Session->read('emp_fkey');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $arr_emp = $this->getempdetails($sessionid);
        $arr_shiftreport = $this->DayTimeProcedures->query("select * from working_day_time_procedures where day_time_seq in(select day_time_seq from emp_proff where emp_fkey =$sessionid)");
        $shiftallowance = ($arr_shiftreport['0']['working_day_time_procedures']['shift_allowance'] && $arr_shiftreport['0']['working_day_time_procedures']['shift_allowance'] != '') ? $arr_shiftreport['0']['working_day_time_procedures']['shift_allowance'] : '0';
              //  ($arr_shiftreport['0']['working_day_time_procedures']['shift_allowance']) ? $arr_shiftreport['0']['working_day_time_procedures']['shift_allowance'] : '0';
        $arr_otcomp = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $shiftallowance");
        //debug($arr_shiftreport);
        $otcomponent = ($arr_shiftreport['0']['working_day_time_procedures']['otcomponents'] && $arr_shiftreport['0']['working_day_time_procedures']['otcomponents'] != '') ? $arr_shiftreport['0']['working_day_time_procedures']['otcomponents'] : '0';   
       
        $arr_otcomponent = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $otcomponent");
        
        $this->set('arr_shiftreport', $arr_shiftreport);
        $this->set('arr_emp', $arr_emp);
        $this->set('arr_otcomp', $arr_otcomp);
        $this->set('arr_otcomponent', $arr_otcomponent);
//debug($otcomponent);
//debug($arr_otcomponent);
        // echo $arr_emp['designation']['desig_name'];die();
        switch ($mode) {
            case 'pdf' :
                //echo "entered in";die();
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('shiftpolicyreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('shiftpolicyreport.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel' :

                break;
            default :
                $this->set('mode', '');
                $this->render('shiftpolicyreport');
                break;
        }
    }

    public function getempdetails($id = '') {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');


        $fields = 'designation.desig_name,emp_pkey,address,city,state,pincode,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'designation',
                'alias' => 'designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = designation.desig_code')
            )
        );
        $conditions = array('EmployeeDetails.status' => 1, 'EmployeeDetails.emp_pkey="' . $id . '"');
        $arr_emp = $this->EmployeeDetails->find("first", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));
        return $arr_emp;
    }

    public function attendencereportpdf($month = '') {

        $first = date('Y-m-d', strtotime($month));
        $last = date('Y-m-t', strtotime($month));
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $sessionid = $this->Session->read('emp_fkey');
        $arr_emp = $this->getempdetails($sessionid);
        //debug($sessionid);
        $resp_att = array();
        $resp_att["rows"] = array();
        $arr_attendancereport = $this->DeviceAttendance->query("SELECT `DeviceAttendance`.LOGDATE,`DeviceAttendance`.C1,`DeviceAttendance`.C2,`DeviceAttendance`.C3,`EmployeeDetails`.first_name,`EmployeeDetails`.last_name,`department`.`dept_name`,`empproff`.emp_dept "
                . "FROM `device_attandance` AS `DeviceAttendance` "
                . "LEFT JOIN `emp_details` AS `EmployeeDetails`"
                . " ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`) "
                . "LEFT JOIN `emp_proff` AS `empproff` ON (`empproff`.`emp_fkey` = `EmployeeDetails`.`emp_pkey`)"
                . " LEFT JOIN `department` AS `department` ON (`department`.`dept_code` = `empproff`.`emp_dept`) "
                . "WHERE `EmployeeDetails`.`emp_pkey` ='$sessionid' and  `DeviceAttendance`.`LOGDATE` between '$first' and '$last'"
                . "ORDER BY  `DeviceAttendance`.`LOGDATE`,`EmployeeDetails`.`emp_id`,c1");

        //  debug($arr_attendancereport);
        $out = array();
        foreach ($arr_attendancereport as $key => $value) {
            $out['C1'] = isset($value['DeviceAttendance']['C1']) ? $value['DeviceAttendance']['C1'] : '';
            $resp_att["rows"][$key] = $out;
            $out['C2'] = isset($value['DeviceAttendance']['C2']) ? $value['DeviceAttendance']['C2'] : '';
            $resp_att["rows"][$key] = $out;
            $out['C3'] = isset($value['DeviceAttendance']['C3']) ? $value['DeviceAttendance']['C3'] : '';
            $resp_att["rows"][$key] = $out;
            $out['LOGDATE'] = isset($value['DeviceAttendance']['LOGDATE']) ? $value['DeviceAttendance']['LOGDATE'] : '';
            $resp_att["rows"][$key] = $out;
        }
        $this->set('arr_emp', $arr_emp);
        $this->set('arr_data', $resp_att);
        //debug($resp_att);
        $this->set('month', $month);
        $this->set('mode', 'pdf');
        $view = new View($this, false);
        $view_output = $view->render('attendencereportpdf');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($view_output);
        $html2pdf->Output('attendencereport.pdf', 'D');
        //$this->render('reportshiftpolicy');                
    }

    public function leavereportpdf($month = '') {

        $first = date('Y-m-d', strtotime($month));
        $last = date('Y-m-t', strtotime($month));
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $sessionid = $this->Session->read('emp_fkey');
        $arr_emp = $this->getempdetails($sessionid);
        //debug($sessionid);
        $resp_att = array();
        $resp_att["rows"] = array();
        $arr_attendancereport = $this->EmployeeLeaveTransaction->query("select leave_date,leave_session, Leavestatus, Remarks  from  emp_leave_transactions 
                           where   Leavestatus in('Applied','Authorized','Approved','Cancelled')
                         
and LEAVEENTRYID in(select LEAVEENTRYID from leaveentries where EMP_fkey='$sessionid'  and  `leave_date` between '$first' and '$last')"
                . "and Leavestatus in('Applied','Authorized','Approved','Cancelled')");

        // debug($arr_attendancereport);
        $out = array();
        // debug($out);

        foreach ($arr_attendancereport as $key => $value) {
            // debug($value);
            $out['leave_date'] = isset($value['emp_leave_transactions']['leave_date']) ? $value['emp_leave_transactions']['leave_date'] : '';
            $resp_att["rows"][$key] = $out;
            $out['Leavestatus'] = isset($value['emp_leave_transactions']['Leavestatus']) ? $value['emp_leave_transactions']['Leavestatus'] : '';
            $resp_att["rows"][$key] = $out;
            if ($value['emp_leave_transactions']['leave_session'] == 3) {
                $sess = "Full Day Leave";
            } elseif ($value['emp_leave_transactions']['leave_session'] == 2) {
                $sess = "Second Half ";
            } else {
                $sess = "First Half ";
            }
            $out['leave_session'] = isset($sess) ? $sess : '';
            $resp_att["rows"][$key] = $out;
            $out['Remarks'] = isset($value['emp_leave_transactions']['Remarks']) ? $value['emp_leave_transactions']['Remarks'] : '';
            $resp_att["rows"][$key] = $out;


            $resp_att["rows"][$key] = $out;
        }
        $this->set('arr_emp', $arr_emp);
        $this->set('arr_data', $resp_att);
        //debug($resp_att);
        $this->set('month', $month);
        $this->set('mode', 'pdf');
        $view = new View($this, false);
        $view_output = $view->render('leavereportpdf');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($view_output);
        $html2pdf->Output('leavereport.pdf', 'D');
        //$this->render('reportshiftpolicy');                
    }
  // Report For Customer Visists Added By nimisha 18/06/2019 start  
    public function CustomerVisits(){
        $arr_form_data = $this->request->data;   
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        
    }
    

    public function loadcustomer() {
        
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $emp_fkey = $this->Session->read('emp_fkey');
         $from_dates = isset($arr_form_data['from_dates']) ? $arr_form_data['from_dates']. ' 00:00' : '';
        $to_dates = isset($arr_form_data['to_dates']) ? $arr_form_data['to_dates']. ' 23:59' : '';
        $report_month = $from_dates.' - '.$to_dates;
        if ($emp_fkey == 0) {
            echo "NO Emp";
            return false;
        }
        $empinfo = $this->MobileUserauditor->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
        $this->set("empinfo", $empinfo);
        $arr_location_updates = $this->MobileUserauditor->query("SELECT EmpName , EMP_PKEY,employee_id, customer_name ,location ,branch,designation,department,purpose, INTIME, 
                                                        INVAL, (CASE WHEN INVAL = 'in' AND OUTVAL = 'in' THEN NULL ELSE OUTTIME END) AS OUTTIME ,
                                                        OUTVAL FROM (SELECT MO.C1 AS OUTVAL,MO.EMP_PKEY,MO.purpose,MO.employee_id,MO.branch,
                                                        MO.designation,MO.department,MO.EmpName ,MO.customer_name ,MO.location , MO.created_time AS OUTTIME,
                                                        @S AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)' ,
                                                        @LOGIN := @S AS INTIME, @C AS 'LEAD(c1) over(PARTITION BY emp_pkey ORDER BY logdate)',
                                                        @INVAL := @C AS INVAL,(CASE WHEN @PARTITION_BY_COLUMN = customer_name OR 
                                                        @_SEASON IS NULL THEN @S := created_time
                                                        END) TMP_VALUE, (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C := C1
                                                        END) TMP_VALUE1,(@PARTITION_BY_COLUMN := customer_name) PARTITION_COLUMN 
                                                        FROM 
                                                        (SELECT ed.emp_pkey,ed.branch,ed.designation,ed.department,ed.employee_id, ed.EmpName, att.created_time,
                                                        att.purpose,customer_name ,location , lcase(att.stepinout) c1
                                                        FROM (SELECT @PARTITION_BY_COLUMN = NULL, @S := NULL, @C := NULL, @LOGIN := NULL, @INVAL := NULL ) 
                                                        vars, mob_user_locations att, employee_info ed where att.user_id= ed.employee_id
                                                        and ed.emp_pkey = $emp_fkey and date_format(att.created_time,'%Y-%m-%d %T') 
                                                        BETWEEN DATE_FORMAT('$from_dates', '%Y-%m-%d %T')
                                                        AND DATE_FORMAT('$to_dates', '%Y-%m-%d %T')
                                                        order by created_time ) MO) XX
                                                        WHERE INVAL = 'in' AND OUTVAL IN ('out' )");
        
        $arr_resp = array(
                'data' => array()
            );      
            $i = 1;
            foreach ($arr_location_updates as $key => $att) {
//                debug($att);
                $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
                $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["XX"]['customer_name']) ? $att["XX"]['customer_name'] : '';
                $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["XX"]['purpose']) ? $att["XX"]['purpose'] : '';
                $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["XX"]['location']) ? $att["XX"]['location'] : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['INTIME']) ? $att["XX"]['INTIME'] : '';
                $arr_resp['data'][$i][]/* ['Location'] */ = isset($att["0"]['OUTTIME']) ? $att["0"]['OUTTIME'] : '';
                $t1 = strtotime($att['XX']['INTIME']);
                $t2 = strtotime($att['0']['OUTTIME']);
                $delta_T = ($t2 - $t1);
                $minutes = round(((($delta_T % 604800) % 86400) % 3600) / 60); 
                $sec = round((((($delta_T % 604800) % 86400) % 3600) % 60));
                $arr_resp['data'][$i][]/* ['Location'] */ =  $minutes." Min  " .$sec ." Sec";
                
                $i++;
                
            }
            $this->set('arr_leavepolicydetails_for_template', $arr_resp);
            $this->set('report_month', $report_month);
    }
    public function downloadpdf($mode,$from_dates = '',$to_dates ='') {
        //$this->autoRender = FALSE;
        $arr_request_data = $_REQUEST;
        // debug($arr_request_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
         $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
//         debug($from_dates);
//         debug($to_dates);
        $emp_fkey = $this->Session->read('emp_fkey');
//         $from_dates = isset($arr_request_data['from_dates']) ? $arr_request_data['from_dates']. ' 00:00' : '';
//        $to_dates = isset($arr_request_data['to_dates']) ? $arr_request_data['to_dates']. ' 23:59' : '';
        $report_month = $from_dates.' - '.$to_dates;
        $empinfo = $this->MobileUserauditor->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
        $this->set("empinfo", $empinfo);
        $arr_location_updates = $this->MobileUserauditor->query("SELECT EmpName , EMP_PKEY,employee_id, customer_name ,location ,branch,designation,department,purpose, INTIME, 
                                                        INVAL, (CASE WHEN INVAL = 'in' AND OUTVAL = 'in' THEN NULL ELSE OUTTIME END) AS OUTTIME ,
                                                        OUTVAL FROM (SELECT MO.C1 AS OUTVAL,MO.EMP_PKEY,MO.purpose,MO.employee_id,MO.branch,
                                                        MO.designation,MO.department,MO.EmpName ,MO.customer_name ,MO.location , MO.created_time AS OUTTIME,
                                                        @S AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)' ,
                                                        @LOGIN := @S AS INTIME, @C AS 'LEAD(c1) over(PARTITION BY emp_pkey ORDER BY logdate)',
                                                        @INVAL := @C AS INVAL,(CASE WHEN @PARTITION_BY_COLUMN = customer_name OR 
                                                        @_SEASON IS NULL THEN @S := created_time
                                                        END) TMP_VALUE, (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C := C1
                                                        END) TMP_VALUE1,(@PARTITION_BY_COLUMN := customer_name) PARTITION_COLUMN 
                                                        FROM 
                                                        (SELECT ed.emp_pkey,ed.branch,ed.designation,ed.department,ed.employee_id, ed.EmpName, att.created_time,
                                                        att.purpose,customer_name ,location , lcase(att.stepinout) c1
                                                        FROM (SELECT @PARTITION_BY_COLUMN = NULL, @S := NULL, @C := NULL, @LOGIN := NULL, @INVAL := NULL ) 
                                                        vars, mob_user_locations att, employee_info ed where att.user_id= ed.employee_id
                                                        and ed.emp_pkey = $emp_fkey and date_format(att.created_time,'%Y-%m-%d %T') 
                                                        BETWEEN DATE_FORMAT('$from_dates', '%Y-%m-%d %T')
                                                        AND DATE_FORMAT('$to_dates', '%Y-%m-%d %T')
                                                        order by created_time ) MO) XX
                                                        WHERE INVAL = 'in' AND OUTVAL IN ('out' )");
       
        
        $arr_resp = array(
                'data' => array()
            );      
            $i = 1;
            foreach ($arr_location_updates as $key => $att) {
//                debug($att);
                $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
                $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["XX"]['customer_name']) ? $att["XX"]['customer_name'] : '';
                $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["XX"]['purpose']) ? $att["XX"]['purpose'] : '';
                $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["XX"]['location']) ? $att["XX"]['location'] : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['INTIME']) ? $att["XX"]['INTIME'] : '';
                $arr_resp['data'][$i][]/* ['Location'] */ = isset($att["0"]['OUTTIME']) ? $att["0"]['OUTTIME'] : '';
                $t1 = strtotime($att['XX']['INTIME']);
                $t2 = strtotime($att['0']['OUTTIME']);
                $delta_T = ($t2 - $t1);
                $minutes = round(((($delta_T % 604800) % 86400) % 3600) / 60); 
                $sec = round((((($delta_T % 604800) % 86400) % 3600) % 60));
                $arr_resp['data'][$i][]/* ['Location'] */ =  $minutes." Min  " .$sec ." Sec";
                
                $i++;
                
            }
//            debug($arr_resp);
            $this->set('arr_leavepolicydetails_for_template', $arr_resp);
            $this->set('report_month', $report_month);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
//        if (isset($arr_leavepolicydetails_for_template) && !empty($arr_leavepolicydetails_for_template)) {
            //    ob_clean(); 
//            $this->set('arr_leavepolicydetails_for_template', $arr_resp);
//            $this->set('mode', 'pdf');
//            $view = new View($this, false);
//            $view_output = $view->render('downloadpdf');
//
//            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
//
//            $html2pdf = new HTML2PDF('P', 'A3', 'en');
//            $html2pdf->pdf->SetDisplayMode('fullpage');
//            $html2pdf->writeHTML($view_output);
//            $html2pdf->Output('Mobilelocation.pdf', 'D');
            // $this->render('downloadpdf');
            // ob_end_clean();
            
            $view = new View($this, false);
            $view_output = $view->render('downloadpdf');
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
                try
                    {
                    
                    
                   
                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('CustomerVisitsReport.pdf', 'D');

                     $this->render('downloadpdf');
                    
                    
                    
    //                        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    //                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
    //                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
    //                    $html2pdf->pdf->SetDisplayMode('fullpage');
    ////                        $html2pdf->pdf->SetDisplayMode('fullpage');
    //                        $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
    ////                        $html2pdf->writeHTML($content);
    //                        $html2pdf->writeHTML($view_output);
    //                        $html2pdf->Output('SalaryReport.pdf', 'D');
    //                        $this->render('downloadpdf');
                    }

                    catch(HTML2PDF_exception $e) {
                        echo $e;
                        exit;
                    }
            
//        }
    }
    
    // Customer Visits Report Ends
    
    // Attendance Location Report Added By nimisha 18/06/2019 starts 
    
    public function AttendanceLocation(){
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
    }
    public function loadattendacelocation(){
        $arr_form_data = $this->request->data;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
//        $company_code = $this->Session->read('company_code'); //company_code
      
        $emp_fkey = $this->Session->read('emp_fkey');
      
            $fromdate = isset($arr_form_data['from_dates']) ? $arr_form_data['from_dates']. ' 00:00' : '';
            $todate = isset($arr_form_data['to_dates']) ? $arr_form_data['to_dates']. ' 23:59' : '';
            $report_month = $fromdate.' - '.$todate;
            $this->set("report_month", $report_month);
            $data = array();    
             $empid = $this->DeviceAttendance->query("SELECT `emp_id` FROM `emp_details` WHERE `emp_pkey` = '$emp_fkey'");
//            debug($empid);
            $id = $empid['0']['emp_details']['emp_id'];
            $dates = $this->DeviceAttendance->query("SELECT distinct date_format(LOGDATE,'%Y-%m-%d') as ym   FROM device_attandance where `emp_id` = '$id' 
                                                    and cast(LOGDATE as date) BETWEEN '$fromdate' AND '$todate'");
            
            foreach ($dates as $value) {
//                    debug($value);
                $date = $value['0']['ym'];
//                debug($date);
               $data[] =  $this->DeviceAttendance->query("SELECT `Branch`.`branch_name`,`emp_detail_timeattandance`.`duration`, concat(EmployeeDetails.first_name,' ',
                                                ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,
                                                 `DeviceAttendance`.`C1`, `DeviceAttendance`.`LOGDATE`, if((DeviceAttendance.C3 in ('',NULL)),
                                                Branch.branch_name,DeviceAttendance.C3) AS Location FROM `device_attandance` AS `DeviceAttendance` 
                                                INNER JOIN `emp_details` AS `EmployeeDetails` ON (`EmployeeDetails`.`emp_id` = `DeviceAttendance`.`emp_id`) 
                                                LEFT JOIN `branches` AS `Branch` ON (`DeviceAttendance`.`branch_code` = `Branch`.`branch_code`) 
                                                LEFT JOIN `emp_detail_timeattandance` AS `emp_detail_timeattandance` 
                                                ON (`EmployeeDetails`.`emp_pkey` = `emp_detail_timeattandance`.`emp_pkey`) 
                                                WHERE LOGDATE BETWEEN '$date 00:00:00' and '$date 23:59:59'  AND `DeviceAttendance`.`status` >= 'Y'
                                                 AND `EmployeeDetails`.`emp_pkey` = ('$emp_fkey')  and `emp_detail_timeattandance`.`att_date` = '$date'
                                                 ORDER BY `DeviceAttendance`.`LOGDATE` ASC");
            }
            
//            debug($data);
//            $duration = $this->DeviceAttendance->query("SELECT `emp_pkey`, `att_date`, `duration` FROM `emp_detail_timeattandance`WHERE `emp_pkey` = '$emp_fkey' and att_date between '$fromdate' and '$todate'");
//            debug($duration);
//            debug($today_att);
            
            $arr_resp = array(
                'data' => array()
            );

            $i = 1;
            
//                debug($arr_resp);
            
            foreach ($data as $key => $val) {
                
                foreach ($val as  $att) {
//                    debug($att);
                $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
//                $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
                
                $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("Y-m-d", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
                $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
                $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
                $arr_resp['data'][$i][]/* ['Location'] */ = isset($att['emp_detail_timeattandance']['duration']) ? $att['emp_detail_timeattandance']['duration'] : '';
                
                
                $i++;
            }
            }
             $this->set('arr_leavepolicydetails_for_template', $arr_resp); 
              $empinfo = $this->DeviceAttendance->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
                $this->set("empinfo", $empinfo);
    
    }
    public function downloadattendancelocation($mode,$from_dates = '',$to_dates ='') {
        
    }
    
    // Attendance Location Reports Ends
    
    //Km travelled Report Added By Nimisha on 18/06/2019 starts
    
    public function KmTravelled(){
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
    }
    public function loadkmtravelled(){
        $arr_form_data = $this->request->data;
//        debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
         $emp_fkey = $this->Session->read('emp_fkey');
         $from_dates = isset($arr_form_data['from_dates']) ? $arr_form_data['from_dates']. ' 00:00' : '';
        $to_dates = isset($arr_form_data['to_dates']) ? $arr_form_data['to_dates']. ' 23:59' : '';
        $report_month = $from_dates.' - '.$to_dates;
        if ($emp_fkey == 0) {
            echo "NO Emp";
            return false;
        }
        $get_user_id = $this->MobileUserauditor->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';

        if ($user_id == '') {
            echo "NO user_id";
            return FALSE;
        }
        $empinfo = $this->MobileUserauditor->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
        $arr_location = $this->MobileUserauditor->query("SELECT user_id,created_time,latitude,longitude,location FROM `mob_user_locations` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates'  order by created_time ");
        
        $arr_location_dates = $this->MobileUserauditor->query("SELECT distinct date_format(created_time ,'%Y-%m-%d') as ym FROM mob_user_locations where `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' GROUP BY ym ");
//        debug($arr_location_dates);
        $lat2 = isset($arr_location['0']['mob_user_locations']['latitude']) ? $arr_location['0']['mob_user_locations']['latitude']:'';
        $lon2  = isset($arr_location['0']['mob_user_locations']['longitude'])?$arr_location['0']['mob_user_locations']['longitude']:'';
        $totaldistance = 0;
        foreach ($arr_location_dates as $val) {
//            debug($val);
            
                $ss = $val['0']['ym'];
                $dtt = new DateTime($ss);
                $date2 = $dtt->format('m-d-Y');
            foreach ($arr_location as $value) {
                
                $s = $value["mob_user_locations"]['created_time'];
                $dt = new DateTime($s);
                $date1 = $dt->format('m-d-Y');
//                
                
                if($date1 == $date2){
//                    debug($date2);
                    $lat1  = $value['mob_user_locations']['latitude'];
                    $lon1 = $value['mob_user_locations']['longitude'];
                    $theta = $lon1 - $lon2;
    
                                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                                    $dist = acos($dist);
                                    $dist = rad2deg($dist);
                                    $miles = $dist * 60 * 1.1515;
//                                    debug($miles);
                                    $kmdistance = $miles * 1.609344;
                                    $totaldistance = $totaldistance + $kmdistance;

                         $lat2 = $value['mob_user_locations']['latitude'];
                         $lon2  = $value['mob_user_locations']['longitude'];
//                         debug($totaldistance);
                }
                
            }
            $distance[] = array(
                'date' => $date2,
                'km' => round($totaldistance,2) 
            );
                  
        }
       
        
        $arr_resp = array(
                'data' => array()
            );
       
            
            $i = 1;
            if(!empty($distance)){
            foreach ($distance as $key => $att) {
//                debug($att);
                $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
                
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['date'];
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['km'];
                

                $i++;
            }
            }
        $this->set('arr_leavepolicydetails_for_template', $arr_resp);
        $this->set('empinfo', $empinfo);
        $this->set('report_month', $report_month);
    }
    public function downlaodkmtravelled($mode,$from_dates = '',$to_dates ='') {
//      $arr_form_data = $this->request->data;
//        debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
//        $from_dates = isset($arr_form_data['from_dates']) ? $arr_form_data['from_dates']. ' 00:00' : '';
//        $to_dates = isset($arr_form_data['to_dates']) ? $arr_form_data['to_dates']. ' 23:59' : '';
        $report_month = $from_dates.' - '.$to_dates;
        if ($emp_fkey == 0) {
            echo "NO Emp";
            return false;
        }
        $get_user_id = $this->MobileUserauditor->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';

        if ($user_id == '') {
            echo "NO user_id";
            return FALSE;
        }
        $empinfo = $this->MobileUserauditor->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
        $arr_location = $this->MobileUserauditor->query("SELECT user_id,created_time,latitude,longitude,location FROM `mob_user_locations` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates'  order by created_time ");
        
        $arr_location_dates = $this->MobileUserauditor->query("SELECT distinct date_format(created_time ,'%Y-%m-%d') as ym FROM mob_user_locations where `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' GROUP BY ym ");
//        debug($arr_location_dates);
        $lat2 = isset($arr_location['0']['mob_user_locations']['latitude']) ? $arr_location['0']['mob_user_locations']['latitude']:'';
        $lon2  = isset($arr_location['0']['mob_user_locations']['longitude'])?$arr_location['0']['mob_user_locations']['longitude']:'';
        $totaldistance = 0;
        foreach ($arr_location_dates as $val) {
//            debug($val);
            
                $ss = $val['0']['ym'];
                $dtt = new DateTime($ss);
                $date2 = $dtt->format('m-d-Y');
            foreach ($arr_location as $value) {
                
                $s = $value["mob_user_locations"]['created_time'];
                $dt = new DateTime($s);
                $date1 = $dt->format('m-d-Y');
//                
                
                if($date1 == $date2){
//                    debug($date2);
                    $lat1  = $value['mob_user_locations']['latitude'];
                    $lon1 = $value['mob_user_locations']['longitude'];
                    $theta = $lon1 - $lon2;
    
                                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                                    $dist = acos($dist);
                                    $dist = rad2deg($dist);
                                    $miles = $dist * 60 * 1.1515;
//                                    debug($miles);
                                    $kmdistance = $miles * 1.609344;
                                    $totaldistance = $totaldistance + $kmdistance;

                         $lat2 = $value['mob_user_locations']['latitude'];
                         $lon2  = $value['mob_user_locations']['longitude'];
//                         debug($totaldistance);
                }
                
            }
            $distance[] = array(
                'date' => $date2,
                'km' => round($totaldistance,2) 
            );
                  
        }
       
        
        $arr_resp = array(
                'data' => array()
            );
       
            
            $i = 1;
            if(!empty($distance)){
            foreach ($distance as $key => $att) {
//                debug($att);
                $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
                
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['date'];
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['km'];
                

                $i++;
            }
            }
        $this->set('arr_leavepolicydetails_for_template', $arr_resp);
      
//        debug($arr_resp);
        $this->set('empinfo', $empinfo);
        $this->set('report_month', $report_month);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

            $view = new View($this, false);
            $view_output = $view->render('downlaodkmtravelled');
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
                try
                    {
                    
                    
                   
                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('CustomerVisitsDistanceTravelledReportt.pdf', 'D');

                     $this->render('downlaodkmtravelled');
                    
                 $this->render('downloadpdf');
                    }

                    catch(HTML2PDF_exception $e) {
                        echo $e;
                        exit;
                    }
            
//        }
    }
    
   //Customer visits Distance Travelled Report  Ends
    
    
//    Mobile Tracking Distance Travelled report  addded By nimisha 18/06/2019 starts
    
    public function KmtravelledTracking(){
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
    }
    public function loadkmtravelledtracking(){
         $arr_form_data = $_REQUEST;
//        debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
         $emp_fkey = $this->Session->read('emp_fkey');
         $from_dates = isset($arr_form_data['from_dates']) ? $arr_form_data['from_dates']. ' 00:00' : '';
        $to_dates = isset($arr_form_data['to_dates']) ? $arr_form_data['to_dates']. ' 23:59' : '';
        $report_month = $from_dates.' - '.$to_dates;
         $this->set('report_month', $report_month);
        if ($emp_fkey == 0) {
            echo "NO Emp";
            return false;
        }
        $get_user_id = $this->MobileUserauditor->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';

        if ($user_id == '') {
            echo "NO user_id";
            return FALSE;
        }
        $empinfo = $this->MobileUserauditor->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
        $arr_location = $this->MobileUserauditor->query("SELECT user_id,created_time,latitude,longitude,location FROM `mob_user_tracking` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates'  order by created_time ");
        
        $arr_location_dates = $this->MobileUserauditor->query("SELECT distinct date_format(created_time ,'%Y-%m-%d') as ym FROM mob_user_tracking where `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' GROUP BY ym ");
//        debug($arr_location_dates);
        $lat2 = isset($arr_location['0']['mob_user_tracking']['latitude']) ? $arr_location['0']['mob_user_tracking']['latitude']:'';
        $lon2  = isset($arr_location['0']['mob_user_tracking']['longitude'])?$arr_location['0']['mob_user_tracking']['longitude']:'';
        $totaldistance = 0;
        foreach ($arr_location_dates as $val) {
//            debug($val);
            
                $ss = $val['0']['ym'];
                $dtt = new DateTime($ss);
                $date2 = $dtt->format('m-d-Y');
            foreach ($arr_location as $value) {
                
                $s = $value["mob_user_tracking"]['created_time'];
                $dt = new DateTime($s);
                $date1 = $dt->format('m-d-Y');
//                
                
                if($date1 == $date2){
//                    debug($date2);
                    $lat1  = $value['mob_user_tracking']['latitude'];
                    $lon1 = $value['mob_user_tracking']['longitude'];
                    $theta = $lon1 - $lon2;
    
                                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                                    $dist = acos($dist);
                                    $dist = rad2deg($dist);
                                    $miles = $dist * 60 * 1.1515;
//                                    debug($miles);
                                    $kmdistance = $miles * 1.609344;
                                    $totaldistance = $totaldistance + $kmdistance;

                         $lat2 = $value['mob_user_tracking']['latitude'];
                         $lon2  = $value['mob_user_tracking']['longitude'];
//                         debug($totaldistance);
                }
                
            }
            $distance[] = array(
                'date' => $date2,
                'km' => round($totaldistance,2) 
            );
                  
        }
       
        
        $arr_resp = array(
                'data' => array()
            );
       
            
            $i = 1;
            if(!empty($distance)){
            foreach ($distance as $key => $att) {
//                debug($att);
                $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
                
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['date'];
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['km'];
                

                $i++;
            }
            }
        $this->set('arr_leavepolicydetails_for_template', $arr_resp);
        $this->set('empinfo', $empinfo);
    }
    public function downloadkmtravelledtracking($mode,$from_dates = '',$to_dates ='') {
//      $arr_form_data = $this->request->data;
//        debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
//        $from_dates = isset($arr_form_data['from_dates']) ? $arr_form_data['from_dates']. ' 00:00' : '';
//        $to_dates = isset($arr_form_data['to_dates']) ? $arr_form_data['to_dates']. ' 23:59' : '';
        $report_month = $from_dates.' - '.$to_dates;
        if ($emp_fkey == 0) {
            echo "NO Emp";
            return false;
        }
        $get_user_id = $this->MobileUserauditor->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';

        if ($user_id == '') {
            echo "NO user_id";
            return FALSE;
        }
        $empinfo = $this->MobileUserauditor->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
        $arr_location = $this->MobileUserauditor->query("SELECT user_id,created_time,latitude,longitude,location FROM `mob_user_tracking` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates'  order by created_time ");
        
        $arr_location_dates = $this->MobileUserauditor->query("SELECT distinct date_format(created_time ,'%Y-%m-%d') as ym FROM mob_user_tracking where `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' GROUP BY ym ");
//        debug($arr_location_dates);
        $lat2 = isset($arr_location['0']['mob_user_tracking']['latitude']) ? $arr_location['0']['mob_user_tracking']['latitude']:'';
        $lon2  = isset($arr_location['0']['mob_user_tracking']['longitude'])?$arr_location['0']['mob_user_tracking']['longitude']:'';
        $totaldistance = 0;
        foreach ($arr_location_dates as $val) {
//            debug($val);
            
                $ss = $val['0']['ym'];
                $dtt = new DateTime($ss);
                $date2 = $dtt->format('m-d-Y');
            foreach ($arr_location as $value) {
                
                $s = $value["mob_user_tracking"]['created_time'];
                $dt = new DateTime($s);
                $date1 = $dt->format('m-d-Y');
//                
                
                if($date1 == $date2){
//                    debug($date2);
                    $lat1  = $value['mob_user_tracking']['latitude'];
                    $lon1 = $value['mob_user_tracking']['longitude'];
                    $theta = $lon1 - $lon2;
    
                                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                                    $dist = acos($dist);
                                    $dist = rad2deg($dist);
                                    $miles = $dist * 60 * 1.1515;
//                                    debug($miles);
                                    $kmdistance = $miles * 1.609344;
                                    $totaldistance = $totaldistance + $kmdistance;

                         $lat2 = $value['mob_user_tracking']['latitude'];
                         $lon2  = $value['mob_user_tracking']['longitude'];
//                         debug($totaldistance);
                }
                
            }
            $distance[] = array(
                'date' => $date2,
                'km' => round($totaldistance,2) 
            );
                  
        
                  
        }
       
        
        $arr_resp = array(
                'data' => array()
            );
       
            
            $i = 1;
            if(!empty($distance)){
            foreach ($distance as $key => $att) {
//                debug($att);
                $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
                
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['date'];
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['km'];
                

                $i++;
            }
            }
        $this->set('arr_leavepolicydetails_for_template', $arr_resp);
      
//        debug($arr_resp);
        $this->set('empinfo', $empinfo);
        $this->set('report_month', $report_month);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

            $view = new View($this, false);
            $view_output = $view->render('downloadkmtravelledtracking');
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
                try
                    {
                    
                    
                   
                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('MobileTrackingDistanceTravelledReport.pdf', 'D');

                     $this->render('downloadkmtravelledtracking');
                    
//                 $this->render('downloadpdf');
                    }

                    catch(HTML2PDF_exception $e) {
                        echo $e;
                        exit;
                    }
            
//        }
    }
    
    // ends
    
    
    
}
