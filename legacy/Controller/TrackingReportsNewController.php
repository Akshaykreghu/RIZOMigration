<?php

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
class TrackingReportsNewController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'TrackingReportsNew';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $helpers = array('GoogleMap');
    public $uses = array('UserCredentials', 'EmployeeDetails', 'Units', 'DeviceAttendance', 'AttendanceRegister', 'MobileUserauditor', 'CompanyContactInfo', 'DbConfig', 'ReportCriterias', 'MobileUserTracking', 'ReportAudit', 'CentralUserCredentials');
    public $components = array('DatatablesManagement', 'MasterdataManagement');


    public function hrreports()
    {
        $current_feature_id = $this->Session->read('current_feature_id');
        $user_group = $this->Session->read('user_group');
        $is_addon = (isset($current_feature_id) && !empty($current_feature_id));

        if ($user_group == 2 && !$is_addon) {
            $arr_reporttypes = array(
                'Customervisit' => 'Customer Visit Detailed Report'
            );
        } else {
            $arr_reporttypes = array(
                'AttendanceLocation' => 'Attendance - Location Report',
                'Customervisit' => 'Customer Visit Detailed Report',
                'KmTravelled' => 'Customer Visits - Distance Travelled'
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
        //         debug($type);die();
        if ($type != '') {
            $this->set('type', $type);
            $user_group = $this->Session->read('user_group');
            $current_feature_id = $this->Session->read('current_feature_id');
            $is_addon = (isset($current_feature_id) && !empty($current_feature_id));
            $is_restricted = ($user_group == 2 && !$is_addon);

            switch ($type) {

                case 'AttendanceLocation':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'MobileTrack':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;

                case 'Client':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'Customervisit':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    if ($is_restricted) {
                        $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type, 'reportcriteria' => 'EmployeeDetails')))));
                    } else {
                        $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    }
                    break;

                case 'KmTravelled':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
                    break;
                case 'KmTravelledTracking':
                    $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
                    $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reporttype' => $type)))));
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

            $user_group = $this->Session->read('user_group');
            $current_feature_id = $this->Session->read('current_feature_id');
            $is_addon = (isset($current_feature_id) && !empty($current_feature_id));
            $is_restricted = ($user_group == 2 && !$is_addon);

            if ($is_restricted) {
                $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type, 'reportcriteria' => 'EmployeeDetails')))));
            } else {
                $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.', $this->ReportCriterias->find("all", array("conditions" => array("status" => 1, 'reportcriteria NOT IN(' . $str_currentcriterias . ')', 'reporttype' => $type)))));
            }

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
            if ($this->_modelExists($model)) {
                $this->set('index', $index);
                $model = ($model == 'EmployeeDetails') ? 'Employees' : $model;
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
        if (isset($model) && $model != '') {
            $this->{$model}->useDbConfig = $this->Session->read('ds');

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
            } else if ($model == 'Units') {
                $user_group = $this->Session->read('user_group');
                $emp_pkey = $this->Session->read('emp_fkey');
                $feature_id = $this->Session->read('current_feature_id');
                $context = $this->MasterdataManagement->getFeatureAccessContext();

                if ($user_group == 1) {
                    $branches = $this->MasterdataManagement->getBranchesForAll();
                } else if ($context['has_access']) {
                    if ($context['is_hierarchy']) {
                        $branches = $this->MasterdataManagement->getHierarchyBranches($emp_pkey);
                    } else {
                        $branches = $this->MasterdataManagement->getAllocatedBranches($emp_pkey, $feature_id);
                    }
                } else {
                    $branches = $this->MasterdataManagement->getOwnBranch($emp_pkey);
                }

                $arr_criteriaItems = array();
                if (!empty($branches)) {
                    foreach ($branches as $key => $b) {
                        $arr_criteriaItems[$key]['key'] = $b['b']['branch_code'];
                        $arr_criteriaItems[$key]['text'] = $b['b']['branch_name'];
                    }
                }
                echo json_encode($arr_criteriaItems);
                $this->autoRender = false;
                return;
            } else if ($model == 'DayTimeProcedures') {
                $arr_order = array("DayTimeProcedures.day_time_desc" => "ASC");
                $conditions = array("active" => 1);
            } else if ($model == 'LeavePolicyGroup') {
                $arr_order = array("LeavePolicyGroup.LEAVEPOLICY_GROUP_NAME" => "ASC");
                $conditions = array("status" => 1);
            } else {
                $conditions = array("status" => 1);
            }

            $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions, "order" => $arr_order)));
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
                case 'Verticals':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['vert_code'];
                        $arr_criteriaItems[$key]['text'] = $value['vert_name'];
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

                case 'LeavePolicyGroup':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['LEAVEPOLICY_GROUP_ID'];
                        $arr_criteriaItems[$key]['text'] = $value['LEAVEPOLICY_GROUP_NAME'];
                        $key++;
                    }
                    break;


                case 'EmployeeDetails':

                    $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT(first_name,"  ",ifnull(last_name," ")," - ",EmployeeProfessionalDetails.emp_company_id) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no,EmployeeDetails.status';
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

                    $user_group = $this->Session->read('user_group');
                    $emp_pkey = $this->Session->read('emp_fkey');
                    $feature_id = $this->Session->read('current_feature_id');
                    $context = $this->MasterdataManagement->getFeatureAccessContext();

                    if ($user_group == 1) {
                        // Admin: no extra conditions, sees all employees
                    } else if ($context['has_access']) {
                        if ($context['is_hierarchy']) {
                            $conditions[] = array("EmployeeProfessionalDetails.attr1" => $emp_pkey);
                        } else {
                            $branches = $this->MasterdataManagement->getAllocatedBranches($emp_pkey, $feature_id);
                            $branch_codes = array();
                            if (!empty($branches)) {
                                foreach ($branches as $b) {
                                    $branch_codes[] = $b['b']['branch_code'];
                                }
                            }
                            $conditions[] = array("EmployeeDetails.branch_code" => $branch_codes);
                        }
                    } else {
                        $own_branch = $this->MasterdataManagement->getOwnBranch($emp_pkey);
                        $branch_code = isset($own_branch[0]['b']['branch_code']) ? $own_branch[0]['b']['branch_code'] : '0';
                        $conditions[] = array("EmployeeDetails.branch_code" => $branch_code);
                    }

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
            echo json_encode($arr_criteriaItems);
        }
    }

    public function reportAudit($type, $mode)
    {
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'AttendanceLocation':
                $dataForHistory['report_type'] = "Attendance - Location Report";
                break;
            case 'Client':
                $dataForHistory['report_type'] = "Customer Visits Report";
                break;
            case 'Customervisit':
                $dataForHistory['report_type'] = "Customer Visit Detailed Report";
                break;
            case 'KmTravelled':
                $dataForHistory['report_type'] = "Customer Visits - Distance Travelled Report";
                break;
            case 'KmTravelledTracking':
                $dataForHistory['report_type'] = "Mobile Tracking - Distance Travelled Report";
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
                case 'Units':
                    $criteria_name_array[] = 'belonging to a Branch';
                    break;
                case 'EmployeeDetails':
                    $criteria_name_array[] = 'belonging to an Employee';
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

    public function generatereport($type = '', $mode = '')
    {
        $this->autoRender = false;
        $company_code = strtoupper($this->Session->read('company_code'));
        $company = array("a" => "NRTH", "b" => "EVPR", "c" => "PVUI", "d" => "SBRR", "e" => "ALDS", "f" => "CIES");
        switch ($type) {
            case 'AttendanceLocation':
                $this->generateAttendanceLocation($mode);
                break;
            case 'MobileTrack':
                $this->generateMobileTrack($mode);
                break;
            case 'Client':
                $this->generateClient($mode);
                break;
            case 'Customervisit'://debug(array_search($company_code,$company,true));
                if (array_search($company_code, $company, true)) {
                    $this->generateCustomer_google($mode);
                } else {
                    $this->generateCustomer($mode);
                }
                break;
            case 'KmTravelled':
                if (array_search($company_code, $company, true)) {
                    $this->generateKmTravelled_google($mode);
                } else {
                    $this->generateKmTravelled($mode);
                }
                break;
            case 'KmTravelledTracking':
                $this->generateKmTravelledTracking($mode);
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

    public function getEmployeesByBranch()
    {
        $this->autoRender = false;
        $branch = isset($_REQUEST['branch']) ? $_REQUEST['branch'] : '0';
        $emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $context = $this->MasterdataManagement->getFeatureAccessContext();

        if ($user_group == 1) {
            $employees = $this->MasterdataManagement->getAllEmployeesByBranch($branch, 0);
        } else if ($context['has_access']) {
            if ($context['is_hierarchy']) {
                $employees = $this->MasterdataManagement->getHierarchyEmployeesByBranch($emp_pkey, $branch);
            } else {
                $employees = $this->MasterdataManagement->getAllEmployeesByBranch($branch, 0);
            }
        } else {
            $employees = $this->MasterdataManagement->getAllEmployeesByBranch($branch, 0);
        }

        $result = array();
        if (!empty($employees)) {
            foreach ($employees as $emp) {
                $obj = new stdClass();
                $obj->id = $emp[0]['id'];
                $obj->text = $emp[0]['text'];
                $result[] = $obj;
            }
        }
        echo json_encode($result);
    }





    private function generateClient($mode)
    {
        $arr_form_data = $_REQUEST;
        //debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $criteria = isset($arr_form_data['select-criteria1']) ? $arr_form_data['select-criteria1'] : '';
        $report_month = date('Y-m', strtotime($arr_form_data['reportfrom']));
        $fromdate = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
        $todate = date("Y-m-t", strtotime($arr_form_data['reportfrom']));
        $from_dates = isset($fromdate) ? $fromdate . ' 00:00' : '';
        $to_dates = isset($todate) ? $todate . ' 23:59' : '';
        $arr_location_updates = array();
        if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
            $emp_fkey = isset($arr_form_data['EmployeeDetails']['0']) ? $arr_form_data['EmployeeDetails']['0'] : 0;
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
            $arr_location = $this->MobileUserauditor->query("SELECT mob_user_locations.location,mob_user_locations.latitude,mob_user_locations.longitude,"
                . "employee_info.EmpName,employee_info.employee_id,employee_info.branch FROM `mob_user_locations` "
                . "left join user_credentials on(user_credentials.user_id =  mob_user_locations.user_id) "
                . "left join employee_info on(employee_info.emp_pkey = user_credentials.emp_fkey) WHERE mob_user_locations.user_id = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
            //        $arr_location_updates = $this->MobileUserauditor->query("SELECT EmpName , EMP_PKEY,employee_id, customer_name ,location ,branch,designation,department,purpose, INTIME, 
//                                                        INVAL, (CASE WHEN INVAL = 'in' AND OUTVAL = 'in' THEN NULL ELSE OUTTIME END) AS OUTTIME ,
//                                                        OUTVAL FROM (SELECT MO.C1 AS OUTVAL,MO.EMP_PKEY,MO.purpose,MO.employee_id,MO.branch,
//                                                        MO.designation,MO.department,MO.EmpName ,MO.customer_name ,MO.location , MO.created_time AS OUTTIME,
//                                                        @S AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)' ,
//                                                        @LOGIN := @S AS INTIME, @C AS 'LEAD(c1) over(PARTITION BY emp_pkey ORDER BY logdate)',
//                                                        @INVAL := @C AS INVAL,(CASE WHEN @PARTITION_BY_COLUMN = customer_name OR 
//                                                        @_SEASON IS NULL THEN @S := created_time
//                                                        END) TMP_VALUE, (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C := C1
//                                                        END) TMP_VALUE1,(@PARTITION_BY_COLUMN := customer_name) PARTITION_COLUMN 
//                                                        FROM 
//                                                        (SELECT ed.emp_pkey,ed.branch,ed.designation,ed.department,ed.employee_id, ed.EmpName, att.created_time,
//                                                        att.purpose,customer_name ,location , lcase(att.stepinout) c1
//                                                        FROM (SELECT @PARTITION_BY_COLUMN = NULL, @S := NULL, @C := NULL, @LOGIN := NULL, @INVAL := NULL ) 
//                                                        vars, mob_user_locations att, employee_info ed,user_credentials uc where att.user_id= uc.user_id
//                                                        and uc.emp_fkey=ed.emp_pkey  and att.user_id= '$user_id' 
//							and date_format(att.created_time,'%Y-%m-%d %T') 
//                                                        BETWEEN DATE_FORMAT('$from_dates','%Y-%m-%d %T')
//                                                        AND DATE_FORMAT('$to_dates','%Y-%m-%d %T')
//                                                        order by created_time ) MO) XX
//                                                        WHERE INVAL = 'in' AND OUTVAL IN ('out' ) order by INTIME");
            $arr_location_updates = $this->MobileUserauditor->query("SELECT EmpName , EMP_PKEY,employee_id, customer_name ,location ,branch,designation,department,purpose,
         INLAt,INLONG , INTIME,INVAL,OUTLAT,OUTLONG,(CASE WHEN INVAL = 'in' AND OUTVAL = 'in' THEN NULL ELSE OUTTIME END) AS OUTTIME,
         OUTVAL FROM (SELECT MO.C1 AS OUTVAL,MO.EMP_PKEY,MO.purpose,MO.employee_id,MO.branch,MO.designation,MO.department,MO.EmpName ,MO.customer_name,
         MO.location,MO.latitude As INLAt,MO.longitude AS INLONG,MO.created_time AS OUTTIME,@S AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)',
	 @LOGIN := @S AS INTIME,@C AS 'LEAD(c1) over(PARTITION BY emp_pkey ORDER BY logdate)',@INVAL := @C AS INVAL,
         @C2 AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)1' ,@LOGIN1 := @C2 AS OUTLAT,
         @C3 AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)2' ,@LOGIN3 := @C3 AS OUTLONG,
	 (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR  @_SEASON IS NULL THEN @S := created_time  END) TMP_VALUE,
	 (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C := C1 END) TMP_VALUE1,
	 (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C2 := lat1 END) TMP_VALUE2,
	 (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C3 := long1 END) TMP_VALUE3,
	 (@PARTITION_BY_COLUMN := customer_name) PARTITION_COLUMN FROM 
         (SELECT ed.emp_pkey,ed.branch,ed.designation,ed.department,ed.employee_id, ed.EmpName, att.created_time,
         att.purpose,customer_name ,location , lcase(att.stepinout) c1,latitude,longitude,latitude lat1,longitude long1
         FROM (SELECT @PARTITION_BY_COLUMN = NULL, @S := NULL, @C := NULL,@C3 := NULL,@LOGIN3 := NULL, @C2 := NULL,@LOGIN1 := NULL,@LOGIN := NULL, 
         @INVAL := NULL,@lat:=null,@long:=null )vars, mob_user_locations att, employee_info ed,user_credentials uc where att.user_id= uc.user_id
         and uc.emp_fkey=ed.emp_pkey and att.user_id= '$user_id' and date_format(att.created_time,'%Y-%m-%d %T') 
	 BETWEEN DATE_FORMAT('$from_dates','%Y-%m-%d %T') AND DATE_FORMAT('$to_dates','%Y-%m-%d %T') order by created_time ) MO) XX
         WHERE INVAL = 'in' AND OUTVAL IN ('out' ) order by INTIME");

        } else {
            $branch = isset($arr_form_data['Units']['0']) ? $arr_form_data['Units']['0'] : 0;

            $user_group = $this->Session->read('user_group');
            $logged_emp_pkey = $this->Session->read('emp_fkey');
            $feature_id = $this->Session->read('current_feature_id');
            $context = $this->MasterdataManagement->getFeatureAccessContext();

            $hier_emp_condition = '';
            if ($user_group == 1) {
                // Admin: no extra filter
            } else if ($context['has_access']) {
                if ($context['is_hierarchy']) {
                    $hier_emp_condition = " and ed.emp_pkey IN (SELECT emp_fkey FROM emp_proff WHERE attr1 = '$logged_emp_pkey') ";
                } else {
                    $branches = $this->MasterdataManagement->getAllocatedBranches($logged_emp_pkey, $feature_id);
                    $branch_codes = array();
                    if (!empty($branches)) {
                        foreach ($branches as $b) {
                            $branch_codes[] = "'" . $b['b']['branch_code'] . "'";
                        }
                    }
                    if (!empty($branch_codes)) {
                        $hier_emp_condition = " and ed.branch_code in (" . implode(',', $branch_codes) . ") ";
                    }
                }
            } else {
                $hier_emp_condition = " and ed.emp_pkey = '$logged_emp_pkey' ";
            }

            $arr_location = $this->MobileUserauditor->query("SELECT mob_user_locations.location,mob_user_locations.latitude,mob_user_locations.longitude, "
                . "employee_info.EmpName,employee_info.employee_id,employee_info.branch FROM `mob_user_locations` "
                . "left join user_credentials on(user_credentials.user_id =  mob_user_locations.user_id) "
                . "left join employee_info on(employee_info.emp_pkey = user_credentials.emp_fkey) WHERE `branch_code` = '$branch' and created_time between '$from_dates' and '$to_dates' ");

            $emplist = $this->MobileUserauditor->query("SELECT distinct(EMP_PKEY) as emp FROM (SELECT MO.C1 AS OUTVAL,MO.EMP_PKEY,MO.purpose,MO.employee_id,MO.branch,
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
                                                        vars, mob_user_locations att, employee_info ed,user_credentials uc where att.user_id= uc.user_id
                                                        and uc.emp_fkey=ed.emp_pkey  and ed.branch_code= '$branch' $hier_emp_condition
							and date_format(att.created_time,'%Y-%m-%d %T')
                                                        BETWEEN DATE_FORMAT('$from_dates','%Y-%m-%d %T')
                                                        AND DATE_FORMAT('$to_dates','%Y-%m-%d %T')
                                                        order by created_time ) MO) XX
                                                        WHERE INVAL = 'in' AND OUTVAL IN ('out' )");

            foreach ($emplist as $list) {
                $emp = $list['XX']['emp'];
                $get_user_id = $this->MobileUserauditor->query("SELECT user_id from user_credentials where emp_fkey = '$emp' ");
                $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';
                //        $arr_location_updates[] = $this->MobileUserauditor->query("SELECT EmpName , EMP_PKEY,employee_id, customer_name ,location ,branch,designation,department,purpose, INTIME, 
//                                                        INVAL, (CASE WHEN INVAL = 'in' AND OUTVAL = 'in' THEN NULL ELSE OUTTIME END) AS OUTTIME ,
//                                                        OUTVAL FROM (SELECT MO.C1 AS OUTVAL,MO.EMP_PKEY,MO.purpose,MO.employee_id,MO.branch,
//                                                        MO.designation,MO.department,MO.EmpName ,MO.customer_name ,MO.location , MO.created_time AS OUTTIME,
//                                                        @S AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)' ,
//                                                        @LOGIN := @S AS INTIME, @C AS 'LEAD(c1) over(PARTITION BY emp_pkey ORDER BY logdate)',
//                                                        @INVAL := @C AS INVAL,(CASE WHEN @PARTITION_BY_COLUMN = customer_name OR 
//                                                        @_SEASON IS NULL THEN @S := created_time
//                                                        END) TMP_VALUE, (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C := C1
//                                                        END) TMP_VALUE1,(@PARTITION_BY_COLUMN := customer_name) PARTITION_COLUMN 
//                                                        FROM 
//                                                        (SELECT ed.emp_pkey,ed.branch,ed.designation,ed.department,ed.employee_id, ed.EmpName, att.created_time,
//                                                        att.purpose,customer_name ,location , lcase(att.stepinout) c1
//                                                        FROM (SELECT @PARTITION_BY_COLUMN = NULL, @S := NULL, @C := NULL, @LOGIN := NULL, @INVAL := NULL ) 
//                                                        vars, mob_user_locations att, employee_info ed,user_credentials uc where att.user_id= uc.user_id
//                                                        and uc.emp_fkey=ed.emp_pkey  and att.user_id= '$user_id' 
//							and date_format(att.created_time,'%Y-%m-%d %T') 
//                                                        BETWEEN DATE_FORMAT('$from_dates','%Y-%m-%d %T')
//                                                        AND DATE_FORMAT('$to_dates','%Y-%m-%d %T')
//                                                        order by created_time ) MO) XX
//                                                        WHERE INVAL = 'in' AND OUTVAL IN ('out' ) order by INTIME");
                $arr_location_updates[] = $this->MobileUserauditor->query("SELECT EmpName , EMP_PKEY,employee_id, customer_name ,location ,branch,designation,department,purpose,
         INLAt,INLONG , INTIME,INVAL,OUTLAT,OUTLONG,(CASE WHEN INVAL = 'in' AND OUTVAL = 'in' THEN NULL ELSE OUTTIME END) AS OUTTIME,
         OUTVAL FROM (SELECT MO.C1 AS OUTVAL,MO.EMP_PKEY,MO.purpose,MO.employee_id,MO.branch,MO.designation,MO.department,MO.EmpName ,MO.customer_name,
         MO.location,MO.latitude As INLAt,MO.longitude AS INLONG,MO.created_time AS OUTTIME,@S AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)',
	 @LOGIN := @S AS INTIME,@C AS 'LEAD(c1) over(PARTITION BY emp_pkey ORDER BY logdate)',@INVAL := @C AS INVAL,
         @C2 AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)1' ,@LOGIN1 := @C2 AS OUTLAT,
         @C3 AS 'LEAD(logdate) over(PARTITION BY emp_pkey ORDER BY logdate)2' ,@LOGIN3 := @C3 AS OUTLONG,
	 (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR  @_SEASON IS NULL THEN @S := created_time  END) TMP_VALUE,
	 (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C := C1 END) TMP_VALUE1,
	 (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C2 := lat1 END) TMP_VALUE2,
	 (CASE WHEN @PARTITION_BY_COLUMN = customer_name OR @_SEASON IS NULL THEN @C3 := long1 END) TMP_VALUE3,
	 (@PARTITION_BY_COLUMN := customer_name) PARTITION_COLUMN FROM 
         (SELECT ed.emp_pkey,ed.branch,ed.designation,ed.department,ed.employee_id, ed.EmpName, att.created_time,
         att.purpose,customer_name ,location , lcase(att.stepinout) c1,latitude,longitude,latitude lat1,longitude long1
         FROM (SELECT @PARTITION_BY_COLUMN = NULL, @S := NULL, @C := NULL,@C3 := NULL,@LOGIN3 := NULL, @C2 := NULL,@LOGIN1 := NULL,@LOGIN := NULL, 
         @INVAL := NULL,@lat:=null,@long:=null )vars, mob_user_locations att, employee_info ed,user_credentials uc where att.user_id= uc.user_id
         and uc.emp_fkey=ed.emp_pkey and att.user_id= '$user_id' and date_format(att.created_time,'%Y-%m-%d %T') 
	 BETWEEN DATE_FORMAT('$from_dates','%Y-%m-%d %T') AND DATE_FORMAT('$to_dates','%Y-%m-%d %T') order by created_time ) MO) XX
         WHERE INVAL = 'in' AND OUTVAL IN ('out' ) order by INTIME");
            }
        }
        // debug($arr_location_updates);
        $location = array();
        foreach ($arr_location as $key => $val) {

            $location[] = array($val['mob_user_locations']['location'], $val['mob_user_locations']['latitude'], $val['mob_user_locations']['longitude']);
        }
        $this->set("datas", json_encode($location));

        //    $empinfo = $this->MobileUserauditor->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
