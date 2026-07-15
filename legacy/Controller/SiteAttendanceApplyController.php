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

App::uses('ConnectionManager', 'Model', 'Organization');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class SiteAttendanceApplyController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    // public $name = 'SiteAttendance';
    public $name = 'SiteAttendanceApply';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Useraccess', 'EmployeeMenu', 'SiteMaster', 'SiteAttendance', 'SiteTransactions', 'Access_site', 'SiteHistory', 'SiteMasterApprovalDetails', 'SiteMasterApproval');
    public $components = array('MasterdataManagement');

    public function index()
    {
        //        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');

        //$arr_employee = $this->EmployeeDetails->find('all', array('conditions' => array('status' => 1)));
        //$this->set('arr_employee', $arr_employee);
        //        $arr_order = array('parent_id ASC');
        //        $arr_parent = $this->EmployeeMenu->find("all", array(
        //            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
        //            'order' => $arr_order,
        //            'conditions' => array('EmployeeMenu.parent_id = 0', 'EmployeeMenu.active' => 'Y')
        //        ));
        //
        //        $arr_child = $this->EmployeeMenu->find("all", array(
        //            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
        //            'order' => $arr_order,
        //            'conditions' => array('EmployeeMenu.parent_id != 0', 'EmployeeMenu.active' => 'Y')
        //        ));
        //        $user_group = $this->Session->read('user_group');
        //
        //        $this->set('user_group', $user_group);
        //        $this->set('arr_parent', $arr_parent);
        //        $this->set('arr_child', $arr_child);
    }


    public function filtersite()
    {
        $this->autoRender = false;
        $this->SiteMaster->useDbConfig = $this->Session->read('ds');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = " and ( site_name like '%$q%' or site_id like '%$q%') ";
        } else {
            $q_condition = "";
        }

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
          
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
            $branch_condition = "";
        }
        //employee branch wise sorting ends here

        $site_array = $this->SiteMaster->query("select * from site Where status = '1' $q_condition $branch_condition ");

        $array = array();
        $sites = "";

        foreach ($site_array as $key => $value) {

            $sites[] = array(
                'id' => $value['site']['site_pkey'],
                'text' => $value['site']['site_name'] . ' - ' . $value['site']['site_id']
            );
        }
        $array['items'] = $sites;
        echo json_encode($array);
    }


    public function form($site_pkey = 0)
    {
        $user_id = $this->Session->read('login_user_id'); // Edited by Akshay on 28-7-2023
        $user_group = $this->Session->read('user_group'); // Edited by Akshay on 12-8-2023
        $this->set('user_id', trim($user_id));
        $this->set('user_group', trim($user_group));
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_designations = $this->EmployeeDetails->query("select * from designation where status = '1' ");
        $this->set('arr_designations', $arr_designations);

        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);

        $arr_contacts = $this->EmployeeDetails->query("select * from contacts where status = '1' and relationship != '' order by company_name Asc");
        $this->set('arr_contacts', $arr_contacts);

        $arr_branches = $this->EmployeeDetails->query("SELECT `branch_code`, `branch_name`, `status` FROM `branches` WHERE `status` = '1'");
        $this->set('arr_branches', $arr_branches);

        $arr_mode = $this->EmployeeDetails->query("select payment_mode_pkey,mode from payment_mode where status = '1'");
        $this->set('arr_mode', $arr_mode);

        if ($site_pkey != 0) {
            //          
            $arr_sites = $this->EmployeeDetails->query("select site.*,contacts.*,concat(emp_details.first_name,' ',ifnull(emp_details.last_name,'')) as manager,branches.branch_code "
                . "from site left join emp_details on (emp_details.emp_pkey = site.user_pkey) left join contacts on (contacts.contact_id = site.contact_name) "
                . "left join branches on (site.branch_code=branches.branch_code) where site_pkey = '$site_pkey'  ");
            // debug($arr_sites);exit;
            $this->set('arr_sites', $arr_sites);
            $arr_sites_transscv = $this->EmployeeDetails->query("select site_transactions.*,wd.day_time_desc,wd.day_time_seq,designation.desig_code,designation.id,designation.desig_name from site_transactions
            join working_day_time_procedures wd on (wd.day_time_seq = site_transactions.day_time_seq_fkey)
            left join designation on (designation.id = site_transactions.designation_id)
            where site_fkey = '$site_pkey'  and site_transactions.status = '1'  ");
            $this->set('arr_sites_transscv', $arr_sites_transscv);
        }
    }

    public function form2($siteid = 0)
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($siteid != 0) {
            $arr_policy = $this->EmployeeDetails->query("select * from site_transactions where status = '1' and site_transactions_pkey = '$siteid' ");
            $this->set('arr_policy', $arr_policy);
        }

        $arr_designations = $this->EmployeeDetails->query("select * from designation where status = '1' ");
        $this->set('arr_designations', $arr_designations);

        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);
    }

    public function check_start_date($date = '', $site_transaction_fkey = 0, $site_fkey = 0, $day_time_seq = 0)
    {
        $this->autoRender = false;
        $this->SiteTransactions->useDbConfig = $this->Session->read('ds');
        $site_attendance_count = $this->SiteTransactions->query("SELECT COUNT(*) as count FROM `site_attendance` WHERE `site_fkey` = '$site_fkey' AND `day_time_seq_fkey` = '$day_time_seq' AND `status` = '3' AND att_date<'$date'");
        $site_attendance = $site_attendance_count[0][0]['count'];
        $resp = array();
        $resp["success"] = false;
        $emp_site_detail_timeattendance_count = $this->SiteTransactions->query("SELECT COUNT(*) as count FROM `emp_site_detail_timeattandance` WHERE `site_transactions_fkey` = '$site_transaction_fkey' AND att_date<'$date'");
        $emp_site_detail_timeattendance = $emp_site_detail_timeattendance_count[0][0]['count'];
        if ($site_attendance != '0' || $emp_site_detail_timeattendance != '0') {
            $resp["success"] = true;
            $resp["msg"] = "Cannot set this date as Start Date. Data exist in the previous few days.";
        }
        echo json_encode($resp);
    }

    public function check_end_date($date = '', $site_transaction_fkey = 0, $site_fkey = 0, $day_time_seq = 0)
    {
        $this->autoRender = false;
        $this->SiteTransactions->useDbConfig = $this->Session->read('ds');
        $site_attendance_count = $this->SiteTransactions->query("SELECT COUNT(*) as count FROM `site_attendance` WHERE `site_fkey` = '$site_fkey' AND `day_time_seq_fkey` = '$day_time_seq' AND att_date>'$date'  AND `status` = '3'");
        $site_attendance = $site_attendance_count[0][0]['count'];
        $resp = array();
        $resp["success"] = false;
        $emp_site_detail_timeattendance_count = $this->SiteTransactions->query("SELECT COUNT(*) as count FROM `emp_site_detail_timeattandance` WHERE `site_transactions_fkey` = '$site_transaction_fkey' AND att_date>'$date'");
        $emp_site_detail_timeattendance = $emp_site_detail_timeattendance_count[0][0]['count'];
        if ($site_attendance != '0' || $emp_site_detail_timeattendance != '0') {
            $resp["success"] = true;
            $resp["msg"] = "Cannot set this date as End Date. Data exist the next few days.";
        }
        echo json_encode($resp);
    }

    public function insec($emp_pkey = 0)
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $arr_order = array('parent_id ASC');
        $arr_parent = $this->EmployeeMenu->query("select u.status,EmployeeMenu.menu_id,parent_id,menu_url,menu_title,menu_name,u.user_access_pkey,if (lcase(u.active) ='y','Y','N') as active
 from emp_menu EmployeeMenu  left join user_access as u on(u.menu_id = EmployeeMenu.menu_id) and u.user_fkey='$emp_pkey' and u.active='Y' where EmployeeMenu.parent_id = 0 and EmployeeMenu.active = 'Y'");
        $adminid = '0';
        $fetchUser = $this->Useraccess->query("select * from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$emp_pkey' ");
        $this->set('fetchUser', $fetchUser);
        $arr_child = $this->EmployeeMenu->query("select EmployeeMenu.menu_id,parent_id,menu_url,menu_title,menu_name,u.user_access_pkey,if (lcase(u.active) ='y','Y','N') as active from emp_menu EmployeeMenu  left join user_access as u on(u.menu_id = EmployeeMenu.menu_id) and u.user_fkey=$emp_pkey and u.active='Y' where u.active = 'Y' and EmployeeMenu.parent_id != 0 ");
        $this->set('arr_parent', $arr_parent);
        $this->set('arr_child', $arr_child);
        $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and active = 'Y' ");
        $this->set('arr_useraccess', $arr_useraccess);
    }

    public function lists($param = "") //Edited by Akshay on 12-8-2023
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $offset = ($page - 1) * $limit;
        $resp_data = array();

        $this->autoRender = false;
        $arr_data = $this->request->data;
        //added by megha on 19_06_19
        $categorypkey = isset($arr_data['site']) ? $arr_data['site'] : '0';
        $siteenddate = isset($arr_data['siteenddate']) ? $arr_data['siteenddate'] : '0';
        if ($categorypkey == 0) {
            $cond = "";
        } else {
            $cond = " and site.site_pkey  = $categorypkey ";
        }
        if ($siteenddate == 0) {
            $cond1 = "";
        } else if ($siteenddate > 0) {
            $cond1 = " and DATEDIFF(end_date_effective,now()) > 0 and DATEDIFF(end_date_effective,now()) < $siteenddate ";
        } else {
            $cond1 = " and DATEDIFF(end_date_effective,now()) < 0  ";
        }

        //end
        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $temp_site_keys1 = $this->Useraccess->query("select distinct site_pkey from site left join site_transactions on(site.site_pkey = site_transactions.site_fkey)  where user_pkey=" . $cur_emp_key . " $cond1 and site.status=1 and site_transactions.status=1");

            $temp_site_keys2 = $this->Useraccess->query("select distinct access_site.site_fkey from access_site left join site on(site.site_pkey = access_site.site_fkey) "
                . "left join site_transactions on(site.site_pkey = site_transactions.site_fkey) where emp_fkey=" . $cur_emp_key . " and access_site.site_fkey not in (select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1) $cond1 and site.status=1 and site_transactions.status=1
                    and access_site.status = 1");
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
                // $branch_condition = "";
                $branch_condition = " and site_pkey in (0) ";
            }
        } else {
            $branch_condition = "";
        }
        //employee branch wise sorting ends here

        $sortcolumn = isset($arr_data['sort']) ? $arr_data['sort'] : '';

        $sortorder = isset($arr_data['order']) ? $arr_data['order'] : '';

        $user_fkey = isset($arr_data['user_fkey']) ? $arr_data['user_fkey'] : '9';

        $arr_useraccess = $this->Useraccess->query("select distinct site.site_pkey, ifnull((select sum(emp_count) from site_transactions where site_fkey = site.site_pkey and status = '1' ),0) as counts ,site.* from site "
            . " left join site_transactions on(site.site_pkey = site_transactions.site_fkey) "
            . " where site.status = '1' and site_transactions.status =1 $cond $cond1 $branch_condition ORDER BY site.site_pkey DESC limit $limit offset $offset ");


        if ($sortcolumn != '' && $sortorder !== '') {
            $arr_order = array($sortcolumn . ' ' . $sortorder);
        } else {
            $arr_order = array('site_pkey DESC');
        }
        $totalcount = $this->Useraccess->query("SELECT count(distinct(site_pkey)) as cnt FROM `site` left join site_transactions on(site.site_pkey = site_transactions.site_fkey) "
            . "where site.status = '1' and site_transactions.status =1 $cond $cond1 $branch_condition ");

        $rows = array();
        foreach ($arr_useraccess as $key => $val) {
            $rows[] = array_merge($val['site'], $val['0']);
        }

        $resp_data["total"] = $totalcount['0']['0']['cnt'];
        $resp_data["rows"] = $rows;
        echo json_encode($resp_data);
    }
    public function save($emp_pkey = '', $s = '')
    {
        $this->autoRender = false;
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        if ($s == 'All') {
            $where = '';
            $wh = '';
        } else {
            $where = "and parent_id = $s or menu_id = '$s' ";
            $wh = "";
        }
        $par = $this->EmployeeMenu->query("select menu_id from emp_menu where parent_id = '$s' $wh ");

        if (count($par) > 0) {
            $ch = $this->EmployeeMenu->query("select menu_id from emp_menu where active = 'Y' $where ");

            foreach ($ch as $menu) {
                $this->Useraccess->useDbConfig = $this->Session->read('ds');
                $arr_form_data = array();
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = '';
                $arr_form_data['menu_id'] = '';
                $arr_form_data['active'] = '';
                $arr_form_data['status'] = '0';
                $id = $menu['emp_menu']['menu_id'];
                $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $id ");
                if (count($arr_useraccess) > 0) {

                    $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
                }
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = $emp_pkey;
                $arr_form_data['menu_id'] = $id;
                $arr_form_data['active'] = 'Y';

                $this->Useraccess->saveAll($arr_form_data);
            }
        } else {
            $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $s ");

            if (count($arr_useraccess) > 0) {

                $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
            }
            $arr_form_data['organization_id'] = '1';
            $arr_form_data['user_fkey'] = $emp_pkey;
            $arr_form_data['menu_id'] = $s;
            $arr_form_data['active'] = 'Y';
            $arr_form_data['status'] = '1'; //set ! if no submenu

            $this->Useraccess->save($arr_form_data);
        }
        echo json_encode(array('msg' => 'Useraccess saved successfully'));
    }

    public function addDefault($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($outs = $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function resetDefault($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $removes = $this->EmployeeDetails->query("UPDATE user_access set active = 'N' where user_fkey = '$emp_fkey' and active = 'Y' ");
        if ($outs = $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function deletemens($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($removes = $this->EmployeeDetails->query("UPDATE user_access set active = 'N' where user_fkey = '$emp_fkey' and active = 'Y' ")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function listuseraccess()
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');

        $resp_data = array();

        $this->autoRender = false;
        $arr_data = $this->request->data;

        $offset = ($page - 1) * $limit;

        $sortcolumn = isset($arr_data['sort']) ? $arr_data['sort'] : '';

        $sortorder = isset($arr_data['order']) ? $arr_data['order'] : '';

        $user_fkey = isset($arr_data['user_fkey']) ? $arr_data['user_fkey'] : '9';

        $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $user_fkey and active = 'Y' ");


        $arr_active_menus = array();
        foreach ($arr_useraccess as $key => $val) {
            $arr_active_menus[] = $val['user_access']['menu_id'];
        }

        if ($sortcolumn != '' && $sortorder !== '') {
            $arr_order = array($sortcolumn . ' ' . $sortorder);
        } else {
            $arr_order = array('parent_id ASC');
        }
        $totalcount = $this->EmployeeMenu->find("count", array(
            'conditions' => array('EmployeeMenu.parent_id != 0')
        ));

        $arr_menu = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array(/* 'EmployeeMenu.parent_id != 0', */'EmployeeMenu.active' => 'Y'),
            'offset' => $offset,
            'limit' => $limit
        ));

        $rows = array();

        foreach ($arr_menu as $key => $val) {
            $val['EmployeeMenu']['parent'] = $val[0]['parent'];
            if (in_array($val['EmployeeMenu']['menu_id'], $arr_active_menus)) {
                $val['EmployeeMenu']['accessallow'] = 'Y';
            } else {
                $val['EmployeeMenu']['accessallow'] = 'N';
            }
            $rows[] = $val['EmployeeMenu'];
        }

        $resp_data["total"] = $totalcount;
        $resp_data["rows"] = $rows;




        echo json_encode($resp_data);
    }

    public function delete($emp_pkey = '', $s = '')
    {
        $this->autoRender = false;
        if ($s == 'All') {
            $where = '';
            $wh = '';
        } else {
            $where = "and parent_id = $s or menu_id = '$s' ";
            $wh = "and menu_id = $s";
        }

        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        $par = $this->EmployeeMenu->query("select menu_id from emp_menu where parent_id = '0' $wh ");

        if (count($par) > 0) {
            $ch = $this->EmployeeMenu->query("select menu_id from emp_menu where active = 'Y'  $where ");

            foreach ($ch as $menu) {
                $this->Useraccess->useDbConfig = $this->Session->read('ds');
                $arr_form_data = array();
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = '';
                $arr_form_data['menu_id'] = '';
                $arr_form_data['active'] = '';

                $id = $menu['emp_menu']['menu_id'];

                $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $id ");

                if (count($arr_useraccess) > 0) {

                    $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
                }
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = $emp_pkey;
                $arr_form_data['menu_id'] = $id;
                $arr_form_data['active'] = 'N';

                $this->Useraccess->saveAll($arr_form_data);
            }
        } else {
            $arr_useraccess = $this->Useraccess->query("select * from user_access where user_fkey = $emp_pkey and menu_id = $s ");


            $array_men = explode(',', $s);


            if (count($arr_useraccess) > 0) {


                $arr_form_data['user_access_pkey'] = $arr_useraccess['0']['user_access']['user_access_pkey'];
                $arr_form_data['organization_id'] = '1';
                $arr_form_data['user_fkey'] = $emp_pkey;
                $arr_form_data['menu_id'] = $s;
                $arr_form_data['active'] = 'N';
                $this->Useraccess->save($arr_form_data);
            }
        }
        echo json_encode(array('msg' => 'Useraccess saved successfully'));
    }

    public function Employee($user_pkey = 0, $DD = '')
    {
        $this->autoRender = false;

        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        $adminid = '#';


        $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

        $pkey = $fetchUser['0']['Useraccess']['user_access_pkey'];

        if ($pkey != '') {
            $arr_useraccess_data['user_access_pkey'] = $pkey;
        }
        $arr_useraccess_data['organization_id'] = '1';
        $arr_useraccess_data['user_fkey'] = $user_pkey;
        $arr_useraccess_data['menu_id'] = $adminid;
        if ($DD == "EMPLOYEE") {
            $arr_useraccess_data['active'] = 'Y';
        } else {
            $arr_useraccess_data['active'] = 'N';
        }
        $arr_useraccess_data['status'] = '1';


        $this->Useraccess->save($arr_useraccess_data);
    }

    public function shift_delete_check($site_pkey = 0, $site_transaction_fkey = 0, $TdDayTime = 0)
    {
        $this->autoRender = false;
        $this->SiteTransactions->useDbConfig = $this->Session->read('ds');

        $resp = array();
        $resp["success"] = true;
        $site_attendance_count = $this->SiteTransactions->query("SELECT COUNT(*) as count FROM `site_attendance` WHERE `site_fkey` = '$site_pkey' AND `day_time_seq_fkey` = '$TdDayTime' ");
        $emp_site_detail_timeattendance_count = $this->SiteTransactions->query("SELECT COUNT(*) as count FROM `emp_site_detail_timeattandance` WHERE `site_fkey` = '$site_pkey' AND `site_transactions_fkey` = '$site_transaction_fkey'");
        $site_attendance = $site_attendance_count[0][0]['count'];
        $emp_site_detail_timeattendance = $emp_site_detail_timeattendance_count[0][0]['count'];
        if ($site_attendance != '0' || $emp_site_detail_timeattendance != '0') {
            $resp["success"] = false;
            $resp["msg"] = "This shift cannot be delete. Because it have data. Please close the shift by edit End Date as current date.";
        }
        echo json_encode($resp);
    }

    public function saveSite()
    {
        $this->autoRender = false;
        $arr_data = $this->request->data;
        $this->SiteHistory->useDbConfig = $this->Session->read('ds');
        $this->SiteMaster->useDbConfig = $this->Session->read('ds');
        $this->SiteTransactions->useDbConfig = $this->Session->read('ds');
        $this->SiteMasterApproval->useDbConfig = $this->Session->read('ds');
        $this->SiteMasterApprovalDetails->useDbConfig = $this->Session->read('ds');

        //Edited by Akshay on 9-8-2023
        // Fetch existing data from the database based on the site_pkey
        $pkey = isset($arr_data['site_pkey']) ? $arr_data['site_pkey'] : '';
        if ($pkey != '') {
            $existingSiteData = $this->SiteMaster->findBySitePkey($pkey);
        }
        $user = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $current_time = date('Y-m-d H:i:s'); //Ended


        $arr_form_data = array();
        $arr_data_count = count($arr_data['TdDayTime']);

        for ($i = 0; $i < $arr_data_count; $i++) {

            if ($arr_data['editkey'][$i] == 0) {
                if ($arr_data['site_pkey']) {
                    $arr_form_data['site_pkey'] = isset($arr_data['site_pkey']) ? $arr_data['site_pkey'] : '';
                }

                $arr_form_data['site_id'] = $arr_data['site_id'];
                $arr_form_data['site_name'] = $arr_data['site_name'];
                $arr_form_data['branch_code'] = $arr_data['branch_code'];
                $arr_form_data['latitude'] = $arr_data['latitude'];
                $arr_form_data['longitude'] = $arr_data['longitude'];
                $arr_form_data['address'] = $arr_data['address'];
                $arr_form_data['customer_name'] = $arr_data['customer_name'];
                $arr_form_data['user_pkey'] = $arr_data['user_pkey'];
                $arr_form_data['contact_name'] = $arr_data['contact_name'];
                $arr_form_data['customer_contact'] = $arr_data['customer_contact'];
                $arr_form_data['special_remarks'] = $arr_data['special_remarks'];
                $arr_form_data['payment_mode'] = isset($arr_data['payment_mode']) ? $arr_data['payment_mode'] : '';

                // Minimum days for site punch attendance bulk update. by arul on 16-3-23
                $arr_form_data['min_days_before'] = isset($arr_data['min_days_before']) ? $arr_data['min_days_before'] : '';


                $arr_form_data['organization_id'] = isset($arr_data['organization_id']) ? 1 : 1;

                $this->SiteMaster->save($arr_form_data);

                $site_pkey = $this->SiteMaster->getLastInsertId();;
            }
        }


        //Edited by Akshay on 9-8-2023
        if (isset($existingSiteData)) {
            $changes = array();
            $existing = array();

            foreach ($arr_form_data as $key => $value) {
                if (isset($existingSiteData['SiteMaster'][$key]) && $existingSiteData['SiteMaster'][$key] != $value) {
                    // Add the field and its new value to the $changes array
                    $changes[$key] = $value;
                    $existing[$key] = $existingSiteData['SiteMaster'][$key];

                    // Save the changes in the site_master_approval_details table

                }
            }

            if (count($changes) > 0) {
                $approvalData = [
                    'site_fkey' => $pkey,
                    'created_by' => $user,
                    'created_date' => $current_time,
                    'remarks' => 'Approved by Admin',
                    'status' => 'approved'
                ];
                $this->SiteMasterApproval->save($approvalData);
                // Get the newly inserted record from the database

                $newlyInsertedRecord = $this->SiteMasterApproval->find('first', [
                    'conditions' => ['site_master_approval_pkey' => $this->SiteMasterApproval->id]
                ]);

                $siteMasterApprovalDetailsData = array();
                foreach ($changes as $key => $change) {
                    $siteMasterApprovalDetailsData[] = [
                        'site_master_approval_fkey' => $newlyInsertedRecord['SiteMasterApproval']['site_master_approval_pkey'], // Assuming you have the primary key of site_master_approval here
                        'fieldname' => $key,
                        'old_value' => $existing[$key],
                        'new_value' => $change,
                        'created_by' => $user, // You need to set the correct user ID or name here
                        'created_date' => date('Y-m-d H:i:s'), // Current timestamp
                        'type' => 'general',
                        'status' => 'approved'
                    ];
                }
            }
        }

        if (isset($siteMasterApprovalDetailsData)) {

            if (count($siteMasterApprovalDetailsData) > 0) {
                foreach ($siteMasterApprovalDetailsData as $smaData) {
                    // Save the record in the site_master_approval_details table
                    $this->SiteMasterApprovalDetails->create();
                    $this->SiteMasterApprovalDetails->save($smaData);
                }
            }
        } //Ended



        for ($i = 0; $i < $arr_data_count; $i++) {

            if ($arr_data['editkey'][$i] == 0) {
                $site_data = array();
                if ($arr_data['site_pkey']) {
                    $site_data['site_fkey'] = $arr_data['site_pkey'];
                } else {
                    $site_data['site_fkey'] = $site_pkey;
                }
                if (isset($arr_data['site_transaction_fkey'][$i])) {
                    $site_data['site_transactions_pkey'] = $arr_data['site_transaction_fkey'][$i];
                }

                $site_data['day_time_seq_fkey'] = $arr_data['TdDayTime'][$i];
                $site_data['designation_id'] = $arr_data['TdDesg'][$i];
                $site_data['status'] = $arr_data['status'][$i];
                $site_data['emp_count'] = $arr_data['TdCount'][$i];
                $site_data['srate'] = $arr_data['TdSRate'][$i];
                $site_data['eratess'] = $arr_data['Rate'][$i];
                $site_data['start_date_effective'] = $arr_data['StDE'][$i];
                $site_data['end_date_effective'] = $arr_data['EtDE'][$i];
                $site_data['created_by'] = $this->Session->read('login_user_id');
                if ($arr_data['site_pkey'] == '') {
                    $site_data['action'] = "i";
                } else {
                    $site_data['action'] = "u";
                }

                $this->SiteTransactions->saveAll($site_data);
                $this->SiteHistory->saveAll($site_data);


                //Edited by Akshay on 9-8-2023
                $history_pkey = $this->SiteHistory->getInsertID();

                $arr_shift_data = $this->SiteHistory->query("SELECT site_transactions_pkey, site_fkey, status
                                                                FROM site_history 
                                                                WHERE site_history_pkey = $history_pkey ");
                foreach ($arr_shift_data as $data) { //For new whift
                    $site_pkey = $data['site_history']['site_fkey'];
                    $st_fkey = $data['site_history']['site_transactions_pkey'];

                    if ($data['site_history']['site_transactions_pkey'] == 0 && $data['site_history']['status'] == 1) {

                        $data1 = [
                            'site_fkey' => $site_pkey,
                            'site_transactions_fkey' => $st_fkey,
                            'created_by' => $user,
                            'created_date' => $current_time,
                            'remarks' => 'Approved by Admin',
                            'status' => 'approved'
                        ];

                        $insertSma = $this->SiteMasterApproval->save($data1);

                        $site_master_approval_pkey = $this->SiteMasterApproval->getLastInsertID();

                        $insertSmd = $this->SiteMasterApproval->query("INSERT INTO site_master_approval_details (site_master_approval_fkey, fieldname, old_value, new_value, created_by, created_date, type, status )
                        VALUES ( $site_master_approval_pkey,`status`, 0,1, '$user','$current_time', 'shift', 'approved' )");

                        $updateSiteHistory =  $this->SiteMasterApproval->query("UPDATE site_history SET site_master_approval_fkey = $site_master_approval_pkey, approved_status = 'Approved by Admin'
                                                                                    WHERE site_history_pkey = $history_pkey");
                    } elseif ($data['site_history']['status'] == 0) { //Delete shift

                        $smaData = [
                            'site_fkey' => $site_pkey,
                            'site_transactions_fkey' => $st_fkey,
                            'created_by' => $user,
                            'remarks' => 'Approved by Admin',
                            'created_date' => $current_time,
                            'modified_by' => NULL,
                            'modified_date' => NULL,
                            'status' => 'approved'
                        ];

                        $insertSma = $this->SiteMasterApproval->save($smaData);
                        $site_master_approval_pkey = $this->SiteMasterApproval->getLastInsertID();



                        $insertSmd = $this->SiteMasterApproval->query("INSERT INTO site_master_approval_details (site_master_approval_fkey, fieldname, old_value, new_value, created_by, created_date)
                                                                            VALUES ($site_master_approval_pkey, `status`, 1, 0, '$user', '$current_time')");

                        $updateSiteHistory =  $this->SiteMasterApproval->query("UPDATE site_history SET site_master_approval_fkey = $site_master_approval_pkey
                                                                                    WHERE site_history_pkey = $history_pkey");
                    }
                } //Ended
            } else {
                $update_data = array();
                $update_data['site_transactions_pkey'] = $arr_data['site_transaction_fkey'][$i];
                $user = $this->Session->read('login_user_id');
                $update_data['day_time_seq_fkey'] = $arr_data['TdDayTime'][$i];
                $update_data['designation_id'] = $arr_data['TdDesg'][$i];
                $update_data['status'] = $arr_data['status'][$i];
                $update_data['emp_count'] = $arr_data['TdCount'][$i];
                $update_data['srate'] = $arr_data['TdSRate'][$i];
                $update_data['eratess'] = $arr_data['Rate'][$i];
                $update_data['start_date_effective'] = date("Y-m-d", strtotime($arr_data['StDE'][$i]));
                $update_data['end_date_effective'] = $arr_data['EtDE'][$i];
                $update_data['modified_by'] = "$user";
                $update_data['action'] = "u";

                //Edited by Akshay on 9-8-2023
                if ($update_data['site_transactions_pkey']) {
                    $existingTransactionsData = $this->SiteTransactions->findBySiteTransactionsPkey($update_data['site_transactions_pkey']);
                }


                if ($existingTransactionsData) {

                    $changes = array();
                    $existing = array();
                    $siteMasterApprovalDetailsData = array();

                    foreach ($update_data as $key => $value) {
                        if (isset($existingTransactionsData['SiteTransactions'][$key]) && $existingTransactionsData['SiteTransactions'][$key] != $value && $key != 'created_by') {
                            // Add the field and its new value to the $changes array
                            $changes[$key] = $value;
                            $existing[$key] = $existingTransactionsData['SiteTransactions'][$key];

                            // Save the changes in the site_master_approval_details table
                            $siteMasterApprovalDetailsData[] = [
                                // 'site_master_approval_fkey' => $existingTransactionsData['SiteTransactions'][],
                                'site_transactions_fkey' => $update_data['site_transactions_pkey'],
                                'fieldname' => $key,
                                'old_value' => $existing[$key],
                                'new_value' => $changes[$key],
                                'created_by' => $user, // You need to set the correct user ID or name here
                                'created_date' => $current_time, // Current timestamp
                                'type' => 'shift'
                            ];
                        }
                    }
                }

                $user = $this->Session->read('login_user_id');
                $approvalData1 = [
                    'site_fkey' => $pkey,
                    'created_by' => $user,
                    'created_date' => $current_time,
                    'remarks' => 'Approved by Admin',
                    'status' => 'approved'
                ];
                $this->SiteMasterApproval->save($approvalData1);
                $lastInsertedId = $this->SiteMasterApproval->getInsertID();

                foreach ($siteMasterApprovalDetailsData as $smaData) {

                    $stfkey =  isset($smaData['site_transactions_fkey']) ? $smaData['site_transactions_fkey'] : '';
                    $stfield =  isset($smaData['fieldname']) ? $smaData['fieldname'] : '';
                    $stold =  isset($smaData['old_value']) ? $smaData['old_value'] : '';
                    $stnew = isset($smaData['new_value']) ? $smaData['new_value'] : '';
                    $stcrtby = isset($smaData['created_by']) ? $smaData['created_by'] : '';
                    $stcrtdt =  date('Y-m-d H:i:s', strtotime($smaData['created_date']));

                    $sttype = isset($smaData['type']) ? $smaData['type'] : 'shift';

                    $sql = "INSERT INTO site_master_approval_details (site_master_approval_fkey, site_transactions_fkey, fieldname, old_value, new_value, created_by, created_date, type, status)
                            VALUES ($lastInsertedId,$stfkey, '$stfield', '$stold', '$stnew', '$stcrtby', '$stcrtdt', '$sttype', 'approved')";
                    $this->SiteMasterApprovalDetails->query($sql);
                } //Ended

                $this->SiteTransactions->saveAll($update_data);
                $this->SiteHistory->saveAll($update_data);
            }
        }
    }

    public function admin($user_pkey = 0, $DD = '')
    {
        $this->autoRender = false;

        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        $adminid = '0';

        $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

        $pkey = isset($fetchUser['0']['Useraccess']['user_access_pkey']) ? $fetchUser['0']['Useraccess']['user_access_pkey'] : '';
        if ($pkey != '') {
            $arr_useraccess_data['user_access_pkey'] = $pkey;
        }
        $arr_useraccess_data['organization_id'] = '1';
        $arr_useraccess_data['user_fkey'] = $user_pkey;
        $arr_useraccess_data['menu_id'] = $adminid;
        if ($DD == "ADMINS") {
            $arr_useraccess_data['active'] = 'Y';
        } else {
            $arr_useraccess_data['active'] = 'N';
        }
        $arr_useraccess_data['status'] = '1';


        $this->Useraccess->save($arr_useraccess_data);
    }

    public function get()
    {
        $this->autoRender = FALSE;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        $empids = $_POST['emp_id'];
        $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");

        $adminid = $arr_useraccess['0']['empmenu']['menu_id'];
        $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$empids' and menu_id = '$adminid' and active = 'Y' ");

        $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
        if ($access == 'Y') {
            $active = 'Y';
        } else {
            $active = 'N';
        }

        echo json_encode($active);
    }

    public function deleteuser($user_access_pkey = 0)
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        if ($user_access_pkey != 0) {
            $this->Useraccess->updateAll(array('status' => 0), array('user_access_pkey' => $user_access_pkey));
            echo json_encode(array('msg' => 'User deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'User deletion failed!'));
        }
    }

    public function saveuseraccess()
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $menu_id = isset($arr_data['menu_id']) ? $arr_data['menu_id'] : '';
        $user_pkey = isset($arr_data['user_pkey']) ? $arr_data['user_pkey'] : '';
        $active = isset($arr_data['active']) ? $arr_data['active'] : '';

        $arr_useraccess = $this->Useraccess->find(
            'all',
            array(
                'fields' => 'user_access_pkey',
                'conditions' => array(
                    'menu_id' => $menu_id,
                    'user_fkey' => $user_pkey
                )
            )
        );

        if (isset($arr_useraccess[0]['Useraccess']['user_access_pkey'])) {
            //Update
            $user_access_pkey = $arr_useraccess[0]['Useraccess']['user_access_pkey'];
            $this->Useraccess->updateAll(array('active' => "'$active'"), array('user_access_pkey' => $user_access_pkey));
            echo json_encode(array('msg' => 'User access updated successfully!'));
        } else {
            //Add
            $arr_useraccess_data = array();
            $arr_useraccess_data['organization_id'] = 1;
            $arr_useraccess_data['user_fkey'] = $user_pkey;
            $arr_useraccess_data['menu_id'] = $menu_id;
            $arr_useraccess_data['active'] = $active;
            $this->Useraccess->save($arr_useraccess_data);

            echo json_encode(array('msg' => 'User access saved successfully!'));
        }
    }

    public function get_incompleteDate($sitefkey = 0, $shift = 0)
    {
        //date_format(current_date,'%Y-%m-%d') and 
        // and  dt<=  (select distinct max(date_format(end_date_effective,'%Y-%m-%d')) from site_transactions  where site_fkey='$sitefkey' and site_transactions.status = '1' )"

        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $site_data = $this->Useraccess->query("select min(dt) start,max(dt) end from calendar_table where dt >= greatest(date_format(DATE_SUB(NOW(),
            INTERVAL (select min_days_before from site where site_pkey='$sitefkey') DAY),'%Y-%m-%d'), (select distinct min(date_format(start_date_effective,'%Y-%m-%d'))  
            from site_transactions where site_fkey='$sitefkey' and site_transactions.status = '1')) 
            and  dt<=  (select distinct max(date_format(end_date_effective,'%Y-%m-%d')) from site_transactions where site_fkey='$sitefkey' 
            and site_transactions.status = '1') and dt not in (select att_date from site_shift_close where site_fkey='$sitefkey' and
            day_time_seq_fkey ='$shift' and shift_closed_status='Y' and rec_status=1) order by '1' ;");

        $first = $site_data['0']['0']['start'];
        $last = $site_data['0']['0']['end'];
        echo json_encode(array('success' => 1, 'first' => $first, 'last' => $last));
    }

    public function pnch()
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            //            $branch_condition = " and user_pkey=" . $cur_emp_key . " and site_pkey in (select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and status=1)";
            $temp_site_keys1 = $this->Useraccess->query("select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1");
            $temp_site_keys2 = $this->Useraccess->query("select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and site_fkey not in (select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1) and status=1");
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
            $branch_condition = "";
        }
        //employee branch wise sorting ends here        
        $site_data = $this->Useraccess->query("SELECT site_pkey,site_id,site_name FROM site WHERE status = '1' " . $branch_condition);

        $this->set('site_data', $site_data);
    }

    public function get_shift($month = '', $fkey = '')
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $site_fkey = $fkey;
        $date_att = $month;

        $site_data = $this->Useraccess->query("select  day_time_seq_fkey,day_time_desc,on_dutty1,off_dutty1,working_time1,site_transactions.start_date_effective,
                            site_transactions.end_date_effective  from site_transactions 
                            join working_day_time_procedures
                            on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) 
                            where site_transactions.start_date_effective <= '$date_att' 
                            and IFNULL(site_transactions.end_date_effective, '3000-01-01') >= '$date_att' and 
                            site_transactions.site_fkey ='$site_fkey' and site_transactions.status = '1' group by day_time_seq_fkey");

        $array = array();
        $branch = array();

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
            $site[] = array(
                'id' => $value['site_transactions']['day_time_seq_fkey'],
                'text' => $value['working_day_time_procedures']['day_time_desc'] . $shift
            );
        }

        if (!empty($site)) {
            $array['items'] = $site;
        }

        echo json_encode($array);
    }

    public function load_sites($site_fkey = 0, $shift_fkey = 0, $date = "")
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $shift_fkey = $shift_fkey;
        $site_fkey = $site_fkey;
        $att_date = $date;
        $site_data = $this->Useraccess->query("select distinct day_time_seq_fkey,day_time_desc,on_dutty1,off_dutty1,working_time1,designation_id,emp_count,id,desig_name "
            . "from site_transactions "
            . "join working_day_time_procedures on "
            . "(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) "
            . "join designation on (site_transactions.designation_id = designation.id) "
            . "where site_transactions.day_time_seq_fkey ='$shift_fkey' and site_transactions.site_fkey ='$site_fkey' "
            . "and site_transactions.start_date_effective <='$att_date' and site_transactions.end_date_effective >='$att_date'"
            . "and site_transactions.status = 1 and site_transactions.day_time_seq_fkey not in
             (select day_time_seq_fkey from site_attendance where att_date='$att_date' and site_fkey='$site_fkey' and status=3 and active = 1)");
        $count_close = $this->Useraccess->query("select count(*)"
            . "from site_attendance "
            . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$att_date' and status = '3' and active = 1");
        if ($count_close['0']['0']['count(*)'] > 0) {
            $site_data = "";
        }
        $this->set('site_data', $site_data);
    }

    public function add_site($site_fkey = 0, $shift_fkey = 0, $date = '')
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $shift_fkey = $shift_fkey;
        $site_fkey = $site_fkey;
        $user_id = 'DEMO12611';
        $date = $date; //'2019-01-01';
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $branch_condition = " where emp_pkey='" . $cur_emp_key . "'";
        } else {
            $branch_condition = "";
        }
        $site_data = $this->Useraccess->query("select distinct `ed`.`emp_pkey` AS `emp_pkey`,concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),' - ',`ep`.`emp_company_id`, ' - ',designation.desig_code) 
        AS `EmpName`,`ep`.`emp_company_id` AS `employee_id`,`br`.`branch_name` AS `branch`,`designation`.`desig_name` AS `designation`,`department`.`dept_name` AS `department`,
        `ep`.`joining_date` AS `joining_date` from ((((`emp_proff` `ep` join `emp_details` `ed` on((`ed`.`emp_pkey` = `ep`.`emp_fkey`)))
        left join `branches` `br` on((`ed`.`branch_code` = `br`.`branch_code`))) 
        left join `designation` on((`ep`.`designation` = `designation`.`desig_code`)))
        left join `department` on((`ep`.`emp_dept` = `department`.`dept_code`))) 
        where ed.status =1 and ed.branch_code in (select branch_code from emp_details " . $branch_condition . ") and ep.joining_date <='$date'
        and ed.emp_pkey not in ( select distinct emp_fkey from site_attendance 
        where att_date ='$date' and in_time is not null and active=1 and site_fkey = '$site_fkey' and day_time_seq_fkey='$shift_fkey') ");

        $shift_data = $this->Useraccess->query("select * from working_day_time_procedures where day_time_seq = '$shift_fkey' ");
        $this->set('att_data', $date);
        $this->set('site_data', $site_data);
        $this->set('shift_data', $shift_data);
    }

    //Shift close Button start
    public function shift_closure()
    {
        $this->autoRender = false;
        $this->SiteAttendance->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        $datess = $arr_form_data['att_date']; //'2018-01-17';
        $site_fkey = $arr_form_data['site_fkey']; //'1';
        $shift_fkey = $arr_form_data['day_time_seq_fkey']; //'1';
        $user_id = 'Admin';
        $creation_date = date("Y-m-d H:i:s");
        $success = 0;
        $messagess = '';
        $count1 = $this->SiteAttendance->query("select count(*) "
            . "from site_attendance "
            . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$datess' and status = '1' ");
        $count2 = $this->SiteAttendance->query("select count(*) "
            . "from site_attendance "
            . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$datess' and status = '2' ");

        if ($count1['0']['0']['count(*)'] <= 0 && $count2['0']['0']['count(*)'] > 0) {

            $sql_update = $this->SiteAttendance->updateAll(
                array(
                    'status' => '3',
                    'modified_by' => '"' . $user_id . '"',
                    'modified_date' => '"' . $creation_date . '"'
                ),
                array(
                    'site_fkey' => $site_fkey,
                    'att_date' => $datess,
                    'day_time_seq_fkey' => $shift_fkey,
                    'active' => 1
                )
            );

            $success = 1;
            $messagess = "Shift closed successfully";
        } else {
            $success = 0;
            $messagess = "Can't close shift. Please check all punchings.";
        }
        $arrResponse = array(
            'success' => $success,
            'message' => $messagess,
            'data' => array()
        );

        return json_encode($arrResponse);
    }
    //Shift open function
    public function shift_open()
    {
        $this->autoRender = false;
        $this->SiteAttendance->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        $datess = $arr_form_data['att_date']; //'2018-01-17';
        $site_fkey = $arr_form_data['site_fkey']; //'1';
        $shift_fkey = isset($arr_form_data['day_time_seq_fkey']) ? $arr_form_data['day_time_seq_fkey'] : ''; //'1';
        $user_id = 'Admin';
        $creation_date = date("Y-m-d H:i:s");
        $success = 0;
        $messagess = '';


        $sql_update = $this->SiteAttendance->updateAll(
            array(
                'status' => '4',
                'modified_by' => '"' . $user_id . '"',
                'modified_date' => '"' . $creation_date . '"'
            ),
            array(
                'site_fkey' => $site_fkey,
                'att_date' => $datess,
                'day_time_seq_fkey' => $shift_fkey,
                'active' => 1
            )
        );

        $result = $this->SiteAttendance->deleteAll(array('status' => '4', 'active' => '1', 'site_fkey' => $site_fkey, 'att_date' => $datess, 'day_time_seq_fkey' => $shift_fkey));
        $success = 1;
        $messagess = "Shift opened successfully.";
        $arrResponse = array(
            'success' => $success,
            'message' => $messagess,
            'data' => array()
        );

        return json_encode($arrResponse);
    }

    public function close_shift($site_fkey = 0, $shift_fkey = 0, $date = "")
    {

        $this->SiteAttendance->useDbConfig = $this->Session->read('ds');

        $shift_fkey = $shift_fkey;
        $site_fkey = $site_fkey;
        $att_date = $date;

        $siteatt_data = array();
        $count = $this->SiteAttendance->query("select count(*) "
            . "from site_attendance "
            . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$att_date' and status = '1' and active= '1' ");
        if ($count > 0) {
            $siteatt_data = $this->SiteAttendance->query("select * "
                . " from site_attendance "
                . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$att_date' and active= '1' and status = '2' ");
        }

        $count1 = $this->SiteAttendance->query("select count(*) "
            . "from site_attendance "
            . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$att_date' and (status = '1' or status='2') and active = 1");

        if ($count1['0']['0']['count(*)'] == '0') {
            $count_close = $this->SiteAttendance->query("select count(*)"
                . "from site_attendance "
                . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$att_date' and status = '3' and active= '1' ");

            if ($count_close['0']['0']['count(*)'] > 0) {
                $cnt = 0;
            } else {
                $cnt = 1;
            }
        } else {
            $cnt = 1;
        }

        $this->set('shift_open', $cnt);
        $this->set('siteatt_data', $siteatt_data);
    }

    //Shift close Button end
    public function data_site()
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $date_passed = $arr_form_data['att_date']; //'2018-01-17';
        $site_fkey = $arr_form_data['site']; //'1';
        $shift_id = $arr_form_data['day_time_seq_fkey']; //'1';
        $designation_id = $arr_form_data['desig']; //'20';
        $arr_result = array();
        $sql_desig = "SELECT * FROM `designation` where desig_code = '$designation_id' ";
        $row_designation_details = $this->Useraccess->query($sql_desig);

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " AND ed.branch_code='" . $cur_emp_branch . "'";
        } else {
            $branch_condition = "";
        }

        $sql = "select distinct '$date_passed' att_date,sa.emp_fkey,
            concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
            AS `EmpName`,on_dutty1,off_dutty1,isnextday,'d' recordtype,sa.active 
            from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
            where ep.emp_fkey=ed.emp_pkey
            and att_date in (select max(att_date) from site_attendance where 
            site_fkey = $site_fkey
            and day_time_seq_fkey = $shift_id
            and designation_id = '$designation_id'
            and att_date <'$date_passed' )
            and  site_fkey = $site_fkey
            and day_time_seq_fkey= $shift_id
            and designation_id = '$designation_id'
            and ed.emp_pkey=sa.emp_fkey
            and designation.id = sa.designation_id 
            and wd.day_time_seq=day_time_seq_fkey
	    and sa.emp_fkey not in
	    (select sa.emp_fkey
	    from site_attendance sa ,emp_details ed ,designation ,working_day_time_procedures wd
	    where att_date ='$date_passed' 
	    and ed.emp_pkey=sa.emp_fkey
	    and designation.desig_code = sa.designation_id 
	    and wd.day_time_seq=day_time_seq_fkey) " . $branch_condition;




        $row_emp_details = $this->Useraccess->query($sql);


        $sql2 = "select distinct att_date ,site_attendance_pkey , sa.emp_fkey,
            concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
            AS `EmpName`,on_dutty1,off_dutty1,working_time1,isnextday,in_time,out_time,'O' recordtype,sa.active 
            from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
            where att_date ='$date_passed'
            and ep.emp_fkey=ed.emp_pkey    
            and site_fkey =$site_fkey  
            and day_time_seq_fkey= $shift_id
            and designation_id = '$designation_id'
            and ed.emp_pkey=sa.emp_fkey
            and designation.id = sa.designation_id 
            and wd.day_time_seq=day_time_seq_fkey ";


        $row_emp_details2 = $this->Useraccess->query($sql2);

        $arr_emps = array();
        foreach ($row_emp_details as $key => $value) {
            $arr_emps[] = $value['sa']['emp_fkey'];
        }
        $unset_array = array();

        foreach ($row_emp_details2 as $key => $value) {
            $keyFound = array_search($value['sa']['emp_fkey'], $arr_emps);

            if (gettype($keyFound) == "integer") {
                $unset_array[] = $keyFound;
                unset($row_emp_details[$keyFound]);
            }
        }

        $unique_unset = array_unique($unset_array);

        if (!empty($unique_unset) && $unique_unset != FALSE) {
            foreach ($unique_unset as $val) {
                unset($row_emp_details[$val]);
            }
        }
        $row_emp_details = array_values($row_emp_details);


        if (!empty($row_emp_details) || !empty($row_emp_details2)) {

            $arrResponse = array(
                'success' => 1,
                'message' => 'Your attendance logs retrieved successfully',
                'data' => array(
                    "first_att" => $row_emp_details,
                    "second_att" => $row_emp_details2
                )
            );
        } else {
            $arrResponse = array(
                'success' => 0,
                'message' => 'No attendance logs found',
                'data' => array()
            );
        }


        $this->set('arrResponse', $arrResponse['data']);
    }

    //Activate / Deactivate button for Punch transactions start

    public function mark_activestatus($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $created_by = 'Admin';
        $creation_date = date("Y-m-d H:i:S");

        $site_attendance_pkey = $arr_form_data['site_attendance_pkey'];
        $arr_resp = array();
        if ($site_attendance_pkey != null) {
            $success = 1;
            $messagess = "Punching disabled successfully.";
            $save_sql = "update site_attendance set active = '0',modified_by = '$created_by',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
            $res_save_sql = $this->Useraccess->query($save_sql);
        } else {
            $success = 0;
            $messagess = "Failure";
        }
        $arrResponse = array(
            'success' => $success,
            'message' => $messagess,
            'data' => array()
        );

        return json_encode($arrResponse);
    }

    public function mark_deactivestatus($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $created_by = 'Admin';
        $creation_date = date("Y-m-d H:i:S");
        $site_attendance_pkey = $arr_form_data['site_attendance_pkey'];
        $arr_resp = array();
        if ($site_attendance_pkey != null) {
            $success = 1;
            $messagess = "Punching enabled successfully.";
            $save_sql = "update site_attendance set active = '1',modified_by = '$created_by',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
            $res_save_sql = $this->Useraccess->query($save_sql);
        } else {
            $success = 0;
            $messagess = "Failure";
        }
        $arrResponse = array(
            'success' => $success,
            'message' => $messagess,
            'data' => array()
        );

        return json_encode($arrResponse);
    }

    //Activate / Deactivate button for Punch transactions end

    public function mark_attendance($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $login_user_id = $this->Session->read('login_user_id');

        $arr_form_data = $this->request->data;
        $site_attendance_pkey = $arr_form_data['site_attendance_pkey'];

        $site_fkey = $arr_form_data['site_fkey'];
        $user_id = $arr_form_data['day_time_seq_fkey'];
        $day_time_seq_fkey = $arr_form_data['day_time_seq_fkey'];
        $designation_id = $arr_form_data['designation_id'];
        $out_times = date("H:i:s", strtotime($arr_form_data['out_time']));

        $in_time = date("H:i:s", strtotime($arr_form_data['out_time']));

        $att_date = $arr_form_data['att_date'];
        $checkout_time = isset($arr_form_data['checkout_time']) ? date("H:i:s", strtotime($arr_form_data['checkout_time'])) : '';
        $checkin_time = isset($arr_form_data['checkin_time']) ? date("H:i:s", strtotime($arr_form_data['checkin_time'])) : '';
        $created_by = 'Admin';
        $creation_date = date("Y-m-d H:i:S");
        $mobile_pkey = 0;
        $arr_response = array();
        $arr_resp = array();
        $success = '';
        if ($site_attendance_pkey != null) {

            if ('00:00:00' <= $out_times) {
                if ($out_times <= $checkout_time) {

                    $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
                } else {
                    $out_date = $att_date;
                }
            } else {
                $out_date = $att_date;
            }
            $out_time = $out_date . ' ' . $out_times;
            // end out time next day 2
            $insert_cjeck_sql1 = "SELECT `mark_site_attendance_out_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', null , '$out_time', '$att_date')  as resps ";

            $row_emp_details = $this->Useraccess->query($insert_cjeck_sql1);


            $resps = $row_emp_details[0][0]['resps'];

            $success = 1;
            $messagess = "Punch out successfully completed";
            if ($resps == 'update') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');

                if ($user_group == 2) {

                    $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$login_user_id',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
                } else {
                    $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$created_by',modified_date = '$creation_date' where site_attendance_pkey = '$site_attendance_pkey' ";
                }
                $res_save_sql = $this->Useraccess->query($save_sql);
            } else {
                $success = 0;
                $messagess = $resps;
            }
            $arr_resp['uploaded_time'] = $creation_date;
            $arr_resp['site_attendance_pkey'] = $site_attendance_pkey;
            $arr_resp['mob_pkey'] = $mobile_pkey;
        } else {


            if ($checkin_time > $in_time) {
                $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
            } else {
                $out_date = $att_date;
            }
            $in_time = $out_date . ' ' . $in_time;
            $insert_cjeck_sql1 = "SELECT `mark_site_attendance_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', '$in_time', null, '$att_date')  as resps ";

            $res_insert_cjeck_sql1_sql1 = $this->Useraccess->query($insert_cjeck_sql1);
            $resps = $res_insert_cjeck_sql1_sql1[0][0]['resps'];
            if ($resps == 'insert') {

                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');

                if ($user_group == 2) {

                    $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_fkey','$att_date','$in_time','$login_user_id')";
                } else {
                    $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_fkey','$att_date','$in_time','$created_by')";
                }


                $res_sql1 = $this->Useraccess->query($save_sql1);

                $success = 1;
                $messagess = "Punch in successfully completed";
                $new_login_auditor_pkey = $this->Useraccess->getLastInsertID();
            } else {
                $success = 0;
                $messagess = $resps;
            }

            $arr_resp['uploaded_time'] = $creation_date;

            $arr_resp['mob_pkey'] = $mobile_pkey;
        }
        if (!empty($arr_resp)) {

            $arrResponse = array(
                'success' => $success,
                'message' => $messagess,
                'data' => $arr_resp
            );
        } else {
            $arrResponse = array(
                'success' => $success,
                'message' => $messagess,
                'data' => array()
            );
        }
        return json_encode($arrResponse);
    }


    // This is to update site attendance
    public function update_attendance($emp_fkey = 0)
    {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $login_user_id = $this->Session->read('login_user_id');

        $arr_form_data = $this->request->data;
        $site_attendance_pkey = $arr_form_data['site_attendance_pkey'];

        $site_fkey = $arr_form_data['site_fkey'];
        $user_id = $arr_form_data['day_time_seq_fkey'];
        $day_time_seq_fkey = $arr_form_data['day_time_seq_fkey'];
        $designation_id = $arr_form_data['designation_id'];
        $out_times = date("H:i:s", strtotime($arr_form_data['out_time']));

        $in_time = date("H:i:s", strtotime($arr_form_data['in_time']));
        $att_date = $arr_form_data['att_date'];
        $checkout_time = isset($arr_form_data['checkout_time']) ? date("H:i:s", strtotime($arr_form_data['checkout_time'])) : '';
        $checkin_time = isset($arr_form_data['checkin_time']) ? date("H:i:s", strtotime($arr_form_data['checkin_time'])) : '';
        $created_by = 'Admin';
        $creation_date = date("Y-m-d H:i:S");
        $mobile_pkey = 0;
        $new_site_attendance_pkey = '';
        $arr_response = array();
        $arr_resp = array();
        $success = '';
        if ($site_attendance_pkey != null) {

            // This is to deactivate existing attendance
            $this->mark_activestatus();

            // In time calculation and update

            if ($checkin_time > $in_time) {
                $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
            } else {
                $out_date = $att_date;
            }
            $in_time = $out_date . ' ' . $in_time;

            // Calling mark_site_attendance_fn
            $insert_cjeck_sql1 = "SELECT `mark_site_attendance_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', '$in_time', null , '$att_date')  as resps ";

            $res_insert_cjeck_sql1_sql1 = $this->Useraccess->query($insert_cjeck_sql1);
            $resps = $res_insert_cjeck_sql1_sql1[0][0]['resps'];
            if ($resps == 'insert') {

                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
                if ($user_group == 2) {
                    $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_fkey','$att_date','$in_time','$login_user_id')";
                } else {
                    $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_fkey','$att_date','$in_time','$created_by')";
                }

                $res_sql1 = $this->Useraccess->query($save_sql1);
                // $new_site_attendance_pkey = $this->Useraccess->getLastInsertID(); // This is not working
                $last_inserted_data = $this->Useraccess->query("SELECT MAX(site_attendance_pkey) as last_id FROM site_attendance WHERE active=1 AND emp_fkey = " . $emp_fkey . " AND site_fkey = " . $site_fkey . " AND day_time_seq_fkey = " . $day_time_seq_fkey . "");
                $new_site_attendance_pkey = ($last_inserted_data && isset($last_inserted_data[0][0]['last_id'])) ? $last_inserted_data[0][0]['last_id'] : '';
                $success = 1;
                $messagess = "Punch in successfully completed";
            } else {
                $success = 0;
                $messagess = $resps;
                $arrResponse = array(
                    'success' => $success,
                    'message' => $messagess,
                    'data' => $arr_resp
                );
            }

            // in time updated, update out time
            if ($success && $new_site_attendance_pkey != '') {
                // out time calculation and update

                if ('00:00:00' <= $out_times) {
                    if ($out_times <= $checkout_time) {
                        $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
                    } else {
                        $out_date = $att_date;
                    }
                } else {
                    $out_date = $att_date;
                }
                $out_time = $out_date . ' ' . $out_times;

                $insert_cjeck_sql1 = "SELECT `mark_site_attendance_out_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', null , '$out_time', '$att_date')  as resps ";
                $row_emp_details = $this->Useraccess->query($insert_cjeck_sql1);

                $resps = $row_emp_details[0][0]['resps'];
                $success = 1;
                $messagess = "Punch update successfully completed";

                if ($resps == 'update') {
                    if ($new_site_attendance_pkey) {

                        $user_group = $this->Session->read('user_group');
                        $user = $this->Session->read('company_code');
                        if ($user_group == 2) {
                            $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$login_user_id',modified_date = '$creation_date' where site_attendance_pkey = '$new_site_attendance_pkey' ";
                        } else {
                            $save_sql = "update site_attendance set out_time = '$out_time',status= '2',modified_by = '$created_by',modified_date = '$creation_date' where site_attendance_pkey = '$new_site_attendance_pkey' ";
                        }
                        $res_save_sql = $this->Useraccess->query($save_sql);
                    } else {
                        $success = 0;
                        $messagess = "Failed to Update Attendance";
                    }
                } else {
                    $success = 0;
                    $messagess = $resps;
                }

                if (!$success) {
                    // If not insert new attendance, reactivate existing attendance
                    $this->mark_deactivestatus();
                    // This is to delete inserted new attendance
                    $save_sql = "DELETE FROM site_attendance where site_attendance_pkey = '$new_site_attendance_pkey' ";
                    $res_save_sql = $this->Useraccess->query($save_sql);
                }

                $arr_resp['uploaded_time'] = $creation_date;
                $arr_resp['site_attendance_pkey'] = $site_attendance_pkey;
                $arr_resp['mob_pkey'] = $mobile_pkey;

                $arrResponse = array(
                    'success' => $success,
                    'message' => $messagess,
                    'data' => $arr_resp
                );
            } else {
                // If not insert new attendance, reactivate existing attendance
                $this->mark_deactivestatus();
            }
        } else {
            $success = 0;
            $messagess = '';
            $arrResponse = array(
                'success' => $success,
                'message' => $messagess,
                'data' => array()
            );
        }
        return json_encode($arrResponse);
    }

    public function site_allocate($site_pkey = 0)
    {

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $condition = array("emp_pkey not in (select emp_fkey from access_site where site_fkey=$site_pkey and status=1)");
        $this->set("site_pkey", $site_pkey);
        //The below code is to display only unallocated employees to the specified store in employee list. By ***ARUL P DAS on 17/1/2020
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1, $condition)));
        $this->set("arr_employees", $arr_employees);
        //query edited by ***ARUL P DAS on 17/1/2020
        $arr_employees_allocates = $this->EmployeeDetails->query("select distinct emp_fkey,emp_details.first_name,last_name from access_site join emp_details on (emp_details.emp_pkey = access_site.emp_fkey) where site_fkey = '$site_pkey' and access_site.status = '1'");

        $this->set("arr_employees_allocates", $arr_employees_allocates);
    }

    public function save_allocate()
    {
        $this->autoRender = FALSE;

        $this->Access_site->useDbConfig = $this->Session->read('ds');

        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;

        $emps = $arr_form_data['emps'];
        $site_code = $arr_form_data['site_code'];
        $resp_att = array();
        $cnt = $this->Access_site->query("select count(*) as count from access_site where site_fkey='$site_code' and emp_fkey='$emps' and status=1 ");
        $count = $cnt['0']['0']['count'];

        if ($count == 0) {
            try {
                $this->Access_site->query(" insert into access_site(site_fkey,emp_fkey) values('$site_code','$emps') ");
                $resp_att['success'] = 1;
                $resp_att['msg'] = "Employee site allocation successful.";
                echo json_encode($resp_att);
            } catch (Exception $e) {
                $resp_att['success'] = 0;
                $resp_att['msg'] = "Saving failed.";
                echo json_encode($resp_att);
            }
        } else {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Employee already exists.";
            echo json_encode($resp_att);
        }
    }

    public function remove_allocate()
    {
        $this->autoRender = FALSE;

        $this->Access_site->useDbConfig = $this->Session->read('ds');
        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;

        $emps = $arr_form_data['emps'];
        $site_code = $arr_form_data['site_code'];

        $resp_att = array();
        try {


            $this->Access_site->query("update access_site set status = '0' where site_fkey = '$site_code' and emp_fkey = '$emps' ");
            $resp_att['success'] = 1;
            $resp_att['msg'] = "Removed Successfully ";
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }





    // This is to edit closed shift by arul on 13-3-23

    public function load_closed_sites($site_fkey = 0, $shift_fkey = 0, $date = "")
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->SiteAttendance->useDbConfig = $this->Session->read('ds');
        $shift_fkey = $shift_fkey;
        $site_fkey = $site_fkey;
        $att_date = $date;
        $user_id = 'Admin';
        $creation_date = date("Y-m-d H:i:s");

        /// This is to update all records into status 4 and then update to 2. by arul.
        $sql_update = $this->SiteAttendance->updateAll(
            array(
                'status' => '4',
                'modified_by' => '"' . $user_id . '"',
                'modified_date' => '"' . $creation_date . '"'
            ),
            array(
                'site_fkey' => $site_fkey,
                'att_date' => $att_date,
                'day_time_seq_fkey' => $shift_fkey,
                'active' => 1
            )
        );

        $sql_update = $this->SiteAttendance->updateAll(
            array(
                'status' => '2',
                'modified_by' => '"' . $user_id . '"',
                'modified_date' => '"' . $creation_date . '"'
            ),
            array(
                'site_fkey' => $site_fkey,
                'att_date' => $att_date,
                'day_time_seq_fkey' => $shift_fkey,
                'active' => 1
            )
        );



        $site_data = $this->Useraccess->query("select distinct day_time_seq_fkey,day_time_desc,on_dutty1,off_dutty1,working_time1,designation_id,emp_count,id,desig_name "
            . "from site_transactions "
            . "join working_day_time_procedures on "
            . "(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) "
            . "join designation on (site_transactions.designation_id = designation.id) "
            . "where site_transactions.day_time_seq_fkey ='$shift_fkey' and site_transactions.site_fkey ='$site_fkey' "
            . "and site_transactions.start_date_effective <='$att_date' and site_transactions.end_date_effective >='$att_date'"
            . "and site_transactions.status = 1");

        /*
        and site_transactions.day_time_seq_fkey not in
             (select day_time_seq_fkey from site_attendance where att_date='$att_date' and site_fkey='$site_fkey' and status=3 and active = 1)
        */
        $this->set('site_data', $site_data);
    }

    public function data_edit_site()
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;

        $date_passed = $arr_form_data['att_date']; //'2018-01-17';
        $site_fkey = $arr_form_data['site']; //'1';
        $shift_id = $arr_form_data['day_time_seq_fkey']; //'1';
        $designation_id = $arr_form_data['desig']; //'20';
        $arr_result = array();
        $sql_desig = "SELECT * FROM `designation` where desig_code = '$designation_id' ";
        $row_designation_details = $this->Useraccess->query($sql_desig);

        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $branch_condition = " AND ed.branch_code='" . $cur_emp_branch . "'";
        } else {
            $branch_condition = "";
        }

        $sql = "select distinct '$date_passed' att_date,sa.emp_fkey,
            concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
            AS `EmpName`,on_dutty1,off_dutty1,isnextday,'d' recordtype,sa.active 
            from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
            where ep.emp_fkey=ed.emp_pkey
            and att_date in (select max(att_date) from site_attendance where 
            site_fkey = $site_fkey
            and day_time_seq_fkey = $shift_id
            and designation_id = '$designation_id'
            and att_date <'$date_passed' )
            and  site_fkey = $site_fkey
            and day_time_seq_fkey= $shift_id
            and designation_id = '$designation_id'
            and ed.emp_pkey=sa.emp_fkey
            and designation.id = sa.designation_id 
            and wd.day_time_seq=day_time_seq_fkey
	    and sa.emp_fkey not in
	    (select sa.emp_fkey
	    from site_attendance sa ,emp_details ed ,designation ,working_day_time_procedures wd
	    where att_date ='$date_passed' 
	    and ed.emp_pkey=sa.emp_fkey
	    and designation.desig_code = sa.designation_id 
	    and wd.day_time_seq=day_time_seq_fkey) " . $branch_condition;




        $row_emp_details = $this->Useraccess->query($sql);


        $sql2 = "select distinct att_date ,site_attendance_pkey , sa.emp_fkey,
            concat(`ed`.`first_name`,' ',ifnull(`ed`.`middile_name`,''),' ',ifnull(`ed`.`last_name`,''),'-',`ep`.`emp_company_id`,' (',designation.desig_code,')') 
            AS `EmpName`,on_dutty1,off_dutty1,working_time1,isnextday,in_time,out_time,'O' recordtype,sa.active 
            from site_attendance sa ,emp_details ed ,emp_proff ep,designation ,working_day_time_procedures wd
            where att_date ='$date_passed'
            and ep.emp_fkey=ed.emp_pkey    
            and site_fkey =$site_fkey  
            and day_time_seq_fkey= $shift_id
            and designation_id = '$designation_id'
            and ed.emp_pkey=sa.emp_fkey
            and designation.id = sa.designation_id 
            and wd.day_time_seq=day_time_seq_fkey ";


        $row_emp_details2 = $this->Useraccess->query($sql2);

        $arr_emps = array();
        foreach ($row_emp_details as $key => $value) {
            $arr_emps[] = $value['sa']['emp_fkey'];
        }
        $unset_array = array();

        foreach ($row_emp_details2 as $key => $value) {
            $keyFound = array_search($value['sa']['emp_fkey'], $arr_emps);

            if (gettype($keyFound) == "integer") {
                $unset_array[] = $keyFound;
                unset($row_emp_details[$keyFound]);
            }
        }

        $unique_unset = array_unique($unset_array);

        if (!empty($unique_unset) && $unique_unset != FALSE) {
            foreach ($unique_unset as $val) {
                unset($row_emp_details[$val]);
            }
        }
        $row_emp_details = array_values($row_emp_details);

        if (!empty($row_emp_details) || !empty($row_emp_details2)) {

            $arrResponse = array(
                'success' => 1,
                'message' => 'Your attendance logs retrieved successfully',
                'data' => array(
                    "first_att" => $row_emp_details,
                    "second_att" => $row_emp_details2
                )
            );
        } else {
            $arrResponse = array(
                'success' => 0,
                'message' => 'No attendance logs found',
                'data' => array()
            );
        }

        $this->set('arrResponse', $arrResponse['data']);
    }

    //Edited by Akshay on 28-7-2023
    public function saveSiteApply()
    {
        $this->autoRender = false;
        $arr_data = $this->request->data;
        $this->SiteHistory->useDbConfig = $this->Session->read('ds');
        $this->SiteMaster->useDbConfig = $this->Session->read('ds');
        $this->SiteTransactions->useDbConfig = $this->Session->read('ds');
        $this->SiteMasterApproval->useDbConfig = $this->Session->read('ds');
        $this->SiteMasterApprovalDetails->useDbConfig = $this->Session->read('ds');
        // Fetch existing data from the database based on the site_pkey
        $pkey = isset($arr_data['site_pkey']) ? $arr_data['site_pkey'] : '';
        if ($pkey != '') {
            $existingSiteData = $this->SiteMaster->findBySitePkey($pkey);
        }
        $user = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $current_time = date('Y-m-d H:i:s');

        $arr_form_data = array();
        $arr_data_count = count($arr_data['TdDayTime']);

        for ($i = 0; $i < $arr_data_count; $i++) {

            if ($arr_data['editkey'][$i] == 0 || $arr_data['editkey'][$i] == 1) {
                if ($arr_data['site_pkey']) {
                    $arr_form_data['site_pkey'] = isset($arr_data['site_pkey']) ? $arr_data['site_pkey'] : '';
                }

                $arr_form_data['site_id'] = $arr_data['site_id'];
                $arr_form_data['site_name'] = $arr_data['site_name'];
                $arr_form_data['branch_code'] = $arr_data['branch_code'];
                $arr_form_data['latitude'] = $arr_data['latitude'];
                $arr_form_data['longitude'] = $arr_data['longitude'];
                $arr_form_data['address'] = $arr_data['address'];
                $arr_form_data['customer_name'] = $arr_data['customer_name'];
                $arr_form_data['user_pkey'] = $arr_data['user_pkey'];
                $arr_form_data['contact_name'] = $arr_data['contact_name'];
                $arr_form_data['customer_contact'] = $arr_data['customer_contact'];
                $arr_form_data['special_remarks'] = $arr_data['special_remarks'];
                $arr_form_data['payment_mode'] = isset($arr_data['payment_mode']) ? $arr_data['payment_mode'] : '';

                // Minimum days for site punch attendance bulk update. by arul on 16-3-23
                $arr_form_data['min_days_before'] = isset($arr_data['min_days_before']) ? $arr_data['min_days_before'] : '';

                $arr_form_data['organization_id'] = isset($arr_data['organization_id']) ? 1 : 1;
            }
        }

        if (isset($existingSiteData)) {
            $changes = array();
            $existing = array();

            foreach ($arr_form_data as $key => $value) {
                if (isset($existingSiteData['SiteMaster'][$key]) && $existingSiteData['SiteMaster'][$key] != $value) {
                    // Add the field and its new value to the $changes array
                    $changes[$key] = $value;
                    $existing[$key] = $existingSiteData['SiteMaster'][$key];

                    // Save the changes in the site_master_approval_details table

                }
            }

            if (count($changes) > 0) {
                $approvalData = [
                    'site_fkey' => $pkey,
                    'created_by' => $user,
                    'created_date' => $current_time
                ];
                $this->SiteMasterApproval->save($approvalData);
                // Get the newly inserted record from the database

                $newlyInsertedRecord = $this->SiteMasterApproval->find('first', [
                    'conditions' => ['site_master_approval_pkey' => $this->SiteMasterApproval->id]
                ]);

                $siteMasterApprovalDetailsData = array();
                foreach ($changes as $key => $change) {
                    $siteMasterApprovalDetailsData[] = [
                        'site_master_approval_fkey' => $newlyInsertedRecord['SiteMasterApproval']['site_master_approval_pkey'], // Assuming you have the primary key of site_master_approval here
                        'fieldname' => $key,
                        'old_value' => $existing[$key],
                        'new_value' => $change,
                        'created_by' => $user, // You need to set the correct user ID or name here
                        'created_date' => date('Y-m-d H:i:s'), // Current timestamp
                        'type' => 'general'
                    ];
                }
            }
        } else { //Edited by Akshay on 10-8-2023
            $changes = array();
            $existing = array();

            $arr_form_data['status'] = 2;
            $this->SiteMaster->save($arr_form_data);

            $site_pkey = $this->SiteMaster->getLastInsertId();

            foreach ($arr_form_data as $key => $value) {
                // Add the field and its new value to the $changes array
                if (trim($key) != 'status') {
                    $changes[$key] = $value;
                    $existing[$key] = '';
                }

                // Save the changes in the site_master_approval_details table

            }

            if (count($changes) > 0) {
                $approvalData = [
                    'site_fkey' => $site_pkey,
                    'created_by' => $user,
                    'created_date' => $current_time,
                    'status' => 'pending'
                ];
                $this->SiteMasterApproval->save($approvalData);
                // Get the newly inserted record from the database

                $newlyInsertedRecord = $this->SiteMasterApproval->find('first', [
                    'conditions' => ['site_master_approval_pkey' => $this->SiteMasterApproval->id]
                ]);

                $siteMasterApprovalDetailsData = array();
                foreach ($changes as $key => $change) {
                    $siteMasterApprovalDetailsData[] = [
                        'site_master_approval_fkey' => $newlyInsertedRecord['SiteMasterApproval']['site_master_approval_pkey'], // Assuming you have the primary key of site_master_approval here
                        'fieldname' => $key,
                        'old_value' => $existing[$key],
                        'new_value' => $change,
                        'created_by' => $user, // You need to set the correct user ID or name here
                        'created_date' => date('Y-m-d H:i:s'), // Current timestamp
                        'type' => 'new_site',
                        'status' => 'pending'
                    ];
                }
            }
        }


        if (isset($siteMasterApprovalDetailsData)) {

            if (count($siteMasterApprovalDetailsData) > 0) {
                foreach ($siteMasterApprovalDetailsData as $smaData) {
                    // Save the record in the site_master_approval_details table
                    $this->SiteMasterApprovalDetails->create();
                    $this->SiteMasterApprovalDetails->save($smaData);
                }
            }
        }


        for ($i = 0; $i < $arr_data_count; $i++) {

            if ($arr_data['editkey'][$i] == 0) {
                $site_data = array();
                if ($arr_data['site_pkey']) {
                    $site_data['site_fkey'] = $arr_data['site_pkey'];
                } else {
                    $site_data['site_fkey'] = $site_pkey;
                }
                if (isset($arr_data['site_transaction_fkey'][$i])) {
                    $site_data['site_transactions_pkey'] = $arr_data['site_transaction_fkey'][$i];
                }

                $site_data['day_time_seq_fkey'] = $arr_data['TdDayTime'][$i];
                $site_data['designation_id'] = $arr_data['TdDesg'][$i];
                $site_data['status'] = $arr_data['status'][$i];
                $site_data['emp_count'] = $arr_data['TdCount'][$i];
                $site_data['srate'] = $arr_data['TdSRate'][$i];
                $site_data['eratess'] = $arr_data['Rate'][$i];
                $site_data['start_date_effective'] = $arr_data['StDE'][$i];
                $site_data['end_date_effective'] = $arr_data['EtDE'][$i];
                $site_data['created_by'] = $this->Session->read('login_user_id');
                if ($arr_data['site_pkey'] == '') {
                    $site_data['action'] = "i";
                } else {
                    $site_data['action'] = "u";
                }

                $this->SiteHistory->saveAll($site_data);
                $history_pkey = $this->SiteHistory->getInsertID();

                $arr_shift_data = $this->SiteHistory->query("SELECT site_transactions_pkey, site_fkey, status
                                                                FROM site_history 
                                                                WHERE site_history_pkey = $history_pkey ");
                foreach ($arr_shift_data as $data) { //For new whift
                    $site_pkey = $data['site_history']['site_fkey'];
                    $st_fkey = $data['site_history']['site_transactions_pkey'];

                    if ($data['site_history']['site_transactions_pkey'] == 0 && $data['site_history']['status'] == 1) {

                        $data1 = [
                            'site_fkey' => $site_pkey,
                            'site_transactions_fkey' => $st_fkey,
                            'created_by' => $user,
                            'created_date' => $current_time
                        ];

                        $insertSma = $this->SiteMasterApproval->save($data1);

                        $site_master_approval_pkey = $this->SiteMasterApproval->getLastInsertID();

                        $insertSmd = $this->SiteMasterApproval->query("INSERT INTO site_master_approval_details (site_master_approval_fkey, fieldname, old_value, new_value, created_by, created_date, type )
                        VALUES ( $site_master_approval_pkey,`status`, 0,1, '$user','$current_time', 'shift' )");

                        $updateSiteHistory =  $this->SiteMasterApproval->query("UPDATE site_history SET site_master_approval_fkey = $site_master_approval_pkey
                                                                                    WHERE site_history_pkey = $history_pkey");
                    } elseif ($data['site_history']['status'] == 0) { //Delete shift

                        $smaData = [
                            'site_fkey' => $site_pkey,
                            'site_transactions_fkey' => $st_fkey,
                            'created_by' => $user,
                            'status' => 'pending',
                            'created_date' => $current_time,
                            'modified_by' => NULL,
                            'modified_date' => NULL
                        ];

                        $insertSma = $this->SiteMasterApproval->save($smaData);
                        $site_master_approval_pkey = $this->SiteMasterApproval->getLastInsertID();



                        $insertSmd = $this->SiteMasterApproval->query("INSERT INTO site_master_approval_details (site_master_approval_fkey, fieldname, old_value, new_value, created_by, created_date,`type`)
                                                                            VALUES ($site_master_approval_pkey, `status`, 1, 0, '$user', '$current_time', 'shift')");

                        $updateSiteHistory =  $this->SiteMasterApproval->query("UPDATE site_history SET site_master_approval_fkey = $site_master_approval_pkey
                                                                                    WHERE site_history_pkey = $history_pkey");
                    }
                }
            } else {
                $update_data = array();
                $update_data['site_transactions_pkey'] = $arr_data['site_transaction_fkey'][$i];
                $user = $this->Session->read('login_user_id');
                $update_data['day_time_seq_fkey'] = $arr_data['TdDayTime'][$i];
                $update_data['designation_id'] = $arr_data['TdDesg'][$i];
                $update_data['status'] = $arr_data['status'][$i];
                $update_data['emp_count'] = $arr_data['TdCount'][$i];
                $update_data['srate'] = $arr_data['TdSRate'][$i];
                $update_data['eratess'] = $arr_data['Rate'][$i];
                $update_data['start_date_effective'] = date("Y-m-d", strtotime($arr_data['StDE'][$i]));
                $update_data['end_date_effective'] = $arr_data['EtDE'][$i];
                $update_data['modified_by'] = "$user";
                $update_data['action'] = "u";


                if ($update_data['site_transactions_pkey']) {
                    $existingTransactionsData = $this->SiteTransactions->findBySiteTransactionsPkey($update_data['site_transactions_pkey']);
                }


                if ($existingTransactionsData) {

                    $changes = array();
                    $existing = array();
                    $siteMasterApprovalDetailsData = array();

                    foreach ($update_data as $key => $value) {
                        if (isset($existingTransactionsData['SiteTransactions'][$key]) && $existingTransactionsData['SiteTransactions'][$key] != $value && $key != 'created_by') {
                            // Add the field and its new value to the $changes array
                            $changes[$key] = $value;
                            $existing[$key] = $existingTransactionsData['SiteTransactions'][$key];

                            // Save the changes in the site_master_approval_details table
                            $siteMasterApprovalDetailsData[] = [
                                // 'site_master_approval_fkey' => $existingTransactionsData['SiteTransactions'][],
                                'site_transactions_fkey' => $update_data['site_transactions_pkey'],
                                'fieldname' => $key,
                                'old_value' => $existing[$key],
                                'new_value' => $changes[$key],
                                'created_by' => $user, // You need to set the correct user ID or name here
                                'created_date' => $current_time, // Current timestamp
                                'type' => 'shift'
                            ];
                        }
                    }
                }

                $user = $this->Session->read('login_user_id');
                $approvalData1 = [
                    'site_fkey' => $pkey,
                    'created_by' => $user,
                    'created_date' => $current_time

                ];
                $this->SiteMasterApproval->save($approvalData1);
                $lastInsertedId = $this->SiteMasterApproval->getInsertID();

                foreach ($siteMasterApprovalDetailsData as $smaData) {

                    $stfkey =  isset($smaData['site_transactions_fkey']) ? $smaData['site_transactions_fkey'] : '';
                    $stfield =  isset($smaData['fieldname']) ? $smaData['fieldname'] : '';
                    $stold =  isset($smaData['old_value']) ? $smaData['old_value'] : '';
                    $stnew = isset($smaData['new_value']) ? $smaData['new_value'] : '';
                    $stcrtby = isset($smaData['created_by']) ? $smaData['created_by'] : '';
                    $stcrtdt =  date('Y-m-d H:i:s', strtotime($smaData['created_date']));

                    $sttype = isset($smaData['type']) ? $smaData['type'] : 'shift';

                    $sql = "INSERT INTO site_master_approval_details (site_master_approval_fkey, site_transactions_fkey, fieldname, old_value, new_value, created_by, created_date, type)
                            VALUES ($lastInsertedId,$stfkey, '$stfield', '$stold', '$stnew', '$stcrtby', '$stcrtdt', '$sttype')";
                    $this->SiteMasterApprovalDetails->query($sql);
                }
            }
        }
    }
    //Edited by Akshay on 31-7-2023
    public function approval()
    {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');

        $arr_employee = $this->EmployeeDetails->find('all', array('conditions' => array('status' => 1)));
        $this->set('arr_employee', $arr_employee);
        $arr_order = array('parent_id ASC');
        $arr_parent = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array('EmployeeMenu.parent_id = 0', 'EmployeeMenu.active' => 'Y')
        ));

        $arr_child = $this->EmployeeMenu->find("all", array(
            'fields' => 'EmployeeMenu.*,(SELECT menu_name FROM emp_menu AS m2 WHERE m2.menu_id = EmployeeMenu.parent_id) AS parent',
            'order' => $arr_order,
            'conditions' => array('EmployeeMenu.parent_id != 0', 'EmployeeMenu.active' => 'Y')
        ));
        $user_group = $this->Session->read('user_group');

        $this->set('user_group', $user_group);
        $this->set('arr_parent', $arr_parent);
        $this->set('arr_child', $arr_child);
    }

    public function approvallists($param = "")
    {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $arr_data = $this->request->data;

        $condition = "WHERE sma.status IS NOT NULL ";
        if (isset($arr_data['status'])) {
            if ($arr_data['status'] == 0) {
                $condition = "WHERE sma.status IS NOT NULL ";
            } elseif ($arr_data['status'] == 1) {
                $condition = "WHERE sma.status = 'pending' ";
            } elseif ($arr_data['status'] == 2) {
                $condition = "WHERE sma.status = 'approved' ";
            } elseif ($arr_data['status'] == 3) {
                $condition = "WHERE sma.status = 'rejected' ";
            }
        }
        $condition2 = "";
        $site_pkey = isset($arr_data['site_name']) ? $arr_data['site_name'] : 0;
        if ($site_pkey != 0) {
            $condition2 = "AND site.site_pkey = $site_pkey";
        }

        $page = isset($arr_data['page']) ? (int)$arr_data['page'] : 1;
        $rowsPerPage = isset($arr_data['rows']) ? (int)$arr_data['rows'] : 10;
        $offset = ($page - 1) * $rowsPerPage;


        $arr_useraccess = $this->Useraccess->query("SELECT sma.site_master_approval_pkey, site.site_name, site.site_id, site.site_pkey, sma.remarks, sma.created_date, sma.created_by,
                                                            CONCAT(UPPER(LEFT(sma.status, 1)), LOWER(SUBSTRING(sma.status, 2))) AS status 
                                                            FROM site_master_approval sma "
            . "LEFT JOIN site site ON (site.site_pkey = sma.site_fkey) "
            . $condition . $condition2
            . " ORDER BY sma.created_date DESC "
            . "LIMIT $offset, $rowsPerPage;");

        $total_count = $this->Useraccess->query("SELECT count(distinct(sma.site_master_approval_pkey)) as cnt FROM site_master_approval sma left join site site ON (site.site_pkey = sma.site_fkey)
                                                     $condition  $condition2");

        $rows = array();
        foreach ($arr_useraccess as $key => $val) {
            $rows[] = array_merge($val['sma'], $val['site'], $val[0]);
        }

        $resp_data["rows"] = $rows;
        $resp_data["total"] = $total_count[0][0]['cnt']; // Total count of records
        echo json_encode($resp_data);
    }

    public function viewapproval($sma_pkey = 0, $status = '')
    {

        $user = $this->Session->read('login_user_id');
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_site_history = array();
        $arr_site_edited = array();
        $arr_shift_new = array();
        $arr_shift_delete = array();
        $this->set('arr_shift_delete', $arr_shift_delete);
        $this->set('arr_shift_new', $arr_shift_new);
        try{
        $arr_site_history = $this->Useraccess->query("SELECT site.*, branches.branch_name,payment_mode.mode, contacts.company_name, contacts.phone
                                                            FROM site 
                                                            LEFT JOIN branches ON branches.branch_code = site.branch_code
                                                            LEFT JOIN payment_mode ON payment_mode.payment_mode_pkey = site.payment_mode
                                                            LEFT JOIN contacts ON contacts.contact_id = site.contact_name
                                                            WHERE site.site_pkey IN (SELECT DISTINCT site_fkey FROM site_master_approval WHERE site_master_approval_pkey = :sma_pkey);", ['sma_pkey' => $sma_pkey]);
        $arr_site_edited = $this->Useraccess->query(
            "SELECT fieldname, old_value, new_value
                                                            FROM site_master_approval_details AS sma 
                                                            WHERE site_master_approval_fkey = :sma_pkey
                                                            AND type = 'general' AND fieldname != 'pending';",
            ['sma_pkey' => $sma_pkey]
        );
        // debug($sma_pkey);
        // debug($arr_site_edited); exit;

        foreach ($arr_site_edited as $key => $details) {
            $old_value =  isset($details['sma']['old_value']) ? $details['sma']['old_value'] : '';
            $new_value =  isset($details['sma']['new_value']) ? $details['sma']['new_value'] : '';
            if (isset($details['sma']['fieldname']) && trim($details['sma']['fieldname']) == 'payment_mode') {
                $old_value1 = $this->Useraccess->query("SELECT mode FROM payment_mode WHERE payment_mode_pkey = $old_value");
                $old_value1 = isset($old_value1[0]['payment_mode']['mode']) ? $old_value1[0]['payment_mode']['mode'] : '';
                $new_value1 = $this->Useraccess->query("SELECT mode FROM payment_mode WHERE payment_mode_pkey = $new_value");
                $new_value1 = isset($new_value1[0]['payment_mode']['mode']) ? $new_value1[0]['payment_mode']['mode'] : '';
                $arr_site_edited[$key]['sma']['old_value'] = $old_value1;
                $arr_site_edited[$key]['sma']['new_value'] = $new_value1;
            } elseif (isset($details['sma']['fieldname']) && trim($details['sma']['fieldname']) == 'branch_code') {
                $old_value1 = $this->Useraccess->query("SELECT branch_name FROM branches WHERE branch_code = '$old_value'");
                $old_value1 = isset($old_value1[0]['branches']['branch_name']) ? $old_value1[0]['branches']['branch_name'] : '';
                $new_value1 = $this->Useraccess->query("SELECT branch_name FROM branches WHERE branch_code = '$new_value'");
                $new_value1 = $new_value1[0]['branches']['branch_name'];
                $arr_site_edited[$key]['sma']['old_value'] = $old_value1;
                $arr_site_edited[$key]['sma']['new_value'] = $new_value1;
            } elseif (isset($details['sma']['fieldname']) && trim($details['sma']['fieldname']) == 'user_pkey') {
               
                $old_value1 = $this->Useraccess->query("SELECT first_name,last_name FROM emp_details 
                                                        WHERE emp_pkey = $old_value");
                $first_name_old = isset($old_value1[0]['emp_details']['first_name']) ? $old_value1[0]['emp_details']['first_name'] : '';
                $last_name_old = isset($old_value1[0]['emp_details']['last_name']) ? $old_value1[0]['emp_details']['last_name'] : '';
                $old_value1 = $first_name_old . ' ' . $last_name_old;

                $new_value1 = $this->Useraccess->query("SELECT first_name,last_name FROM emp_details 
                                                        WHERE emp_pkey = $new_value");
                $first_name_new = isset($new_value1[0]['emp_details']['first_name']) ? $new_value1[0]['emp_details']['first_name'] : '';
                $last_name_new = isset($new_value1[0]['emp_details']['last_name']) ? $new_value1[0]['emp_details']['last_name'] : '';
                $new_value1 = $first_name_new . ' ' . $last_name_new;
                $arr_site_edited[$key]['sma']['old_value'] = $old_value1;
                $arr_site_edited[$key]['sma']['new_value'] = $new_value1;
            } elseif (isset($details['sma']['fieldname']) && trim($details['sma']['fieldname']) == 'contact_name') {
                $old_value1 = $this->Useraccess->query("SELECT company_name FROM contacts WHERE contact_id = $old_value");
                $old_value1 = isset($old_value1[0]['contacts']['company_name']) ? $old_value1[0]['contacts']['company_name'] : '';
                $new_value1 = $this->Useraccess->query("SELECT company_name FROM contacts WHERE contact_id = $new_value");
                $new_value1 = isset($new_value1[0]['contacts']['company_name']) ? $new_value1[0]['contacts']['company_name'] : '';
                $arr_site_edited[$key]['sma']['old_value'] = $old_value1;
                $arr_site_edited[$key]['sma']['new_value'] = $new_value1;
            }
        }

        $arr_new_site = $this->Useraccess->query(
            "SELECT fieldname, new_value
                                                        FROM site_master_approval_details AS sma 
                                                        WHERE site_master_approval_fkey = :sma_pkey
                                                        AND type = 'new_site' AND fieldname != 'pending';",
            ['sma_pkey' => $sma_pkey]
        );

        foreach ($arr_new_site as $key => $details) {
            $new_value =  isset($details['sma']['new_value']) ? $details['sma']['new_value'] : '';
            if (isset($details['sma']['fieldname']) && trim($details['sma']['fieldname']) == 'payment_mode') {
                $new_value1 = $this->Useraccess->query("SELECT mode FROM payment_mode WHERE payment_mode_pkey = $new_value");
                $new_value1 = isset($new_value1[0]['payment_mode']['mode']) ? $new_value1[0]['payment_mode']['mode'] : '';
                $arr_new_site[$key]['sma']['new_value'] = $new_value1;
            } elseif (isset($details['sma']['fieldname']) && trim($details['sma']['fieldname']) == 'user_pkey') {
      
                $new_value1 = $this->Useraccess->query("SELECT first_name,last_name FROM emp_details 
                                                        WHERE emp_pkey = $new_value");
                $first_name = isset($new_value1[0]['emp_details']['first_name']) ? $new_value1[0]['emp_details']['first_name'] : '';
                $last_name = isset($new_value1[0]['emp_details']['last_name']) ? $new_value1[0]['emp_details']['last_name'] : '';
                $new_value1 = $first_name . ' ' . $last_name;
                $arr_new_site[$key]['sma']['new_value'] = $new_value1;
            } elseif (isset($details['sma']['fieldname']) && trim($details['sma']['fieldname']) == 'contact_name') {
                $new_value1 = $this->Useraccess->query("SELECT company_name FROM contacts WHERE contact_id = $new_value");
                $new_value1 = isset($new_value1[0]['contacts']['company_name']) ? $new_value1[0]['contacts']['company_name'] : '';
                $arr_new_site[$key]['sma']['new_value'] = $new_value1;
            } elseif (isset($details['sma']['fieldname']) && trim($details['sma']['fieldname']) == 'branch_code') {
                $new_value1 = $this->Useraccess->query("SELECT branch_name FROM branches WHERE branch_code = '$new_value'");
                $new_value1 = $new_value1[0]['branches']['branch_name'];
                $arr_new_site[$key]['sma']['new_value'] = $new_value1;
            }
        }

        $this->set('status', $status);
        $this->set('user', $user);
        $this->set('arr_site_edited', $arr_site_edited);
        $this->set('arr_new_site', $arr_new_site);
        $site_pkey = $arr_site_history[0]['site']['site_pkey'];
        $this->set('arr_site_history', $arr_site_history);
        $this->set('sma_pkey', $sma_pkey);
        $this->set('site_pkey', $site_pkey);

        $arr_shift = $this->Useraccess->query(
            "SELECT site_transactions_fkey, fieldname, old_value, new_value
                                        FROM site_master_approval_details
                                        WHERE site_master_approval_fkey = :sma_pkey
                                        AND end_date_effective IS NULL
                                        AND type = 'shift'
                                        
                                        AND fieldname != 'pending'
                                        AND site_transactions_fkey IS NOT NULL
                                        ORDER BY site_transactions_fkey;",
            ['sma_pkey' => $sma_pkey]
        );


        $arr_shift_edit = array();
        foreach ($arr_shift as $key => $data) {
            if (trim($data['site_master_approval_details']['fieldname']) != '') {
                $key1 = $data['site_master_approval_details']['site_transactions_fkey'];
                if (trim($key1) != '') {
                    $arr_shift_edit[$key1][] = [
                        'fieldname' => $data['site_master_approval_details']['fieldname'],
                        'old_value' =>  $data['site_master_approval_details']['old_value'],
                        'new_value' => $data['site_master_approval_details']['new_value']
                    ];
                }
            }
        }

        foreach ($arr_shift_edit as $key2 => $datas) {

            $arr_shift_name = $this->Useraccess->query("SELECT wd.day_time_desc FROM site_transactions st
                                                        LEFT JOIN working_day_time_procedures wd ON wd.day_time_seq = st.day_time_seq_fkey
                                                        WHERE st.site_transactions_pkey = $key2");
            $shift_name = isset($arr_shift_name[0]['wd']['day_time_desc']) ? $arr_shift_name[0]['wd']['day_time_desc'] : '';
            $arr_shift_edit[$key2]['shift_name'] = $shift_name;
            foreach ($datas as $key3 => $data) {
                if (isset($data['fieldname']) && trim($data['fieldname']) == 'designation_id') {
                    $shift_old_value = isset($data['old_value']) ? $data['old_value'] : '';
                    $shift_new_value = isset($data['new_value']) ? $data['new_value'] : '';

                    $shift_old_value1 = $this->Useraccess->query("SELECT desig_name FROM designation WHERE id = $shift_old_value");
                    $shift_new_value1 = $this->Useraccess->query("SELECT desig_name FROM designation WHERE id = $shift_new_value");

                    $shift_old_value1 = isset($shift_old_value1[0]['designation']['desig_name']) ? $shift_old_value1[0]['designation']['desig_name'] : '';
                    $shift_new_value1 = isset($shift_new_value1[0]['designation']['desig_name']) ? $shift_new_value1[0]['designation']['desig_name'] : '';

                    $arr_shift_edit[$key2][$key3]['old_value'] =  $shift_old_value1;
                    $arr_shift_edit[$key2][$key3]['new_value'] =  $shift_new_value1;
                }
            }
        }


        $this->set('arr_shift_edit', $arr_shift_edit);

        $arr_shift_new = $this->Useraccess->query("SELECT wd.day_time_desc,desig.desig_name, st.emp_count, st.srate, st.eratess, st.start_date_effective, st.end_date_effective
                                                        FROM site_history st
                                                        LEFT JOIN designation desig ON ( desig.id =st.designation_id )
                                                        LEFT JOIN working_day_time_procedures wd ON (wd.day_time_seq = st.day_time_seq_fkey) 
                                                        WHERE st.status = 1 AND st.site_fkey = $site_pkey AND st.site_master_approval_fkey = $sma_pkey
                                                        AND st.site_transactions_pkey = 0 ");


        $this->set('arr_shift_new', $arr_shift_new);

        $arr_shift_delete = $this->Useraccess->query("SELECT wd.day_time_desc,desig.desig_name, st.emp_count, st.srate, st.eratess, st.start_date_effective, st.end_date_effective
                                                        FROM site_history st
                                                        LEFT JOIN designation desig ON ( desig.id =st.designation_id )
                                                        LEFT JOIN working_day_time_procedures wd ON (wd.day_time_seq = st.day_time_seq_fkey) 
                                                        WHERE st.status = 0 AND st.site_fkey = $site_pkey AND st.site_master_approval_fkey = $sma_pkey ");
    
        $this->set('arr_shift_delete', $arr_shift_delete);
    } catch (Exception $ex) {
        // debug($ex);
    }

    }

    public function saveApproved($sma_pkey = '', $site_pkey = '', $approve = '')
    {
        $message = true;
        $this->autoRender = false;
        $arr_data = $this->request->data;
        $this->SiteHistory->useDbConfig = $this->Session->read('ds');
        $this->SiteMaster->useDbConfig = $this->Session->read('ds');
        $this->SiteTransactions->useDbConfig = $this->Session->read('ds');
        $user = $this->Session->read('login_user_id');
        date_default_timezone_set('Asia/Kolkata');
        $current_time = date('Y-m-d H:i:s');
        $arr_form_data = array();
        $arr_form_data1 = array();
        $remarks = isset($arr_data['remarks']) ? $arr_data['remarks'] : '';
        $btn_approve = isset($arr_data['approve']) ? trim($arr_data['approve']) : '';
        if($btn_approve == ''){
            $btn_approve = $approve;
        }
        if ($btn_approve == 'true') {
            $sma_pkey = isset($arr_data['sma_pkey']) ? $arr_data['sma_pkey'] : $sma_pkey;
            $site_pkey = isset($arr_data['site_pkey']) ? $arr_data['site_pkey'] : $site_pkey;
            try {
                $arr_edited_transactions = $this->SiteMaster->query("SELECT sd.fieldname, sd.new_value, sd.site_transactions_fkey
                                                                        FROM site_master_approval sma
                                                                        LEFT JOIN site_master_approval_details sd ON (sma.site_master_approval_pkey = sd.site_master_approval_fkey)
                                                                        WHERE sma.site_master_approval_pkey = $sma_pkey
                                                                        AND sma.status = 'pending'
                                                                        AND type = 'shift'
                                                                        AND sd.fieldname != 'pending'
                                                                        AND sd.fieldname != 'status'
                                                                        ORDER BY sd.site_transactions_fkey
                                                                        ");

                if (count($arr_edited_transactions) > 0) {
                    foreach ($arr_edited_transactions as $data) {
                        $fieldname = isset($data['sd']['fieldname']) ? trim($data['sd']['fieldname']) : '';
                        $new_value = isset($data['sd']['new_value']) ? $data['sd']['new_value'] : '';
                        $site_transactions_fkey = isset($data['sd']['site_transactions_fkey']) ? $data['sd']['site_transactions_fkey'] : '';

                        if ($fieldname != '') {
                            $sql = "UPDATE site_transactions
                                        SET $fieldname = '$new_value'
                                        WHERE site_transactions_pkey = $site_transactions_fkey";

                            $update_site_transaction = $this->SiteMaster->query($sql);
                        }
                    }
                    $user = $this->Session->read('login_user_id');
                    date_default_timezone_set('Asia/Kolkata');
                    $current_time = date('Y-m-d H:i:s');
                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                                                SET status = 'approved',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                remarks = '$remarks'
                                                                                WHERE site_master_approval_pkey = $sma_pkey
                                                                                AND status = 'pending'");
                    $update_site_master_approval_details = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                                                        SET status = 'approved',
                                                                                        modified_by = '$user',
                                                                                        modified_date = '$current_time'
                                                                                        WHERE site_master_approval_fkey = $sma_pkey
                                                                                        AND type = 'shift'
                                                                                        AND status = 'pending'");
                }


                $arr_edited_fields = $this->SiteMaster->query(
                                                            "SELECT fieldname, old_value, new_value
                                                                    FROM site_master_approval_details AS sd 
                                                                    WHERE site_master_approval_fkey = :sma_pkey
                                                                    AND type = 'general' AND fieldname != 'pending';",
                    ['sma_pkey' => $sma_pkey]
                );


                if (count($arr_edited_fields) > 0) {
                    foreach ($arr_edited_fields as $data) {
                        $fieldname = isset($data['sd']['fieldname']) ? trim($data['sd']['fieldname']) : '';
                        $new_value = isset($data['sd']['new_value']) ? $data['sd']['new_value'] : '';


                        if ($fieldname != '') {

                            $sql = "UPDATE site
                                        SET $fieldname = '$new_value'
                                        WHERE site_pkey =  $site_pkey";

                            $update_site = $this->SiteMaster->query($sql);
                        }
                    }

                    $user = $this->Session->read('login_user_id');
                    date_default_timezone_set('Asia/Kolkata');
                    $current_time = date('Y-m-d H:i:s');
                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                                                SET status = 'approved',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                remarks = '$remarks'
                                                                                WHERE site_master_approval_pkey = $sma_pkey
                                                                                AND status = 'pending'");
                    $update_site_master_approval_details = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                                                        SET status = 'approved',
                                                                                        modified_by = '$user',
                                                                                        modified_date = '$current_time'
                                                                                        WHERE site_master_approval_fkey = $sma_pkey
                                                                                        AND type = 'general'
                                                                                        AND status = 'pending'");
                }




                $arr_created_shifts = $this->SiteMaster->query("SELECT site_history_pkey, site_transactions_pkey, day_time_seq_fkey, designation_id, emp_count, srate, eratess, created_by, creation_date, start_date_effective, end_date_effective FROM site_history
                                                                    WHERE site_fkey = $site_pkey
                                                                    AND site_master_approval_fkey = $sma_pkey
                                                                    AND status = 1
                                                                    AND site_transactions_pkey = 0
                                                                    AND approved_status = 'pending'");

                if (count($arr_created_shifts) > 0) {
                    foreach ($arr_created_shifts as $data) {
                        $site_trans_pkey = isset($data['site_history']['site_transactions_pkey']) ? $data['site_history']['site_transactions_pkey'] : '';

                        if ($site_trans_pkey != '') {

                            $dts_fkey = isset($data['site_history']['day_time_seq_fkey']) ? $data['site_history']['day_time_seq_fkey'] : '';
                            $des_id = isset($data['site_history']['designation_id']) ? $data['site_history']['designation_id'] : '';
                            $emp_count = isset($data['site_history']['emp_count']) ? $data['site_history']['emp_count'] : '';
                            $srate = isset($data['site_history']['srate']) ? $data['site_history']['srate'] : '';
                            $eratess = isset($data['site_history']['eratess']) ? $data['site_history']['eratess'] : '';
                            $created_by = isset($data['site_history']['created_by']) ? $data['site_history']['created_by'] : '';

                            $start_date_effective = isset($data['site_history']['start_date_effective']) ? $data['site_history']['start_date_effective'] : '';
                            $end_date_effective = isset($data['site_history']['end_date_effective']) ? $data['site_history']['end_date_effective'] : '';
                            $status = 1;
                            $site_his_pkey = isset($data['site_history']['site_history_pkey']) ? $data['site_history']['site_history_pkey'] : '';

                            $siteMasterTransactionsData = array();

                            $siteMasterTransactionsData = [
                                'site_fkey' => $site_pkey,
                                'site_master_approval_fkey' => $sma_pkey,
                                'day_time_seq_fkey' => $dts_fkey,
                                'designation_id' =>  $des_id,
                                'emp_count' => $emp_count,
                                'srate' => $srate,
                                'eratess' => $eratess,
                                'created_by' => $created_by,
                                'modified_by' => NULL,
                                'creation_date' => $current_time,
                                'modified_date' => NULL,
                                'start_date_effective' => $start_date_effective,
                                'end_date_effective' => $end_date_effective,
                                'status' => $status



                            ];



                            $this->SiteTransactions->save($siteMasterTransactionsData);
                            $site_transactions_pkey = $this->SiteTransactions->getLastInsertId();


                            $updateSiteHistory = $this->SiteTransactions->query("UPDATE site_history SET approved_status = 'approved'
                                                                                    WHERE site_history_pkey = $site_his_pkey
                                                                                    AND approved_status = 'pending'
                                                                                    ");
                        }
                    }

                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                                                SET status = 'approved',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                remarks = '$remarks'
                                                                                WHERE site_master_approval_pkey = $sma_pkey
                                                                                AND status = 'pending'");

                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                                                SET status = 'approved',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                site_transactions_fkey = $site_transactions_pkey
                                                                                WHERE site_master_approval_fkey = $sma_pkey
                                                                                AND status = 'pending'");
                }


                $arr_deleted_shifts = $this->SiteMaster->query("SELECT wd.day_time_desc,desig.desig_name, st.emp_count, st.srate, st.eratess, st.start_date_effective, st.end_date_effective, st.site_transactions_pkey
                                                    FROM site_history st
                                                    LEFT JOIN designation desig ON ( desig.id =st.designation_id )
                                                    LEFT JOIN working_day_time_procedures wd ON (wd.day_time_seq = st.day_time_seq_fkey) 
                                                    WHERE st.status = 0 AND st.site_fkey = $site_pkey AND st.site_master_approval_fkey = $sma_pkey ");

                if (count($arr_deleted_shifts) > 0) {
                    foreach ($arr_deleted_shifts as $data) {
                        $site_trans_pkey = isset($data['st']['site_transactions_pkey']) ? $data['st']['site_transactions_pkey'] : '';
                        if ($site_trans_pkey != '') {
                            $updateSiteTransaction = $this->SiteTransactions->query("UPDATE site_transactions 
                                                                                        SET status = 0
                                                                                        WHERE site_transactions_pkey = $site_trans_pkey");
                        }
                    }


                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                                                SET status = 'approved',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                remarks = '$remarks'
                                                                                WHERE site_master_approval_pkey = $sma_pkey
                                                                                AND status = 'pending'");

                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                                                SET status = 'approved',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time'
                                                                                WHERE site_master_approval_fkey = $sma_pkey
                                                                                AND status = 'pending'");
                }


                //New site
                $arr_new_site = $this->SiteMaster->query("SELECT site_pkey FROM site
                                                                WHERE status = 2 
                                                                AND site_pkey =$site_pkey ");


                if (count($arr_new_site) > 0) {
                    foreach ($arr_new_site as $details) {
                        
                        $site_pkey1 = isset($details['site']['site_pkey']) ? intval($details['site']['site_pkey']) : 0;
                        $update_site = $this->SiteMaster->query("UPDATE site SET status = 1 WHERE site_pkey = $site_pkey1");

                        $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                    SET status = 'approved',
                                                    modified_by = '$user',
                                                    modified_date = '$current_time',
                                                    remarks = '$remarks'
                                                    WHERE site_master_approval_pkey = $sma_pkey
                                                    AND status = 'pending'");

                        $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                    SET status = 'approved',
                                                    modified_by = '$user',
                                                    modified_date = '$current_time'
                                                    WHERE site_master_approval_fkey = $sma_pkey
                                                    AND status = 'pending'");
                    }
                }





                $message = true;
            } catch (Exception $ex) {
                $message = false;
            }

            if ($message) {
                return json_encode(array('success' => TRUE, 'message' => 'Approved successfully.'));
?>

          <?php  } else {
                return json_encode(array('success' => FALSE, 'message' => 'Failed to Approve.'));
            ?>

         <?php }
        } elseif ($btn_approve == 'false') {
            $user = $this->Session->read('login_user_id');
            date_default_timezone_set('Asia/Kolkata');
            $current_time = date('Y-m-d H:i:s');

            $sma_pkey = isset($arr_data['sma_pkey']) ? $arr_data['sma_pkey'] : $sma_pkey;
            $site_pkey = isset($arr_data['site_pkey']) ? $arr_data['site_pkey'] : $site_pkey;
            try {
                $arr_edited_transactions = $this->SiteMaster->query("SELECT sd.fieldname, sd.new_value, sd.site_transactions_fkey
                                                                        FROM site_master_approval sma
                                                                        LEFT JOIN site_master_approval_details sd ON (sma.site_master_approval_pkey = sd.site_master_approval_fkey)
                                                                        WHERE sma.site_master_approval_pkey = $sma_pkey
                                                                        AND sma.status = 'pending'
                                                                        AND type = 'shift'
                                                                        ORDER BY sd.site_transactions_fkey
                                                                        ");

                if (count($arr_edited_transactions) > 0) {

                    $user = $this->Session->read('login_user_id');
                    date_default_timezone_set('Asia/Kolkata');
                    $current_time = date('Y-m-d H:i:s');
                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                                                SET status = 'rejected',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                remarks = '$remarks'
                                                                                WHERE site_master_approval_pkey = $sma_pkey
                                                                                AND status = 'pending'");
                    $update_site_master_approval_details = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                                                        SET status = 'rejected',
                                                                                        modified_by = '$user',
                                                                                        modified_date = '$current_time'
                                                                                        WHERE site_master_approval_fkey = $sma_pkey
                                                                                        AND type = 'shift'
                                                                                        AND status = 'pending'");
                }

                $arr_edited_fields = $this->SiteMaster->query("SELECT sd.fieldname, sd.new_value
                                                                    FROM site_master_approval sma
                                                                    LEFT JOIN site_master_approval_details sd ON (sma.site_master_approval_pkey = sd.site_master_approval_fkey)
                                                                    WHERE sma.site_master_approval_pkey = $sma_pkey
                                                                    AND sma.status = 'pending'
                                                                    AND type = 'general'
                                                                    ORDER BY sd.site_transactions_fkey
                                                                    ");
                if (count($arr_edited_fields) > 0) {

                    $user = $this->Session->read('login_user_id');
                    date_default_timezone_set('Asia/Kolkata');
                    $current_time = date('Y-m-d H:i:s');
                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                                                SET status = 'rejected',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                remarks = '$remarks'
                                                                                WHERE site_master_approval_pkey = $sma_pkey
                                                                                AND status = 'pending'");
                    $update_site_master_approval_details = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                                                        SET status = 'rejected',
                                                                                        modified_by = '$user',
                                                                                        modified_date = '$current_time'
                                                                                        WHERE site_master_approval_fkey = $sma_pkey
                                                                                        AND status = 'pending'");
                }


                $arr_created_transactions = $this->SiteMaster->query("SELECT site_history_pkey, site_transactions_pkey FROM site_history 
                                                                    WHERE site_fkey = $site_pkey
                                                                    AND site_master_approval_fkey = $sma_pkey
                                                                    AND site_transactions_pkey = 0
                                                                    AND status = 1 ");



                if (count($arr_created_transactions) > 0) {
                    foreach ($arr_created_transactions as $data) {
                        $site_trans_pkey = isset($data['site_transactions']['site_transactions_pkey']) ? $data['site_transactions']['site_transactions_pkey'] : '';

                        if ($site_trans_pkey != '') {
                            $sql = "UPDATE  site_history
                                        SET approved_status = 'rejected' 
                                        WHERE site_transactions_pkey = 0
                                        AND status = '1'
                                        AND approved_status = 'pending'";
                            $update_site_transactions = $this->SiteMaster->query($sql);
                        }
                    }


                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                                                SET status = 'approved',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                remarks = '$remarks'
                                                                                WHERE site_master_approval_pkey = $sma_pkey
                                                                                AND status = 'pending'");

                    $update_site_master_approval_details = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                                                        SET status = 'approved',
                                                                                        modified_by = '$user',
                                                                                        modified_date = '$current_time'
                                                                                        WHERE site_master_approval_fkey = $sma_pkey
                                                                                        AND status = 'pending'");

                    $updateSiteHistory = $this->SiteTransactions->query("UPDATE site_history SET approved_status = 'approved'
                                                                                        WHERE site_master_approval_fkey = $sma_pkey
                                                                                        AND approved_status = 'pending'
                                                                                        AND site_transactions_pkey = 0
                                                                                        ");
                }

                $arr_deleted_transactions = $this->SiteMaster->query("SELECT site_history_pkey, site_transactions_pkey FROM site_history
                                                                        WHERE site_fkey = $site_pkey
                                                                        AND site_master_approval_fkey = $sma_pkey
                                                                        AND status = '0'
                                                                        AND site_transactions_pkey != 0
                                                                        AND approved_status = 'pending'");
                if (count($arr_deleted_transactions) > 0) {
                    foreach ($arr_deleted_transactions as $data) {
                        $site_trans_pkey = isset($data['site_history']['site_transactions_pkey']) ? $data['site_history']['site_transactions_pkey'] : '';

                        if ($site_trans_pkey != '') {

                            $updateSiteTransaction = $this->SiteTransactions->query("UPDATE site_history
                                                                                        SET approved_status = 'rejected'
                                                                                        WHERE site_transactions_pkey = $site_trans_pkey");
                        }
                    }

                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                                                SET status = 'rejected',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time',
                                                                                remarks = '$remarks'
                                                                                WHERE site_master_approval_pkey = $sma_pkey
                                                                                AND status = 'pending'");

                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                                                SET status = 'rejected',
                                                                                modified_by = '$user',
                                                                                modified_date = '$current_time'
                                                                                WHERE site_master_approval_fkey = $sma_pkey
                                                                                AND status = 'pending'");
                }


                //New site
                $arr_new_site = $this->SiteMaster->query("SELECT site_pkey FROM site
                                    WHERE status = 2 
                                    AND site_pkey = $site_pkey ");

                if (count($arr_new_site) > 0) {
                    $update_site = $this->SiteMaster->query("UPDATE site SET status = 2 WHERE site_pkey = $site_pkey");

                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval
                                                            SET status = 'rejected',
                                                            modified_by = '$user',
                                                            modified_date = '$current_time',
                                                            remarks = '$remarks'
                                                            WHERE site_master_approval_pkey = $sma_pkey
                                                            AND status = 'pending'");

                    $update_site_master_approval = $this->SiteMaster->query("UPDATE site_master_approval_details
                                                            SET status = 'rejected',
                                                            modified_by = '$user',
                                                            modified_date = '$current_time'
                                                            WHERE site_master_approval_fkey = $sma_pkey
                                                            AND status = 'pending'");
                }
                $message = true;
            } catch (Exception $ex) {
                $message = false;
            }

            if ($message) {
                return json_encode(array('success' => TRUE, 'message' => 'Rejected successfully.'));
            ?>

          <?php  } else {
                return json_encode(array('success' => FALSE, 'message' => 'Failed to Reject.'));
            ?>

         <?php }
        }
    }

    //Confirmation modal
    public function confirmation_modal($sma_pkey = 0, $status = '', $site_pkey = 0, $action = '')
    {
        $user_group = $this->Session->read('user_group');
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->set('sma_pkey', $sma_pkey);
        $this->set('status', $status);
        $this->set('site_pkey', $site_pkey);
        $this->set('action', $action);
    }
}
