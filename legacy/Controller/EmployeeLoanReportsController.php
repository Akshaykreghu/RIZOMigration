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
class EmployeeLoanReportsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EmployeeLoanReports';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EditPunches', 'Attendance', 'CentralControl', 'UserCredentials', 'EmployeeDetails', 'EmployeeProfessionalDetails', 'DeviceAttendance', 'Departments', 'Grades', 'Verticals', 'Units', 'ReportCriterias', 'AttendanceRegister', 'AttendanceRegisterReport', 'DbConfig', 'MobileUserauditor', 'EmployeeLoan', 'ReportAudit'); //santhu
    public $components = array('MasterdataManagement');

    public function hrreports() {
        $arr_reporttypes = array(
            'Loan' => 'Employee Loan',
        );
        $this->set('arr_reporttypes', $arr_reporttypes);
    }

    /*
     * Change Sub Report type
     */

    public function changereporttype($type = '') {
        $this->autoRender = FALSE;
        // debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            switch ($type) {
                case 'Loan':
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

    public function loadcriteriaitems($index, $str_criteria = '', $type = '') {
        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $this->set('criteria', $model);
                if (isset($type)) {
                    $this->set('type', $type);
                }
                $this->render('loadcriteriaitems');
            } else {
                return '';
            }
        } else {
            return '';
        } 
    }

    public function listcriteriaitems($str_criteria = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds'); // Edited by Akshay on 29-1-2025
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        $conditions = array(); // Edited by Akshay on 28-1-2025
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');
            if ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                // Edited by Akshay on 27-1-2025
                $company_code = $this->Session->read('company_code');
                if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                    debug($conditions);
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions = array("Units.status" => 1, "branch_code" => $is_ho);
                    } else {
                        $conditions = array("Units.status" => 1);
                    }
                } elseif ($user_group == 2) {
                    // End
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1); // Edited by Akshay on 14-3-2025

                    // Edited by Akshay on 11-3-2025
                    if ($company_code == 'GAAR' || $company_code == 'HRBL'){
                        $user_id = $this->Session->read("login_user_id"); //user id
                        $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                        $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;
                        $condition = '';
                        if ($special_access != 1) {
                            $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                            $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                            $conditions = array("Units.status" => 1, "branch_code !=" => $directors_branch);
                        }
                    }
                    // End

                } else {
                    $arr_order = array("Units.branch_name" => "ASC");
                    $conditions = array("Units.status" => 1);
                }
            } else {
                $conditions = array("status" => 1);
                // Edited by Akshay on 11-3-2025
                $user_group = $this->Session->read("user_group");
                $company_code = $this->Session->read('company_code');
                if( $user_group == 2 && ($company_code == 'GAAR' || $company_code == 'HRBL')){
                    $user_id = $this->Session->read("login_user_id"); //user id
                    $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                    $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;
                    $condition = '';
                    if ($special_access != 1) {
                        $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                        $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                        $conditions = array("status" => 1, "branch_code !=" => $directors_branch);
                    }
                }
                // End
            }

            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {

                case 'Units':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $value['branch_name'];
                        $key++;
                    }
                    break;
                case 'EmployeeDetails':
                    $fields = 'emp_pkey,status,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
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


                    $arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions[] = array("status in(1,2)");
                    } else {

                        $conditions[] = array("status" => 1);
                    }

                    //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2) {
                        // Edited by Akshay on 27-1-2025
                        $company_code = $this->Session->read('company_code');
                        if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $emp_pkey = $this->Session->read('emp_fkey');
                            $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                            if ($is_ho != 1) {
                                $conditions["branch_code"] = $is_ho;
                            }
                        }
                        // Edited by Akshay on 11-3-2025
                        elseif ($company_code == 'GAAR' || $company_code == 'HRBL') {
                            $user_id = $this->Session->read("login_user_id"); //user id
                            $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                            $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;
                            $condition = '';
                            if ($special_access != 1) {
                                $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                                $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                                $conditions[] = array("EmployeeDetails.branch_code !=" => $directors_branch);
                            }
                        }
                        // End
                        else {
                            $cur_emp_key = $this->Session->read("emp_fkey");
                            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                            $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                        }
                        // End
                    }
                    //employee branch wise sorting ends here

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp = $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions
                    ));
                    foreach ($arr_emp as $key => $value) {
                        $arr_criteriaItems[$key]['text'] = $value[0]['name'];
                        $arr_criteriaItems[$key]['key'] = $value["EmployeeDetails"]['emp_pkey'];
                        $arr_criteriaItems[$key]['status'] = $value["EmployeeDetails"]['status'];
                        $key++;
                    }
                    break;
            }
            echo json_encode($arr_criteriaItems);
        }
    }

    public function reportAudit($type, $mode) {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'Loan':
                $dataForHistory['report_type'] = "Employees Loan Reports";
                break;
            case 'LoanSummary':
                $dataForHistory['report_type'] = "Employee Loan Summary Report";
                break;
            case 'LoanDetails':
                $dataForHistory['report_type'] = "Employee Loan Detail Reports";
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
                case 'EmployeeDetails': $criteria_name_array[] = 'belonging to an Employee';
                    break;
                case 'Units': $criteria_name_array[] = 'belonging to a Branch';
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

//function for dropdown
    public function generatereport($type = '', $mode = '') {
        $this->autoRender = false;
//debug($mode);
        switch ($type) {
            case 'Loan':
                $this->generateemployeeeloan($mode);
                break;

            case 'MobilelocationRep':
                $this->generatemobilelocationreport($type, $mode);
                break;
            default:
                return false;
                break;
        }
  $this->reportAudit($type, $mode);
    }

    public function listemployeefields() {
        App::import('Vendor', 'EmployeeInformationFields', array('file' => 'ReportFields' . DS . 'EmployeeInformationFields.php'));
        $arr_empinformation_fields = new EmployeeInformationFields();
        $arr_emp_field_headings = array_merge(
                $arr_empinformation_fields->getFieldHeadings('EmployeeDetails'), $arr_empinformation_fields->getFieldHeadings('EmployeeProfessionalDetails'), $arr_empinformation_fields->getFieldHeadings('Departments'), $arr_empinformation_fields->getFieldHeadings('Grades'), $arr_empinformation_fields->getFieldHeadings('Verticals'), $arr_empinformation_fields->getFieldHeadings('Units')
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

    private function _modelExists($modelName) {
        $models = App::objects('model');
        return in_array($modelName, $models);
    }

      private function generateemployeeeloan($mode = '')
    {

        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $this->EmployeeLoan->useDbConfig = $this->Session->read('ds');
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        $report_month = $arr_form_data['reportfrom'];
        // $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        //Edited by Akshay on 7-8-2024
        $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        //End
        $to = date('Y-m-t', strtotime($arr_form_data['reportto']));

        $needBranchWiseReport = false;
        $conditions = array();
        $conditions[] = 'created_date >="' . $from . '" and created_date<="' . $to . '"';
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        //debug($conditions);

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }

            $arr_reportcriterias = Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array(
                "fields" => "reportcriteria,reportcriteria_field",
                "conditions" => array(
                    "reporttype" => "Loan",
                    "status" => 1,
                    'reportcriteria' => $str_criteria_item
                )
            )));
            //debug($str_criteria_item);
            if (isset($arr_reportcriterias[0]['reportcriteria_field']) && isset($arr_form_data[$str_criteria_item])) {
                $conditions[] = $arr_reportcriterias[0]['reportcriteria'] . "." . $arr_reportcriterias[0]['reportcriteria_field'] . ' IN (\'' . implode("','", $arr_form_data[$str_criteria_item]) . '\')';
            }
        }

        if (!isset($arr_form_data[$str_criteria_item])) {
            echo "<h1>Please Choose Criteria Items</h1>";
            return;
        }

        $condition = "and EmployeeDetails.status = '1'  ";
        //        debug($arr_form_data);
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and EmployeeDetails.status in ('1','2') ";
        }
        $str_conditions = implode(' AND ', $conditions);
        //Edited by Akshay on 6-8-2024
        $loan_complete = isset($arr_form_data['loan_comp']) ? $arr_form_data['loan_comp'] : '0';
        $loan_cond = "and el.is_completed = 'N' ";
        if ($loan_complete == '1') {
            $loan_cond = " ";
        }
        //End

        //Edited by Akshay on 6-8-2024
        $arr_employee_loan = $this->EmployeeLoan->query("select ei.*,el.*,uc.user_id,tr.last_approved_working_date,(select sum(amount_paid) from emp_loan_info where loan_pkey = el.emp_loan_pkey)as loan_paid,EmployeeDetails.branch_code,EmployeeDetails.status,Units.branch_name
                            from emp_details EmployeeDetails
                            INNER join emp_loan el on (EmployeeDetails.emp_pkey = el.emp_fkey) 
                            LEFT JOIN branches AS Units ON (EmployeeDetails.branch_code = Units.branch_code)
                            LEFT join employee_info ei on (EmployeeDetails.emp_pkey= ei.emp_pkey)
                            LEFT join user_credentials uc on (uc.emp_fkey= ei.emp_pkey)
                            LEFT join termination tr on (tr.emp_fkey= ei.emp_pkey and tr.status=1)
                            where  el.status=1 
                            $loan_cond
                            and $str_conditions $condition
                            ORDER BY ei.EmpName
                            ");
        //End
        // debug($arr_employee_loan);
        $arr_emp_loan_template = array();
        if ($needBranchWiseReport) {
            //Parse array for branchwise report
            foreach ($arr_employee_loan as $employee_loan) {
                // debug($employee_loan);
                $branch_code = isset($employee_loan['EmployeeDetails']['branch_code']) ? $employee_loan['EmployeeDetails']['branch_code'] : '';
                // debug($branch_code);
                //  $empstatus = isset($employee_loan['EmployeeDetails']['status']) && $employee_loan['EmployeeDetails']['status']=="2" ? '(Resigned)' :'';
                $branch_name = isset($employee_loan['Units']['branch_name']) ? $employee_loan['Units']['branch_name'] : '';
                //debug($branch_name);
                //branch wiss
                if ($branch_code != '') {
                    if (!isset($arr_emp_loan_template[$branch_code])) {
                        $arr_emp_loan_template[$branch_code] = array(
                            'branch_name' => $branch_name,
                            'employeeloan' => array()
                        );
                    }

                    $request = array();
                    $request['status'] = isset($employee_loan['EmployeeDetails']['status']) && $employee_loan['EmployeeDetails']['status'] == "2" ? '(Resigned)' : '';
                    $request['emp_name'] = isset($employee_loan['ei']['EmpName']) ? $employee_loan['ei']['EmpName'] : '';
                    $request['employee_id'] = isset($employee_loan['ei']['employee_id']) ? $employee_loan['ei']['employee_id'] : '';
                    $request['designation'] = isset($employee_loan['ei']['designation']) ? $employee_loan['ei']['designation'] : '';
                    $request['department'] = isset($employee_loan['ei']['department']) ? $employee_loan['ei']['department'] : '';
                    $request['loan_amount'] = isset($employee_loan['el']['loan_amount']) ? $employee_loan['el']['loan_amount'] : '';
                    $request['tenure'] = isset($employee_loan['el']['tenure']) ? $employee_loan['el']['tenure'] : '';
                    $request['intrest_rate'] = isset($employee_loan['el']['intrest_rate']) ? $employee_loan['el']['intrest_rate'] : 0; //Edited by Akshay on 8-8-2024
                    $request['emi_amount'] = isset($employee_loan['el']['emi_amount']) ? round($employee_loan['el']['emi_amount']) : ''; //Edited by Askhay on 12-8-2024
                    $request['userid'] = isset($employee_loan['uc']['user_id']) ? $employee_loan['uc']['user_id'] : '';
                    $request['join'] = isset($employee_loan['ei']['joining_date']) ? $employee_loan['ei']['joining_date'] : '';
                    $request['termin'] = isset($employee_loan['tr']['last_approved_working_date']) ? $employee_loan['tr']['last_approved_working_date'] : '';
                    $start_date = $employee_loan['el']['emi_start_month'];
                    $date = explode("-", $start_date);
                    $year = ($date[0]);
                    $mon = ($date[1]);
                    if ($mon == 1) {
                        $mon = 'Jan';
                    } else if ($mon == 2) {
                        $mon = 'Feb';
                    } else if ($mon == 3) {
                        $mon = 'Mar';
                    } else if ($mon == 4) {
                        $mon = 'Apr';
                    } else if ($mon == 5) {
                        $mon = 'May';
                    } else if ($mon == 6) {
                        $mon = 'Jun';
                    } else if ($mon == 7) {
                        $mon = 'Jul';
                    } else if ($mon == 8) {
                        $mon = 'Aug';
                    } else if ($mon == 9) {
                        $mon = 'Sep';
                    } else if ($mon == 10) {
                        $mon = 'Oct';
                    } else if ($mon == 11) {
                        $mon = 'Nov';
                    } else if ($mon == 12) {
                        $mon = 'Dec';
                    }
                    $getmonth = $mon . "-" . $year;
                    $request['start_month'] = $getmonth;
                    $request['emi_end_month'] = isset($employee_loan['el']['emi_end_month']) ? $employee_loan['el']['emi_end_month'] : '';
                    $request['remarks'] = isset($employee_loan['0']['loan_paid']) ? $employee_loan['0']['loan_paid'] : '';
                    //Edited by Akshay on 6-8-2024
                    $request['branch'] = isset($employee_loan['ei']['branch']) ? $employee_loan['ei']['branch'] : '';
                    $request['loan_status'] = isset($employee_loan['el']['is_completed']) ? $employee_loan['el']['is_completed'] : '';
                    //End
                    $arr_emp_loan_template[$branch_code]['employeeloan'][] = $request;
                }
            }
        } else {
            //Parse array for simple report
            $arr_emp_loan_template['employeeeloan'] = array();
            foreach ($arr_employee_loan as $employee_loan) {
                $request = array();
                $request['status'] = isset($employee_loan['EmployeeDetails']['status']) && $employee_loan['EmployeeDetails']['status'] == "2" ? '(Resigned)' : '';
                $request['emp_name'] = isset($employee_loan['ei']['EmpName']) ? $employee_loan['ei']['EmpName'] : '';
                $request['employee_id'] = isset($employee_loan['ei']['employee_id']) ? $employee_loan['ei']['employee_id'] : '';
                $request['designation'] = isset($employee_loan['ei']['designation']) ? $employee_loan['ei']['designation'] : '';
                $request['department'] = isset($employee_loan['ei']['department']) ? $employee_loan['ei']['department'] : '';
                $request['branch'] = isset($employee_loan['ei']['branch']) ? $employee_loan['ei']['branch'] : '';
                $request['loan_amount'] = isset($employee_loan['el']['loan_amount']) ? $employee_loan['el']['loan_amount'] : '';
                $request['tenure'] = isset($employee_loan['el']['tenure']) ? $employee_loan['el']['tenure'] : '';
                $request['intrest_rate'] = isset($employee_loan['el']['intrest_rate']) ? $employee_loan['el']['intrest_rate'] : 0; //Edited by Akshay on 8-8-2024
                $request['emi_amount'] = isset($employee_loan['el']['emi_amount']) ? round($employee_loan['el']['emi_amount']) : ''; //Edited by Akshay on 12-8-2024
                $request['userid'] = isset($employee_loan['uc']['user_id']) ? $employee_loan['uc']['user_id'] : '';
                $request['join'] = isset($employee_loan['ei']['joining_date']) ? $employee_loan['ei']['joining_date'] : '';
                $request['termin'] = isset($employee_loan['tr']['last_approved_working_date']) ? $employee_loan['tr']['last_approved_working_date'] : '';
                $start_date = $employee_loan['el']['emi_start_month'];
                $date = explode("-", $start_date);
                $year = ($date[0]);
                $mon = ($date[1]);
                if ($mon == 1) {
                    $mon = 'Jan';
                } else if ($mon == 2) {
                    $mon = 'Feb';
                } else if ($mon == 3) {
                    $mon = 'Mar';
                } else if ($mon == 4) {
                    $mon = 'Apr';
                } else if ($mon == 5) {
                    $mon = 'May';
                } else if ($mon == 6) {
                    $mon = 'Jun';
                } else if ($mon == 7) {
                    $mon = 'Jul';
                } else if ($mon == 8) {
                    $mon = 'Aug';
                } else if ($mon == 9) {
                    $mon = 'Sep';
                } else if ($mon == 10) {
                    $mon = 'Oct';
                } else if ($mon == 11) {
                    $mon = 'Nov';
                } else if ($mon == 12) {
                    $mon = 'Dec';
                }
                $getmonth = $mon . "-" . $year;
                $request['start_month'] = $getmonth;
                $request['emi_end_month'] = isset($employee_loan['el']['emi_end_month']) ? $employee_loan['el']['emi_end_month'] : '';
                $request['remarks'] = isset($employee_loan['0']['loan_paid']) ? $employee_loan['0']['loan_paid'] : '';
                //Edited by Akshay on 6-8-2024
                $request['loan_status'] = isset($employee_loan['el']['is_completed']) ? $employee_loan['el']['is_completed'] : '';
                //End
                $arr_emp_loan_template['employeeloan'][] = $request;
            }
        }

        $this->set('needBranchWiseReport', $needBranchWiseReport);
        $this->set('arr_emp_loan_template', $arr_emp_loan_template);

        //debug($arr_emp_expenses_template);
        //Set informations needed for report
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $user_id = $this->Session->read('login_user_id');
        $this->set('user_id', $user_id);
        $date_time = date('d-m-Y H:i');
        $this->set('date_time', $date_time);
        //Edited by Akshay on 7-8-2024
        $from = (new DateTime($from))->format('F Y');
        $to = (new DateTime($to))->format('F Y');
        $this->set('from', $from);
        $this->set('to', $to);
        //End
        $str_company_code = $this->Session->read('company_code');
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('Emploanreport');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output($str_company_code . "_Employee_loan_" . $from . " to " . $to . ".pdf", 'D');
                //$this->render('reportleavepolicy');
                break;
            case 'excel':
                $file_name = isset($str_company_code) ? $str_company_code . "_Employee_loan_" . $from . " to " . $to . ".xlsx" : "ShiftPolicy" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Employee Loan " . $from . " to " . $to);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $worksheet->mergeCells('A1:H1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:H2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );
                for ($col = 'A'; $col !== 'Z'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $columncount = 0;
                $rowcount = 3;
                if (isset($needBranchWiseReport) && $needBranchWiseReport == true) {

                    // $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $branchname);
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    // $rowcount = $rowcount + 1;
                    if (count($arr_emp_loan_template) != 0){
                        $start_row = $rowcount;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Loan Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Tenure(month)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Interest Rate(%)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'EMI Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'EMI Start Month');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'EMI End Month');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Paid');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                        //Edited by Akshay on 6-8-2024
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'Balance Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                        //End
                        $k = 1;
                        $rowcount = $rowcount + 1;
                    }
                    foreach ($arr_emp_loan_template as $branch_code => $emp_loan) {
                        $branchname = $emp_loan['branch_name'];
                        $arr_data = $emp_loan['employeeloan'];
                        if (count($arr_data) >= 0) {
                            foreach ($arr_data as $val) {
                                // debug($val);
                                //  $empstatus = isset($employee_loan['EmployeeDetails']['status']) && $employee_loan['EmployeeDetails']['status']=="2" ? '(Resigned)' :'';
                                $status = $val['status'];
                                $name = $val['emp_name'] . $status;
                                $emp_id = $val['employee_id'];
                                $des = $val['designation'];
                                $dept = $val['department'];
                                $amt = $val['loan_amount'];
                                $ten = $val['tenure'];
                                $int = $val['intrest_rate'];
                                $emi = $val['emi_amount'];
                                $star = $val['start_month'];
                                $end = date('M-Y', strtotime($val['emi_end_month']));
                                $rem = $val['remarks'];
                                $userid = $val['userid'];
                                $join = date('d-m-Y', strtotime($val['join']));
                                $termin = ($val['termin'] != '') ? date('d-m-Y', strtotime($val['termin'])) : '';
                                //Edited by Akshay on 6-8-2024
                                $branch = $val['branch'];
                                $loan_status = $val['loan_status'];
                                $balanceamt = $val['loan_amount'] - $val['remarks'];
                                //End
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $k);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $emp_id);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $userid);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $name);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $branch);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $dept);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $des);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $join);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $termin);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $amt);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $ten);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $int);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $emi);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $star);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, $end);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, $rem);
                                //Edited by Akshay on 6-8-2024
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, $balanceamt);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, ($loan_status == 'Y' ? 'Completed' : 'Active'));
                                //End
                                foreach (range('A', 'Q') as $column_name) {
                                    $worksheet->getStyle($column_name . $rowcount)->getAlignment()->applyFromArray(
                                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                    );
                                }

                                $rowcount = $rowcount + 1;
                                $k++;
                            }
                        } else {
                            $msg = 'No Report found under this';
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $msg);
                        }
                        // $rowcount++;
                        //Edited by Akshay on 6-8-2024
                        $styleArray = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );
                        if (!isset($k) || $k <= 1) {
                            $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'H3')->applyFromArray($styleArray);
                        } else {
                            $objPHPExcel->getActiveSheet()->getStyle('A' . $start_row . ':' . 'R' . ($rowcount - 1))->applyFromArray($styleArray);
                        }
                        //End
                    }
 
                    if (count($arr_emp_loan_template) == 0) {
                        $worksheet->mergeCells('A3:H3');
                        $msg = 'No data available under the selected criteria.';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, 3, $msg);
                    }
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                } else {

                    $arr_data = isset($arr_emp_loan_template['employeeloan']) ? $arr_emp_loan_template['employeeloan'] : array();
                    if (count($arr_data) > 0) {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, 'Sl No');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'User ID');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Employee Name');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Branch');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Department');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), $rowcount, 'Designation');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), $rowcount, 'Joining Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), $rowcount, 'Termination Date');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, 'Loan Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, 'Tenure(month)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, 'Interest Rate(%)');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, 'EMI Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, 'EMI Start Month');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, 'EMI End Month');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, 'Paid');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
                        //Edited by Akshay on 6-8-2024
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, 'Balance Amount');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 16), $rowcount)->getFont()->setBold(true);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, 'Status');
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 17), $rowcount)->getFont()->setBold(true);
                        //End
                        $rowcount = $rowcount + 1;
                        $k = 1;
                        foreach ($arr_data as $val) {
                            // $empstatus = isset($employee_loan['EmployeeDetails']['status']) && $employee_loan['EmployeeDetails']['status']=="2" ? '(Resigned)' :'';
                            $status = $val['status'];
                            $name = $val['emp_name'] . $status;
                            $emp_id = $val['employee_id'];
                            $des = $val['designation'];
                            $dept = $val['department'];
                            $branch = $val['branch'];
                            $amt = $val['loan_amount'];
                            $ten = $val['tenure'];
                            $int = $val['intrest_rate'];
                            $emi = $val['emi_amount'];
                            $star = $val['start_month'];
                            $end = date('M-Y', strtotime($val['emi_end_month']));
                            $rem = $val['remarks'];
                            $userid = $val['userid'];
                            $join = date('d-m-Y', strtotime($val['join']));
                            $termin = ($val['termin'] != '') ? date('d-m-Y', strtotime($val['termin'])) : '';
                            //Edited by Akshay on 6-8-2024
                            $balanceamt = $val['loan_amount'] - $val['remarks'];
                            $loan_status = $val['loan_status'];
                            //End

                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), ($rowcount), $emp_id);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), ($rowcount), $userid);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), ($rowcount), $name);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), ($rowcount), $branch);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), ($rowcount), $dept);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 6), ($rowcount), $des);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 7), ($rowcount), $join);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 8), ($rowcount), $termin);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 9), $rowcount, $amt);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 10), $rowcount, $ten);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 11), $rowcount, $int);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 12), $rowcount, $emi);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 13), $rowcount, $star);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 14), $rowcount, $end);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 15), $rowcount, $rem);
                            //Edited by Akshay on 6-8-2024
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 16), $rowcount, $balanceamt);
                            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 17), $rowcount, ($loan_status == 'Y' ? 'Completed' : 'Active'));
                            //End
                            foreach (range('A', 'Q') as $column_name) {
                                $worksheet->getStyle($column_name . $rowcount)->getAlignment()->applyFromArray(
                                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                                );
                            }
                            $rowcount = $rowcount + 1;
                            $k++;
                        }
                    } else {
                        $worksheet->mergeCells('A3:H3');
                        $msg = 'No data available under the selected criteria.';
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, 3, $msg);
                    }
                    //Edited by Akshay on 6-8-2024
                    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    if (!isset($k) || $k <= 1) {
                        $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'H3')->applyFromArray($styleArray);
                    } else {
                        $objPHPExcel->getActiveSheet()->getStyle('A3:' . 'R' . ($rowcount - 1))->applyFromArray($styleArray);
                    }
                    //End
                }
                $objPHPExcel->getActiveSheet()->setTitle('Employee Loan');

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
                $this->render('Emploanreport');
                break;
        }
    }

    private function generatesummaryreport($mode) {
        $arr_form_data = $_REQUEST;
        $this->EditPunches->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');
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
        $fd = $arr_form_data['reportfrom'] . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }

        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $fields = 'EditPunches.*,Branch.branch_name,EmployeeDetails.first_name,EmployeeDetails.last_name';
                $joins = array(
                    array(
                        'table' => 'branches',
                        'alias' => 'Branch',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunches.branch_code = Branch.branch_code',
                            'Branch.status=1'
                        )
                    ),
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array(
                            'EditPunches.emp_id = EmployeeDetails.emp_id',
                            'EmployeeDetails.status=1'
                        )
                    )
                );
                $conditions[] = 'EmployeeDetails.emp_pkey="' . $leavepolicygroupid . '" and EditPunches.DEVICEID = 0 and EditPunches.LOGDATE between "' . $from . '" and "' . $to . '"';
                $arr_leavepolicy_details = $this->EditPunches->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions));

                $arr_leavepolicydetails_for_template[] = array(
                    'summary' => $arr_leavepolicy_details,
                        // 'employees'=>$arr_leavepolicy_employees
                );
            }
