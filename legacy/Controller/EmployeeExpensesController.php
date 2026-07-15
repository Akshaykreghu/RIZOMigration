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
class EmployeeExpensesController extends AppController
{

    /**
     * Controller name
     *
     * @var string
     */
    //public $layout = "default";
    public $name = 'EmployeeExpenses';
    public $datatable;

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('CentralControl', 'UserCredentials', 'EmployeeDetails', 'Designation', 'EmployeeProfessionalDetails', 'Departments', 'Grades', 'Verticals', 'Units', 'TaxHead', 'EmployeeCTC', 'EmployeeLoan', 'EmployeeExpenses', 'SalarySlip');
    public $components = array('MasterdataManagement');

    /*
     * Employees landing view
     */

    public function index()
    {
        $this->autoRender = FALSE;
        $user_group = $this->Session->read("user_group");
        if ($user_group == '1') {
            //Admin view
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $active_emp_count = $this->EmployeeDetails->find('count', array('conditions' => array('status' => 1)));
            $this->set('active_emp_count', $active_emp_count);

            //Fetch Units for the company
            $arr_branches = $this->MasterdataManagement->getBranchesListForCombo();
            $this->set('arr_branches', $arr_branches);
            $this->render('index');
        } else if ($user_group == '2') {
            //Employee View
            $emp_fkey = $this->Session->read("emp_fkey");
            $this->setup($emp_fkey);
            $this->render('setup');
        }
    }

    /*
     * List employees for Ext JS framework
     * Added on 06 April 2015
     */

    public function listemployees()
    {
        $this->autoRender = FALSE;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];

        $ofst = ($page - 1) * $limit;

        $conditions = array();

        $fields = 'emp_pkey,EmployeeProfessionalDetails.emp_company_id,CONCAT_WS(" ",first_name,last_name) as name,EmployeeProfessionalDetails.designation,EmployeeProfessionalDetails.joining_date,mobile_no';
        $joins = array(
            array(
                'table' => 'emp_proff',
                'alias' => 'EmployeeProfessionalDetails',
                'type' => 'LEFT',
                'foreignKey' => false,
                'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
            )
        );
        $conditions[] = array('status' => 1);

        //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
        $user_group = $this->Session->read('user_group');
        if ($user_group == 2) {
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
            $conditions[] = array("EmployeeDetails.branch_code" => $cur_emp_branch);
        }
        //employee branch wise sorting ends here

        $this->datatable["conditions"] = $conditions;
        $resp_emp = array();
        $resp_emp["rows"] = array();
        $count = $this->EmployeeDetails->find("count", array("conditions" => $conditions));
        $arr_emp = $this->EmployeeDetails->find("all", array('fields' => $fields, 'joins' => $joins, "conditions" => $conditions, 'limit' => intval($limit), 'offset' => intval($ofst)));
        foreach ($arr_emp as $key => $value) {
            $resp_emp["rows"][$key] = array_merge($value["EmployeeDetails"], $value["EmployeeProfessionalDetails"], $value[0]);
        }
        $resp_emp["total"] = $count;
        echo json_encode($resp_emp);
    }

    /*
     * Show tax Head Details form
     */

    //popup for save and update
    public function form()
    {
        $this->UserCredentials->useDbConfig = $this->Session->read('ds');
        $this->Units->useDbConfig = $this->Session->read('ds');
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        // $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => array('status' => 1))));
        //Company ID Added by ***ARUL P DAS on 20/12/2019

        $user_group = $this->Session->read('user_group');
        //        if ($user_group == 2) {
        //            $cur_emp_key = $this->Session->read("emp_fkey");
        //            $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        //            $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
        //            $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
        //            $conditions = " and emp_details.branch_code ='" . $cur_emp_branch . "'";
        //        } else {
        //            $conditions = "";
        //        }
        //employee branch wise sorting ends here
        if ($user_group != 2) { //This is admin side code
            $emp_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 order by first_name ASC');
            $this->set("arr_employees", $emp_list);
        } else { //This is employee side code
            $cur_emp_key = $this->Session->read("emp_fkey");
            $this->set("cur_emp_key", $cur_emp_key);

            $arr_company = $this->EmployeeDetails->find(
                'first',
                array(
                    'fields' => 'company_code',
                    'conditions' => array(
                        'status' => 1,
                        'emp_pkey' => $cur_emp_key
                    )
                )
            );
            $exp_list = $this->EmployeeDetails->query('select expense_type_code,expense_type_name,expense_type_pkey from expense_type where status=1 order by expense_type_name ASC');
            $this->set("expense_list", $exp_list);
            $company_code = $arr_company['EmployeeDetails']['company_code'];
            //            $emp_list = $this->EmployeeDetails->query('select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1' . $conditions);

            $auth_list_array = $this->EmployeeDetails->query("select leave_auth_apr_person_fn('" . $company_code . "'," . $cur_emp_key . ",'auth') as resps");
            $auth_keys = $auth_list_array[0][0]["resps"];

            $auth_query = 'select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 ';
            $auth_query .= ($auth_keys) ? ' and emp_pkey in (' . $auth_keys . ') ' : ' and emp_pkey != ' . $cur_emp_key;

            $auth_emp_list = $this->EmployeeDetails->query($auth_query);
            $this->set("auth_employees", $auth_emp_list);


            $apr_list_array = $this->EmployeeDetails->query("select leave_auth_apr_person_fn('" . $company_code . "'," . $cur_emp_key . ",'api') as resps");
            $apr_keys = $apr_list_array[0][0]["resps"];

            $apr_query = 'select emp_pkey,first_name,last_name,emp_company_id from emp_details join emp_proff where emp_details.emp_pkey=emp_proff.emp_fkey and emp_details.status=1 ';
            $apr_query .= ($apr_keys) ? 'and emp_pkey!=' . $cur_emp_key . ' and emp_pkey in (' . $apr_keys . ')' : ' and emp_pkey != ' . $cur_emp_key;

            $emp_list = $this->EmployeeDetails->query($apr_query);
            $this->set("apr_employees", $emp_list);
        }
        $data['emp_expenses_pkey'] = 0;
        $data['emp_fkey'] = '';
        $data['expenses_amount'] = "";
        $data['affected_month'] = "";
        $data['remarks'] = "";
        $data['is_credited'] = "";
        if (isset($_REQUEST['emp_expenses_pkey']) && $_REQUEST['emp_expenses_pkey'] != 0) {
            $data_db = $this->EmployeeExpenses->find("first", array("conditions" => array("emp_expenses_pkey" => $_REQUEST['emp_expenses_pkey'])));
            // debug($data_db);
            // exit();
            $data = $data_db['EmployeeExpenses'];
            // debug($data);
        }
        //  debug($data);
        $this->layout = null;
        $this->set("data", $data);

        $exp_list = $this->EmployeeDetails->query('select expense_type_code,expense_type_name,expense_type_pkey from expense_type where status=1 order by expense_type_name ASC');
        $this->set("expense_list", $exp_list);
        
    }

    //checking salary slip
    public function salarycheck()
    {
        $arr_request = $this->request->data;
        // debug($arr_request);
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');
        //        debug($arr_request);
        $empfkey = $arr_request['emp_id'];
         //$form_month = $arr_request['date'];
        $form_month = date('Y-m-d', strtotime($arr_request['date']));
        $data = array();
        //DOJ checking added by ***ARUL P DAS on 18/3/2020
        $join = array(
            array(
                "table" => "emp_details",
                "type" => "LEFT",
                "conditions" => "emp_details.emp_pkey=EmployeeProfessionalDetails.emp_fkey"
            )
        );
        $doj_array = $this->EmployeeProfessionalDetails->find("first", array("fields" => "joining_date", "conditions" => array("emp_fkey" => $empfkey, "emp_details.status" => 1), "joins" => $join));
        $doj = $doj_array["EmployeeProfessionalDetails"]["joining_date"];
        //        debug($doj);
        //        debug($form_month);
        if (isset($form_month) && $form_month != "") {
            if ($form_month < $doj) {
                $data['msg'] = "Employee Not Exist in this Date";
                $data['rows'] = array(0 => "Employee exist");
            } else {
                $month = explode("-", $form_month);
                $year = $month[0];
                $mon = $month[1];
                $set_month = $year . '-' . $mon;
                $arr_salary_month_check = $this->EmployeeExpenses->query("select emp_salary_slip.salary_amount FROM emp_salary_slip WHERE month_year= '$set_month' AND emp_fkey= '$empfkey' and end_date_effective is null");
                // debug($arr_salary_month_check);
                //$extingsalary=$arr_salary_month_check[0]['emp_salary_slip']['salary_amount'];
                //debug($extingsalary);
                $this->set('arr_salary_month_check', $arr_salary_month_check);
                $data['msg'] = "Salary already processed";
                $data['rows'] = $arr_salary_month_check;
            }
        }
        echo json_encode($data);
    }

    //save 
    public function employeeloansave()
    {
        $this->autoRender = FALSE;
        $this->layout = null;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)
        $arr_form_data = $this->request->data;
        $arr_form_data['created_by'] = $this->Session->read('login_user_id');
        if (isset($arr_form_data['authorized_by'])) {
           // $arr_form_data['expense_date'] =$arr_form_data['affected_month'];
          $arr_form_data['expense_date'] = date('Y-m-d H:i:s', strtotime($arr_form_data['affected_month'])); 
          $arr_form_data['affected_month'] = date('Y-m-d', strtotime($arr_form_data['affected_month']));
        } else {
            $arr_form_data['authorized_date'] = date('Y-m-d');
            // $arr_form_data['remarks_auth'] = "Uploaded By Admin";
            $arr_form_data['remarks_approved'] = "Approved By Admin";
            $arr_form_data['remarks_auth'] = "Authorized By Admin";
            $arr_form_data['remarks'] = $arr_form_data['remarks'];
            //$arr_form_data['expense_date'] = $arr_form_data['affected_month'];
             $arr_form_data['expense_date'] = date('Y-m-d', strtotime($arr_form_data['affected_month']));
            $arr_form_data['affected_month'] = date('Y-m-d', strtotime($arr_form_data['affected_month']));
            $arr_form_data['expense_status'] = "Approved";
            $arr_form_data['approved_date'] = date('Y-m-d');
            $msg = "Expense Approved Successfully!!!";
        }
        $result = $this->EmployeeExpenses->save($arr_form_data);
             
        $lastkey_array = $this->EmployeeExpenses->find('first', array('fields' => 'emp_expenses_pkey', 'order' => 'emp_expenses_pkey DESC'));
        $emp_pkey = $arr_form_data['emp_fkey'];
        $arr_id_key = $lastkey_array["EmployeeExpenses"]["emp_expenses_pkey"];
        //        $dirsep = "/";
        $companycode = strtolower($this->Session->read('company_code'));
        $target_dir = "/var/www/html/mpm/expense/" . $companycode . "/";
        $arr_form_data2 = array();
        try {
            //            $cwd_path = getcwd() . $dirsep;
            //            debug($cwd_path);
            //            $file_webroot_path = "expense" . $dirsep . $companycode . $dirsep;
            //            debug($file_webroot_path);
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
            if ($_FILES['image']['size'] > 5000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $finfo->file($_FILES['image']['tmp_name']),
                array(
                    'jpg' => 'image/jpg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'pdf' => 'application/pdf',
                ),
                true
            )) {
                throw new RuntimeException('Invalid file format.');
            }

            //            $filename = sprintf('%s.%s', sha1_file($_FILES['image']['tmp_name']), $ext);
            $filename = $emp_pkey . '_' . $arr_id_key . '_' . basename($_FILES["image"]["name"]);

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename)) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            $arr_form_data2["image"] = "'" . $filename . "'";
            //                $this->Session->write('company_logo', $file_webroot_path . $filename);
        } catch (RuntimeException $e) {
        }

        //        $file_webroot_path = "files/companylogos/". $companycode ."/";
        //        debug($arr_form_data);
        //        $this->EmployeeExpenses->id($arr_id_key);
        if (count($arr_form_data2) > 0) {
            $result = $this->EmployeeExpenses->updateAll($arr_form_data2, array('emp_expenses_pkey' => $arr_id_key));
        }
        $resp = array();
        $resp["success"] = true;
        $resp["msg"] = isset($msg) ? $msg : "Employee Expenses Added Successfully";
        echo json_encode($resp);
    }

    //list 

