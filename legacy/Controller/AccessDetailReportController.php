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
ini_set('max_execution_time', 300);
App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class AccessDetailReportController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'AccessDetailReport';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('ReportCriterias', 'ReportAudit');
    public $components = array('MasterdataManagement');

    public function hrreports() {
        $arr_reporttypes = array(
            'ReportsAudit' => 'Reports',
            'FormsAudit' => 'Forms',
            'LoginAudit' => 'Login',
//            'holiday' => 'Holiday Group Reports',
                /* 'leave' => 'Leaves Report',
                  'attendance' => 'Attendance Summary', */
                //'tax' => 'Tax Declarations'
        );
//        

        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '') {
        $this->autoRender = FALSE;
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'ReportsAudit':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'FormsAudit':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'LoginAudit':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                default :
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

    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '') {
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

    public function loadcriteriaitems($index, $str_criteria = '') {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
                $model = ($model == 'Users') ? 'ReportAudit' : $model;
                $this->set('criteria', $model);
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function listcriteriaitems($str_criteria = '') {
        $this->autoRender = false;
        $model = $str_criteria;
        $join = array();
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');

            $conditions[] = array("status" => 1);

            if ($model == 'ReportAudit') {
                $conditions[] = ' ReportAudit.user_name  NOT REGEXP "support" ';
                $fields = array("DISTINCT `user_id`, `user_name`");
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => $fields, "conditions" => $conditions, "joins" => $join)));
            } else {
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "joins" => $join)));
            }

            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'ReportAudit':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['user_id'];
                        $arr_criteriaItems[$key]['text'] = $value['user_name'];
                        $key++;
                    }
                    break;
                case 'Units':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;
                case 'EmployeeDetails':
                    //edited by arul - changed empid ad company id
                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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
                    $conditions[] = array('status' => 1);

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $key++;
                    }
                    break;
            }
            echo json_encode($arr_criteriaItems);
        }
    }