//        debug($arr_location_updates);
        //  $this->set("empinfo", $empinfo);
        //att.user_id= ed.employee_id and  condition removed by megha on 22/07/19




        $arr_resp = array(
            'data' => array()
        );
        //       $lat2 = $value['mob_user_locations']['latitude'];
//                $lon2  = $value['mob_user_locations']['longitude'];
//                }
//                    $lat1  = $value['mob_user_locations']['latitude'];
//                    $lon1 = $value['mob_user_locations']['longitude'];
//                    $theta = $lon1 - $lon2;
//    
//                                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
//                                    //$dist = acos($dist);
//                                    $dist = acos(min($dist,1));
//                                    $dist = rad2deg($dist);
//                                    $miles = $dist * 60 * 1.1515;
////                                    debug($miles);
//                                    $kmdistance = $miles * 1.609344;
//                                    $kmeter =  round($kmdistance,2);
//                                    $totaldistance = $totaldistance + $kmeter;
        if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
            $i = 1;
            foreach ($arr_location_updates as $key => $att) {
                //   debug($att);
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
                $fullDays = floor($delta_T / (60 * 60 * 24));
                $fullHours = floor(($delta_T - ($fullDays * 60 * 60 * 24)) / (60 * 60)) + ($fullDays * 24);
                //$fullMinutes = floor(($delta_T-($fullDays*60*60*24)-($fullHours*60*60))/60);

                $arr_resp['data'][$i][]/* ['Location'] */ = $fullHours . " Hour " . $minutes . " Min  " . $sec . " Sec";
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['employee_id']) ? $att["XX"]['employee_id'] : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['EmpName']) ? $att["XX"]['EmpName'] : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['branch']) ? $att["XX"]['branch'] : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['designation']) ? $att["XX"]['designation'] : '';
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['department']) ? $att["XX"]['department'] : '';
                //calculating distance travelled
                $latitudeFrom = isset($att["XX"]['INLAt']) ? $att["XX"]['INLAt'] : '';
                $longitudeFrom = isset($att["XX"]['INLONG']) ? $att["XX"]['INLONG'] : '';

                $latitudeTo = isset($att["XX"]['OUTLAT']) ? $att["XX"]['OUTLAT'] : '';
                $longitudeTo = isset($att["XX"]['OUTLONG']) ? $att["XX"]['OUTLONG'] : '';

                //Calculate distance from latitude and longitude
                $theta = $longitudeFrom - $longitudeTo;
                $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) + cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                if ($latitudeFrom == $latitudeTo && $longitudeTo == $longitudeFrom) {
                    $distance = '0 km';
                } else {
                    $distance = round(($miles * 1.609344), 2) . ' km';
                }
                $arr_resp['data'][$i][]/* ['C1'] */ = isset($distance) ? $distance : '';
                $i++;
            }
        } else {
            $i = 1;
            foreach ($arr_location_updates as $key => $att1) {
                foreach ($att1 as $key => $att) {
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
                    $fullDays = floor($delta_T / (60 * 60 * 24));
                    $fullHours = floor(($delta_T - ($fullDays * 60 * 60 * 24)) / (60 * 60)) + ($fullDays * 24);
                    //$fullMinutes = floor(($delta_T-($fullDays*60*60*24)-($fullHours*60*60))/60);

                    $arr_resp['data'][$i][]/* ['Location'] */ = $fullHours . " Hour " . $minutes . " Min  " . $sec . " Sec";
                    $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['employee_id']) ? $att["XX"]['employee_id'] : '';
                    $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['EmpName']) ? $att["XX"]['EmpName'] : '';
                    $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['branch']) ? $att["XX"]['branch'] : '';
                    $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['designation']) ? $att["XX"]['designation'] : '';
                    $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['department']) ? $att["XX"]['department'] : '';
                    //calculating distance travelled
                    $latitudeFrom = isset($att["XX"]['INLAt']) ? $att["XX"]['INLAt'] : '';
                    $longitudeFrom = isset($att["XX"]['INLONG']) ? $att["XX"]['INLONG'] : '';

                    $latitudeTo = isset($att["XX"]['OUTLAT']) ? $att["XX"]['OUTLAT'] : '';
                    $longitudeTo = isset($att["XX"]['OUTLONG']) ? $att["XX"]['OUTLONG'] : '';

                    //Calculate distance from latitude and longitude
                    $theta = $longitudeFrom - $longitudeTo;
                    $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) + cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
                    $dist = acos($dist);
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;

                    $distance = round(($miles * 1.609344), 2) . ' km';
                    if ($miles == 'NAN') {
                        $distance = 0;
                    }
                    $arr_resp['data'][$i][]/* ['C1'] */ = isset($distance) ? $distance : '';
                    $i++;
                }
            }
        }

        $this->set('arr_leavepolicydetails_for_template', $arr_resp);
        //         $this->set("arr_location", $arr_location_updates);



        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');

        //Parsing the months
        $spli_month = explode(" - ", $month);
        $start_month = $month;
        //        isset($spli_month[0])?date('Y-m-d',  strtotime($spli_month[0])):date('Y-m-d');
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-1');
        //        isset($spli_month[1])?date('Y-m-d',  strtotime($spli_month[1])):date('Y-m-d');
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
        //        $fd = $start_month . ' ' . '00:00:00';
