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
ini_set("display_errors", 0);

App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class LeaveRequestController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'LeaveRequest';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('AppModel', 'LeaveRequests','EmpLeaveApproval', 'EmployeeConfig','SalaryHeadItems', 'EmployeeDetails', 'EmployeeLeaveTransaction', 'LeavePolicy', 'EmployeeInfo');
    public $components = array('Session');

    private function fixEncoding($str)
    {
        if (empty($str)) return $str;
        if (preg_match('/[ÃÂÄ]/', $str)) {
            $decoded = mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
            if (mb_check_encoding($decoded, 'UTF-8')) {
                return $decoded;
            }
        }
        return $str;
    }

    /*
     * Leave List Landing Page
     */

    public function index() {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $company_code=$this->Session->read('company_code');
        $this->set('company_code',$company_code);
        //My Leaves count
        $leave_count = $this->LeaveRequests->find('count', array('conditions' => array('EMP_fkey' => $cur_emp_key)));
        $this->set('leave_count', $leave_count);
    }

    /*
     * Leave List Landing Page
     */

    public function employeeleaves() {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");

        //Employee Leaves count
        //On 10 Aug 2015
        $emp_leave_count = $this->LeaveRequests->find('count', array(
            'conditions' => array(
                'OR' => array(
                    array('ISAutherizedby' => $cur_emp_key, 'LEAVESTATUS IN("Applied","Authorized")'),
                    array('APPROVEDBY' => $cur_emp_key, 'LEAVESTATUS IN ("Authorized","Approved","Rejected")'),
                )
            )
                )
        );
        $this->set('emp_leave_count', $emp_leave_count);
    }

    /*
     * Employee Leave Lists
     * By santhosh on 21 March 2015
     */
    /* public function listempleaves() {
      $sessionObj = $this -> Session -> read("Auth.User");
      $cur_emp_key = $sessionObj['emp_fkey'];

      $columns = array( array('db' => 'LEAVEENTRYID', 'dt' => 0), array('db' => 'emp_name', 'dt' => 1), array('db' => 'leave_type', 'dt' => 2), array('db' => 'applied_date', 'dt' => 3), array('db' => 'FROMDATE', 'dt' => 4), array('db' => 'TODATE', 'dt' => 5), array('db' => 'LEAVESTATUS', 'dt' => 6));
      $this -> datatable["fields"] = 'LEAVEENTRYID, CONCAT(first_name, " ", last_name) AS emp_name, SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';
      $this -> datatable["joins"] = array( array('table' => 'salary_head_items', 'alias' => 'SalaryHeadItems', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')), array('table' => 'emp_details', 'alias' => 'EmployeeDetails', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey')));
      $this -> datatable["conditions"] = array (
      'OR' => array(
      array('ISAutherizedby' => $cur_emp_key),
      array('APPROVEDBY' => $cur_emp_key),
      )
      );

      $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
      echo json_encode($this -> DataTable -> getData('LeaveRequests', $columns));
      $this -> autoRender = FALSE;
      } */
    /*
     * List employee leave requests
     * Added on 23 April 2015
     */

//    public function getusers() {
//        $this->autoRender = false;
//        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//        $emp_fkey = $this->Session->read('emp_fkey');
//        $arr_request_data = $this->request->query;
////debug($arr_request_data);
//        if (isset($arr_request_data['username'])) {
//            $searchkey = $arr_request_data['username'];
//            $filter_condition = 'first_name LIKE "%' . $searchkey . '%" and emp_pkey != ' . $emp_fkey;
//        } else {
//            $filter_condition = '';
//        }
//        $arr_users = $this->EmployeeDetails->find('all', array(
//            'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
//            'conditions' => array(
//                'status' => 1,
//                $filter_condition
//            )
//                )
//        );  
//        //  debug($arr_users);
//        $arr_filterresult = array();
//        foreach ($arr_users as $val) {
//            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val[0]) : array();
//        }
//        //  debug($arr_filterresult);
//        echo json_encode($arr_filterresult);
//    }
//      public function getusers()
//     {
//         $this->autoRender = false;
//         $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
//         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//         $emp_fkey = $this->Session->read('emp_fkey');
//         $arr_request_data = $this->request->query;
//         //added by megha on 10/06/19 authorised by hierarchy persons only
//         $action = isset($arr_request_data['action']) ? $arr_request_data['action'] : '';
//         $auth_emp = isset($arr_request_data['uname']) ? $arr_request_data['uname'] : '';
//         $arr_company = $this->EmployeeDetails->find(
//             'first',
//             array(
//                 'fields' => 'company_code',
//                 'conditions' => array(
//                     'status' => 1,
//                     'emp_pkey' => $emp_fkey
//                 )
//             )
//         );
//         $company_code = strtolower($arr_company['EmployeeDetails']['company_code']);
//         //added by megha on 5_08_2019 Resignation authorised by list
//         if ($action == '') {
//             $auth = "select `leave_auth_apr_person_fn`('$company_code','$emp_fkey','api') as resps";
//         } else
//             // end Resignation authorised by list
//             $emp_ids1 = 0;
//             if ($action == 'auth') {
//                 $auth = "select `leave_auth_apr_person_fn`('$company_code','$emp_fkey','$action') as resps";
//             } else {
//                 $auth = "select `leave_auth_apr_person_fn`('$company_code','$emp_fkey','$action') as resps";
//                 $auth_per = "select `leave_auth_apr_person_fn`('$company_code','$emp_fkey','auth') as resps";
//                 $row_details1 = $this->EmployeeDetails->query($auth_per);
//                 $emp_ids1 = $row_details1['0']['0']['resps'];
//             }
       
//         $row_details = $this->EmployeeDetails->query($auth);
//         $emp_ids = $row_details['0']['0']['resps'];
//         if (isset($arr_request_data['username'])) {
//             $searchkey = $arr_request_data['username'];
//             $filter_condition = '(first_name LIKE "%' . $searchkey . '%" or last_name LIKE "%' . $searchkey . '%") ';
//         } else {
//             $filter_condition = '';
//         }
// //        if ($emp_ids == '0' && $auth_emp != '') {
// //
// //            $emp_condition = ' emp_pkey != ' . $emp_fkey . ' and emp_pkey in (' . $auth_emp . ')';
// //        } else
//         if ($emp_ids != '0') {
//             $emp_condition = ' emp_pkey != ' . $emp_fkey . ' and emp_pkey in (' . $emp_ids . ')';
//         } else {
//             if ($emp_ids1 != '0') {
//              $emp_condition = ' emp_pkey != ' . $emp_fkey . ' and emp_pkey in (' . $emp_ids1 . ')';   
//             }else{
//             $emp_condition = ' emp_pkey != ' . $emp_fkey;
//             }
//         }
      
//         //   debug('first_name LIKE "%' . $searchkey . '%" and emp_pkey != ' . $emp_fkey. 'and emp_pkey in ('.$emp_ids.')');
//         $arr_users = $this->EmployeeDetails->find(
//             'all',
//             array(
//                 'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
//                 'conditions' => array(
//                     'status' => 1,
//                     $filter_condition, $emp_condition
//                 )
//             )
//         );
//         //added by megha on 10/06/19
//         if ($action == 'apr') {
//             $arr_emp = $this->EmployeeConfig->find(
//                 "all",
//                 array(
//                     'fields' => 'policy_id',
//                     'conditions' => array(
//                         'EmployeeConfig.type' => 'LAPPR',
//                         'EmployeeConfig.emp_fkey' => $emp_fkey,
//                         'EmployeeConfig.status' => '1',
//                     )
//                 )
//             );
           
//             if (count($arr_emp) > 0) {
//                 foreach ($arr_emp as $val) {
//                     if (isset($val['EmployeeConfig']['policy_id'])) {
//                         $arr_employees_to_exclude[] = $val['EmployeeConfig']['policy_id'];
//                     }
//                 }
//                 $arr_users = $this->EmployeeDetails->find(
//                     'all',
//                     array(
//                         'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
//                         'conditions' => array(
//                             'status' => 1,
//                             'emp_pkey' => $arr_employees_to_exclude,
//                             $filter_condition
//                         )
//                     )
//                 );
//             } else {
//                 //   debug('first_name LIKE "%' . $searchkey . '%" and emp_pkey != ' . $emp_fkey. 'and emp_pkey in ('.$emp_ids.')');
//                 $arr_users = $this->EmployeeDetails->find(
//                     'all',
//                     array(
//                         'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
//                         'conditions' => array(
//                             'status' => 1,
//                             $filter_condition, $emp_condition
//                         )
//                     )
//                 );
//             }
//         }
       
//         $arr_filterresult = array();
//         foreach ($arr_users as $val) {
//             $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val[0]) : array();
//         }
//         echo json_encode($arr_filterresult);
//     }

// edited by bindu 15-01-2026
    public function getusers()
{
    $this->autoRender = false;
    $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    $emp_fkey = $this->Session->read('emp_fkey');
    $arr_request_data = $this->request->query;

    $action   = isset($arr_request_data['action']) ? $arr_request_data['action'] : '';
    $auth_emp = isset($arr_request_data['uname']) ? $arr_request_data['uname'] : '';

    $arr_company = $this->EmployeeDetails->find(
        'first',
        array(
            'fields' => 'company_code',
            'conditions' => array(
                'status' => 1,
                'emp_pkey' => $emp_fkey
            )
        )
    );

    $company_code = strtolower($arr_company['EmployeeDetails']['company_code']);

    if ($action == '') {
        $auth = "select leave_auth_apr_person_fn('$company_code','$emp_fkey','api') as resps";
    } else {
        $emp_ids1 = 0;
    }

    if ($action == 'auth') {
        $auth = "select leave_auth_apr_person_fn('$company_code','$emp_fkey','$action') as resps";
    } else {
        $auth = "select leave_auth_apr_person_fn('$company_code','$emp_fkey','$action') as resps";
        $auth_per = "select leave_auth_apr_person_fn('$company_code','$emp_fkey','auth') as resps";
        $row_details1 = $this->EmployeeDetails->query($auth_per);
        $emp_ids1 = $row_details1[0][0]['resps'];
    }

    $row_details = $this->EmployeeDetails->query($auth);
    $emp_ids = $row_details[0][0]['resps'];

    if (isset($arr_request_data['username'])) {
        $searchkey = $arr_request_data['username'];
        $filter_condition = '(first_name LIKE "%' . $searchkey . '%" OR last_name LIKE "%' . $searchkey . '%")';
    } else {
        $filter_condition = '';
    }

    if ($emp_ids != '0') {
        $emp_condition = 'emp_pkey != ' . $emp_fkey . ' AND emp_pkey IN (' . $emp_ids . ')';
    } else {
        if ($emp_ids1 != '0') {
            $emp_condition = 'emp_pkey != ' . $emp_fkey . ' AND emp_pkey IN (' . $emp_ids1 . ')';
        } else {
            $emp_condition = 'emp_pkey != ' . $emp_fkey;
        }
    }

    // ================= MAIN QUERY =================
    $findOptions = array(
        'fields' => array(
            'EmployeeDetails.emp_pkey',
            'CASE
    WHEN EmployeeProff.emp_company_id IS NOT NULL
         AND EmployeeProff.emp_company_id != ""
    THEN TRIM(
        CONCAT(
            IFNULL(EmployeeDetails.first_name, ""),
            " ",
            IFNULL(EmployeeDetails.last_name, ""),
            " - ",
            EmployeeProff.emp_company_id
        )
    )
    ELSE TRIM(
        CONCAT(
            IFNULL(EmployeeDetails.first_name, ""),
            " ",
            IFNULL(EmployeeDetails.last_name, "")
        )
    )
END AS full_name'
        ),
        'joins' => array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProff',
                'type' => 'LEFT',
                'conditions' => array(
                    'EmployeeProff.emp_fkey = EmployeeDetails.emp_pkey'
                )
            )
        ),
        'conditions' => array(
            'EmployeeDetails.status' => 1,
            $filter_condition,
            $emp_condition
        )
    );

    $arr_users = $this->EmployeeDetails->find('all', $findOptions);

    // ================= APR CASE =================
    if ($action == 'apr') {

        $arr_emp = $this->EmployeeConfig->find(
            'all',
            array(
                'fields' => 'policy_id',
                'conditions' => array(
                    'EmployeeConfig.type' => 'LAPPR',
                    'EmployeeConfig.emp_fkey' => $emp_fkey,
                    'EmployeeConfig.status' => 1
                )
            )
        );

        if (count($arr_emp) > 0) {
            foreach ($arr_emp as $val) {
                if (isset($val['EmployeeConfig']['policy_id'])) {
                    $arr_employees_to_exclude[] = $val['EmployeeConfig']['policy_id'];
                }
            }

            $findOptions['conditions'] = array(
                'EmployeeDetails.status' => 1,
                'EmployeeDetails.emp_pkey' => $arr_employees_to_exclude,
                $filter_condition
            );

        } else {
            $findOptions['conditions'] = array(
                'EmployeeDetails.status' => 1,
                $filter_condition,
                $emp_condition
            );
        }

        $arr_users = $this->EmployeeDetails->find('all', $findOptions);
    }

    // ================= FINAL RESULT =================
    $arr_filterresult = array();
    foreach ($arr_users as $val) {
        $arr_filterresult[] = array_merge(
            $val['EmployeeDetails'],
            $val[0]
        );
    }
    foreach ($arr_filterresult as &$row) {
        if (isset($row['full_name'])) $row['full_name'] = $this->fixEncoding($row['full_name']);
    }
    unset($row);
// debug($arr_filterresult);
    echo json_encode($arr_filterresult);
    exit();
}
// edited by bindu 15-01-2026 end

    public function getusers_notify() {
        $this->autoRender = false;
        $arr_request_data = $this->request->query;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if (isset($arr_request_data['username'])) {
            $searchkey = $arr_request_data['username'];
            $filter_condition = 'first_name LIKE "%' . $searchkey . '%" ' ;
        } else {
            $filter_condition = '';
        }
         $arr_users = $this->EmployeeDetails->find('all', array(
            'fields' => 'emp_pkey,concat(first_name," ",ifnull(last_name,"")," ",emp_id) as full_name ',
            'conditions' => array(
                'status' => 1,$filter_condition
            )
                )
        );
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ?  array_merge($val['EmployeeDetails'], $val[0]) : array();
        }
        foreach ($arr_filterresult as &$row) {
            if (isset($row['full_name'])) $row['full_name'] = $this->fixEncoding($row['full_name']);
        }
        unset($row);
        echo json_encode($arr_filterresult);
    }
    public function listempleaves() {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $company_code = strtolower($this->Session->read('company_code'));
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $cur_emp_key = $this->Session->read("emp_fkey");

        $fields = 'leave_days,EMP_fkey,ISAutherizedby,APPROVEDBY,LEAVEENTRYID, CONCAT(first_name, " ", last_name) AS emp_name, SalaryHeadItems.item as leave_type,SalaryHeadItems.salary_head_item_pkey,applied_date,FROMDATE,TODATE,LEAVESTATUS';
        $joins = array(
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey')
            )
        );

        //Include Leave cancellation request on list
        //On 11 March 2017
        /* $conditions  =   array (
          'OR' => array(
          array('ISAutherizedby' => $cur_emp_key,'LEAVESTATUS IN("Applied")'),
          array('APPROVEDBY' => $cur_emp_key,'LEAVESTATUS IN ("Authorized")'),
          )
          ); */
        $conditions = array(
            'OR' => array(
                array('ISAutherizedby' => $cur_emp_key, 'LEAVESTATUS IN("CancellationOfAuthorized","Applied")'),
                array('APPROVEDBY' => $cur_emp_key, 'LEAVESTATUS IN ("Authorized")','APPROVED_date is NULL'),
                array('APPROVEDBY' => $cur_emp_key, 'LEAVESTATUS IN ("CancellationOfApproved")')
            )
        );

        $resp_empleaverequests = array();
        $resp_empleaverequests["rows"] = array();
        $count = $this->LeaveRequests->find("count", array("conditions" => $conditions));

        $arr_empleaverequests = $this->LeaveRequests->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'order'=>array('LEAVEENTRYID DESC'),
            'limit' => intval($limit),
            'offset' => intval($ofst)
        ));

        // debug($arr_empleaverequests);


        foreach ($arr_empleaverequests as $key => $value) {
            $emp = $value["LeaveRequests"]["EMP_fkey"];
            $arr_policy = $this->LeaveRequests->query("select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey = $emp");
            $policy = $arr_policy['0']["emp_proff"]["LEAVEPOLICY_GROUP_ID"];
            $leave_type = $value["SalaryHeadItems"]["salary_head_item_pkey"];
            $arr_leavepolicy = $this->LeaveRequests->query("select first_name,email,emp_pkey from emp_details where emp_pkey = (select sanction_by from leavepolicy where LEAVEPOLICY_GROUP_ID = '$policy' and leval_of_approval = '3' and salary_head_item_fkey = '$leave_type' and status = 1)");
            
            $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveRequests"], $value["SalaryHeadItems"], $value[0]);
            if (isset($resp_empleaverequests["rows"][$key]["emp_name"])) {
                $resp_empleaverequests["rows"][$key]["emp_name"] = $this->fixEncoding($resp_empleaverequests["rows"][$key]["emp_name"]);
            }
            if ($resp_empleaverequests["rows"][$key]['APPROVEDBY'] == $cur_emp_key) {
                 if(count($arr_leavepolicy) > 0){
                    $resp_empleaverequests["rows"][$key]['Action'] = 'Authorize';
                 }else{
                    $resp_empleaverequests["rows"][$key]['Action'] = 'Approve';
                 }
            } else {
                $resp_empleaverequests["rows"][$key]['Action'] = 'Authorize';
            }
        }
        $resp_empleaverequests["total"] = $count;
        echo json_encode($resp_empleaverequests);
    }

    public function listempleavesverified() {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $cur_emp_key = $this->Session->read("emp_fkey");

        $fields = 'leave_days,ISAutherizedby,APPROVEDBY,LEAVEENTRYID, CONCAT(first_name, " ", last_name) AS emp_name, SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';
        $joins = array(
            array(
                'table' => 'salary_head_items',
                'alias' => 'SalaryHeadItems',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
            ),
            array(
                'table' => 'emp_details',
                'alias' => 'EmployeeDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey')
            )
        );
		// added by megha on 24_08_2019 Cancellation Approved added on approved by array
        $conditions = array(
            'OR' => array(
                array('APPROVEDBY' => $cur_emp_key, 'LEAVESTATUS IN ("Approved","Authorized","Rejected","Cancellation Approved")','APPROVED_date is NOT NULL'),
                array('ISAutherizedby' => $cur_emp_key, 'LEAVESTATUS IN("Authorized","Rejected","Approved","Cancellation Authorized","Cancellation Approved")')
            )
        );

        $resp_empleaverequests = array();
        $resp_empleaverequests["rows"] = array();
        $count = $this->LeaveRequests->find("count", array("conditions" => $conditions));

        $arr_empleaverequests = $this->LeaveRequests->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'order'=>array('LEAVEENTRYID DESC'),
            'limit' => intval($limit),
            'offset' => intval($ofst)
        ));
        foreach ($arr_empleaverequests as $key => $value) {
            $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveRequests"], $value["SalaryHeadItems"], $value[0]);
            if (isset($resp_empleaverequests["rows"][$key]["emp_name"])) {
                $resp_empleaverequests["rows"][$key]["emp_name"] = $this->fixEncoding($resp_empleaverequests["rows"][$key]["emp_name"]);
            }
            if ($resp_empleaverequests["rows"][$key]['APPROVEDBY'] == $cur_emp_key) {
                $resp_empleaverequests["rows"][$key]['Action'] = 'Approve';
            } else {
                $resp_empleaverequests["rows"][$key]['Action'] = 'Authorize';
            }
        }
        $resp_empleaverequests["total"] = $count;
        echo json_encode($resp_empleaverequests);
    }

    /*
     * Manage Employee Leave
     * By santhosh on 21 March 2015
     */
    public function manageempleave($leaveentryId = 0) {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeInfo->useDbConfig = $this->Session->read('ds');
        $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        if ($leaveentryId) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $cur_user_name = $this->Session->read("user_name");
            $this->set('curuserid', $cur_emp_key);
            $this->set('curusername', $cur_user_name);
            $this->set('leaveentryId', $leaveentryId);
            $company_code = strtolower($this->Session->read('company_code'));
            $this->set('company_code', $company_code);
            if ($this->checkIfLeaveRequestEditable($leaveentryId)) {
                
                try{
                //Update mode
                    $this->set('mode', 'edit');

                    $this->set('arr_reportingemployees', $this->getReportingEmployeeList(1, $leaveentryId));
		    //Fetch leave balance : On 21 Feb 2016
                    
                    $arr_leave_details = $this->LeaveRequests->find("first", array(
                        'fields' => 'EmployeeInformation.*,TODATE,FROMDATE,ISAutherized,ISAPPROVED,APPROVED_date,ISAutherizedby,APPROVEDBY,salary_head_item_fkey,EMP_fkey,LEAVESTATUS',
                        'conditions' => array('LEAVEENTRYID' => $leaveentryId),
                        'joins' => array(
                            array(
                                'table' => 'employee_info',
                                'alias' => 'EmployeeInformation',
                                'type' => 'LEFT',
                                'foreignKey' => false,
                                'conditions' => array('LeaveRequests.EMP_fkey = EmployeeInformation.emp_pkey')
                            )
                        )
                    ));
                    $fromdate = isset($arr_leave_details['FROMDATE'])? $arr_leave_details['FROMDATE'] :'';
                    
                    $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;
                    $emp_fkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : 0;
                    $authorized = isset($arr_leave_details['LeaveRequests']['ISAutherizedby'])?$arr_leave_details['LeaveRequests']['ISAutherizedby']:0;
                    $authorized_name = $this->EmployeeDetails->find("all",array("conditions"=>array("emp_pkey"=>$authorized)));
                    $this->set("authorized_name",$authorized_name);
                    $arr_leave_details['authorized_name'] = $this->fixEncoding($authorized_name['0']['EmployeeDetails']['first_name'].' '.$authorized_name['0']['EmployeeDetails']['last_name']);
                   // debug($arr_leave_details);
                    //Check if leave is isnegative
                    //On 09 Oct 2016
                    //$arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, date('Y')) AS leave_balance");
                    //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
                    //$this->set('leavebalance',$leavebalance);
                    
                    $arr_leave_policy = $this->LeavePolicy->find("all", array(
                        'fields' => array('ALLOW_NEGETIVE','REMARKS','sanction_by'),
                        'conditions' => array(
                            'salary_head_item_fkey' => $salary_head_item_fkey,
                            'leval_of_approval' => '3',
                            'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $emp_fkey . ')'
                        )
                            )
                    );
                   $sanction = isset($arr_leave_policy[0]['LeavePolicy']['sanction_by'])?$arr_leave_policy[0]['LeavePolicy']['sanction_by']:'0';
                    $this->set("sanction", $sanction);																					   
                    $this->loadEmpLeaveDetails($leaveentryId,$sanction);
                    $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';
                   // $leavetyperemarks = isset($arr_leave_policy[0]['LeavePolicy']['REMARKS']) ? $arr_leave_policy[0]['LeavePolicy']['REMARKS'] : '';
                   
                    $to_date = isset($arr_leave_details['LeaveRequests']['TODATE']) ? $arr_leave_details['LeaveRequests']['TODATE'] : '';
                    $fromdate = isset($arr_leave_details['LeaveRequests']['FROMDATE']) ? $arr_leave_details['LeaveRequests']['FROMDATE'] : '';
                    $empfkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : '';
                    $LEAVESTATUS = isset($arr_leave_details['LeaveRequests']['LEAVESTATUS']) ? $arr_leave_details['LeaveRequests']['LEAVESTATUS'] : '';
                    try {
                        if($LEAVESTATUS == 'CancellationOfApproved' || $LEAVESTATUS == 'CancellationOfAuthorized'){
                    $responsedata = $this->criterias1($empfkey,$fromdate,$to_date);
                        }else{
                            $responsedata = $this->criterias($empfkey,$fromdate,$to_date,$LEAVESTATUS);
                        }
                    $resposemsg = isset($responsedata['message']) ? $responsedata['message'] : '';
                    $response = isset($responsedata['success']) ? $responsedata['success'] : 'false';
                    } catch (Exception $ex) {
                                            
                    $message = 'Try again';
                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'message' => $message));
                    }
                    
                    
                    $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
                     and branch_code= (select branch_code from emp_details where emp_Pkey='$emp_fkey' ) ORDER BY fin_year DESC LIMIT 1  ");
                    $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
                    $to_year = date('Y', strtotime($to_date));
                    $to_month = date('m', strtotime($to_date));
                     $att_startdate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$to_date', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$to_date', '%Y-%m-01'), 2) as monthly_att_todate");  
        $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
        
        if($to_date > $att_enddate1){
        $month = date("Y-m-d", strtotime("+1 month", strtotime($att_enddate1)));
        }else{
         $month =  $att_enddate1;  
        }
        $to_month = date("Y-m",  strtotime($month));
           //edited by athira on 21-09-2025
                    $company_code=$this->Session->read('company_code');
                    $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
                        $leave_days="NULL";
                        $arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, $leave_days) AS leave_balance");
                        $leavebalance = isset($arr_leavebalance[0][0]['leave_balance']) ? $arr_leavebalance[0][0]['leave_balance'] : 0;
                        $this->set('leavebalance', $leavebalance);
                    }
                     else{
                    if ($allow_negative == 'Y') {
                        //Check yearly balance
                        $arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, $finyear) AS leave_balance");
                        $leavebalance = isset($arr_leavebalance[0][0]['leave_balance']) ? $arr_leavebalance[0][0]['leave_balance'] : 0;
                        $this->set('leavebalance', $leavebalance);
                    } else {
                        //Check monthly balance
                        $arr_leavebalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn($emp_fkey,$salary_head_item_fkey, $to_month, $finyear) as leave_balance");
                        $leavebalance = isset($arr_leavebalance[0][0]['leave_balance']) ? $arr_leavebalance[0][0]['leave_balance'] : 0;
                        $this->set('leavebalance', $leavebalance);
                    }
                }
                    //Ends

                    $ISAutherized = $ISAPPROVED = 0;                
                    $myrole = 0;

                    $leave_cancellation_msg = (isset($arr_leave_details['LeaveRequests']['LEAVESTATUS']) && in_array($arr_leave_details['LeaveRequests']['LEAVESTATUS'],array("Cancelled","Cancellation Authorized")))?" Cancellation Request":"";
                    if ($arr_leave_details['LeaveRequests']['ISAutherizedby'] == $cur_emp_key) {
                        $this->set('head', "Authorize Leave$leave_cancellation_msg");
                        if ($arr_leave_details['LeaveRequests']['ISAutherized'] == 1 && !in_array($arr_leave_details['LeaveRequests']['LEAVESTATUS'], array('CancellationOfAuthorized', 'Cancellation Authorized',))) {
                            $ISAutherized = 1;
                        }
                        $myrole = 1;
                    }
                    if ($arr_leave_details['LeaveRequests']['APPROVEDBY'] == $cur_emp_key && $arr_leave_details['LeaveRequests']['APPROVED_date'] == NULL) {
                        $this->set('head', "Approve Leave$leave_cancellation_msg");
                        if ($arr_leave_details['LeaveRequests']['ISAPPROVED'] == 1 && !in_array($arr_leave_details['LeaveRequests']['LEAVESTATUS'], array('CancellationOfAuthorized', 'Cancellation Authorized'))) {
                            $ISAPPROVED = 1;
                        }
                        $myrole = 2;
                    }
                    
                   
                    //else
                    //{
                    //    $button1 = 0;
                    //}
                   // debug($arr_leave_policy);
                    
                    $leavedata = $arr_leave_details;
                    $this->set("leavedata", $leavedata);
                    $this->set("myrole", $myrole);
                    $this->set("ISAutherized", $ISAutherized);
                    $this->set("ISAPPROVED", $ISAPPROVED);
                    $this->set('arr_leave_policy',$arr_leave_policy);
                    $this->set('resposemsg',$resposemsg);
                    $this->set('response',$response);
