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
 */
class UserController extends AppController {

    public $datatable = array();

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'User';
    public $layout = 'default';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserCredentials', 'CompanyContactInfo', 'EmployeeDetails', 'UserCredentials', 'Banks', 'MobileUserCredentials', 'CentralUserCredentials');
    public $components = array('DatatablesManagement');

    /*
     * Dashboard landing view
     */

    public function saveavatar() {
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $resp = array();
        $this->autoRender = FALSE;
        try {

            if (
                    !isset($_FILES['avatarfile']['error']) ||
                    is_array($_FILES['avatarfile']['error'])
            ) {
                $resp['error'] = 'Invalid parameters.';
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['avatarfile']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $resp['error'] = 'No file sent.';
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $resp['error'] = 'Exceeded filesize limit.';
                default:
                    $resp['error'] = 'Unknown errors.';
            }

            // You should also check filesize here. 
            if ($_FILES['avatarfile']['size'] > 1000000) {
                $resp['error'] = 'Exceeded filesize limit.';
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                    $finfo->file($_FILES['avatarfile']['tmp_name']), array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                    ), true
                    )) {
                $resp['error'] = 'Invalid file format.';
            }

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.

            if ($user_group == '1') {
                $filename = sprintf('img/avatar/%s.%s', sha1_file($_FILES['avatarfile']['tmp_name']), $ext
                );
                if (!move_uploaded_file(
                                $_FILES['avatarfile']['tmp_name'], $filename
                        )) {
                    $resp['error'] = 'Failed to move uploaded file.';
                }
                //   echo 'File is uploaded successfully.';
                $this->CentralUserCredentials->setDataSource('controldb');
                /*
                  $this->CentralUserCredentials->updateAll(
                  array('avatar' => $filename),
                  array('user_id' => $this->Session->read('user_pkey'))
                  ); */

                $data['user_pkey'] = $this->Session->read('user_pkey');
                $data['avatar'] = $filename;
                //debug($data);
                $this->CentralUserCredentials->save($data);
            } else {
                $filename = sprintf('img/avatar/%s.%s', sha1_file($_FILES['avatarfile']['tmp_name']), $ext
                );
                if (!move_uploaded_file(
                                $_FILES['avatarfile']['tmp_name'], $filename
                        )) {
                    $resp['error'] = 'Failed to move uploaded file.';
                }
                //   echo 'File is uploaded successfully.';
                $this->UserCredentials->useDbConfig = $this->Session->read('ds');
                //debug($filename);
                $this->UserCredentials->query("UPDATE `user_credentials` SET `avatar` = '$filename' WHERE `emp_fkey` = '$emp_fkey' ");
            }
        } catch (RuntimeException $e) {

            //  echo $e->getMessage();
        }
        //echo json_encode( $resp);
    }
    
    public function checkpassvalidation($param = '') {
        
        $this->autoRender = FALSE;
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $resp = array();
        
        $arr_form_data = $this->request->data;
        $old_pass = $_REQUEST['oldpass'];
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $password = Security::hash($old_pass, null, true);
        $where = " password ='" . $password . "' ";
        $arr_user_details = $this->UserCredentials->find('first', array('conditions' => $where));
        
        if($arr_user_details){
            return 1;
        }else{
            return 0;
        }
        
    }
    
    public function change_image($param = '') {
     
        
    }
    
    public function load_basic_details($param = '') {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $emp = $this->Session->read('emp_fkey');
            $fields = 'INFOs.*,EmployeeDetails.emp_pkey,EmployeeDetails.*,Branches.branch_name,designation.desig_name,designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.*,mobile_no';
            $joins = array(
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                ),
                array(
                    'table' => 'designation',
                    'alias' => 'designation',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1)
                ),
                array(
                    'table' => 'branches',
                    'alias' => 'Branches',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1)
                ),
                array(
                    'table' => 'employee_info',
                    'alias' => 'INFOs',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = INFOs.emp_pkey')
                )
            );
            $conditions = array("EmployeeDetails.status" => "1", "EmployeeDetails.emp_pkey" => $emp);
            $this->datatable["conditions"] = $conditions;
            $resp_emp = array();
            $resp_emp["rows"] = array();
            $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
            $arr_data = $this->EmployeeDetails->find("all", array(
                'fields' => $fields,
                'joins' => $joins,
                "conditions" => $conditions,
                    )
            );
            
            $users = $this->UserCredentials->find('first', array("conditions" => array("emp_fkey" => $emp)));
            $user = $users['UserCredentials'];
            
            $this->set('arr_data', $arr_data);
    }
    
    
    public function savebasics_form(){
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $emp = $this->Session->read('emp_fkey');
            $fields = 'INFOs.*,EmployeeDetails.emp_pkey,EmployeeDetails.*,Branches.branch_name,designation.desig_name,designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.*,mobile_no';
            $joins = array(
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                ),
                array(
                    'table' => 'designation',
                    'alias' => 'designation',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1)
                ),
                array(
                    'table' => 'branches',
                    'alias' => 'Branches',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1)
                ),
                array(
                    'table' => 'employee_info',
                    'alias' => 'INFOs',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = INFOs.emp_pkey')
                )
            );
            $conditions = array("EmployeeDetails.status" => "1", "EmployeeDetails.emp_pkey" => $emp);
            $this->datatable["conditions"] = $conditions;
            $resp_emp = array();
            $resp_emp["rows"] = array();
            $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
            $arr_data = $this->EmployeeDetails->find("all", array(
                'fields' => $fields,
                'joins' => $joins,
                "conditions" => $conditions,
                    )
            );
        $company_code = strtoupper($this->Session->read('company_code'));
        $this->set('company_code', $company_code);
        $this->set('arr_data', $arr_data);
    }

    public function loadImage($param = '') {
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $emp = $this->Session->read('emp_fkey');
        $fields = 'INFOs.*,EmployeeDetails.emp_pkey,EmployeeDetails.*,Branches.branch_name,designation.desig_name,designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.*,mobile_no';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            ),
            array(
                'table' => 'designation',
                'alias' => 'designation',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1)
            ),
            array(
                'table' => 'branches',
                'alias' => 'Branches',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1)
            ),
            array(
                'table' => 'employee_info',
                'alias' => 'INFOs',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = INFOs.emp_pkey')
            )
        );
        $conditions = array("EmployeeDetails.status" => "1", "EmployeeDetails.emp_pkey" => $emp);
        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
        $arr_data = $this->EmployeeDetails->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            "conditions" => $conditions,
                )
        );
        //        debug($arr_data); 
        $users = $this->UserCredentials->find('first', array("conditions" => array("emp_fkey" => $emp)));
        $user = $users['UserCredentials'];
        $user['avatar'] = isset($user['avatar']) ? $user['avatar'] : "img/picture.jpg";
        $this->set("user", $user);
        $this->set('arr_data', $arr_data);
    }

    public function profile() {

        $user_group = $this->Session->read('user_group');
         $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $plan=$this->UserCredentials->query('SELECT plan FROM comp_contact_info');
        $plan=isset($plan['0']['comp_contact_info']['plan'])?$plan['0']['comp_contact_info']['plan']:'';
		 $this->set('plan',$plan);
        if ($user_group == '1') {

            $this->CentralUserCredentials->setDataSource('controldb');
		$where['user_id'] =  $this->Session->read('login_user_id');
		$arr_central_user_details=$this->CentralUserCredentials->find('first',array('conditions'=>$where));
	   
		$user = $arr_central_user_details['CentralUserCredentials'];
		if($user['avatar'] == null){
			$user['avatar'] = "img/picture.jpg"; 
		}
            $this->set("user", $user);
            $this->CompanyContactInfo->useDbConfig = $this->Session->read('ds');
            $arr_comp_contact_info = $this->CompanyContactInfo->find('first');
            $this->set("contactinfo", $arr_comp_contact_info['CompanyContactInfo']);
            $this->render('profile');
            
        } else if ($user_group == '2') {
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $emp = $this->Session->read('emp_fkey');
            $fields = 'INFOs.*,EmployeeDetails.emp_pkey,EmployeeDetails.*,Branches.branch_name,designation.desig_name,designation.desig_code,emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.*,mobile_no';
            $joins = array(
                array(
                    'table' => 'emp_proff',
                    'alias' => 'EmployeeProfessionalDetails',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
                ),
                array(
                    'table' => 'designation',
                    'alias' => 'designation',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeProfessionalDetails.designation = designation.desig_code', 'designation.status' => 1)
                ),
                array(
                    'table' => 'branches',
                    'alias' => 'Branches',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.branch_code = Branches.branch_code', 'Branches.status' => 1)
                ),
                array(
                    'table' => 'employee_info',
                    'alias' => 'INFOs',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeDetails.emp_pkey = INFOs.emp_pkey')
                )
            );
            $conditions = array("EmployeeDetails.status" => "1", "EmployeeDetails.emp_pkey" => $emp);
            $this->datatable["conditions"] = $conditions;
            $resp_emp = array();
            $resp_emp["rows"] = array();
            $count = $this->EmployeeDetails->find("count", array('joins' => $joins, "conditions" => $conditions));
            $arr_data = $this->EmployeeDetails->find("all", array(
                'fields' => $fields,
                'joins' => $joins,
                "conditions" => $conditions,
                    )
            );
            $arr_activits = $this->EmployeeDetails->query("select * from activity where emp_fkey = '$emp' ");
            //        debug($arr_data); 
            $users = $this->UserCredentials->find('first', array("conditions" => array("emp_fkey" => $emp)));
            $user = $users['UserCredentials'];
            $user['avatar'] = isset($user['avatar']) ? $user['avatar'] : "img/picture.jpg";
            $this->set("user", $user);
            $this->set('arr_data', $arr_data);
            $this->set('emp_fkey', $emp);
            $this->set('arr_activits', $arr_activits);
            $this->render('user');
        }
    }

    public function savePassword() {
        $this->autoRender = FALSE;
        $resp = array();
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        $password = "";
        $password1 = "";
        $password2 = "";
        $isAvailable = true;
        if (isset($_REQUEST["password"])) {
            $password = $_REQUEST["password"];
        } if (isset($_REQUEST["password1"])) {
            $password1 = $_REQUEST["password1"];
        } if (isset($_REQUEST["password2"])) {
            $password2 = $_REQUEST["password2"];
        }
        $proceed = true;
        if (!$password) {
            $resp["success"] = false;
            $resp["msg"] = "Current Password Is Invalid";
            $proceed = false;
        }
        if (!$password1) {
            $resp["success"] = false;
            $resp["msg"] = "New Password Is Invalid";
            $proceed = false;
        }
        if (!$password2) {
            $resp["success"] = false;
            $resp["msg"] = "Password Is Not Matching";
            $proceed = false;
        }
        if ($password1 != $password2) {
            $resp["success"] = false;
            $resp["msg"] = "Password Is Not Matching";
            $proceed = false;
        }

        $password = Security::hash($password, null, true);
        $password_new = Security::hash($password1, null, true);
        if ($user_group == '1') {
            $username = $this->Session->read("login_user_id");
            $where = "user_id = '" . $username . "' and password ='" . $password . "' AND access_allowed = 'y'";
            //	
            $this->CentralUserCredentials->setDataSource('controldb');

            $arr_central_user_details = $this->CentralUserCredentials->find('first', array('conditions' => $where));
            if ($arr_central_user_details && $proceed) {

                $data['user_pkey'] = $arr_central_user_details['CentralUserCredentials']['user_pkey'];
                $data['password'] = $password_new;
                $this->CentralUserCredentials->save($data);
                $resp["success"] = true;
                $resp["msg"] = "Password Changed Successfully";
            } else {
                $resp["success"] = false;
                $resp["msg"] = "Current Password Is Invalid";
            }
        } else {
            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $this->MobileUserCredentials->useDbConfig = $this->Session->read('ds');
            $user_password = $this->UserCredentials->find("first", array("conditions" => array("emp_fkey" => $emp_fkey)));
            $arr_data['user_pkey'] = $user_password['UserCredentials']['user_pkey'];
            $arr_data['password'] = $password_new;
            $this->UserCredentials->save($arr_data);
            $user_id = $user_password['UserCredentials']['user_id'];
            try{
                $this->MobileUserCredentials->query("UPDATE mob_user_credentials set password = '$password1' where user_id = '$user_id'");
                $resp["success"] = true;
                $resp["msg"] = "Password Changed Successfully ";
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["msg"] = "Password Change Failed ";
            }
            
        }

        echo json_encode($resp);
    }
    
    public function savebasics($param = '') {
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;;
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');
        
        $arr_form_data['modified_by'] = $this->Session->read('login_user_id');
        
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        try{
            $users = $this->EmployeeDetails->save($arr_form_data);
            $resp["success"] = true;
            $resp["msg"] = "Changes saved Successfully ";
        } catch (Exception $ex) {
            $resp["success"] = false;
            $resp["msg"] = "Saving Failed ";
        }
		echo json_encode($resp);
        
    }
    
    public function saveNames() {
        $this->autoRender = FALSE;
        $arr_form_data = $this->request->data;
        $resp = array();
        $user_group = $this->Session->read('user_group');
        $emp_fkey = $this->Session->read('emp_fkey');

        if ($user_group == '1') {
            $username = $this->Session->read("login_user_id");
            $where = "user_id = '" . $username . "' AND access_allowed = 'y'";
            //	
            $this->CentralUserCredentials->setDataSource('controldb');

            $arr_central_user_details = $this->CentralUserCredentials->find('first', array('conditions' => $where));
            if ($arr_central_user_details) {

                $data['user_pkey'] = $arr_central_user_details['CentralUserCredentials']['user_pkey'];
                $data['first_name'] = $arr_form_data['first_name'];
                $data['last_name'] = $arr_form_data['last_name'];
//                $data['middle_name'] = $arr_form_data['middle_name'];
                $this->CentralUserCredentials->save($data);
                $resp["success"] = true;
                $resp["msg"] = "Info Changed Successfully";
            } else {
                $resp["success"] = false;
                $resp["msg"] = "Invalid";
            }
        } else {
            $this->UserCredentials->useDbConfig = $this->Session->read('ds');
            $this->MobileUserCredentials->useDbConfig = $this->Session->read('ds');
            $user_password = $this->UserCredentials->find("first", array("conditions" => array("emp_fkey" => $emp_fkey)));
            $arr_data['user_pkey'] = $user_password['UserCredentials']['user_pkey'];
            $arr_data['password'] = $password_new;
            $this->UserCredentials->save($arr_data);
            $user_id = $user_password['UserCredentials']['user_id'];
            $this->MobileUserCredentials->query("UPDATE mob_user_credentials set password = '$password1' where user_id = '$user_id'");
        }

        echo json_encode($resp);
    }

}
