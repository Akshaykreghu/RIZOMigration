<?php

class DashboardController extends AppController {

    public $layout = "default";
    public $name = "Dashboard";
    public $uses = array("EmployeeDetails", "Earlyin", "Latein", "Earlyout", "Lateout", "EmployeeProfessionalDetails", "DeviceAttendance", "LeaveRequests", "EmployeeDetails", "Device","Useraccess","AttendancePunch");

    public function index() {
        //action logic goes here..
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            
            //$this->empdashboard();
            $this -> Useraccess ->useDbConfig = $this->Session->read('ds');
            $emp_fkey = $this->Session->read('emp_fkey');
            $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$emp_fkey' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                //Hierarchy dashboard
                $this->hierarchydashboard();
            } else {
                //Employee dashboard
                $this->empdashboard();
            }
            
        } else {
            //Admin dashboard
            
            $arr_menus = $this->getMenus();
            $this->set('arr_menu', $arr_menus);
            
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $count = $this->EmployeeDetails->find("count", array("conditions" => array("emp_id !=" => null,'status'=>1)));
            $this->set("total_emps", $count);
            
            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $arr_present = $this->DeviceAttendance->query("select count(*) as presentcount from present_today");
            $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
            $this->set("present", $present);

            $arr_empleaverequests = $this->listemployeeleaverequests(0);
            $this->set("arr_empleaverequests", $arr_empleaverequests);
			
			/**
			 * Fetch miss punch count of current months
			 * On 04 March 2017
			 */
            $arr_empmisspunches = $this->listemployeemisspunches();
            $this->set("arr_empmisspunches", $arr_empmisspunches); 
			//Ends
			
//            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
//            $this->set('arr_employees', $arr_employees);
            $table_joins[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
            $table_joins[] =  array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
            $this->set('arr_employees', $arr_employees);
             $conditions = array(
                'OR' => array(
                    array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'),'status'=>1),
                    array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'),'status'=>1),
                )
            );
            $this->Latein->useDbConfig = $this->Session->read('ds');
            //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
            $arr_employees_pics = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.date_of_birth","EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name","USC.avatar","Empproff.joining_date"),"joins"=>$table_joins, "conditions" => $conditions));
            $arr_reminders = $this->EmployeeDetails->query("select emp_details.first_name,emp_details.last_name,emp_proff.day_time_seq,emp_proff.HOLIDAY_GROUP_ID,emp_proff.LEAVEPOLICY_GROUP_ID,emp_proff.structure_id from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 and (emp_proff.day_time_seq is null or emp_proff.HOLIDAY_GROUP_ID is null or emp_proff.LEAVEPOLICY_GROUP_ID is null or emp_proff.structure_id is null) ");
            $salary_missed = $this->Latein->query("select concat(first_name,' ',last_name) name from emp_details where emp_details.status = '1' and emp_pkey not in (select distinct(emp_fkey) from emp_ctc_upload)");
            
            $today = date('Y-m-d');
            $leaves = $this->Latein->query("select count(*) lea from emp_leave_transactions where Leavestatus in ('Approved') and leave_date = '$today' ");
            $leaves = isset($leaves['0']['0']['lea'])?$leaves['0']['0']['lea']:0;
            $this->set("leaves",$leaves);
            $this->set("salary_missed",$salary_missed);
            $this->set("reminders",$arr_reminders);
            $this->set('arr_employees_pics', $arr_employees_pics);
            //Get total number of devices
            $cnt_total_devices = $this->Device->find("count", array("conditions" => array('Device.company_code' => $this->Session->read('company_code'))));
            $this->set('cnt_total_devices', $cnt_total_devices);
        }
    }

    public function hierarchydashboard() {
        //Hierarchy dashboard
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');
        $arr_menus = $this->getMenus();
        $this->set('arr_menu', $arr_menus);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$count = $this->EmployeeDetails->find("count", array("conditions" => array("emp_id !=" => null)));
       // $this->set("total_emps", $count);
      $table_joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
        );
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $count = $this->EmployeeDetails->find("count", array("joins"=>$table_joins,"conditions" => array("EmployeeDetails.status"=>"1","EmployeeProfessionalDetails.attr1 = '$emp_fkey' or EmployeeProfessionalDetails.emp_fkey = '$emp_fkey' ")));
        $this->set("total_emps", $count);
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        //$present = $this->DeviceAttendance->find("count",array("conditions"=>array("emp_id !="=>null)));
//        $arr_present = $this->DeviceAttendance->query("SELECT COUNT(DISTINCT DeviceAttendance.emp_id) AS presentcount,EmployeeDetails.first_name 
//                FROM `device_attandance` AS `DeviceAttendance` 
//                left join emp_details as EmployeeDetails on(DeviceAttendance.emp_id = EmployeeDetails.emp_id) 
//                left join emp_proff as EmpProff on(EmployeeDetails.emp_pkey = EmpProff.emp_fkey) 
//                WHERE DeviceAttendance.LOGDATE >= date_sub(current_date,interval 1 day) 
//                AND UCASE(DeviceAttendance.status) = 'Y' 
//                AND UCASE(DeviceAttendance.c1) = 'in' 
//                AND DeviceAttendance.emp_id not in (select emp_id from device_attandance where DeviceAttendance.LOGDATE >= date_sub(current_date,interval 1 day)
//                and UCASE(DeviceAttendance.status) = 'Y' AND UCASE(DeviceAttendance.c1) = 'out' )
//                    and (EmpProff.emp_fkey = '$emp_fkey' or EmpProff.attr1 = '$emp_fkey' )");
//        $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
        $emp_fkey = $this->Session->read('emp_fkey');
        $arr_present = $this->DeviceAttendance->query("select count(*) presentcount from present_today where emp_pkey in (select emp_fkey from emp_proff where attr1 ='$emp_fkey' or emp_fkey = '$emp_fkey' ) ");
        $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
        $this->set("present", $present);
        $this->set("present", $present);
        $user_id = isset($present['0']['user_credentials']['user_id'])?$present['0']['user_credentials']['user_id']:'';
        $present_type = $this->DeviceAttendance->query("SELECT punchtype FROM mob_user_credentials WHERE user_id = '$user_id' ");
        $this->set("punch_type",isset($present_type['0']['mob_user_credentials']['punchtype'])?$present_type['0']['mob_user_credentials']['punchtype']:'');
        
        /*$today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );
        $today_join[] = array(
            'table' => 'emp_proff',
            'alias' => 'EmployeeProfessionalDetails',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
        );

        $today_cond = array("EmployeeProfessionalDetails.attr1" => $emp_fkey, "LOGDATE >=" => date("Y-m-d", strtotime("now")), "DeviceAttendance.status >=" => "Y");
        $today_att = $this->DeviceAttendance->find("all", array(
            "order" => "EmployeeDetails.emp_id ASC,DeviceAttendance.LOGDATE ASC",
            "conditions" => $today_cond,
            'joins' => $today_join,
            "fields" => "DeviceAttendance.*EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
                )
        );
        $this->set("today_att", $today_att);
        
        $this_month_cond = array("EmployeeProfessionalDetails.attr1" => $emp_fkey, "LOGDATE >=" => date("Y-m-01", strtotime("now")), "LOGDATE <=" => date("Y-m-t", strtotime("now")), "DeviceAttendance.status >=" => "Y");
        $this_month_att = $this->DeviceAttendance->find("all", array(
            "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
            "conditions" => $this_month_cond,
            'joins' => $today_join,
            "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
                )
        );
        $this->set("this_month_att", $this_month_att);

        $last_month_cond = array("EmployeeProfessionalDetails.attr1" => $emp_fkey, "LOGDATE >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))), "LOGDATE <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))), "DeviceAttendance.status >=" => "Y");
        $last_month_att = $this->DeviceAttendance->find("all", array(
            "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
            "conditions" => $last_month_cond,
            'joins' => $today_join,
            "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name,if((`DeviceAttendance`.`C3` in ('',NULL)),`DeviceAttendance`.`branch_code`,`DeviceAttendance`.`C3`) AS `Location`"
                )
        );
        $this->set("last_month_att", $last_month_att);*/

        $arr_empleaverequests = $this->listemployeeleaverequests(0);
        $this->set("arr_empleaverequests", $arr_empleaverequests);
        $arr_events = $this->getEvents($emp_fkey);
        $this->set("arr_events",$arr_events);

        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        $this->set('arr_employees', $arr_employees);
        $table_joins[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
            $table_joins[] =  array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
            $this->set('arr_employees', $arr_employees);
             $conditions = array(
                'OR' => array(
                    array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'),'status'=>1),
                    array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'),'status'=>1),
                )
            );
            //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
            $arr_employees_pics = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.date_of_birth","EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name","USC.avatar","Empproff.joining_date"),"joins"=>$table_joins, "conditions" => $conditions));
            $this->set('arr_employees_pics', $arr_employees_pics);
            //debug($arr_employees_pics);
            $emp_pkeys = array();
            foreach($arr_employees_pics as $val)
            {
                $emp_pkeys[] = $val['EmployeeDetails']['emp_pkey'];
            }
            $wish = 0;
            if (in_array("$emp_fkey", $emp_pkeys)) {
                $wish = 1;
                
            }
            $this->set("wish",$wish);
        //Get total number of devices
        $cnt_total_devices = $this->Device->find("count", array("conditions" => array('Device.company_code' => $this->Session->read('company_code'))));
        $this->set('cnt_total_devices', $cnt_total_devices);
        
        $this->render('hierarchydashboard');
    }
	
    public function getEvents($emp_fkey = 0){
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $arr_events = $this->LeaveRequests->query("select 'HOL', HOLIDAYNAME,date_format(HOLIDAYDATE,'%M-%d') date_month,emp_fkey from holidays left join emp_proff on (holidays.HOLIDAY_GROUP_ID = emp_proff.HOLIDAY_GROUP_ID) where emp_fkey = '$emp_fkey' and date_format(HOLIDAYDATE,'%m-%d') between date_format(current_date,'%m-%d') and date_format(date_add(current_date,INTERVAL 30 DAY),'%m-%d') and emp_fkey in (select emp_pkey from emp_details where status = 1)
        union
        select 'BIR', first_name,date_format(date_of_birth,'%M-%d') date_month,emp_pkey from emp_details where date_format(date_of_birth,'%m-%d') between date_format(current_date,'%m-%d') and date_format(date_add(current_date,INTERVAL 30 DAY),'%m-%d') and  status = 1
        union
        select 'JOIN', first_name,date_format(joining_date,'%M-%d') date_month,emp_pkey from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where date_format(joining_date,'%m-%d') between date_format(current_date,'%m-%d') and date_format(date_add(current_date,INTERVAL 30 DAY),'%m-%d') and emp_details.status = 1
        order by date_format(date_month,'%m-%d')");
        return $arr_events;
    }
	
    public function empdashboard() {
        //action logic goes here..
        //Employee dashboard
        $emp_fkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');
        if ($emp_fkey) {
            $arr_menus = $this->getMenus();
            $this->set('arr_menu', $arr_menus);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $day = date("Y-m-1");
            $to_days = date("Y-m-d");
            $working_days_counts = $this->EmployeeDetails->query("select count(*) working_days from emp_detail_timeattandance where emp_pkey = '$emp_fkey' and att_date between '$day' and '$to_days' and weekoff is null and holiday is null and others is null -- leaves != LOP");
            $leaves_taken_count = $this->EmployeeDetails->query("select count(*) working_days from emp_detail_timeattandance where emp_pkey = '$emp_fkey' and att_date between '$day' and '$to_days' and leaves not in ('LOP/LOP') ");
            $present_days_count = $this->EmployeeDetails->query("select sum(a) presents from(
            select count(*) as a from emp_detail_timeattandance where present in ('P/P') and 	emp_pkey = '$emp_fkey' and att_date between '$day' and '$to_days' 
            union all
            select count(*)/2 as a from emp_detail_timeattandance where (instr(present,'/P') > 0 or instr(present,'P/') > 0) and present != 'P/P'  and 	emp_pkey = '$emp_fkey' and att_date between '$day' and '$to_days' 
     )ass ");
//            debug($present_days_count);
//            debug($working_days_counts);
            $pr_count = isset($present_days_count['0']['0']['presents'])?$present_days_count['0']['0']['presents']:0;
            $wr_count = isset($working_days_counts['0']['0']['working_days'])?$working_days_counts['0']['0']['working_days']:0;
            $lea_count = isset($leaves_taken_count['0']['0']['working_days'])?$leaves_taken_count['0']['0']['working_days']:0;
            $percentage_total = 0;
            if($pr_count!=0){
            $prsent_percentage = 100 * round($pr_count+$lea_count) / round($wr_count);
            $percentage_total = round($prsent_percentage,1);
            }
            $this->set("percentage_total", $percentage_total);
            $user_id = isset($present['0']['user_credentials']['user_id']) ? $present['0']['user_credentials']['user_id'] : '';
            $present_type = $this->DeviceAttendance->query("SELECT punchtype FROM mob_user_credentials WHERE user_id = '$user_id' ");
            $this->set("punch_type", isset($present_type['0']['mob_user_credentials']['punchtype']) ? $present_type['0']['mob_user_credentials']['punchtype'] : '');
            /*$today_join[] = array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
            );
            $today_join[] = array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            );

            $today_cond = array("LOGDATE >=" => date("Y-m-d", strtotime("now")), "DeviceAttendance.status >=" => "Y", 'EmployeeDetails.emp_pkey' => $emp_fkey);
            $today_att = $this->DeviceAttendance->find("all", array(
                "order" => "EmployeeDetails.emp_id ASC,DeviceAttendance.LOGDATE ASC",
                "conditions" => $today_cond,
                'joins' => $today_join,
                "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name"
                    )
            );
            $this->set("today_att", $today_att);

            $this_month_cond = array("LOGDATE >=" => date("Y-m-01", strtotime("now")), "LOGDATE <=" => date("Y-m-t", strtotime("now")), "DeviceAttendance.status >=" => "Y", 'EmployeeDetails.emp_pkey' => $emp_fkey);
            $this_month_att = $this->DeviceAttendance->find("all", array(
                "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
                "conditions" => $this_month_cond,
                'joins' => $today_join,
                "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name"
                    )
            );
            $this->set("this_month_att", $this_month_att);

            $last_month_cond = array("LOGDATE >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))), "LOGDATE <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))), "DeviceAttendance.status >=" => "Y", 'EmployeeDetails.emp_pkey' => $emp_fkey);
            $last_month_att = $this->DeviceAttendance->find("all", array(
                "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
                "conditions" => $last_month_cond,
                'joins' => $today_join,
                "fields" => "DeviceAttendance.*,EmployeeDetails.first_name,EmployeeDetails.last_name"
                    )
            );
            $this->set("last_month_att", $last_month_att);*/
            $table_joins[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
            $table_joins[] =  array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
            $this->set('arr_employees', $arr_employees);
             $conditions = array(
                'OR' => array(
                    array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'),'status'=>1),
                    array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'),'status'=>1),
                )
            );
            //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
            $arr_employees_pics = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.date_of_birth","EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name","USC.avatar","Empproff.joining_date"),"joins"=>$table_joins, "conditions" => $conditions));
            $this->set('arr_employees_pics', $arr_employees_pics);
            $emp_pkeys = array();
            foreach($arr_employees_pics as $val)
            {
                $emp_pkeys[] = $val['EmployeeDetails']['emp_pkey'];
            }
            $wish = 0;
            if (in_array("$emp_fkey", $emp_pkeys)) {
                $wish = 1;
                
            }
            $this->set("wish",$wish);
            $arr_empleaverequests = $this->listemployeeleaverequests($emp_fkey);
            $this->set("arr_empleaverequests", $arr_empleaverequests);
            $arr_events = $this->getEvents($emp_fkey);
            $this->set("arr_events",$arr_events);
            
            $this->render('empdashboard');
        } else {
            $this->Session->destroy();
            $this->redirect("/");
        }
    }
	
	public function timers()
    {
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        date_default_timezone_set("Asia/Kolkata"); 
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $t=time();
        $now = date("h:i:sa");
        $today = date('Y-m-d H:i:s');
        //debug($today);
//        $sessions_start = $this->Earlyin->query("select c1,min(LOGDATE) as starts from device_attandance left join emp_details as emps on(emps.emp_id = device_attandance.emp_id) where emps.emp_pkey = '$emp_fkey' and  lcase(c1)='in'  and DATE_FORMAT(LOGDATE,'%y-%m-%d') ");
//        debug($sessions_start);
//        if($sessions_start['0']['0']['starts'] == null)
//        {
//            $sessions_start1 = $this->Earlyin->query("select c1,max(LOGDATE) as starts from device_attandance left join emp_details as emps on(emps.emp_id = device_attandance.emp_id) where emps.emp_pkey = '$emp_fkey' and DATE_FORMAT(LOGDATE,'%y-%m-%d-1') ");
//            if($sessions_start1['0']['0']['starts'] != NULL && $sessions_start1['0']['device_attandance']['c1'] == 'in')
//            {
//                $session_started = $sessions_start1['0']['0']['starts'];
//            }
//            else
//            {
//                $session_started = null;
//            }
//        }
//        $sessions_end = $this->Earlyin->query("select max(LOGDATE) as starts from device_attandance left join emp_details as emps on(emps.emp_id = device_attandance.emp_id) where emps.emp_pkey = '$emp_fkey' and  lcase(c1)='out'  and DATE_FORMAT(LOGDATE,'%y-%m-%d') ");
//        debug($sessions_end);
        $sessions_start = $this->Earlyin->query("  select min(LOGDATE) vLOGDATE_intime
                  from device_attandance att,emp_details ed      
                    where att.emp_id=ed.emp_id 
                   and att.company_code=ed.company_code 
                 and lcase(c1)='in' and emp_pkey=$emp_fkey and ucase(att.status)='Y'
                and (LOGDATE,att.emp_id)
                 in (select min(LOGDATE) ,emp_id from device_attandance where 
                 lcase(c1)='in' and ucase(status)='Y'and date_format(LOGDATE,'%Y-%m-%d') >=
      date_format(current_date,'%Y-%m-%d') group by emp_id);");
        $start_date = $sessions_start['0']['0']['vLOGDATE_intime'];
        if($start_date == NULL)
        {
           $set = 0; 
        }
        else
        {
        $arr_session = $this->Earlyin->query("select ed.emp_pkey,    ed.first_name,      att.logdate,    att.C1

from device_attandance att, emp_details ed
WHERE 
                        att.emp_id = ed.emp_id and ucase(att.status)='Y'
                        AND ed.emp_pkey =  $emp_fkey
                        AND DATE_FORMAT(att.logdate,'%Y-%m-%d %T') BETWEEN 
                            DATE_FORMAT('$start_date', '%Y-%m-%d %T') AND DATE_FORMAT(CURRENT_TIMESTAMP(), '%Y-%m-%d %T');");
        $set = 0;
        $t_run = 1;
        //debug($arr_session);
        if(empty($arr_session))
        {
            $stat = date_create(date($today));
            $en = date_create(date($start_date));
            $diff=date_diff($stat,$en);
            $hours = $diff->h * 60;
            $minuites = $diff->i + $hours;
            $set = $minuites;
            //debug($set);
        }
        else
        {
        $timers = array();
        foreach ($arr_session as $key => $value)
        {
            if($value['att']['C1'] == 'in' || $value['att']['C1'] == 'IN')
            {
                
                if(isset($arr_session[$key+1]['att']['logdate']) && strtolower($arr_session[$key+1]['att']['C1']) == 'out')
                    { 
                        $starter = $arr_session[$key+1]['att']['logdate'];
                        $ens = $value['att']['logdate'];
                        $stat = date_create(date($starter));
                        $en = date_create(date($ens));
                        $diff = date_diff($stat,$en);
                        $hours = $diff->h * 60;
                        $minuites = $diff->i + $hours;
                        $set = $set + $minuites;
                        $t_run = 0;
                    }
                    else if(!isset($arr_session[$key+1]))
                    {
                        $ens = $value['att']['logdate'];
                        $stat = date_create(date($today));
                        $en = date_create(date($ens));
                        $diff = date_diff($stat,$en);
                        $hours = $diff->h * 60;
                        $minuites = $diff->i + $hours;
                        $set = $set + $minuites;
                        $t_run =1;
                    }
                    else{
                        
                    }
                //debug($set);
            }
            
        }
        
        
        }
        }
        $this->set("set",$set);
        echo json_encode(array('set'=>$set,'run'=>$t_run));
    }
 public function form($day = '', $day2 = 0) {
        //   debug($day);
        //    debug($day2);
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->Latein->useDbConfig = $this->Session->read('ds');
        $this->Earlyout->useDbConfig = $this->Session->read('ds');
        $this->Lateout->useDbConfig = $this->Session->read('ds');
		
        $time = date("Y-m-d");
        $from = date('Y-m-1', strtotime($time));
        $to = date('Y-m-t', strtotime($time));
        $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
        // debug($lmonth);
        $ltomonth = date("Y-m-t", strtotime('-1 months', strtotime("now")));
        //  debug($ltomonth);
		
        switch ($day2) {
            case 'Daily' : $condition = "where emp_early_in.LOGDATE >= '$time' ";
                break;
            case 'Month' : $condition = "where emp_early_in.LOGDATE between '$from' and '$to'";
                break;
            case 'LastMonth' : $condition = "where emp_early_in.LOGDATE between '$lmonth' and '$ltomonth' ";
                break;
           
            default : echo "No criterias found";
                break;
        }

        switch ($day) {
            case 'Earlyin' : $results = $this->Earlyin->query("select emp_early_in.* from emp_early_in as emp_early_in $condition ");
                break;
            case 'Latein' : $results = $this->Latein->query("select emp_early_in.* from emp_late_in as emp_early_in $condition ");
                break;
            case 'Earlyout' : $results = $this->Earlyout->query("select emp_early_in.* from emp_early_out as emp_early_in $condition");
                break;
            case 'Lateout' : $results = $this->Lateout->query("select emp_early_in.* from emp_late_out as emp_early_in $condition ");
                break;
            default : echo "No criterias found";
                break;
        }

        // debug($results);
        $this->set("results", $results);
        $this->render('form');
    }
    public function form2($day = '', $day2 = 0) {
        //   debug($day);
        //    debug($day2);
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->Latein->useDbConfig = $this->Session->read('ds');
        $this->Earlyout->useDbConfig = $this->Session->read('ds');
        $this->Lateout->useDbConfig = $this->Session->read('ds');
		$emp_fkey = $this->Session->read('emp_fkey');
        $time = date("Y-m-d");
        $from = date('Y-m-1', strtotime($time));
        $to = date('Y-m-t', strtotime($time));
        $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
        // debug($lmonth);
        $ltomonth = date("Y-m-t", strtotime('-1 months', strtotime("now")));
        //  debug($ltomonth);
		$join= "join emp_proff as ep on(ep.emp_fkey = emp_early_in.emp_pkey)";
        switch ($day2) {
            case 'Daily' : $condition = "where emp_early_in.LOGDATE >= '$time' and ep.attr1 = $emp_fkey ";
                break;
            case 'Month' : $condition = "where emp_early_in.LOGDATE between '$from' and '$to' and ep.attr1 = $emp_fkey ";
                break;
            case 'LastMonth' : $condition = "where emp_early_in.LOGDATE between '$lmonth' and '$ltomonth' and ep.attr1 = $emp_fkey ";
                break;
           
            default : echo "No criterias found";
                break;
        }

        switch ($day) {
            case 'Earlyin' : $results = $this->Earlyin->query("select emp_early_in.* from emp_early_in as emp_early_in $join $condition ");
                break;
            case 'Latein' : $results = $this->Latein->query("select emp_early_in.* from emp_late_in as emp_early_in $join $condition ");
                break;
            case 'Earlyout' : $results = $this->Earlyout->query("select emp_early_in.* from emp_early_out as emp_early_in $join $condition");
                break;
            case 'Lateout' : $results = $this->Lateout->query("select emp_early_in.* from emp_late_out as emp_early_in $join $condition ");
                break;
            default : echo "No criterias found";
                break;
        }

        // debug($results);
        $this->set("results", $results);
        $this->render('form');
    }

    public function share($customerId, $recipeId) {
        //action logic goes here..
    }

    public function presenttoday() {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        if($user_group == 2){
            $emp_fkey = $this->Session->read('emp_fkey');
            $results = $this->Lateout->query("select * from present_today where emp_pkey in (select emp_fkey from emp_proff where attr1 ='$emp_fkey' or emp_fkey = '$emp_fkey' ) ");
        }else{
            $results = $this->Lateout->query("select * from present_today");
        }
        // debug($results);
        $this->set("results", $results);
    }
	public function absenttoday($mode = 0) {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $results = $this->Lateout->query("select * from present_today");
        /* server timezone */
$timezone = new DateTimeZone("Asia/Kolkata" );
$date = new DateTime();
$date->setTimezone($timezone );
$dates =   $date->format( 'd-m-Y H:i a' );
        $empsin = array('0');
        foreach ($results as $val)
        {
            $empsin[] = $val['present_today']['emp_pkey'];
        }
        $emps = implode(',', $empsin);
        $user_group = $this->Session->read('user_group');
        if($user_group == 2){
        $emp_fkey = $this->Session->read('emp_fkey');
        $results1 = $this->Lateout->query("select first_name,last_name from emp_details where emp_pkey not in($emps) and emp_pkey in (select emp_fkey from emp_proff where  emp_fkey = '$emp_fkey' or attr1 ='$emp_fkey' )  and status = 1");
        }else{
        $results1 = $this->Lateout->query("select first_name,last_name from emp_details where emp_pkey not in($emps) and status = 1");
        }
        $this->set("results1", $results1);
        if($mode)
        {
            
            
            $content = 
            '<style>
td,th
{
    text-align: left;
}
</style>
                <h2>Absent Employees '.$dates.'</h2><hr>
                ';
            $content .='<div style="text-align:center;">';
            $content .='<table>';
            $content .='<thead>
                              <tr>
                              <th>No</th>
                                  <th>Employee NAME</th>
                                  
                                 
                              
                              </tr>
                            </thead>';
            $content .='<tbody>';
            $content .='';
                      $i = 0;
                      foreach ($results1 as $val) {
                      $i++;      
            $content .='<tr><td>'.$i.'</td><td>'.$val['emp_details']['first_name'].' '.$val['emp_details']['last_name'];
            
            $content .='</td></tr>';
                        }
            $content .='</tbody></table></div>';
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
                try
                    {
                        $html2pdf = new HTML2PDF('P', 'Legal', 'en');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                        $html2pdf->writeHTML($content);
                        $html2pdf->Output('absenttoday.pdf', 'D');
                    }

                    catch(HTML2PDF_exception $e) {
                        echo $e;
                        exit;
                    }
        }
    }
    public function download()
    {
       
    $content = ob_get_clean();
        $view = new View($this, false);
                $view_output = $view->render('download');
                App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' .DS . 'html2pdf.class.php'));
                try
                    {
                        $html2pdf = new HTML2PDF('P', 'Legal', 'en');
                        $html2pdf->pdf->SetDisplayMode('fullpage');
                        $html2pdf->writeHTML($content);
                        $html2pdf->Output('absenttoday.pdf', 'D');
                    }

                    catch(HTML2PDF_exception $e) {
                        echo $e;
                        exit;
                    }
                    $this->render('download');
                //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
                //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                
                
                
    }
    public function search($query) {
        //action logic goes here..
    }

    public function gettodayattendace() {
        $this->autoRender = false;
    }

    public function getMenus() {
        $resp_menu = array();

        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);

        $context = isset($_REQUEST["context"]) ? $_REQUEST["context"] : "main";
        $root = isset($_REQUEST["root"]) ? $_REQUEST["root"] : 0;

        $conditions = array('active = "Y"');
		 $conditionss = array('Useraccess.active = "Y" and EmployeeMenu.active = "Y"');
        if ($context == "sub") {
            $conditions['parent_id'] = $root;
        }
        if ($user_group == 2) {
            /*
             * Employee Menu
             * Added by santhosh on 14 March 2015
             */
             $emp_fkey = $this->Session->read('emp_fkey');       
            $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
   $menudb = $this->EmployeeMenu->find("all",array(
                            'joins'=>array(
                                array(
                                    'table' => 'user_access',
                                    'alias' => 'Useraccess',
                                    'type' => 'INNER',
                                    'conditions' => array(
                                        'EmployeeMenu.menu_id = Useraccess.menu_id'
                                    )
                                )
                            ),
                            'conditions'=>array($conditionss,
                                'Useraccess.user_fkey'=>$emp_fkey
                            ),
							'order' => array('EmployeeMenu.menu_id ASC')
                        )
                    
                );
            
            $menu = array();
            
         //   $menudb = $this->EmployeeMenu->find("all", array("conditions" => $conditions));
        //  debug($menudb);
              /*$arr_mastermenu = array();
                $arr_sitemanagementmenu = array();
                $arr_transmenu = array();
                $arr_reportsmenu = array();
                $arr_securitymenu = array();
                foreach ($menudb as $key => $value){
                    switch ($value['parent_id']) {
                        case 1:
                            $arr_mastermenu[] = $value;
                            break;
                        case 2:
                            $arr_sitemanagementmenu[] = $value;
                            break;
                        case 3:
                            $arr_transmenu[] = $value;
                            break;
                        case 4:
                            $arr_reportsmenu[] = $value;
                            break;
                        case 5:
                            $arr_securitymenu[] = $value;
                            break;
                        default:
                            break;
                    }
                }
                $this->set('arr_mastermenu', $arr_mastermenu);
                $this->set('arr_sitemanagementmenu', $arr_sitemanagementmenu);
                $this->set('arr_transmenu', $arr_transmenu);
                $this->set('arr_reportsmenu', $arr_reportsmenu);
                $this->set('arr_securitymenu', $arr_securitymenu);*/
                
                
                
                
            foreach ($menudb as $key => $value) {
                $m = $value['EmployeeMenu'];
                $arr_menu = array();
                $arr_menu["id"] = $m["menu_id"];
                // $m["menu_url"];
                $arr_menu["url"] = $m["menu_url"];
                $arr_menu["text"] = $m["menu_title"];
                $arr_menu["iconCls"] = $m["iconCls"];
                $arr_menu["leaf"] = true;
                if (isset($menu[$m['parent_id']])) {
                    $menu[$m['parent_id']]["leaf"] = false;
                    $menu[$m['parent_id']]['children'][] = $arr_menu;
                } else {
                    $menu[$m['menu_id']] = $arr_menu;
                }
            }
            //debug($arr_menu);
        } else {
            //Admin menu
            $this->Menu->useDbConfig = $this->Session->read('ds');
            //$this->Menu->recover('tree');
            $menudb = $this->Menu->find("all", array("conditions" => $conditions));

            $menu = array();

            foreach ($menudb as $key => $value) {
                $m = $value['Menu'];
                $arr_menu = array();
                $arr_menu["id"] = ($context == "main") ? $m["menu_id"] : (($m["menu_url"]) ? $m["menu_url"] : "none_" . $m["menu_id"]);
                $arr_menu["url"] = $m["menu_url"];
                $arr_menu["text"] = $m["menu_title"];
                $arr_menu["iconCls"] = isset($m["iconCls"]) ? $m["iconCls"] : "fa fa-user";
                $arr_menu["leaf"] = true;
                // "xf007@FontAwesome";

                if (isset($menu[$m['parent_id']])) {
                    //$menu[$m['parent_id']]["leaf"] = false;
                    $menu[$m['parent_id']]['children'][] = $arr_menu;
                } else {
                    $arr_menu["leaf"] = true;
                    $menu[$m['menu_id']] = $arr_menu;
                }
            }
        }

        //   $resp_menu["success"] = true;
        $i = 0;
        if ($context == "main") {
            foreach ($menu as $key => $value) {
                $resp_menu[$i++] = $value;
            }
        } else {
            foreach ($menu as $key => $value) {
                $resp_menu[$i++] = $value;
            }
        }
        //	debug($resp_menu);
        if ($context == "main") {
            //echo json_encode($resp_menu);
            //debug($resp_menu);
            //$conditions['parent_id'] = $root;
        } else if ($root) {
            //echo json_encode($resp_menu);
            //debug($resp_menu);
        }

        return $resp_menu;
    }

    public function listemployeeleaverequests($emp_fkey = 0) {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $fields = 'LEAVEENTRYID, CONCAT(first_name, " ", last_name) AS emp_name, SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';
        $joins = array(
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey')
            )
        );
        $conditions = array();
        if ($emp_fkey != 0) {
            $conditions = array(
                'OR' => array(
                    array('ISAutherizedby' => $emp_fkey, 'LEAVESTATUS IN("Applied","Authorized")'),
                    array('APPROVEDBY' => $emp_fkey, 'LEAVESTATUS IN ("Authorized","Approved","Rejected")'),
                )
            );
        }

        $arr_empleaverequests = $this->LeaveRequests->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions
        ));
        return $arr_empleaverequests;
    }

    public function listtodayattendance($hierarchy = '') {
        $this->autoRender = FALSE;
        
        $today_cond = array(
            "LOGDATE >=" => date("Y-m-d", strtotime("now")), 
            "DeviceAttendance.status >=" => "Y"
        );
		
        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );
		
        $emp_fkey = $this->Session->read('emp_fkey');
        if($emp_fkey != ''){
            if($hierarchy == 'hierarchy'){                
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                //$today_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey 
                $today_cond[] = "EmployeeDetails.emp_pkey in (select emp_fkey from emp_proff where attr1 = '$emp_fkey' or emp_fkey = '$emp_fkey')  "; 
            }else{
                $today_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
            }
        }
        
        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );
        
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $today_att = $this->DeviceAttendance->find("all", array(
            "order" => "EmployeeDetails.emp_id ASC,DeviceAttendance.LOGDATE ASC",
            "conditions" => $today_cond,
            'joins' => $today_join,
            "fields" => "Branch.branch_name,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location"
                )
        );

        $arr_resp = array(
            'data' => array()
        );

        $i = 0;
        foreach ($today_att as $key => $att) {
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d/m/Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
        }
        return json_encode($arr_resp);
    }
    
    
    public function LocationUpdates($hierarchy = '') {
        $this->autoRender = FALSE;
        
        $this_month_cond = array(
            "LOGDATE >=" => date("Y-m-01", strtotime("now")),
            "LOGDATE <=" => date("Y-m-t", strtotime("now")),
            "DeviceAttendance.status >=" => "Y"
        );
		
        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );
		
        $emp_fkey = $this->Session->read('emp_fkey');
        if($emp_fkey != ''){
            if($hierarchy == 'hierarchy'){                
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                $this_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
            }else{
                $this_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
            }
        }
        
        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );

        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this_month_att = $this->DeviceAttendance->query("select MB.*,concat(UC.first_name, ' ' ,UC.last_name)as Name from mob_user_locations as MB left join user_credentials as UC on(UC.user_id = MB.user_id) where DATE_FORMAT(created_time,'%y-%m-%d') =current_date order by created_time,name desc");

        $arr_resp = array(
            'data' => array()
        );
        $i = 0;
        foreach ($this_month_att as $key => $att) {
           $locations = array_merge($att['MB'],$att['0']);
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($locations['Name']) ? $locations['Name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($locations["location"]) ? $locations["location"] : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($locations['created_time']) ? $locations['created_time'] : '';
            $i++;
        }
        return json_encode($arr_resp);
    }
    
    
    
    public function listthismonthattendance($hierarchy = '') {
        $this->autoRender = FALSE;
        
        $this_month_cond = array(
            "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime("now")),
            "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime("now")),
            "DeviceAttendance.status >=" => "Y"
        );
		
        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );
		
        $emp_fkey = $this->Session->read('emp_fkey');
        if($emp_fkey != ''){
            if($hierarchy == 'hierarchy'){                
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                $this_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
            }else{
                $this_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
            }
        }
        
        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );

        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $this_month_att = $this->DeviceAttendance->find("all", array(
            "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
            "conditions" => $this_month_cond,
            'joins' => $today_join,
            "fields" => "Branch.branch_name,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location"
                )
        );

        $arr_resp = array(
            'data' => array()
        );

        
        $i = 0;
        foreach ($this_month_att as $key => $att) {
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d/m/Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
        }
        return json_encode($arr_resp);
    }

    public function checkpunch($x = 0,$y = 0,$z = 0){
        $arr_form_data = $this->request->data; 
       
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->autoRender = false;
        $this->AttendancePunch->useDbConfig = $this->Session->read('ds');
        $latitude = $y;
        $longitude = $z;
        function getlocation($latitude, $longitude) {
        $geolocation = $latitude . ',' . $longitude;
        //var_dump($longitude);
        $request = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $geolocation . '&sensor=false&key=AIzaSyAtYvL7xbQpcBIRGzO0X_F6tC8lH_skfzY';
        $file_contents = file_get_contents($request);
        $json_decode = json_decode($file_contents);
        // var_dump($json_decode);
        if (isset($json_decode->results[0])) {
        $formatted_address = $json_decode->results[0]->formatted_address;
        } else {
        $formatted_address = '';
        }
        return $formatted_address;
}

        $loc = getlocation($latitude, $longitude);
//        debug($loc);
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
      

            function getRealIpAddr() {
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                return $ip;
            }
//            function getBrowser() {
//                $browser = array("Navigator" => "/Navigator(.*)/i",
//                    "Firefox" => "/Firefox(.*)/i",
//                    "Internet Explorer" => "/MSIE(.*)/i",
//                    "Google Chrome" => "/chrome(.*)/i",
//                    "MAXTHON" => "/MAXTHON(.*)/i",
//                    "Opera" => "/Opera(.*)/i",
//                );
//
//                foreach ($browser as $key => $value) {
//                    $info = array();
//                    $agent = "";
//                    if (preg_match($value, $_SERVER['HTTP_USER_AGENT'])) {
//                        $info = array_merge($info, array("Browser" => $key));
//                        $info = array_merge($info, array(
//                            "Version" => "0"));
//                        break;
//                    } else {
//                        $info = array_merge($info, array("Browser" => "UnKnown"));
//                        $info = array_merge($info, array("Version" => "UnKnown"));
//                    }
//                }
//                return $info['Browser'];
//            }
//            debug($_SERVER['HTTP_USER_AGENT']);
              
//        echo($x);
  function getBrowser() {
  $u_agent = $_SERVER['HTTP_USER_AGENT'];
  $bname = 'Unknown';
  $platform = 'Unknown';
  $version= "";
  // First get the platform?
  if (preg_match('/linux/i', $u_agent)) {
    $platform = 'linux';
  } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
    $platform = 'mac';
  } elseif (preg_match('/windows|win32/i', $u_agent)) {
    $platform = 'windows';
  }
  // Next get the name of the useragent yes seperately and for good reason
  if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
    $bname = 'Internet Explorer';
    $ub = "MSIE";
  } elseif(preg_match('/Firefox/i',$u_agent)) {
    $bname = 'Mozilla Firefox';
    $ub = "Firefox";
  } elseif(preg_match('/Chrome/i',$u_agent)) {
    $bname = 'Google Chrome';
    $ub = "Chrome";
  } elseif(preg_match('/Safari/i',$u_agent)) {
    $bname = 'Apple Safari';
    $ub = "Safari";
  } elseif(preg_match('/Opera/i',$u_agent)) {
    $bname = 'Opera';
    $ub = "Opera";
  } elseif(preg_match('/Netscape/i',$u_agent)) {
    $bname = 'Netscape';
    $ub = "Netscape";
  }
  // finally get the correct version number
  $known = array('Version', $ub, 'other');
  $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
  if (!preg_match_all($pattern, $u_agent, $matches)) {
    // we have no matching number just continue
  }
  // see how many we have
  $i = count($matches['browser']);
  if ($i != 1) {
    //we will have two since we are not using 'other' argument yet
    //see if version is before or after the name
    if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
      $version= $matches['version'][0];
    } else {
      $version= $matches['version'][1];
    }
  } else {
    $version= $matches['version'][0];
  }
  // check if we have a number
  if ($version==null || $version=="") {$version="?";}