//                    debug($response);
                }
                catch (Exception $ex) {
                                        
                    $message = 'Failed';
                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'message' => $message));
                }
                //$this->set("rejected",$rejected);
            } else { 
               $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
                    $this->EmployeeInfo->useDbConfig = $this->Session->read('ds');
                    $arr_leave_details = $this->LeaveRequests->find("first", array(
                        'fields' => 'EmployeeInformation.*,TODATE,FROMDATE,ISAutherized,ISAPPROVED,APPROVED_date,ISAutherizedby,APPROVEDBY,salary_head_item_fkey,EMP_fkey,LEAVESTATUS,file_name,file_type',
                        'conditions' => array('LEAVEENTRYID' => $leaveentryId),
                        'joins' => array(
                            array(
                                'table' => 'employee_info',
                                'alias' => 'EmployeeInformation',
                                'type' => 'LEFT',
                                'foreignKey' => false,
                                'conditions' => array('LeaveRequests.EMP_fkey = EmployeeInformation.emp_pkey')
                            )
                        )
                    ));
                    $fromdate = isset($arr_leave_details['FROMDATE'])? $arr_leave_details['FROMDATE'] :'';
                    
                    $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;
                    $emp_fkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : 0;
                    $authorized = isset($arr_leave_details['LeaveRequests']['ISAutherizedby'])?$arr_leave_details['LeaveRequests']['ISAutherizedby']:0;
                    $authorized_name = $this->EmployeeDetails->find("all",array("conditions"=>array("emp_pkey"=>$authorized)));

                     $to_date = isset($arr_leave_details['LeaveRequests']['TODATE']) ? $arr_leave_details['LeaveRequests']['TODATE'] : '';
                    $fromdate = isset($arr_leave_details['LeaveRequests']['FROMDATE']) ? $arr_leave_details['LeaveRequests']['FROMDATE'] : '';
                    $empfkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : '';
                    $LEAVESTATUS = isset($arr_leave_details['LeaveRequests']['LEAVESTATUS']) ? $arr_leave_details['LeaveRequests']['LEAVESTATUS'] : '';
                    $this->set("authorized_name",$authorized_name);
                    $arr_leave_details['authorized_name'] = $this->fixEncoding($authorized_name['0']['EmployeeDetails']['first_name'].' '.$authorized_name['0']['EmployeeDetails']['last_name']);
                     $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
                    $arr_leave_policy = $this->LeavePolicy->find("all", array(
                        'fields' => array('ALLOW_NEGETIVE','REMARKS','sanction_by'),
                        'conditions' => array(
                            'salary_head_item_fkey' => $salary_head_item_fkey,
                            'leval_of_approval' =>'3',
                            'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $emp_fkey . ')'
                        )
                            )
                    );
                   $sanction = isset($arr_leave_policy[0]['LeavePolicy']['sanction_by'])?$arr_leave_policy[0]['LeavePolicy']['sanction_by']:'0';
                    $this->set("sanction", $sanction);	
                 //edited by athira on 21-09-2025  
               
                $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
                     and branch_code= (select branch_code from emp_details where emp_Pkey='$emp_fkey' ) ORDER BY fin_year DESC LIMIT 1  ");
                $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
                $to_year = date('Y', strtotime($to_date));
                $to_month = date('m', strtotime($to_date));
                $att_startdate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$to_date', '%Y-%m-01'), 1) as monthly_att_fromdate");
                $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$to_date', '%Y-%m-01'), 2) as monthly_att_todate");
                $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
                $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];


                if ($to_date > $att_enddate1) {
                    $month = date("Y-m-d", strtotime("+1 month", strtotime($att_enddate1)));
                } else {
                    $month =  $att_enddate1;
                }
                $to_month = date("Y-m",  strtotime($month));
                $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';
                 
                
                    $company_code=$this->Session->read('company_code');
                    $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                    ];
                if (!in_array($company_code, $restricted_companies, true)) {
                        $leave_days="NULL";
                        $arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, $leave_days) AS leave_balance");
                        $leavebalance = isset($arr_leavebalance[0][0]['leave_balance']) ? $arr_leavebalance[0][0]['leave_balance'] : 0;
                        $this->set('leavebalance', $leavebalance);
                     }
                     else{
                 if ($allow_negative == 'Y') {
                     //Check yearly balance
                    $arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, $finyear) AS leave_balance");
                     $leavebalance = isset($arr_leavebalance[0][0]['leave_balance']) ? $arr_leavebalance[0][0]['leave_balance'] : 0;
                     $this->set('leavebalance', $leavebalance);
                 } else {
                     //Check monthly balance
                     $arr_leavebalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn($emp_fkey,$salary_head_item_fkey, $to_month, $finyear) as leave_balance");
                     $leavebalance = isset($arr_leavebalance[0][0]['leave_balance']) ? $arr_leavebalance[0][0]['leave_balance'] : 0;
                     $this->set('leavebalance', $leavebalance);
                 }
                     }
                
                //Ends
              try {                  
                    $this->set('mode', 'view');
                    $this->set('head', 'Leave Details');
                    $this->loadEmpLeaveDetails($leaveentryId,$sanction);
               
              } catch (Exception $ex) {
                     $message = 'Failed';
                    return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'message' => $message));
              
              }
                
            }
              
        }
         $company_code = strtolower($this->Session->read('company_code'));
         $this->set('company_code', $company_code);
            $path = "https://v1.mypayrollmaster.online/leaveupload/" . strtolower($company_code) . "/";
            $this->set('path', $path);
        $this->render('manageempleave');
        
    }

    /*
     * My Leave Lists
     * By santhosh on 19 March 2015
     */
    /* public function listleaves() {
      $sessionObj = $this -> Session -> read("Auth.User");
      $cur_emp_key = $sessionObj['emp_fkey'];

      $columns = array( array('db' => 'LEAVEENTRYID', 'dt' => 0), array('db' => 'leave_type', 'dt' => 1), array('db' => 'applied_date', 'dt' => 2), array('db' => 'FROMDATE', 'dt' => 3), array('db' => 'TODATE', 'dt' => 4), array('db' => 'LEAVESTATUS', 'dt' => 5));
      $this -> datatable["fields"] = 'LEAVEENTRYID,SalaryHeadItems.item as leave_type,applied_date,FROMDATE,TODATE,LEAVESTATUS';
      $this -> datatable["joins"] = array( array('table' => 'salary_head_items', 'alias' => 'SalaryHeadItems', 'type' => 'LEFT', 'foreignKey' => false, 'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')));
      $this -> datatable["conditions"] = array('EMP_fkey' => $cur_emp_key);

      $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
      echo json_encode($this -> DataTable -> getData('LeaveRequests', $columns));
      $this -> autoRender = FALSE;
      } */

    /*
     * My Leave Requests
     * By santhosh on 23 April 2015
     */

    public function getfinyear($date = null) {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where '$date' between start_month and end_month");
        $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
        return $finyear;
    }

    // edited by athira on 02-10-2025
