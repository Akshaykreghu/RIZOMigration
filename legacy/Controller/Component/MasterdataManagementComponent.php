<?php
class MasterdataManagementComponent extends Component {
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
	
	public function getDepartmentsListForCombo() {
		$this->controller->Departments->useDbConfig = $this->Session->read('ds');
		$arr_departments	=	Set::extract('/Departments/.',$this->controller->Departments->find('all',array('fields'=>'dept_code,dept_name','conditions'=>array('status'=>1))));
		return $arr_departments;
	}
	public function getGradesListForCombo() {
		$this->controller->Grades->useDbConfig = $this->Session->read('ds');
		$arr_grades	=	Set::extract('/Grades/.',$this->controller->Grades->find('all',array('fields'=>'grade_pkey,grade_code,grade_name','conditions'=>array('status'=>1))));
		return $arr_grades;
	}
	public function getVerticalsListForCombo()
	{
		$this->controller->Verticals->useDbConfig = $this->Session->read('ds');
		$arr_verticals	=	Set::extract('/Verticals/.',$this->controller->Verticals->find('all',array('fields'=>'vert_code,vertical_name','conditions'=>array('status'=>1))));
		return $arr_verticals;
	}
	public function getBranchesListForCombo($emps = 0)
	{
		$this->controller->Units->useDbConfig = $this->Session->read('ds');
                if($emps != 0){
                    //$conditions = array("status = 1 and branch_code in (select distinct(emp_branch) from emp_proff where emp_proff.attr1 = 14)");
                     
                    //edited by megha on 03_06_19 emp_proff.attr1 = 14 changed tp $emps
                    //$conditions = array("status = 1 and branch_code in (select distinct(emp_branch) from emp_proff where emp_proff.attr1 = '$emps')");
                $conditions = array("status = 1 and branch_code in (select distinct(emp_branch) from emp_proff join emp_details on (emp_proff.emp_fkey=emp_details.emp_pkey) where emp_proff.attr1 = ".$emps." and emp_details.status=1)");
                    
                }
                else{
                    $conditions = array("status = 1");
                }
		$arr_branches	=	Set::extract('/Units/.',$this->controller->Units->find('all',array('fields'=>'id,branch_code,branch_name','conditions'=>array($conditions))));
		return $arr_branches;
	}
        
