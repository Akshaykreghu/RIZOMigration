<?php

class DashboardController extends AppController
{

    public $layout = "default";
    public $helpers = array('GoogleMap');
    public $name = "Dashboard";
    public $uses = array("EmployeeDetails",'Menu', "Earlyin", "Latein", "Earlyout", "Lateout", "EmployeeProfessionalDetails", "DeviceAttendance", "IssueReport", "LeaveRequests", "EmployeeDetails", "Device", "Useraccess", "AttendancePunch", "MobileUserauditor", "MobileUserTracking", "SettingsRunner", "GeneralSettings", "ReportAudit", "Wish", "EmailContent", "CentralControl"); //Edited by Akshay on 13-6-2024
    public $components = array('DatatablesManagement');
    
    public function index()
    {
        //action logic goes here..
        $user_group = $this->Session->read('user_group'); // edited by anukrishnan 27-01-2025

        if ($this->Session->read('ds') == null) {
            $this->redirect(array('controller' => 'Site', 'action' => 'login'));
        }

        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025

        // Edited by Akshay on 13-2025
        $this->set('user_group', $user_group);
        $this->set('company_code', $company_code);
        // End

        if ($user_group == 2 && $company_code != 'ABSG') { // Edited by Akshay on 25-1-2025
            // if (false) {// Edited by Akshay on 13-1-2025
            //$this->empdashboard();
            $this->Useraccess->useDbConfig = $this->Session->read('ds');
            $emp_fkey = $this->Session->read('emp_fkey');
            $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$emp_fkey' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                //Hierarchy dashboard
                $this->hierarchydashboard();
            } else {
                //Employee dashboard
                 $this->empdashboard();
               // $this->hierarchydashboard();
            }
        } else {
            //Admin dashboard

            // Edited by Akshay on 12-2-2025
            if ($user_group == '2' && ($company_code == '' || $company_code == 'ABSG')) {
                $arr_menus = $this->getMenusForAbs();
            }else{
                $arr_menus = $this->getMenus();
            }
            // $arr_menus = $this->getMenus();
            // End

            $this->set('arr_menu', $arr_menus);

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $count = $this->EmployeeDetails->find("count", array("conditions" => array("emp_id !=" => null, 'status' => 1)));

            // edited by anukrishnan 27-01-2025 open
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $emp_pkey = $this->Session->read('emp_fkey');
                $arr_is_ho = $this->EmployeeDetails->query(
                    "SELECT get_branch_code_abs_fn(:emp_pkey) as branch",
                    ['emp_pkey' => $emp_pkey]
                );
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                $conditions = [];

                if ($is_ho != 1) {
                    $conditions[] = "EmployeeDetails.branch_code = '$is_ho' AND EmployeeDetails.status = 1";
                } else {
                    $conditions = array("EmployeeDetails.status" => 1);
                }

                $count = $this->EmployeeDetails->find('count', [
                    'conditions' => $conditions
                ]);
            }
            // edited by anukrishnan 27-01-2025 close

            $this->set("total_emps", $count);

            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $arr_present = $this->DeviceAttendance->query("select count(*) as presentcount from present_today");
            $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;

            // edited by anukrishnan 27-01-2025 open
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $conditions = [];
                if ($is_ho != 1) {
                    $conditions[] = "e.branch_code = '$is_ho'";
                }
                $where_clause = '';
                if (!empty($conditions)) {
                    $where_clause = 'WHERE ' . implode(' AND ', $conditions);
                }

                $arr_present = $this->DeviceAttendance->query("
                                SELECT COUNT(*) AS presentcount
                                FROM present_today p
                                JOIN emp_details e ON p.emp_pkey = e.emp_pkey
                                $where_clause
                            ");
                $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
            }
            // edited by anukrishnan 27-01-2025 close

            $this->set("present", $present);
            //added by megha on 25/11/2019 presenttodayall list
            $today = date('Y-m-d');

            $arr_presentall = $this->DeviceAttendance->query("select count(*) as presentcount from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
            // debug($arr_presentall);
            $presentall = isset($arr_presentall[0][0]['presentcount']) ? $arr_presentall[0][0]['presentcount'] : 0;
            // edited by anukrishnan 27-01-2025 open
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $conditions = '';
                if ($is_ho != 1) {
                    $conditions = " AND emp_details.branch_code = '$is_ho'";
                }
                $arr_presentall = $this->DeviceAttendance->query("select count(*) as presentcount from present_today_all
                                                                    left join emp_details on present_today_all.emp_pkey = emp_details.emp_pkey
                                                                    where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' $conditions");
                $presentall = isset($arr_presentall[0][0]['presentcount']) ? $arr_presentall[0][0]['presentcount'] : 0;
                // $conditions[] = "STR_TO_DATE(DeviceAttendance.LOGDATE, '%Y-%m-%d') = '$today'";
                // $presentall = $this->DeviceAttendance->find('count', [
                //     'fields' => ['emp_id', 'DATE(DeviceAttendance.LOGDATE) AS logdate', 'COUNT(*) AS count'],
                //     'conditions' => implode(' AND ', $conditions), // Combine conditions as a string
                //     'group' => ['emp_id', 'DATE(DeviceAttendance.LOGDATE)'],
                // ]);
                if ($presentall === false) {
                    $presentall = 0;
                }
            }
            // edited by anukrishnan 27-01-2025 close
            $this->set("presentall", $presentall);

            $arr_empleaverequests = $this->listemployeeleaverequestscounts(0);
            $this->set("arr_empleaverequests", $arr_empleaverequests);

            /**
             * Fetch miss punch count of current months
             * On 04 March 2017
             */
            //            $arr_empmisspunches = $this->listemployeemisspunches();
            //            $this->set("arr_empmisspunches", $arr_empmisspunches); 
            //	//Ends
            //            $today = date('Y-m-d');
            //            $leaves = $this->Latein->query("select count(*) lea from emp_leave_transactions where Leavestatus in ('Approved') and leave_date = '$today' ");
            //            $leaves = isset($leaves['0']['0']['lea'])?$leaves['0']['0']['lea']:0;
            //            $this->set("leaves",$leaves);
        }
        $this->Menu->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-02-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set("plan", $plan);
        //edited by sinsiya on 13-03-2025
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $todaydate = date("Y-m-d");
        $results = $this->Lateout->query("SELECT * FROM present_today_all WHERE DATE(LOGDATE) = '{$todaydate}'"); //edited by anukrishnan_10-02-2025
        /* server timezone */
        $timezone = new DateTimeZone("Asia/Kolkata");
        $date = new DateTime();
        $date->setTimezone($timezone);
        $dates = $date->format('d-m-Y H:i a');
        $empsin = array('0');
        foreach ($results as $val) {
            $empsin[] = $val['present_today_all']['emp_pkey'];
        }
        $emps = implode(',', $empsin);
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $today = $this->Lateout->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
            $empstr = array();
            foreach ($today as $key => $att) {
                $empstr[] = $att['a']['emp_fkey'];
            }
            $str = implode("','", $empstr);
            //$results1 = $this->Lateout->query("select first_name,last_name from emp_details where emp_pkey not in($emps) and emp_pkey in (select emp_fkey from emp_proff where  emp_fkey = '$emp_fkey' or attr1 ='$emp_fkey' )  and status = 1");
           // $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and emp_pkey in ('$str') and status = 1");
            // edited by anukrishnan_ 28-01-2025 open
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 AND emp_details.branch_code = '$is_ho'");
            } else {
                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1");
            }
            // edited by anukrishnan_ 28-01-2025 close
        } else {
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1");
        }
        $res=count($results1);
        $this->set("results1", $res);
        //end
        //edited by athira on 08-08-2025
       
        $user_group=$this->Session->read('user_group');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_name='';
        if($user_group== 2){
            $user=$this->Session->read('emp_fkey');
            $emp_data=$this->EmployeeDetails->query("SELECT EmpName from employee_info where emp_pkey='$user'");
            $emp_name=$emp_data[0]['employee_info']['EmpName'];
        }
        $this->set('emp_name',$emp_name);
        if ($company_code =='GLET'){
        $announcements=$this->EmployeeDetails->query('SELECT * from announcements');
        $this->set('announcements',$announcements);
       }
        
        //end
         //edited by athira on 09-09-2025
        $zoomRegistrationLink = "https://us02web.zoom.us/j/9562628000?omn=87964574191"; // put your registration link
    $nextZoomSession = $this->getNextWednesday(); // function to calculate next Wednesday

    $this->set('zoomRegistrationLink', $zoomRegistrationLink);
    $this->set('nextZoomSession', $nextZoomSession);
    //end
    //  if ($company_code =='VGFS' || $company_code =='VSFS' || $company_code =='ABSG'){
    //  $this->render('index_2');
    //  }
    }
 // Example helper function in the controller:
 
    //edited by athira on 09-09-2025