//   public function employeelist()
// {
//     $this->autoRender = false;

//     $arr_request_data = $this->request->data;

//     $emp_fkey_request = !empty($arr_request_data['employee']) 
//         ? (int)$arr_request_data['employee'] 
//         : 0;

//     $branch_code_request = !empty($arr_request_data['branch']) 
//         ? $arr_request_data['branch'] 
//         : '';

//     $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
//     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

//     /* =============================
//        SAFE PAGINATION
//     ============================== */

//     $limit = isset($_REQUEST['rows']) ? (int)$_REQUEST['rows'] : 10;
//     $page  = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

//     if ($limit <= 0) {
//         $limit = 10;
//     }

//     if ($limit > 100) {
//         $limit = 100;
//     }

//     $ofst = ($page - 1) * $limit;

//     $emp_condition = '';
//     $branch_condition = '';

//     /* =============================
//        EMPLOYEE FILTER
//     ============================== */

//     if ($emp_fkey_request > 0) {
//         $emp_condition .= " AND au.emp_fkey = " . $emp_fkey_request;
//     }

//     if ($branch_code_request != '') {
//         $branch_condition .= " AND ed.branch_code = '" . addslashes($branch_code_request) . "' ";
//     }

//     /* =============================
//        SESSION VALUES
//     ============================== */

//     $current_emp_pkey = $this->Session->read('emp_fkey');
//     $user_group = $this->Session->read('user_group');
//     $company_code = strtoupper($this->Session->read('company_code'));

//     /* =============================
//        USER GROUP 2 (MANAGER)
//     ============================== */

//     if ($user_group == '2') {

//         /* ===== COMPANY ABSG ===== */
//         if ($company_code == 'ABSG') {

//             $arr_is_ho = $this->EmployeeDetails->query(
//                 "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
//                 array('emp_pkey' => $current_emp_pkey)
//             );

//             $is_ho = 0;

//             if (!empty($arr_is_ho) && isset($arr_is_ho[0][0]['branch'])) {
//                 $is_ho = $arr_is_ho[0][0]['branch'];
//             }

//             if ($is_ho != 1) {
//                 $branch_condition .= " AND ed.branch_code = '" . $is_ho . "' ";
//             }
//         }

//         /* ===== OTHER SPECIAL COMPANIES ===== */
//         elseif (in_array($company_code, 
//             array('GLET','TUDS'))) {

//             $feature_fkey = $this->Session->read('current_feature_id');

//             if (empty($feature_fkey)) {
//                 $feature_fkey = 33;
//             }

//             $accessResults = $this->EmployeeDetails->query("
//                 SELECT branch_fkey, is_hierarchy
//                 FROM user_feature_branch_access
//                 WHERE user_fkey = ?
//                 AND feature_fkey = ?
//                 AND LCASE(active) = 'y'
//             ", array($current_emp_pkey, $feature_fkey));

//             $allocated_branches = array();
//             $is_hierarchy = 'N';

//             if (!empty($accessResults)) {
//                 foreach ($accessResults as $row) {

//                     $allocated_branches[] =
//                         $row['user_feature_branch_access']['branch_fkey'];

//                     if (strtoupper(
//                         $row['user_feature_branch_access']['is_hierarchy']
//                     ) == 'Y') {
//                         $is_hierarchy = 'Y';
//                     }
//                 }
//             }

//             if ($is_hierarchy == 'Y') {

//                 $branch_condition .= 
//                     " AND (ep.attr1 = " . $current_emp_pkey .
//                     " OR ep.emp_fkey = " . $current_emp_pkey . ") ";

//             } elseif (!empty($allocated_branches)) {

//                 $branch_codes_str = "'" . 
//                     implode("','", $allocated_branches) . "'";

//                 $branch_condition .= 
//                     " AND ed.branch_code IN (" . $branch_codes_str . ") ";

//             } else {

//                 $empBranchInfo = $this->EmployeeDetails->query(
//                     "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
//                     array($current_emp_pkey)
//                 );

//                 $own_branch = '0';

//                 if (!empty($empBranchInfo) && 
//                     isset($empBranchInfo[0]['emp_proff']['emp_branch'])) {
//                     $own_branch = $empBranchInfo[0]['emp_proff']['emp_branch'];
//                 }

//                 $branch_condition .= 
//                     " AND ed.branch_code = '" . $own_branch . "' ";
//             }
//         }

//         /* ===== OTHER COMPANIES → SHOW ALL ===== */
//         else {
//             // No restriction
//         }
//     }

//     /* =============================
//        NORMAL EMPLOYEE
//     ============================== */

//     else {

//         $empBranchInfo = $this->EmployeeDetails->query(
//             "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
//             array($current_emp_pkey)
//         );

//         $own_branch = '0';

//         if (!empty($empBranchInfo) && 
//             isset($empBranchInfo[0]['emp_proff']['emp_branch'])) {
//             $own_branch = $empBranchInfo[0]['emp_proff']['emp_branch'];
//         }

//         $branch_condition .= 
//             " AND ed.branch_code = '" . $own_branch . "' ";
//     }

//     /* =============================
//        COUNT QUERY
//     ============================== */

//     $counts = $this->EmployeeExpenses->query("
//         SELECT COUNT(*) AS total_count
//         FROM emp_details ed
//         INNER JOIN emp_expense au 
//             ON ed.emp_pkey = au.emp_fkey
//         LEFT JOIN emp_proff ep 
//             ON ed.emp_pkey = ep.emp_fkey
//         LEFT JOIN employee_info ei 
//             ON ed.emp_pkey = ei.emp_pkey
//         WHERE au.is_credited = 'N'
//         AND au.status = 1
//         AND au.expense_status = 'Applied'
//         $emp_condition
//         $branch_condition
//     ");

//     $count = 0;

//     if (!empty($counts) && isset($counts[0][0]['total_count'])) {
//         $count = $counts[0][0]['total_count'];
//     }

//     /* =============================
//        DATA QUERY
//     ============================== */

//     $arr_att = $this->EmployeeExpenses->query("
//         SELECT au.*, ep.emp_company_id, ei.EmpName
//         FROM emp_details ed
//         INNER JOIN emp_expense au 
//             ON ed.emp_pkey = au.emp_fkey
//         LEFT JOIN emp_proff ep 
//             ON ed.emp_pkey = ep.emp_fkey
//         LEFT JOIN employee_info ei 
//             ON ed.emp_pkey = ei.emp_pkey
//         WHERE au.is_credited = 'N'
//         AND au.status = 1
//         AND au.expense_status = 'Applied'
//         $emp_condition
//         $branch_condition
//         ORDER BY au.created_date DESC
//         LIMIT $limit OFFSET $ofst
//     ");

