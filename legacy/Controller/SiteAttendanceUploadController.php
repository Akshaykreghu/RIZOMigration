<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class SiteAttendanceUploadController extends AppController
{
    public $datatable = array();
    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'SiteAttendanceUpload';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'Units', 'CompanyInfo', 'EmployeeDetails', 'Useraccess', 'EmployeeMenu', 'SiteMaster', 'SiteAttendance', 'SiteTransactions', 'Access_site', 'SiteHistory');
    public $components = array('DatatablesManagement');
    // public $components = array('MasterdataManagement');

    public function index()
    {

        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->SiteMaster->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions = array("branch_code" => $cur_emp_branch, "status" => 1);


            //Site conditions start here
            $temp_site_keys1 = $this->SiteMaster->query("select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1");
            $temp_site_keys2 = $this->SiteMaster->query("select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and site_fkey not in (select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1) and status=1");
            $site_key = array();
            foreach ($temp_site_keys1 as $val) {
                $site_key[] = $val['site']['site_pkey'];
            }
            foreach ($temp_site_keys2 as $val) {
                $site_key[] = $val['access_site']['site_fkey'];
            }
            if (count($site_key) > 0) {
                $branch_condition = " and site_pkey in (" . implode(',', $site_key) . ") ";
            } else {
                $branch_condition = "";
            }
        } else {
            $conditions = array("status" => 1);
            $branch_condition = "";
        }
        //employee branch wise sorting ends here

        $arr_branches = $this->Units->find("all", array("conditions" => $conditions));
        //debug($arr_branches);
        $this->set("arr_branches", $arr_branches);

        // Site array
        $site_array = $this->SiteMaster->query("select * from site Where status = '1' $branch_condition ");
        $sites = "";
        foreach ($site_array as $key => $value) {
            $sites[] = array(
                'id' => $value['site']['site_pkey'],
                'text' => $value['site']['site_name'] . ' - ' . $value['site']['site_id']
            );
        }
        $this->set("arr_sites", $sites);

        $arr_designations = $this->EmployeeDetails->query("select * from designation where status = '1' ");
        $this->set('arr_designations', $arr_designations);
        
        $shift_array = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures ");
        $shift = "";
        foreach ($shift_array as $key => $value) {
            $shift[] = array(
                'id' => $value['working_day_time_procedures']['day_time_seq'],
                'text' => $value['working_day_time_procedures']['day_time_desc'] 
            );
        }
        $this->set("shift_array", $shift);
        
        $emp_array = $this->EmployeeDetails->query("select emp_pkey,EmpName,employee_id from employee_info Where emp_status = '1' ");
        $emp = "";
        foreach ($emp_array as $key => $value) {
            $emp[] = array(
                'id' => $value['employee_info']['emp_pkey'],
                'text' => $value['employee_info']['EmpName'] . ' - ' .$value['employee_info']['employee_id']
            );
        }
        $this->set("emp_array", $emp);
    }

    public function get_shift($month = '', $fkey = '', $designation_id = '')
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $site_fkey = $fkey;
        $date_att = isset($month)?date("Y-m-t", strtotime($month)):date(Y-m-d);

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and (working_day_time_procedures.day_time_desc like '%$q%' ) ";
        } else {
            $q_condition = "";
        }
        $site_condition = "";
        if ($site_fkey) {
            $site_condition = " and site_transactions.site_fkey = $site_fkey ";
        }
        $designation_condition = "";
        if ($designation_id) {
            $designation_condition = " and site_transactions.designation_id = $designation_id ";
        }
        $month_condition = "";
        if ($date_att) {
            $month_condition = " site_transactions.start_date_effective <= '$date_att' and IFNULL(site_transactions.end_date_effective, '3000-01-01') >= '$date_att' ";
        }

        $site_data = $this->Useraccess->query("select day_time_seq_fkey,day_time_desc,on_dutty1,off_dutty1,working_time1,site_transactions.start_date_effective,site_transactions.end_date_effective
                            from site_transactions join working_day_time_procedures on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) 
                            where $month_condition  $site_condition $q_condition $designation_condition and site_transactions.status = '1' group by day_time_seq_fkey");
        $array = array();
           foreach ($site_data as $key => $value) {
            $shift_fkey = $value['site_transactions']['day_time_seq_fkey'];
            $count_close = $this->Useraccess->query("select count(*) "
                . " from site_attendance "
                . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$date_att' and status = '3' and active = 1");

            if ($count_close['0']['0']['count(*)'] > 0) {
                $shift = " - Closed";
            } else {
                $shift = "";
            }
           // $site[] = array("id" => "0", "text" => "ALL");
            $site[] = array(
                'id' => $value['site_transactions']['day_time_seq_fkey'],
                'text' => $value['working_day_time_procedures']['day_time_desc'] . $shift
            );
        }

        if (!empty($site)) {
            $array['items'] = $site;
        }

        //debug($array);
        echo json_encode($array);
    }


    public function load_site_data()
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
        $date_passed = $arr_form_data['month']; //'2018-01-17';
        $site_fkey = $arr_form_data['filterby_site']; //'1';
        $shift_id = $arr_form_data['filterby_shift']; //'1';
        $designation_id = $arr_form_data['filterby_designation']; //'1';
        $branch = $arr_form_data['branch'];
        $emp_pkey = $arr_form_data['filterby_employee'];

        $limit = isset($arr_form_data['rows']) ? $arr_form_data['rows'] : 50;
        $page = isset($arr_form_data['page']) ? $arr_form_data['page'] : 1;

        // $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'emp_name';
        // $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

        $ofst = ($page - 1) * $limit;

        $branch_condition = "";

        if ($branch) {
            $branch_condition = " AND ed.branch_code='" . $branch . "'";
        }

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " AND ed.branch_code='" . $cur_emp_branch . "'";
        }

        ///////////////// COUNT QUERY STARTS HERE ///////////////
        $count_sql = "select count(*) as cnt
        from
            site_attendance sa,
            emp_details ed,
            emp_proff ep,
            designation,
            working_day_time_procedures wd,
            site s
        where 1 ";
        if ($date_passed) {
            $count_sql .= " AND att_date LIKE \"%$date_passed%\" ";
        }
        if ($site_fkey) {
            $count_sql .= " AND site_fkey = $site_fkey ";
        }
        if ($shift_id) {
            $count_sql .= " AND day_time_seq_fkey = $shift_id ";
        }
        if ($designation_id) {
            $count_sql .= " AND sa.designation_id = $designation_id ";
        }
        if ($emp_pkey) {
            $count_sql .= " AND ed.emp_pkey = $emp_pkey ";
        }

        $count_sql .= "and ep.emp_fkey = ed.emp_pkey
        and sa.site_fkey = s.site_pkey 
        and ed.emp_pkey = sa.emp_fkey
        and designation.id = sa.designation_id
        and wd.day_time_seq = day_time_seq_fkey
        and sa.active = 1 " . $branch_condition;


        $count_rows = $this->Useraccess->query($count_sql);

        ///////////////// COUNT QUERY ENDS HERE ///////////////

        $sql = "select distinct
            att_date,
            wd.day_time_desc,
            s.site_id,
            s.site_name,
            site_attendance_pkey,
            sa.emp_fkey,
            concat (`ed`.`first_name`,' ',ifnull (`ed`.`middile_name`, ''),' ',ifnull (`ed`.`last_name`, ''),'-',`ep`.`emp_company_id`) AS `EmpName`,
            ep.emp_company_id,
            working_time1,
            isnextday,
            in_time,
            out_time,
            'O' recordtype,
            sa.active,
            designation_id,sa.creation_date,
            designation.desig_code,
            designation.desig_name
        from
            site_attendance sa,
            emp_details ed,
            emp_proff ep,
            designation,
            working_day_time_procedures wd,
            site s
        where 1 ";
        if ($date_passed) {
            $sql .= " AND att_date LIKE \"%$date_passed%\" ";
        }
        if ($site_fkey) {
            $sql .= " AND site_fkey = $site_fkey ";
        }
        if ($shift_id) {
            $sql .= " AND day_time_seq_fkey = $shift_id ";
        }
        if ($designation_id) {
            $sql .= " AND sa.designation_id = $designation_id ";
        }
        if ($emp_pkey) {
            $sql .= " AND ed.emp_pkey = $emp_pkey ";
        }

        $sql .= "and ep.emp_fkey = ed.emp_pkey
            and sa.site_fkey = s.site_pkey 
            and ed.emp_pkey = sa.emp_fkey
            and designation.id = sa.designation_id
            and wd.day_time_seq = day_time_seq_fkey
            and sa.active = 1 " . $branch_condition;

        $sql .= " order by sa.site_attendance_pkey DESC LIMIT $limit OFFSET $ofst";

        // debug($sql);

        $row_emp_details = $this->Useraccess->query($sql);

        // debug($row_emp_details); exit;

        $result_array = [];
        if ($row_emp_details) {
            foreach ($row_emp_details as $items) {
                $result_array[] = array_merge($items['sa'], $items[0], $items['wd'], $items['s'], $items['ep'], $items['designation']);
            }
        }

        $resp_att = array();
        $resp_att["rows"] = $result_array;
        $resp_att["total"] = isset($count_rows) && $count_rows ? $count_rows[0][0]['cnt'] : '0';
        echo json_encode($resp_att);
    }
     public function attendance($month = '')
    {
          $this->set("month", $month);
     }
    public function form($month = '')
    {
        // The below variables should be from form function parameters
        $branch = '';
        $filterby_site = '';
        $filterby_shift = '';

        $this->set("branch", $branch);
        $this->set("month", $month);
        $this->set("filterby_site", $filterby_site);
        $this->set("filterby_shift", $filterby_shift);


        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->SiteMaster->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions = array("branch_code" => $cur_emp_branch, "status" => 1);


            //Site conditions start here
            $temp_site_keys1 = $this->SiteMaster->query("select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1");
            $temp_site_keys2 = $this->SiteMaster->query("select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and site_fkey not in (select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1) and status=1");
            $site_key = array();
            foreach ($temp_site_keys1 as $val) {
                $site_key[] = $val['site']['site_pkey'];
            }
            foreach ($temp_site_keys2 as $val) {
                $site_key[] = $val['access_site']['site_fkey'];
            }
            if (count($site_key) > 0) {
                $branch_condition = " and site_pkey in (" . implode(',', $site_key) . ") ";
            } else {
                $branch_condition = "";
            }
        } else {
            $conditions = array("status" => 1);
            $branch_condition = "";
        }
        //employee branch wise sorting ends here

        $arr_branches = $this->Units->find("all", array("conditions" => $conditions));
        //debug($arr_branches);
        $this->set("arr_branches", $arr_branches);

        $arr_designations = $this->EmployeeDetails->query("select * from designation where status = '1' ");
        $this->set('arr_designations', $arr_designations);

        // Site array
        $site_array = $this->SiteMaster->query("select * from site Where status = '1' $branch_condition ");
        $sites = "";
        foreach ($site_array as $key => $value) {
            $sites[] = array(
                'id' => $value['site']['site_pkey'],
                'text' => $value['site']['site_name'] . ' - ' . $value['site']['site_id']
            );
        }
        $this->set("arr_sites", $sites);

        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        $arr_employees = $this->EmployeeDetails->find("all", array('fields' => 'EmployeeDetails.*,EmployeeProfessionalDetails.emp_company_id,', 'conditions' => $conditions, 'joins' => $joins));
        $this->set("arr_employees", $arr_employees);
    }

    public function designationFilter($site_key = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_designations = $this->EmployeeDetails->query("select designation.* from site_transactions
        join working_day_time_procedures
        join designation
        on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey)
        where site_transactions.designation_id = designation.id
        and site_transactions.site_fkey = $site_key and site_transactions.status = '1'
        group by designation.id");

        $array = [];
        if ($arr_designations) {
            $array_designation = [];
            foreach ($arr_designations as $key => $value) {
                $array_designation[] = array(
                    'id' => $value['designation']['id'],
                    'text' => $value['designation']['desig_name']
                );
            }
            $array['items'] = $array_designation;
        }
        echo json_encode($array);
        exit;
    }

    public function branchFilter($site_key = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_branches = $this->EmployeeDetails->query("select distinct
            br.branch_code,
            br.branch_name
        from
            branches br,
            site s
        where  s.branch_code = br.branch_code
        order by s.branch_code DESC");

        $array = [];
        if ($arr_branches) {
            $array_branch = [];
            foreach ($arr_branches as $key => $value) {
                $array_branch[] = array(
                    'id' => $value['br']['branch_code'],
                    'text' => $value['br']['branch_name']
                );
            }
            $array['items'] = $array_branch;
        }
        echo json_encode($array);
        exit;
    }

    // public function designationFilter($site_fkey = '', $att_date = '', $shift_fkey = '')
    // {
    //     $this->autoRender = false;
    //     $date = date('Y-m-t', strtotime($att_date));
    //     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    //     $site_data = $this->EmployeeDetails->query("select designation_id,desig_name "
    //         . "from site_transactions "
    //         . "join working_day_time_procedures on "
    //         . "(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) "
    //         . "join designation on (site_transactions.designation_id = designation.id) "
    //         . "where site_transactions.site_fkey ='$site_fkey' "
    //         . "and site_transactions.start_date_effective <='$date' and site_transactions.end_date_effective >='$date'"
    //         . "and site_transactions.status = 1 and site_transactions.day_time_seq_fkey not in
    //          (select day_time_seq_fkey from site_attendance where att_date='$date' and site_fkey='$site_fkey' and status=3 and active = 1) group by designation_id");
    //     //  site_transactions.day_time_seq_fkey ='$shift_fkey' and 

    //     $array = [];
    //     if ($site_data) {
    //         $array_designation = [];
    //         foreach ($site_data as $key => $value) {
    //             $array_designation[] = array(
    //                 'id' => $value['site_transactions']['designation_id'],
    //                 'text' => $value['designation']['desig_name']
    //             );
    //         }
    //         $array['items'] = $array_designation;
    //     }
    //     echo json_encode($array);
    //     exit;
    // }

    public function employeefilter($branch = '', $shift_employees = false)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = "and (first_name like '%$q%' OR emp_proff.emp_company_id like '%$q%' ) "; //Added employee company id search by ***ARUL P DAS on 19/12/2019
        } else {
            $q_condition = "";
        }
        $condition = '';
        if ($branch != '') {
            $condition = " and emp_details.branch_code='$branch' ";
        }

        $shift_employees_condition = "";
        // if ($shift_employees) {
         //   $shift_employees_condition = " and emp_proff.day_time_seq is not null ";
       // }

        // $arr_site_cnt = $this->EmployeeDetails->query("select count(*) from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey)  left join branches bn on (emp_details.branch_code = bn.branch_code) where emp_details.status= '1' and bn.status ='1' $shift_employees_condition $q_condition $condition ");


        $arr_site = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_proff.emp_fkey = emp_details.emp_pkey) left join branches bn on (emp_details.branch_code = bn.branch_code) where emp_details.status= '1' and bn.status ='1' $q_condition $condition order by first_name ASC");

 

        $array = array();
        $employees = [];
        // $employees[] = array("id" => "0", "text" => "ALL");
        //***emp_pkey replaced with emp_company_id in the employee list. By ARUL P DAS on 05-11-2019***
        foreach ($arr_site as $key => $value) {
            $employees[] = array(
                'id' => $value['emp_details']['emp_pkey'],
                'text' => $value['emp_details']['first_name'] . " " . $value['emp_details']['last_name'] . " - " . $value['emp_proff']['emp_company_id']
            );
        }
        $array['items'] = $employees;
        echo json_encode($array);
    }

    // This is to save site punch bulk update
    function submitform()
    {
        $this->autoRender = FALSE;
        $arr_requestdata = $this->request->data;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $login_user_id = $this->Session->read('login_user_id');

        // Inputs from form
        $site_fkey = $arr_requestdata['form_site_filter'];
        $month = $arr_requestdata['form_month_filter'];
        $branch = $arr_requestdata['form_branch_filter'];
        $emp_pkey = $arr_requestdata['form_emp_filter'];
        $designation_id = $arr_requestdata['form_designation'];
        $shift_fkey = $arr_requestdata['form_shift_filter']; // This is the common shift for all day which is not using here

        // Other fields
        $created_by = 'Admin';
        $creation_date = date("Y-m-d H:i:S");
        $mobile_pkey = 0;
        $success_array = [];

        $error = 0;
        $success_array['message'] = [];

        if ($site_fkey != null) {
            $input_date = ($month) ? $month : date('F Y');
            $date = date('F Y', strtotime($input_date));
            $i = 0;
            while (strtotime($date) <= strtotime(date('Y-m') . '-' . date('t', strtotime($date)))) {
                $att_date = date('Y-m-d', strtotime($date)); //Date
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date))); //Adds 1 day onto current date
                // echo $day . '<br>';
                $i++;
                if ($i > 31) {
                    break;
                }

                $day_time_seq_fkey = '';
                $day_time_seq_fkey = isset($arr_requestdata['reg-date-' . $i]) ? $arr_requestdata['reg-date-' . $i] : '';
               
                if ($day_time_seq_fkey) {

                    $existing_sql1 = "select distinct att_date ,site_attendance_pkey , sa.emp_fkey,
                    concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
                    AS `EmpName`,on_dutty1,off_dutty1,working_time1,isnextday,in_time,out_time,'O' recordtype,sa.active,designation_id  
                    from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
                    where att_date ='$att_date'
                    and ep.emp_fkey=ed.emp_pkey    
                    and site_fkey = $site_fkey
                    and day_time_seq_fkey= $day_time_seq_fkey
                    and sa.designation_id = $designation_id
                    and ed.emp_pkey=sa.emp_fkey
                    and designation.id = sa.designation_id 
                    and wd.day_time_seq=day_time_seq_fkey
                    and sa.active = 1 and sa.status = 3";

                    $att_existing1 = $this->Useraccess->query($existing_sql1);

                    if (!$att_existing1) {
                    $existing_sql = "select distinct att_date ,site_attendance_pkey , sa.emp_fkey,
                    concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
                    AS `EmpName`,on_dutty1,off_dutty1,working_time1,isnextday,in_time,out_time,'O' recordtype,sa.active,designation_id  
                    from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
                    where att_date ='$att_date'
                    and ep.emp_fkey=ed.emp_pkey    
                    and site_fkey = $site_fkey
                    and day_time_seq_fkey= $day_time_seq_fkey
                    and sa.designation_id = $designation_id
                    and ed.emp_pkey=sa.emp_fkey
                    and designation.id = sa.designation_id 
                    and wd.day_time_seq=day_time_seq_fkey
                    and ed.emp_pkey = $emp_pkey
                    and sa.active = 1 and sa.status = 1";

                    $att_existing = $this->Useraccess->query($existing_sql);
                    
                    // Existing entry in site attendance. which need to update. only add checkout time
                    if ($att_existing) {
                        $site_attendance_pkey = $att_existing[0]['sa']['site_attendance_pkey'];
                        $designation_id = $att_existing[0]['sa']['designation_id'];
                        $in_times = $att_existing[0]['wd']['on_dutty1'];
                        $out_times = $att_existing[0]['wd']['off_dutty1'];
                        $isnextday = $att_existing[0]['wd']['isnextday'];

                        $in_time = $att_date . ' ' . $in_times;

                       // if ('00:00:00' <= $out_times) {
                            if (($out_times >= $in_times) && $isnextday) {
                                $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
                            } else {
                                $out_date = $att_date;
                            }
//                        } else {
                        //    $out_date = $att_date;
                       // }
                        // if ($isnextday) {
                        //     $temp_att_date = '';
                        //     $temp_att_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
                        //     $att_date = $temp_att_date;
                        // }
                        $out_time = $out_date . ' ' . $out_times;

                        $insert_cjeck_sql1 = "SELECT `mark_site_attendance_out_fn`('$site_fkey', '$day_time_seq_fkey', '$day_time_seq_fkey', '$designation_id', '$emp_pkey', null , '$out_time', '$att_date')  as resps ";
                        $row_emp_details = $this->Useraccess->query($insert_cjeck_sql1);
                        $resps = $row_emp_details[0][0]['resps'];
                        if ($resps == 'update') {
                            $user_group = $this->Session->read('user_group');
                            $user = $this->Session->read('company_code');
                            if ($user_group == 2) {
                                $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$login_user_id',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
                            } else {
                                $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$created_by',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
                            }
                            $res_save_sql = $this->Useraccess->query($save_sql);
                            //$res_save_sql1 = $this->Useraccess->query("update site_attendance set status= '3',modified_by = '$login_user_id',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ");
                           
                            // $success_array['message'] .= "<br>" . $out_time . " (Updated successfully)";
                        } else {
                            $error = 1;
                            $success_array['message'][] =  array("date" => $att_date, "error" => $resps);
                        }
                    } else {
                        // This function is to add checkin and checkout time.

                        // and designation_id = '65'

                        $not_existing_sql = "select on_dutty1,off_dutty1,isnextday,designation_id 
                        from site_transactions sa ,designation ,working_day_time_procedures wd
                        where site_fkey = $site_fkey
                        and day_time_seq_fkey= $day_time_seq_fkey
                        and sa.designation_id = $designation_id
                        and designation.id = sa.designation_id 
                        and wd.day_time_seq=day_time_seq_fkey";

                        $site_and_day_time_seq = $this->Useraccess->query($not_existing_sql);
                      
                        if ($site_and_day_time_seq) {
                            $designation_id = isset($site_and_day_time_seq[0]['sa']['designation_id']) ? $site_and_day_time_seq[0]['sa']['designation_id'] : '';
                            $in_times = $site_and_day_time_seq[0]['wd']['on_dutty1'];
                            $out_times = $site_and_day_time_seq[0]['wd']['off_dutty1'];
                            $isnextday = $site_and_day_time_seq[0]['wd']['isnextday'];
                        } 
//                        else {
//                            $error = 1;
//                            // $success_array['message'] .= "<br>" . $att_date . " (No shift policy allocated)";
//                            $success_array['message'][] =  array("date" => $att_date, "error" => "(No shift policy allocated)");
//                            continue;
//                        }

                        $out_date = '';
//                        if (($in_times > $out_times) && $isnextday) {
//                            $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
//                        } else {
//                            $out_date = $att_date;
//                        }
                        $in_time = $att_date . ' ' . $in_times;
                        $insert_cjeck_sql1 = "SELECT `mark_site_attendance_fn`('$site_fkey', 'Admin', '$day_time_seq_fkey', '$designation_id', '$emp_pkey', '$in_time', null, '$att_date')  as resps ";
                        $res_insert_cjeck_sql1_sql1 = $this->Useraccess->query($insert_cjeck_sql1);
                        $resps = $res_insert_cjeck_sql1_sql1[0][0]['resps'];
                        if ($resps == 'insert') {

                            $user_group = $this->Session->read('user_group');
                            $user = $this->Session->read('company_code');
                            $save_sql1 = "";
                            if ($user_group == 2) {
                                $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_pkey','$att_date','$in_time','$login_user_id')";
                            } else {
                                $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_pkey','$att_date','$in_time','$created_by')";
                            }
                            $res_sql1 = $this->Useraccess->query($save_sql1);

                            // This is to get last inseted Id
                            $existing_sql = "select distinct att_date ,site_attendance_pkey , sa.emp_fkey,
                            concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
                            AS `EmpName`,on_dutty1,off_dutty1,working_time1,isnextday,in_time,out_time,'O' recordtype,sa.active,designation_id  
                            from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
                            where att_date ='$att_date'
                            and ep.emp_fkey=ed.emp_pkey    
                            and site_fkey = $site_fkey
                            and day_time_seq_fkey= $day_time_seq_fkey
                            and sa.designation_id = $designation_id
                            and ed.emp_pkey=sa.emp_fkey
                            and designation.id = sa.designation_id 
                            and wd.day_time_seq=day_time_seq_fkey
                            and ed.emp_pkey = $emp_pkey
                            and sa.active = 1 and sa.status = 1";

                            $att_existing = $this->Useraccess->query($existing_sql);
                            // Existing entry in site attendance. which need to update
                            if ($att_existing) {
                                $site_attendance_pkey = $att_existing[0]['sa']['site_attendance_pkey'];
                                $designation_id = $att_existing[0]['sa']['designation_id'];
                                $in_times = $att_existing[0]['wd']['on_dutty1'];
                                $out_times = $att_existing[0]['wd']['off_dutty1'];
                                $isnextday = $att_existing[0]['wd']['isnextday'];

                              //  if ('00:00:00' <= $out_times) {
                                    if (($out_times >= $in_times) && $isnextday) {
                                        $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
                                    } else {
                                        $out_date = $att_date;
                                    }
//                                } else {
//                                    $out_date = $att_date;
//                                }
                                $out_time = $out_date . ' ' . $out_times;

                                $insert_cjeck_sql1 = "SELECT `mark_site_attendance_out_fn`('$site_fkey', '$day_time_seq_fkey', '$day_time_seq_fkey', '$designation_id', '$emp_pkey', null , '$out_time', '$att_date')  as resps ";
                                $row_emp_details = $this->Useraccess->query($insert_cjeck_sql1);

                                $resps = $row_emp_details[0][0]['resps'];

                                if ($resps == 'update') {
                                    $user_group = $this->Session->read('user_group');
                                    $user = $this->Session->read('company_code');
                                    if ($user_group == 2) {
                                        $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$login_user_id',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
                                    } else {
                                        $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$created_by',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
                                    }
                                    $res_save_sql = $this->Useraccess->query($save_sql);
                                    //$res_save_sql1 = $this->Useraccess->query("update site_attendance set status= '3',modified_by = '$login_user_id',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ");
                           
                                    // $success_array['message'] .= "<br>" . $out_time . " (" . $resps . ")";
                                } else {
                                    $error = 1;
                                    $success_array['message'][] =  array("date" => $att_date, "error" => $resps);
                                }
                            }
                            // $success_array['message'] .= "<br>" . $in_time . " (Punch in successfully completed)";
                        } else {
                            $error = 1;
                            $success_array['message'][] =  array("date" => $att_date, "error" => $resps);
                        }
                    }
                }
                 else {
                            $error = 1;
                            $success_array['message'][] =  array("date" => $att_date, "error" => "Shift Closed");
                        }
                }
            }
        }
        $success_array['error'] = $error;
        echo json_encode($success_array);
    }

    // Site attendance excel upload format download. on 15-3-23
    public function downloadsiteattendanceformat($ctcuploadtype = 0, $branch = "", $month  = "", $site = '', $shift = "")
    {

        $this->autoRender = FALSE;
        $company_code = $this->Session->read('company_code');
        $file_name = isset($company_code) ? strtolower($company_code) . "_site_attendance_uploads.xlsx" : "sitectcformat_" . time() . ".xlsx";
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file_name);
        App::import('Vendor', 'EmployeeCTCData', array('file' => 'EmployeeCTCData.php'));
        App::import('Vendor', 'PHPExcel', array('file' => 'PHPExcel.php'));
        $empctcdata = new EmployeeCTCData($ctcuploadtype);
        $emp_credentials_schema = $empctcdata->getFieldHeadings('UserCredentials');
        $emp_details_schema = $empctcdata->getFieldHeadings('EmployeeDetails');
        $emp_proff_schema = $empctcdata->getFieldHeadings('EmployeeProfessionalDetails');
        $emp_ctc_schema = $empctcdata->getFieldHeadings('EmployeeAttendanceUploadDetiled');
        $emp_schema = array_merge($emp_credentials_schema, $emp_proff_schema, $emp_details_schema, $emp_ctc_schema);

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        // $objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");

        $objPHPExcel->setActiveSheetIndex(0);

        $worksheet = $objPHPExcel->getActiveSheet();
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        // $arr_leavetypes = $this->EmployeeDetails->query("select salary_head_item_pkey,item,occurance from salary_head_items where head_fkey = 6  and value='Y' and status = '1' ");


        //////////////// HEADER ROWS START HERE ////////////////
        // Site headers are changed by arul on 16-3-2023

        $site_headers = ['Employee ID', 'Company Employee ID', 'Employee Name', 'Designation Code', 'Designation Name', 'Site Code', 'Site Name', 'Shift', 'Direction'];
        foreach ($site_headers as $column => $site_heads) {
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($column) . "1", $site_heads);
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column))->setWidth(14);
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($column) . "1")->getFont()->setBold(true);
        }

        ////////////// HEADER ROWS ENDS HERE //////////////////

        //added and commented by megha for attendance range change for sh infra
        $month1 =  $month . '-01';
        $att_startdate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->EmployeeDetails->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = strtotime($att_startdate['0']['0']['monthly_att_fromdate']);
        $att_enddate1 = strtotime($att_enddate['0']['0']['monthly_att_todate']);

        $att_enddate2 =  strtotime("+1 day", $att_enddate1);
        $begin = new DateTime(date('Y-m-d', $att_startdate1));
        $end = new DateTime(date('Y-m-d', $att_enddate2));
        $interval = new DateInterval('P1D'); // 1 Day
        $dateRange = new DatePeriod($begin, $interval, $end);
        $format = "Y-m-d";
        $range = [];
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }

        // $user_login = $this->Session->read('login_user_id');
        // $yearmonth = $month . '-01';
        // $query = "CALL insert_update_att_reg('$company_code','$branch','$user_login','$yearmonth',@Perr_msg);";
        // $this->EmployeeDetails->query($query);
        // try {

        //     $attend = $this->EmployeeDetails->query("Call emp_detail_att_reg('$company_code','$branch','$user_login','$yearmonth','$emp_pkey', @`Perr_msg`)");
        // } catch (Exception $ex) {
        // }
        //Fill form with existing users 
        // $emp_credentials_fields = $empctcdata->getFieldNames('UserCredentials');
        // $emp_details_fields = $empctcdata->getFieldNames('EmployeeDetails');
        // $emp_ctc_fields = $empctcdata->getFieldNames('EmployeeCTC');
        // $emp_fields = array_merge(array_keys($emp_credentials_fields), array_keys($emp_details_fields), array_keys($emp_ctc_fields));

        // if (isset($emp_pkey) && !empty($emp_pkey)) {
        //     $condtion[] = "  EmployeeDetails.emp_pkey='$emp_pkey'";
        // }

        //////////////// DATE RANGE STARTS HERE ////////////////
        $col = 9; // 4
        foreach ($range as $vals) {
            $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($col) . "1", $vals);
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth(14);
            $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . "1")->getFont()->setBold(true);
            $col++;
        }
        //////////////// DATE RANGE ENDS HERE ////////////////

        $condtion = "";
        if (isset($branch) && !empty($branch)) {
            $condtion[] = "  EmployeeDetails.branch_code='$branch'";
        }

        $arr_empdetails = $this->EmployeeDetails->find('all', array(
            'fields' => 'UserCredentials.user_id,EmployeeProff.emp_company_id, concat(EmployeeDetails.first_name," ",EmployeeDetails.last_name) as Name,Designation.desig_code,Designation.desig_name,Site.site_id,Site.site_name,DayTimeProcedures.day_time_desc',
            'joins' => array(
                array(
                    'table' => 'user_credentials',
                    'alias' => 'UserCredentials',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = UserCredentials.emp_fkey')
                ),
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProff',
                    'type' => 'INNER',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProff.emp_fkey')
                ),
                array(
                    'table' => 'access_site',
                    'alias' => 'AccessSite',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = AccessSite.emp_fkey', 'AccessSite.status = 1')
                ),
                array(
                    'table' => 'site',
                    'alias' => 'Site',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('AccessSite.site_fkey = Site.site_pkey', 'Site.status = 1')
                ),
                array(
                    'table' => 'working_day_time_procedures',
                    'alias' => 'DayTimeProcedures',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeProff.day_time_seq = DayTimeProcedures.day_time_seq')
                ),
                array(
                    'table' => 'designation',
                    'alias' => 'Designation',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeProff.designation = Designation.desig_code', 'Designation.status = 1')
                )
            ),
            'conditions' => array(
                'EmployeeDetails.status' => 1, $condtion,
                'Site.site_pkey' => $site, 'EmployeeProff.day_time_seq' => $shift

            )
        ));

        // debug($arr_empdetails);return;

        $rowindex = 2;
        $columnindex = 0;

        if ($arr_empdetails) {
            foreach ($arr_empdetails as $rows) {
                $columnindex = 0;


                /////////////// IN TIME DETAILS STARTS HERE ///////////////

                foreach ($rows as $columns) {
                    $user_ids = isset($columns['user_id']) ? $columns['user_id'] : 0;
                    foreach ($columns as $column) {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $columnindex++;
                    }
                    $user_ids = isset($rows['UserCredentials']['user_id']) ? $rows['UserCredentials']['user_id'] : 0;
                }
                $arr_user_pkeys = $this->EmployeeDetails->query("select emp_fkey from user_credentials where user_id = '$user_ids' ");
                $emp_pkey = isset($arr_user_pkeys['0']['user_credentials']['emp_fkey']) ? $arr_user_pkeys['0']['user_credentials']['emp_fkey'] : 0;

                ////////////////////THIS IS IN TIME////////////////////////
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, "in");
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $columnindex++;



                ///////////////////////// IN TIME SHOULD BE HERE //////////////////////////////

                ///////////////////////// IN TIME SHOULD BE HERE //////////////////////////////




                $yearmonths = date("Y-m-1", strtotime($month));
                $month = date('Y-m', strtotime($yearmonths));

                // $test_empdetaileattendance = $this->EmployeeDetails->query("select * from `attendance_register` where `emp_fkey` = '$emp_pkey' AND `month_year` = '$month'");


                //////////////////////// OUT TIME ROW STARTS HERE ////////////////////////
                $rowindex++;
                $columnindex = 0;
                foreach ($rows as $columns) {
                    foreach ($columns as $column) {
                        $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, $column);
                        $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                        $columnindex++;
                    }
                }

                /////////////////////////OUT//////////////////////////////
                $objPHPExcel->getActiveSheet()->SetCellValue(PHPExcel_Cell::stringFromColumnIndex($columnindex) . $rowindex, "out");
                $objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($columnindex))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $columnindex++;



                ///////////////////////// OUT TIME SHOULD BE HERE //////////////////////////////

                ///////////////////////// OUT TIME SHOULD BE HERE //////////////////////////////


                $rowindex++;
            }
        }


        $objPHPExcel->getActiveSheet()->setTitle('Employee Attendance Upload Data');

        ///////////////////////// HELP PAGE STARTS HERE //////////////////////////////

        // $objWorkSheet = $objPHPExcel->createSheet(2);
        // $objWorkSheet->getStyle('A1')->getFont()->setBold(true);
        // $objWorkSheet->getColumnDimension('B')->setWidth(80);
        // $objWorkSheet->getStyle('B1')->getFont()->setBold(true);
        // $cll = 2;

        // $objWorkSheet->setCellValue('B1', 'Help');
        // $objWorkSheet->setCellValue('B3', 'Time format should be in hh:mm 24 hrs format ');

        // $objWorkSheet->setCellValue('D10', 'Color Fill');
        // $objWorkSheet->setTitle('Help');

        $objPHPExcel->getProperties()->setCreator("Administrator");
        $objPHPExcel->getProperties()->setLastModifiedBy("Administrator");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Employee Data Format By Forsight");
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(dirname(__FILE__) . "/" . $file_name);
        readfile(dirname(__FILE__) . "/" . $file_name);
        unlink(dirname(__FILE__) . "/" . $file_name);
    }
}
