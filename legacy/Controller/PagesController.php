<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
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

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends LoginAppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('CentralUserCredentials','CentralControl','Useraccess','UserCredentials');
        
        public $components = array('LoginManagement','Session');
        /**
 * Displays a view
 *
 * @return void
 * @throws NotFoundException When the view file could not be found
 *	or MissingViewException in debug mode.
 */
	public function display() {
        
            if (isset($_COOKIE['user_id']) && isset($_COOKIE['password'])){
                $userg =isset($_COOKIE['userGroup'])?$_COOKIE['userGroup'] :'';
                if($userg == '1'){
                $arr_resp = $this->LoginManagement->verifyAdminLogin($_COOKIE['user_id'],$_COOKIE['password']);
                if (!empty($arr_resp)) {
                    $this->redirect(array('controller' => 'Dashboard', 'action' => 'index'));
                }
                }
                else if($userg == '2')
                {
                    $arr_resp = $this->LoginManagement->verifyEmployeeLogin($_COOKIE['user_id'],$_COOKIE['password']);
                        if (!empty($arr_resp)) {
                            if(isset($arr_resp['locked']) && $arr_resp['locked']){
                                $proceed = false;
                                $messages = "Account locked for security reasons. Please contact Administrator.";
                            }else if(isset($arr_resp['resetlogin']) && $arr_resp['resetlogin']){
                                $ms = isset($arr_resp['ms'])?$arr_resp['ms']:'/';
                                $this->redirect($ms);
                            }else{
                                 $this->Useraccess->useDbConfig=$this->Session->read('ds');
                                 $userid =  $_COOKIE['user_id'];
                             //    $arr_useraccess = $this->Useraccess->query("select menu_name,menu_id,menu_title from emp_menu as empmenu where menu_name  = 'Dashboard' and menu_title ='HIerarchy'");
                                 $user = $this->Useraccess ->query("select * from user_credentials where user_id = '$userid'");
                                 $user_pkey = isset($user['0']['user_credentials']['emp_fkey']) ? $user['0']['user_credentials']['emp_fkey'] : '';
                               //  $adminid=$arr_useraccess['0']['empmenu']['menu_id'];
                                 $useracess = $this->Useraccess->query("select * from user_access as Useraccess where user_fkey = '$user_pkey' and menu_id = '0' and active = 'Y' ");
                                 $access = isset($useracess['0']['Useraccess']['active']) ? $useracess['0']['Useraccess']['active'] : '' ;
                                   if($access == 'Y')
                                     {
                                           $this->redirect(array('controller' => 'Dashboard', 'action' => 'hierarchydashboard'));
                                     }
                                   else 
                                     {
                                           $this->redirect(array('controller' => 'Dashboard', 'action' => 'empdashboard'));
                            
                                     }
                                  }
                        } else {
                            $proceed = false;
                            $messages = "Invalid Username or Password";
                        }
                }
                else {
                    setcookie('user_id', '', time()-(60*60*1),'/');                            
                    setcookie('password', '', time()-(60*60*1),'/');
                }
                
            }
            //else
            //{
                $string = $_SERVER["SERVER_NAME"] . $_SERVER['REQUEST_URI'];
                $arr = explode(".", $string, 2);
                $company = $arr[0];
                $this->set('company',$company);
                switch($company)
                {
                    case "localhost":$this->layout = "giridhar";
                        break;
                   case "giridhar":$this->layout = "giridhar";
                        break;
                    
                    default:$this->layout = "login";
                        break;
                }
               $path = func_get_args();

		$count = count($path);
		if (!$count) {
			return $this->redirect('/');
		}
		$page = $subpage = $title_for_layout = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title_for_layout = Inflector::humanize($path[$count - 1]);
		}
		$this->set(compact('page', 'subpage', 'title_for_layout'));

		try {
			$this->render(implode('/', $path));
		} catch (MissingViewException $e) {
			if (Configure::read('debug')) {
				throw $e;
			}
			throw new NotFoundException();
		}
	}
        //}
}