//        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
//            $report_month = $arr_form_data['reportfrom'];
//            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
//            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
//        }
//        $report_month = $arr_form_data['reportfrom']." - ".$todate;
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];


        $arr_leavepolicydetails_for_template = array();
        //if (isset($arr_location_updates) && !empty($arr_location_updates)) {
        $this->set("criteria", $arr_form_data['hidden-criteria1']);
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set("report_month", $report_month);
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':

                //                     echo "hlo";
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('Dashboard');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A4', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('CustomerVisitsReport.pdf', 'D');
                //                $this->render('reportsalary');
                // $this->render('reportsummary');                
                break;
            case 'excel':

                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "CustomerVisitsReport.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Customer Visits Reports - $report_month");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'G'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:K1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                //                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                );
//                $worksheet->mergeCells('A2:B2');
                $rowcount = 2;
                $columncount = 0;
                $i = 0;
                for ($col = 'A'; $col !== 'K'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                // $empname =  isset($empinfo['0']['employee_info']['EmpName']) ? $empinfo['0']['employee_info']['EmpName'] : '';
                //  $empid =  isset($empinfo['0']['employee_info']['employee_id']) ? $empinfo['0']['employee_info']['employee_id'] : '';
//                  $desig = isset($empinfo['0']['employee_info']['designation']) ? $empinfo['0']['employee_info']['designation'] : '';
//                  $dep = isset($empinfo['0']['employee_info']['department']) ? $empinfo['0']['employee_info']['department'] : '';
//                  $branch = isset($empinfo['0']['employee_info']['branch']) ? $empinfo['0']['employee_info']['branch'] : '';
//                $join = isset($empinfo['0']['employee_info']['joining_date']) ? $empinfo['0']['employee_info']['joining_date'] : '';

                //                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee Name : ");
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount, $empname);
////                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);
//                        
//                         $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount, "Employee ID : ");
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount, $empid);
////                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount)->getFont()->setBold(true);
//                       //2nd row 
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount+1, 'Designation :');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount+1)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount+1, $desig);
////                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+1)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount+1, "Department :");
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount+1)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount+1, $dep);
////                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+1)->getFont()->setBold(true);
//                        //3rd row
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount+2, 'Branch :');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount+2)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+1, $rowcount+2, $branch);
////                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+2)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+2, $rowcount+2, 'Joining Date :');
//                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+2, $rowcount+2)->getFont()->setBold(true);
//                        
//                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount+3, $rowcount+2, $join);
                // $rowcount = 3;


                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Customer Name');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Employee Name');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Branch ');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Purpose');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Location');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Check-In');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), 'Check-Out');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), 'Duration');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), 'Distance');
                //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Location');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Customer');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Purpose');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'IN/OUT');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                for ($i = 0; $i <= 12; $i++) {
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                }


                // $rowcount++;
                // debug($arr_data);die();
                // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);

                //  $rowcount1 = $rowcount + 1;
                if (count($arr_location_updates) > 0) {
                    $k = 1;
                    $data = $arr_resp['data'];
                    foreach ($data as $val) {
                        $columnindex = 0;
                        $rowcount++;
                        //                            $employee_id = $val['employee_info']['employee_id'];
//                            $name = $val['employee_info']['EmpName'];
//                            $department = $val['employee_info']['department'];
//                            $branch = $val['employee_info']['branch'];
//                            $dates = $val['mob_user_tracking']['created_time'];
                        $location = $val[3];
                        $customer = $val[1];
                        $purpose = $val[2];
                        $in = $val[4];
                        $out = $val[5];
                        $duration = $val[6];
                        $id = $val[7];
                        $name = $val[8];
                        $branch = $val[9];
                        $distance = isset($val[12]) ? $val[12] : 0;
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $customer);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $id);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $name);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $branch);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $purpose);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $location);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $in);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 8) . $rowcount, $out);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 9) . $rowcount, $duration);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 10) . $rowcount, $distance);
                        $k++;
                    }
                } else {
                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'There is no data under this criteria');
                    $worksheet->mergeCells('A' . $rowcount . ':K' . $rowcount);
                    $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );
                    $rowcount++;
                }
                $objPHPExcel->getActiveSheet()->setTitle('Customer Visit Report');
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
                $this->render('Dashboard');
                break;
        }
        //        } else {
