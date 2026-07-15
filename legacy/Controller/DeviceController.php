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
 * 
 * Device form by Arul P Das on 29_7_21
 */
class DeviceController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Device';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'Device','branches');
    public $components = array('DatatablesManagement');

    /*
	 * Dashboard landing view
	 */
    public function index()
    {
        //$this->layout = FALSE;
        $company_code = $this->Session->read('company_code');
         $this->branches->useDbConfig = $this->Session->read('ds');
       $branches = $this->Device->query("SELECT * FROM mypayrol_control_db.company_branches WHERE `status` = '1' AND company_code = '" . $company_code . "'");
       

     //debug($branches);

        // Edited by Akshay on 7-2-2025
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $current_emp_pkey = $this->Session->read('emp_fkey');
        $user_group = $this->Session->read('user_group');
        $company_code = $this->Session->read('company_code');
        $is_ho = 1;
        if ($user_group == '2' && ($company_code == 'GLET' || $company_code == 'ABSG')) {

            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $arr_is_ho = $this->UserCredentials->query(
                "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
                ['emp_pkey' => $current_emp_pkey]
            );
            $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
            if ($is_ho != 1) {
                $branches = array_filter($branches, function ($branch) use ($is_ho) {
                     
                    return $branch['company_branches']['branch_code'] === $is_ho;
                });
            }
        }
        $this->set('is_ho', $is_ho);
        // End

        $this->set('branches', $branches);
        $devices = $this->Device->query("SELECT `DeviceId`, `DeviceFName`, `SerialNumber` FROM `mypayrol_control_db`.`devices` WHERE `company_code` = '" . $company_code . "'");
        $this->set('devices', $devices);
        // back button
         $plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
        $plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
        $this->set('plan', $plan);
        $this->CentralUserCredentials->setDataSource('controldb');

        $company_code = $this->Session->read('company_code');

        $data = $this->CentralUserCredentials->find('first', array(
            'conditions' => array(
                'CentralUserCredentials.company_code' => $company_code
            ),
            'fields' => array('CentralUserCredentials.plan_id'),
            'recursive' => -1
        ));

        $planId = !empty($data)
            ? (int)$data['CentralUserCredentials']['plan_id']
            : null;
              $user_group = $this->Session->read('user_group');
      
        $this->set('planId', $planId);
        $this->set('user_group', $user_group);
    }

    public function listDev()
    {
        $this->autoRender = false;
        $company_code = $this->Session->read('company_code');
        $condition = '';
        $emp = (isset($_REQUEST['emp'])) ? $_REQUEST['emp'] : ''; // This is the search parameter
        $branch = (isset($_REQUEST['branch'])) ? $_REQUEST['branch'] : ''; // This is the filter parameter
        $serial_no = (isset($_REQUEST['serial_no'])) ? $_REQUEST['serial_no'] : ''; // This is the filter parameter
        if ($emp) {
            $condition .= " AND (DeviceFName LIKE '%" . $emp . "%' OR branch_name LIKE '%" . $emp . "%' OR SerialNumber LIKE '%" . $emp . "%' OR DeviceLocation LIKE '%" . $emp . "%') ";
        }
        if ($branch) {
            $condition .= " AND devices.branch_code = '".$branch."' ";
        }
        if ($serial_no) {
            $condition .= " AND devices.SerialNumber = '".$serial_no."' ";
        }
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        // echo $company_code;
        $json_output = array();
        $result = array();
        $i = 0;

        $emp_devices = $this->Device->query("SELECT `DeviceId`, `DeviceFName`, `DeviceSName`, devices.branch_code, `branch_name`, `SerialNumber`, `DeviceLocation` FROM mypayrol_control_db.devices LEFT JOIN mypayrol_control_db.company_branches ON company_branches.branch_code = devices.branch_code WHERE devices.company_code = '" . $company_code . "' $condition ORDER BY DeviceId DESC LIMIT $limit OFFSET $ofst");
        $emp_devices_count = $this->Device->query("SELECT count(DeviceId) as total FROM mypayrol_control_db.devices LEFT JOIN mypayrol_control_db.company_branches ON company_branches.branch_code = devices.branch_code WHERE devices.company_code = '" . $company_code . "' $condition ");

        // echo "<pre>";print_r($emp_devices);echo "</pre>";
        foreach ($emp_devices as $data) {
            $result[$i]['DeviceId']         = $data['devices']['DeviceId'];
            $result[$i]['DeviceFName']      = $data['devices']['DeviceFName'];
            $result[$i]['DeviceSName']      = $data['devices']['DeviceSName'];
            $result[$i]['branch_code']      = $data['devices']['branch_code'];
            $result[$i]['SerialNumber']     = $data['devices']['SerialNumber'];
            $result[$i]['DeviceLocation']   = $data['devices']['DeviceLocation'];

            $result[$i]['branch_name']      = $data['company_branches']['branch_name'];

            $i++;
        }
        $json_output['rows'] = $result;
        $json_output['total'] = $emp_devices_count[0][0]['total'];
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        echo json_encode($json_output);
    }

    public function addEditDev($dev_id = '')
    {
        $this->autoRender = true;
        $company_code = $this->Session->read('company_code');
        if ($dev_id) {
            $dev_data = $this->Device->query("SELECT `DeviceId`, `DeviceFName`, `DeviceSName`, devices.branch_code, `branch_name`, `SerialNumber`, `DeviceLocation` FROM mypayrol_control_db.devices LEFT JOIN mypayrol_control_db.company_branches ON company_branches.branch_code = devices.branch_code WHERE `DeviceId` = $dev_id AND devices.company_code = '" .$company_code. "'");

            $this->set('dev_data', array_merge($dev_data[0]['devices'],$dev_data[0]['company_branches']));
            $this->set('dev_id', $dev_id);
        }

        $branches = $this->Device->query("SELECT * FROM mypayrol_control_db.company_branches WHERE `status` = '1' AND company_code = '".$company_code."'");
        $this->set('branches',$branches);
        // echo "<pre>";
        // print_r($branches);
        // echo "</pre>";
    }

    public function saveDev()
    {
        $this->autoRender = false;
        // echo "<pre>";
        // print_r($_REQUEST);
        // echo "</pre>";
        $DeviceId = isset($_REQUEST['DeviceId']) ? $_REQUEST['DeviceId'] : '';
        $SerialNumber = isset($_REQUEST['SerialNumber']) ? $_REQUEST['SerialNumber'] : '';
        $DeviceLocation = isset($_REQUEST['DeviceLocation']) ? $_REQUEST['DeviceLocation'] : '';
        $branch_code = isset($_REQUEST['branch_code']) ? $_REQUEST['branch_code'] : '';
        $DeviceFName = isset($_REQUEST['DeviceFName']) ? $_REQUEST['DeviceFName'] : '';

        $new_DeviceId = 0;
        
        $login_user = $this->Session->read('login_user_id');
        $company_code = $this->Session->read('company_code');
        $date = date("Y-m-d H:i:s");

        if(!$DeviceId){
            $last_dev_id = $this->Device->query("SELECT MAX(`DeviceId`) as DeviceId FROM mypayrol_control_db.devices WHERE devices.company_code = '" .$company_code. "'");
            $new_DeviceId = intval($last_dev_id[0][0]['DeviceId']) + 1;
        }

        if ($SerialNumber && $DeviceLocation) {
            if($DeviceId){
                $SqlQuery = "UPDATE ";
            }else{
                $SqlQuery = "INSERT ";
            }
            $SqlQuery .= " mypayrol_control_db.devices SET SerialNumber = '" .$SerialNumber. "' , DeviceLocation = '" .$DeviceLocation. "'";
            if($branch_code){
                $SqlQuery .= " , branch_code = '" .$branch_code. "'";
            }
            if($DeviceFName){
                $SqlQuery .= " , DeviceFName = '" .$DeviceFName. "'";
            }
            if($new_DeviceId && !$DeviceId){
                $SqlQuery .= " , DeviceId = " .$new_DeviceId. "";
            }
            if(!$DeviceId){
                $SqlQuery .= " , company_code = '" .$company_code. "'";
            }else{
                $SqlQuery .= " WHERE company_code = '" .$company_code. "' AND DeviceId = ".$DeviceId;
            }
            // echo $SqlQuery;
            $this->Device->query($SqlQuery);

            echo true;
        } else {
            echo false;
        }
    }

    public function checkDevice(){
        $this->autoRender = false;
        $result = 'OKAY';
        if($_POST){
            $DeviceId = isset($_POST['deviceId']) ? trim($_POST['deviceId']) : '';
            $company_code = $this->Session->read('company_code');
            $dev_data = $this->Device->query("SELECT `DeviceId` FROM mypayrol_control_db.devices WHERE `DeviceId` = '" .$DeviceId. "' AND devices.company_code = '" .$company_code. "'");
            if($dev_data){
                $result = isset($dev_data[0]['devices']['DeviceId']) ? 'EXIST' : 'OKAY';
            }
        }
        echo $result;
    }
}
