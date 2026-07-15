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
    public $uses = array('AppModel', 'LeaveRequests', 'SalaryHeadItems', 'EmployeeDetails', 'EmployeeLeaveTransaction', 'LeavePolicy');
    public $components = array('Session');

    /*
     * Leave List Landing Page
     */

    public function index() {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");

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

    public function getusers() {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $emp_fkey = $this->Session->read('emp_fkey');
        $arr_request_data = $this->request->query;
//debug($arr_request_data);
        if (isset($arr_request_data['username'])) {
            $searchkey = $arr_request_data['username'];
            $filter_condition = 'first_name LIKE "%' . $searchkey . '%" and emp_pkey != ' . $emp_fkey;
        } else {
            $filter_condition = '';
        }
        $arr_users = $this->EmployeeDetails->find('all', array(
            'fields' => 'emp_pkey,first_name,concat(first_name," ",last_name," ",emp_id) as full_name ',
            'conditions' => array(
                'status' => 1,
                $filter_condition
            )
                )
        );
        //  debug($arr_users);
        $arr_filterresult = array();
        foreach ($arr_users as $val) {
            $arr_filterresult[] = isset($val['EmployeeDetails']) ? array_merge($val['EmployeeDetails'], $val[0]) : array();
        }
        //  debug($arr_filterresult);
        echo json_encode($arr_filterresult);
    }

    public function listempleaves() {
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
                array('ISAutherizedby' => $cur_emp_key, 'LEAVESTATUS IN("CancellationOfAuthorized","Applied","CancellationOfApproved"  )'),
                array('APPROVEDBY' => $cur_emp_key, 'LEAVESTATUS IN ("Authorized","Cancellation Authorized" )'),
            )
        );

        $resp_empleaverequests = array();
        $resp_empleaverequests["rows"] = array();
        $count = $this->LeaveRequests->find("count", array("conditions" => $conditions));

        $arr_empleaverequests = $this->LeaveRequests->find("all", array(
            'fields' => $fields,
            'joins' => $joins,
            'conditions' => $conditions,
            'order' => array('LEAVEENTRYID DESC'),
            'limit' => intval($limit),
            'offset' => intval($ofst)
        ));

        foreach ($arr_empleaverequests as $key => $value) {
            $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveRequests"], $value["SalaryHeadItems"], $value[0]);
            if ($resp_empleaverequests["rows"][$key]['APPROVEDBY'] == $cur_emp_key) {
                $resp_empleaverequests["rows"][$key]['Action'] = 'Approve';
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
        $conditions = array(
            'OR' => array(
                array('APPROVEDBY' => $cur_emp_key, 'LEAVESTATUS IN ("Approved","Rejected")'),
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
            'order' => array('LEAVEENTRYID DESC'),
            'limit' => intval($limit),
            'offset' => intval($ofst)
        ));
        foreach ($arr_empleaverequests as $key => $value) {
            $resp_empleaverequests["rows"][$key] = array_merge($value["LeaveRequests"], $value["SalaryHeadItems"], $value[0]);
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
        if ($leaveentryId) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $cur_user_name = $this->Session->read("user_name");
            $this->set('curuserid', $cur_emp_key);
            $this->set('curusername', $cur_user_name);
            $this->set('leaveentryId', $leaveentryId);

            if ($this->checkIfLeaveRequestEditable($leaveentryId)) {
                //Update mode
                $this->set('mode', 'edit');

                $this->set('arr_reportingemployees', $this->getReportingEmployeeList(1, $leaveentryId));
                $this->loadEmpLeaveDetails($leaveentryId);

                //Fetch leave balance : On 21 Feb 2016
                $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
                $arr_leave_details = $this->LeaveRequests->find("first", array(
                    'fields' => 'TODATE,ISAutherized,ISAPPROVED,ISAutherizedby,APPROVEDBY,salary_head_item_fkey,EMP_fkey,LEAVESTATUS',
                    'conditions' => array('LEAVEENTRYID' => $leaveentryId)
                ));
                $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;
                $emp_fkey = isset($arr_leave_details['LeaveRequests']['EMP_fkey']) ? $arr_leave_details['LeaveRequests']['EMP_fkey'] : 0;
                $authorized = isset($arr_leave_details['LeaveRequests']['ISAutherizedby']) ? $arr_leave_details['LeaveRequests']['ISAutherizedby'] : 0;
                $authorized_name = $this->EmployeeDetails->find("all", array("conditions" => array("emp_pkey" => $authorized)));
                $this->set("authorized_name", $authorized_name);
                $arr_leave_details['authorized_name'] = $authorized_name['0']['EmployeeDetails']['first_name'] . ' ' . $authorized_name['0']['EmployeeDetails']['last_name'];

                //Check if leave is isnegative
                //On 09 Oct 2016
                //$arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, date('Y')) AS leave_balance");
                //$leavebalance = isset($arr_leavebalance[0][0]['leave_balance'])?$arr_leavebalance[0][0]['leave_balance']:0;
                //$this->set('leavebalance',$leavebalance);
                $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
                $arr_leave_policy = $this->LeavePolicy->find("all", array(
                    'fields' => 'ALLOW_NEGETIVE',
                    'conditions' => array(
                        'salary_head_item_fkey' => $salary_head_item_fkey,
                        'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $cur_emp_key . ')'
                    )
                        )
                );
                $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';
                $to_date = isset($arr_leave_details['LeaveRequests']['TODATE']) ? $arr_leave_details['LeaveRequests']['TODATE'] : '';
                $get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = '0' ");
                $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
                $to_year = date('Y', strtotime($to_date));
                $to_month = date('m', strtotime($to_date));
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
                //Ends

                $ISAutherized = $ISAPPROVED = 0;
                $myrole = 0;

                $leave_cancellation_msg = (isset($arr_leave_details['LeaveRequests']['LEAVESTATUS']) && in_array($arr_leave_details['LeaveRequests']['LEAVESTATUS'], array("Cancelled", "Cancellation Authorized"))) ? " Cancellation Request" : "";
                if ($arr_leave_details['LeaveRequests']['ISAutherizedby'] == $cur_emp_key) {
                    $this->set('head', "Authorize Leave$leave_cancellation_msg");
                    if ($arr_leave_details['LeaveRequests']['ISAutherized'] == 1 && !in_array($arr_leave_details['LeaveRequests']['LEAVESTATUS'], array('CancellationOfAuthorized', 'Cancellation Authorized',))) {
                        $ISAutherized = 1;
                    }
                    $myrole = 1;
                }
                if ($arr_leave_details['LeaveRequests']['APPROVEDBY'] == $cur_emp_key) {
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

                $this->set("myrole", $myrole);
                $this->set("ISAutherized", $ISAutherized);
                $this->set("ISAPPROVED", $ISAPPROVED);
                //$this->set("rejected",$rejected);
            } else {
                //View mode
                $this->set('mode', 'view');
                $this->set('head', 'Leave Details');
                $this->loadEmpLeaveDetails($leaveentryId);
            }
        }
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

    public function listleaves() {
        $this->autoRender = FALSE;
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $cur_emp_key = $this->Session->read("emp_fkey");

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
            'order' => array('LEAVEENTRYID DESC'),
            'limit' => intval($limit),
            'offset' => intval($ofst)
        ));
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
        $cur_emp_key = $this->Session->read("emp_fkey");
        if ($leaveentryId) {
            //Edit leave
            $this->set('head', 'Edit Leave');
            $this->set('leaveentryId', $leaveentryId);
            $this->loadLeaveDetails($leaveentryId);
        } else {
            //Request leave
            $this->set('head', 'Apply Leave');
            $this->set('leaveentryId', 0);

            $arr_leave_details = array();
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
            $arr_leaverequestkeys = array_keys($this->LeaveRequests->schema());
            foreach ($arr_leaverequestkeys as $key) {
                $arr_leave_details[$key] = '';
            }
            $this->set('arr_leave_details', $arr_leave_details);
        }
        $this->set('arr_leave_type', $this->getLeaveType());
        $this->set('arr_reportingemployees', $this->getReportingEmployeeList(0, 0));

        $arr_users = $this->LeaveRequests->query("select concat(first_name,' ',last_name) as name from emp_details where status = '1' and emp_pkey not in ('$cur_emp_key') ");
        $this->set("arr_users", $arr_users);
        //  debug($this->getReportingEmployeeList(0,0));
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
                "head_fkey in(select head_pkey from  salary_heads where lcase(item_type)='leave' and value='Y' and status=1) and salary_head_item_pkey IN(select salary_head_item_fkey from leavepolicy where LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey='$cur_emp_key'))"
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
            $arr_leave_details = $this->LeaveRequests->find('first', array(
                'fields' => 'LeaveRequests.*, (SELECT CONCAT(first_name, " ", last_name) FROM emp_details WHERE emp_pkey=ISAutherizedby) AS AUTHORIZEDBYNAME,(SELECT CONCAT(first_name, " ", last_name) FROM emp_details WHERE emp_pkey=APPROVEDBY) AS APPROVEDBYNAME',
                'conditions' => array(
                    'LEAVEENTRYID' => $leaveentryId
                )
                    )
            );
            $arr_leave_details = array_merge($arr_leave_details['LeaveRequests'], $arr_leave_details[0]);
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
    }

    public function loadEmpLeaveDetails($leaveentryId = 0) {
        if (isset($leaveentryId) && $leaveentryId != 0 && $leaveentryId != '') {
            $arr_leave_details = array();
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
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
            $emp_name = isset($arr_leave_details[0]['emp_name']) ? $arr_leave_details[0]['emp_name'] : '';
            $leave_type = isset($arr_leave_details['SalaryHeadItems']['leave_type']) ? $arr_leave_details['SalaryHeadItems']['leave_type'] : '';
            $approvedby = isset($arr_leave_details[0]['APPROVEDBYNAME']) ? $arr_leave_details[0]['APPROVEDBYNAME'] : '';
            //debug($arr_leave_details);die();
            $authorized = isset($arr_leave_details['LeaveRequests']['ISAutherizedby']) ? $arr_leave_details['LeaveRequests']['ISAutherizedby'] : 0;
            $authorized_name = $this->EmployeeDetails->find("all", array("conditions" => array("emp_pkey" => $authorized)));
            $arr_leave_details = $arr_leave_details['LeaveRequests'];
            $arr_leave_details['emp_name'] = $emp_name;
            $arr_leave_details['leave_type'] = $leave_type;
            $arr_leave_details['APPROVEDBYNAME'] = $approvedby;
            $arr_leave_details['authorized_name'] = $authorized_name['0']['EmployeeDetails']['first_name'] . ' ' . $authorized_name['0']['EmployeeDetails']['last_name'];
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
                    $arr_leave_details['actionType'] = 'Approve';
                } else {
                    $arr_leave_details['actionType'] = 'Authorize';
                }
            } else {
                $arr_leave_details['actionType'] = 'Authorize';
            }
            //return json_encode(array('success'=>true,'leaveentryId'=>$leaveentryId,'data'=>$arr_leave_details));
            $this->set('arr_leave_details', $arr_leave_details);
        }
    }

    public function checkIfLeaveRequestEditable($leaveentryId = 0) {
        $cur_emp_key = $this->Session->read("emp_fkey");

        $arr_leave_details = array();
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');

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
        $arr_leave_details = $arr_leave_details['LeaveRequests'];
        if (($arr_leave_details['LEAVESTATUS'] == 'Approved' || $arr_leave_details['LEAVESTATUS'] == 'Rejected' || $arr_leave_details['LEAVESTATUS'] == 'Cancelled') && ($cur_emp_key != $arr_leave_details['APPROVEDBY'])) {
            return false;
        } else {
            return true;
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
        //debug($arr_form_data);
        $leaveentryId = isset($arr_form_data['LEAVEENTRYID']) ? $arr_form_data['LEAVEENTRYID'] : 0;

        $data = array();

        $data['applied_date'] = date('Y-m-d');
        $data['EMP_fkey'] = $cur_emp_key;
        $data['FROMDATE'] = $arr_form_data['FROMDATE'];
        $data['FROMHALF'] = $arr_form_data['FROMHALF'];
        $data['TODATE'] = $arr_form_data['TODATE'];
        $data['TOHALF'] = $arr_form_data['TOHALF'];
        $data['ISAutherizedby'] = $arr_form_data['ISAutherizedby'];
        $data['APPROVEDBY'] = $arr_form_data['APPROVEDBY'];
        $data['Reason'] = $arr_form_data['Reason'];
        $data['contact_No'] = $arr_form_data['contact_No'];
        $data['contact_person'] = $arr_form_data['contact_person'];
        $data['leave_days'] = $arr_form_data['leave_days'];

        $fromdate = isset($arr_form_data['FROMDATE']) ? $arr_form_data['FROMDATE'] : '';
        $arr_attendance_register = $this->LeaveRequests->query("select count(*) cnt from attendance_register where month_year=date_format('$fromdate','%Y-%m') and isdelete='N' and emp_fkey= '$cur_emp_key' ");
        if (isset($arr_attendance_register) && $arr_attendance_register['0']['0']['cnt'] != 0) {
            $resp['message'] = "Leave Can not be saved , Attendance Verified for this Month ";
            $resp["success"] = false;
            return json_encode($resp);
        }

        if ($leaveentryId) {
            //Edit Leave
            $myLeaveAction = isset($arr_form_data['myLeaveAction']) ? $arr_form_data['myLeaveAction'] : '';
            $data['LEAVEENTRYID'] = $leaveentryId;



            if (isset($arr_attendance_register) && $arr_attendance_register['0']['0']['cnt'] != 0) {
                $message = 'Leave Can not be Changed, Attendance Verified ';
                $resp["success"] = false;
                $resp["leaveentryId"] = 0;
                $resp['warningmessage'] = '';
                $resp["message"] = "Leave Can not be Cancelled, Attendance Verified. ";
                return json_encode($resp);
            }
            $arr_data = array(
                //'LeaveRequests.salary_head_item_fkey' => "'".$data['salary_head_item_fkey']."'",
                'LeaveRequests.applied_date' => "'" . $data['applied_date'] . "'",
                'LeaveRequests.EMP_fkey' => "'" . $data['EMP_fkey'] . "'",
                'LeaveRequests.FROMDATE' => "'" . $data['FROMDATE'] . "'",
                'LeaveRequests.FROMHALF' => $data['FROMHALF'],
                'LeaveRequests.TODATE' => "'" . $data['TODATE'] . "'",
                'LeaveRequests.TOHALF' => $data['TOHALF'],
                'LeaveRequests.ISAutherizedby' => "'" . $data['ISAutherizedby'] . "'",
                'LeaveRequests.APPROVEDBY' => "'" . $data['APPROVEDBY'] . "'",
                'LeaveRequests.Reason' => "'" . $data['Reason'] . "'",
                'LeaveRequests.contact_No' => "'" . $data['contact_No'] . "'",
                'LeaveRequests.contact_person' => "'" . $data['contact_person'] . "'",
                    //'LeaveRequests.leave_days' => "'".$data['leave_days']."'",                             
            );

            $arr_leave_message = $this->LeaveRequests->find("first", array(
                'fields' => 'LEAVESTATUS',
                'conditions' => array('LEAVEENTRYID' => $leaveentryId)
            ));

            if ($myLeaveAction == 'Cancelled') {
                $arr_data['LeaveRequests.LEAVESTATUS'] = "'Cancelled'";
                $message = 'Leave cancellation successfull';
            } else if ($myLeaveAction == 'Cancellation Applied') {
//                debug($arr_leave_message);
                if ($arr_leave_message['LeaveRequests']['LEAVESTATUS'] == "Authorized") {
                    $arr_data['LeaveRequests.LEAVESTATUS'] = "'CancellationOfAuthorized'";
                } else {
                    $arr_data['LeaveRequests.LEAVESTATUS'] = "'CancellationOfApproved'";
                }

                $message = 'Leave cancellation successfull';
            } else {
                $arr_data['LeaveRequests.LEAVESTATUS'] = "'Applied'";
                $message = 'Leave details updated successfully';
            }

            $this->LeaveRequests->updateAll(
                    $arr_data, array('LeaveRequests.LEAVEENTRYID' => $leaveentryId)
            );

            /**
             * Send a leave cancellation request, if the current leave is already authorised / approved
             * On 11 March 2017
             */
            $applied_emps = isset($data['EMP_fkey']) ? $data['EMP_fkey'] : '';
            $applied_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$applied_emps'");

            if ($myLeaveAction == 'Cancellation Applied') {
                $data['FROMDATE'] = isset($arr_form_data['FROMDATE']) ? $arr_form_data['FROMDATE'] : '';
                $data['TODATE'] = isset($arr_form_data['TODATE']) ? $arr_form_data['TODATE'] : '';

                $auth_pkey = isset($data['ISAutherizedby']) ? $data['ISAutherizedby'] : '';
                $arr_authorized_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$auth_pkey'");
                $approved_pkey = isset($data['APPROVEDBY']) ? $data['APPROVEDBY'] : '';
                //$arr_approved_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$approved_pkey'");
                if (!empty($arr_authorized_emp['0']['emp_details']['email'])) {
                    $auth_email = array('Email' => $arr_authorized_emp['0']['emp_details']['email'], 'Name' => $arr_authorized_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                    $this->sendcancellationappliedmail($data, $auth_email);
                }
                $out = $this->callLeaveTransactionProcedure($leaveentryId);
            } else if ($myLeaveAction == 'Cancelled') {
                $data['FROMDATE'] = isset($arr_form_data['FROMDATE']) ? $arr_form_data['FROMDATE'] : '';
                $data['TODATE'] = isset($arr_form_data['TODATE']) ? $arr_form_data['TODATE'] : '';
                if ($applied_emp['0']['emp_details']['email']) {
                    $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $applied_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                    $this->sendcancelledmail($data, $auth_email);
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

            /* $fromtime       =   $arr_form_data['FROMTIME'];
              $totime         =   $arr_form_data['TOTIME'];
              //$fromtimehalf   =   $arr_form_data['FROMTIMEHALF'];
              //$totimehalf         =   $arr_form_data['TOTIMEHALF'];
              $data['FROMDATE'] = $arr_form_data['FROMDATE']." ".$fromtime."00";
              $data['TODATE'] = $arr_form_data['TODATE']." ".$totime."00"; */

            /*
             * Check if any leave is already applied in the same range, 
             * If yes then prompt user to delete the existing one and proceed
             * Starts
             */
            $from_date = date('Y-m-d', $leavefromtimestamp);
            $to_date = date('Y-m-d', $leavetotimestamp);
            $arr_count_leave_exists = $this->LeaveRequests->query("select count(*) AS COUNT from emp_leave_transactions where leave_date between '$from_date' and '$to_date' and LEAVESTATUS in ('Applied','Approved','Authorized' ) and LEAVEENTRYID in (select LEAVEENTRYID  from leaveentries where EMP_fkey = $cur_emp_key)");
            $count = isset($arr_count_leave_exists[0][0]['COUNT']) ? $arr_count_leave_exists[0][0]['COUNT'] : 0;
            if ($count > 0) {
                $resp["success"] = false;
                $resp["leaveentryId"] = 0;
                $resp['warningmessage'] = '';
                $resp["message"] = "Leave already existing in the range $from_date - $to_date. Please remove it, before applying.";
                return json_encode($resp);
            }
            //Ends
            //Apply New Leave
            $data['LEAVEENTRYID'] = 0;
            $this->LeaveRequests->save($data);
            $leaveentryId = $this->LeaveRequests->getLastInsertID();
            $message = 'Leave requisition successfull';

            $out = $this->callLeaveTransactionProcedure($leaveentryId);

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
            $arr_leave_details1 = $this->LeaveRequests->find("first", array(
                'fields' => 'LEAVEENTRYID,ISAutherizedby,applied_date,message,APPROVEDBY,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
                'conditions' => array('LEAVEENTRYID' => $leaveentryId)
            ));
            $outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? $arr_leave_details1['LeaveRequests'] : array();
            $auth_pkey = $outputParameter1['ISAutherizedby'];
            $approved_pkey = $outputParameter1['APPROVEDBY'];
            $arr_authorized_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$auth_pkey'");
            $arr_approved_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$approved_pkey'");
            $apprvd_email = isset($arr_approved_emp['0']['emp_details']['email']) ? $arr_approved_emp['0']['emp_details']['email'] : 'sanjundev@gmail.com';
            $applied_emps = $outputParameter1['EMP_fkey'];
            $applied_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$applied_emps'");
            $auth_email = array('Email' => $arr_authorized_emp['0']['emp_details']['email'], 'Name' => $arr_authorized_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
            if ($arr_authorized_emp['0']['emp_details']['email']) {
                $this->sendauthorizationmail($outputParameter1, $auth_email);
            }
        }
        $resp["success"] = true;
        $resp["leaveentryId"] = $leaveentryId;
        return json_encode($resp);
    }

    /*
     * Grand Leave
     * On 22 March 2015
     */

    public function grandLeave() {
        $this->autoRender = FALSE;

        $arr_form_data = $this->request->data;
        $actionType = isset($arr_form_data['actionType']) ? $arr_form_data['actionType'] : '';
        $leaveentryId = isset($arr_form_data['LEAVEENTRYID']) ? $arr_form_data['LEAVEENTRYID'] : 0;

        $resp = array();

        $cur_emp_key = $this->Session->read("emp_fkey");

        if ($cur_emp_key != 0 && $leaveentryId != 0) {
            $this->LeaveRequests->useDbConfig = $this->Session->read('ds');

            //Check if leave is isnegative
            //On 09 Oct 2016
            //Check if leave balance
            //On 24 Sep 2016
            //$arr_leave_balance = $this->getLeaveBalanceForAuthOrApproval($leaveentryId);
            //$leave_days = isset($arr_leave_balance[0])?$arr_leave_balance[0]:0;
            //$monthly_balance = isset($arr_leave_balance[1])?$arr_leave_balance[1]:0;
            //if($monthly_balance - $leave_days < 0){
            //    $resp["success"] = false;
            //    $resp["message"] = "Insufficient leave balance for the month, $monthly_balance available";
            //    return json_encode($resp);
            //}

            $arr_leave_details = $this->LeaveRequests->find("first", array(
                'fields' => 'salary_head_item_fkey,EMP_fkey,LEAVESTATUS,ISAutherizedby,APPROVEDBY,FROMDATE,TODATE,applied_date,leave_days,message',
                'conditions' => array('LEAVEENTRYID' => $leaveentryId)
            ));

            /**
             * Authorize / Approve Leave cancellation request
             * On 11 March 2017
             */
            $leave_status = isset($arr_leave_details['LeaveRequests']['LEAVESTATUS']) ? $arr_leave_details['LeaveRequests']['LEAVESTATUS'] : '';
            if ($leave_status == "Cancellation Applied") {

                $ISAutherizedby = isset($arr_leave_details['LeaveRequests']['ISAutherizedby']) ? $arr_leave_details['LeaveRequests']['ISAutherizedby'] : '';
                $APPROVEDBY = isset($arr_leave_details['LeaveRequests']['APPROVEDBY']) ? $arr_leave_details['LeaveRequests']['APPROVEDBY'] : '';
                if ($cur_emp_key == $ISAutherizedby || $cur_emp_key == $APPROVEDBY) {
                    if ($cur_emp_key == $ISAutherizedby) {
                        $actionType = "Cancellation Authorized";
                    }
                    if ($cur_emp_key == $APPROVEDBY) {
                        $actionType = "Cancellation Approved";
                    }
                } else {
                    $actionType = "";
                }
            } else if ($leave_status == "Cancellation Authorized") {
                $APPROVEDBY = isset($arr_leave_details['LeaveRequests']['APPROVEDBY']) ? $arr_leave_details['LeaveRequests']['APPROVEDBY'] : '';
                if ($cur_emp_key == $APPROVEDBY) {
                    $actionType = "Cancellation Approved";
                } else {
                    $actionType = "";
                }
            }
            //Ends
            else {

                //Leave balance checking
                $salary_head_item_fkey = isset($arr_leave_details['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_details['LeaveRequests']['salary_head_item_fkey'] : 0;

                $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
                $arr_leave_policy = $this->LeavePolicy->find("all", array(
                    'fields' => 'ALLOW_NEGETIVE',
                    'conditions' => array(
                        'salary_head_item_fkey' => $salary_head_item_fkey,
                        'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $cur_emp_key . ')'
                    )
                        )
                );
                $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';
                if ($actionType != 'Reject') {
                    if (strtoupper($allow_negative) == 'Y') {
                        //Check if leave balance for year
                        $arr_leave_balance = $this->getYearlyLeaveBalanceForAuthOrApproval($leaveentryId);
                        $leave_days = isset($arr_leave_balance[0]) ? $arr_leave_balance[0] : 0;
                        $yearly_balance = isset($arr_leave_balance[1]) ? $arr_leave_balance[1] : 0;
                        if ($yearly_balance - $leave_days < 0) {
                            $resp["success"] = false;
                            $resp["message"] = "Insufficient leave balance for the month, $yearly_balance available";
                            return json_encode($resp);
                        }
                    } else {
                        //Check if leave balance for month
                        //On 24 Sep 2016
                        $arr_leave_balance = $this->getLeaveBalanceForAuthOrApproval($leaveentryId);
                        $leave_days = isset($arr_leave_balance[0]) ? $arr_leave_balance[0] : 0;
                        $monthly_balance = isset($arr_leave_balance[1]) ? $arr_leave_balance[1] : 0;
                        if ($monthly_balance - $leave_days < 0) {
                            $resp["success"] = false;
                            $resp["message"] = "Insufficient leave balance for the month, $monthly_balance available";
                            return json_encode($resp);
                        }
                    }
                }
            }
            //Ends

            $arr_leave_details1 = $this->LeaveRequests->find("first", array(
                'fields' => 'LEAVEENTRYID,ISAutherizedby,applied_date,message,APPROVEDBY,EMP_fkey,FROMDATE,FROMHALF,TODATE,TOHALF,leave_days,LEAVESTATUS',
                'conditions' => array('LEAVEENTRYID' => $leaveentryId)
            ));
            $outputParameter1 = isset($arr_leave_details1['LeaveRequests']) ? $arr_leave_details1['LeaveRequests'] : array();
            $auth_pkey = $outputParameter1['ISAutherizedby'];
            $approved_pkey = $outputParameter1['APPROVEDBY'];
            $arr_approved_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$approved_pkey'");

//            if (isset($arr_approved_emp['0']['emp_details']['email'])) {
                $apprvd_email = $arr_approved_emp['0']['emp_details']['email'];
                $applied_emps = $outputParameter1['EMP_fkey'];
                $applied_emp = $this->LeaveRequests->query("select first_name,email from emp_details where emp_pkey = '$applied_emps'");

                $data = array();
                $data['APPROVEDBY'] = $arr_form_data['APPROVEDBY'];
                $curdate = date('Y-m-d');
                if ($actionType == 'Authorize') {
                    $data['LEAVESTATUS'] = "'Authorized'";
                    $outputParameter1['action'] = "Authorized";
                    $data['Autherized_date'] = "'" . $curdate . "'";
                    $data['ISAutherized'] = 1;
                    $auth_email = array('Email' => $arr_approved_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                    if ($arr_approved_emp['0']['emp_details']['email']) {
                        $this->sendapprovemail($outputParameter1, $auth_email);
                    }
                } else if ($actionType == 'Approve') {
                    $data['LEAVESTATUS'] = "'Approved'";
                    $outputParameter1['action'] = "Approved";
                    $data['APPROVED_date'] = "'" . $curdate . "'";
                    $data['ISAPPROVED'] = 1;
                    if ($applied_emp['0']['emp_details']['email']) {
                        $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                        $this->sendapprovedmail($outputParameter1, $auth_email);
                    }
                } else if ($actionType == 'Reject') {
                    if ($leave_status == "CancellationOfAuthorized" || $leave_status == "CancellationOfApproved") {
                        $data['LEAVESTATUS'] = "'Authorized'";
                        $outputParameter1['action'] = "Authorized";
                        $data['Autherized_date'] = "'" . $curdate . "'";
                        $data['ISAutherized'] = 1;
                        $data['APPROVED_date'] = "''";
                        $data['ISAPPROVED'] = 0;
                        $auth_email = array('Email' => $arr_approved_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                        if ($arr_approved_emp['0']['emp_details']['email']) {
                            $this->sendapprovemail($outputParameter1, $auth_email);
                        }
                    } else {
                        $data['LEAVESTATUS'] = "'Rejected'";
                        $data['Autherized_date'] = "'" . $curdate . "'";
                        $outputParameter1['action'] = "Rejected";
                        if ($applied_emp['0']['emp_details']['email']) {
                            $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                            $this->sendrejectedsmail($outputParameter1, $auth_email);
                        }
                    }
                } else if ($actionType == 'Approve Cancellation') {
                    $actionType = "Cancellation Approved";
                    $data['LEAVESTATUS'] = "'Cancellation Approved'";
                    $outputParameter1['action'] = "Cancellation Approved";
                    $data['Autherized_date'] = "'" . $curdate . "'";
                    $data['ISAutherized'] = 1;
                    $data['message'] = '"Leave cancellation Approved"';
                    $auth_email = array('Email' => $arr_approved_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                    if ($arr_approved_emp['0']['emp_details']['email']) {
                        $this->sendapprovemail($outputParameter1, $auth_email);
                    }
                } else if ($actionType == 'Authorize Cancellation') {
                    $actionType = "Cancellation Authorize";
                    $data['LEAVESTATUS'] = "'Cancellation Authorized'";
                    $outputParameter1['action'] = "Cancellation Authorized";
                    $data['Autherized_date'] = "'" . $curdate . "'";
                    $data['ISAutherized'] = 1;
                    $data['message'] = '"Leave cancellation Authorized"';
                    $auth_email = array('Email' => $arr_approved_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                    if ($arr_approved_emp['0']['emp_details']['email']) {
                        $this->sendapprovemail($outputParameter1, $auth_email);
                    }
                } else if ($actionType == 'Cancelled') {
                    $actionType = "Cancellation Approve";
                    $data['LEAVESTATUS'] = "'Cancelled'";
                    $outputParameter1['action'] = "Cancellation Approved";
                    $data['APPROVED_date'] = "'" . $curdate . "'";
                    $data['ISAPPROVED'] = 1;
                    $data['message'] = '"Leave cancellation Approved"';
                    if ($applied_emp['0']['emp_details']['email']) {
                        $auth_email = array('Email' => $applied_emp['0']['emp_details']['email'], 'Name' => $arr_approved_emp['0']['emp_details']['first_name'], 'Appliedby' => $applied_emp['0']['emp_details']['first_name']);
                        $this->sendapprovedmail($outputParameter1, $auth_email);
                    }
                } else {
                    $resp["success"] = false;
                    $resp["leaveentryId"] = $leaveentryId;
                    $resp["message"] = 'Leave processing failed';
                    return json_encode($resp);
                }
//            }

            $arr_old_leave_remarks = $this->LeaveRequests->find("first", array(
                'fields' => 'REMARKS',
                'conditions' => array('LEAVEENTRYID' => $leaveentryId)
            ));
            $oldremarks = isset($arr_old_leave_remarks['LeaveRequests']['REMARKS']) ? $arr_old_leave_remarks['LeaveRequests']['REMARKS'] : '';
            $resp["success"] = true;
            $data['REMARKS'] = "'" . $oldremarks . "<br>" . $arr_form_data['REMARKS'] . "'";
            
            try {
                $this->LeaveRequests->updateAll(
                        $data, array('LeaveRequests.LEAVEENTRYID' => $leaveentryId)
                );
                $resp["success"] = true;
            } catch (Exception $ex) {
                echo $ex->getMessage();
                $resp["success"] = false;
            }

            // call Procedure 
            try {
                $out = $this->callLeaveTransactionProcedure($leaveentryId);
                $resp["success"] = true;
            } catch (Exception $ex) {
                echo $ex->getMessage();
                $resp["success"] = false;
            }


            $resp["leaveentryId"] = $leaveentryId;
            $resp["message"] = 'Leave ' . $actionType . ' successfully';
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

    public function GetLeaveBalance($leavebalance = "", $end_date = "") {

        $this->autoRender = false;
        $salary_head_item_fkey = $leavebalance;


        $cur_emp_key = $this->Session->read("emp_fkey");
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');

        $year = date('Y', strtotime($end_date));
        $months = date('m', strtotime($end_date));
        $from = date("Y-m-01", strtotime($end_date));
        $to_date = date("Y-m-t", strtotime($end_date));
        $get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = '0' "); // removed this line code - and '$end_date' between start_month and end_month
        $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
        $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$cur_emp_key','$salary_head_item_fkey','$finyear') as LeaveBalance");
        //echo "select leave_balance_inthe_year_fn('$cur_emp_key','$salary_head_item_fkey','$finyear') as LeaveBalance";
        $this->set('lbalance', $lbalance);
        //$lbalance1 = $this->LeaveRequests->query("select alloted_leave_forthe_month from leavepolicy where LEAVEPOLICY_GROUP_ID in ( select LEAVEPOLICY_GROUP_ID from emp_proff where emp_fkey = $cur_emp_key) and salary_head_item_fkey = '$leavebalance' ");
        $lmonthbalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn('$cur_emp_key','$salary_head_item_fkey','$months','$finyear') as LeaveBalancemonth");
        //echo "select leave_balance_inthe_month_fn('$cur_emp_key','$salary_head_item_fkey','$months','$finyear') as LeaveBalancemonth";die();
        $this->set('lmonthbalance', $lmonthbalance);
        $yearly_balance = isset($lbalance['0']['0']['LeaveBalance']) ? $lbalance['0']['0']['LeaveBalance'] : 0;
        $monthly_balance = isset($lmonthbalance['0']['0']['LeaveBalancemonth']) ? $lmonthbalance['0']['0']['LeaveBalancemonth'] : 0;

        //Check if leave is isnegative
        //On 09 Oct 2016


        $this->LeavePolicy->useDbConfig = $this->Session->read('ds');
        $arr_leave_count_takens = $this->LeavePolicy->query("SELECT COUNT(*) cnt FROM emp_leave_transactions JOIN leaveentries ON (leaveentries.LEAVEENTRYID = emp_leave_transactions.LEAVEENTRYID) WHERE emp_leave_transactions.Leavestatus IN ('Approved','Authorized') and leaveentries.EMP_fkey IN ('$cur_emp_key') and emp_leave_transactions.leave_date BETWEEN '$from' and '$to_date' and leaveentries.salary_head_item_fkey IN ('$salary_head_item_fkey') ");

        $arr_leave_policy = $this->LeavePolicy->find("all", array(
            'fields' => 'ALLOW_NEGETIVE',
            'conditions' => array(
                'salary_head_item_fkey' => $salary_head_item_fkey,
                'LEAVEPOLICY_GROUP_ID IN (SELECT LEAVEPOLICY_GROUP_ID FROM emp_proff WHERE emp_fkey=' . $cur_emp_key . ')'
            )
                )
        );


//        debug($arr_leave_count_takens);
        $allow_negative = isset($arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE']) ? $arr_leave_policy[0]['LeavePolicy']['ALLOW_NEGETIVE'] : '';
        //Ends

        echo json_encode(array(
            'yearly_balance' => $yearly_balance,
            'monthly_balance' => ($monthly_balance > 0) ? $monthly_balance - (isset($arr_leave_count_takens['0']['0']['cnt']) ? $arr_leave_count_takens['0']['0']['cnt'] : 0) : 0,
            'monthly_taken' => isset($arr_leave_count_takens['0']['0']['cnt']) ? $arr_leave_count_takens['0']['0']['cnt'] : 0,
            'allow_negative' => strtoupper($allow_negative)
        ));
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
                                'fields' => 'LEAVESTATUS,FROMDATE,FROMHALF,TODATE,TOHALF,REMARKS',
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
            $get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where '$to_date' between start_month and end_month");
            $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
            $lbalance = $this->LeaveRequests->query("select leave_balance_inthe_year_fn('$emp_fkey','$salary_head_item_fkey','$finyear') as LeaveBalance");
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
        $from_month = date('m', strtotime($from_date));
        $from_year = date('Y', strtotime($from_date));
        $get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = '0' "); // '$from_date' between start_month and end_month
        $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
        $lmonthbalance = $this->LeaveRequests->query("select leave_balance_inthe_month_fn('$emp_fkey','$salary_head_item_fkey','$from_month','$finyear') as LeaveBalancemonth");
        $monthly_balance = isset($lmonthbalance['0']['0']['LeaveBalancemonth']) ? $lmonthbalance['0']['0']['LeaveBalancemonth'] : 0;

        return array($leave_days, $monthly_balance);
    }

    //Ends
    /*
     * Get yearly leave balance for auth/approve
     * On 24 Sep 2016
     */
    public function getYearlyLeaveBalanceForAuthOrApproval($leaveentryId = "") {
        $this->LeaveRequests->useDbConfig = $this->Session->read('ds');
        $arr_leave_entry = $this->LeaveRequests->find('first', array(
            'conditions' => array('LeaveRequests.LEAVEENTRYID' => $leaveentryId)
        ));
        $salary_head_item_fkey = isset($arr_leave_entry['LeaveRequests']['salary_head_item_fkey']) ? $arr_leave_entry['LeaveRequests']['salary_head_item_fkey'] : '';
        $emp_fkey = isset($arr_leave_entry['LeaveRequests']['EMP_fkey']) ? $arr_leave_entry['LeaveRequests']['EMP_fkey'] : '';
        $leave_days = isset($arr_leave_entry['LeaveRequests']['leave_days']) ? $arr_leave_entry['LeaveRequests']['leave_days'] : 0;
        $to_date = isset($arr_leave_entry['LeaveRequests']['TODATE']) ? $arr_leave_entry['LeaveRequests']['TODATE'] : '';
        $to_year = date('Y', strtotime($to_date));
        $get_finyear = $this->LeaveRequests->query("select fin_year from fin_year where Year_status = 'OPEN' and vattr1 = '0' ");
        $finyear = isset($get_finyear['0']['fin_year']['fin_year']) ? $get_finyear['0']['fin_year']['fin_year'] : date('Y');
        $arr_leavebalance = $this->LeaveRequests->query("SELECT leave_balance_inthe_year_fn($emp_fkey, $salary_head_item_fkey, '$finyear') AS leave_balance");
        $leavebalance = isset($arr_leavebalance[0][0]['leave_balance']) ? $arr_leavebalance[0][0]['leave_balance'] : 0;

        return array($leave_days, $leavebalance);
    }

    function sendauthorizationmail($output, $auth) {
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $auth['Name'];
        $applied_emp = $auth['Appliedby'];
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset($output['message']) ? $output['message'] : "";
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
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
            $mail->addAddress($email);     // Add a recipient
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->AddAttachment('<?php echo $this->webroot; ?>');
            $mail->AddEmbeddedImage('<?php echo $this->webroot; ?>/files/mpm.png', 'mpm');
            $mail->Subject = "MyPayrollMaster - Leave Authorization Request";
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
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Leave Authorization Request from <font style="color:#fff;">' . $applied_emp . '</font> </h1>
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

    function sendapprovemail($output, $auth) {
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $auth['Name'];
        $applied_emp = $auth['Appliedby'];
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset($output['message']) ? $output['message'] : "";
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
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
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
                                                    <h1 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Leave ' . $action . ' Request from <font style="color:#fff;">' . $applied_emp . '</font> </h1>
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
        $auth_name = $auth['Name'];
        $applied_emp = $auth['Appliedby'];
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset($output['message']) ? $output['message'] : "";
        $action = $output['action'];
        $action_msg = ($action != "Cancellation Approved") ? "has been Approved" : $action;
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
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
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
                                                    <h2 style="font-family:HelveticaNeue-Light,arial,sans-serif;font-size:48px;color:#fff;line-height:48px;font-weight:bold;margin:0;padding:0">Your Leave ' . $action_msg . '</h2>
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

//            return $mail->send();
        } catch (Exception $ex) {
            return false;
        }
        return false;
    }

    function sendrejectedsmail($output, $auth) {
        $this->autoRender = FALSE;
        $email = $auth['Email'];
        $auth_name = $auth['Name'];
        $applied_emp = $auth['Appliedby'];
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset($output['message']) ? $output['message'] : "";
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
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
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
        $auth_name = $auth['Name'];
        $applied_emp = $auth['Appliedby'];
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset($output['message']) ? $output['message'] : "";
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
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
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
        $auth_name = $auth['Name'];
        $applied_emp = $auth['Appliedby'];
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset($output['message']) ? $output['message'] : "";
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
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
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
        $auth_name = $auth['Name'];
        $applied_emp = $auth['Appliedby'];
        $leavedays = $output['leave_days'];
        $leavefrom = $output['FROMDATE'];
        $leaveend = $output['TODATE'];
        $applied = $output['applied_date'];
        $message = isset($output['message']) ? $output['message'] : "";
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
            $mail->Username = 'info@mypayrollmaster.in';                 // SMTP username
            $mail->Password = 'welcome123';                           // SMTP password
            $mail->SMTPSecure = 'tls';                           // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587; //25;                                    // TCP port to connect to

            $mail->setFrom('info@mypayrollmaster.in', 'My Payroll Master');
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
    function callLeaveTransactionProcedure($leaveentryId = '') {
        if (!empty($leaveentryId)) {
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

    //Ends
}