//            echo "<div style='color:red'><h3>No record Found</h3></div>";
//            // $this->layout=null;
//        }
    }

    private function callDistanceAPI($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // ⏱️ max 3 sec
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return false; // API failed
        }
        curl_close($ch);
        return json_decode($response, true);
    }
    private function generateCustomer_google($mode)
    {
        $arr_form_data = $_REQUEST;
        //  debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->CentralUserCredentials->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');

        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-t');
        $start_month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] . ' ' . '00:00:00' : date('Y-m-1');
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $report_month = $arr_form_data['reportfrom'] . " - " . $arr_form_data['reportto'];
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
        $condition = 'and  emp_details.status = 1';
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and  emp_details.status in('1','2')";
        }
        $arr_leavepolicydetails_for_template = array();
        $arr_DetaildAttendance = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            // $arr_sitemonth_proc = $this->DeviceAttendance->query("SELECT `site_time_duration_check`('$fd', '', '')");
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $this->DeviceAttendance->query("SELECT `customer_visit_history_fn`('1')");
                ini_set('memory_limit', '-1');
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("select cus_visit_locations_pkey,start_latitude,start_longitude,stepin_latitude,stepin_longitude,stepout_latitude,stepout_longitude,"
                        . "start_time,start_location,stepin_time,stepin_location,stepout_time,contact_person,contact_number,stepout_location,purpose,customer_name1,employee_info.employee_id,"
                        . "employee_info.EmpName,employee_info.branch,"
                        . "duration1, start_stepin_distance, duration2, stepin_stepout_distance, total_duration, total_distance "
                        . " from cus_visit_locations "
                        . "left join employee_info on (cus_visit_locations.emp_fkey = employee_info.emp_pkey) "
                        . "where employee_info.emp_pkey = '$leavepolicygroupid' and stepin_time "
                        . "BETWEEN '$start_month' AND '$end_month' group by stepin_time");
                } else {
                    $user_group = $this->Session->read('user_group');
                    $logged_emp_pkey = $this->Session->read('emp_fkey');
                    $feature_id = $this->Session->read('current_feature_id');
                    $context = $this->MasterdataManagement->getFeatureAccessContext();

                    $hier_join = '';
                    $hier_condition = '';
                    if ($user_group == 1) {
                        // Admin: no extra filter
                    } else if ($context['has_access']) {
                        if ($context['is_hierarchy']) {
                            $hier_join = " left join emp_proff on (emp_proff.emp_fkey = employee_info.emp_pkey) ";
                            $hier_condition = " and emp_proff.attr1 = '$logged_emp_pkey' ";
                        } else {
                            $branches = $this->MasterdataManagement->getAllocatedBranches($logged_emp_pkey, $feature_id);
                            $branch_codes = array();
                            if (!empty($branches)) {
                                foreach ($branches as $b) {
                                    $branch_codes[] = "'" . $b['b']['branch_code'] . "'";
                                }
                            }
                            if (!empty($branch_codes)) {
                                $hier_condition = " and employee_info.branch_code in (" . implode(',', $branch_codes) . ") ";
                            }
                        }
                    } else {
                        $hier_condition = " and employee_info.emp_pkey = '$logged_emp_pkey' ";
                    }

                    $arr_leavepolicy_details = $this->DeviceAttendance->query("select cus_visit_locations_pkey,start_latitude,start_longitude,stepin_latitude,stepin_longitude,stepout_latitude,stepout_longitude,"
                        . "start_time,start_location,stepin_time,stepin_location,stepout_time,contact_person,contact_number,stepout_location,purpose,customer_name1,employee_info.employee_id,"
                        . "employee_info.EmpName,employee_info.branch,"
                        . "duration1, start_stepin_distance, duration2, stepin_stepout_distance, total_duration, total_distance "
                        . " from cus_visit_locations "
                        . "left join employee_info on (cus_visit_locations.emp_fkey = employee_info.emp_pkey) "
                        . $hier_join
                        . "where branch_code = '$leavepolicygroupid' $hier_condition and stepin_time "
                        . "BETWEEN '$start_month' AND '$end_month' group by stepin_time");
                }

                if (!empty($arr_leavepolicy_details)) {
                    $count_of_api_call = 0;
                    // This is to check the balance api call in the control db by Arul on 29-9-22
                    $this->CentralUserCredentials->setDataSource('controldb');
                    $arr_company_db = $this->CentralUserCredentials->query("SELECT balance_count FROM customervisit WHERE date_format(report_date,'%Y-%m') = '" . date("Y-m") . "' ORDER BY id DESC LIMIT 1;");
                    $balance_api_call = ($arr_company_db && isset($arr_company_db[0]['customervisit']['balance_count'])) ? $arr_company_db[0]['customervisit']['balance_count'] : '8000';
                    //  debug($arr_company_db);
                    foreach ($arr_leavepolicy_details as $key => $value) {

                        if (!$value['cus_visit_locations']['total_duration'] && !$value['cus_visit_locations']['total_distance'] && $balance_api_call > 0) {

                            $count_of_api_call++; // This is the count to check with balance of api call limit.

                            $temp = $value;
                            $StartTime = isset($temp['cus_visit_locations']['start_time']) ? $temp['cus_visit_locations']['start_time'] : '';
                            $StepinTime = isset($temp['cus_visit_locations']['stepin_time']) ? $temp['cus_visit_locations']['stepin_time'] : '';
                            $StepoutTime = isset($temp['cus_visit_locations']['stepout_time']) ? $temp['cus_visit_locations']['stepout_time'] : '';

                            if (isset($StartTime) && !empty($StartTime)) {
                                $t1 = strtotime($StartTime);
                                $t2 = strtotime($StepinTime);
                                $t3 = strtotime($StepoutTime);
                                //duration 1
                                $delta_T = ($t2 - $t1);
                                $minutes = floor(((($delta_T % 604800) % 86400) % 3600) / 60);
                                $sec = round((((($delta_T % 604800) % 86400) % 3600) % 60));
                                $fullDays = floor($delta_T / (60 * 60 * 24));
                                $fullHours = floor(($delta_T - ($fullDays * 60 * 60 * 24)) / (60 * 60)) + ($fullDays * 24);
                                $Duration = $fullHours . " Hour " . $minutes . " Min  " . $sec . " Sec";
                                //duration 2 
                                $delta_T1 = ($t3 - $t2);
                                $minutes1 = floor(((($delta_T1 % 604800) % 86400) % 3600) / 60);
                                $sec1 = round((((($delta_T1 % 604800) % 86400) % 3600) % 60));
                                $fullDays1 = floor($delta_T1 / (60 * 60 * 24));
                                $fullHours1 = floor(($delta_T1 - ($fullDays1 * 60 * 60 * 24)) / (60 * 60)) + ($fullDays1 * 24);
                                $Duration1 = $fullHours1 . " Hour " . $minutes1 . " Min  " . $sec1 . " Sec";
                                //total duration
                                $delta_T2 = ($t3 - $t1);
                                $minutes2 = floor(((($delta_T2 % 604800) % 86400) % 3600) / 60);
                                $sec2 = round((((($delta_T2 % 604800) % 86400) % 3600) % 60));
                                $fullDays2 = floor($delta_T2 / (60 * 60 * 24));
                                $fullHours2 = floor(($delta_T2 - ($fullDays2 * 60 * 60 * 24)) / (60 * 60)) + ($fullDays2 * 24);
                                $TotalDuration = $fullHours2 . " Hour " . $minutes2 . " Min  " . $sec2 . " Sec";
                            } else {
                                $Duration = 0;
                                $t2 = strtotime($StepinTime);
                                $t3 = strtotime($StepoutTime);
                                //duration 2 
                                $delta_T1 = ($t3 - $t2);
                                $minutes1 = floor(((($delta_T1 % 604800) % 86400) % 3600) / 60);
                                $sec1 = round((((($delta_T1 % 604800) % 86400) % 3600) % 60));
                                $fullDays1 = floor($delta_T1 / (60 * 60 * 24));
                                $fullHours1 = floor(($delta_T1 - ($fullDays1 * 60 * 60 * 24)) / (60 * 60)) + ($fullDays1 * 24);
                                $TotalDuration = $Duration1 = $fullHours1 . " Hour " . $minutes1 . " Min  " . $sec1 . " Sec";
                            }

                            ////$StarttoStepinDistance 
//                            $origin = isset($temp['cus_visit_locations']['start_location']) ? $temp['cus_visit_locations']['start_location'] : '';
//                            $dest = isset($temp['cus_visit_locations']['stepin_location']) ? $temp['cus_visit_locations']['stepin_location'] : '';
                            $lat = isset($temp['cus_visit_locations']['start_latitude']) ? $temp['cus_visit_locations']['start_latitude'] : '';
                            $lon = isset($temp['cus_visit_locations']['start_longitude']) ? $temp['cus_visit_locations']['start_longitude'] : '';
                            $lat1 = isset($temp['cus_visit_locations']['stepin_latitude']) ? $temp['cus_visit_locations']['stepin_latitude'] : '';
                            $lon1 = isset($temp['cus_visit_locations']['stepin_longitude']) ? $temp['cus_visit_locations']['stepin_longitude'] : '';

                            if (isset($lat) && !empty($lat) && isset($lat1) && !empty($lat1)) {
                                //                                 $origin = urlencode($origin);
//                                 $dest = urlencode($dest);
                                //if($origin == $dest){
                                if ($lat == $lat1 && $lon == $lon1) {
                                    $distance = 0;
                                } else {
                                    //$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?destinations='.$origin.'&origins='.$dest.'&key=AIzaSyDCRnB84OWMvOU1Yv6vsXSLo9U0mxyx3Lc';
                                    $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?destinations=' . $lat1 . ',' . $lon1 . '&origins=' . $lat . ',' . $lon . '&key=AIzaSyDCRnB84OWMvOU1Yv6vsXSLo9U0mxyx3Lc';
                                    //$response = json_decode(file_get_contents($url), true);
                                    $response = $this->callDistanceAPI($url);
                                    if (!$response || !isset($response['rows'][0]['elements'][0])) {
                                        // ⚠️ FALLBACK → calculate manually
                                        $theta = $lon1 - $lon;
                                        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat)) + cos(deg2rad($lat1)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
                                        $dist = acos($dist);
                                        $dist = rad2deg($dist);
                                        $miles = $dist * 60 * 1.1515;
                                        $distance = round(($miles * 1.609344), 2);
                                    } else {
                                        $distance = isset($response['rows'][0]['elements'][0]['distance']['text']) ? $response['rows'][0]['elements'][0]['distance']['text'] : 0;
                                    }
                                    // $distance = isset($response['rows'][0]['elements']['0']['distance']['text']) ? $response['rows'][0]['elements']['0']['distance']['text'] : 0;
                                    // $status = isset($response['rows'][0]['elements']['0']['status']) ? $response['rows'][0]['elements']['0']['status'] : 0;
                                    // if ($status == 'NOT_FOUND') {
                                    //     //Calculate distance from latitude and longitude
                                    //     $theta = $lon1 - $lon;
                                    //     $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat)) + cos(deg2rad($lat1)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
                                    //     $dist = acos($dist);
                                    //     $dist = rad2deg($dist);
                                    //     $miles = $dist * 60 * 1.1515;
                                    //     $distance = round(($miles * 1.609344), 2);
                                    // }
                                }
                                if ($distance == 'NAN' || empty($distance)) {
                                    $distance = 0;
                                }
                                $StarttoStepinDistance = isset($distance) ? $distance : '';
                            } else {
                                $StarttoStepinDistance = 0;
                            }

                            //StepintoStepoutDistance
//                            $origin1 = isset($temp['cus_visit_locations']['stepin_location']) ? $temp['cus_visit_locations']['stepin_location'] : '';
//                            $dest1 = isset($temp['cus_visit_locations']['stepout_location']) ? $temp['cus_visit_locations']['stepout_location'] : '';
                            $lat3 = isset($temp['cus_visit_locations']['stepin_latitude']) ? $temp['cus_visit_locations']['stepin_latitude'] : '';
                            $lon3 = isset($temp['cus_visit_locations']['stepin_longitude']) ? $temp['cus_visit_locations']['stepin_longitude'] : '';
                            $lat4 = isset($temp['cus_visit_locations']['stepout_latitude']) ? $temp['cus_visit_locations']['stepout_latitude'] : '';
                            $lon4 = isset($temp['cus_visit_locations']['stepout_longitude']) ? $temp['cus_visit_locations']['stepout_longitude'] : '';

                            if (isset($lat3) && !empty($lat3) && isset($lat4) && !empty($lat4)) {

                                //                                 $origin1 = urlencode($origin1);
//                                 $dest1 = urlencode($dest1);
                                //if($origin == $dest){
                                if ($lat3 == $lat4 && $lon3 == $lon4) {
                                    $distance1 = 0;
                                } else {
                                    //$url =   'https://maps.googleapis.com/maps/api/distancematrix/json?destinations='.$origin1.'&origins='.$dest1.'&key=AIzaSyDCRnB84OWMvOU1Yv6vsXSLo9U0mxyx3Lc';
                                    $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?destinations=' . $lat4 . ',' . $lon4 . '&origins=' . $lat3 . ',' . $lon3 . '&key=AIzaSyDCRnB84OWMvOU1Yv6vsXSLo9U0mxyx3Lc';
                                    // $response = json_decode(file_get_contents($url), true);
                                    $response = $this->callDistanceAPI($url);
                                    if (!$response || !isset($response['rows'][0]['elements'][0])) {
                                        // ⚠️ FALLBACK → calculate manually
                                        $theta1 = $lon4 - $lon3;
                                        $dist1 = sin(deg2rad($lat4)) * sin(deg2rad($lat3)) + cos(deg2rad($lat4)) * cos(deg2rad($lat3)) * cos(deg2rad($theta1));
                                        $dist1 = acos($dist1);
                                        $dist1 = rad2deg($dist1);
                                        $miles1 = $dist1 * 60 * 1.1515;
                                        $distance1 = round(($miles1 * 1.609344), 2);
                                    } else {
                                        $distance1 = isset($response['rows'][0]['elements'][0]['distance']['text']) ? $response['rows'][0]['elements'][0]['distance']['text'] : 0;
                                    }
                                    // $distance1 = isset($response['rows'][0]['elements']['0']['distance']['text']) ? $response['rows'][0]['elements']['0']['distance']['text'] : 0;
                                    // $status = isset($response['rows'][0]['elements']['0']['status']) ? $response['rows'][0]['elements']['0']['status'] : 0;
                                    // if ($status == 'NOT_FOUND') {
                                    //     //Calculate distance from latitude and longitude
                                    //     $theta1 = $lon4 - $lon3;
                                    //     $dist1 = sin(deg2rad($lat4)) * sin(deg2rad($lat3)) + cos(deg2rad($lat4)) * cos(deg2rad($lat3)) * cos(deg2rad($theta1));
                                    //     $dist1 = acos($dist1);
                                    //     $dist1 = rad2deg($dist1);
                                    //     $miles1 = $dist1 * 60 * 1.1515;
                                    //     $distance1 = round(($miles1 * 1.609344), 2);
                                    // }

                                }
                                if ($distance1 == 'NAN' || empty($distance1)) {
                                    $distance1 = 0;
                                }
                                $StepintoStepoutDistance = isset($distance1) ? $distance1 : '';
                            } else {
                                $StepintoStepoutDistance = 0;
                            }
                            if (strpos($StarttoStepinDistance, ' m') !== false) {
                                $StarttoStepinDistance = str_replace(" m", "", $StarttoStepinDistance);
                                $StarttoStepinDistance = round(($StarttoStepinDistance / 1000), 2) . ' km';

                            }
                            if (strpos($StepintoStepoutDistance, ' m') !== false) {
                                $StepintoStepoutDistance = str_replace(" m", "", $StepintoStepoutDistance);
                                $StepintoStepoutDistance = round(($StepintoStepoutDistance / 1000), 2) . ' km';
                            }
                            $TotalDistance = round(($StarttoStepinDistance + $StepintoStepoutDistance), 2);

                            $temp['cus_visit_locations']['duration1'] = $Duration;
                            $temp['cus_visit_locations']['start_stepin_distance'] = $StarttoStepinDistance;
                            $temp['cus_visit_locations']['duration2'] = $Duration1;
                            $temp['cus_visit_locations']['stepin_stepout_distance'] = $StepintoStepoutDistance;
                            $temp['cus_visit_locations']['total_duration'] = $TotalDuration;
                            $temp['cus_visit_locations']['total_distance'] = $TotalDistance;

                            $arr_leavepolicy_details[$key] = $temp;

                            // This is to update first distance, second distance and total distance.
                            $this->DeviceAttendance->query("UPDATE cus_visit_locations 
                            SET 
                            duration1 = '" . $Duration . "',
                            start_stepin_distance = '" . $StarttoStepinDistance . "',
                            duration2 = '" . $Duration1 . "',
                            stepin_stepout_distance = '" . $StepintoStepoutDistance . "',
                            total_duration = '" . $TotalDuration . "',
                            total_distance = '" . $TotalDistance . "'
                            WHERE cus_visit_locations_pkey = " . $temp['cus_visit_locations']['cus_visit_locations_pkey'] . "");
                            // This is to update api balance count in control table. by Arul on 29-9-22
                            $pkey = $temp['cus_visit_locations']['cus_visit_locations_pkey'];
                            if ($count_of_api_call && $balance_api_call > 0) {
                                $new_balance = $balance_api_call - $count_of_api_call;
                                if ($new_balance <= 0) {
                                    $new_balance = 0;
                                }
                                $this->CentralUserCredentials->query("INSERT INTO customervisit 
                        VALUES ('','" . $company_code . "'," . $pkey . ",'" . date('Y-m-d') . "'," . $count_of_api_call . "," . $new_balance . ",'" . date('Y-m-d H:i:s') . "')");
                            }
                        }
                    }

                    $arr_leavepolicydetails_for_template[] = array(
                        'leavepolicyname' => isset($leavepolicyname[0]['branches']['branch_name']) ? $leavepolicyname[0]['branches']['branch_name'] : '',
                        'summary' => $arr_leavepolicy_details
                    );
                }
            }

            // debug($arr_leavepolicydetails_for_template); 

            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('report_month', $report_month);
        $this->set('criterias', $arr_form_data['select-criteria1']);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {

            case 'excel':

                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "CustomerVisitsDetailedReport.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Customer Visit Detailed Reports - " . $report_month);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:R1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                //                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                );