public function getEmployeeDates() {
    $this->autoRender = false; // we’ll return JSON
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
    $emp_fkey = $this->Session->read('emp_fkey'); // or ->getData() if POST

    // Get joining date
    $joining_date = $this->EmployeeDetails->query("
        SELECT joining_date 
        FROM emp_proff 
        WHERE emp_fkey='$emp_fkey'
    ");
    $joining_date = isset($joining_date[0]['emp_proff']['joining_date']) 
        ? $joining_date[0]['emp_proff']['joining_date'] 
        : '';

    // Get termination date
     $termination = $this->EmployeeDetails->query("
        SELECT last_approved_working_date 
        FROM termination LEFT JOIN emp_details ON (emp_details.emp_pkey = termination.emp_fkey)
        WHERE termination.emp_fkey='$emp_fkey'  AND emp_details.status=2
    ");

    $termination_date = !empty($termination[0]['termination']['last_approved_working_date']) 
        ? $termination[0]['termination']['last_approved_working_date'] 
        : null;

    // Return JSON
    echo json_encode([
        'emp_fkey' => $emp_fkey,
        'joining_date' => $joining_date,
        'termination_date' => $termination_date
    ]);
}
// end

    public function listleaves() {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $cur_emp_key = $this->Session->read("emp_fkey");
        try{
                 $fields = 'ISAutherizedby,APPROVEDBY,LEAVEENTRYID,SalaryHeadItems.item as leave_type,applied_date,FROMDATE,FROMHALF,TODATE,TOHALF,LEAVESTATUS,leave_days';
            $joins = array(
                array(
                    'table' => 'salary_head_items',
                    'alias' => 'SalaryHeadItems',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
                )
            );
            $conditions = array('EMP_fkey' => $cur_emp_key);

            $resp_myleaverequests = array();
            $resp_myleaverequests["rows"] = array();
            $count = $this->LeaveRequests->find("count", array("conditions" => $conditions));

            $arr_myleaverequests = $this->LeaveRequests->find("all", array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions,
                'order'=>array('LEAVEENTRYID DESC'),
                'limit' => intval($limit),
                'offset' => intval($ofst)
            ));
        } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Leaves can't be Listed, Please try again";
                return json_encode($resp);
        }
       
        foreach ($arr_myleaverequests as $key => $value) {
            $resp_myleaverequests["rows"][$key] = array_merge($value["LeaveRequests"], $value["SalaryHeadItems"]);

            if ($resp_myleaverequests["rows"][$key]['FROMHALF'] == '1') {
                $resp_myleaverequests["rows"][$key]['FROMHALF'] = 'First Half';
            } else if ($resp_myleaverequests["rows"][$key]['FROMHALF'] == '2') {
                $resp_myleaverequests["rows"][$key]['FROMHALF'] = 'Second Half';
            } else {
                $resp_myleaverequests["rows"][$key]['FROMHALF'] = '--';
            }

            if ($resp_myleaverequests["rows"][$key]['TOHALF'] == '1') {
                $resp_myleaverequests["rows"][$key]['TOHALF'] = 'First Half';
            } else if ($resp_myleaverequests["rows"][$key]['TOHALF'] == '2') {
                $resp_myleaverequests["rows"][$key]['TOHALF'] = 'Second Half';
            } else {
                $resp_myleaverequests["rows"][$key]['TOHALF'] = '--';
            }
        }
        $resp_myleaverequests["total"] = $count;
        echo json_encode($resp_myleaverequests);
    }

     public function addeditleave($leaveentryId = 0) {
        $emp_fkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');
       
         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
         $arr_mobile = $this->EmployeeDetails->query('select mobile_no ,classification
             from emp_details EmployeeDetails where status = 1 and emp_pkey = '.$emp_fkey);
        if ($leaveentryId) {
            //Edit leave
            try{
                $this->set('head', 'Edit Leave');
                $this->set('leaveentryId', $leaveentryId);       
                $this->loadLeaveDetails($leaveentryId);
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Leave Editing Failed, Please try again";
                return json_encode($resp);
            }
            
        } else {
            //Request leave
            try{
                $this->set('head', 'Apply Leave');
                $this->set('leaveentryId', 0);
                $arr_leave_details = array();
                $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
                $arr_leaverequestkeys = array_keys($this->LeaveRequests->schema());
                foreach ($arr_leaverequestkeys as $key) {
                    $arr_leave_details[$key] = '';
                }
               //added new section for MBCET College  
                
               
       // if($company_code == 'MBCT'){
        $auth = "select `leave_auth_apr_person_fn`('$company_code','$emp_fkey','auth') as resps";
        $row_details = $this->EmployeeDetails->query($auth);
        $emp_ids = $row_details['0']['0']['resps'];
   
        if ($emp_ids != '0' && $emp_ids != null) {
           $emp_condition = ' and emp_pkey != ' . $emp_fkey.' and emp_pkey in ('.$emp_ids.')';
        } else {
           $emp_condition = ' and emp_pkey != ' . $emp_fkey.' ';
        }   
    
         $arr_users2 = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_id,mobile_no 
             from emp_details EmployeeDetails where status = 1 '.$emp_condition);
        
         $arr_leave_details['contact_No'] = isset($arr_mobile['0']['EmployeeDetails']['mobile_no'])?$arr_mobile['0']['EmployeeDetails']['mobile_no']:'';
      
       if(count($arr_users2) == 1){
         $arr_leave_details['AUTHORIZEDBYNAME'] = $arr_users2['0']['EmployeeDetails']['first_name'].' '.$arr_users2['0']['EmployeeDetails']['last_name'].' '.$arr_users2['0']['EmployeeDetails']['emp_id'];
         $auth_emp = $arr_leave_details['ISAutherizedby'] = $arr_users2['0']['EmployeeDetails']['emp_pkey'];
         
//         $apr = "select `leave_auth_apr_person_fn`('$company_code','$auth_emp','apr') as resps"; 
//         $row_details = $this->EmployeeDetails->query($apr);
//         $emp_ids = $row_details['0']['0']['resps'];
//         if ($emp_ids != '0') {
//           $emp_condition = ' and emp_pkey != ' . $emp_fkey.' and emp_pkey in ('.$emp_ids.')';
//         } else {
//           $emp_condition = ' and emp_pkey = ' . $auth_emp.' ';
//         }  
//         $arr_users1 = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name 
//             from emp_details EmployeeDetails where status = 1 '.$emp_condition);
//         if(count($arr_users1) == 1){
//         $arr_leave_details['APPROVEDBYNAME'] = $arr_users1['0']['EmployeeDetails']['first_name'].' '.$arr_users1['0']['EmployeeDetails']['last_name'];
//         $arr_leave_details['APPROVEDBY'] = $arr_users1['0']['EmployeeDetails']['emp_pkey'];
//          }
         }
        //  if ($action == 'apr'){
        $arr_employees_to_exclude = array();
         $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
        $arr_emp = $this->EmployeeConfig->find("all", 
            array(
                'fields' => 'policy_id', 
                'conditions' => array(
                    'EmployeeConfig.type' => 'LAPPR',
                    'EmployeeConfig.emp_fkey'=>$emp_fkey,
                    'EmployeeConfig.status' => 1
                )
            )
        );
        foreach ($arr_emp as $val){
            if(isset($val['EmployeeConfig']['policy_id'])){
                $arr_employees_to_exclude[] = $val['EmployeeConfig']['policy_id'];
            }            
        }
        $arr_users1 = $this->EmployeeDetails->find('all', array(
            'fields' => 'emp_pkey,first_name,last_name,emp_id,concat(first_name," ",last_name," ",emp_id) as full_name ',
            'conditions' => array(
                'status' => 1,
                'emp_pkey'=> $arr_employees_to_exclude
            )
          )
        );
        if(count($arr_users1) == 1){
         $arr_leave_details['APPROVEDBYNAME'] = $arr_users1['0']['EmployeeDetails']['first_name'].' '.$arr_users1['0']['EmployeeDetails']['last_name'].' '.$arr_users1['0']['EmployeeDetails']['emp_id'];
         $arr_leave_details['APPROVEDBY'] = $arr_users1['0']['EmployeeDetails']['emp_pkey'];
          }
            // $arr_employees = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
            //                                                     WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'LAPPR' 
            //                                                                                 AND status = 1 AND emp_fkey = $emp_fkey)
            //                                                     ");
            //edited by athira on 28-08-2025
                $plan=$this->EmployeeDetails->query("SELECT plan FROM comp_contact_info");
                $plan=$plan[0]['comp_contact_info']['plan'];
                $this->set('plan',$plan);

                if($plan == 'basic'){
                    $arr_employees = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
                                                                WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'HIERARCHY' 
                                                                                            AND status = 1 AND emp_fkey = $emp_fkey)
                                                                ");  
                }
                else{
                 $arr_employees = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
                                                                WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'LAPPR' 
                                                                                            AND status = 1 AND emp_fkey = $emp_fkey)
                                                                ");
                }
                //end
                  $this->set("arr_employees", $arr_employees);
       // }
       // }
       //end
       $this->set('arr_leave_details', $arr_leave_details);
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Leave Requesting Failed, Please try again";
                return json_encode($resp);
            }
            
        }  
        // $this->set('arr_leave_type', $this->getLeaveType());
        //$this->set('arr_reportingemployees', $this->getReportingEmployeeList(0, 0));
        $leaves = $this->getLeaveType();
        $new_leave = array();
        $gender = $arr_mobile['0']['EmployeeDetails']['classification'];
        // debug($gender);
        foreach($leaves as $leave) {
            if($gender == 'male' && $leave['SalaryHeadItems']['salary_head_item_pkey'] != 98) {
                // $new_leave = $leave;
                array_push($new_leave, $leave);
            } else if($gender == 'female' && $leave['SalaryHeadItems']['salary_head_item_pkey'] != 99) {
                // $new_leave = $leave;
                array_push($new_leave, $leave);
            }
        }
        // debug($new_leave);
        $this->set('arr_leave_type', $new_leave);
        $arr_users = $this->LeaveRequests->query("select concat(first_name,' ',ifnull(last_name,'')) as name,emp_pkey,classification  from emp_details where"
                . " status = '1' and emp_pkey not in ('$emp_fkey')  order by name ");
        $this->set("arr_users",$arr_users);
        if($company_code == 'MBCT'){
        $arr_leave_details['Notified'] = isset($arr_users1['0']['EmployeeDetails']['first_name'])?$arr_users1['0']['EmployeeDetails']['first_name'].' '.$arr_users1['0']['EmployeeDetails']['last_name'].' '.$arr_users1['0']['EmployeeDetails']['emp_id']:'';
        $arr_leave_details['Notifiedby'] = isset($arr_users1['0']['EmployeeDetails']['emp_pkey'])?$arr_users1['0']['EmployeeDetails']['emp_pkey']:'';
        }
    }

    /* public function getLeaveType() {
      $this -> autoRender = FALSE;
      $this -> SalaryHeadItems -> useDbConfig = $this -> Session -> read('ds');
      $leave_type = Set::extract('/SalaryHeadItems/.', $this -> SalaryHeadItems -> find("all", array("conditions" => array("head_fkey" => 6, "value" => "Y", "status" => 1))));
      return json_encode($leave_type);
      } */

    public function getLeaveType() {
        $arr_leave_type = array();
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');

        //On 09 Oct 2016
        //$arr_leave_type = $this->SalaryHeadItems->find("all", array("conditions" => array("head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1)")));
        $cur_emp_key = $this->Session->read("emp_fkey");
        $arr_leave_type = $this->SalaryHeadItems->find("all", array(
            "conditions" => array(
                "head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where  LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$cur_emp_key') and leavepolicy.status=1) and item_part = 'Direct'"
            )
        ));

        return $arr_leave_type;
        /* $resp_leavetypes = array();
          foreach ($leave_type as $key => $value) {
          $resp_leavetypes["leavetypes"][$key] = $value;
          }
          return json_encode($resp_leavetypes); */
    }

    /* public function getReportingEmployeeList($showall = 0,$leaveentryId = 0) {
      $appliedBy  =   0;
      if($leaveentryId){
      $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
      $arr_leave_applied_by   =   $this->LeaveRequests->find('first',array("fields" => array("EMP_fkey"),'conditions'=>array('LEAVEENTRYID'=>$leaveentryId)));
      $appliedBy  =   isset($arr_leave_applied_by['LeaveRequests']['EMP_fkey'])?$arr_leave_applied_by['LeaveRequests']['EMP_fkey']:0;
      }
      $this -> autoRender = FALSE;
      $this -> EmployeeDetails -> useDbConfig = $this -> Session -> read('ds');

      $sessionObj = $this -> Session -> read("Auth.User");
      $cur_emp_key = $sessionObj['emp_fkey'];

      if($showall){
      if($appliedBy){
      $arr_conditions =   array('emp_pkey != ' . $appliedBy);
      }else{
      $arr_conditions =   array();
      }
      }else{
      $arr_conditions =   array('emp_pkey != ' . $cur_emp_key);
      }

      $employee_list = Set::extract('/EmployeeDetails/.', $this -> EmployeeDetails -> find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => $arr_conditions)));
      return json_encode($employee_list);
      } */

    public function getReportingEmployeeList($showall = 0, $leaveentryId = 0) {
        //$showall = isset($this->request->query['showall'])?$this->request->query['showall']:0;
        //$leaveentryId = isset($this->request->query['leaveentryId'])?$this->request->query['leaveentryId']:0;
        $appliedBy = 0;
        if ($leaveentryId) {
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
            $arr_leave_applied_by = $this->LeaveRequests->find('first', array("fields" => array("EMP_fkey"), 'conditions' => array('LEAVEENTRYID' => $leaveentryId)));
            $appliedBy = isset($arr_leave_applied_by['LeaveRequests']['EMP_fkey']) ? $arr_leave_applied_by['LeaveRequests']['EMP_fkey'] : 0;
        }
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

        $cur_emp_key = $this->Session->read("emp_fkey");

        if ($showall) {
            if ($appliedBy) {
                $arr_conditions = array('emp_pkey != ' . $appliedBy);
            } else {
                $arr_conditions = array();
            }
        } else {
            $arr_conditions = array('emp_pkey != ' . $cur_emp_key);
        }

        $employee_list = Set::extract('/EmployeeDetails/.', $this->EmployeeDetails->find("all", array("fields" => array("emp_pkey", "emp_name"), "conditions" => $arr_conditions)));

        /* $resp_reportingemployees = array();
          foreach ($employee_list as $key => $value) {
          $resp_reportingemployees["reportingemployees"][$key] = $value;
          } */

        //return json_encode($resp_reportingemployees);
        return $employee_list;
																																														  
    }
															 
    public function loadLeaveDetails($leaveentryId = 0) {
        $arr_leave_details = array();
        if (isset($leaveentryId) && $leaveentryId != 0 && $leaveentryId != '') {
            $arr_leave_details = array();
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
            
            //edited by athira on 23-05-2026

            $arr_leave_details = $this->LeaveRequests->find(
            'first',
            array(
                'fields' => '
                    LeaveRequests.*,

                    (
                        SELECT CONCAT(
                            IFNULL(first_name, ""),
                            " ",
                            IFNULL(last_name, "")
                        )
                        FROM emp_details
                        WHERE emp_pkey = LeaveRequests.ISAutherizedby
                    ) AS AUTHORIZEDBYNAME,

                    (
                        SELECT CONCAT(
                            IFNULL(first_name, ""),
                            " ",
                            IFNULL(last_name, "")
                        )
                        FROM emp_details
                        WHERE emp_pkey = LeaveRequests.APPROVEDBY
                    ) AS APPROVEDBYNAME
                ',
                'conditions' => array(
                    'LEAVEENTRYID' => $leaveentryId
                )
            )
        );

            //edited by athira on 23-05-2026
            $arr_leave_details = array_merge($arr_leave_details['LeaveRequests'], $arr_leave_details[0]);
            $leave = $arr_leave_details['salary_head_item_fkey'];
            $arr_leave = $this->LeaveRequests->query("select salary_head_items.item,salary_head_items.salary_head_item_pkey from salary_head_items where salary_head_item_pkey = $leave");
//            debug($arr_leave);
            $remarkkey = $arr_leave['0']['salary_head_items']['salary_head_item_pkey'];
//            debug($remarkkey);
            $arr_remarks = $this->LeaveRequests->query(" select REMARKS from leavepolicy where leavepolicy.salary_head_item_fkey = $remarkkey ");
//           debug($arr_remarks);
            /* if(isset($arr_leave_details['FROMDATE'])){
              $arr_fromdate   =   explode(' ', $arr_leave_details['FROMDATE']);
              $arr_leave_details['FROMDATE']  =   isset($arr_fromdate[0])?$arr_fromdate[0]:'';
              $arr_leave_details['FROMTIME']  =   isset($arr_fromdate[1])?substr($arr_fromdate[1], 0, strrpos( $arr_fromdate[1], ':')):'';
              }
              if(isset($arr_leave_details['TODATE'])){
              $arr_todate   =   explode(' ', $arr_leave_details['TODATE']);
              $arr_leave_details['TODATE']  =   isset($arr_todate[0])?$arr_todate[0]:'';
              $arr_leave_details['TOTIME']  =   isset($arr_todate[1])?substr($arr_todate[1], 0, strrpos( $arr_todate[1], ':')):'';
              } */
            //return json_encode($arr_leave_details);
            //return json_encode(array('success'=>true,'leaveentryId'=>$leaveentryId,'data'=>$arr_leave_details));
        } else {
            //return json_encode(array('success'=>false,'data'=>array()));
        }
        $this->set('arr_leave_details', $arr_leave_details);
        $this->set('arr_leave', $arr_leave);
        $this->set('arr_remarks', $arr_remarks);
        $company_code = strtolower($this->Session->read('company_code'));
        $this->set('company_code', $company_code); 
        $path = "https://v1.mypayrollmaster.online/leaveupload/" . strtolower($company_code) . "/";
        $this->set('path', $path);
    }

    public function loadEmpLeaveDetails($leaveentryId = 0,$sanction = 0) {
        if (isset($leaveentryId) && $leaveentryId != 0 && $leaveentryId != '') {
            $arr_leave_details = array();
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $arr_leave_details = $this->LeaveRequests->find('first', array(
                'fields' => array('EmployeeInformation.*','SalaryHeadItems.salary_head_item_pkey','leavepolicy.REMARKS','LeaveRequests.*', 'CONCAT(first_name, " ", last_name) AS emp_name', '(SELECT CONCAT(first_name, " ", last_name) FROM emp_details WHERE emp_pkey=APPROVEDBY) AS APPROVEDBYNAME', 'SalaryHeadItems.item as leave_type'),
                'joins' => array(
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'conditions' => array(
                            'LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'salary_head_items',
                        'alias' => 'SalaryHeadItems',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
                    ),
                     array(
                        'table' => 'leavepolicy',
                        'alias' => 'leavepolicy',
                        'type' => 'LEFT',
                       
                        'conditions' => array('leavepolicy.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
                    ),
                    array(
                            'table' => 'employee_info',
                            'alias' => 'EmployeeInformation',
                            'type' => 'LEFT',
                            'foreignKey' => false,
                            'conditions' => array('LeaveRequests.EMP_fkey = EmployeeInformation.emp_pkey')
                        )
                ),
                'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                    )
            );
          //debug($arr_leave_details);
            $remarks = isset($arr_leave_details['leavepolicy']['REMARKS']) ? $arr_leave_details['leavepolicy']['REMARKS'] : '';
            $emp_name = isset($arr_leave_details[0]['emp_name']) ? $this->fixEncoding($arr_leave_details[0]['emp_name']) : '';
            $leave_type = isset($arr_leave_details['SalaryHeadItems']['leave_type']) ? $arr_leave_details['SalaryHeadItems']['leave_type'] : '';
            $approvedby = isset($arr_leave_details[0]['APPROVEDBYNAME']) ? $this->fixEncoding($arr_leave_details[0]['APPROVEDBYNAME']) : '';
            //debug($arr_leave_details);die();
            $authorized = isset($arr_leave_details['LeaveRequests']['ISAutherizedby'])?$arr_leave_details['LeaveRequests']['ISAutherizedby']:0;
            $authorized_name = $this->EmployeeDetails->find("all",array("conditions"=>array("emp_pkey"=>$authorized)));

            $arr_emp_infos = array();
            $arr_emp_infos['branch'] = $arr_leave_details['EmployeeInformation']['branch'];
            $arr_emp_infos['department'] = $arr_leave_details['EmployeeInformation']['department'];
            $arr_emp_infos['employee_id'] = $arr_leave_details['EmployeeInformation']['employee_id'];
            $arr_emp_infos['designation'] = $arr_leave_details['EmployeeInformation']['designation'];
            $arr_leave_details = $arr_leave_details['LeaveRequests'];
            $arr_leave_details['emp_name'] = $emp_name;
            $arr_leave_details['leave_type'] = $leave_type;
            $arr_leave_details['APPROVEDBYNAME'] = $approvedby;
            $arr_leave_details['authorized_name'] = $this->fixEncoding($authorized_name['0']['EmployeeDetails']['first_name'].' '.$authorized_name['0']['EmployeeDetails']['last_name']);
            $arr_leave_details['remarks'] = $remarks;
            /* if(isset($arr_leave_details['FROMDATE'])){
              $arr_fromdate   =   explode(' ', $arr_leave_details['FROMDATE']);
              $arr_leave_details['FROMDATE']  =   isset($arr_fromdate[0])?$arr_fromdate[0]:'';
              $arr_leave_details['FROMTIME']  =   isset($arr_fromdate[1])?substr($arr_fromdate[1], 0, strrpos( $arr_fromdate[1], ':')):'';
              }
              if(isset($arr_leave_details['TODATE'])){
              $arr_todate   =   explode(' ', $arr_leave_details['TODATE']);
              $arr_leave_details['TODATE']  =   isset($arr_todate[0])?$arr_todate[0]:'';
              $arr_leave_details['TOTIME']  =   isset($arr_todate[1])?substr($arr_todate[1], 0, strrpos( $arr_todate[1], ':')):'';
              } */
            if (isset($arr_leave_details['FROMHALF'])) {
                if ($arr_leave_details['FROMHALF'] == 1) {
                    $arr_leave_details['FROMHALF'] = 'First Half';
                } else if ($arr_leave_details['FROMHALF'] == 2) {
                    $arr_leave_details['FROMHALF'] = 'Second Half';
                }
            }
            if (isset($arr_leave_details['TOHALF'])) {
                if ($arr_leave_details['TOHALF'] == 1) {
                    $arr_leave_details['TOHALF'] = 'First Half';
                } else if ($arr_leave_details['TOHALF'] == 2) {
                    $arr_leave_details['TOHALF'] = 'Second Half';
                }
            }
            //return json_encode($arr_leave_details);

            $cur_emp_key = $this->Session->read("emp_fkey");
            $arr_leave_details['curuserid'] = $cur_emp_key;
            $arr_leave_details['curusername'] = $this->Session->read("user_name");

            if (isset($arr_leave_details['APPROVEDBY'])) {
                if ($cur_emp_key == $arr_leave_details['APPROVEDBY']) {
//                    if($sanction !=''){
//                      $arr_leave_details['actionType'] = 'Authorize';  
//                    }else{
                    $arr_leave_details['actionType'] = 'Approve';
                   // }
                } else {
                    $arr_leave_details['actionType'] = 'Authorize';
                }
            } else {
                $arr_leave_details['actionType'] = 'Authorize';
            }
            //return json_encode(array('success'=>true,'leaveentryId'=>$leaveentryId,'data'=>$arr_leave_details));
            $this->set('arr_leave_details', $arr_leave_details);
            $this->set('arr_emp_infos', $arr_emp_infos);
           // debug($arr_leave_details);
        }
    }

    public function checkIfLeaveRequestEditable($leaveentryId = 0) {
        $cur_emp_key = $this->Session->read("emp_fkey");
        $arr_form_data = $this->request->data;
        $arr_leave_details = array();
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        try{
            $arr_leave_details = $this->LeaveRequests->find('first', array(
                'fields' => array('LeaveRequests.*', 'CONCAT(first_name, " ", last_name) AS emp_name', '(SELECT CONCAT(first_name, " ", last_name) FROM emp_details WHERE emp_pkey=APPROVEDBY) AS APPROVEDBYNAME', 'SalaryHeadItems.item as leave_type'),
                'joins' => array(
                    array(
                        'table' => 'emp_details',
                        'alias' => 'EmployeeDetails',
                        'type' => 'LEFT',
                        'conditions' => array(
                            'LeaveRequests.EMP_fkey = EmployeeDetails.emp_pkey'
                        )
                    ),
                    array(
                        'table' => 'salary_head_items',
                        'alias' => 'SalaryHeadItems',
                        'type' => 'LEFT',
                        'foreignKey' => false,
                        'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
                    )
                ),
                'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                    )
            );
    //        debug($arr_leave_details['LeaveRequests']);
            $arr_leave_details = $arr_leave_details['LeaveRequests'];
               
            switch ($arr_leave_details['LEAVESTATUS']) {
                case "Approved": 
                         return false;
                         break;
                case "Authorized":
                         if($cur_emp_key == $arr_leave_details['ISAutherizedby']){
                            return false;
                            break;
                        }else{
                            return true;
                            break;
                        }

                case "Rejected":
                        return false;
                        break;
                case "Cancellation Approved":
                        return false;
                        break;
                case "Cancellation Authorized":
                        return false;
                        break;
                case "Applied" :
                        return true;
                        break;
                case "CancellationOfApproved" :
                        return true;
                        break;
                case "CancellationOfAuthorized" :
                        return true;
                        break;
                default:
                       return true;

                    break;
            }
            
        } catch (Exception $ex) {

                $message = 'Edit Or View Mode Loading Failed';
                return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'message' => $message));
                                    }
//        if (($arr_leave_details['LEAVESTATUS'] == 'Approved' || $arr_leave_details['LEAVESTATUS'] == 'Authorized' || $arr_leave_details['LEAVESTATUS'] == 'Rejected' || $arr_leave_details['LEAVESTATUS'] == 'Cancelled') && ($cur_emp_key != $arr_leave_details['APPROVEDBY'])) {
//            return false;
//        } else if($arr_leave_details['LEAVESTATUS'] == 'Authorized' || $arr_leave_details['LEAVESTATUS'] == 'Applied' || $arr_leave_details['LEAVESTATUS'] == 'CancellationOfAuthorized' ) {
//            return true;
//        }
    }
  public function saveDocument() {
        $message = '';
        $resp = array();
        $resp["message"] = '';
        $result = 0;
        $cur_emp_key = $this->Session->read("emp_fkey");
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        
        $leaveentryId = isset($arr_form_data['LEAVEENTRYID']) ? $arr_form_data['LEAVEENTRYID'] : 0;
        //Image upload
            $img = isset($_FILES['image'])?$_FILES['image']:'';
            if($img){
            $companycode = strtolower($this->Session->read('company_code'));
        $target_dir = "/var/www/html/mpm/leaveupload/" . $companycode . "/";
         try {
//            $cwd_path = getcwd() . $dirsep;
//            $file_webroot_path = "expense" . $dirsep . $companycode . $dirsep;
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, TRUE);
            }

            if (!isset($_FILES['image']['error']) || is_array($_FILES['image']['error'])) {
                throw new RuntimeException('Invalid parameters.');
            }
            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('No file sent.');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Exceeded filesize limit.');
                default:
                    throw new RuntimeException('Unknown errors.');
            }

            // You should also check filesize here. 
            if ($_FILES['image']['size'] > 10000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                    $finfo->file($_FILES['image']['tmp_name']), array(
                'jpg' => 'image/jpg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                    ), true
                    )) {
               throw new RuntimeException('Invalid file format.');
            }
                $time=date('Y-m-d h:i:s');
//            $filename = sprintf('%s.%s', sha1_file($_FILES['image']['tmp_name']), $ext);
            $filename = $leaveentryId . '-' .$time. '-' . basename($_FILES["image"]["name"]);
 //edited by sinsiya on 20-03-2025
 $filename = preg_replace("/[^a-zA-Z0-9-_]/", "", $filename); 
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
           // $arr_form_data2["add_file_name"] = "'" . $filename . "'";
            $arr_file = $this->LeaveRequests->find('all',array('conditions'=>array('LEAVEENTRYID'=>$leaveentryId)));
            $getfile = isset($arr_file['0']['LeaveRequests']['file_name'])?$arr_file['0']['LeaveRequests']['file_name']:'';
            $getfiletype = isset($arr_file['0']['LeaveRequests']['file_type'])?$arr_file['0']['LeaveRequests']['file_type']:'';
            $file_type = isset($arr_form_data['filename'])?$arr_form_data['filename']:'';
            //edited by sinsiya on 15-05-2025
            $file_type = preg_replace('/[^a-zA-Z0-9\s._-]/', '', $file_type); 
            //$result = $this->LeaveRequests->updateAll($arr_form_data2, array('LEAVEENTRYID' => $leaveentryId));
              if (!empty($getfile)) {
                $arr_form_data2["file_name"] = "'" . addslashes($getfile . ',' . $filename) . "'";
                $arr_form_data2["file_type"] = "'" . addslashes($getfiletype . ',' . $file_type) . "'";
                 $result = $this->LeaveRequests->updateAll($arr_form_data2, array('LEAVEENTRYID' => $leaveentryId));
            } else {
                $arr_form_data2["file_name"] = "'" . addslashes($filename) . "'";
                $arr_form_data2["file_type"] = "'" . addslashes($file_type) . "'";
                 $result = $this->LeaveRequests->updateAll($arr_form_data2, array('LEAVEENTRYID' => $leaveentryId));
            }
            // if(!empty($getfile)){
            //           $arr_form_data2["file_name"] = "'$getfile,$filename'";
            //           $arr_form_data2["file_type"] = "'$getfiletype,$file_type'";
            //           $result = $this->LeaveRequests->updateAll($arr_form_data2, array('LEAVEENTRYID' => $leaveentryId));
            // }else{
            //           $arr_form_data2["file_name"] = "'$filename'";
            //           $arr_form_data2["file_type"] = "'$file_type'";   
            //           $result = $this->LeaveRequests->updateAll($arr_form_data2, array('LEAVEENTRYID' => $leaveentryId));
            // }
        } catch (RuntimeException $ex) {
           //$message = 'Try again';
             return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'message' => $ex->getMessage()));
        }
            }
            
           if($result){
            
     $resp['message'] = "Document Uploaded Successfully. ";
            $resp["success"] = true; 
            return json_encode($resp); 
  }
  }
  public function saveLeaveEntry($leaveentryId = 0) {
        $message = '';
        $resp = array();
        $resp['warningmessage'] = '';
        $resp["message"] = '';
        $cur_emp_key = $this->Session->read("emp_fkey");
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $arr_form_data = $this->request->data;
        $leaveentryId = isset($arr_form_data['LEAVEENTRYID']) ? $arr_form_data['LEAVEENTRYID'] : 0;
        $leavestatus = isset($arr_form_data['LEAVESTATUS']) ? $arr_form_data['LEAVESTATUS'] : '';
        $data = array();
        $ccto = isset($arr_form_data['Notifiedby'])?$arr_form_data['Notifiedby']:'';
        $cc = isset($arr_form_data['cc'])?implode(",",$arr_form_data['cc']):'';
        if($ccto > 0){
           $cc_toemp = $this->LeaveRequests->query("select email from emp_details where emp_pkey = '$ccto'");
           
           $cc = $ccto.','.$cc;
           $ccmail = $cc_toemp['0']['emp_details']['email'];
        }
        $cc_arr = isset($arr_form_data['cc'])?$arr_form_data['cc']:'';
        if ($leaveentryId) {
             $data['EMP_fkey'] = $cur_emp_key;
        }else{
            $data['applied_date'] = date('Y-m-d');
            $data['EMP_fkey'] = $cur_emp_key;
            $fromdate = $data['FROMDATE'] = $arr_form_data['FROMDATE'];
            $data['FROMHALF'] = $arr_form_data['FROMHALF'];
            $todate = $data['TODATE'] = $arr_form_data['TODATE'];
            $data['TOHALF'] = $arr_form_data['TOHALF'];
            $data['ISAutherizedby'] = isset($arr_form_data['ISAutherizedby']) ? $arr_form_data['ISAutherizedby'] : '';
            $data['APPROVEDBY'] = $arr_form_data['APPROVEDBY'];
            $data['Reason'] = $arr_form_data['Reason'];
            $data['contact_No'] = $arr_form_data['contact_No'];
            $data['contact_person'] = $arr_form_data['contact_person'];
            $data['leave_days'] = $arr_form_data['leave_days'];
            $data['cctome'] = $cc;
            $file_type = isset($arr_form_data['filename'])?$arr_form_data['filename']:'';
            //edited by sinsiya on 15-05-2025
             $file_type = preg_replace('/[^a-zA-Z0-9\s.]/', '', $file_type);
             // edited by athira on 20-05-2026
             $hasPunch = $this->checkAttendancePunches($cur_emp_key, $fromdate, $todate, $data['FROMHALF'], $data['TOHALF']);
            if ($hasPunch) {
                // Determine appropriate message based on which half(s) conflict
                if ($hasPunch == 1) {
                    $msg = "Leave cannot be applied, attendance exists for the first half. Apply leave for next halves";
                } elseif ($hasPunch == 2) {
                    $msg = "Leave cannot be applied, attendance exists for the second half. Apply leave for next halves";
                } else {
                    // full‑day conflict (both halves present)
                    $msg = "Leave cannot be applied, attendance exists for full day";
                }
                $resp["message"] = $msg;
                $resp["success"] = false;
                return json_encode($resp);
            }
            // ended by athira on 20-05-2026
        }
       // debug($arr_form_data);
        $cc_mail = '';
        if($cc_arr){
          foreach ($cc_arr as $val){
           $cc_emp[] = $this->LeaveRequests->query("select email from emp_details where emp_pkey = '$val'");
           //$cc_mail .= $cc_emp['0']['emp_details']['email'].',';
          }
        }
        $fromdate = isset($arr_form_data['FROMDATE'])?$arr_form_data['FROMDATE']:'';
        $todate = isset($arr_form_data['TODATE'])?$arr_form_data['TODATE']:'';
        try{
	//edited by megha on 08_04_2020 attendance register date range change
	$month  = date('m', strtotime($fromdate));
	$year  = date('Y', strtotime($fromdate));
        $month1 = $year.'-'.$month.'-01';
        $att_startdate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$month1', '%Y-%m-01'), 2) as monthly_att_todate");  
        $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
	if (($fromdate >= $att_startdate1) && ($todate <= $att_enddate1)){
        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $count = $arr_attendance_register['0']['0']['cnt'];
        }else if (($fromdate <= $att_enddate1) && ($todate >= $att_enddate1)){
        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$fromdate','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $yearmonth1 = date('Y-m-d', strtotime($fromdate.' + 1 months'));
        $arr_attendance_register1= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth1','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $count = $arr_attendance_register['0']['0']['cnt'] + $arr_attendance_register1['0']['0']['cnt'];
        }else if(($fromdate >= $att_enddate1) && ($todate >= $att_enddate1)){
        $yearmonth = date('Y-m-d', strtotime($att_enddate1.' + 1 months'));
        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $count = $arr_attendance_register['0']['0']['cnt'];
        }else if(($fromdate <= $att_enddate1) && ($todate <= $att_enddate1)){
        $yearmonth = date('Y-m-d', strtotime($att_enddate1));
        $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$yearmonth','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        $count = $arr_attendance_register['0']['0']['cnt'];
        }else{
        $count = 0;    
        }
           //end  attendance register date range change
           // $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$fromdate','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        } catch (Exception $ex) {

        }

       
        if(isset($count) && $count > 0){
            $resp['message'] = "Leave Can not be saved , Attendance Verified for this Month ";
            $resp["success"] = false; 
            return json_encode($resp);
        }  
    
       
          
        if ($leaveentryId) {
            //Edit Leave
            $myLeaveAction = isset($arr_form_data['myLeaveAction']) ? $arr_form_data['myLeaveAction'] : '';
            $data['LEAVEENTRYID'] = $leaveentryId;
            $cancelreason =  isset($arr_form_data['Reason']) ? $arr_form_data['Reason'] : '';
            
            
            if(isset($arr_attendance_register) && $arr_attendance_register['0']['0']['cnt'] != 0){
                $message = 'Leave Can not be Changed, Attendance Verified ';
                $resp["success"] = false;
                $resp["leaveentryId"] = 0;
                $resp['warningmessage'] = '';
                $resp["message"] = "Leave Can not be Cancelled, Attendance Verified. ";
                return json_encode($resp);
            }
            $arr_data = array(
                //'LeaveRequests.salary_head_item_fkey' => "'".$data['salary_head_item_fkey']."'",
//                'LeaveRequests.applied_date' => "'" . $data['applied_date'] . "'",
//                'LeaveRequests.EMP_fkey' => "'" . $data['EMP_fkey'] . "'",
//                'LeaveRequests.FROMDATE' => "'".$data['FROMDATE']."'",
//                'LeaveRequests.FROMHALF' => $data['FROMHALF'],
//                'LeaveRequests.TODATE' => "'".$data['TODATE']."'",
//                'LeaveRequests.TOHALF' => $data['TOHALF'],   
//                'LeaveRequests.ISAutherizedby' => "'" . $data['ISAutherizedby'] . "'",
//                'LeaveRequests.APPROVEDBY' => "'" . $data['APPROVEDBY'] . "'",
//                'LeaveRequests.Reason' => "'" . $data['Reason'] . "'",
//                'LeaveRequests.contact_No' => "'" . $data['contact_No'] . "'",
//                'LeaveRequests.contact_person' => "'" . $data['contact_person'] . "'",
                    //'LeaveRequests.leave_days' => "'".$data['leave_days']."'",                             
            );
            try{
                $arr_leave_message = $this->LeaveRequests->find("first", array(
                    'fields' => 'LEAVESTATUS',
                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                ));
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["leaveentryId"] = 0;
                $resp['warningmessage'] = '';
                $resp["message"] = "Leave Details Saving Failed. Please try again.";
                return json_encode($resp);
            }
          
            if ($myLeaveAction == 'Cancelled') {
                $arr_data['LeaveRequests.LEAVESTATUS'] = "'Cancelled'";
                $message = 'Leave cancellation successfull.';
            } else if ($myLeaveAction == 'Cancellation Applied') {
                if($arr_leave_message['LeaveRequests']['LEAVESTATUS'] == "Authorized"){
                    $arr_data['LeaveRequests.LEAVESTATUS'] = "'CancellationOfAuthorized'";
                }else{
                    $arr_data['LeaveRequests.LEAVESTATUS'] = "'CancellationOfApproved'";
                }
                $message = 'Leave cancellation successfull';
            } else {
                $arr_data['LeaveRequests.LEAVESTATUS'] = "'Applied'";
                $message = 'Leave details updated successfully';
            }
            $arr_data['Reason'] = "'$cancelreason'";
           if($leavestatus == 'Cannot Apply 0 days'){
                $resp["success"] = false;
                $resp["leaveentryId"] = 0;
                $resp['warningmessage'] = '';
                $resp["message"] = "Cannot apply 0 days leave. ";
                return json_encode($resp);
            }
            try{
                $this->LeaveRequests->updateAll(
                    $arr_data, array('LeaveRequests.LEAVEENTRYID' => $leaveentryId)
                );
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["leaveentryId"] = 0;
                $resp['warningmessage'] = '';
                $resp["message"] = "Leave Details Updating Failed. Please try again ";
                return json_encode($resp);
            }
           
            /**
             * Send a leave cancellation request, if the current leave is already authorised / approved
             * On 11 March 2017
             */
            $applied_emps = isset($data['EMP_fkey']) ? $data['EMP_fkey'] : '';
            $applied_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$applied_emps'");
            $applied_leave = $this->LeaveRequests->query("select applied_date,leave_days,ISAutherizedby,APPROVEDBY,APPROVED_date from leaveentries where LEAVEENTRYID = '$leaveentryId'");
             $arr_leave_details1 = $this->LeaveRequests->find("first", array(
                'fields' => 'EmpProff.emp_company_id,EmpProff.LEAVEPOLICY_GROUP_ID,salary_head_items.item,salary_head_items.salary_head_item_pkey,LEAVEENTRYID,ISAutherizedby,applied_date,Autherized_date,Reason,contact_person,message,APPROVEDBY,APPROVED_date,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
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
                )),
                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                ));
                //$outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? $arr_leave_details1['LeaveRequests'] : array();
                $outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? array($arr_leave_details1['LeaveRequests'],$arr_leave_details1['EmpProff'],$arr_leave_details1['salary_head_items']) : array();
                
            if ($myLeaveAction == 'Cancellation Applied') {
                $data['LEAVESTATUS'] ="Cancelled";
                $data['FROMDATE'] = isset($arr_form_data['FROMDATE']) ? $arr_form_data['FROMDATE'] : '';
                $data['TODATE'] = isset($arr_form_data['TODATE']) ? $arr_form_data['TODATE'] : '';
                $data['applied_date'] = isset($applied_leave['0']['leaveentries']['applied_date']) ? $applied_leave['0']['leaveentries']['applied_date'] : '';
                $data['leave_days'] = isset($applied_leave['0']['leaveentries']['leave_days']) ? $applied_leave['0']['leaveentries']['leave_days'] : '';
                //$auth_pkey = isset($data['ISAutherizedby']) ? $data['ISAutherizedby'] : '';

                if (isset($applied_leave['0']['leaveentries']['APPROVED_date'])) {
                $auth_pkey = isset($applied_leave['0']['leaveentries']['APPROVEDBY']) ? $applied_leave['0']['leaveentries']['APPROVEDBY'] : '';
                }else{
                $auth_pkey = isset($applied_leave['0']['leaveentries']['ISAutherizedby']) ? $applied_leave['0']['leaveentries']['ISAutherizedby'] : '';
                }
                $arr_authorized_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$auth_pkey'");
                $approved_pkey = isset($data['APPROVEDBY']) ? $data['APPROVEDBY'] : '';
                //$arr_approved_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$approved_pkey'");
             
//edited by athira on 23-09-2025
  // if (isset($auth_pkey)) {
                if (!empty($auth_pkey) && !empty($arr_authorized_emp)) {
                    //end
                    $outputParameter1['action'] = " Cancellation " ; 
                    $auth_email = array('Email' => $arr_authorized_emp['0']['emp_details']['email'], 'Name' => $arr_authorized_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                    $this->sendauthorizationmail($outputParameter1, $auth_email);
                }
                $out = $this->callLeaveTransactionProcedure($leaveentryId);                
            }else if($myLeaveAction == 'Cancelled') {
                $data['LEAVESTATUS'] ="Cancelled";
                $data['FROMDATE'] = isset($arr_form_data['FROMDATE']) ? $arr_form_data['FROMDATE'] : '';
                $data['TODATE'] = isset($arr_form_data['TODATE']) ? $arr_form_data['TODATE'] : '';
                $data['applied_date'] = isset($applied_leave['0']['leaveentries']['applied_date']) ? $applied_leave['0']['leaveentries']['applied_date'] : '';
                $data['leave_days'] = isset($applied_leave['0']['leaveentries']['leave_days']) ? $applied_leave['0']['leaveentries']['leave_days'] : '';
             
                if (isset($applied_emp['0']['emp_details']['email'])) {
                   $outputParameter1['action'] = " Cancellation " ; 
                    $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $applied_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                    $this->sendauthorizationmail($outputParameter1, $auth_email);
                }
                $out = $this->callLeaveTransactionProcedure($leaveentryId);
            }
            //Ends
        } else {

            $data['message'] = 'Applied';
			
            $data['salary_head_item_fkey'] = $arr_form_data['salary_head_item_fkey'];

            /* $leavefromtimestamp = strtotime($arr_form_data['FROMDATE']." ".$arr_form_data['FROMTIME']);
              $leavetotimestamp = strtotime($arr_form_data['TODATE']." ".$arr_form_data['TOTIME']); */
            $leavefromtimestamp = strtotime($arr_form_data['FROMDATE']);
            $leavetotimestamp = strtotime($arr_form_data['TODATE']);

            $data['FROMDATE'] = date('Y-m-d H:i:s', $leavefromtimestamp);
            $data['FROMHALF'] = $arr_form_data['FROMHALF'];
            $data['TODATE'] = date('Y-m-d H:i:s', $leavetotimestamp);
            $data['TOHALF'] = $arr_form_data['TOHALF'];

            $from_date = date('Y-m-d', $leavefromtimestamp);
            $to_date = date('Y-m-d', $leavetotimestamp);
            //To get monthlybalance,leavetaken//  added by nimisha.. 15/05/2019 // Start
            
            $fromddate = date("Y-m-01",strtotime($arr_form_data['FROMDATE']));
            $todate = date("Y-m-t",strtotime($arr_form_data['TODATE']));
            $leavetype  = $arr_form_data['salary_head_item_fkey'];
            $months = date('m', strtotime($to_date));
//            debug($fromddate);debug($todate);debug($leavetype);debug($months);
            $arr_leave_count_takens = $this->LeaveRequests->query("select sum(cnt) Applied_leaves from (
                                                        SELECT COUNT(*)
                                                        cnt FROM emp_leave_transactions JOIN leaveentries ON (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID ) 
                                                        WHERE emp_leave_transactions.Leavestatus IN ('Applied') and leaveentries.EMP_fkey IN ('$cur_emp_key') and leave_session=3 and emp_leave_transactions.Remarks!='No need Leave'
                                                        and emp_leave_transactions.leave_date BETWEEN '$fromddate' and '$todate' and leaveentries.salary_head_item_fkey IN ('$leavetype') 
                                                        union all
                                                        SELECT COUNT(*)*0.5
                                                        cnt FROM emp_leave_transactions JOIN leaveentries ON (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID ) 
                                                        WHERE emp_leave_transactions.Leavestatus IN ('Applied') and leaveentries.EMP_fkey IN ('$cur_emp_key') and leave_session!=3 and emp_leave_transactions.Remarks!='No need Leave'
                                                        and emp_leave_transactions.leave_date BETWEEN '$fromddate' and '$todate' and leaveentries.salary_head_item_fkey IN ('$leavetype') )cnt");
//            
            
            $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
                                                        and branch_code= (select branch_code from emp_details where emp_Pkey='$cur_emp_key' ) ORDER BY fin_year DESC LIMIT 1  "); // removed this line code - and '$end_date' between start_month and end_month
            $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
               //edited by athira on 21-09-2025
            $company_code=$this->Session->read('company_code');
             $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
		    $monthly_balance =  0;
            }else{
            $lmonthbalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn('$cur_emp_key','$leavetype','$months','$finyear') as LeaveBalancemonth");
            $monthly_balance = isset($lmonthbalance['0']['0']['LeaveBalancemonth']) ? $lmonthbalance['0']['0']['LeaveBalancemonth'] : 0;
            }
            $date1_ts = strtotime($arr_form_data['FROMDATE']);
            $date2_ts = strtotime($arr_form_data['TODATE']);
            $diff = $date2_ts - $date1_ts;

            $dif = round($diff / 86400);
            $leavedays = $dif + 1;
            if($arr_form_data['FROMHALF'] == 1 && $arr_form_data['TOHALF'] == 1){
                $leavedays = $leavedays - 0.5;
            }
            else if($arr_form_data['FROMHALF'] == 2 && $arr_form_data['TOHALF'] == 2){
                $leavedays = $leavedays - 0.5;
            }
            else if($arr_form_data['FROMHALF'] == 2 && $arr_form_data['TOHALF'] == 1){
                $leavedays = $leavedays - 1;
            }
