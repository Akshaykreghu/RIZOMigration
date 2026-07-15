<?php

App::uses('Component', 'Controller');
App::uses('Router', 'Routing');
App::uses('Security', 'Utility');
App::uses('Debugger', 'Utility');
App::uses('Hash', 'Utility');
App::uses('CakeSession', 'Model/Datasource');
App::uses('BaseAuthorize', 'Controller/Component/Auth');
App::uses('BaseAuthenticate', 'Controller/Component/Auth');

class LoginManagementComponent extends Component
{

    public $components = array('Session');
    public $controller;
    public $primary = array();

    public function initialize(Controller $controller)
    {
        $this->controller = $controller;
    }

    //called after Controller::beforeFilter()
    function startup(Controller $controller)
    {
    }

    //called after Controller::beforeRender()
    function beforeRender(Controller $controller)
    {
    }

    //called after Controller::render()
    function shutdown(Controller $controller)
    {
    }

    //called before Controller::redirect()
    function beforeRedirect(Controller $controller, $url, $status = null, $exit = true)
    {
    }

    function redirectSomewhere($value)
    {
        // utilizing a controller method
        $this->controller->redirect($value);
    }

    public function verifyAdminLogin($username, $password)
    {
        $arr_user_details = array();
        $password = Security::hash($password, null, true);
        $where = " (user_id = '" . $username . "' OR email = '" . $username . "' ) and password ='" . $password . "' AND access_allowed = 'y'";

        $this->controller->CentralUserCredentials->setDataSource('controldb');

        $arr_central_user_details = $this->controller->CentralUserCredentials->find('first', array('conditions' => $where));

        $company_code = isset($arr_central_user_details['CentralUserCredentials']['company_code']) ? $arr_central_user_details['CentralUserCredentials']['company_code'] : '';

        $this->controller->CentralControl->setDataSource('controldb');
        $arr_company_db = $this->controller->CentralControl->find('first', array(
            'fields' => 'CentralControl.*',
            'conditions' => array('company_code' => $company_code, 'end_date_effective >= ' . date("Y-m-d"))
        ));


        if (isset($arr_company_db['CentralControl'])) {
            $reset_login_flag = isset($arr_central_user_details['CentralUserCredentials']['reset_login_flag']) ? $arr_central_user_details['CentralUserCredentials']['reset_login_flag'] : '';

            if ($reset_login_flag == 'Y') {

                $key = Security::hash(String::uuid(), 'sha512', true);
                $hash = sha1($username . rand(0, 100));

                $url = Router::url(array('controller' => 'Site', 'action' => 'resetadmin'), true) . '/' . $username . '/' . $key . '#' . $hash;
                $ms = $url;
                $ms = wordwrap($ms, 1000);

                //Insert key on table
                $user_pkey = isset($arr_central_user_details['CentralUserCredentials']['user_pkey']) ? $arr_central_user_details['CentralUserCredentials']['user_pkey'] : 0;

                $data = array();
                $data['user_pkey'] = $user_pkey;
                $data['attr1'] = $key;
                $result = $this->controller->CentralUserCredentials->save($data);

                return array('resetlogin' => true, 'ms' => $ms);
            } else {
                //$this->Session->write('login_user_id', $arr_central_user_details['CentralUserCredentials']['user_id']);
                $this->Session->write('login_user_id', isset($arr_central_user_details['CentralUserCredentials']['user_id']) ? strtoupper($arr_central_user_details['CentralUserCredentials']['user_id']) : '');
                $this->Session->write('user_group', 1);
                $firstname = isset($arr_central_user_details['CentralUserCredentials']['first_name']) ? $arr_central_user_details['CentralUserCredentials']['first_name'] : '';
                $lastname = isset($arr_central_user_details['CentralUserCredentials']['last_name']) ? $arr_central_user_details['CentralUserCredentials']['last_name'] : '';
                //$this->Session->write('user_name', $arr_central_user_details['CentralUserCredentials']['first_name'] . " " . $arr_central_user_details['CentralUserCredentials']['last_name']);
                $this->Session->write('user_name', $firstname . " " . $lastname);
                $this->Session->write('company_key', $arr_company_db['CentralControl']['control_pkey']);
                $this->Session->write('company_code', strtoupper($company_code));
            }
        } else {
            $this->Session->delete('login_user_id');
        }
        return $arr_central_user_details;
    }

