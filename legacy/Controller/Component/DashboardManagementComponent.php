<?php
class LoginManagementComponent extends Component {
    public $components = array('Session');
	public $controller;
	public $primary = array();
	
	public function initialize(Controller $controller){
	    $this->controller = $controller;
	}
	//called after Controller::beforeFilter()
	function startup(Controller $controller) {
	}
	//called after Controller::beforeRender()
	function beforeRender(Controller $controller) {
	}
	//called after Controller::render()
	function shutdown(Controller $controller) {
	}
	//called before Controller::redirect()
	function beforeRedirect(Controller $controller, $url, $status=null, $exit=true) {
	}
	function redirectSomewhere($value) {
	// utilizing a controller method
	    $this->controller->redirect($value);
	}	
	
	public function verifyAdminLogin($username,$password) {
				$arr_user_details = array();
				$where="user_id = '" . $username . "' and password ='".$password."' AND access_allowed = 'y'";
				$arr_central_user_details=$this->controller->CentralUserCredentials->find('first',array('conditions'=>$where));
				$company_code = isset($arr_central_user_details['CentralUserCredentials']['company_code'])?$arr_central_user_details['CentralUserCredentials']['company_code']:'';
				$arr_company_db = $this->controller->CentralControl->find('first',
						array('fields' => 'CentralControl.*',
								'conditions'=>array('company_code'=>$company_code,'end_date_effective >= '.date("Y-m-d"))
						));
				if (count($arr_company_db) == 1)
				{
					//company exists and fetch corresponding DB
					$this->controller->UserCredentials->setDataSource('companydb');
					$where="user_id = '" . $username . "' and password ='".$password."' AND access_allowed = 'y'";
					//echo $where;
					$arr_user_details=$this->controller->UserCredentials->find('first',array('conditions'=>$where));
					//var_dump($arr_user_details);die();
					if (count($arr_user_details) == 1)
					{
						$this->Session->write('login_user_id',$arr_user_details['UserCredentials']['user_id']);
						$this->Session->write('user_group',1);
						$this->Session->write('user_name',$arr_user_details['UserCredentials']['first_name']." ".$arr_user_details['UserCredentials']['last_name']);
						$this->Session->write('company_key',$arr_company_db['CentralControl']['control_pkey']);
					}
					else
					{
						$this->Session->delete('login_user_id');
					}
				}else{
					$this->Session->delete('login_user_id');
				}
				return $arr_user_details;
	}
	public function verifyEmployeeLogin($username,$password) {
		$arr_user_details = array();
		$company_code = substr($username,0,4);
		$arr_company_db = $this->controller->CentralControl->find('first',
				array('fields' => 'CentralControl.*',
						'conditions'=>array('company_code'=>$company_code,'end_date_effective'>=date('Y-M-D')/*, 'active'=>1*/)
				));
		if (count($arr_company_db) == 1)
		{
			//company exists and fetch corresponding DB
			$this->controller->UserCredentials->setDataSource('companydb');
			
			$where="user_id = '" . $username . "' and password ='".$password."' AND access_allowed = 'y'";
			$arr_user_details=$this->controller->UserCredentials->find('first',array('conditions'=>$where));
				
			if (count($arr_user_details) == 1)
			{
				$this->Session->write('login_user_id',$arr_user_details['UserCredentials']['user_id']);
				$this->Session->write('user_group',2);
				$this->Session->write('user_name',$arr_user_details['UserCredentials']['first_name']." ".$arr_user_details['UserCredentials']['last_name']);
				$this->Session->write('company_key',$arr_company_db['CentralControl']['control_pkey']);
			}
			else
			{
				$this->Session->delete('login_user_id');
			}
		}else{
			$this->Session->delete('login_user_id');
		}
		return $arr_user_details;
	}
}