//   $availablebalance = ($monthly_balance > 0)? $monthly_balance - (isset($arr_leave_count_takens['0']['0']['Applied_leaves'])?$arr_leave_count_takens['0']['0']['Applied_leaves']:0) :0;
//        if($availablebalance < $leavedays && $arr_leave_count_takens['0']['0']['Applied_leaves'] != 0.0){
//                $resp["success"] = false;
//                $resp["message"] = "You have already applied ".$arr_leave_count_takens['0']['0']['Applied_leaves']." leaves from your current available balance. You can either cancel previously applied leaves or try with another leave type ";
//                return json_encode($resp);
//            }
            //To get monthlybalance,leavetaken//  added by nimisha.. 15/05/2019 // End
            $count = 0;
            try {
               // $arr_count_leave_exists = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $cur_emp_key)");
                //added by megha on 4/7/19 leave half day condition removed leave session condition added
                //$arr_count_leave_exists = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and leave_session = 3 and LEAVESTATUS in ('Applied','Approved','Authorized','CancellationOfApproved','CancellationOfAuthorized') and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $cur_emp_key)");
                if($arr_form_data['FROMHALF'] == 1){
                $arr_count_leave_exists = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and leave_session in (1,3) and Leavestatus in ('Applied','Approved','Authorized') and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $cur_emp_key)");
                }else{
                $arr_count_leave_exists = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and leave_session in (2,3) and Leavestatus in ('Applied','Approved','Authorized') and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $cur_emp_key)");
                }
                $count = isset($arr_count_leave_exists[0][0]['COUNT']) ? $arr_count_leave_exists[0][0]['COUNT'] : 0;
              
                if ($count == 0) {
                if($arr_form_data['FROMHALF'] == 1 && $arr_form_data['TOHALF'] == 1){
                $arr_count_leave_exists1 = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and leave_session = 1 and Leavestatus in ('Applied','Approved','Authorized') and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $cur_emp_key)");
                $count = isset($arr_count_leave_exists1[0][0]['COUNT']) ? $arr_count_leave_exists1[0][0]['COUNT'] : 0;
                }
                if($arr_form_data['FROMHALF'] == 2 && $arr_form_data['TOHALF'] == 2){
                $arr_count_leave_exists1 = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and leave_session = 2 and Leavestatus in ('Applied','Approved','Authorized') and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $cur_emp_key)");
                $count = isset($arr_count_leave_exists1[0][0]['COUNT']) ? $arr_count_leave_exists1[0][0]['COUNT'] : 0;
                } 
                }
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Unable To Fetch Previous Leaves, Please Try Again";
                return json_encode($resp);
            }
           
            
            if ($count > 0) {
                $resp["success"] = false;
                $resp["leaveentryId"] = 0;
                $resp['warningmessage'] = '';
                $resp["message"] = "Leave already existing in the range $from_date - $to_date. Please remove it, before applying.";
                return json_encode($resp);
            }
//            Ends
// Check For Weekoff---------------------------------------//
//                $arr_checkweekoff = $this->LeaveRequests->query("SELECT count(*) AS COUNT FROM emp_detail_timeattandance WHERE emp_pkey = '$cur_emp_key' and  att_date between '$from_date' and '$to_date' and weekoff= 'WO'");
//                $count = isset($arr_checkweekoff[0][0]['COUNT']) ? $arr_checkweekoff[0][0]['COUNT'] : 0;
//                if ($count > 0) {
//                    $resp["message"] = "Weeekoff already existing in the range of these Leave(s) . Please remove it, before applying.";
//                    $resp["success"] = false;
//                    return json_encode($resp);
//                }
// Check FOR HOLIDAY------------------------------------//
//                $arr_checkholiday = $this->LeaveRequests->query("SELECT count(*) AS COUNT FROM emp_detail_timeattandance WHERE emp_pkey = '$cur_emp_key' and  att_date between '$from_date' and '$to_date' and holiday= 'HO' ");
//                $count = isset($arr_checkholiday[0][0]['COUNT']) ? $arr_checkholiday[0][0]['COUNT'] : 0;
//                if ($count > 0) {
//                    $resp["message"] = "Holiday already existing in the range of these Leave(s) . Please remove it, before applying.";
//                    $resp["success"] = false;
//                    return json_encode($resp);
//                }          
            
            //Image upload
            $img = isset($_FILES['image'])?$_FILES['image']:'';
           
            if($img){
            $companycode = strtolower($this->Session->read('company_code'));
        $target_dir = "/var/www/html/mpm/leaveupload/" . $companycode . "/";
        try {
//            $cwd_path = getcwd() . $dirsep;
//            $file_webroot_path = "expense" . $dirsep . $companycode . $dirsep;
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, TRUE);
            }

            if (!isset($_FILES['image']['error']) || is_array($_FILES['image']['error'])) {
                throw new RuntimeException('Invalid parameters.');
            }
            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('No file sent.');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Exceeded filesize limit.');
                default:
                    throw new RuntimeException('Unknown errors.');
            }

            // You should also check filesize here. 
            if ($_FILES['image']['size'] > 10000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                    $finfo->file($_FILES['image']['tmp_name']), array(
                'jpg' => 'image/jpg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                    ), true
                    )) {
               throw new RuntimeException('Invalid file format.');
            }
                $time=date('Y-m-d h:i:s');
//            $filename = sprintf('%s.%s', sha1_file($_FILES['image']['tmp_name']), $ext);
            $filename = $leaveentryId . '-' .$time. '-' .  basename($_FILES["image"]["name"]);
           //edited by sinsiya on 05-03-2025
           $filename = preg_replace("/[^a-zA-Z0-9-_]/", "", $filename);   
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            $arr_form_data2["file_name"] = "'" . $filename . "'";
            
            // if (count($arr_form_data2) > 0) {
            //$result = $this->LeaveRequests->updateAll($arr_form_data2, array('LEAVEENTRYID' => $leaveentryId));
        //}
        } catch (RuntimeException $ex) {
           //$message = 'Try again';
             return json_encode(array('success' => FALSE, 'error' => $ex->getMessage(), 'message' => $ex->getMessage()));
        }
            }
            //if($result){
                //Apply New Leave
            $data['LEAVEENTRYID'] = 0;
            $this->LeaveRequests->save($data);
            $leaveentryId = $this->LeaveRequests->getLastInsertID();
            $company_code = strtoupper($this->Session->read('company_code'));
            if($company_code == 'HRBL'){
                $compoff_id = isset($arr_form_data['compoff_id'])?$arr_form_data['compoff_id']:'';
                $this->LeaveRequests->query("Update scheduled_break_off set coff_status	='Y' ,leave_entry_id = '$leaveentryId' where id='$compoff_id'");
            }
            $message = 'Leave requisition successfull';
            $arr_form_data2["file_name"] = isset($arr_form_data2["file_name"])?$arr_form_data2["file_name"]:0;
             if (count($arr_form_data2) > 0) {
                 $arr_form_data2["file_type"] = "'" . $file_type . "'";
            $result = $this->LeaveRequests->updateAll($arr_form_data2, array('LEAVEENTRYID' => $leaveentryId));
       }
           // }
            try {
                $out = $this->callLeaveTransactionProcedure($leaveentryId);
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Something Went Wrong On Leave Transaction, Try Again";
                return json_encode($resp);
            }
       
            if (!$out/* !== 'Successfull' */) {
                $arr_leave_message = $this->LeaveRequests->find("first", array(
                    'fields' => 'message',
                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                ));

                if (isset($arr_leave_message["LeaveRequests"]['message']) && $arr_leave_message["LeaveRequests"]['message'] != '') {
                    $resp["warningmessage"] = $arr_leave_message["LeaveRequests"]['message'];
                } else {
                    $resp["message"] = $message;
                }
            }
            try {
                $arr_leave_details1 = $this->LeaveRequests->find("first", array(
                'fields' => 'EmpProff.emp_company_id,EmpProff.LEAVEPOLICY_GROUP_ID,salary_head_items.item,salary_head_items.salary_head_item_pkey,LEAVEENTRYID,ISAutherizedby,applied_date,Reason,contact_person,message,APPROVEDBY,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
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
                )),
                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                ));
               
            } catch (Exception $ex) {
                $resp["success"] = false; 
                $resp["message"] = "Something Went Wrong On Fetching Leave Details";
                return json_encode($resp);
            }
            
            $outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? array($arr_leave_details1['LeaveRequests'],$arr_leave_details1['EmpProff'],$arr_leave_details1['salary_head_items']) : array();
           
            $auth_pkey = $outputParameter1['0']['ISAutherizedby'];
            $approved_pkey = $outputParameter1['0']['APPROVEDBY'];
            $arr_authorized_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$auth_pkey'");
            
            $arr_approved_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$approved_pkey'");
            
            $policy = $outputParameter1['1']['LEAVEPOLICY_GROUP_ID'];
            $leave_type = $outputParameter1['2']['salary_head_item_pkey'];
            $arr_leavepolicy = $this->LeaveRequests->query("select first_name,email,emp_pkey from emp_details where emp_pkey = (select sanction_by from leavepolicy where LEAVEPOLICY_GROUP_ID = '$policy' and  leval_of_approval = 3 and salary_head_item_fkey = '$leave_type' and status = 1)");
            
            $outputParameter1['action'] = " Authorization " ; 
            if($auth_pkey == $approved_pkey){
                if(count($arr_leavepolicy) > 0){
                    $outputParameter1['action'] = " Authorization " ; 
                }else{
                    $outputParameter1['action'] = " Approval " ; 
                }
             $arr_authorized_emp['0']['emp_details']['email'] = $arr_approved_emp['0']['emp_details']['email'];
            }
            $apprvd_email = isset($arr_approved_emp['0']['emp_details']['email'])?$arr_approved_emp['0']['emp_details']['email']:'noreply@mypayrollmaster.online';
            $applied_emps = $outputParameter1['0']['EMP_fkey'];
            $applied_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$applied_emps'");
            $auth_email = array('Email' => $arr_authorized_emp['0']['emp_details']['email'], 'Name' => $arr_authorized_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
            $outputParameter1['cc_email1'] = isset($cc_emp)?$cc_emp:'';
            $outputParameter1['cc_email'] = isset($ccmail)?$ccmail:'';
            if ($arr_authorized_emp['0']['emp_details']['email']) {
                //uncommented by megha on 03_08_2019
                
                $this->sendauthorizationmail($outputParameter1, $auth_email);
            }
        }
      //  debug($arr_authorized_emp['0']['emp_details']['email']);
        $resp["success"] = true;
        $resp["leaveentryId"] = $leaveentryId;
        return json_encode($resp);
    }
    /*
     * Grand Leave
     * On 22 March 2015
     */
    public function criterias($pkey = 0,$fromdate = 0,$todate = 0,$leavestatus=''){
        $this->autoRender = FALSE;

        // check for leave on present day-------------------------//
        // edited by athira on 22-05-2026
        if (empty($leavestatus)) {
        $hasPunch = $this->checkAttendancePunches($pkey, $fromdate, $todate, 1, 2);
        if ($hasPunch) {
            if ($hasPunch == 1) {
                $msg = "Attendance already existing for the first half. Please remove it, before applying.";
            } elseif ($hasPunch == 2) {
                $msg = "Attendance already existing for the second half. Please remove it, before applying.";
            } else {
                $msg = "Attendance already existing for full day. Please remove it, before applying.";
            }
            $resp["message"] = $msg;
            $resp["success"] = false;
            return $resp;
        }
        }

        // ended by athira on 22-05-2026
         
        $att_startdate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$fromdate', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$fromdate', '%Y-%m-01'), 2) as monthly_att_todate");  
        $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
        
        if($fromdate > $att_enddate1){
        $month = date("Y-m-d", strtotime("+1 month", strtotime($att_enddate1)));
        }else{
         $month =  $att_enddate1;  
        }
//check for attendance verified----------------------------//
            $arr_attendance_register= $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$month','%Y-%m') and isdelete='N' and emp_fkey= '$pkey' ");
            if(isset($arr_attendance_register) && $arr_attendance_register['0']['0']['cnt'] != 0){
                    $resp['message'] = "Leave Can not be saved , Attendance Verified for this Month ";
                    $resp["success"] = false; 
                    return $resp;
                } 