    public function verifyEmployeeLogin($username, $password, $type = '')
    {
        $arr_user_details = array();

        if ($type == '') {
            $password = Security::hash($password, null, true);
        }

        //$company_code = substr($username, 0, 4);
        //$company_code = substr($username, 0, 4);


        $this->controller->CentralControl->setDataSource('controldb');

        if (strpos($username, '@') !== false) {
            $arr_device_branches = $this->controller->CentralControl->query("SELECT * FROM emp_device_comp_branch WHERE lcase(email) = '" . $username . "' and status=1 limit 1 ");
            // debug($arr_device_branches); die();
            $company_code = 0;
            if (count($arr_device_branches) == 1) {
                $company_code = isset($arr_device_branches['0']['emp_device_comp_branch']['Company_code']) ? $arr_device_branches['0']['emp_device_comp_branch']['Company_code'] : 0;
                $username = isset($arr_device_branches['0']['emp_device_comp_branch']['emp_username']) ? $arr_device_branches['0']['emp_device_comp_branch']['emp_username'] : 0;
            }

            $arr_company_db = $this->controller->CentralControl->find('first', array(
                'fields' => 'CentralControl.*',
                'conditions' => array('company_code' => $company_code, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
            ));
        } else {

            $company_code = preg_replace("/[^a-zA-Z]+/", "", $username);
            $arr_company_db = $this->controller->CentralControl->find('first', array(
                'fields' => 'CentralControl.*',
                'conditions' => array('company_code' => $company_code, 'end_date_effective' >= date('Y-M-D')/* , 'active'=>1 */)
            ));
        }

        if (count($arr_company_db) == 1) {
            //company exists and fetch corresponding DB	

            $user_name = isset($arr_company_db['CentralControl']['Admin_name']) ? $arr_company_db['CentralControl']['Admin_name'] : '';
            $user_db = isset($arr_company_db['CentralControl']['user_db']) ? $arr_company_db['CentralControl']['user_db'] : '';
            $user_pwd = isset($arr_company_db['CentralControl']['user_pwd']) ? $arr_company_db['CentralControl']['user_pwd'] : '';
            $companydb = array(
                'datasource' => 'Database/Mysql',
                'persistent' => false,
                'host' => '127.0.0.1',
                'login' => $user_name,
                'password' => $user_pwd,
                'database' => $user_db,
                'prefix' => '',
                //'encoding' => 'utf8',
            );
            ConnectionManager::create('companydb', $companydb);
            $this->Session->write("ds", 'companydb');

            //$this->controller->UserCredentials->setDataSource('companydb');
            $this->controller->UserCredentials->useDbConfig = $this->Session->read('ds');
            $arr_user_details_for_lock_check = $this->controller->UserCredentials->find('first', array('conditions' => array('user_id' => $username)));
            $cnt_incorrect_login_attempt = isset($arr_user_details_for_lock_check['UserCredentials']['incorrect_login_attempt']) ? $arr_user_details_for_lock_check['UserCredentials']['incorrect_login_attempt'] : 0;

            if ($cnt_incorrect_login_attempt == 3) {
                $this->Session->delete('login_user_id');
                return array('locked' => true);
            }

            $where = "user_id = '" . $username . "' and password ='" . $password . "' AND access_allowed = 'y'";
            $arr_user_details = $this->controller->UserCredentials->find('first', array('conditions' => $where));
            $emp_pkey = isset($arr_user_details['UserCredentials']['emp_fkey']) ? $arr_user_details['UserCredentials']['emp_fkey'] : '';
            $arr_user_name = $this->controller->UserCredentials->query("select concat(first_name,' ',IFNULL(last_name, '')) Name from emp_details where emp_pkey = '$emp_pkey' and status = '1' ");
            if (count($arr_user_details) == 1) {
                if ($arr_user_name) {
                    $reset_login_flag = isset($arr_user_details['UserCredentials']['reset_login_flag']) ? $arr_user_details['UserCredentials']['reset_login_flag'] : '';
                    if ($reset_login_flag == 'Y') {

                        $key = Security::hash(String::uuid(), 'sha512', true);
                        $hash = sha1($username . rand(0, 100));

                        $url = Router::url(array('controller' => 'Site', 'action' => 'reset'), true) . '/' . $company_code . '/' . $key . '#' . $hash;
                        $ms = $url;
                        $ms = wordwrap($ms, 1000);

                        //Insert key on table
                        $user_pkey = isset($arr_user_details['UserCredentials']['user_pkey']) ? $arr_user_details['UserCredentials']['user_pkey'] : 0;

                        $data = array();
                        $data['user_pkey'] = $user_pkey;
                        $data['attr1'] = $key;
                        $this->controller->UserCredentials->save($data);

                        /*$this->redirect(
                        array(
                            'controller' => 'Site', 
                            'action' => 'reset'                            
                        ),
                        array(
                            'company_code' => $company_code,
                            'token' => $key.'#'.$hash
                        )
                    );*/

                        return array('resetlogin' => true, 'ms' => $ms);
                    } else {   //edited by megha code to uppercase             
                        $this->Session->write('login_user_id', strtoupper($arr_user_details['UserCredentials']['user_id']));
                        $this->Session->write('user_group', 2);
                        //$this->Session->write('user_name', $arr_user_details['UserCredentials']['first_name'] . " " . $arr_user_details['UserCredentials']['last_name']);
                        $this->Session->write('user_name', $arr_user_name['0']['0']['Name']);
                        $this->Session->write('company_key', $arr_company_db['CentralControl']['control_pkey']);
                        $this->Session->write('emp_fkey', $arr_user_details['UserCredentials']['emp_fkey']);
                        $this->Session->write('company_code', strtoupper($company_code));
                    }
                } else {
                    return array('Unauthorized' => true);
                }
            } else {
                $this->Session->delete('login_user_id');
                $data = array();
                $dates = $data['end_date'] = date('Y-m-d');
                $end_date = isset($arr_user_details_for_lock_check['UserCredentials']['end_date']) ? $arr_user_details_for_lock_check['UserCredentials']['end_date'] : '';

                if ($end_date === $dates) {
                    $cnt_incorrect_login_attempt++;
                } else {
                    $cnt_incorrect_login_attempt = 1;
                }
                //$cnt_incorrect_login_attempt++;
                //$cnt_incorrect_login_attempt += $cnt_incorrect_login_attempt;
                $user_pkey = isset($arr_user_details_for_lock_check['UserCredentials']['user_pkey']) ? $arr_user_details_for_lock_check['UserCredentials']['user_pkey'] : 0;
                if ($user_pkey > 0) {
                    $data = array();
                    $data['user_pkey'] = $user_pkey;
                    $data['incorrect_login_attempt'] = $cnt_incorrect_login_attempt;
                    if ($cnt_incorrect_login_attempt == 3) {
                        $data['locked'] = 1;
                        $this->controller->UserCredentials->save($data);
                        return array('locked' => true);
                    } else {
                        $this->controller->UserCredentials->save($data);
                    }
                }
            }
        } else {
            $this->Session->delete('login_user_id');
        }
        return $arr_user_details;
    }
}