	public function getDesignationsListForCombo() {
		$this->controller->Designation->useDbConfig = $this->Session->read('ds');
		$arr_Designation	=	Set::extract('/Designation/.',$this->controller->Designation->find('all',array('fields'=>'desig_code,desig_name','conditions'=>array('status'=>1))));
		return $arr_Designation;
        }
        public function getStoreListForCombo()
	{
		$this->controller->Store->useDbConfig = $this->Session->read('ds');
		$arr_stores	=	Set::extract('/Store/.',$this->controller->Store->find('all',array('fields'=>'store_location,store_master_pkey','conditions'=>array('status'=>1))));
		return $arr_stores;
	}
	public function getEmployeesForFeature($emp_id = null,$branch_id = null)
{
    $ds = $this->Session->read('ds');

   $user_id_session = $this->Session->read('user_id');
   $emp_pkey = !empty($user_id_session) ? $user_id_session : $this->Session->read('emp_fkey');
    $feature_id = $this->Session->read('current_feature_id');

    $this->controller->EmployeeDetails->useDbConfig = $ds;

    // Check hierarchy
    $access = $this->controller->EmployeeDetails->query("
        SELECT is_hierarchy
        FROM user_feature_branch_access
        WHERE user_fkey='$emp_pkey'
        AND feature_fkey='$feature_id'
        AND active='Y'
        LIMIT 1
    ");

    // HIERARCHY ACCESS
    if(!empty($access) && $access[0]['user_feature_branch_access']['is_hierarchy']=='Y')
    {
        $employees = $this->controller->EmployeeDetails->query("
            SELECT emp_pkey id,
            CONCAT(emp_fname,' ',emp_lname) text
            FROM emp_details
            WHERE emp_pkey IN
            (
                SELECT emp_fkey
                FROM emp_proff
                WHERE manager_emp_id='$emp_pkey'
            )
            AND status=1
        ");

        return $employees;
    }

    // BRANCH ACCESS
    $branch_condition = "";

    if(!empty($branch_id))
        $branch_condition = "AND emp_branch='$branch_id'";
    else
        $branch_condition = "
        AND emp_branch IN
        (
            SELECT branch_fkey
            FROM user_feature_branch_access
            WHERE user_fkey='$emp_pkey'
            AND feature_fkey='$feature_id'
            AND active='Y'
        )";

    $employees = $this->controller->EmployeeDetails->query("
        SELECT emp_pkey id,
        CONCAT(emp_fname,' ',emp_lname) text
        FROM emp_details
        WHERE status=1
        $branch_condition
        ORDER BY emp_fname
    ");

    return $employees;
}
public function getFeatureAccessContext()
{
    $ds = $this->Session->read('ds');

    $user_id_session = $this->Session->read('user_id');
    $emp_pkey = !empty($user_id_session) ? $user_id_session : $this->Session->read('emp_fkey');
    $feature_id = $this->Session->read('current_feature_id');

    $this->controller->EmployeeDetails->useDbConfig = $ds;

    $access = $this->controller->EmployeeDetails->query("
        SELECT is_hierarchy
        FROM user_feature_branch_access
        WHERE user_fkey='$emp_pkey'
        AND feature_fkey='$feature_id'
        AND active='Y'
        LIMIT 1
    ");

    $context = [
        'is_ho' => false,
        'is_hierarchy' => false,
        'has_access' => !empty($access)
    ];

    if(!empty($access) && $access[0]['user_feature_branch_access']['is_hierarchy']=='Y')
        $context['is_hierarchy']=true;

    return $context;
}

public function getOwnBranch($emp_pkey)
{
    $controller = $this->controller;
    $ds = $controller->Session->read('ds');
    $controller->EmployeeDetails->useDbConfig = $ds;

    $sql = "
        SELECT 
            b.branch_code,
            b.branch_name
        FROM branches b
        JOIN emp_details e ON e.branch_code = b.branch_code
        WHERE e.emp_pkey = '$emp_pkey'
        AND b.status = 1
        LIMIT 1
    ";

    return $controller->EmployeeDetails->query($sql);
}
public function getBranchesForFeature()
{
    $ds = $this->Session->read('ds');

    $user_id_session = $this->Session->read('user_id');
    $emp_pkey = !empty($user_id_session) ? $user_id_session : $this->Session->read('emp_fkey');
    $feature_id = $this->Session->read('current_feature_id');

    $this->controller->Units->useDbConfig = $ds;

    $branches = $this->controller->Units->query("
        SELECT id,branch_code,branch_name
        FROM branches
        WHERE status=1
        AND branch_code IN
        (
            SELECT branch_fkey
            FROM user_feature_branch_access
            WHERE user_fkey = '$emp_pkey'
            AND feature_fkey = '$feature_id'
            AND active='Y'
        )
        ORDER BY branch_name
    ");

    $result = [];

    if(!empty($branches))
    {
        foreach($branches as $b)
        {
            $result[] = (object)[
                'id'   => $b['branches']['id'],
                'text' => $b['branches']['branch_name'],
                'code' => $b['branches']['branch_code']
            ];
        }
    }

    return $result;
}

// public function getHierarchyEmployeesByBranch($emp_pkey,$branch_code)
// {
//     $controller = $this->controller;

//     $ds = $controller->Session->read('ds');
//     $controller->EmployeeDetails->useDbConfig = $ds;

//     $sql = "
//         SELECT 
//             e.emp_pkey AS id,
//             CONCAT(e.first_name,' (',e.emp_id,')') AS text
//         FROM emp_details e
//         JOIN emp_proff p 
//             ON p.emp_fkey = e.emp_pkey
//         WHERE p.attr1 = ".$emp_pkey."
//         AND e.branch_code = '".$branch_code."'
//         AND e.status = 1
//         ORDER BY e.first_name
//     ";

//     return $controller->EmployeeDetails->query($sql);
// }
public function getHierarchyEmployeesByBranch($emp_pkey, $branch_code)
{
    $controller = $this->controller;
    $ds = $controller->Session->read('ds');
    $controller->EmployeeDetails->useDbConfig = $ds;

    // Make sure inputs are safe
    $emp_pkey_safe = intval($emp_pkey);
    $branch_code_safe = $controller->EmployeeDetails->getDataSource()->value($branch_code, 'string');

    $sql = "
        SELECT 
            e.emp_pkey AS id,
            CONCAT(e.first_name,' (',p.emp_company_id,')') AS text
        FROM emp_details e
        JOIN emp_proff p 
            ON p.emp_fkey = e.emp_pkey
        WHERE p.attr1 = {$emp_pkey_safe}
        AND e.branch_code = {$branch_code_safe}
        AND e.status = 1
        ORDER BY e.first_name
    ";

    return $controller->EmployeeDetails->query($sql);
}
public function getHierarchyBranches($emp_pkey)
{
    $controller = $this->controller;

    $ds = $controller->Session->read('ds');
    $controller->EmployeeDetails->useDbConfig = $ds;

    $sql = "
        SELECT DISTINCT 
            b.branch_code,
            b.branch_name
        FROM branches b
        JOIN emp_details e 
            ON e.branch_code = b.branch_code
        JOIN emp_proff p 
            ON p.emp_fkey = e.emp_pkey
        WHERE p.attr1 = ".$emp_pkey."
        AND b.status = 1
        ORDER BY b.branch_name
    ";

    return $controller->EmployeeDetails->query($sql);
}

public function getBranchesForAll()
{
    $controller = $this->controller;
    $ds = $controller->Session->read('ds');
    $controller->Units->useDbConfig = $ds;

    $sql = "
        SELECT 
            b.branch_code,
            b.branch_name
        FROM branches b
        WHERE b.status = 1
        ORDER BY b.branch_name
    ";

    return $controller->Units->query($sql);
}

public function getAllocatedBranches($user_fkey, $feature_id)
{
    $controller = $this->controller;
    $ds = $controller->Session->read('ds');
    $controller->Units->useDbConfig = $ds;

    $sql = "
        SELECT 
            b.branch_code,
            b.branch_name
        FROM branches b
        WHERE b.status = 1
        AND b.branch_code IN (
            SELECT branch_fkey 
            FROM user_feature_branch_access 
            WHERE user_fkey = '$user_fkey' 
            AND feature_fkey = '$feature_id' 
            AND active = 'Y'
        )
        ORDER BY b.branch_name
    ";

    return $controller->Units->query($sql);
}

// public function getAllEmployeesByBranch($branch_code)
// {
//     $controller = $this->controller;
//     $ds = $controller->Session->read('ds');
//     $controller->EmployeeDetails->useDbConfig = $ds;

//     $sql = "
//         SELECT 
//         e.emp_pkey AS id,
//         CONCAT(e.first_name,' (',p.emp_company_id,')') AS text
//         FROM emp_details e
//         LEFT JOIN emp_proff p ON p.emp_fkey = e.emp_pkey
//         WHERE e.branch_code = '$branch_code'
//         AND e.status = 1
//         ORDER BY e.first_name
//     ";

//     return $controller->EmployeeDetails->query($sql);
// }


public function getAllEmployeesByBranch($branch_code, $exclude_emp_pkey = 0)
{
    $controller = $this->controller;
    $ds = $controller->Session->read('ds');
    $controller->EmployeeDetails->useDbConfig = $ds;

    $excludeCondition = "";
    $params = array($branch_code);

    if (!empty($exclude_emp_pkey)) {
        $excludeCondition = " AND e.emp_pkey != ?";
        $params[] = $exclude_emp_pkey;
    }

    $sql = "
        SELECT 
            e.emp_pkey AS id,
            CONCAT(e.first_name,' (',p.emp_company_id,')') AS text
        FROM emp_details e
        LEFT JOIN emp_proff p ON p.emp_fkey = e.emp_pkey
        WHERE p.emp_branch = ?
          AND e.status = 1
          $excludeCondition
        ORDER BY e.first_name
    ";

    return $controller->EmployeeDetails->query($sql, $params);
}
}