//     $resp_att = array();
//     $resp_att["rows"] = array();

//     if (!empty($arr_att)) {
//         foreach ($arr_att as $key => $value) {

//             $out = array();

//             $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
//             $out['empid'] = isset($value['ep']['emp_company_id']) ? $value['ep']['emp_company_id'] : '';
//             $out['emp_expenses_pkey'] = isset($value['au']['emp_expenses_pkey']) ? $value['au']['emp_expenses_pkey'] : '';
//             $out['expenses_amount'] = isset($value['au']['expenses_amount']) ? $value['au']['expenses_amount'] : '';
//             $out['affected_month'] = isset($value['au']['affected_month']) 
//                 ? date('d-m-Y', strtotime($value['au']['affected_month'])) 
//                 : '';
//             $out['remarks'] = isset($value['au']['remarks']) ? $value['au']['remarks'] : '';

//             $resp_att["rows"][$key] = $out;
//         }
//     }

//     $resp_att["total"] = $count;

//     echo json_encode($resp_att);
// }

//  public function employeelist()
// {
//     $this->autoRender = false;

//     $arr_request_data = $this->request->data;

//     $emp_fkey_request = !empty($arr_request_data['employee']) 
//         ? (int)$arr_request_data['employee'] 
//         : 0;

//     $branch_code_request = !empty($arr_request_data['branch']) 
//         ? $arr_request_data['branch'] 
//         : '';

//     $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
//     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

//     /* =============================
//        SAFE PAGINATION
//     ============================== */

//     $limit = isset($_REQUEST['rows']) ? (int)$_REQUEST['rows'] : 10;
//     $page  = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

//     if ($limit <= 0) {
//         $limit = 10;
//     }

//     if ($limit > 100) {
//         $limit = 100;
//     }

//     $ofst = ($page - 1) * $limit;

//     $emp_condition = '';
//     $branch_condition = '';

//     /* =============================
//        EMPLOYEE FILTER
//     ============================== */

//     if ($emp_fkey_request > 0) {
//         $emp_condition .= " AND au.emp_fkey = " . $emp_fkey_request;
//     }

//     if ($branch_code_request != '') {
//         $branch_condition .= " AND ed.branch_code = '" . addslashes($branch_code_request) . "' ";
//     }

//     /* =============================
//        SESSION VALUES
//     ============================== */

//     $current_emp_pkey = $this->Session->read('emp_fkey');
//     $user_group = $this->Session->read('user_group');
//     $company_code = strtoupper($this->Session->read('company_code'));

//     /* =============================
//        USER GROUP 2 (MANAGER)
//     ============================== */

//     /* =============================
//        USER GROUP 1 & 2 (ADMIN & MANAGER)
//     ============================== */

//     if ($user_group == '1' || $user_group == '2') {

//         /* ===== COMPANY ABSG (MANAGER ONLY) ===== */
//         if ($company_code == 'ABSG' && $user_group == '2') {

//             $arr_is_ho = $this->EmployeeDetails->query(
//                 "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
//                 array('emp_pkey' => $current_emp_pkey)
//             );

//             $is_ho = 0;

//             if (!empty($arr_is_ho) && isset($arr_is_ho[0][0]['branch'])) {
//                 $is_ho = $arr_is_ho[0][0]['branch'];
//             }

//             if ($is_ho != 1) {
//                 $branch_condition .= " AND ed.branch_code = '" . $is_ho . "' ";
//             }
//         }

//         /* ===== OTHER SPECIAL COMPANIES (ADMIN & MANAGER) ===== */
//         elseif (in_array($company_code, array('GLET','TUDS'))) {

//             $feature_fkey = $this->Session->read('current_feature_id');

//             if (empty($feature_fkey)) {
//                 $feature_fkey = 33;
//             }

//             $accessResults = $this->EmployeeDetails->query("
//                 SELECT branch_fkey, is_hierarchy
//                 FROM user_feature_branch_access
//                 WHERE user_fkey = ?
//                 AND feature_fkey = ?
//                 AND LCASE(active) = 'y'
//             ", array($current_emp_pkey, $feature_fkey));

//             $allocated_branches = array();
//             $is_hierarchy = 'N';

//             if (!empty($accessResults)) {
//                 foreach ($accessResults as $row) {

//                     $allocated_branches[] =
//                         $row['user_feature_branch_access']['branch_fkey'];

//                     if (strtoupper(
//                         $row['user_feature_branch_access']['is_hierarchy']
//                     ) == 'Y') {
//                         $is_hierarchy = 'Y';
//                     }
//                 }
//             }

//             if ($is_hierarchy == 'Y') {

//                 $branch_condition .= 
//                     " AND (ep.attr1 = " . $current_emp_pkey .
//                     " OR ep.emp_fkey = " . $current_emp_pkey . ") ";

//             } elseif (!empty($allocated_branches)) {

//                 $branch_codes_str = "'" . 
//                     implode("','", $allocated_branches) . "'";

//                 $branch_condition .= 
//                     " AND ed.branch_code IN (" . $branch_codes_str . ") ";

//             } else {

//                 $empBranchInfo = $this->EmployeeDetails->query(
//                     "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
//                     array($current_emp_pkey)
//                 );

//                 $own_branch = '0';

//                 if (!empty($empBranchInfo) && 
//                     isset($empBranchInfo[0]['emp_proff']['emp_branch'])) {
//                     $own_branch = $empBranchInfo[0]['emp_proff']['emp_branch'];
//                 }

//                 $branch_condition .= 
//                     " AND ed.branch_code = '" . $own_branch . "' ";
//             }
//         }

//         /* ===== OTHER COMPANIES (ADMIN FULL ACCESS) ===== */
//         elseif ($user_group == '1') {
//             // No branch restriction for Admin in other companies
//             $empBranchInfo = $this->EmployeeDetails->query(
//             "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
//             array($current_emp_pkey)
//         );

//         $own_branch = '0';

//         if (!empty($empBranchInfo) && 
//             isset($empBranchInfo[0]['emp_proff']['emp_branch'])) {
//             $own_branch = $empBranchInfo[0]['emp_proff']['emp_branch'];
//         }

//         $branch_condition .= 
//             " AND ed.branch_code = '" . $own_branch . "' ";
//     }

//     /* =============================
//        COUNT QUERY
//     ============================== */

//     $counts = $this->EmployeeExpenses->query("
//         SELECT COUNT(*) AS total_count
//         FROM emp_details ed
//         INNER JOIN emp_expense au 
//             ON ed.emp_pkey = au.emp_fkey
//         LEFT JOIN emp_proff ep 
//             ON ed.emp_pkey = ep.emp_fkey
//         LEFT JOIN employee_info ei 
//             ON ed.emp_pkey = ei.emp_pkey
//         WHERE au.is_credited = 'N'
//         AND au.status = 1
//         AND au.expense_status = 'Applied'
//         $emp_condition
//         $branch_condition
//     ");

//     $count = 0;

//     if (!empty($counts) && isset($counts[0][0]['total_count'])) {
//         $count = $counts[0][0]['total_count'];
//     }

//     /* =============================
//        DATA QUERY
//     ============================== */

//     $arr_att = $this->EmployeeExpenses->query("
//         SELECT au.*, ep.emp_company_id, ei.EmpName
//         FROM emp_details ed
//         INNER JOIN emp_expense au 
//             ON ed.emp_pkey = au.emp_fkey
//         LEFT JOIN emp_proff ep 
//             ON ed.emp_pkey = ep.emp_fkey
//         LEFT JOIN employee_info ei 
//             ON ed.emp_pkey = ei.emp_pkey
//         WHERE au.is_credited = 'N'
//         AND au.status = 1
//         AND au.expense_status = 'Applied'
//         $emp_condition
//         $branch_condition
//         ORDER BY au.created_date DESC
//         LIMIT $limit OFFSET $ofst
//     ");

//     $resp_att = array();
//     $resp_att["rows"] = array();

//     if (!empty($arr_att)) {
//         foreach ($arr_att as $key => $value) {

//             $out = array();

//             $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
//             $out['empid'] = isset($value['ep']['emp_company_id']) ? $value['ep']['emp_company_id'] : '';
//             $out['emp_expenses_pkey'] = isset($value['au']['emp_expenses_pkey']) ? $value['au']['emp_expenses_pkey'] : '';
//             $out['expenses_amount'] = isset($value['au']['expenses_amount']) ? $value['au']['expenses_amount'] : '';
//             $out['affected_month'] = isset($value['au']['affected_month']) 
//                 ? date('d-m-Y', strtotime($value['au']['affected_month'])) 
//                 : '';
//             $out['remarks'] = isset($value['au']['remarks']) ? $value['au']['remarks'] : '';

//             $resp_att["rows"][$key] = $out;
//         }
//         }

//         /* ===== OTHER COMPANIES (MANAGER OWN BRANCH) ===== */
//         else {
//              $empBranchInfo = $this->EmployeeDetails->query(
//                 "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
//                 array($current_emp_pkey)
//             );

//             $own_branch = '0';