// Check For Weekoff---------------------------------------//
//                $arr_checkweekoff = $this->LeaveRequests->query("SELECT count(*) AS COUNT FROM emp_detail_timeattandance WHERE emp_pkey = '$pkey' and  att_date between '$fromdate' and '$todate' and weekoff= 'WO'");
//             
//                $count = isset($arr_checkweekoff[0][0]['COUNT']) ? $arr_checkweekoff[0][0]['COUNT'] : 0;
//                if ($count > 0) {
//                    $resp["message"] = "Weeekoff already existing in the range of these Leave(s) . Please remove it, before applying.";
//                    $resp["success"] = false;
//                    return $resp;
//                }
// Check FOR HOLIDAY------------------------------------//
//                $arr_checkholiday = $this->LeaveRequests->query("SELECT count(*) AS COUNT FROM emp_detail_timeattandance WHERE emp_pkey = '$pkey' and  att_date between '$fromdate' and '$todate' and holiday= 'HO' ");
//                $count = isset($arr_checkholiday[0][0]['COUNT']) ? $arr_checkholiday[0][0]['COUNT'] : 0;
//                if ($count > 0) {
//                    $resp["message"] = "Holiday already existing in the range of these Leave(s) . Please remove it, before applying.";
//                    $resp["success"] = false;
//                    return $resp;
//                }
            } 
            
    public function criterias1($pkey = 0, $fromdate = 0, $todate = 0)
    {
        $this->autoRender = FALSE;
        $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$fromdate', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];

        if ($fromdate > $att_enddate1) {
            $month = date("Y-m-d", strtotime("+1 month", strtotime($att_enddate1)));
        } else {
            $month =  $att_enddate1;
        }
        //check for attendance verified----------------------------//
        $arr_attendance_register = $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$month','%Y-%m') and isdelete='N' and emp_fkey= '$pkey' ");
        if (isset($arr_attendance_register) && $arr_attendance_register['0']['0']['cnt'] != 0) {
            $resp['message'] = "Leave cannot be saved , attendance verified for this month. ";
            $resp["success"] = false;
            return $resp;
        }
    }

     public function grandLeave() {
        $this->autoRender = FALSE;
        $company_code = strtolower($this->Session->read('company_code'));
        $arr_form_data = $this->request->data;
        $actionType = isset($arr_form_data['actionType']) ? $arr_form_data['actionType'] : '';
        $leaveentryId = isset($arr_form_data['LEAVEENTRYID']) ? $arr_form_data['LEAVEENTRYID'] : 0;
        
        $resp = array();

        $cur_emp_key = $this->Session->read("emp_fkey");

        if ($cur_emp_key != 0 && $leaveentryId != 0) {
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');

            $fromdate = isset($arr_form_data['leavefromdate']) ? $arr_form_data['leavefromdate'] : '';
            $todate = isset($arr_form_data['leavetodate']) ? $arr_form_data['leavetodate'] : '';
            $empid = isset($arr_form_data['leaveempid']) ? $arr_form_data['leaveempid'] : '';
            
            try{
                $empkey = $this->LeaveRequests->query("SELECT `emp_pkey` FROM `employee_info` WHERE `employee_id` = '$empid'");
                $pkey = isset($empkey['0']['employee_info']['emp_pkey']) ? $empkey['0']['employee_info']['emp_pkey'] : '';         

            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Leave Details Fetching Wrong. Please try again";
                return json_encode($resp);
            }
            
           try{
//                $arr_leave_details = $this->LeaveRequests->find("first", array(
//                    'fields' => 'salary_head_item_fkey,EMP_fkey,LEAVESTATUS,ISAutherizedby,APPROVEDBY,FROMDATE,TODATE,applied_date,leave_days,message',
//                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
//                ));
                 $arr_leave_details = $this->LeaveRequests->find("first", array(
                'fields' => 'EmpProff.emp_company_id,salary_head_items.item,LEAVEENTRYID,ISAutherizedby,applied_date,Reason,contact_person,message,APPROVEDBY,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
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
                )),
                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                ));
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Leave Details Fetching Wrong. Please try again";
                return json_encode($resp);
            }

            /**
             * Authorize / Approve Leave cancellation request
             * On 11 March 2017
             */
                $leave_status = isset($arr_leave_details['LeaveRequests']['LEAVESTATUS']) ? $arr_leave_details['LeaveRequests']['LEAVESTATUS'] : '';
            
                //Leave balance checking
                $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;
                if($actionType != 'Reject' && $leave_status == 'Applied')
                {
                    $responsedata = $this->criterias($pkey,$fromdate,$todate,$leave_status);
                    if($responsedata != null){
                        $resposemsg = isset($responsedata['message']) ? $responsedata['message'] : '';
                        $response = isset($responsedata['success']) ? $responsedata['success'] : '';
                        $resp["success"] = $response;
                        $resp["message"] = $resposemsg;
                        return json_encode($resp);
                    }
                }
                $this->EmpLeaveApproval->useDbConfig = $this->Session->read('ds');
                $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
                $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
                try{
                    $arr_leave_policy = $this->LeavePolicy->find("all", array(
                    'fields' => 'ALLOW_NEGETIVE',
                    'conditions' => array(
                        'salary_head_item_fkey' => $salary_head_item_fkey,
                        'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $cur_emp_key . ')'
                    )
                        )
                    );
                    $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';                   
                } catch (Exception $ex) {
                    $resp["success"] = false;
                    $resp["message"] = "Something Went Wrong on Leave Balance checking, Please Try Again";
                    return json_encode($resp);
                }
                
                                                if ($actionType != 'Reject' && $actionType != 'Approve Cancellation' && $actionType != 'Authorize Cancellation') {
														
                                                if (strtoupper($allow_negative) == 'Y') {
                                                    //Check if leave balance for year
                                                    $arr_leave_balance = $this->getYearlyLeaveBalanceForAuthOrApproval($leaveentryId);
                                                    $leave_days = isset($arr_leave_balance[0]) ? $arr_leave_balance[0] : 0;
                                                    $yearly_balance = isset($arr_leave_balance[1]) ? $arr_leave_balance[1] : 0;
//                                                    if($leave_status == 'Applied'){
//                                                    if ($yearly_balance - $leave_days < 0) {
//                                                        $resp["success"] = false;
//							$resp["message"] = "Insufficient leave balance for the month, $yearly_balance available";
//                                                        return json_encode($resp);
//                                                    } }else{
						    if ($actionType == 'Approve'){
							$resp["success"] = true;
							$resp["message"] = "Leave Approved";
						    }
                                                  // }
                                                } else {
                                                    //Check if leave balance for month
                                                    //On 24 Sep 2016
                                                    $arr_leave_balance = $this->getLeaveBalanceForAuthOrApproval($leaveentryId);
                                                    $leave_days = isset($arr_leave_balance[0]) ? $arr_leave_balance[0] : 0;
                                                    $monthly_balance = isset($arr_leave_balance[1]) ? $arr_leave_balance[1] : 0;
//                                                    if($leave_status == 'Applied'){
//                                                        if ($monthly_balance - $leave_days < 0) {
//                                                        $resp["success"] = false;$resp["message"] = "Insufficient leave balance for the month, $monthly_balance available";
//                                                        return json_encode($resp);
//                                                               }
//                                                     } else{
						    if ($actionType == 'Approve'){
							$resp["success"] = true;
							$resp["message"] = "Leave Approved";
                                                     }}
						      // }
                                                    }
               
            //Ends
            try{
//                $arr_leave_details1 = $this->LeaveRequests->find("first", array(
//                    'fields' => 'LEAVEENTRYID,ISAutherizedby,applied_date,message,APPROVEDBY,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
//                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
//                ));
                 $arr_leave_details1 = $this->LeaveRequests->find("first", array(
                'fields' => 'EmpProff.emp_company_id,EmpProff.LEAVEPOLICY_GROUP_ID,salary_head_items.item,salary_head_items.salary_head_item_pkey,LEAVEENTRYID,ISAutherizedby,applied_date,Autherized_date,Reason,contact_person,message,APPROVEDBY,APPROVED_date,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
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
                )),
                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                ));
                //$outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? $arr_leave_details1['LeaveRequests'] : array();
                $outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? array($arr_leave_details1['LeaveRequests'],$arr_leave_details1['EmpProff'],$arr_leave_details1['salary_head_items']) : array();
                $auth_date = isset($outputParameter1['0']['Autherized_date']) ? $outputParameter1['0']['Autherized_date'] : '';
                $leave_status = isset($outputParameter1['0']['LEAVESTATUS']) ? $outputParameter1['0']['LEAVESTATUS'] : '';
                $auth_pkey = $outputParameter1['0']['ISAutherizedby'];
                $approved_pkey = $outputParameter1['0']['APPROVEDBY'];
                $leave_type = $arr_leave_details1['salary_head_items']['salary_head_item_pkey'];
                $leave_grp_id = $arr_leave_details1['EmpProff']['LEAVEPOLICY_GROUP_ID'];
                $applied_emps = $outputParameter1['0']['EMP_fkey'];
                $applied_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$applied_emps'");
                $auth_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$auth_pkey'");
                $arr_approved_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$approved_pkey'");
                $arr_leavepolicy_emp = $this->LeaveRequests->query("select first_name,email,emp_pkey from emp_details where emp_pkey = (select sanction_by from leavepolicy where LEAVEPOLICY_GROUP_ID = '$leave_grp_id' and leval_of_approval = '3' and salary_head_item_fkey = '$leave_type' and status = 1)");
                 } catch (Exception $ex) {
                $resp["success"] = FALSE;
                $resp["message"] = "Data Fetching Failed Please try Again";
                return json_encode($resp);
            }
          
            $arr_approvedempmail = '';
            $arr_appliedemp_mail = '';
            $arr_authempmail = '';
            $arr_finalempmail = '';
            $issendappliedmail = false;
            if($applied_emp['0']['emp_details']['email']){
                $issendappliedmail = true;
                $arr_appliedemp_mail = $applied_emp['0']['emp_details']['email'];
            }
            $issendauthmail = false;
            $authemail = isset($auth_emp['0']['emp_details']['email'])?$auth_emp['0']['emp_details']['email']:'';
            if($auth_emp['0']['emp_details']['email']){
                $issendauthmail = true;
                $arr_authempmail = $auth_emp['0']['emp_details']['email'];
            }
            $issendapprovedmail = false;
            $appremail = isset($arr_approved_emp['0']['emp_details']['email'])?$arr_approved_emp['0']['emp_details']['email']:'';
            if($appremail){
                $issendapprovedmail = true;
                $arr_approvedempmail = $arr_approved_emp['0']['emp_details']['email'];
            }
            $issendfinalmail = false;
            $leave_email = isset($arr_leavepolicy_emp['0']['emp_details']['email']) ? $arr_leavepolicy_emp['0']['emp_details']['email']:'';
            if($leave_email != ''){
                $issendfinalmail = true;
                $arr_finalempmail = $arr_leavepolicy_emp['0']['emp_details']['email'];
            }
            $data = array();
            $data['APPROVEDBY'] = $arr_form_data['APPROVEDBY'];
            $curdate = date('Y-m-d');
//            debug($issendfinalmail);
//            debug($auth_date);
         // debug($actionType);die();