protected function getNextWednesday() {
    $today = strtotime('today');
    $dayOfWeek = date('N', $today); // 1 = Monday, 7 = Sunday
    $currentHour = date('H'); // 24-hour format, e.g., 13 = 1 PM

    if ($dayOfWeek == 3) { 
        // Today is Wednesday
        if ($currentHour < 12) {
            // Before 12:00 PM -> use today's date
            return date('Y-m-d', $today);
        } else {
            // After 12:00 PM -> get next Wednesday
            $nextWednesday = strtotime('next Wednesday', $today);
            return date('Y-m-d', $nextWednesday);
        }
    } else {
        // For all other days, get next Wednesday
        $nextWednesday = strtotime('next Wednesday', $today);
        return date('Y-m-d', $nextWednesday);
    }
}
//end
    public function menuAudit($menu)
    {
        $this->autoRender = FALSE;
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $dataForHistory = array();
        $dataForHistory['report_type'] = $menu;
        $dataForHistory['mode'] = "Menu";
        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
        $this->ReportAudit->save($dataForHistory);
        echo json_encode(array('success' => 1, 'msg' => $menu));
    }

    public function hierarchydashboard()
    {
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
        //edited by ASHIN on 09-07-24   
        $table_joins[] = array(
            'table' => 'emp_config',
            'alias' => 'EmployeeConfig',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = EmployeeConfig.emp_fkey')
        );
        //End
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        //$count = $this->EmployeeDetails->find("count", array("joins" => $table_joins, "conditions" => array("EmployeeDetails.status" => "1", "EmployeeProfessionalDetails.attr1 = '$emp_fkey' or EmployeeProfessionalDetails.emp_fkey = '$emp_fkey' ")));
        //heirarchy condition
        $today = $this->DeviceAttendance->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
        $emps = array();
        foreach ($today as $key => $att) {
            $emps[] = $att['a']['emp_fkey'];
        }
        $str = implode("','", $emps);
       //edited by ASHIN on 19-07-24
    //edited by sinsiya on 25-11-2024
    $count = $this->EmployeeDetails->find("count", array(
        "joins" => $table_joins,
            "conditions" => array(
             "EmployeeDetails.status" => "1",
                "EmployeeConfig.status" => "1",       
                "EmployeeProfessionalDetails.emp_fkey IN ('$str')",
                "EmployeeProfessionalDetails.attr1" => $emp_fkey,
                "OR" => array(
                    "EmployeeConfig.policy_id" => $emp_fkey,
                    "EmployeeConfig.emp_fkey" => $emp_fkey
                ),
                "EmployeeConfig.type" => 'HIERARCHY'
            )
    ));
//debug( $emp_fkey);
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

        $general_settings = $this->DeviceAttendance->query("SELECT COUNT(*) AS count FROM genaral_setings WHERE when_itis='Employee_Login' AND status=1");

        if ($general_settings[0][0]['count']) {
            $settings_runner = $this->DeviceAttendance->query("select count(*) as count from settings_runner where emp_fkey=$emp_fkey and exit_status IN ('Finished','Never')");
            $this->set("settings_runner", $settings_runner[0][0]['count']);
        }
       // $arr_present = $this->DeviceAttendance->query("select count(*) presentcount from present_today where emp_pkey in ('$str' ) ");
         $arr_present = $this->DeviceAttendance->query("select count(*) presentcount from present_today left join emp_proff on (present_today.emp_pkey = emp_proff.emp_fkey) where emp_pkey in ('$str' ) and emp_proff.attr1= $emp_fkey ");
        $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
        $this->set("present", $present);
        //added by megha on 25/11/2019 presenttodayall list    
        $today = date('Y-m-d');     //edited by ASHIN on 28-06-24
       // $today = date("Y-m-d");
       // debug($user_group);
       // edited by sinsiya on 25-07-2025
        if ($user_group == 2) {
            $emp_fkey = $this->Session->read('emp_fkey');
              $resultsall = $this->DeviceAttendance->query("select count(*) presentcount from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey where present_today_all.emp_pkey in (select emp_fkey from emp_proff where attr1 ='$emp_fkey' ) and STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
          //  $resultsall = $this->Lateout->query("select *, ei.emp_id, ei.EmpName, ei.employee_id from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey where emp_pkey in (select emp_fkey from emp_proff where attr1 ='$emp_fkey' or emp_fkey = '$emp_fkey' ) and STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
        } else {
            //edited by megha on 10-04-2024
            // $resultsall = $this->Lateout->query("select * from present_today_all");
            //Edited by Akshay on 13 -5 2024
            //if ($company_code == 'DEMO') {
                $resultsall = $this->DeviceAttendance->query("select count(*) presentcount from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
//            } else {
//                $resultsall = $this->Lateout->query("select * from present_today_all");
//            }
            //End
            //  . "where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
            //$resultsall = $this->Lateout->query("select *, ei.emp_id, ei.EmpName, ei.employee_id from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey  where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
        }
        // $startdate = $this->Lateout->query("select start_month, end_month  from fin_year where Year_status='OPEN' and vattr1=0 and status = 1 and is_current_finyear='Y'
        // and branch_code = (select branch_code from emp_details where emp_pkey='407')  order by start_month desc limit 1");
        // debug($startdate);
        $presentall = isset($resultsall[0][0]['presentcount']) ? $resultsall[0][0]['presentcount'] : 0;
        $this->set("presentall", $presentall);
       // $arr_presentall = $this->DeviceAttendance->query("select count(*) presentcount from present_today_all where emp_pkey in ('$str' ) and STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
      //  $presentall = isset($arr_presentall[0][0]['presentcount']) ? $arr_presentall[0][0]['presentcount'] : 0;
       // $this->set("presentall", $presentall);

        $present = $this->EmployeeDetails->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($present['0']['user_credentials']['user_id']) ? $present['0']['user_credentials']['user_id'] : '';
        $present_type = $this->DeviceAttendance->query("SELECT punchtype FROM mob_user_credentials WHERE user_id = '$user_id' ");
        $this->set("punch_type", isset($present_type['0']['mob_user_credentials']['punchtype']) ? $present_type['0']['mob_user_credentials']['punchtype'] : '');

        /* $today_join[] = array(
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
          $this->set("last_month_att", $last_month_att); */

        $arr_empleaverequests = $this->listemployeeleaverequests(0);
        $this->set("arr_empleaverequests", $arr_empleaverequests);
        $arr_events = $this->getEvents($emp_fkey);
        $this->set("arr_events", $arr_events);

        $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        $this->set('arr_employees', $arr_employees);
        $table_joins[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        //edited by ASHIN on 09-07-24 
        $conditions = array(
            array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'), 'EmployeeDetails.status' => 1, 'EmployeeConfig.policy_id' => $emp_fkey, 'EmployeeConfig.type' => 'HIERARCHY')
        );

        // debug(  $conditions);

        $conditions1 = array(
            array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'EmployeeDetails.status' => 1, 'EmployeeConfig.policy_id' => $emp_fkey, 'EmployeeConfig.type' => 'HIERARCHY')
        );

        $arr_employees_pics = $this->EmployeeDetails->find("all", array(
            "fields" => array("DISTINCT EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar"),
            "joins" => $table_joins,
            "conditions" => $conditions
        ));
        $this->set('arr_employees_pics', $arr_employees_pics);

        // DEBUG(array(
        //     "fields" => array("EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar"),
        //     "joins" => $table_joins,
        //     "conditions" => $conditions));

        $arr_employees_work = $this->EmployeeDetails->find("all", array(
            "fields" => array(" DISTINCT Empproff.joining_date", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar"),
            "joins" => $table_joins,
            "conditions" => $conditions1 
        ));
        $this->set('arr_employees_work', $arr_employees_work);
        //End
        //Get total number of devices
        $cnt_total_devices = $this->Device->find("count", array("conditions" => array('Device.company_code' => $this->Session->read('company_code'))));
        $this->set('cnt_total_devices', $cnt_total_devices);
        //edited by sinsiya on 17-07-2025
       $this->Lateout->useDbConfig = $this->Session->read('ds');
        $todaydate = date("Y-m-d");
     //   debug($todaydate);
        $results = $this->Lateout->query("SELECT * FROM present_today_all WHERE DATE(LOGDATE) = '{$todaydate}'"); //edited by anukrishnan_10-02-2025
        /* server timezone */
      
        $empsin = array('0');
        foreach ($results as $val) {
            $empsin[] = $val['present_today_all']['emp_pkey'];
        }
     //   debug($empsin);
        $emps = implode(',', $empsin);
        $user_group = $this->Session->read('user_group');
       // debug($user_group);
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025
        if ($user_group == '2' || ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $today = $this->Lateout->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
            $empstr = array();
            foreach ($today as $key => $att) {
                $empstr[] = $att['a']['emp_fkey'];
            }
            $str = implode("','", $empstr);
            //edited by athira on 11-06-2025
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and emp_pkey in ('$str') and status = 1 ORDER BY first_name");
            //end
            // edited by anukrishnan_ 28-01-2025 open
//            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//            $arr_is_ho = $this->EmployeeDetails->query(
//                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
//                ['emp_pkey' => $emp_fkey]
//            );
//            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
//            //edited by athira on 11-06-2025
//            if ($is_ho != 1) {
//                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 AND emp_details.branch_code = '$is_ho' ORDER BY first_name");
//            } else {
//                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 ORDER BY first_name");
//            }
            // edited by anukrishnan_ 28-01-2025 close
        } else {
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 ORDER BY first_name");
        }
        //end
         
        $res=count($results1);
        $this->set("results1", $res);
        $this->render('hierarchydashboard');
    } 

    public function load_birthdays()
    {
        $date = date('Y-m-d');          //edited by ASHIN on 28-06-24
        $table_joins[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins[] = array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins[] = array(
            'table' => 'wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Birthday', 'Wish.date' => $date)
        );
        $table_joins1[] = array(
            'table' => 'user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => 'emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => 'wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Work Anniversary', 'Wish.date' => $date)
        );
        //            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
        //            $this->set('arr_employees', $arr_employees);
        $conditions = array(

            //                array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'), 'status' => 1),
            //                array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1),
            //   array("MOD( DATEDIFF( CURDATE( ) , `EmployeeDetails.date_of_birth`) /30, 12 ) >1 and (((month(`EmployeeDetails.date_of_birth`)) = (month(curdate())) 
            //   and (day(`EmployeeDetails.date_of_birth`)) > (day (curdate() ))) or ((month(`EmployeeDetails.date_of_birth`)) = (month(curdate())+1) and (day(`EmployeeDetails.date_of_birth`)) < (day (curdate() ))))", 'status' => 1)
            array(" DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')
 or DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d') 
 or DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d') or 
 DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d') or 
 DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')", 'status' => 1)
        );
        $conditions1 = array(

            array(" DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')
 or DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d') 
 or DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d') or 
 DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d') or 
 DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d') ", 'status' => 1)

        );
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Latein->useDbConfig = $this->Session->read('ds');
        //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
        $arr_employees_pics = $this->EmployeeDetails->find(
            "all",
            array(
                "fields" => array("Wish.emp_fkey,EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"),
                "joins" => $table_joins,
                "order" => "EmployeeDetails.date_of_birth DESC",
                "conditions" => $conditions
            )
        );
        $arr_employees_pics1 = $this->EmployeeDetails->find(
            "all",
            array(
                "fields" => array("Wish.emp_fkey,EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"),
                "joins" => $table_joins1,
                "order" => "Empproff.joining_date DESC",
                "conditions" => $conditions1
            )
        );

        $arr_reminders = $this->EmployeeDetails->query("select emp_details.first_name,emp_details.last_name,emp_proff.day_time_seq,emp_proff.HOLIDAY_GROUP_ID,emp_proff.LEAVEPOLICY_GROUP_ID,emp_proff.structure_id from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 and (emp_proff.day_time_seq is null or emp_proff.HOLIDAY_GROUP_ID is null or emp_proff.LEAVEPOLICY_GROUP_ID is null or emp_proff.structure_id is null) ");
        $salary_missed = $this->Latein->query("select concat(first_name,' ',ifnull(last_name,'')) name from emp_details where emp_details.status = '1' and emp_pkey not in (select distinct(emp_fkey) from emp_ctc_upload)");

        $today = date('Y-m-d');          //edited by ASHIN on 28-06-24
        $leaves = $this->Latein->query("select count(*) lea from emp_leave_transactions where Leavestatus in ('Approved') and leave_date = '$today' ");
        $leaves = isset($leaves['0']['0']['lea']) ? $leaves['0']['0']['lea'] : 0;
        $this->set("leaves", $leaves);
        $this->set("salary_missed", $salary_missed);
        $this->set("reminders", $arr_reminders);
        $this->set('arr_employees_pics', $arr_employees_pics);
        $this->set('arr_employees_pics1', $arr_employees_pics1);
    }

    public function getEvents($emp_fkey = 0)
    {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $arr_events = $this->LeaveRequests->query("select 'HOL', HOLIDAYNAME,date_format(HOLIDAYDATE,'%M-%d') date_month,emp_fkey from holidays left join emp_proff on (holidays.HOLIDAY_GROUP_ID = emp_proff.HOLIDAY_GROUP_ID) where emp_fkey = '$emp_fkey' and date_format(HOLIDAYDATE,'%m-%d') between date_format(current_date,'%m-%d') and date_format(date_add(current_date,INTERVAL 30 DAY),'%m-%d') and emp_fkey in (select emp_pkey from emp_details where status = 1)
        union
        select 'BIR', first_name,date_format(date_of_birth,'%M-%d') date_month,emp_pkey from emp_details where date_format(date_of_birth,'%m-%d') between date_format(current_date,'%m-%d') and date_format(date_add(current_date,INTERVAL 30 DAY),'%m-%d') and  status = 1
        union
        select 'JOIN', first_name,date_format(joining_date,'%M-%d') date_month,emp_pkey from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where date_format(joining_date,'%m-%d') between date_format(current_date,'%m-%d') and date_format(date_add(current_date,INTERVAL 30 DAY),'%m-%d') and emp_details.status = 1
        order by date_format(date_month,'%m-%d')");
        return $arr_events;
    }

    public function empcalendar()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        $currentyear = date("Y");
        $arr_empevents = $this->EmployeeDetails->query("select emp_proff.joining_date, emp_proff.HOLIDAY_GROUP_ID,emp_proff.emp_fkey,emp_details.date_of_birth from emp_proff 
                                                          left join emp_details on emp_details.emp_pkey = emp_proff.emp_fkey where  emp_proff.emp_fkey = $emp_fkey");
        $holidayid = isset($arr_empevents['0']['emp_proff']['HOLIDAY_GROUP_ID']) ? $arr_empevents['0']['emp_proff']['HOLIDAY_GROUP_ID'] : '0';
        //         debug($arr_empevents);    
        //         $joinindate = isset($arr_empevents['0']['emp_proff']['joining_date']) ? $arr_empevents['0']['emp_proff']['joining_date'] : 0;
        //         $birthday = isset($arr_empevents['0']['emp_details']['date_of_birth']) ? $arr_empevents['0']['emp_details']['date_of_birth'] : 0;
        //         $join = $currentyear.date("-m-d", strtotime($joinindate));
        //         $birth = $currentyear.date("-m-d", strtotime($birthday));
        $arr_holidays = $this->EmployeeDetails->query("select HOLIDAYNAME,HOLIDAYDATE,HOLIDAY_GROUP_ID,HOLIDAYTYPE from holidays where HOLIDAY_GROUP_ID = '$holidayid' and HOLIDAYDATE between '$currentyear-01-01' and '$currentyear-12-31'");
        $holidays = array();
        $data = array();
        foreach ($arr_holidays as $val) {
            $holidays['HOLIDAYID'] = $val['holidays']['HOLIDAYDATE'];
            $holidays['name'] = $val['holidays']['HOLIDAYNAME'];
            $holidays['HOLIDAYTYPE'] = $val['holidays']['HOLIDAYTYPE'];
            $holidays['backgroundColor'] = 'crimson';
            $holidays['borderColor'] = 'crimson';
            //             $holidays['rendering'] = 'background';
            $holidays['start'] = $val['holidays']['HOLIDAYDATE'];
            $holidays['end'] = $val['holidays']['HOLIDAYDATE'];

            $data[] = $holidays;
        }


        $birthday = isset($arr_empevents['0']['emp_details']['date_of_birth']) ? $arr_empevents['0']['emp_details']['date_of_birth'] : 0;
        $birth = $currentyear . date("-m-d", strtotime($birthday));
        $holidays['name'] = 'Your Birthday';
        $holidays['backgroundColor'] = 'yellow';
        $holidays['borderColor'] = 'yellow';
        $holidays['start'] = $birth;
        $holidays['end'] = $birth;
        $data[] = $holidays;

        $joinindate = isset($arr_empevents['0']['emp_proff']['joining_date']) ? $arr_empevents['0']['emp_proff']['joining_date'] : 0;
        $join = $currentyear . date("-m-d", strtotime($joinindate));
        $holidays['name'] = 'Your Work Anniversary';
        $holidays['backgroundColor'] = 'turquoise';
        $holidays['borderColor'] = 'turquoise';
        $holidays['start'] = $join;
        $holidays['end'] = $join;
        $data[] = $holidays;

        //            debug($data);
        echo json_encode($data);
    }

    public function empdashboard()
    {
        //action logic goes here..
        //Employee dashboard
        $emp_fkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');
        $curr_year = date("Y");

        if ($emp_fkey) {

            $arr_menus = $this->getMenus();
            $this->set('arr_menu', $arr_menus);
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $this->AttendancePunch->useDbConfig = $this->Session->read('ds');
            $this->AttendancePunch->query("SELECT `last_punch_fn`($emp_fkey) punch");
            $day = date("Y-m-01");
            $to_days = date("Y-m-t");
            $today = date('Y-m-d');
            $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
            //            debug($yestday);
            //-----birthday and joingday -----//
            $arr_empevents = $this->EmployeeDetails->query("select emp_proff.joining_date, emp_proff.HOLIDAY_GROUP_ID,emp_proff.emp_fkey,emp_details.date_of_birth from emp_proff 
                                                  left join emp_details on emp_details.emp_pkey = emp_proff.emp_fkey where  emp_proff.emp_fkey = $emp_fkey");
            $joinindate = isset($arr_empevents['0']['emp_proff']['joining_date']) ? $arr_empevents['0']['emp_proff']['joining_date'] : 0;
            $birthday = isset($arr_empevents['0']['emp_details']['date_of_birth']) ? $arr_empevents['0']['emp_details']['date_of_birth'] : 0;
            $currentyear = date("Y");
            $join = $currentyear . date("-m-d", strtotime($joinindate));
            $birth = $currentyear . date("-m-d", strtotime($birthday));
            //            debug($join);
            $this->set("join", $join);
            $this->set("birth", $birth);

            //-----total leave count -----//
            //            $arr_yearleavcount = $this->EmployeeDetails->query("SELECT  SUM(`leave_total`)  FROM `attendance_register`WHERE `month_year` between '$curr_year-01' AND '$curr_year-12' AND `emp_fkey` = '$emp_fkey'");
            $arr_yearleavcount = $this->EmployeeDetails->query("SELECT SUM(`leave_days`) FROM `leaveentries` WHERE `EMP_fkey` = '$emp_fkey' AND  
  applied_date between '$curr_year-01-01' and '$curr_year-12-31' and (LEAVESTATUS = 'Approved') or (LEAVESTATUS = 'Authorised')");
            //            debug("SELECT  SUM(`leave_total`)  FROM `attendance_register`WHERE `month_year` between '$curr_year-01' AND '$curr_year-12' AND `emp_fkey` = '$emp_fkey'");
            $yearly_leavecount = isset($arr_yearleavcount['0']['0']['SUM(`leave_days`)']) ? $arr_yearleavcount['0']['0']['SUM(`leave_days`)'] : '0';
            $this->set("yearly_leavecount", $yearly_leavecount);
            //--days count--//

            $working_days_counts = $this->EmployeeDetails->query("select count(*) working_days from emp_detail_timeattandance where emp_pkey = '$emp_fkey' and att_date between '$day' and '$to_days' and weekoff is null and holiday is null and others is null -- leaves != LOP");
            $leaves_taken_count = $this->EmployeeDetails->query("select count(*) working_days from emp_detail_timeattandance where emp_pkey = '$emp_fkey' and att_date between '$day' and '$to_days' and leaves not in ('LOP/LOP') ");
            $present_days_count = $this->EmployeeDetails->query("select sum(a) presents from(select count(*) as a from emp_detail_timeattandance 
where (weekoff is null or holiday is null) and present in ('P/P') and weekoff is null and holiday is null and emp_pkey = '$emp_fkey' and att_date between '$day' and
'$to_days' union all
select count(*)/2 as a from emp_detail_timeattandance where ((weekoff is null) or (holiday is null)) and ((instr(present,'/P') > 0 
or instr(present,'P/') > 0)) and present != 'P/P' and emp_pkey = '$emp_fkey' 
and att_date between '$day' and '$to_days'
)ass 
");
            //                    ("select sum(a) presents from(select count(*) as a from emp_detail_timeattandance where present in ('P/P') and 	emp_pkey = '$emp_fkey'  and (weekoff is null or holiday is null) and att_date between '$day' and '$to_days' union all
            //                                                                select count(*)/2 as a from emp_detail_timeattandance where (instr(present,'/P') > 0 or instr(present,'P/') > 0) and present != 'P/P'  and 	emp_pkey = '$emp_fkey' and (weekoff is null or holiday is null) and att_date between '$day' and '$to_days')ass ");
            //            $lastmonthwrkingdays = $this->EmployeeDetails->query("select count(*) as working_days from emp_detail_timeattandance where emp_pkey = '$emp_fkey' and yearmonth  = '$lmonth' and weekoff is null and holiday is null");
            //            $lastmnthpresnt = $this->EmployeeDetails->query("select sum(a) presents from(select count(*) as a from emp_detail_timeattandance where present in ('P/P') and emp_pkey = '$emp_fkey' and yearmonth  = '$lmonth' union all select count(*)/2 as a from emp_detail_timeattandance where (instr(present,'/P') > 0 or instr(present,'P/') > 0) and present != 'P/P'  and emp_pkey = '$emp_fkey' and yearmonth  = '$lmonth')ass ");
            //            debug($working_days_counts);
            // Emp id -------//           
            $emp = $this->EmployeeDetails->query("select emp_id from emp_details where emp_pkey = $emp_fkey");
            $empid = isset($emp['0']['emp_details']['emp_id']) ? $emp['0']['emp_details']['emp_id'] : '';
            $idd = $this->EmployeeDetails->query("SELECT `employee_id` FROM `employee_info` WHERE `emp_pkey` = '$emp_fkey'");
            $employeeid = isset($idd['0']['employee_info']['employee_id']) ? $idd['0']['employee_info']['employee_id'] : '';
            $this->set("employeeid", $employeeid);
            //   --Miss punch ----//       

            $arr_misspunch = $this->EmployeeDetails->query("select ed.emp_id,concat(ed.first_name,' ',ifnull(ed.last_name,'')) as fullname, yearmonth,att_date,present, count(*) misscount
                                                            from emp_detail_timeattandance edt inner join emp_details ed on ed.emp_pkey=$emp_fkey
                                                            and present<>'P/P' and present is not null and date_format(current_date,'%y-%m') = date_format(yearmonth,'%y-%m') and att_date not in (date_format(current_date,'%y-%m-%d'))  and edt.emp_pkey = $emp_fkey
                                                            group by edt.emp_pkey,att_date order by 3 desc");
            $misspunch = isset($arr_misspunch['0']['0']['misscount']) ? $arr_misspunch['0']['0']['misscount'] : '0';
            //---- Last Punch -----// 

            $lastpunch = $this->EmployeeDetails->query("SELECT LOGDATE,DIRECTION FROM device_attandance where emp_id = $empid ORDER BY LOGDATE DESC  LIMIT 1 ;  ");

            $arr_lastpuchinfo = $this->AttendancePunch->query("SELECT `last_punch_fn`($emp_fkey) punch");
            //            debug($arr_lastpuchinfo);
            $dir = isset($arr_lastpuchinfo['0']['0']['punch']) ? $arr_lastpuchinfo['0']['0']['punch'] : 'out';
            $this->set("dir", $dir);
            //             debug($lastpunch);
            //------Absent Days -----//

            $arr_absentdays = $this->EmployeeDetails->query("select sum(absent)absent from (SELECT count(*) as absent FROM `emp_detail_timeattandance`WHERE `yearmonth` = '$day' AND `emp_pkey` = '$emp_fkey' and att_date between '$day' and '$today' 
                                                            and present is null and weekoff is null and holiday is null 
                                                            union all
                                                            SELECT count(*)*.5 as absent FROM `emp_detail_timeattandance`WHERE `yearmonth` = '$day' AND `emp_pkey` = '$emp_fkey' and att_date between '$day' and '$today' 
                                                            and present in('P/A','A/P') and weekoff is null and holiday is null)m");

            $absentdays = isset($arr_absentdays['0']['0']['absent']) ? $arr_absentdays['0']['0']['absent'] : '0';

            //---- Employee Details ------//   

            $empinfo = $this->EmployeeDetails->query("select branch,designation,department,joining_date from employee_info where emp_pkey = $emp_fkey");

            //-- Shift policy / Session -------//   

            $shiftpolicy = $this->EmployeeDetails->query("select on_dutty1,off_dutty1,off_dutty2 from working_day_time_procedures where day_time_seq in(select day_time_seq from emp_proff where emp_fkey =$emp_fkey)");

            //----Salary Bar Chart -----//

            $arr_salarybarchart = $this->EmployeeDetails->query("SELECT * FROM `payroll_master`WHERE emp_fkey = '$emp_fkey' and action = 'Approved' ORDER BY month_year  DESC LIMIT 7");
            //            debug($arr_salarybarchart);
            $arr_month = array();
            $arr_netsalary = array();
            $arr_grosssalary = array();
            if (!empty($arr_salarybarchart)) {
                foreach ($arr_salarybarchart as $value) {

                    $arr_month[] = date("Y-m", strtotime($value['payroll_master']['month_year']));
                    $arr_netsalary[] = isset($value['payroll_master']['net_salary']) ? $value['payroll_master']['net_salary'] : '0';
                    $arr_grosssalary[] = isset($value['payroll_master']['gross_salary']) ? $value['payroll_master']['gross_salary'] : '0';
                }
                $showbarchart = 'Yes';
            } else {
                $arr_month[] = 0;
                $arr_netsalary[] = 0;
                $arr_grosssalary[] = 0;
                $showbarchart = 'No';
            }
            //            debug($arr_month);
            $month_rev = array_reverse($arr_month);
            $netsalary_rev = array_reverse($arr_netsalary);
            $gross_rev = array_reverse($arr_grosssalary);
            $month = implode("','", $month_rev);
            $netsalary = implode(", ", $netsalary_rev);
            $grosssalary = implode(", ", $gross_rev);
            //            if(empty($month)){
            //                $month = "No Salary To Show";
            //            }
            //            debug($month);
            $this->set("arr_month", $month);
            $this->set("arr_netsalary", $netsalary);
            $this->set("arr_grosssalary", $grosssalary);
            $this->set("showbarchart", $showbarchart);

            //---Donut chart ----//           

            $arr_donutchartdata = $this->EmployeeDetails->query("SELECT presant_total,month_year,weekoff_total,holiday_total,leave_total,lop_total,month_year FROM `attendance_register`WHERE emp_fkey = '$emp_fkey' and isdelete = 'N' ORDER BY month_year  DESC LIMIT 1");
            //            debug($arr_donutchartdata);
            if (!empty($arr_donutchartdata)) {
                $showdonutchart = 'Yes';
            } else {
                $showdonutchart = 'No';
            }
            $lastmonth = isset($arr_donutchartdata['0']['attendance_register']['month_year']) ? $arr_donutchartdata['0']['attendance_register']['month_year'] : '';
            if ($lastmonth == '') {
                $last_date = '';
            } else {
                $last_date = date("F-Y", strtotime($lastmonth));
            }


            $presentdays = isset($arr_donutchartdata['0']['attendance_register']['presant_total']) ? $arr_donutchartdata['0']['attendance_register']['presant_total'] : '0';
            $leavedays = isset($arr_donutchartdata['0']['attendance_register']['leave_total']) ? $arr_donutchartdata['0']['attendance_register']['leave_total'] : '0';
            $lop = isset($arr_donutchartdata['0']['attendance_register']['lop_total']) ? $arr_donutchartdata['0']['attendance_register']['lop_total'] : '0';
            $holidays = isset($arr_donutchartdata['0']['attendance_register']['holiday_total']) ? $arr_donutchartdata['0']['attendance_register']['holiday_total'] : '0';
            $weekoff = isset($arr_donutchartdata['0']['attendance_register']['weekoff_total']) ? $arr_donutchartdata['0']['attendance_register']['weekoff_total'] : '0';
            $calanderdays = $presentdays + $leavedays + $lop + $holidays + $weekoff;
            $workingdays = $presentdays + $leavedays + $lop;
            $this->set("presentdays", $presentdays);
            $this->set("leavedays", $leavedays);
            $this->set("lop", $lop);
            $this->set("holidays", $holidays);
            $this->set("calanderdays", $calanderdays);
            $this->set("workingdays", $workingdays);
            $this->set("weekoff", $weekoff);
            $this->set("showdonutchart", $showdonutchart);
            $this->set("last_date", $last_date);
            $pr_count = isset($present_days_count['0']['0']['presents']) ? round($present_days_count['0']['0']['presents'], 1) : 0.0;
            if ($working_days_counts['0']['0']['working_days'] > 0) {
                $wr_count = $working_days_counts['0']['0']['working_days'];
            } else {
                $wr_count = 1;
            }
            $lea_count = isset($leaves_taken_count['0']['0']['working_days']) ? $leaves_taken_count['0']['0']['working_days'] : 0;

            $percentage_total = 0;
            if ($pr_count != 0) {
                $prsent_percentage = 100 * ($pr_count) / ($wr_count);
                $percentage_total = round($prsent_percentage, 1);
            }
            $this->set("percentage_total", $percentage_total);

            //-----------last month percentage -------------//
            $lastmonthper_total = 0;
            if ($presentdays != 0) {
                $lastmonthper = 100 * $presentdays / $workingdays;
                $lastmonthper_total = round($lastmonthper, 1);
            }
            $this->set("lastmonthper_total", $lastmonthper_total);
            // -------  line Chart ------ //

            $areachartdata = $this->EmployeeDetails->query("SELECT presant_total,leave_total,lop_total,month_year FROM `attendance_register`WHERE emp_fkey = '$emp_fkey' and isdelete = 'N' ORDER BY month_year  DESC LIMIT 7");


            //          debug($areachartdata);
            if (!empty($areachartdata)) {
                foreach ($areachartdata as $value) {

                    $precount[] = isset($value['attendance_register']['presant_total']) ? ($value['attendance_register']['presant_total']) : 0;
                    $leavecount[] = isset($value['attendance_register']['leave_total']) ? $value['attendance_register']['leave_total'] : 0;
                    $lopcount[] = isset($value['attendance_register']['lop_total']) ? $value['attendance_register']['lop_total'] : 0;
                    $area_month[] = isset($value['attendance_register']['month_year']) ? $value['attendance_register']['month_year'] : 0;
                }
                $showlinechart = 'Yes';
            } else {
                $precount[] = 0;
                $leavecount[] = 0;
                $lopcount[] = 0;
                $area_month[] = 0;
                $showlinechart = 'No';
            }
            $wrkcount = array_map(function () {
                return array_sum(func_get_args());
            }, $precount, $leavecount, $lopcount);

            $wrkcount_rev = array_reverse($wrkcount);
            $precount_rev = array_reverse($precount);
            $leavecount_rev = array_reverse($leavecount);
            $lopcount_rev = array_reverse($lopcount);
            $area_month_rev = array_reverse($area_month);
            $wrkdays = implode(", ", $wrkcount_rev);
            $chartmonth = implode("','", $area_month_rev);
            $pdays = implode(", ", $precount_rev);
            $ldays = implode(", ", $leavecount_rev);
            $lopdays = implode(", ", $lopcount_rev);

            //            debug($wrkdays);
            if (empty($chartmonth)) {
                $chartmonth = "No Salary To Show";
            }
            $this->set("chartmonth", $chartmonth);
            $this->set("pdays", $pdays);
            $this->set("ldays", $ldays);
            $this->set("lopdays", $lopdays);
            $this->set("wrkdays", $wrkdays);
            $this->set("showlinechart", $showlinechart);
            //            debug($wrkdays);
            //            debug($lopdays);
            //            
            //            debug($areachart);


            $present = $this->EmployeeDetails->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
            $user_id = isset($present['0']['user_credentials']['user_id']) ? $present['0']['user_credentials']['user_id'] : '';
            $present_type = $this->DeviceAttendance->query("SELECT punchtype FROM mob_user_credentials WHERE user_id = '$user_id' ");
            $this->set("punch_type", isset($present_type['0']['mob_user_credentials']['punchtype']) ? $present_type['0']['mob_user_credentials']['punchtype'] : '');
            /* $today_join[] = array(
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
              $this->set("last_month_att", $last_month_att); */
            $table_joins[] = array(
                'table' => 'user_credentials',
                'alias' => 'USC',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
            );
            $table_joins[] = array(
                'table' => 'emp_proff',
                'alias' => 'Empproff',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
            );
            $arr_employees = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => array('status' => 1))));
            $this->set('arr_employees', $arr_employees);
            $conditions = array(
                //'OR' => array(
                array("DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d')" => date('m-d'), 'status' => 1, 'emp_pkey' => $emp_fkey)
                //array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1),
                //)
            );
            $conditions1 = array(
                array("DATE_FORMAT(Empproff.joining_date,'%m-%d')" => date('m-d'), 'status' => 1, 'emp_pkey' => $emp_fkey)
            );
            //$conditions[] = array("EmployeeDetails.date_of_birth" == date('Y-m-d') or "Empproff.joining_date" == date('Y-m-d'));
            $arr_employees_pics = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.date_of_birth", "EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar"), "joins" => $table_joins, "conditions" => $conditions));
            $this->set('arr_employees_pics', $arr_employees_pics);

            $arr_employees_work = $this->EmployeeDetails->find("all", array("fields" => array("EmployeeDetails.emp_pkey", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date"), "joins" => $table_joins, "conditions" => $conditions1));
            $this->set('arr_employees_work', $arr_employees_work);
            //            $emp_pkeys = array();
            //            foreach ($arr_employees_pics as $val) {
            //                $emp_pkeys[] = $val['EmployeeDetails']['emp_pkey'];
            //            }
            //            $wish = 0;
            //            if (in_array("$emp_fkey", $emp_pkeys)) {
            //                $wish = 1;
            //            }
            //
            //            $this->set("wish", $wish);
            $arr_empleaverequests = $this->listemployeeleaverequests($emp_fkey);
            $this->set("arr_empleaverequests", $arr_empleaverequests);
            $arr_events = $this->getEvents($emp_fkey);
            $this->set("arr_events", $arr_events);
            $this->set("working_days_counts", $working_days_counts);
            $this->set("leaves_taken_count", $leaves_taken_count);
            $this->set("present_days_count", isset($present_days_count) ? round($present_days_count, 1) : 0);
            $this->set("shiftpolicy", $shiftpolicy);
            $this->set("empinfo", $empinfo);
            $this->set("pr_count", $pr_count);
            $this->set("lastpunch", $lastpunch);
            $this->set("absentdays", $absentdays);
            $this->set("misspunch", $misspunch);

            $general_settings = $this->DeviceAttendance->query("SELECT COUNT(*) AS count FROM genaral_setings WHERE when_itis='Employee_Login' AND status=1");
            //debug($general_settings);   exit();            
            if ($general_settings[0][0]['count']) {
                $settings_runner = $this->DeviceAttendance->query("select count(*) as count from settings_runner where emp_fkey=$emp_fkey and exit_status IN ('Finished','Never')");
                $this->set("settings_runner", $settings_runner[0][0]['count']);
            }
            //            debug($misspunch);
            $this->render('empdashboard');
        } else {
            $this->Session->destroy();
            $this->redirect("/");
        }
    }

    public function locationtracking()
    {
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        $to = date("Y-m-d");
        $from_dates = isset($to) ? $to . ' 00:00' : '';
        $to_dates = isset($to) ? $to . ' 23:59' : '';
        $get_user_id = $this->MobileUserTracking->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';
        $arr_location = $this->MobileUserTracking->query("SELECT * FROM `mob_user_tracking` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");
        $this->set("arr_location", $arr_location);
    }

    public function issuereport($issuedate = 0)
    {
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
       // $arr_issues = $this->Earlyin->query("select * from mob_report ");
        //edited by sinsiya on 18-06-2024
        $arr_issues = $this->Earlyin->query("
    SELECT 
        mob_report.*, 
        employee_info.employee_id, 
        employee_info.EmpName,
        branches.branch_name
    FROM 
        mob_report
    INNER JOIN 
        user_credentials ON mob_report.user_id = user_credentials.user_id
    INNER JOIN 
        employee_info ON user_credentials.emp_fkey = employee_info.emp_pkey
    INNER JOIN 
        branches ON employee_info.branch_code = branches.branch_code
    ORDER BY 
        mob_report.mob_report_pkey DESC    
");
        if ($issuedate != '') {
           // $arr_issues = $this->Earlyin->query("select * from mob_report ");
           $arr_issues = $this->Earlyin->query("
    SELECT 
        mob_report.*, 
        employee_info.employee_id, 
        employee_info.EmpName,
        branches.branch_name
    FROM 
        mob_report
    INNER JOIN 
        user_credentials ON mob_report.user_id = user_credentials.user_id
    INNER JOIN 
        employee_info ON user_credentials.emp_fkey = employee_info.emp_pkey
    INNER JOIN 
        branches ON employee_info.branch_code = branches.branch_code
    ORDER BY 
        mob_report.mob_report_pkey DESC    
");
        }
        $arr_resp = array(
            'data' => array()
        );


        $i = 0;
        $j=1;
        foreach ($arr_issues as $key => $att) {
            // debug($att); edited by sinsiya 13-06-2024
            $arr_resp['data'][$i][] = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att['employee_info']['EmpName']) ? $att['employee_info']['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['employee_id'] */ = isset($att['employee_info']['employee_id']) ? $att['employee_info']['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['employee_id'] */ = isset($att['branches']['branch_name']) ? $att['branches']['branch_name'] : '';
           // $date = isset($att['mob_report']['date_rep']) ? $att['mob_report']['date_rep'] : '';
            //$formatted_date = date('d-m-y', strtotime($date));
            $arr_resp['data'][$i][]/* ['date'] */ = isset($att['mob_report']['date_rep']) ? date('d-m-Y H:i:s',strtotime($att['mob_report']['date_rep'])) : '';
            // $arr_resp['data'][$i][]/* ['date'] */ = isset($att['mob_report']['date_rep']) ? $att['mob_report']['date_rep'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att['mob_report']['report_type']) ? $att['mob_report']['report_type'] : '';
//          $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att['mob_report']['user_id']) ? $att['mob_report']['user_id'] : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att['mob_report']['remarks']) ? $att['mob_report']['remarks'] : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att['mob_report']['location']) ? $att['mob_report']['location'] : '';

          

            $i++; 
            $j++;
        }
        return json_encode($arr_resp);
    }

    public function loadmap()
    {
        $this->MobileUserTracking->useDbConfig = $this->Session->read('ds');

        $emp_fkey = $this->Session->read('emp_fkey');
        //        $from = date('Y-m-01');
        $to = date("Y-m-d");
        $from_dates = isset($to) ? $to . ' 00:00' : '';
        $to_dates = isset($to) ? $to . ' 23:59' : '';
        if ($emp_fkey == 0) {
            echo "NO Emp";
            return false;
        }
        $get_user_id = $this->MobileUserTracking->query("SELECT user_id from user_credentials where emp_fkey = '$emp_fkey' ");
        $user_id = isset($get_user_id['0']['user_credentials']['user_id']) ? $get_user_id['0']['user_credentials']['user_id'] : '';

        if ($user_id == '') {
            echo "NO user_id";
            return FALSE;
        }
        $arr_location = $this->MobileUserTracking->query("SELECT * FROM `mob_user_tracking` WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");

        $location = array();
        foreach ($arr_location as $key => $val) {

            $location[] = array($val['mob_user_tracking']['location'], $val['mob_user_tracking']['latitude'], $val['mob_user_tracking']['longitude']);
        }
        //                            debug($location);
        if (!empty($location)) {
            foreach ($location as $value) {
                $lat[] = isset($value['1']) ? $value['1'] : '9.96';
                $long[] = isset($value['2']) ? $value['2'] : '76.28';
                $minlat = min($lat);
                $maxlong = max($long);
            }
        } else {
            $minlat = 9.96;
            $maxlong = 76.28;
        }
        $this->set("minlat", $minlat);
        $this->set("maxlong", $maxlong);

        $arr_location_updates = $this->MobileUserTracking->query("SELECT employee_info.*,mob_user_tracking.* FROM `mob_user_tracking` INNER join employee_info on (employee_info.emp_pkey = $emp_fkey) WHERE `user_id` = '$user_id' and created_time between '$from_dates' and '$to_dates' ");

        $this->set("arr_location", $arr_location_updates);

        $this->set("datas", json_encode($location));
    }

    public function timers()
    {
        $this->autoRender = false;
        $emp_fkey = $this->Session->read('emp_fkey');
        date_default_timezone_set("Asia/Kolkata");
        $this->Earlyin->useDbConfig = $this->Session->read('ds');
        $t = time();
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
        if ($start_date == NULL) {
            $set = 0;
            $t_run = 1;
        } else {
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
            if (empty($arr_session)) {
                $stat = date_create(date($today));
                $en = date_create(date($start_date));
                $diff = date_diff($stat, $en);
                $hours = $diff->h * 60;
                $minuites = $diff->i + $hours;
                $set = $minuites;
                //debug($set);
            } else {
                $timers = array();
                foreach ($arr_session as $key => $value) {
                    if ($value['att']['C1'] == 'in' || $value['att']['C1'] == 'IN') {

                        if (isset($arr_session[$key + 1]['att']['logdate']) && strtolower($arr_session[$key + 1]['att']['C1']) == 'out') {
                            $starter = $arr_session[$key + 1]['att']['logdate'];
                            $ens = $value['att']['logdate'];
                            $stat = date_create(date($starter));
                            $en = date_create(date($ens));
                            $diff = date_diff($stat, $en);
                            $hours = $diff->h * 60;
                            $minuites = $diff->i + $hours;
                            $set = $set + $minuites;
                            $t_run = 0;
                        } else if (!isset($arr_session[$key + 1])) {
                            $ens = $value['att']['logdate'];
                            $stat = date_create(date($today));
                            $en = date_create(date($ens));
                            $diff = date_diff($stat, $en);
                            $hours = $diff->h * 60;
                            $minuites = $diff->i + $hours;
                            $set = $set + $minuites;
                            $t_run = 1;
                        } else {
                            $t_run = '';
                        }
                        //debug($set);
                    }
                }
            }
        }
        $this->set("set", $set);
        echo json_encode(array('set' => $set, 'run' => $t_run));
    }

    public function form($day = '', $day2 = 0)
    {
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
            case 'Daily':
                $condition = "where emp_early_in.LOGDATE >= '$time' ";
                break;
            case 'Month':
                $condition = "where emp_early_in.LOGDATE between '$from' and '$to'";
                break;
            case 'LastMonth':
                $condition = "where emp_early_in.LOGDATE between '$lmonth' and '$ltomonth' ";
                break;

            default:
                echo "No criterias found";
                break;
        }

        switch ($day) {
            case 'Earlyin':
                $results = $this->Earlyin->query("select emp_early_in.* from emp_early_in as emp_early_in $condition ");
                break;
            case 'Latein':
                $results = $this->Latein->query("select emp_early_in.* from emp_late_in as emp_early_in $condition ");
                break;
            case 'Earlyout':
                $results = $this->Earlyout->query("select emp_early_in.* from emp_early_out as emp_early_in $condition");
                break;
            case 'Lateout':
                $results = $this->Lateout->query("select emp_early_in.* from emp_late_out as emp_early_in $condition ");
                break;
            default:
                echo "No criterias found";
                break;
        }

        // debug($results);
        $this->set("results", $results);
        $this->render('form');
    }

    public function empform($day = '', $day2 = 0)
    {
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

        switch ($day2) {
            case 'Daily':
                $condition = "where emp_early_in.LOGDATE >= '$time' ";
                break;
            case 'Month':
                $condition = "where emp_early_in.LOGDATE between '$from' and '$to'";
                break;
            case 'LastMonth':
                $condition = "where emp_early_in.LOGDATE between '$lmonth' and '$ltomonth' ";
                break;

            default:
                echo "No criterias found";
                break;
        }

        switch ($day) {
            case 'Earlyin':
                $results = $this->Earlyin->query("select emp_early_in.* from emp_early_in as emp_early_in $condition  and emp_pkey = $emp_fkey");
                break;
            case 'Latein':
                $results = $this->Latein->query("select emp_early_in.* from emp_late_in as emp_early_in $condition and emp_pkey = $emp_fkey");
                break;
            case 'Earlyout':
                $results = $this->Earlyout->query("select emp_early_in.* from emp_early_out as emp_early_in $condition and emp_pkey = $emp_fkey");
                break;
            case 'Lateout':
                $results = $this->Lateout->query("select emp_early_in.* from emp_late_out as emp_early_in $condition and emp_pkey = $emp_fkey");
                break;
            default:
                echo "No criterias found";
                break;
        }

        // debug($results);
        $this->set("results", $results);
        $this->render('form');
    }

    public function form2($day = '', $day2 = 0)
    {
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
        $join = "join emp_proff as ep on(ep.emp_fkey = emp_early_in.emp_pkey)";
        switch ($day2) {
            case 'Daily':
                $condition = "where emp_early_in.LOGDATE >= '$time' and ep.attr1 = $emp_fkey ";
                break;
            case 'Month':
                $condition = "where emp_early_in.LOGDATE between '$from' and '$to' and ep.attr1 = $emp_fkey ";
                break;
            case 'LastMonth':
                $condition = "where emp_early_in.LOGDATE between '$lmonth' and '$ltomonth' and ep.attr1 = $emp_fkey ";
                break;

            default:
                echo "No criterias found";
                break;
        }

        switch ($day) {
            case 'Earlyin':
                $results = $this->Earlyin->query("select emp_early_in.* from emp_early_in as emp_early_in $join $condition ");
                break;
            case 'Latein':
                $results = $this->Latein->query("select emp_early_in.* from emp_late_in as emp_early_in $join $condition ");
                break;
            case 'Earlyout':
                $results = $this->Earlyout->query("select emp_early_in.* from emp_early_out as emp_early_in $join $condition");
                break;
            case 'Lateout':
                $results = $this->Lateout->query("select emp_early_in.* from emp_late_out as emp_early_in $join $condition ");
                break;
            default:
                echo "No criterias found";
                break;
        }

        // debug($results);
        $this->set("results", $results);
        $this->render('form');
    }

    //The below function is to set Emergency contact number of an employee. By ***ARUL P DAS on 14_8_2020
    public function general_setting()
    {
        $this->SettingsRunner->useDbConfig = $this->Session->read('ds');
        $this->GeneralSettings->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        $time = date("Y-m-d");
        $from = date('Y-m-1', strtotime($time));
        $to = date('Y-m-t', strtotime($time));
        $lmonth = date("Y-m-01", strtotime('-1 months', strtotime("now")));
        // debug($lmonth);
        $ltomonth = date("Y-m-t", strtotime('-1 months', strtotime("now")));
        //  debug($ltomonth);
        $general_settings = $this->GeneralSettings->query("SELECT * FROM genaral_setings WHERE when_itis='Employee_Login' AND status=1");
        $this->set("general_settings", $general_settings);
        $emp_family_details = $general_settings = $this->GeneralSettings->query("SELECT emp_family_pkey,name,relation,contact_number,alternate_number FROM `emp_family` WHERE `emp_fkey` = $emp_fkey AND status=1 ORDER BY `emp_family_pkey`");
        $this->set("family_details", $emp_family_details);
        $this->render('generalsettings');
    }

    //The below function is to save Emergency contact number of an employee. By ***ARUL P DAS on 14_8_2020
    public function save_emergency_contact($family_key, $settings_fkey)
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->SettingsRunner->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array();
        $resp["success"] = false;
        if ($this->SettingsRunner->query("UPDATE emp_family set emergency_contact = 'N' where emp_fkey in('$emp_fkey') ")) {
            $resp["success"] = true;
        }
        if ($this->SettingsRunner->query("UPDATE emp_family set emergency_contact = 'Y' where emp_family_pkey = '$family_key' ")) {
            $resp["success"] = true;
        }

        $exist_check = $this->SettingsRunner->query("select * from settings_runner where emp_fkey=$emp_fkey");
        if (isset($exist_check[0]['settings_runner'])) {
            $updated = $exist_check[0]['settings_runner']['updated_times'] + 1;
            $this->SettingsRunner->query("update settings_runner set exit_status='Finished',updated_times=$updated,modification_date=now() where emp_fkey=$emp_fkey and settings_fkey=$settings_fkey");
            $resp["success"] = true;
        } else {
            $this->SettingsRunner->query("insert into settings_runner(settings_fkey,emp_fkey,exit_status,updated_times) values($settings_fkey,$emp_fkey,'Finished',0)");
            $resp["success"] = true;
        }

        $resp["msg"] = "save success";
        echo json_encode($resp);
    }

    //The below function is to set never remaind Emergency contact number pop up modal. By ***ARUL P DAS on 14_8_2020
    public function never_remind_emergency($settings_fkey)
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->SettingsRunner->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array();
        $resp["success"] = false;
        $exist_check = $this->SettingsRunner->query("select * from settings_runner where emp_fkey=$emp_fkey");
        //        debug($exist_check);
        if (isset($exist_check[0]['settings_runner'])) {
            $updated = $exist_check[0]['settings_runner']['updated_times'] + 1;
            $this->SettingsRunner->query("update settings_runner set exit_status='Never',updated_times=$updated,modification_date=now() where emp_fkey=$emp_fkey and settings_fkey=$settings_fkey");
            $resp["success"] = true;
        } else {
            $this->SettingsRunner->query("insert into settings_runner(settings_fkey,emp_fkey,exit_status,updated_times) values($settings_fkey,$emp_fkey,'Never',0)");
            $resp["success"] = true;
        }
        $resp["msg"] = "save success";
        echo json_encode($resp);
    }

    //The below function is to set remaind later Emergency contact number pop up modal. By ***ARUL P DAS on 14_8_2020
    public function later_remind_emergency($settings_fkey)
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $this->SettingsRunner->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $resp = array();
        $resp["success"] = false;
        $exist_check = $this->SettingsRunner->query("select * from settings_runner where emp_fkey=$emp_fkey");
        if (isset($exist_check[0]['settings_runner'])) {
            $updated = $exist_check[0]['settings_runner']['updated_times'] + 1;
            $this->SettingsRunner->query("update settings_runner set exit_status='Next_time',updated_times=$updated,modification_date=now() where emp_fkey=$emp_fkey and settings_fkey=$settings_fkey");
            $resp["success"] = true;
        } else {
            $this->SettingsRunner->query("insert into settings_runner(settings_fkey,emp_fkey,exit_status,updated_times) values($settings_fkey,$emp_fkey,'Next_time',0)");
            $resp["success"] = true;
        }
        $resp["msg"] = "save success";
        echo json_encode($resp);
    }

    public function share($customerId, $recipeId)
    {
        //action logic goes here..
    }

    //added by megha on 25/11/2019 presenttodayall list
    public function presenttodayall()
    {

        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        $company_code = strtoupper($this->Session->read('company_code')); //Edited by Akshay on 13-5-2024
        $today = date("Y-m-d");
        if ($user_group == 2) {
            $emp_fkey = $this->Session->read('emp_fkey');
              $resultsall = $this->Lateout->query("select *, ei.emp_id, ei.EmpName, ei.employee_id from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey where present_today_all.emp_pkey in (select emp_fkey from emp_proff where attr1 ='$emp_fkey' ) and STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
          //  $resultsall = $this->Lateout->query("select *, ei.emp_id, ei.EmpName, ei.employee_id from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey where emp_pkey in (select emp_fkey from emp_proff where attr1 ='$emp_fkey' or emp_fkey = '$emp_fkey' ) and STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
        } else {
            //edited by megha on 10-04-2024
            // $resultsall = $this->Lateout->query("select * from present_today_all");
            //Edited by Akshay on 13 -5 2024
            //if ($company_code == 'DEMO') {
                $resultsall = $this->Lateout->query("select * from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' ");
//            } else {
//                $resultsall = $this->Lateout->query("select * from present_today_all");
//            }
            //End
            //  . "where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
            //$resultsall = $this->Lateout->query("select *, ei.emp_id, ei.EmpName, ei.employee_id from `present_today_all` left join `employee_info` ei on present_today_all.emp_pkey = ei.emp_pkey  where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
        }
        // $startdate = $this->Lateout->query("select start_month, end_month  from fin_year where Year_status='OPEN' and vattr1=0 and status = 1 and is_current_finyear='Y'
        // and branch_code = (select branch_code from emp_details where emp_pkey='407')  order by start_month desc limit 1");
        // debug($startdate);
        $this->set("presentall", $resultsall);

        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $time = strtotime($date_time);
        $month_year = date("d F Y", $time);
        $this->set('month_year', $month_year);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
    }

    public function presenttoday()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $today = $this->Lateout->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
            $emps = array();
            foreach ($today as $key => $att) {
                $emps[] = $att['a']['emp_fkey'];
            }
            $str = implode("','", $emps);
            //edited by athira on 11-06-2025
            $results = $this->Lateout->query(
                "select *,ei.emp_id ,ei.EmpName,ei.employee_id from present_today left join employee_info ei "
                    . "ON present_today.emp_pkey = ei.emp_pkey where present_today.emp_pkey in ('$str') ORDER BY EmpName"
            );
            //end

            // edited by anukrishnan_28-01-2025 open
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            //edited by athira on 11-06-2025
            if ($is_ho != 1) {
                $results = $this->Lateout->query("select *,ei.emp_id,ei.EmpName,ei.employee_id from `present_today` left join employee_info ei ON present_today.emp_pkey = ei.emp_pkey WHERE ei.branch_code = '$is_ho' ORDER BY EmpName");
            } else {
                $results = $this->Lateout->query("select *,ei.emp_id,ei.EmpName,ei.employee_id from `present_today` left join employee_info ei ON present_today.emp_pkey = ei.emp_pkey ORDER BY EmpName");
            }
            // edited by anukrishnan_28-01-2025 close
        } else {
            $results = $this->Lateout->query("select *,ei.emp_id,ei.EmpName,ei.employee_id from `present_today` left join employee_info ei ON present_today.emp_pkey = ei.emp_pkey ORDER BY EmpName");
        }
        //end

        $this->set("results", $results);
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');
        $time = strtotime($date_time);
        $month_year = date("d F Y", $time);
        $this->set('month_year', $month_year);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
    }

     public function absenttoday($mode = 0)
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $todaydate = date("Y-m-d");
     //   debug($todaydate);
        $results = $this->Lateout->query("SELECT * FROM present_today_all WHERE DATE(LOGDATE) = '{$todaydate}'"); //edited by anukrishnan_10-02-2025
        /* server timezone */
        $timezone = new DateTimeZone("Asia/Kolkata");
        $date = new DateTime();
        $date->setTimezone($timezone);
        $dates = $date->format('d-m-Y H:i a');
        $empsin = array('0');
        foreach ($results as $val) {
            $empsin[] = $val['present_today_all']['emp_pkey'];
        }
     //   debug($empsin);
        $emps = implode(',', $empsin);
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025
        if ($user_group == '2' ) {
            $emp_fkey = $this->Session->read('emp_fkey');
                 $today = $this->Lateout->query("select emp_fkey ,attr1 from (
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' )a   where emp_fkey not in ('$emps') order by 1");
        //     $today = $this->Lateout->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        // select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        // union 
        // select emp_fkey,attr1 from emp_proff where attr1 in 
        // (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        // union 
        // select emp_fkey,attr1 from emp_proff where attr1 in 
        // (select emp_fkey from emp_proff where  attr1 in 
        // (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        // union 
        // select emp_fkey,attr1 from emp_proff where attr1 in 
        // (select emp_fkey from emp_proff where  attr1 in 
        // (select emp_fkey from emp_proff where   attr1 in 
        // (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        // union 
        // select emp_fkey,attr1 from emp_proff where attr1 in 
        // (select emp_fkey from emp_proff where  attr1 in 
        // (select emp_fkey from emp_proff where   attr1 in 
        // (select emp_fkey from emp_proff where attr1 in 
        // (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        // union
        // select emp_fkey,attr1 from emp_proff where attr1 in 
        // (select emp_fkey from emp_proff where  attr1 in 
        // (select emp_fkey from emp_proff where   attr1 in 
        // (select emp_fkey from emp_proff where attr1 in 
        // (select emp_fkey from emp_proff where  attr1 in
        // (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
            $empstr = array();
            foreach ($today as $key => $att) {
                $empstr[] = $att['a']['emp_fkey'];
            }
            $str = implode("','", $empstr);
            
            //edited by athira on 11-06-2025
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and emp_pkey in ('$str') and status = 1 ORDER BY first_name");
            //end
            // edited by anukrishnan_ 28-01-2025 open
//            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//            $arr_is_ho = $this->EmployeeDetails->query(
//                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
//                ['emp_pkey' => $emp_fkey]
//            );
//            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
//            //edited by athira on 11-06-2025
//            if ($is_ho != 1) {
//                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 AND emp_details.branch_code = '$is_ho' ORDER BY first_name");
//            } else {
//                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 ORDER BY first_name");
//            }
            // edited by anukrishnan_ 28-01-2025 close
        } else {
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 ORDER BY first_name");
        }
        //end
        $user_id = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $date_time = date('d-m-Y H:i');

        $time = strtotime($date_time);
        $month_year = date("d F Y", $time);
        $this->set('user_id', $user_id);
        $this->set('date_time', $date_time);
        $this->set('month_year', $month_year);

        $this->set("results1", $results1);
        if ($mode) {


            $content = '<style>
td,th
{
    text-align: left;
}
</style>
                <h2>Absent Employees on ' . $dates . '</h2><hr>
                ';
            $content .= '<div style="text-align:center;">';
            $content .= '<table>';
            $content .= '<thead>
                              <tr>
                              <th>Sl. No.</th>
                                  <th>Employee Name</th>
                              </tr>
                            </thead>';
            $content .= '<tbody>';
            $content .= '';
            $i = 0;
            foreach ($results1 as $val) {
                $i++;
                $content .= '<tr><td>' . $i . '</td><td>' . $val['emp_details']['first_name'] . ' ' . $val['emp_details']['last_name'] . ' - ' . $val['emp_proff']['emp_company_id'];

                $content .= '</td></tr>';
            }
            $content .= '</tbody></table></div>';
            App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
            try {
                $html2pdf = new HTML2PDF('P', 'Legal', 'en');
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
                $html2pdf->writeHTML($content);
                $html2pdf->Output('absenttoday.pdf', 'D');
            } catch (HTML2PDF_exception $e) {
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
        App::import('Vendor', 'HTML2PDF', array('file' => 'html2pdf_v4.03' . DS . 'html2pdf.class.php'));
        try {
            $html2pdf = new HTML2PDF('P', 'Legal', 'en');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($content);
            $html2pdf->Output('absenttoday.pdf', 'D');
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
        $this->render('download');
        //$html2pdf->addFont('inherit', '', getcwd().'/fonts/glyphicons-halflings-regular.ttf');
        //$html2pdf->pdf->SetFont('times', 'BI', 20, '', 'false');
    }

    public function search($query)
    {
        //action logic goes here..
    }

    public function gettodayattendace()
    {
        $this->autoRender = false;
    }

    public function getMenus()
    {
        $resp_menu = array();

        $user_group = $this->Session->read('user_group');
        $this->set('user_group', $user_group);

        $context = isset($_REQUEST["context"]) ? $_REQUEST["context"] : "main";
        $root = isset($_REQUEST["root"]) ? $_REQUEST["root"] : 0;

        $conditions = array('active = "Y" ');
        $conditionss = array('Useraccess.active = "Y" and EmployeeMenu.active = "Y" and EmployeeMenu.is_default != "M"');
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
            $menudb = $this->EmployeeMenu->find(
                "all",
                array(
                    'joins' => array(
                        array(
                            'table' => 'user_access',
                            'alias' => 'Useraccess',
                            'type' => 'INNER',
                            'conditions' => array(
                                'EmployeeMenu.menu_id = Useraccess.menu_id'
                            )
                        )
                    ),
                    'conditions' => array(
                        $conditionss,
                        'Useraccess.user_fkey' => $emp_fkey
                    ),
                    'order' => array('EmployeeMenu.menu_id ASC')
                )
            );

            $menu = array();
            //debug($menudb); 
            //   $menudb = $this->EmployeeMenu->find("all", array("conditions" => $conditions));
            //  debug($menudb);
            /* $arr_mastermenu = array();
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
              $this->set('arr_securitymenu', $arr_securitymenu); */




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
            
        } else {
            //Admin menu
            $this->Menu->useDbConfig = $this->Session->read('ds');
            //edited by athira on 11-06-2025
            try {
            $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
            $plan = $plan['0']['comp_contact_info']['plan'];
            //$this->Menu->recover('tree');
            if ($plan == 'basic') {
                $conditions['Menu.plan'] = 'basic';
            } else {
                $menudb = $this->Menu->find("all", array("conditions" => $conditions));
            }
           } catch (Exception $ex) {
            }
            $menudb = $this->Menu->find("all", array("conditions" => $conditions));

            //end

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

    public function listemployeeleaverequestscounts($emp_fkey = 0)
    {

        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $fields = 'count(*) as counts';
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
        return isset($arr_empleaverequests) ? $arr_empleaverequests['0']['0']['counts'] : 0;
    }

    public function misspunch($emp_fkey = 0)
    {
        $arr_empmisspunches = $this->listemployeemisspunches();
       // debug($arr_empmisspunches);exit;
        //            $this->set("arr_empmisspunches", $arr_empmisspunches);
        $this->set("results", $arr_empmisspunches);
    }

    public function listemployeeleaverequests($emp_fkey = 0)
    {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        //edited by sinsiya to join the table employee_info 
        $fields = 'LEAVEENTRYID, CONCAT(first_name, " ", last_name) AS emp_name,emp_info.employee_id,Branch.branch_name,SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';
       
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
            ),
             array(
                'table' => 'employee_info',
                'alias' => 'emp_info',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.EMP_fkey = emp_info.emp_pkey')
            ),
            array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
              )
            
        );
        $month = date("Y-m");
        $conditions = array(" FROMDATE >= '$month' ");
        if ($emp_fkey != 0) {
            $conditions = array(
                'OR' => array(
                    array('ISAutherizedby' => $emp_fkey, 'LEAVESTATUS IN("Applied","Authorized")'),
                    array('APPROVEDBY' => $emp_fkey, 'LEAVESTATUS IN ("Authorized","Approved","Rejected")'),
                )
            );
        }
//edited by sinsiya on 18-06-2024 for getting latest records
        $arr_empleaverequests = $this->LeaveRequests->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'order' => ['LEAVEENTRYID' => 'DESC']

        ));
        return $arr_empleaverequests;
    }

    public function todayattendance()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
    }

    public function lastmonthattendanceinfo()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
    }

    public function thismonthattendanceinfo()
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
    }

    public function listtodayattendance($hierarchy = '')
    {
        $this->autoRender = FALSE;
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        if ($company_code == 'BKHS') {
            $today_cond = array(
                "LOGDATE >=" => date("Y-m-d", strtotime("now")),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", "", "SIT"),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        } else {
            $today_cond = array(
                "LOGDATE >=" => date("Y-m-d", strtotime("now")),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", ""),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        }

        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );

        $emp_fkey = $this->Session->read('emp_fkey');
        if ($emp_fkey != '') {
            if ($hierarchy == 'hierarchy') {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                //heirarchy condition
                //$today_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey 
        //edited by athira on 10-09-2025
             if ($company_code == 'INFR') {
                         $today = $this->DeviceAttendance->query("SELECT emp_fkey, attr1 
FROM emp_proff 
LEFT JOIN emp_details 
    ON emp_details.emp_pkey = emp_proff.emp_fkey
WHERE emp_proff.attr1 = '$emp_fkey' 
");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['emp_proff']['emp_fkey'];
                }
                 $emps[] = $emp_fkey;
            }else{
                        $today = $this->DeviceAttendance->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['a']['emp_fkey'];
                }
            }
                //end
                $str = implode("','", $emps);
                $today_cond[] = "EmployeeDetails.emp_pkey in ('$str')";
                //$today_cond[] = "EmployeeDetails.emp_pkey in (select emp_fkey from emp_proff where attr1 = '$emp_fkey' or emp_fkey = '$emp_fkey') or EmployeeDetails.emp_pkey in ($str))  ";
            } else {
                $today_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
            }
        }
        //edited by sinsiya on 13-06-2024
        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );
        $today_join[] = array(
            'table' => 'employee_info',
            'alias' => 'emp_info',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = emp_info.emp_pkey')
        );


        $today_att = $this->DeviceAttendance->find(
            "all",
            array(
                "order" => "EmployeeDetails.emp_id ASC,DeviceAttendance.LOGDATE ASC,DeviceAttendance.device_attandance_seq",
                "conditions" => $today_cond,
                'joins' => $today_join,
                "fields" => "Branch.branch_name,emp_info.employee_id,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
--  if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location
 COALESCE(DeviceAttendance.C3,Branch.branch_name) AS Location "
            )
        );
        //  DEBUG($today_att);
        $arr_resp = array();
        $i = 0;
        //edited by sinsiya on 13-06-2024
        $j = 1;
        foreach ($today_att as $key => $att) {
            //    debug($att); 
            //edited by ASHIN on 05-07-24    
            $arr_resp['data'][$i][]/* ['SL NO'] */ = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["emp_info"]['employee_id']) ? $att["emp_info"]['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['Branch']  */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d-m-Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            //$arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i:s", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */      = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location']*/ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
            $j++;
        }
        //debug($arr_resp);
        return json_encode($arr_resp);
    }

   public function LocationUpdates($hierarchy = '')
    {
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
        if ($emp_fkey != '') {
            if ($hierarchy == 'hierarchy') {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                $this_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
            } else {
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
     //edited by sinsiya on 13-06-2024 removed order by MB.created_time, Name DESC,
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
      $this_month_att = $this->DeviceAttendance->query("select MB.*,concat(UC.first_name, ' ' ,UC.last_name)as Name,emp_info.branch,
       emp_info.employee_id from mob_user_locations as MB "
              . "left join user_credentials as UC on(UC.user_id = MB.user_id) 
              LEFT JOIN employee_info AS emp_info ON (UC.emp_fkey = emp_info.emp_pkey)
              where DATE_FORMAT(created_time,'%y-%m-%d') =current_date order by MB.mob_user_locations_pkey desc");
//$this_month_att = $this->DeviceAttendance->query("
//    SELECT
//        MB.*,
//        CONCAT(UC.first_name, ' ', UC.last_name) AS Name,
//        Branch.branch_name,
//        emp_info.employee_id
//    FROM
//        mob_user_locations AS MB
//    LEFT JOIN
//        user_credentials AS UC ON (UC.user_id = MB.user_id)
//    LEFT JOIN
//        employee_info AS emp_info ON (UC.emp_fkey = emp_info.emp_pkey)
//    LEFT JOIN
//        branches AS Branch ON (emp_info.branch_code = Branch.branch_code)
//    WHERE
//        DATE_FORMAT(MB.created_time,'%y-%m-%d') = CURRENT_DATE
//    ORDER BY
//     MB.mob_user_locations_pkey DESC
//");
        $arr_resp = array(
            'data' => array()
        );
        $i = 0;
        $j=1;
        //edited by sinsiya on 13-06-2024
        foreach ($this_month_att as $key => $att) {
            $locations = array_merge($att['MB'], $att['0']);
            $arr_resp['data'][$i][]/* ['SLNO'] */ = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($locations['Name']) ? $locations['Name'] : '';
            $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["emp_info"]['employee_id']) ? $att["emp_info"]['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["emp_info"]['branch']) ? $att["emp_info"]['branch'] : '';

//edited by ASHIN on 07-07-24        
         // $arr_resp['data'][$i][]/* ['ACTION'] */ = isset($locations["stepinout"]) ? $locations["stepinout"] : '';
         $arr_resp['data'][$i][]/* ['ACTION'] */ = isset($locations["stepinout"]) ?
             (strtoupper($locations["stepinout"]) == 'IN' ? 'STEP IN' :
             (strtoupper($locations["stepinout"]) == 'OUT' ? 'STEP OUT' :
             ($locations["stepinout"] == 'START FROM' ? 'START' : ''))) : '';

            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($locations['created_time']) ? date('d-m-Y H:i:s',strtotime($locations['created_time'])) : '';    //edited by ASHIN on 02-07-24

            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($locations["location"]) ? $locations["location"] : '';
    

        
            $i++;
            $j++;
        }
        return json_encode($arr_resp);
        //debug($arr_resp);
    }
   // edited by sinsiya on 03-07-2024
   public function EmployeeEvent()
    {
        
 $this->autoRender = FALSE;
 $date = date('Y-m-d');
 $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
// Define table joins
//$table_joins = array(
//    array(
//        'table' => 'user_credentials',
//        'alias' => 'USC',
//        'type' => 'LEFT',
//        'foreignKey' => false,
//        'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
//    ),
//    array(
//        'table' => 'emp_proff',
//        'alias' => 'Empproff',
//        'type' => 'LEFT',
//        'foreignKey' => false,
//        'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
//    ),
//    array(
//        'table' => 'branches',
//        'alias' => 'branch',
//        'type' => 'LEFT',
//        'foreignKey' => false,
//        'conditions' => array('EmployeeDetails.branch_code = branch.branch_code')
//    ),
//    array(
//        'table' => 'employee_info',
//        'alias' => 'emp_info',
//        'type' => 'LEFT',
//        'foreignKey' => false,
//        'conditions' => array('EmployeeDetails.emp_pkey = emp_info.emp_pkey')
//    )
//);

// Define conditions for birthdays and work anniversaries
//$conditions = array(
//    array(
//        "OR" => array(
//            "DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')",
//            "DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d')",
//            "DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d')",
//            "DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d')",
//            "DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')"
//        ),
//        'EmployeeDetails.status' => 1
//    )
//);
//
//$conditions1 = array(
//    array(
//        "OR" => array(
//            "DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')",
//            "DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d')",
//            "DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d')",
//            "DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d')",
//            "DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')"
//        ),
//        'EmployeeDetails.status' => 1
//    )
//);



// Find employees with upcoming birthdays
//$birthday_employees = $this->EmployeeDetails->find(
//    "all",
//    array(
//        "fields" => array(
//            "EmployeeDetails.date_of_birth",
//            "emp_info.employee_id",
//            "EmployeeDetails.emp_name",
//            "branch.branch_name",
//            "USC.avatar",
//            "'Birthday' AS event_type", // Add this field to identify event type
//            "NULL AS joining_date" // Placeholder for joining_date to match the anniversary query
//        ),
//        "joins" => $table_joins,
//        "order" => array(
//            "EmployeeDetails.date_of_birth DESC"
//        ),
//        "conditions" => $conditions
//    )
//);
//
$birthday_employees = $this->EmployeeDetails->query("
    SELECT 
       DISTINCT `EmployeeDetails`.`date_of_birth`, 
        `emp_info`.`employee_id`,
        Wish.emp_fkey,
        EmployeeDetails.emp_pkey,
        CONCAT(EmployeeDetails.first_name, ' ', EmployeeDetails.last_name) AS emp_name,
        `branch`.`branch_name`, 
        `USC`.`avatar`, 
        'Birthday' AS `event_type`, 
        NULL AS `joining_date`
    FROM 
        `emp_details` AS `EmployeeDetails`
    LEFT JOIN 
        `user_credentials` AS `USC` ON (`EmployeeDetails`.`emp_pkey` = `USC`.`emp_fkey`)
    LEFT JOIN 
        `emp_proff` AS `Empproff` ON (`EmployeeDetails`.`emp_pkey` = `Empproff`.`emp_fkey`)
    LEFT JOIN 
        `branches` AS `branch` ON (`EmployeeDetails`.`branch_code` = `branch`.`branch_code`)
    LEFT JOIN 
        `employee_info` AS `emp_info` ON (`EmployeeDetails`.`emp_pkey` = `emp_info`.`emp_pkey`)
    LEFT JOIN 
        `wishes` AS `Wish` ON (`Wish`.`emp_fkey` = `Empproff`.`emp_fkey` AND `Wish`.`type` = 'Birthday' AND `Wish`.`date` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
    WHERE 
        DATE_FORMAT(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(`EmployeeDetails`.`date_of_birth`, '%m-%d')), '%Y-%m-%d')
        BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        AND `EmployeeDetails`.`status` = 1
     GROUP BY 
        EmployeeDetails.emp_pkey   
    ORDER BY 
        DATE_FORMAT(`EmployeeDetails`.`date_of_birth`, '%m-%d') ASC
");


//debug($birthday_employees);
//// Find employees with upcoming work anniversaries
//$anniversary_employees = $this->EmployeeDetails->find(
//    "all",
//    array(
//        "fields" => array(
//            "NULL AS date_of_birth", // Placeholder for date_of_birth to match the birthday query
//            "emp_info.employee_id",
//            "EmployeeDetails.emp_name",
//            "branch.branch_name",
//            "USC.avatar",
//            "Empproff.joining_date",
//            "'Anniversary' AS event_type" // Add this field to identify event type
//        ),
//        "joins" => $table_joins,
//        "order" => array(
//            "Empproff.joining_date DESC"
//        ),
//        "conditions" => $conditions1
//    )
//);
// $anniversary_employees = $this->EmployeeDetails->query("SELECT 
//     DISTINCT NULL AS `date_of_birth`, 
//     `emp_info`.`employee_id`,
//     Wish.emp_fkey,
//     EmployeeDetails.emp_pkey,
//     CONCAT(EmployeeDetails.first_name, ' ', EmployeeDetails.last_name) AS emp_name,
//     `branch`.`branch_name`, 
//     `USC`.`avatar`, 
//     `Empproff`.`joining_date`, 
//     'Anniversary' AS `event_type`
// FROM 
//     `emp_details` AS `EmployeeDetails`
// LEFT JOIN 
//     `user_credentials` AS `USC` ON (`EmployeeDetails`.`emp_pkey` = `USC`.`emp_fkey`)
// LEFT JOIN 
//     `emp_proff` AS `Empproff` ON (`EmployeeDetails`.`emp_pkey` = `Empproff`.`emp_fkey`)
// LEFT JOIN 
//    `branches` AS `branch` ON (`EmployeeDetails`.`branch_code` = `branch`.`branch_code`)
// LEFT JOIN 
//     `employee_info` AS `emp_info` ON (`EmployeeDetails`.`emp_pkey` = `emp_info`.`emp_pkey`)
// LEFT JOIN 
//      `wishes` AS `Wish` ON (`Wish`.`emp_fkey` = `Empproff`.`emp_fkey` AND `Wish`.`type` = 'Work Anniversary' AND `Wish`.`date` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
// WHERE 
//     (
//         (
//             DATE_FORMAT(`Empproff`.`joining_date`, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +30 DAY), '%m-%d')
//             OR DATE_FORMAT(`Empproff`.`joining_date`, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +31 DAY), '%m-%d')
//             OR DATE_FORMAT(`Empproff`.`joining_date`, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +7 DAY), '%m-%d')
//             OR DATE_FORMAT(`Empproff`.`joining_date`, '%m-%d') = DATE_FORMAT(DATE_ADD(curdate(), INTERVAL +2 DAY), '%m-%d')
//             OR DATE_FORMAT(`Empproff`.`joining_date`, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')
//         )
//     )
//     AND (`EmployeeDetails`.`status` = 1)
//     GROUP BY 
//         EmployeeDetails.emp_pkey
//     ORDER BY 
//         DATE_FORMAT(`Empproff`.`joining_date`, '%m-%d') ASC");

        //edited by athira on 21-04-2025

        $anniversary_employees = $this->EmployeeDetails->query("SELECT 
             NULL AS `date_of_birth`, 
            `emp_info`.`employee_id`,
            Wish.emp_fkey,
            EmployeeDetails.emp_pkey,
            CONCAT(EmployeeDetails.first_name, ' ', EmployeeDetails.last_name) AS emp_name,
            `branch`.`branch_name`, 
            `USC`.`avatar`, 
            `Empproff`.`joining_date`, 
            'Anniversary' AS `event_type`
        FROM 
            `emp_details` AS `EmployeeDetails`
        LEFT JOIN 
            `user_credentials` AS `USC` ON (`EmployeeDetails`.`emp_pkey` = `USC`.`emp_fkey`)
        LEFT JOIN 
            `emp_proff` AS `Empproff` ON (`EmployeeDetails`.`emp_pkey` = `Empproff`.`emp_fkey`)
        LEFT JOIN 
        `branches` AS `branch` ON (`EmployeeDetails`.`branch_code` = `branch`.`branch_code`)
        LEFT JOIN 
            `employee_info` AS `emp_info` ON (`EmployeeDetails`.`emp_pkey` = `emp_info`.`emp_pkey`)
        LEFT JOIN 
            `wishes` AS `Wish` ON (`Wish`.`emp_fkey` = `Empproff`.`emp_fkey` AND `Wish`.`type` = 'Work Anniversary' AND `Wish`.`date` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
        WHERE 
            DATE_FORMAT(`Empproff`.`joining_date`, '%m-%d') BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') 
            AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), '%m-%d')
            AND YEAR(`Empproff`.`joining_date`) < YEAR(CURDATE())
            AND `EmployeeDetails`.`status` = 1 ORDER BY
                DATE_FORMAT(`Empproff`.`joining_date`, '%m-%d') ASC");

       //end
//debug($anniversary_employees);
// Combine the two result sets
$arr_employees_pics = array_merge($birthday_employees, $anniversary_employees);
//debug($arr_employees_pics);
$arr_resp = array('data' => array());
$i = 1;

// Populate response array
foreach ($arr_employees_pics as $att) {
    
    $row_data = array();
    $row_data[] = $i; // SLNO
    $avatarPath = isset($att['USC']['avatar']) ? $this->webroot . '' . $att['USC']['avatar'] : $this->webroot . 'img/avatar5.png';
    $imageHTML = '<img src="' . $avatarPath . '" style="width: 28px; margin-left: 20px; border-radius: 5px;">';
    $row_data[] = $imageHTML; // User Image
    $row_data[] = isset($att[0]["emp_name"]) &&!empty($att[0]["emp_name"])? $att[0]["emp_name"] : ''; // EmpName
    $row_data[] = isset($att["emp_info"]['employee_id']) &&!empty($att["emp_info"]['employee_id'])? $att["emp_info"]['employee_id'] : ''; // EmpID
    $row_data[] = isset($att["branch"]['branch_name']) &&!empty($att["branch"]['branch_name'])? $att["branch"]['branch_name'] : ''; // Branch
    $row_data[] = isset($att[0]['event_type']) &&!empty($att[0]['event_type'])? $att[0]['event_type'] : ''; // DOJ (event type)
//    if (isset($att[0]['event_type']) && $att[0]['event_type'] == 'Birthday') {
//        $row_data[] = isset($att['EmployeeDetails']['date_of_birth']) &&!empty($att['EmployeeDetails']['date_of_birth'])? $att['EmployeeDetails']['date_of_birth'] : ''; // DOB
//    } elseif (isset($att[0]['event_type']) && $att[0]['event_type'] == 'Anniversary'){
//        $row_data[] = isset($att['Empproff']['joining_date']) &&!empty($att['Empproff']['joining_date'])? $att['Empproff']['joining_date'] : ''; // Joining Date
//    }
    if (isset($att[0]['event_type']) && $att[0]['event_type'] == 'Birthday') {
    if (isset($att['EmployeeDetails']['date_of_birth']) && !empty($att['EmployeeDetails']['date_of_birth'])) {
        $date_of_birth = new DateTime($att['EmployeeDetails']['date_of_birth']);
        $date_of_birth->setDate(date('Y'), $date_of_birth->format('m'), $date_of_birth->format('d'));
        $row_data[] = $date_of_birth->format('d-m-Y'); // DOB with current year
    } else {
        $row_data[] = ''; // DOB not available
    }
} elseif (isset($att[0]['event_type']) && $att[0]['event_type'] == 'Anniversary') {
    if (isset($att['Empproff']['joining_date']) && !empty($att['Empproff']['joining_date'])) {
        $joining_date = new DateTime($att['Empproff']['joining_date']);
        $joining_date->setDate(date('Y'), $joining_date->format('m'), $joining_date->format('d'));
        $row_data[] = $joining_date->format('d-m-Y'); // Joining Date with current year
    } else {
        $row_data[] = ''; // Joining Date not available
    }
}
// Add the appropriate button based on the event type
$button = '';
if (isset($att[0]['event_type'])) {
    $eventType = $att[0]['event_type'];
    $emp = isset($att['Wish']['emp_fkey']) ? $att['Wish']['emp_fkey'] : 0;

    if ($eventType == 'Anniversary') {
        if ($emp == 0) {
            $button = '<button class="btn btn-primary" onclick="openWishModal(\'' . $att['EmployeeDetails']['emp_pkey'] . '\', \'Work Anniversary\')">Wish</button>';
        } else {
            $button = '<button class="btn btn-success" disabled>Wished</button>';
        }
    } elseif ($eventType == 'Birthday') {
        if ($emp == 0) {
            $button = '<button class="btn btn-primary" onclick="openWishModal(\'' . $att['EmployeeDetails']['emp_pkey'] . '\', \'Birthday\')">Wish</button>';
        } else {
            $button = '<button class="btn btn-success" disabled>Wished</button>';
        }
    }
}

$row_data[] = $button; // Action
$arr_resp['data'][] = $row_data;
  $i++;  
}

//debug($arr_resp);
return json_encode($arr_resp);
       // $arr_reminders = $this->EmployeeDetails->query("select emp_details.first_name,emp_details.last_name,emp_proff.day_time_seq,emp_proff.HOLIDAY_GROUP_ID,emp_proff.LEAVEPOLICY_GROUP_ID,emp_proff.structure_id from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where emp_details.status = 1 and (emp_proff.day_time_seq is null or emp_proff.HOLIDAY_GROUP_ID is null or emp_proff.LEAVEPOLICY_GROUP_ID is null or emp_proff.structure_id is null) ");
       // $salary_missed = $this->Latein->query("select concat(first_name,' ',ifnull(last_name,'')) name from emp_details where emp_details.status = '1' and emp_pkey not in (select distinct(emp_fkey) from emp_ctc_upload)");

        //$today = date('Y-m-d');
        //$leaves = $this->Latein->query("select count(*) lea from emp_leave_transactions where Leavestatus in ('Approved') and leave_date = '$today' ");
        //$leaves = isset($leaves['0']['0']['lea']) ? $leaves['0']['0']['lea'] : 0;
        //$this->set("leaves", $leaves);
        //$this->set("salary_missed", $salary_missed);
        //$this->set("reminders", $arr_reminders);
        //$this->set('arr_employees_pics', $arr_employees_pics);
        //$this->set('arr_employees_pics1', $arr_employees_pics1);
    }    
    public function listthismonthattendance($hierarchy = '')
    {
        $this->autoRender = FALSE;
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        if ($company_code == 'BKHS') {
            $this_month_cond = array(
                "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime("now")),
                "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime("now")),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", "", "SIT"),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        } else {
            $this_month_cond = array(
                "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime("now")),
                "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime("now")),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", ""),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        }

        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );

        $emp_fkey = $this->Session->read('emp_fkey');
        if ($emp_fkey != '') {
            if ($hierarchy == 'hierarchy') {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                // $this_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
                //heirarchy condition
                 //edited by athira on 10-09-2025
                     if ($company_code == 'INFR') {
                         $today = $this->DeviceAttendance->query("SELECT emp_fkey, attr1 
FROM emp_proff 
LEFT JOIN emp_details 
    ON emp_details.emp_pkey = emp_proff.emp_fkey
WHERE emp_proff.attr1 = '$emp_fkey' 
");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['emp_proff']['emp_fkey'];
                }
                $emps[] = $emp_fkey;
            }else{
                $today = $this->DeviceAttendance->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['a']['emp_fkey'];
                }
            } 
        
                //end
                $str = implode("','", $emps);
                $this_month_cond[] = "EmployeeDetails.emp_pkey in ('$str')";
            } else {
                $this_month_cond['EmployeeDetails.emp_pkey'] = $this->Session->read('emp_fkey');
            }
        }
        //edited by sinsiya on 13-06-2024
        $today_join[] = array(
            'table' => 'branches',
            'alias' => 'Branch',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.branch_code = Branch.branch_code')
        );
        $today_join[] = array(
            'table' => 'employee_info',
            'alias' => 'emp_info',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = emp_info.emp_pkey')
        );


        //edited by sinsiya 13-06-2024
        $this_month_att = $this->DeviceAttendance->find(
            "all",
            array(
                "order" => "DeviceAttendance.device_attandance_seq DESC",
                "conditions" => $this_month_cond,
                'joins' => $today_join,
                "fields" => "Branch.branch_name,DeviceAttendance.device_attandance_seq,emp_info.employee_id,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
--  if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location
 COALESCE(DeviceAttendance.C3,Branch.branch_name) AS Location "
            )
        );
        //debug($this_month_att);
        $arr_resp = array(
            'data' => array()
        );


        $i = 0;
        $j = 1;
        //edited by sinsiya on 13-06-2024
        // $s=1;
        //edited by ASHIN on 05-07-24      
        foreach ($this_month_att as $key => $att) {
            $arr_resp['data'][$i][]/* ['SL NO'] */ = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
             $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["emp_info"]['employee_id']) ? $att["emp_info"]['employee_id'] : '';       
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d-m-Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            // $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i:s", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
            $j++;
        }
        //debug($arr_resp);
        return json_encode($arr_resp);
    }

    public function listleaverequests()
    {
        $leaverRequests = $this->listemployeeleaverequests(0);
        $this->set("results", $leaverRequests);
    }

    public function checkpunch($x = 0, $y = 0, $z = 0)
    {
        $arr_form_data = $this->request->data;

        $emp_fkey = $this->Session->read('emp_fkey');
        $this->autoRender = false;
        $this->AttendancePunch->useDbConfig = $this->Session->read('ds');
        $latitude = $y;
        $longitude = $z;

        function getlocation($latitude, $longitude)
        {
            $geolocation = $latitude . ',' . $longitude;
            //var_dump($longitude);
            $request = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $geolocation . '&sensor=false&key=AIzaSyBmp4GQqI30Qis3uVCEbDncRA667nvO61A';
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

        function getRealIpAddr()
        {
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
        function getBrowser()
        {
            $u_agent = $_SERVER['HTTP_USER_AGENT'];
            $bname = 'Unknown';
            $platform = 'Unknown';
            $version = "";
            // First get the platform?
            if (preg_match('/linux/i', $u_agent)) {
                $platform = 'linux';
            } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
                $platform = 'mac';
            } elseif (preg_match('/windows|win32/i', $u_agent)) {
                $platform = 'windows';
            }
            // Next get the name of the useragent yes seperately and for good reason
            if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
                $bname = 'Internet Explorer';
                $ub = "MSIE";
            } elseif (preg_match('/Firefox/i', $u_agent)) {
                $bname = 'Mozilla Firefox';
                $ub = "Firefox";
            } elseif (preg_match('/Chrome/i', $u_agent)) {
                $bname = 'Google Chrome';
                $ub = "Chrome";
            } elseif (preg_match('/Safari/i', $u_agent)) {
                $bname = 'Apple Safari';
                $ub = "Safari";
            } elseif (preg_match('/Opera/i', $u_agent)) {
                $bname = 'Opera';
                $ub = "Opera";
            } elseif (preg_match('/Netscape/i', $u_agent)) {
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
                if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                    $version = $matches['version'][0];
                } else {
                    $version = $matches['version'][1];
                }
            } else {
                $version = $matches['version'][0];
            }
            // check if we have a number
            if ($version == null || $version == "") {
                $version = "?";
            }
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
        $host = php_uname('n');
        if ($x === '1') {
            $dir = 'in';
        } else {
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

    public function lastpunch()
    {
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

    public function listlastmonthattendance($hierarchy = '')
    {
        $this->autoRender = FALSE;

        $company_code = strtoupper($this->Session->read('company_code'));
        if ($company_code == 'BKHS') {
            $last_month_cond = array(
                "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))),
                "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", "", "SIT"),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        } else {
            $last_month_cond = array(
                "date_format(LOGDATE,'%Y-%m-%d') >=" => date("Y-m-01", strtotime('-1 months', strtotime("now"))),
                "date_format(LOGDATE,'%Y-%m-%d') <=" => date("Y-m-t", strtotime('-1 months', strtotime("now"))),
                "DeviceAttendance.status >=" => "Y",
                "OR" => array(
                    "DeviceAttendance.C2 " => array("MOB", ""),
                    "DeviceAttendance.C2  is NULL"
                )
            );
        }

        $today_join[] = array(
            'table' => 'emp_details',
            'alias' => 'EmployeeDetails',
            'type' => 'INNER',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_id = DeviceAttendance.emp_id')
        );
        $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        if ($emp_fkey != '') {
            if ($hierarchy == 'hierarchy') {
                $today_join[] = array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                );
                //$last_month_cond[] = "(EmployeeProfessionalDetails.attr1 = $emp_fkey or EmployeeProfessionalDetails.emp_fkey = $emp_fkey )";
                //heirarchy condition
                    //edited by athira on 10-09-2025
                    if ($company_code == 'INFR') {
                        $today = $this->DeviceAttendance->query("SELECT emp_fkey, attr1 
FROM emp_proff 
LEFT JOIN emp_details 
    ON emp_details.emp_pkey = emp_proff.emp_fkey
WHERE emp_proff.attr1 = '$emp_fkey' 
");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['emp_proff']['emp_fkey'];
                }
                 $emps[] = $emp_fkey;
            }else{
                $today = $this->DeviceAttendance->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
                $emps = array();
                foreach ($today as $key => $att) {
                    $emps[] = $att['a']['emp_fkey'];
                }
            }

                $str = implode("','", $emps);
                $last_month_cond[] = "EmployeeDetails.emp_pkey in ('$str')";
            } else {
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
        //edited by sinsiya on 13-06-2024
        $today_join[] = array(
            'table' => 'employee_info',
            'alias' => 'emp_info',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = emp_info.emp_pkey')
        );

        //EDITED BY SINSIYA ON 18-09-2024
        $last_month_att = $this->DeviceAttendance->find(
            "all",
            array(
                "order" => "DeviceAttendance.device_attandance_seq DESC",
                "conditions" => $last_month_cond,
                'joins' => $today_join,
                "fields" => "Branch.branch_name,emp_info.employee_id,concat(EmployeeDetails.first_name,' ',ifnull(EmployeeDetails.middile_name,''),' ',ifnull(EmployeeDetails.last_name,'')) AS EmpName,DeviceAttendance.C1,DeviceAttendance.LOGDATE,
-- if((DeviceAttendance.C3 in ('',NULL)),Branch.branch_name,DeviceAttendance.C3) AS Location
            COALESCE(DeviceAttendance.C3,Branch.branch_name) AS Location "
            )
        );

        $arr_resp = array(
            'data' => array()
        );

        $i = 0;
        $j = 1;
        foreach ($last_month_att as $key => $att) {
            //EDITED BY ASHIN ON 05-07-24         
            $arr_resp['data'][$i][]/* ['SL NO'] */ = $j;
            $arr_resp['data'][$i][]/* ['EmpName'] */ = isset($att[0]['EmpName']) ? $att[0]['EmpName'] : '';
            $arr_resp['data'][$i][]/* ['EmpID'] */ = isset($att["emp_info"]['employee_id']) ? $att["emp_info"]['employee_id'] : '';
            $arr_resp['data'][$i][]/* ['Branch'] */ = isset($att["Branch"]['branch_name']) ? $att["Branch"]['branch_name'] : '';
            $arr_resp['data'][$i][]/* ['LOGDATE'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("d-m-Y", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';      //EDITED BY ASHIN on 28-06-24
            // $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['LOGTIME'] */ = isset($att["DeviceAttendance"]['LOGDATE']) ? date("H:i:s", strtotime($att["DeviceAttendance"]['LOGDATE'])) : '';
            $arr_resp['data'][$i][]/* ['C1'] */ = isset($att["DeviceAttendance"]['C1']) ? $att["DeviceAttendance"]['C1'] : '';
            $arr_resp['data'][$i][]/* ['Location'] */ = isset($att[0]['Location']) ? $att[0]['Location'] : '';
            $i++;
            $j++;
        }
        return json_encode($arr_resp);
        // DEBUG($arr_resp);
    }

    public function listemployeemisspunches()
    {
        //edited by sinsiya 13-06-2024
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//        $arr_empmisspunches = $this->EmployeeDetails->query("select ed.emp_id,concat(ed.first_name,' ',ifnull(ed.last_name,'')) as fullname, yearmonth, count(*) misscount
//     from emp_detail_timeattandance edt ,emp_details ed where ed.emp_pkey=edt.emp_pkey 
//    and present<>'P/P' and present is not null and date_format(current_date,'%y-%m') =date_format(yearmonth,'%y-%m')
//    group by edt.emp_pkey,ed.first_name,yearmonth order by 3 desc");
        //edited by sinsiya on 18-06-2024
        $arr_empmisspunches = $this->EmployeeDetails->query("SELECT 
    ed.emp_id,
    CONCAT(ed.first_name, ' ', IFNULL(ed.last_name, '')) AS fullname,
    emp_info.employee_id,
    branch.branch_name,
    edt.yearmonth,
    COUNT(*) AS misscount
FROM 
    emp_detail_timeattandance edt
JOIN 
    emp_details ed ON ed.emp_pkey = edt.emp_pkey
JOIN 
    employee_info emp_info ON emp_info.emp_pkey = edt.emp_pkey
JOIN 
    branches branch ON branch.branch_code = emp_info.branch_code
WHERE 
    edt.present <> 'P/P'
    AND edt.present IS NOT NULL
    AND DATE_FORMAT(CURRENT_DATE, '%y-%m') = DATE_FORMAT(edt.yearmonth, '%y-%m')
 group by edt.emp_pkey,ed.first_name,yearmonth order by 3 desc,edt.emp_detail_timeattandance_pkey DESC
");
        return $arr_empmisspunches;
    }

    //Edited by Akshay on 18-11-2023
    public function wish_modal($emp_pkey = 0, $event = '')
    {
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $this->set('emp_pkey', $emp_pkey);
        $this->set('event', $event);
        $emp_name = '';
        try {
            $arr_name = $this->Lateout->query("SELECT ei.EmpName FROM employee_info ei WHERE ei.emp_pkey = $emp_pkey");
            $emp_name = isset($arr_name[0]['ei']['EmpName']) ? $arr_name[0]['ei']['EmpName'] : '';
        } catch (Exception $e) {
        }
        $this->set('emp_name', $emp_name);
    }
    public function convertimage()
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        $arr_form_data = $this->request->data;

        $emp_pkey = $arr_form_data['emp_pkey'];
        $event = $arr_form_data['event'];
        $remark = isset($arr_form_data['remarks']) ? $arr_form_data['remarks'] : '';


        try {

            //Edited by Aksahy on 28-11-2023
            $google_fonts = "Roboto";

            $data = array(
                'html' => '',
                'css' => '',
                'google_fonts' => $google_fonts,
                'url' => 'https://v1.mypayrollmaster.online/Dashboard/renderImageTempalte'
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://hcti.io/v1/image");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            curl_setopt($ch, CURLOPT_POST, 1);
            // Retrieve your user_id and api_key from https://htmlcsstoimage.com/dashboard
            curl_setopt($ch, CURLOPT_USERPWD, "3ffab2bd-74e7-4198-8153-263ff1c985d2" . ":" . "8ee1bf09-7791-48d6-96ae-e6ace86eafc4");

            $headers = array();
            $headers = array("Content-Type: application/x-www-form-urlencoded");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $res = json_decode($result, true);
            //var_dump($res);
            // $this->sendemailtemplatedatabase($emp_pkey, $event, $remark); 
            $this->sendemailtemplate($emp_pkey, $event, $remark); //Edited by Akshay on 28-11-2023
            // $this->sendemailtemplate($res['url']);
            // https://hcti.io/v1/image/202dc04d-5efc-482e-8f92-bb51612c84cf
        } catch (Exception $e) {
            //  debug($e);
        }
    }
    public function sendemailtemplatedatabase($emp_pkey = 0, $event = '', $remark = '')
    {
        
        $this->autorender = false;
        $this->layout = null;
        $this->render(false);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->Wish->useDbConfig = $this->Session->read('ds');
        try {

            $arr_emp_details =  $this->EmployeeDetails->query("SELECT ei.EmpName, ed.email, uc.avatar,ei.joining_date FROM employee_info ei 
                                                            LEFT JOIN emp_details ed ON ed.emp_pkey = ei.emp_pkey
                                                            LEFT JOIN user_credentials AS uc ON uc.emp_fkey = ei.emp_pkey
                                                            WHERE ei.emp_pkey = $emp_pkey                
                                                        ");
            // debug($arr_emp_details); exit;
            $email = isset($arr_emp_details[0]['ed']['email']) ? $arr_emp_details[0]['ed']['email'] : '';
            $name  = isset($arr_emp_details[0]['ei']['EmpName']) ? $arr_emp_details[0]['ei']['EmpName'] : '';
            $avatar = isset($arr_emp_details[0]['uc']['avatar']) ? $arr_emp_details[0]['uc']['avatar'] : '';
            if($avatar = 'img/placeholdermen.jpeg'){
                $avatar = '';
            }
            // Assuming $joiningDate is a string in the format "YYYY-MM-DD"
            $joiningDate =  $arr_emp_details[0]['ei']['joining_date'];

            // Create DateTime objects for the joining date and current date
            $startDate = new DateTime($joiningDate);
            $endDate = new DateTime(date("Y-m-d"));

            // Calculate the interval between the two dates
            $interval = $startDate->diff($endDate);

            // Get the number of years
            $years = $interval->y;

            // Output the work anniversary
            if ($years == 1) {
                $suffix = "ST";
            } elseif ($years == 2) {
                $suffix = "ND";
            } elseif ($years == 3) {
                $suffix = "RD";
             } else {
                $suffix = "TH";
            }

            $arr_comp_contact = $this->EmployeeDetails->query("SELECT cc.logo FROM comp_contact_info cc");
            $logo_url = isset($arr_comp_contact[0]['cc']['logo']) ? $arr_comp_contact[0]['cc']['logo'] : '';
            $action = $event;
            // debug($action);
            $actions = trim($action);
            $mailcontent = $this->EmployeeDetails->query("SELECT * FROM `Email_Content` WHERE `mail_type` = 'wishes' AND `Type` = '$actions'");
            //  debug($mailcontent);
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = false;
            $mail->isSMTP();
            $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
            $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('mypayrollmaster@office24.online');
            $mail->addAddress($email);
            $mail->addBCC('projects@greatleap.tech');
           // $mail->addBCC('meghaforsight@gmail.com');
            $mail->addReplyTo('mypayrollmaster@office24.online');
            $mail->isHTML(true);
            $pic = $mailcontent['0']['Email_Content']['Image'];
            //$image = 'https://v1.mypayrollmaster.online/img/6.png'; //Edited by Akshay on 28-11-2023
            // $profileimage = 'https://qaoci.mypayrollmaster.online/User/profile';
            $profileimage = 'https://v1.mypayrollmaster.online/' . $avatar;
            if ($avatar != 'img/placeholdermen.jpeg') {
                // $photo = '<div class="profile-image" style="padding-left: 20px; padding-bottom: 30px;left:20px;bottom:20px;">
                //                 <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 200px; max-height: 200px; width: auto; height: auto; object-fit: contain;">
                //             </div>';
                // $photo = '<div class="profile-image" style="width:150px;height:150px;position: absolute; left: 20px; bottom: 0px; padding-left: 20px; z-index: 999; flex: 1;">
                //             <img src="'.$profileimage.'" alt="Profile Image" style="max-width: 150px; max-height: 150px; width: auto; height: auto; object-fit: contain;padding-bottom:0px;">
                //         </div>';
                if ($event == 'Work Anniversary') {
                    $photo ='';
                    if($avatar){
                    $photo = '<div class="profile-image" style="text-align: left; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                                <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 100px; max-height: 130px; width: auto; height: auto; object-fit: contain; padding-top: 0px; padding-left: 10px;">
                            </div>
                            ';
                    }
                } else {
                    $photo = '<div class="profile-image" style="text-align: right; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                                <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 150px; max-height: 100px; width: auto; height: auto; object-fit: contain; padding-bottom: 0px;">
                            </div>
                            ';
                }
            } else {
                if ($event == 'Work Anniversary') {
                    $photo = '<div class="profile-image" style="text-align: left; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                               
                            </div>
                            ';
                } else {
                    $photo = '<div class="profile-image" style="text-align: right; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                               
                            </div>
                            ';
                }
            }

            $logo_img = '';

            // if($logo_url != 'img/placeholdermen.jpeg'){
            //     $logo = 'https://qaoci.mypayrollmaster.online/'.$logo_url;
            //     $logo_img = '<div class="logo" style="text-align: left; width: 200px; height: 50px; overflow: hidden; margin-right:400px;">
            //                     <img src="'.$logo.'" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            //                 </div>';
            // }else{
            //     $logo_img = '';
            // }



            //Edited by Akshay on 28-11-2023
            if ($event == 'Work Anniversary') {
                // $image = 'http://qaoci.mypayrollmaster.online/newlogin/img/pink_flower_frame_edited.jpg';//Edited by Akshay on 28-11-2023
                $image = 'https://v1.mypayrollmaster.online/' . $pic;
                $color = '#fff';
                $heading = '';
                $heading = '<h3 style="margin-top:0px;text-align:center;color:#AA336A;">' . $heading . '</h3>';
                $heading_colour = '#fff';
                $wish_content = ' ' . $years . '<sup>' . $suffix . '<sup> ';
                $name_div = $mailcontent['0']['Email_Content']['Message'];
            } else {
                $image = 'https://v1.mypayrollmaster.online/' . $pic;
                $color = '#63cf22';
                $heading = '';
                $heading_colour = '#333333';
                $wish_content = '';
                $name_div = $mailcontent['0']['Email_Content']['Message'];
                $name_div = str_replace('{:name}', $name, $name_div);
            }
            $org_subject = $mailcontent['0']['Email_Content']['subject'];
            $subject = str_replace('{:event}', $event, $org_subject);
            $subject = str_replace('{:name}', $name, $subject);
            $mail->Subject = $subject;
            $content = $mailcontent['0']['Email_Content']['Content'];
            $content = str_replace('{:image}', $image, $content);
            $content = str_replace('{:color}', $color, $content);
            $content = str_replace('{:wish_content}', $wish_content, $content);
            $content = str_replace('{:photo}', $photo, $content);
            $content = str_replace('{:heading}', $heading, $content);
            $content = str_replace('{:name_div}', $name_div, $content);
            $content = str_replace('{:remark}', $remark, $content);
            
            if ($event == 'Work Anniversary') {
                $mail->MsgHTML($content);
            } else {
                $mail->MsgHTML($content);
            }
            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {

                

$type = $arr_form_data['type'] = $event;
$date = $arr_form_data['date'] = date('Y-m-d');
$remarks = $arr_form_data['remarks'] = $remark;
$emp_fkey = $arr_form_data['emp_fkey'] = $emp_pkey;
$result = $this->EmployeeDetails->query("INSERT INTO `wishes` (`type`, `date`, `remarks`, `emp_fkey`) 
VALUES ('$type', '$date', '$remarks', '$emp_fkey')");
// Assuming 'Wish' table has an 'id' column and primary key is set correctly
//$result = $this->Wish->save($arr_form_data);
// Handle the save result
if ($result) { 
    // Save was successful
    echo 'Message has been sent';
} else {
    // Save failed
    echo 'Message has not been sent';
}    
            }
        } catch (Exception $ex) {
            var_dump($ex->getMessage());
        }
    }

    public function sendemailtemplate($emp_pkey = 0, $event = '', $remark = '')
    {
        $this->autorender = false;
        $this->layout = null;
        $this->render(false);
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        try {
            $arr_emp_details =  $this->EmployeeDetails->query("SELECT ei.EmpName, ed.email, uc.avatar,ei.joining_date FROM employee_info ei 
                                                            LEFT JOIN emp_details ed ON ed.emp_pkey = ei.emp_pkey
                                                            LEFT JOIN user_credentials AS uc ON uc.emp_fkey = ei.emp_pkey
                                                            WHERE ei.emp_pkey = $emp_pkey                
                                                        ");
         
            $email = isset($arr_emp_details[0]['ed']['email']) ? $arr_emp_details[0]['ed']['email'] : '';
            $name  = isset($arr_emp_details[0]['ei']['EmpName']) ? $arr_emp_details[0]['ei']['EmpName'] : '';
            $avatar = isset($arr_emp_details[0]['uc']['avatar']) ? $arr_emp_details[0]['uc']['avatar'] : '';
            // Assuming $joiningDate is a string in the format "YYYY-MM-DD"
            $joiningDate =  $arr_emp_details[0]['ei']['joining_date'];

          
            //edited by athira on 31-07-2025
            $joiningDate = new DateTime($joiningDate);
            $today = new DateTime();
            // Build this year's anniversary date
            $anniversaryThisYear = DateTime::createFromFormat('Y-m-d', $today->format('Y') . '-' . $joiningDate->format('m-d'));
            // Always show how many years will be completed **on** the upcoming anniversary
            $years = $anniversaryThisYear->format('Y') - $joiningDate->format('Y');
           //end

            // Output the work anniversary
            if ($years == 1) {
                $suffix = "ST";
            } elseif ($years == 2) {
                $suffix = "ND";
            } elseif ($years == 3) {
                $suffix = "RD";
            } else {
                $suffix = "TH";
            }

            $arr_comp_contact = $this->EmployeeDetails->query("SELECT cc.logo FROM comp_contact_info cc");
            $logo_url = isset($arr_comp_contact[0]['cc']['logo']) ? $arr_comp_contact[0]['cc']['logo'] : '';

            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = false;
            $mail->isSMTP();
            $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
            $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('mypayrollmaster@office24.online');
            $mail->addAddress($email);
            $mail->addBCC('projects@greatleap.tech');
            $mail->addReplyTo('mypayrollmaster@office24.online');
            $mail->isHTML(true);
            $mail->Subject = $event . ' of ' . $name;
            
            $profileimage = 'https://v1.mypayrollmaster.online/' . $avatar;
           
            if ($event == 'Work Anniversary') {
                $backgroundUrl = 'https://v1.mypayrollmaster.online/img/82.jpg'; // Work anniversary background
                $profileUrl = $profileimage; // Actual image URL or path of the employee
                $outputPath = WWW_ROOT . 'img' . DS . 'work_anniv_merged.jpg'; // Path to save output
                $event = 'Work Anniversary';
                $suffix = ' Years';      // Example: " Years" or " Yr"

                $this->mergeImages($backgroundUrl, $profileUrl, $outputPath, $event, $name, $years, $suffix);

                $generatedImageUrl = Router::url('/img/' . basename($outputPath), true) . '?v=' . time();
				 $mail->MsgHTML(
                        '
                                    <!DOCTYPE html>
                                    <html>
                                    <head>
                                        <title>Work Anniversary Wish</title>
                                        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                                    </head>
                                    <body style="margin: 0; padding: 0;">
                                        <div style="width: 100%; max-width: 626px; margin: 0 auto;">
                                            <img src="' . $generatedImageUrl . '" alt="Work Anniversary Banner" style="width: 100%; height: auto; display: block;" />
                                        </div>
                                        <div style="font-size: 15px; color: #ff0000; padding-top: 20px; font-weight: bold; padding-left: 10px;">
                                            ' . $remark . '
                                        </div>
                                    </body>
                                    </html>'
                    );
            } else {
    
              $backgroundUrl = 'https://v1.mypayrollmaster.online/img/14.jpg';
                    $profileUrl = $profileimage;
                    $outputPath = WWW_ROOT . 'img' . DS . 'merged_image.jpg';
                    $this->mergeImages($backgroundUrl, $profileUrl, $outputPath, $event, $name, $years, $suffix);
                    //$generatedImageUrl = Router::url('/img/' . basename($outputPath), true);

                     $generatedImageUrl = Router::url('/img/' . basename($outputPath), true) . '?v=' . time();

                    // $mail->addEmbeddedImage($outputPath, 'mergedimagecid', 'merged_image.jpg');
                    $mail->MsgHTML('
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>Birthday Wish</title>
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                                </head>
                                <body style="margin: 0; padding: 0;">
                                    <div style="width: 100%; max-width: 626px; margin: 0 auto;">
                                        <img src="' . $generatedImageUrl . '" alt="Birthday Banner" style="width: 100%; height: auto; display: block;" />
                                    </div>
                                    <div style="font-size: 15px; color: #ff0000; padding-top: 20px; font-weight: bold; padding-left: 10px;">
                                        ' . $remark . '
                                    </div>
                                </body>
                                </html>');
            }

            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                $this->Wish->useDbConfig = $this->Session->read('ds');
                date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
                $arr_form_data = $this->request->data;
                $arr_form_data['created_by'] = $this->Session->read('login_user_id');
                $arr_form_data['type'] = $event;
                $arr_form_data['date'] = date('Y-m-d');
                $arr_form_data['remarks'] = $remark;
                $arr_form_data['emp_fkey'] = $emp_pkey;

                $result = $this->Wish->save($arr_form_data);

                echo 'Message has been sent';
            }
        } catch (Exception $ex) {
            var_dump('$ex->getMessage()');
        }
    }
//      public function sendemailtemplate($emp_pkey = 0, $event = '', $remark = '')
//     {
//         $this->autorender = false;
//         $this->layout = null;
//         $this->render(false);
//         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

//         try {

//             $arr_emp_details =  $this->EmployeeDetails->query("SELECT ei.EmpName, ed.email, uc.avatar,ei.joining_date FROM employee_info ei 
//                                                             LEFT JOIN emp_details ed ON ed.emp_pkey = ei.emp_pkey
//                                                             LEFT JOIN user_credentials AS uc ON uc.emp_fkey = ei.emp_pkey
//                                                             WHERE ei.emp_pkey = $emp_pkey                
//                                                         ");
//             // debug($arr_emp_details); exit;
//             $email = isset($arr_emp_details[0]['ed']['email']) ? $arr_emp_details[0]['ed']['email'] : '';
//             $name  = isset($arr_emp_details[0]['ei']['EmpName']) ? $arr_emp_details[0]['ei']['EmpName'] : '';
//             $avatar = isset($arr_emp_details[0]['uc']['avatar']) ? $arr_emp_details[0]['uc']['avatar'] : '';
//             // Assuming $joiningDate is a string in the format "YYYY-MM-DD"
//             $joiningDate =  $arr_emp_details[0]['ei']['joining_date'];

//               // Create DateTime objects for the joining date and current date
//             // $startDate = new DateTime($joiningDate);
//             // $endDate = new DateTime(date("Y-m-d"));

//             // // Calculate the interval between the two dates
//             // $interval = $startDate->diff($endDate);

//             // // Get the number of years
//             // $years = $interval->y;
//             //edited by athira on 31-07-2025
//             $joiningDate = new DateTime($joiningDate);
//             $today = new DateTime();

//             // Build this year's anniversary date
//             $anniversaryThisYear = DateTime::createFromFormat('Y-m-d', $today->format('Y') . '-' . $joiningDate->format('m-d'));

//             // Always show how many years will be completed **on** the upcoming anniversary
//             $years = $anniversaryThisYear->format('Y') - $joiningDate->format('Y');

//            //end
//             // Output the work anniversary


//             if ($years == 1) {
//                 $suffix = "ST";
//             } elseif ($years == 2) {
//                 $suffix = "ND";
//             } elseif ($years == 3) {
//                 $suffix = "RD";
//             } else {
//                 $suffix = "TH";
//             }

//             $arr_comp_contact = $this->EmployeeDetails->query("SELECT cc.logo FROM comp_contact_info cc");
//             $logo_url = isset($arr_comp_contact[0]['cc']['logo']) ? $arr_comp_contact[0]['cc']['logo'] : '';

//             App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
//             $mail = new PHPMailer;
//             $mail->SMTPDebug = false;
//             $mail->isSMTP();
//             $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
//             $mail->SMTPAuth = true;
//             $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
//             $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
//             $mail->SMTPSecure = 'tls';
//             $mail->Port = 587;

//             $mail->setFrom('mypayrollmaster@office24.online');
//             $mail->addAddress($email);
//             $mail->addBCC('projects@greatleap.tech');
//             $mail->addBCC('meghaforsight@gmail.com');
//             $mail->addReplyTo('mypayrollmaster@office24.online');
//             $mail->isHTML(true);

//             $image = 'http://v1.mypayrollmaster.online/newlogin/img/6.png'; //Edited by Akshay on 28-11-2023
//             // $profileimage = 'https://qaoci.mypayrollmaster.online/User/profile';
//             $profileimage = 'https://v1.mypayrollmaster.online/' . $avatar;
//             if ($avatar != 'img/placeholdermen.jpeg') {
//                 // $photo = '<div class="profile-image" style="padding-left: 20px; padding-bottom: 30px;left:20px;bottom:20px;">
//                 //                 <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 200px; max-height: 200px; width: auto; height: auto; object-fit: contain;">
//                 //             </div>';
//                 // $photo = '<div class="profile-image" style="width:150px;height:150px;position: absolute; left: 20px; bottom: 0px; padding-left: 20px; z-index: 999; flex: 1;">
//                 //             <img src="'.$profileimage.'" alt="Profile Image" style="max-width: 150px; max-height: 150px; width: auto; height: auto; object-fit: contain;padding-bottom:0px;">
//                 //         </div>';
//                 if ($event == 'Work Anniversary') {
//                     $photo = '<div class="profile-image" style="text-align: left; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
//                                 <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 100px; max-height: 130px; width: auto; height: auto; object-fit: contain; padding-top: 0px; padding-left: 10px;">
//                             </div>
//                             ';
//                 } else {
//                     $photo = '<div class="profile-image" style="text-align: right; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
//                                 <img src="' . $profileimage . '" alt="Profile Image" style="max-width: 150px; max-height: 100px; width: auto; height: auto; object-fit: contain; padding-bottom: 0px;">
//                             </div>
//                             ';
//                 }
//             } else {
//                 if ($event == 'Work Anniversary') {
//                     $photo = '<div class="profile-image" style="text-align: left; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                               
//                             </div>
//                             ';
//                 } else {
//                     $photo = '<div class="profile-image" style="text-align: right; width: 100%; height: 130px; position: absolute; top: 0px; left: 0px;  z-index: 999;">
                               
//                             </div>
//                             ';
//                 }
//             }

//             $logo_img = '';

//             // if($logo_url != 'img/placeholdermen.jpeg'){
//             //     $logo = 'https://qaoci.mypayrollmaster.online/'.$logo_url;
//             //     $logo_img = '<div class="logo" style="text-align: left; width: 200px; height: 50px; overflow: hidden; margin-right:400px;">
//             //                     <img src="'.$logo.'" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
//             //                 </div>';
//             // }else{
//             //     $logo_img = '';
//             // }



//             //Edited by Akshay on 28-11-2023 
//             if ($event == 'Work Anniversary') {
//                 // $image = 'http://qaoci.mypayrollmaster.online/newlogin/img/pink_flower_frame_edited.jpg';//Edited by Akshay on 28-11-2023
//                 $image = 'https://v1.mypayrollmaster.online/img/80.png';
//                 $color = '#fff';
//                 $heading = '';
//                 $heading = '<h3 style="margin-top:0px;text-align:center;color:#AA336A;">' . $heading . '</h3>';
//                 $heading_colour = '#fff';
//                 $wish_content = ' ' . $years . '<sup>' . $suffix . '<sup> ';

//                 $name_div = '<div class="text-over-image" style="color: #ff0000; text-align: left;padding-left:260px; font-size: 18px; padding-top: 10px;font-weight:bold;">
//             <i> ' . '' . '</i>
//         </div>';
//             } else {
//                 $image = 'https://v1.mypayrollmaster.online/img/6.png';  
//                 $color = '#63cf22';
//                 $heading = '';
//                 $heading_colour = '#333333';
//                 $wish_content = '';

// //                $name_div = '<div class="text-over-image" style="color: #332c2b; text-align: right; font-size: 18px; padding-top: 0px;padding-right:90px;font-weight:900!important;">
// //                                Dear ' . $name . '
// //                            </div>';
//                 $name_div = '<div style="color: #332c2b; font-size: 18px; font-weight: 900; padding-right: 10px;">
//                 Dear ' . $name . '
//             </div>';
                
//             }

//             $mail->Subject = $event . ' of ' . $name;

//             if ($event == 'Work Anniversary') {
//                 $mail->MsgHTML('
//              <!DOCTYPE html>
//     <html>
//     <head>
//         <title>Work Anniversary Wish</title>
//     </head>
//     <body>
//         <!--[if gte mso 9]>
//         <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:400px;height:300px;">
//             <v:fill type="tile" src="' . $image . '" color="#eeeeee" />
//             <v:textbox inset="0,0,0,0">
//         <![endif]-->
        
//         <div class="background-image" style="background: url(' . $image . ') no-repeat center center / cover; background-color: #eeeeee; width: 300px; height: 396px; position: relative;">
        
//             <div class="text-over-image" style="flex: 1; color: ' . $color . '; text-align: left; font-size: 27px; padding-top: 30px; padding-left: 100px; font-weight: bold; font-style: italic;">
//                 ' . $wish_content . '
//             </div>

//             <div class="text-over-image" style="flex: 1; color: ' . $color . '; text-align: left; font-size: 12px; padding-top: 130px; padding-left: 303px; font-weight: bold;">
//                 ' . $heading . '
//             </div>

//             <div style="display: flex; justify-content: center; align-items: center; height: 100%; padding-top: 5px;padding-left:250px;">
//                 ' . $photo . '
//             </div>
            
//             ' . $name_div . '
            
//             <div style="display: flex; padding-top: 10px;">
//                 <!-- Additional content can be added here if needed -->
//             </div>
//         </div>
        
//         <!--[if gte mso 9]>
//             </v:textbox>
//         </v:rect>
//         <![endif]-->
//     </body>
//     </html>
    
//     <div style="font-size: 15px; color: #ff0000; padding-top: 20px; font-weight: bold; padding-left: 10px;">
//         ' . $remark . '
//     </div>
// ');

//             } else {
// //                $mail->MsgHTML('
// //                <!DOCTYPE html>
// //                <html>
// //                <head>
// //                    <title>Birthday Wish</title>
// //                </head>
// //                <body>
// //                    <!--[if gte mso 9]>
// //                    <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:420px;height:300px;">
// //                        <v:fill type="tile" src="' . $image . '" color="#eeeeee" />
// //                        <v:textbox inset="0,0,0,0">
// //                    <![endif]-->
// //                    
// //                    <div class="background-image" style="background: url(' . $image . ') no-repeat center center / cover; background-color: #eeeeee; width: 695px; height: 496px; position: relative;"> 
// //			 
// //                    ' . $photo . '
// //                    ' . $heading . '
// //                        ' . $name_div . '
// //
// //                        <div style=" display: flex;padding-top:0px;">
// //                            <div class="text-over-image" style="flex: 1;color: ' . $color . '; text-align: left; font-size: 15px; padding-top: 0px;padding-left:30px;font-weight: bold;">
// //                                ' . $wish_content . '
// //                            </div>                    
// //    
// //                        </div>
// //    
// //                    </div>
// //                    <!--[if gte mso 9]>
// //                        </v:textbox>
// //                    </v:rect>
// //                    <![endif]-->
// //                </body>
// //                </html> <div style="font-size: 15px; color: #ff0000;padding-top:20px;font-weight:bold;padding-left:10px;">' . $remark . '</div>
// //            ');
// $mail->MsgHTML('
// <!DOCTYPE html>
// <html>
// <head>
//   <title>Birthday Wish</title>
//   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
//   <style>
//     @media only screen and (max-width: 600px) {
//       .name-profile-wrapper {
//         flex-direction: column !important;
//         align-items: flex-end !important;
//       }
//         .bg-wrapper {
//       min-height: 600px !important; /* increase height slightly for mobile */
//       padding: 10px !important;     /* optional: reduce padding to avoid crowding */
//     }
//     }
//   </style>
// </head>
// <body style="margin: 0; padding: 0;">

//   <!--[if gte mso 9]>
//   <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:695px;height:400px;">
//     <v:fill type="tile" src="' . $image . '" color="#eeeeee" />
//     <v:textbox inset="0,0,0,0">
//   <![endif]-->

// <!-- <div style="background: url(' . $image . ') no-repeat center center / cover; background-color: #eeeeee; position: relative; box-sizing: border-box; padding: 20px;">-->
// <div class="bg-wrapper" style="background: url(' . $image . ') no-repeat center center / cover; background-color: #eeeeee; position: relative; box-sizing: border-box; ">
//     <!-- Profile Image + Name Side by Side -->
//     <div class="name-profile-wrapper" style="display: flex; justify-content: flex-end; align-items: center; ;">

     

//       <!-- Profile Image on right -->
//       <img src="' . $profileimage . '" alt="Profile Image"
//            style="width: 100px; height: auto; border-radius: 8px; display: block;" />
//             <!-- Name on left -->
//       ' . $name_div . '

//     </div>

//     <!-- Heading -->
//     ' . $heading . '

//     <!-- Message -->
//     <div style="color: ' . $color . '; text-align: left; font-size: 15px; font-weight: bold; padding-top: 10px; padding-left: 10px;">
//       ' . $wish_content . '
//     </div>
// <div style="padding: 50px;">
// </div>
//   </div>

//   <!--[if gte mso 9]>
//     </v:textbox>
//   </v:rect>
//   <![endif]-->

//   <!-- Remark -->
//   <div style="font-size: 15px; color: #ff0000; padding-top: 20px; font-weight: bold; padding-left: 10px;">
//     ' . $remark . '
//   </div>

// </body>
// </html>
// ');


// //$mail->MsgHTML('
// //<!DOCTYPE html>
// //<html>
// //<head>
// //  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
// //  <style>
// //    @media only screen and (max-width: 600px) {
// //      .main-container {
// //        width: 100% !important;
// //      }
// //      .content {
// //        font-size: 14px !important;
// //        padding: 20px !important;
// //      }
// //    }
// //  </style>
// //</head>
// //<body style="margin:0; padding:0; background-color:#eeeeee;">
// //
// //  <center>
// //    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
// //      <tr>
// //        <td align="center">
// //
// //          <!--[if gte mso 9]>
// //          <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:400px;">
// //            <v:fill type="frame" src="cid:birthdaybg" color="#eeeeee" />
// //            <v:textbox inset="0,0,0,0">
// //          <![endif]-->
// //
// //          <table class="main-container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; background-image: url(cid:birthdaybg); background-size: cover; background-position: center; background-repeat: no-repeat;">
// //            <tr>
// //              <td class="content" style="padding: 40px; font-family: Arial, sans-serif; font-size: 16px; color: #ffffff; font-weight: bold; text-align: left;">
// //                
// //                <!-- Optional Photo -->
// //                <div style="margin-top: 0px;">' . $photo . '</div>
// //
// //                <!-- Name -->
// //                <div style="margin-bottom: 0px;">' . $name_div . '</div>
// //
// //                <!-- Wish Message -->
// //                <div style="line-height: .5;">' . $wish_content . '</div>
// //
// //              </td>
// //            </tr>
// //          </table>
// //
// //          <!--[if gte mso 9]>
// //            </v:textbox>
// //          </v:rect>
// //          <![endif]-->
// //
// //          <!-- Optional Remark -->
// //          <table width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; background-color:#ffffff;">
// //            <tr>
// //              <td style="font-size:15px; color:#ff0000; font-weight:bold; padding:20px 10px; font-family: Arial, sans-serif;">
// //                ' . $remark . '
// //              </td>
// //            </tr>
// //          </table>
// //
// //        </td>
// //      </tr>
// //    </table>
// //  </center>
// //
// //</body>
// //</html>
// //');

// //$mail->MsgHTML('
// //<!DOCTYPE html>
// //<html>
// //<head>
// //  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
// //  <style>
// //    @media only screen and (max-width: 600px) {
// //      .main-table {
// //        width: 100% !important;
// //      }
// //      .content {
// //        font-size: 14px !important;
// //        padding: 20px !important;
// //      }
// //    }
// //  </style>
// //</head>
// //<body style="margin:0; padding:0; background-color:#eeeeee;">
// //
// //  <center>
// //    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#eeeeee">
// //      <tr>
// //        <td align="center">
// //
// //          <!--[if gte mso 9]>
// //          <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:695px;height:100%;">
// //            <v:fill type="frame" src="' . $image . '" color="#eeeeee" />
// //            <v:textbox inset="0,0,0,0">
// //          <![endif]-->
// //
// //          <table width="695" cellpadding="0" cellspacing="0" border="0" class="main-table" style="width:100%; max-width:695px; background-image: url(' . $image . '); background-size: cover; background-repeat: no-repeat; background-position: center;">
// //            <tr>
// //              <td class="content" style="padding: 40px 40px 30px 40px; font-family: Arial, sans-serif; font-size: 16px; color:' . $color . '; font-weight: bold; text-align: left; min-height: 400px;">
// //                
// //                <div style="margin-bottom: -20px;">
// //                  ' . $photo . '
// //                </div>
// //
// //                <div style="margin-bottom: 15px;">
// //                  ' . $name_div . '
// //                </div>
// //
// //                <div style="margin-top: 10px;">
// //                  ' . $wish_content . '
// //                </div>
// //
// //              </td>
// //            </tr>
// //          </table>
// //
// //          <!--[if gte mso 9]>
// //            </v:textbox>
// //          </v:rect>
// //          <![endif]-->
// //
// //          <!-- Remark section -->
// //          <table width="695" cellpadding="0" cellspacing="0" border="0" style="max-width:695px; background-color:#ffffff; width:100%;">
// //            <tr>
// //              <td style="font-size:15px; color:#ff0000; font-weight:bold; padding:20px 10px; font-family: Arial, sans-serif;">
// //                ' . $remark . '
// //              </td>
// //            </tr>
// //          </table>
// //
// //        </td>
// //      </tr>
// //    </table>
// //  </center>
// //
// //</body>
// //</html>
// //');
 
// //$mail->MsgHTML('
// //<!DOCTYPE html>
// //<html>
// //<head>
// //  <title>Birthday Wish</title>
// //  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
// //  <style>
// //    @media only screen and (max-width: 480px) {
// //      .background-image {
// //        width: 100% !important;
// //        height: auto !important;
// //      }
// //      .text-over-image {
// //        font-size: 14px !important;
// //        padding-left: 10px !important;
// //        padding-right: 10px !important;
// //      }
// //    }
// //  </style>
// //</head>
// //<body style="margin:0; padding:0; background-color:#eeeeee;">
// //  <!--[if gte mso 9]>
// //  <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:450px;height:396px;">
// //    <v:fill type="tile" src="' . $image . '" color="#eeeeee" />
// //    <v:textbox inset="0,0,0,0">
// //  <![endif]-->
// //  
// //  <div class="background-image" style="background: url(' . $image . ') no-repeat center center / cover; background-color: #eeeeee; width: 100%; max-width: 450px; height: auto; padding: 20px; box-sizing: border-box; font-family: sans-serif;">
// //    ' . $photo . '
// //    ' . $heading . '
// //    ' . $name_div . '
// //
// //    <div style="display: flex; flex-direction: column; padding-top: 10px;">
// //      <div class="text-over-image" style="color:' . $color . '; text-align: left; font-size: 15px; padding-left: 30px; font-weight: bold; line-height: 1.5; word-break: break-word;">
// //        ' . $wish_content . '
// //      </div>                    
// //    </div>
// //  </div>
// //
// //  <!--[if gte mso 9]>
// //    </v:textbox>
// //  </v:rect>
// //  <![endif]-->
// //
// //  <div style="font-size: 15px; color: #ff0000; padding-top: 20px; font-weight: bold; padding-left: 10px; font-family: sans-serif;">
// //    ' . $remark . '
// //  </div>
// //</body>
// //</html>
// //');


//             }
//             if (!$mail->send()) {
//                 echo 'Message could not be sent.';
//                 echo 'Mailer Error: ' . $mail->ErrorInfo;
//             } else {
//                 $this->Wish->useDbConfig = $this->Session->read('ds');
//                 date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
//                 $arr_form_data = $this->request->data;
//                 $arr_form_data['created_by'] = $this->Session->read('login_user_id');
//                 $arr_form_data['type'] = $event;
//                 $arr_form_data['date'] = date('Y-m-d');
//                 $arr_form_data['remarks'] = $remark;
//                 $arr_form_data['emp_fkey'] = $emp_pkey;
																				
//                 $result = $this->Wish->save($arr_form_data);
						
//                 echo 'Message has been sent';
	 
//             }
//         } catch (Exception $ex) {
//             var_dump('$ex->getMessage()');
//         }
//     }

       // Edited by Akshay on 7-7-2025
    private function mergeImages($backgroundUrl, $profileUrl, $outputPath, $event = '', $name = '', $years = '', $suffix = '')
    {
        $background = $this->createImageFromFile($backgroundUrl);
        if (!$background) return false;

        $profile = $this->createImageFromFile($profileUrl);
        if (!$profile) return false;

        $bg_width = imagesx($background);
        $bg_height = imagesy($background);

        // Work Anniversary or Birthday settings
        if ($event === 'Work Anniversary') {
            $final_profile_size = 220;
            $dest_x = 100; // bottom-left corner
            $dest_y = $bg_height - $final_profile_size - 100;
        } else {
            $final_profile_size = 300;
            $dest_x = ($bg_width - $final_profile_size) / 2;
            $dest_y = 100; // moved up by 40px (was 220)
        }

        // Resize
        $profile_resized = imagecreatetruecolor($final_profile_size, $final_profile_size);
        imagealphablending($profile_resized, false);
        imagesavealpha($profile_resized, true);
        $transparent = imagecolorallocatealpha($profile_resized, 0, 0, 0, 127);
        imagefill($profile_resized, 0, 0, $transparent);
        imagecopyresampled(
            $profile_resized,
            $profile,
            0,
            0,
            0,
            0,
            $final_profile_size,
            $final_profile_size,
            imagesx($profile),
            imagesy($profile)
        );

        // Apply circular mask only if not Work Anniversary
        if ($event !== 'Work Anniversary') {
            $mask = imagecreatetruecolor($final_profile_size, $final_profile_size);
            imagealphablending($mask, false);
            imagesavealpha($mask, true);
            $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
            imagefill($mask, 0, 0, $transparent);
            $circle_color = imagecolorallocate($mask, 255, 255, 255);
            imagefilledellipse($mask, $final_profile_size / 2, $final_profile_size / 2, $final_profile_size, $final_profile_size, $circle_color);

            for ($x = 0; $x < $final_profile_size; $x++) {
                for ($y = 0; $y < $final_profile_size; $y++) {
                    if (imagecolorat($mask, $x, $y) == $circle_color) {
                        imagesetpixel($profile_resized, $x, $y, imagecolorat($profile_resized, $x, $y));
                    } else {
                        imagesetpixel($profile_resized, $x, $y, $transparent);
                    }
                }
            }
            imagedestroy($mask);
        }



        // Text settings
        $fontPath = WWW_ROOT . 'fonts' . DS . 'Arial.ttf';
        $textColor = imagecolorallocate($background, 149, 91, 15); // rgb(149, 91, 15)

        if ($event === 'Work Anniversary') {
            $yearText = ($years == 1) ? 'Year' : 'Years';


            $fontSize = 27;
            $textColor = imagecolorallocate($background, 255, 255, 255); // white

            $shadowColor = imagecolorallocate($background, 0, 0, 0); // black shadow
            $shadowOffsetX = 2;
            $shadowOffsetY = 2;

            // 🔵 Center profile image vertically (middle Y) and left align on X
            $final_profile_size = 220;
            $dest_x = ($bg_width - $final_profile_size) / 2 - 400; // Shift 50px to the left
            $dest_y = ($bg_height - $final_profile_size) / 2;

            // Place profile image
            imagecopy($background, $profile_resized, $dest_x, $dest_y, 0, 0, $final_profile_size, $final_profile_size);

            $lines = [
                "Dear {$name},",
                "Thank You for {$years} {$yearText} of Remarkable Contribution"
            ];
            $lineHeight = $fontSize + 10;
            $currentY = $dest_y + $final_profile_size + 40;

            foreach ($lines as $line) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
                $textWidth = $bbox[2] - $bbox[0];
                $textX = ($bg_width - $textWidth) / 2;

                // Draw shadow
                imagettftext($background, $fontSize, 0, $textX + $shadowOffsetX, $currentY + $shadowOffsetY, $shadowColor, $fontPath, $line);

                // Draw main text
                imagettftext($background, $fontSize, 0, $textX, $currentY, $textColor, $fontPath, $line);

                $currentY += $lineHeight;
            }
        } else {
            // Place profile image
            imagecopy($background, $profile_resized, $dest_x, $dest_y, 0, 0, $final_profile_size, $final_profile_size);

            // Birthday name text
            $fontSize = 18;
            $lineSpacing = 30;
            $lines = [$name];
            $startY = $dest_y + $final_profile_size + 250;

            foreach ($lines as $i => $line) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
                $textWidth = $bbox[2] - $bbox[0];
                $textX = ($bg_width - $textWidth) / 2;
                $textY = $startY + $i * ($fontSize + $lineSpacing);

                imagettftext($background, $fontSize, 0, $textX, $textY, $textColor, $fontPath, $line);
            }
        }

        imagejpeg($background, $outputPath, 80);
        imagedestroy($background);
        imagedestroy($profile);
        imagedestroy($profile_resized);

        return true;
    }

    private function createImageFromFile($filePath)
    {   

        $imageType = exif_imagetype($filePath);

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filePath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filePath);
            default:
                return false;
        }
    }
    // End
    //Edited by Akshay on 10-6-2024
    public function automation_load_birthdays()
    {
        $this->autoRender = false;
        $date = date('Y-m-d');
        $db = 'mypayrol_mpm586';
        $company_code = 'KDNH';
        $table_joins[] = array(
            'table' => $db.'.user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins[] = array(
            'table' => $db.'.emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins[] = array(
            'table' => $db.'.wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Birthday', 'Wish.date' => $date)
        );
        $table_joins1[] = array(
            'table' => $db.'.user_credentials',
            'alias' => 'USC',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = USC.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => $db.'.emp_proff',
            'alias' => 'Empproff',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('EmployeeDetails.emp_pkey = Empproff.emp_fkey')
        );
        $table_joins1[] = array(
            'table' => $db.'.wishes',
            'alias' => 'Wish',
            'type' => 'LEFT',
            'foreignKey' => false,
            'conditions' => array('Wish.emp_fkey = Empproff.emp_fkey', 'Wish.type' => 'Work Anniversary', 'Wish.date' => $date)
        );
        $conditions = array(
            array(
                "DATE_FORMAT(EmployeeDetails.date_of_birth, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')",
                'EmployeeDetails.status' => 1
            )
        );
        $conditions1 = array(
            array(
                "DATE_FORMAT(Empproff.joining_date, '%m-%d') = DATE_FORMAT(curdate(), '%m-%d')",
                'EmployeeDetails.status' => 1
            )
        );

       
        $this->CentralControl->setDataSource('controldb');
        $arr_company_db = $this->CentralControl->find('first', array(
            'fields' => 'CentralControl.*',
            'conditions' => array('company_code' => $company_code, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
        ));
        if (count($arr_company_db) == 1) {
            $user_name = isset($arr_company_db['CentralControl']['Admin_name']) ? $arr_company_db['CentralControl']['Admin_name'] : '';
            $user_db = isset($arr_company_db['CentralControl']['user_db']) ? $arr_company_db['CentralControl']['user_db'] : '';
            $user_pwd = isset($arr_company_db['CentralControl']['user_pwd']) ? $arr_company_db['CentralControl']['user_pwd'] : '';
            $companydb = array(
                'datasource' => 'Database/Mysql',
                'persistent' => false,
                'host' => '127.0.0.1',  //Configure::read('SERVERHOST'),
                'login' => $user_name,
                'password' => $user_pwd,
                'database' => $user_db,
                'prefix' => '',
            );
            ConnectionManager::create('companydb', $companydb);
            $this->Session->write("ds", 'companydb');

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        }

        $arr_employees_pics = array();
        $arr_employees_pics1 = array();
        try {
            $arr_employees_pics = $this->EmployeeDetails->find(
                "all",
                array(
                    "table" => $db.".emp_details AS EmployeeDetails",
                    "fields" => array("DISTINCT EmployeeDetails.emp_pkey", "EmployeeDetails.date_of_birth",  "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date", "Wish.emp_fkey"),
                    "joins" => $table_joins,
                    "order" => "EmployeeDetails.date_of_birth DESC",
                    "conditions" => $conditions
                )
            );
        } catch (Exception $e) {
           // debug($e);
        }

        try {
            $arr_employees_pics1 = $this->EmployeeDetails->find(
                "all",
                array(
                    "table" => $db.".emp_details AS EmployeeDetails",
                    "fields" => array("DISTINCT EmployeeDetails.emp_pkey", "EmployeeDetails.date_of_birth", "EmployeeDetails.emp_name", "USC.avatar", "Empproff.joining_date", "Wish.emp_fkey"),
                    "joins" => $table_joins1,
                    "order" => "Empproff.joining_date DESC",
                    "conditions" => $conditions1
                )
            );
        } catch (Exception $e) {
            //debug($e);
        }

        $today = date('Y-m-d');
        $url = 'https://v1.mypayrollmaster.online/Dashboard/convertimage';
        foreach ($arr_employees_pics as $employee) {
            $emp_pkey = isset($employee['EmployeeDetails']['emp_pkey']) ? $employee['EmployeeDetails']['emp_pkey'] : 0;
            $this->automation_convertimage($emp_pkey, 'Birthday', 'Happy birthday.');
        }
        foreach ($arr_employees_pics1 as $employee) {
            $emp_pkey = isset($employee['EmployeeDetails']['emp_pkey']) ? $employee['EmployeeDetails']['emp_pkey'] : 0;
            $this->automation_convertimage($emp_pkey, 'Work Anniversary', 'Happy work anniversary.');
        }

        if (isset($e)) {
            $succes = false;
            $message = 'Error occured while sending message';
        } else {
            $succes = true;
            $message = 'Sending wishes';
        }
        $this->Session->destroy();
        echo json_encode(array('success' => $succes, 'msg' => $message, 'emp' => count($arr_employees_pics), 'emp1' => count($arr_employees_pics1)));
    }

    public function automation_convertimage($emp_pkey = 0, $event = '', $remark = '')
    {

        $this->autorender = false;
        $this->layout = null;
        $this->render(false);


        try {

            //Edited by Aksahy on 28-11-2023
            $google_fonts = "Roboto";

            $data = array(
                'html' => '',
                'css' => '',
                'google_fonts' => $google_fonts,
                'url' => 'https://v1.mypayrollmaster.online/Dashboard/renderImageTempalte'
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://hcti.io/v1/image");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            curl_setopt($ch, CURLOPT_POST, 1);
            // Retrieve your user_id and api_key from https://htmlcsstoimage.com/dashboard
            curl_setopt($ch, CURLOPT_USERPWD, "3ffab2bd-74e7-4198-8153-263ff1c985d2" . ":" . "8ee1bf09-7791-48d6-96ae-e6ace86eafc4");

            $headers = array();
            $headers = array("Content-Type: application/x-www-form-urlencoded");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $res = json_decode($result, true);
            //var_dump($res);
            // $this->sendemailtemplatedatabase($emp_pkey, $event, $remark); 
            $this->sendemailtemplate($emp_pkey, $event, $remark); //Edited by Akshay on 28-11-2023
            // $this->sendemailtemplate($res['url']);
            // https://hcti.io/v1/image/202dc04d-5efc-482e-8f92-bb51612c84cf
        } catch (Exception $e) {
            //  debug($e);
        }
    }
    //End
     // Edited by Akshay on 4-2-2025
     public function getMenusForAbs()
     {
         $resp_menu = array();
         $emp_fkey = $this->Session->read('emp_fkey');
         $user_group = $this->Session->read('user_group');
         $this->set('user_group', $user_group);
 
         $context = isset($_REQUEST["context"]) ? $_REQUEST["context"] : "main";
         $root = isset($_REQUEST["root"]) ? $_REQUEST["root"] : 0;
 
         $conditions = array('active = "Y" ');
         $conditionss = array("(Useraccess.active = 'Y' OR EmployeeMenu.parent_id = 0) AND EmployeeMenu.active = 'Y' AND EmployeeMenu.is_default != 'M'");
         if ($context == "sub") {
             $conditions['parent_id'] = $root;
         }
         if ($user_group == 2) {
             //Admin menu
             $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
             //$this->Menu->recover('tree');
             try {
                 $menudb = $this->EmployeeMenu->find("all",                array(
                     'joins' => array(
                         array(
                             'table' => 'user_access',
                             'alias' => 'Useraccess',
                             'type' => 'INNER',
                             'conditions' => array(
                                 'EmployeeMenu.menu_id = Useraccess.menu_id'
                             )
                         )
                     ),
                     'conditions' => array(
                         $conditionss,
                         'OR' => array(
                             'Useraccess.user_fkey' => $emp_fkey,
                             'EmployeeMenu.parent_id' => 0
                         )
                     ),
                     'group' => array('EmployeeMenu.menu_id'),
                     'order' => array('EmployeeMenu.menu_id ASC')
                 ), array("conditions" => $conditionss));
             } catch (Exception $e) {
                 debug($e);
             }
 
             $menu = array();
 
             foreach ($menudb as $key => $value) {
                 $m = $value['EmployeeMenu'];
                 $arr_menu = array();
                 $arr_menu["id"] = ($context == "main") ? $m["menu_id"] : (($m["menu_url"]) ? $m["menu_url"] : "none_" . $m["menu_id"]);
                 $arr_menu["url"] = $m["menu_url"];
                 $arr_menu["text"] = $m["menu_title"];
                 $arr_menu["iconCls"] = isset($m["iconCls"]) ? $m["iconCls"] : "fa fa-user";
                 $arr_menu["leaf"] = true;
                 $arr_menu["parent_id"] = $m["parent_id"];
                 // "xf007@FontAwesome";
 
                 if (isset($menu[$m['parent_id']])) {
                     $menu[$m['parent_id']]['children'][] = $arr_menu;
                 } else {
                     $arr_menu["leaf"] = true;
                     $menu[$m['menu_id']] = $arr_menu;
                 }
             }
         }
 
         // debug($menu);
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
        
         if ($context == "main") {
             //echo json_encode($resp_menu);
             //debug($resp_menu);
             //$conditions['parent_id'] = $root;
         } else if ($root) {
             //echo json_encode($resp_menu);
             //debug($resp_menu);
         }
 
         $resp_menu = array_filter($resp_menu, function ($item) {
             return !(
                 ($item['parent_id'] == 0) &&
                 !isset($item['children'])
             );
         });
         
         return $resp_menu;
     }
     //End
      //edited by athira on 10-02-2025
    public function sendFormEmail()
    {
        $this->autoRender = false; // Disable view rendering
        $this->response->type('json'); // Set response type

        if ($this->request->is('post')) {
            try {
                // Validate and process data
                $senderEmail = $this->request->data['mail'];
                $description = $this->request->data['description'];

                if (empty($senderEmail) || empty($description)) {
                    throw new Exception("All fields are required");
                }

                if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format");
                }

                // Reuse PHPMailer config from sendemailtemplate
                App::import('Vendor', 'PHPMailer', ['file' => 'PHPMailer/PHPMailerAutoload.php']);
                $mail = new PHPMailer;
                $mail->SMTPDebug = false;
                $mail->isSMTP();
                $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
                $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Set email addresses
                $mail->setFrom('mypayrollmaster@office24.online'); // Sender address from form -verified mail id 
                $mail->addReplyTo($senderEmail); // clients mail
                $mail->addAddress('sales@greatleap.tech'); // Fixed recipient - company mail
                $mail->addBCC('projects@greatleap.tech');

                // Build email content
                $mail->Subject = "Upgrade Plan Request from $senderEmail";
                $mail->Body = "
                         <html>
                         <body>
                             <p>Dear Team,</p>
                             <p>A new upgrade request has been received.</p>
                             
                             <h3>User Details:</h3>
                             <ul>
                                 <li><strong>Email : </strong> $senderEmail</li>
                                 <li><strong>Message : </strong><br>$description</li>
                             </ul>
                             
                             <h3>Upgrade Plan Request:</h3>
                             <p>The user is interested in upgrading their current plan. Please review their request and provide further assistance.</p>
                         </body>
                         </html>
                     ";

                $mail->isHTML(true); // Send as HTML
                if (!$mail->send()) {
                    throw new Exception("Mailer Error: " . $mail->ErrorInfo);
                }

                // Success response
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Email sent successfully'
                ]);
            } catch (Exception $ex) {
                // Error response
                echo json_encode([
                    'status' => 'error',
                    'message' => $ex->getMessage()
                ]);
            }
        }
    }
     public function dashboard_old(){
        $user_group = $this->Session->read('user_group'); // edited by anukrishnan 27-01-2025



        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025

        // Edited by Akshay on 13-2025
        $this->set('user_group', $user_group);
        $this->set('company_code', $company_code);
        // End

        //edited by athira on 05-07-2025
        if ($user_group == 2  && $company_code != 'ABSG') { // Edited by Akshay on 25-1-2025
            //end
            // if (false) {// Edited by Akshay on 13-1-2025
            //$this->empdashboard();
            $this->Useraccess->useDbConfig = $this->Session->read('ds');
            $emp_fkey = $this->Session->read('emp_fkey');
            $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$emp_fkey' and menu_id = '0' and active = 'Y'");
            $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
            if ($access == 'Y') {
                //Hierarchy dashboard
                $this->hierarchydashboard();
            } else {
                //Employee dashboard
                $this->empdashboard();
                // $this->hierarchydashboard();
            }
        } else {
            //Admin dashboard

            // Edited by Akshay on 12-2-2025
            if ($user_group == '2' && ($company_code == 'ABSG')) {
                $arr_menus = $this->getMenusForAbs();
            } else {
                $arr_menus = $this->getMenus();
            }
            // $arr_menus = $this->getMenus();
            // End

            $this->set('arr_menu', $arr_menus);

            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $count = $this->EmployeeDetails->find("count", array("conditions" => array("emp_id !=" => null, 'status' => 1)));

            // edited by anukrishnan 27-01-2025 open
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $emp_pkey = $this->Session->read('emp_fkey');
                $arr_is_ho = $this->EmployeeDetails->query(
                    "SELECT get_branch_code_abs_fn(:emp_pkey) as branch",
                    ['emp_pkey' => $emp_pkey]
                );
                $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
                $conditions = [];

                if ($is_ho != 1) {
                    $conditions[] = "EmployeeDetails.branch_code = '$is_ho' AND EmployeeDetails.status = 1";
                } else {
                    $conditions = array("EmployeeDetails.status" => 1);
                }

                $count = $this->EmployeeDetails->find('count', [
                    'conditions' => $conditions
                ]);
            }
            // edited by anukrishnan 27-01-2025 close

            $this->set("total_emps", $count);

            $this->DeviceAttendance->useDbConfig = $this->Session->read('ds');
            $arr_present = $this->DeviceAttendance->query("select count(*) as presentcount from present_today");
            $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;

            // edited by anukrishnan 27-01-2025 open
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $conditions = [];
                if ($is_ho != 1) {
                    $conditions[] = "e.branch_code = '$is_ho'";
                }
                $where_clause = '';
                if (!empty($conditions)) {
                    $where_clause = 'WHERE ' . implode(' AND ', $conditions);
                }

                $arr_present = $this->DeviceAttendance->query("
                                SELECT COUNT(*) AS presentcount
                                FROM present_today p
                                JOIN emp_details e ON p.emp_pkey = e.emp_pkey
                                $where_clause
                            ");
                $present = isset($arr_present[0][0]['presentcount']) ? $arr_present[0][0]['presentcount'] : 0;
            }
            // edited by anukrishnan 27-01-2025 close

            $this->set("present", $present);
            //added by megha on 25/11/2019 presenttodayall list
            $today = date('Y-m-d');

            $arr_presentall = $this->DeviceAttendance->query("select count(*) as presentcount from present_today_all where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today'");
            // debug($arr_presentall);
            $presentall = isset($arr_presentall[0][0]['presentcount']) ? $arr_presentall[0][0]['presentcount'] : 0;
            // edited by anukrishnan 27-01-2025 open
            if ($user_group == 2 && ($company_code == 'GLET' || $company_code == 'ABSG')) {
                $conditions = '';
                if ($is_ho != 1) {
                    $conditions = " AND emp_details.branch_code = '$is_ho'";
                }
                $arr_presentall = $this->DeviceAttendance->query("select count(*) as presentcount from present_today_all
                                                                    left join emp_details on present_today_all.emp_pkey = emp_details.emp_pkey
                                                                    where STR_TO_DATE(LOGDATE, '%Y-%m-%d') = '$today' $conditions");
                $presentall = isset($arr_presentall[0][0]['presentcount']) ? $arr_presentall[0][0]['presentcount'] : 0;
                // $conditions[] = "STR_TO_DATE(DeviceAttendance.LOGDATE, '%Y-%m-%d') = '$today'";
                // $presentall = $this->DeviceAttendance->find('count', [
                //     'fields' => ['emp_id', 'DATE(DeviceAttendance.LOGDATE) AS logdate', 'COUNT(*) AS count'],
                //     'conditions' => implode(' AND ', $conditions), // Combine conditions as a string
                //     'group' => ['emp_id', 'DATE(DeviceAttendance.LOGDATE)'],
                // ]);
                if ($presentall === false) {
                    $presentall = 0;
                }
            }
            // edited by anukrishnan 27-01-2025 close
            $this->set("presentall", $presentall);

            $arr_empleaverequests = $this->listemployeeleaverequestscounts(0);
            $this->set("arr_empleaverequests", $arr_empleaverequests);

            /**
             * Fetch miss punch count of current months
             * On 04 March 2017
             */
            //            $arr_empmisspunches = $this->listemployeemisspunches();
            //            $this->set("arr_empmisspunches", $arr_empmisspunches); 
            //	//Ends
            //            $today = date('Y-m-d');
            //            $leaves = $this->Latein->query("select count(*) lea from emp_leave_transactions where Leavestatus in ('Approved') and leave_date = '$today' ");
            //            $leaves = isset($leaves['0']['0']['lea'])?$leaves['0']['0']['lea']:0;
            //            $this->set("leaves",$leaves);
        }

        //edited by sinsiya on 13-03-2025
        $this->Lateout->useDbConfig = $this->Session->read('ds');
        $todaydate = date("Y-m-d");
        $results = $this->Lateout->query("SELECT * FROM present_today_all WHERE DATE(LOGDATE) = '{$todaydate}'"); //edited by anukrishnan_10-02-2025
        /* server timezone */
        $timezone = new DateTimeZone("Asia/Kolkata");
        $date = new DateTime();
        $date->setTimezone($timezone);
        $dates = $date->format('d-m-Y H:i a');
        $empsin = array('0');
        foreach ($results as $val) {
            $empsin[] = $val['present_today_all']['emp_pkey'];
        }
        $emps = implode(',', $empsin);
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {
            $emp_fkey = $this->Session->read('emp_fkey');
            $today = $this->Lateout->query("select emp_fkey ,attr1 from (select emp_fkey,attr1 from emp_proff where emp_fkey = '$emp_fkey' union
        select emp_fkey,attr1 from emp_proff where attr1='$emp_fkey' 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey') 
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey')))
        union 
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))
        union
        select emp_fkey,attr1 from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in 
        (select emp_fkey from emp_proff where   attr1 in 
        (select emp_fkey from emp_proff where attr1 in 
        (select emp_fkey from emp_proff where  attr1 in
        (select emp_fkey from emp_proff where  attr1='$emp_fkey'))))))a order by 1");
            $empstr = array();
            foreach ($today as $key => $att) {
                $empstr[] = $att['a']['emp_fkey'];
            }
            $str = implode("','", $empstr);
            //$results1 = $this->Lateout->query("select first_name,last_name from emp_details where emp_pkey not in($emps) and emp_pkey in (select emp_fkey from emp_proff where  emp_fkey = '$emp_fkey' or attr1 ='$emp_fkey' )  and status = 1");
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and emp_pkey in ('$str') and status = 1");
            // edited by anukrishnan_ 28-01-2025 open
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->EmployeeDetails->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $emp_fkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1 AND emp_details.branch_code = '$is_ho'");
            } else {
                $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1");
            }
            // edited by anukrishnan_ 28-01-2025 close
        } else {
            $results1 = $this->Lateout->query("select first_name,last_name,emp_id,emp_proff.emp_company_id from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) where emp_pkey not in($emps) and status = 1");
        }
        $res = count($results1);
        $this->set("results1", $res);
        //end
        $this->Menu->useDbConfig = $this->Session->read('ds');
        //edited by athira on 04-02-2025
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set("plan", $plan);
    }
    //end
//    public function UserCredentials() {
//    return $this->redirect(array(
//        'controller' => 'UserCredentials',
//        'action' => 'listCredentials'
//    ));
//}
}