//             if (!empty($empBranchInfo) && 
//                 isset($empBranchInfo[0]['emp_proff']['emp_branch'])) {
//                 $own_branch = $empBranchInfo[0]['emp_proff']['emp_branch'];
//             }

//             $branch_condition .= 
//                 " AND ed.branch_code = '" . $own_branch . "' ";
//         }
//     }

//     /* =============================
//        ALL OTHERS (FALLBACK)
//     ============================== */

//     else {

//         $empBranchInfo = $this->EmployeeDetails->query(
//             "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
//             array($current_emp_pkey)
//         );

//         $own_branch = '0';

//         if (!empty($empBranchInfo) && 
//             isset($empBranchInfo[0]['emp_proff']['emp_branch'])) {
//             $own_branch = $empBranchInfo[0]['emp_proff']['emp_branch'];
//         }

//         $branch_condition .= 
//             " AND ed.branch_code = '" . $own_branch . "' ";
//     }

//     /* =============================
//        COUNT QUERY
//     ============================== */

//     $counts = $this->EmployeeExpenses->query("
//         SELECT COUNT(*) AS total_count
//         FROM emp_details ed
//         INNER JOIN emp_expense au 
//             ON ed.emp_pkey = au.emp_fkey
//         LEFT JOIN emp_proff ep 
//             ON ed.emp_pkey = ep.emp_fkey
//         LEFT JOIN employee_info ei 
//             ON ed.emp_pkey = ei.emp_pkey
//         WHERE au.is_credited = 'N'
//         AND au.status = 1
//         AND au.expense_status = 'Applied'
//         $emp_condition
//         $branch_condition
//     ");

//     $count = 0;

//     if (!empty($counts) && isset($counts[0][0]['total_count'])) {
//         $count = $counts[0][0]['total_count'];
//     }

//     /* =============================
//        DATA QUERY
//     ============================== */

//     $arr_att = $this->EmployeeExpenses->query("
//         SELECT au.*, ep.emp_company_id, ei.EmpName
//         FROM emp_details ed
//         INNER JOIN emp_expense au 
//             ON ed.emp_pkey = au.emp_fkey
//         LEFT JOIN emp_proff ep 
//             ON ed.emp_pkey = ep.emp_fkey
//         LEFT JOIN employee_info ei 
//             ON ed.emp_pkey = ei.emp_pkey
//         WHERE au.is_credited = 'N'
//         AND au.status = 1
//         AND au.expense_status = 'Applied'
//         $emp_condition
//         $branch_condition
//         ORDER BY au.created_date DESC
//         LIMIT $limit OFFSET $ofst
//     ");

//     $resp_att = array();
//     $resp_att["rows"] = array();

//     if (!empty($arr_att)) {
//         foreach ($arr_att as $key => $value) {

//             $out = array();

//             $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
//             $out['empid'] = isset($value['ep']['emp_company_id']) ? $value['ep']['emp_company_id'] : '';
//             $out['emp_expenses_pkey'] = isset($value['au']['emp_expenses_pkey']) ? $value['au']['emp_expenses_pkey'] : '';
//             $out['expenses_amount'] = isset($value['au']['expenses_amount']) ? $value['au']['expenses_amount'] : '';
//             $out['affected_month'] = isset($value['au']['affected_month']) 
//                 ? date('d-m-Y', strtotime($value['au']['affected_month'])) 
//                 : '';
//             $out['remarks'] = isset($value['au']['remarks']) ? $value['au']['remarks'] : '';

//             $resp_att["rows"][$key] = $out;
//         }
//     }

//     $resp_att["total"] = $count;

