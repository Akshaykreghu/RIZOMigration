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
ini_set('max_execution_time', 30000);

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class SiteReportsController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'SiteReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Attendance', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Site', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'DbConfig', 'MobileUserauditor', 'CompanyContactInfo', 'Contacts', 'EmpSiteDetailsAttendance', 'SiteTransactions', 'Siteattendanceregister', 'Branches', 'EmployeeProfessionalDetails', 'Designation', 'ReportAudit'); //santhu
    public $components = array('MasterdataManagement');

    /* public $arr_employee_reportcriterias = array(
      'Departments' => 'belonging to a Department',
      'Grades' => 'belonging to a Grade',
      'Verticals' => 'belonging to a Vertical',
      'Units' => 'belonging to a Branch',
      'EmployeeDetails' => 'randomly, without criteria'
      );

      public $arr_employee_reportcriteria_fields = array(
      'Departments' => 'emp_dept',
      'Grades' => 'emp_grade',
      'Verticals' => 'emp_vertical',
      'Units' => 'emp_branch',
      'EmployeeDetails' => 'emp_pkey'
      ); */

    /*
     * HR Reports landing view
     */

    public function hrreports()
    {
        $company_code = strtoupper($this->Session->read('company_code'));
        if ($company_code == 'VGFS' || $company_code == 'VSFS') {
            //added by  on 24/02/2020 Billing report hided from employee view
            $user_group = $this->Session->read('user_group');
            if ($user_group == 2) {
                $arr_reporttypes = array(
                    'AssignmentAttendance' => 'Assignment Wise Reports',
                    'EmployeePayHours' => 'Employee Pay Hours Reports',
                    'SiteClockWise' => 'Clock Wise Attendance Reports',
                    // added by 
                    'RottaMaster' => 'Rota Master ',
                    'RottaMasternew' => 'Rota Master_New ',
                    'SiteRate' => 'Site Rate Reports',
                    'Customer' => 'Customer/Vendor Reports',
                );
            } else {
                $arr_reporttypes = array(
                    'AssignmentAttendance' => 'Assignment Wise Reports',
                    'EmployeePayHours' => 'Employee Pay Hours Reports',
                    'SiteClockWise' => 'Clock Wise Attendance Reports',
                    //added by amal
                    'ClientReport(Actual)' => 'Site Detailed Report (Actual Rate)',
                    //added by 
                    'ClientReportStandard' => 'Site Detailed Report (Standard Rate)',
                    'RottaMaster' => 'Rota Master ',
                    'RottaMasternew' => 'Rota Master_New ',
                    'SiteRate' => 'Site Rate Reports',
                    'Customer' => 'Customer/Vendor Reports',
                );
            }
        } else {
            $arr_reporttypes = array(
                'AssignmentAttendance' => 'Assignment Wise Reports',
                'EmployeePayHours' => 'Employee Pay Hours Reports',
                'SiteClockWise' => 'Clock Wise Attendance Reports',
                'ClientReport(Actual)' => 'Site Detailed Report (Actual Rate)',
                'ClientReportStandard' => 'Site Detailed Report (Standard Rate)',
                'RottaMaster' => 'Rota Master',
                'RottaMasternew' => 'Rota Master_New',
                'SiteRate' => 'Site Rate Reports',
                'Customer' => 'Customer/Vendor Reports',
            );
        }
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '')
    {
        $this->autoRender = FALSE;
        // debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'employee':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'AssignmentAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'SiteClockWise':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'EmployeePayHours':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'AttendanceRep':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'DetailedAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                    //santhu
                case 'TimeAttendance':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                    //Sanju
                case 'Dashboard':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'MobilelocationRep':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Overtime':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                    //added by 
                case 'ClientReportStandard':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                    //added by amal
                case 'ClientReport(Actual)':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                    //added by 
                case 'RottaMaster':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'RottaMasternew':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'SiteRate':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Customer':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                default:
                    echo "No criterias found";
                    break;
            }
            $this->render('showreport');
        } else {
            echo "No criterias found";
        }
    }

    /*
     * Add criterias
     */

    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '')
    {
        $this->autoRender = FALSE;
        if ($type != '' && $str_currentcriterias != '') {
            //$arr_currentcriterias = explode(',', $str_currentcriterias);
            //$arr_remainingcriterias = array_diff(array_flip($this->arr_employee_reportcriterias), $arr_currentcriterias);
            //$this->set('arr_remainingcriterias',array_flip($arr_remainingcriterias));
            $str_currentcriterias = "'" . str_replace(",", "','", $str_currentcriterias) . "'";
            $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type)))));
            $this->set('newindex', $newindex);
            $this->render('showcriteria');
        } else {
            return '';
        }
    }

    /*
     * Load criteria items
     */

    public function loadcriteriaitems($index, $str_criteria = '')
    {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            $model = ($model == 'Bank') ? 'Site' : $model;
            $model = ($model == 'Cash') ? 'Site' : $model;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Contacts') ? 'Customer/Vendor' : $model;
                $model = ($model == 'AccessSite') ? 'Site Manager' : $model;
                $this->set('criteria', $model);
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
    public function itemcriteria($branch = '')
    {

        $this->Site->useDbConfig = $this->Session->read('ds');
        $this->set('branch', $branch);
        $this->set('criteria', "Contacts");
        $arr_itemlist = $this->Site->query("select distinct contacts.company_name,contacts.contact_id,contacts.status from contacts left join site on (contacts.contact_id = site.contact_name) where site.branch_code='$branch' and contacts.status = '1' and relationship = 'Customer' order by company_name ASC ");
        $this->set('arr_itemlist', $arr_itemlist);
        $this->render('itemcriteria');
    }

    public function itemcriteriaSite($emp = '', $date = '')
    {
        $this->autoRender = FALSE;
        $this->set('employee', $emp);
        $this->set('criteria', "Site");
        $format_date = date('Y-m', strtotime($date));
        $this->set('site_report_date', $format_date);
        //        $conditions =  "and ed.emp_pkey = $emp ";
        //        
        //        $arr_itemlist = $this->Site->query("select ed.emp_pkey,site_attendance.site_fkey,site.site_id,site.site_name
        //        from site_attendance
        //        left join site on (site.site_pkey = site_attendance.site_fkey )
        //        left join site_transactions as site_t on (site_t.site_fkey = site.site_pkey )
        //        left join emp_details ed on(emp_pkey=site_attendance.emp_fkey)
        //        WHERE site_attendance.status=3 and site_attendance.site_fkey=site_t.site_fkey 
        //        and site_t.day_time_seq_fkey=site_attendance.day_time_seq_fkey
        //        and site_t.designation_id=site_attendance.designation_id
        //        and DATE_FORMAT(att_date ,'%Y-%m') = '".$format_date."' and '".$format_date."' between DATE_FORMAT(site_t.start_date_effective,'%Y-%m')
        //        and DATE_FORMAT(site_t.end_date_effective,'%Y-%m') 
        //        $conditions 
        //        group by site_attendance.site_fkey
        //        order by ed.emp_pkey");
        //        $this->set('arr_itemlist', $arr_itemlist);
        $this->render('itemcriteria');
    }

    public function itemcriterialist($branch = '')
    {
        $this->autoRender = false;
        $arr_criteriaItems = array();
        $this->Site->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->Site->query("select distinct contacts.company_name,contacts.contact_id,contacts.status from contacts left join site on (contacts.contact_id = site.contact_name) where site.branch_code='$branch' and contacts.status = '1' order by company_name ASC ");
        foreach ($arr_emp as $key => $value) {
            // debug($value);
            $arr_criteriaItems[$key]['text'] = $value['contacts']['company_name'];
            $arr_criteriaItems[$key]['key'] = $value['contacts']['contact_id'];
        }
        echo json_encode($arr_criteriaItems);
    }

    public function itemcriterialistSite($emp = '', $date = '')
    {
        $this->autoRender = false;
        $arr_criteriaItems = array();
        $this->Site->useDbConfig = $this->Session->read('ds');
        $format_date = date('Y-m', strtotime($date));
        $arr_emp = $this->Site->query("select site.site_pkey,site.site_id,site.site_name from  site
left join site_transactions as site_t on (site_t.site_fkey = site.site_pkey ) 
left join emp_details ed on(ed.emp_pkey=site.user_pkey) WHERE site.status=1 and site_t.status = 1 
and site.site_pkey=site_t.site_fkey  and
DATE_FORMAT(site_t.start_date_effective,'%Y-%m') <= '$format_date'
and DATE_FORMAT(site_t.end_date_effective,'%Y-%m')  >= '$format_date'
and site.user_pkey =$emp 
group by site.site_pkey
union 
select site.site_pkey,site.site_id,site.site_name from  site 
left join access_site on (access_site.site_fkey = site.site_pkey ) 
left join site_transactions as site_t on (site_t.site_fkey = site.site_pkey ) 
left join emp_details ed on(emp_pkey=access_site.emp_fkey) WHERE site.status=1 and site_t.status = 1 and access_site.status = 1
and site.site_pkey=site_t.site_fkey and
 DATE_FORMAT(site_t.start_date_effective,'%Y-%m') <= '$format_date'
 and DATE_FORMAT(site_t.end_date_effective,'%Y-%m') >= '$format_date'
and access_site.emp_fkey IN ($emp) 
group by site.site_pkey order by 3");
        foreach ($arr_emp as $key => $value) {
            $arr_criteriaItems[$key]['text'] = $value['0']['site_name'] . ' - ' . $value['0']['site_id'];
            $arr_criteriaItems[$key]['key'] = $value['0']['site_pkey'];
        }
        echo json_encode($arr_criteriaItems);
    }

    public function sitelists($sitemanager = '')
    {
        $this->Site->useDbConfig = $this->Session->read('ds');
        $arr_sitelist = $this->Site->query("select distinct site_pkey,site_name,site_id from site where user_pkey=" . $sitemanager . " and site.status=1   union
        select site_name,site_id from site left join access_site on (site.site_pkey = access_site.site_fkey)
        where access_site.emp_fkey=" . $sitemanager . " and access_site.status=1 and site.status=1 ");
        $this->set('arr_sitelist', $arr_sitelist);
    }

    public function listcriteriaitems($str_criteria = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds'); // Edited by Akshay on 29-1-2025
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        if ($model == 'AccessSite') {
            $model1 = 'EmployeeDetails';
        } else {
            $model1 = $model;
        }
        if (isset($model) && $model != '') {
            if (($model1 != 'Bank') && ($model1 != 'Cash')) {
                $this->{$model1}->useDbConfig = $this->Session->read('ds');
            }

            $arr_order = array();
            if ($model == 'Departments') {
                $arr_order = array("Departments.dept_name" => "ASC");
                $conditions = array("status" => 1);
            } else if ($model == 'Grades') {
                $arr_order = array("Grades.grade_name" => "ASC");
                $conditions = array("status" => 1);
            } else if ($model == 'Verticals') {
                $arr_order = array("Verticals.vertical_name" => "ASC");
                $conditions = array("status" => 1);
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    // Edited by Akshay on 29-1-2025
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions = array("Units.status" => 1, "branch_code" => $is_ho);
                        } else {
                            $conditions = array("Units.status" => 1);
                        }
                    } else {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $arr_order = array("Units.branch_name" => "ASC");
                        $conditions = array("Units.status" => 1, "branch_code" => $cur_emp_branch);
                    }
                    // End
                } else {
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1);
                }
            } else if ($model == 'DayTimeProcedures') {
                $arr_order = array("DayTimeProcedures.day_time_desc" => "ASC");
                $conditions = array("active" => 1);
            } else if ($model == 'LeavePolicyGroup') {
                $arr_order = array("LeavePolicyGroup.LEAVEPOLICY_GROUP_NAME" => "ASC");
                $conditions = array("status" => 1);
            } else if ($model == 'Contacts') {
                $arr_order = array("Contacts.company_name" => "ASC");
                // $conditions = array("relationship" => 'Vendor');
                $conditions = array();
            } else if ($model == 'Site') {
                //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->Site->useDbConfig = $this->Session->read('ds');
                    $temp_site_keys1 = $this->Site->query("select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1");
                    $temp_site_keys2 = $this->Site->query("select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and site_fkey not in (select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1) and status=1");
                    $site_key = array();
                    foreach ($temp_site_keys1 as $val) {


                        $site_key[] = $val['site']['site_pkey'];
                    }
                    foreach ($temp_site_keys2 as $val) {
                        $site_key[] = $val['access_site']['site_fkey'];
                    }
                    if (count($site_key) > 0) {
                        $conditions = " Site.status=1 and Site.site_pkey in (" . implode(',', $site_key) . ") ";
                    } else {
                        $conditions = " Site.status=1 ";
                    }
                    $join = array();
                    $fields = array("DISTINCT `Site`.`site_pkey`, `Site`.`organization_id`, `Site`.`site_id`, `Site`.`site_name`, `Site`.`location_id`, `Site`.`work_type_id`, `Site`.`user_pkey`, `Site`.`latitude`, `Site`.`longitude`, `Site`.`address`, `Site`.`special_remarks`, `Site`.`creation_date`, `Site`.`jurisdiction`, `Site`.`expected_starting_date`, `Site`.`contact_name`, `Site`.`expected_compleation_date`, `Site`.`customer_name`, `Site`.`customer_siteid`, `Site`.`customer_refno`, `Site`.`customer_po_number`, `Site`.`customer_contact`, `Site`.`po_expirydate`, `Site`.`allocated_fund`, `Site`.`active`, `Site`.`status`");
                    $arr_order = array("Site.site_name" => "ASC");
                    //                    $conditions = array("Site.status" => 1, "emp_details.branch_code" => $cur_emp_branch);
                    //                    $conditions = array("Site.status" => 1, "Site.user_pkey" => $cur_emp_key,"Site.site_pkey in (select site_fkey from access_site where emp_fkey=".$cur_emp_key." and status=1)");
                } else { //employee branch wise sorting ends here
                    $arr_order = array("Site.site_name" => "ASC");
                    $fields = array("DISTINCT `Site`.`site_pkey`, `Site`.`organization_id`, `Site`.`site_id`, `Site`.`site_name`, `Site`.`location_id`, `Site`.`work_type_id`, `Site`.`user_pkey`, `Site`.`latitude`, `Site`.`longitude`, `Site`.`address`, `Site`.`special_remarks`, `Site`.`creation_date`, `Site`.`jurisdiction`, `Site`.`expected_starting_date`, `Site`.`contact_name`, `Site`.`expected_compleation_date`, `Site`.`customer_name`, `Site`.`customer_siteid`, `Site`.`customer_refno`, `Site`.`customer_po_number`, `Site`.`customer_contact`, `Site`.`po_expirydate`, `Site`.`allocated_fund`, `Site`.`active`, `Site`.`status`");
                    $conditions = array("Site.status" => 1);
                    $join = array();
                    $fields = array();
                }
            } else if ($model == 'Bank') {
                //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->Site->useDbConfig = $this->Session->read('ds');
                    $temp_site_keys1 = $this->Site->query("select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1 and payment_mode=1");
                    $temp_site_keys2 = $this->Site->query("select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and site_fkey not in (select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1 and payment_mode=1 ) and status=1");
                    $site_key = array();
                    foreach ($temp_site_keys1 as $val) {


                        $site_key[] = $val['site']['site_pkey'];
                    }
                    foreach ($temp_site_keys2 as $val) {
                        $site_key[] = $val['access_site']['site_fkey'];
                    }
                    if (count($site_key) > 0) {
                        $conditions = " Site.status=1 and Site.site_pkey in (" . implode(',', $site_key) . ") ";
                    } else {
                        $conditions = " Site.status=1 ";
                    }
                    $join = array();
                    $fields = array("DISTINCT `Site`.`site_pkey`, `Site`.`organization_id`, `Site`.`site_id`, `Site`.`site_name`, `Site`.`location_id`, `Site`.`work_type_id`, `Site`.`user_pkey`, `Site`.`latitude`, `Site`.`longitude`, `Site`.`address`, `Site`.`special_remarks`, `Site`.`creation_date`, `Site`.`jurisdiction`, `Site`.`expected_starting_date`, `Site`.`contact_name`, `Site`.`expected_compleation_date`, `Site`.`customer_name`, `Site`.`customer_siteid`, `Site`.`customer_refno`, `Site`.`customer_po_number`, `Site`.`customer_contact`, `Site`.`po_expirydate`, `Site`.`allocated_fund`, `Site`.`active`, `Site`.`status`");
                    $arr_order = array("Site.site_name" => "ASC");
                    //                    $conditions = array("Site.status" => 1, "emp_details.branch_code" => $cur_emp_branch);
                    //                    $conditions = array("Site.status" => 1, "Site.user_pkey" => $cur_emp_key,"Site.site_pkey in (select site_fkey from access_site where emp_fkey=".$cur_emp_key." and status=1)");
                } else { //employee branch wise sorting ends here
                    $this->Site->useDbConfig = $this->Session->read('ds');
                    $arr_order = array("Site.site_name" => "ASC");
                    $fields = array("DISTINCT `Site`.`site_pkey`, `Site`.`organization_id`, `Site`.`site_id`, `Site`.`site_name`, `Site`.`location_id`, `Site`.`work_type_id`, `Site`.`user_pkey`, `Site`.`latitude`, `Site`.`longitude`, `Site`.`address`, `Site`.`special_remarks`, `Site`.`creation_date`, `Site`.`jurisdiction`, `Site`.`expected_starting_date`, `Site`.`contact_name`, `Site`.`expected_compleation_date`, `Site`.`customer_name`, `Site`.`customer_siteid`, `Site`.`customer_refno`, `Site`.`customer_po_number`, `Site`.`customer_contact`, `Site`.`po_expirydate`, `Site`.`allocated_fund`, `Site`.`active`, `Site`.`status`");
                    $temp_site_keys1 = $this->Site->query("select site_pkey from site where status=1 and payment_mode=1");
                    $site_key = array();
                    foreach ($temp_site_keys1 as $val) {


                        $site_key[] = $val['site']['site_pkey'];
                    }
                    if (count($site_key) > 0) {
                        $conditions = " Site.status=1 and Site.site_pkey in (" . implode(',', $site_key) . ") ";
                    } else {
                        $conditions = " Site.status=1 ";
                    }
                    //$conditions = array("Site.status" => 1);
                    $join = array();
                    $fields = array();
                }
            } else if ($model == 'Cash') {
                //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                $user_group = $this->Session->read('user_group');
                if ($user_group == 2) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->Site->useDbConfig = $this->Session->read('ds');
                    $temp_site_keys1 = $this->Site->query("select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1 and payment_mode=2");
                    $temp_site_keys2 = $this->Site->query("select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and site_fkey not in (select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1 and payment_mode=2) and status=1");
                    $site_key = array();
                    foreach ($temp_site_keys1 as $val) {


                        $site_key[] = $val['site']['site_pkey'];
                    }
                    foreach ($temp_site_keys2 as $val) {
                        $site_key[] = $val['access_site']['site_fkey'];
                    }
                    if (count($site_key) > 0) {
                        $conditions = " Site.status=1 and Site.site_pkey in (" . implode(',', $site_key) . ") ";
                    } else {
                        $conditions = " Site.status=1 ";
                    }
                    $join = array();
                    $fields = array("DISTINCT `Site`.`site_pkey`, `Site`.`organization_id`, `Site`.`site_id`, `Site`.`site_name`, `Site`.`location_id`, `Site`.`work_type_id`, `Site`.`user_pkey`, `Site`.`latitude`, `Site`.`longitude`, `Site`.`address`, `Site`.`special_remarks`, `Site`.`creation_date`, `Site`.`jurisdiction`, `Site`.`expected_starting_date`, `Site`.`contact_name`, `Site`.`expected_compleation_date`, `Site`.`customer_name`, `Site`.`customer_siteid`, `Site`.`customer_refno`, `Site`.`customer_po_number`, `Site`.`customer_contact`, `Site`.`po_expirydate`, `Site`.`allocated_fund`, `Site`.`active`, `Site`.`status`");
                    $arr_order = array("Site.site_name" => "ASC");
                    //                    $conditions = array("Site.status" => 1, "emp_details.branch_code" => $cur_emp_branch);
                    //                    $conditions = array("Site.status" => 1, "Site.user_pkey" => $cur_emp_key,"Site.site_pkey in (select site_fkey from access_site where emp_fkey=".$cur_emp_key." and status=1)");
                } else { //employee branch wise sorting ends here
                    $this->Site->useDbConfig = $this->Session->read('ds');
                    $arr_order = array("Site.site_name" => "ASC");
                    $fields = array("DISTINCT `Site`.`site_pkey`, `Site`.`organization_id`, `Site`.`site_id`, `Site`.`site_name`, `Site`.`location_id`, `Site`.`work_type_id`, `Site`.`user_pkey`, `Site`.`latitude`, `Site`.`longitude`, `Site`.`address`, `Site`.`special_remarks`, `Site`.`creation_date`, `Site`.`jurisdiction`, `Site`.`expected_starting_date`, `Site`.`contact_name`, `Site`.`expected_compleation_date`, `Site`.`customer_name`, `Site`.`customer_siteid`, `Site`.`customer_refno`, `Site`.`customer_po_number`, `Site`.`customer_contact`, `Site`.`po_expirydate`, `Site`.`allocated_fund`, `Site`.`active`, `Site`.`status`");
                    $temp_site_keys1 = $this->Site->query("select site_pkey from site where status=1 and payment_mode=2");
                    $site_key = array();
                    foreach ($temp_site_keys1 as $val) {


                        $site_key[] = $val['site']['site_pkey'];
                    }
                    if (count($site_key) > 0) {
                        $conditions = " Site.status=1 and Site.site_pkey in (" . implode(',', $site_key) . ") ";
                    } else {
                        $conditions = " Site.status=1 ";
                    }
                    //$conditions = array("Site.status" => 1);
                    $join = array();
                    $fields = array();
                }
            } else {
                // Edited by Akshay on 29-1-2025
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions = array("status" => 1, "branch_code" => $is_ho);
                    } else {
                        $conditions = array("status" => 1);
                    }
                } else {
                    $conditions = array("status" => 1);
                }
                // End
                $join = array();
            }

            if ($model == "Site") {
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "order" => $arr_order, "joins" => $join)));
            } else {
                if (($model == 'Bank') || ($model == 'Cash')) {
                    $model = 'Site';
                    $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "order" => $arr_order, "joins" => $join)));
                } else {
                    $arr_criteriaItemsDB = Set::extract('/' . $model1 . '/.', $this->{$model1}->find("all", array("conditions" => $conditions, "order" => $arr_order)));
                }
            }
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'Departments':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['dept_code'];
                        $arr_criteriaItems[$key]['text'] = $value['dept_name'];
                        $key++;
                    }
                    break;
                case 'Grades':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['grade_code'];
                        $arr_criteriaItems[$key]['text'] = $value['grade_name'];
                        $key++;
                    }
                    break;
                case 'Site':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['site_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['site_name'];
                        $key++;
                    }
                    break;
                    //                     case 'Bank':
                    //                    foreach ($arr_criteriaItemsDB as $key => $value) {
                    //                        $arr_criteriaItems[$key]['key'] = $value['site_pkey'];
                    //                        $arr_criteriaItems[$key]['text'] = $value['site_name'];
                    //                        $key++;
                    //                    }
                    //                    break;
                    //                     case 'Cash':
                    //                    foreach ($arr_criteriaItemsDB as $key => $value) {
                    //                        $arr_criteriaItems[$key]['key'] = $value['site_pkey'];
                    //                        $arr_criteriaItems[$key]['text'] = $value['site_name'];
                    //                        $key++;
                    //                    }
                    //                    break;
                case 'Units':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;
                case 'LeavePolicyGroup':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVEPOLICY_GROUP_ID'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVEPOLICY_GROUP_NAME'];
                        $key++;
                    }
                    break;
                case 'AccessSite':
                    $fields = 'EmployeeDetails.emp_pkey,status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name';
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                        )
                    );

                    $conditions = array();
                    $conditions[] = array("(EmployeeDetails.emp_pkey in (select user_pkey from site left join site_transactions on(site.site_pkey = site_transactions.site_fkey) where site.status = 1 and site_transactions.status=1) or EmployeeDetails.emp_pkey in (select emp_fkey from access_site left join site on (site.site_pkey = access_site.site_fkey) "
                        . " left join site_transactions on(site.site_pkey = site_transactions.site_fkey) where access_site.status = 1 and site.status = 1 and site_transactions.status=1))");
                    //$conditions2[] = array("EmployeeDetails.emp_pkey in (select emp_fkey from access_site where status = 1)");
                    $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions[] = array("EmployeeDetails.status in(1,2)");
                    } else {
                        $conditions[] = array("EmployeeDetails.status" => 1);
                    }

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2) {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                    }
                    //employee branch wise sorting ends here

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        'order' => $arr_order
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;
                case 'EmployeeDetails':
                    $fields = 'EmployeeDetails.emp_pkey,status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
                    $joins = array(
                        array(
                            'table' => 'emp_proff',
                            'alias' => 'EmployeeProfessionalDetails',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                        )
                    );

                    $conditions = array();

                    //                    $conditions[] = array("EmployeeDetails.emp_pkey in (select emp_pkey from emp_site_detail_timeattandance)");
                    $conditions[] = array("EmployeeDetails.emp_pkey in (select emp_fkey from site_attendance)");
                    $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {

                        $conditions[] = array("EmployeeDetails.status in(1,2)");
                    } else {


                        $conditions[] = array("EmployeeDetails.status" => 1);
                    }

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');

                    // Edited by Akshay on 12-2-2025
                    $current_emp_pkey = $this->Session->read('emp_fkey');
                    $company_code = $this->Session->read('company_code');
                    $branch_condition = "";
                    if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $arr_is_ho = $this->EmployeeDetails->query(
                            "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                            ['emp_pkey' => $current_emp_pkey]
                        );
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions[] = array("EmployeeDetails.branch_code" => $is_ho);
                        }
                    }else
                    // End

                    if ($user_group == 2) {
                        $cur_emp_key = $this->Session->read("emp_fkey");
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                        $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                        $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                    }
                    //employee branch wise sorting ends here

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        'order' => $arr_order
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;
                case 'Contacts':
                    $this->Contacts->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->Contacts->query("select company_name,contact_id,status from contacts where  status = '1' and relationship = 'Customer' order by company_name ASC");
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value['contacts']['company_name'];
                        $arr_criteriaItems[$key]['key'] = $value['contacts']['contact_id'];
                    }
                    break;
                case 'DayTimeProcedures':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['day_time_seq'];
                        $arr_criteriaItems[$key]['text'] = $value['day_time_desc'];
                        $key++;
                    }
                case 'attendance':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['day_time_seq'];
                        $arr_criteriaItems[$key]['text'] = $value['day_time_desc'];
                        $key++;
                    }
                    break;
            }
            if ($model1 == 'AccessSite') {
                $model = 'AccessSite';
            }
            echo json_encode($arr_criteriaItems);
        }
    }
    public function reportAudit($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By  on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'AssignmentAttendance':
                $dataForHistory['report_type'] = "Site Assignment Wise Attendance Report";
                break;
            case 'EmployeePayHours':
                $dataForHistory['report_type'] = "Employee Pay Hours Report ";
                break;
            case 'SiteClockWise':
                $dataForHistory['report_type'] = "Clock Wise Attendance Report";
                break;
            case 'ClientReport(Actual)':
                $dataForHistory['report_type'] = "Site Detailed Report (Actual Rate)";
                break;
            case 'ClientReportStandard':
                $dataForHistory['report_type'] = "Site Detailed Report (Standard Rate)";
                break;
            case 'RottaMaster':
                $dataForHistory['report_type'] = "Rota Master Report";
                break;
            case 'RottaMasternew':
                $dataForHistory['report_type'] = "Rota Master_New Report";
                break;
            case 'SiteRate':
                $dataForHistory['report_type'] = "Site Rate Report";
                break;
            case 'Customer':
                $dataForHistory['report_type'] = "Customer/Vendor Report";
                break;
        }

        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
        $dataForHistory['include_resigned'] = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : '';
        $dataForHistory['Include_negative_salary'] = isset($arr_form_data['ngtvsal']) ? $arr_form_data['ngtvsal'] : '';
        $criteria_count = isset($arr_form_data['hidden-criterias-count']) ? $arr_form_data['hidden-criterias-count'] : 1;
        $i = 1;
        $criteria_array = array();
        $criteria_name_array = array();
        $items_array = array();
        $items_count_array = array();
        while ($i <= $criteria_count) {
            $criteria = isset($arr_form_data['hidden-criteria' . $i]) ? $arr_form_data['hidden-criteria' . $i] : '';
            $criteria_array[] = $criteria;

            switch ($criteria) {
                case 'EmployeeDetails':
                    $criteria_name_array[] = 'belonging to an Employee';
                    break;
                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'Contacts':
                    $criteria_name_array[] = 'belonging to a Client';
                    break;
                case 'Site':
                    $criteria_name_array[] = 'belonging to a Site';
                    break;
                case 'AccessSite':
                    $criteria_name_array[] = 'belonging to a Site Manager';
                    break;
                case 'Bank':
                    $criteria_name_array[] = 'belonging to a Bank';
                    break;
                case 'Cash':
                    $criteria_name_array[] = 'belonging to Cash';
                    break;
                default:
                    break;
            }
            $items_array[] = isset($arr_form_data[$criteria]) ? implode(",", $arr_form_data[$criteria]) : '';
            $items_count_array[] = isset($arr_form_data[$criteria]) ? count($arr_form_data[$criteria]) : 0;

            $i++;
        }

        $dataForHistory['criteria'] = implode(",", $criteria_array);
        $dataForHistory['criteria_name'] = implode(",", $criteria_name_array);
        $dataForHistory['items'] = implode(",", $items_array);
        $dataForHistory['items_count'] = implode(",", $items_count_array);


        if ($mode == 'pdf') {
            $dataForHistory['mode'] = 'PDF Download';
        } else if ($mode == 'excel') {
            $dataForHistory['mode'] = 'Excel Download';
        } else {
            $dataForHistory['mode'] = 'View Report';
        }

        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';

        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $this->ReportAudit->save($dataForHistory);
    }
    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        switch ($type) {
            case 'employee':
                $this->generateemployeereport($mode);
                break;
                //attendance register report
            case 'Attendance':
                $this->generatesummaryreport($mode);
                break;
            case 'EmployeePayHours':
                $this->generateEmployeePayHoursreport($mode);
                break;
            case 'AttendanceRep':
                $this->generateattendancereport($mode);
                break;
            case 'AssignmentAttendance':
                $this->generateDetailedreport($mode);
                break;
            case 'TimeAttendance':
                $this->generatetimeattendancereport($type, $mode);
                break;
            case 'MobilelocationRep':
                $this->generatemobilelocationreport($type, $mode);
                break;
            case 'SiteClockWise':
                $this->generateCheckinlogsReport($mode);
                break;
            case 'Overtime':
                $this->Overtimereport($type, $mode);
                break;
            case 'ClientReportStandard':
                $this->generateClientReportStandard($type, $mode);
                break;
            case 'ClientReport(Actual)':
                $this->generateClientReport($type, $mode);
                break;
            case 'RottaMaster':
                $this->generateRottaMaster($mode);
                break;
            case 'RottaMasternew':
                $this->generateRottaMasternew($mode);
                break;
            case 'SiteRate':
                $this->generateSiteRate($mode);
                break;
            case 'Customer':
                $this->generateCustomerReport($mode);
                break;
            default:
                return false;
                break;
        }
        $this->reportAudit($type, $mode);
    }

    public function listemployeefields()
    {
        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
            $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
            $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
            $arr_empinformation_fields->getFieldHeadings('Departments'),
            $arr_empinformation_fields->getFieldHeadings('Grades'),
            $arr_empinformation_fields->getFieldHeadings('Verticals'),
            $arr_empinformation_fields->getFieldHeadings('Units')
        );
        $arr_emp_field_names = array(
            'EmployeeDetails' => $arr_empinformation_fields->getFieldNames('EmployeeDetails'),
            'EmployeeProfessionalDetails' => $arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails'),
            'Departments' => $arr_empinformation_fields->getFieldNames('Departments'),
            'Grades' => $arr_empinformation_fields->getFieldNames('Grades'),
            'Verticals' => $arr_empinformation_fields->getFieldNames('Verticals'),
            'Units' => $arr_empinformation_fields->getFieldNames('Units')
        );

        $resp_emp = array();
        $resp_emp["rows"] = array();
        foreach ($arr_emp_field_names as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $data['id'] = $key . '.' . $key1;
                $data['data'] = array($value1);
                $resp_emp["rows"][] = $data;
            }
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }

    private function _modelExists($modelName)
    {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }
    private function generateClientReportStandard($type = '', $mode = '')
    {
        $user_name = $this->Session->read('user_name');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);

        $this->set('user_name', $user_name);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $arr_form_data = $_REQUEST;
        // debug($mode);
        // debug($arr_form_data);
        $this->set('criteria', $arr_form_data['hidden-criteria1']);
        $reportfrom = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        $this->set('month', $reportfrom);
        $start_month = date('Y-m-01', strtotime($reportfrom));

        // Edited by Akshay on 21-8-2025
        $company_code = strtoupper($this->Session->read('company_code'));
        $lastDay = date("d", strtotime("last day of $start_month"));
        if ($company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'SCRT') {
            $report_date = strtotime($reportfrom . "-01");
            if ($report_date < strtotime("2025-08-01")) {
                $eratess_column = "site_transactions.eratess_31";
            } else {
                $days_in_month = date("t", $report_date); // t = number of days in the month
                $eratess_column = "site_transactions.eratess_" . $days_in_month;
            }

            $select_eratess = $eratess_column;
        } else {
            $select_eratess = "site_transactions.eratess";
        }
        // End
        // $end_month=date('Y-m-31',strtotime($reportfrom));
        $contacts = isset($arr_form_data['Contacts']) ? $arr_form_data['Contacts'] : '';
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_clientreport = array();
        $arr_clientreport_res = array();
        $arr_clientreport_res_pdf = array();
        if (!empty($contacts)) {
            foreach ($contacts as $value) {
                // $arr_clientreport[]=$this->EmployeeDetails->query("SELECT  site_transactions.site_fkey,site.site_id,site.address,site.special_remarks,site.site_name,site.customer_name,site.customer_contact,designation.desig_name ,day_time_desc,DAY(LAST_DAY('$start_month')) days,emp_count,srate,eratess,site_transactions.start_date_effective ,site_transactions.end_date_effective, DATE_FORMAT(site_transactions.modified_date,'%Y-%m-%d') as modified_date,
                //     (working_time1/60) shift_hours, ((srate*(working_time1/60))*DAY(LAST_DAY('$start_month'))) RATEPERHead ,contacts.company_name,contacts.address,contacts.first_name,contacts.middle_name,contacts.last_name,contacts.c_designation,
                //     contacts.phone,contacts.email							
                //     from site_transactions
                //     left join site on (site.site_pkey = site_transactions.site_fkey ) 
                //     left join contacts on (contacts.contact_id = site.contact_name) 
                //     left join designation on (designation.id = site_transactions.designation_id)
                //     left join working_day_time_procedures on (day_time_seq_fkey=day_time_seq)
                //     WHERE site_transactions.status=1
                //     and '$reportfrom' between DATE_FORMAT(site_transactions.start_date_effective,'%Y-%m') and DATE_FORMAT(site_transactions.end_date_effective,'%Y-%m')
                //     and contact_id=$value
                // -- group by site_transactions.site_fkey,site_transactions.day_time_seq_fkey,designation_id
                // order by site_transactions.site_fkey");
                $arr_clientreport[] = $this->EmployeeDetails->query("SELECT site_transactions.site_fkey,site.site_id,site.address,site.special_remarks,site.site_name,site.customer_name,site.customer_contact,designation.desig_name ,day_time_desc,
                abs(CASE WHEN DATE_FORMAT(site_transactions.start_date_effective,'%Y-%m')=DATE_FORMAT('$start_month','%Y-%m') and
                DATE_FORMAT(site_transactions.end_date_effective,'%Y-%m')=DATE_FORMAT('$start_month','%Y-%m') then
                DATEDIFF(DATE_ADD(site_transactions.start_date_effective, INTERVAL 0 DAY) ,DATE_ADD(site_transactions.end_date_effective, INTERVAL 1 DAY))
                WHEN site_transactions.start_date_effective between '$start_month' and LAST_DAY('$start_month') then
                DATEDIFF(DATE_ADD(site_transactions.start_date_effective, INTERVAL 0 DAY) ,LAST_DAY('$start_month'))
                WHEN site_transactions.end_date_effective between '$start_month' and LAST_DAY('$start_month') then
                DATEDIFF('$start_month',DATE_ADD(site_transactions.end_date_effective, INTERVAL 1 DAY)) else DAY(LAST_DAY('$start_month')) end) days,
                emp_count,srate,
                $select_eratess AS eratess,
                site_transactions.start_date_effective ,site_transactions.end_date_effective, DATE_FORMAT(site_transactions.modified_date,'%Y-%m-%d') as modified_date,
                (working_time1/60) shift_hours, ((srate*(working_time1/60))*DAY(LAST_DAY('$start_month'))) RATEPERHead ,contacts.company_name,contacts.address,contacts.first_name,contacts.middle_name,contacts.last_name,contacts.c_designation,
                contacts.phone,contacts.email

                from site_transactions
                left join site on (site.site_pkey = site_transactions.site_fkey )
                left join contacts on (contacts.contact_id = site.contact_name)
                left join designation on (designation.id = site_transactions.designation_id)
                left join working_day_time_procedures on (day_time_seq_fkey=day_time_seq)
                WHERE site_transactions.status=1
                and '$reportfrom' between DATE_FORMAT(site_transactions.start_date_effective,'%Y-%m')
                and DATE_FORMAT(site_transactions.end_date_effective,'%Y-%m')
                and contact_id=$value
                -- group by site_transactions.site_fkey,site_transactions.day_time_seq_fkey,designation_id
                order by site_transactions.site_fkey");
            }
            foreach ($arr_clientreport as $each) {
                $billing_value = 0;
                $total_number = 0;
                $site_id = 0;
                foreach ($each as $key => $value) {
                    $arr_clientreport_res[$value['contacts']['company_name']][$key] = $value;
                }
            }

            if (!empty($arr_clientreport)) {
                foreach ($arr_clientreport as $each) {
                    foreach ($each as $key => $value) {
                        if (count($arr_clientreport_res_pdf) < 10) {
                            $arr_clientreport_res_pdf[$value['contacts']['company_name']][$key] = $value;
                        }
                    }
                }
            }
        } else {
            $no_criteria = "No Criteria Selected";
            $this->set('no_criteria', $no_criteria);
        }

        $this->set('arr_clientreport', $arr_clientreport);
        $this->set('arr_clientreport_res', $arr_clientreport_res);
        $this->set('arr_clientreport_res_pdf', $arr_clientreport_res_pdf);
        // debug($arr_clientreport);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('clientstandardreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('SiteDetailedReport(StandardRate).pdf', 'D');
                $this->render('clientstandardreport');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_SiteDetailedReport(StandardRate)" . $reportfrom . ".xlsx" : "StandardSiteWiseDetailsReport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();
                if (!empty($arr_clientreport_res)) {
                    $month_num = date('m', strtotime($reportfrom));
                    $year = date('Y', strtotime($reportfrom));
                    // echo $month_num;
                    $monthName = date('F', mktime(0, 0, 0, $month_num, 10)); // March

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Site Detailed Report (Standard Rate) : " . $year . "-" . $monthName);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                    for ($col = 'A'; $col !== 'Y'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:Y1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $msg = '';
                    if ($arr_form_data['hidden-criteria1'] == 'Contacts') {
                        $msg = 'Belonging to a Client';
                    } else {
                        $msg = 'Belonging to a ' . $criteria;
                    }
                    $worksheet->setCellValueByColumnAndRow(0, 2, "(" . $msg . " Report run by " . $user_name . " - " . $date_time . ")");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                    $worksheet->mergeCells('A2:Y2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );

                    $rowcount = 3;
                    for ($i = 0; $i < 29; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                    }
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Client Name');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Client Address');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Site Code');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Site Name');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Site Address');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Segment');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Contact Person');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Contact Person Designation');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Mobile Number');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Mail ID');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Customer Name');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Customer Contact Number');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Contract Start Date');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Contract End Date');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Last Modified Date');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Shift Policy');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, 'Number of Employees');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, 'Shift Hours');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(20) . $rowcount, 'Days');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(21) . $rowcount, 'Sales Rate');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(22) . $rowcount, 'Sales Rate Per Head');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(23) . $rowcount, 'Expense Rate');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowcount, 'Value');
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, 'Total Billing Value');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, 'Wages Per Person');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(26) . $rowcount, 'Total Wages');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(27) . $rowcount, 'Mark Up');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(28) . $rowcount, 'Mark Up %');

                    $rowcount = 4;

                    $i = 1;
                    $grant_total_wages = 0;
                    $total_value = 0;
                    $total_wages_per_person = 0;
                    $total_wages_total = 0;
                    $markup_total = 0;
                    $markup_total_percentage = 0;
                    foreach ($arr_clientreport_res as $each) {
                        $items = count($each);
                        $billing_value = array();
                        $total_wages = 0;
                        $site = 0;
                        $total_number = 0;

                        // foreach ($each as $test) {
                        //                     // debug($test);
                        //     $number=$test['site_transactions']['emp_count'];
                        //     $total_number+=$number;
                        //     $shift_hours=$test[0]['shift_hours'];
                        //     $shift_days=$test[0]['days'];
                        //     $rate_per_head=$test[0]['RATEPERHead'];
                        //     $rate_per_hour=$rate_per_head/$shift_days/$number;
                        //     $res_value=$rate_per_hour*$shift_days*$shift_hours*$number;
                        //     $billing_value[$test['site']['site_id']]=isset($billing_value[$test['site']['site_id']])?$billing_value[$test['site']['site_id']]+$res_value:$res_value;
                        //     $srate=$test['site_transactions']['eratess'];
                        //     $wages=($srate*$shift_hours*$shift_days)*$number;
                        //     $wages_per_person[$test['site']['site_id']]=isset($wages_per_person[$test['site']['site_id']])?$wages_per_person[$test['site']['site_id']]+$wages:$wages;
                        //     $total_wages[$test['site']['site_id']]=isset($total_wages[$test['site']['site_id']])?$wages_per_person[$test['site']['site_id']]*$total_number:$wages_per_person[$test['site']['site_id']];
                        //     $markup[$test['site']['site_id']]=(($billing_value[$test['site']['site_id']]-$total_wages[$test['site']['site_id']])/$total_wages[$test['site']['site_id']])*100;
                        // }
                        foreach ($each as $value) {
                            $number = $value['site_transactions']['emp_count'];
                            $shift_hours = $value[0]['shift_hours'];
                            $shift_days = $value[0]['days'];
                            // $rate_per_head=round($value[0]['RATEPERHead']);
                            $sales_rate = $value['site_transactions']['srate'];
                            $rate_per_head = $sales_rate * $shift_hours * $shift_days;
                            if ($rate_per_head != 0) {
                                $rate_per_hour = ($rate_per_head / $shift_days) / $shift_hours;
                            } else {
                                $rate_per_hour = 0;
                            }
                            $res_value = $rate_per_hour * $shift_days * $shift_hours * $number;
                            $erate = $value['site_transactions']['eratess'];
                            $wages = ($erate * $shift_hours * $shift_days);
                            $total_wages = $wages * $number;
                            $markup = $res_value - $total_wages;
                            if ($total_wages != 0) {
                                $markup_percentage = (($res_value - $total_wages) / $total_wages) * 100;
                            } else {
                                $markup_percentage = 0;
                            }
                            $grant_total_wages = round($grant_total_wages) + round($total_wages);



                            $total_value = round($total_value) + round($res_value);
                            $total_wages_per_person = round($total_wages_per_person) + round($wages);
                            $total_wages_total = round($total_wages_total) + round($total_wages);
                            $end_date = $value['site_transactions']['end_date_effective'];
                            $today = date('Y-m-d');
                            $closed_site = '';
                            if ($end_date < $today) {
                                $closed_site = ' (Closed Site)';
                            }

                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $value['contacts']['company_name']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $value['contacts']['address']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $value['site']['site_id']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $value['site']['site_name'] . $closed_site);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $value['site']['address']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $value['site']['special_remarks']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $value['contacts']['first_name'] . ' ' . $value['contacts']['middle_name'] . ' ' . $value['contacts']['last_name']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $value['contacts']['c_designation']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $value['contacts']['phone']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $value['contacts']['email']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $value['site']['customer_name']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $value['site']['customer_contact']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $value['site_transactions']['start_date_effective']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $value['site_transactions']['end_date_effective']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $value[0]['modified_date']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $value['working_day_time_procedures']['day_time_desc']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, $value['designation']['desig_name']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, $value['site_transactions']['emp_count']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, round($value[0]['shift_hours'], 2));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(20) . $rowcount, $value[0]['days']);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(21) . $rowcount, round($rate_per_hour, 2));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(22) . $rowcount, round($rate_per_head, 2));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(23) . $rowcount, round($erate, 2));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowcount, round($res_value));
                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, round($billing_value[$test['site']['site_id']]));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, round($wages));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(26) . $rowcount, round($total_wages));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(27) . $rowcount, round($markup));
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(28) . $rowcount, round($markup_percentage));
                            $i++;
                            $rowcount++;
                        }
                    }
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, 'Grand Total');
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(25, $rowcount)->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(26) . $rowcount, round($grant_total_wages));
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(26, $rowcount)->getFont()->setBold(true);
                    // $rowcount++;
                    // $worksheet->mergeCells('A'.$rowcount.':T'.$rowcount);
                    // $worksheet->mergeCells('U'.$rowcount.':W'.$rowcount);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Grand Total');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':X' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowcount, round($total_value));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(24, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, round($total_wages_per_person));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(25, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(26) . $rowcount, round($grant_total_wages));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(26, $rowcount)->getFont()->setBold(true);
                    $markup_total = $total_value - $total_wages_total;
                    $markup_total_percentage = (($total_value - $total_wages_total) / $total_wages_total) * 100;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(27) . $rowcount, round($markup_total));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(27, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(28) . $rowcount, round($markup_total_percentage));
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(28, $rowcount)->getFont()->setBold(true);
                } else {
                    $worksheet->setCellValueByColumnAndRow(0, 1, isset($no_criteria) ? $no_criteria : 'There is no data');
                }

                $objPHPExcel->getActiveSheet()->setTitle('SiteDetailedReport_StandardRate');

                $criteria = $arr_form_data['hidden-criteria1'];
                if (isset($criteria)) {
                    if ($criteria == 'Contacts') {
                        $msg = 'belonging to a Client';
                    } else {
                        $msg = 'belonging to a ' . $criteria;
                    }
                }
                /* header footer */
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L ' . $msg . ' Downloaded By ' . $user_name . '&R Page &P / &N');
                $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter('&L ' . $msg . ' Downloaded By ' . $user_name . '&R Page &P / &N');
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
                break;
            default:
                $this->set('mode', '');
                $this->render('clientstandardreport');
                break;
        }
    }
    private function generateemployeereport($mode = '')
    {
        $arr_form_data = $_REQUEST;

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $arr_reportfields = array();
        $fields = '';
        if (isset($arr_form_data['hidden-reportfields']) && $arr_form_data['hidden-reportfields'] != '') {
            $fields = $arr_form_data['hidden-reportfields'];
            $search = array('EmployeeDetails.', 'EmployeeProfessionalDetails.', 'Departments.', 'Grades.', 'Verticals.', 'Units.');
            $replace = array('', '', '', '', '', '');
            $str_reportfieldheadings = str_replace($search, $replace, $fields);
            $arr_reportfieldheadings = explode(',', $str_reportfieldheadings);
        }
        //$fields = 'EmployeeDetails.*,EmployeeProfessionalDetails.*,Departments.*,Grades.*,Verticals.*,Units.*';

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'department',
                'alias' => 'Departments',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_dept = Departments.dept_code')
            ),
            array(
                'table' => 'grade',
                'alias' => 'Grades',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_grade = Grades.grade_code')
            ),
            array(
                'table' => 'verticals',
                'alias' => 'Verticals',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_vertical = Verticals.vert_code')
            ),
            array(
                'table' => 'branches',
                'alias' => 'Units',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.emp_branch = Units.branch_code')
            ),
        );
        $conditions = array('EmployeeDetails.status' => 1);

        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
            $conditions[] = 'EmployeeProfessionalDetails.joining_date BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
        }
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$str_employee_reportcriteria_field = $this->arr_employee_reportcriteria_fields[$str_criteria_item];
            //$this->arr_employee_reportcriteria_fields[$str_criteria_item];

            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("fields" => "reportcriteria_field", "conditions" => array("status" => 1, 'reportcriteria' => $str_criteria_item))));
            if (isset($arr_reportcriterias[0]['reportcriteria_field'])) {
                if ($str_criteria_item != 'EmployeeProfessionalDetails') {
                    $conditions[] = $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
                }
            }
        }
        $arr_emp_details = $this->EmployeeDetails->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions
        ));

        /* $arr_employee_personal = Set::extract('/EmployeeDetails/.',$arr_emp_details);
          $arr_employee_professional = Set::extract('/EmployeeProfessionalDetails/.',$arr_emp_details);
          $arr_employee_departments = Set::extract('/Departments/.',$arr_emp_details);
          $arr_employee_grades = Set::extract('/Grades/.',$arr_emp_details);
          $arr_employee_verticals = Set::extract('/Verticals/.',$arr_emp_details);
          $arr_employee_units = Set::extract('/Units/.',$arr_emp_details);
          $arr_employee_report_details = array_merge($arr_employee_personal,$arr_employee_professional,$arr_employee_departments,$arr_employee_grades,$arr_employee_verticals,$arr_employee_units); */

        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
            $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'),
            $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'),
            $arr_empinformation_fields->getFieldHeadings('Departments'),
            $arr_empinformation_fields->getFieldHeadings('Grades'),
            $arr_empinformation_fields->getFieldHeadings('Verticals'),
            $arr_empinformation_fields->getFieldHeadings('Units')
        );
        $arr_emp_field_names = array(
            'EmployeeDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeDetails')),
            'EmployeeProfessionalDetails' => array_keys($arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails')),
            'Departments' => array_keys($arr_empinformation_fields->getFieldNames('Departments')),
            'Grades' => array_keys($arr_empinformation_fields->getFieldNames('Grades')),
            'Verticals' => array_keys($arr_empinformation_fields->getFieldNames('Verticals')),
            'Units' => array_keys($arr_empinformation_fields->getFieldNames('Units'))
        );

        $this->set('arr_emp_field_headings', $arr_emp_field_headings);
        $this->set('arr_report_field_headings', $arr_reportfieldheadings);
        $this->set('arr_emp_field_names', $arr_emp_field_names);
        $this->set('arr_employee_report_details', $arr_emp_details);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportemployeeinformation');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('EmployeeInformation.pdf', 'D');
                //$this->render('reportemployeeinformation');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_EmployeeInformation.xlsx" : "EmployeeInformation_" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Information Report");
                $worksheet->mergeCells('A1:F1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $sheet = array($arr_emp_field_headings);

                $columnindex = 0;
                foreach ($sheet as $row => $columns) {
                    foreach ($columns as $column => $data) {
                        if (in_array($column, $arr_reportfieldheadings)) {
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . "2", $data);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $columnindex++;
                        }
                    }
                }

                $rowcount = 3;
                foreach ($arr_emp_details as $value) {
                    $columnindex = 0;
                    foreach ($arr_emp_field_names as $key => $val) {
                        foreach ($val as $val1) {
                            if (isset($value[$key][$val1])) {
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $value[$key][$val1]);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $columnindex++;
                            }
                        }
                    }
                    $rowcount++;
                }

                $objPHPExcel->getActiveSheet()->setTitle('Employee Information');

                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                $objWriter->save(dirname(__FILE__) . "/" . $file_name);

                // output headers so that the file is downloaded rather than displayed
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $file_name);

                readfile(dirname(__FILE__) . "/" . $file_name);
                unlink(dirname(__FILE__) . "/" . $file_name);
                break;
            default:
                $this->set('mode', '');
                $this->render('reportemployeeinformation');
                break;
        }
    }

    //////


    private function generateDetailedreport($mode)
    {
        $arr_form_data = $_REQUEST;
         $company_code = $this->Session->read('company_code');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'];
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t H:m:s', strtotime($arr_form_data['reportfrom'] . ' ' . '23:00:00'));
            $month = date('M - Y', strtotime($from));
            $this->set('month', $month);
        }
        $criterias = $arr_form_data['select-criteria1'];



        //  debug($fd);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        $condition = 'and  emp_details.status = 1';
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition =  "and  emp_details.status in('1','2')";
        }
        $arr_leavepolicydetails_for_template = array();
        //  debug($arr_leavepolicygroupids);
        $arr_DetaildAttendance = array();
        $arr_sitemonth_proc = $this->DeviceAttendance->query("SELECT `site_time_duration_check`('$fd', '', '')");
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    //                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(duration) as times ,branches.branch_name, count(duration) as days, contacts.company_name,emp_details.emp_pkey , designation.desig_name, emp_details.first_name,"
                    //                            . "emp_proff.emp_company_id, emp_details.last_name , site.site_name, site_fkey from emp_site_detail_timeattandance "
                    //                            . "left join emp_details on (emp_details.emp_pkey = emp_site_detail_timeattandance.emp_pkey) "
                    //                            . "left join site on (site.site_pkey = emp_site_detail_timeattandance.site_fkey ) "
                    //                            . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                    //                            . "left join designation on (designation.desig_code = emp_proff.designation) "
                    //                            . "left join branches on branches.branch_code = emp_details.branch_code "
                    //                            . "left join contacts on (contacts.contact_id = site.contact_name) "
                    //                            . "WHERE emp_site_detail_timeattandance.emp_pkey = '$leavepolicygroupid' and yearmonth = '$from' and duration > 0 $condition
                    //                            group by site_fkey ");
                    //Query changed with the table site_attendance on 12/8/2020 by ***


                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(duration) as times ,branches.branch_name,site.payment_mode, "
                        . "count(distinct att_date) as days, contacts.company_name, "
                        . "emp_details.emp_pkey , designation.desig_name, emp_details.first_name, "
                        . "emp_proff.emp_company_id, emp_details.last_name , site.site_name,site.site_id, site_fkey "
                        . "from site_attendance "
                        . "left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) "
                        . "left join site on (site.site_pkey = site_attendance.site_fkey ) "
                        . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                        . "left join designation on (designation.id = site_attendance.designation_id) "
                        . "left join branches on branches.branch_code = emp_details.branch_code "
                        . "left join contacts on (contacts.contact_id = site.contact_name) "
                        . "WHERE site_attendance.emp_fkey = '$leavepolicygroupid' and site_attendance.status=3 and site_attendance.active=1 and site.status = 1 "
                        . "and DATE_FORMAT(site_attendance.att_date, '%Y-%m')= '$fd' and duration > 0 $condition "
                        . "group by site_fkey");
                } else   if ($arr_form_data['select-criteria1'] == 'Units') {

                    //                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(duration) as times ,branches.branch_name,contacts.company_name, count(duration) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,"
                    //                            . "emp_proff.emp_company_id, emp_details.last_name , site.site_name, site_fkey from emp_site_detail_timeattandance "
                    //                            . "left join emp_details on (emp_details.emp_pkey = emp_site_detail_timeattandance.emp_pkey) "
                    //                            . "left join site on (site.site_pkey = emp_site_detail_timeattandance.site_fkey ) "
                    //                            . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                    //                            . "left join designation on (designation.desig_code = emp_proff.designation) "
                    //                            . "left join branches on branches.branch_code = emp_details.branch_code "
                    //                            . "left join contacts on (contacts.contact_id = site.contact_name) "
                    //                            . "WHERE branches.branch_code = '$leavepolicygroupid' and yearmonth = '$from' and duration > 0 $condition
                    //                          group by site_fkey ,emp_pkey ");
                    //Query changed with the table site_attendance on 11/8/2020 by ***

                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(duration) as times ,branches.branch_name,site.payment_mode,
                        contacts.company_name, count(distinct att_date) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,
                        emp_proff.emp_company_id, emp_details.last_name , site.site_name,site.site_id, site_fkey from site_attendance
                        left join emp_details on (emp_details.emp_pkey = site_attendance.emp_Fkey)
                        left join site on (site.site_pkey = site_attendance.site_fkey )
                        left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey
                        left join designation on (designation.id = site_attendance.designation_id) 
                        left join branches on (branches.branch_code = emp_details.branch_code)
                        left join contacts on (contacts.contact_id = site.contact_name) 
                        WHERE branches.branch_code = '$leavepolicygroupid' AND  DATE_FORMAT(site_attendance.att_date, '%Y-%m')= '$fd'  AND duration > 0 
                        $condition  and site_attendance.status=3 and site_attendance.active=1 and site.status = 1
                        group by site_fkey ,emp_pkey");
                } else {


                    $user_group = $this->Session->read('user_group');
                    $emp_branch_condition = "";

                    // Edited by Akshay on 11-2-2025
                    $current_emp_pkey = $this->Session->read('emp_fkey');
                    $user_group = $this->Session->read('user_group');
                    $company_code = $this->Session->read('company_code');
                         $branch_condition = '';  
                    if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                        $branch_condition = '';
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $arr_is_ho = $this->EmployeeDetails->query(
                            "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                            ['emp_pkey' => $current_emp_pkey]
                        );
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $branch_condition = " AND emp_details.branch_code = '$is_ho' ";
                        }else{
                            $branch_condition = '';  
                                              }                      
                    } else
                        // End

                        if ($user_group == 2) {
                            $cur_emp_key = $this->Session->read("emp_fkey");
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                            $emp_branch_condition = "and branches.branch_code='$cur_emp_branch'";
                        }
                    //                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(duration)  as times ,branches.branch_name, site.payment_mode,"
                    //                            . "contacts.company_name,count(duration) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,"
                    //                            . "emp_proff.emp_company_id, emp_details.last_name , site_name,site.site_id, site_fkey from emp_site_detail_timeattandance left join emp_details on (emp_details.emp_pkey = emp_site_detail_timeattandance.emp_pkey) left join site on (site.site_pkey = emp_site_detail_timeattandance.site_fkey ) left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                    //                            . "left join designation on (designation.desig_code = emp_proff.designation) "
                    //                            . "left join contacts on (contacts.contact_id = site.contact_name) "
                    //                            . "left join branches on branches.branch_code = emp_details.branch_code "
                    //                            . "WHERE site_fkey= '$leavepolicygroupid' $emp_branch_condition and yearmonth = '$from' and duration > 0 $condition
                    //                     group by emp_pkey ");

                    // Edited by Akshay on 11-2-2025
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(duration) as times ,branches.branch_name,site.payment_mode,
                        contacts.company_name, count(distinct att_date) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,
                        emp_proff.emp_company_id, emp_details.last_name , site.site_name,site.site_id, site_fkey from site_attendance
                        left join emp_details on (emp_details.emp_pkey = site_attendance.emp_Fkey)
                        left join site on (site.site_pkey = site_attendance.site_fkey )
                        left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey
                        left join designation on (designation.id = site_attendance.designation_id) 
                        left join branches on (branches.branch_code = emp_details.branch_code)
                        left join contacts on (contacts.contact_id = site.contact_name) 
                        WHERE site_attendance.site_fkey = '$leavepolicygroupid' AND  DATE_FORMAT(site_attendance.att_date, '%Y-%m')= '$fd'  AND duration > 0 
                        $condition  and site_attendance.status=3 and site_attendance.active=1 and site.status = 1
                        $branch_condition
                        group by site_fkey ,emp_pkey");
                    // End
                }

                if (!empty($arr_leavepolicy_details['0']['0']['times'])) {
                    $arr_leavepolicydetails_for_template[] = array(
                        //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                        'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
            }
            //  debug($arr_leavepolicydetails_for_template);
            //            $arr_dates = array();
            //debug($arr_dates);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($this_month_att);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        date_default_timezone_set("Asia/Calcutta");
        $arr_date = date('d-m-Y H:i');
        $this->set('arr_date', $arr_date);
        $this->set('criterias', $arr_form_data['select-criteria1']);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
         //edited by sinsiya on 15-09-2025
        $this->set('company_code', $company_code);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('timeattendancereports');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('timeattendancereports.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "SiteAssignmentReports" . $fd . ".xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Site Assignment Wise Report - " . $fd . "");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'J'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                $worksheet->setCellValueByColumnAndRow(0, 2, " Report run by " . $user_name . "-" . $arr_date);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:J2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 3;
                //$i = 0;
                //                  $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Attendance  Details of '.$name );
                //                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                //                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                //                    $rowcount = $rowcount + 1;
                //                 if($criterias == 'EmployeeDetails'){
                //                     if(!empty($arr_leavepolicydetails_for_template)){
                //
                //                          $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                //
                //
                //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Client name');
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);
                //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Site');
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                //                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee ID');
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);
                //                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Employee Name'); 
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);
                //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Branch'); 
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, ($rowcount))->getFont()->setBold(true);
                //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Designation');
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, ($rowcount))->getFont()->setBold(true);
                //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Total Hours');
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, ($rowcount))->getFont()->setBold(true);
                //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), 'Total Actual Days');
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, ($rowcount))->getFont()->setBold(true);
                //                         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), 'Payment Mode');
                //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, ($rowcount))->getFont()->setBold(true);
                //                        $rowcount = $rowcount + 1;
                //                 } }

                //$arr_daata = $value['summary'];
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Client name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Site');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Branch');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Designation');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Total Hours');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), 'Total Actual Days');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), 'Payment Mode');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, ($rowcount))->getFont()->setBold(true);
                $rowcount = $rowcount + 1;
                $i = 1;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    //$i += 1;     

                    $arr_daata = $value['summary'];

                    if (empty($arr_daata)) continue;


                    $branch = $arr_daata['0']['branches']['branch_name'];
                    //                     if($criterias != 'EmployeeDetails')
                    //                    { 
                    //                    if($criterias != 'Units')
                    //                        {
                    //                       // $name =  isset($arr_daata[0]['site']['site_name'])?$arr_daata[0]['site']['site_name']:''; 
                    //                        }
                    //
                    //                        else{
                    //                       // $name =  isset($arr_daata[0]['branches']['branch_name'])?$arr_daata[0]['branches']['branch_name']:'';    
                    //							 										  
                    //                        }
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Pay Hours Details of '.$name );
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    // $worksheet->mergeCells('A'.$rowcount.':I'.$rowcount.'');
                    // $worksheet->getStyle('A'.$rowcount.':I'.$rowcount.'')->getAlignment()->applyFromArray(
                    // array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );
                    // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    // $rowcount = $rowcount + 1;

                    // }

                    //                    if($arr_form_data['select-criteria1'] == 'EmployeeDetails'){
                    //                        $name = $arr_daata['0']['emp_details']['first_name'].' - '.$arr_daata['0']['emp_details']['last_name'];
                    //                    }else{
                    //                        $name = $arr_daata['0']['site']['site_name'];
                    //                    }

                    foreach ($arr_daata as $employee => $val) {
                        $client = $val['contacts']['company_name'];
                        $site = $val['site']['site_name'] . ' (' . $val['site']['site_id'] . ')';
                        $emp_company_id = $val['emp_proff']['emp_company_id'];
                        $emp_name = $val['emp_details']['first_name'] . $val['emp_details']['last_name'];
                        $designation = $val['designation']['desig_name'];
                        $total_hours = $val['0']['times'];
                        $total_actual_hours = $val['0']['days'];
                        if ($val['site']['payment_mode'] == '1') {
                            $payment_mode = 'Bank';
                        }
                        if ($val['site']['payment_mode'] == '2') {
                            $payment_mode = 'Cash';
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), $client);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . ($rowcount), $site);
                        //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . ($rowcount), $emp_company_id);
                        //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        // $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$rowcount, $emp_company_id,PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $emp_name);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $branch);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $designation);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), round($total_hours));
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $total_actual_hours);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), $payment_mode);
                        //                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((5), ($rowcount))->getFont()->setBold(true);

                        //                    $worksheet->getStyle("A".($rowcount).":F".($rowcount+1))->applyFromArray($border_style1);

                        $columnindex = 1;



                        $rowcount++;
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('G3:G400')
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                        $objPHPExcel->getActiveSheet()
                            ->getStyle('H3:H400')
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:J' . $row)->applyFromArray($BStyle);

                        foreach (range('A', 'J') as $columnID) {
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                        }
                        $i++;
                    }
                }
                if (empty($arr_leavepolicydetails_for_template)) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records found under this Criteria');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount . '');
                    $worksheet->getStyle('A' . $rowcount . '')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                }

                $objPHPExcel->getActiveSheet()->setTitle('Site Assignment Report');
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
                break;
            default:
                $this->set('mode', '');
                $this->render('timeattendancereports');
                break;
        }
    }






    private function generateEmployeePayHoursreport($mode)
    {
        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $fd = $arr_form_data['reportfrom'];
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t H:m:s', strtotime($arr_form_data['reportfrom'] . ' ' . '23:00:00'));
            $month = date('M - Y', strtotime($from));
            $this->set('month', $month);
        }
        $company_code = $this->Session->read('company_code'); //company_code
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');

        //By santhosh on 27 Dec 2015
        //$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',  strtotime($month));
        //On 20 Feb 2016
        //$att_enddate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);

        //  debug($fd);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        $condition = 'and  emp_details.status = 1';
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition =  "and  emp_details.status in('1','2')";
        }

        // Edited by Akshay on 11-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');

        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->DeviceAttendance->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $condition .= " AND emp_details.branch_code = '$is_ho' ";
            }
        }
        // End

        $arr_leavepolicydetails_for_template = array();
        //  debug($arr_leavepolicygroupids);
        $arr_DetaildAttendance = array();
        // $arr_sitemonth_proc = $this->DeviceAttendance->query("SELECT `site_time_duration_check`('$fd', '', '')");
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            // debug($arr_leavepolicygroupids);
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    //Applied new query of refering the table site_attendance on 12/8/2020 by ***
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(duration) as times , contacts.company_name,contacts.contact_id, branches.branch_name,"
                        . "count(distinct att_date) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,"
                        . "emp_proff.emp_company_id, emp_details.last_name , site.site_name, site_attendance.site_fkey from site_attendance "
                        . "left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) "
                        . "left join site on (site.site_pkey = site_attendance.site_fkey ) "
                        . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                        . "left join branches on branches.branch_code = emp_details.branch_code "
                        . "left join designation on (designation.id = site_attendance.designation_id) "
                        . "left join contacts on (contacts.contact_id = site.contact_name) "
                        . "WHERE site_attendance.emp_fkey = $leavepolicygroupid and site_attendance.status=3 and site_attendance.active=1 and site.status = 1 and "
                        . "DATE_FORMAT(site_attendance.att_date, '%Y-%m')= '$fd' and site_attendance.duration > 0 $condition "
                        . "group by emp_pkey");
                    //                      $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(emp_site_detail_timeattandance.duration)  as times , site_attendance_register.*, branches.branch_name,"
                    //                            . "count(emp_site_detail_timeattandance.duration) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,"
                    //                            . "emp_proff.emp_company_id, emp_details.last_name , site.site_name, emp_site_detail_timeattandance.site_fkey from emp_site_detail_timeattandance "
                    //                            . "left join emp_details on (emp_details.emp_pkey = emp_site_detail_timeattandance.emp_pkey) "
                    //                            . "left join site on (site.site_pkey = emp_site_detail_timeattandance.site_fkey ) "
                    //                            . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                    //                            . "left join branches on branches.branch_code = emp_details.branch_code "
                    //                            . "left join designation on (designation.desig_code = emp_proff.designation) "																				
                    //                            . "left join site_attendance_register on site_attendance_register.emp_fkey = emp_details.emp_pkey "
                    //                            . "WHERE emp_site_detail_timeattandance.emp_pkey = $leavepolicygroupid and "
                    //                            . "emp_site_detail_timeattandance.yearmonth = '$from' and site_attendance_register.month_year = '$report_month' and emp_site_detail_timeattandance.duration > 0 $condition
                    //                           group by emp_pkey ");
                    // debug($arr_leavepolicy_details);
                    //                     $arr_leavepolicy_details1 = $this->DeviceAttendance->query("SELECT SUM(emp_site_detail_timeattandance.duration) as times , branches.branch_name,"
                    //                            . "count(emp_site_detail_timeattandance.duration) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,"
                    //                            . "emp_proff.emp_company_id, emp_details.last_name , site.site_name, emp_site_detail_timeattandance.site_fkey from emp_site_detail_timeattandance "
                    //                            . "left join emp_details on (emp_details.emp_pkey = emp_site_detail_timeattandance.emp_pkey) "
                    //                            . "left join site on (site.site_pkey = emp_site_detail_timeattandance.site_fkey ) "
                    //                            . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                    //                            . "left join branches on branches.branch_code = emp_details.branch_code "
                    //                            . "left join designation on (designation.desig_code = emp_proff.designation) "
                    //                            . "WHERE emp_site_detail_timeattandance.emp_pkey = $leavepolicygroupid and emp_site_detail_timeattandance.yearmonth = '$from' and emp_site_detail_timeattandance.duration > 0 $condition
                    //                           group by emp_pkey limit 991,1980");
                    //                      $arr_leavepolicy_details2 = $this->DeviceAttendance->query("SELECT SUM(emp_site_detail_timeattandance.duration)  as times , branches.branch_name,"
                    //                            . "count(emp_site_detail_timeattandance.duration) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,"
                    //                            . "emp_proff.emp_company_id, emp_details.last_name , site.site_name, emp_site_detail_timeattandance.site_fkey from emp_site_detail_timeattandance "
                    //                            . "left join emp_details on (emp_details.emp_pkey = emp_site_detail_timeattandance.emp_pkey) "
                    //                            . "left join site on (site.site_pkey = emp_site_detail_timeattandance.site_fkey ) "
                    //                            . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                    //                            . "left join branches on branches.branch_code = emp_details.branch_code "
                    //                            . "left join designation on (designation.desig_code = emp_proff.designation) "
                    //                            . "WHERE emp_site_detail_timeattandance.emp_pkey = $leavepolicygroupid and emp_site_detail_timeattandance.yearmonth = '$from' and emp_site_detail_timeattandance.duration > 0 $condition
                    //                           group by emp_pkey limit 1981,2970");
                } elseif ($arr_form_data['select-criteria1'] == 'Contacts') {
                    //Applied new query of refering the table site_attendance on 12/8/2020 by ***
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("select distinct contacts.company_name,contacts.contact_id,"
                        . "SUM(site_attendance.duration) as times , "
                        . "branches.branch_name,count(distinct att_date) as days, emp_details.emp_pkey ,"
                        . "designation.desig_name, emp_details.first_name,emp_proff.emp_company_id, emp_details.last_name , site.site_name, "
                        . "site_attendance.site_fkey "
                        . "from site_attendance "
                        . "left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) "
                        . "left join site on (site.site_pkey = site_attendance.site_fkey ) "
                        . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                        . "left join branches on branches.branch_code = emp_details.branch_code "
                        . "left join designation on (designation.id = site_attendance.designation_id) "
                        . "left join contacts on (contacts.contact_id = site.contact_name) "
                       . "WHERE site.contact_name = '$leavepolicygroupid' and site.status = 1 and  DATE_FORMAT(site_attendance.att_date, '%Y-%m') = '$fd' and site_attendance.status=3 and site_attendance.active=1  "
                    . "and site_attendance.duration > 0 $condition "
                        . "group by emp_pkey");
                    // debug($arr_leavepolicy_details);

                } else {
                    //                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(emp_site_detail_timeattandance.duration)  as times ,site_attendance_register.*, branches.branch_name,"
                    //                            . "count(emp_site_detail_timeattandance.duration) as days, emp_details.emp_pkey , designation.desig_name, emp_details.first_name,"
                    //                            . "emp_proff.emp_company_id, emp_details.last_name , site.site_name, emp_site_detail_timeattandance.site_fkey from emp_site_detail_timeattandance "
                    //                            . "left join emp_details on (emp_details.emp_pkey = emp_site_detail_timeattandance.emp_pkey) "
                    //                            . "left join site on (site.site_pkey = emp_site_detail_timeattandance.site_fkey ) "
                    //																											
                    //                            . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                    //                            . "left join branches on branches.branch_code = emp_details.branch_code "
                    //                            . "left join designation on (designation.desig_code = emp_proff.designation) "
                    //                            . "left join site_attendance_register on site_attendance_register.emp_fkey = emp_details.emp_pkey "
                    //                            . "WHERE branches.branch_code = '$leavepolicygroupid' and emp_site_detail_timeattandance.yearmonth = '$from' "
                    //                            . "and site_attendance_register.month_year = '$report_month'  and emp_site_detail_timeattandance.duration > 0 $condition
                    //                            group by emp_pkey");
                    //Applied new query of refering the table site_attendance on 11/8/2020 by ***
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT  contacts.company_name,contacts.contact_id,"
                        . "SUM(site_attendance.duration) as times , "
                        . "branches.branch_name,count(distinct att_date) as days, emp_details.emp_pkey ,"
                        . "designation.desig_name, emp_details.first_name,emp_proff.emp_company_id, emp_details.last_name , site.site_name, "
                        . "site_attendance.site_fkey "
                        . "from site_attendance "
                        . "left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) "
                        . "left join site on (site.site_pkey = site_attendance.site_fkey ) "
                        . "left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey "
                        . "left join branches on branches.branch_code = emp_details.branch_code "
                        . "left join designation on (designation.id = site_attendance.designation_id) "
                        . "left join contacts on (contacts.contact_id = site.contact_name) "
                        . "WHERE branches.branch_code = '$leavepolicygroupid' and DATE_FORMAT(site_attendance.att_date, '%Y-%m') = '$fd' "
                        . "and site_attendance.duration > 0 and site_attendance.status=3 and site_attendance.active=1 $condition "
                        . "group by emp_pkey"); 
                }
                //debug($arr_leavepolicy_details);
                if (!empty($arr_leavepolicy_details['0']['0']['times'])) {
                    $arr_leavepolicydetails_for_template[] = array(
                        //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                        'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
                //              if(!empty($arr_leavepolicy_details1['0']['0']['times'])){
                //                $arr_leavepolicydetails_for_template[] = array(
                //                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                //                    'summary' => $arr_leavepolicy_details1,
                //                        // 'employees'=>$arr_leavepolicy_employees
                //                );
                //            } 
                //              if(!empty($arr_leavepolicy_details2['0']['0']['times'])){
                //                $arr_leavepolicydetails_for_template[] = array(
                //                    //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
                //                    'summary' => $arr_leavepolicy_details2,
                //                        // 'employees'=>$arr_leavepolicy_employees
                //                );
                //            } 

            }

            //            $arr_dates = array();
            //debug($arr_dates);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($this_month_att);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        date_default_timezone_set("Asia/Calcutta");
        $arr_date = date('d-m-Y H:i');
        $this->set('arr_date', $arr_date);
        $criterias = $arr_form_data['select-criteria1'];
        $this->set('criterias', $arr_form_data['select-criteria1']);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('payhoursreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('payhoursreport.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "PayHoursReport" . $fd . ".xlsx" : "PayHoursReport" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Pay Hours Report - " . $fd . "");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'H'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:H1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 2, " Report run by " . $user_name . "-" . $arr_date);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:H2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 3;

                //if($criterias == 'EmployeeDetails'){
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl.No');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Client Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Total Hours');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Total Actual Days');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, ($rowcount))->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Branch');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, ($rowcount))->getFont()->setBold(true);
                $rowcount = $rowcount + 1;

                // }
                $i = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {

                    $arr_daata = $value['summary'];

                    if (empty($arr_daata))                        continue;

                    $branch = $arr_daata['0']['branches']['branch_name'];
                    //                     if($criterias == 'Units')
                    //                    { 
                    //                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Pay Hours Details of '.$branch );
                    //                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    //                        // $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount.'');
                    //                        // $worksheet->getStyle('A'.$rowcount.':G'.$rowcount.'')->getAlignment()->applyFromArray(
                    //                        // array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    //                        // );
                    //                        // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                        // $rowcount = $rowcount + 1;
                    //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl.No');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Client Name'); 
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Employee Name'); 
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Total Hours');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Total Actual Days');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Branch');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, ($rowcount))->getFont()->setBold(true);
                    //                         $rowcount = $rowcount + 1;
                    //                           $i = 0;
                    //                    }
                    //                    
                    //                                 
                    //                 
                    //                     if($criterias == 'Contacts')
                    //                    { 
                    //                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Pay Hours Details of '.$clientName );
                    //                        // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    //                        // $worksheet->mergeCells('A'.$rowcount.':G'.$rowcount.'');
                    //                        // $worksheet->getStyle('A'.$rowcount.':G'.$rowcount.'')->getAlignment()->applyFromArray(
                    //                        // array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    //                        // );
                    //                        // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //                        // $rowcount = $rowcount + 1;
                    //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl.No');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Client Name'); 
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);
                    //
                    //                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    //
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Employee Name'); 
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Total Hours');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Total Actual Days');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, ($rowcount))->getFont()->setBold(true);
                    //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Branch');
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, ($rowcount))->getFont()->setBold(true);
                    //                         $rowcount = $rowcount + 1;
                    //                           $i = 0;
                    //                    }



                    foreach ($arr_daata as $employee => $val) {

                        $i += 1;
                        $site = $val['site']['site_name'];
                        $clientname = $val['contacts']['company_name'];
                        $emp_company_id = $val['emp_proff']['emp_company_id'];
                        $emp_name = $val['emp_details']['first_name'] . $val['emp_details']['last_name'];
                        $designation = $val['designation']['desig_name'];
                        $total_hours = round($val['0']['times']);
                        $total_actual_hours = $val['0']['days'];

                        //$tot = 0;
                        //                    $workings = 0;
                        //                    foreach($arr_dates as $key=> $date)
                        //                                            {
                        //                        $newIndex='FIELD'.($key+1);
                        //                       // $tot += $val['site_attendance_register'][$newIndex];
                        //                        if($val['site_attendance_register'][$newIndex] > 0){
                        //                            $workings++;
                        //							   
                        //                        }						
                        //                    }

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), $i);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $clientname);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $emp_company_id);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $emp_name);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $designation);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $total_hours);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $total_actual_hours);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $branch);
                        // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), $emp_company_id);
                        //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        // $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$rowcount, $emp_company_id,PHPExcel_Cell_DataType::TYPE_STRING);
                        //  $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $clientname);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $emp_name);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $designation);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), round($total_hours,2));
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $total_actual_hours);
                        // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $branch);

                        $columnindex = 1;

                        $rowcount++;
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );
                        $objPHPExcel->getActiveSheet()
                            ->getStyle('G3:G400')
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                        $objPHPExcel->getActiveSheet()
                            ->getStyle('F3:F400')
                            ->getAlignment()
                            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:H' . $row)->applyFromArray($BStyle);

                        foreach (range('A', 'H') as $columnID) {
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                        }
                    }
                    // $rowcount++;
                }
                if (empty($arr_leavepolicydetails_for_template)) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records found under this Criteria');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount . '');
                    $worksheet->getStyle('A' . $rowcount . '')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                }
                $objPHPExcel->getActiveSheet()->setTitle('Pay Hours Report');
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
                break;
            default:
                $this->set('mode', '');
                $this->render('payhoursreport');
                break;
        }
    }
    private function generateCheckinlogsReport($mode)
    {
        $arr_form_data = $_REQUEST;
        //  debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');

        //By santhosh on 27 Dec 2015
        //$att_startdate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_enddate = date('t',  strtotime($month));
        //On 20 Feb 2016
        //$att_enddate = isset($arr_db_config[0]['attendance_date'])?$arr_db_config[0]['attendance_date']:1;
        //$att_startdate = $att_enddate + 1;
        $attendance_date = isset($arr_db_config[0]['attendance_date']) ? $arr_db_config[0]['attendance_date'] : 0;
        $att_enddate = date('d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime($month)))));
        $att_startdate = date('d', strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-' . $attendance_date . ' day', strtotime(date('Y-m-t', strtotime('-1 months', strtotime($month)))))))));
        $arr_date_in_selectedmonth = range(1, $att_enddate);
        if ($att_startdate != 1) {
            $arr_date_in_prevmonth = range($att_startdate, date('t', strtotime('-1 months', strtotime($month))));
        } else {
            $arr_date_in_prevmonth = array();
        }

        $arr_dates = array_merge($arr_date_in_prevmonth, $arr_date_in_selectedmonth);
        $this->set('arr_dates', $arr_dates);

        //        debug($arr_dates);
        $fd = $arr_form_data['reportfrom'];
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t H:m:s', strtotime($arr_form_data['reportfrom'] . ' ' . '23:00:00'));
        }



        //  debug($fd);
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }


        $condition = 'and  emp_details.status = 1';
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition =  "and  emp_details.status in('1','2')";
        }
        $arr_leavepolicydetails_for_template = array();
        //  debug($arr_leavepolicygroupids); 
        $arr_DetaildAttendance = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            $arr_sitemonth_proc = $this->DeviceAttendance->query("SELECT `site_time_duration_check`('$fd', '', '')");
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {





                ini_set('memory_limit', '-1');
                if ($arr_form_data['select-criteria1'] == 'Units') {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT site_attendance_register.*,emp_details.first_name,"
                        . "emp_details.last_name,branches.branch_name,emp_proff.emp_company_id,"
                        . "designation.desig_name FROM `site_attendance_register` "
                        . "left join emp_details on (emp_details.emp_pkey = site_attendance_register.emp_fkey) "
                        . "left join branches on (branches.branch_code = emp_details.branch_code) "
                        . "left join emp_proff on (emp_proff.emp_fkey = site_attendance_register.emp_fkey) "
                        . "left join designation on (designation.desig_code = emp_proff.designation ) "
                        . "WHERE `site_attendance_register`.`month_year` = '$from' AND `emp_details`.`branch_code` = '$leavepolicygroupid' $condition");
                    $leavepolicyname = $this->DeviceAttendance->query("SELECT branch_name FROM branches WHERE branch_code = '$leavepolicygroupid'  ");
                } else {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("SELECT SUM(duration) as times , emp_details.emp_pkey , designation.desig_name, first_name,emp_proff.emp_company_id, last_name , site_name, site_fkey from emp_site_detail_timeattandance left join emp_details on (emp_details.emp_pkey = emp_site_detail_timeattandance.emp_pkey) left join site on (site.site_pkey = emp_site_detail_timeattandance.site_fkey ) left join emp_proff on emp_proff.emp_fkey = emp_details.emp_pkey left join designation on (designation.desig_code = emp_proff.designation) WHERE site_fkey= '$leavepolicygroupid' $condition and yearmonth = '$from'
  group by emp_pkey");
                    $leavepolicyname = $this->DeviceAttendance->query("SELECT branch_name FROM branches WHERE branch_code = '$leavepolicygroupid'  ");
                }

                //                debug($arr_leavepolicy_details);
                if (!empty($arr_leavepolicy_details)) {
                    $arr_leavepolicydetails_for_template[] = array(
                        'leavepolicyname' => isset($leavepolicyname[0]['branches']['branch_name']) ? $leavepolicyname[0]['branches']['branch_name'] : '',
                        'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                    );
                }
            }

            // debug($arr_leavepolicydetails_for_template); 
            //            $arr_dates = array();
            //debug($arr_dates);
            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($this_month_att);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('criterias', $arr_form_data['select-criteria1']);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
                //            case 'pdf' :
                //                $this->set('mode', 'pdf');
                //                $view = new View($this, false);
                //                $view_output = $view->render('detailedattendance');
                //                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //
                //                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                //                $html2pdf->pdf->SetDisplayMode('fullpage');
                //                $html2pdf->writeHTML($view_output);
                //                $html2pdf->Output('detailedattendance.pdf', 'D');
                //                //$this->render('reportshiftpolicy');                
                //                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "SiteClockwiseReports" . $fd . ".xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Clock Wise Attendance Report - " . $fd . "");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 2;
                $i = 0;
                ////debug($arr_dates);


                $i += 1;
                //$arr_daata = $value['summary'];
                $i = 0;
                foreach ($arr_leavepolicydetails_for_template as $value) {
                    //                   $i += 1;     
                    $arr_daata = $value['summary'];

                    if (empty($arr_daata))                        continue;

                    //                    debug($arr_daata['0']['site']['site_name']); die();
                    //                    if($arr_form_data['select-criteria1'] == 'EmployeeDetails'){
                    //                        $name = $arr_daata['0']['emp_details']['first_name'].' - '.$arr_daata['0']['emp_details']['last_name'];
                    //                    }else{
                    //                        $name = $arr_daata['0']['site']['site_name'];
                    //                    }
                    $employees = $value;
                    $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Clock Wise Attendance Report of ' . $employees['leavepolicyname']);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $rowcount = $rowcount + 2;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);
                    // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Date Of join');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);
                    $cols = 4;
                    foreach ($arr_dates as $val) {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4 + $val), ($rowcount), $val);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4 + $val, ($rowcount))->getFont()->setBold(true);
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5 + 31), ($rowcount), 'Total Hours');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5 + 31, ($rowcount))->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6 + 31), ($rowcount), 'No of Working Day');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6 + 31, ($rowcount))->getFont()->setBold(true);
                    $rowcount = $rowcount + 2;
                    $arr_e = $employees['summary'];
                    $i = 0;
                    foreach ($arr_e as $employee => $val) {

                        $i = $i + 1;
                        $site = $val['site_attendance_register']['emp_name'];
                        $emp_company_id = $val['emp_proff']['emp_company_id'];
                        $emp_name = $val['branches']['branch_name'];
                        $designation = $val['designation']['desig_name'];
                        $total_hours = $val['designation']['desig_name'];
                        //                        $total_actual_hours = $val['0']['times'];

                        //                        $info =  current($date);
                        //                        $status = isset($info['0']['EmployeeDetails']['status']) && $info['0']['EmployeeDetails']['status'] == 2 ? ' (Resigned)' : '';
                        //                        $termin = isset($info['0']['termination']['last_approved_working_date']) ? $info['0']['termination']['last_approved_working_date'] :'';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), $i);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), $site);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $emp_company_id);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $emp_name);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $designation);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $total_hours);
                        //                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((5), ($rowcount))->getFont()->setBold(true);
                        $tot = 0;
                        $workings = 0;
                        foreach ($arr_dates as $key => $date) {
                            $newIndex = 'FIELD' . ($key + 1);
                            $tot += $val['site_attendance_register'][$newIndex];
                            if ($val['site_attendance_register'][$newIndex] > 0) {
                                $workings++;
                            }
                            //$ecjo =  round($val['site_attendance_register'][$newIndex]); 
                            $ecjo = (round($val['site_attendance_register'][$newIndex]) == 0) ? '' : round($val['site_attendance_register'][$newIndex], 2);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5 + $key), ($rowcount), $ecjo);
                            //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((5+$key), ($rowcount))->getFont()->setBold(true);
                        }
                        //$tot = round($tot);
                        $tot = round($tot, 2);
                        //                    $worksheet->getStyle("A".($rowcount).":F".($rowcount+1))->applyFromArray($border_style1);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5 + 31) . ($rowcount), $tot);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6 + 31) . ($rowcount), $workings);

                        $columnindex = 1;

                        $rowcount++;
                    }
                    $rowcount++;
                }
                if (empty($arr_leavepolicydetails_for_template)) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records found under this Criteria');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount . '');
                    $worksheet->getStyle('A' . $rowcount . '')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                }
                $objPHPExcel->getActiveSheet()->setTitle('Clock Wise Attendance Report');
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
                break;
            default:
                $this->set('mode', '');
                $this->render('detailedattendance');
                break;
        }
    }

    private function generateClientReport($type = '', $mode = '')
    {
        $arr_form_data = $_REQUEST;
        $no_page = 2;
        $user_name = $this->Session->read('user_name');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');

        $limit = 1;
        $arr_leavepolicygroupids = array();
        $int_criterias_count = '';
        $int_criterias_count = isset($arr_form_data['hidden-criterias-count']) ? $arr_form_data['hidden-criterias-count'] : '';

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];


            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }

        $year = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
        date_default_timezone_set('Asia/Kolkata');
        $month = date('Y - F', strtotime($year));

        $needBranchWiseReport = false;
        $needEmployeeWiseReport = false;
        if ($str_criteria_item == 'Contacts') {
            $needBranchWiseReport = true;
        } else if ($str_criteria_item == 'Site') {
            $needEmployeeWiseReport = true;
        }
        // $arr_form_data = $_REQUEST;

        $arr_clientreport = array();
        $arr_clientreport_res = array();


        // debug($arr_count);
        // $count = $arr_count['0']['0']['count(*)'];
        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('needEmployeeWiseReport', $needEmployeeWiseReport);
        $arr_site_for_template = array();
        $arr_site_for_template1 = array();
        $str_query = '';
        if ($needEmployeeWiseReport) {
            $str_query = "select concat(ed.first_name,' ',ifnull(ed.middile_name,''),' ',ifnull(ed.last_name,'')) AS EmpName,site_attendance.site_fkey,
                working_day_time_procedures.day_time_desc,designation.desig_name ,site.site_id,site.site_name,site.address,site.special_remarks,site.customer_name,site.customer_contact,day_time_desc,SUM(ifnull(site_attendance.duration,0)) as duration,round(SUM(ifnull(site_attendance.duration,0))*emp_rate) as amount,site_t.start_date_effective,site_t.end_date_effective
            ,count(distinct site_attendance.att_date) as days,sales_rate,emp_rate,count(distinct site_attendance.emp_fkey) actual_empcount,(working_time1/60) shift_hours,((sales_rate*(working_time1/60))*count(distinct site_attendance.att_date)) RATEPERHead,
            contacts.company_name,contacts.address,contacts.first_name,contacts.last_name,contacts.c_designation,contacts.phone,contacts.email
            from site_attendance
            left join site on (site.site_pkey = site_attendance.site_fkey )
            left join site_transactions as site_t on (site_t.site_fkey = site.site_pkey)
            left join contacts on (contacts.contact_id = site.contact_name)
            left join designation on (designation.id = site_attendance.designation_id)
            left join working_day_time_procedures on (site_attendance.day_time_seq_fkey=day_time_seq)
            left join emp_details ed on(emp_pkey=site_attendance.emp_fkey)
            WHERE site_attendance.status=3 and site_attendance.active=1 and site_attendance.site_fkey=site_t.site_fkey and site_t.day_time_seq_fkey=site_attendance.day_time_seq_fkey
            and site_t.designation_id=site_attendance.designation_id
            and DATE_FORMAT(att_date ,'%Y-%m') = '$year' and '$year' between DATE_FORMAT(site_t.start_date_effective,'%Y-%m')
            and DATE_FORMAT(site_t.end_date_effective,'%Y-%m') 
            and ed.emp_pkey IN (_emp_id) and site_attendance.site_fkey IN (_site_id)
            group by concat(ed.first_name,' ',ifnull(ed.middile_name,''),' ',ifnull(ed.last_name,'')) ,site_attendance.site_fkey,site_t.day_time_seq_fkey 
              order by 1,2,3";
        } else {
            $str_query = "select site_attendance.site_fkey,designation.desig_name ,site.site_id,site.site_name,site.address,site.special_remarks,site.customer_name,site.customer_contact,day_time_desc,SUM(ifnull(site_attendance.duration,0)) as duration,site_t.modified_date,site_t.start_date_effective,site_t.end_date_effective,--
                            count(distinct site_attendance.att_date) as days,sales_rate,emp_rate,count(distinct site_attendance.emp_fkey) actual_empcount, emp_count,
                            (working_time1/60) shift_hours, ((sales_rate*(working_time1/60))*count(distinct site_attendance.att_date)) RATEPERHead,contacts.company_name,contacts.address,contacts.first_name,contacts.last_name,contacts.c_designation,contacts.phone,contacts.email

                            from site_attendance
                            left join site on (site.site_pkey = site_attendance.site_fkey )
                            left join site_transactions as site_t on (site_t.site_fkey = site.site_pkey )
                            left join contacts on (contacts.contact_id = site.contact_name)
                            left join designation on (designation.id = site_attendance.designation_id)
                            left join working_day_time_procedures on (site_attendance.day_time_seq_fkey=day_time_seq)
                            WHERE site_attendance.status=3 and site_attendance.active=1 and site_attendance.site_fkey=site_t.site_fkey and site_t.day_time_seq_fkey=site_attendance.day_time_seq_fkey
                            and site_t.designation_id=site_attendance.designation_id
                            and DATE_FORMAT(att_date ,'%Y-%m') = '$year' and '$year' between DATE_FORMAT(site_t.start_date_effective,'%Y-%m')
                                            and DATE_FORMAT(site_t.end_date_effective,'%Y-%m') and contact_id= '_contact_id'
                            group by site_attendance.site_fkey ,site_attendance.day_time_seq_fkey,site_attendance.designation_id
                            order by site_attendance.site_fkey";
        }

        // echo "<pre>";print_r($arr_leavepolicygroupids);echo "</pre>";
        // echo "<pre>";print_r($arr_form_data['EmployeeDetails']);echo "</pre>";

        if ($arr_leavepolicygroupids != '') {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_empleaverequests = array();
                // if ($arr_form_data['select-criteria1'] == 'Units') {
                // and lcase(head_type) not in ('manually','variable') and ectc.emp_fkey = '$leavepolicygroupid' and $condition and end_date_effective is null
                // debug($arr_empleaverequests);

                if ($arr_form_data['hidden-criteria1'] == 'Contacts') {

                    $key = $leavepolicygroupid;

                    // fetch employees in current branch
                    $arr_contact = $this->EmployeeDetails->query("select * from contacts where contact_id = '$leavepolicygroupid'");

                    // debug($arr_contact);
                    // loop fetched  query

                    foreach ($arr_contact as $contacts) {
                        $arr_empleaverequests = array();
                        $c_id = $contacts['contacts']['contact_id'];

                        $arr_results = $this->EmployeeDetails->query(str_replace("_contact_id", $c_id, $str_query));

                        //// debug($arr_results);
                        // debug($limit);
                        if (!empty($arr_results))
                            $arr_empleaverequests[] = $arr_results;

                        if (!empty($arr_results)) {
                            $arr_site_for_template[$key][] = array(
                                'summary' => $arr_empleaverequests,
                            );
                        }
                        if ($limit <= 10) {
                            if (!empty($arr_results)) {
                                $arr_site_for_template1[$key][] = array(
                                    'summary' => $arr_empleaverequests,
                                );
                                $limit = $limit + 1;
                            }
                        }
                    }
                } else if ($arr_form_data['hidden-criteria1'] == 'Site') {

                    // This is employee wise. 

                    $key = $leavepolicygroupid;

                    $temp_query = str_replace("_site_id", $leavepolicygroupid, $str_query);

                    $emp_ids = (isset($arr_form_data['EmployeeDetails'])) ? implode(",", $arr_form_data['EmployeeDetails']) : '';

                    $arr_results = $this->EmployeeDetails->query(str_replace("_emp_id", $emp_ids, $temp_query));

                    if (!empty($arr_results))
                        $arr_empleaverequests[] = $arr_results;

                    if (!empty($arr_results)) {
                        $arr_site_for_template[$key][] = array(
                            'summary' => $arr_empleaverequests,
                        );
                    }
                    if ($limit <= 10) {
                        if (!empty($arr_results)) {
                            $arr_site_for_template1[$key][] = array(
                                'summary' => $arr_empleaverequests,
                            );
                            $limit = $limit + 1;
                        }
                    }
                }
            }

            // debug($arr_empleaverequests);
        }
        $arr_date = date('d-m-Y H:i');
        // $monthNum = $year date();
        // debug(count($arr_salary_for_template));
        // debug($arr_site_for_template);
        $this->set('arr_site_for_template', $arr_site_for_template);
        $this->set('arr_site_for_template1', $arr_site_for_template1);
        $this->set('arr_date', $arr_date);
        $this->set('month', $month);
        // debug($arr_site_for_template);
        // $employee_attendance = array();
        // foreach ($arr_empleaverequests as $val) {
        //     $emp = $val['0']['ectc']['head_operator'];
        //     $emppk = $val['0']['ectc']['head_type'];
        //     $itempart = $val['0']['ectc']['item_part'];
        //     $employee_attendance[$emppk][$emp][$itempart][] = $val;
        // }
        // $this->set('employee_attendance', $employee_attendance);
        $this->set('arr_site_for_template', $arr_site_for_template);
        $cr = $arr_form_data['hidden-criteria1'];
        $this->set('cr', $cr);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('year', $year);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);

        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('clientreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('P', 'A2', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('SiteDetailReport(Actual).pdf', 'D');
                $this->render('clientreport');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "SiteDetailedReport(ActualRate)" . $month . ".xlsx" : "SiteDetailedReport(ActualRate)" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Site Detailed Report(Actual Rate) : " . $month . "");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'AB'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                if ($needEmployeeWiseReport) {
                    $worksheet->mergeCells('A1:N1');
                } else {
                    $worksheet->mergeCells('A1:AC1');
                }
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $msg = '';
                if ($arr_form_data['select-criteria1'] == 'Contacts') {
                    $msg = 'Belonging to a Client';
                } else if ($arr_form_data['select-criteria1'] == 'Units') {
                    $msg = 'Belonging to a Branch';
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $msg = 'Belonging to a Employee';
                }
                $worksheet->setCellValueByColumnAndRow(0, 2, "(" . $msg . " Report run by " . $user_name . "-" . $arr_date . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                if ($needEmployeeWiseReport) {
                    $worksheet->mergeCells('A2:N2');
                } else {
                    $worksheet->mergeCells('A2:AB2');
                }
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 3;

                for ($i = 0; $i < 28; $i++) {
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                }

                if ($needEmployeeWiseReport) {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Client Name');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Client Address');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Site Code');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Site Name');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Site Address');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Shift Hour');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Working Hours');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Days');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Sales Rate');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Expense Rate');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Value');
                } else {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Client Name');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Client Address');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Site Code');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Site Name');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Site Address');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Segment');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Contact Person');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Contact Person Designation');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Mobile Number');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Mail ID');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Contract Start Date');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Contract End Date');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, ' Last Modified Date');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Shift Policy');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Number of Employees');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, 'Shift Hour');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, 'Actual Number of Employees');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, 'Actual Working Hours');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(20) . $rowcount, 'Days');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(21) . $rowcount, 'Sales Rate');
                    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(22) . $rowcount, 'Sales Rate Per Head');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(22) . $rowcount, 'Expense Rate');

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(23) . $rowcount, 'Value');
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, 'Total Billing Value');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowcount, 'Wages Per Person');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, 'Total Wages');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(26) . $rowcount, 'Mark Up');
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(27) . $rowcount, 'Mark Up %');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(27, $rowcount)->getFont()->setBold(true);
                }

                $rowcount = 4;

                $i = 1;
                $grant_total_wages = 0;
                $grant_total_wages = 0;
                $total_value = 0;
                $total_w_p = 0;
                $total_mark_up = 0;
                $t_markup_pr = 0;
                $res_value = 0;
                $wages = 0;
                $markup = 0;
                $markup_percentage = 0;
                if (!empty($arr_site_for_template)) {
                    foreach ($arr_site_for_template as $value) {
                        // debug($value);

                        foreach ($value as $each) {
                            foreach ($each['summary']['0'] as $val) {
                                // debug($val);
                                # code...
                                // debug($each);
                                $number = $val['0']['actual_empcount'];
                                $shift_hours = ($val[0]['duration']);
                                $shift_days = $val[0]['days'];
                                $sales_rate = $val['site_attendance']['sales_rate'];
                                $erate = $val['site_attendance']['emp_rate'];
                                $rate_per_head = $erate * $shift_hours * $shift_days;
                                if ($rate_per_head != 0 && $shift_days != 0 && $shift_hours != 0) {
                                    $rate_per_hour = $erate * $shift_hours * $shift_days;
                                } else {
                                    $rate_per_hour = "0";
                                }
                                // debug($rate_per_hour);
                                $res_value = $erate */*$shift_days*/ $shift_hours /*$number*/;

                                $wages = ($erate * $shift_hours) / $number;
                                $total_wages = $wages * $number;
                                $markup = $res_value - $total_wages;
                                if ($res_value || $total_wages != 0) {
                                    $markup_percentage = (($res_value - $total_wages) / $total_wages) * 100;
                                } else {
                                    $markup_percentage = "0";
                                }
                                //    $grant_total_wages = $grant_total_wages + $total_wages;
                                //    $total_value = $total_value + $res_value;
                                //    $total_w_p = $total_w_p + $wages;
                                //    $total_mark_up = $total_mark_up + $markup;
                                $grant_total_wages = round($grant_total_wages) + round($total_wages);
                                $total_value = round($total_value) + round($res_value);
                                $total_w_p = round($total_w_p) + round($wages);
                                $total_mark_up = round($total_mark_up) + round($markup);
                                if ($total_value && $grant_total_wages != 0) {
                                    $t_markup_pr = round((($total_value - $grant_total_wages) / $grant_total_wages) * 100, 2);
                                } else {
                                    $t_markup_pr = "0";
                                }
                                $end_date = $val['site_t']['end_date_effective'];
                                $today = date('Y-m-d');
                                $closed_site = '';
                                if ($end_date < $today) {
                                    $closed_site = ' (Closed Site)';
                                }

                                if ($needEmployeeWiseReport) {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $val['contacts']['company_name']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $val['contacts']['address']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $val['site']['site_id']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $val['site']['site_name'] . $closed_site);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $val['site']['address']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $val['0']['EmpName']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $val['designation']['desig_name']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, round($val[0]['shift_hours'], 2));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, round($val[0]['duration'], 2));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $val[0]['days']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, round($val['site_attendance']['sales_rate'], 2));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, round($val['site_attendance']['emp_rate'], 2));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, round($res_value));
                                } else {
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $val['contacts']['company_name']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $val['contacts']['address']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $val['site']['site_id']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $val['site']['site_name'] . $closed_site);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $val['site']['address']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $val['site']['special_remarks']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $val['contacts']['first_name'] . ' ' . $val['contacts']['last_name']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $val['contacts']['c_designation']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $val['contacts']['phone']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $val['contacts']['email']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $val['site_t']['start_date_effective']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $val['site_t']['end_date_effective']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $val['site_t']['modified_date']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $val['working_day_time_procedures']['day_time_desc']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $val['designation']['desig_name']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $val['site_t']['emp_count']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, round($val[0]['shift_hours'], 2));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, $val[0]['actual_empcount']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, round($val[0]['duration'], 2));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(20) . $rowcount, $val[0]['days']);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(21) . $rowcount, round($val['site_attendance']['sales_rate'], 2));
                                    //                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(22) . $rowcount, $rate_per_head);
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(22) . $rowcount, round($val['site_attendance']['emp_rate'], 2));

                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(23) . $rowcount, round($res_value));
                                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnInde(19) . $rowcount, round($billing_value[$test['site']['site_id']]));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowcount, round($wages));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, round($total_wages));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(26) . $rowcount, round($markup));
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(27) . $rowcount, round($markup_percentage));
                                }



                                $i++;
                                $rowcount++;
                            }
                        }
                    }
                    // $rowcount++;
                    // $worksheet->mergeCells('A'.$rowcount.':T'.$rowcount);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Grand Total');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                    if ($needEmployeeWiseReport) {
                        $worksheet->mergeCells('A' . $rowcount . ':M' . $rowcount);
                    } else {
                        $worksheet->mergeCells('A' . $rowcount . ':W' . $rowcount);
                    }
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                    );

                    if ($needEmployeeWiseReport) {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $total_value);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                    } else {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(23) . $rowcount, $total_value);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(23, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowcount, $total_w_p);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(24, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, $grant_total_wages);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(25, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(26) . $rowcount, $total_mark_up);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(26, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(27) . $rowcount, round($t_markup_pr));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(27, $rowcount)->getFont()->setBold(true);
                    }
                } else {
                    $worksheet->mergeCells('A4:AB4');
                    $worksheet->getStyle('A4')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, 4, "There is no data available under the selected criteria.");

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 4)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 4)->getFont()->setSize(16);

                    //                for ($col = 'A'; $col !== 'Y'; $col++) {
                    //                    $objPHPExcel->getActiveSheet()
                    //                            ->getColumnDimension($col)
                    //                            ->setAutoSize(true);
                    //                }
                    //                $worksheet->mergeCells('A1:AB1');
                    //                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    //                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    //                );
                }

                $objPHPExcel->getActiveSheet()->setTitle('SiteDetailedReport(ActualRate)');
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
                break;
            default:
                $this->set('mode', '');
                $this->render('clientreport');
                break;
        }
    }

    private function generateRottaMaster($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');


        $start = $month . "-01";
        $end = date('Y-m-t', strtotime($start));

        $interval = new DateInterval('P1D');
        $format = 'd';
        $format1 = 'Y-m-d';
        $realEnd = new DateTime($end);
        $realEnd->add($interval);
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        // Use loop to store date into array 
        $array = array();
        foreach ($period as $date) {
            $array[] = $date->format($format);
        }
        foreach ($period as $date1) {
            $array1[] = $date1->format($format1);
        }
        $fd = $arr_form_data['reportfrom'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t H:m:s', strtotime($arr_form_data['reportfrom'] . ' ' . '23:00:00'));
        }

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $arr_siteattendance_for_template = array();
        $arr_siteattendance = array();
        ini_set('memory_limit', '-1');
        ini_set('memory_limit', '-1');
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition =  "and  emp_details.status in('1','2')";
        } else {
            $condition =  "and  emp_details.status  = 1";
        }

        // Edited by Akshay on 11-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = strtoupper($this->Session->read('company_code'));
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND emp_details.branch_code = '$is_ho' ";
            }
        }
        // End

        $sitedata = array();
        $array_sites = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $sitedata = array();
                if ($arr_form_data['select-criteria1'] == 'Site') {
                    $branch_wise = 0;
                    // Edited by Akshay on 11-2-2025
                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_id,site.site_name,site.payment_mode,emp_details.emp_pkey,emp_details.first_name,emp_details.last_name,emp_details.status,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      att_date,sum(site_attendance.duration)duration,site.site_pkey FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code and branches.status = 1)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$from%' and status=3 
                          $condition group by emp_fkey)
                          $branch_condition
                      group by site_attendance.emp_fkey
                      order by emp_details.first_name,att_date");
                    $arr_results_date = $this->EmployeeDetails->query("select emp_details.emp_pkey,emp_details.status,site_attendance.site_fkey,
                      att_date,sum(site_attendance.duration)duration FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$from%' and status=3 
                          $condition group by emp_fkey)
                          $branch_condition
                      group by site_attendance.emp_fkey,att_date
                      order by site_attendance.emp_fkey,att_date");
                    // End
                    if (!empty($arr_results_date))
                        $array_sites[] = $arr_results_date;
                } else if ($arr_form_data['select-criteria1'] == 'AccessSite') {
                    $branch_wise = 0;
                    // Edited by Akshay on 11-2-2025
                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_id,site.site_name,site.payment_mode,emp_details.emp_pkey,emp_details.first_name,emp_details.last_name,emp_details.status,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      att_date,sum(site_attendance.duration)duration,site.site_pkey FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code and branches.status = 1)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$from%' and status=3 $condition group by emp_fkey)
                      $branch_condition
                      group by site_attendance.emp_fkey
                      order by emp_details.first_name,att_date");
                    $arr_results_date = $this->EmployeeDetails->query("select emp_details.emp_pkey,emp_details.status,site_attendance.site_fkey,
                      att_date,sum(site_attendance.duration)duration FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$from%' and status=3 $condition group by emp_fkey)
                      $branch_condition
                      group by site_attendance.emp_fkey,att_date
                      order by site_attendance.emp_fkey,att_date");
                    // End
                    if (!empty($arr_results_date))
                        $array_sites[] = $arr_results_date;
                } else {
                    $branch_wise = 1;
                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_pkey,site.site_id,site.payment_mode,site.site_name,
                      emp_details.emp_pkey,emp_details.first_name,emp_details.last_name,emp_details.status,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      att_date,sum(site_attendance.duration)duration,branches.branch_code FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code and branches.status = 1)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND branches.branch_code = '$leavepolicygroupid' AND site.status=1 $condition
                      group by site_attendance.emp_fkey,site_attendance.site_fkey
                      order by site.site_name,emp_details.first_name,att_date");
                    $arr_results_date = $this->EmployeeDetails->query("select emp_details.emp_pkey,emp_details.status,branches.branch_code,
                      att_date,sum(site_attendance.duration)duration,site_attendance.site_fkey FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey)
                      left join branches on (branches.branch_code = emp_details.branch_code and branches.status = 1)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND branches.branch_code = '$leavepolicygroupid' $condition group by site_attendance.site_fkey,site_attendance.emp_fkey,att_date
                      order by site_attendance.emp_fkey,emp_details.first_name,att_date");

                    $pids = array();
                    foreach ($arr_results_date as $h) {
                        $pids[] = $h['site_attendance']['site_fkey'];
                    }
                    $uniquePids = array_unique($pids);
                    if (!empty($uniquePids))
                        $array_sites[] = array(
                            'branch' => $leavepolicygroupid,
                            'sites' => $uniquePids
                        );
                }
                foreach ($arr_results as $data) {
                    //                    debug($data);
                    $emp_key = $data['emp_details']['emp_pkey'];
                    $site_key = $data['site']['site_pkey'];
                    foreach ($arr_results_date as $dat) {
                        //                        debug($dat);
                        $emp_fkey = $dat['emp_details']['emp_pkey'];
                        $att_date = $dat['site_attendance']['att_date'];
                        $site_fkey = $dat['site_attendance']['site_fkey'];
                        if ($emp_key == $emp_fkey && $site_key == $site_fkey) {
                            $start = -2;
                            $date = substr($att_date, $start);
                            foreach ($array as $arr) {
                                $date1 = isset($sitedata[$emp_key][$site_key][$arr]) ? $sitedata[$emp_key][$site_key][$arr] : '0';
                                if ($date1 == 0) {
                                    if ($arr == $date) {
                                        $sitedata[$emp_key][$site_key][$arr] = $dat['0']['duration'];
                                    } else {
                                        $sitedata[$emp_key][$site_key][$arr] = 0;
                                    }
                                }
                            }
                        }
                    }
                }
                //                debug($sitedata);
                if (!empty($sitedata))
                    $arr_results['dates'] = $sitedata;

                if (!empty($arr_results))
                    $arr_siteattendance_for_template[] = array(
                        'summary' => $arr_results
                    );
            }
        }

        if ($arr_form_data['select-criteria1'] == 'Site') {
            $list_sites = array();
            foreach ($array_sites as $value) {
                $list_sites[] = $value[0]['site_attendance']['site_fkey'];
            }

            $contract_list = array();
            foreach ($list_sites as $leavepolicygroupid) {
                $contract_arr = array();
                foreach ($array1 as $arr) {
                    $total = 0;
                    $arr_results_hours = $this->EmployeeDetails->query("select  site.site_pkey,site_transactions.day_time_seq_fkey,
                      working_day_time_procedures.minuts_calc_perday,site_transactions.emp_count,
                      sum(round((working_day_time_procedures.minuts_calc_perday)/60,1)*site_transactions.emp_count) duration 
                      FROM `site_transactions` 
                      left join site on (site.site_pkey = site_transactions.site_fkey) 
                      left join working_day_time_procedures on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey)
                      WHERE '$arr' between site_transactions.start_date_effective and site_transactions.end_date_effective 
                      AND `site`.`site_pkey` = '$leavepolicygroupid' AND `site_transactions` .`status`=1 and site.status=1
                      group by site.site_pkey order by site.site_pkey");
                    $total = isset($arr_results_hours[0][0]['duration']) ? $arr_results_hours[0][0]['duration'] : 0;
                    if (count($arr) > 0) {
                        $contract_arr[] = array(
                            'date' => $arr,
                            'duration' => $total,
                            'site' => $leavepolicygroupid
                        );
                    }
                }
                $contract_list[] = $contract_arr;
            }
        } else if ($arr_form_data['select-criteria1'] == 'AccessSite') {
            $list_sites = array();
            foreach ($array_sites as $value) {
                $list_sites[] = $value[0]['site_attendance']['site_fkey'];
            }

            $contract_list = array();
            foreach ($list_sites as $leavepolicygroupid) {
                $contract_arr = array();
                foreach ($array1 as $arr) {
                    $total = 0;
                    $arr_results_hours = $this->EmployeeDetails->query("select  site.site_pkey,site_transactions.day_time_seq_fkey,
                      working_day_time_procedures.minuts_calc_perday,site_transactions.emp_count,
                      sum(round((working_day_time_procedures.minuts_calc_perday)/60,1)*site_transactions.emp_count) duration 
                      FROM `site_transactions` 
                      left join site on (site.site_pkey = site_transactions.site_fkey) 
                      left join working_day_time_procedures on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey)
                      WHERE '$arr' between site_transactions.start_date_effective and site_transactions.end_date_effective 
                      AND `site`.`site_pkey` = '$leavepolicygroupid' AND `site_transactions` .`status`=1 and site.status=1
                      group by site.site_pkey order by site.site_pkey");
                    $total = isset($arr_results_hours[0][0]['duration']) ? $arr_results_hours[0][0]['duration'] : 0;
                    if (count($arr) > 0) {
                        $contract_arr[] = array(
                            'date' => $arr,
                            'duration' => $total,
                            'site' => $leavepolicygroupid
                        );
                    }
                }
                $contract_list[] = $contract_arr;
            }
            foreach ($arr_form_data['AccessSite'] as $val) {
                $manager = isset($val) ? $val : '';
            }
            $arr_manager = $this->EmployeeDetails->query("select EmpName from employee_info where emp_pkey = $manager");
            $managername = $arr_manager['0']['employee_info']['EmpName'];
            $this->set('manager', $managername);
        } else {
            $contract_list = array();
            foreach ($array_sites as $value) {
                $branch = $value['branch'];
                $contract_arr = array();
                $site = $value['sites'];
                foreach ($array1 as $arr) {

                    $subtotal = 0;
                    foreach ($site as $values) {
                        $total = 0;
                        $arr_results_hours = $this->EmployeeDetails->query("select  site.site_pkey,site_transactions.day_time_seq_fkey,
                      working_day_time_procedures.minuts_calc_perday,site_transactions.emp_count,
                      sum(round((working_day_time_procedures.minuts_calc_perday)/60,1)*site_transactions.emp_count) duration 
                      FROM `site_transactions` 
                      left join site on (site.site_pkey = site_transactions.site_fkey) 
                      left join working_day_time_procedures on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey)
                      WHERE '$arr' between site_transactions.start_date_effective and site_transactions.end_date_effective 
                      AND `site`.`site_pkey` = '$values' AND `site_transactions` .`status`=1 and site.status=1
                      group by site.site_pkey order by site.site_pkey");
                        $total = isset($arr_results_hours[0][0]['duration']) ? $arr_results_hours[0][0]['duration'] : 0;
                        $subtotal = $subtotal + $total;
                    }
                    if (count($arr) > 0) {
                        $contract_arr[] = array(
                            'date' => $arr,
                            'duration' => $subtotal,
                            'branch' => $branch
                        );
                    }
                }
                $contract_list[] = $contract_arr;
            }
        }
        //          debug($contract_list); 
        $this->set('month', $arr_form_data['reportfrom']);
        $this->set('contract_list', $contract_list);
        $this->set('array', $array);
        $this->set('sitedata', $sitedata);
        $this->set('branch_wise', $branch_wise);
        $this->set('arr_siteattendance_for_template', $arr_siteattendance_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('criterias', $arr_form_data['select-criteria1']);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
         //edited by sinsiya on 15-09-2025
        $this->set('company_code', $company_code);
        switch ($mode) {
                //            case 'pdf' :
                //                $this->set('mode', 'pdf');
                //                $view = new View($this, false);
                //                $view_output = $view->render('detailedattendance');
                //                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //
                //                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                //                $html2pdf->pdf->SetDisplayMode('fullpage');
                //                $html2pdf->writeHTML($view_output);
                //                $html2pdf->Output('detailedattendance.pdf', 'D');
                //                //$this->render('reportshiftpolicy');                
                //                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "RotaMaster" . $fd . ".xlsx" : "RotaMaster" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Rota Master ");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Rota Master - " . $mname . "  "  . $year);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'AZ'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:J2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 3;
                if ($arr_form_data['select-criteria1'] == 'AccessSite') {
                    $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Site Manager / Superior :  ' . $managername);

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $rowcount = $rowcount + 1;
                }
                $i = 0;
                $i += 1;
                if (!empty($arr_siteattendance_for_template)) {
                    foreach ($arr_siteattendance_for_template as $values) {
                        //The below code is to reset the arrays for calculate VARIANCE... By ***on 22/7/2020
                        if (isset($list)) {
                            unset($list);
                            unset($arrays);
                        }
                        //////////////////////////////////////////////////////////////////////////////////////

                        $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount);
                        if ($branch_wise == 0) {
                            $site_pkey = $values['summary']['0']['site']['site_pkey'];
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Rota Master Report of ' . $values['summary']['0']['site']['site_name']);
                        } else {
                            $branch_code = $values['summary']['0']['branches']['branch_code'];
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Rota Master Report of ' . $values['summary']['0']['branches']['branch_name']);
                        }
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Client Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Site ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Site Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), 'Payment Mode');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $col = 9;
                        foreach ($array as $val) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col), ($rowcount), $val);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, ($rowcount))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $col++;
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col), ($rowcount), 'Total Hours');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col + 1), ($rowcount), 'No of Working Day');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                        $rowcount++;
                        $i = 0;
                         $mannedTotalnew = 0;
                        $date_array = array();
                        foreach ($values['summary'] as $value) {
                            $arr1 = '';
                            $emp = 0;
                            $i = $i + 1;
                            if (!empty($value['emp_details']['first_name'])) {
                                $empstatus = isset($value['emp_details']['status']) && $value['emp_details']['status'] == "2" ? '  (Resigned)' : '';
                                $name = $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . $empstatus;
                                $emp_id = $value['emp_proff']['emp_company_id'];
                                $branch = $value['branches']['branch_name'];
                                $designation = $value['designation']['desig_name'];
                                $company = $value["contacts"]['company_name'];
                                $site_id = $value['site']['site_id'];
                                $site_name = $value['site']['site_name'];
                                $emp = $value['emp_details']['emp_pkey'];
                                $site_key = $value['site']['site_pkey'];
                                if ($value['site']['payment_mode'] == '1') {
                                    $payment_mode = 'Bank';
                                }
                                if ($value['site']['payment_mode'] == '2') {
                                    $payment_mode = 'Cash';
                                }
                                $payment_mode = isset($payment_mode) ? $payment_mode : '';
                                $tot = 0;
                                $workings = 0;
                                $arr1 = isset($values['summary']['dates'][$emp][$site_key]) ? $values['summary']['dates'][$emp][$site_key] : '';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $i);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $name);
                                //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $emp_id);
                                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $rowcount, $emp_id, PHPExcel_Cell_DataType::TYPE_STRING);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $branch);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $designation);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $company);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $site_id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $site_name);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $payment_mode);
                                $col1 = 9;
                                if ($arr1 != '') {
                                    foreach ($arr1 as $key => $date) {
                                        $date_array[$key] = isset($date_array[$key]) ? $date_array[$key] : 0;
                                        $date_array[$key] += round($date,2); // Edited by Akshay on 2-4-2025
                                        if ($date > 0) {
                                            $workings++;
                                            //edited by sinsiya on 15-09-2025
                                            if($company_code == "ABSG"){
                                               $tot = $tot + $date; // Edited by Akshay on 2-4-2025
                                            }else{
                                               $tot = $tot + round($date);  
                                            }
                                        }
                                        if ($date == 0) {
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), '');
                                        } else {
                                             //edited by sinsiya on 15-09-2025
                                            if($company_code == "ABSG"){
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $date); 
                                            }else{
                                              $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), round($date));    // Edited by Akshay on 2-4-2025
                                            }
                                        }
                                        $col1 = $col1 + 1;
                                    }
                                }
                                //edited by sinsiya on 15-09-2025
                                 if($company_code == "ABSG"){  $mannedTotalnew += $tot;
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $tot);
                                 }else{
                                $mannedTotalnew += round($tot);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), round($tot));
                                 }
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1 + 1), ($rowcount), $workings);
                            }
                            $rowcount++;
                        }
                        $rowcount--;
                        /////////MANNED HOURS , CONTRACTED HOURS , VARIANCE. BY *** on 23/7/2020
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'MANNED HOURS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount . '');
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $mannedTotal = 0;
                        $col1 = 9;
                        foreach ($date_array as $key => $val) {
                            $mannedTotal = $mannedTotal + $val;
                            $arrays['manned'][] = $val;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $val);
                            $col1 = $col1 + 1;
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), round($mannedTotalnew));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col1), ($rowcount))->getFont()->setBold(true);

                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'CONTRACTED HOURS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount . '');
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $contractsTotal = 0;
                        $col1 = 9;
                        if ($branch_wise == 0) {
                            foreach ($contract_list as $val) {
                                if ($val[0]['site'] == $site_pkey) {
                                    foreach ($val as $key => $set) {
                                        $contractsTotal = $contractsTotal + $set['duration'];
                                        $arrays['contract'][] = $set['duration'];
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $set['duration']);
                                        $col1 = $col1 + 1;
                                    }
                                }
                            }
                        } else {
                            foreach ($contract_list as $val) {
                                if ($val[0]['branch'] == $branch_code) {
                                    foreach ($val as $set) {
                                        $contractsTotal = $contractsTotal + $set['duration'];
                                        $arrays['contract'][] = $set['duration']; //debug($arrays);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $set['duration']);
                                        $col1 = $col1 + 1;
                                    }
                                }
                            }
                        }
                        $list[] = $arrays;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), round($contractsTotal,1));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col1), ($rowcount))->getFont()->setBold(true);

                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'VARIANCE');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount . '');
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $var = 0;
                        $contracts = array();
                        $manned = array();
                        $totalHoursOfvariance = 0;
                        $col1 = 9;
                        foreach ($list as $key => $val) {
                            foreach ($val as $head => $value) {
                                if ($head == "contract") {
                                    $contracts = $value;
                                } else if ($head == "manned") {
                                    $manned = $value;
                                }
                            }
                            for ($i = 0; $i < count($manned); $i++) {
                                $var = $contracts[$i] - $manned[$i];
                                $totalHoursOfvariance = $totalHoursOfvariance + $var;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), round($var));
                                $col1 = $col1 + 1;
                            }
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), round($totalHoursOfvariance));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col1), ($rowcount))->getFont()->setBold(true);

                        $rowcount = $rowcount + 2;
                    }
                } else {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records found under this Criteria');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount . '');
                    $worksheet->getStyle('A' . $rowcount . '')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                }
                $objPHPExcel->getActiveSheet()->setTitle('Rota Master');
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
                break;
            default:
                $this->set('mode', '');
                $this->render('rottamaster');
                break;
        }
    }

    private function generateRottaMasternew($mode)
    {
        $arr_form_data = $_REQUEST;
        // debug($mode);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code')); //company_code
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');


        $start = $month . "-01";
        $end = date('Y-m-t', strtotime($start));

        $interval = new DateInterval('P1D');
        $format = 'd';
        $format1 = 'Y-m-d';
        $realEnd = new DateTime($end);
        $realEnd->add($interval);
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        // Use loop to store date into array 
        $array = array();
        foreach ($period as $date) {
            $array[] = $date->format($format);
        }
        foreach ($period as $date1) {
            $array1[] = $date1->format($format1);
        }
        $fd = $arr_form_data['reportfrom'];
        // $Td=$arr_form_data['reportto'].' '.'00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t H:m:s', strtotime($arr_form_data['reportfrom'] . ' ' . '23:00:00'));
        }

        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            // $arr_leavepolicygroupids =$arr_form_data[$str_criteria_item];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $arr_siteattendance_for_template = array();
        $arr_siteattendance = array();
        ini_set('memory_limit', '-1');
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition =  "and  emp_details.status in('1','2')";
        } else {
            $condition =  "and  emp_details.status  = 1";
        }
        $sitedata = array();
        $array_sites = array();

        // Edited by Akshay on 12-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND emp_details.branch_code = '$is_ho' ";
            }
        }
        // End

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $sitedata = array();
                if ($arr_form_data['select-criteria1'] == 'Site') {
                    $branch_wise = 0;
                    // Edited by Akshay on 12-2-2025
                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_id,site.site_name,site.payment_mode,emp_details.emp_pkey,emp_details.first_name,emp_details.last_name,emp_details.status,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      att_date,sum(site_attendance.duration)duration,site.site_pkey FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code and branches.status = 1)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$from%' and status=3 $condition group by emp_fkey)
                      $branch_condition
                      group by site_attendance.emp_fkey
                      order by emp_details.first_name,att_date");
                    $arr_results_date = $this->EmployeeDetails->query("select emp_details.emp_pkey,emp_details.status,site_attendance.site_fkey,
                      att_date,sum(site_attendance.duration)duration FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$from%' and status=3 $condition group by emp_fkey)
                      $branch_condition
                      group by site_attendance.emp_fkey,att_date
                      order by site_attendance.emp_fkey,att_date");
                    // End
                    if (!empty($arr_results_date))
                        $array_sites[] = $arr_results_date;
                } else if ($arr_form_data['select-criteria1'] == 'AccessSite') {
                    $branch_wise = 0;
                    // Edited by Akshay on 12-2-2025
                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_id,site.site_name,site.payment_mode,emp_details.emp_pkey,emp_details.first_name,emp_details.last_name,emp_details.status,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      att_date,sum(site_attendance.duration)duration,site.site_pkey FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code and branches.status = 1)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$from%' and status=3 $condition group by emp_fkey)
                      $branch_condition
                      group by site_attendance.emp_fkey
                      order by emp_details.first_name,att_date");
                    $arr_results_date = $this->EmployeeDetails->query("select emp_details.emp_pkey,emp_details.status,site_attendance.site_fkey,
                      att_date,sum(site_attendance.duration)duration FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$from%' and status=3 $condition group by emp_fkey)
                      $branch_condition
                      group by site_attendance.emp_fkey,att_date
                      order by site_attendance.emp_fkey,att_date");
                    // End
                    if (!empty($arr_results_date))
                        $array_sites[] = $arr_results_date;
                } else {
                    $branch_wise = 1;
                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_pkey,site.site_id,site.payment_mode,site.site_name,
                      emp_details.emp_pkey,emp_details.first_name,emp_details.last_name,emp_details.status,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      att_date,sum(site_attendance.duration)duration,branches.branch_code FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code and branches.status = 1)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND branches.branch_code = '$leavepolicygroupid' AND site.status=1 $condition
                      group by site_attendance.emp_fkey,site_attendance.site_fkey
                      order by site.site_name,emp_details.first_name,att_date");
                    $arr_results_date = $this->EmployeeDetails->query("select emp_details.emp_pkey,emp_details.status,branches.branch_code,
                      att_date,sum(site_attendance.duration)duration,site_attendance.site_fkey FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey)
                      left join branches on (branches.branch_code = emp_details.branch_code and branches.status = 1)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 
                      AND branches.branch_code = '$leavepolicygroupid' $condition group by site_attendance.site_fkey,site_attendance.emp_fkey,att_date
                      order by site_attendance.emp_fkey,att_date");

                    $pids = array();
                    foreach ($arr_results_date as $h) {
                        $pids[] = $h['site_attendance']['site_fkey'];
                    }
                    $uniquePids = array_unique($pids);
                    if (!empty($uniquePids))
                        $array_sites[] = array(
                            'branch' => $leavepolicygroupid,
                            'sites' => $uniquePids
                        );
                }
                foreach ($arr_results as $data) {
                    //                    debug($data);
                    $emp_key = $data['emp_details']['emp_pkey'];
                    $site_key = $data['site']['site_pkey'];
                    foreach ($arr_results_date as $dat) {
                        //                        debug($dat);
                        $emp_fkey = $dat['emp_details']['emp_pkey'];
                        $att_date = $dat['site_attendance']['att_date'];
                        $site_fkey = $dat['site_attendance']['site_fkey'];
                        if ($emp_key == $emp_fkey && $site_key == $site_fkey) {
                            $start = -2;
                            $date = substr($att_date, $start);
                            foreach ($array as $arr) {
                                $date1 = isset($sitedata[$emp_key][$site_key][$arr]) ? $sitedata[$emp_key][$site_key][$arr] : '0';
                                if ($date1 == 0) {
                                    if ($arr == $date) {
                                        $sitedata[$emp_key][$site_key][$arr] = $dat['0']['duration'];
                                    } else {
                                        $sitedata[$emp_key][$site_key][$arr] = 0;
                                    }
                                }
                            }
                        }
                    }
                }
                //                debug($sitedata);
                if (!empty($sitedata))
                    $arr_results['dates'] = $sitedata;

                if (!empty($arr_results))
                    $arr_siteattendance_for_template[] = array(
                        'summary' => $arr_results
                    );
            }
        }

        if ($arr_form_data['select-criteria1'] == 'Site') {
            $list_sites = array();
            foreach ($array_sites as $value) {
                $list_sites[] = $value[0]['site_attendance']['site_fkey'];
            }

            $contract_list = array();
            foreach ($list_sites as $leavepolicygroupid) {
                $contract_arr = array();
                foreach ($array1 as $arr) {
                    $total = 0;
                    $arr_results_hours = $this->EmployeeDetails->query("select  site.site_pkey,site_transactions.day_time_seq_fkey,
                      working_day_time_procedures.minuts_calc_perday,site_transactions.emp_count,
                      sum(round((working_day_time_procedures.minuts_calc_perday)/60,0)*site_transactions.emp_count) duration 
                      FROM `site_transactions` 
                      left join site on (site.site_pkey = site_transactions.site_fkey) 
                      left join working_day_time_procedures on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey)
                      WHERE '$arr' between site_transactions.start_date_effective and site_transactions.end_date_effective 
                      AND `site`.`site_pkey` = '$leavepolicygroupid' AND `site_transactions` .`status`=1 and site.status=1
                      group by site.site_pkey order by site.site_pkey");
                    $total = isset($arr_results_hours[0][0]['duration']) ? $arr_results_hours[0][0]['duration'] : 0;
                    if (count($arr) > 0) {
                        $contract_arr[] = array(
                            'date' => $arr,
                            'duration' => $total,
                            'site' => $leavepolicygroupid
                        );
                    }
                }
                $contract_list[] = $contract_arr;
            }
        } else if ($arr_form_data['select-criteria1'] == 'AccessSite') {
            $list_sites = array();
            foreach ($array_sites as $value) {
                $list_sites[] = $value[0]['site_attendance']['site_fkey'];
            }

            $contract_list = array();
            foreach ($list_sites as $leavepolicygroupid) {
                $contract_arr = array();
                foreach ($array1 as $arr) {
                    $total = 0;
                    $arr_results_hours = $this->EmployeeDetails->query("select  site.site_pkey,site_transactions.day_time_seq_fkey,
                      working_day_time_procedures.minuts_calc_perday,site_transactions.emp_count,
                      sum(round((working_day_time_procedures.minuts_calc_perday)/60,0)*site_transactions.emp_count) duration 
                      FROM `site_transactions` 
                      left join site on (site.site_pkey = site_transactions.site_fkey) 
                      left join working_day_time_procedures on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey)
                      WHERE '$arr' between site_transactions.start_date_effective and site_transactions.end_date_effective 
                      AND `site`.`site_pkey` = '$leavepolicygroupid' AND `site_transactions` .`status`=1 and site.status=1
                      group by site.site_pkey order by site.site_pkey");
                    $total = isset($arr_results_hours[0][0]['duration']) ? $arr_results_hours[0][0]['duration'] : 0;
                    if (count($arr) > 0) {
                        $contract_arr[] = array(
                            'date' => $arr,
                            'duration' => $total,
                            'site' => $leavepolicygroupid
                        );
                    }
                }
                $contract_list[] = $contract_arr;
            }
            foreach ($arr_form_data['AccessSite'] as $val) {
                $manager = isset($val) ? $val : '';
            }
            $arr_manager = $this->EmployeeDetails->query("select EmpName from employee_info where emp_pkey = $manager");
            $managername = $arr_manager['0']['employee_info']['EmpName'];
            $this->set('manager', $managername);
        } else {
            $contract_list = array();
            foreach ($array_sites as $value) {
                $branch = $value['branch'];
                $contract_arr = array();
                $site = $value['sites'];
                foreach ($array1 as $arr) {

                    $subtotal = 0;
                    foreach ($site as $values) {
                        $total = 0;
                        $arr_results_hours = $this->EmployeeDetails->query("select  site.site_pkey,site_transactions.day_time_seq_fkey,
                      working_day_time_procedures.minuts_calc_perday,site_transactions.emp_count,
                      sum(round((working_day_time_procedures.minuts_calc_perday)/60,0)*site_transactions.emp_count) duration 
                      FROM `site_transactions` 
                      left join site on (site.site_pkey = site_transactions.site_fkey) 
                      left join working_day_time_procedures on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey)
                      WHERE '$arr' between site_transactions.start_date_effective and site_transactions.end_date_effective 
                      AND `site`.`site_pkey` = '$values' AND `site_transactions` .`status`=1 and site.status=1
                      group by site.site_pkey order by site.site_pkey");
                        $total = isset($arr_results_hours[0][0]['duration']) ? $arr_results_hours[0][0]['duration'] : 0;
                        $subtotal = $subtotal + $total;
                    }
                    if (count($arr) > 0) {
                        $contract_arr[] = array(
                            'date' => $arr,
                            'duration' => $subtotal,
                            'branch' => $branch
                        );
                    }
                }
                $contract_list[] = $contract_arr;
            }
        }
        //          debug($contract_list); 
        $this->set('month', $arr_form_data['reportfrom']);
        $this->set('contract_list', $contract_list);
        $this->set('array', $array);
        $this->set('sitedata', $sitedata);
        $this->set('branch_wise', $branch_wise);
        $this->set('arr_siteattendance_for_template', $arr_siteattendance_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('criterias', $arr_form_data['select-criteria1']);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        //edited by sinsiya on 15-09-2025
        $this->set('company_code', $company_code);
        switch ($mode) {
                //            case 'pdf' :
                //                $this->set('mode', 'pdf');
                //                $view = new View($this, false);
                //                $view_output = $view->render('detailedattendance');
                //                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //
                //                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                //                $html2pdf->pdf->SetDisplayMode('fullpage');
                //                $html2pdf->writeHTML($view_output);
                //                $html2pdf->Output('detailedattendance.pdf', 'D');
                //                //$this->render('reportshiftpolicy');                
                //                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "RotaMaster" . $fd . ".xlsx" : "RotaMaster" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Rota Master ");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Rota Master_New - " . $mname . "  "  . $year);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'AZ'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                $worksheet->mergeCells('A1:J1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:J2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $rowcount = 3;
                if ($arr_form_data['select-criteria1'] == 'AccessSite') {
                    $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Site Manager / Superior :  ' . $managername);

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $rowcount = $rowcount + 1;
                }
                $i = 0;
                $i += 1;
                if (!empty($arr_siteattendance_for_template)) {
                    foreach ($arr_siteattendance_for_template as $values) {
                        //The below code is to reset the arrays for calculate VARIANCE... By ***on 22/7/2020
                        if (isset($list)) {
                            unset($list);
                            unset($arrays);
                        }
                        //////////////////////////////////////////////////////////////////////////////////////

                        $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount);
                        if ($branch_wise == 0) {
                            $site_pkey = $values['summary']['0']['site']['site_pkey'];
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Rota Master Report of ' . $values['summary']['0']['site']['site_name']);
                        } else {
                            $branch_code = $values['summary']['0']['branches']['branch_code'];
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Rota Master Report of ' . $values['summary']['0']['branches']['branch_name']);
                        }
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        );
                        $rowcount = $rowcount + 1;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Client Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Site ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Site Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), 'Payment Mode');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $col = 9;
                        foreach ($array as $val) {
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col), ($rowcount), $val);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, ($rowcount))->getFont()->setBold(true);
                            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                            $col++;
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col), ($rowcount), 'Total Hours');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col + 1), ($rowcount), 'No of Working Day');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                        $rowcount++;
                        $i = 0;
                        $date_array = array();
                        foreach ($values['summary'] as $value) {
                            $arr1 = '';
                            $emp = 0;
                            $i = $i + 1;
                            if (!empty($value['emp_details']['first_name'])) {
                                $empstatus = isset($value['emp_details']['status']) && $value['emp_details']['status'] == "2" ? '  (Resigned)' : '';
                                $name = $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . $empstatus;
                                $emp_id = $value['emp_proff']['emp_company_id'];
                                $branch = $value['branches']['branch_name'];
                                $designation = $value['designation']['desig_name'];
                                $company = $value["contacts"]['company_name'];
                                $site_id = $value['site']['site_id'];
                                $site_name = $value['site']['site_name'];
                                $emp = $value['emp_details']['emp_pkey'];
                                $site_key = $value['site']['site_pkey'];
                                if ($value['site']['payment_mode'] == '1') {
                                    $payment_mode = 'Bank';
                                }
                                if ($value['site']['payment_mode'] == '2') {
                                    $payment_mode = 'Cash';
                                }
                                $payment_mode = isset($payment_mode) ? $payment_mode : '';
                                $tot = 0;
                                $workings = 0;
                                $arr1 = isset($values['summary']['dates'][$emp][$site_key]) ? $values['summary']['dates'][$emp][$site_key] : '';
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((0), ($rowcount), $i);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), $name);
                                //                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), $emp_id);
                                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $rowcount, $emp_id, PHPExcel_Cell_DataType::TYPE_STRING);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), $branch);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), $designation);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), $company);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), $site_id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), $site_name);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), $payment_mode);
                                $col1 = 9;
                                if ($arr1 != '') {
                                    foreach ($arr1 as $key => $date) {
                                        $date_array[$key] = isset($date_array[$key]) ? $date_array[$key] : 0;
                                        $date_array[$key] += round($date); // Edited by Akshay on 2-4-2025
                                        if ($date > 0) {
                                            $workings++;
                                           // $tot = $tot + round($date); // Edited by Akshay on 2-4-2025
                                            //edited by sinsiya on 15-09-2025
                                            if($company_code == "ABSG"){
                                               $tot = $tot + $date; // Edited by Akshay on 2-4-2025
                                            }else{
                                               $tot = $tot + round($date);  
                                            }
                                        }
                                        if ($date == 0) {
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), '');
                                        } else {
                                           // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), round($date)); // Edited by Akshay on 2-4-2025
                                         //edited by sinsiya on 15-09-2025
                                            if($company_code == "ABSG"){
                                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $date); 
                                            }else{
                                              $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), round($date));    // Edited by Akshay on 2-4-2025
                                            }
                                        }
                                        $col1 = $col1 + 1;
                                    }
                                }
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $tot);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1 + 1), ($rowcount), $workings);
                            }
                            $rowcount++;
                        }
                        $rowcount--;
                        /////////MANNED HOURS , CONTRACTED HOURS , VARIANCE. BY *** on 23/7/2020
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'MANNED HOURS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount . '');
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $mannedTotal = 0;
                        $col1 = 9;
                        foreach ($date_array as $key => $val) {
                            $mannedTotal = $mannedTotal + $val;
                            $arrays['manned'][] = $val;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $val);
                            $col1 = $col1 + 1;
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $mannedTotal);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col1), ($rowcount))->getFont()->setBold(true);

                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'CONTRACTED HOURS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount . '');
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $contractsTotal = 0;
                        $col1 = 9;
                        if ($branch_wise == 0) {
                            foreach ($contract_list as $val) {
                                if ($val[0]['site'] == $site_pkey) {
                                    foreach ($val as $key => $set) {
                                        $contractsTotal = $contractsTotal + $set['duration'];
                                        $arrays['contract'][] = $set['duration'];
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $set['duration']);
                                        $col1 = $col1 + 1;
                                    }
                                }
                            }
                        } else {
                            foreach ($contract_list as $val) {
                                if ($val[0]['branch'] == $branch_code) {
                                    foreach ($val as $set) {
                                        $contractsTotal = $contractsTotal + $set['duration'];
                                        $arrays['contract'][] = $set['duration']; //debug($arrays);
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $set['duration']);
                                        $col1 = $col1 + 1;
                                    }
                                }
                            }
                        }
                        $list[] = $arrays;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $contractsTotal);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col1), ($rowcount))->getFont()->setBold(true);

                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'VARIANCE');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount . '');
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $var = 0;
                        $contracts = array();
                        $manned = array();
                        $totalHoursOfvariance = 0;
                        $col1 = 9;
                        foreach ($list as $key => $val) {
                            foreach ($val as $head => $value) {
                                if ($head == "contract") {
                                    $contracts = $value;
                                } else if ($head == "manned") {
                                    $manned = $value;
                                }
                            }
                            for ($i = 0; $i < count($manned); $i++) {
                                $var = $contracts[$i] - $manned[$i];
                                $totalHoursOfvariance = $totalHoursOfvariance + $var;
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $var);
                                $col1 = $col1 + 1;
                            }
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $totalHoursOfvariance);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col1), ($rowcount))->getFont()->setBold(true);

                        $rowcount++;

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'ACTUAL HOURS');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount . '');
                        $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
                        );
                        $actualTotal = 0;
                        $col1 = 9;
                        foreach ($date_array as $key => $val) {
                            $val = (float)$val;
                            $val1 = explode(".", $val);
                            $val1[1] = isset($val1[1]) ? $val1[1] : 0;
                            if (strlen($val1[1]) < 2) {
                                $val1[1] = $val1[1] . '0';
                            }
                            $val2 = round($val1[1] * 60 / 100);
                            if (strlen($val2) > 2) {
                                $val2 = substr($val2, 0, 2);
                            }
                            $value = $val1[0] . ':' . $val2;
                            $arrays['actualhour'][] = $value;
                            $actualTotal = $actualTotal + $val;
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $value);
                            $col1 = $col1 + 1;
                        }
                        $actualTotal = (float)$actualTotal;
                        $val1 = explode(".", $actualTotal);
                        $val1[1] = isset($val1[1]) ? $val1[1] : 0;
                        if (strlen($val1[1]) < 2) {
                            $val1[1] = $val1[1] . '0';
                        } else {
                            $val1[1] = substr($val1[1], 0, 2) . '.' . substr($val1[1], 2, 1);
                        }
                        $val2 = $val1[1] * 60 / 100;
                        if (strlen($val2) > 2) {
                            $val2 = round($val2);
                            $val2 = substr($val2, 0, 2);
                        }
                        $actualTotal = $val1[0] . ":" . $val2;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col1), ($rowcount), $actualTotal);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($col1), ($rowcount))->getFont()->setBold(true);

                        $rowcount++;
                    }
                    $col1++;
                    $columnString = PHPExcel_Cell::stringFromColumnIndex($col1);
                    $BStyle = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $row = $rowcount - 1;
                    $objPHPExcel->getActiveSheet()->getStyle('A1:' . $columnString . $row)->applyFromArray($BStyle);
                } else {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records found under this Criteria');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount . '');
                    $worksheet->getStyle('A' . $rowcount . '')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                }
                $objPHPExcel->getActiveSheet()->setTitle('Rota Master_New');
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
                break;
            default:
                $this->set('mode', '');
                $this->render('rottamasternew');
                break;
        }
    }

    private function generateSiteRate($mode)
    {
        $arr_form_data = $_REQUEST;
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');


        $start = $month . "-01";
        $end = date('Y-m-t', strtotime($start));

        // Edited by Akshay on 21-8-2025
        $company_code = strtoupper($this->Session->read('company_code'));
        $lastDay = date("d", strtotime("last day of $start"));
        if ($company_code == 'GLET' || $company_code == 'ABSG' || $company_code == 'SCRT') {
            $start_date = strtotime($start);
            if ($start_date < strtotime("2025-08-01")) {
                $eratess_column = "site_transactions.eratess_31";
            } else {
                $days_in_month = date("t", $start_date); // t = number of days in the month
                $eratess_column = "site_transactions.eratess_" . $days_in_month;
            }

            $select_eratess = $eratess_column;
        } else {
            $select_eratess = "site_transactions.eratess";
        }
        // End

        $fd = $arr_form_data['reportfrom'];

        $monthdate = date('M-Y', strtotime($arr_form_data['reportfrom']));
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition =  "and  emp_details.status in('1','2')";
        } else {
            $condition =  "and  emp_details.status  = 1";
        }
        $arr_siteattendance_for_template = array();
        $arr_siteattendance = array();
        ini_set('memory_limit', '-1');
        $sitedata = array();
        $array_sites = array();

        // Edited by Akshay on 12-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND emp_details.branch_code = '$is_ho' ";
            }
        }
        // End

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $sitedata = array();
                if ($arr_form_data['select-criteria1'] == 'Site') {
                    // Edited by Akshay on 12-2-2025
                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_id,site.site_name,count(site_attendance.att_date) days,sum(site_attendance.duration) hours,emp_details.emp_pkey pkey,site_attendance.designation_id des,site_transactions.day_time_seq_fkey seq,
                      emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      site.site_pkey,working_day_time_procedures.day_time_desc,round((working_day_time_procedures.minuts_calc_perday)/60,0) perday,
                      site_transactions.srate,
                      $select_eratess AS eratess,
                      site_transactions.start_date_effective,site_transactions.end_date_effective
                      FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join site_transactions on (site.site_pkey = site_transactions.site_fkey)
                      left join working_day_time_procedures on(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) 
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1 $condition
                      AND `site_attendance`.`site_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where site_fkey = '$leavepolicygroupid' and att_date like '%$month%' and status=3 and active=1 
                        group by emp_fkey) and designation.id=site_attendance.designation_id and site_attendance.day_time_seq_fkey = working_day_time_procedures.day_time_seq
                        $branch_condition
                      group by site_attendance.emp_fkey ,site.site_pkey  ,site_attendance.day_time_seq_fkey ,site_attendance.designation_id 
                      order by emp_details.first_name,att_date");
                    // End

                    if (!empty($arr_results))
                        $arr_siteattendance_for_template[] = array(
                            'summary' => $arr_results
                        );
                } else if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {

                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_id,site.site_name,count(site_attendance.att_date) days,sum(site_attendance.duration) hours,emp_details.emp_pkey pkey,site_attendance.designation_id des,site_transactions.day_time_seq_fkey seq,
                      emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      site.site_pkey,working_day_time_procedures.day_time_desc,round((working_day_time_procedures.minuts_calc_perday)/60,0) perday,
                      site_transactions.srate,
                      $select_eratess AS eratess, 
                      site_transactions.start_date_effective,site_transactions.end_date_effective  FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join site_transactions on (site.site_pkey = site_transactions.site_fkey)
                      left join working_day_time_procedures on(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) 
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3 and site_attendance.active=1  $condition
                      AND `site_attendance`.`emp_fkey` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance where emp_fkey = '$leavepolicygroupid' and att_date like '%$month%' and status=3 and active=1
                        group by emp_fkey) and designation.id=site_attendance.designation_id and site_attendance.day_time_seq_fkey = working_day_time_procedures.day_time_seq
                      group by site_attendance.emp_fkey ,site.site_pkey  ,site_attendance.day_time_seq_fkey ,site_attendance.designation_id 
                      order by emp_details.first_name,att_date");

                    if (!empty($arr_results))
                        $arr_siteattendance_for_template[] = array(
                            'summary' => $arr_results
                        );
                } else {

                    $arr_results = $this->EmployeeDetails->query("select contacts.company_name,site.site_id,site.site_name,count(site_attendance.att_date) days,sum(site_attendance.duration) hours,emp_details.emp_pkey pkey,site_attendance.designation_id des,site_transactions.day_time_seq_fkey seq,
                      emp_details.first_name,emp_details.last_name,branches.branch_name,emp_proff.emp_company_id,designation.desig_name ,
                      site.site_pkey,working_day_time_procedures.day_time_desc,round((working_day_time_procedures.minuts_calc_perday)/60,0) perday,
                      site_transactions.srate,
                      $select_eratess AS eratess, 
                      site_transactions.start_date_effective,site_transactions.end_date_effective FROM `site_attendance` 
                      left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                      left join branches on (branches.branch_code = emp_details.branch_code)
                      left join emp_proff on (emp_proff.emp_fkey = site_attendance.emp_fkey) 
                      left join designation on (designation.id = site_attendance.designation_id)
                      left join site on (site.site_pkey = site_attendance.site_fkey)
                      left join site_transactions on (site.site_pkey = site_transactions.site_fkey)
                      left join working_day_time_procedures on(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) 
                      left join contacts on (contacts.contact_id = site.contact_name)
                      WHERE `site_attendance`.`att_date` like '%$month%'  and site_attendance.status=3  and site_attendance.active=1 $condition
                      AND `emp_details`.`branch_code` = '$leavepolicygroupid' and `site_attendance`.`emp_fkey` in 
                      (select emp_fkey from site_attendance left join emp_details on (emp_details.emp_pkey = site_attendance.emp_fkey) 
                       where emp_details.branch_code = '$leavepolicygroupid' and att_date like '%$month%' and site_attendance.status=3 and active=1
                        group by emp_fkey) and  designation.id=site_attendance.designation_id and site_attendance.day_time_seq_fkey = working_day_time_procedures.day_time_seq
                      group by site_attendance.emp_fkey ,site.site_pkey  ,site_attendance.day_time_seq_fkey ,site_attendance.designation_id 
                      order by emp_details.first_name,att_date");

                    if (!empty($arr_results))
                        $arr_siteattendance_for_template[] = array(
                            'summary' => $arr_results
                        );
                }
            }
        }
        //  debug($arr_siteattendance_for_template);
        $this->set('month', $arr_form_data['reportfrom']);
        $this->set('monthdate', $monthdate);
        $this->set('startmonth', $start);
        $this->set('sitedata', $sitedata);

        $this->set('arr_siteattendance_for_template', $arr_siteattendance_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('criterias', $cr);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
                //            case 'pdf' :
                //                $this->set('mode', 'pdf');
                //                $view = new View($this, false);
                //                $view_output = $view->render('detailedattendance');
                //                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //
                //                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                //                $html2pdf->pdf->SetDisplayMode('fullpage');
                //                $html2pdf->writeHTML($view_output);
                //                $html2pdf->Output('detailedattendance.pdf', 'D');
                //                //$this->render('reportshiftpolicy');                
                //                break; 
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "SiteRateDetails" . $fd . ".xlsx" : "SiteRateDetails" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Site Rate Details");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Site Rate Details - " . $monthdate);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(17);

                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                $worksheet->mergeCells('A1:Z1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 2, "( Report run by " . $user_id . " - " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:Z2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $columncount = 0;
                $rowcount = 3;



                //                            $worksheet->mergeCells('A' . $rowcount . ':Z' . $rowcount);
                //                             if($cr == 'Site'){ 
                //			    $site_name = isset($value['summary']['0']['site']['site_name']) ? $value['summary']['0']['site']['site_name'] . " - " . $value['summary']['0']['site']['site_id'] : '';
                //                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Details of - : ' . $site_name);
                //                             }else if($cr == 'EmployeeDetails'){
                //                            $emp_name = isset($value['summary']['0']['emp_details']['first_name']) ? $value['summary']['0']['emp_details']['first_name']. " " . $value['summary']['0']['emp_details']['last_name']:'';
                //                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Details of - : ' . $emp_name);
                //                            }else{
                //                            $branch_name = isset($value['summary']['0']['branches']['branch_name']) ? $value['summary']['0']['branches']['branch_name']:'';
                //                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Details of - : ' . $branch_name);
                //                             }
                //                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                //                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, ($rowcount))->getFont()->setSize(14);


                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $rowcount, 'Employee Details')
                    ->setCellValue('K' . $rowcount, 'Standard Rate')
                    ->setCellValue('Q' . $rowcount, 'Actual Rate')
                    ->setCellValue('W' . $rowcount, 'Variance');
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':J' . $rowcount);
                $objPHPExcel->getActiveSheet()->mergeCells('K' . $rowcount . ':P' . $rowcount);
                $objPHPExcel->getActiveSheet()->mergeCells('Q' . $rowcount . ':V' . $rowcount);
                $objPHPExcel->getActiveSheet()->mergeCells('W' . $rowcount . ':Z' . $rowcount);


                $worksheet->mergeCells('A' . $rowcount . ':J' . $rowcount);
                $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('K' . $rowcount . ':P' . $rowcount);
                $worksheet->getStyle('K' . $rowcount)->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('Q' . $rowcount . ':V' . $rowcount);
                $worksheet->getStyle('Q' . $rowcount)->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->mergeCells('W' . $rowcount . ':Z' . $rowcount);
                $worksheet->getStyle('W' . $rowcount)->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowcount . ':J' . $rowcount);
                $objPHPExcel->getActiveSheet()->mergeCells('K' . $rowcount . ':P' . $rowcount);
                $objPHPExcel->getActiveSheet()->mergeCells('Q' . $rowcount . ':V' . $rowcount);
                $objPHPExcel->getActiveSheet()->mergeCells('W' . $rowcount . ':Z' . $rowcount);

                $objPHPExcel->getActiveSheet()->getStyle("A" . $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle("K" . $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle("Q" . $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle("W" . $rowcount)->getFont()->setBold(true);
                $rowcount++;


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Employee Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Employee ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Branch');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Designation');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Client Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Site ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Site Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Shift Policy');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Shift Hour');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Days');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Shift Hour');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Sales Rate/Hour');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Total Sales');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Expense Rate/Hour');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Total Expense');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Shift Hour');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, 'Days');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, 'Sales Rate');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, 'Total Sales');

                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(19, $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(20) . $rowcount, 'Expense Rate');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(20, $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(21) . $rowcount, 'Total Expense');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(21, $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(22) . $rowcount, 'Site Profit');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(22, $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(23) . $rowcount, 'Sales');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(23, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowcount, 'Expense');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(24, $rowcount)->getFont()->setBold(true);

                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, 'Shift Hours');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(25, $rowcount)->getFont()->setBold(true);
                $rowcount = $rowcount + 1;
                $i = 1;
                if (!empty($arr_siteattendance_for_template)) {
                    foreach ($arr_siteattendance_for_template as $value) {

                        $arr_data = $value['summary'];
                        if (count($arr_data) > 0) {
                            $tot = 0;

                            $row1 = $rowcount;
                            foreach ($arr_data as $val) {
                                $slno = $i;

                                $sitename = $val['site']['site_name'];
                                $siteid = $val['site']['site_id'];
                                $clientname = $val['contacts']['company_name'];
                                $employeename = $val['emp_details']['first_name'] . ' ' . $val['emp_details']['last_name'];

                                $branch = $val['branches']['branch_name'];
                                $employeeid = $val['emp_proff']['emp_company_id'];
                                $designation = $val['designation']['desig_name'];
                                $shiftpolicy = $val['working_day_time_procedures']['day_time_desc'];
                                $shifthour = $val['0']['perday']; //employee shift hr
                                $shifthours = $val['0']['hours']; //actual shift hr
                                $day = $val['0']['days'];
                                $salesrate = $val['site_transactions']['srate'];
                                $expenserate = $val['site_transactions']['eratess'];
                                $totalsales = round($shifthours * $salesrate);
                                $totalexpense = round($shifthours * $expenserate);
                                // standard rate
                                $start_date_effective = $val['site_transactions']['start_date_effective'];
                                $end_date_effective = $val['site_transactions']['end_date_effective'];
                                //calculation of standard days

                                $lastDateOfMonth = date("Y-m-t", strtotime($month));
                                $start = '';
                                $end = '';
                                if ($month > $start_date_effective && $lastDateOfMonth < $end_date_effective) {
                                    if ($month > $start_date_effective) {
                                        $start = $month;
                                    } else {
                                        $start = $start_date_effective;
                                    }
                                    if ($lastDateOfMonth < $end_date_effective) {
                                        $end = $lastDateOfMonth;
                                    } else {
                                        $end = $end_date_effective;
                                    }
                                } elseif ($month > $start_date_effective || $lastDateOfMonth < $end_date_effective) {
                                    if ($month > $start_date_effective) {
                                        $start = $month;
                                    } else {
                                        $start = $start_date_effective;
                                    }
                                    if ($lastDateOfMonth < $end_date_effective) {
                                        $end = $lastDateOfMonth;
                                    } else {
                                        $end = $end_date_effective;
                                    }
                                } else {
                                    if ($month > $start_date_effective) {
                                        $start = $month;
                                    } else {
                                        $start = $start_date_effective;
                                    }
                                    if ($lastDateOfMonth < $end_date_effective) {
                                        $end = $lastDateOfMonth;
                                    } else {
                                        $end = $end_date_effective;
                                    }
                                }
                                $startTimeStamp = strtotime($start);
                                $endTimeStamp = strtotime($end);

                                $timeDiff = abs($endTimeStamp - $startTimeStamp);
                                $numberDays = $timeDiff / 86400;  // 86400 seconds in one day
                                $numberDays = intval($numberDays) + 1;
                                $tot = $shifthour * $numberDays; // standard shift hr

                                $sh = $val['site_transactions']['srate']; // standard sales rate/hr
                                $eh = $val['site_transactions']['eratess']; // standard expense rate/hr
                                $sts = round($tot * $sh); // standard total sales
                                $ste = round($tot * $eh); //  ''      total expense
                                /////////////varience/////
                                $siteprofit = round($totalsales - $totalexpense);
                                $sales = round($sts - $totalsales);
                                $expense = round($ste - $totalexpense);
                                $shift = $tot - $shifthours;

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $employeename);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $employeeid);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $designation);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $clientname);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $siteid);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $sitename);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $shiftpolicy);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $shifthour);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $numberDays);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $tot);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $sh);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $sts);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);



                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $eh);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $ste);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(15))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);


                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, $shifthours);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(16))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, $day);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(17))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, $salesrate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(18))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . $rowcount, $totalsales);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(19))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(20) . $rowcount, $expenserate);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(20))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(21) . $rowcount, $totalexpense);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(21))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(22) . $rowcount, $siteprofit);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(22))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(23) . $rowcount, $sales);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(23))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(24) . $rowcount, $expense);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(24))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(25) . $rowcount, $shift);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(25))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                                $rowcount++;
                                $i++;
                            }
                            $row2 = $rowcount - 1;
                            $worksheet->getStyle('A' . $row1 . ':Z' . $row2)->getAlignment()->applyFromArray(
                                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                            );
                        }
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );
                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:Z' . $row)->applyFromArray($BStyle);

                        foreach (range('A', 'Z') as $columnID) {
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                        }
                    }
                } else {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'No Records found under this Criteria');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells('A' . $rowcount . ':Z' . $rowcount . '');
                    $worksheet->getStyle('A' . $rowcount . '')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                }


                $objPHPExcel->getActiveSheet()->setTitle('Site Rate Details');
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
                break;
            default:
                $this->set('mode', '');
                $this->render('siterate');
                break;
        }
    }

    private function generateCustomerReport($mode)
    {
        $arr_form_data = $_REQUEST;
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $company_code = $this->Session->read('company_code'); //company_code
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');


        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_leavepolicygroupids = $crit;

            if ($str_criteria_item == '') {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }

            if (!isset($arr_form_data[$str_criteria_item])) {
                echo "<h1>No Criteria Selected</h1>";
                die();
            }
        }
        $arr_vendor_summary_for_template = array();
        $arr_vendor = array();
        ini_set('memory_limit', '-1');

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $arr_vendor = $this->EmployeeDetails->query("select * from contacts where status = 1 and contact_id ='$leavepolicygroupid'");
                if (!empty($arr_vendor)) {
                    $arr_vendor_summary_for_template[] = array(
                        'summary' => $arr_vendor
                    );
                }
            }
        }
        //debug($arr_vendor_summary_for_template);
        $this->set('arr_vendor_summary_for_template', $arr_vendor_summary_for_template);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('criterias', $cr);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('Customer/Vendor');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('customer/vendor.pdf', 'D');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "CustomerandVendor_Details.xlsx" : "Customer/Vendor_Details" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Customer and Vendor Details By Greatleap");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Customer and Vendor Details");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(15);



                for ($col = 'A'; $col !== 'S'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                $worksheet->mergeCells('A1:S1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $BStyle = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $worksheet->setCellValueByColumnAndRow(0, 2, "( Report run by " . $user_id . " - " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $worksheet->mergeCells('A2:S2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );

                $rowcount = 3;
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl. No.');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Company Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Reg.No');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Address');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'City');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'State');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Pincode');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Email ID');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Phone');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'TAN');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'PAN No');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'GST No');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Bank Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Bank Branch');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'IFSC Code');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Account No');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount, 'Relationship');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount, 'Contact Person Name');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17, $rowcount)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount, 'Designation');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18, $rowcount)->getFont()->setBold(true);

                $i = 1;
                foreach ($arr_vendor_summary_for_template as $data1) {
                    foreach ($data1['summary'] as $data) {
                        $slno = $i;
                        $rowcount++;
                        $Companyname = isset($data['contacts']['company_name']) ? $data['contacts']['company_name'] : '';
                        $Regno = isset($data['contacts']['reg']) ? $data['contacts']['reg'] : '';
                        $Address = isset($data['contacts']['address']) ? $data['contacts']['address'] : '';
                        $city = isset($data['contacts']['city']) ? $data['contacts']['city'] : '';
                        $state = isset($data['contacts']['state']) ? $data['contacts']['state'] : '';
                        $pincode = isset($data['contacts']['pincode']) ? $data['contacts']['pincode'] : '';
                        $email = isset($data['contacts']['email']) ? $data['contacts']['email'] : '';
                        $phone = isset($data['contacts']['phone']) ? $data['contacts']['phone'] : '';
                        $tan = isset($data['contacts']['tin']) ? $data['contacts']['tin'] : '';
                        $pan = isset($data['contacts']['pan_no']) ? $data['contacts']['pan_no'] : '';
                        $gst = isset($data['contacts']['gst']) ? $data['contacts']['gst'] : '';
                        $bank = isset($data['contacts']['bank_name']) ? $data['contacts']['bank_name'] : '';
                        $bankbranch = isset($data['contacts']['bank_branch']) ? $data['contacts']['bank_branch'] : '';
                        $ifsc = isset($data['contacts']['ifsc_code']) ? $data['contacts']['ifsc_code'] : '';
                        $accountnumber = isset($data['contacts']['account_no']) ? $data['contacts']['account_no'] : '';
                        $relationship = isset($data['contacts']['relationship']) ? $data['contacts']['relationship'] : '';
                        $contactperson = isset($data['contacts']['first_name']) ? $data['contacts']['first_name'] : '';
                        $designation = isset($data['contacts']['c_designation']) ? $data['contacts']['c_designation'] : '';
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $slno);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $Companyname);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount,  $Regno);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount,  $Address);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $city);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount,  $state);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount,  $pincode);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount,  $email);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount,  $phone);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount,  $tan);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount,  $pan);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount,  $gst);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount,  $bank);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount,  $bankbranch);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount,  $ifsc);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount,  $accountnumber);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(15))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . $rowcount,  $relationship);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(16))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . $rowcount,  $contactperson);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(17))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . $rowcount,  $designation);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(18))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    }
                    $i++;
                }


                $objPHPExcel->getActiveSheet()->setTitle(' Customer and Vendor Details');
                foreach (range('A', 'S') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }
                $row2 = $rowcount - 1;
                $worksheet->getStyle('A3:S' . $row2)->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                );
                $BStyle = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('A1:S' . $rowcount)->applyFromArray($BStyle);
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
                break;
            default:
                $this->set('mode', '');
                $this->render('customerlist');
                break;
        }
    }
}