//                $worksheet->mergeCells('A2:B2');
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:R2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $rowcount = 3;
                $columncount = 0;
                $i = 0;
                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                $i += 1;

                foreach ($arr_leavepolicydetails_for_template as $value) {

                    $arr_daata = $value['summary'];

                    if (empty($arr_daata))
                        continue;

                    $employees = $value;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Customer Name');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Employee Name');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Branch ');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Purpose');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Start Time');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Start Location');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), 'Step in Time');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), 'Step-in Location');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), 'Duration ');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((11), ($rowcount), 'Start to Step-in Distance(in km)');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((12), ($rowcount), 'Step-out Time');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((13), ($rowcount), 'Step-out location');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((14), ($rowcount), 'Duration ');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((15), ($rowcount), 'Step-in to Step-out Distance(in km)');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((16), ($rowcount), 'Total Duration');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((17), ($rowcount), ' Total Distance(in km)');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((18), ($rowcount), ' Contact Person');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((19), ($rowcount), ' Contact Number');

                    for ($i = 0; $i <= 19; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }

                    $rowcount = $rowcount + 1;
                    $arr_e = $employees['summary'];
                    $i = 1;
                    foreach ($arr_e as $employee => $val) {
                        $customer = $val['cus_visit_locations']['customer_name1'];
                        $employee_id = $val['employee_info']['employee_id'];
                        $name = $val['employee_info']['EmpName'];
                        $branch = $val['employee_info']['branch'];
                        $purpose = $val['cus_visit_locations']['purpose'];
                        $StartTime = isset($val['cus_visit_locations']['start_time']) ? $val['cus_visit_locations']['start_time'] : '';
                        $StartLocation = isset($val['cus_visit_locations']['start_location']) ? $val['cus_visit_locations']['start_location'] : '';
                        $StepinTime = isset($val['cus_visit_locations']['stepin_time']) ? $val['cus_visit_locations']['stepin_time'] : '';
                        $StepinLocation = isset($val['cus_visit_locations']['stepin_location']) ? $val['cus_visit_locations']['stepin_location'] : '';
                        $StepoutTime = isset($val['cus_visit_locations']['stepout_time']) ? $val['cus_visit_locations']['stepout_time'] : '';
                        $stepoutlocation = isset($val['cus_visit_locations']['stepout_location']) ? $val['cus_visit_locations']['stepout_location'] : '';


                        $Duration = isset($val['cus_visit_locations']['duration1']) ? $val['cus_visit_locations']['duration1'] : '';
                        $StarttoStepinDistance = isset($val['cus_visit_locations']['start_stepin_distance']) ? $val['cus_visit_locations']['start_stepin_distance'] : '';
                        $Duration1 = isset($val['cus_visit_locations']['duration2']) ? $val['cus_visit_locations']['duration2'] : '';
                        $StepintoStepoutDistance = isset($val['cus_visit_locations']['stepin_stepout_distance']) ? $val['cus_visit_locations']['stepin_stepout_distance'] : '';
                        $TotalDuration = isset($val['cus_visit_locations']['total_duration']) ? $val['cus_visit_locations']['total_duration'] : '';
                        $TotalDistance = isset($val['cus_visit_locations']['total_distance']) ? $val['cus_visit_locations']['total_distance'] : '';
                        $contactperson = isset($val['cus_visit_locations']['contact_person']) ? $val['cus_visit_locations']['contact_person'] : '';
                        $contactnumber = isset($val['cus_visit_locations']['contact_number']) ? $val['cus_visit_locations']['contact_number'] : '';
                        if (strpos($StarttoStepinDistance, ' km') !== false) {
                            $StarttoStepinDistance = str_replace(" km", "", $StarttoStepinDistance);
                            $StarttoStepinDistance = round($StarttoStepinDistance, 2);
                        }
                        if (strpos($StepintoStepoutDistance, ' km') !== false) {
                            $StepintoStepoutDistance = str_replace(" km", "", $StepintoStepoutDistance);
                            $StepintoStepoutDistance = round($StepintoStepoutDistance, 2);
                        }
                        $TotalDistance = round($TotalDistance, 2);

                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), $i);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), $customer);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . ($rowcount), $employee_id);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . ($rowcount), $name);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . ($rowcount), $branch);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . ($rowcount), $purpose);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . ($rowcount), $StartTime);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . ($rowcount), $StartLocation);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . ($rowcount), $StepinTime);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . ($rowcount), $StepinLocation);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . ($rowcount), $Duration);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . ($rowcount), $StarttoStepinDistance);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . ($rowcount), $StepoutTime);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . ($rowcount), $stepoutlocation);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . ($rowcount), $Duration1);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . ($rowcount), $StepintoStepoutDistance);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . ($rowcount), $TotalDuration);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . ($rowcount), $TotalDistance);
                        //edited by sinisya on 19-07-2024
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . ($rowcount), $contactperson);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . ($rowcount), ' ' . $contactnumber);
                        $rowcount++;
                        $i++;
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );

                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:T' . $row)->applyFromArray($BStyle);

                        foreach (range('A', 'T') as $columnID) {
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                        }
                    }
                }
                $objPHPExcel->getActiveSheet()->setTitle('Customer Visit Detailed Report');
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
                $this->render('customer_google');
                break;
        }
    }


    private function generateCustomer($mode)
    {
        $arr_form_data = $_REQUEST;
        //  debug($arr_form_data);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        //echo date('d-m-Y H:i');
        $date_time = date('d-m-Y H:i');
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m');

        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-t');
        $start_month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] . ' ' . '00:00:00' : date('Y-m-1');
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $report_month = $arr_form_data['reportfrom'] . " - " . $arr_form_data['reportto'];
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
        $condition = 'and  emp_details.status = 1';
        if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {
            $condition = "and  emp_details.status in('1','2')";
        }
        $arr_leavepolicydetails_for_template = array();
        $arr_DetaildAttendance = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            // $arr_sitemonth_proc = $this->DeviceAttendance->query("SELECT `site_time_duration_check`('$fd', '', '')");
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {
                $this->DeviceAttendance->query("SELECT `customer_visit_history_fn`('1')");
                ini_set('memory_limit', '-1');
                if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                    $arr_leavepolicy_details = $this->DeviceAttendance->query("select start_latitude,start_longitude,stepin_latitude,stepin_longitude,stepout_latitude,stepout_longitude,"
                        . "start_time,start_location,stepin_time,stepin_location,stepout_time,stepout_location,purpose,customer_name1,contact_person,contact_number,employee_info.employee_id,"
                        . "employee_info.EmpName,employee_info.branch from cus_visit_locations "
                        . "left join employee_info on (cus_visit_locations.emp_fkey = employee_info.emp_pkey) "
                        . "where employee_info.emp_pkey = '$leavepolicygroupid' and stepin_time "
                        . "BETWEEN '$start_month' AND '$end_month' group by stepin_time");
                } else {
                    $user_group = $this->Session->read('user_group');
                    $logged_emp_pkey = $this->Session->read('emp_fkey');
                    $feature_id = $this->Session->read('current_feature_id');
                    $context = $this->MasterdataManagement->getFeatureAccessContext();

                    $hier_join = '';
                    $hier_condition = '';
                    if ($user_group == 1) {
                        // Admin: no extra filter
                    } else if ($context['has_access']) {
                        if ($context['is_hierarchy']) {
                            $hier_join = " left join emp_proff on (emp_proff.emp_fkey = employee_info.emp_pkey) ";
                            $hier_condition = " and emp_proff.attr1 = '$logged_emp_pkey' ";
                        } else {
                            $branches = $this->MasterdataManagement->getAllocatedBranches($logged_emp_pkey, $feature_id);
                            $branch_codes = array();
                            if (!empty($branches)) {
                                foreach ($branches as $b) {
                                    $branch_codes[] = "'" . $b['b']['branch_code'] . "'";
                                }
                            }
                            if (!empty($branch_codes)) {
                                $hier_condition = " and employee_info.branch_code in (" . implode(',', $branch_codes) . ") ";
                            }
                        }
                    } else {
                        $hier_condition = " and employee_info.emp_pkey = '$logged_emp_pkey' ";
                    }

                    $arr_leavepolicy_details = $this->DeviceAttendance->query("select start_latitude,start_longitude,stepin_latitude,stepin_longitude,stepout_latitude,stepout_longitude,"
                        . "start_time,start_location,stepin_time,stepin_location,stepout_time,stepout_location,purpose,customer_name1,contact_person,contact_number,employee_info.employee_id,"
                        . "employee_info.EmpName,employee_info.branch from cus_visit_locations "
                        . "left join employee_info on (cus_visit_locations.emp_fkey = employee_info.emp_pkey) "
                        . $hier_join
                        . "where branch_code = '$leavepolicygroupid' $hier_condition and stepin_time "
                        . "BETWEEN '$start_month' AND '$end_month' group by stepin_time");
                }

                if (!empty($arr_leavepolicy_details)) {
                    $arr_leavepolicydetails_for_template[] = array(
                        'leavepolicyname' => isset($leavepolicyname[0]['branches']['branch_name']) ? $leavepolicyname[0]['branches']['branch_name'] : '',
                        'summary' => $arr_leavepolicy_details
                    );
                }
            }

            // debug($arr_leavepolicydetails_for_template); 

            $this->set('arr_leavepolicydetails_for_template', $arr_leavepolicydetails_for_template);
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            die();
        }
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $user_name = $this->Session->read('user_name');
        $this->set('user_name', $user_name);
        $this->set('report_month', $report_month);
        $this->set('criterias', $arr_form_data['select-criteria1']);
        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
        $this->set('arr_comp_contact_info', $arr_comp_contact_info);
        switch ($mode) {
            case 'pdf':
                $this->set('mode', 'pdf');
                $view = new View($this, false);
                $view_output = $view->render('customer');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                $html2pdf = new HTML2PDF('L', 'A3', 'en');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($view_output);
                $html2pdf->Output('customer.pdf', 'D');
                //$this->render('reportshiftpolicy');                
                break;
            case 'excel':

                $str_company_code = $this->Session->read('company_code');
                $file_name = isset($str_company_code) ? $str_company_code . "CustomerVisitsDetailedReport.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                $objPHPExcel = new PHPExcel();

                $objPHPExcel->getProperties()->setCreator("Administrator");
                $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                $objPHPExcel->setActiveSheetIndex(0);

                $worksheet = $objPHPExcel->getActiveSheet();

                $worksheet->setCellValueByColumnAndRow(0, 1, "Customer Visit Detailed Reports - " . $report_month);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }
                $worksheet->mergeCells('A1:R1');
                $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                //                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                );