//     echo json_encode($resp_att);
// }
public function employeelist()
{
    $this->autoRender = false;

    $arr_request_data = $this->request->data;
    $emp_fkey_request = !empty($arr_request_data['employee']) ? (int)$arr_request_data['employee'] : 0;
    $branch_code_request = !empty($arr_request_data['branch']) ? $arr_request_data['branch'] : '';

    $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    /* =============================
       SAFE PAGINATION
    ============================= */
    $limit = isset($_REQUEST['rows']) ? (int)$_REQUEST['rows'] : 10;
    $page  = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

    if ($limit <= 0) { $limit = 10; }
    if ($limit > 100) { $limit = 100; }
    $offset = ($page - 1) * $limit;

    /* =============================
       EMPLOYEE & BRANCH FILTERS
    ============================= */
    $emp_condition = '';
    if ($emp_fkey_request > 0) {
        $emp_condition = " AND au.emp_fkey = " . $emp_fkey_request;
    }

    $branch_condition = '';
    if ($branch_code_request != '') {
        $branch_condition = " AND ed.branch_code = '" . addslashes($branch_code_request) . "'";
    }

    /* =============================
       SESSION VALUES
    ============================= */
    $current_emp_pkey = $this->Session->read('emp_fkey');
    $user_group = $this->Session->read('user_group');
    $company_code = strtoupper($this->Session->read('company_code'));

    /* =============================
       BRANCH ACCESS CONTROL
    ============================= */
    if ($user_group == '1') {
    // Admin: filter by branch if provided
    if (!empty($branch_code_request)) {
        $branch_condition = " AND ed.branch_code = '" . addslashes($branch_code_request) . "'";
    } else {
        $branch_condition = ''; // No restriction if branch not selected
    }
} elseif ($user_group == '2') {
       if (!in_array($company_code, [
    'KWMT','ABSG','MBCT','DRRC','SRTS','MRBS','DJIC','STCL',
    'SHYD','AGNG','ESNP','GTRA','VGNN','AYRK','VGFS','VSFS'
])) {
            $feature_fkey = $this->Session->read('current_feature_id');
            if (empty($feature_fkey)) { $feature_fkey = 33; }

            $accessResults = $this->EmployeeDetails->query(
                "SELECT branch_fkey, is_hierarchy
                 FROM user_feature_branch_access
                 WHERE user_fkey = ?
                   AND feature_fkey = ?
                   AND LCASE(active) = 'y'",
                array($current_emp_pkey, $feature_fkey)
            );

            $allocated_branches = array();
            $is_hierarchy = 'N';

            if (!empty($accessResults)) {
                foreach ($accessResults as $row) {
                    $allocated_branches[] = $row['user_feature_branch_access']['branch_fkey'];
                    if (strtoupper($row['user_feature_branch_access']['is_hierarchy']) == 'Y') {
                        $is_hierarchy = 'Y';
                    }
                }
            }

            if ($is_hierarchy == 'Y') {
                $branch_condition .= " AND (ep.attr1 = " . $current_emp_pkey . " OR ep.emp_fkey = " . $current_emp_pkey . ")";
            } elseif (!empty($allocated_branches)) {
                $branch_codes_str = "'" . implode("','", $allocated_branches) . "'";
                $branch_condition .= " AND ed.branch_code IN (" . $branch_codes_str . ")";
            } else {
                $empBranchInfo = $this->EmployeeDetails->query(
                    "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
                    array($current_emp_pkey)
                );
                $own_branch = (isset($empBranchInfo[0]['emp_proff']['emp_branch'])) ? $empBranchInfo[0]['emp_proff']['emp_branch'] : '0';
                $branch_condition .= " AND ed.branch_code = '" . $own_branch . "'";
            }

        } else {
            // Other companies → own branch only
            $empBranchInfo = $this->EmployeeDetails->query(
                "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
                array($current_emp_pkey)
            );
            $own_branch = (isset($empBranchInfo[0]['emp_proff']['emp_branch'])) ? $empBranchInfo[0]['emp_proff']['emp_branch'] : '0';
            $branch_condition .= " AND ed.branch_code = '" . $own_branch . "'";
        }
    }

    /* =============================
       TOTAL COUNT
    ============================= */
    $counts = $this->EmployeeExpenses->query(
        "SELECT COUNT(*) AS total_count
         FROM emp_details ed
         INNER JOIN emp_expense au ON ed.emp_pkey = au.emp_fkey
         LEFT JOIN emp_proff ep ON ed.emp_pkey = ep.emp_fkey
         LEFT JOIN employee_info ei ON ed.emp_pkey = ei.emp_pkey
         WHERE au.is_credited = 'N'
           AND au.status = 1
           AND au.expense_status = 'Applied'
           $emp_condition
           $branch_condition"
    );

    $total_count = (isset($counts[0][0]['total_count'])) ? $counts[0][0]['total_count'] : 0;

    /* =============================
       FETCH DATA
    ============================= */
    $arr_att = $this->EmployeeExpenses->query(
        "SELECT au.*, ep.emp_company_id, ei.EmpName
         FROM emp_details ed
         INNER JOIN emp_expense au ON ed.emp_pkey = au.emp_fkey
         LEFT JOIN emp_proff ep ON ed.emp_pkey = ep.emp_fkey
         LEFT JOIN employee_info ei ON ed.emp_pkey = ei.emp_pkey
         WHERE au.is_credited = 'N'
           AND au.status = 1
           AND au.expense_status = 'Applied'
           $emp_condition
           $branch_condition
         ORDER BY au.created_date DESC
         LIMIT $limit OFFSET $offset"
    );

    /* =============================
       FORMAT RESPONSE
    ============================= */
    $resp_att = array('total' => $total_count, 'rows' => array());

    if (!empty($arr_att)) {
        foreach ($arr_att as $key => $value) {
            $resp_att['rows'][$key] = array(
                'empname' => isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '',
                'empid' => isset($value['ep']['emp_company_id']) ? $value['ep']['emp_company_id'] : '',
                'emp_expenses_pkey' => isset($value['au']['emp_expenses_pkey']) ? $value['au']['emp_expenses_pkey'] : '',
                'expenses_amount' => isset($value['au']['expenses_amount']) ? $value['au']['expenses_amount'] : '',
                'affected_month' => (!empty($value['au']['affected_month'])) ? date('d-m-Y', strtotime($value['au']['affected_month'])) : '',
                'remarks' => isset($value['au']['remarks']) ? $value['au']['remarks'] : ''
            );
        }
    }

    echo json_encode($resp_att);
}

    public function employeeverifiedlist()
    {
        $this->autoRender = FALSE;
        $arr_request_data = $this->request->data;
        //        $emp_fkey = isset($arr_request_data['employee']) ? $arr_request_data['employee'] : '';
        $branch_code = isset($arr_request_data['branch']) ? $arr_request_data['branch'] : '';
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $this->datatable["conditions"] = array('status' => 1);
        $resp_att = array();
        $resp_att["rows"] = array();
        $emp_condition = '';
        $branch_condition = '';

        if (!empty($arr_request_data['employee']) && $arr_request_data['employee'] != 0) {
            $emp = $arr_request_data['employee'];
            $emp_condition = "and au.emp_fkey=$emp";
        } else {
            $emp_condition = ' ';
        }
        if ($branch_code != '') {
            $branch_condition = "and ed.branch_code='$branch_code'";
        }

        if (isset($arr_request_data['emp']) && $arr_request_data['emp'] != "") {
            $conditions = " and au.expense_status in (" . $arr_request_data['emp'] . ")";
        } else {
            $conditions = "";
        }

        $counts = $this->EmployeeExpenses->query(" select
            COUNT(*)
            from emp_details ed
            INNER join emp_expense au on (ed.emp_pkey = au.emp_fkey) 
            LEFT join emp_proff ep on (ed.emp_pkey= ep.emp_fkey)
            LEFT join employee_info ei on (ed.emp_id= ei.emp_id)
            where  au.is_credited = 'N'
            and au.status in (1,2,0) and au.expense_status not in ('Applied')
            $emp_condition $branch_condition $conditions ");

        $count = $counts[0][0]['COUNT(*)'];
        $arr_att = $this->EmployeeExpenses->query("select au.*,ep.emp_company_id,ei.EmpName
            from emp_details ed
            INNER join emp_expense au on (ed.emp_pkey = au.emp_fkey) 
            LEFT join emp_proff ep on (ed.emp_pkey= ep.emp_fkey)
            LEFT join employee_info ei on (ed.emp_id= ei.emp_id)
            where  au.is_credited = 'N'
            and au.status in (1,2,0)  and au.expense_status not in ('Applied')
            $emp_condition $branch_condition $conditions"
            . " ORDER BY created_date desc "
            . "limit $limit  offset $ofst ");

        // debug($arr_att);
        $out = array();
        foreach ($arr_att as $key => $value) {
            $out['empname'] = isset($value['ei']['EmpName']) ? $value['ei']['EmpName'] : '';
            $out['empid'] = isset($value['ep']['emp_company_id']) ? $value['ep']['emp_company_id'] : '';
            $out['emp_expenses_pkey'] = isset($value['au']['emp_expenses_pkey']) ? $value['au']['emp_expenses_pkey'] : '';
            $out['expenses_amount'] = isset($value['au']['expenses_amount']) ? $value['au']['expenses_amount'] : '';
            $affected_month = isset($value['au']['affected_month']) ? $value['au']['affected_month'] : '';
            if ($affected_month === '0000-00-00') {
                $out['affected_month']='00-00-0000';
            }
            else{
                $out['affected_month']=date('d-m-Y', strtotime($value['au']['affected_month']));
            }
            $out['remarks'] = isset($value['au']['remarks']) ? $value['au']['remarks'] : '';
            $out['is_credited'] = isset($value['au']['is_credited']) ? $value['au']['is_credited'] : '';
            $out['created_date'] = isset($value['au']['created_date']) ? $value['au']['created_date'] : '';
            $out['created_by'] = isset($value['au']['created_by']) ? $value['au']['created_by'] : '';
            $out['modified_by'] = isset($value['au']['modified_by']) ? $value['au']['modified_by'] : '';
            $out['modified_date'] = isset($value['au']['modified_date']) ? $value['au']['modified_date'] : '';
            $out['status'] = isset($value['au']['status']) ? $value['au']['status'] : '';
            $out['expense_status'] = isset($value['au']['expense_status']) ? $value['au']['expense_status'] : '';

            $resp_att["rows"][$key] = $out;
        }
        $resp_att["total"] = $count;
        echo json_encode($resp_att);
    }

    //main page dropdown       
//     public function Expenses()
//     {
//         $this->UserCredentials->useDbConfig = $this->Session->read('ds');
//         $this->Units->useDbConfig = $this->Session->read('ds');
//         $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');


//         //The below code is to check if the login user is admin or employee.And if employee,shows his/her branch data only.
//         $user_group = $this->Session->read('user_group');
//         if ($user_group == 2) {
//             $cur_emp_key = $this->Session->read("emp_fkey");
//             $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//             $cur_emp_branch_find = $this->EmployeeDetails->find("all", array("fields" => "branch_code", "conditions" => array("emp_pkey" => $cur_emp_key, "status" => 1)));
//             $cur_emp_branch = $cur_emp_branch_find[0]['EmployeeDetails']['branch_code'];
//             //editedby anukrishnan_29-01-2025 open
//             $emp_fkey = $this->Session->read('emp_fkey');
//             $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
//             $arr_is_ho = $this->EmployeeDetails->query(
//                 "SELECT get_branch_code_abs_fn(:emp_pkey) AS branch",
//                 ['emp_pkey' => $emp_fkey]
//             );
//             $is_ho = isset($arr_is_ho[0][0]['branch']) ? $arr_is_ho[0][0]['branch'] : 0;
//             if ($is_ho != 1) {
//                 $conditions = array("branch_code" => $is_ho, "status" => 1);
//             } else {
//                 $conditions = array("status" => 1);
//             }
//             $this->set('is_ho', $is_ho);
//             // $conditions = array("branch_code" => $cur_emp_branch, "status" => 1);
//             //editedby anukrishnan_29-01-2025 close
//         } else {
//             $conditions = array("status" => 1);
//         }



//         $this->set("arr_branches", $arr_branches = $this->Units->find("all", array("conditions" => $conditions)));
//         $this->set("arr_employees", $arr_employees = $this->EmployeeDetails->find("all", array('conditions' => $conditions)));

//         $plan=$this->Menu->query('SELECT plan FROM comp_contact_info');
//         $plan=isset($plan['0']['comp_contact_info']['plan'])?$plan['0']['comp_contact_info']['plan']:'';
//         $this->set('plan',$plan);
//          $this->CentralUserCredentials->setDataSource('controldb');

//     $company_code = $this->Session->read('company_code');

//     $data = $this->CentralUserCredentials->find('first', array(
//         'conditions' => array(
//             'CentralUserCredentials.company_code' => $company_code
//         ),
//         'fields' => array('CentralUserCredentials.plan_id'),
//         'recursive' => -1
//     ));

//     $planId = !empty($data)
//         ? (int)$data['CentralUserCredentials']['plan_id']
//         : null;
//         // debug($plan);
// // debug($planId);
//     $this->set('planId',$planId);
//      $this->set('user_group', $user_group);
//     //  debug($planId);
//     //  debug($user_group);
//     //  debug($plan);
//     }
// public function Expenses()
// {
//     $this->UserCredentials->useDbConfig = $this->Session->read('ds');
//     $this->Units->useDbConfig = $this->Session->read('ds');
//     $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

//     $user_group   = $this->Session->read('user_group');
//     $company_code = strtoupper($this->Session->read('company_code'));
//     $user_fkey    = $this->Session->read('emp_fkey');

//     $arr_branches  = array();
//     $arr_employees = array();

//     /*
//     =====================================================
//     COMPANY BASED CONTROL
//     =====================================================
//     */
//     if (in_array($company_code, array('GLET','TUDS'))) {

//         /*
//         =====================================================
//         USER GROUP 2 → BRANCH + HIERARCHY LOGIC
//         =====================================================
//         */
//         if ($user_group == 2) {

//             $feature_fkey = $this->Session->read('current_feature_id') ?: 33;

//             $accessResults = $this->EmployeeDetails->query("
//                 SELECT branch_fkey, is_hierarchy
//                 FROM user_feature_branch_access
//                 WHERE user_fkey = ?
//                 AND feature_fkey = ?
//                 AND LCASE(active) = 'y'
//             ", array($user_fkey, $feature_fkey));

//             $allocated_branches = array();
//             $is_hierarchy = 'N';

//             if (!empty($accessResults)) {
//                 foreach ($accessResults as $row) {
//                     $allocated_branches[] = $row['user_feature_branch_access']['branch_fkey'];
//                     if (strtoupper($row['user_feature_branch_access']['is_hierarchy']) == 'Y') {
//                         $is_hierarchy = 'Y';
//                     }
//                 }
//             }

//             // ✅ Hierarchy Wise
//             if ($is_hierarchy == 'Y') {

//                 $arr_branches = $this->Units->query("
//                     SELECT id, branch_code, branch_name
//                     FROM branches
//                     WHERE status = 1
//                     AND branch_code IN (
//                         SELECT DISTINCT emp_branch
//                         FROM emp_proff
//                         WHERE attr1 = ? OR emp_fkey = ?
//                     )
//                 ", array($user_fkey, $user_fkey));

//                 $arr_employees = $this->EmployeeDetails->query("
//                     SELECT ed.*, ep.*
//                     FROM emp_details ed
//                     JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
//                     WHERE (ep.attr1 = ? OR ep.emp_fkey = ?)
//                     AND ed.status = 1
//                 ", array($user_fkey, $user_fkey));
//             }
//             // ✅ Branch Wise
//             elseif (!empty($allocated_branches)) {

//                 $conditions = array(
//                     'branch_code' => $allocated_branches,
//                     'status' => 1
//                 );

//                 $arr_branches = $this->Units->find('all', array(
//                     'conditions' => $conditions
//                 ));

//                 $arr_employees = $this->EmployeeDetails->find('all', array(
//                     'conditions' => $conditions
//                 ));
//             }
//             // ✅ Fallback → Own Branch
//             else {

//                 $branch = $this->EmployeeDetails->query(
//                     "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
//                     array($user_fkey)
//                 );

//                 $own_branch = isset($branch[0]['emp_proff']['emp_branch'])
//                     ? $branch[0]['emp_proff']['emp_branch']
//                     : '';

//                 $conditions = array(
//                     'branch_code' => $own_branch,
//                     'status' => 1
//                 );

//                 $arr_branches = $this->Units->find('all', array(
//                     'conditions' => $conditions
//                 ));

//                 $arr_employees = $this->EmployeeDetails->find('all', array(
//                     'conditions' => $conditions
//                 ));
//             }
//         }

//         /*
//         =====================================================
//         NORMAL EMPLOYEE → ONLY OWN BRANCH
//         =====================================================
//         */
//         else {

//             $branch = $this->EmployeeDetails->find('first', array(
//                 'fields' => array('branch_code'),
//                 'conditions' => array(
//                     'emp_pkey' => $user_fkey,
//                     'status' => 1
//                 )
//             ));

//            $own_branch = isset($branch['EmployeeDetails']['branch_code'])
//     ? $branch['EmployeeDetails']['branch_code']
//     : '';

//             $conditions = array(
//                 'branch_code' => $own_branch,
//                 'status' => 1
//             );

//             $arr_branches = $this->Units->find('all', array(
//                 'conditions' => $conditions
//             ));

//             $arr_employees = $this->EmployeeDetails->find('all', array(
//                 'conditions' => $conditions
//             ));
//         }

//     }
//     /*
//     =====================================================
//     OTHER COMPANIES → ADMIN ALL (status = 1)
//     =====================================================
//     */
//     else {

//         $conditions = array('status' => 1);

//         $arr_branches = $this->Units->find('all', array(
//             'conditions' => $conditions
//         ));

//         $arr_employees = $this->EmployeeDetails->find('all', array(
//             'conditions' => $conditions
//         ));
//     }

//     $this->set('arr_branches', $arr_branches);
//     $this->set('arr_employees', $arr_employees);
//     $this->set('user_group', $user_group);
public function Expenses()
{
    $this->UserCredentials->useDbConfig = $this->Session->read('ds');
    $this->Units->useDbConfig = $this->Session->read('ds');
    $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');

    $user_group   = $this->Session->read('user_group');
    $company_code = strtoupper($this->Session->read('company_code'));
    $user_fkey    = $this->Session->read('emp_fkey');

    $arr_branches  = array();
    $arr_employees = array();

   
$not_allowed_companies = array(
    'KWMT','ABSG','MBCT','DRRC','SRTS','MRBS','DJIC','STCL',
    'SHYD','AGNG','ESNP','GTRA','VGNN','AYRK','VGFS','VSFS'
);
    /*
    =====================================================
    ADMIN → FULL ACCESS
    =====================================================
    */
    if ($user_group == 1) {

        $conditions = array('status' => 1);

        $arr_branches = $this->Units->find('all', array(
            'conditions' => $conditions
        ));

        $arr_employees = $this->EmployeeDetails->find('all', array(
            'conditions' => $conditions
        ));
    }

    /*
    =====================================================
    GROUP 2 + ALLOWED COMPANY → BRANCH + HIERARCHY
    =====================================================
    */
   elseif ($user_group == 2 && !in_array($company_code, $not_allowed_companies)) {

        $feature_fkey = $this->Session->read('current_feature_id') ?: 33;

        $accessResults = $this->EmployeeDetails->query("
            SELECT branch_fkey, is_hierarchy
            FROM user_feature_branch_access
            WHERE user_fkey = ?
            AND feature_fkey = ?
            AND LCASE(active) = 'y'
        ", array($user_fkey, $feature_fkey));

        $allocated_branches = array();
        $is_hierarchy = 'N';

        if (!empty($accessResults)) {
            foreach ($accessResults as $row) {

                $allocated_branches[] = $row['user_feature_branch_access']['branch_fkey'];

                if (strtoupper($row['user_feature_branch_access']['is_hierarchy']) == 'Y') {
                    $is_hierarchy = 'Y';
                }
            }
        }

        /*
        ===== Hierarchy Access =====
        */
        if ($is_hierarchy == 'Y') {

            $arr_branches = $this->Units->query("
                SELECT id, branch_code, branch_name
                FROM branches
                WHERE status = 1
                AND branch_code IN (
                    SELECT DISTINCT emp_branch
                    FROM emp_proff
                    WHERE attr1 = ? OR emp_fkey = ?
                )
            ", array($user_fkey, $user_fkey));

            $arr_employees = $this->EmployeeDetails->query("
                SELECT ed.*, ep.*
                FROM emp_details ed
                JOIN emp_proff ep ON ep.emp_fkey = ed.emp_pkey
                WHERE (ep.attr1 = ? OR ep.emp_fkey = ?)
                AND ed.status = 1
            ", array($user_fkey, $user_fkey));
        }

        /*
        ===== Branch Only Access =====
        */
        elseif (!empty($allocated_branches)) {

            $conditions = array(
                'branch_code' => $allocated_branches,
                'status' => 1
            );

            $arr_branches = $this->Units->find('all', array(
                'conditions' => $conditions
            ));

            $arr_employees = $this->EmployeeDetails->find('all', array(
                'conditions' => $conditions
            ));
        }

        /*
        ===== Fallback Own Branch =====
        */
        else {

            $branch = $this->EmployeeDetails->query(
                "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
                array($user_fkey)
            );

            $own_branch = isset($branch[0]['emp_proff']['emp_branch'])
                ? $branch[0]['emp_proff']['emp_branch']
                : '';

            $conditions = array(
                'branch_code' => $own_branch,
                'status' => 1
            );

            $arr_branches = $this->Units->find('all', array(
                'conditions' => $conditions
            ));

            $arr_employees = $this->EmployeeDetails->find('all', array(
                'conditions' => $conditions
            ));
        }
    }

    /*
    =====================================================
    ALL OTHER USERS → OWN BRANCH ONLY
    =====================================================
    */
    else {

        $branch = $this->EmployeeDetails->find('first', array(
            'fields' => array('branch_code'),
            'conditions' => array(
                'emp_pkey' => $user_fkey,
                'status' => 1
            )
        ));

        $own_branch = isset($branch['EmployeeDetails']['branch_code'])
            ? $branch['EmployeeDetails']['branch_code']
            : '';

        $conditions = array(
            'branch_code' => $own_branch,
            'status' => 1
        );

        $arr_branches = $this->Units->find('all', array(
            'conditions' => $conditions
        ));

        $arr_employees = $this->EmployeeDetails->find('all', array(
            'conditions' => $conditions
        ));
    }

    $this->set(compact('arr_branches', 'arr_employees', 'user_group'));
}
// }
    //delete
    public function deleteEmployee()
    {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["ids"]);
            //debug($ar_ids);
            $this->EmployeeExpenses->updateAll(
                array('EmployeeExpenses.status' => 0, 'EmployeeExpenses.expense_status' => "'Removed'"),
                array('EmployeeExpenses.emp_expenses_pkey' => $ar_ids)
            );
            $result['success'] = 1;
            $result['msg'] = "Record(s)  deleted successfully.";
        }
        echo json_encode($result);
    }
    public function jsons($branch = '', $resigned = '')
    {
        $this->autoRender = false;
        $this->EmployeeDetails->useDbConfig = $this->Session->read('ds');
        $company_code = strtoupper($this->Session->read('company_code'));
        $is_glet = (strpos($company_code, 'GLET') !== false);

         if (in_array($company_code, ['GLET'])){
            // New Robust Logic for GLET
            $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
            $user_fkey = $this->Session->read('emp_fkey');
            $user_group = $this->Session->read('user_group');
            $feature_fkey = $this->Session->read('current_feature_id');
            if (!$feature_fkey) $feature_fkey = 33; 

            $resign_condition = ($resigned == '1') ? " emp_details.status IN ('1','2') " : " emp_details.status = '1' ";
            $q_condition = ($q != null) ? " AND (emp_details.first_name LIKE '%$q%' OR emp_proff.emp_company_id LIKE '%$q%') " : "";

            $access_condition = "";
           if ($user_group == 2 && !in_array($company_code, [
    'KWMT','ABSG','MBCT','DRRC','SRTS','MRBS','DJIC','STCL',
    'SHYD','AGNG','ESNP','GTRA','VGNN','AYRK','VGFS','VSFS'
])){

                $access = $this->EmployeeDetails->query("
                    SELECT branch_fkey, is_hierarchy
                    FROM user_feature_branch_access
                    WHERE user_fkey = '".$user_fkey."'
                    AND feature_fkey = '".$feature_fkey."'
                    AND LCASE(active) = 'y'
                ");


                if (!empty($access)) {
                    $is_hierarchy_active = 'N';
                    $allocated_branches = array();
                    foreach($access as $row) {
                        if (strtoupper($row['user_feature_branch_access']['is_hierarchy']) == 'Y') $is_hierarchy_active = 'Y';
                        $allocated_branches[] = $row['user_feature_branch_access']['branch_fkey'];
                    }

                    if ($is_hierarchy_active == 'Y') {
                        $access_condition = " AND (emp_proff.attr1 = '$user_fkey' OR emp_proff.emp_fkey = '$user_fkey') ";
                    } else if (!empty($allocated_branches)) {
                        $b_str = implode("','", $allocated_branches);
                        $access_condition = " AND emp_proff.emp_branch IN ('$b_str') ";
                    } else {
                        $access_condition = " AND emp_proff.emp_fkey = '$user_fkey' ";
                    }
                } else {
                    // Fallback to own branch if no explicit feature access
                    $empBranchInfo = $this->EmployeeDetails->query(
                        "SELECT emp_branch FROM emp_proff WHERE emp_fkey = ?",
                        array($user_fkey)
                    );
                    $own_branch = isset($empBranchInfo[0]['emp_proff']['emp_branch']) ? $empBranchInfo[0]['emp_proff']['emp_branch'] : '0';
                    $access_condition = " AND emp_proff.emp_branch = '$own_branch' ";
                }
            }

            $branch_condition = "";
            if ($branch && $branch != '0' && $branch != 'null') {
                $branch_condition = " AND emp_proff.emp_branch = '$branch' ";
            }

            $sql = "SELECT emp_details.*, emp_proff.* 
                    FROM emp_details 
                    LEFT JOIN emp_proff ON (emp_details.emp_pkey = emp_proff.emp_fkey) 
                    WHERE $resign_condition $q_condition $access_condition $branch_condition 
                    ORDER BY emp_details.first_name ASC";
            
            $results = $this->EmployeeDetails->query($sql);

            $items = array();
            $items[] = array("id" => "0", "text" => "ALL");
            foreach ($results as $row) {
                $items[] = array(
                    'id' => $row['emp_details']['emp_pkey'],
                    'text' => $row['emp_details']['first_name'] . ' ' . $row['emp_details']['last_name'] . ' - ' . $row['emp_proff']['emp_company_id']
                );
            }
            
            echo json_encode(array('items' => $items));
            exit;

        } else {
            // Legacy Logic for non-GLET
            $q = isset($_REQUEST['q']) ? $_REQUEST['q'] : NULL;
            if ($branch == '0') {
                $branch_condition = "";
            } else if ($branch != null) {
                $branch_condition = " and branch_code in ('$branch') ";
            } else {
                $branch_condition = "";
            }
            if ($resigned == '1') {
                $resign_condition =  "  emp_details.status in('1','2') ";
            } else {
                $resign_condition = "  emp_details.status = '1' ";
            }
            if ($q != null) {
                $q_condition = " and (first_name like '%$q%' OR emp_proff.emp_company_id like '%$q%' ) ";
            } else {
                $q_condition = "";
            }
            if ($this->Session->read('emp_fkey')) {
                $emp_condition = ""; // Preserving old behavior
            } else {
                $emp_condition = "";
            }
            
            $branch_array = $this->EmployeeDetails->query("select * from emp_details left join emp_proff on (emp_details.emp_pkey = emp_proff.emp_fkey) where  $resign_condition $branch_condition $q_condition $emp_condition ORDER BY first_name ASC ");

            $items = array();
            $items[] = array("id" => "0", "text" => "ALL");
            foreach ($branch_array as $value) {
                $items[] = array(
                    'id' => $value['emp_details']['emp_pkey'],
                    'text' => $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id']
                );
            }
            echo json_encode(array('items' => $items));
            exit;
        }
    }
    //cancel
    public function cancalEmployee()
    {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $result = array('success' => 0);
        if (isset($_REQUEST["ids"])) {
            $ar_ids = explode(",", $_REQUEST["ids"]);
            //debug($ar_ids);
            $this->EmployeeExpenses->updateAll(
                array('EmployeeExpenses.status' => 1, 'EmployeeExpenses.expense_status' => "'Cancelled'"),
                array('EmployeeExpenses.emp_expenses_pkey' => $ar_ids)
            );
            $result['success'] = 1;
            $result['msg'] = "Expense cancelled successfully.";
        }
        echo json_encode($result);
    }

    public function empexpenselist()
    {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");

        $emp_exp_count = $this->EmployeeExpenses->find(
            'count',
            array(
                'conditions' => array(
                    'OR' => array(
                        array('authorized_by' => $cur_emp_key, 'expense_status IN ("Applied")'),
                    )
                )
            )
        );
        //        debug($emp_exp_count);
        $this->set('emp_exp_count', $emp_exp_count);
    }

   public function manageexpense($expenseId = 0)
    {

        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $this->set('cur_emp_key', $cur_emp_key);
        $this->set('user_group', $this->Session->read("user_group"));
        $exp_status = $this->EmployeeExpenses->query("select emp_expense.expense_status,authorized_by,approved_by from emp_expense where emp_expense.emp_expenses_pkey = '$expenseId'");
        $auth = $exp_status['0']['emp_expense']['authorized_by'];
        $apr = $exp_status['0']['emp_expense']['approved_by'];
        $this->set('auth', $auth);
        $this->set('apr', $apr);
            //    debug($apr);

        if (isset($auth) && $auth != '' && $auth != 'Admin') {
            $auth_person = $this->EmployeeExpenses->query(
    "select EmpName from employee_info where emp_pkey IN (" . $auth . ")"
);
            $this->set('auth_person', $auth_person);
        } else {
            $this->set('auth_by_admin', "Admin");
        }

        if (isset($apr) && $apr != '' && $apr != 'Admin') {
           $apr_person = $this->EmployeeExpenses->query(
    "select EmpName from employee_info where emp_pkey IN (" . $apr . ")"
);
            $this->set('apr_person', $apr_person);
        } else {
            $this->set('apr_by_admin', "Admin");
        }
        $status = $exp_status['0']['emp_expense']['expense_status'];
        //         debug($status);
        if ($status == 'Approved' || $status == 'Rejected' || $status == 'Removed' || $status == 'Cancelled') {
            $this->set('mode', 'view');
            $arr_expense_verified = $this->EmployeeExpenses->query("SELECT emp_expense.*,employee_info.* FROM `emp_expense` left join employee_info ON emp_expense.emp_fkey = employee_info.emp_pkey where emp_expense.emp_expenses_pkey = '$expenseId' and emp_expense.expense_status in ('Approved','Rejected','Removed','Cancelled')");
            $this->set('arr_expense', $arr_expense_verified);
        } else {
            if ($cur_emp_key == $auth && $status == 'Authorized') {
                $this->set('mode', 'view');
            } else {
                $this->set('mode', 'edit');
            }
            $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*,employee_info.* FROM `emp_expense` left join employee_info ON emp_expense.emp_fkey = employee_info.emp_pkey where emp_expense.emp_expenses_pkey = '$expenseId' and emp_expense.expense_status in ('Applied','Authorized')");
            $this->set('arr_expense', $arr_expense);
        }
$this->set('status', $status);
    }

    public function grandexpense()
    {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        date_default_timezone_set('Asia/Kolkata');
         $cur_emp_key = $this->Session->read("emp_fkey"); //Current emp_pkey. If it is admin, there is a null value.
        $user_group = $this->Session->read("user_group");
        $arr_form_data = $this->request->data;
        $curr_expensepkey = $arr_form_data['expensepkey'];
        $currentdate = date("Y-m-d");
        $authemarks = isset($arr_form_data['AUTHREMARKS']) ? $arr_form_data['AUTHREMARKS'] : '';
        $appremarks = isset($arr_form_data['REMARKS']) ? $arr_form_data['REMARKS'] : '';
        $arr_data = array();
        $arr_exp_message = $this->EmployeeExpenses->find("first", array(
            'fields' => 'expense_status,authorized_by,remarks_auth,authorized_date,approved_by,approved_date,remarks_approved',
            'conditions' => array('emp_expenses_pkey' => $curr_expensepkey)
        ));
        $auth_person = $arr_exp_message['EmployeeExpenses']['authorized_by'];
        $apr_person = $arr_exp_message['EmployeeExpenses']['approved_by'];
       if (isset($arr_form_data['reject']) && $arr_form_data['reject'] == '0') {
            if ($cur_emp_key == $apr_person || $user_group == 1) {
                $arr_data['EmployeeExpenses.expense_status'] = "'Approved'";
                $arr_data['approved_date'] = date("Y-m-d");
               // $arr_data['EmployeeExpenses.approved_by'] = "'Admin'";
            } else if ($cur_emp_key == $auth_person) {
                $arr_data['EmployeeExpenses.expense_status'] = "'Authorized'";
            }
            $arr_data['EmployeeExpenses.status'] = "1";
        } else {
            $arr_data['EmployeeExpenses.expense_status'] = "'Rejected'";
            $arr_data['EmployeeExpenses.status'] = "2";
        }
        if ($cur_emp_key == null) {

           // $arr_data['EmployeeExpenses.authorized_by'] = "'Admin'";

            if (isset($arr_form_data['reject']) && $arr_form_data['reject'] == '0') {
                $arr_data['EmployeeExpenses.remarks_auth'] = "'Authorized by Admin. " . $authemarks . "'";
                $arr_data['EmployeeExpenses.remarks_approved'] = "'Approved by Admin. " . $appremarks . "'";
                $arr_data['EmployeeExpenses.approved_date'] = "'$currentdate'";
                $arr_data['EmployeeExpenses.authorized_date'] = "'$currentdate'";
            } else {
                $arr_data['EmployeeExpenses.remarks_auth'] = "'Rejected by Admin. " . $authemarks . "'";
                $arr_data['EmployeeExpenses.remarks_approved'] = "'Rejected by Admin. " . $appremarks . "'";
                $arr_data['EmployeeExpenses.approved_date'] = "'$currentdate'";
                $arr_data['EmployeeExpenses.authorized_date'] = "'$currentdate'";
            }
        } else { 
            if ($cur_emp_key == $apr_person) {
                $arr_data['EmployeeExpenses.remarks_approved'] = "'$appremarks'";
            } else if ($cur_emp_key == $auth_person) {
                $arr_data['EmployeeExpenses.remarks_auth'] = "'$authemarks'";
            }
        }
        if ($cur_emp_key == $apr_person || $user_group == 1) {
            $arr_data['EmployeeExpenses.approved_date'] = "'$currentdate'";
        } else if ($cur_emp_key == $auth_person) {
            $arr_data['EmployeeExpenses.authorized_date'] = "'$currentdate'";
        }
        $this->EmployeeExpenses->updateAll(
            $arr_data,
            array('EmployeeExpenses.emp_expenses_pkey' => $curr_expensepkey)
        );
        $resp["success"] = true;
        if (isset($arr_form_data['reject']) && $arr_form_data['reject'] == '0') {
            if ($cur_emp_key == $apr_person || $user_group == 1) {
                $resp["message"] = 'Expense Approved Successfully!!!';
            } else if ($cur_emp_key == $auth_person) {
                $resp["message"] = 'Expense Authorized Successfully!!!';
            }
        } else {
            $resp["message"] = 'Expense Rejected Successfully!!!';
        }
        return json_encode($resp);
        // debug($auth_name);
    }

    public function listempexpense()
    {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
        $cur_emp_key = $this->Session->read("emp_fkey");
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        //        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*, CONCAT(first_name,' ',last_name) AS emp_name
        //                                                    FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status = 'Applied'");
        //         debug($arr_expense);
        //        if(isset($arr_request_data['emp']) && $arr_request_data['emp']!=null){
        //            $conditions = " and (first_name like '%" . $arr_request_data['emp'] . "%' OR last_name like '%" . $arr_request_data['emp'] . "%' OR expense_type like '%" . $arr_request_data['emp'] . "%')";
        //        }else{
        //            $conditions="";
        //        }


        $arr_count = $this->EmployeeExpenses->query("SELECT count(*) as count
        FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where (emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status IN ('Applied')) OR (emp_expense.approved_by = '$cur_emp_key' and emp_expense.expense_status IN ('Authorized')) order by created_date desc");
        $count = (int) $arr_count[0][0]['count'];

        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*, CONCAT(first_name,' ',last_name) AS emp_name
        FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where (emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status IN ('Applied')) OR (emp_expense.approved_by = '$cur_emp_key' and emp_expense.expense_status IN ('Authorized')) order by created_date desc limit $limit offset $ofst");

        $arr_expensedata["rows"] = array();
        foreach ($arr_expense as $key => $value) {
            $arr_expensedata["rows"][$key] = array_merge($value['emp_expense'], $value['0']);
        }
        $arr_expensedata["total"] = $count;
        echo json_encode($arr_expensedata);
    }

    public function listempexpenseverified()
    {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $arr_request_data = $this->request->data;
        $cur_emp_key = $this->Session->read("emp_fkey");
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        //        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*, CONCAT(first_name,' ',last_name) AS emp_name
        //                                                    FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status = 'Approved' order by authorized_date desc");
        if (isset($arr_request_data['emp']) && $arr_request_data['emp'] != "") {
            $conditions = " (emp_expense.authorized_by = '$cur_emp_key' AND emp_expense.expense_status in (" . $arr_request_data['emp'] . ") AND authorized_date != '0000-00-00') OR (emp_expense.approved_by = '$cur_emp_key' AND emp_expense.expense_status IN (" . $arr_request_data['emp'] . ") AND approved_date != '0000-00-00')";
        } else {
            $conditions = " (emp_expense.authorized_by = '$cur_emp_key' and emp_expense.expense_status IN ('Authorized','Approved','Rejected') AND authorized_date != '0000-00-00') OR (emp_expense.approved_by = '$cur_emp_key' and emp_expense.expense_status IN ('Approved','Rejected') AND approved_date != '0000-00-00') ";
        }

        $count_array = $this->EmployeeExpenses->query("SELECT count(*) as count
        FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where  " . $conditions . " order by created_date desc");

        $count = (int) $count_array[0][0]['count'];
        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*, CONCAT(first_name,' ',last_name) AS emp_name
        FROM `emp_expense` left join emp_details ON emp_expense.emp_fkey= emp_details.emp_pkey where " . $conditions . " order by created_date desc limit $limit offset $ofst");
        //         debug($arr_expense);

        $arr_expenseverifieddata["rows"] = array();
        foreach ($arr_expense as $key => $value) {
            $arr_expenseverifieddata["rows"][$key] = array_merge($value['emp_expense'], $value['0']);
        }
        $arr_expenseverifieddata["total"] = $count;
        echo json_encode($arr_expenseverifieddata);
    }

    public function employeerequests()
    {

        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
    }

    public function viewrequest()
    {
        $this->autoRender = FALSE;
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page'];
        $ofst = ($page - 1) * $limit;
        $count_array = $this->EmployeeExpenses->query("SELECT count(*) as count
                                                        FROM `emp_expense` 
                                                        left join emp_details ed1 ON emp_expense.authorized_by = ed1.emp_pkey 
                                                        left join emp_details ed2 ON emp_expense.approved_by = ed2.emp_pkey 
                                                        where emp_expense.emp_fkey = '$cur_emp_key' and emp_expense.status != 0");
        $count = $count_array[0][0]['count'];
        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*, CONCAT(ed1.first_name,' ',ed1.last_name) AS emp_name,      
                                                        CONCAT(ed2.first_name,' ',ed2.last_name) AS emp_name2
                                                        FROM `emp_expense` 
                                                        left join emp_details ed1 ON emp_expense.authorized_by = ed1.emp_pkey 
                                                        left join emp_details ed2 ON emp_expense.approved_by = ed2.emp_pkey 
                                                        where emp_expense.emp_fkey = '$cur_emp_key' and emp_expense.status != 0 order by created_date desc limit $limit offset $ofst");
        $arr_expensedata["rows"] = array();
        foreach ($arr_expense as $key => $value) {
            if ($value['0']['emp_name'] == null) {
                $value['0']['emp_name'] = "Admin";
            }
            if ($value['0']['emp_name2'] == null) {
                $value['0']['emp_name2'] = "Admin";
            }
            $arr_expensedata["rows"][$key] = array_merge($value['emp_expense'], $value['0']);
        }
        $arr_expensedata["total"] = (int) $count;
        echo json_encode($arr_expensedata);
    }

     public function viewexpense($expenseId = 0)
    {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $cur_emp_key = $this->Session->read("emp_fkey");
        $arr_expense = $this->EmployeeExpenses->query("SELECT emp_expense.*,employee_info.*
                                                        FROM `emp_expense` left join employee_info ON emp_expense.emp_fkey = employee_info.emp_pkey 
                                                        where emp_expense.emp_fkey = '$cur_emp_key' and emp_expense.emp_expenses_pkey = '$expenseId' ");
        $this->set('arr_expense', $arr_expense);
        $auth = $arr_expense[0]['emp_expense']['authorized_by'];
        $apr = $arr_expense[0]['emp_expense']['approved_by'];

        if (isset($auth) && $auth != '') {
            $auth_person = $this->EmployeeExpenses->query("select EmpName from employee_info where emp_pkey=" . $auth . "");
            $this->set('auth_person', $auth_person);
        } else {
            $this->set('auth_by_admin', "Admin");
        }

        if (isset($apr) && $apr != NULL) {
            $apr_person = $this->EmployeeExpenses->query("select EmpName from employee_info where emp_pkey=" . $apr . "");
            $this->set('apr_person', $apr_person);
        } else {
            $this->set('apr_by_admin', "Admin");
        }
    }

    public function showimage($pkey = 0)
    {
        $this->EmployeeExpenses->useDbConfig = $this->Session->read('ds');
        $str_company_code = $this->Session->read('company_code');
        $this->set('company_code', $str_company_code);
        $image = $this->EmployeeExpenses->query("select emp_expense.image from emp_expense where emp_expense.emp_expenses_pkey = '$pkey'");
        $this->set('image', $image);
    }
}
                