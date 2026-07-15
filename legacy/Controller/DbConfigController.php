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
class DbConfigController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'DbConfig';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('DayTimeProcedures', 'CompanyContactInfo', 'DbConfig', 'Designation', 'Departments','MobileUserCredentials','UserCredentials' );
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function index() {
        $this->layout = FALSE;

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
    }

    public function form() {
        $data['id'] = 0;
        $data['dept_code'] = "";
        $data['dept_name'] = "";
        $data['status'] = "";


        $this->DbConfig->useDbConfig = $this->Session->read('ds');

        $data_db = $this->DbConfig->find("first");

        $data = $data_db['DbConfig'];


        $this->layout = null;
        $this->set("data_db", $data_db); //debug($data_db);
    }

    public function config() {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $company = $this->Session->read('company_code');
        $update = $this->DbConfig->query("UPDATE db_config SET company_code = '$company' ");
         //$update = $this->DbConfig->query("UPDATE branches SET company_code = '$company' ");
        //EDITED BY MEGHA on 13/09/2019 updated branchcode and company code
        $branch = $company.'01';
        $update1 = $this->DbConfig->query("UPDATE branches SET company_code = '$company',branch_code = '$branch'  ORDER BY ID LIMIT 1 ");
        $update2 = $this->DbConfig->query("UPDATE fin_year SET company_code = '$company',branch_code = '$branch' ORDER BY Fin_year_seq LIMIT 2 ");
        //end
    }

    public function designation_departments() {
        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        $res = mysql_query('SELECT * FROM department WHERE status = 1 ');
        $Arr_holidays = array();
        while($row = mysql_fetch_assoc($res)){
            $Arr_holidays[] = $row;
        }
        $res = mysql_query('SELECT * FROM designation WHERE status = 1 ');
        $Arr_holidays_designations = array();
        while($row = mysql_fetch_assoc($res)){
            $Arr_holidays_designations[] = $row;
        }
        $this->set("arr_holidays", $Arr_holidays);
        $this->set("arr_holidays_designations", $Arr_holidays_designations);
        $fetch_departments = $this->DbConfig->query('SELECT * FROM department WHERE status = 1 ');
        
        $fetch_designations = $this->DbConfig->query('SELECT * FROM designation WHERE status = 1 ');
        
        $this->set("fetch_departments", $fetch_departments);
        $this->set("fetch_designations", $fetch_designations);
    }
    
    public function save_shift($pShift_id = 0) {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        $arr_form_data = $this->request->data;
        $pShift_id = $arr_form_data['id'];
            $res = mysql_query("SELECT * FROM working_day_time_procedures WHERE day_time_seq = '$pShift_id' ");
            $policy = mysql_fetch_assoc($res);
            $save_data = array();
            $day_time_seq = '';
            $day_time_desc = $policy['day_time_desc'];
            $Sunday = $policy['Sunday'];
            $Sunday_F = $policy['Sunday_F'];
            $Monday = $policy['Monday'];
            $Monday_F = $policy['Monday_F'];
            $Tuesday = $policy['Tuesday'];
            $Tuesday_F = $policy['Tuesday_F'];
            $Wednesday = $policy['Wednesday'];
            $Wednesday_F = $policy['Wednesday_F'];
            $Thursday = $policy['Thursday'];
            $Thursday_F = $policy['Thursday_F'];
            $Friday = $policy['Friday'];
            $Friday_F = $policy['Friday_F'];
            $Saturday = $policy['Saturday'];
            $Saturday_F = $policy['Saturday_F'];
            $on_dutty1 = $policy['on_dutty1'];
            $off_dutty1 = $policy['off_dutty1'];
            $working_time1 = $policy['working_time1'];
            $on_dutty2 = $policy['on_dutty2'];
            $off_dutty2 = $policy['off_dutty2'];
            $working_time2 = $policy['working_time2'];
            $on_dutty3 = $policy['on_dutty3'];
            $off_dutty3 = $policy['off_dutty3'];
            $working_time3 = $policy['working_time3'];
            $on_dutty4 = $policy['on_dutty4'];
            $off_dutty4 = $policy['off_dutty4'];
            $working_time4 = $policy['working_time4'];
            $minuts_calc_perday = $policy['minuts_calc_perday'];
            $minuts_aftr_on_dutty_cal_late = $policy['minuts_aftr_on_dutty_cal_late'];
            $minuts_bfr_off_dutty_cal_early = $policy['minuts_bfr_off_dutty_cal_early'];
            $min_cal_late_ifnoclockin = $policy['min_cal_late_ifnoclockin'];
            $min_cal_leave_early_ifnoclockout = $policy['min_cal_leave_early_ifnoclockout'];
            $min_aftr_off_dutty_cal_ot = $policy['min_aftr_off_dutty_cal_ot'];
            $min_bfr_on_dutty_cal_ot = $policy['min_bfr_on_dutty_cal_ot'];
            $work_time_day_off_cal_ot = $policy['work_time_day_off_cal_ot'];
            $active = $policy['active'];
            $isnextday = $policy['isnextday'];
            $shift_allowance = $policy['shift_allowance'];
            $otcomponents = $policy['otcomponents'];
            $strict_monitorings = $policy['strict_monitorings'];
            $start_date_effective = $policy['start_date_effective'];
            $end_date_effective = $policy['end_date_effective'];
            $minutes_per_half = $policy['minutes_per_half'];
            $is_multiple_days = $policy['is_multiple_days'];
            $no_of_shift_days = $policy['no_of_shift_days'];
            $minuts_calc_perday = $policy['minuts_calc_perday'];
            $minuts_calc_perday = $policy['minuts_calc_perday'];
            $minuts_calc_perday = $policy['minuts_calc_perday'];
            $is_exception = $policy['is_exception'];
            //edited by megha removed duplicate entry of shift policy on 14/09/2019
            $policy_list = $this->DayTimeProcedures->query("SELECT count(*) FROM working_day_time_procedures where day_time_desc='$day_time_desc'");
            $count=$policy_list['0']['0']['count(*)'];
            //debug($count);
            //$this->DayTimeProcedures->query("INSERT INTO working_day_time_procedures VALUES('$day_time_seq','$day_time_desc','$Sunday','$Sunday_F','$Monday','$Monday_F','$Tuesday','$Tuesday_F','$Wednesday','$Wednesday_F','$Thursday','$Thursday_F','$Friday','$Friday_F','$Saturday','$Saturday_F','$on_dutty1','$off_dutty1','$working_time1','$on_dutty2','$off_dutty2','$working_time2','$on_dutty3','$off_dutty3','$working_time3','$on_dutty4','$off_dutty4','$working_time4','$minuts_calc_perday','$minuts_aftr_on_dutty_cal_late','$minuts_bfr_off_dutty_cal_early','$min_cal_late_ifnoclockin','$min_cal_leave_early_ifnoclockout','$min_aftr_off_dutty_cal_ot','$min_bfr_on_dutty_cal_ot','$work_time_day_off_cal_ot','$active','$isnextday','$shift_allowance','$otcomponents','2018-01-01','2019-01-01','$strict_monitorings','$minutes_per_half','$is_multiple_days','$no_of_shift_days','$is_exception') ");
            //edited by megha added column names in query on 05/09/2019
            if($count<=0){
            $this->DayTimeProcedures->query("INSERT INTO working_day_time_procedures (day_time_seq, day_time_desc,Sunday,Sunday_F,Monday,Monday_F,Tuesday,Tuesday_F,Wednesday,Wednesday_F,Thursday,Thursday_F,Friday,Friday_F,Saturday,Saturday_F,on_dutty1,off_dutty1,working_time1,on_dutty2,off_dutty2,working_time2,on_dutty3,off_dutty3,working_time3,on_dutty4,off_dutty4,working_time4,minuts_calc_perday,minuts_aftr_on_dutty_cal_late,minuts_bfr_off_dutty_cal_early,min_cal_late_ifnoclockin,min_cal_leave_early_ifnoclockout,min_aftr_off_dutty_cal_ot,min_bfr_on_dutty_cal_ot,work_time_day_off_cal_ot,active,isnextday,shift_allowance,otcomponents,start_date_effective,end_date_effective,strict_monitorings,minutes_per_half,is_multiple_days,no_of_shift_days,is_exception,include_break) "
                    . "VALUES('$day_time_seq','$day_time_desc','$Sunday','$Sunday_F','$Monday','$Monday_F','$Tuesday','$Tuesday_F','$Wednesday','$Wednesday_F','$Thursday','$Thursday_F','$Friday','$Friday_F','$Saturday','$Saturday_F','$on_dutty1','$off_dutty1','$working_time1','$on_dutty2','$off_dutty2','$working_time2','$on_dutty3','$off_dutty3','$working_time3','$on_dutty4','$off_dutty4','$working_time4','$minuts_calc_perday','$minuts_aftr_on_dutty_cal_late','$minuts_bfr_off_dutty_cal_early','$min_cal_late_ifnoclockin','$min_cal_leave_early_ifnoclockout','$min_aftr_off_dutty_cal_ot','$min_bfr_on_dutty_cal_ot','$work_time_day_off_cal_ot','$active','$isnextday','$shift_allowance','$otcomponents','2018-01-01','2019-01-01','$strict_monitorings','$minutes_per_half','$is_multiple_days','$no_of_shift_days','$is_exception','N') ");
              // 
              //         $this->DayTimeProcedures->save($policy);
            return json_encode(array("success"=>true));
            }else{
               return json_encode(array("success"=>false)); 
            }
        
    }
    
    public function remove_shift(){
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $pShift_id = $arr_form_data['id'];
        $this->DayTimeProcedures->query("DELETE FROM working_day_time_procedures WHERE day_time_seq = '$pShift_id' ");
        return json_encode(array("success"=>true));
    }
    
    public function holidays_s() {
        $response = file_get_contents('https://calendarific.com/api/v2/holidays?country=IN&year=2019&api_key=b4620f916b7bf502a05e6828584e2af58ebab255');
        $response = json_decode($response);
        $arr_holiday = array();
        foreach ($response->response->holidays as $holidays){
            $arr_holiday[] = array(
                "name" =>$holidays->name,
                "description"=>$holidays->description,
                "date"=> $holidays->date->iso 
            );
        }
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        $res = mysql_query('SELECT * FROM holiday_group WHERE status = 1 ');
        $Arr_holidays = array();
        while($row = mysql_fetch_assoc($res)){
            $Arr_holidays[] = $row;
        }
        $this->set("arr_holidays", $Arr_holidays);
//        debug($Arr_holidays);
    }

    public function holidays() {
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        $res = mysql_query('SELECT * FROM holiday_group WHERE status = 1 ');
        $Arr_holidays = array();
        while($row = mysql_fetch_assoc($res)){
            $Arr_holidays[] = $row;
        }
        $this->set("arr_holidays", $Arr_holidays);
//        debug($Arr_holidays);
    }
    
    public function save_holidays(){
        $this->autoRender = FALSE;
        $this->layout = null;

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        
        $arr_form_data = $this->request->data;
        if(empty($arr_form_data['checklists'])){
            $this->redirect(array('controller' => 'DbConfig', 'action' => 'policy'));
        }
        foreach($arr_form_data['checklists'] as $val){
            $res = mysql_query("SELECT * FROM holiday_group WHERE status = 1 and HOLIDAY_GROUP_ID = '$val' ");
            $row = mysql_fetch_assoc($res);
            $holiday_group_name = $row['HOLIDAY_GROUP_NAME'];
            $res_days = mysql_query("SELECT * FROM holidays WHERE status = 1 and HOLIDAY_GROUP_ID = '$val' ");
            $Arr_holidays = array();
            while ($row_days = mysql_fetch_assoc($res_days)) {
                $Arr_holidays[] = $row_days;
            }
//            debug($Arr_holidays); 
            $this->DbConfig->query("INSERT INTO holiday_group (HOLIDAY_GROUP_NAME) VALUES('$holiday_group_name') ");
            foreach($Arr_holidays as $vals){
                $holiday_name = $vals['HOLIDAYNAME'];
                $holiday_date = $vals['HOLIDAYDATE'];
                $holiday_desc = $vals['DESCRIPTION'];
                $holiday_type = 'MANDATORY';
                $this->DbConfig->query("INSERT INTO holidays (HOLIDAY_GROUP_ID,HOLIDAYNAME,HOLIDAYDATE,DESCRIPTION,HOLIDAYTYPE) VALUES('$val','$holiday_name','$holiday_date','$holiday_desc','MANDATORY') ");
            }
            
            
        }
        
        $this->redirect(array('controller' => 'DbConfig', 'action' => 'policy'));
    }
    
    public function policy(){
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        $this->DayTimeProcedures->query("UPDATE wizard_config SET link = 'DbConfig/policy' , state = '1' ");
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        $res = mysql_query("SELECT * FROM working_day_time_procedures WHERE active = '1' ");
        $Arr_holidays = array();
        while($row = mysql_fetch_assoc($res)){
            $Arr_holidays[] = $row;
        }
        
        $Arr_holidays_local = $this->DayTimeProcedures->find("all",array("conditions"=>array("active"=>"1")));
        $this->set("arr_holidays_local", $Arr_holidays_local);
        
        $this->set("arr_holidays", $Arr_holidays);
        
    }
    
    public function show_policiess($id = 0){
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        $res = mysql_query("SELECT * FROM working_day_time_procedures WHERE day_time_seq = '$id' ");
        $Arr_holidays = array();
        $respdata = mysql_fetch_assoc($res);
        $this->set("data", $respdata);
        
        $components_res = mysql_query("SELECT * FROM `salary_head_items` left join salary_heads on (salary_heads.head_pkey = salary_head_items.head_fkey) WHERE head_occurance = 'VARIABLE' and lcase(value) = 'y'");
//        $components = mysql_fetch_assoc($components);
        $components = array();
        while($row = mysql_fetch_assoc($components_res)){
            $components[] = $row;
        }
        $this->set("components",$components);
//        debug($respdata);
//        $arr_shiftreport = $this->DayTimeProcedures->query("select * from working_day_time_procedures where day_time_seq in(select day_time_seq from emp_proff where emp_fkey =$sessionid)");
//        $shiftallowance = ($arr_shiftreport['0']['working_day_time_procedures']['shift_allowance'] && $arr_shiftreport['0']['working_day_time_procedures']['shift_allowance'] != '') ? $arr_shiftreport['0']['working_day_time_procedures']['shift_allowance'] : '0';
              //  ($arr_shiftreport['0']['working_day_time_procedures']['shift_allowance']) ? $arr_shiftreport['0']['working_day_time_procedures']['shift_allowance'] : '0';
//        $arr_otcomp = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $shiftallowance");
//        //debug($arr_shiftreport);
//        $otcomponent = ($arr_shiftreport['0']['working_day_time_procedures']['otcomponents'] && $arr_shiftreport['0']['working_day_time_procedures']['otcomponents'] != '') ? $arr_shiftreport['0']['working_day_time_procedures']['otcomponents'] : '0';   
//       
//        $arr_otcomponent = $this->SalaryHeadItems->query("select item from salary_head_items where salary_head_item_pkey = $otcomponent");
        
//        $this->set('arr_shiftreport', $arr_shiftreport);
//        $this->set('arr_emp', $arr_emp);
//        $this->set('arr_otcomp', $arr_otcomp);
//        $this->set('arr_otcomponent', $arr_otcomponent);
    }
    
    public function save_policies(){
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        
        $arr_form_data = $this->request->data;
        if(empty($arr_form_data['checklists'])){
            $this->redirect(array('controller' => 'DbConfig', 'action' => 'leave_heads'));
        }
        
        $this->redirect(array('controller' => 'DbConfig', 'action' => 'leave_heads'));
        
    }
    
    public function SetupComplete(){
        
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        $this->DayTimeProcedures->query("UPDATE wizard_config SET state = '1' ");
        
        $this->redirect(array('controller' => 'Dashboard', 'action' => 'index'));
    }
    
    public function leave_heads() {
        $this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
        $leave_heads = $this->DayTimeProcedures->query("SELECT * FROM `salary_head_items` WHERE item_type = 'LEAVE' and status = '1' ");
        $this->set("leave_heads", $leave_heads);
//        debug($leave_heads);
    }
    
    public function save_leave_head() {
        $this->Designation->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $this->autoRender = FALSE;
        
        $id = $arr_form_data['id'];
        $type = $arr_form_data['type'];
        //$data['org_id'] = $companycode;
        //$data['status']    = $arr_form_data['status'];
        $resp = array();
        
        try{
            if($type == 1)
                $result = $this->Designation->query("UPDATE salary_head_items SET value = 'N' WHERE salary_head_item_pkey = '$id' ");
            else
                $result = $this->Designation->query("UPDATE salary_head_items SET value = 'Y' WHERE salary_head_item_pkey = '$id' ");
            $resp["success"] = true;
            $resp["msg"] = "Department Deleted Successfully";
        } catch (Exception $ex) {
            $resp["success"] = false;
            $resp["msg"] = "Department Deletion failed ";
        }
        

        
        echo json_encode($resp);
    }
    
    public function salary_policy() {
        
    }
    
    public function emp_upload() {
        
    }
    
    public function load_config() {
        
    }
    
    public function emp_login() {
        
    }
    
    
    public function save_Desig(){
        $this->Designation->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $this->autoRender = FALSE;
        
        $data['desig_code'] = $arr_form_data['id'];
        $id = $arr_form_data['id'];
        $data['desig_name'] = $arr_form_data['named'];
        //$data['org_id'] = $companycode;
        //$data['status']    = $arr_form_data['status'];
        $resp = array();
        
        $int_desigcount = $this->Designation->find("count",array(
                    'conditions' => array('Designation.desig_code' => $id,'Designation.status' => 1     )
                )
            );
        if($int_desigcount > 0){
            $resp["success"] = false;
            $resp["msg"] = "Designation Already Exists ";
            echo json_encode($resp);
            return false;
        }
        try{
            $result = $this->Designation->save($data);
            $resp["success"] = true;
            $resp["msg"] = "Designation Saved Successfully";
        } catch (Exception $ex) {
            $resp["success"] = false;
            $resp["msg"] = "Designation Saving failed ";
        }
        

        
        echo json_encode($resp);
    }
    
    public function welcome() {
        
    }
    
    public function savecompletess($bank_id = 0) {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        //   debug($arr_form_data);
        
        $arr_emps = $this->UserCredentials->find("all",array("conditions"=>array("password = '' ")));
        
        foreach ($arr_emps as $emps) {
            $userid = $emps['UserCredentials']['user_id'];
            $email = $emps['UserCredentials']['email'];
            
            $password1 = $arr_form_data['pasword'];
            $arr_form_data['password'] = Security::hash($password1, null, true);
            $arr_form_data['reset_login_flag'] = 'Y';
            $arr_form_data['locked'] = '0';
            $arr_form_data['incorrect_login_attempt'] = '0';
//            }
            // debug($arr_form_data);
            $this->MobileUserCredentials->useDbConfig = $this->Session->read('ds');
            $arr_mobile_data = array();
            $arr_mobile_data['user_id'] = $emps['UserCredentials']['user_id'];
            $arr_form_data['user_pkey'] = $emps['UserCredentials']['user_pkey'];
            $user = $this->MobileUserCredentials->find("first", array("conditions" => array("user_id" => $arr_mobile_data['user_id'])));
            if (count($user) > 0) {
                $arr_mobile_data['user_pkey'] = $user['MobileUserCredentials']['user_pkey'];
            }

            
            $arr_mobile_data['firstname'] = $emps['UserCredentials']['first_name'];
            $arr_mobile_data['lastname'] = $emps['UserCredentials']['last_name'];
            $arr_mobile_data['password'] = $password1;

            $arr_mobile_data['token'] = "Y";
            $arr_form_data['mobileaccess'] = 'Y';
            $arr_mobile_data['locked'] = 'N';


            $arr_form_data['punchtype'] = 'M';
            $arr_mobile_data['punchtype'] = 'M';
            
            debug($arr_form_data);
            debug($arr_mobile_data);
            $this->MobileUserCredentials->saveAll($arr_mobile_data);

            $this->UserCredentials->saveAll($arr_form_data);
            //  debug($data_db);
        }
        echo json_encode(array('msg' => 'User Credentials saved successfully'));
        //$this->render();
//        if(isset($password1)){
//            $this->sendpasswordemail($email, $userid, $password1);
//        }
    }
    
    public function remove_desig(){
        $this->Designation->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $this->autoRender = FALSE;
        
        $id = $arr_form_data['id'];
        //$data['org_id'] = $companycode;
        //$data['status']    = $arr_form_data['status'];
        $resp = array();
        
        try{
            $result = $this->Designation->query("UPDATE designation SET status = '0' WHERE desig_code = '$id' ");
            $resp["success"] = true;
            $resp["msg"] = "Designation Deleted Successfully";
        } catch (Exception $ex) {
            $resp["success"] = false;
            $resp["msg"] = "Designation Deletion failed ";
        }
        

        
        echo json_encode($resp);
    }


    public function showholidays($id = 0){
        $link = mysql_connect('localhost', 'root', 'Localhost&*()');
        mysql_select_db('mypayrol_control_db', $link);
        $res = mysql_query("SELECT * FROM holidays WHERE HOLIDAY_GROUP_ID = '$id' and status = 1 ");
        $Arr_holidays = array();
        while($row = mysql_fetch_assoc($res)){
            $Arr_holidays[] = $row;
        }
        $this->set("arr_holidays", $Arr_holidays);
    }

    public function savedepartment(){
        $this->Departments->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $this->autoRender = FALSE;
        
        $data['dept_code'] = $arr_form_data['id'];
        $id = $arr_form_data['id'];
        $data['dept_name'] = $arr_form_data['named'];
        //$data['org_id'] = $companycode;
        //$data['status']    = $arr_form_data['status'];
        $resp = array();
        
        $int_desigcount = $this->Departments->find("count",array(
                    'conditions' => array('Departments.dept_code' => $id,'Departments.status' => 1     )
                )
            );
        if($int_desigcount > 0){
            $resp["success"] = false;
            $resp["msg"] = "Departments Already Exists ";
            echo json_encode($resp);
            return false;
        }
        try{
            $result = $this->Departments->save($data);
            $resp["success"] = true;
            $resp["msg"] = "Departments Saved Successfully";
        } catch (Exception $ex) {
            $resp["success"] = false;
            $resp["msg"] = "Departments Saving failed ";
        }
        

        
        echo json_encode($resp);
    }

    public function remove_dept(){
        $this->Departments->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $this->autoRender = FALSE;
        
        $id = $arr_form_data['id'];
        //$data['org_id'] = $companycode;
        //$data['status']    = $arr_form_data['status'];
        $resp = array();
        
        try{
            $result = $this->Departments->query("UPDATE department SET status = '0' WHERE dept_code = '$id' ");
            $resp["success"] = true;
            $resp["msg"] = "Department Deleted Successfully";
        } catch (Exception $ex) {
            $resp["success"] = false;
            $resp["msg"] = "Department Deletion failed ";
        }
        

        
        echo json_encode($resp);
    }
    
    public function login_cred() {
        
    }
    
    public function completed_setup() {
        
    }
    
    public function listdb() {
        $this->autoRender = FALSE;
        $this->datatable["conditions"] = array("status" => 1);

        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'company_code';
        $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $this->DbConfig->useDbConfig = $this->Session->read('ds');
        //    echo json_encode($this->DataTable->getData('Departments',$columns));
        $resp_banks = array();
        $resp_banks["rows"] = array();
        $this->DbConfig->useDbConfig = $this->Session->read('ds');

        $count = $this->DbConfig->find("count");

        $arr_banks = $this->DbConfig->find("all", array(
            'order' => array($sort => $order),
            'limit' => intval($limit),
            'offset' => intval($ofst)
                )
        );

        foreach ($arr_banks as $key => $value) {
            $resp_banks["rows"][$key] = $value["DbConfig"];
        }
        $resp_banks["total"] = $count;
        echo json_encode($resp_banks);
    }

    public function deleteDepartment() {
        $this->autoRender = FALSE;
        $this->Departments->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["ids"]);
            //	debug($ar_ids);
            $this->Departments->updateAll(
                    array('Departments.status' => 0), array('Departments.id' => $ar_ids)
            );
            $result['success'] = true;
            $result['msg'] = "Record(s)  deleted successfully.";
        }

        echo json_encode($result);
    }

}
