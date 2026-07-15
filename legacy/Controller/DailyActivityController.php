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
class DailyActivityController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'DailyActivity';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('EmployeeDetails', 'Useraccess','ProjectActivity','EmployeeMenu', 'SiteMaster', 'SiteAttendance', 'SiteTransactions', 'Access_site', 'SiteHistory');
    public $components = array('MasterdataManagement');

    public function index() {
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
    public function activity_form($activity_pkey = 0){
	$this->SiteMaster->useDbConfig = $this->Session->read('ds');
        
         if($activity_pkey == 0){
            $title = 'Add Work Details';
        }
       
        else{
            $title = 'Edit Work Details';
        }
        $this->set('title', $title);
        $this->layout = NULL;
        $site = $this->SiteMaster->query("select site_pkey,site_id,site_name from site where status = 1 order by site_name desc");
        $this->set('site',$site); 
       
        $data = $this->SiteMaster->query("select * from project_activity where activity_pkey ='$activity_pkey' ");
        $this->set('arr_data',$data);
        
    }
    public function activity_save(){
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->ProjectActivity->useDbConfig = $this->Session->read('ds');
        $arr_form_datas = $this->request->data; 
        $project = $arr_form_data['project_fkey'] = $arr_form_datas['project'];
        $work_date = $arr_form_data['date'] = "'".$arr_form_datas['work_date']."'"; 
        $arr_form_data['work_detail'] = "'".$arr_form_datas['work_detail']."'"; 
        $activity_pkey = $arr_form_data['activity_pkey'] = $arr_form_datas['activity_pkey'];
        $int_datecount = 0;
        if ($work_date !='') {
            $int_datecount = $this->ProjectActivity->find("count", array(
            'conditions' => array('ProjectActivity.date' => $work_date, 'ProjectActivity.status' => 1 ,'ProjectActivity.activity_pkey'=> $activity_pkey)
            ));
        }
       
        if($activity_pkey){
            $time=$this->ProjectActivity->query("select now() as time");
            $this->set('time',$time);
            $arr_form_data['modified_date'] = "'".$time['0']['0']['time']."'";   
            $arr_form_data['modified_by'] = "'".$this->Session->read('user_name')."'"; 
            $this->ProjectActivity->updateAll($arr_form_data, array('activity_pkey' => $activity_pkey));
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Work Detail Updated Successfully";
            echo json_encode($resp);
        }else{
            if($int_datecount >0){
            $resp["msg"] = "Work detail already added.";
            $resp =array();
            $resp["success"] = false;
            echo json_encode($resp);
            }else{
            //$arr_form_data['creation_date'] = $time['0']['0']['time'];
            $arr_form_datas['project_fkey'] = $arr_form_datas['project'];
            $arr_form_datas['date'] = $arr_form_datas['work_date'];
            $arr_form_datas['created_by'] = "'".$this->Session->read('user_name')."'"; 
            $arr_form_datas['status']=1;
            $result = $this->ProjectActivity->save($arr_form_datas);
            $resp = array();
            $resp["success"] = true;
            $resp["msg"] = "Work Detail Added Successfully";
            echo json_encode($resp);
            }
        }
    }
    //added by megha on 19_06_19 search box
    public function filtersite() {
        $this->autoRender = false;
        $this->SiteMaster->useDbConfig = $this->Session->read('ds');

        $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
        if ($q != null) {
            $q_condition = " and (site_name like '%$q%' or site_id like '%$q%') ";
        } else {
            $q_condition = "";
        }

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
//        $user_group = $this->Session->read('user_group');
//        if ($user_group == 2) {
//            $cur_emp_key = $this->Session->read("emp_fkey");
////            $branch_condition = " and user_pkey=" . $cur_emp_key . " and site_pkey in (select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and status=1)";
//            $temp_site_keys1 = $this->SiteMaster->query("select site_pkey from site where user_pkey=" . $cur_emp_key." and status=1");
//            $temp_site_keys2 = $this->SiteMaster->query("select site_fkey from project_activity where emp_fkey=" . $cur_emp_key . " and site_fkey not in (select site_pkey from site where user_pkey=" . $cur_emp_key . " and status=1) and status=1");
//            $site_key = array();
//            foreach ($temp_site_keys1 as $val) {
//                $site_key[] = $val['site']['site_pkey'];
//            }
//            foreach ($temp_site_keys2 as $val) {
//                $site_key[] = $val['access_site']['site_fkey'];
//            }
//            if (count($site_key) > 0) {
//                $branch_condition = " and site_pkey in (" . implode(',', $site_key) . ") ";
//            } else {
//                $branch_condition = "";
//            }
//        } else {
//            $branch_condition = "";
//        }
        //employee branch wise sorting ends here

        $site_array = $this->SiteMaster->query("select * from site Where status = '1' $q_condition order by site_name asc");
     
        //$datas = $this->request->data;
        $array = array();
        $sites = "";
//        $branch[] = array("id" => "0", "text" => "ALL");
        foreach ($site_array as $key => $value) {
            //  $sites .= '<option >'.$value['site']['site_name'];
            $sites[] = array(
                'id' => $value['site']['site_pkey'],
                'text' => $value['site']['site_name'].'-'. $value['site']['site_id']
            );
        }
        $array['items'] = $sites;
        echo json_encode($array);
    }

    //end
   
 public function delete($activity_pkey = 0)
    {
	$this->ProjectActivity->useDbConfig = $this->Session->read('ds');
        $this->autoRender = FALSE ; 
        if($activity_pkey ==0){
        }
        if ($activity_pkey != 0) {
            $this->ProjectActivity->updateAll(array('status' => 0), array('activity_pkey' => $activity_pkey));
            echo json_encode(array('msg' => 'Daily activity deleted successfully!'));
        } else {
            echo json_encode(array('msg' => 'Daily activity deletion failed!'));
        }     
    }
	public function checkprojectexists($project_id = 0,$pkey = 0) {
        $this->autoRender = false;
        $arr_requestdata = $this->request->data;
        $project_id = isset($arr_requestdata['project_id']) ? $arr_requestdata['project_id'] : '';
		$pkey = isset($arr_requestdata['pkey']) ? $arr_requestdata['pkey'] : '';
		if($pkey !=''){
			$Where = "SiteMaster.site_pkey != $pkey";
		}else{
			$Where = "";
		}
        $int_procount = 0;
        if ($project_id != '') {
            $this->SiteMaster->useDbConfig = $this->Session->read('ds');
           $int_procount = $this->SiteMaster->find("count", array(
                'conditions' => array('SiteMaster.site_id' => $project_id, 'SiteMaster.status' => 1,$Where)
                    )
            );
        }
        echo $int_procount;
    }
    public function form2($siteid = 0) {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($siteid != 0) {
            $arr_policy = $this->EmployeeDetails->query("select * from site_transactions where status = '1' and site_transactions_pkey = '$siteid' ");
            $this->set('arr_policy', $arr_policy);
        }
        // debug($arr_policy);
        $arr_designations = $this->EmployeeDetails->query("select * from designation where status = '1' ");
        $this->set('arr_designations', $arr_designations);

        $arr_shifts = $this->EmployeeDetails->query("select day_time_seq,day_time_desc from working_day_time_procedures where active = '1' ");
        $this->set('arr_shifts', $arr_shifts);
    }

   
    public function lists($param = "") {
        $this->ProjectActivity->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $offset = ($page - 1) * $limit;
        $resp_data = array();

        $this->autoRender = false;
        $arr_data = $this->request->data;
        $categorypkey = isset($arr_data['project']) ? $arr_data['project'] : '0';
        if ($categorypkey == 0) {
            $cond = "";
        } else {
            $cond = " and project_fkey  = $categorypkey ";
        }
        $sortcolumn = isset($arr_data['sort']) ? $arr_data['sort'] : '';
        $sortorder = isset($arr_data['order']) ? $arr_data['order'] : '';
        if(isset($arr_data['site_name'])){
            $where[] = "(site.site_name like '%".$arr_data['site_name']."%') or (site.site_id like '%".$arr_data['site_id']."%')";
        }

        $arr_useraccess = $this->ProjectActivity->query("select site.site_name,site.site_id,site.site_pkey,project_activity.work_detail,
            project_activity.project_fkey,project_activity.date,project_activity.activity_pkey from project_activity 
            left join site on(project_activity.project_fkey = site.site_pkey)
            where site.status = '1' and project_activity.status = '1' $cond  ORDER BY project_activity.date DESC limit $limit offset $offset ");

        if ($sortcolumn != '' && $sortorder !== '') {
            $arr_order = array($sortcolumn . ' ' . $sortorder);
        } else {
            $arr_order = array('site_pkey DESC');
        }
        $totalcount = $this->ProjectActivity->query("SELECT count(*) as cnt FROM `project_activity`  left join site on(project_activity.project_fkey = site.site_pkey)
            where site.status = '1' and project_activity.status = '1' $cond ");

        $rows = array();
        foreach ($arr_useraccess as $key => $val) {
           //$rows[] = $val['site'];
            //$rows[] = array_merge($val['site'], $val['0']);
            $val['project_activity']['date'] = isset($val['project_activity']["date"]) ? date('d-m-Y',strtotime($val['project_activity']["date"])) : '';
            $rows[] = array_merge($val['site'], $val['project_activity']);
        }

        $resp_data["total"] = $totalcount['0']['0']['cnt'];
        $resp_data["rows"] = $rows;
        echo json_encode($resp_data);
    }

    public function save($emp_pkey = '', $s = '') {
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

    public function addDefault($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($outs = $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function resetDefault($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $removes = $this->EmployeeDetails->query("UPDATE user_access set active = 'N' where user_fkey = '$emp_fkey' and active = 'Y' ");
        if ($outs = $this->EmployeeDetails->query("CALL `insert_default_menu`('$emp_fkey')")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function deletemens($emp_fkey = 0) {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($removes = $this->EmployeeDetails->query("UPDATE user_access set active = 'N' where user_fkey = '$emp_fkey' and active = 'Y' ")) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Menu Added'
            ));
            ;
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Menu Not Added'
            ));
        }
    }

    public function listuseraccess() {
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

        //debug($arr_useraccess);
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
        //debug($arr_menu);
        $rows = array();
        //debug($arr_menu);
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


    public function Employee($user_pkey = 0, $DD = '') {
        $this->autoRender = false;
        //  debug($DD);
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        // $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = '#';
        //  $arr_empdashboard = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='EmpDashboard'");

        $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

        $pkey = $fetchUser['0']['Useraccess']['user_access_pkey'];
//  debug($pkey);
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

    public function saveProject() {
        $this->autoRender = false;
        $arr_data = $this->request->data;
        $this->SiteHistory->useDbConfig = $this->Session->read('ds');
        $this->SiteMaster->useDbConfig = $this->Session->read('ds');
        $this->SiteTransactions->useDbConfig = $this->Session->read('ds');
        $arr_form_data = array();
        //$arr_data_count = count($arr_data['TdDayTime']);
       // for ($i = 0; $i < $arr_data_count; $i++) {
            //if ($arr_data['editkey'][$i] == 0) {
                if ($arr_data['site_pkey']) {
                    $arr_form_data['site_pkey'] = isset($arr_data['site_pkey']) ? $arr_data['site_pkey'] : '';
                }
                $arr_form_data['site_id'] = $arr_data['project_id'];
                $arr_form_data['site_name'] = $arr_data['project_name'];
                $arr_form_data['latitude'] = $arr_data['latitude'];
                $arr_form_data['longitude'] = $arr_data['longitude'];
                $arr_form_data['address'] = $arr_data['address'];
                //$arr_form_data['work_details'] = $arr_data['work_details'];
                $arr_form_data['user_pkey'] = $arr_data['user_pkey'];
                $arr_form_data['contact_name'] = $arr_data['siteengineer1'];
                $arr_form_data['work_details'] = $arr_data['work_details'];
                $arr_form_data['special_remarks'] = $arr_data['special_remarks'];
				$arr_form_data['expected_starting_date'] = $arr_data['date_commencement_wo'];
                $arr_form_data['actual_starting_date'] = $arr_data['date_commencement_actual'];
				$arr_form_data['expected_compleation_date'] = $arr_data['date_completion_wo'];
                $arr_form_data['actual_completion_date'] = $arr_data['date_completion_actual'];
				$arr_form_data['allocated_fund'] = $arr_data['amountutilised'];
                $arr_form_data['released_fund'] = $arr_data['amountreleased'];
				$arr_form_data['customer_refno'] = $arr_data['siteengineer1'];
				$arr_form_data['po_expirydate'] = $arr_data['work_order_date'];
       //       $arr_form_data['jurisdiction'] = $arr_data['jurisdiction'];
                $arr_form_data['organization_id'] = isset($arr_data['organization_id']) ? 1 : 1;
                $this->SiteMaster->save($arr_form_data);

                $site_pkey = $this->SiteMaster->getLastInsertId();
                ;
           // }
      //  }
    }

    public function admin($user_pkey = 0, $DD = '') {
        $this->autoRender = false;
        //  debug($DD);
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        // $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = '0';
        //  $arr_empdashboard = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='EmpDashboard'");
// debug($adminid);
        //  debug($empid);
        $fetchUser = $this->Useraccess->query("select user_access_pkey,menu_id from user_access as Useraccess where menu_id  = '$adminid' and user_fkey ='$user_pkey'");

        $pkey = isset($fetchUser['0']['Useraccess']['user_access_pkey']) ? $fetchUser['0']['Useraccess']['user_access_pkey'] : '';
//  debug($pkey);
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

    public function get() {
        $this->autoRender = FALSE;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        //      debug($_POST['emp_id']);
        $empids = $_POST['emp_id'];
        $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
        // debug($user_pkey);
        // debug($arr_useraccess);
        $adminid = $arr_useraccess['0']['empmenu']['menu_id'];
        $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$empids' and menu_id = '$adminid' and active = 'Y' ");
        //  debug($useracess);
        $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';
        if ($access == 'Y') {
            $active = 'Y';
        } else {
            $active = 'N';
        }
// debug($active);
        echo json_encode($active);
    }

    public function deleteuser($user_access_pkey = 0) {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        if ($user_access_pkey != 0) {
            $this->Useraccess->updateAll(array('status' => 0), array('user_access_pkey' => $user_access_pkey));
            echo json_encode(array('msg' => 'User deletion successfull!'));
        } else {
            echo json_encode(array('msg' => 'User deletion failed!'));
        }
    }

    public function saveuseraccess() {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_data = $this->request->data;
        $menu_id = isset($arr_data['menu_id']) ? $arr_data['menu_id'] : '';
        $user_pkey = isset($arr_data['user_pkey']) ? $arr_data['user_pkey'] : '';
        $active = isset($arr_data['active']) ? $arr_data['active'] : '';
        //  debug($arr_data);
        $arr_useraccess = $this->Useraccess->find('all', array(
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

    public function get_incompleteDate($sitefkey = 0,$shift = 0) {
        //date_format(current_date,'%Y-%m-%d') and 
        // and  dt<=  (select distinct max(date_format(end_date_effective,'%Y-%m-%d')) from site_transactions  where site_fkey='$sitefkey' and site_transactions.status = '1' )"

        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $this->autoRender = false;
        $site_data = $this->Useraccess->query("select min(dt) start,max(dt) end from calendar_table where dt >= (select distinct min(date_format(start_date_effective,'%Y-%m-%d')) "
                . " from site_transactions  where site_fkey='$sitefkey' and site_transactions.status = '1' )"
                . "  and  dt<=  (select distinct max(date_format(end_date_effective,'%Y-%m-%d')) from site_transactions  where site_fkey='$sitefkey' and site_transactions.status = '1' ) "
                . "and  dt not in (select att_date  from  site_shift_close where site_fkey='$sitefkey' and
                day_time_seq_fkey ='$shift' and 				shift_closed_status='Y' and rec_status=1 )"
                //. "and att_date not in (select att_date  from site_attendance where site_fkey='101' and status<>3)"
                . "order by '1' ;");
        // debug($site_data);

        $first = $site_data['0']['0']['start'];
        $last = $site_data['0']['0']['end'];
        echo json_encode(array('success' => 1, 'first' => $first, 'last' => $last));
    }

    /* public function pnch() {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
//            $branch_condition = " and user_pkey=" . $cur_emp_key . " and site_pkey in (select site_fkey from access_site where emp_fkey=" . $cur_emp_key . " and status=1)";
            $temp_site_keys1 = $this->Useraccess->query("select site_pkey from site where user_pkey=" . $cur_emp_key." and status=1");
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
    } */

    public function get_shift($month = '', $fkey = '') {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $site_fkey = $fkey;
        $date_att = $month;
        $site_data = $this->Useraccess->query("select distinct day_time_seq_fkey,day_time_desc,on_dutty1,off_dutty1,working_time1,site_transactions.start_date_effective,
                            site_transactions.end_date_effective  from site_transactions 
                            join working_day_time_procedures
                            on (working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) 
                            where site_transactions.start_date_effective <= '$date_att' 
                            and IFNULL(site_transactions.end_date_effective, '3000-01-01') >= '$date_att' and 
                            site_transactions.site_fkey ='$site_fkey' and site_transactions.status = '1' and site_transactions.day_time_seq_fkey not in
                            (select day_time_seq_fkey from site_attendance where att_date='$date_att' and site_fkey='$site_fkey' and status=3) ");
        //debug($site_data);
        $array = array();
        $branch = array();
        //$site[] = array("id" => "0", "text" => "ALL");

        foreach ($site_data as $key => $value) {
            $site[] = array(
                'id' => $value['site_transactions']['day_time_seq_fkey'],
                'text' => $value['working_day_time_procedures']['day_time_desc']
            );
        }

        if (!empty($site)) {
            $array['items'] = $site;
        }

        //debug($array);
        echo json_encode($array);
    }

    public function load_sites($site_fkey = 0,$shift_fkey = 0,$date="") {
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
                . "and site_transactions.start_date_effective <='$att_date' and site_transactions.end_date_effective >='$att_date';");
        $this->set('site_data', $site_data);
    }

    public function add_site($site_fkey = 0, $shift_fkey = 0, $date = '') {
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
AS `EmpName`,`ep`.`emp_company_id` AS `employee_id`,`br`.`branch_name` AS `branch`,`designation`.`desig_name` AS `designation`
,`department`.`dept_name` AS `department`,`ep`.`joining_date` AS `joining_date` from ((((`emp_proff` `ep` 
join `emp_details` `ed` on((`ed`.`emp_pkey` = `ep`.`emp_fkey`))) left join `branches` `br` 
on((`ed`.`branch_code` = `br`.`branch_code`))) left join `designation` on((`ep`.`designation` = `designation`.`desig_code`)))
left join `department` on((`ep`.`emp_dept` = `department`.`dept_code`))) 
where ed.status =1 and ed.branch_code in (select branch_code from emp_details " . $branch_condition . "
)
and ep.joining_date <='$date'
and ed.emp_pkey not in (
select emp_fkey
 from site_attendance sa ,emp_details ed ,designation ,working_day_time_procedures wd
 where att_date ='$date'  and out_time is null
 and ed.emp_pkey=sa.emp_fkey
 and designation.id = sa.designation_id 
 and wd.day_time_seq=day_time_seq_fkey) ");

        $shift_data = $this->Useraccess->query("select * from working_day_time_procedures where day_time_seq = '$shift_fkey' ");
//        debug($site_data);
        $this->set('att_data', $date);
        $this->set('site_data', $site_data);
        $this->set('shift_data', $shift_data);
    }

//Shift close Button start
    public function shift_closure() {
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
                    ), array(
                'site_fkey' => $site_fkey,
                'att_date' => $datess,
                'day_time_seq_fkey' => $shift_fkey,
                'active' => 1
                    )
            );
            // $sql_update = "update site_attendance set status = '3', modified_by='$user_id', modified_date='$creation_date' where site_fkey='$site_fkey' and att_date='$datess' and day_time_seq_fkey='$shift_fkey' ";
            // debug($sql_update);
            // $sql_employee_shift = $this->SiteAttendance->query($sql_update);
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

    public function close_shift($site_fkey = 0, $shift_fkey = 0, $date = "") {
        //  $this->autoRender = false;
        $this->SiteAttendance->useDbConfig = $this->Session->read('ds');
        //$arr_form_data = $this->request->data;
        //debug($arr_form_data);
//        $att_date = $arr_form_data['att_date']; //'2018-01-17';
//        $site_fkey = $arr_form_data['site']; //'1';
//        $shift_fkey =  $arr_form_data['shift']; //'1';
        $shift_fkey = $shift_fkey;
        $site_fkey = $site_fkey;
        $att_date = $date;
//       $success = 'NO';
//       $messagess = "failure";
        $siteatt_data = array();
        $count = $this->SiteAttendance->query("select count(*) "
                . "from site_attendance "
                . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$att_date' and status = '1' ");
        if ($count > 0) {
            $siteatt_data = $this->SiteAttendance->query("select * "
                    . "from site_attendance "
                    . " where day_time_seq_fkey = '$shift_fkey' and site_fkey ='$site_fkey' and att_date = '$att_date' and status = '2' ");
        }
        //debug($siteatt_data);
//       if(!empty($siteatt_data)){
//           $success = 'YES';
//           $messagess = "success";
//       }
//       
//       $arrResponse = array(
//                'success' => $success,
//                'message' => $messagess,
//                'data' => $siteatt_data
//            );

        $this->set('siteatt_data', $siteatt_data);
        // echo json_encode($arrResponse);
    }

    //Shift close Button end
    public function data_site() {
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
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



//        print_r($sql);die();
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

//        var_dump($row_emp_details);

        $row_emp_details2 = $this->Useraccess->query($sql2);
        //debug($row_emp_details2);
        $arr_emps = array();
        foreach ($row_emp_details as $key => $value) {
            $arr_emps[] = $value['sa']['emp_fkey'];
        }
        $unset_array = array();

        foreach ($row_emp_details2 as $key => $value) {
            $keyFound = array_search($value['sa']['emp_fkey'], $arr_emps);
//            var_dump(gettype($keyFound));
//            unset($row_emp_details[$keyFound]);
            if (gettype($keyFound) == "integer") {
                $unset_array[] = $keyFound;
                unset($row_emp_details[$keyFound]);
            }
        }
//        var_dump($unset_array);
        $unique_unset = array_unique($unset_array);

        if (!empty($unique_unset) && $unique_unset != FALSE) {
            foreach ($unique_unset as $val) {
                unset($row_emp_details[$val]);
            }
        }
        $row_emp_details = array_values($row_emp_details);
//        $row_emp_details['inner'] = $row_emp_details2;

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

//        debug($arrResponse);
        $this->set('arrResponse', $arrResponse['data']);
    }

    //Activate / Deactivate button for Punch transactions start

    public function mark_activestatus($emp_fkey = 0) {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $created_by = 'Admin';
        $creation_date = date("Y-m-d H:i:S");
        //debug($arr_form_data);
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

    public function mark_deactivestatus($emp_fkey = 0) {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $created_by = 'Admin';
        $creation_date = date("Y-m-d H:i:S");
        //debug($arr_form_data);
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

    public function mark_attendance($emp_fkey = 0) {
        $this->autoRender = false;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $login_user_id = $this->Session->read('login_user_id');

        $arr_form_data = $this->request->data;
        //debug($arr_form_data);
        $site_attendance_pkey = $arr_form_data['site_attendance_pkey'];

        $site_fkey = $arr_form_data['site_fkey'];
        $user_id = $arr_form_data['day_time_seq_fkey'];
        $day_time_seq_fkey = $arr_form_data['day_time_seq_fkey'];
        $designation_id = $arr_form_data['designation_id'];
        $out_times = date("H:i:s", strtotime($arr_form_data['out_time']));

        $in_time = date("H:i:s", strtotime($arr_form_data['out_time']));
        //debug($in_time);
        $att_date = $arr_form_data['att_date'];
        $checkout_time = isset($arr_form_data['checkout_time']) ? date("H:i:s", strtotime($arr_form_data['checkout_time'])) : '';
        $checkin_time = isset($arr_form_data['checkin_time']) ? date("H:i:s", strtotime($arr_form_data['checkin_time'])) : '';
        $created_by = 'Admin';
        $creation_date = date("Y-m-d H:i:S");
        $mobile_pkey = 0;
        $arr_response = array();
        $arr_resp = array();
        $success;
        if ($site_attendance_pkey != null) {
            // edited by megha on 30/08/2019 out time next day 1
//        if($arr_form_data['out_time']>'23:59'){
//        $out_date = isset($arr_form_data['out_date'])?$arr_form_data['out_date']:$att_date;
//        }else{
//        $out_date =  $att_date; 
//        }
            //$out_time = $att_date.' '.$out_times;
            // edited by megha on 30/08/2019 out time next day 2
//            if($out_date > $att_date){
//             $out_time = $out_date.' '.$out_times;  
//             }else{
//              $out_time = $att_date.' '.$out_times;
//             }
            if ('00:00:00' <= $out_times) {
                if ($out_times <= $checkout_time) {
                    //  debug($out_times);
                    $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
                } else {
                    $out_date = $att_date;
                }
            } else {
                $out_date = $att_date;
            }
            $out_time = $out_date . ' ' . $out_times;
            // end out time next day 2
            // debug("SELECT `mark_site_attendance_out_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', null , '$out_time', '$att_date')  as resps ");
            $insert_cjeck_sql1 = "SELECT `mark_site_attendance_out_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', null , '$out_time', '$att_date')  as resps ";
            //debug($insert_cjeck_sql1);
// var_dump($insert_cjeck_sql1);
            $row_emp_details = $this->Useraccess->query($insert_cjeck_sql1);

            //debug($row_emp_details);
            $resps = $row_emp_details[0][0]['resps'];
            //debug($resps);
            $success = 1;
            $messagess = "Punch out successfully completed";
            if ($resps == 'update') {
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
//                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                if ($user_group == 2) {
//                    $userid = $this->Session->read("login_user_id");
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
            //  $in_time = $att_date.' '.$in_time;
//            if($arr_form_data['out_time']>'23:59'){
//            $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
//            }else{
//            $out_date =  $att_date; 
//            }

            if ($checkin_time > $in_time) {
                $out_date = date('Y-m-d', strtotime($att_date . ' +1 day'));
            } else {
                $out_date = $att_date;
            }
            $in_time = $out_date . ' ' . $in_time;
            $insert_cjeck_sql1 = "SELECT `mark_site_attendance_fn`('$site_fkey', '$user_id', '$day_time_seq_fkey', '$designation_id', '$emp_fkey', '$in_time', null, '$att_date')  as resps ";

            // var_dump($save_sql1);
            //debug($insert_cjeck_sql1);
            $res_insert_cjeck_sql1_sql1 = $this->Useraccess->query($insert_cjeck_sql1);
            $resps = $res_insert_cjeck_sql1_sql1[0][0]['resps'];
            if ($resps == 'insert') {

//            $emp_id = $row_emp_details[0]['emp_id'];
                $user_group = $this->Session->read('user_group');
                $user = $this->Session->read('company_code');
//                if ($user_group == 2 && ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs')) {
                if ($user_group == 2) {
//                    $userid = $this->Session->read("login_user_id");
                    $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_fkey','$att_date','$in_time','$login_user_id')";
                } else {
                    $save_sql1 = "insert into site_attendance(site_fkey,mobile_pkey,day_time_seq_fkey,designation_id,emp_fkey,att_date,in_time,created_by) values('$site_fkey','$mobile_pkey','$day_time_seq_fkey','$designation_id','$emp_fkey','$att_date','$in_time','$created_by')";
                }
                // var_dump($save_sql1);

                $res_sql1 = $this->Useraccess->query($save_sql1);
//                $res_sql1->execute();
                $success = 1;
                $messagess = "Punch in successfully completed";
                $new_login_auditor_pkey = $this->Useraccess->getLastInsertID();
            } else {
                $success = 0;
                $messagess = $resps;
            }

            $arr_resp['uploaded_time'] = $creation_date;
//            $arr_resp['site_attendance_pkey'] = $new_login_auditor_pkey;
            $arr_resp['mob_pkey'] = $mobile_pkey;
            //debug($arr_resp);
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

    public function site_allocate($site_pkey = 0) {

        // debug($site_pkey);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
        $this->set("site_pkey", $site_pkey);
        //The below code is to display only unallocated employees to the specified store in employee list. By ***ARUL P DAS on 17/1/2020
        // $condition="`emp_pkey` not in (select emp_pkey from site where site_pkey=$site_pkey and site.status=1)";
        $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1)));
        $this->set("arr_employees", $arr_employees);
        //query edited by ***ARUL P DAS on 17/1/2020
        $arr_employees_allocates = $this->EmployeeDetails->query("select distinct emp_fkey,emp_details.first_name,last_name from access_site join emp_details on (emp_details.emp_pkey = access_site.emp_fkey) where site_fkey = '$site_pkey' and access_site.status = '1'");
        // 
//        $arr_employees_allocates = $this->EmployeeDetails->query(" select emp_pkey,emp_details.first_name,last_name from access_store join emp_details on (emp_details.emp_pkey = access_store.emp_fkey) where store_pkey = '$store_pkey' and access_store.status = '1' and emp_pkey != access_store.emp_fkey ");
        $this->set("arr_employees_allocates", $arr_employees_allocates);
//            debug($arr_employees_allocates);
    }

    public function save_allocate() {
        $this->autoRender = FALSE;
        // $this->Store->useDbConfig = $this->Session->read('ds');
        // $this->site_access->useDbConfig = $this->Session->read('ds');
        $this->Access_site->useDbConfig = $this->Session->read('ds');

        $this->layout = null;
        $result = array('success' => 0);
        $arr_form_data = $this->request->data;
        // debug($arr_form_data);
        $emps = $arr_form_data['emps'];
        $site_code = $arr_form_data['site_code'];
        $resp_att = array();
        try {
            $this->Access_site->query(" insert into access_site(site_fkey,emp_fkey) values('$site_code','$emps') ");
            $resp_att['success'] = 1;
            $resp_att['msg'] = "Saved Successfully ";
            echo json_encode($resp_att);
        } catch (Exception $e) {
            $resp_att['success'] = 0;
            $resp_att['msg'] = "Saving failed ";
            echo json_encode($resp_att);
        }
    }

    public function remove_allocate() {
        $this->autoRender = FALSE;
        // $this->Store->useDbConfig = $this->Session->read('ds');
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

}
