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

class VariableReportController extends AppController
{



    /**

     * Controller name

     *

     * @var string

     */

    //public $layout = "default";

    public $name = 'VariableReport';

    public $datatable;



    /**

     * This controller does not use a model

     *

     * @var array

     */

    public $uses = array(
        'EditPunches',
        'Attendance',
        'CentralControl',
        'UserCredentials',
        'EmployeeDetails',
        'EmployeeProfessionalDetails',
        'DeviceAttendance',
        'Departments',
        'Grades',
        'Verticals',
        'Units',
        'ReportCriterias',
        'AttendanceRegister',
        'SalaryHeadItems',
        'AttendanceRegisterReport',
        'DbConfig',
        'MobileUserauditor',
        'EmployeeLoan',

        'Taxsalarycomponents',
        'EmpCtcTransaction',
        'FinancialYear',
        'EmployeeGrossDetails',
        'LeaveRequests',
        'ReportAudit',
        'Item'
    ); //santhu

    public $components = array('MasterdataManagement');

    public function hrreports()
    {

        $arr_reporttypes = array(

            'Variable' => 'Variable Upload',
            //Edited by Akshay on 27-5-2024
            'Fixed' => 'Fixed Payment',
            //End
        );

        $this->set('arr_reporttypes', $arr_reporttypes);
    }


