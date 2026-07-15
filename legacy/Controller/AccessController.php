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
App::uses('AccessController', 'Controller');
ini_set("display_errors", 1);

App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class AccessController extends Controller {

    /**
     * Controller name
     *
     * @var string
     */
    // public $layout = "default";
    public $name = 'Leaveapi';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('AppModel', 'LeaveRequests','EmpLeaveApproval', 'EmployeeConfig','SalaryHeadItems', 'EmployeeDetails', 'EmployeeLeaveTransaction', 'LeavePolicy', 'EmployeeInfo');
    public $components = array('LoginManagement', 'Session', 'Email');

    /*
     * Leave List Landing Page
     */

    public function checkLogin() {
       
        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

        $controldb_config = ConnectionManager::getDataSource('controldb')->config;
        //$link = mysql_connect('localhost','mpm_cntrl_usr','Localhost&*()');
        $link = mysql_connect('127.0.0.1', 'mpm_cntrl_usr', 'MyPyR01@Cntr1#LB');
        mysql_select_db($controldb_config['database'], $link);
        $res = mysql_query("SELECT `single_signon_fn`('" . @$_GET['fullstring'] ."', '" . @$_GET['company_code'] ."', '" . @$_GET['emp_company_id'] ."', '" . @$_GET['emp_email'] ."', '" . @$_GET['code'] ."') as authorize;");
        $row = mysql_fetch_assoc($res);

        if($row['authorize'] != '0') {
            $database = explode("||", $row['authorize'])[0];
            $user_id = explode("||", $row['authorize'])[1];

            $linkDatabase = mysql_connect('127.0.0.1',$database,'Localhost&*()');
            mysql_select_db($database, $linkDatabase);
            $res2 = mysql_query("SELECT user_id,password FROM mob_user_credentials WHERE user_id = '". $user_id ."' ") or die("Invalid query: " . mysql_error());
            $row2 = mysql_fetch_assoc($res2);
            header("Location: https://v1.mypayrollmaster.online/Site/login?user_id=". $row2['user_id'] ."&password=". $row2['password']);
            exit;
        } else {
            die("Invalid credentials");
        }


    } 

}