//                $worksheet->mergeCells('A2:B2');
                $worksheet->setCellValueByColumnAndRow(0, 2, "(Report Run by " . $user_id . " at " . $date_time . ")");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()->setSize(13);
                $worksheet->mergeCells('A2:R2');
                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
                    array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                );
                $rowcount = 3;
                $columncount = 0;
                $i = 0;
                for ($col = 'A'; $col !== 'R'; $col++) {
                    $objPHPExcel->getActiveSheet()
                        ->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                $i += 1;

                foreach ($arr_leavepolicydetails_for_template as $value) {
                    //                   $i += 1;     
                    $arr_daata = $value['summary'];

                    if (empty($arr_daata))
                        continue;

                    $employees = $value;
                    // $worksheet->mergeCells('A'.$rowcount.':R'.$rowcount);
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Customer Visits Detailed Report of '.$employees['leavepolicyname'] );
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, ($rowcount))->getFont()->setBold(true);
                    // $worksheet->getStyle('A'.$rowcount)->getAlignment()->applyFromArray(
                    //     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                    // );
                    // $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('86bfe0');
                    // $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    // $rowcount = $rowcount + 2;

                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Customer Name');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Employee ID');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Employee Name');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Branch ');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Purpose');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((6), ($rowcount), 'Start Time');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((7), ($rowcount), 'Start Location');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((8), ($rowcount), 'Step in Time');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((9), ($rowcount), 'Step-in Location');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((10), ($rowcount), 'Duration ');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((11), ($rowcount), 'Start to Step-in Distance');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((12), ($rowcount), 'Step-out Time');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((13), ($rowcount), 'Step-out location');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((14), ($rowcount), 'Duration ');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((15), ($rowcount), 'Step-in to Step-out Distance');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((16), ($rowcount), 'Total Duration');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((17), ($rowcount), ' Total Distance');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((18), ($rowcount), ' Contact Person');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((19), ($rowcount), ' Contact Number');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(19))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    for ($i = 0; $i <= 19; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }


                    // $rowcount++;
                    // debug($arr_data);die();
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);

                    //  $rowcount1 = $rowcount + 1;
                    $rowcount = $rowcount + 1;
                    $arr_e = $employees['summary'];
                    $i = 1;
                    foreach ($arr_e as $employee => $val) {
                        $customer = $val['cus_visit_locations']['customer_name1'];
                        $employee_id = $val['employee_info']['employee_id'];
                        $name = $val['employee_info']['EmpName'];
                        $branch = $val['employee_info']['branch'];
                        $purpose = $val['cus_visit_locations']['purpose'];
                        $StartTime = isset($val['cus_visit_locations']['start_time']) ? $val['cus_visit_locations']['start_time'] : '';
                        $StartLocation = isset($val['cus_visit_locations']['start_location']) ? $val['cus_visit_locations']['start_location'] : '';
                        $StepinTime = isset($val['cus_visit_locations']['stepin_time']) ? $val['cus_visit_locations']['stepin_time'] : '';
                        $StepinLocation = isset($val['cus_visit_locations']['stepin_location']) ? $val['cus_visit_locations']['stepin_location'] : '';
                        $StepoutTime = isset($val['cus_visit_locations']['stepout_time']) ? $val['cus_visit_locations']['stepout_time'] : '';
                        $stepoutlocation = isset($val['cus_visit_locations']['stepout_location']) ? $val['cus_visit_locations']['stepout_location'] : '';
                        $contactperson = isset($val['cus_visit_locations']['contact_person']) ? $val['cus_visit_locations']['contact_person'] : '';
                        $contactnumber = isset($val['cus_visit_locations']['contact_number']) ? $val['cus_visit_locations']['contact_number'] : '';
                        if (isset($StartTime) && !empty($StartTime)) {
                            $t1 = strtotime($StartTime);
                            $t2 = strtotime($StepinTime);
                            $t3 = strtotime($StepoutTime);
                            //duration 1
                            $delta_T = ($t2 - $t1);
                            $minutes = floor(((($delta_T % 604800) % 86400) % 3600) / 60);
                            $sec = round((((($delta_T % 604800) % 86400) % 3600) % 60));
                            $fullDays = floor($delta_T / (60 * 60 * 24));
                            $fullHours = floor(($delta_T - ($fullDays * 60 * 60 * 24)) / (60 * 60)) + ($fullDays * 24);
                            $Duration = $fullHours . " Hour " . $minutes . " Min  " . $sec . " Sec";
                            //duration 2 
                            $delta_T1 = ($t3 - $t2);
                            $minutes1 = floor(((($delta_T1 % 604800) % 86400) % 3600) / 60);
                            $sec1 = round((((($delta_T1 % 604800) % 86400) % 3600) % 60));
                            $fullDays1 = floor($delta_T1 / (60 * 60 * 24));
                            $fullHours1 = floor(($delta_T1 - ($fullDays1 * 60 * 60 * 24)) / (60 * 60)) + ($fullDays1 * 24);
                            $Duration1 = $fullHours1 . " Hour " . $minutes1 . " Min  " . $sec1 . " Sec";
                            //total duration
                            $delta_T2 = ($t3 - $t1);
                            $minutes2 = floor(((($delta_T2 % 604800) % 86400) % 3600) / 60);
                            $sec2 = round((((($delta_T2 % 604800) % 86400) % 3600) % 60));
                            $fullDays2 = floor($delta_T2 / (60 * 60 * 24));
                            $fullHours2 = floor(($delta_T2 - ($fullDays2 * 60 * 60 * 24)) / (60 * 60)) + ($fullDays2 * 24);
                            $TotalDuration = $fullHours2 . " Hour " . $minutes2 . " Min  " . $sec2 . " Sec";
                        } else {
                            $Duration = 0;
                            $t2 = strtotime($StepinTime);
                            $t3 = strtotime($StepoutTime);
                            //duration 2 
                            $delta_T1 = ($t3 - $t2);
                            $minutes1 = floor(((($delta_T1 % 604800) % 86400) % 3600) / 60);
                            $sec1 = round((((($delta_T1 % 604800) % 86400) % 3600) % 60));
                            $fullDays1 = floor($delta_T1 / (60 * 60 * 24));
                            $fullHours1 = floor(($delta_T1 - ($fullDays1 * 60 * 60 * 24)) / (60 * 60)) + ($fullDays1 * 24);
                            $TotalDuration = $Duration1 = $fullHours1 . " Hour " . $minutes1 . " Min  " . $sec1 . " Sec";

                        }

                        ////$StarttoStepinDistance 
                        if (isset($StartTime) && !empty($StartTime)) {
                            $lat = isset($val['cus_visit_locations']['start_latitude']) ? $val['cus_visit_locations']['start_latitude'] : '';
                            $lon = isset($val['cus_visit_locations']['start_longitude']) ? $val['cus_visit_locations']['start_longitude'] : '';
                            $lat1 = isset($val['cus_visit_locations']['stepin_latitude']) ? $val['cus_visit_locations']['stepin_latitude'] : '';
                            $lon1 = isset($val['cus_visit_locations']['stepin_longitude']) ? $val['cus_visit_locations']['stepin_longitude'] : '';
                            //Calculate distance from latitude and longitude
                            $theta = $lon1 - $lon;
                            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat)) + cos(deg2rad($lat1)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
                            $dist = acos($dist);
                            $dist = rad2deg($dist);
                            $miles = $dist * 60 * 1.1515;
                            $distance = round(($miles * 1.609344), 2);
                            if ($miles == 'NAN') {
                                $distance = 0;
                            }
                            $StarttoStepinDistance = isset($distance) ? $distance : '';
                        } else {
                            $StarttoStepinDistance = 0;
                        }

                        //StepintoStepoutDistance
                        if (isset($StepinTime) && !empty($StepinTime)) {
                            $lat3 = isset($val['cus_visit_locations']['stepin_latitude']) ? $val['cus_visit_locations']['stepin_latitude'] : '';
                            $lon3 = isset($val['cus_visit_locations']['stepin_longitude']) ? $val['cus_visit_locations']['stepin_longitude'] : '';
                            $lat4 = isset($val['cus_visit_locations']['stepout_latitude']) ? $val['cus_visit_locations']['stepout_latitude'] : '';
                            $lon4 = isset($val['cus_visit_locations']['stepout_longitude']) ? $val['cus_visit_locations']['stepout_longitude'] : '';
                            //Calculate distance from latitude and longitude
                            $theta1 = $lon4 - $lon3;
                            $dist1 = sin(deg2rad($lat4)) * sin(deg2rad($lat3)) + cos(deg2rad($lat4)) * cos(deg2rad($lat3)) * cos(deg2rad($theta1));
                            $dist1 = acos($dist1);
                            $dist1 = rad2deg($dist1);
                            $miles1 = $dist1 * 60 * 1.1515;
                            $distance1 = round(($miles1 * 1.609344), 2);
                            if ($miles1 == 'NAN') {
                                $distance1 = 0;
                            }
                            $StepintoStepoutDistance = isset($distance1) ? $distance1 : '';
                        } else {
                            $StepintoStepoutDistance = 0;
                        }

                        $TotalDistance = ($StarttoStepinDistance + $StepintoStepoutDistance);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . ($rowcount), $i);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . ($rowcount), $customer);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . ($rowcount), $employee_id);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . ($rowcount), $name);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . ($rowcount), $branch);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . ($rowcount), $purpose);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . ($rowcount), $StartTime);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . ($rowcount), $StartLocation);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . ($rowcount), $StepinTime);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . ($rowcount), $StepinLocation);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . ($rowcount), $Duration);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . ($rowcount), $StarttoStepinDistance . ' km');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . ($rowcount), $StepoutTime);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . ($rowcount), $stepoutlocation);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . ($rowcount), $Duration1);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . ($rowcount), $StepintoStepoutDistance . ' km');
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(16) . ($rowcount), $TotalDuration);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(17) . ($rowcount), $TotalDistance . ' km');
                        //edited by sinisya on 19-07-2024
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(18) . ($rowcount), $contactperson);
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(19) . ($rowcount), ' ' . $contactnumber);

                        $rowcount++;
                        $i++;
                        $BStyle = array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        );

                        $row = $rowcount - 1;
                        $objPHPExcel->getActiveSheet()->getStyle('A1:T' . $row)->applyFromArray($BStyle);

                        foreach (range('A', 'T') as $columnID) {
                            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                        }

                        // }
                        // else{
                        // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'There is no data under this criteria');
                        // $worksheet->mergeCells('A' . $rowcount . ':R' . $rowcount);
                        //             $worksheet->getStyle('A' . $rowcount)->getAlignment()->applyFromArray(
                        //                     array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
                        //             );
                        //     $rowcount++;
                        // }
                    }
                }
                $objPHPExcel->getActiveSheet()->setTitle('Customer Visit Detailed Report');
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
                $this->render('customer');
                break;
        }
        //        } else {
//            echo "<div style='color:red'><h3>No record Found</h3></div>";
//            // $this->layout=null;
//        }
    }
    private function generateAttendanceLocation($mode)
    {
        $arr_form_data = $_REQUEST;
        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');
        $emp_fkey = isset($arr_form_data['EmployeeDetails']['0']) ? $arr_form_data['EmployeeDetails']['0'] : 0;
        //Parsing the months
        $spli_month = explode(" - ", $month);
        $start_month = $month;
        //        isset($spli_month[0])?date('Y-m-d',  strtotime($spli_month[0])):date('Y-m-d');
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-1');
        //        isset($spli_month[1])?date('Y-m-d',  strtotime($spli_month[1])):date('Y-m-d');
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
        $fd = $start_month . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $report_month = $arr_form_data['reportfrom'] . " - " . $arr_form_data['reportto'];
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }


        $this->AttendanceRegister->useDbConfig = $this->Session->read('ds');

        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {
            foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {



            }



            $today_cond = array(
                // "LOGDATE BETWEEN '$fd' and '$end_month' ",
                "LOGDATE >=" => '$fd',
                "LOGDATE <=" => '$end_month',
                "DeviceAttendance.status >=" => "Y"
            );

            $user_group = $this->Session->read('user_group');
            $logged_emp_pkey = $this->Session->read('emp_fkey');
            $feature_id = $this->Session->read('current_feature_id');
            $context = $this->MasterdataManagement->getFeatureAccessContext();
            $add_hier_join = false;

            if ($arr_form_data['select-criteria1'] == 'EmployeeDetails') {
                $today_cond[] = array("EmployeeDetails.emp_pkey" => $arr_leavepolicygroupids);
            } else {
                $today_cond[] = array("EmployeeDetails.branch_code" => $arr_leavepolicygroupids);
                if ($user_group == 1) {
                    // Admin: no extra filter
                } else if ($context['has_access']) {
                    if ($context['is_hierarchy']) {
                        $add_hier_join = true;
                        $today_cond[] = array("EmpProff.attr1" => $logged_emp_pkey);
                    } else {
                        $branches = $this->MasterdataManagement->getAllocatedBranches($logged_emp_pkey, $feature_id);
                        $branch_codes = array();
                        if (!empty($branches)) {
                            foreach ($branches as $b) {
                                $branch_codes[] = $b['b']['branch_code'];
                            }
                        }
                        $today_cond[] = array("EmployeeDetails.branch_code" => $branch_codes);
                    }
                } else {
                    $today_cond[] = array("EmployeeDetails.emp_pkey" => $logged_emp_pkey);
                }
            }

            $today_join[] = array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'INNER',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
            );

            $today_join[] = array(
                'table' => 'branches',
                'alias' => 'Branch',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('DeviceAttendance.branch_code = Branch.branch_code')
            );

            if ($add_hier_join) {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmpProff',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmpProff.emp_fkey = EmployeeDetails.emp_pkey')
                );
            }

            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $today_att = $this->DeviceAttendance->find(
                "all",
                array(
                    "order" => "DeviceAttendance.LOGDATE ASC",
                    "conditions" => $today_cond,
                    'joins' => $today_join,
                    "fields" => "Branch.branch_name,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
                if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location"
                )
            );

            $fromdate = $arr_form_data['reportfrom'];
            $todate = $arr_form_data['reportto'];
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
                $data[] = $this->DeviceAttendance->query("SELECT `Branch`.`branch_name`,`emp_detail_timeattandance`.`duration`, concat(EmployeeDetails.first_name,' ',
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
            foreach ($data as $key => $val) {
                //edited by amal tracking report changes on 23/08/2019 1
                $j = 1;
                foreach ($val as $att) {
                    //edited by amal tracking report changes on 23/08/2019 2
                    //$arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
                    $arr_resp['data'][$i][] = $j;
                    //                $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';

                    $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("Y-m-d", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
                    $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
                    $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
                    $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
                    $arr_resp['data'][$i][]/* ['Location'] */ = isset($att['emp_detail_timeattandance']['duration']) ? $att['emp_detail_timeattandance']['duration'] : '';
                    //edited by amal tracking report changes on 23/08/2019 1
                    $j++;
                    $i++;
                }
            }
            //            debug($arr_resp);
            $this->set('arr_leavepolicydetails_for_template', $arr_resp);
            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            $empinfo = $this->DeviceAttendance->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
            //             $this->set('duration', $duration);    

            //        debug($duration);
            $this->set("empinfo", $empinfo);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('attendance_location');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('MobileTrackingReport.pdf', 'D');


                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "AttendanceLocationReport.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Attendance - Location Report  - $report_month");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                    for ($col = 'A'; $col !== 'G'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:K1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );
                    //                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                );
//                $worksheet->mergeCells('A2:B2');
                    $rowcount = 2;
                    $columncount = 0;
                    $i = 0;
                    $empname = isset($empinfo['0']['employee_info']['EmpName']) ? $empinfo['0']['employee_info']['EmpName'] : '';
                    $empid = isset($empinfo['0']['employee_info']['employee_id']) ? $empinfo['0']['employee_info']['employee_id'] : '';
                    $desig = isset($empinfo['0']['employee_info']['designation']) ? $empinfo['0']['employee_info']['designation'] : '';
                    $dep = isset($empinfo['0']['employee_info']['department']) ? $empinfo['0']['employee_info']['department'] : '';
                    $branch = isset($empinfo['0']['employee_info']['branch']) ? $empinfo['0']['employee_info']['branch'] : '';
                    $join = isset($empinfo['0']['employee_info']['joining_date']) ? $empinfo['0']['employee_info']['joining_date'] : '';

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee Name : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $empname);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, "Employee ID : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount, $empid);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount)->getFont()->setBold(true);
                    //2nd row 
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Designation :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $desig);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 1, "Department :");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 1, $dep);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+1)->getFont()->setBold(true);
                    //3rd row
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Branch :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $branch);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 2, 'Joining Date :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 2, $join);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+2)->getFont()->setBold(true);
////              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Customer Visists Report  '.$empinfo['0']['employee_info']['EmpName']);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                    $rowcount = 6;


                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Date');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Time');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'In/Out');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((4), ($rowcount), 'Location');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Duration');


                    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Location');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Customer');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Purpose');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'IN/OUT');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    for ($i = 0; $i <= 9; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }


                    $rowcount = 6;
                    //                   debug($arr_data);
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);
                    $count = (count($data));
                    $rowcount1 = $rowcount + 1;
                    if (count($data) >= 0) {
                        $k = 1;
                        $data = $arr_resp['data'];
                        foreach ($data as $val) {
                            $columnindex = 0;
                            $rowcount++;
                            //                            $employee_id = $val['employee_info']['employee_id'];
//                            $name = $val['employee_info']['EmpName'];
//                            $department = $val['employee_info']['department'];
//                            $branch = $val['employee_info']['branch'];
//                            $dates = $val['mob_user_tracking']['created_time'];
                            $date = $val[1];

                            $time = $val[2];
                            $direction = $val[3];
                            $location = $val[4];
                            $duration = isset($val[5]) ? $val[5] : '0';
                            if ($val[0] == 2) {
                                if ($date == $val[1]) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((5), ($rowcount), 'Duration');
                                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $duration);
                                }
                            }
                            if ($k != 1) {
                                if ($datee != $val[1]) {
                                    //$objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $duration);
                                }
                            }
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $time);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $direction);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $location);
                            //                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $duration);
                            $datee = $date;

                            $k++;
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setTitle('Attendance - Location Report');
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
                    $this->render('attendance_location');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }
    private function generateMobileTrack($mode)
    {
        $arr_form_data = $_REQUEST;
        //        debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from_dates = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] . ' 00:00' : '';
        $to_dates = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . ' 23:59' : '';
        $emp_fkey = isset($arr_form_data['EmployeeDetails']['0']) ? $arr_form_data['EmployeeDetails']['0'] : 0;
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
        $arr_location = $this->MobileUserauditor->query("SELECT * FROM `mob_user_locations` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $location = array();
        foreach ($arr_location as $key => $val) {

            $location[] = array($val['mob_user_locations']['location'], $val['mob_user_locations']['latitude'], $val['mob_user_locations']['longitude']);
        }
        $this->set("datas", json_encode($location));

        //        debug("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $empinfo = $this->MobileUserTracking->query("select EmpName,employee_id,branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");
        //        debug($arr_location_updates);
        $this->set("empinfo", $empinfo);

        $arr_resp = array(
            'data' => array()
        );


        $i = 1;
        foreach ($arr_location_updates as $key => $att) {
            //                debug($att);
            $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
            $s = $att["mob_user_tracking"]['created_time'];
            $dt = new DateTime($s);
            $date = $dt->format('m/d/Y');
            $time = $dt->format('H:i:s');
            $arr_resp['data'][$i][]/* ['Branch'] */ = $date;
            $arr_resp['data'][$i][]/* ['Branch'] */ = $time;
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["mob_user_tracking"]['location']) ? $att["mob_user_tracking"]['location'] : '';
            //                $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["XX"]['location']) ? $att["XX"]['location'] : '';
//                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['INTIME']) ? $att["XX"]['INTIME'] : '';
//                $arr_resp['data'][$i][]/* ['Location'] */ = isset($att["0"]['OUTTIME']) ? $att["0"]['OUTTIME'] : '';
//                $t1 = strtotime($att['XX']['INTIME']);
//                $t2 = strtotime($att['0']['OUTTIME']);
//                $delta_T = ($t2 - $t1);
//                $minutes = round(((($delta_T % 604800) % 86400) % 3600) / 60); 
//                $sec = round((((($delta_T % 604800) % 86400) % 3600) % 60));
//                $arr_resp['data'][$i][]/* ['Location'] */ =  $minutes." Min  " .$sec ." Sec";
//                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['EmpName']) ? $att["XX"]['EmpName'] : '';
//                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['employee_id']) ? $att["XX"]['employee_id'] : '';
//                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['branch']) ? $att["XX"]['branch'] : '';
//                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['designation']) ? $att["XX"]['designation'] : '';
//                $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["XX"]['department']) ? $att["XX"]['department'] : '';
            $i++;
        }

        $this->set('arr_leavepolicydetails_for_template', $arr_resp);
        //         $this->set("arr_location", $arr_location_updates);



        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');

        //Parsing the months
        $spli_month = explode(" - ", $month);
        $start_month = $month;
        //        isset($spli_month[0])?date('Y-m-d',  strtotime($spli_month[0])):date('Y-m-d');
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-1');
        //        isset($spli_month[1])?date('Y-m-d',  strtotime($spli_month[1])):date('Y-m-d');
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
        $fd = $start_month . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $report_month = $arr_form_data['reportfrom'] . " - " . $arr_form_data['reportto'];
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }


        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {







            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);


                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('MobileTrackingReport.pdf', 'D');
                    $view_output = $view->render('mobile_track');

                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "MobileTrackingReport.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Mobile Tracking Report  - $report_month");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                    for ($col = 'A'; $col !== 'G'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:K1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );
                    //                $worksheet->getStyle('A2')->getAlignment()->applyFromArray(