//return array(
//  'userAgent' => $u_agent,
//  'name'      => $bname,
//  'version'   => $bname,
//  'platform'  => $platform,
//  'pattern'    => $pattern
//  );
  return $bname;
}
$browser = getBrowser();
$os = php_uname('s');
$host =  php_uname('n');
        if($x ==='1'){
                   $dir = 'in';
               }else{
                   $dir = 'out';
               }
                $auditor_arr = array();
               
                $auditor_arr['emp_fkey'] = $emp_fkey;
                $auditor_arr['direction'] = $dir;
                $auditor_arr['ip_ad'] = getRealIpAddr();
                $auditor_arr['location'] = $loc;
                $auditor_arr['browser'] = $browser;
                $auditor_arr['host_name'] = $host;
                $auditor_arr['os'] = $os;

                $result = $this->AttendancePunch->save($auditor_arr);
                $resp = array();
                $resp["success"] = true;
                $resp["msg"] = "puch success";

                echo json_encode($resp);
            

        
//           
        }
        public function lastpunch() {
            $arr_form_data = $this->request->data;
            $emp_fkey = $this->Session->read('emp_fkey');
            $this->autoRender = false;
            $this->AttendancePunch->useDbConfig = $this->Session->read('ds');
            $arr_lastpuchinfo = $this->AttendancePunch->query("SELECT `last_punch_fn`($emp_fkey) punch");
    //        debug($arr_lastpuchinfo);
            $lastpunch = isset($arr_lastpuchinfo['0']['0']['punch']) ? $arr_lastpuchinfo['0']['0']['punch'] : 'out';
    //        debug($lastpunch);
    //        echo $lastpunch;
            $resp = array();
            if ($lastpunch == 'in') {

                $resp["success"] = true;
    //            $resp["msg"] = "puch success";
            } else {
                $resp["success"] = false;
    //            $resp["msg"] = "puch success";
            }
            echo json_encode($resp);
        }
        
    public function listlastmonthattendance($hierarchy = '') {
        $this->autoRender = FALSE;
        
        $last_month_cond = array(
            "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))), 
            "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))), 
            "DeviceAttendance.status >=" => "Y"
        );
		
        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );
		
        $emp_fkey = $this->Session->read('emp_fkey');
        if($emp_fkey != ''){
            if($hierarchy == 'hierarchy'){                
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                $last_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
            }else{
                $last_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
            }
        }
        
        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );

        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $last_month_att = $this->DeviceAttendance->find("all", array(
            "order" => "EmployeeDetails.emp_id,DeviceAttendance.LOGDATE",
            "conditions" => $last_month_cond,
            'joins' => $today_join,
            "fields" => "Branch.branch_name,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location"
                )
        );

        $arr_resp = array(
            'data' => array()
        );

        $i = 0;
        foreach ($last_month_att as $key => $att) {
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d/m/Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
        }
        return json_encode($arr_resp);
    }

    public function listemployeemisspunches() {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_empmisspunches = $this->EmployeeDetails->query("select ed.emp_id,concat(ed.first_name,' ',ifnull(ed.last_name,'')) as fullname, yearmonth, count(*) misscount
 from emp_detail_timeattandance edt ,emp_details ed where ed.emp_pkey=edt.emp_pkey
and present<>'P/P' and present is not null and date_format(current_date,'%y-%m') =date_format(yearmonth,'%y-%m')
group by edt.emp_pkey,ed.first_name,yearmonth order by 3 desc");
        return $arr_empmisspunches;
    }
	
}
?>