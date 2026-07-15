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
ini_set('max_execution_time', 2000); 
ini_set('memory_limit', '1024M');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class SalarySlipReportsController extends AppController
{

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'SalarySlipReports';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'EmpCtcTransaction', 'EmployeeDetails');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index()
    {
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        $this->set('company_code', $company_code);
    }


    public function Reports($id = 0)
    {
        $date = $id;
        $emp = $this->Session->read('emp_fkey');
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $f = date('Y-m', strtotime($date));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);

        $this->set('mname1', $mname);
        $this->set('y1', $year);


        $arr_empleaverequests = $this->EmpCtcTransaction->query("select ectc.month_year,br.branch_name,ed.first_name,ed.middile_name,ed.last_name,"
            . "ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ep.joining_date,ed.classification ,ed.pf,ar.holiday_total "
            . "from emp_salary_slip as ectc "
            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                       left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       where ectc.head_operator = 'ADDITION' and desg.status = 1
                       and ectc.item_part = 'DIRECT' and ectc.emp_fkey = '$emp' and ectc.month_year ='$date' and ar.month_year= '$date' 
                       and ed.status = 1 and end_date_effective is null ");

        $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.* "
            . "from emp_salary_slip as ectc "
            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            where ectc.head_operator = 'Deduction' and ectc.emp_fkey = '$emp' 
                            and month_year ='$date' and ed.status = 1 and end_date_effective is null ");
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
            . "from payroll_master "
            . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
            . "left join department as d on(d.dept_code = ep.emp_dept) "
            . "left join designation as dd on (dd.desig_code = ep.designation)"
            . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
            . " and payroll_master.month_year ='$date' and dd.status = 1"
            . " and payroll_master.emp_fkey = '$emp' and payroll_master.action = 'Approved' ");


        $arr_check_payr_processed = $this->EmpCtcTransaction->query(" select * from payroll_master where payroll_master.month_year ='$date' and payroll_master.emp_fkey = '$emp' ");
        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails
        );
        $msgs = '';
        if (!empty($arr_check_payr_processed) && $arr_check_payr_processed['0']['payroll_master']['action'] != 'Approved') {
            $arr_salary_for_template = array();
            $msgs = 'Payroll Not Approved To show ';
        }
        if (empty($arr_check_payr_processed)) {
            $msgs = 'Payroll Not Processed for this month ';
        }
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $this->set('msgs', $msgs);
        $this->set('date', $date);
           //Edited by Akshay on 15-5-2024
        $company_code = strtoupper($this->Session->read('company_code'));
        if($company_code == 'DEMO' || $company_code == 'HRBL'){
            $this->autoRender = FALSE;
            $this->render('synthite_reports');
        }
        //End
    }

    public function SalarySlipdownload($id = 0)
    {

        $date = $id;
        $emp = $this->Session->read('emp_fkey');
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        $f = date('Y-m', strtotime($date));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);

        $this->set('mname1', $mname);
        $this->set('y1', $year);


        $arr_empleaverequests = $this->EmpCtcTransaction->query("select ectc.month_year,br.branch_name,ed.first_name,ed.middile_name,ed.last_name,"
                . "ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ep.joining_date,ed.classification ,ed.pf, ed.status,ar.holiday_total,ar.weekoff_total "
                . "from emp_salary_slip as ectc "
                . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                       left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and ectc.emp_fkey = '$emp'"
                . " and ectc.month_year ='$date' and ar.month_year= '$date' "
                . "and ed.status = 1 and desg.status = 1 "
                . "and end_date_effective is null ");

        $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
                . "from emp_salary_slip as ectc "
                . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            where ectc.head_operator = 'Deduction' and ectc.emp_fkey = '$emp' "
                . " and month_year ='$date' and ed.status = 1 and end_date_effective is null ");
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                . "from payroll_master "
                . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                . "left join department as d on(d.dept_code = ep.emp_dept) "
                . "left join designation as dd on (dd.desig_code = ep.designation)"
                . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                . " and payroll_master.month_year ='$date' and dd.status = 1"
                . " and payroll_master.emp_fkey = '$emp'  and payroll_master.action = 'Approved' ");

        $lop = $this->EmpCtcTransaction->query("SELECT month_year,emp_fkey,SUM(salary_rate) AS total_salary_amount,presant_total,leave_total,
                lop_total FROM emp_salary_slip AS ectc LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey WHERE head_operator = 'Addition' AND head_type != 'Manually'
                AND item_part = 'Direct' AND ectc.emp_fkey = '$emp' AND ectc.month_year = '$date' AND end_date_effective IS NULL");
        $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
                    left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
                    left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
                    left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
                    where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$emp' and date_format(termination.last_approved_working_date,'%Y-%m') = '$date'  group by emp_details.emp_pkey");
        $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails,
            'lopdeduction' => $lop,
            'settle' => $settle
        );
        if (empty($empdetails)) {
            $arr_salary_for_template = array();
        }
        $status = 0;
        $arr_check_payr_processed = $this->EmpCtcTransaction->query(" select * from payroll_master where payroll_master.month_year ='$date' and payroll_master.emp_fkey = '$emp' ");
        //        debug($arr_check_payr_processed);
        //        $arr_salary_for_template[] = array(
        //            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
        //            'summary' => $arr_empleaverequests,
        //            'withoutcomponent' => $salaryslipwithoutcomponents,
        //            'empdet' => $empdetails
        //        );
        $msgs = 'Payroll Approved ';
        if (!empty($arr_check_payr_processed) && $arr_check_payr_processed['0']['payroll_master']['action'] != 'Approved') {
            $arr_salary_for_template = array();
            $status = 1;
            $msgs = 'Payroll Not Approved To show ';
        }
        if (empty($arr_check_payr_processed)) {
            $status = 2;
            $msgs = 'Payroll Not Processed for this month ';
        }

        $timestamp = strtotime($date);
        $monthYear = date("F Y", $timestamp);
        $this->set('monthYear', $monthYear);
        $this->set('msgs', $msgs);
        $this->set('status', $status);

        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        // debug($arr_salary_for_template);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $arr_emp = $this->getempdetails($emp);
        $this->set('arr_emp', $arr_emp);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        // debug($arr_comp_contact_info);
        $this->set('mode', 'pdf');
        $view = new View($this, false);

        if($company_code == 'DRRC'){
            $view_output = $view->render('slipthirdversion');
        }else if($company_code == 'DJOC'){
            $view_output = $view->render('slipfirstversion');
        }
        else if($company_code == 'DJIC' || $company_code == 'DEMO'){
            $view_output = $view->render('slipsecondversion');
        }
        else if($company_code == 'DEMO' || $company_code == 'HRBL'){ 
            $view_output = $view->render('synthite_salary_slipdownload');
        }
        else{
            $view_output = $view->render('salary_slipdownload');
        }
        
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $view_output = mb_convert_encoding($view_output, 'UTF-8', 'auto');
        $html2pdf->writeHTML($view_output);
        $html2pdf->Output('SalarySlip.pdf', 'D');
    }

    public function SalarySlipdownloadpdf($id = 0)
    {

        $date = $id;
        $emp = $this->Session->read('emp_fkey');
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $f = date('Y-m', strtotime($date));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);

        $this->set('mname1', $mname);
        $this->set('y1', $year);


        $arr_empleaverequests = $this->EmpCtcTransaction->query("select ectc.month_year,br.branch_name,ed.first_name,ed.middile_name,ed.last_name,"
            . "ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ep.joining_date,ed.classification ,ed.pf,ar.holiday_total "
            . "from emp_salary_slip as ectc "
            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                       left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       
                                          where ectc.head_operator = 'ADDITION' 
                                          and ectc.item_part = 'DIRECT' 
                                          and ectc.emp_fkey = '$emp'"
            . " and ectc.month_year ='$date' and ar.month_year= '$date' "
            . "and ed.status = 1  "
            . "and end_date_effective is null ");

        //                   }
        $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
            . "from emp_salary_slip as ectc "
            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$emp' "
            . " and month_year ='$date' "
            . "and ed.status = 1 and "
            . "end_date_effective is null ");
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
            . "from payroll_master "
            . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
            . "left join department as d on(d.dept_code = ep.emp_dept) "
            . "left join designation as dd on (dd.desig_code = ep.designation)"
            . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
            . " and payroll_master.month_year ='$date'"
            . " and payroll_master.emp_fkey = '$emp'  and payroll_master.action = 'Approved' ");


        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails
        );
        if (empty($empdetails)) {
            $arr_salary_for_template = array();
        }
        $status = 0;
        $arr_check_payr_processed = $this->EmpCtcTransaction->query(" select * from payroll_master where payroll_master.month_year ='$date' and payroll_master.emp_fkey = '$emp' ");

        $msgs = 'Payroll Approved ';
        if (!empty($arr_check_payr_processed) && $arr_check_payr_processed['0']['payroll_master']['action'] != 'Approved') {
            $arr_salary_for_template = array();
            $status = 1;
            $msgs = 'Payroll Not Approved To show ';
        }
        if (empty($arr_check_payr_processed)) {
            $status = 2;
            $msgs = 'Payroll Not Processed for this month ';
        }
        $this->set('msgs', $msgs);
        $this->set('status', $status);

        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        // debug($arr_salary_for_template);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $arr_emp = $this->getempdetails($emp);
        $this->set('arr_emp', $arr_emp);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        // debug($arr_comp_contact_info);
        $this->set('mode', 'pdf');
        $view = new View($this, false);
        $view_output = $view->render('salary_slipdownloadpdf');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

        $html2pdf = new HTML2PDF('P', 'Legal', 'en');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($view_output);
        $html2pdf->Output('SalarySlip.pdf', 'D');
    }

    
     //Edited by Akshay on 4-10-2023
    public function slipsecondversion($id = 0)
    {   
        $company_code = $this->Session->read('company_code');
        if($company_code == 'DEMO' || $company_code == 'DJIC'){
            
                $date = $id;
                $emp = $this->Session->read('emp_fkey');
                $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
                $company_code = $this->Session->read('company_code');
        
                $condition = "and ed.status = '1'  ";
                //        debug($arr_form_data);
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $condition = "and ed.status in ('1','2') ";
                        }
        
                //debug($arr_salary_for_template);
                $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct ectc.month_year,br.branch_name,ed.first_name,"
                . "ed.middile_name,ed.last_name,ed.status,ectc.payroll_master_fkey,ectc.head_operator,ectc.head_type,ectc.item_part,ectc.salary_head_item_desc,
                ectc.structure_det_value,ectc.salary_amount,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.pf,ed.classification,"
                        . "ed.ifsc_code,ed.esi,ep.joining_date,ed.status,ar.weekoff_total,ar.holiday_total,payroll_master.days_leave "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey = '$emp' "
                        . "and ectc.month_year ='$date' "
                        . $condition
                        . "and end_date_effective is null and payroll_master.action in ('Approved','Processed') and ar.isdelete= 'N' and ar.month_year= '$date' order by salhead.salary_head_item_order1,ed.first_name ");
        
                //                   }
                $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.* "
                    . "from emp_salary_slip as ectc "
                    . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                                    left join branches as br on (br.branch_code = ep.emp_branch)
                                    
                                                  where ectc.head_operator = 'Deduction' 
                                                  and ectc.emp_fkey = '$emp' "
                    . " and month_year ='$date' "
                    . "and ed.status = 1 and "
                    . "end_date_effective is null ");
                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                    . "from payroll_master "
                    . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                    . "left join department as d on(d.dept_code = ep.emp_dept) "
                    . "left join designation as dd on (dd.desig_code = ep.designation)"
                    . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                    . " and payroll_master.month_year ='$date'"
                    . " and payroll_master.emp_fkey = '$emp' and payroll_master.action = 'Approved' ");
        
        
                // debug($empdetails);
        
                $arr_check_payr_processed = $this->EmpCtcTransaction->query(" select * from payroll_master where payroll_master.month_year ='$date' and payroll_master.emp_fkey = '$emp' ");
        
                $lop = $this->EmpCtcTransaction->query("SELECT month_year,emp_fkey,SUM(salary_rate) AS total_salary_amount,presant_total,leave_total,
                            lop_total FROM emp_salary_slip AS ectc LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey WHERE head_operator = 'Addition' AND head_type != 'Manually'
                            AND item_part = 'Direct' AND ectc.emp_fkey = '$emp' AND ectc.month_year = '$date' AND end_date_effective IS NULL");
                $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
                                left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
                                left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
                                left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
                                where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$emp' and date_format(termination.last_approved_working_date,'%Y-%m') = '$date'  group by emp_details.emp_pkey");
                $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
                $arr_salary_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_empleaverequests,
                    'withoutcomponent' => $salaryslipwithoutcomponents,
                    'empdet' => $empdetails,
                    'lopdeduction' => $lop,
                    'settle' => $settle
                );
                $msgs = ""; //'Payroll Not Processed ';
                if (!empty($arr_check_payr_processed) && $arr_check_payr_processed['0']['payroll_master']['action'] != 'Approved') {
                    $arr_salary_for_template = array();
                    $msgs = 'Payroll Not Approved For Selected Month ';
                }
                if (empty($arr_check_payr_processed)) {
                    $msgs = 'Payroll Not Processed for this month ';
                }
                $timestamp = strtotime($date);
                $monthYear = date("F Y", $timestamp);
                $this->set('arr_salary_for_template', $arr_salary_for_template);
                $this->set('msgs', $msgs);
                $this->set('date', $date);
                $this->set('monthYear', $monthYear);
                $this->set('mode', '');
            
        
        }
        else{ 
        $date = $id;
        $emp = $this->Session->read('emp_fkey');
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');



        //debug($arr_salary_for_template);
        $arr_empleaverequests = $this->EmpCtcTransaction->query("select ectc.month_year,br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ed.status, ed.classification,ep.joining_date "
            . "from emp_salary_slip as ectc "
            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       
                                          where ectc.head_operator = 'ADDITION' 
                                          and ectc.item_part = 'DIRECT' 
                                          and ectc.emp_fkey = '$emp'"
            . " and month_year ='$date' "
            . "and ed.status = 1  "
            . "and end_date_effective is null ");

        //                   }
        $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.* "
            . "from emp_salary_slip as ectc "
            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$emp' "
            . " and month_year ='$date' "
            . "and ed.status = 1 and "
            . "end_date_effective is null ");
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->EmpCtcTransaction->query("select ep.designation,tr.last_approved_working_date,user_credentials.user_id,ep.emp_dept,ep.emp_company_id,ep.joining_date,d.dept_name,dd.desig_name,payroll_master.calander_days,payroll_master.days_leave,payroll_master.working_days,payroll_master.loss_of_pay,payroll_master.days_presant "
            . "from payroll_master "
            . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
            . "left join department as d on(d.dept_code = ep.emp_dept) "
            . "left join designation as dd on (dd.desig_code = ep.designation)"
            . "left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey) "
            . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey) "
            . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
            . " and payroll_master.month_year ='$date'"
            . " and payroll_master.emp_fkey = '$emp' and payroll_master.action = 'Approved' ");


        // debug($empdetails);

        $arr_check_payr_processed = $this->EmpCtcTransaction->query(" select * from payroll_master where payroll_master.month_year ='$date' and payroll_master.emp_fkey = '$emp' ");

        $lop = $this->EmpCtcTransaction->query("SELECT month_year,emp_fkey,SUM(salary_rate) AS total_salary_amount,presant_total,leave_total,
                    lop_total FROM emp_salary_slip AS ectc LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey WHERE head_operator = 'Addition' AND head_type != 'Manually'
                    AND item_part = 'Direct' AND ectc.emp_fkey = '$emp' AND ectc.month_year = '$date' AND end_date_effective IS NULL");
        $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
                        left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
                        left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
                        left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
                        where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$emp' and date_format(termination.last_approved_working_date,'%Y-%m') = '$date'  group by emp_details.emp_pkey");
        $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails,
            'lopdeduction' => $lop,
            'settle' => $settle
        );
        $msgs = ""; //'Payroll Not Processed ';
        if (!empty($arr_check_payr_processed) && $arr_check_payr_processed['0']['payroll_master']['action'] != 'Approved') {
            $arr_salary_for_template = array();
            $msgs = 'Payroll Not Approved For Selected Month ';
        }
        if (empty($arr_check_payr_processed)) {
            $msgs = 'Payroll Not Processed for this month ';
        }
        $timestamp = strtotime($date);
        $monthYear = date("F Y", $timestamp);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $this->set('msgs', $msgs);
        $this->set('date', $date);
        $this->set('monthYear', $monthYear);
        $this->set('mode', '');
    }
    }

    public function SalarySlipdownloadSecondVersion($id = 0) 
    {
        $date = $id;
        $emp = $this->Session->read('emp_fkey');
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $f = date('Y-m', strtotime($date));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);

        $this->set('mname1', $mname);
        $this->set('y1', $year);


        $arr_empleaverequests = $this->EmpCtcTransaction->query("select ectc.month_year,br.branch_name,ed.first_name,ed.middile_name,ed.last_name,"
                . "ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ep.joining_date,ed.classification ,ed.pf,ar.holiday_total "
                . "from emp_salary_slip as ectc "
                . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                       left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                       left join branches as br on (br.branch_code = ep.emp_branch)
                        left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and ectc.emp_fkey = '$emp'"
                . " and ectc.month_year ='$date' and ar.month_year= '$date' "
                . "and ed.status = 1 and desg.status = 1 "
                . "and end_date_effective is null order by salhead.salary_head_item_order1");

        $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.last_name,ectc.* "
                . "from emp_salary_slip as ectc "
                . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            where ectc.head_operator = 'Deduction' and ectc.emp_fkey = '$emp' "
                . " and month_year ='$date' and ed.status = 1 and end_date_effective is null ");
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                . "from payroll_master "
                . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                . "left join department as d on(d.dept_code = ep.emp_dept) "
                . "left join designation as dd on (dd.desig_code = ep.designation)"
                . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                . " and payroll_master.month_year ='$date' and dd.status = 1"
                . " and payroll_master.emp_fkey = '$emp'  and payroll_master.action = 'Approved' ");


        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails
        );
        if (empty($empdetails)) {
            $arr_salary_for_template = array();
        }
        $status = 0;
        $arr_check_payr_processed = $this->EmpCtcTransaction->query(" select * from payroll_master where payroll_master.month_year ='$date' and payroll_master.emp_fkey = '$emp' ");
        //        debug($arr_check_payr_processed);
        //        $arr_salary_for_template[] = array(
        //            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
        //            'summary' => $arr_empleaverequests,
        //            'withoutcomponent' => $salaryslipwithoutcomponents,
        //            'empdet' => $empdetails
        //        );
        $msgs = 'Payroll Approved ';
        if (!empty($arr_check_payr_processed) && $arr_check_payr_processed['0']['payroll_master']['action'] != 'Approved') {
            $arr_salary_for_template = array();
            $status = 1;
            $msgs = 'Payroll Not Approved To show ';
        }
        if (empty($arr_check_payr_processed)) {
            $status = 2;
            $msgs = 'Payroll Not Processed for this month ';
        }
        $this->set('msgs', $msgs);
        $this->set('status', $status);

        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        // debug($arr_salary_for_template);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $arr_emp = $this->getempdetails($emp);
        $this->set('arr_emp', $arr_emp);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        // debug($arr_comp_contact_info);
        $this->set('mode', 'pdf');
        $view = new View($this, false);
        $view_output = $view->render('salary_slipdownload');
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($view_output);
        $html2pdf->Output('SalarySlip.pdf', 'D');
    }
    public function getempdetails($id = '')
    {
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
     public function slipthirdversion($id = 0)
    {   
        $company_code = $this->Session->read('company_code');
        if($company_code == 'DRRC'){
            {
                $date = $id;
                $emp = $this->Session->read('emp_fkey');
                $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
                $company_code = $this->Session->read('company_code');
        
                $condition = "and ed.status = '1'  ";
                //        debug($arr_form_data);
                        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
                            $condition = "and ed.status in ('1','2') ";
                        }
        
                //debug($arr_salary_for_template);
                $arr_empleaverequests = $this->EmpCtcTransaction->query("select distinct ectc.month_year,br.branch_name,ed.first_name,"
                . "ed.middile_name,ed.last_name,ed.status,ectc.payroll_master_fkey,ectc.head_operator,ectc.head_type,ectc.item_part,ectc.salary_head_item_desc,
                ectc.structure_det_value,ectc.salary_amount,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.pf,ed.classification,"
                        . "ed.ifsc_code,ed.esi,ep.joining_date,ed.status,ar.weekoff_total,ar.holiday_total,payroll_master.days_leave "
                        . "from emp_salary_slip as ectc "
                        . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                           left join attendance_register as ar on (ar.emp_fkey = ed.emp_pkey)
                           left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                           left join  payroll_master  on (payroll_master.payroll_master_pkey = ectc.payroll_master_fkey) 
                           left join branches as br on (br.branch_code = ep.emp_branch)
                           left join designation as desg on (desg.desig_code = ep.designation)
                           left join salary_head_items as salhead on (salhead.salary_head_item_pkey = ectc.salary_head_item_fkey)
                           where ectc.head_operator = 'ADDITION' and ectc.item_part = 'DIRECT' and round(ectc.salary_amount) != '0' 
                           and ectc.emp_fkey = '$emp' "
                        . "and ectc.month_year ='$date' "
                        . $condition
                        . "and end_date_effective is null and payroll_master.action in ('Approved','Processed') and ar.isdelete= 'N' and ar.month_year= '$date' order by salhead.salary_head_item_order1,ed.first_name ");
        
                //                   }
                $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.* "
                    . "from emp_salary_slip as ectc "
                    . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                                    left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                                    left join branches as br on (br.branch_code = ep.emp_branch)
                                    
                                                  where ectc.head_operator = 'Deduction' 
                                                  and ectc.emp_fkey = '$emp' "
                    . " and month_year ='$date' "
                    . "and ed.status = 1 and "
                    . "end_date_effective is null ");
                $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
                $empdetails = $this->EmpCtcTransaction->query("select ep.designation,ep.emp_dept,ep.emp_company_id,d.dept_name,dd.desig_name,payroll_master.* "
                    . "from payroll_master "
                    . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
                    . "left join department as d on(d.dept_code = ep.emp_dept) "
                    . "left join designation as dd on (dd.desig_code = ep.designation)"
                    . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
                    . " and payroll_master.month_year ='$date'"
                    . " and payroll_master.emp_fkey = '$emp' and payroll_master.action = 'Approved' ");
        
        
                // debug($empdetails);
        
                $arr_check_payr_processed = $this->EmpCtcTransaction->query(" select * from payroll_master where payroll_master.month_year ='$date' and payroll_master.emp_fkey = '$emp' ");
        
                $lop = $this->EmpCtcTransaction->query("SELECT month_year,emp_fkey,SUM(salary_rate) AS total_salary_amount,presant_total,leave_total,
                            lop_total FROM emp_salary_slip AS ectc LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey WHERE head_operator = 'Addition' AND head_type != 'Manually'
                            AND item_part = 'Direct' AND ectc.emp_fkey = '$emp' AND ectc.month_year = '$date' AND end_date_effective IS NULL");
                $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
                                left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
                                left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
                                left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
                                where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$emp' and date_format(termination.last_approved_working_date,'%Y-%m') = '$date'  group by emp_details.emp_pkey");
                $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
                $arr_salary_for_template[] = array(
                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                    'summary' => $arr_empleaverequests,
                    'withoutcomponent' => $salaryslipwithoutcomponents,
                    'empdet' => $empdetails,
                    'lopdeduction' => $lop,
                    'settle' => $settle
                );
                $msgs = ""; //'Payroll Not Processed ';
                if (!empty($arr_check_payr_processed) && $arr_check_payr_processed['0']['payroll_master']['action'] != 'Approved') {
                    $arr_salary_for_template = array();
                    $msgs = 'Payroll Not Approved For Selected Month ';
                }
                if (empty($arr_check_payr_processed)) {
                    $msgs = 'Payroll Not Processed for this month ';
                }
                $timestamp = strtotime($date);
                $monthYear = date("F Y", $timestamp);
                $this->set('arr_salary_for_template', $arr_salary_for_template);
                $this->set('msgs', $msgs);
                $this->set('date', $date);
                $this->set('monthYear', $monthYear);
                $this->set('mode', '');
            }
        
        }
        else{ 
        $date = $id;
        $emp = $this->Session->read('emp_fkey');
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');



        //debug($arr_salary_for_template);
        $arr_empleaverequests = $this->EmpCtcTransaction->query("select ectc.month_year,br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.*,ed.account_no,ed.company_pf,ed.bank_name,ed.branch_name,ed.ifsc_code,ed.esi,ed.status, ed.classification,ep.joining_date "
            . "from emp_salary_slip as ectc "
            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey) 
                       left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey) 
                    
                       left join branches as br on (br.branch_code = ep.emp_branch)
                       left join designation as desg on (desg.desig_code = ep.designation)
                       
                                          where ectc.head_operator = 'ADDITION' 
                                          and ectc.item_part = 'DIRECT' 
                                          and ectc.emp_fkey = '$emp'"
            . " and month_year ='$date' "
            . "and ed.status = 1  "
            . "and end_date_effective is null ");

        //                   }
        $salaryslipwithoutcomponents = $this->EmpCtcTransaction->query("select br.branch_name,ed.first_name,ed.middile_name,ed.last_name,ectc.* "
            . "from emp_salary_slip as ectc "
            . "left join  emp_details as ed on (ed.emp_pkey = ectc.emp_fkey)
                            left join emp_proff as ep on(ep.emp_fkey = ed.emp_pkey)
                            left join branches as br on (br.branch_code = ep.emp_branch)
                            
                                          where ectc.head_operator = 'Deduction' 
                                          and ectc.emp_fkey = '$emp' "
            . " and month_year ='$date' "
            . "and ed.status = 1 and "
            . "end_date_effective is null ");
        $payroll_pkey = isset($arr_empleaverequests['0']['ectc']['payroll_master_fkey']) ? $arr_empleaverequests['0']['ectc']['payroll_master_fkey'] : '';
        $empdetails = $this->EmpCtcTransaction->query("select ep.designation,tr.last_approved_working_date,user_credentials.user_id,ep.emp_dept,ep.emp_company_id,ep.joining_date,d.dept_name,dd.desig_name,payroll_master.calander_days,payroll_master.days_leave,payroll_master.working_days,payroll_master.loss_of_pay,payroll_master.days_presant "
            . "from payroll_master "
            . "left join emp_proff as ep on (ep.emp_fkey = payroll_master.emp_fkey) "
            . "left join department as d on(d.dept_code = ep.emp_dept) "
            . "left join designation as dd on (dd.desig_code = ep.designation)"
            . "left join termination as tr on (tr.emp_fkey = payroll_master.emp_fkey) "
            . "left join user_credentials as user_credentials on (user_credentials.emp_fkey = payroll_master.emp_fkey) "
            . " where payroll_master.payroll_master_pkey = '$payroll_pkey'"
            . " and payroll_master.month_year ='$date'"
            . " and payroll_master.emp_fkey = '$emp' and payroll_master.action = 'Approved' ");


        // debug($empdetails);

        $arr_check_payr_processed = $this->EmpCtcTransaction->query(" select * from payroll_master where payroll_master.month_year ='$date' and payroll_master.emp_fkey = '$emp' ");

        $lop = $this->EmpCtcTransaction->query("SELECT month_year,emp_fkey,SUM(salary_rate) AS total_salary_amount,presant_total,leave_total,
                    lop_total FROM emp_salary_slip AS ectc LEFT JOIN emp_details AS ed ON ed.emp_pkey = ectc.emp_fkey WHERE head_operator = 'Addition' AND head_type != 'Manually'
                    AND item_part = 'Direct' AND ectc.emp_fkey = '$emp' AND ectc.month_year = '$date' AND end_date_effective IS NULL");
        $arr_settle = $this->EmpCtcTransaction->query(" select sum(salary_amount),info.*,ectc.* from emp_settle_slip as ectc  
                        left join employee_info as info on (info.emp_pkey = ectc.emp_fkey)
                        left join emp_details as emp_details on (emp_details.emp_pkey = ectc.emp_fkey) 
                        left join termination as termination on (termination.emp_fkey = info.emp_pkey and termination.status=1) 
                        where ectc.status='Y' and ectc.approved = 'Y' and ectc.type!='SALARY' and emp_details.emp_pkey='$emp' and date_format(termination.last_approved_working_date,'%Y-%m') = '$date'  group by emp_details.emp_pkey");
        $settle = isset($arr_settle['0']['0']['sum(salary_amount)']) ? $arr_settle['0']['0']['sum(salary_amount)'] : 0;
        $arr_salary_for_template[] = array(
            //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
            'summary' => $arr_empleaverequests,
            'withoutcomponent' => $salaryslipwithoutcomponents,
            'empdet' => $empdetails,
            'lopdeduction' => $lop,
            'settle' => $settle
        );
        $msgs = ""; //'Payroll Not Processed ';
        if (!empty($arr_check_payr_processed) && $arr_check_payr_processed['0']['payroll_master']['action'] != 'Approved') {
            $arr_salary_for_template = array();
            $msgs = 'Payroll Not Approved For Selected Month ';
        }
        if (empty($arr_check_payr_processed)) {
            $msgs = 'Payroll Not Processed for this month ';
        }
        $timestamp = strtotime($date);
        $monthYear = date("F Y", $timestamp);
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $this->set('msgs', $msgs);
        $this->set('date', $date);
        $this->set('monthYear', $monthYear);
        $this->set('mode', '');
    }
    }
}
 