//      
//              foreach ($arr_leavepolicydetails_for_template as $key => $value) {
//                  foreach($value['summary'] as $ky => $vaal){
//            $int_days_present = count(array_keys($vaal["AttendanceRegister"], "P"));
//            $int_days_leave = count(array_keys($vaal["AttendanceRegister"], "L"));
//            $int_days_holidays = count(array_keys($vaal["AttendanceRegister"], "HO"));
//
//            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_present'] = $int_days_present;
//            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_leave'] = $int_days_leave;
//            $arr_leavepolicydetails_for_template[$key]['summary'][$ky]["AttendanceRegister"]['days_holidays'] = $int_days_holidays;
//            }
//        }
            // debug($resp_register);
//   debug($arr_leavepolicydetails_for_template);  

            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);

            //Set informations needed for report

            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            switch ($mode) {
                case 'pdf' :
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('reportsummary');
                    //   debug($view_output);
                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('L', 'A2', 'en');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('reportsummary.pdf', 'D');
                    // $this->render('reportsummary');                
                    break;
                case 'excel' :

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "_attendane.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee attence  Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Summary Attendance Employees Report");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    $worksheet->mergeCells('A1:F1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    for ($col = 'A'; $col !== 'Z'; $col++) {
                        $objPHPExcel->getActiveSheet()
                                ->getColumnDimension($col)
                                ->setAutoSize(true);
                    }

                    $rowcount = 2;
                    $columncount = 0;
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Employee Name');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 1), $rowcount, 'Employee Id');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 1), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 2), $rowcount, 'Designation');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 3), $rowcount, 'Joining Date');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 4), $rowcount, 'Department');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($columncount + 5), $rowcount, 'Branch');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Present Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Leave Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Holiday Days');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
                    $columnindex = $columncount + 9;
                    foreach ($arr_dates as $key => $date) {

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $date);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columnindex), $rowcount)->getFont()->setBold(true);
                        $columnindex++;
                    }
                    $rowcount = 3;
                    //$rowcount1=3;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $branch = isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : 'No Datas Found Under This Branch';
                        //echo $branch;die();                     
                        //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount,'branch');

                        $arr_data = $value['summary'];
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                        if (count($arr_data) >= 0) {
                            foreach ($arr_data as $key => $val) {
                                $columnindex = 0;
                                $name = $val['AttendanceRegister']['emp_name'];
                                //$name=$name.$key;
                                $present = $val['AttendanceRegister']['days_present'];
                                $leave = $val['AttendanceRegister']['days_leave'];
                                $holidays = $val['AttendanceRegister']['days_holidays'];
                                $id = $val['Info']['employee_id'];
                                $des = $val['Info']['designation'];
                                $join = $val['Info']['joining_date'];
                                $department = $val['Info']['department'];
                                $branch = $val['Info']['branch'];
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $des);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $join);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $department);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $present);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $leave);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $holidays);

                                $columnindex = $columnindex + 9;
                                foreach ($arr_dates as $key => $date) {
                                    $newIndex = 'FIELD' . ($key + 1);
                                    $dta = $val['AttendanceRegister'][$newIndex];
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $dta);
                                    $columnindex++;
                                }

                                $rowcount++;
                            }
                        }
                        $rowcount1 = $rowcount + 1;
                    }

                    $objPHPExcel->getActiveSheet()->setTitle('Attendance Policy');
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
                default :
                    $this->set('mode', '');
                    $this->render('reportsummary');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

}
