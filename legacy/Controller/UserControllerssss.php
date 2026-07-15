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
class UserController extends AppController {

	/**
	 * Controller name
	 *
	 * @var string
	 */
	public $name = 'Usssser';
	/**
	 * This controller does not use a model
	 *
	 * @var array
	 */
	public $uses = array('UserCredentials', 'CentralControl', 'CentralUserCredentials', 'Registrations', 'Currency', 'Country', 'MasterDb','EmployeeMenu');

	public $components = array('LoginManagement', 'Session','Email');

public function profile()
{
	
}

	public function savePassword() {
		$this -> autoRender = FALSE;
		$resp = array();
		
		
		$password   =	"";
		$password1  =	"";
		$password2  =	"";
		$isAvailable = true;
		if (isset($_REQUEST["password"])) {
			$password  =	$_REQUEST["password"];
		} if(isset($_REQUEST["password1"])){
				$password1  =	$_REQUEST["password1"];
		} if(isset($_REQUEST["password2"])){
			$password2  =	$_REQUEST["password2"];
		}
		$proceed = true;
		if(!$password){
			$resp["success"] = false;
			$resp["msg"] = "Current Password Is Invalid";
			$proceed = false;
		}
		if(!$password1){
			$resp["success"] = false;
			$resp["msg"] = "New Password Is Invalid";
			$proceed = false;
		}
		if(!$password2){
			$resp["success"] = false;
			$resp["msg"] = "Password Is Not Matching";
			$proceed = false;
		}
		if($password1 != $password2){
			$resp["success"] = false;
			$resp["msg"] = "Password Is Not Matching";
				$proceed = false;
		}
	//	debug($password1 ."!=". $password2);
		$password = 	Security::hash($password, null, true);
		$password_new = 	Security::hash($password1, null, true);
		$username  = $this->Session->read("login_user_id");
				$where="user_id = '" . $username . "' and password ='".$password."' AND access_allowed = 'y'";
					//	
				$this->CentralUserCredentials->setDataSource('controldb');
			
				$arr_central_user_details=$this->CentralUserCredentials->find('first',array('conditions'=>$where));
		
				if($arr_central_user_details && $proceed){
					
					$data['user_pkey'] = $arr_central_user_details['CentralUserCredentials']['user_pkey'];
					$data['password'] = $password_new;
					$this->CentralUserCredentials->save($data);
					$resp["success"] = true;
					$resp["msg"] = "Password Changed Successfully";
				}

			
		echo json_encode($resp); 
		}
		//echo json_encode(array('valid' => $isAvailable, ));


	public function register() {
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
		if ($this -> Registrations -> save($user)) {
			$success = true;
			$this->setup($user);
			
		}
		//$user[''] = $_POST['firstname'];
		//$user[''] = $_POST['firstname'];

		echo json_encode(array('saved' => $success, ));
		$this -> autoRender = FALSE;

	}

}