//                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
//                );
//                $worksheet->mergeCells('A2:B2');
                    $rowcount = 2;
                    $columncount = 0;
                    $i = 0;
                    $empname = isset($empinfo['0']['employee_info']['EmpName']) ? $empinfo['0']['employee_info']['EmpName'] : '';
                    $empid = isset($empinfo['0']['employee_info']['employee_id']) ? $empinfo['0']['employee_info']['employee_id'] : '';
                    $desig = isset($empinfo['0']['employee_info']['designation']) ? $empinfo['0']['employee_info']['designation'] : '';
                    $dep = isset($empinfo['0']['employee_info']['department']) ? $empinfo['0']['employee_info']['department'] : '';
                    $branch = isset($empinfo['0']['employee_info']['branch']) ? $empinfo['0']['employee_info']['branch'] : '';
                    $join = isset($empinfo['0']['employee_info']['joining_date']) ? $empinfo['0']['employee_info']['joining_date'] : '';

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee Name : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $empname);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, "Employee ID : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount, $empid);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount)->getFont()->setBold(true);
                    //2nd row 
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Designation :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $desig);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 1, "Department :");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 1, $dep);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+1)->getFont()->setBold(true);
                    //3rd row
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Branch :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $branch);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 2, 'Joining Date :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 2, $join);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+2)->getFont()->setBold(true);
////              $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Customer Visists Report  '.$empinfo['0']['employee_info']['EmpName']);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setBold(true);
//                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, $rowcount)->getFont()->setSize(12);
                    $rowcount = 6;


                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Date');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Time');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((3), ($rowcount), 'Location');


                    //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Location');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Customer');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Purpose');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'IN/OUT');