//                if($response != false)
//                    {
                      if ($actionType == 'Authorize') 
                        {               
                            $data['LEAVESTATUS'] = "'Authorized'";
                            if ($issendfinalmail) {
                              if($auth_date == ''){
                                 if ($cur_emp_key == $arr_leave_details['LeaveRequests']['APPROVEDBY'] && $cur_emp_key == $arr_leave_details['LeaveRequests']['ISAutherizedby']) {
                          
                                 $outputParameter1['action'] = "Approval";
                                 $outputParameter1['LEAVESTATUS'] = "Authorized"; 
                                 $data['APPROVED_date'] = "'" . $curdate . "'";
                                 $data['ISAPPROVED'] = 1;
                                 $data['Autherized_date'] = "'" . $curdate . "'";
                                 $data['ISAutherized'] = 1;
//                                $comp = $this->EmpLeaveApproval->query("select subdomain,business_name from comp_contact_info");
//                                $arr_id = $this->EmpLeaveApproval->query("SELECT UUID() AS UUID_Value");
//                                $uuid = $arr_id['0']['0']['UUID_Value'];
                                $insertdetails['created_by'] = $username = $this->Session->read('login_user_id');
                                $key = Security::hash(String::uuid(), 'sha512', true);
                                $hash = sha1($username . rand(0, 100));
                                $url = Router::url(array('controller' => 'Site', 'action' => 'approval'), true) . '/' . $company_code . '/' . $key . '#' . $hash;
                                $ms = $url;
                                $ms = wordwrap($ms, 1000);
                                
                                $insertdetails['LEAVEENTRYID'] = $leaveentryId;
                                $insertdetails['sanction_person'] = $arr_leavepolicy_emp['0']['emp_details']['emp_pkey'];
                                $outputParameter1['email_url'] = $ms;
                                $insertdetails['email_url'] = $key;
                                $insertdetails['leave_status'] = "Authorized";
                                $insertdetails['created_by'] = $this->Session->read('login_user_id');
                                $this->EmpLeaveApproval->save($insertdetails);
                                $auth_email = array('Email' => $arr_finalempmail, 'Name' => $arr_leavepolicy_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                
                                $this->sendfinalapprovalmail($outputParameter1, $auth_email);
                                
                              }else{
                                  $outputParameter1['action'] = "Authorize";
                                 $outputParameter1['LEAVESTATUS'] = "Authorized";
                                 $data['Autherized_date'] = "'" . $curdate . "'";
                                 $data['ISAutherized'] = 1;
                                 
                                 $auth_email = array('Email' => $arr_approved_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                if ($issendapprovedmail) {
                                 $this->sendauthorizationmail($outputParameter1, $auth_email);
                                }
                            
                              }
                            }else if($leave_status == 'Authorized'){
                                $outputParameter1['action'] = "Approval";
                                 $outputParameter1['LEAVESTATUS'] = "Authorized"; 
                                 $data['APPROVED_date'] = "'" . $curdate . "'";
                                 $data['ISAPPROVED'] = 1;
                                 $data['Autherized_date'] = "'" . $curdate . "'";
                                 $data['ISAutherized'] = 1;
//                                $comp = $this->EmpLeaveApproval->query("select subdomain,business_name from comp_contact_info");
//                                $arr_id = $this->EmpLeaveApproval->query("SELECT UUID() AS UUID_Value");
//                                $uuid = $arr_id['0']['0']['UUID_Value'];
                                $insertdetails['created_by'] = $username = $this->Session->read('login_user_id');
                                $key = Security::hash(String::uuid(), 'sha512', true);
                                $hash = sha1($username . rand(0, 100));
                                $url = Router::url(array('controller' => 'Site', 'action' => 'approval'), true) . '/' . $company_code . '/' . $key . '#' . $hash;
                                $ms = $url;
                                $ms = wordwrap($ms, 1000);
                                
                                $insertdetails['LEAVEENTRYID'] = $leaveentryId;
                                $insertdetails['sanction_person'] = $arr_leavepolicy_emp['0']['emp_details']['emp_pkey'];
                                $outputParameter1['email_url'] = $ms;
                                $insertdetails['email_url'] = $key;
                                $insertdetails['leave_status'] = "Authorized";
                                $insertdetails['created_by'] = $this->Session->read('login_user_id');
                                $this->EmpLeaveApproval->save($insertdetails);
                                $auth_email = array('Email' => $arr_finalempmail, 'Name' => $arr_leavepolicy_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                
                                $this->sendfinalapprovalmail($outputParameter1, $auth_email);
                            }else{
                            $outputParameter1['action'] = "Authorize";
                            $outputParameter1['LEAVESTATUS'] = "Authorized";
                            $data['APPROVED_date'] = "'" . $curdate . "'";
                            $data['ISAPPROVED'] = 1;
                            $auth_email = array('Email' => $arr_approved_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                            if ($issendapprovedmail) {
                                $this->sendauthorizationmail($outputParameter1, $auth_email);
                            }
                            }
                        }else{
                           $outputParameter1['action'] = "Approval";
                            $outputParameter1['LEAVESTATUS'] = "Authorized";
                            $data['Autherized_date'] = "'" . $curdate . "'";
                            $data['ISAutherized'] = 1;
                            $auth_email = array('Email' => $arr_approved_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                            if ($issendapprovedmail) {
                                $this->sendauthorizationmail($outputParameter1, $auth_email);
                            }  
                        }}
                        else if ($actionType == 'Approve')
                        {    if ($issendfinalmail) {
                            $outputParameter1['action'] = "Approval";
                                 $outputParameter1['LEAVESTATUS'] = "Authorized"; 
                                 $data['APPROVED_date'] = "'" . $curdate . "'";
                                 $data['ISAPPROVED'] = 1;
                                 if ($cur_emp_key == $arr_leave_details['LeaveRequests']['APPROVEDBY'] && $cur_emp_key == $arr_leave_details['LeaveRequests']['ISAutherizedby']) {
                                $data['Autherized_date'] = "'" . $curdate . "'";
                                $data['ISAutherized'] = 1;
                                }
//                                $comp = $this->EmpLeaveApproval->query("select subdomain,business_name from comp_contact_info");
//                                $arr_id = $this->EmpLeaveApproval->query("SELECT UUID() AS UUID_Value");
//                                $uuid = $arr_id['0']['0']['UUID_Value'];
                                $insertdetails['created_by'] = $username = $this->Session->read('login_user_id');
                                $key = Security::hash(String::uuid(), 'sha512', true);
                                $hash = sha1($username . rand(0, 100));
                                $url = Router::url(array('controller' => 'Site', 'action' => 'approval'), true) . '/' . $company_code . '/' . $key . '#' . $hash;
                                $ms = $url;
                                $ms = wordwrap($ms, 1000);
                                
                                $insertdetails['LEAVEENTRYID'] = $leaveentryId;
                                $insertdetails['sanction_person'] = $arr_leavepolicy_emp['0']['emp_details']['emp_pkey'];
                                $outputParameter1['email_url'] = $ms;
                                $insertdetails['email_url'] = $key;
                                $insertdetails['leave_status'] = "Authorized";
                                $insertdetails['created_by'] = $this->Session->read('login_user_id');
                                $this->EmpLeaveApproval->save($insertdetails);
                             //  debug($outputParameter1);
//                                die();
                                $auth_email = array('Email' => $arr_finalempmail, 'Name' => $arr_leavepolicy_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                
                                $this->sendfinalapprovalmail($outputParameter1, $auth_email);
                        }   else{       
                            $data['LEAVESTATUS'] = "'Approved'";
                            $outputParameter1['action'] = "Approved";
                            $outputParameter1['LEAVESTATUS'] = "Approved";
                            $data['APPROVED_date'] = "'" . $curdate . "'";
                            $data['ISAPPROVED'] = 1;
                            if ($cur_emp_key == $arr_leave_details['LeaveRequests']['APPROVEDBY'] && $cur_emp_key == $arr_leave_details['LeaveRequests']['ISAutherizedby']) {
                                $data['Autherized_date'] = "'" . $curdate . "'";
                                $data['ISAutherized'] = 1;
                            }
                            // debug($outputParameter1);
                            if ($issendappliedmail) {
                                $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $applied_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                //   debug($auth_email);
                                $this->sendauthorizationmail($outputParameter1, $auth_email);
                                
                            }
                        }
                        } 
                        else if ($actionType == 'Reject')
                        {   
                            if($leave_status == 'CancellationOfAuthorized'){
                                $actionType = "Cancellation Rejecte";
                                $data['LEAVESTATUS'] = "'Authorized'";
                                $outputParameter1['action'] = "Authorized";
                                $outputParameter1['LEAVESTATUS'] = "Authorized";
                                $data['APPROVED_date'] = "'" . $curdate . "'";
                                $data['ISAPPROVED'] = 1;
                                $data['message'] = '"Leave cancellation Rejected"';
                                if ($issendappliedmail) {
                                    $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                    $this->sendauthorizationmail($outputParameter1, $auth_email);
                                    // Send Cancelation rejected mail
                                }
                            }
                            else if($leave_status == 'CancellationOfApproved'){
                                $actionType = "Cancellation Rejecte";
                                $data['LEAVESTATUS'] = "'Approved'";
                                $outputParameter1['action'] = "Approved";
                                $outputParameter1['LEAVESTATUS'] = "Approved";
                                $data['APPROVED_date'] = "'" . $curdate . "'";
                                $data['ISAPPROVED'] = 1;
                                $data['message'] = '"Leave cancellation Rejected"';
                                
                                if ($issendapprovedmail) {
                                    $auth_email = array('Email' => $arr_approved_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                    $this->sendauthorizationmail($outputParameter1, $auth_email);
                                    // Send Cancelation rejected mail
                                }
                            }else{
                                $data['LEAVESTATUS'] = "'Rejected'";
                                if($leave_status =="Applied"){
                                $data['Autherized_date'] = "'" . $curdate . "'";
                                }else{
                                $data['APPROVED_date'] = "'" . $curdate . "'";    
                                }
                                //edite by megha on 30/11/2019 leave detailed report
                                //$appby = isset($arr_form_data['APPROVEDBY']) ? $arr_form_data['APPROVEDBY'] : '';
                                if($arr_leave_details1['LeaveRequests']['ISAutherizedby'] == $arr_leave_details1['LeaveRequests']['APPROVEDBY']){
                                 $data['APPROVED_date'] = "'" . $curdate . "'";   
                                }
                                //end
                                $outputParameter1['action'] = "Rejected";
                                $outputParameter1['LEAVESTATUS'] = "Rejected";
                                if ($issendapprovedmail) {
                                    $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $applied_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                   $this->sendauthorizationmail($outputParameter1, $auth_email);
                                }
                            }
                        }
                        else if ($actionType == 'Approve Cancellation')
                        {    
                            if($leave_status == 'Cancellation Approved'){
                                $actionType = "Cancellation Approve";
                                $data['LEAVESTATUS'] = "'Cancellation Approved'";
                                $outputParameter1['action'] = "Cancellation Approved";
                                $outputParameter1['LEAVESTATUS'] = "Cancellation Approved";
                                $data['APPROVED_date'] = "'" . $curdate . "'";
                                $data['ISAPPROVED'] = 1;
                                $data['message'] = '"Leave cancellation Approved"';
                               //debug($applied_emp);
                                if ($issendappliedmail) {
                                    $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $applied_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                    $this->sendauthorizationmail($outputParameter1, $auth_email);
                                    // Send Cancelation rejected mail
                                }
                            }else{
                            $actionType = "Cancellation Approve";
                            $data['LEAVESTATUS'] = "'Cancellation Approved'";
                            $outputParameter1['action'] = "Cancellation Approved";
                            $outputParameter1['LEAVESTATUS'] = "Cancellation Approved";
                            $data['Autherized_date'] = "'" . $curdate . "'";
                            $data['ISAutherized'] = 1;
                            $data['message'] = '"Leave cancellation Approve"';
                            $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $applied_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                           
                            if ($issendapprovedmail) {
                               $this->sendauthorizationmail($outputParameter1, $auth_email);
                            }
                            }
                        }
                        else if ($actionType == 'Authorize Cancellation') 
                        {   
                            if($leave_status == 'Cancellation Authorized'){
                                $actionType = "Cancellation Authorize";
                                $data['LEAVESTATUS'] = "'Cancellation Authorized'";
                                $outputParameter1['action'] = "Cancellation Authorized";
                                $outputParameter1['LEAVESTATUS'] = "Cancellation Authorized";
                                if($cur_emp_key != $arr_leave_details['LeaveRequests']['ISAutherizedby']){
                                $data['APPROVED_date'] = "'" . $curdate . "'";
                                $data['ISAPPROVED'] = 1;
                                }else{
                                 $data['Autherized_date'] = "'" . $curdate . "'";
                            $data['ISAutherized'] = 1;   
                                }
                                $data['message'] = '"Leave Cancellation Authorized"';
//                                if($company_code == 'mbct' || $company_code == 'demo' || $company_code == 'gede'){
//                                    if($cur_emp_key != $arr_leave_details['LeaveRequests']['ISAutherizedby']){
//                                        if ($issendauthmail) {
//                                    $auth_email = array('Email' => $auth_emp['0']['emp_details']['email'], 'Name' => $auth_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
//                                    $this->sendauthorizationmail($outputParameter1, $auth_email);
//                                    // Send Cancelation rejected mail to first authorised person
//                                }
//                                    }
//                                }
                                if ($issendappliedmail) {
                                    $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $applied_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                    $this->sendauthorizationmail($outputParameter1, $auth_email);
                                    // Send Cancelation rejected mail
                                }
                            }else{            
                            $actionType = "Cancellation Authorize";
                            $data['LEAVESTATUS'] = "'Cancellation Authorized'";
                            $outputParameter1['action'] = "Cancellation Authorized";
                            $outputParameter1['LEAVESTATUS'] = "Cancellation Authorized";
                            $data['Autherized_date'] = "'" . $curdate . "'";
                            $data['ISAutherized'] = 1;
                            $data['message'] = '"Leave cancellation Authorized"';
                            $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $applied_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                            if ($issendapprovedmail) {
                                $this->sendauthorizationmail($outputParameter1, $auth_email);
                            }
                            }
                        } 
                        else if ($actionType == 'Cancelled') 
                        {
                            $actionType = "Cancellation Approve";
                            $data['LEAVESTATUS'] = "'Cancelled'";
                            $outputParameter1['action'] = "Cancellation Approved";
                            $outputParameter1['LEAVESTATUS'] = "Cancelled";
                            $data['APPROVED_date'] = "'" . $curdate . "'";
                            $data['ISAPPROVED'] = 1;
                            $data['message'] = '"Leave cancellation Approved"';
                            if ($issendappliedmail) {
                                $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                                $this->sendauthorizationmail($outputParameter1, $auth_email);
                            }
                        }
                        else{
                            $resp["success"] = false;
                            $resp["leaveentryId"] = $leaveentryId;
                            $resp["message"] = 'Leave processing failed';
                            return json_encode($resp);
                        }
//                }           
                
            
            

          
            $apprem = isset($arr_form_data['ApproveRemarks']) ? $arr_form_data['ApproveRemarks'] : 'null';
            $authrem = isset($arr_form_data['AuthoriseRemarks']) ? $arr_form_data['AuthoriseRemarks'] : 'null';
//            debug($authrem);
            $data['ApproveRemarks'] = "'" . $apprem . "'";
            $data['AuthoriseRemarks'] =  "'" . $authrem . "'";
            //try{
                $this->LeaveRequests->updateAll(
                        $data, array('LeaveRequests.LEAVEENTRYID' => $leaveentryId)
                );
                
               // try{
                    $out = $this->callLeaveTransactionProcedure($leaveentryId);
              /*   } catch (Exception $ex) {
                    $resp["success"] = true;
                    $resp["leaveentryId"] = $leaveentryId;
                    $resp["message"] = 'Leave Transaction Failed Please try again ';
                    return json_encode($resp);
                }
                
            } catch (Exception $ex) {
                $resp["success"] = true;
                $resp["leaveentryId"] = $leaveentryId;
                $resp["message"] = 'Leave Update Failed Please try again ';
                return json_encode($resp);
            } */
            
            if($actionType == 'Reject'){
                $actionType = 'rejected';
            }
            
            $resp["success"] = true;
            $resp["leaveentryId"] = $leaveentryId;
            $resp["message"] = 'Leave ' . $actionType . 'd successfully';
            return json_encode($resp);
        }
    }


    public function deleteLeaveRequests() {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        //debug($_REQUEST["ids"]);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = $_REQUEST["ids"];
            //  debug($ar_ids);
            $this->EmployeeLeaveTransaction->query("Delete From emp_leave_transactions WHERE ((`LEAVEENTRYID` = '$ar_ids'))");

            $this->LeaveRequests->deleteAll(
                    array('LeaveRequests.LEAVEENTRYID' => $ar_ids)
            );
            $result['success'] = 1;
        }
        echo json_encode($result);
    }

    /*
     * Show leave days
     * On 03 Aug 2015
     */

    public function GetLeaveBalance($salary_head_item_fkey = "", $end_date = "") {
        
        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        
        $cur_emp_key = $this->Session->read("emp_fkey");
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $year = date('Y', strtotime($end_date));
         //edited by megha on 07/08/2019 passing today date into leave balance month function
        //$months = date('m', strtotime($end_date));
        $att_startdate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$end_date', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$end_date', '%Y-%m-01'), 2) as monthly_att_todate");  
        $att_startdate1 = date("Y-m-d",strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("Y-m-d",strtotime($att_enddate['0']['0']['monthly_att_todate']));
        if ($end_date != '') {
                $months = date("Y-m-d", strtotime($end_date));
            } 
        else $months = date("Y-m-d"); 
        $from_date = $arr_form_data['start_date'];
        $from_half = $arr_form_data['start_sess'];
        $to_half = $arr_form_data['end_sess'];
        $lrule ="Success";
        if ($end_date != '') {
        $lrule = $this->LeaveRequests->query("select leave_rules_fn('$cur_emp_key','$salary_head_item_fkey','','$from_date','$from_half','$end_date','$to_half') as LeaveRule");
        $lrule = isset($lrule['0']['0']['LeaveRule']) ? $lrule['0']['0']['LeaveRule'] : '';
        }
        $from = date("Y-m-01",strtotime($end_date));
        $to_date = date("Y-m-t",strtotime($end_date));
        $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
        and branch_code= (select branch_code from emp_details where emp_Pkey='$cur_emp_key' ) ORDER BY fin_year DESC LIMIT 1  "); // removed this line code - and '$end_date' between start_month and end_month
        $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
        $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$salary_head_item_fkey','$finyear') as LeaveBalance");
        //echo "select leave_balance_inthe_year_fn('$cur_emp_key','$salary_head_item_fkey','$finyear') as LeaveBalance";
	$this->set('lbalance', $lbalance);
        if($end_date >= $att_startdate1 && $end_date <= $att_enddate1){
          $months = $end_date;
       }else{ 
             //edited by megha on 24-07-2025
           $months = date('Y-m', strtotime($end_date . ' + 1 months')) . '-01';
          //$months = $end_date;
       }
  
        //$lbalance1 = $this->LeaveRequests->query("select alloted_leave_forthe_month from leavepolicy where LEAVEPOLICY_GROUP_ID in ( select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey = $cur_emp_key) and salary_head_item_fkey = '$leavebalance' ");
        $lmonthbalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn('$cur_emp_key','$salary_head_item_fkey','$months','$finyear') as LeaveBalancemonth");
        //echo "select leave_balance_inthe_month_fn('$cur_emp_key','$salary_head_item_fkey','$months','$finyear') as LeaveBalancemonth";die();
		$this->set('lmonthbalance', $lmonthbalance);
        $yearly_balance = isset($lbalance['0']['0']['LeaveBalance']) ? $lbalance['0']['0']['LeaveBalance'] : 0;
        $monthly_balance = isset($lmonthbalance['0']['0']['LeaveBalancemonth']) ? $lmonthbalance['0']['0']['LeaveBalancemonth'] : 0;
       
        //Check if leave is isnegative
        //On 09 Oct 2016
        
        
        $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
//        $arr_leave_count_takens = $this->LeavePolicy->query("SELECT COUNT(*) cnt FROM emp_leave_transactions JOIN leaveentries "
//                . "ON (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) WHERE emp_leave_transactions.Leavestatus "
//                . "IN ('Applied','Authorized') and leaveentries.EMP_fkey IN ('$cur_emp_key') and emp_leave_transactions.leave_date "
//                . "BETWEEN '$from' and '$to_date' and leaveentries.salary_head_item_fkey IN ('$salary_head_item_fkey') ");
        
 //        $arr_leave_count_takens = $this->LeavePolicy->query("SELECT SUM(leave_days) cnt FROM emp_leave_transactions JOIN leaveentries ON "
//                . "(leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) WHERE emp_leave_transactions.Leavestatus IN ('Applied','Authorized')"
//                . " and leaveentries.EMP_fkey IN ('$cur_emp_key') and emp_leave_transactions.leave_date BETWEEN '$from' and '$to_date'"
//                . " and leaveentries.salary_head_item_fkey IN ('$salary_head_item_fkey') ");
        // Query Edited - bcz when .5 leave appliy the count taken as 1.  By Nimisha 14/05/2019
        
        $arr_leave_count_takens = $this->LeavePolicy->query("select sum(cnt) Applied_leaves from (
                                                        SELECT COUNT(*)
                                                        cnt FROM emp_leave_transactions JOIN leaveentries ON (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID ) 
                                                        WHERE emp_leave_transactions.Leavestatus IN ('Applied') and leaveentries.EMP_fkey IN ('$cur_emp_key') and leave_session=3 and emp_leave_transactions .Remarks!='No need Leave'
                                                        and emp_leave_transactions.leave_date BETWEEN '$from' and '$to_date' and leaveentries.salary_head_item_fkey IN ('$salary_head_item_fkey') 
                                                        union all
                                                        SELECT COUNT(*)*0.5
                                                        cnt FROM emp_leave_transactions JOIN leaveentries ON (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID ) 
                                                        WHERE emp_leave_transactions.Leavestatus IN ('Applied') and leaveentries.EMP_fkey IN ('$cur_emp_key') and leave_session!=3 and emp_leave_transactions .Remarks!='No need Leave'
                                                        and emp_leave_transactions.leave_date BETWEEN '$from' and '$to_date' and leaveentries.salary_head_item_fkey IN ('$salary_head_item_fkey') )cnt");
        $arr_leave_policy = $this->LeavePolicy->find("all", array(
            'fields' => 'ALLOW_NEGETIVE,REMARKS,leave_policy_type',
            'conditions' => array(
                'salary_head_item_fkey' => $salary_head_item_fkey,
                'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $cur_emp_key . ')'
            )
                )
        );
        
        //         $end_date = "",$start_date 
//        $leavedays = 0;
//        debug($arr_form_data);       
  
        // Calculating leave Days --- Added by Nimisha 15/05/2019
        $date1_ts = strtotime($arr_form_data['start_date']);
	$date2_ts = strtotime($arr_form_data['end_date']);
	$diff = $date2_ts - $date1_ts;

        $dif = round($diff / 86400);
        $leavedays = $dif + 1;
        if($arr_form_data['start_sess'] == 1 && $arr_form_data['end_sess'] == 1){
            $leavedays = $leavedays - 0.5;
        }
        else if($arr_form_data['start_sess'] == 2 && $arr_form_data['end_sess'] == 2){
            $leavedays = $leavedays - 0.5;
        }
        else if($arr_form_data['start_sess'] == 2 && $arr_form_data['end_sess'] == 1){
            $leavedays = $leavedays - 1;
        }
        
//        else if(){  
//            
//        }
        $leave_policy_type = isset($arr_leave_policy[0]['LeavePolicy']['leave_policy_type']) ? $arr_leave_policy[0]['LeavePolicy']['leave_policy_type'] : '';
       
        $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';
        //Ends
         $leave_policy = $this->LeavePolicy->query("select min_day_before_apply from leavepolicy where salary_head_item_fkey = '$salary_head_item_fkey' and LEAVEPOLICY_GROUP_ID IN 
                   (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$cur_emp_key') and leavepolicy.status=1");
            $planned_leave = isset($leave_policy['0']['leavepolicy']['min_day_before_apply'])?$leave_policy['0']['leavepolicy']['min_day_before_apply']:0;
	    
        $company_code = strtoupper($this->Session->read('company_code'));
       
        if ($company_code == 'MBCT' || $company_code == 'HRBL') {
             $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $arr_salary_head = $this->SalaryHeadItems->find(
            "all",
            array(
                'fields' => 'Occurance',
                'conditions' => array(
                    'salary_head_item_pkey' => $salary_head_item_fkey,
                )
            )
        );
        $head = isset($arr_salary_head['0']['SalaryHeadItems']['Occurance'])?$arr_salary_head['0']['SalaryHeadItems']['Occurance']:'';
        // compo off details by arul on 27-1-23
        $compoff_available_dates = [];
        if(isset($head) && $head == 'COFF') {
            if($leave_policy_type == 'Q'){
                $day = 90;
            }else if($leave_policy_type == 'H'){
                $day = 180;
            }else if($leave_policy_type == 'M'){
                $day = 30;
            }else{
                $day = 365;
            }
            if($leave_policy_type == 'Y'){
            $get_finyear = $this->LeaveRequests->query("select fin_year ,start_month,end_month from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
             and branch_code= (select branch_code from emp_details where emp_Pkey='$cur_emp_key' ) ORDER BY fin_year DESC LIMIT 1  ");
            
            $finyear_start = isset($get_finyear['0']['fin_year']['start_month']) ? $get_finyear['0']['fin_year']['start_month'] : '';
            $finyear_end = isset($get_finyear['0']['fin_year']['end_month']) ? $get_finyear['0']['fin_year']['end_month'] : '';
            }else{
            $finyear_end = date('Y-m-d');  
            $get_finyear = $this->LeaveRequests->query("SELECT DATE_ADD(current_date,INTERVAL - ".$day." DAY) AS DateAdd");
            $finyear_start = isset($get_finyear['0']['0']['DateAdd']) ? $get_finyear['0']['0']['DateAdd'] : '';
           // debug($get_finyear);
            }
            
            $arr_leavelist = $this->EmployeeDetails->query("select id,break_off_date,first_half from scheduled_break_off where emp_fkey = $cur_emp_key and type = 'W' and coff_status = 'N' and status = 1 and break_off_date between '".$finyear_start."' and '".$finyear_end."'  order by first_half ASC");
       //    debug("select id,break_off_date,first_half from scheduled_break_off where emp_fkey = $cur_emp_key and type = 'W' and coff_status = 'N' and status = 1 and break_off_date between '".$finyear_start."' and '".$finyear_end."'");
            if($arr_leavelist) {
                foreach($arr_leavelist as $items) {
                    if(isset($items['scheduled_break_off']['break_off_date'])) {
                        $break_off_id = $items['scheduled_break_off']['id'];
                        $break_off_date = $items['scheduled_break_off']['break_off_date'];
                        $type = ($items['scheduled_break_off']['first_half'] == 'N') ? '1' : '0.5';
                        $compoff_available_dates[] =  [$break_off_date,$type,$break_off_id];
                    }
                }
            }
        }
            if ( $company_code == 'MBCT'){
                 $auth = $this->LeavePolicy->query("select `leave_auth_apr_person_fnmbct`('$company_code','$cur_emp_key','apr','$salary_head_item_fkey') as resps");
           }else{    
                 $auth = $this->LeavePolicy->query("select `leave_auth_apr_person_fn`('$company_code','$cur_emp_key','apr') as resps");
            }
            $notified = $this->LeavePolicy->query("select document_mandatory from leavepolicy where salary_head_item_fkey='$salary_head_item_fkey' and LEAVEPOLICY_GROUP_ID 
	     in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey='$cur_emp_key')and status = 1");
            $apr = isset($auth['0']['0']['resps']) ? $auth['0']['0']['resps'] : '';
            $name = '';
            //$name1 = '';
            //$noti = isset($notified['0']['leavepolicy']['notified_by']) ?  $notified['0']['leavepolicy']['notified_by'] : '';
            $document_mandatory = isset($notified['0']['leavepolicy']['document_mandatory']) ?  $notified['0']['leavepolicy']['document_mandatory'] : '';
            if ($apr) {
                $arr_users1 = $this->EmployeeDetails->find(
                    'all',
                    array(
                        'fields' => 'first_name,last_name,emp_id ',
                        'conditions' => array(
                            'status' => 1,
                            'emp_pkey' => $apr
                        )
                    )
                );
                if (count($arr_users1) == 1) {
                    $name = $arr_users1['0']['EmployeeDetails']['first_name'] . ' ' . $arr_users1['0']['EmployeeDetails']['last_name'] . ' ' . $arr_users1['0']['EmployeeDetails']['emp_id'];
                }
            }
//            if ($noti) {
//                $arr_users2 = $this->EmployeeDetails->find(
//                    'all',
//                    array(
//                        'fields' => 'first_name,last_name,emp_id ',
//                        'conditions' => array(
//                            'status' => 1,
//                            'emp_pkey' => $noti
//                        )
//                    )
//                );
//                if (count($arr_users2) == 1) {
//                    $name1 = $arr_users2['0']['EmployeeDetails']['first_name'] . ' ' . $arr_users2['0']['EmployeeDetails']['last_name'] . ' ' . $arr_users2['0']['EmployeeDetails']['emp_id'];
//                }
//            }

            echo json_encode(array(
                'yearly_balance' => $yearly_balance,
                'APPROVEDBY' => $apr,
                'APPROVEDBYNAME' => $name,
                'PlannedLeave' =>$planned_leave,
                //'NotifiedBY' => $noti,
                //'NotifiedBYNAME' => $name1,
                'document_mandatory' => $document_mandatory,
                //            'monthly_balance' => ($monthly_balance > 0)? $monthly_balance - (isset($arr_leave_count_takens['0']['0']['cnt'])?$arr_leave_count_takens['0']['0']['cnt']:0) :0,
                //            'monthly_taken' => isset($arr_leave_count_takens['0']['0']['cnt'])?$arr_leave_count_takens['0']['0']['cnt']:0,
                //'monthly_balance' => ($monthly_balance > 0)? $monthly_balance - (isset($arr_leave_count_takens['0']['0']['Applied_leaves'])?$arr_leave_count_takens['0']['0']['Applied_leaves']:0) :0,
                'monthly_balance' => $monthly_balance,
                'appliedleaves' => isset($arr_leave_count_takens['0']['0']['Applied_leaves']) ? $arr_leave_count_takens['0']['0']['Applied_leaves'] : 0,
                'allow_negative' => strtoupper($allow_negative),
                'leavedays' => $leavedays,
                'leaverule' => $lrule,
                'remarks' =>  isset($arr_leave_policy['0']['LeavePolicy']['REMARKS']) ? $arr_leave_policy['0']['LeavePolicy']['REMARKS'] : '',
                'compoff_available_dates' => ($compoff_available_dates) ? $compoff_available_dates : ''
           ));
        } else {
            $notified = $this->LeavePolicy->query("select document_mandatory from leavepolicy where salary_head_item_fkey='$salary_head_item_fkey' and LEAVEPOLICY_GROUP_ID 
	     in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey='$cur_emp_key') and status = 1");
//            $apr = isset($auth['0']['0']['resps']) ? $auth['0']['0']['resps'] : '';
//            $name = '';
//            $name1 = '';
            //$noti = isset($notified['0']['leavepolicy']['notified_by']) ?  $notified['0']['leavepolicy']['notified_by'] : '';
            $document_mandatory = isset($notified['0']['leavepolicy']['document_mandatory']) ?  $notified['0']['leavepolicy']['document_mandatory'] : '';
            
            echo json_encode(array(
                'document_mandatory' => $document_mandatory,
                'PlannedLeave' =>$planned_leave,
                'yearly_balance' => $yearly_balance,
                'monthly_balance' => $monthly_balance,
                'appliedleaves' => isset($arr_leave_count_takens['0']['0']['Applied_leaves']) ? $arr_leave_count_takens['0']['0']['Applied_leaves'] : 0,
                'allow_negative' => strtoupper($allow_negative),
                'leavedays' => $leavedays,
                'leaverule' => $lrule,
                'remarks' =>  isset($arr_leave_policy['0']['LeavePolicy']['REMARKS']) ? $arr_leave_policy['0']['LeavePolicy']['REMARKS'] : ''
            ));
        }
    }

    public function showleavedays($leaveentryid = 0) {
        // debug($leaveentryid);
        if ($leaveentryid) {
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
            $this->EmployeeLeaveTransaction->useDbConfig = $this->Session->read('ds');

            /*
             * Show not applied leave status
             * On 09 Oct 2016
             */
            $arr_leave_status = Set::extract('/LeaveRequests/.', $this->LeaveRequests->find("first", array(
                                'fields' => 'LEAVESTATUS,FROMDATE,FROMHALF,TODATE,TOHALF,ApproveRemarks',
                                'conditions' => array('LEAVEENTRYID' => $leaveentryid)
                                    )
                            )
            );
            $this->set('arr_leave_status', $arr_leave_status);
            //Ends

            $fields = 'EmployeeLeaveTransaction.*,LeaveRequests.EMP_fkey,SalaryHeadItems.salary_head_item_pkey,SalaryHeadItems.item as leave_type';
            $joins = array(
                array(
                    'table' => 'leaveentries',
                    'alias' => 'LeaveRequests',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('EmployeeLeaveTransaction.LEAVEENTRYID = LeaveRequests.LEAVEENTRYID')
                ),
                array(
                    'table' => 'salary_head_items',
                    'alias' => 'SalaryHeadItems',
                    'type' => 'LEFT',
                    'foreignKey' => false,
                    'conditions' => array('LeaveRequests.salary_head_item_fkey = SalaryHeadItems.salary_head_item_pkey')
                )
            );
            $conditions = array('EmployeeLeaveTransaction.LEAVEENTRYID' => $leaveentryid);

            $arr_leavetransactions = $this->EmployeeLeaveTransaction->find("all", array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions,
            ));

            $resp_leavetransactions = array();
            $leave_type = isset($arr_leavetransactions[0]['SalaryHeadItems']['leave_type']) ? $arr_leavetransactions[0]['SalaryHeadItems']['leave_type'] : '';
            $resp_leavetransactions = Set::extract('/EmployeeLeaveTransaction/.', $arr_leavetransactions);
            $this->set('head', 'Leave Details');
            $this->set('leave_type', $leave_type);
            $this->set('resp_leavetransactions', $resp_leavetransactions);

            $arr_leave_details = $this->LeaveRequests->find("first", array(
                'fields' => 'salary_head_item_fkey,EMP_fkey',
                'conditions' => array('LEAVEENTRYID' => $leaveentryid)
            ));
            $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;
            $emp_fkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : 0;

            //Fetch leave balance
            //$arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, date('Y')) AS leave_balance");
            //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
            //$this->set('leavebalance',$leavebalance);
            // edited by sruthi start here 24-09-2016//
            $to_date = isset($arr_leave_details['LeaveRequests']['TODATE']) ? $arr_leave_details['LeaveRequests']['TODATE'] : '';
            $to_year = date('Y', strtotime($to_date));
            $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
             and branch_code= (select branch_code from emp_details where emp_Pkey='$emp_fkey' ) ORDER BY fin_year DESC LIMIT 1  ");
            $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
            //edited by athira on 21-09-2025
            $company_code=$this->Session->read('company_code');
                $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
		      $leave_days="NULL";
            $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$emp_fkey','$salary_head_item_fkey',$leave_days) as LeaveBalance");
            }
            else{
             $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$emp_fkey','$salary_head_item_fkey','$finyear') as LeaveBalance");
            }
           //end
            $leavebalance = isset($lbalance[0][0]['LeaveBalance']) ? $lbalance[0][0]['LeaveBalance'] : 0;
            $this->set('leavebalance', $leavebalance);
            // edited by sruthi ends here 24-09-2016//
            //Check if leave is isnegative or issandwitch
            $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
            $arr_leave_policy = $this->LeavePolicy->find("all", array(
                'fields' => 'IS_SANDWICH,ALLOW_NEGETIVE',
                'conditions' => array(
                    'salary_head_item_fkey' => $salary_head_item_fkey,
                    'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $emp_fkey . ')'
                )
            ));
            $is_sandwitch = isset($arr_leave_policy[0]['LeavePolicy']['IS_SANDWICH']) ? $arr_leave_policy[0]['LeavePolicy']['IS_SANDWICH'] : '';
            $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';
            //echo $is_sandwitch."-".$allow_negative;
            $this->set('is_sandwitch', $is_sandwitch);
            $this->set('allow_negative', $allow_negative);
            //SELECT IS_SANDWICH,ALLOW_NEGETIVE from leavepolicy where salary_head_item_fkey=87 and LEAVEPOLICY_GROUP_ID   in (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=11);
        }
    }

    //Ends


    /*
     * Get leave balance for auth/approve
     * On 24 Sep 2016
     */
    public function getLeaveBalanceForAuthOrApproval($leaveentryId = "") {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $arr_leave_entry = $this->LeaveRequests->find('first', array(
            'conditions' => array('LeaveRequests.LEAVEENTRYID' => $leaveentryId)
        ));
        $salary_head_item_fkey = isset($arr_leave_entry['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_entry['LeaveRequests']['salary_head_item_fkey'] : '';
        $emp_fkey = isset($arr_leave_entry['LeaveRequests']['EMP_fkey']) ? $arr_leave_entry['LeaveRequests']['EMP_fkey'] : '';
        $leave_days = isset($arr_leave_entry['LeaveRequests']['leave_days']) ? $arr_leave_entry['LeaveRequests']['leave_days'] : 0;
        $from_date = isset($arr_leave_entry['LeaveRequests']['FROMDATE']) ? $arr_leave_entry['LeaveRequests']['FROMDATE'] : '';
        //$from_month = date('m', strtotime($from_date));
        $att_startdate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$from_date', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$from_date', '%Y-%m-01'), 2) as monthly_att_todate");  
        $att_startdate1 = $att_startdate['0']['0']['monthly_att_fromdate'];
        $att_enddate1 = $att_enddate['0']['0']['monthly_att_todate'];
        
        if($from_date > $att_enddate1){
        $month = date("Y-m-d", strtotime("+1 month", strtotime($att_enddate1)));
        }else{ 
         $month =  $att_enddate1;  
        }
        $from_month = date("Y-m",  strtotime($month));
        $from_year = date('Y', strtotime($from_date));
        $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
   and branch_code= (select branch_code from emp_details where emp_Pkey='$emp_fkey' ) ORDER BY fin_year DESC LIMIT 1 "); // '$from_date' between start_month and end_month
        $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
       //edited by athira on 21-09-2025
            $company_code=$this->Session->read('company_code');
          $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
		    $lmonthbalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn('$emp_fkey','$salary_head_item_fkey','$from_month','$finyear') as LeaveBalancemonth");
            $monthly_balance = isset($lmonthbalance['0']['0']['LeaveBalancemonth']) ? $lmonthbalance['0']['0']['LeaveBalancemonth'] : 0;
            }else{
            $monthly_balance = 0;    
            }
        return array($leave_days, $monthly_balance);
    }

    //Ends
    /*
     * Get yearly leave balance for auth/approve
     * On 24 Sep 2016
     */
    public function getYearlyLeaveBalanceForAuthOrApproval($leaveentryId = "") {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        try {
            $arr_leave_entry = $this->LeaveRequests->find('first', array(
            'conditions' => array('LeaveRequests.LEAVEENTRYID' => $leaveentryId)
            ));
        } catch (Exception $ex) {
            $resp["success"] = false;
            $resp["message"] = "Something Went Wrong on Leave Balance checking, Please Try Again";
            return json_encode($resp);
        }
        
        $salary_head_item_fkey = isset($arr_leave_entry['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_entry['LeaveRequests']['salary_head_item_fkey'] : '';
        $emp_fkey = isset($arr_leave_entry['LeaveRequests']['EMP_fkey']) ? $arr_leave_entry['LeaveRequests']['EMP_fkey'] : '';
        $leave_days = isset($arr_leave_entry['LeaveRequests']['leave_days']) ? $arr_leave_entry['LeaveRequests']['leave_days'] : 0;
        $to_date = isset($arr_leave_entry['LeaveRequests']['TODATE']) ? $arr_leave_entry['LeaveRequests']['TODATE'] : '';
        $to_year = date('Y', strtotime($to_date));
        $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
         and branch_code= (select branch_code from emp_details where emp_Pkey='$emp_fkey' ) ORDER BY fin_year DESC LIMIT 1  ");
        $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
        //edited by athira on 21-09-2025
        $company_code=$this->Session->read('company_code');
       $restricted_companies = [
                    'KWMT','ABSG','MBCT','MRBS','STCL',
                    'AGNG','ESNP','VGNN','AYRK','VGFS','VSFS'
                ];
               if (!in_array($company_code, $restricted_companies, true)) {
		   $leave_days="NULL";
        $arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, $leave_days) AS leave_balance");
        }
        else{
         $arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, '$finyear') AS leave_balance");
        }
        //end
        $leavebalance = isset($arr_leavebalance[0][0]['leave_balance']) ? $arr_leavebalance[0][0]['leave_balance'] : 0;

        return array($leave_days, $leavebalance);
    }

   function sendauthorizationmail($output, $auth) {
       //debug($output);die;
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $this->fixEncoding($auth['Name']);
        $applied_emp = $this->fixEncoding($auth['Appliedby']);
        $leavedays = $output['0']['leave_days'];
        $applied_emp_id = $output['1']['emp_company_id'];
        $leavetype = $output['2']['item'];
        $leavefrom = $output['0']['FROMDATE'];
        $duties = $output['0']['contact_person'];
        $leaveend = $output['0']['TODATE'];
        $applied = $output['0']['applied_date'];
        $action = $output['action'];
        $ccmail = isset($output['cc_email'])?$output['cc_email']:'';
        $addressess = isset($output['cc_email1'])?$output['cc_email1']:'';
       // debug($addresses);
        $reason = isset( $output['0']['Reason'] )?$output['0']['Reason']:"";
        $cur_user_name = $this->Session->read("user_name");
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        if($action ==='Approved'){
               $msgs= 'Your Leave Request has been approved successfully.';
            }elseif($action ==='Rejected'){
               $msgs= 'Sorry. Your Leave Request has been rejected.';
            }elseif($action ==='Cancellation Approved'){
               $msgs= 'Your Leave Cancellation Request has been approved successfully.';
            }elseif($action ==='Cancellation Authorized'){
               $msgs= 'Your Leave Cancellation Request has been authorized successfully.';
            }else{
               $msgs= 'You have a Leave '.$action.' Request from one of your team-mates in '.$companyname.' which needs your further action to move the workflow forward.';
            }
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = false;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            //$mail->Host = 'smtp.zoho.in'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password 
            //$mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            //$mail->Password = 'mypayrollmaster123'; 
//            $mail->Username = 'noreply@mypayrollmaster.online';                 // SMTP username
//            $mail->Password = '@Password90#'; 
            $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';                 // SMTP username
            $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('mypayrollmaster@office24.online', $cur_user_name);
            $mail->addAddress($email);     // Add a recipient
            //$mail->addBCC('projects@greatleap.tech'); 
            if($ccmail){
            $mail->addCC($ccmail);}
            if($addressess){
            foreach ($addressess as $key => $value) {
                $value = $value['0']['emp_details']['email'];
            $mail->addCC($value,'My Payroll Master User');     // Add a recipient
            } 
            }
            $mail->addReplyTo('mypayrollmaster@office24.online', $cur_user_name);
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
                                        		<div style="color:#404040;font-size:15px;line-height:22px;font-weight:lighter;padding:0;margin:0">Dear '.$auth_name.'     
                                                   <br><br>'.$msgs.' </div>
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

    function sendfinalapprovalmail($output, $auth) {
       //debug($output);die;
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $this->fixEncoding($auth['Name']);
        $applied_emp = $this->fixEncoding($auth['Appliedby']);
        $leavedays = $output['0']['leave_days'];
        $applied_emp_id = $output['1']['emp_company_id'];
        $leavetype = $output['2']['item'];
        $leavefrom = $output['0']['FROMDATE'];
        $duties = $output['0']['contact_person'];
        $leaveend = $output['0']['TODATE'];
        $applied = $output['0']['applied_date'];
        $action = $output['action'];
        $url = $output['email_url'];
        $reason = isset( $output['0']['Reason'] )?$output['0']['Reason']:"";
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $cur_user_name = $this->Session->read("user_name");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        if($action ==='Approved'){
               $msgs= ' Your Leave Request has been approved successfully.';
            }elseif($action ==='Rejected'){
                $msgs= 'Sorry. Your Leave Request has been rejected.';
            }else{
               $msgs= ' You have a Leave '.$action.'  Request from one of your team-mates in '.$companyname.' which needs your 
                                                        further action to move the workflow forward.';
            }
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            $mail->SMTPDebug = false;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            //$mail->Host = 'smtp.zoho.in'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->Host = 'smtp.email.ap-hyderabad-1.oci.oraclecloud.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password 
            //$mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            //$mail->Password = 'mypayrollmaster123'; 
//            $mail->Username = 'noreply@mypayrollmaster.online';                 // SMTP username
//            $mail->Password = '@Password90#'; 
            $mail->Username = 'ocid1.user.oc1..aaaaaaaatro73lat7eqcxyj3l3ihljndby5hgi4lolh6v3ndjz7s7cfyst7a@ocid1.tenancy.oc1..aaaaaaaaspm2wdossjgzaijbbwjkw52ze5upoj57oft2cdge2wx2mavcwquq.f3.com';                 // SMTP username
            $mail->Password = 'm$Xt&:CFT7KCFkB]S$K)';
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('mypayrollmaster@office24.online', $cur_user_name);
            $mail->addAddress($email);     // Add a recipient
            //$mail->addBCC($email); 
            $mail->addReplyTo('mypayrollmaster@office24.online', $cur_user_name);
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddEmbeddedImage('https://login.mypayrollmaster.online/newlogin/img/logo.png', 'MPM');
            $mail->Subject = "Action Needed : MPM : Leave Request Details";
            $mail->AltBody    = '<!DOCTYPE html>';
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
                                        		<div style="color:#404040;font-size:15px;line-height:22px;font-weight:lighter;padding:0;margin:0">Dear '.$auth_name.'     
                                                   <br><br>'.$msgs.' </div>
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
                                        	<td width="120" align="right" valign="top"><img src="https://mypayrollmaster.online/forsight/files/user1.png" alt="Employee Name" width="90" height="90"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Employee Name</h3>
                                                <div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
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
                                        	<td width="120" align="right" valign="top"><img src="https://mypayrollmaster.online/forsight/files/name1.png" alt="Employee ID" width="90" height="90"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Employee ID</h3>
                                                <div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
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
                                        	<td width="120" align="right" valign="top"><img src="https://mypayrollmaster.online/forsight/files/calendar2.png" alt="Leave Type" width="90" height="90" ></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Type</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavetype . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="https://mypayrollmaster.online/forsight/files/calendar1.png" alt="Leave Days" width="90" height="90" ></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Days</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavedays . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="https://mypayrollmaster.online/forsight/files/clock1.png" alt="Leave Dates" width="90" height="90" ></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Dates</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavefrom . ' - ' . $leaveend . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="https://mypayrollmaster.online/forsight/files/calendar2.png" alt="Applied Date" width="90" height="90" ></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Applied Date</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $applied . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="https://mypayrollmaster.online/forsight/files/name1.png" alt="Reasons" width="90" height="90" ></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Reason</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $reason . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="https://mypayrollmaster.online/forsight/files/user1.png" alt="Duties handed over" width="90" height="90" ></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Duties handed over to</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
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
                                                        	<td align="center" style="margin:0;text-align:center"><a href=' . $url . ' style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#2e7695;padding:14px 40px;display:block" target="_blank">Take action.</a></td>
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

    function sendapprovemail($output, $auth) {
		
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $this->fixEncoding($auth['Name']);
        $applied_emp = $this->fixEncoding($auth['Appliedby']);
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset( $output['message'] )?$output['message']:"";
        $action = $output['action'];
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password
            $mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            $mail->Password = 'mypayrollmaster123'; 
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('noreply@mypayrollmaster.com', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "MyPayrollMaster - Leave $action Request";
            $mail->Body = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
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
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.com/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="codexworld" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(65, 132, 243);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Leave '.$action.' Request from <font style="color:#fff;">' . $applied_emp . '</font> </h1>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.com is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.com/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
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
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Leave Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/user.png" alt="tool" width="120" height="120"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Employee Name</h3>
                                                <div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
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
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/icon-expiration.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Days</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavedays . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/fixed_date_icon_sales_page.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Session</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavefrom . ' - ' . $leaveend . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Utilities-calendar-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Applied Date</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $applied . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Messages-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Message</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $message . '</div>
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
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.com/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login to take action!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
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
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
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
</div>';

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    function sendapprovedmail($output, $auth) {
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $this->fixEncoding($auth['Name']);
        $applied_emp = $this->fixEncoding($auth['Appliedby']);
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset( $output['message'] )?$output['message']:"";
        $action = $output['action'];
												 
													
													  
													  
				
												 
		   
        $action_msg = ($action != "Cancellation Approved")?"has been Approved":$action;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password
            $mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            $mail->Password = 'mypayrollmaster123'; 
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('noreply@mypayrollmaster.com', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "MyPayrollMaster - Leave $action Successfully";
            $mail->Body = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
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
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.com/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="Mypayrollmaster" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(10, 169, 0);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:68px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Congratulations !</h1><br>
                                                    <h2 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Your Leave '.$action_msg.'</h2>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.com is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.com/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
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
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Leave Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/icon-expiration.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Days</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavedays . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/fixed_date_icon_sales_page.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Session</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavefrom . ' - ' . $leaveend . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Utilities-calendar-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Applied Date</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $applied . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Messages-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Message</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $message . '</div>
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
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.com/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
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
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
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
</div>';

            return $mail->send();
							
							
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    function sendrejectedsmail($output, $auth) {
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $this->fixEncoding($auth['Name']);
        $applied_emp = $this->fixEncoding($auth['Appliedby']);
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset( $output['message'] )?$output['message']:"";
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password
            $mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            $mail->Password = 'mypayrollmaster123'; 
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('noreply@mypayrollmaster.com', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "MyPayrollMaster - Your Leave has Rejected";
            $mail->Body = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
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
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.com/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="Mypayrollmaster" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(169, 23, 0);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:68px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Sorry !</h1><br>
                                                    <h2 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Your Leave Has Been Rejected</h2>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.com is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.com/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
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
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Leave Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/icon-expiration.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Days</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavedays . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/fixed_date_icon_sales_page.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Session</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavefrom . ' - ' . $leaveend . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Utilities-calendar-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Applied Date</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $applied . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Messages-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Message</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $message . '</div>
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
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.com/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
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
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
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
</div>';

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    function leaves() {
        $this->autoRender = false;
        $userPkey = $this->Session->read("emp_fkey");
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $noti = $this->EmployeeMenu->query("select leaveentries.EMP_fkey,empdetails.first_name,empdetails.last_name from leaveentries left join emp_details as empdetails on (empdetails.emp_pkey = leaveentries.EMP_fkey) where (ISAutherizedby ='$userPkey' and ISAutherized = '0'  AND LEAVESTATUS IN('Applied')) or (APPROVEDBY = '$userPkey' and ISAPPROVED = '0' and ISAutherized = '1' AND LEAVESTATUS IN('Authorized'))");
        $this->set("noti", $noti);
        echo count($noti);
    }

    function loadleaves() {
        $userPkey = $this->Session->read("emp_fkey");
        $this->EmployeeMenu->useDbConfig = $this->Session->read('ds');
        $noti = $this->EmployeeMenu->query("select leaveentries.EMP_fkey,empdetails.first_name,empdetails.last_name from leaveentries left join emp_details as empdetails on (empdetails.emp_pkey = leaveentries.EMP_fkey) where (ISAutherizedby ='$userPkey' and ISAutherized = '0'  AND LEAVESTATUS IN('Applied')) or (APPROVEDBY = '$userPkey' and ISAPPROVED = '0' and ISAutherized = '1' AND LEAVESTATUS IN('Authorized'))");
        $this->set("noti", $noti);
    }

    //Ends
	

    /**
     * Leave Cancellation Request Email
     * On 11 March 2017
     */
    function sendcancellationappliedmail($output, $auth) {
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $this->fixEncoding($auth['Name']);
        $applied_emp = $this->fixEncoding($auth['Appliedby']);
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset( $output['message'] )?$output['message']:"";
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password
            $mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            $mail->Password = 'mypayrollmaster123'; 
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('noreply@mypayrollmaster.com', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "MyPayrollMaster - Leave Cancellation Request";
            $mail->Body = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
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
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.com/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="codexworld" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(65, 132, 243);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Leave Cancellation Request from <font style="color:#fff;">' . $applied_emp . '</font> </h1>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.com is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.com/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
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
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Leave Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/user.png" alt="tool" width="120" height="120"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                                <h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Employee Name</h3>
                                                <div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
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
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/icon-expiration.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Days</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavedays . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/fixed_date_icon_sales_page.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Session</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavefrom . ' - ' . $leaveend . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Utilities-calendar-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Applied Date</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $applied . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Messages-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Message</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $message . '</div>
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
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.com/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login to take action!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
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
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
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
</div>';

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }
    //Ends
    
    /**
     * Leave Cancellation approved Email
     * On 18 March 2017
     */
    function sendcancellationapprovedmail($output, $auth) {
        $this->autoRender = FALSE;
        $action = isset($output['action']) ? $output['action'] : "Accepted";
        $email = $auth['Email'];
        $auth_name = $this->fixEncoding($auth['Name']);
        $applied_emp = $this->fixEncoding($auth['Appliedby']);
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset( $output['message'] )?$output['message']:"";
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password
            $mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            $mail->Password = 'mypayrollmaster123'; 
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('noreply@mypayrollmaster.com', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "MyPayrollMaster - Leave Cancellation $action Successfully";
            $mail->Body = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
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
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.com/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="Mypayrollmaster" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(10, 169, 0);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:68px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Congratulations !</h1><br>
                                                    <h2 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Your Leave Cancellation Has Been ' . $action . '</h2>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.com is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.com/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
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
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Leave Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/icon-expiration.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Days</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavedays . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/fixed_date_icon_sales_page.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Session</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavefrom . ' - ' . $leaveend . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Utilities-calendar-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Applied Date</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $applied . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Messages-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Message</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $message . '</div>
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
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.com/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
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
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
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
</div>';

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }
    //Ends
    
    /**
     * When leave changes from applied to cancelled
     * On 15 April 2017
     */
    function sendcancelledmail($output, $auth) {
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $this->fixEncoding($auth['Name']);
        $applied_emp = $this->fixEncoding($auth['Appliedby']);
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset( $output['message'] )?$output['message']:"";
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $comp = $this->EmployeeDetails->query("select subdomain,business_name from comp_contact_info");
        //$url = isset($comp['0']['comp_contact_info']['subdomain'])? 'href="'.$comp['0']['comp_contact_info']['url'].'"': 'href="login.mypayrollmaster.com"';
        $companyname = isset($comp['0']['comp_contact_info']['business_name']) ? $comp['0']['comp_contact_info']['business_name'] : 'Your Company';
        //$user_name = $this->EmployeeDetails->query("select first_name,last_name from user_credentials where user_id = '$userid' ");
        //$name = $user_name['0']['user_credentials']['first_name'].' '.$user_name['0']['user_credentials']['last_name'];
        try {
            App::import('Vendor', 'PHPMailer', array('file' => 'PHPMailer/PHPMailerAutoload.php'));
            $mail = new PHPMailer;
            //$mail->SMTPDebug = 2;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.zoho.com'; //'IW-00163E007722';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
//            $mail->Password = 'welcome123';                           // SMTP password
            $mail->Username = 'noreply@mypayrollmaster.com';                 // SMTP username
            $mail->Password = 'mypayrollmaster123'; 
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to
            //$mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->setFrom('noreply@mypayrollmaster.com', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "MyPayrollMaster - Your Leave has Cancelled";
            $mail->Body = '<div style="font-family:HelveticaNeue-Light,Arial,sans-serif;background-color:#eeeeee">
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
                                                <td align="left" valign="middle" style="padding:0;margin:0;font-size:0;line-height:0"><a href="http://mypayrollmaster.com/" target="_blank"><img style="height: 40px;" src="http://184.107.133.75/mypayrollmaster/wp-content/uploads/2016/04/mpm2.png" alt="Mypayrollmaster" ></a></td>
                                                <td width="30"></td>
                                            </tr>
                                       	</tbody>
                                        </table>
                                  	</td>
                    			</tr>
                                <tr>
                                    <td colspan="3" align="center">
                                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        	<tr>
                                            	<td colspan="3" height="60"></td></tr><tr style="background-color: rgb(169, 23, 0);
    COLOR: white;
    height: 140px;"><td width="25"></td>
                                                <td align="center">
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:68px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Yes</h1><br>
                                                    <h2 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">You have cancelled your leave.</h2>
                                                </td>
                                                <td width="25"></td>
                                            </tr>
                                            <tr>
                                            	<td colspan="3" height="40"></td></tr><tr><td colspan="5" align="center" style="padding: 0px 149px 0px 149px;">
                                                    <p style="color:#404040;font-size:16px;line-height:24px;font-weight:lighter;padding:0;margin:0">mypayrollmaster.com is a highly advanced and comprehensive time, attendance and payroll processing online software from Business Forsight Labs LLP, a company which built its trust on its compliance management and business startup services.</p><br>
                                                    <p style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">
                    Automatic statutory compliance and calculations.</p>

                                                </td>
                                            </tr>
                                            <tr>
                                            <td colspan="4">
                                                <div style="width:100%;text-align:center;margin:30px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="font-family:HelveticaNeue-Light,Arial,sans-serif;margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                            <td align="center" style="margin:0;text-align:center"><a href="http://mypayrollmaster.com/" style="font-size:21px;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#0096d3;padding:14px 40px;display:block;letter-spacing:1.2px" target="_blank">Visit website!</a></td>
                                                      	</tr>
                                                   	</tbody>
                                                    </table>
                                               	</div>
                                           	</td>
                                       	</tr>
                                        <tr><td colspan="3" height="30"></td></tr>
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
                                        	<td colspan="4" align="center">&nbsp;</td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4" align="center"><h2 style="font-size:24px">Leave Details Are</h2></td>
                                      	</tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                      	</tr>
                                        
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                       	</tr>
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/icon-expiration.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Days</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavedays . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/fixed_date_icon_sales_page.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Leave Session</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $leavefrom . ' - ' . $leaveend . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Utilities-calendar-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Applied Date</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $applied . '</div>
                                          		<div style="line-height:10px;padding:0;margin:0">&nbsp;</div>
                                           	</td>
                                            <td width="30"></td>
                                        </tr>
                                        <tr>
                                        	<td colspan="4">&nbsp;</td>
                                        </tr>
                                        <tr>
                                        	<td colspan="5" height="40" style="padding:0;margin:0;font-size:0;line-height:0"></td>
                                      	</tr>

                                        
                                        <tr>
                                        	<td width="120" align="right" valign="top"><img src="http://184.107.133.75/ws/Messages-icon.png" alt="creditibility" width="120" height="120" class="CToWUd"></td>
                                            <td width="30"></td>
                                            <td align="left" valign="middle">
                                            	<h3 style="color:#404040;font-size:18px;line-height:24px;font-weight:bold;padding:0;margin:0">Message</h3>
                                              	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                              	<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">' . $message . '</div>
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
                                        		<div style="color:#404040;font-size:16px;line-height:22px;font-weight:lighter;padding:0;margin:0">Visit MyPayrollMaster now and access your organisation, view live attendance, download reports and payroll. </div>
                                          	</td>
                                      	</tr>
                                        <tr>
                                        	<td align="center">
                                                <div style="text-align:center;width:100%;padding:40px 0">
                                                    <table align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;padding:0">
                                                    <tbody>
                                                    	<tr>
                                                        	<td align="center" style="margin:0;text-align:center"><a href="http://login.mypayrollmaster.com/" style="font-size:18px;font-family:HelveticaNeue-Light,Arial,sans-serif;line-height:22px;text-decoration:none;color:#ffffff;font-weight:bold;border-radius:2px;background-color:#00a3df;padding:14px 40px;display:block" target="_blank">Login!</a></td>
                                                    	</tr>
                                                   	</tbody>
                                                 	</table>
                                              	</div>
                                        	</td>
                                      </tr><tr><td>&nbsp;</td>
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
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">&copy; 2016 mypayrollmaster. All rights reserved.</div>
                                                	<div style="line-height:5px;padding:0;margin:0">&nbsp;</div>
                                                	<div style="color:#a3a3a3;font-size:12px;line-height:12px;padding:0;margin:0">Made in India</div>
                                        		</td>
                                              	<td align="right" valign="top">
                                                	<span style="line-height:20px;font-size:10px"><a href="https://www.facebook.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/BggPYqAh.png" alt="fb"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://twitter.com/mypayrollmaster" target="_blank"><img src="http://i.imgbox.com/j3NsGLak.png" alt="twit"></a>&nbsp;</span>
                                                    <span style="line-height:20px;font-size:10px"><a href="https://plus.google.com/mypayrollmater" target="_blank"><img src="http://i.imgbox.com/wFyxXQyf.png" alt="g"></a>&nbsp;</span>
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
</div>';

            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }
    //Ends
    
    /*
     * Call procedure 'leave_transaction_prc'
     * On Applying leave
     * By santhosh on 03 Aug 2015
     */
    function callLeaveTransactionProcedure($leaveentryId='') {
        if(!empty($leaveentryId)){
            $arr_leave_details = $this->LeaveRequests->find("first", array(
                'fields' => 'LEAVEENTRYID,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
                'conditions' => array('LEAVEENTRYID' => $leaveentryId)
            ));
            $outputParameter = isset($arr_leave_details['LeaveRequests']) ? $arr_leave_details['LeaveRequests'] : array();

            $outputParameter['LEAVESTATUS'] = "'" . $outputParameter['LEAVESTATUS'] . "'";
            $outputParameter['FROMDATE'] = "'" . $outputParameter['FROMDATE'] . "'";
            $outputParameter['TODATE'] = "'" . $outputParameter['TODATE'] . "'";

            $out = $this->LeaveRequests->leaveTransactionPrc($outputParameter);
            return $out;
        }
    }
     public function deletedoc($id =0,$name = 0,$document = 0){
        $this -> autoRender = FALSE;
        $this -> LeaveRequests -> useDbConfig = $this -> Session -> read('ds');
        $arr_emp_transaction_all = $this->LeaveRequests->find('all',array('conditions'=>array('LEAVEENTRYID'=>$id)));
        $filename = $arr_emp_transaction_all['0']['LeaveRequests']['file_name'];
        $type = $arr_emp_transaction_all['0']['LeaveRequests']['file_type'];
        $doc_name = str_replace($document, '', $filename); 
        $doc_name = "'$doc_name'";
        $doc_type = str_replace($name, '', $type); 
        $doc_type = "'$doc_type'";
        $result = $this->LeaveRequests->updateAll(array("file_name"=>$doc_name,"file_type"=>$doc_type),array('LEAVEENTRYID'=>$id));
        $message = 'Document Deleted successfully';
        return json_encode(array('status'=>1,'LEAVEENTRYID'=>$id,'message'=>$message));
                     
    }
    //Ends
    //edited by athira on 21-09-2025
    public function GetLeaveBalanceNew($salary_head_item_fkey = "", $end_date = "")
    {

        $this->autoRender = false;
        $arr_form_data = $this->request->data;
        $from_date = $arr_form_data['start_date'];

        $cur_emp_key = $this->Session->read("emp_fkey");
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $year = date('Y', strtotime($end_date));

        $leavePolicy = $this->LeavePolicy->find("all", array(
    'fields' => array('minimum_service','exceptions','status','minimum_leave','maximum_leave','min_day_before_apply','leave_policy_type'),
    'conditions' => array(
        'salary_head_item_fkey' => $salary_head_item_fkey,
        'status' => 1,
        "LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey = $cur_emp_key)"
    )
));

$leave_policy_type=$leavePolicy[0]['LeavePolicy']['leave_policy_type'];

$cycle_dates=
$att_start_date="";
$att_end_date="";
 

                $this->set('leave_policy_type',$leave_policy_type);

$min_service_flag = true;
$min_service_msg  = "";
$adv_notice_flag  = true;
$adv_notice       = "";
$min_leave_limit="";
$max_leave_limit="";

// ✅ Minimum Service Check
if ($leavePolicy && $leavePolicy[0]['LeavePolicy']['exceptions'] == 'Y') {
    if (!empty($leavePolicy[0]['LeavePolicy']['minimum_service'])) {
    $joining_date = $this->LeaveRequests->query(
        "SELECT joining_date from emp_proff where emp_fkey='$cur_emp_key'"
    );
    $joining_date = $joining_date[0]['emp_proff']['joining_date'];

    $joining = new DateTime($joining_date);
    $min_service = $leavePolicy[0]['LeavePolicy']['minimum_service'];
    $min_date = (new DateTime($joining_date))->modify("+$min_service month");
    $leaveDay = new DateTime($from_date);

    if ($leaveDay < $min_date) {
        $min_service_flag = false;
        $min_service_msg = "Employee must complete minimum service of $min_service month(s) before applying leave. Eligible from " . $min_date->format('Y-m-d');
    }
}


    // ✅ Advance Notice Check

    if (!empty($leavePolicy[0]['LeavePolicy']['min_day_before_apply'])) {
        $minDays = (int)$leavePolicy[0]['LeavePolicy']['min_day_before_apply'];

        $today = date('Y-m-d');
        $leaveStart = $from_date; // coming from form

        $diffDays = (strtotime($leaveStart) - strtotime($today)) / (60 * 60 * 24);

        if ($diffDays < $minDays) {
            $adv_notice_flag = false;
            $adv_notice = "Leave should be applied at least ".$minDays." day(s) in advance.";
        }
    }

    $min_leave_limit = !empty($leavePolicy[0]['LeavePolicy']['minimum_leave']) 
                    ? (int)$leavePolicy[0]['LeavePolicy']['minimum_leave'] 
                    : 0;

$max_leave_limit = !empty($leavePolicy[0]['LeavePolicy']['maximum_leave']) 
                    ? (int)$leavePolicy[0]['LeavePolicy']['maximum_leave'] 
                    : 0;
    
}

        $att_startdate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$end_date', '%Y-%m-01'), 1) as monthly_att_fromdate");
        $att_enddate = $this->LeaveRequests->query("select att_start_end_fn(DATE_FORMAT('$end_date', '%Y-%m-01'), 2) as monthly_att_todate");
        $att_startdate1 = date("Y-m-d", strtotime($att_startdate['0']['0']['monthly_att_fromdate']));
        $att_enddate1 = date("Y-m-d", strtotime($att_enddate['0']['0']['monthly_att_todate']));
        if ($end_date != '') {
            $months = date("Y-m-d", strtotime($end_date));
        } else $months = date("Y-m-d");
        $from_date = $arr_form_data['start_date'];

        $leave_days=$from_date;
        $from_half = $arr_form_data['start_sess'];
        $to_half = $arr_form_data['end_sess'];
        $lrule = "Success";
      
        $from = date("Y-m-01", strtotime($end_date));
        $to_date = date("Y-m-t", strtotime($end_date));
        $get_finyear = $this->LeaveRequests->query("select fin_year  from fin_year where lcase(Year_status)='open' and vattr1 = 0 and is_current_finyear='Y' and status=1
        and branch_code= (select branch_code from emp_details where emp_Pkey='$cur_emp_key' ) ORDER BY fin_year DESC LIMIT 1  "); // removed this line code - and '$end_date' between start_month and end_month
        $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
        
        if($leave_days == ''){
            $leave_days="NULL";
          $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$salary_head_item_fkey',$leave_days) as LeaveBalance");
        }
        else{
            
           $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$salary_head_item_fkey','$leave_days') as LeaveBalance"); 
        }
        
        $this->set('lbalance', $lbalance);
        if ($end_date >= $att_startdate1 && $end_date <= $att_enddate1) {
            $months = $end_date;
        } else {
            $months = date('Y-m', strtotime($end_date . ' + 1 months')) . '-01';
        }
        
        $yearly_balance = isset($lbalance['0']['0']['LeaveBalance']) ? $lbalance['0']['0']['LeaveBalance'] : 0;
        $monthly_balance =  0;

        $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
    

        $arr_leave_count_takens = $this->LeavePolicy->query("select sum(cnt) Applied_leaves from (
                                                        SELECT COUNT(*)
                                                        cnt FROM emp_leave_transactions JOIN leaveentries ON (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID ) 
                                                        WHERE emp_leave_transactions.Leavestatus IN ('Applied') and leaveentries.EMP_fkey IN ('$cur_emp_key') and leave_session=3 and emp_leave_transactions .Remarks!='No need Leave'
                                                        and emp_leave_transactions.leave_date BETWEEN '$from' and '$to_date' and leaveentries.salary_head_item_fkey IN ('$salary_head_item_fkey') 
                                                        union all
                                                        SELECT COUNT(*)*0.5
                                                        cnt FROM emp_leave_transactions JOIN leaveentries ON (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID ) 
                                                        WHERE emp_leave_transactions.Leavestatus IN ('Applied') and leaveentries.EMP_fkey IN ('$cur_emp_key') and leave_session!=3 and emp_leave_transactions .Remarks!='No need Leave'
                                                        and emp_leave_transactions.leave_date BETWEEN '$from' and '$to_date' and leaveentries.salary_head_item_fkey IN ('$salary_head_item_fkey') )cnt");
        $arr_leave_policy = $this->LeavePolicy->find(
            "all",
            array(
                'fields' => 'ALLOW_NEGETIVE,REMARKS,leave_policy_type',
                'conditions' => array(
                    'salary_head_item_fkey' => $salary_head_item_fkey,
                    'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $cur_emp_key . ')'
                )
            )
        );
    

        // Calculating leave Days --- Added by Nimisha 15/05/2019
        $date1_ts = strtotime($arr_form_data['start_date']);
        $date2_ts = strtotime($arr_form_data['end_date']);
        $diff = $date2_ts - $date1_ts;

        $dif = round($diff / 86400);
        $leavedays = $dif + 1;
        if ($arr_form_data['start_sess'] == 1 && $arr_form_data['end_sess'] == 1) {
            $leavedays = $leavedays - 0.5;
        } else if ($arr_form_data['start_sess'] == 2 && $arr_form_data['end_sess'] == 2) {
            $leavedays = $leavedays - 0.5;
        } else if ($arr_form_data['start_sess'] == 2 && $arr_form_data['end_sess'] == 1) {
            $leavedays = $leavedays - 1;
        }

        $leave_policy_type = isset($arr_leave_policy[0]['LeavePolicy']['leave_policy_type']) ? $arr_leave_policy[0]['LeavePolicy']['leave_policy_type'] : '';

        $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';
        //Ends
        $leave_policy = $this->LeavePolicy->query("select min_day_before_apply from leavepolicy where salary_head_item_fkey = '$salary_head_item_fkey' and LEAVEPOLICY_GROUP_ID IN 
                   (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$cur_emp_key') and leavepolicy.status=1");
        $planned_leave = isset($leave_policy['0']['leavepolicy']['min_day_before_apply']) ? $leave_policy['0']['leavepolicy']['min_day_before_apply'] : 0;

        $company_code = strtoupper($this->Session->read('company_code'));

        if ($company_code == 'MBCT' || $company_code == 'HRBL' || $company_code =='GLET') {
            $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
            $arr_salary_head = $this->SalaryHeadItems->find(
                "all",
                array(
                    'fields' => 'Occurance',
                    'conditions' => array(
                        'salary_head_item_pkey' => $salary_head_item_fkey,
                    )
                )
            );
            $head = isset($arr_salary_head['0']['SalaryHeadItems']['Occurance']) ? $arr_salary_head['0']['SalaryHeadItems']['Occurance'] : '';
        
    // Call for from_date
    $this->LeaveRequests->query("
        CALL leave_start_end_prc('$cur_emp_key', '$salary_head_item_fkey', '$from_date', @p_startdate1, @p_enddate1)
    ");
    $from_result = $this->LeaveRequests->query("SELECT @p_startdate1 AS start_date1, @p_enddate1 AS end_date1");
    $from_startdate = $from_result[0][0]['start_date1'];
    $from_enddate = $from_result[0][0]['end_date1'];

// }

            if ($company_code == 'HRBL') {
                $auth = $this->LeavePolicy->query("select `leave_auth_apr_person_fn`('$company_code','$cur_emp_key','apr','$salary_head_item_fkey') as resps");
            } 
            else {
                $auth = $this->LeavePolicy->query("select `leave_auth_apr_person_fnmbct`('$company_code','$cur_emp_key','apr','$salary_head_item_fkey') as resps");
            }
            $notified = $this->LeavePolicy->query("select document_mandatory from leavepolicy where salary_head_item_fkey='$salary_head_item_fkey' and LEAVEPOLICY_GROUP_ID 
	     in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey='$cur_emp_key')and status = 1");
            $apr = isset($auth['0']['0']['resps']) ? $auth['0']['0']['resps'] : '';
            $name = '';
           
            $document_mandatory = isset($notified['0']['leavepolicy']['document_mandatory']) ?  $notified['0']['leavepolicy']['document_mandatory'] : '';
            if ($apr) {
                $arr_users1 = $this->EmployeeDetails->find(
                    'all',
                    array(
                        'fields' => 'first_name,last_name,emp_id ',
                        'conditions' => array(
                            'status' => 1,
                            'emp_pkey' => $apr
                        )
                    )
                );
                if (count($arr_users1) == 1) {
                    $name = $arr_users1['0']['EmployeeDetails']['first_name'] . ' ' . $arr_users1['0']['EmployeeDetails']['last_name'] . ' ' . $arr_users1['0']['EmployeeDetails']['emp_id'];
                }
            }
          
            echo json_encode(array(
                'yearly_balance' => $yearly_balance,
                'APPROVEDBY' => $apr,
                'APPROVEDBYNAME' => $name,
                'PlannedLeave' => $planned_leave,
                "min_service_flag" => $min_service_flag,
                'adv_notice_flag'=>$adv_notice_flag,
                'adv_notice'=>$adv_notice,
                "min_service_msg"  => $min_service_msg,
                "min_leave_limit"  => $min_leave_limit,
                "max_leave_limit"  => $max_leave_limit,
                'leave_policy_type'=> $leave_policy_type,
                'allow_negative'   => strtoupper($allow_negative),
                'att_start_date'   => $from_startdate, // ✅ add cycle start
                'att_end_date'     => $from_enddate,  // ✅ add cycle end
                //'NotifiedBY' => $noti,
                //'NotifiedBYNAME' => $name1,
                'document_mandatory' => $document_mandatory,
               'monthly_balance' => $monthly_balance,
                'appliedleaves' => isset($arr_leave_count_takens['0']['0']['Applied_leaves']) ? $arr_leave_count_takens['0']['0']['Applied_leaves'] : 0,
                'allow_negative' => strtoupper($allow_negative),
                'leavedays' => $leavedays,
                'leaverule' => $lrule,
                'remarks' =>  isset($arr_leave_policy['0']['LeavePolicy']['REMARKS']) ? $arr_leave_policy['0']['LeavePolicy']['REMARKS'] : '',
                // 'compoff_available_dates' => ($all_available) ? $all_available : ''
            ));
        } else {
            $notified = $this->LeavePolicy->query("select document_mandatory from leavepolicy where salary_head_item_fkey='$salary_head_item_fkey' and LEAVEPOLICY_GROUP_ID 
	     in (select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey='$cur_emp_key') and status = 1");
        
            $document_mandatory = isset($notified['0']['leavepolicy']['document_mandatory']) ?  $notified['0']['leavepolicy']['document_mandatory'] : '';

             

            echo json_encode(array(
                'document_mandatory' => $document_mandatory,
                'PlannedLeave' => $planned_leave,
                'yearly_balance' => $yearly_balance,
                 "min_service_flag" => $min_service_flag,
                'adv_notice_flag'=>$adv_notice_flag,
                'adv_notice'=>$adv_notice,
                "min_service_msg"  => $min_service_msg,
                'monthly_balance' => $monthly_balance,
                "min_leave_limit"  => $min_leave_limit,
                "max_leave_limit"  => $max_leave_limit,
                'leave_policy_type'=> $leave_policy_type,
                'allow_negative'   => strtoupper($allow_negative),
                'att_start_date'   => $att_start_date, // ✅ add cycle start
                'att_end_date'     => $att_end_date ,   // ✅ add cycle end
                'appliedleaves' => isset($arr_leave_count_takens['0']['0']['Applied_leaves']) ? $arr_leave_count_takens['0']['0']['Applied_leaves'] : 0,
                'allow_negative' => strtoupper($allow_negative),
                'leavedays' => $leavedays,
                'leaverule' => $lrule,
                'remarks' =>  isset($arr_leave_policy['0']['LeavePolicy']['REMARKS']) ? $arr_leave_policy['0']['LeavePolicy']['REMARKS'] : ''
            ));
        }
    }
     //edited by athira on 21-09-2025
     public function addeditleave_new($leaveentryId = 0)
    {
        $emp_fkey = $this->Session->read('emp_fkey');
        $company_code = $this->Session->read('company_code');

        $this->set("company_code", $company_code);

        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $arr_mobile = $this->EmployeeDetails->query('select mobile_no ,classification
             from emp_details EmployeeDetails where status = 1 and emp_pkey = ' . $emp_fkey);
        if ($leaveentryId) {
            //Edit leave
            try {
                $this->set('head', 'Edit Leave');
                $this->set('leaveentryId', $leaveentryId);
                $this->loadLeaveDetails($leaveentryId);
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Leave Editing Failed, Please try again";
                return json_encode($resp);
            }
        } else {
            //Request leave
            try {
                $this->set('head', 'Apply Leave');
                $this->set('leaveentryId', 0);
                $arr_leave_details = array();
                $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
                $arr_leaverequestkeys = array_keys($this->LeaveRequests->schema());
                foreach ($arr_leaverequestkeys as $key) {
                    $arr_leave_details[$key] = '';
                }
                //added new section for MBCET College  


                // if($company_code == 'MBCT'){
                $auth = "select `leave_auth_apr_person_fn`('$company_code','$emp_fkey','auth') as resps";
                $row_details = $this->EmployeeDetails->query($auth);
                $emp_ids = $row_details['0']['0']['resps'];

                if ($company_code=='INFR'){
                $auth = "select `leave_auth_apr_person_fn`('$company_code','$emp_fkey','apr') as resps";
                $row_details1 = $this->EmployeeDetails->query($auth);
                 $apr = isset($row_details1[0][0]['resps']) ? $row_details1[0][0]['resps'] : '';
                 
            }

                if ($emp_ids != '0' && $emp_ids != null) {
                    $emp_condition = ' and emp_pkey != ' . $emp_fkey . ' and emp_pkey in (' . $emp_ids . ')';
                } else {
                    $emp_condition = ' and emp_pkey != ' . $emp_fkey . ' ';
                }

            //     $arr_users2 = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_id,mobile_no 
            //  from emp_details EmployeeDetails where status = 1 ' . $emp_condition);
             $arr_users2 = $this->EmployeeDetails->query(
    'SELECT 
        EmployeeDetails.emp_pkey,
        EmployeeDetails.first_name,
        EmployeeDetails.last_name,
        EmployeeDetails.emp_id,
        EmployeeDetails.mobile_no,
        EmployeeProff.emp_company_id
    FROM emp_details EmployeeDetails
    LEFT JOIN emp_proff EmployeeProff ON EmployeeProff.emp_fkey = EmployeeDetails.emp_pkey
    WHERE EmployeeDetails.status = 1 ' . $emp_condition
);

                $arr_leave_details['contact_No'] = isset($arr_mobile['0']['EmployeeDetails']['mobile_no']) ? $arr_mobile['0']['EmployeeDetails']['mobile_no'] : '';

                if (count($arr_users2) == 1) {
                    // $arr_leave_details['AUTHORIZEDBYNAME'] = $arr_users2['0']['EmployeeDetails']['first_name'] . ' ' . $arr_users2['0']['EmployeeDetails']['last_name'] . ' ' . $arr_users2['0']['EmployeeDetails']['emp_id'];
                    $firstName = isset($arr_users2[0]['EmployeeDetails']['first_name']) ? $arr_users2[0]['EmployeeDetails']['first_name'] : '';
    $lastName = !empty($arr_users2[0]['EmployeeDetails']['last_name']) ? ' ' . $arr_users2[0]['EmployeeDetails']['last_name'] : '';
    $companyId = !empty($arr_users2[0]['EmployeeProff']['emp_company_id']) ? ' - ' . $arr_users2[0]['EmployeeProff']['emp_company_id'] : '';

    $arr_leave_details['AUTHORIZEDBYNAME'] = trim($firstName . $lastName . $companyId);
    // edited bybindu 15-01-2026 end
                    $auth_emp = $arr_leave_details['ISAutherizedby'] = $arr_users2['0']['EmployeeDetails']['emp_pkey'];

                    
                }
               
                $arr_employees_to_exclude = array();
                $this->EmployeeConfig->useDbConfig = $this->Session->read('ds');
                $arr_emp = $this->EmployeeConfig->find(
                    "all",
                    array(
                        'fields' => 'policy_id',
                        'conditions' => array(
                            'EmployeeConfig.type' => 'LAPPR',
                            'EmployeeConfig.emp_fkey' => $emp_fkey,
                            'EmployeeConfig.status' => 1
                        )
                    )
                );
                foreach ($arr_emp as $val) {
                    if (isset($val['EmployeeConfig']['policy_id'])) {
                        $arr_employees_to_exclude[] = $val['EmployeeConfig']['policy_id'];
                    }
                }
                $arr_users1 = $this->EmployeeDetails->find(
                    'all',
                    array(
                        'fields' => 'emp_pkey,first_name,last_name,emp_id,concat(first_name," ",last_name," ",emp_id) as full_name ',
                        'conditions' => array(
                            'status' => 1,
                            'emp_pkey' => $arr_employees_to_exclude
                        )
                    )
                );
                if (count($arr_users1) == 1) {
                    $arr_leave_details['APPROVEDBYNAME'] = $arr_users1['0']['EmployeeDetails']['first_name'] . ' ' . $arr_users1['0']['EmployeeDetails']['last_name'] . ' ' . $arr_users1['0']['EmployeeDetails']['emp_id'];
                    $arr_leave_details['APPROVEDBY'] = $arr_users1['0']['EmployeeDetails']['emp_pkey'];
                }
                
                //edited by athira on 28-08-2025
                $plan=$this->EmployeeDetails->query("SELECT plan FROM comp_contact_info");
                $plan=$plan[0]['comp_contact_info']['plan'];
                $this->set('plan',$plan);

                if($plan == 'basic'){
                    $arr_employees = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
                                                                WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'HIERARCHY' 
                                                                                            AND status = 1 AND emp_fkey = $emp_fkey)
                                                                ");  
                }
                else{
                 $arr_employees = $this->EmployeeDetails->query("SELECT ei.EmpName, ei.employee_id, ei.emp_pkey FROM employee_info ei 
                                                                WHERE ei.emp_pkey IN (SELECT DISTINCT policy_id FROM emp_config WHERE type = 'LAPPR' 
                                                                                            AND status = 1 AND emp_fkey = $emp_fkey)
                                                                ");
                }
                //end
                
            if ($company_code == 'INFR' && !empty($apr)) {
            
                // sanitize (important)
                $apr = implode(',', array_map('intval', explode(',', $apr)));

                $arr_employees = $this->EmployeeDetails->query("
                    SELECT ei.EmpName, ei.employee_id, ei.emp_pkey 
                    FROM employee_info ei 
                    WHERE ei.emp_pkey IN ($apr)
                ");
            }
                $this->set("arr_employees", $arr_employees);
                
                
               
                $this->set('arr_leave_details', $arr_leave_details);
            } catch (Exception $ex) {
                $resp["success"] = false;
                $resp["message"] = "Leave Requesting Failed, Please try again";
                return json_encode($resp);
            }
        }
      
        $leaves = $this->getLeaveType(); //debug($leaves);
        $new_leave = array();
        $gender = $arr_mobile['0']['EmployeeDetails']['classification'];
  
        foreach ($leaves as $leave) {
            if ($gender == 'male' && $leave['SalaryHeadItems']['salary_head_item_pkey'] != 98) {
             
                array_push($new_leave, $leave);
            } else if ($gender == 'female' && $leave['SalaryHeadItems']['salary_head_item_pkey'] != 99) {
              
                array_push($new_leave, $leave);
            } else {
                array_push($new_leave, $leave);
            } //End
        }
      

        $this->set('arr_leave_type', $new_leave);
        $arr_users = $this->LeaveRequests->query("select concat(first_name,' ',ifnull(last_name,'')) as name,emp_pkey,classification  from emp_details where"
            . " status = '1' and emp_pkey not in ('$emp_fkey')  order by name ");
        $this->set("arr_users", $arr_users);
        if ($company_code == 'MBCT') {
            $arr_leave_details['Notified'] = $arr_users1['0']['EmployeeDetails']['first_name'] . ' ' . $arr_users1['0']['EmployeeDetails']['last_name'] . ' ' . $arr_users1['0']['EmployeeDetails']['emp_id'];
            $arr_leave_details['Notifiedby'] = $arr_users1['0']['EmployeeDetails']['emp_pkey'];
        }
    }


    // edited by athira on 22-05-2026
private function checkAttendancePunches($emp_pkey, $fromdate, $todate, $fromhalf = 1, $tohalf = 2)
{
    // Fetch attendance records within the range that have any present indication
    $attendance_records = $this->LeaveRequests->query("SELECT att_date, present FROM emp_detail_timeattandance WHERE emp_pkey = '$emp_pkey' and att_date between '$fromdate' and '$todate' and (present LIKE 'P/%' OR present LIKE '%/P' OR present = 'P' OR present LIKE 'p/%' OR present LIKE '%/p' OR present = 'p')");
    if (empty($attendance_records)) {
        return 0; // no conflict
    }

    $from_date_only = date('Y-m-d', strtotime($fromdate));
    $to_date_only   = date('Y-m-d', strtotime($todate));
    $conflictMask   = 0; // bit 1 = first half, bit 2 = second half

    foreach ($attendance_records as $rec) {
        $att_date = date('Y-m-d', strtotime($rec['emp_detail_timeattandance']['att_date']));
        $present  = isset($rec['emp_detail_timeattandance']['present']) ? trim($rec['emp_detail_timeattandance']['present']) : '';

        $first_half_present  = false;
        $second_half_present = false;

        if (strpos($present, '/') !== false) {
            $parts = explode('/', $present);
            if (isset($parts[0]) && strcasecmp(trim($parts[0]), 'P') == 0) {
                $first_half_present = true;
            }
            if (isset($parts[1]) && strcasecmp(trim($parts[1]), 'P') == 0) {
                $second_half_present = true;
            }
        } else {
            if (strcasecmp($present, 'P') == 0) {
                $first_half_present = true;
                $second_half_present = true;
            }
        }

        if (!$first_half_present && !$second_half_present) {
            continue; // no present on this day
        }

        // Determine whether each half should be checked based on request boundaries
        $check_first_half  = true;
        $check_second_half = true;
        if ($att_date === $from_date_only && $fromhalf == 2) {
            $check_first_half = false;
        }
        if ($att_date === $to_date_only && $tohalf == 1) {
            $check_second_half = false;
        }

        if ($check_first_half && $first_half_present) {
            $conflictMask |= 1; // first half conflict
        }
        if ($check_second_half && $second_half_present) {
            $conflictMask |= 2; // second half conflict
        }
    }

    return $conflictMask; // 0 = none, 1 = first, 2 = second, 3 = both
}

    // ended by athira on 22-05-2026

}