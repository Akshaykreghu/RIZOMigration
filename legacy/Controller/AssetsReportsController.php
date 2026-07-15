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
 */
class AssetsReportsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
    //public $layout = "default";
    public $name = 'AssetsReports';
    public $datatable;

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $uses = array('LeavePolicyGroup','CentralControl','UserCredentials','EmployeeDetails','AssetType','EmployeeProfessionalDetails','Departments','Grades','Assets','AssetsName','allocate','AssetsModel','Verticals','Units','ReportCriterias','DayTimeProcedures','EmpCtcTransaction','LeaveRequests','ReportAudit');
    public $components = array('MasterdataManagement');
    
    /*public $arr_employee_reportcriterias = array(
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
    );*/

    /*
     * HR Reports landing view
     */
   public function hrreports()
    {           
        $arr_reporttypes = array(
//            'employee' => 'Employee Information',
       'History' => 'Asset History Report'
        );
        $this->set('arr_reporttypes',$arr_reporttypes);
    }
    
    /*
     * Change Sub Report type
     */ 
    public function changereporttype($type=''){
        $this -> autoRender = FALSE;
        $this->ReportCriterias->useDbConfig = $this->Session->read('ds');
        if($type != ''){
            $this->set('type',$type);
            switch ($type){
               
                case 'employee':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'History':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'List':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break; 
                case 'SummaryPayroll':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                case 'Salaryslip':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                //santhu
                case 'salary':
                                $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
                                $this->set('arr_reportcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1,'reporttype'=>$type)))));
                                break;
                default :
                        echo "No criterias found";
                        break;
            }
            $this->render('showreport');
        }else{
            echo "No criterias found";
        }
    }
    
    /*
     * Add criterias
     */
    public function addreportcriteria($type='', $newindex='',$str_currentcriterias=''){
        $this -> autoRender = FALSE;
        if($type != '' && $str_currentcriterias != ''){            
            //$arr_currentcriterias = explode(',', $str_currentcriterias);
            //$arr_remainingcriterias = array_diff(array_flip($this->arr_employee_reportcriterias), $arr_currentcriterias);
            //$this->set('arr_remainingcriterias',array_flip($arr_remainingcriterias));
            $str_currentcriterias = "'".str_replace(",","','",$str_currentcriterias)."'";
            $this->ReportCriterias->useDbConfig = $this -> Session -> read('ds');
            $this->set('arr_remainingcriterias', Set::extract('/ReportCriterias/.',$this->ReportCriterias->find("all",array("conditions"=>array("status"=>1, 'reportcriteria NOT IN('.$str_currentcriterias.')','reporttype'=>$type)))));
                                
            $this->set('newindex',$newindex);
            $this->render('showcriteria');
        }else{
            return '';
        }
    }
    
    /*
     * Load criteria items
     */
    public function loadcriteriaitems($index,$str_criteria=''){
        $this -> autoRender = FALSE;
        if($str_criteria != ''){
            $model = $str_criteria;
            if($this->_modelExists($model)){
                $this->set('index',$index);
                $model = ($model == 'EmployeeDetails')?'Employees':$model;
                $this->set('criteria',$model);
                $this->render('loadcriteriaitems');
            }else{
                return '';
            }
        }else{
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
            $this->AssetType->useDbConfig = $this->Session->read('ds');
            if ($model == 'SalaryHeadItems') {
                $conditions = array("head_fkey" => 6, "value" => 'Y', "status" => 1);
            } elseif ($model == 'DayTimeProcedures') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Leavestatus') {
                $conditions = array();
                //edited by amal asset report criterias 
            } elseif ($model == 'AssetsModel') {
                $conditions = array("active" => 1);
            } elseif ($model == 'Assets') {
                $conditions = array("active" => 1, "status !=" => 'Not Allocated');
            } elseif ($model == 'AssetsName') {
                $conditions = array("active" => 1, "status !=" => 'Not Allocated');
            } else {
                $conditions = array("status" => 1);
            }
            if ($model == 'AssetsModel') {
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => array("DISTINCT brand"), "conditions" => $conditions)));
            } else if ($model == 'Assets') {
                $model = 'AssetType';
                //$arr_criteriaItemsDB = Set::extract('/'.$model.'/.',$this->{$model}->find("all",array("fields"=>array("DISTINCT Type"),"conditions"=>$conditions)));
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("fields" => array('DISTINCT asset_type_pkey', 'asset_type_name'), "joins" => array(array(
                    'table' => 'asset_management',
                    'alias' => 'Assets',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('Assets.Type = AssetType.asset_type_pkey')
                )))));
                $model = 'Assets';
            } else {
                $arr_criteriaItemsDB = Set::extract('/' . $model . '/.', $this->{$model}->find("all", array("conditions" => $conditions)));
            }
            $arr_criteriaItems = array();
            $key = 0;
            switch ($model) {
                case 'Assets':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['asset_type_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['asset_type_name'];
                        $key++;
                    }
                    break;
                case 'AssetsName':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['asset_pkey'];
                        $arr_criteriaItems[$key]['text'] = $value['name'] . '' . $value['serial_no'];
                        $key++;
                    }
                    break;
                case 'AssetsModel':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['brand'];
                        $arr_criteriaItems[$key]['text'] = $value['brand'];
                        $key++;
                    }
                    break;
                case 'List':
                    foreach ($arr_criteriaItemsDB as $key => $value) {
                        $arr_criteriaItems[$key]['key'] = $value['grade_code'];
                        $arr_criteriaItems[$key]['text'] = $value['grade_name'];
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
                        ),
                        array(
                            'table' => 'asset_allocate',
                            'alias' => 'AssetAllocate',
                            'type' => 'INNER',
                            'foreignKey' => false,
                            'conditions' => array('EmployeeDetails.emp_pkey = AssetAllocate.emp_fkey')
                        )
                    );

                    $conditions = array();


                    if (isset($arr_requestdata['name']) && $arr_requestdata['name'] == '1') {
                        $conditions = array("EmployeeDetails.status in(1,2)");
                    } else {

                        $conditions = array("EmployeeDetails.status" => 1);
                    }

                    // Edited by Akshay on 28-1-2025
                    $user = $this->Session->read('company_code');
                    $user_group = $this->Session->read('user_group');
                    if ($user_group == 2 && ($user == 'GLET' || $user == 'ABSG')) {
                        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                        $emp_pkey = $this->Session->read('emp_fkey');
                        $arr_is_ho = $this->EmployeeDetails->query("SELECT get_branch_code_abs_fn($emp_pkey) as branch;");
                        $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                        if ($is_ho != 1) {
                            $conditions["EmployeeDetails.branch_code"] = $is_ho;
                        }
                    }
                    // End

                    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                    $arr_emp =  $this->EmployeeDetails->find("all", array(
                        'fields' => $fields,
                        'joins' => $joins,
                        'conditions' => $conditions,
                        'group' => 'emp_pkey'
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
   public function reportAudit($type,$mode){
        $this->autoRender = false;

        //This is to save download history. By Arul P Das on 25_1_2021
        $dataForHistory = array();
        $arr_form_data = $_REQUEST;

        switch ($type) {
            case 'History':
                $dataForHistory['report_type'] = "Assets History Report";
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
                case 'Assets': $criteria_name_array[] = 'belonging to a Assets Type';
                    break;
                case 'AssetsName': $criteria_name_array[] = 'belonging to a Assets Name';
                    break;
                case 'AssetsModel': $criteria_name_array[] = 'belonging to a Assets Brand';
                    break;
                default : break;
            }
            $items_array[] = isset($arr_form_data[$criteria])?implode(",", $arr_form_data[$criteria]):'';
            $items_count_array[] = isset($arr_form_data[$criteria])?count($arr_form_data[$criteria]):0;

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
    public function generatereport($type='',$mode=''){
        $this->autoRender = false;

       switch ($type) {
            case 'History':
                $this->generateHistoryreport($mode);
                break;
            case 'salarystructure':
                $this->generateEmpSalaryreport($mode);
                break;
             case 'SummaryPayroll':
                $this->GenerateSummaryPayrolreport($mode);
                break;
             case 'Salaryslip':
                $this->GenerateSalarySlipreport($mode);
                break;
             case 'TimeAttendance':
                $this->generatetimeattendancereport($type, $mode);
                break;
            default:
                return false;
                break;
        }
		  $this->reportAudit($type,$mode);
    }
    
    public function listemployeefields(){
        App::import('Vendor', 'EmployeeInformationFields', array('file'=>'ReportFields'.DS.'EmployeeInformationFields.php'));
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
                $data['id'] = $key.'.'.$key1;
                $data['data'] = array($value1);
                $resp_emp["rows"][] = $data;
            }
        }
        echo json_encode($resp_emp);
        $this->autoRender = FALSE;
    }
    
    private function _modelExists($modelName){
      $models = App::objects('model');
      return in_array($modelName,$models);
   }
   
   
   
   private function generateHistoryreport($mode)
   {
       $arr_form_data = $_REQUEST;
       //  debug($arr_form_data);
       //   debug($mode);
       $this->Assets->useDbConfig = $this->Session->read('ds');
       //  debug($arr_form_data);
       //Build conditions based on criterias recieved
       //        $fd=$arr_form_data['reportfrom'].' '.'00:00:00';
       //         $Td=$arr_form_data['reportto'].' '.'00:00:00';
       //$report_month = $arr_form_data['reportfrom'];
       $from = date('Y-m',  strtotime($arr_form_data['reportfrom']));
       //            $to = date('Y-m-t',  strtotime($arr_form_data['reportfrom']));
       //            debug($to);
       //            debug($from);
       $arr_leavepolicygroupids = array();
       $str_criteria_item = array();
       $conditions = array();
       $int_criterias_count = $arr_form_data['hidden-criterias-count'];
       for ($i = 1; $i <= $int_criterias_count; $i++) {
           $str_criteria_item = $arr_form_data['hidden-criteria' . $i];
           $criteria = $arr_form_data['select-criteria' . $i];
           $arr_leavepolicygroupids = isset($arr_form_data[$str_criteria_item]) ? $arr_form_data[$str_criteria_item] : '';
           if (isset($arr_form_data[$arr_form_data['hidden-criteria' . $i]])) {
           } else {
               echo "Please Choose Criteria Items";
               die();
           }
           $ids = implode('","', $arr_leavepolicygroupids);
           //order added to the query belongs to the criteria selected.
           //added by megha on 13/08/2019 1
           if ($str_criteria_item == 'Assets') {
               $conditions[] = 'Assets.Type in ("' . $ids . '") ';
               //$order = "allocate.allocate_pkey DESC,Assets.Type ASC";
               $order = "Assets.Type ASC,EmployeeDetails.first_name ASC,EmployeeDetails.last_name ASC";
           }
           if ($str_criteria_item == 'AssetsModel') {
               $conditions[] = 'Assets.brand in ("' . $ids . '") ';
               //$order = "allocate.allocate_pkey DESC,Assets.brand ASC";
               $order = "Assets.brand ASC,EmployeeDetails.first_name ASC,EmployeeDetails.last_name ASC";
           }
           if ($str_criteria_item == 'AssetsName') {
               $conditions[] = 'Assets.asset_pkey in ("' . $ids . '") ';
               //$order = "allocate.allocate_pkey DESC,Assets.name ASC";
               $order = "Assets.name ASC,EmployeeDetails.first_name ASC,EmployeeDetails.last_name ASC";
           }
           if ($str_criteria_item == 'EmployeeDetails') {
               $conditions[] = 'EmployeeDetails.emp_pkey in ("' . $ids . '") ';
               //$order = "EmployeeDetails.first_name ASC,EmployeeDetails.last_name ASC";
               $order = "allocate.allocate_pkey DESC";
           }
           //ends order added to the query belongs to the criteria selected.

       }
       //debug($str_criteria_item);
       //debug($conditions);
       $arr_leavepolicydetails_for_template = array();
       if ($arr_leavepolicygroupids != '') {
           foreach ($arr_leavepolicygroupids as $leavepolicygroupid) {

               //  debug($leavepolicygroupid);
               //added by amal on 16/08/2019 allocate.description
               $fields = array("allocate.description,AssetType.asset_type_name,allocate.asset_state,allocate.damaged_amout,Assets.name,Assets.specifications,Assets.value,Assets.condition,Assets.year,Assets.asset_pkey,Assets.Type,Assets.serial_no,Assets.warranty,Assets.model,Assets.brand,allocate.status,allocate.allocated_date,allocate.retreived_date,allocate.emp_fkey,EmployeeDetails.first_name,EmployeeDetails.last_name,EmployeeDetails.emp_pkey,EmployeeDetails.status,EmployeeDetails.emp_id");

               $joins = array(
                   array(
                       'table' => 'asset_allocate',
                       'alias' => 'allocate',
                       'type' => 'LEFT',
                       'foreignKey' => false,
                       'conditions' => array('Assets.asset_pkey = allocate.asset')
                   ),
                   array(
                       'table' => 'emp_details',
                       'alias' => 'EmployeeDetails',
                       'type' => 'LEFT',
                       'foreignKey' => false,
                       'conditions' => array('EmployeeDetails.emp_pkey = allocate.emp_fkey')
                   ),
                   array(
                       'table' => 'asset_types',
                       'alias' => 'AssetType',
                       'type' => 'LEFT',
                       'foreignKey' => false,
                       'conditions' => array('Assets.Type = AssetType.asset_type_pkey')
                   ),
               );
               if (isset($arr_form_data['resigned']) && $arr_form_data['resigned'] == '1') {

                   $conditions[] = "EmployeeDetails.status in('1','2')";
               } else {
                   $conditions[] = "EmployeeDetails.status in('1')";
               }

               // Edited by Akshay on 11-2-2025
               $current_emp_pkey = $this->Session->read('emp_fkey');
               $user_group = $this->Session->read('user_group');
               $company_code = $this->Session->read('company_code');
               if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

                   $this->Assets->useDbConfig = $this->Session->read('ds');
                   $arr_is_ho = $this->Assets->query(
                       "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                       ['emp_pkey' => $current_emp_pkey]
                   );
                   $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                   if ($is_ho != 1) {
                       $conditions[] = "EmployeeDetails.branch_code = '$is_ho'";
                   }
               }
               // End

               //order added to the query belongs to the criteria selected.
               //added by megha on 13/08/2019 2
               $assets_array = $this->Assets->find("all", array("conditions" => $conditions, "order" => $order, "joins" => $joins, "fields" => $fields));

               $asset_management_array = array();
               $this->set('assets_array', $assets_array);
               foreach ($assets_array as $val) {
                   $emppk = $val['Assets']['name'];

                   $asset_management_array[$emppk][] = $val;
               }

               //            $arr_salary_for_template[] = array(
               //                //'leavepolicyname'=> isset($arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'])?$arr_leavepolicy_details[0]['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']:'',
               //                'summary' => $arr_empleaverequests,
               //                     'withoutcomponent'=>$salaryslipwithoutcomponents,
               //                'empdet'=>$empdetails
               //            );
           }
       }
       // $this->set('arr_salary_for_template', $arr_salary_for_template);
       //  debug($arr_salary_for_template);
       //   $employee_attendance= array();
       //      foreach($arr_empleaverequests as $val)
       //      {
       //          $emp = $val['ectc']['head_operator'];
       //          $emppk = $val['ectc']['head_type'];
       //          $itempart = $val['ectc']['item_part'];
       //          $employee_attendance[$emppk][$emp][$itempart][] = $val;
       //          
       //      }
       //  debug($employee_attendance);

       //  $this->set('employee_attendance', $employee_attendance);


       // debug($resp_register);
       // debug($arr_leavepolicydetails_for_template);  
       //  debug($arr_salary_for_template);
       // $this->set('arr_salary_for_template', $arr_salary_for_template);
       $cr = $arr_form_data['select-criteria1'];
       $this->set('cr', $cr);

       $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
       $user_name = $this->Session->read('user_name');
       $this->set('user_name', $user_name);
       $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
       $this->set('arr_comp_contact_info', $arr_comp_contact_info);

       switch ($mode) {
           case 'pdf':
               $this->set('mode', 'pdf');
               $view = new View($this, false);
               $view_output = $view->render('History');
               App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));

               $html2pdf = new HTML2PDF('L', 'A4', 'en');
               //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
               //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
               $html2pdf->pdf->SetDisplayMode('fullpage');
               $html2pdf->writeHTML($view_output);
               $html2pdf->Output('AssetsReports.pdf', 'D');
               $this->render('History');
               break;
           case 'excel':
               $str_company_code   =   $this->Session->read('company_code');
               $file_name  = isset($str_company_code) ? $str_company_code . "_AssetReport.xlsx" : "AssetReport" . strtotime() . ".xlsx";

               App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
               $objPHPExcel = new PHPExcel();

               $objPHPExcel->getProperties()->setCreator("Administrator");
               $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
               $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
               $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
               $objPHPExcel->getProperties()->setDescription("Assets Information Report");

               $objPHPExcel->setActiveSheetIndex(0);

               $worksheet = $objPHPExcel->getActiveSheet();

               $worksheet->setCellValueByColumnAndRow(0, 1, "Asset History Report");
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
               $worksheet->mergeCells('A1:P1');
               $worksheet->getStyle('A1')->getAlignment()->applyFromArray(
                   array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
               );
               $columncount = 0;
               $rowcount = 2;
               //edited by amal 
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow((0), $rowcount)->getFont()->setBold(true);
               $rowcount += 1;
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, 'Sl. No');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount, $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(0))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, 'Asset Name');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columncount + 1, $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, 'Specifications');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 2), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, 'Value');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 3), $rowcount)->getFont()->setBold(true);
               //commented by amal on 16/08/2019 
               //                 $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3).$rowcount, 'Conditions');
               //                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
               //                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4).$rowcount, 'Year');
               //$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, 'Type');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 4), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, 'Asset Sl. No');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 5), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, 'Warranty');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 6), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, 'Model');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 7), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, 'Brand');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 8), $rowcount)->getFont()->setBold(true);
               //added by amal on 16/08/2019 allocate.description
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, 'Description');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 9), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, 'Status');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 10), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, 'Asset Condition');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 11), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, 'Current Value');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 12), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, 'Allocated To');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 13), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, 'Allocated Date');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 14), $rowcount)->getFont()->setBold(true);
               $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, 'Retreived Date');
               $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(($columncount + 15), $rowcount)->getFont()->setBold(true);
               $rowcount++;
               $i = 0;
               foreach ($assets_array as $val) {
                   if ($val['allocate']['asset_state'] == 3) {
                       $state = "Not Working";
                   }
                   if ($val['allocate']['asset_state']  == 1) {
                       $state = "Good";
                   }
                   if ($val['allocate']['asset_state'] == 0) {
                       $state = "Good";
                   }
                   if ($val['allocate']['asset_state']  == 2) {
                       $state = "Damage But Working";
                   }
                   //edited by megha on 3/12/2019 current value changed
                   if (!empty($val['allocate']['damaged_amout'])) {
                       $value1 = $val['allocate']['damaged_amout'];
                   } else {
                       $value1 = $val['Assets']['value'];
                   }
                   //end
                   $empstatus = isset($val['EmployeeDetails']['status']) && $val['EmployeeDetails']['status'] == "2" ? '(Resigned)' : '';
                   $empid = isset($val['EmployeeDetails']['emp_id']) ? $val['EmployeeDetails']['emp_id'] : '';
                   $i = $i + 1;
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(0) . $rowcount, $i);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(1) . $rowcount, $val['Assets']['name']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(2) . $rowcount, $val['Assets']['specifications']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3) . $rowcount, $val['Assets']['value']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(3).$rowcount, $val['Assets']['condition']);
                   //                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(3))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   //                    $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4).$rowcount, $val['Assets']['year']);
                   //                    $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(4) . $rowcount, $val['AssetType']['asset_type_name']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(4))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(5) . $rowcount, $val['Assets']['serial_no']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(5))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(6) . $rowcount, $val['Assets']['warranty']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(6))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(7) . $rowcount, $val['Assets']['model']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(7))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(8) . $rowcount, $val['Assets']['brand']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(8))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   //added by amal on 16/08/2019 allocate.description
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(9) . $rowcount, $val['allocate']['description']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(9))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(10) . $rowcount, $val['allocate']['status']);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(10))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(11) . $rowcount, $state);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(11))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(12) . $rowcount, $value1);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(12))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(13) . $rowcount, $val['EmployeeDetails']['first_name'] . ' ' . $val['EmployeeDetails']['last_name'] . ' ' . $val['EmployeeDetails']['emp_id'] . $empstatus);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(13))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $allocated = $val['allocate']['allocated_date'];
                   $allocated_date = date('d-m-Y', strtotime($allocated));
                   $retrieved_date = isset($val['allocate']['retreived_date']) && !empty($val['allocate']['retreived_date'])
                       ? date('d-m-Y', strtotime($val['allocate']['retreived_date']))
                       : '';
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(14) . $rowcount, $allocated_date);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(14))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex(15) . $rowcount, $retrieved_date);
                   $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex(15))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                   $rowcount++;
               }

               $objPHPExcel->getActiveSheet()->setTitle('Asset History report');

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
               $this->render('History');
               break;
       }
   }
   
    
   
}