//                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    for ($i = 0; $i <= 9; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }


                    $rowcount = 6;
                    // debug($arr_data);die();
                    // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0).$rowcount1,$branch);

                    $rowcount1 = $rowcount + 1;
                    if (count($arr_location_updates) >= 0) {
                        $k = 1;
                        $data = $arr_resp['data'];
                        foreach ($data as $val) {
                            $columnindex = 0;
                            $rowcount++;
                            //                            $employee_id = $val['employee_info']['employee_id'];
//                            $name = $val['employee_info']['EmpName'];
//                            $department = $val['employee_info']['department'];
//                            $branch = $val['employee_info']['branch'];
//                            $dates = $val['mob_user_tracking']['created_time'];
                            $date = $val[1];
                            $time = $val[2];
                            $location = $val[3];


                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $time);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 3) . $rowcount, $location);
                            //                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 4) . $rowcount, $in);
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 5) . $rowcount, $out);
//                            // $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex).$rowcount,$name);
//                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 6) . $rowcount, $duration);
////                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 7) . $rowcount, $customer);
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
                    break;
                default:
                    $this->set('mode', '');
                    $this->render('mobile_track');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

    private function generateKmTravelled($mode)
    {
        $arr_form_data = $_REQUEST;
        //        debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from_dates = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] . ' 00:00' : '';
        $to_dates = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . ' 23:59' : '';
        $emp_fkey = isset($arr_form_data['EmployeeDetails']['0']) ? $arr_form_data['EmployeeDetails']['0'] : 0;
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
        $lat2 = isset($arr_location['0']['mob_user_locations']['latitude']) ? $arr_location['0']['mob_user_locations']['latitude'] : '';
        $lon2 = isset($arr_location['0']['mob_user_locations']['longitude']) ? $arr_location['0']['mob_user_locations']['longitude'] : '';
        // $totaldistance = 0;

        foreach ($arr_location_dates as $val) {
            $i = 0;
            $totaldistance = 0;
            $ss = $val['0']['ym'];
            $dtt = new DateTime($ss);
            $date2 = $dtt->format('d-m-Y');
            foreach ($arr_location as $value) {

                $s = $value["mob_user_locations"]['created_time'];
                $dt = new DateTime($s);
                $date1 = $dt->format('d-m-Y');
                //                

                if ($date1 == $date2) {
                    $i++;
                    if ($i == 1) {
                        $lat2 = $value['mob_user_locations']['latitude'];
                        $lon2 = $value['mob_user_locations']['longitude'];
                    }
                    $lat1 = $value['mob_user_locations']['latitude'];
                    $lon1 = $value['mob_user_locations']['longitude'];
                    $theta = $lon1 - $lon2;

                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                    //$dist = acos($dist);
                    $dist = acos(min($dist, 1));
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;
                    //                                    debug($miles);
                    $kmdistance = $miles * 1.609344;
                    $kmeter = round($kmdistance, 2);
                    $totaldistance = $totaldistance + $kmeter;
                    // $totaldistance = $totaldistance + $kmdistance;

                    $lat2 = $value['mob_user_locations']['latitude'];
                    $lon2 = $value['mob_user_locations']['longitude'];
                    //                         debug($totaldistance);
                }

            }
            $distance[] = array(
                'date' => $date2,
                'km' => round($totaldistance, 2)
            );

        }


        $arr_resp = array(
            'data' => array()
        );


        $i = 1;
        if (!empty($distance)) {
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





        $location = array();
        foreach ($arr_location as $key => $val) {

            $location[] = array($val['mob_user_locations']['location'], $val['mob_user_locations']['latitude'], $val['mob_user_locations']['longitude']);
        }

        $this->set("datas", json_encode($location));

        //        debug("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        //$arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_locations.* FROM `mob_user_locations` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");

        //        debug($arr_location_updates);



        //         $this->set("arr_location", $arr_location_updates);



        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');

        //Parsing the months
        $spli_month = explode(" - ", $month);
        $start_month = $month;
        //        isset($spli_month[0])?date('Y-m-d',  strtotime($spli_month[0])):date('Y-m-d');
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-1');
        //        isset($spli_month[1])?date('Y-m-d',  strtotime($spli_month[1])):date('Y-m-d');
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
        $fd = $start_month . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $report_month = $arr_form_data['reportfrom'] . " - " . $arr_form_data['reportto'];
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }


        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {


            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            //            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('kmtravelled');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('DistanceTravelled.pdf', 'D');


                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "DistanceTravelled Report.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Customer Visit Distance Travelled Report  - $report_month");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                    for ($col = 'A'; $col !== 'G'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:K1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );

                    $rowcount = 2;
                    $columncount = 0;
                    $i = 0;
                    $empname = isset($empinfo['0']['employee_info']['EmpName']) ? $empinfo['0']['employee_info']['EmpName'] : '';
                    $empid = isset($empinfo['0']['employee_info']['employee_id']) ? $empinfo['0']['employee_info']['employee_id'] : '';
                    $desig = isset($empinfo['0']['employee_info']['designation']) ? $empinfo['0']['employee_info']['designation'] : '';
                    $dep = isset($empinfo['0']['employee_info']['department']) ? $empinfo['0']['employee_info']['department'] : '';
                    $branch = isset($empinfo['0']['employee_info']['branch']) ? $empinfo['0']['employee_info']['branch'] : '';
                    $join = isset($empinfo['0']['employee_info']['joining_date']) ? $empinfo['0']['employee_info']['joining_date'] : '';

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee Name : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $empname);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, "Employee ID : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount, $empid);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount)->getFont()->setBold(true);
                    //2nd row 
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Designation :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $desig);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 1, "Department :");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 1, $dep);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+1)->getFont()->setBold(true);
                    //3rd row
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Branch :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $branch);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 2, 'Joining Date :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 2, $join);

                    $rowcount = 6;


                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Date');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Approximate Distance Travelled(KM)');

                    for ($i = 0; $i <= 9; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }
                    $rowcount = 6;

                    $rowcount1 = $rowcount + 1;
                    if (count($arr_location_updates) >= 0) {
                        $k = 1;
                        $data = $arr_resp['data'];
                        foreach ($data as $val) {
                            $columnindex = 0;
                            $rowcount++;
                            //                           
                            $date = $val[1];
                            $dist = $val[2];
                            //                            $location = $val[3];


                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $dist);

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
                    break;
                default:
                    $this->set('mode', '');
                    $this->render('kmtravelled');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }
    private function generateKmTravelled_google($mode)
    {
        $arr_form_data = $_REQUEST;
        //        debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from_dates = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] . ' 00:00' : '';
        $to_dates = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . ' 23:59' : '';
        $emp_fkey = isset($arr_form_data['EmployeeDetails']['0']) ? $arr_form_data['EmployeeDetails']['0'] : 0;
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

        $arr_location_dates = $this->MobileUserauditor->query("SELECT distinct date_format(stepin_time ,'%Y-%m-%d') as ym,sum(total_distance) total "
            . "FROM cus_visit_locations where `user_id` = '$user_id' and stepin_time between '$from_dates' and '$to_dates' GROUP BY ym ");

        //        $lat2 = isset($arr_location['0']['mob_user_locations']['latitude']) ? $arr_location['0']['mob_user_locations']['latitude']:'';
//        $lon2  = isset($arr_location['0']['mob_user_locations']['longitude'])?$arr_location['0']['mob_user_locations']['longitude']:'';

        foreach ($arr_location_dates as $val) {
            //            $i =0;
//            $totaldistance = 0;
            $ss = $val['0']['ym'];
            $dtt = new DateTime($ss);
            $date2 = $dtt->format('d-m-Y');
            $totaldistance = $val['0']['total'];
            //            foreach ($arr_location as $value) {
//                $s = $value["mob_user_locations"]['created_time'];
//                $dt = new DateTime($s);
//                $date1 = $dt->format('d-m-Y');               
//                
//                if($date1 == $date2){
//                $i++;
//                if($i == 1 ){
//                $lat2 = $value['mob_user_locations']['latitude'];
//                $lon2  = $value['mob_user_locations']['longitude'];
//                }
//                    $lat1  = $value['mob_user_locations']['latitude'];
//                    $lon1 = $value['mob_user_locations']['longitude'];
//                    $theta = $lon1 - $lon2;
//    
//                                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
//                                    //$dist = acos($dist);
//                                    $dist = acos(min($dist,1));
//                                    $dist = rad2deg($dist);
//                                    $miles = $dist * 60 * 1.1515;
//                                    $kmdistance = $miles * 1.609344;
//                                    $kmeter =  round($kmdistance,2);
//                                    $totaldistance = $totaldistance + $kmeter;
//
//                         $lat2 = $value['mob_user_locations']['latitude'];
//                         $lon2  = $value['mob_user_locations']['longitude'];
//                }
//                
//            }
            $distance[] = array(
                'date' => $date2,
                'km' => round($totaldistance, 2)
            );

        }


        $arr_resp = array(
            'data' => array()
        );


        $i = 1;
        if (!empty($distance)) {
            foreach ($distance as $key => $att) {
                $arr_resp['data'][$i][]/* ['EmpName'] */ = $i;
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['date'];
                $arr_resp['data'][$i][]/* ['Branch'] */ = $att['km'];
                $i++;
            }
        }
        $this->set('arr_leavepolicydetails_for_template', $arr_resp);
        $this->set('empinfo', $empinfo);

        $location = array();
        foreach ($arr_location as $key => $val) {

            $location[] = array($val['mob_user_locations']['location'], $val['mob_user_locations']['latitude'], $val['mob_user_locations']['longitude']);
        }

        $this->set("datas", json_encode($location));
        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_locations.* FROM `mob_user_locations` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");

        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');

        //Parsing the months
        $spli_month = explode(" - ", $month);
        $start_month = $month;
        //        isset($spli_month[0])?date('Y-m-d',  strtotime($spli_month[0])):date('Y-m-d');
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-1');
        //        isset($spli_month[1])?date('Y-m-d',  strtotime($spli_month[1])):date('Y-m-d');
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
        $fd = $start_month . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $report_month = $arr_form_data['reportfrom'] . " - " . $arr_form_data['reportto'];
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }


        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {


            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('kmtravelled');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('DistanceTravelled.pdf', 'D');


                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "DistanceTravelled Report.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Customer Visit Distance Travelled Report  - $report_month");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(16);
                    for ($col = 'A'; $col !== 'D'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:D1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );

                    $rowcount = 2;
                    $columncount = 0;
                    $i = 0;
                    $empname = isset($empinfo['0']['employee_info']['EmpName']) ? $empinfo['0']['employee_info']['EmpName'] : '';
                    $empid = isset($empinfo['0']['employee_info']['employee_id']) ? $empinfo['0']['employee_info']['employee_id'] : '';
                    $desig = isset($empinfo['0']['employee_info']['designation']) ? $empinfo['0']['employee_info']['designation'] : '';
                    $dep = isset($empinfo['0']['employee_info']['department']) ? $empinfo['0']['employee_info']['department'] : '';
                    $branch = isset($empinfo['0']['employee_info']['branch']) ? $empinfo['0']['employee_info']['branch'] : '';
                    $join = isset($empinfo['0']['employee_info']['joining_date']) ? $empinfo['0']['employee_info']['joining_date'] : '';

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee Name : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $empname);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, "Employee ID : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount, $empid);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount)->getFont()->setBold(true);
                    //2nd row 
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Designation :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $desig);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 1, "Department :");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 1, $dep);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+1)->getFont()->setBold(true);
                    //3rd row
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Branch :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $branch);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 2, 'Joining Date :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 2, $join);

                    $rowcount = 6;


                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Date');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Approximate Distance Travelled(KM)');

                    for ($i = 0; $i <= 9; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }
                    $worksheet->mergeCells('A5:D5');
                    $rowcount = 6;

                    $rowcount1 = $rowcount + 1;
                    if (count($arr_location_updates) >= 0) {
                        $k = 1;
                        $data = $arr_resp['data'];
                        foreach ($data as $val) {
                            $columnindex = 0;
                            $rowcount++;
                            //                           
                            $date = $val[1];
                            $dist = $val[2];
                            //                            $location = $val[3];


                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $dist);

                            $k++;
                        }
                    }
                    foreach (range('A', 'D') as $columnID) {
                        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);

                    }
                    $BStyle = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    //$row=$rowcount-1;
                    $objPHPExcel->getActiveSheet()->getStyle('A1:D' . $rowcount)->applyFromArray($BStyle);
                    $objPHPExcel->getActiveSheet()->setTitle('Customer Visit Report');
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
                    $this->render('kmtravelled');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }
    private function generateKmTravelledTracking($mode)
    {
        $arr_form_data = $_REQUEST;
        //        debug($arr_form_data);
        $this->MobileUserauditor->useDbConfig = $this->Session->read('ds');
        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $from_dates = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] . ' 00:00' : '';
        $to_dates = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . ' 23:59' : '';
        $emp_fkey = isset($arr_form_data['EmployeeDetails']['0']) ? $arr_form_data['EmployeeDetails']['0'] : 0;
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
        $lat2 = isset($arr_location['0']['mob_user_tracking']['latitude']) ? $arr_location['0']['mob_user_tracking']['latitude'] : '';
        $lon2 = isset($arr_location['0']['mob_user_tracking']['longitude']) ? $arr_location['0']['mob_user_tracking']['longitude'] : '';
        // $totaldistance = 0;
        foreach ($arr_location_dates as $val) {
            $i = 0;
            $totaldistance = 0;
            $ss = $val['0']['ym'];
            $dtt = new DateTime($ss);
            $date2 = $dtt->format('d-m-Y');
            foreach ($arr_location as $value) {

                $s = $value["mob_user_tracking"]['created_time'];
                $dt = new DateTime($s);
                $date1 = $dt->format('d-m-Y');
                //                

                if ($date1 == $date2) {
                    $i++;
                    if ($i == 1) {
                        $lat2 = $value['mob_user_tracking']['latitude'];
                        $lon2 = $value['mob_user_tracking']['longitude'];
                    }
                    $lat1 = $value['mob_user_tracking']['latitude'];
                    $lon1 = $value['mob_user_tracking']['longitude'];
                    $theta = $lon1 - $lon2;

                    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                    //$dist = acos($dist);
                    $dist = acos(min($dist, 1));
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;
                    //                                    debug($miles);

                    $kmdistance = $miles * 1.609344;
                    $kmeter = round($kmdistance, 2);
                    $totaldistance = $totaldistance + $kmeter;

                    $lat2 = $value['mob_user_tracking']['latitude'];
                    $lon2 = $value['mob_user_tracking']['longitude'];
                    //                         debug($totaldistance);
                }

            }
            $distance[] = array(
                'date' => $date2,
                'km' => round($totaldistance, 2)
            );

        }


        $arr_resp = array(
            'data' => array()
        );


        $i = 1;
        if (!empty($distance)) {
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





        //        $location = array();
//        foreach ($arr_location as $key => $val) {
//
//            $location[] = array($val['mob_user_locations']['location'], $val['mob_user_locations']['latitude'], $val['mob_user_locations']['longitude']);
//        }
//        
//        $this->set("datas", json_encode($location));

        //        debug("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");

        //        debug($arr_location_updates);



        //         $this->set("arr_location", $arr_location_updates);



        $company_code = $this->Session->read('company_code'); //company_code
        $user_id = $this->Session->read('company_code');
        $arr_db_config = Set::extract('/DbConfig/.', $this->DbConfig->find("first", array("fields" => array("attendance_date"), "conditions" => array('active' => 'Y', 'company_code' => $company_code))));
        $month = isset($arr_form_data['reportfrom']) ? $arr_form_data['reportfrom'] : date('Y-m-1');

        //Parsing the months
        $spli_month = explode(" - ", $month);
        $start_month = $month;
        //        isset($spli_month[0])?date('Y-m-d',  strtotime($spli_month[0])):date('Y-m-d');
        $end_month = isset($arr_form_data['reportto']) ? $arr_form_data['reportto'] . " " . "23:59:59" : date('Y-m-1');
        //        isset($spli_month[1])?date('Y-m-d',  strtotime($spli_month[1])):date('Y-m-d');
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
        $fd = $start_month . ' ' . '00:00:00';
        if ((isset($arr_form_data['reportfrom']) && $arr_form_data['reportfrom'] != '')) {
            $report_month = $arr_form_data['reportfrom'];
            $from = date('Y-m-1', strtotime($arr_form_data['reportfrom']));
            $to = date('Y-m-t', strtotime($arr_form_data['reportfrom']));
        }
        $report_month = $arr_form_data['reportfrom'] . " - " . $arr_form_data['reportto'];
        $arr_leavepolicygroupids = array();
        $int_criterias_count = $arr_form_data['hidden-criterias-count'];
        for ($i = 1; $i <= $int_criterias_count; $i++) {
            $str_criteria_item = $arr_form_data['hidden-criteria' . $i];

            $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
        }


        $arr_leavepolicydetails_for_template = array();
        if (isset($arr_leavepolicygroupids) && !empty($arr_leavepolicygroupids)) {


            $this->set("criteria", $arr_form_data['hidden-criteria1']);
            //            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $user_name = $this->Session->read('user_name');
            $this->set('user_name', $user_name);
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set('arr_comp_contact_info', $arr_comp_contact_info);
            $this->set("report_month", $report_month);
            switch ($mode) {
                case 'pdf':
                    //echo "entered in";die();
                    $this->set('mode', 'pdf');
                    $view = new View($this, false);
                    $view_output = $view->render('kmtravelledtracking');

                    App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

                    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
                    //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                    //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->writeHTML($view_output);
                    $html2pdf->Output('MobileTrackingDistanceTravelled.pdf', 'D');


                    // $this->render('reportsummary');                
                    break;
                case 'excel':

                    $str_company_code = $this->Session->read('company_code');
                    $file_name = isset($str_company_code) ? $str_company_code . "Mobile Tracking - DistanceTravelled Report.xlsx" : "AttendanceA" . strtotime() . ".xlsx";

                    App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
                    $objPHPExcel = new PHPExcel();

                    $objPHPExcel->getProperties()->setCreator("Administrator");
                    $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
                    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
                    $objPHPExcel->getProperties()->setDescription("Employee Lcoation Updates Report By Forsight");

                    $objPHPExcel->setActiveSheetIndex(0);

                    $worksheet = $objPHPExcel->getActiveSheet();

                    $worksheet->setCellValueByColumnAndRow(0, 1, "Mobile Tracking - Distance Travelled Report  - $report_month");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setSize(18);
                    for ($col = 'A'; $col !== 'G'; $col++) {
                        $objPHPExcel->getActiveSheet()
                            ->getColumnDimension($col)
                            ->setAutoSize(true);
                    }
                    $worksheet->mergeCells('A1:K1');
                    $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                        array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, )
                    );

                    $rowcount = 2;
                    $columncount = 0;
                    $i = 0;
                    $empname = isset($empinfo['0']['employee_info']['EmpName']) ? $empinfo['0']['employee_info']['EmpName'] : '';
                    $empid = isset($empinfo['0']['employee_info']['employee_id']) ? $empinfo['0']['employee_info']['employee_id'] : '';
                    $desig = isset($empinfo['0']['employee_info']['designation']) ? $empinfo['0']['employee_info']['designation'] : '';
                    $dep = isset($empinfo['0']['employee_info']['department']) ? $empinfo['0']['employee_info']['department'] : '';
                    $branch = isset($empinfo['0']['employee_info']['branch']) ? $empinfo['0']['employee_info']['branch'] : '';
                    $join = isset($empinfo['0']['employee_info']['joining_date']) ? $empinfo['0']['employee_info']['joining_date'] : '';

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount, "Employee Name : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount, $empname);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount, "Employee ID : ");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount, $empid);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount)->getFont()->setBold(true);
                    //2nd row 
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 1, 'Designation :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 1, $desig);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 1, "Department :");
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 1)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 1, $dep);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+3, $rowcount+1)->getFont()->setBold(true);
                    //3rd row
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount, $rowcount + 2, 'Branch :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 1, $rowcount + 2, $branch);
                    //                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount+1, $rowcount+2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 2, $rowcount + 2, 'Joining Date :');
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 2, $rowcount + 2)->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columncount + 3, $rowcount + 2, $join);

                    $rowcount = 6;


                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl No');
                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((1), ($rowcount), 'Date');
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow((2), ($rowcount), 'Approximate Distance Travelled(KM)');

                    for ($i = 0; $i <= 9; $i++) {
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i, ($rowcount))->getFont()->setBold(true);
                    }
                    $rowcount = 6;

                    $rowcount1 = $rowcount + 1;
                    if (count($arr_location_updates) >= 0) {
                        $k = 1;
                        $data = $arr_resp['data'];
                        foreach ($data as $val) {
                            $columnindex = 0;
                            $rowcount++;
                            //                           
                            $date = $val[1];
                            $dist = $val[2];
                            //                            $location = $val[3];


                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowcount, $k);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 1) . $rowcount, $date);
                            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex + 2) . $rowcount, $dist);

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
                    break;
                default:
                    $this->set('mode', '');
                    $this->render('kmtravelledtracking');
                    break;
            }
        } else {
            echo "<div style='color:red'><h3>No record Found</h3></div>";
            // $this->layout=null;
        }
    }

}
