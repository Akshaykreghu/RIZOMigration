<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    public $uses = array('UserCredentials', 'CentralControl', 'CentralUserCredentials', 'Registrations', 'Currency', 'Country', 'MasterDb', 'EmployeeMenu', 'Menu', 'CompanyContactInfo', 'EmployeeCTC');
    public $layout = null;
    public $components = array('Email', 'DataTable', 'Session', 'RequestHandler');
    public function beforeFilter()
    {
        parent::beforeFilter();
//added for remove error page of session out by megha on 22-04-2025
$user_group = $this->Session->read('user_group'); 
if ($user_group !=1 && $user_group !=2) {
    $this->redirect(array('controller' => 'Site', 'action' => 'login'));
}
//end
        if ($this->Session->read("company_key")) {

            $company_key = $this->Session->read("company_key");
            if (($company_key) && $company_key != '') {

                $controldb_config = ConnectionManager::getDataSource('controldb')->config;
                $link = mysql_connect('127.0.0.1', 'mpm_cntrl_usr', 'MyPyR01@Cntr1#LB');
                mysql_select_db($controldb_config['database'], $link);
                $res = mysql_query('SELECT * FROM central_control WHERE control_pkey = ' . $company_key);
                $row = mysql_fetch_assoc($res);
                if (!empty($row)) {
                    $dbUser = $row['Admin_name'];
                    $dbName = $row['user_db'];
                    $config = array();
                    // Set correct database name
                    // Add new config to registry
                    //ConnectionManager::create('companydb', $config);
                    $controldb = array(
                        'datasource' => 'Database/Mysql',
                        'persistent' => false,
                        'host' => '127.0.0.1',
                        'login' => $dbUser,
                        'password' => $row['user_pwd'],
                        'database' => $dbName,
                        'prefix' => '',
                        //'encoding' => 'utf8',
                    );
                    ConnectionManager::create('companydb', $controldb);
                    $this->Session->write("ds", 'companydb');
                    // Add new config to registry
                    //ConnectionManager::create('companydb', $controldb);
                    // Point model to new config
                    //  $this->useDbConfig = 'companydb';
                    //$this->setDataSource('companydb');
                    //Logo fetching
                    if ($this->Session->read('company_logo') == '') {
                        //$dirsep = "\/";
                        //$file_webroot_path = $dirsep . "files" . $dirsep . "companylogos" . $dirsep . $row['company_code'] . $dirsep;
                        $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
                        $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
                        $company_logo = isset($arr_comp_contact_info['CompanyContactInfo']['logo']) ? $arr_comp_contact_info['CompanyContactInfo']['logo'] : '';
                        $company_name = isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : '';
                        $this->Session->write('company_logo', $company_logo);
                        $this->Session->write('company_name', $company_name);
                    }
                }
            }
            $this->Menu->useDbConfig = $this->Session->read('ds');
            $menudb = $this->Menu->find("all");

            $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
            $user_group = $this->Session->read("user_group");
            $this->set('user_group', $user_group);
            $conditions = array();
            if ($user_group == '2') {
                $userPkey = $this->Session->read("emp_fkey");

                $noti = $this->EmployeeMenu->query("select leaveentries.EMP_fkey,empdetails.first_name,empdetails.last_name from leaveentries left join emp_details as empdetails on (empdetails.emp_pkey = leaveentries.EMP_fkey) where (ISAutherizedby ='$userPkey' and ISAutherized = '0'  AND LEAVESTATUS IN('Applied')) or (APPROVEDBY = '$userPkey' and ISAPPROVED = '0' and ISAutherized = '1' AND LEAVESTATUS IN('Authorized'))");
                $this->set("noti", $noti);
                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                $users = $this->UserCredentials->find('first', array("conditions" => array("emp_fkey" => $userPkey)));
                $user = $users['UserCredentials'];
                $user['avatar'] = isset($user['avatar']) ? $user['avatar'] : "img/picture.jpg";
                $this->set("user", $user);
                $empevents = $this->EmployeeMenu->query("  select 'BIR', first_name,date_format(date_of_birth,'%M-%d') date_month,emp_pkey from emp_details where date_format(date_of_birth,'%m-%d') between date_format(current_date,'%m-%d') and date_format(date_add(current_date,INTERVAL 7 DAY),'%m-%d') and  status = 1
                                                                                      union
                                                                                      select 'JOIN', first_name,date_format(joining_date,'%M-%d') date_month,emp_pkey from emp_proff left join emp_details on (emp_details.emp_pkey = emp_proff.emp_fkey) where date_format(joining_date,'%m-%d') between date_format(current_date,'%m-%d') and date_format(date_add(current_date,INTERVAL 7 DAY),'%m-%d') and emp_details.status = 1
                                                                                      order by date_format(date_month,'%m-%d')");
                $this->set("empevents", $empevents);
                // For hiding the team leave notification menu -- Added By Nimisha On 01-06-2019 // start    

                $teamleavemenu = $this->EmployeeMenu->query("SELECT `menu_id` FROM `emp_menu` WHERE `menu_name` = 'Team Leave Requests' ");
                $teamleaveid = isset($teamleavemenu['0']['emp_menu']['menu_id']) ? $teamleavemenu['0']['emp_menu']['menu_id'] : '';
                $menu = $this->EmployeeMenu->query("SELECT `active` FROM `user_access` WHERE `user_fkey` = '$userPkey' AND `menu_id` = '$teamleaveid'");
                $showteamleavenoti = isset($menu['0']['user_access']['active']) ? ($menu['0']['user_access']['active']) : 'Y';
                $this->set("showteamleavenoti", $showteamleavenoti);
                $this->set("menu", $menu);
                //                                            $this->set("user_group",$user_group);

                //End  
            } else {
                //                                                  $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                //                                                  $fetch_config_wizd = $this->UserCredentials->query("SELECT * from wizard_config ");
                //                                                  if($fetch_config_wizd['0']['wizard_config']['link'] == 'Site/config'){
                //                                                      $this->redirect(array('controller' => 'Site', 'action' => 'config'));
                //                                                  }
                $user['avatar'] = ($this->Session->read('company_logo') != '') ? $this->Session->read('company_logo') : 'img/avatar5.png';
                $this->set("user", $user);
            }
            //added by megha on 25/03/2022 site enddate notification
            $company_code = strtolower($this->Session->read("company_code"));
            // var_dump($compzzzany_code); exit;
            if ($company_code == 'vgfs' || $company_code == 'vsfs' || $company_code == 'gede' || $company_code == 'absg' || $company_code == 'demo' || $company_code == 'glet') {
                $notify = $this->EmployeeMenu->query(
                    "select distinct site.site_id,working_day_time_procedures.day_time_desc from site_transactions "
                        . "left join site on(site.site_pkey = site_transactions.site_fkey) "
                        . "left join working_day_time_procedures on(working_day_time_procedures.day_time_seq = site_transactions.day_time_seq_fkey) "
                        . "where DATEDIFF(site_transactions.end_date_effective,now()) > 0 and DATEDIFF(site_transactions.end_date_effective,now()) < 31 "
                        . "and site_pkey is not null and site.status = 1 and site_transactions.status = 1"
                );
                $this->set("notify", $notify);
            }
            // edited by anukrishanan_03-02-2025  open
            // $this->loadModel('EmployeeCTC');
            $fromDate = date("Y-m-01");
            $nextMonth = date("Y-m-d", strtotime("+1 month"));
            $entDate = date("Y-m-t", strtotime($nextMonth));

            $this->EmployeeCTC->useDbConfig = $this->Session->read('ds');
            // Edited by Akshay on 20-3-2025
            if ($company_code == 'demo' || $company_code == 'glet') {
                // if (true) {
                $incrementdata = $this->EmployeeCTC->query("
                                            SELECT e.emp_fkey, ei.EmpName, ei.employee_id, e.next_increment_date
                                                FROM emp_ctc_upload e
                                                JOIN employee_info ei ON e.emp_fkey = ei.emp_pkey
                                                WHERE e.next_increment_date BETWEEN '$fromDate' AND '$entDate'
                                                AND e.created_date = (
                                                    SELECT MAX(sub.created_date) 
                                                    FROM emp_ctc_upload sub 
                                                    WHERE sub.emp_fkey = e.emp_fkey
                                                )
                                                ORDER BY ei.EmpName ASC");

                $this->set('incrementdata', $incrementdata);
                $notificationCount = count($incrementdata);
                $this->set('notificationCount', $notificationCount);
            }
            // End

            // edited by anukrishanan_03-02-2025  close
            //site notification
            //$this->set("menu",$menu);
        } else {
            //				if ($this->request->is('ajax') ){//$this->RequestHandler->accepts(array('xml', 'rss', 'atom','json'))) {
            ////         				header("HTTP/1.0 401 Unauthorized");
            //						die;
            //					   }else{
            //					   	$this->redirect("/");
            //					   }

        }
        //edited by athira on 10-02-2025
        $this->Menu->useDbConfig = $this->Session->read('ds');
        $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = $plan['0']['comp_contact_info']['plan'];
        $this->set('plan', $plan);
        //end

    }
    public  function setDataSource($model)
    {

        $model->useDbConfig = $this->Session->read('ds');
    }
}
