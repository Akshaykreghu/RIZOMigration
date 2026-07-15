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
class infoController extends Controller {

    /**
     * Controller name
     *
     * @var string
     */
    // public $layout = "default";
    public $name = 'info';
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

    public function index() {
       
        $this->autorender = false;
        $this->layout = null;
        $this->render(false);

echo phpinfo();

    } 

}