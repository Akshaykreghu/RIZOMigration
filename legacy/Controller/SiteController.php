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
App::uses('LoginAppController', 'Controller');
ini_set('max_execution_time', 300);
App::uses('ConnectionManager', 'Cake\Datasource');
App::uses('CakeEmail', 'Network/Email');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
ini_set("display_errors", 1);

class SiteController extends LoginAppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Site';
    public $layout = 'login';

    /**
     * This controller does not use a model
     *
     * @var array
     */


    public $uses = array('UserCredentials', 'Useraccess', 'login_auditor', 'CentralControl', 'CentralUserCredentials', 'Registrations', 'Currency', 'Country', 'MasterDb', 'EmployeeMenu', 'ReportAudit', 'CompanyContactInfo', 'LeavePolicy');
    public $components = array('LoginManagement', 'Session', 'Email');

    public function index()
    {

        /*
          if ($this -> Auth -> loggedIn()) {
          $this -> redirect(array('controller' => 'App', 'action' => 'index'));
          } */

        //$result = $this->send(); 
        /*
          $countries = array();
          $currencies = array();

          $countries = $this -> Country -> find('list', array('fields' => array('Country.country_code', 'Country.name'), 'recursive' => 0));
          $currencies = $this -> Currency -> find('list', array('fields' => array('Currency.currency_code', 'Currency.currency_name'), 'recursive' => 0));

          $this -> set("countries", $countries);
          $this -> set("currencies", $currencies); */
    }

    public function mailSend()
    {
        # code...
        App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
        $mail = new PHPMailer;
        $mail->SMTPDebug = 2;                               // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
        $mail->Password = 'welcome123';                           // SMTP password
        $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
        $mail->addAddress('test-9uoipp5jk@srv1.mail-tester.com', 'Tester.com');     // Add a recipient
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = 'Added a new Employee';
        $mail->Body = 'This is the HTML message body <b> Name:sasac </b>';
        //$mail->Subject  = '<hr><h1><strong>HI ! '.$empname.' </strong></h1>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $this->autoRender = FALSE;

        if (!$mail->send()) {
            //echo 'Message could not be sent.';
            //echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            //echo 'Message has been sent';
        }
    }



    public function employeeLoginAudit()
    {
        $this->ReportAudit->useDbConfig = $this->Session->read('ds');
        $dataForHistory = array();
        $dataForHistory['report_type'] = "Logged in";
        $dataForHistory['mode'] = "Auth";
        $user_id = $this->Session->read('login_user_id');
        $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
        $user_name = $this->Session->read('user_name');
        $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
        $dataForHistory['status'] = 1;
        $this->ReportAudit->save($dataForHistory);
    }

    public function adminLoginAudit()
    {
        $company_code = $this->Session->read('company_code');

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

            $this->ReportAudit->useDbConfig = $this->Session->read('ds');

            $dataForHistory = array();
            $dataForHistory['report_type'] = "Logged in";
            $dataForHistory['mode'] = "Auth";
            $user_id = $this->Session->read('login_user_id');
            $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
            $user_name = $this->Session->read('user_name');
            $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
            $dataForHistory['status'] = 1;
            $this->ReportAudit->save($dataForHistory);
        }
    }

    public function getCompanyType($companyCode, $user_pkey)
    {

        $this->CentralControl->setDataSource('controldb');

        // check if the login attempt is from a hedge submdomains
        $query_check_company = $this->CentralControl->find('first', array(
            'fields' => 'CentralControl.*',
            'conditions' => array('company_code' => $companyCode, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
        ));
        $subdomain = isset($query_check_company['CentralControl']['subdomain']) ? $query_check_company['CentralControl']['subdomain'] : '';
        if ($subdomain != '') {

            $sessionAdmin = $subdomain;
            $multiadmin_user_pkey = $user_pkey;

            $arr_list_of_accesses_sites = $this->CentralControl->query("select control_pkey,company_code,company_name,Address from central_control where control_pkey in (select control_fkey from super_admin_access  where subdomain='" . $sessionAdmin . "' and user_fkey= " . $multiadmin_user_pkey . " );");
            if (!empty($arr_list_of_accesses_sites)) {
                $this->Session->write("multiadmin", $sessionAdmin);
                $this->Session->write("multiadmin_user_pkey", $user_pkey);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function companies_list()
    {
        // $this->autoRender = FALSE;
        $sessionAdmin = $this->Session->read('multiadmin');
        $multiadmin_user_pkey = $this->Session->read('multiadmin_user_pkey');
        $this->CentralControl->setDataSource('controldb');

        if ($sessionAdmin) {
            $arr_list_of_accesses_sites = $this->CentralControl->query("select control_pkey,company_code,company_name,Address from central_control where control_pkey in (select control_fkey from super_admin_access  where subdomain='" . $sessionAdmin . "' and user_fkey= " . $multiadmin_user_pkey . " );");

            $this->set('arr_list_of_accesses_sites', $arr_list_of_accesses_sites);
            $this->render('admin_lists');
        } else {
            $this->redirect(array('controller' => 'Site', 'action' => 'login'));
        }

        // login in based from the Hedge admins
        // $arr_company_db = $this->CentralControl->find('all', array('fields' => 'CentralControl.*',
        //     'conditions' => array('subdomain' => $sessionAdmin, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
        // ));


    }

    public function loginWithCentral($companyCode = '')
    {
        $this->autoRender = FALSE;

        $sessionAdmin = $this->Session->read('multiadmin');
        $multiadmin_user_pkey = $this->Session->read('multiadmin_user_pkey');

        if ($sessionAdmin) {

            $this->CentralUserCredentials->setDataSource('controldb');

            $arr_central_user_details = $this->CentralUserCredentials->find('first', array('conditions' => array("company_code" => $companyCode)));

            $this->CentralControl->setDataSource('controldb');
            $arr_company_db = $this->CentralControl->find('first', array(
                'fields' => 'CentralControl.*',
                'conditions' => array('company_code' => $companyCode, 'end_date_effective >= ' . date("Y-m-d"))
            ));

            if (isset($arr_company_db['CentralControl'])) {
                $reset_login_flag = isset($arr_central_user_details['CentralUserCredentials']['reset_login_flag']) ? $arr_central_user_details['CentralUserCredentials']['reset_login_flag'] : '';

                if ($reset_login_flag == 'Y') {
                    // Skipped this for temporary add it after finishing this

                } else {
                    //$this->Session->write('login_user_id', $arr_central_user_details['CentralUserCredentials']['user_id']);
                    $this->Session->write('login_user_id', isset($arr_central_user_details['CentralUserCredentials']['user_id']) ? strtoupper($arr_central_user_details['CentralUserCredentials']['user_id']) : '');
                    $this->Session->write('user_group', 1);
                    $firstname = isset($arr_central_user_details['CentralUserCredentials']['first_name']) ? $arr_central_user_details['CentralUserCredentials']['first_name'] : '';
                    $lastname = isset($arr_central_user_details['CentralUserCredentials']['last_name']) ? $arr_central_user_details['CentralUserCredentials']['last_name'] : '';
                    //$this->Session->write('user_name', $arr_central_user_details['CentralUserCredentials']['first_name'] . " " . $arr_central_user_details['CentralUserCredentials']['last_name']);
                    //$this->Session->write('user_name', $firstname . " " . $lastname);
                    $this->Session->write('user_name', $sessionAdmin);
                    $this->Session->write('company_key', $arr_company_db['CentralControl']['control_pkey']);
                    $this->Session->write('company_code', strtoupper($companyCode));
                }
            } else {
                $this->Session->delete('login_user_id');
            }

            $this->redirect(array('controller' => 'Dashboard', 'action' => 'index'));
        } else {
            // unauthorized access
            $this->redirect(array('controller' => 'Site', 'action' => 'login'));
        }
    }

    public function login()
    {
        $messages = array();
        $proceed = true;
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->data = $_GET;
            }
            if (!empty($this->data)) {
                if (!isset($this->data['user_id']) || $this->data['user_id'] == "") {
                    $messages = "Invalid Username or Password";
                    $proceed = false;
                }
                //                if (!isset($this->data['user_group'])) {
                //                    $messages = "Please select user group";
                //                    $proceed = false;
                //                }
                if ($proceed) {
                    $string = $_SERVER["SERVER_NAME"] . $_SERVER['REQUEST_URI'];
                    $arr = explode(".", $string, 2);
                    $company = $arr[0];
                    $this->set('company', $company);
                    //if ($this->data['user_group'] == 1) {

                    $arr_resp = $this->LoginManagement->verifyAdminLogin($this->data['user_id'], $this->data['password']);

                    if (!empty($arr_resp)) {
                        $company_code = isset($arr_resp['CentralUserCredentials']['company_code']) ? $arr_resp['CentralUserCredentials']['company_code'] : '';
                        $user_pkey = isset($arr_resp['CentralUserCredentials']['user_pkey']) ? $arr_resp['CentralUserCredentials']['user_pkey'] : '';
                        $isMultiAdmin = $this->getCompanyType($company_code, $user_pkey);

                        if ($isMultiAdmin == true) {
                            // multiadmin
                            // set a session and redirect to new list page
                            // from list page show all admin lists fetch from db where domain name == session set value
                            // onclick item, audit/set usercredential session with that DB
                            $this->redirect(array('controller' => 'Site', 'action' => 'companies_list'));
                        } else {
                            $this->adminLoginAudit();

                            if (isset($arr_resp['resetlogin']) && $arr_resp['resetlogin']) {
                                $ms = isset($arr_resp['ms']) ? $arr_resp['ms'] : '/';
                                $this->redirect($ms);
                            } else {
                                if (isset($this->data['rememberme'])) {
                                    $username = $this->data['user_id'];
                                    $password = $this->data['password'];
                                    $userGroup = 1;
                                    //$userGroup = $this->data['user_group'];
                                    setcookie('user_id', $username, time() + (86400 * 30), '/');
                                    setcookie('password', $password, time() + (86400 * 30), '/');
                                    setcookie('userGroup', $userGroup, time() + (86400 * 30), '/');
                                }
                                $this->redirect(array('controller' => 'Dashboard', 'action' => 'index'));
                            }
                        }
                    } else {
                        //                            $proceed = false;
                        //                            $messages = "Invalid Username or Password";
                        //                        }
                        //} else if ($this->data['user_group'] == 2) {

                        $arr_resp = $this->LoginManagement->verifyEmployeeLogin($this->data['user_id'], $this->data['password']);
                        if (!empty($arr_resp)) {
                            //$arr_resp = $arr_resp['UserCredentials'];
                            if (isset($arr_resp['locked']) && $arr_resp['locked']) {

                                $proceed = false;
                                $messages = "Account locked for security reasons. Please contact Administrator.";
                            } else if (isset($arr_resp['resetlogin']) && $arr_resp['resetlogin']) {

                                $ms = isset($arr_resp['ms']) ? $arr_resp['ms'] : '/';
                                $this->redirect($ms);
                            } else if (isset($arr_resp['Unauthorized']) && $arr_resp['Unauthorized']) {

                                $proceed = false;
                                $messages = "You are not authorized to use this application";
                            } else {
                                if (isset($this->data['rememberme'])) {
                                    $username = $this->data['user_id'];
                                    $password = $this->data['password'];
                                    $userGroup = 2;
                                    //$userGroup = $this->data['user_group'];
                                    setcookie('user_id', $username, time() + (86400 * 30), '/');
                                    setcookie('password', $password, time() + (86400 * 30), '/');
                                    setcookie('userGroup', $userGroup, time() + (86400 * 30), '/');
                                }
                                //  $ curl -u Ashokan:Welcome135 -X POST https://nextcloud.mypayrollmaster.com/ocs/v1.php/cloud/users -d userid="NewUsrId" -d password="NewUsrPasswd" -d displayName="NewUsrDisplayName" -d email="nxtusr@emailid.com" --header 'OCS-APIRequest: true'


                                $userid = $this->data['user_id'];
                                $password = $this->data['password'];
                                //    $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
                                $this->Useraccess->useDbConfig = $this->Session->read('ds');
                                $user = $this->Useraccess->query("select * from user_credentials where (user_id = '$userid' or email = '$userid')");
                                $user_pkey = isset($user['0']['user_credentials']['emp_fkey']) ? $user['0']['user_credentials']['emp_fkey'] : '';
                                $email = isset($user['0']['user_credentials']['email']) ? $user['0']['user_credentials']['email'] : '';
                                $first_name = isset($user['0']['user_credentials']['first_name']) ? $user['0']['user_credentials']['first_name'] : '';
                                //  $adminid=$arr_useraccess['0']['empmenu']['menu_id'];
                                $loginrest = $this->Useraccess->query("update user_credentials set locked = 0,incorrect_login_attempt = 0 where user_id = '$userid' ");

                                $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$user_pkey' and menu_id = '0' and active = 'Y' ");
                                $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '';

                                $this->employeeLoginAudit();
                                $company_code = $this->Session->read('company_code'); // Edited by Akshay on 25-1-2025
                                if ($access == 'Y') {
                                    //                                           saveauditor();
                                    // $this->redirect(array('controller' => 'Dashboard', 'action' => 'hierarchydashboard'));
                                    // Edited by Akshay on 25-1-2025
                                    if ($company_code == 'GLET' || $company_code == 'ABSG') {
                                        $this->redirect(array('controller' => 'Dashboard', 'action' => 'index'));
                                    } else {
                                        $this->redirect(array('controller' => 'Dashboard', 'action' => 'hierarchydashboard'));
                                    }
                                    // End
                                } else {
                                    //                                           saveauditor();
                                    // $this->redirect(array('controller' => 'Dashboard', 'action' => 'empdashboard'));
                                    // Edited by Akshay on 25-1-2025
                                    if ($company_code == 'GLET' || $company_code == 'ABSG') {
                                        $this->redirect(array('controller' => 'Dashboard', 'action' => 'index'));
                                    } else {
                                        $this->redirect(array('controller' => 'Dashboard', 'action' => 'empdashboard'));
                                    }
                                    // End
                                }
                            }
                        } else {
                            $proceed = false;
                            $messages = "Invalid Username or Password";
                        }
                    }
                    //                    else {
                    //                        $proceed = false;
                    //                        $messages = "Invalid Username or Password";
                    //                    }
                }
            } else {

                $messages = "Invalid Request";
            }

            function saveauditor()
            {
                $auditor_arr = array();
                $auditor_arr['user_cred'] = isset($_GET['user_id']) ? $_GET['user_id'] : "";
                $auditor_arr['user_ip'] = getRealIpAddr();
                $auditor_arr['user_browser'] = getBrowser();
                $auditor_arr['company1'] = isset($_SESSION['company_code']) ? $_SESSION['company_code'] : '';
                $auditor_arr['company2'] = isset($_SESSION['ds']) ? $_SESSION['ds'] : '';
                $auditor_time = "";
                $auditor_arr['auditor_type'] = "LOGIN";
                $auditor_arr['message'] = $messages;
                $auditor_arr['user_cred2'] = isset($_GET['password']) ? $_GET['password'] : "";
                $this->login_auditor->setDataSource('controldb');
                $this->login_auditor->save($auditor_arr);
            }

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

            $info = array();
            $agent = "";

            function getBrowser()
            {
                $browser = array(
                    "Navigator" => "/Navigator(.*)/i",
                    "Firefox" => "/Firefox(.*)/i",
                    "Internet Explorer" => "/MSIE(.*)/i",
                    "Google Chrome" => "/chrome(.*)/i",
                    "MAXTHON" => "/MAXTHON(.*)/i",
                    "Opera" => "/Opera(.*)/i",
                );

                foreach ($browser as $key => $value) {
                    $info = array();
                    $agent = "";
                    if (preg_match($value, $_SERVER['HTTP_USER_AGENT'])) {
                        $info = array_merge($info, array("Browser" => $key));
                        $info = array_merge($info, array(
                            "Version" => "0"
                        ));
                        break;
                    } else {
                        $info = array_merge($info, array("Browser" => "UnKnown"));
                        $info = array_merge($info, array("Version" => "UnKnown"));
                    }
                }
                return $info['Browser'];
            }

            //            saveauditor();


            $this->set("messages", $messages);
        }
        $company = 'giridhar';
        $this->set('company', $company);
    }

    public function logoutAudit()
    {
        $company_code = $this->Session->read('company_code');

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

            $this->ReportAudit->useDbConfig = $this->Session->read('ds');

            $dataForHistory = array();
            $dataForHistory['report_type'] = "Logged out";
            $dataForHistory['mode'] = "Auth";
            $user_id = $this->Session->read('login_user_id');
            $dataForHistory['user_id'] = isset($user_id) ? $user_id : '';
            $user_name = $this->Session->read('user_name');
            $dataForHistory['user_name'] = isset($user_name) ? $user_name : '';
            $dataForHistory['status'] = 1;
            $this->ReportAudit->save($dataForHistory);
        }
    }

    function logout()
    {
        $this->logoutAudit();

        // $sessionAdmin = $this->Session->read('multiadmin');
        // $multiadmin_user_pkey = $this->Session->read('multiadmin_user_pkey');
        // if(isset($sessionAdmin)) {
        //     $this->Session->destroy();
        //     $this->Session->write("multiadmin", $sessionAdmin);
        //     $this->Session->write("multiadmin_user_pkey", $multiadmin_user_pkey);
        //     $this->redirect(array('controller' => 'Site', 'action' => 'companies_list'));
        // } else {
        //     $this->Session->destroy();
        // }

        $this->Session->destroy();
        //        $string = $_SERVER["SERVER_NAME"] . $_SERVER['REQUEST_URI'];
        //        $arr = explode(".", $string, 2);
        //        $company = $arr[0];
        //        $domains = isset($company)?$company:'localhost';
        // remove 'site_auth' cookie
        setcookie('user_id', '', time() - (60 * 60 * 1), '/');
        setcookie('password', '', time() - (60 * 60 * 1), '/');
        setcookie('userGroup', '', time() - (60 * 60 * 1), '/');
        $this->redirect("/");
    }

    public function config()
    {
    }

    public function getduplicate($fieled_name = '', $table = '')
    {
        $this->autoRender = FALSE;
        $sql = "SELECT $fieled_name, COUNT(*) c FROM $table GROUP BY $fieled_name HAVING c > 1;";
        $this->Useraccess->useDbConfig = $this->Session->read('ds');
        $value = $this->Useraccess->query($sql);
        return $value;
    }

    public function checkusername()
    {
        $this->autoRender = FALSE;

        $isAvailable = true;
        if (isset($_REQUEST["username"])) {
            $username = $_REQUEST['username'];

            $count = $this->CentralUserCredentials->find("count", array("conditions" => array("user_id" => $username)));
            //echo $count;
            $countfromreg = $this->Registrations->find("count", array("conditions" => array("desired_username" => $username)));
            if ($count > 0 || $countfromreg > 0) {
                $isAvailable = false;
            }
        }
        echo json_encode(array('valid' => $isAvailable,));
    }

    function passwordreset()
    {
        $this->layout = "nologin";
    }

    function sendtoken()
    {
        $this->layout = "nologin";
        $msg = array();
        $this->UserCredentials->recursive = -1;
        $success = false;
        if (!empty($this->data)) {
            if (empty($this->data['username'])) {
                $msg[] = 'Please Provide Your Correct Username ';
            } else if (empty($this->data['email'])) {
                $msg[] = 'Please Provide Your Email Address that You used to Register with Us';
            } else {
                $email = $this->data['email'];
                $username = $this->data['username'];
                $fu = $this->CentralUserCredentials->find('first', array('conditions' => array('CentralUserCredentials.email' => $email, 'CentralUserCredentials.user_id' => $username)));
                if ($fu) {
                    if ($fu['CentralUserCredentials']['access_allowed']) {
                        $key = Security::hash(String::uuid(), 'sha512', true);
                        $hash = sha1($fu['CentralUserCredentials']['user_id'] . rand(0, 100));
                        $url = Router::url(array('controller' => 'Site', 'action' => 'forgotadmin'), true) . '/' . $username . '/' . $key . '#' . $hash;
                        $ms = $url;
                        $ms = wordwrap($ms, 1000);

                        $fu['CentralUserCredentials']['tokenhash'] = $key;
                        $data['user_pkey'] = $fu['CentralUserCredentials']['user_pkey'];
                        $data['attr1'] = $key;
                        if ($this->CentralUserCredentials->save($data)) {
                            try {
                                if ($this->sendtokenmail($email, $ms)) {
                                    $success = true;
                                }
                            } catch (Exception $ex) {
                                $msg = array();
                                $msg[] = 'Error Sending Reset link. Please Contact Support';
                            }
                            $this->set('ms', $ms);
                            $msg = array();
                            $msg[] = 'Check Your Email To Reset your password';
                            $success = true;
                            //============EndEmail=============//
                        } else {
                            $msg = array();
                            $msg[] = 'Error Generating Reset link';
                        }
                    } else {
                        $msg = array();
                        $msg[] = 'This Account is not Active yet.Check Your mail to activate it';
                    }
                } else {

                    $fu = $this->CentralUserCredentials->query("Select emp_username from emp_device_comp_branch where (emp_username = '$username' or email = '$username' ) and email = '$email'");
                    $usernm = isset($fu['0']['emp_device_comp_branch']['emp_username']) ? $fu['0']['emp_device_comp_branch']['emp_username'] : '';
                    $company_code = substr($usernm, 0, 4);
                    //$company_code = substr($this->data['username'], 0, 4);
                    $this->CentralControl->setDataSource('controldb');
                    $arr_company_db = $this->CentralControl->find('first', array(
                        'fields' => 'CentralControl.*',
                        'conditions' => array('company_code' => $company_code, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
                    ));
                    if (count($fu) == 1) {
                        if (!empty($arr_company_db)) {
                            //company exists and fetch corresponding DB
                            $user_name = isset($arr_company_db['CentralControl']['Admin_name']) ? $arr_company_db['CentralControl']['Admin_name'] : '';
                            $user_db = isset($arr_company_db['CentralControl']['user_db']) ? $arr_company_db['CentralControl']['user_db'] : '';
                            $user_pwd = isset($arr_company_db['CentralControl']['user_pwd']) ? $arr_company_db['CentralControl']['user_pwd'] : '';

                            $link = mysql_connect('127.0.0.1', $user_name, $user_pwd);
                            mysql_select_db($user_db, $link);
                            $res = mysql_query('SELECT * FROM user_credentials WHERE email="' . $email . '" AND (user_id="' . $this->data['username'] . '" or email="' . $this->data['username'] . '") AND user_group!=2');
                            $row = mysql_fetch_assoc($res);
                            //  debug('SELECT * FROM user_credentials WHERE email="'.$email.'" AND user_id="'.$this->data['username'].'" AND user_group!=2');

                            if (!empty($row)) {
                                if ($row['access_allowed']) {

                                    $key = Security::hash(String::uuid(), 'sha512', true);
                                    $hash = sha1($row['user_id'] . rand(0, 100));
                                    $url = Router::url(array('controller' => 'Site', 'action' => 'forgot'), true) . '/' . $company_code . '/' . $key . '#' . $hash;
                                    $ms = $url;
                                    $ms = wordwrap($ms, 1000);

                                    //Insert key on table
                                    $user_pkey = $row['user_pkey'];
                                    $result = mysql_query('UPDATE user_credentials SET attr1="' . $key . '" WHERE user_pkey=' . $user_pkey);

                                    if ($result) {

                                        try {
                                            /* $Email = new CakeEmail();
                                      $Email->config('gmail');
                                      $Email->template('resetpw');
                                      //$Email->from (  'My Payroll Master <info@mypayrollmaster.com>');
                                      $Email->sender('info@mypayrollmaster.com', 'My Payroll Master');
                                      $Email->to($email);
                                      $Email->subject('Reset Your My Payroll Master Password');
                                      $Email->emailFormat('html');
                                      $Email->viewVars(array('ms' => $ms));
                                      $Email->send();
                                      $success = true; */

                                            /* $fromemail = "My Payroll Master <info@mypayrollmaster.com>";

                                      $headers = "From: My Payroll Master <info@mypayrollmaster.com>\r\n";
                                      $headers .= "Reply-To: ". strip_tags($fromemail) . "\r\n";
                                      $headers .= "MIME-Version: 1.0\r\n";
                                      $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

                                      $subject = "Reset Your My Payroll Master Password";
                                      $message = '<p>Click on the link below to Reset Your Password </p><br/>'
                                      .'<a href="'.$ms.'">Click here to Reset Your Password</a><br/>'
                                      .'<pre>or Visit this Link</pre><br/>'
                                      .'<p><a href="'.$ms.'">'.$ms.'</a></p>';

                                      if(mail($email,$subject,$message,$headers)){
                                      $success = true;
                                      } */

                                            if ($this->sendtokenmail($email, $ms)) {
                                                $success = true;
                                            }
                                        } catch (Exception $ex) {
                                            //debug($ms);die();
                                            $msg = array();
                                            $msg[] = 'Error Sending Reset link. Please Contact Support';
                                        }

                                        $this->set('ms', $ms);
                                        $msg = array();
                                        $msg[] = 'Check Your Email To Reset your password';
                                        $success = true;
                                        //============EndEmail=============//
                                    } else {
                                        $msg = array();
                                        $msg[] = 'Error Generating Reset link';
                                    }
                                } else {
                                    $msg = array();
                                    $msg[] = 'This Account is not Active yet.Check Your mail to activate it';
                                }
                            } else {
                                $msg = array();
                                $msg[] = 'Email does Not Exist';
                            }
                        } else {
                            $msg = array();
                            $msg[] = 'User does Not Exist';
                        }
                    } else {
                        $msg = array();
                        $msg[] = 'Email id is invalid';
                    }
                }
            }
        }
        $this->set("msg", $msg);
        if ($success) {
            return $this->redirect(
                array('controller' => 'Site', 'action' => 'success')
            );
        } else {
            $this->data = array();
            $this->render("passwordreset");
        }
    }

    public function reset($company_code = '', $token = null)
    {

        $this->layout = "nologin";
        $msg = "";
        if (isset($this->request['company_code']) && isset($this->request['token'])) {
            $company_code = $this->request['company_code'];
            $token = $this->request['token'];
            $this->set('company', $company_code);
        }

        if (!empty($token) && $company_code != '') {
            $this->CentralControl->setDataSource('controldb');
            $arr_company_db = $this->CentralControl->find('first', array(
                'fields' => 'CentralControl.*',
                'conditions' => array('company_code' => $company_code, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
            ));
            if (!empty($arr_company_db)) {
                //company exists and fetch corresponding DB
                $user_name = isset($arr_company_db['CentralControl']['Admin_name']) ? $arr_company_db['CentralControl']['Admin_name'] : '';
                $user_db = isset($arr_company_db['CentralControl']['user_db']) ? $arr_company_db['CentralControl']['user_db'] : '';
                $user_pwd = isset($arr_company_db['CentralControl']['user_pwd']) ? $arr_company_db['CentralControl']['user_pwd'] : '';

                $link = mysql_connect('127.0.0.1', $user_name, $user_pwd);
                mysql_select_db($user_db, $link);
                $res = mysql_query('SELECT * FROM user_credentials WHERE attr1="' . $token . '"');
                $row = mysql_fetch_assoc($res);

                //echo 'SELECT * FROM user_credentials WHERE attr1="'.$token.'"';
                //echo $user_db."--".$user_pwd;debug($row);die();
                if (!empty($row)) {
                    if (!empty($this->data)) {
                        $data = $this->data;
                        $new_hash = sha1($row['user_id'] . rand(0, 100)); //created token
                        $user_id = $row['user_id'];
                        $attr1 = $new_hash;
                        //added by megha password reset
                        $oldpassword = $this->data['oldpassword'];
                        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $old_pass = Security::hash($oldpassword, null, true);
                        $where = " password ='" . $old_pass . "' and user_id ='" . $user_id;
                        $arr_user_details = mysql_query("SELECT count(*) FROM user_credentials where password ='" . $old_pass . "' and user_id ='" . $user_id . "'");
                        $row = mysql_fetch_assoc($arr_user_details);
                        if ($row['count(*)'] < 1) {
                            $this->set("msg", "Current Password is not matching");
                        } else {
                            //end
                            $password = $this->data['password'];
                            $cpassword = $this->data['password_confirm'];
                            $this->data = array();
                            $uppercase = preg_match('@[A-Z]@', $password);
                            $lowercase = preg_match('@[a-z]@', $password);
                            $number = preg_match('@[0-9]@', $password);
                            if ($password != $cpassword) {
                                $this->set("msg", "New Password and Confirm Password are not matching");
                            } else {
                                $newpassword = Security::hash($password, null, true);
                                //Save new password here
                                $result = mysql_query('UPDATE user_credentials SET locked=0, incorrect_login_attempt=0, attr1="' . $attr1 . '", password="' . $newpassword . '", reset_login_flag="N" WHERE user_id="' . $user_id . '"');
                                $mobresult = mysql_query('UPDATE mob_user_credentials SET password="' . $password . '" WHERE user_id="' . $user_id . '"');
                                $this->redirect('/');
                            }
                        }
                    }
                } else {
                    $this->set("msg", 'Token Corrupted,,Please Retry.the reset link work only for once.');
                }
            } else {
                $this->redirect('/');
            }
        } else {
            $this->redirect('/');
        }
    }
    //added by megha password reset admin 11/02/2020
    public function resetadmin($company_code = '', $token = null)
    {

        $this->layout = "nologin";
        $msg = "";
        if (isset($this->request['company_code']) && isset($this->request['token'])) {
            $company_code = $this->request['company_code'];
            $token = $this->request['token'];
            $this->set('company', $company_code);
        }

        if (!empty($token) && $company_code != '') {
            $this->CentralControl->setDataSource('controldb');
            $arr_company_db = $this->CentralUserCredentials->find('first', array('conditions' => array('CentralUserCredentials.user_id' => $company_code)));

            if (!empty($arr_company_db)) {
                //debug($arr_company_db);
                $control_key = $arr_company_db['CentralUserCredentials']['control_fkey'];
                $arr_data = $this->CentralControl->find('first', array('fields' => 'CentralControl.*', 'conditions' => array('control_pkey' => $control_key)));
                $control = $arr_data['CentralControl']['attr1'];
                $arr_user = $this->CentralUserCredentials->query("SELECT * FROM payroll_signup where payroll_signup_pkey ='" . $control . "'");
                //debug($arr_user);
                $row = $this->CentralUserCredentials->find('first', array('conditions' => array('CentralUserCredentials.attr1' => $token)));
                if (!empty($row)) {
                    if (!empty($this->data)) {
                        $data = $this->data;
                        $new_hash = sha1($row['CentralUserCredentials']['user_id'] . rand(0, 100)); //created token
                        $user_id = $row['CentralUserCredentials']['user_id'];
                        $attr1 = $new_hash;

                        $oldpassword = $this->data['oldpassword'];
                        //$this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        $old_pass = Security::hash($oldpassword, null, true);
                        // $where = " password ='" . $old_pass . "' and user_id ='" . $user_id;
                        $arr_user_details = $this->CentralUserCredentials->query("SELECT count(*) FROM user_credentials where password ='" . $old_pass . "' and user_id ='" . $user_id . "'");
                        //$row = mysql_fetch_assoc($arr_user_details);

                        if ($arr_user_details['0']['0']['count(*)'] < 1) {
                            $this->set("msg", "Current Password is not matching");
                        } else {
                            $password = $this->data['password'];
                            $cpassword = $this->data['password_confirm'];
                            $this->data = array();
                            $uppercase = preg_match('@[A-Z]@', $password);
                            $lowercase = preg_match('@[a-z]@', $password);
                            $number = preg_match('@[0-9]@', $password);
                            if ($password != $cpassword) {
                                $this->set("msg", "New Password and Confirm Password are not matching");
                            } else {
                                $newpassword = Security::hash($password, null, true);
                                //Save new password here
                                $result = $this->CentralUserCredentials->query('UPDATE user_credentials SET locked=0,attr1="' . $attr1 . '", password="' . $newpassword . '", reset_login_flag="N" WHERE user_id="' . $user_id . '"');
                                //$mobresult = mysql_query('UPDATE mob_user_credentials SET password="' . $password . '" WHERE user_id="' . $user_id . '"');
                                $user_name = isset($arr_data['CentralControl']['Admin_name']) ? $arr_data['CentralControl']['Admin_name'] : '';
                                $user_db = isset($arr_data['CentralControl']['user_db']) ? $arr_data['CentralControl']['user_db'] : '';
                                $user_pwd = isset($arr_data['CentralControl']['user_pwd']) ? $arr_data['CentralControl']['user_pwd'] : '';

                                $link = mysql_connect('127.0.0.1', $user_name, $user_pwd);
                                mysql_select_db($user_db, $link);
                                //$res = mysql_query('SELECT * FROM comp_contact_info WHERE attr1="' . $token . '"');
                                //$row = mysql_fetch_assoc($res);

                                $company = $arr_user['0']['payroll_signup']['company_name'];
                                $phone = $arr_user['0']['payroll_signup']['Contact_Phone'];
                                $email = $arr_user['0']['payroll_signup']['admin_email'];
                                $code = $arr_user['0']['payroll_signup']['company_code'];
                                $name = $arr_user['0']['payroll_signup']['attr7'];
                                $branch = $arr_user['0']['payroll_signup']['company_code'] . '01';
                                $set1 = mysql_query('UPDATE comp_contact_info SET business_name="' . $company . '",phone="' . $phone . '", email="' . $email . '" WHERE id=1');
                                $set2 = mysql_query('UPDATE branches SET company_code="' . $code . '",branch_code="' . $branch . '", branch_name="' . $name . '" WHERE id=1');
                                $set3 = mysql_query('UPDATE db_config SET company_code="' . $code . '" WHERE db_config_pkey=2');
                                $set4 = mysql_query('UPDATE fin_year SET company_code="' . $code . '",branch_code="' . $branch . '"');
                                $this->redirect('/');
                            }
                        }
                    }
                } else {
                    $this->set("msg", 'Token Corrupted,,Please Retry.the reset link work only for once.');
                }
            } else {
                $this->redirect('/');
            }
        } else {
            $this->redirect('/');
        }
    }
    public function forgot($company_code = '', $token = null)
    {

        $this->layout = "nologin";
        $msg = "";
        if (isset($this->request['company_code']) && isset($this->request['token'])) {
            $company_code = $this->request['company_code'];
            $token = $this->request['token'];
            $this->set('company', $company_code);
        }

        if (!empty($token) && $company_code != '') {
            $this->CentralControl->setDataSource('controldb');
            $arr_company_db = $this->CentralControl->find('first', array(
                'fields' => 'CentralControl.*',
                'conditions' => array('company_code' => $company_code, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
            ));
            if (!empty($arr_company_db)) {
                //company exists and fetch corresponding DB
                $user_name = isset($arr_company_db['CentralControl']['Admin_name']) ? $arr_company_db['CentralControl']['Admin_name'] : '';
                $user_db = isset($arr_company_db['CentralControl']['user_db']) ? $arr_company_db['CentralControl']['user_db'] : '';
                $user_pwd = isset($arr_company_db['CentralControl']['user_pwd']) ? $arr_company_db['CentralControl']['user_pwd'] : '';

                $link = mysql_connect('127.0.0.1', $user_name, $user_pwd);
                mysql_select_db($user_db, $link);
                $res = mysql_query('SELECT * FROM user_credentials WHERE attr1="' . $token . '"');
                $row = mysql_fetch_assoc($res);

                //echo 'SELECT * FROM user_credentials WHERE attr1="'.$token.'"';
                //echo $user_db."--".$user_pwd;debug($row);die();
                if (!empty($row)) {
                    if (!empty($this->data)) {
                        $data = $this->data;
                        $new_hash = sha1($row['user_id'] . rand(0, 100)); //created token
                        $user_id = $row['user_id'];
                        $attr1 = $new_hash;
                        //			$oldpassword = $this->data['oldpassword'];
                        //			$this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        //                        $old_pass = Security::hash($oldpassword, null, true);
                        //                        $where = " password ='" . $old_pass . "' and user_id ='" . $user_id  ;
                        //                        $arr_user_details = mysql_query("SELECT count(*) FROM user_credentials where password ='" . $old_pass . "' and user_id ='" . $user_id ."'");
                        //			$row = mysql_fetch_assoc($arr_user_details);
                        //		        if ($row['count(*)'] < 1) {
                        //                            $this->set("msg", "Current Password is not matching");
                        //                        }else {
                        $password = $this->data['password'];
                        $cpassword = $this->data['password_confirm'];
                        $this->data = array();
                        $uppercase = preg_match('@[A-Z]@', $password);
                        $lowercase = preg_match('@[a-z]@', $password);
                        $number = preg_match('@[0-9]@', $password);
                        if ($password != $cpassword) {
                            $this->set("msg", "New Password and Confirm Password are not matching");
                        } else {
                            $newpassword = Security::hash($password, null, true);
                            $result = mysql_query('UPDATE user_credentials SET locked=0, incorrect_login_attempt=0, attr1="' . $attr1 . '", password="' . $newpassword . '", reset_login_flag="N" WHERE user_id="' . $user_id . '"');
                            $mobresult = mysql_query('UPDATE mob_user_credentials SET password="' . $password . '" WHERE user_id="' . $user_id . '"');
                            $this->redirect('/');
                        }
                        //}
                    }
                } else {
                    $this->set("msg", 'Token Corrupted,,Please Retry.the reset link work only for once.');
                }
            } else {
                $this->redirect('/');
            }
        } else {
            $this->redirect('/');
        }
    }
    public function forgotadmin($company_code = '', $token = null)
    {

        $this->layout = "nologin";
        $msg = "";
        if (isset($this->request['company_code']) && isset($this->request['token'])) {
            $company_code = $this->request['company_code'];
            $token = $this->request['token'];
            $this->set('company', $company_code);
        }

        if (!empty($token) && $company_code != '') {
            $this->CentralControl->setDataSource('controldb');
            $fu = $this->CentralUserCredentials->find('first', array('conditions' => array('CentralUserCredentials.user_id' => $company_code)));

            if ($fu) {

                $row = $this->CentralUserCredentials->find('first', array('conditions' => array('CentralUserCredentials.attr1' => $token)));
                //debug($row);
                if (!empty($row)) {
                    if (!empty($this->data)) {
                        $data = $this->data;
                        $new_hash = sha1($row['CentralUserCredentials']['user_id'] . rand(0, 100)); //created token
                        $user_id = $row['CentralUserCredentials']['user_id'];
                        $attr1 = $new_hash;
                        //			$oldpassword = $this->data['oldpassword'];
                        //			$this->UserCredentials->useDbConfig = $this->Session->read('ds');
                        //                        $old_pass = Security::hash($oldpassword, null, true);
                        //                        $where = " password ='" . $old_pass . "' and user_id ='" . $user_id  ;
                        //                        $arr_user_details = mysql_query("SELECT count(*) FROM user_credentials where password ='" . $old_pass . "' and user_id ='" . $user_id ."'");
                        //			$row = mysql_fetch_assoc($arr_user_details);
                        //		        if ($row['count(*)'] < 1) {
                        //                            $this->set("msg", "Current Password is not matching");
                        //                        }else {
                        $password = $this->data['password'];
                        $cpassword = $this->data['password_confirm'];
                        $this->data = array();
                        $uppercase = preg_match('@[A-Z]@', $password);
                        $lowercase = preg_match('@[a-z]@', $password);
                        $number = preg_match('@[0-9]@', $password);
                        if ($password != $cpassword) {
                            $this->set("msg", "New Password and Confirm Password are not matching");
                        } else {
                            $newpassword = Security::hash($password, null, true);
                            $result = $this->CentralUserCredentials->query('UPDATE user_credentials SET locked=0,attr1="' . $attr1 . '", password="' . $newpassword . '", reset_login_flag="N" WHERE user_id="' . $user_id . '"');
                            //$mobresult = mysql_query('UPDATE mob_user_credentials SET password="' . $password . '" WHERE user_id="' . $user_id . '"');
                            $this->redirect('/');
                        }
                        //}
                    }
                } else {
                    $this->set("msg", 'Token Corrupted,,Please Retry.the reset link work only for once.');
                }
            } else {
                $this->redirect('/');
            }
        } else {
            $this->redirect('/');
        }
    }
    public function leavesuccess($id = 0)
    {
        //$this->layout = "nologin";
        $this->autoRender = FALSE;
        $resp = array();
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
        try {
            $arr_leave_details1 = $this->LeaveRequests->find("first", array(
                'fields' => 'EmpProff.emp_company_id,EmpProff.LEAVEPOLICY_GROUP_ID,salary_head_items.item,salary_head_items.salary_head_item_pkey,LEAVEENTRYID,ISAutherizedby,applied_date,Autherized_date,Reason,contact_person,message,APPROVEDBY,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
                'joins' => array(
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmpDetails',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmpDetails.emp_pkey = LeaveRequests.emp_fkey')
                    ),
                    array(
                        'table' => 'emp_proff',
                        'alias' => 'EmpProff',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('EmpDetails.emp_pkey = EmpProff.emp_fkey')
                    ),
                    array(
                        'table' => 'salary_head_items',
                        'alias' => 'salary_head_items',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('salary_head_items.salary_head_item_pkey = LeaveRequests.salary_head_item_fkey')
                    )
                ),
                'conditions' => array('LEAVEENTRYID' => $id)
            ));
            $outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? array($arr_leave_details1['LeaveRequests'], $arr_leave_details1['EmpProff'], $arr_leave_details1['salary_head_items']) : array();
            $auth_date = isset($outputParameter1['0']['Autherized _date']) ? $outputParameter1['0']['Autherized_date'] : '';
            $auth_pkey = $outputParameter1['0']['ISAutherizedby'];
            $approved_pkey = $outputParameter1['0']['APPROVEDBY'];
            $applied_emps = $outputParameter1['0']['EMP_fkey'];
            $salary_head_item_pkey = $outputParameter1['2']['salary_head_item_pkey'];
            $applied_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$applied_emps'");
            $auth_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$auth_pkey'");
            $arr_approved_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$approved_pkey'");
            $leave_status = isset($arr_leave_details1['LeaveRequests']['LEAVESTATUS']) ? $arr_leave_details1['LeaveRequests']['LEAVESTATUS'] : '';
            $arr_leavepolicy = $this->LeaveRequests->query("select first_name,email,emp_pkey from emp_details where emp_pkey = (select sanction_by from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $applied_emps . ')' and salary_head_item_fkey = '$salary_head_item_pkey' and status = 1)");
            $sanction = isset($arr_leavepolicy[0]['emp_details']['first_name']) ? $arr_leavepolicy[0]['emp_details']['first_name'] : '0';
        } catch (Exception $ex) {
            $resp["success"] = FALSE;
            $resp["message"] = "Data Fetching Failed Please try Again";
            return json_encode($resp);
        }

        $arr_approvedempmail = '';
        $arr_appliedemp_mail = '';
        $arr_authempmail = '';
        $issendappliedmail = false;
        if ($applied_emp['0']['emp_details']['email']) {
            $issendappliedmail = true;
            $arr_appliedemp_mail = $applied_emp['0']['emp_details']['email'];
        }
        $issendauthmail = false;
        if ($auth_emp['0']['emp_details']['email']) {
            $issendauthmail = true;
            $arr_authempmail = $auth_emp['0']['emp_details']['email'];
        }
        $issendapprovedmail = false;
        if ($arr_approved_emp['0']['emp_details']['email']) {
            $issendapprovedmail = true;
            $arr_approvedempmail = $arr_approved_emp['0']['emp_details']['email'];
        }

        $curdate = date('Y-m-d');
        if ($actionType == 'Approved') {
            $outputParameter1['action'] = "Approved";
            $outputParameter1['LEAVESTATUS'] = "Approved";
            if ($issendappliedmail) {
                $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $sanction, 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                $this->sendauthorizationmail($outputParameter1, $auth_email);
            }
        } else {
            $outputParameter1['action'] = "Rejected";
            $outputParameter1['LEAVESTATUS'] = "Rejected";
            if ($issendappliedmail) {
                $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $sanction, 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                $this->sendauthorizationmail($outputParameter1, $auth_email);
            }
        }
    }
    public function approval($company_code = '', $token = null)
    {
        $this->layout = "nologin";
        $msg = "";
        if (isset($this->request['company_code']) && isset($this->request['token'])) {
            $company_code = $this->request['company_code'];
            $token = $this->request['token'];
            $this->set('company', $company_code);
        }

        if (!empty($token) && $company_code != '') {
            $this->CentralControl->setDataSource('controldb');
            $arr_company_db = $this->CentralControl->find('first', array(
                'fields' => 'CentralControl.*',
                'conditions' => array('company_code' => $company_code, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
            ));
            if (!empty($arr_company_db)) {
                //company exists and fetch corresponding DB
                $user_name = isset($arr_company_db['CentralControl']['Admin_name']) ? $arr_company_db['CentralControl']['Admin_name'] : '';
                $user_db = isset($arr_company_db['CentralControl']['user_db']) ? $arr_company_db['CentralControl']['user_db'] : '';
                $user_pwd = isset($arr_company_db['CentralControl']['user_pwd']) ? $arr_company_db['CentralControl']['user_pwd'] : '';

                $link = mysql_connect('127.0.0.1', $user_name, $user_pwd);
                mysql_select_db($user_db, $link);
                $res = mysql_query('SELECT * FROM emp_leave_approval WHERE email_url="' . $token . '"  and status = 0');
                $row = mysql_fetch_assoc($res);

                if (!empty($row)) {
                    if (!empty($this->data)) {
                        $data = $this->data;
                        $id = $row['LEAVEENTRYID'];
                        $remarks = $data['remarks'];
                        $status = $data['status'];
                        $result1 = mysql_query('UPDATE emp_leave_approval SET sanction_remarks="' . $remarks . '", status= 1,leave_status="' . $status . '" WHERE LEAVEENTRYID="' . $id . '"');
                        $result2 = mysql_query('UPDATE emp_leave_transactions SET Leavestatus="' . $status . '" WHERE LEAVEENTRYID="' . $id . '"');
                        $result3 = mysql_query('UPDATE leaveentries SET LEAVESTATUS="' . $status . '" WHERE LEAVEENTRYID="' . $id . '"');
                        $resp = array();
                        try {
                            $arr_leave_details1  = mysql_query('Select EmpProff.emp_company_id,EmpProff.LEAVEPOLICY_GROUP_ID,salary_head_items.item,'
                                . 'salary_head_items.salary_head_item_pkey,LEAVEENTRYID,ISAutherizedby,applied_date,Autherized_date,Reason,contact_person,message,'
                                . 'APPROVEDBY,LeaveRequests.EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS from leaveentries LeaveRequests '
                                . 'left join emp_details as EmpDetails on (EmpDetails.emp_pkey = LeaveRequests.emp_fkey) '
                                . 'left join emp_proff as EmpProff on (EmpDetails.emp_pkey = EmpProff.emp_fkey) '
                                . 'left join salary_head_items on (salary_head_items.salary_head_item_pkey = LeaveRequests.salary_head_item_fkey) '
                                . 'where LEAVEENTRYID = "' . $id . '" ');
                            $outputParameter1 = mysql_fetch_assoc($arr_leave_details1);

                            //$outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? array($arr_leave_details1['LeaveRequests'],$arr_leave_details1['EmpProff'],$arr_leave_details1['salary_head_items']) : array();
                            $auth_date = isset($outputParameter1['Autherized _date']) ? $outputParameter1['Autherized_date'] : '';
                            $auth_pkey = $outputParameter1['ISAutherizedby'];
                            $approved_pkey = $outputParameter1['APPROVEDBY'];
                            $applied_emps = $outputParameter1['EMP_fkey'];
                            $applied_emp = mysql_query('select first_name,email from emp_details where emp_pkey = "' . $applied_emps . '"');
                            $auth_emp = mysql_query('select first_name,email from emp_details where emp_pkey = "' . $auth_pkey . '"');
                            $arr_approved_emp = mysql_query('select first_name,email from emp_details where emp_pkey = "' . $approved_pkey . '"');
                            $applied_emp = mysql_fetch_assoc($applied_emp);
                            $auth_emp = mysql_fetch_assoc($auth_emp);
                            $leave_type = $outputParameter1['salary_head_item_pkey'];
                            $arr_approved_emp = mysql_fetch_assoc($arr_approved_emp);
                            $arr_leavepolicy_emp = mysql_query('select first_name,email,emp_pkey from emp_details where emp_pkey = (select sanction_by from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey= "' . $applied_emps . '") and salary_head_item_fkey = "' . $leave_type . '" and status = 1)');
                            $outputParameter2 = mysql_fetch_assoc($arr_leavepolicy_emp);
                            $sanction = $outputParameter2['first_name'];
                        } catch (Exception $ex) {
                            $resp["success"] = FALSE;
                            $resp["message"] = "Data Fetching Failed Please try Again";
                        }

                        $arr_approvedempmail = '';
                        $arr_appliedemp_mail = '';
                        $arr_authempmail = '';
                        $issendappliedmail = false;
                        if ($applied_emp['email']) {
                            $issendappliedmail = true;
                            $arr_appliedemp_mail = $applied_emp['email'];
                        }
                        $issendauthmail = false;
                        if ($auth_emp['email']) {
                            $issendauthmail = true;
                            $arr_authempmail = $auth_emp['email'];
                        }
                        $issendapprovedmail = false;
                        if ($arr_approved_emp['email']) {
                            $issendapprovedmail = true;
                            $arr_approvedempmail = $arr_approved_emp['email'];
                        }
                        $curdate = date('Y-m-d');
                        if ($status == 'Approved') {
                            $outputParameter1['action'] = "Approved";
                            $outputParameter1['LEAVESTATUS'] = "Approved";
                            if ($issendappliedmail) {
                                $auth_email = array('Email' => $applied_emp['email'], 'Name' => $sanction, 'Appliedby' => $applied_emp['first_name']);
                                $this->sendauthorizationmail($outputParameter1, $auth_email);
                            }
                        } else {
                            $outputParameter1['action'] = "Rejected";
                            $outputParameter1['LEAVESTATUS'] = "Rejected";
                            if ($issendappliedmail) {
                                $auth_email = array('Email' => $applied_emp['email'], 'Name' => $sanction, 'Appliedby' => $applied_emp['first_name']);
                                $this->sendauthorizationmail($outputParameter1, $auth_email);
                            }
                        }
                        //                            $mobresult = mysql_query('UPDATE mob_user_credentials SET password="' . $password . '" WHERE user_id="' . $user_id . '"');
                        $this->redirect('/');
                        //                        }
                        //$this->leavesuccess(); 
                    } else {
                        //$this->leavesuccess(); 
                    }
                } else {
                    $this->set("msg", 'Token Corrupted,Please retry. The approval link works only for once.');
                }
            } else {
                $this->redirect('/');
            }
        } else {
            $this->redirect('/');
        }
    }
    public function register()
    {
        //debug($_POST);
        $success = false;

        $user = array();
        $user['first_name'] = $_POST['firstname'];
        $user['last_name'] = $_POST['lastname'];
        $user['email'] = $_POST['email'];
        $user['mobile_no'] = $_POST['mobile'];
        $user['company_name'] = $_POST['companyname'];
        $user['desired_username'] = $_POST['username'];

        $user['currency'] = $_POST['currency'];

        $user['country'] = $_POST['country'];
        $user['signup_date'] = date("Y-m-d H:i:s");
        if ($this->Registrations->save($user)) {
            $success = true;
            $this->setup($user);
        }
        //$user[''] = $_POST['firstname'];
        //$user[''] = $_POST['firstname'];

        echo json_encode(array('saved' => $success,));
        $this->autoRender = FALSE;
    }

    public function success()
    {
        $this->layout = "nologin";
    }


    public function setup($user)
    {
        $this->autoRender = false;
        /*
          $file = ROOT . '/setup/setup.sql';

          if (!file_exists($file)) {
          exit('Could not load sql file: ' . $file);
          }
          $dbservername = "localhost";
          $dbusername = "mypayrollmasterdbu";
          $dbpassword = "PayrollMaster))&";
          $conn = mysql_connect($dbservername, $dbusername, $dbpassword);
          if (!$conn) {
          die('Could not connect: ' . mysql_error());
          }

          $dbname = "client_db_" . strtotime("now");

          if (mysql_query("CREATE DATABASE " . $dbname, $conn) === TRUE) {

          $db_selected = mysql_select_db($dbname, $conn);
          if (!$db_selected) {
          die('Can\'t use foo : ' . mysql_error());
          }
          }
          $lines = file($file);

          if ($lines) {
          $sql = '';

          foreach($lines as $line) {
          if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
          $sql .= $line;


          if (preg_match('/;\s*$/', $line)) {
          mysql_query($sql) ;//or print_r('Error performing query \'<strong>' . $sql . '\': ' . mysql_error() . '<br /><br /> ------');

          $sql = '';
          }
          }
          }
          }

          $userexist = $this -> CentralUserCredentials -> find("count", array("conditions" => array("user_id" => $user['desired_username'])));

          if($userexist > 0)
          {
          $username = $this->generateRandomString();
          }else{
          $username = $user['desired_username'];
          }

          $password = $this->random_password();
          $cname = str_replace(' ', '_', $user['company_name']);
          $cnameLength = strlen($cname);
          if($cnameLength >3){
          $cname =  substr(str_replace(' ', '_', $user['company_name']), 0,3);
          }else{

          $cname =  substr(str_replace(' ', '_', $user['company_name']), 0,$cnameLength);
          }
          $company = array();

          $company["company_code"] = $cname  ;
          $company["company_name"] = $user['company_name'];
          $company["Address"] = "";
          $company["Admin_name"] = $dbusername;
          $company["user_db"] = $dbname;
          $company["user_pwd"] = $dbpassword;
          $company["created_date"] = date("Y-m-d H:i:s");
          $company["active"] = "active";

          $this -> CentralControl->save($company);
          $cid =  $this->CentralControl->id;

          $userin = array();
          $userin["user_pkey"] = 0 ;
          $userin["phone"] = $user["mobile_no"] ;
          $userin["email"] = $user["email"] ;
          $userin["first_name"] = $user["first_name"] ;
          $userin["last_name"]  = $user["last_name"] ;
          $userin["access_allowed"] = "Y";
          $userin["password"] = $password;
          $userin["user_id"] = $username;
          $userin["control_fkey"] = $cid;
          $userin["company_code"] = $cname;
          $userin["start_date"] = date("Y-m-d H:i:s");

          $this -> CentralUserCredentials -> save($userin);
          $insert_client_user = "INSERT INTO `user_credentials`( `company_code`, `user_id`, `password`, `access_allowed`, `start_date`, `first_name`, `last_name`,  `email`, `phone`, `reset_login_flag`) VALUES ('".$cname."',".$username.",".$password.",'Y',".date('Y-m-d H:i:s').",".$user['first_name'].",".$user['last_name'].",".$user['email'].",".$user['mobile_no'].")";
          mysql_query($insert_client_user)or print('Error performing query \'<strong>' . $line . '\': ' . mysql_error() . '<br /><br />');

         */

        //$Email = new CakeEmail();

        $this->Email->smtpOptions = array(
            'port' => '465',
            'timeout' => '3000',
            'host' => 'smtp.gmail.com',
            'username' => 'developer.binesh@gmail.com',
            'password' => 'developerbinesh',
            'tls' => true
        );
        $this->Email->delivery = "gmail";
        $this->Email->to = "bineshbabu.t@gmail.com";
        $this->Email->from = "info@forsight.com";
        $this->Email->replyTo = "bineshbabu.t@gmail.com";
        $this->Email->subject = 'This is a subject';
        $this->Email->template = 'registration';
        $this->Email->sendAs = 'both';
        $this->Email->send("Testing Email", "registration");

        /*
          $Email = new CakeEmail('gmail');
          //	$Email->from(array('info@forsight.com' => 'Forsight'));
          $Email->to($userin["email"]);
          $Email->subject('Forsight Account Registration');
          $Email->template('default', 'default');
          $Email->emailFormat('both');
          $Email->send(); */

        //die ;
    }

    function random_password($length = 6)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    function generateRandomString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function sendtokenmail($email = '', $ms = '')
    {

        $this->autoRender = FALSE;
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            //$mail = new PHPMailer;
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
           // $mail->Host = 'smtp.zoho.in'; //'smtpauth.net4india.com';//'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            //$mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            //$mail->Password = 'Mypayroll125#'; 		// SMTP password
           // $mail->Username = 'noreply@mypayrollmaster.online';                 // SMTP username
            //$mail->Password = '@Password90#';
                                              // TCP port to connect to
            $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com';
            $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';
            $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25; 

            $mail->setFrom('mypayrollmaster@office24.online', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            //$mail->addBCC($email); 
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->addReplyTo('noreply@mypayrollmaster.online', 'My Payroll Master');
            $subject = "Reset Your My Payroll Master Password";
            $message = '<p>Click on the link below to Reset Your Password </p><br/>'
                . '<a href="' . $ms . '">Click here to Reset Your Password</a><br/>'
                . '<pre>or Visit this Link</pre><br/>'
                . '<p><a href="' . $ms . '">' . $ms . '</a></p>';

            $mail->Subject = $subject;
            $mail->AltBody    = '<!DOCTYPE html>';
            $mail->MsgHTML('<html>' . $message . '</html>');
            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }
    //sending leave approved mail to applied person after final approval
    function sendauthorizationmail($output, $auth)
    {
        //debug($output);die;
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $auth['Name'];
        $applied_emp = $auth['Appliedby'];
        $leavedays = $output['leave_days'];
        $applied_emp_id = $output['emp_company_id'];
        $leavetype = $output['item'];
        $leavefrom = $output['FROMDATE'];
        $duties = $output['contact_person'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $action = $output['action'];
        $ccmail = isset($output['cc_email']) ? $output['cc_email'] : '';
        $reason = isset($output['Reason']) ? $output['Reason'] : "";
        //$cur_user_name = $this->Session->read("user_name");
        if ($action === 'Approved') {
            $msgs = 'Your Leave Request has been approved successfully.';
        } else {
            $msgs = 'Sorry. Your Leave Request has been rejected.';
        }
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = false;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.in'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            //            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            //            $mail->Password = 'welcome123';                           // SMTP password 
            //$mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            //$mail->Password = 'mypayrollmaster123'; 
            $mail->Username = 'noreply@mypayrollmaster.online';                 // SMTP username
            $mail->Password = '@Password90#';
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('noreply@mypayrollmaster.online', $auth_name);
            $mail->addAddress($email);     // Add a recipient
            $mail->addBCC($email);
            if ($ccmail) {
                $mail->addBCC($ccmail);
            }
            $mail->addReplyTo('noreply@mypayrollmaster.online', $auth_name);
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddEmbeddedImage('https://login.mypayrollmaster.online/newlogin/img/logo.png', 'MPM');
            $mail->AltBody    = '<!DOCTYPE html>';
            $mail->Subject = "Action Needed : MPM : Leave Request Details";
            $mail->MsgHTML('<html><div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
	<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
    <tbody>
        <tr>
        	<td>
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:100%!important">
                <tbody>
                	<tr>
                    	<td>
                			<table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                            <tbody>
                            	<tr>
                                    <td colspan="3" height="80" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="padding:0;margin:0;font-size:0;line-height:0">
                                        <table width="690" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td width="30"></td>
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="https://login.mypayrollmaster.online/" target="_blank"><img style="height: 40px;" src="https://login.mypayrollmaster.online/newlogin/img/logo.png" alt="MypayrollMaster" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                               
                            
                            <tr bgcolor="#ffffff">
                                <td width="30" bgcolor="#eeeeee"></td>
                                <td>
                                 <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td>
                                            	<h2 style="color:#404040;font-size:22px;font-weight:bold;line-height:26px;padding:0;margin:0">&nbsp;</h2>
                                        		<div style="color:#404040;font-size:15px;line-height:22px;font-weight:lighter;padding:0;margin:0">Dear ' . $applied_emp . '     
                                                   <br><br>' . $msgs . ' </div>
                                          	</td>
                                      	</tr>
                                        </tbody>
                                        </table>
                                    <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td width="200" align="right" valign="top"><h3 style="color:#404040;font-size:16px;line-height:22px;font-weight:bold;padding:0;margin:0">Employee Name</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">'
                . $applied_emp .
                '</div>
                                                <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                          	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>
                                            <tr>
                                        	<td width="200" align="right" valign="top"><h3 style="color:#404040;font-size:16px;line-height:22px;font-weight:bold;padding:0;margin:0">Employee ID</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">'
                . $applied_emp_id .
                '</div>
                                                <div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                          	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="200" align="right" valign="top"><h3 style="color:#404040;font-size:16px;line-height:22px;font-weight:bold;padding:0;margin:0">Leave Type</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavetype . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>
                                        <tr>
                                        	<td width="200" align="right" valign="top"><h3 style="color:#404040;font-size:16px;line-height:22px;font-weight:bold;padding:0;margin:0">Leave Days</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavedays . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>
                                        <tr>
                                        <td width="200" align="right" valign="top"><h3 style="color:#404040;font-size:16px;line-height:22px;font-weight:bold;padding:0;margin:0">Leave Dates</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavefrom . ' - ' . $leaveend . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>
                                        <tr>
                                        <td width="200" align="right" valign="top"><h3 style="color:#404040;font-size:16px;line-height:22px;font-weight:bold;padding:0;margin:0">Applied Date</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $applied . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        <td width="200" align="right" valign="top"><h3 style="color:#404040;font-size:16px;line-height:22px;font-weight:bold;padding:0;margin:0">Reason</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $reason . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        <td width="200" align="right" valign="top"><h3 style="color:#404040;font-size:16px;line-height:22px;font-weight:bold;padding:0;margin:0">Duties handed over to</h3></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $duties . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                  	</tbody>
                                    </table>
                                    <table width="570" align="center" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    	<tr>
                                        	<td>
                                            	<h2 style="color:#404040;font-size:22px;font-weight:bold;line-height:26px;padding:0;margin:0">&nbsp;</h2>
                                        		<div style="color:#404040;font-size:15px;line-height:22px;font-weight:lighter;padding:0;margin:0">Please click below to take action on this.</div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.online/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#2e7695;padding:14px 40px;display:block" target="_blank">Take action.</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr>
                                      <tr>
                                        	<td>This is an auto generated mail from mypayrollmaster.online, your online HRMS. 
                                                My Payroll Master is the product of GREAT LEAP Technologies Pvt Ltd. 
                                                You may find further details about My Payroll Master in <a href="http://mypayrollmaster.online/" target="_blank">www.mypayrollmaster.online</a>
                                          	</td>
                                      	</tr>
                                        <tr><td><br>Thanks & Regards <br><br>My Payroll Master Team&nbsp;</td>
                                      </tr>
                                       <tr><td>&nbsp;</td>
                                      </tr></tbody></table></td>
                                <td width="30" bgcolor="#eeeeee"></td>
                            </tr>
                          	</tbody>
                            </table>
                  			<table align="center" width="750px" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee" style="width:750px!important">
                            <tbody>
                            	<tr>
                                	<td>
                                        <table width="630" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
                                        <tbody>
                                        	<tr><td colspan="2" height="30"></td></tr>
                                            <tr>
                                            	<td width="360" valign="top">
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 Mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                 </td>
                                            </tr>
                                            <tr><td colspan="2" height="5"></td></tr>
                                           
                                      	</tbody>
                                        </table>
                                   	</td>
                  				</tr>
                          	</tbody>
                            </table>
                  		</td>
                	</tr>
              	</tbody>
                </table>
            </td>
		</tr>
 	</tbody>
    </table>
</div></html>');

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }
    public function sendtestemail()
    {

        $this->autoRender = FALSE;

        /* try {
          $test = imagecreatefrompng("http://beta.mypayrollmaster.com/app/webroot/files/companylogos/FORG/6ebb5955902c32cae558445e947576a042d03054.png");
          } catch (Exception $e) {
          echo 'Caught exception: ',  $e->getMessage(), "\n";
          }
          die('died'); */
        try {

            /* //ini_set('SMTP','184.107.133.75');
              //ini_set('smtp_port',25);
              $subject = "My subject";
              $txt = "Hello world!";
              $headers = "From: info@mypayrollmaster.com";

              if(mail($to,$subject,$txt,$headers)){
              echo "Sent successfully";
              }else{
              echo "Sent failed";
              }

              $Email = new CakeEmail();
              $Email->config('gmail');
              $Email->sender('info@mypayrollmaster.com', 'My Payroll Master');
              $Email->subject('Test mail');
              $Email->emailFormat('html');
              $Email->send('Test email desc'); */

            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            //If this account fails, use the email info@myprojectsmaster.com
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            //$mail->Username = 'info@myprojectsmaster.com';                 // SMTP username
            //$mail->Password = 'Welcome123';                           // SMTP password

            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->addAddress('sanjundev@gmail.com', 'Sanjun Dev');     // Add a recipient
            $mail->addAddress('bashokan77@gmail.com', 'Ashokan');     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = 'Here is the subject';
            $mail->Body = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ';
                debug($mail->ErrorInfo);
            } else {
                echo 'Message has been sent';
            }
        } catch (Exception $ex) {
            debug($ex);
        }
    }

    public function testcon()
    {

        $this->autoRender = FALSE;
        $servername = "184.107.133.75";
        $username = "localhost";
        $password = "Localhot&*()";
        $dbname = "central_control";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } else {
            die("Connection success.");
        }

        /* $sql = "SELECT id, firstname, lastname FROM MyGuests";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
          // output data of each row
          while($row = $result->fetch_assoc()) {
          echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
          }
          } else {
          echo "0 results";
          } */
        $conn->close();
    }
}