//    public function reportAudit($type, $mode) {
//        $this->autoRender = false;
//
//        //This is to save download history. By Arul P Das on 25_1_2021
//        $dataForHistory = array();
//        $arr_form_data = $_REQUEST;
//
//        switch ($type) {
//            case 'employee':
//                $dataForHistory['report_type'] = "Employee Information Report";
//                break;
//            case 'shiftpolicy':
//                $dataForHistory['report_type'] = "Shift Policy Report";
//                break;
//            case 'leavepolicy':
//                $dataForHistory['report_type'] = "Leave Policy Report";
//                break;
//            case 'holiday':
//                $dataForHistory['report_type'] = "Holiday Policy Report";
//                break;
//        }
//
//        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';
//        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';
//        $dataForHistory['include_resigned'] = isset($arr_form_data['resigned']) ? $arr_form_data['resigned'] : '';
//        $dataForHistory['Include_negative_salary'] = isset($arr_form_data['ngtvsal']) ? $arr_form_data['ngtvsal'] : '';
//        $criteria_count = isset($arr_form_data['hidden-criterias-count']) ? $arr_form_data['hidden-criterias-count'] : 1;
//        $i = 1;
//        $criteria_array = array();
//        $criteria_name_array = array();
//        $items_array = array();
//        $items_count_array = array();
//        while ($i <= $criteria_count) {
//            $criteria = isset($arr_form_data['hidden-criteria' . $i]) ? $arr_form_data['hidden-criteria' . $i] : '';
//            $criteria_array[] = $criteria;
//
//            switch ($criteria) {
//                case 'EmployeeDetails': $criteria_name_array[] = 'belonging to an Employee';
//                    break;
//                case 'Units': $criteria_name_array[] = 'belonging to a Branch';
//                    break;
//                case 'Departments': $criteria_name_array[] = 'belonging to a Department';
//                    break;
//                case 'EmployeeProfessionalDetails': $criteria_name_array[] = 'belonging to a Joining Date';
//                    break;
//                case 'DayTimeProcedures': $criteria_name_array[] = 'belonging to a Shift Policy Group';
//                    break;
//                case 'LeavePolicyGroup': $criteria_name_array[] = 'belonging to a Leave Policy';
//                    break;
//                case 'HolidayGroup': $criteria_name_array[] = 'belonging to a Holiday Policy Group';
//                    break;
//
//                default : break;
//            }
//            $items_array[] = isset($arr_form_data[$criteria]) ? implode(",", $arr_form_data[$criteria]) : '';
//            $items_count_array[] = isset($arr_form_data[$criteria]) ? count($arr_form_data[$criteria]) : 0;
//
//            $i++;
//        }
//
//        $dataForHistory['criteria'] = implode(",", $criteria_array);
//        $dataForHistory['criteria_name'] = implode(",", $criteria_name_array);
//        $dataForHistory['items'] = implode(",", $items_array);
//        $dataForHistory['items_count'] = implode(",", $items_count_array);
//
//
//        if ($mode == 'pdf') {
//            $dataForHistory['mode'] = 'PDF Download';
//        } else if ($mode == 'excel') {
//            $dataForHistory['mode'] = 'Excel Download';
//        } else {
//            $dataForHistory['mode'] = 'View Report';
//        }
//
//        $user_id = $this->Session->read('login_user_id');
//        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
//        $user_name = $this->Session->read('user_name');
//        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
//
//        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
//        $this->ReportAudit->save($dataForHistory);
//    }

    public function generatereport($type = '', $mode = '') {
        // debug($mode);
        // debug($type);
        $this->autoRender = false;
        switch ($type) {
            case 'ReportsAudit':
                $this->generateaduitreport($mode);
                break;
            case 'FormsAudit':
                $this->generateformauditreport($mode);
                break;
            case 'LoginAudit':
                $this->generateloginauditreport($mode);
                break;
            default:
                return false;
                break;
        }

//        $this->reportAudit($type, $mode);
    }

    private function _modelExists($modelName) {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }

    private function generateaduitreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');

        $fields = 'ReportAudit.report_type,ReportAudit.report_component,ReportAudit.report_from,ReportAudit.report_to,ReportAudit.include_resigned,ReportAudit.Include_negative_salary,ReportAudit.criteria_name,ReportAudit.mode,ReportAudit.user_name,ReportAudit.creation_date';

        $joins = array();
        $conditions = array('ReportAudit.status' => 1, ' ReportAudit.mode IN ("View Report","Excel Download","PDF Download") ');


        //
        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
            $conditions[] = 'date_format(ReportAudit.creation_date,"%Y-%m") BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
        }


        $arr_userids = array();

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            //debug($str_criteria_item);
            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_userids = $crit;
        }
        $arr_audit_for_template = array();
        if (isset($arr_userids) && !empty($arr_userids)) {
            
            //This is for searching each user
//            foreach ($arr_userids as $userid) {
//                $conditions[] = ' ReportAudit.user_id ="'.$userid.'" ';
//                $arr_shiftpolicy_details = $this->ReportAudit->find("all", array(
//                    'fields' => $fields,
//                    'joins' => $joins,
//                    'conditions' => $conditions,
//                ));
//                if(count($arr_shiftpolicy_details)>0){
//                    foreach ($arr_shiftpolicy_details as $data){
//                        $arr_audit_for_template[] = $data['ReportAudit'];
//                    }
//                }
//            }
            //This is for searching each user
            
            $ids = '"';
            $ids .= implode('","', $arr_userids);
            $ids .= '"';
            $conditions[] = ' ReportAudit.user_id IN (' . $ids . ') ';
            $conditions[] = ' ReportAudit.user_name  NOT REGEXP "support" ';
            $arr_shiftpolicy_details = $this->ReportAudit->find("all", array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions,
                'order' => array('creation_date desc'),
            ));
            if (count($arr_shiftpolicy_details) > 0) {
                foreach ($arr_shiftpolicy_details as $data) {
                    $arr_audit_for_template[] = $data['ReportAudit'];
                }
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($arr_audit_for_template)  ;

        $this->set('arr_audit_for_template', $arr_audit_for_template);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('type','ReportsAudit');

        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('reportaudit');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //The below code is edited by ***ARUL P DAS on 20/1/2020
                $html2pdf = new HTML2PDF('P', 'A3', 'en');
                $html2pdf->setTestTdInOnePage(false);

                //$html2pdf = new HTML2PDF('P', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('ReportAudit.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;

//            case 'excel' :
//                break;
            default :
                $this->set('mode', '');
                $this->render('reportaudit');
                break;
        }
    }
    
    private function generateformauditreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');

        $fields = 'ReportAudit.report_type,ReportAudit.user_name,ReportAudit.creation_date';

        $joins = array();
        $conditions = array('ReportAudit.status' => 1, ' ReportAudit.mode = "Menu" ');


        //
        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
            $conditions[] = 'date_format(ReportAudit.creation_date,"%Y-%m") BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
        }


        $arr_userids = array();

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            //debug($str_criteria_item);
            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_userids = $crit;
        }
        $arr_audit_for_template = array();
        if (isset($arr_userids) && !empty($arr_userids)) {
            
            //This is for searching each user
//            foreach ($arr_userids as $userid) {
//                $conditions[] = ' ReportAudit.user_id ="'.$userid.'" ';
//                $arr_shiftpolicy_details = $this->ReportAudit->find("all", array(
//                    'fields' => $fields,
//                    'joins' => $joins,
//                    'conditions' => $conditions,
//                ));
//                if(count($arr_shiftpolicy_details)>0){
//                    foreach ($arr_shiftpolicy_details as $data){
//                        $arr_audit_for_template[] = $data['ReportAudit'];
//                    }
//                }
//            }
            //This is for searching each user
            
            $ids = '"';
            $ids .= implode('","', $arr_userids);
            $ids .= '"';
            $conditions[] = ' ReportAudit.user_id IN (' . $ids . ') ';
            $conditions[] = ' ReportAudit.user_name  NOT REGEXP "support" ';
            $arr_shiftpolicy_details = $this->ReportAudit->find("all", array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions,
                'order' => array('creation_date desc'),
            ));
            if (count($arr_shiftpolicy_details) > 0) {
                foreach ($arr_shiftpolicy_details as $data) {
                    $arr_audit_for_template[] = $data['ReportAudit'];
                }
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($arr_audit_for_template)  ;

        $this->set('arr_audit_for_template', $arr_audit_for_template);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('type','ReportsAudit');

        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('formaudit');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //The below code is edited by ***ARUL P DAS on 20/1/2020
                $html2pdf = new HTML2PDF('P', 'A3', 'en');
                $html2pdf->setTestTdInOnePage(false);

                //$html2pdf = new HTML2PDF('P', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('FormAudit.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;

//            case 'excel' :
//                break;
            default :
                $this->set('mode', '');
                $this->render('formaudit');
                break;
        }
    }
    
    private function generateloginauditreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');

        $fields = 'ReportAudit.report_type,ReportAudit.report_component,ReportAudit.report_from,ReportAudit.report_to,ReportAudit.include_resigned,ReportAudit.Include_negative_salary,ReportAudit.criteria_name,ReportAudit.mode,ReportAudit.user_name,ReportAudit.creation_date';

        $joins = array();
        $conditions = array('ReportAudit.status' => 1, ' ReportAudit.mode IN ("Auth") ');


        //
        //Build conditions based on criterias recieved
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '') && (isset($arr_form_data['reportto']) && $arr_form_data['reportto'] != '')) {
            $conditions[] = 'date_format(ReportAudit.creation_date,"%Y-%m") BETWEEN "' . $arr_form_data['reportfrom'] . '" AND "' . $arr_form_data['reportto'] . '"';
        }


        $arr_userids = array();

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            //debug($str_criteria_item);
            //$conditions[] = $arr_reportcriterias[0]['reportcriteria'].".".$arr_reportcriterias[0]['reportcriteria_field'].' IN (\''.implode("','",$arr_form_data[$str_criteria_item]).'\')';
            $crit = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $arr_userids = $crit;
        }
        $arr_audit_for_template = array();
        if (isset($arr_userids) && !empty($arr_userids)) {
            
            //This is for searching each user
//            foreach ($arr_userids as $userid) {
//                $conditions[] = ' ReportAudit.user_id ="'.$userid.'" ';
//                $arr_shiftpolicy_details = $this->ReportAudit->find("all", array(
//                    'fields' => $fields,
//                    'joins' => $joins,
//                    'conditions' => $conditions,
//                ));
//                if(count($arr_shiftpolicy_details)>0){
//                    foreach ($arr_shiftpolicy_details as $data){
//                        $arr_audit_for_template[] = $data['ReportAudit'];
//                    }
//                }
//            }
            //This is for searching each user
            
            $ids = '"';
            $ids .= implode('","', $arr_userids);
            $ids .= '"';
            $conditions[] = ' ReportAudit.user_id IN (' . $ids . ') ';
            $conditions[] = ' ReportAudit.user_name  NOT REGEXP "support" ';
            $arr_shiftpolicy_details = $this->ReportAudit->find("all", array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions,
                'order' => array('creation_date desc'),
            ));
            if (count($arr_shiftpolicy_details) > 0) {
                foreach ($arr_shiftpolicy_details as $data) {
                    $arr_audit_for_template[] = $data['ReportAudit'];
                }
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        //debug($arr_audit_for_template)  ;

        $this->set('arr_audit_for_template', $arr_audit_for_template);

        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('type','ReportsAudit');

        switch ($mode) {
            case 'pdf' :
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('loginaudit');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                //The below code is edited by ***ARUL P DAS on 20/1/2020
                $html2pdf = new HTML2PDF('P', 'A3', 'en');
                $html2pdf->setTestTdInOnePage(false);

                //$html2pdf = new HTML2PDF('P', 'Legal', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('LoginAudit.pdf', 'D');
                //$this->render('loginaudit');                
                break;

//            case 'excel' :
//                break;
            default :
                $this->set('mode', '');
                $this->render('loginaudit');
                break;
        }
    }
}