    public function changereporttype($type = '')
    {

        $this->autoRender = FALSE;

        if ($type != '') {

            $this->set('type', $type);

            switch ($type) {

                case 'Variable':

                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');

                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));

                    break;

                    //Edited by Akshay on 27-5-2024
                case 'Fixed':

                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');

                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));

                    break;
                    //End

                default:

                    echo "No criterias found";

                    break;
            }

            $this->render('showreport');
        } else {

            echo "No criterias found";
        }
    }

    public function addreportcriteria($type = '', $newindex = '', $str_currentcriterias = '')
    {
        $this->autoRender = FALSE;
        if ($type != '' && $str_currentcriterias != '') {
            $str_currentcriterias = "'" . str_replace(",", "','", $str_currentcriterias) . "'";
            $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type)))));

            $this->set('newindex', $newindex);
            $this->render('showcriteria');
        } else {
            return '';
        }
    }


    public function loadcriteriaitems($index, $str_criteria = '')
    {

        $this->autoRender = FALSE;
        if ($str_criteria != '') {
            $model = $str_criteria;
            if ($this->_modelExists($model)) {
                $this->set('index', $index);

                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
                $model = ($model == 'Units') ? 'Branches' : $model;
                $model = ($model == 'Item') ? 'Variables' : $model;
                $this->set('criteria', $model);
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
        $model = $str_criteria;
        $arr_requestdata = $this->request->data;
        $user = $this->Session->read('company_code');
        $conditions = array();

        if (isset($model) && $model != '') {
            if ($model != 'Item') {

                $this->{$model}->useDbConfig = $this->Session->read('ds');
            } else {

                $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
            }

            // if ($model != 'Item') {

            //    $conditions = array("status" => 1);

            // } else {

            //   $conditions = array("status" => 1);

            // }

            //$arr_criteriaItemsDB =$this->EmployeeDetails->query("SELECT * FROM emp_variable_upload ORDER BY salary_head_item_desc ASC");
            // $conditions = array("status" => 1);
            $user_group = $this->Session->read('user_group');
            $emp_pkey = $this->Session->read('emp_fkey');
            if ($model == "Units") {
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG' || $user == 'DEMO')) {
                    $cur_emp_key = $this->Session->read("emp_fkey");
                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
                    $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
                    $emp_pkey = $this->Session->read("emp_fkey");
                    // $conditions[] = array("Units.branch_code" => $cur_emp_branch);
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions[] = array("Units.branch_code" => $is_ho, "Units.status" => 1);
                    } else {
                        $conditions[] = array("Units.status" => 1);
                    }
                } else {
                    $conditions[] = array("Units.status" => 1);

                    // Edited by Akshay on 11-3-2025
                    if ($user_group == 2) {
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $company_code = $this->Session->read('company_code');
                        if ($company_code == 'GAAR' || $company_code == 'HRBL') {
                            $user_id = $this->Session->read("login_user_id"); //user id
                            $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                            $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;

                            if ($special_access != 1) {
                                $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                                $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                                $conditions['Units.branch_code !='] = $directors_branch;
                            }
                        }
                    }
                    // End

                }
            } elseif ($model == "EmployeeDetails") {
                if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG' || $user == 'DEMO')) {
                    $emp_pkey = $this->Session->read("emp_fkey");
                    // $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
                    $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                    $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                    if ($is_ho != 1) {
                        $conditions["EmployeeDetails.branch_code"] = $is_ho;
                    }
                }
            }
            if ($model == 'Item') {
                //$arr_order = array("SalaryHeadItems.item" => "ASC");
                $arr_order = array("TRIM(SalaryHeadItems.item)" => "ASC");
                //$conditions = array("status" => 1);
                $conditions = [
                    array("status" => 1),
                    array("item_type" => "Manually"),
                    array("value" => "Y")
                ];
                $arr_criteriaItemsDB = Set::extract('/SalaryHeadItems/.', $this->SalaryHeadItems->find("all", array("conditions" => $conditions, "order" => $arr_order)));
            } else {
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            }
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


                case 'Item':

                    foreach ($arr_criteriaItemsDB as $key => $value) {

                        $arr_criteriaItems[$key]['key'] = $value['salary_head_item_pkey'];

                        $arr_criteriaItems[$key]['text'] = $value['item'];

                        $key++;
                        //$arr_order = array("Item.salary_head_item_desc" => "ASC");

                    }


                    break;

                case 'Departments':

                    foreach ($arr_criteriaItemsDB as $key => $value) {

                        $arr_criteriaItems[$key]['key'] = $value['dept_code'];

                        $arr_criteriaItems[$key]['text'] = $value['dept_name'];

                        $key++;
                    }

                    break;

                case 'EmployeeDetails':

                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,status';

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

                    //$arr_order = array("EmployeeDetails.emp_name" => "ASC");
                    $arr_order = array("CONCAT(EmployeeDetails.first_name, IFNULL(EmployeeDetails.last_name, ' '))" => "ASC");

                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {

                        $conditions = array("status in(1,2)");
                    } else {

                        $conditions = array("status" => 1);
                    }
                    if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG' || $user == 'DEMO')) {
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;

                        if ($is_ho != 1 && !empty($is_ho)) {
                            $conditions["EmployeeDetails.branch_code"] = $is_ho;
                        }
                    }

                    // Edited by Akshay on 11-3-2025
                    elseif ($user_group == 2) {
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $user_id = $this->Session->read("login_user_id"); //user id
                        $company_code = $this->Session->read('company_code');
                        if ($company_code == 'GAAR' || $company_code == 'HRBL') {
                            $special_access = $this->EmployeeDetails->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                            $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;

                            if ($special_access != 1) {
                                $directors_branch = $this->EmployeeDetails->query("SELECT get_directors_branch_code() AS branch;");
                                $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                                $conditions['EmployeeDetails.branch_code !='] = $directors_branch;
                            }
                        }
                    }
                    // End


                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

                    $arr_emp = $this->EmployeeDetails->find(
                        "all",
                        array(

                            'fields' => $fields,

                            'joins' => $joins,

                            'conditions' => $conditions,
                            'order' => $arr_order

                        )
                    );

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

    public function reportAudit($type, $mode)
    {

        $this->autoRender = false;
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;
        $dataForHistory['report_from'] = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : '';

        $dataForHistory['report_to'] = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] : '';

        switch ($type) {

            case 'Variable':

                $dataForHistory['report_type'] = "Variable Upload";

                break;
            case 'Fixed':

                $dataForHistory['report_type'] = "Fixed Payment";

                break;
        }

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

                case 'Item':
                    $criteria_name_array[] = 'belonging to an Item';

                    break;

                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';

                    break;

                default:
                    break;
            }

            $items_array[] = implode(",", $arr_form_data[$criteria]);
            $items_count_array[] = count($arr_form_data[$criteria]);
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

    public function generatereport($type = '', $mode = '')
    {

        $this->autoRender = false;

        switch ($type) {

            case 'Variable':

                $this->generatevariableuploadreport($mode);

                break;

                //Edited by Akshay on 27-5-2024
            case 'Fixed':

                $this->generatefixeduploadreport($mode);

                break;
                //End

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
            $arr_empinformation_fields->getFieldHeadings('Units')->$arr_empinformation_fields->getFieldHeadings('Item')
        );

        $arr_emp_field_names = array(
            'EmployeeDetails' => $arr_empinformation_fields->getFieldNames('EmployeeDetails'),
            'EmployeeProfessionalDetails' => $arr_empinformation_fields->getFieldNames('EmployeeProfessionalDetails'),
            'Departments' => $arr_empinformation_fields->getFieldNames('Departments'),
            'Grades' => $arr_empinformation_fields->getFieldNames('Grades'),
            'Verticals' => $arr_empinformation_fields->getFieldNames('Verticals'),
            'Units' => $arr_empinformation_fields->getFieldNames('Units'),
            'SalaryHeadItems' => $arr_empinformation_fields->getFieldNames('SalaryHeadItems')
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

    private function generatevariableuploadreport($mode)
    {

        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $from = date('m-Y', strtotime($arr_form_data['reportfrom']));
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $conditions = array();
        $arr_salary_for_template = array();
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        // Edited by Akshay on 12-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmpCtcTransaction->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND ed.branch_code = '$is_ho' ";
            }
        }
        // End

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $needBranchWiseReport = false;
            $needItemWiseReport = false;

            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }

            $this->set('needBranchWiseReport', $needBranchWiseReport);

            if ($str_criteria_item == 'Item') {
                $needItemWiseReport = true;
            }
            $this->set('needItemWiseReport', $needItemWiseReport);
            $this->set('str_criteria_item', $str_criteria_item);
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
            $resign_condition = " and ei.emp_status in ('1','2') ";
            //$resign_condition =  "and ed.status  = '2'";
        } else {
            $resign_condition = " and ei.emp_status = '1'";
        }

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($str_criteria_item == 'Units') {
                    $arr_gross = $this->EmpCtcTransaction->query("SELECT DISTINCT CONCAT(ed.first_name, ' ', ed.last_name) AS empname,ed.emp_pkey,ed.status,
        vu.month_year,
        vu.emp_fkey,
        vu.emp_variables_upload_pkey,
        vu.salary_head_item_desc,
        vu.uploaded_amount,
        vu.head_operator,
        vu.head_type,
        vu.item_part,
        vu.remarks,
        vu.month_year,
        vu.creation_date,
         vu.created_by,
        ei.branch,ei.designation,ei.department,ei.EmpName,ei.employee_id,ei.emp_id,ei.branch_code,ei.emp_status 
    FROM emp_details ed
    Left join emp_variables_upload vu ON (ed.emp_pkey = vu.emp_fkey)
    Left join employee_info ei ON (ed.emp_pkey = ei.emp_pkey)
    WHERE ei.branch_code = '$leavepolicygroupid' and vu.status=1 and vu.month_year ='$from' $resign_condition
    ORDER BY ei.branch ASC,ei.EmpName ASC
");
                } else if ($str_criteria_item == 'EmployeeDetails') {
                    $arr_gross = $this->EmpCtcTransaction->query("
     SELECT DISTINCT CONCAT(ed.first_name, ' ', ed.last_name) AS empname,ed.emp_pkey,
        vu.month_year,
        vu.emp_fkey,
        vu.emp_variables_upload_pkey,
        vu.salary_head_item_desc,
        vu.uploaded_amount,
        vu.head_operator,
        vu.head_type,
        vu.item_part,
        vu.remarks,
        vu.month_year,
        vu.creation_date,
         vu.created_by,
        ei.branch,ei.designation,ei.department,ei.EmpName,ei.employee_id,ei.branch_code,ei.emp_id,ei.emp_status ,ed.status   
    FROM emp_details ed
    Left join  emp_variables_upload vu ON (vu.emp_fkey = ed.emp_pkey)
    Left join employee_info ei ON (ei.emp_pkey = ed.emp_pkey)
    WHERE ei.emp_pkey = $leavepolicygroupid and vu.status= 1 and vu.month_year = '$from' $resign_condition
    ORDER BY ei.branch ASC,ei.EmpName ASC
");
                } else {

                    // Edited by Akshay on 17-3-2025
                    $user = $this->Session->read('company_code');
                    if ($user_group == 2 && ($user == 'GAAR' || $user == 'HRBL')) {
                        $user_id = $this->Session->read("login_user_id"); //user id
                        $special_access = $this->EmpCtcTransaction->query("SELECT COUNT(*) AS special_access FROM special_access WHERE user_id = '$user_id' AND status = 1;");
                        $special_access = ($special_access[0][0]['special_access'] > 0) ? 1 : 0;
                        $condition = '';
                        if ($special_access != 1) {
                            $directors_branch = $this->EmpCtcTransaction->query("SELECT get_directors_branch_code() AS branch;");
                            $directors_branch = isset($directors_branch[0][0]['branch']) ? $directors_branch[0][0]['branch'] : '';
                            $branch_condition .= " AND ed.branch_code != '$directors_branch' ";
                        }
                    }
                    // End

                    // Edited by Akshay on 12-2-2025
                    $arr_gross = $this->EmpCtcTransaction->query("
    SELECT DISTINCT CONCAT(ed.first_name, ' ', ed.last_name) AS empname,ed.emp_pkey,
        vu.month_year,
        vu.emp_fkey,
        vu.emp_variables_upload_pkey,
        vu.salary_head_item_desc,
        vu.uploaded_amount,
        vu.head_operator,
        vu.head_type,
        vu.item_part,
        vu.remarks,
        vu.month_year,
        vu.creation_date,
         vu.created_by,
        ei.branch,ei.designation,ei.department,ei.EmpName,ei.employee_id,ei.emp_id,ei.branch_code,ei.emp_status,ed.status 
    FROM emp_details ed 
    Left join  emp_variables_upload vu on (vu.emp_fkey = ed.emp_pkey)
    Left join employee_info ei on (ei.emp_pkey = ed.emp_pkey)
    WHERE vu.salary_head_item_fkey = $leavepolicygroupid and vu.status= 1 and vu.month_year ='$from' $resign_condition $branch_condition ORDER BY vu.salary_head_item_desc ASC,ei.EmpName ASC
     
");
                    // End
                }

                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = array(
                        'summary' => $arr_gross,

                    );
                }
            }
        }
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        $user_id = $this->Session->read('login_user_id');
        $this->set('user_id', $user_id);
        $date_time = date('d-m-Y H:i');
        $this->set('date_time', $date_time);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);

        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('variable');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('PFSummary.pdf', 'D');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Variable Upload.XLSX" . $year . "-" . $month . ".xlsx" : "Variable Upload" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Variable Upload - " . $mname . "  "  . $year);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);

                for ($col = 'A'; $col !== 'S'; $col++) {

                    $objPHPExcel->getActiveSheet()

                        ->getColumnDimension($col)

                        ->setAutoSize(true);
                }

                $worksheet->mergeCells('A1:P1');

                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                );
                date_default_timezone_set('Asia/Kolkata');
                $worksheet->mergeCells("A2:P2");
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                if (!empty($arr_salary_for_template)) {

                    $rowcount = 3;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Sl No');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Employee ID');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'User ID');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Employee Name');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Branch');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'Department');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'Designation');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, 'Variable Item');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, 'Amount');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 'Operator');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 'Type');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, 'Item Part');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, 'Affected Month');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, 'Remarks');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, 'Uploaded By');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 14, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, 'Date and Time');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 15, $rowcount)->getFont()->setBold(true);


                    $rowcount = 4;

                    $i = 1;
                    $total = 0;
                    //if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) {

                    foreach ($arr_salary_for_template as $value) {

                        $arr_daata = $value['summary'];
                        $employees = $value;
                        if (count($value) >= 0) {
                            $col = 0;

                            // $i=1;
                            $arr_e = $employees['summary'];

                            foreach ($arr_daata as $employee => $val) {

                                $status = (isset($val['ei']['emp_status'])) && $val['ei']['emp_status'] == "2" ? '(Resigned)' : '';
                                //debug($status);exit();
                                $name = $val['ei']['EmpName'] . $status;
                                $id = $val['ei']['emp_id'];
                                $user_id = $val['ei']['employee_id'];
                                $branch = $val['ei']['branch'];
                                $department = $val['ei']['department'];
                                $designation = $val['ei']['designation'];
                                $vitem = $val['vu']['salary_head_item_desc'];
                                $amount = $val['vu']['uploaded_amount'];
                                $operator = $val['vu']['head_operator'];
                                $type = $val['vu']['head_type'];
                                $item = $val['vu']['item_part'];
                                $amonth = $val['vu']['month_year'];
                                $remarks = $val['vu']['remarks'];
                                $uploaded = $val['vu']['created_by'];
                                $uploaded_time = $val['vu']['creation_date'];
                                $total += $amount;

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $i++);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $user_id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, ($department));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, ($designation));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, ($vitem));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $amount);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, ($operator));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, ($type));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, ($item));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, ($amonth));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, ($remarks));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, ($uploaded));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, ($uploaded_time));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $rowcount++;
                            }
                        }
                    }

                    //                      $objPHPExcel->getActiveSheet()
                    // ->getStyle('A3:P400')
                    // ->getAlignment()
                    // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    //  $objPHPExcel->getActiveSheet()
                    // ->getStyle('A1:P1')
                    // ->getAlignment()
                    // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $col = 0;
                    $worksheet->mergeCells('A' . $rowcount . ':H' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);


                    $worksheet->mergeCells('I' . $rowcount . ':P' . $rowcount);
                    $worksheet->getStyle('I' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $worksheet->setCellValueByColumnAndRow(8, $rowcount, $total);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8, $rowcount)->getFont()->setBold(true);

                    $rowcount++;
                    $BStyle = array(

                        'borders' => array(

                            'allborders' => array(

                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )

                        )

                    );


                    $row = $rowcount - 1;
                    $objPHPExcel->getActiveSheet()->getStyle('A1:P' . $row)->applyFromArray($BStyle);
                    foreach (range('A', 'P') as $columnID) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                    }
                } else {

                    $rowcount = 1;
                    $worksheet->mergeCells('A' . $rowcount . ':P' . $rowcount);
                    $worksheet->mergeCells('A1:P1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, "Variable Upload - " . $mname . " "  . $year);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $rowcount = 2;
                    $worksheet->mergeCells('A2:P2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, "Report run by" . $user_id . " "  . $date_time);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells("A3:P3");
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(false);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
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
                $objPHPExcel->getActiveSheet()->getStyle('A1:P' . $row)->applyFromArray($BStyle);

                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('Variable Upload');

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

            case 'print':

                //   echo "entered in";

                $this->set('mode', 'print');

                $this->render('variable');

                break;

            default:

                $this->set('mode', '');

                $this->render('variable');

                break;
        }
    }


    //Edited by Akshay on 27-5-2024
    private function generatefixeduploadreport($mode)
    {

        $arr_form_data = $_REQUEST;
        $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
        $from = date('m-Y', strtotime($arr_form_data['reportfrom']));
        $fromDate = date('Y-m', strtotime($arr_form_data['reportfrom']));
        if (isset($arr_form_data['hidden-criteria' . 1]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        if (isset($arr_form_data[$arr_form_data['hidden-criteria' . 1]]) == 0) {
            echo "Choose Criteria ";
            return false;
        }
        $conditions = array();
        $arr_salary_for_template = array();
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];

        // Edited by Akshay on 12-2-2025
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $branch_condition = "";
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->EmpCtcTransaction->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmpCtcTransaction->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branch_condition = " AND ed.branch_code = '$is_ho' ";
            }
        }
        // End

        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
            $needBranchWiseReport = false;
            $needItemWiseReport = false;

            if ($str_criteria_item == 'Units') {
                $needBranchWiseReport = true;
            }

            $this->set('needBranchWiseReport', $needBranchWiseReport);

            if ($str_criteria_item == 'Item') {
                $needItemWiseReport = true;
            }
            $this->set('needItemWiseReport', $needItemWiseReport);
            $this->set('str_criteria_item', $str_criteria_item);
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
            $resign_condition = " and ei.emp_status in ('1','2') ";
            //$resign_condition =  "and ed.status  = '2'";
        } else {
            $resign_condition = " and ei.emp_status = '1'";
        }

        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                if ($str_criteria_item == 'Units') {
                    try {
                        $arr_gross = $this->EmpCtcTransaction->query("SELECT DISTINCT CONCAT(ed.first_name, ' ', ed.last_name) AS empname,ed.emp_pkey,ed.status, uc.user_id,
                        vu.*,
                        ei.branch,ei.designation,ei.department,ei.EmpName,ei.employee_id,ei.emp_id,ei.branch_code,ei.emp_status, ei.joining_date,
                        COALESCE(
                                    (SELECT CONCAT(EmpName, ' - ', employee_id) FROM employee_info WHERE emp_pkey = (SELECT emp_fkey FROM user_credentials WHERE user_id = vu.created_by)),
                                    vu.created_by
                                ) AS created_by 
                        FROM emp_details ed
                        Left join emp_fixed_component_upload vu ON (ed.emp_pkey = vu.emp_fkey)
                        Left join employee_info ei ON (ed.emp_pkey = ei.emp_pkey)
                        Left join user_credentials uc ON (uc.emp_fkey = ed.emp_pkey)
                        WHERE ei.branch_code = '$leavepolicygroupid' and vu.status=1 and DATE_FORMAT(vu.start_date_effective, '%m-%Y') <= '$from'
                        AND (DATE_FORMAT(vu.end_date_effective, '%m-%Y') >= '$from' OR vu.end_date_effective IS NULL OR vu.end_date_effective = '0000-00-00')
                        AND vu.start_date_effective != '0000-00-00' 
                        $resign_condition
                        ORDER BY ei.branch ASC,ei.EmpName ASC
                    ");
                    } catch (Exception $e) {
                        debug($e);
                    }
                } else if ($str_criteria_item == 'EmployeeDetails') {
                    try {
                        $arr_gross = $this->EmpCtcTransaction->query("SELECT DISTINCT CONCAT(ed.first_name, ' ', ed.last_name) AS empname,ed.emp_pkey, uc.user_id,
                           vu.*,
                           ei.branch,ei.designation,ei.department,ei.EmpName,ei.employee_id,ei.branch_code,ei.emp_id,ei.emp_status ,ed.status, ei.joining_date,
                           COALESCE(
                                    (SELECT CONCAT(EmpName, ' - ', employee_id) FROM employee_info WHERE emp_pkey = (SELECT emp_fkey FROM user_credentials WHERE user_id = vu.created_by)),
                                    vu.created_by
                                ) AS created_by    
                           FROM emp_details ed
                           Left join  emp_fixed_component_upload vu ON (vu.emp_fkey = ed.emp_pkey)
                           Left join employee_info ei ON (ei.emp_pkey = ed.emp_pkey)
                           Left join user_credentials uc ON (uc.emp_fkey = ed.emp_pkey)
                           WHERE ei.emp_pkey = $leavepolicygroupid and vu.status= 1 and DATE_FORMAT(vu.start_date_effective, '%m-%Y') <= '$from'
                           AND (DATE_FORMAT(vu.end_date_effective, '%m-%Y') >= '$from' OR vu.end_date_effective IS NULL OR vu.end_date_effective = '0000-00-00') 
                           AND vu.start_date_effective != '0000-00-00'  
                           $resign_condition
                           ORDER BY ei.branch ASC,ei.EmpName ASC
                       ");
                    } catch (Exception $e) {
                        debug($e);
                    }
                } else {
                    try {
                        // Edited by Akshay on 12-2-2025
                        $arr_gross = $this->EmpCtcTransaction->query("SELECT DISTINCT CONCAT(ed.first_name, ' ', ed.last_name) AS empname,ed.emp_pkey, uc.user_id,
                            vu.*,
                            ei.branch,ei.designation,ei.department,ei.EmpName,ei.employee_id,ei.emp_id,ei.branch_code,ei.emp_status,ed.status, ei.joining_date,
                            COALESCE(
                                    (SELECT CONCAT(EmpName, ' - ', employee_id) FROM employee_info WHERE emp_pkey = (SELECT emp_fkey FROM user_credentials WHERE user_id = vu.created_by)),
                                    vu.created_by
                                ) AS created_by  
                            FROM emp_details ed 
                            Left join  emp_fixed_component_upload vu on (vu.emp_fkey = ed.emp_pkey)
                            Left join employee_info ei on (ei.emp_pkey = ed.emp_pkey)
                            Left join user_credentials uc ON (uc.emp_fkey = ed.emp_pkey)
                            WHERE vu.salary_head_item_fkey = $leavepolicygroupid and vu.status= 1 and DATE_FORMAT(vu.start_date_effective, '%m-%Y') <= '$from'
                            AND (DATE_FORMAT(vu.end_date_effective, '%m-%Y') >= '$from' OR vu.end_date_effective IS NULL OR vu.end_date_effective = '0000-00-00') 
                            AND vu.start_date_effective != '0000-00-00' 
                            $resign_condition 
                            $branch_condition
                            ORDER BY vu.salary_head_item_desc ASC,ei.EmpName ASC
                            
                        ");
                        // End
                    } catch (Exception $e) {
                        debug($e);
                    }
                }

                if (!empty($arr_gross)) {
                    $arr_salary_for_template[] = array(
                        'summary' => $arr_gross,

                    );
                }
            }
        }
        $this->set('arr_salary_for_template', $arr_salary_for_template);
        $cr = $arr_form_data['select-criteria1'];
        $this->set('cr', $cr);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        $this->set('month', $from);
        $user_id = $this->Session->read('login_user_id');
        $this->set('user_id', $user_id);
        $date_time = date('d-m-Y H:i');
        $this->set('date_time', $date_time);
        $f = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $time = strtotime($f);
        $month = date("m", $time);
        $mname = date('F', mktime(0, 0, 0, $month, 10));
        $month1 =  $month . '-01';
        $year = date("Y", $time);
        $this->set('mname', $mname);
        $this->set('year', $year);

        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('fixed');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('PFSummary.pdf', 'D');
                break;
            case 'excel':
                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "_Fixed Payment" . $year . "-" . $month . ".xlsx" : "Fixed Payment" . strtotime() . ".xlsx";
                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Information Report By Forsight");
                $objPHPExcel->setActiveSheetIndex(0);
                $worksheet = $objPHPExcel->getActiveSheet();
                $worksheet->setCellValueByColumnAndRow(0, 1, "Fixed Payment - " . $mname . "  "  . $year);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);

                for ($col = 'A'; $col !== 'S'; $col++) {

                    $objPHPExcel->getActiveSheet()

                        ->getColumnDimension($col)

                        ->setAutoSize(true);
                }

                $worksheet->mergeCells('A1:I1');

                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(

                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)

                );
                date_default_timezone_set('Asia/Kolkata');
                $worksheet->mergeCells("A2:I2");
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                );


                if (!empty($arr_salary_for_template)) {

                    $rowcount = 3;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, 'Sl No');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, 'Employee ID');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, 'User ID');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, 'Employee Name');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 3, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, 'Branch');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 4, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, 'Department');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 5, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, 'Designation');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 6, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, 'Date of Joining');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 7, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, 'Fixed Payment Item');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 8, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, 'Amount');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 9, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, 'Operator');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 10, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, 'Start Month');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 11, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, 'End Month');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 12, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, 'Occurence');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 13, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, 'Remarks');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 14, $rowcount)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, 'Uploaded Date and Time');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 15, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount, 'Uploaded By');

                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col + 16, $rowcount)->getFont()->setBold(true);


                    $rowcount = 4;

                    $i = 1;
                    $total = 0;
                    //if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) {

                    foreach ($arr_salary_for_template as $value) {

                        $arr_daata = $value['summary'];
                        $employees = $value;
                        if (count($value) >= 0) {
                            $col = 0;

                            // $i=1;
                            $arr_e = $employees['summary'];

                            foreach ($arr_daata as $employee => $val) {

                                $status = (isset($val['ei']['emp_status'])) && $val['ei']['emp_status'] == "2" ? '(Resigned)' : '';
                                //debug($status);exit();
                                $name = $val[0]['empname'] . $status;
                                $id = $val['ei']['employee_id'];
                                $user_id = $val['uc']['user_id'];
                                $branch = $val['ei']['branch'];
                                $department = $val['ei']['department'];
                                $designation = $val['ei']['designation'];
                                $vitem = $val['vu']['salary_head_item_desc'];
                                $doj = isset($val['ei']['joining_date']) ? date('d-m-Y', strtotime($val['ei']['joining_date'])) : '';
                                if (isset($val['vu']['start_date_effective']) && $val['vu']['start_date_effective'] != '0000-00-00') {
                                    $start_date = isset($val['vu']['start_date_effective']) ? date('m-Y', strtotime($val['vu']['start_date_effective'])) : '';
                                } else {
                                    $start_date = '';
                                }
                                if (isset($val['vu']['end_date_effective']) && $val['vu']['end_date_effective'] != '0000-00-00') {
                                    $end_date = isset($val['vu']['end_date_effective']) ? date('m-Y', strtotime($val['vu']['end_date_effective'])) : '';
                                } else {
                                    $end_date = '';
                                }
                                $amount = isset($val['vu']['uploaded_amount']) ? $val['vu']['uploaded_amount'] : '';
                                $operator = isset($val['vu']['head_operator']) ? $val['vu']['head_operator'] : '';
                                $remarks = isset($val['vu']['remarks']) ? $val['vu']['remarks'] : '';
                                $uploaded = isset($val[0]['created_by']) ? $val[0]['created_by'] : '';
                                $uploaded_time = isset($val['vu']['creation_date']) ? date('d-m-Y H:i:s', strtotime($val['vu']['creation_date'])) : '';
                                // $occurance = $val['vu']['occurance'];
                                switch ($val['vu']['occurance']) {
                                    case 3:
                                        $occurance =  "Monthly";
                                        break;
                                    case 4:
                                        $occurance = "Bi-Monthly";
                                        break;
                                    case 5:
                                        $occurance = "Quarterly";
                                        break;
                                    case 2:
                                        $occurance = "Half-Yearly";
                                        break;
                                    case 1:
                                        $occurance = "Yearly";
                                        break;
                                    default:
                                        $occurance = "";
                                        break;
                                }
                                $total += $amount;

                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount, $i++);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount, $id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 1) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount, $user_id);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 2) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount, $name);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 3) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount, $branch);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 4) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount, ($department));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 5) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount, ($designation));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 6) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount, ($doj));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 7) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount, $vitem);
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 8) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount, ($amount));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 9) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount, ($operator));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 10) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount, ($start_date));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 11) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount, ($end_date));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 12) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount, ($occurance));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 13) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount, ($remarks));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 14) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount, ($uploaded_time));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 15) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount, ($uploaded));
                                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col + 16) . $rowcount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                                $rowcount++;
                            }
                        }
                    }

                    //                      $objPHPExcel->getActiveSheet()
                    // ->getStyle('A3:P400')
                    // ->getAlignment()
                    // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    //  $objPHPExcel->getActiveSheet()
                    // ->getStyle('A1:P1')
                    // ->getAlignment()
                    // ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $col = 0;
                    $worksheet->mergeCells('A' . $rowcount . ':I' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $worksheet->setCellValueByColumnAndRow(0, $rowcount, "Total");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);


                    $worksheet->mergeCells('J' . $rowcount . ':Q' . $rowcount);
                    $worksheet->getStyle('J' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,)
                    );
                    $worksheet->setCellValueByColumnAndRow(9, $rowcount, $total);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9, $rowcount)->getFont()->setBold(true);

                    $rowcount++;
                    $BStyle = array(

                        'borders' => array(

                            'allborders' => array(

                                'style' => PHPExcel_Style_Border::BORDER_THIN

                            )

                        )

                    );


                    $row = $rowcount - 1;
                    $objPHPExcel->getActiveSheet()->getStyle('A3:Q' . $row)->applyFromArray($BStyle);
                    foreach (range('A', 'Q') as $columnID) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                    }
                } else {

                    $rowcount = 1;
                    $worksheet->mergeCells('A1:I1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, "Fixed Payment - " . $mname . " "  . $year);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $rowcount = 2;
                    $worksheet->mergeCells('A2:I2');
                    $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    );
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, "Report run by" . $user_id . " "  . $date_time);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    $worksheet->mergeCells("A3:P3");
                    $worksheet->setCellValueByColumnAndRow(0, 3, "No data available under the selected criteria.");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setBold(false);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->getFont()->setSize(13);
                    $worksheet->getStyle('A3')->getAlignment()->applyFromArray(
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
                $objPHPExcel->getActiveSheet()->getStyle('A3:P' . $row)->applyFromArray($BStyle);

                $objPHPExcel->getActiveSheet()->setShowGridlines(false);
                $objPHPExcel->getActiveSheet()->setTitle('Fixed Payment');

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

            case 'print':

                //   echo "entered in";

                $this->set('mode', 'print');

                $this->render('fixed');

                break;

            default:

                $this->set('mode', '');

                $this->render('fixed');

                break;
        }
    }
    //End
}
